<?php
// public/api/chatbot.php - AI-Powered Event Ticketing Chatbot

// Suppress error display and use output buffering
error_reporting(E_ALL);
ini_set('display_errors', 0);
ini_set('log_errors', 1);

// Start output buffering early to catch any errors
if (!ob_get_level()) {
    ob_start();
}

// Catch fatal errors and return JSON
register_shutdown_function(function() {
    $error = error_get_last();
    if ($error !== NULL && in_array($error['type'], [E_ERROR, E_PARSE, E_CORE_ERROR, E_COMPILE_ERROR])) {
        // Only handle if we haven't already sent output
        if (!headers_sent()) {
            ob_clean();
            http_response_code(500);
            header('Content-Type: application/json');
            echo json_encode([
                'success' => false,
                'error' => 'System error',
                'message' => 'Please try again later',
                'error_details' => $error['message'] . ' in ' . $error['file'] . ' on line ' . $error['line']
            ]);
        }
        exit();
    }
});

// Set headers early
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, GET, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

// Handle preflight
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

// CORRECTED: Include required files with proper absolute path
$project_root = dirname(dirname(dirname(__FILE__))); // Gets: C:\xampp\htdocs\event-booking-website

try {
    require_once $project_root . '/database/session_init.php';
    require_once $project_root . '/config/db_connect.php';
    
    // Load AI service if available
    $aiServicePath = $project_root . '/app/services/AIChatbotService.php';
    $aiEnabled = file_exists($aiServicePath);
    if ($aiEnabled) {
        require_once $aiServicePath;
    }
} catch (Exception $e) {
    ob_clean();
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'error' => 'System initialization error',
        'message' => 'Please try again later'
    ]);
    exit();
}

class EventChatbotPDO {
    private $pdo;
    private $user_id;
    private $session_id;
    private $conversation_id;
    
    public function __construct($pdo_connection) {
        $this->pdo = $pdo_connection;
        
        try {
            $this->session_id = session_id();
        } catch (Exception $e) {
            error_log("Chatbot session error: " . $e->getMessage());
            $this->session_id = 'guest_' . uniqid();
        }
        
        if (empty($this->session_id)) {
            $this->session_id = 'guest_' . uniqid();
        }
        
        $this->user_id = isset($_SESSION['user_id']) ? $_SESSION['user_id'] : null;
        
        // Start or resume conversation (may return null if tables don't exist)
        try {
            $this->conversation_id = $this->getOrCreateConversation();
        } catch (Exception $e) {
            error_log("Chatbot conversation init error: " . $e->getMessage());
            $this->conversation_id = null; // Continue without conversation tracking
        }
    }
    
    public function processMessage($user_message) {
        $user_message = trim($user_message);
        
        if (empty($user_message)) {
            $response = "Hello! How can I help with your event tickets today?";
            $this->logMessage('user', $user_message, 'empty', 0);
            $this->logMessage('bot', $response, 'greeting', 1.0);
            return $this->createResponse($response);
        }
        
        // Log user message
        $this->logMessage('user', $user_message, null, 0);
        
        // Try AI-powered response first (if AI service is available and configured)
        $response = null;
        $intent = null;
        $confidence = 0;
        
        if (class_exists('AIChatbotService')) {
            try {
                $aiService = new AIChatbotService($this->pdo);
                $response = $aiService->processMessage($user_message, $this->conversation_id);
                if ($response && $response !== "I apologize, but I'm having trouble processing your request right now. Please try again or contact support.") {
                    $intent = 'ai_response';
                    $confidence = 0.95;
                }
            } catch (Exception $e) {
                error_log("AI Chatbot Service Error: " . $e->getMessage());
                // Continue to fallback
            }
        }
        
        // If AI fails or not available, fallback to rule-based system
        if (!$response) {
            // 1. Check training data
            $response = $this->checkTrainingData($user_message);
            $intent = 'trained_response';
            $confidence = 1.0;
            
            // 2. Check for specific events query
            if (!$response && $this->isEventQuery($user_message)) {
                $response = $this->getEventsFromDatabase($user_message);
                $intent = 'events_query';
                $confidence = 0.9;
            }
            
            // 3. Check for refund queries
            if (!$response && $this->isRefundQuery($user_message)) {
                $response = $this->getRefundResponse();
                $intent = 'refund';
                $confidence = 0.85;
            }
            
            // 4. Check for booking queries
            if (!$response && $this->isBookingQuery($user_message)) {
                $response = $this->getBookingResponse();
                $intent = 'booking';
                $confidence = 0.85;
            }
            
            // 5. Final fallback response
            if (!$response) {
                $response = $this->getFallbackResponse($user_message);
                $intent = 'general';
                $confidence = 0.5;
            }
        }
        
        // Log bot response
        $this->logMessage('bot', $response, $intent, $confidence);
        
        return $this->createResponse($response);
    }
    
    private function getOrCreateConversation() {
        try {
            // Check if table exists first
            $tableCheck = $this->pdo->query("SHOW TABLES LIKE 'chatbot_conversations'");
            if ($tableCheck->rowCount() === 0) {
                error_log("Chatbot: chatbot_conversations table does not exist");
                return null; // Return null if table doesn't exist
            }
            
            // Check if conversation exists
            $stmt = $this->pdo->prepare("
                SELECT id FROM chatbot_conversations 
                WHERE session_id = ? AND (status = 'active' OR status IS NULL)
                ORDER BY created_at DESC LIMIT 1
            ");
            $stmt->execute([$this->session_id]);
            $row = $stmt->fetch();
            
            if ($row) {
                return $row['id'];
            }
            
            // Create new conversation
            $user_ip = $_SERVER['REMOTE_ADDR'] ?? '';
            $user_agent = $_SERVER['HTTP_USER_AGENT'] ?? '';
            
            $stmt = $this->pdo->prepare("
                INSERT INTO chatbot_conversations (user_id, session_id, user_ip, user_agent) 
                VALUES (?, ?, ?, ?)
            ");
            $stmt->execute([$this->user_id, $this->session_id, $user_ip, $user_agent]);
            
            return $this->pdo->lastInsertId();
            
        } catch (PDOException $e) {
            error_log("Chatbot conversation error: " . $e->getMessage());
            return null; // Return null on error, chatbot will still work
        }
    }
    
    private function logMessage($type, $text, $intent = null, $confidence = null) {
        if (!$this->conversation_id) return false;
        
        try {
            // Check if table exists first
            $tableCheck = $this->pdo->query("SHOW TABLES LIKE 'chatbot_messages'");
            if ($tableCheck->rowCount() === 0) {
                return false; // Silently fail if table doesn't exist
            }
            
            $stmt = $this->pdo->prepare("
                INSERT INTO chatbot_messages (conversation_id, message_type, message_text, intent, confidence) 
                VALUES (?, ?, ?, ?, ?)
            ");
            return $stmt->execute([$this->conversation_id, $type, $text, $intent, $confidence]);
            
        } catch (PDOException $e) {
            error_log("Chatbot message log error: " . $e->getMessage());
            return false; // Silently fail, don't break chatbot
        }
    }
    
    private function checkTrainingData($message) {
        try {
            // Check if table exists first
            $tableCheck = $this->pdo->query("SHOW TABLES LIKE 'chatbot_training'");
            if ($tableCheck->rowCount() === 0) {
                return false; // Table doesn't exist, skip training data
            }
            
            $message_lower = strtolower($message);
            $words = explode(' ', preg_replace('/[^a-z0-9\s]/', '', $message_lower));
            
            // Build search query
            $placeholders = [];
            $params = [];
            
            foreach ($words as $word) {
                if (strlen($word) > 2) {
                    $placeholders[] = "user_input LIKE ? OR keywords LIKE ?";
                    $search_term = "%$word%";
                    $params[] = $search_term;
                    $params[] = $search_term;
                }
            }
            
            if (empty($placeholders)) {
                return false;
            }
            
            $where_clause = implode(' OR ', $placeholders);
            $query = "
                SELECT correct_response, intent FROM chatbot_training 
                WHERE is_active = 1 AND ($where_clause)
                ORDER BY use_count DESC, LENGTH(user_input) ASC 
                LIMIT 1
            ";
            
            $stmt = $this->pdo->prepare($query);
            $stmt->execute($params);
            $row = $stmt->fetch();
            
            if ($row) {
                // Update use count (optional, don't fail if it doesn't work)
                try {
                    $update_stmt = $this->pdo->prepare("
                        UPDATE chatbot_training 
                        SET use_count = use_count + 1 
                        WHERE id = (
                            SELECT id FROM (
                                SELECT id FROM chatbot_training 
                                WHERE is_active = 1 AND ($where_clause)
                                ORDER BY use_count DESC, LENGTH(user_input) ASC 
                                LIMIT 1
                            ) AS temp
                        )
                    ");
                    $update_stmt->execute($params);
                } catch (PDOException $e) {
                    // Ignore update errors
                }
                
                return $row['correct_response'];
            }
            
        } catch (PDOException $e) {
            error_log("Chatbot training data error: " . $e->getMessage());
        }
        
        return false;
    }
    
    private function isEventQuery($message) {
        $message_lower = strtolower($message);
        $event_keywords = ['events', 'upcoming', 'shows', 'concerts', 'sports', 'movies', 'theater', 'what\'s on', 'available', 'happening'];
        
        foreach ($event_keywords as $keyword) {
            if (strpos($message_lower, $keyword) !== false) {
                return true;
            }
        }
        
        return false;
    }
    
    private function getEventsFromDatabase($message) {
        // Check for specific event names first
        $specific_events = ['tul8te', 'marwan pablo', 'ahly', 'avengers', 'helmy', 'comedy', 'basketball', 'football'];
        $message_lower = strtolower($message);
        
        foreach ($specific_events as $event) {
            if (strpos($message_lower, $event) !== false) {
                return $this->getSpecificEventDetails($event);
            }
        }
        
        // Get all upcoming events
        return $this->getUpcomingEvents();
    }
    
    private function getSpecificEventDetails($event_name) {
        try {
            $search_term = "%$event_name%";
            
            $query = "
                SELECT e.title, e.date, e.price, e.available_tickets, 
                       v.name as venue_name, v.city,
                       GROUP_CONCAT(DISTINCT etc.category_name SEPARATOR ', ') as categories
                FROM events e
                JOIN venues v ON e.venue_id = v.id
                LEFT JOIN event_ticket_categories etc ON e.id = etc.event_id
                WHERE e.status = 'active' 
                AND e.date >= CURDATE()
                AND (e.title LIKE ? OR e.description LIKE ?)
                GROUP BY e.id
                ORDER BY e.date ASC
                LIMIT 3
            ";
            
            $stmt = $this->pdo->prepare($query);
            $stmt->execute([$search_term, $search_term]);
            $events = $stmt->fetchAll();
            
            if ($events) {
                $response = "🎟️ **Events matching \"$event_name\":**\n\n";
                
                foreach ($events as $row) {
                    $date = date('M d, Y h:i A', strtotime($row['date']));
                    $response .= "🎭 **" . htmlspecialchars($row['title']) . "**\n";
                    $response .= "📅 " . $date . "\n";
                    $response .= "📍 " . htmlspecialchars($row['venue_name']) . ", " . htmlspecialchars($row['city']) . "\n";
                    $response .= "💰 From " . $row['price'] . " USD\n";
                    $response .= "🎫 " . $row['available_tickets'] . " tickets available\n";
                    
                    if ($row['categories']) {
                        $response .= "📋 Categories: " . htmlspecialchars($row['categories']) . "\n";
                    }
                    
                    $response .= "---\n";
                }
                
                $response .= "\nClick any event on our website to book tickets!";
                return $response;
            }
            
        } catch (PDOException $e) {
            error_log("Chatbot event details error: " . $e->getMessage());
        }
        
        return "I couldn't find specific events matching \"$event_name\". Try browsing our Events page for all available options.";
    }
    
    private function getUpcomingEvents() {
        try {
            $query = "
                SELECT e.title, e.date, e.price, e.available_tickets, v.name as venue_name
                FROM events e
                JOIN venues v ON e.venue_id = v.id
                WHERE e.status = 'active' 
                AND e.date >= CURDATE()
                ORDER BY e.date ASC
                LIMIT 5
            ";
            
            $stmt = $this->pdo->prepare($query);
            $stmt->execute();
            $events = $stmt->fetchAll();
            
            if ($events) {
                $response = "🎭 **Upcoming Events:**\n\n";
                
                foreach ($events as $row) {
                    $date = date('M d, Y', strtotime($row['date']));
                    $response .= "• **" . htmlspecialchars($row['title']) . "**\n";
                    $response .= "  📅 " . $date . " | ";
                    $response .= "📍 " . htmlspecialchars($row['venue_name']) . " | ";
                    $response .= "💰 " . $row['price'] . " USD\n";
                    $response .= "  🎫 " . $row['available_tickets'] . " tickets left\n\n";
                }
                
                $response .= "Browse all events on our website to find the perfect one for you!";
                return $response;
            }
            
        } catch (PDOException $e) {
            error_log("Chatbot upcoming events error: " . $e->getMessage());
        }
        
        return "We're adding new events regularly! Check back soon or contact us for event suggestions.";
    }
    
    private function isRefundQuery($message) {
        $message_lower = strtolower($message);
        $refund_keywords = ['refund', 'cancel', 'return', 'money back', 'get back', 'reimbursement'];
        
        foreach ($refund_keywords as $keyword) {
            if (strpos($message_lower, $keyword) !== false) {
                return true;
            }
        }
        
        return false;
    }
    
    private function getRefundResponse() {
        $user_info = '';
        if ($this->user_id) {
            $user_info = "\n\n👤 **Personal Note:** Since you're logged in (User ID: {$this->user_id}), you can check your bookings in your account dashboard for easy refund requests.";
        }
        
        return "🔄 **Refund Policy**\n\n" .
               "✅ **Refunds Available:** Up to 48 hours before event start time\n" .
               "❌ **No Refunds:** Within 48 hours of event\n" .
               "📧 **How to Request:** Email support@event-booking.com with:\n" .
               "   • Your booking code\n" .
               "   • Reason for refund\n" .
               "   • Event details\n\n" .
               "⏱️ **Processing:** 5-7 business days after approval" .
               $user_info;
    }
    
    private function isBookingQuery($message) {
        $message_lower = strtolower($message);
        $booking_keywords = ['book', 'buy', 'purchase', 'get ticket', 'reserve', 'how to book'];
        
        foreach ($booking_keywords as $keyword) {
            if (strpos($message_lower, $keyword) !== false) {
                return true;
            }
        }
        
        return false;
    }
    
    private function getBookingResponse() {
        $login_info = '';
        if ($this->user_id) {
            $login_info = "\n\n👤 **Great!** I see you're already logged in as User ID: {$this->user_id}. You can book tickets directly and they'll be saved to your account.";
        } else {
            $login_info = "\n\n💡 **Tip:** Login or create an account first for faster booking and ticket management. Your tickets will be saved automatically!";
        }
        
        return "🎫 **How to Book Tickets**\n\n" .
               "1. **Browse Events** - Visit our homepage or Events page\n" .
               "2. **Select Event** - Click on any event you like\n" .
               "3. **Choose Tickets** - Select category and quantity\n" .
               "4. **Checkout** - Review your selection\n" .
               "5. **Payment** - Complete secure payment\n" .
               "6. **Confirmation** - Receive e-ticket via email\n\n" .
               "🎟️ Tickets are also saved in your account dashboard." .
               $login_info;
    }
    
    private function getFallbackResponse($message) {
        $user_context = $this->user_id ? "\n\n👤 *Note: You're logged in as user #{$this->user_id}*" : "";
        
        $responses = [
            "I understand you're asking about: \"{$message}\"\nFor specific help, please:\n• Browse our Events page\n• Check FAQ section\n• Contact support if urgent{$user_context}",
            "I can help with:\n• Booking tickets\n• Event information\n• Refund policies\n• Ticket issues\n• General support\n\nWhat specifically do you need?{$user_context}",
            "As your event assistant, I specialize in ticket-related queries. Could you rephrase your question about events or bookings?{$user_context}"
        ];
        
        return $responses[array_rand($responses)];
    }
    
    private function createResponse($message) {
        return [
            'success' => true,
            'response' => $message,
            'user_id' => $this->user_id,
            'session_id' => $this->session_id,
            'conversation_id' => $this->conversation_id,
            'timestamp' => date('Y-m-d H:i:s')
        ];
    }
}

// Handle requests
try {
    // Get database connection - use global $pdo created by db_connect.php
    global $pdo;
    
    if (!isset($pdo) || !$pdo) {
        // Fallback: try Database class
        if (class_exists('Database')) {
            $database = new Database();
            $pdo = $database->getConnection();
        }
        
        if (!$pdo) {
            throw new Exception("Database connection failed");
        }
    }
    
    // Instantiate chatbot with error handling
    try {
        $chatbot = new EventChatbotPDO($pdo);
    } catch (Exception $e) {
        ob_clean();
        error_log("Chatbot instantiation error: " . $e->getMessage());
        error_log("Chatbot instantiation trace: " . $e->getTraceAsString());
        if (!headers_sent()) {
            http_response_code(500);
            header('Content-Type: application/json');
        }
        echo json_encode([
            'success' => false,
            'error' => 'Chatbot initialization failed',
            'message' => 'Please try again later',
            'error_details' => $e->getMessage()
        ]);
        exit();
    }
    
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $input = file_get_contents('php://input');
        $data = json_decode($input, true);
        
        if (json_last_error() !== JSON_ERROR_NONE) {
            $data = $_POST;
        }
        
        $message = isset($data['message']) ? trim($data['message']) : '';
        
        if (empty($message)) {
            ob_clean();
            echo json_encode([
                'success' => false,
                'error' => 'Please type a question'
            ]);
            exit;
        }
        
        $response = $chatbot->processMessage($message);
        ob_clean(); // Clear any output before sending JSON
        echo json_encode($response, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
        
    } elseif ($_SERVER['REQUEST_METHOD'] === 'GET') {
        // Return API info
        $user_id = isset($_SESSION['user_id']) ? $_SESSION['user_id'] : null;
        
        ob_clean(); // Clear any output before sending JSON
        echo json_encode([
            'success' => true,
            'name' => 'Event Booking Chatbot (PDO)',
            'version' => '4.0',
            'user_logged_in' => !is_null($user_id),
            'user_id' => $user_id,
            'database' => 'PDO connected',
            'features' => [
                'AI-powered responses (OpenAI GPT)',
                'RAG - Retrieval Augmented Generation',
                'Real-time event data integration',
                'User context aware',
                'Conversation history management',
                'Training data fallback',
                'PDO database integration'
            ]
        ], JSON_PRETTY_PRINT);
    }
    
} catch (Exception $e) {
    ob_clean();
    error_log("Chatbot Error: " . $e->getMessage());
    error_log("Chatbot Error Trace: " . $e->getTraceAsString());
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'error' => 'System error',
        'message' => 'Please try again later'
    ]);
}
?>
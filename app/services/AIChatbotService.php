<?php
// AI-Powered Chatbot Service with RAG (Retrieval Augmented Generation)

class AIChatbotService {
    private $pdo;
    private $config;
    private $conversationHistory = [];
    private $userContext = [];
    
    public function __construct($pdo) {
        $this->pdo = $pdo;
        $configPath = dirname(dirname(__DIR__)) . '/config/ai_config.php';
        if (file_exists($configPath)) {
            $this->config = require $configPath;
        } else {
            // Default config if file doesn't exist
            $this->config = [
                'api_key' => '',
                'model' => 'gpt-4o-mini',
                'temperature' => 0.7,
                'max_tokens' => 500,
                'use_fallback' => true,
                'max_conversation_history' => 10,
                'include_user_context' => true,
                'include_event_data' => true
            ];
        }
        $this->loadUserContext();
    }
    
    /**
     * Process user message with AI
     */
    public function processMessage($userMessage, $conversationId = null) {
        // Add to conversation history
        $this->conversationHistory[] = ['role' => 'user', 'content' => $userMessage];
        
        // Limit history size
        if (count($this->conversationHistory) > $this->config['max_conversation_history'] * 2) {
            $this->conversationHistory = array_slice($this->conversationHistory, -$this->config['max_conversation_history'] * 2);
        }
        
        // Build context with RAG
        $context = $this->buildContext($userMessage);
        
        // Get AI response
        $aiResponse = $this->getAIResponse($userMessage, $context);
        
        // If AI fails, return null so the main chatbot can use its rule-based fallback
        // Don't use internal fallback here - let the main chatbot handle it
        if (!$aiResponse) {
            error_log("AI Chatbot Service: Failed to get AI response, returning null for fallback");
            return null; // Return null to trigger rule-based fallback in chatbot.php
        }
        
        // Add AI response to history
        if ($aiResponse) {
            $this->conversationHistory[] = ['role' => 'assistant', 'content' => $aiResponse];
        }
        
        return $aiResponse;
    }
    
    /**
     * Build context using RAG (Retrieval Augmented Generation)
     */
    private function buildContext($userMessage) {
        $context = [];
        
        // 1. System knowledge about the website
        $context['website_info'] = $this->getWebsiteKnowledge();
        
        // 2. Real-time event data (if query is about events)
        if ($this->isEventRelated($userMessage)) {
            $context['events'] = $this->getRelevantEvents($userMessage);
        }
        
        // 3. User-specific context
        if ($this->config['include_user_context']) {
            $context['user'] = $this->userContext;
        }
        
        // 4. Policies and rules
        $context['policies'] = $this->getPolicies();
        
        // 5. Training data (for reference)
        $context['training_data'] = $this->getRelevantTrainingData($userMessage);
        
        return $context;
    }
    
    /**
     * Get AI response from AI API (supports multiple providers)
     */
    private function getAIResponse($userMessage, $context) {
        $provider = $this->config['provider'] ?? 'openai';
        
        // Check if API key is configured (required for all providers including OpenRouter)
        if (empty($this->config['api_key'])) {
            error_log("AI Chatbot: API key not configured for provider: $provider");
            if ($provider === 'openrouter') {
                error_log("AI Chatbot: OpenRouter requires an API key. Get a free one at https://openrouter.ai/keys");
            }
            return null; // Will use fallback
        }
        
        try {
            // Build system prompt with context
            $systemPrompt = $this->buildSystemPrompt($context);
            
            // Build messages array
            $messages = [
                ['role' => 'system', 'content' => $systemPrompt]
            ];
            
            // Add conversation history
            foreach ($this->conversationHistory as $msg) {
                $messages[] = $msg;
            }
            
            // Make API call based on provider
            $response = null;
            switch ($provider) {
                case 'openrouter':
                    $response = $this->callOpenRouter($messages);
                    break;
                case 'google':
                    $response = $this->callGoogleGemini($messages);
                    break;
                case 'groq':
                    $response = $this->callGroq($messages);
                    break;
                case 'huggingface':
                    $response = $this->callHuggingFace($messages);
                    break;
                case 'openai':
                default:
                    $response = $this->callOpenAI($messages);
                    break;
            }
            
            // Parse response based on provider
            $aiText = null;
            if ($provider === 'google') {
                if ($response && isset($response['candidates'][0]['content']['parts'][0]['text'])) {
                    $aiText = trim($response['candidates'][0]['content']['parts'][0]['text']);
                }
            } elseif ($provider === 'huggingface') {
                if (isset($response['choices'][0]['message']['content'])) {
                    $aiText = trim($response['choices'][0]['message']['content']);
                } elseif (isset($response[0]['generated_text'])) {
                    $aiText = trim($response[0]['generated_text']);
                } elseif (isset($response['generated_text'])) {
                    $aiText = trim($response['generated_text']);
                }
            } else {
                if ($response && isset($response['choices'][0]['message']['content'])) {
                    $aiText = trim($response['choices'][0]['message']['content']);
                }
            }
            
            if ($aiText) {
                return $aiText;
            } else {
                error_log("AI Chatbot: Invalid response format from API (Provider: $provider)");
                return null;
            }
            
        } catch (Exception $e) {
            $errorMsg = $e->getMessage();
            error_log("AI Chatbot Error ($provider): " . $errorMsg);
            error_log("AI Chatbot Error Trace: " . $e->getTraceAsString());
            
            if (strpos($errorMsg, 'Billing Required') !== false || 
                strpos($errorMsg, 'insufficient_quota') !== false) {
                error_log("AI Chatbot: Billing/quota issue detected");
            }
            
            if (strpos($errorMsg, 'API key') !== false || 
                strpos($errorMsg, '401') !== false ||
                strpos($errorMsg, 'Unauthorized') !== false) {
                error_log("AI Chatbot: API key issue - check your API key in config/ai_config.php");
            }
            
            return null; // Return null to trigger rule-based fallback
        }
    }
    
    /**
     * Call OpenRouter API
     */
    private function callOpenRouter($messages) {
        $model = $this->config['model'] ?? 'meta-llama/llama-3.2-3b-instruct';
        $model = str_replace(':free', '', $model);
        
        $url = 'https://openrouter.ai/api/v1/chat/completions';
        
        $postData = [
            'model' => $model,
            'messages' => $messages,
            'temperature' => $this->config['temperature'] ?? 0.7,
            'max_tokens' => $this->config['max_tokens'] ?? 500
        ];
        
        $headers = [
            'Content-Type: application/json',
            'HTTP-Referer: https://event-booking-website.com',
            'X-Title: EGZLY Event Booking'
        ];
        
        if (!empty($this->config['api_key'])) {
            $headers[] = 'Authorization: Bearer ' . $this->config['api_key'];
        }
        
        $ch = curl_init($url);
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_POST => true,
            CURLOPT_HTTPHEADER => $headers,
            CURLOPT_POSTFIELDS => json_encode($postData),
            CURLOPT_TIMEOUT => 30,
            CURLOPT_SSL_VERIFYPEER => true
        ]);
        
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $error = curl_error($ch);
        curl_close($ch);
        
        if ($error) {
            error_log("AI Chatbot OpenRouter cURL Error: " . $error);
            throw new Exception("cURL Error: " . $error);
        }
        
        if ($httpCode !== 200) {
            $errorData = json_decode($response, true);
            $errorMessage = isset($errorData['error']['message']) ? $errorData['error']['message'] : 'Unknown error';
            
            error_log("AI Chatbot OpenRouter Error ($httpCode): " . $errorMessage);
            
            if ($httpCode === 401 && empty($this->config['api_key'])) {
                throw new Exception("OpenRouter requires an API key. Get a free one at https://openrouter.ai/keys. Error: " . $errorMessage);
            }
            
            throw new Exception("API Error ($httpCode): " . $errorMessage);
        }
        
        $decoded = json_decode($response, true);
        if (json_last_error() !== JSON_ERROR_NONE) {
            error_log("AI Chatbot OpenRouter JSON Error: " . json_last_error_msg());
            throw new Exception("Invalid JSON response from OpenRouter API");
        }
        
        return $decoded;
    }
    
    /**
     * Call Google Gemini API (FREE - No credit card needed!)
     */
    private function callGoogleGemini($messages) {
        $model = $this->config['model'] ?? 'gemini-1.5-flash';
        $apiKey = $this->config['api_key'];
        $url = "https://generativelanguage.googleapis.com/v1beta/models/{$model}:generateContent?key={$apiKey}";
        
        // Convert messages format for Gemini
        $contents = [];
        foreach ($messages as $msg) {
            if ($msg['role'] === 'system') {
                // Gemini doesn't have system messages, prepend to first user message
                if (!empty($contents)) {
                    $contents[0]['parts'][0]['text'] = $msg['content'] . "\n\n" . $contents[0]['parts'][0]['text'];
                } else {
                    $contents[] = [
                        'role' => 'user',
                        'parts' => [['text' => $msg['content']]]
                    ];
                }
            } else {
                $role = $msg['role'] === 'assistant' ? 'model' : 'user';
                $contents[] = [
                    'role' => $role,
                    'parts' => [['text' => $msg['content']]]
                ];
            }
        }
        
        $postData = [
            'contents' => $contents,
            'generationConfig' => [
                'temperature' => $this->config['temperature'] ?? 0.7,
                'maxOutputTokens' => $this->config['max_tokens'] ?? 500
            ]
        ];
        
        $ch = curl_init($url);
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_POST => true,
            CURLOPT_HTTPHEADER => ['Content-Type: application/json'],
            CURLOPT_POSTFIELDS => json_encode($postData),
            CURLOPT_TIMEOUT => 30,
            CURLOPT_SSL_VERIFYPEER => true
        ]);
        
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $error = curl_error($ch);
        curl_close($ch);
        
        if ($error) {
            throw new Exception("cURL Error: " . $error);
        }
        
        if ($httpCode !== 200) {
            $errorData = json_decode($response, true);
            $errorMessage = isset($errorData['error']['message']) ? $errorData['error']['message'] : 'Unknown error';
            throw new Exception("API Error ($httpCode): " . $errorMessage);
        }
        
        return json_decode($response, true);
    }
    
    /**
     * Call Groq API (FREE tier available)
     */
    private function callGroq($messages) {
        $url = 'https://api.groq.com/openai/v1/chat/completions';
        $postData = [
            'model' => $this->config['model'] ?? 'llama-3.1-8b-instant',
            'messages' => $messages,
            'temperature' => $this->config['temperature'] ?? 0.7,
            'max_tokens' => $this->config['max_tokens'] ?? 500
        ];
        
        $ch = curl_init($url);
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_POST => true,
            CURLOPT_HTTPHEADER => [
                'Content-Type: application/json',
                'Authorization: Bearer ' . $this->config['api_key']
            ],
            CURLOPT_POSTFIELDS => json_encode($postData),
            CURLOPT_TIMEOUT => 30,
            CURLOPT_SSL_VERIFYPEER => true
        ]);
        
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $error = curl_error($ch);
        curl_close($ch);
        
        if ($error) {
            throw new Exception("cURL Error: " . $error);
        }
        
        if ($httpCode !== 200) {
            $errorData = json_decode($response, true);
            $errorMessage = isset($errorData['error']['message']) ? $errorData['error']['message'] : 'Unknown error';
            throw new Exception("API Error ($httpCode): " . $errorMessage);
        }
        
        return json_decode($response, true);
    }
    
    /**
     * Call Hugging Face Inference API (FREE tier - No billing required!)
     */
    private function callHuggingFace($messages) {
        $model = $this->config['model'] ?? 'mistralai/Mistral-7B-Instruct-v0.2';
        
        // Try router endpoint (required as of 2024)
        // Note: Some models may need to be deployed on Hugging Face infrastructure
        $url = "https://router.huggingface.co/models/{$model}";
        
        // Build prompt in instruction format (works best with Mistral/Llama models)
        $systemPrompt = '';
        $userMessages = [];
        $assistantMessages = [];
        
        foreach ($messages as $msg) {
            if ($msg['role'] === 'system') {
                $systemPrompt = $msg['content'];
            } elseif ($msg['role'] === 'user') {
                $userMessages[] = $msg['content'];
            } elseif ($msg['role'] === 'assistant') {
                $assistantMessages[] = $msg['content'];
            }
        }
        
        // Build conversation in Mistral format
        $prompt = '';
        if ($systemPrompt) {
            $prompt = "<s>[INST] " . $systemPrompt . "\n\n";
        } else {
            $prompt = "<s>[INST] ";
        }
        
        // Add conversation history
        $conversationCount = max(count($userMessages), count($assistantMessages));
        for ($i = 0; $i < $conversationCount; $i++) {
            if (isset($userMessages[$i])) {
                if ($i > 0 || $systemPrompt) {
                    $prompt .= "\n\n";
                }
                $prompt .= $userMessages[$i] . " [/INST]";
            }
            if (isset($assistantMessages[$i])) {
                $prompt .= " " . $assistantMessages[$i] . " </s><s>[INST] ";
            }
        }
        
        // If no conversation history, just use the last user message
        if (empty($userMessages) && !empty($messages)) {
            foreach (array_reverse($messages) as $msg) {
                if ($msg['role'] === 'user') {
                    if ($systemPrompt) {
                        $prompt = "<s>[INST] " . $systemPrompt . "\n\n" . $msg['content'] . " [/INST]";
                    } else {
                        $prompt = "<s>[INST] " . $msg['content'] . " [/INST]";
                    }
                    break;
                }
            }
        }
        
        // Ensure prompt ends properly
        if (substr($prompt, -7) !== '[/INST]') {
            $prompt .= " [/INST]";
        }
        
        $postData = [
            'inputs' => $prompt,
            'parameters' => [
                'temperature' => $this->config['temperature'] ?? 0.7,
                'max_new_tokens' => $this->config['max_tokens'] ?? 500,
                'return_full_text' => false
            ]
        ];
        
        $headers = ['Content-Type: application/json'];
        if (!empty($this->config['api_key'])) {
            $headers[] = 'Authorization: Bearer ' . $this->config['api_key'];
        }
        
        $ch = curl_init($url);
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_POST => true,
            CURLOPT_HTTPHEADER => $headers,
            CURLOPT_POSTFIELDS => json_encode($postData),
            CURLOPT_TIMEOUT => 60, // Hugging Face can be slower, especially first request
            CURLOPT_SSL_VERIFYPEER => true
        ]);
        
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $error = curl_error($ch);
        curl_close($ch);
        
        if ($error) {
            throw new Exception("cURL Error: " . $error);
        }
        
        if ($httpCode === 503) {
            $errorData = json_decode($response, true);
            $estimatedTime = isset($errorData['estimated_time']) ? $errorData['estimated_time'] : 30;
            throw new Exception("Model is loading. Please wait " . round($estimatedTime) . " seconds and try again.");
        }
        
        if ($httpCode !== 200) {
            $errorData = json_decode($response, true);
            $errorMessage = isset($errorData['error']) ? $errorData['error'] : 'Unknown error';
            if (is_array($errorMessage)) {
                $errorMessage = json_encode($errorMessage);
            }
            throw new Exception("API Error ($httpCode): " . $errorMessage);
        }
        
        $decoded = json_decode($response, true);
        
        if (json_last_error() !== JSON_ERROR_NONE) {
            error_log("AI Chatbot: JSON decode error: " . json_last_error_msg());
            throw new Exception("Invalid JSON response from Hugging Face API");
        }
        
        if (isset($decoded[0]['generated_text'])) {
            $generatedText = trim($decoded[0]['generated_text']);
            if (strpos($generatedText, '[/INST]') !== false) {
                $parts = explode('[/INST]', $generatedText);
                if (count($parts) > 1) {
                    $generatedText = trim($parts[1]);
                }
            }
            return ['choices' => [['message' => ['content' => $generatedText]]]];
        }
        
        if (isset($decoded['generated_text'])) {
            $generatedText = trim($decoded['generated_text']);
            if (strpos($generatedText, '[/INST]') !== false) {
                $parts = explode('[/INST]', $generatedText);
                if (count($parts) > 1) {
                    $generatedText = trim($parts[1]);
                }
            }
            return ['choices' => [['message' => ['content' => $generatedText]]]];
        }
        
        if (isset($decoded['error'])) {
            $errorMsg = is_array($decoded['error']) ? json_encode($decoded['error']) : $decoded['error'];
            throw new Exception("Hugging Face API Error: " . $errorMsg);
        }
        
        error_log("AI Chatbot: Unexpected Hugging Face response format");
        throw new Exception("Unexpected response format from Hugging Face API");
    }
    
    /**
     * Call OpenAI API
     */
    private function callOpenAI($messages) {
        $apiUrl = $this->config['api_url'] ?? 'https://api.openai.com/v1/chat/completions';
        $ch = curl_init($apiUrl);
        
        $postData = [
            'model' => $this->config['model'],
            'messages' => $messages,
            'temperature' => $this->config['temperature'],
            'max_tokens' => $this->config['max_tokens']
        ];
        
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_POST => true,
            CURLOPT_HTTPHEADER => [
                'Content-Type: application/json',
                'Authorization: Bearer ' . $this->config['api_key']
            ],
            CURLOPT_POSTFIELDS => json_encode($postData),
            CURLOPT_TIMEOUT => 30,
            CURLOPT_SSL_VERIFYPEER => true
        ]);
        
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $error = curl_error($ch);
        curl_close($ch);
        
        if ($error) {
            error_log("AI Chatbot cURL Error: " . $error);
            throw new Exception("cURL Error: " . $error);
        }
        
        if ($httpCode !== 200) {
            $errorData = json_decode($response, true);
            $errorMessage = isset($errorData['error']['message']) ? $errorData['error']['message'] : 'Unknown error';
            $errorCode = isset($errorData['error']['code']) ? $errorData['error']['code'] : '';
            
            error_log("AI Chatbot API Error ($httpCode): " . $errorMessage);
            
            if (strpos(strtolower($errorMessage), 'billing') !== false || 
                strpos(strtolower($errorMessage), 'payment') !== false ||
                strpos(strtolower($errorMessage), 'insufficient_quota') !== false ||
                $errorCode === 'insufficient_quota' ||
                $httpCode === 429) {
                error_log("AI Chatbot: Billing/payment issue detected");
                throw new Exception("Billing Required: Please set up billing/payment on your OpenAI account at https://platform.openai.com/account/billing. Error: " . $errorMessage);
            }
            
            throw new Exception("API Error ($httpCode): " . $errorMessage);
        }
        
        $decodedResponse = json_decode($response, true);
        if (json_last_error() !== JSON_ERROR_NONE) {
            error_log("AI Chatbot JSON Decode Error: " . json_last_error_msg());
            throw new Exception("Invalid JSON response from API");
        }
        
        return $decodedResponse;
    }
    
    /**
     * Build system prompt with website knowledge
     */
    private function buildSystemPrompt($context) {
        $prompt = "You are an intelligent AI assistant for an event ticketing website called EGZLY. ";
        $prompt .= "Your role is to help users with event bookings, ticket information, refunds, and general support.\n\n";
        
        $prompt .= "## Website Knowledge:\n";
        $prompt .= $context['website_info'] . "\n\n";
        
        $prompt .= "## Policies & Rules:\n";
        $prompt .= $context['policies'] . "\n\n";
        
        if (!empty($context['user'])) {
            $prompt .= "## User Context:\n";
            $prompt .= "- User ID: " . ($context['user']['id'] ?? 'Guest') . "\n";
            $prompt .= "- Status: " . ($context['user']['logged_in'] ? 'Logged in' : 'Guest user') . "\n";
            if (!empty($context['user']['bookings'])) {
                // bookings is already a count (integer), not an array
                $bookingsCount = is_array($context['user']['bookings']) 
                    ? count($context['user']['bookings']) 
                    : $context['user']['bookings'];
                $prompt .= "- Active Bookings: " . $bookingsCount . "\n";
            }
            $prompt .= "\n";
        }
        
        if (!empty($context['events'])) {
            $prompt .= "## Current Events Data:\n";
            $prompt .= $context['events'] . "\n\n";
        }
        
        $prompt .= "## Instructions:\n";
        $prompt .= "1. Answer questions naturally and conversationally\n";
        $prompt .= "2. Use the provided context to give accurate, up-to-date information\n";
        $prompt .= "3. If you don't know something, say so and suggest contacting support\n";
        $prompt .= "4. Be helpful, friendly, and professional\n";
        $prompt .= "5. Always provide actionable information when possible\n";
        $prompt .= "6. Use emojis sparingly and appropriately\n";
        $prompt .= "7. Keep responses concise but informative\n";
        $prompt .= "8. If asked about events, use the real-time event data provided\n";
        $prompt .= "9. Reference specific policies when discussing refunds, cancellations, or terms\n";
        $prompt .= "10. Maintain conversation context from previous messages\n\n";
        
        $prompt .= "## Response Format:\n";
        $prompt .= "Respond naturally in plain text. Use line breaks for readability. ";
        $prompt .= "You can use markdown-style formatting (**, *, lists) but keep it simple.\n";
        
        return $prompt;
    }
    
    /**
     * Get website knowledge base
     */
    private function getWebsiteKnowledge() {
        return "EGZLY is an event ticketing platform that allows users to book tickets for various events including:
- Sports events (football, basketball, etc.)
- Entertainment events (concerts, comedy shows, movies)
- Theater performances
- Other special events

**Key Features:**
- Users can browse events by category (Sports, Entertainment)
- Multiple ticket categories per event (e.g., VIP, Premium, Regular, Fanpit, Golden Circle)
- Different seating types: Theatre, Stadium, and Standing
- Ticket customization available for $9.99 per ticket
- Secure checkout with multiple payment options
- User accounts for managing bookings
- Real-time ticket availability

**Booking Process:**
1. Browse events on homepage or events page
2. Select an event to view details
3. Choose ticket category and quantity
4. Select seats (for theatre/stadium) or quantity (for standing)
5. Proceed to checkout
6. Enter user information
7. Complete payment
8. Receive confirmation and tickets

**Ticket Customization:**
- Available after booking
- Cost: $9.99 per customized ticket
- Allows adding guest names to physical tickets
- Can customize subset of purchased tickets";
    }
    
    /**
     * Get policies and rules
     */
    private function getPolicies() {
        return "**Refund Policy:**
- Refunds available up to 48 hours before event start time
- No refunds within 48 hours of event
- Processing time: 5-7 business days after approval
- Contact support@event-booking.com for refund requests

**Ticket Policies:**
- Tickets are non-transferable unless customized
- Lost tickets: Contact support with booking reference
- Event cancellations: Full refund automatically processed
- Event postponements: Tickets remain valid for rescheduled date

**Booking Policies:**
- Minimum 1 ticket per booking
- Maximum tickets per booking: Check event details
- Reservations held for 15 minutes during checkout
- Payment must be completed within reservation time

**Privacy & Security:**
- User data is securely stored
- Payment information is encrypted
- Personal information used only for booking purposes";
    }
    
    /**
     * Get relevant events from database
     */
    private function getRelevantEvents($userMessage) {
        try {
            $messageLower = strtolower($userMessage);
            
            // Check for specific event names
            $eventKeywords = $this->extractEventKeywords($userMessage);
            
            if (!empty($eventKeywords)) {
                // Search for specific events
                $placeholders = str_repeat('?,', count($eventKeywords) - 1) . '?';
                $query = "
                    SELECT e.id, e.title, e.date, e.description, e.price, 
                           v.name as venue_name, v.city, v.address,
                           GROUP_CONCAT(DISTINCT etc.category_name SEPARATOR ', ') as categories
                    FROM events e
                    JOIN venues v ON e.venue_id = v.id
                    LEFT JOIN event_ticket_categories etc ON e.id = etc.event_id
                    WHERE e.status = 'active' 
                    AND e.date >= CURDATE()
                    AND (e.title LIKE CONCAT('%', ?, '%') OR e.description LIKE CONCAT('%', ?, '%'))
                    GROUP BY e.id
                    ORDER BY e.date ASC
                    LIMIT 5
                ";
                
                $stmt = $this->pdo->prepare($query);
                $params = [];
                foreach ($eventKeywords as $keyword) {
                    $params[] = $keyword;
                    $params[] = $keyword;
                }
                $stmt->execute($params);
                $events = $stmt->fetchAll();
            } else {
                // Get upcoming events
                $query = "
                    SELECT e.id, e.title, e.date, e.description, e.price,
                           v.name as venue_name, v.city,
                           GROUP_CONCAT(DISTINCT etc.category_name SEPARATOR ', ') as categories
                    FROM events e
                    JOIN venues v ON e.venue_id = v.id
                    LEFT JOIN event_ticket_categories etc ON e.id = etc.event_id
                    WHERE e.status = 'active' 
                    AND e.date >= CURDATE()
                    GROUP BY e.id
                    ORDER BY e.date ASC
                    LIMIT 10
                ";
                
                $stmt = $this->pdo->prepare($query);
                $stmt->execute();
                $events = $stmt->fetchAll();
            }
            
            if (empty($events)) {
                return "No upcoming events found at this time.";
            }
            
            $output = "Upcoming Events:\n";
            foreach ($events as $event) {
                $date = date('M d, Y h:i A', strtotime($event['date']));
                $output .= "\n**{$event['title']}**\n";
                $output .= "- Date: {$date}\n";
                $output .= "- Venue: {$event['venue_name']}, {$event['city']}\n";
                $output .= "- Price: From \${$event['price']}\n";
                if ($event['categories']) {
                    $output .= "- Categories: {$event['categories']}\n";
                }
                $output .= "- ID: {$event['id']}\n";
            }
            
            return $output;
            
        } catch (Exception $e) {
            error_log("Error fetching events for AI context: " . $e->getMessage());
            return "Event data temporarily unavailable.";
        }
    }
    
    /**
     * Extract event keywords from message
     */
    private function extractEventKeywords($message) {
        $messageLower = strtolower($message);
        $keywords = [];
        
        // Common event names/terms
        $eventTerms = ['tul8te', 'marwan pablo', 'ahly', 'avengers', 'helmy', 'comedy', 
                      'basketball', 'football', 'concert', 'sports', 'entertainment'];
        
        foreach ($eventTerms as $term) {
            if (strpos($messageLower, $term) !== false) {
                $keywords[] = $term;
            }
        }
        
        return $keywords;
    }
    
    /**
     * Check if message is event-related
     */
    private function isEventRelated($message) {
        $messageLower = strtolower($message);
        $eventKeywords = ['event', 'events', 'upcoming', 'shows', 'concerts', 'sports', 
                         'movies', 'theater', 'what\'s on', 'available', 'happening', 
                         'tickets', 'booking', 'book'];
        
        foreach ($eventKeywords as $keyword) {
            if (strpos($messageLower, $keyword) !== false) {
                return true;
            }
        }
        
        return false;
    }
    
    /**
     * Get relevant training data for context
     */
    private function getRelevantTrainingData($message) {
        try {
            $messageLower = strtolower($message);
            $words = explode(' ', preg_replace('/[^a-z0-9\s]/', '', $messageLower));
            $words = array_filter($words, function($w) { return strlen($w) > 2; });
            
            if (empty($words)) {
                return '';
            }
            
            $placeholders = [];
            $params = [];
            
            foreach ($words as $word) {
                $placeholders[] = "user_input LIKE ? OR keywords LIKE ?";
                $searchTerm = "%$word%";
                $params[] = $searchTerm;
                $params[] = $searchTerm;
            }
            
            $whereClause = implode(' OR ', $placeholders);
            $query = "
                SELECT user_input, correct_response, intent 
                FROM chatbot_training 
                WHERE is_active = 1 AND ($whereClause)
                ORDER BY use_count DESC 
                LIMIT 3
            ";
            
            $stmt = $this->pdo->prepare($query);
            $stmt->execute($params);
            $results = $stmt->fetchAll();
            
            if (empty($results)) {
                return '';
            }
            
            $output = "Relevant FAQ/Training Data:\n";
            foreach ($results as $row) {
                $output .= "Q: {$row['user_input']}\n";
                $output .= "A: {$row['correct_response']}\n\n";
            }
            
            return $output;
            
        } catch (Exception $e) {
            return '';
        }
    }
    
    /**
     * Load user context
     */
    private function loadUserContext() {
        if (isset($_SESSION['user_id'])) {
            $this->userContext = [
                'id' => $_SESSION['user_id'],
                'logged_in' => true
            ];
            
            // Load user bookings if needed
            try {
                $stmt = $this->pdo->prepare("
                    SELECT COUNT(*) as booking_count 
                    FROM bookings 
                    WHERE user_id = ? AND status = 'confirmed'
                ");
                $stmt->execute([$_SESSION['user_id']]);
                $result = $stmt->fetch();
                $this->userContext['bookings'] = $result['booking_count'] ?? 0;
            } catch (Exception $e) {
                // Ignore errors
            }
        } else {
            $this->userContext = ['logged_in' => false];
        }
    }
    
    /**
     * Fallback to training data if AI fails
     */
    private function getFallbackResponse($message) {
        try {
            $messageLower = strtolower($message);
            $words = explode(' ', preg_replace('/[^a-z0-9\s]/', '', $messageLower));
            
            $placeholders = [];
            $params = [];
            
            foreach ($words as $word) {
                if (strlen($word) > 2) {
                    $placeholders[] = "user_input LIKE ? OR keywords LIKE ?";
                    $searchTerm = "%$word%";
                    $params[] = $searchTerm;
                    $params[] = $searchTerm;
                }
            }
            
            if (empty($placeholders)) {
                return null;
            }
            
            $whereClause = implode(' OR ', $placeholders);
            $query = "
                SELECT correct_response FROM chatbot_training 
                WHERE is_active = 1 AND ($whereClause)
                ORDER BY use_count DESC, LENGTH(user_input) ASC 
                LIMIT 1
            ";
            
            $stmt = $this->pdo->prepare($query);
            $stmt->execute($params);
            $row = $stmt->fetch();
            
            if ($row) {
                return $row['correct_response'];
            }
            
        } catch (Exception $e) {
            error_log("Fallback response error: " . $e->getMessage());
        }
        
        return null;
    }
    
    /**
     * Clear conversation history
     */
    public function clearHistory() {
        $this->conversationHistory = [];
    }
    
    /**
     * Get conversation history
     */
    public function getHistory() {
        return $this->conversationHistory;
    }
}


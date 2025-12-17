<?php
// API for ticket reservations (15-minute holds)
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, DELETE, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

require_once __DIR__ . '/../../config/db_connect.php';
require_once __DIR__ . '/../../app/models/TicketReservation.php';
require_once __DIR__ . '/../../app/models/EventTicketCategory.php';
require_once __DIR__ . '/../../database/session_init.php';

$response = ['success' => false, 'message' => 'Unknown error'];
$statusCode = 200;

try {
    $reservation = new TicketReservation($pdo);
    $ticketCategory = new EventTicketCategory($pdo);
    
    $action = $_GET['action'] ?? $_POST['action'] ?? '';
    $method = $_SERVER['REQUEST_METHOD'];
    
    // Clean up expired reservations
    $reservation->expireOldReservations();
    
    if ($method === 'POST' && $action === 'reserve') {
        // Reserve tickets for 15 minutes
        $eventId = $_POST['event_id'] ?? 0;
        $categoryName = $_POST['category_name'] ?? '';
        $quantity = (int)($_POST['quantity'] ?? 0);
        
        if (!$eventId || !$categoryName || $quantity <= 0) {
            $response = ['success' => false, 'message' => 'Invalid parameters'];
            $statusCode = 400;
        } else {
            // Check available tickets
            // Debug: Log what we're looking for
            error_log("Looking for category: event_id=$eventId, category_name='$categoryName'");
            
            $ticketCategory->getByEventAndCategory($eventId, $categoryName);
            if (!$ticketCategory->id) {
                // Try to get all categories for this event to see what exists
                $stmt = $ticketCategory->getByEventId($eventId);
                $availableCategories = [];
                while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                    $availableCategories[] = $row['category_name'];
                }
                error_log("Available categories for event $eventId: " . implode(', ', $availableCategories));
                
                $response = [
                    'success' => false, 
                    'message' => "Ticket category '$categoryName' not found. Available: " . implode(', ', $availableCategories)
                ];
                $statusCode = 404;
            } else {
                // Get reserved count
                $reservedCount = $reservation->getByEventAndCategory($eventId, $categoryName);
                $available = $ticketCategory->available_tickets - $reservedCount;
                
                if ($quantity > $available) {
                    $response = [
                        'success' => false, 
                        'message' => "Only {$available} tickets available in this category"
                    ];
                    $statusCode = 400;
                } else {
                    // Create reservation
                    $reservation->event_id = $eventId;
                    $reservation->category_name = $categoryName;
                    $reservation->quantity = $quantity;
                    $reservation->user_id = isLoggedIn() ? $_SESSION['user_id'] : null;
                    $reservation->session_id = session_id();
                    
                    // Set expiry to 15 minutes from now
                    $expiresAt = date('Y-m-d H:i:s', strtotime('+15 minutes'));
                    $reservation->expires_at = $expiresAt;
                    
                    if ($reservation->create()) {
                        $response = [
                            'success' => true,
                            'message' => 'Tickets reserved for 15 minutes',
                            'reservation_id' => $reservation->id,
                            'expires_at' => $expiresAt
                        ];
                        $statusCode = 201;
                    } else {
                        $response = ['success' => false, 'message' => 'Failed to create reservation'];
                        $statusCode = 500;
                    }
                }
            }
        }
    }
    elseif ($method === 'GET' && $action === 'getReservations') {
        // Get current reservations for session
        $sessionId = session_id();
        $stmt = $reservation->getBySession($sessionId);
        
        $reservations = [];
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $reservations[] = $row;
        }
        
        $response = ['success' => true, 'reservations' => $reservations];
    }
    elseif ($method === 'GET' && $action === 'getReservation') {
        // Get a single reservation by ID
        $reservationId = $_GET['id'] ?? 0;
        
        if ($reservationId) {
            $query = "SELECT * FROM ticket_reservations WHERE id = ?";
            $stmt = $pdo->prepare($query);
            $stmt->execute([$reservationId]);
            $reservationData = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if ($reservationData) {
                $response = ['success' => true, 'reservation' => $reservationData];
            } else {
                $response = ['success' => false, 'message' => 'Reservation not found'];
            }
        } else {
            $response = ['success' => false, 'message' => 'Reservation ID required'];
        }
    }
    elseif ($method === 'POST' && $action === 'confirm') {
        // Confirm reservation (convert to booking)
        $reservationId = $_POST['reservation_id'] ?? 0;
        
        if ($reservation->confirmReservation($reservationId)) {
            // Update available tickets
            $reservation->id = $reservationId;
            // Get reservation details to update category
            $query = "SELECT event_id, category_name, quantity FROM ticket_reservations WHERE id = ?";
            $stmt = $pdo->prepare($query);
            $stmt->execute([$reservationId]);
            $resData = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if ($resData) {
                $ticketCategory->getByEventAndCategory($resData['event_id'], $resData['category_name']);
                $ticketCategory->updateAvailableTickets($resData['quantity']);
            }
            
            $response = ['success' => true, 'message' => 'Reservation confirmed'];
        } else {
            $response = ['success' => false, 'message' => 'Failed to confirm reservation'];
            $statusCode = 400;
        }
    }
    elseif ($method === 'DELETE' || ($method === 'POST' && $action === 'release')) {
        // Release reservation
        $sessionId = session_id();
        if ($reservation->deleteBySession($sessionId)) {
            $response = ['success' => true, 'message' => 'Reservations released'];
        } else {
            $response = ['success' => false, 'message' => 'Failed to release reservations'];
            $statusCode = 400;
        }
    }
    elseif ($method === 'GET' && $action === 'getAvailability') {
        // Get availability for event categories
        $eventId = $_GET['event_id'] ?? 0;
        
        if (!$eventId) {
            $response = ['success' => false, 'message' => 'Event ID required'];
            $statusCode = 400;
        } else {
            $stmt = $ticketCategory->getByEventId($eventId);
            $categories = [];
            
            while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                $reservedCount = $reservation->getByEventAndCategory($eventId, $row['category_name']);
                $categories[] = [
                    'category_name' => $row['category_name'],
                    'total_tickets' => $row['total_tickets'],
                    'available_tickets' => $row['available_tickets'],
                    'reserved_tickets' => $reservedCount,
                    'actually_available' => $row['available_tickets'] - $reservedCount,
                    'price' => $row['price']
                ];
            }
            
            $response = ['success' => true, 'categories' => $categories];
        }
    }
    
} catch (Exception $e) {
    error_log("Ticket reservation error: " . $e->getMessage());
    $response = ['success' => false, 'message' => 'Server error: ' . $e->getMessage()];
    $statusCode = 500;
}

http_response_code($statusCode);
echo json_encode($response);
?>


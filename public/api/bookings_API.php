<?php
// bookings_API.php - API for booking management
// Suppress error display and use output buffering
error_reporting(E_ALL);
ini_set('display_errors', 0);
ini_set('log_errors', 1);

// Start output buffering early
if (!ob_get_level()) {
    ob_start();
}

// Log all errors
set_error_handler(function($errno, $errstr, $errfile, $errline) {
    error_log("PHP Error [$errno]: $errstr in $errfile on line $errline");
    return false; // Let PHP handle it normally
});

// Catch fatal errors
register_shutdown_function(function() {
    $error = error_get_last();
    if ($error !== NULL && in_array($error['type'], [E_ERROR, E_PARSE, E_CORE_ERROR, E_COMPILE_ERROR])) {
        ob_clean();
        http_response_code(500);
        echo json_encode([
            'success' => false,
            'message' => 'Fatal error: ' . $error['message'],
            'file' => $error['file'],
            'line' => $error['line']
        ]);
        exit();
    }
});

try {
    // Normalize path for Windows compatibility
    // __DIR__ is: C:\xampp\htdocs\event-booking-website\public\api
    // We need to go up 2 levels to get to: C:\xampp\htdocs\event-booking-website
    $currentDir = __DIR__; // public/api
    
    // Use dirname() twice - more reliable than realpath with ../
    $baseDir = dirname(dirname($currentDir));
    
    // Verify the base directory exists and contains expected files
    if (!is_dir($baseDir)) {
        throw new Exception("Base directory does not exist: " . $baseDir . " | Current dir: " . $currentDir);
    }
    
    $dbConnectPath = $baseDir . DIRECTORY_SEPARATOR . 'config' . DIRECTORY_SEPARATOR . 'db_connect.php';
    $bookingsModelPath = $baseDir . DIRECTORY_SEPARATOR . 'app' . DIRECTORY_SEPARATOR . 'models' . DIRECTORY_SEPARATOR . 'BookingsModel.php';
    
    // Normalize paths
    $dbConnectPath = str_replace(['/', '\\'], DIRECTORY_SEPARATOR, $dbConnectPath);
    $bookingsModelPath = str_replace(['/', '\\'], DIRECTORY_SEPARATOR, $bookingsModelPath);
    
    // Verify files exist
    if (!file_exists($dbConnectPath)) {
        // Try to find it with realpath
        $resolved = realpath($dbConnectPath);
        if ($resolved && file_exists($resolved)) {
            $dbConnectPath = $resolved;
        } else {
            throw new Exception("db_connect.php not found. Tried: " . $dbConnectPath . " | Base dir: " . $baseDir . " | Current: " . $currentDir);
        }
    }
    
    if (!file_exists($bookingsModelPath)) {
        $resolved = realpath($bookingsModelPath);
        if ($resolved && file_exists($resolved)) {
            $bookingsModelPath = $resolved;
        } else {
            throw new Exception("BookingsModel.php not found. Tried: " . $bookingsModelPath . " | Base dir: " . $baseDir);
        }
    }
    
    require_once $dbConnectPath;
    require_once $bookingsModelPath;
} catch (Exception $e) {
    ob_clean();
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Failed to load required files: ' . $e->getMessage()
    ]);
    exit();
} catch (Error $e) {
    ob_clean();
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Fatal error loading files: ' . $e->getMessage()
    ]);
    exit();
}

// Clear any output that might have been generated
ob_clean();

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

// Handle preflight requests
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

// Create BookingsModel instance
try {
    $database = new Database();
    $db = $database->getConnection();
    
    if (!$db) {
        throw new Exception('Database connection returned null');
    }
    
    $bookingsModel = new BookingsModel($db);
} catch (Exception $e) {
    ob_clean();
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Database connection error: ' . $e->getMessage(),
        'trace' => $e->getTraceAsString()
    ]);
    exit();
} catch (Error $e) {
    ob_clean();
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Fatal database error: ' . $e->getMessage(),
        'file' => $e->getFile(),
        'line' => $e->getLine()
    ]);
    exit();
}

$action = $_GET['action'] ?? '';

try {
    switch ($action) {
        case 'getAll':
            $page = $_GET['page'] ?? 1;
            $limit = $_GET['limit'] ?? 10;
            
            // Collect filters
            $filters = [
                'status' => $_GET['status'] ?? '',
                'payment_status' => $_GET['payment_status'] ?? '',
                'search' => $_GET['search'] ?? ''
            ];
            
            $result = $bookingsModel->getAllBookings($page, $limit, $filters);
            
            echo json_encode([
                'success' => true,
                'data' => $result['bookings'],
                'pagination' => [
                    'total' => $result['total'],
                    'page' => $result['page'],
                    'limit' => $result['limit'],
                    'pages' => $result['pages']
                ]
            ]);
            break;
            
        case 'getOne':
            $id = $_GET['id'] ?? 0;
            if (!$id) {
                throw new Exception('Booking ID is required');
            }
            
            $booking = $bookingsModel->getBookingById($id);
            if ($booking) {
                echo json_encode([
                    'success' => true,
                    'data' => $booking
                ]);
            } else {
                echo json_encode([
                    'success' => false,
                    'message' => 'Booking not found'
                ]);
            }
            break;
            
        case 'getStats':
            $stats = $bookingsModel->getBookingStats();
            echo json_encode([
                'success' => true,
                'stats' => $stats
            ]);
            break;
            
        case 'updateStatus':
            $data = json_decode(file_get_contents('php://input'), true);
            $id = $data['id'] ?? 0;
            $status = $data['status'] ?? '';
            
            if (!$id || !$status) {
                throw new Exception('ID and status are required');
            }
            
            if ($bookingsModel->updateBookingStatus($id, $status)) {
                echo json_encode([
                    'success' => true,
                    'message' => 'Booking status updated successfully'
                ]);
            } else {
                echo json_encode([
                    'success' => false,
                    'message' => 'Failed to update booking status'
                ]);
            }
            break;
            
        case 'updatePaymentStatus':
            $data = json_decode(file_get_contents('php://input'), true);
            $id = $data['id'] ?? 0;
            $payment_status = $data['payment_status'] ?? '';
            
            if (!$id || !$payment_status) {
                throw new Exception('ID and payment status are required');
            }
            
            if ($bookingsModel->updatePaymentStatus($id, $payment_status)) {
                echo json_encode([
                    'success' => true,
                    'message' => 'Payment status updated successfully'
                ]);
            } else {
                echo json_encode([
                    'success' => false,
                    'message' => 'Failed to update payment status'
                ]);
            }
            break;
            
        case 'delete':
            $id = $_GET['id'] ?? 0;
            if (!$id) {
                throw new Exception('Booking ID is required');
            }
            
            if ($bookingsModel->deleteBooking($id)) {
                echo json_encode([
                    'success' => true,
                    'message' => 'Booking deleted successfully'
                ]);
            } else {
                echo json_encode([
                    'success' => false,
                    'message' => 'Failed to delete booking'
                ]);
            }
            break;
            
        case 'cancel':
            try {
                $data = json_decode(file_get_contents('php://input'), true);
                $id = $data['id'] ?? $_GET['id'] ?? 0;
                
                if (!$id) {
                    throw new Exception('Booking ID is required');
                }
                
                $result = $bookingsModel->cancelBooking($id);
                echo json_encode($result);
            } catch (Exception $e) {
                error_log("Cancel booking error: " . $e->getMessage());
                error_log("Cancel booking trace: " . $e->getTraceAsString());
                http_response_code(500);
                echo json_encode([
                    'success' => false,
                    'message' => 'Error cancelling booking: ' . $e->getMessage()
                ]);
            }
            break;
            
        case 'approveCashPayment':
            try {
                $data = json_decode(file_get_contents('php://input'), true);
                $id = $data['id'] ?? $_GET['id'] ?? 0;
                
                if (!$id) {
                    throw new Exception('Booking ID is required');
                }
                
                $result = $bookingsModel->approveCashPayment($id);
                echo json_encode($result);
            } catch (Exception $e) {
                error_log("Approve cash payment error: " . $e->getMessage());
                error_log("Approve cash payment trace: " . $e->getTraceAsString());
                http_response_code(500);
                echo json_encode([
                    'success' => false,
                    'message' => 'Error approving payment: ' . $e->getMessage()
                ]);
            }
            break;
            
        case 'recent':
            $limit = $_GET['limit'] ?? 10;
            $bookings = $bookingsModel->getRecentBookings($limit);
            echo json_encode([
                'success' => true,
                'data' => $bookings
            ]);
            break;
            
        case 'create':
            // Normalize path for Windows compatibility
            // __DIR__ is: public/api, go up 2 levels to get base directory
            $currentDir = __DIR__;
            $baseDir = dirname(dirname($currentDir));
            
            $sessionInitPath = $baseDir . DIRECTORY_SEPARATOR . 'database' . DIRECTORY_SEPARATOR . 'session_init.php';
            $sessionInitPath = str_replace(['/', '\\'], DIRECTORY_SEPARATOR, $sessionInitPath);
            
            if (!file_exists($sessionInitPath)) {
                // Try with realpath
                $resolved = realpath($sessionInitPath);
                if ($resolved && file_exists($resolved)) {
                    $sessionInitPath = $resolved;
                } else {
                    throw new Exception('session_init.php not found. Tried: ' . $sessionInitPath . ' | Base dir: ' . $baseDir . ' | Current: ' . $currentDir);
                }
            }
            require_once $sessionInitPath;
            
            if (!isset($_SESSION['user_id'])) {
                throw new Exception('User must be logged in to create a booking');
            }
            
            $input = file_get_contents('php://input');
            $data = json_decode($input, true);
            
            if (!$data) {
                $jsonError = json_last_error_msg();
                error_log("JSON decode error: " . $jsonError . " | Input: " . substr($input, 0, 500));
                throw new Exception('Invalid request data: ' . $jsonError);
            }
            
            // Validate required fields
            $required = ['event_id', 'ticket_count', 'customer_first_name', 'customer_last_name', 'customer_email'];
            $missingFields = [];
            foreach ($required as $field) {
                if (empty($data[$field])) {
                    $missingFields[] = $field;
                }
            }
            
            if (!empty($missingFields)) {
                throw new Exception("Required fields missing: " . implode(', ', $missingFields));
            }
            
            // Validate event_id is a valid integer
            $data['event_id'] = intval($data['event_id']);
            if ($data['event_id'] <= 0) {
                throw new Exception("Invalid event_id: " . $data['event_id']);
            }
            
            // Validate ticket_count
            $data['ticket_count'] = intval($data['ticket_count']);
            if ($data['ticket_count'] <= 0) {
                throw new Exception("Invalid ticket_count: " . $data['ticket_count']);
            }
            
            // Add user_id from session
            if (!isset($_SESSION['user_id'])) {
                throw new Exception('User session not found. Please log in again.');
            }
            $data['user_id'] = intval($_SESSION['user_id']);
            
            // Log booking attempt
            $apiStartTime = microtime(true);
            error_log("Creating booking - User: " . $data['user_id'] . ", Event: " . $data['event_id'] . ", Tickets: " . $data['ticket_count']);
            
            try {
                $result = $bookingsModel->createBooking($data);
                $apiTime = microtime(true) - $apiStartTime;
                error_log("DEBUG: createBooking API call took " . round($apiTime, 3) . " seconds");
                
                if ($result['success']) {
                    error_log("Booking created successfully - ID: " . $result['booking_id'] . ", Code: " . $result['booking_code']);
                    
                    // Send response immediately
                    ob_clean();
                    echo json_encode([
                        'success' => true,
                        'message' => 'Booking created successfully',
                        'booking_id' => $result['booking_id'],
                        'booking_code' => $result['booking_code']
                    ]);
                    
                    // Flush output to send response to client immediately
                    if (function_exists('fastcgi_finish_request')) {
                        fastcgi_finish_request();
                    } else {
                        // For non-FastCGI environments, flush output
                        if (ob_get_level()) {
                            ob_end_flush();
                        }
                        flush();
                    }
                } else {
                    throw new Exception('Failed to create booking - no success flag returned');
                }
            } catch (Exception $bookingError) {
                error_log("Booking creation error: " . $bookingError->getMessage());
                error_log("Booking data: " . json_encode([
                    'event_id' => $data['event_id'],
                    'ticket_count' => $data['ticket_count'],
                    'categories_count' => count($data['ticket_categories'] ?? []),
                    'seats_count' => count($data['booked_seats'] ?? [])
                ]));
                error_log("Stack trace: " . $bookingError->getTraceAsString());
                throw $bookingError;
            } catch (Error $bookingError) {
                error_log("Booking creation fatal error: " . $bookingError->getMessage());
                error_log("File: " . $bookingError->getFile() . " Line: " . $bookingError->getLine());
                throw new Exception("Fatal error creating booking: " . $bookingError->getMessage());
            }
            break;
            
        case 'getBookedSeats':
            $eventId = $_GET['event_id'] ?? 0;
            if (!$eventId) {
                throw new Exception('Event ID is required');
            }
            
            $seats = $bookingsModel->getBookedSeats($eventId);
            echo json_encode([
                'success' => true,
                'seats' => $seats
            ]);
            break;
            
        default:
            echo json_encode([
                'success' => false,
                'message' => 'Invalid action'
            ]);
    }
} catch (PDOException $e) {
    ob_clean();
    http_response_code(500);
    $errorMsg = "Database error: " . $e->getMessage();
    error_log("Bookings API PDO Error: " . $e->getMessage() . " | Code: " . $e->getCode() . " | File: " . $e->getFile() . " | Line: " . $e->getLine());
    echo json_encode([
        'success' => false,
        'message' => $errorMsg,
        'error_type' => 'PDOException',
        'error_code' => $e->getCode()
    ]);
} catch (Exception $e) {
    ob_clean();
    http_response_code(500);
    $errorMsg = $e->getMessage();
    error_log("Bookings API Error: " . $e->getMessage() . " | File: " . $e->getFile() . " | Line: " . $e->getLine() . " | Trace: " . $e->getTraceAsString());
    echo json_encode([
        'success' => false,
        'message' => $errorMsg,
        'error_type' => 'Exception',
        'file' => $e->getFile(),
        'line' => $e->getLine()
    ]);
} catch (Error $e) {
    ob_clean();
    http_response_code(500);
    $errorMsg = 'Fatal error: ' . $e->getMessage();
    error_log("Bookings API Fatal Error: " . $e->getMessage() . " | File: " . $e->getFile() . " | Line: " . $e->getLine() . " | Trace: " . $e->getTraceAsString());
    echo json_encode([
        'success' => false,
        'message' => $errorMsg,
        'error_type' => 'FatalError',
        'file' => $e->getFile(),
        'line' => $e->getLine()
    ]);
} catch (Throwable $e) {
    ob_clean();
    http_response_code(500);
    $errorMsg = 'Unexpected error: ' . $e->getMessage();
    error_log("Bookings API Throwable: " . $e->getMessage() . " | File: " . $e->getFile() . " | Line: " . $e->getLine());
    echo json_encode([
        'success' => false,
        'message' => $errorMsg,
        'error_type' => 'Throwable'
    ]);
}

if (ob_get_level() > 0) {
    ob_end_flush();
}
?>
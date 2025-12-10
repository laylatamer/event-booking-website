<?php
// /public/api/admin_events_API.php - Admin Events CRUD API
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

error_reporting(E_ALL);
ini_set('display_errors', 1);

// Get absolute paths
$currentDir = __DIR__;
$projectRoot = dirname(dirname($currentDir));

// Include database configuration
$configPath = $projectRoot . '/config/db_connect.php';
if (!file_exists($configPath)) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Database config not found']);
    exit;
}

require_once $configPath;

// Include AdminController
$controllerPath = $projectRoot . '/app/controllers/AdminController.php';
if (!file_exists($controllerPath)) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'AdminController not found']);
    exit;
}

require_once $controllerPath;

try {
    $database = new Database();
    $db = $database->getConnection();
    
    $adminController = new AdminController($db);
    
    $action = $_GET['action'] ?? '';
    $method = $_SERVER['REQUEST_METHOD'];
    
    switch ($method) {
        case 'GET':
            handleGetRequest($adminController, $action);
            break;
            
        case 'POST':
            handlePostRequest($adminController, $action);
            break;
            
        case 'PUT':
            handlePutRequest($adminController, $action);
            break;
            
        case 'DELETE':
            handleDeleteRequest($adminController, $action);
            break;
            
        default:
            http_response_code(405);
            echo json_encode(['success' => false, 'message' => 'Method not allowed']);
    }
    
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Server error: ' . $e->getMessage()
    ]);
}

function handleGetRequest($adminController, $action) {
    switch ($action) {
        case 'getEvent':
            $id = $_GET['id'] ?? 0;
            if ($id) {
                try {
                    $event = $adminController->getEvent($id);
                    if ($event) {
                        echo json_encode(['success' => true, 'event' => $event]);
                    } else {
                        echo json_encode(['success' => false, 'message' => 'Event not found']);
                    }
                } catch (Exception $e) {
                    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
                }
            } else {
                echo json_encode(['success' => false, 'message' => 'Event ID required']);
            }
            break;
            
        case 'getAll':
            try {
                $events = $adminController->getAllEvents();
                echo json_encode([
                    'success' => true,
                    'events' => $events,
                    'count' => count($events)
                ]);
            } catch (Exception $e) {
                echo json_encode(['success' => false, 'message' => $e->getMessage()]);
            }
            break;
            
        case 'getSubcategories':
            $main_category_id = $_GET['main_category_id'] ?? 0;
            if ($main_category_id) {
                try {
                    $subcategories = $adminController->getSubcategoriesByMainCategory($main_category_id);
                    echo json_encode([
                        'success' => true,
                        'subcategories' => $subcategories
                    ]);
                } catch (Exception $e) {
                    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
                }
            } else {
                echo json_encode(['success' => false, 'message' => 'Main category ID required']);
            }
            break;
            
        default:
            echo json_encode(['success' => false, 'message' => 'Invalid action']);
    }
}

function handlePostRequest($adminController, $action) {
    switch ($action) {
        case 'addEvent':
            try {
                $eventData = [
                    'title' => $_POST['title'] ?? '',
                    'description' => $_POST['description'] ?? '',
                    'subcategory_id' => $_POST['subcategory_id'] ?? 0,
                    'venue_id' => $_POST['venue_id'] ?? 0,
                    'date' => $_POST['date'] ?? '',
                    'end_date' => $_POST['end_date'] ?? null,
                    'price' => $_POST['price'] ?? 0,
                    'discounted_price' => $_POST['discounted_price'] ?? null,
                    'image_url' => $_POST['image_url'] ?? '',
                    'gallery_images' => !empty($_POST['gallery_images']) ? json_decode($_POST['gallery_images'], true) : [],
                    'total_tickets' => $_POST['total_tickets'] ?? 0,
                    'available_tickets' => $_POST['available_tickets'] ?? $_POST['total_tickets'] ?? 0,
                    'min_tickets_per_booking' => $_POST['min_tickets_per_booking'] ?? 1,
                    'max_tickets_per_booking' => $_POST['max_tickets_per_booking'] ?? 10,
                    'terms_conditions' => $_POST['terms_conditions'] ?? '',
                    'additional_info' => !empty($_POST['additional_info']) ? json_decode($_POST['additional_info'], true) : [],
                    'status' => $_POST['status'] ?? 'draft'
                ];
                
                // Validate required fields
                $required = ['title', 'description', 'subcategory_id', 'venue_id', 'date', 'price', 'total_tickets'];
                foreach ($required as $field) {
                    if (empty($eventData[$field])) {
                        echo json_encode(['success' => false, 'message' => "Missing required field: $field"]);
                        return;
                    }
                }
                
                // Check if venue exists and is active
                $venue = $adminController->getVenue($eventData['venue_id']);
                if (!$venue || ($venue['status'] ?? '') !== 'active') {
                    echo json_encode(['success' => false, 'message' => 'Selected venue is not available']);
                    return;
                }
                
                // Check if subcategory exists
                $subcategory = $adminController->getSubcategory($eventData['subcategory_id']);
                if (!$subcategory) {
                    echo json_encode(['success' => false, 'message' => 'Selected subcategory does not exist']);
                    return;
                }
                
                // Create event
                $result = $adminController->createEvent($eventData);
                
                if ($result) {
                    echo json_encode([
                        'success' => true,
                        'message' => 'Event created successfully',
                        'eventId' => $result
                    ]);
                } else {
                    echo json_encode(['success' => false, 'message' => 'Failed to create event']);
                }
                
            } catch (Exception $e) {
                echo json_encode(['success' => false, 'message' => $e->getMessage()]);
            }
            break;
            
        default:
            echo json_encode(['success' => false, 'message' => 'Invalid action']);
    }
}

function handlePutRequest($adminController, $action) {
    if ($action === 'updateEvent') {
        try {
            $json = file_get_contents('php://input');
            $data = json_decode($json, true);
            
            if (json_last_error() !== JSON_ERROR_NONE) {
                throw new Exception('Invalid JSON data');
            }
            
            $eventId = $data['id'] ?? 0;
            if (!$eventId) {
                echo json_encode(['success' => false, 'message' => 'Event ID required']);
                return;
            }
            
            // Update event
            $result = $adminController->updateEvent($eventId, $data);
            
            if ($result) {
                echo json_encode([
                    'success' => true,
                    'message' => 'Event updated successfully'
                ]);
            } else {
                echo json_encode(['success' => false, 'message' => 'Failed to update event']);
            }
            
        } catch (Exception $e) {
            echo json_encode(['success' => false, 'message' => $e->getMessage()]);
        }
    }
}

function handleDeleteRequest($adminController, $action) {
    if ($action === 'deleteEvent') {
        try {
            $json = file_get_contents('php://input');
            $data = json_decode($json, true);
            
            if (json_last_error() !== JSON_ERROR_NONE) {
                throw new Exception('Invalid JSON data');
            }
            
            $eventId = $data['id'] ?? 0;
            if (!$eventId) {
                echo json_encode(['success' => false, 'message' => 'Event ID required']);
                return;
            }
            
            // Delete event
            $result = $adminController->deleteEvent($eventId);
            
            if ($result) {
                echo json_encode([
                    'success' => true,
                    'message' => 'Event deleted successfully'
                ]);
            } else {
                echo json_encode(['success' => false, 'message' => 'Failed to delete event']);
            }
            
        } catch (Exception $e) {
            echo json_encode(['success' => false, 'message' => $e->getMessage()]);
        }
    }
}
?>
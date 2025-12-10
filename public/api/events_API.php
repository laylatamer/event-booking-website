<?php
// /event-booking-website/public/api/events_API.php
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

// Include EventController
$controllerPath = $projectRoot . '/app/controllers/EventController.php';
if (!file_exists($controllerPath)) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'EventController not found']);
    exit;
}

require_once $controllerPath;

try {
    $database = new Database();
    $db = $database->getConnection();
    
    $eventController = new EventController($db);
    
    $action = $_GET['action'] ?? '';
    
    switch ($_SERVER['REQUEST_METHOD']) {
        case 'GET':
            handleGetRequest($eventController, $action);
            break;
            
        case 'POST':
            handlePostRequest($eventController, $action);
            break;
            
        case 'PUT':
            handlePutRequest($eventController, $action);
            break;
            
        case 'DELETE':
            handleDeleteRequest($eventController, $action);
            break;
            
        default:
            echo json_encode(['success' => false, 'message' => 'Invalid method']);
    }
    
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Server error: ' . $e->getMessage()
    ]);
}

function handleGetRequest($controller, $action) {
    switch ($action) {
        case 'getAll':
            try {
                $events = $controller->getAllEvents();
                echo json_encode([
                    'success' => true,
                    'events' => $events,
                    'count' => count($events)
                ]);
            } catch (Exception $e) {
                echo json_encode(['success' => false, 'message' => $e->getMessage()]);
            }
            break;
            
        case 'getOne':
            $id = $_GET['id'] ?? 0;
            if ($id) {
                try {
                    $event = $controller->getEventById($id);
                    if ($event) {
                        echo json_encode(['success' => true, 'event' => $event]);
                    } else {
                        echo json_encode(['success' => false, 'message' => 'Event not found']);
                    }
                } catch (Exception $e) {
                    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
                }
            } else {
                echo json_encode(['success' => false, 'message' => 'ID required']);
            }
            break;
            
        case 'getByCategory':
            $category = $_GET['category'] ?? '';
            if ($category) {
                try {
                    $events = $controller->getEventsByMainCategory($category);
                    echo json_encode(['success' => true, 'events' => $events]);
                } catch (Exception $e) {
                    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
                }
            } else {
                echo json_encode(['success' => false, 'message' => 'Category required']);
            }
            break;
            
        case 'getUpcoming':
            $limit = $_GET['limit'] ?? 10;
            try {
                $events = $controller->getUpcomingEvents($limit);
                echo json_encode(['success' => true, 'events' => $events]);
            } catch (Exception $e) {
                echo json_encode(['success' => false, 'message' => $e->getMessage()]);
            }
            break;
            
        case 'getFilters':
            // Get all categories, subcategories, venues for filters
            try {
                $categories = [];
                $subcategories = $controller->getAllSubcategories();
                $venues = $controller->getAllVenues();
                
                // Extract unique main categories from subcategories
                foreach ($subcategories as $sub) {
                    if (!in_array($sub['main_category_name'], $categories)) {
                        $categories[] = $sub['main_category_name'];
                    }
                }
                
                echo json_encode([
                    'success' => true,
                    'categories' => $categories,
                    'subcategories' => $subcategories,
                    'venues' => $venues
                ]);
            } catch (Exception $e) {
                echo json_encode(['success' => false, 'message' => $e->getMessage()]);
            }
            break;
            
        default:
            echo json_encode(['success' => false, 'message' => 'Invalid action']);
    }
}

function handlePostRequest($controller, $action) {
    if ($action === 'create') {
        try {
            $json = file_get_contents('php://input');
            $data = json_decode($json, true);
            
            if (json_last_error() !== JSON_ERROR_NONE) {
                throw new Exception('Invalid JSON data');
            }
            
            // Validate required fields
            $required = ['title', 'description', 'subcategory_id', 'venue_id', 'date', 'price', 'total_tickets'];
            foreach ($required as $field) {
                if (empty($data[$field])) {
                    echo json_encode(['success' => false, 'message' => "Missing required field: $field"]);
                    return;
                }
            }
            
            // Create event using Event model
            require_once __DIR__ . '/../../app/models/Event.php';
            $database = new Database();
            $db = $database->getConnection();
            
            $event = new Event($db);
            
            // Set properties
            $event->title = $data['title'];
            $event->description = $data['description'];
            $event->subcategory_id = $data['subcategory_id'];
            $event->venue_id = $data['venue_id'];
            $event->date = $data['date'];
            $event->end_date = $data['end_date'] ?? null;
            $event->price = $data['price'];
            $event->discounted_price = $data['discounted_price'] ?? null;
            $event->image_url = $data['image_url'] ?? 'https://placehold.co/600x400/2a2a2a/f97316?text=Event';
            $event->gallery_images = $data['gallery_images'] ?? [];
            $event->total_tickets = $data['total_tickets'];
            $event->available_tickets = $data['total_tickets']; // Initially same as total
            $event->min_tickets_per_booking = $data['min_tickets_per_booking'] ?? 1;
            $event->max_tickets_per_booking = $data['max_tickets_per_booking'] ?? 10;
            $event->terms_conditions = $data['terms_conditions'] ?? '';
            $event->additional_info = $data['additional_info'] ?? [];
            $event->status = $data['status'] ?? 'active';
            
            // Create event
            $eventId = $event->create();
            
            if ($eventId) {
                echo json_encode([
                    'success' => true,
                    'message' => 'Event created successfully',
                    'eventId' => $eventId
                ]);
            } else {
                echo json_encode(['success' => false, 'message' => 'Failed to create event']);
            }
            
        } catch (Exception $e) {
            echo json_encode(['success' => false, 'message' => $e->getMessage()]);
        }
    }
}

function handlePutRequest($controller, $action) {
    if ($action === 'update') {
        try {
            $id = $_GET['id'] ?? 0;
            $json = file_get_contents('php://input');
            $data = json_decode($json, true);
            
            if (!$id) {
                echo json_encode(['success' => false, 'message' => 'Event ID required']);
                return;
            }
            
            // Update event
            require_once __DIR__ . '/../../app/models/Event.php';
            $database = new Database();
            $db = $database->getConnection();
            
            $event = new Event($db);
            $event->id = $id;
            
            // Only update provided fields
            if (isset($data['title'])) $event->title = $data['title'];
            if (isset($data['description'])) $event->description = $data['description'];
            if (isset($data['subcategory_id'])) $event->subcategory_id = $data['subcategory_id'];
            if (isset($data['venue_id'])) $event->venue_id = $data['venue_id'];
            if (isset($data['date'])) $event->date = $data['date'];
            if (isset($data['end_date'])) $event->end_date = $data['end_date'];
            if (isset($data['price'])) $event->price = $data['price'];
            if (isset($data['discounted_price'])) $event->discounted_price = $data['discounted_price'];
            if (isset($data['image_url'])) $event->image_url = $data['image_url'];
            if (isset($data['gallery_images'])) $event->gallery_images = $data['gallery_images'];
            if (isset($data['total_tickets'])) $event->total_tickets = $data['total_tickets'];
            if (isset($data['available_tickets'])) $event->available_tickets = $data['available_tickets'];
            if (isset($data['min_tickets_per_booking'])) $event->min_tickets_per_booking = $data['min_tickets_per_booking'];
            if (isset($data['max_tickets_per_booking'])) $event->max_tickets_per_booking = $data['max_tickets_per_booking'];
            if (isset($data['terms_conditions'])) $event->terms_conditions = $data['terms_conditions'];
            if (isset($data['additional_info'])) $event->additional_info = $data['additional_info'];
            if (isset($data['status'])) $event->status = $data['status'];
            
            if ($event->update()) {
                echo json_encode(['success' => true, 'message' => 'Event updated successfully']);
            } else {
                echo json_encode(['success' => false, 'message' => 'Failed to update event']);
            }
            
        } catch (Exception $e) {
            echo json_encode(['success' => false, 'message' => $e->getMessage()]);
        }
    }
}

function handleDeleteRequest($controller, $action) {
    if ($action === 'delete') {
        try {
            $id = $_GET['id'] ?? 0;
            
            if (!$id) {
                echo json_encode(['success' => false, 'message' => 'Event ID required']);
                return;
            }
            
            require_once __DIR__ . '/../../app/models/Event.php';
            $database = new Database();
            $db = $database->getConnection();
            
            $event = new Event($db);
            $event->id = $id;
            
            if ($event->delete()) {
                echo json_encode(['success' => true, 'message' => 'Event deleted successfully']);
            } else {
                echo json_encode(['success' => false, 'message' => 'Failed to delete event']);
            }
            
        } catch (Exception $e) {
            echo json_encode(['success' => false, 'message' => $e->getMessage()]);
        }
    }
}
?>
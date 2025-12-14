<?php
// /public/api/events_API.php - Unified Events API (Both Admin & Public)
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

// Determine which controller to use based on the action
$action = $_GET['action'] ?? '';
$method = $_SERVER['REQUEST_METHOD'];

// Actions that need AdminController
$adminActions = ['addEvent', 'updateEvent', 'deleteEvent', 'getEvent', 'getAll', 'getSubcategories'];

// Actions that need EventController (public)
$publicActions = ['getPublicEvent', 'getPublicEvents', 'getByCategory', 'getCategories', 
                  'getVenues', 'getUpcoming', 'getSubcategoriesByCategory'];

try {
    $database = new Database();
    $db = $database->getConnection();
    
    // Load the appropriate controller based on action
    if (in_array($action, $adminActions)) {
        // Include AdminController
        $controllerPath = $projectRoot . '/app/controllers/AdminController.php';
        if (!file_exists($controllerPath)) {
            http_response_code(500);
            echo json_encode(['success' => false, 'message' => 'AdminController not found']);
            exit;
        }
        require_once $controllerPath;
        $controller = new AdminController($db);
        
        switch ($method) {
            case 'GET':
                handleAdminGetRequest($controller, $action);
                break;
                
            case 'POST':
                handleAdminPostRequest($controller, $action);
                break;
                
            case 'PUT':
                handleAdminPutRequest($controller, $action);
                break;
                
            case 'DELETE':
                handleAdminDeleteRequest($controller, $action);
                break;
                
            default:
                http_response_code(405);
                echo json_encode(['success' => false, 'message' => 'Method not allowed']);
        }
        
    } else {
        // Default to EventController for public actions
        $controllerPath = $projectRoot . '/app/controllers/EventController.php';
        if (!file_exists($controllerPath)) {
            http_response_code(500);
            echo json_encode(['success' => false, 'message' => 'EventController not found']);
            exit;
        }
        require_once $controllerPath;
        $controller = new EventController($db);
        
        handlePublicRequest($controller, $action, $method);
    }
    
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Server error: ' . $e->getMessage()
    ]);
}

// ==================== ADMIN HANDLERS ====================
function handleAdminGetRequest($controller, $action) {
    switch ($action) {
        case 'getEvent':
            $id = $_GET['id'] ?? 0;
            if ($id) {
                try {
                    $event = $controller->getEvent($id);
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
            
        case 'getSubcategories':
            $main_category_id = $_GET['main_category_id'] ?? 0;
            if ($main_category_id) {
                try {
                    $subcategories = $controller->getSubcategoriesByMainCategory($main_category_id);
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
            echo json_encode(['success' => false, 'message' => 'Invalid admin action']);
    }
}

function handleAdminPostRequest($controller, $action) {
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
                $venue = $controller->getVenue($eventData['venue_id']);
                if (!$venue || ($venue['status'] ?? '') !== 'active') {
                    echo json_encode(['success' => false, 'message' => 'Selected venue is not available']);
                    return;
                }
                
                // Check if subcategory exists
                $subcategory = $controller->getSubcategory($eventData['subcategory_id']);
                if (!$subcategory) {
                    echo json_encode(['success' => false, 'message' => 'Selected subcategory does not exist']);
                    return;
                }
                
                // Create event
                $result = $controller->createEvent($eventData);
                
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
            echo json_encode(['success' => false, 'message' => 'Invalid admin action']);
    }
}

function handleAdminPutRequest($controller, $action) {
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
            $result = $controller->updateEvent($eventId, $data);
            
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

function handleAdminDeleteRequest($controller, $action) {
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
            $result = $controller->deleteEvent($eventId);
            
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

// ==================== PUBLIC HANDLERS ====================
function handlePublicRequest($controller, $action, $method) {
    if ($method !== 'GET') {
        http_response_code(405);
        echo json_encode(['success' => false, 'message' => 'Method not allowed for public access']);
        return;
    }
    
    switch ($action) {
        case 'getPublicEvents':
        case 'getAllActive':  // Alias for getPublicEvents
            handleGetAllPublicEvents($controller);
            break;
            
        case 'getPublicEvent':
        case 'getOne':  // Alias for getPublicEvent
            handleGetPublicEvent($controller);
            break;
            
        case 'getByCategory':
            handleGetEventsByCategory($controller);
            break;
            
        case 'getCategories':
            handleGetCategories($controller);
            break;
            
        case 'getVenues':
            handleGetVenues($controller);
            break;
            
        case 'getUpcoming':
            handleGetUpcomingEvents($controller);
            break;
            
        case 'getSubcategoriesByCategory':
            handleGetSubcategoriesByCategory($controller);
            break;
            
        default:
            // If no action specified, return all active events
            handleGetAllPublicEvents($controller);
    }
}

function handleGetAllPublicEvents($controller) {
    try {
        $events = $controller->getAllActiveEvents();
        echo json_encode([
            'success' => true,
            'events' => $events,
            'count' => count($events)
        ]);
    } catch (Exception $e) {
        echo json_encode(['success' => false, 'message' => $e->getMessage()]);
    }
}

function handleGetPublicEvent($controller) {
    $id = $_GET['id'] ?? 0;
    if ($id) {
        try {
            $event = $controller->getEventForPublicDisplay($id);
            if ($event) {
                echo json_encode(['success' => true, 'event' => $event]);
            } else {
                echo json_encode(['success' => false, 'message' => 'Event not found or inactive']);
            }
        } catch (Exception $e) {
            echo json_encode(['success' => false, 'message' => $e->getMessage()]);
        }
    } else {
        echo json_encode(['success' => false, 'message' => 'Event ID required']);
    }
}

function handleGetEventsByCategory($controller) {
    $categoryId = $_GET['category_id'] ?? 0;
    $categoryName = $_GET['category_name'] ?? '';
    
    if ($categoryId) {
        try {
            $events = $controller->getEventsByMainCategoryId($categoryId);
            echo json_encode([
                'success' => true,
                'events' => $events,
                'count' => count($events)
            ]);
        } catch (Exception $e) {
            echo json_encode(['success' => false, 'message' => $e->getMessage()]);
        }
    } elseif ($categoryName) {
        try {
            $events = $controller->getEventsByMainCategoryName($categoryName);
            echo json_encode([
                'success' => true,
                'events' => $events,
                'count' => count($events)
            ]);
        } catch (Exception $e) {
            echo json_encode(['success' => false, 'message' => $e->getMessage()]);
        }
    } else {
        echo json_encode(['success' => false, 'message' => 'Category ID or Name required']);
    }
}

function handleGetCategories($controller) {
    try {
        $categories = $controller->getMainCategoriesWithEvents();
        echo json_encode([
            'success' => true,
            'categories' => $categories
        ]);
    } catch (Exception $e) {
        echo json_encode(['success' => false, 'message' => $e->getMessage()]);
    }
}

function handleGetVenues($controller) {
    try {
        $venues = $controller->getVenuesWithEvents();
        echo json_encode([
            'success' => true,
            'venues' => $venues
        ]);
    } catch (Exception $e) {
        echo json_encode(['success' => false, 'message' => $e->getMessage()]);
    }
}

function handleGetUpcomingEvents($controller) {
    $limit = $_GET['limit'] ?? 10;
    try {
        $events = $controller->getUpcomingEvents($limit);
        echo json_encode([
            'success' => true,
            'events' => $events,
            'count' => count($events)
        ]);
    } catch (Exception $e) {
        echo json_encode(['success' => false, 'message' => $e->getMessage()]);
    }
}

function handleGetSubcategoriesByCategory($controller) {
    $categoryName = $_GET['category'] ?? '';
    if ($categoryName) {
        try {
            $subcategories = $controller->getSubcategoriesByMainCategoryName($categoryName);
            echo json_encode([
                'success' => true,
                'subcategories' => $subcategories
            ]);
        } catch (Exception $e) {
            echo json_encode(['success' => false, 'message' => $e->getMessage()]);
        }
    } else {
        echo json_encode(['success' => false, 'message' => 'Category name required']);
    }
}
?>
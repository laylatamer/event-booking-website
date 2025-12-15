<?php
// /public/api/events_API.php - Unified Events API (Both Admin & Public)

// Start output buffering to catch any PHP errors/warnings
ob_start();

// Set error reporting but don't display errors (log them instead)
error_reporting(E_ALL);
ini_set('display_errors', 0);
ini_set('log_errors', 1);

// Helper function to send clean JSON response
function sendJsonResponse($data, $statusCode = 200) {
    ob_clean(); // Clear any PHP warnings/errors
    if (!headers_sent()) {
        http_response_code($statusCode);
        header('Content-Type: application/json');
    }
    echo json_encode($data);
    ob_end_flush();
    exit;
}

// Helper function to output clean JSON (doesn't exit)
function outputCleanJson($data, $statusCode = 200) {
    ob_clean(); // Clear any PHP warnings/errors
    if (!headers_sent()) {
        http_response_code($statusCode);
        header('Content-Type: application/json');
    }
    echo json_encode($data);
}

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    ob_end_clean();
    http_response_code(200);
    exit();
}

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

// Global error handler to catch any unexpected errors
set_error_handler(function($errno, $errstr, $errfile, $errline) {
    // Log the error but don't output it
    error_log("PHP Error [$errno]: $errstr in $errfile on line $errline");
    return false; // Let PHP handle it normally
});

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
                handleAdminGetRequest($controller, $action, $db);
                break;
                
            case 'POST':
                handleAdminPostRequest($controller, $action, $db);
                break;
                
            case 'PUT':
                handleAdminPutRequest($controller, $action, $db);
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
    error_log("Unhandled exception in events_API.php: " . $e->getMessage());
    error_log("Stack trace: " . $e->getTraceAsString());
    sendJsonResponse([
        'success' => false,
        'message' => 'Server error: ' . $e->getMessage()
    ], 500);
} catch (Error $e) {
    error_log("Fatal error in events_API.php: " . $e->getMessage());
    error_log("Stack trace: " . $e->getTraceAsString());
    sendJsonResponse([
        'success' => false,
        'message' => 'Server error: ' . $e->getMessage()
    ], 500);
}

// ==================== ADMIN HANDLERS ====================
function handleAdminGetRequest($controller, $action, $db) {
    switch ($action) {
        case 'getEvent':
            $id = $_GET['id'] ?? 0;
            if ($id) {
                try {
                    $event = $controller->getEvent($id);
                    if ($event) {
                        // Fetch ticket categories for this event (optional - don't fail if this errors)
                        try {
                            require_once __DIR__ . '/../../app/models/EventTicketCategory.php';
                            // Ensure $db is not null
                            if (!$db) {
                                require_once __DIR__ . '/../../config/db_connect.php';
                                $database = new Database();
                                $db = $database->getConnection();
                            }
                            $ticketCategoryModel = new EventTicketCategory($db);
                            $ticketCategoriesStmt = $ticketCategoryModel->getByEventId($id);
                            $ticketCategories = [];
                            while ($row = $ticketCategoriesStmt->fetch(PDO::FETCH_ASSOC)) {
                                $ticketCategories[] = $row;
                            }
                            $event['ticket_categories'] = $ticketCategories;
                        } catch (Exception $e) {
                            // If ticket categories fail to load, just set empty array (don't break the whole request)
                            $event['ticket_categories'] = [];
                        }
                        
                        outputCleanJson(['success' => true, 'event' => $event]);
                    } else {
                        outputCleanJson(['success' => false, 'message' => 'Event not found'], 404);
                    }
                } catch (Exception $e) {
                    error_log("Error in getEvent: " . $e->getMessage());
                    outputCleanJson(['success' => false, 'message' => $e->getMessage()], 500);
                }
            } else {
                outputCleanJson(['success' => false, 'message' => 'Event ID required'], 400);
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

function handleAdminPostRequest($controller, $action, $db) {
    switch ($action) {
        case 'addEvent':
            try {
                // Check if venue exists and is active (needed for capacity check)
                $venueId = $_POST['venue_id'] ?? 0;
                $venue = $controller->getVenue($venueId);
                if (!$venue || ($venue['status'] ?? '') !== 'active') {
                    echo json_encode(['success' => false, 'message' => 'Selected venue is not available']);
                    return;
                }
                
                // Parse ticket categories from POST data
                // Try JSON first (more reliable), then fall back to nested array parsing
                $ticketCategories = [];
                
                // Method 1: Try JSON (preferred method - sent from JavaScript)
                if (isset($_POST['ticket_categories_json']) && !empty($_POST['ticket_categories_json'])) {
                    $ticketCategoriesJson = json_decode($_POST['ticket_categories_json'], true);
                    if (json_last_error() === JSON_ERROR_NONE && is_array($ticketCategoriesJson)) {
                        // Convert array format to associative array by category_name
                        foreach ($ticketCategoriesJson as $category) {
                            $categoryName = $category['category_name'] ?? '';
                            if ($categoryName) {
                                $ticketCategories[$categoryName] = $category;
                            }
                        }
                    }
                }
                
                // Method 2: Try direct access (PHP auto-parses nested arrays from form)
                if (empty($ticketCategories) && isset($_POST['ticket_categories']) && is_array($_POST['ticket_categories'])) {
                    $ticketCategories = $_POST['ticket_categories'];
                }
                
                // Method 3: Manual parsing if auto-parsing didn't work
                if (empty($ticketCategories)) {
                    foreach ($_POST as $key => $value) {
                        if (preg_match('/^ticket_categories\[([^\]]+)\]\[([^\]]+)\]$/', $key, $matches)) {
                            $categoryName = $matches[1];
                            $field = $matches[2];
                            if (!isset($ticketCategories[$categoryName])) {
                                $ticketCategories[$categoryName] = [];
                            }
                            $ticketCategories[$categoryName][$field] = $value;
                        }
                    }
                }
                
                $totalTickets = 0;
                $minPrice = null;
                
                if (empty($ticketCategories)) {
                    outputCleanJson([
                        'success' => false, 
                        'message' => 'Please add ticket categories. Select a venue with seating type first, then fill in ticket quantities and prices for each category.'
                    ], 400);
                    return;
                }
                
                // Validate and calculate totals from categories
                foreach ($ticketCategories as $categoryName => $category) {
                    $categoryTickets = (int)($category['total_tickets'] ?? 0);
                    $categoryPrice = (float)($category['price'] ?? 0);
                    $actualCategoryName = $category['category_name'] ?? $categoryName;
                    
                    if ($categoryTickets <= 0) {
                        outputCleanJson(['success' => false, 'message' => "Ticket count must be greater than 0 for category: " . $actualCategoryName], 400);
                        return;
                    }
                    
                    if ($categoryPrice < 0) {
                        outputCleanJson(['success' => false, 'message' => "Price must be 0 or greater for category: " . $actualCategoryName], 400);
                        return;
                    }
                    
                    $totalTickets += $categoryTickets;
                    
                    // Track minimum price for base price
                    if ($minPrice === null || $categoryPrice < $minPrice) {
                        $minPrice = $categoryPrice;
                    }
                }
                
                // Check if total tickets exceed venue capacity
                $venueCapacity = (int)($venue['capacity'] ?? 0);
                if ($venueCapacity > 0 && $totalTickets > $venueCapacity) {
                    outputCleanJson(['success' => false, 'message' => "Total tickets ($totalTickets) exceed venue capacity ($venueCapacity). Please reduce ticket quantities."], 400);
                    return;
                }
                
                // Check if subcategory exists
                $subcategoryId = $_POST['subcategory_id'] ?? 0;
                $subcategory = $controller->getSubcategory($subcategoryId);
                if (!$subcategory) {
                    outputCleanJson(['success' => false, 'message' => 'Selected subcategory does not exist'], 400);
                    return;
                }
                
                // Build event data
                $eventData = [
                    'title' => $_POST['title'] ?? '',
                    'description' => $_POST['description'] ?? '',
                    'subcategory_id' => $subcategoryId,
                    'venue_id' => $venueId,
                    'date' => $_POST['date'] ?? '',
                    'end_date' => $_POST['end_date'] ?? null,
                    'price' => $minPrice ?? 0, // Use minimum category price as base price
                    'discounted_price' => $_POST['discounted_price'] ?? null,
                    'image_url' => $_POST['image_url'] ?? '',
                    'gallery_images' => !empty($_POST['gallery_images']) ? json_decode($_POST['gallery_images'], true) : [],
                    'total_tickets' => $totalTickets, // Calculated from categories
                    'available_tickets' => $totalTickets, // Same as total (all available initially)
                    'min_tickets_per_booking' => $_POST['min_tickets_per_booking'] ?? 1,
                    'max_tickets_per_booking' => $_POST['max_tickets_per_booking'] ?? 10,
                    'terms_conditions' => $_POST['terms_conditions'] ?? '',
                    'additional_info' => !empty($_POST['additional_info']) ? json_decode($_POST['additional_info'], true) : [],
                    'status' => $_POST['status'] ?? 'draft'
                ];
                
                // Validate required fields (removed price and total_tickets - they're calculated)
                $required = ['title', 'description', 'subcategory_id', 'venue_id', 'date'];
                foreach ($required as $field) {
                    if (empty($eventData[$field])) {
                        outputCleanJson(['success' => false, 'message' => "Missing required field: $field"], 400);
                        return;
                    }
                }
                
                // Create event
                $result = $controller->createEvent($eventData);
                
                if ($result) {
                    $eventId = $result; // createEvent now returns the event ID
                    
                    // Create ticket categories if provided
                    if (!empty($ticketCategories)) {
                        try {
                            require_once __DIR__ . '/../../app/models/EventTicketCategory.php';
                            // Ensure $db is not null
                            if (!$db) {
                                require_once __DIR__ . '/../../config/db_connect.php';
                                $database = new Database();
                                $db = $database->getConnection();
                            }
                            $ticketCategoryModel = new EventTicketCategory($db);
                            
                            $createdCount = 0;
                            foreach ($ticketCategories as $categoryName => $category) {
                                $ticketCategoryModel->event_id = $eventId;
                                $ticketCategoryModel->category_name = $category['category_name'] ?? $categoryName;
                                $ticketCategoryModel->total_tickets = (int)($category['total_tickets'] ?? 0);
                                $ticketCategoryModel->available_tickets = (int)($category['total_tickets'] ?? 0);
                                $ticketCategoryModel->price = (float)($category['price'] ?? 0);
                                
                                if (!empty($ticketCategoryModel->category_name) && $ticketCategoryModel->total_tickets > 0) {
                                    if (!$ticketCategoryModel->create()) {
                                        error_log("Failed to create ticket category: " . $ticketCategoryModel->category_name . " for event: " . $eventId);
                                        // Continue with other categories even if one fails
                                    } else {
                                        $createdCount++;
                                    }
                                }
                            }
                        } catch (Exception $e) {
                            error_log("Error creating ticket categories: " . $e->getMessage());
                            // Don't fail the entire event creation if categories fail
                        } catch (Error $e) {
                            error_log("Fatal error creating ticket categories: " . $e->getMessage());
                            // Don't fail the entire event creation if categories fail
                        }
                    }
                    
                    outputCleanJson([
                        'success' => true,
                        'message' => 'Event created successfully',
                        'eventId' => $eventId
                    ], 200);
                } else {
                    outputCleanJson(['success' => false, 'message' => 'Failed to create event'], 500);
                }
                
            } catch (Exception $e) {
                outputCleanJson(['success' => false, 'message' => $e->getMessage()], 500);
            } catch (Error $e) {
                outputCleanJson(['success' => false, 'message' => 'Server error: ' . $e->getMessage()], 500);
            }
            break;
            
        default:
            echo json_encode(['success' => false, 'message' => 'Invalid admin action']);
    }
}

function handleAdminPutRequest($controller, $action, $db) {
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
                // Handle ticket categories update if provided
                $ticketCategories = [];
                
                // Method 1: Try JSON (preferred method - sent from JavaScript)
                if (isset($data['ticket_categories_json']) && !empty($data['ticket_categories_json'])) {
                    $ticketCategoriesJson = is_string($data['ticket_categories_json']) ? 
                        json_decode($data['ticket_categories_json'], true) : $data['ticket_categories_json'];
                    if (json_last_error() === JSON_ERROR_NONE && is_array($ticketCategoriesJson)) {
                        foreach ($ticketCategoriesJson as $category) {
                            $categoryName = $category['category_name'] ?? '';
                            if ($categoryName) {
                                $ticketCategories[$categoryName] = $category;
                            }
                        }
                    }
                }
                
                // Method 2: Direct array
                if (empty($ticketCategories) && isset($data['ticket_categories']) && is_array($data['ticket_categories'])) {
                    $ticketCategories = $data['ticket_categories'];
                }
                
                // Update ticket categories if provided
                if (!empty($ticketCategories)) {
                    try {
                        require_once __DIR__ . '/../../app/models/EventTicketCategory.php';
                        // Ensure $db is not null
                        if (!$db) {
                            require_once __DIR__ . '/../../config/db_connect.php';
                            $database = new Database();
                            $db = $database->getConnection();
                        }
                        $ticketCategoryModel = new EventTicketCategory($db);
                        
                        // Delete existing categories for this event
                        $ticketCategoryModel->deleteByEventId($eventId);
                        
                        // Create new categories
                        $createdCount = 0;
                        foreach ($ticketCategories as $categoryName => $category) {
                            $ticketCategoryModel->event_id = $eventId;
                            $ticketCategoryModel->category_name = $category['category_name'] ?? $categoryName;
                            $ticketCategoryModel->total_tickets = (int)($category['total_tickets'] ?? 0);
                            $ticketCategoryModel->available_tickets = (int)($category['total_tickets'] ?? 0);
                            $ticketCategoryModel->price = (float)($category['price'] ?? 0);
                            
                            if (!empty($ticketCategoryModel->category_name) && $ticketCategoryModel->total_tickets > 0) {
                                if ($ticketCategoryModel->create()) {
                                    $createdCount++;
                                }
                            }
                        }
                    } catch (Exception $e) {
                        error_log("Error updating ticket categories: " . $e->getMessage());
                    }
                }
                
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
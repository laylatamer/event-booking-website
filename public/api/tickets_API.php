<?php
/**
 * Tickets API
 * RESTful API endpoint for ticket management
 */

// Enable error reporting for debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Set headers for CORS and JSON response
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization');

// Handle preflight requests
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

try {
    // Load configuration and models
    require_once __DIR__ . '/../../../config/db_connect.php';
    require_once __DIR__ . '/../../../app/models/TicketsModel.php';
    
    // Initialize database connection
    $database = new Database();
    $db = $database->getConnection();
    
    // Initialize TicketsModel
    $ticketsModel = new TicketsModel($db);
    
    // Get request method
    $method = $_SERVER['REQUEST_METHOD'];
    
    // Get request data
    $input = json_decode(file_get_contents('php://input'), true);
    $queryParams = $_GET;
    
    // Get action from query parameter
    $action = $queryParams['action'] ?? '';
    
    // Process request based on method and action
    switch ($action) {
        case 'getAll':
            handleGetAllTickets($ticketsModel, $queryParams);
            break;
            
        case 'getOne':
            handleGetTicketById($ticketsModel, $queryParams);
            break;
            
        case 'getStats':
            handleGetStats($ticketsModel);
            break;
            
        case 'create':
            if ($method === 'POST') {
                handleCreateTicket($ticketsModel, $input);
            } else {
                sendError('Method not allowed', 405);
            }
            break;
            
        case 'update':
            if ($method === 'PUT' || $method === 'POST') {
                handleUpdateTicket($ticketsModel, $queryParams, $input);
            } else {
                sendError('Method not allowed', 405);
            }
            break;
            
        case 'updateStatus':
            if ($method === 'PUT' || $method === 'POST') {
                handleUpdateStatus($ticketsModel, $input);
            } else {
                sendError('Method not allowed', 405);
            }
            break;
            
        case 'updateQuantity':
            if ($method === 'PUT' || $method === 'POST') {
                handleUpdateQuantity($ticketsModel, $input);
            } else {
                sendError('Method not allowed', 405);
            }
            break;
            
        case 'delete':
            handleDeleteTicket($ticketsModel, $queryParams);
            break;
            
        case 'byEvent':
            handleGetTicketsByEvent($ticketsModel, $queryParams);
            break;
            
        case 'dropdown':
            handleGetTicketsForDropdown($ticketsModel, $queryParams);
            break;
            
        case 'checkAvailability':
            handleCheckAvailability($ticketsModel, $queryParams);
            break;
            
        case 'dashboardSummary':
            handleGetDashboardSummary($ticketsModel);
            break;
            
        default:
            sendError('Invalid action specified', 400);
            break;
    }
    
} catch (Exception $e) {
    error_log("Tickets API Error: " . $e->getMessage());
    sendError($e->getMessage(), 500);
}

/**
 * Send JSON response
 */
function sendResponse($data, $statusCode = 200) {
    http_response_code($statusCode);
    echo json_encode($data);
    exit();
}

/**
 * Send error response
 */
function sendError($message, $statusCode = 500) {
    sendResponse([
        'success' => false,
        'message' => $message,
        'timestamp' => date('Y-m-d H:i:s')
    ], $statusCode);
}

/**
 * Send success response
 */
function sendSuccess($data = [], $message = 'Success') {
    sendResponse([
        'success' => true,
        'message' => $message,
        'data' => $data,
        'timestamp' => date('Y-m-d H:i:s')
    ]);
}

/**
 * Handle GET all tickets
 */
function handleGetAllTickets($ticketsModel, $params) {
    try {
        // Get pagination parameters
        $page = isset($params['page']) ? intval($params['page']) : 1;
        $limit = isset($params['limit']) ? intval($params['limit']) : 10;
        
        // Prepare filters
        $filters = [
            'status' => $params['status'] ?? '',
            'event_id' => $params['event_id'] ?? '',
            'type' => $params['type'] ?? '',
            'search' => $params['search'] ?? '',
            'min_price' => $params['min_price'] ?? '',
            'max_price' => $params['max_price'] ?? ''
        ];
        
        // Validate pagination
        if ($page < 1) $page = 1;
        if ($limit < 1 || $limit > 100) $limit = 10;
        
        // Get tickets
        $result = $ticketsModel->getAllTickets($page, $limit, $filters);
        
        sendSuccess([
            'tickets' => $result['tickets'],
            'pagination' => [
                'total' => $result['total'],
                'page' => $result['page'],
                'limit' => $result['limit'],
                'pages' => $result['pages'],
                'has_prev' => $result['has_prev'],
                'has_next' => $result['has_next']
            ]
        ]);
        
    } catch (Exception $e) {
        sendError('Failed to fetch tickets: ' . $e->getMessage());
    }
}

/**
 * Handle GET ticket by ID
 */
function handleGetTicketById($ticketsModel, $params) {
    try {
        $id = $params['id'] ?? 0;
        
        if (!$id) {
            sendError('Ticket ID is required', 400);
        }
        
        $ticket = $ticketsModel->getTicketById($id);
        
        if (!$ticket) {
            sendError('Ticket not found', 404);
        }
        
        sendSuccess($ticket);
        
    } catch (Exception $e) {
        sendError('Failed to fetch ticket: ' . $e->getMessage());
    }
}

/**
 * Handle GET ticket statistics
 */
function handleGetStats($ticketsModel) {
    try {
        $stats = $ticketsModel->getTicketStats();
        sendSuccess($stats);
        
    } catch (Exception $e) {
        sendError('Failed to fetch ticket statistics: ' . $e->getMessage());
    }
}

/**
 * Handle POST create ticket
 */
function handleCreateTicket($ticketsModel, $data) {
    try {
        // Validate required fields
        $required = ['event_id', 'name', 'type', 'price', 'quantity_total'];
        foreach ($required as $field) {
            if (empty($data[$field])) {
                sendError("Field '{$field}' is required", 400);
            }
        }
        
        // Create ticket
        $ticketId = $ticketsModel->createTicket($data);
        
        if (!$ticketId) {
            sendError('Failed to create ticket', 500);
        }
        
        // Get the created ticket
        $ticket = $ticketsModel->getTicketById($ticketId);
        
        sendSuccess($ticket, 'Ticket created successfully');
        
    } catch (Exception $e) {
        sendError('Failed to create ticket: ' . $e->getMessage());
    }
}

/**
 * Handle PUT/POST update ticket
 */
function handleUpdateTicket($ticketsModel, $params, $data) {
    try {
        $id = $params['id'] ?? $data['id'] ?? 0;
        
        if (!$id) {
            sendError('Ticket ID is required', 400);
        }
        
        if (empty($data)) {
            sendError('No data provided for update', 400);
        }
        
        $success = $ticketsModel->updateTicket($id, $data);
        
        if (!$success) {
            sendError('Failed to update ticket', 500);
        }
        
        // Get updated ticket
        $ticket = $ticketsModel->getTicketById($id);
        
        sendSuccess($ticket, 'Ticket updated successfully');
        
    } catch (Exception $e) {
        sendError('Failed to update ticket: ' . $e->getMessage());
    }
}

/**
 * Handle PUT/POST update ticket status
 */
function handleUpdateStatus($ticketsModel, $data) {
    try {
        if (empty($data['id']) || empty($data['status'])) {
            sendError('Ticket ID and status are required', 400);
        }
        
        $success = $ticketsModel->updateTicketStatus($data['id'], $data['status']);
        
        if (!$success) {
            sendError('Failed to update ticket status', 500);
        }
        
        sendSuccess([], 'Ticket status updated successfully');
        
    } catch (Exception $e) {
        sendError('Failed to update ticket status: ' . $e->getMessage());
    }
}

/**
 * Handle PUT/POST update ticket quantity
 */
function handleUpdateQuantity($ticketsModel, $data) {
    try {
        if (empty($data['id']) || empty($data['quantity_total'])) {
            sendError('Ticket ID and quantity are required', 400);
        }
        
        $success = $ticketsModel->updateTicket($data['id'], ['quantity_total' => $data['quantity_total']]);
        
        if (!$success) {
            sendError('Failed to update ticket quantity', 500);
        }
        
        sendSuccess([], 'Ticket quantity updated successfully');
        
    } catch (Exception $e) {
        sendError('Failed to update ticket quantity: ' . $e->getMessage());
    }
}

/**
 * Handle DELETE ticket
 */
function handleDeleteTicket($ticketsModel, $params) {
    try {
        $id = $params['id'] ?? 0;
        
        if (!$id) {
            sendError('Ticket ID is required', 400);
        }
        
        $success = $ticketsModel->deleteTicket($id);
        
        if (!$success) {
            sendError('Failed to delete ticket', 500);
        }
        
        sendSuccess([], 'Ticket deleted successfully');
        
    } catch (Exception $e) {
        sendError('Failed to delete ticket: ' . $e->getMessage());
    }
}

/**
 * Handle GET tickets by event
 */
function handleGetTicketsByEvent($ticketsModel, $params) {
    try {
        $eventId = $params['event_id'] ?? 0;
        
        if (!$eventId) {
            sendError('Event ID is required', 400);
        }
        
        $tickets = $ticketsModel->getTicketsByEvent($eventId);
        sendSuccess($tickets);
        
    } catch (Exception $e) {
        sendError('Failed to fetch event tickets: ' . $e->getMessage());
    }
}

/**
 * Handle GET tickets for dropdown
 */
function handleGetTicketsForDropdown($ticketsModel, $params) {
    try {
        $eventId = $params['event_id'] ?? null;
        
        $tickets = $ticketsModel->getTicketsForDropdown($eventId);
        sendSuccess($tickets);
        
    } catch (Exception $e) {
        sendError('Failed to fetch tickets for dropdown: ' . $e->getMessage());
    }
}

/**
 * Handle GET check ticket availability
 */
function handleCheckAvailability($ticketsModel, $params) {
    try {
        $ticketId = $params['ticket_id'] ?? 0;
        $quantity = isset($params['quantity']) ? intval($params['quantity']) : 1;
        
        if (!$ticketId) {
            sendError('Ticket ID is required', 400);
        }
        
        $available = $ticketsModel->checkAvailability($ticketId, $quantity);
        
        sendSuccess(['available' => $available]);
        
    } catch (Exception $e) {
        sendError('Failed to check ticket availability: ' . $e->getMessage());
    }
}

/**
 * Handle GET dashboard summary
 */
function handleGetDashboardSummary($ticketsModel) {
    try {
        $summary = $ticketsModel->getDashboardSummary();
        sendSuccess($summary);
        
    } catch (Exception $e) {
        sendError('Failed to fetch dashboard summary: ' . $e->getMessage());
    }
}
?>
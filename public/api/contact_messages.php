<?php
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
        if (!headers_sent()) {
            ob_clean();
            http_response_code(500);
            header('Content-Type: application/json');
            echo json_encode([
                'success' => false,
                'error' => 'System error',
                'message' => 'Please try again later'
            ]);
        }
        exit();
    }
});

// Include error handler FIRST - before any other code
require_once __DIR__ . '/../../config/error_handler.php';

// Set headers early
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

try {
    require_once __DIR__ . '/../../config/db_connect.php';
    require_once __DIR__ . '/../../app/models/ContactMessage.php';
    require_once __DIR__ . '/../../app/controllers/ContactController.php';
} catch (Exception $e) {
    if (ob_get_level()) ob_clean();
    http_response_code(500);
    echo json_encode(['success' => false, 'error' => 'Failed to load dependencies', 'message' => $e->getMessage()]);
    exit;
}

try {
    $controller = new ContactController(new ContactMessage($pdo));
    $method = $_SERVER['REQUEST_METHOD'];

    switch ($method) {
        case 'GET':
            if (isset($_GET['id'])) {
                $message = $controller->show((int) $_GET['id']);
                if ($message === null) {
                    http_response_code(404);
                    echo json_encode(['error' => 'Message not found']);
                } else {
                    echo json_encode($message, JSON_PRETTY_PRINT);
                }
            } else {
                $messages = $controller->index();
                echo json_encode($messages);
            }
            break;

        case 'PATCH':
        case 'PUT':
            $input = file_get_contents('php://input');
            
            // Try to parse as JSON first
            $data = json_decode($input, true);
            if (json_last_error() !== JSON_ERROR_NONE || !is_array($data)) {
                // Fall back to form-urlencoded
                parse_str($input, $data);
            }
            
            if (!isset($data['id'], $data['status'])) {
                http_response_code(400);
                echo json_encode(['error' => 'id and status are required']);
                break;
            }
            $ok = $controller->update((int) $data['id'], $data['status']);
            echo json_encode(['ok' => $ok]);
            break;

        case 'DELETE':
            $input = file_get_contents('php://input');
            
            // Try to parse as JSON first
            $data = json_decode($input, true);
            if (json_last_error() !== JSON_ERROR_NONE || !is_array($data)) {
                // Fall back to form-urlencoded
                parse_str($input, $data);
            }
            
            if (!isset($data['id'])) {
                http_response_code(400);
                echo json_encode(['error' => 'id is required']);
                break;
            }
            $ok = $controller->destroy((int) $data['id']);
            echo json_encode(['ok' => $ok]);
            break;

        default:
            http_response_code(405);
            echo json_encode(['error' => 'Method not allowed']);
    }
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => 'Server error']);
}



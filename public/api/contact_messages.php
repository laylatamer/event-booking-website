<?php

try {
    require_once __DIR__ . '/../../config/db_connect.php';
    require_once __DIR__ . '/../../app/models/ContactMessage.php';
    require_once __DIR__ . '/../../app/controllers/ContactController.php';
} catch (Exception $e) {
    http_response_code(500);
    header('Content-Type: application/json');
    echo json_encode(['error' => 'Failed to load dependencies']);
    exit;
}

header('Content-Type: application/json');

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



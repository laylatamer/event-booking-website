<?php

require_once __DIR__ . '/../../app/controllers/ContactController.php';

header('Content-Type: application/json');

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
            echo json_encode($controller->index(), JSON_PRETTY_PRINT);
        }
        break;

    case 'PATCH':
    case 'PUT':
        parse_str(file_get_contents('php://input'), $data);
        if (!isset($data['id'], $data['status'])) {
            http_response_code(400);
            echo json_encode(['error' => 'id and status are required']);
            break;
        }
        $ok = $controller->update((int) $data['id'], $data['status']);
        echo json_encode(['ok' => $ok]);
        break;

    case 'DELETE':
        parse_str(file_get_contents('php://input'), $data);
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



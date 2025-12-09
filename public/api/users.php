<?php

try {
    require_once __DIR__ . '/../../config/db_connect.php';
    require_once __DIR__ . '/../../app/models/User.php';
    require_once __DIR__ . '/../../app/controllers/UserController.php';
} catch (Exception $e) {
    http_response_code(500);
    header('Content-Type: application/json');
    echo json_encode(['error' => 'Failed to load dependencies']);
    exit;
}

header('Content-Type: application/json');

try {
    $controller = new UserController(new User($pdo));
    $method = $_SERVER['REQUEST_METHOD'];

    switch ($method) {
        case 'GET':
            if (isset($_GET['id'])) {
                $user = $controller->show((int) $_GET['id']);
                if ($user === null) {
                    http_response_code(404);
                    echo json_encode(['error' => 'User not found']);
                } else {
                    echo json_encode($user);
                }
            } else {
                $users = $controller->index();
                echo json_encode($users);
            }
            break;

        case 'POST':
            $data = $_POST;
            
            // Additional password validation
            if (!empty($data['password'])) {
                if (strlen($data['password']) < 8) {
                    http_response_code(400);
                    echo json_encode(['ok' => false, 'message' => 'Password must be at least 8 characters long']);
                    break;
                }
                
                if (!preg_match('/[A-Z]/', $data['password']) || !preg_match('/[\W_]/', $data['password'])) {
                    http_response_code(400);
                    echo json_encode(['ok' => false, 'message' => 'Password must contain at least one uppercase letter and one symbol']);
                    break;
                }
            }

            $result = $controller->store($data);
            if ($result['ok']) {
                echo json_encode(['ok' => true, 'id' => $result['id']]);
            } else {
                http_response_code(400);
                echo json_encode(['ok' => false, 'message' => $result['message'] ?? 'Unable to create user']);
            }
            break;

        case 'DELETE':
            $input = file_get_contents('php://input');
            
            $data = json_decode($input, true);
            if (json_last_error() !== JSON_ERROR_NONE || !is_array($data)) {
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


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
    require_once __DIR__ . '/../../app/models/User.php';
    require_once __DIR__ . '/../../app/controllers/UserController.php';
} catch (Exception $e) {
    if (ob_get_level()) ob_clean();
    http_response_code(500);
    echo json_encode(['success' => false, 'error' => 'Failed to load dependencies', 'message' => $e->getMessage()]);
    exit;
}

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
                // Ensure it's an array
                if (!is_array($users)) {
                    $users = [];
                }
                if (ob_get_level()) ob_clean();
                echo json_encode($users);
                exit();
            }
            break;

        case 'POST':
            $data = $_POST;
            
            // Check if this is an update request (has 'id' field) - used for file uploads with FormData
            if (isset($data['id']) && !empty($data['id'])) {
                // Handle update via POST (for file uploads)
                $contentType = $_SERVER['CONTENT_TYPE'] ?? '';
                $isMultipart = strpos($contentType, 'multipart/form-data') !== false;
                
                // Process profile image upload if present
                if (!empty($_FILES) && isset($_FILES['profile_image'])) {
                    $file = $_FILES['profile_image'];
                    if ($file['error'] === UPLOAD_ERR_OK) {
                        // Validate file type
                        $finfo = finfo_open(FILEINFO_MIME_TYPE);
                        $mime = finfo_file($finfo, $file['tmp_name']);
                        finfo_close($finfo);
                        
                        $allowed = [
                            'image/jpeg' => 'jpg',
                            'image/png'  => 'png',
                            'image/gif'  => 'gif',
                        ];
                        
                        if (!isset($allowed[$mime])) {
                            http_response_code(400);
                            echo json_encode(['ok' => false, 'message' => 'Profile image must be JPG, PNG, or GIF.']);
                            break;
                        }
                        
                        if ($file['size'] > 2 * 1024 * 1024) {
                            http_response_code(400);
                            echo json_encode(['ok' => false, 'message' => 'Profile image must be 2MB or smaller.']);
                            break;
                        }
                        
                        // Create upload directory if it doesn't exist
                        $uploadDir = __DIR__ . '/../../uploads/profile_pics/';
                        if (!is_dir($uploadDir)) {
                            mkdir($uploadDir, 0777, true);
                        }
                        
                        // Generate unique filename
                        $userId = (int)$data['id'];
                        $newFileName = 'user_' . $userId . '_' . time() . '.' . $allowed[$mime];
                        $targetPath = $uploadDir . $newFileName;
                        
                        // Move uploaded file
                        if (move_uploaded_file($file['tmp_name'], $targetPath)) {
                            // Save the relative path to the database
                            $data['profile_image_path'] = 'uploads/profile_pics/' . $newFileName;
                            error_log("Profile image uploaded successfully: " . $data['profile_image_path']);
                        } else {
                            error_log("Failed to move uploaded file from {$file['tmp_name']} to {$targetPath}");
                            http_response_code(500);
                            echo json_encode(['ok' => false, 'message' => 'Failed to save the uploaded image.']);
                            break;
                        }
                    } elseif ($file['error'] !== UPLOAD_ERR_NO_FILE) {
                        // Handle upload errors (except NO_FILE which is fine)
                        $uploadErrors = [
                            UPLOAD_ERR_INI_SIZE => 'File exceeds upload_max_filesize directive.',
                            UPLOAD_ERR_FORM_SIZE => 'File exceeds MAX_FILE_SIZE directive.',
                            UPLOAD_ERR_PARTIAL => 'File was only partially uploaded.',
                            UPLOAD_ERR_NO_TMP_DIR => 'Missing temporary folder.',
                            UPLOAD_ERR_CANT_WRITE => 'Failed to write file to disk.',
                            UPLOAD_ERR_EXTENSION => 'A PHP extension stopped the file upload.',
                        ];
                        $errorMsg = $uploadErrors[$file['error']] ?? 'Unknown upload error.';
                        error_log("Profile image upload error: " . $errorMsg);
                        http_response_code(400);
                        echo json_encode(['ok' => false, 'message' => 'Image upload failed: ' . $errorMsg]);
                        break;
                    }
                }
                
                // Handle update
                $result = $controller->update((int) $data['id'], $data);
                if ($result['ok']) {
                    echo json_encode(['ok' => true, 'message' => 'User updated successfully']);
                } else {
                    http_response_code(400);
                    echo json_encode(['ok' => false, 'message' => $result['message'] ?? 'Failed to update user']);
                }
                break;
            }
            
            // Otherwise, handle as create (new user)
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

        case 'PUT':
        case 'PATCH':
            // Handle both JSON and multipart/form-data (for file uploads)
            $data = [];
            $contentType = $_SERVER['CONTENT_TYPE'] ?? '';
            $isMultipart = strpos($contentType, 'multipart/form-data') !== false;
            
            // Check if this is a multipart/form-data request (FormData from JavaScript)
            // For PUT requests, $_POST may not be populated, so check $_FILES first
            // If we have files, it's definitely multipart. Also check if $_POST has data.
            if ($isMultipart || !empty($_FILES) || !empty($_POST)) {
                // Handle FormData request
                // For PUT with multipart, PHP might not populate $_POST, so parse from input if needed
                if (!empty($_POST)) {
                    $data = $_POST;
                } else {
                    // Parse multipart/form-data from php://input
                    // This is a fallback for when $_POST isn't populated
                    $input = file_get_contents('php://input');
                    if ($input && $isMultipart) {
                        // For multipart, try to extract form fields (simple approach)
                        // Note: This is a simplified parser - full multipart parsing is complex
                        // If this doesn't work, we may need to use a library or change to POST
                        parse_str($input, $data);
                    }
                }
                
                // Process profile image upload if present
                if (!empty($_FILES) && isset($_FILES['profile_image'])) {
                    $file = $_FILES['profile_image'];
                    if ($file['error'] === UPLOAD_ERR_OK) {
                        // Validate file type
                        $finfo = finfo_open(FILEINFO_MIME_TYPE);
                        $mime = finfo_file($finfo, $file['tmp_name']);
                        finfo_close($finfo);
                        
                        $allowed = [
                            'image/jpeg' => 'jpg',
                            'image/png'  => 'png',
                            'image/gif'  => 'gif',
                        ];
                        
                        if (!isset($allowed[$mime])) {
                            http_response_code(400);
                            echo json_encode(['ok' => false, 'message' => 'Profile image must be JPG, PNG, or GIF.']);
                            break;
                        }
                        
                        if ($file['size'] > 2 * 1024 * 1024) {
                            http_response_code(400);
                            echo json_encode(['ok' => false, 'message' => 'Profile image must be 2MB or smaller.']);
                            break;
                        }
                        
                        // Create upload directory if it doesn't exist
                        $uploadDir = __DIR__ . '/../../uploads/profile_pics/';
                        if (!is_dir($uploadDir)) {
                            mkdir($uploadDir, 0777, true);
                        }
                        
                        // Generate unique filename - try to get ID from $data or use timestamp
                        $userId = (int)($data['id'] ?? 0);
                        if ($userId === 0) {
                            // If we still don't have ID, log error
                            error_log("Warning: User ID not found in FormData. Files: " . print_r($_FILES, true));
                        }
                        $newFileName = 'user_' . ($userId > 0 ? $userId : '0') . '_' . time() . '.' . $allowed[$mime];
                        $targetPath = $uploadDir . $newFileName;
                        
                        // Move uploaded file
                        if (move_uploaded_file($file['tmp_name'], $targetPath)) {
                            // Save the relative path to the database
                            $data['profile_image_path'] = 'uploads/profile_pics/' . $newFileName;
                            error_log("Profile image uploaded successfully: " . $data['profile_image_path']);
                        } else {
                            error_log("Failed to move uploaded file from {$file['tmp_name']} to {$targetPath}");
                            http_response_code(500);
                            echo json_encode(['ok' => false, 'message' => 'Failed to save the uploaded image.']);
                            break;
                        }
                    } elseif ($file['error'] !== UPLOAD_ERR_NO_FILE) {
                        // Handle upload errors (except NO_FILE which is fine)
                        $uploadErrors = [
                            UPLOAD_ERR_INI_SIZE => 'File exceeds upload_max_filesize directive.',
                            UPLOAD_ERR_FORM_SIZE => 'File exceeds MAX_FILE_SIZE directive.',
                            UPLOAD_ERR_PARTIAL => 'File was only partially uploaded.',
                            UPLOAD_ERR_NO_TMP_DIR => 'Missing temporary folder.',
                            UPLOAD_ERR_CANT_WRITE => 'Failed to write file to disk.',
                            UPLOAD_ERR_EXTENSION => 'A PHP extension stopped the file upload.',
                        ];
                        $errorMsg = $uploadErrors[$file['error']] ?? 'Unknown upload error.';
                        error_log("Profile image upload error: " . $errorMsg);
                        http_response_code(400);
                        echo json_encode(['ok' => false, 'message' => 'Image upload failed: ' . $errorMsg]);
                        break;
                    }
                }
            } else {
                // Handle JSON request (no FormData)
                $input = file_get_contents('php://input');
                $data = json_decode($input, true);
                
                if (json_last_error() !== JSON_ERROR_NONE || !is_array($data)) {
                    parse_str($input, $data);
                }
            }
            
            // Debug logging
            error_log("PUT request - Content-Type: " . $contentType);
            error_log("PUT request - POST data: " . print_r($_POST, true));
            error_log("PUT request - Parsed data: " . print_r($data, true));
            
            if (!isset($data['id']) || empty($data['id'])) {
                http_response_code(400);
                echo json_encode(['ok' => false, 'message' => 'User ID is required']);
                break;
            }
            
            $result = $controller->update((int) $data['id'], $data);
            if ($result['ok']) {
                echo json_encode(['ok' => true, 'message' => 'User updated successfully']);
            } else {
                http_response_code(400);
                echo json_encode(['ok' => false, 'message' => $result['message'] ?? 'Failed to update user']);
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
    error_log("Users API error: " . $e->getMessage());
    if (ob_get_level()) ob_clean();
    http_response_code(500);
    echo json_encode(['success' => false, 'error' => 'Server error', 'message' => $e->getMessage()]);
    exit();
}

// Clean any output before sending JSON
if (ob_get_level()) {
    ob_clean();
}
?>

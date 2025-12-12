<?php
// /public/api/categories_API.php - COMPLETE UPDATED VERSION WITH IMAGE UPLOAD
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

// Enable error reporting for debugging (disable in production)
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Handle CORS preflight
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

// Get absolute paths
$currentDir = __DIR__; // /event-booking-website/public/api
$projectRoot = dirname(dirname($currentDir)); // /event-booking-website

// Debug info (you can remove this in production)
$debugInfo = [
    'currentDir' => $currentDir,
    'projectRoot' => $projectRoot,
    'configPath' => $projectRoot . '/config/db_connect.php',
    'configExists' => file_exists($projectRoot . '/config/db_connect.php'),
    'modelsDir' => $projectRoot . '/app/models/',
    'modelsExist' => is_dir($projectRoot . '/app/models/'),
    'controllerPath' => $projectRoot . '/app/controllers/AdminController.php',
    'controllerExists' => file_exists($projectRoot . '/app/controllers/AdminController.php')
];

// Check if required files exist
$configPath = $projectRoot . '/config/db_connect.php';
if (!file_exists($configPath)) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Database configuration file not found',
        'debug' => $debugInfo
    ]);
    exit;
}

// Include database configuration
require_once $configPath;

// Image upload helper function
function handleImageUpload($file, $type = 'subcategories') {
    $uploadDir = __DIR__ . '/../../public/uploads/' . $type . '/';
    
    // Create directory if it doesn't exist
    if (!file_exists($uploadDir)) {
        mkdir($uploadDir, 0755, true);
    }
    
    // Check for upload errors
    if ($file['error'] !== UPLOAD_ERR_OK) {
        throw new Exception('Upload error: ' . $file['error']);
    }
    
    // Check file size (5MB max)
    $maxSize = 5 * 1024 * 1024; // 5MB
    if ($file['size'] > $maxSize) {
        throw new Exception('File too large. Maximum size: 5MB');
    }
    
    // Check file type
    $allowedTypes = ['image/jpeg', 'image/jpg', 'image/png', 'image/gif', 'image/webp'];
    $finfo = finfo_open(FILEINFO_MIME_TYPE);
    $mime = finfo_file($finfo, $file['tmp_name']);
    finfo_close($finfo);
    
    if (!in_array($mime, $allowedTypes)) {
        throw new Exception('Invalid file type. Allowed: ' . implode(', ', $allowedTypes));
    }
    
    // Generate unique filename
    $extension = pathinfo($file['name'], PATHINFO_EXTENSION);
    $filename = uniqid() . '_' . time() . '.' . $extension;
    $filepath = $uploadDir . $filename;
    
    // Move uploaded file
    if (!move_uploaded_file($file['tmp_name'], $filepath)) {
        throw new Exception('Failed to save file');
    }
    
    // Generate URL
    $baseUrl = 'http://' . $_SERVER['HTTP_HOST'] . '/event-booking-website/public/uploads/' . $type . '/';
    $fileUrl = $baseUrl . $filename;
    
    return [
        'success' => true,
        'url' => $fileUrl,
        'filename' => $filename
    ];
}

try {
    // Create database connection
    $database = new Database();
    $db = $database->getConnection();
    
    // Check if controller exists before including
    $controllerPath = $projectRoot . '/app/controllers/AdminController.php';
    if (!file_exists($controllerPath)) {
        throw new Exception('AdminController.php not found at: ' . $controllerPath);
    }
    
    // Include the controller
    require_once $controllerPath;
    
    // Create admin controller
    $adminController = new AdminController($db);
    
    $action = $_GET['action'] ?? '';
    
    switch ($_SERVER['REQUEST_METHOD']) {
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
            echo json_encode([
                'success' => false, 
                'message' => 'Invalid request method'
            ]);
    }
    
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Server error: ' . $e->getMessage(),
        'error_type' => get_class($e),
        'debug' => $debugInfo
    ]);
}

function handleGetRequest($adminController, $action) {
    switch ($action) {
        case 'getAll':
            try {
                $mainCategories = $adminController->getAllMainCategories();
                $subcategories = $adminController->getAllSubcategories();
                
                echo json_encode([
                    'success' => true,
                    'mainCategories' => $mainCategories,
                    'subcategories' => $subcategories,
                    'counts' => [
                        'mainCategories' => count($mainCategories),
                        'subcategories' => count($subcategories)
                    ]
                ]);
            } catch (Exception $e) {
                echo json_encode([
                    'success' => false,
                    'message' => 'Error fetching data: ' . $e->getMessage()
                ]);
            }
            break;
            
        case 'getOne':
            $id = $_GET['id'] ?? 0;
            if ($id) {
                try {
                    $subcategory = $adminController->getSubcategory($id);
                    if ($subcategory) {
                        echo json_encode([
                            'success' => true,
                            'data' => $subcategory
                        ]);
                    } else {
                        echo json_encode([
                            'success' => false,
                            'message' => 'Subcategory not found'
                        ]);
                    }
                } catch (Exception $e) {
                    echo json_encode([
                        'success' => false,
                        'message' => 'Error fetching subcategory: ' . $e->getMessage()
                    ]);
                }
            } else {
                echo json_encode(['success' => false, 'message' => 'ID is required']);
            }
            break;
            
        case 'test':
            // Test endpoint
            echo json_encode([
                'success' => true,
                'message' => 'API is working',
                'timestamp' => date('Y-m-d H:i:s'),
                'action' => $action
            ]);
            break;
            
        default:
            echo json_encode(['success' => false, 'message' => 'Invalid action']);
    }
}

function handlePostRequest($adminController, $action) {
    if ($action === 'create') {
        try {
            // Get JSON data from request body
            $json = file_get_contents('php://input');
            $data = json_decode($json, true);
            
            if (json_last_error() !== JSON_ERROR_NONE) {
                throw new Exception('Invalid JSON data: ' . json_last_error_msg());
            }
            
            if (empty($data['main_category_id']) || empty($data['name'])) {
                echo json_encode([
                    'success' => false, 
                    'message' => 'Main category ID and name are required',
                    'received' => $data
                ]);
                return;
            }
            
            $result = $adminController->createSubcategory($data);
            
            if ($result) {
                echo json_encode([
                    'success' => true, 
                    'message' => 'Subcategory created successfully',
                    'id' => $adminController->subcategory->id ?? null
                ]);
            } else {
                echo json_encode([
                    'success' => false, 
                    'message' => 'Failed to create subcategory'
                ]);
            }
        } catch (Exception $e) {
            echo json_encode([
                'success' => false,
                'message' => 'Error creating subcategory: ' . $e->getMessage()
            ]);
        }
    }
}

function handlePutRequest($adminController, $action) {
    if ($action === 'update') {
        try {
            $id = $_GET['id'] ?? 0;
            
            if (!$id) {
                echo json_encode([
                    'success' => false, 
                    'message' => 'ID is required'
                ]);
                return;
            }
            
            // Get the JSON data from request body
            $json = file_get_contents('php://input');
            $data = json_decode($json, true);
            
            // Check for JSON decode error
            if (json_last_error() !== JSON_ERROR_NONE) {
                throw new Exception('Invalid JSON data: ' . json_last_error_msg());
            }
            
            // Log received data for debugging
            error_log("Update data received for ID {$id}: " . print_r($data, true));
            
            if (empty($data['main_category_id']) || empty($data['name'])) {
                echo json_encode([
                    'success' => false, 
                    'message' => 'Main category ID and name are required',
                    'received_data' => $data
                ]);
                return;
            }
            
            $result = $adminController->updateSubcategory($id, $data);
            
            if ($result) {
                echo json_encode([
                    'success' => true, 
                    'message' => 'Subcategory updated successfully'
                ]);
            } else {
                echo json_encode([
                    'success' => false, 
                    'message' => 'Failed to update subcategory'
                ]);
            }
        } catch (Exception $e) {
            http_response_code(500);
            echo json_encode([
                'success' => false,
                'message' => 'Error updating subcategory: ' . $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
        }
    }
}

function handleDeleteRequest($adminController, $action) {
    if ($action === 'delete') {
        try {
            $id = $_GET['id'] ?? 0;
            
            if (!$id) {
                echo json_encode([
                    'success' => false, 
                    'message' => 'ID is required'
                ]);
                return;
            }
            
            // Check if subcategory exists first
            $subcategory = $adminController->getSubcategory($id);
            if (!$subcategory) {
                echo json_encode([
                    'success' => false,
                    'message' => 'Subcategory not found'
                ]);
                return;
            }
            
            $result = $adminController->deleteSubcategory($id);
            
            if ($result) {
                echo json_encode([
                    'success' => true, 
                    'message' => 'Subcategory deleted successfully'
                ]);
            } else {
                echo json_encode([
                    'success' => false, 
                    'message' => 'Failed to delete subcategory'
                ]);
            }
        } catch (Exception $e) {
            echo json_encode([
                'success' => false,
                'message' => 'Error deleting subcategory: ' . $e->getMessage()
            ]);
        }
    }
}
?>
<?php
// Start output buffering to catch any errors
ob_start();

// Set headers early
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST');
header('Access-Control-Allow-Headers: Content-Type');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    ob_clean();
    http_response_code(200);
    exit();
}

// Suppress error display - we'll return JSON errors instead
error_reporting(E_ALL);
ini_set('display_errors', 0);
ini_set('log_errors', 1);

// Try to load Cloudinary service (suppress errors)
$useCloudinary = false;
$cloudinaryService = null;
try {
    // Check if vendor/autoload.php exists (composer dependencies)
    $vendorPath = __DIR__ . '/../../vendor/autoload.php';
    if (file_exists($vendorPath)) {
        require_once $vendorPath;
    }
    
    $cloudinaryServicePath = __DIR__ . '/../../app/services/CloudinaryService.php';
    if (file_exists($cloudinaryServicePath)) {
        require_once $cloudinaryServicePath;
        $cloudinaryService = new CloudinaryService();
        $useCloudinary = $cloudinaryService->isEnabled();
    }
} catch (Throwable $e) {
    // Silently fail - will use local storage
    error_log("Cloudinary not available: " . $e->getMessage());
    $useCloudinary = false;
    $cloudinaryService = null;
}

// Get absolute paths (fallback to local storage)
$currentDir = __DIR__;
$projectRoot = dirname(dirname($currentDir));
$uploadDir = $projectRoot . '/public/uploads/';

// Define allowed upload types and directories
$allowedTypes = [
    'subcategories' => [
        'dir' => $uploadDir . 'subcategories/',
        'max_size' => 5242880, // 5MB
        'allowed_mime' => ['image/jpeg', 'image/png', 'image/gif', 'image/webp']
    ],
    'events' => [
        'dir' => $uploadDir . 'events/',
        'max_size' => 10485760, // 10MB
        'allowed_mime' => ['image/jpeg', 'image/png', 'image/gif', 'image/webp']
    ],
    'event_gallery' => [
        'dir' => $uploadDir . 'events/gallery/',
        'max_size' => 10485760, // 10MB
        'allowed_mime' => ['image/jpeg', 'image/png', 'image/gif', 'image/webp']
    ]
];

// Ensure upload directories exist
foreach ($allowedTypes as $type => $config) {
    if (!file_exists($config['dir'])) {
        mkdir($config['dir'], 0755, true);
    }
}

try {
    $type = $_POST['type'] ?? 'events';
    
    if (!isset($allowedTypes[$type])) {
        throw new Exception('Invalid upload type');
    }
    
    $config = $allowedTypes[$type];
    
    if (!isset($_FILES['image'])) {
        throw new Exception('No file uploaded');
    }
    
    $file = $_FILES['image'];
    
    // Check for upload errors
    if ($file['error'] !== UPLOAD_ERR_OK) {
        throw new Exception('Upload error: ' . $file['error']);
    }
    
    // Check file size
    if ($file['size'] > $config['max_size']) {
        throw new Exception('File too large. Maximum size: ' . ($config['max_size'] / 1024 / 1024) . 'MB');
    }
    
    // Check file type
    $finfo = finfo_open(FILEINFO_MIME_TYPE);
    $mime = finfo_file($finfo, $file['tmp_name']);
    finfo_close($finfo);
    
    if (!in_array($mime, $config['allowed_mime'])) {
        throw new Exception('Invalid file type. Allowed: ' . implode(', ', $config['allowed_mime']));
    }
    
    // Try Cloudinary first, fallback to local storage
    if ($useCloudinary && $cloudinaryService) {
        try {
            // Map upload type to Cloudinary folder
            $folderMap = [
                'subcategories' => 'subcategories',
                'events' => 'events',
                'event_gallery' => 'events/gallery'
            ];
            $folder = $folderMap[$type] ?? 'uploads';
            
            $result = $cloudinaryService->uploadImage($file, $folder);
            
            if ($result && isset($result['success']) && $result['success']) {
                // Clean any output before sending JSON
                ob_clean();
                
                // Return Cloudinary URL (full URL, not relative path)
                echo json_encode([
                    'success' => true,
                    'url' => $result['url'], // Full Cloudinary URL
                    'cloudinary_url' => $result['url'],
                    'public_id' => $result['public_id'] ?? null,
                    'message' => 'File uploaded successfully to Cloudinary',
                    'storage' => 'cloudinary'
                ]);
                exit;
            } else {
                // Cloudinary failed, fall back to local
                $errorMsg = $result['message'] ?? 'Unknown error';
                error_log("Cloudinary upload failed: " . $errorMsg . " - Falling back to local storage");
            }
        } catch (Throwable $e) {
            // If Cloudinary throws an exception, log it and fall back to local
            error_log("Cloudinary upload exception: " . $e->getMessage());
            error_log("Cloudinary upload stack trace: " . $e->getTraceAsString());
            // Continue to local storage fallback
        }
    }
    
    // Fallback to local storage
    // Generate unique filename
    $extension = pathinfo($file['name'], PATHINFO_EXTENSION);
    $filename = uniqid() . '_' . time() . '.' . $extension;
    $filepath = $config['dir'] . $filename;
    
    // Move uploaded file
    if (!move_uploaded_file($file['tmp_name'], $filepath)) {
        throw new Exception('Failed to save file');
    }
    
    // Generate relative path only (not full URL) - like "uploads/events/file.jpg"
    // This will be normalized by imageUrl() helper when displayed
    $relativePath = str_replace($projectRoot . '/public/', '', $filepath);
    // Ensure it starts with "uploads/"
    if (strpos($relativePath, 'uploads/') !== 0) {
        $relativePath = 'uploads/' . basename($relativePath);
    }
    
    // Clean any output before sending JSON
    ob_clean();
    
    echo json_encode([
        'success' => true,
        'url' => $relativePath, // Return relative path only, not full URL
        'filename' => $filename,
        'message' => 'File uploaded successfully (local storage - will be lost on redeploy)',
        'storage' => 'local',
        'warning' => 'Cloudinary not configured. Images will be lost on Railway redeploy.'
    ]);
    
} catch (Exception $e) {
    // Clean any output before sending JSON error
    ob_clean();
    
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
} catch (Throwable $e) {
    // Catch any other errors (fatal errors, etc.)
    ob_clean();
    
    // Log the full error for debugging
    error_log("Upload API fatal error: " . $e->getMessage());
    error_log("Upload API stack trace: " . $e->getTraceAsString());
    error_log("Upload API file: " . $e->getFile() . " line: " . $e->getLine());
    
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Upload failed: ' . $e->getMessage(),
        'error_type' => get_class($e),
        'error_file' => basename($e->getFile()),
        'error_line' => $e->getLine()
    ]);
}

// End output buffering
ob_end_flush();
?>
<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST');
header('Access-Control-Allow-Headers: Content-Type');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

error_reporting(E_ALL);
ini_set('display_errors', 1);

// Try to load Cloudinary service
$useCloudinary = false;
$cloudinaryService = null;
try {
    require_once __DIR__ . '/../../app/services/CloudinaryService.php';
    $cloudinaryService = new CloudinaryService();
    $useCloudinary = $cloudinaryService->isEnabled();
} catch (Exception $e) {
    error_log("Cloudinary not available: " . $e->getMessage());
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
        // Map upload type to Cloudinary folder
        $folderMap = [
            'subcategories' => 'subcategories',
            'events' => 'events',
            'event_gallery' => 'events/gallery'
        ];
        $folder = $folderMap[$type] ?? 'uploads';
        
        $result = $cloudinaryService->uploadImage($file, $folder);
        
        if ($result['success']) {
            // Return Cloudinary URL (full URL, not relative path)
            echo json_encode([
                'success' => true,
                'url' => $result['url'], // Full Cloudinary URL
                'cloudinary_url' => $result['url'],
                'public_id' => $result['public_id'],
                'message' => 'File uploaded successfully to Cloudinary'
            ]);
            exit;
        } else {
            // Cloudinary failed, fall back to local
            error_log("Cloudinary upload failed: " . ($result['message'] ?? 'Unknown error') . " - Falling back to local storage");
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
    
    echo json_encode([
        'success' => true,
        'url' => $relativePath, // Return relative path only, not full URL
        'filename' => $filename,
        'message' => 'File uploaded successfully (local storage)'
    ]);
    
} catch (Exception $e) {
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}
?>
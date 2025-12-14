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

// Get absolute paths
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
    
    // Generate unique filename
    $extension = pathinfo($file['name'], PATHINFO_EXTENSION);
    $filename = uniqid() . '_' . time() . '.' . $extension;
    $filepath = $config['dir'] . $filename;
    
    // Move uploaded file
    if (!move_uploaded_file($file['tmp_name'], $filepath)) {
        throw new Exception('Failed to save file');
    }
    
    // Generate URL relative to public directory
    $relativePath = str_replace($projectRoot . '/public/', '', $filepath);
    $baseUrl = 'http://' . $_SERVER['HTTP_HOST'] . dirname(dirname($_SERVER['PHP_SELF'])) . '/';
    $fileUrl = $baseUrl . $relativePath;
    
    echo json_encode([
        'success' => true,
        'url' => $fileUrl,
        'filename' => $filename,
        'message' => 'File uploaded successfully'
    ]);
    
} catch (Exception $e) {
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}
?>
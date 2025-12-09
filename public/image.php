<?php
// Image proxy/serving endpoint for profile pictures
// This ensures images are accessible regardless of path issues

$imagePath = $_GET['path'] ?? '';

if (empty($imagePath)) {
    // Return a proper default avatar instead of transparent pixel
    header('Content-Type: image/svg+xml');
    echo '<?xml version="1.0" encoding="UTF-8"?><svg width="100" height="100" xmlns="http://www.w3.org/2000/svg"><rect width="100" height="100" fill="#1f2937"/><circle cx="50" cy="40" r="18" fill="#4b5563"/><path d="M20 85 Q20 70 50 70 Q80 70 80 85" fill="#4b5563"/></svg>';
    exit;
}

// Security: Only allow paths within uploads directory
$imagePath = str_replace(['..', '\\'], ['', '/'], $imagePath);
$imagePath = ltrim($imagePath, '/');

// Construct full file path
// imagePath is like "uploads/profile_pics/prof_68f76afade590.jpg"
// public/image.php is at: public/image.php
// uploads/ is at: uploads/ (root level)
// So from public/, we go up one level: ../uploads/...
$fullPath = __DIR__ . '/../' . $imagePath;

// Check if file exists and is within allowed directory
if (!file_exists($fullPath) || !is_file($fullPath)) {
    // Try alternative path (in case uploads is in a different location)
    $altPath = __DIR__ . '/../../uploads/' . str_replace('uploads/', '', $imagePath);
    if (file_exists($altPath) && is_file($altPath)) {
        $fullPath = $altPath;
    } else {
        // File doesn't exist - redirect to a placeholder service
        // Using a simple data URI for a gray avatar icon
        header('Content-Type: image/svg+xml');
        echo '<?xml version="1.0" encoding="UTF-8"?><svg width="100" height="100" xmlns="http://www.w3.org/2000/svg"><rect width="100" height="100" fill="#1f2937"/><circle cx="50" cy="40" r="18" fill="#4b5563"/><path d="M20 85 Q20 70 50 70 Q80 70 80 85" fill="#4b5563"/></svg>';
        exit;
    }
}

// Check if file is actually in uploads directory (only if file exists)
if (file_exists($fullPath)) {
    $realPath = realpath($fullPath);
    $uploadsDir = realpath(__DIR__ . '/../uploads');
    if ($uploadsDir && strpos($realPath, $uploadsDir) !== 0) {
        // Security violation - return default avatar instead of transparent pixel
        header('Content-Type: image/svg+xml');
        echo '<?xml version="1.0" encoding="UTF-8"?><svg width="100" height="100" xmlns="http://www.w3.org/2000/svg"><rect width="100" height="100" fill="#1f2937"/><circle cx="50" cy="40" r="18" fill="#4b5563"/><path d="M20 85 Q20 70 50 70 Q80 70 80 85" fill="#4b5563"/></svg>';
        exit;
    }
}

// Determine content type
$ext = strtolower(pathinfo($fullPath, PATHINFO_EXTENSION));
$mimeTypes = [
    'jpg' => 'image/jpeg',
    'jpeg' => 'image/jpeg',
    'png' => 'image/png',
    'gif' => 'image/gif',
];

if (isset($mimeTypes[$ext])) {
    header('Content-Type: ' . $mimeTypes[$ext]);
} else {
    header('Content-Type: image/jpeg');
}

readfile($fullPath);

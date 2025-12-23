<?php
/**
 * Cloudinary Status Checker
 * Use this to verify Cloudinary is configured correctly
 * Access: /check_cloudinary.php
 */

header('Content-Type: application/json');

$status = [
    'composer_installed' => false,
    'cloudinary_sdk_available' => false,
    'cloudinary_configured' => false,
    'environment_variables' => [],
    'test_upload' => false,
    'errors' => []
];

// Check if Composer is installed
$vendorPath = __DIR__ . '/../vendor/autoload.php';
if (file_exists($vendorPath)) {
    $status['composer_installed'] = true;
    try {
        require_once $vendorPath;
    } catch (Exception $e) {
        $status['errors'][] = 'Failed to load vendor/autoload.php: ' . $e->getMessage();
    }
} else {
    $status['errors'][] = 'vendor/autoload.php not found. Run: composer install';
}

// Check if Cloudinary SDK is available
if (class_exists('Cloudinary\Cloudinary')) {
    $status['cloudinary_sdk_available'] = true;
} else {
    $status['errors'][] = 'Cloudinary SDK not found. Install: composer require cloudinary/cloudinary_php';
}

// Check environment variables
$cloudName = getenv('CLOUDINARY_CLOUD_NAME') ?: '';
$apiKey = getenv('CLOUDINARY_API_KEY') ?: '';
$apiSecret = getenv('CLOUDINARY_API_SECRET') ?: '';

$status['environment_variables'] = [
    'CLOUDINARY_CLOUD_NAME' => !empty($cloudName) ? '✓ Set' : '✗ Not set',
    'CLOUDINARY_API_KEY' => !empty($apiKey) ? '✓ Set' : '✗ Not set',
    'CLOUDINARY_API_SECRET' => !empty($apiSecret) ? '✓ Set' : '✗ Not set'
];

// Check if Cloudinary is fully configured
if ($status['cloudinary_sdk_available'] && !empty($cloudName) && !empty($apiKey) && !empty($apiSecret)) {
    $status['cloudinary_configured'] = true;
    
    // Try to initialize Cloudinary
    try {
        require_once __DIR__ . '/../app/services/CloudinaryService.php';
        $cloudinaryService = new CloudinaryService();
        if ($cloudinaryService->isEnabled()) {
            $status['test_upload'] = 'Cloudinary service initialized successfully';
        } else {
            $status['errors'][] = 'Cloudinary service not enabled despite credentials being set';
        }
    } catch (Exception $e) {
        $status['errors'][] = 'Failed to initialize CloudinaryService: ' . $e->getMessage();
    }
} else {
    if (!$status['cloudinary_sdk_available']) {
        $status['errors'][] = 'Cloudinary SDK not available';
    }
    if (empty($cloudName) || empty($apiKey) || empty($apiSecret)) {
        $status['errors'][] = 'Cloudinary environment variables not set in Railway';
    }
}

// Summary
$status['summary'] = $status['cloudinary_configured'] 
    ? '✓ Cloudinary is fully configured and ready to use'
    : '✗ Cloudinary is NOT configured. Check errors above.';

echo json_encode($status, JSON_PRETTY_PRINT);


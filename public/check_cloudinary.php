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
    
    // Additional diagnostics
    $vendorDir = __DIR__ . '/../vendor';
    if (is_dir($vendorDir)) {
        $status['vendor_dir_exists'] = true;
        $status['vendor_contents'] = array_values(array_filter(scandir($vendorDir), function($item) {
            return $item !== '.' && $item !== '..';
        }));
        
        // Check for Cloudinary package
        $cloudinaryDirs = [
            __DIR__ . '/../vendor/cloudinary/cloudinary_php',
            __DIR__ . '/../vendor/cloudinary/cloudinary-php',
            __DIR__ . '/../vendor/cloudinary'
        ];
        
        foreach ($cloudinaryDirs as $dir) {
            if (is_dir($dir)) {
                $status['cloudinary_dir_found'] = $dir;
                $status['cloudinary_dir_contents'] = array_slice(scandir($dir), 0, 10);
                break;
            }
        }
        
        // Check specific file paths
        $cloudinaryPaths = [
            __DIR__ . '/../vendor/cloudinary/cloudinary_php/src/Cloudinary.php',
            __DIR__ . '/../vendor/cloudinary/cloudinary-php/src/Cloudinary.php',
            __DIR__ . '/../vendor/cloudinary/cloudinary_php/Cloudinary.php'
        ];
        
        foreach ($cloudinaryPaths as $path) {
            if (file_exists($path)) {
                $status['cloudinary_file_found'] = $path;
                // Try to manually require it
                try {
                    require_once $path;
                    if (class_exists('Cloudinary\Cloudinary')) {
                        $status['cloudinary_sdk_available'] = true;
                        $status['errors'] = array_filter($status['errors'], function($err) {
                            return strpos($err, 'Cloudinary SDK not found') === false;
                        });
                    }
                } catch (Exception $e) {
                    $status['errors'][] = 'Failed to load Cloudinary.php: ' . $e->getMessage();
                }
                break;
            }
        }
    } else {
        $status['vendor_dir_exists'] = false;
        $status['errors'][] = 'Vendor directory not found at: ' . $vendorDir;
    }
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


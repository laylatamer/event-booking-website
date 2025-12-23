<?php
/**
 * Path Helper for Railway Deployment
 * 
 * Determines the correct base path for assets (CSS, JS, images)
 * Works for both local development and Railway deployment
 */

// Determine if we're in Railway (serving from /app/public) or local
$isRailway = (getenv('RAILWAY_ENVIRONMENT') !== false || 
              file_exists('/app') || 
              (isset($_SERVER['SERVER_SOFTWARE']) && strpos($_SERVER['SERVER_SOFTWARE'], 'PHP') !== false && !file_exists(__DIR__ . '/../../.env')));

// Base URL for assets
// In Railway: / (since we serve from /app/public, which is the web root)
// Locally: /event-booking-website/public/ or /public/ depending on setup
if (!defined('BASE_ASSETS_PATH')) {
    if ($isRailway) {
        // Railway: public is the web root
        define('BASE_ASSETS_PATH', '/');
    } else {
        // Local development: check if we're in a subdirectory
        $scriptName = $_SERVER['SCRIPT_NAME'] ?? '';
        if (strpos($scriptName, '/event-booking-website/') !== false) {
            define('BASE_ASSETS_PATH', '/event-booking-website/public/');
        } else {
            define('BASE_ASSETS_PATH', '/public/');
        }
    }
}

// Helper function to get asset path
function asset($path) {
    // Remove leading slash if present
    $path = ltrim($path, '/');
    // Remove 'public/' prefix if present (for compatibility)
    $path = preg_replace('#^public/#', '', $path);
    return BASE_ASSETS_PATH . $path;
}

/**
 * Helper function to normalize image URLs
 * Uses image.php proxy endpoint (same as profile pictures) for reliable access
 * Handles both absolute URLs (http/https) and relative paths
 */
function imageUrl($url) {
    if (empty($url)) {
        return '/image.php?path=';
    }
    
    // If it's already an absolute URL (external), return as-is
    if (preg_match('/^https?:\/\//', $url)) {
        return $url;
    }
    
    // If it's already using the image.php proxy, return as-is
    if (strpos($url, '/image.php?path=') === 0) {
        return $url;
    }
    
    // Clean up the path (remove backslashes, normalize)
    $url = str_replace('\\', '/', trim($url));
    
    // Remove leading slash if present (we'll add it back via proxy)
    $url = ltrim($url, '/');
    
    // If it's in uploads directory (most common case)
    // Use image.php proxy endpoint (same as profile pictures) for reliable access
    if (strpos($url, 'uploads/') === 0) {
        return '/image.php?path=' . urlencode($url);
    }
    
    // If it contains uploads anywhere, extract and use proxy
    if (strpos($url, 'uploads/') !== false) {
        $parts = explode('uploads/', $url);
        if (count($parts) > 1) {
            return '/image.php?path=' . urlencode('uploads/' . $parts[1]);
        }
    }
    
    // For other paths, also use proxy for consistency
    return '/image.php?path=' . urlencode($url);
}


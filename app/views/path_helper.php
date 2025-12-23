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
 * Handles both absolute URLs (http/https) and relative paths
 * Ensures images are accessible via web (uploads/ paths become /uploads/)
 */
function imageUrl($url) {
    if (empty($url)) {
        return 'https://placehold.co/400x400/2a2a2a/f97316?text=Event';
    }
    
    // If it's already an absolute URL, return as-is
    if (preg_match('/^https?:\/\//', $url)) {
        return $url;
    }
    
    // Clean up the path (remove backslashes, normalize)
    $url = str_replace('\\', '/', trim($url));
    
    // If it starts with /, it's already an absolute path - return as-is
    if (strpos($url, '/') === 0) {
        return $url;
    }
    
    // If it's in uploads directory (most common case)
    // Paths like "uploads/events/file.jpg" should become "/uploads/events/file.jpg"
    if (strpos($url, 'uploads/') === 0) {
        return '/' . $url; // Make it absolute from web root
    }
    
    // If it contains uploads anywhere, extract and make absolute
    if (strpos($url, 'uploads/') !== false) {
        $parts = explode('uploads/', $url);
        if (count($parts) > 1) {
            return '/uploads/' . $parts[1];
        }
    }
    
    // Default: prepend with base assets path
    return BASE_ASSETS_PATH . ltrim($url, '/');
}


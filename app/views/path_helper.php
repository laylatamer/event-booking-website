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


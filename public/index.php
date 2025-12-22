<?php
/**
 * Main entry point for the application
 * Routes to homepage
 */

// Get the base path - adjust for Railway deployment
$basePath = dirname(__DIR__);

// Include homepage view
// Check if we're in Railway (public is root) or local (project root)
if (file_exists($basePath . '/app/views/homepage.php')) {
    // Project root structure (local or Railway with project root)
    require_once $basePath . '/app/views/homepage.php';
} elseif (file_exists(__DIR__ . '/../app/views/homepage.php')) {
    // Alternative path
    require_once __DIR__ . '/../app/views/homepage.php';
} else {
    // Fallback: try to find it
    $possiblePaths = [
        '/app/app/views/homepage.php',
        dirname(__DIR__) . '/app/views/homepage.php',
        __DIR__ . '/../app/views/homepage.php',
    ];
    
    $found = false;
    foreach ($possiblePaths as $path) {
        if (file_exists($path)) {
            require_once $path;
            $found = true;
            break;
        }
    }
    
    if (!$found) {
        die("Error: Could not find homepage.php. Please check your file structure.");
    }
}


<?php
/**
 * Main entry point for the application
 * Routes to homepage
 * 
 * This file handles path resolution for Railway deployment
 * where public/ might be the root directory
 */

// Determine project root
$projectRoot = null;
$scriptDir = __DIR__;

// Try to find project root by looking for config/ or app/ directories
$possibleRoots = [
    dirname($scriptDir),           // Parent of public (if public is subdirectory)
    $scriptDir,                     // Current dir (if public is root)
    '/app',                         // Railway root
];

foreach ($possibleRoots as $root) {
    if (is_dir($root . '/config') || is_dir($root . '/app')) {
        $projectRoot = $root;
        break;
    }
}

// If we're in public/ and it's the root, parent directories might be at /app/../ 
// But Railway copies only public/, so we need to check if app/ exists
if (!$projectRoot) {
    // Check if app/views exists relative to current directory
    if (file_exists($scriptDir . '/../app/views/homepage.php')) {
        $projectRoot = dirname($scriptDir);
    } elseif (file_exists('/app/app/views/homepage.php')) {
        $projectRoot = '/app';
    }
}

// If still not found, show error with debug info
if (!$projectRoot || !file_exists($projectRoot . '/app/views/homepage.php')) {
    die("Error: Could not find homepage.php<br><br>" .
        "Script directory: " . htmlspecialchars($scriptDir) . "<br>" .
        "Project root: " . ($projectRoot ? htmlspecialchars($projectRoot) : 'Not found') . "<br>" .
        "Files in script dir: " . implode(', ', array_slice(scandir($scriptDir), 0, 10)) . "<br>" .
        "Please ensure Railway's root directory is set to project root (not 'public')");
}

// Include homepage - it will handle its own path resolution
require_once $projectRoot . '/app/views/homepage.php';


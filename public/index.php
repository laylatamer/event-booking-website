<?php
/**
 * Main Router for the Application
 * 
 * Handles all page requests and routes them to the correct view files
 * Works for both local development and Railway deployment
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
    die("Error: Could not find project structure<br><br>" .
        "Script directory: " . htmlspecialchars($scriptDir) . "<br>" .
        "Project root: " . ($projectRoot ? htmlspecialchars($projectRoot) : 'Not found') . "<br>" .
        "Files in script dir: " . implode(', ', array_slice(scandir($scriptDir), 0, 10)) . "<br>" .
        "Please ensure Railway's root directory is set to project root (not 'public')");
}

// Get the requested page from URL
$requestUri = $_SERVER['REQUEST_URI'] ?? '/';
$requestPath = parse_url($requestUri, PHP_URL_PATH);

// Remove query string and leading/trailing slashes
$requestPath = trim($requestPath, '/');

// Map URLs to view files
$routes = [
    '' => 'homepage.php',
    'index.php' => 'homepage.php',
    'homepage.php' => 'homepage.php',
    'allevents.php' => 'allevents.php',
    'booking.php' => 'booking.php',
    'checkout.php' => 'checkout.php',
    'booking_confirmation.php' => 'booking_confirmation.php',
    'contact_form.php' => 'contact_form.php',
    'contact.php' => 'contact_form.php',
    'faq.php' => 'faq.php',
    'profile.php' => 'profile.php',
    'auth.php' => 'auth.php',
    'logout.php' => 'logout.php',
    'sports.php' => 'sports.php',
    'entertainment.php' => 'entertainment.php',
    'customize_tickets.php' => 'customize_tickets.php',
    'ticket.php' => 'ticket.php',
    'ticket_verification.php' => 'ticket_verification.php',
    'terms&conditions.php' => 'terms&conditions.php',
    'admin' => 'admin/index.php',
    'admin/' => 'admin/index.php',
    'admin/index.php' => 'admin/index.php',
];

// Handle API requests - let them pass through
if (strpos($requestPath, 'api/') === 0) {
    // API requests are handled by their own files in public/api/
    return false; // Let PHP server handle it
}

// Handle static files (CSS, JS, images) - let them pass through
$staticExtensions = ['css', 'js', 'jpg', 'jpeg', 'png', 'gif', 'svg', 'webp', 'ico', 'json'];
$extension = pathinfo($requestPath, PATHINFO_EXTENSION);
if (in_array(strtolower($extension), $staticExtensions)) {
    return false; // Let PHP server handle static files
}

// Determine which view file to load
$viewFile = null;

// Check if it's a direct route
if (isset($routes[$requestPath])) {
    $viewFile = $routes[$requestPath];
} else {
    // Try to match the request path directly to a view file
    $viewFile = basename($requestPath);
    if (!file_exists($projectRoot . '/app/views/' . $viewFile)) {
        $viewFile = null;
    }
}

// Default to homepage if no route matches
if (!$viewFile) {
    $viewFile = 'homepage.php';
}

// Check if view file exists
$viewPath = $projectRoot . '/app/views/' . $viewFile;
if (!file_exists($viewPath)) {
    http_response_code(404);
    die("Page not found: " . htmlspecialchars($requestPath));
}

// Include the view file
require_once $viewPath;


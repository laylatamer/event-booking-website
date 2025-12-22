<?php
/**
 * Router script for PHP built-in server
 * 
 * Add this to your Dockerfile CMD:
 * php -S 0.0.0.0:$PORT -t public public/router.php
 */

// Determine project root
$projectRoot = dirname(__DIR__);
if (!is_dir($projectRoot . '/app/views')) {
    $projectRoot = '/app';
}

// Get the requested path
$requestUri = $_SERVER['REQUEST_URI'] ?? '/';
$requestPath = parse_url($requestUri, PHP_URL_PATH);
$requestPath = trim($requestPath, '/');

// Handle static files - serve them directly
$staticExtensions = ['css', 'js', 'jpg', 'jpeg', 'png', 'gif', 'svg', 'webp', 'ico', 'json', 'pdf'];
$extension = pathinfo($requestPath, PATHINFO_EXTENSION);
if (in_array(strtolower($extension), $staticExtensions)) {
    return false; // Let server serve static files
}

// Handle API requests
if (strpos($requestPath, 'api/') === 0) {
    return false; // Let API files handle themselves
}

// Handle utility scripts in public directory (check_database.php, fix_chatbot_tables.php, etc.)
$utilityScripts = ['check_database.php', 'fix_chatbot_tables.php', 'import_database.php', 'db_connect_railway.php'];
if (in_array($requestPath, $utilityScripts) || in_array(basename($requestPath), $utilityScripts)) {
    $scriptPath = __DIR__ . '/' . basename($requestPath);
    if (file_exists($scriptPath)) {
        require $scriptPath;
        exit;
    }
}

// Map routes to view files
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
];

// Get view file
$viewFile = $routes[$requestPath] ?? basename($requestPath);

// Check if view exists
$viewPath = $projectRoot . '/app/views/' . $viewFile;
if (!file_exists($viewPath)) {
    http_response_code(404);
    echo "404 - Page not found: " . htmlspecialchars($requestPath);
    exit;
}

// Include the view
require $viewPath;


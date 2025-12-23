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

// Preserve query string for admin routes
$queryString = parse_url($requestUri, PHP_URL_QUERY);
if ($queryString) {
    $_SERVER['QUERY_STRING'] = $queryString;
    parse_str($queryString, $_GET);
}

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

// Handle utility scripts in public directory (only if they exist)
$utilityScripts = ['run_migration.php'];
if (in_array($requestPath, $utilityScripts) || in_array(basename($requestPath), $utilityScripts)) {
    $scriptPath = __DIR__ . '/' . basename($requestPath);
    if (file_exists($scriptPath)) {
        require $scriptPath;
        exit;
    }
}

// Handle admin routes - must check BEFORE other routes
if (strpos($requestPath, 'admin') === 0 || $requestPath === 'admin') {
    $adminPath = preg_replace('#^admin/?#', '', $requestPath); // Remove 'admin' or 'admin/' prefix
    $adminPath = $adminPath ?: 'index.php';
    
    // Ensure .php extension
    if (!preg_match('/\.php$/', $adminPath)) {
        $adminPath .= '.php';
    }
    
    $adminViewPath = $projectRoot . '/app/views/admin/' . $adminPath;
    
    // Debug: log the path being checked
    error_log("Admin route check: requestPath='$requestPath', adminPath='$adminPath', fullPath='$adminViewPath', exists=" . (file_exists($adminViewPath) ? 'yes' : 'no'));
    
    if (file_exists($adminViewPath)) {
        require $adminViewPath;
        exit;
    } else {
        // Redirect to error page for 404
        http_response_code(404);
        $errorPage = $projectRoot . '/app/views/error.php';
        if (file_exists($errorPage)) {
            $_GET['code'] = 404;
            require $errorPage;
            exit;
        } else {
            echo "404 - Admin page not found: " . htmlspecialchars($adminPath);
            exit;
        }
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
    'contact.php' => 'contact.php', // Handle contact.php in public directory
    'error.php' => 'error.php', // Allow direct access to error page
];

// Get view file
$viewFile = $routes[$requestPath] ?? basename($requestPath);

// Check if it's a file in public directory (like contact.php)
$publicFilePath = __DIR__ . '/' . basename($requestPath);
if (file_exists($publicFilePath) && basename($requestPath) === $viewFile && $viewFile !== 'contact_form.php') {
    require $publicFilePath;
    exit;
}

// Check if view exists
$viewPath = $projectRoot . '/app/views/' . $viewFile;
if (!file_exists($viewPath)) {
    // Redirect to error page for 404
    http_response_code(404);
    $errorPage = $projectRoot . '/app/views/error.php';
    if (file_exists($errorPage)) {
        $_GET['code'] = 404;
        require $errorPage;
        exit;
    } else {
        echo "404 - Page not found: " . htmlspecialchars($requestPath);
        exit;
    }
}

// Include the view
require $viewPath;


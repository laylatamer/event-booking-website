<?php
/**
 * Centralized Error Handler
 * Catches PHP errors, exceptions, and fatal errors and redirects to custom error page
 */

// Prevent infinite loops - if we're already handling an error, don't handle it again
if (defined('ERROR_HANDLER_ACTIVE')) {
    return;
}
define('ERROR_HANDLER_ACTIVE', true);

// Define error log path
define('ERROR_LOG_PATH', __DIR__ . '/../logs/error.log');

// Ensure logs directory exists
$logsDir = dirname(ERROR_LOG_PATH);
if (!file_exists($logsDir)) {
    @mkdir($logsDir, 0755, true);
}

/**
 * Custom error handler for PHP errors (warnings, notices, etc.)
 */
function customErrorHandler($errno, $errstr, $errfile, $errline) {
    // Don't handle errors if error reporting is disabled
    if (!(error_reporting() & $errno)) {
        return false;
    }
    
    // Log the error
    $errorMsg = sprintf(
        "[%s] Error #%d: %s in %s on line %d\n",
        date('Y-m-d H:i:s'),
        $errno,
        $errstr,
        $errfile,
        $errline
    );
    @error_log($errorMsg, 3, ERROR_LOG_PATH);
    
    // Check if this is an API request
    $isApiRequest = (
        strpos($_SERVER['REQUEST_URI'] ?? '', '/api/') !== false ||
        strpos($_SERVER['SCRIPT_NAME'] ?? '', '/api/') !== false ||
        (isset($_SERVER['HTTP_ACCEPT']) && strpos($_SERVER['HTTP_ACCEPT'], 'application/json') !== false)
    );
    
    // For fatal errors, warnings, and user errors, redirect to error page
    if ($errno === E_ERROR || $errno === E_USER_ERROR) {
        if ($isApiRequest && !headers_sent()) {
            http_response_code(500);
            header('Content-Type: application/json');
            echo json_encode([
                'success' => false,
                'error' => 'An error occurred processing your request',
                'message' => $errstr
            ]);
            exit;
        }
        redirectToErrorPage(500, $errstr);
    }
    
    // Return false to let PHP handle the error normally if we don't redirect
    return false;
}

/**
 * Custom exception handler
 */
function customExceptionHandler($exception) {
    // Log the exception
    $errorMsg = sprintf(
        "[%s] Uncaught Exception: %s in %s on line %d\nStack trace:\n%s\n",
        date('Y-m-d H:i:s'),
        $exception->getMessage(),
        $exception->getFile(),
        $exception->getLine(),
        $exception->getTraceAsString()
    );
    @error_log($errorMsg, 3, ERROR_LOG_PATH);
    
    // Check if this is an API request
    $isApiRequest = (
        strpos($_SERVER['REQUEST_URI'] ?? '', '/api/') !== false ||
        strpos($_SERVER['SCRIPT_NAME'] ?? '', '/api/') !== false ||
        (isset($_SERVER['HTTP_ACCEPT']) && strpos($_SERVER['HTTP_ACCEPT'], 'application/json') !== false)
    );
    
    if ($isApiRequest && !headers_sent()) {
        // Return JSON error for API requests
        http_response_code(500);
        header('Content-Type: application/json');
        echo json_encode([
            'success' => false,
            'error' => 'An error occurred processing your request',
            'message' => $exception->getMessage()
        ]);
        exit;
    }
    
    // Redirect to error page for regular requests
    redirectToErrorPage(500, $exception->getMessage());
}

/**
 * Shutdown function to catch fatal errors
 */
function customShutdownHandler() {
    $error = error_get_last();
    
    if ($error !== null && in_array($error['type'], [E_ERROR, E_CORE_ERROR, E_COMPILE_ERROR, E_PARSE, E_RECOVERABLE_ERROR])) {
        // Log the fatal error
        $errorMsg = sprintf(
            "[%s] Fatal Error: %s in %s on line %d\n",
            date('Y-m-d H:i:s'),
            $error['message'],
            $error['file'],
            $error['line']
        );
        @error_log($errorMsg, 3, ERROR_LOG_PATH);
        
        // Redirect to error page
        redirectToErrorPage(500, $error['message']);
    }
}

/**
 * Redirect to the custom error page
 */
function redirectToErrorPage($statusCode = 500, $errorMessage = '') {
    // Prevent recursive calls
    static $handling = false;
    if ($handling) {
        return;
    }
    $handling = true;
    
    // Clear any output that might have been sent
    while (ob_get_level()) {
        @ob_end_clean();
    }
    
    // Set the HTTP status code
    if (!headers_sent()) {
        http_response_code($statusCode);
    }
    
    // Determine the absolute path to the error page
    $errorPagePath = __DIR__ . '/../app/views/error.php';
    
    // If error page doesn't exist at expected location, try alternative
    if (!file_exists($errorPagePath)) {
        // Try relative to current script
        $scriptPath = $_SERVER['SCRIPT_FILENAME'] ?? __FILE__;
        $baseDir = dirname(dirname($scriptPath));
        $errorPagePath = $baseDir . '/app/views/error.php';
    }
    
    // Store error message in session for potential debugging (optional)
    try {
        if (session_status() === PHP_SESSION_ACTIVE || session_status() === PHP_SESSION_NONE) {
            // Try to start session if not already started
            if (session_status() === PHP_SESSION_NONE) {
                @session_start();
            }
            if (session_status() === PHP_SESSION_ACTIVE) {
                $_SESSION['last_error'] = [
                    'message' => $errorMessage,
                    'code' => $statusCode,
                    'time' => time()
                ];
            }
        }
    } catch (Exception $e) {
        // Ignore session errors
    }
    
    // If headers haven't been sent, try to redirect
    if (!headers_sent()) {
        // Calculate relative path for redirect
        $scriptDir = dirname($_SERVER['SCRIPT_NAME'] ?? '');
        $errorPageUrl = '/event-booking-website/app/views/error.php';
        
        if (strpos($scriptDir, '/app/views') !== false) {
            $errorPageUrl = 'error.php';
        } elseif (strpos($scriptDir, '/public') !== false) {
            $errorPageUrl = '../app/views/error.php';
        } elseif (strpos($scriptDir, '/config') !== false || strpos($scriptDir, '/database') !== false) {
            $errorPageUrl = '../app/views/error.php';
        }
        
        header("Location: $errorPageUrl");
        exit;
    } else {
        // Headers already sent, include the error page directly
        if (file_exists($errorPagePath)) {
            include $errorPagePath;
            exit;
        } else {
            // Fallback: show basic error message
            echo "<!DOCTYPE html><html><head><title>Error $statusCode</title></head><body>";
            echo "<h1>Error $statusCode</h1>";
            echo "<p>An error occurred: " . htmlspecialchars($errorMessage) . "</p>";
            echo "</body></html>";
            exit;
        }
    }
}

// Register the error handlers
set_error_handler('customErrorHandler', E_ALL | E_STRICT);
set_exception_handler('customExceptionHandler');
register_shutdown_function('customShutdownHandler');

// Set error reporting (can be overridden in individual files if needed)
error_reporting(E_ALL);
ini_set('display_errors', 0); // Don't display errors directly
ini_set('log_errors', 1);     // Log errors to file
ini_set('error_log', ERROR_LOG_PATH);


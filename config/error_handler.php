<?php
/**
 * Centralized Error Handler
 * This file should be included at the VERY TOP of all PHP files
 * It catches all types of errors including syntax errors, fatal errors, and exceptions
 */

// Disable error display - we'll show our custom error page instead
ini_set('display_errors', 0);
ini_set('display_startup_errors', 0);
error_reporting(E_ALL);

// Prevent infinite redirect loops
$errorHandlerActive = true;

// Get current URI to check if we're already on error page
$currentUri = $_SERVER['REQUEST_URI'] ?? '';
$isErrorPage = strpos($currentUri, 'error.php') !== false;

// Only set handlers if not already on error page
if (!$isErrorPage) {
    
    // Set error handler for all error types
    set_error_handler(function($errno, $errstr, $errfile, $errline) use ($isErrorPage) {
        // Don't handle errors if we're already on the error page
        if ($isErrorPage) {
            return false;
        }
        
        // Log the error
        error_log("PHP Error [$errno]: $errstr in $errfile on line $errline");
        
        // For fatal errors, redirect to error page
        if ($errno === E_ERROR || $errno === E_CORE_ERROR || $errno === E_COMPILE_ERROR || $errno === E_PARSE || $errno === E_RECOVERABLE_ERROR) {
            // Clear any output buffer
            while (ob_get_level() > 0) {
                ob_end_clean();
            }
            
            // Redirect to error page
            if (!headers_sent()) {
                http_response_code(500);
                $errorPage = __DIR__ . '/../app/views/error.php';
                if (file_exists($errorPage)) {
                    $_GET['code'] = 500;
                    require $errorPage;
                    exit;
                }
            }
        }
        
        // Return false to let PHP handle other errors normally
        return false;
    }, E_ALL | E_STRICT);
    
    // Set exception handler for uncaught exceptions
    set_exception_handler(function($exception) use ($isErrorPage) {
        // Don't handle exceptions if we're already on the error page
        if ($isErrorPage) {
            return;
        }
        
        // Log the exception
        error_log("Uncaught Exception: " . $exception->getMessage() . " in " . $exception->getFile() . " on line " . $exception->getLine());
        error_log("Stack trace: " . $exception->getTraceAsString());
        
        // Clear any output buffer
        while (ob_get_level() > 0) {
            ob_end_clean();
        }
        
        // Redirect to error page
        if (!headers_sent()) {
            http_response_code(500);
            $errorPage = __DIR__ . '/../app/views/error.php';
            if (file_exists($errorPage)) {
                $_GET['code'] = 500;
                require $errorPage;
                exit;
            }
        }
    });
    
    // Register shutdown function to catch fatal errors that occur after script execution
    register_shutdown_function(function() use ($isErrorPage) {
        // Don't handle errors if we're already on the error page
        if ($isErrorPage) {
            return;
        }
        
        $error = error_get_last();
        if ($error !== null && in_array($error['type'], [E_ERROR, E_CORE_ERROR, E_COMPILE_ERROR, E_PARSE, E_RECOVERABLE_ERROR])) {
            // Log the fatal error
            error_log("Fatal Error [{$error['type']}]: " . $error['message'] . " in " . $error['file'] . " on line " . $error['line']);
            
            // Clear any output buffer
            while (ob_get_level() > 0) {
                ob_end_clean();
            }
            
            // For parse errors, we need to set HTTP status and let Apache handle it
            // or redirect if headers haven't been sent
            if (!headers_sent()) {
                http_response_code(500);
                $errorPage = __DIR__ . '/../app/views/error.php';
                if (file_exists($errorPage)) {
                    $_GET['code'] = 500;
                    require $errorPage;
                    exit;
                }
            } else {
                // If headers already sent, try to output error page directly
                $errorPage = __DIR__ . '/../app/views/error.php';
                if (file_exists($errorPage)) {
                    $_GET['code'] = 500;
                    require $errorPage;
                    exit;
                }
            }
        }
    });
}

// Start output buffering to catch any errors
ob_start();


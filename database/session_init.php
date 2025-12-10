<?php
/**
 * Session Initialization File
 * Include this file at the top of every page that needs session support
 * This ensures sessions are started properly and prevents duplicate session_start() calls
 */

// Configure session timeout to 15 minutes (900 seconds)
ini_set('session.gc_maxlifetime', 900); // Session data lifetime
ini_set('session.cookie_lifetime', 900); // Cookie lifetime

// Start session if not already started
// Use output buffering to prevent any output issues
if (session_status() === PHP_SESSION_NONE) {
    if (!headers_sent()) {
        // Set session cookie parameters for 15 minutes
        session_set_cookie_params([
            'lifetime' => 900, // 15 minutes
            'path' => '/',
            'domain' => '',
            'secure' => false, // Set to true if using HTTPS
            'httponly' => true,
            'samesite' => 'Lax'
        ]);
        
        session_start();
        
        // Regenerate session ID periodically to prevent session fixation
        if (!isset($_SESSION['created'])) {
            $_SESSION['created'] = time();
        } else if (time() - $_SESSION['created'] > 300) {
            // Regenerate session ID every 5 minutes
            session_regenerate_id(true);
            $_SESSION['created'] = time();
        }
        
        // Check if session has expired (15 minutes of inactivity)
        if (isset($_SESSION['last_activity']) && (time() - $_SESSION['last_activity'] > 900)) {
            // Session expired - destroy it
            session_unset();
            session_destroy();
            session_start();
        }
        
        // Update last activity time
        $_SESSION['last_activity'] = time();
    }
}

// Initialize default session variables if they don't exist (only set is_admin, not user_id)
// Don't set user_id to null as it would interfere with login checks
if (!isset($_SESSION['is_admin'])) {
    $_SESSION['is_admin'] = false;
}

// Helper function to check if user is logged in
function isLoggedIn() {
    return isset($_SESSION['user_id']) && $_SESSION['user_id'] !== null && $_SESSION['user_id'] !== '';
}

// Helper function to check if user is admin
function isAdmin() {
    return isset($_SESSION['is_admin']) && $_SESSION['is_admin'] === true;
}

// Helper function to require login (redirects to auth.php if not logged in)
function requireLogin() {
    if (!isLoggedIn()) {
        // Determine correct path based on current file location
        $scriptPath = $_SERVER['PHP_SELF'];
        $authPath = 'auth.php';
        
        // If we're in admin directory, go up one level
        if (strpos($scriptPath, '/admin/') !== false || strpos($scriptPath, '\\admin\\') !== false) {
            $authPath = '../auth.php';
        }
        
        header('Location: ' . $authPath);
        exit;
    }
}

// Helper function to require admin (redirects to auth.php if not admin)
function requireAdmin() {
    if (!isAdmin()) {
        // Determine correct path based on current file location
        $scriptPath = $_SERVER['PHP_SELF'];
        $authPath = 'auth.php';
        
        // If we're in admin directory, go up one level
        if (strpos($scriptPath, '/admin/') !== false || strpos($scriptPath, '\\admin\\') !== false) {
            $authPath = '../auth.php';
        }
        
        header('Location: ' . $authPath);
        exit;
    }
}

// Helper function to get current user ID
function getCurrentUserId() {
    return $_SESSION['user_id'] ?? null;
}

// Helper function to get current username
function getCurrentUsername() {
    return $_SESSION['username'] ?? $_SESSION['user_name'] ?? 'Guest';
}

// Helper function to get current user email
function getCurrentUserEmail() {
    return $_SESSION['user_email'] ?? null;
}


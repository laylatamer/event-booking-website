<?php
// Start session
require_once __DIR__ . '/../../database/session_init.php';

// Clear all session data
$_SESSION = [];

// Delete the session cookie if it exists
if (ini_get('session.use_cookies')) {
    $params = session_get_cookie_params();
    setcookie(session_name(), '', time() - 42000, $params['path'], $params['domain'], $params['secure'], $params['httponly']);
}

// Finally destroy the session
session_destroy();

// Redirect back to login/register screen
header('Location: auth.php');
exit;


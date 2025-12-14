<?php
// Start session
require_once __DIR__ . '/../database/session_init.php';

require_once __DIR__ . '/../app/controllers/ContactController.php';

// Check if user is logged in
if (!isLoggedIn()) {
    // Set message in session for display on login page
    $_SESSION['auth_message'] = ['text' => 'You should be logged in to send a message', 'type' => 'error'];
    // Redirect to login page
    header('Location: ../app/views/auth.php');
    exit;
}

$controller = new ContactController(new ContactMessage($pdo));

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: ../app/views/contact_form.php');
    exit;
}

$result = $controller->store($_POST);

if ($result['ok']) {
    $_SESSION['contact_status'] = ['type' => 'success', 'message' => 'Thanks! We received your message.'];
} else {
    $_SESSION['contact_status'] = ['type' => 'error', 'message' => $result['message']];
}

header('Location: ../app/views/contact_form.php');
exit;



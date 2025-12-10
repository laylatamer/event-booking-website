<?php
// Start session
require_once __DIR__ . '/../config/session_init.php';

require_once __DIR__ . '/../app/controllers/ContactController.php';

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



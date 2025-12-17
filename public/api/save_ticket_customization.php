<?php
/**
 * API Endpoint: Save Ticket Customization
 * Saves customized ticket information to session/database
 */

header('Content-Type: application/json');
require_once __DIR__ . '/../../database/session_init.php';
require_once __DIR__ . '/../../config/db_connect.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    echo json_encode([
        'success' => false,
        'message' => 'User not authenticated'
    ]);
    exit();
}

// Get POST data
$json = file_get_contents('php://input');
$data = json_decode($json, true);

if (!$data) {
    echo json_encode([
        'success' => false,
        'message' => 'Invalid request data'
    ]);
    exit();
}

try {
    // Validate required fields
    $required = ['event_id', 'customized_count', 'customization_cost', 'guest_names'];
    foreach ($required as $field) {
        if (!isset($data[$field])) {
            throw new Exception("Missing required field: {$field}");
        }
    }
    
    // Store in session for checkout page
    $_SESSION['ticket_customization'] = [
        'event_id' => $data['event_id'],
        'reservation_id' => $data['reservation_id'] ?? null,
        'total_tickets' => $data['total_tickets'],
        'customized_count' => $data['customized_count'],
        'customization_cost' => $data['customization_cost'],
        'guest_names' => $data['guest_names'],
        'event_details' => $data['event_details'],
        'created_at' => date('Y-m-d H:i:s')
    ];
    
    // Optionally save to database (create table if needed)
    $database = new Database();
    $db = $database->getConnection();
    
    // Create table if it doesn't exist
    $createTable = "
        CREATE TABLE IF NOT EXISTS ticket_customizations (
            id INT AUTO_INCREMENT PRIMARY KEY,
            user_id INT NOT NULL,
            event_id INT NOT NULL,
            reservation_id INT NULL,
            customized_count INT NOT NULL,
            customization_cost DECIMAL(10,2) NOT NULL,
            guest_names JSON NOT NULL,
            event_details JSON NOT NULL,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            INDEX (user_id),
            INDEX (event_id)
        )
    ";
    $db->exec($createTable);
    
    // Insert or update customization data
    $stmt = $db->prepare("
        INSERT INTO ticket_customizations 
        (user_id, event_id, reservation_id, customized_count, customization_cost, guest_names, event_details)
        VALUES (?, ?, ?, ?, ?, ?, ?)
        ON DUPLICATE KEY UPDATE
            customized_count = VALUES(customized_count),
            customization_cost = VALUES(customization_cost),
            guest_names = VALUES(guest_names),
            event_details = VALUES(event_details)
    ");
    
    $stmt->execute([
        $_SESSION['user_id'],
        $data['event_id'],
        $data['reservation_id'],
        $data['customized_count'],
        $data['customization_cost'],
        json_encode($data['guest_names']),
        json_encode($data['event_details'])
    ]);
    
    echo json_encode([
        'success' => true,
        'message' => 'Ticket customization saved successfully',
        'customization_id' => $db->lastInsertId()
    ]);
    
} catch (Exception $e) {
    error_log("Error saving ticket customization: " . $e->getMessage());
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}
?>


<?php
// app/api/venue.php

// Suppress error display and use output buffering
error_reporting(E_ALL);
ini_set('display_errors', 0);
ini_set('log_errors', 1);

// Start output buffering early to catch any errors
if (!ob_get_level()) {
    ob_start();
}

// Catch fatal errors and return JSON
register_shutdown_function(function() {
    $error = error_get_last();
    if ($error !== NULL && in_array($error['type'], [E_ERROR, E_PARSE, E_CORE_ERROR, E_COMPILE_ERROR])) {
        // Only handle if we haven't already sent output
        if (!headers_sent()) {
            ob_clean();
            http_response_code(500);
            header('Content-Type: application/json');
            echo json_encode([
                'success' => false,
                'error' => 'System error',
                'message' => 'Please try again later',
                'error_details' => $error['message'] . ' in ' . $error['file'] . ' on line ' . $error['line']
            ]);
        }
        exit();
    }
});

// Set JSON header immediately
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, GET, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

$response = ['success' => false, 'message' => 'Unknown error'];
$statusCode = 200;

try {
    require_once __DIR__ . '/../../config/db_connect.php';
    require_once __DIR__ . '/../../app/models/Venue.php';
    
    if (!isset($pdo)) {
        throw new Exception('PDO connection not available');
    }
    
    $venue = new Venue($pdo);
    $action = $_GET['action'] ?? ($_POST['action'] ?? '');
    $method = $_SERVER['REQUEST_METHOD'];
    
    if ($method === 'GET' && $action === 'get') {
        // Get single venue
        $id = $_GET['id'] ?? 0;
        $venue->id = $id;
        
        if ($venue->readOne()) {
            $response = [
                'success' => true,
                'venue' => [
                    'id' => $venue->id,
                    'name' => $venue->name,
                    'address' => $venue->address,
                    'city' => $venue->city,
                    'country' => $venue->country,
                    'capacity' => $venue->capacity,
                    'description' => $venue->description,
                    'facilities' => is_array($venue->facilities) ? $venue->facilities : (json_decode($venue->facilities, true) ?: []),
                    'google_maps_url' => $venue->google_maps_url,
                    'image_url' => $venue->image_url,
                    'status' => $venue->status,
                    'seating_type' => $venue->seating_type
                ]
            ];
        } else {
            $response = ['success' => false, 'message' => 'Venue not found'];
            $statusCode = 404;
        }
    }
    elseif ($method === 'POST' && $action === 'add') {
        // Add new venue
        $venue->name = $_POST['name'] ?? '';
        $venue->address = $_POST['address'] ?? '';
        $venue->city = $_POST['city'] ?? '';
        $venue->country = $_POST['country'] ?? 'Egypt';
        $venue->capacity = (int)($_POST['capacity'] ?? 0);
        $venue->description = $_POST['description'] ?? '';
        $venue->google_maps_url = $_POST['google_maps_url'] ?? '';
        $venue->status = $_POST['status'] ?? 'active';
        $venue->seating_type = $_POST['seating_type'] ?? null;
        
        // Handle image upload
        if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
            try {
                $venue->image_url = $venue->uploadImage($_FILES['image']);
            } catch (Exception $e) {
                $response = ['success' => false, 'message' => $e->getMessage()];
                $statusCode = 400;
                http_response_code($statusCode);
                echo json_encode($response);
                exit;
            }
        } else {
            $venue->image_url = '';
        }
        
        // Handle facilities
        $facilities = [];
        if (isset($_POST['facilities'])) {
            if (is_array($_POST['facilities'])) {
                $facilities = $_POST['facilities'];
            } else {
                $decoded = json_decode($_POST['facilities'], true);
                $facilities = is_array($decoded) ? $decoded : [];
            }
        }
        $venue->facilities = $facilities;
        
        if (empty($venue->name) || empty($venue->address) || empty($venue->city) || empty($venue->capacity)) {
            $response = ['success' => false, 'message' => 'Please fill all required fields'];
            $statusCode = 400;
        } elseif ($venue->create()) {
            $response = ['success' => true, 'message' => 'Venue added successfully', 'id' => $venue->id];
            $statusCode = 201;
        } else {
            $response = ['success' => false, 'message' => 'Failed to add venue'];
            $statusCode = 500;
        }
    }
    elseif ($method === 'POST' && $action === 'edit') {
        // Edit venue
        $venue->id = $_POST['id'] ?? 0;
        
        // First get current venue to check existing image
        if (!$venue->readOne()) {
            $response = ['success' => false, 'message' => 'Venue not found'];
            $statusCode = 404;
            http_response_code($statusCode);
            echo json_encode($response);
            exit;
        }
        
        $currentImage = $venue->image_url;
        
        // Update venue properties
        $venue->name = $_POST['name'] ?? '';
        $venue->address = $_POST['address'] ?? '';
        $venue->city = $_POST['city'] ?? '';
        $venue->country = $_POST['country'] ?? 'Egypt';
        $venue->capacity = (int)($_POST['capacity'] ?? 0);
        $venue->description = $_POST['description'] ?? '';
        $venue->google_maps_url = $_POST['google_maps_url'] ?? '';
        $venue->status = $_POST['status'] ?? 'active';
        $venue->seating_type = $_POST['seating_type'] ?? null;
        
        // Handle image upload
        if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
            try {
                // Delete old image if exists
                $venue->deleteImage($currentImage);
                
                // Upload new image
                $venue->image_url = $venue->uploadImage($_FILES['image']);
            } catch (Exception $e) {
                $response = ['success' => false, 'message' => $e->getMessage()];
                $statusCode = 400;
                http_response_code($statusCode);
                echo json_encode($response);
                exit;
            }
        } else {
            // Keep existing image
            $venue->image_url = $currentImage;
        }
        
        // Handle facilities
        $facilities = [];
        if (isset($_POST['facilities'])) {
            if (is_array($_POST['facilities'])) {
                $facilities = $_POST['facilities'];
            } else {
                $decoded = json_decode($_POST['facilities'], true);
                $facilities = is_array($decoded) ? $decoded : [];
            }
        }
        $venue->facilities = $facilities;
        
        if (empty($venue->name) || empty($venue->address) || empty($venue->city) || empty($venue->capacity)) {
            $response = ['success' => false, 'message' => 'Please fill all required fields'];
            $statusCode = 400;
        } elseif ($venue->update()) {
            $response = ['success' => true, 'message' => 'Venue updated successfully'];
        } else {
            $response = ['success' => false, 'message' => 'Failed to update venue'];
            $statusCode = 500;
        }
    }
    elseif ($method === 'POST' && $action === 'delete') {
        // Delete venue
        $venue->id = $_POST['id'] ?? 0;
        
        if (!$venue->id) {
            $response = ['success' => false, 'message' => 'Venue ID is required'];
            $statusCode = 400;
        } elseif ($venue->readOne()) {
            // Delete the image file first
            $venue->deleteImage($venue->image_url);
            
            // Then delete from database
            if ($venue->delete()) {
                $response = ['success' => true, 'message' => 'Venue deleted successfully'];
            } else {
                $response = ['success' => false, 'message' => 'Failed to delete venue'];
                $statusCode = 500;
            }
        } else {
            $response = ['success' => false, 'message' => 'Venue not found'];
            $statusCode = 404;
        }
    }
    else {
        $response = ['success' => false, 'message' => 'Invalid action'];
        $statusCode = 400;
    }
} catch (PDOException $e) {
    error_log("Database error: " . $e->getMessage());
    $response = ['success' => false, 'message' => 'Database error'];
    $statusCode = 500;
} catch (Exception $e) {
    error_log("Error: " . $e->getMessage());
    $response = ['success' => false, 'message' => 'Server error'];
    $statusCode = 500;
}

// Clean any output before sending JSON
if (ob_get_level()) {
    ob_clean();
}

http_response_code($statusCode);
echo json_encode($response);
exit();
?>
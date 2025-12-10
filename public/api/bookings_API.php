<?php
// bookings_API.php - API for booking management
require_once __DIR__ . '/../../../config/db_connect.php';
require_once __DIR__ . '/../../../app/models/BookingsModel.php';

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

// Handle preflight requests
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

// Create BookingsModel instance
$bookingsModel = new BookingsModel($pdo);

$action = $_GET['action'] ?? '';

try {
    switch ($action) {
        case 'getAll':
            $page = $_GET['page'] ?? 1;
            $limit = $_GET['limit'] ?? 10;
            
            // Collect filters
            $filters = [
                'status' => $_GET['status'] ?? '',
                'payment_status' => $_GET['payment_status'] ?? '',
                'search' => $_GET['search'] ?? ''
            ];
            
            $result = $bookingsModel->getAllBookings($page, $limit, $filters);
            
            echo json_encode([
                'success' => true,
                'data' => $result['bookings'],
                'pagination' => [
                    'total' => $result['total'],
                    'page' => $result['page'],
                    'limit' => $result['limit'],
                    'pages' => $result['pages']
                ]
            ]);
            break;
            
        case 'getOne':
            $id = $_GET['id'] ?? 0;
            if (!$id) {
                throw new Exception('Booking ID is required');
            }
            
            $booking = $bookingsModel->getBookingById($id);
            if ($booking) {
                echo json_encode([
                    'success' => true,
                    'data' => $booking
                ]);
            } else {
                echo json_encode([
                    'success' => false,
                    'message' => 'Booking not found'
                ]);
            }
            break;
            
        case 'getStats':
            $stats = $bookingsModel->getBookingStats();
            echo json_encode([
                'success' => true,
                'stats' => $stats
            ]);
            break;
            
        case 'updateStatus':
            $data = json_decode(file_get_contents('php://input'), true);
            $id = $data['id'] ?? 0;
            $status = $data['status'] ?? '';
            
            if (!$id || !$status) {
                throw new Exception('ID and status are required');
            }
            
            if ($bookingsModel->updateBookingStatus($id, $status)) {
                echo json_encode([
                    'success' => true,
                    'message' => 'Booking status updated successfully'
                ]);
            } else {
                echo json_encode([
                    'success' => false,
                    'message' => 'Failed to update booking status'
                ]);
            }
            break;
            
        case 'updatePaymentStatus':
            $data = json_decode(file_get_contents('php://input'), true);
            $id = $data['id'] ?? 0;
            $payment_status = $data['payment_status'] ?? '';
            
            if (!$id || !$payment_status) {
                throw new Exception('ID and payment status are required');
            }
            
            if ($bookingsModel->updatePaymentStatus($id, $payment_status)) {
                echo json_encode([
                    'success' => true,
                    'message' => 'Payment status updated successfully'
                ]);
            } else {
                echo json_encode([
                    'success' => false,
                    'message' => 'Failed to update payment status'
                ]);
            }
            break;
            
        case 'delete':
            $id = $_GET['id'] ?? 0;
            if (!$id) {
                throw new Exception('Booking ID is required');
            }
            
            if ($bookingsModel->deleteBooking($id)) {
                echo json_encode([
                    'success' => true,
                    'message' => 'Booking deleted successfully'
                ]);
            } else {
                echo json_encode([
                    'success' => false,
                    'message' => 'Failed to delete booking'
                ]);
            }
            break;
            
        case 'recent':
            $limit = $_GET['limit'] ?? 10;
            $bookings = $bookingsModel->getRecentBookings($limit);
            echo json_encode([
                'success' => true,
                'data' => $bookings
            ]);
            break;
            
        default:
            echo json_encode([
                'success' => false,
                'message' => 'Invalid action'
            ]);
    }
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}
?>
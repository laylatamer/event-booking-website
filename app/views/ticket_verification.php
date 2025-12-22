<?php
/**
 * Dynamic Ticket Display Page
 * Accessible via QR code - shows ticket and user details
 * URL: /app/views/ticket_verification.php?code=BOOKING_CODE
 */

require_once __DIR__ . '/../../config/error_handler.php';
require_once __DIR__ . '/../../config/db_connect.php';
require_once __DIR__ . '/../models/BookingsModel.php';

// Get booking code from URL
$bookingCode = $_GET['code'] ?? '';
$error = '';
$booking = null;

if (empty($bookingCode)) {
    $error = 'No booking code provided. Please scan a valid QR code.';
} else {
    try {
        $database = new Database();
        $db = $database->getConnection();
        $bookingsModel = new BookingsModel($db);
        
        // Get booking by code
        $booking = $bookingsModel->getBookingByCode(htmlspecialchars($bookingCode));
        
        if (!$booking) {
            $error = 'Ticket not found. Please check your booking code.';
        }
    } catch (Exception $e) {
        error_log("Ticket verification error: " . $e->getMessage());
        $error = 'Error loading ticket information. Please try again later.';
    }
}

// Format dates and times if booking exists
$eventDate = 'N/A';
$eventTime = 'N/A';
$bookingDate = 'N/A';

if ($booking) {
    if (!empty($booking['event_date'])) {
        try {
            $eventDateTime = new DateTime($booking['event_date']);
            $eventDate = $eventDateTime->format('F j, Y');
            $eventTime = $eventDateTime->format('g:i A');
        } catch (Exception $e) {
            $eventDate = 'N/A';
            $eventTime = 'N/A';
        }
    }
    
    if (!empty($booking['created_at'])) {
        try {
            $bookingDateTime = new DateTime($booking['created_at']);
            $bookingDate = $bookingDateTime->format('F j, Y \a\t g:i A');
        } catch (Exception $e) {
            $bookingDate = 'N/A';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $booking ? 'Ticket - ' . htmlspecialchars($booking['event_title'] ?? 'Event') : 'Ticket Not Found'; ?> | EحGZLY</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            padding: 20px;
            display: flex;
            justify-content: center;
            align-items: center;
        }
        
        .ticket-container {
            background: white;
            border-radius: 20px;
            box-shadow: 0 20px 60px rgba(0, 0, 0, 0.3);
            max-width: 600px;
            width: 100%;
            overflow: hidden;
            position: relative;
        }
        
        .ticket-header {
            background: linear-gradient(135deg, #f97316 0%, #ea580c 100%);
            color: white;
            padding: 30px;
            text-align: center;
            position: relative;
        }
        
        .ticket-header::after {
            content: '';
            position: absolute;
            bottom: -20px;
            left: 0;
            right: 0;
            height: 40px;
            background: white;
            border-radius: 50% 50% 0 0 / 100% 100% 0 0;
        }
        
        .ticket-header h1 {
            font-size: 32px;
            margin-bottom: 10px;
            font-weight: 700;
        }
        
        .booking-code {
            font-size: 24px;
            letter-spacing: 3px;
            font-weight: 600;
            margin-top: 10px;
            background: rgba(255, 255, 255, 0.2);
            padding: 10px 20px;
            border-radius: 10px;
            display: inline-block;
        }
        
        .ticket-body {
            padding: 40px 30px;
        }
        
        .ticket-section {
            margin-bottom: 30px;
            padding-bottom: 25px;
            border-bottom: 2px dashed #e5e7eb;
        }
        
        .ticket-section:last-child {
            border-bottom: none;
        }
        
        .section-title {
            font-size: 18px;
            font-weight: 700;
            color: #f97316;
            margin-bottom: 15px;
            text-transform: uppercase;
            letter-spacing: 1px;
        }
        
        .detail-row {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            margin-bottom: 12px;
            flex-wrap: wrap;
        }
        
        .detail-label {
            font-weight: 600;
            color: #6b7280;
            font-size: 14px;
            min-width: 120px;
        }
        
        .detail-value {
            color: #1f2937;
            font-size: 15px;
            text-align: right;
            flex: 1;
            font-weight: 500;
        }
        
        .event-title {
            font-size: 24px;
            font-weight: 700;
            color: #1f2937;
            margin-bottom: 20px;
        }
        
        .ticket-categories {
            background: #f9fafb;
            border-radius: 10px;
            padding: 20px;
            margin-top: 15px;
        }
        
        .category-item {
            display: flex;
            justify-content: space-between;
            padding: 10px 0;
            border-bottom: 1px solid #e5e7eb;
        }
        
        .category-item:last-child {
            border-bottom: none;
        }
        
        .seats-list {
            display: flex;
            flex-wrap: wrap;
            gap: 8px;
            margin-top: 10px;
        }
        
        .seat-badge {
            background: #f97316;
            color: white;
            padding: 6px 12px;
            border-radius: 6px;
            font-size: 13px;
            font-weight: 600;
        }
        
        .status-badge {
            display: inline-block;
            padding: 6px 14px;
            border-radius: 20px;
            font-size: 13px;
            font-weight: 600;
            text-transform: uppercase;
        }
        
        .status-confirmed {
            background: #10b981;
            color: white;
        }
        
        .status-pending {
            background: #f59e0b;
            color: white;
        }
        
        .status-cancelled {
            background: #ef4444;
            color: white;
        }
        
        .total-amount {
            font-size: 28px;
            font-weight: 700;
            color: #f97316;
        }
        
        .ticket-footer {
            background: #f9fafb;
            padding: 20px 30px;
            text-align: center;
            color: #6b7280;
            font-size: 13px;
        }
        
        .qr-instruction {
            background: linear-gradient(135deg, #dbeafe 0%, #bfdbfe 100%);
            border-left: 4px solid #3b82f6;
            padding: 18px 20px;
            margin-top: 30px;
            border-radius: 8px;
            font-size: 14px;
            color: #1e40af;
            line-height: 1.6;
        }
        
        .qr-instruction strong {
            display: block;
            margin-bottom: 8px;
            font-size: 15px;
        }
        
        .error-message {
            background: white;
            border-radius: 24px;
            padding: 60px 40px;
            text-align: center;
            box-shadow: 0 25px 50px rgba(0, 0, 0, 0.25);
            max-width: 500px;
            width: 100%;
        }
        
        .error-message h2 {
            font-size: 28px;
            color: #ef4444;
            margin-bottom: 15px;
        }
        
        .error-message p {
            font-size: 16px;
            color: #6b7280;
            line-height: 1.6;
        }
        
        .status-paid {
            background: #10b981;
            color: white;
        }
        
        .status-unpaid {
            background: #ef4444;
            color: white;
        }
        
        .status-refunded {
            background: #6b7280;
            color: white;
        }
        
        @media (max-width: 600px) {
            .ticket-header h1 {
                font-size: 24px;
            }
            
            .booking-code {
                font-size: 18px;
                letter-spacing: 2px;
            }
            
            .ticket-body {
                padding: 30px 20px;
            }
            
            .detail-row {
                flex-direction: column;
            }
            
            .detail-value {
                text-align: left;
                margin-top: 5px;
            }
        }
    </style>
</head>
<body>
    <?php if ($error): ?>
        <div class="error-message">
            <h2>❌ Error</h2>
            <p><?php echo htmlspecialchars($error); ?></p>
        </div>
    <?php elseif ($booking): ?>
        <div class="ticket-container">
            <div class="ticket-header">
                <h1>🎟️ EحGZLY</h1>
                <p style="font-size: 16px; opacity: 0.9;">Event Ticket</p>
                <div class="booking-code"><?php echo htmlspecialchars($bookingCode); ?></div>
            </div>
            
            <div class="ticket-body">
                <!-- Event Information -->
                <div class="ticket-section">
                    <div class="section-title">Event Information</div>
                    <div class="event-title"><?php echo htmlspecialchars($booking['event_title'] ?? 'N/A'); ?></div>
                    <?php if (!empty($booking['event_description'])): ?>
                    <div style="color: #6b7280; font-size: 14px; margin-bottom: 15px; line-height: 1.6;">
                        <?php echo nl2br(htmlspecialchars($booking['event_description'])); ?>
                    </div>
                    <?php endif; ?>
                    <div class="detail-row">
                        <span class="detail-label">Date:</span>
                        <span class="detail-value"><?php echo htmlspecialchars($eventDate); ?></span>
                    </div>
                    <div class="detail-row">
                        <span class="detail-label">Time:</span>
                        <span class="detail-value"><?php echo htmlspecialchars($eventTime); ?></span>
                    </div>
                    <?php if (!empty($booking['venue_name'])): ?>
                    <div class="detail-row">
                        <span class="detail-label">Venue:</span>
                        <span class="detail-value"><?php echo htmlspecialchars($booking['venue_name']); ?></span>
                    </div>
                    <?php endif; ?>
                    <?php if (!empty($booking['venue_address']) || !empty($booking['venue_city'])): ?>
                    <div class="detail-row">
                        <span class="detail-label">Location:</span>
                        <span class="detail-value">
                            <?php 
                            $locationParts = array_filter([
                                $booking['venue_address'] ?? '',
                                $booking['venue_city'] ?? '',
                                $booking['venue_country'] ?? ''
                            ]);
                            echo htmlspecialchars(implode(', ', $locationParts));
                            ?>
                        </span>
                    </div>
                    <?php endif; ?>
                </div>
                
                <!-- Customer Information -->
                <div class="ticket-section">
                    <div class="section-title">Customer Information</div>
                    <div class="detail-row">
                        <span class="detail-label">Name:</span>
                        <span class="detail-value"><?php echo htmlspecialchars($booking['user_name'] ?? 'N/A'); ?></span>
                    </div>
                    <?php if (!empty($booking['user_email'])): ?>
                    <div class="detail-row">
                        <span class="detail-label">Email:</span>
                        <span class="detail-value"><?php echo htmlspecialchars($booking['user_email']); ?></span>
                    </div>
                    <?php endif; ?>
                    <?php if (!empty($booking['user_phone'])): ?>
                    <div class="detail-row">
                        <span class="detail-label">Phone:</span>
                        <span class="detail-value"><?php echo htmlspecialchars($booking['user_phone']); ?></span>
                    </div>
                    <?php endif; ?>
                </div>
                
                <!-- Ticket Details -->
                <div class="ticket-section">
                    <div class="section-title">Ticket Details</div>
                    <div class="detail-row">
                        <span class="detail-label">Number of Tickets:</span>
                        <span class="detail-value"><?php echo intval($booking['ticket_count'] ?? 0); ?></span>
                    </div>
                    
                    <?php if (!empty($booking['ticket_categories'])): ?>
                    <div class="ticket-categories">
                        <strong style="display: block; margin-bottom: 10px; color: #1f2937;">Ticket Categories:</strong>
                        <?php foreach ($booking['ticket_categories'] as $category): ?>
                        <div class="category-item">
                            <span><?php echo htmlspecialchars($category['category_name'] ?? ''); ?> x<?php echo intval($category['quantity'] ?? 0); ?></span>
                            <span>$<?php echo number_format(floatval($category['price'] ?? 0), 2); ?></span>
                        </div>
                        <?php endforeach; ?>
                    </div>
                    <?php endif; ?>
                    
                    <?php if (!empty($booking['booked_seats'])): ?>
                    <div style="margin-top: 15px;">
                        <strong style="display: block; margin-bottom: 10px; color: #1f2937;">Seats:</strong>
                        <div class="seats-list">
                            <?php foreach ($booking['booked_seats'] as $seat): ?>
                            <span class="seat-badge">
                                <?php echo htmlspecialchars($seat['seat_id'] ?? ''); ?>
                                <?php if (!empty($seat['category_name'])): ?>
                                (<?php echo htmlspecialchars($seat['category_name']); ?>)
                                <?php endif; ?>
                            </span>
                            <?php endforeach; ?>
                        </div>
                    </div>
                    <?php endif; ?>
                </div>
                
                <!-- Payment Information -->
                <div class="ticket-section">
                    <div class="section-title">Payment Information</div>
                    <div class="detail-row">
                        <span class="detail-label">Payment Method:</span>
                        <span class="detail-value">
                            <?php 
                            $paymentMethod = strtolower($booking['payment_method'] ?? 'cash');
                            echo $paymentMethod === 'card' ? 'Credit/Debit Card' : 'Cash (Pay at Venue)';
                            ?>
                        </span>
                    </div>
                    <div class="detail-row">
                        <span class="detail-label">Payment Status:</span>
                        <span class="detail-value">
                            <span class="status-badge status-<?php echo strtolower($booking['payment_status'] ?? 'pending'); ?>">
                                <?php echo ucfirst($booking['payment_status'] ?? 'Pending'); ?>
                            </span>
                        </span>
                    </div>
                    <div class="detail-row" style="margin-top: 15px; padding-top: 15px; border-top: 2px solid #e5e7eb;">
                        <span class="detail-label">Total Amount:</span>
                        <span class="detail-value total-amount">$<?php echo number_format(floatval($booking['final_amount'] ?? 0), 2); ?></span>
                    </div>
                </div>
                
                <!-- Booking Information -->
                <div class="ticket-section">
                    <div class="section-title">Booking Information</div>
                    <div class="detail-row">
                        <span class="detail-label">Booking Date:</span>
                        <span class="detail-value"><?php echo htmlspecialchars($bookingDate); ?></span>
                    </div>
                    <div class="detail-row">
                        <span class="detail-label">Status:</span>
                        <span class="detail-value">
                            <span class="status-badge status-<?php echo strtolower($booking['status'] ?? 'confirmed'); ?>">
                                <?php echo ucfirst($booking['status'] ?? 'Confirmed'); ?>
                            </span>
                        </span>
                    </div>
                </div>
                
                <div class="qr-instruction">
                    <strong>📱 Important:</strong> Please show this page to venue staff for entry. Keep this page open or take a screenshot for offline access.
                </div>
            </div>
            
            <div class="ticket-footer">
                <p>Thank you for choosing EحGZLY!</p>
                <p style="margin-top: 5px;">For support, please contact us at support@egzly.com</p>
            </div>
        </div>
    <?php endif; ?>
</body>
</html>

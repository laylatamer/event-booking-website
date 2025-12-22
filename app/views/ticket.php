<?php
/**
 * Dynamic Ticket Display Page
 * Shows ticket and user information when QR code is scanned
 * Accessible via: /app/views/ticket.php?code=BOOKING_CODE
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
        error_log("Ticket page error: " . $e->getMessage());
        $error = 'Error loading ticket information. Please try again later.';
    }
}

// Format dates and times
$eventDateTime = null;
$bookingDateTime = null;

if ($booking) {
    if (!empty($booking['event_date'])) {
        try {
            $eventDateTime = new DateTime($booking['event_date']);
        } catch (Exception $e) {
            $eventDateTime = null;
        }
    }
    
    if (!empty($booking['created_at'])) {
        try {
            $bookingDateTime = new DateTime($booking['created_at']);
        } catch (Exception $e) {
            $bookingDateTime = null;
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
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Oxygen, Ubuntu, Cantarell, sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            padding: 20px;
            display: flex;
            justify-content: center;
            align-items: center;
        }
        
        .ticket-wrapper {
            max-width: 700px;
            width: 100%;
            animation: fadeIn 0.5s ease-in;
        }
        
        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(20px); }
            to { opacity: 1; transform: translateY(0); }
        }
        
        .ticket-card {
            background: white;
            border-radius: 24px;
            box-shadow: 0 25px 50px rgba(0, 0, 0, 0.25);
            overflow: hidden;
            position: relative;
        }
        
        .ticket-header {
            background: linear-gradient(135deg, #f97316 0%, #ea580c 100%);
            color: white;
            padding: 40px 30px;
            text-align: center;
            position: relative;
        }
        
        .ticket-header::after {
            content: '';
            position: absolute;
            bottom: -25px;
            left: 0;
            right: 0;
            height: 50px;
            background: white;
            border-radius: 50% 50% 0 0 / 100% 100% 0 0;
        }
        
        .ticket-header h1 {
            font-size: 36px;
            margin-bottom: 8px;
            font-weight: 800;
            letter-spacing: 1px;
        }
        
        .ticket-header p {
            font-size: 16px;
            opacity: 0.95;
            margin-bottom: 20px;
        }
        
        .booking-code-display {
            display: inline-block;
            font-size: 20px;
            letter-spacing: 4px;
            font-weight: 700;
            background: rgba(255, 255, 255, 0.25);
            padding: 12px 24px;
            border-radius: 12px;
            margin-top: 10px;
            backdrop-filter: blur(10px);
        }
        
        .ticket-content {
            padding: 50px 35px 35px;
        }
        
        .info-section {
            margin-bottom: 35px;
            padding-bottom: 30px;
            border-bottom: 2px dashed #e5e7eb;
        }
        
        .info-section:last-child {
            border-bottom: none;
            margin-bottom: 0;
            padding-bottom: 0;
        }
        
        .section-heading {
            font-size: 16px;
            font-weight: 700;
            color: #f97316;
            margin-bottom: 20px;
            text-transform: uppercase;
            letter-spacing: 1.5px;
            display: flex;
            align-items: center;
            gap: 8px;
        }
        
        .section-heading::before {
            content: '';
            width: 4px;
            height: 20px;
            background: #f97316;
            border-radius: 2px;
        }
        
        .event-title {
            font-size: 28px;
            font-weight: 700;
            color: #1f2937;
            margin-bottom: 25px;
            line-height: 1.3;
        }
        
        .info-item {
            display: grid;
            grid-template-columns: 140px 1fr;
            gap: 15px;
            margin-bottom: 16px;
            align-items: start;
        }
        
        .info-label {
            font-weight: 600;
            color: #6b7280;
            font-size: 14px;
        }
        
        .info-value {
            color: #1f2937;
            font-size: 15px;
            font-weight: 500;
            word-break: break-word;
        }
        
        .user-name {
            font-size: 22px;
            font-weight: 700;
            color: #1f2937;
            margin-bottom: 15px;
        }
        
        .ticket-categories-box {
            background: #f9fafb;
            border-radius: 12px;
            padding: 20px;
            margin-top: 15px;
        }
        
        .category-row {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 12px 0;
            border-bottom: 1px solid #e5e7eb;
        }
        
        .category-row:last-child {
            border-bottom: none;
        }
        
        .category-name {
            font-weight: 600;
            color: #374151;
        }
        
        .category-price {
            color: #f97316;
            font-weight: 700;
        }
        
        .seats-container {
            display: flex;
            flex-wrap: wrap;
            gap: 10px;
            margin-top: 12px;
        }
        
        .seat-tag {
            background: linear-gradient(135deg, #f97316 0%, #ea580c 100%);
            color: white;
            padding: 8px 16px;
            border-radius: 8px;
            font-size: 13px;
            font-weight: 600;
            box-shadow: 0 2px 4px rgba(249, 115, 22, 0.3);
        }
        
        .status-tag {
            display: inline-block;
            padding: 6px 16px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        
        .status-confirmed {
            background: #10b981;
            color: white;
        }
        
        .status-pending {
            background: #f59e0b;
            color: white;
        }
        
        .status-paid {
            background: #10b981;
            color: white;
        }
        
        .status-unpaid {
            background: #ef4444;
            color: white;
        }
        
        .amount-total {
            font-size: 32px;
            font-weight: 800;
            color: #f97316;
            margin-top: 10px;
        }
        
        .error-message {
            background: white;
            border-radius: 24px;
            padding: 60px 40px;
            text-align: center;
            box-shadow: 0 25px 50px rgba(0, 0, 0, 0.25);
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
        
        .ticket-footer {
            background: #f9fafb;
            padding: 25px 35px;
            text-align: center;
            color: #6b7280;
            font-size: 13px;
            line-height: 1.8;
        }
        
        .important-note {
            background: linear-gradient(135deg, #dbeafe 0%, #bfdbfe 100%);
            border-left: 4px solid #3b82f6;
            padding: 18px 20px;
            margin-top: 30px;
            border-radius: 8px;
            font-size: 14px;
            color: #1e40af;
            line-height: 1.6;
        }
        
        .important-note strong {
            display: block;
            margin-bottom: 8px;
            font-size: 15px;
        }
        
        @media (max-width: 640px) {
            .ticket-header {
                padding: 30px 20px;
            }
            
            .ticket-header h1 {
                font-size: 28px;
            }
            
            .booking-code-display {
                font-size: 16px;
                letter-spacing: 3px;
                padding: 10px 20px;
            }
            
            .ticket-content {
                padding: 40px 25px 25px;
            }
            
            .event-title {
                font-size: 22px;
            }
            
            .info-item {
                grid-template-columns: 1fr;
                gap: 8px;
            }
            
            .info-value {
                margin-left: 0;
            }
            
            .amount-total {
                font-size: 26px;
            }
        }
    </style>
</head>
<body>
    <div class="ticket-wrapper">
        <?php if ($error): ?>
            <div class="error-message">
                <h2>⚠️ Ticket Not Found</h2>
                <p><?php echo htmlspecialchars($error); ?></p>
            </div>
        <?php else: ?>
            <div class="ticket-card">
                <!-- Header -->
                <div class="ticket-header">
                    <h1>🎟️ EحGZLY</h1>
                    <p>Event Ticket</p>
                    <div class="booking-code-display"><?php echo htmlspecialchars($booking['booking_code'] ?? ''); ?></div>
                </div>
                
                <!-- Content -->
                <div class="ticket-content">
                    <!-- Event Information -->
                    <div class="info-section">
                        <div class="section-heading">Event Information</div>
                        <div class="event-title"><?php echo htmlspecialchars($booking['event_title'] ?? 'N/A'); ?></div>
                        
                        <?php if ($eventDateTime): ?>
                        <div class="info-item">
                            <div class="info-label">Date</div>
                            <div class="info-value"><?php echo $eventDateTime->format('F j, Y'); ?></div>
                        </div>
                        <div class="info-item">
                            <div class="info-label">Time</div>
                            <div class="info-value"><?php echo $eventDateTime->format('g:i A'); ?></div>
                        </div>
                        <?php endif; ?>
                        
                        <?php if (!empty($booking['venue_name'])): ?>
                        <div class="info-item">
                            <div class="info-label">Venue</div>
                            <div class="info-value"><?php echo htmlspecialchars($booking['venue_name']); ?></div>
                        </div>
                        <?php endif; ?>
                        
                        <?php if (!empty($booking['venue_address'])): ?>
                        <div class="info-item">
                            <div class="info-label">Location</div>
                            <div class="info-value">
                                <?php 
                                $location = [];
                                if (!empty($booking['venue_address'])) $location[] = $booking['venue_address'];
                                if (!empty($booking['venue_city'])) $location[] = $booking['venue_city'];
                                if (!empty($booking['venue_country'])) $location[] = $booking['venue_country'];
                                echo htmlspecialchars(implode(', ', $location));
                                ?>
                            </div>
                        </div>
                        <?php endif; ?>
                    </div>
                    
                    <!-- Customer Information -->
                    <div class="info-section">
                        <div class="section-heading">Customer Information</div>
                        <div class="user-name"><?php echo htmlspecialchars($booking['user_name'] ?? 'N/A'); ?></div>
                        
                        <?php if (!empty($booking['user_email'])): ?>
                        <div class="info-item">
                            <div class="info-label">Email</div>
                            <div class="info-value"><?php echo htmlspecialchars($booking['user_email']); ?></div>
                        </div>
                        <?php endif; ?>
                        
                        <?php if (!empty($booking['user_phone'])): ?>
                        <div class="info-item">
                            <div class="info-label">Phone</div>
                            <div class="info-value"><?php echo htmlspecialchars($booking['user_phone']); ?></div>
                        </div>
                        <?php endif; ?>
                    </div>
                    
                    <!-- Ticket Details -->
                    <div class="info-section">
                        <div class="section-heading">Ticket Details</div>
                        
                        <div class="info-item">
                            <div class="info-label">Number of Tickets</div>
                            <div class="info-value"><?php echo intval($booking['ticket_count'] ?? 0); ?></div>
                        </div>
                        
                        <?php if (!empty($booking['ticket_categories'])): ?>
                        <div class="ticket-categories-box">
                            <strong style="display: block; margin-bottom: 12px; color: #1f2937; font-size: 15px;">Ticket Categories:</strong>
                            <?php foreach ($booking['ticket_categories'] as $category): ?>
                            <div class="category-row">
                                <span class="category-name">
                                    <?php echo htmlspecialchars($category['category_name'] ?? ''); ?> 
                                    × <?php echo intval($category['quantity'] ?? 0); ?>
                                </span>
                                <span class="category-price">$<?php echo number_format(floatval($category['price'] ?? 0), 2); ?></span>
                            </div>
                            <?php endforeach; ?>
                        </div>
                        <?php endif; ?>
                        
                        <?php if (!empty($booking['booked_seats'])): ?>
                        <div style="margin-top: 20px;">
                            <strong style="display: block; margin-bottom: 12px; color: #1f2937; font-size: 15px;">Seat Numbers:</strong>
                            <div class="seats-container">
                                <?php foreach ($booking['booked_seats'] as $seat): ?>
                                <span class="seat-tag">
                                    <?php echo htmlspecialchars($seat['seat_id'] ?? ''); ?>
                                    <?php if (!empty($seat['category_name'])): ?>
                                    <span style="opacity: 0.9;">(<?php echo htmlspecialchars($seat['category_name']); ?>)</span>
                                    <?php endif; ?>
                                </span>
                                <?php endforeach; ?>
                            </div>
                        </div>
                        <?php endif; ?>
                    </div>
                    
                    <!-- Payment Information -->
                    <div class="info-section">
                        <div class="section-heading">Payment Information</div>
                        
                        <div class="info-item">
                            <div class="info-label">Payment Method</div>
                            <div class="info-value">
                                <?php 
                                $paymentMethod = $booking['payment_method'] ?? 'cash';
                                echo $paymentMethod === 'card' ? '💳 Credit/Debit Card' : '💰 Cash (Pay at Venue)';
                                ?>
                            </div>
                        </div>
                        
                        <div class="info-item">
                            <div class="info-label">Payment Status</div>
                            <div class="info-value">
                                <span class="status-tag status-<?php echo strtolower($booking['payment_status'] ?? 'pending'); ?>">
                                    <?php echo ucfirst($booking['payment_status'] ?? 'Pending'); ?>
                                </span>
                            </div>
                        </div>
                        
                        <div class="info-item" style="margin-top: 20px; padding-top: 20px; border-top: 2px solid #e5e7eb;">
                            <div class="info-label">Total Amount</div>
                            <div class="info-value amount-total">$<?php echo number_format(floatval($booking['final_amount'] ?? 0), 2); ?></div>
                        </div>
                    </div>
                    
                    <!-- Booking Information -->
                    <div class="info-section">
                        <div class="section-heading">Booking Information</div>
                        
                        <?php if ($bookingDateTime): ?>
                        <div class="info-item">
                            <div class="info-label">Booking Date</div>
                            <div class="info-value"><?php echo $bookingDateTime->format('F j, Y \a\t g:i A'); ?></div>
                        </div>
                        <?php endif; ?>
                        
                        <div class="info-item">
                            <div class="info-label">Booking Status</div>
                            <div class="info-value">
                                <span class="status-tag status-<?php echo strtolower($booking['status'] ?? 'confirmed'); ?>">
                                    <?php echo ucfirst($booking['status'] ?? 'Confirmed'); ?>
                                </span>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Important Note -->
                    <div class="important-note">
                        <strong>📱 Important Notice</strong>
                        Please show this page to venue staff for entry. You can take a screenshot or bookmark this page for offline access.
                    </div>
                </div>
                
                <!-- Footer -->
                <div class="ticket-footer">
                    <p><strong>Thank you for choosing EحGZLY!</strong></p>
                    <p style="margin-top: 8px;">For support, contact us at support@egzly.com</p>
                </div>
            </div>
        <?php endif; ?>
    </div>
</body>
</html>


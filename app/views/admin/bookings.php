<?php
// bookings.php - Admin Bookings View
// Initialize database connection
require_once __DIR__ . '/../../../config/db_connect.php';
$database = new Database();
$db = $database->getConnection();

// Include the BookingsModel
require_once __DIR__ . '/../../../app/models/BookingsModel.php';
$bookingsModel = new BookingsModel($db);

// Get booking statistics for the dashboard
$stats = $bookingsModel->getBookingStats();

// Get filter parameters
$filters = [
    'status' => $_GET['status'] ?? '',
    'payment_status' => $_GET['payment_status'] ?? '',
    'search' => $_GET['search'] ?? ''
];

// Pagination
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$limit = 10;
if ($page < 1) $page = 1;

// Fetch bookings data
$result = $bookingsModel->getAllBookings($page, $limit, $filters);
$bookings = $result['bookings'];
$totalBookings = $result['total'];
$totalPages = $result['pages'];
$currentPage = $result['page'];

// Helper functions for display
function formatDate($dateString) {
    if (!$dateString) return 'N/A';
    $date = new DateTime($dateString);
    return $date->format('M d, Y');
}

function formatDateTime($dateString) {
    if (!$dateString) return 'N/A';
    $date = new DateTime($dateString);
    return $date->format('M d, Y h:i A');
}

function formatCurrency($amount) {
    return '$' . number_format($amount, 2);
}

function escapeHtml($text) {
    return htmlspecialchars($text ?? '', ENT_QUOTES, 'UTF-8');
}

// Generate short 4-digit booking ID
function generateShortId($id) {
    return str_pad($id % 10000, 4, '0', STR_PAD_LEFT);
}

// Get payment method display
function getPaymentMethodDisplay($paymentMethod, $paymentStatus) {
    // Always show the actual payment method, not inferred from status
    if ($paymentMethod === 'cash') {
        return 'Cash';
    } elseif ($paymentMethod === 'card' || $paymentMethod === 'credit' || $paymentMethod === 'debit') {
        return 'Credit/Debit';
    } elseif ($paymentStatus === 'pending') {
        // If payment method is not set but status is pending, assume cash
        return 'Cash';
    } elseif ($paymentStatus === 'paid' && empty($paymentMethod)) {
        // If payment method is not set but status is paid, assume card
        return 'Credit/Debit';
    }
    return $paymentMethod ?: 'Cash';
}

// Build pagination URL with filters
function buildPaginationUrl($page, $filters) {
    $params = ['page' => $page];
    if (!empty($filters['status'])) $params['status'] = $filters['status'];
    if (!empty($filters['payment_status'])) $params['payment_status'] = $filters['payment_status'];
    if (!empty($filters['search'])) $params['search'] = $filters['search'];
    
    $queryString = http_build_query($params);
    return "?section=bookings&" . $queryString;
}
?>

<div id="bookings-section" class="section-content">

    <div class="content-card">
        <div class="stats-grid small">
            <div class="stat-card small">
                <p class="stat-label">Total Bookings</p>
                <h3 class="stat-value"><?php echo $stats['total_bookings']; ?></h3>
            </div>
            <div class="stat-card small">
                <p class="stat-label">Active</p>
                <h3 class="stat-value"><?php echo $stats['pending_bookings'] + $stats['confirmed_bookings']; ?></h3>
            </div>
            <div class="stat-card small">
                <p class="stat-label">Completed</p>
                <h3 class="stat-value"><?php echo $stats['completed_bookings']; ?></h3>
            </div>
            <div class="stat-card small">
                <p class="stat-label">Revenue</p>
                <h3 class="stat-value"><?php echo formatCurrency($stats['total_revenue']); ?></h3>
            </div>
        </div>
    </div>

    <div class="content-card">
        <div class="table-controls">
            <div class="controls-left">
                <form method="GET" action="" class="filter-form" style="display: flex; gap: 15px; align-items: center;">
                    <input type="hidden" name="section" value="bookings">
                    <?php if (!empty($filters['search'])): ?>
                        <input type="hidden" name="search" value="<?php echo escapeHtml($filters['search']); ?>">
                    <?php endif; ?>
                    <select name="status" class="clean-filter-select" onchange="this.form.submit()">
                        <option value="">All Status</option>
                        <option value="pending" <?php echo $filters['status'] === 'pending' ? 'selected' : ''; ?>>Pending</option>
                        <option value="confirmed" <?php echo $filters['status'] === 'confirmed' ? 'selected' : ''; ?>>Confirmed</option>
                        <option value="completed" <?php echo $filters['status'] === 'completed' ? 'selected' : ''; ?>>Completed</option>
                        <option value="cancelled" <?php echo $filters['status'] === 'cancelled' ? 'selected' : ''; ?>>Cancelled</option>
                    </select>
                    <select name="payment_status" class="clean-filter-select" onchange="this.form.submit()">
                        <option value="">Payment Status</option>
                        <option value="pending" <?php echo $filters['payment_status'] === 'pending' ? 'selected' : ''; ?>>Pending</option>
                        <option value="paid" <?php echo $filters['payment_status'] === 'paid' ? 'selected' : ''; ?>>Paid</option>
                        <option value="failed" <?php echo $filters['payment_status'] === 'failed' ? 'selected' : ''; ?>>Failed</option>
                    </select>
                </form>
            </div>
            <div class="controls-right">
                <a href="?section=bookings" class="icon-btn" title="Reset Filters">
                    <i data-feather="refresh-cw"></i>
                </a>
            </div>
        </div>

        <div class="clean-table-container">
            <table class="clean-table">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Event</th>
                        <th>Customer</th>
                        <th>Date</th>
                        <th>Tickets</th>
                        <th>Amount</th>
                        <th>Status</th>
                        <th>Payment</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($bookings)): ?>
                        <tr>
                            <td colspan="9" class="empty-state">
                                <i data-feather="calendar"></i>
                                <p>No bookings found</p>
                                <?php if (!empty($filters['status']) || !empty($filters['payment_status']) || !empty($filters['search'])): ?>
                                    <a href="?section=bookings" class="primary-btn">Clear Filters</a>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($bookings as $booking): ?>
                            <?php
                            $bookingId = $booking['id'];
                            $shortId = generateShortId($bookingId);
                            $ticketCount = $booking['ticket_count'];
                            $finalAmount = $booking['final_amount'] ?? $booking['total_amount'] ?? 0;
                            $status = $booking['status'];
                            $paymentStatus = $booking['payment_status'];
                            $createdAt = $booking['created_at'];
                            
                            // User information
                            $firstName = $booking['first_name'] ?? '';
                            $lastName = $booking['last_name'] ?? '';
                            $userName = trim($firstName . ' ' . $lastName) ?: 'User #' . $booking['user_id'];
                            $userEmail = $booking['user_email'] ?? '';
                            
                            // Event information
                            $eventTitle = $booking['event_title'] ?? 'Event #' . $booking['event_id'];
                            $venueName = $booking['venue_name'] ?? '';
                            $eventDate = $booking['event_date'] ?? '';
                            
                            // Payment method
                            $paymentMethod = getPaymentMethodDisplay($booking['payment_method'] ?? '', $paymentStatus);
                            $actualPaymentMethod = $booking['payment_method'] ?? 'cash'; // Get actual payment method, not display
                            ?>
                            <tr>
                                <td>
                                    <span class="booking-id">#<?php echo $shortId; ?></span>
                                    <div class="compact-date"><?php echo date('m/d/Y', strtotime($createdAt)); ?></div>
                                </td>
                                <td>
                                    <div class="clean-event-info">
                                        <strong><?php echo escapeHtml($eventTitle); ?></strong>
                                        <small><?php echo escapeHtml($venueName); ?></small>
                                        <?php if ($eventDate): ?>
                                            <small class="compact-time"><?php echo date('M d, h:i A', strtotime($eventDate)); ?></small>
                                        <?php endif; ?>
                                    </div>
                                </td>
                                <td>
                                    <div class="clean-user-info">
                                        <strong><?php echo escapeHtml($userName); ?></strong>
                                        <small><?php echo escapeHtml($userEmail); ?></small>
                                        <?php if ($booking['user_phone'] ?? ''): ?>
                                            <small class="compact-time"><?php echo escapeHtml($booking['user_phone']); ?></small>
                                        <?php endif; ?>
                                    </div>
                                </td>
                                <td>
                                    <div class="compact-date"><?php echo date('M d, Y', strtotime($createdAt)); ?></div>
                                    <div class="compact-time"><?php echo date('h:i A', strtotime($createdAt)); ?></div>
                                </td>
                                <td>
                                    <strong style="font-size: 16px; color: #ff5722;"><?php echo $ticketCount; ?></strong>
                                    <div class="compact-time">ticket<?php echo $ticketCount != 1 ? 's' : ''; ?></div>
                                </td>
                                <td>
                                    <strong style="font-size: 15px;"><?php echo formatCurrency($finalAmount); ?></strong>
                                </td>
                                <td>
                                    <span class="clean-status-badge <?php echo $status; ?>">
                                        <?php echo ucfirst($status); ?>
                                    </span>
                                </td>
                                <td>
                                    <span class="clean-payment-badge <?php echo $paymentStatus; ?>">
                                        <?php echo ucfirst($paymentStatus); ?>
                                    </span>
                                    <div class="payment-method-badge">
                                        <?php echo $paymentMethod; ?>
                                    </div>
                                </td>
                                <td>
                                    <div class="action-buttons" style="display: flex; gap: 8px; align-items: center;">
                                        <button class="action-btn view-btn" onclick="viewBooking(<?php echo $bookingId; ?>)" title="View Details">
                                            <i data-feather="eye"></i>
                                        </button>
                                        <?php if ($status !== 'cancelled'): ?>
                                            <button class="action-btn cancel-btn" onclick="cancelBooking(<?php echo $bookingId; ?>)" title="Cancel Booking">
                                                <i data-feather="x-circle"></i>
                                            </button>
                                        <?php endif; ?>
                                        <?php if ($actualPaymentMethod === 'cash' && $paymentStatus === 'pending'): ?>
                                            <button class="action-btn approve-btn" onclick="approveCashPayment(<?php echo $bookingId; ?>)" title="Approve Cash Payment">
                                                <i data-feather="check-circle"></i>
                                            </button>
                                        <?php endif; ?>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>

        <div class="clean-table-footer">
            <div class="clean-table-info">
                Page <span><?php echo $currentPage; ?></span> of <span><?php echo $totalPages; ?></span>
                • Showing <span><?php echo min((($currentPage - 1) * $limit) + 1, $totalBookings); ?></span>-<span><?php echo min($currentPage * $limit, $totalBookings); ?></span>
                of <span><?php echo $totalBookings; ?></span> bookings
            </div>
            <div class="clean-pagination">
                <?php if ($currentPage > 1): ?>
                    <a href="<?php echo buildPaginationUrl($currentPage - 1, $filters); ?>" class="clean-pagination-btn">
                        <i data-feather="chevron-left" style="width: 16px; height: 16px;"></i>
                    </a>
                <?php else: ?>
                    <button class="clean-pagination-btn" disabled>
                        <i data-feather="chevron-left" style="width: 16px; height: 16px;"></i>
                    </button>
                <?php endif; ?>
                
                <div id="bookings-pages">
                    <?php 
                    // Show limited page numbers
                    $maxPagesToShow = 5;
                    $startPage = max(1, $currentPage - floor($maxPagesToShow / 2));
                    $endPage = min($totalPages, $startPage + $maxPagesToShow - 1);
                    
                    if ($startPage > 1): ?>
                        <a href="<?php echo buildPaginationUrl(1, $filters); ?>" class="clean-pagination-btn">1</a>
                        <?php if ($startPage > 2): ?>
                            <span style="padding: 0 5px; color: #888; display: flex; align-items: center;">...</span>
                        <?php endif; ?>
                    <?php endif; ?>
                    
                    <?php for ($i = $startPage; $i <= $endPage; $i++): ?>
                        <a href="<?php echo buildPaginationUrl($i, $filters); ?>" 
                           class="clean-pagination-btn <?php echo $i === $currentPage ? 'active' : ''; ?>">
                            <?php echo $i; ?>
                        </a>
                    <?php endfor; ?>
                    
                    <?php if ($endPage < $totalPages): ?>
                        <?php if ($endPage < $totalPages - 1): ?>
                            <span style="padding: 0 5px; color: #888; display: flex; align-items: center;">...</span>
                        <?php endif; ?>
                        <a href="<?php echo buildPaginationUrl($totalPages, $filters); ?>" class="clean-pagination-btn"><?php echo $totalPages; ?></a>
                    <?php endif; ?>
                </div>
                
                <?php if ($currentPage < $totalPages): ?>
                    <a href="<?php echo buildPaginationUrl($currentPage + 1, $filters); ?>" class="clean-pagination-btn">
                        <i data-feather="chevron-right" style="width: 16px; height: 16px;"></i>
                    </a>
                <?php else: ?>
                    <button class="clean-pagination-btn" disabled>
                        <i data-feather="chevron-right" style="width: 16px; height: 16px;"></i>
                    </button>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<!-- View Booking Details Modal -->
<div id="view-booking-modal" class="modal hidden">
    <div class="modal-content large">
        <div class="modal-header">
            <h3>Booking Details</h3>
            <button class="close-modal" onclick="closeModal('view-booking')">
                <i data-feather="x"></i>
            </button>
        </div>
        <div id="booking-details" class="booking-details-container">
            <!-- Booking details will be populated by JavaScript -->
        </div>
        <div class="modal-actions">
            <button type="button" class="secondary-btn" onclick="closeModal('view-booking')">Close</button>
            <button class="primary-btn" id="print-booking-btn" onclick="printBooking()">
                <i data-feather="printer"></i>
                <span>Print Receipt</span>
            </button>
        </div>
    </div>
</div>

<script>
// Initialize feather icons
document.addEventListener('DOMContentLoaded', function() {
    feather.replace();
});

// Store current booking for printing
let currentBookingDetails = null;

function viewBooking(bookingId) {
    // AJAX call to fetch booking details
    fetch(`/event-booking-website/public/api/bookings_API.php?action=getOne&id=${bookingId}`)
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                currentBookingDetails = data.data;
                renderBookingDetails(data.data);
                openModal('view-booking');
            } else {
                alert('Failed to load booking details: ' + data.message);
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('Failed to load booking details');
        });
}

function renderBookingDetails(booking) {
    const container = document.getElementById('booking-details');
    if (!container) return;
    
    const totalAmount = parseFloat(booking.total_amount) || 0;
    const finalAmount = parseFloat(booking.final_amount) || totalAmount;
    const discount = parseFloat(booking.discount) || 0;
    const tax = parseFloat(booking.tax) || 0;
    const ticketPrice = parseFloat(booking.ticket_price) || (finalAmount / (booking.ticket_count || 1));
    
    // Generate short ID
    const shortId = String(booking.id % 10000).padStart(4, '0');
    
    container.innerHTML = `
        <div class="booking-details-grid">
            <div class="booking-detail-section">
                <h4>Booking Information</h4>
                <div class="detail-row">
                    <span class="detail-label">Booking ID:</span>
                    <span class="detail-value"><span class="booking-id">#${shortId}</span></span>
                </div>
                <div class="detail-row">
                    <span class="detail-label">Original Code:</span>
                    <span class="detail-value">${escapeHtml(booking.booking_code)}</span>
                </div>
                <div class="detail-row">
                    <span class="detail-label">Booking Date:</span>
                    <span class="detail-value">${formatDateTime(booking.created_at)}</span>
                </div>
                <div class="detail-row">
                    <span class="detail-label">Status:</span>
                    <span class="detail-value">
                        <span class="clean-status-badge ${booking.status}">
                            ${booking.status ? booking.status.charAt(0).toUpperCase() + booking.status.slice(1) : 'Unknown'}
                        </span>
                    </span>
                </div>
                <div class="detail-row">
                    <span class="detail-label">Payment Status:</span>
                    <span class="detail-value">
                        <span class="clean-payment-badge ${booking.payment_status}">
                            ${booking.payment_status ? booking.payment_status.charAt(0).toUpperCase() + booking.payment_status.slice(1) : 'Pending'}
                        </span>
                    </span>
                </div>
                <div class="detail-row">
                    <span class="detail-label">Payment Method:</span>
                    <span class="detail-value">${escapeHtml(getPaymentMethodDisplay(booking.payment_method, booking.payment_status))}</span>
                </div>
                ${booking.transaction_id ? `
                <div class="detail-row">
                    <span class="detail-label">Transaction ID:</span>
                    <span class="detail-value">${escapeHtml(booking.transaction_id)}</span>
                </div>
                ` : ''}
            </div>
            
            <div class="booking-detail-section">
                <h4>Customer Information</h4>
                <div class="detail-row">
                    <span class="detail-label">Name:</span>
                    <span class="detail-value">${escapeHtml(booking.user_name || 'N/A')}</span>
                </div>
                <div class="detail-row">
                    <span class="detail-label">Email:</span>
                    <span class="detail-value">${escapeHtml(booking.user_email)}</span>
                </div>
                ${booking.user_phone ? `
                <div class="detail-row">
                    <span class="detail-label">Phone:</span>
                    <span class="detail-value">${escapeHtml(booking.user_phone)}</span>
                </div>
                ` : ''}
                ${booking.user_address ? `
                <div class="detail-row">
                    <span class="detail-label">Address:</span>
                    <span class="detail-value">${escapeHtml(booking.user_address)}</span>
                </div>
                ` : ''}
                ${booking.user_city ? `
                <div class="detail-row">
                    <span class="detail-label">City:</span>
                    <span class="detail-value">${escapeHtml(booking.user_city)}</span>
                </div>
                ` : ''}
            </div>
            
            <div class="booking-detail-section">
                <h4>Event Information</h4>
                <div class="detail-row">
                    <span class="detail-label">Event:</span>
                    <span class="detail-value">${escapeHtml(booking.event_title)}</span>
                </div>
                ${booking.main_category_name && booking.subcategory_name ? `
                <div class="detail-row">
                    <span class="detail-label">Category:</span>
                    <span class="detail-value">${escapeHtml(booking.main_category_name)} > ${escapeHtml(booking.subcategory_name)}</span>
                </div>
                ` : ''}
                ${booking.event_date ? `
                <div class="detail-row">
                    <span class="detail-label">Event Date:</span>
                    <span class="detail-value">${formatDateTime(booking.event_date)}</span>
                </div>
                ` : ''}
                ${booking.venue_name ? `
                <div class="detail-row">
                    <span class="detail-label">Venue:</span>
                    <span class="detail-value">${escapeHtml(booking.venue_name)}</span>
                </div>
                ` : ''}
                ${booking.venue_address ? `
                <div class="detail-row">
                    <span class="detail-label">Venue Address:</span>
                    <span class="detail-value">${escapeHtml(booking.venue_address)}</span>
                </div>
                ` : ''}
                ${booking.venue_city && booking.venue_country ? `
                <div class="detail-row">
                    <span class="detail-label">Location:</span>
                    <span class="detail-value">${escapeHtml(booking.venue_city)}, ${escapeHtml(booking.venue_country)}</span>
                </div>
                ` : ''}
                ${booking.event_description ? `
                <div class="detail-row">
                    <span class="detail-label">Description:</span>
                    <span class="detail-value" style="max-width: 300px;">${escapeHtml(booking.event_description)}</span>
                </div>
                ` : ''}
            </div>
            
            <div class="booking-detail-section">
                <h4>Order Summary</h4>
                <div class="detail-row">
                    <span class="detail-label">Tickets:</span>
                    <span class="detail-value"><strong>${booking.ticket_count || 1}</strong></span>
                </div>
                <div class="detail-row">
                    <span class="detail-label">Price per Ticket:</span>
                    <span class="detail-value">$${ticketPrice.toFixed(2)}</span>
                </div>
                ${totalAmount > 0 ? `
                <div class="detail-row">
                    <span class="detail-label">Subtotal:</span>
                    <span class="detail-value">$${totalAmount.toFixed(2)}</span>
                </div>
                ` : ''}
                ${discount > 0 ? `
                <div class="detail-row">
                    <span class="detail-label">Discount:</span>
                    <span class="detail-value text-success">-$${discount.toFixed(2)}</span>
                </div>
                ` : ''}
                ${tax > 0 ? `
                <div class="detail-row">
                    <span class="detail-label">Tax:</span>
                    <span class="detail-value">$${tax.toFixed(2)}</span>
                </div>
                ` : ''}
                <div class="detail-row">
                    <span class="detail-label">Total Amount:</span>
                    <span class="detail-value"><strong style="font-size: 18px; color: #ff5722;">$${finalAmount.toFixed(2)}</strong></span>
                </div>
            </div>
        </div>
        
        ${booking.notes ? `
        <div class="booking-notes">
            <h4>Additional Notes</h4>
            <p>${escapeHtml(booking.notes)}</p>
        </div>
        ` : ''}
    `;
}

function getPaymentMethodDisplay(paymentMethod, paymentStatus) {
    // Always show the actual payment method, not inferred from status
    if (paymentMethod === 'cash') {
        return 'Cash';
    } else if (paymentMethod === 'card' || paymentMethod === 'credit' || paymentMethod === 'debit') {
        return 'Credit/Debit';
    } else if (paymentStatus === 'pending') {
        // If payment method is not set but status is pending, assume cash
        return 'Cash';
    } else if (paymentStatus === 'paid' && !paymentMethod) {
        // If payment method is not set but status is paid, assume card
        return 'Credit/Debit';
    }
    return paymentMethod || 'Cash';
}

function printBooking() {
    if (!currentBookingDetails) return;
    
    const printWindow = window.open('', '_blank');
    const shortId = String(currentBookingDetails.id % 10000).padStart(4, '0');
    const totalAmount = parseFloat(currentBookingDetails.total_amount) || 0;
    const finalAmount = parseFloat(currentBookingDetails.final_amount) || totalAmount;
    
    printWindow.document.write(`
        <html>
            <head>
                <title>Booking Receipt - #${shortId}</title>
                <style>
                    body { 
                        font-family: Arial, sans-serif; 
                        padding: 40px;
                        background: white;
                        color: black;
                        line-height: 1.6;
                    }
                    .receipt { 
                        max-width: 800px; 
                        margin: 0 auto; 
                        border: 2px solid #000;
                        padding: 40px;
                    }
                    .header { 
                        text-align: center; 
                        margin-bottom: 40px;
                        border-bottom: 2px solid #000;
                        padding-bottom: 20px;
                    }
                    .header h1 {
                        color: #ff5722;
                        margin: 0 0 10px 0;
                    }
                    .booking-id {
                        font-size: 24px;
                        font-weight: bold;
                        color: #333;
                        margin: 10px 0;
                    }
                    .details-grid {
                        display: grid;
                        grid-template-columns: repeat(2, 1fr);
                        gap: 30px;
                        margin-bottom: 40px;
                    }
                    .detail-section {
                        margin-bottom: 30px;
                    }
                    .detail-section h3 {
                        margin-top: 0;
                        color: #333;
                        border-bottom: 1px solid #ddd;
                        padding-bottom: 10px;
                        font-size: 16px;
                    }
                    .detail-row {
                        display: flex;
                        justify-content: space-between;
                        margin-bottom: 10px;
                        padding-bottom: 10px;
                        border-bottom: 1px solid #eee;
                    }
                    .detail-label {
                        font-weight: bold;
                        color: #666;
                        font-size: 13px;
                    }
                    .detail-value {
                        text-align: right;
                        font-size: 14px;
                    }
                    .status-badge {
                        display: inline-block;
                        padding: 4px 12px;
                        border-radius: 20px;
                        font-size: 12px;
                        font-weight: bold;
                        margin-left: 10px;
                    }
                    .footer { 
                        text-align: center; 
                        margin-top: 40px; 
                        font-size: 14px; 
                        color: #666; 
                        border-top: 1px solid #ddd;
                        padding-top: 20px;
                    }
                    .total-amount {
                        font-size: 24px;
                        color: #ff5722;
                        font-weight: bold;
                        text-align: right;
                        margin-top: 20px;
                        padding-top: 20px;
                        border-top: 2px solid #333;
                    }
                    @media print {
                        body { padding: 0; }
                        .no-print { display: none; }
                    }
                </style>
            </head>
            <body>
                <div class="receipt">
                    <div class="header">
                        <h1>EحGZLY</h1>
                        <h2>Booking Receipt</h2>
                        <div class="booking-id">Booking #${shortId}</div>
                        <p>${new Date().toLocaleDateString()} ${new Date().toLocaleTimeString()}</p>
                    </div>
                    
                    <div class="details-grid">
                        <div class="detail-section">
                            <h3>Booking Information</h3>
                            <div class="detail-row">
                                <span class="detail-label">Booking Code:</span>
                                <span class="detail-value">${escapeHtml(currentBookingDetails.booking_code)}</span>
                            </div>
                            <div class="detail-row">
                                <span class="detail-label">Booking Date:</span>
                                <span class="detail-value">${formatDateTime(currentBookingDetails.created_at)}</span>
                            </div>
                            <div class="detail-row">
                                <span class="detail-label">Status:</span>
                                <span class="detail-value">${currentBookingDetails.status.charAt(0).toUpperCase() + currentBookingDetails.status.slice(1)}</span>
                            </div>
                            <div class="detail-row">
                                <span class="detail-label">Payment:</span>
                                <span class="detail-value">${currentBookingDetails.payment_status.charAt(0).toUpperCase() + currentBookingDetails.payment_status.slice(1)}</span>
                            </div>
                            <div class="detail-row">
                                <span class="detail-label">Payment Method:</span>
                                <span class="detail-value">${getPaymentMethodDisplay(currentBookingDetails.payment_method, currentBookingDetails.payment_status)}</span>
                            </div>
                        </div>
                        
                        <div class="detail-section">
                            <h3>Customer Information</h3>
                            <div class="detail-row">
                                <span class="detail-label">Name:</span>
                                <span class="detail-value">${escapeHtml(currentBookingDetails.user_name || 'N/A')}</span>
                            </div>
                            <div class="detail-row">
                                <span class="detail-label">Email:</span>
                                <span class="detail-value">${escapeHtml(currentBookingDetails.user_email)}</span>
                            </div>
                            ${currentBookingDetails.user_phone ? `
                            <div class="detail-row">
                                <span class="detail-label">Phone:</span>
                                <span class="detail-value">${escapeHtml(currentBookingDetails.user_phone)}</span>
                            </div>
                            ` : ''}
                        </div>
                        
                        <div class="detail-section">
                            <h3>Event Information</h3>
                            <div class="detail-row">
                                <span class="detail-label">Event:</span>
                                <span class="detail-value">${escapeHtml(currentBookingDetails.event_title)}</span>
                            </div>
                            ${currentBookingDetails.event_date ? `
                            <div class="detail-row">
                                <span class="detail-label">Event Date:</span>
                                <span class="detail-value">${formatDateTime(currentBookingDetails.event_date)}</span>
                            </div>
                            ` : ''}
                            ${currentBookingDetails.venue_name ? `
                            <div class="detail-row">
                                <span class="detail-label">Venue:</span>
                                <span class="detail-value">${escapeHtml(currentBookingDetails.venue_name)}</span>
                            </div>
                            ` : ''}
                        </div>
                        
                        <div class="detail-section">
                            <h3>Order Summary</h3>
                            <div class="detail-row">
                                <span class="detail-label">Tickets:</span>
                                <span class="detail-value">${currentBookingDetails.ticket_count || 1}</span>
                            </div>
                            <div class="detail-row">
                                <span class="detail-label">Price per Ticket:</span>
                                <span class="detail-value">$${(parseFloat(currentBookingDetails.ticket_price) || 0).toFixed(2)}</span>
                            </div>
                            <div class="detail-row">
                                <span class="detail-label">Total:</span>
                                <span class="detail-value">$${finalAmount.toFixed(2)}</span>
                            </div>
                        </div>
                    </div>
                    
                    <div class="total-amount">
                        Total: $${finalAmount.toFixed(2)}
                    </div>
                    
                    <div class="footer">
                        <p>Thank you for using EحGZLY!</p>
                        <p>Booking ID: #${shortId} | ${currentBookingDetails.booking_code}</p>
                        <p>Printed on: ${new Date().toLocaleDateString()} ${new Date().toLocaleTimeString()}</p>
                    </div>
                </div>
                <div class="no-print" style="text-align: center; margin-top: 20px;">
                    <button onclick="window.print()" style="padding: 12px 24px; background: #3b82f6; color: white; border: none; border-radius: 5px; cursor: pointer; font-size: 16px;">
                        Print Receipt
                    </button>
                </div>
            </body>
        </html>
    `);
    printWindow.document.close();
}

function openModal(modalId) {
    const modal = document.getElementById(modalId + '-modal');
    if (modal) {
        modal.classList.remove('hidden');
        document.body.style.overflow = 'hidden';
    }
}

function closeModal(modalId) {
    const modal = document.getElementById(modalId + '-modal');
    if (modal) {
        modal.classList.add('hidden');
        document.body.style.overflow = 'auto';
    }
}

// Helper functions
function escapeHtml(text) {
    const div = document.createElement('div');
    div.textContent = text;
    return div.innerHTML;
}

function formatDateTime(dateString) {
    if (!dateString) return 'N/A';
    const date = new Date(dateString);
    return date.toLocaleDateString('en-US', { 
        year: 'numeric', 
        month: 'long', 
        day: 'numeric',
        hour: '2-digit',
        minute: '2-digit'
    });
}

// Cancel booking function
async function cancelBooking(bookingId) {
    if (!confirm('Are you sure you want to cancel this booking? This will return the tickets and seats to available inventory.')) {
        return;
    }
    
    try {
        const response = await fetch('/event-booking-website/public/api/bookings_API.php?action=cancel', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify({ id: bookingId })
        });
        
        const result = await response.json();
        
        if (result.success) {
            alert('✅ ' + result.message);
            location.reload(); // Reload to show updated status
        } else {
            alert('❌ Error: ' + (result.message || 'Failed to cancel booking'));
        }
    } catch (error) {
        console.error('Error cancelling booking:', error);
        alert('❌ Error cancelling booking. Please try again.');
    }
}

// Approve cash payment function
async function approveCashPayment(bookingId) {
    if (!confirm('Are you sure the customer has paid in cash? This will mark the payment as paid and confirm the booking.')) {
        return;
    }
    
    try {
        const response = await fetch('/event-booking-website/public/api/bookings_API.php?action=approveCashPayment', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify({ id: bookingId })
        });
        
        const result = await response.json();
        
        if (result.success) {
            alert('✅ ' + result.message);
            location.reload(); // Reload to show updated status
        } else {
            alert('❌ Error: ' + (result.message || 'Failed to approve payment'));
        }
    } catch (error) {
        console.error('Error approving payment:', error);
        alert('❌ Error approving payment. Please try again.');
    }
}
</script>
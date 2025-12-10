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
?>

<div id="bookings-section" class="section-content">
    <h2 class="section-title">Ticket Bookings Management</h2>
    <p class="section-description">View, manage, and audit all user ticket purchases and payment statuses.</p>

    <div class="content-card">
        <div class="stats-grid small">
            <div class="stat-card small">
                <p class="stat-label">Total Bookings</p>
                <h3 class="stat-value" id="stat-total-bookings"><?php echo $stats['total_bookings'] ?? '0'; ?></h3>
            </div>
            <div class="stat-card small">
                <p class="stat-label">Completed</p>
                <h3 class="stat-value" id="stat-completed-bookings"><?php echo $stats['completed_bookings'] ?? '0'; ?></h3>
            </div>
            <div class="stat-card small">
                <p class="stat-label">Pending</p>
                <h3 class="stat-value" id="stat-pending-bookings"><?php echo $stats['pending_bookings'] ?? '0'; ?></h3>
            </div>
            <div class="stat-card small">
                <p class="stat-label">Cancelled</p>
                <h3 class="stat-value" id="stat-cancelled-bookings"><?php echo $stats['cancelled_bookings'] ?? '0'; ?></h3>
            </div>
            <div class="stat-card small">
                <p class="stat-label">Total Revenue</p>
                <h3 class="stat-value" id="stat-total-revenue">$<?php echo number_format($stats['total_revenue'] ?? 0, 2); ?></h3>
            </div>
        </div>
    </div>

    <div class="content-card">
        <div class="table-controls">
            <div class="controls-left">
                <div class="search-container">
                    <input type="text" id="booking-search" placeholder="Search by code, email, or event..." class="search-input">
                    <i data-feather="search" class="search-icon"></i>
                </div>
                <select id="booking-status-filter" class="filter-select">
                    <option value="">All Status</option>
                    <option value="pending">Pending</option>
                    <option value="confirmed">Confirmed</option>
                    <option value="completed">Completed</option>
                    <option value="cancelled">Cancelled</option>
                </select>
                <select id="payment-status-filter" class="filter-select">
                    <option value="">All Payments</option>
                    <option value="pending">Payment Pending</option>
                    <option value="paid">Paid</option>
                    <option value="failed">Failed</option>
                    <option value="refunded">Refunded</option>
                </select>
            </div>
            <div class="controls-right">
                <button class="icon-btn" id="refresh-bookings-btn">
                    <i data-feather="refresh-cw"></i>
                </button>
            </div>
        </div>

        <div class="table-container">
            <table class="data-table" id="bookings-table">
                <thead>
                    <tr>
                        <th>Booking Code</th>
                        <th>Event</th>
                        <th>User</th>
                        <th>Booking Date</th>
                        <th>Tickets</th>
                        <th>Amount</th>
                        <th>Status</th>
                        <th>Payment</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody id="bookings-table-body">
                    <tr>
                        <td colspan="9" class="loading-state">
                            <div class="loading-spinner"></div>
                            <p>Loading bookings...</p>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>

        <div class="table-footer">
            <div class="table-info">
                Showing <span id="bookings-start">0</span> to <span id="bookings-end">0</span> of <span id="bookings-total">0</span> entries
            </div>
            <div class="pagination">
                <button class="pagination-btn" id="bookings-prev">Previous</button>
                <div id="bookings-pages"></div>
                <button class="pagination-btn" id="bookings-next">Next</button>
            </div>
        </div>
    </div>
</div>

<!-- View Booking Details Modal -->
<div id="view-booking-modal" class="modal hidden">
    <div class="modal-content large">
        <div class="modal-header">
            <h3>Booking Details</h3>
            <button class="close-modal" data-modal="view-booking">
                <i data-feather="x"></i>
            </button>
        </div>
        <div id="booking-details" class="booking-details-container">
            <!-- Booking details will be populated by JavaScript -->
        </div>
        <div class="modal-actions">
            <button type="button" class="secondary-btn" data-modal="view-booking">Close</button>
            <button class="primary-btn" id="print-booking-btn">
                <i data-feather="printer"></i>
                <span>Print</span>
            </button>
        </div>
    </div>
</div>

<!-- Update Status Modal -->
<div id="update-status-modal" class="modal hidden">
    <div class="modal-content small">
        <div class="modal-header">
            <h3>Update Booking Status</h3>
            <button class="close-modal" data-modal="update-status">
                <i data-feather="x"></i>
            </button>
        </div>
        <div class="confirmation-content">
            <p>Update status for booking <strong id="booking-code-display"></strong>:</p>
            <select id="new-booking-status" class="status-select">
                <option value="pending">Pending</option>
                <option value="confirmed">Confirmed</option>
                <option value="completed">Completed</option>
                <option value="cancelled">Cancelled</option>
            </select>
        </div>
        <div class="modal-actions">
            <button type="button" class="secondary-btn" data-modal="update-status">Cancel</button>
            <button type="button" id="confirm-update-status-btn" class="primary-btn">Update Status</button>
        </div>
    </div>
</div>

<!-- Update Payment Status Modal -->
<div id="update-payment-modal" class="modal hidden">
    <div class="modal-content small">
        <div class="modal-header">
            <h3>Update Payment Status</h3>
            <button class="close-modal" data-modal="update-payment">
                <i data-feather="x"></i>
            </button>
        </div>
        <div class="confirmation-content">
            <p>Update payment status for booking <strong id="payment-booking-code"></strong>:</p>
            <select id="new-payment-status" class="status-select">
                <option value="pending">Payment Pending</option>
                <option value="paid">Paid</option>
                <option value="failed">Failed</option>
                <option value="refunded">Refunded</option>
            </select>
        </div>
        <div class="modal-actions">
            <button type="button" class="secondary-btn" data-modal="update-payment">Cancel</button>
            <button type="button" id="confirm-update-payment-btn" class="primary-btn">Update Payment</button>
        </div>
    </div>
</div>

<?php
// tickets.php - Admin Tickets View
// Initialize database connection
require_once __DIR__ . '/../../../config/db_connect.php';
$database = new Database();
$db = $database->getConnection();

// Include the TicketsModel
require_once __DIR__ . '/../../../app/models/TicketsModel.php';
$ticketsModel = new TicketsModel($db);

// Get ticket statistics for the dashboard
$stats = $ticketsModel->getTicketStats();

// Get events for dropdown
try {
    $eventsQuery = "SELECT id, title FROM events ORDER BY date DESC";
    $eventsStmt = $db->prepare($eventsQuery);
    $eventsStmt->execute();
    $events = $eventsStmt->fetchAll(PDO::FETCH_ASSOC);
} catch (Exception $e) {
    $events = [];
}
?>

<div id="tickets-section" class="section-content">
    <h2 class="section-title">Tickets Management</h2>
    <p class="section-description">Create, manage, and monitor all tickets for your events.</p>

    <div class="content-card">
        <div class="stats-grid small">
            <div class="stat-card small">
                <p class="stat-label">Total Tickets</p>
                <h3 class="stat-value" id="stat-total-tickets"><?php echo $stats['total_tickets'] ?? '0'; ?></h3>
            </div>
            <div class="stat-card small">
                <p class="stat-label">Active</p>
                <h3 class="stat-value" id="stat-active-tickets"><?php echo $stats['active_tickets'] ?? '0'; ?></h3>
            </div>
            <div class="stat-card small">
                <p class="stat-label">Sold Out</p>
                <h3 class="stat-value" id="stat-sold-out-tickets"><?php echo $stats['sold_out_tickets'] ?? '0'; ?></h3>
            </div>
            <div class="stat-card small">
                <p class="stat-label">Available Tickets</p>
                <h3 class="stat-value" id="stat-available-tickets"><?php echo $stats['available_quantity'] ?? '0'; ?></h3>
            </div>
            <div class="stat-card small">
                <p class="stat-label">Average Price</p>
                <h3 class="stat-value" id="stat-average-price">$<?php echo number_format($stats['average_price'] ?? 0, 2); ?></h3>
            </div>
        </div>
    </div>

    <div class="content-card">
        <div class="table-controls">
            <div class="controls-left">
                <div class="search-container">
                    <input type="text" id="ticket-search" placeholder="Search tickets..." class="search-input">
                    <i data-feather="search" class="search-icon"></i>
                </div>
                <select id="ticket-status-filter" class="filter-select">
                    <option value="">All Status</option>
                    <option value="active">Active</option>
                    <option value="inactive">Inactive</option>
                    <option value="sold_out">Sold Out</option>
                </select>
                <select id="ticket-event-filter" class="filter-select">
                    <option value="">All Events</option>
                    <?php foreach ($events as $event): ?>
                    <option value="<?php echo $event['id']; ?>"><?php echo htmlspecialchars($event['title']); ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="controls-right">
                <button class="primary-btn" id="add-ticket-btn">
                    <i data-feather="plus"></i>
                    <span>Add New Ticket</span>
                </button>
                <button class="icon-btn" id="refresh-tickets-btn">
                    <i data-feather="refresh-cw"></i>
                </button>
            </div>
        </div>

        <div class="table-container">
            <table class="data-table" id="tickets-table">
                <thead>
                    <tr>
                        <th>Ticket Name</th>
                        <th>Event</th>
                        <th>Price</th>
                        <th>Quantity</th>
                        <th>Status</th>
                        <th>Sale Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody id="tickets-table-body">
                    <tr>
                        <td colspan="8" class="loading-state">
                            <div class="loading-spinner"></div>
                            <p>Loading tickets...</p>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>

        <div class="table-footer">
            <div class="table-info">
                Showing <span id="tickets-start">0</span> to <span id="tickets-end">0</span> of <span id="tickets-total">0</span> entries
            </div>
            <div class="pagination">
                <button class="pagination-btn" id="tickets-prev">Previous</button>
                <div id="tickets-pages"></div>
                <button class="pagination-btn" id="tickets-next">Next</button>
            </div>
        </div>
    </div>
</div>

<!-- Add/Edit Ticket Modal -->
<div id="ticket-modal" class="modal hidden">
    <div class="modal-content">
        <div class="modal-header">
            <h3 id="ticket-modal-title">Add New Ticket</h3>
            <button class="close-modal" data-modal="ticket">
                <i data-feather="x"></i>
            </button>
        </div>
        <form id="ticket-form">
            <input type="hidden" id="ticket-id">
            <div class="form-grid">
                <div class="form-group">
                    <label for="ticket-event-id">Event *</label>
                    <select id="ticket-event-id" name="event_id" required>
                        <option value="">Select Event</option>
                        <?php foreach ($events as $event): ?>
                        <option value="<?php echo $event['id']; ?>"><?php echo htmlspecialchars($event['title']); ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="form-group">
                    <label for="ticket-name">Ticket Name *</label>
                    <input type="text" id="ticket-name" name="name" required placeholder="e.g., General Admission">
                </div>
                <div class="form-group two-columns">
                    <div>
                        <label for="ticket-price">Price ($) *</label>
                        <div class="price-input">
                            <span>$</span>
                            <input type="number" id="ticket-price" name="price" step="0.01" min="0" required>
                        </div>
                    </div>
                    <div>
                        <label for="ticket-discounted-price">Discounted Price ($)</label>
                        <div class="price-input">
                            <span>$</span>
                            <input type="number" id="ticket-discounted-price" name="discounted_price" step="0.01" min="0">
                        </div>
                    </div>
                </div>
                <div class="form-group two-columns">
                    <div>
                        <label for="ticket-quantity-total">Total Quantity *</label>
                        <input type="number" id="ticket-quantity-total" name="quantity_total" min="1" required>
                    </div>
                    <div>
                        <label for="ticket-status">Status</label>
                        <select id="ticket-status" name="status">
                            <option value="active">Active</option>
                            <option value="inactive">Inactive</option>
                        </select>
                    </div>
                </div>
                <div class="form-group two-columns">
                    <div>
                        <label for="ticket-min-order">Min per Order</label>
                        <input type="number" id="ticket-min-order" name="min_per_order" min="1" value="1">
                    </div>
                    <div>
                        <label for="ticket-max-order">Max per Order</label>
                        <input type="number" id="ticket-max-order" name="max_per_order" min="1" value="10">
                    </div>
                </div>
                <div class="form-group two-columns">
                    <div>
                        <label for="ticket-sales-start">Sales Start Date</label>
                        <input type="datetime-local" id="ticket-sales-start" name="sales_start_date">
                    </div>
                    <div>
                        <label for="ticket-sales-end">Sales End Date</label>
                        <input type="datetime-local" id="ticket-sales-end" name="sales_end_date">
                    </div>
                </div>
                <div class="form-group">
                    <label for="ticket-description">Description</label>
                    <textarea id="ticket-description" name="description" rows="3" placeholder="Describe what this ticket includes..."></textarea>
                </div>
                <div class="form-group full-width">
                    <label>Features (What's included)</label>
                    <div id="ticket-features-container">
                        <div class="feature-input-row">
                            <input type="text" class="feature-input" placeholder="e.g., Access to main event">
                            <button type="button" class="secondary-btn add-feature-btn">
                                <i data-feather="plus"></i>
                            </button>
                        </div>
                    </div>
                    <input type="hidden" id="ticket-features" name="features">
                </div>
                <div class="form-group">
                    <label for="ticket-currency">Currency</label>
                    <select id="ticket-currency" name="currency">
                        <option value="USD">USD ($)</option>
                        <option value="EUR">EUR (€)</option>
                        <option value="GBP">GBP (£)</option>
                    </select>
                </div>
            </div>
            <div class="modal-actions">
                <button type="button" class="secondary-btn" data-modal="ticket">Cancel</button>
                <button type="submit" class="primary-btn" id="ticket-submit-btn">
                    <span id="ticket-submit-text">Add Ticket</span>
                    <i data-feather="loader" class="hidden" id="ticket-loading-icon"></i>
                </button>
            </div>
        </form>
    </div>
</div>

<!-- View Ticket Details Modal -->
<div id="view-ticket-modal" class="modal hidden">
    <div class="modal-content large">
        <div class="modal-header">
            <h3>Ticket Details</h3>
            <button class="close-modal" data-modal="view-ticket">
                <i data-feather="x"></i>
            </button>
        </div>
        <div id="ticket-details" class="ticket-details-container">
            <!-- Ticket details will be populated by JavaScript -->
        </div>
        <div class="modal-actions">
            <button type="button" class="secondary-btn" data-modal="view-ticket">Close</button>
            <button class="primary-btn" id="edit-ticket-btn">
                <i data-feather="edit-2"></i>
                <span>Edit Ticket</span>
            </button>
        </div>
    </div>
</div>

<!-- Quick Update Modal -->
<div id="quick-update-modal" class="modal hidden">
    <div class="modal-content small">
        <div class="modal-header">
            <h3 id="quick-update-title">Update Ticket</h3>
            <button class="close-modal" data-modal="quick-update">
                <i data-feather="x"></i>
            </button>
        </div>
        <div class="confirmation-content">
            <p id="quick-update-message"></p>
            <div class="form-group" id="quick-update-field">
                <!-- Dynamic field will be inserted here -->
            </div>
        </div>
        <div class="modal-actions">
            <button type="button" class="secondary-btn" data-modal="quick-update">Cancel</button>
            <button type="button" id="confirm-quick-update-btn" class="primary-btn">Update</button>
        </div>
    </div>
</div>

<!-- Confirmation Modal -->
<div id="confirmation-modal" class="modal hidden">
    <div class="modal-content small">
        <div class="modal-header">
            <h3>Confirm Action</h3>
            <button class="close-modal" data-modal="confirmation">
                <i data-feather="x"></i>
            </button>
        </div>
        <div class="confirmation-content">
            <p id="confirmation-message">Are you sure you want to perform this action?</p>
        </div>
        <div class="modal-actions">
            <button type="button" class="secondary-btn" data-modal="confirmation">Cancel</button>
            <button type="button" id="confirm-action-btn" class="danger-btn">Confirm</button>
        </div>
    </div>
</div>
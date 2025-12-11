<?php
// tickets.php - Admin Tickets View
// Initialize database connection
require_once __DIR__ . '/../../../config/db_connect.php';
$database = new Database();
$db = $database->getConnection();

// Get events for dropdown
$events = [];
try {
    $eventsQuery = "SELECT id, title FROM events ORDER BY date DESC";
    $eventsStmt = $db->prepare($eventsQuery);
    $eventsStmt->execute();
    $events = $eventsStmt->fetchAll(PDO::FETCH_ASSOC);
} catch (Exception $e) {
    $events = [];
}

// Get tickets from database
$tickets = [];
$totalTickets = 0;
$activeTickets = 0;
$soldOutTickets = 0;
$availableTickets = 0;
$averagePrice = 0;

try {
    // Get statistics
    $statsQuery = "SELECT 
        COUNT(*) as total_tickets,
        SUM(CASE WHEN status = 'active' THEN 1 ELSE 0 END) as active_tickets,
        SUM(CASE WHEN status = 'sold_out' THEN 1 ELSE 0 END) as sold_out_tickets,
        SUM(quantity_available) as available_tickets,
        AVG(price) as average_price
    FROM tickets";
    
    $statsStmt = $db->prepare($statsQuery);
    $statsStmt->execute();
    $stats = $statsStmt->fetch(PDO::FETCH_ASSOC);
    
    $totalTickets = $stats['total_tickets'] ?? 0;
    $activeTickets = $stats['active_tickets'] ?? 0;
    $soldOutTickets = $stats['sold_out_tickets'] ?? 0;
    $availableTickets = $stats['available_tickets'] ?? 0;
    $averagePrice = number_format($stats['average_price'] ?? 0, 2);
    
    // Get tickets with event info
    $ticketsQuery = "SELECT 
        t.*,
        e.title as event_title,
        e.date as event_date
    FROM tickets t
    LEFT JOIN events e ON t.event_id = e.id
    ORDER BY t.created_at DESC";
    
    $ticketsStmt = $db->prepare($ticketsQuery);
    $ticketsStmt->execute();
    $tickets = $ticketsStmt->fetchAll(PDO::FETCH_ASSOC);
    
} catch (Exception $e) {
    // Handle error silently
    error_log("Tickets page error: " . $e->getMessage());
}
?>

<div id="tickets-section" class="section-content">
    
    <div class="content-card">
        <div class="stats-grid small">
            <div class="stat-card small">
                <p class="stat-label">Total Tickets</p>
                <h3 class="stat-value" id="stat-total-tickets"><?php echo $totalTickets; ?></h3>
            </div>
            <div class="stat-card small">
                <p class="stat-label">Active</p>
                <h3 class="stat-value" id="stat-active-tickets"><?php echo $activeTickets; ?></h3>
            </div>
            <div class="stat-card small">
                <p class="stat-label">Sold Out</p>
                <h3 class="stat-value" id="stat-sold-out-tickets"><?php echo $soldOutTickets; ?></h3>
            </div>
            <div class="stat-card small">
                <p class="stat-label">Available Tickets</p>
                <h3 class="stat-value" id="stat-available-tickets"><?php echo $availableTickets; ?></h3>
            </div>
            <div class="stat-card small">
                <p class="stat-label">Average Price</p>
                <h3 class="stat-value" id="stat-average-price">$<?php echo $averagePrice; ?></h3>
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
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody id="tickets-table-body">
                    <?php if (empty($tickets)): ?>
                    <tr>
                        <td colspan="6" class="empty-state">
                            <i data-feather="ticket"></i>
                            <p>No tickets found</p>
                        </td>
                    </tr>
                    <?php else: ?>
                        <?php foreach ($tickets as $ticket): 
                            // Calculate availability percentage
                            $availabilityPercentage = ($ticket['quantity_total'] > 0) ? 
                                ($ticket['quantity_available'] / $ticket['quantity_total']) * 100 : 0;
                            
                            $availabilityClass = 'success';
                            if ($availabilityPercentage < 20) {
                                $availabilityClass = 'danger';
                            } else if ($availabilityPercentage < 50) {
                                $availabilityClass = 'warning';
                            }
                            
                            // Format price
                            $price = number_format($ticket['price'], 2);
                            
                            // Format date
                            $eventDate = $ticket['event_date'] ? date('M d, Y', strtotime($ticket['event_date'])) : 'N/A';
                        ?>
                        <tr>
                            <td>
                                <strong><?php echo htmlspecialchars($ticket['name']); ?></strong>
                                <br><small class="text-muted"><?php echo htmlspecialchars($ticket['type']); ?></small>
                            </td>
                            <td>
                                <div class="event-info">
                                    <strong><?php echo htmlspecialchars($ticket['event_title'] ?? 'Unknown Event'); ?></strong>
                                    <small><?php echo $eventDate; ?></small>
                                </div>
                            </td>
                            <td>
                                <strong>$<?php echo $price; ?></strong>
                            </td>
                            <td>
                                <div class="quantity-info">
                                    <div class="quantity-bar">
                                        <div class="quantity-fill" style="width: <?php echo $availabilityPercentage; ?>%"></div>
                                    </div>
                                    <div class="quantity-numbers">
                                        <span class="text-<?php echo $availabilityClass; ?>"><?php echo $ticket['quantity_available']; ?></span>
                                        <span class="text-muted">/ <?php echo $ticket['quantity_total']; ?></span>
                                        <small class="text-muted">(<?php echo $ticket['quantity_sold']; ?> sold)</small>
                                    </div>
                                </div>
                            </td>
                            <td>
                                <span class="status-badge <?php echo $ticket['status']; ?>">
                                    <?php echo ucfirst($ticket['status']); ?>
                                </span>
                            </td>
                            <td>
                                <div class="action-buttons">
                                    <button class="action-btn update-status-btn" data-id="<?php echo $ticket['id']; ?>" data-name="<?php echo htmlspecialchars($ticket['name']); ?>" title="Update Status">
                                        <i data-feather="toggle-right"></i>
                                    </button>
                                    <button class="action-btn delete delete-ticket" data-id="<?php echo $ticket['id']; ?>" data-name="<?php echo htmlspecialchars($ticket['name']); ?>" title="Delete Ticket">
                                        <i data-feather="trash-2"></i>
                                    </button>
                                </div>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>

        <div class="table-footer">
            <div class="table-info">
                Showing <span id="tickets-total"><?php echo count($tickets); ?></span> entries
            </div>
        </div>
    </div>
</div>

<!-- Quick Update Modal -->
<div id="quick-update-modal" class="modal hidden">
    <div class="modal-content small">
        <div class="modal-header">
            <h3 id="quick-update-title">Update Ticket Status</h3>
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

<script>
// Simple JavaScript for basic functionality
document.addEventListener('DOMContentLoaded', function() {
    // Initialize feather icons
    if (typeof feather !== 'undefined') {
        feather.replace();
    }
    
    // Refresh button
    document.getElementById('refresh-tickets-btn')?.addEventListener('click', function() {
        window.location.reload();
    });
    
    // Search functionality
    const searchInput = document.getElementById('ticket-search');
    if (searchInput) {
        searchInput.addEventListener('input', function() {
            const searchTerm = this.value.toLowerCase();
            const rows = document.querySelectorAll('#tickets-table-body tr');
            
            rows.forEach(row => {
                const text = row.textContent.toLowerCase();
                row.style.display = text.includes(searchTerm) ? '' : 'none';
            });
        });
    }
    
    // Status filter
    const statusFilter = document.getElementById('ticket-status-filter');
    if (statusFilter) {
        statusFilter.addEventListener('change', function() {
            const filterValue = this.value;
            const rows = document.querySelectorAll('#tickets-table-body tr');
            
            rows.forEach(row => {
                if (!filterValue) {
                    row.style.display = '';
                    return;
                }
                
                const statusCell = row.querySelector('.status-badge');
                if (statusCell && statusCell.classList.contains(filterValue)) {
                    row.style.display = '';
                } else {
                    row.style.display = 'none';
                }
            });
        });
    }
    
    // Event filter
    const eventFilter = document.getElementById('ticket-event-filter');
    if (eventFilter) {
        eventFilter.addEventListener('change', function() {
            const filterValue = this.value;
            const rows = document.querySelectorAll('#tickets-table-body tr');
            
            rows.forEach(row => {
                if (!filterValue || filterValue === '') {
                    row.style.display = '';
                    return;
                }
                
                const eventName = row.querySelector('.event-info strong')?.textContent || '';
                const eventOption = eventFilter.querySelector(`option[value="${filterValue}"]`)?.textContent || '';
                
                if (eventName.includes(eventOption.split(' - ')[0])) {
                    row.style.display = '';
                } else {
                    row.style.display = 'none';
                }
            });
        });
    }
});
</script>
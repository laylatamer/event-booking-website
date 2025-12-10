<?php
// events.php - Updated with database integration
require_once __DIR__ . '/../../../config/db_connect.php';
require_once __DIR__ . '/../../../app/controllers/EventController.php';
require_once __DIR__ . '/../../../app/controllers/AdminController.php';

// Create controllers
$database = new Database();
$db = $database->getConnection();
$eventController = new EventController($db);
$adminController = new AdminController($db);

// Get all events (AdminController returns an array)
$events = $adminController->getAllEvents();

// Get categories and subcategories for filters
$categories = $adminController->getAllMainCategories();
$subcategories = $adminController->getAllSubcategories();
$venues = $adminController->getAllVenues();
?>

<!-- Events Section -->
<div id="events-section" class="section-content active">
    <div class="content-card">
        <div class="table-header">
            <h2>Events Management</h2>
            <button class="primary-btn" id="add-event-btn">
                <i data-feather="plus"></i>
                Add New Event
            </button>
        </div>

        <div class="table-controls">
            <div class="controls-left">
                <div class="search-container">
                    <input type="text" id="event-search" placeholder="Search events..." class="search-input">
                    <i data-feather="search" class="search-icon"></i>
                </div>
                <select id="event-category-filter" class="filter-select">
                    <option value="">All Categories</option>
                    <?php foreach ($categories as $category): ?>
                    <option value="<?php echo htmlspecialchars($category['name']); ?>">
                        <?php echo htmlspecialchars($category['name']); ?>
                    </option>
                    <?php endforeach; ?>
                </select>
                <select id="event-status-filter" class="filter-select">
                    <option value="">All Status</option>
                    <option value="active">Active</option>
                    <option value="inactive">Inactive</option>
                    <option value="draft">Draft</option>
                </select>
            </div>
            <div class="controls-right">
                <button class="icon-btn" id="refresh-events-btn">
                    <i data-feather="refresh-cw"></i>
                </button>
            </div>
        </div>

        <div class="table-container">
            <table class="data-table">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Event Title</th>
                        <th>Category</th>
                        <th>Date</th>
                        <th>Venue</th>
                        <th>Tickets</th>
                        <th>Price</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody id="events-table-body">
                    <?php if (empty($events)): ?>
                    <tr>
                        <td colspan="9" class="empty-state">
                            <i data-feather="calendar"></i>
                            <p>No events found</p>
                            <button class="primary-btn" id="add-first-event-btn">Add Your First Event</button>
                        </td>
                    </tr>
                    <?php else: ?>
                        <?php foreach ($events as $event): 
                            $date = new DateTime($event['date']);
                            $endDate = $event['end_date'] ? new DateTime($event['end_date']) : null;
                        ?>
                        <tr data-id="<?php echo $event['id']; ?>" data-status="<?php echo $event['status']; ?>">
                            <td>#<?php echo str_pad($event['id'], 4, '0', STR_PAD_LEFT); ?></td>
                            <td>
                                <div class="event-title-cell">
                                    <?php if ($event['image_url']): ?>
                                    <img src="<?php echo htmlspecialchars($event['image_url']); ?>" 
                                         alt="<?php echo htmlspecialchars($event['title']); ?>" 
                                         class="event-thumbnail">
                                    <?php else: ?>
                                    <div class="event-thumbnail-placeholder">
                                        <i data-feather="calendar"></i>
                                    </div>
                                    <?php endif; ?>
                                    <div>
                                        <strong><?php echo htmlspecialchars($event['title']); ?></strong>
                                        <small><?php echo substr(htmlspecialchars($event['description']), 0, 80) . '...'; ?></small>
                                    </div>
                                </div>
                            </td>
                            <td>
                                <?php 
                                // Get main category name from joined data
                                $mainCategoryName = $event['main_category_name'] ?? 'Unknown';
                                ?>
                                <span class="category-badge <?php echo $adminController->getCategoryClass($mainCategoryName); ?>">
                                    <?php echo htmlspecialchars($mainCategoryName); ?>
                                </span>
                                <small><?php echo htmlspecialchars($event['subcategory_name'] ?? 'Unknown'); ?></small>
                            </td>
                            <td>
                                <?php echo $date->format('M d, Y'); ?>
                                <?php if ($endDate && $date->format('Y-m-d') != $endDate->format('Y-m-d')): ?>
                                <br><small>to <?php echo $endDate->format('M d'); ?></small>
                                <?php endif; ?>
                            </td>
                            <td><?php echo htmlspecialchars($event['venue_name']); ?></td>
                            <td>
                                <div class="ticket-info">
                                    <span class="ticket-count"><?php echo $event['available_tickets']; ?>/<?php echo $event['total_tickets']; ?></span>
                                    <div class="ticket-progress">
                                        <div class="progress-bar" style="width: <?php echo ($event['available_tickets'] / max(1, $event['total_tickets'])) * 100; ?>%"></div>
                                    </div>
                                </div>
                            </td>
                            <td>
                                <?php if (!empty($event['discounted_price']) && $event['discounted_price'] < $event['price']): ?>
                                <span class="original-price">$<?php echo number_format($event['price'], 2); ?></span>
                                <span class="discounted-price">$<?php echo number_format($event['discounted_price'], 2); ?></span>
                                <?php else: ?>
                                $<?php echo number_format($event['price'], 2); ?>
                                <?php endif; ?>
                            </td>
                            <td>
                                <span class="status-badge <?php echo $event['status']; ?>">
                                    <?php echo ucfirst($event['status']); ?>
                                </span>
                            </td>
                            <td>
                                <div class="action-buttons">
                                    <button class="action-btn edit-event" data-id="<?php echo $event['id']; ?>" title="Edit">
                                        <i data-feather="edit-2"></i>
                                    </button>
                                    <button class="action-btn view-event" data-id="<?php echo $event['id']; ?>" title="View Details">
                                        <i data-feather="eye"></i>
                                    </button>
                                    <button class="action-btn delete delete-event" 
                                            data-id="<?php echo $event['id']; ?>" 
                                            data-name="<?php echo htmlspecialchars($event['title']); ?>" 
                                            title="Delete">
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
                Showing <span id="events-start">1</span> to <span id="events-end"><?php echo count($events); ?></span> 
                of <span id="events-total"><?php echo count($events); ?></span> events
            </div>
            <div class="pagination">
                <button class="pagination-btn" id="events-prev">Previous</button>
                <span class="pagination-info">Page 1</span>
                <button class="pagination-btn" id="events-next">Next</button>
            </div>
        </div>
    </div>
</div>

<!-- View Event Modal (Keep this one - it's not in modals.php) -->
<div id="view-event-modal" class="modal hidden">
    <div class="modal-content large">
        <div class="modal-header">
            <h3>Event Details</h3>
            <button class="close-modal" data-modal="view-event">
                <i data-feather="x"></i>
            </button>
        </div>
        <div id="event-details-content">
            <!-- Event details will be loaded here -->
        </div>
    </div>
</div>

<script>
// Debug: Check what's loaded
document.addEventListener('DOMContentLoaded', function() {
    console.log('Events page loaded');
    console.log('Add event form exists:', document.getElementById('add-event-form') ? 'Yes' : 'No');
    console.log('Admin event title exists:', document.getElementById('admin-event-title') ? 'Yes' : 'No');
    
    // Test if JavaScript file loaded
    if (typeof editEvent === 'function') {
        console.log('events.js functions are available');
    } else {
        console.log('events.js NOT loaded properly');
    }
});
</script>
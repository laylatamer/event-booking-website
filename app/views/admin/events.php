<?php
// events.php - Admin Events Management
require_once __DIR__ . '/../../../config/db_connect.php';
require_once __DIR__ . '/../../../app/controllers/AdminController.php';

// Create controllers
$database = new Database();
$db = $database->getConnection();
$adminController = new AdminController($db);

// Get all events with details
$events = $adminController->getAllEventsWithDetails();

// Get main categories, subcategories, and venues for forms
$mainCategories = $adminController->getAllMainCategories();
$venues = $adminController->getAllVenues();

// Filter active venues only
$activeVenues = array_filter($venues, function($venue) {
    return ($venue['status'] ?? '') === 'active';
});
?>

<!-- Events Section -->


        <div class="table-controls">
            <div class="controls-left">
                <div class="search-container">
                    <input type="text" id="event-search" placeholder="Search events..." class="search-input">
                    <i data-feather="search" class="search-icon"></i>
                </div>
                <select id="event-category-filter" class="filter-select">
                    <option value="">All Categories</option>
                    <?php foreach ($mainCategories as $category): ?>
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
                                <span class="category-badge">
                                    <?php echo htmlspecialchars($event['main_category_name']); ?>
                                </span>
                                <br>
                                <small><?php echo htmlspecialchars($event['subcategory_name']); ?></small>
                            </td>
                            <td>
                                <?php echo $date->format('M d, Y'); ?>
                                <br>
                                <small><?php echo $date->format('h:i A'); ?></small>
                                <?php if ($endDate && $date->format('Y-m-d') != $endDate->format('Y-m-d')): ?>
                                <br><small>to <?php echo $endDate->format('M d, h:i A'); ?></small>
                                <?php endif; ?>
                            </td>
                            <td>
                                <?php echo htmlspecialchars($event['venue_name']); ?>
                                <br>
                                <small>Capacity: <?php echo number_format($event['venue_capacity']); ?></small>
                            </td>
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
                                <br>
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
                Showing <span id="events-start"><?php echo count($events) > 0 ? '1' : '0'; ?></span> 
                to <span id="events-end"><?php echo count($events); ?></span> 
                of <span id="events-total"><?php echo count($events); ?></span> events
            </div>
        </div>
    </div>
</div>

<script>
// Populate dropdowns on page load
document.addEventListener('DOMContentLoaded', function() {
    // Populate main categories dropdown
    const mainCategorySelect = document.getElementById('admin-event-main-category');
    const editMainCategorySelect = document.getElementById('admin-edit-event-main-category');
    
    <?php foreach ($mainCategories as $category): ?>
    if (mainCategorySelect) {
        const option = document.createElement('option');
        option.value = <?php echo $category['id']; ?>;
        option.textContent = '<?php echo addslashes($category['name']); ?>';
        mainCategorySelect.appendChild(option);
    }
    if (editMainCategorySelect) {
        const option = document.createElement('option');
        option.value = <?php echo $category['id']; ?>;
        option.textContent = '<?php echo addslashes($category['name']); ?>';
        editMainCategorySelect.appendChild(option);
    }
    <?php endforeach; ?>
    
    // Populate venues dropdown
    const venueSelect = document.getElementById('admin-event-venue');
    const editVenueSelect = document.getElementById('admin-edit-event-venue');
    
    <?php foreach ($activeVenues as $venue): ?>
    if (venueSelect) {
        const option = document.createElement('option');
        option.value = <?php echo $venue['id']; ?>;
        option.textContent = '<?php echo addslashes($venue['name'] . ' - ' . $venue['city'] . ' (Cap: ' . $venue['capacity'] . ')'); ?>';
        venueSelect.appendChild(option);
    }
    if (editVenueSelect) {
        const option = document.createElement('option');
        option.value = <?php echo $venue['id']; ?>;
        option.textContent = '<?php echo addslashes($venue['name'] . ' - ' . $venue['city'] . ' (Cap: ' . $venue['capacity'] . ')'); ?>';
        editVenueSelect.appendChild(option);
    }
    <?php endforeach; ?>
    
    // Check if venues exist
    const noVenuesMessage = document.createElement('div');
    noVenuesMessage.className = 'alert-message';
    noVenuesMessage.style.cssText = 'background: #fef3c7; color: #92400e; padding: 1rem; border-radius: 0.5rem; margin-bottom: 1rem;';
    
    <?php if (empty($activeVenues)): ?>
    noVenuesMessage.innerHTML = '<strong>⚠️ No Active Venues Found!</strong><br>You need to add at least one active venue before creating events. Go to Locations section to add venues.';
    document.querySelector('.table-header').insertAdjacentElement('afterend', noVenuesMessage);
    document.getElementById('add-event-btn').disabled = true;
    document.getElementById('add-event-btn').style.opacity = '0.5';
    document.getElementById('add-event-btn').style.cursor = 'not-allowed';
    <?php endif; ?>
    
    // Check if categories exist
    <?php if (empty($mainCategories)): ?>
    if (!document.querySelector('.alert-message')) {
        noVenuesMessage.innerHTML = '<strong>⚠️ No Categories Found!</strong><br>You need to set up categories before creating events. Go to Categories section to add categories.';
        document.querySelector('.table-header').insertAdjacentElement('afterend', noVenuesMessage);
        document.getElementById('add-event-btn').disabled = true;
        document.getElementById('add-event-btn').style.opacity = '0.5';
        document.getElementById('add-event-btn').style.cursor = 'not-allowed';
    }
    <?php endif; ?>
});
</script>
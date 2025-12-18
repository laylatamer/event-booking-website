<?php
require_once __DIR__ . '/../../../config/db_connect.php';
$database = new Database();
$db = $database->getConnection();

$seatingTypes = ['standing', 'stadium', 'theatre'];
$eventsBySeatingType = [];
$statistics = [
    'total_tickets' => 0,
    'active' => 0,
    'sold_out' => 0,
    'available_tickets' => 0,
    'average_price' => 0
];

try {
    foreach ($seatingTypes as $seatingType) {
        $query = "SELECT 
                    e.id,
                    e.title,
                    e.date,
                    e.end_date,
                    e.status as event_status,
                    v.seating_type,
                    v.name as venue_name
                  FROM events e
                  JOIN venues v ON e.venue_id = v.id
                  WHERE v.seating_type = :seating_type
                  AND e.status = 'active'
                  ORDER BY e.date ASC";
        
        $stmt = $db->prepare($query);
        $stmt->bindParam(':seating_type', $seatingType);
        $stmt->execute();
        $events = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        foreach ($events as &$event) {
            $categoriesQuery = "SELECT 
                                  category_name,
                                  price,
                                  total_tickets,
                                  available_tickets
                                FROM event_ticket_categories
                                WHERE event_id = :event_id
                                ORDER BY price ASC";
            
            $catStmt = $db->prepare($categoriesQuery);
            $catStmt->bindParam(':event_id', $event['id']);
            $catStmt->execute();
            $event['categories'] = $catStmt->fetchAll(PDO::FETCH_ASSOC);
            
            $event['status'] = (strtotime($event['date']) < time()) ? 'expired' : 'active';
            
            foreach ($event['categories'] as $category) {
                $statistics['total_tickets'] += $category['total_tickets'];
                $statistics['available_tickets'] += $category['available_tickets'];
                
                if ($category['available_tickets'] == 0 && $category['total_tickets'] > 0) {
                    $statistics['sold_out']++;
                }
            }
            
            if ($event['status'] === 'active') {
                $statistics['active'] += count($event['categories']);
            }
        }
        
        $eventsBySeatingType[$seatingType] = $events;
    }
    
    $totalPrice = 0;
    $priceCount = 0;
    foreach ($eventsBySeatingType as $events) {
        foreach ($events as $event) {
            foreach ($event['categories'] as $category) {
                $totalPrice += $category['price'];
                $priceCount++;
            }
        }
    }
    
    $statistics['average_price'] = $priceCount > 0 ? number_format($totalPrice / $priceCount, 2) : '0.00';
    
} catch (Exception $e) {
    error_log("Tickets page error: " . $e->getMessage());
}
?>

<div id="tickets-section" class="section-content">
    <div class="content-card">
        <div class="stats-grid small">
            <div class="stat-card small">
                <p class="stat-label">Total Tickets</p>
                <h3 class="stat-value" id="stat-total-tickets"><?php echo $statistics['total_tickets']; ?></h3>
            </div>
            <div class="stat-card small">
                <p class="stat-label">Active</p>
                <h3 class="stat-value" id="stat-active-tickets"><?php echo $statistics['active']; ?></h3>
            </div>
            <div class="stat-card small">
                <p class="stat-label">Sold Out</p>
                <h3 class="stat-value" id="stat-sold-out-tickets"><?php echo $statistics['sold_out']; ?></h3>
            </div>
            <div class="stat-card small">
                <p class="stat-label">Available Tickets</p>
                <h3 class="stat-value" id="stat-available-tickets"><?php echo $statistics['available_tickets']; ?></h3>
            </div>
            <div class="stat-card small">
                <p class="stat-label">Average Price</p>
                <h3 class="stat-value" id="stat-average-price">$<?php echo $statistics['average_price']; ?></h3>
            </div>
        </div>
    </div>

    <?php foreach ($seatingTypes as $seatingType): 
        $events = $eventsBySeatingType[$seatingType] ?? [];
        if (empty($events)) continue;
    ?>
    <div class="content-card seating-type-container" data-seating-type="<?php echo $seatingType; ?>">
        <div class="seating-type-header">
            <h2 class="seating-type-title"><?php echo ucfirst($seatingType); ?> Events</h2>
            <span class="event-count-badge"><?php echo count($events); ?> event(s)</span>
        </div>

        <?php foreach ($events as $event): ?>
        <div class="event-container">
            <div class="event-header">
                <h3 class="event-name"><?php echo htmlspecialchars($event['title']); ?></h3>
                <span class="event-status-badge <?php echo $event['status']; ?>">
                    <?php echo ucfirst($event['status']); ?>
                </span>
            </div>

            <div class="event-categories-table">
                <table class="categories-table">
                    <thead>
                        <tr>
                            <th class="event-column">Event</th>
                            <?php 
                            $categoryCount = count($event['categories']);
                            for ($i = 0; $i < 3; $i++): 
                                $category = $event['categories'][$i] ?? null;
                            ?>
                            <th class="category-column">
                                <div class="category-header">
                                    <span class="category-name">
                                        <?php echo $category ? htmlspecialchars($category['category_name']) : '-'; ?>
                                    </span>
                                </div>
                            </th>
                            <?php endfor; ?>
                            <th class="status-column">Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td class="event-name-cell">
                                <strong><?php echo htmlspecialchars($event['title']); ?></strong>
                                <br>
                                <small class="text-muted"><?php echo htmlspecialchars($event['venue_name']); ?></small>
                                <br>
                                <small class="text-muted"><?php echo date('M d, Y', strtotime($event['date'])); ?></small>
                            </td>
                            <?php 
                            for ($i = 0; $i < 3; $i++): 
                                $category = $event['categories'][$i] ?? null;
                            ?>
                            <td class="category-data-cell">
                                <?php if ($category): ?>
                                <div class="category-info">
                                    <div class="category-label"><?php echo htmlspecialchars($category['category_name']); ?></div>
                                    <div class="category-price">$<?php echo number_format($category['price'], 2); ?></div>
                                    <div class="category-quantity">
                                        <span class="quantity-available"><?php echo $category['available_tickets']; ?></span>
                                        <span class="quantity-total">/ <?php echo $category['total_tickets']; ?></span>
                                    </div>
                                </div>
                                <?php else: ?>
                                <div class="category-info empty">
                                    <span class="text-muted">-</span>
                                </div>
                                <?php endif; ?>
                            </td>
                            <?php endfor; ?>
                            <td class="status-cell">
                                <span class="status-badge <?php echo $event['status']; ?>">
                                    <?php echo ucfirst($event['status']); ?>
                                </span>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
        <?php endforeach; ?>
    </div>
    <?php endforeach; ?>

    <?php if (empty(array_filter($eventsBySeatingType))): ?>
    <div class="content-card">
        <div class="empty-state">
            <i data-feather="ticket"></i>
            <p>No tickets found</p>
        </div>
    </div>
    <?php endif; ?>
</div>

<style>
.seating-type-container {
    margin-bottom: 2rem;
    border: 2px solid #1f2937;
    border-radius: 12px;
    overflow: hidden;
    background: #000000;
}

.seating-type-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 1.5rem;
    background: #1f2937;
    border-bottom: 2px solid #374151;
}

.seating-type-title {
    margin: 0;
    font-size: 1.5rem;
    color: #ffffff;
    text-transform: capitalize;
}

.event-count-badge {
    background: var(--primary-orange, #f97316);
    color: white;
    padding: 0.5rem 1rem;
    border-radius: 20px;
    font-size: 0.875rem;
    font-weight: 600;
}

.event-container {
    margin: 1.5rem;
    padding: 1.5rem;
    background: #111827;
    border: 1px solid #374151;
    border-radius: 8px;
    box-shadow: 0 1px 3px rgba(0, 0, 0, 0.3);
}

.event-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 1rem;
    padding-bottom: 1rem;
    border-bottom: 1px solid #374151;
}

.event-name {
    margin: 0;
    font-size: 1.25rem;
    color: #ffffff;
}

.event-status-badge {
    padding: 0.375rem 0.75rem;
    border-radius: 6px;
    font-size: 0.875rem;
    font-weight: 600;
    text-transform: capitalize;
}

.event-status-badge.active {
    background: #d1fae5;
    color: #065f46;
}

.event-status-badge.expired {
    background: #fee2e2;
    color: #991b1b;
}

.event-categories-table {
    overflow-x: auto;
}

.categories-table {
    width: 100%;
    border-collapse: collapse;
}

.categories-table thead {
    background: #1f2937;
}

.categories-table th {
    padding: 1rem;
    text-align: left;
    font-weight: 600;
    color: #ffffff;
    border-bottom: 2px solid #374151;
}

.category-column {
    min-width: 150px;
}

.category-header {
    display: flex;
    flex-direction: column;
    align-items: center;
}

.category-name {
    font-weight: 600;
    color: var(--primary-orange, #f97316);
    margin-bottom: 0.25rem;
}

.categories-table td {
    padding: 1rem;
    border-bottom: 1px solid #374151;
    vertical-align: middle;
    color: #ffffff;
}

.event-name-cell {
    min-width: 200px;
    color: #ffffff;
}

.event-name-cell .text-muted {
    color: #9ca3af;
}

.category-data-cell {
    text-align: center;
    vertical-align: top;
}

.category-info {
    padding: 0.5rem;
}

.category-info.empty {
    color: #9ca3af;
    font-style: italic;
}

.category-label {
    font-size: 0.875rem;
    font-weight: 600;
    color: #f97316;
    margin-bottom: 0.5rem;
    text-transform: capitalize;
}

.category-price {
    font-size: 1.125rem;
    font-weight: 700;
    color: #ffffff;
    margin-bottom: 0.5rem;
}

.category-quantity {
    font-size: 0.875rem;
    color: #9ca3af;
}

.quantity-available {
    font-weight: 600;
    color: #059669;
}

.quantity-total {
    color: #9ca3af;
}

.status-badge {
    padding: 0.375rem 0.75rem;
    border-radius: 6px;
    font-size: 0.875rem;
    font-weight: 600;
    text-transform: capitalize;
    display: inline-block;
}

.status-badge.active {
    background: #d1fae5;
    color: #065f46;
}

.status-badge.expired {
    background: #fee2e2;
    color: #991b1b;
}

.empty-state {
    text-align: center;
    padding: 3rem;
    color: #6b7280;
}

.empty-state i {
    width: 48px;
    height: 48px;
    margin-bottom: 1rem;
    color: #9ca3af;
}

@media (max-width: 768px) {
    .event-categories-table {
        overflow-x: scroll;
    }
    
    .category-column {
        min-width: 120px;
    }
}
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
    if (typeof feather !== 'undefined') {
        feather.replace();
    }
});
</script>

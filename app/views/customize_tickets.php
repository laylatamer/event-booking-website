<?php
// Start session
require_once __DIR__ . '/../../database/session_init.php';
require_once __DIR__ . '/../../config/db_connect.php';
require_once __DIR__ . '/../../app/controllers/EventController.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: auth.php');
    exit();
}

// Get reservation data from session (passed from checkout)
$reservationId = $_GET['reservation_id'] ?? $_SESSION['temp_reservation_id'] ?? null;
$reservationIds = $_GET['reservation_ids'] ?? $_GET['reservations'] ?? $_SESSION['temp_reservation_ids'] ?? null;
$eventId = $_GET['event_id'] ?? $_SESSION['temp_event_id'] ?? null;
$totalTickets = $_GET['tickets'] ?? $_SESSION['temp_tickets'] ?? 0;
$alreadyCustomizedCount = $_GET['already_customized'] ?? 0;


if (!$eventId || !$totalTickets) {
    header('Location: allevents.php');
    exit();
}

// Create database connection and controller
$database = new Database();
$db = $database->getConnection();
$eventController = new EventController($db);
require_once __DIR__ . '/../../app/models/TicketReservation.php';
$reservationModel = new TicketReservation($db);

// Extend expiration for reservations in customization flow (reactivate if expired)
if ($reservationIds) {
    // Extend expiration by 30 minutes and reactivate expired reservations
    $reservationModel->extendExpirationForReservations($reservationIds, 30);
}

// Fetch reservations grouped by category
$reservationsByCategory = [];
$totalTicketsFromDB = 0;

if ($reservationIds) {
    // Parse comma-separated reservation IDs
    $ids = explode(',', $reservationIds);
    $ids = array_map('trim', $ids);
    $ids = array_filter($ids);
    $ids = array_map('intval', $ids); // Convert to integers
    
    if (!empty($ids)) {
        // Query with flexible status - accept reserved, confirmed, or expired (we just reactivated them)
        $placeholders = str_repeat('?,', count($ids) - 1) . '?';
        $query = "SELECT id, category_name, quantity, status, event_id FROM ticket_reservations WHERE id IN ($placeholders) AND (status = 'reserved' OR status = 'confirmed' OR status = 'expired')";
        $stmt = $db->prepare($query);
        $stmt->execute($ids);
        
        $rowCount = 0;
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            // Verify event_id matches
            if ((int)$row['event_id'] != (int)$eventId) {
                continue;
            }
            
            // If reservation is expired, reactivate it (extendExpirationForReservations should have done this, but double-check)
            if ($row['status'] === 'expired') {
                // Reactivate this specific reservation
                $reactivateQuery = "UPDATE ticket_reservations SET status = 'reserved', expires_at = DATE_ADD(NOW(), INTERVAL 30 MINUTE) WHERE id = ?";
                $reactivateStmt = $db->prepare($reactivateQuery);
                $reactivateStmt->execute([$row['id']]);
                $row['status'] = 'reserved'; // Update local status
            }
            
            $rowCount++;
            $categoryName = $row['category_name'];
            if (!isset($reservationsByCategory[$categoryName])) {
                $reservationsByCategory[$categoryName] = [
                    'category' => $categoryName,
                    'total' => 0,
                    'reservation_ids' => []
                ];
            }
            $reservationsByCategory[$categoryName]['total'] += (int)$row['quantity'];
            $reservationsByCategory[$categoryName]['reservation_ids'][] = $row['id'];
            $totalTicketsFromDB += (int)$row['quantity'];
        }
    }
} elseif ($reservationId) {
    // Single reservation
    $query = "SELECT id, category_name, quantity FROM ticket_reservations WHERE id = ? AND event_id = ? AND status = 'reserved'";
    $stmt = $db->prepare($query);
    $stmt->execute([$reservationId, $eventId]);
    $row = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($row) {
        $categoryName = $row['category_name'];
        $reservationsByCategory[$categoryName] = [
            'category' => $categoryName,
            'total' => (int)$row['quantity'],
            'reservation_ids' => [$row['id']]
        ];
        $totalTicketsFromDB = (int)$row['quantity'];
    }
} else {
    // Fallback: Fetch all active reservations for this event and user
    $userId = $_SESSION['user_id'] ?? null;
    $sessionId = session_id();
    
    // Try with user_id first, then session_id, then just event_id
    if ($userId) {
        $query = "SELECT id, category_name, quantity, status, user_id, session_id FROM ticket_reservations 
                  WHERE event_id = ? AND (user_id = ? OR session_id = ?) AND status = 'reserved' 
                  ORDER BY reserved_at DESC";
        $stmt = $db->prepare($query);
        $stmt->execute([$eventId, $userId, $sessionId]);
    } else {
        $query = "SELECT id, category_name, quantity, status, user_id, session_id FROM ticket_reservations 
                  WHERE event_id = ? AND session_id = ? AND status = 'reserved' 
                  ORDER BY reserved_at DESC";
        $stmt = $db->prepare($query);
        $stmt->execute([$eventId, $sessionId]);
    }
    
    
    $rowCount = 0;
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        $rowCount++;
        $categoryName = $row['category_name'];
        if (!isset($reservationsByCategory[$categoryName])) {
            $reservationsByCategory[$categoryName] = [
                'category' => $categoryName,
                'total' => 0,
                'reservation_ids' => []
            ];
        }
        $reservationsByCategory[$categoryName]['total'] += (int)$row['quantity'];
        $reservationsByCategory[$categoryName]['reservation_ids'][] = $row['id'];
        $totalTicketsFromDB += (int)$row['quantity'];
    }
}

// Use DB total if available, otherwise use URL parameter
if ($totalTicketsFromDB > 0) {
    $totalTickets = $totalTicketsFromDB;
}

// Fetch event details
$event = $eventController->getEventForPublicDisplay($eventId);

if (!$event) {
    header('Location: allevents.php');
    exit();
}

// Format event date and time
$eventDate = new DateTime($event['date']);
$formattedDate = $eventDate->format('l, F j, Y');
$formattedTime = $eventDate->format('g:i A');

// Get venue information
$venueName = $event['venue']['name'] ?? 'TBD';
$venueCity = $event['venue']['city'] ?? '';
$venueAddress = $event['venue']['address'] ?? '';
$venueFullLocation = trim($venueName . ', ' . $venueCity);

// Customization cost per ticket
$costPerTicket = 9.99;

?>

<!DOCTYPE html>
<html lang="en">
<?php
    // Include path helper if not already included
    if (!defined('BASE_ASSETS_PATH')) {
        require_once __DIR__ . '/path_helper.php';
    }
?>
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Customize Your Tickets - <?php echo htmlspecialchars($event['title']); ?></title>
    
    <link rel="stylesheet" href="<?= asset('css/ticket-customize.css') ?>">
    <link rel="stylesheet" href="<?= asset('css/ticket-design.css') ?>">
    <link rel="stylesheet" href="<?= asset('css/navbar.css') ?>">
    <link rel="stylesheet" href="<?= asset('css/footer.css') ?>">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
</head>
<body>
    <?php include 'partials/header.php'; ?>
    
    <div class="customize-container">
        <!-- Page Header -->
        <div class="page-header">
            <h1>🎟️ Customize Your Physical Tickets</h1>
            <p>Make your event tickets special with personalized names!</p>
        </div>
        
        <!-- Event Info Summary -->
        <div class="selection-card">
            <h2>Event Information</h2>
            <div class="event-info-grid">
                <div class="info-item">
                    <span class="info-label">Event</span>
                    <span class="info-value highlight"><?php echo htmlspecialchars($event['title']); ?></span>
                </div>
                <div class="info-item">
                    <span class="info-label">Date & Time</span>
                    <span class="info-value"><?php echo $formattedDate; ?> at <?php echo $formattedTime; ?></span>
                </div>
                <div class="info-item">
                    <span class="info-label">Venue</span>
                    <span class="info-value"><?php echo htmlspecialchars($venueFullLocation); ?></span>
                </div>
                <div class="info-item">
                    <span class="info-label">Total Tickets Reserved</span>
                    <span class="info-value"><?php echo $totalTickets; ?> Ticket<?php echo $totalTickets > 1 ? 's' : ''; ?></span>
                </div>
            </div>
        </div>
        
        <!-- Ticket Category Selector -->
        <div class="selection-card">
            <h2>Select Tickets to Customize by Category</h2>
            <p style="color: #9ca3af; margin-bottom: 20px;">Each customized ticket costs $<?php echo number_format($costPerTicket, 2); ?> extra</p>
            
            <div id="category-selectors" class="category-selectors">
                <?php if (empty($reservationsByCategory)): ?>
                    <div style="background: rgba(239, 68, 68, 0.1); border: 2px solid #ef4444; padding: 20px; border-radius: 8px; margin-bottom: 20px;">
                        <p style="color: #ef4444; font-weight: 600;">⚠️ No ticket categories found. Please contact support.</p>
                    </div>
                <?php else: ?>
                    <?php foreach ($reservationsByCategory as $categoryData): ?>
                        <div class="category-selector-item" data-category="<?php echo htmlspecialchars($categoryData['category']); ?>">
                            <div class="category-info">
                                <span class="category-name"><?php echo htmlspecialchars($categoryData['category']); ?></span>
                                <span class="category-total">(<?php echo $categoryData['total']; ?> available)</span>
                            </div>
                            <div class="category-quantity-control">
                                <button class="qty-btn category-decrease" data-category="<?php echo htmlspecialchars($categoryData['category']); ?>">-</button>
                                <span class="qty-display category-qty" data-category="<?php echo htmlspecialchars($categoryData['category']); ?>">0</span>
                                <button class="qty-btn category-increase" data-category="<?php echo htmlspecialchars($categoryData['category']); ?>">+</button>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
            
            <div class="cost-info">
                <p><strong>Customization Fee:</strong> $<?php echo number_format($costPerTicket, 2); ?> per ticket</p>
                <p><strong>Tickets to Customize:</strong> <span id="customize-count">0</span></p>
                <p class="total-cost">Total Customization Cost: $<span id="total-cost">0.00</span></p>
            </div>
            
            <button class="generate-btn" id="generate-btn" onclick="generateTickets()" disabled>
                Generate Tickets to Customize
            </button>
        </div>
        
        <!-- Tickets Container (Hidden initially) -->
        <div id="tickets-container" class="tickets-container hidden">
            <!-- Tickets will be dynamically generated here -->
        </div>
        
        <!-- Action Buttons (Hidden initially) -->
        <div id="action-buttons" class="action-buttons hidden">
            <button class="btn btn-secondary" onclick="window.history.back()">
                ← Back to Checkout
            </button>
            <button class="btn btn-primary" onclick="saveCustomization()">
                Save & Continue to Payment →
            </button>
        </div>
    </div>
    
    <?php include 'partials/footer.php'; ?>
    
    <!-- Pass PHP data to JavaScript -->
    <script>
        const eventData = {
            id: <?php echo $eventId; ?>,
            title: <?php echo json_encode($event['title']); ?>,
            date: <?php echo json_encode($formattedDate); ?>,
            time: <?php echo json_encode($formattedTime); ?>,
            venue: <?php echo json_encode($venueFullLocation); ?>,
            venueAddress: <?php echo json_encode($venueAddress); ?>,
            totalTickets: <?php echo $totalTickets; ?>,
            costPerTicket: <?php echo $costPerTicket; ?>,
            reservationId: <?php echo json_encode($reservationId); ?>,
            reservationIds: <?php echo json_encode($reservationIds ? explode(',', $reservationIds) : []); ?>,
            reservationsByCategory: <?php echo json_encode($reservationsByCategory); ?>
        };
    </script>
    
    <script src="<?= asset('js/customize-tickets.js') ?>"></script>
    <script src="<?= asset('js/navbar.js') ?>"></script>
</body>
</html>


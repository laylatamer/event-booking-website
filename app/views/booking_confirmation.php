<?php
// Include error handler FIRST - before any other code
require_once __DIR__ . '/../../config/error_handler.php';

// Start session and require login
require_once __DIR__ . '/../../database/session_init.php';
requireLogin();

require_once __DIR__ . '/../../config/db_connect.php';

// Get booking code from URL
$bookingCode = $_GET['code'] ?? null;

if (!$bookingCode) {
    $_SESSION['error_message'] = 'No booking code provided.';
    header('Location: profile.php');
    exit;
}

$userId = (int) $_SESSION['user_id'];

// Fetch booking details
try {
    // First, let's check if the booking exists at all (without user_id check)
    $checkSql = "SELECT id, user_id FROM bookings WHERE booking_code = :booking_code LIMIT 1";
    $checkStmt = $pdo->prepare($checkSql);
    $checkStmt->execute([':booking_code' => $bookingCode]);
    $bookingCheck = $checkStmt->fetch(PDO::FETCH_ASSOC);

    if (!$bookingCheck) {
        $_SESSION['error_message'] = 'Booking not found. Please check your booking code.';
        header('Location: profile.php');
        exit;
    }

    if ((int)$bookingCheck['user_id'] !== $userId) {
        $_SESSION['error_message'] = 'You do not have permission to view this booking.';
        header('Location: profile.php');
        exit;
    }

    // Simplified query with LEFT JOINs to handle missing tables gracefully
    $bookingSql = "
        SELECT 
            b.id,
            b.booking_code,
            b.created_at,
            b.final_amount,
            b.status,
            b.payment_status,
            b.payment_method,
            b.ticket_count,
            b.subtotal,
            b.service_fee,
            b.processing_fee,
            b.customization_fee,
            b.customer_first_name,
            b.customer_last_name,
            b.customer_email,
            b.customer_phone,
            b.ticket_details,
            e.id as event_id,
            e.title as event_title,
            e.description as event_description,
            e.date as event_date,
            e.image_url as event_image,
            v.name as venue_name,
            v.address as venue_address,
            v.city as venue_city,
            COALESCE(mc.name, 'General') as category_name
        FROM bookings b
        LEFT JOIN events e ON b.event_id = e.id
        LEFT JOIN venues v ON e.venue_id = v.id
        LEFT JOIN subcategories sc ON e.subcategory_id = sc.id
        LEFT JOIN main_categories mc ON sc.main_category_id = mc.id
        WHERE b.booking_code = :booking_code AND b.user_id = :user_id
        LIMIT 1
    ";

    $bookingStmt = $pdo->prepare($bookingSql);
    $bookingStmt->execute([
        ':booking_code' => $bookingCode,
        ':user_id' => $userId
    ]);
    $booking = $bookingStmt->fetch(PDO::FETCH_ASSOC);

    if (!$booking) {
        $_SESSION['error_message'] = 'Unable to load booking details. Please try again.';
        header('Location: profile.php');
        exit;
    }

    // Fetch booked seats (if table exists)
    $seatsByCategory = [];
    try {
        $seatsSql = "
            SELECT 
                seat_id,
                category_name
            FROM booked_seats
            WHERE booking_id = :booking_id
            ORDER BY category_name, seat_id
        ";

        $seatsStmt = $pdo->prepare($seatsSql);
        $seatsStmt->execute([':booking_id' => $booking['id']]);
        $bookedSeats = $seatsStmt->fetchAll(PDO::FETCH_ASSOC);

        // Group seats by category
        foreach ($bookedSeats as $seat) {
            $category = $seat['category_name'];
            if (!isset($seatsByCategory[$category])) {
                $seatsByCategory[$category] = [];
            }
            $seatsByCategory[$category][] = $seat['seat_id'];
        }
    } catch (\PDOException $seatError) {
        // If booked_seats table doesn't exist, just continue without seat info
        error_log("Booked seats table error (non-critical): " . $seatError->getMessage());
    }

} catch (\PDOException $e) {
    // Log detailed error for debugging
    $errorDetails = "Error fetching booking details for code '$bookingCode': " . $e->getMessage() . " | SQL State: " . $e->getCode();
    error_log($errorDetails);
    
    // Store detailed error in session for debugging (remove in production)
    $_SESSION['error_message'] = 'Database error: ' . $e->getMessage();
    header('Location: profile.php');
    exit;
}

// Format dates
$eventDate = new DateTime($booking['event_date']);
$formattedEventDate = $eventDate->format('l, F j, Y');
$formattedEventTime = $eventDate->format('g:i A');

$bookingDate = new DateTime($booking['created_at']);
$formattedBookingDate = $bookingDate->format('M d, Y g:i A');

// Parse ticket details if available
$ticketDetails = [];
if (!empty($booking['ticket_details'])) {
    $ticketDetails = json_decode($booking['ticket_details'], true) ?? [];
}

// Use event image directly from database (like booking.php does)
$eventImageSrc = $booking['event_image'] ?: 'https://placehold.co/300x200/16181d/9aa3af?text=Event+Image';

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Booking Confirmation | Eحgzly</title>
    <link rel="stylesheet" href="../../public/css/booking_confirmation.css">
</head>
<body>

    <div class="navbar-wrap" role="navigation" aria-label="Primary">
        <div class="container navbar-container">
            <div class="navbar">
                <a class="brand" href="homepage.php" aria-label="Homepage"><span class="brand-name">Eحgzly</span></a>
                <div class="search" role="search">
                    <div class="search-field">
                        <input type="search" name="q" placeholder="Search events, artists, venues" />
                        <svg class="search-icon" viewBox="0 0 24 24"><path fill="currentColor" d="M15.5 14h-.79l-.28-.27a6.471 6.471 0 0 0 1.57-4.23 6.5 6.5 0 1 0-6.5 6.5 6.471 6.471 0 0 0 4.23-1.57l.27.28v.79l4.99 4.99c.39.39 1.02.39 1.41 0 .39-.39.39-1.02 0-1.41L15.5 14zm-6 0C7.01 14 5 11.99 5 9.5S7.01 5 9.5 5 14 7.01 14 9.5 11.99 14 9.5 14z"/></svg>
                    </div>
                </div>
                <div class="actions">
                    <nav class="nav" aria-label="Main">
                        <a href="allevents.php">Events</a><a href="faq.php">FAQs</a><a href="contact_form.php">Contact</a>
                    </nav>
                    <a class="profile-btn" aria-label="Profile" href="profile.php"><svg width="20" height="20" viewBox="0 0 24 24"><path d="M12 12c2.761 0 5-2.239 5-5s-2.239-5-5-5-5 2.239-5 5 2.239 5 5 5Z" fill="currentColor"/><path d="M4 20.2C4 16.88 7.582 14 12 14s8 2.88 8 6.2c0 .994-.806 1.8-1.8 1.8H5.8C4.806 22 4 21.194 4 20.2Z" fill="currentColor"/></svg></a>
                </div>
            </div>
        </div>
    </div>

    <div class="container page-content">
        <div class="confirmation-header">
            <div class="success-icon">✓</div>
            <h1>Booking Confirmed!</h1>
            <p>Your tickets have been successfully booked</p>
        </div>

        <div class="booking-details-card">
            <div class="card-header">
                <h2>Booking Information</h2>
                <span class="status-badge status-<?php echo htmlspecialchars($booking['payment_status']); ?>">
                    <?php echo htmlspecialchars(ucfirst($booking['payment_status'])); ?>
                </span>
            </div>

            <div class="booking-info-grid">
                <div class="info-item">
                    <span class="info-label">Booking Code</span>
                    <span class="info-value highlight"><?php echo htmlspecialchars($booking['booking_code']); ?></span>
                </div>
                <div class="info-item">
                    <span class="info-label">Booking Date</span>
                    <span class="info-value"><?php echo htmlspecialchars($formattedBookingDate); ?></span>
                </div>
                <div class="info-item">
                    <span class="info-label">Total Tickets</span>
                    <span class="info-value"><?php echo htmlspecialchars($booking['ticket_count']); ?></span>
                </div>
                <div class="info-item">
                    <span class="info-label">Total Amount</span>
                    <span class="info-value highlight"><?php echo number_format($booking['final_amount'], 2); ?> EGP</span>
                </div>
            </div>
        </div>

        <div class="event-details-card">
            <div class="card-header">
                <h2>Event Details</h2>
            </div>

            <div class="event-content">
                <div class="event-image">
                    <img src="<?php echo htmlspecialchars($eventImageSrc); ?>" 
                         alt="<?php echo htmlspecialchars($booking['event_title']); ?>"
                         onerror="this.onerror=null; this.src='data:image/svg+xml,%3Csvg xmlns=%22http://www.w3.org/2000/svg%22 width=%22300%22 height=%22200%22%3E%3Crect width=%22300%22 height=%22200%22 fill=%22%2316181d%22/%3E%3Ctext x=%2250%25%22 y=%2250%25%22 dominant-baseline=%22middle%22 text-anchor=%22middle%22 font-family=%22system-ui%22 font-size=%2218%22 fill=%22%239aa3af%22%3EEvent Image%3C/text%3E%3C/svg%3E';">
                </div>

                <div class="event-info">
                    <h3><?php echo htmlspecialchars($booking['event_title']); ?></h3>
                    <div class="event-meta">
                        <div class="meta-item">
                            <svg width="20" height="20" viewBox="0 0 24 24" fill="currentColor">
                                <path d="M19 4h-1V2h-2v2H8V2H6v2H5c-1.11 0-1.99.9-1.99 2L3 20c0 1.1.89 2 2 2h14c1.1 0 2-.9 2-2V6c0-1.1-.9-2-2-2zm0 16H5V10h14v10zM9 14H7v-2h2v2zm4 0h-2v-2h2v2zm4 0h-2v-2h2v2zm-8 4H7v-2h2v2zm4 0h-2v-2h2v2zm4 0h-2v-2h2v2z"/>
                            </svg>
                            <span><?php echo htmlspecialchars($formattedEventDate); ?></span>
                        </div>
                        <div class="meta-item">
                            <svg width="20" height="20" viewBox="0 0 24 24" fill="currentColor">
                                <path d="M12 2C6.5 2 2 6.5 2 12s4.5 10 10 10 10-4.5 10-10S17.5 2 12 2zm0 18c-4.41 0-8-3.59-8-8s3.59-8 8-8 8 3.59 8 8-3.59 8-8 8zm.5-13H11v6l5.2 3.2.8-1.3-4.5-2.7V7z"/>
                            </svg>
                            <span><?php echo htmlspecialchars($formattedEventTime); ?></span>
                        </div>
                        <div class="meta-item">
                            <svg width="20" height="20" viewBox="0 0 24 24" fill="currentColor">
                                <path d="M12 2C8.13 2 5 5.13 5 9c0 5.25 7 13 7 13s7-7.75 7-13c0-3.87-3.13-7-7-7zm0 9.5c-1.38 0-2.5-1.12-2.5-2.5s1.12-2.5 2.5-2.5 2.5 1.12 2.5 2.5-1.12 2.5-2.5 2.5z"/>
                            </svg>
                            <span><?php echo htmlspecialchars($booking['venue_name']); ?>, <?php echo htmlspecialchars($booking['venue_city']); ?></span>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <?php if (!empty($seatsByCategory)): ?>
            <div class="seats-card">
                <div class="card-header">
                    <h2>Your Seats</h2>
                </div>

                <div class="seats-content">
                    <?php foreach ($seatsByCategory as $category => $seats): ?>
                        <div class="seat-category">
                            <h4><?php echo htmlspecialchars($category); ?></h4>
                            <div class="seats-list">
                                <?php foreach ($seats as $seat): ?>
                                    <span class="seat-badge"><?php echo htmlspecialchars($seat); ?></span>
                                <?php endforeach; ?>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        <?php endif; ?>

        <div class="payment-breakdown-card">
            <div class="card-header">
                <h2>Payment Breakdown</h2>
            </div>

            <div class="breakdown-content">
                <div class="breakdown-item">
                    <span>Subtotal</span>
                    <span><?php echo number_format($booking['subtotal'], 2); ?> EGP</span>
                </div>
                <?php if ($booking['service_fee'] > 0): ?>
                    <div class="breakdown-item">
                        <span>Service Fee</span>
                        <span><?php echo number_format($booking['service_fee'], 2); ?> EGP</span>
                    </div>
                <?php endif; ?>
                <?php if ($booking['processing_fee'] > 0): ?>
                    <div class="breakdown-item">
                        <span>Processing Fee</span>
                        <span><?php echo number_format($booking['processing_fee'], 2); ?> EGP</span>
                    </div>
                <?php endif; ?>
                <?php if ($booking['customization_fee'] > 0): ?>
                    <div class="breakdown-item">
                        <span>Customization Fee</span>
                        <span><?php echo number_format($booking['customization_fee'], 2); ?> EGP</span>
                    </div>
                <?php endif; ?>
                <div class="breakdown-divider"></div>
                <div class="breakdown-item total">
                    <span>Total Amount</span>
                    <span><?php echo number_format($booking['final_amount'], 2); ?> EGP</span>
                </div>
                <div class="breakdown-item">
                    <span>Payment Method</span>
                    <span class="payment-method"><?php echo htmlspecialchars(ucfirst($booking['payment_method'])); ?></span>
                </div>
            </div>
        </div>

        <div class="customer-info-card">
            <div class="card-header">
                <h2>Customer Information</h2>
            </div>

            <div class="customer-info-grid">
                <div class="info-item">
                    <span class="info-label">Name</span>
                    <span class="info-value"><?php echo htmlspecialchars($booking['customer_first_name'] . ' ' . $booking['customer_last_name']); ?></span>
                </div>
                <div class="info-item">
                    <span class="info-label">Email</span>
                    <span class="info-value"><?php echo htmlspecialchars($booking['customer_email']); ?></span>
                </div>
                <?php if (!empty($booking['customer_phone'])): ?>
                    <div class="info-item">
                        <span class="info-label">Phone</span>
                        <span class="info-value"><?php echo htmlspecialchars($booking['customer_phone']); ?></span>
                    </div>
                <?php endif; ?>
            </div>
        </div>

        <div class="action-buttons">
            <a href="profile.php" class="btn btn-secondary">← Back to Profile</a>
            <button onclick="window.print()" class="btn btn-primary">🖨️ Print Tickets</button>
        </div>
    </div>

    <footer class="footer" id="footer">
        <div class="container">
            <div class="footer-top">
                <div>
                    <div class="brand-wordmark" style="background: linear-gradient(90deg, #ffffff 0%, #ffffff 48%, var(--accent-strong) 52%, var(--accent) 100%); -webkit-background-clip: text; background-clip: text; color: transparent;">Eحgzly</div>
                    <p class="footer-tagline">Discover concerts, theatre, sports, festivals and more — book securely and instantly.</p>
                </div>
                <div>
                    <p class="footer-heading">Explore</p>
                    <nav class="footer-links" aria-label="Footer">
                        <a href="#about">About Us</a>
                        <a href="faq.php">FAQs</a>
                        <a href="#contact">Contact Us</a>
                        <a href="#privacy">Privacy Policy</a>
                    </nav>
                </div>
                <div style="display:flex; align-items:center; justify-content:center;">
                    <a href="#contact" class="help-cta" aria-label="Need some help? Contact us">
                        <span>❔ Need some help? Contact us</span>
                    </a>
                </div>
            </div>
            <div class="footer-divider"></div>
            <div class="footer-middle">
                <div class="socials" aria-label="Follow us">
                    <span class="footer-heading" style="margin:0;">Follow us</span>
                    <a class="soc" href="#" aria-label="Instagram" title="Instagram">
                        <svg viewBox="0 0 24 24" aria-hidden="true">
                            <path d="M7 2h10a5 5 0 0 1 5 5v10a5 5 0 0 1-5 5H7a5 5 0 0 1-5-5V7a5 5 0 0 1 5-5Zm0 2a3 3 0 0 0-3 3v10a3 3 0 0 0 3 3h10a3 3 0 0 0 3-3V7a3 3 0 0 0-3-3H7Zm5 3.5A5.5 5.5 0 1 1 6.5 13 5.5 5.5 0 0 1 12 7.5Zm0 2A3.5 3.5 0 1 0 15.5 13 3.5 3.5 0 0 0 12 9.5Zm5.75-2.75a1.25 1.25 0 1 1-1.25 1.25 1.25 1.25 0 0 1 1.25-1.25Z"/>
                        </svg>
                    </a>
                    <a class="soc" href="#" aria-label="Facebook" title="Facebook">
                        <svg viewBox="0 0 24 24" aria-hidden="true">
                            <path d="M13.5 9H16V6h-2.5C11.57 6 10 7.57 10 9.5V11H8v3h2v7h3v-7h2.1l.4-3H13v-1.5c0-.28.22-.5.5-.5Z"/>
                        </svg>
                    </a>
                    <a class="soc" href="#" aria-label="Twitter" title="Twitter">
                        <svg viewBox="0 0 24 24" aria-hidden="true">
                            <path d="M21.5 6.5a7 7 0 0 1-2 .55 3.46 3.46 0 0 0 1.53-1.93 7 7 0 0 1-2.2.86 3.5 3.5 0 0 0-6 3.19 9.93 9.93 0 0 1-7.2-3.65 3.5 3.5 0 0 0 1.08 4.68 3.46 3.46 0 0 1-1.58-.44v.04a3.5 3.5 0 0 0 2.8 3.43 3.53 3.53 0 0 1-1.58.06 3.5 3.5 0 0 0 3.27 2.43A7.03 7.03 0 0 1 3 17.5a9.93 9.93 0 0 0 5.37 1.57c6.45 0 9.98-5.45 9.98-10.18v-.47a7.1 7.1 0 0 0 1.65-1.92Z"/>
                        </svg>
                    </a>
                    <a class="soc" href="#" aria-label="TikTok" title="TikTok">
                        <svg viewBox="0 0 24 24" aria-hidden="true">
                            <path d="M15.5 3a5.5 5.5 0 0 0 .13 1.21 5.5 5.5 0 0 0 4.16 4.17A6.98 6.98 0 0 0 20 6.5c-1.2 0-2.3-.36-3.25-1.02v8.3c0 3.2-2.6 5.77-5.8 5.72A5.75 5.75 0 0 1 5.5 13.7a5.75 5.75 0 0 1 6.8-5.68v3.06a2.75 2.75 0 1 0 2.2 2.7V3h1Z"/>
                        </svg>
                    </a>
                </div>
                <div class="copyright">© Eحgzly 2025 – <a href="#privacy" style="color:inherit; text-decoration:none; font-weight:700;">Privacy Policy</a></div>
            </div>
            <div class="footer-divider"></div>
            <div class="footer-bottom">
                <div></div>
                <div style="display:flex; gap:10px; align-items:center;">
                    <span class="footer-heading" style="margin:0;">Back to top</span>
                    <a href="#top" class="soc" aria-label="Back to top" title="Back to top" style="width:40px;height:40px;">
                        <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M12 6l6 6H6l6-6Z" fill="#111827"/></svg>
                    </a>
                </div>
            </div>
        </div>
    </footer>

</body>
</html>

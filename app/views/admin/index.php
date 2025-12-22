<?php
// Include error handler FIRST - before any other code
require_once __DIR__ . '/../../../config/error_handler.php';

// Start session and require admin access
require_once __DIR__ . '/../../../database/session_init.php';

// Require admin login
requireAdmin();

// Load admin user data from database if user_id is numeric (not admin_session)
require_once __DIR__ . '/../../../config/db_connect.php';

$adminUser = null;
$adminName = 'Administrator';
$adminEmail = $_SESSION['user_email'] ?? 'admin@egzly.com';
$adminImage = null;
$adminInitials = 'A';

// If user_id is numeric, fetch user data from database
$adminPhone = null;
$adminFirstName = null;
$adminLastName = null;
$adminAddress = null;
$adminCity = null;
$adminCountry = null;
$adminState = null;
$adminIsAdmin = false;

if (isset($_SESSION['user_id']) && is_numeric($_SESSION['user_id'])) {
    try {
        $userId = (int) $_SESSION['user_id'];
        // Fetch all user fields from database
        $userSql = "SELECT id, first_name, last_name, email, phone_number, profile_image_path, address, city, country, state, is_admin FROM users WHERE id = :id LIMIT 1";
        $userStmt = $pdo->prepare($userSql);
        $userStmt->execute([':id' => $userId]);
        $adminUser = $userStmt->fetch();
        
        if ($adminUser) {
            $adminFirstName = $adminUser['first_name'] ?? '';
            $adminLastName = $adminUser['last_name'] ?? '';
            $adminName = trim(($adminUser['first_name'] ?? '') . ' ' . ($adminUser['last_name'] ?? ''));
            if (empty($adminName)) {
                $adminName = $adminUser['email'];
            }
            $adminEmail = $adminUser['email'];
            $adminImage = $adminUser['profile_image_path'];
            $adminPhone = $adminUser['phone_number'] ?? null;
            $adminAddress = $adminUser['address'] ?? null;
            $adminCity = $adminUser['city'] ?? null;
            $adminCountry = $adminUser['country'] ?? null;
            $adminState = $adminUser['state'] ?? null;
            $adminIsAdmin = ($adminUser['is_admin'] == 1 || $adminUser['is_admin'] === '1' || $adminUser['is_admin'] === true);
            
            // Generate initials from name
            $nameParts = explode(' ', $adminName);
            if (count($nameParts) >= 2) {
                $adminInitials = strtoupper(substr($nameParts[0], 0, 1) . substr($nameParts[1], 0, 1));
            } else {
                $adminInitials = strtoupper(substr($adminName, 0, 1));
            }
        }
    } catch (\Exception $e) {
        error_log("Error loading admin user data: " . $e->getMessage());
    }
} else {
    // For admin_session, use session data
    $adminName = $_SESSION['username'] ?? $_SESSION['user_name'] ?? 'Administrator';
    $adminEmail = $_SESSION['user_email'] ?? 'admin@egzly.com';
    $adminImage = $_SESSION['user_image'] ?? null;
    
    // Generate initials from name
    $nameParts = explode(' ', $adminName);
    if (count($nameParts) >= 2) {
        $adminInitials = strtoupper(substr($nameParts[0], 0, 1) . substr($nameParts[1], 0, 1));
    } else {
        $adminInitials = strtoupper(substr($adminName, 0, 1));
    }
}

// Get the current section from URL parameter, default to dashboard
$currentSection = isset($_GET['section']) ? $_GET['section'] : 'dashboard';

// Validate section
$validSections = ['dashboard', 'users', 'bookings', 'reports', 'events', 'categories', 'locations', 'tickets', 'messages'];
if (!in_array($currentSection, $validSections)) {
    $currentSection = 'dashboard';
}
?>
<!DOCTYPE html>
<html lang="en">
<?php
    // Include path helper if not already included
    if (!defined('BASE_ASSETS_PATH')) {
        require_once __DIR__ . '/../path_helper.php';
    }
?>
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>EحGZLY - Admin Panel</title>
    <link rel="stylesheet" href="<?= asset('css/adminPanel.css') ?>">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/feather-icons/dist/feather.min.css">
</head>
<body>
    <div class="app-container">
        <?php include 'includes/sidebar.php'; ?>

        <!-- Main Content -->
        <div class="main-content">
            <?php include 'includes/header.php'; ?>

            <?php
            // Load the appropriate section
            $sectionFile = __DIR__ . '/' . $currentSection . '.php';
            
            // If we are on the dashboard, fetch the data!
            if ($currentSection === 'dashboard' || !file_exists($sectionFile)) {
                // Initialize models
                require_once __DIR__ . '/../../../app/models/User.php';
                require_once __DIR__ . '/../../../app/models/Event.php';
                require_once __DIR__ . '/../../../app/models/BookingsModel.php';
                
                $userModel = new User($pdo);
                $eventModel = new Event($pdo);
                $bookingModel = new BookingsModel($pdo);
                
                // Fetch stats
                $totalUsers = $userModel->count();
                $totalEvents = $eventModel->count();
                $bookingStats = $bookingModel->getBookingStats();
                
                $totalBookings = $bookingStats['total_bookings'] ?? 0;
                $totalRevenue = $bookingStats['total_revenue'] ?? 0;
                
                // Fetch chart data
                $bookingsChartData = $bookingModel->getBookingsLast7Days();
                $revenueChartData = $bookingModel->getRevenueByCategory();
                
                // Prepare chart data for JS
                $chartLabels = json_encode(array_keys($bookingsChartData));
                $chartValues = json_encode(array_values($bookingsChartData));
                
                $revLabels = json_encode(array_column($revenueChartData, 'name'));
                $revValues = json_encode(array_column($revenueChartData, 'revenue'));
                
                // Fetch recent bookings
                $recentBookingsData = $bookingModel->getAllBookings(1, 5); // Page 1, Limit 5
                $recentBookings = $recentBookingsData['bookings'] ?? [];
                
                // Fallback to dashboard if file not found
                include 'dashboard.php';
            } else {
                include $sectionFile;
            }
            ?>
            
            <script>
                // Show only the active section
                document.addEventListener('DOMContentLoaded', function() {
                    const allSections = document.querySelectorAll('.section-content');
                    allSections.forEach(section => {
                        section.classList.remove('active');
                    });
                    const activeSection = document.getElementById('<?php echo $currentSection; ?>-section');
                    if (activeSection) {
                        activeSection.classList.add('active');
                    }
                    // Initialize Feather icons
                    if (typeof feather !== 'undefined') {
                        feather.replace();
                    }
                });
            </script>
        </div>
    </div>

    <?php if ($currentSection !== 'locations'): ?>
    <?php include 'includes/modals.php'; ?>
<?php endif; ?>

    <script src="https://cdn.jsdelivr.net/npm/feather-icons/dist/feather.min.js"></script>
    <script>
        // Make current user ID available to JavaScript
        window.currentUserId = <?php echo isset($_SESSION['user_id']) && is_numeric($_SESSION['user_id']) ? (int)$_SESSION['user_id'] : 'null'; ?>;
    </script>
    <script src="<?= asset('js/admin/common.js') ?>"></script>
    
    <?php
    // Only include specific section JS files
    // Use asset() helper for proper path resolution
    if ($currentSection === 'categories') {
        echo '<script src="' . asset('js/admin/categories.js') . '"></script>';
    } else if (file_exists(__DIR__ . '/../../../public/js/admin/' . $currentSection . '.js')) {
        echo '<script src="' . asset('js/admin/' . $currentSection . '.js') . '"></script>';
    } else {
        echo '<!-- No JS file for section: ' . $currentSection . ' -->';
    }
    ?>
    
</body>
</html>
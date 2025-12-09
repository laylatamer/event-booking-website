<?php
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
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>EحGZLY - Admin Panel</title>
    <link rel="stylesheet" href="../../../public/css/adminPanel.css">
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
            if (file_exists($sectionFile)) {
                include $sectionFile;
            } else {
                include 'dashboard.php';
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
                });
            </script>
        </div>
    </div>

    <?php if ($currentSection !== 'locations'): ?>
    <?php include 'includes/modals.php'; ?>
<?php endif; ?>

    <script src="https://cdn.jsdelivr.net/npm/feather-icons/dist/feather.min.js"></script>
    <script src="../../../public/js/admin/common.js"></script>
    <script src="../../../public/js/admin/<?php echo $currentSection; ?>.js"></script>
</body>
</html>


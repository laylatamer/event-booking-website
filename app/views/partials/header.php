<?php
// Session should already be started by session_init.php
// But we check just in case this file is included directly
if (session_status() == PHP_SESSION_NONE) {
    require_once __DIR__ . '/../../database/session_init.php';
}

// Initialize variables to prevent undefined variable warnings
$isLoggedIn = false;
$userName = '';
$userImage = null;
$isAdmin = false;

// Check if user is logged in (check both username and user_name for compatibility)
if (isset($_SESSION['user_id']) && $_SESSION['user_id'] !== null) {
    $isLoggedIn = true;
    $userName = $_SESSION['username'] ?? $_SESSION['user_name'] ?? 'User';
    $userImage = $_SESSION['user_image'] ?? null;
    $isAdmin = isAdmin();
}

// Get profile image source
if ($isLoggedIn && !empty($userImage) && $userImage !== null) {
    $cleanPath = ltrim($userImage, '/\\');
    $profileImageSrc = '../../public/image.php?path=' . urlencode($cleanPath);
} else {
    // Use default profile picture
    $profileImageSrc = '../../public/image.php?path=';
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tickets | Home</title>
    <?php
    // Get the base path for favicon - try different possible paths
    $baseUrl = '';
    if (isset($_SERVER['REQUEST_URI'])) {
        $uri = $_SERVER['REQUEST_URI'];
        if (strpos($uri, '/event-booking-website/') !== false) {
            $baseUrl = '/event-booking-website';
        }
    }
    $faviconPath = $baseUrl . '/favicon.ico';
    ?>
    <link rel="icon" type="image/png" href="<?= htmlspecialchars($faviconPath) ?>">
    <link rel="shortcut icon" href="<?= htmlspecialchars($faviconPath) ?>">
    <link rel="apple-touch-icon" href="<?= htmlspecialchars($faviconPath) ?>">
    <link rel="stylesheet" href="../../public/css/header.css">
    <link rel="stylesheet" href="../../public/css/navbar.css">
    <script src="../../public/js/header.js"></script>
        </head>
<body>
    <div class="page-top-gap" aria-hidden="true"></div>

    <div class="container">
        
        <div class="nav-sentinel" aria-hidden="true"></div>

        <div id="navbarWrap" class="navbar-wrap" role="navigation" aria-label="Primary">
            <div class="navbar" id="navbar">
                <a class="brand" href="homepage.php" aria-label="Homepage">
                    <span class="brand-name" style="background: linear-gradient(180deg, #f8f8f8 0%, #e8e8e8 18%, #d6d6d6 32%, #bfbfbf 48%, #a4a4a4 62%, #d8d8d8 78%, #ffffff 100%); -webkit-background-clip: text; background-clip: text; color: transparent; text-shadow: 0 1px 0 rgba(255,255,255,0.6), 0 -1px 0 rgba(0,0,0,0.28), 0 2px 6px rgba(0,0,0,0.35);">Eحgzly</span>
                </a>

                <div class="search" role="search">
                    <div class="search-field">
                        <input type="search" name="q" aria-label="Search events" placeholder="Search events, artists, venues" />
                       
                        <svg class="search-icon" viewBox="0 0 24 24" aria-hidden="true">
                            <path fill="currentColor" d="M15.5 14h-.79l-.28-.27a6.471 6.471 0 0 0 1.57-4.23 6.5 6.5 0 1 0-6.5 6.5 6.471 6.471 0 0 0 4.23-1.57l.27.28v.79l4.99 4.99c.39.39 1.02.39 1.41 0 .39-.39.39-1.02 0-1.41L15.5 14zm-6 0C7.01 14 5 11.99 5 9.5S7.01 5 9.5 5 14 7.01 14 9.5 11.99 14 9.5 14z"/>
                        </svg>
                       
                        <svg class="clear-icon" viewBox="0 0 24 24" aria-hidden="true">
                            <path fill="currentColor" d="M18.3 5.71a1 1 0 0 0-1.41 0L12 10.59 7.11 5.7A1 1 0 0 0 5.7 7.11L10.59 12l-4.9 4.89a1 1 0 1 0 1.41 1.42L12 13.41l4.89 4.9a1 1 0 0 0 1.42-1.41L13.41 12l4.9-4.89a1 1 0 0 0-.01-1.4z"/>
                        </svg>
                    </div>
                </div>

                <div class="actions">
                    <nav class="nav" aria-label="Main">
                        <a href="allevents.php">Events</a>
                        <a href="faq.php">FAQs</a>
                        <a href="contact_form.php">Contact & Support</a>
                    </nav>
                   
                     <!-- DYNAMICALLY RENDERED LOGIN/LOGOUT BUTTON -->
                    <?php if ($isLoggedIn): ?>
                        <div class="auth-actions">
                            <div class="profile-dropdown">
                                <button type="button" class="profile-btn" aria-label="User menu" aria-expanded="false" aria-haspopup="true">
                                    <img src="<?= htmlspecialchars($profileImageSrc) ?>" alt="Profile" class="profile-img">
                                </button>
                                <div class="dropdown-menu">
                                    <a href="profile.php" class="dropdown-item">
                                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                            <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"></path>
                                            <circle cx="12" cy="7" r="4"></circle>
                                        </svg>
                                        <span>Profile</span>
                                    </a>
                                    <?php if ($isAdmin): ?>
                                    <a href="admin/index.php" class="dropdown-item">
                                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                            <rect x="3" y="3" width="18" height="18" rx="2" ry="2"></rect>
                                            <line x1="9" y1="3" x2="9" y2="21"></line>
                                            <line x1="3" y1="9" x2="21" y2="9"></line>
                                        </svg>
                                        <span>Admin Panel</span>
                                    </a>
                                    <?php endif; ?>
                                    <a href="logout.php" class="dropdown-item">
                                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                            <path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"></path>
                                            <polyline points="16 17 21 12 16 7"></polyline>
                                            <line x1="21" y1="12" x2="9" y2="12"></line>
                                        </svg>
                                        <span>Logout</span>
                                    </a>
                                </div>
                            </div>
                        </div>
                    <?php else: ?>
                        <!-- FIX: Using a single <a> element with button styling for valid HTML -->
                        <a href="auth.php" class="profile-btn" aria-label="Profile or Login">
                            <svg width="20" height="20" viewBox="0 0 24 24" fill="currentColor" xmlns="http://www.w3.org/2000/svg" aria-hidden="true">
                                <path d="M12 12c2.761 0 5-2.239 5-5s-2.239-5-5-5-5 2.239-5 5 2.239 5 5 5Z"/>
                                <path d="M4 20.2C4 16.88 7.582 14 12 14s8 2.88 8 6.2c0 .994-.806 1.8-1.8 1.8H5.8C4.806 22 4 21.194 4 20.2Z"/>
                            </svg>
                        </a>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        
        <div id="navPlaceholder" class="nav-placeholder" aria-hidden="true"></div>

</body>
</html>
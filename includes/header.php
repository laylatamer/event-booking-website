<?php
// Initialize session if not already started
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Initialize variables to prevent undefined variable warnings
$isLoggedIn = false;
$userName = '';

// Check if user is logged in
if (isset($_SESSION['user_id']) && isset($_SESSION['username'])) {
    $isLoggedIn = true;
    $userName = $_SESSION['username'];
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tickets | Home</title>
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
                        <a href="logout.php" class="logout-btn">
                            Logout (<?= $userName ?>)
                        </a>
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
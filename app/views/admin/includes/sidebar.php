<!-- Sidebar -->
<div class="sidebar">
    <div class="sidebar-header">
        <h1 class="logo">EحGZLY</h1>
        <p class="subtitle">Admin Portal</p>
    </div>

    <nav class="sidebar-nav">
        <ul>
            <li>
                <a href="/admin?section=dashboard" class="nav-btn <?php echo ($currentSection === 'dashboard') ? 'active' : ''; ?>" data-section="dashboard">
                    <i data-feather="grid"></i>
                    <span>Dashboard</span>
                </a>
            </li>
            <li>
                <a href="/admin?section=users" class="nav-btn <?php echo ($currentSection === 'users') ? 'active' : ''; ?>" data-section="users">
                    <i data-feather="users"></i>
                    <span>Users</span>
                </a>
            </li>
            <li>
                <a href="/admin?section=bookings" class="nav-btn <?php echo ($currentSection === 'bookings') ? 'active' : ''; ?>" data-section="bookings">
                    <i data-feather="calendar"></i>
                    <span>Bookings</span>
                </a>
            </li>
            <li>
                <a href="/admin?section=events" class="nav-btn <?php echo ($currentSection === 'events') ? 'active' : ''; ?>" data-section="events">
                    <i data-feather="layers"></i>
                    <span>Manage Events</span>
                </a>
            </li>
            <li>
                <a href="/admin?section=categories" class="nav-btn <?php echo ($currentSection === 'categories') ? 'active' : ''; ?>" data-section="categories">
                    <i data-feather="tag"></i>
                    <span>Event Categories</span>
                </a>
            </li>
            <li>
                <a href="/admin?section=locations" class="nav-btn <?php echo ($currentSection === 'locations') ? 'active' : ''; ?>" data-section="locations">
                    <i data-feather="map-pin"></i>
                    <span>Venues</span>
                </a>
            </li>
            <li>
                <a href="/admin?section=tickets" class="nav-btn <?php echo ($currentSection === 'tickets') ? 'active' : ''; ?>" data-section="tickets">
                    <i data-feather="film"></i>
                    <span>Tickets</span>
                </a>
            </li>
            <li>
                <a href="/admin?section=messages" class="nav-btn <?php echo ($currentSection === 'messages') ? 'active' : ''; ?>" data-section="messages">
                    <i data-feather="mail"></i>
                    <span>Messages</span>
                </a>
            </li>
        </ul>
    </nav>

    <div class="user-profile-sidebar">
        <div class="profile-info">
            <div class="avatar">
                <?php if (!empty($adminImage)): ?>
                    <?php 
                    $cleanPath = ltrim($adminImage, '/\\');
                    $imageSrc = '../../../public/image.php?path=' . urlencode($cleanPath);
                    ?>
                    <img src="<?php echo htmlspecialchars($imageSrc); ?>" alt="Admin Avatar" style="width: 100%; height: 100%; object-fit: cover;">
                <?php else: ?>
                    <div class="avatar-initials"><?php echo htmlspecialchars($adminInitials); ?></div>
                <?php endif; ?>
            </div>
            <div>
                <p class="username"><?php echo htmlspecialchars($adminName); ?></p>
                <p class="user-email"><?php echo htmlspecialchars($adminEmail); ?></p>
            </div>
        </div>
        <a href="/" class="exit-btn" title="Exit to Homepage">
            <i data-feather="log-out"></i>
            <span>Exit to Homepage</span>
        </a>
    </div>
</div>


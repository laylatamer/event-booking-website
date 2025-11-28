<!-- Sidebar -->
<div class="sidebar">
    <div class="sidebar-header">
        <h1 class="logo">EحGZLY</h1>
        <p class="subtitle">Admin Portal</p>
    </div>

    <nav class="sidebar-nav">
        <ul>
            <li>
                <a href="index.php?section=dashboard" class="nav-btn <?php echo ($currentSection === 'dashboard') ? 'active' : ''; ?>" data-section="dashboard">
                    <i data-feather="grid"></i>
                    <span>Dashboard</span>
                </a>
            </li>
            <li>
                <a href="index.php?section=users" class="nav-btn <?php echo ($currentSection === 'users') ? 'active' : ''; ?>" data-section="users">
                    <i data-feather="users"></i>
                    <span>Users</span>
                </a>
            </li>
            <li>
                <a href="index.php?section=bookings" class="nav-btn <?php echo ($currentSection === 'bookings') ? 'active' : ''; ?>" data-section="bookings">
                    <i data-feather="calendar"></i>
                    <span>Bookings</span>
                </a>
            </li>
            <li>
                <a href="index.php?section=reports" class="nav-btn <?php echo ($currentSection === 'reports') ? 'active' : ''; ?>" data-section="reports">
                    <i data-feather="bar-chart-2"></i>
                    <span>Reports</span>
                </a>
            </li>
            <li>
                <a href="index.php?section=events" class="nav-btn <?php echo ($currentSection === 'events') ? 'active' : ''; ?>" data-section="events">
                    <i data-feather="layers"></i>
                    <span>Manage Events</span>
                </a>
            </li>
            <li>
                <a href="index.php?section=categories" class="nav-btn <?php echo ($currentSection === 'categories') ? 'active' : ''; ?>" data-section="categories">
                    <i data-feather="tag"></i>
                    <span>Event Categories</span>
                </a>
            </li>
            <li>
                <a href="index.php?section=locations" class="nav-btn <?php echo ($currentSection === 'locations') ? 'active' : ''; ?>" data-section="locations">
                    <i data-feather="map-pin"></i>
                    <span>Locations</span>
                </a>
            </li>
            <li>
                <a href="index.php?section=tickets" class="nav-btn <?php echo ($currentSection === 'tickets') ? 'active' : ''; ?>" data-section="tickets">
                    <i data-feather="film"></i>
                    <span>Tickets</span>
                </a>
            </li>
            <li>
                <a href="index.php?section=messages" class="nav-btn <?php echo ($currentSection === 'messages') ? 'active' : ''; ?>" data-section="messages">
                    <i data-feather="mail"></i>
                    <span>Messages</span>
                </a>
            </li>
        </ul>
    </nav>

    <div class="user-profile-sidebar">
        <div class="profile-info">
            <div class="avatar">
                <img src="default-avatar.png" alt="Admin" id="user-avatar">
            </div>
            <div>
                <p class="username">Admin</p>
                <p class="user-email">admin@egzly.com</p>
            </div>
        </div>
    </div>
</div>


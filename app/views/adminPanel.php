
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>EحGZLY - Admin Panel</title>
    <link rel="stylesheet" href="../../public/css/adminPanel.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/feather-icons/dist/feather.min.css">
</head>
<body>
    <div class="app-container">
        <!-- Sidebar -->
        <div class="sidebar">
            <div class="sidebar-header">
                <h1 class="logo">EحGZLY</h1>
                <p class="subtitle">Admin Portal</p>
            </div>

            <nav class="sidebar-nav">
                <ul>
                    <li>
                        <button class="nav-btn active" data-section="dashboard">
                            <i data-feather="grid"></i>
                            <span>Dashboard</span>
                        </button>
                    </li>
                    <li>
                        <button class="nav-btn" data-section="users">
                            <i data-feather="users"></i>
                            <span>Users</span>
                        </button>
                    </li>
                    <li>
                        <button class="nav-btn" data-section="bookings">
                            <i data-feather="calendar"></i>
                            <span>Bookings</span>
                        </button>
                    </li>
                    <li>
                        <button class="nav-btn" data-section="reports">
                            <i data-feather="bar-chart-2"></i>
                            <span>Reports</span>
                        </button>
                    </li>
                    <li>
                        <button class="nav-btn" data-section="events">
                            <i data-feather="layers"></i>
                            <span>Manage Events</span>
                        </button>
                    </li>
                    <li>
                        <button class="nav-btn" data-section="categories">
                            <i data-feather="tag"></i>
                            <span>Event Categories</span>
                        </button>
                    </li>
                    <li>
                        <button class="nav-btn" data-section="locations">
                            <i data-feather="map-pin"></i>
                            <span>Locations</span>
                        </button>
                    </li>
                    <li>
                        <button class="nav-btn" data-section="tickets">
                         <i data-feather="film"></i>
                            <span>Tickets</span>
                        </button>
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

        <!-- Main Content -->
        <div class="main-content">
            <!-- Dashboard Section -->
            <div id="dashboard-section" class="section-content active">
                <div class="content-header">
                    <h2>Dashboard Overview</h2>
                    <div class="header-actions">
                        <div class="search-container">
                            <input type="text" placeholder="Search..." class="search-input">
                            <i data-feather="search" class="search-icon"></i>
                        </div>
                    </div>
                </div>

                <!-- Stats Cards -->
                <div class="stats-grid">
                    <div class="stat-card">
                        <div class="stat-header">
                            <div>
                                <p class="stat-label">Total Users</p>
                                <h3 class="stat-value">1,248</h3>
                            </div>
                            <div class="stat-icon users-icon">
                                <i data-feather="users"></i>
                            </div>
                        </div>
                        <div class="stat-trend positive">
                            <i data-feather="trending-up"></i>
                            <span>12.5% from last month</span>
                        </div>
                    </div>

                    <div class="stat-card">
                        <div class="stat-header">
                            <div>
                                <p class="stat-label">Total Events</p>
                                <h3 class="stat-value">84</h3>
                            </div>
                            <div class="stat-icon events-icon">
                                <i data-feather="calendar"></i>
                            </div>
                        </div>
                        <div class="stat-trend positive">
                            <i data-feather="trending-up"></i>
                            <span>8.2% from last month</span>
                        </div>
                    </div>

                    <div class="stat-card">
                        <div class="stat-header">
                            <div>
                                <p class="stat-label">Total Bookings</p>
                                <h3 class="stat-value">3,752</h3>
                            </div>
                            <div class="stat-icon bookings-icon">
                                <i data-feather="ticket"></i>
                            </div>
                        </div>
                        <div class="stat-trend positive">
                            <i data-feather="trending-up"></i>
                            <span>22.3% from last month</span>
                        </div>
                    </div>

                    <div class="stat-card">
                        <div class="stat-header">
                            <div>
                                <p class="stat-label">Revenue</p>
                                <h3 class="stat-value">$48,920</h3>
                            </div>
                            <div class="stat-icon revenue-icon">
                                <i data-feather="dollar-sign"></i>
                            </div>
                        </div>
                        <div class="stat-trend positive">
                            <i data-feather="trending-up"></i>
                            <span>15.7% from last month</span>
                        </div>
                    </div>
                </div>

                <!-- Recent Bookings -->
                <div class="content-card">
                    <div class="card-header">
                        <h3>Recent Bookings</h3>
                        <button class="view-all-btn">View All</button>
                    </div>
                    <div class="table-container">
                        <table class="data-table">
                            <thead>
                                <tr>
                                    <th>Booking ID</th>
                                    <th>Event</th>
                                    <th>User</th>
                                    <th>Date</th>
                                    <th>Tickets</th>
                                    <th>Amount</th>
                                    <th>Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td>#EVT-4892</td>
                                    <td>Summer Music Festival</td>
                                    <td>john.doe@example.com</td>
                                    <td>Jun 15, 2023</td>
                                    <td>2</td>
                                    <td>$120.00</td>
                                    <td><span class="status-badge completed">Completed</span></td>
                                </tr>
                                <tr>
                                    <td>#EVT-3567</td>
                                    <td>Tech Conference 2023</td>
                                    <td>sarah.smith@example.com</td>
                                    <td>Jun 12, 2023</td>
                                    <td>1</td>
                                    <td>$75.00</td>
                                    <td><span class="status-badge completed">Completed</span></td>
                                </tr>
                                <tr>
                                    <td>#EVT-2781</td>
                                    <td>Art Exhibition</td>
                                    <td>mike.johnson@example.com</td>
                                    <td>Jun 10, 2023</td>
                                    <td>4</td>
                                    <td>$60.00</td>
                                    <td><span class="status-badge pending">Pending</span></td>
                                </tr>
                                <tr>
                                    <td>#EVT-1895</td>
                                    <td>Food Festival</td>
                                    <td>emily.wilson@example.com</td>
                                    <td>Jun 8, 2023</td>
                                    <td>3</td>
                                    <td>$45.00</td>
                                    <td><span class="status-badge cancelled">Cancelled</span></td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>

                <!-- Charts Row -->
                <div class="charts-grid">
                    <div class="content-card">
                        <h3>Bookings Overview</h3>
                        <div class="chart-placeholder">
                            <p>Chart would be displayed here</p>
                        </div>
                    </div>
                    <div class="content-card">
                        <h3>Revenue by Category</h3>
                        <div class="chart-placeholder">
                            <p>Chart would be displayed here</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Users Section -->
            <div id="users-section" class="section-content">
                <div class="content-header">
                    <h2>User Management</h2>
                    <button id="add-user-btn" class="primary-btn">
                        <i data-feather="plus"></i>
                        <span>Add New User</span>
                    </button>
                </div>

                <div class="content-card">
                    <div class="table-controls">
                        <div class="controls-left">
                            <div class="search-container">
                                <input type="text" id="user-search" placeholder="Search users..." class="search-input">
                                <i data-feather="search" class="search-icon"></i>
                            </div>
                            <select id="user-role-filter" class="filter-select">
                                <option>Filter by role</option>
                                <option>Admin</option>
                                <option>User</option>
                                <option>Organizer</option>
                            </select>
                        </div>
                        <div class="controls-right">
                            <button class="icon-btn">
                                <i data-feather="download"></i>
                            </button>
                            <button class="icon-btn">
                                <i data-feather="filter"></i>
                            </button>
                        </div>
                    </div>

                    <div class="table-container">
                        <table class="data-table" id="users-table">
                            <thead>
                                <tr>
                                    <th>
                                        <div class="checkbox-header">
                                            <input type="checkbox" id="select-all-users" class="checkbox-input">
                                            <span>User</span>
                                        </div>
                                    </th>
                                    <th>Email</th>
                                    <th>Role</th>
                                    <th>Joined</th>
                                    <th>Status</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody id="users-table-body">
                                <!-- Users will be populated by JavaScript -->
                            </tbody>
                        </table>
                    </div>

                    <div class="table-footer">
                        <div class="table-info">
                            Showing <span id="users-start">1</span> to <span id="users-end">4</span> of <span id="users-total">24</span> entries
                        </div>
                        <div class="pagination">
                            <button class="pagination-btn" id="users-prev">Previous</button>
                            <button class="pagination-btn active">1</button>
                            <button class="pagination-btn">2</button>
                            <button class="pagination-btn">3</button>
                            <button class="pagination-btn" id="users-next">Next</button>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Bookings Section -->
            <div id="bookings-section" class="section-content">
                <div class="content-header">
                    <h2>Booking Management</h2>
                    <div class="header-actions">
                        <div class="search-container">
                            <input type="text" id="booking-search" placeholder="Search bookings..." class="search-input">
                            <i data-feather="search" class="search-icon"></i>
                        </div>
                    </div>
                </div>

                <div class="content-card">
                    <div class="stats-grid small">
                        <div class="stat-card small">
                            <p class="stat-label">Total Bookings</p>
                            <h3 class="stat-value">1,248</h3>
                        </div>
                        <div class="stat-card small">
                            <p class="stat-label">Completed</p>
                            <h3 class="stat-value">984</h3>
                        </div>
                        <div class="stat-card small">
                            <p class="stat-label">Pending</p>
                            <h3 class="stat-value">156</h3>
                        </div>
                        <div class="stat-card small">
                            <p class="stat-label">Cancelled</p>
                            <h3 class="stat-value">108</h3>
                        </div>
                    </div>

                    <div class="table-container">
                        <table class="data-table" id="bookings-table">
                            <thead>
                                <tr>
                                    <th>Booking ID</th>
                                    <th>Event</th>
                                    <th>User</th>
                                    <th>Date</th>
                                    <th>Tickets</th>
                                    <th>Amount</th>
                                    <th>Status</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody id="bookings-table-body">
                                <!-- Bookings will be populated by JavaScript -->
                            </tbody>
                        </table>
                    </div>

                    <div class="table-footer">
                        <div class="table-info">
                            Showing <span id="bookings-start">1</span> to <span id="bookings-end">4</span> of <span id="bookings-total">24</span> entries
                        </div>
                        <div class="pagination">
                            <button class="pagination-btn" id="bookings-prev">Previous</button>
                            <button class="pagination-btn active">1</button>
                            <button class="pagination-btn">2</button>
                            <button class="pagination-btn">3</button>
                            <button class="pagination-btn" id="bookings-next">Next</button>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Reports Section -->
            <div id="reports-section" class="section-content">
                <div class="content-header">
                    <h2>Reports & Analytics</h2>
                    <div class="header-actions">
                        <select class="filter-select">
                            <option>Last 7 days</option>
                            <option>Last 30 days</option>
                            <option>Last 90 days</option>
                            <option>This year</option>
                            <option>Custom range</option>
                        </select>
                        <button class="primary-btn">
                            <i data-feather="download"></i>
                            <span>Export</span>
                        </button>
                    </div>
                </div>

                <div class="charts-grid">
                    <div class="content-card">
                        <h3>Revenue Overview</h3>
                        <div class="chart-placeholder">
                            <p>Revenue chart would be displayed here</p>
                        </div>
                    </div>
                    <div class="content-card">
                        <h3>Bookings by Event Type</h3>
                        <div class="chart-placeholder">
                            <p>Pie chart would be displayed here</p>
                        </div>
                    </div>
                </div>

                <div class="content-card">
                    <h3>Top Events</h3>
                    <div class="table-container">
                        <table class="data-table">
                            <thead>
                                <tr>
                                    <th>Event</th>
                                    <th>Category</th>
                                    <th>Location</th>
                                    <th>Date</th>
                                    <th>Bookings</th>
                                    <th>Revenue</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td>Summer Music Festival</td>
                                    <td>Music</td>
                                    <td>Central Park</td>
                                    <td>Jun 15, 2023</td>
                                    <td>248</td>
                                    <td>$14,880</td>
                                </tr>
                                <tr>
                                    <td>Tech Conference 2023</td>
                                    <td>Technology</td>
                                    <td>Convention Center</td>
                                    <td>Jun 12, 2023</td>
                                    <td>187</td>
                                    <td>$14,025</td>
                                </tr>
                                <tr>
                                    <td>Art Exhibition</td>
                                    <td>Art</td>
                                    <td>Museum of Modern Art</td>
                                    <td>Jun 10, 2023</td>
                                    <td>156</td>
                                    <td>$2,340</td>
                                </tr>
                                <tr>
                                    <td>Food Festival</td>
                                    <td>Food & Drink</td>
                                    <td>Downtown Square</td>
                                    <td>Jun 8, 2023</td>
                                    <td>143</td>
                                    <td>$2,145</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>

                <div class="content-card">
                    <h3>User Engagement</h3>
                    <div class="stats-grid small">
                        <div class="stat-card small">
                            <p class="stat-label">Active Users</p>
                            <h3 class="stat-value">1,028</h3>
                        </div>
                        <div class="stat-card small">
                            <p class="stat-label">New Signups</p>
                            <h3 class="stat-value">156</h3>
                        </div>
                        <div class="stat-card small">
                            <p class="stat-label">Avg. Session</p>
                            <h3 class="stat-value">4.2 min</h3>
                        </div>
                    </div>
                    <div class="chart-placeholder">
                        <p>User engagement chart would be displayed here</p>
                    </div>
                </div>
            </div>

            <!-- Events Section -->
            <div id="events-section" class="section-content">
                <div class="content-header">
                    <h2>Event Management</h2>
                    <button id="add-event-btn" class="primary-btn">
                        <i data-feather="plus"></i>
                        <span>Add New Event</span>
                    </button>
                </div>

                <div class="content-card">
                    <div class="table-controls">
                        <div class="controls-left">
                            <div class="search-container">
                                <input type="text" id="event-search" placeholder="Search events..." class="search-input">
                                <i data-feather="search" class="search-icon"></i>
                            </div>
                            <select id="event-category-filter" class="filter-select">
                                <option>All Categories</option>
                                <option>Music</option>
                                <option>Technology</option>
                                <option>Art</option>
                                <option>Food & Drink</option>
                            </select>
                        </div>
                        <div class="controls-right">
                            <button class="icon-btn">
                                <i data-feather="filter"></i>
                            </button>
                        </div>
                    </div>

                    <div class="events-grid" id="events-grid">
                        <!-- Events will be populated by JavaScript -->
                    </div>

                    <div class="table-footer">
                        <div class="table-info">
                            Showing <span id="events-start">1</span> to <span id="events-end">3</span> of <span id="events-total">12</span> events
                        </div>
                        <div class="pagination">
                            <button class="pagination-btn" id="events-prev">Previous</button>
                            <button class="pagination-btn active">1</button>
                            <button class="pagination-btn">2</button>
                            <button class="pagination-btn">3</button>
                            <button class="pagination-btn" id="events-next">Next</button>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Categories Section -->
            <div id="categories-section" class="section-content">
                <div class="content-header">
                    <h2>Event Categories</h2>
                    <button id="add-category-btn" class="primary-btn">
                        <i data-feather="plus"></i>
                        <span>Add New Category</span>
                    </button>
                </div>

                <div class="content-card">
                    <div class="table-container">
                        <table class="data-table" id="categories-table">
                            <thead>
                                <tr>
                                    <th>Category</th>
                                    <th>Description</th>
                                    <th>Events</th>
                                    <th>Status</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody id="categories-table-body">
                                <!-- Categories will be populated by JavaScript -->
                            </tbody>
                        </table>
                    </div>

                    <div class="table-footer">
                        <div class="table-info">
                            Showing <span id="categories-start">1</span> to <span id="categories-end">4</span> of <span id="categories-total">8</span> categories
                        </div>
                        <div class="pagination">
                            <button class="pagination-btn" id="categories-prev">Previous</button>
                            <button class="pagination-btn active">1</button>
                            <button class="pagination-btn">2</button>
                            <button class="pagination-btn" id="categories-next">Next</button>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Locations Section -->
            <div id="locations-section" class="section-content">
                <div class="content-header">
                    <h2>Venue Locations</h2>
                    <button id="add-location-btn" class="primary-btn">
                        <i data-feather="plus"></i>
                        <span>Add New Location</span>
                    </button>
                </div>

                <div class="content-card">
                    <div class="locations-grid" id="locations-grid">
                        <!-- Locations will be populated by JavaScript -->
                    </div>

                    <div class="table-footer">
                        <div class="table-info">
                            Showing <span id="locations-start">1</span> to <span id="locations-end">3</span> of <span id="locations-total">9</span> locations
                        </div>
                        <div class="pagination">
                            <button class="pagination-btn" id="locations-prev">Previous</button>
                            <button class="pagination-btn active">1</button>
                            <button class="pagination-btn">2</button>
                            <button class="pagination-btn">3</button>
                            <button class="pagination-btn" id="locations-next">Next</button>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Tickets Section -->
            <div id="tickets-section" class="section-content">
                <div class="content-header">
                    <h2>Ticket Management</h2>
                    <button id="add-ticket-btn" class="primary-btn">
                        <i data-feather="plus"></i>
                        <span>Add New Ticket Type</span>
                    </button>
                </div>

                <div class="content-card">
                    <div class="table-container">
                        <table class="data-table" id="tickets-table">
                            <thead>
                                <tr>
                                    <th>Event</th>
                                    <th>Ticket Type</th>
                                    <th>Price</th>
                                    <th>Available</th>
                                    <th>Sold</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody id="tickets-table-body">
                                <!-- Tickets will be populated by JavaScript -->
                            </tbody>
                        </table>
                    </div>

                    <div class="table-footer">
                        <div class="table-info">
                            Showing <span id="tickets-start">1</span> to <span id="tickets-end">4</span> of <span id="tickets-total">16</span> ticket types
                        </div>
                        <div class="pagination">
                            <button class="pagination-btn" id="tickets-prev">Previous</button>
                            <button class="pagination-btn active">1</button>
                            <button class="pagination-btn">2</button>
                            <button class="pagination-btn">3</button>
                            <button class="pagination-btn" id="tickets-next">Next</button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Modals - All hidden by default -->
    <!-- Add User Modal -->
    <div id="add-user-modal" class="modal hidden">
        <div class="modal-content">
            <div class="modal-header">
                <h3>Add New User</h3>
                <button class="close-modal" data-modal="add-user">
                    <i data-feather="x"></i>
                </button>
            </div>
            <form id="add-user-form">
                <div class="form-grid">
                    <div class="form-group">
                        <label>Full Name</label>
                        <input type="text" id="user-name" required>
                    </div>
                    <div class="form-group">
                        <label>Email</label>
                        <input type="email" id="user-email" required>
                    </div>
                    <div class="form-group">
                        <label>Role</label>
                        <select id="user-role" required>
                            <option value="">Select Role</option>
                            <option value="admin">Admin</option>
                            <option value="user">User</option>
                            <option value="organizer">Organizer</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label>Password</label>
                        <input type="password" id="user-password" required>
                    </div>
                </div>
                <div class="modal-actions">
                    <button type="button" class="secondary-btn" data-modal="add-user">Cancel</button>
                    <button type="submit" class="primary-btn">Save User</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Edit User Modal -->
    <div id="edit-user-modal" class="modal hidden">
        <div class="modal-content">
            <div class="modal-header">
                <h3>Edit User</h3>
                <button class="close-modal" data-modal="edit-user">
                    <i data-feather="x"></i>
                </button>
            </div>
            <form id="edit-user-form">
                <input type="hidden" id="edit-user-id">
                <div class="form-grid">
                    <div class="form-group">
                        <label>Full Name</label>
                        <input type="text" id="edit-user-name" required>
                    </div>
                    <div class="form-group">
                        <label>Email</label>
                        <input type="email" id="edit-user-email" required>
                    </div>
                    <div class="form-group">
                        <label>Role</label>
                        <select id="edit-user-role" required>
                            <option value="admin">Admin</option>
                            <option value="user">User</option>
                            <option value="organizer">Organizer</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label>Status</label>
                        <select id="edit-user-status" required>
                            <option value="active">Active</option>
                            <option value="inactive">Inactive</option>
                            <option value="pending">Pending</option>
                        </select>
                    </div>
                </div>
                <div class="modal-actions">
                    <button type="button" class="secondary-btn" data-modal="edit-user">Cancel</button>
                    <button type="submit" class="primary-btn">Update User</button>
                </div>
            </form>
        </div>
    </div>

    <!-- View Booking Modal -->
    <div id="view-booking-modal" class="modal hidden">
        <div class="modal-content">
            <div class="modal-header">
                <h3>Booking Details</h3>
                <button class="close-modal" data-modal="view-booking">
                    <i data-feather="x"></i>
                </button>
            </div>
            <div id="booking-details">
                <!-- Booking details will be populated by JavaScript -->
            </div>
            <div class="modal-actions">
                <button type="button" class="secondary-btn" data-modal="view-booking">Close</button>
                <button class="primary-btn" id="print-booking-btn">
                    <i data-feather="printer"></i>
                    <span>Print</span>
                </button>
            </div>
        </div>
    </div>

    <!-- Add Event Modal -->
    <div id="add-event-modal" class="modal hidden">
        <div class="modal-content large">
            <div class="modal-header">
                <h3>Add New Event</h3>
                <button class="close-modal" data-modal="add-event">
                    <i data-feather="x"></i>
                </button>
            </div>
            <form id="add-event-form">
                <div class="form-grid two-columns">
                    <div class="form-group">
                        <label>Event Name</label>
                        <input type="text" id="event-name" required>
                    </div>
                    <div class="form-group">
                        <label>Category</label>
                        <select id="event-category" required>
                            <option value="">Select Category</option>
                            <option value="music">Music</option>
                            <option value="technology">Technology</option>
                            <option value="art">Art</option>
                            <option value="food">Food & Drink</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label>Start Date</label>
                        <input type="date" id="event-start-date" required>
                    </div>
                    <div class="form-group">
                        <label>End Date</label>
                        <input type="date" id="event-end-date" required>
                    </div>
                    <div class="form-group">
                        <label>Location</label>
                        <select id="event-location" required>
                            <option value="">Select Location</option>
                            <option value="central-park">Central Park</option>
                            <option value="convention-center">Convention Center</option>
                            <option value="museum">Museum of Modern Art</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label>Organizer</label>
                        <select id="event-organizer" required>
                            <option value="">Select Organizer</option>
                            <option value="music-festivals">Music Festivals Inc.</option>
                            <option value="tech-events">Tech Events LLC</option>
                            <option value="art-exhibitions">Art Exhibitions Co.</option>
                        </select>
                    </div>
                    <div class="form-group full-width">
                        <label>Description</label>
                        <textarea id="event-description" rows="3"></textarea>
                    </div>
                    <div class="form-group full-width">
                        <label>Event Image</label>
                        <div class="file-upload">
                            <i data-feather="upload"></i>
                            <p>Click to upload or drag and drop</p>
                            <input type="file" id="event-image" accept="image/*">
                        </div>
                    </div>
                </div>
                <div class="modal-actions">
                    <button type="button" class="secondary-btn" data-modal="add-event">Cancel</button>
                    <button type="submit" class="primary-btn">Create Event</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Edit Event Modal -->
    <div id="edit-event-modal" class="modal hidden">
        <div class="modal-content large">
            <div class="modal-header">
                <h3>Edit Event</h3>
                <button class="close-modal" data-modal="edit-event">
                    <i data-feather="x"></i>
                </button>
            </div>
            <form id="edit-event-form">
                <input type="hidden" id="edit-event-id">
                <div class="form-grid two-columns">
                    <div class="form-group">
                        <label>Event Name</label>
                        <input type="text" id="edit-event-name" required>
                    </div>
                    <div class="form-group">
                        <label>Category</label>
                        <select id="edit-event-category" required>
                            <option value="music">Music</option>
                            <option value="technology">Technology</option>
                            <option value="art">Art</option>
                            <option value="food">Food & Drink</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label>Start Date</label>
                        <input type="date" id="edit-event-start-date" required>
                    </div>
                    <div class="form-group">
                        <label>End Date</label>
                        <input type="date" id="edit-event-end-date" required>
                    </div>
                    <div class="form-group">
                        <label>Location</label>
                        <select id="edit-event-location" required>
                            <option value="central-park">Central Park</option>
                            <option value="convention-center">Convention Center</option>
                            <option value="museum">Museum of Modern Art</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label>Organizer</label>
                        <select id="edit-event-organizer" required>
                            <option value="music-festivals">Music Festivals Inc.</option>
                            <option value="tech-events">Tech Events LLC</option>
                            <option value="art-exhibitions">Art Exhibitions Co.</option>
                        </select>
                    </div>
                    <div class="form-group full-width">
                        <label>Description</label>
                        <textarea id="edit-event-description" rows="3"></textarea>
                    </div>
                    <div class="form-group full-width">
                        <label>Event Image</label>
                        <div class="file-upload">
                            <i data-feather="upload"></i>
                            <p>Click to upload or drag and drop</p>
                            <input type="file" id="edit-event-image" accept="image/*">
                        </div>
                    </div>
                </div>
                <div class="modal-actions">
                    <button type="button" class="secondary-btn" data-modal="edit-event">Cancel</button>
                    <button type="submit" class="primary-btn">Update Event</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Add Category Modal -->
    <div id="add-category-modal" class="modal hidden">
        <div class="modal-content">
            <div class="modal-header">
                <h3>Add New Category</h3>
                <button class="close-modal" data-modal="add-category">
                    <i data-feather="x"></i>
                </button>
            </div>
            <form id="add-category-form">
                <div class="form-grid">
                    <div class="form-group">
                        <label>Category Name</label>
                        <input type="text" id="category-name" required>
                    </div>
                    <div class="form-group">
                        <label>Description</label>
                        <textarea id="category-description" rows="3"></textarea>
                    </div>
                    <div class="form-group">
                        <label>Icon</label>
                        <select id="category-icon" required>
                            <option value="">Select Icon</option>
                            <option value="music">Music</option>
                            <option value="cpu">Technology</option>
                            <option value="palette">Art</option>
                            <option value="coffee">Food & Drink</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label>Color</label>
                        <select id="category-color" required>
                            <option value="">Select Color</option>
                            <option value="orange">Orange</option>
                            <option value="black">Black</option>
                            <option value="white">White</option>
                        </select>
                    </div>
                </div>
                <div class="modal-actions">
                    <button type="button" class="secondary-btn" data-modal="add-category">Cancel</button>
                    <button type="submit" class="primary-btn">Add Category</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Edit Category Modal -->
    <div id="edit-category-modal" class="modal hidden">
        <div class="modal-content">
            <div class="modal-header">
                <h3>Edit Category</h3>
                <button class="close-modal" data-modal="edit-category">
                    <i data-feather="x"></i>
                </button>
            </div>
            <form id="edit-category-form">
                <input type="hidden" id="edit-category-id">
                <div class="form-grid">
                    <div class="form-group">
                        <label>Category Name</label>
                        <input type="text" id="edit-category-name" required>
                    </div>
                    <div class="form-group">
                        <label>Description</label>
                        <textarea id="edit-category-description" rows="3"></textarea>
                    </div>
                    <div class="form-group">
                        <label>Icon</label>
                        <select id="edit-category-icon" required>
                            <option value="music">Music</option>
                            <option value="cpu">Technology</option>
                            <option value="palette">Art</option>
                            <option value="coffee">Food & Drink</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label>Color</label>
                        <select id="edit-category-color" required>
                            <option value="orange">Orange</option>
                            <option value="black">Black</option>
                            <option value="white">White</option>
                        </select>
                    </div>
                </div>
                <div class="modal-actions">
                    <button type="button" class="secondary-btn" data-modal="edit-category">Cancel</button>
                    <button type="submit" class="primary-btn">Update Category</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Add Location Modal -->
    <div id="add-location-modal" class="modal hidden">
        <div class="modal-content">
            <div class="modal-header">
                <h3>Add New Location</h3>
                <button class="close-modal" data-modal="add-location">
                    <i data-feather="x"></i>
                </button>
            </div>
            <form id="add-location-form">
                <div class="form-grid">
                    <div class="form-group">
                        <label>Venue Name</label>
                        <input type="text" id="location-name" required>
                    </div>
                    <div class="form-group">
                        <label>Address</label>
                        <input type="text" id="location-address" required>
                    </div>
                    <div class="form-group two-columns">
                        <div>
                            <label>City</label>
                            <input type="text" id="location-city" required>
                        </div>
                        <div>
                            <label>State</label>
                            <input type="text" id="location-state" required>
                        </div>
                    </div>
                    <div class="form-group">
                        <label>Capacity</label>
                        <input type="number" id="location-capacity" required>
                    </div>
                    <div class="form-group">
                        <label>Venue Image</label>
                        <div class="file-upload">
                            <i data-feather="upload"></i>
                            <p>Click to upload or drag and drop</p>
                            <input type="file" id="location-image" accept="image/*">
                        </div>
                    </div>
                </div>
                <div class="modal-actions">
                    <button type="button" class="secondary-btn" data-modal="add-location">Cancel</button>
                    <button type="submit" class="primary-btn">Add Location</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Edit Location Modal -->
    <div id="edit-location-modal" class="modal hidden">
        <div class="modal-content">
            <div class="modal-header">
                <h3>Edit Location</h3>
                <button class="close-modal" data-modal="edit-location">
                    <i data-feather="x"></i>
                </button>
            </div>
            <form id="edit-location-form">
                <input type="hidden" id="edit-location-id">
                <div class="form-grid">
                    <div class="form-group">
                        <label>Venue Name</label>
                        <input type="text" id="edit-location-name" required>
                    </div>
                    <div class="form-group">
                        <label>Address</label>
                        <input type="text" id="edit-location-address" required>
                    </div>
                    <div class="form-group two-columns">
                        <div>
                            <label>City</label>
                            <input type="text" id="edit-location-city" required>
                        </div>
                        <div>
                            <label>State</label>
                            <input type="text" id="edit-location-state" required>
                        </div>
                    </div>
                    <div class="form-group">
                        <label>Capacity</label>
                        <input type="number" id="edit-location-capacity" required>
                    </div>
                    <div class="form-group">
                        <label>Venue Image</label>
                        <div class="file-upload">
                            <i data-feather="upload"></i>
                            <p>Click to upload or drag and drop</p>
                            <input type="file" id="edit-location-image" accept="image/*">
                        </div>
                    </div>
                </div>
                <div class="modal-actions">
                    <button type="button" class="secondary-btn" data-modal="edit-location">Cancel</button>
                    <button type="submit" class="primary-btn">Update Location</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Add Ticket Modal -->
    <div id="add-ticket-modal" class="modal hidden">
        <div class="modal-content">
            <div class="modal-header">
                <h3>Add New Ticket Type</h3>
                <button class="close-modal" data-modal="add-ticket">
                    <i data-feather="x"></i>
                </button>
            </div>
            <form id="add-ticket-form">
                <div class="form-grid">
                    <div class="form-group">
                        <label>Event</label>
                        <select id="ticket-event" required>
                            <option value="">Select Event</option>
                            <option value="summer-music">Summer Music Festival</option>
                            <option value="tech-conference">Tech Conference 2023</option>
                            <option value="art-exhibition">Art Exhibition</option>
                            <option value="food-festival">Food Festival</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label>Ticket Type</label>
                        <input type="text" id="ticket-type" required>
                    </div>
                    <div class="form-group">
                        <label>Price</label>
                        <div class="price-input">
                            <span>$</span>
                            <input type="number" step="0.01" id="ticket-price" required>
                        </div>
                    </div>
                    <div class="form-group">
                        <label>Quantity Available</label>
                        <input type="number" id="ticket-quantity" required>
                    </div>
                    <div class="form-group">
                        <label>Description</label>
                        <textarea id="ticket-description" rows="3"></textarea>
                    </div>
                </div>
                <div class="modal-actions">
                    <button type="button" class="secondary-btn" data-modal="add-ticket">Cancel</button>
                    <button type="submit" class="primary-btn">Add Ticket Type</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Edit Ticket Modal -->
    <div id="edit-ticket-modal" class="modal hidden">
        <div class="modal-content">
            <div class="modal-header">
                <h3>Edit Ticket Type</h3>
                <button class="close-modal" data-modal="edit-ticket">
                    <i data-feather="x"></i>
                </button>
            </div>
            <form id="edit-ticket-form">
                <input type="hidden" id="edit-ticket-id">
                <div class="form-grid">
                    <div class="form-group">
                        <label>Event</label>
                        <select id="edit-ticket-event" required>
                            <option value="summer-music">Summer Music Festival</option>
                            <option value="tech-conference">Tech Conference 2023</option>
                            <option value="art-exhibition">Art Exhibition</option>
                            <option value="food-festival">Food Festival</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label>Ticket Type</label>
                        <input type="text" id="edit-ticket-type" required>
                    </div>
                    <div class="form-group">
                        <label>Price</label>
                        <div class="price-input">
                            <span>$</span>
                            <input type="number" step="0.01" id="edit-ticket-price" required>
                        </div>
                    </div>
                    <div class="form-group">
                        <label>Quantity Available</label>
                        <input type="number" id="edit-ticket-quantity" required>
                    </div>
                    <div class="form-group">
                        <label>Description</label>
                        <textarea id="edit-ticket-description" rows="3"></textarea>
                    </div>
                </div>
                <div class="modal-actions">
                    <button type="button" class="secondary-btn" data-modal="edit-ticket">Cancel</button>
                    <button type="submit" class="primary-btn">Update Ticket Type</button>
                </div>
            </form>
        </div>
    </div>

    <!-- User Profile Modal -->
    <div id="user-profile-modal" class="modal hidden">
        <div class="modal-content">
            <div class="modal-header">
                <h3>User Profile</h3>
                <button class="close-modal" data-modal="user-profile">
                    <i data-feather="x"></i>
                </button>
            </div>
            <form id="user-profile-form">
                <div class="profile-avatar-section">
                    <div class="avatar-upload">
                        <div class="avatar-preview">
                            <img id="profile-avatar-preview" src="default-avatar.png" alt="Profile Avatar">
                        </div>
                        <div class="avatar-upload-controls">
                            <input type="file" id="avatar-upload" accept="image/*" class="hidden">
                            <button type="button" id="change-avatar-btn" class="secondary-btn">Change Avatar</button>
                        </div>
                    </div>
                </div>
                <div class="form-grid">
                    <div class="form-group">
                        <label>Full Name</label>
                        <input type="text" id="profile-name" value="Admin" required>
                    </div>
                    <div class="form-group">
                        <label>Email</label>
                        <input type="email" id="profile-email" value="admin@egzly.com" required>
                    </div>
                    <div class="form-group">
                        <label>Role</label>
                        <input type="text" id="profile-role" value="Administrator" readonly>
                    </div>
                    <div class="form-group">
                        <label>Phone</label>
                        <input type="tel" id="profile-phone" value="+1 (555) 123-4567">
                    </div>
                </div>
                <div class="modal-actions">
                    <button type="button" id="logout-btn" class="danger-btn">Logout</button>
                    <button type="submit" class="primary-btn">Save Changes</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Confirmation Modal -->
    <div id="confirmation-modal" class="modal hidden">
        <div class="modal-content small">
            <div class="modal-header">
                <h3>Confirm Action</h3>
                <button class="close-modal" data-modal="confirmation">
                    <i data-feather="x"></i>
                </button>
            </div>
            <div class="confirmation-content">
                <p id="confirmation-message">Are you sure you want to perform this action?</p>
            </div>
            <div class="modal-actions">
                <button type="button" class="secondary-btn" data-modal="confirmation">Cancel</button>
                <button type="button" id="confirm-action-btn" class="danger-btn">Confirm</button>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/feather-icons/dist/feather.min.js"></script>
    <script src="../../public/js/adminPanel.js"></script>
</body>
</html>
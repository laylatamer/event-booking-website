<?php
// Define header titles for each section
$headerTitles = [
    'dashboard' => 'Dashboard Overview',
    'users' => 'User Management',
    'bookings' => 'Booking Management',
    'reports' => 'Reports & Analytics',
    'events' => 'Event Management',
    'categories' => 'Event Categories',
    'locations' => 'Venue Locations',
    'tickets' => 'Ticket Management',
    'messages' => 'Contact Messages'
];

$currentTitle = isset($headerTitles[$currentSection]) ? $headerTitles[$currentSection] : 'Admin Panel';
?>

<div class="content-header">
    <h2><?php echo $currentTitle; ?></h2>
    <div class="header-actions">
        <?php if ($currentSection === 'users'): ?>
            <button id="add-user-btn" class="primary-btn">
                <i data-feather="plus"></i>
                <span>Add New User</span>
            </button>
        <?php elseif ($currentSection === 'bookings'): ?>
            <div class="search-container">
                <input type="text" id="booking-search" placeholder="Search bookings..." class="search-input">
                <i data-feather="search" class="search-icon"></i>
            </div>
        <?php elseif ($currentSection === 'reports'): ?>
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
        <?php elseif ($currentSection === 'events'): ?>
            <button id="add-event-btn" class="primary-btn">
                <i data-feather="plus"></i>
                <span>Add New Event</span>
            </button>
       
        <?php elseif ($currentSection === 'locations'): ?>
            <button id="add-location-btn" class="primary-btn">
                <i data-feather="plus"></i>
                <span>Add New Location</span>
            </button>
        <?php elseif ($currentSection === 'tickets'): ?>
            <button id="add-ticket-btn" class="primary-btn">
                <i data-feather="plus"></i>
                <span>Add New Ticket Type</span>
            </button>
        <?php elseif ($currentSection === 'messages'): ?>
            <div class="search-container">
                <input type="text" id="message-search" placeholder="Search messages..." class="search-input">
                <i data-feather="search" class="search-icon"></i>
            </div>
        <?php else: ?>
            <div class="search-container">
                <input type="text" placeholder="Search..." class="search-input">
                <i data-feather="search" class="search-icon"></i>
            </div>
        <?php endif; ?>
    </div>
</div>


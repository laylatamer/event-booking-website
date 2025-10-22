// Admin Panel Scripts

// Data storage (in a real app, this would be handled by a backend)
let users = [
    { id: 1, name: 'John Doe', email: 'john.doe@example.com', role: 'admin', joined: 'May 12, 2022', status: 'active', avatar: 'default-avatar.png' },
    { id: 2, name: 'Sarah Smith', email: 'sarah.smith@example.com', role: 'user', joined: 'Jun 5, 2022', status: 'active', avatar: 'default-avatar.png' },
    { id: 3, name: 'Mike Johnson', email: 'mike.johnson@example.com', role: 'organizer', joined: 'Apr 28, 2022', status: 'pending', avatar: 'default-avatar.png' },
    { id: 4, name: 'Emily Wilson', email: 'emily.wilson@example.com', role: 'user', joined: 'Mar 15, 2022', status: 'inactive', avatar: 'default-avatar.png' }
];

let bookings = [
    { id: 1, bookingId: '#EVT-4892', event: 'Summer Music Festival', user: 'john.doe@example.com', date: 'Jun 15, 2023', tickets: 2, amount: 120.00, status: 'completed' },
    { id: 2, bookingId: '#EVT-3567', event: 'Tech Conference 2023', user: 'sarah.smith@example.com', date: 'Jun 12, 2023', tickets: 1, amount: 75.00, status: 'completed' },
    { id: 3, bookingId: '#EVT-2781', event: 'Art Exhibition', user: 'mike.johnson@example.com', date: 'Jun 10, 2023', tickets: 4, amount: 60.00, status: 'pending' },
    { id: 4, bookingId: '#EVT-1895', event: 'Food Festival', user: 'emily.wilson@example.com', date: 'Jun 8, 2023', tickets: 3, amount: 45.00, status: 'cancelled' }
];

let events = [
    { id: 1, name: 'Summer Music Festival', category: 'music', date: 'Jun 15, 2023', location: 'Central Park', status: 'active', image: null },
    { id: 2, name: 'Tech Conference 2023', category: 'technology', date: 'Jun 12, 2023', location: 'Convention Center', status: 'active', image: null },
    { id: 3, name: 'Art Exhibition', category: 'art', date: 'Jun 10, 2023', location: 'Museum of Modern Art', status: 'active', image: null }
];

let categories = [
    { id: 1, name: 'Music', description: 'Concerts, festivals, and music events', events: 24, status: 'active', icon: 'music', color: 'orange' },
    { id: 2, name: 'Technology', description: 'Tech conferences and workshops', events: 18, status: 'active', icon: 'cpu', color: 'black' },
    { id: 3, name: 'Art', description: 'Art exhibitions and galleries', events: 15, status: 'active', icon: 'palette', color: 'white' },
    { id: 4, name: 'Food & Drink', description: 'Food festivals and tasting events', events: 12, status: 'active', icon: 'coffee', color: 'orange' }
];

let locations = [
    { id: 1, name: 'Central Park', address: 'New York, NY', capacity: 5000, image: null },
    { id: 2, name: 'Convention Center', address: 'Chicago, IL', capacity: 10000, image: null },
    { id: 3, name: 'Museum of Modern Art', address: 'San Francisco, CA', capacity: 1200, image: null }
];

let tickets = [
    { id: 1, event: 'Summer Music Festival', type: 'General Admission', price: 60.00, available: 124, sold: 376 },
    { id: 2, event: 'Tech Conference 2023', type: 'Standard Pass', price: 75.00, available: 56, sold: 244 },
    { id: 3, event: 'Art Exhibition', type: 'Adult Ticket', price: 15.00, available: 42, sold: 158 },
    { id: 4, event: 'Food Festival', type: 'Tasting Pass', price: 15.00, available: 28, sold: 172 }
];

// Global variables
let currentUser = {
    id: 1,
    name: 'Admin',
    email: 'admin@egzly.com',
    role: 'Administrator',
    phone: '+1 (555) 123-4567',
    avatar: 'default-avatar.png'
};

let currentPage = {
    users: 1,
    bookings: 1,
    events: 1,
    categories: 1,
    locations: 1,
    tickets: 1
};

const itemsPerPage = 4;

// Initialize the application
document.addEventListener('DOMContentLoaded', function() {
    // Initialize Feather icons
    feather.replace();
    
    // Close all modals on page load
    closeAllModals();
    
    // Set up event listeners
    initializeEventListeners();
    
    // Set dashboard as active
    showSection('dashboard');
    
    // Load only dashboard data initially
    loadDashboardData();
});

// Function to close all modals
function closeAllModals() {
    const modals = document.querySelectorAll('.modal');
    modals.forEach(modal => {
        modal.classList.add('hidden');
    });
    document.body.style.overflow = 'auto';
}

// Load dashboard data (stats and recent bookings)
function loadDashboardData() {
    // This would typically load from an API
    // For now, we'll just ensure the dashboard data is visible
    const dashboardSection = document.getElementById('dashboard-section');
    if (dashboardSection.classList.contains('active')) {
        // Stats are already in HTML, no need to load
        // Recent bookings table is already populated in HTML
    }
}

// Initialize all event listeners
function initializeEventListeners() {
    // Sidebar navigation
    const navButtons = document.querySelectorAll('.nav-btn');
    navButtons.forEach(button => {
        button.addEventListener('click', function() {
            const section = this.getAttribute('data-section');
            showSection(section);
            
            // Update active state
            navButtons.forEach(btn => btn.classList.remove('active'));
            this.classList.add('active');
        });
    });
    
    // User profile sidebar click
    document.querySelector('.user-profile-sidebar').addEventListener('click', function() {
        openModal('user-profile');
        loadUserProfile();
    });
    
    // Modal close buttons
    const closeButtons = document.querySelectorAll('.close-modal');
    closeButtons.forEach(button => {
        button.addEventListener('click', function() {
            const modalId = this.getAttribute('data-modal');
            closeModal(modalId);
        });
    });
    
    // Close modal when clicking outside
    const modals = document.querySelectorAll('.modal');
    modals.forEach(modal => {
        modal.addEventListener('click', function(e) {
            if (e.target === this) {
                const modalId = this.id.replace('-modal', '');
                closeModal(modalId);
            }
        });
    });
    
    // Add buttons
    document.getElementById('add-user-btn').addEventListener('click', () => openModal('add-user'));
    document.getElementById('add-event-btn').addEventListener('click', () => openModal('add-event'));
    document.getElementById('add-category-btn').addEventListener('click', () => openModal('add-category'));
    document.getElementById('add-location-btn').addEventListener('click', () => openModal('add-location'));
    document.getElementById('add-ticket-btn').addEventListener('click', () => openModal('add-ticket'));
    
    // Form submissions
    document.getElementById('add-user-form').addEventListener('submit', handleAddUser);
    document.getElementById('edit-user-form').addEventListener('submit', handleEditUser);
    document.getElementById('add-event-form').addEventListener('submit', handleAddEvent);
    document.getElementById('edit-event-form').addEventListener('submit', handleEditEvent);
    document.getElementById('add-category-form').addEventListener('submit', handleAddCategory);
    document.getElementById('edit-category-form').addEventListener('submit', handleEditCategory);
    document.getElementById('add-location-form').addEventListener('submit', handleAddLocation);
    document.getElementById('edit-location-form').addEventListener('submit', handleEditLocation);
    document.getElementById('add-ticket-form').addEventListener('submit', handleAddTicket);
    document.getElementById('edit-ticket-form').addEventListener('submit', handleEditTicket);
    document.getElementById('user-profile-form').addEventListener('submit', handleUpdateProfile);
    
    // Search functionality
    document.getElementById('user-search').addEventListener('input', filterUsers);
    document.getElementById('booking-search').addEventListener('input', filterBookings);
    document.getElementById('event-search').addEventListener('input', filterEvents);
    
    // Filter functionality
    document.getElementById('user-role-filter').addEventListener('change', filterUsers);
    document.getElementById('event-category-filter').addEventListener('change', filterEvents);
    
    // Pagination
    document.getElementById('users-prev').addEventListener('click', () => changePage('users', -1));
    document.getElementById('users-next').addEventListener('click', () => changePage('users', 1));
    document.getElementById('bookings-prev').addEventListener('click', () => changePage('bookings', -1));
    document.getElementById('bookings-next').addEventListener('click', () => changePage('bookings', 1));
    document.getElementById('events-prev').addEventListener('click', () => changePage('events', -1));
    document.getElementById('events-next').addEventListener('click', () => changePage('events', 1));
    document.getElementById('categories-prev').addEventListener('click', () => changePage('categories', -1));
    document.getElementById('categories-next').addEventListener('click', () => changePage('categories', 1));
    document.getElementById('locations-prev').addEventListener('click', () => changePage('locations', -1));
    document.getElementById('locations-next').addEventListener('click', () => changePage('locations', 1));
    document.getElementById('tickets-prev').addEventListener('click', () => changePage('tickets', -1));
    document.getElementById('tickets-next').addEventListener('click', () => changePage('tickets', 1));
    
    // Logout button
    document.getElementById('logout-btn').addEventListener('click', handleLogout);
    
    // Change avatar button
    document.getElementById('change-avatar-btn').addEventListener('click', () => {
        document.getElementById('avatar-upload').click();
    });
    
    // Avatar upload
    document.getElementById('avatar-upload').addEventListener('change', handleAvatarUpload);
    
    // Print booking button
    document.getElementById('print-booking-btn').addEventListener('click', printBooking);
}

// Show a specific section and load its data
function showSection(sectionId) {
    // Hide all sections
    const sections = document.querySelectorAll('.section-content');
    sections.forEach(section => {
        section.classList.remove('active');
    });
    
    // Show the selected section
    const targetSection = document.getElementById(`${sectionId}-section`);
    targetSection.classList.add('active');
    
    // Load data for the section if needed
    switch(sectionId) {
        case 'users':
            if (document.getElementById('users-table-body').children.length === 0) {
                loadUsers();
            }
            break;
        case 'bookings':
            if (document.getElementById('bookings-table-body').children.length === 0) {
                loadBookings();
            }
            break;
        case 'events':
            if (document.getElementById('events-grid').children.length === 0) {
                loadEvents();
            }
            break;
        case 'categories':
            if (document.getElementById('categories-table-body').children.length === 0) {
                loadCategories();
            }
            break;
        case 'locations':
            if (document.getElementById('locations-grid').children.length === 0) {
                loadLocations();
            }
            break;
        case 'tickets':
            if (document.getElementById('tickets-table-body').children.length === 0) {
                loadTickets();
            }
            break;
    }
    
    // Update URL without reloading
    history.pushState(null, '', `#${sectionId}`);
}

// Open a modal
function openModal(modalId) {
    document.getElementById(`${modalId}-modal`).classList.remove('hidden');
    document.body.style.overflow = 'hidden';
}

// Close a modal
function closeModal(modalId) {
    document.getElementById(`${modalId}-modal`).classList.add('hidden');
    document.body.style.overflow = 'auto';
    
    // Reset form if it's an add form
    if (modalId.startsWith('add-')) {
        const form = document.getElementById(`${modalId}-form`);
        if (form) {
            form.reset();
        }
    }
}

// Load users data
function loadUsers() {
    const usersTableBody = document.getElementById('users-table-body');
    usersTableBody.innerHTML = '';
    
    if (users.length === 0) {
        usersTableBody.innerHTML = `
            <tr>
                <td colspan="6" class="empty-state">
                    <i data-feather="users"></i>
                    <p>No users found</p>
                </td>
            </tr>
        `;
        feather.replace();
        return;
    }

    const startIndex = (currentPage.users - 1) * itemsPerPage;
    const endIndex = startIndex + itemsPerPage;
    const usersToShow = users.slice(startIndex, endIndex);
    
    usersToShow.forEach(user => {
        const row = document.createElement('tr');
        row.innerHTML = `
            <td>
                <div class="checkbox-header">
                    <input type="checkbox" class="checkbox-input user-checkbox" data-id="${user.id}">
                    <div class="user-info">
                        <div class="avatar small">
                            <img src="${user.avatar}" alt="${user.name}">
                        </div>
                        <span>${user.name}</span>
                    </div>
                </div>
            </td>
            <td>${user.email}</td>
            <td>${user.role}</td>
            <td>${user.joined}</td>
            <td><span class="status-badge ${user.status}">${user.status.charAt(0).toUpperCase() + user.status.slice(1)}</span></td>
            <td>
                <div class="action-buttons">
                    <button class="action-btn edit-user" data-id="${user.id}">
                        <i data-feather="edit-2"></i>
                    </button>
                    <button class="action-btn delete delete-user" data-id="${user.id}">
                        <i data-feather="trash-2"></i>
                    </button>
                </div>
            </td>
        `;
        usersTableBody.appendChild(row);
    });
    
    // Update pagination info
    document.getElementById('users-start').textContent = startIndex + 1;
    document.getElementById('users-end').textContent = Math.min(endIndex, users.length);
    document.getElementById('users-total').textContent = users.length;
    
    // Update pagination buttons
    updatePaginationButtons('users', users.length);
    
    // Add event listeners to action buttons
    feather.replace();
    document.querySelectorAll('.edit-user').forEach(button => {
        button.addEventListener('click', function() {
            const userId = parseInt(this.getAttribute('data-id'));
            editUser(userId);
        });
    });
    
    document.querySelectorAll('.delete-user').forEach(button => {
        button.addEventListener('click', function() {
            const userId = parseInt(this.getAttribute('data-id'));
            deleteUser(userId);
        });
    });
}

// Filter users based on search and role filter
function filterUsers() {
    const searchTerm = document.getElementById('user-search').value.toLowerCase();
    const roleFilter = document.getElementById('user-role-filter').value;
    
    const filteredUsers = users.filter(user => {
        const matchesSearch = user.name.toLowerCase().includes(searchTerm) || 
                             user.email.toLowerCase().includes(searchTerm);
        const matchesRole = roleFilter === 'Filter by role' || user.role === roleFilter.toLowerCase();
        
        return matchesSearch && matchesRole;
    });
    
    // Update the table with filtered users
    const usersTableBody = document.getElementById('users-table-body');
    usersTableBody.innerHTML = '';
    
    const startIndex = (currentPage.users - 1) * itemsPerPage;
    const endIndex = startIndex + itemsPerPage;
    const usersToShow = filteredUsers.slice(startIndex, endIndex);
    
    usersToShow.forEach(user => {
        const row = document.createElement('tr');
        row.innerHTML = `
            <td>
                <div class="checkbox-header">
                    <input type="checkbox" class="checkbox-input user-checkbox" data-id="${user.id}">
                    <div class="user-info">
                        <div class="avatar small">
                            <img src="${user.avatar}" alt="${user.name}">
                        </div>
                        <span>${user.name}</span>
                    </div>
                </div>
            </td>
            <td>${user.email}</td>
            <td>${user.role}</td>
            <td>${user.joined}</td>
            <td><span class="status-badge ${user.status}">${user.status.charAt(0).toUpperCase() + user.status.slice(1)}</span></td>
            <td>
                <div class="action-buttons">
                    <button class="action-btn edit-user" data-id="${user.id}">
                        <i data-feather="edit-2"></i>
                    </button>
                    <button class="action-btn delete delete-user" data-id="${user.id}">
                        <i data-feather="trash-2"></i>
                    </button>
                </div>
            </td>
        `;
        usersTableBody.appendChild(row);
    });
    
    // Update pagination info
    document.getElementById('users-start').textContent = filteredUsers.length > 0 ? startIndex + 1 : 0;
    document.getElementById('users-end').textContent = Math.min(endIndex, filteredUsers.length);
    document.getElementById('users-total').textContent = filteredUsers.length;
    
    // Update pagination buttons
    updatePaginationButtons('users', filteredUsers.length);
    
    // Add event listeners to action buttons
    feather.replace();
    document.querySelectorAll('.edit-user').forEach(button => {
        button.addEventListener('click', function() {
            const userId = parseInt(this.getAttribute('data-id'));
            editUser(userId);
        });
    });
    
    document.querySelectorAll('.delete-user').forEach(button => {
        button.addEventListener('click', function() {
            const userId = parseInt(this.getAttribute('data-id'));
            deleteUser(userId);
        });
    });
}

// Handle add user form submission
function handleAddUser(e) {
    e.preventDefault();
    
    const name = document.getElementById('user-name').value;
    const email = document.getElementById('user-email').value;
    const role = document.getElementById('user-role').value;
    const password = document.getElementById('user-password').value;
    
    const newUser = {
        id: users.length > 0 ? Math.max(...users.map(u => u.id)) + 1 : 1,
        name,
        email,
        role,
        joined: new Date().toLocaleDateString('en-US', { year: 'numeric', month: 'short', day: 'numeric' }),
        status: 'active',
        avatar: 'default-avatar.png'
    };
    
    users.push(newUser);
    loadUsers();
    closeModal('add-user');
    
    // Show success message (in a real app)
    alert('User added successfully!');
}

// Edit user
function editUser(userId) {
    const user = users.find(u => u.id === userId);
    if (!user) return;
    
    document.getElementById('edit-user-id').value = user.id;
    document.getElementById('edit-user-name').value = user.name;
    document.getElementById('edit-user-email').value = user.email;
    document.getElementById('edit-user-role').value = user.role;
    document.getElementById('edit-user-status').value = user.status;
    
    openModal('edit-user');
}

// Handle edit user form submission
function handleEditUser(e) {
    e.preventDefault();
    
    const userId = parseInt(document.getElementById('edit-user-id').value);
    const name = document.getElementById('edit-user-name').value;
    const email = document.getElementById('edit-user-email').value;
    const role = document.getElementById('edit-user-role').value;
    const status = document.getElementById('edit-user-status').value;
    
    const userIndex = users.findIndex(u => u.id === userId);
    if (userIndex !== -1) {
        users[userIndex].name = name;
        users[userIndex].email = email;
        users[userIndex].role = role;
        users[userIndex].status = status;
        
        loadUsers();
        closeModal('edit-user');
        
        // Show success message (in a real app)
        alert('User updated successfully!');
    }
}

// Delete user with confirmation
function deleteUser(userId) {
    showConfirmation('Are you sure you want to delete this user?', () => {
        const userIndex = users.findIndex(u => u.id === userId);
        if (userIndex !== -1) {
            users.splice(userIndex, 1);
            loadUsers();
            
            // Show success message (in a real app)
            alert('User deleted successfully!');
        }
    });
}

// Load bookings data
function loadBookings() {
    const bookingsTableBody = document.getElementById('bookings-table-body');
    bookingsTableBody.innerHTML = '';

    if (bookings.length === 0) {
        bookingsTableBody.innerHTML = `
            <tr>
                <td colspan="8" class="empty-state">
                    <i data-feather="calendar"></i>
                    <p>No bookings found</p>
                </td>
            </tr>
        `;
        feather.replace();
        return;
    }
    
    const startIndex = (currentPage.bookings - 1) * itemsPerPage;
    const endIndex = startIndex + itemsPerPage;
    const bookingsToShow = bookings.slice(startIndex, endIndex);
    
    bookingsToShow.forEach(booking => {
        const row = document.createElement('tr');
        row.innerHTML = `
            <td>${booking.bookingId}</td>
            <td>${booking.event}</td>
            <td>${booking.user}</td>
            <td>${booking.date}</td>
            <td>${booking.tickets}</td>
            <td>$${booking.amount.toFixed(2)}</td>
            <td><span class="status-badge ${booking.status}">${booking.status.charAt(0).toUpperCase() + booking.status.slice(1)}</span></td>
            <td>
                <div class="action-buttons">
                    <button class="action-btn view-booking" data-id="${booking.id}">
                        <i data-feather="eye"></i>
                    </button>
                    <button class="action-btn print-booking" data-id="${booking.id}">
                        <i data-feather="printer"></i>
                    </button>
                </div>
            </td>
        `;
        bookingsTableBody.appendChild(row);
    });
    
    // Update pagination info
    document.getElementById('bookings-start').textContent = startIndex + 1;
    document.getElementById('bookings-end').textContent = Math.min(endIndex, bookings.length);
    document.getElementById('bookings-total').textContent = bookings.length;
    
    // Update pagination buttons
    updatePaginationButtons('bookings', bookings.length);
    
    // Add event listeners to action buttons
    feather.replace();
    document.querySelectorAll('.view-booking').forEach(button => {
        button.addEventListener('click', function() {
            const bookingId = parseInt(this.getAttribute('data-id'));
            viewBooking(bookingId);
        });
    });
    
    document.querySelectorAll('.print-booking').forEach(button => {
        button.addEventListener('click', function() {
            const bookingId = parseInt(this.getAttribute('data-id'));
            printBooking(bookingId);
        });
    });
}

// Filter bookings based on search
function filterBookings() {
    const searchTerm = document.getElementById('booking-search').value.toLowerCase();
    
    const filteredBookings = bookings.filter(booking => {
        return booking.bookingId.toLowerCase().includes(searchTerm) || 
               booking.event.toLowerCase().includes(searchTerm) || 
               booking.user.toLowerCase().includes(searchTerm);
    });
    
    // Update the table with filtered bookings
    const bookingsTableBody = document.getElementById('bookings-table-body');
    bookingsTableBody.innerHTML = '';
    
    const startIndex = (currentPage.bookings - 1) * itemsPerPage;
    const endIndex = startIndex + itemsPerPage;
    const bookingsToShow = filteredBookings.slice(startIndex, endIndex);
    
    bookingsToShow.forEach(booking => {
        const row = document.createElement('tr');
        row.innerHTML = `
            <td>${booking.bookingId}</td>
            <td>${booking.event}</td>
            <td>${booking.user}</td>
            <td>${booking.date}</td>
            <td>${booking.tickets}</td>
            <td>$${booking.amount.toFixed(2)}</td>
            <td><span class="status-badge ${booking.status}">${booking.status.charAt(0).toUpperCase() + booking.status.slice(1)}</span></td>
            <td>
                <div class="action-buttons">
                    <button class="action-btn view-booking" data-id="${booking.id}">
                        <i data-feather="eye"></i>
                    </button>
                    <button class="action-btn print-booking" data-id="${booking.id}">
                        <i data-feather="printer"></i>
                    </button>
                </div>
            </td>
        `;
        bookingsTableBody.appendChild(row);
    });
    
    // Update pagination info
    document.getElementById('bookings-start').textContent = filteredBookings.length > 0 ? startIndex + 1 : 0;
    document.getElementById('bookings-end').textContent = Math.min(endIndex, filteredBookings.length);
    document.getElementById('bookings-total').textContent = filteredBookings.length;
    
    // Update pagination buttons
    updatePaginationButtons('bookings', filteredBookings.length);
    
    // Add event listeners to action buttons
    feather.replace();
    document.querySelectorAll('.view-booking').forEach(button => {
        button.addEventListener('click', function() {
            const bookingId = parseInt(this.getAttribute('data-id'));
            viewBooking(bookingId);
        });
    });
    
    document.querySelectorAll('.print-booking').forEach(button => {
        button.addEventListener('click', function() {
            const bookingId = parseInt(this.getAttribute('data-id'));
            printBooking(bookingId);
        });
    });
}

// View booking details
function viewBooking(bookingId) {
    const booking = bookings.find(b => b.id === bookingId);
    if (!booking) return;
    
    const bookingDetails = document.getElementById('booking-details');
    bookingDetails.innerHTML = `
        <div class="booking-detail">
            <p><strong>Booking ID:</strong> ${booking.bookingId}</p>
            <p><strong>Event:</strong> ${booking.event}</p>
            <p><strong>User:</strong> ${booking.user}</p>
            <p><strong>Date:</strong> ${booking.date}</p>
            <p><strong>Tickets:</strong> ${booking.tickets}</p>
            <p><strong>Amount:</strong> $${booking.amount.toFixed(2)}</p>
            <p><strong>Status:</strong> <span class="status-badge ${booking.status}">${booking.status.charAt(0).toUpperCase() + booking.status.slice(1)}</span></p>
        </div>
    `;
    
    // Store the current booking ID for printing
    document.getElementById('print-booking-btn').setAttribute('data-booking-id', bookingId);
    
    openModal('view-booking');
}

// Print booking
function printBooking(bookingId = null) {
    if (!bookingId) {
        bookingId = document.getElementById('print-booking-btn').getAttribute('data-booking-id');
    }
    
    const booking = bookings.find(b => b.id === parseInt(bookingId));
    if (!booking) return;
    
    // In a real app, this would generate a proper printable ticket
    const printWindow = window.open('', '_blank');
    printWindow.document.write(`
        <html>
            <head>
                <title>Booking Ticket - ${booking.bookingId}</title>
                <style>
                    body { font-family: Arial, sans-serif; padding: 20px; }
                    .ticket { border: 2px solid #000; padding: 20px; max-width: 400px; margin: 0 auto; }
                    .header { text-align: center; margin-bottom: 20px; }
                    .details { margin-bottom: 20px; }
                    .detail { margin-bottom: 10px; }
                    .footer { text-align: center; margin-top: 20px; font-size: 12px; color: #666; }
                </style>
            </head>
            <body>
                <div class="ticket">
                    <div class="header">
                        <h1>EحGZLY</h1>
                        <h2>Event Ticket</h2>
                    </div>
                    <div class="details">
                        <div class="detail"><strong>Booking ID:</strong> ${booking.bookingId}</div>
                        <div class="detail"><strong>Event:</strong> ${booking.event}</div>
                        <div class="detail"><strong>User:</strong> ${booking.user}</div>
                        <div class="detail"><strong>Date:</strong> ${booking.date}</div>
                        <div class="detail"><strong>Tickets:</strong> ${booking.tickets}</div>
                        <div class="detail"><strong>Amount:</strong> $${booking.amount.toFixed(2)}</div>
                        <div class="detail"><strong>Status:</strong> ${booking.status.charAt(0).toUpperCase() + booking.status.slice(1)}</div>
                    </div>
                    <div class="footer">
                        <p>Thank you for your booking!</p>
                    </div>
                </div>
            </body>
        </html>
    `);
    printWindow.document.close();
    printWindow.print();
}

// Load events data
function loadEvents() {
    const eventsGrid = document.getElementById('events-grid');
    eventsGrid.innerHTML = '';

    if (events.length === 0) {
        eventsGrid.innerHTML = `
            <div class="empty-state" style="grid-column: 1 / -1;">
                <i data-feather="calendar"></i>
                <p>No events found</p>
            </div>
        `;
        feather.replace();
        return;
    }

    
    const startIndex = (currentPage.events - 1) * itemsPerPage;
    const endIndex = startIndex + itemsPerPage;
    const eventsToShow = events.slice(startIndex, endIndex);
    
    eventsToShow.forEach(event => {
        const eventCard = document.createElement('div');
        eventCard.className = 'event-card';
        eventCard.innerHTML = `
            <div class="event-image">
                ${event.image ? 
                    `<img src="${event.image}" alt="${event.name}" style="width: 100%; height: 100%; object-fit: cover;">` : 
                    `<i data-feather="image"></i>`
                }
            </div>
            <div class="event-content">
                <h3 class="event-title">${event.name}</h3>
                <p class="event-meta">${event.date} • ${event.location}</p>
                <div class="event-actions">
                    <span class="status-badge ${event.status}">${event.status.charAt(0).toUpperCase() + event.status.slice(1)}</span>
                    <div class="action-buttons">
                        <button class="action-btn edit-event" data-id="${event.id}">
                            <i data-feather="edit-2"></i>
                        </button>
                        <button class="action-btn delete delete-event" data-id="${event.id}">
                            <i data-feather="trash-2"></i>
                        </button>
                    </div>
                </div>
            </div>
        `;
        eventsGrid.appendChild(eventCard);
    });
    
    // Update pagination info
    document.getElementById('events-start').textContent = startIndex + 1;
    document.getElementById('events-end').textContent = Math.min(endIndex, events.length);
    document.getElementById('events-total').textContent = events.length;
    
    // Update pagination buttons
    updatePaginationButtons('events', events.length);
    
    // Add event listeners to action buttons
    feather.replace();
    document.querySelectorAll('.edit-event').forEach(button => {
        button.addEventListener('click', function() {
            const eventId = parseInt(this.getAttribute('data-id'));
            editEvent(eventId);
        });
    });
    
    document.querySelectorAll('.delete-event').forEach(button => {
        button.addEventListener('click', function() {
            const eventId = parseInt(this.getAttribute('data-id'));
            deleteEvent(eventId);
        });
    });
}

// Filter events based on search and category filter
function filterEvents() {
    const searchTerm = document.getElementById('event-search').value.toLowerCase();
    const categoryFilter = document.getElementById('event-category-filter').value;
    
    const filteredEvents = events.filter(event => {
        const matchesSearch = event.name.toLowerCase().includes(searchTerm) || 
                             event.location.toLowerCase().includes(searchTerm);
        const matchesCategory = categoryFilter === 'All Categories' || event.category === categoryFilter.toLowerCase();
        
        return matchesSearch && matchesCategory;
    });
    
    // Update the grid with filtered events
    const eventsGrid = document.getElementById('events-grid');
    eventsGrid.innerHTML = '';
    
    const startIndex = (currentPage.events - 1) * itemsPerPage;
    const endIndex = startIndex + itemsPerPage;
    const eventsToShow = filteredEvents.slice(startIndex, endIndex);
    
    eventsToShow.forEach(event => {
        const eventCard = document.createElement('div');
        eventCard.className = 'event-card';
        eventCard.innerHTML = `
            <div class="event-image">
                ${event.image ? 
                    `<img src="${event.image}" alt="${event.name}" style="width: 100%; height: 100%; object-fit: cover;">` : 
                    `<i data-feather="image"></i>`
                }
            </div>
            <div class="event-content">
                <h3 class="event-title">${event.name}</h3>
                <p class="event-meta">${event.date} • ${event.location}</p>
                <div class="event-actions">
                    <span class="status-badge ${event.status}">${event.status.charAt(0).toUpperCase() + event.status.slice(1)}</span>
                    <div class="action-buttons">
                        <button class="action-btn edit-event" data-id="${event.id}">
                            <i data-feather="edit-2"></i>
                        </button>
                        <button class="action-btn delete delete-event" data-id="${event.id}">
                            <i data-feather="trash-2"></i>
                        </button>
                    </div>
                </div>
            </div>
        `;
        eventsGrid.appendChild(eventCard);
    });
    
    // Update pagination info
    document.getElementById('events-start').textContent = filteredEvents.length > 0 ? startIndex + 1 : 0;
    document.getElementById('events-end').textContent = Math.min(endIndex, filteredEvents.length);
    document.getElementById('events-total').textContent = filteredEvents.length;
    
    // Update pagination buttons
    updatePaginationButtons('events', filteredEvents.length);
    
    // Add event listeners to action buttons
    feather.replace();
    document.querySelectorAll('.edit-event').forEach(button => {
        button.addEventListener('click', function() {
            const eventId = parseInt(this.getAttribute('data-id'));
            editEvent(eventId);
        });
    });
    
    document.querySelectorAll('.delete-event').forEach(button => {
        button.addEventListener('click', function() {
            const eventId = parseInt(this.getAttribute('data-id'));
            deleteEvent(eventId);
        });
    });
}

// Handle add event form submission
function handleAddEvent(e) {
    e.preventDefault();
    
    const name = document.getElementById('event-name').value;
    const category = document.getElementById('event-category').value;
    const startDate = document.getElementById('event-start-date').value;
    const endDate = document.getElementById('event-end-date').value;
    const location = document.getElementById('event-location').value;
    const organizer = document.getElementById('event-organizer').value;
    const description = document.getElementById('event-description').value;
    const imageFile = document.getElementById('event-image').files[0];
    
    // Format date
    const formattedDate = new Date(startDate).toLocaleDateString('en-US', { 
        year: 'numeric', 
        month: 'short', 
        day: 'numeric' 
    });
    
    const newEvent = {
        id: events.length > 0 ? Math.max(...events.map(e => e.id)) + 1 : 1,
        name,
        category,
        date: formattedDate,
        location: document.querySelector(`#event-location option[value="${location}"]`).textContent,
        organizer,
        description,
        status: 'active',
        image: imageFile ? URL.createObjectURL(imageFile) : null
    };
    
    events.push(newEvent);
    loadEvents();
    closeModal('add-event');
    
    // Show success message (in a real app)
    alert('Event added successfully!');
}

// Edit event
function editEvent(eventId) {
    const event = events.find(e => e.id === eventId);
    if (!event) return;
    
    document.getElementById('edit-event-id').value = event.id;
    document.getElementById('edit-event-name').value = event.name;
    document.getElementById('edit-event-category').value = event.category;
    
    // Set dates (you would need to parse the formatted date back to input format)
    // This is a simplified version
    document.getElementById('edit-event-start-date').value = '2023-06-15';
    document.getElementById('edit-event-end-date').value = '2023-06-15';
    
    document.getElementById('edit-event-location').value = event.location.toLowerCase().replace(' ', '-');
    document.getElementById('edit-event-organizer').value = event.organizer;
    document.getElementById('edit-event-description').value = event.description || '';
    
    openModal('edit-event');
}

// Handle edit event form submission
function handleEditEvent(e) {
    e.preventDefault();
    
    const eventId = parseInt(document.getElementById('edit-event-id').value);
    const name = document.getElementById('edit-event-name').value;
    const category = document.getElementById('edit-event-category').value;
    const startDate = document.getElementById('edit-event-start-date').value;
    const endDate = document.getElementById('edit-event-end-date').value;
    const location = document.getElementById('edit-event-location').value;
    const organizer = document.getElementById('edit-event-organizer').value;
    const description = document.getElementById('edit-event-description').value;
    const imageFile = document.getElementById('edit-event-image').files[0];
    
    // Format date
    const formattedDate = new Date(startDate).toLocaleDateString('en-US', { 
        year: 'numeric', 
        month: 'short', 
        day: 'numeric' 
    });
    
    const eventIndex = events.findIndex(e => e.id === eventId);
    if (eventIndex !== -1) {
        events[eventIndex].name = name;
        events[eventIndex].category = category;
        events[eventIndex].date = formattedDate;
        events[eventIndex].location = document.querySelector(`#edit-event-location option[value="${location}"]`).textContent;
        events[eventIndex].organizer = organizer;
        events[eventIndex].description = description;
        
        if (imageFile) {
            events[eventIndex].image = URL.createObjectURL(imageFile);
        }
        
        loadEvents();
        closeModal('edit-event');
        
        // Show success message (in a real app)
        alert('Event updated successfully!');
    }
}

// Delete event with confirmation
function deleteEvent(eventId) {
    showConfirmation('Are you sure you want to delete this event?', () => {
        const eventIndex = events.findIndex(e => e.id === eventId);
        if (eventIndex !== -1) {
            events.splice(eventIndex, 1);
            loadEvents();
            
            // Show success message (in a real app)
            alert('Event deleted successfully!');
        }
    });
}

// Load categories data
function loadCategories() {
    const categoriesTableBody = document.getElementById('categories-table-body');
    categoriesTableBody.innerHTML = '';

      if (categories.length === 0) {
        categoriesTableBody.innerHTML = `
            <tr>
                <td colspan="5" class="empty-state">
                    <i data-feather="tag"></i>
                    <p>No categories found</p>
                </td>
            </tr>
        `;
        feather.replace();
        return;
    }
    
    const startIndex = (currentPage.categories - 1) * itemsPerPage;
    const endIndex = startIndex + itemsPerPage;
    const categoriesToShow = categories.slice(startIndex, endIndex);
    
    categoriesToShow.forEach(category => {
        const row = document.createElement('tr');
        row.innerHTML = `
            <td>
                <div class="category-info">
                    <div class="category-icon ${category.color}">
                        <i data-feather="${category.icon}"></i>
                    </div>
                    <span>${category.name}</span>
                </div>
            </td>
            <td>${category.description}</td>
            <td>${category.events}</td>
            <td><span class="status-badge ${category.status}">${category.status.charAt(0).toUpperCase() + category.status.slice(1)}</span></td>
            <td>
                <div class="action-buttons">
                    <button class="action-btn edit-category" data-id="${category.id}">
                        <i data-feather="edit-2"></i>
                    </button>
                    <button class="action-btn delete delete-category" data-id="${category.id}">
                        <i data-feather="trash-2"></i>
                    </button>
                </div>
            </td>
        `;
        categoriesTableBody.appendChild(row);
    });
    
    // Update pagination info
    document.getElementById('categories-start').textContent = startIndex + 1;
    document.getElementById('categories-end').textContent = Math.min(endIndex, categories.length);
    document.getElementById('categories-total').textContent = categories.length;
    
    // Update pagination buttons
    updatePaginationButtons('categories', categories.length);
    
    // Add event listeners to action buttons
    feather.replace();
    document.querySelectorAll('.edit-category').forEach(button => {
        button.addEventListener('click', function() {
            const categoryId = parseInt(this.getAttribute('data-id'));
            editCategory(categoryId);
        });
    });
    
    document.querySelectorAll('.delete-category').forEach(button => {
        button.addEventListener('click', function() {
            const categoryId = parseInt(this.getAttribute('data-id'));
            deleteCategory(categoryId);
        });
    });
}

// Handle add category form submission
function handleAddCategory(e) {
    e.preventDefault();
    
    const name = document.getElementById('category-name').value;
    const description = document.getElementById('category-description').value;
    const icon = document.getElementById('category-icon').value;
    const color = document.getElementById('category-color').value;
    
    const newCategory = {
        id: categories.length > 0 ? Math.max(...categories.map(c => c.id)) + 1 : 1,
        name,
        description,
        events: 0,
        status: 'active',
        icon,
        color
    };
    
    categories.push(newCategory);
    loadCategories();
    closeModal('add-category');
    
    // Show success message (in a real app)
    alert('Category added successfully!');
}

// Edit category
function editCategory(categoryId) {
    const category = categories.find(c => c.id === categoryId);
    if (!category) return;
    
    document.getElementById('edit-category-id').value = category.id;
    document.getElementById('edit-category-name').value = category.name;
    document.getElementById('edit-category-description').value = category.description;
    document.getElementById('edit-category-icon').value = category.icon;
    document.getElementById('edit-category-color').value = category.color;
    
    openModal('edit-category');
}

// Handle edit category form submission
function handleEditCategory(e) {
    e.preventDefault();
    
    const categoryId = parseInt(document.getElementById('edit-category-id').value);
    const name = document.getElementById('edit-category-name').value;
    const description = document.getElementById('edit-category-description').value;
    const icon = document.getElementById('edit-category-icon').value;
    const color = document.getElementById('edit-category-color').value;
    
    const categoryIndex = categories.findIndex(c => c.id === categoryId);
    if (categoryIndex !== -1) {
        categories[categoryIndex].name = name;
        categories[categoryIndex].description = description;
        categories[categoryIndex].icon = icon;
        categories[categoryIndex].color = color;
        
        loadCategories();
        closeModal('edit-category');
        
        // Show success message (in a real app)
        alert('Category updated successfully!');
    }
}

// Delete category with confirmation
function deleteCategory(categoryId) {
    showConfirmation('Are you sure you want to delete this category?', () => {
        const categoryIndex = categories.findIndex(c => c.id === categoryId);
        if (categoryIndex !== -1) {
            categories.splice(categoryIndex, 1);
            loadCategories();
            
            // Show success message (in a real app)
            alert('Category deleted successfully!');
        }
    });
}

// Load locations data
function loadLocations() {
    const locationsGrid = document.getElementById('locations-grid');
    locationsGrid.innerHTML = '';

    if (locations.length === 0) {
        locationsGrid.innerHTML = `
            <div class="empty-state" style="grid-column: 1 / -1;">
                <i data-feather="map-pin"></i>
                <p>No locations found</p>
            </div>
        `;
        feather.replace();
        return;
    }
    
    const startIndex = (currentPage.locations - 1) * itemsPerPage;
    const endIndex = startIndex + itemsPerPage;
    const locationsToShow = locations.slice(startIndex, endIndex);
    
    locationsToShow.forEach(location => {
        const locationCard = document.createElement('div');
        locationCard.className = 'location-card';
        locationCard.innerHTML = `
            <div class="location-image">
                ${location.image ? 
                    `<img src="${location.image}" alt="${location.name}" style="width: 100%; height: 100%; object-fit: cover;">` : 
                    `<i data-feather="map-pin"></i>`
                }
            </div>
            <div class="location-content">
                <h3 class="location-title">${location.name}</h3>
                <p class="location-meta">${location.address}</p>
                <div class="location-info">
                    <span class="location-capacity">Capacity: ${location.capacity.toLocaleString()}</span>
                    <div class="action-buttons">
                        <button class="action-btn edit-location" data-id="${location.id}">
                            <i data-feather="edit-2"></i>
                        </button>
                        <button class="action-btn delete delete-location" data-id="${location.id}">
                            <i data-feather="trash-2"></i>
                        </button>
                    </div>
                </div>
            </div>
        `;
        locationsGrid.appendChild(locationCard);
    });
    
    // Update pagination info
    document.getElementById('locations-start').textContent = startIndex + 1;
    document.getElementById('locations-end').textContent = Math.min(endIndex, locations.length);
    document.getElementById('locations-total').textContent = locations.length;
    
    // Update pagination buttons
    updatePaginationButtons('locations', locations.length);
    
    // Add event listeners to action buttons
    feather.replace();
    document.querySelectorAll('.edit-location').forEach(button => {
        button.addEventListener('click', function() {
            const locationId = parseInt(this.getAttribute('data-id'));
            editLocation(locationId);
        });
    });
    
    document.querySelectorAll('.delete-location').forEach(button => {
        button.addEventListener('click', function() {
            const locationId = parseInt(this.getAttribute('data-id'));
            deleteLocation(locationId);
        });
    });
}

// Handle add location form submission
function handleAddLocation(e) {
    e.preventDefault();
    
    const name = document.getElementById('location-name').value;
    const address = document.getElementById('location-address').value;
    const city = document.getElementById('location-city').value;
    const state = document.getElementById('location-state').value;
    const capacity = parseInt(document.getElementById('location-capacity').value);
    const imageFile = document.getElementById('location-image').files[0];
    
    const newLocation = {
        id: locations.length > 0 ? Math.max(...locations.map(l => l.id)) + 1 : 1,
        name,
        address: `${address}, ${city}, ${state}`,
        capacity,
        image: imageFile ? URL.createObjectURL(imageFile) : null
    };
    
    locations.push(newLocation);
    loadLocations();
    closeModal('add-location');
    
    // Show success message (in a real app)
    alert('Location added successfully!');
}

// Edit location
function editLocation(locationId) {
    const location = locations.find(l => l.id === locationId);
    if (!location) return;
    
    // Parse address into components
    const addressParts = location.address.split(', ');
    
    document.getElementById('edit-location-id').value = location.id;
    document.getElementById('edit-location-name').value = location.name;
    document.getElementById('edit-location-address').value = addressParts[0];
    document.getElementById('edit-location-city').value = addressParts[1];
    document.getElementById('edit-location-state').value = addressParts[2];
    document.getElementById('edit-location-capacity').value = location.capacity;
    
    openModal('edit-location');
}

// Handle edit location form submission
function handleEditLocation(e) {
    e.preventDefault();
    
    const locationId = parseInt(document.getElementById('edit-location-id').value);
    const name = document.getElementById('edit-location-name').value;
    const address = document.getElementById('edit-location-address').value;
    const city = document.getElementById('edit-location-city').value;
    const state = document.getElementById('edit-location-state').value;
    const capacity = parseInt(document.getElementById('edit-location-capacity').value);
    const imageFile = document.getElementById('edit-location-image').files[0];
    
    const locationIndex = locations.findIndex(l => l.id === locationId);
    if (locationIndex !== -1) {
        locations[locationIndex].name = name;
        locations[locationIndex].address = `${address}, ${city}, ${state}`;
        locations[locationIndex].capacity = capacity;
        
        if (imageFile) {
            locations[locationIndex].image = URL.createObjectURL(imageFile);
        }
        
        loadLocations();
        closeModal('edit-location');
        
        // Show success message (in a real app)
        alert('Location updated successfully!');
    }
}

// Delete location with confirmation
function deleteLocation(locationId) {
    showConfirmation('Are you sure you want to delete this location?', () => {
        const locationIndex = locations.findIndex(l => l.id === locationId);
        if (locationIndex !== -1) {
            locations.splice(locationIndex, 1);
            loadLocations();
            
            // Show success message (in a real app)
            alert('Location deleted successfully!');
        }
    });
}

// Load tickets data
function loadTickets() {
    const ticketsTableBody = document.getElementById('tickets-table-body');
    ticketsTableBody.innerHTML = '';
    
    if (tickets.length === 0) {
        ticketsTableBody.innerHTML = `
            <tr>
                <td colspan="6" class="empty-state">
                    <i data-feather="ticket"></i>
                    <p>No tickets found</p>
                </td>
            </tr>
        `;
        feather.replace();
        return;
    }

    const startIndex = (currentPage.tickets - 1) * itemsPerPage;
    const endIndex = startIndex + itemsPerPage;
    const ticketsToShow = tickets.slice(startIndex, endIndex);
    
    ticketsToShow.forEach(ticket => {
        const row = document.createElement('tr');
        row.innerHTML = `
            <td>${ticket.event}</td>
            <td>${ticket.type}</td>
            <td>$${ticket.price.toFixed(2)}</td>
            <td>${ticket.available}</td>
            <td>${ticket.sold}</td>
            <td>
                <div class="action-buttons">
                    <button class="action-btn edit-ticket" data-id="${ticket.id}">
                        <i data-feather="edit-2"></i>
                    </button>
                    <button class="action-btn delete delete-ticket" data-id="${ticket.id}">
                        <i data-feather="trash-2"></i>
                    </button>
                </div>
            </td>
        `;
        ticketsTableBody.appendChild(row);
    });
    
    // Update pagination info
    document.getElementById('tickets-start').textContent = startIndex + 1;
    document.getElementById('tickets-end').textContent = Math.min(endIndex, tickets.length);
    document.getElementById('tickets-total').textContent = tickets.length;
    
    // Update pagination buttons
    updatePaginationButtons('tickets', tickets.length);
    
    // Add event listeners to action buttons
    feather.replace();
    document.querySelectorAll('.edit-ticket').forEach(button => {
        button.addEventListener('click', function() {
            const ticketId = parseInt(this.getAttribute('data-id'));
            editTicket(ticketId);
        });
    });
    
    document.querySelectorAll('.delete-ticket').forEach(button => {
        button.addEventListener('click', function() {
            const ticketId = parseInt(this.getAttribute('data-id'));
            deleteTicket(ticketId);
        });
    });
}

// Handle add ticket form submission
function handleAddTicket(e) {
    e.preventDefault();
    
    const event = document.getElementById('ticket-event').value;
    const type = document.getElementById('ticket-type').value;
    const price = parseFloat(document.getElementById('ticket-price').value);
    const quantity = parseInt(document.getElementById('ticket-quantity').value);
    const description = document.getElementById('ticket-description').value;
    
    const newTicket = {
        id: tickets.length > 0 ? Math.max(...tickets.map(t => t.id)) + 1 : 1,
        event: document.querySelector(`#ticket-event option[value="${event}"]`).textContent,
        type,
        price,
        available: quantity,
        sold: 0,
        description
    };
    
    tickets.push(newTicket);
    loadTickets();
    closeModal('add-ticket');
    
    // Show success message (in a real app)
    alert('Ticket type added successfully!');
}

// Edit ticket
function editTicket(ticketId) {
    const ticket = tickets.find(t => t.id === ticketId);
    if (!ticket) return;
    
    document.getElementById('edit-ticket-id').value = ticket.id;
    document.getElementById('edit-ticket-event').value = ticket.event.toLowerCase().replace(' ', '-');
    document.getElementById('edit-ticket-type').value = ticket.type;
    document.getElementById('edit-ticket-price').value = ticket.price;
    document.getElementById('edit-ticket-quantity').value = ticket.available;
    document.getElementById('edit-ticket-description').value = ticket.description || '';
    
    openModal('edit-ticket');
}

// Handle edit ticket form submission
function handleEditTicket(e) {
    e.preventDefault();
    
    const ticketId = parseInt(document.getElementById('edit-ticket-id').value);
    const event = document.getElementById('edit-ticket-event').value;
    const type = document.getElementById('edit-ticket-type').value;
    const price = parseFloat(document.getElementById('edit-ticket-price').value);
    const quantity = parseInt(document.getElementById('edit-ticket-quantity').value);
    const description = document.getElementById('edit-ticket-description').value;
    
    const ticketIndex = tickets.findIndex(t => t.id === ticketId);
    if (ticketIndex !== -1) {
        tickets[ticketIndex].event = document.querySelector(`#edit-ticket-event option[value="${event}"]`).textContent;
        tickets[ticketIndex].type = type;
        tickets[ticketIndex].price = price;
        tickets[ticketIndex].available = quantity;
        tickets[ticketIndex].description = description;
        
        loadTickets();
        closeModal('edit-ticket');
        
        // Show success message (in a real app)
        alert('Ticket type updated successfully!');
    }
}

// Delete ticket with confirmation
function deleteTicket(ticketId) {
    showConfirmation('Are you sure you want to delete this ticket type?', () => {
        const ticketIndex = tickets.findIndex(t => t.id === ticketId);
        if (ticketIndex !== -1) {
            tickets.splice(ticketIndex, 1);
            loadTickets();
            
            // Show success message (in a real app)
            alert('Ticket type deleted successfully!');
        }
    });
}

// Load user profile data
function loadUserProfile() {
    document.getElementById('profile-name').value = currentUser.name;
    document.getElementById('profile-email').value = currentUser.email;
    document.getElementById('profile-role').value = currentUser.role;
    document.getElementById('profile-phone').value = currentUser.phone;
    document.getElementById('profile-avatar-preview').src = currentUser.avatar;
    
    // Update sidebar avatar
    document.getElementById('user-avatar').src = currentUser.avatar;
}

// Handle update profile form submission
function handleUpdateProfile(e) {
    e.preventDefault();
    
    const name = document.getElementById('profile-name').value;
    const email = document.getElementById('profile-email').value;
    const phone = document.getElementById('profile-phone').value;
    
    currentUser.name = name;
    currentUser.email = email;
    currentUser.phone = phone;
    
    // Update sidebar
    document.querySelector('.username').textContent = name;
    document.querySelector('.user-email').textContent = email;
    
    closeModal('user-profile');
    
    // Show success message (in a real app)
    alert('Profile updated successfully!');
}

// Handle avatar upload
function handleAvatarUpload(e) {
    const file = e.target.files[0];
    if (file) {
        const reader = new FileReader();
        reader.onload = function(event) {
            currentUser.avatar = event.target.result;
            document.getElementById('profile-avatar-preview').src = event.target.result;
            document.getElementById('user-avatar').src = event.target.result;
        };
        reader.readAsDataURL(file);
    }
}

// Handle logout
function handleLogout() {
    showConfirmation('Are you sure you want to log out?', () => {
        // In a real app, this would redirect to the login page
        alert('You have been logged out. Redirecting to login page...');
        // window.location.href = 'login.html';
    });
}

// Show confirmation modal
function showConfirmation(message, confirmCallback) {
    document.getElementById('confirmation-message').textContent = message;
    openModal('confirmation');
    
    // Set up confirm button
    const confirmBtn = document.getElementById('confirm-action-btn');
    confirmBtn.onclick = function() {
        confirmCallback();
        closeModal('confirmation');
    };
}

// Change page for pagination
function changePage(section, direction) {
    const totalItems = {
        users: users.length,
        bookings: bookings.length,
        events: events.length,
        categories: categories.length,
        locations: locations.length,
        tickets: tickets.length
    }[section];
    
    const totalPages = Math.ceil(totalItems / itemsPerPage);
    
    currentPage[section] += direction;
    
    if (currentPage[section] < 1) {
        currentPage[section] = 1;
    } else if (currentPage[section] > totalPages) {
        currentPage[section] = totalPages;
    }
    
    // Reload the section data
    switch(section) {
        case 'users':
            loadUsers();
            break;
        case 'bookings':
            loadBookings();
            break;
        case 'events':
            loadEvents();
            break;
        case 'categories':
            loadCategories();
            break;
        case 'locations':
            loadLocations();
            break;
        case 'tickets':
            loadTickets();
            break;
    }
}

// Update pagination buttons state
function updatePaginationButtons(section, totalItems) {
    const totalPages = Math.ceil(totalItems / itemsPerPage);
    const prevBtn = document.getElementById(`${section}-prev`);
    const nextBtn = document.getElementById(`${section}-next`);
    
    prevBtn.disabled = currentPage[section] === 1;
    nextBtn.disabled = currentPage[section] === totalPages;
    
    if (prevBtn.disabled) {
        prevBtn.style.opacity = '0.5';
        prevBtn.style.cursor = 'not-allowed';
    } else {
        prevBtn.style.opacity = '1';
        prevBtn.style.cursor = 'pointer';
    }
    
    if (nextBtn.disabled) {
        nextBtn.style.opacity = '0.5';
        nextBtn.style.cursor = 'not-allowed';
    } else {
        nextBtn.style.opacity = '1';
        nextBtn.style.cursor = 'pointer';
    }
}
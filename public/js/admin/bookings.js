// Bookings Section Scripts

let bookings = [
    { id: 1, bookingId: '#EVT-4892', event: 'Summer Music Festival', user: 'john.doe@example.com', date: 'Jun 15, 2023', tickets: 2, amount: 120.00, status: 'completed' },
    { id: 2, bookingId: '#EVT-3567', event: 'Tech Conference 2023', user: 'sarah.smith@example.com', date: 'Jun 12, 2023', tickets: 1, amount: 75.00, status: 'completed' },
    { id: 3, bookingId: '#EVT-2781', event: 'Art Exhibition', user: 'mike.johnson@example.com', date: 'Jun 10, 2023', tickets: 4, amount: 60.00, status: 'pending' },
    { id: 4, bookingId: '#EVT-1895', event: 'Food Festival', user: 'emily.wilson@example.com', date: 'Jun 8, 2023', tickets: 3, amount: 45.00, status: 'cancelled' }
];

let currentPage = { bookings: 1 };
const itemsPerPage = 4;

document.addEventListener('DOMContentLoaded', function() {
    initializeBookingsEventListeners();
    loadBookings();
});

function initializeBookingsEventListeners() {
    const bookingSearch = document.getElementById('booking-search');
    if (bookingSearch) {
        bookingSearch.addEventListener('input', filterBookings);
    }
    
    const bookingsPrev = document.getElementById('bookings-prev');
    const bookingsNext = document.getElementById('bookings-next');
    if (bookingsPrev) bookingsPrev.addEventListener('click', () => changePage('bookings', -1));
    if (bookingsNext) bookingsNext.addEventListener('click', () => changePage('bookings', 1));
    
    const printBookingBtn = document.getElementById('print-booking-btn');
    if (printBookingBtn) {
        printBookingBtn.addEventListener('click', printBooking);
    }
}

function loadBookings() {
    const bookingsTableBody = document.getElementById('bookings-table-body');
    if (!bookingsTableBody) return;
    
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
    
    document.getElementById('bookings-start').textContent = startIndex + 1;
    document.getElementById('bookings-end').textContent = Math.min(endIndex, bookings.length);
    document.getElementById('bookings-total').textContent = bookings.length;
    
    updatePaginationButtons('bookings', bookings.length, itemsPerPage);
    
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

function filterBookings() {
    const searchTerm = document.getElementById('booking-search').value.toLowerCase();
    
    const filteredBookings = bookings.filter(booking => {
        return booking.bookingId.toLowerCase().includes(searchTerm) || 
               booking.event.toLowerCase().includes(searchTerm) || 
               booking.user.toLowerCase().includes(searchTerm);
    });
    
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
    
    document.getElementById('bookings-start').textContent = filteredBookings.length > 0 ? startIndex + 1 : 0;
    document.getElementById('bookings-end').textContent = Math.min(endIndex, filteredBookings.length);
    document.getElementById('bookings-total').textContent = filteredBookings.length;
    
    updatePaginationButtons('bookings', filteredBookings.length, itemsPerPage);
    
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
    
    document.getElementById('print-booking-btn').setAttribute('data-booking-id', bookingId);
    openModal('view-booking');
}

function printBooking(bookingId = null) {
    if (!bookingId) {
        bookingId = document.getElementById('print-booking-btn').getAttribute('data-booking-id');
    }
    
    const booking = bookings.find(b => b.id === parseInt(bookingId));
    if (!booking) return;
    
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

function changePage(section, direction) {
    const totalItems = bookings.length;
    const totalPages = Math.ceil(totalItems / itemsPerPage);
    
    currentPage[section] += direction;
    
    if (currentPage[section] < 1) {
        currentPage[section] = 1;
    } else if (currentPage[section] > totalPages) {
        currentPage[section] = totalPages;
    }
    
    loadBookings();
}


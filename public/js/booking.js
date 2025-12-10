/**
 * Bookings Management JavaScript Module
 * Handles all booking-related functionality in the admin panel
 */

// Configuration
const API_BASE_URL = '/event-booking-website/public/api/bookings_API.php';
const ITEMS_PER_PAGE = 10;

// State management
const bookingsState = {
    currentPage: 1,
    filters: {
        status: '',
        payment_status: '',
        search: ''
    },
    totalBookings: 0,
    totalPages: 0
};

// DOM Elements cache
const elements = {
    // Table elements
    tableBody: null,
    searchInput: null,
    statusFilter: null,
    paymentFilter: null,
    refreshBtn: null,
    prevBtn: null,
    nextBtn: null,
    pagesContainer: null,
    
    // Stats elements
    statTotal: null,
    statCompleted: null,
    statPending: null,
    statCancelled: null,
    statRevenue: null,
    
    // Modal elements
    viewBookingModal: null,
    updateStatusModal: null,
    updatePaymentModal: null,
    bookingDetails: null,
    printBtn: null,
    
    // Pagination info
    startElement: null,
    endElement: null,
    totalElement: null
};

// Utility Functions
const utils = {
    /**
     * Escape HTML to prevent XSS attacks
     */
    escapeHtml(text) {
        const div = document.createElement('div');
        div.textContent = text;
        return div.innerHTML;
    },

    /**
     * Format date string to readable format
     */
    formatDate(dateString) {
        if (!dateString) return 'N/A';
        const date = new Date(dateString);
        return date.toLocaleDateString('en-US', { 
            year: 'numeric', 
            month: 'short', 
            day: 'numeric' 
        });
    },

    /**
     * Format date with time
     */
    formatDateTime(dateString) {
        if (!dateString) return 'N/A';
        const date = new Date(dateString);
        return date.toLocaleDateString('en-US', { 
            year: 'numeric', 
            month: 'long', 
            day: 'numeric',
            hour: '2-digit',
            minute: '2-digit'
        });
    },

    /**
     * Format currency
     */
    formatCurrency(amount) {
        return new Intl.NumberFormat('en-US', {
            style: 'currency',
            currency: 'USD'
        }).format(amount || 0);
    },

    /**
     * Show loading state in table
     */
    showLoading() {
        if (elements.tableBody) {
            elements.tableBody.innerHTML = `
                <tr>
                    <td colspan="9" class="loading-state">
                        <div class="loading-spinner"></div>
                        <p>Loading bookings...</p>
                    </td>
                </tr>
            `;
        }
    },

    /**
     * Show error state in table
     */
    showError(message) {
        if (elements.tableBody) {
            elements.tableBody.innerHTML = `
                <tr>
                    <td colspan="9" class="error-state">
                        <i data-feather="alert-circle"></i>
                        <p>${message}</p>
                        <button class="primary-btn" onclick="bookings.loadBookings()">Try Again</button>
                    </td>
                </tr>
            `;
            feather.replace();
        }
    },

    /**
     * Show empty state in table
     */
    showEmptyState() {
        if (elements.tableBody) {
            elements.tableBody.innerHTML = `
                <tr>
                    <td colspan="9" class="empty-state">
                        <i data-feather="calendar"></i>
                        <p>No bookings found</p>
                        <button class="primary-btn" onclick="bookings.resetFilters()">Clear Filters</button>
                    </td>
                </tr>
            `;
            feather.replace();
        }
    }
};

// API Functions
const api = {
    /**
     * Fetch bookings from API with current filters
     */
    async fetchBookings(page = 1) {
        try {
            const params = new URLSearchParams({
                action: 'getAll',
                page: page,
                limit: ITEMS_PER_PAGE,
                ...bookingsState.filters
            });

            const response = await fetch(`${API_BASE_URL}?${params}`);
            
            if (!response.ok) {
                throw new Error(`HTTP error! status: ${response.status}`);
            }

            const result = await response.json();
            
            if (!result.success) {
                throw new Error(result.message || 'Failed to load bookings');
            }

            return result;
        } catch (error) {
            console.error('API Error:', error);
            throw error;
        }
    },

    /**
     * Fetch booking details by ID
     */
    async fetchBookingDetails(id) {
        try {
            const response = await fetch(`${API_BASE_URL}?action=getOne&id=${id}`);
            
            if (!response.ok) {
                throw new Error(`HTTP error! status: ${response.status}`);
            }

            const result = await response.json();
            
            if (!result.success) {
                throw new Error(result.message || 'Failed to load booking details');
            }

            return result.data;
        } catch (error) {
            console.error('API Error:', error);
            throw error;
        }
    },

    /**
     * Update booking status
     */
    async updateBookingStatus(id, status) {
        try {
            const response = await fetch(`${API_BASE_URL}?action=updateStatus`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({
                    id: id,
                    status: status
                })
            });

            const result = await response.json();
            
            if (!result.success) {
                throw new Error(result.message || 'Failed to update status');
            }

            return result;
        } catch (error) {
            console.error('API Error:', error);
            throw error;
        }
    },

    /**
     * Update payment status
     */
    async updatePaymentStatus(id, paymentStatus) {
        try {
            const response = await fetch(`${API_BASE_URL}?action=updatePaymentStatus`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({
                    id: id,
                    payment_status: paymentStatus
                })
            });

            const result = await response.json();
            
            if (!result.success) {
                throw new Error(result.message || 'Failed to update payment status');
            }

            return result;
        } catch (error) {
            console.error('API Error:', error);
            throw error;
        }
    },

    /**
     * Delete booking
     */
    async deleteBooking(id) {
        try {
            const response = await fetch(`${API_BASE_URL}?action=delete&id=${id}`);
            const result = await response.json();
            
            if (!result.success) {
                throw new Error(result.message || 'Failed to delete booking');
            }

            return result;
        } catch (error) {
            console.error('API Error:', error);
            throw error;
        }
    },

    /**
     * Fetch booking statistics
     */
    async fetchStats() {
        try {
            const response = await fetch(`${API_BASE_URL}?action=getStats`);
            const result = await response.json();
            
            if (!result.success) {
                throw new Error(result.message || 'Failed to load stats');
            }

            return result.stats;
        } catch (error) {
            console.error('API Error:', error);
            throw error;
        }
    }
};

// Bookings Management Functions
const bookings = {
    /**
     * Initialize bookings management
     */
    init() {
        this.cacheElements();
        this.bindEvents();
        this.loadBookings();
        this.loadStats();
    },

    /**
     * Cache DOM elements for performance
     */
    cacheElements() {
        // Table elements
        elements.tableBody = document.getElementById('bookings-table-body');
        elements.searchInput = document.getElementById('booking-search');
        elements.statusFilter = document.getElementById('booking-status-filter');
        elements.paymentFilter = document.getElementById('payment-status-filter');
        elements.refreshBtn = document.getElementById('refresh-bookings-btn');
        elements.prevBtn = document.getElementById('bookings-prev');
        elements.nextBtn = document.getElementById('bookings-next');
        elements.pagesContainer = document.getElementById('bookings-pages');
        
        // Stats elements
        elements.statTotal = document.getElementById('stat-total-bookings');
        elements.statCompleted = document.getElementById('stat-completed-bookings');
        elements.statPending = document.getElementById('stat-pending-bookings');
        elements.statCancelled = document.getElementById('stat-cancelled-bookings');
        elements.statRevenue = document.getElementById('stat-total-revenue');
        
        // Modal elements
        elements.viewBookingModal = document.getElementById('view-booking-modal');
        elements.updateStatusModal = document.getElementById('update-status-modal');
        elements.updatePaymentModal = document.getElementById('update-payment-modal');
        elements.bookingDetails = document.getElementById('booking-details');
        elements.printBtn = document.getElementById('print-booking-btn');
        
        // Pagination info
        elements.startElement = document.getElementById('bookings-start');
        elements.endElement = document.getElementById('bookings-end');
        elements.totalElement = document.getElementById('bookings-total');
    },

    /**
     * Bind event listeners
     */
    bindEvents() {
        // Search
        if (elements.searchInput) {
            elements.searchInput.addEventListener('input', (e) => {
                bookingsState.filters.search = e.target.value;
                bookingsState.currentPage = 1;
                this.loadBookings();
            });
        }
        
        // Status filter
        if (elements.statusFilter) {
            elements.statusFilter.addEventListener('change', (e) => {
                bookingsState.filters.status = e.target.value;
                bookingsState.currentPage = 1;
                this.loadBookings();
            });
        }
        
        // Payment status filter
        if (elements.paymentFilter) {
            elements.paymentFilter.addEventListener('change', (e) => {
                bookingsState.filters.payment_status = e.target.value;
                bookingsState.currentPage = 1;
                this.loadBookings();
            });
        }
        
        // Refresh button
        if (elements.refreshBtn) {
            elements.refreshBtn.addEventListener('click', () => {
                bookingsState.currentPage = 1;
                this.loadBookings();
                this.loadStats();
            });
        }
        
        // Pagination
        if (elements.prevBtn) {
            elements.prevBtn.addEventListener('click', () => this.changePage(-1));
        }
        
        if (elements.nextBtn) {
            elements.nextBtn.addEventListener('click', () => this.changePage(1));
        }
        
        // Print button
        if (elements.printBtn) {
            elements.printBtn.addEventListener('click', this.printBooking.bind(this));
        }
        
        // Update status button
        const confirmUpdateStatusBtn = document.getElementById('confirm-update-status-btn');
        if (confirmUpdateStatusBtn) {
            confirmUpdateStatusBtn.addEventListener('click', this.handleUpdateStatus.bind(this));
        }
        
        // Update payment button
        const confirmUpdatePaymentBtn = document.getElementById('confirm-update-payment-btn');
        if (confirmUpdatePaymentBtn) {
            confirmUpdatePaymentBtn.addEventListener('click', this.handleUpdatePayment.bind(this));
        }
        
        // Close modal buttons
        document.addEventListener('click', (e) => {
            if (e.target.closest('.close-modal')) {
                const button = e.target.closest('.close-modal');
                const modalName = button.getAttribute('data-modal');
                this.closeModal(modalName);
            }
            
            if (e.target.closest('.secondary-btn[data-modal]')) {
                const button = e.target.closest('.secondary-btn[data-modal]');
                const modalName = button.getAttribute('data-modal');
                this.closeModal(modalName);
            }
            
            if (e.target.classList.contains('modal')) {
                const modalId = e.target.id.replace('-modal', '');
                this.closeModal(modalId);
            }
        });
        
        // Delegate dynamic buttons
        document.addEventListener('click', (e) => {
            // View booking
            if (e.target.closest('.view-booking')) {
                const button = e.target.closest('.view-booking');
                const bookingId = parseInt(button.getAttribute('data-id'));
                this.viewBooking(bookingId);
            }
            
            // Update status
            if (e.target.closest('.update-status-btn')) {
                const button = e.target.closest('.update-status-btn');
                const bookingId = parseInt(button.getAttribute('data-id'));
                const bookingCode = button.getAttribute('data-code');
                this.showUpdateStatusModal(bookingId, bookingCode);
            }
            
            // Update payment
            if (e.target.closest('.update-payment-btn')) {
                const button = e.target.closest('.update-payment-btn');
                const bookingId = parseInt(button.getAttribute('data-id'));
                const bookingCode = button.getAttribute('data-code');
                this.showUpdatePaymentModal(bookingId, bookingCode);
            }
            
            // Delete booking
            if (e.target.closest('.delete-booking')) {
                const button = e.target.closest('.delete-booking');
                const bookingId = parseInt(button.getAttribute('data-id'));
                const bookingCode = button.getAttribute('data-code');
                this.deleteBooking(bookingId, bookingCode);
            }
        });
    },

    /**
     * Load bookings from API
     */
    async loadBookings() {
        utils.showLoading();
        
        try {
            const result = await api.fetchBookings(bookingsState.currentPage);
            this.renderBookingsTable(result.data, result.pagination);
        } catch (error) {
            utils.showError('Failed to load bookings. Please try again.');
            console.error('Load Bookings Error:', error);
        }
    },

    /**
     * Render bookings table
     */
    renderBookingsTable(bookings, pagination) {
        if (!bookings || bookings.length === 0) {
            utils.showEmptyState();
            this.updatePaginationInfo(pagination);
            return;
        }
        
        let html = '';
        
        bookings.forEach(booking => {
            const amount = parseFloat(booking.total_amount) || parseFloat(booking.ticket_price) || 0;
            
            html += `
                <tr>
                    <td>
                        <strong>${utils.escapeHtml(booking.booking_code)}</strong>
                        <small class="text-muted">${utils.formatDate(booking.created_at)}</small>
                    </td>
                    <td>
                        <div class="event-info">
                            <strong>${utils.escapeHtml(booking.event_title)}</strong>
                            <small>${utils.escapeHtml(booking.venue_name)}</small>
                        </div>
                    </td>
                    <td>
                        <div class="user-info">
                            <strong>${utils.escapeHtml(booking.user_name || booking.user_email)}</strong>
                            <small>${utils.escapeHtml(booking.user_email)}</small>
                        </div>
                    </td>
                    <td>${utils.formatDate(booking.created_at)}</td>
                    <td>${booking.ticket_count || booking.tickets || 1}</td>
                    <td>
                        <strong>${utils.formatCurrency(amount)}</strong>
                        ${booking.payment_method ? `<br><small>${booking.payment_method}</small>` : ''}
                    </td>
                    <td>
                        <span class="status-badge ${booking.status}">
                            ${booking.status ? booking.status.charAt(0).toUpperCase() + booking.status.slice(1) : 'Unknown'}
                        </span>
                        ${booking.refunded ? '<br><small class="text-warning">Refunded</small>' : ''}
                    </td>
                    <td>
                        <span class="status-badge ${booking.payment_status}">
                            ${booking.payment_status ? booking.payment_status.charAt(0).toUpperCase() + booking.payment_status.slice(1) : 'Pending'}
                        </span>
                    </td>
                    <td>
                        <div class="action-buttons">
                            <button class="action-btn view-booking" data-id="${booking.id}" title="View Details">
                                <i data-feather="eye"></i>
                            </button>
                            <button class="action-btn edit update-status-btn" data-id="${booking.id}" data-code="${booking.booking_code}" title="Update Status">
                                <i data-feather="edit-2"></i>
                            </button>
                            <button class="action-btn payment update-payment-btn" data-id="${booking.id}" data-code="${booking.booking_code}" title="Update Payment">
                                <i data-feather="dollar-sign"></i>
                            </button>
                            <button class="action-btn delete delete-booking" data-id="${booking.id}" data-code="${booking.booking_code}" title="Delete Booking">
                                <i data-feather="trash-2"></i>
                            </button>
                        </div>
                    </td>
                </tr>
            `;
        });
        
        elements.tableBody.innerHTML = html;
        this.updatePaginationInfo(pagination);
        this.renderPaginationButtons(pagination.pages);
        
        // Update feather icons
        feather.replace();
    },

    /**
     * Update pagination information
     */
    updatePaginationInfo(pagination) {
        if (!pagination) return;
        
        const start = ((bookingsState.currentPage - 1) * ITEMS_PER_PAGE) + 1;
        const end = Math.min(bookingsState.currentPage * ITEMS_PER_PAGE, pagination.total);
        
        elements.startElement.textContent = pagination.total > 0 ? start : 0;
        elements.endElement.textContent = end;
        elements.totalElement.textContent = pagination.total;
        
        bookingsState.totalBookings = pagination.total;
        bookingsState.totalPages = pagination.pages;
        
        // Update button states
        elements.prevBtn.disabled = bookingsState.currentPage === 1;
        elements.nextBtn.disabled = bookingsState.currentPage === pagination.pages;
    },

    /**
     * Render pagination buttons
     */
    renderPaginationButtons(totalPages) {
        if (!elements.pagesContainer) return;
        
        let buttons = '';
        const maxButtons = 5;
        let startPage = Math.max(1, bookingsState.currentPage - Math.floor(maxButtons / 2));
        let endPage = Math.min(totalPages, startPage + maxButtons - 1);
        
        if (endPage - startPage + 1 < maxButtons) {
            startPage = Math.max(1, endPage - maxButtons + 1);
        }
        
        // Previous ellipsis
        if (startPage > 1) {
            buttons += `<button class="pagination-btn" data-page="1">1</button>`;
            if (startPage > 2) {
                buttons += `<span class="pagination-ellipsis">...</span>`;
            }
        }
        
        // Page numbers
        for (let i = startPage; i <= endPage; i++) {
            buttons += `
                <button class="pagination-btn ${i === bookingsState.currentPage ? 'active' : ''}" 
                        data-page="${i}">
                    ${i}
                </button>
            `;
        }
        
        // Next ellipsis
        if (endPage < totalPages) {
            if (endPage < totalPages - 1) {
                buttons += `<span class="pagination-ellipsis">...</span>`;
            }
            buttons += `<button class="pagination-btn" data-page="${totalPages}">${totalPages}</button>`;
        }
        
        elements.pagesContainer.innerHTML = buttons;
        
        // Add click handlers
        elements.pagesContainer.querySelectorAll('.pagination-btn[data-page]').forEach(button => {
            button.addEventListener('click', (e) => {
                const page = parseInt(e.target.getAttribute('data-page'));
                if (page && page !== bookingsState.currentPage) {
                    bookingsState.currentPage = page;
                    this.loadBookings();
                }
            });
        });
    },

    /**
     * Change page
     */
    changePage(direction) {
        const newPage = bookingsState.currentPage + direction;
        
        if (newPage >= 1 && newPage <= bookingsState.totalPages) {
            bookingsState.currentPage = newPage;
            this.loadBookings();
        }
    },

    /**
     * Reset all filters
     */
    resetFilters() {
        bookingsState.filters = {
            status: '',
            payment_status: '',
            search: ''
        };
        
        if (elements.searchInput) elements.searchInput.value = '';
        if (elements.statusFilter) elements.statusFilter.value = '';
        if (elements.paymentFilter) elements.paymentFilter.value = '';
        
        bookingsState.currentPage = 1;
        this.loadBookings();
    },

    /**
     * Load and update statistics
     */
    async loadStats() {
        try {
            const stats = await api.fetchStats();
            
            if (elements.statTotal) elements.statTotal.textContent = stats.total_bookings;
            if (elements.statCompleted) elements.statCompleted.textContent = stats.completed_bookings;
            if (elements.statPending) elements.statPending.textContent = stats.pending_bookings;
            if (elements.statCancelled) elements.statCancelled.textContent = stats.cancelled_bookings;
            if (elements.statRevenue) elements.statRevenue.textContent = utils.formatCurrency(stats.total_revenue);
        } catch (error) {
            console.error('Load Stats Error:', error);
        }
    },

    /**
     * View booking details
     */
    async viewBooking(bookingId) {
        try {
            const booking = await api.fetchBookingDetails(bookingId);
            this.renderBookingDetails(booking);
            this.openModal('view-booking');
            feather.replace();
        } catch (error) {
            console.error('View Booking Error:', error);
            alert('Failed to load booking details: ' + error.message);
        }
    },

    /**
     * Render booking details
     */
    renderBookingDetails(booking) {
        if (!booking || !elements.bookingDetails) return;
        
        const totalAmount = parseFloat(booking.total_amount) || parseFloat(booking.ticket_price) || 0;
        
        const html = `
            <div class="booking-details-grid">
                <div class="booking-detail-section">
                    <h4>Booking Information</h4>
                    <div class="detail-row">
                        <span class="detail-label">Booking Code:</span>
                        <span class="detail-value">${utils.escapeHtml(booking.booking_code)}</span>
                    </div>
                    <div class="detail-row">
                        <span class="detail-label">Booking Date:</span>
                        <span class="detail-value">${utils.formatDateTime(booking.created_at)}</span>
                    </div>
                    <div class="detail-row">
                        <span class="detail-label">Status:</span>
                        <span class="detail-value">
                            <span class="status-badge ${booking.status}">
                                ${booking.status.charAt(0).toUpperCase() + booking.status.slice(1)}
                            </span>
                        </span>
                    </div>
                    <div class="detail-row">
                        <span class="detail-label">Payment Status:</span>
                        <span class="detail-value">
                            <span class="status-badge ${booking.payment_status}">
                                ${booking.payment_status.charAt(0).toUpperCase() + booking.payment_status.slice(1)}
                            </span>
                        </span>
                    </div>
                    ${booking.payment_method ? `
                    <div class="detail-row">
                        <span class="detail-label">Payment Method:</span>
                        <span class="detail-value">${utils.escapeHtml(booking.payment_method)}</span>
                    </div>
                    ` : ''}
                    ${booking.transaction_id ? `
                    <div class="detail-row">
                        <span class="detail-label">Transaction ID:</span>
                        <span class="detail-value">${utils.escapeHtml(booking.transaction_id)}</span>
                    </div>
                    ` : ''}
                </div>
                
                <div class="booking-detail-section">
                    <h4>User Information</h4>
                    <div class="detail-row">
                        <span class="detail-label">Name:</span>
                        <span class="detail-value">${utils.escapeHtml(booking.user_name)}</span>
                    </div>
                    <div class="detail-row">
                        <span class="detail-label">Email:</span>
                        <span class="detail-value">${utils.escapeHtml(booking.user_email)}</span>
                    </div>
                    ${booking.user_phone ? `
                    <div class="detail-row">
                        <span class="detail-label">Phone:</span>
                        <span class="detail-value">${utils.escapeHtml(booking.user_phone)}</span>
                    </div>
                    ` : ''}
                </div>
                
                <div class="booking-detail-section">
                    <h4>Event Information</h4>
                    <div class="detail-row">
                        <span class="detail-label">Event:</span>
                        <span class="detail-value">${utils.escapeHtml(booking.event_title)}</span>
                    </div>
                    <div class="detail-row">
                        <span class="detail-label">Category:</span>
                        <span class="detail-value">${utils.escapeHtml(booking.main_category_name)} > ${utils.escapeHtml(booking.subcategory_name)}</span>
                    </div>
                    <div class="detail-row">
                        <span class="detail-label">Date & Time:</span>
                        <span class="detail-value">${utils.formatDateTime(booking.event_date)}</span>
                    </div>
                    <div class="detail-row">
                        <span class="detail-label">Venue:</span>
                        <span class="detail-value">${utils.escapeHtml(booking.venue_name)}</span>
                    </div>
                    <div class="detail-row">
                        <span class="detail-label">Address:</span>
                        <span class="detail-value">${utils.escapeHtml(booking.venue_address)}, ${utils.escapeHtml(booking.venue_city)}, ${utils.escapeHtml(booking.venue_country)}</span>
                    </div>
                    ${booking.event_description ? `
                    <div class="detail-row">
                        <span class="detail-label">Description:</span>
                        <span class="detail-value">${utils.escapeHtml(booking.event_description)}</span>
                    </div>
                    ` : ''}
                </div>
                
                <div class="booking-detail-section">
                    <h4>Order Summary</h4>
                    <div class="detail-row">
                        <span class="detail-label">Ticket Quantity:</span>
                        <span class="detail-value">${booking.ticket_count || booking.tickets || 1}</span>
                    </div>
                    <div class="detail-row">
                        <span class="detail-label">Price per Ticket:</span>
                        <span class="detail-value">${utils.formatCurrency(booking.ticket_price)}</span>
                    </div>
                    <div class="detail-row">
                        <span class="detail-label">Total Amount:</span>
                        <span class="detail-value"><strong>${utils.formatCurrency(totalAmount)}</strong></span>
                    </div>
                    ${booking.discount ? `
                    <div class="detail-row">
                        <span class="detail-label">Discount:</span>
                        <span class="detail-value text-success">-${utils.formatCurrency(booking.discount)}</span>
                    </div>
                    ` : ''}
                    ${booking.tax ? `
                    <div class="detail-row">
                        <span class="detail-label">Tax:</span>
                        <span class="detail-value">${utils.formatCurrency(booking.tax)}</span>
                    </div>
                    ` : ''}
                </div>
            </div>
            
            ${booking.notes ? `
            <div class="booking-notes">
                <h4>Additional Notes</h4>
                <p>${utils.escapeHtml(booking.notes)}</p>
            </div>
            ` : ''}
        `;
        
        elements.bookingDetails.innerHTML = html;
        elements.printBtn.setAttribute('data-booking-id', booking.id);
    },

    /**
     * Show update status modal
     */
    showUpdateStatusModal(bookingId, bookingCode) {
        document.getElementById('booking-code-display').textContent = bookingCode;
        document.getElementById('confirm-update-status-btn').setAttribute('data-booking-id', bookingId);
        this.openModal('update-status');
    },

    /**
     * Show update payment modal
     */
    showUpdatePaymentModal(bookingId, bookingCode) {
        document.getElementById('payment-booking-code').textContent = bookingCode;
        document.getElementById('confirm-update-payment-btn').setAttribute('data-booking-id', bookingId);
        this.openModal('update-payment');
    },

    /**
     * Handle update status
     */
    async handleUpdateStatus() {
        const bookingId = document.getElementById('confirm-update-status-btn').getAttribute('data-booking-id');
        const newStatus = document.getElementById('new-booking-status').value;
        
        if (!bookingId || !newStatus) return;
        
        try {
            const result = await api.updateBookingStatus(bookingId, newStatus);
            
            alert('Booking status updated successfully!');
            this.closeModal('update-status');
            this.loadBookings();
            this.loadStats();
        } catch (error) {
            console.error('Update Status Error:', error);
            alert('Failed to update booking status: ' + error.message);
        }
    },

    /**
     * Handle update payment
     */
    async handleUpdatePayment() {
        const bookingId = document.getElementById('confirm-update-payment-btn').getAttribute('data-booking-id');
        const newPaymentStatus = document.getElementById('new-payment-status').value;
        
        if (!bookingId || !newPaymentStatus) return;
        
        try {
            const result = await api.updatePaymentStatus(bookingId, newPaymentStatus);
            
            alert('Payment status updated successfully!');
            this.closeModal('update-payment');
            this.loadBookings();
            this.loadStats();
        } catch (error) {
            console.error('Update Payment Error:', error);
            alert('Failed to update payment status: ' + error.message);
        }
    },

    /**
     * Delete booking
     */
    async deleteBooking(bookingId, bookingCode) {
        if (!confirm(`Are you sure you want to delete booking ${bookingCode}? This action cannot be undone.`)) {
            return;
        }
        
        try {
            const result = await api.deleteBooking(bookingId);
            
            alert('Booking deleted successfully!');
            this.loadBookings();
            this.loadStats();
        } catch (error) {
            console.error('Delete Booking Error:', error);
            alert('Failed to delete booking: ' + error.message);
        }
    },

    /**
     * Print booking receipt
     */
    printBooking() {
        const bookingDetails = elements.bookingDetails.innerHTML;
        const printWindow = window.open('', '_blank');
        
        printWindow.document.write(`
            <html>
                <head>
                    <title>Booking Receipt</title>
                    <style>
                        body { 
                            font-family: Arial, sans-serif; 
                            padding: 40px;
                            background: white;
                            color: black;
                        }
                        .receipt { 
                            max-width: 800px; 
                            margin: 0 auto; 
                            border: 2px solid #000;
                            padding: 40px;
                        }
                        .header { 
                            text-align: center; 
                            margin-bottom: 40px;
                            border-bottom: 2px solid #000;
                            padding-bottom: 20px;
                        }
                        .booking-details-grid {
                            display: grid;
                            grid-template-columns: repeat(2, 1fr);
                            gap: 30px;
                            margin-bottom: 40px;
                        }
                        .booking-detail-section {
                            margin-bottom: 30px;
                        }
                        .booking-detail-section h4 {
                            margin-top: 0;
                            color: #333;
                            border-bottom: 1px solid #ddd;
                            padding-bottom: 10px;
                        }
                        .detail-row {
                            display: flex;
                            justify-content: space-between;
                            margin-bottom: 10px;
                            padding-bottom: 10px;
                            border-bottom: 1px solid #eee;
                        }
                        .detail-label {
                            font-weight: bold;
                            color: #666;
                        }
                        .detail-value {
                            text-align: right;
                        }
                        .status-badge {
                            display: inline-block;
                            padding: 4px 12px;
                            border-radius: 20px;
                            font-size: 12px;
                            font-weight: bold;
                            margin-left: 10px;
                        }
                        .footer { 
                            text-align: center; 
                            margin-top: 40px; 
                            font-size: 14px; 
                            color: #666; 
                            border-top: 1px solid #ddd;
                            padding-top: 20px;
                        }
                        @media print {
                            body { padding: 0; }
                            .no-print { display: none; }
                        }
                    </style>
                </head>
                <body>
                    <div class="receipt">
                        <div class="header">
                            <h1>EحGZLY</h1>
                            <h2>Booking Receipt</h2>
                            <p>${new Date().toLocaleDateString()} ${new Date().toLocaleTimeString()}</p>
                        </div>
                        ${bookingDetails}
                        <div class="footer">
                            <p>Thank you for using EحGZLY!</p>
                            <p>www.egzly.com</p>
                        </div>
                    </div>
                    <div class="no-print" style="text-align: center; margin-top: 20px;">
                        <button onclick="window.print()" style="padding: 12px 24px; background: #3b82f6; color: white; border: none; border-radius: 5px; cursor: pointer; font-size: 16px;">
                            Print Receipt
                        </button>
                    </div>
                </body>
            </html>
        `);
        printWindow.document.close();
    },

    /**
     * Open modal
     */
    openModal(modalId) {
        const modal = document.getElementById(modalId + '-modal');
        if (modal) {
            modal.classList.remove('hidden');
            document.body.style.overflow = 'hidden';
        }
    },

    /**
     * Close modal
     */
    closeModal(modalId) {
        const modal = document.getElementById(modalId + '-modal');
        if (modal) {
            modal.classList.add('hidden');
            document.body.style.overflow = 'auto';
        }
    }
};

// Initialize when DOM is loaded
document.addEventListener('DOMContentLoaded', function() {
    bookings.init();
});

// Make available globally for debugging
window.bookings = bookings;
window.bookingsAPI = api;
window.bookingsUtils = utils;

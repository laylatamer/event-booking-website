// Configuration
const API_BASE_URL = '/event-booking-website/public/api/tickets_API.php';
const ITEMS_PER_PAGE = 10;

// State management
const ticketsState = {
    currentPage: 1,
    filters: {
        status: '',
        event_id: '',
        search: ''
    },
    totalTickets: 0,
    totalPages: 0,
    currentTicketId: null
};

// DOM Elements cache
const elements = {
    // Table elements
    tableBody: null,
    searchInput: null,
    statusFilter: null,
    eventFilter: null,
    addTicketBtn: null,
    refreshBtn: null,
    prevBtn: null,
    nextBtn: null,
    pagesContainer: null,
    
    // Stats elements
    statTotal: null,
    statActive: null,
    statSoldOut: null,
    statAvailable: null,
    statAveragePrice: null,
    
    // Modal elements
    ticketModal: null,
    viewTicketModal: null,
    quickUpdateModal: null,
    confirmationModal: null,
    ticketDetails: null,
    
    // Form elements
    ticketForm: null,
    ticketIdInput: null,
    ticketNameInput: null,
    ticketEventSelect: null,
    ticketPriceInput: null,
    ticketDiscountedPriceInput: null,
    ticketQuantityInput: null,
    ticketStatusSelect: null,
    ticketMinOrderInput: null,
    ticketMaxOrderInput: null,
    ticketSalesStartInput: null,
    ticketSalesEndInput: null,
    ticketDescriptionInput: null,
    ticketFeaturesInput: null,
    ticketFeaturesContainer: null,
    ticketCurrencySelect: null,
    ticketSubmitBtn: null,
    ticketSubmitText: null,
    ticketLoadingIcon: null,
    
    // Quick update elements
    quickUpdateTitle: null,
    quickUpdateMessage: null,
    quickUpdateField: null,
    confirmQuickUpdateBtn: null,
    
    // Confirmation elements
    confirmationMessage: null,
    confirmActionBtn: null,
    
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
            month: 'short', 
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
     * Format number with commas
     */
    formatNumber(number) {
        return new Intl.NumberFormat('en-US').format(number || 0);
    },

    /**
     * Show loading state in table
     */
    showLoading() {
        if (elements.tableBody) {
            elements.tableBody.innerHTML = `
                <tr>
                    <td colspan="8" class="loading-state">
                        <div class="loading-spinner"></div>
                        <p>Loading tickets...</p>
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
                    <td colspan="8" class="error-state">
                        <i data-feather="alert-circle"></i>
                        <p>${message}</p>
                        <button class="primary-btn" onclick="tickets.loadTickets()">Try Again</button>
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
                    <td colspan="8" class="empty-state">
                        <i data-feather="ticket"></i>
                        <p>No tickets found</p>
                        <button class="primary-btn" onclick="tickets.resetFilters()">Clear Filters</button>
                    </td>
                </tr>
            `;
            feather.replace();
        }
    },

    /**
     * Show loading on submit button
     */
    showButtonLoading(button, textElement, icon) {
        if (button) button.disabled = true;
        if (textElement) textElement.textContent = 'Processing...';
        if (icon) {
            icon.classList.remove('hidden');
            icon.style.animation = 'spin 1s linear infinite';
        }
    },

    /**
     * Hide loading on submit button
     */
    hideButtonLoading(button, textElement, icon, originalText) {
        if (button) button.disabled = false;
        if (textElement) textElement.textContent = originalText;
        if (icon) icon.classList.add('hidden');
    }
};

// API Functions
const api = {
    /**
     * Fetch tickets from API with current filters
     */
    async fetchTickets(page = 1) {
        try {
            const params = new URLSearchParams({
                action: 'getAll',
                page: page,
                limit: ITEMS_PER_PAGE,
                ...ticketsState.filters
            });

            const response = await fetch(`${API_BASE_URL}?${params}`);
            
            if (!response.ok) {
                throw new Error(`HTTP error! status: ${response.status}`);
            }

            const result = await response.json();
            
            if (!result.success) {
                throw new Error(result.message || 'Failed to load tickets');
            }

            return result;
        } catch (error) {
            console.error('API Error:', error);
            throw error;
        }
    },

    /**
     * Fetch ticket details by ID
     */
    async fetchTicketDetails(id) {
        try {
            const response = await fetch(`${API_BASE_URL}?action=getOne&id=${id}`);
            
            if (!response.ok) {
                throw new Error(`HTTP error! status: ${response.status}`);
            }

            const result = await response.json();
            
            if (!result.success) {
                throw new Error(result.message || 'Failed to load ticket details');
            }

            return result.data;
        } catch (error) {
            console.error('API Error:', error);
            throw error;
        }
    },

    /**
     * Create new ticket
     */
    async createTicket(data) {
        try {
            const response = await fetch(`${API_BASE_URL}?action=create`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify(data)
            });

            const result = await response.json();
            
            if (!result.success) {
                throw new Error(result.message || 'Failed to create ticket');
            }

            return result;
        } catch (error) {
            console.error('API Error:', error);
            throw error;
        }
    },

    /**
     * Update ticket
     */
    async updateTicket(id, data) {
        try {
            const response = await fetch(`${API_BASE_URL}?action=update&id=${id}`, {
                method: 'PUT',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify(data)
            });

            const result = await response.json();
            
            if (!result.success) {
                throw new Error(result.message || 'Failed to update ticket');
            }

            return result;
        } catch (error) {
            console.error('API Error:', error);
            throw error;
        }
    },

    /**
     * Update ticket status
     */
    async updateTicketStatus(id, status) {
        try {
            const response = await fetch(`${API_BASE_URL}?action=updateStatus`, {
                method: 'PUT',
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
                throw new Error(result.message || 'Failed to update ticket status');
            }

            return result;
        } catch (error) {
            console.error('API Error:', error);
            throw error;
        }
    },

    /**
     * Update ticket quantity
     */
    async updateTicketQuantity(id, quantityTotal) {
        try {
            const response = await fetch(`${API_BASE_URL}?action=updateQuantity`, {
                method: 'PUT',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({
                    id: id,
                    quantity_total: quantityTotal
                })
            });

            const result = await response.json();
            
            if (!result.success) {
                throw new Error(result.message || 'Failed to update ticket quantity');
            }

            return result;
        } catch (error) {
            console.error('API Error:', error);
            throw error;
        }
    },

    /**
     * Delete ticket
     */
    async deleteTicket(id) {
        try {
            const response = await fetch(`${API_BASE_URL}?action=delete&id=${id}`);
            const result = await response.json();
            
            if (!result.success) {
                throw new Error(result.message || 'Failed to delete ticket');
            }

            return result;
        } catch (error) {
            console.error('API Error:', error);
            throw error;
        }
    },

    /**
     * Fetch ticket statistics
     */
    async fetchStats() {
        try {
            const response = await fetch(`${API_BASE_URL}?action=getStats`);
            const result = await response.json();
            
            if (!result.success) {
                throw new Error(result.message || 'Failed to load stats');
            }

            return result.data;
        } catch (error) {
            console.error('API Error:', error);
            throw error;
        }
    }
};

// Tickets Management Functions
const tickets = {
    /**
     * Initialize tickets management
     */
    init() {
        this.cacheElements();
        this.bindEvents();
        this.loadTickets();
        this.loadStats();
    },

    /**
     * Cache DOM elements for performance
     */
    cacheElements() {
        // Table elements
        elements.tableBody = document.getElementById('tickets-table-body');
        elements.searchInput = document.getElementById('ticket-search');
        elements.statusFilter = document.getElementById('ticket-status-filter');
        elements.eventFilter = document.getElementById('ticket-event-filter');
        elements.addTicketBtn = document.getElementById('add-ticket-btn');
        elements.refreshBtn = document.getElementById('refresh-tickets-btn');
        elements.prevBtn = document.getElementById('tickets-prev');
        elements.nextBtn = document.getElementById('tickets-next');
        elements.pagesContainer = document.getElementById('tickets-pages');
        
        // Stats elements
        elements.statTotal = document.getElementById('stat-total-tickets');
        elements.statActive = document.getElementById('stat-active-tickets');
        elements.statSoldOut = document.getElementById('stat-sold-out-tickets');
        elements.statAvailable = document.getElementById('stat-available-tickets');
        elements.statAveragePrice = document.getElementById('stat-average-price');
        
        // Modal elements
        elements.ticketModal = document.getElementById('ticket-modal');
        elements.viewTicketModal = document.getElementById('view-ticket-modal');
        elements.quickUpdateModal = document.getElementById('quick-update-modal');
        elements.confirmationModal = document.getElementById('confirmation-modal');
        elements.ticketDetails = document.getElementById('ticket-details');
        
        // Form elements
        elements.ticketForm = document.getElementById('ticket-form');
        elements.ticketIdInput = document.getElementById('ticket-id');
        elements.ticketNameInput = document.getElementById('ticket-name');
        elements.ticketEventSelect = document.getElementById('ticket-event-id');
        elements.ticketPriceInput = document.getElementById('ticket-price');
        elements.ticketDiscountedPriceInput = document.getElementById('ticket-discounted-price');
        elements.ticketQuantityInput = document.getElementById('ticket-quantity-total');
        elements.ticketStatusSelect = document.getElementById('ticket-status');
        elements.ticketMinOrderInput = document.getElementById('ticket-min-order');
        elements.ticketMaxOrderInput = document.getElementById('ticket-max-order');
        elements.ticketSalesStartInput = document.getElementById('ticket-sales-start');
        elements.ticketSalesEndInput = document.getElementById('ticket-sales-end');
        elements.ticketDescriptionInput = document.getElementById('ticket-description');
        elements.ticketFeaturesInput = document.getElementById('ticket-features');
        elements.ticketFeaturesContainer = document.getElementById('ticket-features-container');
        elements.ticketCurrencySelect = document.getElementById('ticket-currency');
        elements.ticketSubmitBtn = document.getElementById('ticket-submit-btn');
        elements.ticketSubmitText = document.getElementById('ticket-submit-text');
        elements.ticketLoadingIcon = document.getElementById('ticket-loading-icon');
        
        // Quick update elements
        elements.quickUpdateTitle = document.getElementById('quick-update-title');
        elements.quickUpdateMessage = document.getElementById('quick-update-message');
        elements.quickUpdateField = document.getElementById('quick-update-field');
        elements.confirmQuickUpdateBtn = document.getElementById('confirm-quick-update-btn');
        
        // Confirmation elements
        elements.confirmationMessage = document.getElementById('confirmation-message');
        elements.confirmActionBtn = document.getElementById('confirm-action-btn');
        
        // Pagination info
        elements.startElement = document.getElementById('tickets-start');
        elements.endElement = document.getElementById('tickets-end');
        elements.totalElement = document.getElementById('tickets-total');
        
        // Edit ticket button
        elements.editTicketBtn = document.getElementById('edit-ticket-btn');
    },

    /**
     * Bind event listeners
     */
    bindEvents() {
        // Search
        if (elements.searchInput) {
            elements.searchInput.addEventListener('input', (e) => {
                ticketsState.filters.search = e.target.value;
                ticketsState.currentPage = 1;
                this.loadTickets();
            });
        }
        
        // Status filter
        if (elements.statusFilter) {
            elements.statusFilter.addEventListener('change', (e) => {
                ticketsState.filters.status = e.target.value;
                ticketsState.currentPage = 1;
                this.loadTickets();
            });
        }
        
        // Event filter
        if (elements.eventFilter) {
            elements.eventFilter.addEventListener('change', (e) => {
                ticketsState.filters.event_id = e.target.value;
                ticketsState.currentPage = 1;
                this.loadTickets();
            });
        }
        
        // Add ticket button
        if (elements.addTicketBtn) {
            elements.addTicketBtn.addEventListener('click', () => this.showAddModal());
        }
        
        // Refresh button
        if (elements.refreshBtn) {
            elements.refreshBtn.addEventListener('click', () => {
                ticketsState.currentPage = 1;
                this.loadTickets();
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
        
        // Ticket form submission
        if (elements.ticketForm) {
            elements.ticketForm.addEventListener('submit', this.handleFormSubmit.bind(this));
        }
        
        // Features management
        this.setupFeaturesManagement();
        
        // Quick update button
        if (elements.confirmQuickUpdateBtn) {
            elements.confirmQuickUpdateBtn.addEventListener('click', this.handleQuickUpdate.bind(this));
        }
        
        // Confirmation button
        if (elements.confirmActionBtn) {
            elements.confirmActionBtn.addEventListener('click', this.handleConfirmedAction.bind(this));
        }
        
        // Edit ticket button
        if (elements.editTicketBtn) {
            elements.editTicketBtn.addEventListener('click', () => {
                if (ticketsState.currentTicketId) {
                    this.editTicket(ticketsState.currentTicketId);
                }
            });
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
            // View ticket
            if (e.target.closest('.view-ticket')) {
                const button = e.target.closest('.view-ticket');
                const ticketId = parseInt(button.getAttribute('data-id'));
                this.viewTicket(ticketId);
            }
            
            // Edit ticket
            if (e.target.closest('.edit-ticket')) {
                const button = e.target.closest('.edit-ticket');
                const ticketId = parseInt(button.getAttribute('data-id'));
                this.editTicket(ticketId);
            }
            
            // Update status
            if (e.target.closest('.update-status-btn')) {
                const button = e.target.closest('.update-status-btn');
                const ticketId = parseInt(button.getAttribute('data-id'));
                const ticketName = button.getAttribute('data-name');
                this.showQuickStatusUpdate(ticketId, ticketName);
            }
            
            // Update quantity
            if (e.target.closest('.update-quantity-btn')) {
                const button = e.target.closest('.update-quantity-btn');
                const ticketId = parseInt(button.getAttribute('data-id'));
                const ticketName = button.getAttribute('data-name');
                this.showQuickQuantityUpdate(ticketId, ticketName);
            }
            
            // Delete ticket
            if (e.target.closest('.delete-ticket')) {
                const button = e.target.closest('.delete-ticket');
                const ticketId = parseInt(button.getAttribute('data-id'));
                const ticketName = button.getAttribute('data-name');
                this.deleteTicket(ticketId, ticketName);
            }
        });
        
        // Price input validation
        if (elements.ticketPriceInput) {
            elements.ticketPriceInput.addEventListener('input', this.validatePrices.bind(this));
        }
        
        if (elements.ticketDiscountedPriceInput) {
            elements.ticketDiscountedPriceInput.addEventListener('input', this.validatePrices.bind(this));
        }
    },

    /**
     * Setup features management
     */
    setupFeaturesManagement() {
        if (!elements.ticketFeaturesContainer) return;
        
        // Add initial feature input row
        this.addFeatureInputRow();
        
        // Add feature button event delegation
        elements.ticketFeaturesContainer.addEventListener('click', (e) => {
            if (e.target.closest('.add-feature-btn')) {
                this.addFeatureInputRow();
            }
            
            if (e.target.closest('.remove-feature-btn')) {
                const row = e.target.closest('.feature-input-row');
                if (row && elements.ticketFeaturesContainer.children.length > 1) {
                    row.remove();
                    this.updateFeaturesInput();
                }
            }
        });
        
        // Listen for feature input changes
        elements.ticketFeaturesContainer.addEventListener('input', () => {
            this.updateFeaturesInput();
        });
    },

    /**
     * Add feature input row
     */
    addFeatureInputRow() {
        const row = document.createElement('div');
        row.className = 'feature-input-row';
        row.innerHTML = `
            <input type="text" class="feature-input" placeholder="e.g., Access to main event">
            <button type="button" class="secondary-btn add-feature-btn">
                <i data-feather="plus"></i>
            </button>
            <button type="button" class="secondary-btn remove-feature-btn">
                <i data-feather="minus"></i>
            </button>
        `;
        elements.ticketFeaturesContainer.appendChild(row);
        feather.replace();
    },

    /**
     * Update features hidden input
     */
    updateFeaturesInput() {
        const featureInputs = elements.ticketFeaturesContainer.querySelectorAll('.feature-input');
        const features = Array.from(featureInputs)
            .map(input => input.value.trim())
            .filter(value => value !== '');
        
        elements.ticketFeaturesInput.value = JSON.stringify(features);
    },

    /**
     * Load features from data
     */
    loadFeatures(features) {
        if (!features || !Array.isArray(features)) return;
        
        // Clear existing inputs except first one
        const rows = elements.ticketFeaturesContainer.querySelectorAll('.feature-input-row');
        rows.forEach((row, index) => {
            if (index > 0) row.remove();
        });
        
        // Set first input
        const firstInput = elements.ticketFeaturesContainer.querySelector('.feature-input');
        if (firstInput && features.length > 0) {
            firstInput.value = features[0];
        }
        
        // Add remaining features
        for (let i = 1; i < features.length; i++) {
            this.addFeatureInputRow();
            const inputs = elements.ticketFeaturesContainer.querySelectorAll('.feature-input');
            if (inputs[i]) {
                inputs[i].value = features[i];
            }
        }
        
        this.updateFeaturesInput();
    },

    /**
     * Validate price inputs
     */
    validatePrices() {
        const price = parseFloat(elements.ticketPriceInput.value) || 0;
        const discountedPrice = parseFloat(elements.ticketDiscountedPriceInput.value) || 0;
        
        if (discountedPrice > price) {
            elements.ticketDiscountedPriceInput.setCustomValidity('Discounted price cannot be higher than regular price');
        } else {
            elements.ticketDiscountedPriceInput.setCustomValidity('');
        }
    },

    /**
     * Load tickets from API
     */
    async loadTickets() {
        utils.showLoading();
        
        try {
            const result = await api.fetchTickets(ticketsState.currentPage);
            this.renderTicketsTable(result.data.tickets, result.data.pagination);
        } catch (error) {
            utils.showError('Failed to load tickets. Please try again.');
            console.error('Load Tickets Error:', error);
        }
    },

    /**
     * Render tickets table
     */
    renderTicketsTable(tickets, pagination) {
        if (!tickets || tickets.length === 0) {
            utils.showEmptyState();
            this.updatePaginationInfo(pagination);
            return;
        }
        
        let html = '';
        
        tickets.forEach(ticket => {
            const availabilityPercentage = ticket.availability_percentage_formatted || 0;
            let availabilityClass = 'success';
            
            if (availabilityPercentage < 20) {
                availabilityClass = 'danger';
            } else if (availabilityPercentage < 50) {
                availabilityClass = 'warning';
            }
            
            html += `
                <tr>
                    <td>
                        <strong>${utils.escapeHtml(ticket.name)}</strong>
                    </td>
                    <td>
                        <div class="event-info">
                            <strong>${utils.escapeHtml(ticket.event_title)}</strong>
                            <small>${utils.formatDate(ticket.event_date)}</small>
                        </div>
                    </td>
                    <td>
                        <strong>${utils.formatCurrency(ticket.price)}</strong>
                        ${ticket.discounted_price ? `<br><small class="text-success">${utils.formatCurrency(ticket.discounted_price)}</small>` : ''}
                    </td>
                    <td>
                        <div class="quantity-info">
                            <div class="quantity-bar">
                                <div class="quantity-fill" style="width: ${availabilityPercentage}%"></div>
                            </div>
                            <div class="quantity-numbers">
                                <span class="text-${availabilityClass}">${ticket.quantity_available}</span>
                                <span class="text-muted">/ ${ticket.quantity_total}</span>
                                <small class="text-muted">(${ticket.quantity_sold} sold)</small>
                            </div>
                        </div>
                    </td>
                    <td>
                        <span class="status-badge ${ticket.status}">
                            ${ticket.status ? ticket.status.charAt(0).toUpperCase() + ticket.status.slice(1) : 'Unknown'}
                        </span>
                    </td>
                    <td>
                        <span class="status-badge ${ticket.sale_status}">
                            ${ticket.sale_status ? ticket.sale_status.charAt(0).toUpperCase() + ticket.sale_status.slice(1) : 'Unknown'}
                        </span>
                    </td>
                    <td>
                        <div class="action-buttons">
                            <button class="action-btn view-ticket" data-id="${ticket.id}" title="View Details">
                                <i data-feather="eye"></i>
                            </button>
                            <button class="action-btn edit edit-ticket" data-id="${ticket.id}" title="Edit Ticket">
                                <i data-feather="edit-2"></i>
                            </button>
                            <button class="action-btn update-status-btn" data-id="${ticket.id}" data-name="${ticket.name}" title="Update Status">
                                <i data-feather="toggle-right"></i>
                            </button>
                            <button class="action-btn update-quantity-btn" data-id="${ticket.id}" data-name="${ticket.name}" title="Update Quantity">
                                <i data-feather="plus-circle"></i>
                            </button>
                            <button class="action-btn delete delete-ticket" data-id="${ticket.id}" data-name="${ticket.name}" title="Delete Ticket">
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
        
        const start = ((ticketsState.currentPage - 1) * ITEMS_PER_PAGE) + 1;
        const end = Math.min(ticketsState.currentPage * ITEMS_PER_PAGE, pagination.total);
        
        elements.startElement.textContent = pagination.total > 0 ? start : 0;
        elements.endElement.textContent = end;
        elements.totalElement.textContent = pagination.total;
        
        ticketsState.totalTickets = pagination.total;
        ticketsState.totalPages = pagination.pages;
        
        // Update button states
        elements.prevBtn.disabled = ticketsState.currentPage === 1;
        elements.nextBtn.disabled = ticketsState.currentPage === pagination.pages;
    },

    /**
     * Render pagination buttons
     */
    renderPaginationButtons(totalPages) {
        if (!elements.pagesContainer) return;
        
        let buttons = '';
        const maxButtons = 5;
        let startPage = Math.max(1, ticketsState.currentPage - Math.floor(maxButtons / 2));
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
                <button class="pagination-btn ${i === ticketsState.currentPage ? 'active' : ''}" 
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
                if (page && page !== ticketsState.currentPage) {
                    ticketsState.currentPage = page;
                    this.loadTickets();
                }
            });
        });
    },

    /**
     * Change page
     */
    changePage(direction) {
        const newPage = ticketsState.currentPage + direction;
        
        if (newPage >= 1 && newPage <= ticketsState.totalPages) {
            ticketsState.currentPage = newPage;
            this.loadTickets();
        }
    },

    /**
     * Reset all filters
     */
    resetFilters() {
        ticketsState.filters = {
            status: '',
            event_id: '',
            search: ''
        };
        
        if (elements.searchInput) elements.searchInput.value = '';
        if (elements.statusFilter) elements.statusFilter.value = '';
        if (elements.eventFilter) elements.eventFilter.value = '';
        
        ticketsState.currentPage = 1;
        this.loadTickets();
    },

    /**
     * Load and update statistics
     */
    async loadStats() {
        try {
            const stats = await api.fetchStats();
            
            if (elements.statTotal) elements.statTotal.textContent = utils.formatNumber(stats.total_tickets);
            if (elements.statActive) elements.statActive.textContent = utils.formatNumber(stats.active_tickets);
            if (elements.statSoldOut) elements.statSoldOut.textContent = utils.formatNumber(stats.sold_out_tickets);
            if (elements.statAvailable) elements.statAvailable.textContent = utils.formatNumber(stats.available_quantity);
            if (elements.statAveragePrice) elements.statAveragePrice.textContent = utils.formatCurrency(stats.average_price);
        } catch (error) {
            console.error('Load Stats Error:', error);
        }
    },

    /**
     * Show add ticket modal
     */
    showAddModal() {
        // Reset form
        elements.ticketForm.reset();
        elements.ticketIdInput.value = '';
        elements.ticketModalTitle.textContent = 'Add New Ticket';
        elements.ticketSubmitText.textContent = 'Add Ticket';
        
        // Set default values
        elements.ticketStatusSelect.value = 'active';
        elements.ticketMinOrderInput.value = '1';
        elements.ticketMaxOrderInput.value = '10';
        elements.ticketCurrencySelect.value = 'USD';
        
        // Clear features
        this.loadFeatures([]);
        
        // Set default sales dates
        const now = new Date();
        const tomorrow = new Date(now);
        tomorrow.setDate(tomorrow.getDate() + 1);
        const nextMonth = new Date(now);
        nextMonth.setMonth(nextMonth.getMonth() + 1);
        
        elements.ticketSalesStartInput.value = this.formatDateTimeLocal(now);
        elements.ticketSalesEndInput.value = this.formatDateTimeLocal(nextMonth);
        
        this.openModal('ticket');
    },

    /**
     * View ticket details
     */
    async viewTicket(ticketId) {
        try {
            const ticket = await api.fetchTicketDetails(ticketId);
            this.renderTicketDetails(ticket);
            ticketsState.currentTicketId = ticketId;
            this.openModal('view-ticket');
            feather.replace();
        } catch (error) {
            console.error('View Ticket Error:', error);
            alert('Failed to load ticket details: ' + error.message);
        }
    },

    /**
     * Render ticket details
     */
    renderTicketDetails(ticket) {
        if (!ticket || !elements.ticketDetails) return;
        
        const html = `
            <div class="ticket-details-grid">
                <div class="ticket-detail-section">
                    <h4>Ticket Information</h4>
                    <div class="detail-row">
                        <span class="detail-label">Ticket Name:</span>
                        <span class="detail-value">${utils.escapeHtml(ticket.name)}</span>
                    </div>
                    <div class="detail-row">
                        <span class="detail-label">Type:</span>
                        <span class="detail-value">${utils.escapeHtml(ticket.type)}</span>
                    </div>
                    <div class="detail-row">
                        <span class="detail-label">Status:</span>
                        <span class="detail-value">
                            <span class="status-badge ${ticket.status}">
                                ${ticket.status.charAt(0).toUpperCase() + ticket.status.slice(1)}
                            </span>
                        </span>
                    </div>
                    <div class="detail-row">
                        <span class="detail-label">Sale Status:</span>
                        <span class="detail-value">
                            <span class="status-badge ${ticket.sale_status}">
                                ${ticket.sale_status.charAt(0).toUpperCase() + ticket.sale_status.slice(1)}
                            </span>
                        </span>
                    </div>
                    <div class="detail-row">
                        <span class="detail-label">Created:</span>
                        <span class="detail-value">${utils.formatDateTime(ticket.created_at)}</span>
                    </div>
                    <div class="detail-row">
                        <span class="detail-label">Last Updated:</span>
                        <span class="detail-value">${utils.formatDateTime(ticket.updated_at)}</span>
                    </div>
                </div>
                
                <div class="ticket-detail-section">
                    <h4>Pricing Information</h4>
                    <div class="detail-row">
                        <span class="detail-label">Price:</span>
                        <span class="detail-value"><strong>${utils.formatCurrency(ticket.price)}</strong></span>
                    </div>
                    ${ticket.discounted_price ? `
                    <div class="detail-row">
                        <span class="detail-label">Discounted Price:</span>
                        <span class="detail-value text-success">${utils.formatCurrency(ticket.discounted_price)}</span>
                    </div>
                    <div class="detail-row">
                        <span class="detail-label">Discount:</span>
                        <span class="detail-value text-success">${ticket.discount_percentage || 0}% off</span>
                    </div>
                    ` : ''}
                    <div class="detail-row">
                        <span class="detail-label">Currency:</span>
                        <span class="detail-value">${ticket.currency}</span>
                    </div>
                </div>
                
                <div class="ticket-detail-section">
                    <h4>Quantity Information</h4>
                    <div class="detail-row">
                        <span class="detail-label">Total Quantity:</span>
                        <span class="detail-value">${utils.formatNumber(ticket.quantity_total)}</span>
                    </div>
                    <div class="detail-row">
                        <span class="detail-label">Available:</span>
                        <span class="detail-value text-success">${utils.formatNumber(ticket.quantity_available)}</span>
                    </div>
                    <div class="detail-row">
                        <span class="detail-label">Sold:</span>
                        <span class="detail-value">${utils.formatNumber(ticket.quantity_sold)}</span>
                    </div>
                    <div class="detail-row">
                        <span class="detail-label">Availability:</span>
                        <span class="detail-value">
                            <div class="availability-bar">
                                <div class="availability-fill" style="width: ${ticket.availability_percentage_formatted || 0}%"></div>
                            </div>
                            <span class="availability-text">${ticket.availability_percentage_formatted || 0}% available</span>
                        </span>
                    </div>
                    <div class="detail-row">
                        <span class="detail-label">Min per Order:</span>
                        <span class="detail-value">${ticket.min_per_order}</span>
                    </div>
                    <div class="detail-row">
                        <span class="detail-label">Max per Order:</span>
                        <span class="detail-value">${ticket.max_per_order}</span>
                    </div>
                </div>
                
                <div class="ticket-detail-section">
                    <h4>Event Information</h4>
                    <div class="detail-row">
                        <span class="detail-label">Event:</span>
                        <span class="detail-value">${utils.escapeHtml(ticket.event_title)}</span>
                    </div>
                    <div class="detail-row">
                        <span class="detail-label">Date & Time:</span>
                        <span class="detail-value">${utils.formatDateTime(ticket.event_date)}</span>
                    </div>
                    <div class="detail-row">
                        <span class="detail-label">Venue:</span>
                        <span class="detail-value">${utils.escapeHtml(ticket.venue_name)}</span>
                    </div>
                    <div class="detail-row">
                        <span class="detail-label">Category:</span>
                        <span class="detail-value">${utils.escapeHtml(ticket.main_category_name)} > ${utils.escapeHtml(ticket.subcategory_name)}</span>
                    </div>
                </div>
                
                <div class="ticket-detail-section">
                    <h4>Sales Period</h4>
                    <div class="detail-row">
                        <span class="detail-label">Sales Start:</span>
                        <span class="detail-value">${ticket.sales_start_date ? utils.formatDateTime(ticket.sales_start_date) : 'Immediately'}</span>
                    </div>
                    <div class="detail-row">
                        <span class="detail-label">Sales End:</span>
                        <span class="detail-value">${ticket.sales_end_date ? utils.formatDateTime(ticket.sales_end_date) : 'Until event'}</span>
                    </div>
                </div>
            </div>
            
            ${ticket.description ? `
            <div class="ticket-description">
                <h4>Description</h4>
                <p>${utils.escapeHtml(ticket.description)}</p>
            </div>
            ` : ''}
            
            ${ticket.features && ticket.features.length > 0 ? `
            <div class="ticket-features">
                <h4>Features Included</h4>
                <ul class="features-list">
                    ${ticket.features.map(feature => `<li>${utils.escapeHtml(feature)}</li>`).join('')}
                </ul>
            </div>
            ` : ''}
        `;
        
        elements.ticketDetails.innerHTML = html;
    },

    /**
     * Edit ticket
     */
    async editTicket(ticketId) {
        try {
            const ticket = await api.fetchTicketDetails(ticketId);
            
            // Populate form
            elements.ticketIdInput.value = ticket.id;
            elements.ticketNameInput.value = ticket.name;
            elements.ticketEventSelect.value = ticket.event_id;
            elements.ticketPriceInput.value = ticket.price;
            elements.ticketDiscountedPriceInput.value = ticket.discounted_price || '';
            elements.ticketQuantityInput.value = ticket.quantity_total;
            elements.ticketStatusSelect.value = ticket.status;
            elements.ticketMinOrderInput.value = ticket.min_per_order || 1;
            elements.ticketMaxOrderInput.value = ticket.max_per_order || 10;
            elements.ticketSalesStartInput.value = ticket.sales_start_date ? this.formatDateTimeLocal(new Date(ticket.sales_start_date)) : '';
            elements.ticketSalesEndInput.value = ticket.sales_end_date ? this.formatDateTimeLocal(new Date(ticket.sales_end_date)) : '';
            elements.ticketDescriptionInput.value = ticket.description || '';
            elements.ticketCurrencySelect.value = ticket.currency || 'USD';
            
            // Load features
            this.loadFeatures(ticket.features || []);
            
            // Update modal title and button
            elements.ticketModalTitle.textContent = 'Edit Ticket';
            elements.ticketSubmitText.textContent = 'Update Ticket';
            
            this.closeModal('view-ticket');
            this.openModal('ticket');
            
        } catch (error) {
            console.error('Edit Ticket Error:', error);
            alert('Failed to load ticket for editing: ' + error.message);
        }
    },

    /**
     * Show quick status update modal
     */
    showQuickStatusUpdate(ticketId, ticketName) {
        ticketsState.currentTicketId = ticketId;
        
        elements.quickUpdateTitle.textContent = 'Update Ticket Status';
        elements.quickUpdateMessage.textContent = `Update status for ticket "${ticketName}":`;
        
        elements.quickUpdateField.innerHTML = `
            <select id="quick-status-select" class="status-select">
                <option value="active">Active</option>
                <option value="inactive">Inactive</option>
                <option value="sold_out">Sold Out</option>
            </select>
        `;
        
        this.openModal('quick-update');
    },

    /**
     * Show quick quantity update modal
     */
    showQuickQuantityUpdate(ticketId, ticketName) {
        ticketsState.currentTicketId = ticketId;
        
        elements.quickUpdateTitle.textContent = 'Update Ticket Quantity';
        elements.quickUpdateMessage.textContent = `Update total quantity for ticket "${ticketName}":`;
        
        elements.quickUpdateField.innerHTML = `
            <div class="form-group">
                <label for="quick-quantity-input">Total Quantity</label>
                <input type="number" id="quick-quantity-input" class="form-control" min="1" required>
            </div>
        `;
        
        this.openModal('quick-update');
    },

    /**
     * Handle quick update
     */
    async handleQuickUpdate() {
        const ticketId = ticketsState.currentTicketId;
        
        if (!ticketId) return;
        
        try {
            if (elements.quickUpdateTitle.textContent.includes('Status')) {
                // Update status
                const newStatus = document.getElementById('quick-status-select').value;
                const result = await api.updateTicketStatus(ticketId, newStatus);
                
                alert('Ticket status updated successfully!');
            } else {
                // Update quantity
                const newQuantity = document.getElementById('quick-quantity-input').value;
                const result = await api.updateTicketQuantity(ticketId, parseInt(newQuantity));
                
                alert('Ticket quantity updated successfully!');
            }
            
            this.closeModal('quick-update');
            this.loadTickets();
            this.loadStats();
            
        } catch (error) {
            console.error('Quick Update Error:', error);
            alert('Failed to update ticket: ' + error.message);
        }
    },

    /**
     * Delete ticket with confirmation
     */
    deleteTicket(ticketId, ticketName) {
        ticketsState.currentTicketId = ticketId;
        
        elements.confirmationMessage.textContent = `Are you sure you want to delete ticket "${ticketName}"? This action cannot be undone.`;
        
        this.openModal('confirmation');
    },

    /**
     * Handle confirmed action (delete)
     */
    async handleConfirmedAction() {
        const ticketId = ticketsState.currentTicketId;
        
        if (!ticketId) return;
        
        try {
            const result = await api.deleteTicket(ticketId);
            
            alert('Ticket deleted successfully!');
            this.closeModal('confirmation');
            this.loadTickets();
            this.loadStats();
            
        } catch (error) {
            console.error('Delete Ticket Error:', error);
            alert('Failed to delete ticket: ' + error.message);
        }
    },

    /**
     * Handle form submission
     */
    async handleFormSubmit(e) {
        e.preventDefault();
        
        // Show loading state
        const originalText = elements.ticketSubmitText.textContent;
        utils.showButtonLoading(
            elements.ticketSubmitBtn,
            elements.ticketSubmitText,
            elements.ticketLoadingIcon
        );
        
        try {
            // Collect form data
            const formData = new FormData(elements.ticketForm);
            const data = Object.fromEntries(formData.entries());
            
            // Parse JSON fields
            if (data.features) {
                try {
                    data.features = JSON.parse(data.features);
                } catch (error) {
                    data.features = [];
                }
            }
            
            // Parse numeric fields
            data.price = parseFloat(data.price);
            if (data.discounted_price) {
                data.discounted_price = parseFloat(data.discounted_price);
            }
            data.quantity_total = parseInt(data.quantity_total);
            data.min_per_order = parseInt(data.min_per_order) || 1;
            data.max_per_order = parseInt(data.max_per_order) || 10;
            
            // Handle empty dates
            if (!data.sales_start_date) delete data.sales_start_date;
            if (!data.sales_end_date) delete data.sales_end_date;
            
            const ticketId = elements.ticketIdInput.value;
            
            let result;
            if (ticketId) {
                // Update existing ticket
                result = await api.updateTicket(ticketId, data);
            } else {
                // Create new ticket
                result = await api.createTicket(data);
            }
            
            alert(result.message || (ticketId ? 'Ticket updated successfully!' : 'Ticket created successfully!'));
            
            this.closeModal('ticket');
            this.loadTickets();
            this.loadStats();
            
        } catch (error) {
            console.error('Form Submit Error:', error);
            alert('Failed to save ticket: ' + error.message);
        } finally {
            // Hide loading state
            utils.hideButtonLoading(
                elements.ticketSubmitBtn,
                elements.ticketSubmitText,
                elements.ticketLoadingIcon,
                originalText
            );
        }
    },

    /**
     * Format date for datetime-local input
     */
    formatDateTimeLocal(date) {
        const year = date.getFullYear();
        const month = String(date.getMonth() + 1).padStart(2, '0');
        const day = String(date.getDate()).padStart(2, '0');
        const hours = String(date.getHours()).padStart(2, '0');
        const minutes = String(date.getMinutes()).padStart(2, '0');
        
        return `${year}-${month}-${day}T${hours}:${minutes}`;
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
    tickets.init();
});

// Make available globally for debugging
window.tickets = tickets;
window.ticketsAPI = api;
window.ticketsUtils = utils;
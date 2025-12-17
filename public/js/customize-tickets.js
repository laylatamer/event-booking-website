// Ticket Customization JavaScript - Per Category Selection
let selectedByCategory = {}; // { "Fanpit": 2, "Golden Circle": 1, "Regular": 1 }
let guestNames = {}; // { "1": "JOHN DOE", "2": "JANE SMITH" }
let ticketCounter = 0; // Global counter for ticket numbering

// Initialize
document.addEventListener('DOMContentLoaded', function() {
    // Initialize selectedByCategory with zeros
    if (eventData.reservationsByCategory) {
        Object.keys(eventData.reservationsByCategory).forEach(category => {
            selectedByCategory[category] = 0;
        });
    }
    
    // Attach event listeners to category buttons
    attachCategoryListeners();
    updateDisplay();
});

// Attach listeners to category increase/decrease buttons
function attachCategoryListeners() {
    document.querySelectorAll('.category-increase').forEach(btn => {
        btn.addEventListener('click', function() {
            const category = this.getAttribute('data-category');
            increaseCategoryQuantity(category);
        });
    });
    
    document.querySelectorAll('.category-decrease').forEach(btn => {
        btn.addEventListener('click', function() {
            const category = this.getAttribute('data-category');
            decreaseCategoryQuantity(category);
        });
    });
}

// Increase quantity for a category
function increaseCategoryQuantity(category) {
    const categoryData = eventData.reservationsByCategory[category];
    if (!categoryData) return;
    
    const current = selectedByCategory[category] || 0;
    const max = categoryData.total;
    
    if (current < max) {
        selectedByCategory[category] = current + 1;
        updateCategoryDisplay(category);
        updateDisplay();
    }
}

// Decrease quantity for a category
function decreaseCategoryQuantity(category) {
    const current = selectedByCategory[category] || 0;
    if (current > 0) {
        selectedByCategory[category] = current - 1;
        updateCategoryDisplay(category);
        updateDisplay();
    }
}

// Update display for a specific category
function updateCategoryDisplay(category) {
    const qtyDisplay = document.querySelector(`.category-qty[data-category="${category}"]`);
    if (qtyDisplay) {
        qtyDisplay.textContent = selectedByCategory[category] || 0;
    }
    
    // Update button states
    const categoryData = eventData.reservationsByCategory[category];
    if (categoryData) {
        const current = selectedByCategory[category] || 0;
        const max = categoryData.total;
        
        const increaseBtn = document.querySelector(`.category-increase[data-category="${category}"]`);
        const decreaseBtn = document.querySelector(`.category-decrease[data-category="${category}"]`);
        
        if (increaseBtn) increaseBtn.disabled = current >= max;
        if (decreaseBtn) decreaseBtn.disabled = current <= 0;
    }
}

// Calculate total selected tickets
function getTotalSelected() {
    return Object.values(selectedByCategory).reduce((sum, qty) => sum + qty, 0);
}

// Update overall display
function updateDisplay() {
    const totalSelected = getTotalSelected();
    document.getElementById('customize-count').textContent = totalSelected;
    
    const totalCost = (totalSelected * eventData.costPerTicket).toFixed(2);
    document.getElementById('total-cost').textContent = totalCost;
    
    // Update generate button
    const generateBtn = document.getElementById('generate-btn');
    if (generateBtn) {
        generateBtn.disabled = totalSelected === 0;
    }
}

// Generate tickets based on selected categories
function generateTickets() {
    const totalSelected = getTotalSelected();
    if (totalSelected === 0) {
        alert('Please select at least one ticket to customize.');
        return;
    }
    
    const container = document.getElementById('tickets-container');
    const actionButtons = document.getElementById('action-buttons');
    
    // Clear existing tickets
    container.innerHTML = '';
    guestNames = {};
    ticketCounter = 0;
    
    // Generate tickets for each category
    Object.keys(selectedByCategory).forEach(category => {
        const quantity = selectedByCategory[category] || 0;
        for (let i = 0; i < quantity; i++) {
            ticketCounter++;
            const ticketHTML = createTicketHTML(ticketCounter, category);
            container.innerHTML += ticketHTML;
        }
    });
    
    // Show tickets and action buttons
    container.classList.remove('hidden');
    actionButtons.classList.remove('hidden');
    
    // Scroll to tickets
    setTimeout(() => {
        container.scrollIntoView({ behavior: 'smooth', block: 'start' });
    }, 100);
    
    // Add event listeners to inputs
    attachInputListeners();
}

// Create ticket HTML - WITH TICKET TYPE CONTAINER
function createTicketHTML(ticketNumber, categoryName) {
    // Generate random barcode lines
    let barcodeHTML = '';
    for (let i = 0; i < 20; i++) {
        const height = Math.floor(Math.random() * 60 + 40);
        barcodeHTML += `<div class="barcode-line" style="height: ${height}%"></div>`;
    }
    
    // Generate random ticket number
    const ticketId = 'TKT-' + Math.random().toString(36).substr(2, 9).toUpperCase();
    
    return `
        <div class="ticket-wrapper" data-ticket="${ticketNumber}" data-category="${categoryName}">
            <div class="perspective-container">
                <div class="ticket-container" id="ticket-${ticketNumber}">
                    <!-- FRONT OF TICKET -->
                    <div class="ticket-face ticket-front">
                        <div class="ticket-notch-left"></div>
                        <div class="ticket-notch-right"></div>
                        
                        <!-- TICKET TYPE CONTAINER (Orange, text downward) -->
                        <div class="ticket-type-container">
                            <div class="ticket-type-text">${categoryName.toUpperCase()}</div>
                        </div>
                        
                        <div class="ticket-main-section">
                            <div class="ticket-border">
                                <div class="ticket-content-front">
                                    <h1 class="ticket-title">TICKET</h1>
                                    
                                    <div class="ticket-logo-container">
                                        <img src="../../public/img/logo2.png" alt="EGZLY Logo" class="ticket-logo">
                                    </div>

                                    <div class="ticket-info-grid">
                                        <div class="ticket-info-item">
                                            <div class="ticket-info-label">DATE</div>
                                            <div class="ticket-info-value date-value">${eventData.date}</div>
                                        </div>
                                        <div class="ticket-separator"></div>
                                        <div class="ticket-info-item">
                                            <div class="ticket-info-label">NAME</div>
                                            <div class="ticket-info-value" id="display-name-${ticketNumber}">-</div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="ticket-stub">
                            <div class="ticket-stub-text">${eventData.title.toUpperCase()}</div>
                            <div class="ticket-barcode">
                                ${barcodeHTML}
                            </div>
                            <div class="ticket-stub-number">760987-28875</div>
                        </div>
                    </div>

                    <!-- BACK OF TICKET -->
                    <div class="ticket-face ticket-back">
                        <div class="ticket-notch-left"></div>
                        <div class="ticket-notch-right"></div>
                        
                        <div class="ticket-back-content">
                            <h2 class="ticket-back-title">${eventData.title}</h2>
                            
                            <div class="ticket-back-details">
                                <div class="ticket-detail-row">
                                    <span class="ticket-detail-label">Date:</span>
                                    <span class="ticket-detail-value">${eventData.date}</span>
                                </div>
                                <div class="ticket-detail-row">
                                    <span class="ticket-detail-label">Time:</span>
                                    <span class="ticket-detail-value">${eventData.time}</span>
                                </div>
                                <div class="ticket-detail-row">
                                    <span class="ticket-detail-label">Venue:</span>
                                    <span class="ticket-detail-value">${eventData.venue}</span>
                                </div>
                                <div class="ticket-detail-row">
                                    <span class="ticket-detail-label">Category:</span>
                                    <span class="ticket-detail-value">${categoryName}</span>
                                </div>
                                <div class="ticket-detail-row">
                                    <span class="ticket-detail-label">Guest:</span>
                                    <span class="ticket-detail-value" id="back-display-name-${ticketNumber}">-</span>
                                </div>
                            </div>

                            <div class="ticket-back-footer">
                                <p>Please present this ticket at the entrance</p>
                                <p class="ticket-back-code">Ticket ID: ${ticketId}</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Guest Name Input -->
            <div style="margin-top: 20px; text-align: center;">
                <label for="guest-${ticketNumber}" style="display: block; margin-bottom: 10px; color: #fff; font-weight: 600; font-size: 14px;">
                    👤 Enter Guest Name for ${categoryName} Ticket #${ticketNumber}
                </label>
                <input 
                    type="text" 
                    id="guest-${ticketNumber}" 
                    class="guest-name-input" 
                    placeholder="Enter guest name..."
                    data-ticket="${ticketNumber}"
                    required
                    style="width: 100%; max-width: 400px; padding: 12px; border-radius: 8px; border: 2px solid #ff5722; background: rgba(255, 87, 34, 0.1); color: #fff; font-size: 16px; font-weight: 600;"
                />
            </div>
            
            <!-- Flip Button -->
            <button class="flip-button" onclick="flipTicket(${ticketNumber})">
                <span class="flip-button-icon">↻</span>
                <span id="flip-text-${ticketNumber}">View Back</span>
            </button>
        </div>
    `;
}

// Attach input listeners
function attachInputListeners() {
    const inputs = document.querySelectorAll('.guest-name-input');
    inputs.forEach(input => {
        input.addEventListener('input', function() {
            const ticketNumber = this.getAttribute('data-ticket');
            const name = this.value.trim().toUpperCase();
            guestNames[ticketNumber] = name;
            
            // Update name displays on ticket front and back
            const frontDisplay = document.getElementById(`display-name-${ticketNumber}`);
            const backDisplay = document.getElementById(`back-display-name-${ticketNumber}`);
            if (frontDisplay) frontDisplay.textContent = name || '-';
            if (backDisplay) backDisplay.textContent = name || '-';
        });
    });
}

// Flip ticket function
window.flipTicket = function(ticketNumber) {
    const ticket = document.getElementById(`ticket-${ticketNumber}`);
    const flipText = document.getElementById(`flip-text-${ticketNumber}`);
    
    if (ticket) {
        ticket.classList.toggle('flipped');
        const isFlipped = ticket.classList.contains('flipped');
        if (flipText) {
            flipText.textContent = isFlipped ? 'View Front' : 'View Back';
        }
    }
}

// Validate all guest names
function validateGuestNames() {
    const inputs = document.querySelectorAll('.guest-name-input');
    let allValid = true;
    
    inputs.forEach(input => {
        if (!input.value.trim()) {
            input.focus();
            input.style.borderColor = '#ef4444';
            setTimeout(() => {
                input.style.borderColor = '';
            }, 2000);
            allValid = false;
        }
    });
    
    return allValid;
}

// Save customization
async function saveCustomization() {
    // Validate all guest names are filled
    if (!validateGuestNames()) {
        alert('❌ Please enter a guest name for all tickets.');
        return;
    }
    
    const totalSelected = getTotalSelected();
    
    // Prepare data to save
    const customizationData = {
        event_id: eventData.id,
        reservation_id: eventData.reservationId,
        total_tickets: eventData.totalTickets,
        customized_count: totalSelected,
        customization_cost: (totalSelected * eventData.costPerTicket).toFixed(2),
        guest_names: guestNames,
        tickets_by_category: selectedByCategory,
        event_details: {
            title: eventData.title,
            date: eventData.date,
            time: eventData.time,
            venue: eventData.venue
        }
    };
    
    try {
        // Show loading state
        const saveBtn = event.target;
        const originalText = saveBtn.textContent;
        saveBtn.textContent = 'Saving...';
        saveBtn.disabled = true;
        
        // Save to session/database
        const response = await fetch('../../public/api/save_ticket_customization.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify(customizationData)
        });
        
        const result = await response.json();
        
        if (result.success) {
            // Store in session for checkout
            sessionStorage.setItem('ticket_customization', JSON.stringify(customizationData));
            
            // Redirect back to checkout with customization data
            alert('✅ Tickets customized successfully!');
            window.location.href = `checkout.php?event_id=${eventData.id}&customized=true`;
        } else {
            throw new Error(result.message || 'Failed to save customization');
        }
    } catch (error) {
        console.error('Error saving customization:', error);
        alert('❌ Error saving customization. Please try again.');
        
        // Restore button
        event.target.textContent = originalText;
        event.target.disabled = false;
    }
}

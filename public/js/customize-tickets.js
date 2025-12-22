// Ticket Customization JavaScript - Per Category Selection
let selectedByCategory = {}; // { "Fanpit": 2, "Golden Circle": 1, "Regular": 1 }
let guestNames = {}; // { "1": "JOHN DOE", "2": "JANE SMITH" }
let ticketCounter = 0; // Global counter for ticket numbering
let alreadyCustomized = null; // Track already customized tickets
let alreadyCustomizedCount = 0; // Total count of already customized tickets

// Initialize
document.addEventListener('DOMContentLoaded', function() {
    // Check for already customized tickets from sessionStorage
    const sessionCustomization = sessionStorage.getItem('ticket_customization');
    if (sessionCustomization) {
        try {
            const customData = JSON.parse(sessionCustomization);
            // Only use if it's for the same event
            if (customData.event_id == eventData.id) {
                alreadyCustomized = customData;
                alreadyCustomizedCount = customData.customized_count || 0;
            } else {
                alreadyCustomized = null;
                alreadyCustomizedCount = 0;
            }
        } catch (e) {
            console.warn("Failed to parse already customized data:", e);
            alreadyCustomized = null;
            alreadyCustomizedCount = 0;
        }
    }
    
    // Also check URL parameter (but only if not already set from sessionStorage)
    if (alreadyCustomizedCount === 0) {
        const urlParams = new URLSearchParams(window.location.search);
        const urlAlreadyCustomized = parseInt(urlParams.get('already_customized') || 0);
        if (urlAlreadyCustomized > 0) {
            alreadyCustomizedCount = urlAlreadyCustomized;
        }
    }
    
    // Validate: alreadyCustomizedCount should not exceed totalTickets
    if (alreadyCustomizedCount > eventData.totalTickets) {
        console.warn('Invalid alreadyCustomizedCount:', alreadyCustomizedCount, 'exceeds totalTickets:', eventData.totalTickets);
        alreadyCustomizedCount = 0;
        alreadyCustomized = null;
    }
    
    // Initialize selectedByCategory with zeros (NEVER restore from alreadyCustomized - those are already done)
    if (eventData.reservationsByCategory) {
        Object.keys(eventData.reservationsByCategory).forEach(category => {
            selectedByCategory[category] = 0;
            // IMPORTANT: Don't restore from alreadyCustomized - those tickets are already customized
            // We only want to track NEW selections, not already customized ones
        });
    }
    
    // Restore guest names if available (for display only)
    if (alreadyCustomized && alreadyCustomized.guest_names) {
        guestNames = { ...alreadyCustomized.guest_names };
    }
    
    
    // Attach event listeners to category buttons
    attachCategoryListeners();
    updateDisplay();
    
    // Render already customized tickets if any
    if (alreadyCustomizedCount > 0 && alreadyCustomized && alreadyCustomized.guest_names) {
        renderAlreadyCustomizedTickets();
    }
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
    const alreadyCustomizedInCategory = (alreadyCustomized && alreadyCustomized.tickets_by_category && alreadyCustomized.tickets_by_category[category]) ? alreadyCustomized.tickets_by_category[category] : 0;
    
    // Max is total minus already customized in this category
    const max = categoryData.total - alreadyCustomizedInCategory;
    
    // Also check total customized count
    const totalSelected = getTotalSelected();
    const totalAvailable = Math.max(0, eventData.totalTickets - alreadyCustomizedCount);
    
    // Validate: ensure we don't exceed total available
    if (totalAvailable <= 0) {
        alert(`All ${eventData.totalTickets} ticket(s) have already been customized.`);
        return;
    }
    
    if (totalSelected >= totalAvailable) {
        alert(`You can only customize ${totalAvailable} more ticket(s). ${alreadyCustomizedCount} ticket(s) are already customized.`);
        return;
    }
    
    if (current < max) {
        selectedByCategory[category] = current + 1;
        updateCategoryDisplay(category);
        updateDisplay();
    } else {
        alert(`Maximum ${max} ticket(s) available to customize in this category. ${alreadyCustomizedInCategory} ticket(s) are already customized.`);
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
        const alreadyCustomizedInCategory = (alreadyCustomized && alreadyCustomized.tickets_by_category && alreadyCustomized.tickets_by_category[category]) ? alreadyCustomized.tickets_by_category[category] : 0;
        const max = categoryData.total - alreadyCustomizedInCategory;
        
        // Also check total available across all categories
        const totalSelected = getTotalSelected();
        const totalAvailable = Math.max(0, eventData.totalTickets - alreadyCustomizedCount);
        
        const increaseBtn = document.querySelector(`.category-increase[data-category="${category}"]`);
        const decreaseBtn = document.querySelector(`.category-decrease[data-category="${category}"]`);
        
        // Disable increase if: at max for category OR total selected >= total available OR total available is 0
        if (increaseBtn) {
            increaseBtn.disabled = current >= max || totalSelected >= totalAvailable || totalAvailable <= 0;
        }
        if (decreaseBtn) {
            decreaseBtn.disabled = current <= 0;
        }
    }
}

// Calculate total selected tickets
function getTotalSelected() {
    return Object.values(selectedByCategory).reduce((sum, qty) => sum + qty, 0);
}

// Update overall display
function updateDisplay() {
    const totalSelected = getTotalSelected();
    const totalAvailable = eventData.totalTickets - alreadyCustomizedCount;
    
    // Show remaining tickets available to customize
    const customizeCountEl = document.getElementById('customize-count');
    if (customizeCountEl) {
        customizeCountEl.textContent = totalSelected;
    }
    
    // Update info text to show remaining tickets
    const infoText = document.querySelector('.selection-card p');
    if (infoText) {
        if (alreadyCustomizedCount > 0) {
            infoText.textContent = `Each customized ticket costs $${eventData.costPerTicket.toFixed(2)} extra. ${alreadyCustomizedCount} ticket(s) already customized. ${totalAvailable} ticket(s) remaining.`;
        } else {
            infoText.textContent = `Each customized ticket costs $${eventData.costPerTicket.toFixed(2)} extra. You can customize up to ${eventData.totalTickets} ticket(s).`;
        }
    }
    
    // Update remaining tickets info
    const remainingTicketsEl = document.getElementById('remaining-tickets-count');
    if (remainingTicketsEl) {
        const remaining = Math.max(0, totalAvailable); // Don't show negative
        remainingTicketsEl.textContent = remaining;
        if (totalAvailable < 0) {
            remainingTicketsEl.style.color = '#ef4444'; // Red if negative (error state)
            console.error('Invalid remaining tickets calculation:', totalAvailable, 'totalTickets:', eventData.totalTickets, 'alreadyCustomizedCount:', alreadyCustomizedCount);
        } else if (totalAvailable === 0) {
            remainingTicketsEl.style.color = '#f59e0b'; // Orange if none remaining
        } else {
            remainingTicketsEl.style.color = '#22c55e'; // Green
        }
    }
    
    const totalCost = (totalSelected * eventData.costPerTicket).toFixed(2);
    const totalCostEl = document.getElementById('total-cost');
    if (totalCostEl) {
        totalCostEl.textContent = totalCost;
    }
    
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
    
    // Merge with existing customization if any
    let finalGuestNames = { ...guestNames };
    let finalTicketsByCategory = { ...selectedByCategory };
    let finalCustomizedCount = totalSelected;
    
    if (alreadyCustomized) {
        // Merge guest names (new ones override old ones if same ticket number)
        finalGuestNames = { ...alreadyCustomized.guest_names, ...guestNames };
        
        // Merge tickets by category (add new selections to existing)
        Object.keys(alreadyCustomized.tickets_by_category || {}).forEach(cat => {
            if (!finalTicketsByCategory[cat]) {
                finalTicketsByCategory[cat] = 0;
            }
            finalTicketsByCategory[cat] += alreadyCustomized.tickets_by_category[cat];
        });
        
        // Add already customized count
        finalCustomizedCount += alreadyCustomized.customized_count;
    }
    
    // Prepare data to save
    const customizationData = {
        event_id: eventData.id,
        reservation_id: eventData.reservationId,
        total_tickets: eventData.totalTickets,
        customized_count: finalCustomizedCount,
        customization_cost: (finalCustomizedCount * eventData.costPerTicket).toFixed(2),
        guest_names: finalGuestNames,
        tickets_by_category: finalTicketsByCategory,
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
        const response = await fetch('/api/save_ticket_customization.php', {
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
            
            // Store customization cost in sessionStorage for checkout
            sessionStorage.setItem('customization_fee', customizationData.customization_cost);
            
            // Get reservation IDs from eventData or build from reservationsByCategory
            let reservationIds = [];
            if (eventData.reservationIds && Array.isArray(eventData.reservationIds)) {
                reservationIds = eventData.reservationIds;
            } else if (eventData.reservationId) {
                reservationIds = [eventData.reservationId];
            } else if (eventData.reservationsByCategory) {
                // Build reservation IDs from all categories
                Object.values(eventData.reservationsByCategory).forEach(catData => {
                    if (catData.reservation_ids && Array.isArray(catData.reservation_ids)) {
                        reservationIds.push(...catData.reservation_ids);
                    }
                });
            }
            
            // Redirect back to checkout with reservation IDs to preserve order
            let redirectUrl = `checkout.php?event_id=${eventData.id}`;
            if (reservationIds.length > 0) {
                redirectUrl += `&reservations=${reservationIds.join(',')}`;
            }
            redirectUrl += `&customized=true`;
            
            alert('✅ Tickets customized successfully!');
            window.location.href = redirectUrl;
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

// Render already customized tickets
function renderAlreadyCustomizedTickets() {
    if (!alreadyCustomized || !alreadyCustomized.guest_names) return;
    
    const container = document.getElementById('tickets-container');
    if (!container) return;
    
    // Create a section for already customized tickets
    const existingSection = document.getElementById('already-customized-section');
    if (existingSection) {
        existingSection.remove();
    }
    
    const section = document.createElement('div');
    section.id = 'already-customized-section';
    section.style.cssText = 'margin-bottom: 30px; padding: 20px; background: rgba(34, 197, 94, 0.1); border: 2px solid rgba(34, 197, 94, 0.3); border-radius: 12px;';
    
    const title = document.createElement('h3');
    title.textContent = `✅ ${alreadyCustomizedCount} Ticket(s) Already Customized`;
    title.style.cssText = 'color: #22c55e; margin-bottom: 15px; font-size: 18px;';
    section.appendChild(title);
    
    const info = document.createElement('p');
    info.textContent = `You can customize ${eventData.totalTickets - alreadyCustomizedCount} more ticket(s).`;
    info.style.cssText = 'color: #9ca3af; margin-bottom: 15px;';
    section.appendChild(info);
    
    // List customized tickets
    Object.keys(alreadyCustomized.guest_names).forEach(ticketNum => {
        const guestName = alreadyCustomized.guest_names[ticketNum];
        const ticketDiv = document.createElement('div');
        ticketDiv.style.cssText = 'padding: 10px; margin: 5px 0; background: rgba(34, 197, 94, 0.05); border-radius: 6px;';
        ticketDiv.textContent = `Ticket #${ticketNum}: ${guestName}`;
        section.appendChild(ticketDiv);
    });
    
    container.insertBefore(section, container.firstChild);
}

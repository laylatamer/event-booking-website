// Initialize Vanta.js background (only if element exists and VANTA is available)
// Wait for DOM to be ready and VANTA to be fully loaded
if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', initVanta);
} else {
    // DOM already loaded, wait a bit for VANTA to be available
    setTimeout(initVanta, 100);
}

function initVanta() {
    // Check if THREE.js is loaded first (required by VANTA)
    if (typeof THREE === 'undefined') {
        // THREE.js not loaded yet, try again
        setTimeout(initVanta, 200);
        return;
    }
    
    if (typeof VANTA === 'undefined') {
        // VANTA not loaded yet, try again
        setTimeout(initVanta, 200);
        return;
    }
    
    const vantaElement = document.getElementById('vanta-bg');
    if (vantaElement) {
        try {
            VANTA.NET({
                el: "#vanta-bg",
                THREE: THREE, // Explicitly pass THREE.js
                color: 0xf97316,
                backgroundColor: 0x1a1a1a,
                points: 12,
                maxDistance: 20,
                spacing: 15
            });
        } catch (error) {
            console.warn('VANTA.js initialization failed:', error);
        }
    }
}

// Initialize feather icons
feather.replace();

// Read 'eventId' and 'quantity' parameters from the current page's URL
const urlParams = new URLSearchParams(window.location.search);
// Support both 'eventId' and 'event_id' formats
const urlEventId = parseInt(urlParams.get('eventId') || urlParams.get('event_id'));
// Support both 'quantity' and 'reservations' formats
const reservationsParam = urlParams.get('reservations');

// Initialize orderItems as an empty array
let orderItems = [];
let events = []; // Will be populated from API
// Initialize default date/time variables (will be updated if event found)
let formattedDate = 'N/A';
let formattedTime = 'N/A';
// Flag to prevent double submissions
let isBookingInProgress = false;
// Store max tickets limit from event
let maxTicketsPerBooking = null;

// Fetch real event data from API and initialize order
async function initializeCheckout() {
    try {
        // Always fetch fresh data from reservations if available in URL
        // Don't restore from sessionStorage if we have reservation IDs in URL
        const shouldRestoreFromStorage = !reservationsParam && urlEventId;
        
        if (shouldRestoreFromStorage) {
            // Check if we're returning from customization - try to restore orderItems from sessionStorage
            const savedOrderItems = sessionStorage.getItem('checkout_orderItems');
            const savedEvents = sessionStorage.getItem('checkout_events');
            
            if (savedOrderItems && savedEvents) {
                try {
                    orderItems = JSON.parse(savedOrderItems);
                    events = JSON.parse(savedEvents);
                    
                    // Verify the event ID matches
                    if (events.length > 0 && events[0].id === urlEventId) {
                        // Restore successful - render immediately
                        const eventDate = new Date(events[0].date);
                        formattedDate = eventDate.toLocaleDateString('en-US', { weekday: 'long', year: 'numeric', month: 'long', day: 'numeric' });
                        formattedTime = eventDate.toLocaleTimeString('en-US', { hour: 'numeric', minute: '2-digit' });
                        
                        renderOrderItems();
                        calculateTotals();
                        setupEventListeners();
                        setupInputHighlights();
                        setTimeout(updateTicketsAvailable, 100);
                        return; // Exit early - we've restored from sessionStorage
                    }
                } catch (e) {
                    console.warn("Failed to restore from sessionStorage, will reload:", e);
                }
            }
        }
        
        // Fetch event data from API
        if (urlEventId) {
            const response = await fetch(`/api/events_API.php?action=getEvent&id=${urlEventId}`);
            const data = await response.json();
            
            if (data.success && data.event) {
                const currentEvent = data.event;
                const ticketCategories = data.event.ticket_categories || [];
                
                // Store max tickets limit
                maxTicketsPerBooking = currentEvent.max_tickets_per_booking || null;
                
                currentEvent.location = currentEvent.location || currentEvent.venue || 'TBA';
                events = [currentEvent]; // Add to events array
                
                // Build orderItems dynamically from reservations with correct category prices
                orderItems = [];
                
                if (reservationsParam) {
                    // First, try to extend reservations to give more time
                    try {
                        // The PHP side should have already extended them, but we can also try here
                        // This is just a safety measure - the main extension happens in checkout.php
                    } catch (e) {
                        console.warn('Could not extend reservations:', e);
                    }
                    
                    // Fetch actual reservations with category details
                    const reservationIds = reservationsParam.split(',').filter(id => id.trim());
                    
                    if (reservationIds.length === 0) {
                        console.warn('No valid reservation IDs found in URL');
                    }
                    
                    // Group reservations by category
                    const categoryMap = {};
                    let foundReservations = 0;
                    let expiredReservations = 0;
                    
                    // Fetch all reservations in parallel for better performance
                    const reservationPromises = reservationIds.map(async (resId) => {
                        try {
                            const resResponse = await fetch(`/api/ticket_reservations.php?action=getReservation&id=${resId}`);
                            if (resResponse.ok) {
                                const resData = await resResponse.json();
                                if (resData.success && resData.reservation) {
                                    return { success: true, id: resId, reservation: resData.reservation };
                                } else {
                                    return { success: false, id: resId, error: resData.message || 'Unknown error' };
                                }
                            } else {
                                return { success: false, id: resId, error: `HTTP ${resResponse.status}`, status: resResponse.status };
                            }
                        } catch (e) {
                            return { success: false, id: resId, error: e.message };
                        }
                    });
                    
                    const reservationResults = await Promise.all(reservationPromises);
                    
                    for (const result of reservationResults) {
                        if (result.success && result.reservation) {
                            foundReservations++;
                            const reservation = result.reservation;
                            
                            // Check if reservation is expired
                            // Give a 5 second grace period to account for timing issues
                            let expiresAt = null;
                            if (reservation.expires_at) {
                                // Parse the expiration date - handle both ISO format and MySQL datetime format
                                const expiresAtStr = reservation.expires_at;
                                // If it's in MySQL format (YYYY-MM-DD HH:MM:SS), treat it as UTC
                                if (expiresAtStr.match(/^\d{4}-\d{2}-\d{2} \d{2}:\d{2}:\d{2}$/)) {
                                    // MySQL datetime format - treat as UTC by appending 'Z'
                                    // This prevents timezone conversion issues
                                    expiresAt = new Date(expiresAtStr.replace(' ', 'T') + 'Z');
                                } else {
                                    // ISO format or other - let Date parse it
                                    expiresAt = new Date(expiresAtStr);
                                }
                            }
                            
                            const now = new Date();
                            const gracePeriod = 5000; // 5 seconds in milliseconds
                            
                            // Reservation is expired if: it's marked as expired OR expires_at is more than gracePeriod in the past
                            // Correct logic: expiresAt < (now - gracePeriod) means it expired more than gracePeriod ago
                            const isExpired = reservation.is_expired || (expiresAt && expiresAt.getTime() < (now.getTime() - gracePeriod));
                            
                            if (isExpired) {
                                expiredReservations++;
                                console.warn(`Reservation ${result.id} has expired (expires_at: ${reservation.expires_at}, parsed: ${expiresAt ? expiresAt.toISOString() : 'null'}, now: ${now.toISOString()}, gracePeriod: ${gracePeriod}ms)`);
                                continue;
                            }
                            
                            const categoryName = reservation.category_name;
                            const quantity = parseInt(reservation.quantity) || 0;
                            
                            if (!categoryName || quantity <= 0) {
                                console.warn(`Invalid reservation data for ID ${result.id}:`, reservation);
                                continue;
                            }
                            
                            // Debug: Log available categories and what we're looking for
                            console.log(`Looking for category "${categoryName}" in ticket categories:`, ticketCategories.map(c => c.category_name || c.name));
                            console.log(`Full ticket categories array:`, ticketCategories);
                            
                            // Find the price for this category
                            // Try both category_name and name fields, with case-insensitive matching
                            const category = ticketCategories.find(cat => {
                                const catName = cat.category_name || cat.name || '';
                                return catName.toLowerCase().trim() === categoryName.toLowerCase().trim();
                            });
                            
                            if (!category) {
                                console.error(`Category "${categoryName}" not found in ticket categories. Available categories:`, ticketCategories.map(c => ({
                                    category_name: c.category_name,
                                    name: c.name,
                                    price: c.price
                                })));
                                // Still add to categoryMap with price 0 so we can see it in the UI
                                // This will help debug the issue
                            }
                            
                            const categoryPrice = category ? parseFloat(category.price || category.price_per_ticket || 0) : 0;
                            
                            // Always add to categoryMap, even if category not found (for debugging)
                            if (!categoryMap[categoryName]) {
                                categoryMap[categoryName] = {
                                    categoryName: categoryName,
                                    quantity: 0,
                                    price: categoryPrice
                                };
                            }
                            categoryMap[categoryName].quantity += quantity;
                            
                            console.log(`Added reservation to categoryMap:`, {
                                categoryName: categoryName,
                                quantity: categoryMap[categoryName].quantity,
                                price: categoryPrice,
                                foundCategory: !!category
                            });
                        } else {
                            // Log the error but don't fail immediately
                            if (result.status === 404) {
                                console.warn(`Reservation ${result.id} not found (may have expired or been deleted)`);
                            } else {
                                console.warn(`Failed to get reservation ${result.id}:`, result.error);
                            }
                        }
                    }
                    
                    // If no valid reservations were found, show a helpful message
                    // But wait a moment - the extension might still be processing
                    if (foundReservations === 0 && reservationIds.length > 0) {
                        // Wait longer and try once more - extension might need time
                        console.warn('No reservations found on first try, waiting 1 second and retrying...');
                        await new Promise(resolve => setTimeout(resolve, 1000));
                        
                        // Retry fetching reservations
                        const retryPromises = reservationIds.map(async (resId) => {
                            try {
                                const resResponse = await fetch(`/api/ticket_reservations.php?action=getReservation&id=${resId}`);
                                if (resResponse.ok) {
                                    const resData = await resResponse.json();
                                    if (resData.success && resData.reservation) {
                                        return { success: true, id: resId, reservation: resData.reservation };
                                    }
                                }
                            } catch (e) {
                                // Ignore retry errors
                            }
                            return { success: false, id: resId };
                        });
                        
                        const retryResults = await Promise.all(retryPromises);
                        const retryFound = retryResults.filter(r => r.success && r.reservation).length;
                        
                        if (retryFound === 0) {
                            console.error('All reservations expired or not found after retry. Redirecting to booking page...');
                            alert('Your ticket reservations have expired. Please select your tickets again.');
                            // Use urlEventId or currentEvent.id if available
                            const redirectEventId = urlEventId || (currentEvent ? currentEvent.id : null);
                            if (redirectEventId) {
                                window.location.href = `booking.php?id=${redirectEventId}`;
                            } else {
                                window.location.href = '/';
                            }
                            return;
                        } else {
                            // Found some on retry - process them
                            console.log(`Found ${retryFound} reservations on retry`);
                            for (const result of retryResults) {
                                if (result.success && result.reservation) {
                                    foundReservations++;
                                    const reservation = result.reservation;
                                    
                                    // Check if reservation is expired (with grace period)
                                    const expiresAt = reservation.expires_at ? new Date(reservation.expires_at) : null;
                                    const now = new Date();
                                    const gracePeriod = 5000; // 5 seconds in milliseconds
                                    const isExpired = reservation.is_expired || (expiresAt && expiresAt.getTime() < (now.getTime() - gracePeriod));
                                    
                                    if (isExpired) {
                                        expiredReservations++;
                                        continue;
                                    }
                                    
                                    const categoryName = reservation.category_name;
                                    const quantity = parseInt(reservation.quantity) || 0;
                                    
                                    if (!categoryName || quantity <= 0) {
                                        continue;
                                    }
                                    
                                    // Find the price for this category
                                    const category = ticketCategories.find(cat => cat.category_name === categoryName);
                                    const categoryPrice = category ? parseFloat(category.price) : 0;
                                    
                                    if (!categoryMap[categoryName]) {
                                        categoryMap[categoryName] = {
                                            categoryName: categoryName,
                                            quantity: 0,
                                            price: categoryPrice
                                        };
                                    }
                                    categoryMap[categoryName].quantity += quantity;
                                }
                            }
                        }
                    }
                    
                    // If some reservations expired but we have valid ones, continue
                    if (expiredReservations > 0 && foundReservations > 0) {
                        console.warn(`${expiredReservations} reservation(s) expired, but ${foundReservations} are still valid. Continuing with valid reservations.`);
                    }
                    
                    // Convert categoryMap to orderItems
                    Object.keys(categoryMap).forEach(categoryName => {
                        const catData = categoryMap[categoryName];
                        if (catData.quantity > 0) {
                            orderItems.push({
                                eventId: currentEvent.id,
                                quantity: catData.quantity,
                                categoryName: categoryName,
                                price: catData.price,
                                ticketType: categoryName
                            });
                        }
                    });
                    
                    // Verify we got all reservations
                    if (Object.keys(categoryMap).length === 0 && reservationIds.length > 0) {
                        console.error('WARNING: No categories found despite having reservation IDs!');
                        console.error('This might indicate reservations are missing category_name or are expired.');
                    }
                } else {
                    // Fallback: use quantity parameter if no reservations
                    const urlQuantity = parseInt(urlParams.get('quantity')) || 0;
                    if (urlQuantity > 0) {
                        // Use average price if no specific category
                        let basePrice = 0;
                        if (ticketCategories.length > 0) {
                            const totalPrice = ticketCategories.reduce((sum, cat) => sum + parseFloat(cat.price), 0);
                            basePrice = totalPrice / ticketCategories.length;
                        } else {
                            basePrice = 50.00;
                        }
                        
                        orderItems = [{
                            eventId: currentEvent.id,
                            quantity: urlQuantity,
                            categoryName: ticketCategories.length > 0 ? ticketCategories[0].category_name : 'General',
                            price: basePrice,
                            ticketType: "Standard Ticket"
                        }];
                    }
                }
                
                if (orderItems.length > 0) {
                    // Save to sessionStorage for restoration
                    sessionStorage.setItem('checkout_orderItems', JSON.stringify(orderItems));
                    sessionStorage.setItem('checkout_events', JSON.stringify(events));
                    
                    // Format date/time
                    const eventDate = new Date(currentEvent.date);
                    formattedDate = eventDate.toLocaleDateString('en-US', { weekday: 'long', year: 'numeric', month: 'long', day: 'numeric' });
                    formattedTime = eventDate.toLocaleTimeString('en-US', { hour: 'numeric', minute: '2-digit' });
                    
                    // Render the page
                    renderOrderItems();
                    calculateTotals();
                    setupEventListeners();
                    setupInputHighlights();
                    
                    // Update ticket customization count
                    setTimeout(updateTicketsAvailable, 100);
                    setTimeout(updateTicketsAvailable, 500);
                } else {
                    console.error("No valid order items found");
                }
            } else {
                console.error("Failed to load event data");
            }
        } else {
            console.error("No event ID in URL");
        }
    } catch (error) {
        console.error("Error initializing checkout:", error);
    }
}

// Call initialization
initializeCheckout();


// --- Helper functions for validation ---

function showError(fieldId, message) {
    const errorField = document.getElementById(fieldId);
    if (errorField) {
        errorField.textContent = message;
        errorField.classList.add('chk-form-field__error--visible');
    }
}

function hideError(fieldId) {
    const errorField = document.getElementById(fieldId);
    if (errorField) {
        errorField.textContent = '';
        errorField.classList.remove('chk-form-field__error--visible');
    }
}


function clearErrors() {
    const errorFields = document.querySelectorAll('.chk-form-field__error');
    errorFields.forEach(field => {
        field.textContent = '';
        field.classList.remove('chk-form-field__error--visible');
    });
}

// --- Page initialization is now handled by initializeCheckout() function above ---

// Set up input highlight effects
function setupInputHighlights() {
    const inputs = [
        'firstName', 'lastName', 'email', 'phone',
        'cardNumber', 'cardHolder', 'cardExpiry', 'cardCVV'
    ];
    inputs.forEach(inputId => {
        const input = document.getElementById(inputId);
        const container = input ? input.closest('.chk-form-field') : null; // Find parent container
        if (input && container) {
            input.addEventListener('focus', () => {
                container.classList.add('active');
            });
            input.addEventListener('blur', () => {
                container.classList.remove('active');
            });
        }
    });
}

// Render order items in the summary
function renderOrderItems() {
    const orderItemsContainer = document.getElementById('orderItems');
    orderItemsContainer.innerHTML = ''; // Always clear previous items first

    // Check if the orderItems array is empty or invalid
    if (!orderItems || orderItems.length === 0) {
        orderItemsContainer.innerHTML = '<p style="color: #9ca3af;">No items in your order.</p>'; // Display a message, added inline style for clarity
        return; // Stop the function here if no items
    }

    // Loop through the items received from the URL (or modified by +/- buttons)
    orderItems.forEach(item => {
        // Find the full event details using the item's eventId
        const eventItem = events.find(e => e.id === item.eventId);
        if (eventItem) {
            // Format date/time specifically for this item being rendered
            const itemEventDate = new Date(eventItem.date);
            const itemFormattedDate = itemEventDate.toLocaleDateString('en-US', { weekday: 'long', year: 'numeric', month: 'long', day: 'numeric' });
            const itemFormattedTime = itemEventDate.toLocaleTimeString('en-US', { hour: 'numeric', minute: '2-digit' });

            // Use item price if available (from category), otherwise use event price
            const itemPrice = item.price || eventItem.price || 0;
            const ticketType = item.ticketType || item.categoryName || 'Standard Ticket';

            // Create the HTML for the order item
            const itemElement = document.createElement('div');
            itemElement.className = 'chk-order-item';
            itemElement.innerHTML = `
                <div class="chk-order-item__icon-wrapper">
                    <i class="fas fa-ticket-alt chk-order-item__icon"></i>
                </div>
                <div class="chk-order-item__info">
                    <h3 class="chk-order-item__title">${eventItem.title}</h3>
                    <p class="chk-order-item__detail">${itemFormattedDate} ${itemFormattedTime}</p>
                    <p class="chk-order-item__detail">${eventItem.location}</p>
                    <div class="chk-quantity-control">
                        <button class="chk-quantity-control__button chk-quantity-btn decrease" data-id="${eventItem.id}" data-category="${item.categoryName || ''}" ${item.quantity <= 1 ? 'disabled style="opacity: 0.5; cursor: not-allowed;"' : ''}>
                            <i class="fas fa-minus"></i>
                        </button>
                        <span class="chk-quantity-control__display">${item.quantity}</span>
                        <button class="chk-quantity-control__button chk-quantity-btn increase" data-id="${eventItem.id}" disabled style="opacity: 0.5; cursor: not-allowed;" title="Cannot increase tickets in checkout">
                            <i class="fas fa-plus"></i>
                        </button>
                    </div>
                </div>
                <div class="chk-order-item__price-section">
                    <p class="chk-order-item__price">$${(itemPrice * item.quantity).toFixed(2)}</p>
                    <p class="chk-order-item__ticket-type">${ticketType}</p>
                </div>
            `;
            // Add the created item HTML to the page
            orderItemsContainer.appendChild(itemElement);
        }
    });

    // IMPORTANT: Re-attach listeners to the NEW +/- buttons after creating them
    // NOTE: Increase button is disabled in checkout - users cannot add more tickets
    document.querySelectorAll('.chk-quantity-btn.increase').forEach(btn => {
        // Keep button disabled - no click handler needed
        btn.disabled = true;
        btn.style.opacity = '0.5';
        btn.style.cursor = 'not-allowed';
        btn.title = 'Cannot increase tickets in checkout';
    });
    document.querySelectorAll('.chk-quantity-btn.decrease').forEach(btn => {
        btn.addEventListener('click', function() {
            const eventId = parseInt(this.getAttribute('data-id'));
            const categoryName = this.getAttribute('data-category') || null;
            decreaseQuantity(eventId, categoryName);
        });
    });
    
    // Update ticket customization count after rendering
    if (typeof updateTicketsAvailable === 'function') {
        updateTicketsAvailable();
    }
}


// Calculate order totals
function calculateTotals() {
    let subtotal = 0;
    
    // Calculate subtotal from orderItems using actual item prices
    orderItems.forEach(item => {
        // Use item.price if available (from category), otherwise fallback to event price
        const itemPrice = item.price || 0;
        subtotal += itemPrice * item.quantity;
    });

    // Get customization fee from multiple sources - ONLY if tickets were actually customized
    let customizationFee = 0;
    let customizedCount = 0;
    
    // Try sessionStorage first (from customize-tickets.js)
    const sessionCustomization = sessionStorage.getItem('ticket_customization');
    if (sessionCustomization) {
        try {
            const customData = JSON.parse(sessionCustomization);
            customizedCount = parseInt(customData.customized_count || 0);
            // Only add fee if tickets were actually customized AND it's for the current event
            const customEventId = parseInt(customData.event_id || 0);
            if (customizedCount > 0 && customEventId === urlEventId && urlEventId > 0) {
                customizationFee = parseFloat(customData.customization_cost || 0);
            } else if (customEventId !== urlEventId && urlEventId > 0) {
                // Clear old customization data for different event
                sessionStorage.removeItem('ticket_customization');
                sessionStorage.removeItem('customization_fee');
            } else if (customizedCount === 0) {
                // Clear if no tickets were actually customized
                sessionStorage.removeItem('ticket_customization');
                sessionStorage.removeItem('customization_fee');
            }
        } catch (e) {
            console.warn("Failed to parse sessionStorage customization:", e);
            // Clear invalid data
            sessionStorage.removeItem('ticket_customization');
            sessionStorage.removeItem('customization_fee');
        }
    }
    
    // Try window.customizationData (from PHP session)
    if (customizedCount === 0 && window.customizationData) {
        customizedCount = parseInt(window.customizationData.customized_count || 0);
        // Only use if it's for the current event and tickets were actually customized
        const phpEventId = parseInt(window.customizationData.event_id || 0);
        if (customizedCount > 0 && phpEventId === urlEventId && urlEventId > 0) {
            customizationFee = parseFloat(window.customizationData.customization_cost || 0);
        }
    }
    
    // Calculate fees
    const serviceFee = orderItems.length * 5.99;
    const processingFee = subtotal * 0.03;
    const total = subtotal + serviceFee + processingFee + customizationFee;

    // Use querySelector for safer element finding, fallback to '0.00'
    const subtotalEl = document.getElementById('subtotal');
    const serviceFeeEl = document.getElementById('service-fee');
    const processingFeeEl = document.getElementById('processing-fee');
    const customizationFeeEl = document.getElementById('customization-fee');
    const customizationFeeRow = document.getElementById('customization-fee-row');
    const totalEl = document.getElementById('total');
    const placeOrderBtn = document.getElementById('placeOrderBtn');

    if (subtotalEl) subtotalEl.textContent = `$${subtotal.toFixed(2)}`;
    if (serviceFeeEl) serviceFeeEl.textContent = `$${serviceFee.toFixed(2)}`;
    if (processingFeeEl) processingFeeEl.textContent = `$${processingFee.toFixed(2)}`;
    if (customizationFeeEl) customizationFeeEl.textContent = `$${customizationFee.toFixed(2)}`;
    if (customizationFeeRow) {
        customizationFeeRow.style.display = customizationFee > 0 ? 'flex' : 'none';
    }
    if (totalEl) totalEl.textContent = `$${total.toFixed(2)}`;

    // Update place order button with total
    if(placeOrderBtn){
        const isCash = document.getElementById('cashOption')?.classList.contains('chk-payment-method--active');
        if (isCash) {
            placeOrderBtn.innerHTML = `<span>Reserve Tickets ($${total.toFixed(2)})</span><i class="fas fa-arrow-right chk-button__icon"></i>`;
        } else {
            placeOrderBtn.innerHTML = `<span>Pay $${total.toFixed(2)}</span><i class="fas fa-arrow-right chk-button__icon"></i>`;
        }
    }
}

// Increase item quantity (Modify global array and re-render)
function increaseQuantity(eventId) {
    // Find the item in our global orderItems array
    const item = orderItems.find(i => i.eventId === eventId);
    if (item) {
        // Calculate total tickets across all items
        const totalTickets = orderItems.reduce((sum, i) => sum + i.quantity, 0);
        
        // Check max tickets limit if set
        if (maxTicketsPerBooking !== null && totalTickets >= maxTicketsPerBooking) {
            alert(`Maximum ${maxTicketsPerBooking} tickets per booking. You cannot add more tickets.`);
            return;
        }
        
        // Increase its quantity
        item.quantity++;
        // Save to sessionStorage
        sessionStorage.setItem('checkout_orderItems', JSON.stringify(orderItems));
        // Re-run the functions to update the display and totals
        renderOrderItems();
        calculateTotals();
        // Update ticket customization count
        if (typeof updateTicketsAvailable === 'function') {
            updateTicketsAvailable();
        }
    }
}

// Decrease item quantity (Modify global array and re-render)
function decreaseQuantity(eventId, categoryName) {
    // Find the item - if categoryName is provided, match by both eventId and categoryName
    let item;
    if (categoryName) {
        item = orderItems.find(i => i.eventId === eventId && i.categoryName === categoryName);
    } else {
        // Fallback: find first item with matching eventId
        item = orderItems.find(i => i.eventId === eventId);
    }
    
    // Only decrease if quantity is more than 1
    if (item && item.quantity > 1) {
        // Decrease its quantity
        item.quantity--;
        // Save to sessionStorage
        sessionStorage.setItem('checkout_orderItems', JSON.stringify(orderItems));
        // Re-run the functions to update the display and totals
        renderOrderItems();
        calculateTotals();
        // Update ticket customization count
        if (typeof updateTicketsAvailable === 'function') {
            updateTicketsAvailable();
        }
    } else if (item && item.quantity === 1) {
        // Remove item if quantity becomes 0 (but we don't allow going below 1)
        // Just keep it at 1
    }
}


// Set up all event listeners
function setupEventListeners() {
    const creditCardOption = document.getElementById('creditCardOption');
    const cashOption = document.getElementById('cashOption');
    const creditCardForm = document.getElementById('creditCardForm');
    const cashForm = document.getElementById('cashForm');
    const paymentSection = document.getElementById('paymentSection');

    // Payment method toggle
    if (creditCardOption && cashOption && creditCardForm && cashForm && paymentSection) {
        creditCardOption.addEventListener('click', () => {
            creditCardOption.classList.add('chk-payment-method--active');
            cashOption.classList.remove('chk-payment-method--active');
            creditCardForm.classList.remove('chk-hidden');
            cashForm.classList.add('chk-hidden');
            calculateTotals();
            paymentSection.classList.add('active');
            setTimeout(() => { paymentSection.classList.remove('active'); }, 500);
        });

        cashOption.addEventListener('click', () => {
            cashOption.classList.add('chk-payment-method--active');
            creditCardOption.classList.remove('chk-payment-method--active');
            cashForm.classList.remove('chk-hidden');
            creditCardForm.classList.add('chk-hidden');
            calculateTotals();
            paymentSection.classList.add('active');
            setTimeout(() => { paymentSection.classList.remove('active'); }, 500);
        });
    }


    // Card formatting and filtering (Ensure elements exist)
    const cardNumberInput = document.getElementById('cardNumber');
    const cardHolderInput = document.getElementById('cardHolder');
    const cardExpiryInput = document.getElementById('cardExpiry');
    const cardCVVInput = document.getElementById('cardCVV');

    if (cardNumberInput) cardNumberInput.addEventListener('input', formatCardNumber);
    if (cardHolderInput) cardHolderInput.addEventListener('input', formatCardHolder);
    if (cardExpiryInput) cardExpiryInput.addEventListener('input', formatCardExpiry);
    if (cardCVVInput) cardCVVInput.addEventListener('input', formatCardCVV);

    // FIX: Add event listeners for card flipping here instead of using inline HTML attributes
    // 1. CVV Input: Focus to flip card to back
    if (cardCVVInput) {
        cardCVVInput.addEventListener('focus', () => flipCard(true));
        cardCVVInput.addEventListener('blur', () => flipCard(false));
    }
    
    // 2. Other Card Inputs: Focus to flip card to front
    // This is the main fix. When any other card field is focused (or blurred)
    // the card should flip back to the front view.
    const otherCardFields = [cardNumberInput, cardHolderInput, cardExpiryInput];
    otherCardFields.forEach(input => {
        if (input) {
            // When focusing on a non-CVV field, flip card to front (false)
            input.addEventListener('focus', () => flipCard(false));
        }
    });
    
    // NOTE: The blur event on these fields is now redundant because focusing on
    // any of them flips the card to the front, and blurring the CVV field
    // handles flipping back from the CVV field. The focus listeners above are enough.


    // Place Order Button Logic
    const placeOrderBtn = document.getElementById('placeOrderBtn');
    if (placeOrderBtn) {
        placeOrderBtn.addEventListener('click', () => {
            // Prevent double submissions
            if (isBookingInProgress) {
                console.log('Booking already in progress, ignoring click');
                return;
            }

            clearErrors();
            const isCash = cashOption?.classList.contains('chk-payment-method--active'); // Safe navigation

            if (!orderItems || orderItems.length === 0) {
                showNoticeModal('Empty Order', 'Please add at least one ticket to your order!');
                return;
            }

            // Validate User Info first
            const isUserInfoValid = validateUserInfoForm();

            if (isCash) {
                if (isUserInfoValid) {
                    // User info is valid, show cash warning
                    document.getElementById('cashWarningModal')?.classList.remove('chk-hidden');
                } else {
                    // User info is invalid, shake the card
                    shakeElement(document.getElementById('userInfoCard'));
                }
            } else {
                // It's a Credit Card payment
                const isCardInfoValid = validateCreditCardForm();

                if (isUserInfoValid && isCardInfoValid) {
                    // Both forms are valid, process payment
                    showPaymentSuccess();
                } else {
                    // Shake the invalid sections
                    if (!isUserInfoValid) shakeElement(document.getElementById('userInfoCard'));
                    if (!isCardInfoValid) shakeElement(document.getElementById('paymentSection'));
                }
            }
        });
    }

    // Cash Warning Modal Listeners
    const cashModal = document.getElementById('cashWarningModal');
    const confirmReservationBtn = document.getElementById('confirmReservationBtn');
    const cancelReservationBtn = document.getElementById('cancelReservationBtn');
    const cancelReservationBtnSecondary = document.getElementById('cancelReservationBtnSecondary');

    if (cashModal && confirmReservationBtn && cancelReservationBtn && cancelReservationBtnSecondary) {
        confirmReservationBtn.addEventListener('click', () => {
            // Prevent double submissions
            if (isBookingInProgress) {
                console.log('Booking already in progress, ignoring click');
                return;
            }
            cashModal.classList.add('chk-hidden');
            showReservationSuccess();
        });
        cancelReservationBtn.addEventListener('click', () => {
            cashModal.classList.add('chk-hidden');
        });
        cancelReservationBtnSecondary.addEventListener('click', () => {
            cashModal.classList.add('chk-hidden');
        });
    }

    // Notice Modal Listeners
    const noticeModal = document.getElementById('noticeModal');
    const noticeModalCloseBtn = document.getElementById('noticeModalCloseBtn');
    const noticeModalOkBtn = document.getElementById('noticeModalOkBtn');

    if (noticeModal && noticeModalCloseBtn && noticeModalOkBtn) {
        noticeModalCloseBtn.addEventListener('click', () => {
            noticeModal.classList.add('chk-hidden');
        });
        noticeModalOkBtn.addEventListener('click', () => {
            noticeModal.classList.add('chk-hidden');
        });
    }

    // Add listeners to clear errors on input
    const fieldsToWatch = [
        { inputId: 'firstName', errorId: 'firstNameError' },
        { inputId: 'lastName', errorId: 'lastNameError' },
        { inputId: 'email', errorId: 'emailError' },
        { inputId: 'phone', errorId: 'phoneError' },
        { inputId: 'cardNumber', errorId: 'cardNumberError' },
        { inputId: 'cardHolder', errorId: 'cardHolderError' },
        { inputId: 'cardExpiry', errorId: 'cardExpiryError' },
        { inputId: 'cardCVV', errorId: 'cardCVVError' },
    ];

    fieldsToWatch.forEach(field => {
        const inputElement = document.getElementById(field.inputId);
        if (inputElement) {
            // Using 'input' event to detect any change, including typing, pasting, etc.
            inputElement.addEventListener('input', () => {
                hideError(field.errorId);
            });
        }
    });
}

// --- Form Validation and Success Functions ---

/**
 * Validates the user information form.
 */
function validateUserInfoForm() {
    let isValid = true;
    const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/; // Simple email regex

    const firstName = document.getElementById('firstName')?.value ?? '';
    const lastName = document.getElementById('lastName')?.value ?? '';
    const email = document.getElementById('email')?.value ?? '';
    const phone = document.getElementById('phone')?.value ?? '';

    if (!firstName) {
        showError('firstNameError', 'First name is required');
        isValid = false;
    }
    if (!lastName) {
        showError('lastNameError', 'Last name is required');
        isValid = false;
    }
    if (!email || !emailRegex.test(email)) {
        showError('emailError', 'Enter a valid email address');
        isValid = false;
    }
    if (!phone || phone.length < 7) { // Simple phone check
        showError('phoneError', 'Enter a valid phone number');
        isValid = false;
    }

    return isValid;
}

/**
 * Validates the credit card form.
 */
function validateCreditCardForm() {
    let isValid = true;
    const cardNumber = (document.getElementById('cardNumber')?.value ?? '').replace(/\s/g, '');
    const cardHolder = document.getElementById('cardHolder')?.value ?? '';
    const cardExpiry = document.getElementById('cardExpiry')?.value ?? '';
    const cardCVV = document.getElementById('cardCVV')?.value ?? '';


    // Card Number
    if (!cardNumber || cardNumber.length < 16 || !/^\d+$/.test(cardNumber)) {
        showError('cardNumberError', 'Please enter a valid 16-digit card number');
        isValid = false;
    }

    // Card Holder
    if (!cardHolder) {
        showError('cardHolderError', 'Please enter the card holder\'s name');
        isValid = false;
    }

    // Card Expiry
    if (!/^(0[1-9]|1[0-2])\/\d{2}$/.test(cardExpiry)) {
        showError('cardExpiryError', 'Use MM/YY format');
        isValid = false;
    } else {
        const [inputMonth, inputYear] = cardExpiry.split('/').map(Number);
        const now = new Date();
        const currentMonth = now.getMonth() + 1; // 1-12
        const currentYear = now.getFullYear() % 100; // e.g., 25 (for 2025)

        // Check if card year is in the past
        // OR if it's the current year and the month is in the past
        if (inputYear < currentYear || (inputYear === currentYear && inputMonth < currentMonth)) {
            showError('cardExpiryError', 'This card is expired');
            isValid = false;
        }
    }

    // Card CVV
    if (!cardCVV || cardCVV.length < 3 || !/^\d+$/.test(cardCVV)) {
        showError('cardCVVError', 'Enter a valid 3 or 4-digit CVV');
        if (isValid) flipCard(true); // Flip only if other fields are valid
        isValid = false;
    }

    // Shake container if not valid
    if (!isValid) {
        shakeElement(document.getElementById('paymentSection'));
    }

    return isValid;
}

/**
 * Resets all forms to default state.
 */
function resetForms() {
    // Clear all text inputs
    const inputs = document.querySelectorAll('.chk-form-field__input');
    inputs.forEach(input => input.value = '');

    // Uncheck "save card"
    const saveCardCheckbox = document.getElementById('saveCard');
    if (saveCardCheckbox) saveCardCheckbox.checked = false;


    // Reset credit card visual (Check elements exist first)
    const displayCardNumber = document.getElementById('displayCardNumber');
    const displayCardHolder = document.getElementById('displayCardHolder');
    const displayCardExpiry = document.getElementById('displayCardExpiry');
    const displayCardCVV = document.getElementById('displayCardCVV');

    if(displayCardNumber) displayCardNumber.textContent = '•••• •••• •••• ••••';
    if(displayCardHolder) displayCardHolder.textContent = 'FULL NAME';
    if(displayCardExpiry) displayCardExpiry.textContent = 'MM/YY';
    if(displayCardCVV) displayCardCVV.textContent = '•••';


    // Flip card back to front
    flipCard(false);

    // Reset card brand icon
    detectCardType('');

    // Clear all error messages
    clearErrors();
}

async function showPaymentSuccess() {
    // Prevent double submissions
    if (isBookingInProgress) {
        console.log('Booking already in progress, ignoring duplicate call');
        return;
    }

    // Set flag and disable button
    isBookingInProgress = true;
    const placeOrderBtn = document.getElementById('placeOrderBtn');
    let originalText = 'Complete Purchase';
    if (placeOrderBtn) {
        const spanElement = placeOrderBtn.querySelector('span');
        originalText = spanElement?.textContent || 'Complete Purchase';
        placeOrderBtn.disabled = true;
        placeOrderBtn.style.opacity = '0.6';
        placeOrderBtn.style.cursor = 'not-allowed';
        if (spanElement) {
            spanElement.textContent = 'Processing...';
        }
    }

    const paymentSection = document.getElementById('paymentSection');
    if(paymentSection) paymentSection.classList.add('chk-animate-pulse');

    try {
        // Create booking
        const bookingResult = await createBooking('card');
        
        setTimeout(() => {
            if(paymentSection) paymentSection.classList.remove('chk-animate-pulse');
            showConfetti();
            if (bookingResult.success) {
                showNoticeModal('Payment Successful', `Your payment has been processed! Booking Code: ${bookingResult.booking_code}. Your tickets will be emailed to you shortly.`, () => {
                    // Redirect to homepage after modal is closed
                    window.location.href = '../../app/views/homepage.php';
                });
            } else {
                showNoticeModal('Payment Processed', 'Your payment has been processed! However, there was an issue saving your booking. Please contact support with your payment details.');
                // Re-enable button on error
                isBookingInProgress = false;
                if (placeOrderBtn) {
                    placeOrderBtn.disabled = false;
                    placeOrderBtn.style.opacity = '1';
                    placeOrderBtn.style.cursor = 'pointer';
                    if (placeOrderBtn.querySelector('span')) {
                        placeOrderBtn.querySelector('span').textContent = originalText;
                    }
                }
            }
            resetForms();
        }, 1000);
    } catch (error) {
        // Re-enable button on error
        isBookingInProgress = false;
        if (placeOrderBtn) {
            placeOrderBtn.disabled = false;
            placeOrderBtn.style.opacity = '1';
            placeOrderBtn.style.cursor = 'pointer';
            if (placeOrderBtn.querySelector('span')) {
                placeOrderBtn.querySelector('span').textContent = 'Complete Purchase';
            }
        }
        if(paymentSection) paymentSection.classList.remove('chk-animate-pulse');
        console.error('Error processing payment:', error);
        showNoticeModal('Payment Error', 'There was an error processing your payment. Please try again.');
    }
}

async function showReservationSuccess() {
    // Prevent double submissions
    if (isBookingInProgress) {
        console.log('Booking already in progress, ignoring duplicate call');
        return;
    }

    // Set flag and disable button
    isBookingInProgress = true;
    const placeOrderBtn = document.getElementById('placeOrderBtn');
    const confirmReservationBtn = document.getElementById('confirmReservationBtn');
    let originalText = 'Complete Purchase';
    let confirmOriginalText = 'Confirm Reservation';
    
    if (placeOrderBtn) {
        const spanElement = placeOrderBtn.querySelector('span');
        originalText = spanElement?.textContent || 'Complete Purchase';
        placeOrderBtn.disabled = true;
        placeOrderBtn.style.opacity = '0.6';
        placeOrderBtn.style.cursor = 'not-allowed';
        if (spanElement) {
            spanElement.textContent = 'Processing...';
        }
    }
    
    if (confirmReservationBtn) {
        confirmOriginalText = confirmReservationBtn.textContent.trim() || 'Confirm Reservation';
        confirmReservationBtn.disabled = true;
        confirmReservationBtn.style.opacity = '0.6';
        confirmReservationBtn.style.cursor = 'not-allowed';
        confirmReservationBtn.textContent = 'Processing...';
    }

    const paymentSection = document.getElementById('paymentSection');
    if(paymentSection) paymentSection.classList.add('chk-animate-pulse');

    try {
        // Create booking
        const bookingResult = await createBooking('cash');
        
        setTimeout(() => {
            if(paymentSection) paymentSection.classList.remove('chk-animate-pulse');
            showConfetti();
            if (bookingResult.success) {
                showNoticeModal('Reservation Confirmed', `Your reservation is confirmed! Booking Code: ${bookingResult.booking_code}. Please bring your ID to the venue.`, () => {
                    // Redirect to homepage after modal is closed
                    window.location.href = '../../app/views/homepage.php';
                });
            } else {
                showNoticeModal('Reservation Error', 'There was an issue saving your reservation. Please contact support.');
                // Re-enable buttons on error
                isBookingInProgress = false;
                if (placeOrderBtn) {
                    placeOrderBtn.disabled = false;
                    placeOrderBtn.style.opacity = '1';
                    placeOrderBtn.style.cursor = 'pointer';
                    const spanElement = placeOrderBtn.querySelector('span');
                    if (spanElement) {
                        spanElement.textContent = originalText;
                    }
                }
                if (confirmReservationBtn) {
                    confirmReservationBtn.disabled = false;
                    confirmReservationBtn.style.opacity = '1';
                    confirmReservationBtn.style.cursor = 'pointer';
                    confirmReservationBtn.textContent = confirmOriginalText;
                }
            }
            resetForms();
        }, 1000);
    } catch (error) {
        // Re-enable buttons on error
        isBookingInProgress = false;
        if (placeOrderBtn) {
            placeOrderBtn.disabled = false;
            placeOrderBtn.style.opacity = '1';
            placeOrderBtn.style.cursor = 'pointer';
            const spanElement = placeOrderBtn.querySelector('span');
            if (spanElement) {
                spanElement.textContent = originalText;
            }
        }
        if (confirmReservationBtn) {
            confirmReservationBtn.disabled = false;
            confirmReservationBtn.style.opacity = '1';
            confirmReservationBtn.style.cursor = 'pointer';
            const confirmSpanElement = confirmReservationBtn.querySelector('span');
            if (confirmSpanElement) {
                confirmSpanElement.textContent = confirmOriginalText;
            }
        }
        if(paymentSection) paymentSection.classList.remove('chk-animate-pulse');
        console.error('Error processing reservation:', error);
        showNoticeModal('Reservation Error', 'There was an error processing your reservation. Please try again.');
    }
}

async function createBooking(paymentMethod) {
    try {
        // Get user info
        const firstName = document.getElementById('firstName')?.value || '';
        const lastName = document.getElementById('lastName')?.value || '';
        const email = document.getElementById('email')?.value || '';
        const phone = document.getElementById('phone')?.value || '';
        
        // Get totals
        const subtotal = parseFloat(document.getElementById('subtotal')?.textContent.replace('$', '') || 0);
        const serviceFee = parseFloat(document.getElementById('service-fee')?.textContent.replace('$', '') || 0);
        const processingFee = parseFloat(document.getElementById('processing-fee')?.textContent.replace('$', '') || 0);
        const customizationFeeEl = document.getElementById('customization-fee');
        const customizationFee = customizationFeeEl && customizationFeeEl.style.display !== 'none' 
            ? parseFloat(customizationFeeEl.textContent.replace('$', '') || 0) 
            : 0;
        
        // Get ticket categories from orderItems
        const ticketCategories = [];
        const bookedSeats = [];
        let totalTicketCount = 0;
        
        // Get selected seats from sessionStorage (from booking page)
        let selectedSeatsFromStorage = [];
        const selectedSeatsStr = sessionStorage.getItem('selected_seats');
        if (selectedSeatsStr) {
            try {
                selectedSeatsFromStorage = JSON.parse(selectedSeatsStr);
            } catch (e) {
                console.warn("Failed to parse selected seats:", e);
            }
        }
        
        // Group seats by category
        const seatsByCategory = {};
        selectedSeatsFromStorage.forEach(seat => {
            if (!seatsByCategory[seat.category_name]) {
                seatsByCategory[seat.category_name] = [];
            }
            seatsByCategory[seat.category_name].push(seat.seat_id);
        });
        
        orderItems.forEach(item => {
            const categoryName = item.categoryName || item.ticketType;
            ticketCategories.push({
                category_name: categoryName,
                quantity: item.quantity,
                price: item.price || 0
            });
            totalTicketCount += item.quantity;
            
            // Add seats for this category if available
            if (seatsByCategory[categoryName] && seatsByCategory[categoryName].length > 0) {
                seatsByCategory[categoryName].forEach(seatId => {
                    bookedSeats.push({
                        seat_id: seatId,
                        category_name: categoryName
                    });
                });
            }
        });
        
        // Get reservation IDs from URL or sessionStorage
        const urlParams = new URLSearchParams(window.location.search);
        const reservationIds = urlParams.get('reservations') || 
                              urlParams.get('reservation_ids') ||
                              sessionStorage.getItem('temp_reservation_ids') ||
                              null;
        
        // Get event ID
        let eventId = parseInt(urlParams.get('event_id') || urlParams.get('eventId') || 0);
        if (!eventId && events.length > 0) {
            eventId = events[0].id;
        }
        
        // Validate event ID
        if (!eventId || eventId === 0) {
            console.error('Missing event ID!', { urlParams: urlParams.toString(), events: events });
            throw new Error('Event ID is required for booking');
        }
        
        // Get customization data if available
        const customizationData = sessionStorage.getItem('ticket_customization');
        let ticketDetails = {};
        if (customizationData) {
            try {
                const customData = JSON.parse(customizationData);
                ticketDetails = {
                    customized: customData.customized_count > 0,
                    customized_count: customData.customized_count || 0,
                    guest_names: customData.guest_names || {},
                    tickets_by_category: customData.tickets_by_category || {}
                };
            } catch (e) {
                console.warn("Failed to parse customization data:", e);
            }
        }
        
        // Validate required data
        if (!firstName || !lastName || !email) {
            throw new Error('Please fill in all required customer information (First Name, Last Name, Email)');
        }
        
        if (totalTicketCount === 0) {
            throw new Error('No tickets selected for booking');
        }
        
        if (ticketCategories.length === 0) {
            throw new Error('No ticket categories found');
        }
        
        // Prepare booking data
        const bookingData = {
            user_id: null, // Will be set by backend from session
            event_id: eventId,
            ticket_count: totalTicketCount,
            subtotal: subtotal,
            service_fee: serviceFee,
            processing_fee: processingFee,
            customization_fee: customizationFee,
            payment_method: paymentMethod,
            customer_first_name: firstName,
            customer_last_name: lastName,
            customer_email: email,
            customer_phone: phone,
            ticket_categories: ticketCategories,
            booked_seats: bookedSeats,
            reservation_ids: reservationIds,
            ticket_details: ticketDetails
        };
        
        
        // Call API
        const response = await fetch('/api/bookings_API.php?action=create', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify(bookingData)
        });
        
        // Get response text first
        const responseText = await response.text();
        
        // Check if response is OK
        if (!response.ok) {
            console.error('API Error Response (HTTP ' + response.status + '):', responseText);
            
            // Try to parse as JSON for better error message
            let errorData;
            try {
                errorData = JSON.parse(responseText);
                throw new Error(`HTTP ${response.status}: ${errorData.message || errorData.error || 'Unknown error'}${errorData.error_type ? ' (' + errorData.error_type + ')' : ''}`);
            } catch (parseError) {
                // Not JSON, show raw text
                throw new Error(`HTTP ${response.status}: ${responseText.substring(0, 500)}`);
            }
        }
        
        // Try to parse JSON
        let result;
        try {
            result = JSON.parse(responseText);
        } catch (parseError) {
            console.error('JSON Parse Error. Response was:', responseText);
            throw new Error('Invalid JSON response from server. Response: ' + responseText.substring(0, 500));
        }
        
        
        if (result.success) {
            // Clear session storage
            sessionStorage.removeItem('checkout_orderItems');
            sessionStorage.removeItem('checkout_events');
            sessionStorage.removeItem('ticket_customization');
            sessionStorage.removeItem('customization_fee');
            sessionStorage.removeItem('temp_reservation_ids');
            sessionStorage.removeItem('temp_reservation_id');
            sessionStorage.removeItem('temp_event_id');
            sessionStorage.removeItem('temp_tickets');
            sessionStorage.removeItem('selected_seats');
            
            return {
                success: true,
                booking_id: result.booking_id,
                booking_code: result.booking_code
            };
        } else {
            console.error('Booking failed:', result);
            throw new Error(result.message || 'Failed to create booking');
        }
        
    } catch (error) {
        console.error('Error creating booking:', error);
        console.error('Error stack:', error.stack);
        
        // Only log bookingData if it was defined
        if (typeof bookingData !== 'undefined') {
            console.error('Booking data that failed:', {
                event_id: bookingData?.event_id,
                ticket_count: bookingData?.ticket_count,
                categories: bookingData?.ticket_categories?.length,
                customer_email: bookingData?.customer_email
            });
        } else {
            console.error('Booking data was not created before error occurred');
        }
        
        return {
            success: false,
            message: error.message || 'Failed to create booking'
        };
    }
}


// --- Card Formatting and Display Functions ---

function formatCardNumber(e) {
    // This function already filters for numbers and formats
    let value = e.target.value.replace(/\s+/g, '').replace(/[^0-9]/gi, '');
    let formatted = '';
    for (let i = 0; i < value.length; i++) {
        if (i > 0 && i % 4 === 0) formatted += ' ';
        formatted += value[i];
    }
    e.target.value = formatted;
    updateCardDisplay();
    detectCardType(value);
}

/**
 * Filters card holder input for letters and spaces only.
 */
function formatCardHolder(e) {
    // Filter value to only allow letters and spaces
    let value = e.target.value.replace(/[^a-zA-Z\s]/g, '');
    e.target.value = value;

    // Call the original update function
    updateCardHolderDisplay.call(e.target); // Renamed to avoid conflict
}

// Renamed to avoid conflict with the event handler name
function updateCardHolderDisplay() {
    const displayCardHolder = document.getElementById('displayCardHolder');
    if(displayCardHolder) displayCardHolder.textContent = this.value.toUpperCase() || 'FULL NAME';
}

function formatCardExpiry(e) {
    // This function already filters for numbers and formats
    let value = e.target.value.replace(/\D/g, '');
    if (value.length > 2) {
        value = value.substring(0, 2) + '/' + value.substring(2, 4);
    }
    e.target.value = value;
    const displayCardExpiry = document.getElementById('displayCardExpiry');
    if(displayCardExpiry) displayCardExpiry.textContent = value || 'MM/YY';
}

/**
 * Filters CVV input for numbers only.
 */
function formatCardCVV(e) {
    // Filter value to only allow numbers
    let value = e.target.value.replace(/\D/g, '');
    e.target.value = value;

    // Call the original update function
    updateCardCVVDisplay.call(e.target); // Renamed to avoid conflict
}

// Renamed to avoid conflict with the event handler name
function updateCardCVVDisplay() {
     const displayCardCVV = document.getElementById('displayCardCVV');
     if(displayCardCVV) displayCardCVV.textContent = this.value.replace(/./g, '•') || '•••';
}


function updateCardDisplay() {
    const cardNumberValue = document.getElementById('cardNumber')?.value ?? ''; // Safe navigation
    const displayNumber = document.getElementById('displayCardNumber');
    if (displayNumber) {
        if (cardNumberValue) {
            displayNumber.textContent = cardNumberValue; // Show the formatted number
        } else {
            displayNumber.textContent = '•••• •••• •••• ••••';
        }
    }
}


function detectCardType(cardNumber) {
    const brandIconBack = document.getElementById('cardBrandIconBack');
    if (!brandIconBack) return; // Exit if element not found

    let iconClass = 'fa-cc-visa';
    if (/^4/.test(cardNumber)) {
        iconClass = 'fa-cc-visa';
    } else if (/^5[1-5]/.test(cardNumber)) {
        iconClass = 'fa-cc-mastercard';
    } else if (/^3[47]/.test(cardNumber)) {
        iconClass = 'fa-cc-amex';
    } else if (/^6(?:011|5)/.test(cardNumber)) {
        iconClass = 'fa-cc-discover';
    }
    brandIconBack.className = `fab ${iconClass} chk-card-brand-back`;

    // Also update the front icon if it exists (though it's missing in your latest HTML)
    const brandIconFront = document.getElementById('cardBrandIcon');
    if(brandIconFront) {
         brandIconFront.className = `fab ${iconClass} chk-card-brand`;
    }
}

// --- Utility Functions ---

function flipCard(flip) {
    const creditCard = document.getElementById('creditCard');
    if (creditCard) { // Check if element exists
      if (flip) {
          creditCard.classList.add('flipped');
      } else {
          // Check if the card is currently flipped before removing the class
          if (creditCard.classList.contains('flipped')) {
              creditCard.classList.remove('flipped');
          }
      }
    }
}

function shakeElement(element) {
    if (element) {
        element.classList.add('chk-animate-shake');
        setTimeout(() => {
            element.classList.remove('chk-animate-shake');
        }, 500);
    }
}

// Show CVV tooltip
function showCVVTooltip() {
    const cvvTooltip = document.getElementById('cvvTooltip');
    if (cvvTooltip) { // Check if element exists
      cvvTooltip.classList.remove('chk-hidden');
    }
}

// Hide CVV tooltip
function hideCVVTooltip() {
    const cvvTooltip = document.getElementById('cvvTooltip');
      if (cvvTooltip) { // Check if element exists
        cvvTooltip.classList.add('chk-hidden');
      }
}

function showNoticeModal(title, message, onClose = null) {
    const noticeModalHeader = document.getElementById('noticeModalHeader');
    const noticeModalText = document.getElementById('noticeModalText');
    const noticeModal = document.getElementById('noticeModal');
    const noticeModalCloseBtn = document.getElementById('noticeModalCloseBtn');
    const noticeModalOkBtn = document.getElementById('noticeModalOkBtn');

    if (noticeModalHeader && noticeModalText && noticeModal) { // Check if elements exist
      noticeModalHeader.textContent = title;
      noticeModalText.textContent = message;
      noticeModal.classList.remove('chk-hidden');
      
      // If callback provided, set up close handlers
      if (onClose) {
          const handleClose = () => {
              noticeModal.classList.add('chk-hidden');
              onClose();
          };
          
          // Handle close button
          if (noticeModalCloseBtn) {
              // Remove existing listeners by cloning
              const newCloseBtn = noticeModalCloseBtn.cloneNode(true);
              noticeModalCloseBtn.parentNode.replaceChild(newCloseBtn, noticeModalCloseBtn);
              newCloseBtn.addEventListener('click', handleClose);
          }
          
          // Handle OK button
          if (noticeModalOkBtn) {
              const newOkBtn = noticeModalOkBtn.cloneNode(true);
              noticeModalOkBtn.parentNode.replaceChild(newOkBtn, noticeModalOkBtn);
              newOkBtn.addEventListener('click', handleClose);
          }
          
          // Close on backdrop click
          const backdropHandler = (e) => {
              if (e.target === noticeModal) {
                  noticeModal.classList.add('chk-hidden');
                  noticeModal.removeEventListener('click', backdropHandler);
                  onClose();
              }
          };
          noticeModal.addEventListener('click', backdropHandler);
      }
    }
}


// Show confetti effect
function showConfetti() {
    // Check if confetti elements might already exist to prevent duplicates if clicked quickly
    if (document.querySelector('.chk-confetti')) {
        return;
    }

    for (let i = 0; i < 150; i++) {
        const confetti = document.createElement('div');
        confetti.className = 'chk-confetti';
        confetti.style.left = Math.random() * 100 + 'vw';
        confetti.style.backgroundColor = `hsl(${Math.random() * 360}, 100%, 50%)`;
        confetti.style.width = Math.random() * 8 + 4 + 'px';
        confetti.style.height = Math.random() * 8 + 4 + 'px';
        confetti.style.opacity = Math.random() * 0.5 + 0.5;
        document.body.appendChild(confetti);

        const animation = confetti.animate([
            { transform: `translateY(-20px) rotate(0deg)`, opacity: 1 }, // Start slightly above viewport
            { transform: `translateY(105vh) rotate(${Math.random() * 720}deg)`, opacity: 0 } // Go slightly below
        ], {
            duration: Math.random() * 2000 + 3000, // Duration between 3-5 seconds
            easing: 'ease-out'
        });
        // Remove the element after animation finishes
        animation.onfinish = () => confetti.remove();
    }
}

// ============================================
// Ticket Customization Function
// ============================================
window.goToCustomization = function() {
    // Get event ID from multiple sources
    let eventId = null;
    
    // Try URL parameters first
    const urlParams = new URLSearchParams(window.location.search);
    eventId = parseInt(urlParams.get('eventId') || urlParams.get('event_id'));
    
    // Fallback to other sources
    if (!eventId) {
        eventId = window.reservationData?.eventId || 
                  sessionStorage.getItem('eventId') ||
                  (typeof urlEventId !== 'undefined' ? urlEventId : null);
    }
    
    // Get total tickets
    const totalTickets = getTotalTicketsCount();
    
    // Get reservation IDs from URL (comma-separated)
    const reservationIds = urlParams.get('reservations') || 
                          urlParams.get('reservation_ids') ||
                          sessionStorage.getItem('temp_reservation_ids') ||
                          null;
    
    // Get single reservation ID if no multiple IDs
    const reservationId = urlParams.get('reservation_id') ||
                         window.reservationData?.reservationId || 
                         sessionStorage.getItem('temp_reservation_id') ||
                         null;
    
    if (!eventId || totalTickets === 0) {
        alert('⚠️ No tickets found. Please add tickets to your cart first.\n\nEvent ID: ' + (eventId || 'missing') + '\nTickets: ' + totalTickets);
        return;
    }
    
    // Get already customized tickets info
    let alreadyCustomized = null;
    const sessionCustomization = sessionStorage.getItem('ticket_customization');
    if (sessionCustomization) {
        try {
            const customData = JSON.parse(sessionCustomization);
            // Only pass if it's for the same event
            if (customData.event_id == eventId) {
                alreadyCustomized = {
                    customized_count: customData.customized_count || 0,
                    guest_names: customData.guest_names || {},
                    tickets_by_category: customData.tickets_by_category || {}
                };
            }
        } catch (e) {
            console.warn("Failed to parse customization data:", e);
        }
    }
    
    // Also check window.customizationData
    if (!alreadyCustomized && window.customizationData && window.customizationData.event_id == eventId) {
        alreadyCustomized = {
            customized_count: window.customizationData.customized_count || 0,
            guest_names: window.customizationData.guest_names || {},
            tickets_by_category: window.customizationData.tickets_by_category || {}
        };
    }
    
    // Store data in session for customize page
    sessionStorage.setItem('temp_event_id', eventId);
    sessionStorage.setItem('temp_tickets', totalTickets);
    if (alreadyCustomized) {
        sessionStorage.setItem('temp_already_customized', JSON.stringify(alreadyCustomized));
    }
    if (reservationIds) {
        sessionStorage.setItem('temp_reservation_ids', reservationIds);
    } else if (reservationId) {
        sessionStorage.setItem('temp_reservation_id', reservationId);
    }
    
    // Redirect to customization page
    let redirectUrl = `customize_tickets.php?event_id=${eventId}&tickets=${totalTickets}`;
    if (reservationIds) {
        redirectUrl += `&reservations=${reservationIds}`;
    } else if (reservationId) {
        redirectUrl += `&reservation_id=${reservationId}`;
    }
    if (alreadyCustomized && alreadyCustomized.customized_count > 0) {
        redirectUrl += `&already_customized=${alreadyCustomized.customized_count}`;
    }
    window.location.href = redirectUrl;
}

// Helper function to get total tickets count
function getTotalTicketsCount() {
    let total = 0;
    
    // Method 1: Get from orderItems array (most reliable)
    if (typeof orderItems !== 'undefined' && Array.isArray(orderItems)) {
        orderItems.forEach(item => {
            total += item.quantity || 0;
        });
        if (total > 0) return total;
    }
    
    // Method 2: Get from DOM (after page renders - most reliable for reservation mode)
    const orderItemElements = document.querySelectorAll('.chk-quantity-control__display');
    if (orderItemElements.length > 0) {
        orderItemElements.forEach(item => {
            const text = item.textContent || '';
            const qty = parseInt(text.replace(/[^0-9]/g, '')) || 0;
            total += qty;
        });
        if (total > 0) return total;
    }
    
    // Method 3: Get from URL parameters (fallback for direct links)
    const urlParams = new URLSearchParams(window.location.search);
    let urlQuantity = parseInt(urlParams.get('quantity'));
    
    if (urlQuantity && urlQuantity > 0) {
        return urlQuantity;
    }
    
    // If all else fails, return 0
    return total;
}

// Update total tickets display
function updateTicketsAvailable() {
    const totalTicketsEl = document.getElementById('total-tickets-available');
    if (totalTicketsEl) {
        const total = getTotalTicketsCount();
        totalTicketsEl.textContent = total;
    }
}

// Initialize ticket count display
if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', function() {
        // Wait for orderItems to be rendered
        setTimeout(updateTicketsAvailable, 500);
        setTimeout(updateTicketsAvailable, 1500);
        
        // Update when cart changes
        const observer = new MutationObserver(updateTicketsAvailable);
        const orderItemsContainer = document.getElementById('orderItems');
        if (orderItemsContainer) {
            observer.observe(orderItemsContainer, { childList: true, subtree: true });
        }
    });
} else {
    // DOM already loaded
    setTimeout(updateTicketsAvailable, 100);
    setTimeout(updateTicketsAvailable, 1000);
}
document.addEventListener('DOMContentLoaded', () => {

    // Initialize Vanta.js background
    if (typeof VANTA !== 'undefined' && document.getElementById('vanta-bg')) {
        VANTA.NET({
            el: "#vanta-bg",
            color: 0xf97316,
            backgroundColor: 0x0,
            points: 12,
            maxDistance: 20,
            spacing: 15
        });
    }

    // Initialize feather icons
    if (typeof feather !== 'undefined') {
        feather.replace();
    }

    // Get event data from PHP
    const eventData = window.eventData || {};
    const eventId = eventData.eventId;
    const venueSeatingType = eventData.venueSeatingType;
    const ticketCategories = eventData.ticketCategories || [];
    const minTickets = eventData.minTickets || 1;
    const maxTickets = eventData.maxTickets || 10;
    
    // Load availability on page load
    if (eventId) {
        loadAvailability();
    }

    // Fixed fee per ticket
    const fixedFeePerTicket = 5.99;

    // Global object to track selected tickets by category
    // Format: { 'CategoryName': quantity }
    let selectedTickets = {};
    
    // Initialize selectedTickets with all categories set to 0
    ticketCategories.forEach(cat => {
        selectedTickets[cat.category_name] = 0;
    });

    // State for current seating layout - start as null so switch always runs
    let currentSeatingLayout = null;
    
    // Reservations tracking
    let activeReservations = [];
    let reservationCheckInterval = null;

    // --------------------------------------------------------
    // CORE FUNCTIONALITY: Calculate Total and Update UI
    // --------------------------------------------------------
    function updateCheckoutTotal() {
        let totalCount = 0;
        let subtotal = 0;

        // Calculate from selected tickets
        Object.keys(selectedTickets).forEach(categoryName => {
            const quantity = selectedTickets[categoryName] || 0;
            if (quantity > 0) {
                const category = ticketCategories.find(cat => cat.category_name === categoryName);
                if (category) {
                    totalCount += quantity;
                    subtotal += quantity * parseFloat(category.price);
                }
            }
        });

        const totalFees = (fixedFeePerTicket * totalCount);
        const finalTotal = (subtotal + totalFees);
        
        // Update UI elements
        const totalSeatsCountElement = document.getElementById('selected-seats-count');
        const checkoutBtn = document.getElementById('checkout-btn');

        if (totalSeatsCountElement) {
            totalSeatsCountElement.textContent = totalCount;
        }

        // Update category-specific counts in sidebar
        ticketCategories.forEach(category => {
            const countElement = document.getElementById(`ticket-count-${category.category_name.toLowerCase().replace(/\s+/g, '-')}`);
            if (countElement) {
                countElement.textContent = selectedTickets[category.category_name] || 0;
            }
        });

        if (checkoutBtn) {
            if (totalCount >= minTickets && totalCount <= maxTickets) {
                checkoutBtn.textContent = `Checkout • $${finalTotal.toFixed(2)}`;
                checkoutBtn.disabled = false;
                checkoutBtn.classList.remove('opacity-50', 'cursor-not-allowed');
                checkoutBtn.classList.add('hover:shadow-orange-700/50');
            } else {
                if (totalCount > 0) {
                    checkoutBtn.textContent = `Select ${minTickets}-${maxTickets} tickets`;
                } else {
                    checkoutBtn.textContent = `Proceed to Checkout`;
                }
                checkoutBtn.disabled = true;
                checkoutBtn.classList.add('opacity-50', 'cursor-not-allowed');
                checkoutBtn.classList.remove('hover:shadow-orange-700/50');
            }
        }
    }

    // --------------------------------------------------------
    // RESERVATION SYSTEM
    // --------------------------------------------------------
    async function reserveTickets() {
        if (!eventId) return false;

        try {
            // First, release any existing reservations
            await releaseReservations();

            // Create new reservations for selected tickets
            const reservationPromises = [];
            Object.keys(selectedTickets).forEach(categoryName => {
                const quantity = selectedTickets[categoryName] || 0;
                if (quantity > 0) {
                    // Verify category exists in ticketCategories
                    const categoryExists = ticketCategories.some(cat => cat.category_name === categoryName);
                    if (!categoryExists) {
                        console.error('Category mismatch!', {
                            requested: categoryName,
                            available: ticketCategories.map(c => c.category_name)
                        });
                        alert(`Error: Category "${categoryName}" not found. Please refresh and try again.`);
                        return;
                    }
                    
                    const formData = new FormData();
                    formData.append('action', 'reserve');
                    formData.append('event_id', eventId);
                    formData.append('category_name', categoryName);
                    formData.append('quantity', quantity);

                    reservationPromises.push(
                        fetch('/api/ticket_reservations.php', {
                            method: 'POST',
                            body: formData
                        }).then(async res => {
                            const data = await res.json();
                            if (!res.ok) {
                                return { success: false, message: data.message || `HTTP ${res.status} error` };
                            }
                            return data;
                        }).catch(error => {
                            console.error('Reservation fetch error:', error);
                            return { success: false, message: 'Network error: ' + error.message };
                        })
                    );
                }
            });

            if (reservationPromises.length === 0) {
                console.warn('No reservations to create');
                return true; // No tickets selected, but that's okay
            }
            
            const results = await Promise.all(reservationPromises);
            const failed = results.find(r => !r || !r.success);
            
            if (failed) {
                console.error('Reservation failed:', failed);
                alert('Reservation failed: ' + (failed.message || 'Unknown error'));
                return false;
            }
            
            // Check if any results are missing reservation_id
            const validResults = results.filter(r => r && r.success);
            if (validResults.length === 0 || validResults.some(r => !r.reservation_id)) {
                console.error('Reservation results incomplete:', results);
                alert('Reservation incomplete. Please try again.');
                return false;
            }

            // Store reservation IDs
            activeReservations = validResults.filter(r => r.reservation_id).map(r => r.reservation_id);
            
            // Start checking reservation expiry
            startReservationCheck();
            
            return true;
        } catch (error) {
            console.error('Reservation error:', error);
            alert('Error reserving tickets. Please try again.');
            return false;
        }
    }

    async function releaseReservations() {
        try {
            await fetch('/api/ticket_reservations.php?action=release', {
                method: 'POST'
            });
            activeReservations = [];
            stopReservationCheck();
        } catch (error) {
            console.error('Error releasing reservations:', error);
        }
    }

    function startReservationCheck() {
        // Check every minute if reservations are still valid
        reservationCheckInterval = setInterval(async () => {
            try {
                const response = await fetch(`/api/ticket_reservations.php?action=getReservations`);
                const data = await response.json();
                
                if (data.success && data.reservations.length === 0) {
                    // Reservations expired
                    alert('Your ticket reservations have expired. Please select again.');
                    selectedTickets = {};
                    ticketCategories.forEach(cat => {
                        selectedTickets[cat.category_name] = 0;
                    });
                    updateCheckoutTotal();
                    stopReservationCheck();
                }
            } catch (error) {
                console.error('Error checking reservations:', error);
            }
        }, 60000); // Check every minute
    }

    function stopReservationCheck() {
        if (reservationCheckInterval) {
            clearInterval(reservationCheckInterval);
            reservationCheckInterval = null;
        }
    }

    // --------------------------------------------------------
    // TICKET SELECTION HANDLERS
    // --------------------------------------------------------
    function handleTicketSelection(categoryName, change) {
        const category = ticketCategories.find(cat => cat.category_name === categoryName);
        if (!category) return;

        const current = selectedTickets[categoryName] || 0;
        const newValue = Math.max(0, Math.min(category.available_tickets, current + change));
        
        // Check total tickets limit
        const totalSelected = Object.values(selectedTickets).reduce((sum, qty) => sum + qty, 0) - current + newValue;
        if (totalSelected > maxTickets) {
            alert(`Maximum ${maxTickets} tickets per booking`);
            return;
        }

        selectedTickets[categoryName] = newValue;
        updateCheckoutTotal();
    }

    // --------------------------------------------------------
    // SEATING MODAL LOGIC
    // --------------------------------------------------------
    const seatingModal = document.getElementById('seating-modal');
    const seatMapContainer = document.getElementById('seating-map-views');
    const openSeatModalBtn = document.getElementById('open-seat-modal-btn');
    const closeSeatingModalBtn = document.getElementById('close-seating-modal-btn');
    const doneSelectingBtn = document.getElementById('done-selecting-btn');
    const layoutToggleButtons = document.querySelectorAll('.layout-toggle-btn');

    // Seating managers
    let theatreSeatingManager = null;
    let stadiumSeatingManager = null;

    const openSeatingModal = async () => {
        if (seatingModal) {
            // Load availability
            await loadAvailability();
            
            // Reload booked seats to ensure they're up to date when modal opens
            try {
                const seatsResponse = await fetch(`/api/bookings_API.php?action=getBookedSeats&event_id=${eventId}`);
                const seatsData = await seatsResponse.json();
                
                if (seatsData.success && seatsData.seats) {
                    // Update seating managers with latest booked seats
                    if (stadiumSeatingManager) {
                        stadiumSeatingManager.bookedSeats = new Set(seatsData.seats);
                        stadiumSeatingManager.generateSeats();
                        stadiumSeatingManager.renderSeating();
                    }
                    if (theatreSeatingManager) {
                        theatreSeatingManager.bookedSeats = new Set(seatsData.seats);
                        theatreSeatingManager.generateSeats();
                        theatreSeatingManager.renderSeating();
                    }
                }
            } catch (e) {
                console.warn('Could not reload booked seats:', e);
            }
            
            seatingModal.classList.remove('hidden');
            document.body.classList.add('overflow-hidden');
            if (typeof feather !== 'undefined') {
                feather.replace();
            }
            
            // Initialize seating managers based on venue type
            if (!ticketCategories || ticketCategories.length === 0) {
                console.error('ERROR: No ticket categories available! Cannot generate seats.');
                alert('Error: No ticket categories found for this event. Please contact support.');
                return;
            }
            
            if (venueSeatingType === 'stadium') {
                // First switch the layout to show stadium
                switchSeatingLayout('stadium');
                
                // Wait a bit for DOM to update, then initialize manager
                setTimeout(async () => {
                    if (!stadiumSeatingManager && typeof StadiumSeatingManager !== 'undefined') {
                        try {
                            // Load booked seats first
                            let bookedSeatsList = [];
                            try {
                                const seatsResponse = await fetch(`/api/bookings_API.php?action=getBookedSeats&event_id=${eventId}`);
                                const seatsData = await seatsResponse.json();
                                if (seatsData.success && seatsData.seats) {
                                    bookedSeatsList = seatsData.seats;
                                }
                            } catch (e) {
                                console.warn('Could not load booked seats:', e);
                            }
                            
                            stadiumSeatingManager = new StadiumSeatingManager(
                                ticketCategories,
                                selectedTickets,
                                updateCheckoutTotal,
                                bookedSeatsList
                            );
                        } catch (error) {
                            console.error('Error creating StadiumSeatingManager:', error);
                        }
                    }
                }, 200);
            } else if (venueSeatingType === 'standing') {
                // Standing layout uses simple quantity selection
                switchSeatingLayout('standing');
                initializeStandingLayout();
            } else if (venueSeatingType === 'theatre') {
                // First switch the layout to show theatre
                switchSeatingLayout('theatre');
                
                // Wait a bit for DOM to update, then initialize manager
                setTimeout(async () => {
                    if (!theatreSeatingManager && typeof TheatreSeatingManager !== 'undefined') {
                        try {
                            // Load booked seats first
                            let bookedSeatsList = [];
                            try {
                                const seatsResponse = await fetch(`/api/bookings_API.php?action=getBookedSeats&event_id=${eventId}`);
                                const seatsData = await seatsResponse.json();
                                if (seatsData.success && seatsData.seats) {
                                    bookedSeatsList = seatsData.seats;
                                }
                            } catch (e) {
                                console.warn('Could not load booked seats:', e);
                            }
                            
                            theatreSeatingManager = new TheatreSeatingManager(
                                ticketCategories,
                                selectedTickets,
                                updateCheckoutTotal,
                                bookedSeatsList
                            );
                        } catch (error) {
                            console.error('Error creating TheatreSeatingManager:', error);
                        }
                    }
                }, 200);
            }
        }
    };
    
    const closeSeatingModal = () => {
        if (seatingModal) {
            seatingModal.classList.add('hidden');
            document.body.classList.remove('overflow-hidden');
        }
    };

    async function loadAvailability() {
        if (!eventId) return;
        
        try {
            // Load ticket availability
            const response = await fetch(`/api/ticket_reservations.php?action=getAvailability&event_id=${eventId}`);
            const data = await response.json();
            
            if (data.success && data.categories) {
                // Update ticket categories with current availability
                data.categories.forEach(cat => {
                    const index = ticketCategories.findIndex(tc => tc.category_name === cat.category_name);
                    if (index !== -1) {
                        ticketCategories[index].available_tickets = cat.actually_available;
                        ticketCategories[index].reserved_tickets = cat.reserved_tickets || 0;
                    }
                });
                
                // Update UI to show availability
                updateSeatingAvailability();
            } else {
                console.error('Failed to load availability:', data);
            }
            
            // Load booked seats
            const seatsResponse = await fetch(`/api/bookings_API.php?action=getBookedSeats&event_id=${eventId}`);
            const seatsData = await seatsResponse.json();
            
            if (seatsData.success && seatsData.seats && seatsData.seats.length > 0) {
                console.log('Loaded booked seats:', seatsData.seats);
                
                // Update seating managers with booked seats
                if (stadiumSeatingManager) {
                    stadiumSeatingManager.bookedSeats = new Set(seatsData.seats);
                    stadiumSeatingManager.generateSeats();
                    stadiumSeatingManager.renderSeating();
                }
                if (theatreSeatingManager) {
                    theatreSeatingManager.bookedSeats = new Set(seatsData.seats);
                    theatreSeatingManager.generateSeats();
                    theatreSeatingManager.renderSeating();
                }
            } else {
            }
        } catch (error) {
            console.error('Error loading availability:', error);
        }
    }

    function updateSeatingAvailability() {
        // Update category labels with availability
        ticketCategories.forEach(category => {
            const available = category.available_tickets || 0;
            const categoryElements = document.querySelectorAll(`[data-category="${category.category_name}"]`);
            categoryElements.forEach(el => {
                const label = el.querySelector('.section-label');
                if (label) {
                    const price = parseFloat(category.price).toFixed(2);
                    label.textContent = `${category.category_name} - $${price} (${available} available)`;
                }
            });
        });
    }

    function switchSeatingLayout(newLayout) {
        
        // Always update if different (or if null/undefined)
        if (newLayout === currentSeatingLayout && currentSeatingLayout !== null) {
            return;
        }
        
        currentSeatingLayout = newLayout;
        
        // Toggle Visibility
        const theatreLayout = document.getElementById('layout-theatre');
        const stadiumLayout = document.getElementById('layout-stadium');
        const standingLayout = document.getElementById('layout-standing');
        
        console.log('Theatre layout element:', theatreLayout);
        console.log('Stadium layout element:', stadiumLayout);
        console.log('Standing layout element:', standingLayout);
        
        // Hide all layouts first
        if (theatreLayout) theatreLayout.classList.add('hidden');
        if (stadiumLayout) stadiumLayout.classList.add('hidden');
        if (standingLayout) standingLayout.classList.add('hidden');
        
        // Show the selected layout
        if (newLayout === 'theatre' && theatreLayout) {
            theatreLayout.classList.remove('hidden');
            console.log('Theatre layout shown');
        } else if (newLayout === 'stadium' && stadiumLayout) {
            stadiumLayout.classList.remove('hidden');
            console.log('Stadium layout shown');
        } else if (newLayout === 'standing' && standingLayout) {
            standingLayout.classList.remove('hidden');
            console.log('Standing layout shown');
        }
        
        // Update Button Styles
        layoutToggleButtons.forEach(btn => {
            const layoutType = btn.getAttribute('data-layout');
            if (layoutType === newLayout) {
                btn.classList.add('bg-orange-600', 'text-white');
                btn.classList.remove('text-gray-400', 'hover:bg-gray-700', 'hover:text-white');
            } else {
                btn.classList.remove('bg-orange-600', 'text-white');
                btn.classList.add('text-gray-400', 'hover:bg-gray-700', 'hover:text-white');
            }
        });
    }

    // --------------------------------------------------------
    // STANDING LAYOUT: Quantity Selection
    // --------------------------------------------------------
    // Use a flag to track if event listeners are already attached
    let standingLayoutInitialized = false;
    
    function initializeStandingLayout() {
        const standingSummary = document.getElementById('standing-summary');
        const standingTotal = document.getElementById('standing-total');
        const standingLayout = document.getElementById('layout-standing');
        
        // Only attach event listeners once using event delegation
        // This prevents multiple listeners from being attached when function is called multiple times
        if (!standingLayoutInitialized && standingLayout) {
            standingLayoutInitialized = true;
            
            // Use event delegation on the standing layout container
            // This handles clicks on all quantity buttons, even if they're added later
            standingLayout.addEventListener('click', (e) => {
                // Check if clicked element is a quantity button
                const btn = e.target.closest('.standing-qty-btn');
                if (!btn) return;
                
                const categoryName = btn.getAttribute('data-category');
                const action = btn.getAttribute('data-action');
                const input = document.querySelector(`.standing-qty-input[data-category="${categoryName}"]`);
                
                if (!input) return;
                
                let currentQty = parseInt(input.value) || 0;
                const maxQty = parseInt(input.getAttribute('max')) || 10;
                
                if (action === 'increase' && currentQty < maxQty) {
                    currentQty++;
                } else if (action === 'decrease' && currentQty > 0) {
                    currentQty--;
                }
                
                input.value = currentQty;
                
                // Update selectedTickets
                selectedTickets[categoryName] = currentQty;
                
                // Update checkout total (global function)
                updateCheckoutTotal();
                
                // Update standing summary
                updateStandingSummary();
            });
            
            // Handle direct input changes using event delegation
            standingLayout.addEventListener('input', (e) => {
                const input = e.target;
                if (!input.classList.contains('standing-qty-input')) return;
                
                const categoryName = input.getAttribute('data-category');
                let qty = parseInt(input.value) || 0;
                const maxQty = parseInt(input.getAttribute('max')) || 10;
                
                // Enforce limits
                if (qty < 0) qty = 0;
                if (qty > maxQty) qty = maxQty;
                
                input.value = qty;
                selectedTickets[categoryName] = qty;
                
                updateCheckoutTotal();
                updateStandingSummary();
            });
        }
        
        // Always update the summary when layout is initialized/shown
        function updateStandingSummary() {
            let summaryHTML = '';
            let total = 0;
            let hasTickets = false;
            
            ticketCategories.forEach(category => {
                const qty = selectedTickets[category.category_name] || 0;
                if (qty > 0) {
                    hasTickets = true;
                    const subtotal = qty * parseFloat(category.price);
                    total += subtotal;
                    summaryHTML += `
                        <div class="flex justify-between text-sm">
                            <span>${qty}x ${category.category_name}</span>
                            <span>$${subtotal.toFixed(2)}</span>
                        </div>
                    `;
                }
            });
            
            if (!hasTickets) {
                summaryHTML = '<p class="text-center text-gray-500">No tickets selected yet</p>';
            }
            
            if (standingSummary) standingSummary.innerHTML = summaryHTML;
            if (standingTotal) standingTotal.textContent = `$${total.toFixed(2)}`;
        }
        
        // Update summary immediately
        updateStandingSummary();
    }

    // Handle category selection in seating modal
    if (seatMapContainer) {
        seatMapContainer.addEventListener('click', (e) => {
            const categoryName = e.target.getAttribute('data-category');
            if (categoryName && e.target.classList.contains('category-select-btn')) {
                const change = e.target.classList.contains('increase') ? 1 : -1;
                handleTicketSelection(categoryName, change);
            }
        });
    }

    // Layout toggle buttons
    layoutToggleButtons.forEach(btn => {
        btn.addEventListener('click', () => {
            const layout = btn.getAttribute('data-layout');
            switchSeatingLayout(layout);
        });
    });

    if (openSeatModalBtn) {
        openSeatModalBtn.addEventListener('click', openSeatingModal);
    }
    
    if (closeSeatingModalBtn) {
        closeSeatingModalBtn.addEventListener('click', closeSeatingModal);
    }

    if (doneSelectingBtn) {
        doneSelectingBtn.addEventListener('click', async () => {
            // Reserve tickets when done selecting
            const reserved = await reserveTickets();
            if (reserved) {
                updateCheckoutTotal();
                closeSeatingModal();
            }
        });
    }

    // Hide modal if user clicks outside
    if (seatingModal) {
        seatingModal.addEventListener('click', (e) => {
            if (e.target.id === 'seating-modal') {
                closeSeatingModal();
            }
        });
    }

    // --------------------------------------------------------
    // TERMS & CONDITIONS MODAL LOGIC 
    // --------------------------------------------------------
    const termsModal = document.getElementById('terms-modal');
    const closeModalBtn = document.getElementById('close-modal-btn');
    const openTermsModalLink = document.getElementById('open-terms-modal');

    if (openTermsModalLink && termsModal) {
        openTermsModalLink.addEventListener('click', (e) => {
            e.preventDefault();
            termsModal.classList.remove('hidden');
            document.body.classList.add('overflow-hidden');
            if (typeof feather !== 'undefined') {
                feather.replace();
            }
        });
    }

    const closeTermsModal = () => {
        if (termsModal) {
            termsModal.classList.add('hidden');
            document.body.classList.remove('overflow-hidden');
        }
    };

    if (closeModalBtn) {
        closeModalBtn.addEventListener('click', closeTermsModal);
    }

    if (termsModal) {
        termsModal.addEventListener('click', (e) => {
            if (e.target.id === 'terms-modal') {
                closeTermsModal();
            }
        });
    }

    // --------------------------------------------------------
    // CHECKOUT BUTTON
    // --------------------------------------------------------
    const checkoutBtn = document.getElementById('checkout-btn');
    if (checkoutBtn) {
        checkoutBtn.addEventListener('click', async () => {
            const totalCount = Object.values(selectedTickets).reduce((sum, qty) => sum + qty, 0);
            
            if (totalCount < minTickets) {
                alert(`Minimum ${minTickets} ticket(s) required`);
                return;
            }
            
            if (totalCount > maxTickets) {
                alert(`Maximum ${maxTickets} tickets per booking`);
                return;
            }

            // Ensure tickets are reserved
            const reserved = await reserveTickets();
            if (reserved && activeReservations.length > 0) {
                // Get selected seats from seating managers
                let selectedSeats = [];
                if (venueSeatingType === 'stadium' && stadiumSeatingManager) {
                    selectedSeats = stadiumSeatingManager.getSelectedSeats();
                } else if (venueSeatingType === 'theatre' && theatreSeatingManager) {
                    selectedSeats = theatreSeatingManager.getSelectedSeats();
                }
                
                // Store selected seats in sessionStorage
                if (selectedSeats.length > 0) {
                    sessionStorage.setItem('selected_seats', JSON.stringify(selectedSeats));
                }
                
                // Redirect to checkout with reservation data
                const params = new URLSearchParams({
                    event_id: eventId,
                    reservations: activeReservations.join(',')
                });
                window.location.href = `checkout.php?${params.toString()}`;
            } else {
                alert('Failed to reserve tickets. Please try again.');
            }
        });
    }

    // Initialize
    updateCheckoutTotal();
    
    // Clean up on page unload
    window.addEventListener('beforeunload', () => {
        releaseReservations();
    });
});

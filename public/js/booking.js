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
                        fetch('../../public/api/ticket_reservations.php', {
                            method: 'POST',
                            body: formData
                        }).then(res => res.json())
                    );
                }
            });

            const results = await Promise.all(reservationPromises);
            const failed = results.find(r => !r.success);
            
            if (failed) {
                alert('Reservation failed: ' + failed.message);
                return false;
            }

            // Store reservation IDs
            activeReservations = results.filter(r => r.reservation_id).map(r => r.reservation_id);
            
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
            await fetch('../../public/api/ticket_reservations.php?action=release', {
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
                const response = await fetch(`../../public/api/ticket_reservations.php?action=getReservations`);
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
                setTimeout(() => {
                    if (!stadiumSeatingManager && typeof StadiumSeatingManager !== 'undefined') {
                        try {
                            stadiumSeatingManager = new StadiumSeatingManager(
                                ticketCategories,
                                selectedTickets,
                                updateCheckoutTotal
                            );
                        } catch (error) {
                            console.error('Error creating StadiumSeatingManager:', error);
                        }
                    }
                }, 200);
            } else if (venueSeatingType === 'theatre' || venueSeatingType === 'standing') {
                // First switch the layout to show theatre
                switchSeatingLayout('theatre');
                
                // Wait a bit for DOM to update, then initialize manager
                setTimeout(() => {
                    if (!theatreSeatingManager && typeof TheatreSeatingManager !== 'undefined') {
                        try {
                            theatreSeatingManager = new TheatreSeatingManager(
                                ticketCategories,
                                selectedTickets,
                                updateCheckoutTotal
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
            const response = await fetch(`../../public/api/ticket_reservations.php?action=getAvailability&event_id=${eventId}`);
            const data = await response.json();
            
            if (data.success) {
                // Update ticket categories with current availability
                data.categories.forEach(cat => {
                    const index = ticketCategories.findIndex(tc => tc.category_name === cat.category_name);
                    if (index !== -1) {
                        ticketCategories[index].available_tickets = cat.actually_available;
                    }
                });
                
                // Update UI to show availability
                updateSeatingAvailability();
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
        console.log('switchSeatingLayout called with:', newLayout, 'current:', currentSeatingLayout);
        
        // Always update if different (or if null/undefined)
        if (newLayout === currentSeatingLayout && currentSeatingLayout !== null) {
            console.log('Layout already set, skipping');
            return;
        }
        
        currentSeatingLayout = newLayout;
        
        // Toggle Visibility
        const theatreLayout = document.getElementById('layout-theatre');
        const stadiumLayout = document.getElementById('layout-stadium');
        
        console.log('Theatre layout element:', theatreLayout);
        console.log('Stadium layout element:', stadiumLayout);
        
        if (newLayout === 'theatre') {
            if (theatreLayout) {
                theatreLayout.classList.remove('hidden');
                console.log('Theatre layout shown');
            }
            if (stadiumLayout) {
                stadiumLayout.classList.add('hidden');
                console.log('Stadium layout hidden');
            }
        } else if (newLayout === 'stadium') {
            if (theatreLayout) {
                theatreLayout.classList.add('hidden');
                console.log('Theatre layout hidden');
            }
            if (stadiumLayout) {
                stadiumLayout.classList.remove('hidden');
                console.log('Stadium layout shown');
            }
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
            if (reserved) {
                // Redirect to checkout with reservation data
                const params = new URLSearchParams({
                    event_id: eventId,
                    reservations: activeReservations.join(',')
                });
                window.location.href = `checkout.php?${params.toString()}`;
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

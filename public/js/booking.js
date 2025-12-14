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

    // Fixed fee per ticket (used in total calculation)
    const fixedFeePerTicket = 5.99;

    // Global array to track selected seats (RESERVED SEATING ONLY)
    // Stores objects like { id: 'A1', price: 75.00, type: 'vip' }
    let selectedSeats = []; 
    
    // Global object to track General Admission tickets (STADIUM ONLY)
    // Stores counts like { 'vip': 0, 'general': 0 }
    let generalAdmissionTickets = { 'vip': 0, 'general': 0 };

    // State for current seating layout
    let currentSeatingLayout = 'theatre'; // Default view

    // --------------------------------------------------------
    // CORE FUNCTIONALITY: Calculate Total and Update UI
    // --------------------------------------------------------
    function updateCheckoutTotal() {
        let totalCount = 0;
        let generalSeatsCount = 0;
        let vipSeatsCount = 0;
        let subtotal = 0;

        if (currentSeatingLayout === 'theatre') {
            // Reserved Seating (Theatre) logic
            totalCount = selectedSeats.length;
            subtotal = selectedSeats.reduce((sum, seat) => sum + seat.price, 0);
            generalSeatsCount = selectedSeats.filter(seat => seat.type === 'general').length;
            vipSeatsCount = selectedSeats.filter(seat => seat.type === 'vip').length;

        } else if (currentSeatingLayout === 'stadium') {
            // General Admission (Stadium) logic
            generalSeatsCount = generalAdmissionTickets['general'];
            vipSeatsCount = generalAdmissionTickets['vip'];
            totalCount = generalSeatsCount + vipSeatsCount;
            
            // Fetch prices dynamically (using data attributes on the General Ticket/VIP Ticket display elements)
            const generalPriceElement = document.querySelector('[data-ticket-type="general"]');
            const vipPriceElement = document.querySelector('[data-ticket-type="vip"]');
            
            const generalPrice = parseFloat(generalPriceElement ? generalPriceElement.getAttribute('data-base-price') : 0);
            const vipPrice = parseFloat(vipPriceElement ? vipPriceElement.getAttribute('data-base-price') : 0);
            
            subtotal = (generalSeatsCount * generalPrice) + (vipSeatsCount * vipPrice);
        }

        const totalFees = (fixedFeePerTicket * totalCount);
        const finalTotal = (subtotal + totalFees);
        
        // 2. Update UI elements
        const totalSeatsCountElement = document.getElementById('selected-seats-count');
        const generalCountElement = document.getElementById('general-ticket-count');
        const vipCountElement = document.getElementById('vip-ticket-count');
        const checkoutBtn = document.getElementById('checkout-btn');

        // Update total count
        if (totalSeatsCountElement) {
            totalSeatsCountElement.textContent = totalCount;
        }
        
        // Update category-specific counts
        if (generalCountElement) { 
            generalCountElement.textContent = generalSeatsCount;
        }

        if (vipCountElement) { 
            vipCountElement.textContent = vipSeatsCount;
        }

        if (checkoutBtn) {
            if (totalCount > 0) {
                checkoutBtn.textContent = `Checkout • $${finalTotal.toFixed(2)}`;
                checkoutBtn.disabled = false;
                checkoutBtn.classList.remove('opacity-50', 'cursor-not-allowed');
                checkoutBtn.classList.add('hover:shadow-orange-700/50');
            } else {
                checkoutBtn.textContent = `Proceed to Checkout`;
                checkoutBtn.disabled = true;
                checkoutBtn.classList.add('opacity-50', 'cursor-not-allowed');
                checkoutBtn.classList.remove('hover:shadow-orange-700/50');
            }
        }
    }

    // Initialize UI on load
    updateCheckoutTotal(); 

    // --------------------------------------------------------
    // SEAT SELECTION MODAL LOGIC & VIEW SWITCHING
    // --------------------------------------------------------
    const seatingModal = document.getElementById('seating-modal');
    const openSeatModalBtn = document.getElementById('open-seat-modal-btn');
    const closeSeatingModalBtn = document.getElementById('close-seating-modal-btn');
    const doneSelectingBtn = document.getElementById('done-selecting-btn');
    const seatMapContainer = document.getElementById('seating-map-views'); 
    
    // Layout buttons and views
    const theatreLayout = document.getElementById('layout-theatre');
    const stadiumLayout = document.getElementById('layout-stadium');
    const layoutToggleButtons = document.querySelectorAll('.layout-toggle-btn');
    
    /**
     * Handles ticket selection for General Admission (Stadium View).
     * @param {string} ticketType - 'vip' or 'general'
     */
    function handleGeneralAdmissionClick(ticketType) {
        const currentCount = generalAdmissionTickets[ticketType];
        
        // Get the appropriate display name
        const displayName = ticketType === 'vip' ? 'Lower Tier VIP' : 'Upper Tier General';

        // Prompt the user for the number of tickets
        const input = prompt(`Enter number of tickets for ${displayName}: (Current: ${currentCount})`);

        if (input !== null) {
            const count = parseInt(input);
            if (!isNaN(count) && count >= 0 && count <= 10) { // Limit to 10 tickets for safety
                generalAdmissionTickets[ticketType] = count;
            } else if (count > 10) {
                 alert("You can select a maximum of 10 tickets per transaction.");
            } else if (count < 0) {
                 alert("Ticket count cannot be negative.");
            } else {
                alert("Invalid input. Please enter a number.");
            }
        }
    }


    function switchSeatingLayout(newLayout) {
        if (newLayout === currentSeatingLayout) return; 
        
        // 1. Update state
        currentSeatingLayout = newLayout;
        
        // 2. Toggle Visibility
        if (newLayout === 'theatre') {
            theatreLayout.classList.remove('hidden');
            stadiumLayout.classList.add('hidden');
        } else if (newLayout === 'stadium') {
            theatreLayout.classList.add('hidden');
            stadiumLayout.classList.remove('hidden');
        }
        
        // 3. Update Button Styles
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
        
        // 4. Update the visual state of the seats (important for Theatre view)
        // Clear all visual selections first
        document.querySelectorAll('.seat.selected').forEach(seat => seat.classList.remove('selected'));
        
        // If switching to THEATRE, re-apply visual selection from selectedSeats array
        if (newLayout === 'theatre') {
            selectedSeats.forEach(selectedSeat => {
                const seatElement = document.querySelector(`[data-seat-id="${selectedSeat.id}"]`);
                if (seatElement) {
                    seatElement.classList.add('selected');
                }
            });
        } 
        
        // If switching to STADIUM, the visual map is just a guide, so no seats are marked 'selected'.
        // The purchase is done via the quantity prompt.
    }

    // Initialize button listeners for layout switching
    layoutToggleButtons.forEach(btn => {
        btn.addEventListener('click', () => {
            switchSeatingLayout(btn.getAttribute('data-layout'));
        });
    });

    const openSeatingModal = () => {
        if (seatingModal) {
            seatingModal.classList.remove('hidden');
            document.body.classList.add('overflow-hidden');
            if (typeof feather !== 'undefined') {
                feather.replace();
            }
            switchSeatingLayout(currentSeatingLayout); 
        }
    };
    
    const closeSeatingModal = () => {
        if (seatingModal) {
            seatingModal.classList.add('hidden');
            document.body.classList.remove('overflow-hidden');
        }
    };
    
    // Seat Toggling Logic (Handles selection/deselection)
    if (seatMapContainer) {
        seatMapContainer.addEventListener('click', (e) => {
            // Ensure click target is a seat and is available
            if (e.target.classList.contains('seat') && e.target.classList.contains('available')) {
                
                const seatType = e.target.getAttribute('data-seat-type'); 

                if (currentSeatingLayout === 'theatre') {
                    // --- RESERVED SEATING MODE (Individual seat selection) ---
                    const seatId = e.target.getAttribute('data-seat-id');
                    const seatPrice = parseFloat(e.target.getAttribute('data-price'));

                    if (e.target.classList.contains('selected')) {
                        // Deselect seat
                        e.target.classList.remove('selected');
                        selectedSeats = selectedSeats.filter(seat => seat.id !== seatId);
                    } else {
                        // Select seat
                        e.target.classList.add('selected');
                        selectedSeats.push({ id: seatId, price: seatPrice, type: seatType });
                    }
                } else if (currentSeatingLayout === 'stadium') {
                    // --- GENERAL ADMISSION MODE (Quantity selection) ---
                    // Prevent individual seat selection; open quantity prompt instead.
                    handleGeneralAdmissionClick(seatType);
                }
            }
        });
    }

    if (openSeatModalBtn) {
        openSeatModalBtn.addEventListener('click', openSeatingModal);
    }
    
    if (closeSeatingModalBtn) {
        closeSeatingModalBtn.addEventListener('click', closeSeatingModal);
    }

    if (doneSelectingBtn) {
        doneSelectingBtn.addEventListener('click', () => {
            updateCheckoutTotal(); 
            closeSeatingModal();
        });
    }

    // Hide modal if user clicks outside of it (on the overlay)
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

    
    // --- Checkout Button Logic ---
    const checkoutBtn = document.getElementById('checkout-btn');
    if (checkoutBtn) {
        checkoutBtn.addEventListener('click', () => {
            
            let seatIds = '';
            let ticketCount = 0;
            
            if (currentSeatingLayout === 'theatre') {
                // Reserved Seating: Send specific seat IDs
                ticketCount = selectedSeats.length;
                seatIds = selectedSeats.map(seat => seat.id).join(',');
            } else {
                // General Admission: Send category counts instead of specific seats
                ticketCount = generalAdmissionTickets['general'] + generalAdmissionTickets['vip'];
                // Use a simplified indicator for General Admission categories
                if(generalAdmissionTickets['general'] > 0) {
                    seatIds += `GA_G:${generalAdmissionTickets['general']}`;
                }
                if(generalAdmissionTickets['vip'] > 0) {
                     seatIds += (seatIds ? ';' : '') + `GA_V:${generalAdmissionTickets['vip']}`;
                }
            }


            if (ticketCount === 0) {
                alert("Please select at least one ticket before proceeding to checkout.");
                return;
            }

            const eventId = checkoutBtn.getAttribute('data-event-id');

            // Pass the data for the next page
            window.location.href = `checkout.php?event_id=${eventId}&seats=${seatIds}&tickets=${ticketCount}&layout=${currentSeatingLayout}`;
        });
    }

    // Parallax effect (Client-side visual effect)
    window.addEventListener('scroll', function() {
        const scrollPosition = window.scrollY;

        // Banner parallax
        const banner = document.getElementById('event-banner');
        if (banner) {
            banner.style.backgroundPositionY = `calc(50% + ${scrollPosition * 0.3}px)`;
        }

        // Content parallax
        const parallaxLayers = document.querySelectorAll('.parallax-layer');
        parallaxLayers.forEach((layer, index) => {
            const speed = 0.1 + (index * 0.05);
            const offsetY = scrollPosition * speed;
            layer.style.transform = `translateY(${offsetY}px)`;
        });
    });

});
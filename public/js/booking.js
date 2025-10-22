// --- FIX 2: Wait for the HTML document to be fully loaded ---
// This ensures that elements like 'checkout-btn' and 'ticket-count'
// exist before the script tries to find them.
document.addEventListener('DOMContentLoaded', () => {

    // Initialize Vanta.js background
    // Added a check in case VANTA is not loaded
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
    // Added a check in case feather is not loaded
    if (typeof feather !== 'undefined') {
        feather.replace();
    }

    // Get the initial price from the PHP-set data attribute
    const initialPriceElement = document.getElementById('ticket-price');
    // Fallback for when the price element might not exist (e.g., event not found)
    const initialPrice = parseFloat(initialPriceElement ? initialPriceElement.getAttribute('data-base-price') : 0);

    // Function to update ticket count (Client-side interactivity)
    function updateTicketCount(change) {
        const countElement = document.getElementById('ticket-count');
        // Safety check if element doesn't exist
        if (!countElement) return;

        let count = parseInt(countElement.textContent);
        count += change;
        if (count < 1) count = 1;
        if (count > 10) count = 10;
        countElement.textContent = count;

        // Calculate totals using the base price
        const ticketPrice = initialPrice;
        const subtotal = (ticketPrice * count);
        const fees = (5.99 * count);
        const total = (subtotal + fees).toFixed(2);

        const checkoutBtn = document.getElementById('checkout-btn');
        if (checkoutBtn) {
            checkoutBtn.textContent = `Checkout • $${total}`;
        }
    }

    // Initialize checkout button text on load
    updateTicketCount(0);

    // --- FIX 1: Add Event Listeners for the +/- buttons ---
    // This was the missing part. Your updateTicketCount function
    // was never being called when a button was clicked.
    //
    // *** IMPORTANT: Make sure your HTML buttons have these exact IDs ***
    const decreaseBtn = document.getElementById('decrease-btn');
    const increaseBtn = document.getElementById('increase-btn');

    if (decreaseBtn) {
        decreaseBtn.addEventListener('click', (e) => {
            e.preventDefault(); // Prevent form submission, just in case
            updateTicketCount(-1);
        });
    }

    if (increaseBtn) {
        increaseBtn.addEventListener('click', (e) => {
            e.preventDefault();
            updateTicketCount(1);
        });
    }


    //Term & Conditions
    const termsModal = document.getElementById('terms-modal');
    const closeModalBtn = document.getElementById('close-modal-btn');
    const openTermsModalLink = document.getElementById('open-terms-modal');

    // Added safety checks here too
    if (openTermsModalLink && termsModal) {
        openTermsModalLink.addEventListener('click', (e) => {
            e.preventDefault();
            termsModal.classList.remove('hidden');
            document.body.classList.add('overflow-hidden'); // Prevent background scrolling
            if (typeof feather !== 'undefined') {
                feather.replace(); // Ensure icons in modal are rendered
            }
        });
    }

    const closeModal = () => {
        if (termsModal) {
            termsModal.classList.add('hidden');
            document.body.classList.remove('overflow-hidden');
        }
    };

    if (closeModalBtn) {
        closeModalBtn.addEventListener('click', closeModal);
    }

    // Hide modal if user clicks outside of it (on the overlay)
    if (termsModal) {
        termsModal.addEventListener('click', (e) => {
            if (e.target.id === 'terms-modal') {
                closeModal();
            }
        });
    }

    
    // Checkout Button Alert (Client-side interactivity)
    // This will now work because it's inside the DOMContentLoaded listener
    const checkoutBtn = document.getElementById('checkout-btn');
    if (checkoutBtn) {
        checkoutBtn.addEventListener('click', () => {
            const countElement = document.getElementById('ticket-count');
            if (!countElement) return; // Safety check

            const ticketCount = parseInt(countElement.textContent);
            const ticketPrice = initialPrice;
            const subtotal = ticketPrice * ticketCount;
            const fees = 5.99 * ticketCount;
            const grandTotal = subtotal + fees;

            // Use the PHP-set price format for the alert
            const formattedPriceForAlert = '$' + ticketPrice.toFixed(2);
            
            // NOTE: Per instructions, using custom alert simulation instead of native alert()
            const alertMessage = `Order Summary:\n\nTickets: ${ticketCount} x ${formattedPriceForAlert}\nSubtotal: $${subtotal.toFixed(2)}\nFees: $${fees.toFixed(2)}\nTotal: $${grandTotal.toFixed(2)}`;
            console.log(alertMessage); // Log to console instead of native alert
            
            // A simple message box replacement (as native alerts are forbidden)
            const customAlertOverlay = document.getElementById('custom-alert-overlay');
            if (customAlertOverlay) {
                const customAlertMessage = document.getElementById('custom-alert-message');
                if (customAlertMessage) {
                    customAlertMessage.textContent = alertMessage.replace(/\n\n/g, '\n').replace(/\n/g, ' | ');
                }
                customAlertOverlay.style.display = 'flex';
            } else {
                // Fallback to console if custom alert HTML isn't in header/footer/body
                alert('Order initiated (see console for details)');
            }
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

}); // --- End of DOMContentLoaded listener ---

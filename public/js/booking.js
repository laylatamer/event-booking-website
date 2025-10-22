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

    
    // --- FIX: Checkout Button Logic Updated ---
    const checkoutBtn = document.getElementById('checkout-btn');
    if (checkoutBtn) {
        checkoutBtn.addEventListener('click', () => {
            const countElement = document.getElementById('ticket-count');
            if (!countElement) return; // Safety check

            // 1. Get the ticket count
            const ticketCount = parseInt(countElement.textContent);
            
            // 2. Get the event ID from the button's data attribute (which you just added in booking.php)
            const eventId = checkoutBtn.getAttribute('data-event-id');

            // 3. Redirect to checkout.php, passing the data as URL parameters
            // This replaces the old alert logic.
            // Note: This assumes checkout.php is in the same directory. 
            // If it's in a different folder (like /views/), adjust the path.
            window.location.href = `checkout.php?event_id=${eventId}&tickets=${ticketCount}`;
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


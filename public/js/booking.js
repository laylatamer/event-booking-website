  // Initialize Vanta.js background
        VANTA.NET({
            el: "#vanta-bg",
            color: 0xf97316,
            backgroundColor: 0x0,
            points: 12,
            maxDistance: 20,
            spacing: 15
        });

        // Initialize feather icons
        feather.replace();

        // Get the initial price from the PHP-set data attribute
        const initialPriceElement = document.getElementById('ticket-price');
        // Fallback for when the price element might not exist (e.g., event not found)
        const initialPrice = parseFloat(initialPriceElement ? initialPriceElement.getAttribute('data-base-price') : 0);

        // Function to update ticket count (Client-side interactivity)
        function updateTicketCount(change) {
            const countElement = document.getElementById('ticket-count');
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

            document.getElementById('checkout-btn').textContent = `Checkout • $${total}`;
        }

        // Initialize checkout button text on load
        updateTicketCount(0);

        //Term & Conditions
        const termsModal = document.getElementById('terms-modal');
        const closeModalBtn = document.getElementById('close-modal-btn');
        const openTermsModalLink = document.getElementById('open-terms-modal');

        openTermsModalLink.addEventListener('click', (e) => {
            e.preventDefault();
            termsModal.classList.remove('hidden');
            document.body.classList.add('overflow-hidden'); // Prevent background scrolling
            feather.replace(); // Ensure icons in modal are rendered
        });

        const closeModal = () => {
            termsModal.classList.add('hidden');
            document.body.classList.remove('overflow-hidden');
        };

        closeModalBtn.addEventListener('click', closeModal);

        // Hide modal if user clicks outside of it (on the overlay)
        termsModal.addEventListener('click', (e) => {
            if (e.target.id === 'terms-modal') {
                closeModal();
            }
        });


        // Checkout Button Alert (Client-side interactivity)
        document.getElementById('checkout-btn').addEventListener('click', () => {
            const ticketCount = parseInt(document.getElementById('ticket-count').textContent);
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
            if (document.getElementById('custom-alert-overlay')) {
                document.getElementById('custom-alert-message').textContent = alertMessage.replace(/\n\n/g, '\n').replace(/\n/g, ' | ');
                document.getElementById('custom-alert-overlay').style.display = 'flex';
            } else {
                 // Fallback to console if custom alert HTML isn't in header/footer/body
                 alert('Order initiated (see console for details)');
            }
        });

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

        
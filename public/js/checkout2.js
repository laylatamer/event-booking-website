// Initialize Vanta.js background
VANTA.NET({
    el: "#vanta-bg",
    color: 0xf97316,
    backgroundColor: 0x1a1a1a,
    points: 12,
    maxDistance: 20,
    spacing: 15
});

// Initialize feather icons
feather.replace();

// IMPORTANT: Event data MUST match your event_data.php
// In a real app, fetch this from the server. For now, copy it.
const events = [
    { id: 1, title: "Summer Music Festival 2023", description: "...", price: 89.99, date: "2023-08-15T18:00:00", location: "Central Park, NYC" },
    { id: 2, title: "Tech Conference 2023", description: "...", price: 199.99, date: "2023-09-22T09:00:00", location: "Convention Center, SF" },
    // === ADD EVENT 3 HERE ===
    { id: 3, title: "Your Event Title for ID 3", description: "Event 3 description", price: 50.00, date: "2023-11-10T20:00:00", location: "Some Venue" },
    // === ADD ANY OTHER EVENTS HERE ===
];

// Read 'eventId' and 'quantity' parameters from the current page's URL
const urlParams = new URLSearchParams(window.location.search);
const urlEventId = parseInt(urlParams.get('eventId')); // Get 'eventId'
const urlQuantity = parseInt(urlParams.get('quantity')); // Get 'quantity'

// Find the event details in the 'events' array using the ID from the URL
const currentEvent = events.find(e => e.id === urlEventId);

// Initialize orderItems as an empty array
let orderItems = [];
// Initialize default date/time variables (will be updated if event found)
let formattedDate = 'N/A';
let formattedTime = 'N/A';

// Check if we found a valid event AND a valid quantity from the URL
if (currentEvent && urlQuantity > 0) {
    // If yes, create the orderItems array with the event from the URL
     orderItems = [
        {
            eventId: currentEvent.id,       // Use the ID from the URL
            quantity: urlQuantity,         // Use the quantity from the URL
            ticketType: "Standard Ticket"  // Set a default ticket type
        }
    ];
    // Also format the date/time for the specific event found
     const eventDate = new Date(currentEvent.date);
     formattedDate = eventDate.toLocaleDateString('en-US', { weekday: 'long', year: 'numeric', month: 'long', day: 'numeric' });
     formattedTime = eventDate.toLocaleTimeString('en-US', { hour: 'numeric', minute: '2-digit' });

} else {
    // If no valid eventId or quantity was in the URL, log an error
    console.error("Event ID or quantity missing/invalid in URL. Order cannot be created.");
    // You could also display an error message to the user here using showNoticeModal
    // showNoticeModal('Error', 'Could not load order details. Please return to the event page.');
}


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

// --- Initialize the page ---
document.addEventListener('DOMContentLoaded', function() {
    renderOrderItems();
    calculateTotals();
    setupEventListeners();
    setupInputHighlights();
});

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

            // Create the HTML for the order item
            const itemElement = document.createElement('div');
            itemElement.className = 'chk-order-item';
            itemElement.innerHTML = `
                <div class="chk-order-item__icon-wrapper">
                    <i class="fas fa-ticket-alt chk-order-item__icon"></i>
                </div>
                <div class="chk-order-item__info">
                    <h3 class="chk-order-item__title">${eventItem.title}</h3>
                    <p class="chk-order-item__detail">${itemFormattedDate} at ${itemFormattedTime}</p> {/* Use item's date/time */}
                    <p class="chk-order-item__detail">${eventItem.location}</p>
                    <div class="chk-quantity-control">
                        <button class="chk-quantity-control__button chk-quantity-btn decrease" data-id="${eventItem.id}">
                            <i class="fas fa-minus"></i>
                        </button>
                        <span class="chk-quantity-control__display">${item.quantity}</span> {/* Use item's quantity */}
                        <button class="chk-quantity-control__button chk-quantity-btn increase" data-id="${eventItem.id}">
                            <i class="fas fa-plus"></i>
                        </button>
                    </div>
                </div>
                <div class="chk-order-item__price-section">
                    <p class="chk-order-item__price">$${(eventItem.price * item.quantity).toFixed(2)}</p> {/* Calculate price */}
                    <p class="chk-order-item__ticket-type">${item.ticketType}</p>
                </div>
            `;
            // Add the created item HTML to the page
            orderItemsContainer.appendChild(itemElement);
        }
    });

    // IMPORTANT: Re-attach listeners to the NEW +/- buttons after creating them
    document.querySelectorAll('.chk-quantity-btn.increase').forEach(btn => {
        btn.addEventListener('click', function() {
            const eventId = parseInt(this.getAttribute('data-id'));
            increaseQuantity(eventId);
        });
    });
    document.querySelectorAll('.chk-quantity-btn.decrease').forEach(btn => {
        btn.addEventListener('click', function() {
            const eventId = parseInt(this.getAttribute('data-id'));
            decreaseQuantity(eventId);
        });
    });
}


// Calculate order totals
function calculateTotals() {
    let subtotal = 0;
    orderItems.forEach(item => {
        const eventItem = events.find(e => e.id === item.eventId);
        if (eventItem) {
            subtotal += eventItem.price * item.quantity;
        }
    });

    // Calculate fees
    const serviceFee = orderItems.length * 5.99;
    const processingFee = subtotal * 0.03;
    const total = subtotal + serviceFee + processingFee;

    // Use querySelector for safer element finding, fallback to '0.00'
    const subtotalEl = document.getElementById('subtotal');
    const serviceFeeEl = document.getElementById('service-fee');
    const processingFeeEl = document.getElementById('processing-fee');
    const totalEl = document.getElementById('total');
    const placeOrderBtn = document.getElementById('placeOrderBtn');

    if (subtotalEl) subtotalEl.textContent = `$${subtotal.toFixed(2)}`;
    if (serviceFeeEl) serviceFeeEl.textContent = `$${serviceFee.toFixed(2)}`;
    if (processingFeeEl) processingFeeEl.textContent = `$${processingFee.toFixed(2)}`;
    if (totalEl) totalEl.textContent = `$${total.toFixed(2)}`;


    // Update place order button with total
    if(placeOrderBtn){
        const isCash = document.getElementById('cashOption')?.classList.contains('chk-payment-method--active'); // Added safe navigation
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
        // Increase its quantity
        item.quantity++;
        // Re-run the functions to update the display and totals
        renderOrderItems();
        calculateTotals();
    }
}

// Decrease item quantity (Modify global array and re-render)
function decreaseQuantity(eventId) {
    // Find the item in our global orderItems array
    const item = orderItems.find(i => i.eventId === eventId);
    // Only decrease if quantity is more than 1
    if (item && item.quantity > 1) {
        // Decrease its quantity
        item.quantity--;
        // Re-run the functions to update the display and totals
        renderOrderItems();
        calculateTotals();
    }
    // Optional: Add logic here if you want to remove item if quantity becomes 0
    // else if (item && item.quantity === 1) { /* Remove item logic */ }
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


    // Place Order Button Logic
    const placeOrderBtn = document.getElementById('placeOrderBtn');
    if (placeOrderBtn) {
        placeOrderBtn.addEventListener('click', () => {
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

function showPaymentSuccess() {
    const paymentSection = document.getElementById('paymentSection');
    if(paymentSection) paymentSection.classList.add('chk-animate-pulse'); // Check existence

    setTimeout(() => {
        if(paymentSection) paymentSection.classList.remove('chk-animate-pulse');
        showConfetti();
        showNoticeModal('Payment Successful', 'Your payment has been processed! Your tickets will be emailed to you shortly.');
        resetForms(); // Reset all forms
    }, 1000);
}

function showReservationSuccess() {
    const paymentSection = document.getElementById('paymentSection');
     if(paymentSection) paymentSection.classList.add('chk-animate-pulse'); // Check existence

    setTimeout(() => {
        if(paymentSection) paymentSection.classList.remove('chk-animate-pulse');
        showConfetti();
        showNoticeModal('Reservation Confirmed', 'Your reservation is confirmed! Please bring your ID to the venue.');
        resetForms(); // Reset all forms
    }, 1000);
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
          // Only flip back if CVV field is empty (optional, but good UX)
         const cvvInput = document.getElementById('cardCVV');
         if (!cvvInput || !cvvInput.value) { // Check if input exists and is empty
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

function showNoticeModal(title, message) {
    const noticeModalHeader = document.getElementById('noticeModalHeader');
    const noticeModalText = document.getElementById('noticeModalText');
    const noticeModal = document.getElementById('noticeModal');

    if (noticeModalHeader && noticeModalText && noticeModal) { // Check if elements exist
      noticeModalHeader.textContent = title;
      noticeModalText.textContent = message;
      noticeModal.classList.remove('chk-hidden');
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
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

// Sample event data
const events = [
    { id: 1, title: "Summer Music Festival 2023", description: "...", price: 89.99, date: "2023-08-15T18:00:00", location: "Central Park, NYC" },
    { id: 2, title: "Tech Conference 2023", description: "...", price: 199.99, date: "2023-09-22T09:00:00", location: "Convention Center, SF" }
];

// Get event ID from URL or default to 1
const urlParams = new URLSearchParams(window.location.search);
const eventId = parseInt(urlParams.get('id')) || 1;
const event = events.find(e => e.id === eventId) || events[0];

// Format date
const eventDate = new Date(event.date);
const formattedDate = eventDate.toLocaleDateString('en-US', { weekday: 'long', year: 'numeric', month: 'long', day: 'numeric' });
const formattedTime = eventDate.toLocaleTimeString('en-US', { hour: 'numeric', minute: '2-digit' });

// Current order items
let orderItems = [
    { eventId: event.id, quantity: 2, ticketType: "General Admission" }
];

// --- Helper functions for validation ---

function showError(fieldId, message) {
    const errorField = document.getElementById(fieldId);
    if (errorField) {
        errorField.textContent = message;
        errorField.classList.add('form-field__error--visible');
    }
}

function hideError(fieldId) {
    const errorField = document.getElementById(fieldId);
    if (errorField) {
        errorField.textContent = '';
        errorField.classList.remove('form-field__error--visible');
    }
}


function clearErrors() {
    const errorFields = document.querySelectorAll('.form-field__error');
    errorFields.forEach(field => {
        field.textContent = '';
        field.classList.remove('form-field__error--visible');
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
        const container = input ? input.closest('.form-field') : null; // Find parent container
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
    orderItemsContainer.innerHTML = '';
    orderItems.forEach(item => {
        const eventItem = events.find(e => e.id === item.eventId);
        if (eventItem) {
            const itemElement = document.createElement('div');
            itemElement.className = 'order-item';
            itemElement.innerHTML = `
                <div class="order-item__icon-wrapper">
                    <i class="fas fa-ticket-alt order-item__icon"></i>
                </div>
                <div class="order-item__info">
                    <h3 class="order-item__title">${eventItem.title}</h3>
                    <p class="order-item__detail">${formattedDate} at ${formattedTime}</p>
                    <p class="order-item__detail">${eventItem.location}</p>
                    <div class="quantity-control">
                        <button class="quantity-control__button quantity-btn decrease" data-id="${eventItem.id}">
                            <i class="fas fa-minus"></i>
                        </button>
                        <span class="quantity-control__display">${item.quantity}</span>
                        <button class="quantity-control__button quantity-btn increase" data-id="${eventItem.id}">
                            <i class="fas fa-plus"></i>
                        </button>
                    </div>
                </div>
                <div class="order-item__price-section">
                    <p class="order-item__price">$${(eventItem.price * item.quantity).toFixed(2)}</p>
                    <p class="order-item__ticket-type">${item.ticketType}</p>
                </div>
            `;
            orderItemsContainer.appendChild(itemElement);
        }
    });

    // Add event listeners to quantity buttons
    document.querySelectorAll('.quantity-btn.increase').forEach(btn => {
        btn.addEventListener('click', function() {
            const eventId = parseInt(this.getAttribute('data-id'));
            increaseQuantity(eventId);
        });
    });
    document.querySelectorAll('.quantity-btn.decrease').forEach(btn => {
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

    document.getElementById('subtotal').textContent = `$${subtotal.toFixed(2)}`;
    document.getElementById('service-fee').textContent = `$${serviceFee.toFixed(2)}`;
    document.getElementById('processing-fee').textContent = `$${processingFee.toFixed(2)}`;
    document.getElementById('total').textContent = `$${total.toFixed(2)}`;

    // Update place order button with total
    const placeOrderBtn = document.getElementById('placeOrderBtn');
    const isCash = document.getElementById('cashOption').classList.contains('payment-method--active');
    if (isCash) {
        placeOrderBtn.innerHTML = `<span>Reserve Tickets ($${total.toFixed(2)})</span><i class="fas fa-arrow-right button__icon"></i>`;
    } else {
        placeOrderBtn.innerHTML = `<span>Pay $${total.toFixed(2)}</span><i class="fas fa-arrow-right button__icon"></i>`;
    }
}

// Increase item quantity
function increaseQuantity(eventId) {
    const item = orderItems.find(i => i.eventId === eventId);
    if (item) {
        item.quantity++;
        renderOrderItems();
        calculateTotals();
    }
}

// Decrease item quantity
function decreaseQuantity(eventId) {
    const item = orderItems.find(i => i.eventId === eventId);
    if (item && item.quantity > 1) {
        item.quantity--;
        renderOrderItems();
        calculateTotals();
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
    creditCardOption.addEventListener('click', () => {
        creditCardOption.classList.add('payment-method--active');
        cashOption.classList.remove('payment-method--active');
        creditCardForm.classList.remove('hidden');
        cashForm.classList.add('hidden');
        calculateTotals();
        paymentSection.classList.add('active');
        setTimeout(() => { paymentSection.classList.remove('active'); }, 500);
    });

    cashOption.addEventListener('click', () => {
        cashOption.classList.add('payment-method--active');
        creditCardOption.classList.remove('payment-method--active');
        cashForm.classList.remove('hidden');
        creditCardForm.classList.add('hidden');
        calculateTotals();
        paymentSection.classList.add('active');
        setTimeout(() => { paymentSection.classList.remove('active'); }, 500);
    });

    // Card formatting and filtering
    document.getElementById('cardNumber').addEventListener('input', formatCardNumber);
    document.getElementById('cardHolder').addEventListener('input', formatCardHolder);
    document.getElementById('cardExpiry').addEventListener('input', formatCardExpiry);
    document.getElementById('cardCVV').addEventListener('input', formatCardCVV);

    // Place Order Button Logic
    const placeOrderBtn = document.getElementById('placeOrderBtn');
    placeOrderBtn.addEventListener('click', () => {
        clearErrors();
        const isCash = cashOption.classList.contains('payment-method--active');

        if (orderItems.length === 0) {
            showNoticeModal('Empty Order', 'Please add at least one ticket to your order!');
            return;
        }

        // Validate User Info first
        const isUserInfoValid = validateUserInfoForm();

        if (isCash) {
            if (isUserInfoValid) {
                // User info is valid, show cash warning
                document.getElementById('cashWarningModal').classList.remove('hidden');
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

    // Cash Warning Modal Listeners
    const cashModal = document.getElementById('cashWarningModal');
    document.getElementById('confirmReservationBtn').addEventListener('click', () => {
        cashModal.classList.add('hidden');
        showReservationSuccess();
    });
    document.getElementById('cancelReservationBtn').addEventListener('click', () => {
        cashModal.classList.add('hidden');
    });
    document.getElementById('cancelReservationBtnSecondary').addEventListener('click', () => {
        cashModal.classList.add('hidden');
    });

    // Notice Modal Listeners
    const noticeModal = document.getElementById('noticeModal');
    document.getElementById('noticeModalCloseBtn').addEventListener('click', () => {
        noticeModal.classList.add('hidden');
    });
    document.getElementById('noticeModalOkBtn').addEventListener('click', () => {
        noticeModal.classList.add('hidden');
    });

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

    const firstName = document.getElementById('firstName').value;
    const lastName = document.getElementById('lastName').value;
    const email = document.getElementById('email').value;
    const phone = document.getElementById('phone').value;

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
    const cardNumber = document.getElementById('cardNumber').value.replace(/\s/g, '');
    const cardHolder = document.getElementById('cardHolder').value;
    const cardExpiry = document.getElementById('cardExpiry').value;
    const cardCVV = document.getElementById('cardCVV').value;

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
    if (!isValid && document.getElementById('paymentSection')) {
        shakeElement(document.getElementById('paymentSection'));
    }

    return isValid;
}

/**
 * Resets all forms to default state.
 */
function resetForms() {
    // Clear all text inputs
    const inputs = document.querySelectorAll('.form-field__input');
    inputs.forEach(input => input.value = '');

    // Uncheck "save card"
    document.getElementById('saveCard').checked = false;

    // Reset credit card visual
    document.getElementById('displayCardNumber').textContent = '•••• •••• •••• ••••';
    document.getElementById('displayCardHolder').textContent = 'FULL NAME';
    document.getElementById('displayCardExpiry').textContent = 'MM/YY';
    document.getElementById('displayCardCVV').textContent = '•••';

    // Flip card back to front
    flipCard(false);

    // Reset card brand icon
    detectCardType('');

    // Clear all error messages
    clearErrors();
}

function showPaymentSuccess() {
    const paymentSection = document.getElementById('paymentSection');
    paymentSection.classList.add('animate-pulse');
    setTimeout(() => {
        paymentSection.classList.remove('animate-pulse');
        showConfetti();
        showNoticeModal('Payment Successful', 'Your payment has been processed! Your tickets will be emailed to you shortly.');
        resetForms(); // Reset all forms
    }, 1000);
}

function showReservationSuccess() {
    const paymentSection = document.getElementById('paymentSection');
    paymentSection.classList.add('animate-pulse');
    setTimeout(() => {
        paymentSection.classList.remove('animate-pulse');
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
    document.getElementById('displayCardHolder').textContent = this.value.toUpperCase() || 'FULL NAME';
}

function formatCardExpiry(e) {
    // This function already filters for numbers and formats
    let value = e.target.value.replace(/\D/g, '');
    if (value.length > 2) {
        value = value.substring(0, 2) + '/' + value.substring(2, 4);
    }
    e.target.value = value;
    document.getElementById('displayCardExpiry').textContent = value || 'MM/YY';
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
    document.getElementById('displayCardCVV').textContent = this.value.replace(/./g, '•') || '•••';
}


function updateCardDisplay() {
    const cardNumber = document.getElementById('cardNumber').value;
    const displayNumber = document.getElementById('displayCardNumber');
    if (cardNumber) {
        displayNumber.textContent = cardNumber;
    } else {
        displayNumber.textContent = '•••• •••• •••• ••••';
    }
}

function detectCardType(cardNumber) {
    const brandIconBack = document.getElementById('cardBrandIconBack');
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
    if (brandIconBack) { // Check if element exists before setting className
      brandIconBack.className = `fab ${iconClass} card-brand-back`;
    }
}

// --- Utility Functions ---

function flipCard(flip) {
    const creditCard = document.getElementById('creditCard');
    if (creditCard) { // Check if element exists
      if (flip) {
          creditCard.classList.add('flipped');
      } else {
          creditCard.classList.remove('flipped');
      }
    }
}

function shakeElement(element) {
    if (element) {
        element.classList.add('animate-shake');
        setTimeout(() => {
            element.classList.remove('animate-shake');
        }, 500);
    }
}

// Show CVV tooltip
function showCVVTooltip() {
    const cvvTooltip = document.getElementById('cvvTooltip');
    if (cvvTooltip) { // Check if element exists
      cvvTooltip.classList.remove('hidden');
    }
}

// Hide CVV tooltip
function hideCVVTooltip() {
    const cvvTooltip = document.getElementById('cvvTooltip');
     if (cvvTooltip) { // Check if element exists
       cvvTooltip.classList.add('hidden');
     }
}

function showNoticeModal(title, message) {
    const noticeModalHeader = document.getElementById('noticeModalHeader');
    const noticeModalText = document.getElementById('noticeModalText');
    const noticeModal = document.getElementById('noticeModal');

    if (noticeModalHeader && noticeModalText && noticeModal) { // Check if elements exist
      noticeModalHeader.textContent = title;
      noticeModalText.textContent = message;
      noticeModal.classList.remove('hidden');
    }
}


// Show confetti effect
function showConfetti() {
    for (let i = 0; i < 150; i++) {
        const confetti = document.createElement('div');
        confetti.className = 'confetti';
        confetti.style.left = Math.random() * 100 + 'vw';
        confetti.style.backgroundColor = `hsl(${Math.random() * 360}, 100%, 50%)`;
        confetti.style.width = Math.random() * 8 + 4 + 'px';
        confetti.style.height = Math.random() * 8 + 4 + 'px';
        confetti.style.opacity = Math.random() * 0.5 + 0.5;
        document.body.appendChild(confetti);

        const animation = confetti.animate([
            { transform: `translateY(0) rotate(0deg)`, opacity: 1 },
            { transform: `translateY(100vh) rotate(${Math.random() * 720}deg)`, opacity: 0 }
        ], {
            duration: Math.random() * 2000 + 3000,
            easing: 'ease-out'
        });
        animation.onfinish = () => confetti.remove();
    }
}
   
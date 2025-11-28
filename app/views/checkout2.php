<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Checkout</title>
    <link rel="stylesheet" href="../../public/css/checkout2.css">
    <script src="https://unpkg.com/feather-icons"></script>
    <script src="https://cdn.jsdelivr.net/npm/vanta@latest/dist/vanta.net.min.js"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body class="chk-checkout-page">
    <div id="vanta-bg"></div>

    <main class="chk-checkout-container">
        <div class="chk-checkout-content-wrapper">
            <div class="chk-checkout-layout">
                <div class="chk-checkout-layout__order-details">
                    <div class="chk-checkout-header">
                        <h1 class="chk-checkout-header__title">Complete Your Purchase</h1>
                        <p class="chk-checkout-header__subtitle">Review your order details and complete payment</p>
                    </div>

                    <div class="chk-order-summary-card">
                        <h2 class="chk-card-header">Order Summary</h2>
                        <div class="chk-order-items-container" id="orderItems">
                            </div>
                        <div class="chk-totals-summary">
                            <div class="chk-totals-summary__row">
                                <span class="chk-totals-summary__label">Subtotal</span>
                                <span class="chk-totals-summary__value" id="subtotal">$0.00</span>
                            </div>
                            <div class="chk-totals-summary__row">
                                <span class="chk-totals-summary__label">Service Fee</span>
                                <span class="chk-totals-summary__value" id="service-fee">$0.00</span>
                            </div>
                            <div class="chk-totals-summary__row">
                                <span class="chk-totals-summary__label">Processing Fee</span>
                                <span class="chk-totals-summary__value" id="processing-fee">$0.00</span>
                            </div>
                            <div class="chk-totals-summary__row chk-totals-summary__row--total">
                                <span>Total</span>
                                <span id="total">$0.00</span>
                            </div>
                        </div>
                    </div>

                    <div class="chk-attendee-info-card" id="userInfoCard">
                        <h2 class="chk-card-header">User Information</h2>
                        <div class="chk-form-grid">
                            <div class="chk-form-field">
                                <input type="text" id="firstName" class="chk-form-field__input chk-input-field" placeholder=" ">
                                <label class="chk-floating-label">First Name</label>
                                <span class="chk-form-field__error" id="firstNameError"></span>
                            </div>
                            <div class="chk-form-field">
                                <input type="text" id="lastName" class="chk-form-field__input chk-input-field" placeholder=" ">
                                <label class="chk-floating-label">Last Name</label>
                                <span class="chk-form-field__error" id="lastNameError"></span>
                            </div>
                            <div class="chk-form-field chk-form-field--span-2">
                                <input type="email" id="email" class="chk-form-field__input chk-input-field" placeholder=" ">
                                <label class="chk-floating-label">Email Address</label>
                                <span class="chk-form-field__error" id="emailError"></span>
                            </div>
                            <div class="chk-form-field chk-form-field--span-2">
                                <input type="tel" id="phone" class="chk-form-field__input chk-input-field" placeholder=" ">
                                <label class="chk-floating-label">Phone Number</label>
                                <span class="chk-form-field__error" id="phoneError"></span>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="chk-checkout-layout__payment-column">
                    <div class="chk-payment-card chk-payment-section" id="paymentSection">
                        <h2 class="chk-card-header">Payment Method</h2>
                        <div class="chk-payment-method-selector">
                            <div class="chk-payment-method chk-payment-method--active" id="creditCardOption">
                                <div class="chk-payment-method__content">
                                    <div class="chk-payment-method__icon-wrapper">
                                        <i class="fas fa-credit-card chk-payment-method__icon"></i>
                                    </div>
                                    <span class="chk-payment-method__label">Credit/Debit Card</span>
                                </div>
                            </div>
                            <div class="chk-payment-method" id="cashOption">
                                <div class="chk-payment-method__content">
                                    <div class="chk-payment-method__icon-wrapper">
                                        <i class="fas fa-money-bill-wave chk-payment-method__icon"></i>
                                    </div>
                                    <span class="chk-payment-method__label">Pay at Venue</span>
                                </div>
                            </div>
                        </div>

                        <div id="creditCardForm">
                            <div class="chk-card-container">
                                <div class="chk-card" id="creditCard">
                                    <div class="chk-card-front">
                                        <div>
                                            <div class="chk-card-header-flex">
                                                <b><span class="chk-card-issuer">EحGZLY</span></b>
                                            </div>
                                            <div class="chk-card-number" id="displayCardNumber">•••• •••• •••• ••••</div>
                                        </div>
                                        <div class="chk-card-details">
                                            <div class="chk-card-holder" id="displayCardHolder">FULL NAME</div>
                                            <div class="chk-card-expiry" id="displayCardExpiry">MM/YY</div>
                                        </div>
                                    </div>
                                    <div class="chk-card-back">
                                        <div class="chk-card-magnetic-strip"></div>
                                        <div class="chk-card-cvv-strip" id="displayCardCVV">•••</div>
                                        <div class="chk-card-signature">Authorized Signature</div>
                                        <div class="chk-card-hologram"></div>
                                        <i class="fab fa-cc-visa chk-card-brand-back" id="cardBrandIconBack"></i>
                                    </div>
                                </div>
                            </div>
                            <div class="chk-form-field chk-input-highlight" id="cardNumberContainer">
                                <input type="text" id="cardNumber" class="chk-form-field__input chk-input-field" placeholder="1234 5678 9012 3456" maxlength="19">
                                <label class="chk-floating-label">Card Number</label>
                                <span class="chk-form-field__error" id="cardNumberError"></span>
                            </div>
                            <div class="chk-form-field chk-input-highlight" id="cardHolderContainer">
                                <input type="text" id="cardHolder" class="chk-form-field__input chk-input-field" placeholder="As it appears on your card">
                                <label class="chk-floating-label">Card Holder Name</label>
                                <span class="chk-form-field__error" id="cardHolderError"></span>
                            </div>
                            <div class="chk-form-grid chk-form-grid--halves">
                                <div class="chk-form-field chk-input-highlight" id="cardExpiryContainer">
                                    <input type="text" id="cardExpiry" class="chk-form-field__input chk-input-field" placeholder="MM/YY" maxlength="5">
                                    <label class="chk-floating-label">Expiration Date</label>
                                    <span class="chk-form-field__error" id="cardExpiryError"></span>
                                </div>
                                <div class="chk-form-field chk-input-highlight" id="cardCVVContainer">
                                    <input type="text" id="cardCVV" class="chk-form-field__input chk-input-field" placeholder="•••" maxlength="4" onfocus="flipCard(true)" onblur="flipCard(false)">
                                    <label class="chk-floating-label">CVV</label>
                                    <div class="chk-cvv-tooltip-trigger" onclick="showCVVTooltip()">
                                        <i class="fas fa-question-circle"></i>
                                    </div>
                                    <span class="chk-form-field__error" id="cardCVVError"></span>
                                </div>
                            </div>
                            <div class="chk-checkbox-field">
                                <input type="checkbox" id="saveCard" class="chk-checkbox-field__input">
                                <label for="saveCard" class="chk-checkbox-field__label">Save card for future purchases</label>
                            </div>
                        </div>

                        <div id="cashForm" class="chk-hidden">
                            <div class="chk-info-box chk-info-box--warning">
                                <div class="chk-info-box__content">
                                    <i class="fas fa-info-circle chk-info-box__icon"></i>
                                    <p class="chk-info-box__text">You can pay for your tickets when you arrive at the venue. Please bring a valid ID and your booking confirmation.</p>
                                </div>
                            </div>
                        </div>

                        <button id="placeOrderBtn" class="chk-button chk-button--primary chk-button--full-width chk-gradient-bg chk-pulse-animation">
                            <span>Complete Purchase</span>
                            <i class="fas fa-arrow-right chk-button__icon"></i>
                        </button>
                        <div class="chk-form-footer-text">
                            <p>By placing your order, you agree to our <a href="terms.html" class="chk-form-footer-text__link">Terms & Conditions</a></p>
                        </div>
                        <div class="chk-secure-payment-badge">
                            <i class="fas fa-shield-alt chk-secure-payment-badge__icon"></i>
                            <span class="chk-secure-payment-badge__text">Secure Payment Processing</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </main>

    <div id="cvvTooltip" class="chk-modal-overlay chk-hidden">
        <div class="chk-modal-content">
            <button onclick="hideCVVTooltip()" class="chk-modal-close-button">
                <i class="fas fa-times"></i>
            </button>
            <h3 class="chk-modal-header">Where to find your CVV?</h3>
            <p class="chk-modal-text">The CVV is a 3 or 4-digit number on your card that provides extra security.</p>
            <div class="chk-modal-list-item">
                <i class="fas fa-credit-card chk-modal-list-item__icon"></i>
                <span>Visa/Mastercard: 3 digits on back</span>
            </div>
            <div class="chk-modal-list-item">
                <i class="fas fa-credit-card chk-modal-list-item__icon"></i>
                <span>American Express: 4 digits on front</span>
            </div>
        </div>
    </div>
    
    <div id="noticeModal" class="chk-modal-overlay chk-hidden">
        <div class="chk-modal-content">
            <button id="noticeModalCloseBtn" class="chk-modal-close-button">
                <i class="fas fa-times"></i>
            </button>
            <h3 id="noticeModalHeader" class="chk-modal-header">Notice</h3>
            <p id="noticeModalText" class="chk-modal-text">
                This is a generic notice.
            </p>
            <div class="chk-modal-actions">
                 <button id="noticeModalOkBtn" class="chk-button chk-button--primary chk-gradient-bg">OK</button>
            </div>
        </div>
    </div>

    <div id="cashWarningModal" class="chk-modal-overlay chk-hidden">
        <div class="chk-modal-content">
            <button id="cancelReservationBtn" class="chk-modal-close-button">
                <i class="fas fa-times"></i>
            </button>
            <h3 class="chk-modal-header">Reservation Policy</h3>
            <p class="chk-modal-text">
                Please note: If you reserve tickets and do not pay for them at the time of the event, the amount of the tickets reserved must be paid or you will not be able to book another ticket.
            </p>
            <div class="chk-modal-actions">
                 <button id="confirmReservationBtn" class="chk-button chk-button--primary chk-gradient-bg">Confirm Reservation</button>
                 <button id="cancelReservationBtnSecondary" class="chk-button chk-button--secondary">Cancel</button>
            </div>
        </div>
    </div>

    <script src="../../public/js/checkout2.js"></script>
</body>
</html>
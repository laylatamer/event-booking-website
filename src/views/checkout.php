<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Checkout</title>

    <link rel="stylesheet" href="../../public/css/checkout.css">

    <script src="https://unpkg.com/feather-icons"></script>
    <script src="https://cdn.jsdelivr.net/npm/vanta@latest/dist/vanta.net.min.js"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body class="checkout-page">
    <div id="vanta-bg"></div>

    <main class="checkout-container">
        <div class="checkout-content-wrapper">
            <div class="checkout-layout">
                <div class="checkout-layout__order-details">
                    <div class="checkout-header">
                        <h1 class="checkout-header__title">Complete Your Purchase</h1>
                        <p class="checkout-header__subtitle">Review your order details and complete payment</p>
                    </div>

                    <div class="order-summary-card">
                        <h2 class="card-header">Order Summary</h2>
                        <div class="order-items-container" id="orderItems">
                            </div>
                        <div class="totals-summary">
                            <div class="totals-summary__row">
                                <span class="totals-summary__label">Subtotal</span>
                                <span class="totals-summary__value" id="subtotal">$0.00</span>
                            </div>
                            <div class="totals-summary__row">
                                <span class="totals-summary__label">Service Fee</span>
                                <span class="totals-summary__value" id="service-fee">$0.00</span>
                            </div>
                            <div class="totals-summary__row">
                                <span class="totals-summary__label">Processing Fee</span>
                                <span class="totals-summary__value" id="processing-fee">$0.00</span>
                            </div>
                            <div class="totals-summary__row totals-summary__row--total">
                                <span>Total</span>
                                <span id="total">$0.00</span>
                            </div>
                        </div>
                    </div>

                    <div class="attendee-info-card">
                        <h2 class="card-header">User Information</h2>
                        <div class="form-grid">
                            <div class="form-field">
                                <input type="text" class="form-field__input input-field" placeholder=" ">
                                <label class="floating-label">First Name</label>
                            </div>
                            <div class="form-field">
                                <input type="text" class="form-field__input input-field" placeholder=" ">
                                <label class="floating-label">Last Name</label>
                            </div>
                            <div class="form-field form-field--span-2">
                                <input type="email" class="form-field__input input-field" placeholder=" ">
                                <label class="floating-label">Email Address</label>
                            </div>
                            <div class="form-field form-field--span-2">
                                <input type="tel" class="form-field__input input-field" placeholder=" ">
                                <label class="floating-label">Phone Number</label>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="checkout-layout__payment-column">
                    <div class="payment-card payment-section" id="paymentSection">
                        <h2 class="card-header">Payment Method</h2>
                        <div class="payment-method-selector">
                            <div class="payment-method payment-method--active" id="creditCardOption">
                                <div class="payment-method__content">
                                    <div class="payment-method__icon-wrapper">
                                        <i class="fas fa-credit-card payment-method__icon"></i>
                                    </div>
                                    <span class="payment-method__label">Credit/Debit Card</span>
                                </div>
                            </div>
                            <div class="payment-method" id="cashOption">
                                <div class="payment-method__content">
                                    <div class="payment-method__icon-wrapper">
                                        <i class="fas fa-money-bill-wave payment-method__icon"></i>
                                    </div>
                                    <span class="payment-method__label">Pay at Venue</span>
                                </div>
                            </div>
                        </div>

                        <div id="creditCardForm">
                            <div class="card-container">
                                <div class="card" id="creditCard">
                                    <div class="card-front">
                                        <div>
                                            <div class="card-header-flex">
                                                <b><span class="card-issuer">EحGZLY</span></b>
                                            </div>
                                            <div class="card-number" id="displayCardNumber">•••• •••• •••• ••••</div>
                                        </div>
                                        <div class="card-details">
                                            <div class="card-holder" id="displayCardHolder">FULL NAME</div>
                                            <div class="card-expiry" id="displayCardExpiry">MM/YY</div>
                                        </div>
                                    </div>
                                    <div class="card-back">
                                        <div class="card-magnetic-strip"></div>
                                        <div class="card-cvv-strip" id="displayCardCVV">•••</div>
                                        <div class="card-signature">Authorized Signature</div>
                                        <div class="card-hologram"></div>
                                        <i class="fab fa-cc-visa card-brand-back" id="cardBrandIconBack"></i>
                                    </div>
                                </div>
                            </div>
                            <div class="form-field input-highlight" id="cardNumberContainer">
                                <input type="text" id="cardNumber" class="form-field__input input-field" placeholder="1234 5678 9012 3456" maxlength="19">
                                <label class="floating-label">Card Number</label>
                                <span class="form-field__error" id="cardNumberError"></span>
                            </div>
                            <div class="form-field input-highlight" id="cardHolderContainer">
                                <input type="text" id="cardHolder" class="form-field__input input-field" placeholder="As it appears on your card">
                                <label class="floating-label">Card Holder Name</label>
                                <span class="form-field__error" id="cardHolderError"></span>
                            </div>
                            <div class="form-grid form-grid--halves">
                                <div class="form-field input-highlight" id="cardExpiryContainer">
                                    <input type="text" id="cardExpiry" class="form-field__input input-field" placeholder="MM/YY" maxlength="5">
                                    <label class="floating-label">Expiration Date</label>
                                    <span class="form-field__error" id="cardExpiryError"></span>
                                </div>
                                <div class="form-field input-highlight" id="cardCVVContainer">
                                    <input type="text" id="cardCVV" class="form-field__input input-field" placeholder="•••" maxlength="4" onfocus="flipCard(true)" onblur="flipCard(false)">
                                    <label class="floating-label">CVV</label>
                                    <div class="cvv-tooltip-trigger" onclick="showCVVTooltip()">
                                        <i class="fas fa-question-circle"></i>
                                    </div>
                                    <span class="form-field__error" id="cardCVVError"></span>
                                </div>
                            </div>
                            <div class="checkbox-field">
                                <input type="checkbox" id="saveCard" class="checkbox-field__input">
                                <label for="saveCard" class="checkbox-field__label">Save card for future purchases</label>
                            </div>
                        </div>

                        <div id="cashForm" class="hidden">
                            <div class="info-box info-box--warning">
                                <div class="info-box__content">
                                    <i class="fas fa-info-circle info-box__icon"></i>
                                    <p class="info-box__text">You can pay for your tickets when you arrive at the venue. Please bring a valid ID and your booking confirmation.</p>
                                </div>
                            </div>
                        </div>

                        <button id="placeOrderBtn" class="button button--primary button--full-width gradient-bg pulse-animation">
                            <span>Complete Purchase</span>
                            <i class="fas fa-arrow-right button__icon"></i>
                        </button>
                        <div class="form-footer-text">
                            <p>By placing your order, you agree to our <a href="terms.html" class="form-footer-text__link">Terms & Conditions</a></p>
                        </div>
                        <div class="secure-payment-badge">
                            <i class="fas fa-shield-alt secure-payment-badge__icon"></i>
                            <span class="secure-payment-badge__text">Secure Payment Processing</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </main>

    <div id="cvvTooltip" class="modal-overlay hidden">
        <div class="modal-content">
            <button onclick="hideCVVTooltip()" class="modal-close-button">
                <i class="fas fa-times"></i>
            </button>
            <h3 class="modal-header">Where to find your CVV?</h3>
            <p class="modal-text">The CVV is a 3 or 4-digit number on your card that provides extra security.</p>
            <div class="modal-list-item">
                <i class="fas fa-credit-card modal-list-item__icon"></i>
                <span>Visa/Mastercard: 3 digits on back</span>
            </div>
            <div class="modal-list-item">
                <i class="fas fa-credit-card modal-list-item__icon"></i>
                <span>American Express: 4 digits on front</span>
            </div>
        </div>
    </div>
    
    <div id="noticeModal" class="modal-overlay hidden">
        <div class="modal-content">
            <button id="noticeModalCloseBtn" class="modal-close-button">
                <i class="fas fa-times"></i>
            </button>
            <h3 id="noticeModalHeader" class="modal-header">Notice</h3>
            <p id="noticeModalText" class="modal-text">
                This is a generic notice.
            </p>
            <div class="modal-actions">
                 <button id="noticeModalOkBtn" class="button button--primary gradient-bg">OK</button>
            </div>
        </div>
    </div>

    <div id="cashWarningModal" class="modal-overlay hidden">
        <div class="modal-content">
            <button id="cancelReservationBtn" class="modal-close-button">
                <i class="fas fa-times"></i>
            </button>
            <h3 class="modal-header">Reservation Policy</h3>
            <p class="modal-text">
                Please note: If you reserve tickets and do not pay for them at the time of the event, the amount of the tickets reserved must be paid or you will not be able to book another ticket.
            </p>
            <div class="modal-actions">
                 <button id="confirmReservationBtn" class="button button--primary gradient-bg">Confirm Reservation</button>
                 <button id="cancelReservationBtnSecondary" class="button button--secondary">Cancel</button>
            </div>
        </div>
    </div>

    
    <script type="module" src="../../public/css/checkout.js"></script>
    
    
</body>
</html>
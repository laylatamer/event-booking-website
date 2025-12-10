// public/js/utils/app.js - Consent-Aware Version

document.addEventListener('DOMContentLoaded', function() {
    const CONSENT_COOKIE_NAME = 'cookie_consent_status';
    const VISIT_COOKIE_NAME = 'user_has_visited';
    const EXPIRATION_DAYS = 365;
    
    const banner = document.getElementById('cookie-banner');
    const acceptBtn = document.getElementById('cookie-accept-btn');
    const denyBtn = document.getElementById('cookie-deny-btn');

    // Check if the user has already accepted or denied
    const consentStatus = getCookie(CONSENT_COOKIE_NAME);

    if (consentStatus === 'accepted') {
        // Consent given previously, execute the functional cookie logic
        executeFunctionalLogic();
    } else if (consentStatus === 'denied') {
        // Consent denied previously, do nothing or show minimal necessary warning
        console.log("Consent denied. No functional cookies set.");
        // Ensure the banner is hidden if it somehow reappears
        if (banner) banner.classList.add('hidden'); 
    } else {
        // No consent status found, show the banner
        if (banner) {
            banner.classList.remove('hidden');
            // Use a short delay for smooth CSS transition
            setTimeout(() => banner.classList.add('show'), 10); 
        }

        // --- Attach Event Listeners ---
        if (acceptBtn) {
            acceptBtn.addEventListener('click', () => {
                // 1. Set the consent cookie
                setCookie(CONSENT_COOKIE_NAME, 'accepted', EXPIRATION_DAYS);
                // 2. Hide the banner
                if (banner) banner.classList.remove('show');
                // 3. Execute the initial functional cookie logic
                executeFunctionalLogic();
            });
        }
        
        if (denyBtn) {
             denyBtn.addEventListener('click', () => {
                // 1. Set the consent cookie (can be shorter term if needed)
                setCookie(CONSENT_COOKIE_NAME, 'denied', EXPIRATION_DAYS);
                // 2. Hide the banner
                if (banner) banner.classList.remove('show');
                console.log("Consent denied. No functional cookies set.");
            });
        }
    }
    
    // Function that runs ONLY if consent is accepted
    function executeFunctionalLogic() {
        if (!getCookie(VISIT_COOKIE_NAME)) {
            // This is the "done once" logic
            setCookie(VISIT_COOKIE_NAME, 'true', EXPIRATION_DAYS);
            console.log(`[Cookie Set] First visit detected. Functional cookie '${VISIT_COOKIE_NAME}' set.`);
            // ... (Your one-time welcome message code here)
        } else {
            console.log(`[Cookie Found] Welcome back.`);
        }
    }
});
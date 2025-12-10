// public/js/utils/cookie_utils.js

/**
 * Sets a cookie with a name, value, and optional expiration days.
 * @param {string} name - The name of the cookie.
 * @param {string} value - The value of the cookie.
 * @param {number} days - Number of days until the cookie expires.
 */
function setCookie(name, value, days) {
    let expires = "";
    if (days) {
        const date = new Date();
        // Calculate expiration date
        date.setTime(date.getTime() + (days * 24 * 60 * 60 * 1000));
        expires = "; expires=" + date.toUTCString();
    }
    // Set the cookie: path=/ ensures it works across the entire site
    document.cookie = name + "=" + (value || "") + expires + "; path=/; SameSite=Lax";
}

/**
 * Gets the value of a specified cookie.
 * @param {string} name - The name of the cookie to retrieve.
 * @returns {string|null} The cookie value, or null if not found.
 */
function getCookie(name) {
    const nameEQ = name + "=";
    // Split the document.cookie string by semicolons to get individual cookies
    const ca = document.cookie.split(';');
    for(let i = 0; i < ca.length; i++) {
        let c = ca[i];
        // Trim leading spaces
        while (c.charAt(0) === ' ') c = c.substring(1, c.length);
        // Check if this is the cookie we are looking for
        if (c.indexOf(nameEQ) === 0) {
            return c.substring(nameEQ.length, c.length);
        }
    }
    return null;
}
// Footer JavaScript functionality
// This file contains JavaScript that should run on all pages that include the footer

// Back to top functionality
(function() {
    // Find all "back to top" links
    const backToTopLinks = document.querySelectorAll('a[href="#top"]');
    
    if (backToTopLinks.length === 0) return;
    
    // Add smooth scroll behavior to back to top links
    backToTopLinks.forEach(link => {
        link.addEventListener('click', function(e) {
            e.preventDefault();
            
            // Smooth scroll to top
            window.scrollTo({
                top: 0,
                behavior: 'smooth'
            });
        });
    });
    
    // Show/hide back to top button based on scroll position
    const backToTopButton = document.querySelector('a[href="#top"]');
    if (backToTopButton) {
        const footer = document.querySelector('.footer');
        if (footer) {
            const observer = new IntersectionObserver((entries) => {
                entries.forEach(entry => {
                    if (entry.isIntersecting) {
                        // Footer is visible, hide back to top button
                        backToTopButton.style.opacity = '0.7';
                    } else {
                        // Footer is not visible, show back to top button
                        backToTopButton.style.opacity = '1';
                    }
                });
            }, {
                threshold: 0.1
            });
            
            observer.observe(footer);
        }
    }
})();

// Social media link tracking (optional - for analytics)
(function() {
    const socialLinks = document.querySelectorAll('.socials .soc');
    
    socialLinks.forEach(link => {
        link.addEventListener('click', function() {
            const platform = this.getAttribute('aria-label') || 'Unknown';
            console.log(`Social media link clicked: ${platform}`);
            // Here you could add analytics tracking
            // Example: gtag('event', 'click', { event_category: 'social', event_label: platform });
        });
    });
})();

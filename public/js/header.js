// Header JavaScript functionality
// This file contains JavaScript that should run on all pages that include the header

// Navbar initialization
(function() {
    const wrap = document.getElementById('navbarWrap');
    if (!wrap) return;
    wrap.classList.remove('navbar-pinned', 'navbar-compact');
    wrap.style.width = '';
})();

// Dashboard Section Scripts

// Load dashboard data (stats and recent bookings)
function loadDashboardData() {
    // This would typically load from an API
    // For now, we'll just ensure the dashboard data is visible
    const dashboardSection = document.getElementById('dashboard-section');
    if (dashboardSection && dashboardSection.classList.contains('active')) {
        // Stats are already in HTML, no need to load
        // Recent bookings table is already populated in HTML
    }
}

// Initialize dashboard when DOM is ready
document.addEventListener('DOMContentLoaded', function() {
    loadDashboardData();
});


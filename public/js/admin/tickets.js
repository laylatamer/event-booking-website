// Simple tickets management script
document.addEventListener('DOMContentLoaded', function() {
    // Initialize feather icons
    if (typeof feather !== 'undefined') {
        feather.replace();
    }
    
    // Simple filtering functions
    const searchInput = document.getElementById('ticket-search');
    const statusFilter = document.getElementById('ticket-status-filter');
    const eventFilter = document.getElementById('ticket-event-filter');
    const refreshBtn = document.getElementById('refresh-tickets-btn');
    
    // Filter tickets by search term
    function filterBySearch() {
        const searchTerm = searchInput.value.toLowerCase();
        const rows = document.querySelectorAll('#tickets-table-body tr');
        
        rows.forEach(row => {
            const text = row.textContent.toLowerCase();
            row.style.display = text.includes(searchTerm) ? '' : 'none';
        });
    }
    
    // Filter tickets by status
    function filterByStatus() {
        const filterValue = statusFilter.value;
        const rows = document.querySelectorAll('#tickets-table-body tr');
        
        rows.forEach(row => {
            if (!filterValue) {
                row.style.display = '';
                return;
            }
            
            const statusCell = row.querySelector('.status-badge');
            if (statusCell && statusCell.classList.contains(filterValue)) {
                row.style.display = '';
            } else {
                row.style.display = 'none';
            }
        });
    }
    
    // Filter tickets by event
    function filterByEvent() {
        const filterValue = eventFilter.value;
        const rows = document.querySelectorAll('#tickets-table-body tr');
        
        rows.forEach(row => {
            if (!filterValue || filterValue === '') {
                row.style.display = '';
                return;
            }
            
            const eventName = row.querySelector('.event-info strong')?.textContent || '';
            const eventOption = eventFilter.querySelector(`option[value="${filterValue}"]`)?.textContent || '';
            
            if (eventName.includes(eventOption.split(' - ')[0])) {
                row.style.display = '';
            } else {
                row.style.display = 'none';
            }
        });
    }
    
    // Event listeners
    if (searchInput) {
        searchInput.addEventListener('input', filterBySearch);
    }
    
    if (statusFilter) {
        statusFilter.addEventListener('change', filterByStatus);
    }
    
    if (eventFilter) {
        eventFilter.addEventListener('change', filterByEvent);
    }
    
    if (refreshBtn) {
        refreshBtn.addEventListener('click', function() {
            window.location.reload();
        });
    }
    
    // Handle action buttons
    document.addEventListener('click', function(e) {
        // Update status button
        if (e.target.closest('.update-status-btn')) {
            const button = e.target.closest('.update-status-btn');
            const ticketId = button.getAttribute('data-id');
            const ticketName = button.getAttribute('data-name');
            
            alert(`Would update status for ticket: ${ticketName} (ID: ${ticketId})`);
        }
        
        // Delete button
        if (e.target.closest('.delete-ticket')) {
            const button = e.target.closest('.delete-ticket');
            const ticketId = button.getAttribute('data-id');
            const ticketName = button.getAttribute('data-name');
            
            if (confirm(`Are you sure you want to delete ticket: ${ticketName}?`)) {
                alert(`Would delete ticket: ${ticketName} (ID: ${ticketId})`);
            }
        }
    });
});
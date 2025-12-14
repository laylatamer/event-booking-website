(function() {
    // API endpoint for public events
    const API_BASE = '../../public/api/events_API.php';
    
    // State management
    let allEvents = [];
    let activeFilter = {
        date: 'All Dates',
        category: 'All Sports',  // Changed from 'All Categories'
        venue: 'All Venues'
    };

    // State for Date Picker
    let currentCalendarDate = new Date();
    currentCalendarDate.setDate(1);
    let selectedDayElement = null;
    let selectedDateValue = 'All Dates';

    // --- API FUNCTIONS ---

    /**
     * Fetch sports events from API
     */
    async function fetchSportsEvents() {
        try {
            const response = await fetch(`${API_BASE}?action=getByCategory&category_name=Sports`);
            if (!response.ok) {
                throw new Error(`HTTP error! status: ${response.status}`);
            }
            const data = await response.json();
            
            if (data.success) {
                allEvents = data.events || [];
                return allEvents;
            } else {
                console.error('API error:', data.message);
                allEvents = [];
                return [];
            }
        } catch (error) {
            console.error('Error fetching sports events:', error);
            allEvents = [];
            return [];
        }
    }

    /**
     * Fetch event details for blurb modal
     */
    async function fetchEventDetails(eventId) {
        try {
            const response = await fetch(`${API_BASE}?action=getPublicEvent&id=${eventId}`);
            if (!response.ok) {
                throw new Error(`HTTP error! status: ${response.status}`);
            }
            const data = await response.json();
            return data.success ? data.event : null;
        } catch (error) {
            console.error('Error fetching event details:', error);
            return null;
        }
    }

    /**
     * Fetch sports subcategories
     */
    async function fetchSportsSubcategories() {
        try {
            const response = await fetch(`${API_BASE}?action=getSubcategoriesByCategory&category=Sports`);
            if (!response.ok) {
                throw new Error(`HTTP error! status: ${response.status}`);
            }
            const data = await response.json();
            return data.success ? data.subcategories : [];
        } catch (error) {
            console.error('Error fetching sports subcategories:', error);
            return [];
        }
    }

    /**
     * Fetch venues for filter
     */
    async function fetchVenues() {
        try {
            const response = await fetch(`${API_BASE}?action=getVenues`);
            if (!response.ok) {
                throw new Error(`HTTP error! status: ${response.status}`);
            }
            const data = await response.json();
            return data.success ? data.venues : [];
        } catch (error) {
            console.error('Error fetching venues:', error);
            return [];
        }
    }

    // --- UTILITY FUNCTIONS ---

    /**
     * Format date for display
     */
    function formatEventDateForCard(dateString) {
        try {
            const date = new Date(dateString);
            const month = date.toLocaleString('en-US', { month: 'short' });
            const day = date.getDate();
            let hours = date.getHours();
            const minutes = date.getMinutes().toString().padStart(2, '0');
            const ampm = hours >= 12 ? 'PM' : 'AM';
            hours = hours % 12;
            hours = hours ? hours : 12;
            return `${month} ${day} | ${hours}:${minutes} ${ampm}`;
        } catch (e) {
            return 'Date TBD';
        }
    }

    // --- UI FUNCTIONS ---

    /**
     * Shows a modal overlay
     */
    function showModal(modalElement) {
        modalElement.classList.add('active');
        document.body.classList.add('no-scroll');
    }

    /**
     * Hides a modal overlay
     */
    function hideModal(modalElement) {
        modalElement.classList.remove('active');
        document.body.classList.remove('no-scroll');
    }

    /**
     * Updates filter button styles
     */
    function updateFilterButtonStyles() {
        const filters = ['date', 'category', 'venue'];
        filters.forEach(filterType => {
            const btn = document.getElementById(`filter-${filterType}-btn`);
            const value = activeFilter[filterType];
            const icon = { date: 'calendar', category: 'blocks', venue: 'map-pin' }[filterType];
            
            let defaultText = 'All Dates';
            if (filterType === 'category') defaultText = 'All Sports';
            if (filterType === 'venue') defaultText = 'All Venues';

            const isFiltered = value && value !== defaultText;

            if (isFiltered) {
                btn.classList.add('active-filter');
                btn.innerHTML = `<i data-lucide="${icon}"></i> ${value}`;
            } else {
                btn.classList.remove('active-filter');
                btn.innerHTML = `<i data-lucide="${icon}"></i> ${defaultText}`;
            }
        });
        if (typeof lucide !== 'undefined') {
            lucide.createIcons();
        }
    }

    /**
     * Renders event cards based on current filter
     */
    function renderEventCards() {
        const grid = document.getElementById('events-grid');
        if (!grid) return;

        const filteredEvents = allEvents.filter(event => {
            // 1. Filter by Date
            let dateMatch = activeFilter.date === 'All Dates';
            if (activeFilter.date !== 'All Dates' && event.date) {
                try {
                    const eventDate = new Date(event.date);
                    const eventDateString = eventDate.toLocaleString('en-US', { month: 'short', day: 'numeric' });
                    dateMatch = eventDateString.includes(activeFilter.date);
                } catch (e) {
                    console.error("Invalid date format in event:", event.date);
                    dateMatch = false;
                }
            }

            // 2. Filter by Category (Sport type)
            const categoryMatch = activeFilter.category === 'All Sports' || 
                                 event.subcategory === activeFilter.category;

            // 3. Filter by Venue
            const venueMatch = activeFilter.venue === 'All Venues' || 
                              event.location === activeFilter.venue ||
                              event.venue_name === activeFilter.venue;

            return dateMatch && categoryMatch && venueMatch;
        });

        grid.innerHTML = '';

        if (filteredEvents.length === 0) {
            grid.innerHTML = `<div class="loading-indicator">No sports events match your current filter selection. Try resetting filters.</div>`;
            return;
        }

        filteredEvents.forEach(event => {
            const card = document.createElement('div');
            card.className = `event-card-base`;
            card.setAttribute('data-event-id', event.id);
            
            // Format date for display
            const formattedDate = event.formattedDate || formatEventDateForCard(event.date);
            const venueName = event.location || event.venue_name || 'Venue TBD';
            const category = event.subcategory || 'Sport';
            const imageUrl = event.image || event.image_url || `https://placehold.co/400x400/2a2a2a/f97316?text=${encodeURIComponent(category)}`;

            card.innerHTML = `
                <div class="event-image-container">
                    <img src="${imageUrl}" 
                         alt="${event.title}" 
                         class="event-card-img">
                    <span class="event-category-tag">${category}</span>
                </div>
                <div class="event-details">
                    <h3 class="event-title">${event.title}</h3>
                    <p class="event-date">${formattedDate}</p>
                    <p class="event-venue">${venueName}</p>
                    <button class="book-now-button">
                        Book Now
                    </button>
                </div>
            `;
            grid.appendChild(card);
        });

        // Add event listeners to cards
        grid.querySelectorAll('[data-event-id]').forEach(card => {
            card.addEventListener('click', async (e) => {
                if (e.target.classList.contains('book-now-button')) {
                    const eventId = card.getAttribute('data-event-id');
                    window.location.href = `booking.php?id=${eventId}`;
                    return;
                }
                
                const eventId = card.getAttribute('data-event-id');
                await showBlurbModal(eventId);
            });
        });
    }

    /**
     * Shows event details in blurb modal
     */
    async function showBlurbModal(eventId) {
        const modal = document.getElementById('blurb-modal');
        const titleEl = document.getElementById('blurb-modal-title');
        const contentEl = document.getElementById('modal-content');
        
        if (!modal) return;
        
        // Show loading state
        contentEl.innerHTML = `
            <div class="loading-spinner">
                <i data-lucide="loader-circle"></i>
                <p>Loading event details...</p>
            </div>
        `;
        
        if (typeof lucide !== 'undefined') {
            lucide.createIcons();
        }
        
        showModal(modal);
        
        // Fetch event details from API
        const event = await fetchEventDetails(eventId);
        
        if (event) {
            titleEl.textContent = `${event.title} - Details`;
            
            // Format price display
            let priceDisplay = `$${event.price ? parseFloat(event.price).toFixed(2) : '0.00'}`;
            if (event.discounted_price && event.discounted_price < event.price) {
                priceDisplay = `<span style="text-decoration: line-through; color: #999;">$${parseFloat(event.price).toFixed(2)}</span> 
                                <span style="color: #FF5722; font-weight: bold;">$${parseFloat(event.discounted_price).toFixed(2)}</span>`;
            }
            
            contentEl.innerHTML = `
                <div class="blurb-text-box">
                    <div style="margin-bottom: 1rem;">
                        <img src="${event.image || event.image_url || 'https://placehold.co/600x300/2a2a2a/f97316?text=Sport'}" 
                             alt="${event.title}" 
                             style="width:100%;border-radius:8px;margin-bottom:1rem;">
                    </div>
                    
                    <p style="color: #FF5722; font-weight: 700; margin-bottom: 0.5rem;">Event Description:</p>
                    <p style="margin-bottom: 1rem;">${event.description}</p>
                    
                    <div style="display: grid; grid-template-columns: repeat(2, 1fr); gap: 1rem; margin: 1rem 0;">
                        <div style="background: #1a1a1a; padding: 0.75rem; border-radius: 6px;">
                            <p style="font-size: 0.875rem; color: #9ca3af; margin-bottom: 0.25rem;">Date & Time</p>
                            <p style="font-weight: 600;">${event.formattedDateTime || formatEventDateForCard(event.date)}</p>
                        </div>
                        
                        <div style="background: #1a1a1a; padding: 0.75rem; border-radius: 6px;">
                            <p style="font-size: 0.875rem; color: #9ca3af; margin-bottom: 0.25rem;">Sport Type</p>
                            <p style="font-weight: 600;">${event.subcategory || 'Sports'}</p>
                        </div>
                        
                        <div style="background: #1a1a1a; padding: 0.75rem; border-radius: 6px;">
                            <p style="font-size: 0.875rem; color: #9ca3af; margin-bottom: 0.25rem;">Venue</p>
                            <p style="font-weight: 600;">${event.venue?.name || event.location || 'TBD'}</p>
                        </div>
                        
                        <div style="background: #1a1a1a; padding: 0.75rem; border-radius: 6px;">
                            <p style="font-size: 0.875rem; color: #9ca3af; margin-bottom: 0.25rem;">Price</p>
                            <p style="font-weight: 600;">${priceDisplay}</p>
                        </div>
                    </div>
                    
                    ${event.available_tickets ? `
                    <div style="margin: 1rem 0; padding: 0.75rem; background: #1a1a1a; border-radius: 6px;">
                        <p style="font-size: 0.875rem; color: #9ca3af; margin-bottom: 0.25rem;">Tickets Available</p>
                        <p style="font-weight: 600; color: ${event.available_tickets > 10 ? '#10B981' : '#EF4444'}">
                            ${event.available_tickets} / ${event.total_tickets || 'N/A'}
                        </p>
                    </div>
                    ` : ''}
                    
                    <div style="text-align: center; margin-top: 1.5rem;">
                        <button onclick="window.location.href='booking.php?id=${event.id}'" 
                                style="background: #FF5722; color: white; border: none; padding: 0.75rem 2rem; border-radius: 8px; font-weight: 600; cursor: pointer;">
                            Book Now
                        </button>
                    </div>
                </div>
            `;
        } else {
            contentEl.innerHTML = `
                <div class="blurb-text-box">
                    <p style="color: #EF4444; text-align: center; padding: 2rem;">
                        Unable to load event details. Please try again.
                    </p>
                </div>
            `;
        }
        
        // Reinitialize Lucide icons
        if (typeof lucide !== 'undefined') {
            lucide.createIcons();
        }
    }

    /**
     * Renders calendar for date picker
     */
    function renderCalendar() {
        const monthYearDisplay = document.getElementById('current-month-year');
        const daysContainer = document.getElementById('calendar-days');
        const date = currentCalendarDate;
        const today = new Date();
        today.setHours(0, 0, 0, 0);

        monthYearDisplay.textContent = date.toLocaleString('en-US', { month: 'long', year: 'numeric' });

        const firstDayOfMonth = new Date(date.getFullYear(), date.getMonth(), 1);
        const startingDayOfWeek = firstDayOfMonth.getDay(); 
        const daysInMonth = new Date(date.getFullYear(), date.getMonth() + 1, 0).getDate();

        daysContainer.innerHTML = ''; 

        // Add blank tiles for preceding days
        for (let i = 0; i < startingDayOfWeek; i++) {
            daysContainer.innerHTML += '<div></div>';
        }

        // Add day tiles
        for (let day = 1; day <= daysInMonth; day++) {
            const currentDay = new Date(date.getFullYear(), date.getMonth(), day);
            const isCurrentMonth = currentDay.getMonth() === date.getMonth();
            
            let classes = 'day-tile';

            const isToday = currentDay.toLocaleDateString() === today.toLocaleDateString() && isCurrentMonth;
            
            if (isCurrentMonth) {
                if (isToday) {
                    classes += ' is-today';
                } else {
                    classes += ' is-active-month';
                }
            }

            // Check if this day matches the currently applied filter value
            const month = currentDay.toLocaleString('en-US', { month: 'short' });
            const dateFilterValue = `${month} ${day}`;

            if (activeFilter.date === dateFilterValue) {
                classes += ' is-selected';
            }

            const dayElement = `<div class="${classes}" data-day="${day}" data-month="${date.getMonth()}" data-year="${date.getFullYear()}">${day}</div>`;
            daysContainer.innerHTML += dayElement;
        }
    }
    
    /**
     * Resets all filters
     */
    function resetFilters() {
        activeFilter = {
            date: 'All Dates',
            category: 'All Sports',
            venue: 'All Venues'
        };

        // Clear visual date selection
        if (selectedDayElement) {
            selectedDayElement.classList.remove('is-selected');
            selectedDayElement = null;
        }
        
        // Clear any temporary selections in list modals
        document.querySelectorAll('.filter-list-item').forEach(item => item.classList.remove('selected'));

        // Refresh UI
        updateFilterButtonStyles();
        renderEventCards();
    }

    /**
     * Populates sports subcategories modal with dynamic data
     */
    async function populateSportsSubcategoriesModal() {
        const container = document.getElementById('category-list-container');
        if (!container) return;
        
        const subcategories = await fetchSportsSubcategories();
        
        let html = '<div class="filter-list-item" data-value="">All Sports</div>';
        subcategories.forEach(subcat => {
            if (subcat.event_count > 0) { // Only show if there are events
                html += `<div class="filter-list-item" data-value="${subcat.name}">
                            ${subcat.name} (${subcat.event_count})
                         </div>`;
            }
        });
        
        container.innerHTML = html;
    }

    /**
     * Populates venue modal with dynamic data
     */
    async function populateVenueModal() {
        const container = document.getElementById('venue-list-container');
        if (!container) return;
        
        const venues = await fetchVenues();
        
        let html = '<div class="filter-list-item" data-value="">All Venues</div>';
        venues.forEach(venue => {
            html += `<div class="filter-list-item" data-value="${venue.name}">${venue.name} (${venue.city})</div>`;
        });
        
        container.innerHTML = html;
    }

    // --- INITIALIZATION ---

    document.addEventListener('DOMContentLoaded', async () => {
        // Show loading state
        const grid = document.getElementById('events-grid');
        if (grid) {
            grid.innerHTML = '<div class="loading-indicator">Loading sports events...</div>';
        }
        
        // Fetch events from API
        await fetchSportsEvents();
        
        // Initial render
        updateFilterButtonStyles();
        renderEventCards();
        
        // Populate modals with dynamic data
        await populateSportsSubcategoriesModal();
        await populateVenueModal();

        const modalButtons = document.querySelectorAll('[data-filter-type]');
        const closeButtons = document.querySelectorAll('.modal-close-btn, .modal-footer button[data-modal-id]');
        const applyButtons = document.querySelectorAll('.apply-filter-btn');

        // 1. Modal Toggle Logic
        modalButtons.forEach(button => {
            button.addEventListener('click', async () => {
                const filterType = button.getAttribute('data-filter-type');
                const modalId = `${filterType}-modal`;
                const modal = document.getElementById(modalId);
                
                if (modal && filterType !== 'reset') {
                    showModal(modal);
                    if (filterType === 'date') {
                        renderCalendar();
                    }
                }
            });
        });

        // Close modals
        closeButtons.forEach(button => {
            button.addEventListener('click', () => {
                const modalId = button.getAttribute('data-modal-id');
                const modal = document.getElementById(modalId) || button.closest('.modal-overlay');
                if (modal) {
                    hideModal(modal);
                }
            });
        });

        // 2. Date Modal Logic
        const dateModal = document.getElementById('date-modal');
        if (dateModal) {
            // Handle day selection
            dateModal.addEventListener('click', (e) => {
                const target = e.target.closest('.day-tile');
                if (target && (target.classList.contains('is-active-month') || target.classList.contains('is-today'))) {
                    
                    // Reset previous selection style
                    if (selectedDayElement) {
                        selectedDayElement.classList.remove('is-selected');
                    }
                    // Set new selection style
                    selectedDayElement = target;
                    selectedDayElement.classList.add('is-selected');

                    // Update temporary selected date value
                    const day = selectedDayElement.getAttribute('data-day');
                    const monthIndex = parseInt(selectedDayElement.getAttribute('data-month'));
                    const month = new Date(0, monthIndex).toLocaleString('en-US', { month: 'short' });
                    selectedDateValue = `${month} ${day}`;
                }
            });

            // Month navigation
            document.getElementById('prev-month')?.addEventListener('click', () => {
                currentCalendarDate.setMonth(currentCalendarDate.getMonth() - 1);
                renderCalendar();
            });
            document.getElementById('next-month')?.addEventListener('click', () => {
                currentCalendarDate.setMonth(currentCalendarDate.getMonth() + 1);
                renderCalendar();
            });
        }

        // 3. Category/Venue Filter Logic
        function setupFilterSelection(containerId, filterType) {
            const container = document.getElementById(containerId);
            if (!container) return;

            // Clear and set selection style based on current activeFilter state
            const allItems = container.querySelectorAll('.filter-list-item');
            allItems.forEach(item => {
                item.classList.remove('selected');
                if (item.getAttribute('data-value') === activeFilter[filterType]) {
                    item.classList.add('selected');
                }
            });

            // Add click handler for selection change
            container.onclick = (e) => {
                let target = e.target.closest('.filter-list-item');
                if (target) {
                    // Deselect previous item in the list
                    allItems.forEach(item => item.classList.remove('selected'));
                    // Select new item
                    target.classList.add('selected');
                    // Update the main state immediately for list selections
                    activeFilter[filterType] = target.getAttribute('data-value');
                }
            };
        }

        // 4. Apply Button Logic
        applyButtons.forEach(btn => {
            btn.addEventListener('click', () => {
                const modal = btn.closest('.modal-overlay');
                const filterType = btn.getAttribute('data-filter-type');
                
                if (filterType === 'date' && selectedDayElement) {
                    // Apply the date selected in the calendar
                    activeFilter.date = selectedDateValue;
                } else if (filterType === 'date' && !selectedDayElement) {
                    // If no day is selected in the calendar but 'Apply' is clicked, reset to default 'All Dates'
                    activeFilter.date = 'All Dates';
                }
                // Category/Venue state is updated directly by list selection handler

                // Refresh UI
                updateFilterButtonStyles();
                renderEventCards();
                
                hideModal(modal);
            });
        });

        // 5. Reset Button Logic
        document.getElementById('reset-filters-btn')?.addEventListener('click', resetFilters);

        // Add click handlers for filter list items after they're populated
        const categoryContainer = document.getElementById('category-list-container');
        const venueContainer = document.getElementById('venue-list-container');
        
        if (categoryContainer) {
            categoryContainer.addEventListener('click', (e) => {
                const target = e.target.closest('.filter-list-item');
                if (target) {
                    setupFilterSelection('category-list-container', 'category');
                }
            });
        }
        
        if (venueContainer) {
            venueContainer.addEventListener('click', (e) => {
                const target = e.target.closest('.filter-list-item');
                if (target) {
                    setupFilterSelection('venue-list-container', 'venue');
                }
            });
        }
    });
})();
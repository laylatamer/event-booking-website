  (function() {
            // --- MOCK DATA ---
            const mockEvents = [
    { 
        id: 1, 
        title: "Nightlife Fest", 
        date: "Oct 26 | 11:00 PM", 
        venue: "AUC Tahrir", 
        category: "Nightlife", 
        image: "https://images.unsplash.com/photo-1506157786151-b8491531f063?w=400&h=400&fit=crop"
    },
    { 
        id: 2, 
        title: "Cairo EDM Massive", 
        date: "Oct 20 | 11:00 PM", 
        venue: "The Temple Rooftop", 
        category: "DJ Set", 
        image: "https://images.unsplash.com/photo-1514525253161-7a46d19cd819?w=400&h=400&fit=crop"
    },
    { 
        id: 3, 
        title: "Underground Tech", 
        date: "Oct 22 | 12:00 AM", 
        venue: "The Vault Club", 
        category: "Techno", 
        image: "https://images.unsplash.com/photo-1506157786151-b8491531f063?w=400&h=400&fit=crop"
    },
    { 
        id: 4, 
        title: "Ladies Night House", 
        date: "Oct 23 | 10:30 PM", 
        venue: "Giza Sky Bar", 
        category: "House Music", 
        image: "https://images.unsplash.com/photo-1519677100203-a0e668c92439?w=400&h=400&fit=crop"
    },
    { 
        id: 5, 
        title: "Hip Hop Rhythms", 
        date: "Oct 24 | 10:00 PM", 
        venue: "The Dock Studio", 
        category: "Hip Hop", 
        image: "https://images.unsplash.com/photo-1521334884684-d80222895322?w=400&h=400&fit=crop"
    },
    { 
        id: 6, 
        title: "Friday Sunset Beats", 
        date: "Oct 25 | 06:00 PM", 
        venue: "Sahl Hasheesh Beach", 
        category: "Beach Party", 
        image: "https://images.unsplash.com/photo-1507525428034-b723cf961d3e?w=400&h=400&fit=crop"
    },
];


            let activeFilter = {
                date: 'All Dates', // Default state for date filter
                category: 'All Categories', // Default state for category filter
                venue: 'All Venues' // Default state for venue filter
            };

            // State for Date Picker
            let currentCalendarDate = new Date();
            currentCalendarDate.setDate(1); // Always start at the 1st of the month for calculation
            let selectedDayElement = null; // Stores the currently selected day tile element
            let selectedDateValue = 'All Dates'; // Stores the temporary date selection value


            // --- UI FUNCTIONS ---

            /**
             * Shows a modal overlay and prevents background scrolling.
             * @param {HTMLElement} modalElement The modal to display.
             */
            function showModal(modalElement) {
                modalElement.classList.add('active');
                document.body.classList.add('no-scroll');
            }

            /**
             * Hides a modal overlay and re-enables background scrolling.
             * @param {HTMLElement} modalElement The modal to hide.
             */
            function hideModal(modalElement) {
                modalElement.classList.remove('active');
                document.body.classList.remove('no-scroll');
            }

            /**
             * Updates the visual style of the filter buttons based on activeFilter state.
             */
            function updateFilterButtonStyles() {
                const filters = ['date', 'category', 'venue'];
                filters.forEach(filterType => {
                    const btn = document.getElementById(`filter-${filterType}-btn`);
                    const value = activeFilter[filterType];
                    const icon = { date: 'calendar', category: 'blocks', venue: 'map-pin' }[filterType];
                    
                    let defaultText = 'All Dates';
                    if (filterType === 'category') defaultText = 'All Categories';
                    if (filterType === 'venue') defaultText = 'All Venues';

                    // Determine if the filter is actively filtering (not set to default 'All' value)
                    const isFiltered = value && value !== defaultText;

                    if (isFiltered) {
                        btn.classList.add('active-filter');
                        btn.innerHTML = `<i data-lucide="${icon}"></i> ${value}`;
                    } else {
                        btn.classList.remove('active-filter');
                        btn.innerHTML = `<i data-lucide="${icon}"></i> ${defaultText}`;
                    }
                });
                // Re-initialize Lucide icons
                if (typeof lucide !== 'undefined') {
                    lucide.createIcons();
                }
            }


            /**
             * Renders event cards based on the current filter settings.
             */
            function renderEventCards() {
                const grid = document.getElementById('events-grid');
                if (!grid) return;

                const filteredEvents = mockEvents.filter(event => {
                    
                    // Filter by date (simplified simulation)
                    let dateMatch = activeFilter.date === 'All Dates';
                    if (activeFilter.date !== 'All Dates') {
                        // Match by simplified date string (e.g., "Oct 21")
                        dateMatch = event.date.includes(activeFilter.date);
                    }

                    const categoryMatch = activeFilter.category === 'All Categories' || event.category === activeFilter.category;
                    const venueMatch = activeFilter.venue === 'All Venues' || event.venue === activeFilter.venue;

                    return dateMatch && categoryMatch && venueMatch;
                });

                grid.innerHTML = ''; // Clear existing content

                if (filteredEvents.length === 0) {
                    grid.innerHTML = `<div class="loading-indicator">No events match your current filter selection. Try resetting filters.</div>`;
                    return;
                }

                filteredEvents.forEach(event => {
                    const card = document.createElement('div');
                    card.className = `event-card-base`;
                    card.setAttribute('data-event-id', event.id);
                    card.innerHTML = `
                        <div class="event-image-container">
                            <img src="${event.image}" onerror="this.onerror=null; this.src='https://placehold.co/400x400/2a2a2a/f97316?text=${event.category}'" alt="${event.title}" class="event-card-img">
                            <span class="event-category-tag">${event.category}</span>
                        </div>
                        <div class="event-details">
                            <h3 class="event-title">${event.title}</h3>
                            <p class="event-date">
                                ${event.date}
                            </p>
                            <p class="event-venue">
                                ${event.venue}
                            </p>
                            <button class="book-now-button">
                                Book Now
                            </button>
                        </div>
                    `;
                    grid.appendChild(card);
                });

                // Add event listeners to show the blurb modal
                grid.querySelectorAll('[data-event-id]').forEach(card => {
                    card.addEventListener('click', (e) => {
                        // Prevent opening the blurb if the 'Book Now' button was clicked
                        if (e.target.classList.contains('book-now-button')) {
                            console.log('Book Now clicked - simulating navigation/booking.');
                            return;
                        }
                        showBlurbModal(card.getAttribute('data-event-id'));
                    });
                });
            }

            function showBlurbModal(eventId) {
                const modal = document.getElementById('blurb-modal');
                const titleEl = document.getElementById('blurb-modal-title');
                const contentEl = document.getElementById('modal-content');
                const event = mockEvents.find(e => e.id == eventId);

                if (!modal || !event) return;

                titleEl.textContent = `${event.title} - Promo`;
                contentEl.innerHTML = `
                    <div class="blurb-text-box">
                        <p style="color: #FF5722; font-weight: 700; margin-bottom: 0.5rem;">Event Details:</p>
                        <p style="margin-bottom: 1rem;">Don't miss the thrilling experience of <span style="font-weight: 600;">${event.title}</span>! Set on <span style="color: #f97316;">${event.date}</span> at <span style="color: #f97316;">${event.venue}</span>, it's the highlight of the week for ${event.category} lovers.</p>
                        <p style="font-size: 0.875rem; color: #9ca3af;">This is a simulated promotional blurb. Click "Got it!" to close.</p>
                    </div>
                `;

                showModal(modal);
            }

            /**
             * Renders the calendar grid for the current month.
             */
            function renderCalendar() {
                const monthYearDisplay = document.getElementById('current-month-year');
                const daysContainer = document.getElementById('calendar-days');
                const date = currentCalendarDate;
                const today = new Date();
                today.setHours(0, 0, 0, 0); // Normalize today's date

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
                    } else {
                        classes += ' is-inactive-month';
                    }

                    // Check if this day matches the currently applied filter value
                    const month = currentDay.toLocaleString('en-US', { month: 'short' });
                    const dateValue = `${month} ${day}`;

                    if (activeFilter.date === dateValue) {
                        classes += ' is-selected';
                        // Keep track of the selected element if it's the active filter
                        // This helps persistence when navigating months
                        if (!selectedDayElement || selectedDayElement.getAttribute('data-day') != day) {
                            // Find or create the reference to the element
                            // This part is tricky to do accurately without rendering, so we rely on the click handler to update selectedDayElement
                        }
                    }

                    const dayElement = `<div class="${classes}" data-day="${day}" data-month="${date.getMonth()}" data-year="${date.getFullYear()}">${day}</div>`;
                    daysContainer.innerHTML += dayElement;
                }
            }
            
            /**
             * Resets all filters to their default "All..." state.
             */
            function resetFilters() {
                activeFilter = {
                    date: 'All Dates',
                    category: 'All Categories',
                    venue: 'All Venues'
                };

                // Clear visual date selection
                if (selectedDayElement) {
                    selectedDayElement.classList.remove('is-selected');
                    selectedDayElement = null;
                }
                
                // Clear any temporary selections in list modals (for safety)
                document.querySelectorAll('.filter-list-item').forEach(item => item.classList.remove('selected'));

                // Refresh UI and cards
                updateFilterButtonStyles();
                renderEventCards();
            }


            // --- INITIALIZATION AND EVENT LISTENERS ---

            document.addEventListener('DOMContentLoaded', () => {
                // Initial render of events and filter styles
                updateFilterButtonStyles();
                renderEventCards();

                const modalButtons = document.querySelectorAll('[data-filter-type]');
                const closeButtons = document.querySelectorAll('.modal-close-btn, .modal-footer button[data-modal-id]');
                const applyButtons = document.querySelectorAll('.apply-filter-btn');

                // 1. Modal Toggle Logic
                modalButtons.forEach(button => {
                    button.addEventListener('click', () => {
                        const filterType = button.getAttribute('data-filter-type');
                        const modalId = `${filterType}-modal`;
                        const modal = document.getElementById(modalId);
                        
                        if (modal && filterType !== 'reset') { // Do not open modal if it's the reset button
                            showModal(modal);
                            if (filterType === 'date') {
                                renderCalendar();
                            } else {
                                // Ensure list selection visual state matches the current filter state
                                setupFilterSelection(`${filterType}-list-container`, filterType);
                            }
                        }
                    });
                });

                // Attach click listeners to close modals
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

                    // Month navigation (Prev/Next)
                    document.getElementById('prev-month')?.addEventListener('click', () => {
                        currentCalendarDate.setMonth(currentCalendarDate.getMonth() - 1);
                        renderCalendar();
                    });
                    document.getElementById('next-month')?.addEventListener('click', () => {
                        currentCalendarDate.setMonth(currentCalendarDate.getMonth() + 1);
                        renderCalendar();
                    });
                }

                // 3. Category/Venue Filter Logic (List items)
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

                    // Add click handler for selection change (updates activeFilter directly)
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

                // 5. Reset Button Logic (NEW)
                document.getElementById('reset-filters-btn')?.addEventListener('click', resetFilters);
            });
        })();


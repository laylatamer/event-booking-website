// Events Section Scripts

let events = [
    { id: 1, name: 'Summer Music Festival', category: 'music', date: 'Jun 15, 2023', location: 'Central Park', status: 'active', image: null },
    { id: 2, name: 'Tech Conference 2023', category: 'technology', date: 'Jun 12, 2023', location: 'Convention Center', status: 'active', image: null },
    { id: 3, name: 'Art Exhibition', category: 'art', date: 'Jun 10, 2023', location: 'Museum of Modern Art', status: 'active', image: null }
];

let currentPage = { events: 1 };
const itemsPerPage = 4;

document.addEventListener('DOMContentLoaded', function() {
    initializeEventsEventListeners();
    loadEvents();
});

function initializeEventsEventListeners() {
    const addEventBtn = document.getElementById('add-event-btn');
    if (addEventBtn) {
        addEventBtn.addEventListener('click', () => openModal('add-event'));
    }
    
    const addEventForm = document.getElementById('add-event-form');
    if (addEventForm) {
        addEventForm.addEventListener('submit', handleAddEvent);
    }
    
    const editEventForm = document.getElementById('edit-event-form');
    if (editEventForm) {
        editEventForm.addEventListener('submit', handleEditEvent);
    }
    
    const eventSearch = document.getElementById('event-search');
    if (eventSearch) {
        eventSearch.addEventListener('input', filterEvents);
    }
    
    const eventCategoryFilter = document.getElementById('event-category-filter');
    if (eventCategoryFilter) {
        eventCategoryFilter.addEventListener('change', filterEvents);
    }
    
    const eventsPrev = document.getElementById('events-prev');
    const eventsNext = document.getElementById('events-next');
    if (eventsPrev) eventsPrev.addEventListener('click', () => changePage('events', -1));
    if (eventsNext) eventsNext.addEventListener('click', () => changePage('events', 1));
}

function loadEvents() {
    const eventsGrid = document.getElementById('events-grid');
    if (!eventsGrid) return;
    
    eventsGrid.innerHTML = '';

    if (events.length === 0) {
        eventsGrid.innerHTML = `
            <div class="empty-state" style="grid-column: 1 / -1;">
                <i data-feather="calendar"></i>
                <p>No events found</p>
            </div>
        `;
        feather.replace();
        return;
    }

    const startIndex = (currentPage.events - 1) * itemsPerPage;
    const endIndex = startIndex + itemsPerPage;
    const eventsToShow = events.slice(startIndex, endIndex);
    
    eventsToShow.forEach(event => {
        const eventCard = document.createElement('div');
        eventCard.className = 'event-card';
        eventCard.innerHTML = `
            <div class="event-image">
                ${event.image ? 
                    `<img src="${event.image}" alt="${event.name}" style="width: 100%; height: 100%; object-fit: cover;">` : 
                    `<i data-feather="image"></i>`
                }
            </div>
            <div class="event-content">
                <h3 class="event-title">${event.name}</h3>
                <p class="event-meta">${event.date} • ${event.location}</p>
                <div class="event-actions">
                    <span class="status-badge ${event.status}">${event.status.charAt(0).toUpperCase() + event.status.slice(1)}</span>
                    <div class="action-buttons">
                        <button class="action-btn edit-event" data-id="${event.id}">
                            <i data-feather="edit-2"></i>
                        </button>
                        <button class="action-btn delete delete-event" data-id="${event.id}">
                            <i data-feather="trash-2"></i>
                        </button>
                    </div>
                </div>
            </div>
        `;
        eventsGrid.appendChild(eventCard);
    });
    
    document.getElementById('events-start').textContent = startIndex + 1;
    document.getElementById('events-end').textContent = Math.min(endIndex, events.length);
    document.getElementById('events-total').textContent = events.length;
    
    updatePaginationButtons('events', events.length, itemsPerPage);
    
    feather.replace();
    document.querySelectorAll('.edit-event').forEach(button => {
        button.addEventListener('click', function() {
            const eventId = parseInt(this.getAttribute('data-id'));
            editEvent(eventId);
        });
    });
    
    document.querySelectorAll('.delete-event').forEach(button => {
        button.addEventListener('click', function() {
            const eventId = parseInt(this.getAttribute('data-id'));
            deleteEvent(eventId);
        });
    });
}

function filterEvents() {
    const searchTerm = document.getElementById('event-search').value.toLowerCase();
    const categoryFilter = document.getElementById('event-category-filter').value;
    
    const filteredEvents = events.filter(event => {
        const matchesSearch = event.name.toLowerCase().includes(searchTerm) || 
                             event.location.toLowerCase().includes(searchTerm);
        const matchesCategory = categoryFilter === 'All Categories' || event.category === categoryFilter.toLowerCase();
        
        return matchesSearch && matchesCategory;
    });
    
    const eventsGrid = document.getElementById('events-grid');
    eventsGrid.innerHTML = '';
    
    const startIndex = (currentPage.events - 1) * itemsPerPage;
    const endIndex = startIndex + itemsPerPage;
    const eventsToShow = filteredEvents.slice(startIndex, endIndex);
    
    eventsToShow.forEach(event => {
        const eventCard = document.createElement('div');
        eventCard.className = 'event-card';
        eventCard.innerHTML = `
            <div class="event-image">
                ${event.image ? 
                    `<img src="${event.image}" alt="${event.name}" style="width: 100%; height: 100%; object-fit: cover;">` : 
                    `<i data-feather="image"></i>`
                }
            </div>
            <div class="event-content">
                <h3 class="event-title">${event.name}</h3>
                <p class="event-meta">${event.date} • ${event.location}</p>
                <div class="event-actions">
                    <span class="status-badge ${event.status}">${event.status.charAt(0).toUpperCase() + event.status.slice(1)}</span>
                    <div class="action-buttons">
                        <button class="action-btn edit-event" data-id="${event.id}">
                            <i data-feather="edit-2"></i>
                        </button>
                        <button class="action-btn delete delete-event" data-id="${event.id}">
                            <i data-feather="trash-2"></i>
                        </button>
                    </div>
                </div>
            </div>
        `;
        eventsGrid.appendChild(eventCard);
    });
    
    document.getElementById('events-start').textContent = filteredEvents.length > 0 ? startIndex + 1 : 0;
    document.getElementById('events-end').textContent = Math.min(endIndex, filteredEvents.length);
    document.getElementById('events-total').textContent = filteredEvents.length;
    
    updatePaginationButtons('events', filteredEvents.length, itemsPerPage);
    
    feather.replace();
    document.querySelectorAll('.edit-event').forEach(button => {
        button.addEventListener('click', function() {
            const eventId = parseInt(this.getAttribute('data-id'));
            editEvent(eventId);
        });
    });
    
    document.querySelectorAll('.delete-event').forEach(button => {
        button.addEventListener('click', function() {
            const eventId = parseInt(this.getAttribute('data-id'));
            deleteEvent(eventId);
        });
    });
}

function handleAddEvent(e) {
    e.preventDefault();
    
    const name = document.getElementById('event-name').value;
    const category = document.getElementById('event-category').value;
    const startDate = document.getElementById('event-start-date').value;
    const endDate = document.getElementById('event-end-date').value;
    const location = document.getElementById('event-location').value;
    const organizer = document.getElementById('event-organizer').value;
    const description = document.getElementById('event-description').value;
    const imageFile = document.getElementById('event-image').files[0];
    
    const formattedDate = new Date(startDate).toLocaleDateString('en-US', { 
        year: 'numeric', 
        month: 'short', 
        day: 'numeric' 
    });
    
    const newEvent = {
        id: events.length > 0 ? Math.max(...events.map(e => e.id)) + 1 : 1,
        name,
        category,
        date: formattedDate,
        location: document.querySelector(`#event-location option[value="${location}"]`).textContent,
        organizer,
        description,
        status: 'active',
        image: imageFile ? URL.createObjectURL(imageFile) : null
    };
    
    events.push(newEvent);
    loadEvents();
    closeModal('add-event');
    alert('Event added successfully!');
}

function editEvent(eventId) {
    const event = events.find(e => e.id === eventId);
    if (!event) return;
    
    document.getElementById('edit-event-id').value = event.id;
    document.getElementById('edit-event-name').value = event.name;
    document.getElementById('edit-event-category').value = event.category;
    document.getElementById('edit-event-start-date').value = '2023-06-15';
    document.getElementById('edit-event-end-date').value = '2023-06-15';
    document.getElementById('edit-event-location').value = event.location.toLowerCase().replace(' ', '-');
    document.getElementById('edit-event-organizer').value = event.organizer;
    document.getElementById('edit-event-description').value = event.description || '';
    
    openModal('edit-event');
}

function handleEditEvent(e) {
    e.preventDefault();
    
    const eventId = parseInt(document.getElementById('edit-event-id').value);
    const name = document.getElementById('edit-event-name').value;
    const category = document.getElementById('edit-event-category').value;
    const startDate = document.getElementById('edit-event-start-date').value;
    const endDate = document.getElementById('edit-event-end-date').value;
    const location = document.getElementById('edit-event-location').value;
    const organizer = document.getElementById('edit-event-organizer').value;
    const description = document.getElementById('edit-event-description').value;
    const imageFile = document.getElementById('edit-event-image').files[0];
    
    const formattedDate = new Date(startDate).toLocaleDateString('en-US', { 
        year: 'numeric', 
        month: 'short', 
        day: 'numeric' 
    });
    
    const eventIndex = events.findIndex(e => e.id === eventId);
    if (eventIndex !== -1) {
        events[eventIndex].name = name;
        events[eventIndex].category = category;
        events[eventIndex].date = formattedDate;
        events[eventIndex].location = document.querySelector(`#edit-event-location option[value="${location}"]`).textContent;
        events[eventIndex].organizer = organizer;
        events[eventIndex].description = description;
        
        if (imageFile) {
            events[eventIndex].image = URL.createObjectURL(imageFile);
        }
        
        loadEvents();
        closeModal('edit-event');
        alert('Event updated successfully!');
    }
}

function deleteEvent(eventId) {
    showConfirmation('Are you sure you want to delete this event?', () => {
        const eventIndex = events.findIndex(e => e.id === eventId);
        if (eventIndex !== -1) {
            events.splice(eventIndex, 1);
            loadEvents();
            alert('Event deleted successfully!');
        }
    });
}

function changePage(section, direction) {
    const totalItems = events.length;
    const totalPages = Math.ceil(totalItems / itemsPerPage);
    
    currentPage[section] += direction;
    
    if (currentPage[section] < 1) {
        currentPage[section] = 1;
    } else if (currentPage[section] > totalPages) {
        currentPage[section] = totalPages;
    }
    
    loadEvents();
}


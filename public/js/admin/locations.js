// Locations Section Scripts

let locations = [
    { id: 1, name: 'Central Park', address: 'New York, NY', capacity: 5000, image: null },
    { id: 2, name: 'Convention Center', address: 'Chicago, IL', capacity: 10000, image: null },
    { id: 3, name: 'Museum of Modern Art', address: 'San Francisco, CA', capacity: 1200, image: null }
];

let currentPage = { locations: 1 };
const itemsPerPage = 4;

document.addEventListener('DOMContentLoaded', function() {
    initializeLocationsEventListeners();
    loadLocations();
});

function initializeLocationsEventListeners() {
    const addLocationBtn = document.getElementById('add-location-btn');
    if (addLocationBtn) {
        addLocationBtn.addEventListener('click', () => openModal('add-location'));
    }
    
    const addLocationForm = document.getElementById('add-location-form');
    if (addLocationForm) {
        addLocationForm.addEventListener('submit', handleAddLocation);
    }
    
    const editLocationForm = document.getElementById('edit-location-form');
    if (editLocationForm) {
        editLocationForm.addEventListener('submit', handleEditLocation);
    }
    
    const locationsPrev = document.getElementById('locations-prev');
    const locationsNext = document.getElementById('locations-next');
    if (locationsPrev) locationsPrev.addEventListener('click', () => changePage('locations', -1));
    if (locationsNext) locationsNext.addEventListener('click', () => changePage('locations', 1));
}

function loadLocations() {
    const locationsGrid = document.getElementById('locations-grid');
    if (!locationsGrid) return;
    
    locationsGrid.innerHTML = '';

    if (locations.length === 0) {
        locationsGrid.innerHTML = `
            <div class="empty-state" style="grid-column: 1 / -1;">
                <i data-feather="map-pin"></i>
                <p>No locations found</p>
            </div>
        `;
        feather.replace();
        return;
    }
    
    const startIndex = (currentPage.locations - 1) * itemsPerPage;
    const endIndex = startIndex + itemsPerPage;
    const locationsToShow = locations.slice(startIndex, endIndex);
    
    locationsToShow.forEach(location => {
        const locationCard = document.createElement('div');
        locationCard.className = 'location-card';
        locationCard.innerHTML = `
            <div class="location-image">
                ${location.image ? 
                    `<img src="${location.image}" alt="${location.name}" style="width: 100%; height: 100%; object-fit: cover;">` : 
                    `<i data-feather="map-pin"></i>`
                }
            </div>
            <div class="location-content">
                <h3 class="location-title">${location.name}</h3>
                <p class="location-meta">${location.address}</p>
                <div class="location-info">
                    <span class="location-capacity">Capacity: ${location.capacity.toLocaleString()}</span>
                    <div class="action-buttons">
                        <button class="action-btn edit-location" data-id="${location.id}">
                            <i data-feather="edit-2"></i>
                        </button>
                        <button class="action-btn delete delete-location" data-id="${location.id}">
                            <i data-feather="trash-2"></i>
                        </button>
                    </div>
                </div>
            </div>
        `;
        locationsGrid.appendChild(locationCard);
    });
    
    document.getElementById('locations-start').textContent = startIndex + 1;
    document.getElementById('locations-end').textContent = Math.min(endIndex, locations.length);
    document.getElementById('locations-total').textContent = locations.length;
    
    updatePaginationButtons('locations', locations.length, itemsPerPage);
    
    feather.replace();
    document.querySelectorAll('.edit-location').forEach(button => {
        button.addEventListener('click', function() {
            const locationId = parseInt(this.getAttribute('data-id'));
            editLocation(locationId);
        });
    });
    
    document.querySelectorAll('.delete-location').forEach(button => {
        button.addEventListener('click', function() {
            const locationId = parseInt(this.getAttribute('data-id'));
            deleteLocation(locationId);
        });
    });
}

function handleAddLocation(e) {
    e.preventDefault();
    
    const name = document.getElementById('location-name').value;
    const address = document.getElementById('location-address').value;
    const city = document.getElementById('location-city').value;
    const state = document.getElementById('location-state').value;
    const capacity = parseInt(document.getElementById('location-capacity').value);
    const imageFile = document.getElementById('location-image').files[0];
    
    const newLocation = {
        id: locations.length > 0 ? Math.max(...locations.map(l => l.id)) + 1 : 1,
        name,
        address: `${address}, ${city}, ${state}`,
        capacity,
        image: imageFile ? URL.createObjectURL(imageFile) : null
    };
    
    locations.push(newLocation);
    loadLocations();
    closeModal('add-location');
    alert('Location added successfully!');
}

function editLocation(locationId) {
    const location = locations.find(l => l.id === locationId);
    if (!location) return;
    
    const addressParts = location.address.split(', ');
    
    document.getElementById('edit-location-id').value = location.id;
    document.getElementById('edit-location-name').value = location.name;
    document.getElementById('edit-location-address').value = addressParts[0];
    document.getElementById('edit-location-city').value = addressParts[1];
    document.getElementById('edit-location-state').value = addressParts[2];
    document.getElementById('edit-location-capacity').value = location.capacity;
    
    openModal('edit-location');
}

function handleEditLocation(e) {
    e.preventDefault();
    
    const locationId = parseInt(document.getElementById('edit-location-id').value);
    const name = document.getElementById('edit-location-name').value;
    const address = document.getElementById('edit-location-address').value;
    const city = document.getElementById('edit-location-city').value;
    const state = document.getElementById('edit-location-state').value;
    const capacity = parseInt(document.getElementById('edit-location-capacity').value);
    const imageFile = document.getElementById('edit-location-image').files[0];
    
    const locationIndex = locations.findIndex(l => l.id === locationId);
    if (locationIndex !== -1) {
        locations[locationIndex].name = name;
        locations[locationIndex].address = `${address}, ${city}, ${state}`;
        locations[locationIndex].capacity = capacity;
        
        if (imageFile) {
            locations[locationIndex].image = URL.createObjectURL(imageFile);
        }
        
        loadLocations();
        closeModal('edit-location');
        alert('Location updated successfully!');
    }
}

function deleteLocation(locationId) {
    showConfirmation('Are you sure you want to delete this location?', () => {
        const locationIndex = locations.findIndex(l => l.id === locationId);
        if (locationIndex !== -1) {
            locations.splice(locationIndex, 1);
            loadLocations();
            alert('Location deleted successfully!');
        }
    });
}

function changePage(section, direction) {
    const totalItems = locations.length;
    const totalPages = Math.ceil(totalItems / itemsPerPage);
    
    currentPage[section] += direction;
    
    if (currentPage[section] < 1) {
        currentPage[section] = 1;
    } else if (currentPage[section] > totalPages) {
        currentPage[section] = totalPages;
    }
    
    loadLocations();
}


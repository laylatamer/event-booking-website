<?php
// locations.php
require_once __DIR__ . '/../../../config/db_connect.php';
require_once __DIR__ . '/../../../app/models/Venue.php';         

// Create Venue instance
$venue = new Venue($pdo);

// Get all venues for display
$stmt = $venue->readAll();
$venues = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!-- Locations Section -->
<div class="location-table-controls">
    <div class="controls-left">
        <div class="search-container">
            <input type="text" id="venue-search" placeholder="Search venues..." class="search-input">
            <i data-feather="search" class="search-icon"></i>
        </div>
        <select id="venue-status-filter" class="filter-select">
            <option value="">All Status</option>
            <option value="active">Active</option>
            <option value="inactive">Inactive</option>
            <option value="under_maintenance">Under Maintenance</option>
        </select>
    </div>
    <div class="controls-right">
        <button class="icon-btn" id="refresh-venues-btn">
            <i data-feather="refresh-cw"></i>
        </button>
    </div>
</div>

<div class="locations-grid" id="locations-grid">
    <?php if (empty($venues)): ?>
        <div class="location-empty-state">
            <i data-feather="map-pin"></i>
            <p>No venues found</p>
            <button class="primary-btn" id="add-first-venue-btn">Add Your First Venue</button>
        </div>
    <?php else: ?>
        <?php foreach ($venues as $venueItem): ?>
        <div class="location-card" data-id="<?php echo $venueItem['id']; ?>" 
             data-status="<?php echo $venueItem['status']; ?>">
            <div class="location-image">
                <?php if (!empty($venueItem['image_url'])): ?>
                    <?php 
                    $imageUrl = $venueItem['image_url'];
                    // If it's a local path, build a project-aware URL so files under
                    // public/uploads/... are reachable when the site is served from
                    // a subfolder (e.g. /event-booking-website/)
                    if ($imageUrl && !str_starts_with($imageUrl, 'http')) {
                        // Project public base - adjust if your site is served from a different subfolder
                        $projectBase = '/event-booking-website/public';
                        $imageUrl = $projectBase . '/' . ltrim($imageUrl, '/');
                    }
                    ?>
                    <img src="<?php echo htmlspecialchars($imageUrl); ?>" 
                         alt="<?php echo htmlspecialchars($venueItem['name']); ?>" 
                         class="venue-image">
                <?php else: ?>
                    <div class="venue-placeholder">
                        <i data-feather="map-pin"></i>
                    </div>
                <?php endif; ?>
                <span class="location-status-badge <?php echo $venueItem['status']; ?>">
                    <?php echo ucfirst(str_replace('_', ' ', $venueItem['status'])); ?>
                </span>
            </div>
            <div class="location-content">
                <h3 class="location-title"><?php echo htmlspecialchars($venueItem['name']); ?></h3>
                <p class="location-meta">
                    <i data-feather="map-pin" class="w-4 h-4"></i>
                    <?php echo htmlspecialchars($venueItem['city'] . ', ' . $venueItem['country']); ?>
                </p>
                <p class="location-address">
                    <i data-feather="home" class="w-4 h-4"></i>
                    <?php echo htmlspecialchars($venueItem['address']); ?>
                </p>
                <div class="location-info">
                    <div class="venue-stats">
                        <span class="venue-capacity-badge">
                            <i data-feather="users" class="w-3 h-3"></i>
                            <?php echo number_format($venueItem['capacity']); ?> capacity
                        </span>
                        <?php
                        // Get event count for this venue
                        $venueObj = new Venue($pdo);
                        $venueObj->id = $venueItem['id'];
                        $eventCount = $venueObj->getEventCount();
                        ?>
                        <span class="venue-event-badge">
                            <i data-feather="calendar" class="w-3 h-3"></i>
                            <?php echo $eventCount; ?> event<?php echo $eventCount != 1 ? 's' : ''; ?>
                        </span>
                    </div>
                    <div class="location-action-buttons">
                        <button class="location-action-btn edit-location" 
                                data-id="<?php echo $venueItem['id']; ?>" 
                                title="Edit Venue">
                            <i data-feather="edit-2"></i>
                        </button>
                        <button class="location-action-btn view-events" 
                                data-id="<?php echo $venueItem['id']; ?>" 
                                title="View Events">
                            <i data-feather="calendar"></i>
                        </button>
                        <button class="location-action-btn delete delete-location" 
                                data-id="<?php echo $venueItem['id']; ?>" 
                                data-name="<?php echo htmlspecialchars($venueItem['name']); ?>" 
                                title="Delete Venue">
                            <i data-feather="trash-2"></i>
                        </button>
                    </div>
                </div>
            </div>
        </div>
        <?php endforeach; ?>
    <?php endif; ?>
</div>

<div class="table-footer">
    <div class="table-info">
        Showing <span id="locations-start">1</span> to <span id="locations-end"><?php echo count($venues); ?></span> 
        of <span id="locations-total"><?php echo count($venues); ?></span> venues
    </div>
    <div class="pagination">
        <!-- Pagination will be handled by JavaScript -->
    </div>
</div>

<!-- Add/Edit Venue Modals - INLINE VERSION -->
<!-- Add Location Modal -->
<div id="add-location-modal" class="modal hidden">
    <div class="modal-content">
        <div class="modal-header">
            <h3>Add New Venue</h3>
            <button class="close-modal" data-modal="add-location">
                <i data-feather="x"></i>
            </button>
        </div>
        <form id="add-location-form" enctype="multipart/form-data">
            <div class="form-grid">
                <div class="form-group">
                    <label>Venue Name *</label>
                    <input type="text" id="location-name" name="name" required>
                </div>
                <div class="form-group">
                    <label>Address *</label>
                    <input type="text" id="location-address" name="address" required>
                </div>
                <div class="form-group two-columns">
                    <div>
                        <label>City *</label>
                        <input type="text" id="location-city" name="city" required>
                    </div>
                    <div>
                        <label>Country *</label>
                        <input type="text" id="location-country" name="country" value="Egypt" required>
                    </div>
                </div>
                <div class="form-group">
                    <label>Capacity *</label>
                    <input type="number" id="location-capacity" name="capacity" required min="1">
                </div>
                <div class="form-group">
                    <label>Seating Type *</label>
                    <select id="location-seating-type" name="seating_type" required>
                        <option value="">Select Seating Type</option>
                        <option value="stadium">Stadium View (Cat1, Cat2, Cat3)</option>
                        <option value="theatre">Theatre View (Gold, Premium, Regular)</option>
                        <option value="standing">Standing (Regular, Fanpit, Golden Circle)</option>
                    </select>
                    <small class="form-help">Choose the seating layout type for this venue</small>
                </div>
                <div class="form-group">
                    <label>Description</label>
                    <textarea id="location-description" name="description" rows="3" placeholder="Brief description of the venue..."></textarea>
                </div>
                <div class="form-group">
                    <label>Google Maps URL</label>
                    <input type="url" id="location-google-maps" name="google_maps_url" placeholder="https://maps.google.com/?q=...">
                </div>
                <div class="form-group">
                    <label>Venue Image</label>
                    <input type="file" id="location-image" name="image" accept="image/*">
                    <small class="form-help">Max size: 5MB. Allowed: JPG, PNG, GIF, WebP</small>
                </div>
                <div class="form-group">
                    <label>Status</label>
                    <select id="location-status" name="status">
                        <option value="active">Active</option>
                        <option value="inactive">Inactive</option>
                        <option value="under_maintenance">Under Maintenance</option>
                    </select>
                </div>
                <div class="form-group full-width">
                    <label>Facilities</label>
                    <div class="location-facilities-grid">
                        <?php 
                        $commonFacilities = [
                            'WiFi', 'Parking', 'AC', 'Heating', 'Restrooms', 
                            'Accessibility', 'Food & Drinks', 'Stage', 'Lighting',
                            'Sound System', 'Projector', 'Dressing Rooms', 'Security',
                            'First Aid', 'Coat Check', 'Wheelchair Access', 'Elevator'
                        ];
                        foreach ($commonFacilities as $facility): ?>
                        <label class="location-facility-checkbox">
                            <input type="checkbox" name="facilities[]" value="<?php echo htmlspecialchars($facility); ?>">
                            <span><?php echo htmlspecialchars($facility); ?></span>
                        </label>
                        <?php endforeach; ?>
                    </div>
                    <div class="location-custom-facility-input">
                        <input type="text" id="custom-facility" placeholder="Add custom facility">
                        <button type="button" id="add-custom-facility" class="secondary-btn">Add</button>
                    </div>
                </div>
            </div>
            <div class="modal-actions">
                <button type="button" class="secondary-btn" data-modal="add-location">Cancel</button>
                <button type="submit" class="primary-btn">Add Venue</button>
            </div>
        </form>
    </div>
</div>

<!-- Edit Location Modal -->
<div id="edit-location-modal" class="modal hidden">
    <div class="modal-content">
        <div class="modal-header">
            <h3>Edit Venue</h3>
            <button class="close-modal" data-modal="edit-location">
                <i data-feather="x"></i>
            </button>
        </div>
        <form id="edit-location-form" enctype="multipart/form-data">
            <input type="hidden" id="edit-location-id" name="id">
            <div class="form-grid">
                <div class="form-group">
                    <label>Venue Name *</label>
                    <input type="text" id="edit-location-name" name="name" required>
                </div>
                <div class="form-group">
                    <label>Address *</label>
                    <input type="text" id="edit-location-address" name="address" required>
                </div>
                <div class="form-group two-columns">
                    <div>
                        <label>City *</label>
                        <input type="text" id="edit-location-city" name="city" required>
                    </div>
                    <div>
                        <label>Country *</label>
                        <input type="text" id="edit-location-country" name="country" required>
                    </div>
                </div>
                <div class="form-group">
                    <label>Capacity *</label>
                    <input type="number" id="edit-location-capacity" name="capacity" required min="1">
                </div>
                <div class="form-group">
                    <label>Seating Type *</label>
                    <select id="edit-location-seating-type" name="seating_type" required>
                        <option value="">Select Seating Type</option>
                        <option value="stadium">Stadium View (Cat1, Cat2, Cat3)</option>
                        <option value="theatre">Theatre View (Gold, Premium, Regular)</option>
                        <option value="standing">Standing (Regular, Fanpit, Golden Circle)</option>
                    </select>
                    <small class="form-help">Choose the seating layout type for this venue</small>
                </div>
                <div class="form-group">
                    <label>Description</label>
                    <textarea id="edit-location-description" name="description" rows="3"></textarea>
                </div>
                <div class="form-group">
                    <label>Google Maps URL</label>
                    <input type="url" id="edit-location-google-maps" name="google_maps_url">
                </div>
                <div class="form-group">
                    <label>Venue Image</label>
                    <input type="file" id="edit-location-image" name="image" accept="image/*">
                    <small class="form-help">Max size: 5MB. Allowed: JPG, PNG, GIF, WebP</small>
                    <div id="current-image-preview" style="margin-top: 10px;"></div>
                </div>
                <div class="form-group">
                    <label>Status</label>
                    <select id="edit-location-status" name="status">
                        <option value="active">Active</option>
                        <option value="inactive">Inactive</option>
                        <option value="under_maintenance">Under Maintenance</option>
                    </select>
                </div>
                <div class="form-group full-width">
                    <label>Facilities</label>
                    <div class="location-facilities-grid" id="edit-facilities-grid">
                        <?php foreach ($commonFacilities as $facility): ?>
                        <label class="location-facility-checkbox">
                            <input type="checkbox" name="facilities[]" value="<?php echo htmlspecialchars($facility); ?>">
                            <span><?php echo htmlspecialchars($facility); ?></span>
                        </label>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>
            <div class="modal-actions">
                <button type="button" class="secondary-btn" data-modal="edit-location">Cancel</button>
                <button type="submit" class="primary-btn">Update Venue</button>
            </div>
        </form>
    </div>
</div>

<!-- Confirmation Modal (for delete operations) -->
<div id="confirmation-modal" class="modal hidden">
    <div class="modal-content small">
        <div class="modal-header">
            <h3>Confirm Action</h3>
            <button class="close-modal" data-modal="confirmation">
                <i data-feather="x"></i>
            </button>
        </div>
        <div class="confirmation-content">
            <p id="confirmation-message">Are you sure you want to perform this action?</p>
        </div>
        <div class="modal-actions">
            <button type="button" class="secondary-btn" data-modal="confirmation">Cancel</button>
            <button type="button" id="confirm-action-btn" class="danger-btn">Confirm</button>
        </div>
    </div>
</div>

<script>
// Initialize when DOM is loaded
document.addEventListener('DOMContentLoaded', function() {
    // Initialize Feather icons
    feather.replace();
    
    // Initialize venue event listeners
    initializeVenueEventListeners();
});

function initializeVenueEventListeners() {
    // Add location button
    const addLocationBtn = document.getElementById('add-location-btn');
    const addFirstVenueBtn = document.getElementById('add-first-venue-btn');
    
    if (addLocationBtn) {
        addLocationBtn.addEventListener('click', function() {
            openModal('add-location');
        });
    }
    
    if (addFirstVenueBtn) {
        addFirstVenueBtn.addEventListener('click', function() {
            openModal('add-location');
        });
    }
    
    // Close modal buttons
    const closeButtons = document.querySelectorAll('.close-modal');
    closeButtons.forEach(button => {
        button.addEventListener('click', function() {
            const modalId = this.getAttribute('data-modal');
            closeModal(modalId);
        });
    });
    
    // Cancel buttons
    const cancelButtons = document.querySelectorAll('[data-modal].secondary-btn');
    cancelButtons.forEach(button => {
        button.addEventListener('click', function() {
            const modalId = this.getAttribute('data-modal');
            closeModal(modalId);
        });
    });
    
    // Close modal when clicking outside
    document.querySelectorAll('.modal').forEach(modal => {
        modal.addEventListener('click', function(e) {
            if (e.target === this) {
                const modalId = this.id.replace('-modal', '');
                closeModal(modalId);
            }
        });
    });
    
    // Add location form
    const addLocationForm = document.getElementById('add-location-form');
    if (addLocationForm) {
        addLocationForm.addEventListener('submit', handleAddLocation);
    }
    
    // Edit location form
    const editLocationForm = document.getElementById('edit-location-form');
    if (editLocationForm) {
        editLocationForm.addEventListener('submit', handleEditLocation);
    }
    
    // Add custom facility
    const addCustomFacilityBtn = document.getElementById('add-custom-facility');
    if (addCustomFacilityBtn) {
        addCustomFacilityBtn.addEventListener('click', addCustomFacility);
    }
    
    // Edit venue buttons (delegated event for dynamic content)
    document.addEventListener('click', function(e) {
        if (e.target.closest('.edit-location')) {
            const button = e.target.closest('.edit-location');
            const venueId = button.getAttribute('data-id');
            editVenue(venueId);
        }
        
        if (e.target.closest('.delete-location')) {
            const button = e.target.closest('.delete-location');
            const venueId = button.getAttribute('data-id');
            const venueName = button.getAttribute('data-name');
            deleteVenue(venueId, venueName);
        }
        
        if (e.target.closest('.view-events')) {
            const button = e.target.closest('.view-events');
            const venueId = button.getAttribute('data-id');
            viewVenueEvents(venueId);
        }
    });
    
    // Search functionality
    const searchInput = document.getElementById('venue-search');
    if (searchInput) {
        searchInput.addEventListener('input', function() {
            const searchTerm = this.value.toLowerCase();
            filterVenues(searchTerm);
        });
    }
    
    // Status filter
    const statusFilter = document.getElementById('venue-status-filter');
    if (statusFilter) {
        statusFilter.addEventListener('change', function() {
            filterByStatus(this.value);
        });
    }
    
    // Refresh button
    const refreshBtn = document.getElementById('refresh-venues-btn');
    if (refreshBtn) {
        refreshBtn.addEventListener('click', function() {
            window.location.reload();
        });
    }
}

// Modal functions
function openModal(modalId) {
    const modal = document.getElementById(modalId + '-modal');
    if (modal) {
        modal.classList.remove('hidden');
        document.body.style.overflow = 'hidden';
    }
}

function closeModal(modalId) {
    const modal = document.getElementById(modalId + '-modal');
    if (modal) {
        modal.classList.add('hidden');
        document.body.style.overflow = 'auto';
        
        // Reset form if it's an add form
        if (modalId === 'add-location') {
            const form = document.getElementById('add-location-form');
            if (form) {
                form.reset();
            }
        }
    }
}

function showConfirmation(message, confirmCallback) {
    const confirmationMessage = document.getElementById('confirmation-message');
    if (confirmationMessage) {
        confirmationMessage.textContent = message;
        openModal('confirmation');
        
        // Set up confirm button
        const confirmBtn = document.getElementById('confirm-action-btn');
        if (confirmBtn) {
            confirmBtn.onclick = function() {
                confirmCallback();
                closeModal('confirmation');
            };
        }
    }
}

function filterVenues(searchTerm) {
    const cards = document.querySelectorAll('.location-card');
    let visibleCount = 0;
    
    cards.forEach(card => {
        const title = card.querySelector('.location-title').textContent.toLowerCase();
        const address = card.querySelector('.location-address').textContent.toLowerCase();
        const city = card.querySelector('.location-meta').textContent.toLowerCase();
        
        if (title.includes(searchTerm) || address.includes(searchTerm) || city.includes(searchTerm)) {
            card.style.display = 'block';
            visibleCount++;
        } else {
            card.style.display = 'none';
        }
    });
    
    updateVenueCount(visibleCount);
}

function filterByStatus(status) {
    const cards = document.querySelectorAll('.location-card');
    let visibleCount = 0;
    
    cards.forEach(card => {
        const cardStatus = card.getAttribute('data-status');
        
        if (!status || status === '' || cardStatus === status) {
            card.style.display = 'block';
            visibleCount++;
        } else {
            card.style.display = 'none';
        }
    });
    
    updateVenueCount(visibleCount);
}

function updateVenueCount(visibleCount) {
    const startElement = document.getElementById('locations-start');
    const endElement = document.getElementById('locations-end');
    
    if (startElement) startElement.textContent = visibleCount > 0 ? '1' : '0';
    if (endElement) endElement.textContent = visibleCount;
}

function addCustomFacility() {
    const input = document.getElementById('custom-facility');
    const facility = input.value.trim();
    
    if (facility) {
        // Add to add form
        const addGrid = document.querySelector('#add-location-form .location-facilities-grid');
        if (addGrid) {
            const newCheckbox = document.createElement('label');
            newCheckbox.className = 'location-facility-checkbox';
            newCheckbox.innerHTML = `
                <input type="checkbox" name="facilities[]" value="${facility}" checked>
                <span>${facility}</span>
            `;
            addGrid.appendChild(newCheckbox);
        }
        
        // Add to edit form
        const editGrid = document.querySelector('#edit-location-form .location-facilities-grid');
        if (editGrid) {
            const newCheckbox = document.createElement('label');
            newCheckbox.className = 'location-facility-checkbox';
            newCheckbox.innerHTML = `
                <input type="checkbox" name="facilities[]" value="${facility}">
                <span>${facility}</span>
            `;
            editGrid.appendChild(newCheckbox);
        }
        
        input.value = '';
    }
}

function editVenue(venueId) {
    fetch(`/api/venue.php?action=get&id=${venueId}`)
        .then(response => response.json())
        .then(data => {
            if (data.success && data.venue) {
                const venue = data.venue;
                
                // Populate edit form
                document.getElementById('edit-location-id').value = venue.id;
                document.getElementById('edit-location-name').value = venue.name;
                document.getElementById('edit-location-address').value = venue.address;
                document.getElementById('edit-location-city').value = venue.city;
                document.getElementById('edit-location-country').value = venue.country;
                document.getElementById('edit-location-capacity').value = venue.capacity;
                document.getElementById('edit-location-seating-type').value = venue.seating_type || '';
                document.getElementById('edit-location-description').value = venue.description || '';
                document.getElementById('edit-location-google-maps').value = venue.google_maps_url || '';
                document.getElementById('edit-location-status').value = venue.status;
                
                // Show current image if exists
                const previewDiv = document.getElementById('current-image-preview');
                if (previewDiv) {
                    if (venue.image_url) {
                        let imageUrl = venue.image_url;
                        // If it's a local path, prepend slash
                        if (imageUrl && !imageUrl.startsWith('http')) {
                            imageUrl = '/' + imageUrl;
                        }
                        previewDiv.innerHTML = `
                            <p>Current Image:</p>
                            <img src="${imageUrl}" alt="${venue.name}" 
                                 style="max-width: 200px; max-height: 150px; margin-top: 5px; border-radius: 4px;">
                        `;
                    } else {
                        previewDiv.innerHTML = '';
                    }
                }
                
                // Populate facilities checkboxes
                const facilities = venue.facilities || [];
                document.querySelectorAll('#edit-location-form input[name="facilities[]"]').forEach(cb => {
                    cb.checked = facilities.includes(cb.value);
                });
                
                openModal('edit-location');
                feather.replace();
            } else {
                alert('Error loading venue data: ' + (data.message || 'Unknown error'));
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('Error loading venue data');
        });
}

function deleteVenue(venueId, venueName) {
    showConfirmation(`Are you sure you want to delete "${venueName}"? This action cannot be undone.`, () => {
        const formData = new FormData();
        formData.append('action', 'delete');
        formData.append('id', venueId);
        
        fetch('../../../public/api/venue.php', {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                alert(data.message);
                window.location.reload();
            } else {
                alert('Error: ' + data.message);
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('An error occurred. Please try again.');
        });
    });
}

function viewVenueEvents(venueId) {
    // For now, just show a message
    alert('Event viewing functionality will be implemented soon!');
}

function handleAddLocation(e) {
    e.preventDefault();
    
    const form = e.target;
    const formData = new FormData(form);
    formData.append('action', 'add');
    
    // Show loading
    const submitBtn = e.target.querySelector('button[type="submit"]');
    const originalText = submitBtn.innerHTML;
    submitBtn.disabled = true;
    submitBtn.innerHTML = '<i data-feather="loader" class="animate-spin"></i> Adding...';
    feather.replace();
    
    fetch('../../../public/api/venue.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            alert(data.message);
            closeModal('add-location');
            form.reset();
            window.location.reload();
        } else {
            alert('Error: ' + data.message);
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('An error occurred. Please try again.');
    })
    .finally(() => {
        submitBtn.disabled = false;
        submitBtn.innerHTML = originalText;
        feather.replace();
    });
}

function handleEditLocation(e) {
    e.preventDefault();
    
    const form = e.target;
    const formData = new FormData(form);
    formData.append('action', 'edit');
    
    // Show loading
    const submitBtn = e.target.querySelector('button[type="submit"]');
    const originalText = submitBtn.innerHTML;
    submitBtn.disabled = true;
    submitBtn.innerHTML = '<i data-feather="loader" class="animate-spin"></i> Updating...';
    feather.replace();
    
    fetch('/api/venue.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            alert(data.message);
            closeModal('edit-location');
            window.location.reload();
        } else {
            alert('Error: ' + data.message);
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('An error occurred. Please try again.');
    })
    .finally(() => {
        submitBtn.disabled = false;
        submitBtn.innerHTML = originalText;
        feather.replace();
    });
}

// Make these functions globally available
window.editVenue = editVenue;
window.deleteVenue = deleteVenue;
window.viewVenueEvents = viewVenueEvents;
window.addCustomFacility = addCustomFacility;
window.openModal = openModal;
window.closeModal = closeModal;
window.showConfirmation = showConfirmation;
</script>

// events.js - Admin Panel Events Management JavaScript (Updated with admin-specific IDs)
document.addEventListener('DOMContentLoaded', function() {
    // Initialize Feather icons
    if (typeof feather !== 'undefined') {
        feather.replace();
    }
    
    // Initialize event listeners
    initializeEventListeners();
    
    // Load subcategories when main category changes (admin-specific IDs)
    const mainCategorySelect = document.getElementById('admin-event-main-category');
    const editMainCategorySelect = document.getElementById('admin-edit-event-main-category');
    
    if (mainCategorySelect) {
        mainCategorySelect.addEventListener('change', function() {
            loadSubcategories(this.value, 'admin-event-subcategory');
        });
    }
    
    if (editMainCategorySelect) {
        editMainCategorySelect.addEventListener('change', function() {
            loadSubcategories(this.value, 'admin-edit-event-subcategory');
        });
    }
    
    // Set current datetime as default for new events (admin-specific IDs)
    const eventDateInput = document.getElementById('admin-event-date');
    if (eventDateInput) {
        const now = new Date();
        const localDateTime = now.toISOString().slice(0, 16);
        eventDateInput.value = localDateTime;
    }
    
    // Set min date for end date (admin-specific IDs)
    const startDateInput = document.getElementById('admin-event-date');
    const endDateInput = document.getElementById('admin-event-end-date');
    const editStartDateInput = document.getElementById('admin-edit-event-date');
    const editEndDateInput = document.getElementById('admin-edit-event-end-date');
    
    if (startDateInput && endDateInput) {
        startDateInput.addEventListener('change', function() {
            endDateInput.min = this.value;
        });
    }
    
    if (editStartDateInput && editEndDateInput) {
        editStartDateInput.addEventListener('change', function() {
            editEndDateInput.min = this.value;
        });
    }
});

function initializeEventListeners() {
    // Add event button (these IDs stay the same - they're not in forms)
    const addEventBtn = document.getElementById('add-event-btn');
    const addFirstEventBtn = document.getElementById('add-first-event-btn');
    
    if (addEventBtn) {
        addEventBtn.addEventListener('click', function() {
            openModal('add-event');
        });
    }
    
    if (addFirstEventBtn) {
        addFirstEventBtn.addEventListener('click', function() {
            openModal('add-event');
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
    
    // Add event form
    const addEventForm = document.getElementById('add-event-form');
    if (addEventForm) {
        addEventForm.addEventListener('submit', handleAddEvent);
    }
    
    // Edit event form
    const editEventForm = document.getElementById('edit-event-form');
    if (editEventForm) {
        editEventForm.addEventListener('submit', handleEditEvent);
    }
    
    // Edit event buttons (delegated event for dynamic content)
    document.addEventListener('click', function(e) {
        if (e.target.closest('.edit-event')) {
            const button = e.target.closest('.edit-event');
            const eventId = button.getAttribute('data-id');
            editEvent(eventId);
        }
        
        if (e.target.closest('.delete-event')) {
            const button = e.target.closest('.delete-event');
            const eventId = button.getAttribute('data-id');
            const eventName = button.getAttribute('data-name');
            deleteEvent(eventId, eventName);
        }
        
        if (e.target.closest('.view-event')) {
            const button = e.target.closest('.view-event');
            const eventId = button.getAttribute('data-id');
            viewEvent(eventId);
        }
    });
    
    // Search functionality
    const searchInput = document.getElementById('event-search');
    if (searchInput) {
        searchInput.addEventListener('input', function() {
            const searchTerm = this.value.toLowerCase();
            filterEvents(searchTerm);
        });
    }
    
    // Category filter
    const categoryFilter = document.getElementById('event-category-filter');
    if (categoryFilter) {
        categoryFilter.addEventListener('change', function() {
            filterByCategory(this.value);
        });
    }
    
    // Status filter
    const statusFilter = document.getElementById('event-status-filter');
    if (statusFilter) {
        statusFilter.addEventListener('change', function() {
            filterByStatus(this.value);
        });
    }
    
    // Refresh button
    const refreshBtn = document.getElementById('refresh-events-btn');
    if (refreshBtn) {
        refreshBtn.addEventListener('click', function() {
            window.location.reload();
        });
    }
}

function loadSubcategories(mainCategoryId, targetSelectId) {
    const subcategorySelect = document.getElementById(targetSelectId);
    
    if (!mainCategoryId) {
        if (subcategorySelect) {
            subcategorySelect.innerHTML = '<option value="">Select Subcategory</option>';
            subcategorySelect.disabled = true;
        }
        return;
    }
    
    // Fetch subcategories via AJAX
    fetch(`../../../public/api/admin_api.php?action=getSubcategories&main_category_id=${mainCategoryId}`)
        .then(response => {
            if (!response.ok) {
                throw new Error('Network response was not ok');
            }
            return response.json();
        })
        .then(data => {
            if (data.success && subcategorySelect) {
                let options = '<option value="">Select Subcategory</option>';
                data.subcategories.forEach(subcategory => {
                    options += `<option value="${subcategory.id}">${subcategory.name}</option>`;
                });
                subcategorySelect.innerHTML = options;
                subcategorySelect.disabled = false;
            } else {
                if (subcategorySelect) {
                    subcategorySelect.innerHTML = '<option value="">No subcategories found</option>';
                }
            }
        })
        .catch(error => {
            console.error('Error loading subcategories:', error);
            if (subcategorySelect) {
                subcategorySelect.innerHTML = '<option value="">Error loading subcategories</option>';
            }
        });
}

function filterEvents(searchTerm) {
    const rows = document.querySelectorAll('#events-table-body tr:not(.empty-state)');
    let visibleCount = 0;
    
    rows.forEach(row => {
        const titleCell = row.querySelector('td:nth-child(2)');
        const title = titleCell.querySelector('strong').textContent.toLowerCase();
        const description = titleCell.querySelector('small') ? titleCell.querySelector('small').textContent.toLowerCase() : '';
        
        if (title.includes(searchTerm) || description.includes(searchTerm)) {
            row.style.display = '';
            visibleCount++;
        } else {
            row.style.display = 'none';
        }
    });
    
    updateEventCount(visibleCount);
}

function filterByCategory(category) {
    const rows = document.querySelectorAll('#events-table-body tr:not(.empty-state)');
    let visibleCount = 0;
    
    rows.forEach(row => {
        const categoryCell = row.querySelector('td:nth-child(3) .category-badge');
        const categoryText = categoryCell ? categoryCell.textContent : '';
        
        if (!category || category === '' || categoryText === category) {
            row.style.display = '';
            visibleCount++;
        } else {
            row.style.display = 'none';
        }
    });
    
    updateEventCount(visibleCount);
}

function filterByStatus(status) {
    const rows = document.querySelectorAll('#events-table-body tr:not(.empty-state)');
    let visibleCount = 0;
    
    rows.forEach(row => {
        const rowStatus = row.getAttribute('data-status');
        
        if (!status || status === '' || rowStatus === status) {
            row.style.display = '';
            visibleCount++;
        } else {
            row.style.display = 'none';
        }
    });
    
    updateEventCount(visibleCount);
}

function updateEventCount(visibleCount) {
    const startElement = document.getElementById('events-start');
    const endElement = document.getElementById('events-end');
    
    if (startElement) startElement.textContent = visibleCount > 0 ? '1' : '0';
    if (endElement) endElement.textContent = visibleCount;
}

function openModal(modalId) {
    const modal = document.getElementById(modalId + '-modal');
    if (modal) {
        modal.classList.remove('hidden');
        document.body.style.overflow = 'hidden';
        
        // Reinitialize feather icons in modal
        if (typeof feather !== 'undefined') {
            setTimeout(() => feather.replace(), 100);
        }
    }
}

function closeModal(modalId) {
    const modal = document.getElementById(modalId + '-modal');
    if (modal) {
        modal.classList.add('hidden');
        document.body.style.overflow = 'auto';
        
        // Reset form if it's an add form
        if (modalId === 'add-event') {
            const form = document.getElementById('add-event-form');
            if (form) {
                form.reset();
                // Reset subcategory select (admin-specific ID)
                const subcategorySelect = document.getElementById('admin-event-subcategory');
                if (subcategorySelect) {
                    subcategorySelect.innerHTML = '<option value="">Select Subcategory</option>';
                    subcategorySelect.disabled = true;
                }
                // Reset date to current
                const dateInput = document.getElementById('admin-event-date');
                if (dateInput) {
                    const now = new Date();
                    dateInput.value = now.toISOString().slice(0, 16);
                }
            }
        }
    }
}

function showConfirmation(message, confirmCallback) {
    const confirmationMessage = document.getElementById('confirmation-message');
    if (confirmationMessage) {
        confirmationMessage.textContent = message;
        openModal('confirmation');
        
        const confirmBtn = document.getElementById('confirm-action-btn');
        if (confirmBtn) {
            // Remove previous event listeners
            const newConfirmBtn = confirmBtn.cloneNode(true);
            confirmBtn.parentNode.replaceChild(newConfirmBtn, confirmBtn);
            
            // Add new event listener
            newConfirmBtn.addEventListener('click', function() {
                if (confirmCallback && typeof confirmCallback === 'function') {
                    confirmCallback();
                }
                closeModal('confirmation');
            });
            
            // Update reference
            document.getElementById('confirm-action-btn').onclick = function() {
                if (confirmCallback && typeof confirmCallback === 'function') {
                    confirmCallback();
                }
                closeModal('confirmation');
            };
        }
    }
}

function editEvent(eventId) {
    if (!eventId) {
        console.error('No event ID provided');
        return;
    }
    
    fetch(`../../../public/api/admin_api.php?action=getEvent&id=${eventId}`)
        .then(response => {
            if (!response.ok) {
                throw new Error('Network response was not ok');
            }
            return response.json();
        })
        .then(data => {
            if (data.success && data.event) {
                const event = data.event;
                
                // Populate edit form with admin-specific IDs
                document.getElementById('admin-edit-event-id').value = event.id;
                document.getElementById('admin-edit-event-title').value = event.title;
                document.getElementById('admin-edit-event-description').value = event.description;
                document.getElementById('admin-edit-event-main-category').value = event.main_category_id;
                
                // Load subcategories for this main category
                loadSubcategories(event.main_category_id, 'admin-edit-event-subcategory');
                
                // Set subcategory value after a short delay
                setTimeout(() => {
                    const subcategorySelect = document.getElementById('admin-edit-event-subcategory');
                    if (subcategorySelect) {
                        subcategorySelect.value = event.subcategory_id;
                    }
                }, 300);
                
                document.getElementById('admin-edit-event-venue').value = event.venue_id;
                
                // Format dates for datetime-local input
                if (event.date) {
                    const startDate = new Date(event.date);
                    const startDateInput = document.getElementById('admin-edit-event-date');
                    if (startDateInput) {
                        // Adjust for timezone offset
                        const timezoneOffset = startDate.getTimezoneOffset() * 60000;
                        const localStartDate = new Date(startDate.getTime() - timezoneOffset);
                        startDateInput.value = localStartDate.toISOString().slice(0, 16);
                    }
                }
                
                if (event.end_date) {
                    const endDate = new Date(event.end_date);
                    const endDateInput = document.getElementById('admin-edit-event-end-date');
                    if (endDateInput) {
                        // Adjust for timezone offset
                        const timezoneOffset = endDate.getTimezoneOffset() * 60000;
                        const localEndDate = new Date(endDate.getTime() - timezoneOffset);
                        endDateInput.value = localEndDate.toISOString().slice(0, 16);
                    }
                }
                
                document.getElementById('admin-edit-event-price').value = event.price;
                document.getElementById('admin-edit-event-discounted-price').value = event.discounted_price || '';
                document.getElementById('admin-edit-event-total-tickets').value = event.total_tickets;
                document.getElementById('admin-edit-event-available-tickets').value = event.available_tickets;
                document.getElementById('admin-edit-event-min-tickets').value = event.min_tickets_per_booking || 1;
                document.getElementById('admin-edit-event-max-tickets').value = event.max_tickets_per_booking || 10;
                document.getElementById('admin-edit-event-image-url').value = event.image_url || '';
                
                // Handle gallery images
                const galleryImagesTextarea = document.getElementById('admin-edit-event-gallery-images');
                if (galleryImagesTextarea) {
                    if (event.gallery_images && Array.isArray(event.gallery_images)) {
                        galleryImagesTextarea.value = JSON.stringify(event.gallery_images, null, 2);
                    } else {
                        galleryImagesTextarea.value = '';
                    }
                }
                
                document.getElementById('admin-edit-event-terms').value = event.terms_conditions || '';
                
                // Handle additional info
                const additionalInfoTextarea = document.getElementById('admin-edit-event-additional-info');
                if (additionalInfoTextarea) {
                    if (event.additional_info) {
                        try {
                            const additionalInfo = typeof event.additional_info === 'string' 
                                ? JSON.parse(event.additional_info) 
                                : event.additional_info;
                            additionalInfoTextarea.value = JSON.stringify(additionalInfo, null, 2);
                        } catch (e) {
                            additionalInfoTextarea.value = event.additional_info;
                        }
                    } else {
                        additionalInfoTextarea.value = '';
                    }
                }
                
                document.getElementById('admin-edit-event-status').value = event.status || 'draft';
                
                openModal('edit-event');
                
                // Reinitialize feather icons
                if (typeof feather !== 'undefined') {
                    setTimeout(() => feather.replace(), 100);
                }
            } else {
                alert('Error loading event data: ' + (data.message || 'Unknown error'));
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('Error loading event data. Please check console for details.');
        });
}

function deleteEvent(eventId, eventName) {
    showConfirmation(`Are you sure you want to delete "${eventName}"? This action cannot be undone.`, () => {
        const formData = new FormData();
        formData.append('action', 'deleteEvent');
        formData.append('id', eventId);
        
        fetch('../../../public/api/admin_api.php', {
            method: 'POST',
            body: formData
        })
        .then(response => {
            if (!response.ok) {
                throw new Error('Network response was not ok');
            }
            return response.json();
        })
        .then(data => {
            if (data.success) {
                alert(data.message || 'Event deleted successfully');
                window.location.reload();
            } else {
                alert('Error: ' + (data.message || 'Failed to delete event'));
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('An error occurred. Please try again.');
        });
    });
}

function viewEvent(eventId) {
    fetch(`../../../public/api/events_API.php?action=getOne&id=${eventId}`)
        .then(response => {
            if (!response.ok) {
                throw new Error('Network response was not ok');
            }
            return response.json();
        })
        .then(data => {
            if (data.success && data.event) {
                const event = data.event;
                
                let content = `
                    <div class="event-details">
                        <div class="event-header">
                            ${event.image ? `<img src="${event.image}" alt="${event.title}" class="event-detail-image">` : ''}
                            <h3>${event.title}</h3>
                            <div class="event-meta">
                                <span class="category-badge">${event.main_category || 'Uncategorized'}</span>
                                <span class="status-badge ${event.status}">${event.status}</span>
                            </div>
                        </div>
                        
                        <div class="event-info-grid">
                            <div class="info-section">
                                <h4><i data-feather="calendar"></i> Date & Time</h4>
                                <p>${event.formattedDateTime || event.date}</p>
                                ${event.formattedEndDate ? `<p>to ${event.formattedEndDate} at ${event.formattedEndTime}</p>` : ''}
                            </div>
                            
                            <div class="info-section">
                                <h4><i data-feather="map-pin"></i> Venue</h4>
                                <p><strong>${event.venue?.name || 'Unknown Venue'}</strong></p>
                                <p>${event.venue?.address || ''}, ${event.venue?.city || ''}, ${event.venue?.country || ''}</p>
                                ${event.venue?.capacity ? `<p>Capacity: ${event.venue.capacity.toLocaleString()}</p>` : ''}
                            </div>
                            
                            <div class="info-section">
                                <h4><i data-feather="tag"></i> Pricing</h4>
                                <p>Regular: <strong>${event.formattedPrice || '$' + event.price}</strong></p>
                                ${event.formattedDiscountedPrice ? `<p>Discounted: <strong>${event.formattedDiscountedPrice}</strong></p>` : ''}
                            </div>
                            
                            <div class="info-section">
                                <h4><i data-feather="ticket"></i> Tickets</h4>
                                <p>Available: <strong>${event.available_tickets} / ${event.total_tickets}</strong></p>
                                <p>Booking limits: ${event.min_tickets_per_booking || 1} - ${event.max_tickets_per_booking || 10} tickets</p>
                            </div>
                        </div>
                        
                        <div class="event-description">
                            <h4>Description</h4>
                            <p>${event.description}</p>
                        </div>
                `;
                
                // Add facilities if available
                if (event.venue?.facilities && event.venue.facilities.length > 0) {
                    content += `
                        <div class="event-facilities">
                            <h4><i data-feather="check-circle"></i> Facilities</h4>
                            <div class="facilities-list">
                    `;
                    
                    event.venue.facilities.forEach(facility => {
                        content += `<span class="facility-badge">${facility}</span>`;
                    });
                    
                    content += `
                            </div>
                        </div>
                    `;
                }
                
                // Add terms & conditions if available
                if (event.terms_conditions) {
                    content += `
                        <div class="event-terms">
                            <h4><i data-feather="file-text"></i> Terms & Conditions</h4>
                            <p>${event.terms_conditions}</p>
                        </div>
                    `;
                }
                
                // Add gallery images if available
                if (event.gallery_images && event.gallery_images.length > 0) {
                    content += `
                        <div class="event-gallery">
                            <h4><i data-feather="image"></i> Gallery</h4>
                            <div class="gallery-grid">
                    `;
                    
                    event.gallery_images.forEach((img, index) => {
                        if (index < 4) { // Show only first 4 images
                            content += `<img src="${img}" alt="Gallery image ${index + 1}" class="gallery-thumb">`;
                        }
                    });
                    
                    if (event.gallery_images.length > 4) {
                        content += `<div class="gallery-more">+${event.gallery_images.length - 4} more</div>`;
                    }
                    
                    content += `
                            </div>
                        </div>
                    `;
                }
                
                content += `</div>`;
                
                const contentContainer = document.getElementById('event-details-content');
                if (contentContainer) {
                    contentContainer.innerHTML = content;
                    openModal('view-event');
                    
                    // Reinitialize feather icons
                    if (typeof feather !== 'undefined') {
                        setTimeout(() => feather.replace(), 100);
                    }
                }
            } else {
                alert('Error loading event: ' + (data.message || 'Event not found'));
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('Error loading event details. Please try again.');
        });
}

function handleAddEvent(e) {
    e.preventDefault();
    
    const formData = new FormData(e.target);
    
    // Validate required fields
    const requiredFields = ['title', 'description', 'subcategory_id', 'venue_id', 'date', 'price', 'total_tickets', 'available_tickets'];
    let isValid = true;
    let missingField = '';
    
    requiredFields.forEach(field => {
        const value = formData.get(field);
        if (!value || value.toString().trim() === '') {
            isValid = false;
            missingField = field.replace('_', ' ');
            return;
        }
    });
    
    if (!isValid) {
        alert(`Please fill in the ${missingField} field`);
        return;
    }
    
    // Convert JSON fields
    try {
        const galleryImages = formData.get('gallery_images');
        if (galleryImages && galleryImages.toString().trim() !== '') {
            formData.set('gallery_images', JSON.parse(galleryImages));
        } else {
            formData.set('gallery_images', '[]');
        }
        
        const additionalInfo = formData.get('additional_info');
        if (additionalInfo && additionalInfo.toString().trim() !== '') {
            formData.set('additional_info', JSON.parse(additionalInfo));
        } else {
            formData.set('additional_info', '{}');
        }
    } catch (error) {
        alert('Invalid JSON format in gallery images or additional info: ' + error.message);
        return;
    }
    
    // Add action
    formData.append('action', 'addEvent');
    
    // Show loading
    const submitBtn = e.target.querySelector('button[type="submit"]');
    const originalText = submitBtn.innerHTML;
    submitBtn.disabled = true;
    submitBtn.innerHTML = '<i data-feather="loader" class="animate-spin"></i> Creating...';
    
    if (typeof feather !== 'undefined') {
        feather.replace();
    }
    
    fetch('../../../public/api/admin_api.php', {
        method: 'POST',
        body: formData
    })
    .then(response => {
        if (!response.ok) {
            throw new Error('Network response was not ok');
        }
        return response.json();
    })
    .then(data => {
        if (data.success) {
            alert(data.message || 'Event created successfully');
            closeModal('add-event');
            setTimeout(() => {
                window.location.reload();
            }, 500);
        } else {
            alert('Error: ' + (data.message || 'Failed to create event'));
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('An error occurred. Please try again.');
    })
    .finally(() => {
        submitBtn.disabled = false;
        submitBtn.innerHTML = originalText;
        if (typeof feather !== 'undefined') {
            feather.replace();
        }
    });
}

function handleEditEvent(e) {
    e.preventDefault();
    
    const formData = new FormData(e.target);
    const eventId = formData.get('id');
    
    if (!eventId) {
        alert('Event ID is required');
        return;
    }
    
    // Validate required fields
    const requiredFields = ['title', 'description', 'subcategory_id', 'venue_id', 'date', 'price', 'total_tickets', 'available_tickets'];
    let isValid = true;
    let missingField = '';
    
    requiredFields.forEach(field => {
        const value = formData.get(field);
        if (!value || value.toString().trim() === '') {
            isValid = false;
            missingField = field.replace('_', ' ');
            return;
        }
    });
    
    if (!isValid) {
        alert(`Please fill in the ${missingField} field`);
        return;
    }
    
    // Convert JSON fields
    try {
        const galleryImages = formData.get('gallery_images');
        if (galleryImages && galleryImages.toString().trim() !== '') {
            formData.set('gallery_images', JSON.parse(galleryImages));
        } else {
            formData.set('gallery_images', '[]');
        }
        
        const additionalInfo = formData.get('additional_info');
        if (additionalInfo && additionalInfo.toString().trim() !== '') {
            formData.set('additional_info', JSON.parse(additionalInfo));
        } else {
            formData.set('additional_info', '{}');
        }
    } catch (error) {
        alert('Invalid JSON format in gallery images or additional info: ' + error.message);
        return;
    }
    
    // Add action
    formData.append('action', 'updateEvent');
    
    // Show loading
    const submitBtn = e.target.querySelector('button[type="submit"]');
    const originalText = submitBtn.innerHTML;
    submitBtn.disabled = true;
    submitBtn.innerHTML = '<i data-feather="loader" class="animate-spin"></i> Updating...';
    
    if (typeof feather !== 'undefined') {
        feather.replace();
    }
    
    fetch('../../../public/api/admin_api.php', {
        method: 'POST',
        body: formData
    })
    .then(response => {
        if (!response.ok) {
            throw new Error('Network response was not ok');
        }
        return response.json();
    })
    .then(data => {
        if (data.success) {
            alert(data.message || 'Event updated successfully');
            closeModal('edit-event');
            setTimeout(() => {
                window.location.reload();
            }, 500);
        } else {
            alert('Error: ' + (data.message || 'Failed to update event'));
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('An error occurred. Please try again.');
    })
    .finally(() => {
        submitBtn.disabled = false;
        submitBtn.innerHTML = originalText;
        if (typeof feather !== 'undefined') {
            feather.replace();
        }
    });
}

// Make functions available globally if needed
window.editEvent = editEvent;
window.deleteEvent = deleteEvent;
window.viewEvent = viewEvent;
window.openModal = openModal;
window.closeModal = closeModal;
window.showConfirmation = showConfirmation;

// Debug function to check if elements exist
window.checkElements = function() {
    console.log('Checking admin elements...');
    const elements = [
        'admin-event-main-category',
        'admin-event-subcategory',
        'admin-event-date',
        'admin-event-end-date',
        'admin-edit-event-main-category',
        'admin-edit-event-subcategory',
        'admin-edit-event-date',
        'admin-edit-event-end-date'
    ];
    
    elements.forEach(id => {
        const el = document.getElementById(id);
        console.log(`${id}:`, el ? 'Found' : 'NOT FOUND');
    });
};

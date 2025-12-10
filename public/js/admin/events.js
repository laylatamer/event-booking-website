// events.js - Clean Admin Events Management
const API_BASE = '../../../public/api/events_API.php';

document.addEventListener('DOMContentLoaded', function() {
    // Initialize Feather icons
    if (typeof feather !== 'undefined') {
        feather.replace();
    }
    
    // Initialize event listeners
    initializeEventListeners();
    
    // Setup form dependencies
    setupFormDependencies();
});

function initializeEventListeners() {
    // Add event buttons
    document.addEventListener('click', function(e) {
        if (e.target.closest('#add-event-btn') || e.target.closest('#add-first-event-btn')) {
            e.preventDefault();
            openAddEventModal();
        }
        
        if (e.target.closest('.edit-event')) {
            e.preventDefault();
            const eventId = e.target.closest('.edit-event').getAttribute('data-id');
            editEvent(eventId);
        }
        
        if (e.target.closest('.delete-event')) {
            e.preventDefault();
            const button = e.target.closest('.delete-event');
            const eventId = button.getAttribute('data-id');
            const eventName = button.getAttribute('data-name');
            confirmDeleteEvent(eventId, eventName);
        }
        
        if (e.target.closest('.view-event')) {
            e.preventDefault();
            const eventId = e.target.closest('.view-event').getAttribute('data-id');
            viewEventDetails(eventId);
        }
        
        // Close modals
        if (e.target.closest('.close-modal') || e.target.closest('.secondary-btn[data-modal]')) {
            const button = e.target.closest('.close-modal') || e.target.closest('.secondary-btn[data-modal]');
            const modalId = button.getAttribute('data-modal');
            closeModal(modalId + '-modal');
        }
    });
    
    // Form submissions
    const addEventForm = document.getElementById('add-event-form');
    if (addEventForm) {
        addEventForm.addEventListener('submit', handleAddEvent);
    }
    
    const editEventForm = document.getElementById('edit-event-form');
    if (editEventForm) {
        editEventForm.addEventListener('submit', handleEditEvent);
    }
    
    // Search and filter
    const searchInput = document.getElementById('event-search');
    if (searchInput) {
        searchInput.addEventListener('input', filterEvents);
    }
    
    const categoryFilter = document.getElementById('event-category-filter');
    if (categoryFilter) {
        categoryFilter.addEventListener('change', filterEvents);
    }
    
    const statusFilter = document.getElementById('event-status-filter');
    if (statusFilter) {
        statusFilter.addEventListener('change', filterEvents);
    }
    
    // Refresh button
    const refreshBtn = document.getElementById('refresh-events-btn');
    if (refreshBtn) {
        refreshBtn.addEventListener('click', function() {
            window.location.reload();
        });
    }
}

function setupFormDependencies() {
    // Main category change - load subcategories
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
    
    // Set current datetime for new events
    const eventDateInput = document.getElementById('admin-event-date');
    if (eventDateInput) {
        const now = new Date();
        const localDateTime = now.toISOString().slice(0, 16);
        eventDateInput.value = localDateTime;
    }
    
    // End date min date validation
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
}

async function loadSubcategories(mainCategoryId, targetSelectId) {
    const subcategorySelect = document.getElementById(targetSelectId);
    
    if (!mainCategoryId) {
        if (subcategorySelect) {
            subcategorySelect.innerHTML = '<option value="">Select Subcategory</option>';
            subcategorySelect.disabled = true;
        }
        return;
    }
    
    try {
        const response = await fetch(`${API_BASE}?action=getSubcategories&main_category_id=${mainCategoryId}`);
        
        if (!response.ok) {
            throw new Error('Failed to load subcategories');
        }
        
        const data = await response.json();
        
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
    } catch (error) {
        console.error('Error loading subcategories:', error);
        if (subcategorySelect) {
            subcategorySelect.innerHTML = '<option value="">Error loading subcategories</option>';
        }
    }
}

function openAddEventModal() {
    // Reset form
    const form = document.getElementById('add-event-form');
    if (form) {
        form.reset();
        
        // Reset subcategory
        const subcategorySelect = document.getElementById('admin-event-subcategory');
        if (subcategorySelect) {
            subcategorySelect.innerHTML = '<option value="">Select Subcategory</option>';
            subcategorySelect.disabled = true;
        }
        
        // Set current date
        const dateInput = document.getElementById('admin-event-date');
        if (dateInput) {
            const now = new Date();
            dateInput.value = now.toISOString().slice(0, 16);
        }
        
        // Set default values
        document.getElementById('admin-event-min-tickets').value = 1;
        document.getElementById('admin-event-max-tickets').value = 10;
        document.getElementById('admin-event-status').value = 'draft';
    }
    
    openModal('add-event-modal');
}

async function editEvent(eventId) {
    if (!eventId) {
        alert('Event ID is required');
        return;
    }
    
    try {
        const response = await fetch(`${API_BASE}?action=getEvent&id=${eventId}`);
        
        if (!response.ok) {
            throw new Error('Failed to load event data');
        }
        
        const data = await response.json();
        
        if (data.success && data.event) {
            const event = data.event;
            
            // Populate edit form
            document.getElementById('admin-edit-event-id').value = event.id;
            document.getElementById('admin-edit-event-title').value = event.title;
            document.getElementById('admin-edit-event-description').value = event.description;
            
            // Set main category and load subcategories
            document.getElementById('admin-edit-event-main-category').value = event.main_category_id;
            
            // Load subcategories for this main category
            await loadSubcategories(event.main_category_id, 'admin-edit-event-subcategory');
            
            // Set subcategory after a delay to ensure it's loaded
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
                    startDateInput.value = startDate.toISOString().slice(0, 16);
                }
            }
            
            if (event.end_date) {
                const endDate = new Date(event.end_date);
                const endDateInput = document.getElementById('admin-edit-event-end-date');
                if (endDateInput) {
                    endDateInput.value = endDate.toISOString().slice(0, 16);
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
            
            openModal('edit-event-modal');
            
        } else {
            alert('Error loading event: ' + (data.message || 'Event not found'));
        }
    } catch (error) {
        console.error('Error loading event:', error);
        alert('Error loading event data. Please try again.');
    }
}

async function handleAddEvent(e) {
    e.preventDefault();
    
    const formData = new FormData(e.target);
    
    // Validate required fields
    const requiredFields = ['title', 'description', 'subcategory_id', 'venue_id', 'date', 'price', 'total_tickets'];
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
            formData.set('gallery_images', JSON.stringify(JSON.parse(galleryImages)));
        } else {
            formData.set('gallery_images', '[]');
        }
        
        const additionalInfo = formData.get('additional_info');
        if (additionalInfo && additionalInfo.toString().trim() !== '') {
            formData.set('additional_info', JSON.stringify(JSON.parse(additionalInfo)));
        } else {
            formData.set('additional_info', '{}');
        }
    } catch (error) {
        alert('Invalid JSON format in gallery images or additional info: ' + error.message);
        return;
    }
    
    // Show loading state
    const submitBtn = e.target.querySelector('button[type="submit"]');
    const originalText = submitBtn.innerHTML;
    submitBtn.disabled = true;
    submitBtn.innerHTML = '<i data-feather="loader" class="animate-spin"></i> Creating...';
    
    if (typeof feather !== 'undefined') {
        feather.replace();
    }
    
    try {
        const response = await fetch(`${API_BASE}?action=addEvent`, {
            method: 'POST',
            body: formData
        });
        
        const data = await response.json();
        
        if (data.success) {
            alert(data.message || 'Event created successfully');
            closeModal('add-event-modal');
            setTimeout(() => {
                window.location.reload();
            }, 500);
        } else {
            alert('Error: ' + (data.message || 'Failed to create event'));
        }
    } catch (error) {
        console.error('Error:', error);
        alert('An error occurred. Please try again.');
    } finally {
        submitBtn.disabled = false;
        submitBtn.innerHTML = originalText;
        if (typeof feather !== 'undefined') {
            feather.replace();
        }
    }
}

async function handleEditEvent(e) {
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
    
    // Convert form data to JSON
    const eventData = {
        id: eventId,
        title: formData.get('title'),
        description: formData.get('description'),
        subcategory_id: formData.get('subcategory_id'),
        venue_id: formData.get('venue_id'),
        date: formData.get('date'),
        end_date: formData.get('end_date') || null,
        price: parseFloat(formData.get('price')),
        discounted_price: formData.get('discounted_price') ? parseFloat(formData.get('discounted_price')) : null,
        image_url: formData.get('image_url') || '',
        total_tickets: parseInt(formData.get('total_tickets')),
        available_tickets: parseInt(formData.get('available_tickets')),
        min_tickets_per_booking: parseInt(formData.get('min_tickets_per_booking')) || 1,
        max_tickets_per_booking: parseInt(formData.get('max_tickets_per_booking')) || 10,
        terms_conditions: formData.get('terms_conditions') || '',
        status: formData.get('status') || 'draft'
    };
    
    // Handle JSON fields
    try {
        const galleryImages = formData.get('gallery_images');
        if (galleryImages && galleryImages.toString().trim() !== '') {
            eventData.gallery_images = JSON.parse(galleryImages);
        } else {
            eventData.gallery_images = [];
        }
        
        const additionalInfo = formData.get('additional_info');
        if (additionalInfo && additionalInfo.toString().trim() !== '') {
            eventData.additional_info = JSON.parse(additionalInfo);
        } else {
            eventData.additional_info = {};
        }
    } catch (error) {
        alert('Invalid JSON format: ' + error.message);
        return;
    }
    
    // Show loading state
    const submitBtn = e.target.querySelector('button[type="submit"]');
    const originalText = submitBtn.innerHTML;
    submitBtn.disabled = true;
    submitBtn.innerHTML = '<i data-feather="loader" class="animate-spin"></i> Updating...';
    
    if (typeof feather !== 'undefined') {
        feather.replace();
    }
    
    try {
        const response = await fetch(`${API_BASE}?action=updateEvent`, {
            method: 'PUT',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify(eventData)
        });
        
        const data = await response.json();
        
        if (data.success) {
            alert(data.message || 'Event updated successfully');
            closeModal('edit-event-modal');
            setTimeout(() => {
                window.location.reload();
            }, 500);
        } else {
            alert('Error: ' + (data.message || 'Failed to update event'));
        }
    } catch (error) {
        console.error('Error:', error);
        alert('An error occurred. Please try again.');
    } finally {
        submitBtn.disabled = false;
        submitBtn.innerHTML = originalText;
        if (typeof feather !== 'undefined') {
            feather.replace();
        }
    }
}

function confirmDeleteEvent(eventId, eventName) {
    if (!confirm(`Are you sure you want to delete "${eventName}"? This action cannot be undone.`)) {
        return;
    }
    
    deleteEvent(eventId);
}

async function deleteEvent(eventId) {
    try {
        const response = await fetch(`${API_BASE}?action=deleteEvent`, {
            method: 'DELETE',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify({ id: eventId })
        });
        
        const data = await response.json();
        
        if (data.success) {
            alert(data.message || 'Event deleted successfully');
            window.location.reload();
        } else {
            alert('Error: ' + (data.message || 'Failed to delete event'));
        }
    } catch (error) {
        console.error('Error:', error);
        alert('An error occurred. Please try again.');
    }
}

async function viewEventDetails(eventId) {
    try {
        // Use the public API for viewing details
        const response = await fetch(`../../../public/api/events_API.php?action=getOne&id=${eventId}`);
        
        if (!response.ok) {
            throw new Error('Failed to load event details');
        }
        
        const data = await response.json();
        
        if (data.success && data.event) {
            const event = data.event;
            
            let content = `
                <div class="event-details">
                    <div class="event-header">
                        ${event.image ? `<img src="${event.image}" alt="${event.title}" class="event-detail-image" style="width:100%;max-height:300px;object-fit:cover;border-radius:8px;margin-bottom:1rem;">` : ''}
                        <h3>${event.title}</h3>
                        <div class="event-meta" style="display:flex;gap:1rem;margin-bottom:1rem;">
                            <span class="category-badge">${event.main_category || 'Uncategorized'}</span>
                            <span class="status-badge ${event.status}">${event.status}</span>
                        </div>
                    </div>
                    
                    <div class="event-info-grid" style="display:grid;grid-template-columns:repeat(auto-fit,minmax(250px,1fr));gap:1.5rem;margin:1.5rem 0;">
                        <div class="info-section" style="background:#1a1a1a;padding:1rem;border-radius:8px;">
                            <h4 style="display:flex;align-items:center;gap:0.5rem;margin-bottom:0.5rem;">
                                <i data-feather="calendar"></i> Date & Time
                            </h4>
                            <p>${event.formattedDateTime || event.date}</p>
                            ${event.formattedEndDate ? `<p>to ${event.formattedEndDate} at ${event.formattedEndTime}</p>` : ''}
                        </div>
                        
                        <div class="info-section" style="background:#1a1a1a;padding:1rem;border-radius:8px;">
                            <h4 style="display:flex;align-items:center;gap:0.5rem;margin-bottom:0.5rem;">
                                <i data-feather="map-pin"></i> Venue
                            </h4>
                            <p><strong>${event.venue?.name || 'Unknown Venue'}</strong></p>
                            <p>${event.venue?.address || ''}, ${event.venue?.city || ''}, ${event.venue?.country || ''}</p>
                            ${event.venue?.capacity ? `<p>Capacity: ${event.venue.capacity.toLocaleString()}</p>` : ''}
                        </div>
                        
                        <div class="info-section" style="background:#1a1a1a;padding:1rem;border-radius:8px;">
                            <h4 style="display:flex;align-items:center;gap:0.5rem;margin-bottom:0.5rem;">
                                <i data-feather="tag"></i> Pricing
                            </h4>
                            <p>Regular: <strong>${event.formattedPrice || '$' + event.price}</strong></p>
                            ${event.formattedDiscountedPrice ? `<p>Discounted: <strong>${event.formattedDiscountedPrice}</strong></p>` : ''}
                        </div>
                        
                        <div class="info-section" style="background:#1a1a1a;padding:1rem;border-radius:8px;">
                            <h4 style="display:flex;align-items:center;gap:0.5rem;margin-bottom:0.5rem;">
                                <i data-feather="ticket"></i> Tickets
                            </h4>
                            <p>Available: <strong>${event.available_tickets} / ${event.total_tickets}</strong></p>
                            <p>Booking limits: ${event.min_tickets_per_booking || 1} - ${event.max_tickets_per_booking || 10} tickets</p>
                        </div>
                    </div>
                    
                    <div class="event-description" style="margin:1.5rem 0;">
                        <h4 style="margin-bottom:0.5rem;">Description</h4>
                        <p style="background:#1a1a1a;padding:1rem;border-radius:8px;">${event.description}</p>
                    </div>
            `;
            
            // Add terms & conditions if available
            if (event.terms_conditions) {
                content += `
                    <div class="event-terms" style="margin:1.5rem 0;">
                        <h4 style="display:flex;align-items:center;gap:0.5rem;margin-bottom:0.5rem;">
                            <i data-feather="file-text"></i> Terms & Conditions
                        </h4>
                        <p style="background:#1a1a1a;padding:1rem;border-radius:8px;">${event.terms_conditions}</p>
                    </div>
                `;
            }
            
            content += `</div>`;
            
            // Create or update modal for viewing
            let viewModal = document.getElementById('view-event-modal');
            if (!viewModal) {
                viewModal = document.createElement('div');
                viewModal.id = 'view-event-modal';
                viewModal.className = 'modal hidden';
                viewModal.innerHTML = `
                    <div class="modal-content large">
                        <div class="modal-header">
                            <h3>Event Details</h3>
                            <button class="close-modal" data-modal="view-event">
                                <i data-feather="x"></i>
                            </button>
                        </div>
                        <div id="event-details-container" style="padding:0 1.5rem 1.5rem;max-height:70vh;overflow-y:auto;"></div>
                    </div>
                `;
                document.body.appendChild(viewModal);
            }
            
            const container = document.getElementById('event-details-container') || 
                             viewModal.querySelector('#event-details-container');
            if (container) {
                container.innerHTML = content;
                openModal('view-event-modal');
                
                // Reinitialize feather icons
                if (typeof feather !== 'undefined') {
                    setTimeout(() => feather.replace(), 100);
                }
            }
        } else {
            alert('Error loading event: ' + (data.message || 'Event not found'));
        }
    } catch (error) {
        console.error('Error loading event details:', error);
        alert('Error loading event details. Please try again.');
    }
}

function filterEvents() {
    const searchTerm = document.getElementById('event-search').value.toLowerCase();
    const categoryFilter = document.getElementById('event-category-filter').value;
    const statusFilter = document.getElementById('event-status-filter').value;
    
    const rows = document.querySelectorAll('#events-table-body tr:not(.empty-state)');
    let visibleCount = 0;
    
    rows.forEach(row => {
        const titleCell = row.querySelector('td:nth-child(2)');
        const categoryCell = row.querySelector('td:nth-child(3) .category-badge');
        const rowStatus = row.getAttribute('data-status');
        
        const title = titleCell ? titleCell.textContent.toLowerCase() : '';
        const category = categoryCell ? categoryCell.textContent : '';
        
        let shouldShow = true;
        
        // Search filter
        if (searchTerm && !title.includes(searchTerm)) {
            shouldShow = false;
        }
        
        // Category filter
        if (categoryFilter && category !== categoryFilter) {
            shouldShow = false;
        }
        
        // Status filter
        if (statusFilter && rowStatus !== statusFilter) {
            shouldShow = false;
        }
        
        if (shouldShow) {
            row.style.display = '';
            visibleCount++;
        } else {
            row.style.display = 'none';
        }
    });
    
    // Update counts
    document.getElementById('events-start').textContent = visibleCount > 0 ? '1' : '0';
    document.getElementById('events-end').textContent = visibleCount;
}

function openModal(modalId) {
    const modal = document.getElementById(modalId);
    if (modal) {
        modal.classList.remove('hidden');
        document.body.style.overflow = 'hidden';
        
        // Reinitialize feather icons
        if (typeof feather !== 'undefined') {
            setTimeout(() => feather.replace(), 100);
        }
    }
}

function closeModal(modalId) {
    const modal = document.getElementById(modalId);
    if (modal) {
        modal.classList.add('hidden');
        document.body.style.overflow = 'auto';
    }
}
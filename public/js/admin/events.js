// events.js - Clean Admin Events Management with File Upload
const API_BASE = '../../../public/api/events_API.php';
const UPLOAD_API_URL = '/event-booking-website/public/api/uploads.php';

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
    
    // Venue change - load seating type and show ticket categories
    const venueSelect = document.getElementById('admin-event-venue');
    const editVenueSelect = document.getElementById('admin-edit-event-venue');
    
    if (venueSelect) {
        venueSelect.addEventListener('change', function() {
            if (this.value) {
                loadTicketCategories(this.value, 'add');
            } else {
                const section = document.getElementById('ticket-categories-section');
                if (section) section.style.display = 'none';
            }
        });
    }
    
    if (editVenueSelect) {
        editVenueSelect.addEventListener('change', function() {
            if (this.value) {
                loadTicketCategories(this.value, 'edit');
            } else {
                const section = document.getElementById('edit-ticket-categories-section');
                if (section) section.style.display = 'none';
            }
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
    
    // Event image upload handling
    const eventImageInput = document.getElementById('admin-event-image-file');
    const eventImagePreview = document.getElementById('admin-event-image-preview');
    
    if (eventImageInput && eventImagePreview) {
        eventImageInput.addEventListener('change', function(e) {
            const file = e.target.files[0];
            if (file) {
                if (!validateImageFile(file)) {
                    showEventAlert('Error', 'Please select a valid image file (PNG, JPG, GIF, WebP) under 10MB');
                    this.value = '';
                    return;
                }
                
                const reader = new FileReader();
                reader.onload = function(e) {
                    eventImagePreview.querySelector('img').src = e.target.result;
                    eventImagePreview.classList.remove('hidden');
                };
                reader.readAsDataURL(file);
            }
        });
    }
    
    // Event gallery images upload
    const galleryImagesInput = document.getElementById('admin-event-gallery-files');
    const galleryPreview = document.getElementById('admin-event-gallery-preview');
    
    if (galleryImagesInput && galleryPreview) {
        galleryImagesInput.addEventListener('change', function(e) {
            const files = Array.from(e.target.files);
            galleryPreview.innerHTML = '';
            
            files.forEach(file => {
                if (!validateImageFile(file)) {
                    showEventAlert('Error', 'One or more files are invalid. Please select valid image files under 10MB each.');
                    this.value = '';
                    galleryPreview.innerHTML = '';
                    return;
                }
                
                const reader = new FileReader();
                reader.onload = function(e) {
                    const img = document.createElement('img');
                    img.src = e.target.result;
                    img.style.width = '100%';
                    img.style.height = '100px';
                    img.style.objectFit = 'cover';
                    img.style.borderRadius = '8px';
                    galleryPreview.appendChild(img);
                };
                reader.readAsDataURL(file);
            });
        });
    }
    
    // Edit event image upload handling
    const editEventImageInput = document.getElementById('admin-edit-event-image-file');
    const editEventCurrentImage = document.getElementById('admin-edit-event-current-image');
    
    if (editEventImageInput && editEventCurrentImage) {
        editEventImageInput.addEventListener('change', function(e) {
            const file = e.target.files[0];
            if (file) {
                if (!validateImageFile(file)) {
                    showEventAlert('Error', 'Please select a valid image file (PNG, JPG, GIF, WebP) under 10MB');
                    this.value = '';
                    return;
                }
                
                const reader = new FileReader();
                reader.onload = function(e) {
                    editEventCurrentImage.src = e.target.result;
                    editEventCurrentImage.style.display = 'block';
                };
                reader.readAsDataURL(file);
            }
        });
    }
    
    // Edit event gallery images upload
    const editGalleryImagesInput = document.getElementById('admin-edit-event-gallery-files');
    const editGalleryPreview = document.getElementById('admin-edit-event-gallery-preview');
    
    if (editGalleryImagesInput && editGalleryPreview) {
        editGalleryImagesInput.addEventListener('change', function(e) {
            const files = Array.from(e.target.files);
            
            files.forEach(file => {
                if (!validateImageFile(file)) {
                    showEventAlert('Error', 'One or more files are invalid. Please select valid image files under 10MB each.');
                    this.value = '';
                    return;
                }
                
                const reader = new FileReader();
                reader.onload = function(e) {
                    const img = document.createElement('img');
                    img.src = e.target.result;
                    img.style.width = '100%';
                    img.style.height = '100px';
                    img.style.objectFit = 'cover';
                    img.style.borderRadius = '8px';
                    editGalleryPreview.appendChild(img);
                };
                reader.readAsDataURL(file);
            });
        });
    }
    
    // File upload drag and drop
    const fileUploads = document.querySelectorAll('.file-upload');
    fileUploads.forEach(upload => {
        upload.addEventListener('dragover', function(e) {
            e.preventDefault();
            this.classList.add('drag-over');
        });
        
        upload.addEventListener('dragleave', function(e) {
            e.preventDefault();
            this.classList.remove('drag-over');
        });
        
        upload.addEventListener('drop', function(e) {
            e.preventDefault();
            this.classList.remove('drag-over');
            
            const files = e.dataTransfer.files;
            if (files.length > 0) {
                const input = this.querySelector('input[type="file"]');
                
                // Create a new FileList
                const dataTransfer = new DataTransfer();
                for (let i = 0; i < files.length; i++) {
                    dataTransfer.items.add(files[i]);
                }
                input.files = dataTransfer.files;
                
                // Trigger change event
                input.dispatchEvent(new Event('change', { bubbles: true }));
            }
        });
        
        // Click to open file dialog
        upload.addEventListener('click', function(e) {
            if (!e.target.closest('input[type="file"]')) {
                const input = this.querySelector('input[type="file"]');
                input.click();
            }
        });
    });
}

async function loadTicketCategories(venueId, formType, existingTicketCategories = null) {
    // Determine which container/section to use based on form type
    const isEditForm = formType === 'edit';
    const containerId = isEditForm ? 'edit-ticket-categories-container' : 'ticket-categories-container';
    const sectionId = isEditForm ? 'edit-ticket-categories-section' : 'ticket-categories-section';
    
    if (!venueId) {
        const section = document.getElementById(sectionId);
        if (section) section.style.display = 'none';
        return;
    }
    
    const container = document.getElementById(containerId);
    const section = document.getElementById(sectionId);
    
    if (!container || !section) {
        console.error('Ticket categories container or section not found!', { containerId, sectionId });
        return;
    }
    
    // Show loading state
    container.innerHTML = '<p style="padding: 1rem; color: #666;">Loading venue information...</p>';
    section.style.display = 'block';
    
    try {
        const response = await fetch(`../../../public/api/venue.php?action=get&id=${venueId}`);
        const data = await response.json();
        
        if (!data.success) {
            console.error('API error:', data.message);
            container.innerHTML = `<p style="padding: 1rem; color: #dc2626;">Error: ${data.message || 'Failed to load venue'}</p>`;
            return;
        }
        
        if (!data.venue) {
            console.error('No venue data in response');
            container.innerHTML = '<p style="padding: 1rem; color: #dc2626;">Venue not found</p>';
            return;
        }
        
        if (!data.venue.seating_type) {
            console.warn('Venue has no seating_type set:', data.venue);
            container.innerHTML = `
                <div style="padding: 1rem; background: #fef3c7; border: 1px solid #fbbf24; border-radius: 8px; color: #92400e;">
                    <strong>⚠️ Warning:</strong> This venue doesn't have a seating type set.<br>
                    Please go to <strong>Locations</strong> section and set the seating type for this venue first.
                </div>
            `;
            section.style.display = 'block';
            return;
        }
        
        const seatingType = data.venue.seating_type;
        container.innerHTML = '';
        
        let categories = [];
        if (seatingType === 'stadium') {
            categories = ['Cat1', 'Cat2', 'Cat3'];
        } else if (seatingType === 'theatre') {
            categories = ['Gold', 'Premium', 'Regular'];
        } else if (seatingType === 'standing') {
            categories = ['Regular', 'Fanpit', 'Golden Circle'];
        } else {
            container.innerHTML = `<p style="padding: 1rem; color: #dc2626;">Unknown seating type: ${seatingType}</p>`;
            return;
        }
        
        // If editing, use provided existing ticket categories
        let existingCategories = {};
        if (isEditForm && existingTicketCategories && Array.isArray(existingTicketCategories)) {
            existingTicketCategories.forEach(cat => {
                existingCategories[cat.category_name] = cat;
            });
        }
        
        categories.forEach((category, index) => {
            const existing = existingCategories[category] || {};
            const categoryDiv = document.createElement('div');
            categoryDiv.className = 'form-group';
            categoryDiv.style.marginBottom = '1rem';
            categoryDiv.innerHTML = `
                <div style="background: #f3f4f6; padding: 1rem; border-radius: 8px; border: 1px solid #e5e7eb;">
                    <label style="font-weight: 600; margin-bottom: 0.5rem; display: block; color: #111;">${category} Category *</label>
                    <div class="form-group two-columns" style="margin: 0;">
                        <div>
                            <label style="font-size: 0.875rem; color: #374151; margin-bottom: 0.25rem; display: block;">Total Tickets *</label>
                            <input type="number" 
                                   name="ticket_categories[${category}][total_tickets]" 
                                   class="ticket-category-input" 
                                   data-category="${category}"
                                   min="1" 
                                   required
                                   value="${existing.total_tickets || ''}"
                                   placeholder="e.g., 2000"
                                   style="width: 100%; padding: 0.5rem; border: 1px solid #d1d5db; border-radius: 4px;">
                        </div>
                        <div>
                            <label style="font-size: 0.875rem; color: #374151; margin-bottom: 0.25rem; display: block;">Price per Ticket *</label>
                            <input type="number" 
                                   name="ticket_categories[${category}][price]" 
                                   class="ticket-category-input" 
                                   data-category="${category}"
                                   min="0" 
                                   step="0.01"
                                   required
                                   value="${existing.price || ''}"
                                   placeholder="e.g., 50.00"
                                   style="width: 100%; padding: 0.5rem; border: 1px solid #d1d5db; border-radius: 4px;">
                        </div>
                    </div>
                    <input type="hidden" name="ticket_categories[${category}][category_name]" value="${category}">
                </div>
            `;
            container.appendChild(categoryDiv);
        });
        
        section.style.display = 'block';
        
    } catch (error) {
        console.error('Error loading ticket categories:', error);
        container.innerHTML = `<p style="padding: 1rem; color: #dc2626;">Error: ${error.message}</p>`;
        section.style.display = 'block';
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
        
        // Hide ticket categories section
        const ticketSection = document.getElementById('ticket-categories-section');
        if (ticketSection) {
            ticketSection.style.display = 'none';
        }
        
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
        
        // Clear image previews
        const imagePreview = document.getElementById('admin-event-image-preview');
        if (imagePreview) {
            imagePreview.classList.add('hidden');
            imagePreview.querySelector('img').src = '';
        }
        
        const galleryPreview = document.getElementById('admin-event-gallery-preview');
        if (galleryPreview) {
            galleryPreview.innerHTML = '';
        }
        
        // Clear file inputs
        const imageInput = document.getElementById('admin-event-image-file');
        if (imageInput) imageInput.value = '';
        
        const galleryInput = document.getElementById('admin-event-gallery-files');
        if (galleryInput) galleryInput.value = '';
    }
    
    openModal('add-event-modal');
}

async function editEvent(eventId) {
    if (!eventId) {
        showEventAlert('Error', 'Event ID is required');
        return;
    }
    
    try {
        const response = await fetch(`${API_BASE}?action=getEvent&id=${eventId}`);
        
        if (!response.ok) {
            const errorText = await response.text();
            console.error('API response error:', response.status, errorText);
            throw new Error(`Failed to load event data: ${response.status}`);
        }
        
        const data = await response.json();
        
        if (!data) {
            throw new Error('No data received from server');
        }
        
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
            
            // Handle main image
            const currentImage = document.getElementById('admin-edit-event-current-image');
            const imageUrlInput = document.getElementById('admin-edit-event-image-url');
            if (event.image_url) {
                currentImage.src = event.image_url;
                currentImage.style.display = 'block';
                imageUrlInput.value = event.image_url;
            } else {
                currentImage.style.display = 'none';
                imageUrlInput.value = '';
            }
            
            // Handle gallery images
            const galleryPreview = document.getElementById('admin-edit-event-gallery-preview');
            const galleryImagesInput = document.getElementById('admin-edit-event-gallery-images');
            if (galleryPreview && galleryImagesInput) {
                galleryPreview.innerHTML = '';
                
                if (event.gallery_images && Array.isArray(event.gallery_images) && event.gallery_images.length > 0) {
                    event.gallery_images.forEach(imageUrl => {
                        const img = document.createElement('img');
                        img.src = imageUrl;
                        img.style.width = '100%';
                        img.style.height = '100px';
                        img.style.objectFit = 'cover';
                        img.style.borderRadius = '8px';
                        galleryPreview.appendChild(img);
                    });
                    galleryImagesInput.value = JSON.stringify(event.gallery_images);
                } else {
                    galleryImagesInput.value = '[]';
                }
            }
            
            // Clear file inputs
            document.getElementById('admin-edit-event-image-file').value = '';
            document.getElementById('admin-edit-event-gallery-files').value = '';
            
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
            
            // Load ticket categories AFTER all form fields are populated (pass existing categories if available)
            if (event.venue_id) {
                await loadTicketCategories(event.venue_id, 'edit', event.ticket_categories || []);
            }
            
            openModal('edit-event-modal');
            
        } else {
            showEventAlert('Error', data.message || 'Event not found');
        }
    } catch (error) {
        console.error('Error loading event:', error);
        showEventAlert('Error', 'Error loading event data. Please try again.');
    }
}

async function handleAddEvent(e) {
    e.preventDefault();
    
    // Show loading state
    const submitBtn = e.target.querySelector('button[type="submit"]');
    const originalText = submitBtn.innerHTML;
    submitBtn.disabled = true;
    submitBtn.innerHTML = '<i data-feather="loader" class="animate-spin"></i> Creating...';
    
    if (typeof feather !== 'undefined') {
        feather.replace();
    }
    
    try {
        let imageUrl = '';
        let galleryUrls = [];
        
        // Upload main image if provided
        const imageFile = document.getElementById('admin-event-image-file').files[0];
        if (imageFile) {
            const uploadResult = await uploadImage(imageFile, 'events');
            if (uploadResult.success) {
                imageUrl = uploadResult.url;
                document.getElementById('admin-event-image-url').value = imageUrl;
            } else {
                throw new Error('Failed to upload main image: ' + uploadResult.message);
            }
        }
        
        // Upload gallery images if provided
        const galleryFiles = Array.from(document.getElementById('admin-event-gallery-files').files);
        if (galleryFiles.length > 0) {
            galleryUrls = await uploadMultipleImages(galleryFiles, 'event_gallery');
            document.getElementById('admin-event-gallery-images').value = JSON.stringify(galleryUrls);
        }
        
        // Get form data
        const formData = new FormData(e.target);
        
        // Collect ticket categories directly from form inputs (more reliable than FormData)
        const ticketCategoriesObj = {};
        const ticketCategoryInputs = document.querySelectorAll('#ticket-categories-container input.ticket-category-input');
        const ticketCategoryHiddenInputs = document.querySelectorAll('#ticket-categories-container input[type="hidden"][name*="category_name"]');
        
        // Get data from visible inputs
        ticketCategoryInputs.forEach(input => {
            const categoryName = input.getAttribute('data-category');
            if (!categoryName) return;
            
            if (!ticketCategoriesObj[categoryName]) {
                ticketCategoriesObj[categoryName] = {};
            }
            
            if (input.name.includes('[total_tickets]')) {
                ticketCategoriesObj[categoryName].total_tickets = input.value;
            } else if (input.name.includes('[price]')) {
                ticketCategoriesObj[categoryName].price = input.value;
            }
        });
        
        // Get category names from hidden inputs
        ticketCategoryHiddenInputs.forEach(input => {
            const match = input.name.match(/ticket_categories\[([^\]]+)\]/);
            if (match) {
                const categoryName = match[1];
                if (ticketCategoriesObj[categoryName]) {
                    ticketCategoriesObj[categoryName].category_name = input.value || categoryName;
                }
            }
        });
        
        
        // Check if ticket categories section is visible and has data
        const ticketSection = document.getElementById('ticket-categories-section');
        const isTicketSectionVisible = ticketSection && ticketSection.style.display !== 'none';
        
        if (isTicketSectionVisible && Object.keys(ticketCategoriesObj).length === 0) {
            throw new Error('Please fill in ticket categories. Make sure you selected a venue with seating type and filled in all category fields.');
        }
        
        // Validate each category has tickets and price
        for (const [categoryName, category] of Object.entries(ticketCategoriesObj)) {
            const totalTickets = parseInt(category.total_tickets || 0);
            const price = parseFloat(category.price || 0);
            
            if (totalTickets <= 0) {
                throw new Error(`Please enter a valid ticket count (greater than 0) for ${categoryName} category`);
            }
            if (isNaN(price) || price < 0) {
                throw new Error(`Please enter a valid price (0 or greater) for ${categoryName} category`);
            }
        }
        
        // Validate required fields (removed price and total_tickets - they're calculated from categories)
        const requiredFields = ['title', 'description', 'subcategory_id', 'venue_id', 'date'];
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
            throw new Error(`Please fill in the ${missingField} field`);
        }
        
        // Set price and total_tickets as hidden values (will be calculated on server)
        formData.set('price', '0'); // Will be calculated from categories
        formData.set('total_tickets', '0'); // Will be calculated from categories
        
        // Clear existing ticket_categories from FormData
        const keysToDelete = [];
        formData.forEach((value, key) => {
            if (key.startsWith('ticket_categories[')) {
                keysToDelete.push(key);
            }
        });
        keysToDelete.forEach(key => formData.delete(key));
        
        // Send ticket categories as JSON string (more reliable than nested FormData)
        if (Object.keys(ticketCategoriesObj).length > 0) {
            // Convert to array format for easier PHP parsing
            const ticketCategoriesArray = Object.entries(ticketCategoriesObj).map(([categoryName, category]) => ({
                category_name: category.category_name || categoryName,
                total_tickets: parseInt(category.total_tickets || 0),
                price: parseFloat(category.price || 0)
            }));
            formData.set('ticket_categories_json', JSON.stringify(ticketCategoriesArray));
        }
        
        // Convert JSON fields
        try {
            const galleryImages = document.getElementById('admin-event-gallery-images').value;
            if (galleryImages && galleryImages.trim() !== '') {
                formData.set('gallery_images', galleryImages);
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
            throw new Error('Invalid JSON format in gallery images or additional info: ' + error.message);
        }
        
        // Send to API
        const response = await fetch(`${API_BASE}?action=addEvent`, {
            method: 'POST',
            body: formData
        });
        
        const data = await response.json();
        
        if (data.success) {
            showEventAlert('Success', data.message || 'Event created successfully');
            closeModal('add-event-modal');
            setTimeout(() => {
                window.location.reload();
            }, 500);
        } else {
            throw new Error(data.message || 'Failed to create event');
        }
    } catch (error) {
        console.error('Error:', error);
        showEventAlert('Error', error.message);
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
    
    // Show loading state
    const submitBtn = e.target.querySelector('button[type="submit"]');
    const originalText = submitBtn.innerHTML;
    submitBtn.disabled = true;
    submitBtn.innerHTML = '<i data-feather="loader" class="animate-spin"></i> Updating...';
    
    if (typeof feather !== 'undefined') {
        feather.replace();
    }
    
    try {
        const eventId = document.getElementById('admin-edit-event-id').value;
        if (!eventId) {
            throw new Error('Event ID is required');
        }
        
        let imageUrl = document.getElementById('admin-edit-event-image-url').value;
        let galleryUrls = [];
        
        // Upload new main image if provided
        const imageFile = document.getElementById('admin-edit-event-image-file').files[0];
        if (imageFile) {
            const uploadResult = await uploadImage(imageFile, 'events');
            if (uploadResult.success) {
                imageUrl = uploadResult.url;
            } else {
                throw new Error('Failed to upload main image: ' + uploadResult.message);
            }
        }
        
        // Upload new gallery images if provided
        const galleryFiles = Array.from(document.getElementById('admin-edit-event-gallery-files').files);
        if (galleryFiles.length > 0) {
            const newGalleryUrls = await uploadMultipleImages(galleryFiles, 'event_gallery');
            
            // Get existing gallery images
            const existingGallery = document.getElementById('admin-edit-event-gallery-images').value;
            let existingUrls = [];
            if (existingGallery && existingGallery.trim() !== '') {
                try {
                    existingUrls = JSON.parse(existingGallery);
                } catch (e) {
                    existingUrls = [];
                }
            }
            
            // Combine existing and new images
            galleryUrls = [...existingUrls, ...newGalleryUrls];
        } else {
            // Use existing gallery images
            const existingGallery = document.getElementById('admin-edit-event-gallery-images').value;
            if (existingGallery && existingGallery.trim() !== '') {
                try {
                    galleryUrls = JSON.parse(existingGallery);
                } catch (e) {
                    galleryUrls = [];
                }
            }
        }
        
        // Collect ticket categories (same as add event, but check both containers)
        const ticketCategoriesObj = {};
        const ticketCategoryInputs = document.querySelectorAll('#ticket-categories-container input.ticket-category-input, #edit-ticket-categories-container input.ticket-category-input');
        const ticketCategoryHiddenInputs = document.querySelectorAll('#ticket-categories-container input[type="hidden"][name*="category_name"], #edit-ticket-categories-container input[type="hidden"][name*="category_name"]');
        
        ticketCategoryInputs.forEach(input => {
            const categoryName = input.getAttribute('data-category');
            if (!categoryName) return;
            
            if (!ticketCategoriesObj[categoryName]) {
                ticketCategoriesObj[categoryName] = {};
            }
            
            if (input.name.includes('[total_tickets]')) {
                ticketCategoriesObj[categoryName].total_tickets = input.value;
            } else if (input.name.includes('[price]')) {
                ticketCategoriesObj[categoryName].price = input.value;
            }
        });
        
        ticketCategoryHiddenInputs.forEach(input => {
            const match = input.name.match(/ticket_categories\[([^\]]+)\]/);
            if (match) {
                const categoryName = match[1];
                if (ticketCategoriesObj[categoryName]) {
                    ticketCategoriesObj[categoryName].category_name = input.value || categoryName;
                }
            }
        });
        
        // Convert to array format
        const ticketCategoriesArray = Object.entries(ticketCategoriesObj).map(([categoryName, category]) => ({
            category_name: category.category_name || categoryName,
            total_tickets: parseInt(category.total_tickets || 0),
            price: parseFloat(category.price || 0)
        }));
        
        // Prepare event data
        const eventData = {
            id: eventId,
            title: document.getElementById('admin-edit-event-title').value,
            description: document.getElementById('admin-edit-event-description').value,
            subcategory_id: document.getElementById('admin-edit-event-subcategory').value,
            venue_id: document.getElementById('admin-edit-event-venue').value,
            date: document.getElementById('admin-edit-event-date').value,
            end_date: document.getElementById('admin-edit-event-end-date').value || null,
            price: parseFloat(document.getElementById('admin-edit-event-price').value),
            discounted_price: document.getElementById('admin-edit-event-discounted-price').value ? 
                parseFloat(document.getElementById('admin-edit-event-discounted-price').value) : null,
            image_url: imageUrl || '',
            gallery_images: galleryUrls,
            total_tickets: parseInt(document.getElementById('admin-edit-event-total-tickets').value),
            available_tickets: parseInt(document.getElementById('admin-edit-event-available-tickets').value),
            min_tickets_per_booking: parseInt(document.getElementById('admin-edit-event-min-tickets').value) || 1,
            max_tickets_per_booking: parseInt(document.getElementById('admin-edit-event-max-tickets').value) || 10,
            terms_conditions: document.getElementById('admin-edit-event-terms').value || '',
            status: document.getElementById('admin-edit-event-status').value || 'draft'
        };
        
        // Add ticket categories if provided
        if (ticketCategoriesArray.length > 0) {
            eventData.ticket_categories_json = JSON.stringify(ticketCategoriesArray);
        }
        
        // Handle additional info
        const additionalInfoTextarea = document.getElementById('admin-edit-event-additional-info');
        if (additionalInfoTextarea && additionalInfoTextarea.value.trim() !== '') {
            try {
                eventData.additional_info = JSON.parse(additionalInfoTextarea.value);
            } catch (error) {
                throw new Error('Invalid JSON format in additional info: ' + error.message);
            }
        } else {
            eventData.additional_info = {};
        }
        
        // Validate required fields
        const requiredFields = ['title', 'description', 'subcategory_id', 'venue_id', 'date', 'price', 'total_tickets', 'available_tickets'];
        let isValid = true;
        let missingField = '';
        
        requiredFields.forEach(field => {
            if (!eventData[field] || eventData[field].toString().trim() === '') {
                isValid = false;
                missingField = field.replace('_', ' ');
                return;
            }
        });
        
        if (!isValid) {
            throw new Error(`Please fill in the ${missingField} field`);
        }
        
        // Send to API
        const response = await fetch(`${API_BASE}?action=updateEvent`, {
            method: 'PUT',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify(eventData)
        });
        
        const data = await response.json();
        
        if (data.success) {
            showEventAlert('Success', data.message || 'Event updated successfully');
            closeModal('edit-event-modal');
            setTimeout(() => {
                window.location.reload();
            }, 500);
        } else {
            throw new Error(data.message || 'Failed to update event');
        }
    } catch (error) {
        console.error('Error:', error);
        showEventAlert('Error', error.message);
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
            showEventAlert('Success', data.message || 'Event deleted successfully');
            window.location.reload();
        } else {
            showEventAlert('Error', data.message || 'Failed to delete event');
        }
    } catch (error) {
        console.error('Error:', error);
        showEventAlert('Error', 'An error occurred. Please try again.');
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
            showEventAlert('Error', data.message || 'Event not found');
        }
    } catch (error) {
        console.error('Error loading event details:', error);
        showEventAlert('Error', 'Error loading event details. Please try again.');
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

// File upload helper functions
async function uploadImage(file, type = 'events') {
    const formData = new FormData();
    formData.append('type', type);
    formData.append('image', file);
    
    try {
        const response = await fetch(UPLOAD_API_URL, {
            method: 'POST',
            body: formData
        });
        
        if (!response.ok) {
            throw new Error(`HTTP error! status: ${response.status}`);
        }
        
        const data = await response.json();
        return data;
    } catch (error) {
        console.error('Upload error:', error);
        return { success: false, message: 'Upload failed: ' + error.message };
    }
}

async function uploadMultipleImages(files, type = 'event_gallery') {
    const uploadPromises = [];
    
    for (const file of files) {
        uploadPromises.push(uploadImage(file, type));
    }
    
    try {
        const results = await Promise.all(uploadPromises);
        const successfulUploads = results.filter(result => result.success);
        return successfulUploads.map(result => result.url);
    } catch (error) {
        console.error('Multiple upload error:', error);
        return [];
    }
}

function validateImageFile(file) {
    // Check file size (10MB max for events)
    const maxSize = 10 * 1024 * 1024; // 10MB
    if (file.size > maxSize) {
        return false;
    }
    
    // Check file type
    const allowedTypes = ['image/jpeg', 'image/jpg', 'image/png', 'image/gif', 'image/webp'];
    if (!allowedTypes.includes(file.type.toLowerCase())) {
        return false;
    }
    
    return true;
}

function showEventAlert(title, message) {
    alert(`${title}: ${message}`);
}
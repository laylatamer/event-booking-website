// /public/js/admin/categories.js - COMPLETE UPDATED VERSION WITH IMAGE UPLOAD
const API_BASE_URL = '/api/categories_API.php';
const UPLOAD_API_URL = '/api/uploads.php';

let subcategories = [];
let isInitialized = false;

document.addEventListener('DOMContentLoaded', function() {
    const categoriesSection = document.getElementById('categories-section');
    if (categoriesSection) {
        initializeCategories();
    }
});

function initializeCategories() {
    if (isInitialized) return;
    
    // Setup modal handling
    setupModalHandling();
    
    // Setup event listeners
    setupEventListeners();
    
    // Setup file uploads
    setupFileUploads();
    
    // Load data from API
    loadCategories();
    
    isInitialized = true;
}

function setupModalHandling() {
    // Close modal when clicking X or cancel button
    document.addEventListener('click', function(e) {
        // Handle close buttons
        if (e.target.closest('.close-modal')) {
            const btn = e.target.closest('.close-modal');
            const modalName = btn.getAttribute('data-modal');
            if (modalName) {
                closeModal(`${modalName}-modal`);
            }
        }
        
        // Handle cancel buttons
        if (e.target.closest('.secondary-btn[data-modal]')) {
            const btn = e.target.closest('.secondary-btn[data-modal]');
            const modalName = btn.getAttribute('data-modal');
            if (modalName) {
                closeModal(`${modalName}-modal`);
            }
        }
        
        // Close modal when clicking outside
        if (e.target.classList.contains('modal')) {
            e.target.classList.add('hidden');
        }
    });
}

function setupEventListeners() {
    // Use event delegation for dynamic content
    document.addEventListener('click', function(e) {
        // Add subcategory buttons
        if (e.target.closest('.add-subcategory-btn')) {
            e.preventDefault();
            const btn = e.target.closest('.add-subcategory-btn');
            handleAddSubcategoryClick(btn);
        }
        
        // Edit subcategory buttons
        if (e.target.closest('.edit-subcategory')) {
            e.preventDefault();
            const btn = e.target.closest('.edit-subcategory');
            const subcategoryId = parseInt(btn.getAttribute('data-id'));
            editSubcategory(subcategoryId);
        }
        
        // Delete subcategory buttons
        if (e.target.closest('.delete-subcategory')) {
            e.preventDefault();
            const btn = e.target.closest('.delete-subcategory');
            const subcategoryId = parseInt(btn.getAttribute('data-id'));
            deleteSubcategory(subcategoryId);
        }
    });
    
    // Add subcategory form submission
    const addSubcategoryForm = document.getElementById('add-subcategory-form');
    if (addSubcategoryForm) {
        addSubcategoryForm.addEventListener('submit', function(e) {
            e.preventDefault();
            handleAddSubcategorySubmit(e);
        });
    } else {
        console.warn('Add subcategory form not found');
    }
    
    // Edit subcategory form submission
    const editSubcategoryForm = document.getElementById('edit-subcategory-form');
    if (editSubcategoryForm) {
        editSubcategoryForm.addEventListener('submit', function(e) {
            e.preventDefault();
            handleEditSubcategorySubmit(e);
        });
    } else {
        console.warn('Edit subcategory form not found');
    }
}

function setupFileUploads() {
    
    // Subcategory image upload
    const subcategoryImageInput = document.getElementById('subcategory-image');
    const subcategoryImagePreview = document.getElementById('subcategory-image-preview');
    
    if (subcategoryImageInput && subcategoryImagePreview) {
        subcategoryImageInput.addEventListener('change', function(e) {
            const file = e.target.files[0];
            if (file) {
                // Validate file
                if (!validateImageFile(file)) {
                    showAlert('Error', 'Please select a valid image file (PNG, JPG, GIF, WebP) under 5MB');
                    this.value = '';
                    return;
                }
                
                const reader = new FileReader();
                reader.onload = function(e) {
                    subcategoryImagePreview.querySelector('img').src = e.target.result;
                    subcategoryImagePreview.classList.remove('hidden');
                };
                reader.readAsDataURL(file);
            }
        });
    }
    
    // Edit subcategory image upload
    const editSubcategoryImageInput = document.getElementById('edit-subcategory-image');
    const editSubcategoryCurrentImage = document.getElementById('edit-subcategory-current-image');
    
    if (editSubcategoryImageInput && editSubcategoryCurrentImage) {
        editSubcategoryImageInput.addEventListener('change', function(e) {
            const file = e.target.files[0];
            if (file) {
                // Validate file
                if (!validateImageFile(file)) {
                    showAlert('Error', 'Please select a valid image file (PNG, JPG, GIF, WebP) under 5MB');
                    this.value = '';
                    return;
                }
                
                const reader = new FileReader();
                reader.onload = function(e) {
                    editSubcategoryCurrentImage.src = e.target.result;
                    editSubcategoryCurrentImage.style.display = 'block';
                };
                reader.readAsDataURL(file);
            }
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
                const file = files[0];
                const input = this.querySelector('input[type="file"]');
                
                // Create a new FileList
                const dataTransfer = new DataTransfer();
                dataTransfer.items.add(file);
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

function validateImageFile(file) {
    // Check file size (5MB max)
    const maxSize = 5 * 1024 * 1024; // 5MB
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

async function uploadImage(file, type = 'subcategories') {
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

function handleAddSubcategoryClick(btn) {
    const mainCategoryId = btn.getAttribute('data-main-category');
    const mainCategoryName = btn.closest('.category-section').querySelector('h3').textContent
        .replace(/[^a-zA-Z\s]/g, '').trim();
    
    
    const mainCategoryIdInput = document.getElementById('subcategory-main-category-id');
    const categoryNameSpan = document.getElementById('subcategory-main-category-name');
    const categoryIconSpan = document.getElementById('subcategory-category-icon');
    
    if (mainCategoryIdInput && categoryNameSpan && categoryIconSpan) {
        mainCategoryIdInput.value = mainCategoryId;
        
        // Set category name and icon based on category type
        if (mainCategoryId === '1') { // Sports
            categoryNameSpan.textContent = 'Sports';
            categoryIconSpan.innerHTML = '<i data-feather="activity"></i>';
            
            // Add sports class to parent container
            const categoryDisplay = categoryNameSpan.closest('.main-category-display');
            if (categoryDisplay) {
                categoryDisplay.className = 'main-category-display sports';
            }
        } else if (mainCategoryId === '2') { // Entertainment
            categoryNameSpan.textContent = 'Entertainment';
            categoryIconSpan.innerHTML = '<i data-feather="music"></i>';
            
            // Add entertainment class to parent container
            const categoryDisplay = categoryNameSpan.closest('.main-category-display');
            if (categoryDisplay) {
                categoryDisplay.className = 'main-category-display entertainment';
            }
        }
        
        // Reset form
        const form = document.getElementById('add-subcategory-form');
        if (form) {
            form.reset();
            
            // Clear image preview
            const imagePreview = document.getElementById('subcategory-image-preview');
            if (imagePreview) {
                imagePreview.classList.add('hidden');
                imagePreview.querySelector('img').src = '';
            }
            
            // Reset file input
            const imageInput = document.getElementById('subcategory-image');
            if (imageInput) {
                imageInput.value = '';
            }
            
            // Set default status
            const statusSelect = document.getElementById('subcategory-status');
            if (statusSelect) {
                statusSelect.value = 'active';
            }
        }
        
        // Initialize feather icons
        if (typeof feather !== 'undefined') {
            setTimeout(() => feather.replace(), 100);
        }
        
        openModal('add-subcategory-modal');
    } else {
        console.error('Modal elements not found!');
    }
}

// Modal functions
function openModal(modalId) {
    const modal = document.getElementById(modalId);
    if (modal) {
        modal.classList.remove('hidden');
        document.body.style.overflow = 'hidden';
        
        // Replace feather icons
        if (typeof feather !== 'undefined') {
            feather.replace();
        }
    } else {
        console.error('Modal not found:', modalId);
        // List all available modals for debugging
        const allModals = document.querySelectorAll('.modal');
    }
}


function clearModalDisplays() {
    // Clear add subcategory modal display
    const addCategoryDisplay = document.querySelector('#add-subcategory-modal .main-category-display');
    if (addCategoryDisplay) {
        addCategoryDisplay.className = 'main-category-display';
    }
    
    const addCategoryName = document.getElementById('subcategory-main-category-name');
    const addCategoryIcon = document.getElementById('subcategory-category-icon');
    if (addCategoryName) addCategoryName.textContent = '';
    if (addCategoryIcon) addCategoryIcon.innerHTML = '';
    
    // Clear edit subcategory modal display
    const editCategoryDisplay = document.querySelector('#edit-subcategory-modal .main-category-display');
    if (editCategoryDisplay) {
        editCategoryDisplay.className = 'main-category-display';
    }
    
    const editCategoryName = document.getElementById('edit-subcategory-main-category-name');
    const editCategoryIcon = document.getElementById('edit-subcategory-category-icon');
    if (editCategoryName) editCategoryName.textContent = '';
    if (editCategoryIcon) editCategoryIcon.innerHTML = '';
}


function closeModal(modalId) {
    const modal = document.getElementById(modalId);
    if (modal) {
        modal.classList.add('hidden');
        document.body.style.overflow = 'auto';
        
        // Clear modal displays when closing
        setTimeout(() => {
            clearModalDisplays();
        }, 300);
    }
}

async function loadCategories() {
    try {
        console.log('Loading categories from API...');
        console.log('API URL:', `${API_BASE_URL}?action=getAll`);
        
        const response = await fetch(`${API_BASE_URL}?action=getAll`);
        
        console.log('Response status:', response.status);
        console.log('Response ok:', response.ok);
        
        if (!response.ok) {
            const errorText = await response.text();
            throw new Error(`HTTP error! status: ${response.status}, response: ${errorText}`);
        }
        
        const result = await response.json();
        console.log('API Response:', result);
        
        if (result.success) {
            subcategories = result.subcategories || [];
            console.log('Loaded subcategories from API:', subcategories.length);
            renderCategories();
        } else {
            console.error('API Error:', result.message);
            showAlert('Error', 'Failed to load categories: ' + result.message);
        }
    } catch (error) {
        console.error('Error loading categories:', error);
        showAlert('Error', 'Failed to load categories. Please check console for details.');
    }
}

function renderCategories() {
    console.log('Rendering categories...');
    
    const sportsSubcategories = subcategories.filter(sub => sub.main_category_id == 1);
    const entertainmentSubcategories = subcategories.filter(sub => sub.main_category_id == 2);
    
    console.log('Sports subcategories:', sportsSubcategories.length);
    console.log('Entertainment subcategories:', entertainmentSubcategories.length);
    
    renderCategoryTable('sports-categories-body', sportsSubcategories);
    renderCategoryTable('entertainment-categories-body', entertainmentSubcategories);
    
    // Replace feather icons
    if (typeof feather !== 'undefined') {
        feather.replace();
    }
}

function renderCategoryTable(tableId, subcategories) {
    const tableBody = document.getElementById(tableId);
    if (!tableBody) {
        console.error('Table body not found:', tableId);
        return;
    }
    
    if (subcategories.length === 0) {
        tableBody.innerHTML = `
            <tr>
                <td colspan="4" class="empty-state">
                    <i data-feather="folder"></i>
                    <p>No subcategories found</p>
                </td>
            </tr>
        `;
        // Replace feather icons
        if (typeof feather !== 'undefined') {
            setTimeout(() => feather.replace(), 100);
        }
        return;
    }
    
    tableBody.innerHTML = '';
    
    subcategories.forEach(subcategory => {
        const row = document.createElement('tr');
        row.innerHTML = `
            <td>
                ${subcategory.image_url ? 
                    (() => {
                        let imgUrl = escapeHtml(subcategory.image_url);
                        if (imgUrl && !imgUrl.startsWith('http') && !imgUrl.startsWith('/')) {
                            imgUrl = '/' + imgUrl;
                        }
                        return `<img src="${imgUrl}" alt="${escapeHtml(subcategory.name)}" style="width: 50px; height: 50px; object-fit: cover; border-radius: 8px;">`;
                    })() : 
                    '<div style="width: 50px; height: 50px; background: linear-gradient(135deg, #333, #444); border-radius: 8px; display: flex; align-items: center; justify-content: center;"><i data-feather="image" style="color: #666;"></i></div>'
                }
            </td>
            <td>${escapeHtml(subcategory.name)}</td>
            <td><span class="status-badge ${subcategory.status}">${subcategory.status.charAt(0).toUpperCase() + subcategory.status.slice(1)}</span></td>
            <td>
                <div class="action-buttons">
                    <button class="action-btn edit-subcategory" data-id="${subcategory.id}">
                        <i data-feather="edit-2"></i>
                    </button>
                    <button class="action-btn delete delete-subcategory" data-id="${subcategory.id}">
                        <i data-feather="trash-2"></i>
                    </button>
                </div>
            </td>
        `;
        tableBody.appendChild(row);
    });
    
    // Replace feather icons
    if (typeof feather !== 'undefined') {
        setTimeout(() => feather.replace(), 100);
    }
}

function escapeHtml(text) {
    const div = document.createElement('div');
    div.textContent = text;
    return div.innerHTML;
}

async function handleAddSubcategorySubmit(e) {
    console.log('Handling add subcategory submit...');
    
    const nameInput = document.getElementById('subcategory-name');
    const statusSelect = document.getElementById('subcategory-status');
    const mainCategoryIdInput = document.getElementById('subcategory-main-category-id');
    const imageInput = document.getElementById('subcategory-image');
    
    if (!nameInput || !statusSelect || !mainCategoryIdInput) {
        showAlert('Error', 'Form elements not found');
        return;
    }
    
    const name = nameInput.value.trim();
    const mainCategoryId = mainCategoryIdInput.value;
    const status = statusSelect.value;
    const imageFile = imageInput.files[0];
    
    console.log('Subcategory data:', { name, mainCategoryId, status, hasImage: !!imageFile });
    
    if (!name) {
        showAlert('Error', 'Please enter a subcategory name');
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
        let imageUrl = null;
        
        // Upload image if provided
        if (imageFile) {
            console.log('Uploading image...');
            const uploadResult = await uploadImage(imageFile, 'subcategories');
            
            if (!uploadResult.success) {
                showAlert('Error', 'Failed to upload image: ' + uploadResult.message);
                submitBtn.disabled = false;
                submitBtn.innerHTML = originalText;
                if (typeof feather !== 'undefined') feather.replace();
                return;
            }
            
            imageUrl = uploadResult.url;
            console.log('Image uploaded successfully:', imageUrl);
        }
        
        // Prepare data
        const subcategoryData = {
            main_category_id: parseInt(mainCategoryId),
            name: name,
            status: status
        };
        
        if (imageUrl) {
            subcategoryData.image_url = imageUrl;
        }
        
        console.log('Sending to:', `${API_BASE_URL}?action=create`);
        console.log('Data:', subcategoryData);
        
        const response = await fetch(`${API_BASE_URL}?action=create`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify(subcategoryData)
        });
        
        console.log('Response status:', response.status);
        console.log('Response ok:', response.ok);
        
        const text = await response.text();
        console.log('Raw response:', text);
        
        if (!response.ok) {
            throw new Error(`HTTP error! status: ${response.status}, response: ${text}`);
        }
        
        try {
            const result = JSON.parse(text);
            
            if (result.success) {
                // Reload categories from API
                await loadCategories();
                closeModal('add-subcategory-modal');
                showAlert('Success', 'Subcategory added successfully!');
                e.target.reset();
                
                // Clear image preview
                const imagePreview = document.getElementById('subcategory-image-preview');
                if (imagePreview) {
                    imagePreview.classList.add('hidden');
                    imagePreview.querySelector('img').src = '';
                }
            } else {
                showAlert('Error', result.message || 'Failed to add subcategory');
            }
        } catch (jsonError) {
            console.error('JSON Parse Error:', jsonError);
            console.error('Response was not JSON:', text);
            showAlert('Error', 'Server returned invalid JSON. Check API file.');
        }
    } catch (error) {
        console.error('Error adding subcategory:', error);
        showAlert('Error', 'Error adding subcategory: ' + error.message);
    } finally {
        submitBtn.disabled = false;
        submitBtn.innerHTML = originalText;
        if (typeof feather !== 'undefined') {
            feather.replace();
        }
    }
}

async function editSubcategory(subcategoryId) {
    try {
        console.log('Loading subcategory:', subcategoryId);
        const response = await fetch(`${API_BASE_URL}?action=getOne&id=${subcategoryId}`);
        
        console.log('Response status:', response.status);
        
        const text = await response.text();
        console.log('Raw response:', text);
        
        if (!response.ok) {
            throw new Error(`HTTP error! status: ${response.status}, response: ${text}`);
        }
        
        const result = JSON.parse(text);
        
        if (result.success && result.data) {
            const subcategory = result.data;
            
            // Set form values
            document.getElementById('edit-subcategory-id').value = subcategory.id;
            document.getElementById('edit-subcategory-name').value = subcategory.name;
            document.getElementById('edit-subcategory-status').value = subcategory.status;
            document.getElementById('edit-subcategory-existing-image').value = subcategory.image_url || '';
            
            // Set main category display (uneditable)
            const mainCategoryId = subcategory.main_category_id;
            const categoryNameSpan = document.getElementById('edit-subcategory-main-category-name');
            const categoryIconSpan = document.getElementById('edit-subcategory-category-icon');
            
            if (mainCategoryId == 1) { // Sports
                document.getElementById('edit-subcategory-main-category-id').value = '1';
                if (categoryNameSpan) categoryNameSpan.textContent = 'Sports';
                if (categoryIconSpan) categoryIconSpan.innerHTML = '<i data-feather="activity"></i>';
                
                // Add sports class
                const categoryDisplay = categoryNameSpan.closest('.main-category-display');
                if (categoryDisplay) {
                    categoryDisplay.className = 'main-category-display sports';
                }
            } else if (mainCategoryId == 2) { // Entertainment
                document.getElementById('edit-subcategory-main-category-id').value = '2';
                if (categoryNameSpan) categoryNameSpan.textContent = 'Entertainment';
                if (categoryIconSpan) categoryIconSpan.innerHTML = '<i data-feather="music"></i>';
                
                // Add entertainment class
                const categoryDisplay = categoryNameSpan.closest('.main-category-display');
                if (categoryDisplay) {
                    categoryDisplay.className = 'main-category-display entertainment';
                }
            }
            
            // Show current image if exists
            const currentImage = document.getElementById('edit-subcategory-current-image');
            if (subcategory.image_url) {
                let imgUrl = subcategory.image_url;
                if (imgUrl && !imgUrl.startsWith('http') && !imgUrl.startsWith('/')) {
                    imgUrl = '/' + imgUrl;
                }
                currentImage.src = imgUrl;
                currentImage.style.display = 'block';
            } else {
                currentImage.style.display = 'none';
            }
            
            // Clear file input
            const imageInput = document.getElementById('edit-subcategory-image');
            if (imageInput) {
                imageInput.value = '';
            }
            
            // Initialize feather icons
            if (typeof feather !== 'undefined') {
                setTimeout(() => feather.replace(), 100);
            }
            
            openModal('edit-subcategory-modal');
        } else {
            showAlert('Error', result.message || 'Failed to load subcategory');
        }
    } catch (error) {
        console.error('Error loading subcategory:', error);
        showAlert('Error', 'Error loading subcategory details: ' + error.message);
    }
}

async function handleEditSubcategorySubmit(e) {
    e.preventDefault();
    console.log('Handling edit subcategory submit...');
    
    const id = document.getElementById('edit-subcategory-id').value;
    const name = document.getElementById('edit-subcategory-name').value.trim();
    const mainCategoryId = document.getElementById('edit-subcategory-main-category-id').value;
    const status = document.getElementById('edit-subcategory-status').value;
    const existingImage = document.getElementById('edit-subcategory-existing-image').value;
    const imageInput = document.getElementById('edit-subcategory-image');
    const imageFile = imageInput.files[0];
    
    console.log('Editing subcategory ID:', id, 'Data:', { name, mainCategoryId, status, existingImage, hasImage: !!imageFile });
    
    if (!name || !id) {
        showAlert('Error', 'Please fill all required fields');
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
        let imageUrl = existingImage || null;
        
        // Upload new image if provided
        if (imageFile) {
            console.log('Uploading new image...');
            const uploadResult = await uploadImage(imageFile, 'subcategories');
            
            if (!uploadResult.success) {
                showAlert('Error', 'Failed to upload image: ' + uploadResult.message);
                submitBtn.disabled = false;
                submitBtn.innerHTML = originalText;
                if (typeof feather !== 'undefined') feather.replace();
                return;
            }
            
            imageUrl = uploadResult.url;
            console.log('New image uploaded successfully:', imageUrl);
        }
        
        // Prepare data
        const subcategoryData = {
            main_category_id: parseInt(mainCategoryId),
            name: name,
            status: status
        };
        
        if (imageUrl !== undefined) {
            subcategoryData.image_url = imageUrl;
        }
        
        console.log('Sending update to:', `${API_BASE_URL}?action=update&id=${id}`);
        console.log('Data:', subcategoryData);
        
        const response = await fetch(`${API_BASE_URL}?action=update&id=${id}`, {
            method: 'PUT',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify(subcategoryData)
        });
        
        console.log('Response status:', response.status);
        
        const text = await response.text();
        console.log('Raw response:', text);
        
        if (!response.ok) {
            throw new Error(`HTTP error! status: ${response.status}, response: ${text}`);
        }
        
        const result = JSON.parse(text);
        
        if (result.success) {
            // Reload categories from API
            await loadCategories();
            closeModal('edit-subcategory-modal');
            showAlert('Success', 'Subcategory updated successfully!');
        } else {
            showAlert('Error', result.message || 'Failed to update subcategory');
        }
    } catch (error) {
        console.error('Error updating subcategory:', error);
        showAlert('Error', 'Error updating subcategory: ' + error.message);
    } finally {
        submitBtn.disabled = false;
        submitBtn.innerHTML = originalText;
        if (typeof feather !== 'undefined') {
            feather.replace();
        }
    }
}

async function deleteSubcategory(subcategoryId) {
    showConfirmation('Are you sure you want to delete this subcategory?', async () => {
        try {
            console.log('Deleting subcategory:', subcategoryId);
            const response = await fetch(`${API_BASE_URL}?action=delete&id=${subcategoryId}`, {
                method: 'DELETE'
            });
            
            console.log('Response status:', response.status);
            
            const text = await response.text();
            console.log('Raw response:', text);
            
            if (!response.ok) {
                throw new Error(`HTTP error! status: ${response.status}, response: ${text}`);
            }
            
            const result = JSON.parse(text);
            
            if (result.success) {
                // Reload categories from API
                await loadCategories();
                showAlert('Success', 'Subcategory deleted successfully!');
            } else {
                showAlert('Error', result.message || 'Failed to delete subcategory');
            }
        } catch (error) {
            console.error('Error deleting subcategory:', error);
            showAlert('Error', 'Error deleting subcategory: ' + error.message);
        }
    });
}

// Utility functions
function showAlert(title, message) {
    // Create alert modal if it doesn't exist
    let alertModal = document.getElementById('alert-modal');
    if (!alertModal) {
        alertModal = document.createElement('div');
        alertModal.id = 'alert-modal';
        alertModal.className = 'modal hidden';
        alertModal.innerHTML = `
            <div class="modal-content small">
                <div class="modal-header">
                    <h3>${title}</h3>
                    <button class="close-modal" data-modal="alert">
                        <i data-feather="x"></i>
                    </button>
                </div>
                <div class="confirmation-content" style="padding: 2rem;">
                    <p>${message}</p>
                </div>
                <div class="modal-actions">
                    <button type="button" class="primary-btn" data-modal="alert">OK</button>
                </div>
            </div>
        `;
        document.body.appendChild(alertModal);
        
        // Add event listener for OK button
        setTimeout(() => {
            const okBtn = alertModal.querySelector('.primary-btn[data-modal="alert"]');
            if (okBtn) {
                okBtn.addEventListener('click', () => closeModal('alert-modal'));
            }
        }, 100);
    } else {
        // Update existing modal
        alertModal.querySelector('h3').textContent = title;
        alertModal.querySelector('.confirmation-content p').textContent = message;
    }
    
    openModal('alert-modal');
}

function showConfirmation(message, callback) {
    // Check if confirmation modal exists
    const modal = document.getElementById('confirmation-modal');
    const messageEl = document.getElementById('confirmation-message');
    const confirmBtn = document.getElementById('confirm-action-btn');
    
    if (modal && messageEl && confirmBtn) {
        messageEl.textContent = message;
        
        // Remove previous listeners
        const newConfirmBtn = confirmBtn.cloneNode(true);
        confirmBtn.parentNode.replaceChild(newConfirmBtn, confirmBtn);
        
        // Add new listener
        newConfirmBtn.addEventListener('click', function() {
            callback();
            closeModal('confirmation-modal');
        });
        
        openModal('confirmation-modal');
    } else {
        // Fallback to browser confirm
        if (confirm(message)) {
            callback();
        }
    }
}

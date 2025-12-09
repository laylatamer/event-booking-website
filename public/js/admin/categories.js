// /public/js/admin/categories.js - COMPLETE VERSION
const API_BASE_URL = '/event-booking-website/public/api/categories_API.php';

let subcategories = [];
let isInitialized = false;

document.addEventListener('DOMContentLoaded', function() {
    console.log('DOM loaded, checking for categories section...');
    console.log('API Base URL:', API_BASE_URL);
    
    const categoriesSection = document.getElementById('categories-section');
    if (categoriesSection) {
        console.log('Categories section found, initializing...');
        initializeCategories();
    } else {
        console.log('Categories section not found on this page');
    }
});

function initializeCategories() {
    if (isInitialized) return;
    
    console.log('Initializing categories...');
    
    // Setup modal handling
    setupModalHandling();
    
    // Setup event listeners
    setupEventListeners();
    
    // Load data from API
    loadCategories();
    
    isInitialized = true;
}

function setupModalHandling() {
    console.log('Setting up modal handling...');
    
    // Close modal when clicking X or cancel button
    document.addEventListener('click', function(e) {
        // Handle close buttons
        if (e.target.closest('.close-modal')) {
            const btn = e.target.closest('.close-modal');
            const modalName = btn.getAttribute('data-modal');
            if (modalName) {
                console.log('Closing modal:', modalName);
                closeModal(`${modalName}-modal`);
            }
        }
        
        // Handle cancel buttons
        if (e.target.closest('.secondary-btn[data-modal]')) {
            const btn = e.target.closest('.secondary-btn[data-modal]');
            const modalName = btn.getAttribute('data-modal');
            if (modalName) {
                console.log('Closing modal via cancel:', modalName);
                closeModal(`${modalName}-modal`);
            }
        }
        
        // Close modal when clicking outside
        if (e.target.classList.contains('modal')) {
            console.log('Closing modal by clicking outside');
            e.target.classList.add('hidden');
        }
    });
}

function setupEventListeners() {
    console.log('Setting up event listeners...');
    
    // Use event delegation for dynamic content
    document.addEventListener('click', function(e) {
        // Add subcategory buttons
        if (e.target.closest('.add-subcategory-btn')) {
            e.preventDefault();
            const btn = e.target.closest('.add-subcategory-btn');
            console.log('Add subcategory button clicked');
            handleAddSubcategoryClick(btn);
        }
        
        // Edit subcategory buttons
        if (e.target.closest('.edit-subcategory')) {
            e.preventDefault();
            const btn = e.target.closest('.edit-subcategory');
            const subcategoryId = parseInt(btn.getAttribute('data-id'));
            console.log('Edit subcategory clicked:', subcategoryId);
            editSubcategory(subcategoryId);
        }
        
        // Delete subcategory buttons
        if (e.target.closest('.delete-subcategory')) {
            e.preventDefault();
            const btn = e.target.closest('.delete-subcategory');
            const subcategoryId = parseInt(btn.getAttribute('data-id'));
            console.log('Delete subcategory clicked:', subcategoryId);
            deleteSubcategory(subcategoryId);
        }
    });
    
    // Add subcategory form submission
    const addSubcategoryForm = document.getElementById('add-subcategory-form');
    if (addSubcategoryForm) {
        addSubcategoryForm.addEventListener('submit', function(e) {
            e.preventDefault();
            console.log('Add subcategory form submitted');
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
            console.log('Edit subcategory form submitted');
            handleEditSubcategorySubmit(e);
        });
    } else {
        console.warn('Edit subcategory form not found');
    }
}

function handleAddSubcategoryClick(btn) {
    const mainCategoryId = btn.getAttribute('data-main-category');
    const mainCategoryName = btn.closest('.category-section').querySelector('h3').textContent
        .replace(/[^a-zA-Z\s]/g, '').trim();
    
    console.log('Main Category ID:', mainCategoryId, 'Name:', mainCategoryName);
    
    const mainCategoryIdInput = document.getElementById('subcategory-main-category-id');
    const mainCategoryNameInput = document.getElementById('subcategory-main-category-name');
    
    if (mainCategoryIdInput && mainCategoryNameInput) {
        mainCategoryIdInput.value = mainCategoryId;
        mainCategoryNameInput.value = mainCategoryName;
        
        // Reset form
        const form = document.getElementById('add-subcategory-form');
        if (form) {
            form.reset();
            // Set default status
            const statusSelect = document.getElementById('subcategory-status');
            if (statusSelect) {
                statusSelect.value = 'active';
            }
        }
        
        openModal('add-subcategory-modal');
    } else {
        console.error('Modal inputs not found!');
        console.log('mainCategoryIdInput:', mainCategoryIdInput);
        console.log('mainCategoryNameInput:', mainCategoryNameInput);
    }
}

// Modal functions
function openModal(modalId) {
    console.log('Attempting to open modal:', modalId);
    const modal = document.getElementById(modalId);
    if (modal) {
        console.log('Modal found, removing hidden class');
        modal.classList.remove('hidden');
        // Replace feather icons
        if (typeof feather !== 'undefined') {
            feather.replace();
        }
    } else {
        console.error('Modal not found:', modalId);
        // List all available modals for debugging
        const allModals = document.querySelectorAll('.modal');
        console.log('Available modals:', Array.from(allModals).map(m => m.id));
    }
}

function closeModal(modalId) {
    console.log('Closing modal:', modalId);
    const modal = document.getElementById(modalId);
    if (modal) {
        modal.classList.add('hidden');
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
                <td colspan="3" class="empty-state">
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
    
    if (!nameInput || !statusSelect || !mainCategoryIdInput) {
        showAlert('Error', 'Form elements not found');
        return;
    }
    
    const subcategoryData = {
        main_category_id: parseInt(mainCategoryIdInput.value),
        name: nameInput.value.trim(),
        status: statusSelect.value
    };
    
    console.log('Subcategory data to send:', subcategoryData);
    
    if (!subcategoryData.name) {
        showAlert('Error', 'Please enter a subcategory name');
        return;
    }
    
    try {
        console.log('Sending to:', `${API_BASE_URL}?action=create`);
        
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
            
            document.getElementById('edit-subcategory-id').value = subcategory.id;
            document.getElementById('edit-subcategory-main-category').value = subcategory.main_category_id;
            document.getElementById('edit-subcategory-name').value = subcategory.name;
            document.getElementById('edit-subcategory-status').value = subcategory.status;
            
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
    
    const subcategoryId = parseInt(document.getElementById('edit-subcategory-id').value);
    const subcategoryData = {
        main_category_id: parseInt(document.getElementById('edit-subcategory-main-category').value),
        name: document.getElementById('edit-subcategory-name').value.trim(),
        status: document.getElementById('edit-subcategory-status').value
    };
    
    console.log('Editing subcategory ID:', subcategoryId, 'Data:', subcategoryData);
    
    if (!subcategoryData.name) {
        showAlert('Error', 'Please enter a subcategory name');
        return;
    }
    
    try {
        console.log('Sending update to:', `${API_BASE_URL}?action=update&id=${subcategoryId}`);
        
        const response = await fetch(`${API_BASE_URL}?action=update&id=${subcategoryId}`, {
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
    alert(`${title}: ${message}`);
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

// Debug function to check modals
function debugModals() {
    console.log('=== DEBUG: Available Modals ===');
    const modals = document.querySelectorAll('.modal');
    modals.forEach(modal => {
        console.log(`Modal ID: ${modal.id}, Hidden: ${modal.classList.contains('hidden')}`);
    });
    console.log('===============================');
}

// Path testing function from old code
async function testApiConnection() {
    console.log('Testing API connection...');
    
    // Try different paths
    const testPaths = [
        'api/categories_API.php',
        './api/categories_API.php',
        '../api/categories_API.php',
        '/event-booking-website/public/api/categories_API.php'
    ];
    
    for (const path of testPaths) {
        try {
            console.log(`Trying path: ${path}`);
            const response = await fetch(`${path}?action=getAll`);
            console.log(`Path ${path}: Status ${response.status}, OK: ${response.ok}`);
            
            if (response.ok) {
                const text = await response.text();
                console.log(`Path ${path}: Response (first 200 chars):`, text.substring(0, 200));
                return path;
            }
        } catch (error) {
            console.log(`Path ${path}: Error:`, error.message);
        }
    }
    
    console.error('No API path worked!');
    return null;
}

// Make functions available globally if needed
window.debugModals = debugModals;
window.testApiConnection = testApiConnection;

// Optional: Expose subcategories for debugging
window.subcategories = subcategories;
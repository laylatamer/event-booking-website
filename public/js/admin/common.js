// Common Admin Panel Scripts

// Global variables
let currentUser = {
    id: 1,
    name: 'Admin',
    email: 'admin@egzly.com',
    role: 'Administrator',
    phone: '+1 (555) 123-4567',
    avatar: 'default-avatar.png'
};

let currentPage = {
    users: 1,
    bookings: 1,
    events: 1,
    categories: 1,
    locations: 1,
    tickets: 1,
    messages: 1
};

// Initialize the application
document.addEventListener('DOMContentLoaded', function() {
    // Initialize Feather icons
    feather.replace();
    
    // Close all modals on page load
    closeAllModals();
    
    // Set up common event listeners
    initializeCommonEventListeners();
});

// Function to close all modals
function closeAllModals() {
    const modals = document.querySelectorAll('.modal');
    modals.forEach(modal => {
        modal.classList.add('hidden');
    });
    document.body.style.overflow = 'auto';
}

// Initialize common event listeners
function initializeCommonEventListeners() {
    // User profile sidebar click
    const userProfileSidebar = document.querySelector('.user-profile-sidebar');
    if (userProfileSidebar) {
        userProfileSidebar.addEventListener('click', function() {
            openModal('user-profile');
            loadUserProfile();
        });
    }
    
    // Modal close buttons
    const closeButtons = document.querySelectorAll('.close-modal');
    closeButtons.forEach(button => {
        button.addEventListener('click', function() {
            const modalId = this.getAttribute('data-modal');
            closeModal(modalId);
        });
    });
    
    // Close modal when clicking outside
    const modals = document.querySelectorAll('.modal');
    modals.forEach(modal => {
        modal.addEventListener('click', function(e) {
            if (e.target === this) {
                const modalId = this.id.replace('-modal', '');
                closeModal(modalId);
            }
        });
    });
    
    // Cancel buttons in modals
    const cancelButtons = document.querySelectorAll('[data-modal]');
    cancelButtons.forEach(button => {
        if (button.classList.contains('secondary-btn') || button.classList.contains('close-modal')) {
            button.addEventListener('click', function() {
                const modalId = this.getAttribute('data-modal');
                closeModal(modalId);
            });
        }
    });
    
    // Logout button
    const logoutBtn = document.getElementById('logout-btn');
    if (logoutBtn) {
        logoutBtn.addEventListener('click', handleLogout);
    }
    
    // Change avatar button
    const changeAvatarBtn = document.getElementById('change-avatar-btn');
    if (changeAvatarBtn) {
        changeAvatarBtn.addEventListener('click', () => {
            document.getElementById('avatar-upload').click();
        });
    }
    
    // Avatar upload
    const avatarUpload = document.getElementById('avatar-upload');
    if (avatarUpload) {
        avatarUpload.addEventListener('change', handleAvatarUpload);
    }
    
    // User profile form
    const userProfileForm = document.getElementById('user-profile-form');
    if (userProfileForm) {
        userProfileForm.addEventListener('submit', handleUpdateProfile);
    }
}

// Open a modal
function openModal(modalId) {
    const modal = document.getElementById(`${modalId}-modal`);
    if (modal) {
        modal.classList.remove('hidden');
        document.body.style.overflow = 'hidden';
    }
}

// Close a modal
function closeModal(modalId) {
    const modal = document.getElementById(`${modalId}-modal`);
    if (modal) {
        modal.classList.add('hidden');
        document.body.style.overflow = 'auto';
        
        // Reset form if it's an add form
        if (modalId.startsWith('add-')) {
            const form = document.getElementById(`${modalId}-form`);
            if (form) {
                form.reset();
            }
        }
    }
}

// Load user profile data
function loadUserProfile() {
    // Get all profile form fields
    const profileFirstName = document.getElementById('profile-first-name');
    const profileLastName = document.getElementById('profile-last-name');
    const profileEmail = document.getElementById('profile-email');
    const profilePhone = document.getElementById('profile-phone');
    const profileAddress = document.getElementById('profile-address');
    const profileCity = document.getElementById('profile-city');
    const profileState = document.getElementById('profile-state');
    const profileCountry = document.getElementById('profile-country');
    const profileRole = document.getElementById('profile-role');
    
    // Don't overwrite PHP-set values - they already contain database data
    // This function is mainly for fallback if values are missing
    
    // Update avatar preview if needed
    const avatarPreview = document.getElementById('profile-avatar-preview');
    if (avatarPreview && (!avatarPreview.src || avatarPreview.src.includes('default-avatar.png'))) {
        avatarPreview.src = currentUser.avatar;
    }
    
    // Update sidebar avatar
    const userAvatar = document.getElementById('user-avatar');
    if (userAvatar) {
        userAvatar.src = currentUser.avatar;
    }
}

// Handle update profile form submission
async function handleUpdateProfile(e) {
    e.preventDefault();
    
    const firstName = document.getElementById('profile-first-name')?.value || '';
    const lastName = document.getElementById('profile-last-name')?.value || '';
    const email = document.getElementById('profile-email')?.value || '';
    const phone = document.getElementById('profile-phone')?.value || '';
    const address = document.getElementById('profile-address')?.value || '';
    const city = document.getElementById('profile-city')?.value || '';
    const state = document.getElementById('profile-state')?.value || '';
    const country = document.getElementById('profile-country')?.value || '';
    
    // Get current user ID from the page (should be set in PHP)
    const userId = window.currentUserId || document.getElementById('profile-user-id')?.value;
    
    if (!userId) {
        alert('Error: User ID not found. Please refresh the page.');
        return;
    }
    
    // Disable submit button to prevent double submission
    const submitBtn = e.target.querySelector('button[type="submit"]');
    if (submitBtn) {
        submitBtn.disabled = true;
        submitBtn.textContent = 'Saving...';
    }
    
    try {
        const response = await fetch('/event-booking-website/public/api/users.php', {
            method: 'PUT',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify({
                id: parseInt(userId),
                first_name: firstName,
                last_name: lastName,
                email: email,
                phone_number: phone,
                address: address,
                city: city,
                state: state,
                country: country
            })
        });
        
        const result = await response.json();
        
        if (result.ok) {
            const fullName = `${firstName} ${lastName}`.trim() || firstName || lastName;
            
            // Update sidebar
            const username = document.querySelector('.username');
            const userEmail = document.querySelector('.user-email');
            if (username) username.textContent = fullName;
            if (userEmail) userEmail.textContent = email;
            
            closeModal('user-profile');
            
            // Show success message
            alert('Profile updated successfully!');
            
            // Reload page to show updated data
            window.location.reload();
        } else {
            alert('Error: ' + (result.message || 'Failed to update profile. Please try again.'));
            if (submitBtn) {
                submitBtn.disabled = false;
                submitBtn.textContent = 'Save Changes';
            }
        }
    } catch (error) {
        console.error('Error updating profile:', error);
        alert('Error: Failed to update profile. Please try again.');
        if (submitBtn) {
            submitBtn.disabled = false;
            submitBtn.textContent = 'Save Changes';
        }
    }
}

// Handle avatar upload
function handleAvatarUpload(e) {
    const file = e.target.files[0];
    if (file) {
        const reader = new FileReader();
        reader.onload = function(event) {
            currentUser.avatar = event.target.result;
            document.getElementById('profile-avatar-preview').src = event.target.result;
            const userAvatar = document.getElementById('user-avatar');
            if (userAvatar) {
                userAvatar.src = event.target.result;
            }
        };
        reader.readAsDataURL(file);
    }
}

// Handle logout
function handleLogout() {
    showConfirmation('Are you sure you want to log out?', () => {
        // In a real app, this would redirect to the login page
        alert('You have been logged out. Redirecting to login page...');
        // window.location.href = 'login.html';
    });
}

// Show confirmation modal
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

// Update pagination buttons state
function updatePaginationButtons(section, totalItems, itemsPerPage = 4) {
    const totalPages = Math.ceil(totalItems / itemsPerPage);
    const prevBtn = document.getElementById(`${section}-prev`);
    const nextBtn = document.getElementById(`${section}-next`);
    
    if (prevBtn) {
        prevBtn.disabled = currentPage[section] === 1;
        if (prevBtn.disabled) {
            prevBtn.style.opacity = '0.5';
            prevBtn.style.cursor = 'not-allowed';
        } else {
            prevBtn.style.opacity = '1';
            prevBtn.style.cursor = 'pointer';
        }
    }
    
    if (nextBtn) {
        nextBtn.disabled = currentPage[section] >= totalPages;
        if (nextBtn.disabled) {
            nextBtn.style.opacity = '0.5';
            nextBtn.style.cursor = 'not-allowed';
        } else {
            nextBtn.style.opacity = '1';
            nextBtn.style.cursor = 'pointer';
        }
    }
}


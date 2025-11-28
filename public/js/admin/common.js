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
    tickets: 1
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
    if (document.getElementById('profile-name')) {
        document.getElementById('profile-name').value = currentUser.name;
        document.getElementById('profile-email').value = currentUser.email;
        document.getElementById('profile-role').value = currentUser.role;
        document.getElementById('profile-phone').value = currentUser.phone;
        document.getElementById('profile-avatar-preview').src = currentUser.avatar;
        
        // Update sidebar avatar
        const userAvatar = document.getElementById('user-avatar');
        if (userAvatar) {
            userAvatar.src = currentUser.avatar;
        }
    }
}

// Handle update profile form submission
function handleUpdateProfile(e) {
    e.preventDefault();
    
    const name = document.getElementById('profile-name').value;
    const email = document.getElementById('profile-email').value;
    const phone = document.getElementById('profile-phone').value;
    
    currentUser.name = name;
    currentUser.email = email;
    currentUser.phone = phone;
    
    // Update sidebar
    const username = document.querySelector('.username');
    const userEmail = document.querySelector('.user-email');
    if (username) username.textContent = name;
    if (userEmail) userEmail.textContent = email;
    
    closeModal('user-profile');
    
    // Show success message (in a real app)
    alert('Profile updated successfully!');
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


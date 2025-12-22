// Users Section Scripts
(function() {
'use strict';

let users = [];
let filteredUsers = [];
const itemsPerPage = 4;
const apiUrl = '/api/users.php';

if (typeof currentPage !== 'undefined' && !currentPage.users) {
    currentPage.users = 1;
}

// Initialize users section
document.addEventListener('DOMContentLoaded', function() {
    initializeUsersEventListeners();
    loadUsers();
});

// Initialize event listeners for users section
function initializeUsersEventListeners() {
    // Add user button
    const addUserBtn = document.getElementById('add-user-btn');
    if (addUserBtn) {
        addUserBtn.addEventListener('click', () => openModal('add-user'));
    }
    
    // Form submissions
    const addUserForm = document.getElementById('add-user-form');
    if (addUserForm) {
        addUserForm.addEventListener('submit', handleAddUser);
    }
    
    const editUserForm = document.getElementById('edit-user-form');
    if (editUserForm) {
        editUserForm.addEventListener('submit', handleEditUser);
    }
    
    // Search functionality
    const userSearch = document.getElementById('user-search');
    if (userSearch) {
        userSearch.addEventListener('input', filterUsers);
    }
    
    // Filter functionality
    const userRoleFilter = document.getElementById('user-role-filter');
    if (userRoleFilter) {
        userRoleFilter.addEventListener('change', filterUsers);
    }
    
    // Pagination
    const usersPrev = document.getElementById('users-prev');
    const usersNext = document.getElementById('users-next');
    if (usersPrev) usersPrev.addEventListener('click', () => changePage('users', -1));
    if (usersNext) usersNext.addEventListener('click', () => changePage('users', 1));
}

// Load users from API
async function loadUsers() {
    const usersTableBody = document.getElementById('users-table-body');
    if (!usersTableBody) return;
    
    try {
        const response = await fetch(apiUrl);
        
        if (!response.ok) {
            throw new Error('Failed to load users');
        }
        
        const data = await response.json();
        
        if (Array.isArray(data)) {
            users = data.map(user => ({
                id: user.id,
                name: `${user.first_name || ''} ${user.last_name || ''}`.trim() || 'Unknown',
                email: user.email || '',
                role: (user.is_admin === 1 || user.is_admin === '1' || user.is_admin === true) ? 'admin' : 'user',
                joined: user.created_at ? new Date(user.created_at).toLocaleDateString('en-US', { year: 'numeric', month: 'short', day: 'numeric' }) : 'N/A',
                lastLogin: user.last_login ? new Date(user.last_login).toLocaleDateString('en-US', { year: 'numeric', month: 'short', day: 'numeric', hour: '2-digit', minute: '2-digit' }) : 'Never',
                status: user.status || 'active',
                avatar: user.profile_image_path || null
            }));
        } else {
            users = [];
        }
        
        filteredUsers = [...users];
        displayUsers();
    } catch (error) {
        if (usersTableBody) {
            usersTableBody.innerHTML = `
                <tr>
                    <td colspan="7" class="empty-state">
                        <i data-feather="alert-circle"></i>
                        <p>Error loading users. Please try again later.</p>
                    </td>
                </tr>
            `;
            feather.replace();
        }
    }
}

// Display users in the table
function displayUsers() {
    const usersTableBody = document.getElementById('users-table-body');
    if (!usersTableBody) return;
    
    usersTableBody.innerHTML = '';
    
        if (filteredUsers.length === 0) {
            usersTableBody.innerHTML = `
                <tr>
                    <td colspan="7" class="empty-state">
                        <i data-feather="users"></i>
                        <p>No users found</p>
                    </td>
                </tr>
            `;
            feather.replace();
            updatePaginationInfo(0);
            return;
        }

    const startIndex = (currentPage.users - 1) * itemsPerPage;
    const endIndex = startIndex + itemsPerPage;
    const usersToShow = filteredUsers.slice(startIndex, endIndex);
    
    usersToShow.forEach(user => {
        const row = document.createElement('tr');
        // Default to image proxy with empty path (returns default avatar)
        let avatarSrc = '/event-booking-website/public/image.php?path=';
        
        if (user.avatar && user.avatar !== 'default-avatar.png' && user.avatar !== '' && user.avatar !== null) {
            const avatarPath = String(user.avatar).trim();
            if (avatarPath.startsWith('http://') || avatarPath.startsWith('https://')) {
                avatarSrc = avatarPath;
            } else if (avatarPath.startsWith('/')) {
                avatarSrc = avatarPath;
            } else {
                // Database stores: uploads/profile_pics/prof_68f76afade590.jpg
                // Clean the path - remove Windows paths, relative paths, normalize slashes
                let cleanPath = avatarPath.replace(/^[A-Z]:[\\\/].*?event-booking-website[\\\/]/i, '');
                cleanPath = cleanPath.replace(/^(\.\.\/)+/, '');
                cleanPath = cleanPath.replace(/\\/g, '/');
                cleanPath = cleanPath.replace(/^\/+/, '');
                // Use image proxy which handles missing files gracefully
                avatarSrc = `/event-booking-website/public/image.php?path=${encodeURIComponent(cleanPath)}`;
            }
        }
        
        row.innerHTML = `
            <td>
                <div class="checkbox-header">
                    <input type="checkbox" class="checkbox-input user-checkbox" data-id="${user.id}">
                    <div class="user-info">
                        <div class="avatar small">
                            <img src="${avatarSrc}" alt="${escapeHtml(user.name)}" onerror="this.src='/event-booking-website/default-avatar.png'; this.onerror=null;">
                        </div>
                        <span>${escapeHtml(user.name)}</span>
                    </div>
                </div>
            </td>
            <td>${escapeHtml(user.email)}</td>
            <td><span class="role-badge ${user.role === 'admin' ? 'admin' : 'user'}">${user.role.charAt(0).toUpperCase() + user.role.slice(1)}</span></td>
            <td>${escapeHtml(user.joined)}</td>
            <td>${escapeHtml(user.lastLogin)}</td>
            <td><span class="status-badge ${user.status}">${user.status.charAt(0).toUpperCase() + user.status.slice(1)}</span></td>
            <td>
                <div class="action-buttons">
                    <button class="action-btn edit-user" data-id="${user.id}">
                        <i data-feather="edit-2"></i>
                    </button>
                    <button class="action-btn delete delete-user" data-id="${user.id}">
                        <i data-feather="trash-2"></i>
                    </button>
                </div>
            </td>
        `;
        usersTableBody.appendChild(row);
    });
    
    updatePaginationInfo(filteredUsers.length);
    updatePaginationButtons('users', filteredUsers.length, itemsPerPage);
    
    feather.replace();
    
    document.querySelectorAll('.edit-user').forEach(button => {
        button.addEventListener('click', function() {
            const userId = parseInt(this.getAttribute('data-id'));
            editUser(userId);
        });
    });
    
    document.querySelectorAll('.delete-user').forEach(button => {
        button.addEventListener('click', function() {
            const userId = parseInt(this.getAttribute('data-id'));
            deleteUser(userId);
        });
    });
}

// Update pagination info
function updatePaginationInfo(totalItems) {
    const startIndex = (currentPage.users - 1) * itemsPerPage;
    const endIndex = Math.min(startIndex + itemsPerPage, totalItems);
    
    document.getElementById('users-start').textContent = totalItems > 0 ? startIndex + 1 : 0;
    document.getElementById('users-end').textContent = endIndex;
    document.getElementById('users-total').textContent = totalItems;
}

// Escape HTML to prevent XSS
function escapeHtml(text) {
    const div = document.createElement('div');
    div.textContent = text;
    return div.innerHTML;
}

// Filter users based on search and role filter
function filterUsers() {
    const searchTerm = document.getElementById('user-search').value.toLowerCase();
    const roleFilter = document.getElementById('user-role-filter').value;
    
    filteredUsers = users.filter(user => {
        const matchesSearch = user.name.toLowerCase().includes(searchTerm) || 
                             user.email.toLowerCase().includes(searchTerm);
        const matchesRole = !roleFilter || roleFilter === 'Filter by role' || user.role === roleFilter.toLowerCase();
        
        return matchesSearch && matchesRole;
    });
    
    currentPage.users = 1;
    displayUsers();
}

// Handle add user form submission
async function handleAddUser(e) {
    e.preventDefault();
    
    const firstName = document.getElementById('user-first-name').value.trim();
    const lastName = document.getElementById('user-last-name').value.trim();
    const email = document.getElementById('user-email').value.trim();
    const phone = document.getElementById('user-phone').value.trim();
    const password = document.getElementById('user-password').value;
    const address = document.getElementById('user-address').value.trim();
    const city = document.getElementById('user-city').value.trim();
    
    // Validation
    if (!firstName || !lastName || !email || !password) {
        alert('Please fill in all required fields');
        return;
    }
    
    if (!email.includes('@')) {
        alert('Please enter a valid email address');
        return;
    }
    
    if (password.length < 8) {
        alert('Password must be at least 8 characters long');
        return;
    }
    
    if (!/[A-Z]/.test(password) || !/[\W_]/.test(password)) {
        alert('Password must contain at least one uppercase letter and one symbol');
        return;
    }
    
    try {
        const formData = new URLSearchParams();
        formData.append('first_name', firstName);
        formData.append('last_name', lastName);
        formData.append('email', email);
        formData.append('password', password);
        if (phone) formData.append('phone_number', phone);
        if (address) formData.append('address', address);
        if (city) formData.append('city', city);
        
        const response = await fetch(apiUrl, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
            },
            body: formData.toString()
        });
        
        if (response.ok) {
            const result = await response.json();
            if (result.ok) {
                document.getElementById('add-user-form').reset();
                await loadUsers();
                closeModal('add-user');
                alert('User added successfully');
            } else {
                alert(result.message || 'Failed to add user');
            }
        } else {
            const error = await response.json();
            alert(error.message || 'Failed to add user');
        }
    } catch (error) {
        alert('Error adding user');
    }
}

// Edit user
function editUser(userId) {
    const user = users.find(u => u.id === userId);
    if (!user) return;
    
    document.getElementById('edit-user-id').value = user.id;
    document.getElementById('edit-user-name').value = user.name;
    document.getElementById('edit-user-email').value = user.email;
    document.getElementById('edit-user-role').value = user.role;
    document.getElementById('edit-user-status').value = user.status;
    
    openModal('edit-user');
}

// Handle edit user form submission
function handleEditUser(e) {
    e.preventDefault();
    alert('Edit user functionality not implemented yet');
    closeModal('edit-user');
}

// Delete user with confirmation
function deleteUser(userId) {
    showConfirmation('Are you sure you want to delete this user?', async () => {
        try {
            const formData = new URLSearchParams();
            formData.append('id', userId);
            
            const response = await fetch(apiUrl, {
                method: 'DELETE',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: formData.toString()
            });
            
            if (response.ok) {
                const result = await response.json();
                if (result.ok) {
                    await loadUsers();
                    alert('User deleted successfully');
                } else {
                    alert('Failed to delete user');
                }
            } else {
                throw new Error('Failed to delete user');
            }
        } catch (error) {
            alert('Error deleting user');
        }
    });
}

// Change page for pagination
function changePage(section, direction) {
    const totalItems = filteredUsers.length;
    const totalPages = Math.ceil(totalItems / itemsPerPage);
    
    currentPage[section] += direction;
    
    if (currentPage[section] < 1) {
        currentPage[section] = 1;
    } else if (currentPage[section] > totalPages) {
        currentPage[section] = totalPages;
    }
    
    displayUsers();
}

})(); // End of IIFE


// Users Section Scripts

// Data storage (in a real app, this would be handled by a backend)
let users = [
    { id: 1, name: 'John Doe', email: 'john.doe@example.com', role: 'admin', joined: 'May 12, 2022', status: 'active', avatar: 'default-avatar.png' },
    { id: 2, name: 'Sarah Smith', email: 'sarah.smith@example.com', role: 'user', joined: 'Jun 5, 2022', status: 'active', avatar: 'default-avatar.png' },
    { id: 3, name: 'Mike Johnson', email: 'mike.johnson@example.com', role: 'organizer', joined: 'Apr 28, 2022', status: 'pending', avatar: 'default-avatar.png' },
    { id: 4, name: 'Emily Wilson', email: 'emily.wilson@example.com', role: 'user', joined: 'Mar 15, 2022', status: 'inactive', avatar: 'default-avatar.png' }
];

let currentPage = { users: 1 };
const itemsPerPage = 4;

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

// Load users data
function loadUsers() {
    const usersTableBody = document.getElementById('users-table-body');
    if (!usersTableBody) return;
    
    usersTableBody.innerHTML = '';
    
    if (users.length === 0) {
        usersTableBody.innerHTML = `
            <tr>
                <td colspan="6" class="empty-state">
                    <i data-feather="users"></i>
                    <p>No users found</p>
                </td>
            </tr>
        `;
        feather.replace();
        return;
    }

    const startIndex = (currentPage.users - 1) * itemsPerPage;
    const endIndex = startIndex + itemsPerPage;
    const usersToShow = users.slice(startIndex, endIndex);
    
    usersToShow.forEach(user => {
        const row = document.createElement('tr');
        row.innerHTML = `
            <td>
                <div class="checkbox-header">
                    <input type="checkbox" class="checkbox-input user-checkbox" data-id="${user.id}">
                    <div class="user-info">
                        <div class="avatar small">
                            <img src="${user.avatar}" alt="${user.name}">
                        </div>
                        <span>${user.name}</span>
                    </div>
                </div>
            </td>
            <td>${user.email}</td>
            <td>${user.role}</td>
            <td>${user.joined}</td>
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
    
    // Update pagination info
    document.getElementById('users-start').textContent = startIndex + 1;
    document.getElementById('users-end').textContent = Math.min(endIndex, users.length);
    document.getElementById('users-total').textContent = users.length;
    
    // Update pagination buttons
    updatePaginationButtons('users', users.length, itemsPerPage);
    
    // Add event listeners to action buttons
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

// Filter users based on search and role filter
function filterUsers() {
    const searchTerm = document.getElementById('user-search').value.toLowerCase();
    const roleFilter = document.getElementById('user-role-filter').value;
    
    const filteredUsers = users.filter(user => {
        const matchesSearch = user.name.toLowerCase().includes(searchTerm) || 
                             user.email.toLowerCase().includes(searchTerm);
        const matchesRole = roleFilter === 'Filter by role' || user.role === roleFilter.toLowerCase();
        
        return matchesSearch && matchesRole;
    });
    
    // Update the table with filtered users
    const usersTableBody = document.getElementById('users-table-body');
    usersTableBody.innerHTML = '';
    
    const startIndex = (currentPage.users - 1) * itemsPerPage;
    const endIndex = startIndex + itemsPerPage;
    const usersToShow = filteredUsers.slice(startIndex, endIndex);
    
    usersToShow.forEach(user => {
        const row = document.createElement('tr');
        row.innerHTML = `
            <td>
                <div class="checkbox-header">
                    <input type="checkbox" class="checkbox-input user-checkbox" data-id="${user.id}">
                    <div class="user-info">
                        <div class="avatar small">
                            <img src="${user.avatar}" alt="${user.name}">
                        </div>
                        <span>${user.name}</span>
                    </div>
                </div>
            </td>
            <td>${user.email}</td>
            <td>${user.role}</td>
            <td>${user.joined}</td>
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
    
    // Update pagination info
    document.getElementById('users-start').textContent = filteredUsers.length > 0 ? startIndex + 1 : 0;
    document.getElementById('users-end').textContent = Math.min(endIndex, filteredUsers.length);
    document.getElementById('users-total').textContent = filteredUsers.length;
    
    // Update pagination buttons
    updatePaginationButtons('users', filteredUsers.length, itemsPerPage);
    
    // Add event listeners to action buttons
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

// Handle add user form submission
function handleAddUser(e) {
    e.preventDefault();
    
    const name = document.getElementById('user-name').value;
    const email = document.getElementById('user-email').value;
    const role = document.getElementById('user-role').value;
    const password = document.getElementById('user-password').value;
    
    const newUser = {
        id: users.length > 0 ? Math.max(...users.map(u => u.id)) + 1 : 1,
        name,
        email,
        role,
        joined: new Date().toLocaleDateString('en-US', { year: 'numeric', month: 'short', day: 'numeric' }),
        status: 'active',
        avatar: 'default-avatar.png'
    };
    
    users.push(newUser);
    loadUsers();
    closeModal('add-user');
    
    // Show success message (in a real app)
    alert('User added successfully!');
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
    
    const userId = parseInt(document.getElementById('edit-user-id').value);
    const name = document.getElementById('edit-user-name').value;
    const email = document.getElementById('edit-user-email').value;
    const role = document.getElementById('edit-user-role').value;
    const status = document.getElementById('edit-user-status').value;
    
    const userIndex = users.findIndex(u => u.id === userId);
    if (userIndex !== -1) {
        users[userIndex].name = name;
        users[userIndex].email = email;
        users[userIndex].role = role;
        users[userIndex].status = status;
        
        loadUsers();
        closeModal('edit-user');
        
        // Show success message (in a real app)
        alert('User updated successfully!');
    }
}

// Delete user with confirmation
function deleteUser(userId) {
    showConfirmation('Are you sure you want to delete this user?', () => {
        const userIndex = users.findIndex(u => u.id === userId);
        if (userIndex !== -1) {
            users.splice(userIndex, 1);
            loadUsers();
            
            // Show success message (in a real app)
            alert('User deleted successfully!');
        }
    });
}

// Change page for pagination
function changePage(section, direction) {
    const totalItems = users.length;
    const totalPages = Math.ceil(totalItems / itemsPerPage);
    
    currentPage[section] += direction;
    
    if (currentPage[section] < 1) {
        currentPage[section] = 1;
    } else if (currentPage[section] > totalPages) {
        currentPage[section] = totalPages;
    }
    
    loadUsers();
}


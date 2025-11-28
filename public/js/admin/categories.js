// Categories Section Scripts

let categories = [
    { id: 1, name: 'Music', description: 'Concerts, festivals, and music events', events: 24, status: 'active', icon: 'music', color: 'orange' },
    { id: 2, name: 'Technology', description: 'Tech conferences and workshops', events: 18, status: 'active', icon: 'cpu', color: 'black' },
    { id: 3, name: 'Art', description: 'Art exhibitions and galleries', events: 15, status: 'active', icon: 'palette', color: 'white' },
    { id: 4, name: 'Food & Drink', description: 'Food festivals and tasting events', events: 12, status: 'active', icon: 'coffee', color: 'orange' }
];

let currentPage = { categories: 1 };
const itemsPerPage = 4;

document.addEventListener('DOMContentLoaded', function() {
    initializeCategoriesEventListeners();
    loadCategories();
});

function initializeCategoriesEventListeners() {
    const addCategoryBtn = document.getElementById('add-category-btn');
    if (addCategoryBtn) {
        addCategoryBtn.addEventListener('click', () => openModal('add-category'));
    }
    
    const addCategoryForm = document.getElementById('add-category-form');
    if (addCategoryForm) {
        addCategoryForm.addEventListener('submit', handleAddCategory);
    }
    
    const editCategoryForm = document.getElementById('edit-category-form');
    if (editCategoryForm) {
        editCategoryForm.addEventListener('submit', handleEditCategory);
    }
    
    const categoriesPrev = document.getElementById('categories-prev');
    const categoriesNext = document.getElementById('categories-next');
    if (categoriesPrev) categoriesPrev.addEventListener('click', () => changePage('categories', -1));
    if (categoriesNext) categoriesNext.addEventListener('click', () => changePage('categories', 1));
}

function loadCategories() {
    const categoriesTableBody = document.getElementById('categories-table-body');
    if (!categoriesTableBody) return;
    
    categoriesTableBody.innerHTML = '';

    if (categories.length === 0) {
        categoriesTableBody.innerHTML = `
            <tr>
                <td colspan="5" class="empty-state">
                    <i data-feather="tag"></i>
                    <p>No categories found</p>
                </td>
            </tr>
        `;
        feather.replace();
        return;
    }
    
    const startIndex = (currentPage.categories - 1) * itemsPerPage;
    const endIndex = startIndex + itemsPerPage;
    const categoriesToShow = categories.slice(startIndex, endIndex);
    
    categoriesToShow.forEach(category => {
        const row = document.createElement('tr');
        row.innerHTML = `
            <td>
                <div class="category-info">
                    <div class="category-icon ${category.color}">
                        <i data-feather="${category.icon}"></i>
                    </div>
                    <span>${category.name}</span>
                </div>
            </td>
            <td>${category.description}</td>
            <td>${category.events}</td>
            <td><span class="status-badge ${category.status}">${category.status.charAt(0).toUpperCase() + category.status.slice(1)}</span></td>
            <td>
                <div class="action-buttons">
                    <button class="action-btn edit-category" data-id="${category.id}">
                        <i data-feather="edit-2"></i>
                    </button>
                    <button class="action-btn delete delete-category" data-id="${category.id}">
                        <i data-feather="trash-2"></i>
                    </button>
                </div>
            </td>
        `;
        categoriesTableBody.appendChild(row);
    });
    
    document.getElementById('categories-start').textContent = startIndex + 1;
    document.getElementById('categories-end').textContent = Math.min(endIndex, categories.length);
    document.getElementById('categories-total').textContent = categories.length;
    
    updatePaginationButtons('categories', categories.length, itemsPerPage);
    
    feather.replace();
    document.querySelectorAll('.edit-category').forEach(button => {
        button.addEventListener('click', function() {
            const categoryId = parseInt(this.getAttribute('data-id'));
            editCategory(categoryId);
        });
    });
    
    document.querySelectorAll('.delete-category').forEach(button => {
        button.addEventListener('click', function() {
            const categoryId = parseInt(this.getAttribute('data-id'));
            deleteCategory(categoryId);
        });
    });
}

function handleAddCategory(e) {
    e.preventDefault();
    
    const name = document.getElementById('category-name').value;
    const description = document.getElementById('category-description').value;
    const icon = document.getElementById('category-icon').value;
    const color = document.getElementById('category-color').value;
    
    const newCategory = {
        id: categories.length > 0 ? Math.max(...categories.map(c => c.id)) + 1 : 1,
        name,
        description,
        events: 0,
        status: 'active',
        icon,
        color
    };
    
    categories.push(newCategory);
    loadCategories();
    closeModal('add-category');
    alert('Category added successfully!');
}

function editCategory(categoryId) {
    const category = categories.find(c => c.id === categoryId);
    if (!category) return;
    
    document.getElementById('edit-category-id').value = category.id;
    document.getElementById('edit-category-name').value = category.name;
    document.getElementById('edit-category-description').value = category.description;
    document.getElementById('edit-category-icon').value = category.icon;
    document.getElementById('edit-category-color').value = category.color;
    
    openModal('edit-category');
}

function handleEditCategory(e) {
    e.preventDefault();
    
    const categoryId = parseInt(document.getElementById('edit-category-id').value);
    const name = document.getElementById('edit-category-name').value;
    const description = document.getElementById('edit-category-description').value;
    const icon = document.getElementById('edit-category-icon').value;
    const color = document.getElementById('edit-category-color').value;
    
    const categoryIndex = categories.findIndex(c => c.id === categoryId);
    if (categoryIndex !== -1) {
        categories[categoryIndex].name = name;
        categories[categoryIndex].description = description;
        categories[categoryIndex].icon = icon;
        categories[categoryIndex].color = color;
        
        loadCategories();
        closeModal('edit-category');
        alert('Category updated successfully!');
    }
}

function deleteCategory(categoryId) {
    showConfirmation('Are you sure you want to delete this category?', () => {
        const categoryIndex = categories.findIndex(c => c.id === categoryId);
        if (categoryIndex !== -1) {
            categories.splice(categoryIndex, 1);
            loadCategories();
            alert('Category deleted successfully!');
        }
    });
}

function changePage(section, direction) {
    const totalItems = categories.length;
    const totalPages = Math.ceil(totalItems / itemsPerPage);
    
    currentPage[section] += direction;
    
    if (currentPage[section] < 1) {
        currentPage[section] = 1;
    } else if (currentPage[section] > totalPages) {
        currentPage[section] = totalPages;
    }
    
    loadCategories();
}


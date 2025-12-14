<!-- Categories Section -->
<div id="categories-section" class="section-content">
    <div class="content-card">
        <!-- Sports Category Section -->
        <div class="category-section" id="sports-section">
            <div class="category-header">
                <h3><i data-feather="activity"></i> Sports Categories</h3>
                <button class="primary-btn add-subcategory-btn" data-main-category="1">
                    <i data-feather="plus"></i>
                    Add Sports Subcategory
                </button>
            </div>
            <div class="table-container">
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>Image</th> <!-- Added Image column -->
                            <th>Subcategory Name</th>
                            <th>Status</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody id="sports-categories-body">
                        <!-- Sports subcategories will be populated by JavaScript -->
                        <tr>
                            <td colspan="4" class="empty-state">
                                <i data-feather="folder"></i>
                                <p>No subcategories found</p>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Entertainment Category Section -->
        <div class="category-section" id="entertainment-section">
            <div class="category-header">
                <h3><i data-feather="film"></i> Entertainment Categories</h3>
                <button class="primary-btn add-subcategory-btn" data-main-category="2">
                    <i data-feather="plus"></i>
                    Add Entertainment Subcategory
                </button>
            </div>
            <div class="table-container">
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>Image</th> <!-- Added Image column -->
                            <th>Subcategory Name</th>
                            <th>Status</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody id="entertainment-categories-body">
                        <!-- Entertainment subcategories will be populated by JavaScript -->
                        <tr>
                            <td colspan="4" class="empty-state">
                                <i data-feather="folder"></i>
                                <p>No subcategories found</p>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<!-- After your main script -->
<script>
// This script will be overridden by categories.js, but kept for backward compatibility
document.addEventListener('DOMContentLoaded', function() {
    console.log('Categories section loaded');
    
    // Initialize feather icons
    if (typeof feather !== 'undefined') {
        feather.replace();
    }
});
</script>
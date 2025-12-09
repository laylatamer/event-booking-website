<!-- Users Section -->
<div id="users-section" class="section-content">
    <div class="content-card">
        <div class="table-controls">
            <div class="controls-left">
                <div class="search-container">
                    <input type="text" id="user-search" placeholder="Search users..." class="search-input">
                    <i data-feather="search" class="search-icon"></i>
                </div>
                <select id="user-role-filter" class="filter-select">
                    <option>Filter by role</option>
                    <option>Admin</option>
                    <option>User</option>
                    <option>Organizer</option>
                </select>
            </div>
            <div class="controls-right">
            </div>
        </div>

        <div class="table-container">
            <table class="data-table" id="users-table">
                <thead>
                    <tr>
                        <th>
                            <div class="checkbox-header">
                                <input type="checkbox" id="select-all-users" class="checkbox-input">
                                <span>User</span>
                            </div>
                        </th>
                        <th>Email</th>
                        <th>Role</th>
                        <th>Joined</th>
                        <th>Last Login</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody id="users-table-body">
                    <!-- Users will be populated by JavaScript -->
                </tbody>
            </table>
        </div>

        <div class="table-footer">
            <div class="table-info">
                Showing <span id="users-start">1</span> to <span id="users-end">4</span> of <span id="users-total">24</span> entries
            </div>
            <div class="pagination">
                <button class="pagination-btn" id="users-prev">Previous</button>
                <button class="pagination-btn active">1</button>
                <button class="pagination-btn">2</button>
                <button class="pagination-btn">3</button>
                <button class="pagination-btn" id="users-next">Next</button>
            </div>
        </div>
    </div>
</div>


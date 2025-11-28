<!-- Events Section -->
<div id="events-section" class="section-content">
    <div class="content-card">
        <div class="table-controls">
            <div class="controls-left">
                <div class="search-container">
                    <input type="text" id="event-search" placeholder="Search events..." class="search-input">
                    <i data-feather="search" class="search-icon"></i>
                </div>
                <select id="event-category-filter" class="filter-select">
                    <option>All Categories</option>
                    <option>Music</option>
                    <option>Technology</option>
                    <option>Art</option>
                    <option>Food & Drink</option>
                </select>
            </div>
            <div class="controls-right">
                <button class="icon-btn">
                    <i data-feather="filter"></i>
                </button>
            </div>
        </div>

        <div class="events-grid" id="events-grid">
            <!-- Events will be populated by JavaScript -->
        </div>

        <div class="table-footer">
            <div class="table-info">
                Showing <span id="events-start">1</span> to <span id="events-end">3</span> of <span id="events-total">12</span> events
            </div>
            <div class="pagination">
                <button class="pagination-btn" id="events-prev">Previous</button>
                <button class="pagination-btn active">1</button>
                <button class="pagination-btn">2</button>
                <button class="pagination-btn">3</button>
                <button class="pagination-btn" id="events-next">Next</button>
            </div>
        </div>
    </div>
</div>


<!-- Bookings Section -->
<div id="bookings-section" class="section-content">
    <div class="content-card">
        <div class="stats-grid small">
            <div class="stat-card small">
                <p class="stat-label">Total Bookings</p>
                <h3 class="stat-value">1,248</h3>
            </div>
            <div class="stat-card small">
                <p class="stat-label">Completed</p>
                <h3 class="stat-value">984</h3>
            </div>
            <div class="stat-card small">
                <p class="stat-label">Pending</p>
                <h3 class="stat-value">156</h3>
            </div>
            <div class="stat-card small">
                <p class="stat-label">Cancelled</p>
                <h3 class="stat-value">108</h3>
            </div>
        </div>

        <div class="table-container">
            <table class="data-table" id="bookings-table">
                <thead>
                    <tr>
                        <th>Booking ID</th>
                        <th>Event</th>
                        <th>User</th>
                        <th>Date</th>
                        <th>Tickets</th>
                        <th>Amount</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody id="bookings-table-body">
                    <!-- Bookings will be populated by JavaScript -->
                </tbody>
            </table>
        </div>

        <div class="table-footer">
            <div class="table-info">
                Showing <span id="bookings-start">1</span> to <span id="bookings-end">4</span> of <span id="bookings-total">24</span> entries
            </div>
            <div class="pagination">
                <button class="pagination-btn" id="bookings-prev">Previous</button>
                <button class="pagination-btn active">1</button>
                <button class="pagination-btn">2</button>
                <button class="pagination-btn">3</button>
                <button class="pagination-btn" id="bookings-next">Next</button>
            </div>
        </div>
    </div>
</div>


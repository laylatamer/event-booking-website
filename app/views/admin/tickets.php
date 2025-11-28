<!-- Tickets Section -->
<div id="tickets-section" class="section-content">
    <div class="content-card">
        <div class="table-container">
            <table class="data-table" id="tickets-table">
                <thead>
                    <tr>
                        <th>Event</th>
                        <th>Ticket Type</th>
                        <th>Price</th>
                        <th>Available</th>
                        <th>Sold</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody id="tickets-table-body">
                    <!-- Tickets will be populated by JavaScript -->
                </tbody>
            </table>
        </div>

        <div class="table-footer">
            <div class="table-info">
                Showing <span id="tickets-start">1</span> to <span id="tickets-end">4</span> of <span id="tickets-total">16</span> ticket types
            </div>
            <div class="pagination">
                <button class="pagination-btn" id="tickets-prev">Previous</button>
                <button class="pagination-btn active">1</button>
                <button class="pagination-btn">2</button>
                <button class="pagination-btn">3</button>
                <button class="pagination-btn" id="tickets-next">Next</button>
            </div>
        </div>
    </div>
</div>


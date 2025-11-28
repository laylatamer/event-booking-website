<!-- Messages Section -->
<div id="messages-section" class="section-content">
    <div class="content-card">
        <div class="table-controls">
            <div class="controls-left">
                <select id="message-status-filter" class="filter-select">
                    <option value="">All Status</option>
                    <option value="new">New</option>
                    <option value="read">Read</option>
                    <option value="replied">Replied</option>
                    <option value="archived">Archived</option>
                </select>
            </div>
            <div class="controls-right">
                <button class="icon-btn" id="mark-all-read-btn" title="Mark all as read">
                    <i data-feather="check-circle"></i>
                </button>
                <button class="icon-btn" id="export-messages-btn" title="Export messages">
                    <i data-feather="download"></i>
                </button>
            </div>
        </div>

        <div class="table-container">
            <table class="data-table" id="messages-table">
                <thead>
                    <tr>
                        <th>
                            <div class="checkbox-header">
                                <input type="checkbox" id="select-all-messages" class="checkbox-input">
                                <span>From</span>
                            </div>
                        </th>
                        <th>Email</th>
                        <th>Subject</th>
                        <th>Message</th>
                        <th>Date</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody id="messages-table-body">
                    <!-- Messages will be populated by JavaScript -->
                </tbody>
            </table>
        </div>

        <div class="table-footer">
            <div class="table-info">
                Showing <span id="messages-start">1</span> to <span id="messages-end">0</span> of <span id="messages-total">0</span> messages
            </div>
            <div class="pagination">
                <button class="pagination-btn" id="messages-prev">Previous</button>
                <button class="pagination-btn active">1</button>
                <button class="pagination-btn" id="messages-next">Next</button>
            </div>
        </div>
    </div>
</div>

<!-- View Message Modal -->
<div id="view-message-modal" class="modal hidden">
    <div class="modal-content large">
        <div class="modal-header">
            <h3>Message Details</h3>
            <button class="close-modal" data-modal="view-message">
                <i data-feather="x"></i>
            </button>
        </div>
        <div id="message-details">
            <!-- Message details will be populated by JavaScript -->
        </div>
        <div class="modal-actions">
            <button type="button" class="secondary-btn" data-modal="view-message">Close</button>
            <button type="button" id="reply-message-btn" class="primary-btn">
                <i data-feather="mail"></i>
                <span>Reply</span>
            </button>
            <button type="button" id="mark-read-btn" class="secondary-btn">
                <i data-feather="check"></i>
                <span>Mark as Read</span>
            </button>
        </div>
    </div>
</div>


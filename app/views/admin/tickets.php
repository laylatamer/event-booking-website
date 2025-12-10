<?php
// tickets.php - Admin View for Managing Ticket Types/Tiers for Events

$database = new Database();
$db = $database->getConnection();
$ticketsModel = new TicketsModel($db);
// $eventModel = new Event($db);
?>

<div id="tickets-section" class="section-content">
    <h2 class="section-title">Event Ticket Tiers</h2>
    <p class="section-description">Manage the different ticket types (e.g., General, VIP) and their inventory for your events.</p>

    <div class="content-card">
        <div class="table-header-controls">
            <h3>Ticket Tiers List</h3>
            <button class="btn btn-primary" id="add-ticket-btn">
                <i class="fas fa-plus"></i> Add New Ticket Type
            </button>
        </div>
        
        <div class="table-container">
            <table class="data-table" id="tickets-table">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Event Title</th>
                        <th>Ticket Name</th>
                        <th>Price</th>
                        <th>Total Qty</th>
                        <th>Available</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody id="tickets-table-body">
                    <tr><td colspan="8" style="text-align:center;">No ticket types found.</td></tr>
                </tbody>
            </table>
        </div>
    </div>
</div>

<div id="ticket-modal" class="modal hidden">
    <div class="modal-content">
        <div class="modal-header">
            <h3 id="modal-title">Add New Ticket Type</h3>
            <span class="close-btn">&times;</span>
        </div>
        <form id="ticket-form">
            <input type="hidden" id="ticket-id" name="id">
            
            <label for="event-select">Associated Event:</label>
            <select id="event-select" name="event_id" required>
                <option value="">-- Select Event --</option>
            </select>
            
            <label for="ticket-name">Ticket Name (e.g., VIP, General):</label>
            <input type="text" id="ticket-name" name="name" required>
            
            <label for="ticket-description">Description:</label>
            <textarea id="ticket-description" name="description"></textarea>
            
            <label for="ticket-price">Price ($):</label>
            <input type="number" id="ticket-price" name="price" step="0.01" min="0" required>
            
            <label for="ticket-total-quantity">Total Quantity:</label>
            <input type="number" id="ticket-total-quantity" name="total_quantity" min="1" required>
            
            <label for="ticket-status">Status:</label>
            <select id="ticket-status" name="is_active">
                <option value="1">Active</option>
                <option value="0">Inactive</option>
            </select>
            
            <button type="submit" class="btn btn-primary form-submit-btn">Save Ticket Type</button>
        </form>
    </div>
</div>
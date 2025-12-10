<!-- Modals - All hidden by default -->
<!-- Add User Modal -->
<div id="add-user-modal" class="modal hidden">
    <div class="modal-content">
        <div class="modal-header">
            <h3>Add New User</h3>
            <button class="close-modal" data-modal="add-user">
                <i data-feather="x"></i>
            </button>
        </div>
        <form id="add-user-form">
            <div class="form-grid">
                <div class="form-group">
                    <label>Full Name</label>
                    <input type="text" id="user-name" required>
                </div>
                <div class="form-group">
                    <label>Email</label>
                    <input type="email" id="user-email" required>
                </div>
                <div class="form-group">
                    <label>Role</label>
                    <select id="user-role" required>
                        <option value="">Select Role</option>
                        <option value="admin">Admin</option>
                        <option value="user">User</option>
                        <option value="organizer">Organizer</option>
                    </select>
                </div>
                <div class="form-group">
                    <label>Password</label>
                    <input type="password" id="user-password" required>
                </div>
            </div>
            <div class="modal-actions">
                <button type="button" class="secondary-btn" data-modal="add-user">Cancel</button>
                <button type="submit" class="primary-btn">Save User</button>
            </div>
        </form>
    </div>
</div>

<!-- Edit User Modal -->
<div id="edit-user-modal" class="modal hidden">
    <div class="modal-content">
        <div class="modal-header">
            <h3>Edit User</h3>
            <button class="close-modal" data-modal="edit-user">
                <i data-feather="x"></i>
            </button>
        </div>
        <form id="edit-user-form">
            <input type="hidden" id="edit-user-id">
            <div class="form-grid">
                <div class="form-group">
                    <label>Full Name</label>
                    <input type="text" id="edit-user-name" required>
                </div>
                <div class="form-group">
                    <label>Email</label>
                    <input type="email" id="edit-user-email" required>
                </div>
                <div class="form-group">
                    <label>Role</label>
                    <select id="edit-user-role" required>
                        <option value="admin">Admin</option>
                        <option value="user">User</option>
                        <option value="organizer">Organizer</option>
                    </select>
                </div>
                <div class="form-group">
                    <label>Status</label>
                    <select id="edit-user-status" required>
                        <option value="active">Active</option>
                        <option value="inactive">Inactive</option>
                        <option value="pending">Pending</option>
                    </select>
                </div>
            </div>
            <div class="modal-actions">
                <button type="button" class="secondary-btn" data-modal="edit-user">Cancel</button>
                <button type="submit" class="primary-btn">Update User</button>
            </div>
        </form>
    </div>
</div>

<!-- View Booking Modal -->
<div id="view-booking-modal" class="modal hidden">
    <div class="modal-content">
        <div class="modal-header">
            <h3>Booking Details</h3>
            <button class="close-modal" data-modal="view-booking">
                <i data-feather="x"></i>
            </button>
        </div>
        <div id="booking-details">
            <!-- Booking details will be populated by JavaScript -->
        </div>
        <div class="modal-actions">
            <button type="button" class="secondary-btn" data-modal="view-booking">Close</button>
            <button class="primary-btn" id="print-booking-btn">
                <i data-feather="printer"></i>
                <span>Print</span>
            </button>
        </div>
    </div>
</div>

<!-- Add Event Modal (Updated with admin-specific IDs) -->
<div id="add-event-modal" class="modal hidden">
    <div class="modal-content large">
        <div class="modal-header">
            <h3>Add New Event</h3>
            <button class="close-modal" data-modal="add-event">
                <i data-feather="x"></i>
            </button>
        </div>
        <form id="add-event-form">
            <div class="form-grid">
                <!-- Basic Information -->
                <div class="form-section">
                    <h4>Basic Information</h4>
                    <div class="form-group">
                        <label>Event Title *</label>
                        <input type="text" id="admin-event-title" name="title" required>
                    </div>
                    <div class="form-group">
                        <label>Description *</label>
                        <textarea id="admin-event-description" name="description" rows="4" required></textarea>
                    </div>
                </div>

                <!-- Category & Venue -->
                <div class="form-section">
                    <h4>Category & Venue</h4>
                    <div class="form-group">
                        <label>Main Category *</label>
                        <select id="admin-event-main-category" name="main_category_id" required>
                            <option value="">Select Category</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label>Subcategory *</label>
                        <select id="admin-event-subcategory" name="subcategory_id" required disabled>
                            <option value="">Select Subcategory</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label>Venue *</label>
                        <select id="admin-event-venue" name="venue_id" required>
                            <option value="">Select Venue</option>
                        </select>
                    </div>
                </div>

                <!-- Date & Time -->
                <div class="form-section">
                    <h4>Date & Time</h4>
                    <div class="form-group">
                        <label>Start Date & Time *</label>
                        <input type="datetime-local" id="admin-event-date" name="date" required>
                    </div>
                    <div class="form-group">
                        <label>End Date & Time (Optional)</label>
                        <input type="datetime-local" id="admin-event-end-date" name="end_date">
                    </div>
                </div>

                <!-- Pricing -->
                <div class="form-section">
                    <h4>Pricing</h4>
                    <div class="form-group two-columns">
                        <div>
                            <label>Regular Price *</label>
                            <input type="number" id="admin-event-price" name="price" min="0" step="0.01" required>
                        </div>
                        <div>
                            <label>Discounted Price (Optional)</label>
                            <input type="number" id="admin-event-discounted-price" name="discounted_price" min="0" step="0.01">
                        </div>
                    </div>
                </div>

                <!-- Tickets -->
                <div class="form-section">
                    <h4>Tickets</h4>
                    <div class="form-group two-columns">
                        <div>
                            <label>Total Tickets *</label>
                            <input type="number" id="admin-event-total-tickets" name="total_tickets" min="1" required>
                        </div>
                        <div>
                            <label>Available Tickets *</label>
                            <input type="number" id="admin-event-available-tickets" name="available_tickets" min="1" required>
                        </div>
                    </div>
                    <div class="form-group two-columns">
                        <div>
                            <label>Min per Booking</label>
                            <input type="number" id="admin-event-min-tickets" name="min_tickets_per_booking" min="1" value="1">
                        </div>
                        <div>
                            <label>Max per Booking</label>
                            <input type="number" id="admin-event-max-tickets" name="max_tickets_per_booking" min="1" value="10">
                        </div>
                    </div>
                </div>

                <!-- Media -->
                <div class="form-section">
                    <h4>Media</h4>
                    <div class="form-group">
                        <label>Main Image URL</label>
                        <input type="url" id="admin-event-image-url" name="image_url" placeholder="https://example.com/image.jpg">
                    </div>
                    <div class="form-group">
                        <label>Gallery Images (JSON array, one per line)</label>
                        <textarea id="admin-event-gallery-images" name="gallery_images" rows="3" placeholder='["https://example.com/image1.jpg", "https://example.com/image2.jpg"]'></textarea>
                    </div>
                </div>

                <!-- Additional Information -->
                <div class="form-section">
                    <h4>Additional Information</h4>
                    <div class="form-group">
                        <label>Terms & Conditions</label>
                        <textarea id="admin-event-terms" name="terms_conditions" rows="3"></textarea>
                    </div>
                    <div class="form-group">
                        <label>Additional Info (JSON)</label>
                        <textarea id="admin-event-additional-info" name="additional_info" rows="3" placeholder='{"duration": "2 hours", "age_restriction": "18+"}'></textarea>
                    </div>
                    <div class="form-group">
                        <label>Status</label>
                        <select id="admin-event-status" name="status">
                            <option value="draft">Draft</option>
                            <option value="active">Active</option>
                            <option value="inactive">Inactive</option>
                        </select>
                    </div>
                </div>
            </div>
            <div class="modal-actions">
                <button type="button" class="secondary-btn" data-modal="add-event">Cancel</button>
                <button type="submit" class="primary-btn">Create Event</button>
            </div>
        </form>
    </div>
</div>

<!-- Edit Event Modal (Updated with admin-specific IDs) -->
<div id="edit-event-modal" class="modal hidden">
    <div class="modal-content large">
        <div class="modal-header">
            <h3>Edit Event</h3>
            <button class="close-modal" data-modal="edit-event">
                <i data-feather="x"></i>
            </button>
        </div>
        <form id="edit-event-form">
            <input type="hidden" id="admin-edit-event-id" name="id">
            <div class="form-grid">
                <!-- Basic Information -->
                <div class="form-section">
                    <h4>Basic Information</h4>
                    <div class="form-group">
                        <label>Event Title *</label>
                        <input type="text" id="admin-edit-event-title" name="title" required>
                    </div>
                    <div class="form-group">
                        <label>Description *</label>
                        <textarea id="admin-edit-event-description" name="description" rows="4" required></textarea>
                    </div>
                </div>

                <!-- Category & Venue -->
                <div class="form-section">
                    <h4>Category & Venue</h4>
                    <div class="form-group">
                        <label>Main Category *</label>
                        <select id="admin-edit-event-main-category" name="main_category_id" required>
                            <option value="">Select Category</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label>Subcategory *</label>
                        <select id="admin-edit-event-subcategory" name="subcategory_id" required>
                            <option value="">Select Subcategory</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label>Venue *</label>
                        <select id="admin-edit-event-venue" name="venue_id" required>
                            <option value="">Select Venue</option>
                        </select>
                    </div>
                </div>

                <!-- Date & Time -->
                <div class="form-section">
                    <h4>Date & Time</h4>
                    <div class="form-group">
                        <label>Start Date & Time *</label>
                        <input type="datetime-local" id="admin-edit-event-date" name="date" required>
                    </div>
                    <div class="form-group">
                        <label>End Date & Time (Optional)</label>
                        <input type="datetime-local" id="admin-edit-event-end-date" name="end_date">
                    </div>
                </div>

                <!-- Pricing -->
                <div class="form-section">
                    <h4>Pricing</h4>
                    <div class="form-group two-columns">
                        <div>
                            <label>Regular Price *</label>
                            <input type="number" id="admin-edit-event-price" name="price" min="0" step="0.01" required>
                        </div>
                        <div>
                            <label>Discounted Price (Optional)</label>
                            <input type="number" id="admin-edit-event-discounted-price" name="discounted_price" min="0" step="0.01">
                        </div>
                    </div>
                </div>

                <!-- Tickets -->
                <div class="form-section">
                    <h4>Tickets</h4>
                    <div class="form-group two-columns">
                        <div>
                            <label>Total Tickets *</label>
                            <input type="number" id="admin-edit-event-total-tickets" name="total_tickets" min="1" required>
                        </div>
                        <div>
                            <label>Available Tickets *</label>
                            <input type="number" id="admin-edit-event-available-tickets" name="available_tickets" min="1" required>
                        </div>
                    </div>
                    <div class="form-group two-columns">
                        <div>
                            <label>Min per Booking</label>
                            <input type="number" id="admin-edit-event-min-tickets" name="min_tickets_per_booking" min="1">
                        </div>
                        <div>
                            <label>Max per Booking</label>
                            <input type="number" id="admin-edit-event-max-tickets" name="max_tickets_per_booking" min="1">
                        </div>
                    </div>
                </div>

                <!-- Media -->
                <div class="form-section">
                    <h4>Media</h4>
                    <div class="form-group">
                        <label>Main Image URL</label>
                        <input type="url" id="admin-edit-event-image-url" name="image_url">
                    </div>
                    <div class="form-group">
                        <label>Gallery Images (JSON array)</label>
                        <textarea id="admin-edit-event-gallery-images" name="gallery_images" rows="3"></textarea>
                    </div>
                </div>

                <!-- Additional Information -->
                <div class="form-section">
                    <h4>Additional Information</h4>
                    <div class="form-group">
                        <label>Terms & Conditions</label>
                        <textarea id="admin-edit-event-terms" name="terms_conditions" rows="3"></textarea>
                    </div>
                    <div class="form-group">
                        <label>Additional Info (JSON)</label>
                        <textarea id="admin-edit-event-additional-info" name="additional_info" rows="3"></textarea>
                    </div>
                    <div class="form-group">
                        <label>Status</label>
                        <select id="admin-edit-event-status" name="status">
                            <option value="draft">Draft</option>
                            <option value="active">Active</option>
                            <option value="inactive">Inactive</option>
                        </select>
                    </div>
                </div>
            </div>
            <div class="modal-actions">
                <button type="button" class="secondary-btn" data-modal="edit-event">Cancel</button>
                <button type="submit" class="primary-btn">Update Event</button>
            </div>
        </form>
    </div>
</div>

<!-- Add Category Modal -->
<div id="add-category-modal" class="modal hidden">
    <div class="modal-content">
        <div class="modal-header">
            <h3>Add New Category</h3>
            <button class="close-modal" data-modal="add-category">
                <i data-feather="x"></i>
            </button>
        </div>
        <form id="add-category-form">
            <div class="form-grid">
                <div class="form-group">
                    <label>Category Name</label>
                    <input type="text" id="category-name" required>
                </div>
                <div class="form-group">
                    <label>Description</label>
                    <textarea id="category-description" rows="3"></textarea>
                </div>
                <div class="form-group">
                    <label>Icon</label>
                    <select id="category-icon" required>
                        <option value="">Select Icon</option>
                        <option value="music">Music</option>
                        <option value="cpu">Technology</option>
                        <option value="palette">Art</option>
                        <option value="coffee">Food & Drink</option>
                    </select>
                </div>
                <div class="form-group">
                    <label>Color</label>
                    <select id="category-color" required>
                        <option value="">Select Color</option>
                        <option value="orange">Orange</option>
                        <option value="black">Black</option>
                        <option value="white">White</option>
                    </select>
                </div>
            </div>
            <div class="modal-actions">
                <button type="button" class="secondary-btn" data-modal="add-category">Cancel</button>
                <button type="submit" class="primary-btn">Add Category</button>
            </div>
        </form>
    </div>
</div>

<!-- Edit Category Modal -->
<div id="edit-category-modal" class="modal hidden">
    <div class="modal-content">
        <div class="modal-header">
            <h3>Edit Category</h3>
            <button class="close-modal" data-modal="edit-category">
                <i data-feather="x"></i>
            </button>
        </div>
        <form id="edit-category-form">
            <input type="hidden" id="edit-category-id">
            <div class="form-grid">
                <div class="form-group">
                    <label>Category Name</label>
                    <input type="text" id="edit-category-name" required>
                </div>
                <div class="form-group">
                    <label>Description</label>
                    <textarea id="edit-category-description" rows="3"></textarea>
                </div>
                <div class="form-group">
                    <label>Icon</label>
                    <select id="edit-category-icon" required>
                        <option value="music">Music</option>
                        <option value="cpu">Technology</option>
                        <option value="palette">Art</option>
                        <option value="coffee">Food & Drink</option>
                    </select>
                </div>
                <div class="form-group">
                    <label>Color</label>
                    <select id="edit-category-color" required>
                        <option value="orange">Orange</option>
                        <option value="black">Black</option>
                        <option value="white">White</option>
                    </select>
                </div>
            </div>
            <div class="modal-actions">
                <button type="button" class="secondary-btn" data-modal="edit-category">Cancel</button>
                <button type="submit" class="primary-btn">Update Category</button>
            </div>
        </form>
    </div>
</div>

<!-- Add Location Modal -->
<div id="add-location-modal" class="modal hidden">
    <div class="modal-content">
        <div class="modal-header">
            <h3>Add New Location</h3>
            <button class="close-modal" data-modal="add-location">
                <i data-feather="x"></i>
            </button>
        </div>
        <form id="add-location-form">
            <div class="form-grid">
                <div class="form-group">
                    <label>Venue Name</label>
                    <input type="text" id="location-name" required>
                </div>
                <div class="form-group">
                    <label>Address</label>
                    <input type="text" id="location-address" required>
                </div>
                <div class="form-group two-columns">
                    <div>
                        <label>City</label>
                        <input type="text" id="location-city" required>
                    </div>
                    <div>
                        <label>State</label>
                        <input type="text" id="location-state" required>
                    </div>
                </div>
                <div class="form-group">
                    <label>Capacity</label>
                    <input type="number" id="location-capacity" required>
                </div>
                <div class="form-group">
                    <label>Venue Image</label>
                    <div class="file-upload">
                        <i data-feather="upload"></i>
                        <p>Click to upload or drag and drop</p>
                        <input type="file" id="location-image" accept="image/*">
                    </div>
                </div>
            </div>
            <div class="modal-actions">
                <button type="button" class="secondary-btn" data-modal="add-location">Cancel</button>
                <button type="submit" class="primary-btn">Add Location</button>
            </div>
        </form>
    </div>
</div>

<!-- Edit Location Modal -->
<div id="edit-location-modal" class="modal hidden">
    <div class="modal-content">
        <div class="modal-header">
            <h3>Edit Location</h3>
            <button class="close-modal" data-modal="edit-location">
                <i data-feather="x"></i>
            </button>
        </div>
        <form id="edit-location-form">
            <input type="hidden" id="edit-location-id">
            <div class="form-grid">
                <div class="form-group">
                    <label>Venue Name</label>
                    <input type="text" id="edit-location-name" required>
                </div>
                <div class="form-group">
                    <label>Address</label>
                    <input type="text" id="edit-location-address" required>
                </div>
                <div class="form-group two-columns">
                    <div>
                        <label>City</label>
                        <input type="text" id="edit-location-city" required>
                    </div>
                    <div>
                        <label>State</label>
                        <input type="text" id="edit-location-state" required>
                    </div>
                </div>
                <div class="form-group">
                    <label>Capacity</label>
                    <input type="number" id="edit-location-capacity" required>
                </div>
                <div class="form-group">
                    <label>Venue Image</label>
                    <div class="file-upload">
                        <i data-feather="upload"></i>
                        <p>Click to upload or drag and drop</p>
                        <input type="file" id="edit-location-image" accept="image/*">
                    </div>
                </div>
            </div>
            <div class="modal-actions">
                <button type="button" class="secondary-btn" data-modal="edit-location">Cancel</button>
                <button type="submit" class="primary-btn">Update Location</button>
            </div>
        </form>
    </div>
</div>

<!-- Add Ticket Modal -->
<div id="add-ticket-modal" class="modal hidden">
    <div class="modal-content">
        <div class="modal-header">
            <h3>Add New Ticket Type</h3>
            <button class="close-modal" data-modal="add-ticket">
                <i data-feather="x"></i>
            </button>
        </div>
        <form id="add-ticket-form">
            <div class="form-grid">
                <div class="form-group">
                    <label>Event</label>
                    <select id="ticket-event" required>
                        <option value="">Select Event</option>
                    </select>
                </div>
                <div class="form-group">
                    <label>Ticket Type</label>
                    <input type="text" id="ticket-type" required>
                </div>
                <div class="form-group">
                    <label>Price</label>
                    <div class="price-input">
                        <span>$</span>
                        <input type="number" step="0.01" id="ticket-price" required>
                    </div>
                </div>
                <div class="form-group">
                    <label>Quantity Available</label>
                    <input type="number" id="ticket-quantity" required>
                </div>
                <div class="form-group">
                    <label>Description</label>
                    <textarea id="ticket-description" rows="3"></textarea>
                </div>
            </div>
            <div class="modal-actions">
                <button type="button" class="secondary-btn" data-modal="add-ticket">Cancel</button>
                <button type="submit" class="primary-btn">Add Ticket Type</button>
            </div>
        </form>
    </div>
</div>

<!-- Edit Ticket Modal -->
<div id="edit-ticket-modal" class="modal hidden">
    <div class="modal-content">
        <div class="modal-header">
            <h3>Edit Ticket Type</h3>
            <button class="close-modal" data-modal="edit-ticket">
                <i data-feather="x"></i>
            </button>
        </div>
        <form id="edit-ticket-form">
            <input type="hidden" id="edit-ticket-id">
            <div class="form-grid">
                <div class="form-group">
                    <label>Event</label>
                    <select id="edit-ticket-event" required>
                    </select>
                </div>
                <div class="form-group">
                    <label>Ticket Type</label>
                    <input type="text" id="edit-ticket-type" required>
                </div>
                <div class="form-group">
                    <label>Price</label>
                    <div class="price-input">
                        <span>$</span>
                        <input type="number" step="0.01" id="edit-ticket-price" required>
                    </div>
                </div>
                <div class="form-group">
                    <label>Quantity Available</label>
                    <input type="number" id="edit-ticket-quantity" required>
                </div>
                <div class="form-group">
                    <label>Description</label>
                    <textarea id="edit-ticket-description" rows="3"></textarea>
                </div>
            </div>
            <div class="modal-actions">
                <button type="button" class="secondary-btn" data-modal="edit-ticket">Cancel</button>
                <button type="submit" class="primary-btn">Update Ticket Type</button>
            </div>
        </form>
    </div>
</div>

<!-- User Profile Modal -->
<div id="user-profile-modal" class="modal hidden">
    <div class="modal-content">
        <div class="modal-header">
            <h3>User Profile</h3>
            <button class="close-modal" data-modal="user-profile">
                <i data-feather="x"></i>
            </button>
        </div>
        <form id="user-profile-form">
            <div class="profile-avatar-section">
                <div class="avatar-upload">
                    <div class="avatar-preview">
                        <img id="profile-avatar-preview" src="default-avatar.png" alt="Profile Avatar">
                    </div>
                    <div class="avatar-upload-controls">
                        <input type="file" id="avatar-upload" accept="image/*" class="hidden">
                        <button type="button" id="change-avatar-btn" class="secondary-btn">Change Avatar</button>
                    </div>
                </div>
            </div>
            <div class="form-grid">
                <div class="form-group">
                    <label>Full Name</label>
                    <input type="text" id="profile-name" value="Admin" required>
                </div>
                <div class="form-group">
                    <label>Email</label>
                    <input type="email" id="profile-email" value="admin@egzly.com" required>
                </div>
                <div class="form-group">
                    <label>Role</label>
                    <input type="text" id="profile-role" value="Administrator" readonly>
                </div>
                <div class="form-group">
                    <label>Phone</label>
                    <input type="tel" id="profile-phone" value="+1 (555) 123-4567">
                </div>
            </div>
            <div class="modal-actions">
                <button type="button" id="logout-btn" class="danger-btn">Logout</button>
                <button type="submit" class="primary-btn">Save Changes</button>
            </div>
        </form>
    </div>
</div>

<!-- Confirmation Modal -->
<div id="confirmation-modal" class="modal hidden">
    <div class="modal-content small">
        <div class="modal-header">
            <h3>Confirm Action</h3>
            <button class="close-modal" data-modal="confirmation">
                <i data-feather="x"></i>
            </button>
        </div>
        <div class="confirmation-content">
            <p id="confirmation-message">Are you sure you want to perform this action?</p>
        </div>
        <div class="modal-actions">
            <button type="button" class="secondary-btn" data-modal="confirmation">Cancel</button>
            <button type="button" id="confirm-action-btn" class="danger-btn">Confirm</button>
        </div>
    </div>
</div>

<!-- Add Subcategory Modal -->
<div id="add-subcategory-modal" class="modal hidden">
    <div class="modal-content">
        <div class="modal-header">
            <h3>Add New Subcategory</h3>
            <button type="button" class="close-modal" data-modal="add-subcategory">
                <i data-feather="x"></i>
            </button>
        </div>
        <form id="add-subcategory-form">
            <input type="hidden" id="subcategory-main-category-id">
            <div class="form-grid">
                <div class="form-group">
                    <label>Main Category</label>
                    <input type="text" id="subcategory-main-category-name" readonly class="readonly-input">
                </div>
                <div class="form-group">
                    <label>Subcategory Name</label>
                    <input type="text" id="subcategory-name" required>
                </div>
                <div class="form-group">
                    <label>Status</label>
                    <select id="subcategory-status" required>
                        <option value="active">Active</option>
                        <option value="inactive">Inactive</option>
                    </select>
                </div>
            </div>
            <div class="modal-actions">
                <button type="button" class="secondary-btn" data-modal="add-subcategory">Cancel</button>
                <button type="submit" class="primary-btn">Add Subcategory</button>
            </div>
        </form>
    </div>
</div>

<!-- Edit Subcategory Modal -->
<div id="edit-subcategory-modal" class="modal hidden">
    <div class="modal-content">
        <div class="modal-header">
            <h3>Edit Subcategory</h3>
            <button type="button" class="close-modal" data-modal="edit-subcategory">
                <i data-feather="x"></i>
            </button>
        </div>
        <form id="edit-subcategory-form">
            <input type="hidden" id="edit-subcategory-id">
            <div class="form-grid">
                <div class="form-group">
                    <label>Main Category</label>
                    <select id="edit-subcategory-main-category" required>
                        <option value="1">Sports</option>
                        <option value="2">Entertainment</option>
                    </select>
                </div>
                <div class="form-group">
                    <label>Subcategory Name</label>
                    <input type="text" id="edit-subcategory-name" required>
                </div>
                <div class="form-group">
                    <label>Status</label>
                    <select id="edit-subcategory-status" required>
                        <option value="active">Active</option>
                        <option value="inactive">Inactive</option>
                    </select>
                </div>
            </div>
            <div class="modal-actions">
                <button type="button" class="secondary-btn" data-modal="edit-subcategory">Cancel</button>
                <button type="submit" class="primary-btn">Update Subcategory</button>
            </div>
        </form>
    </div>
</div>
// Messages Section Scripts

let messages = [];
let filteredMessages = [];
let currentPage = { messages: 1 };
const itemsPerPage = 10;
const apiUrl = '../../../public/api/contact_messages.php';

document.addEventListener('DOMContentLoaded', function() {
    initializeMessagesEventListeners();
    loadMessages();
});

function initializeMessagesEventListeners() {
    const messageSearch = document.getElementById('message-search');
    if (messageSearch) {
        messageSearch.addEventListener('input', filterMessages);
    }
    
    const messageStatusFilter = document.getElementById('message-status-filter');
    if (messageStatusFilter) {
        messageStatusFilter.addEventListener('change', filterMessages);
    }
    
    const messagesPrev = document.getElementById('messages-prev');
    const messagesNext = document.getElementById('messages-next');
    if (messagesPrev) messagesPrev.addEventListener('click', () => changePage('messages', -1));
    if (messagesNext) messagesNext.addEventListener('click', () => changePage('messages', 1));
    
    const markAllReadBtn = document.getElementById('mark-all-read-btn');
    if (markAllReadBtn) {
        markAllReadBtn.addEventListener('click', markAllAsRead);
    }
    
    const markReadBtn = document.getElementById('mark-read-btn');
    if (markReadBtn) {
        markReadBtn.addEventListener('click', markCurrentMessageAsRead);
    }
    
    const replyMessageBtn = document.getElementById('reply-message-btn');
    if (replyMessageBtn) {
        replyMessageBtn.addEventListener('click', replyToMessage);
    }
}

// Load messages from API
async function loadMessages() {
    try {
        const response = await fetch(apiUrl);
        if (!response.ok) {
            throw new Error('Failed to load messages');
        }
        messages = await response.json();
        filteredMessages = [...messages];
        displayMessages();
    } catch (error) {
        console.error('Error loading messages:', error);
        const messagesTableBody = document.getElementById('messages-table-body');
        if (messagesTableBody) {
            messagesTableBody.innerHTML = `
                <tr>
                    <td colspan="7" class="empty-state">
                        <i data-feather="alert-circle"></i>
                        <p>Error loading messages. Please try again later.</p>
                    </td>
                </tr>
            `;
            feather.replace();
        }
    }
}

// Display messages in the table
function displayMessages() {
    const messagesTableBody = document.getElementById('messages-table-body');
    if (!messagesTableBody) return;
    
    messagesTableBody.innerHTML = '';
    
    if (filteredMessages.length === 0) {
        messagesTableBody.innerHTML = `
            <tr>
                <td colspan="7" class="empty-state">
                    <i data-feather="mail"></i>
                    <p>No messages found</p>
                </td>
            </tr>
        `;
        feather.replace();
        updatePaginationInfo(0);
        return;
    }
    
    const startIndex = (currentPage.messages - 1) * itemsPerPage;
    const endIndex = startIndex + itemsPerPage;
    const messagesToShow = filteredMessages.slice(startIndex, endIndex);
    
    messagesToShow.forEach(message => {
        const row = document.createElement('tr');
        const messagePreview = message.message.length > 50 
            ? message.message.substring(0, 50) + '...' 
            : message.message;
        const status = message.status || 'new';
        const statusClass = status === 'new' ? 'pending' : status === 'read' ? 'completed' : status;
        const date = new Date(message.created_at).toLocaleDateString('en-US', {
            year: 'numeric',
            month: 'short',
            day: 'numeric',
            hour: '2-digit',
            minute: '2-digit'
        });
        
        row.innerHTML = `
            <td>
                <div class="checkbox-header">
                    <input type="checkbox" class="checkbox-input message-checkbox" data-id="${message.id}">
                    <div class="user-info">
                        <span>${escapeHtml(message.name)}</span>
                    </div>
                </div>
            </td>
            <td>${escapeHtml(message.email)}</td>
            <td>${escapeHtml(message.subject)}</td>
            <td class="message-preview">${escapeHtml(messagePreview)}</td>
            <td>${date}</td>
            <td><span class="status-badge ${statusClass}">${status.charAt(0).toUpperCase() + status.slice(1)}</span></td>
            <td>
                <div class="action-buttons">
                    <button class="action-btn view-message" data-id="${message.id}">
                        <i data-feather="eye"></i>
                    </button>
                    <button class="action-btn delete delete-message" data-id="${message.id}">
                        <i data-feather="trash-2"></i>
                    </button>
                </div>
            </td>
        `;
        messagesTableBody.appendChild(row);
    });
    
    updatePaginationInfo(filteredMessages.length);
    updatePaginationButtons('messages', filteredMessages.length, itemsPerPage);
    
    feather.replace();
    
    // Add event listeners
    document.querySelectorAll('.view-message').forEach(button => {
        button.addEventListener('click', function() {
            const messageId = parseInt(this.getAttribute('data-id'));
            viewMessage(messageId);
        });
    });
    
    document.querySelectorAll('.delete-message').forEach(button => {
        button.addEventListener('click', function() {
            const messageId = parseInt(this.getAttribute('data-id'));
            deleteMessage(messageId);
        });
    });
}

// Filter messages
function filterMessages() {
    const searchTerm = document.getElementById('message-search').value.toLowerCase();
    const statusFilter = document.getElementById('message-status-filter').value;
    
    filteredMessages = messages.filter(message => {
        const matchesSearch = 
            message.name.toLowerCase().includes(searchTerm) ||
            message.email.toLowerCase().includes(searchTerm) ||
            message.subject.toLowerCase().includes(searchTerm) ||
            message.message.toLowerCase().includes(searchTerm);
        
        const matchesStatus = !statusFilter || (message.status || 'new') === statusFilter;
        
        return matchesSearch && matchesStatus;
    });
    
    currentPage.messages = 1;
    displayMessages();
}

// View message details
async function viewMessage(messageId) {
    const message = messages.find(m => m.id === messageId);
    if (!message) {
        // Try to fetch from API
        try {
            const response = await fetch(`${apiUrl}?id=${messageId}`);
            if (response.ok) {
                const fetchedMessage = await response.json();
                showMessageDetails(fetchedMessage);
                return;
            }
        } catch (error) {
            console.error('Error fetching message:', error);
        }
        alert('Message not found');
        return;
    }
    
    showMessageDetails(message);
}

// Show message details in modal
function showMessageDetails(message) {
    const messageDetails = document.getElementById('message-details');
    const date = new Date(message.created_at).toLocaleDateString('en-US', {
        year: 'numeric',
        month: 'long',
        day: 'numeric',
        hour: '2-digit',
        minute: '2-digit'
    });
    
    messageDetails.innerHTML = `
        <div class="message-detail-view">
            <div class="message-header-info">
                <div class="message-field">
                    <label>From:</label>
                    <p>${escapeHtml(message.name)}</p>
                </div>
                <div class="message-field">
                    <label>Email:</label>
                    <p><a href="mailto:${escapeHtml(message.email)}">${escapeHtml(message.email)}</a></p>
                </div>
                <div class="message-field">
                    <label>Date:</label>
                    <p>${date}</p>
                </div>
                <div class="message-field">
                    <label>Status:</label>
                    <p><span class="status-badge ${message.status || 'new'}">${(message.status || 'new').charAt(0).toUpperCase() + (message.status || 'new').slice(1)}</span></p>
                </div>
            </div>
            <div class="message-field">
                <label>Subject:</label>
                <p class="message-subject">${escapeHtml(message.subject)}</p>
            </div>
            <div class="message-field">
                <label>Message:</label>
                <div class="message-content">${escapeHtml(message.message).replace(/\n/g, '<br>')}</div>
            </div>
        </div>
    `;
    
    // Store current message ID for actions
    document.getElementById('mark-read-btn').setAttribute('data-message-id', message.id);
    document.getElementById('reply-message-btn').setAttribute('data-message-id', message.id);
    
    openModal('view-message');
}

// Mark message as read
async function markCurrentMessageAsRead() {
    const messageId = parseInt(document.getElementById('mark-read-btn').getAttribute('data-message-id'));
    if (!messageId) return;
    
    await updateMessageStatus(messageId, 'read');
}

// Mark all messages as read
async function markAllAsRead() {
    if (filteredMessages.length === 0) return;
    
    if (!confirm('Mark all messages as read?')) return;
    
    try {
        for (const message of filteredMessages) {
            if ((message.status || 'new') !== 'read') {
                await updateMessageStatus(message.id, 'read');
            }
        }
        await loadMessages();
        alert('All messages marked as read');
    } catch (error) {
        console.error('Error marking all as read:', error);
        alert('Error marking messages as read');
    }
}

// Update message status
async function updateMessageStatus(messageId, status) {
    try {
        const formData = new URLSearchParams();
        formData.append('id', messageId);
        formData.append('status', status);
        
        const response = await fetch(apiUrl, {
            method: 'PATCH',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
            },
            body: formData.toString()
        });
        
        if (response.ok) {
            const result = await response.json();
            if (result.ok) {
                // Update local message
                const message = messages.find(m => m.id === messageId);
                if (message) {
                    message.status = status;
                }
                await loadMessages();
                return true;
            }
        }
        throw new Error('Failed to update message status');
    } catch (error) {
        console.error('Error updating message status:', error);
        alert('Error updating message status');
        return false;
    }
}

// Delete message
function deleteMessage(messageId) {
    showConfirmation('Are you sure you want to delete this message?', async () => {
        try {
            const formData = new URLSearchParams();
            formData.append('id', messageId);
            
            const response = await fetch(apiUrl, {
                method: 'DELETE',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: formData.toString()
            });
            
            if (response.ok) {
                const result = await response.json();
                if (result.ok) {
                    await loadMessages();
                    alert('Message deleted successfully');
                } else {
                    alert('Failed to delete message');
                }
            } else {
                throw new Error('Failed to delete message');
            }
        } catch (error) {
            console.error('Error deleting message:', error);
            alert('Error deleting message');
        }
    });
}

// Reply to message
function replyToMessage() {
    const messageId = parseInt(document.getElementById('reply-message-btn').getAttribute('data-message-id'));
    const message = messages.find(m => m.id === messageId);
    if (!message) return;
    
    // Open email client with pre-filled information
    const subject = encodeURIComponent(`Re: ${message.subject}`);
    const body = encodeURIComponent(`\n\n--- Original Message ---\nFrom: ${message.name} (${message.email})\nDate: ${new Date(message.created_at).toLocaleString()}\n\n${message.message}`);
    window.location.href = `mailto:${message.email}?subject=${subject}&body=${body}`;
}

// Change page for pagination
function changePage(section, direction) {
    const totalItems = filteredMessages.length;
    const totalPages = Math.ceil(totalItems / itemsPerPage);
    
    currentPage[section] += direction;
    
    if (currentPage[section] < 1) {
        currentPage[section] = 1;
    } else if (currentPage[section] > totalPages) {
        currentPage[section] = totalPages;
    }
    
    displayMessages();
}

// Update pagination info
function updatePaginationInfo(totalItems) {
    const startIndex = (currentPage.messages - 1) * itemsPerPage;
    const endIndex = Math.min(startIndex + itemsPerPage, totalItems);
    
    document.getElementById('messages-start').textContent = totalItems > 0 ? startIndex + 1 : 0;
    document.getElementById('messages-end').textContent = endIndex;
    document.getElementById('messages-total').textContent = totalItems;
}

// Escape HTML to prevent XSS
function escapeHtml(text) {
    const div = document.createElement('div');
    div.textContent = text;
    return div.innerHTML;
}


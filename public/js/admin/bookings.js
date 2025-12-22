/**
 * Bookings Management JavaScript Module
 * Handles AJAX operations for bookings
 */

// Configuration
const API_BASE_URL = '/api/bookings_API.php';

// Helper functions
const utils = {
    escapeHtml(text) {
        const div = document.createElement('div');
        div.textContent = text;
        return div.innerHTML;
    },

    formatDate(dateString) {
        if (!dateString) return 'N/A';
        const date = new Date(dateString);
        return date.toLocaleDateString('en-US', { 
            year: 'numeric', 
            month: 'short', 
            day: 'numeric' 
        });
    },

    formatDateTime(dateString) {
        if (!dateString) return 'N/A';
        const date = new Date(dateString);
        return date.toLocaleDateString('en-US', { 
            year: 'numeric', 
            month: 'long', 
            day: 'numeric',
            hour: '2-digit',
            minute: '2-digit'
        });
    },

    formatCurrency(amount) {
        return new Intl.NumberFormat('en-US', {
            style: 'currency',
            currency: 'USD'
        }).format(amount || 0);
    }
};

// API Functions
const api = {
    async fetchBookingDetails(id) {
        try {
            const response = await fetch(`${API_BASE_URL}?action=getOne&id=${id}`);
            
            if (!response.ok) {
                throw new Error(`HTTP error! status: ${response.status}`);
            }

            const result = await response.json();
            
            if (!result.success) {
                throw new Error(result.message || 'Failed to load booking details');
            }

            return result.data;
        } catch (error) {
            console.error('API Error:', error);
            throw error;
        }
    },

    async updateBookingStatus(id, status) {
        try {
            const response = await fetch(`${API_BASE_URL}?action=updateStatus`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({
                    id: id,
                    status: status
                })
            });

            const result = await response.json();
            
            if (!result.success) {
                throw new Error(result.message || 'Failed to update status');
            }

            return result;
        } catch (error) {
            console.error('API Error:', error);
            throw error;
        }
    },

    async updatePaymentStatus(id, paymentStatus) {
        try {
            const response = await fetch(`${API_BASE_URL}?action=updatePaymentStatus`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({
                    id: id,
                    payment_status: paymentStatus
                })
            });

            const result = await response.json();
            
            if (!result.success) {
                throw new Error(result.message || 'Failed to update payment status');
            }

            return result;
        } catch (error) {
            console.error('API Error:', error);
            throw error;
        }
    },

    async deleteBooking(id) {
        try {
            const response = await fetch(`${API_BASE_URL}?action=delete&id=${id}`);
            const result = await response.json();
            
            if (!result.success) {
                throw new Error(result.message || 'Failed to delete booking');
            }

            return result;
        } catch (error) {
            console.error('API Error:', error);
            throw error;
        }
    }
};

// Make available globally
window.bookingsAPI = api;
window.bookingsUtils = utils;
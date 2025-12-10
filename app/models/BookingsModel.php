<?php
/**
 * BookingsModel Class
 * Handles all booking-related database operations
 */

class BookingsModel {
    private $db;
    private $table = 'bookings';
    
    public function __construct($db) {
        $this->db = $db;
    }
    
    /**
     * Get all bookings with filtering and pagination
     */
    public function getAllBookings($page = 1, $limit = 10, $filters = []) {
        try {
            $offset = ($page - 1) * $limit;
            
            // Build query
            $query = "SELECT * FROM " . $this->table . " WHERE 1=1";
            
            // Apply filters
            if (!empty($filters['status'])) {
                $query .= " AND status = :status";
            }
            if (!empty($filters['payment_status'])) {
                $query .= " AND payment_status = :payment_status";
            }
            if (!empty($filters['search'])) {
                $query .= " AND (id LIKE :search OR user_id LIKE :search)";
            }
            if (!empty($filters['start_date'])) {
                $query .= " AND created_at >= :start_date";
            }
            if (!empty($filters['end_date'])) {
                $query .= " AND created_at <= :end_date";
            }
            
            // Get total count
            $countQuery = str_replace("SELECT *", "SELECT COUNT(*) as total", $query);
            $stmt = $this->db->prepare($countQuery);
            $this->bindFilters($stmt, $filters);
            $stmt->execute();
            $totalResult = $stmt->fetch(PDO::FETCH_ASSOC);
            $total = $totalResult['total'] ?? 0;
            
            // Add pagination
            $query .= " ORDER BY created_at DESC LIMIT :limit OFFSET :offset";
            
            $stmt = $this->db->prepare($query);
            $stmt->bindValue(':limit', (int)$limit, PDO::PARAM_INT);
            $stmt->bindValue(':offset', (int)$offset, PDO::PARAM_INT);
            $this->bindFilters($stmt, $filters);
            $stmt->execute();
            
            $bookings = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            $pages = ceil($total / $limit);
            
            return [
                'bookings' => $bookings,
                'total' => $total,
                'page' => $page,
                'limit' => $limit,
                'pages' => $pages,
                'has_prev' => $page > 1,
                'has_next' => $page < $pages
            ];
            
        } catch (Exception $e) {
            throw new Exception("Error fetching bookings: " . $e->getMessage());
        }
    }
    
    /**
     * Get booking by ID
     */
    public function getBookingById($id) {
        try {
            $query = "SELECT * FROM " . $this->table . " WHERE id = :id";
            $stmt = $this->db->prepare($query);
            $stmt->bindParam(':id', $id, PDO::PARAM_INT);
            $stmt->execute();
            
            return $stmt->fetch(PDO::FETCH_ASSOC);
            
        } catch (Exception $e) {
            throw new Exception("Error fetching booking: " . $e->getMessage());
        }
    }
    
    /**
     * Get booking statistics
     */
    public function getBookingStats() {
        try {
            $stats = [];
            
            // Total bookings
            $query = "SELECT COUNT(*) as count FROM " . $this->table;
            $stmt = $this->db->prepare($query);
            $stmt->execute();
            $stats['total_bookings'] = $stmt->fetch(PDO::FETCH_ASSOC)['count'];
            
            // Total revenue
            $query = "SELECT SUM(total_amount) as total FROM " . $this->table . " WHERE payment_status = 'completed'";
            $stmt = $this->db->prepare($query);
            $stmt->execute();
            $stats['total_revenue'] = $stmt->fetch(PDO::FETCH_ASSOC)['total'] ?? 0;
            
            // Pending bookings
            $query = "SELECT COUNT(*) as count FROM " . $this->table . " WHERE status = 'pending'";
            $stmt = $this->db->prepare($query);
            $stmt->execute();
            $stats['pending_bookings'] = $stmt->fetch(PDO::FETCH_ASSOC)['count'];
            
            // Completed bookings
            $query = "SELECT COUNT(*) as count FROM " . $this->table . " WHERE status = 'completed'";
            $stmt = $this->db->prepare($query);
            $stmt->execute();
            $stats['completed_bookings'] = $stmt->fetch(PDO::FETCH_ASSOC)['count'];
            
            // Cancelled bookings
            $query = "SELECT COUNT(*) as count FROM " . $this->table . " WHERE status = 'cancelled'";
            $stmt = $this->db->prepare($query);
            $stmt->execute();
            $stats['cancelled_bookings'] = $stmt->fetch(PDO::FETCH_ASSOC)['count'];
            
            return $stats;
            
        } catch (Exception $e) {
            throw new Exception("Error fetching booking stats: " . $e->getMessage());
        }
    }
    
    /**
     * Create a new booking
     */
    public function createBooking($data) {
        try {
            $query = "INSERT INTO " . $this->table . "
                      (user_id, event_id, ticket_count, total_amount, payment_method, status, payment_status, created_at)
                      VALUES
                      (:user_id, :event_id, :ticket_count, :total_amount, :payment_method, :status, :payment_status, NOW())";
            
            $stmt = $this->db->prepare($query);
            
            $status = $data['status'] ?? 'pending';
            $payment_status = $data['payment_status'] ?? 'pending';
            
            $stmt->bindParam(':user_id', $data['user_id']);
            $stmt->bindParam(':event_id', $data['event_id']);
            $stmt->bindParam(':ticket_count', $data['ticket_count']);
            $stmt->bindParam(':total_amount', $data['total_amount']);
            $stmt->bindParam(':payment_method', $data['payment_method']);
            $stmt->bindParam(':status', $status);
            $stmt->bindParam(':payment_status', $payment_status);
            
            if ($stmt->execute()) {
                return $this->db->lastInsertId();
            }
            return false;
            
        } catch (Exception $e) {
            throw new Exception("Error creating booking: " . $e->getMessage());
        }
    }
    
    /**
     * Update booking status
     */
    public function updateBookingStatus($id, $status) {
        try {
            $query = "UPDATE " . $this->table . " SET status = :status, updated_at = NOW() WHERE id = :id";
            $stmt = $this->db->prepare($query);
            
            $stmt->bindParam(':status', $status);
            $stmt->bindParam(':id', $id, PDO::PARAM_INT);
            
            return $stmt->execute();
            
        } catch (Exception $e) {
            throw new Exception("Error updating booking status: " . $e->getMessage());
        }
    }
    
    /**
     * Update payment status
     */
    public function updatePaymentStatus($id, $payment_status) {
        try {
            $query = "UPDATE " . $this->table . " SET payment_status = :payment_status, updated_at = NOW() WHERE id = :id";
            $stmt = $this->db->prepare($query);
            
            $stmt->bindParam(':payment_status', $payment_status);
            $stmt->bindParam(':id', $id, PDO::PARAM_INT);
            
            return $stmt->execute();
            
        } catch (Exception $e) {
            throw new Exception("Error updating payment status: " . $e->getMessage());
        }
    }
    
    /**
     * Process refund
     */
    public function processRefund($id, $amount, $reason = '') {
        try {
            $query = "INSERT INTO refunds (booking_id, amount, reason, status, created_at)
                      VALUES (:booking_id, :amount, :reason, 'pending', NOW())";
            
            $stmt = $this->db->prepare($query);
            $stmt->bindParam(':booking_id', $id);
            $stmt->bindParam(':amount', $amount);
            $stmt->bindParam(':reason', $reason);
            
            return $stmt->execute();
            
        } catch (Exception $e) {
            throw new Exception("Error processing refund: " . $e->getMessage());
        }
    }
    
    /**
     * Delete booking
     */
    public function deleteBooking($id) {
        try {
            $query = "DELETE FROM " . $this->table . " WHERE id = :id";
            $stmt = $this->db->prepare($query);
            $stmt->bindParam(':id', $id, PDO::PARAM_INT);
            
            return $stmt->execute();
            
        } catch (Exception $e) {
            throw new Exception("Error deleting booking: " . $e->getMessage());
        }
    }
    
    /**
     * Get recent bookings
     */
    public function getRecentBookings($limit = 10) {
        try {
            $query = "SELECT * FROM " . $this->table . " ORDER BY created_at DESC LIMIT :limit";
            $stmt = $this->db->prepare($query);
            $stmt->bindValue(':limit', (int)$limit, PDO::PARAM_INT);
            $stmt->execute();
            
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
            
        } catch (Exception $e) {
            throw new Exception("Error fetching recent bookings: " . $e->getMessage());
        }
    }
    
    /**
     * Get bookings by user
     */
    public function getBookingsByUser($userId, $limit = 10) {
        try {
            $query = "SELECT * FROM " . $this->table . " WHERE user_id = :user_id ORDER BY created_at DESC LIMIT :limit";
            $stmt = $this->db->prepare($query);
            $stmt->bindParam(':user_id', $userId, PDO::PARAM_INT);
            $stmt->bindValue(':limit', (int)$limit, PDO::PARAM_INT);
            $stmt->execute();
            
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
            
        } catch (Exception $e) {
            throw new Exception("Error fetching user bookings: " . $e->getMessage());
        }
    }
    
    /**
     * Get bookings by event
     */
    public function getBookingsByEvent($eventId, $limit = 10) {
        try {
            $query = "SELECT * FROM " . $this->table . " WHERE event_id = :event_id ORDER BY created_at DESC LIMIT :limit";
            $stmt = $this->db->prepare($query);
            $stmt->bindParam(':event_id', $eventId, PDO::PARAM_INT);
            $stmt->bindValue(':limit', (int)$limit, PDO::PARAM_INT);
            $stmt->execute();
            
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
            
        } catch (Exception $e) {
            throw new Exception("Error fetching event bookings: " . $e->getMessage());
        }
    }
    
    /**
     * Get bookings by date range
     */
    public function getBookingsByDateRange($startDate, $endDate) {
        try {
            $query = "SELECT * FROM " . $this->table . " 
                      WHERE DATE(created_at) >= :start_date AND DATE(created_at) <= :end_date
                      ORDER BY created_at DESC";
            
            $stmt = $this->db->prepare($query);
            $stmt->bindParam(':start_date', $startDate);
            $stmt->bindParam(':end_date', $endDate);
            $stmt->execute();
            
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
            
        } catch (Exception $e) {
            throw new Exception("Error fetching bookings by date range: " . $e->getMessage());
        }
    }
    
    /**
     * Get dashboard summary
     */
    public function getDashboardSummary() {
        try {
            $summary = [];
            
            // Stats
            $summary['stats'] = $this->getBookingStats();
            
            // Recent bookings
            $summary['recent_bookings'] = $this->getRecentBookings(5);
            
            return $summary;
            
        } catch (Exception $e) {
            throw new Exception("Error fetching dashboard summary: " . $e->getMessage());
        }
    }
    
    /**
     * Helper function to bind filters
     */
    private function bindFilters(&$stmt, $filters) {
        if (!empty($filters['status'])) {
            $stmt->bindValue(':status', $filters['status']);
        }
        if (!empty($filters['payment_status'])) {
            $stmt->bindValue(':payment_status', $filters['payment_status']);
        }
        if (!empty($filters['search'])) {
            $search = '%' . $filters['search'] . '%';
            $stmt->bindValue(':search', $search);
        }
        if (!empty($filters['start_date'])) {
            $stmt->bindValue(':start_date', $filters['start_date']);
        }
        if (!empty($filters['end_date'])) {
            $stmt->bindValue(':end_date', $filters['end_date']);
        }
    }
}
?>

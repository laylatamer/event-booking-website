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
            
            // Build query with JOINs
            $query = "SELECT 
                        b.*,
                        u.first_name,
                        u.last_name,
                        u.email as user_email,
                        u.phone_number as user_phone,
                        e.title as event_title,
                        e.price as ticket_price,
                        v.name as venue_name,
                        v.address as venue_address,
                        v.city as venue_city,
                        v.country as venue_country,
                        sc.name as subcategory_name,
                        mc.name as main_category_name
                      FROM " . $this->table . " b
                      LEFT JOIN users u ON b.user_id = u.id
                      LEFT JOIN events e ON b.event_id = e.id
                      LEFT JOIN venues v ON e.venue_id = v.id
                      LEFT JOIN subcategories sc ON e.subcategory_id = sc.id
                      LEFT JOIN main_categories mc ON sc.main_category_id = mc.id
                      WHERE 1=1";
            
            // Apply filters
            if (!empty($filters['status'])) {
                $query .= " AND b.status = :status";
            }
            if (!empty($filters['payment_status'])) {
                $query .= " AND b.payment_status = :payment_status";
            }
            if (!empty($filters['search'])) {
                $query .= " AND (b.booking_code LIKE :search 
                           OR u.email LIKE :search 
                           OR u.first_name LIKE :search 
                           OR u.last_name LIKE :search 
                           OR e.title LIKE :search)";
            }
            if (!empty($filters['start_date'])) {
                $query .= " AND b.created_at >= :start_date";
            }
            if (!empty($filters['end_date'])) {
                $query .= " AND b.created_at <= :end_date";
            }
            
            // Get total count
            $countQuery = "SELECT COUNT(*) as total FROM (" . str_replace("SELECT b.*, u.first_name, u.last_name, u.email as user_email, u.phone_number as user_phone, e.title as event_title, e.price as ticket_price, v.name as venue_name, v.address as venue_address, v.city as venue_city, v.country as venue_country, sc.name as subcategory_name, mc.name as main_category_name", "SELECT b.id", $query) . ") as total";
            $stmt = $this->db->prepare($countQuery);
            $this->bindFilters($stmt, $filters);
            $stmt->execute();
            $totalResult = $stmt->fetch(PDO::FETCH_ASSOC);
            $total = $totalResult['total'] ?? 0;
            
            // Add pagination and sorting
            $query .= " ORDER BY b.created_at DESC LIMIT :limit OFFSET :offset";
            
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
            $query = "SELECT 
                        b.*,
                        u.first_name,
                        u.last_name,
                        CONCAT(u.first_name, ' ', u.last_name) as user_name,
                        u.email as user_email,
                        u.phone_number as user_phone,
                        u.address as user_address,
                        u.city as user_city,
                        e.title as event_title,
                        e.description as event_description,
                        e.price as ticket_price,
                        e.date as event_date,
                        v.name as venue_name,
                        v.address as venue_address,
                        v.city as venue_city,
                        v.country as venue_country,
                        sc.name as subcategory_name,
                        mc.name as main_category_name
                      FROM " . $this->table . " b
                      LEFT JOIN users u ON b.user_id = u.id
                      LEFT JOIN events e ON b.event_id = e.id
                      LEFT JOIN venues v ON e.venue_id = v.id
                      LEFT JOIN subcategories sc ON e.subcategory_id = sc.id
                      LEFT JOIN main_categories mc ON sc.main_category_id = mc.id
                      WHERE b.id = :id";
            
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
            
            // Total revenue (using final_amount which is after discounts/taxes)
            $query = "SELECT SUM(final_amount) as total FROM " . $this->table . " WHERE payment_status = 'paid'";
            $stmt = $this->db->prepare($query);
            $stmt->execute();
            $stats['total_revenue'] = $stmt->fetch(PDO::FETCH_ASSOC)['total'] ?? 0;
            
            // Pending bookings
            $query = "SELECT COUNT(*) as count FROM " . $this->table . " WHERE status = 'pending'";
            $stmt = $this->db->prepare($query);
            $stmt->execute();
            $stats['pending_bookings'] = $stmt->fetch(PDO::FETCH_ASSOC)['count'];
            
            // Confirmed bookings
            $query = "SELECT COUNT(*) as count FROM " . $this->table . " WHERE status = 'confirmed'";
            $stmt = $this->db->prepare($query);
            $stmt->execute();
            $stats['confirmed_bookings'] = $stmt->fetch(PDO::FETCH_ASSOC)['count'];
            
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
            
            // Paid bookings
            $query = "SELECT COUNT(*) as count FROM " . $this->table . " WHERE payment_status = 'paid'";
            $stmt = $this->db->prepare($query);
            $stmt->execute();
            $stats['paid_bookings'] = $stmt->fetch(PDO::FETCH_ASSOC)['count'];
            
            // Refunded bookings
            $query = "SELECT COUNT(*) as count FROM " . $this->table . " WHERE payment_status = 'refunded'";
            $stmt = $this->db->prepare($query);
            $stmt->execute();
            $stats['refunded_bookings'] = $stmt->fetch(PDO::FETCH_ASSOC)['count'];
            
            // Failed payments
            $query = "SELECT COUNT(*) as count FROM " . $this->table . " WHERE payment_status = 'failed'";
            $stmt = $this->db->prepare($query);
            $stmt->execute();
            $stats['failed_payments'] = $stmt->fetch(PDO::FETCH_ASSOC)['count'];
            
            return $stats;
            
        } catch (Exception $e) {
            throw new Exception("Error fetching booking stats: " . $e->getMessage());
        }
    }
    
    /**
     * Get bookings count for the last 7 days
     */
    public function getBookingsLast7Days() {
        try {
            $query = "SELECT DATE(created_at) as date, COUNT(*) as count 
                      FROM " . $this->table . " 
                      WHERE created_at >= DATE_SUB(CURDATE(), INTERVAL 6 DAY)
                      GROUP BY DATE(created_at)
                      ORDER BY date ASC";
            
            $stmt = $this->db->prepare($query);
            $stmt->execute();
            
            $results = $stmt->fetchAll(PDO::FETCH_KEY_PAIR);
            
            // Fill in missing days with 0
            $data = [];
            for ($i = 6; $i >= 0; $i--) {
                $date = date('Y-m-d', strtotime("-$i days"));
                $data[$date] = $results[$date] ?? 0;
            }
            
            return $data;
        } catch (Exception $e) {
            return [];
        }
    }

    /**
     * Get revenue by main category
     */
    public function getRevenueByCategory() {
        try {
            $query = "SELECT mc.name, SUM(b.final_amount) as revenue
                      FROM " . $this->table . " b
                      JOIN events e ON b.event_id = e.id
                      JOIN subcategories sc ON e.subcategory_id = sc.id
                      JOIN main_categories mc ON sc.main_category_id = mc.id
                      WHERE b.payment_status = 'paid'
                      GROUP BY mc.id
                      ORDER BY revenue DESC";
            
            $stmt = $this->db->prepare($query);
            $stmt->execute();
            
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (Exception $e) {
            return [];
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
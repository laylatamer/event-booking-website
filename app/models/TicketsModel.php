<?php
/**
 * Tickets Model
 * Handles all database operations related to tickets
 */

class TicketsModel {
    private $db;
    private $table = 'tickets';

    public function __construct(PDO $pdo) {
        $this->db = $pdo;
    }

    /**
     * Get all tickets with pagination and filtering
     * 
     * @param int $page Current page number
     * @param int $limit Items per page
     * @param array $filters Array of filters
     * @return array Ticket data with pagination info
     */
    public function getAllTickets($page = 1, $limit = 10, $filters = []) {
        try {
            $offset = ($page - 1) * $limit;
            
            // Build WHERE clause
            $whereClauses = ['1=1'];
            $params = [];
            
            // Event filter
            if (!empty($filters['event_id'])) {
                $whereClauses[] = "t.event_id = :event_id";
                $params[':event_id'] = $filters['event_id'];
            }
            
            // Status filter
            if (!empty($filters['status'])) {
                $whereClauses[] = "t.status = :status";
                $params[':status'] = $filters['status'];
            }
            
            // Type filter
            if (!empty($filters['type'])) {
                $whereClauses[] = "t.type LIKE :type";
                $params[':type'] = "%{$filters['type']}%";
            }
            
            // Price range filters
            if (!empty($filters['min_price'])) {
                $whereClauses[] = "t.price >= :min_price";
                $params[':min_price'] = $filters['min_price'];
            }
            
            if (!empty($filters['max_price'])) {
                $whereClauses[] = "t.price <= :max_price";
                $params[':max_price'] = $filters['max_price'];
            }
            
            // Search filter
            if (!empty($filters['search'])) {
                $searchTerm = "%{$filters['search']}%";
                $whereClauses[] = "(t.name LIKE :search OR 
                                  t.description LIKE :search OR
                                  e.title LIKE :search)";
                $params[':search'] = $searchTerm;
            }
            
            $whereSQL = implode(' AND ', $whereClauses);
            
            // Get total count for pagination
            $countQuery = "SELECT COUNT(*) as total 
                          FROM {$this->table} t
                          LEFT JOIN events e ON t.event_id = e.id
                          WHERE {$whereSQL}";
            
            $countStmt = $this->db->prepare($countQuery);
            $countStmt->execute($params);
            $totalResult = $countStmt->fetch(PDO::FETCH_ASSOC);
            $totalTickets = $totalResult['total'] ?? 0;
            
            // Main query to get ticket data
            $query = "SELECT 
                t.id,
                t.event_id,
                t.name,
                t.type,
                t.description,
                t.price,
                t.discounted_price,
                t.currency,
                t.quantity_total,
                t.quantity_available,
                t.quantity_sold,
                t.min_per_order,
                t.max_per_order,
                t.sales_start_date,
                t.sales_end_date,
                t.status,
                t.features,
                t.created_at,
                t.updated_at,
                
                -- Event information
                e.title as event_title,
                e.date as event_date,
                e.end_date as event_end_date,
                e.image_url as event_image,
                e.status as event_status,
                
                -- Venue information
                v.name as venue_name,
                v.city as venue_city,
                
                -- Category information
                s.name as subcategory_name,
                mc.name as main_category_name
                
            FROM {$this->table} t
            
            -- Join with events table
            LEFT JOIN events e ON t.event_id = e.id
            
            -- Join with venues table
            LEFT JOIN venues v ON e.venue_id = v.id
            
            -- Join with categories tables
            LEFT JOIN subcategories s ON e.subcategory_id = s.id
            LEFT JOIN main_categories mc ON s.main_category_id = mc.id
            
            WHERE {$whereSQL}
            
            ORDER BY 
                CASE t.status 
                    WHEN 'active' THEN 1
                    WHEN 'inactive' THEN 2
                    WHEN 'sold_out' THEN 3
                    ELSE 4
                END,
                t.created_at DESC
            LIMIT :limit OFFSET :offset";
            
            $stmt = $this->db->prepare($query);
            
            // Bind parameters
            foreach ($params as $key => $value) {
                $stmt->bindValue($key, $value);
            }
            
            // Bind limit and offset
            $stmt->bindValue(':limit', (int)$limit, PDO::PARAM_INT);
            $stmt->bindValue(':offset', (int)$offset, PDO::PARAM_INT);
            
            $stmt->execute();
            $tickets = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            // Format data
            $tickets = array_map([$this, 'formatTicketData'], $tickets);
            
            return [
                'tickets' => $tickets,
                'total' => $totalTickets,
                'page' => $page,
                'limit' => $limit,
                'pages' => ceil($totalTickets / $limit),
                'has_prev' => $page > 1,
                'has_next' => $page < ceil($totalTickets / $limit)
            ];
            
        } catch (PDOException $e) {
            error_log("TicketsModel Error (getAllTickets): " . $e->getMessage());
            throw new Exception("Failed to fetch tickets: " . $e->getMessage());
        }
    }

    /**
     * Get single ticket by ID
     * 
     * @param int $id Ticket ID
     * @return array|null Ticket data or null if not found
     */
    public function getTicketById($id) {
        try {
            $query = "SELECT 
                t.*,
                
                -- Event information
                e.title as event_title,
                e.description as event_description,
                e.date as event_date,
                e.end_date as event_end_date,
                e.image_url as event_image,
                e.gallery_images as event_gallery,
                e.total_tickets as event_total_tickets,
                e.available_tickets as event_available_tickets,
                e.status as event_status,
                
                -- Venue information
                v.name as venue_name,
                v.address as venue_address,
                v.city as venue_city,
                v.country as venue_country,
                v.capacity as venue_capacity,
                v.description as venue_description,
                v.facilities as venue_facilities,
                v.google_maps_url as venue_google_maps,
                v.image_url as venue_image,
                v.status as venue_status,
                
                -- Category information
                s.name as subcategory_name,
                mc.name as main_category_name
                
            FROM {$this->table} t
            
            LEFT JOIN events e ON t.event_id = e.id
            LEFT JOIN venues v ON e.venue_id = v.id
            LEFT JOIN subcategories s ON e.subcategory_id = s.id
            LEFT JOIN main_categories mc ON s.main_category_id = mc.id
            
            WHERE t.id = :id";
            
            $stmt = $this->db->prepare($query);
            $stmt->bindParam(':id', $id, PDO::PARAM_INT);
            $stmt->execute();
            
            $ticket = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if (!$ticket) {
                return null;
            }
            
            // Decode JSON fields
            if (!empty($ticket['features'])) {
                $ticket['features'] = json_decode($ticket['features'], true);
            }
            
            if (!empty($ticket['event_gallery'])) {
                $ticket['event_gallery'] = json_decode($ticket['event_gallery'], true);
            }
            
            if (!empty($ticket['venue_facilities'])) {
                $ticket['venue_facilities'] = json_decode($ticket['venue_facilities'], true);
            }
            
            // Format the ticket data
            return $this->formatTicketData($ticket);
            
        } catch (PDOException $e) {
            error_log("TicketsModel Error (getTicketById): " . $e->getMessage());
            throw new Exception("Failed to fetch ticket: " . $e->getMessage());
        }
    }

    /**
     * Get tickets by event ID
     * 
     * @param int $eventId Event ID
     * @return array Event tickets
     */
    public function getTicketsByEvent($eventId) {
        try {
            $query = "SELECT t.*
                     FROM {$this->table} t
                     WHERE t.event_id = :event_id
                     AND t.status = 'active'
                     ORDER BY t.price ASC";
            
            $stmt = $this->db->prepare($query);
            $stmt->bindParam(':event_id', $eventId, PDO::PARAM_INT);
            $stmt->execute();
            
            $tickets = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            return array_map([$this, 'formatTicketData'], $tickets);
            
        } catch (PDOException $e) {
            error_log("TicketsModel Error (getTicketsByEvent): " . $e->getMessage());
            throw new Exception("Failed to fetch event tickets: " . $e->getMessage());
        }
    }

    /**
     * Create a new ticket
     * 
     * @param array $data Ticket data
     * @return int|false New ticket ID or false on failure
     */
    public function createTicket($data) {
        try {
            $this->db->beginTransaction();
            
            // Validate required fields
            $required = ['event_id', 'name', 'type', 'price', 'quantity_total'];
            foreach ($required as $field) {
                if (empty($data[$field])) {
                    throw new Exception("Field '{$field}' is required");
                }
            }
            
            // Set defaults
            $data['quantity_available'] = $data['quantity_total'] ?? 0;
            $data['quantity_sold'] = 0;
            $data['status'] = $data['status'] ?? 'active';
            $data['currency'] = $data['currency'] ?? 'USD';
            
            // Encode features if provided
            if (!empty($data['features']) && is_array($data['features'])) {
                $data['features'] = json_encode($data['features']);
            }
            
            $query = "INSERT INTO {$this->table} 
                     (event_id, name, type, description, price, discounted_price, 
                      currency, quantity_total, quantity_available, quantity_sold,
                      min_per_order, max_per_order, sales_start_date, sales_end_date,
                      status, features, created_at)
                     VALUES 
                     (:event_id, :name, :type, :description, :price, :discounted_price,
                      :currency, :quantity_total, :quantity_available, :quantity_sold,
                      :min_per_order, :max_per_order, :sales_start_date, :sales_end_date,
                      :status, :features, NOW())";
            
            $stmt = $this->db->prepare($query);
            
            // Bind parameters
            $stmt->bindParam(':event_id', $data['event_id'], PDO::PARAM_INT);
            $stmt->bindParam(':name', $data['name']);
            $stmt->bindParam(':type', $data['type']);
            $stmt->bindParam(':description', $data['description'] ?? null);
            $stmt->bindParam(':price', $data['price']);
            $stmt->bindParam(':discounted_price', $data['discounted_price'] ?? null);
            $stmt->bindParam(':currency', $data['currency']);
            $stmt->bindParam(':quantity_total', $data['quantity_total'], PDO::PARAM_INT);
            $stmt->bindParam(':quantity_available', $data['quantity_available'], PDO::PARAM_INT);
            $stmt->bindParam(':quantity_sold', $data['quantity_sold'], PDO::PARAM_INT);
            $stmt->bindParam(':min_per_order', $data['min_per_order'] ?? 1, PDO::PARAM_INT);
            $stmt->bindParam(':max_per_order', $data['max_per_order'] ?? 10, PDO::PARAM_INT);
            $stmt->bindParam(':sales_start_date', $data['sales_start_date'] ?? null);
            $stmt->bindParam(':sales_end_date', $data['sales_end_date'] ?? null);
            $stmt->bindParam(':status', $data['status']);
            $stmt->bindParam(':features', $data['features'] ?? null);
            
            if ($stmt->execute()) {
                $ticketId = $this->db->lastInsertId();
                
                // Update event total tickets
                $this->updateEventTickets($data['event_id']);
                
                $this->db->commit();
                return $ticketId;
            }
            
            $this->db->rollBack();
            return false;
            
        } catch (PDOException $e) {
            $this->db->rollBack();
            error_log("TicketsModel Error (createTicket): " . $e->getMessage());
            throw new Exception("Failed to create ticket: " . $e->getMessage());
        }
    }

    /**
     * Update ticket
     * 
     * @param int $id Ticket ID
     * @param array $data Ticket data
     * @return bool Success status
     */
    public function updateTicket($id, $data) {
        try {
            $this->db->beginTransaction();
            
            // Get current ticket
            $currentTicket = $this->getTicketById($id);
            if (!$currentTicket) {
                throw new Exception("Ticket not found");
            }
            
            // Prepare update fields
            $updateFields = [];
            $params = [':id' => $id];
            
            $allowedFields = [
                'name', 'type', 'description', 'price', 'discounted_price',
                'currency', 'quantity_total', 'min_per_order', 'max_per_order',
                'sales_start_date', 'sales_end_date', 'status', 'features'
            ];
            
            foreach ($allowedFields as $field) {
                if (array_key_exists($field, $data)) {
                    $updateFields[] = "{$field} = :{$field}";
                    $params[":{$field}"] = $data[$field];
                }
            }
            
            // Handle quantity_total update
            if (isset($data['quantity_total'])) {
                $newQuantityTotal = (int)$data['quantity_total'];
                $currentQuantitySold = (int)$currentTicket['quantity_sold'];
                
                if ($newQuantityTotal < $currentQuantitySold) {
                    throw new Exception("Cannot set total quantity below sold quantity ({$currentQuantitySold})");
                }
                
                // Calculate new available quantity
                $newQuantityAvailable = $newQuantityTotal - $currentQuantitySold;
                $updateFields[] = "quantity_available = :quantity_available";
                $params[':quantity_available'] = $newQuantityAvailable;
            }
            
            if (empty($updateFields)) {
                throw new Exception("No fields to update");
            }
            
            $updateFields[] = "updated_at = NOW()";
            
            $query = "UPDATE {$this->table} 
                     SET " . implode(', ', $updateFields) . "
                     WHERE id = :id";
            
            $stmt = $this->db->prepare($query);
            
            // Bind all parameters
            foreach ($params as $key => $value) {
                if ($key === ':features' && is_array($value)) {
                    $value = json_encode($value);
                }
                $stmt->bindValue($key, $value);
            }
            
            $success = $stmt->execute();
            
            if ($success && isset($data['quantity_total'])) {
                // Update event total tickets
                $this->updateEventTickets($currentTicket['event_id']);
            }
            
            $this->db->commit();
            return $success;
            
        } catch (PDOException $e) {
            $this->db->rollBack();
            error_log("TicketsModel Error (updateTicket): " . $e->getMessage());
            throw new Exception("Failed to update ticket: " . $e->getMessage());
        }
    }

    /**
     * Update ticket status
     * 
     * @param int $id Ticket ID
     * @param string $status New status
     * @return bool Success status
     */
    public function updateTicketStatus($id, $status) {
        try {
            $allowedStatuses = ['active', 'inactive', 'sold_out'];
            
            if (!in_array($status, $allowedStatuses)) {
                throw new Exception("Invalid status: {$status}");
            }
            
            $query = "UPDATE {$this->table} 
                     SET status = :status, updated_at = NOW()
                     WHERE id = :id";
            
            $stmt = $this->db->prepare($query);
            $stmt->bindParam(':status', $status);
            $stmt->bindParam(':id', $id, PDO::PARAM_INT);
            
            return $stmt->execute();
            
        } catch (PDOException $e) {
            error_log("TicketsModel Error (updateTicketStatus): " . $e->getMessage());
            throw new Exception("Failed to update ticket status: " . $e->getMessage());
        }
    }

    /**
     * Update ticket quantities (when tickets are sold)
     * 
     * @param int $id Ticket ID
     * @param int $quantity Quantity sold
     * @return bool Success status
     */
    public function updateTicketQuantities($id, $quantity) {
        try {
            $this->db->beginTransaction();
            
            $query = "UPDATE {$this->table} 
                     SET quantity_sold = quantity_sold + :quantity,
                         quantity_available = quantity_available - :quantity,
                         updated_at = NOW()
                     WHERE id = :id 
                     AND quantity_available >= :quantity";
            
            $stmt = $this->db->prepare($query);
            $stmt->bindParam(':id', $id, PDO::PARAM_INT);
            $stmt->bindParam(':quantity', $quantity, PDO::PARAM_INT);
            
            if (!$stmt->execute()) {
                $this->db->rollBack();
                return false;
            }
            
            // Check if ticket is now sold out
            $ticket = $this->getTicketById($id);
            if ($ticket && $ticket['quantity_available'] <= 0) {
                $this->updateTicketStatus($id, 'sold_out');
            }
            
            $this->db->commit();
            return true;
            
        } catch (PDOException $e) {
            $this->db->rollBack();
            error_log("TicketsModel Error (updateTicketQuantities): " . $e->getMessage());
            throw new Exception("Failed to update ticket quantities: " . $e->getMessage());
        }
    }

    /**
     * Delete ticket
     * 
     * @param int $id Ticket ID
     * @return bool Success status
     */
    public function deleteTicket($id) {
        try {
            $this->db->beginTransaction();
            
            // Get ticket info before deletion
            $ticket = $this->getTicketById($id);
            
            if (!$ticket) {
                throw new Exception("Ticket not found");
            }
            
            // Check if tickets have been sold
            if ($ticket['quantity_sold'] > 0) {
                throw new Exception("Cannot delete ticket with sold quantities");
            }
            
            // Delete the ticket
            $query = "DELETE FROM {$this->table} WHERE id = :id";
            $stmt = $this->db->prepare($query);
            $stmt->bindParam(':id', $id, PDO::PARAM_INT);
            
            if (!$stmt->execute()) {
                $this->db->rollBack();
                return false;
            }
            
            // Update event total tickets
            $this->updateEventTickets($ticket['event_id']);
            
            $this->db->commit();
            return true;
            
        } catch (PDOException $e) {
            $this->db->rollBack();
            error_log("TicketsModel Error (deleteTicket): " . $e->getMessage());
            throw new Exception("Failed to delete ticket: " . $e->getMessage());
        }
    }

    /**
     * Get ticket statistics
     * 
     * @return array Ticket statistics
     */
    public function getTicketStats() {
        try {
            $stats = [];
            
            // Total tickets
            $query = "SELECT COUNT(*) as total FROM {$this->table}";
            $stmt = $this->db->query($query);
            $stats['total_tickets'] = $stmt->fetch(PDO::FETCH_ASSOC)['total'];
            
            // Status counts
            $query = "SELECT 
                SUM(CASE WHEN status = 'active' THEN 1 ELSE 0 END) as active,
                SUM(CASE WHEN status = 'inactive' THEN 1 ELSE 0 END) as inactive,
                SUM(CASE WHEN status = 'sold_out' THEN 1 ELSE 0 END) as sold_out
                FROM {$this->table}";
            
            $stmt = $this->db->query($query);
            $statusStats = $stmt->fetch(PDO::FETCH_ASSOC);
            
            $stats['active_tickets'] = $statusStats['active'] ?? 0;
            $stats['inactive_tickets'] = $statusStats['inactive'] ?? 0;
            $stats['sold_out_tickets'] = $statusStats['sold_out'] ?? 0;
            
            // Quantity stats
            $query = "SELECT 
                SUM(quantity_total) as total_quantity,
                SUM(quantity_available) as available_quantity,
                SUM(quantity_sold) as sold_quantity,
                AVG(price) as average_price,
                MIN(price) as min_price,
                MAX(price) as max_price
                FROM {$this->table} 
                WHERE status = 'active'";
            
            $stmt = $this->db->query($query);
            $quantityStats = $stmt->fetch(PDO::FETCH_ASSOC);
            
            $stats['total_quantity'] = $quantityStats['total_quantity'] ?? 0;
            $stats['available_quantity'] = $quantityStats['available_quantity'] ?? 0;
            $stats['sold_quantity'] = $quantityStats['sold_quantity'] ?? 0;
            $stats['average_price'] = $quantityStats['average_price'] ?? 0;
            $stats['min_price'] = $quantityStats['min_price'] ?? 0;
            $stats['max_price'] = $quantityStats['max_price'] ?? 0;
            
            // Revenue projection
            $query = "SELECT SUM(quantity_available * price) as potential_revenue
                     FROM {$this->table} 
                     WHERE status = 'active'";
            
            $stmt = $this->db->query($query);
            $revenueStats = $stmt->fetch(PDO::FETCH_ASSOC);
            
            $stats['potential_revenue'] = $revenueStats['potential_revenue'] ?? 0;
            
            // Today's new tickets
            $query = "SELECT COUNT(*) as today_tickets
                     FROM {$this->table} 
                     WHERE DATE(created_at) = CURDATE()";
            
            $stmt = $this->db->query($query);
            $todayStats = $stmt->fetch(PDO::FETCH_ASSOC);
            
            $stats['today_tickets'] = $todayStats['today_tickets'] ?? 0;
            
            // Tickets by type
            $query = "SELECT type, COUNT(*) as count
                     FROM {$this->table} 
                     GROUP BY type
                     ORDER BY count DESC";
            
            $stmt = $this->db->query($query);
            $stats['tickets_by_type'] = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            return $stats;
            
        } catch (PDOException $e) {
            error_log("TicketsModel Error (getTicketStats): " . $e->getMessage());
            throw new Exception("Failed to get ticket statistics: " . $e->getMessage());
        }
    }

    /**
     * Get tickets by event for dropdown
     * 
     * @param int $eventId Event ID
     * @return array Tickets for dropdown
     */
    public function getTicketsForDropdown($eventId = null) {
        try {
            $whereClause = "";
            $params = [];
            
            if ($eventId) {
                $whereClause = "WHERE t.event_id = :event_id AND t.status = 'active'";
                $params[':event_id'] = $eventId;
            } else {
                $whereClause = "WHERE t.status = 'active'";
            }
            
            $query = "SELECT 
                t.id,
                t.name,
                t.type,
                t.price,
                t.quantity_available,
                e.title as event_title
            FROM {$this->table} t
            LEFT JOIN events e ON t.event_id = e.id
            {$whereClause}
            ORDER BY e.title, t.name";
            
            $stmt = $this->db->prepare($query);
            
            foreach ($params as $key => $value) {
                $stmt->bindValue($key, $value);
            }
            
            $stmt->execute();
            $tickets = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            // Format for dropdown
            return array_map(function($ticket) {
                $available = $ticket['quantity_available'] > 0 ? "({$ticket['quantity_available']} available)" : "(Sold out)";
                return [
                    'id' => $ticket['id'],
                    'text' => "{$ticket['event_title']} - {$ticket['name']} ({$ticket['type']}) - \${$ticket['price']} {$available}"
                ];
            }, $tickets);
            
        } catch (PDOException $e) {
            error_log("TicketsModel Error (getTicketsForDropdown): " . $e->getMessage());
            throw new Exception("Failed to fetch tickets for dropdown: " . $e->getMessage());
        }
    }

    /**
     * Check ticket availability
     * 
     * @param int $ticketId Ticket ID
     * @param int $quantity Desired quantity
     * @return bool True if available
     */
    public function checkAvailability($ticketId, $quantity = 1) {
        try {
            $query = "SELECT 
                quantity_available,
                status,
                sales_start_date,
                sales_end_date
            FROM {$this->table}
            WHERE id = :id";
            
            $stmt = $this->db->prepare($query);
            $stmt->bindParam(':id', $ticketId, PDO::PARAM_INT);
            $stmt->execute();
            
            $ticket = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if (!$ticket) {
                return false;
            }
            
            // Check status
            if ($ticket['status'] !== 'active') {
                return false;
            }
            
            // Check quantity
            if ($ticket['quantity_available'] < $quantity) {
                return false;
            }
            
            // Check sales dates
            $now = date('Y-m-d H:i:s');
            if ($ticket['sales_start_date'] && $ticket['sales_start_date'] > $now) {
                return false;
            }
            
            if ($ticket['sales_end_date'] && $ticket['sales_end_date'] < $now) {
                return false;
            }
            
            return true;
            
        } catch (PDOException $e) {
            error_log("TicketsModel Error (checkAvailability): " . $e->getMessage());
            throw new Exception("Failed to check ticket availability: " . $e->getMessage());
        }
    }

    /**
     * Get tickets dashboard summary
     * 
     * @return array Dashboard summary
     */
    public function getDashboardSummary() {
        try {
            // Tickets by status trend (last 30 days)
            $query = "SELECT 
                DATE(created_at) as date,
                SUM(CASE WHEN status = 'active' THEN 1 ELSE 0 END) as active,
                SUM(CASE WHEN status = 'inactive' THEN 1 ELSE 0 END) as inactive,
                SUM(CASE WHEN status = 'sold_out' THEN 1 ELSE 0 END) as sold_out
            FROM {$this->table}
            WHERE created_at >= DATE_SUB(NOW(), INTERVAL 30 DAY)
            GROUP BY DATE(created_at)
            ORDER BY date";
            
            $stmt = $this->db->query($query);
            $trendData = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            // Top events by tickets
            $query = "SELECT 
                e.title as event_title,
                COUNT(t.id) as tickets_count,
                SUM(t.quantity_total) as total_tickets,
                SUM(t.quantity_sold) as sold_tickets
            FROM {$this->table} t
            LEFT JOIN events e ON t.event_id = e.id
            WHERE t.created_at >= DATE_SUB(NOW(), INTERVAL 30 DAY)
            GROUP BY t.event_id
            ORDER BY tickets_count DESC
            LIMIT 5";
            
            $stmt = $this->db->query($query);
            $topEvents = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            // Top selling tickets
            $query = "SELECT 
                t.name as ticket_name,
                t.type as ticket_type,
                e.title as event_title,
                t.quantity_sold as sold_quantity,
                (t.quantity_sold * t.price) as total_revenue
            FROM {$this->table} t
            LEFT JOIN events e ON t.event_id = e.id
            WHERE t.quantity_sold > 0
            ORDER BY t.quantity_sold DESC
            LIMIT 5";
            
            $stmt = $this->db->query($query);
            $topSelling = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            // Low stock tickets (less than 10% available)
            $query = "SELECT 
                t.name as ticket_name,
                e.title as event_title,
                t.quantity_available as available,
                t.quantity_total as total,
                ROUND((t.quantity_available / t.quantity_total) * 100, 2) as percentage_available
            FROM {$this->table} t
            LEFT JOIN events e ON t.event_id = e.id
            WHERE t.status = 'active'
            AND t.quantity_total > 0
            AND (t.quantity_available / t.quantity_total) <= 0.1
            ORDER BY percentage_available ASC
            LIMIT 5";
            
            $stmt = $this->db->query($query);
            $lowStock = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            return [
                'trend_data' => $trendData,
                'top_events' => $topEvents,
                'top_selling' => $topSelling,
                'low_stock' => $lowStock
            ];
            
        } catch (PDOException $e) {
            error_log("TicketsModel Error (getDashboardSummary): " . $e->getMessage());
            throw new Exception("Failed to get dashboard summary: " . $e->getMessage());
        }
    }

    /**
     * Format ticket data for display
     * 
     * @param array $ticket Raw ticket data
     * @return array Formatted ticket data
     */
    private function formatTicketData($ticket) {
        if (!$ticket) return $ticket;
        
        // Format dates
        if (!empty($ticket['created_at'])) {
            $ticket['created_at_formatted'] = date('M d, Y h:i A', strtotime($ticket['created_at']));
        }
        
        if (!empty($ticket['updated_at'])) {
            $ticket['updated_at_formatted'] = date('M d, Y h:i A', strtotime($ticket['updated_at']));
        }
        
        if (!empty($ticket['sales_start_date'])) {
            $ticket['sales_start_date_formatted'] = date('M d, Y', strtotime($ticket['sales_start_date']));
        }
        
        if (!empty($ticket['sales_end_date'])) {
            $ticket['sales_end_date_formatted'] = date('M d, Y', strtotime($ticket['sales_end_date']));
        }
        
        if (!empty($ticket['event_date'])) {
            $ticket['event_date_formatted'] = date('M d, Y h:i A', strtotime($ticket['event_date']));
        }
        
        // Format amounts
        if (isset($ticket['price'])) {
            $ticket['price_formatted'] = number_format($ticket['price'], 2);
        }
        
        if (isset($ticket['discounted_price'])) {
            $ticket['discounted_price_formatted'] = number_format($ticket['discounted_price'], 2);
        }
        
        // Calculate discount percentage
        if (isset($ticket['price']) && isset($ticket['discounted_price']) && $ticket['price'] > 0) {
            $discount = (($ticket['price'] - $ticket['discounted_price']) / $ticket['price']) * 100;
            $ticket['discount_percentage'] = round($discount, 2);
        }
        
        // Calculate availability percentage
        if (isset($ticket['quantity_total']) && $ticket['quantity_total'] > 0) {
            $ticket['availability_percentage'] = ($ticket['quantity_available'] / $ticket['quantity_total']) * 100;
            $ticket['availability_percentage_formatted'] = round($ticket['availability_percentage'], 2);
        }
        
        // Add status colors
        $ticket['status_color'] = $this->getStatusColor($ticket['status'] ?? '');
        
        // Add sale status
        $now = date('Y-m-d H:i:s');
        $ticket['sale_status'] = 'available';
        
        if ($ticket['sales_start_date'] && $ticket['sales_start_date'] > $now) {
            $ticket['sale_status'] = 'upcoming';
        } else if ($ticket['sales_end_date'] && $ticket['sales_end_date'] < $now) {
            $ticket['sale_status'] = 'ended';
        } else if ($ticket['quantity_available'] <= 0) {
            $ticket['sale_status'] = 'sold_out';
        }
        
        $ticket['sale_status_color'] = $this->getSaleStatusColor($ticket['sale_status']);
        
        return $ticket;
    }

    /**
     * Get status color for UI
     * 
     * @param string $status Ticket status
     * @return string Color class
     */
    private function getStatusColor($status) {
        $colors = [
            'active' => 'success',
            'inactive' => 'secondary',
            'sold_out' => 'warning'
        ];
        
        return $colors[$status] ?? 'secondary';
    }

    /**
     * Get sale status color for UI
     * 
     * @param string $status Sale status
     * @return string Color class
     */
    private function getSaleStatusColor($status) {
        $colors = [
            'available' => 'success',
            'upcoming' => 'info',
            'ended' => 'secondary',
            'sold_out' => 'warning'
        ];
        
        return $colors[$status] ?? 'secondary';
    }

    /**
     * Update event total tickets when tickets are added/removed
     * 
     * @param int $eventId Event ID
     * @return bool Success status
     */
    private function updateEventTickets($eventId) {
        try {
            // Calculate total tickets for event
            $query = "SELECT 
                SUM(quantity_total) as total_tickets,
                SUM(quantity_available) as available_tickets
            FROM {$this->table}
            WHERE event_id = :event_id
            AND status = 'active'";
            
            $stmt = $this->db->prepare($query);
            $stmt->bindParam(':event_id', $eventId, PDO::PARAM_INT);
            $stmt->execute();
            
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            
            // Update event
            $updateQuery = "UPDATE events 
                           SET total_tickets = :total_tickets,
                               available_tickets = :available_tickets,
                               updated_at = NOW()
                           WHERE id = :event_id";
            
            $updateStmt = $this->db->prepare($updateQuery);
            $updateStmt->bindParam(':total_tickets', $result['total_tickets'] ?? 0);
            $updateStmt->bindParam(':available_tickets', $result['available_tickets'] ?? 0);
            $updateStmt->bindParam(':event_id', $eventId, PDO::PARAM_INT);
            
            return $updateStmt->execute();
            
        } catch (PDOException $e) {
            error_log("TicketsModel Error (updateEventTickets): " . $e->getMessage());
            throw new Exception("Failed to update event tickets: " . $e->getMessage());
        }
    }
}
?>
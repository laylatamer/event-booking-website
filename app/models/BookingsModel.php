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
     * Create a new booking
     * @param array $data Booking data including user_id, event_id, ticket_categories, booked_seats, etc.
     * @return array Result with success status, booking_id, and booking_code
     */
    public function createBooking($data) {
        try {
            // Check if bookings table exists
            $tableCheck = $this->db->query("SHOW TABLES LIKE 'bookings'");
            if ($tableCheck->rowCount() === 0) {
                throw new Exception("Bookings table does not exist. Please run database migrations.");
            }
            
            $this->db->beginTransaction();
            
            // Generate unique booking code
            $bookingCode = $this->generateBookingCode();
            
            // Calculate totals
            $subtotal = $data['subtotal'] ?? 0;
            $serviceFee = $data['service_fee'] ?? 0;
            $processingFee = $data['processing_fee'] ?? 0;
            $customizationFee = $data['customization_fee'] ?? 0;
            $finalAmount = $subtotal + $serviceFee + $processingFee + $customizationFee;
            
            // Determine status and payment status
            $status = 'confirmed';
            $paymentStatus = ($data['payment_method'] ?? 'cash') === 'cash' ? 'pending' : 'paid';
            
            // Check which columns exist in the table
            try {
                $columnCheck = $this->db->query("SHOW COLUMNS FROM " . $this->table);
                if (!$columnCheck) {
                    $errorInfo = $this->db->errorInfo();
                    throw new Exception("Failed to check table columns: " . ($errorInfo[2] ?? 'Unknown error'));
                }
                $existingColumns = [];
                while ($row = $columnCheck->fetch(PDO::FETCH_ASSOC)) {
                    $existingColumns[] = $row['Field'];
                }
            } catch (PDOException $e) {
                throw new Exception("Database error checking columns: " . $e->getMessage());
            }
            
            // Build query based on existing columns
            $columns = ['user_id', 'event_id', 'booking_code', 'ticket_count'];
            $values = [':user_id', ':event_id', ':booking_code', ':ticket_count'];
            $binds = [];
            
            // Add optional columns if they exist
            if (in_array('subtotal', $existingColumns)) {
                $columns[] = 'subtotal';
                $values[] = ':subtotal';
            }
            if (in_array('service_fee', $existingColumns)) {
                $columns[] = 'service_fee';
                $values[] = ':service_fee';
            }
            if (in_array('processing_fee', $existingColumns)) {
                $columns[] = 'processing_fee';
                $values[] = ':processing_fee';
            }
            if (in_array('customization_fee', $existingColumns)) {
                $columns[] = 'customization_fee';
                $values[] = ':customization_fee';
            }
            
            // Use total_amount if subtotal doesn't exist
            if (in_array('total_amount', $existingColumns) && !in_array('subtotal', $existingColumns)) {
                $columns[] = 'total_amount';
                $values[] = ':total_amount';
            }
            
            $columns[] = 'final_amount';
            $values[] = ':final_amount';
            $columns[] = 'payment_method';
            $values[] = ':payment_method';
            $columns[] = 'payment_status';
            $values[] = ':payment_status';
            $columns[] = 'status';
            $values[] = ':status';
            
            // Add customer fields if they exist
            if (in_array('customer_first_name', $existingColumns)) {
                $columns[] = 'customer_first_name';
                $values[] = ':customer_first_name';
            }
            if (in_array('customer_last_name', $existingColumns)) {
                $columns[] = 'customer_last_name';
                $values[] = ':customer_last_name';
            }
            if (in_array('customer_email', $existingColumns)) {
                $columns[] = 'customer_email';
                $values[] = ':customer_email';
            }
            if (in_array('customer_phone', $existingColumns)) {
                $columns[] = 'customer_phone';
                $values[] = ':customer_phone';
            }
            if (in_array('ticket_details', $existingColumns)) {
                $columns[] = 'ticket_details';
                $values[] = ':ticket_details';
            } elseif (in_array('notes', $existingColumns)) {
                // Fallback to notes column if ticket_details doesn't exist
                $columns[] = 'notes';
                $values[] = ':notes';
            }
            
            // Insert booking
            $query = "INSERT INTO " . $this->table . " (" . implode(', ', $columns) . ", created_at)
                     VALUES (" . implode(', ', $values) . ", NOW())";
            
            $stmt = $this->db->prepare($query);
            
            if (!$stmt) {
                $errorInfo = $this->db->errorInfo();
                throw new Exception("Failed to prepare booking query: " . ($errorInfo[2] ?? 'Unknown error'));
            }
            
            // Bind required values
            $stmt->bindValue(':user_id', $data['user_id'], PDO::PARAM_INT);
            $stmt->bindValue(':event_id', $data['event_id'], PDO::PARAM_INT);
            $stmt->bindValue(':booking_code', $bookingCode);
            $stmt->bindValue(':ticket_count', $data['ticket_count'], PDO::PARAM_INT);
            
            // Bind optional fee columns if they exist
            if (in_array('subtotal', $existingColumns)) {
                $stmt->bindValue(':subtotal', $subtotal);
            }
            if (in_array('service_fee', $existingColumns)) {
                $stmt->bindValue(':service_fee', $serviceFee);
            }
            if (in_array('processing_fee', $existingColumns)) {
                $stmt->bindValue(':processing_fee', $processingFee);
            }
            if (in_array('customization_fee', $existingColumns)) {
                $stmt->bindValue(':customization_fee', $customizationFee);
            }
            
            // Use total_amount if subtotal doesn't exist
            if (in_array('total_amount', $existingColumns) && !in_array('subtotal', $existingColumns)) {
                $stmt->bindValue(':total_amount', $subtotal);
            }
            
            $stmt->bindValue(':final_amount', $finalAmount);
            $stmt->bindValue(':payment_method', $data['payment_method'] ?? 'cash');
            $stmt->bindValue(':payment_status', $paymentStatus);
            $stmt->bindValue(':status', $status);
            
            // Bind customer fields if they exist
            if (in_array('customer_first_name', $existingColumns)) {
                $stmt->bindValue(':customer_first_name', $data['customer_first_name'] ?? '');
            }
            if (in_array('customer_last_name', $existingColumns)) {
                $stmt->bindValue(':customer_last_name', $data['customer_last_name'] ?? '');
            }
            if (in_array('customer_email', $existingColumns)) {
                $stmt->bindValue(':customer_email', $data['customer_email'] ?? '');
            }
            if (in_array('customer_phone', $existingColumns)) {
                $stmt->bindValue(':customer_phone', $data['customer_phone'] ?? null);
            }
            // Store ticket_details including ticket_categories and booked_seats for cancellation purposes
            $ticketDetailsToStore = $data['ticket_details'] ?? [];
            // Ensure ticket_categories and booked_seats are included
            if (!isset($ticketDetailsToStore['ticket_categories']) && !empty($data['ticket_categories'])) {
                $ticketDetailsToStore['ticket_categories'] = $data['ticket_categories'];
            }
            if (!isset($ticketDetailsToStore['booked_seats']) && !empty($data['booked_seats'])) {
                $ticketDetailsToStore['booked_seats'] = $data['booked_seats'];
            }
            
            if (in_array('ticket_details', $existingColumns)) {
                $stmt->bindValue(':ticket_details', json_encode($ticketDetailsToStore));
            } elseif (in_array('notes', $existingColumns)) {
                // Fallback to notes column
                $stmt->bindValue(':notes', json_encode($ticketDetailsToStore));
            }
            
            try {
                $executeResult = $stmt->execute();
                if (!$executeResult) {
                    $errorInfo = $stmt->errorInfo();
                    error_log("Booking insert failed - Error Info: " . print_r($errorInfo, true));
                    error_log("Query: " . $query);
                    error_log("Columns: " . print_r($columns, true));
                    throw new Exception("Failed to execute booking query: " . ($errorInfo[2] ?? 'Unknown error') . " | SQL State: " . ($errorInfo[0] ?? 'Unknown'));
                }
            } catch (PDOException $e) {
                error_log("PDO Exception: " . $e->getMessage() . " | Code: " . $e->getCode());
                error_log("Query: " . $query);
                throw new Exception("PDO error executing booking query: " . $e->getMessage() . " | SQL State: " . $e->getCode());
            }
            
            $bookingId = $this->db->lastInsertId();
            
            if (!$bookingId) {
                // Log the issue but don't fail - check if insert actually happened
                error_log("Warning: lastInsertId returned false, but insert may have succeeded");
                // Try to get the ID another way
                try {
                    $checkStmt = $this->db->query("SELECT LAST_INSERT_ID() as id");
                    if ($checkStmt) {
                        $result = $checkStmt->fetch(PDO::FETCH_ASSOC);
                        $bookingId = $result['id'] ?? null;
                    }
                } catch (Exception $e) {
                    error_log("Could not get last insert ID: " . $e->getMessage());
                }
                
                if (!$bookingId) {
                    throw new Exception("Failed to get booking ID after insert. Check if booking was created.");
                }
            }
            
            // Update ticket availability
            if (!empty($data['ticket_categories'])) {
                foreach ($data['ticket_categories'] as $category) {
                    $categoryName = $category['category_name'] ?? $category['categoryName'] ?? '';
                    $quantity = intval($category['quantity'] ?? 0);
                    
                    if (empty($categoryName) || $quantity <= 0) {
                        error_log("Invalid category data: " . print_r($category, true));
                        continue; // Skip invalid categories
                    }
                    
                    try {
                        
                        $updateQuery = "UPDATE event_ticket_categories 
                                       SET available_tickets = available_tickets - :quantity,
                                           updated_at = NOW()
                                       WHERE event_id = :event_id AND category_name = :category_name
                                       AND available_tickets >= :quantity_check";
                        $updateStmt = $this->db->prepare($updateQuery);
                        
                        // Bind all parameters explicitly
                        $updateStmt->bindValue(':quantity', $quantity, PDO::PARAM_INT);
                        $updateStmt->bindValue(':quantity_check', $quantity, PDO::PARAM_INT);
                        $updateStmt->bindValue(':event_id', $data['event_id'], PDO::PARAM_INT);
                        $updateStmt->bindValue(':category_name', $categoryName, PDO::PARAM_STR);
                        
                        $executeResult = $updateStmt->execute();
                        
                        if (!$executeResult) {
                            $errorInfo = $updateStmt->errorInfo();
                            error_log("Failed to execute update query for category $categoryName. Error: " . print_r($errorInfo, true));
                            throw new Exception("Failed to update ticket availability for category: " . $categoryName . " | Error: " . ($errorInfo[2] ?? 'Unknown error'));
                        }
                        
                        if ($updateStmt->rowCount() === 0) {
                            // Check current availability
                            $checkQuery = "SELECT available_tickets, total_tickets FROM event_ticket_categories 
                                         WHERE event_id = :event_id AND category_name = :category_name";
                            $checkStmt = $this->db->prepare($checkQuery);
                            $checkStmt->bindValue(':event_id', $data['event_id'], PDO::PARAM_INT);
                            $checkStmt->bindValue(':category_name', $categoryName);
                            $checkStmt->execute();
                            $current = $checkStmt->fetch(PDO::FETCH_ASSOC);
                            
                            $available = $current['available_tickets'] ?? 0;
                            throw new Exception("Insufficient tickets available for category '$categoryName'. Requested: $quantity, Available: $available");
                        }
                    } catch (PDOException $e) {
                        error_log("PDO Error updating tickets: " . $e->getMessage());
                        throw new Exception("Database error updating ticket availability: " . $e->getMessage());
                    }
                }
            }
            
            // Save booked seats if provided
            if (!empty($data['booked_seats'])) {
                
                // Check if booked_seats table exists
                $tableCheck = $this->db->query("SHOW TABLES LIKE 'booked_seats'");
                if ($tableCheck->rowCount() > 0) {
                    $seatsSaved = 0;
                    $seatsFailed = 0;
                    
                    foreach ($data['booked_seats'] as $seat) {
                        try {
                            // Validate seat data
                            if (empty($seat['seat_id']) || empty($seat['category_name'])) {
                                error_log("Invalid seat data: " . print_r($seat, true));
                                $seatsFailed++;
                                continue;
                            }
                            
                            $seatQuery = "INSERT INTO booked_seats (booking_id, event_id, seat_id, category_name)
                                         VALUES (:booking_id, :event_id, :seat_id, :category_name)
                                         ON DUPLICATE KEY UPDATE booking_id = :booking_id2";
                            $seatStmt = $this->db->prepare($seatQuery);
                            $seatStmt->bindValue(':booking_id', $bookingId, PDO::PARAM_INT);
                            $seatStmt->bindValue(':booking_id2', $bookingId, PDO::PARAM_INT);
                            $seatStmt->bindValue(':event_id', $data['event_id'], PDO::PARAM_INT);
                            $seatStmt->bindValue(':seat_id', $seat['seat_id'], PDO::PARAM_STR);
                            $seatStmt->bindValue(':category_name', $seat['category_name'], PDO::PARAM_STR);
                            
                            if ($seatStmt->execute()) {
                                $seatsSaved++;
                            } else {
                                $errorInfo = $seatStmt->errorInfo();
                                error_log("Failed to execute seat insert for " . $seat['seat_id'] . ": " . print_r($errorInfo, true));
                                $seatsFailed++;
                            }
                        } catch (Exception $seatError) {
                            // Log but don't fail the booking if seat saving fails
                            error_log("Error saving seat " . $seat['seat_id'] . ": " . $seatError->getMessage() . " | Trace: " . $seatError->getTraceAsString());
                            $seatsFailed++;
                        }
                    }
                    
                    error_log("Seat saving summary - Saved: $seatsSaved, Failed: $seatsFailed");
                } else {
                    // Table doesn't exist - log warning but don't fail
                    error_log("Warning: booked_seats table does not exist. Run migration: database/migrations/create_booked_seats_table.sql");
                }
            } else {
                error_log("No booked seats provided in booking data");
            }
            
            // Mark reservations as confirmed
            if (!empty($data['reservation_ids'])) {
                try {
                    $reservationIds = is_array($data['reservation_ids']) 
                        ? $data['reservation_ids'] 
                        : explode(',', $data['reservation_ids']);
                    
                    // Filter out empty values and convert to integers
                    $reservationIds = array_filter(array_map('intval', $reservationIds), function($id) {
                        return $id > 0;
                    });
                    
                    if (!empty($reservationIds)) {
                        $placeholders = implode(',', array_fill(0, count($reservationIds), '?'));
                        $reservationQuery = "UPDATE ticket_reservations 
                                            SET status = 'confirmed' 
                                            WHERE id IN ($placeholders)";
                        $reservationStmt = $this->db->prepare($reservationQuery);
                        $reservationStmt->execute($reservationIds);
                        
                    }
                } catch (Exception $e) {
                    // Log but don't fail the booking if reservation update fails
                    error_log("Warning: Failed to update reservation status: " . $e->getMessage());
                }
            }
            
            $this->db->commit();
            
            return [
                'success' => true,
                'booking_id' => $bookingId,
                'booking_code' => $bookingCode
            ];
            
        } catch (PDOException $e) {
            if ($this->db->inTransaction()) {
                $this->db->rollBack();
            }
            error_log("PDO Error creating booking: " . $e->getMessage() . " | Code: " . $e->getCode() . " | SQL State: " . $e->getCode());
            throw new Exception("Database error creating booking: " . $e->getMessage());
        } catch (Exception $e) {
            if ($this->db->inTransaction()) {
                $this->db->rollBack();
            }
            error_log("Error creating booking: " . $e->getMessage() . " | File: " . $e->getFile() . " | Line: " . $e->getLine());
            throw new Exception("Error creating booking: " . $e->getMessage());
        }
    }
    
    /**
     * Check if a table exists in the database
     * @param string $tableName The name of the table to check
     * @return bool True if the table exists, false otherwise
     */
    private function tableExists($tableName) {
        try {
            $result = $this->db->query("SHOW TABLES LIKE '$tableName'");
            return $result->rowCount() > 0;
        } catch (PDOException $e) {
            error_log("Database error checking table existence for $tableName: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Generate unique booking code
     */
    private function generateBookingCode() {
        do {
            $code = 'EGZ' . strtoupper(substr(uniqid(), -8));
            $stmt = $this->db->prepare("SELECT id FROM " . $this->table . " WHERE booking_code = ?");
            $stmt->execute([$code]);
        } while ($stmt->fetch());
        
        return $code;
    }
    
    /**
     * Get booked seats for an event
     */
    public function getBookedSeats($eventId) {
        try {
            // Check if table exists first
            $tableCheck = $this->db->query("SHOW TABLES LIKE 'booked_seats'");
            if ($tableCheck->rowCount() === 0) {
                // Table doesn't exist yet - return empty array
                return [];
            }
            
            $query = "SELECT seat_id FROM booked_seats WHERE event_id = :event_id";
            $stmt = $this->db->prepare($query);
            $stmt->bindParam(':event_id', $eventId, PDO::PARAM_INT);
            $stmt->execute();
            
            $seats = [];
            while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                $seats[] = $row['seat_id'];
            }
            
            return $seats;
            
        } catch (Exception $e) {
            // Return empty array instead of throwing error
            error_log("Error fetching booked seats: " . $e->getMessage());
            return [];
        }
    }
    
    /**
     * Update booking status
     */
    public function updateBookingStatus($id, $status) {
        try {
            $query = "UPDATE " . $this->table . " SET status = :status WHERE id = :id";
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
    public function updatePaymentStatus($id, $paymentStatus) {
        try {
            $query = "UPDATE " . $this->table . " SET payment_status = :payment_status, updated_at = NOW() WHERE id = :id";
            $stmt = $this->db->prepare($query);
            $stmt->bindParam(':payment_status', $paymentStatus);
            $stmt->bindParam(':id', $id, PDO::PARAM_INT);
            return $stmt->execute();
        } catch (Exception $e) {
            throw new Exception("Error updating payment status: " . $e->getMessage());
        }
    }
    
    /**
     * Cancel booking and return tickets/seats
     */
    public function cancelBooking($id) {
        try {
            if (!$this->tableExists($this->table)) {
                throw new Exception("Bookings table does not exist.");
            }
            
            $this->db->beginTransaction();
            
            // Get booking details first
            $booking = $this->getBookingById($id);
            if (!$booking) {
                throw new Exception("Booking not found");
            }
            
            // Check if already cancelled
            if ($booking['status'] === 'cancelled') {
                throw new Exception("Booking is already cancelled");
            }
            
            // Get ticket details from booking (stored in ticket_details or notes column)
            $ticketDetailsJson = $booking['ticket_details'] ?? $booking['notes'] ?? null;
            $ticketDetails = [];
            if ($ticketDetailsJson) {
                try {
                    $ticketDetails = json_decode($ticketDetailsJson, true);
                } catch (Exception $e) {
                    error_log("Failed to parse ticket_details: " . $e->getMessage());
                }
            }
            
            // If ticket_details doesn't have categories, try to get from ticket_reservations
            if (empty($ticketDetails['ticket_categories'])) {
                // Try to get from reservations if they exist (ticket_reservations doesn't have booking_id column)
                $reservationQuery = "SELECT category_name, quantity FROM ticket_reservations 
                                    WHERE user_id = ? AND event_id = ? AND status = 'confirmed'
                                    ORDER BY id DESC LIMIT 10";
                $reservationStmt = $this->db->prepare($reservationQuery);
                $reservationStmt->execute([$booking['user_id'], $booking['event_id']]);
                $reservations = $reservationStmt->fetchAll(PDO::FETCH_ASSOC);
                
                if (!empty($reservations)) {
                    $ticketDetails['ticket_categories'] = [];
                    foreach ($reservations as $res) {
                        $ticketDetails['ticket_categories'][] = [
                            'category_name' => $res['category_name'],
                            'quantity' => $res['quantity']
                        ];
                    }
                } else {
                    // Fallback: query event_ticket_categories to get all categories and distribute tickets
                    // This is a last resort - ideally ticket_details should be stored
                    $fallbackQuery = "SELECT category_name, total_tickets FROM event_ticket_categories WHERE event_id = ?";
                    $fallbackStmt = $this->db->prepare($fallbackQuery);
                    $fallbackStmt->execute([$booking['event_id']]);
                    $categories = $fallbackStmt->fetchAll(PDO::FETCH_ASSOC);
                    
                    if (!empty($categories)) {
                        $ticketCount = $booking['ticket_count'] ?? 0;
                        $ticketsPerCategory = floor($ticketCount / count($categories));
                        $remainder = $ticketCount % count($categories);
                        
                        $ticketDetails['ticket_categories'] = [];
                        foreach ($categories as $index => $cat) {
                            $qty = $ticketsPerCategory + ($index < $remainder ? 1 : 0);
                            if ($qty > 0) {
                                $ticketDetails['ticket_categories'][] = [
                                    'category_name' => $cat['category_name'],
                                    'quantity' => $qty
                                ];
                            }
                        }
                    }
                }
            }
            
            // Return tickets to available count
            if (!empty($ticketDetails['ticket_categories'])) {
                foreach ($ticketDetails['ticket_categories'] as $category) {
                    $categoryName = $category['category_name'] ?? '';
                    $quantity = intval($category['quantity'] ?? 0);
                    
                    if (empty($categoryName) || $quantity <= 0) {
                        continue;
                    }
                    
                    try {
                        // Use LEAST to ensure we don't exceed total_tickets
                        $updateQuery = "UPDATE event_ticket_categories 
                                       SET available_tickets = LEAST(available_tickets + :quantity, total_tickets),
                                           updated_at = NOW()
                                       WHERE event_id = :event_id AND category_name = :category_name";
                        $updateStmt = $this->db->prepare($updateQuery);
                        $updateStmt->bindValue(':quantity', $quantity, PDO::PARAM_INT);
                        $updateStmt->bindValue(':event_id', $booking['event_id'], PDO::PARAM_INT);
                        $updateStmt->bindValue(':category_name', $categoryName, PDO::PARAM_STR);
                        $updateStmt->execute();
                        
                    } catch (Exception $e) {
                        error_log("Error returning tickets for category $categoryName: " . $e->getMessage());
                        // Continue with other categories
                    }
                }
            } else {
                // Fallback: return all tickets as a single category
                $ticketCount = $booking['ticket_count'] ?? 0;
                if ($ticketCount > 0) {
                    // Try to find the first category for this event
                    $categoryQuery = "SELECT category_name FROM event_ticket_categories WHERE event_id = ? LIMIT 1";
                    $categoryStmt = $this->db->prepare($categoryQuery);
                    $categoryStmt->execute([$booking['event_id']]);
                    $categoryRow = $categoryStmt->fetch(PDO::FETCH_ASSOC);
                    
                    if ($categoryRow) {
                        $updateQuery = "UPDATE event_ticket_categories 
                                       SET available_tickets = LEAST(available_tickets + ?, total_tickets),
                                           updated_at = NOW()
                                       WHERE event_id = ? AND category_name = ?";
                        $updateStmt = $this->db->prepare($updateQuery);
                        $updateStmt->execute([$ticketCount, $booking['event_id'], $categoryRow['category_name']]);
                    }
                }
            }
            
            // Return booked seats (delete from booked_seats table)
            $tableCheck = $this->db->query("SHOW TABLES LIKE 'booked_seats'");
            if ($tableCheck->rowCount() > 0) {
                $deleteSeatsQuery = "DELETE FROM booked_seats WHERE booking_id = :booking_id";
                $deleteSeatsStmt = $this->db->prepare($deleteSeatsQuery);
                $deleteSeatsStmt->bindValue(':booking_id', $id, PDO::PARAM_INT);
                $deleteSeatsStmt->execute();
                $seatsDeleted = $deleteSeatsStmt->rowCount();
            }
            
            // Update booking status to cancelled
            $updateStatusQuery = "UPDATE " . $this->table . " SET status = 'cancelled', updated_at = NOW() WHERE id = :id";
            $updateStatusStmt = $this->db->prepare($updateStatusQuery);
            $updateStatusStmt->bindValue(':id', $id, PDO::PARAM_INT);
            $updateStatusStmt->execute();
            
            $this->db->commit();
            
            return [
                'success' => true,
                'message' => 'Booking cancelled successfully. Tickets and seats have been returned.'
            ];
            
        } catch (Exception $e) {
            if ($this->db->inTransaction()) {
                $this->db->rollBack();
            }
            error_log("Error cancelling booking: " . $e->getMessage());
            throw new Exception("Error cancelling booking: " . $e->getMessage());
        }
    }
    
    /**
     * Approve cash payment (for pay at venue bookings)
     */
    public function approveCashPayment($id) {
        try {
            if (!$this->tableExists($this->table)) {
                throw new Exception("Bookings table does not exist.");
            }
            
            // Get booking details
            $booking = $this->getBookingById($id);
            if (!$booking) {
                throw new Exception("Booking not found");
            }
            
            // Check if payment method is cash
            $paymentMethod = $booking['payment_method'] ?? '';
            if ($paymentMethod !== 'cash') {
                throw new Exception("This booking is not a cash payment. Payment method: " . $paymentMethod);
            }
            
            // Check if payment is already approved
            if ($booking['payment_status'] === 'paid') {
                throw new Exception("Payment has already been approved");
            }
            
            // Update payment status to paid and booking status to confirmed
            $query = "UPDATE " . $this->table . " 
                     SET payment_status = 'paid', 
                         status = 'confirmed',
                         updated_at = NOW() 
                     WHERE id = :id";
            $stmt = $this->db->prepare($query);
            $stmt->bindValue(':id', $id, PDO::PARAM_INT);
            $result = $stmt->execute();
            
            if (!$result) {
                throw new Exception("Failed to update payment status");
            }
            
            return [
                'success' => true,
                'message' => 'Cash payment approved successfully. Booking status updated to confirmed.'
            ];
            
        } catch (Exception $e) {
            error_log("Error approving cash payment: " . $e->getMessage());
            throw new Exception("Error approving cash payment: " . $e->getMessage());
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
            $query = "SELECT 
                        b.*,
                        e.title as event_title,
                        v.name as venue_name
                      FROM " . $this->table . " b
                      LEFT JOIN events e ON b.event_id = e.id
                      LEFT JOIN venues v ON e.venue_id = v.id
                      ORDER BY b.created_at DESC
                      LIMIT :limit";
            
            $stmt = $this->db->prepare($query);
            $stmt->bindValue(':limit', (int)$limit, PDO::PARAM_INT);
            $stmt->execute();
            
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
            
        } catch (Exception $e) {
            throw new Exception("Error fetching recent bookings: " . $e->getMessage());
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
<?php
class TicketReservation {
    private PDO $conn;
    private string $table_name = "ticket_reservations";

    public $id;
    public $event_id;
    public $category_name;
    public $quantity;
    public $user_id;
    public $session_id;
    public $reserved_at;
    public $expires_at;
    public $status;

    public function __construct(PDO $db) {
        $this->conn = $db;
    }

    public function create() {
        $query = "INSERT INTO " . $this->table_name . "
                SET event_id = :event_id,
                    category_name = :category_name,
                    quantity = :quantity,
                    user_id = :user_id,
                    session_id = :session_id,
                    expires_at = :expires_at,
                    status = 'reserved'";
        
        $stmt = $this->conn->prepare($query);
        
        $stmt->bindParam(":event_id", $this->event_id);
        $stmt->bindParam(":category_name", $this->category_name);
        $stmt->bindParam(":quantity", $this->quantity);
        $stmt->bindParam(":user_id", $this->user_id);
        $stmt->bindParam(":session_id", $this->session_id);
        $stmt->bindParam(":expires_at", $this->expires_at);
        
        if($stmt->execute()) {
            $this->id = $this->conn->lastInsertId();
            return true;
        }
        return false;
    }

    public function getBySession($sessionId) {
        $query = "SELECT * FROM " . $this->table_name . " 
                  WHERE session_id = :session_id 
                  AND status = 'reserved'
                  AND expires_at > NOW()
                  ORDER BY reserved_at ASC";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":session_id", $sessionId);
        $stmt->execute();
        
        return $stmt;
    }

    public function getByEventAndCategory($eventId, $categoryName) {
        $query = "SELECT SUM(quantity) as total_reserved FROM " . $this->table_name . " 
                  WHERE event_id = :event_id 
                  AND category_name = :category_name
                  AND status = 'reserved'
                  AND expires_at > NOW()";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":event_id", $eventId);
        $stmt->bindParam(":category_name", $categoryName);
        $stmt->execute();
        
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return $row['total_reserved'] ?? 0;
    }

    public function expireOldReservations() {
        $query = "UPDATE " . $this->table_name . "
                SET status = 'expired'
                WHERE status = 'reserved'
                AND expires_at <= NOW()";
        
        $stmt = $this->conn->prepare($query);
        return $stmt->execute();
    }

    public function confirmReservation($reservationId) {
        $query = "UPDATE " . $this->table_name . "
                SET status = 'confirmed'
                WHERE id = :id AND status = 'reserved'";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":id", $reservationId);
        return $stmt->execute();
    }

    public function deleteBySession($sessionId) {
        $query = "DELETE FROM " . $this->table_name . " 
                  WHERE session_id = :session_id AND status = 'reserved'";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":session_id", $sessionId);
        return $stmt->execute();
    }
}
?>


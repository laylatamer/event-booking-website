<?php
class EventTicketCategory {
    private PDO $conn;
    private string $table_name = "event_ticket_categories";

    public $id;
    public $event_id;
    public $category_name;
    public $total_tickets;
    public $available_tickets;
    public $price;
    public $created_at;
    public $updated_at;

    public function __construct(PDO $db) {
        $this->conn = $db;
    }

    public function create() {
        $query = "INSERT INTO " . $this->table_name . "
                SET event_id = :event_id,
                    category_name = :category_name,
                    total_tickets = :total_tickets,
                    available_tickets = :available_tickets,
                    price = :price";
        
        $stmt = $this->conn->prepare($query);
        
        $stmt->bindParam(":event_id", $this->event_id);
        $stmt->bindParam(":category_name", $this->category_name);
        $stmt->bindParam(":total_tickets", $this->total_tickets);
        $stmt->bindParam(":available_tickets", $this->available_tickets);
        $stmt->bindParam(":price", $this->price);
        
        if($stmt->execute()) {
            $this->id = $this->conn->lastInsertId();
            return true;
        }
        return false;
    }

    public function getByEventId($eventId) {
        $query = "SELECT * FROM " . $this->table_name . " 
                  WHERE event_id = :event_id 
                  ORDER BY price DESC, category_name ASC";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":event_id", $eventId);
        $stmt->execute();
        
        return $stmt;
    }

    public function getByEventAndCategory($eventId, $categoryName) {
        $query = "SELECT * FROM " . $this->table_name . " 
                  WHERE event_id = :event_id AND category_name = :category_name 
                  LIMIT 1";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":event_id", $eventId);
        $stmt->bindParam(":category_name", $categoryName);
        $stmt->execute();
        
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        if($row) {
            $this->id = $row['id'];
            $this->event_id = $row['event_id'];
            $this->category_name = $row['category_name'];
            $this->total_tickets = $row['total_tickets'];
            $this->available_tickets = $row['available_tickets'];
            $this->price = $row['price'];
            return true;
        }
        return false;
    }

    public function updateAvailableTickets($quantity) {
        $query = "UPDATE " . $this->table_name . "
                SET available_tickets = available_tickets - :quantity,
                    updated_at = CURRENT_TIMESTAMP
                WHERE id = :id AND available_tickets >= :quantity";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":quantity", $quantity);
        $stmt->bindParam(":id", $this->id);
        
        return $stmt->execute() && $stmt->rowCount() > 0;
    }

    public function releaseTickets($quantity) {
        $query = "UPDATE " . $this->table_name . "
                SET available_tickets = available_tickets + :quantity,
                    updated_at = CURRENT_TIMESTAMP
                WHERE id = :id";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":quantity", $quantity);
        $stmt->bindParam(":id", $this->id);
        
        return $stmt->execute();
    }

    public function deleteByEventId($eventId) {
        $query = "DELETE FROM " . $this->table_name . " WHERE event_id = :event_id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":event_id", $eventId);
        return $stmt->execute();
    }
}
?>


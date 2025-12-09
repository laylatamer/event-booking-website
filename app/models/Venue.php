<?php
class Venue {
    private PDO $conn;
    private string $table_name = "venues";

    public $id;
    public $name;
    public $address;
    public $city;
    public $country;
    public $capacity;
    public $description;
    public $facilities;
    public $google_maps_url;
    public $image_url;
    public $status;
    public $created_at;
    public $updated_at;

    public function __construct(PDO $db) {
        $this->conn = $db;
    }

    public function create() {
        $query = "INSERT INTO " . $this->table_name . "
                SET name = :name,
                    address = :address,
                    city = :city,
                    country = :country,
                    capacity = :capacity,
                    description = :description,
                    facilities = :facilities,
                    google_maps_url = :google_maps_url,
                    image_url = :image_url,
                    status = :status";
        
        $stmt = $this->conn->prepare($query);
        
        $this->name = htmlspecialchars(strip_tags($this->name));
        $this->address = htmlspecialchars(strip_tags($this->address));
        $this->city = htmlspecialchars(strip_tags($this->city));
        $this->description = htmlspecialchars(strip_tags($this->description));
        $this->facilities = json_encode($this->facilities);
        
        $stmt->bindParam(":name", $this->name);
        $stmt->bindParam(":address", $this->address);
        $stmt->bindParam(":city", $this->city);
        $stmt->bindParam(":country", $this->country);
        $stmt->bindParam(":capacity", $this->capacity);
        $stmt->bindParam(":description", $this->description);
        $stmt->bindParam(":facilities", $this->facilities);
        $stmt->bindParam(":google_maps_url", $this->google_maps_url);
        $stmt->bindParam(":image_url", $this->image_url);
        $stmt->bindParam(":status", $this->status);
        
        if($stmt->execute()) {
            $this->id = $this->conn->lastInsertId();
            return true;
        }
        return false;
    }

    public function readAll() {
        $query = "SELECT * FROM " . $this->table_name . " ORDER BY name ASC";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        return $stmt;
    }

    public function readAllActive() {
        $query = "SELECT * FROM " . $this->table_name . " WHERE status = 'active' ORDER BY name ASC";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        return $stmt;
    }

    public function readOne() {
        $query = "SELECT * FROM " . $this->table_name . " WHERE id = ? LIMIT 0,1";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(1, $this->id);
        $stmt->execute();
        
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if($row) {
            $this->name = $row['name'];
            $this->address = $row['address'];
            $this->city = $row['city'];
            $this->country = $row['country'];
            $this->capacity = $row['capacity'];
            $this->description = $row['description'];
            $this->facilities = json_decode($row['facilities'], true);
            $this->google_maps_url = $row['google_maps_url'];
            $this->image_url = $row['image_url'];
            $this->status = $row['status'];
            $this->created_at = $row['created_at'];
            $this->updated_at = $row['updated_at'];
            return true;
        }
        return false;
    }

    public function update() {
        $query = "UPDATE " . $this->table_name . "
                SET name = :name,
                    address = :address,
                    city = :city,
                    country = :country,
                    capacity = :capacity,
                    description = :description,
                    facilities = :facilities,
                    google_maps_url = :google_maps_url,
                    image_url = :image_url,
                    status = :status,
                    updated_at = CURRENT_TIMESTAMP
                WHERE id = :id";
        
        $stmt = $this->conn->prepare($query);
        
        $this->name = htmlspecialchars(strip_tags($this->name));
        $this->address = htmlspecialchars(strip_tags($this->address));
        $this->city = htmlspecialchars(strip_tags($this->city));
        $this->description = htmlspecialchars(strip_tags($this->description));
        $this->facilities = json_encode($this->facilities);
        
        $stmt->bindParam(":name", $this->name);
        $stmt->bindParam(":address", $this->address);
        $stmt->bindParam(":city", $this->city);
        $stmt->bindParam(":country", $this->country);
        $stmt->bindParam(":capacity", $this->capacity);
        $stmt->bindParam(":description", $this->description);
        $stmt->bindParam(":facilities", $this->facilities);
        $stmt->bindParam(":google_maps_url", $this->google_maps_url);
        $stmt->bindParam(":image_url", $this->image_url);
        $stmt->bindParam(":status", $this->status);
        $stmt->bindParam(":id", $this->id);
        
        return $stmt->execute();
    }

    public function delete() {
        // Check if venue is used in any events
        $checkQuery = "SELECT COUNT(*) as event_count FROM events WHERE venue_id = ?";
        $checkStmt = $this->conn->prepare($checkQuery);
        $checkStmt->bindParam(1, $this->id);
        $checkStmt->execute();
        $result = $checkStmt->fetch(PDO::FETCH_ASSOC);
        
        if ($result['event_count'] > 0) {
            throw new Exception("Cannot delete venue because it is associated with existing events.");
        }
        
        $query = "DELETE FROM " . $this->table_name . " WHERE id = ?";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(1, $this->id);
        return $stmt->execute();
    }

    public function search($keyword) {
        $query = "SELECT * FROM " . $this->table_name . " 
                  WHERE name LIKE :keyword 
                  OR city LIKE :keyword 
                  OR address LIKE :keyword 
                  ORDER BY name ASC";
        
        $stmt = $this->conn->prepare($query);
        $keyword = "%$keyword%";
        $stmt->bindParam(":keyword", $keyword);
        $stmt->execute();
        return $stmt;
    }

    public function getEvents() {
        $query = "SELECT e.* FROM events e 
                  WHERE e.venue_id = ? 
                  AND e.status = 'active' 
                  ORDER BY e.date ASC";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(1, $this->id);
        $stmt->execute();
        return $stmt;
    }

    public function getEventCount() {
        try {
            $query = "SELECT COUNT(*) as count FROM events WHERE venue_id = ?";
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(1, $this->id);
            $stmt->execute();
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            return $result['count'] ?? 0;
        } catch (Exception $e) {
            // Return 0 if events table doesn't exist
            return 0;
        }
    }
}

?>
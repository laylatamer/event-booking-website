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
        
        return $stmt->execute();
    }

    public function readAll() {
        $query = "SELECT * FROM " . $this->table_name . " ORDER BY name ASC";
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
                    status = :status
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
        $query = "DELETE FROM " . $this->table_name . " WHERE id = ?";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(1, $this->id);
        return $stmt->execute();
    }
}
?>
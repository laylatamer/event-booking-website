<?php
class Subcategory {
    private $conn;
    private $table_name = "subcategories";

    public $id;
    public $main_category_id;
    public $name;
    public $status;

    public function __construct($db) {
        $this->conn = $db;
    }

    public function create() {
        $query = "INSERT INTO " . $this->table_name . "
                SET main_category_id = :main_category_id,
                    name = :name,
                    status = :status";
        
        $stmt = $this->conn->prepare($query);
        
        $this->name = htmlspecialchars(strip_tags($this->name));
        
        $stmt->bindParam(":main_category_id", $this->main_category_id);
        $stmt->bindParam(":name", $this->name);
        $stmt->bindParam(":status", $this->status);
        
        return $stmt->execute();
    }

    public function readByMainCategory($main_category_id) {
        $query = "SELECT s.*, m.name as main_category_name 
                 FROM " . $this->table_name . " s
                 JOIN main_categories m ON s.main_category_id = m.id
                 WHERE s.main_category_id = ? 
                 ORDER BY s.name ASC";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(1, $main_category_id);
        $stmt->execute();
        return $stmt;
    }

    public function readAll() {
        $query = "SELECT s.*, m.name as main_category_name 
                 FROM " . $this->table_name . " s
                 JOIN main_categories m ON s.main_category_id = m.id
                 ORDER BY m.name, s.name ASC";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        return $stmt;
    }

    public function readOne() {
        $query = "SELECT s.*, m.name as main_category_name 
                 FROM " . $this->table_name . " s
                 JOIN main_categories m ON s.main_category_id = m.id
                 WHERE s.id = ? LIMIT 0,1";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(1, $this->id);
        $stmt->execute();
        
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if($row) {
            $this->main_category_id = $row['main_category_id'];
            $this->name = $row['name'];
            $this->status = $row['status'];
            return $row;
        }
        return false;
    }

    public function update() {
        $query = "UPDATE " . $this->table_name . "
                SET main_category_id = :main_category_id,
                    name = :name,
                    status = :status
                WHERE id = :id";
        
        $stmt = $this->conn->prepare($query);
        
        $this->name = htmlspecialchars(strip_tags($this->name));
        
        $stmt->bindParam(":main_category_id", $this->main_category_id);
        $stmt->bindParam(":name", $this->name);
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
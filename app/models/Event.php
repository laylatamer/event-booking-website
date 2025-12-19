<?php
class Event {
    private PDO $conn;
    private string $table_name = "events";

    public $id;
    public $title;
    public $description;
    public $subcategory_id;
    public $venue_id;
    public $date;
    public $end_date;
    public $price;
    public $discounted_price;
    public $image_url;
    public $gallery_images;
    public $total_tickets;
    public $available_tickets;
    public $min_tickets_per_booking;
    public $max_tickets_per_booking;
    public $terms_conditions;
    public $additional_info;
    public $status;

    public function __construct(PDO $db) {
        $this->conn = $db;
    }

    public function create() {
        $query = "INSERT INTO " . $this->table_name . "
                SET title = :title,
                    description = :description,
                    subcategory_id = :subcategory_id,
                    venue_id = :venue_id,
                    date = :date,
                    end_date = :end_date,
                    price = :price,
                    discounted_price = :discounted_price,
                    image_url = :image_url,
                    gallery_images = :gallery_images,
                    total_tickets = :total_tickets,
                    available_tickets = :available_tickets,
                    min_tickets_per_booking = :min_tickets_per_booking,
                    max_tickets_per_booking = :max_tickets_per_booking,
                    terms_conditions = :terms_conditions,
                    additional_info = :additional_info,
                    status = :status";
        
        $stmt = $this->conn->prepare($query);
        
        $this->title = htmlspecialchars(strip_tags($this->title));
        $this->description = htmlspecialchars(strip_tags($this->description));
        $this->terms_conditions = htmlspecialchars(strip_tags($this->terms_conditions));
        $this->gallery_images = json_encode($this->gallery_images);
        $this->additional_info = json_encode($this->additional_info);
        
        $stmt->bindParam(":title", $this->title);
        $stmt->bindParam(":description", $this->description);
        $stmt->bindParam(":subcategory_id", $this->subcategory_id);
        $stmt->bindParam(":venue_id", $this->venue_id);
        $stmt->bindParam(":date", $this->date);
        $stmt->bindParam(":end_date", $this->end_date);
        $stmt->bindParam(":price", $this->price);
        $stmt->bindParam(":discounted_price", $this->discounted_price);
        $stmt->bindParam(":image_url", $this->image_url);
        $stmt->bindParam(":gallery_images", $this->gallery_images);
        $stmt->bindParam(":total_tickets", $this->total_tickets);
        $stmt->bindParam(":available_tickets", $this->available_tickets);
        $stmt->bindParam(":min_tickets_per_booking", $this->min_tickets_per_booking);
        $stmt->bindParam(":max_tickets_per_booking", $this->max_tickets_per_booking);
        $stmt->bindParam(":terms_conditions", $this->terms_conditions);
        $stmt->bindParam(":additional_info", $this->additional_info);
        $stmt->bindParam(":status", $this->status);
        
        if($stmt->execute()) {
            return $this->conn->lastInsertId();
        }
        return false;
    }

    public function readAll() {
        $query = "SELECT e.*, 
                         s.name as subcategory_name,
                         s.main_category_id,
                         mc.name as main_category_name,
                         v.name as venue_name,
                         v.city as venue_city
                  FROM " . $this->table_name . " e
                  JOIN subcategories s ON e.subcategory_id = s.id
                  JOIN main_categories mc ON s.main_category_id = mc.id
                  JOIN venues v ON e.venue_id = v.id
                  ORDER BY e.date ASC";
        
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        return $stmt;
    }

    public function readOne() {
        $query = "SELECT e.*, 
                         s.name as subcategory_name,
                         s.main_category_id,
                         mc.name as main_category_name,
                         v.name as venue_name,
                         v.address as venue_address,
                         v.city as venue_city,
                         v.country as venue_country,
                         v.capacity as venue_capacity,
                         v.description as venue_description,
                         v.facilities as venue_facilities,
                         v.google_maps_url as venue_google_maps_url,
                         v.seating_type as venue_seating_type
                  FROM " . $this->table_name . " e
                  JOIN subcategories s ON e.subcategory_id = s.id
                  JOIN main_categories mc ON s.main_category_id = mc.id
                  JOIN venues v ON e.venue_id = v.id
                  WHERE e.id = ? LIMIT 0,1";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(1, $this->id);
        $stmt->execute();
        
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if($row) {
            $this->title = $row['title'];
            $this->description = $row['description'];
            $this->subcategory_id = $row['subcategory_id'];
            $this->venue_id = $row['venue_id'];
            $this->date = $row['date'];
            $this->end_date = $row['end_date'];
            $this->price = $row['price'];
            $this->discounted_price = $row['discounted_price'];
            $this->image_url = $row['image_url'];
            $this->gallery_images = json_decode($row['gallery_images'], true);
            $this->total_tickets = $row['total_tickets'];
            $this->available_tickets = $row['available_tickets'];
            $this->min_tickets_per_booking = $row['min_tickets_per_booking'];
            $this->max_tickets_per_booking = $row['max_tickets_per_booking'];
            $this->terms_conditions = $row['terms_conditions'];
            $this->additional_info = json_decode($row['additional_info'], true);
            $this->status = $row['status'];
            return $row;
        }
        return false;
    }

    public function update() {
        $query = "UPDATE " . $this->table_name . "
                SET title = :title,
                    description = :description,
                    subcategory_id = :subcategory_id,
                    venue_id = :venue_id,
                    date = :date,
                    end_date = :end_date,
                    price = :price,
                    discounted_price = :discounted_price,
                    image_url = :image_url,
                    gallery_images = :gallery_images,
                    total_tickets = :total_tickets,
                    available_tickets = :available_tickets,
                    min_tickets_per_booking = :min_tickets_per_booking,
                    max_tickets_per_booking = :max_tickets_per_booking,
                    terms_conditions = :terms_conditions,
                    additional_info = :additional_info,
                    status = :status
                WHERE id = :id";
        
        $stmt = $this->conn->prepare($query);
        
        $this->title = htmlspecialchars(strip_tags($this->title));
        $this->description = htmlspecialchars(strip_tags($this->description));
        $this->terms_conditions = htmlspecialchars(strip_tags($this->terms_conditions));
        $this->gallery_images = json_encode($this->gallery_images);
        $this->additional_info = json_encode($this->additional_info);
        
        $stmt->bindParam(":title", $this->title);
        $stmt->bindParam(":description", $this->description);
        $stmt->bindParam(":subcategory_id", $this->subcategory_id);
        $stmt->bindParam(":venue_id", $this->venue_id);
        $stmt->bindParam(":date", $this->date);
        $stmt->bindParam(":end_date", $this->end_date);
        $stmt->bindParam(":price", $this->price);
        $stmt->bindParam(":discounted_price", $this->discounted_price);
        $stmt->bindParam(":image_url", $this->image_url);
        $stmt->bindParam(":gallery_images", $this->gallery_images);
        $stmt->bindParam(":total_tickets", $this->total_tickets);
        $stmt->bindParam(":available_tickets", $this->available_tickets);
        $stmt->bindParam(":min_tickets_per_booking", $this->min_tickets_per_booking);
        $stmt->bindParam(":max_tickets_per_booking", $this->max_tickets_per_booking);
        $stmt->bindParam(":terms_conditions", $this->terms_conditions);
        $stmt->bindParam(":additional_info", $this->additional_info);
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

    public function getByCategory($category) {
        $query = "SELECT e.*, 
                         s.name as subcategory_name,
                         mc.name as main_category_name,
                         v.name as venue_name
                  FROM " . $this->table_name . " e
                  JOIN subcategories s ON e.subcategory_id = s.id
                  JOIN main_categories mc ON s.main_category_id = mc.id
                  JOIN venues v ON e.venue_id = v.id
                  WHERE mc.name = ? AND e.status = 'active' 
                  ORDER BY e.date ASC";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(1, $category);
        $stmt->execute();
        return $stmt;
    }

    public function getUpcoming($limit = 10) {
        $query = "SELECT e.*, 
                         s.name as subcategory_name,
                         mc.name as main_category_name,
                         v.name as venue_name,
                         v.city as venue_city  
                  FROM " . $this->table_name . " e
                  JOIN subcategories s ON e.subcategory_id = s.id
                  JOIN main_categories mc ON s.main_category_id = mc.id
                  JOIN venues v ON e.venue_id = v.id
                  WHERE e.date >= NOW() AND e.status = 'active' 
                  ORDER BY e.date ASC 
                  LIMIT ?";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(1, $limit, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt;
    }

    public function updateTicketAvailability($quantity) {
        $query = "UPDATE " . $this->table_name . " 
                 SET available_tickets = available_tickets - ? 
                 WHERE id = ? AND available_tickets >= ?";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(1, $quantity);
        $stmt->bindParam(2, $this->id);
        $stmt->bindParam(3, $quantity);
        
        return $stmt->execute();
    }

    public function count() {
        $query = "SELECT COUNT(*) FROM " . $this->table_name;
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        return (int) $stmt->fetchColumn();
    }
}
?>
<?php
require_once __DIR__ . '/../models/MainCategory.php';
require_once __DIR__ . '/../models/Subcategory.php';
require_once __DIR__ . '/../models/Venue.php';
require_once __DIR__ . '/../models/Event.php';

class AdminController {
    private $mainCategory;
    private $subcategory;
    private $venue;
    private $event;
    private $db; // Add database connection reference

    public function __construct(PDO $db) {
        $this->db = $db; // Store database connection
        $this->mainCategory = new MainCategory($db);
        $this->subcategory = new Subcategory($db);
        $this->venue = new Venue($db);
        $this->event = new Event($db);
    }

    // Main Categories CRUD
    public function createMainCategory($data) {
        $this->mainCategory->name = $data['name'];
        $this->mainCategory->status = $data['status'] ?? 'active';
        
        return $this->mainCategory->create();
    }

    public function updateMainCategory($id, $data) {
        $this->mainCategory->id = $id;
        $this->mainCategory->name = $data['name'];
        $this->mainCategory->status = $data['status'] ?? 'active';
        
        return $this->mainCategory->update();
    }

    public function deleteMainCategory($id) {
        $this->mainCategory->id = $id;
        return $this->mainCategory->delete();
    }

    public function getAllMainCategories() {
        $stmt = $this->mainCategory->readAll();
        $categories = [];
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $categories[] = $row;
        }
        return $categories;
    }

    public function getMainCategory($id) {
        $this->mainCategory->id = $id;
        if ($this->mainCategory->readOne()) {
            return [
                'id' => $this->mainCategory->id,
                'name' => $this->mainCategory->name,
                'status' => $this->mainCategory->status
            ];
        }
        return null;
    }

    // Subcategories CRUD - UPDATED with image support
    public function createSubcategory($data) {
        $this->subcategory->main_category_id = $data['main_category_id'];
        $this->subcategory->name = $data['name'];
        $this->subcategory->image_url = $data['image_url'] ?? null;
        $this->subcategory->status = $data['status'] ?? 'active';
        
        return $this->subcategory->create();
    }

    public function updateSubcategory($id, $data) {
        $this->subcategory->id = $id;
        $this->subcategory->main_category_id = $data['main_category_id'];
        $this->subcategory->name = $data['name'];
        $this->subcategory->image_url = $data['image_url'] ?? null;
        $this->subcategory->status = $data['status'] ?? 'active';
        
        return $this->subcategory->update();
    }

    public function deleteSubcategory($id) {
        $this->subcategory->id = $id;
        return $this->subcategory->delete();
    }

    public function getAllSubcategories() {
        $stmt = $this->subcategory->readAll();
        $subcategories = [];
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $subcategories[] = $row;
        }
        return $subcategories;
    }

    public function getSubcategory($id) {
        $this->subcategory->id = $id;
        return $this->subcategory->readOne();
    }

    public function getSubcategoriesByMainCategory($main_category_id) {
        $stmt = $this->subcategory->readByMainCategory($main_category_id);
        $subcategories = [];
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $subcategories[] = $row;
        }
        return $subcategories;
    }

    // this new method to check if subcategory exists
    public function subcategoryExists($main_category_id, $name, $exclude_id = null) {
        $query = "SELECT id FROM subcategories 
                  WHERE main_category_id = :main_category_id 
                  AND name = :name";
        
        if ($exclude_id) {
            $query .= " AND id != :exclude_id";
        }
        
        $query .= " LIMIT 1";
        
        $stmt = $this->db->prepare($query);
        $stmt->bindParam(":main_category_id", $main_category_id);
        $stmt->bindParam(":name", $name);
        
        if ($exclude_id) {
            $stmt->bindParam(":exclude_id", $exclude_id);
        }
        
        $stmt->execute();
        
        return $stmt->rowCount() > 0;
    }

    // this new method to get subcategories count
    public function getSubcategoriesCount($main_category_id = null) {
        if ($main_category_id) {
            $query = "SELECT COUNT(*) as count FROM subcategories 
                      WHERE main_category_id = :main_category_id";
            $stmt = $this->db->prepare($query);
            $stmt->bindParam(":main_category_id", $main_category_id);
        } else {
            $query = "SELECT COUNT(*) as count FROM subcategories";
            $stmt = $this->db->prepare($query);
        }
        
        $stmt->execute();
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        return $result['count'] ?? 0;
    }

    // Venues CRUD 
    public function createVenue($data, $imageFile = null) {
        $this->venue->name = $data['name'];
        $this->venue->address = $data['address'];
        $this->venue->city = $data['city'];
        $this->venue->country = $data['country'] ?? 'Egypt';
        $this->venue->capacity = $data['capacity'];
        $this->venue->description = $data['description'];
        $this->venue->facilities = $data['facilities'] ?? [];
        $this->venue->google_maps_url = $data['google_maps_url'];
        $this->venue->status = $data['status'] ?? 'active';
        $this->venue->seating_type = $data['seating_type'] ?? null;
        
        // Handle image upload
        if ($imageFile && $imageFile['error'] === UPLOAD_ERR_OK) {
            $this->venue->image_url = $this->venue->uploadImage($imageFile);
        } else {
            $this->venue->image_url = $data['image_url'] ?? '';
        }
        
        return $this->venue->create();
    }

    public function updateVenue($id, $data, $imageFile = null) {
        $this->venue->id = $id;
        
        // First get current venue
        if ($this->venue->readOne()) {
            $currentImage = $this->venue->image_url;
            
            $this->venue->name = $data['name'];
            $this->venue->address = $data['address'];
            $this->venue->city = $data['city'];
            $this->venue->country = $data['country'] ?? 'Egypt';
            $this->venue->capacity = $data['capacity'];
            $this->venue->description = $data['description'];
            $this->venue->facilities = $data['facilities'] ?? [];
            $this->venue->google_maps_url = $data['google_maps_url'];
            $this->venue->status = $data['status'] ?? 'active';
            $this->venue->seating_type = $data['seating_type'] ?? null;
            
            // Handle image upload
            if ($imageFile && $imageFile['error'] === UPLOAD_ERR_OK) {
                // Delete old image if exists
                $this->venue->deleteImage($currentImage);
                
                // Upload new image
                $this->venue->image_url = $this->venue->uploadImage($imageFile);
            } else {
                // Keep existing image
                $this->venue->image_url = $currentImage;
            }
            
            return $this->venue->update();
        }
        return false;
    }

    public function deleteVenue($id) {
        $this->venue->id = $id;
        
        // First get venue to delete image
        if ($this->venue->readOne()) {
            $this->venue->deleteImage($this->venue->image_url);
        }
        
        return $this->venue->delete();
    }

    public function getAllVenues() {
        $stmt = $this->venue->readAll();
        $venues = [];
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $venues[] = $row;
        }
        return $venues;
    }

    public function getVenue($id) {
        $this->venue->id = $id;
        if ($this->venue->readOne()) {
            return [
                'id' => $this->venue->id,
                'name' => $this->venue->name,
                'address' => $this->venue->address,
                'city' => $this->venue->city,
                'country' => $this->venue->country,
                'capacity' => $this->venue->capacity,
                'description' => $this->venue->description,
                'facilities' => $this->venue->facilities,
                'google_maps_url' => $this->venue->google_maps_url,
                'image_url' => $this->venue->image_url,
                'status' => $this->venue->status
            ];
        }
        return null;
    }

    // Events CRUD (keep as is)
    public function createEvent($data) {
        $this->event->title = $data['title'];
        $this->event->description = $data['description'];
        $this->event->subcategory_id = $data['subcategory_id'];
        $this->event->venue_id = $data['venue_id'];
        $this->event->date = $data['date'];
        $this->event->end_date = $data['end_date'] ?? null;
        $this->event->price = $data['price'];
        $this->event->discounted_price = $data['discounted_price'] ?? null;
        $this->event->image_url = $data['image_url'];
        $this->event->gallery_images = $data['gallery_images'] ?? [];
        $this->event->total_tickets = $data['total_tickets'];
        $this->event->available_tickets = $data['available_tickets'];
        $this->event->min_tickets_per_booking = $data['min_tickets_per_booking'] ?? 1;
        $this->event->max_tickets_per_booking = $data['max_tickets_per_booking'] ?? 10;
        $this->event->terms_conditions = $data['terms_conditions'] ?? '';
        $this->event->additional_info = $data['additional_info'] ?? [];
        $this->event->status = $data['status'] ?? 'active';
        
        $eventId = $this->event->create();
        if ($eventId) {
            $this->event->id = $eventId;
            return $eventId; // Return event ID on success
        }
        return false;
    }

    public function updateEvent($id, $data) {
        $this->event->id = $id;
        $this->event->title = $data['title'];
        $this->event->description = $data['description'];
        $this->event->subcategory_id = $data['subcategory_id'];
        $this->event->venue_id = $data['venue_id'];
        $this->event->date = $data['date'];
        $this->event->end_date = $data['end_date'] ?? null;
        $this->event->price = $data['price'];
        $this->event->discounted_price = $data['discounted_price'] ?? null;
        $this->event->image_url = $data['image_url'];
        $this->event->gallery_images = $data['gallery_images'] ?? [];
        $this->event->total_tickets = $data['total_tickets'];
        $this->event->available_tickets = $data['available_tickets'];
        $this->event->min_tickets_per_booking = $data['min_tickets_per_booking'] ?? 1;
        $this->event->max_tickets_per_booking = $data['max_tickets_per_booking'] ?? 10;
        $this->event->terms_conditions = $data['terms_conditions'] ?? '';
        $this->event->additional_info = $data['additional_info'] ?? [];
        $this->event->status = $data['status'] ?? 'active';
        
        return $this->event->update();
    }

    public function deleteEvent($id) {
        $this->event->id = $id;
        return $this->event->delete();
    }

    public function getAllEvents() {
        $stmt = $this->event->readAll();
        $events = [];
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $events[] = $row;
        }
        return $events;
    }

    public function getEvent($id) {
        $this->event->id = $id;
        return $this->event->readOne();
    }


public function getAllEventsWithDetails() {
    $query = "SELECT e.*, 
                     s.name as subcategory_name,
                     s.main_category_id,
                     mc.name as main_category_name,
                     v.name as venue_name,
                     v.city as venue_city,
                     v.capacity as venue_capacity
              FROM events e
              JOIN subcategories s ON e.subcategory_id = s.id
              JOIN main_categories mc ON s.main_category_id = mc.id
              JOIN venues v ON e.venue_id = v.id
              ORDER BY e.date DESC";
    
    $stmt = $this->db->prepare($query);
    $stmt->execute();
    
    $events = [];
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        $events[] = $row;
    }
    return $events;
}

public function getEventWithDetails($id) {
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
                     v.google_maps_url as venue_google_maps_url
              FROM events e
              JOIN subcategories s ON e.subcategory_id = s.id
              JOIN main_categories mc ON s.main_category_id = mc.id
              JOIN venues v ON e.venue_id = v.id
              WHERE e.id = ? LIMIT 1";
    
    $stmt = $this->db->prepare($query);
    $stmt->bindParam(1, $id);
    $stmt->execute();
    
    return $stmt->fetch(PDO::FETCH_ASSOC);
}
}
?>
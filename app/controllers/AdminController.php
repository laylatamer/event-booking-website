<?php
require_once 'models/MainCategory.php';
require_once 'models/Subcategory.php';
require_once 'models/Venue.php';
require_once 'models/Event.php';

class AdminController {
    private $mainCategory;
    private $subcategory;
    private $venue;
    private $event;

    public function __construct(PDO $db) {
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

    // Subcategories CRUD
    public function createSubcategory($data) {
        $this->subcategory->main_category_id = $data['main_category_id'];
        $this->subcategory->name = $data['name'];
        $this->subcategory->status = $data['status'] ?? 'active';
        
        return $this->subcategory->create();
    }

    public function updateSubcategory($id, $data) {
        $this->subcategory->id = $id;
        $this->subcategory->main_category_id = $data['main_category_id'];
        $this->subcategory->name = $data['name'];
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

    // Venues CRUD
    public function createVenue($data) {
        $this->venue->name = $data['name'];
        $this->venue->address = $data['address'];
        $this->venue->city = $data['city'];
        $this->venue->country = $data['country'] ?? 'Egypt';
        $this->venue->capacity = $data['capacity'];
        $this->venue->description = $data['description'];
        $this->venue->facilities = $data['facilities'] ?? [];
        $this->venue->google_maps_url = $data['google_maps_url'];
        $this->venue->image_url = $data['image_url'];
        $this->venue->status = $data['status'] ?? 'active';
        
        return $this->venue->create();
    }

    public function updateVenue($id, $data) {
        $this->venue->id = $id;
        $this->venue->name = $data['name'];
        $this->venue->address = $data['address'];
        $this->venue->city = $data['city'];
        $this->venue->country = $data['country'] ?? 'Egypt';
        $this->venue->capacity = $data['capacity'];
        $this->venue->description = $data['description'];
        $this->venue->facilities = $data['facilities'] ?? [];
        $this->venue->google_maps_url = $data['google_maps_url'];
        $this->venue->image_url = $data['image_url'];
        $this->venue->status = $data['status'] ?? 'active';
        
        return $this->venue->update();
    }

    public function deleteVenue($id) {
        $this->venue->id = $id;
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

    // Events CRUD
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
        
        return $this->event->create();
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
}
?>
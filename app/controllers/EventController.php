<?php
require_once __DIR__ . '/../models/Event.php';
require_once __DIR__ . '/../models/Subcategory.php';
require_once __DIR__ . '/../models/Venue.php';
require_once __DIR__ . '/../models/MainCategory.php';

class EventController {
    private $event;
    private $subcategory;
    private $venue;
    private $mainCategory;

    private $db;

    public function __construct(PDO $db) {
        $this->db = $db; // FIX: Added this line
        $this->event = new Event($db);
        $this->subcategory = new Subcategory($db);
        $this->venue = new Venue($db);
        $this->mainCategory = new MainCategory($db);

    }

    public function getAllEvents() {
        $stmt = $this->event->readAll();
        $events = [];
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $events[] = $this->formatEventForDisplay($row);
        }
        return $events;
    }

    public function getEventById($id) {
        $this->event->id = $id;
        $eventData = $this->event->readOne();
        if ($eventData) {
            return $this->formatEventForDetail($eventData);
        }
        return null;
    }

    public function getEventsByMainCategory($category) {
        $stmt = $this->event->getByCategory($category);
        $events = [];
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $events[] = $this->formatEventForDisplay($row);
        }
        return $events;
    }

    public function getUpcomingEvents($limit = 10) {
        $stmt = $this->event->getUpcoming($limit);
        $events = [];
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $events[] = $this->formatEventForDisplay($row);
        }
        return $events;
    }

    public function getAllSubcategories() {
        $stmt = $this->subcategory->readAll();
        $subcategories = [];
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $subcategories[] = $row;
        }
        return $subcategories;
    }

    public function getAllVenues() {
        $stmt = $this->venue->readAll();
        $venues = [];
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $venues[] = $row;
        }
        return $venues;
    }

    // Add this method - Get subcategories with event counts
    public function getSubcategoriesByMainCategoryName($categoryName) {
        // Get subcategories for a specific main category
        $query = "SELECT s.id, s.name, s.image_url
                  FROM subcategories s
                  JOIN main_categories m ON s.main_category_id = m.id
                  WHERE m.name = ? AND s.status = 'active' AND m.status = 'active'
                  ORDER BY s.name ASC";
        
        $stmt = $this->db->prepare($query);
        $stmt->bindParam(1, $categoryName);
        $stmt->execute();
        
        $subcategories = [];
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            // Count events for this subcategory (shows 0 if no events)
            $countQuery = "SELECT COUNT(*) as event_count 
                          FROM events 
                          WHERE subcategory_id = ? AND status = 'active' AND date >= NOW()";
            $countStmt = $this->db->prepare($countQuery);
            $countStmt->bindParam(1, $row['id']);
            $countStmt->execute();
            $countResult = $countStmt->fetch(PDO::FETCH_ASSOC);
            $eventCount = $countResult['event_count'] ?? 0;
            
            $subcategories[] = [
                'id' => $row['id'],
                'name' => $row['name'],
                'image_url' => $row['image_url'], // Now includes image_url from database
                'main_category' => $categoryName,
                'event_count' => (int)$eventCount
            ];
        }
        
        return $subcategories;
    }

    private function formatEventForDisplay($eventData) {
        $date = new DateTime($eventData['date']);
        
        // Normalize image URL
        $imageUrl = $eventData['image_url'] ?? '';
        if (!empty($imageUrl) && !preg_match('/^https?:\/\//', $imageUrl)) {
            // If relative path, make it absolute
            if (strpos($imageUrl, '/') !== 0) {
                $imageUrl = '/' . ltrim($imageUrl, '/');
            }
        }
        
        return [
            'id' => $eventData['id'],
            'title' => $eventData['title'],
            'description' => substr($eventData['description'], 0, 150) . '...',
            'category' => $eventData['main_category_name'],
            'subcategory' => $eventData['subcategory_name'],
            'date' => $eventData['date'],
            'formattedDate' => $date->format('M d | h:i A'),
            'price' => $eventData['price'],
            'formattedPrice' => '$' . number_format($eventData['price'], 2),
            'location' => $eventData['venue_name'],
            'venue_city' => $eventData['venue_city'],
            'image' => $imageUrl,
            'image_url' => $imageUrl, // Also include for compatibility
            'available_tickets' => $eventData['available_tickets'],
            'status' => $eventData['status']
        ];
    }

    private function formatEventForDetail($eventData) {
        $date = new DateTime($eventData['date']);
        $endDate = $eventData['end_date'] ? new DateTime($eventData['end_date']) : null;
        
        // Handle gallery_images - could be JSON string or array
        $galleryImages = [];
        if (!empty($eventData['gallery_images'])) {
            if (is_string($eventData['gallery_images'])) {
                $decoded = json_decode($eventData['gallery_images'], true);
                $galleryImages = is_array($decoded) ? $decoded : [];
            } elseif (is_array($eventData['gallery_images'])) {
                $galleryImages = $eventData['gallery_images'];
            }
        }
        
        // Handle additional_info - could be JSON string or array
        $additionalInfo = [];
        if (!empty($eventData['additional_info'])) {
            if (is_string($eventData['additional_info'])) {
                $decoded = json_decode($eventData['additional_info'], true);
                $additionalInfo = is_array($decoded) ? $decoded : [];
            } elseif (is_array($eventData['additional_info'])) {
                $additionalInfo = $eventData['additional_info'];
            }
        }
        
        return [
            'id' => $eventData['id'],
            'title' => $eventData['title'],
            'description' => $eventData['description'],
            'main_category' => $eventData['main_category_name'],
            'subcategory' => $eventData['subcategory_name'],
            'date' => $eventData['date'],
            'formattedDate' => $date->format('l, F j, Y'),
            'formattedTime' => $date->format('h:i A'),
            'formattedDateTime' => $date->format('l, F j, Y') . ' at ' . $date->format('h:i A'),
            'end_date' => $eventData['end_date'],
            'formattedEndDate' => $endDate ? $endDate->format('l, F j, Y') : null,
            'formattedEndTime' => $endDate ? $endDate->format('h:i A') : null,
            'price' => $eventData['price'],
            'discounted_price' => $eventData['discounted_price'],
            'formattedPrice' => '$' . number_format($eventData['price'], 2),
            'formattedDiscountedPrice' => $eventData['discounted_price'] ? '$' . number_format($eventData['discounted_price'], 2) : null,
            'image' => $this->normalizeImageUrl($eventData['image_url'] ?? ''),
            'image_url' => $this->normalizeImageUrl($eventData['image_url'] ?? ''),
            'gallery_images' => array_map(function($img) { return $this->normalizeImageUrl($img); }, $galleryImages),
            'total_tickets' => $eventData['total_tickets'],
            'available_tickets' => $eventData['available_tickets'],
            'min_tickets_per_booking' => $eventData['min_tickets_per_booking'],
            'max_tickets_per_booking' => $eventData['max_tickets_per_booking'],
            'terms_conditions' => $eventData['terms_conditions'],
            'additional_info' => $additionalInfo,
            'status' => $eventData['status'],
            // Venue details
            'venue' => [
                'id' => $eventData['venue_id'],
                'name' => $eventData['venue_name'],
                'address' => $eventData['venue_address'],
                'city' => $eventData['venue_city'],
                'country' => $eventData['venue_country'],
                'capacity' => $eventData['venue_capacity'],
                'description' => $eventData['venue_description'],
                'facilities' => !empty($eventData['venue_facilities']) ? (is_string($eventData['venue_facilities']) ? json_decode($eventData['venue_facilities'], true) : $eventData['venue_facilities']) : [],
                'google_maps_url' => $eventData['venue_google_maps_url'],
                'seating_type' => $eventData['venue_seating_type'] ?? null
            ]
        ];
    }

    public function getCategoryClass($category) {
        $classes = [
            'Sports' => 'bg-blue-600',
            'Entertainment' => 'bg-purple-600',
            'Music' => 'bg-red-600',
            'Art' => 'bg-indigo-600',
            'Food' => 'bg-pink-600',
            'Technology' => 'bg-yellow-600',
            'Nightlife' => 'bg-indigo-800',
            'Comedy' => 'bg-orange-600',
            'Festival' => 'bg-green-600',
            'Workshop' => 'bg-teal-600'
        ];
        return $classes[$category] ?? 'bg-gray-600';
    }

     public function getEventsByMainCategoryName($categoryName) {
        $query = "SELECT e.*, 
                         s.name as subcategory_name,
                         s.main_category_id,
                         mc.name as main_category_name,
                         v.name as venue_name,
                         v.city as venue_city,
                         v.address as venue_address
                  FROM events e
                  JOIN subcategories s ON e.subcategory_id = s.id
                  JOIN main_categories mc ON s.main_category_id = mc.id
                  JOIN venues v ON e.venue_id = v.id
                  WHERE mc.name = :category_name 
                  AND e.status = 'active'
                  AND e.date >= NOW()
                  ORDER BY e.date ASC";
        
        $stmt = $this->db->prepare($query);
        $stmt->bindParam(":category_name", $categoryName);
        $stmt->execute();
        
        $events = [];
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $events[] = $this->formatEventForDisplay($row);
        }
        return $events;
    }

    public function getEventsByMainCategoryId($categoryId) {
        $query = "SELECT e.*, 
                         s.name as subcategory_name,
                         s.main_category_id,
                         mc.name as main_category_name,
                         v.name as venue_name,
                         v.city as venue_city,
                         v.address as venue_address
                  FROM events e
                  JOIN subcategories s ON e.subcategory_id = s.id
                  JOIN main_categories mc ON s.main_category_id = mc.id
                  JOIN venues v ON e.venue_id = v.id
                  WHERE mc.id = :category_id 
                  AND e.status = 'active'
                  AND e.date >= NOW()
                  ORDER BY e.date ASC";
        
        $stmt = $this->db->prepare($query);
        $stmt->bindParam(":category_id", $categoryId);
        $stmt->execute();
        
        $events = [];
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $events[] = $this->formatEventForDisplay($row);
        }
        return $events;
    }

    public function getAllActiveEvents() {
        $query = "SELECT e.*, 
                         s.name as subcategory_name,
                         s.main_category_id,
                         mc.name as main_category_name,
                         v.name as venue_name,
                         v.city as venue_city,
                         v.address as venue_address
                  FROM events e
                  JOIN subcategories s ON e.subcategory_id = s.id
                  JOIN main_categories mc ON s.main_category_id = mc.id
                  JOIN venues v ON e.venue_id = v.id
                  WHERE e.status = 'active'
                  AND e.date >= NOW()
                  ORDER BY e.date ASC";
        
        $stmt = $this->db->prepare($query);
        $stmt->execute();
        
        $events = [];
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $events[] = $this->formatEventForDisplay($row);
        }
        return $events;
    }

    public function getMainCategoriesWithEvents() {
        $query = "SELECT DISTINCT mc.id, mc.name, mc.status
                  FROM main_categories mc
                  JOIN subcategories s ON mc.id = s.main_category_id
                  JOIN events e ON s.id = e.subcategory_id
                  WHERE e.status = 'active'
                  AND e.date >= NOW()
                  ORDER BY mc.name ASC";
        
        $stmt = $this->db->prepare($query);
        $stmt->execute();
        
        $categories = [];
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $categories[] = $row;
        }
        return $categories;
    }

    public function getVenuesWithEvents() {
        $query = "SELECT DISTINCT v.id, v.name, v.city
                  FROM venues v
                  JOIN events e ON v.id = e.venue_id
                  WHERE e.status = 'active'
                  AND e.date >= NOW()
                  ORDER BY v.name ASC";
        
        $stmt = $this->db->prepare($query);
        $stmt->execute();
        
        $venues = [];
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $venues[] = $row;
        }
        return $venues;
    }

    public function getEventForPublicDisplay($id) {
        $query = "SELECT e.*, 
                         s.name as subcategory_name,
                         s.main_category_id,
                         mc.name as main_category_name,
                         v.name as venue_name,
                         v.city as venue_city,
                         v.address as venue_address,
                         v.country as venue_country,
                         v.capacity as venue_capacity,
                         v.description as venue_description,
                         v.facilities as venue_facilities,
                         v.google_maps_url as venue_google_maps_url
                  FROM events e
                  JOIN subcategories s ON e.subcategory_id = s.id
                  JOIN main_categories mc ON s.main_category_id = mc.id
                  JOIN venues v ON e.venue_id = v.id
                  WHERE e.id = :id 
                  AND e.status = 'active'";
        
        $stmt = $this->db->prepare($query);
        $stmt->bindParam(":id", $id);
        $stmt->execute();
        
        if ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            return $this->formatEventForDetail($row);
        }
        return null;
    }

}
?>
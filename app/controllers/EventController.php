<?php
require_once __DIR__ . '/../models/Event.php';
require_once __DIR__ . '/../models/Subcategory.php';
require_once __DIR__ . '/../models/Venue.php';

class EventController {
    private $event;
    private $subcategory;
    private $venue;

    public function __construct(PDO $db) {
        $this->event = new Event($db);
        $this->subcategory = new Subcategory($db);
        $this->venue = new Venue($db);
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

    private function formatEventForDisplay($eventData) {
        $date = new DateTime($eventData['date']);
        
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
            'image' => $eventData['image_url'],
            'available_tickets' => $eventData['available_tickets'],
            'status' => $eventData['status']
        ];
    }

    private function formatEventForDetail($eventData) {
        $date = new DateTime($eventData['date']);
        $endDate = $eventData['end_date'] ? new DateTime($eventData['end_date']) : null;
        
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
            'image' => $eventData['image_url'],
            'gallery_images' => $eventData['gallery_images'] ?? [],
            'total_tickets' => $eventData['total_tickets'],
            'available_tickets' => $eventData['available_tickets'],
            'min_tickets_per_booking' => $eventData['min_tickets_per_booking'],
            'max_tickets_per_booking' => $eventData['max_tickets_per_booking'],
            'terms_conditions' => $eventData['terms_conditions'],
            'additional_info' => $eventData['additional_info'] ?? [],
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
                'facilities' => json_decode($eventData['venue_facilities'], true),
                'google_maps_url' => $eventData['venue_google_maps_url']
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
}
?>
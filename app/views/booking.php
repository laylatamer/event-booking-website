<?php
// Start session
require_once __DIR__ . '/../../database/session_init.php';
require_once __DIR__ . '/../../config/db_connect.php';
require_once __DIR__ . '/../../app/controllers/EventController.php';

// Get event ID from URL
$eventId = $_GET['id'] ?? null;

if (!$eventId) {
    // Redirect to homepage if no event ID provided
    header('Location: homepage.php');
    exit;
}

// Fetch event from database
try {
    $database = new Database();
    $db = $database->getConnection();
    $eventController = new EventController($db);
    $eventData = $eventController->getEventById($eventId);
    
    // Fetch ticket categories
    require_once __DIR__ . '/../../app/models/EventTicketCategory.php';
    $ticketCategoryModel = new EventTicketCategory($db);
    
    try {
        $ticketCategoriesStmt = $ticketCategoryModel->getByEventId($eventId);
        $ticketCategories = [];
        while ($row = $ticketCategoriesStmt->fetch(PDO::FETCH_ASSOC)) {
            $ticketCategories[] = $row;
        }
    } catch (Exception $e) {
        error_log("Error fetching ticket categories: " . $e->getMessage());
        $ticketCategories = [];
    }
    
    if (!$eventData) {
        // Event not found - set defaults
        $event = null;
        $venueSeatingType = null;
    } else {
        // Build full location from venue data
        $fullLocation = $eventData['venue']['name'];
        if (!empty($eventData['venue']['address'])) {
            $fullLocation .= ', ' . $eventData['venue']['address'];
        }
        if (!empty($eventData['venue']['city'])) {
            $fullLocation .= ', ' . $eventData['venue']['city'];
        }
        if (!empty($eventData['venue']['country'])) {
            $fullLocation .= ', ' . $eventData['venue']['country'];
        }
        
        // Gallery images are already decoded by EventController
        $galleryImages = $eventData['gallery_images'] ?? [];
        
        // Map database event data to booking page format
        $event = [
            'id' => (int)$eventData['id'],
            'title' => $eventData['title'],
            'description' => $eventData['description'] ?? '',
            'date' => $eventData['date'],
            'location' => $eventData['venue']['name'],
            'fullLocation' => $fullLocation,
            'category' => $eventData['main_category'] ?? $eventData['subcategory'],
            'subcategory' => $eventData['subcategory'],
            'price' => (float)$eventData['price'],
            'discounted_price' => $eventData['discounted_price'] ? (float)$eventData['discounted_price'] : null,
            'image' => $eventData['image'] ?: 'https://placehold.co/1200x630/1f2937/f1f1f1?text=Event',
            'gallery' => $galleryImages,
            'organizer' => $eventData['venue']['name'],
            'available_tickets' => (int)$eventData['available_tickets'],
            'total_tickets' => (int)$eventData['total_tickets'],
            'min_tickets_per_booking' => (int)$eventData['min_tickets_per_booking'],
            'max_tickets_per_booking' => (int)$eventData['max_tickets_per_booking'],
            'terms_conditions' => $eventData['terms_conditions'] ?? '',
            'additional_info' => $eventData['additional_info'] ?? [],
            'venue' => $eventData['venue'],
            'ticket_categories' => $ticketCategories
        ];
        
        // Get venue seating type
        $venueSeatingType = $eventData['venue']['seating_type'] ?? null;
        
        // Fetch venue seating type if not in event data
        if (!$venueSeatingType) {
            require_once __DIR__ . '/../../app/models/Venue.php';
            $venueModel = new Venue($db);
            $venueModel->id = $eventData['venue']['id'];
            if ($venueModel->readOne()) {
                $venueSeatingType = $venueModel->seating_type;
            }
        }
    }
} catch (Exception $e) {
    error_log("Error fetching event: " . $e->getMessage());
    // Fallback to default "Event Not Found"
    $event = null;
    $venueSeatingType = null;
    $ticketCategories = [];
}


// Function to get category class
function getCategoryClass($category) {
    switch(strtolower($category)) {
        case 'music': return 'bg-purple-600';
        case 'sports': return 'bg-blue-600';
        case 'theater': return 'bg-red-600';
        case 'festival': return 'bg-green-600';
        case 'conference': return 'bg-yellow-600';
        case 'food': return 'bg-pink-600';
        case 'art': return 'bg-indigo-600';
        case 'entertainment': return 'bg-purple-600';
        case 'concerts': return 'bg-purple-600';
        case 'nightlife': return 'bg-indigo-600';
        case 'workshops': return 'bg-yellow-600';
        case 'comedy': return 'bg-orange-600';
        case 'technology': return 'bg-yellow-600';
        default: return 'bg-gray-600';
    }
}

// Prepare data for rendering
if ($event) {
    $eventTitle = $event['title'];
    $eventDescription = $event['description'];
    $eventImage = $event['image'];
    $eventPrice = $event['price'];
    
    // NEW: Calculate General and VIP prices for display
    $generalPrice = $eventPrice;
    $formattedGeneralPrice = '$' . number_format($generalPrice, 2);
    $vipPrice = $eventPrice * 1.5;
    $formattedVipPrice = '$' . number_format($vipPrice, 2);
    
    $formattedPrice = '$' . number_format($eventPrice, 2); // Base price for detail section

    $categoryClass = getCategoryClass($event['category']);
    $categoryName = ucfirst($event['category']);
    $eventLocation = $event['location'];
    $eventFullLocation = $event['fullLocation'];
    $eventOrganizer = $event['organizer'];

    // Date formatting
    $eventDate = new DateTime($event['date']);
    $formattedDate = $eventDate->format('l, F j, Y');
    $formattedTime = $eventDate->format('h:i A');
    $formattedDateTime = $formattedDate . ' at ' . $formattedTime;
    
    // Use venue data if available
    $venueData = $event['venue'] ?? null;
} else {
    // Event Not Found defaults
    $eventTitle = 'Event Not Found';
    $eventDescription = 'The requested event could not be found. Please return to the events page.';
    $eventImage = 'https://placehold.co/1200x630/1f2937/f1f1f1?text=Event+Not+Found';
    $formattedPrice = '$0.00';
    $generalPrice = 0;
    $formattedGeneralPrice = '$0.00';
    $vipPrice = 0;
    $formattedVipPrice = '$0.00';
    $categoryClass = 'bg-gray-600';
    $categoryName = 'N/A';
    $eventLocation = 'N/A';
    $eventFullLocation = 'N/A';
    $eventOrganizer = 'N/A';
    $formattedDate = 'N/A';
    $formattedTime = 'N/A';
    $formattedDateTime = 'N/A';
}

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
       <title><?php echo $eventTitle; ?> | Eحgzly</title>

    <link rel="stylesheet" href="../../public/css/booking.css">
    <link rel="stylesheet" href="../../public/css/theatre-seating.css">
    <link rel="stylesheet" href="../../public/css/stadium-seating.css">

    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://unpkg.com/feather-icons"></script>
    <script src="https://cdn.jsdelivr.net/npm/vanta@latest/dist/vanta.net.min.js"></script>
    
</head>
<body class="min-h-screen text-white overflow-x-hidden">
    <div id="vanta-bg"></div> 

    <?php
// Assuming 'includes/' is relative to the root, and this file is in 'src/views/'
include 'partials/header.php'; 
?>

    <div class="relative min-h-screen flex flex-col">
        <div id="event-banner" class="event-banner parallax-layer" style="background-image: url('<?php echo $eventImage; ?>');"></div>

        <main class="container mx-auto px-4 md:px-0 -mt-20 z-10 parallax-layer">
            <div class="bg-black bg-opacity-70 backdrop-blur-md rounded-xl p-8 shadow-2xl">
                <div class="flex flex-col md:flex-row gap-8">
                    <div class="md:w-2/3">
                        <h1 id="event-title" class="text-4xl font-bold mb-4"><?php echo $eventTitle; ?></h1>
                        <div class="flex items-center mb-6">
                            <span id="event-category" class="px-3 py-1 text-xs font-semibold rounded-full mr-4 <?php echo $categoryClass; ?>"><?php echo $categoryName; ?></span>
                            <div class="flex items-center text-gray-300 mr-6">
                                <i data-feather="calendar" class="w-4 h-4 mr-2"></i>
                                <span id="event-date"><?php echo $formattedDate; ?></span>
                            </div>
                            <div class="flex items-center text-gray-300">
                                <i data-feather="map-pin" class="w-4 h-4 mr-2"></i>
                                <span id="event-location"><?php echo $eventLocation; ?></span>
                            </div>
                        </div>

                        <div class="prose max-w-none text-gray-300 mb-8">
                            <p id="event-description"><?php echo $eventDescription; ?></p>
                        </div>

                        <h2 class="text-2xl font-bold mb-4 text-orange-400">Event Details</h2>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-8">
                            <div>
                                <h3 class="text-lg font-semibold mb-2">Date & Time</h3>
                                <p id="event-datetime" class="text-gray-300"><?php echo $formattedDateTime; ?></p>
                            </div>
                            <div>
                                <h3 class="text-lg font-semibold mb-2">Location</h3>
                                <p id="event-full-location" class="text-gray-300"><?php echo $eventFullLocation; ?></p>
                            </div>
                            <div>
                                <h3 class="text-lg font-semibold mb-2">Organizer</h3>
                                <p id="event-organizer" class="text-gray-300"><?php echo $eventOrganizer; ?></p>
                            </div>
                            <div>
                                <h3 class="text-lg font-semibold mb-2">Price</h3>
                                <div id="event-price" class="flex items-center">
                                    <span class="text-2xl font-bold text-orange-400 mr-2"><?php echo $formattedPrice; ?></span>
                                    <span class="text-sm text-gray-400">+ fees</span>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="md:w-1/3">
                        <div class="bg-black bg-opacity-50 p-6 rounded-xl sticky top-6 border border-gray-700">
                            <h3 class="text-xl font-bold mb-4">Get Tickets</h3>
                            <div class="space-y-4" id="ticket-categories-sidebar">
                                <?php if (!empty($ticketCategories)): ?>
                                    <?php foreach ($ticketCategories as $category): ?>
                                        <div class="flex justify-between items-center pb-2 border-b border-gray-700">
                                            <div>
                                                <span class="block font-medium"><?php echo htmlspecialchars($category['category_name']); ?></span>
                                                <span class="text-xs text-gray-400"><?php echo number_format($category['available_tickets']); ?> available</span>
                                            </div>
                                            <div class="text-right flex items-center space-x-4">
                                                <span class="block font-bold" data-ticket-type="<?php echo strtolower(str_replace(' ', '-', $category['category_name'])); ?>" data-base-price="<?php echo $category['price']; ?>">$<?php echo number_format($category['price'], 2); ?></span>
                                                <span id="ticket-count-<?php echo strtolower(str_replace(' ', '-', $category['category_name'])); ?>" class="text-lg font-bold text-orange-400 w-6 text-center">0</span>
                                            </div>
                                        </div>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <!-- Fallback for events without categories -->
                                    <div class="flex justify-between items-center pb-2 border-b border-gray-700">
                                        <div>
                                            <span class="block font-medium">General Ticket</span>
                                            <span class="text-xs text-gray-400">Row D-H Admission</span>
                                        </div>
                                        <div class="text-right flex items-center space-x-4">
                                            <span class="block font-bold" data-ticket-type="general" data-base-price="<?php echo $generalPrice; ?>"><?php echo $formattedGeneralPrice; ?></span>
                                            <span id="ticket-count-general" class="text-lg font-bold text-orange-400 w-6 text-center">0</span>
                                        </div>
                                    </div>
                                    <div class="flex justify-between items-center pb-2 border-b border-gray-700">
                                        <div>
                                            <span class="block font-medium">VIP Ticket</span>
                                            <span class="text-xs text-gray-400">Row A-C Admission</span>
                                        </div>
                                        <div class="text-right flex items-center space-x-4">
                                            <span class="block font-bold" data-ticket-type="vip" data-base-price="<?php echo $vipPrice; ?>"><?php echo $formattedVipPrice; ?></span>
                                            <span id="ticket-count-vip" class="text-lg font-bold text-orange-400 w-6 text-center">0</span>
                                        </div>
                                    </div>
                                <?php endif; ?>
                            </div>

                                <div class="text-center text-sm text-gray-400 font-medium mb-4 pt-2">
                                    <span id="selected-seats-count" class="text-orange-400 font-bold">0</span> Total Tickets Selected (+$5.99 Fee/Ticket)
                                </div>
                                
                                <div class="flex space-x-2">
                                    <button id="open-seat-modal-btn" class="w-1/3 py-3 px-4 text-md font-bold rounded-lg text-white bg-orange-600 transition duration-300 hover:bg-orange-700 hover:shadow-lg flex items-center justify-center" aria-label="Open Seat Selection Map">
                                        <i data-feather="grid" class="w-5 h-5"></i>
                                    </button>
                                    <button id="checkout-btn" data-event-id="<?php echo $event ? $event['id'] : ($eventId ?? ''); ?>" class="w-2/3 py-3 text-md font-bold rounded-lg text-white transition duration-300 gradient-bg opacity-50 cursor-not-allowed hover:shadow-lg hover:shadow-orange-700/50" disabled>
                                        Proceed to Checkout
                                    </button>
                                </div>
                                
                                <p class="text-center text-xs text-gray-400 mb-4">
                                    By proceeding, you agree to the <a href="#" id="open-terms-modal" class="text-orange-400 hover:text-orange-300 underline font-medium">Terms & Conditions</a>.
                                </p>
                                
                            </div>
                        </div>
                    </div>
                </div>

                <div class="mt-12">
                    <h2 class="text-2xl font-bold mb-6 text-orange-400">Venue Overview</h2>
                    <div class="bg-black bg-opacity-30 rounded-xl p-6 mb-8">
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                            <div>
                                <h3 class="text-lg font-semibold mb-4 flex items-center">
                                    <i data-feather="map-pin" class="w-5 h-5 mr-2 text-orange-400"></i>
                                    Location Details
                                </h3>
                                <div id="venue-details" class="space-y-3">
                                    <p class="flex items-start">
                                        <i data-feather="home" class="w-4 h-4 mr-2 mt-1"></i>
                                        <span><?php echo $eventFullLocation; ?></span>
                                    </p>
                                    <?php if ($event && !empty($event['venue']['description'])): ?>
                                    <p class="flex items-start">
                                        <i data-feather="info" class="w-4 h-4 mr-2 mt-1"></i>
                                        <span><?php echo htmlspecialchars($event['venue']['description']); ?></span>
                                    </p>
                                    <?php endif; ?>
                                    <?php if ($event && !empty($event['venue']['capacity'])): ?>
                                    <p class="flex items-start">
                                        <i data-feather="users" class="w-4 h-4 mr-2 mt-1"></i>
                                        <span>Capacity: <?php echo number_format($event['venue']['capacity']); ?> people</span>
                                    </p>
                                    <?php endif; ?>
                                    <?php if ($event && !empty($event['venue']['facilities']) && is_array($event['venue']['facilities'])): ?>
                                        <?php foreach ($event['venue']['facilities'] as $facility): ?>
                                        <p class="flex items-start">
                                            <i data-feather="check-circle" class="w-4 h-4 mr-2 mt-1"></i>
                                            <span><?php echo htmlspecialchars($facility); ?></span>
                                        </p>
                                        <?php endforeach; ?>
                                    <?php endif; ?>
                                    <?php if ($event && !empty($event['additional_info']['age_restriction'])): ?>
                                    <p class="flex items-start">
                                        <i data-feather="shield" class="w-4 h-4 mr-2 mt-1"></i>
                                        <span>Age restriction: <?php echo htmlspecialchars($event['additional_info']['age_restriction']); ?></span>
                                    </p>
                                    <?php endif; ?>
                                </div>
                            </div>
                            <div>
                                <h3 class="text-lg font-semibold mb-4 flex items-center">
                                    <i data-feather="navigation" class="w-5 h-5 mr-2 text-orange-400"></i>
                                    Getting There
                                </h3>
                                <div class="space-y-3">
                                    <?php if ($event && !empty($event['additional_info']['parking'])): ?>
                                    <p class="flex items-start">
                                        <i data-feather="car" class="w-4 h-4 mr-2 mt-1"></i>
                                        <span><?php echo htmlspecialchars($event['additional_info']['parking']); ?></span>
                                    </p>
                                    <?php else: ?>
                                    <p class="flex items-start">
                                        <i data-feather="car" class="w-4 h-4 mr-2 mt-1"></i>
                                        <span>Parking information available at venue</span>
                                    </p>
                                    <?php endif; ?>
                                    <?php if ($event && !empty($event['additional_info']['transportation'])): ?>
                                    <p class="flex items-start">
                                        <i data-feather="train" class="w-4 h-4 mr-2 mt-1"></i>
                                        <span><?php echo htmlspecialchars($event['additional_info']['transportation']); ?></span>
                                    </p>
                                    <?php else: ?>
                                    <p class="flex items-start">
                                        <i data-feather="map-pin" class="w-4 h-4 mr-2 mt-1"></i>
                                        <span>Check venue location for transportation options</span>
                                    </p>
                                    <?php endif; ?>
                                    <?php if ($event && !empty($event['additional_info']['doors_open'])): ?>
                                    <p class="flex items-start">
                                        <i data-feather="clock" class="w-4 h-4 mr-2 mt-1"></i>
                                        <span>Doors open: <?php echo htmlspecialchars($event['additional_info']['doors_open']); ?></span>
                                    </p>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                        <div class="mt-8">
                            <?php if ($event && !empty($event['venue']['google_maps_url'])): ?>
                                <iframe
                                    src="<?php echo htmlspecialchars($event['venue']['google_maps_url']); ?>"
                                    width="100%"
                                    height="300"
                                    style="border:0;"
                                    allowfullscreen=""
                                    loading="lazy"
                                    class="rounded-lg shadow-lg">
                                </iframe>
                            <?php else: ?>
                                <div class="bg-gray-800 rounded-lg p-8 text-center text-gray-400">
                                    <i data-feather="map" class="w-12 h-12 mx-auto mb-4"></i>
                                    <p>Map location not available</p>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>

                <div class="mt-12">
                    <h2 class="text-2xl font-bold mb-6 text-orange-400">Gallery</h2>
                    <div id="event-gallery" class="grid grid-cols-2 md:grid-cols-4 gap-4">
                        <?php
                        // PHP populates the gallery
                        if ($event && !empty($event['gallery'])) {
                            foreach ($event['gallery'] as $image) {
                                echo '
                                    <div class="overflow-hidden rounded-lg h-40">
                                        <img src="' . $image . '" alt="Event image" class="w-full h-full object-cover hover:scale-110 transition duration-300">
                                    </div>
                                ';
                            }
                        }
                        ?>
                    </div>
                </div>
                
            </div>
        </main>

    </div>
    
    <div id="seating-modal" class="modal-overlay hidden fixed inset-0 z-50 flex items-center justify-center bg-black bg-opacity-80 backdrop-blur-sm p-4" aria-modal="true" role="dialog">
        <div class="bg-gray-800 rounded-xl p-8 max-w-5xl w-full max-h-[90vh] overflow-y-auto shadow-2xl border border-orange-500">
            <div class="flex justify-between items-center mb-6 border-b border-gray-700 pb-3">
                <h3 class="text-2xl font-bold text-orange-400">Select Your Seats</h3>
                <button id="close-seating-modal-btn" class="text-gray-400 hover:text-white transition">
                    <i data-feather="x" class="w-6 h-6"></i>
                </button>
            </div>
            
            <?php 
            // Show layout toggle only if venue has seating type
            if ($venueSeatingType === 'stadium' || $venueSeatingType === 'theatre'): 
            ?>
            <div class="flex justify-center mb-6">
                <div class="inline-flex rounded-lg bg-gray-900 p-1 shadow-inner" role="group">
                    <?php if ($venueSeatingType === 'theatre'): ?>
                    <button id="layout-theatre-btn" data-layout="theatre" class="layout-toggle-btn active-layout px-4 py-2 text-sm font-medium rounded-md text-white bg-orange-600 transition duration-150">
                        Theatre View
                    </button>
                    <?php elseif ($venueSeatingType === 'stadium'): ?>
                    <button id="layout-stadium-btn" data-layout="stadium" class="layout-toggle-btn active-layout px-4 py-2 text-sm font-medium rounded-md text-white bg-orange-600 transition duration-150">
                        Stadium View
                    </button>
                    <?php endif; ?>
                </div>
            </div>
            <?php endif; ?>
            <div class="p-0">
                <div id="seating-map-views">

                    <div id="layout-theatre" class="seating-layout-view <?php echo ($venueSeatingType === 'theatre' || $venueSeatingType === 'standing') ? '' : 'hidden'; ?>">
                        <div id="seating-builder-container" class="bg-gray-900 rounded-xl p-8 shadow-inner">
                            <div class="seating-map-area">
                                <!-- Stage -->
                                <div class="stage">
                                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                        <rect width="20" height="14" x="2" y="7" rx="2" ry="2"></rect>
                                        <path d="M16 21V5a2 2 0 0 0-2-2h-4a2 2 0 0 0-2 2v16"></path>
                                    </svg>
                                    <span>STAGE / SCREEN</span>
                                </div>

                                <!-- Theatre Seating Grid -->
                                <div id="theatre-seats" class="theatre-seating-grid"></div>

                                <!-- Legend -->
                                <div class="legend">
                                    <div class="legend-group">
                                        <div class="legend-item">
                                            <div class="legend-color available"></div>
                                            <span>Available</span>
                                        </div>
                                        <div class="legend-item">
                                            <div class="legend-color selected"></div>
                                            <span>Selected</span>
                                        </div>
                                        <div class="legend-item">
                                            <div class="legend-color booked"></div>
                                            <span>Booked</span>
                                        </div>
                                    </div>
                                    <?php if (!empty($ticketCategories)): ?>
                                    <div class="legend-group">
                                        <?php foreach ($ticketCategories as $category): 
                                            // Map category names to badge classes for Theatre
                                            $badgeClass = 'regular';
                                            if ($category['category_name'] === 'Gold') {
                                                $badgeClass = 'vip';
                                            } elseif ($category['category_name'] === 'Premium') {
                                                $badgeClass = 'premium';
                                            }
                                        ?>
                                        <div class="legend-item">
                                            <div class="legend-badge <?php echo $badgeClass; ?>"></div>
                                            <span><?php echo htmlspecialchars($category['category_name']); ?> - $<?php echo number_format($category['price'], 2); ?></span>
                                        </div>
                                        <?php endforeach; ?>
                                    </div>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div id="layout-stadium" class="seating-layout-view <?php echo $venueSeatingType === 'stadium' ? '' : 'hidden'; ?>">
                        <div id="stadium-builder-container" class="bg-gray-900 rounded-xl p-8 shadow-inner">
                            <div class="seating-map-area">
                                <!-- Stadium Circular Layout -->
                                <div class="stadium-container">
                                    <!-- Playing Field -->
                                    <div class="playing-field">
                                        <div class="field-markings"></div>
                                        <div class="field-center-line"></div>
                                        <div class="field-center-circle"></div>
                                        <div class="field-label">FIELD</div>
                                    </div>

                                    <!-- North Section -->
                                    <div class="stadium-section north-section">
                                        <div class="section-title">North Stand</div>
                                        <div id="north-seats" class="section-seats"></div>
                                    </div>

                                    <!-- South Section -->
                                    <div class="stadium-section south-section">
                                        <div class="section-title">South Stand</div>
                                        <div id="south-seats" class="section-seats"></div>
                                    </div>

                                    <!-- West Section -->
                                    <div class="stadium-section west-section">
                                        <div class="section-title">West</div>
                                        <div id="west-seats" class="section-seats"></div>
                                    </div>

                                    <!-- East Section -->
                                    <div class="stadium-section east-section">
                                        <div class="section-title">East</div>
                                        <div id="east-seats" class="section-seats"></div>
                                    </div>
                                </div>

                                <!-- Legend -->
                                <div class="legend">
                                    <div class="legend-group">
                                        <div class="legend-item">
                                            <div class="legend-color available"></div>
                                            <span>Available</span>
                                        </div>
                                        <div class="legend-item">
                                            <div class="legend-color selected"></div>
                                            <span>Selected</span>
                                        </div>
                                        <div class="legend-item">
                                            <div class="legend-color booked"></div>
                                            <span>Booked</span>
                                        </div>
                                    </div>
                                    <?php if (!empty($ticketCategories)): ?>
                                    <div class="legend-group">
                                        <?php foreach ($ticketCategories as $category): 
                                            // Map category names to badge classes for Stadium
                                            $badgeClass = 'regular';
                                            if ($category['category_name'] === 'Cat1') {
                                                $badgeClass = 'vip';
                                            } elseif ($category['category_name'] === 'Cat2') {
                                                $badgeClass = 'premium';
                                            }
                                        ?>
                                        <div class="legend-item">
                                            <div class="legend-badge <?php echo $badgeClass; ?>"></div>
                                            <span><?php echo htmlspecialchars($category['category_name']); ?> - $<?php echo number_format($category['price'], 2); ?></span>
                                        </div>
                                        <?php endforeach; ?>
                                    </div>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="flex justify-end pt-4">
                 <button id="done-selecting-btn" class="px-6 py-3 text-lg font-bold rounded-lg text-white bg-orange-500 hover:bg-orange-600 transition">
                    Done Selecting
                </button>
            </div>
        </div>
    </div>
    <div id="terms-modal" class="hidden fixed inset-0 z-50 flex items-center justify-center bg-black bg-opacity-80 backdrop-blur-sm p-4">
        <div class="bg-gray-800 rounded-xl p-8 max-w-xl w-full max-h-[80vh] overflow-y-auto shadow-2xl border border-orange-500">
            <div class="flex justify-between items-center mb-6 border-b border-gray-700 pb-3">
                <h2 class="text-2xl font-bold text-orange-400">Event Terms & Conditions</h2>
                <button id="close-modal-btn" class="text-gray-400 hover:text-white transition">
                    <i data-feather="x" class="w-6 h-6"></i>
                </button>
            </div>
            <div class="prose max-w-none text-gray-300">
                <?php if ($event && !empty($event['terms_conditions'])): ?>
                    <?php 
                    // Display terms and conditions from database
                    // If it's HTML, display as-is, otherwise format as paragraphs
                    $terms = $event['terms_conditions'];
                    if (strip_tags($terms) === $terms) {
                        // Plain text - split by newlines and format as paragraphs
                        $termsLines = explode("\n", $terms);
                        foreach ($termsLines as $line) {
                            $line = trim($line);
                            if (!empty($line)) {
                                echo '<p>' . nl2br(htmlspecialchars($line)) . '</p>';
                            }
                        }
                    } else {
                        // HTML content - display as-is (already sanitized in database)
                        echo $terms;
                    }
                    ?>
                <?php else: ?>
                    <p><strong>1. Ticket Purchase:</strong> All sales are final. *Refunds/exchanges only if the event is canceled or postponed*.</p>
                    <p><strong>2. Entry & ID:</strong> Requires a *valid ticket* (printed/mobile) and *government-issued photo ID*. Admission may be refused.</p>
                    <p><strong>3. Conduct:</strong> Follow all rules. *Disorderly conduct/non-compliance will result in immediate ejection without refund*.</p>
                    <p><strong>4. Photography/Recording:</strong> Professional cameras, video, or audio recording devices are *prohibited without organizer consent*.</p>
                    <p><strong>5. Personal Liability:</strong> Organizers are *not responsible* for lost/stolen property or injuries. Attend at your own risk.</p>
                    <p><strong>6. Rescheduling/Cancellation:</strong> Rescheduled tickets remain valid. *Canceled events will be refunded* per organizer policy.</p>
                <?php endif; ?>
            </div>
        </div>
    </div>

    
    <?php
// Assuming includes/header.php and includes/footer.php exist relative to this file's location
include 'partials/footer.php';
?>
    <script>
        // Pass PHP data to JavaScript
        window.eventData = {
            eventId: <?php echo $event ? $event['id'] : 'null'; ?>,
            venueSeatingType: <?php echo $venueSeatingType ? "'" . htmlspecialchars($venueSeatingType, ENT_QUOTES) . "'" : 'null'; ?>,
            ticketCategories: <?php echo json_encode($ticketCategories, JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT); ?>,
            minTickets: <?php echo $event ? $event['min_tickets_per_booking'] : 1; ?>,
            maxTickets: <?php echo $event ? $event['max_tickets_per_booking'] : 10; ?>
        };
    </script>
    <script src="../../public/js/theatre-seating.js"></script>
    <script src="../../public/js/stadium-seating.js"></script>
    <script type="module" src="../../public/js/booking.js"></script>

</body>
</html>
<?php
// Use include_once for safety to load the $events array from the centralized file.
// The path '../../includes/event_data.php' assumes booking.php is in 'projectSoft/src/views/'
// and event_data.php is in 'projectSoft/includes/'.
include_once '../../includes/event_data.php';

// The $events array is now available.
// PHP logic to find the event by ID from the URL (GET parameter)
// If no ID is provided in the URL (e.g., event-details.php?id=1), it defaults to event ID 1.
$eventId = $_GET['id'] ?? 1; // Default to ID 1 to show a valid event on initial load
$event = null;

// Find the event
if (isset($events)) {
    foreach ($events as $e) {
        if ($e['id'] == $eventId) {
            $event = $e;
            break;
        }
    }
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
        default: return 'bg-gray-600';
    }
}

// Prepare data for rendering
if ($event) {
    $eventTitle = $event['title'];
    $eventDescription = $event['description'];
    $eventImage = $event['image'];
    $eventPrice = $event['price'];
    $formattedPrice = '$' . number_format($eventPrice, 2);
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
} else {
    // Event Not Found defaults
    $eventTitle = 'Event Not Found';
    $eventDescription = 'The requested event could not be found. Please return to the events page.';
    $eventImage = 'https://placehold.co/1200x630/1f2937/f1f1f1?text=Event+Not+Found';
    $formattedPrice = '$0.00';
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

    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://unpkg.com/feather-icons"></script>
    <script src="https://cdn.jsdelivr.net/npm/vanta@latest/dist/vanta.net.min.js"></script>
    
</head>
<body class="min-h-screen text-white overflow-x-hidden">
    <!-- Vanta Background container -->
    <div id="vanta-bg"></div> 

    <!-- Assuming includes/header.php and includes/footer.php exist relative to this file's location -->
    <?php
// Assuming 'includes/' is relative to the root, and this file is in 'src/views/'
include '../../includes/header.php'; 
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
                            <div class="space-y-4">
                                <div class="flex justify-between items-center pb-2 border-b border-gray-700">
                                    <div>
                                        <span class="block font-medium">Standard Ticket</span>
                                        <span class="text-xs text-gray-400">General Admission</span>
                                    </div>
                                    <div class="text-right">
                                        <span id="ticket-price" class="block font-bold" data-base-price="<?php echo $event ? $event['price'] : 0; ?>"><?php echo $formattedPrice; ?></span>
                                        <span class="text-xs text-gray-400">+ $5.99 fees</span>
                                    </div>
                                </div>
                                <div class="flex items-center">
                                    <button class="px-3 py-1 bg-gray-700 rounded-l" onclick="updateTicketCount(-1)">
                                        <i data-feather="minus" class="w-4 h-4"></i>
                                    </button>
                                    <span id="ticket-count" class="px-4 py-1 bg-gray-800">1</span>
                                    <button class="px-3 py-1 bg-gray-700 rounded-r" onclick="updateTicketCount(1)">
                                        <i data-feather="plus" class="w-4 h-4"></i>
                                    </button>
                                </div>
                                <button id="checkout-btn" class="w-full gradient-bg hover:bg-opacity-90 text-white font-bold py-3 px-4 rounded-lg transition duration-300 transform hover:scale-105">
                                    Proceed to Checkout
                                </button>
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
                                    <p class="flex items-start">
                                        <i data-feather="clock" class="w-4 h-4 mr-2 mt-1"></i>
                                        <span>Doors open 1 hour before event</span>
                                    </p>
                                    <p class="flex items-start">
                                        <i data-feather="info" class="w-4 h-4 mr-2 mt-1"></i>
                                        <span>Age restriction: 18+</span>
                                    </p>
                                </div>
                            </div>
                            <div>
                                <h3 class="text-lg font-semibold mb-4 flex items-center">
                                    <i data-feather="navigation" class="w-5 h-5 mr-2 text-orange-400"></i>
                                    Getting There
                                </h3>
                                <div class="space-y-3">
                                    <p class="flex items-start">
                                        <i data-feather="car" class="w-4 h-4 mr-2 mt-1"></i>
                                        <span>Parking available ($15 per vehicle)</span>
                                    </p>
                                    <p class="flex items-start">
                                        <i data-feather="train" class="w-4 h-4 mr-2 mt-1"></i>
                                        <span>Nearest metro station: Central Station (0.5 miles)</span>
                                    </p>
                                </div>
                            </div>
                        </div>
                        <div class="mt-8">
                            <iframe
                                src="https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d3305.360356087998!2d-118.2436836847844!3d34.05223418060081!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x80c2c75ddc27da13%3A0xe22fdf6f254608f4!2sLos%20Angeles%2C%20CA!5e0!3m2!1sen!2sus!4v1620000000000!5m2!1sen!2sus"
                                width="100%"
                                height="300"
                                style="border:0;"
                                allowfullscreen=""
                                loading="lazy"
                                class="rounded-lg shadow-lg">
                            </iframe>
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

   
    <?php
// Assuming includes/header.php and includes/footer.php exist relative to this file's location
include '../../includes/footer.php';
?>
    <script type="module" src="../../public/js/booking.js"></script>

</body>
</html>

<?php
// Use include_once for safety to load the $events array from the centralized file.
// The path '../../includes/event_data.php' assumes booking.php is in 'projectSoft/src/views/'
// and event_data.php is in 'projectSoft/includes/'.
include_once 'partials/event_data.php';

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
                            <div class="space-y-4">
                                
                                <div class="flex justify-between items-center pb-2 border-b border-gray-700">
                                    <div>
                                        <span class="block font-medium">General Ticket</span>
                                        <span class="text-xs text-gray-400">Row D-H Admission</span>
                                    </div>
                                    <div class="text-right flex items-center space-x-4">
                                        <span class="block font-bold" data-ticket-type="general" data-base-price="<?php echo $generalPrice; ?>"><?php echo $formattedGeneralPrice; ?></span>
                                        <span id="general-ticket-count" class="text-lg font-bold text-orange-400 w-6 text-center">0</span>
                                    </div>
                                </div>
                                
                                <div class="flex justify-between items-center pb-2 border-b border-gray-700">
                                    <div>
                                        <span class="block font-medium">VIP Ticket</span>
                                        <span class="text-xs text-gray-400">Row A-C Admission</span>
                                    </div>
                                    <div class="text-right flex items-center space-x-4">
                                        <span class="block font-bold" data-ticket-type="vip" data-base-price="<?php echo $vipPrice; ?>"><?php echo $formattedVipPrice; ?></span>
                                        <span id="vip-ticket-count" class="text-lg font-bold text-orange-400 w-6 text-center">0</span>
                                    </div>
                                </div>

                                <div class="text-center text-sm text-gray-400 font-medium mb-4 pt-2">
                                    <span id="selected-seats-count" class="text-orange-400 font-bold">0</span> Total Tickets Selected (+$5.99 Fee/Ticket)
                                </div>
                                
                                <div class="flex space-x-2">
                                    <button id="open-seat-modal-btn" class="w-1/3 py-3 px-4 text-md font-bold rounded-lg text-white bg-orange-600 transition duration-300 hover:bg-orange-700 hover:shadow-lg flex items-center justify-center" aria-label="Open Seat Selection Map">
                                        <i data-feather="grid" class="w-5 h-5"></i>
                                    </button>
                                    <button id="checkout-btn" data-event-id="<?php echo $eventId; ?>" class="w-2/3 py-3 text-md font-bold rounded-lg text-white transition duration-300 gradient-bg opacity-50 cursor-not-allowed hover:shadow-lg hover:shadow-orange-700/50" disabled>
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
    
    <div id="seating-modal" class="modal-overlay hidden fixed inset-0 z-50 flex items-center justify-center bg-black bg-opacity-80 backdrop-blur-sm p-4" aria-modal="true" role="dialog">
        <div class="bg-gray-800 rounded-xl p-8 max-w-5xl w-full max-h-[90vh] overflow-y-auto shadow-2xl border border-orange-500">
            <div class="flex justify-between items-center mb-6 border-b border-gray-700 pb-3">
                <h3 class="text-2xl font-bold text-orange-400">Select Your Seats</h3>
                <button id="close-seating-modal-btn" class="text-gray-400 hover:text-white transition">
                    <i data-feather="x" class="w-6 h-6"></i>
                </button>
            </div>
            
            <div class="flex justify-center mb-6">
                <div class="inline-flex rounded-lg bg-gray-900 p-1 shadow-inner" role="group">
                    <button id="layout-theatre-btn" data-layout="theatre" class="layout-toggle-btn active-layout px-4 py-2 text-sm font-medium rounded-l-md text-white bg-orange-600 transition duration-150">
                        Theatre View
                    </button>
                    <button id="layout-stadium-btn" data-layout="stadium" class="layout-toggle-btn px-4 py-2 text-sm font-medium rounded-r-md text-gray-400 hover:text-white hover:bg-gray-700 transition duration-150">
                        Stadium View
                    </button>
                </div>
            </div>
            <div class="p-0">
                <div id="seating-map-views">

                    <div id="layout-theatre" class="seating-layout-view">
                        <div id="seating-builder-container" class="bg-gray-900 rounded-xl p-8 shadow-inner">
                            <div class="seating-map-area">
                                
                                <div class="stage-container">
                                    <span class="stage-label">STAGE / SCREEN</span>
                                </div>

                                <div class="flex justify-center flex-wrap space-x-6 text-sm text-gray-300 mb-8 mt-4">
                                    <div class="flex items-center mt-2">
                                        <div class="seat legend available mr-2"></div> Available (General)
                                    </div>
                                    <div class="flex items-center mt-2">
                                        <div class="seat legend selected mr-2"></div> Selected
                                    </div>
                                    <div class="flex items-center mt-2">
                                        <div class="seat legend reserved mr-2"></div> Reserved
                                    </div>
                                    <div class="flex items-center mt-2">
                                        <div class="seat legend vip mr-2"></div> VIP (Higher Price)
                                    </div>
                                </div>

                                <div class="seating-rows-container">
                                    
                                    <div class="seating-section vip-section">
                                        <?php 
                                        // Price calculated as 1.5 times the base price
                                        $vipPrice = ($event && isset($eventPrice)) ? $eventPrice * 1.5 : 0;
                                        ?>
                                        <div class="section-label">VIP (A-C) - $<?php echo number_format($vipPrice, 2); ?></div>
                                        <?php for ($i = 65; $i <= 67; $i++): // Rows A, B, C ?>
                                            <div class="seat-row" data-row="<?php echo chr($i); ?>">
                                                <span class="row-label"><?php echo chr($i); ?></span>
                                                <?php for ($j = 1; $j <= 10; $j++): ?>
                                                    <?php $status = ($j % 5 === 0) ? 'reserved' : 'available'; ?>
                                                    <div class="seat vip <?php echo $status; ?>" data-seat-type="vip" data-price="<?php echo $vipPrice; ?>" data-seat-id="<?php echo chr($i) . $j; ?>"></div>
                                                <?php endfor; ?>
                                                <span class="row-label"><?php echo chr($i); ?></span>
                                            </div>
                                        <?php endfor; ?>
                                    </div>

                                    <div class="aisle-space">AISLE</div>

                                    <div class="seating-section general-section">
                                        <?php 
                                        // General price is the base price
                                        $generalPrice = ($event && isset($eventPrice)) ? $eventPrice : 0;
                                        ?>
                                        <div class="section-label">General (D-H) - <?php echo $formattedPrice; ?></div>
                                        <?php for ($i = 68; $i <= 72; $i++): // Rows D, E, F, G, H ?>
                                            <div class="seat-row" data-row="<?php echo chr($i); ?>">
                                                <span class="row-label"><?php echo chr($i); ?></span>
                                                <?php for ($j = 1; $j <= 12; $j++): ?>
                                                    <?php $status = ($j % 7 === 0 || $j % 8 === 0) ? 'reserved' : 'available'; ?>
                                                    <div class="seat <?php echo $status; ?>" data-seat-type="general" data-price="<?php echo $generalPrice; ?>" data-seat-id="<?php echo chr($i) . $j; ?>"></div>
                                                <?php endfor; ?>
                                                <span class="row-label"><?php echo chr($i); ?></span>
                                            </div>
                                        <?php endfor; ?>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div id="layout-stadium" class="seating-layout-view hidden">
                        <div id="stadium-builder-container" class="bg-gray-900 rounded-xl p-8 shadow-inner">
                            <div class="seating-map-area">
                                
                                <div class="stage-container">
                                    <span class="stage-label">FIELD / PITCH</span>
                                </div>
                                
                                <div class="seating-rows-container stadium-seating">
                                    
                                    <div class="seating-section vip-section">
                                        <?php $vipPrice = ($event && isset($eventPrice)) ? $eventPrice * 1.5 : 0; ?>
                                        <div class="section-label">Lower Tier VIP (1-5) - $<?php echo number_format($vipPrice, 2); ?></div>
                                        <?php for ($i = 1; $i <= 5; $i++): // Rows 1-5 ?>
                                            <div class="seat-row" data-row="<?php echo $i; ?>">
                                                <span class="row-label"><?php echo $i; ?></span>
                                                <?php for ($j = 1; $j <= 15; $j++): ?>
                                                    <?php $status = ($j % 8 === 0) ? 'reserved' : 'available'; ?>
                                                    <div class="seat vip <?php echo $status; ?>" data-seat-type="vip" data-price="<?php echo $vipPrice; ?>" data-seat-id="<?php echo 'S' . $i . '-' . $j; ?>"></div>
                                                <?php endfor; ?>
                                                <span class="row-label"><?php echo $i; ?></span>
                                            </div>
                                        <?php endfor; ?>
                                    </div>

                                    <div class="aisle-space">CONCOURSE</div>

                                    <div class="seating-section general-section">
                                        <?php $generalPrice = ($event && isset($eventPrice)) ? $eventPrice : 0; ?>
                                        <div class="section-label">Upper Tier General (6-12) - <?php echo $formattedPrice; ?></div>
                                        <?php for ($i = 6; $i <= 12; $i++): // Rows 6-12 ?>
                                            <div class="seat-row" data-row="<?php echo $i; ?>">
                                                <span class="row-label"><?php echo $i; ?></span>
                                                <?php for ($j = 1; $j <= 20; $j++): ?>
                                                    <?php $status = ($j % 10 === 0 || $j % 11 === 0) ? 'reserved' : 'available'; ?>
                                                    <div class="seat <?php echo $status; ?>" data-seat-type="general" data-price="<?php echo $generalPrice; ?>" data-seat-id="<?php echo 'S' . $i . '-' . $j; ?>"></div>
                                                <?php endfor; ?>
                                                <span class="row-label"><?php echo $i; ?></span>
                                            </div>
                                        <?php endfor; ?>
                                    </div>
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
                <p><strong>1. Ticket Purchase:</strong> All sales are final. *Refunds/exchanges only if the event is canceled or postponed*.</p>
                <p><strong>2. Entry & ID:</strong> Requires a *valid ticket* (printed/mobile) and *government-issued photo ID*. Admission may be refused.</p>
                <p><strong>3. Conduct:</strong> Follow all rules. *Disorderly conduct/non-compliance will result in immediate ejection without refund*.</p>
                <p><strong>4. Photography/Recording:</strong> Professional cameras, video, or audio recording devices are *prohibited without organizer consent*.</p>
                <p><strong>5. Personal Liability:</strong> Organizers are *not responsible* for lost/stolen property or injuries. Attend at your own risk.</p>
                <p><strong>6. Rescheduling/Cancellation:</strong> Rescheduled tickets remain valid. *Canceled events will be refunded* per organizer policy.</p>
                </div>
        </div>
    </div>

    
    <?php
// Assuming includes/header.php and includes/footer.php exist relative to this file's location
include 'partials/footer.php';
?>
    <script type="module" src="../../public/js/booking.js"></script>

</body>
</html>
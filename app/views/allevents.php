<?php
// app/views/allevents.php
require_once __DIR__ . '/../../config/db_connect.php';
require_once __DIR__ . '/../../app/controllers/EventController.php';

// Create database connection and controller
$database = new Database();
$db = $database->getConnection();
$eventController = new EventController($db);

// Fetch all active events
$events = $eventController->getAllActiveEvents();

// Fetch categories and venues for filters
$categories = $eventController->getMainCategoriesWithEvents();
$venues = $eventController->getVenuesWithEvents();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>All Events & Activities</title>

    <script type="module" src="https://unpkg.com/lucide@latest/dist/umd/lucide.js"></script>
    <link rel="stylesheet" href="../../public/css/allevents.css">
    
</head>
<body class="page-body">
<?php
include 'partials/header.php';
?>
    
    <main class="main-content">
        
        <h1 class="page-title">Entertainment & Sports</h1>

        <section class="filter-section filter-bg">
            <div class="filter-buttons-list" id="filter-buttons-container">
                
                <button data-filter-type="date" class="filter-btn-base active-filter" id="filter-date-btn">
                    <i data-lucide="calendar"></i> All Dates
                </button>
                <button data-filter-type="category" class="filter-btn-base" id="filter-category-btn">
                    <i data-lucide="blocks"></i> All Categories
                </button>
                <button data-filter-type="venue" class="filter-btn-base" id="filter-venue-btn">
                    <i data-lucide="map-pin"></i> All Venues
                </button>

                <button id="reset-filters-btn" class="filter-btn-base">
                    <i data-lucide="x-circle"></i> Reset Filters
                </button>
                
            </div>
        </section>

        <section class="events-grid" id="events-grid">
            <?php if (empty($events)): ?>
                <div class="loading-indicator">No upcoming events found.</div>
            <?php else: ?>
                <?php foreach ($events as $event): ?>
                    <div class="event-card-base" data-event-id="<?php echo $event['id']; ?>">
                        <div class="event-image-container">
                            <img src="<?php echo htmlspecialchars($event['image']); ?>" 
                                 onerror="this.onerror=null; this.src='https://placehold.co/400x400/2a2a2a/f97316?text=<?php echo urlencode($event['category']); ?>'" 
                                 alt="<?php echo htmlspecialchars($event['title']); ?>" 
                                 class="event-card-img">
                            <span class="event-category-tag"><?php echo htmlspecialchars($event['category']); ?></span>
                        </div>
                        <div class="event-details">
                            <h3 class="event-title"><?php echo htmlspecialchars($event['title']); ?></h3>
                            <p class="event-date"><?php echo $event['formattedDate']; ?></p>
                            <p class="event-venue"><?php echo htmlspecialchars($event['location']); ?></p>
                            <button class="book-now-button" onclick="window.location.href='booking.php?id=<?php echo $event['id']; ?>'">
                                Book Now
                            </button>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </section>

    </main>

    <div id="date-modal" class="modal-overlay" aria-modal="true" role="dialog">
        <div class="modal-content-wrapper">
            <div class="modal-header">
                <h3 class="modal-title">Select Date</h3>
                <button data-modal-id="date-modal" class="modal-close-btn">
                    <i data-lucide="x" class="w-6 h-6"></i>
                </button>
            </div>
            <div class="modal-inner-content">
                <div class="date-picker-container">
                    <div class="calendar-nav">
                        <button id="prev-month">
                            <i data-lucide="chevron-left" class="w-6 h-6"></i>
                        </button>
                        <span class="font-semibold" id="current-month-year" style="font-size: 1.125rem;">October 2025</span>
                        <button id="next-month">
                            <i data-lucide="chevron-right" class="w-6 h-6"></i>
                        </button>
                    </div>

                    <div class="calendar-header-day">
                        <span>Su</span><span>Mo</span><span>Tu</span><span>We</span><span>Th</span><span>Fr</span><span>Sa</span>
                    </div>

                    <div class="calendar-days" id="calendar-days">
                        </div>
                </div>
            </div>
            <div class="modal-footer">
                <button data-filter-type="date" class="apply-filter-btn">
                    Apply Date Filter
                </button>
            </div>
        </div>
    </div>


    <div id="category-modal" class="modal-overlay" aria-modal="true" role="dialog">
        <div class="modal-content-wrapper">
            <div class="modal-header">
                <h3 class="modal-title">Select Category</h3>
                <button data-modal-id="category-modal" class="modal-close-btn">
                    <i data-lucide="x" class="w-6 h-6"></i>
                </button>
            </div>
            <div class="modal-inner-content" id="category-list-container">
                <div class="filter-list-item" data-value="Entertainment">Entertainment</div>
                <div class="filter-list-item" data-value="Nightlife">Nightlife</div>
                <div class="filter-list-item" data-value="Activities">Activities</div>
                <div class="filter-list-item" data-value="Art & Theatre">Art & Theatre</div>
                <div class="filter-list-item" data-value="Concerts">Concerts</div>
                <div class="filter-list-item" data-value="Comedy">Comedy</div>
                <div class="filter-list-item" data-value="Sports">Sports</div>
                <div class="filter-list-item" data-value="Football">Football</div>
                <div class="filter-list-item" data-value="Festivals">Festivals</div>
                <div class="filter-list-item" data-value="Workshops">Workshops</div>
            </div>
            <div class="modal-footer">
                <button data-filter-type="category" class="apply-filter-btn">
                    Apply Category Filter
                </button>
            </div>
        </div>
    </div>


    <div id="venue-modal" class="modal-overlay" aria-modal="true" role="dialog">
        <div class="modal-content-wrapper">
            <div class="modal-header">
                <h3 class="modal-title">Select Venue</h3>
                <button data-modal-id="venue-modal" class="modal-close-btn">
                    <i data-lucide="x" class="w-6 h-6"></i>
                </button>
            </div>
            <div class="modal-inner-content" id="venue-list-container">
                <div class="filter-list-item" data-value="Cairo Jazz Club 610">Cairo Jazz Club 610</div>
                <div class="filter-list-item" data-value="CJC Agouza">CJC Agouza</div>
                <div class="filter-list-item" data-value="Royal Park Mall">Royal Park Mall</div>
                <div class="filter-list-item" data-value="Theatro Gallery">Theatro Gallery</div>
                <div class="filter-list-item" data-value="El Arena">El Arena</div>
                <div class="filter-list-item" data-value="AUC Tahrir">AUC Tahrir</div>
                <div class="filter-list-item" data-value="Giza Pyramids">Giza Pyramids</div>
                </div>
            <div class="modal-footer">
                <button data-filter-type="venue" class="apply-filter-btn">
                    Apply Venue Filter
                </button>
            </div>
        </div>
    </div>

    <div id="blurb-modal" class="modal-overlay" aria-modal="true" role="dialog">
        <div class="modal-content-wrapper">
            <div class="modal-header">
                <h3 class="modal-title" id="blurb-modal-title">✨ Event Promo Blurb</h3>
                <button data-modal-id="blurb-modal" id="close-blurb-modal-btn" class="modal-close-btn">
                    <i data-lucide="x" class="w-6 h-6"></i>
                </button>
            </div>
            <div id="modal-content" class="modal-inner-content">
                <div class="loading-spinner">
                    <i data-lucide="loader-circle"></i>
                    <p>loading...</p>
                </div>
            </div>
            <div class="modal-footer" style="text-align: right;">
                <button data-modal-id="blurb-modal" class="nav-link" style="color: #FF5722; font-weight: 600; background: none; border: none; cursor: pointer;">
                    Got it!
                </button>
            </div>
        </div>
    </div>
   

    <script>
        const allEvents = <?php echo json_encode($events); ?>;
        const API_BASE = '../../public/api/events_API.php';
        
        // Initialize Lucide icons
        document.addEventListener('DOMContentLoaded', function() {
            if (typeof lucide !== 'undefined') {
                lucide.createIcons();
            }
        });
    </script>
    <script type="module" src="../../public/js/allevents.js"></script>
    <?php
    // Include the footer file
    include 'partials/footer.php';
    ?>
</body>
</html>
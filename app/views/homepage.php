<?php
// Start session
require_once __DIR__ . '/../../database/session_init.php';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tickets | Home</title>
    <link rel="stylesheet" href="../../public/css/header.css">
    <link rel="stylesheet" href="../../public/css/navbar.css">
    <link rel="stylesheet" href="../../public/css/footer.css">
    <link rel="stylesheet" href="../../public/css/homepage.css">
</head>
<body>
    <?php
    // Include the header file
    include 'partials/header.php';
    
    // Connect to database and get events
    require_once __DIR__ . '/../../config/db_connect.php';
    require_once __DIR__ . '/../../app/controllers/EventController.php';
    
    
    try {
        $database = new Database();
        $db = $database->getConnection();
        
        $eventController = new EventController($db);
        
        // Get upcoming events (limit to 5 for the slider)
        $upcomingEvents = $eventController->getUpcomingEvents(5);
        
        // Get entertainment categories (NEW - Add these lines)
        $entertainmentSubcategories = $eventController->getSubcategoriesByMainCategoryName('Entertainment');
        
        // Get sports categories (NEW - Add these lines)
        $sportsSubcategories = $eventController->getSubcategoriesByMainCategoryName('Sports');
        
    } catch (Exception $e) {
        // Handle error gracefully
        $upcomingEvents = [];
        $entertainmentSubcategories = []; // NEW
        $sportsSubcategories = []; // NEW
        error_log("Error fetching events: " . $e->getMessage());
    }
    ?>
    
    <!-- Events slider -->
    <section id="events-slider" class="events-slider-section" aria-roledescription="carousel" aria-label="Available events" aria-live="polite">
        <div class="slider-container">
            <div class="slider-track" id="sliderTrack">
                <?php if (empty($upcomingEvents)): ?>
                    <!-- Fallback if no events -->
                    <article class="event-card" data-event>
                        <div class="event-content">
                            <div class="event-header">
                                <h3 class="event-title">No Upcoming Events</h3>
                                <div class="event-sub">Check back soon for new events!</div>
                                <div class="event-venue">Coming Soon</div>
                            </div>
                            <div class="organized">Stay tuned</div>
                            <div class="event-actions">
                                <a href="allevents.php" class="btn primary"><span class="icon" aria-hidden="true">📅</span>View All Events</a>
                            </div>
                        </div>
                        <div class="event-media" style="background-image: url('../../public/img/default-event.jpg'); background-position: center; background-size: cover;">
                            <span class="date-badge">Soon</span>
                        </div>
                    </article>
                <?php else: ?>
                    <?php foreach ($upcomingEvents as $event): ?>
                        <?php
                        // Format dates
                        $eventDate = new DateTime($event['date']);
                        $formattedDate = $eventDate->format('D, M d');
                        $displayDate = $eventDate->format('M d | h:i A');
                        
                        // Default image if none provided
                        $imageUrl = !empty($event['image']) ? $event['image'] : '../../public/img/default-event.jpg';
                        ?>
                        <article class="event-card" data-event data-event-id="<?php echo $event['id']; ?>">
                            <div class="event-content">
                                <div class="event-header">
                                    <h3 class="event-title"><?php echo htmlspecialchars($event['title']); ?></h3>
                                    <div class="event-sub"><?php echo $displayDate; ?></div>
                                    <div class="event-venue"><?php echo htmlspecialchars($event['venue_city']); ?></div>
                                </div>
                                <div class="organized">Category: <?php echo htmlspecialchars($event['category']); ?></div>
                                <div class="org-logos">
                                    <span class="org-logo"><?php echo htmlspecialchars($event['category']); ?></span>
                                </div>
                                <div class="event-actions">
                                    <a href="booking.php?id=<?php echo $event['id']; ?>" class="btn primary">
                                        <span class="icon" aria-hidden="true">💳</span>Book Now
                                    </a>
                                    <a href="event-details.php?id=<?php echo $event['id']; ?>" class="btn secondary">More Info</a>
                                </div>
                            </div>
                            <div class="event-media" style="background-image: url('<?php echo $imageUrl; ?>'); background-position: center; background-size: cover;">
                                <span class="date-badge"><?php echo $formattedDate; ?></span>
                            </div>
                        </article>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
            <div class="slider-controls" aria-hidden="false">
                <button class="slider-btn prev" id="prevBtn" aria-label="Previous event" title="Previous">
                    ‹
                </button>
                <button class="slider-btn next" id="nextBtn" aria-label="Next event" title="Next">
                    ›
                </button>
            </div>
        </div>
        <div class="slider-dots" id="sliderDots" role="tablist" aria-label="Event slides"></div>
    </section>

    <!-- Entertainment Categories Carousel - REPLACED SECTION -->
    <section class="categories-section" id="categories">
        <div class="categories-header">
            <h2 class="categories-title">Explore Entertainment</h2>
            <?php if (!empty($entertainmentSubcategories)): ?>
                <div class="cat-controls">
                    <button class="cat-btn" id="catPrev" aria-label="Previous categories">⟵</button>
                    <button class="cat-btn" id="catNext" aria-label="Next categories">⟶</button>
                </div>
            <?php endif; ?>
        </div>
        <div class="cat-viewport">
            <div class="cat-track" id="catTrack">
                <?php if (empty($entertainmentSubcategories)): ?>
                    <!-- Fallback if no entertainment categories in database -->
                    <div class="cat-card">
                        <div class="cat-media" style="background-image: url('https://images.unsplash.com/photo-1514525253161-7a46d19cd819?q=80&w=1200&auto=format&fit=crop');"></div>
                        <div class="cat-info">
                            <div class="cat-meta">
                                <div class="cat-name">Coming Soon</div>
                                <div class="cat-count">0 Events</div>
                            </div>
                            <button class="cat-arrow" onclick="viewCategory('entertainment')" aria-label="View entertainment events">
                                <span>→</span>
                            </button>
                        </div>
                    </div>
                <?php else: ?>
                    <?php foreach ($entertainmentSubcategories as $subcategory): ?>
                        <?php
                        // Default image if none provided
                        $catImage = !empty($subcategory['image_url']) 
                            ? $subcategory['image_url'] 
                            : 'https://images.unsplash.com/photo-1514525253161-7a46d19cd819?q=80&w=1200&auto=format&fit=crop';
                        ?>
                        <div class="cat-card">
                            <div class="cat-media" style="background-image: url('<?php echo $catImage; ?>');"></div>
                            <div class="cat-info">
                                <div class="cat-meta">
                                    <div class="cat-name"><?php echo htmlspecialchars($subcategory['name']); ?></div>
                                    <div class="cat-count"><?php echo $subcategory['event_count']; ?> Events</div>
                                </div>
                                <button class="cat-arrow" onclick="viewCategory('<?php echo $subcategory['id']; ?>', '<?php echo htmlspecialchars($subcategory['name']); ?>')" 
                                        aria-label="View <?php echo htmlspecialchars($subcategory['name']); ?> events">
                                    <span>→</span>
                                </button>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </div>
    </section>

    <!-- Sports Categories Carousel - REPLACED SECTION -->
    <section class="categories-section" id="sports">
        <div class="categories-header">
            <h2 class="categories-title">Explore Sports</h2>
            <?php if (!empty($sportsSubcategories)): ?>
                <div class="cat-controls">
                    <button class="cat-btn" id="sportsPrev" aria-label="Previous sports">⟵</button>
                    <button class="cat-btn" id="sportsNext" aria-label="Next sports">⟶</button>
                </div>
            <?php endif; ?>
        </div>
        <div class="cat-viewport">
            <div class="cat-track" id="sportsTrack">
                <?php if (empty($sportsSubcategories)): ?>
                    <!-- Fallback if no sports categories in database -->
                    <div class="cat-card">
                        <div class="cat-media" style="background-image: url('https://images.unsplash.com/photo-1431324155629-1a6deb1dec8d?q=80&w=1200&auto=format&fit=crop');"></div>
                        <div class="cat-info">
                            <div class="cat-meta">
                                <div class="cat-name">Coming Soon</div>
                                <div class="cat-count">0 Events</div>
                            </div>
                            <button class="cat-arrow" onclick="viewCategory('sports')" aria-label="View sports events">
                                <span>→</span>
                            </button>
                        </div>
                    </div>
                <?php else: ?>
                    <?php foreach ($sportsSubcategories as $subcategory): ?>
                        <?php
                        // Default image if none provided
                        $catImage = !empty($subcategory['image_url']) 
                            ? $subcategory['image_url'] 
                            : 'https://images.unsplash.com/photo-1431324155629-1a6deb1dec8d?q=80&w=1200&auto=format&fit=crop';
                        ?>
                        <div class="cat-card">
                            <div class="cat-media" style="background-image: url('<?php echo $catImage; ?>');"></div>
                            <div class="cat-info">
                                <div class="cat-meta">
                                    <div class="cat-name"><?php echo htmlspecialchars($subcategory['name']); ?></div>
                                    <div class="cat-count"><?php echo $subcategory['event_count']; ?> Events</div>
                                </div>
                                <button class="cat-arrow" onclick="viewCategory('<?php echo $subcategory['id']; ?>', '<?php echo htmlspecialchars($subcategory['name']); ?>')" 
                                        aria-label="View <?php echo htmlspecialchars($subcategory['name']); ?> events">
                                    <span>→</span>
                                </button>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </div>
    </section>
    <?php
    // Include the footer file
    include 'partials/footer.php';
    ?>
    
    <script src="../../public/js/homepage.js"></script>
    <script src="../../public/js/navbar.js"></script>
    
    <script>
    // Update the viewCategory function to handle dynamic categories
    function viewCategory(categoryId, categoryName) {
        console.log(`Viewing category: ${categoryName} (ID: ${categoryId})`);
        
        // Redirect to category events page
        // You can create a category-events.php page or use allevents.php with filter
        window.location.href = `allevents.php?category=${categoryId}`;
    }
    
    // Optional: Update existing JavaScript to work with dynamic categories
    document.addEventListener('DOMContentLoaded', function() {
        // Your existing homepage.js will still work
        // The carousel controls will work with the new dynamic categories
    });
    </script>
</body>
</html>
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
include 'includes/header.php';
?>
        <!-- Events slider -->
        <section id="events-slider" class="events-slider-section" aria-roledescription="carousel" aria-label="Available events" aria-live="polite">
            <div class="slider-container">
                <div class="slider-track" id="sliderTrack">
                  
                    <article class="event-card" data-event>
                        <div class="event-content">
                            <div class="event-header">
                                <h3 class="event-title">Ali Quandil: Accept Laugh Interact</h3>
                                <div class="event-sub">Oct 24 | 08:00 PM</div>
                                <div class="event-venue">Theatro Arkan</div>
                            </div>
                            <div class="organized">Organized by</div>
                            <div class="org-logos">
                                <span class="org-logo">Theatro</span>
                                <span class="org-logo">Org</span>
                            </div>
                            <div class="event-actions">
                                <a href="#" class="btn primary"><span class="icon" aria-hidden="true">💳</span>Book Now</a>
                                <a href="#" class="btn secondary">More Info</a>
                            </div>
                        </div>
                        <div class="event-media" style="background-image: url('../../public/img/ali-qndeel.jpg'); background-position: center; background-size: cover;">
                            <span class="date-badge">Fri, Nov 21</span>
                        </div>
                    </article>
                    <article class="event-card" data-event>
                        <div class="event-content">
                            <div class="event-header">
                                <h3 class="event-title">Mediterranean Food Fest</h3>
                                <div class="event-sub">Sat, Dec 7 | 06:00 PM</div>
                                <div class="event-venue">Alexandria, Egypt</div>
                            </div>
                            <div class="organized">Organized by</div>
                            <div class="org-logos">
                                <span class="org-logo">Gastro</span>
                                <span class="org-logo">City</span>
                            </div>
                            <div class="event-actions">
                                <a href="#" class="btn primary"><span class="icon" aria-hidden="true">💳</span>Book Now</a>
                                <a href="#" class="btn secondary">More Info</a>
                            </div>
                        </div>
                        <div class="event-media" style="background-image: url('../../public/img/food fest.jpg'); background-position: center; background-size: cover;">
                            <span class="date-badge">Sat, Dec 7</span>
                        </div>
                    </article>
                    <article class="event-card" data-event>
                        <div class="event-content">
                            <div class="event-header">
                                <h3 class="event-title">Pyramids Light Show</h3>
                                <div class="event-sub">Thu, Jan 2 | 09:30 PM</div>
                                <div class="event-venue">Giza, Egypt</div>
                            </div>
                            <div class="organized">Organized by</div>
                            <div class="org-logos">
                                <span class="org-logo">Heritage</span>
                                <span class="org-logo">Tourism</span>
                            </div>
                            <div class="event-actions">
                                <a href="#" class="btn primary"><span class="icon" aria-hidden="true">💳</span>Book Now</a>
                                <a href="#" class="btn secondary">More Info</a>
                            </div>
                        </div>
                        <div class="event-media" style="background-image: url('../../public/img/pyramids.jpg'); background-position: center; background-size: cover;">
                            <span class="date-badge">Thu, Jan 2</span>
                        </div>
                    </article>
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

        <!-- Categories carousel-->
        <section class="categories-section" id="categories">
            <div class="categories-header">
                <h2 class="categories-title">Explore Entertainment</h2>
                <div class="cat-controls">
                    <button class="cat-btn" id="catPrev" aria-label="Previous categories">⟵</button>
                    <button class="cat-btn" id="catNext" aria-label="Next categories">⟶</button>
                </div>
            </div>
            <div class="cat-viewport">
                <div class="cat-track" id="catTrack">
                    <div class="cat-card">
                        <div class="cat-media" style="background-image: url('https://images.unsplash.com/photo-1514525253161-7a46d19cd819?q=80&w=1200&auto=format&fit=crop');"></div>
                        <div class="cat-info">
                            <div class="cat-meta">
                                <div class="cat-name">Nightlife</div>
                                <div class="cat-count">6 Events</div>
                            </div>
                            <button class="cat-arrow" onclick="viewCategory('nightlife')" aria-label="View Nightlife events">
                                <span>→</span>
                            </button>
                        </div>
                    </div>
                    <div class="cat-card">
                        <div class="cat-media" style="background-image: url('https://images.unsplash.com/photo-1483412033650-1015ddeb83d1?q=80&w=1200&auto=format&fit=crop');"></div>
                        <div class="cat-info">
                            <div class="cat-meta">
                                <div class="cat-name">Concerts</div>
                                <div class="cat-count">13 Events</div>
                            </div>
                            <button class="cat-arrow" onclick="viewCategory('concerts')" aria-label="View Concerts events">
                                <span>→</span>
                            </button>
                        </div>
                    </div>
                    <div class="cat-card">
                        <div class="cat-media" style="background-image: url('https://images.unsplash.com/photo-1511671782779-c97d3d27a1d4?q=80&w=1200&auto=format&fit=crop');"></div>
                        <div class="cat-info">
                            <div class="cat-meta">
                                <div class="cat-name">Comedy</div>
                                <div class="cat-count">2 Events</div>
                            </div>
                            <button class="cat-arrow" onclick="viewCategory('comedy')" aria-label="View Comedy events">
                                <span>→</span>
                            </button>
                        </div>
                    </div>
                    <div class="cat-card">
                        <div class="cat-media" style="background-image: url('https://images.unsplash.com/photo-1549880338-65ddcdfd017b?q=80&w=1200&auto=format&fit=crop');"></div>
                        <div class="cat-info">
                            <div class="cat-meta">
                                <div class="cat-name">Art & Theatre</div>
                                <div class="cat-count">26 Events</div>
                            </div>
                            <button class="cat-arrow" onclick="viewCategory('art-theatre')" aria-label="View Art & Theatre events">
                                <span>→</span>
                            </button>
                        </div>
                    </div>
                    <div class="cat-card">
                        <div class="cat-media" style="background-image: url('https://images.unsplash.com/photo-1500530855697-b586d89ba3ee?q=80&w=1200&auto=format&fit=crop');"></div>
                        <div class="cat-info">
                            <div class="cat-meta">
                                <div class="cat-name">Summit</div>
                                <div class="cat-count">4 Events</div>
                            </div>
                            <button class="cat-arrow" onclick="viewCategory('summit')" aria-label="View Summit events">
                                <span>→</span>
                            </button>
                        </div>
                    </div>
                    <div class="cat-card">
                        <div class="cat-media" style="background-image: url('https://images.unsplash.com/photo-1500534314209-a25ddb2bd429?q=80&w=1200&auto=format&fit=crop');"></div>
                        <div class="cat-info">
                            <div class="cat-meta">
                                <div class="cat-name">Activities</div>
                                <div class="cat-count">9 Events</div>
                            </div>
                            <button class="cat-arrow" onclick="viewCategory('activities')" aria-label="View Activities events">
                                <span>→</span>
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <!-- Sports carousel -->
        <section class="categories-section" id="sports">
            <div class="categories-header">
                <h2 class="categories-title">Explore Sports</h2>
                <div class="cat-controls">
                    <button class="cat-btn" id="sportsPrev" aria-label="Previous sports">⟵</button>
                    <button class="cat-btn" id="sportsNext" aria-label="Next sports">⟶</button>
                </div>
            </div>
            <div class="cat-viewport">
                <div class="cat-track" id="sportsTrack">
                    <div class="cat-card">
                        <div class="cat-media" style="background-image: url('https://images.unsplash.com/photo-1431324155629-1a6deb1dec8d?q=80&w=1200&auto=format&fit=crop');"></div>
                        <div class="cat-info">
                            <div class="cat-meta">
                                <div class="cat-name">Football</div>
                                <div class="cat-count">6 Events</div>
                            </div>
                            <button class="cat-arrow" onclick="viewCategory('football')" aria-label="View Football events">
                                <span>→</span>
                            </button>
                        </div>
                    </div>
                    <div class="cat-card">
                        <div class="cat-media" style="background-image: url('https://images.unsplash.com/photo-1546519638-68e109498ffc?q=80&w=1200&auto=format&fit=crop');"></div>
                        <div class="cat-info">
                            <div class="cat-meta">
                                <div class="cat-name">Basketball</div>
                                <div class="cat-count">12 Events</div>
                            </div>
                            <button class="cat-arrow" onclick="viewCategory('basketball')" aria-label="View Basketball events">
                                <span>→</span>
                            </button>
                        </div>
                    </div>
                    <div class="cat-card">
                        <div class="cat-media" style="background-image: url('https://images.unsplash.com/photo-1571019613454-1cb2f99b2d8b?q=80&w=1200&auto=format&fit=crop');"></div>
                        <div class="cat-info">
                            <div class="cat-meta">
                                <div class="cat-name">Tennis</div>
                                <div class="cat-count">8 Events</div>
                            </div>
                            <button class="cat-arrow" onclick="viewCategory('tennis')" aria-label="View Tennis events">
                                <span>→</span>
                            </button>
                        </div>
                    </div>
                    <div class="cat-card">
                        <div class="cat-media" style="background-image: url('https://images.unsplash.com/photo-1549719386-74dfcbf7dbed?q=80&w=1200&auto=format&fit=crop');"></div>
                        <div class="cat-info">
                            <div class="cat-meta">
                                <div class="cat-name">Boxing</div>
                                <div class="cat-count">6 Events</div>
                            </div>
                            <button class="cat-arrow" onclick="viewCategory('boxing')" aria-label="View Boxing events">
                                <span>→</span>
                            </button>
                        </div>
                    </div>
                    <div class="cat-card">
                        <div class="cat-media" style="background-image: url('https://images.unsplash.com/photo-1551698618-1dfe5d97d256?q=80&w=1200&auto=format&fit=crop');"></div>
                        <div class="cat-info">
                            <div class="cat-meta">
                                <div class="cat-name">Handball</div>
                                <div class="cat-count">4 Events</div>
                            </div>
                            <button class="cat-arrow" onclick="viewCategory('handball')" aria-label="View Handball events">
                                <span>→</span>
                            </button>
                        </div>
                    </div>
                    <div class="cat-card">
                        <div class="cat-media" style="background-image: url('https://images.unsplash.com/photo-1574629810360-7efbbe195018?q=80&w=1200&auto=format&fit=crop');"></div>
                        <div class="cat-info">
                            <div class="cat-meta">
                                <div class="cat-name">Volleyball</div>
                                <div class="cat-count">7 Events</div>
                            </div>
                            <button class="cat-arrow" onclick="viewCategory('volleyball')" aria-label="View Volleyball events">
                                <span>→</span>
                            </button>
                        </div>
                    </div>
                    <div class="cat-card">
                        <div class="cat-media" style="background-image: url('https://images.unsplash.com/photo-1554068865-24cecd4e34b8?q=80&w=1200&auto=format&fit=crop');"></div>
                        <div class="cat-info">
                            <div class="cat-meta">
                                <div class="cat-name">Swimming</div>
                                <div class="cat-count">5 Events</div>
                            </div>
                            <button class="cat-arrow" onclick="viewCategory('swimming')" aria-label="View Swimming events">
                                <span>→</span>
                            </button>
                        </div>
                    </div>
                    <div class="cat-card">
                        <div class="cat-media" style="background-image: url('https://images.unsplash.com/photo-1578662996442-48f60103fc96?q=80&w=1200&auto=format&fit=crop');"></div>
                        <div class="cat-info">
                            <div class="cat-meta">
                                <div class="cat-name">Athletics</div>
                                <div class="cat-count">9 Events</div>
                            </div>
                            <button class="cat-arrow" onclick="viewCategory('athletics')" aria-label="View Athletics events">
                                <span>→</span>
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </section>
    </div>

    

    <?php
// Include the footer file
include 'includes/footer.php';
?>
<script src="../../public/js/homepage.js"></script>
<script src="../../public/js/navbar.js"></script>
</body>
</html>
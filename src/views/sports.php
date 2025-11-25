<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>All Events & Activities - Dark Mode</title>
    <script type="module" src="https://unpkg.com/lucide@latest/dist/umd/lucide.js"></script>
    <link rel="stylesheet" href="../../public/css/allevents.css">
   
</head>
<body class="page-body">
<?php
// Include the header file
include '../../includes/header.php';
?>
   

    <!-- Main Content Area -->
    <main class="main-content">
        
        <!-- Page Title -->
        <h1 class="page-title">Sports</h1>

        <!-- Filter Bar Section -->
        <section class="filter-section filter-bg">
            <div class="filter-buttons-list" id="filter-buttons-container">
                
                <!-- Filter Buttons -->
                <button data-filter-type="date" class="filter-btn-base active-filter" id="filter-date-btn">
                    <i data-lucide="calendar"></i> All Dates
                </button>
                <button data-filter-type="category" class="filter-btn-base" id="filter-category-btn">
                    <i data-lucide="blocks"></i> All Categories
                </button>
                <button data-filter-type="venue" class="filter-btn-base" id="filter-venue-btn">
                    <i data-lucide="map-pin"></i> All Venues
                </button>

                <!-- NEW: Reset Filter Button -->
                <button id="reset-filters-btn" class="filter-btn-base">
                    <i data-lucide="x-circle"></i> Reset Filters
                </button>
                
            </div>
        </section>

        <!-- Events Grid -->
        <section class="events-grid" id="events-grid">
            <div id="loading-indicator" class="loading-indicator">Loading events...</div>
        </section>

    </main>

    <!-- --------------------------------------- MODALS --------------------------------------- -->

    <!-- 1. Date Picker Modal -->
    <div id="date-modal" class="modal-overlay" aria-modal="true" role="dialog">
        <div class="modal-content-wrapper">
            <div class="modal-header">
                <h3 class="modal-title">Select Date</h3>
                <button data-modal-id="date-modal" class="modal-close-btn">
                    <i data-lucide="x" class="w-6 h-6"></i>
                </button>
            </div>
            <div class="modal-inner-content">
                <!-- Calendar Component -->
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
                        <!-- Days injected by JS -->
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


    <!-- 2. Category Selection Modal -->
    <div id="category-modal" class="modal-overlay" aria-modal="true" role="dialog">
        <div class="modal-content-wrapper">
            <div class="modal-header">
                <h3 class="modal-title">Select Category</h3>
                <button data-modal-id="category-modal" class="modal-close-btn">
                    <i data-lucide="x" class="w-6 h-6"></i>
                </button>
            </div>
            <div class="modal-inner-content" id="category-list-container">
                <!-- Category items -->
                <div class="filter-list-item" data-value="Nightlife">Nightlife</div>
                <div class="filter-list-item" data-value="Activities">Activities</div>
                <div class="filter-list-item" data-value="Art & Theatre">Art & Theatre</div>
                <div class="filter-list-item" data-value="Concerts">Concerts</div>
                <div class="filter-list-item" data-value="Comedy">Comedy</div>
                <div class="filter-list-item" data-value="Sports">Sports</div>
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


    <!-- 3. Venue Selection Modal -->
    <div id="venue-modal" class="modal-overlay" aria-modal="true" role="dialog">
        <div class="modal-content-wrapper">
            <div class="modal-header">
                <h3 class="modal-title">Select Venue</h3>
                <button data-modal-id="venue-modal" class="modal-close-btn">
                    <i data-lucide="x" class="w-6 h-6"></i>
                </button>
            </div>
            <div class="modal-inner-content" id="venue-list-container">
                <!-- Venue items -->
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

    <!-- 4. Event Blurb Modal -->
    <div id="blurb-modal" class="modal-overlay" aria-modal="true" role="dialog">
        <div class="modal-content-wrapper">
            <div class="modal-header">
                <h3 class="modal-title" id="blurb-modal-title">✨ Event Promo Blurb</h3>
                <button data-modal-id="blurb-modal" id="close-blurb-modal-btn" class="modal-close-btn">
                    <i data-lucide="x" class="w-6 h-6"></i>
                </button>
            </div>
            <div id="modal-content" class="modal-inner-content">
                <!-- Content injected here -->
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


    <!-- --------------------------------------- JAVASCRIPT --------------------------------------- -->
    <script type="module" src="../../public/js/sports.js"></script>
    <?php
// Include the footer file
include '../../includes/footer.php';
?>
</body>
</html>

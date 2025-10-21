<?php
// =================================================================================
// CANONICAL EVENT DATA SOURCE
// This file is included by both 'allevents.php' and 'event-details.php'
// =================================================================================
$events = [
    // 🎭 Comedy
    [ 
        'id' => 1, 
        'title' => "The Comedy Bunch", 
        'date' => "2025-10-21T21:00:00", // Corrected to ISO 8601
        'location' => "Cairo Jazz Club 610", 
        'fullLocation' => "Cairo Jazz Club 610, Sheikh Zayed, Giza",
        'category' => "Comedy", 
        'price' => 250.00,
        'image' => "https://images.unsplash.com/photo-1511671782779-c97d3d27a1d4?w=400&h=400&fit=crop",
        'organizer' => "Laugh Factory Productions",
        'description' => "An evening with the nation's funniest stand-up comedians. Expect non-stop laughs, sharp wit, and unexpected punchlines. Adult language warning.",
        'gallery' => [
            "http://static.photos/comedy/320x240/1a",
            "http://static.photos/comedy/320x240/1b",
            "http://static.photos/comedy/320x240/1c",
            "http://static.photos/comedy/320x240/1d"
        ]
    ],

    // 🎶 Music Concert
    [ 
        'id' => 2, 
        'title' => "Amira Adeeb Live", 
        'date' => "2025-10-22T21:00:00", 
        'location' => "CJC Agouza", 
        'fullLocation' => "CJC Agouza, 15 El Batal Ahmed Abdel Aziz, Giza",
        'category' => "Concerts", // Updated from 'Music' to match filtering modal
        'price' => 350.00,
        'image' => "https://images.unsplash.com/photo-1506157786151-b8491531f063?w=400&h=400&fit=crop",
        'organizer' => "Soul Music Events",
        'description' => "The sensational Amira Adeeb performs her latest album live, blending soulful melodies with modern electronic beats. Limited capacity, book early.",
        'gallery' => [
            "http://static.photos/music/320x240/2a",
            "http://static.photos/music/320x240/2b",
            "http://static.photos/music/320x240/2c",
            "http://static.photos/music/320x240/2d"
        ]
    ],

    // 🎸 Music Concert
    [ 
        'id' => 3, 
        'title' => "Wall Of Sound", 
        'date' => "2025-10-23T21:00:00", 
        'location' => "Theatro Gallery", 
        'fullLocation' => "Theatro Gallery, 10th Street, Heliopolis, Cairo",
        'category' => "Concerts",
        'price' => 180.00,
        'image' => "https://images.unsplash.com/photo-1511671782779-c97d3d27a1d4?w=400&h=400&fit=crop",
        'organizer' => "Rock Nation Co.",
        'description' => "An electrifying night of alternative rock and post-punk from the acclaimed band 'Wall of Sound'. High energy and powerful performance guaranteed.",
        'gallery' => [
            "http://static.photos/rock/320x240/3a",
            "http://static.photos/rock/320x240/3b",
            "http://static.photos/rock/320x240/3c",
            "http://static.photos/rock/320x240/3d"
        ]
    ],

    // 🎷 Music Concert
    [ 
        'id' => 4, 
        'title' => "Jazz Fusion Night", 
        'date' => "2025-10-24T20:00:00", 
        'location' => "El Arena", 
        'fullLocation' => "El Arena, 100 Main Street, Downtown Cairo",
        'category' => "Concerts",
        'price' => 300.00,
        'image' => "https://images.unsplash.com/photo-1511379938547-c1f69419868d?w=400&h=400&fit=crop",
        'organizer' => "Cairo Jazz Foundation",
        'description' => "A special evening dedicated to the best of modern and classic Jazz Fusion. Featuring international guest musicians.",
        'gallery' => [
            "http://static.photos/jazz/320x240/4a",
            "http://static.photos/jazz/320x240/4b",
            "http://static.photos/jazz/320x240/4c",
            "http://static.photos/jazz/320x240/4d"
        ]
    ],

    // 🎨 Activities / Workshop
    [ 
        'id' => 5, 
        'title' => "Beginner's Art Workshop", 
        'date' => "2025-10-25T10:00:00", 
        'location' => "Royal Park Mall", 
        'fullLocation' => "Royal Park Mall, Studio 305, New Cairo",
        'category' => "Workshops", // Updated from 'Activities' to match filtering modal
        'price' => 150.00,
        'image' => "https://images.unsplash.com/photo-1521334884684-d80222895322?w=400&h=400&fit=crop",
        'organizer' => "Art Oasis Collective",
        'description' => "A hands-on workshop focused on painting fundamentals and colour theory. All materials are provided. Perfect for beginners and enthusiasts.",
        'gallery' => [
            "http://static.photos/art/320x240/5a",
            "http://static.photos/art/320x240/5b",
            "http://static.photos/art/320x240/5c",
            "http://static.photos/art/320x240/5d"
        ]
    ],

    // 🌃 Nightlife
    [ 
        'id' => 6, 
        'title' => "Nightlife Festival", 
        'date' => "2025-10-26T23:00:00", 
        'location' => "AUC Tahrir", 
        'fullLocation' => "AUC Tahrir, Main Hall Grounds, Downtown Cairo",
        'category' => "Nightlife", 
        'price' => 500.00,
        'image' => "https://images.unsplash.com/photo-1506157786151-b8491531f063?w=400&h=400&fit=crop",
        'organizer' => "Metro Events Group",
        'description' => "The city's largest indoor nightlife event featuring multiple stages, top international DJs, and a VIP section. Dress to impress.",
        'gallery' => [
            "http://static.photos/nightlife/320x240/6a",
            "http://static.photos/nightlife/320x240/6b",
            "http://static.photos/nightlife/320x240/6c",
            "http://static.photos/nightlife/320x240/6d"
        ]
    ],

    // ⚽ Football Premier League 1
    [ 
        'id' => 7, 
        'title' => "ZED FC vs Petrojet", 
        'date' => "2025-10-19T17:00:00", 
        'location' => "Cairo Stadium", 
        'fullLocation' => "Cairo International Stadium, Nasr City, Cairo",
        'category' => "Sports", 
        'price' => 100.00,
        'image' => "https://images.unsplash.com/photo-1508804185872-d7badad00f7d?w=400&h=400&fit=crop",
        'organizer' => "Egyptian Football Association",
        'description' => "A crucial match in the domestic league. See the rising ZED FC take on the veteran team Petrojet.",
        'gallery' => [
            "http://static.photos/sport/320x240/7a",
            "http://static.photos/sport/320x240/7b",
            "http://static.photos/sport/320x240/7c",
            "http://static.photos/sport/320x240/7d"
        ]
    ],

    // ⚽ Football Premier League 2
    [ 
        'id' => 8, 
        'title' => "Ceramica vs Talea El Geish", 
        'date' => "2025-10-19T20:00:00", 
        'location' => "30 June Stadium", 
        'fullLocation' => "30 June Stadium, New Cairo, Cairo",
        'category' => "Sports", 
        'price' => 90.00,
        'image' => "https://images.unsplash.com/photo-1521412644187-c49fa049e84d?w=400&h=400&fit=crop",
        'organizer' => "Egyptian Football Association",
        'description' => "Mid-table clash with both teams fighting for a European spot. High-stakes and thrilling action expected.",
        'gallery' => [
            "http://static.photos/sport/320x240/8a",
            "http://static.photos/sport/320x240/8b",
            "http://static.photos/sport/320x240/8c",
            "http://static.photos/sport/320x240/8d"
        ]
    ],

    // ⚽ Football Premier League 3
    [ 
        'id' => 9, 
        'title' => "Pyramids FC vs Pharco FC", 
        'date' => "2025-10-21T17:00:00", 
        'location' => "Air Defense Stadium", 
        'fullLocation' => "Air Defense Stadium, Fifth Settlement, Cairo",
        'category' => "Sports", 
        'price' => 120.00,
        'image' => "https://images.unsplash.com/photo-1517927033932-b3d18e61fb3a?w=400&h=400&fit=crop",
        'organizer' => "Egyptian Football Association",
        'description' => "League leaders Pyramids FC defend their top spot against a strong challenger in Pharco FC.",
        'gallery' => [
            "http://static.photos/sport/320x240/9a",
            "http://static.photos/sport/320x240/9b",
            "http://static.photos/sport/320x240/9c",
            "http://static.photos/sport/320x240/9d"
        ]
    ],

    // ⚽ Football Premier League 4
    [ 
        'id' => 10, 
        'title' => "Ahly vs Ittihad of Alexandria", 
        'date' => "2025-10-22T17:00:00", 
        'location' => "Al Salam Stadium", 
        'fullLocation' => "Al Salam Stadium, Shubra El Kheima, Qalyubia",
        'category' => "Sports", 
        'price' => 150.00,
        'image' => "https://images.unsplash.com/photo-1518091043644-c1d4457512c6?w=400&h=400&fit=crop",
        'organizer' => "Egyptian Football Association",
        'description' => "The great derby between Ahly and Ittihad. One of the season's most anticipated and high-tension matches.",
        'gallery' => [
            "http://static.photos/sport/320x240/10a",
            "http://static.photos/sport/320x240/10b",
            "http://static.photos/sport/320x240/10c",
            "http://static.photos/sport/320x240/10d"
        ]
    ],

    // ⚽ Football Premier League 5
    [ 
        'id' => 11, 
        'title' => "Masry vs Smouha SC", 
        'date' => "2025-10-22T20:00:00", 
        'location' => "Borg El Arab Stadium", 
        'fullLocation' => "Borg El Arab Stadium, Alexandria Desert Road",
        'category' => "Sports", 
        'price' => 80.00,
        'image' => "https://images.unsplash.com/photo-1518091043644-c1d4457512c6?w=400&h=400&fit=crop",
        'organizer' => "Egyptian Football Association",
        'description' => "A coastal showdown between Al Masry and Smouha SC. Expect passionate fans and defensive football.",
        'gallery' => [
            "http://static.photos/sport/320x240/11a",
            "http://static.photos/sport/320x240/11b",
            "http://static.photos/sport/320x240/11c",
            "http://static.photos/sport/320x240/11d"
        ]
    ],

    // ⚽ Football Premier League 6
    [ 
        'id' => 12, 
        'title' => "Future FC vs Zamalek SC", 
        'date' => "2025-10-23T20:00:00", 
        'location' => "Cairo Stadium", 
        'fullLocation' => "Cairo International Stadium, Nasr City, Cairo",
        'category' => "Sports", 
        'price' => 130.00,
        'image' => "https://images.unsplash.com/photo-1518091043644-c1d4457512c6?w=400&h=400&fit=crop",
        'organizer' => "Egyptian Football Association",
        'description' => "The young guns of Future FC challenge the historic power of Zamalek SC in a fierce battle for points.",
        'gallery' => [
            "http://static.photos/sport/320x240/12a",
            "http://static.photos/sport/320x240/12b",
            "http://static.photos/sport/320x240/12c",
            "http://static.photos/sport/320x240/12d"
        ]
    ],

    // 🎧 DJ / EDM
    [ 
        'id' => 13, 
        'title' => "Cairo EDM Massive", 
        'date' => "2025-10-20T23:00:00", 
        'location' => "The Temple Rooftop", 
        'fullLocation' => "The Temple Rooftop, 15th Floor, City Stars Mall, Nasr City",
        'category' => "Nightlife", 
        'price' => 450.00,
        'image' => "https://images.unsplash.com/photo-1514525253161-7a46d19cd819?w=400&h=400&fit=crop",
        'organizer' => "Skyline Productions",
        'description' => "A massive EDM showcase featuring three international DJs spinning the best in progressive house and trance. State-of-the-art sound and visuals.",
        'gallery' => [
            "http://static.photos/edm/320x240/13a",
            "http://static.photos/edm/320x240/13b",
            "http://static.photos/edm/320x240/13c",
            "http://static.photos/edm/320x240/13d"
        ]
    ],

    // 🎵 Oriental / Live Band
    [ 
        'id' => 14, 
        'title' => "Oriental Vibes", 
        'date' => "2025-10-21T22:00:00", 
        'location' => "Nile Garden Lounge", 
        'fullLocation' => "Nile Garden Lounge, Nile Corniche, Maadi",
        'category' => "Concerts",
        'price' => 200.00,
        'image' => "https://images.unsplash.com/photo-1507874457470-272b3c8d8ee2?w=400&h=400&fit=crop",
        'organizer' => "Eastern Music Agency",
        'description' => "Enjoy a night of authentic oriental music with a full live band. Traditional instruments and powerful vocals overlooking the Nile.",
        'gallery' => [
            "http://static.photos/oriental/320x240/14a",
            "http://static.photos/oriental/320x240/14b",
            "http://static.photos/oriental/320x240/14c",
            "http://static.photos/oriental/320x240/14d"
        ]
    ],

    // 🎚️ Techno Club
    [ 
        'id' => 15, 
        'title' => "Underground Tech", 
        'date' => "2025-10-22T00:00:00", 
        'location' => "The Vault Club", 
        'fullLocation' => "The Vault Club, Industrial Zone B, 6th of October City",
        'category' => "Nightlife",
        'price' => 550.00,
        'image' => "https://images.unsplash.com/photo-1506157786151-b8491531f063?w=400&h=400&fit=crop",
        'organizer' => "Techno Culture Collective",
        'description' => "A journey into deep and hard techno from two acclaimed local DJs. Strict door policy, RSVP mandatory.",
        'gallery' => [
            "http://static.photos/techno/320x240/15a",
            "http://static.photos/techno/320x240/15b",
            "http://static.photos/techno/320x240/15c",
            "http://static.photos/techno/320x240/15d"
        ]
    ],

    // 🍸 House Music / Ladies Night
    [ 
        'id' => 16, 
        'title' => "Ladies Night House", 
        'date' => "2025-10-23T22:30:00", 
        'location' => "Giza Sky Bar", 
        'fullLocation' => "Giza Sky Bar, 36th Floor, Four Seasons Hotel, Giza",
        'category' => "Nightlife",
        'price' => 600.00,
        'image' => "https://images.unsplash.com/photo-1519677100203-a0e668c92439?w=400&h=400&fit=crop",
        'organizer' => "High Life Entertainment",
        'description' => "Enjoy sunset views and deep house beats. Complimentary drinks for ladies until midnight. Reservations highly recommended.",
        'gallery' => [
            "http://static.photos/house/320x240/16a",
            "http://static.photos/house/320x240/16b",
            "http://static.photos/house/320x240/16c",
            "http://static.photos/house/320x240/16d"
        ]
    ],

    // 🎤 Hip Hop
    [ 
        'id' => 17, 
        'title' => "Hip Hop Rhythms", 
        'date' => "2025-10-24T22:00:00", 
        'location' => "The Dock Studio", 
        'fullLocation' => "The Dock Studio, Downtown Container Area, Cairo",
        'category' => "Concerts",
        'price' => 380.00,
        'image' => "https://images.unsplash.com/photo-1521334884684-d80222895322?w=400&h=400&fit=crop",
        'organizer' => "Urban Sound Collective",
        'description' => "Showcase of Cairo's best underground and mainstream Hip Hop artists. Live DJ sets and open mic cipher session.",
        'gallery' => [
            "http://static.photos/hiphop/320x240/17a",
            "http://static.photos/hiphop/320x240/17b",
            "http://static.photos/hiphop/320x240/17c",
            "http://static.photos/hiphop/320x240/17d"
        ]
    ],

    // 🌅 Beach Party
    [ 
        'id' => 18, 
        'title' => "Friday Sunset Beats", 
        'date' => "2025-10-25T18:00:00", 
        'location' => "Sahl Hasheesh Beach", 
        'fullLocation' => "Sahl Hasheesh Beach, Red Sea Riviera, Hurghada",
        'category' => "Festivals", // Updated from 'Beach Party' to match filtering modal
        'price' => 750.00,
        'image' => "https://images.unsplash.com/photo-1507525428034-b723cf961d3e?w=400&h=400&fit=crop",
        'organizer' => "Red Sea Events",
        'description' => "The ultimate beach party experience featuring tropical house, fire dancers, and a stunning sunset backdrop. Transportation packages available.",
        'gallery' => [
            "http://static.photos/beach/320x240/18a",
            "http://static.photos/beach/320x240/18b",
            "http://static.photos/beach/320x240/18c",
            "http://static.photos/beach/320x240/18d"
        ]
    ],
];
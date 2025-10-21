<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tickets | Home</title>
    <style>
        :root {
            --nav-radius: 20px;
            --nav-shadow: 0 12px 34px rgba(0, 0, 0, 0.28), 0 4px 10px rgba(0, 0, 0, 0.18);
            --nav-bg: #111418; /* dark surface for shalaby */
            --accent: #d65a2e; /* warm orange for shalaby */
            --accent-strong: #ff7a3e;
            --text: #eef2f7;
            --muted: #9aa3af;
            --bg: #0a0b0d; /* base background for shalaby */
            --surface: #0f1115;
            --surface-contrast: #16181d;
            --footer-text: #e8edf5;
            --footer-muted: #a6afbb;
            --orange-glow: rgba(214,90,46,0.22);
            --max-width: 1520px;
            --side-gap: 20px; 
            --search-height: 48px;
        }

        * { box-sizing: border-box; }

        html, body {
            margin: 0;
            padding: 0;
        }

        body {
            font-family: system-ui, -apple-system, Segoe UI, Roboto, Ubuntu, Cantarell, Noto Sans, Helvetica Neue, Arial, "Apple Color Emoji", "Segoe UI Emoji";
            color: var(--text);
            background:
                radial-gradient(1100px 480px at 20% -160px, rgba(214,90,46,0.30), rgba(214,90,46,0) 65%),
                var(--bg);
            min-height: 100vh;
            line-height: 1.4;
        }

        
        .page-top-gap {
            height: 28px; 
        }

        .container {
            max-width: var(--max-width);
            margin: 0 auto;
            padding: 0 16px;
        }

        /* Navbar card styles */
        .navbar-wrap {
            position: relative;
            z-index: 20;
            transition: transform 260ms ease, top 260ms ease, width 260ms ease, border-radius 260ms ease, box-shadow 260ms ease, background-color 260ms ease;
            will-change: transform, top;
        }

        .navbar {
            background: var(--nav-bg);
            border-radius: var(--nav-radius);
            box-shadow: var(--nav-shadow);
            border: 1px solid rgba(255, 255, 255, 0.06);
            padding: 14px 18px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 16px;
        }

        .brand {
            display: flex;
            align-items: center;
            gap: 0px;
            text-decoration: none;
            color: var(--text);
            font-weight: 800;
            letter-spacing: 0.2px;
            font-family: "Times New Roman", Georgia, serif;
            font-size: 34px;
        }
        .brand-name {
            background: linear-gradient(90deg, #ffffff 0%, #ffffff 48%, var(--accent-strong) 52%, var(--accent) 100%);
            -webkit-background-clip: text;
            background-clip: text;
            color: transparent;
        }

        .brand-mark {
            width: 42px;
            height: 42px;
            border-radius: 10px;
            background: linear-gradient(135deg, var(--accent-strong), var(--accent));
            box-shadow: 0 6px 14px rgba(214, 90, 46, 0.45);
        }

        
        .search {
            flex: 1 1 auto;
            max-width: 760px;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .search-field {
            position: relative;
            flex: 1 1 auto;
        }

        .search-field input {
            width: 100%;
            height: var(--search-height);
            padding: 0 44px 0 44px; 
            border-radius: 12px;
            border: 1px solid rgba(255, 255, 255, 0.08);
            background: #0f1115;
            color: var(--text);
            outline: none;
            font-size: 14px;
            transition: box-shadow 150ms ease, border-color 150ms ease, background-color 150ms ease;
        }

        .search-field input::placeholder { color: #8a93a2; }

        .search-field input:focus {
            border-color: rgba(214,90,46,0.55);
            box-shadow: 0 0 0 4px rgba(214,90,46,0.20);
        }

        .search-icon,
        .clear-icon {
            position: absolute;
            top: 50%;
            transform: translateY(-50%);
            width: 18px;
            height: 18px;
            color: #6b7280;
            opacity: 0.9;
            pointer-events: none;
        }

        .search-icon { left: 14px; }
        .clear-icon { right: 14px; }

        .nav {
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .nav a {
            display: inline-block;
            padding: 8px 12px;
            border-radius: 10px;
            color: var(--muted);
            text-decoration: none;
            font-weight: 600;
            transition: color 150ms ease, background-color 150ms ease;
        }

        .nav a[aria-current="page"],
        .nav a:hover {
            color: var(--text);
            background: rgba(214, 90, 46, 0.14);
        }

        .actions {
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .btn {
            appearance: none;
            border: 0;
            background: transparent;
            padding: 8px 12px;
            border-radius: 10px;
            font-weight: 700;
            cursor: pointer;
            text-decoration: none;
        }

        .btn.secondary {
            color: var(--text);
        }

        .btn.primary {
            background: linear-gradient(180deg, var(--accent-strong), var(--accent));
            color: white;
            box-shadow: 0 10px 22px rgba(214, 90, 46, 0.45);
        }

        .btn.primary:hover { filter: brightness(0.98); }
        .btn.primary:active { filter: brightness(0.95); }

        /* Profile icon button */
        .profile-btn {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            width: 40px;
            height: 40px;
            border-radius: 12px;
            border: 1px solid rgba(214,90,46,0.45);
            background: var(--surface);
            color: var(--accent);
            cursor: pointer;
        }
        .profile-btn svg path { fill: currentColor !important; }

        /* Pin behavior removed: navbar stays only at the top */
        .nav-sentinel { display: none; }
        .navbar-pinned { position: static; transform: none; width: auto; }
        .navbar-compact .navbar { padding: 14px 18px; border-radius: var(--nav-radius); background: var(--nav-bg); backdrop-filter: none; }
        .nav-placeholder { height: 0; transition: none; }

        /* Demo hero and content */
        .hero { padding: 56px 0 24px; }
        .hero h1 { margin: 0 0 6px; font-size: 32px; letter-spacing: -0.5px; }
        .hero p { margin: 0; color: var(--muted); }

        /* Events slider styles  */
        .events-slider-section { padding: 8px 0 24px; margin-top: 14px; }
        .slider-container {
            position: relative;
            overflow: hidden;
            border-radius: 26px;
            box-shadow: 0 18px 40px rgba(0,0,0,0.10);
            background: linear-gradient(135deg, rgba(214,90,46,0.25) 0%, rgba(20,22,26,0.96) 58%);
            border: 1px solid rgba(17,24,39,0.06);
            max-width: 1350px;
            margin: 0 auto;
        }
        .slider-track {
            display: grid;
            grid-auto-flow: column;
            grid-auto-columns: 100%;
            transition: transform 520ms cubic-bezier(.22,.61,.36,1);
            will-change: transform;
        }
        .event-card {
            display: grid;
            grid-template-columns: 50% 50%;
            min-height: 360px;
            background: transparent;
            overflow: hidden;
        }
        .event-media {
            position: relative;
            background-size: cover;
            background-position: center;
            min-height: 220px;
            border-left: 1px solid rgba(17,24,39,0.06);
            border-top-right-radius: 26px;
            border-bottom-right-radius: 26px;
        }
        .event-media::after {
            content: "";
            position: absolute;
            inset: 0;
            background: linear-gradient(135deg, rgba(0,0,0,0.25), rgba(0,0,0,0.35));
            pointer-events: none;
        }
        .date-badge {
            position: absolute;
            top: 16px;
            left: 16px;
            padding: 8px 12px;
            border-radius: 12px;
            background: rgba(17,17,17,0.7);
            color: #ffffff;
            font-weight: 800;
            box-shadow: 0 8px 18px rgba(214,90,46,0.22);
        }
        .event-content {
            padding: 28px 24px;
            display: flex;
            flex-direction: column;
            justify-content: center;
            gap: 12px;
            background: var(--nav-bg);
            color: var(--text);
            border-top-left-radius: 26px;
            border-bottom-left-radius: 26px;
        }
        .event-header { display: flex; flex-direction: column; gap: 10px; }
        .event-title { margin: 0; font-size: 32px; line-height: 1.15; letter-spacing: -0.4px; font-weight: 800; }
        .event-sub { font-size: 18px; font-weight: 600; color: var(--text); }
        .event-venue { font-size: 18px; color: var(--text); font-weight: 600; }
        .event-meta { display: flex; gap: 18px; color: var(--muted); font-weight: 600; align-items: center; }
        .event-meta .meta {
            display: inline-flex;
            gap: 8px;
            align-items: center;
            padding: 6px 10px;
            border-radius: 999px;
            background: rgba(255,255,255,0.08);
        }
        .event-actions { display: flex; gap: 18px; margin-top: 8px; align-items: center; }
        .btn.primary { background: linear-gradient(180deg, var(--accent-strong), var(--accent)); color: #ffffff; box-shadow: 0 10px 22px rgba(214,90,46,0.45); }
        .btn.primary .icon { margin-right: 8px; }
        .btn.secondary { background: transparent; border: 0; color: var(--text); font-weight: 700; text-decoration: none; }
        .btn.secondary:hover { text-decoration: none; filter: brightness(1.05); }

        .organized { margin-top: 10px; color: var(--muted); font-weight: 600; font-size: 14px; }
        .org-logos { display: flex; gap: 16px; align-items: center; margin-top: 8px; }
        .org-logo { width: 72px; height: 28px; border-radius: 6px; background: rgba(17,24,39,0.06); display: inline-flex; align-items: center; justify-content: center; font-size: 12px; color: #6b7280; }

        .slider-controls { position: absolute; inset: 0; pointer-events: none; }
        .slider-btn {
            position: absolute;
            top: 50%;
            transform: translateY(-50%);
            pointer-events: auto;
            width: 44px;
            height: 44px;
            border-radius: 50%;
            border: 1px solid rgba(214,90,46,0.45);
            background: linear-gradient(180deg, var(--accent-strong), var(--accent));
            color: #ffffff;
            cursor: pointer;
            box-shadow: 0 10px 24px rgba(214,90,46,0.35);
        }
        .slider-btn:hover { filter: brightness(0.98); }
        .slider-btn:active { filter: brightness(0.96); }
        .slider-btn.prev { left: 18px; display: none; }
        .slider-btn.next { right: 18px; }

        .slider-dots { display: none; }
        .dot { width: 10px; height: 10px; border-radius: 999px; background: rgba(17,24,39,0.18); border: 1px solid rgba(17,24,39,0.18); }
        .dot.active { background: var(--accent); border-color: var(--accent); }

        @media (max-width: 900px) {
            .event-card { grid-template-columns: 1fr; min-height: 360px; }
            .event-media { min-height: 200px; border-left: 0; border-bottom: 1px solid rgba(17,24,39,0.06); border-top-right-radius: 0; border-bottom-right-radius: 0; }
            .event-content { padding: 20px; border-top-left-radius: 0; }
            .event-title { font-size: 26px; }
        }
        
        /* Categories carousel */
        .categories-section { padding: 26px 0 8px; }
        .categories-header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            margin-bottom: 14px;
        }
        .categories-title { font-size: 28px; font-weight: 800; letter-spacing: -0.4px; margin: 0; }
        .cat-controls { display: flex; gap: 10px; }
        .cat-btn {
            width: 44px; height: 44px; border-radius: 999px; border: 1px solid rgba(214,90,46,0.45);
            background: linear-gradient(180deg, var(--accent-strong), var(--accent)); color: #fff; cursor: pointer; box-shadow: 0 10px 22px rgba(214,90,46,0.35);
        }
        .cat-viewport { position: relative; overflow: hidden; width: 100%; }
        .cat-track {
            display: flex; gap: 18px; scroll-behavior: smooth; overflow-x: auto; overscroll-behavior-x: contain;
            scroll-snap-type: x mandatory; padding-bottom: 6px;
            -ms-overflow-style: none; /* IE and Edge */
            scrollbar-width: none; /* Firefox */
        }
        .cat-track::-webkit-scrollbar { display: none; height: 0; }
        .cat-card { flex: 0 0 calc(33.333% - 12px); scroll-snap-align: start; }
        .cat-media {
            width: 100%; height: 190px; border-radius: 26px; background-size: cover; background-position: center; margin-bottom: 10px;
        }
        .cat-info {
            background: #151515; color: #fff; border-radius: 26px; padding: 18px; display: flex; align-items: center; justify-content: space-between;
        }
        .cat-meta { display: flex; flex-direction: column; gap: 6px; }
        .cat-name { font-size: 22px; font-weight: 800; }
        .cat-count { opacity: 0.8; font-size: 14px; }
        .cat-arrow { width: 44px; height: 44px; border-radius: 999px; background: linear-gradient(180deg, var(--accent-strong), var(--accent)); color: #fff; display: inline-flex; align-items: center; justify-content: center; box-shadow: 0 8px 18px rgba(214,90,46,0.35); }
        .cat-arrow span { filter: none; color: #fff; }
        @media (max-width: 900px) {
            .cat-card { flex-basis: 280px; }
            .cat-media { height: 160px; }
        }
        .grid {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 16px;
            padding: 24px 0 64px;
        }

        .card {
            background: white;
            border-radius: 14px;
            padding: 16px;
            box-shadow: 0 4px 18px rgba(0,0,0,0.05);
            border: 1px solid rgba(17,24,39,0.06);
            min-height: 120px;
        }

        /* Responsive */
        @media (max-width: 900px) {
            .grid { grid-template-columns: repeat(2, 1fr); }
        }

        @media (max-width: 720px) {
            .nav { display: none; }
            .search { max-width: none; }
        }

        /* Footer */
        .footer { margin-top: 64px; padding: 56px 0 28px; border-top: 1px solid rgba(255,255,255,0.06); background: linear-gradient(180deg, rgba(12,14,18,0.88), rgba(12,14,18,0.98)); }
        .footer-top { display: grid; grid-template-columns: 1.6fr 1fr 1.2fr; gap: 32px; align-items: start; }
        .brand-wordmark { font-family: "Times New Roman", Georgia, serif; font-weight: 800; font-size: 58px; letter-spacing: -0.6px; color: #ffffff; text-shadow: 0 1px 0 rgba(0,0,0,0.4); }
        .footer-tagline { margin: 8px 0 0; color: var(--footer-muted); max-width: 44ch; }
        .footer-links { display: grid; grid-template-columns: 1fr; gap: 10px; }
        .footer-links a { color: var(--footer-text); text-decoration: none; font-weight: 700; transition: color 150ms ease, opacity 150ms ease; }
        .footer-links a:hover { color: var(--accent-strong); opacity: 1; text-decoration: none; }
        .help-cta { display: flex; align-items: center; justify-content: center; padding: 0 22px; height: 64px; border-radius: 999px; border: 1px solid rgba(255,255,255,0.12); box-shadow: 0 10px 24px rgba(0,0,0,0.24); gap: 10px; background: var(--surface-contrast); color: var(--text); }
        .help-cta::before { content: "?"; display: inline-flex; align-items: center; justify-content: center; width: 28px; height: 28px; border-radius: 999px; border: 1px solid rgba(17,24,39,0.2); font-weight: 800; }
        .help-cta span { font-weight: 800; color: var(--text); }
        .footer-divider { margin: 24px 0; height: 1px; background: rgba(255,255,255,0.08); }
        .footer-middle { display: flex; align-items: center; justify-content: space-between; gap: 16px; padding: 6px 0 10px; }
        .footer-bottom { display: flex; align-items: center; justify-content: space-between; gap: 16px; padding-top: 6px; }
        .footer-heading { margin: 0 0 10px; color: var(--footer-text); font-weight: 800; letter-spacing: .2px; font-size: 14px; text-transform: uppercase; opacity: .9; }
        .socials { display: flex; gap: 12px; align-items: center; }
        .socials .soc { width: 52px; height: 52px; border-radius: 999px; background: var(--accent); display: inline-flex; align-items: center; justify-content: center; box-shadow: 0 10px 20px rgba(214,90,46,0.40); transition: transform 120ms ease, box-shadow 120ms ease, filter 120ms ease; }
        .socials .soc:hover { transform: translateY(-2px); filter: brightness(0.98); box-shadow: 0 14px 26px rgba(214,90,46,0.55); }
        .socials .soc:active { transform: translateY(0); filter: brightness(0.95); }
        .socials .soc svg { width: 22px; height: 22px; fill: #111827; }
        .copyright { color: var(--footer-muted); font-weight: 700; }
        .copyright a { color: inherit; text-decoration: none; border-bottom: 1px solid transparent; }
        .copyright a:hover { border-color: currentColor; }
        @media (max-width: 900px) {
            .footer-top { grid-template-columns: 1fr; gap: 18px; }
            .brand-wordmark { font-size: 44px; }
            .help-cta { height: 56px; }
            .footer-middle { flex-direction: column; align-items: flex-start; gap: 10px; }
            .footer-bottom { flex-direction: column; align-items: flex-start; gap: 12px; }
        }
    </style>
</head>
<body>
    <?php
// Include the header file
include '../../includes/header.php';
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
                        <div class="event-media" style="background-image: url('images/ali-qndeel.jpg'); background-position: center; background-size: cover;">
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
                        <div class="event-media" style="background-image: url('images/food%20fest.jpg'); background-position: center; background-size: cover;">
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
                        <div class="event-media" style="background-image: url('images/pyramids.jpg'); background-position: center; background-size: cover;">
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
                                <div class="cat-count">5 Events</div>
                            </div>
                            <div class="cat-arrow"><span>→</span></div>
                        </div>
                    </div>
                    <div class="cat-card">
                        <div class="cat-media" style="background-image: url('https://images.unsplash.com/photo-1483412033650-1015ddeb83d1?q=80&w=1200&auto=format&fit=crop');"></div>
                        <div class="cat-info">
                            <div class="cat-meta">
                                <div class="cat-name">Concerts</div>
                                <div class="cat-count">13 Events</div>
                            </div>
                            <div class="cat-arrow"><span>→</span></div>
                        </div>
                    </div>
                    <div class="cat-card">
                        <div class="cat-media" style="background-image: url('https://images.unsplash.com/photo-1511671782779-c97d3d27a1d4?q=80&w=1200&auto=format&fit=crop');"></div>
                        <div class="cat-info">
                            <div class="cat-meta">
                                <div class="cat-name">Comedy</div>
                                <div class="cat-count">2 Events</div>
                            </div>
                            <div class="cat-arrow"><span>→</span></div>
                        </div>
                    </div>
                    <div class="cat-card">
                        <div class="cat-media" style="background-image: url('https://images.unsplash.com/photo-1549880338-65ddcdfd017b?q=80&w=1200&auto=format&fit=crop');"></div>
                        <div class="cat-info">
                            <div class="cat-meta">
                                <div class="cat-name">Art & Theatre</div>
                                <div class="cat-count">26 Events</div>
                            </div>
                            <div class="cat-arrow"><span>→</span></div>
                        </div>
                    </div>
                    <div class="cat-card">
                        <div class="cat-media" style="background-image: url('https://images.unsplash.com/photo-1500530855697-b586d89ba3ee?q=80&w=1200&auto=format&fit=crop');"></div>
                        <div class="cat-info">
                            <div class="cat-meta">
                                <div class="cat-name">Summit</div>
                                <div class="cat-count">4 Events</div>
                            </div>
                            <div class="cat-arrow"><span>→</span></div>
                        </div>
                    </div>
                    <div class="cat-card">
                        <div class="cat-media" style="background-image: url('https://images.unsplash.com/photo-1500534314209-a25ddb2bd429?q=80&w=1200&auto=format&fit=crop');"></div>
                        <div class="cat-info">
                            <div class="cat-meta">
                                <div class="cat-name">Activities</div>
                                <div class="cat-count">9 Events</div>
                            </div>
                            <div class="cat-arrow"><span>→</span></div>
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
                                <div class="cat-count">18 Events</div>
                            </div>
                            <div class="cat-arrow"><span>→</span></div>
                        </div>
                    </div>
                    <div class="cat-card">
                        <div class="cat-media" style="background-image: url('https://images.unsplash.com/photo-1546519638-68e109498ffc?q=80&w=1200&auto=format&fit=crop');"></div>
                        <div class="cat-info">
                            <div class="cat-meta">
                                <div class="cat-name">Basketball</div>
                                <div class="cat-count">12 Events</div>
                            </div>
                            <div class="cat-arrow"><span>→</span></div>
                        </div>
                    </div>
                    <div class="cat-card">
                        <div class="cat-media" style="background-image: url('https://images.unsplash.com/photo-1571019613454-1cb2f99b2d8b?q=80&w=1200&auto=format&fit=crop');"></div>
                        <div class="cat-info">
                            <div class="cat-meta">
                                <div class="cat-name">Tennis</div>
                                <div class="cat-count">8 Events</div>
                            </div>
                            <div class="cat-arrow"><span>→</span></div>
                        </div>
                    </div>
                    <div class="cat-card">
                        <div class="cat-media" style="background-image: url('https://images.unsplash.com/photo-1549719386-74dfcbf7dbed?q=80&w=1200&auto=format&fit=crop');"></div>
                        <div class="cat-info">
                            <div class="cat-meta">
                                <div class="cat-name">Boxing</div>
                                <div class="cat-count">6 Events</div>
                            </div>
                            <div class="cat-arrow"><span>→</span></div>
                        </div>
                    </div>
                    <div class="cat-card">
                        <div class="cat-media" style="background-image: url('https://images.unsplash.com/photo-1551698618-1dfe5d97d256?q=80&w=1200&auto=format&fit=crop');"></div>
                        <div class="cat-info">
                            <div class="cat-meta">
                                <div class="cat-name">Handball</div>
                                <div class="cat-count">4 Events</div>
                            </div>
                            <div class="cat-arrow"><span>→</span></div>
                        </div>
                    </div>
                    <div class="cat-card">
                        <div class="cat-media" style="background-image: url('https://images.unsplash.com/photo-1574629810360-7efbbe195018?q=80&w=1200&auto=format&fit=crop');"></div>
                        <div class="cat-info">
                            <div class="cat-meta">
                                <div class="cat-name">Volleyball</div>
                                <div class="cat-count">7 Events</div>
                            </div>
                            <div class="cat-arrow"><span>→</span></div>
                        </div>
                    </div>
                    <div class="cat-card">
                        <div class="cat-media" style="background-image: url('https://images.unsplash.com/photo-1554068865-24cecd4e34b8?q=80&w=1200&auto=format&fit=crop');"></div>
                        <div class="cat-info">
                            <div class="cat-meta">
                                <div class="cat-name">Swimming</div>
                                <div class="cat-count">5 Events</div>
                            </div>
                            <div class="cat-arrow"><span>→</span></div>
                        </div>
                    </div>
                    <div class="cat-card">
                        <div class="cat-media" style="background-image: url('https://images.unsplash.com/photo-1578662996442-48f60103fc96?q=80&w=1200&auto=format&fit=crop');"></div>
                        <div class="cat-info">
                            <div class="cat-meta">
                                <div class="cat-name">Athletics</div>
                                <div class="cat-count">9 Events</div>
                            </div>
                            <div class="cat-arrow"><span>→</span></div>
                        </div>
                    </div>
                </div>
            </div>
        </section>
    </div>

    

    <script>
        (function() {
            
            const wrap = document.getElementById('navbarWrap');
            if (!wrap) return;
            wrap.classList.remove('navbar-pinned', 'navbar-compact');
            wrap.style.width = '';
        })();

        // Events slider logic
        (function() {
            const track = document.getElementById('sliderTrack');
            if (!track) return;
            const prevBtn = document.getElementById('prevBtn');
            const nextBtn = document.getElementById('nextBtn');
            const dotsContainer = document.getElementById('sliderDots');

            let slides = Array.from(track.querySelectorAll('[data-event]'));
            if (slides.length === 0) return;

          
            const firstClone = slides[0].cloneNode(true);
            const lastClone = slides[slides.length - 1].cloneNode(true);
            firstClone.setAttribute('data-clone', 'first');
            lastClone.setAttribute('data-clone', 'last');
            track.insertBefore(lastClone, slides[0]);
            track.appendChild(firstClone);

            slides = Array.from(track.querySelectorAll('[data-event]'));

            let index = 1; 
            let isTransitioning = false;
            let autoplayTimer = null;
            const AUTOPLAY_MS = 4000;
            const TRANSITION_MS = 500;

            function updateDots(activeIndex) {
                dotsContainer.innerHTML = '';
                const realCount = slides.length - 2;
                for (let i = 0; i < realCount; i++) {
                    const btn = document.createElement('button');
                    btn.className = 'dot' + (i === activeIndex ? ' active' : '');
                    btn.setAttribute('role', 'tab');
                    btn.setAttribute('aria-selected', i === activeIndex ? 'true' : 'false');
                    btn.setAttribute('tabindex', i === activeIndex ? '0' : '-1');
                    btn.addEventListener('click', () => goTo(i + 1));
                    dotsContainer.appendChild(btn);
                }
            }

            function setTranslate() {
                const percentage = index * -100;
                track.style.transform = 'translateX(' + percentage + '%)';
            }

            function startAutoplay() {
                stopAutoplay();
                autoplayTimer = setInterval(() => {
                    next();
                }, AUTOPLAY_MS);
            }

            function stopAutoplay() {
                if (autoplayTimer) clearInterval(autoplayTimer);
                autoplayTimer = null;
            }

            function next() {
                if (isTransitioning) return;
                isTransitioning = true;
                index += 1;
                track.style.transition = `transform ${TRANSITION_MS}ms ease`;
                setTranslate();
                
                setTimeout(() => { if (isTransitioning) isTransitioning = false; }, TRANSITION_MS + 120);
            }

            function prev() {
                if (isTransitioning) return;
                isTransitioning = true;
                index -= 1;
                track.style.transition = `transform ${TRANSITION_MS}ms ease`;
                setTranslate();
                setTimeout(() => { if (isTransitioning) isTransitioning = false; }, TRANSITION_MS + 120);
            }

            function goTo(targetIndex) {
                if (isTransitioning) return;
                isTransitioning = true;
                index = targetIndex;
                track.style.transition = `transform ${TRANSITION_MS}ms ease`;
                setTranslate();
                setTimeout(() => { if (isTransitioning) isTransitioning = false; }, TRANSITION_MS + 120);
            }

            track.addEventListener('transitionend', () => {
                const realCount = slides.length - 2;
                if (slides[index].getAttribute('data-clone') === 'first') {
                  
                    track.style.transition = 'none';
                    index = 1;
                    setTranslate();
                } else if (slides[index].getAttribute('data-clone') === 'last') {
                
                    track.style.transition = 'none';
                    index = realCount;
                    setTranslate();
                }
                
                requestAnimationFrame(() => {
                    requestAnimationFrame(() => {
                        track.style.transition = '';
                        isTransitioning = false;
                        updateDots(index - 1);
                    });
                });
            });

            // Controls
            nextBtn.addEventListener('click', () => { stopAutoplay(); next(); startAutoplay(); });
            prevBtn.addEventListener('click', () => { stopAutoplay(); prev(); startAutoplay(); });

           
            track.addEventListener('keydown', (e) => {
                if (e.key === 'ArrowRight') { stopAutoplay(); next(); startAutoplay(); }
                if (e.key === 'ArrowLeft') { stopAutoplay(); prev(); startAutoplay(); }
            });

           
            const sliderSection = document.getElementById('events-slider');
            sliderSection.addEventListener('mouseenter', stopAutoplay);
            sliderSection.addEventListener('mouseleave', startAutoplay);
            // sliderSection.addEventListener('focusin', stopAutoplay);
            // sliderSection.addEventListener('focusout', startAutoplay);

           
            let touchStartX = 0;
            let touchDeltaX = 0;
            track.addEventListener('touchstart', (e) => {
                stopAutoplay();
                touchStartX = e.touches[0].clientX;
                touchDeltaX = 0;
            }, { passive: true });
            track.addEventListener('touchmove', (e) => {
                touchDeltaX = e.touches[0].clientX - touchStartX;
            }, { passive: true });
            track.addEventListener('touchend', () => {
                if (Math.abs(touchDeltaX) > 40) {
                    if (touchDeltaX < 0) next(); else prev();
                }
                startAutoplay();
            });

            
            updateDots(index - 1);
            setTranslate();
           
            setTimeout(startAutoplay, 300);
          
            document.addEventListener('visibilitychange', () => {
                if (document.visibilityState === 'visible') startAutoplay();
                else stopAutoplay();
            });
        })();

        // Categories carousel controls
        (function() {
            const track = document.getElementById('catTrack');
            if (!track) return;
            const prev = document.getElementById('catPrev');
            const next = document.getElementById('catNext');

            function scrollByCard(dir) {
                const card = track.querySelector('.cat-card');
                const amount = card ? card.getBoundingClientRect().width + 18 : 320;
                track.scrollBy({ left: dir * amount, behavior: 'smooth' });
            }

            prev.addEventListener('click', () => scrollByCard(-1));
            next.addEventListener('click', () => scrollByCard(1));
        })();

        // Sports carousel navigation
        (() => {
            const track = document.getElementById('sportsTrack');
            if (!track) return;
            const prev = document.getElementById('sportsPrev');
            const next = document.getElementById('sportsNext');

            function scrollByCard(dir) {
                const card = track.querySelector('.cat-card');
                const amount = card ? card.getBoundingClientRect().width + 18 : 320;
                track.scrollBy({ left: dir * amount, behavior: 'smooth' });
            }

            prev.addEventListener('click', () => scrollByCard(-1));
            next.addEventListener('click', () => scrollByCard(1));
        })();
    </script>
    <?php
// Include the footer file
include '../../includes/footer.php';
?>
</body>
</html>
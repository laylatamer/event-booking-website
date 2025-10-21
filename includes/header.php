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
        </style>
        </head>
<body>
    <div class="page-top-gap" aria-hidden="true"></div>

    <div class="container">
        
        <div class="nav-sentinel" aria-hidden="true"></div>

        <div id="navbarWrap" class="navbar-wrap" role="navigation" aria-label="Primary">
            <div class="navbar" id="navbar">
                <a class="brand" href="homepage.php" aria-label="Homepage">
                    <span class="brand-name" style="background: linear-gradient(180deg, #f8f8f8 0%, #e8e8e8 18%, #d6d6d6 32%, #bfbfbf 48%, #a4a4a4 62%, #d8d8d8 78%, #ffffff 100%); -webkit-background-clip: text; background-clip: text; color: transparent; text-shadow: 0 1px 0 rgba(255,255,255,0.6), 0 -1px 0 rgba(0,0,0,0.28), 0 2px 6px rgba(0,0,0,0.35);">Eحgzly</span>
                </a>

                <div class="search" role="search">
                    <div class="search-field">
                        <input type="search" name="q" aria-label="Search events" placeholder="Search events, artists, venues" />
                       
                        <svg class="search-icon" viewBox="0 0 24 24" aria-hidden="true">
                            <path fill="currentColor" d="M15.5 14h-.79l-.28-.27a6.471 6.471 0 0 0 1.57-4.23 6.5 6.5 0 1 0-6.5 6.5 6.471 6.471 0 0 0 4.23-1.57l.27.28v.79l4.99 4.99c.39.39 1.02.39 1.41 0 .39-.39.39-1.02 0-1.41L15.5 14zm-6 0C7.01 14 5 11.99 5 9.5S7.01 5 9.5 5 14 7.01 14 9.5 11.99 14 9.5 14z"/>
                        </svg>
                       
                        <svg class="clear-icon" viewBox="0 0 24 24" aria-hidden="true">
                            <path fill="currentColor" d="M18.3 5.71a1 1 0 0 0-1.41 0L12 10.59 7.11 5.7A1 1 0 0 0 5.7 7.11L10.59 12l-4.9 4.89a1 1 0 1 0 1.41 1.42L12 13.41l4.89 4.9a1 1 0 0 0 1.42-1.41L13.41 12l4.9-4.89a1 1 0 0 0-.01-1.4z"/>
                        </svg>
                    </div>
                </div>

                <div class="actions">
                    <nav class="nav" aria-label="Main">
                        <a href="allevents.php">Events</a>
                        <a href="faq.php">FAQs</a>
                        <a href="contact_form.php">Contact & Support</a>
                    </nav>
                    <button class="profile-btn" aria-label="Profile">
                        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg" aria-hidden="true">
                            <path d="M12 12c2.761 0 5-2.239 5-5s-2.239-5-5-5-5 2.239-5 5 2.239 5 5 5Z" fill="#6b7280"/>
                            <path d="M4 20.2C4 16.88 7.582 14 12 14s8 2.88 8 6.2c0 .994-.806 1.8-1.8 1.8H5.8C4.806 22 4 21.194 4 20.2Z" fill="#6b7280"/>
                        </svg>
                    </button>
                </div>
            </div>
        </div>

        
        <div id="navPlaceholder" class="nav-placeholder" aria-hidden="true"></div>

       <script>
        (function() {
            
            const wrap = document.getElementById('navbarWrap');
            if (!wrap) return;
            wrap.classList.remove('navbar-pinned', 'navbar-compact');
            wrap.style.width = '';
        })();
</script>
</body>
</html>
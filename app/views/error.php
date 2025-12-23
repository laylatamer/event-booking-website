<?php
// Centralized error page for 404 and 500 (and any other HTTP code)
// Try to load error handler, but don't fail if it has issues
try {
    if (file_exists(__DIR__ . '/../../config/error_handler.php')) {
        require_once __DIR__ . '/../../config/error_handler.php';
    }
} catch (Exception $e) {
    // Ignore errors in error handler itself
}

// Sessions are already centralized; load session init to keep consistency
try {
    require_once __DIR__ . '/../../database/session_init.php';
} catch (Exception $e) {
    // Continue without session if it fails
}

// Determine status code and message
// First check if status code is provided in query string
$status = isset($_GET['code']) ? (int)$_GET['code'] : null;

// If not in query string, check http_response_code
if ($status === null) {
    $status = http_response_code();
}

// If still not set or is 200, default to 404
if ($status === null || $status === 200) {
    $status = 404;
    http_response_code($status);
}

$config = [
    400 => [
        'title' => 'Bad Request',
        'headline' => '400 - Bad Request',
        'description' => 'The request was invalid. Please check and try again.'
    ],
    401 => [
        'title' => 'Unauthorized',
        'headline' => '401 - Unauthorized',
        'description' => 'You need to log in to access this page.'
    ],
    403 => [
        'title' => 'Forbidden',
        'headline' => '403 - Forbidden',
        'description' => 'You do not have permission to access this resource.'
    ],
    404 => [
        'title' => 'Page Not Found',
        'headline' => '404 - Page Not Found',
        'description' => 'The page you are looking for might have been removed, had its name changed, or is temporarily unavailable.'
    ],
    429 => [
        'title' => 'Too Many Requests',
        'headline' => '429 - Slow Down',
        'description' => 'Too many requests. Please wait a moment and try again.'
    ],
    500 => [
        'title' => 'Server Error',
        'headline' => '500 - Server Error',
        'description' => 'We encountered an issue processing your request. Our team has been notified.'
    ],
    502 => [
        'title' => 'Bad Gateway',
        'headline' => '502 - Bad Gateway',
        'description' => 'Upstream service error. Please try again soon.'
    ],
    503 => [
        'title' => 'Service Unavailable',
        'headline' => '503 - Service Unavailable',
        'description' => 'Service is temporarily unavailable. Please try again later.'
    ],
];

$fallback = [
    'title' => 'Something went wrong',
    'headline' => $status . ' - Unexpected Error',
    'description' => 'An unexpected error occurred. Please try again later.'
];

$selected = $config[$status] ?? $fallback;
$title = $selected['title'];
$headline = $selected['headline'];
$description = $selected['description'];

// Provide a safe back link and home link
// Use proper routing - homepage is accessible at / or /homepage.php
$homeUrl = '/homepage.php';
$backUrl = isset($_SERVER['HTTP_REFERER']) ? $_SERVER['HTTP_REFERER'] : $homeUrl;
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($title); ?> | Eحgzly</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        :root {
            --primary: #ff7a3e;
            --primary-dark: #d65a2e;
            --bg-dark: #0a0b0f;
            --bg-card: rgba(15, 23, 42, 0.8);
        }
        
        body {
            margin: 0;
            padding: 0;
            font-family: 'Inter', system-ui, -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
            background: var(--bg-dark);
            color: #e5e7eb;
            min-height: 100vh;
            overflow-x: hidden;
            position: relative;
        }
        
        /* Animated background */
        .bg-animation {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            z-index: 0;
            overflow: hidden;
        }
        
        .bg-gradient {
            position: absolute;
            width: 200%;
            height: 200%;
            background: 
                radial-gradient(circle at 20% 30%, rgba(255,122,62,0.15) 0%, transparent 50%),
                radial-gradient(circle at 80% 70%, rgba(214,90,46,0.12) 0%, transparent 50%),
                radial-gradient(circle at 50% 50%, rgba(255,122,62,0.08) 0%, transparent 70%);
            animation: rotate 20s linear infinite;
        }
        
        @keyframes rotate {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }
        
        .particles {
            position: absolute;
            width: 100%;
            height: 100%;
        }
        
        .particle {
            position: absolute;
            width: 4px;
            height: 4px;
            background: rgba(255,122,62,0.4);
            border-radius: 50%;
            animation: float 15s infinite ease-in-out;
        }
        
        @keyframes float {
            0%, 100% { transform: translateY(0) translateX(0); opacity: 0; }
            10% { opacity: 1; }
            90% { opacity: 1; }
            100% { transform: translateY(-100vh) translateX(50px); opacity: 0; }
        }
        
        /* Main content */
        .error-container {
            position: relative;
            z-index: 1;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 40px 20px;
        }
        
        .error-content {
            max-width: 900px;
            width: 100%;
            text-align: center;
            animation: fadeInUp 0.8s ease-out;
        }
        
        @keyframes fadeInUp {
            from {
                opacity: 0;
                transform: translateY(30px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }
        
        .error-code {
            font-size: 180px;
            font-weight: 900;
            background: linear-gradient(135deg, var(--primary), var(--primary-dark));
            -webkit-background-clip: text;
            background-clip: text;
            -webkit-text-fill-color: transparent;
            line-height: 1;
            margin-bottom: 20px;
            text-shadow: 0 0 80px rgba(255,122,62,0.3);
            animation: pulse 2s ease-in-out infinite;
        }
        
        @keyframes pulse {
            0%, 100% { transform: scale(1); }
            50% { transform: scale(1.02); }
        }
        
        .error-headline {
            font-size: 42px;
            font-weight: 700;
            color: #fff;
            margin-bottom: 16px;
            letter-spacing: -0.5px;
        }
        
        .error-description {
            font-size: 18px;
            color: #cbd5e1;
            line-height: 1.7;
            margin-bottom: 40px;
            max-width: 600px;
            margin-left: auto;
            margin-right: auto;
        }
        
        .error-actions {
            display: flex;
            gap: 16px;
            justify-content: center;
            flex-wrap: wrap;
            margin-bottom: 60px;
        }
        
        .btn {
            display: inline-flex;
            align-items: center;
            gap: 10px;
            padding: 16px 32px;
            border-radius: 12px;
            font-weight: 600;
            font-size: 16px;
            text-decoration: none;
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            border: none;
            cursor: pointer;
            position: relative;
            overflow: hidden;
        }
        
        .btn::before {
            content: '';
            position: absolute;
            top: 0;
            left: -100%;
            width: 100%;
            height: 100%;
            background: linear-gradient(90deg, transparent, rgba(255,255,255,0.2), transparent);
            transition: left 0.5s;
        }
        
        .btn:hover::before {
            left: 100%;
        }
        
        .btn-primary {
            background: linear-gradient(135deg, var(--primary), var(--primary-dark));
            color: #fff;
            box-shadow: 0 8px 24px rgba(255,122,62,0.4);
        }
        
        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 12px 32px rgba(255,122,62,0.5);
        }
        
        .btn-secondary {
            background: rgba(255,255,255,0.08);
            color: #e5e7eb;
            border: 1px solid rgba(255,255,255,0.12);
            backdrop-filter: blur(10px);
        }
        
        .btn-secondary:hover {
            background: rgba(255,255,255,0.12);
            transform: translateY(-2px);
        }
        
        .info-section {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
            gap: 24px;
            margin-top: 60px;
        }
        
        .info-card {
            background: var(--bg-card);
            border: 1px solid rgba(255,122,62,0.2);
            border-radius: 16px;
            padding: 28px;
            backdrop-filter: blur(10px);
            transition: all 0.3s ease;
        }
        
        .info-card:hover {
            transform: translateY(-4px);
            border-color: rgba(255,122,62,0.4);
            box-shadow: 0 8px 24px rgba(255,122,62,0.2);
        }
        
        .info-icon {
            width: 48px;
            height: 48px;
            background: linear-gradient(135deg, rgba(255,122,62,0.2), rgba(214,90,46,0.2));
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            margin-bottom: 16px;
            font-size: 24px;
        }
        
        .info-title {
            font-size: 20px;
            font-weight: 700;
            color: #fff;
            margin-bottom: 12px;
        }
        
        .info-text {
            font-size: 15px;
            color: #cbd5e1;
            line-height: 1.6;
        }
        
        .status-badge {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            padding: 8px 16px;
            background: rgba(255,122,62,0.15);
            border: 1px solid rgba(255,122,62,0.3);
            border-radius: 20px;
            font-size: 14px;
            font-weight: 600;
            color: #ffd9c5;
            margin-bottom: 20px;
        }
        
        @media (max-width: 768px) {
            .error-code {
                font-size: 120px;
            }
            
            .error-headline {
                font-size: 32px;
            }
            
            .error-description {
                font-size: 16px;
            }
            
            .error-actions {
                flex-direction: column;
            }
            
            .btn {
                width: 100%;
                justify-content: center;
            }
            
            .info-section {
                grid-template-columns: 1fr;
            }
        }
        
        @media (max-width: 480px) {
            .error-code {
                font-size: 100px;
            }
            
            .error-headline {
                font-size: 28px;
            }
        }
    </style>
</head>
<body>
    <div class="bg-animation">
        <div class="bg-gradient"></div>
        <div class="particles" id="particles"></div>
    </div>
    
    <div class="error-container">
        <div class="error-content">
            <div class="status-badge">
                <span>Status <?php echo (int)$status; ?></span>
            </div>
            
            <div class="error-code"><?php echo (int)$status; ?></div>
            
            <h1 class="error-headline"><?php echo htmlspecialchars($headline); ?></h1>
            
            <p class="error-description"><?php echo htmlspecialchars($description); ?></p>
            
            <div class="error-actions">
                <a id="btn-home" class="btn btn-primary" href="<?php echo htmlspecialchars($homeUrl); ?>">
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path d="M3 9l9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"></path>
                        <polyline points="9 22 9 12 15 12 15 22"></polyline>
                    </svg>
                    Back to Home
                </a>
                <a id="btn-back" class="btn btn-secondary" href="<?php echo htmlspecialchars($backUrl); ?>">
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path d="M19 12H5M12 19l-7-7 7-7"></path>
                    </svg>
                    Go Back
                </a>
            </div>
            
            <div class="info-section">
                <div class="info-card">
                    <div class="info-icon">💡</div>
                    <div class="info-title">What can you do?</div>
                    <div class="info-text">
                        Return to the homepage and browse our events. Use the navigation menu to explore different sections or try searching for what you're looking for.
                    </div>
                </div>
                <div class="info-card">
                    <div class="info-icon">🆘</div>
                    <div class="info-title">Need help?</div>
                    <div class="info-text">
                        Contact our support team via the contact page. Please include details about when the error occurred and what you were trying to access.
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        // Create animated particles
        (function() {
            const particlesContainer = document.getElementById('particles');
            const particleCount = 30;
            
            for (let i = 0; i < particleCount; i++) {
                const particle = document.createElement('div');
                particle.className = 'particle';
                particle.style.left = Math.random() * 100 + '%';
                particle.style.animationDelay = Math.random() * 15 + 's';
                particle.style.animationDuration = (10 + Math.random() * 10) + 's';
                particlesContainer.appendChild(particle);
            }
        })();
        
        // Handle back button
        (function() {
            const backBtn = document.getElementById('btn-back');
            if (backBtn) {
                backBtn.addEventListener('click', function (e) {
                    // If we have history, go back; otherwise fall back to home.
                    if (window.history.length > 1) {
                        e.preventDefault();
                        window.history.back();
                    }
                });
            }
        })();
    </script>
</body>
</html>


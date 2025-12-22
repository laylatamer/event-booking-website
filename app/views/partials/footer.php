<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Document</title>
    <?php
    // Include path helper if not already included
    if (!defined('BASE_ASSETS_PATH')) {
        require_once __DIR__ . '/../path_helper.php';
    }
    ?>
    <link rel="stylesheet" href="<?= asset('css/footer.css') ?>">
    <script src="<?= asset('js/footer.js') ?>"></script>
</head>
<body>
    <footer class="footer" id="footer">
            <div class="container">
            <div class="footer-top">
                <div>
                    <div class="brand-wordmark" style="background: linear-gradient(90deg, #ffffff 0%, #ffffff 48%, var(--accent-strong) 52%, var(--accent) 100%); -webkit-background-clip: text; background-clip: text; color: transparent;">Eحgzly</div>
                    <p class="footer-tagline">Discover concerts, theatre, sports, festivals and more — book securely and instantly.</p>
                </div>
                <div>
                    <p class="footer-heading">Explore</p>
                    <nav class="footer-links" aria-label="Footer">
                        <a href="#about">About Us</a>
                        <a href="faq.php">FAQs</a>
                        <a href="contact_form.php">Contact Us</a>
                        <a href="#privacy">Privacy Policy</a>
                    </nav>
                </div>
                <div style="display:flex; align-items:center; justify-content:center;">
                    <a href="contact_form.php" class="help-cta" aria-label="Need some help? Contact us">
                        <span>❔ Need some help? Contact us</span>
                    </a>
                </div>
            </div>
            <div class="footer-divider"></div>
            <div class="footer-middle">
                <div class="socials" aria-label="Follow us">
                    <span class="footer-heading" style="margin:0;">Follow us</span>
                    <a class="soc" href="#" aria-label="Instagram" title="Instagram">
                        <svg viewBox="0 0 24 24" aria-hidden="true">
                            <path d="M7 2h10a5 5 0 0 1 5 5v10a5 5 0 0 1-5 5H7a5 5 0 0 1-5-5V7a5 5 0 0 1 5-5Zm0 2a3 3 0 0 0-3 3v10a3 3 0 0 0 3 3h10a3 3 0 0 0 3-3V7a3 3 0 0 0-3-3H7Zm5 3.5A5.5 5.5 0 1 1 6.5 13 5.5 5.5 0 0 1 12 7.5Zm0 2A3.5 3.5 0 1 0 15.5 13 3.5 3.5 0 0 0 12 9.5Zm5.75-2.75a1.25 1.25 0 1 1-1.25 1.25 1.25 1.25 0 0 1 1.25-1.25Z"/>
                        </svg>
                    </a>
                    <a class="soc" href="#" aria-label="Facebook" title="Facebook">
                        <svg viewBox="0 0 24 24" aria-hidden="true">
                            <path d="M13.5 9H16V6h-2.5C11.57 6 10 7.57 10 9.5V11H8v3h2v7h3v-7h2.1l.4-3H13v-1.5c0-.28.22-.5.5-.5Z"/>
                        </svg>
                    </a>
                    <a class="soc" href="#" aria-label="Twitter" title="Twitter">
                        <svg viewBox="0 0 24 24" aria-hidden="true">
                            <path d="M21.5 6.5a7 7 0 0 1-2 .55 3.46 3.46 0 0 0 1.53-1.93 7 7 0 0 1-2.2.86 3.5 3.5 0 0 0-6 3.19 9.93 9.93 0 0 1-7.2-3.65 3.5 3.5 0 0 0 1.08 4.68 3.46 3.46 0 0 1-1.58-.44v.04a3.5 3.5 0 0 0 2.8 3.43 3.53 3.53 0 0 1-1.58.06 3.5 3.5 0 0 0 3.27 2.43A7.03 7.03 0 0 1 3 17.5a9.93 9.93 0 0 0 5.37 1.57c6.45 0 9.98-5.45 9.98-10.18v-.47a7.1 7.1 0 0 0 1.65-1.92Z"/>
                        </svg>
                    </a>
                    <a class="soc" href="#" aria-label="TikTok" title="TikTok">
                        <svg viewBox="0 0 24 24" aria-hidden="true">
                            <path d="M15.5 3a5.5 5.5 0 0 0 .13 1.21 5.5 5.5 0 0 0 4.16 4.17A6.98 6.98 0 0 0 20 6.5c-1.2 0-2.3-.36-3.25-1.02v8.3c0 3.2-2.6 5.77-5.8 5.72A5.75 5.75 0 0 1 5.5 13.7a5.75 5.75 0 0 1 6.8-5.68v3.06a2.75 2.75 0 1 0 2.2 2.7V3h1Z"/>
                        </svg>
                    </a>
                </div>
                <div class="copyright">© Eحgzly 2025 – <a href="#privacy" style="color:inherit; text-decoration:none; font-weight:700;">Privacy Policy</a></div>
            </div>
            <div class="footer-divider"></div>
            <div class="footer-bottom">
                <div></div>
                <div style="display:flex; gap:10px; align-items:center;">
                    <span class="footer-heading" style="margin:0;">Back to top</span>
                    <a href="#top" class="soc" aria-label="Back to top" title="Back to top" style="width:40px;height:40px;">
                        <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M12 6l6 6H6l6-6Z" fill="#111827"/></svg>
                    </a>
                </div>
            </div>
            </div>
        </footer>

    <!-- Chatbot script (fixed path) -->
    <script src="<?= asset('js/chatbot-widget.js') ?>"></script>
</body>
</html>
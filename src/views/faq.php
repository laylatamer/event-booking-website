<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>FAQs | Eحgzly</title>
    <style>
        :root {
            --accent: #d65a2e;
            --accent-strong: #ff7a3e;
            --bg: #0a0b0d;
            --surface: #111418;
            --surface-2: #16181d;
            --text: #eef2f7;
            --muted: #9aa3af;
        }
        * { box-sizing: border-box; }
        html, body { margin: 0; padding: 0; }
        body {
            font-family: system-ui, -apple-system, Segoe UI, Roboto, Ubuntu, Cantarell, Noto Sans, Helvetica Neue, Arial, "Apple Color Emoji", "Segoe UI Emoji";
            background:
                radial-gradient(1100px 480px at 20% -160px, rgba(214,90,46,0.30), rgba(214,90,46,0) 65%),
                var(--bg);
            color: var(--text);
            min-height: 100vh;
        }
        .container { max-width: 1100px; margin: 0 auto; padding: 24px 16px 60px; }
        .header { display: flex; align-items: center; justify-content: space-between; padding: 16px 0 24px; }
        .brand { text-decoration: none; font-weight: 800; font-size: 32px; font-family: "Times New Roman", Georgia, serif; letter-spacing: .2px; }

        .brand span { background: linear-gradient(180deg, #f8f8f8 0%, #e8e8e8 18%, #d6d6d6 32%, #bfbfbf 48%, #a4a4a4 62%, #d8d8d8 78%, #ffffff 100%); -webkit-background-clip: text; background-clip: text; color: transparent; text-shadow: 0 1px 0 rgba(255,255,255,0.6), 0 -1px 0 rgba(0,0,0,0.28), 0 2px 6px rgba(0,0,0,0.35); }
        .back { color: var(--text); text-decoration: none; padding: 10px 14px; border-radius: 10px; border: 1px solid rgba(255,255,255,0.12); background: var(--surface); }

        .title { font-size: 36px; margin: 10px 0 6px; }
        .subtitle { color: var(--muted); margin: 0 0 24px; }

        .faq-list { display: grid; gap: 10px; }
        .faq-item { background: var(--surface); border: 1px solid rgba(255,255,255,0.08); border-radius: 14px; overflow: hidden; }
        .faq-q { width: 100%; text-align: left; background: var(--surface); color: var(--text); padding: 16px 18px; border: 0; cursor: pointer; font-weight: 800; display: flex; justify-content: space-between; align-items: center; }
        .faq-q:hover { background: var(--surface-2); }
        .faq-q .chev { width: 18px; height: 18px; border-radius: 50%; background: linear-gradient(180deg, var(--accent-strong), var(--accent)); color: #fff; display: inline-flex; align-items: center; justify-content: center; font-weight: 900; transition: transform 200ms ease; }
        .faq-a { padding: 0 18px; color: var(--muted); max-height: 0; overflow: hidden; opacity: 0; transform: translateY(-4px); transition: max-height 260ms ease, opacity 220ms ease, transform 220ms ease, padding 220ms ease; }
        .faq-item.open .faq-a { max-height: 260px; opacity: 1; transform: translateY(0); padding: 12px 18px 16px; }
        .faq-item.open .faq-q .chev { transform: rotate(180deg); }

        /* Tips Section */
        .tips-section { margin: 32px 0 40px; }
        .tips-header { display: flex; align-items: center; gap: 12px; margin-bottom: 24px; }
        .tips-icon { width: 24px; height: 24px; color: var(--accent); }
        .tips-title { font-size: 28px; font-weight: 800; margin: 0; color: var(--text); }
        .tips-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(280px, 1fr)); gap: 20px; }
        .tip-card { 
            background: var(--surface); 
            border: 1px solid rgba(255,255,255,0.08); 
            border-top: 3px solid var(--accent); 
            border-radius: 16px; 
            padding: 24px; 
            text-align: center; 
            box-shadow: 0 4px 12px rgba(0,0,0,0.15);
            transition: transform 200ms ease, box-shadow 200ms ease;
        }
        .tip-card:hover { 
            transform: translateY(-2px); 
            box-shadow: 0 8px 20px rgba(0,0,0,0.25); 
        }
        .tip-icon { 
            width: 48px; 
            height: 48px; 
            margin: 0 auto 16px; 
            color: var(--accent); 
            display: flex; 
            align-items: center; 
            justify-content: center; 
        }
        .tip-title { 
            font-size: 18px; 
            font-weight: 800; 
            margin: 0 0 12px; 
            color: var(--text); 
        }
        .tip-text { 
            color: var(--muted); 
            line-height: 1.5; 
            margin: 0; 
            font-size: 14px; 
        }

        .cta { margin-top: 28px; display: flex; gap: 10px; }
        .btn { appearance: none; border: 0; padding: 10px 14px; border-radius: 10px; font-weight: 800; cursor: pointer; }
        .btn.primary { background: linear-gradient(180deg, var(--accent-strong), var(--accent)); color: #fff; box-shadow: 0 10px 22px rgba(214,90,46,0.45); }
        .btn.secondary { background: transparent; color: var(--text); border: 1px solid rgba(255,255,255,0.12); }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <a href="homepage.php" class="brand"><span>Eحgzly</span></a>
            <a href="homepage.php#footer" class="back">← Back</a>
        </div>

        <h1 class="title">Frequently Asked Questions</h1>
        <p class="subtitle">Tap a question to reveal the answer.</p>

        <div class="faq-list" id="faqList">
            <div class="faq-item">
                <button class="faq-q">How do I receive my tickets? <span class="chev">▾</span></button>
                <div class="faq-a">Tickets are emailed instantly after purchase and available in your account as QR codes. You can add them to Apple/Google Wallet.</div>
            </div>
            <div class="faq-item">
                <button class="faq-q">Can I refund or exchange my tickets? <span class="chev">▾</span></button>
                <div class="faq-a">Refunds depend on the organizer’s policy. Many events allow transfers up to 24 hours before start. See the event page for details.</div>
            </div>
            <div class="faq-item">
                <button class="faq-q">Is my payment secure? <span class="chev">▾</span></button>
                <div class="faq-a">Yes. We use PCI-compliant processors and HTTPS. Your card details never touch our servers.</div>
            </div>
            <div class="faq-item">
                <button class="faq-q">Do you charge service fees? <span class="chev">▾</span></button>
                <div class="faq-a">A small service fee helps cover payment processing and platform costs. It’s shown clearly at checkout before payment.</div>
            </div>
            <div class="faq-item">
                <button class="faq-q">Can I buy tickets as a gift? <span class="chev">▾</span></button>
                <div class="faq-a">Yes. Purchase normally, then forward the QR ticket to the recipient or transfer the ticket to their email from your account.</div>
            </div>
            <div class="faq-item">
                <button class="faq-q">Will events be canceled due to weather? <span class="chev">▾</span></button>
                <div class="faq-a">Outdoor events share weather policies on the event page. If an event is postponed or canceled, you’ll be notified with next steps automatically.</div>
            </div>
            <div class="faq-item">
                <button class="faq-q">Which payment methods do you accept? <span class="chev">▾</span></button>
                <div class="faq-a">We accept major cards, local wallets, and bank transfers where available. Options vary by country and are shown at checkout.</div>
            </div>
        </div>

        <!-- Tips Section -->
        <section class="tips-section">
            <div class="tips-header">
                <svg class="tips-icon" viewBox="0 0 24 24" aria-hidden="true">
                    <path fill="currentColor" d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm-2 15l-5-5 1.41-1.41L10 14.17l7.59-7.59L19 8l-9 9z"/>
                </svg>
                <h2 class="tips-title">Tips</h2>
            </div>
            <div class="tips-grid">
                <div class="tip-card">
                    <div class="tip-icon">
                        <svg viewBox="0 0 24 24" aria-hidden="true">
                            <path fill="currentColor" d="M12 2C8.13 2 5 5.13 5 9c0 5.25 7 13 7 13s7-7.75 7-13c0-3.87-3.13-7-7-7zm0 9.5c-1.38 0-2.5-1.12-2.5-2.5s1.12-2.5 2.5-2.5 2.5 1.12 2.5 2.5-1.12 2.5-2.5 2.5z"/>
                        </svg>
                    </div>
                    <h3 class="tip-title">Track Your Tickets</h3>
                    <p class="tip-text">Use the link in your confirmation email to see your ticket status and venue details anytime!</p>
                </div>
                <div class="tip-card">
                    <div class="tip-icon">
                        <svg viewBox="0 0 24 24" aria-hidden="true">
                            <path fill="currentColor" d="M20 6h-2.18c.11-.31.18-.65.18-1a2.996 2.996 0 0 0-5.5-1.65l-.5.67-.5-.68C10.96 2.54 10 2 9 2 7.34 2 6 3.34 6 5c0 .35.07.69.18 1H4c-1.11 0-1.99.89-1.99 2L2 19c0 1.11.89 2 2 2h16c1.11 0 2-.89 2-2V8c0-1.11-.89-2-2-2zm-5-2c.55 0 1 .45 1 1s-.45 1-1 1-1-.45-1-1 .45-1 1-1zM9 4c.55 0 1 .45 1 1s-.45 1-1 1-1-.45-1-1 .45-1 1-1z"/>
                        </svg>
                    </div>
                    <h3 class="tip-title">Gifting Tickets?</h3>
                    <p class="tip-text">Add a personal message at checkout—we'll include it with your gift tickets for a special touch!</p>
                </div>
                <div class="tip-card">
                    <div class="tip-icon">
                        <svg viewBox="0 0 24 24" aria-hidden="true">
                            <path fill="currentColor" d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm-2 15l-5-5 1.41-1.41L10 14.17l7.59-7.59L19 8l-9 9z"/>
                        </svg>
                    </div>
                    <h3 class="tip-title">Need Help?</h3>
                    <p class="tip-text">Check our FAQ above or contact our support team—we're here to help make your event experience perfect!</p>
                </div>
            </div>
        </section>

        <div class="cta">
            <a href="homepage.php#categories" class="btn secondary">Explore categories</a>
            <a href="homepage.php#events-slider" class="btn primary">Find events</a>
        </div>
    </div>

    <script>
        (function(){
            const items = document.querySelectorAll('.faq-item');
            items.forEach(item => {
                const q = item.querySelector('.faq-q');
                q.addEventListener('click', () => {
                    item.classList.toggle('open');
                });
            });
        })();
    </script>
</body>
</html>



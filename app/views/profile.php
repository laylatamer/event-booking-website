<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Profile | Eحgzly</title>
    <link rel="stylesheet" href="../../public/css/profile.css">
</head>
<body>

    <div class="navbar-wrap" role="navigation" aria-label="Primary">
        <div class="container navbar-container">
            <div class="navbar">
                <a class="brand" href="#" aria-label="Homepage"><span class="brand-name">Eحgzly</span></a>
                <div class="search" role="search">
                    <div class="search-field">
                        <input type="search" name="q" placeholder="Search events, artists, venues" />
                        <svg class="search-icon" viewBox="0 0 24 24"><path fill="currentColor" d="M15.5 14h-.79l-.28-.27a6.471 6.471 0 0 0 1.57-4.23 6.5 6.5 0 1 0-6.5 6.5 6.471 6.471 0 0 0 4.23-1.57l.27.28v.79l4.99 4.99c.39.39 1.02.39 1.41 0 .39-.39.39-1.02 0-1.41L15.5 14zm-6 0C7.01 14 5 11.99 5 9.5S7.01 5 9.5 5 14 7.01 14 9.5 11.99 14 9.5 14z"/></svg>
                    </div>
                </div>
                <div class="actions">
                    <nav class="nav" aria-label="Main">
                        <a href="#">Events</a><a href="#">FAQs</a><a href="#">Contact</a>
                    </nav>
                    <button class="profile-btn" aria-label="Profile"><svg width="20" height="20" viewBox="0 0 24 24"><path d="M12 12c2.761 0 5-2.239 5-5s-2.239-5-5-5-5 2.239-5 5 2.239 5 5 5Z" fill="currentColor"/><path d="M4 20.2C4 16.88 7.582 14 12 14s8 2.88 8 6.2c0 .994-.806 1.8-1.8 1.8H5.8C4.806 22 4 21.194 4 20.2Z" fill="currentColor"/></svg></button>
                </div>
            </div>
        </div>
    </div>

    <div class="container page-content">
        <aside class="sidebar">
            <div class="profile-card">
                <div class="profile-header">
                    <div class="logo">
                        <span>Eحgzly Profile</span>
                    </div>
                </div>
                <div class="profile-body">
                    <div class="profile-image">
                        <label for="profile-upload" class="profile-upload-label" title="Change profile picture">
                            <img src="https://via.placeholder.com/150/16181d/9aa3af?text=User" alt="Profile Picture" id="profile-pic">
                            <span class="edit-icon">✏️</span>
                        </label>
                        <input type="file" id="profile-upload" accept="image/png, image/jpeg" hidden>
                    </div>
                    <h3 class="profile-name">محمود شلبي</h3>
                    <div class="profile-id">
                        Eحgzly ID
                        <span>10301732910070</span>
                    </div>
                    <button class="btn primary print-btn">Print</button>
                </div>
            </div>

            <div class="sidebar-filters">
                <div class="form-group">
                    <label for="country-select">Country of Residence</label>
                    <select id="country-select" class="custom-select"></select>
                </div>
                <div class="form-group">
                    <label for="state-select">State / Province</label>
                    <select id="state-select" class="custom-select" disabled>
                        <option value="">Select Country First</option>
                    </select>
                </div>
                <div class="form-group">
                    <label for="team-select">Preferred Team</label>
                    <select id="team-select" class="custom-select"></select>
                </div>
            </div>
        </aside>

        <main class="content">
            <section class="info-card">
                <div class="card-header">
                    <h2>Personal Information</h2>
                    <button class="btn primary update-btn">Update</button>
                </div>
                <div class="form-grid">
                    <div class="form-group"><label for="fan-name">Fan Name</label><input type="text" id="fan-name" value="محمود شلبي"></div>
                    <div class="form-group"><label for="email">Email</label><input type="email" id="email" value="ms6261898@gmail.com"></div>
                    <div class="form-group"><label for="mobile-number">Mobile number</label><input type="text" id="mobile-number" value="+201120034377"></div>
                </div>
            </section>
            <section class="info-card">
                <div class="card-header">
                    <h2>Account Information</h2>
                    <button class="btn primary update-btn">Update</button>
                </div>
                <div class="form-grid">
                    <div class="form-group password-group">
                        <label for="password">Password</label>
                        <div class="password-wrapper"><input type="password" id="password" value="secretpassword"><button type="button" id="password-toggle" class="password-toggle" aria-label="Show password">👁️</button></div>
                    </div>
                    <div class="form-group">
                        <label for="address-one">Address</label>
                        <input type="text" id="address-one" value="north teseen" >
                    </div>
                </div>
            </section>

            <div class="history-section">
                <details class="history-accordion">
                    <summary class="history-header"><h3>Entertainment Tickets</h3><span class="accordion-icon">▶</span></summary>
                    <div class="history-content">
                        <table class="history-table">
                            <thead><tr><th>Event Name</th><th>Date</th><th>Venue</th><th>Action</th></tr></thead>
                            <tbody>
                                <tr><td>Ali Quandil: Standup Show</td><td>24 Oct 2025</td><td>Theatro Arkan</td><td><a href="#" class="view-ticket-btn">View Ticket</a></td></tr>
                                <tr><td>Mediterranean Food Fest</td><td>07 Dec 2025</td><td>Alexandria, Egypt</td><td><a href="#" class="view-ticket-btn">View Ticket</a></td></tr>
                            </tbody>
                        </table>
                    </div>
                </details>
                <details class="history-accordion">
                    <summary class="history-header"><h3>Sports Tickets</h3><span class="accordion-icon">▶</span></summary>
                    <div class="history-content"><div class="no-data-message"><span>ⓘ</span> No data to display.</div></div>
                </details>
                <details class="history-accordion">
                    <summary class="history-header"><h3>Payment History</h3><span class="accordion-icon">▶</span></summary>
                    <div class="history-content">
                        <table class="history-table">
                            <thead><tr><th>Transaction ID</th><th>Date</th><th>Amount</th><th>Status</th></tr></thead>
                            <tbody>
                                <tr><td>#8A4D3-2025</td><td>07 Dec 2025</td><td>EGP 750.00</td><td><span class="status-badge status-successful">Successful</span></td></tr>
                                <tr><td>#F2B1A-2025</td><td>21 Nov 2025</td><td>EGP 400.00</td><td><span class="status-badge status-refunded">Refunded</span></td></tr>
                            </tbody>
                        </table>
                    </div>
                </details>
            </div>
        </main>
    </div>

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
                        <a href="#contact">Contact Us</a>
                        <a href="#privacy">Privacy Policy</a>
                    </nav>
                </div>
                <div style="display:flex; align-items:center; justify-content:center;">
                    <a href="#contact" class="help-cta" aria-label="Need some help? Contact us">
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


    <script src="../../public/css/profile.js"></script>
</body>
</html>
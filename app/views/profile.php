<?php
// Include error handler FIRST - before any other code
require_once __DIR__ . '/../../config/error_handler.php';

// Start session and require login
require_once __DIR__ . '/../../database/session_init.php';
requireLogin();

require_once __DIR__ . '/../../config/db_connect.php';

function ensureUserColumn(PDO $pdo, string $column, string $definition): void
{
    $safeColumn = preg_replace('/[^a-zA-Z0-9_]/', '', $column);
    if ($safeColumn === '') {
        return;
    }

    try {
        $quotedColumn = $pdo->quote($safeColumn);
        $check = $pdo->query("SHOW COLUMNS FROM `users` LIKE {$quotedColumn}");
        if (!$check->fetch()) {
            $pdo->exec("ALTER TABLE `users` ADD COLUMN `{$safeColumn}` {$definition}");
        }
    } catch (\PDOException $e) {
        error_log("Column ensure failed for {$safeColumn}: " . $e->getMessage());
    }
}

function loadUser(PDO $pdo, int $userId): ?array
{
    $stmt = $pdo->prepare('SELECT id, first_name, last_name, email, phone_number, address, city, country, state, preferred_team, profile_image_path, password_hash FROM users WHERE id = :id LIMIT 1');
    $stmt->execute([':id' => $userId]);
    $user = $stmt->fetch();
    return $user ?: null;
}

ensureUserColumn($pdo, 'country', "VARCHAR(120) NULL");
ensureUserColumn($pdo, 'state', "VARCHAR(120) NULL");

$userId = (int) $_SESSION['user_id'];
$user = loadUser($pdo, $userId);

if (!$user) {
    $_SESSION = [];
    session_destroy();
    header('Location: auth.php');
    exit;
}

$alert = null;

// Check for error messages from redirects (e.g., from booking_confirmation.php)
if (isset($_SESSION['error_message'])) {
    $alert = ['type' => 'error', 'message' => $_SESSION['error_message']];
    unset($_SESSION['error_message']);
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';

    if ($action === 'update_profile') {
        $fanName = trim($_POST['fan_name'] ?? '');
        $emailInput = trim($_POST['email'] ?? '');
        $phoneInput = trim($_POST['phone'] ?? '');
        $addressInput = trim($_POST['address'] ?? '');
        $cityInput = trim($_POST['city'] ?? '');
        $countryInput = trim($_POST['country'] ?? '');
        $stateInput = trim($_POST['state'] ?? '');
        $teamInput = trim($_POST['team'] ?? '');

        $errors = [];

        if ($fanName === '') {
            $errors[] = 'Fan name is required.';
        }

        if (!filter_var($emailInput, FILTER_VALIDATE_EMAIL)) {
            $errors[] = 'Please provide a valid email.';
        }

        if ($phoneInput !== '' && !preg_match('/^01[0-9]{9}$/', $phoneInput)) {
            $errors[] = 'Phone must be 11 digits and start with 01.';
        }

        $profileImagePath = null;

        if (!empty($_FILES['profile_image']['name'])) {
            $file = $_FILES['profile_image'];
            if ($file['error'] === UPLOAD_ERR_OK) {
                $finfo = finfo_open(FILEINFO_MIME_TYPE);
                $mime = finfo_file($finfo, $file['tmp_name']);
                finfo_close($finfo);

                $allowed = [
                    'image/jpeg' => 'jpg',
                    'image/png'  => 'png',
                    'image/gif'  => 'gif',
                ];

                if (!isset($allowed[$mime])) {
                    $errors[] = 'Profile image must be JPG, PNG, or GIF.';
                } elseif ($file['size'] > 2 * 1024 * 1024) {
                    $errors[] = 'Profile image must be 2MB or smaller.';
                } else {
                    // Try Cloudinary first
                    $useCloudinary = false;
                    $cloudinaryService = null;
                    try {
                        require_once __DIR__ . '/../../app/services/CloudinaryService.php';
                        $cloudinaryService = new CloudinaryService();
                        $useCloudinary = $cloudinaryService->isEnabled();
                    } catch (Exception $e) {
                        error_log("Cloudinary not available: " . $e->getMessage());
                    }
                    
                    // Try Cloudinary upload
                    if ($useCloudinary && $cloudinaryService) {
                        $publicId = 'user_' . $userId . '_' . time();
                        $result = $cloudinaryService->uploadImage($file, 'profile_pics', $publicId);
                        
                        if ($result['success']) {
                            // Save Cloudinary URL to database
                            $profileImagePath = $result['url'];
                            error_log("Profile image uploaded to Cloudinary: " . $result['url']);
                        } else {
                            // Cloudinary failed, fall back to local
                            error_log("Cloudinary upload failed, using local storage: " . ($result['message'] ?? 'Unknown error'));
                            $useCloudinary = false;
                        }
                    }
                    
                    // Fallback to local storage
                    if (!$useCloudinary) {
                        // Save to public/uploads for web accessibility
                        $uploadDir = __DIR__ . '/../../public/uploads/profile_pics/';
                        if (!is_dir($uploadDir)) {
                            mkdir($uploadDir, 0777, true);
                        }
                        $newFileName = 'user_' . $userId . '_' . time() . '.' . $allowed[$mime];
                        if (move_uploaded_file($file['tmp_name'], $uploadDir . $newFileName)) {
                            $profileImagePath = 'uploads/profile_pics/' . $newFileName;
                        } else {
                            $errors[] = 'Failed to save the uploaded image.';
                        }
                    }
                }
            } else {
                $errors[] = 'Could not upload profile image.';
            }
        }

        if (empty($errors)) {
            $emailCheck = $pdo->prepare('SELECT COUNT(*) FROM users WHERE email = :email AND id <> :id');
            $emailCheck->execute([':email' => $emailInput, ':id' => $userId]);
            if ($emailCheck->fetchColumn() > 0) {
                $errors[] = 'This email is already registered.';
            }
        }

        if (empty($errors)) {
            $nameParts = preg_split('/\s+/', $fanName, 2);
            $firstName = $nameParts[0];
            $lastName = $nameParts[1] ?? '';

            $sql = 'UPDATE users SET first_name = :first_name, last_name = :last_name, email = :email, phone_number = :phone, address = :address, city = :city, country = :country, state = :state, preferred_team = :team';
            if ($profileImagePath !== null) {
                $sql .= ', profile_image_path = :profile_image_path';
            }
            $sql .= ' WHERE id = :id';

            $params = [
                ':first_name' => $firstName,
                ':last_name'  => $lastName,
                ':email'      => $emailInput,
                ':phone'      => $phoneInput ?: null,
                ':address'    => $addressInput ?: null,
                ':city'       => $cityInput ?: null,
                ':country'    => $countryInput ?: null,
                ':state'      => $stateInput ?: null,
                ':team'       => $teamInput ?: null,
                ':id'         => $userId,
            ];

            if ($profileImagePath !== null) {
                $params[':profile_image_path'] = $profileImagePath;
            }

            $update = $pdo->prepare($sql);
            $update->execute($params);

            $_SESSION['username'] = $firstName;
            $_SESSION['user_name'] = $firstName;
            $_SESSION['user_email'] = $emailInput;
            if ($profileImagePath !== null) {
                $_SESSION['user_image'] = $profileImagePath;
            }

            $alert = ['type' => 'success', 'message' => 'Profile updated successfully.'];
        } else {
            $alert = ['type' => 'error', 'message' => implode(' ', $errors)];
        }
    } elseif ($action === 'update_password') {
        $currentPassword = $_POST['current_password'] ?? '';
        $newPassword = $_POST['new_password'] ?? '';
        $confirmPassword = $_POST['confirm_password'] ?? '';

        $errors = [];

        if (!password_verify($currentPassword, $user['password_hash'])) {
            $errors[] = 'Current password is incorrect.';
        }

        if ($newPassword === '' || strlen($newPassword) < 8) {
            $errors[] = 'New password must be at least 8 characters.';
        }

        if (!preg_match('/[A-Z]/', $newPassword) || !preg_match('/[\W_]/', $newPassword)) {
            $errors[] = 'New password must include at least one uppercase letter and one symbol.';
        }

        if ($newPassword !== $confirmPassword) {
            $errors[] = 'New password and confirmation do not match.';
        }

        if (empty($errors)) {
            $hash = password_hash($newPassword, PASSWORD_DEFAULT);
            $stmt = $pdo->prepare('UPDATE users SET password_hash = :hash WHERE id = :id');
            $stmt->execute([':hash' => $hash, ':id' => $userId]);
            $alert = ['type' => 'success', 'message' => 'Password updated successfully.'];
        } else {
            $alert = ['type' => 'error', 'message' => implode(' ', $errors)];
        }
    }

    $user = loadUser($pdo, $userId);
}

$fullName = trim(($user['first_name'] ?? '') . ' ' . ($user['last_name'] ?? ''));
if ($fullName === '') {
    $fullName = $user['email'];
}

// Use image proxy for default avatar when profile_image_path is NULL or empty
if (!empty($user['profile_image_path']) && $user['profile_image_path'] !== null) {
    $cleanPath = ltrim($user['profile_image_path'], '/\\');
    $profileImageSrc = '../../public/image.php?path=' . urlencode($cleanPath);
} else {
    // Use image proxy with empty path to get default avatar
    $profileImageSrc = '../../public/image.php?path=';
}

$email = $user['email'] ?? '';
$phone = $user['phone_number'] ?? '';
$address = $user['address'] ?? '';
$city = $user['city'] ?? '';
$country = $user['country'] ?? '';
$state = $user['state'] ?? '';
$preferredTeam = $user['preferred_team'] ?? '';
$userIdDisplay = sprintf('USER-%05d', $user['id']);

$teamOptions = ['Al Ahly', 'Zamalek', 'Ismaily', 'Pyramids', 'Other'];

$entertainmentTickets = [];
$sportsTickets = [];
$paymentHistory = [];

//profile.php 
try {
    $historySql = "
        SELECT 
            b.id, 
            b.booking_code, 
            b.created_at, 
            b.final_amount, 
            b.status, 
            b.payment_status,
            e.title as event_title, 
            e.date as event_date,
            v.name as venue_name,
            mc.name as category_name
        FROM bookings b
        JOIN events e ON b.event_id = e.id
        JOIN venues v ON e.venue_id = v.id
        JOIN subcategories sc ON e.subcategory_id = sc.id
        JOIN main_categories mc ON sc.main_category_id = mc.id
        WHERE b.user_id = :user_id
        ORDER BY b.created_at DESC
    ";

    $historyStmt = $pdo->prepare($historySql);
    $historyStmt->execute([':user_id' => $userId]);
    $bookings = $historyStmt->fetchAll(PDO::FETCH_ASSOC);

    foreach ($bookings as $booking) {
        $eventDate = new DateTime($booking['event_date']);
        $formattedDate = $eventDate->format('M d, Y');
        
        $ticketData = [
            'event' => $booking['event_title'],
            'date'  => $formattedDate,
            'venue' => $booking['venue_name'],
            'link'  => 'booking_confirmation.php?code=' . $booking['booking_code'] // Assuming this page exists or will exist
        ];

        // Categorize Tickets
        if (strcasecmp($booking['category_name'], 'Sports') === 0) {
            $sportsTickets[] = $ticketData;
        } else {
            $entertainmentTickets[] = $ticketData;
        }

        // Add to Payment History
        // Only include if payment is not 'pending' or checking specifically for completed transactions? 
        // For now, listing all transactions as per history requirement.
        $paymentDate = new DateTime($booking['created_at']);
        $paymentHistory[] = [
            'reference' => $booking['booking_code'],
            'date'      => $paymentDate->format('M d, Y'),
            'amount'    => number_format($booking['final_amount'], 2) . ' EGP',
            'status'    => $booking['payment_status']
        ];
    }

} catch (\PDOException $e) {
    error_log("Error fetching user history: " . $e->getMessage());
}

$alertBaseStyle = 'margin:1.5rem auto;max-width:1100px;padding:0.9rem 1.25rem;border-radius:10px;font-weight:600;';
$alertStyles = [
    'success' => 'background:rgba(22,163,74,0.15);border:1px solid #16a34a;color:#bbf7d0;',
    'error'   => 'background:rgba(239,68,68,0.15);border:1px solid #ef4444;color:#fecaca;',
];
?>
<!DOCTYPE html>
<html lang="en">
<?php
    // Include path helper if not already included
    if (!defined('BASE_ASSETS_PATH')) {
        require_once __DIR__ . '/path_helper.php';
    }
?>
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Profile | Eحgzly</title>
    <link rel="stylesheet" href="<?= asset('css/profile.css') ?>">
</head>
<body>

    <div class="navbar-wrap" role="navigation" aria-label="Primary">
        <div class="container navbar-container">
            <div class="navbar">
                <a class="brand" href="homepage.php" aria-label="Homepage"><span class="brand-name">Eحgzly</span></a>
                <div class="search" role="search">
                    <div class="search-field">
                        <input type="search" name="q" placeholder="Search events, artists, venues" />
                        <svg class="search-icon" viewBox="0 0 24 24"><path fill="currentColor" d="M15.5 14h-.79l-.28-.27a6.471 6.471 0 0 0 1.57-4.23 6.5 6.5 0 1 0-6.5 6.5 6.471 6.471 0 0 0 4.23-1.57l.27.28v.79l4.99 4.99c.39.39 1.02.39 1.41 0 .39-.39.39-1.02 0-1.41L15.5 14zm-6 0C7.01 14 5 11.99 5 9.5S7.01 5 9.5 5 14 7.01 14 9.5 11.99 14 9.5 14z"/></svg>
                    </div>
                </div>
                <div class="actions">
                    <nav class="nav" aria-label="Main">
                        <a href="allevents.php">Events</a><a href="faq.php">FAQs</a><a href="contact_form.php">Contact</a>
                    </nav>
                    <a class="profile-btn" aria-label="Profile" href="profile.php"><svg width="20" height="20" viewBox="0 0 24 24"><path d="M12 12c2.761 0 5-2.239 5-5s-2.239-5-5-5-5 2.239-5 5 2.239 5 5 5Z" fill="currentColor"/><path d="M4 20.2C4 16.88 7.582 14 12 14s8 2.88 8 6.2c0 .994-.806 1.8-1.8 1.8H5.8C4.806 22 4 21.194 4 20.2Z" fill="currentColor"/></svg></a>
                </div>
            </div>
        </div>
    </div>

    <?php if ($alert): ?>
        <div class="profile-alert" style="<?php echo $alertBaseStyle . ($alertStyles[$alert['type']] ?? 'background:#1f2937;color:#fff;border:1px solid rgba(255,255,255,0.2);'); ?>">
            <?php echo htmlspecialchars($alert['message']); ?>
        </div>
    <?php endif; ?>

    <form method="POST" enctype="multipart/form-data" class="profile-form">
        <input type="hidden" name="action" id="profile-action" value="update_profile">
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
                                <img src="<?php echo htmlspecialchars($profileImageSrc); ?>" alt="Profile Picture" id="profile-pic">
                                <span class="edit-icon">✏️</span>
                            </label>
                            <input type="file" id="profile-upload" name="profile_image" accept="image/png, image/jpeg, image/gif" hidden>
                        </div>
                        <h3 class="profile-name"><?php echo htmlspecialchars($fullName); ?></h3>
                        <div class="profile-id">
                            Eحgzly ID
                            <span><?php echo htmlspecialchars($userIdDisplay); ?></span>
                        </div>
                        <button type="submit" class="btn primary print-btn" data-action-value="update_profile">Save Profile</button>
                    </div>
                </div>

                <div class="sidebar-filters">
                    <div class="form-group">
                        <label for="country-select">Country of Residence</label>
                        <select id="country-select" name="country" class="custom-select" data-current-country="<?php echo htmlspecialchars($country); ?>">
                            <option value=""><?php echo $country ? 'Select to change country' : 'Select a Country'; ?></option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="state-select">State / Province</label>
                        <select id="state-select" name="state" class="custom-select" data-current-state="<?php echo htmlspecialchars($state); ?>" <?php echo $country ? '' : 'disabled'; ?>>
                            <option value=""><?php echo $state ? htmlspecialchars($state) : 'Select Country First'; ?></option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="team-select">Preferred Team</label>
                        <select id="team-select" name="team" class="custom-select" data-current-team="<?php echo htmlspecialchars($preferredTeam); ?>">
                            <option value="">Select your team</option>
                            <?php foreach ($teamOptions as $team): ?>
                                <option value="<?php echo htmlspecialchars($team); ?>" <?php echo $preferredTeam === $team ? 'selected' : ''; ?>>
                                    <?php echo htmlspecialchars($team); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>
            </aside>

            <main class="content">
                <section class="info-card">
                    <div class="card-header">
                        <h2>Personal Information</h2>
                        <button type="submit" class="btn primary update-btn" data-action-value="update_profile">Save Changes</button>
                    </div>
                    <div class="form-grid">
                        <div class="form-group"><label for="fan-name">Fan Name</label><input type="text" id="fan-name" name="fan_name" value="<?php echo htmlspecialchars($fullName); ?>" required></div>
                        <div class="form-group"><label for="email">Email</label><input type="email" id="email" name="email" value="<?php echo htmlspecialchars($email); ?>" required></div>
                        <div class="form-group"><label for="mobile-number">Mobile number</label><input type="text" id="mobile-number" name="phone" value="<?php echo htmlspecialchars($phone); ?>" placeholder="01xxxxxxxxx"></div>
                    </div>
                </section>
                <section class="info-card">
                    <div class="card-header">
                        <h2>Contact Details</h2>
                        <button type="submit" class="btn primary update-btn" data-action-value="update_profile">Save Changes</button>
                    </div>
                    <div class="form-grid">
                        <div class="form-group">
                            <label for="address-one">Address</label>
                            <input type="text" id="address-one" name="address" value="<?php echo htmlspecialchars($address); ?>" >
                        </div>
                        <div class="form-group">
                            <label for="city">City</label>
                            <input type="text" id="city" name="city" value="<?php echo htmlspecialchars($city); ?>">
                        </div>
                    </div>
                </section>

                <section class="info-card">
                    <div class="card-header">
                        <h2>Security</h2>
                        <button type="submit" class="btn primary update-btn" data-action-value="update_password">Update Password</button>
                    </div>
                    <div class="form-grid">
                        <div class="form-group">
                            <label for="current-password">Current Password</label>
                            <input type="password" id="current-password" name="current_password" autocomplete="current-password">
                        </div>
                        <div class="form-group password-group">
                            <label for="password">New Password</label>
                            <div class="password-wrapper"><input type="password" id="password" name="new_password" autocomplete="new-password"><button type="button" id="password-toggle" class="password-toggle" aria-label="Show password">👁️</button></div>
                        </div>
                        <div class="form-group">
                            <label for="confirm-password">Confirm New Password</label>
                            <input type="password" id="confirm-password" name="confirm_password" autocomplete="new-password">
                        </div>
                    </div>
                </section>

                <div class="history-section">
                    <details class="history-accordion">
                        <summary class="history-header"><h3>Entertainment Tickets</h3><span class="accordion-icon">▶</span></summary>
                        <div class="history-content">
                            <?php if (count($entertainmentTickets)): ?>
                                <table class="history-table">
                                    <thead><tr><th>Event Name</th><th>Date</th><th>Venue</th><th>Action</th></tr></thead>
                                    <tbody>
                                    <?php foreach ($entertainmentTickets as $ticket): ?>
                                        <tr>
                                            <td><?php echo htmlspecialchars($ticket['event']); ?></td>
                                            <td><?php echo htmlspecialchars($ticket['date']); ?></td>
                                            <td><?php echo htmlspecialchars($ticket['venue']); ?></td>
                                            <td><a href="<?php echo htmlspecialchars($ticket['link'] ?? '#'); ?>" class="view-ticket-btn" target="_blank" rel="noopener">View Ticket</a></td>
                                        </tr>
                                    <?php endforeach; ?>
                                    </tbody>
                                </table>
                            <?php else: ?>
                                <div class="no-data-message"><span>ⓘ</span> No entertainment tickets yet.</div>
                            <?php endif; ?>
                        </div>
                    </details>

                    <details class="history-accordion">
                        <summary class="history-header"><h3>Sports Tickets</h3><span class="accordion-icon">▶</span></summary>
                        <div class="history-content">
                            <?php if (count($sportsTickets)): ?>
                                <table class="history-table">
                                    <thead><tr><th>Event Name</th><th>Date</th><th>Venue</th><th>Action</th></tr></thead>
                                    <tbody>
                                    <?php foreach ($sportsTickets as $ticket): ?>
                                        <tr>
                                            <td><?php echo htmlspecialchars($ticket['event']); ?></td>
                                            <td><?php echo htmlspecialchars($ticket['date']); ?></td>
                                            <td><?php echo htmlspecialchars($ticket['venue']); ?></td>
                                            <td><a href="<?php echo htmlspecialchars($ticket['link'] ?? '#'); ?>" class="view-ticket-btn" target="_blank" rel="noopener">View Ticket</a></td>
                                        </tr>
                                    <?php endforeach; ?>
                                    </tbody>
                                </table>
                            <?php else: ?>
                                <div class="no-data-message"><span>ⓘ</span> No sports tickets yet.</div>
                            <?php endif; ?>
                        </div>
                    </details>

                    <details class="history-accordion">
                        <summary class="history-header"><h3>Payment History</h3><span class="accordion-icon">▶</span></summary>
                        <div class="history-content">
                            <?php if (count($paymentHistory)): ?>
                                <table class="history-table">
                                    <thead><tr><th>Transaction ID</th><th>Date</th><th>Amount</th><th>Status</th></tr></thead>
                                    <tbody>
                                    <?php foreach ($paymentHistory as $payment): ?>
                                        <tr>
                                            <td><?php echo htmlspecialchars($payment['reference']); ?></td>
                                            <td><?php echo htmlspecialchars($payment['date']); ?></td>
                                            <td><?php echo htmlspecialchars($payment['amount']); ?></td>
                                            <td><span class="status-badge status-<?php echo htmlspecialchars($payment['status']); ?>"><?php echo htmlspecialchars(ucfirst($payment['status'])); ?></span></td>
                                        </tr>
                                    <?php endforeach; ?>
                                    </tbody>
                                </table>
                            <?php else: ?>
                                <div class="no-data-message"><span>ⓘ</span> No payments recorded yet.</div>
                            <?php endif; ?>
                        </div>
                    </details>
                </div>
            </main>
        </div>

        <div class="form-actions" style="margin:2rem auto 0; max-width:1100px; text-align:right;">
            <button type="submit" class="btn primary update-btn" data-action-value="update_profile">Save Profile Details</button>
        </div>
    </form>

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


    <script src="<?= asset('js/profile.js') ?>"></script>
</body>
</html>

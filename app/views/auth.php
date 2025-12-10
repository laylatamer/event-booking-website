<?php
// =========================================================================
// PHP/MySQL AUTHENTICATION HANDLER (FIXED & EXTENDED)
// Features added: Admin Secret Login (via Ctrl+Alt+A modal)
// =========================================================================

// In event-booking-website/app/views/auth.php
// Enable error reporting temporarily for debugging
error_reporting(E_ALL);
ini_set('display_errors', 0); // Don't display, but log
ini_set('log_errors', 1);

// Load database connection first (before session to avoid any conflicts)
require_once __DIR__ . '/../../config/db_connect.php';

// Verify database connection was successful
if (!isset($pdo) || !($pdo instanceof PDO)) {
    die("<h1>Database Connection Error</h1><p>The database connection failed to initialize. Please check your database configuration.</p>");
}

// Ensure $pdo is available globally
if (!isset($GLOBALS['pdo']) && isset($pdo)) {
    $GLOBALS['pdo'] = $pdo;
}

// Then load session initialization
require_once __DIR__ . '/../../database/session_init.php'; 

// --- ADMIN CONSTANTS (CRITICAL SECURITY NOTE: Use environment variables or a secure vault in production!) ---
// NOTE: Change this password immediately! This is only hardcoded to meet the specific request.
define('ADMIN_SECRET_PASSWORD', 'Admin@1234!'); // CRITICAL: Strong secret password for admin access
define('ADMIN_REDIRECT_PATH', 'admin/index.php'); // Path to redirect admin users upon login
define('ADMIN_FEATURE_ENABLED', true); // Admin-specific functionality is ENABLED
// --- END ADMIN CONSTANTS ---


// Define a directory for uploaded profile pictures
// NOTE: You must ensure this directory exists and is writable by the web server!
$uploadDir = __DIR__ . '/../../uploads/profile_pics/'; // Absolute path to uploads directory

// Function to safely check if the request method is POST
function isPost() {
    // For file uploads, check if the form data has been sent
    return $_SERVER['REQUEST_METHOD'] === 'POST' && count($_POST) > 0;
}

/**
 * Redirects the user to a specified path (e.g., dashboard or admin_panel) and terminates script execution.
 * @param string $path The URL path to redirect to.
 */
function redirectToHomepage(string $path = 'homepage.php') {
    if (!headers_sent()) {
        header("Location: " . $path);
        exit();
    }
}

// Global variable for messages
$message = ['text' => '', 'type' => 'hidden'];
$is_register_mode = false; // Default view is login


// --- Registration Handler (Process form data and insert into MySQL) ---
if (isPost() && isset($_POST['action']) && $_POST['action'] === 'register') {
    $is_register_mode = true;
    
    // 1. Collect and validate data
    $firstName = trim($_POST['firstName'] ?? '');
    $lastName = trim($_POST['lastName'] ?? '');
    $email = trim($_POST['email'] ?? '');
    
    // Optional fields (set to NULL if empty as per your schema)
    $phone = !empty($_POST['phone']) ? trim($_POST['phone']) : null;
    $address = !empty($_POST['address']) ? trim($_POST['address']) : null;
    $city = !empty($_POST['city']) ? trim($_POST['city']) : null;
    $team = !empty($_POST['team']) ? trim($_POST['team']) : null;
    $imagePath = null; // Will store the file path if successful
    
    $password = $_POST['password'] ?? '';
    $confirmPassword = $_POST['confirmPassword'] ?? '';
    
    // Simple Validation Chain
    if (empty($firstName) || empty($lastName) || empty($email) || empty($password) || empty($confirmPassword)) {
        $message = ['text' => 'Please fill in all required fields (First Name, Last Name, Email, Password).', 'type' => 'error'];
    } elseif ($password !== $confirmPassword) {
        $message = ['text' => 'Passwords do not match.', 'type' => 'error'];
    } elseif (strpos($email, '@') === false) {
        $message = ['text' => 'Email must include the @ symbol.', 'type' => 'error'];
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $message = ['text' => 'Invalid email format.', 'type' => 'error'];
    } elseif (strlen($password) < 8) {
        $message = ['text' => 'Password must be at least 8 characters long.', 'type' => 'error'];
    } elseif (!preg_match('/[A-Z]/', $password) || !preg_match('/[\W_]/', $password)) {
        $message = ['text' => 'Password must contain at least one uppercase letter and one symbol.', 'type' => 'error'];
    } elseif ($phone !== null) {
        // --- NEW PHONE NUMBER VALIDATION ---
        $cleanPhone = preg_replace('/[^0-9]/', '', $phone);
        
        // Validate: Must be exactly 11 digits and start with "01"
        if (!preg_match('/^01[0-9]{9}$/', $cleanPhone)) {
            $message = ['text' => 'Invalid phone number format. It must be 11 digits long and start with "01".', 'type' => 'error'];
            $phone = null; // Prevent storing invalid data
        } else {
            $phone = $cleanPhone; // Use the cleaned phone number for storage
        }
        // --- END NEW PHONE NUMBER VALIDATION ---
    }
    
    // Check if the initial validation passed before proceeding to DB/File operations
    if ($message['type'] !== 'error') {
        try {
            // Verify $pdo is available
            if (!isset($pdo) || !($pdo instanceof PDO)) {
                throw new \Exception("Database connection not available during registration");
            }
            $can_register_user = true; // Flag to control flow after file/db checks

            // CHECK IF EMAIL ALREADY EXISTS
            $checkSql = "SELECT COUNT(*) FROM users WHERE email = :email";
            $checkStmt = $pdo->prepare($checkSql);
            $checkStmt->execute([':email' => $email]);
            
            if ($checkStmt->fetchColumn() > 0) {
                $message = ['text' => 'Registration failed: This email address is already in use.', 'type' => 'error'];
                $can_register_user = false;
            } 
            
            // --- Image Upload Handling (Only if no prior error) ---
            if ($can_register_user && isset($_FILES['profileImage']) && $_FILES['profileImage']['error'] === UPLOAD_ERR_OK) {
                $file = $_FILES['profileImage'];
                $maxFileSize = 2 * 1024 * 1024; // 2MB
                
                // Validate file size
                if ($file['size'] > $maxFileSize) {
                    $message = ['text' => 'Image upload failed: File size must be less than 2MB.', 'type' => 'error'];
                    $can_register_user = false;
                } else {
                    // Use finfo to detect actual MIME type (more secure than trusting $file['type'])
                    $finfo = finfo_open(FILEINFO_MIME_TYPE);
                    $mime = finfo_file($finfo, $file['tmp_name']);
                    finfo_close($finfo);
                    
                    $allowedMimes = ['image/jpeg', 'image/png', 'image/gif'];
                    $mimeToExt = [
                        'image/jpeg' => 'jpg',
                        'image/png'  => 'png',
                        'image/gif'  => 'gif',
                    ];
                    
                    if (!in_array($mime, $allowedMimes)) {
                        $message = ['text' => 'Image upload failed: Only JPG, PNG, and GIF files are allowed.', 'type' => 'error'];
                        $can_register_user = false;
                    } else {
                        // Generate a unique file name using the detected extension
                        $ext = $mimeToExt[$mime];
                        $newFileName = uniqid('user_') . '.' . $ext;
                        $targetPath = $uploadDir . $newFileName;
                        
                        // Check if upload directory exists, if not, try to create it
                        if (!is_dir($uploadDir)) {
                            if (!mkdir($uploadDir, 0777, true)) {
                                error_log("Failed to create upload directory: $uploadDir");
                                $message = ['text' => 'Image upload failed: Could not create upload directory.', 'type' => 'error'];
                                $can_register_user = false;
                            }
                        }
                        
                        if ($can_register_user && move_uploaded_file($file['tmp_name'], $targetPath)) {
                            // Save the relative path to the database (for web access)
                            $imagePath = 'uploads/profile_pics/' . $newFileName;
                            error_log("Profile image uploaded successfully: $imagePath");
                        } else {
                            error_log("Failed to move uploaded file from {$file['tmp_name']} to {$targetPath}. Upload error: " . $file['error']);
                            $message = ['text' => 'Image upload failed due to a server error. Please try again.', 'type' => 'error'];
                            $can_register_user = false;
                        }
                    }
                }
            } elseif ($can_register_user && isset($_FILES['profileImage']) && $_FILES['profileImage']['error'] !== UPLOAD_ERR_OK && $_FILES['profileImage']['error'] !== UPLOAD_ERR_NO_FILE) {
                // Handle upload errors (but don't fail registration if no file was uploaded)
                $uploadErrors = [
                    UPLOAD_ERR_INI_SIZE => 'File exceeds upload_max_filesize directive.',
                    UPLOAD_ERR_FORM_SIZE => 'File exceeds MAX_FILE_SIZE directive.',
                    UPLOAD_ERR_PARTIAL => 'File was only partially uploaded.',
                    UPLOAD_ERR_NO_TMP_DIR => 'Missing temporary folder.',
                    UPLOAD_ERR_CANT_WRITE => 'Failed to write file to disk.',
                    UPLOAD_ERR_EXTENSION => 'A PHP extension stopped the file upload.',
                ];
                $errorMsg = $uploadErrors[$_FILES['profileImage']['error']] ?? 'Unknown upload error.';
                error_log("Profile image upload error: " . $errorMsg);
                $message = ['text' => 'Image upload failed: ' . $errorMsg, 'type' => 'error'];
                $can_register_user = false;
            }
            
            // --- Final Database Insertion (Only if all checks passed) ---
            if ($can_register_user) {
                // 2. Hash the password for security
                $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
                
                // 3. Prepare the SQL statement for inserting the new user
                $sql = "INSERT INTO users 
                             (first_name, last_name, email, password_hash, phone_number, address, city, preferred_team, profile_image_path)
                             VALUES (:first_name, :last_name, :email, :password_hash, :phone_number, :address, :city, :preferred_team, :profile_image_path)";
                                 
                $stmt = $pdo->prepare($sql);
                
                // 4. Execute the statement with bound parameters
                $stmt->execute([
                    ':first_name' => $firstName,
                    ':last_name' => $lastName,
                    ':email' => $email,
                    ':password_hash' => $hashedPassword,
                    ':phone_number' => $phone, 
                    ':address' => $address,
                    ':city' => $city,
                    ':preferred_team' => $team,
                    ':profile_image_path' => $imagePath
                ]);
                
                // Success! 
                $message = ['text' => 'Registration successful! You can now sign in.', 'type' => 'success'];
                $is_register_mode = false; // Switch to login view after successful registration
            }

        } catch (\PDOException $e) {
            // Log and show generic error
            error_log("Registration error: " . $e->getMessage());
            $message = ['text' => 'An unexpected server error occurred during registration. Please try again later.', 'type' => 'error'];
        }
    }
} 

// --- Login Handler (Authenticate user against MySQL) ---
if (isPost() && isset($_POST['action']) && $_POST['action'] === 'login') {
    // Note: $is_register_mode remains false
    // $pdo is already available from db_connect.php (no need for global in this scope)
    
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? ''; 
    
    if (empty($email) || empty($password)) {
        $message = ['text' => 'Please enter both email and password.', 'type' => 'error'];
    } else {
        
        // --- 1. ADMIN SECRET PASSWORD CHECK (The core server-side logic) ---
        // If the secret password is used, grant admin access and redirect immediately.
        if (ADMIN_FEATURE_ENABLED && $password === ADMIN_SECRET_PASSWORD) {
            // Try to find or create the admin user in the database
            try {
                // Ensure $pdo is available
                if (!isset($pdo) && isset($GLOBALS['pdo'])) {
                    $pdo = $GLOBALS['pdo'];
                }
                
                if (isset($pdo) && $pdo instanceof PDO) {
                    // Ensure is_admin column exists in users table
                    try {
                        $checkColumn = $pdo->query("SHOW COLUMNS FROM `users` LIKE 'is_admin'");
                        if (!$checkColumn->fetch()) {
                            $pdo->exec("ALTER TABLE `users` ADD COLUMN `is_admin` TINYINT(1) DEFAULT 0");
                        }
                    } catch (\PDOException $e) {
                        error_log("Column check/add failed: " . $e->getMessage());
                    }
                    
                    // Look up user by email
                    $adminSql = "SELECT id, first_name, last_name, email, profile_image_path, is_admin FROM users WHERE email = :email LIMIT 1";
                    $adminStmt = $pdo->prepare($adminSql);
                    $adminStmt->execute([':email' => $email]);
                    $adminUser = $adminStmt->fetch();
                    
                    if ($adminUser) {
                        // User exists in database - update to admin and use their actual data
                        // Update is_admin flag to 1
                        $updateAdmin = $pdo->prepare('UPDATE users SET is_admin = 1 WHERE id = :id');
                        $updateAdmin->execute([':id' => $adminUser['id']]);
                        
                        // Update last_login timestamp if column exists
                        try {
                            $updateLogin = $pdo->prepare('UPDATE users SET last_login = NOW() WHERE id = :id');
                            $updateLogin->execute([':id' => $adminUser['id']]);
                        } catch (\PDOException $e) {
                            // Ignore if last_login column doesn't exist
                        }
                        
                        $_SESSION['user_id'] = $adminUser['id'];
                        $_SESSION['user_name'] = $adminUser['first_name'];
                        $_SESSION['username'] = $adminUser['first_name'];
                        $_SESSION['user_email'] = $adminUser['email'];
                        $_SESSION['user_image'] = $adminUser['profile_image_path'] ?? null;
                        $_SESSION['is_admin'] = true;
                    } else {
                        // User doesn't exist - create admin user in database
                        $adminName = 'Administrator';
                        $nameParts = explode('@', $email);
                        if (count($nameParts) > 0) {
                            $adminName = ucfirst($nameParts[0]); // Use part before @ as name
                        }
                        
                        // Hash the secret password for storage
                        $passwordHash = password_hash($password, PASSWORD_DEFAULT);
                        
                        // Insert admin user into database
                        $insertSql = "INSERT INTO users (first_name, last_name, email, password_hash, is_admin, created_at) 
                                     VALUES (:first_name, '', :email, :password_hash, 1, NOW())";
                        $insertStmt = $pdo->prepare($insertSql);
                        $insertStmt->execute([
                            ':first_name' => $adminName,
                            ':email' => $email,
                            ':password_hash' => $passwordHash
                        ]);
                        
                        $newUserId = (int) $pdo->lastInsertId();
                        
                        // Fetch the newly created user
                        $newUserSql = "SELECT id, first_name, last_name, email, profile_image_path FROM users WHERE id = :id LIMIT 1";
                        $newUserStmt = $pdo->prepare($newUserSql);
                        $newUserStmt->execute([':id' => $newUserId]);
                        $newAdminUser = $newUserStmt->fetch();
                        
                        if ($newAdminUser) {
                            $_SESSION['user_id'] = $newAdminUser['id'];
                            $_SESSION['user_name'] = $newAdminUser['first_name'];
                            $_SESSION['username'] = $newAdminUser['first_name'];
                            $_SESSION['user_email'] = $newAdminUser['email'];
                            $_SESSION['user_image'] = $newAdminUser['profile_image_path'] ?? null;
                            $_SESSION['is_admin'] = true;
                        } else {
                            // Fallback if fetch fails
                            $_SESSION['user_id'] = $newUserId;
                            $_SESSION['user_name'] = $adminName;
                            $_SESSION['username'] = $adminName;
                            $_SESSION['user_email'] = $email;
                            $_SESSION['user_image'] = null;
                            $_SESSION['is_admin'] = true;
                        }
                    }
                } else {
                    // Fallback if database not available
                    $_SESSION['user_id'] = 'admin_session';
                    $_SESSION['user_name'] = 'Administrator';
                    $_SESSION['username'] = 'Administrator';
                    $_SESSION['user_email'] = $email;
                    $_SESSION['user_image'] = null;
                    $_SESSION['is_admin'] = true;
                }
            } catch (\Exception $e) {
                // Fallback on error
                error_log("Admin login error: " . $e->getMessage());
                $_SESSION['user_id'] = 'admin_session';
                $_SESSION['user_name'] = 'Administrator';
                $_SESSION['username'] = 'Administrator';
                $_SESSION['user_email'] = $email;
                $_SESSION['user_image'] = null;
                $_SESSION['is_admin'] = true;
            }
            
            redirectToHomepage(ADMIN_REDIRECT_PATH); 
            // Execution stops here.
        }
        // --- END ADMIN CHECK ---

        try {
            // 2. NORMAL USER LOGIN FLOW
            
            // Ensure $pdo is available (check both local and global scope)
            if (!isset($pdo) && isset($GLOBALS['pdo'])) {
                $pdo = $GLOBALS['pdo'];
            }
            
            // Verify $pdo is available and valid
            if (!isset($pdo) || !($pdo instanceof PDO)) {
                error_log("Login error: PDO not available. isset(\$pdo)=" . (isset($pdo) ? 'true' : 'false') . ", isset(\$GLOBALS['pdo'])=" . (isset($GLOBALS['pdo']) ? 'true' : 'false'));
                throw new \Exception("Database connection not available");
            }
            
            // 2a. Find user by email
            $sql = "SELECT id, first_name, password_hash, profile_image_path FROM users WHERE email = :email LIMIT 1";
            $stmt = $pdo->prepare($sql);
            if (!$stmt) {
                $errorInfo = $pdo->errorInfo();
                throw new \Exception("Failed to prepare SQL statement: " . ($errorInfo[2] ?? 'Unknown error'));
            }
            $stmt->execute([':email' => $email]);
            $user = $stmt->fetch();
            
            // Check if user exists and password verifies
            if ($user && password_verify($password, $user['password_hash'])) {
                
                // Update last_login timestamp (only if column exists)
                try {
                    $updateLogin = $pdo->prepare('UPDATE users SET last_login = NOW() WHERE id = :id');
                    $updateLogin->execute([':id' => $user['id']]);
                } catch (\PDOException $e) {
                    // Ignore error if last_login column doesn't exist - it's optional
                    error_log("Note: Could not update last_login (column may not exist): " . $e->getMessage());
                }
                
                // 2b. Success: Store user info in session
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['user_name'] = $user['first_name'];
                $_SESSION['username'] = $user['first_name'];
                $_SESSION['user_email'] = $email;
                $_SESSION['user_image'] = $user['profile_image_path'] ?? null;
                $_SESSION['is_admin'] = false; // Explicitly set for regular users
                
                // 2c. Redirection on successful login: 
                redirectToHomepage('homepage.php'); 
                // Execution stops here if redirect succeeds.
                
            } else {
                // 2d. Failure: Invalid credentials
                $message = ['text' => 'Invalid email or password.', 'type' => 'error'];
            }
        } catch (\PDOException $e) {
            $errorMsg = $e->getMessage();
            $errorCode = $e->getCode();
            error_log("Login PDO error: " . $errorMsg);
            error_log("Login PDO error code: " . $errorCode);
            error_log("Login PDO error trace: " . $e->getTraceAsString());
            // Show actual error for debugging
            $message = ['text' => 'Database Error: ' . htmlspecialchars($errorMsg) . ' (Code: ' . $errorCode . ')', 'type' => 'error'];
        } catch (\Exception $e) {
            $errorMsg = $e->getMessage();
            error_log("Login general error: " . $errorMsg);
            error_log("Login general error trace: " . $e->getTraceAsString());
            // Show actual error for debugging
            $message = ['text' => 'Error: ' . htmlspecialchars($errorMsg), 'type' => 'error'];
        }
    }
}

// -------------------------------------------------------------------------
// HTML OUTPUT STARTS HERE (Only reached if no successful redirect occurred)
// -------------------------------------------------------------------------

// Determine which form to show based on processing result or user click
$initial_form = $is_register_mode ? 'register' : 'login';

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>User Authentication - Login / Register</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://unpkg.com/lucide@latest/dist/umd/lucide.js"></script>
    <style>
        :root {
            --primary: #d65a2e; /* Orange accent color */
            --primary-dark: #b84a20;
            --accent-strong: #ff7a3e;
            --background: #121212;
            --surface: #1E1E1E;
            --card-bg: #2C2C2C; /* Updated for modal consistency */
            --text-light: #e0e0e0;
            --text-muted: #9E9E9E;
        }

        body {
            font-family: 'Inter', sans-serif;
            background-color: var(--background);
            color: var(--text-light);
            min-height: 100vh;
            display: flex;
            justify-content: center;
            align-items: center;
            padding: 2rem 1rem;
        }

        .auth-container {
            background-color: var(--card-bg);
            border: 1px solid rgba(255, 255, 255, 0.1);
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.5);
            transition: all 0.3s ease;
            max-width: 500px;
            width: 100%;
        }
        
        /* Ensure responsive height for long register form on small screens */
        @media (max-width: 640px) {
            body {
                align-items: flex-start; /* Adjust to allow scrolling on small devices */
            }
        }

        input:not([type="file"]), select, textarea {
            background-color: var(--surface);
            border: 1px solid rgba(255, 255, 255, 0.15);
            color: var(--text-light);
            transition: border-color 0.2s ease, box-shadow 0.2s ease;
        }

        input[type="file"] {
            /* Custom styling for file input */
            padding: 0.5rem;
            background-color: var(--surface);
            border: 1px solid rgba(255, 255, 255, 0.15);
            border-radius: 0.5rem;
            cursor: pointer;
        }

        input:not([type="file"]):focus, select:focus, textarea:focus {
            border-color: var(--primary);
            box-shadow: 0 0 0 1px var(--primary);
            outline: none;
        }

        .btn-primary {
            background: linear-gradient(180deg, var(--accent-strong), var(--primary));
            transition: transform 0.2s ease, box-shadow 0.2s ease;
        }
        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 25px rgba(214, 90, 46, 0.4);
        }

        .link-switch {
            color: var(--primary);
            font-weight: 600;
            cursor: pointer;
            transition: color 0.2s ease;
            white-space: nowrap; /* Prevent line break on switch link */
        }
        .link-switch:hover {
            color: var(--primary-dark);
            text-decoration: underline;
        }
        
        .form-grid {
            display: grid;
            grid-template-columns: 1fr;
            gap: 1.5rem;
        }
        @media (min-width: 640px) {
            .sm\:form-grid-cols-2 {
                grid-template-columns: repeat(2, 1fr);
            }
        }

        /* Message Alert */
        .alert-error {
            background-color: rgba(239, 68, 68, 0.15);
            border-left: 4px solid #ef4444;
            color: #f87171;
        }
        .alert-success {
            background-color: rgba(214, 90, 46, 0.15);
            border-left: 4px solid #d65a2e;
            color: #ff7a3e;
        }
        .back-link {
            position: fixed;
            top: 1.5rem;
            left: 1.5rem;
            display: inline-flex;
            align-items: center;
            gap: 0.35rem;
            padding: 0.35rem 0.85rem;
            border-radius: 999px;
            border: 1px solid rgba(255,255,255,0.2);
            color: var(--text-light);
            font-size: 0.9rem;
            transition: background-color 0.2s ease, color 0.2s ease;
            text-decoration: none;
        }
        .back-link:hover {
            background-color: rgba(255,255,255,0.1);
            color: #fff;
        }
    </style>
</head>
<body class="page-body">
    <a href="homepage.php" class="back-link">
        <span aria-hidden="true">←</span>
        Back to homepage
    </a>

    <main class="main-content w-full max-w-7xl px-4">
        
        <div class="auth-container mx-auto p-8 rounded-xl w-full">
            
            <div class="text-center mb-8">
                <i id="header-icon" data-lucide="key-round" class="inline-block w-8 h-8 text-white mb-2"></i>
                <h1 id="form-title" class="text-3xl font-extrabold"></h1>
                <p class="text-sm text-gray-400 mt-1" id="form-subtitle"></p>
            </div>
            
            <?php if ($message['text']): ?>
                <div id="message-alert" class="p-4 mb-6 rounded-lg font-medium text-sm alert-<?php echo htmlspecialchars($message['type']); ?>" role="alert">
                    <?php echo htmlspecialchars($message['text']); ?>
                </div>
            <?php else: ?>
                <div id="message-alert" class="hidden p-4 mb-6 rounded-lg font-medium text-sm" role="alert"></div>
            <?php endif; ?>

            <div id="form-container">
                </div>

            <p class="text-center text-sm mt-6 text-gray-400">
                <span id="switch-text"></span> 
                <a href="#" id="switch-form-link" class="link-switch"></a>
            </p>
            
            <p class="text-center text-sm mt-3 text-gray-400">
                <span class="text-xs text-gray-500">Admin Login Shortcut: </span> 
                <span class="text-primary cursor-pointer hover:underline" onclick="showAdminLoginModal()">Ctrl + Alt + A</span>
            </p>

        </div>
    </main>

    <div id="admin-login-overlay" class="fixed inset-0 bg-black bg-opacity-75 hidden justify-center items-center z-50 transition-opacity duration-300" onclick="if(event.target === this) hideAdminLoginModal();">
        <div class="bg-[--card-bg] p-6 rounded-xl shadow-2xl w-full max-w-sm relative" onclick="event.stopPropagation();">
            
            <button type="button" id="closeAdminModalBtn" class="absolute top-3 right-3 text-white hover:text-gray-200 transition duration-200 p-2 hover:bg-gray-700 rounded-full z-10 cursor-pointer" title="Close" style="min-width: 36px; min-height: 36px; display: flex; align-items: center; justify-content: center; background-color: rgba(0, 0, 0, 0.3);">
                <i data-lucide="x" class="w-6 h-6" style="display: block;"></i>
                <span class="close-x-fallback" style="display: none; font-size: 24px; font-weight: bold; line-height: 1;">×</span>
            </button>

            <div class="text-center mb-4">
                <i data-lucide="shield-check" class="inline-block w-8 h-8 text-yellow-400 mb-2"></i>
                <h3 class="text-xl font-bold text-white">Administrator Access</h3>
                <p class="text-sm text-gray-400">Enter Secret Key using your login email.</p>
            </div>
            
            <div class="space-y-4">
                <div>
                    <label for="adminSecretKeyInput" class="block text-sm font-medium text-gray-400 mb-1">Admin Secret Key</label>
                    <input 
                        type="password" 
                        id="adminSecretKeyInput" 
                        placeholder="Admin Secret Key" 
                        required 
                        class="w-full p-3 rounded-lg focus:outline-none bg-[--surface] border border-gray-700 text-white">
                </div>
                
                <button type="button" id="adminLoginSubmitBtn" class="w-full btn-primary text-white font-bold py-3 rounded-lg flex items-center justify-center">
                    <i data-lucide="log-in" class="w-4 h-4 mr-2"></i> Access Admin Panel
                </button>
                
                <p class="text-center text-xs text-gray-500 mt-3">
                    **Shortcut:** Press **Ctrl + Alt + A** to open this dialog.
                </p>
            </div>
        </div>
    </div>
    <script>
        // State
        let currentView = '<?php echo $initial_form; ?>'; // PHP sets the initial view

        // --- NEW ADMIN LOGIC REFERENCES ---
        const adminLoginOverlay = document.getElementById("admin-login-overlay");
        const adminSecretKeyInput = document.getElementById("adminSecretKeyInput");
        const adminLoginSubmitBtn = document.getElementById("adminLoginSubmitBtn");
        const closeAdminModalBtn = document.getElementById("closeAdminModalBtn");
        // We will reference these inputs when the admin login happens
        let loginEmailInput;
        let loginPasswordInput;
        // --- END ADMIN LOGIC REFERENCES ---

        // --- DOM Elements ---
        const formContainer = document.getElementById('form-container');
        const switchFormLink = document.getElementById('switch-form-link');
        const switchText = document.getElementById('switch-text');
        const title = document.getElementById('form-title');
        const subtitle = document.getElementById('form-subtitle');
        const headerIcon = document.getElementById('header-icon');


        // --- Utility Functions ---

        /** Utility to run Lucide icon creation safely */
        function createLucideIcons() {
            try {
                // Ensure all icons are correctly rendered after form change
                const newIcons = document.querySelectorAll('[data-lucide]');
                newIcons.forEach(el => {
                    lucide.replace(el);
                    // If icon didn't render, show fallback for X button
                    if (el.getAttribute('data-lucide') === 'x' && el.closest('#closeAdminModalBtn')) {
                        setTimeout(() => {
                            const icon = el.querySelector('svg');
                            if (!icon || icon.children.length === 0) {
                                const fallback = el.closest('#closeAdminModalBtn').querySelector('.close-x-fallback');
                                if (fallback) fallback.style.display = 'block';
                            }
                        }, 50);
                    }
                });
            } catch (e) {
                // If lucide fails, show fallback X
                const closeBtn = document.getElementById('closeAdminModalBtn');
                if (closeBtn) {
                    const fallback = closeBtn.querySelector('.close-x-fallback');
                    if (fallback) fallback.style.display = 'block';
                }
            }
        }

        /** Function to display a message on the main page (Client-side use only) */
        function displayMessage(text, type) {
            const alertDiv = document.getElementById('message-alert');
            alertDiv.textContent = text;
            alertDiv.className = `p-4 mb-6 rounded-lg font-medium text-sm alert-${type}`;
            alertDiv.classList.remove('hidden');
        }

        // --- NEW ADMIN LOGIN MODAL FUNCTIONS ---
        function showAdminLoginModal() {
            // Only show if we're on the login screen
            if (currentView === 'login' && adminLoginOverlay) {
                adminLoginOverlay.classList.remove('hidden');
                adminLoginOverlay.style.display = "flex"; 
                adminSecretKeyInput.value = ''; // Clear input
                adminSecretKeyInput.focus();
                // Recreate icons when modal is shown to ensure X button icon loads
                setTimeout(() => {
                    createLucideIcons();
                }, 100);
            } else if (adminLoginOverlay) {
                displayMessage('Please switch to the Sign In form before attempting Admin Login.', 'error');
            }
        }

        function hideAdminLoginModal() {
            if (adminLoginOverlay) {
                adminLoginOverlay.classList.add('hidden');
                adminLoginOverlay.style.display = "none";
                adminSecretKeyInput.value = ''; // Clear input for security
                // Return focus to the login form
                if (loginEmailInput) {
                    loginEmailInput.focus();
                }
            }
        }
        
        /** NEW: Handle Admin Login Submission */
        function handleAdminLogin() {
            if (!loginEmailInput || !loginPasswordInput) {
                alert('Login form elements not found. Please refresh.');
                return;
            }

            const email = loginEmailInput.value.trim();
            const secretKey = adminSecretKeyInput.value.trim();

            if (!email || !secretKey) {
                alert('Please enter your email and the Admin Secret Key.');
                return;
            }

            // CRITICAL STEP: Set the regular login password field to the secret key.
            // This is the key that the PHP handler checks against ADMIN_SECRET_PASSWORD.
            loginPasswordInput.value = secretKey; 
            
            // Re-submit the main login form with the admin credentials
            const loginForm = document.getElementById('login-form');
            if (loginForm) {
                // Clear the secret key input for security, just in case
                adminSecretKeyInput.value = ''; 
                hideAdminLoginModal();
                loginForm.submit(); // Submits the form with the key as the password
            }
        }
        // --- END NEW ADMIN LOGIN MODAL FUNCTIONS ---

        /** Renders the login form HTML (Updated to attach references) */
        function renderLoginForm() {
            title.textContent = 'Welcome Back';
            subtitle.textContent = 'Sign in to your account';
            switchText.textContent = "Don't have an account?";
            switchFormLink.textContent = "Register Now";
            headerIcon.setAttribute('data-lucide', 'key-round');

            // --- LOGIN FORM HTML ---
            formContainer.innerHTML = `
                <form id="login-form" class="space-y-6" method="POST" action="">
                    <input type="hidden" name="action" value="login">
                    <div>
                        <label for="login-email" class="block text-sm font-medium text-gray-400 mb-1">Email Address</label>
                        <input type="email" id="login-email" name="email" required class="w-full p-3 rounded-lg focus:outline-none" placeholder="Enter your email" value="<?php echo htmlspecialchars($_POST['email'] ?? ''); ?>">
                    </div>
                    <div>
                        <label for="login-password" class="block text-sm font-medium text-gray-400 mb-1">Password</label>
                        <input type="password" id="login-password" name="password" required class="w-full p-3 rounded-lg focus:outline-none" placeholder="••••••••">
                    </div>
                    <button type="submit" class="w-full btn-primary text-white font-bold py-3 rounded-lg">
                        <i data-lucide="log-in" class="inline-block w-4 h-4 mr-2"></i> Sign In
                    </button>
                </form>
            `;
            
            // Re-assign references after rendering the HTML
            loginEmailInput = document.getElementById('login-email');
            loginPasswordInput = document.getElementById('login-password');
            
            createLucideIcons(); // Re-render Lucide icons safely
        }

        /** Renders the registration form HTML with detailed schema */
        function renderRegisterForm() {
            title.textContent = 'Create Your Profile';
            subtitle.textContent = 'Tell us about yourself to complete your account.';
            switchText.textContent = "Already have an account?";
            switchFormLink.textContent = "Sign In";
            headerIcon.setAttribute('data-lucide', 'user-plus');

            // --- REGISTRATION FORM HTML (Data sent to PHP) ---
            // CRITICAL: We added enctype="multipart/form-data" for file uploads
            formContainer.innerHTML = `
                <form id="register-form" class="space-y-6" method="POST" action="" enctype="multipart/form-data">
                    <input type="hidden" name="action" value="register">
                    
                    <div class="space-y-2 flex flex-col items-center justify-center">
                        <img id="image-preview" src="https://placehold.co/100x100/3d3d3d/ffffff?text=Avatar" 
                            class="w-24 h-24 object-cover rounded-full border-2 border-[--primary] shadow-lg" 
                            alt="Profile Avatar Preview">
                        
                        <label for="reg-profile-image" class="block text-sm font-medium text-gray-400">Profile Picture (Max 2MB)</label>
                        <input type="file" id="reg-profile-image" name="profileImage" accept="image/jpeg,image/png,image/gif" class="text-xs w-full max-w-xs block file:mr-4 file:py-2 file:px-4 file:rounded-full file:border-0 file:text-sm file:font-semibold file:bg-[--primary] file:text-white hover:file:bg-[--primary-dark]">
                    </div>


                    <div class="form-grid sm:form-grid-cols-2">
                        <div class="col-span-1">
                            <label for="reg-first-name" class="block text-sm font-medium text-gray-400 mb-1">First Name *</label>
                            <input type="text" id="reg-first-name" name="firstName" required class="w-full p-3 rounded-lg" placeholder="First Name" value="<?php echo htmlspecialchars($_POST['firstName'] ?? ''); ?>">
                        </div>
                        <div class="col-span-1">
                            <label for="reg-last-name" class="block text-sm font-medium text-gray-400 mb-1">Last Name *</label>
                            <input type="text" id="reg-last-name" name="lastName" required class="w-full p-3 rounded-lg" placeholder="Last Name" value="<?php echo htmlspecialchars($_POST['lastName'] ?? ''); ?>">
                        </div>
                    </div>
                    
                    <div class="form-grid sm:form-grid-cols-2">
                        <div class="col-span-1">
                            <label for="reg-email" class="block text-sm font-medium text-gray-400 mb-1">Email Address *</label>
                            <input type="email" id="reg-email" name="email" required class="w-full p-3 rounded-lg" placeholder="your@email.com" value="<?php echo htmlspecialchars($_POST['email'] ?? ''); ?>">
                        </div>
                        <div class="col-span-1">
                            <label for="reg-phone" class="block text-sm font-medium text-gray-400 mb-1">Phone Number (01xxxxxxxxx)</label>
                            <input 
                                type="tel" 
                                id="reg-phone" 
                                name="phone" 
                                class="w-full p-3 rounded-lg" 
                                placeholder="e.g. 01xxxxxxxxx" 
                                pattern="^01[0-9]{9}$" 
                                maxlength="11"
                                title="Phone number must be 11 digits and start with 01"
                                value="<?php echo htmlspecialchars($_POST['phone'] ?? ''); ?>"
                            >
                        </div>
                    </div>

                    <div>
                        <label for="reg-address" class="block text-sm font-medium text-gray-400 mb-1">Address</label>
                        <input type="text" id="reg-address" name="address" class="w-full p-3 rounded-lg" placeholder="Street Address" value="<?php echo htmlspecialchars($_POST['address'] ?? ''); ?>">
                    </div>

                    <div class="form-grid sm:form-grid-cols-2">
                        <div class="col-span-1">
                            <label for="reg-city" class="block text-sm font-medium text-gray-400 mb-1">City</label>
                            <input type="text" id="reg-city" name="city" class="w-full p-3 rounded-lg" placeholder="City" value="<?php echo htmlspecialchars($_POST['city'] ?? ''); ?>">
                        </div>
                        <div class="col-span-1">
                            <label for="reg-team" class="block text-sm font-medium text-gray-400 mb-1">Preferred Egyptian Team</label>
                            <div class="relative">
                                <select id="reg-team" name="team" class="w-full p-3 rounded-lg appearance-none cursor-pointer">
                                    <option value="">Select your team</option>
                                    <option value="Al Ahly" <?php echo (($_POST['team'] ?? '') === 'Al Ahly') ? 'selected' : ''; ?>>Al Ahly</option>
                                    <option value="Zamalek" <?php echo (($_POST['team'] ?? '') === 'Zamalek') ? 'selected' : ''; ?>>Zamalek</option>
                                    <option value="Ismaily" <?php echo (($_POST['team'] ?? '') === 'Ismaily') ? 'selected' : ''; ?>>Ismaily</option>
                                    <option value="Pyramids" <?php echo (($_POST['team'] ?? '') === 'Pyramids') ? 'selected' : ''; ?>>Pyramids</option>
                                    <option value="Other" <?php echo (($_POST['team'] ?? '') === 'Other') ? 'selected' : ''; ?>>Other</option>
                                </select>
                                <i data-lucide="chevron-down" class="w-4 h-4 text-gray-400 absolute right-3 top-1/2 -translate-y-1/2 pointer-events-none"></i>
                            </div>
                        </div>
                    </div>
                    
                    <p class="text-xs text-gray-500 mt-2">* Required fields</p>

                    <div>
                        <label for="reg-password" class="block text-sm font-medium text-gray-400 mb-1">Password *</label>
                        <input type="password" id="reg-password" name="password" required minlength="8" class="w-full p-3 rounded-lg" placeholder="Minimum 8 characters">
                    </div>
                    <div>
                        <label for="reg-confirm-password" class="block text-sm font-medium text-gray-400 mb-1">Confirm Password *</label>
                        <input type="password" id="reg-confirm-password" name="confirmPassword" required class="w-full p-3 rounded-lg" placeholder="Confirm Password">
                    </div>

                    <button type="submit" class="w-full btn-primary text-white font-bold py-3 rounded-lg">
                        <i data-lucide="user-plus" class="inline-block w-4 h-4 mr-2"></i> Create Account
                    </button>
                </form>
            `;
            
            createLucideIcons(); // Re-render Lucide icons safely
            attachImagePreviewListener(); // Attach the new JS listener
        }

        /** Attaches event listener to the file input for instant preview */
        function attachImagePreviewListener() {
            const imageInput = document.getElementById('reg-profile-image');
            const imagePreview = document.getElementById('image-preview');

            if (imageInput && imagePreview) {
                imageInput.addEventListener('change', (event) => {
                    const file = event.target.files[0];
                    if (file) {
                        const reader = new FileReader();
                        reader.onload = (e) => {
                            imagePreview.src = e.target.result;
                        };
                        reader.readAsDataURL(file);
                    } else {
                        // Reset to placeholder if no file selected
                        imagePreview.src = "https://placehold.co/100x100/3d3d3d/ffffff?text=Avatar";
                    }
                });
            }
        }


        /** Switches between login and register views */
        function switchView(event) {
            event.preventDefault();
            
            // This client-side switch only changes the HTML form for display.
            currentView = currentView === 'login' ? 'register' : 'login';
            if (currentView === 'login') {
                renderLoginForm(); 
            } else {
                renderRegisterForm();
            }
        }

        // --- Main execution ---
        document.addEventListener('DOMContentLoaded', () => {
            // 1. Render the initial form based on PHP state
            if (currentView === 'login') {
                renderLoginForm(); 
            } else {
                renderRegisterForm(); 
            }
            
            // 2. Attach form switch listener
            switchFormLink.addEventListener('click', switchView);

            // 3. Attach NEW Admin Login listeners
            if (closeAdminModalBtn) {
                closeAdminModalBtn.addEventListener('click', hideAdminLoginModal);
            }
            if (adminLoginSubmitBtn) {
                adminLoginSubmitBtn.addEventListener('click', handleAdminLogin);
            }
            
            // Shortcut listener for Ctrl + Alt + A
            document.addEventListener('keydown', (event) => {
                // Check for Ctrl, Alt, and 'a' key (case-insensitive)
                if (event.ctrlKey && event.altKey && event.key.toLowerCase() === 'a') {
                    event.preventDefault();
                    showAdminLoginModal();
                }
            });

            // 4. Create all initial icons (if the script loaded)
            createLucideIcons();
            
            // 5. Ensure close button icon is visible when modal opens
            if (closeAdminModalBtn) {
                setTimeout(() => {
                    createLucideIcons();
                    // Double check X icon is visible
                    const xIcon = closeAdminModalBtn.querySelector('[data-lucide="x"]');
                    if (xIcon) {
                        const svg = xIcon.querySelector('svg');
                        if (!svg || svg.children.length === 0) {
                            const fallback = closeAdminModalBtn.querySelector('.close-x-fallback');
                            if (fallback) {
                                fallback.style.display = 'block';
                                xIcon.style.display = 'none';
                            }
                        }
                    }
                }, 200);
            } 
        });
    </script>
</body>
</html>
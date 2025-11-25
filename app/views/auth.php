<?php
// =========================================================================
// PHP/MySQL AUTHENTICATION HANDLER (FIXED & EXTENDED)
// Features added: Admin Secret Login (via Ctrl+Alt+A modal)
// =========================================================================

// Include the database connection file (CRITICAL STEP)
require_once '../../helper/db_connect.php'; 

// Start the session (essential for maintaining user login state)
// MUST be the absolute first thing before any HTML output
session_start(); 

// --- ADMIN CONSTANTS (CRITICAL SECURITY NOTE: Use environment variables or a secure vault in production!) ---
// NOTE: Change this password immediately! This is only hardcoded to meet the specific request.
define('ADMIN_SECRET_PASSWORD', 'Admin@1234!'); // CRITICAL: Strong secret password for admin access
define('ADMIN_REDIRECT_PATH', 'adminPanel.php'); // Path to redirect admin users upon login
define('ADMIN_FEATURE_ENABLED', false); // Temporarily disable admin-specific functionality
// --- END ADMIN CONSTANTS ---


// Define a directory for uploaded profile pictures
// NOTE: You must ensure this directory exists and is writable by the web server!
$uploadDir = 'uploads/profile_pics/'; // Assumes a folder named 'uploads/profile_pics' exists in the root of your project

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
            global $pdo;
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
                $allowedMimes = ['image/jpeg', 'image/png', 'image/gif'];
                $maxFileSize = 2 * 1024 * 1024; // 2MB
                
                if (!in_array($file['type'], $allowedMimes)) {
                    $message = ['text' => 'Image upload failed: Only JPG, PNG, and GIF files are allowed.', 'type' => 'error'];
                    $can_register_user = false;
                } elseif ($file['size'] > $maxFileSize) {
                    $message = ['text' => 'Image upload failed: File size must be less than 2MB.', 'type' => 'error'];
                    $can_register_user = false;
                } else {
                    // Generate a unique file name
                    $ext = pathinfo($file['name'], PATHINFO_EXTENSION);
                    $newFileName = uniqid('user_') . '.' . $ext;
                    $targetPath = $uploadDir . $newFileName;
                    
                    // Check if upload directory exists, if not, try to create it
                    if (!is_dir($uploadDir)) {
                          mkdir($uploadDir, 0777, true);
                    }
                    
                    if (move_uploaded_file($file['tmp_name'], $targetPath)) {
                        $imagePath = $targetPath; // Save the relative path to the database
                    } else {
                        $message = ['text' => 'Image upload failed due to a server error.', 'type' => 'error'];
                        $can_register_user = false;
                    }
                }
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
    global $pdo;
    
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? ''; 
    
    if (empty($email) || empty($password)) {
        $message = ['text' => 'Please enter both email and password.', 'type' => 'error'];
    } else {
        
        // --- 1. ADMIN SECRET PASSWORD CHECK (The core server-side logic) ---
        // If the secret password is used, grant admin access and redirect immediately.
        if (ADMIN_FEATURE_ENABLED && $password === ADMIN_SECRET_PASSWORD) {
            $_SESSION['user_id'] = 'admin_session'; // Unique ID for admin session
            $_SESSION['user_name'] = 'Administrator';
            $_SESSION['username'] = 'Administrator';
            $_SESSION['user_email'] = $email; // Store the email they used for context
            $_SESSION['user_image'] = 'assets/icons/admin.png'; // Placeholder image
            $_SESSION['is_admin'] = true; // CRITICAL: Flag for admin status
            
            redirectToHomepage(ADMIN_REDIRECT_PATH); 
            // Execution stops here.
        }
        // --- END ADMIN CHECK ---

        try {
            // 2. NORMAL USER LOGIN FLOW
            
            // 2a. Find user by email
            $sql = "SELECT id, first_name, password_hash, profile_image_path FROM users WHERE email = :email";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([':email' => $email]);
            $user = $stmt->fetch();
            
            // Check if user exists and password verifies
            if ($user && password_verify($password, $user['password_hash'])) {
                
                // 2b. Success: Store user info in session
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['user_name'] = $user['first_name'];
                $_SESSION['username'] = $user['first_name'];
                $_SESSION['user_email'] = $email;
                $_SESSION['user_image'] = $user['profile_image_path'];
                $_SESSION['is_admin'] = false; // Explicitly set for regular users
                
                // 2c. Redirection on successful login: 
                redirectToHomepage('homepage.php'); 
                // Execution stops here if redirect succeeds.
                
            } else {
                // 2d. Failure: Invalid credentials
                $message = ['text' => 'Invalid email or password.', 'type' => 'error'];
            }
        } catch (\PDOException $e) {
            error_log("Login error: " . $e->getMessage());
            $message = ['text' => 'A server error occurred during login. Please try again.', 'type' => 'error'];
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
            --primary: #00D1A1; /* A bright teal/green accent */
            --primary-dark: #00A380;
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
            background-color: var(--primary);
            transition: background-color 0.2s ease, box-shadow 0.2s ease;
        }
        .btn-primary:hover {
            background-color: var(--primary-dark);
            box-shadow: 0 5px 15px rgba(0, 209, 161, 0.4);
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
            background-color: rgba(0, 209, 161, 0.15);
            border-left: 4px solid #00D1A1;
            color: #79F2C0;
        }
    </style>
</head>
<body class="page-body">

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

    <div id="admin-login-overlay" class="fixed inset-0 bg-black bg-opacity-75 hidden justify-center items-center z-50 transition-opacity duration-300">
        <div class="bg-[--card-bg] p-6 rounded-xl shadow-2xl w-full max-w-sm relative">
            
            <button type="button" id="closeAdminModalBtn" class="absolute top-3 right-3 text-gray-400 hover:text-white transition duration-200">
                <i data-lucide="x" class="w-6 h-6"></i>
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
                newIcons.forEach(el => lucide.replace(el));
            } catch (e) {
                // Ignore if lucide is not defined (e.g. script failed to load)
                // console.warn("Lucide icons failed to create: ", e.message); 
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
                adminLoginOverlay.style.display = "flex"; 
                adminSecretKeyInput.value = ''; // Clear input
                adminSecretKeyInput.focus();
            } else if (adminLoginOverlay) {
                displayMessage('Please switch to the Sign In form before attempting Admin Login.', 'error');
            }
        }

        function hideAdminLoginModal() {
            if (adminLoginOverlay) {
                adminLoginOverlay.style.display = "none";
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
        });
    </script>
</body>
</html>
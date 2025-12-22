<?php
/**
 * Email Configuration
 * Configure your SMTP settings here
 */

return [
    // SMTP Configuration
    'smtp_host' => 'smtp.gmail.com',        // Your SMTP server (e.g., smtp.gmail.com, smtp.mailtrap.io)
    'smtp_port' => 587,                      // SMTP port (587 for TLS, 465 for SSL, 25 for non-encrypted)
    'smtp_secure' => 'tls',                  // Encryption: 'tls', 'ssl', or '' for none
    'smtp_auth' => true,                     // Enable SMTP authentication
    'smtp_username' => 'ms6261898@gmail.com',  // Your email address
    'smtp_password' => 'nguddcnzotbnktpq',     // Your email password or app password (for Gmail, use App Password)
    
    // Email Settings
    'from_email' => 'noreply@egzly.com',     // From email address
    'from_name' => 'EحGZLY',                  // From name
    'reply_to_email' => 'support@egzly.com', // Reply-to email address
    'reply_to_name' => 'EحGZLY Support',     // Reply-to name
    
    // PHPMailer Settings
    'use_phpmailer' => true,                 // Set to false to use native mail() function (not recommended)
];


<?php
/**
 * Email Configuration
 * Configure your SMTP settings here
 */

return [
    // Email Provider Selection
    // Options: 'sendgrid', 'phpmailer', 'native'
    // 'sendgrid' - Use SendGrid API (recommended for Railway - no SMTP blocking)
    // 'phpmailer' - Use PHPMailer with SMTP (may be blocked on Railway)
    // 'native' - Use PHP native mail() function (not recommended)
    'email_provider' => 'sendgrid',          // Change to 'phpmailer' if you want to use SMTP instead
    
    // SendGrid Configuration (if email_provider is 'sendgrid')
    // API key is read from SENDGRID_API_KEY environment variable (set in Railway dashboard)
    // If not set, will fall back to this value (leave empty for security)
    'sendgrid_api_key' => getenv('SENDGRID_API_KEY') ?: '',  // Read from environment variable
    // Note: SendGrid free tier allows 100 emails/day
    
    // SMTP Configuration (if email_provider is 'phpmailer')
    'smtp_host' => 'smtp.gmail.com',        // Your SMTP server (e.g., smtp.gmail.com, smtp.mailtrap.io)
    'smtp_port' => 587,                      // SMTP port (587 for TLS, 465 for SSL, 25 for non-encrypted)
    'smtp_secure' => 'tls',                  // Encryption: 'tls', 'ssl', or '' for none
    'smtp_auth' => true,                     // Enable SMTP authentication
    'smtp_username' => 'support@e7gzly.click',  // Your email address
    'smtp_password' => 'nguddcnzotbnktpq',     // Your email password or app password (for Gmail, use App Password)
    
    // Email Settings
    'from_email' => getenv('FROM_EMAIL') ?: 'support@e7gzly.click',  // From email (must be verified in SendGrid - use the email you verified)
    'from_name' => 'EحGZLY',                  // From name
    'reply_to_email' => 'support@egzly.com', // Reply-to email address
    'reply_to_name' => 'EحGZLY Support',     // Reply-to name
    
    // PHPMailer Settings (legacy - use email_provider instead)
    'use_phpmailer' => false,                // Deprecated: use 'email_provider' instead
    
    // QR Code URL Settings (for ticket verification)
    // Leave empty to auto-detect, or set manually (e.g., 'http://192.168.1.100/event-booking-website' or 'https://yourdomain.com')
    // This is the base URL used in QR codes - make sure it's accessible from mobile devices on your network
    // 
    // To find your local IP address (Windows):
    //   1. Open Command Prompt
    //   2. Type: ipconfig
    //   3. Look for "IPv4 Address" under your network adapter (usually starts with 192.168.x.x or 10.x.x.x)
    //   4. Set base_url to: 'http://YOUR_IP_ADDRESS/event-booking-website'
    // 
    // To find your local IP address (Mac/Linux):
    //   1. Open Terminal
    //   2. Type: ifconfig (Mac) or ip addr (Linux)
    //   3. Look for your network adapter's IP address (usually starts with 192.168.x.x)
    //   4. Set base_url to: 'http://YOUR_IP_ADDRESS/event-booking-website'
    //
    // IMPORTANT: Make sure your XAMPP/Apache allows connections from your network (not just localhost)
    'base_url' => '',                        // Auto-detect (recommended), or set manually like 'http://192.168.1.100/event-booking-website'
];


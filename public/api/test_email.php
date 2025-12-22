<?php
/**
 * Test Email Functionality
 * This file can be accessed via browser to test if email sending works
 */

error_reporting(E_ALL);
ini_set('display_errors', 1);

// Get base directory
$currentDir = __DIR__;
$baseDir = dirname(dirname($currentDir));

// Include required files
require_once $baseDir . DIRECTORY_SEPARATOR . 'app' . DIRECTORY_SEPARATOR . 'services' . DIRECTORY_SEPARATOR . 'EmailService.php';

// Test email configuration
$testEmail = $_GET['email'] ?? 'test@example.com';

?>
<!DOCTYPE html>
<html>
<head>
    <title>Email Test</title>
    <style>
        body { font-family: Arial, sans-serif; padding: 20px; }
        .success { color: green; }
        .error { color: red; }
        .info { color: blue; }
        pre { background: #f4f4f4; padding: 10px; border-radius: 5px; }
    </style>
</head>
<body>
    <h1>Email Functionality Test</h1>
    
    <form method="GET">
        <label>Test Email Address: <input type="email" name="email" value="<?php echo htmlspecialchars($testEmail); ?>" required></label>
        <button type="submit">Test Email</button>
    </form>
    
    <hr>
    
    <?php
    echo "<h2>System Checks:</h2>";
    
    // Check if mail function exists
    if (function_exists('mail')) {
        echo "<p class='success'>✓ mail() function is available</p>";
    } else {
        echo "<p class='error'>✗ mail() function is NOT available</p>";
    }
    
    // Check if EmailService class exists
    if (class_exists('EmailService')) {
        echo "<p class='success'>✓ EmailService class is loaded</p>";
    } else {
        echo "<p class='error'>✗ EmailService class is NOT loaded</p>";
    }
    
    // Check PHP mail configuration
    echo "<h3>PHP Mail Configuration:</h3>";
    echo "<pre>";
    echo "SMTP: " . ini_get('SMTP') . "\n";
    echo "smtp_port: " . ini_get('smtp_port') . "\n";
    echo "sendmail_from: " . ini_get('sendmail_from') . "\n";
    echo "</pre>";
    
    // If email is provided, test sending
    if (isset($_GET['email']) && filter_var($testEmail, FILTER_VALIDATE_EMAIL)) {
        echo "<h2>Test Results:</h2>";
        
        try {
            $emailService = new EmailService();
            
            $testBookingData = [
                'booking_code' => 'TEST-' . time(),
                'customer_first_name' => 'Test',
                'customer_last_name' => 'User',
                'customer_email' => $testEmail,
                'ticket_count' => 2,
                'final_amount' => 100.00
            ];
            
            $testEventData = [
                'title' => 'Test Event',
                'date' => date('Y-m-d H:i:s', strtotime('+7 days')),
                'venue_name' => 'Test Venue',
                'venue_address' => '123 Test Street'
            ];
            
            echo "<p class='info'>Attempting to send test email to: " . htmlspecialchars($testEmail) . "</p>";
            
            $result = $emailService->sendBookingConfirmationEmail($testBookingData, $testEventData);
            
            if ($result) {
                echo "<p class='success'>✓ Email sent successfully! Check your inbox (and spam folder).</p>";
            } else {
                echo "<p class='error'>✗ Email sending failed. Check PHP error logs for details.</p>";
                echo "<p class='info'>Note: On XAMPP, mail() function requires configuration. See php.ini settings above.</p>";
            }
        } catch (Exception $e) {
            echo "<p class='error'>✗ Error: " . htmlspecialchars($e->getMessage()) . "</p>";
            echo "<pre>" . htmlspecialchars($e->getTraceAsString()) . "</pre>";
        }
    } else {
        echo "<p class='info'>Enter an email address above and click 'Test Email' to send a test email.</p>";
    }
    ?>
    
    <hr>
    <h3>XAMPP Mail Configuration:</h3>
    <p>If mail() is not working, you need to configure SMTP in php.ini:</p>
    <ol>
        <li>Open <code>xampp/php/php.ini</code></li>
        <li>Find the [mail function] section</li>
        <li>Set SMTP to your mail server (e.g., <code>smtp.gmail.com</code>)</li>
        <li>Set smtp_port (usually 587 or 465)</li>
        <li>Set sendmail_from to your email address</li>
        <li>Restart Apache</li>
    </ol>
    <p><strong>Alternative:</strong> Use PHPMailer with SMTP for better email support.</p>
</body>
</html>


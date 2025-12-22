<?php
/**
 * EmailService Class
 * Handles email sending functionality with QR code generation using PHPMailer
 */

// Load email configuration
$configPath = __DIR__ . '/../../config/email_config.php';
if (file_exists($configPath)) {
    $emailConfig = require $configPath;
} else {
    // Default configuration (will use native mail() if PHPMailer not available)
    $emailConfig = [
        'use_phpmailer' => false,
        'from_email' => 'noreply@egzly.com',
        'from_name' => 'EحGZLY',
        'reply_to_email' => 'support@egzly.com',
        'reply_to_name' => 'EحGZLY Support'
    ];
}

// Try to load PHPMailer if configured
$phpmailerLoaded = false;
if ($emailConfig['use_phpmailer'] ?? false) {
    // Try to use Composer autoloader first (recommended)
    $autoloadPath = __DIR__ . '/../../vendor/autoload.php';
    if (file_exists($autoloadPath)) {
        require_once $autoloadPath;
        // Check if PHPMailer class is available
        if (class_exists('PHPMailer\PHPMailer\PHPMailer')) {
            $phpmailerLoaded = true;
            error_log("PHPMailer loaded via Composer autoloader");
        }
    }
    
    // Fallback: Try manual loading if Composer autoloader didn't work
    if (!$phpmailerLoaded) {
        $phpmailerPaths = [
            __DIR__ . '/../../vendor/phpmailer/phpmailer/src/PHPMailer.php',
            __DIR__ . '/../../PHPMailer/PHPMailer.php',
            __DIR__ . '/../../../PHPMailer/PHPMailer.php'
        ];
        
        foreach ($phpmailerPaths as $path) {
            if (file_exists($path)) {
                require_once $path;
                $smtpPath = str_replace('PHPMailer.php', 'SMTP.php', $path);
                $exceptionPath = str_replace('PHPMailer.php', 'Exception.php', $path);
                if (file_exists($smtpPath)) require_once $smtpPath;
                if (file_exists($exceptionPath)) require_once $exceptionPath;
                $phpmailerLoaded = true;
                error_log("PHPMailer loaded from: " . $path);
                break;
            }
        }
    }
    
    if (!$phpmailerLoaded) {
        error_log("WARNING: PHPMailer not found. Falling back to native mail() function.");
        error_log("Please install PHPMailer or set use_phpmailer to false in config/email_config.php");
    }
}

class EmailService {
    private $config;
    private $usePHPMailer;
    
    public function __construct() {
        // Load email configuration
        $configPath = __DIR__ . '/../../config/email_config.php';
        if (file_exists($configPath)) {
            $this->config = require $configPath;
        } else {
            $this->config = [
                'use_phpmailer' => false,
                'from_email' => 'noreply@egzly.com',
                'from_name' => 'EحGZLY',
                'reply_to_email' => 'support@egzly.com',
                'reply_to_name' => 'EحGZLY Support'
            ];
        }
        
        // Check if PHPMailer is available
        $this->usePHPMailer = ($this->config['use_phpmailer'] ?? false) && class_exists('PHPMailer\PHPMailer\PHPMailer');
        
        if ($this->usePHPMailer) {
            error_log("EmailService: Using PHPMailer for email sending");
        } else {
            error_log("EmailService: Using native mail() function");
        }
    }
    
    /**
     * Send booking confirmation email with QR code
     * 
     * @param array $bookingData Booking information
     * @param array $eventData Event information
     * @return bool Success status
     */
    public function sendBookingConfirmationEmail($bookingData, $eventData) {
        try {
            error_log("EmailService: Starting email send process");
            
            $to = $bookingData['customer_email'] ?? '';
            if (empty($to)) {
                error_log("ERROR: No email address provided in booking data");
                return false;
            }
            
            $firstName = $bookingData['customer_first_name'] ?? '';
            $lastName = $bookingData['customer_last_name'] ?? '';
            $fullName = trim($firstName . ' ' . $lastName);
            $bookingCode = $bookingData['booking_code'] ?? '';
            
            error_log("EmailService: Sending email to: " . $to . " for booking: " . $bookingCode);
            
            // Prepare email subject
            $subject = "Booking Confirmation - " . ($eventData['title'] ?? 'Event Ticket');
            
            // Generate QR code as a URL that points to the ticket verification page
            // This ensures QR scanners open it as a webpage showing ticket details
            // Construct absolute URL for QR code that works on mobile devices
            
            // Check if base_url is configured in config
            $configuredBaseUrl = $this->config['base_url'] ?? '';
            
            if (!empty($configuredBaseUrl)) {
                // Use configured base URL
                $baseUrl = rtrim($configuredBaseUrl, '/');
                $ticketUrl = $baseUrl . "/app/views/ticket_verification.php?code=" . urlencode($bookingCode);
                error_log("Using configured base URL for QR code: " . $baseUrl);
            } else {
                // Auto-detect URL
                $protocol = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on') ? "https" : "http";
                
                // Get the host - use HTTP_HOST if available, otherwise try to get IP address
                $host = $_SERVER['HTTP_HOST'] ?? 'localhost';
                
                // If HTTP_HOST contains localhost, try to get the actual server IP address
                // This is important so mobile devices can access the page over the network
                if (strpos($host, 'localhost') !== false || strpos($host, '127.0.0.1') !== false) {
                    // Try to get the server's local IP address
                    $localIP = $this->getServerLocalIP();
                    if ($localIP) {
                        // Get port from HTTP_HOST if it exists (e.g., localhost:8080)
                        $port = '';
                        if (isset($_SERVER['HTTP_HOST']) && strpos($_SERVER['HTTP_HOST'], ':') !== false) {
                            $portParts = explode(':', $_SERVER['HTTP_HOST']);
                            if (isset($portParts[1])) {
                                $port = ':' . $portParts[1];
                            }
                        }
                        $host = $localIP . $port;
                        error_log("Using auto-detected server IP address for QR code: " . $host);
                    } else {
                        error_log("WARNING: Could not determine server IP. QR code will use localhost which may not work on mobile devices.");
                        error_log("TIP: Set 'base_url' in config/email_config.php to manually specify the URL (e.g., 'http://192.168.1.100/event-booking-website')");
                    }
                }
                
                // Build the ticket verification URL
                // Path is fixed based on project structure
                $ticketUrl = $protocol . "://" . $host . "/event-booking-website/app/views/ticket_verification.php?code=" . urlencode($bookingCode);
            }
            
            // Use the URL as QR code data - when scanned, it will open the ticket verification page
            $qrData = $ticketUrl;
            
            error_log("QR Code URL generated: " . $ticketUrl);
            
            // Generate QR code image
            $qrCodeImage = $this->generateQRCode($qrData);
            
            // Build email body with QR code
            if ($this->usePHPMailer && $qrCodeImage) {
                // When using PHPMailer, we'll embed as attachment and reference with CID
                $qrCodeHtml = '<img src="cid:qrcode" alt="Booking QR Code" style="max-width: 300px; height: auto; margin: 20px auto; display: block;" />';
                $emailBody = $this->buildEmailTemplate($fullName, $bookingData, $eventData, $qrCodeHtml);
            } elseif ($qrCodeImage) {
                // For native mail(), use base64 embedding
                $qrCodeBase64 = base64_encode($qrCodeImage);
                $qrCodeHtml = '<img src="data:image/png;base64,' . $qrCodeBase64 . '" alt="Booking QR Code" style="max-width: 300px; height: auto; margin: 20px auto; display: block;" />';
                $emailBody = $this->buildEmailTemplate($fullName, $bookingData, $eventData, $qrCodeHtml);
            } else {
                error_log("Failed to generate QR code for booking: " . $bookingCode);
                // Continue without QR code
                $emailBody = $this->buildEmailTemplate($fullName, $bookingData, $eventData, '');
            }
            
            // Log email attempt details
            error_log("EmailService: Attempting to send email to: " . $to);
            error_log("EmailService: Subject: " . $subject);
            error_log("EmailService: Email body length: " . strlen($emailBody) . " bytes");
            error_log("EmailService: Using PHPMailer: " . ($this->usePHPMailer ? 'Yes' : 'No'));
            error_log("EmailService: QR code generated: " . ($qrCodeImage ? 'Yes' : 'No'));
            
            // Send email using PHPMailer or native mail()
            if ($this->usePHPMailer) {
                $success = $this->sendWithPHPMailer($to, $subject, $emailBody, $fullName, $qrCodeImage);
            } else {
                $success = $this->sendWithNativeMail($to, $subject, $emailBody);
            }
            
            if (!$success) {
                error_log("ERROR: Failed to send email for booking: " . $bookingCode);
                error_log("ERROR: Recipient: " . $to);
                return false;
            }
            
            error_log("SUCCESS: Booking confirmation email sent successfully to: " . $to . " for booking: " . $bookingCode);
            return true;
            
        } catch (Exception $e) {
            error_log("Error sending booking confirmation email: " . $e->getMessage());
            error_log("Exception trace: " . $e->getTraceAsString());
            return false;
        }
    }
    
    /**
     * Send email using PHPMailer
     * 
     * @param string $to Recipient email
     * @param string $subject Email subject
     * @param string $body HTML email body
     * @param string $recipientName Recipient name
     * @param string|false $qrCodeImage QR code image data (optional)
     * @return bool Success status
     */
    private function sendWithPHPMailer($to, $subject, $body, $recipientName = '', $qrCodeImage = false) {
        try {
            // Use fully qualified class name
            $mailClassName = 'PHPMailer\PHPMailer\PHPMailer';
            if (!class_exists($mailClassName)) {
                error_log("ERROR: PHPMailer class not found");
                return false;
            }
            
            $mail = new $mailClassName(true);
            
            // Server settings
            $mail->isSMTP();
            $mail->Host = $this->config['smtp_host'] ?? 'smtp.gmail.com';
            $mail->SMTPAuth = $this->config['smtp_auth'] ?? true;
            $mail->Username = $this->config['smtp_username'] ?? '';
            $mail->Password = $this->config['smtp_password'] ?? '';
            $mail->SMTPSecure = $this->config['smtp_secure'] ?? 'tls';
            $mail->Port = $this->config['smtp_port'] ?? 587;
            
            // Enable verbose debug output (set to 0 for production)
            $mail->SMTPDebug = 0;
            $mail->Debugoutput = function($str, $level) {
                error_log("PHPMailer Debug: " . $str);
            };
            
            // Recipients
            $mail->setFrom(
                $this->config['from_email'] ?? 'noreply@egzly.com',
                $this->config['from_name'] ?? 'EحGZLY'
            );
            $mail->addAddress($to, $recipientName);
            $mail->addReplyTo(
                $this->config['reply_to_email'] ?? 'support@egzly.com',
                $this->config['reply_to_name'] ?? 'EحGZLY Support'
            );
            
            // Add QR code as embedded image if provided
            if ($qrCodeImage) {
                // Save QR code to temporary file and embed it
                // This is the most reliable method for embedding images in emails
                $tempFile = sys_get_temp_dir() . DIRECTORY_SEPARATOR . 'qrcode_' . uniqid() . '.png';
                
                try {
                    if (file_put_contents($tempFile, $qrCodeImage) !== false) {
                        // Embed the image with CID 'qrcode' which matches the HTML reference
                        $mail->addEmbeddedImage($tempFile, 'qrcode', 'qrcode.png');
                        error_log("QR code embedded successfully as attachment");
                        
                        // Clean up temp file after sending
                        register_shutdown_function(function() use ($tempFile) {
                            if (file_exists($tempFile)) {
                                @unlink($tempFile);
                            }
                        });
                    } else {
                        error_log("Warning: Could not write QR code to temp file");
                    }
                } catch (\Exception $e) {
                    error_log("Warning: Could not embed QR code image: " . $e->getMessage());
                    // Clean up temp file on error
                    if (file_exists($tempFile)) {
                        @unlink($tempFile);
                    }
                }
            }
            
            // Content
            $mail->isHTML(true);
            $mail->Subject = $subject;
            $mail->Body = $body;
            $mail->AltBody = strip_tags($body); // Plain text version
            
            // Character encoding
            $mail->CharSet = 'UTF-8';
            
            $mail->send();
            return true;
            
        } catch (\Exception $e) {
            $errorInfo = isset($mail) && method_exists($mail, 'ErrorInfo') ? $mail->ErrorInfo : $e->getMessage();
            error_log("PHPMailer Error: " . $errorInfo);
            error_log("Exception: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Send email using native PHP mail() function
     * 
     * @param string $to Recipient email
     * @param string $subject Email subject
     * @param string $body HTML email body
     * @return bool Success status
     */
    private function sendWithNativeMail($to, $subject, $body) {
        // Check if mail function is available
        if (!function_exists('mail')) {
            error_log("ERROR: mail() function is not available in PHP");
            return false;
        }
        
        // Email headers
        $headers = $this->getEmailHeaders();
        
        // Send email
        $success = @mail($to, $subject, $body, $headers);
        
        // Get last error if any
        $lastError = error_get_last();
        if ($lastError && $lastError['type'] === E_WARNING && strpos($lastError['message'], 'mail') !== false) {
            error_log("ERROR: PHP mail() warning: " . $lastError['message']);
        }
        
        return $success;
    }
    
    /**
     * Get email headers for HTML email (native mail() function)
     * 
     * @return string Email headers
     */
    private function getEmailHeaders() {
        $fromEmail = $this->config['from_email'] ?? 'noreply@egzly.com';
        $fromName = $this->config['from_name'] ?? 'EحGZLY';
        $replyTo = $this->config['reply_to_email'] ?? 'support@egzly.com';
        
        $headers = "MIME-Version: 1.0\r\n";
        $headers .= "Content-Type: text/html; charset=UTF-8\r\n";
        $headers .= "From: " . $fromName . " <" . $fromEmail . ">\r\n";
        $headers .= "Reply-To: " . $replyTo . "\r\n";
        $headers .= "X-Mailer: PHP/" . phpversion() . "\r\n";
        
        return $headers;
    }
    
    /**
     * Generate QR code image from data
     * 
     * @param string $data Data to encode in QR code
     * @return string|false PNG image data or false on failure
     */
    private function generateQRCode($data) {
        try {
            // Use a QR code API service (simple and doesn't require additional libraries)
            // Using api.qrserver.com as Google Charts API is deprecated
            $size = 300;
            
            // URL-encode the data
            $encodedData = urlencode($data);
            
            // Use QR Server API (free and reliable)
            $url = "https://api.qrserver.com/v1/create-qr-code/?size={$size}x{$size}&data={$encodedData}&format=png";
            
            // Set context options for better reliability
            $context = stream_context_create([
                'http' => [
                    'timeout' => 10,
                    'method' => 'GET',
                    'header' => 'User-Agent: EحGZLY Booking System'
                ]
            ]);
            
            // Fetch the QR code image
            $imageData = @file_get_contents($url, false, $context);
            
            if ($imageData === false || empty($imageData)) {
                error_log("Warning: Failed to generate QR code from api.qrserver.com");
                return false;
            }
            
            // Verify it's actually an image (PNG magic number)
            if (substr($imageData, 0, 8) !== "\x89PNG\r\n\x1a\n") {
                error_log("Warning: QR code API returned invalid image data");
                return false;
            }
            
            return $imageData;
            
        } catch (\Exception $e) {
            error_log("Error generating QR code: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Build HTML email template
     * 
     * @param string $fullName Customer full name
     * @param array $bookingData Booking information
     * @param array $eventData Event information
     * @param string $qrCodeHtml QR code HTML (base64 embedded image)
     * @return string HTML email body
     */
    private function buildEmailTemplate($fullName, $bookingData, $eventData, $qrCodeHtml) {
        $eventTitle = htmlspecialchars($eventData['title'] ?? 'Event');
        $eventDate = isset($eventData['date']) ? date('F j, Y \a\t g:i A', strtotime($eventData['date'])) : 'TBA';
        $venueName = htmlspecialchars($eventData['venue_name'] ?? 'TBA');
        $venueAddress = htmlspecialchars($eventData['venue_address'] ?? '');
        $bookingCode = htmlspecialchars($bookingData['booking_code'] ?? '');
        $ticketCount = $bookingData['ticket_count'] ?? 0;
        $finalAmount = isset($bookingData['final_amount']) ? number_format($bookingData['final_amount'], 2) : '0.00';
        
        $html = '
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Booking Confirmation</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            line-height: 1.6;
            color: #333;
            max-width: 600px;
            margin: 0 auto;
            padding: 20px;
            background-color: #f4f4f4;
        }
        .email-container {
            background-color: #ffffff;
            border-radius: 10px;
            padding: 30px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }
        .header {
            text-align: center;
            border-bottom: 3px solid #f97316;
            padding-bottom: 20px;
            margin-bottom: 30px;
        }
        .header h1 {
            color: #f97316;
            margin: 0;
            font-size: 28px;
        }
        .greeting {
            font-size: 18px;
            margin-bottom: 20px;
            color: #333;
        }
        .booking-info {
            background-color: #f9f9f9;
            padding: 20px;
            border-radius: 5px;
            margin: 20px 0;
        }
        .info-row {
            display: flex;
            justify-content: space-between;
            padding: 10px 0;
            border-bottom: 1px solid #e0e0e0;
        }
        .info-row:last-child {
            border-bottom: none;
        }
        .info-label {
            font-weight: bold;
            color: #666;
        }
        .info-value {
            color: #333;
        }
        .qr-section {
            text-align: center;
            margin: 30px 0;
            padding: 20px;
            background-color: #f9f9f9;
            border-radius: 5px;
        }
        .qr-section h3 {
            color: #f97316;
            margin-bottom: 15px;
        }
        .footer {
            margin-top: 30px;
            padding-top: 20px;
            border-top: 1px solid #e0e0e0;
            text-align: center;
            color: #666;
            font-size: 14px;
        }
        .booking-code {
            font-size: 24px;
            font-weight: bold;
            color: #f97316;
            letter-spacing: 2px;
        }
    </style>
</head>
<body>
    <div class="email-container">
        <div class="header">
            <h1>🎟️ EحGZLY</h1>
            <p>Booking Confirmation</p>
        </div>
        
        <div class="greeting">
            Hello ' . htmlspecialchars($fullName) . ',
        </div>
        
        <p>Thank you for your booking! We are excited to have you join us. Your booking has been confirmed and your payment has been processed successfully.</p>
        
        <div class="booking-info">
            <div class="info-row">
                <span class="info-label">Booking Code:</span>
                <span class="info-value booking-code">' . $bookingCode . '</span>
            </div>
            <div class="info-row">
                <span class="info-label">Event:</span>
                <span class="info-value">' . $eventTitle . '</span>
            </div>
            <div class="info-row">
                <span class="info-label">Date & Time:</span>
                <span class="info-value">' . $eventDate . '</span>
            </div>
            <div class="info-row">
                <span class="info-label">Venue:</span>
                <span class="info-value">' . $venueName . ($venueAddress ? ', ' . $venueAddress : '') . '</span>
            </div>
            <div class="info-row">
                <span class="info-label">Number of Tickets:</span>
                <span class="info-value">' . $ticketCount . '</span>
            </div>
            <div class="info-row">
                <span class="info-label">Total Amount:</span>
                <span class="info-value">$' . $finalAmount . '</span>
            </div>
        </div>
        
        ' . ($qrCodeHtml ? '
        <div class="qr-section">
            <h3>Your Booking QR Code</h3>
            <p>Please present this QR code at the venue for entry:</p>
            ' . $qrCodeHtml . '
        </div>
        ' : '') . '
        
        <p><strong>Important Notes:</strong></p>
        <ul>
            <li>Please arrive at least 30 minutes before the event starts</li>
            <li>Bring a valid ID for verification</li>
            <li>Keep this email and your booking code for reference</li>
        </ul>
        
        <div class="footer">
            <p>If you have any questions or need to make changes to your booking, please contact our support team.</p>
            <p>Thank you for choosing EحGZLY!</p>
        </div>
    </div>
</body>
</html>';
        
        return $html;
    }
    
    /**
     * Send contact form confirmation email
     * 
     * @param string $recipientName User's name
     * @param string $recipientEmail User's email
     * @param string $subject The subject of their message (for reference)
     * @return bool Success status
     */
    public function sendContactConfirmationEmail($recipientName, $recipientEmail, $subject = '') {
        try {
            error_log("EmailService: Sending contact confirmation email to: " . $recipientEmail);
            
            if (empty($recipientEmail) || !filter_var($recipientEmail, FILTER_VALIDATE_EMAIL)) {
                error_log("ERROR: Invalid email address provided for contact confirmation");
                return false;
            }
            
            // Prepare email subject
            $emailSubject = "Thank You for Contacting EحGZLY";
            
            // Build email body
            $emailBody = $this->buildContactConfirmationTemplate($recipientName, $subject);
            
            // Log email attempt details
            error_log("EmailService: Contact confirmation - To: " . $recipientEmail);
            error_log("EmailService: Contact confirmation - Subject: " . $emailSubject);
            error_log("EmailService: Contact confirmation - Using PHPMailer: " . ($this->usePHPMailer ? 'Yes' : 'No'));
            
            // Send email using PHPMailer or native mail()
            if ($this->usePHPMailer) {
                $success = $this->sendWithPHPMailer($recipientEmail, $emailSubject, $emailBody, $recipientName);
            } else {
                $success = $this->sendWithNativeMail($recipientEmail, $emailSubject, $emailBody);
            }
            
            if (!$success) {
                error_log("ERROR: Failed to send contact confirmation email to: " . $recipientEmail);
                return false;
            }
            
            error_log("SUCCESS: Contact confirmation email sent successfully to: " . $recipientEmail);
            return true;
            
        } catch (Exception $e) {
            error_log("Error sending contact confirmation email: " . $e->getMessage());
            error_log("Exception trace: " . $e->getTraceAsString());
            return false;
        }
    }
    
    /**
     * Build HTML email template for contact form confirmation
     * 
     * @param string $recipientName User's name
     * @param string $subject The subject of their message (optional)
     * @return string HTML email body
     */
    private function buildContactConfirmationTemplate($recipientName, $subject = '') {
        $name = htmlspecialchars($recipientName);
        $subjectText = !empty($subject) ? htmlspecialchars($subject) : 'your message';
        
        $html = '
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Thank You for Contacting Us</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            line-height: 1.6;
            color: #333;
            max-width: 600px;
            margin: 0 auto;
            padding: 20px;
            background-color: #f4f4f4;
        }
        .email-container {
            background-color: #ffffff;
            border-radius: 10px;
            padding: 30px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }
        .header {
            text-align: center;
            border-bottom: 3px solid #f97316;
            padding-bottom: 20px;
            margin-bottom: 30px;
        }
        .header h1 {
            color: #f97316;
            margin: 0;
            font-size: 28px;
        }
        .greeting {
            font-size: 18px;
            margin-bottom: 20px;
            color: #333;
        }
        .message-content {
            background-color: #f9f9f9;
            padding: 20px;
            border-radius: 5px;
            margin: 20px 0;
            line-height: 1.8;
        }
        .subject-reference {
            background-color: #fff3e0;
            padding: 15px;
            border-left: 4px solid #f97316;
            margin: 20px 0;
            border-radius: 3px;
        }
        .footer {
            margin-top: 30px;
            padding-top: 20px;
            border-top: 1px solid #e0e0e0;
            text-align: center;
            color: #666;
            font-size: 14px;
        }
        .footer p {
            margin: 5px 0;
        }
        .highlight {
            color: #f97316;
            font-weight: bold;
        }
    </style>
</head>
<body>
    <div class="email-container">
        <div class="header">
            <h1>🎟️ EحGZLY</h1>
            <p>Thank You for Contacting Us</p>
        </div>
        
        <div class="greeting">
            Hello ' . $name . ',
        </div>
        
        <div class="message-content">
            <p>Thank you for reaching out to us! We have successfully received your message regarding <span class="highlight">' . $subjectText . '</span>.</p>
            
            <p>Our team has been notified and someone will get back to you shortly. We typically respond within 24-48 hours during business days.</p>
            
            ' . (!empty($subject) ? '
            <div class="subject-reference">
                <strong>Your Message Subject:</strong><br>
                ' . $subjectText . '
            </div>
            ' : '') . '
            
            <p>In the meantime, feel free to explore our upcoming events and book your tickets!</p>
        </div>
        
        <div class="footer">
            <p><strong>Best regards,</strong></p>
            <p>The EحGZLY Team</p>
            <p style="margin-top: 20px; font-size: 12px; color: #999;">
                This is an automated confirmation email. Please do not reply to this message.
            </p>
        </div>
    </div>
</body>
</html>';
        
        return $html;
    }
    
    /**
     * Get the server's local IP address for network access
     * This is needed so mobile devices can access the QR code URL
     * 
     * @return string|false IP address or false if not found
     */
    private function getServerLocalIP() {
        // Method 1: Try SERVER_ADDR if available
        if (isset($_SERVER['SERVER_ADDR']) && $_SERVER['SERVER_ADDR'] !== '127.0.0.1' && $_SERVER['SERVER_ADDR'] !== '::1') {
            return $_SERVER['SERVER_ADDR'];
        }
        
        // Method 2: Try to get IP from network interfaces (Windows/Linux/Mac)
        if (strtoupper(substr(PHP_OS, 0, 3)) === 'WIN') {
            // Windows - use ipconfig
            $command = 'ipconfig | findstr /i "IPv4"';
            $output = @shell_exec($command);
            if ($output) {
                // Extract all IPs from output (format: IPv4 Address. . . . . . . . . . . : 192.168.1.100)
                $lines = explode("\n", $output);
                $preferredIP = null;
                $fallbackIP = null;
                
                foreach ($lines as $line) {
                    if (preg_match('/(\d{1,3}\.\d{1,3}\.\d{1,3}\.\d{1,3})/', $line, $matches)) {
                        $ip = trim($matches[1]);
                        // Skip loopback and invalid addresses
                        if ($ip !== '127.0.0.1' && $ip !== '0.0.0.0' && strpos($ip, '169.254.') !== 0) {
                            // Found a valid IP address (not loopback, not zero, not APIPA)
                            $preferredIP = $ip;
                            break; // Use first valid IP found
                        }
                    }
                }
                
                // Return IP if found
                if ($preferredIP) {
                    return $preferredIP;
                }
            }
        } else {
            // Linux/Mac - use hostname or ifconfig/ip
            // Try hostname -I first (Linux)
            $output = @shell_exec('hostname -I 2>/dev/null');
            if ($output) {
                $ips = explode(' ', trim($output));
                foreach ($ips as $ip) {
                    $ip = trim($ip);
                    if (filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE) === false && 
                        $ip !== '127.0.0.1' && strpos($ip, '169.254.') !== 0) {
                        return $ip;
                    }
                }
            }
            
            // Fallback: Try ifconfig (Mac/Linux)
            $output = @shell_exec('ifconfig 2>/dev/null | grep "inet " | grep -v "127.0.0.1"');
            if ($output && preg_match('/inet (\d{1,3}\.\d{1,3}\.\d{1,3}\.\d{1,3})/', $output, $matches)) {
                $ip = $matches[1];
                if (strpos($ip, '169.254.') !== 0) {
                    return $ip;
                }
            }
        }
        
        // Method 3: Try to get IP from socket connection
        // This method connects to a public server to determine local IP
        try {
            $socket = @socket_create(AF_INET, SOCK_DGRAM, SOL_UDP);
            if ($socket) {
                // Connect to Google DNS (doesn't actually send data)
                @socket_connect($socket, '8.8.8.8', 80);
                $localIP = @socket_getsockname($socket, $ip);
                @socket_close($socket);
                if ($localIP && $ip && $ip !== '127.0.0.1') {
                    return $ip;
                }
            }
        } catch (Exception $e) {
            // Ignore errors
        }
        
        // If all methods fail, return false
        return false;
    }
}

<?php
// terms.php
// This PHP file serves the Terms and Conditions document using the shared header/footer structure.

// Define page variables
$page_title = "Terms and Conditions";
$last_updated = "October 19, 2025";
$company_name = "E7gzly";
$support_email = "support@e7gzly.com";

// NEW: Define Company Address and Google Maps link
// The specific address text displayed to the user. I've added a placeholder name for clarity.
$company_address_text = "E7gzly Company (View Map)"; 
// UPDATED: Set the Google Maps link to the user-provided URL.
$google_maps_link = "https://maps.app.goo.gl/JFKuWYJDqJB42nLN7";
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $page_title; ?></title>
    <!-- Load Inter font from Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@100..900&display=swap" rel="stylesheet">
    <!-- Link to external CSS -->
    <link rel="stylesheet" href="../../public/css/terms.css">
</head>
<body class="page-body">
    <!-- Header MUST be included here, AFTER the <body> tag opens -->
    <?php include '../../includes/header.php'; ?>
    
    <!-- Main Content Area -->
    <main class="main-content">
        <div class="container">
            <!-- Main Title -->
            <h1><?php echo $page_title; ?></h1>
            <p class="last-updated">
                Last updated: <?php echo $last_updated; ?>. Please read these terms carefully before using our ticketing service.
            </p>

            <!-- Section 1: General Agreement -->
            <h2 class="section-heading">1. General Agreement</h2>
            <p>
                Welcome to <?php echo $company_name; ?>. By accessing or using our services, you agree to be bound by these Terms and Conditions ("Terms"). If you disagree with any part of the terms, then you may not access the service.
            </p>
            <p>
                <?php echo $company_name; ?> reserves the right to update, change, or replace any part of these Terms by posting updates and/or changes to our website. It is your responsibility to check this page periodically for changes.
            </p>

            <!-- Section 2: Ticket Purchase & Pricing -->
            <h2 class="section-heading">2. Ticket Purchase & Pricing</h2>
            <h3 class="subsection-heading">2.1. Price and Availability</h3>
            <p>
                All ticket prices are listed in the local currency (e.g., EGP) unless otherwise noted. Ticket purchases are subject to availability and the policies set forth by the event organizer. We reserve the right to change prices at any time before a purchase is completed.
            </p>

            <h3 class="subsection-heading">2.2. Booking Fees</h3>
            <p>
                A non-refundable booking fee is applied to each transaction to cover administrative and operational costs. This fee will be clearly displayed during the checkout process.
            </p>

            <!-- Section 3: Refunds and Cancellations -->
            <h2 class="section-heading">3. Refunds and Cancellations</h2>
            <p>
                All Ticket sales are <strong>"final"</strong> and non-refundable, except in the following limited circumstances:
            </p>
            <ul class="bullet-list">
                <li>If an event is <strong>"canceled"</strong> and not rescheduled.</li>
                <li>If an event is <strong>"postponed"</strong> and you are unable to attend the new date (subject to the event organizer's policy).</li>
                <li>In case of a technical failure on the <?php echo $company_name; ?> platform that resulted in an incorrect or duplicate charge.</li>
            </ul>
            <p>
                We are an agent for the event organizers; therefore, refund policies are dictated by the promoter/venue. Please refer to the specific event page for details on their policy.
            </p>

            <!-- Section 4: Account Registration and Security -->
            <h2 class="section-heading">4. Account Registration and Security</h2>
            <p>
                To purchase tickets, you must register for an account. You agree to provide accurate, current, and complete information during the registration process and to update such information to keep it accurate, current, and complete.
            </p>
            <ul class="bullet-list">
                <li>You are responsible for safeguarding the password that you use to access the service.</li>
                <li>You agree not to disclose your password to any third party.</li>
            </ul>

            <!-- Section 5: Limitation of Liability -->
            <h2 class="section-heading">5. Limitation of Liability</h2>
            <p>
                To the maximum extent permitted by applicable law, <?php echo $company_name; ?> shall not be liable for any indirect, incidental, special, consequential or punitive damages, including without limitation, loss of profits, data, use, goodwill, or other intangible losses, resulting from:
            </p>
            <ol class="numbered-list">
                <li>Your access to or use of or inability to access or use the Service.</li>
                <li>Any conduct or content of any third party on the Service.</li>
                <li>Any content obtained from the Service.</li>
            </ol>

            <!-- Contact Information -->
            <h2 class="section-heading">Contact Information</h2>
            <p>
                If you have any questions about these Terms, please contact us at:
            </p>
            <div class="contact-info">
                <p><strong>Email:</strong> <a href="mailto:<?php echo $support_email; ?>"><?php echo $support_email; ?></a></p>
                <!-- UPDATED: Using the new, specific Google Maps link -->
                <p><strong>Address:</strong> <a href="<?php echo $google_maps_link; ?>" target="_blank" rel="noopener noreferrer"><?php echo $company_address_text; ?></a></p>
            </div>

        </div>
    </main>

    <!-- Link to external JavaScript - renamed to terms.js and path adjusted for structure -->
    <script type="module" src="../../public/js/terms.js"></script>
    <?php
    // Include the footer file
    include '../../includes/footer.php';
    ?>
</body>
</html>

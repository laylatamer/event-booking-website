<?php
// Start session
require_once __DIR__ . '/../../database/session_init.php';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Contact Us | Eحgzly </title>
    <link rel="stylesheet" href="../../public/css/contact_us.css">

    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://unpkg.com/feather-icons"></script>
    <script src="https://cdn.jsdelivr.net/npm/vanta@latest/dist/vanta.net.min.js"></script>
   
</head>
<body class="min-h-screen text-white overflow-x-hidden">
 <?php
// Include the header file
include 'partials/header.php';
?>
<div id="vanta-bg" class="fixed inset-0 -z-10"></div>

<!-- Floating elements for background motion -->
<div class="fixed top-1/4 left-10 w-16 h-16 rounded-full bg-orange-600 opacity-30 floating"></div>
<div class="fixed top-1/3 right-20 w-24 h-24 rounded-full bg-gray-900 opacity-20 floating"></div>
<div class="fixed bottom-1/4 left-1/4 w-20 h-20 rounded-full bg-orange-500 opacity-25 floating"></div>
<div class="fixed top-3/4 right-1/3 w-12 h-12 rounded-full bg-orange-400 opacity-20 floating"></div>
<div class="fixed bottom-1/3 left-2/3 w-28 h-28 rounded-full bg-gray-800 opacity-15 floating"></div>

<div class="relative min-h-screen flex flex-col">
    <!-- Main -->
    <main class="flex-grow flex items-center justify-center px-4 py-12">
        <div class="container mx-auto max-w-4xl">
            <div class="bg-black bg-opacity-50 backdrop-blur-md rounded-2xl overflow-hidden shadow-2xl">
                <div class="grid grid-cols-1 md:grid-cols-2">
                    <!-- Left Side -->
                    <div class="hidden md:block relative gradient-bg p-12">
                        <div class="absolute inset-0 flex flex-col items-center justify-center p-8 text-center">
                            <i data-feather="mail" class="w-16 h-16 mx-auto mb-6 text-orange-300"></i>
                            <h2 class="text-4xl font-bold mb-4">Get In Touch</h2>
                            <p class="text-orange-200 mb-8">We're here to help with any questions about tickets, events, or partnerships.</p>
                            <div class="space-y-4 text-left">
                                <div class="flex items-start">
                                    <i data-feather="phone" class="mr-4 mt-1 text-orange-300"></i>
                                    <div>
                                        <h3 class="font-semibold">Call Us</h3>
                                        <p>+1 (555) 123-4567</p>
                                    </div>
                                </div>
                                <div class="flex items-start">
                                    <i data-feather="clock" class="mr-4 mt-1 text-orange-300"></i>
                                    <div>
                                        <h3 class="font-semibold">Hours</h3>
                                        <p>Mon–Fri: 9AM – 8PM</p>
                                        <p>Sat–Sun: 10AM – 6PM</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Right Side (Form) -->
                    <div class="p-8 md:p-12">
                        <h1 class="text-3xl font-bold mb-2 text-orange-400">Contact Us</h1>
                        <p class="mb-8 text-gray-300">Fill out the form below and we'll get back to you within 24 hours</p>
                        <?php if (isset($_SESSION['contact_status'])): ?>
                            <?php $status = $_SESSION['contact_status']; unset($_SESSION['contact_status']); ?>
                            <div class="mb-6 px-4 py-3 rounded-lg <?php echo $status['type'] === 'success' ? 'bg-green-600/30 border border-green-500 text-green-200' : 'bg-red-600/30 border border-red-500 text-red-200'; ?>">
                                <?php echo htmlspecialchars($status['message']); ?>
                            </div>
                        <?php endif; ?>

                        <form action="../../public/contact.php" method="POST" class="space-y-6">
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                <div>
                                    <label class="block mb-2 text-sm font-medium">Your Name</label>
                                    <input type="text" name="name" required class="w-full px-4 py-3 bg-black bg-opacity-30 border border-gray-700 rounded-lg input-glow focus:outline-none focus:border-orange-500 transition">
                                </div>
                                <div>
                                    <label class="block mb-2 text-sm font-medium">Email Address</label>
                                    <input type="email" name="email" required class="w-full px-4 py-3 bg-black bg-opacity-30 border border-gray-700 rounded-lg input-glow focus:outline-none focus:border-orange-500 transition">
                                </div>
                            </div>
                            <div>
                                <label class="block mb-2 text-sm font-medium">Subject</label>
                                <input type="text" name="subject" required class="w-full px-4 py-3 bg-black bg-opacity-30 border border-gray-700 rounded-lg input-glow focus:outline-none focus:border-orange-500 transition">
                            </div>
                            <div>
                                <label class="block mb-2 text-sm font-medium">Your Message</label>
                                <textarea name="message" rows="4" required class="w-full px-4 py-3 bg-black bg-opacity-30 border border-gray-700 rounded-lg input-glow focus:outline-none focus:border-orange-500 transition"></textarea>
                            </div>
                            <button type="submit" class="w-full gradient-bg text-white font-bold py-3 px-4 rounded-lg transition duration-300 transform hover:scale-105">
                                Send Message <i data-feather="send" class="inline ml-2 w-4 h-4"></i>
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </main>
<?php
// Include the footer file
include 'partials/footer.php';
?>
    <script type="module" src="../../public/js/contact_us.js"></script>

</body>
</html>

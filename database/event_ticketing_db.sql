-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Dec 22, 2025 at 05:25 PM
-- Server version: 10.4.32-MariaDB
-- PHP Version: 8.2.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `event_ticketing_db`
--

-- --------------------------------------------------------

--
-- Table structure for table `booked_seats`
--

CREATE TABLE `booked_seats` (
  `id` int(11) NOT NULL,
  `booking_id` int(11) NOT NULL,
  `event_id` int(11) NOT NULL,
  `seat_id` varchar(100) NOT NULL,
  `category_name` varchar(50) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `booked_seats`
--

INSERT INTO `booked_seats` (`id`, `booking_id`, `event_id`, `seat_id`, `category_name`, `created_at`) VALUES
(1, 10, 12, 'West-2-2', 'Cat1', '2025-12-18 21:46:54'),
(2, 10, 12, 'West-2-3', 'Cat1', '2025-12-18 21:46:54'),
(4, 12, 11, 'G-1', 'Gold', '2025-12-18 22:29:22'),
(5, 12, 11, 'H-4', 'Gold', '2025-12-18 22:29:22'),
(6, 12, 11, 'H-5', 'Gold', '2025-12-18 22:29:22'),
(7, 12, 11, 'A-4', 'Regular', '2025-12-18 22:29:22'),
(8, 13, 15, 'J-1', 'Regular', '2025-12-18 23:31:38'),
(9, 13, 15, 'J-2', 'Regular', '2025-12-18 23:31:38'),
(10, 13, 15, 'J-3', 'Regular', '2025-12-18 23:31:38'),
(11, 14, 12, 'North-4-3', 'Cat2', '2025-12-19 13:49:22'),
(12, 14, 12, 'North-4-4', 'Cat2', '2025-12-19 13:49:22'),
(13, 15, 12, 'North-3-18', 'Cat2', '2025-12-20 18:49:12'),
(14, 15, 12, 'North-2-18', 'Cat1', '2025-12-20 18:49:12'),
(15, 16, 15, 'G-1', 'Regular', '2025-12-22 13:35:30'),
(16, 16, 15, 'G-2', 'Regular', '2025-12-22 13:35:30'),
(17, 16, 15, 'G-3', 'Regular', '2025-12-22 13:35:30');

-- --------------------------------------------------------

--
-- Table structure for table `bookings`
--

CREATE TABLE `bookings` (
  `id` int(11) NOT NULL,
  `booking_code` varchar(50) NOT NULL,
  `user_id` int(11) NOT NULL,
  `event_id` int(11) NOT NULL,
  `ticket_count` int(11) NOT NULL DEFAULT 1,
  `subtotal` decimal(10,2) DEFAULT 0.00,
  `service_fee` decimal(10,2) DEFAULT 0.00,
  `processing_fee` decimal(10,2) DEFAULT 0.00,
  `customization_fee` decimal(10,2) DEFAULT 0.00,
  `total_amount` decimal(10,2) NOT NULL,
  `discount` decimal(10,2) DEFAULT 0.00,
  `tax` decimal(10,2) DEFAULT 0.00,
  `final_amount` decimal(10,2) NOT NULL,
  `currency` varchar(3) DEFAULT 'USD',
  `payment_method` varchar(50) NOT NULL,
  `payment_status` enum('pending','paid','failed','refunded') DEFAULT 'pending',
  `transaction_id` varchar(100) DEFAULT NULL,
  `status` enum('pending','confirmed','completed','cancelled') DEFAULT 'pending',
  `refunded` tinyint(1) DEFAULT 0,
  `refund_amount` decimal(10,2) DEFAULT 0.00,
  `refund_reason` text DEFAULT NULL,
  `notes` text DEFAULT NULL,
  `customer_first_name` varchar(100) DEFAULT NULL,
  `customer_last_name` varchar(100) DEFAULT NULL,
  `customer_email` varchar(255) DEFAULT NULL,
  `customer_phone` varchar(20) DEFAULT NULL,
  `ticket_details` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `bookings`
--

INSERT INTO `bookings` (`id`, `booking_code`, `user_id`, `event_id`, `ticket_count`, `subtotal`, `service_fee`, `processing_fee`, `customization_fee`, `total_amount`, `discount`, `tax`, `final_amount`, `currency`, `payment_method`, `payment_status`, `transaction_id`, `status`, `refunded`, `refund_amount`, `refund_reason`, `notes`, `customer_first_name`, `customer_last_name`, `customer_email`, `customer_phone`, `ticket_details`, `created_at`, `updated_at`) VALUES
(7, 'EGZ2A523618', 16, 9, 4, 3200.00, 17.97, 96.00, 0.00, 0.00, 0.00, 0.00, 3313.97, 'USD', 'cash', 'pending', NULL, 'cancelled', 0, 0.00, NULL, NULL, 'Zyad', 'Ashour', 'zyad2300553@miuegypt.edu.eg', '01285493766', '[]', '2025-12-18 21:31:17', '2025-12-18 22:26:25'),
(8, 'EGZ34F0BF55', 16, 12, 4, 2950.00, 17.97, 88.50, 0.00, 0.00, 0.00, 0.00, 3056.47, 'USD', 'cash', 'pending', NULL, 'cancelled', 0, 0.00, NULL, NULL, 'reem', 'adel', 'reemadel@gmail.com', '01285493700', '[]', '2025-12-18 21:34:07', '2025-12-18 22:26:18'),
(9, 'EGZ57E36599', 16, 12, 4, 1600.00, 5.99, 48.00, 0.00, 0.00, 0.00, 0.00, 1653.99, 'USD', 'cash', 'paid', NULL, 'cancelled', 0, 0.00, NULL, NULL, 'Zyad', 'Ashour', 'zyad2300553@miuegypt.edu.eg', '01285493766', '[]', '2025-12-18 21:43:26', '2025-12-18 22:27:32'),
(10, 'EGZ64DD41BA', 16, 12, 2, 1800.00, 5.99, 54.00, 0.00, 0.00, 0.00, 0.00, 1859.99, 'USD', 'cash', 'paid', NULL, 'confirmed', 0, 0.00, NULL, NULL, 'Zyad', 'Ashour', 'zyad2300553@miuegypt.edu.eg', '01285493766', '[]', '2025-12-18 21:46:54', '2025-12-18 22:18:09'),
(11, 'EGZ894176C4', 16, 12, 1, 750.00, 5.99, 22.50, 9.99, 0.00, 0.00, 0.00, 788.48, 'USD', 'card', 'paid', NULL, 'cancelled', 0, 0.00, NULL, NULL, 'Zyad', 'Ashour', 'zyad2300553@miuegypt.edu.eg', '01285493766', '{\"customized\":true,\"customized_count\":1,\"guest_names\":{\"1\":\"ZYAD ASHOUR\"},\"tickets_by_category\":{\"Cat2\":1}}', '2025-12-18 21:56:36', '2025-12-18 22:27:06'),
(12, 'EGZ0412F932', 16, 11, 4, 1400.00, 11.98, 42.00, 39.96, 0.00, 0.00, 0.00, 1493.94, 'USD', 'cash', 'paid', NULL, 'confirmed', 0, 0.00, NULL, NULL, 'reem', 'adel', 'reemadel@gmail.com', '01285493700', '{\"customized\":true,\"customized_count\":4,\"guest_names\":{\"1\":\"ZYAD ASHOUR\",\"2\":\"MAHMOUD SHALABY\",\"3\":\"MARLY\",\"4\":\"LAYLA\"},\"tickets_by_category\":{\"Gold\":3,\"Regular\":1},\"ticket_categories\":[{\"category_name\":\"Gold\",\"quantity\":3,\"price\":300},{\"category_name\":\"Regular\",\"quantity\":1,\"price\":500}],\"booked_seats\":[{\"seat_id\":\"G-1\",\"category_name\":\"Gold\"},{\"seat_id\":\"H-4\",\"category_name\":\"Gold\"},{\"seat_id\":\"H-5\",\"category_name\":\"Gold\"},{\"seat_id\":\"A-4\",\"category_name\":\"Regular\"}]}', '2025-12-18 22:29:22', '2025-12-18 22:30:08'),
(13, 'EGZED9F0546', 20, 15, 3, 510.00, 5.99, 15.30, 0.00, 0.00, 0.00, 0.00, 531.29, 'USD', 'cash', 'pending', NULL, 'confirmed', 0, 0.00, NULL, NULL, 'fady', 'adel', 'fady00221@miuegypt.edu.eg', '01285493700', '{\"ticket_categories\":[{\"category_name\":\"Regular\",\"quantity\":3,\"price\":170}],\"booked_seats\":[{\"seat_id\":\"J-1\",\"category_name\":\"Regular\"},{\"seat_id\":\"J-2\",\"category_name\":\"Regular\"},{\"seat_id\":\"J-3\",\"category_name\":\"Regular\"}]}', '2025-12-18 23:31:38', '2025-12-18 23:31:38'),
(14, 'EGZ7E25B7AF', 16, 12, 2, 1500.00, 5.99, 45.00, 9.99, 0.00, 0.00, 0.00, 1560.98, 'USD', 'cash', 'pending', NULL, 'confirmed', 0, 0.00, NULL, NULL, 'reem', 'adel', 'reemadel@gmail.com', '01285493700', '{\"customized\":true,\"customized_count\":1,\"guest_names\":{\"1\":\"ZYAD ASHOUR\"},\"tickets_by_category\":{\"Cat2\":1},\"ticket_categories\":[{\"category_name\":\"Cat2\",\"quantity\":2,\"price\":750}],\"booked_seats\":[{\"seat_id\":\"North-4-3\",\"category_name\":\"Cat2\"},{\"seat_id\":\"North-4-4\",\"category_name\":\"Cat2\"}]}', '2025-12-19 13:49:22', '2025-12-19 13:49:22'),
(15, 'EGZFA8974C9', 16, 12, 2, 1650.00, 11.98, 49.50, 19.98, 0.00, 0.00, 0.00, 1731.46, 'USD', 'cash', 'paid', NULL, 'confirmed', 0, 0.00, NULL, NULL, 'Zyad', 'Ashour', 'zyad2300553@miuegypt.edu.eg', '01285493766', '{\"customized\":true,\"customized_count\":2,\"guest_names\":{\"1\":\"ZYAD ASHOUR\",\"2\":\"KEVIN\"},\"tickets_by_category\":{\"Cat1\":1,\"Cat2\":1},\"ticket_categories\":[{\"category_name\":\"Cat2\",\"quantity\":1,\"price\":750},{\"category_name\":\"Cat1\",\"quantity\":1,\"price\":900}],\"booked_seats\":[{\"seat_id\":\"North-3-18\",\"category_name\":\"Cat2\"},{\"seat_id\":\"North-2-18\",\"category_name\":\"Cat1\"}]}', '2025-12-20 18:49:12', '2025-12-20 18:49:34'),
(16, 'EGZ922BC53A', 16, 15, 3, 510.00, 5.99, 15.30, 0.00, 0.00, 0.00, 0.00, 531.29, 'USD', 'cash', 'pending', NULL, 'confirmed', 0, 0.00, NULL, NULL, 'reem', 'adel', 'reemadel@gmail.com', '01285493700', '{\"ticket_categories\":[{\"category_name\":\"Regular\",\"quantity\":3,\"price\":170}],\"booked_seats\":[{\"seat_id\":\"G-1\",\"category_name\":\"Regular\"},{\"seat_id\":\"G-2\",\"category_name\":\"Regular\"},{\"seat_id\":\"G-3\",\"category_name\":\"Regular\"}]}', '2025-12-22 13:35:30', '2025-12-22 13:35:30');

-- --------------------------------------------------------

--
-- Table structure for table `chatbot_conversations`
--

CREATE TABLE `chatbot_conversations` (
  `conversation_id` int(11) NOT NULL,
  `user_id` int(11) UNSIGNED DEFAULT NULL,
  `session_id` varchar(100) NOT NULL,
  `status` enum('active','closed','archived') DEFAULT 'active',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `chatbot_knowledge`
--

CREATE TABLE `chatbot_knowledge` (
  `kb_id` int(11) NOT NULL,
  `question` varchar(255) NOT NULL,
  `answer` text NOT NULL,
  `category` varchar(50) DEFAULT 'general',
  `keywords` text DEFAULT NULL,
  `is_active` tinyint(1) DEFAULT 1,
  `priority` int(11) DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `chatbot_knowledge`
--

INSERT INTO `chatbot_knowledge` (`kb_id`, `question`, `answer`, `category`, `keywords`, `is_active`, `priority`, `created_at`, `updated_at`) VALUES
(1, 'How do I book tickets?', 'To book tickets: 1) Browse events from the homepage 2) Select an event 3) Choose your ticket category and quantity 4) Proceed to checkout 5) Complete payment. You\'ll receive a confirmation email with your tickets.', 'booking', 'book,ticket,purchase,buy,reserve,order,get tickets', 1, 10, '2025-12-18 11:36:07', '2025-12-18 11:36:07'),
(2, 'Can I get a refund?', 'Refunds are available up to 48 hours before the event start time. Please contact our support team via the Contact page or email support@event-booking.com with your booking code.', 'refund', 'refund,cancel,return,money back,cancellation,get back', 1, 9, '2025-12-18 11:36:07', '2025-12-18 11:36:07'),
(3, 'What payment methods do you accept?', 'We accept credit/debit cards (Visa, MasterCard), PayPal, and mobile wallets. All payments are secure and encrypted.', 'payment', 'pay,payment,credit card,debit card,paypal,cash,mobile pay', 1, 8, '2025-12-18 11:36:07', '2025-12-18 11:36:07'),
(4, 'How do I download my tickets?', 'After booking, tickets are available in your account dashboard under \"My Tickets\". You can download them as PDF or they will be sent to your email. You can also show the QR code on your phone at the venue.', 'tickets', 'download,ticket,email,pdf,qr code,print,get ticket', 1, 10, '2025-12-18 11:36:07', '2025-12-18 11:36:07'),
(5, 'Can I transfer my ticket to someone else?', 'Yes, ticket transfers are allowed. Please contact our support team at least 24 hours before the event with the new attendee details and your booking code.', 'transfer', 'transfer,change name,give ticket,sell ticket,share', 1, 7, '2025-12-18 11:36:07', '2025-12-18 11:36:07'),
(6, 'What if I lost my ticket?', 'Login to your account and re-download your ticket from \"My Tickets\". If you can\'t access your account, contact support with your email and booking details.', 'support', 'lost,misplace,forget ticket,can\'t find ticket', 1, 6, '2025-12-18 11:36:07', '2025-12-18 11:36:07'),
(7, 'Are there any booking fees?', 'A small service fee applies to all bookings to cover processing costs. The total amount including all fees is clearly displayed before payment.', 'fees', 'fee,charge,cost,service fee,booking fee,extra charge', 1, 5, '2025-12-18 11:36:07', '2025-12-18 11:36:07'),
(8, 'What are the venue rules?', 'Venue rules vary by event. Common rules include: No outside food/drinks, no smoking, age restrictions may apply. Check the event details page for specific venue information.', 'venue', 'venue,rules,policy,entry,requirements,dress code', 1, 6, '2025-12-18 11:36:07', '2025-12-18 11:36:07'),
(9, 'Do you offer group discounts?', 'Yes! For groups of 10+ people, please contact our group sales team at groups@event-booking.com for special rates and arrangements.', 'discount', 'group,discount,bulk,corporate,company,special rate', 1, 5, '2025-12-18 11:36:07', '2025-12-18 11:36:07'),
(10, 'How do I contact support?', 'You can email us at support@event-booking.com, use the Contact form on our website, or call +1-800-EVENT-NOW during business hours (9 AM - 6 PM local time).', 'contact', 'support,help,contact,phone,email,customer service', 1, 10, '2025-12-18 11:36:07', '2025-12-18 11:36:07'),
(11, 'What time should I arrive?', 'We recommend arriving at least 30-60 minutes before the event start time for smooth entry and seating.', 'event', 'arrive,time,when to come,entry time,doors open', 1, 4, '2025-12-18 11:36:07', '2025-12-18 11:36:07'),
(12, 'Can I bring children?', 'Age restrictions vary by event. Please check the event details page for age requirements. Children under 12 may require adult supervision.', 'event', 'children,kids,age,baby,toddler,family,underage', 1, 4, '2025-12-18 11:36:07', '2025-12-18 11:36:07'),
(13, 'Is parking available?', 'Parking availability depends on the venue. Most venues offer parking, but spaces may be limited. Check the venue details on the event page.', 'venue', 'parking,car,vehicle,transport,drive,where to park', 1, 3, '2025-12-18 11:36:07', '2025-12-18 11:36:07'),
(14, 'What if the event is cancelled?', 'If an event is cancelled, you will receive a full refund automatically within 5-7 business days. You\'ll be notified via email.', 'cancellation', 'cancelled,postponed,rain check,weather,refund if cancelled', 1, 7, '2025-12-18 11:36:07', '2025-12-18 11:36:07'),
(15, 'How do I update my account information?', 'Login to your account and go to \"Profile Settings\" to update your personal information, email, or password.', 'account', 'update,change,profile,information,email,password', 1, 5, '2025-12-18 11:36:07', '2025-12-18 11:36:07'),
(16, 'Are tickets refundable if I can\'t attend?', 'Tickets are refundable up to 48 hours before the event. After that, refunds are not available except for special circumstances.', 'refund', 'can\'t attend,changed plans,sick,emergency,last minute', 1, 8, '2025-12-18 11:36:07', '2025-12-18 11:36:07'),
(17, 'What is your privacy policy?', 'We value your privacy. Your personal information is only used for ticket processing and event communications. Read our full Privacy Policy in the website footer.', 'policy', 'privacy,data,personal information,security,gdpr', 1, 3, '2025-12-18 11:36:07', '2025-12-18 11:36:07'),
(18, 'Do you offer gift cards?', 'Yes! Gift cards are available on our website. They can be used for any event and never expire.', 'gift', 'gift card,voucher,present,give as gift,gift certificate', 1, 4, '2025-12-18 11:36:07', '2025-12-18 11:36:07'),
(19, 'How do I subscribe to event alerts?', 'Subscribe to our newsletter on the homepage footer to receive updates about upcoming events and special offers.', 'newsletter', 'subscribe,alerts,notifications,updates,email list', 1, 3, '2025-12-18 11:36:07', '2025-12-18 11:36:07'),
(20, 'Are there student discounts?', 'Some events offer student discounts. Check the event details page or contact us for student pricing availability.', 'discount', 'student,discount,college,university,school,id', 1, 4, '2025-12-18 11:36:07', '2025-12-18 11:36:07');

-- --------------------------------------------------------

--
-- Table structure for table `chatbot_messages`
--

CREATE TABLE `chatbot_messages` (
  `message_id` int(11) NOT NULL,
  `conversation_id` int(11) NOT NULL,
  `message_type` enum('user','bot') NOT NULL,
  `message_text` text NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `chatbot_training`
--

CREATE TABLE `chatbot_training` (
  `id` int(11) NOT NULL,
  `user_input` varchar(500) NOT NULL,
  `intent` varchar(50) NOT NULL,
  `correct_response` text NOT NULL,
  `keywords` text DEFAULT NULL,
  `use_count` int(11) DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `chatbot_training`
--

INSERT INTO `chatbot_training` (`id`, `user_input`, `intent`, `correct_response`, `keywords`, `use_count`, `created_at`) VALUES
(1, 'I want a refund', 'refund', 'Refunds available up to 48 hours before event. Contact support with booking code.', 'refund,want refund,cancel', 0, '2025-12-18 12:46:21'),
(2, 'How do I get my money back?', 'refund', 'To get a refund: Email support@event-booking.com with your booking code at least 48 hours before event.', 'money back,refund,get back', 0, '2025-12-18 12:46:21'),
(3, 'Can I cancel my ticket?', 'refund', 'Yes, tickets can be cancelled for refund up to 48 hours before the event starts.', 'cancel,ticket,refund', 0, '2025-12-18 12:46:21'),
(4, 'How to book tickets?', 'booking', '1. Browse events 2. Select event 3. Choose tickets 4. Checkout 5. Pay', 'book,ticket,how to,booking', 0, '2025-12-18 12:46:21'),
(5, 'I want to buy tickets', 'booking', 'Great! Browse our events page, select an event, choose your tickets, and proceed to checkout.', 'buy,ticket,purchase', 0, '2025-12-18 12:46:21'),
(6, 'What events are coming?', 'events', 'Upcoming: Tul8te concert (Dec 25), Al Ahly matches, Marwan Pablo (Jan 10), Avengers movie (Jan 1).', 'events,upcoming,what\'s on', 0, '2025-12-18 12:46:21'),
(7, 'How much are tickets?', 'pricing', 'Prices vary: Football 400-900 USD, Concerts 500-2400 USD, Movies 170-300 USD. Check event pages.', 'price,cost,how much,ticket price', 0, '2025-12-18 12:46:21'),
(8, 'Tell me about Tul8te', 'specific_event', 'Tul8te concert on Dec 25, 2025 at Arena El Malahy. Tickets: 500-1000 USD.', 'tul8te,concert', 0, '2025-12-18 12:46:21'),
(9, 'I lost my ticket', 'ticket_delivery', 'Login to your account and re-download from \"My Tickets\" or contact support for help.', 'lost ticket,missing ticket', 0, '2025-12-18 12:46:21'),
(10, 'Need to contact support', 'support', 'Email: support@event-booking.com | Phone: +1-800-EVENT-NOW | Contact form on website', 'contact,support,help', 0, '2025-12-18 12:46:21'),
(11, 'I want a refund', 'refund', 'Refunds available up to 48 hours before event. Contact support with your booking code at support@event-booking.com', 'refund,want refund,cancel ticket', 0, '2025-12-18 13:06:16'),
(12, 'How do I get my money back?', 'refund', 'To request a refund: Email support@event-booking.com with your booking code at least 48 hours before event. Processing takes 5-7 business days.', 'money back,refund,get back,cancel', 0, '2025-12-18 13:06:16'),
(13, 'Can I cancel my ticket?', 'refund', 'Yes! Tickets can be cancelled for a full refund up to 48 hours before the event start time. Contact our support team.', 'cancel,ticket,refund,booking', 0, '2025-12-18 13:06:16'),
(14, 'How to book tickets?', 'booking', 'Booking process: 1) Browse events 2) Select event 3) Choose ticket category 4) Enter details 5) Complete payment 6) Receive e-ticket', 'book,ticket,how to,booking,purchase', 0, '2025-12-18 13:06:16'),
(15, 'What events are available?', 'events', 'I\'ll fetch the latest events for you from our database...', 'events,upcoming,what\'s on,available,shows', 0, '2025-12-18 13:06:16'),
(16, 'How much are tickets?', 'pricing', 'Ticket prices vary by event. Football: 400-900 USD, Concerts: 500-2400 USD, Movies: 170-300 USD. Check specific event pages for exact prices.', 'price,cost,how much,ticket price,expensive', 0, '2025-12-18 13:06:16'),
(17, 'Tell me about Tul8te', 'specific_event', 'Fetching Tul8te event details...', 'tul8te,concert,music', 0, '2025-12-18 13:06:16'),
(18, 'I lost my ticket', 'ticket_delivery', 'Login to your account → \"My Tickets\" section → Download ticket again. Or contact support for assistance.', 'lost ticket,missing ticket,can\'t find ticket', 0, '2025-12-18 13:06:16'),
(19, 'Need to contact support', 'support', 'Support options: Email: support@event-booking.com | Phone: +1-800-EVENT-NOW | Contact form on website | Hours: 9 AM - 6 PM', 'contact,support,help,email,phone', 0, '2025-12-18 13:06:16'),
(20, 'hello', 'greeting', 'Hello! 👋 Welcome to EVENT-BOOK. I\'m your ticket assistant. How can I help you today?', 'hello,hi,hey,greetings', 0, '2025-12-18 13:06:16'),
(21, 'thank you', 'greeting', 'You\'re welcome! 😊 Let me know if you need anything else with your event tickets.', 'thanks,thank you,appreciate', 0, '2025-12-18 13:06:16'),
(22, 'I want a refund', 'refund', 'Refunds available up to 48 hours before event. Contact support with your booking code at support@event-booking.com', 'refund,want refund,cancel ticket', 0, '2025-12-18 13:15:45'),
(23, 'How do I get my money back?', 'refund', 'To request a refund: Email support@event-booking.com with your booking code at least 48 hours before event. Processing takes 5-7 business days.', 'money back,refund,get back,cancel', 0, '2025-12-18 13:15:45'),
(24, 'Can I cancel my ticket?', 'refund', 'Yes! Tickets can be cancelled for a full refund up to 48 hours before the event start time. Contact our support team.', 'cancel,ticket,refund,booking', 0, '2025-12-18 13:15:45'),
(25, 'How to book tickets?', 'booking', 'Booking process: 1) Browse events 2) Select event 3) Choose ticket category 4) Enter details 5) Complete payment 6) Receive e-ticket', 'book,ticket,how to,booking,purchase', 0, '2025-12-18 13:15:45'),
(26, 'What events are available?', 'events', 'I\'ll fetch the latest events for you from our database...', 'events,upcoming,what\'s on,available,shows', 0, '2025-12-18 13:15:45'),
(27, 'How much are tickets?', 'pricing', 'Ticket prices vary by event. Football: 400-900 USD, Concerts: 500-2400 USD, Movies: 170-300 USD. Check specific event pages for exact prices.', 'price,cost,how much,ticket price,expensive', 0, '2025-12-18 13:15:45'),
(28, 'Tell me about Tul8te', 'specific_event', 'Fetching Tul8te event details...', 'tul8te,concert,music', 0, '2025-12-18 13:15:45'),
(29, 'I lost my ticket', 'ticket_delivery', 'Login to your account → \"My Tickets\" section → Download ticket again. Or contact support for assistance.', 'lost ticket,missing ticket,can\'t find ticket', 0, '2025-12-18 13:15:45'),
(30, 'Need to contact support', 'support', 'Support options: Email: support@event-booking.com | Phone: +1-800-EVENT-NOW | Contact form on website | Hours: 9 AM - 6 PM', 'contact,support,help,email,phone', 0, '2025-12-18 13:15:45'),
(31, 'hello', 'greeting', 'Hello! 👋 Welcome to EVENT-BOOK. I\'m your ticket assistant. How can I help you today?', 'hello,hi,hey,greetings', 0, '2025-12-18 13:15:45'),
(32, 'thank you', 'greeting', 'You\'re welcome! 😊 Let me know if you need anything else with your event tickets.', 'thanks,thank you,appreciate', 0, '2025-12-18 13:15:45');

-- --------------------------------------------------------

--
-- Table structure for table `contact_messages`
--

CREATE TABLE `contact_messages` (
  `id` int(10) UNSIGNED NOT NULL,
  `name` varchar(120) NOT NULL,
  `email` varchar(190) NOT NULL,
  `subject` varchar(190) NOT NULL,
  `message` text NOT NULL,
  `status` enum('new','in_progress','closed') NOT NULL DEFAULT 'new',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `contact_messages`
--

INSERT INTO `contact_messages` (`id`, `name`, `email`, `subject`, `message`, `status`, `created_at`, `updated_at`) VALUES
(1, 'marly hossam', 'marly2301599@miuegypt.edu.eg', 'i need an refund asap', 'et et etetetet', 'new', '2025-11-25 15:21:13', '2025-11-25 15:21:13'),
(2, 'marly_hossam', 'marly2301599@miuegypt.edu.eg', 'i need an refund asap', 'etc etc etc', 'new', '2025-11-28 14:35:40', '2025-11-28 14:35:40'),
(3, 'sas', 'sdds@gmail.com', 'free trial', 'd', 'new', '2025-12-17 06:30:41', '2025-12-17 06:30:41'),
(4, 'sas', 'sdds@gmail.com', 'free trial', 'aal', 'new', '2025-12-17 16:42:40', '2025-12-17 16:42:40');

-- --------------------------------------------------------

--
-- Table structure for table `events`
--

CREATE TABLE `events` (
  `id` int(11) NOT NULL,
  `title` varchar(200) NOT NULL,
  `description` text NOT NULL,
  `subcategory_id` int(11) NOT NULL,
  `venue_id` int(11) NOT NULL,
  `date` datetime NOT NULL,
  `end_date` datetime DEFAULT NULL,
  `price` decimal(10,2) NOT NULL,
  `discounted_price` decimal(10,2) DEFAULT NULL,
  `image_url` varchar(500) DEFAULT NULL,
  `gallery_images` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`gallery_images`)),
  `total_tickets` int(11) NOT NULL,
  `available_tickets` int(11) NOT NULL,
  `min_tickets_per_booking` int(11) DEFAULT 1,
  `max_tickets_per_booking` int(11) DEFAULT 10,
  `terms_conditions` text DEFAULT NULL,
  `additional_info` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`additional_info`)),
  `status` enum('active','inactive','draft') DEFAULT 'draft',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `events`
--

INSERT INTO `events` (`id`, `title`, `description`, `subcategory_id`, `venue_id`, `date`, `end_date`, `price`, `discounted_price`, `image_url`, `gallery_images`, `total_tickets`, `available_tickets`, `min_tickets_per_booking`, `max_tickets_per_booking`, `terms_conditions`, `additional_info`, `status`, `created_at`, `updated_at`) VALUES
(9, 'Tul8te', 'Enjoy a night full of amazing surprises at Arena el Malahy', 14, 8, '2025-12-25 14:00:00', '2025-12-25 18:00:00', 800.00, NULL, 'http://localhost/event-booking-website/public/uploads/events/6940513370434_1765822771.png', '[]', 24000, 24000, 1, 10, 'No alcohol', '[]', 'active', '2025-12-15 18:19:31', '2025-12-17 06:25:22'),
(10, 'Al ahly vs Isamily', 'watch the epl match at Cairo stadium', 1, 2, '2025-12-18 18:30:00', '2025-12-18 20:30:00', 400.00, NULL, 'http://localhost/event-booking-website/public/uploads/events/694053f478f55_1765823476.jpg', '[]', 60000, 60000, 1, 10, 'no alcohol', '[]', 'active', '2025-12-15 18:31:16', '2025-12-17 05:15:37'),
(11, 'Stand up comedy with Ahmed Helmy', 'TEST', 4, 3, '2025-12-20 12:00:00', '2025-12-20 16:00:00', 600.00, NULL, 'http://localhost/event-booking-website/public/uploads/events/694057251c1f8_1765824293.jpg', '[]', 30, 30, 1, 10, 'no alcohol', '[]', 'active', '2025-12-15 18:44:53', '2025-12-17 04:55:02'),
(12, 'Ahly vs Zamalek', 'Test', 1, 6, '2025-12-27 13:20:00', '2025-12-27 16:20:00', 400.00, NULL, 'http://localhost/event-booking-website/public/uploads/events/69406e1e67668_1765830174.jpg', '[]', 242, 242, 1, 10, 'no alcohol', '[]', 'active', '2025-12-15 20:22:54', '2025-12-17 04:52:11'),
(15, 'Avengers End game', 'The Marvel avengers end game movie like never before', 3, 13, '2026-01-01 20:30:00', '2026-01-01 23:00:00', 170.00, NULL, 'http://localhost/event-booking-website/public/uploads/events/6942418802921_1765949832.jpg', '[]', 100, 100, 1, 4, 'No alcohol \r\nNo food and drinks \r\n+18 only', '[]', 'active', '2025-12-17 05:37:12', '2025-12-17 05:37:12'),
(16, 'Marwan pablo', 'A full night of rap with mega star Marwan Pablo', 14, 14, '2026-01-10 19:00:00', '2026-01-10 22:00:00', 1000.00, NULL, 'http://localhost/event-booking-website/public/uploads/events/69424643a2cf7_1765951043.jpg', '[]', 1000, 1000, 1, 4, 'No alcohol', '[]', 'active', '2025-12-17 05:57:23', '2025-12-17 06:25:05'),
(17, 'Al Ahly vs Itihad', 'The final match for the basketball egyptian league', 2, 15, '2026-01-02 20:00:00', '2026-01-02 22:30:00', 400.00, NULL, 'http://localhost/event-booking-website/public/uploads/events/6942dfacc79d4_1765990316.png', '[]', 242, 242, 1, 5, 'No alcohol', '[]', 'active', '2025-12-17 16:51:56', '2025-12-17 16:51:56');

-- --------------------------------------------------------

--
-- Table structure for table `event_ticket_categories`
--

CREATE TABLE `event_ticket_categories` (
  `id` int(11) NOT NULL,
  `event_id` int(11) NOT NULL,
  `category_name` varchar(50) NOT NULL,
  `total_tickets` int(11) NOT NULL DEFAULT 0,
  `available_tickets` int(11) NOT NULL DEFAULT 0,
  `price` decimal(10,2) NOT NULL DEFAULT 0.00,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `event_ticket_categories`
--

INSERT INTO `event_ticket_categories` (`id`, `event_id`, `category_name`, `total_tickets`, `available_tickets`, `price`, `created_at`, `updated_at`) VALUES
(23, 12, 'Cat1', 50, 49, 900.00, '2025-12-17 04:52:11', '2025-12-20 18:49:12'),
(24, 12, 'Cat2', 50, 47, 750.00, '2025-12-17 04:52:11', '2025-12-20 18:49:12'),
(25, 12, 'Cat3', 142, 142, 400.00, '2025-12-17 04:52:11', '2025-12-18 22:27:32'),
(26, 11, 'Gold', 40, 37, 300.00, '2025-12-17 04:55:02', '2025-12-18 22:29:22'),
(27, 11, 'Premium', 40, 40, 400.00, '2025-12-17 04:55:02', '2025-12-17 04:55:02'),
(28, 11, 'Regular', 40, 39, 500.00, '2025-12-17 04:55:02', '2025-12-18 22:29:22'),
(35, 10, 'Cat1', 50, 50, 500.00, '2025-12-17 05:15:37', '2025-12-17 05:15:37'),
(36, 10, 'Cat2', 50, 50, 300.00, '2025-12-17 05:15:37', '2025-12-17 05:15:37'),
(37, 10, 'Cat3', 142, 142, 150.00, '2025-12-17 05:15:37', '2025-12-17 05:15:37'),
(41, 15, 'Gold', 20, 20, 300.00, '2025-12-17 05:37:12', '2025-12-17 05:37:12'),
(42, 15, 'Premium', 20, 20, 220.00, '2025-12-17 05:37:12', '2025-12-17 05:37:12'),
(43, 15, 'Regular', 60, 54, 170.00, '2025-12-17 05:37:12', '2025-12-22 13:35:30'),
(47, 16, 'Regular', 500, 500, 1000.00, '2025-12-17 06:25:05', '2025-12-17 06:25:05'),
(48, 16, 'Fanpit', 350, 350, 1500.00, '2025-12-17 06:25:05', '2025-12-17 06:25:05'),
(49, 16, 'Golden Circle', 150, 150, 2400.00, '2025-12-17 06:25:05', '2025-12-17 06:25:05'),
(50, 9, 'Regular', 500, 500, 500.00, '2025-12-17 06:25:22', '2025-12-18 22:26:25'),
(51, 9, 'Fanpit', 250, 250, 700.00, '2025-12-17 06:25:22', '2025-12-18 22:26:25'),
(52, 9, 'Golden Circle', 250, 250, 1000.00, '2025-12-17 06:25:22', '2025-12-18 22:26:25'),
(53, 17, 'Cat1', 50, 50, 800.00, '2025-12-17 16:51:56', '2025-12-17 16:51:56'),
(54, 17, 'Cat2', 50, 50, 650.00, '2025-12-17 16:51:56', '2025-12-17 16:51:56'),
(55, 17, 'Cat3', 142, 142, 400.00, '2025-12-17 16:51:56', '2025-12-17 16:51:56');

-- --------------------------------------------------------

--
-- Table structure for table `main_categories`
--

CREATE TABLE `main_categories` (
  `id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `status` enum('active','inactive') DEFAULT 'active',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `main_categories`
--

INSERT INTO `main_categories` (`id`, `name`, `status`, `created_at`, `updated_at`) VALUES
(1, 'Sports', 'active', '2025-12-08 20:42:52', '2025-12-08 20:42:52'),
(2, 'Entertainment', 'active', '2025-12-08 20:42:52', '2025-12-08 20:42:52');

-- --------------------------------------------------------

--
-- Table structure for table `subcategories`
--

CREATE TABLE `subcategories` (
  `id` int(11) NOT NULL,
  `main_category_id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `image_url` varchar(500) DEFAULT NULL,
  `status` enum('active','inactive') DEFAULT 'active',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `subcategories`
--

INSERT INTO `subcategories` (`id`, `main_category_id`, `name`, `image_url`, `status`, `created_at`, `updated_at`) VALUES
(1, 1, 'Football', 'http://localhost/event-booking-website/public/uploads/subcategories/69424d5ec7c8d_1765952862.png', 'active', '2025-12-08 20:42:52', '2025-12-17 06:27:42'),
(2, 1, 'Basketball', 'http://localhost/event-booking-website/public/uploads/subcategories/69424d54466ad_1765952852.png', 'active', '2025-12-08 20:42:52', '2025-12-17 06:27:32'),
(3, 2, 'Movie', 'http://localhost/event-booking-website/public/uploads/subcategories/69424d3cc13f7_1765952828.png', 'active', '2025-12-08 20:42:52', '2025-12-17 06:27:08'),
(4, 2, 'Theater', 'http://localhost/event-booking-website/public/uploads/subcategories/69424d475337d_1765952839.jpg', 'active', '2025-12-08 20:42:52', '2025-12-17 06:27:19'),
(14, 2, 'Concert', 'http://localhost/event-booking-website/public/uploads/subcategories/69424c610935b_1765952609.jpg', 'active', '2025-12-17 06:23:29', '2025-12-17 06:23:29');

-- --------------------------------------------------------

--
-- Table structure for table `tickets`
--

CREATE TABLE `tickets` (
  `id` int(11) NOT NULL,
  `event_id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `type` varchar(50) NOT NULL DEFAULT 'Regular',
  `price` decimal(10,2) NOT NULL,
  `quantity_total` int(11) NOT NULL,
  `quantity_available` int(11) NOT NULL,
  `quantity_sold` int(11) DEFAULT 0,
  `status` enum('active','inactive','sold_out') DEFAULT 'active',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `ticket_customizations`
--

CREATE TABLE `ticket_customizations` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `event_id` int(11) NOT NULL,
  `reservation_id` int(11) DEFAULT NULL,
  `customized_count` int(11) NOT NULL,
  `customization_cost` decimal(10,2) NOT NULL,
  `guest_names` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL CHECK (json_valid(`guest_names`)),
  `event_details` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL CHECK (json_valid(`event_details`)),
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `ticket_customizations`
--

INSERT INTO `ticket_customizations` (`id`, `user_id`, `event_id`, `reservation_id`, `customized_count`, `customization_cost`, `guest_names`, `event_details`, `created_at`) VALUES
(1, 16, 16, NULL, 2, 19.98, '{\"1\":\"zyad ashour\",\"2\":\"Mahmoud Shalaby\"}', '{\"title\":\"Marwan pablo\",\"date\":\"Saturday, January 10, 2026\",\"time\":\"7:00 PM\",\"venue\":\"U arena, North coast\"}', '2025-12-17 18:01:00'),
(2, 16, 9, NULL, 2, 19.98, '{\"1\":\"MARLY HOSSAM\",\"2\":\"MAHMOUD SHALABY\"}', '{\"title\":\"Tul8te\",\"date\":\"Thursday, December 25, 2025\",\"time\":\"2:00 PM\",\"venue\":\"Arena El malahy, New cairo\"}', '2025-12-17 18:18:22'),
(3, 16, 11, NULL, 3, 29.97, '{\"1\":\"MARLY HOSSAM\",\"2\":\"MAHMOUD SHALABY\",\"3\":\"ZYAD ASHOUR\"}', '{\"title\":\"Stand up comedy with Ahmed Helmy\",\"date\":\"Saturday, December 20, 2025\",\"time\":\"12:00 PM\",\"venue\":\"Cairo Opera House, cairo\"}', '2025-12-17 18:36:33'),
(4, 16, 16, NULL, 3, 29.97, '{\"1\":\"MARLY HOSSAM\",\"2\":\"MAHMOUD SHALABY\",\"3\":\"ZYAD ASHOUR\"}', '{\"title\":\"Marwan pablo\",\"date\":\"Saturday, January 10, 2026\",\"time\":\"7:00 PM\",\"venue\":\"U arena, North coast\"}', '2025-12-17 18:40:11'),
(5, 16, 15, NULL, 3, 29.97, '{\"1\":\"MARLY HOSSAM\",\"2\":\"MAHMOUD SHALABY\",\"3\":\"ZYAD ASHOUR\"}', '{\"title\":\"Avengers End game\",\"date\":\"Thursday, January 1, 2026\",\"time\":\"8:30 PM\",\"venue\":\"Scene Cinemas, New cairo\"}', '2025-12-17 19:08:01'),
(6, 16, 9, NULL, 2, 19.98, '{\"1\":\"ZYAD ASHOUR\",\"2\":\"AMMAR DAWOUD\"}', '{\"title\":\"Tul8te\",\"date\":\"Thursday, December 25, 2025\",\"time\":\"2:00 PM\",\"venue\":\"Arena El malahy, New cairo\"}', '2025-12-18 16:54:44'),
(7, 16, 12, NULL, 2, 19.98, '{\"1\":\"ZYAD ASHOUR\",\"2\":\"MAHMOUD SHALABY\"}', '{\"title\":\"Ahly vs Zamalek\",\"date\":\"Saturday, December 27, 2025\",\"time\":\"1:20 PM\",\"venue\":\"Misr Stadium (New Administrative Capital Stadium), cairo\"}', '2025-12-18 18:50:23'),
(8, 16, 12, NULL, 2, 19.98, '{\"1\":\"MARLY HOSSAM\",\"2\":\"AMMAR DAWOUD\"}', '{\"title\":\"Ahly vs Zamalek\",\"date\":\"Saturday, December 27, 2025\",\"time\":\"1:20 PM\",\"venue\":\"Misr Stadium (New Administrative Capital Stadium), cairo\"}', '2025-12-18 19:16:32'),
(9, 16, 12, NULL, 4, 39.96, '{\"1\":\"ZYAD ASHOUR\",\"2\":\"AMMAR DAWOUD\",\"3\":\"ZYAD ASHOUR\",\"4\":\"ZOX\"}', '{\"title\":\"Ahly vs Zamalek\",\"date\":\"Saturday, December 27, 2025\",\"time\":\"1:20 PM\",\"venue\":\"Misr Stadium (New Administrative Capital Stadium), cairo\"}', '2025-12-18 19:17:14'),
(10, 16, 12, NULL, 1, 9.99, '{\"1\":\"MARLY HOSSAM\"}', '{\"title\":\"Ahly vs Zamalek\",\"date\":\"Saturday, December 27, 2025\",\"time\":\"1:20 PM\",\"venue\":\"Misr Stadium (New Administrative Capital Stadium), cairo\"}', '2025-12-18 19:42:34'),
(11, 16, 12, NULL, 1, 9.99, '{\"1\":\"ZYAD ASHOUR\"}', '{\"title\":\"Ahly vs Zamalek\",\"date\":\"Saturday, December 27, 2025\",\"time\":\"1:20 PM\",\"venue\":\"Misr Stadium (New Administrative Capital Stadium), cairo\"}', '2025-12-18 19:42:57'),
(12, 16, 12, NULL, 3, 29.97, '{\"1\":\"ZYAD ASHOUR\",\"2\":\"MAHMOUD SHALABY\",\"3\":\"ZYAD ASHOUR\"}', '{\"title\":\"Ahly vs Zamalek\",\"date\":\"Saturday, December 27, 2025\",\"time\":\"1:20 PM\",\"venue\":\"Misr Stadium (New Administrative Capital Stadium), cairo\"}', '2025-12-18 19:43:28'),
(13, 16, 12, NULL, 2, 19.98, '{\"1\":\"ZYAD ASHOUR\",\"2\":\"AMMAR DAWOUD\"}', '{\"title\":\"Ahly vs Zamalek\",\"date\":\"Saturday, December 27, 2025\",\"time\":\"1:20 PM\",\"venue\":\"Misr Stadium (New Administrative Capital Stadium), cairo\"}', '2025-12-18 19:57:54'),
(14, 16, 12, NULL, 3, 29.97, '{\"1\":\"ZYAD ASHOUR\",\"2\":\"AMMAR DAWOUD\"}', '{\"title\":\"Ahly vs Zamalek\",\"date\":\"Saturday, December 27, 2025\",\"time\":\"1:20 PM\",\"venue\":\"Misr Stadium (New Administrative Capital Stadium), cairo\"}', '2025-12-18 20:22:38'),
(15, 16, 11, NULL, 4, 39.96, '{\"1\":\"MARLY HOSSAM\",\"2\":\"MAHMOUD SHALABY\"}', '{\"title\":\"Stand up comedy with Ahmed Helmy\",\"date\":\"Saturday, December 20, 2025\",\"time\":\"12:00 PM\",\"venue\":\"Cairo Opera House, cairo\"}', '2025-12-18 20:42:17'),
(16, 16, 12, NULL, 1, 9.99, '{\"1\":\"ZYAD ASHOUR\"}', '{\"title\":\"Ahly vs Zamalek\",\"date\":\"Saturday, December 27, 2025\",\"time\":\"1:20 PM\",\"venue\":\"Misr Stadium (New Administrative Capital Stadium), cairo\"}', '2025-12-18 21:55:34'),
(17, 16, 11, NULL, 4, 39.96, '{\"1\":\"ZYAD ASHOUR\",\"2\":\"MAHMOUD SHALABY\",\"3\":\"MARLY\",\"4\":\"LAYLA\"}', '{\"title\":\"Stand up comedy with Ahmed Helmy\",\"date\":\"Saturday, December 20, 2025\",\"time\":\"12:00 PM\",\"venue\":\"Cairo Opera House, cairo\"}', '2025-12-18 22:29:00'),
(18, 16, 12, NULL, 1, 9.99, '{\"1\":\"ZYAD ASHOUR\"}', '{\"title\":\"Ahly vs Zamalek\",\"date\":\"Saturday, December 27, 2025\",\"time\":\"1:20 PM\",\"venue\":\"Misr Stadium (New Administrative Capital Stadium), cairo\"}', '2025-12-19 13:49:08'),
(19, 16, 12, NULL, 2, 19.98, '{\"1\":\"ZYAD ASHOUR\",\"2\":\"KEVIN\"}', '{\"title\":\"Ahly vs Zamalek\",\"date\":\"Saturday, December 27, 2025\",\"time\":\"1:20 PM\",\"venue\":\"Misr Stadium (New Administrative Capital Stadium), cairo\"}', '2025-12-20 18:48:59');

-- --------------------------------------------------------

--
-- Table structure for table `ticket_reservations`
--

CREATE TABLE `ticket_reservations` (
  `id` int(11) NOT NULL,
  `event_id` int(11) NOT NULL,
  `category_name` varchar(50) NOT NULL,
  `quantity` int(11) NOT NULL DEFAULT 1,
  `user_id` int(11) DEFAULT NULL,
  `session_id` varchar(255) DEFAULT NULL,
  `reserved_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `expires_at` timestamp NULL DEFAULT NULL,
  `status` enum('reserved','confirmed','expired') DEFAULT 'reserved'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `ticket_reservations`
--

INSERT INTO `ticket_reservations` (`id`, `event_id`, `category_name`, `quantity`, `user_id`, `session_id`, `reserved_at`, `expires_at`, `status`) VALUES
(1, 10, 'Cat2', 1, 16, '5ct1qdv50ceqrg22up8dk7vaep', '2025-12-15 20:27:12', '2025-12-15 19:42:12', 'expired'),
(2, 10, 'Cat1', 1, 16, '5ct1qdv50ceqrg22up8dk7vaep', '2025-12-15 20:27:12', '2025-12-15 19:42:12', 'expired'),
(3, 10, 'Cat1', 1, 16, '5ct1qdv50ceqrg22up8dk7vaep', '2025-12-15 20:27:16', '2025-12-15 19:42:16', 'expired'),
(4, 10, 'Cat2', 1, 16, '5ct1qdv50ceqrg22up8dk7vaep', '2025-12-15 20:27:16', '2025-12-15 19:42:16', 'expired'),
(5, 10, 'Cat1', 2, 16, '5ct1qdv50ceqrg22up8dk7vaep', '2025-12-15 20:28:57', '2025-12-15 19:43:57', 'expired'),
(6, 10, 'Cat2', 4, 16, '5ct1qdv50ceqrg22up8dk7vaep', '2025-12-15 20:28:57', '2025-12-15 19:43:57', 'expired'),
(7, 10, 'Cat3', 1, 16, '5ct1qdv50ceqrg22up8dk7vaep', '2025-12-15 20:29:13', '2025-12-15 19:44:13', 'expired'),
(8, 10, 'Cat1', 3, 16, '5ct1qdv50ceqrg22up8dk7vaep', '2025-12-15 20:29:13', '2025-12-15 19:44:13', 'expired'),
(9, 10, 'Cat2', 5, 16, '5ct1qdv50ceqrg22up8dk7vaep', '2025-12-15 20:29:13', '2025-12-15 19:44:13', 'expired'),
(10, 11, 'Regular', 2, 16, 'io73v8lin08r0ep3a6p1jhl4o0', '2025-12-15 20:51:14', '2025-12-15 20:06:14', 'expired'),
(11, 11, 'Regular', 2, 16, '7o8jvphoppsdg1hl124gus1snp', '2025-12-15 20:51:23', '2025-12-15 20:06:23', 'expired'),
(12, 11, 'Premium', 10, 16, '7o8jvphoppsdg1hl124gus1snp', '2025-12-15 20:51:23', '2025-12-15 20:06:23', 'expired'),
(13, 11, 'Premium', 8, 16, '7o8jvphoppsdg1hl124gus1snp', '2025-12-15 20:51:30', '2025-12-15 20:06:30', 'expired'),
(14, 11, 'Regular', 2, 16, '7o8jvphoppsdg1hl124gus1snp', '2025-12-15 20:51:30', '2025-12-15 20:06:30', 'expired'),
(15, 11, 'Premium', 8, 16, '7o8jvphoppsdg1hl124gus1snp', '2025-12-15 20:51:42', '2025-12-15 20:06:42', 'expired'),
(16, 11, 'Regular', 2, 16, '7o8jvphoppsdg1hl124gus1snp', '2025-12-15 20:51:42', '2025-12-15 20:06:42', 'expired'),
(17, 12, 'Cat1', 2, 16, 'g9ukb37n7mqi9ebhll9ktote3n', '2025-12-15 21:00:05', '2025-12-15 20:15:05', 'expired'),
(18, 12, 'Cat3', 2, 16, 'g9ukb37n7mqi9ebhll9ktote3n', '2025-12-15 21:00:05', '2025-12-15 20:15:05', 'expired'),
(19, 12, 'Cat3', 2, 16, 'g9ukb37n7mqi9ebhll9ktote3n', '2025-12-15 21:00:11', '2025-12-15 20:15:11', 'expired'),
(20, 12, 'Cat1', 2, 16, 'g9ukb37n7mqi9ebhll9ktote3n', '2025-12-15 21:00:11', '2025-12-15 20:15:11', 'expired'),
(21, 12, 'Regular', 2, 16, 'g34g4uk9373pticc9ni1p6uh45', '2025-12-15 21:07:55', '2025-12-15 20:22:55', 'expired'),
(22, 12, 'Premium', 3, 16, 'g34g4uk9373pticc9ni1p6uh45', '2025-12-15 21:07:55', '2025-12-15 20:22:55', 'expired'),
(23, 12, 'Premium', 3, 16, 'g34g4uk9373pticc9ni1p6uh45', '2025-12-15 21:07:59', '2025-12-15 20:22:59', 'expired'),
(24, 12, 'Regular', 2, 16, 'g34g4uk9373pticc9ni1p6uh45', '2025-12-15 21:07:59', '2025-12-15 20:22:59', 'expired'),
(25, 10, 'Cat2', 1, 16, 'gdcndk4i2kjlalknhahmdrmq4b', '2025-12-17 04:53:07', '2025-12-17 04:08:07', 'expired'),
(26, 10, 'Cat1', 3, 16, 'gdcndk4i2kjlalknhahmdrmq4b', '2025-12-17 04:53:07', '2025-12-17 04:08:07', 'expired'),
(27, 10, 'Cat3', 1, 16, 'gdcndk4i2kjlalknhahmdrmq4b', '2025-12-17 04:53:07', '2025-12-17 04:08:07', 'expired'),
(28, 10, 'Cat1', 3, 16, 'gdcndk4i2kjlalknhahmdrmq4b', '2025-12-17 04:53:17', '2025-12-17 04:08:17', 'expired'),
(29, 10, 'Cat3', 1, 16, 'gdcndk4i2kjlalknhahmdrmq4b', '2025-12-17 04:53:17', '2025-12-17 04:08:17', 'expired'),
(30, 10, 'Cat2', 1, 16, 'gdcndk4i2kjlalknhahmdrmq4b', '2025-12-17 04:53:17', '2025-12-17 04:08:17', 'expired'),
(31, 10, 'Cat1', 1, 16, 'j8pcflf0cvshq6o8ehfjnp2kvf', '2025-12-17 05:16:31', '2025-12-17 04:31:31', 'expired'),
(32, 10, 'Cat3', 1, 16, 'j8pcflf0cvshq6o8ehfjnp2kvf', '2025-12-17 05:16:31', '2025-12-17 04:31:31', 'expired'),
(33, 9, 'Fanpit', 2, 16, 'j8pcflf0cvshq6o8ehfjnp2kvf', '2025-12-17 05:18:02', '2025-12-17 04:33:02', 'expired'),
(34, 9, 'Regular', 3, 16, 'r0gkpdce4ekthsj57ke9ledd0d', '2025-12-17 05:27:37', '2025-12-17 04:42:37', 'expired'),
(35, 9, 'Fanpit', 1, 16, 'r0gkpdce4ekthsj57ke9ledd0d', '2025-12-17 05:27:37', '2025-12-17 04:42:37', 'expired'),
(36, 9, 'Regular', 3, 16, 'r0gkpdce4ekthsj57ke9ledd0d', '2025-12-17 05:27:43', '2025-12-17 04:42:43', 'expired'),
(37, 9, 'Fanpit', 1, 16, 'r0gkpdce4ekthsj57ke9ledd0d', '2025-12-17 05:27:43', '2025-12-17 04:42:43', 'expired'),
(38, 9, 'Regular', 3, 16, 'r0gkpdce4ekthsj57ke9ledd0d', '2025-12-17 05:27:56', '2025-12-17 04:42:56', 'expired'),
(39, 9, 'Fanpit', 1, 16, 'r0gkpdce4ekthsj57ke9ledd0d', '2025-12-17 05:27:56', '2025-12-17 04:42:56', 'expired'),
(40, 9, 'Regular', 3, 16, 'r0gkpdce4ekthsj57ke9ledd0d', '2025-12-17 05:28:19', '2025-12-17 04:43:19', 'expired'),
(41, 9, 'Fanpit', 1, 16, 'r0gkpdce4ekthsj57ke9ledd0d', '2025-12-17 05:28:19', '2025-12-17 04:43:19', 'expired'),
(42, 10, 'Cat1', 2, 16, 'r0gkpdce4ekthsj57ke9ledd0d', '2025-12-17 05:28:41', '2025-12-17 04:43:41', 'expired'),
(43, 10, 'Cat1', 2, 16, 'r0gkpdce4ekthsj57ke9ledd0d', '2025-12-17 05:28:43', '2025-12-17 04:43:43', 'expired'),
(44, 15, 'Regular', 1, 16, 'h1n1fqpjmnk8he5enn9gafhcd0', '2025-12-17 05:38:05', '2025-12-17 04:53:05', 'expired'),
(45, 15, 'Regular', 1, 16, 'h1n1fqpjmnk8he5enn9gafhcd0', '2025-12-17 05:38:10', '2025-12-17 04:53:10', 'expired'),
(46, 16, 'Regular', 4, NULL, 'agih7qsj0a13ptij3l6gprr4fv', '2025-12-17 05:59:34', '2025-12-17 05:14:34', 'expired'),
(47, 16, 'Fanpit', 4, NULL, 'agih7qsj0a13ptij3l6gprr4fv', '2025-12-17 05:59:34', '2025-12-17 05:14:34', 'expired'),
(48, 16, 'Golden Circle', 4, NULL, 'agih7qsj0a13ptij3l6gprr4fv', '2025-12-17 05:59:34', '2025-12-17 05:14:34', 'expired'),
(49, 16, 'Regular', 4, NULL, 'agih7qsj0a13ptij3l6gprr4fv', '2025-12-17 05:59:38', '2025-12-17 05:14:38', 'expired'),
(50, 16, 'Fanpit', 4, NULL, 'agih7qsj0a13ptij3l6gprr4fv', '2025-12-17 05:59:38', '2025-12-17 05:14:38', 'expired'),
(51, 16, 'Golden Circle', 4, NULL, 'agih7qsj0a13ptij3l6gprr4fv', '2025-12-17 05:59:38', '2025-12-17 05:14:38', 'expired'),
(52, 11, 'Gold', 1, 16, 'afn7qddbo5a0uo7df95qpk44cv', '2025-12-17 07:21:00', '2025-12-17 06:36:00', 'expired'),
(53, 11, 'Premium', 1, 16, 'afn7qddbo5a0uo7df95qpk44cv', '2025-12-17 07:21:00', '2025-12-17 06:36:00', 'expired'),
(54, 11, 'Regular', 1, 16, 'afn7qddbo5a0uo7df95qpk44cv', '2025-12-17 07:21:00', '2025-12-17 06:36:00', 'expired'),
(55, 11, 'Premium', 1, 16, 'afn7qddbo5a0uo7df95qpk44cv', '2025-12-17 07:21:02', '2025-12-17 06:36:02', 'expired'),
(56, 11, 'Gold', 1, 16, 'afn7qddbo5a0uo7df95qpk44cv', '2025-12-17 07:21:02', '2025-12-17 06:36:02', 'expired'),
(57, 11, 'Regular', 1, 16, 'afn7qddbo5a0uo7df95qpk44cv', '2025-12-17 07:21:02', '2025-12-17 06:36:02', 'expired'),
(58, 16, 'Regular', 4, 16, 'adu882qdqdfoscs5sfgsvjtcrp', '2025-12-17 16:31:02', '2025-12-17 15:46:02', 'expired'),
(59, 16, 'Fanpit', 4, 16, 'adu882qdqdfoscs5sfgsvjtcrp', '2025-12-17 16:31:02', '2025-12-17 15:46:02', 'expired'),
(60, 16, 'Golden Circle', 4, 16, 'adu882qdqdfoscs5sfgsvjtcrp', '2025-12-17 16:31:02', '2025-12-17 15:46:02', 'expired'),
(61, 12, 'Cat3', 1, 16, 'adu882qdqdfoscs5sfgsvjtcrp', '2025-12-17 16:32:50', '2025-12-17 15:47:50', 'expired'),
(62, 12, 'Cat1', 2, 16, 'adu882qdqdfoscs5sfgsvjtcrp', '2025-12-17 16:32:50', '2025-12-17 15:47:50', 'expired'),
(63, 12, 'Cat3', 1, 16, 'adu882qdqdfoscs5sfgsvjtcrp', '2025-12-17 16:32:59', '2025-12-17 15:47:59', 'expired'),
(64, 12, 'Cat1', 2, 16, 'adu882qdqdfoscs5sfgsvjtcrp', '2025-12-17 16:32:59', '2025-12-17 15:47:59', 'expired'),
(65, 17, 'Cat1', 2, 16, 'u2u7qi7830o7dih3odhi5l6vg3', '2025-12-17 16:52:45', '2025-12-17 16:07:45', 'expired'),
(66, 17, 'Cat1', 2, 16, 'u2u7qi7830o7dih3odhi5l6vg3', '2025-12-17 16:52:48', '2025-12-17 16:07:48', 'expired'),
(67, 17, 'Cat2', 2, 16, 'u2u7qi7830o7dih3odhi5l6vg3', '2025-12-17 16:53:03', '2025-12-17 16:08:03', 'expired'),
(68, 17, 'Cat1', 1, 16, 'u2u7qi7830o7dih3odhi5l6vg3', '2025-12-17 16:53:03', '2025-12-17 16:08:03', 'expired'),
(69, 17, 'Cat3', 2, 16, 'u2u7qi7830o7dih3odhi5l6vg3', '2025-12-17 16:53:09', '2025-12-17 16:08:09', 'expired'),
(70, 17, 'Cat2', 2, 16, 'u2u7qi7830o7dih3odhi5l6vg3', '2025-12-17 16:53:09', '2025-12-17 16:08:09', 'expired'),
(71, 17, 'Cat1', 1, 16, 'u2u7qi7830o7dih3odhi5l6vg3', '2025-12-17 16:53:09', '2025-12-17 16:08:09', 'expired'),
(72, 17, 'Cat3', 2, 16, 'e77v4gpal15jvmh507auem0u4f', '2025-12-17 16:53:19', '2025-12-17 16:08:19', 'expired'),
(73, 17, 'Cat1', 1, 16, 'e77v4gpal15jvmh507auem0u4f', '2025-12-17 16:53:19', '2025-12-17 16:08:19', 'expired'),
(74, 17, 'Cat2', 2, 16, 'e77v4gpal15jvmh507auem0u4f', '2025-12-17 16:53:19', '2025-12-17 16:08:19', 'expired'),
(75, 16, 'Regular', 1, 16, '58vhu7p6ogjgrgbvl1kn0opn2t', '2025-12-17 17:45:33', '2025-12-17 17:00:33', 'expired'),
(76, 16, 'Fanpit', 2, 16, '58vhu7p6ogjgrgbvl1kn0opn2t', '2025-12-17 17:45:33', '2025-12-17 17:00:33', 'expired'),
(77, 16, 'Golden Circle', 1, 16, '58vhu7p6ogjgrgbvl1kn0opn2t', '2025-12-17 17:45:33', '2025-12-17 17:00:33', 'expired'),
(78, 16, 'Golden Circle', 1, 16, '58vhu7p6ogjgrgbvl1kn0opn2t', '2025-12-17 17:45:36', '2025-12-17 17:00:36', 'expired'),
(79, 16, 'Fanpit', 2, 16, '58vhu7p6ogjgrgbvl1kn0opn2t', '2025-12-17 17:45:36', '2025-12-17 17:00:36', 'expired'),
(80, 16, 'Regular', 1, 16, '58vhu7p6ogjgrgbvl1kn0opn2t', '2025-12-17 17:45:36', '2025-12-17 17:00:36', 'expired'),
(81, 16, 'Fanpit', 1, 16, 'ps3g6kqrs44d7fj3tgs82ufsvb', '2025-12-17 17:52:20', '2025-12-17 17:07:20', 'expired'),
(82, 16, 'Golden Circle', 1, 16, 'ps3g6kqrs44d7fj3tgs82ufsvb', '2025-12-17 17:52:20', '2025-12-17 17:07:20', 'expired'),
(83, 16, 'Regular', 1, 16, 'ps3g6kqrs44d7fj3tgs82ufsvb', '2025-12-17 17:52:20', '2025-12-17 17:07:20', 'expired'),
(84, 16, 'Golden Circle', 3, 16, 'ps3g6kqrs44d7fj3tgs82ufsvb', '2025-12-17 17:52:23', '2025-12-17 17:07:23', 'expired'),
(85, 16, 'Fanpit', 1, 16, 'ps3g6kqrs44d7fj3tgs82ufsvb', '2025-12-17 17:52:23', '2025-12-17 17:07:23', 'expired'),
(86, 16, 'Regular', 1, 16, 'ps3g6kqrs44d7fj3tgs82ufsvb', '2025-12-17 17:52:23', '2025-12-17 17:07:23', 'expired'),
(87, 16, 'Regular', 1, 16, 'ps3g6kqrs44d7fj3tgs82ufsvb', '2025-12-17 17:52:39', '2025-12-17 17:07:39', 'expired'),
(88, 16, 'Golden Circle', 3, 16, 'ps3g6kqrs44d7fj3tgs82ufsvb', '2025-12-17 17:52:39', '2025-12-17 17:07:39', 'expired'),
(89, 16, 'Regular', 1, 16, 'ps3g6kqrs44d7fj3tgs82ufsvb', '2025-12-17 17:52:40', '2025-12-17 17:07:40', 'expired'),
(90, 16, 'Golden Circle', 3, 16, 'ps3g6kqrs44d7fj3tgs82ufsvb', '2025-12-17 17:52:40', '2025-12-17 17:07:40', 'expired'),
(91, 16, 'Golden Circle', 2, 16, 'i8sh13vd6u5j0qjiijq5qvqtgs', '2025-12-17 17:57:39', '2025-12-17 17:12:39', 'expired'),
(92, 16, 'Regular', 1, 16, 'i8sh13vd6u5j0qjiijq5qvqtgs', '2025-12-17 17:57:39', '2025-12-17 17:12:39', 'expired'),
(93, 16, 'Fanpit', 1, 16, 'i8sh13vd6u5j0qjiijq5qvqtgs', '2025-12-17 17:57:39', '2025-12-17 17:12:39', 'expired'),
(94, 16, 'Regular', 1, 16, 'i8sh13vd6u5j0qjiijq5qvqtgs', '2025-12-17 17:57:40', '2025-12-17 17:12:40', 'expired'),
(95, 16, 'Golden Circle', 2, 16, 'i8sh13vd6u5j0qjiijq5qvqtgs', '2025-12-17 17:57:40', '2025-12-17 17:12:40', 'expired'),
(96, 16, 'Fanpit', 1, 16, 'i8sh13vd6u5j0qjiijq5qvqtgs', '2025-12-17 17:57:40', '2025-12-17 17:12:40', 'expired'),
(97, 16, 'Fanpit', 1, 16, 'i8sh13vd6u5j0qjiijq5qvqtgs', '2025-12-17 18:00:12', '2025-12-17 17:15:12', 'expired'),
(98, 16, 'Regular', 1, 16, 'i8sh13vd6u5j0qjiijq5qvqtgs', '2025-12-17 18:00:12', '2025-12-17 17:15:12', 'expired'),
(99, 16, 'Golden Circle', 2, 16, 'i8sh13vd6u5j0qjiijq5qvqtgs', '2025-12-17 18:00:12', '2025-12-17 17:15:12', 'expired'),
(100, 16, 'Regular', 1, 16, 'i8sh13vd6u5j0qjiijq5qvqtgs', '2025-12-17 18:00:15', '2025-12-17 17:15:15', 'expired'),
(101, 16, 'Golden Circle', 2, 16, 'i8sh13vd6u5j0qjiijq5qvqtgs', '2025-12-17 18:00:15', '2025-12-17 17:15:15', 'expired'),
(102, 16, 'Fanpit', 1, 16, 'i8sh13vd6u5j0qjiijq5qvqtgs', '2025-12-17 18:00:15', '2025-12-17 17:15:15', 'expired'),
(103, 16, 'Regular', 4, 16, 'i8sh13vd6u5j0qjiijq5qvqtgs', '2025-12-17 18:01:24', '2025-12-17 17:16:24', 'expired'),
(104, 16, 'Regular', 4, 16, 'i8sh13vd6u5j0qjiijq5qvqtgs', '2025-12-17 18:01:25', '2025-12-17 17:16:25', 'expired'),
(105, 16, 'Fanpit', 1, 16, 'mlc6fa8stoj3vcop9ckvgr13g5', '2025-12-17 18:15:48', '2025-12-17 17:30:48', 'expired'),
(106, 16, 'Regular', 1, 16, 'mlc6fa8stoj3vcop9ckvgr13g5', '2025-12-17 18:15:48', '2025-12-17 17:30:48', 'expired'),
(107, 16, 'Golden Circle', 2, 16, 'mlc6fa8stoj3vcop9ckvgr13g5', '2025-12-17 18:15:48', '2025-12-17 17:30:48', 'expired'),
(108, 16, 'Regular', 1, 16, 'mlc6fa8stoj3vcop9ckvgr13g5', '2025-12-17 18:15:49', '2025-12-17 17:30:49', 'expired'),
(109, 16, 'Fanpit', 1, 16, 'mlc6fa8stoj3vcop9ckvgr13g5', '2025-12-17 18:15:49', '2025-12-17 17:30:49', 'expired'),
(110, 16, 'Golden Circle', 2, 16, 'mlc6fa8stoj3vcop9ckvgr13g5', '2025-12-17 18:15:49', '2025-12-17 17:30:49', 'expired'),
(111, 9, 'Fanpit', 2, 16, 'mlc6fa8stoj3vcop9ckvgr13g5', '2025-12-17 18:17:03', '2025-12-17 17:32:03', 'expired'),
(112, 9, 'Fanpit', 2, 16, 'mlc6fa8stoj3vcop9ckvgr13g5', '2025-12-17 18:17:05', '2025-12-17 17:32:05', 'expired'),
(113, 9, 'Regular', 2, 16, 'mlc6fa8stoj3vcop9ckvgr13g5', '2025-12-17 18:17:23', '2025-12-17 17:32:23', 'expired'),
(114, 9, 'Fanpit', 1, 16, 'mlc6fa8stoj3vcop9ckvgr13g5', '2025-12-17 18:17:23', '2025-12-17 17:32:23', 'expired'),
(115, 9, 'Golden Circle', 1, 16, 'mlc6fa8stoj3vcop9ckvgr13g5', '2025-12-17 18:17:23', '2025-12-17 17:32:23', 'expired'),
(116, 9, 'Regular', 2, 16, 'mlc6fa8stoj3vcop9ckvgr13g5', '2025-12-17 18:17:25', '2025-12-17 17:32:25', 'expired'),
(117, 9, 'Fanpit', 1, 16, 'mlc6fa8stoj3vcop9ckvgr13g5', '2025-12-17 18:17:25', '2025-12-17 17:32:25', 'expired'),
(118, 9, 'Golden Circle', 1, 16, 'mlc6fa8stoj3vcop9ckvgr13g5', '2025-12-17 18:17:25', '2025-12-17 17:32:25', 'expired'),
(119, 9, 'Regular', 4, 16, 'mlc6fa8stoj3vcop9ckvgr13g5', '2025-12-17 18:19:18', '2025-12-17 17:34:18', 'expired'),
(120, 9, 'Regular', 4, 16, 'mlc6fa8stoj3vcop9ckvgr13g5', '2025-12-17 18:19:21', '2025-12-17 17:34:21', 'expired'),
(121, 9, 'Regular', 1, 16, 'rucq8nire2gh46pk5vuvefk1lk', '2025-12-17 18:28:52', '2025-12-17 17:43:52', 'expired'),
(122, 9, 'Fanpit', 1, 16, 'rucq8nire2gh46pk5vuvefk1lk', '2025-12-17 18:28:52', '2025-12-17 17:43:52', 'expired'),
(123, 9, 'Golden Circle', 2, 16, 'rucq8nire2gh46pk5vuvefk1lk', '2025-12-17 18:28:52', '2025-12-17 17:43:52', 'expired'),
(124, 9, 'Regular', 1, 16, 'rucq8nire2gh46pk5vuvefk1lk', '2025-12-17 18:28:53', '2025-12-17 17:43:53', 'expired'),
(125, 9, 'Fanpit', 1, 16, 'rucq8nire2gh46pk5vuvefk1lk', '2025-12-17 18:28:53', '2025-12-17 17:43:53', 'expired'),
(126, 9, 'Golden Circle', 2, 16, 'rucq8nire2gh46pk5vuvefk1lk', '2025-12-17 18:28:53', '2025-12-17 17:43:53', 'expired'),
(127, 9, 'Fanpit', 1, 16, 'rucq8nire2gh46pk5vuvefk1lk', '2025-12-17 18:30:46', '2025-12-17 17:45:46', 'expired'),
(128, 9, 'Regular', 1, 16, 'rucq8nire2gh46pk5vuvefk1lk', '2025-12-17 18:30:46', '2025-12-17 17:45:46', 'expired'),
(129, 9, 'Golden Circle', 2, 16, 'rucq8nire2gh46pk5vuvefk1lk', '2025-12-17 18:30:46', '2025-12-17 17:45:46', 'expired'),
(130, 9, 'Regular', 1, 16, 'rucq8nire2gh46pk5vuvefk1lk', '2025-12-17 18:30:48', '2025-12-17 17:45:48', 'expired'),
(131, 9, 'Fanpit', 1, 16, 'rucq8nire2gh46pk5vuvefk1lk', '2025-12-17 18:30:48', '2025-12-17 17:45:48', 'expired'),
(132, 9, 'Golden Circle', 2, 16, 'rucq8nire2gh46pk5vuvefk1lk', '2025-12-17 18:30:48', '2025-12-17 17:45:48', 'expired'),
(133, 11, 'Premium', 2, 16, 'lj4kc9c1gvu3tabkrq7169fvga', '2025-12-17 18:35:41', '2025-12-17 17:50:41', 'expired'),
(134, 11, 'Gold', 2, 16, 'lj4kc9c1gvu3tabkrq7169fvga', '2025-12-17 18:35:41', '2025-12-17 17:50:41', 'expired'),
(135, 11, 'Gold', 2, 16, 'lj4kc9c1gvu3tabkrq7169fvga', '2025-12-17 18:35:42', '2025-12-17 17:50:42', 'expired'),
(136, 11, 'Premium', 2, 16, 'lj4kc9c1gvu3tabkrq7169fvga', '2025-12-17 18:35:42', '2025-12-17 17:50:42', 'expired'),
(137, 16, 'Regular', 1, 16, 'lj4kc9c1gvu3tabkrq7169fvga', '2025-12-17 18:37:27', '2025-12-17 17:52:27', 'expired'),
(138, 16, 'Golden Circle', 3, 16, 'lj4kc9c1gvu3tabkrq7169fvga', '2025-12-17 18:37:27', '2025-12-17 17:52:27', 'expired'),
(139, 16, 'Regular', 1, 16, 'lj4kc9c1gvu3tabkrq7169fvga', '2025-12-17 18:37:29', '2025-12-17 17:52:29', 'expired'),
(140, 16, 'Golden Circle', 3, 16, 'lj4kc9c1gvu3tabkrq7169fvga', '2025-12-17 18:37:29', '2025-12-17 17:52:29', 'expired'),
(141, 16, 'Fanpit', 1, 16, 'lj4kc9c1gvu3tabkrq7169fvga', '2025-12-17 18:39:07', '2025-12-17 17:54:07', 'expired'),
(142, 16, 'Golden Circle', 2, 16, 'lj4kc9c1gvu3tabkrq7169fvga', '2025-12-17 18:39:07', '2025-12-17 17:54:07', 'expired'),
(143, 16, 'Regular', 1, 16, 'lj4kc9c1gvu3tabkrq7169fvga', '2025-12-17 18:39:07', '2025-12-17 17:54:07', 'expired'),
(144, 16, 'Regular', 1, 16, 'lj4kc9c1gvu3tabkrq7169fvga', '2025-12-17 18:39:17', '2025-12-17 17:54:17', 'expired'),
(145, 16, 'Fanpit', 1, 16, 'lj4kc9c1gvu3tabkrq7169fvga', '2025-12-17 18:39:17', '2025-12-17 17:54:17', 'expired'),
(146, 16, 'Golden Circle', 2, 16, 'lj4kc9c1gvu3tabkrq7169fvga', '2025-12-17 18:39:17', '2025-12-17 17:54:17', 'expired'),
(147, 16, 'Golden Circle', 2, 16, 'lj4kc9c1gvu3tabkrq7169fvga', '2025-12-17 18:39:21', '2025-12-17 17:54:21', 'expired'),
(148, 16, 'Regular', 1, 16, 'lj4kc9c1gvu3tabkrq7169fvga', '2025-12-17 18:39:21', '2025-12-17 17:54:21', 'expired'),
(149, 16, 'Fanpit', 1, 16, 'lj4kc9c1gvu3tabkrq7169fvga', '2025-12-17 18:39:21', '2025-12-17 17:54:21', 'expired'),
(150, 16, 'Golden Circle', 1, 16, 'jen69vdg440qs7qapgmarr8tjn', '2025-12-17 18:40:53', '2025-12-17 17:55:53', 'expired'),
(151, 16, 'Regular', 1, 16, 'jen69vdg440qs7qapgmarr8tjn', '2025-12-17 18:40:53', '2025-12-17 17:55:53', 'expired'),
(152, 16, 'Fanpit', 2, 16, 'jen69vdg440qs7qapgmarr8tjn', '2025-12-17 18:40:53', '2025-12-17 17:55:53', 'expired'),
(153, 16, 'Fanpit', 2, 16, 'jen69vdg440qs7qapgmarr8tjn', '2025-12-17 18:40:55', '2025-12-17 17:55:55', 'expired'),
(154, 16, 'Golden Circle', 1, 16, 'jen69vdg440qs7qapgmarr8tjn', '2025-12-17 18:40:55', '2025-12-17 17:55:55', 'expired'),
(155, 16, 'Regular', 1, 16, 'jen69vdg440qs7qapgmarr8tjn', '2025-12-17 18:40:55', '2025-12-17 17:55:55', 'expired'),
(156, 9, 'Regular', 1, 16, 'q9jb5d50hdsg51aeqf23cf20l3', '2025-12-17 18:54:45', '2025-12-17 18:09:45', 'expired'),
(157, 9, 'Golden Circle', 2, 16, 'q9jb5d50hdsg51aeqf23cf20l3', '2025-12-17 18:54:45', '2025-12-17 18:09:45', 'expired'),
(158, 9, 'Fanpit', 1, 16, 'q9jb5d50hdsg51aeqf23cf20l3', '2025-12-17 18:54:45', '2025-12-17 18:09:45', 'expired'),
(159, 9, 'Golden Circle', 2, 16, 'q9jb5d50hdsg51aeqf23cf20l3', '2025-12-17 18:54:46', '2025-12-17 18:09:46', 'expired'),
(160, 9, 'Regular', 1, 16, 'q9jb5d50hdsg51aeqf23cf20l3', '2025-12-17 18:54:46', '2025-12-17 18:09:46', 'expired'),
(161, 9, 'Fanpit', 1, 16, 'q9jb5d50hdsg51aeqf23cf20l3', '2025-12-17 18:54:46', '2025-12-17 18:09:46', 'expired'),
(162, 16, 'Fanpit', 1, 16, 'q9jb5d50hdsg51aeqf23cf20l3', '2025-12-17 18:56:50', '2025-12-17 18:11:50', 'expired'),
(163, 16, 'Golden Circle', 2, 16, 'q9jb5d50hdsg51aeqf23cf20l3', '2025-12-17 18:56:50', '2025-12-17 18:11:50', 'expired'),
(164, 16, 'Regular', 1, 16, 'q9jb5d50hdsg51aeqf23cf20l3', '2025-12-17 18:56:50', '2025-12-17 18:11:50', 'expired'),
(165, 16, 'Fanpit', 1, 16, 'q9jb5d50hdsg51aeqf23cf20l3', '2025-12-17 18:56:51', '2025-12-17 18:11:51', 'expired'),
(166, 16, 'Golden Circle', 2, 16, 'q9jb5d50hdsg51aeqf23cf20l3', '2025-12-17 18:56:51', '2025-12-17 18:11:51', 'expired'),
(167, 16, 'Regular', 1, 16, 'q9jb5d50hdsg51aeqf23cf20l3', '2025-12-17 18:56:51', '2025-12-17 18:11:51', 'expired'),
(168, 15, 'Premium', 1, 16, 'cbj5u4v56kr5vbfvcuu10beb76', '2025-12-17 19:05:37', '2025-12-17 18:20:37', 'expired'),
(169, 15, 'Gold', 1, 16, 'cbj5u4v56kr5vbfvcuu10beb76', '2025-12-17 19:05:37', '2025-12-17 18:20:37', 'expired'),
(170, 15, 'Regular', 2, 16, 'cbj5u4v56kr5vbfvcuu10beb76', '2025-12-17 19:05:37', '2025-12-17 18:20:37', 'expired'),
(171, 15, 'Premium', 1, 16, 'cbj5u4v56kr5vbfvcuu10beb76', '2025-12-17 19:05:39', '2025-12-17 18:46:39', 'expired'),
(172, 15, 'Regular', 2, 16, 'cbj5u4v56kr5vbfvcuu10beb76', '2025-12-17 19:05:39', '2025-12-17 18:46:39', 'expired'),
(173, 15, 'Gold', 1, 16, 'cbj5u4v56kr5vbfvcuu10beb76', '2025-12-17 19:05:39', '2025-12-17 18:46:39', 'expired'),
(174, 12, 'Cat3', 1, 16, 'afsjfniq5vbbfq4ri059keul8a', '2025-12-17 19:18:06', '2025-12-17 18:33:06', 'expired'),
(175, 12, 'Cat2', 4, 16, 'afsjfniq5vbbfq4ri059keul8a', '2025-12-17 19:18:06', '2025-12-17 18:33:06', 'expired'),
(176, 12, 'Cat1', 3, 16, 'afsjfniq5vbbfq4ri059keul8a', '2025-12-17 19:18:06', '2025-12-17 18:33:06', 'expired'),
(177, 12, 'Cat3', 1, 16, 'afsjfniq5vbbfq4ri059keul8a', '2025-12-17 19:18:09', '2025-12-17 18:58:28', 'expired'),
(178, 12, 'Cat1', 3, 16, 'afsjfniq5vbbfq4ri059keul8a', '2025-12-17 19:18:09', '2025-12-17 18:58:28', 'expired'),
(179, 12, 'Cat2', 4, 16, 'afsjfniq5vbbfq4ri059keul8a', '2025-12-17 19:18:09', '2025-12-17 18:58:28', 'expired'),
(180, 9, 'Fanpit', 1, 16, '0i88vok1c8mj6ssinmihvtjdoh', '2025-12-17 19:30:39', '2025-12-17 18:45:39', 'expired'),
(181, 9, 'Golden Circle', 1, 16, '0i88vok1c8mj6ssinmihvtjdoh', '2025-12-17 19:30:39', '2025-12-17 18:45:39', 'expired'),
(182, 9, 'Regular', 2, 16, '0i88vok1c8mj6ssinmihvtjdoh', '2025-12-17 19:30:39', '2025-12-17 18:45:39', 'expired'),
(183, 9, 'Golden Circle', 1, 16, '0i88vok1c8mj6ssinmihvtjdoh', '2025-12-17 19:30:41', '2025-12-17 19:00:46', 'expired'),
(184, 9, 'Fanpit', 1, 16, '0i88vok1c8mj6ssinmihvtjdoh', '2025-12-17 19:30:41', '2025-12-17 19:00:46', 'expired'),
(185, 9, 'Regular', 2, 16, '0i88vok1c8mj6ssinmihvtjdoh', '2025-12-17 19:30:41', '2025-12-17 19:00:46', 'expired'),
(186, 15, 'Premium', 1, 18, 'r17lgnptlclqrfau753t8kqb6d', '2025-12-17 19:59:56', '2025-12-17 19:14:56', 'expired'),
(187, 15, 'Regular', 1, 18, 'r17lgnptlclqrfau753t8kqb6d', '2025-12-17 19:59:56', '2025-12-17 19:14:56', 'expired'),
(188, 15, 'Gold', 1, 18, 'r17lgnptlclqrfau753t8kqb6d', '2025-12-17 19:59:56', '2025-12-17 19:14:56', 'expired'),
(189, 15, 'Gold', 1, 18, 'r17lgnptlclqrfau753t8kqb6d', '2025-12-17 19:59:58', '2025-12-17 19:30:35', 'expired'),
(190, 15, 'Premium', 1, 18, 'r17lgnptlclqrfau753t8kqb6d', '2025-12-17 19:59:58', '2025-12-17 19:30:35', 'expired'),
(191, 15, 'Regular', 1, 18, 'r17lgnptlclqrfau753t8kqb6d', '2025-12-17 19:59:58', '2025-12-17 19:30:35', 'expired'),
(192, 9, 'Fanpit', 1, 16, 'qt9aisdq4dec2dfkjvc0j7tiuu', '2025-12-18 16:52:52', '2025-12-18 16:07:52', 'expired'),
(193, 9, 'Golden Circle', 1, 16, 'qt9aisdq4dec2dfkjvc0j7tiuu', '2025-12-18 16:52:52', '2025-12-18 16:07:52', 'expired'),
(194, 9, 'Fanpit', 1, 16, 'qt9aisdq4dec2dfkjvc0j7tiuu', '2025-12-18 16:52:54', '2025-12-18 16:24:54', 'expired'),
(195, 9, 'Golden Circle', 1, 16, 'qt9aisdq4dec2dfkjvc0j7tiuu', '2025-12-18 16:52:54', '2025-12-18 16:24:54', 'expired'),
(196, 12, 'Cat1', 1, 16, '4t0vf8vmpp1e1t9ahpdr82euln', '2025-12-18 16:58:46', '2025-12-18 16:13:46', 'expired'),
(197, 12, 'Cat3', 2, 16, '4t0vf8vmpp1e1t9ahpdr82euln', '2025-12-18 16:58:46', '2025-12-18 16:13:46', 'expired'),
(198, 12, 'Cat2', 1, 16, '4t0vf8vmpp1e1t9ahpdr82euln', '2025-12-18 16:58:46', '2025-12-18 16:13:46', 'expired'),
(199, 12, 'Cat2', 1, 16, 'qslf1rrcdfnbj0mvkflmb4qap6', '2025-12-18 18:49:50', '2025-12-18 18:04:50', 'expired'),
(200, 12, 'Cat3', 2, 16, 'qslf1rrcdfnbj0mvkflmb4qap6', '2025-12-18 18:49:50', '2025-12-18 18:04:50', 'expired'),
(201, 12, 'Cat1', 1, 16, 'qslf1rrcdfnbj0mvkflmb4qap6', '2025-12-18 18:49:50', '2025-12-18 18:04:50', 'expired'),
(202, 12, 'Cat1', 1, 16, 'qslf1rrcdfnbj0mvkflmb4qap6', '2025-12-18 18:49:53', '2025-12-18 18:20:41', 'expired'),
(203, 12, 'Cat3', 2, 16, 'qslf1rrcdfnbj0mvkflmb4qap6', '2025-12-18 18:49:53', '2025-12-18 18:20:41', 'expired'),
(204, 12, 'Cat2', 1, 16, 'qslf1rrcdfnbj0mvkflmb4qap6', '2025-12-18 18:49:53', '2025-12-18 18:20:41', 'expired'),
(205, 12, 'Cat2', 2, 16, 'n5mq9g0kj24gb642l7iuf97m8q', '2025-12-18 19:15:52', '2025-12-18 18:30:52', 'expired'),
(206, 12, 'Cat1', 2, 16, 'n5mq9g0kj24gb642l7iuf97m8q', '2025-12-18 19:15:52', '2025-12-18 18:30:52', 'expired'),
(207, 12, 'Cat1', 2, 16, 'n5mq9g0kj24gb642l7iuf97m8q', '2025-12-18 19:15:54', '2025-12-18 18:47:16', 'expired'),
(208, 12, 'Cat2', 2, 16, 'n5mq9g0kj24gb642l7iuf97m8q', '2025-12-18 19:15:54', '2025-12-18 18:47:16', 'expired'),
(209, 11, 'Gold', 2, 16, 'obc9ps9l2q7nf79a7rer1b8nbe', '2025-12-18 19:41:05', '2025-12-18 18:56:05', 'expired'),
(210, 11, 'Regular', 1, 16, 'obc9ps9l2q7nf79a7rer1b8nbe', '2025-12-18 19:41:05', '2025-12-18 18:56:05', 'expired'),
(211, 11, 'Premium', 2, 16, 'obc9ps9l2q7nf79a7rer1b8nbe', '2025-12-18 19:41:05', '2025-12-18 18:56:05', 'expired'),
(212, 11, 'Regular', 1, 16, 'obc9ps9l2q7nf79a7rer1b8nbe', '2025-12-18 19:41:06', '2025-12-18 19:11:15', 'expired'),
(213, 11, 'Premium', 2, 16, 'obc9ps9l2q7nf79a7rer1b8nbe', '2025-12-18 19:41:06', '2025-12-18 19:11:15', 'expired'),
(214, 11, 'Gold', 2, 16, 'obc9ps9l2q7nf79a7rer1b8nbe', '2025-12-18 19:41:06', '2025-12-18 19:11:15', 'expired'),
(215, 12, 'Cat1', 2, 16, 'obc9ps9l2q7nf79a7rer1b8nbe', '2025-12-18 19:41:39', '2025-12-18 18:56:39', 'expired'),
(216, 12, 'Cat2', 1, 16, 'obc9ps9l2q7nf79a7rer1b8nbe', '2025-12-18 19:41:39', '2025-12-18 18:56:39', 'expired'),
(217, 12, 'Cat3', 1, 16, 'obc9ps9l2q7nf79a7rer1b8nbe', '2025-12-18 19:41:39', '2025-12-18 18:56:39', 'expired'),
(218, 12, 'Cat2', 1, 16, 'obc9ps9l2q7nf79a7rer1b8nbe', '2025-12-18 19:41:40', '2025-12-18 19:14:31', 'expired'),
(219, 12, 'Cat1', 2, 16, 'obc9ps9l2q7nf79a7rer1b8nbe', '2025-12-18 19:41:40', '2025-12-18 19:14:31', 'expired'),
(220, 12, 'Cat3', 1, 16, 'obc9ps9l2q7nf79a7rer1b8nbe', '2025-12-18 19:41:40', '2025-12-18 19:14:31', 'expired'),
(221, 12, 'Cat1', 2, 16, 'q0tb75iol80gpclg7a73dpf0fd', '2025-12-18 19:57:35', '2025-12-18 19:12:35', 'expired'),
(222, 12, 'Cat2', 1, 16, 'q0tb75iol80gpclg7a73dpf0fd', '2025-12-18 19:57:35', '2025-12-18 19:12:35', 'expired'),
(223, 12, 'Cat3', 1, 16, 'q0tb75iol80gpclg7a73dpf0fd', '2025-12-18 19:57:35', '2025-12-18 19:12:35', 'expired'),
(224, 12, 'Cat2', 1, 16, 'q0tb75iol80gpclg7a73dpf0fd', '2025-12-18 19:57:37', '2025-12-18 19:28:10', 'expired'),
(225, 12, 'Cat3', 1, 16, 'q0tb75iol80gpclg7a73dpf0fd', '2025-12-18 19:57:37', '2025-12-18 19:28:10', 'expired'),
(226, 12, 'Cat1', 2, 16, 'q0tb75iol80gpclg7a73dpf0fd', '2025-12-18 19:57:37', '2025-12-18 19:28:10', 'expired'),
(227, 12, 'Cat2', 1, 16, 'st8itiheomall0gvqt0m1nra1q', '2025-12-18 20:09:47', '2025-12-18 19:24:47', 'expired'),
(228, 12, 'Cat3', 1, 16, 'st8itiheomall0gvqt0m1nra1q', '2025-12-18 20:09:47', '2025-12-18 19:24:47', 'expired'),
(229, 12, 'Cat1', 2, 16, 'st8itiheomall0gvqt0m1nra1q', '2025-12-18 20:09:47', '2025-12-18 19:24:47', 'expired'),
(230, 12, 'Cat1', 2, 16, 'st8itiheomall0gvqt0m1nra1q', '2025-12-18 20:09:48', '2025-12-18 19:40:25', 'expired'),
(231, 12, 'Cat3', 1, 16, 'st8itiheomall0gvqt0m1nra1q', '2025-12-18 20:09:48', '2025-12-18 19:40:25', 'expired'),
(232, 12, 'Cat2', 1, 16, 'st8itiheomall0gvqt0m1nra1q', '2025-12-18 20:09:48', '2025-12-18 19:40:25', 'expired'),
(233, 12, 'Cat2', 1, 16, 'st8itiheomall0gvqt0m1nra1q', '2025-12-18 20:10:46', '2025-12-18 19:25:46', 'expired'),
(234, 12, 'Cat3', 1, 16, 'st8itiheomall0gvqt0m1nra1q', '2025-12-18 20:10:46', '2025-12-18 19:25:46', 'expired'),
(235, 12, 'Cat1', 1, 16, 'st8itiheomall0gvqt0m1nra1q', '2025-12-18 20:10:46', '2025-12-18 19:25:46', 'expired'),
(236, 12, 'Cat3', 1, 16, 'st8itiheomall0gvqt0m1nra1q', '2025-12-18 20:10:48', '2025-12-18 19:41:06', 'expired'),
(237, 12, 'Cat1', 1, 16, 'st8itiheomall0gvqt0m1nra1q', '2025-12-18 20:10:48', '2025-12-18 19:41:06', 'expired'),
(238, 12, 'Cat2', 1, 16, 'st8itiheomall0gvqt0m1nra1q', '2025-12-18 20:10:48', '2025-12-18 19:41:06', 'expired'),
(239, 12, 'Cat3', 1, 16, 'st71r23dva7bofepk0annqs3qh', '2025-12-18 20:21:36', '2025-12-18 19:36:36', 'expired'),
(240, 12, 'Cat2', 1, 16, 'st71r23dva7bofepk0annqs3qh', '2025-12-18 20:21:36', '2025-12-18 19:36:36', 'expired'),
(241, 12, 'Cat1', 1, 16, 'st71r23dva7bofepk0annqs3qh', '2025-12-18 20:21:36', '2025-12-18 19:36:36', 'expired'),
(242, 12, 'Cat1', 1, 16, 'st71r23dva7bofepk0annqs3qh', '2025-12-18 20:21:38', '2025-12-18 19:52:41', 'expired'),
(243, 12, 'Cat2', 1, 16, 'st71r23dva7bofepk0annqs3qh', '2025-12-18 20:21:38', '2025-12-18 19:52:41', 'expired'),
(244, 12, 'Cat3', 1, 16, 'st71r23dva7bofepk0annqs3qh', '2025-12-18 20:21:38', '2025-12-18 19:52:41', 'expired'),
(245, 11, 'Gold', 1, 16, 'pium2iihjgcbdauejjgiv0iq0a', '2025-12-18 20:41:49', '2025-12-18 19:56:49', 'expired'),
(246, 11, 'Regular', 2, 16, 'pium2iihjgcbdauejjgiv0iq0a', '2025-12-18 20:41:49', '2025-12-18 19:56:49', 'expired'),
(247, 11, 'Premium', 1, 16, 'pium2iihjgcbdauejjgiv0iq0a', '2025-12-18 20:41:49', '2025-12-18 19:56:49', 'expired'),
(248, 11, 'Premium', 1, 16, 'pium2iihjgcbdauejjgiv0iq0a', '2025-12-18 20:41:50', '2025-12-18 20:24:01', 'expired'),
(249, 11, 'Regular', 2, 16, 'pium2iihjgcbdauejjgiv0iq0a', '2025-12-18 20:41:50', '2025-12-18 20:24:01', 'expired'),
(250, 11, 'Gold', 1, 16, 'pium2iihjgcbdauejjgiv0iq0a', '2025-12-18 20:41:50', '2025-12-18 20:24:01', 'expired'),
(251, 17, 'Cat2', 1, 16, 'j6ul5qqj71jnccvep2sh7erpv3', '2025-12-18 20:54:21', '2025-12-18 20:09:21', 'expired'),
(252, 17, 'Cat3', 1, 16, 'j6ul5qqj71jnccvep2sh7erpv3', '2025-12-18 20:54:21', '2025-12-18 20:09:21', 'expired'),
(253, 17, 'Cat1', 1, 16, 'j6ul5qqj71jnccvep2sh7erpv3', '2025-12-18 20:54:21', '2025-12-18 20:09:21', 'expired'),
(254, 17, 'Cat1', 1, 16, 'j6ul5qqj71jnccvep2sh7erpv3', '2025-12-18 20:54:22', '2025-12-18 20:24:23', 'expired'),
(255, 17, 'Cat3', 1, 16, 'j6ul5qqj71jnccvep2sh7erpv3', '2025-12-18 20:54:22', '2025-12-18 20:24:23', 'expired'),
(256, 17, 'Cat2', 1, 16, 'j6ul5qqj71jnccvep2sh7erpv3', '2025-12-18 20:54:22', '2025-12-18 20:24:23', 'expired'),
(257, 17, 'Cat2', 1, 16, 'j6ul5qqj71jnccvep2sh7erpv3', '2025-12-18 20:58:35', '2025-12-18 20:13:35', 'expired'),
(258, 17, 'Cat3', 1, 16, 'j6ul5qqj71jnccvep2sh7erpv3', '2025-12-18 20:58:35', '2025-12-18 20:13:35', 'expired'),
(259, 17, 'Cat1', 1, 16, 'j6ul5qqj71jnccvep2sh7erpv3', '2025-12-18 20:58:35', '2025-12-18 20:13:35', 'expired'),
(260, 17, 'Cat2', 1, 16, 'j6ul5qqj71jnccvep2sh7erpv3', '2025-12-18 20:58:36', '2025-12-18 20:47:14', 'expired'),
(261, 17, 'Cat1', 1, 16, 'j6ul5qqj71jnccvep2sh7erpv3', '2025-12-18 20:58:36', '2025-12-18 20:47:14', 'expired'),
(262, 17, 'Cat3', 1, 16, 'j6ul5qqj71jnccvep2sh7erpv3', '2025-12-18 20:58:36', '2025-12-18 20:47:14', 'expired'),
(263, 9, 'Regular', 1, 16, 'la17tkdob40daunabr588untv7', '2025-12-18 21:18:57', '2025-12-18 20:33:57', 'expired'),
(264, 9, 'Fanpit', 1, 16, 'la17tkdob40daunabr588untv7', '2025-12-18 21:18:57', '2025-12-18 20:33:57', 'expired'),
(265, 9, 'Golden Circle', 2, 16, 'la17tkdob40daunabr588untv7', '2025-12-18 21:18:57', '2025-12-18 20:33:57', 'expired'),
(266, 9, 'Regular', 1, 16, 'la17tkdob40daunabr588untv7', '2025-12-18 21:18:58', '2025-12-18 21:01:00', 'confirmed'),
(267, 9, 'Golden Circle', 2, 16, 'la17tkdob40daunabr588untv7', '2025-12-18 21:18:58', '2025-12-18 21:01:00', 'confirmed'),
(268, 9, 'Fanpit', 1, 16, 'la17tkdob40daunabr588untv7', '2025-12-18 21:18:58', '2025-12-18 21:01:00', 'confirmed'),
(269, 12, 'Cat3', 1, 16, 'hmt8m9p3f8uc1hp4smgm5datkb', '2025-12-18 21:33:57', '2025-12-18 20:48:57', 'expired'),
(270, 12, 'Cat1', 2, 16, 'hmt8m9p3f8uc1hp4smgm5datkb', '2025-12-18 21:33:57', '2025-12-18 20:48:57', 'expired'),
(271, 12, 'Cat2', 1, 16, 'hmt8m9p3f8uc1hp4smgm5datkb', '2025-12-18 21:33:57', '2025-12-18 20:48:57', 'expired'),
(272, 12, 'Cat1', 2, 16, 'hmt8m9p3f8uc1hp4smgm5datkb', '2025-12-18 21:33:58', '2025-12-18 21:03:58', 'confirmed'),
(273, 12, 'Cat2', 1, 16, 'hmt8m9p3f8uc1hp4smgm5datkb', '2025-12-18 21:33:58', '2025-12-18 21:03:58', 'confirmed'),
(274, 12, 'Cat3', 1, 16, 'hmt8m9p3f8uc1hp4smgm5datkb', '2025-12-18 21:33:58', '2025-12-18 21:03:58', 'confirmed'),
(275, 12, 'Cat3', 4, 16, '7nc0u7q6joemdj493bpuf93ukf', '2025-12-18 21:43:15', '2025-12-18 20:58:15', 'expired'),
(276, 12, 'Cat3', 4, 16, '7nc0u7q6joemdj493bpuf93ukf', '2025-12-18 21:43:16', '2025-12-18 21:13:16', 'confirmed'),
(277, 12, 'Cat1', 2, 16, '7nc0u7q6joemdj493bpuf93ukf', '2025-12-18 21:44:32', '2025-12-18 20:59:32', 'expired'),
(278, 12, 'Cat1', 2, 16, '7nc0u7q6joemdj493bpuf93ukf', '2025-12-18 21:44:34', '2025-12-18 21:14:57', 'confirmed'),
(279, 12, 'Cat2', 1, 16, 'k9ol6pddh18kgh5jlrrmu47eoe', '2025-12-18 21:50:20', '2025-12-18 21:05:20', 'expired'),
(280, 12, 'Cat2', 1, 16, 'k9ol6pddh18kgh5jlrrmu47eoe', '2025-12-18 21:50:22', '2025-12-18 21:26:04', 'confirmed'),
(281, 11, 'Gold', 3, 16, 'f7fhbrcfdc67olvpcilu9ecrpu', '2025-12-18 22:28:14', '2025-12-18 21:43:14', 'expired'),
(282, 11, 'Regular', 1, 16, 'f7fhbrcfdc67olvpcilu9ecrpu', '2025-12-18 22:28:14', '2025-12-18 21:43:14', 'expired'),
(283, 11, 'Gold', 3, 16, 'f7fhbrcfdc67olvpcilu9ecrpu', '2025-12-18 22:28:15', '2025-12-18 21:59:01', 'confirmed'),
(284, 11, 'Regular', 1, 16, 'f7fhbrcfdc67olvpcilu9ecrpu', '2025-12-18 22:28:15', '2025-12-18 21:59:01', 'confirmed'),
(285, 11, 'Premium', 1, NULL, 'f1mbohamv093s1kn8od4o0lj2u', '2025-12-18 23:26:12', '2025-12-18 22:41:12', 'expired'),
(286, 11, 'Premium', 1, NULL, 'f1mbohamv093s1kn8od4o0lj2u', '2025-12-18 23:26:14', '2025-12-18 22:41:14', 'expired'),
(287, 17, 'Cat2', 1, NULL, 'f1mbohamv093s1kn8od4o0lj2u', '2025-12-18 23:27:11', '2025-12-18 22:42:11', 'expired'),
(288, 17, 'Cat2', 1, NULL, 'f1mbohamv093s1kn8od4o0lj2u', '2025-12-18 23:27:17', '2025-12-18 22:42:17', 'expired'),
(289, 15, 'Regular', 1, NULL, 'f1mbohamv093s1kn8od4o0lj2u', '2025-12-18 23:28:33', '2025-12-18 22:43:33', 'expired'),
(290, 15, 'Premium', 1, NULL, 'f1mbohamv093s1kn8od4o0lj2u', '2025-12-18 23:28:33', '2025-12-18 22:43:33', 'expired'),
(291, 15, 'Premium', 1, NULL, 'f1mbohamv093s1kn8od4o0lj2u', '2025-12-18 23:28:35', '2025-12-18 22:43:35', 'expired'),
(292, 15, 'Regular', 1, NULL, 'f1mbohamv093s1kn8od4o0lj2u', '2025-12-18 23:28:35', '2025-12-18 22:43:35', 'expired'),
(293, 15, 'Gold', 3, NULL, 'e8fvq4lfgljiuhjhfamngrh0j6', '2025-12-18 23:30:29', '2025-12-18 22:45:29', 'expired'),
(294, 15, 'Gold', 3, NULL, 'e8fvq4lfgljiuhjhfamngrh0j6', '2025-12-18 23:30:30', '2025-12-18 22:45:30', 'expired'),
(295, 15, 'Regular', 3, 20, 'e8fvq4lfgljiuhjhfamngrh0j6', '2025-12-18 23:31:17', '2025-12-18 22:46:17', 'expired'),
(296, 15, 'Regular', 3, 20, 'e8fvq4lfgljiuhjhfamngrh0j6', '2025-12-18 23:31:20', '2025-12-18 23:01:20', 'confirmed'),
(297, 12, 'Cat2', 2, 16, 'gf1qs6frc996l5pcqjvheljrq0', '2025-12-19 13:48:46', '2025-12-19 13:03:46', 'expired'),
(298, 12, 'Cat2', 2, 16, 'gf1qs6frc996l5pcqjvheljrq0', '2025-12-19 13:48:49', '2025-12-19 13:19:10', 'confirmed'),
(299, 15, 'Premium', 3, NULL, 'adar4u18krqnq4kfib2tsajhhf', '2025-12-20 18:46:31', '2025-12-20 18:01:31', 'expired'),
(300, 15, 'Premium', 3, NULL, 'adar4u18krqnq4kfib2tsajhhf', '2025-12-20 18:46:32', '2025-12-20 18:01:32', 'expired'),
(301, 12, 'Cat2', 1, 16, 'adar4u18krqnq4kfib2tsajhhf', '2025-12-20 18:48:15', '2025-12-20 18:03:15', 'expired'),
(302, 12, 'Cat1', 1, 16, 'adar4u18krqnq4kfib2tsajhhf', '2025-12-20 18:48:15', '2025-12-20 18:03:15', 'expired'),
(303, 12, 'Cat1', 1, 16, 'adar4u18krqnq4kfib2tsajhhf', '2025-12-20 18:48:16', '2025-12-20 18:19:01', 'confirmed'),
(304, 12, 'Cat2', 1, 16, 'adar4u18krqnq4kfib2tsajhhf', '2025-12-20 18:48:16', '2025-12-20 18:19:01', 'confirmed'),
(305, 9, 'Regular', 2, 16, 'vlja15fg9bu2nrlfpgg2p5nlsc', '2025-12-20 18:50:57', '2025-12-20 18:05:57', 'expired'),
(306, 9, 'Golden Circle', 1, 16, 'vlja15fg9bu2nrlfpgg2p5nlsc', '2025-12-20 18:50:57', '2025-12-20 18:05:57', 'expired'),
(307, 9, 'Fanpit', 1, 16, 'vlja15fg9bu2nrlfpgg2p5nlsc', '2025-12-20 18:50:57', '2025-12-20 18:05:57', 'expired'),
(308, 9, 'Golden Circle', 1, 16, 'vlja15fg9bu2nrlfpgg2p5nlsc', '2025-12-20 18:50:58', '2025-12-20 18:22:40', 'expired'),
(309, 9, 'Fanpit', 1, 16, 'vlja15fg9bu2nrlfpgg2p5nlsc', '2025-12-20 18:50:58', '2025-12-20 18:22:40', 'expired'),
(310, 9, 'Regular', 2, 16, 'vlja15fg9bu2nrlfpgg2p5nlsc', '2025-12-20 18:50:58', '2025-12-20 18:22:40', 'expired'),
(311, 15, 'Regular', 3, 16, 'd2776d8u6p7vp6crg1gqhithj8', '2025-12-22 13:35:20', '2025-12-22 12:50:20', 'expired'),
(312, 15, 'Regular', 3, 16, 'd2776d8u6p7vp6crg1gqhithj8', '2025-12-22 13:35:21', '2025-12-22 13:05:21', 'confirmed');

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int(11) UNSIGNED NOT NULL,
  `first_name` varchar(50) NOT NULL,
  `last_name` varchar(50) NOT NULL,
  `phone_number` varchar(20) DEFAULT NULL,
  `address` varchar(255) DEFAULT NULL,
  `city` varchar(100) DEFAULT NULL,
  `preferred_team` varchar(100) DEFAULT NULL,
  `profile_image_path` varchar(255) DEFAULT NULL,
  `last_login` datetime DEFAULT NULL,
  `email` varchar(100) NOT NULL,
  `password_hash` varchar(255) NOT NULL,
  `created_at` datetime DEFAULT current_timestamp(),
  `is_admin` tinyint(1) DEFAULT 0,
  `country` varchar(120) DEFAULT NULL,
  `state` varchar(120) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `first_name`, `last_name`, `phone_number`, `address`, `city`, `preferred_team`, `profile_image_path`, `last_login`, `email`, `password_hash`, `created_at`, `is_admin`, `country`, `state`) VALUES
(1, 'adham', 'wael', '01012344763', '82y3hebbd', 'cairo', 'Al Ahly', NULL, NULL, 'do.wael67@gmail.com', '$2y$10$d1lzZso6KA2FwxsbtMJIn.UIwWErpbakcAI5rFjGFFv3uCU4U7A0C', '2025-10-21 14:01:56', 0, NULL, NULL),
(2, 'mahmoud', 'shalaby', '010122300033', 'hsydbyednejd', 'cairo', 'Zamalek', NULL, NULL, 'mahmoud.22@gmail.com', '$2y$10$CxPq2S873MvcPorqf2kpge9MTnTezCsCexzPjEPc0JjaCLNVO1Lvu', '2025-10-21 14:05:01', 0, NULL, NULL),
(4, 'mahmoud', 'shalaby', '010122300033', 'hsydbyednejd', 'cairo', 'Zamalek', 'uploads/profile_pics/prof_68f76afade590.jpg', NULL, 'mahmoud.12@gmail.com', '$2y$10$90q2FUYydNMWz2RQ9KXJyebME66svluNTyfUQPnsvnFrPwbxhbgjS', '2025-10-21 14:14:03', 0, NULL, NULL),
(5, 'adham', 'wael', '01012344763', '82y3hebbd', 'cairo', NULL, NULL, NULL, 'do.wael66@gmail.com', '$2y$10$OAMgX8KuB3o5a3HyAbieUexdORtQz0yVlaKaLZejnP8yzfTLVihza', '2025-10-21 14:37:06', 0, NULL, NULL),
(7, 'layla', 'tamer', '012929293', 'hvdevdye', 'cairo', 'Al Ahly', NULL, NULL, 'layla11@gmail.com', '$2y$10$IiHUFrXfDZnBVTwJlfiWR.q8IVjQArgcI7uycvYvG3HcIQALYBdGS', '2025-10-21 14:59:31', 0, NULL, NULL),
(8, 'adham', 'wael', '01012344763', '82y3hebbd', 'cairo', 'Al Ahly', NULL, NULL, 'do.wael57@gmail.com', '$2y$10$ceC/uhyuIimmft3CWuvu5uh5.NbRi9rRzun3EMkxkINojOWmh5nL2', '2025-10-21 15:06:40', 0, NULL, NULL),
(9, 'marly', 'hossam', '01026777229', '23 abo baker el sedeek', 'cairo', 'Other', NULL, NULL, 'marly.hossam@gmail.com', '$2y$10$zG/b.9gLiZMyfE3mPP68l.jX7oWpIPTTapGZJEyAQn0R.NrGzOb4C', '2025-10-21 15:27:53', 0, NULL, NULL),
(10, 'main', 'main', '010123344763', '23 abo baker el sedeek', 'cairo', 'Al Ahly', 'uploads/profile_pics/user_68f79d6acdfca.jpg', NULL, 'adham@miu.com', '$2y$10$f.6DiCu2oi18BixyI6Rtmu.BozEzVHd2hiAMMbqhtNg5QgRdB8JnW', '2025-10-21 17:49:14', 0, NULL, NULL),
(11, 'adham', 'wael', '01211111111', '82y3hebbd', 'cairo', 'Al Ahly', NULL, NULL, 'do.wael21@gmail.com', '$2y$10$LviW3pK3t8b.xCPtJvAi0uQEU8w6A8sEV8Ksty9iIZlOg19owB3gq', '2025-10-21 18:07:30', 0, NULL, NULL),
(12, 'adham', 'wael', '01212111111', '82y3hebbd', 'cairo', 'Al Ahly', NULL, NULL, 'do.wael22@gmail.com', '$2y$10$w.84O/3N/sTep0hzFy3f2u0mdcALN97opY58WDgOv7E4yKGnPIHZi', '2025-10-21 18:19:25', 0, NULL, NULL),
(13, 'layla', 'tamer', '01223696966', 'blaaaa', 'cairo', 'Al Ahly', NULL, NULL, 'layla@gmail.com', '$2y$10$8MAVd3p19O7j3V/fIHv7gea5VtrMNyVxBAaLZa6N/fz8XQX7k485O', '2025-10-23 00:44:38', 0, NULL, NULL),
(14, 'test', 'test', '01026777229', '23 abo baker el sedeek', 'cairo', NULL, NULL, NULL, 'ali_test@gmail.com', '$2y$10$IFLf5BbGCmPdhZBMM8aqq.B/nTWIh2ii.PSxaTMGsA.fM.N8hDPUK', '2025-12-11 19:24:56', 0, NULL, NULL),
(16, 'Reemadel', '', NULL, NULL, NULL, NULL, NULL, '2025-12-22 15:33:36', 'reemadel@gmail.com', '$2y$10$Ca.J1HnzJ9m8nM2pwsKMPO5wzHFMoYBmepy1KafQ5SRD7pg/lCBva', '2025-12-14 22:04:56', 1, NULL, NULL),
(17, 'Pansyadel', '', NULL, NULL, NULL, NULL, NULL, NULL, 'pansyadel@gmail.com', '$2y$10$eDT8e6YsALT9vJis2Sk9Mu6WqaABAwyq62d/jVUspuKkVSfoBiDPS', '2025-12-15 17:38:46', 1, NULL, NULL),
(18, 'Marly2301599', '', NULL, NULL, NULL, NULL, NULL, '2025-12-18 14:55:09', 'marly2301599@miuegypt.edu.eg', '$2y$10$JIr6BK.s/kNvOgHQFUrz/u.YGfV6wxoeZ3O3Aw.52aKluVuV59l6u', '2025-12-17 21:59:22', 1, NULL, NULL),
(19, 'Numnum.store.25', '', NULL, NULL, NULL, NULL, NULL, NULL, 'numnum.store.25@gmail.com', '$2y$10$MxFlRqrU568e3kuIOrDZl.DM5L2x69dENDKrsqnX7NJZGUsIw.eXa', '2025-12-18 20:31:29', 1, NULL, NULL),
(20, 'fady', 'adel', '01285493700', 'ammar ibn yasser', 'cairo', 'Al Ahly SC', 'uploads/profile_pics/user_69448e7c586da.png', '2025-12-19 01:30:46', 'fady00221@miuegypt.edu.eg', '$2y$10$GXB.mJiI46ybbBXbwrQe8.yQurnvBcKSdTaDedI.wuzOdteshTYES', '2025-12-19 01:30:04', 0, 'Egypt', 'Al Qāhirah');

-- --------------------------------------------------------

--
-- Table structure for table `venues`
--

CREATE TABLE `venues` (
  `id` int(11) NOT NULL,
  `name` varchar(255) NOT NULL,
  `address` varchar(500) NOT NULL,
  `city` varchar(100) NOT NULL,
  `country` varchar(100) DEFAULT 'Egypt',
  `capacity` int(11) NOT NULL,
  `description` text DEFAULT NULL,
  `facilities` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`facilities`)),
  `google_maps_url` varchar(500) DEFAULT NULL,
  `image_url` varchar(500) DEFAULT NULL,
  `status` enum('active','inactive','under_maintenance') DEFAULT 'active',
  `seating_type` varchar(20) DEFAULT NULL COMMENT 'stadium, theatre, or standing',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `venues`
--

INSERT INTO `venues` (`id`, `name`, `address`, `city`, `country`, `capacity`, `description`, `facilities`, `google_maps_url`, `image_url`, `status`, `seating_type`, `created_at`, `updated_at`) VALUES
(2, 'Cairo Stadium', 'Nasr city', 'cairo', 'Egypt', 242, 'stadium with left middle and right sittings', '[\"Parking\",\"Restrooms\",\"Accessibility\",\"Lighting\",\"Sound System\",\"Security\",\"First Aid\"]', 'https://share.google/HRn0cvpoGmbCwh9wx', 'https://lh3.googleusercontent.com/gps-cs-s/AG0ilSzc_LdtKywxVtf3Wlru0drnCyzFsQL_IjRAO8NnX35psrhGmTsLuldSingAbmYFrxd7dAFwePnlo6IGdxicVcLIte6tYxTQ3KzZk20I6VXhd5QibP2fqC3Q9dC1XjUpXbQv2LZsAA=s1360-w1360-h1020-rw', 'active', 'stadium', '2025-12-09 10:19:16', '2025-12-17 04:49:24'),
(3, 'Cairo Opera House', 'southern portion of Gezira Island in the Nile River, in the Zamalek', 'cairo', 'Egypt', 120, 'opera venue for comedy standup', '[\"WiFi\",\"AC\",\"Restrooms\"]', 'https://share.google/lYo5g6UhauP2UdbEb', 'https://lh3.googleusercontent.com/gps-cs-s/AG0ilSzFJ9F48sJfW1YXL7rc1Is8vKYMcx2oi_c5ZGhA2MD1RXR8nW2CaW3RbM9VTZtcjwF8lLXXRZUg3jeuTgqAIQzpfj6-FjV2IyZ_n-_gewTJIqMq47AYR8CibdkgZgViHhqDlOQNuA=s1360-w1360-h1020-rw', 'active', 'theatre', '2025-12-09 10:23:08', '2025-12-17 04:48:41'),
(6, 'Misr Stadium (New Administrative Capital Stadium)', 'new Capital', 'cairo', 'Egypt', 242, 'test', '[\"WiFi\",\"Restrooms\",\"Security\",\"First Aid\"]', 'https://share.google/N2br4nXibik78At99', 'https://lh3.googleusercontent.com/gps-cs-s/AG0ilSwnXTrQ7ze9lTvhFSOr6G1zFmiXtysjCBnUisGZcpnn6ZnoURS_4iPdSxARm3K-hLMYuIRcVBtotHawuC2MSDGnGzTeIR0aaQAwYAw3i4hVKMhXDCI9uK7PFjssDL_9MIIi3oiK=s1360-w1360-h1020-rw', 'active', 'stadium', '2025-12-09 13:25:29', '2025-12-17 04:50:15'),
(8, 'Arena El malahy', 'fifth settelment', 'New cairo', 'Egypt', 1000, 'A wide area for concerts', '[\"Parking\",\"Restrooms\",\"Accessibility\",\"Food & Drinks\",\"Stage\",\"Lighting\",\"Sound System\",\"Projector\",\"Security\",\"First Aid\"]', 'https://maps.app.goo.gl/ZwTdLCuwULYLopNz7', 'uploads/venues/venue_6940488f55942_1765820559.jpg', 'active', 'standing', '2025-12-15 17:42:39', '2025-12-17 04:49:03'),
(9, 'Al ahly stadium', 'fifth settelment', 'New cairo', 'Egypt', 242, 'Test', '[\"Parking\",\"Restrooms\",\"Accessibility\",\"Lighting\",\"Sound System\",\"Security\",\"First Aid\"]', 'https://maps.app.goo.gl/mXEuvEH2M4fkS8Dw5', 'uploads/venues/venue_69406d6bd375a_1765829995.png', 'active', 'stadium', '2025-12-15 20:19:55', '2025-12-15 21:03:26'),
(13, 'Scene Cinemas', 'District 5 ', 'New cairo', 'Egypt', 120, 'Enjoy an unforgettable experience with our high end screens in scene cinemas', '[\"Parking\",\"AC\",\"Heating\",\"Restrooms\",\"Accessibility\",\"Food & Drinks\",\"Stage\",\"Lighting\",\"Sound System\",\"Projector\",\"Security\",\"First Aid\",\"Elevator\"]', 'https://maps.app.goo.gl/mXEuvEH2M4fkS8Dw5', 'uploads/venues/venue_694240ce4e651_1765949646.png', 'active', 'theatre', '2025-12-17 05:34:06', '2025-12-17 05:34:06'),
(14, 'U arena', 'Alamein ', 'North coast', 'Egypt', 1000, 'For ultra premium concerts and unforgetable nights', '[\"Parking\",\"Restrooms\",\"Food & Drinks\",\"Stage\",\"Lighting\",\"Sound System\",\"Projector\",\"Security\",\"First Aid\"]', 'https://maps.app.goo.gl/eAp72b7MPvNfQqSH6', 'uploads/venues/venue_69424597c47f1_1765950871.jpg', 'active', 'standing', '2025-12-17 05:54:31', '2025-12-17 05:54:31'),
(15, 'Hassan Mostafa sports hall', '6th october', 'Giza', 'Egypt', 242, 'A wide area for basketball courts ', '[\"Parking\",\"AC\",\"Restrooms\",\"Accessibility\",\"Lighting\",\"Sound System\",\"Dressing Rooms\",\"Security\",\"First Aid\",\"Wheelchair Access\"]', 'https://maps.app.goo.gl/FH7zVWir7gcjFV2k9', 'uploads/venues/venue_6942decb911cd_1765990091.png', 'active', 'stadium', '2025-12-17 16:48:11', '2025-12-17 16:48:11');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `booked_seats`
--
ALTER TABLE `booked_seats`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unique_seat_booking` (`event_id`,`seat_id`),
  ADD KEY `idx_booking_id` (`booking_id`),
  ADD KEY `idx_event_id` (`event_id`),
  ADD KEY `idx_seat_id` (`seat_id`);

--
-- Indexes for table `bookings`
--
ALTER TABLE `bookings`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `booking_code` (`booking_code`),
  ADD KEY `idx_booking_code` (`booking_code`),
  ADD KEY `idx_user_id` (`user_id`),
  ADD KEY `idx_event_id` (`event_id`),
  ADD KEY `idx_status` (`status`),
  ADD KEY `idx_payment_status` (`payment_status`),
  ADD KEY `idx_created_at` (`created_at`);

--
-- Indexes for table `chatbot_conversations`
--
ALTER TABLE `chatbot_conversations`
  ADD PRIMARY KEY (`conversation_id`),
  ADD KEY `user_id` (`user_id`),
  ADD KEY `session_id` (`session_id`),
  ADD KEY `status` (`status`);

--
-- Indexes for table `chatbot_knowledge`
--
ALTER TABLE `chatbot_knowledge`
  ADD PRIMARY KEY (`kb_id`),
  ADD KEY `category` (`category`),
  ADD KEY `is_active` (`is_active`);
ALTER TABLE `chatbot_knowledge` ADD FULLTEXT KEY `ft_question_answer` (`question`,`answer`);

--
-- Indexes for table `chatbot_messages`
--
ALTER TABLE `chatbot_messages`
  ADD PRIMARY KEY (`message_id`),
  ADD KEY `conversation_id` (`conversation_id`),
  ADD KEY `created_at` (`created_at`);

--
-- Indexes for table `chatbot_training`
--
ALTER TABLE `chatbot_training`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `contact_messages`
--
ALTER TABLE `contact_messages`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `events`
--
ALTER TABLE `events`
  ADD PRIMARY KEY (`id`),
  ADD KEY `subcategory_id` (`subcategory_id`),
  ADD KEY `venue_id` (`venue_id`);

--
-- Indexes for table `event_ticket_categories`
--
ALTER TABLE `event_ticket_categories`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_event_id` (`event_id`),
  ADD KEY `idx_category_name` (`category_name`);

--
-- Indexes for table `main_categories`
--
ALTER TABLE `main_categories`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `subcategories`
--
ALTER TABLE `subcategories`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_main_category` (`main_category_id`);

--
-- Indexes for table `tickets`
--
ALTER TABLE `tickets`
  ADD PRIMARY KEY (`id`),
  ADD KEY `event_id` (`event_id`);

--
-- Indexes for table `ticket_customizations`
--
ALTER TABLE `ticket_customizations`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`),
  ADD KEY `event_id` (`event_id`);

--
-- Indexes for table `ticket_reservations`
--
ALTER TABLE `ticket_reservations`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_event_category` (`event_id`,`category_name`),
  ADD KEY `idx_expires_at` (`expires_at`),
  ADD KEY `idx_status` (`status`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `email` (`email`);

--
-- Indexes for table `venues`
--
ALTER TABLE `venues`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_status` (`status`),
  ADD KEY `idx_city` (`city`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `booked_seats`
--
ALTER TABLE `booked_seats`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=18;

--
-- AUTO_INCREMENT for table `bookings`
--
ALTER TABLE `bookings`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=17;

--
-- AUTO_INCREMENT for table `chatbot_conversations`
--
ALTER TABLE `chatbot_conversations`
  MODIFY `conversation_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `chatbot_knowledge`
--
ALTER TABLE `chatbot_knowledge`
  MODIFY `kb_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=21;

--
-- AUTO_INCREMENT for table `chatbot_messages`
--
ALTER TABLE `chatbot_messages`
  MODIFY `message_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `chatbot_training`
--
ALTER TABLE `chatbot_training`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=33;

--
-- AUTO_INCREMENT for table `contact_messages`
--
ALTER TABLE `contact_messages`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `events`
--
ALTER TABLE `events`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=18;

--
-- AUTO_INCREMENT for table `event_ticket_categories`
--
ALTER TABLE `event_ticket_categories`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=56;

--
-- AUTO_INCREMENT for table `main_categories`
--
ALTER TABLE `main_categories`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT for table `subcategories`
--
ALTER TABLE `subcategories`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=15;

--
-- AUTO_INCREMENT for table `tickets`
--
ALTER TABLE `tickets`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `ticket_customizations`
--
ALTER TABLE `ticket_customizations`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=20;

--
-- AUTO_INCREMENT for table `ticket_reservations`
--
ALTER TABLE `ticket_reservations`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=313;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=21;

--
-- AUTO_INCREMENT for table `venues`
--
ALTER TABLE `venues`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=16;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `booked_seats`
--
ALTER TABLE `booked_seats`
  ADD CONSTRAINT `booked_seats_ibfk_1` FOREIGN KEY (`booking_id`) REFERENCES `bookings` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `booked_seats_ibfk_2` FOREIGN KEY (`event_id`) REFERENCES `events` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `bookings`
--
ALTER TABLE `bookings`
  ADD CONSTRAINT `bookings_ibfk_1` FOREIGN KEY (`event_id`) REFERENCES `events` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `chatbot_conversations`
--
ALTER TABLE `chatbot_conversations`
  ADD CONSTRAINT `chatbot_conversations_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `events`
--
ALTER TABLE `events`
  ADD CONSTRAINT `events_ibfk_1` FOREIGN KEY (`subcategory_id`) REFERENCES `subcategories` (`id`),
  ADD CONSTRAINT `events_ibfk_2` FOREIGN KEY (`venue_id`) REFERENCES `venues` (`id`);

--
-- Constraints for table `event_ticket_categories`
--
ALTER TABLE `event_ticket_categories`
  ADD CONSTRAINT `event_ticket_categories_ibfk_1` FOREIGN KEY (`event_id`) REFERENCES `events` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `subcategories`
--
ALTER TABLE `subcategories`
  ADD CONSTRAINT `subcategories_ibfk_1` FOREIGN KEY (`main_category_id`) REFERENCES `main_categories` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `tickets`
--
ALTER TABLE `tickets`
  ADD CONSTRAINT `tickets_ibfk_1` FOREIGN KEY (`event_id`) REFERENCES `events` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `ticket_reservations`
--
ALTER TABLE `ticket_reservations`
  ADD CONSTRAINT `ticket_reservations_ibfk_1` FOREIGN KEY (`event_id`) REFERENCES `events` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;

-- Migration: Create booked_seats table
-- This table stores which specific seats are booked for each booking

CREATE TABLE IF NOT EXISTS booked_seats (
    id INT PRIMARY KEY AUTO_INCREMENT,
    booking_id INT NOT NULL,
    event_id INT NOT NULL,
    seat_id VARCHAR(50) NOT NULL,
    category_name VARCHAR(50) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (booking_id) REFERENCES bookings(id) ON DELETE CASCADE,
    FOREIGN KEY (event_id) REFERENCES events(id) ON DELETE CASCADE,
    INDEX idx_booking_id (booking_id),
    INDEX idx_event_id (event_id),
    INDEX idx_seat_id (seat_id),
    UNIQUE KEY unique_event_seat (event_id, seat_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


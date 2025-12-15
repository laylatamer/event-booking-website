-- Migration: Add Seating System
-- This migration adds seating type to venues and creates tables for ticket categories and reservations

-- Step 1: Add seating_type column to venues table (only if it doesn't exist)
SET @dbname = DATABASE();
SET @tablename = "venues";
SET @columnname = "seating_type";
SET @preparedStatement = (SELECT IF(
  (
    SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS
    WHERE
      (table_name = @tablename)
      AND (table_schema = @dbname)
      AND (column_name = @columnname)
  ) > 0,
  "SELECT 'Column already exists.'",
  CONCAT("ALTER TABLE ", @tablename, " ADD COLUMN ", @columnname, " VARCHAR(20) NULL COMMENT 'stadium, theatre, or standing' AFTER status")
));
PREPARE alterIfNotExists FROM @preparedStatement;
EXECUTE alterIfNotExists;
DEALLOCATE PREPARE alterIfNotExists;

-- Step 2: Create event_ticket_categories table (only if it doesn't exist)
CREATE TABLE IF NOT EXISTS event_ticket_categories (
    id INT PRIMARY KEY AUTO_INCREMENT,
    event_id INT NOT NULL,
    category_name VARCHAR(50) NOT NULL,
    total_tickets INT NOT NULL DEFAULT 0,
    available_tickets INT NOT NULL DEFAULT 0,
    price DECIMAL(10, 2) NOT NULL DEFAULT 0.00,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (event_id) REFERENCES events(id) ON DELETE CASCADE,
    INDEX idx_event_id (event_id),
    INDEX idx_category_name (category_name)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Step 3: Create ticket_reservations table for 15-minute holds (only if it doesn't exist)
CREATE TABLE IF NOT EXISTS ticket_reservations (
    id INT PRIMARY KEY AUTO_INCREMENT,
    event_id INT NOT NULL,
    category_name VARCHAR(50) NOT NULL,
    quantity INT NOT NULL DEFAULT 1,
    user_id INT NULL,
    session_id VARCHAR(255) NULL,
    reserved_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    expires_at TIMESTAMP NULL,
    status ENUM('reserved', 'confirmed', 'expired') DEFAULT 'reserved',
    FOREIGN KEY (event_id) REFERENCES events(id) ON DELETE CASCADE,
    INDEX idx_event_category (event_id, category_name),
    INDEX idx_expires_at (expires_at),
    INDEX idx_status (status)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


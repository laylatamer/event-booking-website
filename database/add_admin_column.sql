-- Add is_admin column to users table
-- This column marks users as administrators (1 = admin, 0 = regular user)
-- 
-- NOTE: The code automatically adds this column when needed, so you don't need to run this manually.
-- This SQL is provided for reference or if you prefer to add it manually.

-- Check if column exists first (for MySQL 5.7+)
-- For older MySQL versions, just run the ALTER TABLE command directly

ALTER TABLE `users` 
ADD COLUMN `is_admin` TINYINT(1) DEFAULT 0;

-- Optional: Add index for faster admin queries
CREATE INDEX `idx_is_admin` ON `users` (`is_admin`);

-- To verify the column was added, run:
-- DESCRIBE `users`;
-- or
-- SHOW COLUMNS FROM `users` LIKE 'is_admin';


-- Add profile_image_path column to users table if it doesn't exist
-- This allows users to upload and store profile pictures

ALTER TABLE `users` 
ADD COLUMN IF NOT EXISTS `profile_image_path` VARCHAR(255) NULL DEFAULT NULL 
AFTER `preferred_team`;

-- Add index for faster lookups (optional but recommended)
CREATE INDEX IF NOT EXISTS `idx_users_profile_image` ON `users`(`profile_image_path`);


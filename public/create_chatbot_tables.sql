-- Create chatbot tables if they don't exist
-- This ensures the chatbot has the correct table structure

-- Chatbot Conversations Table
CREATE TABLE IF NOT EXISTS `chatbot_conversations` (
  `id` INT(11) NOT NULL AUTO_INCREMENT,
  `user_id` INT(11) DEFAULT NULL,
  `session_id` VARCHAR(255) NOT NULL,
  `user_ip` VARCHAR(45) DEFAULT NULL,
  `user_agent` TEXT DEFAULT NULL,
  `status` VARCHAR(20) DEFAULT 'active',
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_session_id` (`session_id`),
  KEY `idx_user_id` (`user_id`),
  KEY `idx_status` (`status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Chatbot Messages Table
CREATE TABLE IF NOT EXISTS `chatbot_messages` (
  `id` INT(11) NOT NULL AUTO_INCREMENT,
  `conversation_id` INT(11) DEFAULT NULL,
  `message_type` VARCHAR(20) NOT NULL COMMENT 'user or bot',
  `message_text` TEXT NOT NULL,
  `intent` VARCHAR(100) DEFAULT NULL,
  `confidence` DECIMAL(5,2) DEFAULT NULL,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_conversation_id` (`conversation_id`),
  KEY `idx_message_type` (`message_type`),
  CONSTRAINT `fk_chatbot_messages_conversation` FOREIGN KEY (`conversation_id`) 
    REFERENCES `chatbot_conversations` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Chatbot Training Data Table
CREATE TABLE IF NOT EXISTS `chatbot_training` (
  `id` INT(11) NOT NULL AUTO_INCREMENT,
  `user_input` TEXT NOT NULL,
  `correct_response` TEXT NOT NULL,
  `keywords` TEXT DEFAULT NULL,
  `intent` VARCHAR(100) DEFAULT NULL,
  `is_active` TINYINT(1) DEFAULT 1,
  `use_count` INT(11) DEFAULT 0,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_is_active` (`is_active`),
  KEY `idx_intent` (`intent`),
  FULLTEXT KEY `idx_user_input` (`user_input`),
  FULLTEXT KEY `idx_keywords` (`keywords`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


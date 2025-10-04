-- WP GPT RAG Chat - Analytics Tables Creation Script
-- Run this SQL script in your WordPress database to create the missing tables

-- Create Error Logs Table
CREATE TABLE IF NOT EXISTS `wp_wp_gpt_rag_chat_errors` (
    `id` bigint(20) NOT NULL AUTO_INCREMENT,
    `error_type` varchar(50) NOT NULL,
    `api_service` varchar(50) NOT NULL,
    `error_message` text NOT NULL,
    `context` longtext DEFAULT NULL,
    `user_id` bigint(20) DEFAULT NULL,
    `ip_address` varchar(45) DEFAULT NULL,
    `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    KEY `error_type` (`error_type`),
    KEY `api_service` (`api_service`),
    KEY `created_at` (`created_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Create API Usage Tracking Table
CREATE TABLE IF NOT EXISTS `wp_wp_gpt_rag_chat_api_usage` (
    `id` bigint(20) NOT NULL AUTO_INCREMENT,
    `api_service` varchar(50) NOT NULL,
    `endpoint` varchar(100) NOT NULL,
    `tokens_used` int(11) DEFAULT NULL,
    `cost` decimal(10,4) DEFAULT NULL,
    `context` longtext DEFAULT NULL,
    `user_id` bigint(20) DEFAULT NULL,
    `ip_address` varchar(45) DEFAULT NULL,
    `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    KEY `api_service` (`api_service`),
    KEY `endpoint` (`endpoint`),
    KEY `created_at` (`created_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Update the database version option
UPDATE `wp_options` 
SET `option_value` = '2.2.0' 
WHERE `option_name` = 'wp_gpt_rag_chat_db_version';

-- If the option doesn't exist, create it
INSERT IGNORE INTO `wp_options` (`option_name`, `option_value`, `autoload`) 
VALUES ('wp_gpt_rag_chat_db_version', '2.2.0', 'yes');


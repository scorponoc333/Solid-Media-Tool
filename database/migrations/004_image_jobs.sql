-- Migration 004: Image Jobs (async image generation tracking)
-- Created: 2026-04-13

CREATE TABLE IF NOT EXISTS `image_jobs` (
    `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `client_id` INT UNSIGNED NOT NULL DEFAULT 1,
    `uid` VARCHAR(50) NOT NULL COMMENT 'Client-side card UID',
    `kie_task_id` VARCHAR(100) COMMENT 'Kie.ai task ID',
    `prompt` TEXT NOT NULL,
    `status` ENUM('pending','processing','completed','failed') DEFAULT 'pending',
    `image_url` VARCHAR(500) COMMENT 'Final image URL after completion',
    `error_message` TEXT,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX(`client_id`),
    INDEX(`uid`),
    INDEX(`status`),
    INDEX(`kie_task_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

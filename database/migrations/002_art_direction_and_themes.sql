-- ============================================================
-- Migration 002: Art Direction, Content Themes, and Scheduling
-- Database: solidtech_social
-- Created: 2026-04-13
-- ============================================================

USE solidtech_social;

-- ============================================================
-- Table: art_direction_settings
-- Controls AI image generation style per client
-- ============================================================
CREATE TABLE IF NOT EXISTS art_direction_settings (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  client_id INT UNSIGNED NOT NULL,
  image_style VARCHAR(50) NOT NULL DEFAULT 'photorealistic',
  realism_level TINYINT UNSIGNED NOT NULL DEFAULT 8,
  color_temperature VARCHAR(20) NOT NULL DEFAULT 'cold',
  contrast_level VARCHAR(20) NOT NULL DEFAULT 'punchy',
  mood VARCHAR(30) NOT NULL DEFAULT 'professional',
  brand_color_bleed TINYINT UNSIGNED NOT NULL DEFAULT 25,
  illustration_limit VARCHAR(30) NOT NULL DEFAULT 'max_1_per_week',
  avoid_list TEXT DEFAULT NULL,
  watermark_enabled TINYINT(1) NOT NULL DEFAULT 1,
  watermark_website VARCHAR(255) DEFAULT NULL,
  watermark_logo_position VARCHAR(20) NOT NULL DEFAULT 'bottom-left',
  watermark_gradient_opacity TINYINT UNSIGNED NOT NULL DEFAULT 85,
  created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

  UNIQUE INDEX idx_art_direction_client_id (client_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================
-- Table: content_themes
-- Reusable content themes assigned to days of the week
-- ============================================================
CREATE TABLE IF NOT EXISTS content_themes (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  client_id INT UNSIGNED NOT NULL,
  name VARCHAR(100) NOT NULL,
  description TEXT DEFAULT NULL,
  copy_instructions TEXT DEFAULT NULL,
  required_elements JSON DEFAULT NULL,
  default_hashtags TEXT DEFAULT NULL,
  image_style_override VARCHAR(50) NOT NULL DEFAULT 'global',
  sort_order INT NOT NULL DEFAULT 0,
  is_active TINYINT(1) NOT NULL DEFAULT 1,
  created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

  INDEX idx_content_themes_client_id (client_id),
  INDEX idx_content_themes_active (client_id, is_active)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================
-- Table: theme_samples
-- Example posts linked to a theme for AI to mimic
-- ============================================================
CREATE TABLE IF NOT EXISTS theme_samples (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  theme_id INT UNSIGNED NOT NULL,
  sample_content TEXT NOT NULL,
  sort_order INT NOT NULL DEFAULT 0,
  created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,

  INDEX idx_theme_samples_theme_id (theme_id),

  CONSTRAINT fk_theme_samples_theme
    FOREIGN KEY (theme_id) REFERENCES content_themes(id)
    ON DELETE CASCADE
    ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================
-- Table: theme_schedule
-- Maps day-of-week to a theme per client
-- ============================================================
CREATE TABLE IF NOT EXISTS theme_schedule (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  client_id INT UNSIGNED NOT NULL,
  day_of_week TINYINT UNSIGNED NOT NULL COMMENT '0=Sunday, 1=Monday, ..., 6=Saturday',
  theme_id INT UNSIGNED DEFAULT NULL,
  created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

  UNIQUE INDEX idx_theme_schedule_client_day (client_id, day_of_week),

  CONSTRAINT fk_theme_schedule_theme
    FOREIGN KEY (theme_id) REFERENCES content_themes(id)
    ON DELETE SET NULL
    ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================
-- Seed: Default art direction settings for client 1
-- ============================================================
INSERT INTO art_direction_settings (client_id, image_style, realism_level, color_temperature, contrast_level, mood, brand_color_bleed, illustration_limit, avoid_list)
VALUES (
  1,
  'photorealistic',
  8,
  'cold',
  'punchy',
  'professional',
  25,
  'max_1_per_week',
  'cartoon, childish, playful, 3D render, Pixar-style, classroom, playground, clip-art, watercolor, anime, comic, doodle, crayon, sketch, hand-drawn, coloring book'
) ON DUPLICATE KEY UPDATE client_id = client_id;

-- ============================================================
-- Seed: Default themes for SolidTech (client 1)
-- ============================================================
INSERT INTO content_themes (client_id, name, description, copy_instructions, required_elements, default_hashtags, sort_order) VALUES
(1, 'Cybersecurity Tips', 'Practical cybersecurity advice, threat awareness, and protection strategies for businesses', 'Keep tone authoritative but accessible. Use real-world examples of breaches or threats. End with an actionable tip the reader can implement today.', '{"phone":false,"website":true,"cta":true,"hashtags":true,"emojis":false}', '#Cybersecurity #InfoSec #ITSecurity #DataProtection #CyberAware', 1),
(1, 'IT Infrastructure', 'Server management, networking, cloud infrastructure, and hardware topics', 'Focus on reliability and uptime. Mention specific technologies when relevant. Position as expert knowledge sharing.', '{"phone":false,"website":true,"cta":true,"hashtags":true,"emojis":false}', '#ITInfrastructure #CloudComputing #Networking #ServerManagement #TechSolutions', 2),
(1, 'Cloud & Digital Transformation', 'Cloud migration, SaaS adoption, digital workflows, and modernization', 'Emphasize business outcomes and ROI. Avoid overly technical jargon. Focus on the transformation journey.', '{"phone":false,"website":true,"cta":true,"hashtags":true,"emojis":false}', '#CloudMigration #DigitalTransformation #SaaS #CloudFirst #ModernIT', 3),
(1, 'Managed Services', 'Why outsource IT, MSP benefits, proactive monitoring, and IT support value', 'Highlight pain points of self-managed IT. Use statistics when possible. Strong CTA toward consultation.', '{"phone":true,"website":true,"cta":true,"hashtags":true,"emojis":false}', '#ManagedServices #MSP #ITSupport #ITOutsourcing #ProactiveIT', 4),
(1, 'Company Culture', 'Behind the scenes, team highlights, company values, and workplace stories', 'Warm and personable tone. Show the human side of the company. Keep it authentic and genuine.', '{"phone":false,"website":false,"cta":false,"hashtags":true,"emojis":true}', '#CompanyCulture #BehindTheScenes #TeamWork #TechLife #OurTeam', 5),
(1, 'Tech News & Trends', 'Industry news commentary, emerging technology, and trend analysis', 'Be timely and relevant. Add your own perspective — don''t just repeat news. Position as thought leader.', '{"phone":false,"website":true,"cta":false,"hashtags":true,"emojis":false}', '#TechNews #TechTrends #Innovation #FutureTech #ITIndustry', 6);

-- ============================================================
-- Seed: Default weekly schedule for SolidTech (Mon/Wed/Fri)
-- ============================================================
INSERT INTO theme_schedule (client_id, day_of_week, theme_id) VALUES
(1, 1, (SELECT id FROM content_themes WHERE client_id = 1 AND name = 'Cybersecurity Tips' LIMIT 1)),
(1, 3, (SELECT id FROM content_themes WHERE client_id = 1 AND name = 'IT Infrastructure' LIMIT 1)),
(1, 5, (SELECT id FROM content_themes WHERE client_id = 1 AND name = 'Managed Services' LIMIT 1))
ON DUPLICATE KEY UPDATE theme_id = VALUES(theme_id);

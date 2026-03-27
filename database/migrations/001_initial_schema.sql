-- ============================================================
-- Migration 001: Initial Schema
-- Database: solidtech_social
-- Created: 2026-03-23
-- ============================================================

CREATE DATABASE IF NOT EXISTS solidtech_social
  CHARACTER SET utf8mb4
  COLLATE utf8mb4_unicode_ci;

USE solidtech_social;

-- ============================================================
-- Table: users
-- ============================================================
CREATE TABLE IF NOT EXISTS users (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  username VARCHAR(50) NOT NULL UNIQUE,
  first_name VARCHAR(100) DEFAULT NULL,
  email VARCHAR(255) NOT NULL UNIQUE,
  password VARCHAR(255) NOT NULL COMMENT 'bcrypt hash',
  role ENUM('admin', 'editor') NOT NULL DEFAULT 'editor',
  avatar_url VARCHAR(500) DEFAULT NULL,
  created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

  INDEX idx_users_role (role),
  INDEX idx_users_email (email)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================
-- Table: posts
-- ============================================================
CREATE TABLE IF NOT EXISTS posts (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  title VARCHAR(255) NOT NULL,
  content TEXT NOT NULL,
  image_url VARCHAR(500) DEFAULT NULL,
  post_type ENUM('educational', 'promotional', 'engagement', 'storytelling', 'behind_the_scenes') NOT NULL DEFAULT 'educational',
  platform ENUM('instagram', 'facebook', 'linkedin', 'twitter', 'all') NOT NULL DEFAULT 'all',
  status ENUM('draft', 'scheduled', 'published', 'failed') NOT NULL DEFAULT 'draft',
  scheduled_at DATETIME DEFAULT NULL,
  zernio_post_id VARCHAR(100) DEFAULT NULL,
  topic VARCHAR(255) DEFAULT NULL,
  keywords TEXT DEFAULT NULL,
  angle VARCHAR(255) DEFAULT NULL,
  content_hash VARCHAR(64) DEFAULT NULL,
  client_id INT UNSIGNED NOT NULL DEFAULT 1,
  created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

  INDEX idx_posts_status (status),
  INDEX idx_posts_platform (platform),
  INDEX idx_posts_post_type (post_type),
  INDEX idx_posts_scheduled_at (scheduled_at),
  INDEX idx_posts_client_id (client_id),
  INDEX idx_posts_content_hash (content_hash),
  INDEX idx_posts_zernio_post_id (zernio_post_id),
  INDEX idx_posts_status_platform (status, platform),
  INDEX idx_posts_client_status (client_id, status)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================
-- Table: branding_settings
-- ============================================================
CREATE TABLE IF NOT EXISTS branding_settings (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  client_id INT UNSIGNED NOT NULL DEFAULT 1,
  logo_url VARCHAR(500) DEFAULT NULL,
  primary_color VARCHAR(20) NOT NULL DEFAULT '#6366f1',
  secondary_color VARCHAR(20) NOT NULL DEFAULT '#8b5cf6',
  login_bg_url VARCHAR(500) DEFAULT NULL,
  particles_enabled TINYINT(1) NOT NULL DEFAULT 1,
  company_name VARCHAR(100) NOT NULL DEFAULT 'SolidTech',
  tagline VARCHAR(255) DEFAULT NULL,
  created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

  UNIQUE INDEX idx_branding_client_id (client_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================
-- Table: content_memory
-- ============================================================
CREATE TABLE IF NOT EXISTS content_memory (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  client_id INT UNSIGNED NOT NULL DEFAULT 1,
  topic VARCHAR(255) DEFAULT NULL,
  keywords TEXT DEFAULT NULL,
  angle VARCHAR(255) DEFAULT NULL,
  content_hash VARCHAR(64) NOT NULL UNIQUE,
  post_id INT UNSIGNED DEFAULT NULL,
  created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,

  INDEX idx_content_memory_client_id (client_id),
  INDEX idx_content_memory_topic (topic),
  INDEX idx_content_memory_post_id (post_id),

  CONSTRAINT fk_content_memory_post
    FOREIGN KEY (post_id) REFERENCES posts(id)
    ON DELETE SET NULL
    ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================
-- Table: clients (COMMENTED OUT - for future multi-tenant use)
-- ============================================================
-- CREATE TABLE IF NOT EXISTS clients (
--   id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
--   name VARCHAR(100) NOT NULL,
--   slug VARCHAR(100) NOT NULL UNIQUE,
--   logo_url VARCHAR(500) DEFAULT NULL,
--   primary_color VARCHAR(20) DEFAULT '#6366f1',
--   is_active TINYINT(1) NOT NULL DEFAULT 1,
--   created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
--   updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
--
--   INDEX idx_clients_slug (slug),
--   INDEX idx_clients_is_active (is_active)
-- ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================
-- Seed Data: Default admin user
-- Password is bcrypt hash of "admin123"
-- ============================================================
INSERT INTO users (username, email, password, role)
VALUES (
  'admin',
  'admin@solidtech.com',
  '$2y$10$G0r2a.9fsAjYPFx1E.Z/LOF7G9zY4y8dpzYn2laWeFtNmnKxXxV9O',
  'admin'
) ON DUPLICATE KEY UPDATE username = username;

-- ============================================================
-- Seed Data: Default branding settings
-- ============================================================
INSERT INTO branding_settings (client_id, company_name, primary_color, secondary_color, particles_enabled)
VALUES (
  1,
  'SolidTech',
  '#6366f1',
  '#8b5cf6',
  1
) ON DUPLICATE KEY UPDATE client_id = client_id;

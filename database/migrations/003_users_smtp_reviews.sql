-- ============================================================
-- Migration 003: User Management, SMTP, Approval Workflow
-- Database: solidtech_social
-- Created: 2026-04-13
-- ============================================================

USE solidtech_social;

-- ============================================================
-- ALTER: users table — add RBAC + onboarding fields
-- ============================================================
ALTER TABLE users
  MODIFY COLUMN role ENUM('admin', 'editor', 'reviewer') NOT NULL DEFAULT 'editor',
  ADD COLUMN must_change_password TINYINT(1) NOT NULL DEFAULT 0 AFTER avatar_url,
  ADD COLUMN has_completed_tour TINYINT(1) NOT NULL DEFAULT 0 AFTER must_change_password,
  ADD COLUMN is_active TINYINT(1) NOT NULL DEFAULT 1 AFTER has_completed_tour,
  ADD COLUMN invited_by INT UNSIGNED DEFAULT NULL AFTER is_active,
  ADD COLUMN last_login_at DATETIME DEFAULT NULL AFTER invited_by,
  ADD COLUMN client_id INT UNSIGNED NOT NULL DEFAULT 1 AFTER last_login_at;

-- Mark existing admin as tour-complete and active
UPDATE users SET has_completed_tour = 1, is_active = 1 WHERE role = 'admin';

-- ============================================================
-- ALTER: posts table — add pending_review status
-- ============================================================
ALTER TABLE posts
  MODIFY COLUMN status ENUM('draft', 'pending_review', 'scheduled', 'published', 'failed') NOT NULL DEFAULT 'draft';

-- ============================================================
-- Table: smtp_settings
-- Email provider configuration per client
-- ============================================================
CREATE TABLE IF NOT EXISTS smtp_settings (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  client_id INT UNSIGNED NOT NULL,
  provider ENUM('smtp', 'sendgrid', 'mailgun') NOT NULL DEFAULT 'smtp',
  smtp_host VARCHAR(255) DEFAULT NULL,
  smtp_port INT DEFAULT 587,
  smtp_user VARCHAR(255) DEFAULT NULL,
  smtp_pass VARCHAR(255) DEFAULT NULL,
  smtp_encryption ENUM('tls', 'ssl', 'none') NOT NULL DEFAULT 'tls',
  from_name VARCHAR(100) DEFAULT NULL,
  from_email VARCHAR(255) DEFAULT NULL,
  sendgrid_api_key VARCHAR(255) DEFAULT NULL,
  mailgun_api_key VARCHAR(255) DEFAULT NULL,
  mailgun_domain VARCHAR(255) DEFAULT NULL,
  is_configured TINYINT(1) NOT NULL DEFAULT 0,
  created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

  UNIQUE INDEX idx_smtp_client_id (client_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================
-- Table: approval_settings
-- Per-client approval workflow configuration
-- ============================================================
CREATE TABLE IF NOT EXISTS approval_settings (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  client_id INT UNSIGNED NOT NULL,
  approval_required TINYINT(1) NOT NULL DEFAULT 0,
  min_approvals INT UNSIGNED NOT NULL DEFAULT 1,
  created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

  UNIQUE INDEX idx_approval_client_id (client_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================
-- Table: post_reviews
-- Individual approval/rejection records
-- ============================================================
CREATE TABLE IF NOT EXISTS post_reviews (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  post_id INT UNSIGNED NOT NULL,
  reviewer_id INT UNSIGNED NOT NULL,
  status ENUM('approved', 'changes_requested') NOT NULL,
  comment TEXT DEFAULT NULL,
  created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,

  INDEX idx_post_reviews_post (post_id),
  INDEX idx_post_reviews_reviewer (reviewer_id),
  UNIQUE INDEX idx_post_reviews_unique (post_id, reviewer_id),

  CONSTRAINT fk_post_reviews_post
    FOREIGN KEY (post_id) REFERENCES posts(id)
    ON DELETE CASCADE,

  CONSTRAINT fk_post_reviews_reviewer
    FOREIGN KEY (reviewer_id) REFERENCES users(id)
    ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================
-- Seed: Default approval settings (disabled)
-- ============================================================
INSERT INTO approval_settings (client_id, approval_required, min_approvals)
VALUES (1, 0, 1)
ON DUPLICATE KEY UPDATE client_id = client_id;

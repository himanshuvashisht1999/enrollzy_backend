-- ==========================================================
-- Latest DB Change SQL (2026-07-24)
-- Community Category Image column addition
-- ==========================================================

ALTER TABLE `community_categories` ADD COLUMN `image` VARCHAR(255) NULL AFTER `description`;

-- ==========================================================
-- Database Changes SQL Script for Enrollzy Backend
-- Generated on: 2026-07-24
-- Instructions: Run/Import this file directly in phpMyAdmin or MySQL CLI.
-- ==========================================================

-- 1. Latest Change (2026-07-24): Add image column to community_categories table
ALTER TABLE `community_categories` ADD COLUMN `image` VARCHAR(255) NULL AFTER `description`;

-- 2. Recent Change (2026-07-20): Add image column to homepage_sections table
ALTER TABLE `homepage_sections` ADD COLUMN `image` VARCHAR(255) NULL AFTER `cta_url`;

-- 3. Recent Change (2026-07-18): Add reward_amount column to home_benefits table
ALTER TABLE `home_benefits` ADD COLUMN `reward_amount` VARCHAR(255) NULL AFTER `icon`;

-- 4. Recent Change (2026-07-18): Add founder messages to about_us_pages table
ALTER TABLE `about_us_pages` ADD COLUMN `founder_1_message` TEXT NULL AFTER `founder_1_twitter`;
ALTER TABLE `about_us_pages` ADD COLUMN `founder_2_message` TEXT NULL AFTER `founder_2_twitter`;

-- 5. Recent Change (2026-07-18): Add dynamic fields to about_us_pages table
ALTER TABLE `about_us_pages` ADD COLUMN `hero_tagline` TEXT NULL AFTER `hero_description`;
ALTER TABLE `about_us_pages` ADD COLUMN `simplify_decisions_image` VARCHAR(255) NULL AFTER `hero_image`;
ALTER TABLE `about_us_pages` ADD COLUMN `offers_description` TEXT NULL AFTER `offers_subtitle`;
ALTER TABLE `about_us_pages` ADD COLUMN `impacts_title` VARCHAR(255) NULL AFTER `offers_description`;
ALTER TABLE `about_us_pages` ADD COLUMN `founders_title` VARCHAR(255) NULL AFTER `founders_common_message`;
ALTER TABLE `about_us_pages` ADD COLUMN `team_title` VARCHAR(255) NULL AFTER `founders_title`;
ALTER TABLE `about_us_pages` ADD COLUMN `team_subtitle` VARCHAR(255) NULL AFTER `team_title`;
ALTER TABLE `about_us_pages` ADD COLUMN `advisory_title` VARCHAR(255) NULL AFTER `team_subtitle`;
ALTER TABLE `about_us_pages` ADD COLUMN `advisory_subtitle` VARCHAR(255) NULL AFTER `advisory_title`;

-- 6. Recent Change (2026-07-18): Add core values images to about_us_pages table
ALTER TABLE `about_us_pages` ADD COLUMN `mission_image` VARCHAR(255) NULL AFTER `mission_text`;
ALTER TABLE `about_us_pages` ADD COLUMN `vision_image` VARCHAR(255) NULL AFTER `vision_text`;
ALTER TABLE `about_us_pages` ADD COLUMN `philosophy_image` VARCHAR(255) NULL AFTER `philosophy_text`;

-- ======================================================
-- Enrollzy - Hero Banner Pills DB Changes
-- Date: 2026-07-30
-- Description: Adds Pill Labels & Links fields to hero_sliders table.
-- ======================================================

ALTER TABLE `hero_sliders`
  ADD COLUMN `pill_1_label` VARCHAR(255) NULL AFTER `stat_3_label`,
  ADD COLUMN `pill_1_url` VARCHAR(255) NULL AFTER `pill_1_label`,
  ADD COLUMN `pill_2_label` VARCHAR(255) NULL AFTER `pill_1_url`,
  ADD COLUMN `pill_2_url` VARCHAR(255) NULL AFTER `pill_2_label`,
  ADD COLUMN `pill_3_label` VARCHAR(255) NULL AFTER `pill_2_url`,
  ADD COLUMN `pill_3_url` VARCHAR(255) NULL AFTER `pill_3_label`,
  ADD COLUMN `pill_4_label` VARCHAR(255) NULL AFTER `pill_3_url`,
  ADD COLUMN `pill_4_url` VARCHAR(255) NULL AFTER `pill_4_label`;

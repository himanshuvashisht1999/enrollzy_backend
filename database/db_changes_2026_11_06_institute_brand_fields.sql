-- =====================================================================================================================
-- Enrollzy: coaching institutes, brand level (Phase 1)   6 Nov 2026
--
-- 1. organisations: new columns for institutes - coaching categories and exams, teaching modes, WhatsApp, social and app
--    links, founder and director, scholarship test, franchise, CIN and Udyam. Each is added only if it is not there yet.
-- 2. ownership_types: the Ownership Type master list (Masters -> Ownership Types), pre-filled.
-- 3. organisation_media: photo gallery and video links of an institute (and later of its branches).
--
-- The same changes as migration 2026_11_06_120000_institute_brand_fields.
-- Import in phpMyAdmin (select the database, Import, choose this file). Safe to import again: a second import changes nothing.
-- Works on MySQL and MariaDB.
-- =====================================================================================================================

-- 1. organisations columns
SET @x := (SELECT COUNT(*) FROM information_schema.columns WHERE table_schema = DATABASE() AND table_name = 'organisations' AND column_name = 'coaching_category_ids');
SET @s := IF(@x = 0, 'ALTER TABLE `organisations` ADD COLUMN `coaching_category_ids` LONGTEXT NULL', 'SELECT 1');
PREPARE st FROM @s; EXECUTE st; DEALLOCATE PREPARE st;
SET @x := (SELECT COUNT(*) FROM information_schema.columns WHERE table_schema = DATABASE() AND table_name = 'organisations' AND column_name = 'exams_prepared_for');
SET @s := IF(@x = 0, 'ALTER TABLE `organisations` ADD COLUMN `exams_prepared_for` LONGTEXT NULL', 'SELECT 1');
PREPARE st FROM @s; EXECUTE st; DEALLOCATE PREPARE st;
SET @x := (SELECT COUNT(*) FROM information_schema.columns WHERE table_schema = DATABASE() AND table_name = 'organisations' AND column_name = 'teaching_modes');
SET @s := IF(@x = 0, 'ALTER TABLE `organisations` ADD COLUMN `teaching_modes` LONGTEXT NULL', 'SELECT 1');
PREPARE st FROM @s; EXECUTE st; DEALLOCATE PREPARE st;
SET @x := (SELECT COUNT(*) FROM information_schema.columns WHERE table_schema = DATABASE() AND table_name = 'organisations' AND column_name = 'whatsapp_number');
SET @s := IF(@x = 0, 'ALTER TABLE `organisations` ADD COLUMN `whatsapp_number` TEXT NULL', 'SELECT 1');
PREPARE st FROM @s; EXECUTE st; DEALLOCATE PREPARE st;
SET @x := (SELECT COUNT(*) FROM information_schema.columns WHERE table_schema = DATABASE() AND table_name = 'organisations' AND column_name = 'facebook_url');
SET @s := IF(@x = 0, 'ALTER TABLE `organisations` ADD COLUMN `facebook_url` TEXT NULL', 'SELECT 1');
PREPARE st FROM @s; EXECUTE st; DEALLOCATE PREPARE st;
SET @x := (SELECT COUNT(*) FROM information_schema.columns WHERE table_schema = DATABASE() AND table_name = 'organisations' AND column_name = 'instagram_url');
SET @s := IF(@x = 0, 'ALTER TABLE `organisations` ADD COLUMN `instagram_url` TEXT NULL', 'SELECT 1');
PREPARE st FROM @s; EXECUTE st; DEALLOCATE PREPARE st;
SET @x := (SELECT COUNT(*) FROM information_schema.columns WHERE table_schema = DATABASE() AND table_name = 'organisations' AND column_name = 'youtube_url');
SET @s := IF(@x = 0, 'ALTER TABLE `organisations` ADD COLUMN `youtube_url` TEXT NULL', 'SELECT 1');
PREPARE st FROM @s; EXECUTE st; DEALLOCATE PREPARE st;
SET @x := (SELECT COUNT(*) FROM information_schema.columns WHERE table_schema = DATABASE() AND table_name = 'organisations' AND column_name = 'telegram_url');
SET @s := IF(@x = 0, 'ALTER TABLE `organisations` ADD COLUMN `telegram_url` TEXT NULL', 'SELECT 1');
PREPARE st FROM @s; EXECUTE st; DEALLOCATE PREPARE st;
SET @x := (SELECT COUNT(*) FROM information_schema.columns WHERE table_schema = DATABASE() AND table_name = 'organisations' AND column_name = 'play_store_url');
SET @s := IF(@x = 0, 'ALTER TABLE `organisations` ADD COLUMN `play_store_url` TEXT NULL', 'SELECT 1');
PREPARE st FROM @s; EXECUTE st; DEALLOCATE PREPARE st;
SET @x := (SELECT COUNT(*) FROM information_schema.columns WHERE table_schema = DATABASE() AND table_name = 'organisations' AND column_name = 'app_store_url');
SET @s := IF(@x = 0, 'ALTER TABLE `organisations` ADD COLUMN `app_store_url` TEXT NULL', 'SELECT 1');
PREPARE st FROM @s; EXECUTE st; DEALLOCATE PREPARE st;
SET @x := (SELECT COUNT(*) FROM information_schema.columns WHERE table_schema = DATABASE() AND table_name = 'organisations' AND column_name = 'founder_name');
SET @s := IF(@x = 0, 'ALTER TABLE `organisations` ADD COLUMN `founder_name` TEXT NULL', 'SELECT 1');
PREPARE st FROM @s; EXECUTE st; DEALLOCATE PREPARE st;
SET @x := (SELECT COUNT(*) FROM information_schema.columns WHERE table_schema = DATABASE() AND table_name = 'organisations' AND column_name = 'director_name');
SET @s := IF(@x = 0, 'ALTER TABLE `organisations` ADD COLUMN `director_name` TEXT NULL', 'SELECT 1');
PREPARE st FROM @s; EXECUTE st; DEALLOCATE PREPARE st;
SET @x := (SELECT COUNT(*) FROM information_schema.columns WHERE table_schema = DATABASE() AND table_name = 'organisations' AND column_name = 'scholarship_test_name');
SET @s := IF(@x = 0, 'ALTER TABLE `organisations` ADD COLUMN `scholarship_test_name` TEXT NULL', 'SELECT 1');
PREPARE st FROM @s; EXECUTE st; DEALLOCATE PREPARE st;
SET @x := (SELECT COUNT(*) FROM information_schema.columns WHERE table_schema = DATABASE() AND table_name = 'organisations' AND column_name = 'scholarship_test_dates');
SET @s := IF(@x = 0, 'ALTER TABLE `organisations` ADD COLUMN `scholarship_test_dates` TEXT NULL', 'SELECT 1');
PREPARE st FROM @s; EXECUTE st; DEALLOCATE PREPARE st;
SET @x := (SELECT COUNT(*) FROM information_schema.columns WHERE table_schema = DATABASE() AND table_name = 'organisations' AND column_name = 'scholarship_test_url');
SET @s := IF(@x = 0, 'ALTER TABLE `organisations` ADD COLUMN `scholarship_test_url` TEXT NULL', 'SELECT 1');
PREPARE st FROM @s; EXECUTE st; DEALLOCATE PREPARE st;
SET @x := (SELECT COUNT(*) FROM information_schema.columns WHERE table_schema = DATABASE() AND table_name = 'organisations' AND column_name = 'franchise_available');
SET @s := IF(@x = 0, 'ALTER TABLE `organisations` ADD COLUMN `franchise_available` TINYINT(1) NOT NULL DEFAULT 0', 'SELECT 1');
PREPARE st FROM @s; EXECUTE st; DEALLOCATE PREPARE st;
SET @x := (SELECT COUNT(*) FROM information_schema.columns WHERE table_schema = DATABASE() AND table_name = 'organisations' AND column_name = 'franchise_contact_name');
SET @s := IF(@x = 0, 'ALTER TABLE `organisations` ADD COLUMN `franchise_contact_name` TEXT NULL', 'SELECT 1');
PREPARE st FROM @s; EXECUTE st; DEALLOCATE PREPARE st;
SET @x := (SELECT COUNT(*) FROM information_schema.columns WHERE table_schema = DATABASE() AND table_name = 'organisations' AND column_name = 'franchise_contact_phone');
SET @s := IF(@x = 0, 'ALTER TABLE `organisations` ADD COLUMN `franchise_contact_phone` TEXT NULL', 'SELECT 1');
PREPARE st FROM @s; EXECUTE st; DEALLOCATE PREPARE st;
SET @x := (SELECT COUNT(*) FROM information_schema.columns WHERE table_schema = DATABASE() AND table_name = 'organisations' AND column_name = 'franchise_contact_email');
SET @s := IF(@x = 0, 'ALTER TABLE `organisations` ADD COLUMN `franchise_contact_email` TEXT NULL', 'SELECT 1');
PREPARE st FROM @s; EXECUTE st; DEALLOCATE PREPARE st;
SET @x := (SELECT COUNT(*) FROM information_schema.columns WHERE table_schema = DATABASE() AND table_name = 'organisations' AND column_name = 'cin_number');
SET @s := IF(@x = 0, 'ALTER TABLE `organisations` ADD COLUMN `cin_number` TEXT NULL', 'SELECT 1');
PREPARE st FROM @s; EXECUTE st; DEALLOCATE PREPARE st;
SET @x := (SELECT COUNT(*) FROM information_schema.columns WHERE table_schema = DATABASE() AND table_name = 'organisations' AND column_name = 'udyam_number');
SET @s := IF(@x = 0, 'ALTER TABLE `organisations` ADD COLUMN `udyam_number` TEXT NULL', 'SELECT 1');
PREPARE st FROM @s; EXECUTE st; DEALLOCATE PREPARE st;

-- 2. Ownership Type master
CREATE TABLE IF NOT EXISTS `ownership_types` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `title` varchar(255) NOT NULL,
  `status` tinyint(1) NOT NULL DEFAULT 1,
  `sort_order` int(11) NOT NULL DEFAULT 0,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `ownership_types_title_unique` (`title`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
INSERT IGNORE INTO `ownership_types` (`title`, `status`, `sort_order`, `created_at`, `updated_at`) VALUES ('Private Limited Company', 1, 1, NOW(), NOW());
INSERT IGNORE INTO `ownership_types` (`title`, `status`, `sort_order`, `created_at`, `updated_at`) VALUES ('Public Limited Company', 1, 2, NOW(), NOW());
INSERT IGNORE INTO `ownership_types` (`title`, `status`, `sort_order`, `created_at`, `updated_at`) VALUES ('One Person Company (OPC)', 1, 3, NOW(), NOW());
INSERT IGNORE INTO `ownership_types` (`title`, `status`, `sort_order`, `created_at`, `updated_at`) VALUES ('LLP', 1, 4, NOW(), NOW());
INSERT IGNORE INTO `ownership_types` (`title`, `status`, `sort_order`, `created_at`, `updated_at`) VALUES ('Partnership', 1, 5, NOW(), NOW());
INSERT IGNORE INTO `ownership_types` (`title`, `status`, `sort_order`, `created_at`, `updated_at`) VALUES ('Sole Proprietorship', 1, 6, NOW(), NOW());
INSERT IGNORE INTO `ownership_types` (`title`, `status`, `sort_order`, `created_at`, `updated_at`) VALUES ('Trust', 1, 7, NOW(), NOW());
INSERT IGNORE INTO `ownership_types` (`title`, `status`, `sort_order`, `created_at`, `updated_at`) VALUES ('Society', 1, 8, NOW(), NOW());
INSERT IGNORE INTO `ownership_types` (`title`, `status`, `sort_order`, `created_at`, `updated_at`) VALUES ('Section 8 Company', 1, 9, NOW(), NOW());
INSERT IGNORE INTO `ownership_types` (`title`, `status`, `sort_order`, `created_at`, `updated_at`) VALUES ('Government', 1, 10, NOW(), NOW());

-- 3. Photos and videos (campus_id '' = the institute itself)
CREATE TABLE IF NOT EXISTS `organisation_media` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `organisation_id` bigint(20) unsigned NOT NULL,
  `campus_id` varchar(36) NOT NULL DEFAULT '',
  `type` varchar(10) NOT NULL,
  `file_path` varchar(255) DEFAULT NULL,
  `video_url` varchar(255) DEFAULT NULL,
  `caption` varchar(255) DEFAULT NULL,
  `sort_order` int(11) NOT NULL DEFAULT 0,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `organisation_media_organisation_id_campus_id_index` (`organisation_id`, `campus_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- the migration as run, so `php artisan migrate` does not try it again
INSERT INTO `migrations` (`migration`, `batch`)
SELECT '2026_11_06_120000_institute_brand_fields', nb.`batch`
FROM (SELECT COALESCE(MAX(`batch`), 0) + 1 AS `batch` FROM `migrations`) nb
WHERE NOT EXISTS (SELECT 1 FROM `migrations` WHERE `migration` = '2026_11_06_120000_institute_brand_fields');

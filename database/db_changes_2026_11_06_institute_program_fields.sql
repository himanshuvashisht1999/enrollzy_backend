-- =====================================================================================================================
-- Enrollzy: coaching institute programmes (Phase 3)   6 Nov 2026
--
-- organisation_courses:
--   - new columns: product type, target classes, access validity, demo class and video, test-series details, fee details
--     (early-bird / scholarship %, EMI amount and months, GST included, what the fee covers) and the fee range (from / to);
--   - Total Selections, Selections Last Year, Highest Rank and Selection Rate become numbers. Existing text such as
--     "1,200+" or "AIR 15" is converted to 1200 / 15; text with no number becomes empty;
--   - coaching programmes use one mode field, Delivery Mode (Offline / Online / Hybrid / Recorded): where it was empty it is
--     filled from the older Study Mode (Regular -> Offline, Online -> Online, Distance -> Recorded).
-- New tables: course_batches, course_results (year-wise results), course_toppers.
--
-- The same changes as migration 2026_11_06_160000_institute_program_fields.
-- Import in phpMyAdmin (select the database, Import, choose this file). Safe to import again: a second import changes nothing.
-- Works on MySQL 8 and MariaDB 10.0.5 or later.
-- =====================================================================================================================

SET @x := (SELECT COUNT(*) FROM information_schema.columns WHERE table_schema = DATABASE() AND table_name = 'organisation_courses' AND column_name = 'product_type');
SET @s := IF(@x = 0, 'ALTER TABLE `organisation_courses` ADD COLUMN `product_type` VARCHAR(50) NULL', 'SELECT 1');
PREPARE st FROM @s; EXECUTE st; DEALLOCATE PREPARE st;
SET @x := (SELECT COUNT(*) FROM information_schema.columns WHERE table_schema = DATABASE() AND table_name = 'organisation_courses' AND column_name = 'target_classes');
SET @s := IF(@x = 0, 'ALTER TABLE `organisation_courses` ADD COLUMN `target_classes` LONGTEXT NULL', 'SELECT 1');
PREPARE st FROM @s; EXECUTE st; DEALLOCATE PREPARE st;
SET @x := (SELECT COUNT(*) FROM information_schema.columns WHERE table_schema = DATABASE() AND table_name = 'organisation_courses' AND column_name = 'access_validity_months');
SET @s := IF(@x = 0, 'ALTER TABLE `organisation_courses` ADD COLUMN `access_validity_months` INT UNSIGNED NULL', 'SELECT 1');
PREPARE st FROM @s; EXECUTE st; DEALLOCATE PREPARE st;
SET @x := (SELECT COUNT(*) FROM information_schema.columns WHERE table_schema = DATABASE() AND table_name = 'organisation_courses' AND column_name = 'demo_class_available');
SET @s := IF(@x = 0, 'ALTER TABLE `organisation_courses` ADD COLUMN `demo_class_available` TINYINT(1) NOT NULL DEFAULT 0', 'SELECT 1');
PREPARE st FROM @s; EXECUTE st; DEALLOCATE PREPARE st;
SET @x := (SELECT COUNT(*) FROM information_schema.columns WHERE table_schema = DATABASE() AND table_name = 'organisation_courses' AND column_name = 'demo_video_url');
SET @s := IF(@x = 0, 'ALTER TABLE `organisation_courses` ADD COLUMN `demo_video_url` VARCHAR(255) NULL', 'SELECT 1');
PREPARE st FROM @s; EXECUTE st; DEALLOCATE PREPARE st;
SET @x := (SELECT COUNT(*) FROM information_schema.columns WHERE table_schema = DATABASE() AND table_name = 'organisation_courses' AND column_name = 'total_tests');
SET @s := IF(@x = 0, 'ALTER TABLE `organisation_courses` ADD COLUMN `total_tests` INT UNSIGNED NULL', 'SELECT 1');
PREPARE st FROM @s; EXECUTE st; DEALLOCATE PREPARE st;
SET @x := (SELECT COUNT(*) FROM information_schema.columns WHERE table_schema = DATABASE() AND table_name = 'organisation_courses' AND column_name = 'sectional_tests_count');
SET @s := IF(@x = 0, 'ALTER TABLE `organisation_courses` ADD COLUMN `sectional_tests_count` INT UNSIGNED NULL', 'SELECT 1');
PREPARE st FROM @s; EXECUTE st; DEALLOCATE PREPARE st;
SET @x := (SELECT COUNT(*) FROM information_schema.columns WHERE table_schema = DATABASE() AND table_name = 'organisation_courses' AND column_name = 'all_india_rank_available');
SET @s := IF(@x = 0, 'ALTER TABLE `organisation_courses` ADD COLUMN `all_india_rank_available` TINYINT(1) NOT NULL DEFAULT 0', 'SELECT 1');
PREPARE st FROM @s; EXECUTE st; DEALLOCATE PREPARE st;
SET @x := (SELECT COUNT(*) FROM information_schema.columns WHERE table_schema = DATABASE() AND table_name = 'organisation_courses' AND column_name = 'performance_analysis_available');
SET @s := IF(@x = 0, 'ALTER TABLE `organisation_courses` ADD COLUMN `performance_analysis_available` TINYINT(1) NOT NULL DEFAULT 0', 'SELECT 1');
PREPARE st FROM @s; EXECUTE st; DEALLOCATE PREPARE st;
SET @x := (SELECT COUNT(*) FROM information_schema.columns WHERE table_schema = DATABASE() AND table_name = 'organisation_courses' AND column_name = 'early_bird_discount_percent');
SET @s := IF(@x = 0, 'ALTER TABLE `organisation_courses` ADD COLUMN `early_bird_discount_percent` DECIMAL(5,2) NULL', 'SELECT 1');
PREPARE st FROM @s; EXECUTE st; DEALLOCATE PREPARE st;
SET @x := (SELECT COUNT(*) FROM information_schema.columns WHERE table_schema = DATABASE() AND table_name = 'organisation_courses' AND column_name = 'scholarship_discount_max_percent');
SET @s := IF(@x = 0, 'ALTER TABLE `organisation_courses` ADD COLUMN `scholarship_discount_max_percent` DECIMAL(5,2) NULL', 'SELECT 1');
PREPARE st FROM @s; EXECUTE st; DEALLOCATE PREPARE st;
SET @x := (SELECT COUNT(*) FROM information_schema.columns WHERE table_schema = DATABASE() AND table_name = 'organisation_courses' AND column_name = 'emi_amount');
SET @s := IF(@x = 0, 'ALTER TABLE `organisation_courses` ADD COLUMN `emi_amount` INT UNSIGNED NULL', 'SELECT 1');
PREPARE st FROM @s; EXECUTE st; DEALLOCATE PREPARE st;
SET @x := (SELECT COUNT(*) FROM information_schema.columns WHERE table_schema = DATABASE() AND table_name = 'organisation_courses' AND column_name = 'emi_tenure_months');
SET @s := IF(@x = 0, 'ALTER TABLE `organisation_courses` ADD COLUMN `emi_tenure_months` INT UNSIGNED NULL', 'SELECT 1');
PREPARE st FROM @s; EXECUTE st; DEALLOCATE PREPARE st;
SET @x := (SELECT COUNT(*) FROM information_schema.columns WHERE table_schema = DATABASE() AND table_name = 'organisation_courses' AND column_name = 'gst_included');
SET @s := IF(@x = 0, 'ALTER TABLE `organisation_courses` ADD COLUMN `gst_included` TINYINT(1) NOT NULL DEFAULT 0', 'SELECT 1');
PREPARE st FROM @s; EXECUTE st; DEALLOCATE PREPARE st;
SET @x := (SELECT COUNT(*) FROM information_schema.columns WHERE table_schema = DATABASE() AND table_name = 'organisation_courses' AND column_name = 'fee_includes');
SET @s := IF(@x = 0, 'ALTER TABLE `organisation_courses` ADD COLUMN `fee_includes` LONGTEXT NULL', 'SELECT 1');
PREPARE st FROM @s; EXECUTE st; DEALLOCATE PREPARE st;
SET @x := (SELECT COUNT(*) FROM information_schema.columns WHERE table_schema = DATABASE() AND table_name = 'organisation_courses' AND column_name = 'fee_min');
SET @s := IF(@x = 0, 'ALTER TABLE `organisation_courses` ADD COLUMN `fee_min` INT UNSIGNED NULL', 'SELECT 1');
PREPARE st FROM @s; EXECUTE st; DEALLOCATE PREPARE st;
SET @x := (SELECT COUNT(*) FROM information_schema.columns WHERE table_schema = DATABASE() AND table_name = 'organisation_courses' AND column_name = 'fee_max');
SET @s := IF(@x = 0, 'ALTER TABLE `organisation_courses` ADD COLUMN `fee_max` INT UNSIGNED NULL', 'SELECT 1');
PREPARE st FROM @s; EXECUTE st; DEALLOCATE PREPARE st;
SET @x := (SELECT COUNT(*) FROM information_schema.columns WHERE table_schema = DATABASE() AND table_name = 'organisation_courses' AND column_name = 'total_selections_all_time' AND data_type = 'varchar');
SET @s := IF(@x = 1, 'UPDATE `organisation_courses` SET `total_selections_all_time` = CASE WHEN REGEXP_SUBSTR(REPLACE(`total_selections_all_time`, '','', ''''), ''[0-9]+'') IS NULL OR REGEXP_SUBSTR(REPLACE(`total_selections_all_time`, '','', ''''), ''[0-9]+'') = '''' OR REGEXP_SUBSTR(REPLACE(`total_selections_all_time`, '','', ''''), ''[0-9]+'') + 0 > 4294967295 THEN NULL ELSE REGEXP_SUBSTR(REPLACE(`total_selections_all_time`, '','', ''''), ''[0-9]+'') END', 'SELECT 1');
PREPARE st FROM @s; EXECUTE st; DEALLOCATE PREPARE st;
SET @x := (SELECT COUNT(*) FROM information_schema.columns WHERE table_schema = DATABASE() AND table_name = 'organisation_courses' AND column_name = 'total_selections_all_time' AND data_type = 'varchar');
SET @s := IF(@x = 1, 'ALTER TABLE `organisation_courses` MODIFY `total_selections_all_time` INT UNSIGNED NULL', 'SELECT 1');
PREPARE st FROM @s; EXECUTE st; DEALLOCATE PREPARE st;
SET @x := (SELECT COUNT(*) FROM information_schema.columns WHERE table_schema = DATABASE() AND table_name = 'organisation_courses' AND column_name = 'selections_last_year' AND data_type = 'varchar');
SET @s := IF(@x = 1, 'UPDATE `organisation_courses` SET `selections_last_year` = CASE WHEN REGEXP_SUBSTR(REPLACE(`selections_last_year`, '','', ''''), ''[0-9]+'') IS NULL OR REGEXP_SUBSTR(REPLACE(`selections_last_year`, '','', ''''), ''[0-9]+'') = '''' OR REGEXP_SUBSTR(REPLACE(`selections_last_year`, '','', ''''), ''[0-9]+'') + 0 > 4294967295 THEN NULL ELSE REGEXP_SUBSTR(REPLACE(`selections_last_year`, '','', ''''), ''[0-9]+'') END', 'SELECT 1');
PREPARE st FROM @s; EXECUTE st; DEALLOCATE PREPARE st;
SET @x := (SELECT COUNT(*) FROM information_schema.columns WHERE table_schema = DATABASE() AND table_name = 'organisation_courses' AND column_name = 'selections_last_year' AND data_type = 'varchar');
SET @s := IF(@x = 1, 'ALTER TABLE `organisation_courses` MODIFY `selections_last_year` INT UNSIGNED NULL', 'SELECT 1');
PREPARE st FROM @s; EXECUTE st; DEALLOCATE PREPARE st;
SET @x := (SELECT COUNT(*) FROM information_schema.columns WHERE table_schema = DATABASE() AND table_name = 'organisation_courses' AND column_name = 'highest_rank_achieved' AND data_type = 'varchar');
SET @s := IF(@x = 1, 'UPDATE `organisation_courses` SET `highest_rank_achieved` = CASE WHEN REGEXP_SUBSTR(REPLACE(`highest_rank_achieved`, '','', ''''), ''[0-9]+'') IS NULL OR REGEXP_SUBSTR(REPLACE(`highest_rank_achieved`, '','', ''''), ''[0-9]+'') = '''' OR REGEXP_SUBSTR(REPLACE(`highest_rank_achieved`, '','', ''''), ''[0-9]+'') + 0 > 4294967295 THEN NULL ELSE REGEXP_SUBSTR(REPLACE(`highest_rank_achieved`, '','', ''''), ''[0-9]+'') END', 'SELECT 1');
PREPARE st FROM @s; EXECUTE st; DEALLOCATE PREPARE st;
SET @x := (SELECT COUNT(*) FROM information_schema.columns WHERE table_schema = DATABASE() AND table_name = 'organisation_courses' AND column_name = 'highest_rank_achieved' AND data_type = 'varchar');
SET @s := IF(@x = 1, 'ALTER TABLE `organisation_courses` MODIFY `highest_rank_achieved` INT UNSIGNED NULL', 'SELECT 1');
PREPARE st FROM @s; EXECUTE st; DEALLOCATE PREPARE st;
SET @x := (SELECT COUNT(*) FROM information_schema.columns WHERE table_schema = DATABASE() AND table_name = 'organisation_courses' AND column_name = 'average_selection_rate' AND data_type = 'varchar');
SET @s := IF(@x = 1, 'UPDATE `organisation_courses` SET `average_selection_rate` = CASE WHEN REGEXP_SUBSTR(REPLACE(`average_selection_rate`, '','', ''''), ''[0-9]+([.][0-9]+)?'') IS NULL OR REGEXP_SUBSTR(REPLACE(`average_selection_rate`, '','', ''''), ''[0-9]+([.][0-9]+)?'') = '''' OR REGEXP_SUBSTR(REPLACE(`average_selection_rate`, '','', ''''), ''[0-9]+([.][0-9]+)?'') + 0 > 999.99 THEN NULL ELSE REGEXP_SUBSTR(REPLACE(`average_selection_rate`, '','', ''''), ''[0-9]+([.][0-9]+)?'') END', 'SELECT 1');
PREPARE st FROM @s; EXECUTE st; DEALLOCATE PREPARE st;
SET @x := (SELECT COUNT(*) FROM information_schema.columns WHERE table_schema = DATABASE() AND table_name = 'organisation_courses' AND column_name = 'average_selection_rate' AND data_type = 'varchar');
SET @s := IF(@x = 1, 'ALTER TABLE `organisation_courses` MODIFY `average_selection_rate` DECIMAL(5,2) NULL', 'SELECT 1');
PREPARE st FROM @s; EXECUTE st; DEALLOCATE PREPARE st;

UPDATE `organisation_courses` oc JOIN `organisations` o ON o.`id` = oc.`organisation_id` SET oc.`delivery_mode` = CASE oc.`mode` WHEN 'Online' THEN 'Online' WHEN 'Distance' THEN 'Recorded' ELSE 'Offline' END WHERE o.`organisation_type_id` = 3 AND (oc.`delivery_mode` IS NULL OR oc.`delivery_mode` = '') AND oc.`mode` IS NOT NULL AND oc.`mode` <> '';

UPDATE `organisation_courses` oc JOIN `organisations` o ON o.`id` = oc.`organisation_id` SET oc.`delivery_mode` = 'Offline' WHERE o.`organisation_type_id` = 3 AND oc.`delivery_mode` = 'Classroom';

CREATE TABLE IF NOT EXISTS `course_batches` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `organisation_course_id` bigint(20) unsigned NOT NULL,
  `name` varchar(255) DEFAULT NULL,
  `start_date` date DEFAULT NULL,
  `timing` varchar(20) DEFAULT NULL,
  `timing_details` varchar(100) DEFAULT NULL,
  `medium` varchar(30) DEFAULT NULL,
  `total_seats` int(10) unsigned DEFAULT NULL,
  `seats_left` int(10) unsigned DEFAULT NULL,
  `status` varchar(20) NOT NULL DEFAULT 'Open',
  `sort_order` int(11) NOT NULL DEFAULT 0,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `course_batches_organisation_course_id_index` (`organisation_course_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `course_results` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `organisation_course_id` bigint(20) unsigned NOT NULL,
  `year` smallint(5) unsigned NOT NULL,
  `selections` int(10) unsigned DEFAULT NULL,
  `top_rank` int(10) unsigned DEFAULT NULL,
  `notes` varchar(255) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `course_results_organisation_course_id_year_unique` (`organisation_course_id`, `year`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `course_toppers` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `organisation_course_id` bigint(20) unsigned NOT NULL,
  `year` smallint(5) unsigned DEFAULT NULL,
  `name` varchar(255) NOT NULL,
  `rank` int(10) unsigned DEFAULT NULL,
  `exam` varchar(100) DEFAULT NULL,
  `photo_path` varchar(255) DEFAULT NULL,
  `consent` tinyint(1) NOT NULL DEFAULT 0,
  `sort_order` int(11) NOT NULL DEFAULT 0,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `course_toppers_organisation_course_id_index` (`organisation_course_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- the migration as run, so `php artisan migrate` does not try it again
INSERT INTO `migrations` (`migration`, `batch`)
SELECT '2026_11_06_160000_institute_program_fields', nb.`batch`
FROM (SELECT COALESCE(MAX(`batch`), 0) + 1 AS `batch` FROM `migrations`) nb
WHERE NOT EXISTS (SELECT 1 FROM `migrations` WHERE `migration` = '2026_11_06_160000_institute_program_fields');

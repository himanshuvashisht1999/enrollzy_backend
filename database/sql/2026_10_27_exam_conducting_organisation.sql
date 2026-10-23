-- =====================================================================================================
-- Exam conducting body = organisation  (migration 2026_10_27_100000_add_conducting_organisation_to_exm_exams)
--
-- STRUCTURE ONLY. Adds exm_exams.conducting_organisation_id (an organisation of type "Exam Conducting Body")
-- and records the migration so `php artisan migrate` will not run it again.
-- The old column exm_exams.conducting_body_id and the exm_conducting_bodies table are NOT changed or deleted.
--
-- SAFE TO RE-RUN: every step checks first. BEFORE YOU RUN: take a backup. Needs MySQL 5.7+ / MariaDB 10.3+.
-- =====================================================================================================

SET NAMES utf8mb4;

-- 1. The column
SET @s = IF((SELECT COUNT(*) FROM information_schema.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'exm_exams' AND COLUMN_NAME = 'conducting_organisation_id') = 0,
   'ALTER TABLE `exm_exams` ADD COLUMN `conducting_organisation_id` bigint(20) unsigned NULL DEFAULT NULL AFTER `conducting_body_id`',
   'DO 0');
PREPARE st FROM @s; EXECUTE st; DEALLOCATE PREPARE st;

-- 2. The foreign key (clears the link if the organisation is deleted)
SET @s = IF((SELECT COUNT(*) FROM information_schema.TABLE_CONSTRAINTS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'exm_exams' AND CONSTRAINT_NAME = 'exm_exams_conducting_organisation_id_foreign') = 0,
   'ALTER TABLE `exm_exams` ADD CONSTRAINT `exm_exams_conducting_organisation_id_foreign` FOREIGN KEY (`conducting_organisation_id`) REFERENCES `organisations` (`id`) ON DELETE SET NULL',
   'DO 0');
PREPARE st FROM @s; EXECUTE st; DEALLOCATE PREPARE st;

-- 3. Record the migration
SET @batch = (SELECT IFNULL(MAX(`batch`), 0) + 1 FROM `migrations`);
INSERT INTO `migrations` (`migration`, `batch`)
SELECT '2026_10_27_100000_add_conducting_organisation_to_exm_exams', @batch FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM `migrations` WHERE `migration` = '2026_10_27_100000_add_conducting_organisation_to_exm_exams');

-- Check: should show 1 | 1 | 1
SELECT
  (SELECT COUNT(*) FROM information_schema.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'exm_exams' AND COLUMN_NAME = 'conducting_organisation_id') AS column_added,
  (SELECT COUNT(*) FROM information_schema.TABLE_CONSTRAINTS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'exm_exams' AND CONSTRAINT_NAME = 'exm_exams_conducting_organisation_id_foreign') AS foreign_key,
  (SELECT COUNT(*) FROM `migrations` WHERE `migration` = '2026_10_27_100000_add_conducting_organisation_to_exm_exams') AS migration_recorded;

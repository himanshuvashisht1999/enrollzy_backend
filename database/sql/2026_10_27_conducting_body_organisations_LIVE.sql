-- =====================================================================================================
-- LIVE UPDATE 2026-10-27: conducting bodies become organisations (exams: type 5, counsellings: type 6).
-- = 2026_10_27_exam_conducting_organisation.sql + 2026_10_27_counselling_conducting_organisation.sql in one file.
-- Take a full backup first. Safe to run again. Changes structure only; no data is changed or deleted.
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


-- 1. Old columns become optional (values kept)
ALTER TABLE `cns_counsellings` MODIFY `conducting_body_id` BIGINT UNSIGNED NULL;
ALTER TABLE `cns_institute_codes` MODIFY `conducting_body_id` BIGINT UNSIGNED NULL;

-- 2. New columns
SET @s = IF((SELECT COUNT(*) FROM information_schema.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'cns_counsellings' AND COLUMN_NAME = 'conducting_organisation_id') = 0,
   'ALTER TABLE `cns_counsellings` ADD COLUMN `conducting_organisation_id` bigint(20) unsigned NULL DEFAULT NULL AFTER `conducting_body_id`', 'DO 0');
PREPARE st FROM @s; EXECUTE st; DEALLOCATE PREPARE st;
SET @s = IF((SELECT COUNT(*) FROM information_schema.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'cns_institute_codes' AND COLUMN_NAME = 'conducting_organisation_id') = 0,
   'ALTER TABLE `cns_institute_codes` ADD COLUMN `conducting_organisation_id` bigint(20) unsigned NULL DEFAULT NULL AFTER `conducting_body_id`', 'DO 0');
PREPARE st FROM @s; EXECUTE st; DEALLOCATE PREPARE st;

-- 3. Foreign keys (a deleted organisation clears the counselling link and removes its institute codes)
SET @s = IF((SELECT COUNT(*) FROM information_schema.TABLE_CONSTRAINTS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'cns_counsellings' AND CONSTRAINT_NAME = 'cns_counsellings_conducting_organisation_id_foreign') = 0,
   'ALTER TABLE `cns_counsellings` ADD CONSTRAINT `cns_counsellings_conducting_organisation_id_foreign` FOREIGN KEY (`conducting_organisation_id`) REFERENCES `organisations` (`id`) ON DELETE SET NULL', 'DO 0');
PREPARE st FROM @s; EXECUTE st; DEALLOCATE PREPARE st;
SET @s = IF((SELECT COUNT(*) FROM information_schema.TABLE_CONSTRAINTS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'cns_institute_codes' AND CONSTRAINT_NAME = 'cns_institute_codes_conducting_organisation_id_foreign') = 0,
   'ALTER TABLE `cns_institute_codes` ADD CONSTRAINT `cns_institute_codes_conducting_organisation_id_foreign` FOREIGN KEY (`conducting_organisation_id`) REFERENCES `organisations` (`id`) ON DELETE CASCADE', 'DO 0');
PREPARE st FROM @s; EXECUTE st; DEALLOCATE PREPARE st;

-- 4. One code per college per counselling body
SET @s = IF((SELECT COUNT(*) FROM information_schema.STATISTICS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'cns_institute_codes' AND INDEX_NAME = 'cns_institute_codes_org_code_unique') = 0,
   'ALTER TABLE `cns_institute_codes` ADD UNIQUE KEY `cns_institute_codes_org_code_unique` (`conducting_organisation_id`, `code`)', 'DO 0');
PREPARE st FROM @s; EXECUTE st; DEALLOCATE PREPARE st;

-- 5. Record the migration
SET @batch = (SELECT IFNULL(MAX(`batch`), 0) + 1 FROM `migrations`);
INSERT INTO `migrations` (`migration`, `batch`)
SELECT '2026_10_27_110000_add_conducting_organisation_to_counselling', @batch FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM `migrations` WHERE `migration` = '2026_10_27_110000_add_conducting_organisation_to_counselling');

-- Check: should show 1 | 1 | 2 | 1 | 1 | 0
SELECT
  (SELECT COUNT(*) FROM information_schema.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'cns_counsellings' AND COLUMN_NAME = 'conducting_organisation_id') AS counselling_column,
  (SELECT COUNT(*) FROM information_schema.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'cns_institute_codes' AND COLUMN_NAME = 'conducting_organisation_id') AS codes_column,
  (SELECT COUNT(*) FROM information_schema.TABLE_CONSTRAINTS WHERE TABLE_SCHEMA = DATABASE() AND CONSTRAINT_NAME IN ('cns_counsellings_conducting_organisation_id_foreign', 'cns_institute_codes_conducting_organisation_id_foreign')) AS foreign_keys,
  (SELECT COUNT(DISTINCT INDEX_NAME) FROM information_schema.STATISTICS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'cns_institute_codes' AND INDEX_NAME = 'cns_institute_codes_org_code_unique') AS unique_key,
  (SELECT COUNT(*) FROM `migrations` WHERE `migration` = '2026_10_27_110000_add_conducting_organisation_to_counselling') AS migration_recorded,
  (SELECT COUNT(*) FROM information_schema.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME IN ('cns_counsellings', 'cns_institute_codes') AND COLUMN_NAME = 'conducting_body_id' AND IS_NULLABLE = 'NO') AS old_columns_still_required;

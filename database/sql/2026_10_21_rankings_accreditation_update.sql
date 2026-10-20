-- =====================================================================================================
-- Rankings & Accreditation update  (migrations 2026_10_21_100000 .. 2026_10_21_130000)
-- Generated 2026-10-20 11:35:13 from the tested local database.
--
-- STRUCTURE ONLY. This file creates tables and columns and copies NO data, so it is safe even if the live
-- database has moved on since it was prepared.
--
-- WHAT IT DOES
--   1. Creates organisation_groups, ranking_bodies, ranking_categories, accreditation_bodies,
--      entity_rankings, entity_accreditations, organisation_moves.
--   2. Adds identity columns (AISHE code, NIRF institute id, legal entity type, group, campus role...) to
--      organisations, campuses and departments, and two link columns to accreditation_approvals.
--   3. Records the four migrations in the `migrations` table so `php artisan migrate` will not run them again.
--
-- THEN, to fill in the data from whatever is in the live database at that moment:
--   open  /admin/quality/setup  (log in as superadmin / admin), read the preview, press "Run update".
--   That page seeds the starter masters and permissions, links the old Accreditation master, backfills
--   identity and copies the old NAAC / UGC / AICTE / NIRF values as UNVERIFIED records. It only ever adds
--   missing data and can be run again later to pick up newer data.
--
-- SAFE TO RE-RUN: every step checks first, so running it twice changes nothing the second time.
-- BEFORE YOU RUN: take a full backup.
-- Needs MySQL 5.7+ / MariaDB 10.3+. Does not drop or rewrite any existing column or row.
-- =====================================================================================================

SET NAMES utf8mb4;
SET @OLD_SQL_NOTES = @@SQL_NOTES, SQL_NOTES = 0;

-- 0. Safety check: stop if a table called accreditation_bodies already exists with a different structure.
SET @s = IF((SELECT COUNT(*) FROM information_schema.TABLES WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'accreditation_bodies') > 0
             AND (SELECT COUNT(*) FROM information_schema.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'accreditation_bodies' AND COLUMN_NAME = 'short_name') = 0,
   'SELECT * FROM STOP__accreditation_bodies_already_exists_with_a_different_structure', 'DO 0');
PREPARE st FROM @s; EXECUTE st; DEALLOCATE PREPARE st;

-- 1. New tables
CREATE TABLE IF NOT EXISTS `organisation_groups` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL,
  `slug` varchar(255) NOT NULL,
  `website` varchar(255) DEFAULT NULL,
  `notes` text DEFAULT NULL,
  `status` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `organisation_groups_name_unique` (`name`),
  UNIQUE KEY `organisation_groups_slug_unique` (`slug`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `ranking_bodies` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL,
  `short_name` varchar(40) DEFAULT NULL,
  `type` enum('government','international','media') NOT NULL,
  `country` varchar(60) DEFAULT NULL,
  `website` varchar(255) DEFAULT NULL,
  `trust_level` enum('high','medium','low') NOT NULL DEFAULT 'medium',
  `logo` varchar(255) DEFAULT NULL,
  `status` tinyint(1) NOT NULL DEFAULT 1,
  `sort_order` int(10) unsigned NOT NULL DEFAULT 0,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `ranking_bodies_name_unique` (`name`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `ranking_categories` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `ranking_body_id` bigint(20) unsigned NOT NULL,
  `name` varchar(255) NOT NULL,
  `applicable_levels` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`applicable_levels`)),
  `discipline_id` bigint(20) unsigned DEFAULT NULL,
  `status` tinyint(1) NOT NULL DEFAULT 1,
  `sort_order` int(10) unsigned NOT NULL DEFAULT 0,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `ranking_categories_ranking_body_id_name_unique` (`ranking_body_id`,`name`),
  KEY `ranking_categories_discipline_id_foreign` (`discipline_id`),
  CONSTRAINT `ranking_categories_discipline_id_foreign` FOREIGN KEY (`discipline_id`) REFERENCES `disciplines` (`id`) ON DELETE SET NULL,
  CONSTRAINT `ranking_categories_ranking_body_id_foreign` FOREIGN KEY (`ranking_body_id`) REFERENCES `ranking_bodies` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `accreditation_bodies` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL,
  `short_name` varchar(40) NOT NULL,
  `kind` enum('accreditation','statutory_approval','recognition','affiliation') NOT NULL,
  `applicable_levels` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`applicable_levels`)),
  `has_grade` tinyint(1) NOT NULL DEFAULT 0,
  `grade_options` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`grade_options`)),
  `has_validity` tinyint(1) NOT NULL DEFAULT 1,
  `country` varchar(60) DEFAULT NULL,
  `website` varchar(255) DEFAULT NULL,
  `status` tinyint(1) NOT NULL DEFAULT 1,
  `sort_order` int(10) unsigned NOT NULL DEFAULT 0,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `accreditation_bodies_name_unique` (`name`),
  UNIQUE KEY `accreditation_bodies_short_name_unique` (`short_name`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `entity_rankings` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `entity_type` varchar(20) NOT NULL,
  `entity_id` varchar(40) NOT NULL,
  `ranking_body_id` bigint(20) unsigned NOT NULL,
  `ranking_category_id` bigint(20) unsigned NOT NULL,
  `ranking_year` smallint(5) unsigned DEFAULT NULL,
  `rank_exact` int(10) unsigned DEFAULT NULL,
  `rank_band_from` int(10) unsigned DEFAULT NULL,
  `rank_band_to` int(10) unsigned DEFAULT NULL,
  `score` decimal(8,3) DEFAULT NULL,
  `total_ranked` int(10) unsigned DEFAULT NULL,
  `external_institute_id` varchar(40) DEFAULT NULL,
  `source_url` varchar(500) DEFAULT NULL,
  `evidence_file` varchar(255) DEFAULT NULL,
  `published_on` date DEFAULT NULL,
  `verification_status` enum('unverified','verified','rejected') NOT NULL DEFAULT 'unverified',
  `verified_by` bigint(20) unsigned DEFAULT NULL,
  `verified_at` datetime DEFAULT NULL,
  `notes` text DEFAULT NULL,
  `legacy_source` varchar(60) DEFAULT NULL,
  `created_by` bigint(20) unsigned DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `entity_rankings_ranking_category_id_foreign` (`ranking_category_id`),
  KEY `entity_rankings_entity_type_entity_id_index` (`entity_type`,`entity_id`),
  KEY `entity_rankings_lookup` (`ranking_body_id`,`ranking_category_id`,`ranking_year`),
  KEY `entity_rankings_legacy_source_index` (`legacy_source`),
  CONSTRAINT `entity_rankings_ranking_body_id_foreign` FOREIGN KEY (`ranking_body_id`) REFERENCES `ranking_bodies` (`id`),
  CONSTRAINT `entity_rankings_ranking_category_id_foreign` FOREIGN KEY (`ranking_category_id`) REFERENCES `ranking_categories` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `entity_accreditations` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `entity_type` varchar(20) NOT NULL,
  `entity_id` varchar(40) NOT NULL,
  `accreditation_body_id` bigint(20) unsigned NOT NULL,
  `grade` varchar(30) DEFAULT NULL,
  `score` decimal(5,2) DEFAULT NULL,
  `cycle` tinyint(3) unsigned DEFAULT NULL,
  `valid_from` date DEFAULT NULL,
  `valid_to` date DEFAULT NULL,
  `status` enum('active','expired','applied','withdrawn') NOT NULL DEFAULT 'active',
  `certificate_number` varchar(100) DEFAULT NULL,
  `source_url` varchar(500) DEFAULT NULL,
  `evidence_file` varchar(255) DEFAULT NULL,
  `verification_status` enum('unverified','verified','rejected') NOT NULL DEFAULT 'unverified',
  `verified_by` bigint(20) unsigned DEFAULT NULL,
  `verified_at` datetime DEFAULT NULL,
  `notes` text DEFAULT NULL,
  `legacy_source` varchar(60) DEFAULT NULL,
  `created_by` bigint(20) unsigned DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `entity_accreditations_entity_type_entity_id_index` (`entity_type`,`entity_id`),
  KEY `entity_accreditations_accreditation_body_id_status_index` (`accreditation_body_id`,`status`),
  KEY `entity_accreditations_legacy_source_index` (`legacy_source`),
  CONSTRAINT `entity_accreditations_accreditation_body_id_foreign` FOREIGN KEY (`accreditation_body_id`) REFERENCES `accreditation_bodies` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `organisation_moves` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `from_organisation_id` bigint(20) unsigned NOT NULL,
  `to_organisation_id` bigint(20) unsigned NOT NULL,
  `created_target` tinyint(1) NOT NULL DEFAULT 0,
  `moved` longtext DEFAULT NULL,
  `status` enum('applied','undone') NOT NULL DEFAULT 'applied',
  `note` text DEFAULT NULL,
  `created_by` bigint(20) unsigned DEFAULT NULL,
  `undone_at` datetime DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `organisation_moves_from_organisation_id_index` (`from_organisation_id`),
  KEY `organisation_moves_to_organisation_id_index` (`to_organisation_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 2. Identity columns on existing tables (each added only if missing)
SET @s = IF((SELECT COUNT(*) FROM information_schema.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'organisations' AND COLUMN_NAME = 'aishe_code') = 0, 'ALTER TABLE `organisations` ADD COLUMN `aishe_code` varchar(20) DEFAULT NULL', 'DO 0');
PREPARE st FROM @s; EXECUTE st; DEALLOCATE PREPARE st;
SET @s = IF((SELECT COUNT(*) FROM information_schema.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'organisations' AND COLUMN_NAME = 'nirf_institute_id') = 0, 'ALTER TABLE `organisations` ADD COLUMN `nirf_institute_id` varchar(32) DEFAULT NULL', 'DO 0');
PREPARE st FROM @s; EXECUTE st; DEALLOCATE PREPARE st;
SET @s = IF((SELECT COUNT(*) FROM information_schema.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'organisations' AND COLUMN_NAME = 'group_id') = 0, 'ALTER TABLE `organisations` ADD COLUMN `group_id` bigint(20) unsigned DEFAULT NULL', 'DO 0');
PREPARE st FROM @s; EXECUTE st; DEALLOCATE PREPARE st;
SET @s = IF((SELECT COUNT(*) FROM information_schema.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'organisations' AND COLUMN_NAME = 'affiliating_organisation_id') = 0, 'ALTER TABLE `organisations` ADD COLUMN `affiliating_organisation_id` bigint(20) unsigned DEFAULT NULL', 'DO 0');
PREPARE st FROM @s; EXECUTE st; DEALLOCATE PREPARE st;
SET @s = IF((SELECT COUNT(*) FROM information_schema.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'organisations' AND COLUMN_NAME = 'legal_entity_type') = 0, 'ALTER TABLE `organisations` ADD COLUMN `legal_entity_type` enum(\'central\',\'state_public\',\'state_private\',\'deemed\',\'institution_of_national_importance\',\'autonomous_college\',\'affiliated_college\') DEFAULT NULL', 'DO 0');
PREPARE st FROM @s; EXECUTE st; DEALLOCATE PREPARE st;
SET @s = IF((SELECT COUNT(*) FROM information_schema.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'organisations' AND COLUMN_NAME = 'ugc_recognition_date') = 0, 'ALTER TABLE `organisations` ADD COLUMN `ugc_recognition_date` date DEFAULT NULL', 'DO 0');
PREPARE st FROM @s; EXECUTE st; DEALLOCATE PREPARE st;
SET @s = IF((SELECT COUNT(*) FROM information_schema.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'organisations' AND COLUMN_NAME = 'deemed_status_notification_ref') = 0, 'ALTER TABLE `organisations` ADD COLUMN `deemed_status_notification_ref` text DEFAULT NULL', 'DO 0');
PREPARE st FROM @s; EXECUTE st; DEALLOCATE PREPARE st;
SET @s = IF((SELECT COUNT(*) FROM information_schema.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'organisations' AND COLUMN_NAME = 'identity_source_url') = 0, 'ALTER TABLE `organisations` ADD COLUMN `identity_source_url` text DEFAULT NULL', 'DO 0');
PREPARE st FROM @s; EXECUTE st; DEALLOCATE PREPARE st;
SET @s = IF((SELECT COUNT(*) FROM information_schema.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'organisations' AND COLUMN_NAME = 'identity_verified_at') = 0, 'ALTER TABLE `organisations` ADD COLUMN `identity_verified_at` datetime DEFAULT NULL', 'DO 0');
PREPARE st FROM @s; EXECUTE st; DEALLOCATE PREPARE st;
SET @s = IF((SELECT COUNT(*) FROM information_schema.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'organisations' AND COLUMN_NAME = 'identity_verified_by') = 0, 'ALTER TABLE `organisations` ADD COLUMN `identity_verified_by` bigint(20) unsigned DEFAULT NULL', 'DO 0');
PREPARE st FROM @s; EXECUTE st; DEALLOCATE PREPARE st;
SET @s = IF((SELECT COUNT(*) FROM information_schema.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'campuses' AND COLUMN_NAME = 'campus_role') = 0, 'ALTER TABLE `campuses` ADD COLUMN `campus_role` enum(\'main\',\'off_campus\',\'constituent_college\',\'study_centre\') DEFAULT NULL', 'DO 0');
PREPARE st FROM @s; EXECUTE st; DEALLOCATE PREPARE st;
SET @s = IF((SELECT COUNT(*) FROM information_schema.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'campuses' AND COLUMN_NAME = 'aishe_code') = 0, 'ALTER TABLE `campuses` ADD COLUMN `aishe_code` varchar(20) DEFAULT NULL', 'DO 0');
PREPARE st FROM @s; EXECUTE st; DEALLOCATE PREPARE st;
SET @s = IF((SELECT COUNT(*) FROM information_schema.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'campuses' AND COLUMN_NAME = 'nirf_institute_id') = 0, 'ALTER TABLE `campuses` ADD COLUMN `nirf_institute_id` varchar(32) DEFAULT NULL', 'DO 0');
PREPARE st FROM @s; EXECUTE st; DEALLOCATE PREPARE st;
SET @s = IF((SELECT COUNT(*) FROM information_schema.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'campuses' AND COLUMN_NAME = 'board_affiliation_number') = 0, 'ALTER TABLE `campuses` ADD COLUMN `board_affiliation_number` varchar(60) DEFAULT NULL', 'DO 0');
PREPARE st FROM @s; EXECUTE st; DEALLOCATE PREPARE st;
SET @s = IF((SELECT COUNT(*) FROM information_schema.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'campuses' AND COLUMN_NAME = 'affiliation_valid_to') = 0, 'ALTER TABLE `campuses` ADD COLUMN `affiliation_valid_to` date DEFAULT NULL', 'DO 0');
PREPARE st FROM @s; EXECUTE st; DEALLOCATE PREPARE st;
SET @s = IF((SELECT COUNT(*) FROM information_schema.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'campuses' AND COLUMN_NAME = 'inherits_org_accreditation') = 0, 'ALTER TABLE `campuses` ADD COLUMN `inherits_org_accreditation` tinyint(1) NOT NULL DEFAULT 1', 'DO 0');
PREPARE st FROM @s; EXECUTE st; DEALLOCATE PREPARE st;
SET @s = IF((SELECT COUNT(*) FROM information_schema.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'campuses' AND COLUMN_NAME = 'identity_source_url') = 0, 'ALTER TABLE `campuses` ADD COLUMN `identity_source_url` text DEFAULT NULL', 'DO 0');
PREPARE st FROM @s; EXECUTE st; DEALLOCATE PREPARE st;
SET @s = IF((SELECT COUNT(*) FROM information_schema.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'campuses' AND COLUMN_NAME = 'identity_verified_at') = 0, 'ALTER TABLE `campuses` ADD COLUMN `identity_verified_at` datetime DEFAULT NULL', 'DO 0');
PREPARE st FROM @s; EXECUTE st; DEALLOCATE PREPARE st;
SET @s = IF((SELECT COUNT(*) FROM information_schema.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'campuses' AND COLUMN_NAME = 'identity_verified_by') = 0, 'ALTER TABLE `campuses` ADD COLUMN `identity_verified_by` bigint(20) unsigned DEFAULT NULL', 'DO 0');
PREPARE st FROM @s; EXECUTE st; DEALLOCATE PREPARE st;
SET @s = IF((SELECT COUNT(*) FROM information_schema.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'departments' AND COLUMN_NAME = 'academic_unit_type') = 0, 'ALTER TABLE `departments` ADD COLUMN `academic_unit_type` enum(\'department\',\'school\',\'faculty\',\'centre\') NOT NULL DEFAULT \'department\'', 'DO 0');
PREPARE st FROM @s; EXECUTE st; DEALLOCATE PREPARE st;
SET @s = IF((SELECT COUNT(*) FROM information_schema.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'departments' AND COLUMN_NAME = 'nirf_institute_id') = 0, 'ALTER TABLE `departments` ADD COLUMN `nirf_institute_id` varchar(32) DEFAULT NULL', 'DO 0');
PREPARE st FROM @s; EXECUTE st; DEALLOCATE PREPARE st;
SET @s = IF((SELECT COUNT(*) FROM information_schema.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'accreditation_approvals' AND COLUMN_NAME = 'accreditation_body_id') = 0, 'ALTER TABLE `accreditation_approvals` ADD COLUMN `accreditation_body_id` bigint(20) unsigned DEFAULT NULL', 'DO 0');
PREPARE st FROM @s; EXECUTE st; DEALLOCATE PREPARE st;
SET @s = IF((SELECT COUNT(*) FROM information_schema.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'accreditation_approvals' AND COLUMN_NAME = 'grade') = 0, 'ALTER TABLE `accreditation_approvals` ADD COLUMN `grade` varchar(30) DEFAULT NULL', 'DO 0');
PREPARE st FROM @s; EXECUTE st; DEALLOCATE PREPARE st;

-- Indexes and foreign keys for those columns
SET @s = IF((SELECT COUNT(*) FROM information_schema.STATISTICS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'organisations' AND INDEX_NAME = 'organisations_group_id_foreign') = 0, 'ALTER TABLE `organisations` ADD INDEX `organisations_group_id_foreign` (`group_id`)', 'DO 0');
PREPARE st FROM @s; EXECUTE st; DEALLOCATE PREPARE st;
SET @s = IF((SELECT COUNT(*) FROM information_schema.TABLE_CONSTRAINTS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'organisations' AND CONSTRAINT_NAME = 'organisations_group_id_foreign' AND CONSTRAINT_TYPE = 'FOREIGN KEY') = 0, 'ALTER TABLE `organisations` ADD CONSTRAINT `organisations_group_id_foreign` FOREIGN KEY (`group_id`) REFERENCES `organisation_groups` (`id`) ON DELETE SET NULL', 'DO 0');
PREPARE st FROM @s; EXECUTE st; DEALLOCATE PREPARE st;
SET @s = IF((SELECT COUNT(*) FROM information_schema.STATISTICS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'campuses' AND INDEX_NAME = 'campuses_aishe_code_unique') = 0, 'ALTER TABLE `campuses` ADD UNIQUE INDEX `campuses_aishe_code_unique` (`aishe_code`)', 'DO 0');
PREPARE st FROM @s; EXECUTE st; DEALLOCATE PREPARE st;
SET @s = IF((SELECT COUNT(*) FROM information_schema.STATISTICS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'campuses' AND INDEX_NAME = 'campuses_nirf_institute_id_unique') = 0, 'ALTER TABLE `campuses` ADD UNIQUE INDEX `campuses_nirf_institute_id_unique` (`nirf_institute_id`)', 'DO 0');
PREPARE st FROM @s; EXECUTE st; DEALLOCATE PREPARE st;
SET @s = IF((SELECT COUNT(*) FROM information_schema.STATISTICS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'departments' AND INDEX_NAME = 'departments_nirf_institute_id_unique') = 0, 'ALTER TABLE `departments` ADD UNIQUE INDEX `departments_nirf_institute_id_unique` (`nirf_institute_id`)', 'DO 0');
PREPARE st FROM @s; EXECUTE st; DEALLOCATE PREPARE st;
SET @s = IF((SELECT COUNT(*) FROM information_schema.STATISTICS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'accreditation_approvals' AND INDEX_NAME = 'accreditation_approvals_accreditation_body_id_foreign') = 0, 'ALTER TABLE `accreditation_approvals` ADD INDEX `accreditation_approvals_accreditation_body_id_foreign` (`accreditation_body_id`)', 'DO 0');
PREPARE st FROM @s; EXECUTE st; DEALLOCATE PREPARE st;
SET @s = IF((SELECT COUNT(*) FROM information_schema.TABLE_CONSTRAINTS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'accreditation_approvals' AND CONSTRAINT_NAME = 'accreditation_approvals_accreditation_body_id_foreign' AND CONSTRAINT_TYPE = 'FOREIGN KEY') = 0, 'ALTER TABLE `accreditation_approvals` ADD CONSTRAINT `accreditation_approvals_accreditation_body_id_foreign` FOREIGN KEY (`accreditation_body_id`) REFERENCES `accreditation_bodies` (`id`) ON DELETE SET NULL', 'DO 0');
PREPARE st FROM @s; EXECUTE st; DEALLOCATE PREPARE st;

-- 8. Record the migrations so `php artisan migrate` does not try to run them again
SET @batch = (SELECT IFNULL(MAX(batch), 0) + 1 FROM migrations);
INSERT INTO migrations (migration, batch) SELECT '2026_10_21_100000_add_identity_fields_for_quality_records', @batch FROM DUAL WHERE NOT EXISTS (SELECT 1 FROM migrations WHERE migration = '2026_10_21_100000_add_identity_fields_for_quality_records');
INSERT INTO migrations (migration, batch) SELECT '2026_10_21_110000_create_ranking_and_accreditation_tables', @batch FROM DUAL WHERE NOT EXISTS (SELECT 1 FROM migrations WHERE migration = '2026_10_21_110000_create_ranking_and_accreditation_tables');
INSERT INTO migrations (migration, batch) SELECT '2026_10_21_120000_create_organisation_moves_table', @batch FROM DUAL WHERE NOT EXISTS (SELECT 1 FROM migrations WHERE migration = '2026_10_21_120000_create_organisation_moves_table');
INSERT INTO migrations (migration, batch) SELECT '2026_10_21_130000_link_legacy_accreditation_approvals', @batch FROM DUAL WHERE NOT EXISTS (SELECT 1 FROM migrations WHERE migration = '2026_10_21_130000_link_legacy_accreditation_approvals');

-- 9. Read-only check that the structure is in place (all numbers should be 7 / 10 / 9 / 2 / 4)
SELECT (SELECT COUNT(*) FROM information_schema.TABLES WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME IN ('organisation_groups','ranking_bodies','ranking_categories','accreditation_bodies','entity_rankings','entity_accreditations','organisation_moves')) AS new_tables,
       (SELECT COUNT(*) FROM information_schema.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'organisations' AND COLUMN_NAME IN ('aishe_code','nirf_institute_id','group_id','affiliating_organisation_id','legal_entity_type','ugc_recognition_date','deemed_status_notification_ref','identity_source_url','identity_verified_at','identity_verified_by')) AS organisation_columns,
       (SELECT COUNT(*) FROM information_schema.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'campuses' AND COLUMN_NAME IN ('campus_role','aishe_code','nirf_institute_id','board_affiliation_number','affiliation_valid_to','inherits_org_accreditation','identity_source_url','identity_verified_at','identity_verified_by')) AS campus_columns,
       (SELECT COUNT(*) FROM information_schema.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'departments' AND COLUMN_NAME IN ('academic_unit_type','nirf_institute_id')) AS department_columns,
       (SELECT COUNT(*) FROM migrations WHERE migration LIKE '2026_10_21%') AS migrations_recorded;

SET SQL_NOTES = @OLD_SQL_NOTES;

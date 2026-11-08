-- =====================================================================================================================
-- Enrollzy: become an expert (applications, documents, managed expertise list)                        31 Oct 2026
--
-- * expert_expertise_areas: the expertise list experts pick from (was fixed in code), managed in the CRM
--   (Experts > Expertise Areas). Starts with the seven areas the site already uses.
-- * expert_applications, expert_application_notes: applications from the website (My account > Become an expert),
--   reviewed in the CRM (Experts > Applications) with messages, staff notes and status changes.
-- * expert_documents: qualifications, certificates, awards, achievements and ID of applicants and experts, each
--   checked by staff. The files are private (CRM storage/app/private/expert-docs).
-- * settings: applications per person (3) and days to wait after "not approved" (30), editable in the CRM.
--
-- The same change as migration 2026_10_31_160000_create_expert_applications_and_documents.
-- Import in phpMyAdmin (select the database, Import, choose this file), or mysql -u USER -p DATABASE < this-file.sql.
-- Safe to import again: tables and columns that exist are skipped, the seven areas are added only to an empty list,
-- and the migration is recorded once. Works on MySQL 5.7 / 8 and MariaDB 10.2+.
-- =====================================================================================================================

-- 1. Tables (only if missing)
CREATE TABLE IF NOT EXISTS `expert_expertise_areas` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(100) NOT NULL,
  `sort_order` int(10) unsigned NOT NULL DEFAULT 0,
  `status` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `expert_expertise_areas_name_unique` (`name`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `expert_applications` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `user_id` bigint(20) unsigned NOT NULL,
  `expert_id` bigint(20) unsigned DEFAULT NULL,
  `attempt_no` tinyint(3) unsigned NOT NULL DEFAULT 1,
  `status` varchar(20) NOT NULL DEFAULT 'pending',
  `name` varchar(150) NOT NULL,
  `email` varchar(150) NOT NULL,
  `mobile` varchar(20) NOT NULL,
  `gender` varchar(20) DEFAULT NULL,
  `city` varchar(100) DEFAULT NULL,
  `state` varchar(100) DEFAULT NULL,
  `photo` varchar(255) DEFAULT NULL,
  `designation` varchar(150) DEFAULT NULL,
  `organisation` varchar(200) DEFAULT NULL,
  `highest_qualification` varchar(200) DEFAULT NULL,
  `years_of_experience` smallint(5) unsigned DEFAULT NULL,
  `students_counselled` varchar(100) DEFAULT NULL,
  `expertise_areas` json DEFAULT NULL,
  `levels` json DEFAULT NULL,
  `languages` json DEFAULT NULL,
  `session_modes` json DEFAULT NULL,
  `short_bio` varchar(255) DEFAULT NULL,
  `detailed_bio` text DEFAULT NULL,
  `proposed_fee` int(10) unsigned DEFAULT NULL,
  `linkedin_url` varchar(300) DEFAULT NULL,
  `website_url` varchar(300) DEFAULT NULL,
  `submitted_at` timestamp NULL DEFAULT NULL,
  `decided_at` timestamp NULL DEFAULT NULL,
  `decided_by` bigint(20) unsigned DEFAULT NULL,
  `reapply_allowed_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `expert_applications_user_id_index` (`user_id`),
  KEY `expert_applications_expert_id_index` (`expert_id`),
  KEY `expert_applications_status_index` (`status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `expert_application_notes` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `application_id` bigint(20) unsigned NOT NULL,
  `author` varchar(10) NOT NULL,
  `admin_id` bigint(20) unsigned DEFAULT NULL,
  `internal` tinyint(1) NOT NULL DEFAULT 0,
  `from_status` varchar(20) DEFAULT NULL,
  `to_status` varchar(20) DEFAULT NULL,
  `body` text DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `expert_application_notes_application_id_index` (`application_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `expert_documents` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `expert_id` bigint(20) unsigned DEFAULT NULL,
  `application_id` bigint(20) unsigned DEFAULT NULL,
  `type` varchar(20) NOT NULL,
  `title` varchar(200) NOT NULL,
  `issued_by` varchar(200) DEFAULT NULL,
  `year` smallint(5) unsigned DEFAULT NULL,
  `path` varchar(255) NOT NULL,
  `original_name` varchar(255) DEFAULT NULL,
  `mime` varchar(100) DEFAULT NULL,
  `size` int(10) unsigned DEFAULT NULL,
  `status` varchar(10) NOT NULL DEFAULT 'pending',
  `review_note` varchar(500) DEFAULT NULL,
  `reviewed_by` bigint(20) unsigned DEFAULT NULL,
  `reviewed_at` timestamp NULL DEFAULT NULL,
  `show_on_profile` tinyint(1) NOT NULL DEFAULT 1,
  `uploaded_by` varchar(10) NOT NULL DEFAULT 'expert',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `expert_documents_expert_id_index` (`expert_id`),
  KEY `expert_documents_application_id_index` (`application_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 2. The seven expertise areas the site uses today (only into an empty list, so renamed or removed ones stay that way)
INSERT INTO `expert_expertise_areas` (`name`, `sort_order`, `status`, `created_at`, `updated_at`)
SELECT v.`name`, v.`sort_order`, 1, NOW(), NOW() FROM (
    SELECT 'Career Guidance' AS `name`, 1 AS `sort_order` UNION ALL SELECT 'School Admissions', 2 UNION ALL SELECT 'University Admissions', 3
    UNION ALL SELECT 'Competitive Exams', 4 UNION ALL SELECT 'Study Abroad', 5 UNION ALL SELECT 'Scholarships', 6 UNION ALL SELECT 'Online Education', 7
) v
WHERE NOT EXISTS (SELECT 1 FROM `expert_expertise_areas`);

-- 3. Re-apply rules in settings (each column only if missing)
SET @sql = IF((SELECT COUNT(*) FROM information_schema.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'settings' AND COLUMN_NAME = 'expert_max_applications') = 0,
    'ALTER TABLE `settings` ADD COLUMN `expert_max_applications` TINYINT UNSIGNED NOT NULL DEFAULT 3', 'DO 0');
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

SET @sql = IF((SELECT COUNT(*) FROM information_schema.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'settings' AND COLUMN_NAME = 'expert_reapply_wait_days') = 0,
    'ALTER TABLE `settings` ADD COLUMN `expert_reapply_wait_days` SMALLINT UNSIGNED NOT NULL DEFAULT 30', 'DO 0');
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

-- 4. Record the migration as run (once), so "php artisan migrate" does not run it again.
INSERT INTO `migrations` (`migration`, `batch`)
SELECT '2026_10_31_160000_create_expert_applications_and_documents', nb.`batch`
FROM (SELECT COALESCE(MAX(`batch`), 0) + 1 AS `batch` FROM `migrations`) nb
WHERE NOT EXISTS (SELECT 1 FROM `migrations` WHERE `migration` = '2026_10_31_160000_create_expert_applications_and_documents');

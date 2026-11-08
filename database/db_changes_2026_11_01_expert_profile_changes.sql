-- =====================================================================================================================
-- Enrollzy: experts edit their profile, staff approve                                                  1 Nov 2026
--
-- An approved expert edits their profile on the website (expert dashboard > My profile). Every change waits in this
-- table until staff approve it in the CRM (Expert Management > Profile Changes); the public profile changes only then.
--
-- The same change as migration 2026_11_01_110000_create_expert_profile_changes.
-- Import in phpMyAdmin (select the database, Import, choose this file). Safe to import again.
-- =====================================================================================================================

CREATE TABLE IF NOT EXISTS `expert_profile_changes` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `expert_id` bigint(20) unsigned NOT NULL,
  `user_id` bigint(20) unsigned DEFAULT NULL,
  `changes` json NOT NULL,
  `previous` json DEFAULT NULL,
  `photo` varchar(255) DEFAULT NULL,
  `status` varchar(10) NOT NULL DEFAULT 'pending',
  `note` varchar(1000) DEFAULT NULL,
  `reviewed_by` bigint(20) unsigned DEFAULT NULL,
  `reviewed_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `expert_profile_changes_expert_id_index` (`expert_id`),
  KEY `expert_profile_changes_status_index` (`status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO `migrations` (`migration`, `batch`)
SELECT '2026_11_01_110000_create_expert_profile_changes', nb.`batch`
FROM (SELECT COALESCE(MAX(`batch`), 0) + 1 AS `batch` FROM `migrations`) nb
WHERE NOT EXISTS (SELECT 1 FROM `migrations` WHERE `migration` = '2026_11_01_110000_create_expert_profile_changes');

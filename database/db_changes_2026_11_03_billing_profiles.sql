-- =====================================================================================================================
-- Enrollzy: billing details for organisations and campuses (Billing Module → Organisation Billing)   3 Nov 2026
--
-- billing_profiles: the address, GSTIN, TAN, PAN, CIN and contact we print on invoices to an organisation or campus.
--   campus_id '' (empty) is the organisation's own row (campus ids are UUIDs); a campus row can be "same as organisation".
--   For the billing module only: the website never reads this table.
--
-- The same change as migration 2026_11_03_200000_create_billing_profiles_table.
-- Import in phpMyAdmin (select the database, Import, choose this file). Safe to import again: a second import changes nothing.
-- =====================================================================================================================

CREATE TABLE IF NOT EXISTS `billing_profiles` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `organisation_id` bigint(20) unsigned NOT NULL,
  `campus_id` varchar(36) NOT NULL DEFAULT '',
  `same_as_organisation` tinyint(1) NOT NULL DEFAULT 0,
  `legal_name` varchar(255) DEFAULT NULL,
  `contact_person` varchar(255) DEFAULT NULL,
  `email` varchar(255) DEFAULT NULL,
  `phone` varchar(50) DEFAULT NULL,
  `gstin` varchar(25) DEFAULT NULL,
  `tan_number` varchar(25) DEFAULT NULL,
  `pan_number` varchar(25) DEFAULT NULL,
  `cin_number` varchar(50) DEFAULT NULL,
  `address` text DEFAULT NULL,
  `city` varchar(100) DEFAULT NULL,
  `state` varchar(100) DEFAULT NULL,
  `pincode` varchar(20) DEFAULT NULL,
  `country` varchar(100) DEFAULT NULL,
  `notes` text DEFAULT NULL,
  `updated_by` bigint(20) unsigned DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `billing_profiles_organisation_id_campus_id_unique` (`organisation_id`, `campus_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- the migration as run, so `php artisan migrate` does not try it again
INSERT INTO `migrations` (`migration`, `batch`)
SELECT '2026_11_03_200000_create_billing_profiles_table', nb.`batch`
FROM (SELECT COALESCE(MAX(`batch`), 0) + 1 AS `batch` FROM `migrations`) nb
WHERE NOT EXISTS (SELECT 1 FROM `migrations` WHERE `migration` = '2026_11_03_200000_create_billing_profiles_table');

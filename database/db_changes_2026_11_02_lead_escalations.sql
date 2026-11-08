-- =====================================================================================================================
-- Enrollzy: calling - "Escalate to my team lead" and "Mark as important" on the call form   2 Nov 2026
--
-- 1. lead_escalations: a telecaller escalates a lead to their manager with a reason; it closes when that manager
--    calls the lead or resolves it from their dashboard.
-- 2. important_leads: a lead flagged important (one row per lead).
--
-- The same changes as migration 2026_11_02_140000_lead_escalations_and_important.
-- Import in phpMyAdmin (select the database, Import, choose this file). Safe to import again: a second import changes nothing.
-- =====================================================================================================================

CREATE TABLE IF NOT EXISTS `lead_escalations` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `customer_id` bigint(20) unsigned NOT NULL,
  `raised_by` bigint(20) unsigned NOT NULL,
  `team_lead_id` bigint(20) unsigned DEFAULT NULL,
  `reason` varchar(500) NOT NULL,
  `status` enum('open','resolved') NOT NULL DEFAULT 'open',
  `resolved_by` bigint(20) unsigned DEFAULT NULL,
  `resolved_at` timestamp NULL DEFAULT NULL,
  `resolution` varchar(500) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `lead_escalations_team_lead_id_status_index` (`team_lead_id`, `status`),
  KEY `lead_escalations_customer_id_status_index` (`customer_id`, `status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `important_leads` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `customer_id` bigint(20) unsigned NOT NULL,
  `marked_by` bigint(20) unsigned NOT NULL,
  `note` varchar(255) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `important_leads_customer_id_unique` (`customer_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO `migrations` (`migration`, `batch`)
SELECT '2026_11_02_140000_lead_escalations_and_important', nb.`batch`
FROM (SELECT COALESCE(MAX(`batch`), 0) + 1 AS `batch` FROM `migrations`) nb
WHERE NOT EXISTS (SELECT 1 FROM `migrations` WHERE `migration` = '2026_11_02_140000_lead_escalations_and_important');

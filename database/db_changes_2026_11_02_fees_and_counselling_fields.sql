-- =====================================================================================================================
-- Enrollzy: exam fees for several categories + counselling Schema Builder                             2 Nov 2026
--
-- 1. Exam fees: one fee row can cover several categories ("General, EWS, OBC-NCL · Male · Rs 1,000").
--    New column exm_fees.category_ids; every existing row gets its one category as the list.
-- 2. Counselling Schema Builder, like the exam module's: extra tabs unique to one counselling
--    (CRM > Counselling > a counselling > Schema Builder), with values per cycle.
--
-- The same changes as migrations 2026_11_02_100000_add_category_ids_to_exm_fees and
-- 2026_11_02_110000_create_cns_custom_fields_tables.
-- Import in phpMyAdmin (select the database, Import, choose this file). Safe to import again.
-- =====================================================================================================================

-- 1. exm_fees.category_ids (added only when missing)
SET @add_col = (SELECT IF(COUNT(*) = 0,
    'ALTER TABLE `exm_fees` ADD COLUMN `category_ids` json DEFAULT NULL AFTER `category_id`',
    'SELECT 1')
  FROM information_schema.COLUMNS
  WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'exm_fees' AND COLUMN_NAME = 'category_ids');
PREPARE stmt FROM @add_col;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

UPDATE `exm_fees` SET `category_ids` = CONCAT('[', `category_id`, ']')
WHERE `category_ids` IS NULL AND `category_id` IS NOT NULL;

INSERT INTO `migrations` (`migration`, `batch`)
SELECT '2026_11_02_100000_add_category_ids_to_exm_fees', nb.`batch`
FROM (SELECT COALESCE(MAX(`batch`), 0) + 1 AS `batch` FROM `migrations`) nb
WHERE NOT EXISTS (SELECT 1 FROM `migrations` WHERE `migration` = '2026_11_02_100000_add_category_ids_to_exm_fees');

-- 2. counselling Schema Builder
CREATE TABLE IF NOT EXISTS `cns_custom_fields` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `counselling_id` bigint(20) unsigned NOT NULL,
  `key` varchar(60) NOT NULL,
  `label` varchar(120) NOT NULL,
  `type` varchar(20) NOT NULL,
  `options` json DEFAULT NULL,
  `scope` enum('counselling','cycle') NOT NULL DEFAULT 'counselling',
  `sort_order` int(10) unsigned NOT NULL DEFAULT 0,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `cns_custom_fields_counselling_id_key_unique` (`counselling_id`, `key`),
  CONSTRAINT `cns_custom_fields_counselling_id_foreign` FOREIGN KEY (`counselling_id`) REFERENCES `cns_counsellings` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `cns_custom_values` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `field_id` bigint(20) unsigned NOT NULL,
  `cycle_id` bigint(20) unsigned DEFAULT NULL,
  `value` json DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `cns_custom_values_cycle_id_foreign` (`cycle_id`),
  KEY `cns_custom_values_field_id_cycle_id_index` (`field_id`, `cycle_id`),
  CONSTRAINT `cns_custom_values_cycle_id_foreign` FOREIGN KEY (`cycle_id`) REFERENCES `cns_cycles` (`id`) ON DELETE CASCADE,
  CONSTRAINT `cns_custom_values_field_id_foreign` FOREIGN KEY (`field_id`) REFERENCES `cns_custom_fields` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO `migrations` (`migration`, `batch`)
SELECT '2026_11_02_110000_create_cns_custom_fields_tables', nb.`batch`
FROM (SELECT COALESCE(MAX(`batch`), 0) + 1 AS `batch` FROM `migrations`) nb
WHERE NOT EXISTS (SELECT 1 FROM `migrations` WHERE `migration` = '2026_11_02_110000_create_cns_custom_fields_tables');

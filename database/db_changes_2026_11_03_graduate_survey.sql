-- =====================================================================================================================
-- Enrollzy: graduate survey (website /survey/graduates, CRM: Customers → Graduate Survey)   3 Nov 2026
--
-- graduate_surveys: one row per mobile number (a repeat submission updates it and counts it). The main answers have
-- their own columns for filters and export; every question and answer is kept in `answers`.
--
-- The same change as migration 2026_11_03_120000_create_graduate_surveys_table.
-- Import in phpMyAdmin (select the database, Import, choose this file). Safe to import again: a second import changes nothing.
-- =====================================================================================================================

CREATE TABLE IF NOT EXISTS `graduate_surveys` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `phone` varchar(10) NOT NULL,
  `name` varchar(120) NOT NULL,
  `email` varchar(150) DEFAULT NULL,
  `current_state` varchar(60) DEFAULT NULL,
  `stream` varchar(20) NOT NULL,
  `stream_label` varchar(80) NOT NULL,
  `grad_status` varchar(20) DEFAULT NULL,
  `grad_status_label` varchar(80) DEFAULT NULL,
  `aim` varchar(20) NOT NULL,
  `aim_label` varchar(80) NOT NULL,
  `preferred_course` varchar(150) DEFAULT NULL,
  `preferred_place` varchar(255) DEFAULT NULL,
  `focus` varchar(255) DEFAULT NULL,
  `answers` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL CHECK (json_valid(`answers`)),
  `customer_id` bigint(20) unsigned DEFAULT NULL,
  `status` enum('new','contacted','converted','not_interested') NOT NULL DEFAULT 'new',
  `notes` text DEFAULT NULL,
  `submissions` int(10) unsigned NOT NULL DEFAULT 1,
  `consent` tinyint(1) NOT NULL DEFAULT 0,
  `utm` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`utm`)),
  `ip` varchar(45) DEFAULT NULL,
  `user_agent` varchar(255) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `graduate_surveys_phone_unique` (`phone`),
  KEY `graduate_surveys_current_state_index` (`current_state`),
  KEY `graduate_surveys_stream_index` (`stream`),
  KEY `graduate_surveys_aim_index` (`aim`),
  KEY `graduate_surveys_customer_id_index` (`customer_id`),
  KEY `graduate_surveys_status_index` (`status`),
  KEY `graduate_surveys_created_at_index` (`created_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO `migrations` (`migration`, `batch`)
SELECT '2026_11_03_120000_create_graduate_surveys_table', nb.`batch`
FROM (SELECT COALESCE(MAX(`batch`), 0) + 1 AS `batch` FROM `migrations`) nb
WHERE NOT EXISTS (SELECT 1 FROM `migrations` WHERE `migration` = '2026_11_03_120000_create_graduate_surveys_table');

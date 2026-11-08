-- =====================================================================================================================
-- Enrollzy: calling command center (role-wise dashboards, Live Monitor, Team Performance, Notice Board)   2 Nov 2026
--
-- 1. calling_status.is_connected: a call with this status reached the student or parent (the connect rate).
--    Set once for the statuses where someone answered; change it any time on Students > Calling Status.
-- 2. settings: the monitor's rules (shift start, late grace, idle and break limits, overdue limit, default daily calls).
-- 3. notices + notice_acks: the Notice Board, with acknowledgement.
--
-- The same changes as migration 2026_11_02_130000_calling_command_center.
-- Import in phpMyAdmin (select the database, Import, choose this file). Safe to import again: a second import changes
-- nothing (the connected statuses are set only the first time, so later edits are kept).
-- =====================================================================================================================

-- 1. calling_status.is_connected (added only when missing)
SET @sql = (SELECT IF(COUNT(*) = 0,
    'ALTER TABLE `calling_status` ADD COLUMN `is_connected` tinyint(1) NOT NULL DEFAULT 0 AFTER `calling_action_id`', 'SELECT 1')
  FROM information_schema.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'calling_status' AND COLUMN_NAME = 'is_connected');
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

-- the statuses where the person was reached (first import only)
UPDATE `calling_status` SET `is_connected` = 1
WHERE `name` IN ('Call Back Requested', 'Next Year', 'Language Barrier', 'Financial Issue', 'Admission Taken', 'Study Abroad',
                 'Positive Calls', 'Not Interested', 'Neutral Call', 'Wrong Details')
  AND NOT EXISTS (SELECT 1 FROM `migrations` WHERE `migration` = '2026_11_02_130000_calling_command_center');

-- 2. the monitor's rules on settings (each added only when missing)
SET @sql = (SELECT IF(COUNT(*) = 0, 'ALTER TABLE `settings` ADD COLUMN `calling_shift_start` varchar(5) NOT NULL DEFAULT ''10:00''', 'SELECT 1')
  FROM information_schema.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'settings' AND COLUMN_NAME = 'calling_shift_start');
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;
SET @sql = (SELECT IF(COUNT(*) = 0, 'ALTER TABLE `settings` ADD COLUMN `calling_grace_min` smallint unsigned NOT NULL DEFAULT 15', 'SELECT 1')
  FROM information_schema.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'settings' AND COLUMN_NAME = 'calling_grace_min');
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;
SET @sql = (SELECT IF(COUNT(*) = 0, 'ALTER TABLE `settings` ADD COLUMN `calling_idle_min` smallint unsigned NOT NULL DEFAULT 30', 'SELECT 1')
  FROM information_schema.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'settings' AND COLUMN_NAME = 'calling_idle_min');
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;
SET @sql = (SELECT IF(COUNT(*) = 0, 'ALTER TABLE `settings` ADD COLUMN `calling_break_min` smallint unsigned NOT NULL DEFAULT 45', 'SELECT 1')
  FROM information_schema.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'settings' AND COLUMN_NAME = 'calling_break_min');
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;
SET @sql = (SELECT IF(COUNT(*) = 0, 'ALTER TABLE `settings` ADD COLUMN `calling_overdue_max` smallint unsigned NOT NULL DEFAULT 15', 'SELECT 1')
  FROM information_schema.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'settings' AND COLUMN_NAME = 'calling_overdue_max');
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;
SET @sql = (SELECT IF(COUNT(*) = 0, 'ALTER TABLE `settings` ADD COLUMN `calling_daily_target` smallint unsigned NOT NULL DEFAULT 60', 'SELECT 1')
  FROM information_schema.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'settings' AND COLUMN_NAME = 'calling_daily_target');
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

-- 3. Notice Board
CREATE TABLE IF NOT EXISTS `notices` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `posted_by` bigint(20) unsigned NOT NULL,
  `audience` enum('all','team','user') NOT NULL DEFAULT 'all',
  `audience_id` bigint(20) unsigned DEFAULT NULL,
  `title` varchar(200) NOT NULL,
  `body` text NOT NULL,
  `priority` enum('normal','important','urgent') NOT NULL DEFAULT 'normal',
  `pinned` tinyint(1) NOT NULL DEFAULT 0,
  `require_ack` tinyint(1) NOT NULL DEFAULT 0,
  `expires_on` date DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `notices_audience_audience_id_index` (`audience`, `audience_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `notice_acks` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `notice_id` bigint(20) unsigned NOT NULL,
  `staff_id` bigint(20) unsigned NOT NULL,
  `acked_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `notice_acks_notice_id_staff_id_unique` (`notice_id`, `staff_id`),
  CONSTRAINT `notice_acks_notice_id_foreign` FOREIGN KEY (`notice_id`) REFERENCES `notices` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO `migrations` (`migration`, `batch`)
SELECT '2026_11_02_130000_calling_command_center', nb.`batch`
FROM (SELECT COALESCE(MAX(`batch`), 0) + 1 AS `batch` FROM `migrations`) nb
WHERE NOT EXISTS (SELECT 1 FROM `migrations` WHERE `migration` = '2026_11_02_130000_calling_command_center');

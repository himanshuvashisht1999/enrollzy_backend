-- =====================================================================================================================
-- Enrollzy: exam module fixes (v2: safe to import again)                                              31 Oct 2026
--
-- * Events keep the order set on the cycle page (drag to reorder), on the CRM and on the website.
-- * Each event type says which heading of the website's "Important dates" it shows under, so a type added on the
--   Event Types page (or from the Add Event dialog) shows in the right group instead of "Other dates".
-- * One exam pattern per paper: a paper name (JEE Main "Paper 1 (B.E./B.Tech)", "Paper 2A (B.Arch)"), each with its own
--   mode; a section can set its own mode too (Paper 2A's drawing test is pen and paper).
--
-- The same change as migration 2026_10_31_150000_add_event_order_groups_and_paper_names_to_exam_module.
-- Import in phpMyAdmin (select the database, Import, choose this file), or mysql -u USER -p DATABASE < this-file.sql.
--
-- Safe to import again: a column that already exists is skipped, events are numbered only in cycles not numbered yet,
-- and the migration is recorded once. So an import that stopped half way can simply be run again.
-- Works on MySQL 5.7 / 8 and MariaDB 10.x.
-- =====================================================================================================================

-- 1. New columns (each only if missing)
SET @sql = IF((SELECT COUNT(*) FROM information_schema.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'exm_cycle_events' AND COLUMN_NAME = 'sort_order') = 0,
    'ALTER TABLE `exm_cycle_events` ADD COLUMN `sort_order` INT UNSIGNED NOT NULL DEFAULT 0 AFTER `label`', 'DO 0');
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

SET @sql = IF((SELECT COUNT(*) FROM information_schema.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'exm_event_types' AND COLUMN_NAME = 'date_group') = 0,
    'ALTER TABLE `exm_event_types` ADD COLUMN `date_group` VARCHAR(20) NULL AFTER `applies_to`', 'DO 0');
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

SET @sql = IF((SELECT COUNT(*) FROM information_schema.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'exm_patterns' AND COLUMN_NAME = 'paper_name') = 0,
    'ALTER TABLE `exm_patterns` ADD COLUMN `paper_name` VARCHAR(150) NULL AFTER `stage_id`', 'DO 0');
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

-- 2. The group of each event type that has none yet (as the website grouped them until now)
UPDATE `exm_event_types` SET `date_group` = 'application' WHERE `date_group` IS NULL AND `slug` IN ('registration', 'late-fee-window', 'correction-window', 'fee-deposit-payment');
UPDATE `exm_event_types` SET `date_group` = 'exam' WHERE `date_group` IS NULL AND `slug` IN ('city-intimation-slip', 'admit-card', 'exam-date');
UPDATE `exm_event_types` SET `date_group` = 'result' WHERE `date_group` IS NULL AND `slug` IN ('provisional-answer-key', 'answer-key-objection', 'final-answer-key', 'result', 'scorecard', 'merit-list');
UPDATE `exm_event_types` SET `date_group` = 'counselling' WHERE `date_group` IS NULL AND `applies_to` = 'counselling';
UPDATE `exm_event_types` SET `date_group` = 'other' WHERE `date_group` IS NULL;

-- 3. Number each exam cycle's events 1, 2, 3 ... by event type order (registration ... result), then date (undated
--    last), then when added. Only cycles whose events are all still 0, so an order already set is never changed.
--    Counselling dates (no cycle_id) are left as they are.
UPDATE `exm_cycle_events` e
JOIN (
    SELECT a.`id`, COUNT(b.`id`) + 1 AS rn
    FROM `exm_cycle_events` a
    LEFT JOIN `exm_event_types` ta ON ta.`id` = a.`event_type_id`
    JOIN `exm_cycle_events` b ON b.`cycle_id` = a.`cycle_id` AND b.`id` <> a.`id`
    LEFT JOIN `exm_event_types` tb ON tb.`id` = b.`event_type_id`
    WHERE a.`cycle_id` IS NOT NULL
      AND (COALESCE(tb.`sort_order`, -1), b.`start_at` IS NULL, COALESCE(b.`start_at`, '1000-01-01'), b.`id`)
        < (COALESCE(ta.`sort_order`, -1), a.`start_at` IS NULL, COALESCE(a.`start_at`, '1000-01-01'), a.`id`)
    GROUP BY a.`id`
    UNION ALL
    -- the first event of each cycle (nothing comes before it)
    SELECT a.`id`, 1 FROM `exm_cycle_events` a
    LEFT JOIN `exm_event_types` ta ON ta.`id` = a.`event_type_id`
    WHERE a.`cycle_id` IS NOT NULL AND NOT EXISTS (
        SELECT 1 FROM `exm_cycle_events` b LEFT JOIN `exm_event_types` tb ON tb.`id` = b.`event_type_id`
        WHERE b.`cycle_id` = a.`cycle_id` AND b.`id` <> a.`id`
          AND (COALESCE(tb.`sort_order`, -1), b.`start_at` IS NULL, COALESCE(b.`start_at`, '1000-01-01'), b.`id`)
            < (COALESCE(ta.`sort_order`, -1), a.`start_at` IS NULL, COALESCE(a.`start_at`, '1000-01-01'), a.`id`))
) x ON x.`id` = e.`id`
JOIN (SELECT `cycle_id` FROM `exm_cycle_events` WHERE `cycle_id` IS NOT NULL GROUP BY `cycle_id` HAVING MAX(`sort_order`) = 0) fresh ON fresh.`cycle_id` = e.`cycle_id`
SET e.`sort_order` = x.rn;

-- 4. Record the migration as run (once), so "php artisan migrate" does not run it again.
INSERT INTO `migrations` (`migration`, `batch`)
SELECT '2026_10_31_150000_add_event_order_groups_and_paper_names_to_exam_module', nb.`batch`
FROM (SELECT COALESCE(MAX(`batch`), 0) + 1 AS `batch` FROM `migrations`) nb
WHERE NOT EXISTS (SELECT 1 FROM `migrations` WHERE `migration` = '2026_10_31_150000_add_event_order_groups_and_paper_names_to_exam_module');

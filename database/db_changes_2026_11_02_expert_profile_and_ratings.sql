-- =====================================================================================================================
-- Enrollzy: expert profile clean-up and open ratings                                                    2 Nov 2026
--
-- 1. experts.website_url: the expert's own website (Content tab; the "Become an expert" form already asked for it).
-- 2. expert_applications.profile: the form's new optional sections (date of birth, subjects, top achievements of
--    students, beyond the classroom, social profiles, videos, articles), copied to the expert when approved.
-- 3. reviews.booking_id may be empty: anyone signed in can rate an expert on their profile page, not only after a booking.
-- 4. Gender saved as "male" / "female" by the website form becomes "Male" / "Female", so the CRM shows it.
--
-- The same changes as migration 2026_11_02_120000_expert_profile_fields_and_open_ratings.
-- Import in phpMyAdmin (select the database, Import, choose this file). Safe to import again.
-- =====================================================================================================================

-- 1. experts.website_url (added only when missing)
SET @sql = (SELECT IF(COUNT(*) = 0,
    'ALTER TABLE `experts` ADD COLUMN `website_url` varchar(300) DEFAULT NULL AFTER `telegram_channel_url`',
    'SELECT 1')
  FROM information_schema.COLUMNS
  WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'experts' AND COLUMN_NAME = 'website_url');
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

-- 2. expert_applications.profile (added only when missing)
SET @sql = (SELECT IF(COUNT(*) = 0,
    'ALTER TABLE `expert_applications` ADD COLUMN `profile` json DEFAULT NULL AFTER `website_url`',
    'SELECT 1')
  FROM information_schema.COLUMNS
  WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'expert_applications' AND COLUMN_NAME = 'profile');
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

-- 3. a rating without a booking (running this again changes nothing)
ALTER TABLE `reviews` MODIFY `booking_id` bigint(20) unsigned NULL;

-- 4. one spelling of gender
UPDATE `experts` SET `gender` = CONCAT(UPPER(LEFT(`gender`, 1)), LOWER(SUBSTRING(`gender`, 2)))
WHERE BINARY `gender` IN ('male', 'female', 'other');

INSERT INTO `migrations` (`migration`, `batch`)
SELECT '2026_11_02_120000_expert_profile_fields_and_open_ratings', nb.`batch`
FROM (SELECT COALESCE(MAX(`batch`), 0) + 1 AS `batch` FROM `migrations`) nb
WHERE NOT EXISTS (SELECT 1 FROM `migrations` WHERE `migration` = '2026_11_02_120000_expert_profile_fields_and_open_ratings');

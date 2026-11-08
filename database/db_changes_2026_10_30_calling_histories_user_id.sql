-- =====================================================================================================================
-- Enrollzy: fix the Lead Assignment counts that never load (e.g. "Lukhnow University UG")      30 Oct 2026
--
-- calling_histories.user_id holds a lead's id (users.id) but is a varchar(255) with no index. Each "has this lead been
-- called?" check compares a number with text, so MySQL reads the whole call history for every lead: for a category of
-- ~63,000 leads the count runs for minutes and the Pool size / Assigned / Available boxes stay empty.
-- This makes user_id a number column with an index (the same as migration
-- 2026_10_31_140000_make_calling_histories_user_id_numeric). Tested on the 24 Oct data: 4+ minutes -> 0.4 seconds.
--
-- Import once (phpMyAdmin > Import, or mysql -u USER -p DATABASE < this-file.sql). Take a backup first.
-- The change takes about a second for ~10,000 call records.
-- =====================================================================================================================

-- 1. Check: this must return no rows. If it returns any, those call records do not hold a lead id: fix or delete them
--    first (with strict SQL mode the ALTER below would stop on them anyway, and change nothing).
SELECT id, user_id FROM `calling_histories` WHERE user_id IS NULL OR user_id = '' OR user_id NOT REGEXP '^[0-9]+$';

-- 2. The fix: a number column like users.id, with an index.
ALTER TABLE `calling_histories`
    MODIFY `user_id` BIGINT UNSIGNED NOT NULL,
    ADD INDEX `calling_histories_user_id_index` (`user_id`);

-- 3. Record the migration as run, so "php artisan migrate" does not run it again.
INSERT INTO `migrations` (`migration`, `batch`)
SELECT '2026_10_31_140000_make_calling_histories_user_id_numeric', COALESCE(MAX(`batch`), 0) + 1 FROM `migrations`;

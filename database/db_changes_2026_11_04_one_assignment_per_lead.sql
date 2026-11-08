-- =====================================================================================================================
-- Enrollzy: calling - one assignment per lead   4 Nov 2026
--
-- lead_assignments.customer_id becomes unique, so a lead can only ever sit with one staff member. Every assign and
-- reassign already updates that one row; this makes a second row impossible.
-- Step 1 removes older duplicate rows if any exist (the newest stays). The check on 4 Nov found none, so it removes nothing.
--
-- The same change as migration 2026_11_04_120000_unique_lead_assignment_per_customer.
-- Import in phpMyAdmin (select the database, Import, choose this file). Safe to import again: a second import changes nothing.
-- =====================================================================================================================

-- 1. older duplicate assignment rows of a lead (none expected)
DELETE la FROM lead_assignments la
JOIN lead_assignments newer ON newer.customer_id = la.customer_id AND newer.id > la.id;

-- 2. one row per lead from now on (added only if it is not there yet; works on MySQL and MariaDB)
SET @has_unique := (SELECT COUNT(*) FROM information_schema.statistics WHERE table_schema = DATABASE()
  AND table_name = 'lead_assignments' AND index_name = 'lead_assignments_customer_id_unique');
SET @add_unique := IF(@has_unique = 0, 'ALTER TABLE lead_assignments ADD UNIQUE INDEX lead_assignments_customer_id_unique (customer_id)', 'SELECT 1');
PREPARE add_unique FROM @add_unique;
EXECUTE add_unique;
DEALLOCATE PREPARE add_unique;

-- the migration as run, so `php artisan migrate` does not try it again
INSERT INTO `migrations` (`migration`, `batch`)
SELECT '2026_11_04_120000_unique_lead_assignment_per_customer', nb.`batch`
FROM (SELECT COALESCE(MAX(`batch`), 0) + 1 AS `batch` FROM `migrations`) nb
WHERE NOT EXISTS (SELECT 1 FROM `migrations` WHERE `migration` = '2026_11_04_120000_unique_lead_assignment_per_customer');

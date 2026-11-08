-- =====================================================================================================================
-- Enrollzy: coaching institutes, branch level (Phase 2)   6 Nov 2026
--
-- campuses: locality, latitude / longitude, opening and closing time, working days, branch head name and phone,
-- branch WhatsApp, lead email, reading room (and hours), test centre, nearby hostels / PGs, rating and review count.
-- Each column is added only if it is not there yet. Branch photos and videos use the organisation_media table from Phase 1.
--
-- The same changes as migration 2026_11_06_140000_institute_branch_fields.
-- Import in phpMyAdmin (select the database, Import, choose this file). Safe to import again: a second import changes nothing.
-- Works on MySQL and MariaDB.
-- =====================================================================================================================

SET @x := (SELECT COUNT(*) FROM information_schema.columns WHERE table_schema = DATABASE() AND table_name = 'campuses' AND column_name = 'locality');
SET @s := IF(@x = 0, 'ALTER TABLE `campuses` ADD COLUMN `locality` VARCHAR(255) NULL', 'SELECT 1');
PREPARE st FROM @s; EXECUTE st; DEALLOCATE PREPARE st;
SET @x := (SELECT COUNT(*) FROM information_schema.columns WHERE table_schema = DATABASE() AND table_name = 'campuses' AND column_name = 'latitude');
SET @s := IF(@x = 0, 'ALTER TABLE `campuses` ADD COLUMN `latitude` DECIMAL(10,7) NULL', 'SELECT 1');
PREPARE st FROM @s; EXECUTE st; DEALLOCATE PREPARE st;
SET @x := (SELECT COUNT(*) FROM information_schema.columns WHERE table_schema = DATABASE() AND table_name = 'campuses' AND column_name = 'longitude');
SET @s := IF(@x = 0, 'ALTER TABLE `campuses` ADD COLUMN `longitude` DECIMAL(10,7) NULL', 'SELECT 1');
PREPARE st FROM @s; EXECUTE st; DEALLOCATE PREPARE st;
SET @x := (SELECT COUNT(*) FROM information_schema.columns WHERE table_schema = DATABASE() AND table_name = 'campuses' AND column_name = 'opening_time');
SET @s := IF(@x = 0, 'ALTER TABLE `campuses` ADD COLUMN `opening_time` TIME NULL', 'SELECT 1');
PREPARE st FROM @s; EXECUTE st; DEALLOCATE PREPARE st;
SET @x := (SELECT COUNT(*) FROM information_schema.columns WHERE table_schema = DATABASE() AND table_name = 'campuses' AND column_name = 'closing_time');
SET @s := IF(@x = 0, 'ALTER TABLE `campuses` ADD COLUMN `closing_time` TIME NULL', 'SELECT 1');
PREPARE st FROM @s; EXECUTE st; DEALLOCATE PREPARE st;
SET @x := (SELECT COUNT(*) FROM information_schema.columns WHERE table_schema = DATABASE() AND table_name = 'campuses' AND column_name = 'working_days');
SET @s := IF(@x = 0, 'ALTER TABLE `campuses` ADD COLUMN `working_days` LONGTEXT NULL', 'SELECT 1');
PREPARE st FROM @s; EXECUTE st; DEALLOCATE PREPARE st;
SET @x := (SELECT COUNT(*) FROM information_schema.columns WHERE table_schema = DATABASE() AND table_name = 'campuses' AND column_name = 'branch_head_name');
SET @s := IF(@x = 0, 'ALTER TABLE `campuses` ADD COLUMN `branch_head_name` VARCHAR(255) NULL', 'SELECT 1');
PREPARE st FROM @s; EXECUTE st; DEALLOCATE PREPARE st;
SET @x := (SELECT COUNT(*) FROM information_schema.columns WHERE table_schema = DATABASE() AND table_name = 'campuses' AND column_name = 'branch_head_phone');
SET @s := IF(@x = 0, 'ALTER TABLE `campuses` ADD COLUMN `branch_head_phone` VARCHAR(30) NULL', 'SELECT 1');
PREPARE st FROM @s; EXECUTE st; DEALLOCATE PREPARE st;
SET @x := (SELECT COUNT(*) FROM information_schema.columns WHERE table_schema = DATABASE() AND table_name = 'campuses' AND column_name = 'whatsapp_number');
SET @s := IF(@x = 0, 'ALTER TABLE `campuses` ADD COLUMN `whatsapp_number` VARCHAR(30) NULL', 'SELECT 1');
PREPARE st FROM @s; EXECUTE st; DEALLOCATE PREPARE st;
SET @x := (SELECT COUNT(*) FROM information_schema.columns WHERE table_schema = DATABASE() AND table_name = 'campuses' AND column_name = 'lead_email');
SET @s := IF(@x = 0, 'ALTER TABLE `campuses` ADD COLUMN `lead_email` VARCHAR(255) NULL', 'SELECT 1');
PREPARE st FROM @s; EXECUTE st; DEALLOCATE PREPARE st;
SET @x := (SELECT COUNT(*) FROM information_schema.columns WHERE table_schema = DATABASE() AND table_name = 'campuses' AND column_name = 'reading_room_available');
SET @s := IF(@x = 0, 'ALTER TABLE `campuses` ADD COLUMN `reading_room_available` TINYINT(1) NOT NULL DEFAULT 0', 'SELECT 1');
PREPARE st FROM @s; EXECUTE st; DEALLOCATE PREPARE st;
SET @x := (SELECT COUNT(*) FROM information_schema.columns WHERE table_schema = DATABASE() AND table_name = 'campuses' AND column_name = 'reading_room_hours');
SET @s := IF(@x = 0, 'ALTER TABLE `campuses` ADD COLUMN `reading_room_hours` VARCHAR(100) NULL', 'SELECT 1');
PREPARE st FROM @s; EXECUTE st; DEALLOCATE PREPARE st;
SET @x := (SELECT COUNT(*) FROM information_schema.columns WHERE table_schema = DATABASE() AND table_name = 'campuses' AND column_name = 'test_centre_available');
SET @s := IF(@x = 0, 'ALTER TABLE `campuses` ADD COLUMN `test_centre_available` TINYINT(1) NOT NULL DEFAULT 0', 'SELECT 1');
PREPARE st FROM @s; EXECUTE st; DEALLOCATE PREPARE st;
SET @x := (SELECT COUNT(*) FROM information_schema.columns WHERE table_schema = DATABASE() AND table_name = 'campuses' AND column_name = 'nearby_accommodations');
SET @s := IF(@x = 0, 'ALTER TABLE `campuses` ADD COLUMN `nearby_accommodations` LONGTEXT NULL', 'SELECT 1');
PREPARE st FROM @s; EXECUTE st; DEALLOCATE PREPARE st;
SET @x := (SELECT COUNT(*) FROM information_schema.columns WHERE table_schema = DATABASE() AND table_name = 'campuses' AND column_name = 'average_rating');
SET @s := IF(@x = 0, 'ALTER TABLE `campuses` ADD COLUMN `average_rating` DECIMAL(3,2) NULL', 'SELECT 1');
PREPARE st FROM @s; EXECUTE st; DEALLOCATE PREPARE st;
SET @x := (SELECT COUNT(*) FROM information_schema.columns WHERE table_schema = DATABASE() AND table_name = 'campuses' AND column_name = 'total_reviews');
SET @s := IF(@x = 0, 'ALTER TABLE `campuses` ADD COLUMN `total_reviews` INT UNSIGNED NULL', 'SELECT 1');
PREPARE st FROM @s; EXECUTE st; DEALLOCATE PREPARE st;

-- the migration as run, so `php artisan migrate` does not try it again
INSERT INTO `migrations` (`migration`, `batch`)
SELECT '2026_11_06_140000_institute_branch_fields', nb.`batch`
FROM (SELECT COALESCE(MAX(`batch`), 0) + 1 AS `batch` FROM `migrations`) nb
WHERE NOT EXISTS (SELECT 1 FROM `migrations` WHERE `migration` = '2026_11_06_140000_institute_branch_fields');

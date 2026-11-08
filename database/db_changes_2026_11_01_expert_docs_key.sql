-- =====================================================================================================================
-- Enrollzy: expert documents go through the CRM                                                        1 Nov 2026
--
-- On the live server each site can only reach its own folders, so the website could not save expert documents in the
-- CRM's folder (staff saw "404" when opening them). Now the CRM keeps every expert document and the website sends
-- uploads to it and fetches files from it over HTTPS (/api/v1/expert-docs), each request signed with this key.
-- The key is filled with a random value on first use; nothing to set by hand.
--
-- The same change as migration 2026_11_01_100000_add_expert_docs_key_to_settings.
-- Import in phpMyAdmin (select the database, Import, choose this file). Safe to import again.
-- =====================================================================================================================

SET @sql = IF((SELECT COUNT(*) FROM information_schema.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'settings' AND COLUMN_NAME = 'expert_docs_key') = 0,
    'ALTER TABLE `settings` ADD COLUMN `expert_docs_key` VARCHAR(64) NULL', 'DO 0');
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

INSERT INTO `migrations` (`migration`, `batch`)
SELECT '2026_11_01_100000_add_expert_docs_key_to_settings', nb.`batch`
FROM (SELECT COALESCE(MAX(`batch`), 0) + 1 AS `batch` FROM `migrations`) nb
WHERE NOT EXISTS (SELECT 1 FROM `migrations` WHERE `migration` = '2026_11_01_100000_add_expert_docs_key_to_settings');

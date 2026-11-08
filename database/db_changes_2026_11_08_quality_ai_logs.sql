-- =====================================================================================================
-- Rankings & Accreditation: keep what each "Fill by AI" run found, for reference in the CRM
-- Date: 8 Nov 2026
--
-- Adds ONE new table, quality_ai_logs. Nothing else is changed. Shown only on the CRM's Rankings & Accreditation
-- page ("Past AI results"), never on the website.
--
-- Safe to run more than once.
-- =====================================================================================================

CREATE TABLE IF NOT EXISTS quality_ai_logs (
    id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
    entity_type VARCHAR(20) NOT NULL,
    entity_id VARCHAR(40) NOT NULL,
    action VARCHAR(20) NOT NULL,
    engine VARCHAR(30) NOT NULL,
    kinds TEXT NULL,
    steps TEXT NULL,
    notes LONGTEXT NULL,
    `rows` LONGTEXT NULL,
    sources LONGTEXT NULL,
    admin_id BIGINT UNSIGNED NULL,
    created_at TIMESTAMP NULL,
    updated_at TIMESTAMP NULL,
    KEY quality_ai_logs_entity (entity_type, entity_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- record this migration as done, so "php artisan migrate" never tries it again
INSERT INTO migrations (migration, batch)
SELECT '2026_11_08_100000_create_quality_ai_logs_table', t.next_batch
  FROM (SELECT COALESCE(MAX(batch), 0) + 1 AS next_batch FROM migrations) t
 WHERE NOT EXISTS (SELECT 1 FROM migrations WHERE migration = '2026_11_08_100000_create_quality_ai_logs_table');

-- check (should show 0 for a new table)
SELECT COUNT(*) AS saved_ai_results FROM quality_ai_logs;

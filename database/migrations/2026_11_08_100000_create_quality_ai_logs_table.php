<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * quality_ai_logs: what each "Fill by AI" run on a Rankings & Accreditation page found, kept for reference in the CRM only
 * (never shown on the website), so the same research is not run again. E.g. "NIT Jalandhar is an Institution of National
 * Importance ... does not require AICTE approval".
 * Raw SQL, so database/db_changes_2026_11_08_quality_ai_logs.sql does exactly the same.
 */
return new class extends Migration
{
    public function up(): void
    {
        DB::statement(<<<'SQL'
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
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
SQL);
    }

    public function down(): void
    {
        Schema::dropIfExists('quality_ai_logs');
    }
};

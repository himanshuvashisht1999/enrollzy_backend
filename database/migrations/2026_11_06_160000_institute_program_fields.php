<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Coaching institute programmes (organisation_courses of type 3):
 *  - new columns: product type, target classes, access validity, demo class and video, test-series details, fee details
 *    (early-bird / scholarship %, EMI, GST included, what the fee covers) and the fee range as two numbers;
 *  - selections, highest rank and selection rate become numbers (existing text such as "1,200+" or "AIR 15" is converted;
 *    what can't be read becomes empty);
 *  - one mode field: coaching programmes keep Delivery Mode (Offline / Online / Hybrid / Recorded), filled from the older
 *    Study Mode where it was empty;
 *  - tables course_batches, course_results (year-wise) and course_toppers.
 * Raw SQL, so database/db_changes_2026_11_06_institute_program_fields.sql (written from these lists) does exactly the same.
 */
return new class extends Migration
{
    /** organisation_courses columns added: name => SQL type */
    public const ADD = [
        'product_type' => 'VARCHAR(50) NULL',
        'target_classes' => 'LONGTEXT NULL',
        'access_validity_months' => 'INT UNSIGNED NULL',
        'demo_class_available' => 'TINYINT(1) NOT NULL DEFAULT 0',
        'demo_video_url' => 'VARCHAR(255) NULL',
        'total_tests' => 'INT UNSIGNED NULL',
        'sectional_tests_count' => 'INT UNSIGNED NULL',
        'all_india_rank_available' => 'TINYINT(1) NOT NULL DEFAULT 0',
        'performance_analysis_available' => 'TINYINT(1) NOT NULL DEFAULT 0',
        'early_bird_discount_percent' => 'DECIMAL(5,2) NULL',
        'scholarship_discount_max_percent' => 'DECIMAL(5,2) NULL',
        'emi_amount' => 'INT UNSIGNED NULL',
        'emi_tenure_months' => 'INT UNSIGNED NULL',
        'gst_included' => 'TINYINT(1) NOT NULL DEFAULT 0',
        'fee_includes' => 'LONGTEXT NULL',
        'fee_min' => 'INT UNSIGNED NULL',
        'fee_max' => 'INT UNSIGNED NULL',
    ];

    /** text columns that become numbers: name => [new SQL type, the number to keep from the text] */
    public const CONVERT = [
        'total_selections_all_time' => ['INT UNSIGNED NULL', '[0-9]+'],
        'selections_last_year' => ['INT UNSIGNED NULL', '[0-9]+'],
        'highest_rank_achieved' => ['INT UNSIGNED NULL', '[0-9]+'],
        'average_selection_rate' => ['DECIMAL(5,2) NULL', '[0-9]+([.][0-9]+)?'],
    ];

    /** the statements, in order (the SQL file wraps each in "only if not done yet") */
    public static function statements(): array
    {
        $out = [];
        foreach (self::ADD as $col => $type) {
            $out[] = ['add', $col, "ALTER TABLE `organisation_courses` ADD COLUMN `$col` $type"];
        }
        foreach (self::CONVERT as $col => [$type, $re]) {
            // keep the first number in the text (commas removed); nothing readable, or too big for the column, becomes empty
            $max = str_starts_with($type, 'DECIMAL') ? '999.99' : '4294967295';
            $num = "REGEXP_SUBSTR(REPLACE(`$col`, ',', ''), '$re')";
            $out[] = ['convert', $col, "UPDATE `organisation_courses` SET `$col` = CASE WHEN $num IS NULL OR $num = '' OR $num + 0 > $max THEN NULL ELSE $num END"];
            $out[] = ['convert', $col, "ALTER TABLE `organisation_courses` MODIFY `$col` $type"];
        }
        // one mode field for coaching programmes: Delivery Mode, from the older Study Mode where it was empty
        $out[] = ['always', null, "UPDATE `organisation_courses` oc JOIN `organisations` o ON o.`id` = oc.`organisation_id` SET oc.`delivery_mode` = CASE oc.`mode` "
            . "WHEN 'Online' THEN 'Online' WHEN 'Distance' THEN 'Recorded' ELSE 'Offline' END "
            . "WHERE o.`organisation_type_id` = 3 AND (oc.`delivery_mode` IS NULL OR oc.`delivery_mode` = '') AND oc.`mode` IS NOT NULL AND oc.`mode` <> ''"];
        $out[] = ['always', null, "UPDATE `organisation_courses` oc JOIN `organisations` o ON o.`id` = oc.`organisation_id` SET oc.`delivery_mode` = 'Offline' "
            . "WHERE o.`organisation_type_id` = 3 AND oc.`delivery_mode` = 'Classroom'"];
        return $out;
    }

    public const TABLES = [
        'course_batches' => "CREATE TABLE IF NOT EXISTS `course_batches` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `organisation_course_id` bigint(20) unsigned NOT NULL,
  `name` varchar(255) DEFAULT NULL,
  `start_date` date DEFAULT NULL,
  `timing` varchar(20) DEFAULT NULL,
  `timing_details` varchar(100) DEFAULT NULL,
  `medium` varchar(30) DEFAULT NULL,
  `total_seats` int(10) unsigned DEFAULT NULL,
  `seats_left` int(10) unsigned DEFAULT NULL,
  `status` varchar(20) NOT NULL DEFAULT 'Open',
  `sort_order` int(11) NOT NULL DEFAULT 0,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `course_batches_organisation_course_id_index` (`organisation_course_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci",
        'course_results' => "CREATE TABLE IF NOT EXISTS `course_results` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `organisation_course_id` bigint(20) unsigned NOT NULL,
  `year` smallint(5) unsigned NOT NULL,
  `selections` int(10) unsigned DEFAULT NULL,
  `top_rank` int(10) unsigned DEFAULT NULL,
  `notes` varchar(255) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `course_results_organisation_course_id_year_unique` (`organisation_course_id`, `year`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci",
        'course_toppers' => "CREATE TABLE IF NOT EXISTS `course_toppers` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `organisation_course_id` bigint(20) unsigned NOT NULL,
  `year` smallint(5) unsigned DEFAULT NULL,
  `name` varchar(255) NOT NULL,
  `rank` int(10) unsigned DEFAULT NULL,
  `exam` varchar(100) DEFAULT NULL,
  `photo_path` varchar(255) DEFAULT NULL,
  `consent` tinyint(1) NOT NULL DEFAULT 0,
  `sort_order` int(11) NOT NULL DEFAULT 0,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `course_toppers_organisation_course_id_index` (`organisation_course_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci",
    ];

    public function up(): void
    {
        foreach (self::statements() as [$kind, $col, $sql]) {
            if ($kind === 'add' && Schema::hasColumn('organisation_courses', $col)) continue;
            if ($kind === 'convert' && Schema::getColumnType('organisation_courses', $col) !== 'varchar') continue;
            DB::statement($sql);
        }
        foreach (self::TABLES as $sql) DB::statement($sql);
    }

    public function down(): void
    {
        foreach (array_keys(self::TABLES) as $t) Schema::dropIfExists($t);
        foreach (array_keys(self::ADD) as $col) {
            if (Schema::hasColumn('organisation_courses', $col)) DB::statement("ALTER TABLE `organisation_courses` DROP COLUMN `$col`");
        }
        foreach (array_keys(self::CONVERT) as $col) DB::statement("ALTER TABLE `organisation_courses` MODIFY `$col` VARCHAR(255) NULL");
    }
};

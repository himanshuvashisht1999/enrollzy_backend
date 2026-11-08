<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Coaching institute branches (campuses of organisation type 3): locality, map coordinates, timings and working days,
 * branch head, branch WhatsApp and lead email, reading room and test centre, nearby hostels / PGs, rating and reviews.
 * (nearest_landmark already exists. Photos and videos use organisation_media with the campus id.)
 */
return new class extends Migration
{
    /** campuses columns added here: name => SQL type (also used to write the matching SQL file) */
    public const COLUMNS = [
        'locality' => 'VARCHAR(255) NULL',
        'latitude' => 'DECIMAL(10,7) NULL',
        'longitude' => 'DECIMAL(10,7) NULL',
        'opening_time' => 'TIME NULL',
        'closing_time' => 'TIME NULL',
        'working_days' => 'LONGTEXT NULL',
        'branch_head_name' => 'VARCHAR(255) NULL',
        'branch_head_phone' => 'VARCHAR(30) NULL',
        'whatsapp_number' => 'VARCHAR(30) NULL',
        'lead_email' => 'VARCHAR(255) NULL',
        'reading_room_available' => 'TINYINT(1) NOT NULL DEFAULT 0',
        'reading_room_hours' => 'VARCHAR(100) NULL',
        'test_centre_available' => 'TINYINT(1) NOT NULL DEFAULT 0',
        'nearby_accommodations' => 'LONGTEXT NULL',
        'average_rating' => 'DECIMAL(3,2) NULL',
        'total_reviews' => 'INT UNSIGNED NULL',
    ];

    public function up(): void
    {
        Schema::table('campuses', function (Blueprint $table) {
            foreach (self::COLUMNS as $name => $type) {
                if (Schema::hasColumn('campuses', $name)) continue;
                match (true) {
                    str_starts_with($type, 'VARCHAR') => $table->string($name, (int) preg_replace('/\D/', '', $type))->nullable(),
                    str_starts_with($type, 'DECIMAL(10') => $table->decimal($name, 10, 7)->nullable(),
                    str_starts_with($type, 'DECIMAL(3') => $table->decimal($name, 3, 2)->nullable(),
                    str_starts_with($type, 'TIME') => $table->time($name)->nullable(),
                    str_starts_with($type, 'TINYINT') => $table->boolean($name)->default(false),
                    str_starts_with($type, 'INT') => $table->unsignedInteger($name)->nullable(),
                    default => $table->json($name)->nullable(),
                };
            }
        });
    }

    public function down(): void
    {
        Schema::table('campuses', function (Blueprint $table) {
            foreach (array_keys(self::COLUMNS) as $name) {
                if (Schema::hasColumn('campuses', $name)) $table->dropColumn($name);
            }
        });
    }
};

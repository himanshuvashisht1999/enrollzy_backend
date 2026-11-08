<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Coaching institutes (organisation type 3), brand level: categories and exams, contact and social links, founder and
 * director, teaching modes, scholarship test, franchise, CIN / Udyam (organisations columns); the Ownership Type master
 * (ownership_types); photos and videos of an institute or a branch (organisation_media, campus_id '' = the brand).
 */
return new class extends Migration
{
    /** organisations columns added here: name => [type, length] */
    public const COLUMNS = [
        'coaching_category_ids' => ['json'], 'exams_prepared_for' => ['json'], 'teaching_modes' => ['json'],
        'whatsapp_number' => ['string', 30],
        'facebook_url' => ['string', 255], 'instagram_url' => ['string', 255], 'youtube_url' => ['string', 255],
        'telegram_url' => ['string', 255], 'play_store_url' => ['string', 255], 'app_store_url' => ['string', 255],
        'founder_name' => ['string', 255], 'director_name' => ['string', 255],
        'scholarship_test_name' => ['string', 255], 'scholarship_test_dates' => ['string', 255], 'scholarship_test_url' => ['string', 255],
        'franchise_available' => ['boolean'], 'franchise_contact_name' => ['string', 255], 'franchise_contact_phone' => ['string', 30],
        'franchise_contact_email' => ['string', 255],
        'cin_number' => ['string', 30], 'udyam_number' => ['string', 30],
    ];

    public const OWNERSHIP_TYPES = ['Private Limited Company', 'Public Limited Company', 'One Person Company (OPC)', 'LLP', 'Partnership',
        'Sole Proprietorship', 'Trust', 'Society', 'Section 8 Company', 'Government'];

    public function up(): void
    {
        Schema::table('organisations', function (Blueprint $table) {
            foreach (self::COLUMNS as $name => $def) {
                if (Schema::hasColumn('organisations', $name)) continue;
                match ($def[0]) {
                    'json' => $table->json($name)->nullable(),
                    'boolean' => $table->boolean($name)->default(false),
                    default => $table->text($name)->nullable(), // TEXT, not VARCHAR: organisations is near MySQL's row-size limit (length is capped in validation)
                };
            }
        });

        if (!Schema::hasTable('ownership_types')) {
            Schema::create('ownership_types', function (Blueprint $table) {
                $table->id();
                $table->string('title')->unique();
                $table->boolean('status')->default(true);
                $table->integer('sort_order')->default(0);
                $table->timestamps();
            });
            foreach (self::OWNERSHIP_TYPES as $i => $title) {
                DB::table('ownership_types')->insert(['title' => $title, 'status' => 1, 'sort_order' => $i + 1, 'created_at' => now(), 'updated_at' => now()]);
            }
        }

        if (!Schema::hasTable('organisation_media')) {
            Schema::create('organisation_media', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('organisation_id');
                $table->string('campus_id', 36)->default(''); // '' = the institute itself; a branch's photos carry its id
                $table->string('type', 10); // photo | video
                $table->string('file_path')->nullable(); // photo
                $table->string('video_url')->nullable(); // video (YouTube / Vimeo link)
                $table->string('caption')->nullable();
                $table->integer('sort_order')->default(0);
                $table->timestamps();
                $table->index(['organisation_id', 'campus_id']);
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('organisation_media');
        Schema::dropIfExists('ownership_types');
        Schema::table('organisations', function (Blueprint $table) {
            foreach (array_keys(self::COLUMNS) as $name) {
                if (Schema::hasColumn('organisations', $name)) $table->dropColumn($name);
            }
        });
    }
};

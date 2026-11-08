<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Expert profile clean-up (2 Nov 2026):
 * - experts.website_url: the expert's own website (the application form already asked for it).
 * - expert_applications.profile: the optional profile sections of the "Become an expert" form (date of birth, subjects,
 *   top achievements of students, beyond the classroom, videos, articles, social profiles), keyed by the experts
 *   column each one fills on approval.
 * - reviews.booking_id may be empty: anyone signed in can rate an expert on their profile, not only after a booking.
 * - gender saved as "male" / "female" by the website form is stored as "Male" / "Female", like the CRM saves it.
 */
return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasColumn('experts', 'website_url')) {
            Schema::table('experts', fn (Blueprint $t) => $t->string('website_url', 300)->nullable()->after('telegram_channel_url'));
        }
        if (!Schema::hasColumn('expert_applications', 'profile')) {
            Schema::table('expert_applications', fn (Blueprint $t) => $t->json('profile')->nullable()->after('website_url'));
        }
        DB::statement('ALTER TABLE reviews MODIFY booking_id BIGINT UNSIGNED NULL');
        DB::statement("UPDATE experts SET gender = CONCAT(UPPER(LEFT(gender, 1)), LOWER(SUBSTRING(gender, 2))) WHERE gender IN ('male', 'female', 'other')");
    }

    public function down(): void
    {
        Schema::table('experts', fn (Blueprint $t) => $t->dropColumn('website_url'));
        Schema::table('expert_applications', fn (Blueprint $t) => $t->dropColumn('profile'));
    }
};

<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * What the website's experts list shows and filters by: the expert's city, a one-line "Helps with", their areas of
 * expertise and the classes they guide (fixed choices, Expert::EXPERTISE_AREAS / Expert::LEVELS), and whether they
 * offer a free intro call. Consultation (online / in person) uses the existing session_modes column, and students
 * guided the existing no_of_students_counseled.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('experts', function (Blueprint $t) {
            if (!Schema::hasColumn('experts', 'city')) $t->string('city', 100)->nullable()->after('contact_number');
            if (!Schema::hasColumn('experts', 'state')) $t->string('state', 100)->nullable()->after('city');
            if (!Schema::hasColumn('experts', 'helps_with')) $t->string('helps_with', 200)->nullable()->after('short_bio');
            if (!Schema::hasColumn('experts', 'expertise_areas')) $t->json('expertise_areas')->nullable()->after('expert_category_id');
            if (!Schema::hasColumn('experts', 'levels')) $t->json('levels')->nullable()->after('expertise_areas');
            if (!Schema::hasColumn('experts', 'offers_free_intro')) $t->boolean('offers_free_intro')->default(false)->after('price_per_min');
            if (!Schema::hasColumn('experts', 'free_intro_minutes')) $t->unsignedSmallInteger('free_intro_minutes')->default(15)->after('offers_free_intro');
        });
    }

    public function down(): void
    {
        Schema::table('experts', fn (Blueprint $t) => $t->dropColumn(['city', 'state', 'helps_with', 'expertise_areas', 'levels', 'offers_free_intro', 'free_intro_minutes']));
    }
};

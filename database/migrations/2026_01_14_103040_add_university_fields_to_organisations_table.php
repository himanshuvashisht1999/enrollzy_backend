<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('organisations', function (Blueprint $table) {});
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('organisations', function (Blueprint $table) {
             $table->dropColumn([
                'university_id', 'brand_name', 'short_name', 'logo_url', 'cover_image_url',
                'established_year', 'university_type', 'ownership_type', 'about_university',
                'vision_mission', 'core_values',
                'degree_awarding_authority', 'ugc_recognized', 'ugc_approval_number', 'aicte_approved',
                'naac_accredited', 'naac_grade', 'nirf_rank_overall', 'nirf_rank_category',
                'international_accreditations', 'statutory_approvals', 'recognition_documents',
                'governing_body_name', 'chancellor_name', 'vice_chancellor_name', 'autonomous_status',
                'university_category', 'number_of_campuses', 'number_of_constituent_colleges',
                'number_of_affiliated_colleges',
                'levels_offered'
             ]);
        });
    }
};

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
        Schema::table('organisations', function (Blueprint $table) {
            // A. Core Identity
            $table->uuid('university_id')->nullable();
            $table->string('brand_name')->nullable();
            $table->string('short_name')->nullable();
            $table->string('logo_url')->nullable();
            $table->string('cover_image_url')->nullable();
            $table->integer('established_year')->nullable();
            $table->string('university_type')->nullable(); // Central, State, etc.
            $table->string('ownership_type')->nullable(); // Government, Private, etc.
            $table->text('about_university')->nullable();
            $table->text('vision_mission')->nullable();
            $table->json('core_values')->nullable();

            // B. Legal & Regulatory
            $table->boolean('degree_awarding_authority')->nullable();
            $table->boolean('ugc_recognized')->nullable();
            $table->string('ugc_approval_number')->nullable();
            $table->boolean('aicte_approved')->nullable();
            $table->boolean('naac_accredited')->nullable();
            $table->string('naac_grade')->nullable();
            $table->integer('nirf_rank_overall')->nullable();
            $table->integer('nirf_rank_category')->nullable();
            $table->json('international_accreditations')->nullable();
            $table->json('statutory_approvals')->nullable();
            $table->json('recognition_documents')->nullable();

            // C. Governance & Structure
            $table->string('governing_body_name')->nullable();
            $table->string('chancellor_name')->nullable();
            $table->string('vice_chancellor_name')->nullable();
            $table->boolean('autonomous_status')->nullable();
            $table->string('university_category')->nullable(); // Teaching, Research, etc.
            $table->integer('number_of_campuses')->nullable();
            $table->integer('number_of_constituent_colleges')->nullable();
            $table->integer('number_of_affiliated_colleges')->nullable();

            // D. Academic Scope
            $table->json('levels_offered')->nullable(); // Diploma, UG, PG, Doctoral
        });
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

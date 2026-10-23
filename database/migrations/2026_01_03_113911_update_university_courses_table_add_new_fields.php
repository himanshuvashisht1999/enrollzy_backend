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
        Schema::table('university_courses', function (Blueprint $table) {});
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('university_courses', function (Blueprint $table) {
            $table->dropForeign(['program_level_id']);
            $table->dropForeign(['stream_offered_id']);
            $table->dropForeign(['discipline_id']);
            $table->dropForeign(['specialization_id']);
            
            $table->dropColumn([
                'admission_process', 
                'provisional_admission', 
                'eligibility', 
                'fees_structure', 
                'roi', 
                'curriculum', 
                'career_prospects', 
                'placement_details', 
                'program_level_id', 
                'stream_offered_id', 
                'discipline_id', 
                'specialization_id',
                'rating',
                'industrial_collaboration',
                'internship_ranking'
            ]);
        });
    }
};

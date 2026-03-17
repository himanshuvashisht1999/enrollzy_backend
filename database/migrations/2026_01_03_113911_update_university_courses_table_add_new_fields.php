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
        Schema::table('university_courses', function (Blueprint $table) {
            $table->text('admission_process')->nullable();
            $table->boolean('provisional_admission')->default(false);
            $table->text('eligibility')->nullable();
            $table->text('fees_structure')->nullable(); // Explicit fees structure explanation
            $table->enum('roi', ['Low', 'Medium', 'High'])->nullable();
            $table->text('curriculum')->nullable();
            $table->text('career_prospects')->nullable(); // Fixed typo 'Carrier' to 'Career'
            $table->text('placement_details')->nullable();
            
            // Foreign Keys for Master Tables
            $table->foreignId('program_level_id')->nullable()->constrained('program_levels')->nullOnDelete();
            $table->foreignId('stream_offered_id')->nullable()->constrained('stream_offereds')->nullOnDelete();
            $table->foreignId('discipline_id')->nullable()->constrained('disciplines')->nullOnDelete();
            $table->foreignId('specialization_id')->nullable()->constrained('specializations')->nullOnDelete();

            $table->decimal('rating', 3, 1)->nullable(); // e.g. 4.5
            $table->text('industrial_collaboration')->nullable();
            $table->text('internship_ranking')->nullable();
        });
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

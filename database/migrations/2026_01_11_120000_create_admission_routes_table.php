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
        Schema::create('admission_routes', function (Blueprint $table) {
            $table->id();
            
            // Core Relationships
            $table->foreignId('organisation_id')->constrained()->cascadeOnDelete();
            $table->foreignId('course_id')->constrained()->cascadeOnDelete();
            $table->foreignId('exam_id')->nullable()->constrained('exams')->nullOnDelete(); 
            // Nullable because 'Direct' or 'Merit' routes don't have exams.

            // The Magic Field
            $table->string('exam_source_type')->default('external_exam'); 
            // internal_exam, external_exam, multiple_exams, direct_admission, merit_based

            // Cutoff & Eligibility Logic
            $table->string('min_eligibility_qualification')->nullable(); // e.g. Class 12
            $table->string('min_eligibility_marks')->nullable(); // e.g. 75%
            $table->string('min_exam_rank')->nullable();
            $table->string('min_exam_score')->nullable();
            
            // JSON Data for flexibility
            $table->json('cutoff_year_wise')->nullable(); // [{year: 2024, closing_rank: 5000}]
            $table->json('seat_matrix')->nullable(); // Category wise seats
            
            // Process
            $table->text('admission_process_note')->nullable();
            $table->string('application_url')->nullable();
            $table->string('counselling_authority')->nullable(); // e.g. JoSAA
            
            // Meta
            $table->boolean('is_primary_route')->default(false);
            $table->integer('priority_order')->default(0);
            $table->boolean('status')->default(true);

            $table->timestamps();
            
            // Indexes for faster lookup
            $table->index(['organisation_id', 'course_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('admission_routes');
    }
};

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
        Schema::create('students', function (Blueprint $table) {
            $table->id();
            
            // 1. BASIC IDENTITY
            $table->uuid('student_id')->unique()->nullable(); // Public UUID
            $table->string('full_name');
            $table->string('slug')->unique();
            $table->string('profile_photo_url')->nullable();
            $table->string('gender')->nullable();
            $table->year('year_of_birth')->nullable();
            $table->text('short_intro')->nullable();
            $table->string('city')->nullable();
            $table->string('state')->nullable();

            // 2. ACADEMIC BACKGROUND
            $table->string('current_class')->nullable();
            $table->string('school_name')->nullable();
            $table->string('board')->nullable();
            $table->decimal('previous_year_percentage', 5, 2)->nullable();
            $table->string('stream')->nullable(); // PCB, PCM, Arts
            $table->string('competitive_exam_target')->nullable(); // NEET, JEE etc
            $table->string('attempt_type')->nullable(); // Fresher, Dropper
            $table->year('year_of_admission')->nullable();

            // 3. COACHING INSTITUTE ASSOCIATION
            $table->foreignId('organisation_id')->nullable()->constrained('organisations')->onDelete('set null');
            $table->string('institute_name')->nullable(); // Snapshot name
            $table->string('course_enrolled')->nullable();
            $table->string('batch_type')->nullable(); // Regular, Topper, etc.
            $table->string('mode_of_study')->nullable(); // Online/Offline
            $table->string('admission_through')->nullable(); // Direct, Scholarship

            // 4. PERFORMANCE & PROGRESS
            $table->text('test_scores_summary')->nullable();
            $table->decimal('average_test_score', 5, 2)->nullable();
            $table->json('rank_trend')->nullable(); // Array of ranks
            $table->decimal('attendance_percentage', 5, 2)->nullable();
            $table->string('academic_improvement_indicator')->nullable();
            $table->json('strengths')->nullable();
            $table->json('weak_areas')->nullable();

            // 5. ACHIEVEMENTS & RESULTS
            $table->string('exam_attempted')->nullable();
            $table->year('exam_year')->nullable();
            $table->string('exam_score')->nullable();
            $table->string('exam_rank')->nullable();
            $table->string('selection_status')->nullable(); // Selected, Not Selected, Awaiting
            $table->string('college_allotted')->nullable();
            $table->string('category_rank')->nullable();
            $table->boolean('result_verified')->default(false);

            // 6. TESTIMONIAL & EXPERIENCE
            $table->text('student_testimonial')->nullable();
            $table->decimal('rating_for_institute', 2, 1)->nullable();
            $table->decimal('rating_for_faculty', 2, 1)->nullable();
            $table->boolean('would_recommend')->default(false);
            $table->integer('preparation_duration_months')->nullable();
            $table->decimal('study_hours_per_day', 3, 1)->nullable();

            // 7. DIGITAL & COMMUNITY
            $table->json('study_groups_joined')->nullable(); // Array
            $table->boolean('discussion_forum_participation')->default(false);
            $table->string('mentor_assigned')->nullable();
            $table->integer('doubt_sessions_attended')->default(0);

            // 8. PRIVACY
            $table->string('profile_visibility')->default('Private'); // Public, Limited, Private
            $table->json('fields_visible_public')->nullable();
            $table->boolean('contact_visible')->default(false);
            $table->boolean('testimonial_visible')->default(false);
            $table->boolean('consent_for_data_use')->default(false);

            // 9. SEO
            $table->boolean('profile_indexing_allowed')->default(false);
            $table->string('schema_type')->default('Person');
            $table->string('canonical_url')->nullable();

            // 10. ADMIN
            $table->boolean('profile_verified')->default(false);
            $table->string('verification_source')->nullable();
            $table->string('data_source')->default('Manual');
            $table->decimal('confidence_score', 3, 1)->nullable();
            $table->string('status')->default('Active');

            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('students');
    }
};

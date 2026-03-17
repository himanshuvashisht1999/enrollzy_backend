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
        Schema::create('exams', function (Blueprint $table) {
            $table->id();
            
            // 1. CORE EXAM IDENTITY
            $table->uuid('exam_id')->unique(); // UUID for external reference
            $table->string('name'); // exam_name
            $table->string('short_name')->nullable(); // exam_short_name
            $table->string('slug')->unique(); // slug
            $table->string('logo')->nullable(); // logo_url
            $table->string('cover_image')->nullable(); // cover_image_url
            $table->string('exam_type')->nullable(); // National, State etc.
            $table->string('exam_category')->nullable(); // Medical, Engineering etc.
            $table->string('conducting_authority_name')->nullable();
            $table->string('conducting_body_type')->nullable(); // Govt, University etc.
            $table->string('official_website')->nullable();
            $table->longText('about_exam')->nullable();
            $table->string('exam_purpose')->nullable(); // Admission, Scholarship etc.

            // 2. EXAM OWNERSHIP & SOURCE
            $table->string('exam_source_type')->default('External'); // External/Internal
            $table->foreignId('owning_organisation_id')->nullable()->constrained('organisations')->nullOnDelete(); // internal exam link
            $table->string('owning_organisation_name')->nullable(); // Fallback name

            // 3. EXAM ELIGIBILITY (Global)
            $table->string('minimum_qualification')->nullable();
            $table->string('minimum_marks_required')->nullable();
            $table->json('subjects_required')->nullable();
            $table->integer('minimum_age')->nullable();
            $table->integer('maximum_age')->nullable();
            $table->integer('attempt_limit')->nullable();
            $table->boolean('gap_year_allowed')->default(true);
            $table->json('nationality_criteria')->nullable();
            $table->boolean('reservation_applicable')->default(false);
            $table->longText('eligibility_notes')->nullable();

            // 4. EXAM PATTERN & STRUCTURE
            $table->string('exam_mode')->nullable(); // Online, Offline
            $table->string('exam_format')->nullable(); // MCQ, Mixed
            $table->integer('total_questions')->nullable();
            $table->integer('total_marks')->nullable();
            $table->boolean('negative_marking')->default(false);
            $table->text('negative_marking_scheme')->nullable();
            $table->json('sections')->nullable(); // Name, Qs, Marks
            $table->integer('duration_minutes')->nullable();
            $table->json('languages_available')->nullable();

            // 5. SYLLABUS & PREPARATION
            $table->string('syllabus_source')->nullable(); // CBSE, Custom
            $table->string('syllabus_url')->nullable();
            $table->json('subjects_covered')->nullable();
            $table->string('difficulty_level')->nullable();
            $table->json('recommended_classes')->nullable();
            $table->boolean('previous_year_question_papers_available')->default(false);

            // 6. IMPORTANT DATES (Stored in separate exam_sessions table for queryability)
            // But we can keep a "current_session" JSON summary here if needed, skipping for normalization.

            // 7. APPLICATION PROCESS
            $table->string('application_mode')->nullable(); // Online/Offline
            $table->json('application_steps')->nullable();
            $table->json('documents_required')->nullable();
            $table->decimal('application_fee', 10, 2)->nullable();
            $table->string('fee_currency')->default('INR');
            $table->json('payment_modes')->nullable();
            $table->text('application_helpdesk_details')->nullable();

            // 8. RESULT, RANK & SCORING
            $table->string('score_type')->nullable(); // Marks, Percentile
            $table->string('rank_type')->nullable(); // AIR, State
            $table->boolean('normalization_applied')->default(false);
            $table->text('tie_breaking_rules')->nullable();
            $table->string('score_validity_period')->nullable();
            $table->string('result_format_url')->nullable();

            // 9. CUT-OFF DATA (Global)
            $table->string('cutoff_type')->nullable();
            $table->json('cutoff_year_wise')->nullable(); // [{year: 2024, cat: Gen, val: 500}]
            $table->text('cutoff_reference_note')->nullable();

            // 10. COUNSELLING
            $table->boolean('counselling_conducted')->default(false);
            $table->string('counselling_authority')->nullable();
            $table->string('counselling_mode')->nullable();
            $table->integer('number_of_rounds')->nullable();
            $table->string('seat_allocation_basis')->nullable();
            $table->string('reservation_policy_reference')->nullable();
            $table->string('official_counselling_website')->nullable();

            // 11. PARTICIPATING ORGS
            $table->integer('accepting_organization_count')->nullable();
            $table->json('accepting_organizations_sample')->nullable();
            $table->json('course_types_supported')->nullable();

            // 12. FREQUENCY & HISTORY
            $table->string('exam_frequency')->nullable();
            $table->integer('first_conducted_year')->nullable();
            $table->json('years_active')->nullable();
            $table->boolean('exam_discontinued')->default(false);
            $table->string('replaced_by_exam_name')->nullable();

            // 13. SEO & CONTENT
            $table->string('meta_title')->nullable();
            $table->text('meta_description')->nullable();
            $table->json('focus_keywords')->nullable();
            $table->string('schema_type')->default('EducationalAssessment');
            $table->string('canonical_url')->nullable();
            $table->string('indexing_status')->default('index, follow');
            $table->string('breadcrumb_category')->nullable();

            // 14. TRUST & VERIFICATION
            $table->json('official_notification_urls')->nullable();
            $table->string('information_source')->nullable();
            $table->date('last_verified_on')->nullable();
            $table->integer('data_confidence_score')->default(0);
            $table->text('disclaimer_text')->nullable();

            // 15. ADMIN & PLATFORM
            $table->foreignId('created_by')->nullable()->constrained('users');
            $table->foreignId('updated_by')->nullable()->constrained('users');
            $table->string('status')->default('Draft'); // Active, Upcoming, Archived, Draft
            $table->string('visibility')->default('Public');
            $table->boolean('featured_exam')->default(false);

            $table->timestamps();
        });

        // Separate Table for Yearly Sessions (Important Dates)
        Schema::create('exam_sessions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('exam_id')->constrained()->cascadeOnDelete();
            $table->string('academic_year'); // e.g. "2024-25" or "2025"
            $table->string('session_name')->nullable(); // e.g. "January Session"
            
            // Dates
            $table->date('application_start_date')->nullable();
            $table->date('application_end_date')->nullable();
            $table->string('correction_window_dates')->nullable(); // Range or text
            $table->date('admit_card_release_date')->nullable();
            $table->date('exam_date')->nullable();
            $table->date('answer_key_release_date')->nullable();
            $table->date('result_declaration_date')->nullable();
            $table->date('counselling_start_date')->nullable();
            $table->date('counselling_end_date')->nullable();
            
            $table->boolean('is_current_session')->default(false);
            $table->string('status')->default('Upcoming'); // Open, Closed, Ongoing, Completed

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('exam_sessions');
        Schema::dropIfExists('exams');
    }
};

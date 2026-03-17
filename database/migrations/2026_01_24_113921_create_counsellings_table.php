<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('counsellings', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignId('exam_id')->constrained('exams')->onDelete('cascade');

            // 1. Core Identity
            $table->string('counselling_name');
            $table->string('slug')->unique();
            $table->string('counselling_type'); // Centralised, State-Level, Institute-Level
            $table->string('counselling_mode')->default('Online'); // Online, Offline, Hybrid
            $table->string('conducting_authority_name');
            $table->string('conducting_authority_type'); // Central Government, State Government, University Body
            $table->string('official_counselling_website')->nullable();

            // 2. Scope & Applicability
            $table->json('applicable_course_levels')->nullable(); // UG, PG
            $table->json('applicable_quotas')->nullable(); // All India, State, Institutional, Management
            $table->json('applicable_categories')->nullable(); // General, OBC, SC, ST, EWS, PwD
            $table->boolean('domicile_required')->default(false);
            $table->text('state_applicability')->nullable(); // If state counselling, which states? (stored as text or json)

            // 3. Eligibility
            $table->boolean('minimum_exam_qualification_required')->default(true);
            $table->string('minimum_score_or_rank_required')->nullable();
            $table->json('category_wise_eligibility')->nullable();
            $table->string('attempts_allowed')->nullable();
            $table->text('age_criteria_for_counselling')->nullable();
            $table->text('eligibility_notes')->nullable();

            // 4. Rounds
            $table->integer('number_of_rounds')->default(1);
            $table->json('rounds')->nullable(); // Array of round objects

            // 5. Process
            $table->json('registration_process_steps')->nullable();
            $table->longText('choice_filling_process')->nullable();
            $table->boolean('choice_locking_required')->default(true);
            $table->longText('seat_allotment_process')->nullable();
            $table->longText('reporting_process')->nullable();
            $table->longText('document_verification_process')->nullable();
            $table->longText('upgradation_rules')->nullable();
            $table->longText('exit_and_refund_rules')->nullable();

            // 6. Important Dates (Yearly Block)
            $table->json('important_dates')->nullable();

            // 7. Seat Allocation
            $table->string('seat_allocation_basis')->default('Exam Rank'); // Exam Rank, Score
            $table->longText('tie_breaking_rules')->nullable();
            $table->string('reservation_policy_reference')->nullable();
            $table->string('seat_matrix_source')->nullable();
            $table->longText('seat_conversion_rules')->nullable();

            // 8. Documents
            $table->json('documents_required')->nullable();
            $table->longText('document_format_requirements')->nullable();
            $table->boolean('original_documents_required_at_reporting')->default(true);

            // 9. Fees
            $table->string('counselling_fee_amount')->nullable();
            $table->string('fee_currency')->default('INR');
            $table->boolean('fee_refundable')->default(false);
            $table->longText('refund_conditions')->nullable();
            $table->json('payment_modes')->nullable();

            // 10. Participating Institutions
            $table->integer('participating_institutions_count')->nullable();
            $table->text('participating_institutions_note')->nullable();
            $table->json('institution_type_supported')->nullable(); // Government, Private, Deemed

            // 11. Help & Support
            $table->string('helpdesk_contact_number')->nullable();
            $table->string('helpdesk_email')->nullable();
            $table->string('faq_url')->nullable();
            $table->longText('grievance_redressal_process')->nullable();
            $table->json('official_notifications_urls')->nullable();

            // 12. Verification & SEO
            $table->string('information_source')->nullable();
            $table->dateTime('last_verified_on')->nullable();
            $table->decimal('data_confidence_score', 5, 2)->default(100.00);
            $table->text('disclaimer_text')->nullable();

            $table->string('meta_title')->nullable();
            $table->string('meta_description')->nullable();
            $table->json('focus_keywords')->nullable();
            $table->string('schema_type')->default('EducationalOccupationalProgram');
            $table->string('canonical_url')->nullable();
            $table->string('indexing_status')->default('index, follow');

            // 13. System Fields
            $table->string('status')->default('Active'); // Upcoming, Active, Closed, Archived
            $table->string('visibility')->default('Public'); // Public, Draft
            $table->unsignedBigInteger('created_by')->nullable();
            $table->unsignedBigInteger('last_updated_by')->nullable();

            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('counsellings');
    }
};

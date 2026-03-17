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
        Schema::create('exam_stage_interviews', function (Blueprint $table) {
            $table->id();
            $table->uuid('interview_stage_id')->unique();
            $table->foreignId('exam_id')->constrained('exams')->onDelete('cascade');
            $table->foreignId('exam_stage_id')->constrained('exam_stages'); // Link to master stage ID (3 for Interview)

            // 1️⃣ CORE INTERVIEW IDENTITY
            $table->string('stage_name')->default('Interview'); // Interview, Personality Test, Viva Voce
            $table->integer('stage_order')->default(1);
            $table->boolean('mandatory')->default(true);
            $table->string('stage_contribution_type')->default('merit_deciding'); // merit_deciding, qualifying_only, verification_only

            // 2️⃣ CONDUCTING AUTHORITY
            $table->string('interview_conducting_body')->nullable(); // UPSC, State PSC, University Selection Board
            $table->string('interview_panel_type')->nullable(); // Single Panel, Multiple Panels
            $table->text('panel_constitution_guidelines')->nullable();
            $table->string('interview_centres_scope')->nullable(); // National, State, Campus-based
            $table->string('official_interview_guidelines_url')->nullable();

            // 3️⃣ INTERVIEW FORMAT & MODE
            $table->string('interview_mode')->nullable(); // In-Person, Online, Hybrid
            $table->integer('interview_duration_minutes')->nullable();
            $table->integer('number_of_panellists')->nullable();
            $table->json('language_options')->nullable();
            $table->boolean('medium_switch_allowed')->default(false);

            // 4️⃣ EVALUATION PARAMETERS
            $table->json('evaluation_criteria')->nullable(); // Personality, Communication Skills, etc.
            $table->boolean('criteria_weightage_defined')->default(false);

            // 5️⃣ MARKING & MERIT LOGIC
            $table->boolean('marks_applicable')->default(true);
            $table->decimal('maximum_marks', 8, 2)->nullable();
            $table->decimal('minimum_qualifying_marks', 8, 2)->nullable();
            $table->boolean('category_wise_cutoff_applicable')->default(false);
            $table->decimal('weightage_percentage', 5, 2)->nullable();
            $table->boolean('normalization_applied')->default(false);

            // 6️⃣ ELIGIBILITY TO APPEAR
            $table->boolean('previous_stage_qualification_required')->default(true);
            $table->string('shortlisting_basis')->nullable(); // Rank-based, Marks-based
            $table->json('documents_required_for_interview_call')->nullable();

            // 7️⃣ INTERVIEW PROCESS FLOW
            $table->json('interview_process_steps')->nullable(); // Document Verification, Panel Interview, etc.
            $table->boolean('identity_verification_required')->default(true);
            $table->boolean('biometric_verification_required')->default(false);

            // 8️⃣ SCHEDULING & SLOT ALLOCATION
            $table->boolean('slot_booking_required')->default(false);
            $table->string('slot_allocation_method')->nullable(); // Automated, Manual
            $table->boolean('rescheduling_allowed')->default(false);
            $table->text('rescheduling_conditions')->nullable();
            $table->text('late_reporting_policy')->nullable();

            // 9️⃣ RESULT & OUTPUT
            $table->string('interview_result_type')->nullable(); // Marks, Pass/Fail
            $table->string('interview_result_visibility')->nullable(); // Candidate Login, Public
            $table->date('interview_result_declaration_date')->nullable();

            // 🔟 APPEAL / REVIEW
            $table->boolean('appeal_allowed')->default(false);
            $table->text('appeal_process_description')->nullable();
            $table->integer('appeal_time_limit_days')->nullable();
            $table->boolean('appeal_fee_required')->default(false);
            $table->decimal('appeal_fee_amount', 8, 2)->nullable();
            $table->string('final_decision_authority')->nullable();

            // 1️⃣1️⃣ SPECIAL RULES & RELAXATIONS
            $table->json('category_relaxations')->nullable();
            $table->boolean('pwd_accommodations_available')->default(false);
            $table->text('ex_servicemen_relaxations')->nullable();
            $table->text('gender_specific_guidelines')->nullable();

            // 1️⃣2️⃣ FEES
            $table->boolean('interview_fee_required')->default(false);
            $table->decimal('interview_fee_amount', 8, 2)->nullable();
            $table->boolean('fee_refundable')->default(false);
            $table->json('payment_modes')->nullable();

            // 1️⃣3️⃣ DISCLAIMERS & REFERENCES
            $table->text('interview_disclaimer_text')->nullable();
            $table->string('information_source')->nullable();
            $table->date('last_verified_on')->nullable();

            // 1️⃣4️⃣ ADMIN & CONTROL
            $table->string('stage_status')->default('Scheduled'); // Scheduled, Completed, Archived
            $table->string('visibility')->default('Public');
            $table->text('remarks')->nullable();
            $table->timestamp('last_updated_on')->nullable();

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('exam_stage_interviews');
    }
};

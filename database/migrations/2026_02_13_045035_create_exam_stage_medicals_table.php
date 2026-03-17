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
        Schema::create('exam_stage_medicals', function (Blueprint $table) {
            $table->id();
            $table->uuid('medical_stage_id')->unique();
            $table->foreignId('exam_id')->constrained()->onDelete('cascade');
            $table->foreignId('exam_stage_id')->constrained('exam_stages')->onDelete('cascade');
            $table->string('stage_name')->nullable();
            $table->integer('stage_order')->default(4);
            $table->boolean('mandatory')->default(true);
            $table->string('stage_contribution_type')->default('qualifying_only');

            // AUTHORITY
            $table->string('medical_conducting_authority')->nullable();
            $table->string('medical_board_type')->nullable();
            $table->string('medical_centres_scope')->nullable();
            $table->string('medical_centres_list_url')->nullable();
            $table->string('official_medical_guidelines_url')->nullable();

            // GENERAL HEALTH
            $table->boolean('general_health_required')->default(true);
            $table->boolean('free_from_chronic_diseases')->default(true);
            $table->boolean('physical_fitness_required')->default(true);

            // PHYSICAL STANDARDS
            $table->text('height_requirement')->nullable();
            $table->text('weight_standard_reference')->nullable();
            $table->boolean('chest_measurement_required')->default(false);
            $table->boolean('chest_expansion_required')->default(false);

            // VISION
            $table->boolean('vision_test_required')->default(true);
            $table->text('visual_acuity_standards')->nullable();
            $table->boolean('color_vision_required')->default(true);
            $table->boolean('night_blindness_disqualifying')->default(true);
            $table->boolean('spectacles_allowed')->default(false);

            // HEARING & SPEECH
            $table->boolean('hearing_standard_required')->default(true);
            $table->boolean('speech_standard_required')->default(true);

            // SYSTEM CHECKS
            $table->boolean('cardiovascular_system_check')->default(true);
            $table->boolean('respiratory_system_check')->default(true);
            $table->boolean('neurological_system_check')->default(true);
            $table->boolean('musculoskeletal_system_check')->default(true);
            $table->boolean('mental_health_evaluation_required')->default(true);

            // DISQUALIFICATIONS
            $table->json('temporary_disqualifications')->nullable();
            $table->json('permanent_disqualifications')->nullable();
            $table->text('tattoo_policy')->nullable();
            $table->text('surgical_history_rules')->nullable();
            $table->text('pregnancy_rules')->nullable();

            // PROCEDURE
            $table->json('medical_exam_procedure_steps')->nullable();
            $table->json('tests_conducted')->nullable();
            $table->boolean('fasting_required')->default(false);
            $table->string('medical_exam_duration')->nullable();

            // APPEAL / REVIEW
            $table->boolean('medical_review_allowed')->default(false);
            $table->integer('appeal_time_limit_days')->nullable();
            $table->text('review_medical_board_details')->nullable();
            $table->boolean('appeal_fee_required')->default(false);
            $table->decimal('appeal_fee_amount', 8, 2)->nullable();
            $table->string('final_decision_authority')->nullable();

            // RESULT
            $table->string('medical_result_type')->nullable();
            $table->boolean('temporary_unfit_retest_allowed')->default(false);
            $table->integer('retest_timeline_days')->nullable();
            $table->string('medical_result_visibility')->nullable();

            // DOCUMENTS
            $table->json('medical_documents_required')->nullable();
            $table->string('medical_certificate_format_url')->nullable();

            // SCHEDULING
            $table->date('medical_exam_start_date')->nullable();
            $table->date('medical_exam_end_date')->nullable();
            $table->boolean('slot_booking_required')->default(false);
            $table->text('reporting_time_guidelines')->nullable();

            // FEES
            $table->boolean('medical_fee_required')->default(false);
            $table->decimal('medical_fee_amount', 8, 2)->nullable();
            $table->boolean('fee_refundable')->default(false);
            $table->json('payment_mode')->nullable();

            // RELAXATIONS
            $table->text('gender_based_relaxation_rules')->nullable();
            $table->text('category_based_relaxation_rules')->nullable();
            $table->text('ex_servicemen_relaxation')->nullable();
            $table->text('pwd_medical_rules')->nullable();

            // DISCLAIMERS
            $table->text('medical_disclaimer_text')->nullable();
            $table->string('information_source')->nullable();
            $table->date('last_verified_on')->nullable();

            // ADMIN
            $table->string('stage_status')->nullable();
            $table->string('visibility')->nullable();
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
        Schema::dropIfExists('exam_stage_medicals');
    }
};

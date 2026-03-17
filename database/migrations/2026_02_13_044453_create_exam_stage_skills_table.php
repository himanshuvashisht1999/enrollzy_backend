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
        Schema::create('exam_stage_skills', function (Blueprint $table) {
            $table->id();
            $table->uuid('skill_stage_id')->unique();
            $table->foreignId('exam_id')->constrained()->onDelete('cascade');
            $table->foreignId('exam_stage_id')->constrained('exam_stages')->onDelete('cascade');
            $table->string('stage_name')->nullable();
            $table->integer('stage_order')->default(5);
            $table->boolean('mandatory')->default(true);
            $table->string('stage_contribution_type')->default('qualifying_only');

            $table->string('skill_test_category')->nullable();
            $table->string('skill_test_purpose')->nullable();
            $table->json('skills_evaluated')->nullable();

            // TYPING / DATA ENTRY
            $table->json('typing_language_options')->nullable();
            $table->string('minimum_typing_speed')->nullable();
            $table->decimal('accuracy_required_percentage', 5, 2)->nullable();
            $table->decimal('error_tolerance_percentage', 5, 2)->nullable();
            $table->boolean('backspace_allowed')->default(true);

            // COMPUTER / PRACTICAL SKILLS
            $table->json('software_tools_tested')->nullable();
            $table->boolean('task_based_evaluation')->default(false);
            $table->integer('task_completion_time_minutes')->nullable();

            $table->boolean('marks_applicable')->default(false);
            $table->decimal('maximum_marks', 8, 2)->nullable();
            $table->decimal('minimum_qualifying_score', 8, 2)->nullable();
            $table->boolean('pass_fail_only')->default(true);
            $table->boolean('normalization_applied')->default(false);

            $table->boolean('previous_stage_qualification_required')->default(true);
            $table->string('shortlisting_basis')->nullable();
            $table->json('post_wise_skill_requirements')->nullable();
            $table->json('category_wise_relaxations')->nullable();

            $table->string('test_mode')->nullable();
            $table->string('test_environment')->nullable();
            $table->boolean('assistive_devices_allowed')->default(false);
            $table->boolean('pwd_accommodations_available')->default(false);

            $table->string('skill_test_centres_scope')->nullable();
            $table->text('lab_infrastructure_required')->nullable();
            $table->text('reporting_time_guidelines')->nullable();
            $table->boolean('identity_verification_required')->default(true);

            $table->integer('attempts_allowed')->default(1);
            $table->boolean('retest_allowed')->default(false);
            $table->text('retest_conditions')->nullable();
            $table->boolean('temporary_failure_recovery_allowed')->default(false);

            $table->string('skill_test_result_type')->nullable();
            $table->string('result_visibility')->nullable();
            $table->date('result_declaration_date')->nullable();

            $table->boolean('appeal_allowed')->default(false);
            $table->text('appeal_process')->nullable();
            $table->integer('appeal_time_limit_days')->nullable();
            $table->boolean('appeal_fee_required')->default(false);
            $table->decimal('appeal_fee_amount', 8, 2)->nullable();

            $table->json('documents_required')->nullable();
            $table->string('instruction_guidelines_url')->nullable();
            $table->boolean('mock_test_available')->default(false);
            $table->boolean('demo_environment_available')->default(false);

            $table->boolean('skill_test_fee_required')->default(false);
            $table->decimal('skill_test_fee_amount', 8, 2)->nullable();
            $table->boolean('fee_refundable')->default(false);
            $table->json('payment_modes')->nullable();

            $table->text('skill_test_disclaimer_text')->nullable();
            $table->string('information_source')->nullable();
            $table->date('last_verified_on')->nullable();

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
        Schema::dropIfExists('exam_stage_skills');
    }
};

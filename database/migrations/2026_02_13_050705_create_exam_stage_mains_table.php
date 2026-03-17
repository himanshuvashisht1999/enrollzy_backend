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
        Schema::create('exam_stage_mains', function (Blueprint $table) {
            $table->id();
            $table->uuid('main_stage_id')->unique();
            $table->foreignId('exam_id')->constrained()->onDelete('cascade');
            $table->foreignId('exam_stage_id')->constrained('exam_stages')->onDelete('cascade');
            $table->string('stage_name')->nullable();
            $table->integer('stage_order')->default(2);
            $table->boolean('mandatory')->default(true);

            // ELIGIBILITY & PATTERN
            $table->json('subjects_required')->nullable();
            $table->integer('attempt_limit')->nullable();
            $table->boolean('gap_year_allowed')->default(true);
            $table->text('eligibility_notes')->nullable();
            $table->string('exam_mode')->nullable();
            $table->string('exam_format')->nullable();
            $table->integer('total_questions')->nullable();
            $table->integer('total_marks')->nullable();
            $table->integer('duration_minutes')->nullable();
            $table->boolean('negative_marking')->default(false);
            $table->string('negative_marking_scheme')->nullable();
            $table->string('syllabus_url')->nullable();
            $table->string('difficulty_level')->nullable();
            $table->text('syllabus_source')->nullable();
            $table->json('subjects_covered')->nullable();

            // IMPORTANT DATES
            $table->json('sessions_data')->nullable();
            $table->text('admit_card_download_procedure')->nullable();
            $table->text('result_check_procedure')->nullable();

            // RESULT & CUTOFF
            $table->string('score_type')->nullable();
            $table->string('rank_type')->nullable();
            $table->boolean('normalization_applied')->default(false);
            $table->text('tie_breaking_rules')->nullable();
            $table->string('score_validity_period')->nullable();
            $table->string('result_format_url')->nullable();
            $table->string('cutoff_type')->nullable();
            $table->json('cutoff_year_wise')->nullable();
            $table->text('cutoff_reference_note')->nullable();

            // APPLICATION & FEES
            $table->boolean('registration_fee_required')->default(false);
            $table->json('registration_fee_structure')->nullable();
            $table->boolean('late_registration_allowed')->default(false);
            $table->json('late_fee_rules')->nullable();
            $table->boolean('security_deposit_required')->default(false);
            $table->json('security_deposit_structure')->nullable();
            $table->json('payment_modes_allowed')->nullable();
            $table->boolean('transaction_charges_applicable')->default(false);
            $table->string('transaction_charge_borne_by')->nullable();

            $table->timestamp('last_updated_on')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('exam_stage_mains');
    }
};

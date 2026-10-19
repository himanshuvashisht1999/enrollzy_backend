<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Every table hangs off a cycle; a nullable round_id means "round override / round addition".
        $owned = function (Blueprint $table, bool $withRound = true) {
            $table->id();
            $table->foreignId('cycle_id')->constrained('cns_cycles')->cascadeOnDelete();
            if ($withRound) {
                $table->foreignId('round_id')->nullable()->constrained('cns_rounds')->cascadeOnDelete();
            }
        };

        // Eligibility: one row per cycle, plus optional extra rows for a quota with different rules.
        Schema::create('cns_eligibility', function (Blueprint $table) use ($owned) {
            $owned($table, false);
            $table->foreignId('quota_id')->nullable()->constrained('cns_quotas')->nullOnDelete();   // null = all quotas
            $table->enum('qualifying_class', ['class_12', 'graduation', 'diploma', 'post_graduation'])->nullable();
            $table->json('qualifying_subjects')->nullable();
            $table->json('nationality_allowed')->nullable();
            $table->unsignedTinyInteger('age_min')->nullable();
            $table->unsignedTinyInteger('age_max')->nullable();
            $table->date('age_as_of')->nullable();
            $table->enum('domicile_rule', ['none', 'state_domicile', 'study_years_in_state', 'domicile_or_study', 'parent_domicile'])->default('none');
            $table->json('domicile_states')->nullable();
            $table->unsignedTinyInteger('study_years_required')->nullable();
            $table->text('domicile_notes')->nullable();
            $table->text('other_conditions')->nullable();
            $table->timestamps();
        });

        // Category-wise minimum scores for an eligibility row (NEET: 50th percentile General, 40th SC/ST/OBC).
        Schema::create('cns_eligibility_scores', function (Blueprint $table) {
            $table->id();
            $table->foreignId('eligibility_id')->constrained('cns_eligibility')->cascadeOnDelete();
            $table->foreignId('category_id')->constrained('cns_categories');
            $table->foreignId('exam_id')->nullable()->constrained('exm_exams')->nullOnDelete();
            $table->decimal('min_percentile', 6, 3)->nullable();
            $table->decimal('min_score', 10, 2)->nullable();
            $table->unsignedInteger('max_rank')->nullable();
            $table->decimal('min_qualifying_percent', 5, 2)->nullable();
            $table->timestamps();
        });

        Schema::create('cns_process_steps', function (Blueprint $table) use ($owned) {
            $owned($table);
            $table->unsignedSmallInteger('step_no')->default(1);
            $table->string('title');
            $table->text('description')->nullable();
            $table->string('link_url', 500)->nullable();
            $table->foreignId('event_type_id')->nullable()->constrained('exm_event_types')->nullOnDelete();   // shows that event's date on the timeline
            $table->timestamps();
        });

        Schema::create('cns_fees', function (Blueprint $table) use ($owned) {
            $owned($table);
            $table->enum('fee_type', ['registration', 'security_deposit', 'tuition_advance', 'other']);
            $table->foreignId('quota_id')->nullable()->constrained('cns_quotas')->nullOnDelete();
            $table->foreignId('category_id')->nullable()->constrained('cns_categories')->nullOnDelete();
            $table->enum('institute_type', ['government', 'private', 'deemed', 'central_university', 'autonomous', 'other'])->nullable();
            $table->decimal('amount', 12, 2);
            $table->string('currency', 3)->default('INR');
            $table->boolean('is_refundable')->default(false);
            $table->string('note')->nullable();
            $table->timestamps();
        });

        Schema::create('cns_documents', function (Blueprint $table) use ($owned) {
            $owned($table);
            $table->foreignId('document_type_id')->constrained('cns_document_types');
            $table->enum('stage', ['registration', 'reporting', 'document_verification'])->default('registration');
            $table->foreignId('applies_to_quota_id')->nullable()->constrained('cns_quotas')->nullOnDelete();
            $table->foreignId('applies_to_category_id')->nullable()->constrained('cns_categories')->nullOnDelete();
            $table->boolean('is_mandatory')->default(true);
            $table->string('template_file')->nullable();
            $table->string('template_url', 500)->nullable();
            $table->string('format_notes')->nullable();
            $table->unsignedInteger('sort_order')->default(0);
            $table->timestamps();
        });

        // "If the candidate does X, this happens to their deposit and seat."
        Schema::create('cns_rules', function (Blueprint $table) use ($owned) {
            $owned($table);
            $table->foreignId('candidate_action_id')->constrained('cns_candidate_actions');
            $table->foreignId('quota_id')->nullable()->constrained('cns_quotas')->nullOnDelete();
            $table->enum('institute_type', ['government', 'private', 'deemed', 'central_university', 'autonomous', 'other'])->nullable();
            $table->enum('deposit_outcome', ['refunded', 'forfeited', 'partly_refunded', 'adjusted_in_fee', 'not_applicable'])->default('not_applicable');
            $table->decimal('refund_percent', 5, 2)->nullable();
            $table->decimal('refund_amount', 12, 2)->nullable();
            $table->enum('seat_outcome', ['retained', 'forfeited', 'released'])->nullable();
            $table->boolean('eligible_for_next_round')->default(true);
            $table->string('penalty_note')->nullable();
            $table->string('source_url', 500);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        foreach (['cns_rules', 'cns_documents', 'cns_fees', 'cns_process_steps', 'cns_eligibility_scores', 'cns_eligibility'] as $t) {
            Schema::dropIfExists($t);
        }
    }
};

<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('exm_conducting_bodies', function (Blueprint $table) {
            $table->id();
            $table->string('name')->unique();
            $table->string('short_name')->nullable();
            $table->string('website')->nullable();
            $table->boolean('status')->default(true);
            $table->timestamps();
        });

        Schema::create('exm_exams', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('short_name')->nullable();
            $table->string('slug')->unique();
            $table->json('aliases')->nullable();
            $table->enum('source_type', ['external', 'internal'])->default('external');
            $table->unsignedBigInteger('owning_organisation_id')->nullable()->index();
            $table->foreignId('conducting_body_id')->nullable()->constrained('exm_conducting_bodies')->nullOnDelete();
            $table->enum('exam_type', ['national', 'state', 'university', 'international', 'school', 'internal'])->nullable();
            $table->json('exam_categories')->nullable();
            $table->enum('frequency', ['yearly', 'twice_a_year', 'quarterly', 'monthly', 'on_demand'])->nullable();
            $table->json('program_levels')->nullable();
            $table->json('states')->nullable();
            $table->foreignId('feeder_exam_id')->nullable()->constrained('exm_exams')->nullOnDelete();
            $table->json('languages')->nullable();
            $table->longText('about')->nullable();
            $table->string('official_website')->nullable();
            $table->string('logo')->nullable();
            $table->boolean('has_stages')->default(false);
            $table->enum('visibility', ['draft', 'public'])->default('draft');
            $table->boolean('status')->default(true);
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('exm_event_types', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('slug')->unique();
            $table->enum('status_source', ['dates', 'manual'])->default('dates');
            $table->unsignedInteger('sort_order')->default(0);
            $table->boolean('status')->default(true);
            $table->timestamps();
        });

        Schema::create('exm_cycles', function (Blueprint $table) {
            $table->id();
            $table->foreignId('exam_id')->constrained('exm_exams')->cascadeOnDelete();
            $table->unsignedSmallInteger('year');
            $table->string('session_label')->nullable();
            $table->enum('registration_mode', ['window', 'rolling', 'via_institution', 'not_applicable'])->default('window');
            $table->enum('result_mode', ['fixed_date', 'on_demand'])->default('fixed_date');
            $table->enum('cycle_status', ['scheduled', 'postponed', 'cancelled', 'completed'])->default('scheduled');
            $table->string('status_note')->nullable();
            $table->string('timezone')->default('Asia/Kolkata');
            $table->boolean('is_current')->default(false);
            $table->boolean('is_public')->default(false);
            $table->timestamps();
            $table->softDeletes();
            $table->index(['exam_id', 'year']);
        });

        Schema::create('exm_stages', function (Blueprint $table) {
            $table->id();
            $table->foreignId('cycle_id')->constrained('exm_cycles')->cascadeOnDelete();
            $table->string('name');
            $table->unsignedInteger('sort_order')->default(0);
            $table->boolean('is_qualifying')->default(false);
            $table->timestamps();
        });

        Schema::create('exm_cycle_events', function (Blueprint $table) {
            $table->id();
            $table->foreignId('cycle_id')->constrained('exm_cycles')->cascadeOnDelete();
            $table->foreignId('stage_id')->nullable()->constrained('exm_stages')->nullOnDelete();
            $table->foreignId('event_type_id')->constrained('exm_event_types');
            $table->string('label')->nullable();
            $table->dateTime('start_at')->nullable();
            $table->dateTime('end_at')->nullable();
            $table->dateTime('extended_end_at')->nullable();
            $table->enum('date_precision', ['exact', 'month_only', 'tentative', 'tba'])->default('exact');
            $table->enum('manual_status', ['not_announced', 'expected', 'declared', 'postponed', 'withheld', 'cancelled'])->nullable();
            $table->string('on_demand_text')->nullable();
            $table->string('link_url')->nullable();
            $table->text('note')->nullable();
            $table->string('source_url')->nullable();
            $table->dateTime('last_verified_at')->nullable();
            $table->unsignedBigInteger('verified_by')->nullable();
            $table->timestamps();
        });

        Schema::create('exm_audit_logs', function (Blueprint $table) {
            $table->id();
            $table->string('auditable_type');
            $table->unsignedBigInteger('auditable_id');
            $table->unsignedBigInteger('user_id')->nullable();
            $table->string('action', 20);
            $table->json('old_values')->nullable();
            $table->json('new_values')->nullable();
            $table->timestamp('created_at')->useCurrent();
            $table->index(['auditable_type', 'auditable_id']);
        });
    }

    public function down(): void
    {
        foreach (['exm_audit_logs', 'exm_cycle_events', 'exm_stages', 'exm_cycles', 'exm_event_types', 'exm_exams', 'exm_conducting_bodies'] as $t) {
            Schema::dropIfExists($t);
        }
    }
};

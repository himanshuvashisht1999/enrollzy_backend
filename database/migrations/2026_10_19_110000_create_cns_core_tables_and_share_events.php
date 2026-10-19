<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('cns_counsellings', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('short_name', 100)->nullable();
            $table->string('slug')->unique();
            $table->json('aliases')->nullable();
            $table->foreignId('conducting_body_id')->constrained('exm_conducting_bodies');
            $table->enum('level', ['national', 'state', 'university', 'institute']);
            $table->json('states')->nullable();
            $table->unsignedBigInteger('owning_organisation_id')->nullable()->index();
            $table->json('program_levels')->nullable();
            $table->json('course_categories')->nullable();
            $table->enum('mode', ['online', 'offline', 'hybrid'])->default('online');
            $table->string('official_website')->nullable();
            $table->string('registration_portal_url')->nullable();
            $table->string('helpline')->nullable();
            $table->string('logo')->nullable();
            $table->string('cover_image')->nullable();
            $table->longText('about')->nullable();
            $table->enum('visibility', ['draft', 'public'])->default('draft');
            $table->boolean('is_active')->default(true);
            $table->boolean('is_archived')->default(false);
            $table->boolean('is_featured')->default(false);
            $table->unsignedInteger('sort_order')->default(0);
            $table->timestamps();
            $table->softDeletes();
        });

        // One mapping table, read from both sides (exam page and counselling page).
        Schema::create('cns_counselling_exams', function (Blueprint $table) {
            $table->id();
            $table->foreignId('counselling_id')->constrained('cns_counsellings')->cascadeOnDelete();
            $table->foreignId('exam_id')->constrained('exm_exams')->cascadeOnDelete();
            $table->enum('role', ['primary', 'additional'])->default('primary');
            $table->unsignedSmallInteger('valid_from_year')->nullable();
            $table->unsignedSmallInteger('valid_to_year')->nullable();
            $table->string('seat_scope_note')->nullable();
            $table->unsignedInteger('sort_order')->default(0);
            $table->timestamps();
            $table->unique(['counselling_id', 'exam_id']);
        });

        Schema::create('cns_cycles', function (Blueprint $table) {
            $table->id();
            $table->foreignId('counselling_id')->constrained('cns_counsellings')->cascadeOnDelete();
            $table->unsignedSmallInteger('year');
            $table->string('session_label')->nullable();
            $table->enum('cycle_status', ['scheduled', 'postponed', 'cancelled', 'completed'])->default('scheduled');
            $table->string('status_note')->nullable();
            $table->string('timezone')->default('Asia/Kolkata');
            $table->boolean('is_current')->default(false);
            $table->boolean('is_public')->default(false);
            $table->string('information_bulletin_pdf')->nullable();
            $table->string('information_bulletin_url')->nullable();
            $table->longText('reservation_policy')->nullable();
            $table->longText('seat_allotment_method')->nullable();
            $table->json('willingness_options')->nullable();
            $table->string('source_url')->nullable();
            $table->dateTime('last_verified_at')->nullable();
            $table->unsignedBigInteger('verified_by')->nullable();
            $table->timestamps();
            $table->softDeletes();
            $table->index(['counselling_id', 'year']);
        });

        // Which exam cycles feed this counselling year (e.g. NEET-UG 2026).
        Schema::create('cns_cycle_exam_cycles', function (Blueprint $table) {
            $table->id();
            $table->foreignId('cycle_id')->constrained('cns_cycles')->cascadeOnDelete();
            $table->foreignId('exam_cycle_id')->constrained('exm_cycles')->cascadeOnDelete();
            $table->unique(['cycle_id', 'exam_cycle_id']);
        });

        Schema::create('cns_rounds', function (Blueprint $table) {
            $table->id();
            $table->foreignId('cycle_id')->constrained('cns_cycles')->cascadeOnDelete();
            $table->string('name');
            $table->foreignId('round_type_id')->constrained('cns_round_types');
            $table->unsignedInteger('sort_order')->default(0);
            $table->json('quotas')->nullable();                           // null = all quotas of the cycle
            $table->enum('round_status', ['scheduled', 'postponed', 'cancelled', 'completed'])->default('scheduled');
            $table->string('status_note')->nullable();
            $table->boolean('fresh_registration_allowed')->nullable();    // null = inherit from round type
            $table->boolean('choices_carry_forward')->nullable();
            $table->json('willingness_options')->nullable();              // null = inherit from cycle
            $table->longText('instructions')->nullable();
            $table->string('notice_url')->nullable();
            $table->string('source_url')->nullable();
            $table->dateTime('last_verified_at')->nullable();
            $table->unsignedBigInteger('verified_by')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });

        // ---- Share the Exam Module events table: an event belongs to an exam cycle OR a counselling cycle ----
        Schema::table('exm_cycle_events', function (Blueprint $table) {
            $table->dropForeign(['cycle_id']);
        });
        Schema::table('exm_cycle_events', function (Blueprint $table) {
            $table->unsignedBigInteger('cycle_id')->nullable()->change();
        });
        Schema::table('exm_cycle_events', function (Blueprint $table) {
            $table->foreign('cycle_id')->references('id')->on('exm_cycles')->cascadeOnDelete();
            $table->foreignId('counselling_cycle_id')->nullable()->after('cycle_id')->constrained('cns_cycles')->cascadeOnDelete();
            $table->foreignId('round_id')->nullable()->after('counselling_cycle_id')->constrained('cns_rounds')->cascadeOnDelete();
            $table->foreignId('quota_id')->nullable()->after('round_id')->constrained('cns_quotas')->nullOnDelete();
        });
        DB::statement('ALTER TABLE exm_cycle_events ADD CONSTRAINT exm_cycle_events_one_owner CHECK '
            . '((cycle_id IS NOT NULL AND counselling_cycle_id IS NULL) OR (cycle_id IS NULL AND counselling_cycle_id IS NOT NULL))');
    }

    public function down(): void
    {
        DB::table('exm_cycle_events')->whereNull('cycle_id')->delete();
        DB::statement('ALTER TABLE exm_cycle_events DROP CONSTRAINT exm_cycle_events_one_owner');
        Schema::table('exm_cycle_events', function (Blueprint $table) {
            $table->dropForeign(['quota_id']);
            $table->dropForeign(['round_id']);
            $table->dropForeign(['counselling_cycle_id']);
            $table->dropColumn(['quota_id', 'round_id', 'counselling_cycle_id']);
            $table->dropForeign(['cycle_id']);
        });
        Schema::table('exm_cycle_events', function (Blueprint $table) {
            $table->unsignedBigInteger('cycle_id')->nullable(false)->change();
            $table->foreign('cycle_id')->references('id')->on('exm_cycles')->cascadeOnDelete();
        });
        foreach (['cns_rounds', 'cns_cycle_exam_cycles', 'cns_cycles', 'cns_counselling_exams', 'cns_counsellings'] as $t) {
            Schema::dropIfExists($t);
        }
    }
};

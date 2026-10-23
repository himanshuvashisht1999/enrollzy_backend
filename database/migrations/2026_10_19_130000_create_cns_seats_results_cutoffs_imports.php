<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Colleges and courses are referenced by plain indexed ids: those masters live in older modules.
        Schema::create('cns_participants', function (Blueprint $table) {
            $table->id();
            $table->foreignId('cycle_id')->constrained('cns_cycles')->cascadeOnDelete();
            $table->unsignedBigInteger('organisation_id')->index();
            $table->unsignedBigInteger('course_id')->index();
            $table->foreignId('quota_id')->constrained('cns_quotas');
            $table->enum('institute_type', ['government', 'private', 'deemed', 'central_university', 'autonomous', 'other'])->nullable();
            $table->string('institute_code', 60)->nullable();           // snapshot of the authority's code for this cycle
            $table->decimal('annual_tuition_fee', 12, 2)->nullable();
            $table->decimal('other_fees', 12, 2)->nullable();
            $table->text('bond_details')->nullable();
            $table->unsignedInteger('total_seats')->nullable();         // summary only; detail is in the seat matrix
            $table->unsignedBigInteger('import_batch_id')->nullable()->index();
            $table->timestamps();
            $table->unique(['cycle_id', 'organisation_id', 'course_id', 'quota_id'], 'cns_participants_unique');
        });

        // Authorities publish revised seat matrices mid-round: every version is kept, one is live.
        Schema::create('cns_seat_matrix_versions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('round_id')->constrained('cns_rounds')->cascadeOnDelete();
            $table->unsignedSmallInteger('version_no');
            $table->string('label')->nullable();
            $table->date('published_on')->nullable();
            $table->string('source_url', 500)->nullable();
            $table->unsignedBigInteger('imported_by')->nullable();
            $table->dateTime('imported_at')->nullable();
            $table->boolean('is_live')->default(false);
            $table->unsignedInteger('row_count')->default(0);
            $table->unsignedBigInteger('import_batch_id')->nullable()->index();
            $table->timestamps();
            $table->unique(['round_id', 'version_no']);
        });

        Schema::create('cns_seat_matrix_rows', function (Blueprint $table) {
            $table->id();
            $table->foreignId('version_id')->constrained('cns_seat_matrix_versions')->cascadeOnDelete();
            $table->unsignedBigInteger('organisation_id');
            $table->unsignedBigInteger('course_id');
            $table->foreignId('quota_id')->constrained('cns_quotas');
            $table->foreignId('category_id')->constrained('cns_categories');
            $table->enum('gender', ['male', 'female'])->nullable();      // null = gender-neutral
            $table->boolean('pwd')->default(false);
            $table->unsignedInteger('seats')->default(0);
            $table->index(['version_id', 'organisation_id', 'course_id'], 'cns_seat_rows_lookup');
        });

        Schema::create('cns_allotment_results', function (Blueprint $table) {
            $table->id();
            $table->foreignId('round_id')->constrained('cns_rounds')->cascadeOnDelete();
            $table->string('title');
            $table->enum('result_type', ['provisional', 'final', 'revised'])->default('provisional');
            $table->string('file')->nullable();
            $table->string('url', 500)->nullable();
            $table->dateTime('published_at')->nullable();
            $table->string('source_url', 500)->nullable();
            $table->timestamps();
        });

        Schema::create('cns_cutoffs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('round_id')->constrained('cns_rounds')->cascadeOnDelete();
            $table->foreignId('exam_id')->constrained('exm_exams');
            $table->unsignedBigInteger('organisation_id');
            $table->unsignedBigInteger('course_id');
            $table->foreignId('quota_id')->constrained('cns_quotas');
            $table->foreignId('category_id')->constrained('cns_categories');
            $table->enum('gender', ['male', 'female'])->nullable();
            $table->boolean('pwd')->default(false);
            $table->enum('rank_type', ['air', 'category_rank', 'state_merit_rank', 'score', 'percentile']);
            $table->decimal('opening_value', 14, 3)->nullable();
            $table->decimal('closing_value', 14, 3)->nullable();
            $table->unsignedBigInteger('import_batch_id')->nullable()->index();
            $table->timestamps();
            $table->index(['round_id', 'organisation_id', 'course_id'], 'cns_cutoffs_lookup');
        });

        // What the importer learned: "SRM Univ." -> SRM Institute of Science and Technology.
        Schema::create('cns_aliases', function (Blueprint $table) {
            $table->id();
            $table->enum('type', ['organisation', 'course', 'quota', 'category', 'exam']);
            $table->string('alias');
            $table->string('alias_norm')->index();
            $table->unsignedBigInteger('target_id');
            $table->unsignedBigInteger('created_by')->nullable();
            $table->timestamps();
            $table->unique(['type', 'alias_norm']);
        });

        Schema::create('cns_import_batches', function (Blueprint $table) {
            $table->id();
            $table->enum('type', ['participants', 'seats', 'cutoffs']);
            $table->foreignId('cycle_id')->constrained('cns_cycles')->cascadeOnDelete();
            $table->foreignId('round_id')->nullable()->constrained('cns_rounds')->cascadeOnDelete();
            $table->enum('mode', ['append', 'replace'])->default('append');
            $table->boolean('make_live')->default(true);                  // seat matrix: make the new version live
            $table->string('label')->nullable();                          // seat matrix: version label
            $table->date('published_on')->nullable();
            $table->string('source_url', 500)->nullable();
            $table->string('file_name');
            $table->string('file_path');
            $table->enum('status', ['previewed', 'confirmed', 'undone', 'cancelled'])->default('previewed');
            $table->unsignedInteger('total_rows')->default(0);
            $table->unsignedInteger('matched_rows')->default(0);
            $table->unsignedInteger('warning_rows')->default(0);
            $table->unsignedInteger('error_rows')->default(0);
            $table->unsignedInteger('saved_rows')->default(0);
            $table->unsignedBigInteger('created_by')->nullable();
            $table->dateTime('confirmed_at')->nullable();
            $table->dateTime('undone_at')->nullable();
            $table->timestamps();
        });

        Schema::create('cns_import_rows', function (Blueprint $table) {
            $table->id();
            $table->foreignId('batch_id')->constrained('cns_import_batches')->cascadeOnDelete();
            $table->unsignedInteger('row_no');
            $table->json('raw');
            $table->json('resolved')->nullable();
            $table->enum('status', ['matched', 'warning', 'error'])->default('error');
            $table->json('messages')->nullable();
            $table->index(['batch_id', 'status']);
        });
    }

    public function down(): void
    {
        foreach (['cns_import_rows', 'cns_import_batches', 'cns_aliases', 'cns_cutoffs', 'cns_allotment_results',
            'cns_seat_matrix_rows', 'cns_seat_matrix_versions', 'cns_participants'] as $t) {
            Schema::dropIfExists($t);
        }
    }
};

<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Step 2 of the Ranking & Accreditation review: dated, sourced, verifiable records.
 * One table per kind of record, each pointing at whichever LEVEL the issuing body actually assessed
 * (entity_type = organisation | campus | department | course, entity_id = that record's id).
 *
 * entity_id is a string because campuses and departments use UUIDs while organisations and courses use integers.
 */
return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('ranking_bodies')) {
            Schema::create('ranking_bodies', function (Blueprint $table) {
                $table->id();
                $table->string('name')->unique();                                  // NIRF, QS, THE, India Today...
                $table->string('short_name', 40)->nullable();
                $table->enum('type', ['government', 'international', 'media']);
                $table->string('country', 60)->nullable();
                $table->string('website')->nullable();
                $table->enum('trust_level', ['high', 'medium', 'low'])->default('medium');   // media rankings: lower trust, always show the source
                $table->string('logo')->nullable();
                $table->boolean('status')->default(true);
                $table->unsignedInteger('sort_order')->default(0);
                $table->timestamps();
            });
        }

        if (!Schema::hasTable('ranking_categories')) {
            Schema::create('ranking_categories', function (Blueprint $table) {
                $table->id();
                $table->foreignId('ranking_body_id')->constrained('ranking_bodies')->cascadeOnDelete();
                $table->string('name');                                            // Overall, Engineering, Management, Asia, Subject: Computer Science...
                $table->json('applicable_levels')->nullable();                     // which levels this category can be recorded at; null = any
                $table->foreignId('discipline_id')->nullable()->constrained('disciplines')->nullOnDelete();   // subject rankings show on courses of this discipline
                $table->boolean('status')->default(true);
                $table->unsignedInteger('sort_order')->default(0);
                $table->timestamps();
                $table->unique(['ranking_body_id', 'name']);
            });
        }

        if (!Schema::hasTable('accreditation_bodies')) {
            Schema::create('accreditation_bodies', function (Blueprint $table) {
                $table->id();
                $table->string('name')->unique();
                $table->string('short_name', 40)->unique();                        // NAAC, NBA, AICTE, NMC...
                // A grade (NAAC A++), an accreditation (NBA), a statutory approval (AICTE) and a recognition (UGC) are different things.
                $table->enum('kind', ['accreditation', 'statutory_approval', 'recognition', 'affiliation']);
                $table->json('applicable_levels')->nullable();
                $table->boolean('has_grade')->default(false);
                $table->json('grade_options')->nullable();
                $table->boolean('has_validity')->default(true);
                $table->string('country', 60)->nullable();
                $table->string('website')->nullable();
                $table->boolean('status')->default(true);
                $table->unsignedInteger('sort_order')->default(0);
                $table->timestamps();
            });
        }

        if (!Schema::hasTable('entity_rankings')) {
            Schema::create('entity_rankings', function (Blueprint $table) {
                $table->id();
                $table->string('entity_type', 20);
                $table->string('entity_id', 40);
                $table->foreignId('ranking_body_id')->constrained('ranking_bodies');
                $table->foreignId('ranking_category_id')->constrained('ranking_categories');
                $table->unsignedSmallInteger('ranking_year')->nullable();          // null only for migrated legacy values
                $table->unsignedInteger('rank_exact')->nullable();
                $table->unsignedInteger('rank_band_from')->nullable();             // NIRF / QS publish many results as bands (101-150)
                $table->unsignedInteger('rank_band_to')->nullable();
                $table->decimal('score', 8, 3)->nullable();
                $table->unsignedInteger('total_ranked')->nullable();
                $table->string('external_institute_id', 40)->nullable();           // e.g. the NIRF ID
                $table->string('source_url', 500)->nullable();
                $table->string('evidence_file')->nullable();
                $table->date('published_on')->nullable();
                $table->enum('verification_status', ['unverified', 'verified', 'rejected'])->default('unverified');
                $table->unsignedBigInteger('verified_by')->nullable();
                $table->dateTime('verified_at')->nullable();
                $table->text('notes')->nullable();
                $table->string('legacy_source', 60)->nullable()->index();           // which old column this was migrated from
                $table->unsignedBigInteger('created_by')->nullable();
                $table->timestamps();
                $table->softDeletes();
                $table->index(['entity_type', 'entity_id']);
                $table->index(['ranking_body_id', 'ranking_category_id', 'ranking_year'], 'entity_rankings_lookup');
            });
        }

        if (!Schema::hasTable('entity_accreditations')) {
            Schema::create('entity_accreditations', function (Blueprint $table) {
                $table->id();
                $table->string('entity_type', 20);
                $table->string('entity_id', 40);
                $table->foreignId('accreditation_body_id')->constrained('accreditation_bodies');
                $table->string('grade', 30)->nullable();
                $table->decimal('score', 5, 2)->nullable();                        // NAAC CGPA
                $table->unsignedTinyInteger('cycle')->nullable();
                $table->date('valid_from')->nullable();
                $table->date('valid_to')->nullable();
                $table->enum('status', ['active', 'expired', 'applied', 'withdrawn'])->default('active');   // "expired" is also computed from valid_to
                $table->string('certificate_number', 100)->nullable();
                $table->string('source_url', 500)->nullable();
                $table->string('evidence_file')->nullable();
                $table->enum('verification_status', ['unverified', 'verified', 'rejected'])->default('unverified');
                $table->unsignedBigInteger('verified_by')->nullable();
                $table->dateTime('verified_at')->nullable();
                $table->text('notes')->nullable();
                $table->string('legacy_source', 60)->nullable()->index();
                $table->unsignedBigInteger('created_by')->nullable();
                $table->timestamps();
                $table->softDeletes();
                $table->index(['entity_type', 'entity_id']);
                $table->index(['accreditation_body_id', 'status']);
            });
        }
    }

    public function down(): void
    {
        foreach (['entity_accreditations', 'entity_rankings', 'accreditation_bodies', 'ranking_categories', 'ranking_bodies'] as $t) {
            Schema::dropIfExists($t);
        }
    }
};

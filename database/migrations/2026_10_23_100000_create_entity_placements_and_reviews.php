<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Priority 4 of the Ranking & Accreditation review: placements and ratings become RAW records that totals and averages are
 * computed from, instead of typed numbers that can contradict each other.
 *  - entity_placements: raw placement figures per entity per academic year (course level normally; an institution-reported row is allowed at any level).
 *  - entity_reviews: individual ratings/reviews. Averages and counts are computed from these at every level (course -> department -> campus -> organisation).
 */
return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('entity_placements')) {
            Schema::create('entity_placements', function (Blueprint $table) {
                $table->id();
                $table->string('entity_type', 20);
                $table->string('entity_id', 40);
                $table->string('academic_year', 9)->nullable();                     // 2024-25; null only for migrated legacy values
                $table->unsignedInteger('students_graduating')->nullable();         // students eligible / graduating that year
                $table->unsignedInteger('students_placed')->nullable();
                $table->unsignedInteger('students_higher_studies')->nullable();
                $table->unsignedBigInteger('median_salary')->nullable();            // INR per year
                $table->unsignedBigInteger('highest_salary')->nullable();           // INR per year
                $table->unsignedBigInteger('average_salary')->nullable();           // INR per year
                $table->unsignedInteger('recruiters_count')->nullable();
                $table->string('placement_source', 255)->nullable();                // "College placement report 2024-25", "NIRF data"...
                $table->string('source_url', 500)->nullable();
                $table->string('evidence_file')->nullable();
                $table->enum('verification_status', ['unverified', 'verified', 'rejected'])->default('unverified');
                $table->unsignedBigInteger('verified_by')->nullable();
                $table->dateTime('verified_at')->nullable();
                $table->text('notes')->nullable();
                $table->string('legacy_source', 80)->nullable()->index();
                $table->unsignedBigInteger('created_by')->nullable();
                $table->timestamps();
                $table->softDeletes();
                $table->index(['entity_type', 'entity_id']);
                $table->index('academic_year');
            });
        }

        if (!Schema::hasTable('entity_reviews')) {
            Schema::create('entity_reviews', function (Blueprint $table) {
                $table->id();
                $table->string('entity_type', 20);
                $table->string('entity_id', 40);
                $table->string('reviewer_name', 150)->nullable();
                $table->enum('reviewer_type', ['student', 'alumni', 'parent', 'faculty', 'recruiter', 'other'])->default('other');
                $table->decimal('rating', 2, 1);                                    // overall, 1.0 - 5.0
                $table->decimal('infrastructure', 2, 1)->nullable();
                $table->decimal('campus_life', 2, 1)->nullable();
                $table->decimal('academics', 2, 1)->nullable();
                $table->decimal('placements', 2, 1)->nullable();
                $table->decimal('value_for_money', 2, 1)->nullable();
                $table->string('title', 255)->nullable();
                $table->text('comment')->nullable();
                $table->date('reviewed_on')->nullable();
                $table->unsignedSmallInteger('review_year')->nullable();            // when only a year is known
                $table->string('source', 100)->nullable();                          // platform / how the review was collected
                $table->string('source_url', 500)->nullable();
                $table->boolean('is_published')->default(true);                     // hide without deleting
                $table->enum('verification_status', ['unverified', 'verified', 'rejected'])->default('unverified');
                $table->unsignedBigInteger('verified_by')->nullable();
                $table->dateTime('verified_at')->nullable();
                $table->text('notes')->nullable();
                $table->string('legacy_source', 80)->nullable()->index();
                $table->unsignedBigInteger('created_by')->nullable();
                $table->timestamps();
                $table->softDeletes();
                $table->index(['entity_type', 'entity_id']);
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('entity_reviews');
        Schema::dropIfExists('entity_placements');
    }
};

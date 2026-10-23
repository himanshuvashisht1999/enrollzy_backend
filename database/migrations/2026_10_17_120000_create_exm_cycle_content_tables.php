<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('exm_cities', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('state')->nullable();
            $table->boolean('status')->default(true);
            $table->timestamps();
            $table->unique(['name', 'state']);
        });

        // Every content table hangs off a cycle and carries an optional stage.
        $cycleScoped = function (Blueprint $table) {
            $table->id();
            $table->foreignId('cycle_id')->constrained('exm_cycles')->cascadeOnDelete();
            $table->foreignId('stage_id')->nullable()->constrained('exm_stages')->nullOnDelete();
        };

        Schema::create('exm_eligibilities', function (Blueprint $table) use ($cycleScoped) {
            $cycleScoped($table);
            $table->unsignedBigInteger('min_qualification_id')->nullable();   // Program Level master
            $table->json('subjects')->nullable();
            $table->decimal('min_percentage_general', 5, 2)->nullable();
            $table->decimal('min_percentage_reserved', 5, 2)->nullable();
            $table->unsignedTinyInteger('age_min')->nullable();
            $table->unsignedTinyInteger('age_max')->nullable();
            $table->date('age_as_of')->nullable();
            $table->json('age_relaxation')->nullable();                       // [{category, years}]
            $table->unsignedTinyInteger('max_attempts')->nullable();
            $table->string('nationality')->nullable();
            $table->boolean('appearing_allowed')->default(false);
            $table->text('other_conditions')->nullable();
            $table->timestamps();
        });

        Schema::create('exm_patterns', function (Blueprint $table) use ($cycleScoped) {
            $cycleScoped($table);
            $table->enum('mode', ['cbt', 'pen_paper', 'hybrid', 'home_based'])->nullable();
            $table->unsignedSmallInteger('duration_minutes')->nullable();
            $table->unsignedSmallInteger('total_questions')->nullable();
            $table->decimal('total_marks', 8, 2)->nullable();
            $table->boolean('negative_marking')->default(false);
            $table->decimal('negative_marks', 5, 2)->nullable();
            $table->json('question_types')->nullable();
            $table->json('sections')->nullable();                             // [{name, questions, marks}]
            $table->timestamps();
        });

        // One row per category so fees stay filterable ("exams under Rs 1,000").
        Schema::create('exm_fees', function (Blueprint $table) use ($cycleScoped) {
            $cycleScoped($table);
            $table->string('category', 20);
            $table->string('gender', 20)->nullable();
            $table->decimal('amount', 10, 2);
            $table->string('currency', 3)->default('INR');
            $table->timestamps();
            $table->index(['cycle_id', 'category']);
        });

        Schema::create('exm_syllabus', function (Blueprint $table) use ($cycleScoped) {
            $cycleScoped($table);
            $table->string('subject')->nullable();
            $table->json('topics')->nullable();
            $table->string('pdf')->nullable();
            $table->timestamps();
        });

        Schema::create('exm_centres', function (Blueprint $table) use ($cycleScoped) {
            $cycleScoped($table);
            $table->foreignId('city_id')->nullable()->constrained('exm_cities')->nullOnDelete();
            $table->string('country')->default('India');
            $table->timestamps();
        });

        Schema::create('exm_documents', function (Blueprint $table) use ($cycleScoped) {
            $cycleScoped($table);
            $table->string('name');
            $table->boolean('is_mandatory')->default(true);
            $table->string('notes')->nullable();
            $table->timestamps();
        });

        Schema::create('exm_papers', function (Blueprint $table) use ($cycleScoped) {
            $cycleScoped($table);
            $table->string('title');
            $table->unsignedSmallInteger('year')->nullable();
            $table->string('shift')->nullable();
            $table->string('file')->nullable();
            $table->timestamps();
        });

        Schema::create('exm_faqs', function (Blueprint $table) use ($cycleScoped) {
            $cycleScoped($table);
            $table->string('question', 500);
            $table->text('answer');
            $table->unsignedInteger('sort_order')->default(0);
            $table->timestamps();
        });

        // Schema Builder: extras unique to one exam. Keys never change once data exists.
        Schema::create('exm_custom_fields', function (Blueprint $table) {
            $table->id();
            $table->foreignId('exam_id')->constrained('exm_exams')->cascadeOnDelete();
            $table->string('key', 60);
            $table->string('label');
            $table->string('type', 20);
            $table->json('options')->nullable();                              // dropdown choices / group sub-field labels
            $table->enum('scope', ['exam', 'cycle'])->default('exam');
            $table->unsignedInteger('sort_order')->default(0);
            $table->timestamps();
            $table->softDeletes();
            $table->unique(['exam_id', 'key']);
        });

        Schema::create('exm_custom_values', function (Blueprint $table) {
            $table->id();
            $table->foreignId('field_id')->constrained('exm_custom_fields')->cascadeOnDelete();
            $table->foreignId('cycle_id')->nullable()->constrained('exm_cycles')->cascadeOnDelete(); // null = exam-scope value
            $table->json('value')->nullable();
            $table->timestamps();
            $table->unique(['field_id', 'cycle_id']);
        });
    }

    public function down(): void
    {
        foreach (['exm_custom_values', 'exm_custom_fields', 'exm_faqs', 'exm_papers', 'exm_documents', 'exm_centres',
            'exm_syllabus', 'exm_fees', 'exm_patterns', 'exm_eligibilities', 'exm_cities'] as $t) {
            Schema::dropIfExists($t);
        }
    }
};

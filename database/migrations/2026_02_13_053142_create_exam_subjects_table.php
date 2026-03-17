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
        Schema::create('exam_subjects', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignId('exam_id')->constrained()->onDelete('cascade');
            $table->foreignId('exam_stage_id')->constrained('exam_stages')->onDelete('cascade');

            // 1. CORE IDENTITY
            $table->string('subject_name');
            $table->string('subject_code')->nullable();
            $table->string('slug')->unique();
            $table->enum('subject_type', ['Mandatory', 'Optional', 'Language', 'Qualifying'])->default('Mandatory');
            $table->string('subject_group')->nullable(); // e.g. Paper I, Paper II
            $table->integer('display_order')->default(0);

            // 2. APPLICABILITY & SELECTION RULES
            $table->integer('max_subjects_allowed')->default(1);
            $table->boolean('subject_choice_required')->default(false);
            $table->text('subject_combination_rules')->nullable();
            $table->json('applicable_categories')->nullable();
            $table->json('subject_mediums_available')->nullable();

            // 3. EXAM-SPECIFIC SYLLABUS
            $table->json('syllabus_structure')->nullable(); // unit_number, unit_title, topics[]
            $table->text('syllabus_description')->nullable();
            $table->string('official_syllabus_pdf_url')->nullable();
            $table->json('reference_books')->nullable();
            $table->string('syllabus_version')->nullable();
            $table->string('syllabus_effective_year')->nullable();

            // 4. PAPER & MARKING STRUCTURE
            $table->integer('number_of_papers')->default(1);
            $table->json('paper_names')->nullable();
            $table->decimal('total_marks', 8, 2)->nullable();
            $table->json('marks_per_paper')->nullable();
            $table->boolean('negative_marking')->default(false);
            $table->decimal('qualifying_marks', 8, 2)->nullable();
            $table->boolean('normalization_applied')->default(false);

            // 5. STAGE MAPPING
            $table->json('applicable_exam_stages')->nullable(); // Prelims, Mains, Single Stage
            $table->json('stage_weightage_override')->nullable();

            // 6. ELIGIBILITY
            $table->text('minimum_qualification_required')->nullable();
            $table->boolean('background_subject_required')->default(false);
            $table->text('subject_specific_eligibility_notes')->nullable();

            // 7. RESULT & MERIT ROLE
            $table->boolean('subject_contributes_to_merit')->default(true);
            $table->decimal('subject_weightage_percentage', 5, 2)->nullable();
            $table->enum('subject_result_type', ['Marks', 'Pass/Fail'])->default('Marks');

            // 8. IMPORTANT DATES
            $table->boolean('subject_registration_required')->default(false);
            $table->date('subject_change_allowed_till_date')->nullable();

            // 9. SEO & CONTENT
            $table->string('schema_type')->default('ExamSubject');
            $table->string('meta_title')->nullable();
            $table->text('meta_description')->nullable();
            $table->json('focus_keywords')->nullable();
            $table->string('canonical_url')->nullable();

            // 10. ADMIN & CONTROL
            $table->enum('status', ['Active', 'Deprecated'])->default('Active');
            $table->foreignId('created_by')->nullable()->constrained('users');
            $table->timestamp('last_updated_on')->nullable();
            $table->string('information_source')->nullable();

            $table->timestamps();

            // Indexes
            $table->index(['exam_id', 'exam_stage_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('exam_subjects');
    }
};

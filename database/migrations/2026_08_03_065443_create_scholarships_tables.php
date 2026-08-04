<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // 1. Scholarships Table
        Schema::create('scholarships', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->string('slug')->unique();
            $table->string('short_name')->nullable();
            $table->string('scholarship_code')->nullable();
            $table->text('short_description')->nullable();
            $table->longText('overview')->nullable();
            $table->longText('about_scholarship')->nullable();
            $table->longText('why_apply')->nullable();
            $table->longText('selection_process')->nullable();
            $table->longText('terms_conditions')->nullable();
            $table->longText('important_notes')->nullable();
            $table->longText('additional_information')->nullable();
            $table->string('featured_image')->nullable();
            $table->string('banner_image')->nullable();
            $table->string('scholarship_type')->nullable(); // e.g. Merit, Need-Based, Sports, etc.
            $table->string('category')->nullable(); // e.g. Science/Tech, Management, etc.
            $table->decimal('max_amount', 12, 2)->nullable();
            $table->string('amount_prefix')->nullable(); // e.g. Upto INR
            $table->string('amount_suffix')->nullable(); // e.g. Only
            $table->string('provider_name')->nullable();
            $table->string('provider_logo')->nullable();
            $table->string('application_mode')->nullable(); // e.g. Online, Offline, Hybrid
            $table->tinyInteger('status')->default(1); // 0: Inactive/Draft, 1: Active
            $table->tinyInteger('featured')->default(0);
            $table->tinyInteger('featured_on_homepage')->default(0);
            $table->integer('sort_order')->default(0);
            $table->string('cta_text')->nullable()->default('Check Eligibility');
            $table->string('cta_url')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->onDelete('set null');
            $table->foreignId('updated_by')->nullable()->constrained('users')->onDelete('set null');
            $table->timestamps();
        });

        // 2. Scholarship Eligibilities Table
        Schema::create('scholarship_eligibilities', function (Blueprint $table) {
            $table->id();
            $table->foreignId('scholarship_id')->constrained('scholarships')->onDelete('cascade');
            $table->string('minimum_class')->nullable();
            $table->string('maximum_class')->nullable();
            $table->decimal('minimum_percentage', 5, 2)->nullable();
            $table->integer('maximum_age')->nullable();
            $table->string('gender')->default('Any'); // Male, Female, Any
            $table->string('nationality')->nullable();
            $table->string('state')->nullable();
            $table->string('city')->nullable();
            $table->string('category')->nullable(); // General, SC, ST, OBC, etc.
            $table->string('annual_family_income')->nullable(); // e.g. Below 8 Lakhs
            $table->string('course_level')->nullable(); // UG, PG, Diploma
            $table->string('course_type')->nullable(); // Online, Regular
            $table->string('academic_stream')->nullable(); // Engineering, Commerce
            $table->string('entrance_exam')->nullable(); // CAT, NEET, JEE
            $table->decimal('minimum_exam_score', 8, 2)->nullable();
            $table->string('currently_studying')->nullable();
            $table->tinyInteger('graduation_required')->default(0);
            $table->string('work_experience')->nullable();
            $table->text('other_conditions')->nullable();
            $table->timestamps();
        });

        // 3. Scholarship Benefits Table
        Schema::create('scholarship_benefits', function (Blueprint $table) {
            $table->id();
            $table->foreignId('scholarship_id')->constrained('scholarships')->onDelete('cascade');
            $table->string('benefit_title');
            $table->text('benefit_description')->nullable();
            $table->decimal('benefit_amount', 12, 2)->nullable();
            $table->integer('sort_order')->default(0);
            $table->timestamps();
        });

        // 4. Scholarship Courses (Pivot Table)
        Schema::create('scholarship_courses', function (Blueprint $table) {
            $table->id();
            $table->foreignId('scholarship_id')->constrained('scholarships')->onDelete('cascade');
            $table->foreignId('course_id')->constrained('courses')->onDelete('cascade');
            $table->timestamps();
        });

        // 5. Scholarship Universities (Pivot Table)
        Schema::create('scholarship_universities', function (Blueprint $table) {
            $table->id();
            $table->foreignId('scholarship_id')->constrained('scholarships')->onDelete('cascade');
            $table->foreignId('organisation_id')->constrained('organisations')->onDelete('cascade');
            $table->timestamps();
        });

        // 6. Scholarship Documents Table
        Schema::create('scholarship_documents', function (Blueprint $table) {
            $table->id();
            $table->foreignId('scholarship_id')->constrained('scholarships')->onDelete('cascade');
            $table->string('document_name');
            $table->tinyInteger('is_mandatory')->default(1);
            $table->timestamps();
        });

        // 7. Scholarship Dates Table
        Schema::create('scholarship_dates', function (Blueprint $table) {
            $table->id();
            $table->foreignId('scholarship_id')->constrained('scholarships')->onDelete('cascade');
            $table->date('application_start_date')->nullable();
            $table->date('application_end_date')->nullable();
            $table->date('exam_date')->nullable();
            $table->date('result_date')->nullable();
            $table->date('document_verification_date')->nullable();
            $table->date('admission_date')->nullable();
            $table->timestamps();
        });

        // 8. Scholarship FAQs Table
        Schema::create('scholarship_faqs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('scholarship_id')->constrained('scholarships')->onDelete('cascade');
            $table->text('question');
            $table->text('answer');
            $table->integer('sort_order')->default(0);
            $table->timestamps();
        });

        // 9. Scholarship Gallery Table
        Schema::create('scholarship_gallery', function (Blueprint $table) {
            $table->id();
            $table->foreignId('scholarship_id')->constrained('scholarships')->onDelete('cascade');
            $table->string('image');
            $table->string('title')->nullable();
            $table->string('alt_text')->nullable();
            $table->integer('sort_order')->default(0);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('scholarship_gallery');
        Schema::dropIfExists('scholarship_faqs');
        Schema::dropIfExists('scholarship_dates');
        Schema::dropIfExists('scholarship_documents');
        Schema::dropIfExists('scholarship_universities');
        Schema::dropIfExists('scholarship_courses');
        Schema::dropIfExists('scholarship_benefits');
        Schema::dropIfExists('scholarship_eligibilities');
        Schema::dropIfExists('scholarships');
    }
};

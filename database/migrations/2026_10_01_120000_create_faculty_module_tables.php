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
        // 1. Designation Master
        if (!Schema::hasTable('faculty_designations')) {
            Schema::create('faculty_designations', function (Blueprint $table) {
                $table->id();
                $table->string('name');
                $table->string('slug')->unique();
                $table->string('institution_category')->default('all'); // all, university, college, school, coaching
                $table->integer('hierarchy_order')->default(0);
                $table->boolean('status')->default(true);
                $table->timestamps();
            });
        }

        // 2. Faculty Types Master
        if (!Schema::hasTable('faculty_types')) {
            Schema::create('faculty_types', function (Blueprint $table) {
                $table->id();
                $table->string('name');
                $table->boolean('status')->default(true);
                $table->timestamps();
            });
        }

        // 3. Faculty Profiles Master
        if (!Schema::hasTable('faculty_profiles')) {
            Schema::create('faculty_profiles', function (Blueprint $table) {
                $table->id();
                $table->string('faculty_code')->unique(); // Auto-generated code (e.g. FAC-0001)
                $table->string('full_name');
                $table->string('display_name')->nullable();
                $table->string('profile_photo')->nullable();
                $table->unsignedBigInteger('designation_id')->nullable();
                $table->string('designation_name')->nullable();
                $table->unsignedBigInteger('faculty_type_id')->nullable();
                $table->string('gender')->nullable(); // Male, Female, Other
                $table->date('date_of_birth')->nullable();
                $table->text('short_bio')->nullable();
                $table->longText('detailed_biography')->nullable();
                $table->date('date_of_joining')->nullable();
                $table->decimal('experience_years', 4, 1)->default(0);
                $table->boolean('status')->default(true); // Active / Inactive

                // Internal Contact / Communication (Not public by default)
                $table->string('official_email')->nullable();
                $table->string('official_phone')->nullable();
                $table->string('extension')->nullable();
                $table->string('office_location')->nullable();
                $table->string('office_room_number')->nullable();

                // Research Profile Overview
                $table->text('research_interests')->nullable();
                $table->text('research_areas')->nullable();
                $table->integer('total_publications')->default(0);
                $table->integer('books_published')->default(0);
                $table->integer('patents')->default(0);
                $table->integer('research_projects')->default(0);
                $table->string('grants_received')->nullable();

                // Specializations & Subjects Taught (JSON / Comma tags)
                $table->text('specializations')->nullable();
                $table->text('research_areas_tags')->nullable();
                $table->text('subjects_taught')->nullable();

                // Social / Professional Profiles
                $table->string('linkedin_url')->nullable();
                $table->string('google_scholar_url')->nullable();
                $table->string('orcid_id')->nullable();
                $table->string('researchgate_url')->nullable();
                $table->string('personal_website_url')->nullable();
                $table->string('scopus_id')->nullable();
                $table->string('vidwan_id')->nullable();
                $table->string('academia_url')->nullable();

                // Profile URL / SEO
                $table->string('slug')->unique();
                $table->string('meta_title')->nullable();
                $table->text('meta_description')->nullable();
                $table->text('meta_keywords')->nullable();
                $table->string('canonical_url')->nullable();
                $table->string('og_image')->nullable();
                $table->string('profile_status')->default('published');
                $table->boolean('is_indexed')->default(true);

                // Verification Workflow
                $table->string('verification_status')->default('pending'); // pending, verified, unverified, rejected
                $table->unsignedBigInteger('verified_by')->nullable();
                $table->dateTime('verified_date')->nullable();
                $table->string('verification_source')->nullable();
                $table->dateTime('last_verification_date')->nullable();
                $table->text('verification_notes')->nullable();

                $table->timestamps();
                $table->softDeletes();

                $table->foreign('designation_id')->references('id')->on('faculty_designations')->onDelete('set null');
                $table->foreign('faculty_type_id')->references('id')->on('faculty_types')->onDelete('set null');
            });
        }

        // 4. Institutional & Department Mapping
        if (!Schema::hasTable('faculty_department_mapping')) {
            Schema::create('faculty_department_mapping', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('faculty_id');
                $table->unsignedBigInteger('organisation_id');
                $table->char('campus_id', 36)->nullable();
                $table->char('department_id', 36)->nullable();
                $table->unsignedBigInteger('designation_id')->nullable();
                $table->date('joining_date')->nullable();
                $table->boolean('is_primary_department')->default(false);
                $table->boolean('status')->default(true);
                $table->timestamps();

                $table->foreign('faculty_id')->references('id')->on('faculty_profiles')->onDelete('cascade');
                $table->foreign('organisation_id')->references('id')->on('organisations')->onDelete('cascade');
                $table->foreign('campus_id')->references('id')->on('campuses')->onDelete('cascade');
                $table->foreign('department_id')->references('id')->on('departments')->onDelete('cascade');
                $table->foreign('designation_id')->references('id')->on('faculty_designations')->onDelete('set null');
            });
        }

        // 5. Course & Teaching Mapping
        if (!Schema::hasTable('faculty_course_mapping')) {
            Schema::create('faculty_course_mapping', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('faculty_id');
                $table->unsignedBigInteger('organisation_course_id')->nullable();
                $table->unsignedBigInteger('course_id')->nullable();
                $table->unsignedBigInteger('subject_id')->nullable();
                $table->string('subject_name')->nullable();
                $table->string('academic_year')->nullable();
                $table->string('semester')->nullable();
                $table->string('role')->nullable(); // Course Lead, Lecturer, Mentor, etc.
                $table->boolean('status')->default(true);
                $table->timestamps();

                $table->foreign('faculty_id')->references('id')->on('faculty_profiles')->onDelete('cascade');
                $table->foreign('organisation_course_id')->references('id')->on('organisation_courses')->onDelete('cascade');
                $table->foreign('course_id')->references('id')->on('courses')->onDelete('cascade');
            });
        }

        // 6. Academic Qualifications
        if (!Schema::hasTable('faculty_qualifications')) {
            Schema::create('faculty_qualifications', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('faculty_id');
                $table->string('degree');
                $table->string('specialization')->nullable();
                $table->string('institution')->nullable();
                $table->string('university')->nullable();
                $table->string('year')->nullable();
                $table->string('grade_percentage')->nullable();
                $table->string('certificate_document')->nullable();
                $table->integer('display_order')->default(0);
                $table->timestamps();

                $table->foreign('faculty_id')->references('id')->on('faculty_profiles')->onDelete('cascade');
            });
        }

        // 7. Professional Experience
        if (!Schema::hasTable('faculty_experiences')) {
            Schema::create('faculty_experiences', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('faculty_id');
                $table->string('organization');
                $table->string('designation');
                $table->date('start_date')->nullable();
                $table->date('end_date')->nullable();
                $table->boolean('currently_working')->default(false);
                $table->text('description')->nullable();
                $table->integer('display_order')->default(0);
                $table->timestamps();

                $table->foreign('faculty_id')->references('id')->on('faculty_profiles')->onDelete('cascade');
            });
        }

        // 8. Research & Publications
        if (!Schema::hasTable('faculty_publications')) {
            Schema::create('faculty_publications', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('faculty_id');
                $table->text('title');
                $table->string('publication_type')->default('Journal'); // Journal, Conference, Book, Patent, etc.
                $table->string('journal_conference')->nullable();
                $table->string('publisher')->nullable();
                $table->date('publication_date')->nullable();
                $table->string('doi')->nullable();
                $table->string('url')->nullable();
                $table->text('authors')->nullable();
                $table->integer('citation_count')->default(0);
                $table->string('document_pdf')->nullable();
                $table->boolean('verified')->default(false);
                $table->integer('display_order')->default(0);
                $table->timestamps();

                $table->foreign('faculty_id')->references('id')->on('faculty_profiles')->onDelete('cascade');
            });
        }

        // 9. Achievements & Awards
        if (!Schema::hasTable('faculty_achievements')) {
            Schema::create('faculty_achievements', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('faculty_id');
                $table->string('title');
                $table->string('achievement_type')->nullable(); // Award, Honor, Recognition, Fellowship
                $table->string('organization')->nullable();
                $table->string('year')->nullable();
                $table->text('description')->nullable();
                $table->string('certificate_image')->nullable();
                $table->string('verification_status')->default('unverified');
                $table->integer('display_order')->default(0);
                $table->timestamps();

                $table->foreign('faculty_id')->references('id')->on('faculty_profiles')->onDelete('cascade');
            });
        }

        // 10. Certifications
        if (!Schema::hasTable('faculty_certifications')) {
            Schema::create('faculty_certifications', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('faculty_id');
                $table->string('certification_name');
                $table->string('issuing_organization')->nullable();
                $table->date('issue_date')->nullable();
                $table->date('expiry_date')->nullable();
                $table->string('credential_id')->nullable();
                $table->string('certificate_url')->nullable();
                $table->string('certificate_upload')->nullable();
                $table->integer('display_order')->default(0);
                $table->timestamps();

                $table->foreign('faculty_id')->references('id')->on('faculty_profiles')->onDelete('cascade');
            });
        }

        // 11. Institution-Specific Dynamic Custom Fields
        if (!Schema::hasTable('faculty_custom_fields')) {
            Schema::create('faculty_custom_fields', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('faculty_id');
                $table->string('institution_type')->default('general'); // university, school, coaching, college, general
                $table->string('field_key'); // e.g. h_index, classes_taught, air_rank_mentored
                $table->string('field_label');
                $table->string('field_type')->default('text'); // text, number, textarea, select
                $table->text('field_value')->nullable();
                $table->timestamps();

                $table->foreign('faculty_id')->references('id')->on('faculty_profiles')->onDelete('cascade');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('faculty_custom_fields');
        Schema::dropIfExists('faculty_certifications');
        Schema::dropIfExists('faculty_achievements');
        Schema::dropIfExists('faculty_publications');
        Schema::dropIfExists('faculty_experiences');
        Schema::dropIfExists('faculty_qualifications');
        Schema::dropIfExists('faculty_course_mapping');
        Schema::dropIfExists('faculty_department_mapping');
        Schema::dropIfExists('faculty_profiles');
        Schema::dropIfExists('faculty_types');
        Schema::dropIfExists('faculty_designations');
    }
};

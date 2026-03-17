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
        Schema::create('departments', function (Blueprint $table) {
            // 1. Core Identity
            $table->uuid('id')->primary();
            $table->foreignId('organisation_id')->constrained()->onDelete('cascade');
            $table->foreignUuid('campus_id')->constrained()->onDelete('cascade');
            $table->string('department_name');
            $table->string('department_code')->nullable();
            $table->string('slug')->unique();
            $table->enum('department_type', ['Academic', 'Clinical', 'Research', 'Interdisciplinary'])->default('Academic');
            $table->year('established_year')->nullable();
            $table->text('about_department')->nullable();

            // 2. Academic Domain & Scope
            $table->string('discipline_area')->nullable(); // Engineering, Medical, etc.
            $table->json('specializations_supported')->nullable();
            $table->json('education_levels_supported')->nullable(); // UG, PG, Doctoral
            $table->boolean('is_interdisciplinary')->default(false);
            $table->json('collaborating_departments')->nullable(); // IDs of other depts

            // 3. Governance & Leadership
            $table->string('head_of_department_name')->nullable();
            $table->string('head_of_department_designation')->nullable();
            $table->enum('hod_appointment_type', ['Permanent', 'Acting'])->nullable();
            $table->string('hod_email')->nullable();
            $table->string('department_office_contact')->nullable();
            $table->integer('faculty_count')->default(0);

            // 4. Academic Responsibilities
            $table->boolean('curriculum_design_responsibility')->default(false);
            $table->boolean('exam_setting_responsibility')->default(false);
            $table->boolean('research_programs_managed')->default(false);
            $table->boolean('phd_supervision_available')->default(false);
            $table->boolean('industry_collaboration_supported')->default(false);

            // 6. Infrastructure Ownership
            $table->integer('department_labs_count')->default(0);
            $table->boolean('specialized_labs_available')->default(false);
            $table->integer('research_centers_under_department')->default(0);
            $table->boolean('department_library_section')->default(false);
            $table->integer('classrooms_count')->default(0);

            // 7. Academic Output
            $table->integer('research_publications_count')->default(0);
            $table->integer('funded_projects_count')->default(0);
            $table->integer('patents_filed_count')->default(0);
            $table->integer('industry_projects_count')->default(0);

            // 8. Digital & Communication
            $table->string('department_website_url')->nullable();
            $table->string('department_email')->nullable();
            $table->string('department_notice_board_url')->nullable();
            $table->json('online_meeting_tools_used')->nullable();

            // 9. SEO & Discovery
            $table->string('schema_type')->default('EducationalOrganization');
            $table->string('meta_title')->nullable();
            $table->text('meta_description')->nullable();
            $table->json('focus_keywords')->nullable();
            $table->string('canonical_url')->nullable();

            // 10. Admin & Platform Control
            $table->enum('status', ['Active', 'Inactive', 'Archived'])->default('Active');
            $table->enum('visibility', ['Public', 'Internal'])->default('Public');
            $table->string('data_source')->nullable();
            $table->decimal('confidence_score', 5, 2)->nullable();
            $table->unsignedBigInteger('created_by')->nullable(); // Assuming user ID
            $table->timestamp('last_updated_on')->useCurrent();

            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('departments');
    }
};

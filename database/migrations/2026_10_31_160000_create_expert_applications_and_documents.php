<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Experts join through an application on the website, reviewed in the CRM:
 * - expert_expertise_areas: the expertise list (was fixed in code), managed in the CRM. experts.expertise_areas keeps
 *   the names; renaming an area renames it on every expert.
 * - expert_applications: one application per attempt by a website account; staff move it pending -> under review ->
 *   needs changes / approved / rejected. Approving creates the expert (private) linked to the account.
 * - expert_application_notes: the thread on an application (replies both ways, staff-only notes, status changes).
 * - expert_documents: qualification, certificates, awards, achievements, ID... of an application or an expert, each
 *   checked by staff. Files are private (storage/app/private/expert-docs), never in a public folder.
 * - settings: how many applications one person may make, and how long to wait after a rejection.
 */
return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('expert_expertise_areas')) {
            Schema::create('expert_expertise_areas', function (Blueprint $table) {
                $table->id();
                $table->string('name', 100)->unique();
                $table->unsignedInteger('sort_order')->default(0);
                $table->boolean('status')->default(true);
                $table->timestamps();
            });
            $now = now();
            foreach (['Career Guidance', 'School Admissions', 'University Admissions', 'Competitive Exams', 'Study Abroad', 'Scholarships', 'Online Education'] as $i => $name) {
                DB::table('expert_expertise_areas')->insert(['name' => $name, 'sort_order' => $i + 1, 'status' => true, 'created_at' => $now, 'updated_at' => $now]);
            }
        }

        if (!Schema::hasTable('expert_applications')) {
            Schema::create('expert_applications', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('user_id')->index();            // the website account that applied
                $table->unsignedBigInteger('expert_id')->nullable()->index(); // the expert made (or linked) on approval
                $table->unsignedTinyInteger('attempt_no')->default(1);
                $table->string('status', 20)->default('pending')->index(); // pending, under_review, needs_changes, approved, rejected
                $table->string('name', 150);
                $table->string('email', 150);
                $table->string('mobile', 20);
                $table->string('gender', 20)->nullable();
                $table->string('city', 100)->nullable();
                $table->string('state', 100)->nullable();
                $table->string('photo')->nullable();                       // in the private expert-docs folder until approved
                $table->string('designation', 150)->nullable();
                $table->string('organisation', 200)->nullable();
                $table->string('highest_qualification', 200)->nullable();
                $table->unsignedSmallInteger('years_of_experience')->nullable();
                $table->string('students_counselled', 100)->nullable();
                $table->json('expertise_areas')->nullable();
                $table->json('levels')->nullable();
                $table->json('languages')->nullable();
                $table->json('session_modes')->nullable();
                $table->string('short_bio', 255)->nullable();
                $table->text('detailed_bio')->nullable();
                $table->unsignedInteger('proposed_fee')->nullable();       // rupees for a 30-minute session
                $table->string('linkedin_url', 300)->nullable();
                $table->string('website_url', 300)->nullable();
                $table->timestamp('submitted_at')->nullable();
                $table->timestamp('decided_at')->nullable();
                $table->unsignedBigInteger('decided_by')->nullable();
                $table->timestamp('reapply_allowed_at')->nullable();       // staff let this person apply again now
                $table->timestamps();
            });
        }

        if (!Schema::hasTable('expert_application_notes')) {
            Schema::create('expert_application_notes', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('application_id')->index();
                $table->string('author', 10);                               // staff, applicant, system
                $table->unsignedBigInteger('admin_id')->nullable();
                $table->boolean('internal')->default(false);                // staff-only note
                $table->string('from_status', 20)->nullable();
                $table->string('to_status', 20)->nullable();
                $table->text('body')->nullable();
                $table->timestamps();
            });
        }

        if (!Schema::hasTable('expert_documents')) {
            Schema::create('expert_documents', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('expert_id')->nullable()->index();
                $table->unsignedBigInteger('application_id')->nullable()->index();
                $table->string('type', 20);                                 // qualification, certificate, award, achievement, experience, id_proof, other
                $table->string('title', 200);
                $table->string('issued_by', 200)->nullable();
                $table->unsignedSmallInteger('year')->nullable();
                $table->string('path');
                $table->string('original_name')->nullable();
                $table->string('mime', 100)->nullable();
                $table->unsignedInteger('size')->nullable();
                $table->string('status', 10)->default('pending');           // pending, verified, rejected
                $table->string('review_note', 500)->nullable();
                $table->unsignedBigInteger('reviewed_by')->nullable();
                $table->timestamp('reviewed_at')->nullable();
                $table->boolean('show_on_profile')->default(true);          // verified, not an ID: listed on the public profile
                $table->string('uploaded_by', 10)->default('expert');       // expert or staff
                $table->timestamps();
            });
        }

        Schema::table('settings', function (Blueprint $table) {
            if (!Schema::hasColumn('settings', 'expert_max_applications')) $table->unsignedTinyInteger('expert_max_applications')->default(3);
            if (!Schema::hasColumn('settings', 'expert_reapply_wait_days')) $table->unsignedSmallInteger('expert_reapply_wait_days')->default(30);
        });
    }

    public function down(): void
    {
        Schema::table('settings', function (Blueprint $table) {
            foreach (['expert_max_applications', 'expert_reapply_wait_days'] as $c) if (Schema::hasColumn('settings', $c)) $table->dropColumn($c);
        });
        Schema::dropIfExists('expert_documents');
        Schema::dropIfExists('expert_application_notes');
        Schema::dropIfExists('expert_applications');
        Schema::dropIfExists('expert_expertise_areas');
    }
};

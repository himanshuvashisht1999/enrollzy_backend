<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Enhance projects table if columns don't exist
        Schema::table('projects', function (Blueprint $table) {});

        // project_members table (Internal users assigned to project)
        if (!Schema::hasTable('project_members')) {
            Schema::create('project_members', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('project_id')->index();
                $table->unsignedInteger('user_id')->index();
                $table->string('role', 100)->default('Contributor'); // Project Owner, Project Manager, Contributor, Reviewer, Observer
                $table->enum('access_level', ['full', 'edit', 'view'])->default('edit');
                $table->date('joined_at')->nullable();
                $table->enum('status', ['active', 'inactive'])->default('active');
                $table->timestamps();

                $table->unique(['project_id', 'user_id']);
            });
        }

        // project_external_members table (External contacts / agencies assigned to project)
        if (!Schema::hasTable('project_external_members')) {
            Schema::create('project_external_members', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('project_id')->index();
                $table->unsignedBigInteger('external_organization_id')->nullable()->index();
                $table->unsignedBigInteger('external_contact_id')->nullable()->index();
                $table->unsignedBigInteger('external_team_id')->nullable()->index();
                $table->string('role', 100)->default('External Contributor');
                $table->enum('access_level', ['edit', 'view'])->default('edit');
                $table->enum('status', ['active', 'inactive'])->default('active');
                $table->timestamps();
            });
        }

        // project_documents table
        if (!Schema::hasTable('project_documents')) {
            Schema::create('project_documents', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('organization_id')->nullable()->index();
                $table->unsignedBigInteger('project_id')->index();
                $table->unsignedInteger('uploaded_by')->nullable()->index();
                $table->string('title');
                $table->string('file_name');
                $table->string('file_path');
                $table->string('file_type', 100)->nullable();
                $table->unsignedBigInteger('file_size')->default(0);
                $table->string('document_type', 100)->default('General'); // Brief, Contract, SOP, Proposal, Requirement, Meeting Doc, General
                $table->string('version', 20)->default('1.0');
                $table->enum('visibility', ['internal_only', 'all_members'])->default('all_members');
                $table->timestamps();
                $table->softDeletes();
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('project_documents');
        Schema::dropIfExists('project_external_members');
        Schema::dropIfExists('project_members');
    }
};

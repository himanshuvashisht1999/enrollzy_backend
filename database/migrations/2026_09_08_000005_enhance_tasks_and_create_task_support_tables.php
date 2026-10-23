<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // 1. Enhance tasks table
        Schema::table('tasks', function (Blueprint $table) {});

        // 2. task_assignees table
        if (!Schema::hasTable('task_assignees')) {
            Schema::create('task_assignees', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('task_id')->index();
                $table->enum('assignee_type', ['internal_user', 'internal_team', 'external_contact', 'external_team'])->default('internal_user');
                $table->unsignedInteger('user_id')->nullable()->index();
                $table->unsignedBigInteger('team_id')->nullable()->index();
                $table->unsignedBigInteger('external_contact_id')->nullable()->index();
                $table->unsignedBigInteger('external_team_id')->nullable()->index();
                $table->string('role', 100)->default('Assignee'); // Assignee, Reviewer, Observer, Helper
                $table->unsignedInteger('assigned_by')->nullable()->index();
                $table->timestamp('assigned_at')->nullable();
                $table->date('due_date')->nullable();
                $table->enum('status', ['active', 'transferred', 'completed', 'removed'])->default('active');
                $table->boolean('is_primary')->default(true);
                $table->timestamps();
            });
        }

        // 3. task_delegations table
        if (!Schema::hasTable('task_delegations')) {
            Schema::create('task_delegations', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('task_id')->index();
                $table->unsignedInteger('from_user_id')->nullable()->index();
                $table->enum('to_type', ['internal_user', 'internal_team', 'external_contact', 'external_team'])->default('internal_user');
                $table->unsignedInteger('to_user_id')->nullable()->index();
                $table->unsignedBigInteger('to_team_id')->nullable()->index();
                $table->unsignedBigInteger('external_contact_id')->nullable()->index();
                $table->unsignedBigInteger('external_team_id')->nullable()->index();
                $table->timestamp('delegated_at')->nullable();
                $table->text('remarks')->nullable();
                $table->enum('status', ['active', 'accepted', 'recalled', 'completed'])->default('active');
                $table->timestamps();
            });
        }

        // 4. task_dependencies table
        if (!Schema::hasTable('task_dependencies')) {
            Schema::create('task_dependencies', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('task_id')->index();
                $table->unsignedBigInteger('depends_on_task_id')->index();
                $table->enum('dependency_type', ['finish_to_start', 'start_to_start', 'finish_to_finish'])->default('finish_to_start');
                $table->timestamps();

                $table->unique(['task_id', 'depends_on_task_id']);
            });
        }

        // 5. task_checklists table
        if (!Schema::hasTable('task_checklists')) {
            Schema::create('task_checklists', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('task_id')->index();
                $table->string('title');
                $table->boolean('is_completed')->default(false);
                $table->unsignedInteger('completed_by')->nullable()->index();
                $table->timestamp('completed_at')->nullable();
                $table->integer('sort_order')->default(0);
                $table->timestamps();
            });
        }

        // 6. task_time_entries table
        if (!Schema::hasTable('task_time_entries')) {
            Schema::create('task_time_entries', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('task_id')->index();
                $table->unsignedInteger('user_id')->nullable()->index();
                $table->unsignedBigInteger('external_contact_id')->nullable()->index();
                $table->timestamp('started_at')->nullable();
                $table->timestamp('ended_at')->nullable();
                $table->integer('duration_minutes')->default(0);
                $table->text('description')->nullable();
                $table->timestamps();
            });
        }

        // 7. task_recurrences table
        if (!Schema::hasTable('task_recurrences')) {
            Schema::create('task_recurrences', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('task_id')->index();
                $table->enum('frequency', ['daily', 'weekly', 'monthly', 'custom'])->default('daily');
                $table->integer('interval')->default(1);
                $table->json('days_of_week')->nullable();
                $table->date('start_date')->nullable();
                $table->date('end_date')->nullable();
                $table->timestamp('next_run_at')->nullable();
                $table->enum('status', ['active', 'paused', 'completed'])->default('active');
                $table->timestamps();
            });
        }

        // 8. task_activity_logs table (Comprehensive audit trail)
        if (!Schema::hasTable('task_activity_logs')) {
            Schema::create('task_activity_logs', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('organization_id')->nullable()->index();
                $table->unsignedBigInteger('project_id')->nullable()->index();
                $table->unsignedBigInteger('milestone_id')->nullable()->index();
                $table->unsignedBigInteger('task_id')->nullable()->index();
                $table->string('action_type', 100); // created, assigned, reassigned, delegated, status_changed, priority_changed, due_date_changed, comment_added, file_uploaded, completed, verified, closed, reopened
                $table->unsignedInteger('performed_by')->nullable()->index();
                $table->string('performed_by_name')->nullable();
                $table->string('from_state')->nullable();
                $table->string('to_state')->nullable();
                $table->text('description');
                $table->json('extra_data')->nullable();
                $table->timestamps();
            });
        }

        // 9. task_attachments table
        if (!Schema::hasTable('task_attachments')) {
            Schema::create('task_attachments', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('task_id')->index();
                $table->unsignedInteger('uploaded_by')->nullable()->index();
                $table->string('file_name');
                $table->string('file_path');
                $table->string('file_type', 100)->nullable();
                $table->unsignedBigInteger('file_size')->default(0);
                $table->string('version', 20)->default('1.0');
                $table->timestamps();
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('task_attachments');
        Schema::dropIfExists('task_activity_logs');
        Schema::dropIfExists('task_recurrences');
        Schema::dropIfExists('task_time_entries');
        Schema::dropIfExists('task_checklists');
        Schema::dropIfExists('task_dependencies');
        Schema::dropIfExists('task_delegations');
        Schema::dropIfExists('task_assignees');
    }
};

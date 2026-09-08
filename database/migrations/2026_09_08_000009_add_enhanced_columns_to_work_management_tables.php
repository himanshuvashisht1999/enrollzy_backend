<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // 1. Projects table enhancements
        Schema::table('projects', function (Blueprint $table) {
            if (!Schema::hasColumn('projects', 'code')) {
                $table->string('code', 50)->nullable()->after('title');
            }
            if (!Schema::hasColumn('projects', 'department_id')) {
                $table->unsignedBigInteger('department_id')->nullable()->after('code');
            }
            if (!Schema::hasColumn('projects', 'team_id')) {
                $table->unsignedBigInteger('team_id')->nullable()->after('department_id');
            }
            if (!Schema::hasColumn('projects', 'owner_id')) {
                $table->unsignedInteger('owner_id')->nullable()->after('team_id');
            }
            if (!Schema::hasColumn('projects', 'created_by')) {
                $table->unsignedInteger('created_by')->nullable()->after('owner_id');
            }
            if (!Schema::hasColumn('projects', 'lead_user_id')) {
                $table->unsignedInteger('lead_user_id')->nullable()->after('created_by');
            }
            if (!Schema::hasColumn('projects', 'target_end_date')) {
                $table->date('target_end_date')->nullable()->after('due_date');
            }
            if (!Schema::hasColumn('projects', 'actual_end_date')) {
                $table->date('actual_end_date')->nullable()->after('target_end_date');
            }
            if (!Schema::hasColumn('projects', 'progress_percentage')) {
                $table->decimal('progress_percentage', 5, 2)->default(0)->after('status');
            }
            if (!Schema::hasColumn('projects', 'health_status')) {
                $table->string('health_status', 50)->default('on_track')->after('progress_percentage');
            }
            if (!Schema::hasColumn('projects', 'budget')) {
                $table->decimal('budget', 15, 2)->nullable()->after('price');
            }
            if (!Schema::hasColumn('projects', 'actual_cost')) {
                $table->decimal('actual_cost', 15, 2)->nullable()->after('budget');
            }
            if (!Schema::hasColumn('projects', 'risk_level')) {
                $table->string('risk_level', 50)->default('low')->after('health_status');
            }
        });

        // 2. Milestones table enhancements
        Schema::table('milestones', function (Blueprint $table) {
            if (!Schema::hasColumn('milestones', 'team_id')) {
                $table->unsignedBigInteger('team_id')->nullable()->after('project_id');
            }
            if (!Schema::hasColumn('milestones', 'owner_id')) {
                $table->unsignedInteger('owner_id')->nullable()->after('team_id');
            }
            if (!Schema::hasColumn('milestones', 'lead_user_id')) {
                $table->unsignedInteger('lead_user_id')->nullable()->after('owner_id');
            }
            if (!Schema::hasColumn('milestones', 'sequence')) {
                $table->integer('sequence')->default(1)->after('lead_user_id');
            }
            if (!Schema::hasColumn('milestones', 'weight_percentage')) {
                $table->decimal('weight_percentage', 5, 2)->default(0)->after('sequence');
            }
            if (!Schema::hasColumn('milestones', 'progress_percentage')) {
                $table->decimal('progress_percentage', 5, 2)->default(0)->after('weight_percentage');
            }
            if (!Schema::hasColumn('milestones', 'target_end_date')) {
                $table->date('target_end_date')->nullable()->after('due_date');
            }
            if (!Schema::hasColumn('milestones', 'actual_end_date')) {
                $table->date('actual_end_date')->nullable()->after('target_end_date');
            }
            if (!Schema::hasColumn('milestones', 'is_billable')) {
                $table->boolean('is_billable')->default(false)->after('price');
            }
        });

        // 3. Tasks table enhancements
        Schema::table('tasks', function (Blueprint $table) {
            if (!Schema::hasColumn('tasks', 'parent_task_id')) {
                $table->unsignedBigInteger('parent_task_id')->nullable()->after('id');
            }
            if (!Schema::hasColumn('tasks', 'task_code')) {
                $table->string('task_code', 50)->nullable()->after('parent_task_id');
            }
            if (!Schema::hasColumn('tasks', 'team_id')) {
                $table->unsignedBigInteger('team_id')->nullable()->after('project_id');
            }
            if (!Schema::hasColumn('tasks', 'created_by')) {
                $table->unsignedInteger('created_by')->nullable()->after('team_id');
            }
            if (!Schema::hasColumn('tasks', 'actual_hours')) {
                $table->decimal('actual_hours', 8, 2)->default(0)->after('estimated_hours');
            }
            if (!Schema::hasColumn('tasks', 'completed_at')) {
                $table->timestamp('completed_at')->nullable()->after('status');
            }
            if (!Schema::hasColumn('tasks', 'verified_at')) {
                $table->timestamp('verified_at')->nullable()->after('completed_at');
            }
            if (!Schema::hasColumn('tasks', 'verified_by')) {
                $table->unsignedInteger('verified_by')->nullable()->after('verified_at');
            }
            if (!Schema::hasColumn('tasks', 'closed_at')) {
                $table->timestamp('closed_at')->nullable()->after('verified_by');
            }
            if (!Schema::hasColumn('tasks', 'closed_by')) {
                $table->unsignedInteger('closed_by')->nullable()->after('closed_at');
            }
            if (!Schema::hasColumn('tasks', 'is_milestone_deliverable')) {
                $table->boolean('is_milestone_deliverable')->default(false)->after('closed_by');
            }
        });
    }

    public function down(): void
    {
    }
};

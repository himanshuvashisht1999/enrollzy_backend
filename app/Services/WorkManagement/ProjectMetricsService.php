<?php

namespace App\Services\WorkManagement;

use App\Models\Project;
use App\Models\Milestone;
use App\Models\Tasks;

class ProjectMetricsService
{
    public static function updateProjectMetrics($projectId)
    {
        $project = Project::find($projectId);
        if (!$project) return null;

        // Recalculate each milestone progress
        foreach ($project->milestones as $milestone) {
            $milestone->calculateProgress();
        }

        // Recalculate project progress and health
        $progress = $project->calculateProgress();

        return [
            'progress' => $progress,
            'health_status' => $project->health_status,
            'total_tasks' => $project->tasks()->count(),
            'completed_tasks' => $project->tasks()->whereIn('status', ['completed', 'verified', 'closed'])->count(),
            'in_progress_tasks' => $project->tasks()->whereIn('status', ['in_progress', 'accepted', 'under_review'])->count(),
            'pending_tasks' => $project->tasks()->whereIn('status', ['not_started', 'backlog', 'assigned'])->count(),
            'overdue_tasks' => $project->tasks()->whereNotIn('status', ['completed', 'verified', 'closed'])
                ->whereNotNull('due_date')
                ->where('due_date', '<', date('Y-m-d'))
                ->count(),
        ];
    }
}

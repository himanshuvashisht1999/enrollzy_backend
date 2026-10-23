<?php

namespace App\Http\Controllers\Admin\WorkManagement;

use App\Http\Controllers\Controller;
use App\Models\Project;
use App\Models\Team;
use App\Models\Admin;
use App\Models\Tasks;
use App\Models\TaskTimeEntry;
use App\Services\WorkManagement\WorkHierarchyService;
use Illuminate\Http\Request;
use Yajra\DataTables\DataTables;

class WorkReportController extends Controller
{
    public function index(Request $request)
    {
        $user = auth()->user();

        $projectQuery = Project::query();
        WorkHierarchyService::applyProjectScope($projectQuery, $user);

        $totalProjects = (clone $projectQuery)->count();
        $completedProjects = (clone $projectQuery)->where('status', 'completed')->count();

        $taskQuery = Tasks::query();
        WorkHierarchyService::applyTaskScope($taskQuery, $user);

        $totalTasks = (clone $taskQuery)->count();
        $completedTasks = (clone $taskQuery)->whereIn('status', ['completed', 'verified', 'closed'])->count();
        $overdueTasks = (clone $taskQuery)->whereNotIn('status', ['completed', 'verified', 'closed'])
            ->whereNotNull('due_date')
            ->where('due_date', '<', date('Y-m-d'))
            ->count();

        $visibleTaskIds = (clone $taskQuery)->pluck('id')->toArray();
        $totalHoursLogged = round(TaskTimeEntry::whereIn('task_id', $visibleTaskIds)->sum('duration_minutes') / 60, 1);

        $teamQuery = Team::withCount(['tasks', 'members'])->where('status', 'active');
        WorkHierarchyService::applyTeamScope($teamQuery, $user);
        $teams = $teamQuery->get();

        $projects = (clone $projectQuery)->withCount(['tasks', 'milestones'])->get();

        $staffQuery = WorkHierarchyService::getVisibleStaffQuery($user)->withCount('taskAssigneeRecords');
        $staff = $staffQuery->get();

        return view('admin.work_management.reports.index', compact(
            'totalProjects', 'completedProjects', 'totalTasks', 'completedTasks', 'overdueTasks', 'totalHoursLogged',
            'teams', 'projects', 'staff'
        ));
    }
}

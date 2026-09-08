<?php

namespace App\Http\Controllers\Admin\WorkManagement;

use App\Http\Controllers\Controller;
use App\Models\Project;
use App\Models\Team;
use App\Models\Admin;
use App\Models\Tasks;
use App\Models\TaskTimeEntry;
use Illuminate\Http\Request;
use Yajra\DataTables\DataTables;

class WorkReportController extends Controller
{
    public function index(Request $request)
    {
        $totalProjects = Project::count();
        $completedProjects = Project::where('status', 'completed')->count();
        $totalTasks = Tasks::count();
        $completedTasks = Tasks::whereIn('status', ['completed', 'verified', 'closed'])->count();
        $overdueTasks = Tasks::whereNotIn('status', ['completed', 'verified', 'closed'])
            ->whereNotNull('due_date')
            ->where('due_date', '<', date('Y-m-d'))
            ->count();
        $totalHoursLogged = round(TaskTimeEntry::sum('duration_minutes') / 60, 1);

        $teams = Team::withCount(['tasks', 'members'])->get();
        $projects = Project::withCount(['tasks', 'milestones'])->get();
        $staff = Admin::withCount('taskAssigneeRecords')->get();

        return view('admin.work_management.reports.index', compact(
            'totalProjects', 'completedProjects', 'totalTasks', 'completedTasks', 'overdueTasks', 'totalHoursLogged',
            'teams', 'projects', 'staff'
        ));
    }
}

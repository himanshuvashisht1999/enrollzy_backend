<?php

namespace App\Http\Controllers\Admin\WorkManagement;

use App\Http\Controllers\Controller;
use App\Models\Project;
use App\Models\Milestone;
use App\Models\Tasks;
use App\Models\Team;
use App\Models\Admin;
use App\Models\TaskActivityLog;
use App\Models\Meeting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class WorkDashboardController extends Controller
{
    public function overview(Request $request)
    {
        $user = auth()->user();
        $orgId = $user->organization_id ?? null;

        $projectQuery = Project::query();
        if ($orgId) $projectQuery->where('organization_id', $orgId);

        $totalProjects = (clone $projectQuery)->count();
        $activeProjects = (clone $projectQuery)->whereIn('status', ['active', 'in_progress'])->count();
        $delayedProjects = (clone $projectQuery)->where('health_status', 'delayed')->count();
        $completedProjects = (clone $projectQuery)->where('status', 'completed')->count();

        $taskQuery = Tasks::query();
        if ($orgId) $taskQuery->where('organization_id', $orgId);

        $totalTasks = (clone $taskQuery)->count();
        $pendingTasks = (clone $taskQuery)->whereIn('status', ['not_started', 'backlog', 'assigned'])->count();
        $inProgressTasks = (clone $taskQuery)->whereIn('status', ['in_progress', 'accepted', 'under_review'])->count();
        $completedTasks = (clone $taskQuery)->whereIn('status', ['completed', 'verified', 'closed'])->count();
        $overdueTasks = (clone $taskQuery)->whereNotIn('status', ['completed', 'verified', 'closed'])
            ->whereNotNull('due_date')
            ->where('due_date', '<', date('Y-m-d'))
            ->count();

        $recentProjects = (clone $projectQuery)->with(['team', 'owner', 'department'])->orderBy('id', 'desc')->take(6)->get();
        $recentActivities = TaskActivityLog::with('actor')->orderBy('id', 'desc')->take(10)->get();
        $teams = Team::with('leader')->where('status', 'active')->take(6)->get();

        return view('admin.work_management.dashboard.overview', compact(
            'totalProjects', 'activeProjects', 'delayedProjects', 'completedProjects',
            'totalTasks', 'pendingTasks', 'inProgressTasks', 'completedTasks', 'overdueTasks',
            'recentProjects', 'recentActivities', 'teams'
        ));
    }

    public function myWork(Request $request)
    {
        $userId = auth()->id();

        // Find tasks where user is assigned or active primary assignee
        $tasks = Tasks::with(['project', 'milestone_assigned', 'team'])
            ->where(function ($q) use ($userId) {
                $q->where('assigned_to', $userId)
                  ->orWhereHas('activeAssignees', function ($aq) use ($userId) {
                      $aq->where('user_id', $userId);
                  });
            })
            ->orderBy('due_date', 'asc')
            ->get();

        $todayTasks = $tasks->filter(function ($t) {
            return $t->due_date && date('Y-m-d', strtotime($t->due_date)) == date('Y-m-d') && !in_array($t->status, ['completed', 'verified', 'closed']);
        });

        $overdueTasks = $tasks->filter(function ($t) {
            return $t->due_date && date('Y-m-d', strtotime($t->due_date)) < date('Y-m-d') && !in_array($t->status, ['completed', 'verified', 'closed']);
        });

        $upcomingTasks = $tasks->filter(function ($t) {
            return $t->due_date && date('Y-m-d', strtotime($t->due_date)) > date('Y-m-d') && !in_array($t->status, ['completed', 'verified', 'closed']);
        });

        $completedTasks = $tasks->filter(function ($t) {
            return in_array($t->status, ['completed', 'verified', 'closed']);
        });

        $myProjects = Project::where('owner_id', $userId)
            ->orWhereHas('members', function ($q) use ($userId) {
                $q->where('admin.id', $userId);
            })
            ->get();

        return view('admin.work_management.dashboard.my_work', compact(
            'tasks', 'todayTasks', 'overdueTasks', 'upcomingTasks', 'completedTasks', 'myProjects'
        ));
    }

    public function teamWork(Request $request)
    {
        $userId = auth()->id();
        $user = auth()->user();

        // Get teams led by user or where user is member
        $ledTeams = Team::with(['members', 'tasks.activePrimaryAssignee.user'])->where('team_leader_id', $userId)->get();
        if ($user->isSuperAdmin() || $user->is_admin) {
            $allTeams = Team::with(['members', 'tasks.activePrimaryAssignee.user'])->where('status', 'active')->get();
        } else {
            $allTeams = $ledTeams;
        }

        return view('admin.work_management.dashboard.team_work', compact('allTeams', 'ledTeams'));
    }

    public function calendar(Request $request)
    {
        $projects = Project::where('status', 'active')->get();
        return view('admin.work_management.dashboard.calendar', compact('projects'));
    }

    public function calendarEvents(Request $request)
    {
        $tasks = Tasks::whereNotNull('due_date')->get();
        $events = [];

        foreach ($tasks as $t) {
            $events[] = [
                'id' => $t->id,
                'title' => ($t->isSubtask() ? '↳ ' : '') . $t->title,
                'start' => $t->start_date ?: $t->due_date,
                'end' => $t->due_date,
                'url' => route('admin.work_management.tasks.show', encrypt($t->id)),
                'color' => in_array($t->status, ['completed', 'verified', 'closed']) ? '#28a745' : ($t->priority == 'critical' || $t->priority == 'urgent' ? '#dc3545' : '#0d6efd'),
            ];
        }

        $meetings = Meeting::where('status', 'scheduled')->get();
        foreach ($meetings as $m) {
            $events[] = [
                'id' => 'm_' . $m->id,
                'title' => '📅 Meeting: ' . $m->title,
                'start' => $m->meeting_date . 'T' . $m->start_time,
                'end' => $m->end_time ? $m->meeting_date . 'T' . $m->end_time : null,
                'url' => route('admin.work_management.meetings.show', encrypt($m->id)),
                'color' => '#6f42c1',
            ];
        }

        return response()->json($events);
    }
}

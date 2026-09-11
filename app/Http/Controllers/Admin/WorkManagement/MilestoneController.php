<?php

namespace App\Http\Controllers\Admin\WorkManagement;

use App\Http\Controllers\Controller;
use App\Models\Milestone;
use App\Models\Project;
use App\Models\Team;
use App\Models\Admin;
use App\Models\TaskActivityLog;
use App\Services\WorkManagement\ProjectMetricsService;
use App\Services\WorkManagement\WorkHierarchyService;
use Illuminate\Http\Request;
use Yajra\DataTables\DataTables;
use Illuminate\Support\Facades\Validator;
use Exception;

class MilestoneController extends Controller
{
    public function index(Request $request)
    {
        $user = auth()->user();

        if ($request->ajax()) {
            $query = Milestone::with(['project', 'team', 'owner']);

            WorkHierarchyService::applyMilestoneScope($query, $user);

            if ($request->filled('project_id')) {
                $query->where('project_id', $request->project_id);
            }
            if ($request->filled('status')) {
                $query->where('status', $request->status);
            }

            return DataTables::of($query)
                ->addIndexColumn()
                ->addColumn('name', function ($row) {
                    return '<div class="d-flex flex-column">' .
                           '<span class="fw-bold text-dark">' . $row->title . '</span>' .
                           '<small class="text-muted">' . ($row->description ? \Illuminate\Support\Str::limit($row->description, 40) : 'No description') . '</small>' .
                           '</div>';
                })
                ->addColumn('project', function ($row) {
                    return $row->project ? '<a href="' . route('admin.work_management.projects.show', encrypt($row->project_id)) . '" class="text-decoration-none fw-semibold">' . $row->project->title . '</a>' : 'N/A';
                })
                ->addColumn('assigned_team', function ($row) {
                    return $row->team ? '<span class="badge bg-soft-primary text-primary">' . $row->team->name . '</span>' : '<span class="text-muted small">No Team</span>';
                })
                ->addColumn('owner', function ($row) {
                    return $row->owner->name ?? '<span class="text-muted small">Not Assigned</span>';
                })
                ->addColumn('progress_bar', function ($row) {
                    $val = $row->progress ?? 0;
                    $bg = $val >= 100 ? 'bg-success' : ($val >= 50 ? 'bg-primary' : 'bg-warning');
                    return '<div class="d-flex align-items-center gap-2">' .
                           '<div class="progress flex-grow-1" style="height: 6px;">' .
                           '<div class="progress-bar ' . $bg . '" role="progressbar" style="width: ' . $val . '%"></div>' .
                           '</div>' .
                           '<small class="fw-bold">' . $val . '%</small>' .
                           '</div>';
                })
                ->addColumn('timeline', function ($row) {
                    $start = $row->start_date ? date('M d', strtotime($row->start_date)) : '-';
                    $due = $row->due_date ? date('M d, Y', strtotime($row->due_date)) : '-';
                    return '<small class="text-muted">' . $start . ' &rarr; ' . $due . '</small>';
                })
                ->addColumn('status', function ($row) {
                    return GetStatusBadge($row->status);
                })
                ->addColumn('action', function ($row) {
                    $btn = '<div class="btn-group">';
                    $btn .= '<a href="' . route('admin.work_management.milestones.edit', encrypt($row->id)) . '" class="btn btn-sm btn-soft-primary"><i class="fas fa-edit"></i></a>';
                    $btn .= '<form method="POST" action="' . route('admin.work_management.milestones.destroy', encrypt($row->id)) . '" class="ms-1 delete-form" onsubmit="return confirm(\'Delete this milestone?\')">
                                ' . csrf_field() . '
                                ' . method_field('DELETE') . '
                                <button type="submit" class="btn btn-sm btn-soft-danger"><i class="fas fa-trash"></i></button>
                            </form>';
                    $btn .= '</div>';
                    return $btn;
                })
                ->rawColumns(['name', 'project', 'assigned_team', 'owner', 'progress_bar', 'timeline', 'status', 'action'])
                ->make(true);
        }

        $projectQuery = Project::where('status', 'active');
        WorkHierarchyService::applyProjectScope($projectQuery, $user);
        $projects = $projectQuery->get();

        return view('admin.work_management.milestones.index', compact('projects'));
    }

    public function create(Request $request)
    {
        $user = auth()->user();
        $selectedProjectId = $request->project_id ? (is_numeric($request->project_id) ? $request->project_id : decrypt($request->project_id)) : null;

        $projectQuery = Project::where('status', 'active');
        WorkHierarchyService::applyProjectScope($projectQuery, $user);
        $projects = $projectQuery->get();

        $teamQuery = Team::where('status', 'active');
        WorkHierarchyService::applyTeamScope($teamQuery, $user);
        $teams = $teamQuery->get();

        $staff = WorkHierarchyService::getVisibleStaffQuery($user)->get();

        return view('admin.work_management.milestones.create', compact('projects', 'teams', 'staff', 'selectedProjectId'));
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'title' => 'required|string|max:255',
            'project_id' => 'required',
            'status' => 'required',
            'start_date' => 'required|date',
            'due_date' => 'nullable|date|after_or_equal:start_date',
        ]);

        if ($validator->fails()) {
            return redirect()->back()->with('error', $validator->errors()->first())->withInput();
        }

        try {
            $data = $request->all();
            $data['organization_id'] = auth()->user()->organization_id ?? null;
            $data['created_by'] = auth()->id();

            $milestone = Milestone::create($data);
            ProjectMetricsService::updateProjectMetrics($milestone->project_id);

            TaskActivityLog::log(
                'created',
                "Milestone '{$milestone->title}' created under project by " . auth()->user()->name,
                null,
                $milestone->project_id,
                $milestone->id
            );

            if ($request->filled('redirect_to_project')) {
                return redirect()->route('admin.work_management.projects.show', encrypt($milestone->project_id))->with('success', 'Milestone added successfully');
            }

            return redirect()->route('admin.work_management.milestones.index')->with('success', 'Milestone added successfully');
        } catch (Exception $e) {
            return redirect()->back()->with('error', 'Error: ' . $e->getMessage())->withInput();
        }
    }

    public function edit($id)
    {
        $user = auth()->user();
        $id = decrypt($id);
        $milestone = Milestone::findOrFail($id);

        $projectQuery = Project::where('status', 'active');
        WorkHierarchyService::applyProjectScope($projectQuery, $user);
        $projects = $projectQuery->get();

        $teamQuery = Team::where('status', 'active');
        WorkHierarchyService::applyTeamScope($teamQuery, $user);
        $teams = $teamQuery->get();

        $staff = WorkHierarchyService::getVisibleStaffQuery($user)->get();

        return view('admin.work_management.milestones.edit', compact('milestone', 'projects', 'teams', 'staff'));
    }

    public function update(Request $request, $id)
    {
        $id = decrypt($id);
        $milestone = Milestone::findOrFail($id);

        $validator = Validator::make($request->all(), [
            'title' => 'required|string|max:255',
            'project_id' => 'required',
            'status' => 'required',
            'start_date' => 'required|date',
            'due_date' => 'nullable|date|after_or_equal:start_date',
        ]);

        if ($validator->fails()) {
            return redirect()->back()->with('error', $validator->errors()->first())->withInput();
        }

        try {
            $data = $request->except(['_token', '_method']);
            $milestone->update($data);
            ProjectMetricsService::updateProjectMetrics($milestone->project_id);

            TaskActivityLog::log(
                'updated',
                "Milestone '{$milestone->title}' updated by " . auth()->user()->name,
                null,
                $milestone->project_id,
                $milestone->id
            );

            return redirect()->route('admin.work_management.milestones.index')->with('success', 'Milestone updated successfully');
        } catch (Exception $e) {
            return redirect()->back()->with('error', 'Error: ' . $e->getMessage())->withInput();
        }
    }

    public function destroy($id)
    {
        $id = decrypt($id);
        $milestone = Milestone::findOrFail($id);
        $projectId = $milestone->project_id;
        $milestone->delete();
        ProjectMetricsService::updateProjectMetrics($projectId);

        return redirect()->back()->with('success', 'Milestone deleted successfully');
    }

    public function changeStatus(Request $request)
    {
        $milestone = Milestone::findOrFail($request->milestone_id);
        $old = $milestone->status;
        $milestone->status = $request->status;
        if ($request->status == 'completed') {
            $milestone->progress = 100;
        }
        $milestone->save();

        ProjectMetricsService::updateProjectMetrics($milestone->project_id);

        TaskActivityLog::log(
            'status_changed',
            "Milestone '{$milestone->title}' status changed from {$old} to {$milestone->status}",
            null,
            $milestone->project_id,
            $milestone->id,
            $old,
            $milestone->status
        );

        return response()->json(['status' => 1, 'message' => 'Status updated']);
    }
}

<?php

namespace App\Http\Controllers\Admin\Hr;

use App\Http\Controllers\Controller;
use App\Models\Admin;
use App\Models\Project;
use App\Models\Milestone;
use App\Models\Tasks;
use App\Models\TaskComment;
use Illuminate\Http\Request;
use Yajra\DataTables\DataTables;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Exception;

class TaskController extends Controller
{
    public function index(Request $request)
    {
        if ($request->ajax()) {
            $organization_id = auth()->user()->organization_id;
            $data = Tasks::with(['project', 'milestone_assigned', 'assigned_by'])
                ->where('organization_id', $organization_id)
                ->get();

            return DataTables::of($data)
                ->addIndexColumn()
                ->addColumn('title', function ($row) {
                    return '<p class="fw-bold mb-0">' . $row->title . '</p>';
                })
                ->addColumn('project', function ($row) {
                    return $row->project->title ?? 'N/A';
                })
                ->addColumn('assigned_to_text', function ($row) {
                    if ($row->assigned_to) {
                        $ids = explode(',', $row->assigned_to);
                        return Admin::whereIn('id', $ids)->pluck('name')->implode(', ');
                    }
                    return '<span class="text-muted">No Staff</span>';
                })
                ->addColumn('status', function ($row) {
                    return GetStatusBadge($row->status);
                })
                ->addColumn('action', function ($row) {
                    $btn = '<div class="btn-group">';
                    $btn .= '<a href="' . route('admin.hr.projects.tasks.show', encrypt($row->id)) . '" class="btn btn-sm btn-soft-info"><i class="fas fa-eye"></i></a>';
                    $btn .= '<a href="' . route('admin.hr.projects.tasks.edit', encrypt($row->id)) . '" class="btn btn-sm btn-soft-primary"><i class="fas fa-edit"></i></a>';
                    $btn .= '<form method="POST" action="' . route('admin.hr.projects.tasks.destroy', encrypt($row->id)) . '" class="ms-1 delete-form">
                                ' . csrf_field() . '
                                ' . method_field('DELETE') . '
                                <button type="button" class="btn btn-sm btn-soft-danger delete-btn"><i class="fas fa-trash"></i></button>
                            </form>';
                    $btn .= '</div>';
                    return $btn;
                })
                ->rawColumns(['title', 'assigned_to_text', 'status', 'action'])
                ->make(true);
        }

        return view('admin.hr.projects.task.index');
    }

    public function create()
    {
        $organization_id = auth()->user()->organization_id;
        $project = Project::where('organization_id', $organization_id)->get();
        return view('admin.hr.projects.task.create', compact('project'));
    }

    public function getProjectData(Request $request)
    {
        $projectId = $request->project_id;
        $project = Project::findOrFail($projectId);
        
        $staffIds = !empty($project->employee_ids) ? explode(',', $project->employee_ids) : [];
        $staff = Admin::whereIn('id', $staffIds)->get();
        $milestones = Milestone::where('project_id', $projectId)->get();

        return response()->json([
            'status' => 1,
            'staff' => $staff,
            'milestones' => $milestones
        ]);
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'title' => 'required|string|max:255',
            'project_id' => 'required',
            'priority' => 'required',
            'start_date' => 'required|date',
            'assigned_to' => 'required|array',
            'status' => 'required',
        ]);

        if ($validator->fails()) {
            return redirect()->back()->with('error', $validator->errors()->first())->withInput();
        }

        try {
            $data = $request->all();
            $data['assigned_to'] = implode(',', $request->assigned_to);
            $data['staff_id'] = auth()->id();
            $data['organization_id'] = auth()->user()->organization_id;

            Tasks::create($data);
            return redirect()->route('admin.hr.projects.tasks.index')->with('success', 'Task added successfully');
        } catch (Exception $e) {
            return redirect()->back()->with('error', 'Error: ' . $e->getMessage())->withInput();
        }
    }

    public function show($id)
    {
        $id = decrypt($id);
        $task = Tasks::with(['project', 'milestone_assigned', 'comments.user'])->findOrFail($id);
        
        $project = $task->project;
        $staffIds = !empty($project->employee_ids) ? explode(',', $project->employee_ids) : [];
        $staff = Admin::whereIn('id', $staffIds)->get();
        $milestones = Milestone::where('project_id', $task->project_id)->get();
        
        return view('admin.hr.projects.task.show', compact('task', 'project', 'staff', 'milestones'));
    }

    public function edit($id)
    {
        $id = decrypt($id);
        $task = Tasks::where('organization_id', auth()->user()->organization_id)->findOrFail($id);
        $project = Project::where('organization_id', auth()->user()->organization_id)->get();
        
        $currentProject = $task->project;
        $staffIds = !empty($currentProject->employee_ids) ? explode(',', $currentProject->employee_ids) : [];
        $staff = Admin::whereIn('id', $staffIds)->get();
        $milestones = Milestone::where('project_id', $task->project_id)->get();

        return view('admin.hr.projects.task.edit', compact('task', 'project', 'staff', 'milestones'));
    }

    public function update(Request $request, $id)
    {
        $id = decrypt($id);
        $task = Tasks::where('organization_id', auth()->user()->organization_id)->findOrFail($id);

        $validator = Validator::make($request->all(), [
            'title' => 'required|string|max:255',
            'project_id' => 'required',
            'status' => 'required',
        ]);

        if ($validator->fails()) {
            return redirect()->back()->with('error', $validator->errors()->first())->withInput();
        }

        try {
            $data = $request->except(['_token', '_method']);
            if (isset($request->assigned_to)) {
                $data['assigned_to'] = implode(',', $request->assigned_to);
            }
            
            $task->update($data);
            return redirect()->route('admin.hr.projects.tasks.index')->with('success', 'Task updated successfully');
        } catch (Exception $e) {
            return redirect()->back()->with('error', 'Error: ' . $e->getMessage())->withInput();
        }
    }

    public function destroy($id)
    {
        $id = decrypt($id);
        $task = Tasks::where('organization_id', auth()->user()->organization_id)->findOrFail($id);
        $task->delete();
        return redirect()->back()->with('success', 'Task deleted successfully');
    }
}

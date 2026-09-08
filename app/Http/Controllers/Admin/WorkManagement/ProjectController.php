<?php

namespace App\Http\Controllers\Admin\WorkManagement;

use App\Http\Controllers\Controller;
use App\Models\Project;
use App\Models\ProjectCategory;
use App\Models\LeadSource;
use App\Models\Client;
use App\Models\HrDepartment;
use App\Models\Team;
use App\Models\Admin;
use App\Models\ExternalOrganization;
use App\Models\ExternalContact;
use App\Models\ExternalTeam;
use App\Models\ProjectMember;
use App\Models\ProjectExternalMember;
use App\Models\ProjectDocument;
use App\Models\TaskActivityLog;
use App\Services\WorkManagement\ProjectMetricsService;
use Illuminate\Http\Request;
use Yajra\DataTables\DataTables;
use Illuminate\Support\Facades\Validator;
use Exception;

class ProjectController extends Controller
{
    public function index(Request $request)
    {
        if ($request->ajax()) {
            $user = auth()->user();
            $query = Project::with(['department', 'team', 'owner', 'project_category']);

            if ($user->organization_id) {
                $query->where('organization_id', $user->organization_id);
            }

            if ($request->filled('status')) {
                $query->where('status', $request->status);
            }
            if ($request->filled('health_status')) {
                $query->where('health_status', $request->health_status);
            }
            if ($request->filled('department_id')) {
                $query->where('department_id', $request->department_id);
            }
            if ($request->filled('team_id')) {
                $query->where('team_id', $request->team_id);
            }

            return DataTables::of($query)
                ->addIndexColumn()
                ->addColumn('code_title', function ($row) {
                    $code = $row->project_code ? '<span class="badge bg-light text-primary border me-1">' . $row->project_code . '</span>' : '';
                    return '<div class="d-flex flex-column">' .
                           '<a href="' . route('admin.work_management.projects.show', encrypt($row->id)) . '" class="fw-bold text-dark text-decoration-none">' . $code . $row->title . '</a>' .
                           '<small class="text-muted">' . ($row->project_category->name ?? 'General') . ' &bull; ' . ($row->project_type ?? 'Internal') . '</small>' .
                           '</div>';
                })
                ->addColumn('team_dept', function ($row) {
                    $dept = $row->department->name ?? 'No Dept';
                    $team = $row->team->name ?? 'No Team';
                    return '<span class="badge bg-soft-info text-info me-1">' . $dept . '</span><span class="badge bg-soft-primary text-primary">' . $team . '</span>';
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
                ->addColumn('health', function ($row) {
                    $health = $row->health_status ?? 'on_track';
                    $badges = [
                        'on_track' => '<span class="badge bg-soft-success text-success border border-success"><i class="fas fa-circle text-success me-1" style="font-size:6px;"></i> On Track</span>',
                        'at_risk' => '<span class="badge bg-soft-warning text-warning border border-warning"><i class="fas fa-circle text-warning me-1" style="font-size:6px;"></i> At Risk</span>',
                        'delayed' => '<span class="badge bg-soft-danger text-danger border border-danger"><i class="fas fa-circle text-danger me-1" style="font-size:6px;"></i> Delayed</span>',
                        'on_hold' => '<span class="badge bg-secondary">On Hold</span>',
                    ];
                    return $badges[$health] ?? '<span class="badge bg-secondary">' . ucfirst($health) . '</span>';
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
                    $btn .= '<a href="' . route('admin.work_management.projects.show', encrypt($row->id)) . '" class="btn btn-sm btn-soft-info" title="Project Portal"><i class="fas fa-eye"></i></a>';
                    $btn .= '<a href="' . route('admin.work_management.projects.edit', encrypt($row->id)) . '" class="btn btn-sm btn-soft-primary ms-1" title="Edit"><i class="fas fa-edit"></i></a>';
                    $btn .= '<form method="POST" action="' . route('admin.work_management.projects.destroy', encrypt($row->id)) . '" class="ms-1 delete-form" onsubmit="return confirm(\'Are you sure you want to delete this project?\')">
                                ' . csrf_field() . '
                                ' . method_field('DELETE') . '
                                <button type="submit" class="btn btn-sm btn-soft-danger"><i class="fas fa-trash"></i></button>
                            </form>';
                    $btn .= '</div>';
                    return $btn;
                })
                ->rawColumns(['code_title', 'team_dept', 'owner', 'progress_bar', 'health', 'timeline', 'status', 'action'])
                ->make(true);
        }

        $departments = HrDepartment::all();
        $teams = Team::where('status', 'active')->get();

        return view('admin.work_management.projects.index', compact('departments', 'teams'));
    }

    public function create()
    {
        $departments = HrDepartment::all();
        $teams = Team::where('status', 'active')->get();
        $categories = ProjectCategory::all();
        $leadSources = LeadSource::all();
        $clients = Client::all();
        $staff = Admin::where('status', 'active')->get();
        $externalOrgs = ExternalOrganization::where('status', 'active')->get();

        return view('admin.work_management.projects.create', compact(
            'departments', 'teams', 'categories', 'leadSources', 'clients', 'staff', 'externalOrgs'
        ));
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'title' => 'required|string|max:255',
            'category_id' => 'required',
            'start_date' => 'required|date',
            'due_date' => 'nullable|date|after_or_equal:start_date',
            'priority' => 'required',
            'status' => 'required',
        ]);

        if ($validator->fails()) {
            return redirect()->back()->with('error', $validator->errors()->first())->withInput();
        }

        try {
            $data = $request->except(['_token', 'internal_members', 'external_org_ids']);
            $data['organization_id'] = auth()->user()->organization_id ?? null;
            $data['staff_id'] = auth()->id();

            // Auto project code if empty
            if (empty($data['project_code'])) {
                $count = Project::count() + 1;
                $data['project_code'] = 'PRJ-' . date('ym') . '-' . str_pad($count, 4, '0', STR_PAD_LEFT);
            }

            $project = Project::create($data);

            // Sync internal members
            if ($request->has('internal_members')) {
                foreach ($request->internal_members as $userId) {
                    ProjectMember::firstOrCreate([
                        'project_id' => $project->id,
                        'user_id' => $userId,
                    ], [
                        'role' => 'Contributor',
                        'access_level' => 'edit',
                        'joined_at' => now(),
                    ]);
                }
            }

            // Sync external organizations
            if ($request->has('external_org_ids')) {
                foreach ($request->external_org_ids as $orgId) {
                    ProjectExternalMember::firstOrCreate([
                        'project_id' => $project->id,
                        'external_organization_id' => $orgId,
                    ], [
                        'role' => 'External Partner',
                        'access_level' => 'edit',
                    ]);
                }
            }

            TaskActivityLog::log(
                'created',
                "Project '{$project->title}' created by " . auth()->user()->name,
                null,
                $project->id
            );

            return redirect()->route('admin.work_management.projects.show', encrypt($project->id))->with('success', 'Project created successfully');
        } catch (Exception $e) {
            return redirect()->back()->with('error', 'Error: ' . $e->getMessage())->withInput();
        }
    }

    public function show($id)
    {
        $id = decrypt($id);
        $project = Project::with([
            'department', 'team.leader', 'owner', 'creator', 'client', 'lead_source', 'project_category',
            'milestones.tasks.activePrimaryAssignee.user',
            'rootTasks.subtasks.activePrimaryAssignee.user',
            'members.department', 'externalMembers.organization', 'externalMembers.contact', 'externalMembers.team',
            'documents.uploader', 'activityLogs.actor', 'meetings'
        ])->findOrFail($id);

        ProjectMetricsService::updateProjectMetrics($project->id);

        $allStaff = Admin::where('status', 'active')->get();
        $allExternalOrgs = ExternalOrganization::where('status', 'active')->get();

        return view('admin.work_management.projects.show', compact('project', 'allStaff', 'allExternalOrgs'));
    }

    public function edit($id)
    {
        $id = decrypt($id);
        $project = Project::with(['members', 'externalMembers'])->findOrFail($id);
        $departments = HrDepartment::all();
        $teams = Team::where('status', 'active')->get();
        $categories = ProjectCategory::all();
        $leadSources = LeadSource::all();
        $clients = Client::all();
        $staff = Admin::where('status', 'active')->get();
        $externalOrgs = ExternalOrganization::where('status', 'active')->get();

        return view('admin.work_management.projects.edit', compact(
            'project', 'departments', 'teams', 'categories', 'leadSources', 'clients', 'staff', 'externalOrgs'
        ));
    }

    public function update(Request $request, $id)
    {
        $id = decrypt($id);
        $project = Project::findOrFail($id);

        $validator = Validator::make($request->all(), [
            'title' => 'required|string|max:255',
            'category_id' => 'required',
            'start_date' => 'required|date',
            'due_date' => 'nullable|date|after_or_equal:start_date',
            'priority' => 'required',
            'status' => 'required',
        ]);

        if ($validator->fails()) {
            return redirect()->back()->with('error', $validator->errors()->first())->withInput();
        }

        try {
            $data = $request->except(['_token', '_method', 'internal_members', 'external_org_ids']);
            $project->update($data);

            if ($request->has('internal_members')) {
                ProjectMember::where('project_id', $project->id)->delete();
                foreach ($request->internal_members as $userId) {
                    ProjectMember::create([
                        'project_id' => $project->id,
                        'user_id' => $userId,
                        'role' => 'Contributor',
                        'access_level' => 'edit',
                        'joined_at' => now(),
                    ]);
                }
            }

            if ($request->has('external_org_ids')) {
                ProjectExternalMember::where('project_id', $project->id)->delete();
                foreach ($request->external_org_ids as $orgId) {
                    ProjectExternalMember::create([
                        'project_id' => $project->id,
                        'external_organization_id' => $orgId,
                        'role' => 'External Partner',
                        'access_level' => 'edit',
                    ]);
                }
            }

            ProjectMetricsService::updateProjectMetrics($project->id);

            TaskActivityLog::log(
                'updated',
                "Project '{$project->title}' updated by " . auth()->user()->name,
                null,
                $project->id
            );

            return redirect()->route('admin.work_management.projects.show', encrypt($project->id))->with('success', 'Project updated successfully');
        } catch (Exception $e) {
            return redirect()->back()->with('error', 'Error: ' . $e->getMessage())->withInput();
        }
    }

    public function destroy($id)
    {
        $id = decrypt($id);
        $project = Project::findOrFail($id);
        $project->delete();
        return redirect()->route('admin.work_management.projects.index')->with('success', 'Project deleted successfully');
    }

    public function uploadDocument(Request $request, $projectId)
    {
        $projectId = decrypt($projectId);
        $project = Project::findOrFail($projectId);

        $request->validate([
            'title' => 'required|string|max:255',
            'file' => 'required|file|max:20480',
        ]);

        if ($request->hasFile('file')) {
            $file = $request->file('file');
            $fileName = time() . '_' . $file->getClientOriginalName();
            $path = $file->storeAs('project_docs', $fileName, 'public');

            ProjectDocument::create([
                'organization_id' => auth()->user()->organization_id ?? null,
                'project_id' => $project->id,
                'uploaded_by' => auth()->id(),
                'title' => $request->title,
                'file_name' => $file->getClientOriginalName(),
                'file_path' => 'storage/' . $path,
                'file_type' => $file->getClientOriginalExtension(),
                'file_size' => $file->getSize(),
                'document_type' => $request->document_type ?? 'General',
                'version' => $request->version ?? '1.0',
            ]);

            TaskActivityLog::log(
                'file_uploaded',
                "Document '{$request->title}' uploaded to project by " . auth()->user()->name,
                null,
                $project->id
            );

            return redirect()->back()->with('success', 'Document uploaded successfully');
        }

        return redirect()->back()->with('error', 'No file provided');
    }
}

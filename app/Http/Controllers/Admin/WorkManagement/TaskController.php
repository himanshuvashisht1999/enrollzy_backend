<?php

namespace App\Http\Controllers\Admin\WorkManagement;

use App\Http\Controllers\Controller;
use App\Models\Tasks;
use App\Models\Project;
use App\Models\Milestone;
use App\Models\Team;
use App\Models\Admin;
use App\Models\ExternalOrganization;
use App\Models\ExternalContact;
use App\Models\ExternalTeam;
use App\Models\TaskAssignee;
use App\Models\TaskChecklist;
use App\Models\TaskTimeEntry;
use App\Models\TaskAttachment;
use App\Models\TaskDependency;
use App\Models\TaskActivityLog;
use App\Services\WorkManagement\WorkAssignmentService;
use App\Services\WorkManagement\ProjectMetricsService;
use Illuminate\Http\Request;
use Yajra\DataTables\DataTables;
use Illuminate\Support\Facades\Validator;
use Exception;

class TaskController extends Controller
{
    protected $assignmentService;

    public function __construct(WorkAssignmentService $assignmentService)
    {
        $this->assignmentService = $assignmentService;
    }

    public function index(Request $request)
    {
        if ($request->ajax()) {
            $user = auth()->user();
            $query = Tasks::with(['project', 'milestone_assigned', 'team', 'activePrimaryAssignee.user', 'subtasks']);

            if ($user->organization_id) {
                $query->where('organization_id', $user->organization_id);
            }

            if ($request->filled('project_id')) {
                $query->where('project_id', $request->project_id);
            }
            if ($request->filled('milestone_id')) {
                $query->where('milestone', $request->milestone_id);
            }
            if ($request->filled('team_id')) {
                $query->where('team_id', $request->team_id);
            }
            if ($request->filled('status')) {
                $query->where('status', $request->status);
            }
            if ($request->filled('priority')) {
                $query->where('priority', $request->priority);
            }
            if ($request->filled('is_subtask')) {
                if ($request->is_subtask == '1') {
                    $query->whereNotNull('parent_task_id');
                } elseif ($request->is_subtask == '0') {
                    $query->whereNull('parent_task_id');
                }
            }

            if ($request->filled('assigned_to')) {
                $assigneeUserId = $request->assigned_to;
                $query->where(function ($q) use ($assigneeUserId) {
                    $q->where('assigned_to', $assigneeUserId)
                      ->orWhereHas('activeAssignees', function ($aq) use ($assigneeUserId) {
                          $aq->where('user_id', $assigneeUserId);
                      });
                });
            }

            if ($request->filled('overdue') && $request->overdue == '1') {
                $query->whereNotIn('status', ['completed', 'verified', 'closed'])
                      ->whereNotNull('due_date')
                      ->where('due_date', '<', date('Y-m-d'));
            }

            return DataTables::of($query)
                ->addIndexColumn()
                ->addColumn('title_code', function ($row) {
                    $prefix = $row->isSubtask() ? '<span class="text-muted me-1">↳</span>' : '';
                    $code = $row->task_code ? '<span class="badge bg-light text-secondary border me-1">' . $row->task_code . '</span>' : '';
                    $subCount = $row->subtasks->count() > 0 ? ' <span class="badge bg-soft-info text-info rounded-pill ms-1">' . $row->subtasks->count() . ' subtasks</span>' : '';
                    return '<div class="d-flex flex-column">' .
                           '<a href="' . route('admin.work_management.tasks.show', encrypt($row->id)) . '" class="fw-bold text-dark text-decoration-none">' . $prefix . $code . $row->title . $subCount . '</a>' .
                           '<small class="text-muted">' . ($row->project->title ?? 'No Project') . ' &bull; ' . ($row->milestone_assigned->title ?? 'No Milestone') . '</small>' .
                           '</div>';
                })
                ->addColumn('assignee', function ($row) {
                    $primary = $row->activePrimaryAssignee;
                    if ($primary) {
                        return '<span class="badge bg-light text-dark border px-2 py-1"><i class="fas fa-user me-1 text-primary"></i>' . $primary->display_name . '</span>';
                    } elseif ($row->assigned_to) {
                        $staff = Admin::find($row->assigned_to);
                        return $staff ? '<span class="badge bg-light text-dark border px-2 py-1"><i class="fas fa-user me-1 text-primary"></i>' . $staff->name . '</span>' : '<span class="text-muted small">Unassigned</span>';
                    }
                    return '<span class="text-muted small">Unassigned</span>';
                })
                ->addColumn('team_badge', function ($row) {
                    return $row->team ? '<span class="badge bg-soft-primary text-primary">' . $row->team->name . '</span>' : '<span class="text-muted small">-</span>';
                })
                ->addColumn('priority_badge', function ($row) {
                    return $row->getPriorityBadge();
                })
                ->addColumn('due_date_formatted', function ($row) {
                    if (!$row->due_date) return '<span class="text-muted small">No Deadline</span>';
                    $isOverdue = $row->due_date < date('Y-m-d') && !in_array($row->status, ['completed', 'verified', 'closed']);
                    $text = date('M d, Y', strtotime($row->due_date));
                    return $isOverdue ? '<span class="text-danger fw-bold"><i class="fas fa-exclamation-circle me-1"></i>' . $text . '</span>' : '<span class="small text-muted">' . $text . '</span>';
                })
                ->addColumn('status_badge', function ($row) {
                    return $row->getStatusBadge();
                })
                ->addColumn('action', function ($row) {
                    $btn = '<div class="btn-group">';
                    $btn .= '<a href="' . route('admin.work_management.tasks.show', encrypt($row->id)) . '" class="btn btn-sm btn-soft-info" title="View"><i class="fas fa-eye"></i></a>';
                    $btn .= '<a href="' . route('admin.work_management.tasks.edit', encrypt($row->id)) . '" class="btn btn-sm btn-soft-primary ms-1" title="Edit"><i class="fas fa-edit"></i></a>';
                    $btn .= '<form method="POST" action="' . route('admin.work_management.tasks.destroy', encrypt($row->id)) . '" class="ms-1 delete-form" onsubmit="return confirm(\'Delete this task?\')">
                                ' . csrf_field() . '
                                ' . method_field('DELETE') . '
                                <button type="submit" class="btn btn-sm btn-soft-danger"><i class="fas fa-trash"></i></button>
                            </form>';
                    $btn .= '</div>';
                    return $btn;
                })
                ->rawColumns(['title_code', 'assignee', 'team_badge', 'priority_badge', 'due_date_formatted', 'status_badge', 'action'])
                ->make(true);
        }

        $projects = Project::where('status', 'active')->get();
        $teams = Team::where('status', 'active')->get();
        $staff = Admin::where('status', 'active')->get();

        return view('admin.work_management.tasks.index', compact('projects', 'teams', 'staff'));
    }

    public function create(Request $request)
    {
        $selectedProjectId = $request->project_id ? (is_numeric($request->project_id) ? $request->project_id : decrypt($request->project_id)) : null;
        $parentTaskId = $request->parent_task_id ? (is_numeric($request->parent_task_id) ? $request->parent_task_id : decrypt($request->parent_task_id)) : null;
        
        $parentTask = $parentTaskId ? Tasks::find($parentTaskId) : null;
        if ($parentTask) {
            $selectedProjectId = $parentTask->project_id;
        }

        $projects = Project::all();
        $teams = Team::where('status', 'active')->get();
        $staff = Admin::where('status', 'active')->get();
        $externalOrgs = ExternalOrganization::where('status', 'active')->get();
        $externalTeams = ExternalTeam::where('status', 'active')->get();
        $externalContacts = ExternalContact::where('status', 'active')->get();

        $initialProjectId = old('project_id', $selectedProjectId);
        $milestones = $initialProjectId ? Milestone::where('project_id', $initialProjectId)->get() : collect();

        return view('admin.work_management.tasks.create', compact(
            'projects', 'teams', 'staff', 'externalOrgs', 'externalTeams', 'externalContacts', 'selectedProjectId', 'parentTask', 'milestones'
        ));
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'title' => 'required|string|max:255',
            'project_id' => 'required',
            'priority' => 'required',
            'status' => 'required',
            'start_date' => 'nullable|date',
            'due_date' => 'nullable|date|after_or_equal:start_date',
        ]);

        if ($validator->fails()) {
            return redirect()->back()->with('error', $validator->errors()->first())->withInput();
        }

        try {
            $data = $request->except(['_token', 'assignee_type', 'assignee_id', 'attachments']);
            $data['organization_id'] = auth()->user()->organization_id ?? null;
            $data['created_by'] = auth()->id();
            $data['staff_id'] = auth()->id();

            // Auto task code
            if (empty($data['task_code'])) {
                $count = Tasks::count() + 1;
                $prefix = !empty($data['parent_task_id']) ? 'SUB' : 'TSK';
                $data['task_code'] = $prefix . '-' . date('ym') . '-' . str_pad($count, 4, '0', STR_PAD_LEFT);
            }

            $task = Tasks::create($data);

            // Assign work if specified
            if ($request->filled('assignee_type') && $request->filled('assignee_id')) {
                $this->assignmentService->assign(
                    $task->id,
                    $request->assignee_type,
                    $request->assignee_id,
                    'Assignee',
                    true,
                    $task->due_date,
                    auth()->id()
                );
            }

            // Handle multiple uploaded files / photos
            $uploadedCount = 0;
            if ($request->hasFile('attachments')) {
                $files = $request->file('attachments');
                if (!is_array($files)) {
                    $files = [$files];
                }
                $destinationPath = public_path('uploads/tasks/attachments');
                if (!file_exists($destinationPath)) {
                    mkdir($destinationPath, 0777, true);
                }

                foreach ($files as $file) {
                    if ($file && $file->isValid()) {
                        $originalName = $file->getClientOriginalName();
                        $ext = $file->getClientOriginalExtension();
                        $size = $file->getSize();
                        $safeName = time() . '_' . uniqid() . '_' . preg_replace('/[^a-zA-Z0-9_\.-]/', '_', $originalName);
                        $file->move($destinationPath, $safeName);

                        TaskAttachment::create([
                            'task_id' => $task->id,
                            'uploaded_by' => auth()->id(),
                            'file_name' => $originalName,
                            'file_path' => 'uploads/tasks/attachments/' . $safeName,
                            'file_type' => $ext,
                            'file_size' => $size,
                        ]);
                        $uploadedCount++;
                    }
                }
            }

            ProjectMetricsService::updateProjectMetrics($task->project_id);

            $attachNote = $uploadedCount > 0 ? " with {$uploadedCount} attachment(s)" : "";
            TaskActivityLog::log(
                'created',
                ($task->isSubtask() ? "Subtask '{$task->title}'" : "Task '{$task->title}'") . " created{$attachNote} by " . auth()->user()->name,
                $task->id,
                $task->project_id,
                $task->milestone
            );

            if ($task->isSubtask()) {
                return redirect()->route('admin.work_management.tasks.show', encrypt($task->parent_task_id))->with('success', 'Subtask created successfully');
            }

            return redirect()->route('admin.work_management.tasks.show', encrypt($task->id))->with('success', 'Task created successfully');
        } catch (Exception $e) {
            return redirect()->back()->with('error', 'Error: ' . $e->getMessage())->withInput();
        }
    }

    public function show($id)
    {
        $id = is_numeric($id) ? $id : decrypt($id);
        $task = Tasks::with([
            'project.department', 'project.team', 'milestone_assigned', 'team.leader', 'parentTask',
            'subtasks.activePrimaryAssignee.user', 'subtasks.team',
            'activeAssignees.user', 'activeAssignees.team', 'activeAssignees.externalContact', 'activeAssignees.externalTeam',
            'delegations.delegator', 'delegations.targetUser', 'delegations.targetTeam',
            'dependencies.prerequisiteTask', 'checklists.completedBy',
            'timeEntries.user', 'attachments.uploader', 'comments.user', 'comments.replies.user', 'activityLogs.actor'
        ])->findOrFail($id);

        $allStaff = Admin::where('status', 'active')->get();
        $allTeams = Team::where('status', 'active')->get();
        $canPerformAction = $task->canUserPerformAction(auth()->id());

        return view('admin.work_management.tasks.show', compact('task', 'allStaff', 'allTeams', 'canPerformAction'));
    }

    public function edit($id)
    {
        $id = is_numeric($id) ? $id : decrypt($id);
        $task = Tasks::with('attachments.uploader')->findOrFail($id);
        $projects = Project::all();
        $teams = Team::where('status', 'active')->get();
        $staff = Admin::where('status', 'active')->get();
        $milestones = Milestone::where('project_id', $task->project_id)->get();

        return view('admin.work_management.tasks.edit', compact('task', 'projects', 'teams', 'staff', 'milestones'));
    }

    public function update(Request $request, $id)
    {
        $id = is_numeric($id) ? $id : decrypt($id);
        $task = Tasks::findOrFail($id);

        $validator = Validator::make($request->all(), [
            'title' => 'required|string|max:255',
            'project_id' => 'required',
            'priority' => 'required',
            'status' => 'required',
            'due_date' => 'nullable|date|after_or_equal:start_date',
        ]);

        if ($validator->fails()) {
            return redirect()->back()->with('error', $validator->errors()->first())->withInput();
        }

        try {
            $data = $request->except(['_token', '_method', 'attachments']);
            $oldStatus = $task->status;
            $task->update($data);

            // Handle multiple uploaded files / photos on update
            $uploadedCount = 0;
            if ($request->hasFile('attachments')) {
                $files = $request->file('attachments');
                if (!is_array($files)) {
                    $files = [$files];
                }
                $destinationPath = public_path('uploads/tasks/attachments');
                if (!file_exists($destinationPath)) {
                    mkdir($destinationPath, 0777, true);
                }

                foreach ($files as $file) {
                    if ($file && $file->isValid()) {
                        $originalName = $file->getClientOriginalName();
                        $ext = $file->getClientOriginalExtension();
                        $size = $file->getSize();
                        $safeName = time() . '_' . uniqid() . '_' . preg_replace('/[^a-zA-Z0-9_\.-]/', '_', $originalName);
                        $file->move($destinationPath, $safeName);

                        TaskAttachment::create([
                            'task_id' => $task->id,
                            'uploaded_by' => auth()->id(),
                            'file_name' => $originalName,
                            'file_path' => 'uploads/tasks/attachments/' . $safeName,
                            'file_type' => $ext,
                            'file_size' => $size,
                        ]);
                        $uploadedCount++;
                    }
                }
            }

            if ($oldStatus != $task->status) {
                if ($task->status == 'completed') $task->completed_at = now();
                if ($task->status == 'verified') $task->verified_at = now();
                if ($task->status == 'closed') $task->closed_at = now();
                $task->save();
            }

            ProjectMetricsService::updateProjectMetrics($task->project_id);

            $attachNote = $uploadedCount > 0 ? " with {$uploadedCount} new attachment(s)" : "";
            TaskActivityLog::log(
                'updated',
                "Task '{$task->title}' details updated{$attachNote} by " . auth()->user()->name,
                $task->id,
                $task->project_id,
                $task->milestone
            );

            return redirect()->route('admin.work_management.tasks.show', encrypt($task->id))->with('success', 'Task updated successfully');
        } catch (Exception $e) {
            return redirect()->back()->with('error', 'Error: ' . $e->getMessage())->withInput();
        }
    }

    public function destroy($id)
    {
        $id = is_numeric($id) ? $id : decrypt($id);
        $task = Tasks::findOrFail($id);
        $projectId = $task->project_id;
        $parentId = $task->parent_task_id;
        $task->delete();

        ProjectMetricsService::updateProjectMetrics($projectId);

        if ($parentId) {
            return redirect()->route('admin.work_management.tasks.show', encrypt($parentId))->with('success', 'Subtask deleted');
        }

        return redirect()->route('admin.work_management.tasks.index')->with('success', 'Task deleted');
    }

    /**
     * Change Status with Strict Single-Assignee and Dependency checks
     */
    public function changeStatus(Request $request)
    {
        $task = Tasks::findOrFail($request->task_id);
        $userId = auth()->id();

        // 1. Single-Assignee Custody check:
        if (!$task->canUserPerformAction($userId)) {
            return response()->json([
                'status' => 0,
                'message' => 'Action denied: You are not the active assignee for this subtask/task.'
            ], 403);
        }

        // 2. Dependency check (Finish-to-Start):
        if (in_array($request->status, ['in_progress', 'completed', 'verified', 'closed'])) {
            $uncompletedDeps = $task->dependencies()->whereHas('prerequisiteTask', function ($q) {
                $q->whereNotIn('status', ['completed', 'verified', 'closed']);
            })->with('prerequisiteTask')->get();

            if ($uncompletedDeps->count() > 0) {
                $prereqTitles = $uncompletedDeps->pluck('prerequisiteTask.title')->implode(', ');
                return response()->json([
                    'status' => 0,
                    'message' => "Cannot transition status: Prerequisite task(s) [{$prereqTitles}] must be completed first."
                ], 422);
            }
        }

        $oldStatus = $task->status;
        $newStatus = $request->status;
        $task->status = $newStatus;

        if ($newStatus == 'completed') $task->completed_at = now();
        if ($newStatus == 'verified') {
            $task->verified_by = $userId;
            $task->verified_at = now();
        }
        if ($newStatus == 'closed') $task->closed_at = now();

        $task->save();
        ProjectMetricsService::updateProjectMetrics($task->project_id);

        TaskActivityLog::log(
            'status_changed',
            "Status changed from '{$oldStatus}' to '{$newStatus}' by " . auth()->user()->name,
            $task->id,
            $task->project_id,
            $task->milestone,
            $oldStatus,
            $newStatus
        );

        return response()->json([
            'status' => 1,
            'message' => 'Status updated successfully',
            'badge' => $task->getStatusBadge()
        ]);
    }

    /**
     * Subtask Reassignment: Transfers custody & locks previous assignee
     */
    public function reassign(Request $request)
    {
        $request->validate([
            'subtask_id' => 'required',
            'user_id' => 'required',
        ]);

        $subtaskId = is_numeric($request->subtask_id) ? $request->subtask_id : decrypt($request->subtask_id);
        $newUserId = $request->user_id;
        $newTeamId = $request->team_id ?? null;
        $reason = $request->reason ?? null;

        $newAssignee = $this->assignmentService->reassignSubtask($subtaskId, $newUserId, $newTeamId, $reason);

        return response()->json([
            'status' => 1,
            'message' => 'Subtask reassigned successfully. Previous assignee custody has been revoked.',
            'assignee_name' => $newAssignee->display_name
        ]);
    }

    /**
     * Delegate Task across teams
     */
    public function delegate(Request $request)
    {
        $request->validate([
            'task_id' => 'required',
            'to_type' => 'required',
            'to_id' => 'required',
        ]);

        $taskId = is_numeric($request->task_id) ? $request->task_id : decrypt($request->task_id);
        $this->assignmentService->delegate($taskId, $request->to_type, $request->to_id, $request->remarks);

        return response()->json([
            'status' => 1,
            'message' => 'Task delegated successfully.'
        ]);
    }

    /**
     * Kanban Board View
     */
    public function kanban(Request $request)
    {
        $projectId = null;
        if ($request->filled('project_id')) {
            try {
                $projectId = is_numeric($request->project_id) ? $request->project_id : decrypt($request->project_id);
            } catch (\Exception $e) {
                $projectId = null;
            }
        }
        $projects = Project::where('status', 'active')->get();

        $query = Tasks::with(['project', 'team', 'activePrimaryAssignee.user']);
        if ($projectId) {
            $query->where('project_id', $projectId);
        }

        $allTasks = $query->get();

        $columns = [
            'backlog' => ['title' => 'Backlog', 'badge' => 'bg-secondary', 'tasks' => $allTasks->where('status', 'backlog')],
            'not_started' => ['title' => 'Not Started', 'badge' => 'bg-secondary', 'tasks' => $allTasks->where('status', 'not_started')],
            'in_progress' => ['title' => 'In Progress', 'badge' => 'bg-primary', 'tasks' => $allTasks->whereIn('status', ['in_progress', 'accepted'])],
            'under_review' => ['title' => 'Under Review', 'badge' => 'bg-warning text-dark', 'tasks' => $allTasks->where('status', 'under_review')],
            'completed' => ['title' => 'Completed', 'badge' => 'bg-success', 'tasks' => $allTasks->where('status', 'completed')],
            'verified' => ['title' => 'Verified & Closed', 'badge' => 'bg-dark', 'tasks' => $allTasks->whereIn('status', ['verified', 'closed'])],
        ];

        return view('admin.work_management.tasks.kanban', compact('columns', 'projects', 'projectId'));
    }

    // Checklists operations
    public function addChecklist(Request $request)
    {
        $request->validate([
            'task_id' => 'required',
            'title' => 'required|string|max:255',
        ]);

        $checklist = TaskChecklist::create([
            'task_id' => $request->task_id,
            'title' => $request->title,
            'sort_order' => TaskChecklist::where('task_id', $request->task_id)->count() + 1,
        ]);

        return response()->json(['status' => 1, 'checklist' => $checklist]);
    }

    public function toggleChecklist(Request $request)
    {
        $checklist = TaskChecklist::findOrFail($request->checklist_id);
        $checklist->is_completed = !$checklist->is_completed;
        $checklist->completed_by = $checklist->is_completed ? auth()->id() : null;
        $checklist->completed_at = $checklist->is_completed ? now() : null;
        $checklist->save();

        return response()->json(['status' => 1, 'is_completed' => $checklist->is_completed]);
    }

    public function deleteChecklist($id)
    {
        TaskChecklist::findOrFail($id)->delete();
        return response()->json(['status' => 1]);
    }

    // Time Tracking operations
    public function addTimeEntry(Request $request)
    {
        $request->validate([
            'task_id' => 'required',
            'duration_minutes' => 'required|numeric|min:1',
        ]);

        $entry = TaskTimeEntry::create([
            'task_id' => $request->task_id,
            'user_id' => auth()->id(),
            'duration_minutes' => $request->duration_minutes,
            'description' => $request->description,
            'started_at' => now(),
            'ended_at' => now(),
        ]);

        // Update task actual hours
        $task = Tasks::find($request->task_id);
        if ($task) {
            $totalMins = $task->timeEntries()->sum('duration_minutes');
            $task->actual_hours = round($totalMins / 60, 2);
            $task->save();
        }

        return response()->json(['status' => 1, 'entry' => $entry]);
    }

    // Attachment operations
    public function addAttachment(Request $request)
    {
        $request->validate([
            'task_id' => 'required',
        ]);

        $task = Tasks::findOrFail($request->task_id);
        $destinationPath = public_path('uploads/tasks/attachments');
        if (!file_exists($destinationPath)) {
            mkdir($destinationPath, 0777, true);
        }

        $files = [];
        if ($request->hasFile('files')) {
            $files = $request->file('files');
        } elseif ($request->hasFile('file')) {
            $files = [$request->file('file')];
        } elseif ($request->hasFile('attachments')) {
            $files = $request->file('attachments');
        }

        if (empty($files)) {
            return response()->json(['status' => 0, 'message' => 'No files provided.'], 422);
        }

        if (!is_array($files)) {
            $files = [$files];
        }

        $createdAttachments = [];
        foreach ($files as $file) {
            if ($file && $file->isValid()) {
                $originalName = $file->getClientOriginalName();
                $ext = $file->getClientOriginalExtension();
                $size = $file->getSize();
                $safeName = time() . '_' . uniqid() . '_' . preg_replace('/[^a-zA-Z0-9_\.-]/', '_', $originalName);
                $file->move($destinationPath, $safeName);

                $att = TaskAttachment::create([
                    'task_id' => $task->id,
                    'uploaded_by' => auth()->id(),
                    'file_name' => $originalName,
                    'file_path' => 'uploads/tasks/attachments/' . $safeName,
                    'file_type' => $ext,
                    'file_size' => $size,
                ]);

                $att->load('uploader');
                $createdAttachments[] = $att;
            }
        }

        TaskActivityLog::log(
            'file_uploaded',
            count($createdAttachments) . " attachment(s) uploaded by " . auth()->user()->name,
            $task->id,
            $task->project_id,
            $task->milestone
        );

        return response()->json([
            'status' => 1,
            'message' => count($createdAttachments) . ' file(s) uploaded successfully.',
            'attachment' => $createdAttachments[0] ?? null,
            'attachments' => $createdAttachments
        ]);
    }

    public function deleteAttachment($id)
    {
        $attachment = TaskAttachment::findOrFail($id);
        $task = $attachment->task;

        $user = auth()->user();
        $isAuthorized = ($attachment->uploaded_by == auth()->id())
            || ($user->is_admin ?? false)
            || (method_exists($user, 'isSuperAdmin') && $user->isSuperAdmin())
            || (in_array(strtolower($user->role ?? ''), ['superadmin', 'admin']));

        if (!$isAuthorized) {
            return response()->json(['status' => 0, 'message' => 'Unauthorized to delete this file.'], 403);
        }

        $fullPath = public_path($attachment->file_path);
        if (file_exists($fullPath) && is_file($fullPath)) {
            @unlink($fullPath);
        }

        $fileName = $attachment->file_name;
        $attachment->delete();

        if ($task) {
            TaskActivityLog::log(
                'file_deleted',
                "Attachment '{$fileName}' removed by " . auth()->user()->name,
                $task->id,
                $task->project_id,
                $task->milestone
            );
        }

        return response()->json(['status' => 1, 'message' => 'Attachment deleted successfully.']);
    }

    // Dynamic Cascader AJAX
    public function getProjectData(Request $request)
    {
        $projectId = $request->project_id;
        $project = Project::with(['milestones', 'team.members', 'members'])->findOrFail($projectId);

        $milestones = $project->milestones;
        $teams = Team::where('status', 'active')->get();
        $staff = Admin::where('status', 'active')->get();

        return response()->json([
            'status' => 1,
            'milestones' => $milestones,
            'teams' => $teams,
            'staff' => $staff,
        ]);
    }
}

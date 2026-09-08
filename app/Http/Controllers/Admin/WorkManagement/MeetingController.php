<?php

namespace App\Http\Controllers\Admin\WorkManagement;

use App\Http\Controllers\Controller;
use App\Models\Meeting;
use App\Models\MeetingParticipant;
use App\Models\Project;
use App\Models\Admin;
use App\Models\Tasks;
use App\Models\TaskActivityLog;
use Illuminate\Http\Request;
use Yajra\DataTables\DataTables;
use Illuminate\Support\Facades\Validator;
use Exception;

class MeetingController extends Controller
{
    public function index(Request $request)
    {
        if ($request->ajax()) {
            $user = auth()->user();
            $query = Meeting::with(['project', 'creator', 'participants.user']);

            if ($user->organization_id) {
                $query->where('organization_id', $user->organization_id);
            }
            if ($request->filled('project_id')) {
                $query->where('project_id', $request->project_id);
            }

            return DataTables::of($query)
                ->addIndexColumn()
                ->addColumn('title_meeting', function ($row) {
                    return '<div class="d-flex flex-column">' .
                           '<a href="' . route('admin.work_management.meetings.show', encrypt($row->id)) . '" class="fw-bold text-dark text-decoration-none">' . $row->title . '</a>' .
                           '<small class="text-muted">' . ($row->project->title ?? 'General Meeting') . '</small>' .
                           '</div>';
                })
                ->addColumn('datetime', function ($row) {
                    return '<div class="small fw-semibold">' . date('M d, Y', strtotime($row->meeting_date)) . '</div>' .
                           '<small class="text-muted">' . date('h:i A', strtotime($row->start_time)) . ($row->end_time ? ' - ' . date('h:i A', strtotime($row->end_time)) : '') . '</small>';
                })
                ->addColumn('meeting_type_badge', function ($row) {
                    return '<span class="badge bg-soft-info text-info text-capitalize">' . $row->meeting_type . '</span>';
                })
                ->addColumn('participants_count', function ($row) {
                    return '<span class="badge bg-light text-dark border rounded-pill px-3">' . $row->participants->count() . ' participants</span>';
                })
                ->addColumn('status', function ($row) {
                    return GetStatusBadge($row->status);
                })
                ->addColumn('action', function ($row) {
                    $btn = '<div class="btn-group">';
                    $btn .= '<a href="' . route('admin.work_management.meetings.show', encrypt($row->id)) . '" class="btn btn-sm btn-soft-info"><i class="fas fa-eye"></i></a>';
                    $btn .= '<form method="POST" action="' . route('admin.work_management.meetings.destroy', encrypt($row->id)) . '" class="ms-1 delete-form" onsubmit="return confirm(\'Delete this meeting?\')">
                                ' . csrf_field() . '
                                ' . method_field('DELETE') . '
                                <button type="submit" class="btn btn-sm btn-soft-danger"><i class="fas fa-trash"></i></button>
                            </form>';
                    $btn .= '</div>';
                    return $btn;
                })
                ->rawColumns(['title_meeting', 'datetime', 'meeting_type_badge', 'participants_count', 'status', 'action'])
                ->make(true);
        }

        $projects = Project::where('status', 'active')->get();
        return view('admin.work_management.meetings.index', compact('projects'));
    }

    public function create()
    {
        $projects = Project::where('status', 'active')->get();
        $staff = Admin::where('status', 'active')->get();
        return view('admin.work_management.meetings.create', compact('projects', 'staff'));
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'title' => 'required|string|max:255',
            'meeting_date' => 'required|date',
            'start_time' => 'required',
        ]);

        if ($validator->fails()) {
            return redirect()->back()->with('error', $validator->errors()->first())->withInput();
        }

        try {
            $data = $request->except(['_token', 'participant_ids']);
            $data['organization_id'] = auth()->user()->organization_id ?? null;
            $data['created_by'] = auth()->id();

            $meeting = Meeting::create($data);

            if ($request->has('participant_ids')) {
                foreach ($request->participant_ids as $userId) {
                    MeetingParticipant::create([
                        'meeting_id' => $meeting->id,
                        'user_id' => $userId,
                        'response' => 'pending',
                    ]);
                }
            }

            return redirect()->route('admin.work_management.meetings.show', encrypt($meeting->id))->with('success', 'Meeting scheduled successfully');
        } catch (Exception $e) {
            return redirect()->back()->with('error', 'Error: ' . $e->getMessage())->withInput();
        }
    }

    public function show($id)
    {
        $id = decrypt($id);
        $meeting = Meeting::with(['project', 'creator', 'participants.user'])->findOrFail($id);
        $allStaff = Admin::where('status', 'active')->get();

        return view('admin.work_management.meetings.show', compact('meeting', 'allStaff'));
    }

    public function destroy($id)
    {
        $id = decrypt($id);
        $meeting = Meeting::findOrFail($id);
        $meeting->delete();
        return redirect()->route('admin.work_management.meetings.index')->with('success', 'Meeting deleted');
    }

    public function convertDecisionToTask(Request $request)
    {
        $request->validate([
            'meeting_id' => 'required',
            'title' => 'required|string|max:255',
        ]);

        $meeting = Meeting::findOrFail($request->meeting_id);
        $count = Tasks::count() + 1;

        $task = Tasks::create([
            'organization_id' => $meeting->organization_id,
            'project_id' => $meeting->project_id ?: Project::first()->id ?? 1,
            'task_code' => 'TSK-' . date('ym') . '-' . str_pad($count, 4, '0', STR_PAD_LEFT),
            'title' => $request->title,
            'description' => "Created from Decision in Meeting: '{$meeting->title}'\n" . ($request->description ?? ''),
            'priority' => $request->priority ?? 'medium',
            'status' => 'not_started',
            'due_date' => $request->due_date,
            'assigned_to' => $request->assigned_to,
            'created_by' => auth()->id(),
            'staff_id' => auth()->id(),
        ]);

        TaskActivityLog::log(
            'created',
            "Task '{$task->title}' created from Meeting decision '{$meeting->title}'",
            $task->id,
            $task->project_id
        );

        return response()->json([
            'status' => 1,
            'message' => 'Task generated from meeting decision successfully!',
            'task_url' => route('admin.work_management.tasks.show', encrypt($task->id))
        ]);
    }
}

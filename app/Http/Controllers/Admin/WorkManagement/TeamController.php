<?php

namespace App\Http\Controllers\Admin\WorkManagement;

use App\Http\Controllers\Controller;
use App\Models\Team;
use App\Models\TeamMember;
use App\Models\HrDepartment;
use App\Models\Admin;
use App\Models\TaskActivityLog;
use Illuminate\Http\Request;
use Yajra\DataTables\DataTables;
use Illuminate\Support\Facades\Validator;
use Exception;

class TeamController extends Controller
{
    public function index(Request $request)
    {
        if ($request->ajax()) {
            $user = auth()->user();
            $query = Team::with(['department', 'parent', 'leader', 'members']);

            if ($user->organization_id) {
                $query->where('organization_id', $user->organization_id);
            }
            if ($request->filled('department_id')) {
                $query->where('department_id', $request->department_id);
            }
            if ($request->filled('status')) {
                $query->where('status', $request->status);
            }

            return DataTables::of($query)
                ->addIndexColumn()
                ->addColumn('name_code', function ($row) {
                    $code = $row->code ? '<span class="badge bg-light text-primary border me-1">' . $row->code . '</span>' : '';
                    return '<div class="d-flex flex-column">' .
                           '<a href="' . route('admin.work_management.teams.show', encrypt($row->id)) . '" class="fw-bold text-dark text-decoration-none">' . $code . $row->name . '</a>' .
                           '<small class="text-muted">' . ($row->description ? \Illuminate\Support\Str::limit($row->description, 35) : 'No description') . '</small>' .
                           '</div>';
                })
                ->addColumn('parent_team', function ($row) {
                    return $row->parent ? '<span class="badge bg-soft-info text-info"><i class="fas fa-sitemap me-1"></i>' . $row->parent->name . '</span>' : '<span class="text-muted small">Top Level (Parent)</span>';
                })
                ->addColumn('department', function ($row) {
                    return $row->department->name ?? '<span class="text-muted small">Cross-Departmental</span>';
                })
                ->addColumn('leader', function ($row) {
                    return $row->leader ? '<span class="badge bg-light text-dark border"><i class="fas fa-user-tie text-primary me-1"></i>' . $row->leader->name . '</span>' : '<span class="text-muted small">No Leader</span>';
                })
                ->addColumn('members_count', function ($row) {
                    return '<span class="badge bg-primary rounded-pill px-3">' . $row->members->count() . ' members</span>';
                })
                ->addColumn('status', function ($row) {
                    return GetStatusBadge($row->status);
                })
                ->addColumn('action', function ($row) {
                    $btn = '<div class="btn-group">';
                    $btn .= '<a href="' . route('admin.work_management.teams.show', encrypt($row->id)) . '" class="btn btn-sm btn-soft-info" title="Team Hub"><i class="fas fa-eye"></i></a>';
                    $btn .= '<a href="' . route('admin.work_management.teams.edit', encrypt($row->id)) . '" class="btn btn-sm btn-soft-primary ms-1" title="Edit"><i class="fas fa-edit"></i></a>';
                    $btn .= '<form method="POST" action="' . route('admin.work_management.teams.destroy', encrypt($row->id)) . '" class="ms-1 delete-form" onsubmit="return confirm(\'Delete this team?\')">
                                ' . csrf_field() . '
                                ' . method_field('DELETE') . '
                                <button type="submit" class="btn btn-sm btn-soft-danger"><i class="fas fa-trash"></i></button>
                            </form>';
                    $btn .= '</div>';
                    return $btn;
                })
                ->rawColumns(['name_code', 'parent_team', 'department', 'leader', 'members_count', 'status', 'action'])
                ->make(true);
        }

        $departments = HrDepartment::all();
        $parentTeams = Team::whereNull('parent_id')->with('children.children')->get();

        return view('admin.work_management.teams.index', compact('departments', 'parentTeams'));
    }

    public function create()
    {
        $departments = HrDepartment::all();
        $parentTeams = Team::where('status', 'active')->get();
        $staff = Admin::where('status', 'active')->get();

        return view('admin.work_management.teams.create', compact('departments', 'parentTeams', 'staff'));
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'status' => 'required',
        ]);

        if ($validator->fails()) {
            return redirect()->back()->with('error', $validator->errors()->first())->withInput();
        }

        try {
            $data = $request->except(['_token', 'member_ids', 'member_roles']);
            $data['organization_id'] = auth()->user()->organization_id ?? null;

            if (empty($data['code'])) {
                $count = Team::count() + 1;
                $data['code'] = 'TM-' . str_pad($count, 3, '0', STR_PAD_LEFT);
            }

            $team = Team::create($data);

            // Add team leader to team members if assigned
            if (!empty($team->team_leader_id)) {
                TeamMember::firstOrCreate([
                    'team_id' => $team->id,
                    'user_id' => $team->team_leader_id,
                ], [
                    'role' => 'Team Leader',
                    'is_team_leader' => true,
                    'joined_at' => now(),
                    'status' => 'active',
                ]);
            }

            // Sync initial members
            if ($request->has('member_ids')) {
                foreach ($request->member_ids as $userId) {
                    TeamMember::firstOrCreate([
                        'team_id' => $team->id,
                        'user_id' => $userId,
                    ], [
                        'role' => 'Member',
                        'is_team_leader' => ($userId == $team->team_leader_id),
                        'joined_at' => now(),
                        'status' => 'active',
                    ]);
                }
            }

            TaskActivityLog::log(
                'created',
                "Internal Team '{$team->name}' created by " . auth()->user()->name
            );

            return redirect()->route('admin.work_management.teams.show', encrypt($team->id))->with('success', 'Team created successfully');
        } catch (Exception $e) {
            return redirect()->back()->with('error', 'Error: ' . $e->getMessage())->withInput();
        }
    }

    public function show($id)
    {
        $id = decrypt($id);
        $team = Team::with([
            'department', 'parent', 'children.leader', 'children.members', 'leader',
            'members.department', 'projects', 'milestones',
            'tasks.activePrimaryAssignee.user', 'tasks.project'
        ])->findOrFail($id);

        $allStaff = Admin::where('status', 'active')->get();

        return view('admin.work_management.teams.show', compact('team', 'allStaff'));
    }

    public function edit($id)
    {
        $id = decrypt($id);
        $team = Team::with('members')->findOrFail($id);
        $departments = HrDepartment::all();
        $parentTeams = Team::where('id', '!=', $team->id)->where('status', 'active')->get();
        $staff = Admin::where('status', 'active')->get();

        return view('admin.work_management.teams.edit', compact('team', 'departments', 'parentTeams', 'staff'));
    }

    public function update(Request $request, $id)
    {
        $id = decrypt($id);
        $team = Team::findOrFail($id);

        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'status' => 'required',
        ]);

        if ($validator->fails()) {
            return redirect()->back()->with('error', $validator->errors()->first())->withInput();
        }

        try {
            $data = $request->except(['_token', '_method', 'member_ids']);
            $team->update($data);

            if (!empty($team->team_leader_id)) {
                TeamMember::updateOrCreate([
                    'team_id' => $team->id,
                    'user_id' => $team->team_leader_id,
                ], [
                    'role' => 'Team Leader',
                    'is_team_leader' => true,
                    'joined_at' => now(),
                    'status' => 'active',
                ]);
            }

            if ($request->has('member_ids')) {
                foreach ($request->member_ids as $userId) {
                    TeamMember::firstOrCreate([
                        'team_id' => $team->id,
                        'user_id' => $userId,
                    ], [
                        'role' => 'Member',
                        'is_team_leader' => ($userId == $team->team_leader_id),
                        'joined_at' => now(),
                        'status' => 'active',
                    ]);
                }
            }

            return redirect()->route('admin.work_management.teams.show', encrypt($team->id))->with('success', 'Team updated successfully');
        } catch (Exception $e) {
            return redirect()->back()->with('error', 'Error: ' . $e->getMessage())->withInput();
        }
    }

    public function destroy($id)
    {
        $id = decrypt($id);
        $team = Team::findOrFail($id);
        $team->delete();
        return redirect()->route('admin.work_management.teams.index')->with('success', 'Team deleted successfully');
    }

    // Dynamic Member management
    public function addMember(Request $request, $teamId)
    {
        $teamId = decrypt($teamId);
        $team = Team::findOrFail($teamId);

        $request->validate([
            'user_id' => 'required',
        ]);

        TeamMember::firstOrCreate([
            'team_id' => $team->id,
            'user_id' => $request->user_id,
        ], [
            'role' => $request->role ?? 'Member',
            'is_team_leader' => ($request->user_id == $team->team_leader_id),
            'joined_at' => now(),
            'status' => 'active',
        ]);

        return redirect()->back()->with('success', 'Team member added successfully');
    }

    public function removeMember(Request $request, $teamId)
    {
        $teamId = decrypt($teamId);
        TeamMember::where('team_id', $teamId)->where('user_id', $request->user_id)->delete();
        return redirect()->back()->with('success', 'Member removed from team');
    }

    public function getMembersByTeam(Request $request)
    {
        $teamId = $request->team_id;
        $team = Team::with('members')->findOrFail($teamId);

        return response()->json([
            'status' => 1,
            'members' => $team->members,
            'leader' => $team->leader,
        ]);
    }
}

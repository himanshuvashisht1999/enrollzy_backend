@extends('admin.layouts.master')

@section('title', 'Team: ' . $team->name)

@section('content')
<div class="container-fluid">
    <!-- Breadcrumb -->
    <div class="d-flex justify-content-between align-items-center mb-3 flex-wrap gap-2">
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb mb-0">
                <li class="breadcrumb-item"><a href="{{ route('admin.work_management.dashboard.overview') }}">Work Management</a></li>
                <li class="breadcrumb-item"><a href="{{ route('admin.work_management.teams.index') }}">Teams</a></li>
                <li class="breadcrumb-item active" aria-current="page">{{ $team->name }}</li>
            </ol>
        </nav>
        <div class="d-flex gap-2">
            <button class="btn btn-primary btn-sm rounded-pill px-3 shadow-sm" data-bs-toggle="modal" data-bs-target="#addMemberModal">
                <i class="fas fa-user-plus me-1"></i> Add Member
            </button>
            <a href="{{ route('admin.work_management.teams.edit', encrypt($team->id)) }}" class="btn btn-outline-primary btn-sm rounded-pill px-3">
                <i class="fas fa-edit me-1"></i> Edit Team
            </a>
        </div>
    </div>

    <!-- Header Stats Banner -->
    <div class="card shadow-sm border-0 rounded-4 mb-4">
        <div class="card-body p-4">
            <div class="row align-items-center">
                <div class="col-lg-7">
                    <div class="d-flex align-items-center gap-3 mb-2">
                        <span class="badge bg-light text-primary border fs-6 px-3 py-1">{{ $team->code }}</span>
                        <h3 class="fw-bold text-dark m-0">{{ $team->name }}</h3>
                        {!! GetStatusBadge($team->status) !!}
                    </div>
                    <p class="text-muted mb-3">{{ $team->description ?: 'Cross-functional operational team.' }}</p>
                    <div class="d-flex flex-wrap gap-4 text-muted small">
                        <div>
                            <i class="fas fa-user-tie text-primary me-1"></i> Team Leader: 
                            <strong class="text-dark">{{ $team->leader->name ?? 'None Assigned' }}</strong>
                        </div>
                        <div>
                            <i class="fas fa-building text-secondary me-1"></i> Department: 
                            <strong class="text-dark">{{ $team->department->name ?? 'Cross-Departmental' }}</strong>
                        </div>
                        @if($team->parent)
                            <div>
                                <i class="fas fa-sitemap text-info me-1"></i> Parent Team: 
                                <a href="{{ route('admin.work_management.teams.show', encrypt($team->parent->id)) }}" class="text-primary text-decoration-none fw-bold">
                                    {{ $team->parent->name }}
                                </a>
                            </div>
                        @endif
                    </div>
                </div>
                <div class="col-lg-5 mt-3 mt-lg-0">
                    <div class="row g-2 text-center">
                        <div class="col-4">
                            <div class="p-3 bg-light rounded-3">
                                <h4 class="fw-bold text-primary mb-0">{{ $team->members->count() }}</h4>
                                <small class="text-muted">Members</small>
                            </div>
                        </div>
                        <div class="col-4">
                            <div class="p-3 bg-light rounded-3">
                                <h4 class="fw-bold text-info mb-0">{{ $team->children->count() }}</h4>
                                <small class="text-muted">Sub-Teams</small>
                            </div>
                        </div>
                        <div class="col-4">
                            <div class="p-3 bg-light rounded-3">
                                <h4 class="fw-bold text-success mb-0">{{ $team->tasks->count() }}</h4>
                                <small class="text-muted">Tasks</small>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Tabbed Hub -->
    <div class="card shadow-sm border-0 rounded-4">
        <div class="card-header bg-white py-3 border-0">
            <ul class="nav nav-pills" id="teamTab" role="tablist">
                <li class="nav-item">
                    <button class="nav-link active rounded-pill px-4 py-2 me-2" id="members-tab" data-bs-toggle="pill" data-bs-target="#membersTab" type="button">
                        <i class="fas fa-users me-1"></i> Team Roster ({{ $team->members->count() }})
                    </button>
                </li>
                <li class="nav-item">
                    <button class="nav-link rounded-pill px-4 py-2 me-2" id="subteams-tab" data-bs-toggle="pill" data-bs-target="#subteamsTab" type="button">
                        <i class="fas fa-sitemap me-1"></i> Sub-Teams ({{ $team->children->count() }})
                    </button>
                </li>
                <li class="nav-item">
                    <button class="nav-link rounded-pill px-4 py-2 me-2" id="projects-tab" data-bs-toggle="pill" data-bs-target="#projectsTab" type="button">
                        <i class="fas fa-folder me-1"></i> Projects & Milestones
                    </button>
                </li>
                <li class="nav-item">
                    <button class="nav-link rounded-pill px-4 py-2" id="tasks-tab" data-bs-toggle="pill" data-bs-target="#tasksTab" type="button">
                        <i class="fas fa-tasks me-1"></i> Active Tasks ({{ $team->tasks->count() }})
                    </button>
                </li>
            </ul>
        </div>

        <div class="card-body p-4 pt-2">
            <div class="tab-content" id="teamTabContent">
                <!-- Roster Tab -->
                <div class="tab-pane fade show active" id="membersTab" role="tabpanel">
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <h6 class="fw-bold m-0 text-dark">Staff Assigned to this Team</h6>
                        <button class="btn btn-sm btn-outline-primary rounded-pill px-3" data-bs-toggle="modal" data-bs-target="#addMemberModal">
                            <i class="fas fa-plus me-1"></i> Add Member
                        </button>
                    </div>

                    <div class="table-responsive">
                        <table class="table table-hover align-middle">
                            <thead class="bg-light small">
                                <tr>
                                    <th>Staff Member</th>
                                    <th>Email</th>
                                    <th>Department</th>
                                    <th>Team Role</th>
                                    <th>Joined Date</th>
                                    <th class="text-end">Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($team->members as $member)
                                    <tr>
                                        <td>
                                            <div class="d-flex align-items-center gap-2">
                                                <div class="avatar-xs rounded-circle bg-primary text-white d-flex align-items-center justify-content-center fw-bold" style="width: 32px; height: 32px;">
                                                    {{ strtoupper(substr($member->name, 0, 1)) }}
                                                </div>
                                                <div class="fw-bold text-dark">{{ $member->name }}</div>
                                            </div>
                                        </td>
                                        <td class="text-muted small">{{ $member->email }}</td>
                                        <td class="small">{{ $member->department->name ?? 'General' }}</td>
                                        <td>
                                            @if($member->id == $team->team_leader_id)
                                                <span class="badge bg-warning text-dark px-3 py-1"><i class="fas fa-star me-1"></i> Team Leader</span>
                                            @else
                                                <span class="badge bg-light text-secondary border px-3 py-1">{{ $member->pivot->role ?? 'Member' }}</span>
                                            @endif
                                        </td>
                                        <td class="text-muted small">{{ $member->pivot->joined_at ? date('M d, Y', strtotime($member->pivot->joined_at)) : '-' }}</td>
                                        <td class="text-end">
                                            <form action="{{ route('admin.work_management.teams.remove_member', encrypt($team->id)) }}" method="POST" onsubmit="return confirm('Remove {{ $member->name }} from this team?')">
                                                @csrf
                                                <input type="hidden" name="user_id" value="{{ $member->id }}">
                                                <button type="submit" class="btn btn-sm btn-soft-danger rounded-pill px-2">
                                                    <i class="fas fa-user-minus"></i>
                                                </button>
                                            </form>
                                        </td>
                                    </tr>
                                @empty
                                    <tr><td colspan="6" class="text-center text-muted py-4">No members assigned to this team yet.</td></tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>

                <!-- Sub-teams Tab -->
                <div class="tab-pane fade" id="subteamsTab" role="tabpanel">
                    <div class="row g-3">
                        @forelse($team->children as $child)
                            <div class="col-md-6 col-lg-4">
                                <div class="card border rounded-3 p-3 bg-light h-100">
                                    <div class="d-flex justify-content-between align-items-start mb-2">
                                        <div>
                                            <span class="badge bg-white text-primary border me-1">{{ $child->code }}</span>
                                            <a href="{{ route('admin.work_management.teams.show', encrypt($child->id)) }}" class="fw-bold text-dark text-decoration-none">
                                                {{ $child->name }}
                                            </a>
                                        </div>
                                        <span class="badge bg-soft-primary text-primary">{{ $child->members->count() }} staff</span>
                                    </div>
                                    <small class="text-muted d-block">
                                        Leader: <strong>{{ $child->leader->name ?? 'No Leader' }}</strong>
                                    </small>
                                </div>
                            </div>
                        @empty
                            <div class="col-12 text-center text-muted py-4">
                                <p class="mb-0">No sub-teams under {{ $team->name }}.</p>
                            </div>
                        @endforelse
                    </div>
                </div>

                <!-- Projects Tab -->
                <div class="tab-pane fade" id="projectsTab" role="tabpanel">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle">
                            <thead class="bg-light small">
                                <tr>
                                    <th>Project</th>
                                    <th>Progress</th>
                                    <th>Health</th>
                                    <th>Timeline</th>
                                    <th>Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($team->projects as $proj)
                                    <tr>
                                        <td>
                                            <a href="{{ route('admin.work_management.projects.show', encrypt($proj->id)) }}" class="fw-bold text-dark text-decoration-none">
                                                {{ $proj->title }}
                                            </a>
                                        </td>
                                        <td style="width: 180px;">{!! $proj->getProgressProgressBar() !!}</td>
                                        <td>{!! $proj->getHealthBadge() !!}</td>
                                        <td class="small text-muted">{{ $proj->start_date }} - {{ $proj->end_date }}</td>
                                        <td>{!! GetStatusBadge($proj->status) !!}</td>
                                    </tr>
                                @empty
                                    <tr><td colspan="5" class="text-center text-muted py-4">No projects directly assigned to this team.</td></tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>

                <!-- Tasks Tab -->
                <div class="tab-pane fade" id="tasksTab" role="tabpanel">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle">
                            <thead class="bg-light small">
                                <tr>
                                    <th>Task Code & Title</th>
                                    <th>Project</th>
                                    <th>Assignee</th>
                                    <th>Priority</th>
                                    <th>Due Date</th>
                                    <th>Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($team->tasks as $tsk)
                                    <tr>
                                        <td>
                                            <span class="badge bg-light text-secondary border me-1">{{ $tsk->task_code }}</span>
                                            <a href="{{ route('admin.work_management.tasks.show', encrypt($tsk->id)) }}" class="fw-bold text-dark text-decoration-none">
                                                {{ $tsk->title }}
                                            </a>
                                        </td>
                                        <td class="small text-muted">{{ $tsk->project->title ?? '-' }}</td>
                                        <td>
                                            <span class="badge bg-light text-dark border">
                                                <i class="fas fa-user text-primary me-1"></i>
                                                {{ $tsk->activePrimaryAssignee->display_name ?? 'Unassigned' }}
                                            </span>
                                        </td>
                                        <td>{!! $tsk->getPriorityBadge() !!}</td>
                                        <td class="small text-muted">{{ $tsk->due_date ? date('M d, Y', strtotime($tsk->due_date)) : '-' }}</td>
                                        <td>{!! $tsk->getStatusBadge() !!}</td>
                                    </tr>
                                @empty
                                    <tr><td colspan="6" class="text-center text-muted py-4">No tasks currently executing under this team.</td></tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal: Add Member to Team -->
<div class="modal fade" id="addMemberModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 rounded-4 shadow">
            <div class="modal-header border-0 pb-0">
                <h5 class="modal-title fw-bold text-dark"><i class="fas fa-user-plus text-primary me-2"></i> Add Staff Member to {{ $team->name }}</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body p-4">
                <form action="{{ route('admin.work_management.teams.add_member', encrypt($team->id)) }}" method="POST">
                    @csrf
                    <div class="mb-3">
                        <label class="form-label small fw-semibold">Select Staff Member <span class="text-danger">*</span></label>
                        <select name="user_id" class="form-select rounded-3" required>
                            <option value="">Select Staff</option>
                            @foreach($allStaff as $st)
                                @if(!$team->members->contains($st->id))
                                    <option value="{{ $st->id }}">{{ $st->name }} ({{ $st->email }})</option>
                                @endif
                            @endforeach
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label small fw-semibold">Team Role</label>
                        <select name="role" class="form-select rounded-3">
                            <option value="Member">Member</option>
                            <option value="Tech Lead">Tech Lead</option>
                            <option value="Senior Specialist">Senior Specialist</option>
                            <option value="Quality Reviewer">Quality Reviewer</option>
                            <option value="Associate">Associate</option>
                        </select>
                    </div>
                    <button type="submit" class="btn btn-primary w-100 rounded-pill fw-bold">Add Member to Team</button>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection

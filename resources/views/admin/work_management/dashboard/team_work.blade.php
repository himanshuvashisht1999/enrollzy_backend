@extends('admin.layouts.master')

@section('title', 'Team Work Dashboard')

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h4 class="fw-bold mb-1 text-dark"><i class="fas fa-users-cog text-primary me-2"></i> Team Work & Performance</h4>
            <p class="text-muted small mb-0">Team leader overview of workload distribution, pending reviews & team progress</p>
        </div>
    </div>

    <div class="row g-4">
        @forelse($allTeams as $team)
        <div class="col-lg-6">
            <div class="card shadow-sm border-0 rounded-4 h-100">
                <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center">
                    <div>
                        <h6 class="fw-bold text-dark mb-0">
                            <a href="{{ route('admin.work_management.teams.show', encrypt($team->id)) }}" class="text-decoration-none text-dark">
                                {{ $team->name }}
                            </a>
                        </h6>
                        <small class="text-muted"><i class="fas fa-user-tie text-primary me-1"></i> Leader: {{ $team->leader->name ?? 'Unassigned' }}</small>
                    </div>
                    <span class="badge bg-soft-primary text-primary rounded-pill px-3 py-2">
                        {{ $team->members->count() }} Members
                    </span>
                </div>
                <div class="card-body p-3">
                    <h6 class="small fw-bold text-muted text-uppercase mb-3">Member Workload & Active Tasks</h6>
                    <div class="d-flex flex-column gap-2 mb-3">
                        @forelse($team->members as $member)
                        @php
                            $memberTasksCount = \App\Models\Tasks::where(function($q) use ($member) {
                                $q->where('assigned_to', $member->id)
                                  ->orWhereHas('activeAssignees', function($aq) use ($member) {
                                      $aq->where('user_id', $member->id);
                                  });
                            })->whereNotIn('status', ['completed', 'verified', 'closed'])->count();
                        @endphp
                        <div class="p-2 rounded-3 bg-light d-flex justify-content-between align-items-center">
                            <div class="d-flex align-items-center gap-2">
                                <div class="bg-primary text-white rounded-circle d-flex align-items-center justify-content-center fw-bold small" style="width: 32px; height: 32px;">
                                    {{ substr($member->name, 0, 1) }}
                                </div>
                                <div>
                                    <span class="fw-semibold small text-dark d-block">{{ $member->name }}</span>
                                    <small class="text-muted">{{ $member->pivot->role ?? 'Member' }}</small>
                                </div>
                            </div>
                            <span class="badge {{ $memberTasksCount > 5 ? 'bg-danger' : ($memberTasksCount > 0 ? 'bg-info' : 'bg-secondary') }} rounded-pill px-3">
                                {{ $memberTasksCount }} active tasks
                            </span>
                        </div>
                        @empty
                        <div class="text-center py-3 text-muted small">No members assigned to this team.</div>
                        @endforelse
                    </div>

                    <div class="d-flex justify-content-between align-items-center pt-2 border-top">
                        <small class="text-muted">Total Team Tasks: <strong>{{ $team->tasks->count() }}</strong></small>
                        <a href="{{ route('admin.work_management.teams.show', encrypt($team->id)) }}" class="btn btn-sm btn-outline-primary rounded-pill px-3">
                            View Team Board <i class="fas fa-arrow-right ms-1"></i>
                        </a>
                    </div>
                </div>
            </div>
        </div>
        @empty
        <div class="col-12 text-center py-5 text-muted">
            <i class="fas fa-users-slash fs-1 d-block mb-3 opacity-25"></i>
            No teams available. Create teams to see workload distribution!
        </div>
        @endforelse
    </div>
</div>
@endsection

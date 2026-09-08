@extends('admin.layouts.master')

@section('title', 'Work Management - Overview')

@section('content')
<div class="container-fluid">
    <!-- Top Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h4 class="fw-bold mb-1 text-dark"><i class="fas fa-briefcase text-primary me-2"></i> Work Management Overview</h4>
            <p class="text-muted small mb-0">Decoupled enterprise work structure, team coordination & operational tracking</p>
        </div>
        <div class="d-flex gap-2">
            <a href="{{ route('admin.work_management.projects.create') }}" class="btn btn-primary btn-sm rounded-pill px-3 shadow-sm">
                <i class="fas fa-plus me-1"></i> New Project
            </a>
            <a href="{{ route('admin.work_management.tasks.create') }}" class="btn btn-outline-primary btn-sm rounded-pill px-3 shadow-sm">
                <i class="fas fa-tasks me-1"></i> New Task
            </a>
        </div>
    </div>

    <!-- KPI Stats Cards -->
    <div class="row g-3 mb-4">
        <div class="col-xl-3 col-md-6">
            <div class="card shadow-sm border-0 rounded-4 h-100 bg-white">
                <div class="card-body p-3 d-flex align-items-center justify-content-between">
                    <div>
                        <span class="text-muted small fw-semibold text-uppercase d-block mb-1">Total Projects</span>
                        <h3 class="fw-bold mb-0 text-dark">{{ $totalProjects }}</h3>
                        <small class="text-success fw-bold"><i class="fas fa-play-circle me-1"></i>{{ $activeProjects }} Active</small>
                    </div>
                    <div class="bg-soft-primary rounded-4 p-3 text-primary fs-4">
                        <i class="fas fa-folder-open"></i>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6">
            <div class="card shadow-sm border-0 rounded-4 h-100 bg-white">
                <div class="card-body p-3 d-flex align-items-center justify-content-between">
                    <div>
                        <span class="text-muted small fw-semibold text-uppercase d-block mb-1">Total Tasks</span>
                        <h3 class="fw-bold mb-0 text-dark">{{ $totalTasks }}</h3>
                        <small class="text-info fw-bold"><i class="fas fa-spinner me-1"></i>{{ $inProgressTasks }} In Progress</small>
                    </div>
                    <div class="bg-soft-info rounded-4 p-3 text-info fs-4">
                        <i class="fas fa-tasks"></i>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6">
            <div class="card shadow-sm border-0 rounded-4 h-100 bg-white">
                <div class="card-body p-3 d-flex align-items-center justify-content-between">
                    <div>
                        <span class="text-muted small fw-semibold text-uppercase d-block mb-1">Completed Tasks</span>
                        <h3 class="fw-bold mb-0 text-success">{{ $completedTasks }}</h3>
                        <small class="text-muted"><i class="fas fa-check-circle text-success me-1"></i>Verified & Closed</small>
                    </div>
                    <div class="bg-soft-success rounded-4 p-3 text-success fs-4">
                        <i class="fas fa-check-double"></i>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6">
            <div class="card shadow-sm border-0 rounded-4 h-100 bg-white">
                <div class="card-body p-3 d-flex align-items-center justify-content-between">
                    <div>
                        <span class="text-muted small fw-semibold text-uppercase d-block mb-1">Overdue Tasks</span>
                        <h3 class="fw-bold mb-0 text-danger">{{ $overdueTasks }}</h3>
                        <small class="text-danger fw-bold"><i class="fas fa-exclamation-triangle me-1"></i>Needs Immediate Attention</small>
                    </div>
                    <div class="bg-soft-danger rounded-4 p-3 text-danger fs-4">
                        <i class="fas fa-calendar-times"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row g-4 mb-4">
        <!-- Active Projects List -->
        <div class="col-lg-8">
            <div class="card shadow-sm border-0 rounded-4 h-100">
                <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center">
                    <h6 class="m-0 fw-bold text-dark"><i class="fas fa-rocket text-primary me-2"></i> Active Projects</h6>
                    <a href="{{ route('admin.work_management.projects.index') }}" class="btn btn-sm btn-light rounded-pill px-3">View All</a>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0">
                            <thead class="bg-light small">
                                <tr>
                                    <th class="ps-3">Project</th>
                                    <th>Department / Team</th>
                                    <th>Owner</th>
                                    <th>Progress</th>
                                    <th>Health</th>
                                    <th class="text-end pe-3">Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($recentProjects as $p)
                                <tr>
                                    <td class="ps-3">
                                        <a href="{{ route('admin.work_management.projects.show', encrypt($p->id)) }}" class="fw-bold text-dark text-decoration-none">
                                            {{ $p->title }}
                                        </a>
                                        @if($p->project_code)
                                            <span class="badge bg-light text-secondary border ms-1">{{ $p->project_code }}</span>
                                        @endif
                                    </td>
                                    <td>
                                        <small class="text-muted">{{ $p->department->name ?? 'All Depts' }} &bull; {{ $p->team->name ?? 'No Team' }}</small>
                                    </td>
                                    <td>
                                        <small class="fw-semibold">{{ $p->owner->name ?? 'Unassigned' }}</small>
                                    </td>
                                    <td style="width: 140px;">
                                        <div class="d-flex align-items-center gap-2">
                                            <div class="progress flex-grow-1" style="height: 6px;">
                                                <div class="progress-bar bg-primary" role="progressbar" style="width: {{ $p->progress }}%"></div>
                                            </div>
                                            <small class="fw-bold">{{ $p->progress }}%</small>
                                        </div>
                                    </td>
                                    <td>
                                        @php
                                            $h = $p->health_status ?? 'on_track';
                                            $badgeClass = $h == 'on_track' ? 'bg-soft-success text-success border-success' : ($h == 'at_risk' ? 'bg-soft-warning text-warning border-warning' : 'bg-soft-danger text-danger border-danger');
                                        @endphp
                                        <span class="badge {{ $badgeClass }} border text-capitalize small px-2 py-1">
                                            <i class="fas fa-circle me-1" style="font-size: 6px;"></i>{{ str_replace('_', ' ', $h) }}
                                        </span>
                                    </td>
                                    <td class="text-end pe-3">
                                        <a href="{{ route('admin.work_management.projects.show', encrypt($p->id)) }}" class="btn btn-sm btn-soft-primary rounded-circle">
                                            <i class="fas fa-arrow-right"></i>
                                        </a>
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="6" class="text-center py-4 text-muted">No projects found. Create one to get started!</td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <!-- Internal Teams Quick View -->
        <div class="col-lg-4">
            <div class="card shadow-sm border-0 rounded-4 h-100">
                <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center">
                    <h6 class="m-0 fw-bold text-dark"><i class="fas fa-users text-primary me-2"></i> Active Teams</h6>
                    <a href="{{ route('admin.work_management.teams.index') }}" class="btn btn-sm btn-light rounded-pill px-3">All Teams</a>
                </div>
                <div class="card-body p-3">
                    <div class="d-flex flex-column gap-3">
                        @forelse($teams as $team)
                        <div class="p-3 bg-light rounded-4 d-flex justify-content-between align-items-center">
                            <div>
                                <h6 class="fw-bold mb-1">
                                    <a href="{{ route('admin.work_management.teams.show', encrypt($team->id)) }}" class="text-dark text-decoration-none">
                                        {{ $team->name }}
                                    </a>
                                </h6>
                                <small class="text-muted"><i class="fas fa-user-tie me-1 text-primary"></i> Leader: {{ $team->leader->name ?? 'Unassigned' }}</small>
                            </div>
                            <span class="badge bg-white text-primary border rounded-pill px-3 py-2 shadow-sm">
                                {{ $team->members->count() }} members
                            </span>
                        </div>
                        @empty
                        <div class="text-center py-4 text-muted small">No teams created yet.</div>
                        @endforelse
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Recent Activity Audit Log -->
    <div class="card shadow-sm border-0 rounded-4">
        <div class="card-header bg-white py-3">
            <h6 class="m-0 fw-bold text-dark"><i class="fas fa-history text-primary me-2"></i> Recent Work Activity & Audit Trail</h6>
        </div>
        <div class="card-body p-3">
            <div class="timeline-stream">
                @forelse($recentActivities as $act)
                <div class="d-flex gap-3 mb-3 pb-3 border-bottom">
                    <div class="bg-soft-primary rounded-circle d-flex align-items-center justify-content-center flex-shrink-0" style="width: 38px; height: 38px;">
                        <i class="fas fa-check-circle text-primary"></i>
                    </div>
                    <div class="flex-grow-1">
                        <div class="d-flex justify-content-between align-items-center mb-1">
                            <span class="fw-bold text-dark small">{{ $act->performed_by_name ?? 'System' }}</span>
                            <small class="text-muted">{{ $act->created_at->diffForHumans() }}</small>
                        </div>
                        <p class="small text-muted mb-0">{{ $act->description }}</p>
                    </div>
                </div>
                @empty
                <div class="text-center py-3 text-muted small">No activity recorded yet.</div>
                @endforelse
            </div>
        </div>
    </div>
</div>

<style>
    .bg-soft-primary { background-color: rgba(13, 110, 253, 0.1); color: #0d6efd; }
    .bg-soft-success { background-color: rgba(25, 135, 84, 0.1); color: #198754; }
    .bg-soft-warning { background-color: rgba(255, 193, 7, 0.1); color: #856404; }
    .bg-soft-danger { background-color: rgba(220, 53, 69, 0.1); color: #dc3545; }
    .bg-soft-info { background-color: rgba(13, 202, 240, 0.1); color: #0dcaf0; }
</style>
@endsection

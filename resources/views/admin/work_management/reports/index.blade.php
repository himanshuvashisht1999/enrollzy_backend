@extends('admin.layouts.master')

@section('title', 'Work Productivity & Operations Reports')

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-2">
        <div>
            <h4 class="fw-bold text-dark m-0"><i class="fas fa-chart-line text-primary me-2"></i> Work Productivity & Operations Reports</h4>
            <small class="text-muted">High-level visibility into throughput, team workloads, project health, and logged hours</small>
        </div>
    </div>

    <!-- Summary KPI Cards -->
    <div class="row g-3 mb-4">
        <div class="col-md-3">
            <div class="card shadow-sm border-0 rounded-4 p-3 bg-white">
                <div class="d-flex align-items-center justify-content-between">
                    <div>
                        <small class="text-muted text-uppercase fw-bold">Projects Completion</small>
                        <h3 class="fw-bold text-dark mb-0">{{ $completedProjects }} / {{ $totalProjects }}</h3>
                    </div>
                    <div class="rounded-circle bg-soft-primary text-primary d-flex align-items-center justify-content-center" style="width: 48px; height: 48px;">
                        <i class="fas fa-folder-check fa-lg"></i>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-3">
            <div class="card shadow-sm border-0 rounded-4 p-3 bg-white">
                <div class="d-flex align-items-center justify-content-between">
                    <div>
                        <small class="text-muted text-uppercase fw-bold">Tasks Completed</small>
                        <h3 class="fw-bold text-success mb-0">{{ $completedTasks }} / {{ $totalTasks }}</h3>
                    </div>
                    <div class="rounded-circle bg-soft-success text-success d-flex align-items-center justify-content-center" style="width: 48px; height: 48px;">
                        <i class="fas fa-check-circle fa-lg"></i>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-3">
            <div class="card shadow-sm border-0 rounded-4 p-3 bg-white">
                <div class="d-flex align-items-center justify-content-between">
                    <div>
                        <small class="text-muted text-uppercase fw-bold">Overdue Tasks</small>
                        <h3 class="fw-bold text-danger mb-0">{{ $overdueTasks }}</h3>
                    </div>
                    <div class="rounded-circle bg-soft-danger text-danger d-flex align-items-center justify-content-center" style="width: 48px; height: 48px;">
                        <i class="fas fa-exclamation-triangle fa-lg"></i>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-3">
            <div class="card shadow-sm border-0 rounded-4 p-3 bg-white">
                <div class="d-flex align-items-center justify-content-between">
                    <div>
                        <small class="text-muted text-uppercase fw-bold">Hours Logged</small>
                        <h3 class="fw-bold text-info mb-0">{{ $totalHoursLogged }} hrs</h3>
                    </div>
                    <div class="rounded-circle bg-soft-info text-info d-flex align-items-center justify-content-center" style="width: 48px; height: 48px;">
                        <i class="fas fa-clock fa-lg"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row g-4">
        <!-- Team Workloads -->
        <div class="col-lg-6">
            <div class="card shadow-sm border-0 rounded-4 h-100">
                <div class="card-header bg-white py-3">
                    <h6 class="fw-bold text-dark m-0"><i class="fas fa-users text-primary me-2"></i> Team Throughput & Capacity</h6>
                </div>
                <div class="card-body p-3">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0">
                            <thead class="bg-light small">
                                <tr>
                                    <th>Team</th>
                                    <th>Members</th>
                                    <th>Active Tasks</th>
                                    <th>Leader</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($teams as $team)
                                    <tr>
                                        <td class="fw-bold">{{ $team->name }}</td>
                                        <td><span class="badge bg-light text-dark border">{{ $team->members_count }} staff</span></td>
                                        <td><span class="badge bg-soft-primary text-primary">{{ $team->tasks_count }} tasks</span></td>
                                        <td class="small text-muted">{{ $team->leader->name ?? '-' }}</td>
                                    </tr>
                                @empty
                                    <tr><td colspan="4" class="text-center text-muted py-3">No teams found.</td></tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <!-- Project Breakdown -->
        <div class="col-lg-6">
            <div class="card shadow-sm border-0 rounded-4 h-100">
                <div class="card-header bg-white py-3">
                    <h6 class="fw-bold text-dark m-0"><i class="fas fa-folder text-primary me-2"></i> Project Execution Breakdown</h6>
                </div>
                <div class="card-body p-3">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0">
                            <thead class="bg-light small">
                                <tr>
                                    <th>Project</th>
                                    <th>Milestones</th>
                                    <th>Tasks</th>
                                    <th>Health</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($projects as $proj)
                                    <tr>
                                        <td>
                                            <a href="{{ route('admin.work_management.projects.show', encrypt($proj->id)) }}" class="fw-bold text-dark text-decoration-none">
                                                {{ $proj->title }}
                                            </a>
                                        </td>
                                        <td><span class="badge bg-light text-dark border">{{ $proj->milestones_count }}</span></td>
                                        <td><span class="badge bg-soft-info text-info">{{ $proj->tasks_count }}</span></td>
                                        <td>{!! $proj->getHealthBadge() !!}</td>
                                    </tr>
                                @empty
                                    <tr><td colspan="4" class="text-center text-muted py-3">No projects found.</td></tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

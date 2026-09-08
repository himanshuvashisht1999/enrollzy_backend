@extends('admin.layouts.master')

@section('title', 'Project Hub - ' . $project->title)

@push('css')
<style>
    .bg-soft-primary { background-color: rgba(13, 110, 253, 0.1); color: #0d6efd; }
    .bg-soft-success { background-color: rgba(25, 135, 84, 0.1); color: #198754; }
    .bg-soft-warning { background-color: rgba(255, 193, 7, 0.1); color: #856404; }
    .bg-soft-danger { background-color: rgba(220, 53, 69, 0.1); color: #dc3545; }
    .bg-soft-info { background-color: rgba(13, 202, 240, 0.1); color: #0dcaf0; }
    .nav-tabs .nav-link { font-weight: 600; color: #555; border: none; border-bottom: 3px solid transparent; padding: 12px 20px; }
    .nav-tabs .nav-link.active { color: #0d6efd; border-bottom: 3px solid #0d6efd; background: transparent; }
</style>
@endpush

@section('content')
<div class="container-fluid">
    <!-- Top Project Banner Card -->
    <div class="card shadow-sm border-0 rounded-4 mb-4 bg-white">
        <div class="card-body p-4">
            <div class="d-flex flex-wrap justify-content-between align-items-start gap-3 mb-3">
                <div>
                    <div class="d-flex align-items-center gap-2 mb-1">
                        @if($project->project_code)
                            <span class="badge bg-light text-primary border fw-bold">{{ $project->project_code }}</span>
                        @endif
                        <h4 class="fw-bold mb-0 text-dark">{{ $project->title }}</h4>
                        {!! GetStatusBadge($project->status) !!}
                    </div>
                    <p class="text-muted small mb-0">
                        {{ $project->project_category->name ?? 'General Category' }} &bull; 
                        Type: <span class="fw-semibold text-dark">{{ $project->project_type ?? 'Internal' }}</span> &bull; 
                        Priority: {!! (new \App\Models\Tasks(['priority' => $project->priority]))->getPriorityBadge() !!}
                    </p>
                </div>
                <div class="d-flex flex-wrap gap-2">
                    <a href="{{ route('admin.work_management.milestones.create', ['project_id' => encrypt($project->id)]) }}" class="btn btn-outline-primary btn-sm rounded-pill px-3">
                        <i class="fas fa-flag me-1"></i> Add Milestone
                    </a>
                    <a href="{{ route('admin.work_management.tasks.create', ['project_id' => encrypt($project->id)]) }}" class="btn btn-outline-primary btn-sm rounded-pill px-3">
                        <i class="fas fa-plus me-1"></i> Add Task
                    </a>
                    <a href="{{ route('admin.work_management.projects.edit', encrypt($project->id)) }}" class="btn btn-primary btn-sm rounded-pill px-3 shadow-sm">
                        <i class="fas fa-edit me-1"></i> Edit Project
                    </a>
                </div>
            </div>

            <!-- Health & Progress Metrics Bar -->
            <div class="row g-3 pt-3 border-top align-items-center">
                <div class="col-md-3">
                    <small class="text-muted d-block mb-1">Project Progress</small>
                    <div class="d-flex align-items-center gap-2">
                        <div class="progress flex-grow-1" style="height: 8px;">
                            <div class="progress-bar bg-primary" role="progressbar" style="width: {{ $project->progress }}%"></div>
                        </div>
                        <span class="fw-bold small">{{ $project->progress }}%</span>
                    </div>
                </div>
                <div class="col-md-2">
                    <small class="text-muted d-block mb-1">Health Status</small>
                    @php
                        $h = $project->health_status ?? 'on_track';
                        $hClass = $h == 'on_track' ? 'bg-soft-success text-success border-success' : ($h == 'at_risk' ? 'bg-soft-warning text-warning border-warning' : 'bg-soft-danger text-danger border-danger');
                    @endphp
                    <span class="badge {{ $hClass }} border px-3 py-1">
                        <i class="fas fa-circle me-1" style="font-size:6px;"></i> {{ ucfirst(str_replace('_', ' ', $h)) }}
                    </span>
                </div>
                <div class="col-md-3">
                    <small class="text-muted d-block mb-1">Owner & Team</small>
                    <small class="fw-bold text-dark"><i class="fas fa-user-tie text-primary me-1"></i> {{ $project->owner->name ?? 'Unassigned' }}</small>
                    @if($project->team)
                        <span class="badge bg-soft-primary text-primary ms-1">{{ $project->team->name }}</span>
                    @endif
                </div>
                <div class="col-md-2">
                    <small class="text-muted d-block mb-1">Timeline</small>
                    <small class="fw-bold text-dark">
                        {{ $project->start_date ? date('M d, Y', strtotime($project->start_date)) : '-' }} 
                        &rarr; 
                        {{ $project->due_date ? date('M d, Y', strtotime($project->due_date)) : '-' }}
                    </small>
                </div>
                <div class="col-md-2 text-end">
                    <small class="text-muted d-block mb-1">Budget Allocation</small>
                    <span class="fw-bold text-dark">₹{{ number_format($project->budget ?? 0, 2) }}</span>
                </div>
            </div>
        </div>

        <!-- Tab Headers -->
        <div class="card-footer bg-white p-0 border-top">
            <ul class="nav nav-tabs px-4" id="projectHubTabs" role="tablist">
                <li class="nav-item">
                    <a class="nav-link active" id="overview-tab" data-bs-toggle="tab" href="#tab-overview" role="tab"><i class="fas fa-info-circle me-1"></i> Overview</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" id="milestones-tab" data-bs-toggle="tab" href="#tab-milestones" role="tab"><i class="fas fa-flag me-1"></i> Milestones ({{ $project->milestones->count() }})</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" id="tasks-tab" data-bs-toggle="tab" href="#tab-tasks" role="tab"><i class="fas fa-sitemap me-1"></i> Work Breakdown Tree</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" id="roster-tab" data-bs-toggle="tab" href="#tab-roster" role="tab"><i class="fas fa-users me-1"></i> Team & Partners ({{ $project->members->count() + $project->externalMembers->count() }})</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" id="docs-tab" data-bs-toggle="tab" href="#tab-docs" role="tab"><i class="fas fa-folder me-1"></i> Documents ({{ $project->documents->count() }})</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" id="activity-tab" data-bs-toggle="tab" href="#tab-activity" role="tab"><i class="fas fa-history me-1"></i> Audit Trail</a>
                </li>
            </ul>
        </div>
    </div>

    <!-- Tab Content Panels -->
    <div class="tab-content" id="projectHubContent">
        <!-- 1. Overview Tab -->
        <div class="tab-pane fade show active" id="tab-overview" role="tabpanel">
            <div class="row g-4">
                <div class="col-lg-8">
                    <div class="card shadow-sm border-0 rounded-4 mb-4">
                        <div class="card-header bg-white py-3">
                            <h6 class="m-0 fw-bold text-dark">Scope & Objectives</h6>
                        </div>
                        <div class="card-body p-4">
                            <p class="text-dark">{{ $project->description ?: 'No detailed description provided for this project.' }}</p>
                        </div>
                    </div>

                    <!-- Milestone Summary Card -->
                    <div class="card shadow-sm border-0 rounded-4">
                        <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center">
                            <h6 class="m-0 fw-bold text-dark">Milestones Progress</h6>
                            <a href="#tab-milestones" class="btn btn-sm btn-light rounded-pill px-3" onclick="$('#milestones-tab').tab('show')">View Roadmap</a>
                        </div>
                        <div class="card-body p-3">
                            @forelse($project->milestones as $m)
                            <div class="p-3 bg-light rounded-4 mb-3 d-flex justify-content-between align-items-center">
                                <div class="flex-grow-1 me-3">
                                    <div class="d-flex align-items-center gap-2 mb-1">
                                        <span class="badge bg-primary rounded-circle" style="width:20px;height:20px;padding:3px;">{{ $loop->iteration }}</span>
                                        <h6 class="fw-bold mb-0 text-dark">{{ $m->title }}</h6>
                                        {!! GetStatusBadge($m->status) !!}
                                    </div>
                                    <small class="text-muted">{{ $m->tasks->count() }} tasks &bull; Due: {{ $m->due_date ? date('M d, Y', strtotime($m->due_date)) : 'No date' }}</small>
                                </div>
                                <div style="width: 120px;">
                                    <div class="progress" style="height: 6px;">
                                        <div class="progress-bar bg-success" role="progressbar" style="width: {{ $m->progress }}%"></div>
                                    </div>
                                    <small class="text-muted d-block text-end mt-1">{{ $m->progress }}%</small>
                                </div>
                            </div>
                            @empty
                            <div class="text-center py-4 text-muted small">No milestones created yet.</div>
                            @endforelse
                        </div>
                    </div>
                </div>

                <div class="col-lg-4">
                    <!-- Project Details Widget -->
                    <div class="card shadow-sm border-0 rounded-4 mb-4">
                        <div class="card-header bg-white py-3">
                            <h6 class="m-0 fw-bold text-dark">Project Meta</h6>
                        </div>
                        <div class="card-body p-3">
                            <ul class="list-unstyled mb-0 d-flex flex-column gap-3 small">
                                <li>
                                    <span class="text-muted d-block">Department</span>
                                    <span class="fw-bold">{{ $project->department->name ?? 'Cross-Departmental' }}</span>
                                </li>
                                <li>
                                    <span class="text-muted d-block">Lead Team</span>
                                    <span class="fw-bold">{{ $project->team->name ?? 'Unassigned' }}</span>
                                </li>
                                <li>
                                    <span class="text-muted d-block">Client / Account</span>
                                    <span class="fw-bold">{{ $project->client->name ?? 'Internal Project' }}</span>
                                </li>
                                <li>
                                    <span class="text-muted d-block">Created By</span>
                                    <span class="fw-bold">{{ $project->creator->name ?? 'System' }} on {{ $project->created_at->format('M d, Y') }}</span>
                                </li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- 2. Milestones Tab -->
        <div class="tab-pane fade" id="tab-milestones" role="tabpanel">
            <div class="card shadow-sm border-0 rounded-4">
                <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center">
                    <h6 class="m-0 fw-bold text-dark">Project Milestones Roadmap</h6>
                    <a href="{{ route('admin.work_management.milestones.create', ['project_id' => encrypt($project->id)]) }}" class="btn btn-primary btn-sm rounded-pill px-3">
                        <i class="fas fa-plus me-1"></i> Add Milestone
                    </a>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0">
                            <thead class="bg-light small">
                                <tr>
                                    <th class="ps-3">Seq</th>
                                    <th>Milestone</th>
                                    <th>Assigned Team / Owner</th>
                                    <th>Timeline</th>
                                    <th>Progress</th>
                                    <th>Status</th>
                                    <th class="text-end pe-3">Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($project->milestones as $m)
                                <tr>
                                    <td class="ps-3 fw-bold text-muted">{{ $loop->iteration }}</td>
                                    <td>
                                        <span class="fw-bold text-dark">{{ $m->title }}</span>
                                        <small class="text-muted d-block">{{ $m->tasks->count() }} Tasks</small>
                                    </td>
                                    <td>
                                        <small class="text-muted">{{ $m->team->name ?? 'No Team' }} &bull; {{ $m->owner->name ?? 'No Owner' }}</small>
                                    </td>
                                    <td>
                                        <small class="text-muted">{{ $m->start_date ? date('M d', strtotime($m->start_date)) : '' }} &rarr; {{ $m->due_date ? date('M d, Y', strtotime($m->due_date)) : '' }}</small>
                                    </td>
                                    <td style="width: 140px;">
                                        <div class="d-flex align-items-center gap-2">
                                            <div class="progress flex-grow-1" style="height: 6px;">
                                                <div class="progress-bar bg-success" role="progressbar" style="width: {{ $m->progress }}%"></div>
                                            </div>
                                            <small class="fw-bold">{{ $m->progress }}%</small>
                                        </div>
                                    </td>
                                    <td>{!! GetStatusBadge($m->status) !!}</td>
                                    <td class="text-end pe-3">
                                        <a href="{{ route('admin.work_management.milestones.edit', encrypt($m->id)) }}" class="btn btn-sm btn-soft-primary"><i class="fas fa-edit"></i></a>
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="7" class="text-center py-4 text-muted small">No milestones added yet.</td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <!-- 3. Work Breakdown Tree Tab -->
        <div class="tab-pane fade" id="tab-tasks" role="tabpanel">
            <div class="card shadow-sm border-0 rounded-4">
                <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center">
                    <h6 class="m-0 fw-bold text-dark">Work Breakdown Structure (Tasks & Subtasks)</h6>
                    <a href="{{ route('admin.work_management.tasks.create', ['project_id' => encrypt($project->id)]) }}" class="btn btn-primary btn-sm rounded-pill px-3">
                        <i class="fas fa-plus me-1"></i> Add Root Task
                    </a>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0">
                            <thead class="bg-light small">
                                <tr>
                                    <th class="ps-3">Work Item</th>
                                    <th>Milestone</th>
                                    <th>Active Assignee</th>
                                    <th>Priority</th>
                                    <th>Due Date</th>
                                    <th>Status</th>
                                    <th class="text-end pe-3">Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($project->rootTasks as $task)
                                <tr class="table-light">
                                    <td class="ps-3">
                                        <a href="{{ route('admin.work_management.tasks.show', encrypt($task->id)) }}" class="fw-bold text-dark text-decoration-none">
                                            <i class="fas fa-tasks text-primary me-2"></i>{{ $task->title }}
                                        </a>
                                        @if($task->task_code)
                                            <span class="badge bg-white text-secondary border ms-1">{{ $task->task_code }}</span>
                                        @endif
                                    </td>
                                    <td><small class="text-muted">{{ $task->milestone_assigned->title ?? '-' }}</small></td>
                                    <td>
                                        <small class="fw-semibold">{{ $task->activePrimaryAssignee->display_name ?? ($task->assigned_to_user->name ?? 'Unassigned') }}</small>
                                    </td>
                                    <td>{!! $task->getPriorityBadge() !!}</td>
                                    <td><small class="text-muted">{{ $task->due_date ? date('M d, Y', strtotime($task->due_date)) : '-' }}</small></td>
                                    <td>{!! $task->getStatusBadge() !!}</td>
                                    <td class="text-end pe-3">
                                        <a href="{{ route('admin.work_management.tasks.create', ['parent_task_id' => encrypt($task->id)]) }}" class="btn btn-sm btn-outline-primary rounded-pill px-2 py-0" title="Add Subtask">
                                            <i class="fas fa-plus me-1"></i> Subtask
                                        </a>
                                        <a href="{{ route('admin.work_management.tasks.show', encrypt($task->id)) }}" class="btn btn-sm btn-soft-info ms-1"><i class="fas fa-eye"></i></a>
                                    </td>
                                </tr>

                                <!-- Subtasks -->
                                @foreach($task->subtasks as $sub)
                                <tr>
                                    <td class="ps-5">
                                        <a href="{{ route('admin.work_management.tasks.show', encrypt($sub->id)) }}" class="text-dark text-decoration-none small fw-semibold">
                                            <span class="text-muted me-2">↳</span> {{ $sub->title }}
                                        </a>
                                        @if($sub->task_code)
                                            <span class="badge bg-light text-secondary border ms-1" style="font-size:10px;">{{ $sub->task_code }}</span>
                                        @endif
                                    </td>
                                    <td><small class="text-muted">{{ $task->milestone_assigned->title ?? '-' }}</small></td>
                                    <td>
                                        <span class="badge bg-light text-dark border"><i class="fas fa-user me-1 text-primary"></i>{{ $sub->activePrimaryAssignee->display_name ?? 'Unassigned' }}</span>
                                    </td>
                                    <td>{!! $sub->getPriorityBadge() !!}</td>
                                    <td><small class="text-muted">{{ $sub->due_date ? date('M d, Y', strtotime($sub->due_date)) : '-' }}</small></td>
                                    <td>{!! $sub->getStatusBadge() !!}</td>
                                    <td class="text-end pe-3">
                                        <a href="{{ route('admin.work_management.tasks.show', encrypt($sub->id)) }}" class="btn btn-sm btn-soft-primary"><i class="fas fa-arrow-right"></i></a>
                                    </td>
                                </tr>
                                @endforeach
                                @empty
                                <tr>
                                    <td colspan="7" class="text-center py-4 text-muted small">No tasks created under this project yet.</td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <!-- 4. Team & Partner Roster Tab -->
        <div class="tab-pane fade" id="tab-roster" role="tabpanel">
            <div class="row g-4">
                <!-- Internal Members -->
                <div class="col-lg-6">
                    <div class="card shadow-sm border-0 rounded-4 h-100">
                        <div class="card-header bg-white py-3">
                            <h6 class="m-0 fw-bold text-dark"><i class="fas fa-users text-primary me-2"></i> Internal Staff Roster (Multi-Department)</h6>
                        </div>
                        <div class="card-body p-3">
                            <div class="d-flex flex-column gap-2">
                                @forelse($project->members as $member)
                                <div class="p-3 bg-light rounded-4 d-flex justify-content-between align-items-center">
                                    <div class="d-flex align-items-center gap-3">
                                        <div class="bg-primary text-white rounded-circle d-flex align-items-center justify-content-center fw-bold" style="width: 38px; height: 38px;">
                                            {{ substr($member->name, 0, 1) }}
                                        </div>
                                        <div>
                                            <h6 class="fw-bold mb-0 text-dark small">{{ $member->name }}</h6>
                                            <small class="text-muted">{{ $member->department->name ?? 'Staff' }} &bull; {{ $member->pivot->role ?? 'Contributor' }}</small>
                                        </div>
                                    </div>
                                    <span class="badge bg-soft-info text-info rounded-pill px-3">{{ $member->pivot->access_level ?? 'edit' }}</span>
                                </div>
                                @empty
                                <div class="text-center py-4 text-muted small">No internal members assigned yet.</div>
                                @endforelse
                            </div>
                        </div>
                    </div>
                </div>

                <!-- External Partners -->
                <div class="col-lg-6">
                    <div class="card shadow-sm border-0 rounded-4 h-100">
                        <div class="card-header bg-white py-3">
                            <h6 class="m-0 fw-bold text-dark"><i class="fas fa-handshake text-primary me-2"></i> External Agencies & Associate Partners</h6>
                        </div>
                        <div class="card-body p-3">
                            <div class="d-flex flex-column gap-2">
                                @forelse($project->externalMembers as $ext)
                                <div class="p-3 bg-light rounded-4 d-flex justify-content-between align-items-center">
                                    <div class="d-flex align-items-center gap-3">
                                        <div class="bg-success text-white rounded-circle d-flex align-items-center justify-content-center fw-bold" style="width: 38px; height: 38px;">
                                            <i class="fas fa-building"></i>
                                        </div>
                                        <div>
                                            <h6 class="fw-bold mb-0 text-dark small">{{ $ext->organization->name ?? 'External Partner' }}</h6>
                                            <small class="text-muted">{{ $ext->organization->organization_type ?? 'Agency' }} &bull; {{ $ext->role }}</small>
                                        </div>
                                    </div>
                                    <span class="badge bg-soft-success text-success rounded-pill px-3">Partner Access</span>
                                </div>
                                @empty
                                <div class="text-center py-4 text-muted small">No external agencies or partners assigned.</div>
                                @endforelse
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- 5. Documents Tab -->
        <div class="tab-pane fade" id="tab-docs" role="tabpanel">
            <div class="card shadow-sm border-0 rounded-4">
                <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center">
                    <h6 class="m-0 fw-bold text-dark">Project Documents Vault</h6>
                    <button class="btn btn-primary btn-sm rounded-pill px-3" data-bs-toggle="modal" data-bs-target="#uploadDocModal">
                        <i class="fas fa-upload me-1"></i> Upload Document
                    </button>
                </div>
                <div class="card-body p-3">
                    <div class="row g-3">
                        @forelse($project->documents as $doc)
                        <div class="col-md-4">
                            <div class="p-3 bg-light rounded-4 border d-flex justify-content-between align-items-center">
                                <div>
                                    <h6 class="fw-bold mb-1 small text-dark">{{ $doc->title }}</h6>
                                    <small class="text-muted d-block">{{ $doc->document_type }} &bull; v{{ $doc->version }}</small>
                                </div>
                                <a href="{{ asset($doc->file_path) }}" target="_blank" class="btn btn-sm btn-soft-primary rounded-circle">
                                    <i class="fas fa-download"></i>
                                </a>
                            </div>
                        </div>
                        @empty
                        <div class="col-12 text-center py-5 text-muted small">No project documents uploaded yet.</div>
                        @endforelse
                    </div>
                </div>
            </div>
        </div>

        <!-- 6. Activity Log Tab -->
        <div class="tab-pane fade" id="tab-activity" role="tabpanel">
            <div class="card shadow-sm border-0 rounded-4">
                <div class="card-header bg-white py-3">
                    <h6 class="m-0 fw-bold text-dark"><i class="fas fa-history text-primary me-2"></i> Project Audit History</h6>
                </div>
                <div class="card-body p-4">
                    <div class="timeline-stream">
                        @forelse($project->activityLogs as $log)
                        <div class="d-flex gap-3 mb-3 pb-3 border-bottom">
                            <div class="bg-soft-primary rounded-circle d-flex align-items-center justify-content-center flex-shrink-0" style="width: 38px; height: 38px;">
                                <i class="fas fa-check-circle text-primary"></i>
                            </div>
                            <div>
                                <span class="fw-bold text-dark small">{{ $log->performed_by_name ?? 'System' }}</span>
                                <small class="text-muted ms-2">{{ $log->created_at->diffForHumans() }}</small>
                                <p class="small text-muted mb-0 mt-1">{{ $log->description }}</p>
                            </div>
                        </div>
                        @empty
                        <div class="text-center py-4 text-muted small">No audit logs recorded for this project yet.</div>
                        @endforelse
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Upload Doc Modal -->
<div class="modal fade" id="uploadDocModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 rounded-4 shadow-lg">
            <div class="modal-header border-0 pb-0">
                <h6 class="fw-bold">Upload Project Document</h6>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form action="{{ route('admin.work_management.projects.upload_document', encrypt($project->id)) }}" method="POST" enctype="multipart/form-data">
                @csrf
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label small fw-bold">Document Title <span class="text-danger">*</span></label>
                        <input type="text" name="title" class="form-control rounded-3" placeholder="e.g. Project Requirement Document (PRD)" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label small fw-bold">Document Type</label>
                        <select name="document_type" class="form-select rounded-3">
                            <option value="Requirement">Requirement / PRD</option>
                            <option value="Brief">Project Brief</option>
                            <option value="Contract">Contract / Agreement</option>
                            <option value="SOP">Standard Operating Procedure (SOP)</option>
                            <option value="Proposal">Proposal</option>
                            <option value="General" selected>General Document</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label small fw-bold">Select File <span class="text-danger">*</span></label>
                        <input type="file" name="file" class="form-control rounded-3" required>
                    </div>
                </div>
                <div class="modal-footer border-0">
                    <button type="button" class="btn btn-light rounded-pill px-4" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary rounded-pill px-4 shadow-sm">Upload File</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

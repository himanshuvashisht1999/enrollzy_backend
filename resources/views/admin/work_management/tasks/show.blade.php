@extends('admin.layouts.master')

@section('title', 'Task: ' . $task->title)

@push('css')
<style>
    .timeline-widget { position: relative; padding-left: 28px; }
    .timeline-widget::before { content: ''; position: absolute; left: 9px; top: 0; bottom: 0; width: 2px; background: #e2e8f0; }
    .timeline-item { position: relative; margin-bottom: 24px; }
    .timeline-dot { position: absolute; left: -28px; top: 2px; width: 20px; height: 20px; border-radius: 50%; background: #fff; border: 3px solid #3b82f6; }
    .checklist-item.completed span { text-decoration: line-through; color: #94a3b8; }
    .photo-thumb-card { transition: all 0.2s ease-in-out; cursor: pointer; }
    .photo-thumb-card:hover { transform: translateY(-3px); box-shadow: 0 4px 12px rgba(0,0,0,0.12) !important; }
    .img-preview-trigger { cursor: pointer; transition: opacity 0.2s; }
    .img-preview-trigger:hover { opacity: 0.88; }
</style>
@endpush

@section('content')
<div class="container-fluid">
    <!-- Breadcrumb & Top Bar -->
    <div class="d-flex justify-content-between align-items-center mb-3 flex-wrap gap-2">
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb mb-0">
                <li class="breadcrumb-item"><a href="{{ route('admin.work_management.dashboard.overview') }}">Work Management</a></li>
                <li class="breadcrumb-item"><a href="{{ route('admin.work_management.projects.show', encrypt($task->project_id)) }}">{{ $task->project->title ?? 'Project' }}</a></li>
                @if($task->parentTask)
                    <li class="breadcrumb-item"><a href="{{ route('admin.work_management.tasks.show', encrypt($task->parentTask->id)) }}">{{ $task->parentTask->title }}</a></li>
                @endif
                <li class="breadcrumb-item active" aria-current="page">{{ $task->title }}</li>
            </ol>
        </nav>
        <div class="d-flex gap-2">
            <a href="{{ route('admin.work_management.tasks.edit', encrypt($task->id)) }}" class="btn btn-sm btn-outline-primary rounded-pill px-3">
                <i class="fas fa-edit me-1"></i> Edit Task
            </a>
            @if($task->isSubtask())
                <button type="button" class="btn btn-sm btn-warning text-dark rounded-pill px-3 shadow-sm" data-bs-toggle="modal" data-bs-target="#reassignModal">
                    <i class="fas fa-exchange-alt me-1"></i> Reassign Custody
                </button>
            @else
                <button type="button" class="btn btn-sm btn-info text-white rounded-pill px-3 shadow-sm" data-bs-toggle="modal" data-bs-target="#delegateModal">
                    <i class="fas fa-share-square me-1"></i> Delegate Task
                </button>
                <a href="{{ route('admin.work_management.tasks.create', ['parent_task_id' => encrypt($task->id)]) }}" class="btn btn-sm btn-primary rounded-pill px-3 shadow-sm">
                    <i class="fas fa-plus me-1"></i> Add Subtask
                </a>
            @endif
        </div>
    </div>

    @if($task->isSubtask() && !$canPerformAction)
        <div class="alert alert-warning border-0 rounded-4 shadow-sm d-flex align-items-center mb-4">
            <i class="fas fa-lock fa-2x text-warning me-3"></i>
            <div>
                <h6 class="fw-bold mb-1">Single-Assignee Custody Restriction</h6>
                <p class="mb-0 small">
                    Custody is actively held by <strong>{{ $task->activePrimaryAssignee->display_name ?? 'another assignee' }}</strong>. 
                    Actions and status transitions are locked for previous or unassigned team members to ensure single-assignee integrity.
                </p>
            </div>
        </div>
    @endif

    <div class="row g-4">
        <!-- Left 8 Cols: Task Core Info, Subtasks, Checklists, Comments, Activity -->
        <div class="col-lg-8">
            <!-- Header Card -->
            <div class="card shadow-sm border-0 rounded-4 mb-4">
                <div class="card-body p-4">
                    <div class="d-flex justify-content-between align-items-start flex-wrap gap-2 mb-3">
                        <div>
                            <span class="badge bg-light text-secondary border me-2">{{ $task->task_code }}</span>
                            {!! $task->getPriorityBadge() !!}
                            <span id="taskStatusBadgeContainer" class="ms-1">{!! $task->getStatusBadge() !!}</span>
                        </div>
                        <div class="dropdown">
                            <button class="btn btn-sm btn-outline-secondary dropdown-toggle rounded-pill" type="button" data-bs-toggle="dropdown">
                                <i class="fas fa-traffic-light me-1"></i> Transition Status
                            </button>
                            <ul class="dropdown-menu dropdown-menu-end shadow-sm">
                                <li><a class="dropdown-item status-change-btn" href="javascript:void(0)" data-status="backlog">Backlog</a></li>
                                <li><a class="dropdown-item status-change-btn" href="javascript:void(0)" data-status="not_started">Not Started</a></li>
                                <li><a class="dropdown-item status-change-btn" href="javascript:void(0)" data-status="in_progress">In Progress</a></li>
                                <li><a class="dropdown-item status-change-btn" href="javascript:void(0)" data-status="under_review">Under Review</a></li>
                                <li><a class="dropdown-item status-change-btn" href="javascript:void(0)" data-status="completed">Completed</a></li>
                                <li><a class="dropdown-item status-change-btn" href="javascript:void(0)" data-status="verified">Verified</a></li>
                                <li><a class="dropdown-item status-change-btn" href="javascript:void(0)" data-status="closed">Closed</a></li>
                            </ul>
                        </div>
                    </div>

                    <h4 class="fw-bold text-dark mb-2">{{ $task->title }}</h4>
                    <p class="text-muted mb-4">{{ $task->description ?: 'No detailed description provided.' }}</p>

                    <!-- Nav Tabs -->
                    <ul class="nav nav-pills border-bottom pb-2" id="taskTab" role="tablist">
                        @if(!$task->isSubtask())
                        <li class="nav-item">
                            <button class="nav-link active rounded-pill px-3 py-1 me-2" id="subtasks-tab" data-bs-toggle="pill" data-bs-target="#subtasksTab" type="button">
                                <i class="fas fa-sitemap me-1"></i> Subtasks ({{ $task->subtasks->count() }})
                            </button>
                        </li>
                        @endif
                        <li class="nav-item">
                            <button class="nav-link {{ $task->isSubtask() ? 'active' : '' }} rounded-pill px-3 py-1 me-2" id="checklists-tab" data-bs-toggle="pill" data-bs-target="#checklistsTab" type="button">
                                <i class="fas fa-check-square me-1"></i> Checklist (<span id="checklistCount">{{ $task->checklists->where('is_completed', true)->count() }}/{{ $task->checklists->count() }}</span>)
                            </button>
                        </li>
                        <li class="nav-item">
                            <button class="nav-link rounded-pill px-3 py-1 me-2" id="timetracking-tab" data-bs-toggle="pill" data-bs-target="#timetrackingTab" type="button">
                                <i class="fas fa-clock me-1"></i> Time Logs ({{ $task->actual_hours }}h)
                            </button>
                        </li>
                        <li class="nav-item">
                            <button class="nav-link rounded-pill px-3 py-1 me-2" id="attachments-tab" data-bs-toggle="pill" data-bs-target="#attachmentsTab" type="button">
                                <i class="fas fa-paperclip me-1"></i> Files ({{ $task->attachments->count() }})
                            </button>
                        </li>
                        <li class="nav-item">
                            <button class="nav-link rounded-pill px-3 py-1 me-2" id="comments-tab" data-bs-toggle="pill" data-bs-target="#commentsTab" type="button">
                                <i class="fas fa-comments me-1"></i> Comments ({{ $task->comments->count() }})
                            </button>
                        </li>
                        <li class="nav-item">
                            <button class="nav-link rounded-pill px-3 py-1" id="activity-tab" data-bs-toggle="pill" data-bs-target="#activityTab" type="button">
                                <i class="fas fa-history me-1"></i> Audit Trail
                            </button>
                        </li>
                    </ul>

                    <!-- Tab Content -->
                    <div class="tab-content pt-4" id="taskTabContent">
                        <!-- Subtasks Tab -->
                        @if(!$task->isSubtask())
                        <div class="tab-pane fade show active" id="subtasksTab" role="tabpanel">
                            <div class="d-flex justify-content-between align-items-center mb-3">
                                <h6 class="fw-bold m-0">Child Subtasks</h6>
                                <a href="{{ route('admin.work_management.tasks.create', ['parent_task_id' => encrypt($task->id)]) }}" class="btn btn-sm btn-outline-primary rounded-pill">
                                    <i class="fas fa-plus me-1"></i> Add Subtask
                                </a>
                            </div>

                            @if($task->subtasks->isEmpty())
                                <div class="text-center py-4 text-muted bg-light rounded-3">
                                    <i class="fas fa-tasks fa-2x mb-2 text-secondary"></i>
                                    <p class="mb-0">No subtasks created yet. Break down this task into smaller steps.</p>
                                </div>
                            @else
                                <div class="list-group rounded-3 shadow-none border">
                                    @foreach($task->subtasks as $sub)
                                        <div class="list-group-item list-group-item-action d-flex justify-content-between align-items-center p-3">
                                            <div>
                                                <div class="d-flex align-items-center gap-2 mb-1">
                                                    <span class="badge bg-light text-secondary border">{{ $sub->task_code }}</span>
                                                    <a href="{{ route('admin.work_management.tasks.show', encrypt($sub->id)) }}" class="fw-bold text-dark text-decoration-none">
                                                        {{ $sub->title }}
                                                    </a>
                                                </div>
                                                <small class="text-muted">
                                                    Assignee: <strong>{{ $sub->activePrimaryAssignee->display_name ?? 'Unassigned' }}</strong> &bull;
                                                    Due: {{ $sub->due_date ? date('M d, Y', strtotime($sub->due_date)) : 'None' }}
                                                </small>
                                            </div>
                                            <div class="d-flex align-items-center gap-2">
                                                {!! $sub->getPriorityBadge() !!}
                                                {!! $sub->getStatusBadge() !!}
                                                <a href="{{ route('admin.work_management.tasks.show', encrypt($sub->id)) }}" class="btn btn-sm btn-soft-info"><i class="fas fa-arrow-right"></i></a>
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                            @endif
                        </div>
                        @endif

                        <!-- Checklists Tab -->
                        <div class="tab-pane fade {{ $task->isSubtask() ? 'show active' : '' }}" id="checklistsTab" role="tabpanel">
                            <div class="mb-3">
                                <form id="addChecklistForm" class="d-flex gap-2">
                                    <input type="text" id="newChecklistTitle" class="form-control form-control-sm rounded-pill" placeholder="Add a checklist item (e.g. Write unit tests)..." required>
                                    <button type="submit" class="btn btn-sm btn-primary rounded-pill px-3 text-nowrap">
                                        <i class="fas fa-plus me-1"></i> Add
                                    </button>
                                </form>
                            </div>

                            <div id="checklistContainer" class="list-group shadow-none">
                                @forelse($task->checklists as $item)
                                    <div class="list-group-item d-flex justify-content-between align-items-center border rounded-3 mb-2 p-2 checklist-item {{ $item->is_completed ? 'completed bg-light' : '' }}" id="chk-item-{{ $item->id }}">
                                        <div class="form-check d-flex align-items-center gap-2 m-0">
                                            <input class="form-check-input toggle-chk" type="checkbox" data-id="{{ $item->id }}" {{ $item->is_completed ? 'checked' : '' }} id="chk-{{ $item->id }}">
                                            <label class="form-check-label" for="chk-{{ $item->id }}">
                                                <span>{{ $item->title }}</span>
                                            </label>
                                        </div>
                                        <button class="btn btn-sm text-danger delete-chk p-0 border-0" data-id="{{ $item->id }}"><i class="fas fa-times"></i></button>
                                    </div>
                                @empty
                                    <p class="text-muted small text-center py-3" id="noChecklistMsg">No checklist items yet.</p>
                                @endforelse
                            </div>
                        </div>

                        <!-- Time Tracking Tab -->
                        <div class="tab-pane fade" id="timetrackingTab" role="tabpanel">
                            <div class="d-flex justify-content-between align-items-center mb-3">
                                <h6 class="fw-bold m-0">Logged Time Entries</h6>
                                <button class="btn btn-sm btn-outline-primary rounded-pill" data-bs-toggle="modal" data-bs-target="#logTimeModal">
                                    <i class="fas fa-plus me-1"></i> Log Time
                                </button>
                            </div>

                            <div class="table-responsive">
                                <table class="table table-sm table-hover align-middle">
                                    <thead class="bg-light">
                                        <tr>
                                            <th>Staff Member</th>
                                            <th>Duration</th>
                                            <th>Description</th>
                                            <th>Logged At</th>
                                        </tr>
                                    </thead>
                                    <tbody id="timeLogsTbody">
                                        @forelse($task->timeEntries as $entry)
                                            <tr>
                                                <td><span class="fw-semibold">{{ $entry->user->name ?? 'Staff' }}</span></td>
                                                <td><span class="badge bg-soft-primary text-primary">{{ round($entry->duration_minutes / 60, 2) }} hrs</span></td>
                                                <td class="text-muted small">{{ $entry->description ?: '-' }}</td>
                                                <td class="text-muted small">{{ $entry->created_at->format('M d, Y h:i A') }}</td>
                                            </tr>
                                        @empty
                                            <tr><td colspan="4" class="text-center text-muted py-3">No time logs recorded yet.</td></tr>
                                        @endforelse
                                    </tbody>
                                </table>
                            </div>
                        </div>

                        <!-- Attachments Tab -->
                        <div class="tab-pane fade" id="attachmentsTab" role="tabpanel">
                            <div class="card border rounded-3 p-3 bg-light mb-4">
                                <h6 class="fw-bold mb-2 text-dark"><i class="fas fa-cloud-upload-alt text-primary me-2"></i> Upload Files & Photos</h6>
                                <p class="small text-muted mb-2">Upload multiple photos, screenshots, or documents. Uploaded files will be immediately accessible to all assigned members.</p>
                                <form id="uploadAttachmentForm" enctype="multipart/form-data">
                                    <div class="d-flex flex-wrap gap-2 align-items-center">
                                        <div class="flex-grow-1">
                                            <input type="file" id="attachmentFileInput" class="form-control form-control-sm rounded-pill" multiple accept="image/*,.pdf,.doc,.docx,.xls,.xlsx,.ppt,.pptx,.txt,.zip,.rar" required>
                                        </div>
                                        <button type="submit" id="uploadAttachmentBtn" class="btn btn-sm btn-primary rounded-pill px-4 text-nowrap shadow-sm">
                                            <i class="fas fa-upload me-1"></i> Upload Selected Files
                                        </button>
                                    </div>
                                    <div id="attachments_preview_box" class="d-flex flex-wrap gap-2 mt-2"></div>
                                </form>
                            </div>

                            @php
                                $photoAttachments = $task->attachments->filter(function($a) { return $a->isImage(); });
                                $docAttachments = $task->attachments->filter(function($a) { return !$a->isImage(); });
                            @endphp

                            <!-- Photos & Screenshots Gallery -->
                            @if($photoAttachments->isNotEmpty())
                            <div class="mb-4">
                                <h6 class="fw-bold text-dark mb-3"><i class="fas fa-images text-primary me-2"></i> Photos & Screenshots ({{ $photoAttachments->count() }})</h6>
                                <div class="row g-3" id="photosContainer">
                                    @foreach($photoAttachments as $att)
                                    <div class="col-md-3 col-sm-6" id="att-card-{{ $att->id }}">
                                        <div class="card border rounded-3 shadow-sm h-100 photo-thumb-card overflow-hidden">
                                            <div class="position-relative" style="height: 140px; background: #f8fafc;">
                                                <img src="{{ asset($att->file_path) }}" class="w-100 h-100 img-preview-trigger" style="object-fit: cover;" data-img-src="{{ asset($att->file_path) }}" data-img-title="{{ $att->file_name }}" alt="{{ $att->file_name }}">
                                            </div>
                                            <div class="p-2 bg-white">
                                                <div class="fw-bold small text-truncate" title="{{ $att->file_name }}">{{ $att->file_name }}</div>
                                                <div class="d-flex justify-content-between align-items-center mt-1">
                                                    <small class="text-muted" style="font-size: 11px;">{{ $att->formatted_size }} &bull; {{ $att->uploader->name ?? 'Staff' }}</small>
                                                    <div class="btn-group">
                                                        <a href="{{ asset($att->file_path) }}" target="_blank" class="btn btn-sm btn-light p-1 text-primary" title="Open Full Size"><i class="fas fa-external-link-alt"></i></a>
                                                        <button type="button" class="btn btn-sm btn-light p-1 text-danger delete-attachment-btn" data-id="{{ $att->id }}" title="Delete"><i class="fas fa-trash"></i></button>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    @endforeach
                                </div>
                            </div>
                            @endif

                            <!-- Documents & Files List -->
                            <div class="mb-3">
                                <h6 class="fw-bold text-dark mb-2"><i class="fas fa-file-alt text-primary me-2"></i> Documents & Files ({{ $docAttachments->count() }})</h6>
                                <div class="list-group" id="attachmentsContainer">
                                    @forelse($docAttachments as $att)
                                        <div class="list-group-item d-flex justify-content-between align-items-center border rounded-3 mb-2 p-2" id="att-card-{{ $att->id }}">
                                            <div class="d-flex align-items-center gap-2">
                                                <i class="fas fa-file-alt text-primary fa-lg"></i>
                                                <div>
                                                    <div class="fw-bold text-dark small">{{ $att->file_name }}</div>
                                                    <small class="text-muted">{{ $att->formatted_size }} &bull; Uploaded by {{ $att->uploader->name ?? 'Staff' }} &bull; {{ $att->created_at->format('M d, Y') }}</small>
                                                </div>
                                            </div>
                                            <div class="d-flex gap-1">
                                                <a href="{{ asset($att->file_path) }}" target="_blank" class="btn btn-sm btn-soft-primary rounded-pill px-3">
                                                    <i class="fas fa-download me-1"></i> Download
                                                </a>
                                                <button type="button" class="btn btn-sm btn-soft-danger rounded-pill px-2 delete-attachment-btn" data-id="{{ $att->id }}" title="Delete">
                                                    <i class="fas fa-trash"></i>
                                                </button>
                                            </div>
                                        </div>
                                    @empty
                                        @if($photoAttachments->isEmpty())
                                            <p class="text-muted small text-center py-3" id="noAttachmentsMsg">No attachments uploaded yet.</p>
                                        @else
                                            <p class="text-muted small py-2">No standalone documents. Photos are shown above.</p>
                                        @endif
                                    @endforelse
                                </div>
                            </div>
                        </div>

                        <!-- Comments Tab -->
                        <div class="tab-pane fade" id="commentsTab" role="tabpanel">
                            <!-- Comment / Photo Exchange Form -->
                            <form action="{{ route('admin.work_management.comments.store') }}" method="POST" enctype="multipart/form-data" class="card border-0 rounded-4 shadow-sm p-3 mb-4 bg-light">
                                @csrf
                                <input type="hidden" name="task_id" value="{{ $task->id }}">
                                <div class="mb-2">
                                    <label class="form-label small fw-bold text-dark"><i class="fas fa-comment-dots text-primary me-1"></i> Post Update, Feedback or Handover Notes</label>
                                    <textarea name="comment" class="form-control rounded-3" rows="3" placeholder="Write update message, progress notes, or ask questions..." required></textarea>
                                </div>
                                <div class="row g-2 align-items-center">
                                    <div class="col-md-8">
                                        <label class="form-label small fw-semibold mb-1 text-muted"><i class="fas fa-camera text-secondary me-1"></i> Attach Photos / Documents <small>(Multiple files allowed)</small></label>
                                        <input type="file" name="photos[]" id="commentPhotosInput" class="form-control form-control-sm rounded-pill" multiple accept="image/*,.pdf,.doc,.docx,.xls,.xlsx,.txt,.zip">
                                        <div id="commentPhotosPreview" class="d-flex flex-wrap gap-2 mt-2"></div>
                                    </div>
                                    <div class="col-md-4 text-md-end mt-md-3">
                                        <button type="submit" class="btn btn-sm btn-primary rounded-pill px-4 shadow-sm">
                                            <i class="fas fa-paper-plane me-1"></i> Send Update & Photos
                                        </button>
                                    </div>
                                </div>
                            </form>

                            <!-- Comments Stream -->
                            <div class="comments-list">
                                @forelse($task->comments as $c)
                                    <div class="card border rounded-3 p-3 mb-3 bg-white shadow-sm">
                                        <div class="d-flex justify-content-between align-items-center mb-2">
                                            <div class="d-flex align-items-center gap-2">
                                                <div class="avatar-xs rounded-circle bg-primary text-white d-flex align-items-center justify-content-center fw-bold" style="width: 34px; height: 34px;">
                                                    {{ strtoupper(substr($c->user->name ?? 'U', 0, 1)) }}
                                                </div>
                                                <div>
                                                    <div class="fw-bold small text-dark">
                                                        {{ $c->user->name ?? 'User' }}
                                                        @if($task->created_by == $c->user_id)
                                                            <span class="badge bg-soft-info text-info ms-1" style="font-size: 10px;">Assigner / Creator</span>
                                                        @elseif($task->assigned_to == $c->user_id || ($task->activePrimaryAssignee && $task->activePrimaryAssignee->user_id == $c->user_id))
                                                            <span class="badge bg-soft-success text-success ms-1" style="font-size: 10px;">Assignee</span>
                                                        @endif
                                                    </div>
                                                    <small class="text-muted">{{ $c->created_at->diffForHumans() }} &bull; {{ $c->created_at->format('M d, Y h:i A') }}</small>
                                                </div>
                                            </div>
                                        </div>

                                        <p class="mb-2 text-dark small" style="white-space: pre-line;">{{ $c->comment }}</p>

                                        @if(!empty($c->documents_list) && count($c->documents_list) > 0)
                                            <div class="mt-2 pt-2 border-top">
                                                <small class="text-muted fw-semibold d-block mb-1"><i class="fas fa-paperclip me-1"></i> Attached Photos & Files ({{ count($c->documents_list) }}):</small>
                                                <div class="d-flex flex-wrap gap-2">
                                                    @foreach($c->documents_list as $doc)
                                                        @if($doc['is_image'])
                                                            <div class="position-relative border rounded-3 overflow-hidden shadow-none photo-thumb-card" style="width: 110px; height: 90px; cursor: pointer;">
                                                                <img src="{{ $doc['url'] }}" class="w-100 h-100 img-preview-trigger" style="object-fit: cover;" data-img-src="{{ $doc['url'] }}" data-img-title="{{ $doc['name'] }}" alt="{{ $doc['name'] }}">
                                                            </div>
                                                        @else
                                                            <a href="{{ $doc['url'] }}" target="_blank" class="badge bg-light text-dark border p-2 text-decoration-none d-flex align-items-center gap-1">
                                                                <i class="fas fa-file-download text-primary"></i>
                                                                <span class="text-truncate" style="max-width: 140px;">{{ $doc['name'] }}</span>
                                                            </a>
                                                        @endif
                                                    @endforeach
                                                </div>
                                            </div>
                                        @endif
                                    </div>
                                @empty
                                    <p class="text-muted small text-center py-3">No updates or comments yet. Start the conversation!</p>
                                @endforelse
                            </div>
                        </div>

                        <!-- Audit Trail Tab -->
                        <div class="tab-pane fade" id="activityTab" role="tabpanel">
                            <div class="timeline-widget pt-2">
                                @forelse($task->activityLogs as $log)
                                    <div class="timeline-item">
                                        <div class="timeline-dot"></div>
                                        <div>
                                            <span class="small fw-bold text-dark">{{ $log->description }}</span>
                                            <div class="small text-muted">{{ $log->created_at->format('M d, Y h:i A') }} ({{ $log->created_at->diffForHumans() }})</div>
                                        </div>
                                    </div>
                                @empty
                                    <p class="text-muted small">No activity recorded yet.</p>
                                @endforelse
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Right 4 Cols: Active Custody Assignee Card, Metadata, Dependencies -->
        <div class="col-lg-4">
            <!-- Active Assignee Card -->
            <div class="card shadow-sm border-0 rounded-4 mb-4 bg-primary text-white">
                <div class="card-body p-4">
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <h6 class="text-white-50 text-uppercase fw-bold m-0 small">
                            <i class="fas fa-shield-alt me-1"></i> Active Custody
                        </h6>
                        <span class="badge bg-white text-primary rounded-pill px-2">Single-Assignee</span>
                    </div>

                    @if($task->activePrimaryAssignee)
                        <div class="d-flex align-items-center gap-3">
                            <div class="rounded-circle bg-white text-primary d-flex align-items-center justify-content-center fw-bold fs-4" style="width: 50px; height: 50px;">
                                {{ strtoupper(substr($task->activePrimaryAssignee->display_name, 0, 1)) }}
                            </div>
                            <div>
                                <h5 class="fw-bold mb-0 text-white">{{ $task->activePrimaryAssignee->display_name }}</h5>
                                <small class="text-white-50">{{ $task->activePrimaryAssignee->assignee_type_label }}</small>
                            </div>
                        </div>
                        <div class="mt-3 pt-3 border-top border-white-50 small text-white-50">
                            Custody since: <strong>{{ $task->activePrimaryAssignee->assigned_at ? date('M d, Y', strtotime($task->activePrimaryAssignee->assigned_at)) : 'N/A' }}</strong>
                        </div>
                    @else
                        <div class="text-white-50 small">No active assignee currently assigned.</div>
                    @endif
                </div>
            </div>

            <!-- Task Metadata Card -->
            <div class="card shadow-sm border-0 rounded-4 mb-4">
                <div class="card-body p-4">
                    <h6 class="fw-bold text-dark mb-3"><i class="fas fa-info-circle text-primary me-2"></i> Task Details</h6>

                    <ul class="list-group list-group-flush small">
                        <li class="list-group-item d-flex justify-content-between px-0 py-2">
                            <span class="text-muted">Project:</span>
                            <a href="{{ route('admin.work_management.projects.show', encrypt($task->project_id)) }}" class="fw-bold text-primary text-decoration-none">
                                {{ $task->project->title ?? 'N/A' }}
                            </a>
                        </li>
                        <li class="list-group-item d-flex justify-content-between px-0 py-2">
                            <span class="text-muted">Milestone:</span>
                            <span class="fw-bold">{{ $task->milestone_assigned->title ?? 'General' }}</span>
                        </li>
                        <li class="list-group-item d-flex justify-content-between px-0 py-2">
                            <span class="text-muted">Executing Team:</span>
                            <span class="fw-bold">{{ $task->team->name ?? 'Cross-functional' }}</span>
                        </li>
                        <li class="list-group-item d-flex justify-content-between px-0 py-2">
                            <span class="text-muted">Start Date:</span>
                            <span>{{ $task->start_date ? date('M d, Y', strtotime($task->start_date)) : 'N/A' }}</span>
                        </li>
                        <li class="list-group-item d-flex justify-content-between px-0 py-2">
                            <span class="text-muted">Due Date:</span>
                            <span class="{{ ($task->due_date && $task->due_date < date('Y-m-d') && !in_array($task->status, ['completed', 'verified', 'closed'])) ? 'text-danger fw-bold' : '' }}">
                                {{ $task->due_date ? date('M d, Y', strtotime($task->due_date)) : 'No deadline' }}
                            </span>
                        </li>
                        <li class="list-group-item d-flex justify-content-between px-0 py-2">
                            <span class="text-muted">Estimated Hours:</span>
                            <span>{{ $task->estimated_hours ?? 0 }} hrs</span>
                        </li>
                        <li class="list-group-item d-flex justify-content-between px-0 py-2">
                            <span class="text-muted">Logged Hours:</span>
                            <span class="fw-bold text-success">{{ $task->actual_hours ?? 0 }} hrs</span>
                        </li>
                    </ul>
                </div>
            </div>

            <!-- Prerequisites / Dependencies Card -->
            <div class="card shadow-sm border-0 rounded-4">
                <div class="card-body p-4">
                    <h6 class="fw-bold text-dark mb-3"><i class="fas fa-link text-primary me-2"></i> Prerequisite Tasks</h6>
                    @forelse($task->dependencies as $dep)
                        <div class="p-2 border rounded-3 mb-2 small bg-light">
                            <div class="fw-bold text-dark">{{ $dep->prerequisiteTask->title ?? 'Task' }}</div>
                            <div class="d-flex justify-content-between align-items-center mt-1">
                                <span class="text-muted">{{ $dep->dependency_type }}</span>
                                {!! $dep->prerequisiteTask->getStatusBadge() !!}
                            </div>
                        </div>
                    @empty
                        <p class="text-muted small mb-0">No blocking prerequisites. Task is ready for execution.</p>
                    @endforelse
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal: Reassign Subtask Custody -->
<div class="modal fade" id="reassignModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 rounded-4 shadow">
            <div class="modal-header border-0 pb-0">
                <h5 class="modal-title fw-bold text-dark"><i class="fas fa-exchange-alt text-warning me-2"></i> Reassign Subtask Custody</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body p-4">
                <p class="small text-muted mb-3">
                    Reassigning transfers single-assignee custody to the new staff member. The previous assignee's editing rights will be revoked immediately.
                </p>
                <form id="reassignForm">
                    <input type="hidden" name="subtask_id" value="{{ $task->id }}">
                    <div class="mb-3">
                        <label class="form-label small fw-semibold">New Assignee (Staff) <span class="text-danger">*</span></label>
                        <select name="user_id" class="form-select rounded-3" required>
                            <option value="">Select Staff Member</option>
                            @foreach($allStaff as $st)
                                <option value="{{ $st->id }}">{{ $st->name }} ({{ $st->email }})</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label small fw-semibold">New Team (Optional)</label>
                        <select name="team_id" class="form-select rounded-3">
                            <option value="">Maintain Current Team</option>
                            @foreach($allTeams as $tm)
                                <option value="{{ $tm->id }}">{{ $tm->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label small fw-semibold">Reason for Handover</label>
                        <textarea name="reason" class="form-control rounded-3" rows="2" placeholder="e.g. Next development sprint phase..."></textarea>
                    </div>
                    <button type="submit" class="btn btn-warning text-dark w-100 rounded-pill fw-bold">Transfer Custody Now</button>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- Modal: Delegate Task -->
<div class="modal fade" id="delegateModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 rounded-4 shadow">
            <div class="modal-header border-0 pb-0">
                <h5 class="modal-title fw-bold text-dark"><i class="fas fa-share-square text-info me-2"></i> Delegate Task</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body p-4">
                <form id="delegateForm">
                    <input type="hidden" name="task_id" value="{{ $task->id }}">
                    <div class="mb-3">
                        <label class="form-label small fw-semibold">Delegate To Type <span class="text-danger">*</span></label>
                        <select name="to_type" id="delegate_to_type" class="form-select rounded-3" required>
                            <option value="user">Individual Staff Member</option>
                            <option value="team">Internal Team</option>
                        </select>
                    </div>
                    <div class="mb-3" id="delegate_user_group">
                        <label class="form-label small fw-semibold">Select Staff</label>
                        <select name="to_id" id="delegate_user_select" class="form-select rounded-3">
                            @foreach($allStaff as $st)
                                <option value="{{ $st->id }}">{{ $st->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="mb-3" id="delegate_team_group" style="display: none;">
                        <label class="form-label small fw-semibold">Select Team</label>
                        <select id="delegate_team_select" class="form-select rounded-3">
                            @foreach($allTeams as $tm)
                                <option value="{{ $tm->id }}">{{ $tm->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label small fw-semibold">Delegation Remarks</label>
                        <textarea name="remarks" class="form-control rounded-3" rows="2" placeholder="Instructions for the target team/user..."></textarea>
                    </div>
                    <button type="submit" class="btn btn-info text-white w-100 rounded-pill fw-bold">Delegate Task</button>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- Modal: Log Time -->
<div class="modal fade" id="logTimeModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 rounded-4 shadow">
            <div class="modal-header border-0 pb-0">
                <h5 class="modal-title fw-bold text-dark"><i class="fas fa-clock text-primary me-2"></i> Log Work Time</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body p-4">
                <form id="logTimeForm">
                    <input type="hidden" name="task_id" value="{{ $task->id }}">
                    <div class="mb-3">
                        <label class="form-label small fw-semibold">Duration (Minutes) <span class="text-danger">*</span></label>
                        <input type="number" name="duration_minutes" class="form-control rounded-3" placeholder="e.g. 60 (for 1 hour)" required min="1">
                    </div>
                    <div class="mb-3">
                        <label class="form-label small fw-semibold">Work Summary</label>
                        <textarea name="description" class="form-control rounded-3" rows="2" placeholder="Brief note on what was accomplished..."></textarea>
                    </div>
                    <button type="submit" class="btn btn-primary w-100 rounded-pill fw-bold">Save Log Entry</button>
                </form>
            </div>
        </div>
    </div>
</div>
<!-- Modal: Image Preview / Lightbox -->
<div class="modal fade" id="imagePreviewModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content border-0 rounded-4 shadow overflow-hidden bg-dark">
            <div class="modal-header border-0 pb-0 text-white bg-dark">
                <h6 class="modal-title fw-bold text-truncate" id="imagePreviewTitle">Photo Preview</h6>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body p-2 text-center bg-dark">
                <img id="imagePreviewImg" src="" class="img-fluid rounded" style="max-height: 75vh; object-fit: contain;">
            </div>
            <div class="modal-footer border-0 pt-0 bg-dark justify-content-between">
                <small class="text-white-50" id="imagePreviewFooter"></small>
                <a id="imagePreviewDownloadBtn" href="" target="_blank" class="btn btn-sm btn-outline-light rounded-pill px-3">
                    <i class="fas fa-download me-1"></i> Open Original
                </a>
            </div>
        </div>
    </div>
</div>
@endsection

@push('js')
<script>
    const taskId = {{ $task->id }};

    // Image lightbox preview trigger
    $(document).on('click', '.img-preview-trigger', function() {
        var src = $(this).data('img-src') || $(this).attr('src');
        var title = $(this).data('img-title') || 'Photo Preview';
        $('#imagePreviewImg').attr('src', src);
        $('#imagePreviewTitle').text(title);
        $('#imagePreviewDownloadBtn').attr('href', src);
        var myModal = new bootstrap.Modal(document.getElementById('imagePreviewModal'));
        myModal.show();
    });

    // Attachments selection live preview
    $('#attachmentFileInput').on('change', function() {
        var $preview = $('#attachments_preview_box');
        $preview.empty();
        var files = this.files;
        if (files && files.length > 0) {
            Array.from(files).forEach(function(file) {
                var sizeKB = (file.size / 1024).toFixed(1) + ' KB';
                if (file.type.startsWith('image/')) {
                    var reader = new FileReader();
                    reader.onload = function(e) {
                        var imgCard = $('<div class="card p-1 shadow-none border text-center bg-white" style="width: 90px;">' +
                            '<img src="' + e.target.result + '" class="rounded" style="height: 55px; object-fit: cover; width: 100%;">' +
                            '<small class="text-truncate d-block mt-1" style="font-size: 10px;" title="' + file.name + '">' + file.name + '</small>' +
                            '<span class="badge bg-light text-secondary" style="font-size: 9px;">' + sizeKB + '</span>' +
                        '</div>');
                        $preview.append(imgCard);
                    };
                    reader.readAsDataURL(file);
                } else {
                    var docCard = $('<div class="card p-2 shadow-none border text-center d-flex flex-column align-items-center justify-content-center bg-white" style="width: 90px; min-height: 80px;">' +
                        '<i class="fas fa-file-alt fa-2x text-primary mb-1"></i>' +
                        '<small class="text-truncate d-block" style="font-size: 10px; max-width: 80px;" title="' + file.name + '">' + file.name + '</small>' +
                        '<span class="badge bg-light text-secondary" style="font-size: 9px;">' + sizeKB + '</span>' +
                    '</div>');
                    $preview.append(docCard);
                }
            });
        }
    });

    // Comments photos selection live preview
    $('#commentPhotosInput').on('change', function() {
        var $preview = $('#commentPhotosPreview');
        $preview.empty();
        var files = this.files;
        if (files && files.length > 0) {
            Array.from(files).forEach(function(file) {
                var sizeKB = (file.size / 1024).toFixed(1) + ' KB';
                if (file.type.startsWith('image/')) {
                    var reader = new FileReader();
                    reader.onload = function(e) {
                        var imgCard = $('<div class="card p-1 shadow-none border text-center bg-white" style="width: 90px;">' +
                            '<img src="' + e.target.result + '" class="rounded" style="height: 55px; object-fit: cover; width: 100%;">' +
                            '<small class="text-truncate d-block mt-1" style="font-size: 10px;" title="' + file.name + '">' + file.name + '</small>' +
                            '<span class="badge bg-light text-secondary" style="font-size: 9px;">' + sizeKB + '</span>' +
                        '</div>');
                        $preview.append(imgCard);
                    };
                    reader.readAsDataURL(file);
                } else {
                    var docCard = $('<div class="card p-2 shadow-none border text-center d-flex flex-column align-items-center justify-content-center bg-white" style="width: 90px; min-height: 80px;">' +
                        '<i class="fas fa-file-alt fa-2x text-primary mb-1"></i>' +
                        '<small class="text-truncate d-block" style="font-size: 10px; max-width: 80px;" title="' + file.name + '">' + file.name + '</small>' +
                        '<span class="badge bg-light text-secondary" style="font-size: 9px;">' + sizeKB + '</span>' +
                    '</div>');
                    $preview.append(docCard);
                }
            });
        }
    });

    // Status Change
    $('.status-change-btn').on('click', function() {
        var status = $(this).data('status');
        $.post("{{ route('admin.work_management.tasks.change_status') }}", {
            _token: "{{ csrf_token() }}",
            task_id: taskId,
            status: status
        }, function(res) {
            if (res.status === 1) {
                $('#taskStatusBadgeContainer').html(res.badge);
                alert(res.message);
                location.reload();
            } else {
                alert(res.message);
            }
        }).fail(function(xhr) {
            alert(xhr.responseJSON ? xhr.responseJSON.message : 'Error updating status');
        });
    });

    // Checklists AJAX
    $('#addChecklistForm').on('submit', function(e) {
        e.preventDefault();
        var title = $('#newChecklistTitle').val();
        $.post("{{ route('admin.work_management.tasks.add_checklist') }}", {
            _token: "{{ csrf_token() }}",
            task_id: taskId,
            title: title
        }, function(res) {
            if (res.status === 1) {
                $('#noChecklistMsg').remove();
                var item = res.checklist;
                var html = '<div class="list-group-item d-flex justify-content-between align-items-center border rounded-3 mb-2 p-2 checklist-item" id="chk-item-' + item.id + '">' +
                    '<div class="form-check d-flex align-items-center gap-2 m-0">' +
                    '<input class="form-check-input toggle-chk" type="checkbox" data-id="' + item.id + '" id="chk-' + item.id + '">' +
                    '<label class="form-check-label" for="chk-' + item.id + '"><span>' + item.title + '</span></label>' +
                    '</div>' +
                    '<button class="btn btn-sm text-danger delete-chk p-0 border-0" data-id="' + item.id + '"><i class="fas fa-times"></i></button>' +
                    '</div>';
                $('#checklistContainer').append(html);
                $('#newChecklistTitle').val('');
            }
        });
    });

    $(document).on('change', '.toggle-chk', function() {
        var chkId = $(this).data('id');
        var $item = $('#chk-item-' + chkId);
        $.post("{{ route('admin.work_management.tasks.toggle_checklist') }}", {
            _token: "{{ csrf_token() }}",
            checklist_id: chkId
        }, function(res) {
            if (res.status === 1) {
                if (res.is_completed) {
                    $item.addClass('completed bg-light');
                } else {
                    $item.removeClass('completed bg-light');
                }
            }
        });
    });

    $(document).on('click', '.delete-chk', function() {
        var chkId = $(this).data('id');
        $.post("/admin/work-management/tasks/checklists/" + chkId + "/delete", {
            _token: "{{ csrf_token() }}"
        }, function(res) {
            if (res.status === 1) {
                $('#chk-item-' + chkId).fadeOut(function() { $(this).remove(); });
            }
        });
    });

    // Time Tracking AJAX
    $('#logTimeForm').on('submit', function(e) {
        e.preventDefault();
        $.post("{{ route('admin.work_management.tasks.add_time_entry') }}", $(this).serialize() + '&_token={{ csrf_token() }}', function(res) {
            if (res.status === 1) {
                alert('Time logged successfully!');
                location.reload();
            }
        });
    });

    // Attachments Multiple Upload AJAX
    $('#uploadAttachmentForm').on('submit', function(e) {
        e.preventDefault();
        var filesInput = $('#attachmentFileInput')[0];
        if (!filesInput.files || filesInput.files.length === 0) {
            alert('Please select at least one file to upload.');
            return;
        }

        var formData = new FormData();
        formData.append('_token', "{{ csrf_token() }}");
        formData.append('task_id', taskId);

        for (var i = 0; i < filesInput.files.length; i++) {
            formData.append('files[]', filesInput.files[i]);
        }

        var $btn = $('#uploadAttachmentBtn');
        $btn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin me-1"></i> Uploading...');

        $.ajax({
            url: "{{ route('admin.work_management.tasks.add_attachment') }}",
            type: 'POST',
            data: formData,
            processData: false,
            contentType: false,
            success: function(res) {
                $btn.prop('disabled', false).html('<i class="fas fa-upload me-1"></i> Upload Selected Files');
                if (res.status === 1) {
                    alert(res.message || 'Attachments uploaded successfully');
                    location.reload();
                } else {
                    alert(res.message || 'Error uploading files');
                }
            },
            error: function(xhr) {
                $btn.prop('disabled', false).html('<i class="fas fa-upload me-1"></i> Upload Selected Files');
                alert(xhr.responseJSON ? xhr.responseJSON.message : 'Upload failed');
            }
        });
    });

    // Delete Attachment AJAX
    $(document).on('click', '.delete-attachment-btn', function() {
        var attId = $(this).data('id');
        if (!confirm('Are you sure you want to delete this file?')) return;
        $.ajax({
            url: "/admin/work-management/tasks/delete-attachment/" + attId,
            type: 'DELETE',
            data: { _token: "{{ csrf_token() }}" },
            success: function(res) {
                if (res.status === 1) {
                    $('#att-card-' + attId).fadeOut(function() { $(this).remove(); });
                } else {
                    alert(res.message || 'Error deleting file');
                }
            }
        });
    });

    // Reassign Custody AJAX
    $('#reassignForm').on('submit', function(e) {
        e.preventDefault();
        $.post("{{ route('admin.work_management.tasks.reassign') }}", $(this).serialize() + '&_token={{ csrf_token() }}', function(res) {
            if (res.status === 1) {
                alert(res.message);
                location.reload();
            }
        }).fail(function(xhr) {
            alert(xhr.responseJSON ? xhr.responseJSON.message : 'Error reassigning custody');
        });
    });

    // Delegate Form
    $('#delegate_to_type').on('change', function() {
        if ($(this).val() === 'user') {
            $('#delegate_user_group').show();
            $('#delegate_team_group').hide();
            $('#delegate_user_select').attr('name', 'to_id');
            $('#delegate_team_select').removeAttr('name');
        } else {
            $('#delegate_user_group').hide();
            $('#delegate_team_group').show();
            $('#delegate_team_select').attr('name', 'to_id');
            $('#delegate_user_select').removeAttr('name');
        }
    });

    $('#delegateForm').on('submit', function(e) {
        e.preventDefault();
        $.post("{{ route('admin.work_management.tasks.delegate') }}", $(this).serialize() + '&_token={{ csrf_token() }}', function(res) {
            if (res.status === 1) {
                alert(res.message);
                location.reload();
            }
        });
    });
</script>
@endpush

@extends('admin.layouts.master')

@section('title', isset($parentTask) ? 'Create Subtask' : 'Create New Task')

@section('content')
<div class="container-fluid">
    <div class="row justify-content-center">
        <div class="col-lg-10">
            <div class="card shadow-sm border-0 rounded-4">
                <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center">
                    <div>
                        <h5 class="m-0 fw-bold text-dark">
                            <i class="fas fa-plus-circle text-primary me-2"></i>
                            {{ isset($parentTask) ? 'Create Subtask' : 'Create New Task' }}
                        </h5>
                        <small class="text-muted">
                            {{ isset($parentTask) ? 'Add an actionable subtask under parent custody' : 'Create a new task with assignment and scheduling' }}
                        </small>
                    </div>
                    @if(isset($parentTask))
                        <a href="{{ route('admin.work_management.tasks.show', encrypt($parentTask->id)) }}" class="btn btn-sm btn-outline-secondary rounded-pill px-3">
                            <i class="fas fa-arrow-left me-1"></i> Back to Parent Task
                        </a>
                    @else
                        <a href="{{ route('admin.work_management.tasks.index') }}" class="btn btn-sm btn-outline-secondary rounded-pill px-3">
                            <i class="fas fa-arrow-left me-1"></i> Back to Tasks
                        </a>
                    @endif
                </div>

                <div class="card-body p-4">
                    @if(isset($parentTask))
                        <div class="alert alert-info border-0 rounded-3 d-flex align-items-center mb-4">
                            <i class="fas fa-level-down-alt fa-2x me-3 text-info"></i>
                            <div>
                                <h6 class="fw-bold mb-1">Parent Task: <span class="badge bg-light text-dark border">{{ $parentTask->task_code }}</span> {{ $parentTask->title }}</h6>
                                <small class="text-muted">Project: <strong>{{ $parentTask->project->title ?? 'N/A' }}</strong> &bull; Milestone: <strong>{{ $parentTask->milestone_assigned->title ?? 'General' }}</strong></small>
                            </div>
                        </div>
                    @endif

                    <form action="{{ route('admin.work_management.tasks.store') }}" method="POST" id="taskForm" enctype="multipart/form-data">
                        @csrf
                        @if(isset($parentTask))
                            <input type="hidden" name="parent_task_id" value="{{ $parentTask->id }}">
                            <input type="hidden" name="project_id" value="{{ $parentTask->project_id }}">
                            <input type="hidden" name="milestone" value="{{ $parentTask->milestone }}">
                        @endif

                        <div class="row g-3">
                            <div class="col-md-8">
                                <label class="form-label fw-semibold">Task Title <span class="text-danger">*</span></label>
                                <input type="text" name="title" class="form-control rounded-3" placeholder="e.g. Implement user authentication API" required value="{{ old('title') }}">
                            </div>

                            <div class="col-md-4">
                                <label class="form-label fw-semibold">Task Code <small class="text-muted">(Optional, Auto-generated)</small></label>
                                <input type="text" name="task_code" class="form-control rounded-3" placeholder="e.g. TSK-2609-001" value="{{ old('task_code') }}">
                            </div>

                            @if(!isset($parentTask))
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">Project <span class="text-danger">*</span></label>
                                <select name="project_id" id="project_select" class="form-select rounded-3" required>
                                    <option value="">Select Project</option>
                                    @foreach($projects as $proj)
                                        <option value="{{ $proj->id }}" {{ (old('project_id', $selectedProjectId) == $proj->id) ? 'selected' : '' }}>
                                            {{ $proj->title }} ({{ $proj->code ?? 'PRJ' }})
                                        </option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="col-md-6">
                                <label class="form-label fw-semibold">Milestone</label>
                                <select name="milestone" id="milestone_select" class="form-select rounded-3">
                                    <option value="">Select Milestone (Optional)</option>
                                    @if(isset($milestones))
                                        @foreach($milestones as $m)
                                            <option value="{{ $m->id }}" {{ old('milestone') == $m->id ? 'selected' : '' }}>
                                                {{ $m->title }}
                                            </option>
                                        @endforeach
                                    @endif
                                </select>
                            </div>
                            @endif

                            <div class="col-md-4">
                                <label class="form-label fw-semibold">Executing Team</label>
                                <select name="team_id" id="team_select" class="form-select rounded-3">
                                    <option value="">Select Team (Optional)</option>
                                    @foreach($teams as $team)
                                        <option value="{{ $team->id }}" {{ old('team_id') == $team->id ? 'selected' : '' }}>{{ $team->name }}</option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="col-md-4">
                                <label class="form-label fw-semibold">Priority <span class="text-danger">*</span></label>
                                <select name="priority" class="form-select rounded-3" required>
                                    <option value="urgent" {{ old('priority') == 'urgent' ? 'selected' : '' }}>🔴 Urgent</option>
                                    <option value="high" {{ old('priority') == 'high' ? 'selected' : '' }}>🟠 High</option>
                                    <option value="medium" {{ (old('priority', 'medium') == 'medium') ? 'selected' : '' }}>🔵 Medium</option>
                                    <option value="low" {{ old('priority') == 'low' ? 'selected' : '' }}>⚪ Low</option>
                                </select>
                            </div>

                            <div class="col-md-4">
                                <label class="form-label fw-semibold">Initial Status <span class="text-danger">*</span></label>
                                <select name="status" class="form-select rounded-3" required>
                                    <option value="not_started" {{ old('status') == 'not_started' ? 'selected' : '' }}>Not Started</option>
                                    <option value="backlog" {{ old('status') == 'backlog' ? 'selected' : '' }}>Backlog</option>
                                    <option value="in_progress" {{ old('status') == 'in_progress' ? 'selected' : '' }}>In Progress</option>
                                    <option value="under_review" {{ old('status') == 'under_review' ? 'selected' : '' }}>Under Review</option>
                                    <option value="completed" {{ old('status') == 'completed' ? 'selected' : '' }}>Completed</option>
                                </select>
                            </div>

                            <!-- Universal Assignment Section -->
                            <div class="col-12 mt-4">
                                <div class="card border rounded-3 p-3 bg-light">
                                    <h6 class="fw-bold mb-2 text-dark"><i class="fas fa-user-check text-primary me-2"></i> Universal Assignment & Single-Assignee Custody</h6>
                                    <p class="small text-muted mb-3">Assign to an internal staff member, team, or external partner contact/team. For subtasks, this sets active custody.</p>
                                    
                                    <div class="row g-3">
                                        <div class="col-md-5">
                                            <label class="form-label small fw-semibold">Assignee Type</label>
                                            <select name="assignee_type" id="assignee_type_select" class="form-select form-select-sm rounded-3">
                                                <option value="">-- Do Not Assign Yet --</option>
                                                <option value="internal_user" {{ old('assignee_type') == 'internal_user' ? 'selected' : '' }}>Internal Staff Member</option>
                                                <option value="internal_team" {{ old('assignee_type') == 'internal_team' ? 'selected' : '' }}>Internal Team</option>
                                                <option value="external_contact" {{ old('assignee_type') == 'external_contact' ? 'selected' : '' }}>External Partner Contact</option>
                                                <option value="external_team" {{ old('assignee_type') == 'external_team' ? 'selected' : '' }}>External Partner Team</option>
                                            </select>
                                        </div>

                                        <div class="col-md-7" id="assignee_id_container" style="display: none;">
                                            <label class="form-label small fw-semibold">Select Assignee</label>
                                            <select name="assignee_id" id="assignee_id_select" class="form-select form-select-sm rounded-3">
                                                <!-- Dynamic options populated by JS -->
                                            </select>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Timeline & Hours -->
                            <div class="col-md-4 mt-3">
                                <label class="form-label fw-semibold">Estimated Hours</label>
                                <input type="number" step="0.5" name="estimated_hours" class="form-control rounded-3" placeholder="e.g. 8.0" value="{{ old('estimated_hours') }}">
                            </div>

                            <div class="col-md-4 mt-3">
                                <label class="form-label fw-semibold">Start Date</label>
                                <input type="date" name="start_date" class="form-control rounded-3" value="{{ old('start_date', date('Y-m-d')) }}">
                            </div>

                            <div class="col-md-4 mt-3">
                                <label class="form-label fw-semibold">Due Date</label>
                                <input type="date" name="due_date" class="form-control rounded-3" value="{{ old('due_date') }}">
                            </div>

                            <div class="col-12 mt-3">
                                <label class="form-label fw-semibold">Description & Acceptance Criteria</label>
                                <textarea name="description" class="form-control rounded-3" rows="4" placeholder="Detailed scope, technical requirements or acceptance criteria...">{{ old('description') }}</textarea>
                            </div>

                            <!-- Attachments & Photos Section -->
                            <div class="col-12 mt-3">
                                <label class="form-label fw-semibold d-flex justify-content-between align-items-center">
                                    <span><i class="fas fa-paperclip text-primary me-1"></i> Attachments & Photos <small class="text-muted">(Multiple files allowed)</small></span>
                                    <small class="text-muted">Images, PDF, Documents, ZIP (Max 20MB per file)</small>
                                </label>
                                <div class="border rounded-3 p-3 bg-light">
                                    <input type="file" name="attachments[]" id="create_attachments_input" class="form-control rounded-3" multiple accept="image/*,.pdf,.doc,.docx,.xls,.xlsx,.ppt,.pptx,.txt,.zip,.rar">
                                    <div id="create_attachments_preview" class="d-flex flex-wrap gap-2 mt-2"></div>
                                    <small class="text-muted mt-1 d-block"><i class="fas fa-info-circle me-1"></i> You can select multiple images/documents at once. When assigned, the assignee will also be able to view and download all files.</small>
                                </div>
                            </div>
                        </div>

                        <div class="d-flex justify-content-end gap-2 mt-4 pt-3 border-top">
                            @if(isset($parentTask))
                                <a href="{{ route('admin.work_management.tasks.show', encrypt($parentTask->id)) }}" class="btn btn-light rounded-pill px-4">Cancel</a>
                            @else
                                <a href="{{ route('admin.work_management.tasks.index') }}" class="btn btn-light rounded-pill px-4">Cancel</a>
                            @endif
                            <button type="submit" class="btn btn-primary rounded-pill px-5 shadow-sm">
                                <i class="fas fa-save me-1"></i> {{ isset($parentTask) ? 'Create Subtask' : 'Create Task' }}
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('js')
<script>
    const staffList = @json($staff);
    const teamsList = @json($teams);
    const externalContactsList = @json($externalContacts);
    const externalTeamsList = @json($externalTeams);

    function loadProjectData(projectId, selectedMilestoneId) {
        var $milestone = $('#milestone_select');
        if (!projectId) {
            $milestone.empty().append('<option value="">Select Milestone (Optional)</option>').trigger('change');
            return;
        }
        $.get("{{ route('admin.work_management.tasks.ajax_project_data') }}", { project_id: projectId }, function(res) {
            if (res.status === 1) {
                var currentVal = selectedMilestoneId || $milestone.val() || "{{ old('milestone') }}";
                $milestone.empty().append('<option value="">Select Milestone (Optional)</option>');
                if (res.milestones && res.milestones.length > 0) {
                    res.milestones.forEach(function(m) {
                        var isSelected = (currentVal && currentVal == m.id) ? ' selected' : '';
                        $milestone.append('<option value="' + m.id + '"' + isSelected + '>' + m.title + '</option>');
                    });
                }
                $milestone.trigger('change');
            }
        });
    }

    $(document).ready(function() {
        $('#project_select').on('change', function() {
            loadProjectData($(this).val());
        });

        if ($('#project_select').val()) {
            loadProjectData($('#project_select').val(), "{{ old('milestone') }}");
        }

        $('#assignee_type_select').on('change', function() {
            var type = $(this).val();
            var $container = $('#assignee_id_container');
            var $select = $('#assignee_id_select');
            $select.empty();

            if (!type) {
                $container.hide();
                return;
            }

            $container.show();
            if (type === 'internal_user') {
                $select.append('<option value="">-- Select Staff Member --</option>');
                staffList.forEach(function(u) {
                    $select.append('<option value="' + u.id + '">' + u.name + ' (' + (u.email || '') + ')</option>');
                });
            } else if (type === 'internal_team') {
                $select.append('<option value="">-- Select Internal Team --</option>');
                teamsList.forEach(function(t) {
                    $select.append('<option value="' + t.id + '">' + t.name + '</option>');
                });
            } else if (type === 'external_contact') {
                $select.append('<option value="">-- Select Partner Contact --</option>');
                externalContactsList.forEach(function(c) {
                    $select.append('<option value="' + c.id + '">' + c.name + ' (' + (c.designation || 'Partner') + ')</option>');
                });
            } else if (type === 'external_team') {
                $select.append('<option value="">-- Select External Team --</option>');
                externalTeamsList.forEach(function(et) {
                    $select.append('<option value="' + et.id + '">' + et.name + '</option>');
                });
            }
        });

        // Live preview for attachments
        $('#create_attachments_input').on('change', function() {
            var $preview = $('#create_attachments_preview');
            $preview.empty();
            var files = this.files;
            if (files && files.length > 0) {
                Array.from(files).forEach(function(file) {
                    var sizeKB = (file.size / 1024).toFixed(1) + ' KB';
                    if (file.type.startsWith('image/')) {
                        var reader = new FileReader();
                        reader.onload = function(e) {
                            var imgCard = $('<div class="card p-1 shadow-none border text-center" style="width: 100px;">' +
                                '<img src="' + e.target.result + '" class="rounded" style="height: 60px; object-fit: cover; width: 100%;">' +
                                '<small class="text-truncate d-block mt-1" style="font-size: 10px;" title="' + file.name + '">' + file.name + '</small>' +
                                '<span class="badge bg-light text-secondary" style="font-size: 9px;">' + sizeKB + '</span>' +
                            '</div>');
                            $preview.append(imgCard);
                        };
                        reader.readAsDataURL(file);
                    } else {
                        var docCard = $('<div class="card p-2 shadow-none border text-center d-flex flex-column align-items-center justify-content-center" style="width: 100px; min-height: 85px;">' +
                            '<i class="fas fa-file-alt fa-2x text-primary mb-1"></i>' +
                            '<small class="text-truncate d-block" style="font-size: 10px; max-width: 90px;" title="' + file.name + '">' + file.name + '</small>' +
                            '<span class="badge bg-light text-secondary" style="font-size: 9px;">' + sizeKB + '</span>' +
                        '</div>');
                        $preview.append(docCard);
                    }
                });
            }
        });
    });
</script>
@endpush

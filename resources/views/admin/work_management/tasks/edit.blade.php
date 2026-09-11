@extends('admin.layouts.master')

@section('title', 'Edit Task: ' . $task->title)

@section('content')
<div class="container-fluid">
    <div class="row justify-content-center">
        <div class="col-lg-10">
            <div class="card shadow-sm border-0 rounded-4">
                <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center">
                    <div>
                        <h5 class="m-0 fw-bold text-dark">
                            <i class="fas fa-edit text-primary me-2"></i> Edit Task: {{ $task->title }}
                        </h5>
                        <small class="text-muted">Modify task parameters, status, dates, and ownership</small>
                    </div>
                    <a href="{{ route('admin.work_management.tasks.show', encrypt($task->id)) }}" class="btn btn-sm btn-outline-secondary rounded-pill px-3">
                        <i class="fas fa-arrow-left me-1"></i> Back to Task Detail
                    </a>
                </div>

                <div class="card-body p-4">
                    <form action="{{ route('admin.work_management.tasks.update', encrypt($task->id)) }}" method="POST" enctype="multipart/form-data">
                        @csrf
                        @method('PUT')

                        <div class="row g-3">
                            <div class="col-md-8">
                                <label class="form-label fw-semibold">Task Title <span class="text-danger">*</span></label>
                                <input type="text" name="title" class="form-control rounded-3" value="{{ old('title', $task->title) }}" required>
                            </div>

                            <div class="col-md-4">
                                <label class="form-label fw-semibold">Task Code</label>
                                <input type="text" name="task_code" class="form-control rounded-3" value="{{ old('task_code', $task->task_code) }}" readonly>
                            </div>

                            <div class="col-md-6">
                                <label class="form-label fw-semibold">Project <span class="text-danger">*</span></label>
                                <select name="project_id" id="project_select" class="form-select rounded-3" required>
                                    @foreach($projects as $proj)
                                        <option value="{{ $proj->id }}" {{ old('project_id', $task->project_id) == $proj->id ? 'selected' : '' }}>
                                            {{ $proj->title }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="col-md-6">
                                <label class="form-label fw-semibold">Milestone</label>
                                <select name="milestone" id="milestone_select" class="form-select rounded-3">
                                    <option value="">No Milestone</option>
                                    @foreach($milestones as $m)
                                        <option value="{{ $m->id }}" {{ old('milestone', $task->milestone) == $m->id ? 'selected' : '' }}>
                                            {{ $m->title }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="col-md-4">
                                <label class="form-label fw-semibold">Executing Team</label>
                                <select name="team_id" class="form-select rounded-3">
                                    <option value="">No Team Assigned</option>
                                    @foreach($teams as $team)
                                        <option value="{{ $team->id }}" {{ old('team_id', $task->team_id) == $team->id ? 'selected' : '' }}>{{ $team->name }}</option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="col-md-4">
                                <label class="form-label fw-semibold">Priority <span class="text-danger">*</span></label>
                                <select name="priority" class="form-select rounded-3" required>
                                    <option value="urgent" {{ old('priority', $task->priority) == 'urgent' ? 'selected' : '' }}>🔴 Urgent</option>
                                    <option value="high" {{ old('priority', $task->priority) == 'high' ? 'selected' : '' }}>🟠 High</option>
                                    <option value="medium" {{ old('priority', $task->priority) == 'medium' ? 'selected' : '' }}>🔵 Medium</option>
                                    <option value="low" {{ old('priority', $task->priority) == 'low' ? 'selected' : '' }}>⚪ Low</option>
                                </select>
                            </div>

                            <div class="col-md-4">
                                <label class="form-label fw-semibold">Status <span class="text-danger">*</span></label>
                                <select name="status" class="form-select rounded-3" required>
                                    <option value="backlog" {{ old('status', $task->status) == 'backlog' ? 'selected' : '' }}>Backlog</option>
                                    <option value="not_started" {{ old('status', $task->status) == 'not_started' ? 'selected' : '' }}>Not Started</option>
                                    <option value="in_progress" {{ old('status', $task->status) == 'in_progress' ? 'selected' : '' }}>In Progress</option>
                                    <option value="under_review" {{ old('status', $task->status) == 'under_review' ? 'selected' : '' }}>Under Review</option>
                                    <option value="completed" {{ old('status', $task->status) == 'completed' ? 'selected' : '' }}>Completed</option>
                                    <option value="verified" {{ old('status', $task->status) == 'verified' ? 'selected' : '' }}>Verified</option>
                                    <option value="closed" {{ old('status', $task->status) == 'closed' ? 'selected' : '' }}>Closed</option>
                                </select>
                            </div>

                            <div class="col-md-3">
                                <label class="form-label fw-semibold">Estimated Hours</label>
                                <input type="number" step="0.5" name="estimated_hours" class="form-control rounded-3" value="{{ old('estimated_hours', $task->estimated_hours) }}">
                            </div>

                            <div class="col-md-3">
                                <label class="form-label fw-semibold">Actual Hours</label>
                                <input type="number" step="0.5" name="actual_hours" class="form-control rounded-3" value="{{ old('actual_hours', $task->actual_hours) }}">
                            </div>

                            <div class="col-md-3">
                                <label class="form-label fw-semibold">Start Date</label>
                                <input type="date" name="start_date" class="form-control rounded-3" value="{{ old('start_date', $task->start_date ? date('Y-m-d', strtotime($task->start_date)) : '') }}">
                            </div>

                            <div class="col-md-3">
                                <label class="form-label fw-semibold">Due Date</label>
                                <input type="date" name="due_date" class="form-control rounded-3" value="{{ old('due_date', $task->due_date ? date('Y-m-d', strtotime($task->due_date)) : '') }}">
                            </div>

                            <div class="col-12">
                                <label class="form-label fw-semibold">Description</label>
                                <textarea name="description" class="form-control rounded-3" rows="4">{{ old('description', $task->description) }}</textarea>
                            </div>

                            <!-- Existing Attachments -->
                            @if($task->attachments && $task->attachments->count() > 0)
                            <div class="col-12 mt-3">
                                <label class="form-label fw-semibold"><i class="fas fa-paperclip text-primary me-1"></i> Existing Attachments & Photos ({{ $task->attachments->count() }})</label>
                                <div class="row g-2">
                                    @foreach($task->attachments as $att)
                                    <div class="col-md-3 col-sm-6" id="edit_att_{{ $att->id }}">
                                        <div class="card p-2 border rounded-3 h-100 shadow-none bg-light position-relative">
                                            @if($att->isImage())
                                                <a href="{{ asset($att->file_path) }}" target="_blank">
                                                    <img src="{{ asset($att->file_path) }}" class="rounded mb-2 w-100" style="height: 100px; object-fit: cover;">
                                                </a>
                                            @else
                                                <div class="text-center py-3">
                                                    <i class="fas fa-file-alt fa-3x text-primary mb-1"></i>
                                                </div>
                                            @endif
                                            <div class="d-flex justify-content-between align-items-center">
                                                <div class="text-truncate me-1" style="max-width: 140px;">
                                                    <small class="fw-bold d-block text-truncate" title="{{ $att->file_name }}">{{ $att->file_name }}</small>
                                                    <small class="text-muted" style="font-size: 11px;">{{ $att->formatted_size }}</small>
                                                </div>
                                                <div class="btn-group">
                                                    <a href="{{ asset($att->file_path) }}" target="_blank" class="btn btn-sm btn-outline-primary py-0 px-1" title="Download"><i class="fas fa-download"></i></a>
                                                    <button type="button" class="btn btn-sm btn-outline-danger py-0 px-1 delete-edit-attachment" data-id="{{ $att->id }}" title="Delete"><i class="fas fa-trash"></i></button>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    @endforeach
                                </div>
                            </div>
                            @endif

                            <!-- Add More Attachments / Photos -->
                            <div class="col-12 mt-3">
                                <label class="form-label fw-semibold d-flex justify-content-between align-items-center">
                                    <span><i class="fas fa-plus-circle text-primary me-1"></i> Upload More Attachments & Photos <small class="text-muted">(Multiple files allowed)</small></span>
                                    <small class="text-muted">Images, PDF, Documents, ZIP (Max 20MB per file)</small>
                                </label>
                                <div class="border rounded-3 p-3 bg-light">
                                    <input type="file" name="attachments[]" id="edit_attachments_input" class="form-control rounded-3" multiple accept="image/*,.pdf,.doc,.docx,.xls,.xlsx,.ppt,.pptx,.txt,.zip,.rar">
                                    <div id="edit_attachments_preview" class="d-flex flex-wrap gap-2 mt-2"></div>
                                    <small class="text-muted mt-1 d-block"><i class="fas fa-info-circle me-1"></i> New photos/files uploaded will be attached to this task for all assigned members to see.</small>
                                </div>
                            </div>
                        </div>

                        <div class="d-flex justify-content-end gap-2 mt-4 pt-3 border-top">
                            <a href="{{ route('admin.work_management.tasks.show', encrypt($task->id)) }}" class="btn btn-light rounded-pill px-4">Cancel</a>
                            <button type="submit" class="btn btn-primary rounded-pill px-5 shadow-sm">
                                <i class="fas fa-save me-1"></i> Update Task
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
    $('#project_select').on('change', function() {
        var pId = $(this).val();
        var $milestone = $('#milestone_select');
        if (!pId) {
            $milestone.empty().append('<option value="">No Milestone</option>').trigger('change');
            return;
        }
        $.get("{{ route('admin.work_management.tasks.ajax_project_data') }}", { project_id: pId }, function(res) {
            if (res.status === 1) {
                $milestone.empty().append('<option value="">No Milestone</option>');
                if (res.milestones && res.milestones.length > 0) {
                    res.milestones.forEach(function(m) {
                        $milestone.append('<option value="' + m.id + '">' + m.title + '</option>');
                    });
                }
                $milestone.trigger('change');
            }
        });
    });

    // Delete existing attachment
    $('.delete-edit-attachment').on('click', function() {
        var attId = $(this).data('id');
        if (!confirm('Are you sure you want to remove this attachment?')) return;
        $.ajax({
            url: "/admin/work-management/tasks/delete-attachment/" + attId,
            type: 'DELETE',
            data: { _token: "{{ csrf_token() }}" },
            success: function(res) {
                if (res.status === 1) {
                    $('#edit_att_' + attId).fadeOut(function() { $(this).remove(); });
                } else {
                    alert(res.message || 'Error deleting file');
                }
            }
        });
    });

    // Live preview for new attachments
    $('#edit_attachments_input').on('change', function() {
        var $preview = $('#edit_attachments_preview');
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
</script>
@endpush

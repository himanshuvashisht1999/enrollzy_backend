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
                    <form action="{{ route('admin.work_management.tasks.update', encrypt($task->id)) }}" method="POST">
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
        if (!pId) return;
        $.get("{{ route('admin.work_management.tasks.ajax_project_data') }}", { project_id: pId }, function(res) {
            if (res.status === 1) {
                var $milestone = $('#milestone_select');
                $milestone.empty().append('<option value="">No Milestone</option>');
                res.milestones.forEach(function(m) {
                    $milestone.append('<option value="' + m.id + '">' + m.title + '</option>');
                });
            }
        });
    });
</script>
@endpush

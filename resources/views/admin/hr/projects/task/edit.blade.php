@extends('admin.layouts.master')

@section('title', 'Edit Task')

@push('css')
<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
@endpush

@section('content')
<div class="container-fluid">
    <div class="row justify-content-center">
        <div class="col-md-9">
            <div class="card shadow-sm border-0 rounded-4">
                <div class="card-header bg-white py-3">
                    <div class="d-flex align-items-center">
                        <div class="bg-warning bg-opacity-10 p-2 rounded-3 me-3">
                            <i class="fas fa-tasks text-warning"></i>
                        </div>
                        <h5 class="m-0 fw-bold">Edit Task</h5>
                    </div>
                </div>
                <div class="card-body p-4">
                    <form action="{{ route('admin.hr.projects.tasks.update', encrypt($task->id)) }}" method="POST">
                        @csrf
                        @method('PUT')
                        
                        <div class="row g-4">
                            <div class="col-md-12">
                                <label class="form-label fw-semibold">Task Title <span class="text-danger">*</span></label>
                                <input type="text" name="title" class="form-control rounded-pill px-3" placeholder="Enter task name" required value="{{ old('title', $task->title) }}">
                            </div>

                            <div class="col-md-6">
                                <label class="form-label fw-semibold">Project <span class="text-danger">*</span></label>
                                <select name="project_id" id="project_id" class="form-select rounded-pill px-3" required>
                                    @foreach($project as $p)
                                        <option value="{{ $p->id }}" {{ $task->project_id == $p->id ? 'selected' : '' }}>{{ $p->title }}</option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="col-md-6">
                                <label class="form-label fw-bold">Milestone</label>
                                <select name="milestone_id" id="milestone_id" class="form-select select2 rounded-pill px-3" data-placeholder="No Milestone">
                                    <option value=""></option>
                                    @foreach($milestones as $m)
                                        <option value="{{ $m->id }}" {{ $task->milestone_id == $m->id ? 'selected' : '' }}>{{ $m->title }}</option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="col-md-6">
                                <label class="form-label fw-semibold">Start Date <span class="text-danger">*</span></label>
                                <input type="date" name="start_date" class="form-control rounded-pill px-3" required value="{{ old('start_date', $task->start_date) }}">
                            </div>

                            <div class="col-md-6">
                                <label class="form-label fw-semibold">Due Date</label>
                                <input type="date" name="due_date" class="form-control rounded-pill px-3" value="{{ old('due_date', $task->due_date) }}">
                            </div>

                            <div class="col-md-6">
                                <label class="form-label fw-bold">Assigned To <span class="text-danger">*</span></label>
                                <select name="assigned_to[]" id="assigned_to" class="form-select select2 rounded-4" multiple required data-placeholder="Select Project First">
                                    @php $assigned = explode(',', $task->assigned_to); @endphp
                                    @foreach($staff as $s)
                                        <option value="{{ $s->id }}" {{ in_array($s->id, $assigned) ? 'selected' : '' }}>{{ $s->name }}</option>
                                    @endforeach
                                </select>
                                <small class="text-muted ms-2">Hold Ctrl to select multiple</small>
                            </div>

                            <div class="col-md-6">
                                <label class="form-label fw-semibold">Priority <span class="text-danger">*</span></label>
                                <select name="priority" class="form-select rounded-pill px-3" required>
                                    <option value="low" {{ $task->priority == 'low' ? 'selected' : '' }}>Low</option>
                                    <option value="medium" {{ $task->priority == 'medium' ? 'selected' : '' }}>Medium</option>
                                    <option value="high" {{ $task->priority == 'high' ? 'selected' : '' }}>High</option>
                                    <option value="urgent" {{ $task->priority == 'urgent' ? 'selected' : '' }}>Urgent</option>
                                </select>
                            </div>

                            <div class="col-md-12">
                                <label class="form-label fw-semibold">Status <span class="text-danger">*</span></label>
                                <select name="status" class="form-select rounded-pill px-3" required>
                                    <option value="pending" {{ $task->status == 'pending' ? 'selected' : '' }}>Pending</option>
                                    <option value="in_progress" {{ $task->status == 'in_progress' ? 'selected' : '' }}>In Progress</option>
                                    <option value="completed" {{ $task->status == 'completed' ? 'selected' : '' }}>Completed</option>
                                    <option value="cancelled" {{ $task->status == 'cancelled' ? 'selected' : '' }}>Cancelled</option>
                                </select>
                            </div>

                            <div class="col-12">
                                <label class="form-label fw-semibold">Description</label>
                                <textarea name="description" class="form-control rounded-4" rows="4">{{ old('description', $task->description) }}</textarea>
                            </div>
                        </div>

                        <div class="d-flex justify-content-between align-items-center mt-5 pt-2">
                            <a href="{{ route('admin.hr.projects.tasks.index') }}" class="btn btn-light rounded-pill px-4">
                                <i class="fas fa-arrow-left me-1 small"></i> Back
                            </a>
                            <button type="submit" class="btn btn-warning rounded-pill px-5 fw-bold shadow-sm">
                                Update Task <i class="fas fa-save ms-1 small"></i>
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
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.css"></script>
<script>
    $(document).ready(function() {
        $('.select2').select2({ width: '100%' });

        $('#project_id').on('change', function() {
            let projectId = $(this).val();
        if(!projectId) return;

        $.ajax({
            url: "{{ route('admin.hr.projects.tasks.getProjectData') }}",
            type: "GET",
            data: { project_id: projectId },
            success: function(resp) {
                if(resp.status == 1) {
                    let staffHtml = '';
                    resp.staff.forEach(s => {
                        staffHtml += `<option value="${s.id}">${s.name}</option>`;
                    });
                    $('#assigned_to').html(staffHtml).trigger('change');

                    let milestoneHtml = '<option value=""></option>';
                    resp.milestones.forEach(m => {
                        milestoneHtml += `<option value="${m.id}">${m.title}</option>`;
                    });
                    $('#milestone_id').html(milestoneHtml).trigger('change');
                }
            }
        });
    });
    });
</script>
@endpush

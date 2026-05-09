@extends('admin.layouts.master')

@section('title', 'Create New Task')

@push('css')
<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
@endpush

@section('content')
<div class="container-fluid">
    <div class="card shadow-sm border-0 rounded-4">
        <div class="card-header bg-white py-3">
            <h6 class="m-0 fw-bold text-primary">Task Assignment Form</h6>
        </div>
        <div class="card-body">
            <form action="{{ route('admin.hr.projects.tasks.store') }}" method="POST">
                @csrf
                <div class="row g-3">
                    <div class="col-md-6">
                        <label class="form-label small fw-bold">Task Title <span class="text-danger">*</span></label>
                        <input type="text" name="title" class="form-control rounded-3" value="{{ old('title') }}" required>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label small fw-bold">Project <span class="text-danger">*</span></label>
                        <select name="project_id" id="project_id" class="form-select select2 rounded-3" required>
                            <option value="">Select Project</option>
                            @foreach($project as $p)
                                <option value="{{ $p->id }}">{{ $p->title }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label small fw-bold">Priority <span class="text-danger">*</span></label>
                        <select name="priority" class="form-select rounded-3" required>
                            <option value="low">Low</option>
                            <option value="medium" selected>Medium</option>
                            <option value="high">High</option>
                            <option value="urgent">Urgent</option>
                        </select>
                    </div>

                    <div class="col-md-4">
                        <label class="form-label small fw-bold">Start Date <span class="text-danger">*</span></label>
                        <input type="date" name="start_date" id="start_date" class="form-control rounded-3" value="{{ old('start_date', date('Y-m-d')) }}" required>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label small fw-bold">Due Date</label>
                        <input type="date" name="due_date" id="due_date" class="form-control rounded-3" value="{{ old('due_date') }}">
                        <div id="dateError" class="text-danger small mt-1"></div>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label small fw-bold">Estimated Hours</label>
                        <div class="input-group">
                            <input type="number" step="0.5" name="estimated_hours" class="form-control rounded-start-3" value="{{ old('estimated_hours') }}">
                            <span class="input-group-text rounded-end-3 bg-light">hrs</span>
                        </div>
                    </div>

                    <div class="col-md-4">
                        <label class="form-label small fw-bold">Assign To <span class="text-danger">*</span></label>
                        <select name="assigned_to[]" id="assigned_to" class="form-select select2 rounded-3" multiple required data-placeholder="Select Project First">
                        </select>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label small fw-bold">Milestone</label>
                        <select name="milestone" id="milestone" class="form-select select2 rounded-3" data-placeholder="No Milestone">
                            <option value=""></option>
                        </select>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label small fw-bold">Initial Status <span class="text-danger">*</span></label>
                        <select name="status" class="form-select rounded-3" required>
                            <option value="not_started">Not Started</option>
                            <option value="in_progress">In Progress</option>
                            <option value="on_hold">On Hold</option>
                            <option value="completed">Completed</option>
                        </select>
                    </div>

                    <div class="col-12">
                        <label class="form-label small fw-bold">Task Description</label>
                        <textarea name="description" class="form-control rounded-4" rows="4">{{ old('description') }}</textarea>
                    </div>
                </div>

                <div class="text-end mt-4">
                    <a href="{{ route('admin.hr.projects.tasks.index') }}" class="btn btn-light rounded-pill px-4 me-2">Cancel</a>
                    <button type="submit" class="btn btn-primary rounded-pill px-5">Assign Task</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@push('js')
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
<script>
    $(document).ready(function() {
        $('.select2').select2({ width: '100%' });

        $('#project_id').on('change', function() {
            let projectId = $(this).val();
            if(!projectId) return;

            $.ajax({
                url: "{{ route('admin.hr.projects.get-data') }}",
                type: 'POST',
                data: {
                    project_id: projectId,
                    _token: "{{ csrf_token() }}"
                },
                success: function(res) {
                    if(res.status == 1) {
                        let staffOption = $('#assigned_to');
                        staffOption.empty();
                        $.each(res.staff, function(k, v) {
                            staffOption.append(`<option value="${v.id}">${v.name}</option>`);
                        });
                        staffOption.trigger('change');

                        let milestoneOption = $('#milestone');
                        milestoneOption.empty().append('<option value=""></option>');
                        $.each(res.milestones, function(k, v) {
                            milestoneOption.append(`<option value="${v.id}">${v.title}</option>`);
                        });
                        milestoneOption.trigger('change');
                    }
                }
            });
        });

        function validateDates() {
            let start = $('#start_date').val();
            let due = $('#due_date').val();
            if (due && start && new Date(due) < new Date(start)) {
                $('#dateError').text('Due date cannot be before start date');
                return false;
            }
            $('#dateError').text('');
            return true;
        }

        $('#start_date, #due_date').on('change', validateDates);
    });
</script>
@endpush

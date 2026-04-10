@extends('layouts.app')
@section('push_css')
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/select2@4.0.13/dist/css/select2.min.css">
    <link rel="stylesheet"
        href="https://cdn.jsdelivr.net/npm/@ttskch/select2-bootstrap4-theme/dist/select2-bootstrap4.min.css">
@endsection
@section('content')
    <div class="container-fluid">
        <div class="card shadow mb-4">
            <div class="card-header py-3 d-flex justify-content-between">
                <h6 class="m-0 font-weight-bold text-primary">Add Task</h6>
            </div>
            <div class="card-body">
                <form id="addTasksForm" action="{{ route('admin.task.store') }}" method="POST">
                    @csrf
                    <div class="row">
                        <div class="col-md-6 form-group">
                            <label for="title">Title</label>
                            <input type="text" class="form-control" name="title" value="{{ old('title') }}"
                                placeholder="Title" required>
                        </div>
                        <div class="col-md-3 form-group">
                            <label for="project_id">Project</label>
                            <select class="form-control" name="project_id" id="projectID" required>
                                <option value="" selected disabled></option>
                                @foreach ($project as $prjt)
                                    <option value="{{ $prjt->id }}"
                                        {{ old('project_id') == $prjt->id ? 'selected' : '' }}> {{ $prjt->title }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-3 form-group">
                            <label for="priority">Priority</label>
                            <select class="form-control" name="priority" required>
                                <option value="low" {{ old('priority') == 'low' ? 'selected' : '' }}>Low</option>
                                <option value="medium" {{ old('priority') == 'medium' ? 'selected' : '' }}>Medium</option>
                                <option value="high" {{ old('priority') == 'high' ? 'selected' : '' }}>High</option>
                            </select>
                        </div>
                        <div class="col-md-3 form-group">
                            <label for="priority">Is Recursive Task</label>
                            <select class="form-control" name="recursive_task" id="recursive_task" required>
                            <option value="no">No</option>
                                <option value="yes">Yes</option>
                            </select>
                        </div>
                        <div class="col-md-3 form-group">
                            <label for="start_date">Recursive Repeat</label>
                            <select class="form-control" name="recursive_repeat" id="recursive_repeat" disabled required>
                                <option value="Daily">Daily</option>
                                <option value="Weekly">Weekly</option>
                                <option value="Monthly">Monthly</option>
                                <option value="yearly">yearly</option>
                                <option value="Manually">Choose Manually</option>
                            </select>
                        </div>
                        <div class="col-md-3 form-group">
                            <label for="manual_date">Recursive Manualy</label>
                            <input type="date" class="form-control" name="manual_date" id="recursive_interval"
                                value=""
                                disabled required >
                        </div>
                        <div class="col-md-4 form-group">
                            <label for="start_date">Start Date</label>
                            <input type="date" class="form-control" name="start_date" id="start_date"
                                value="" required>
                        </div>
                        <div class="col-md-4 form-group">
                            <label for="due_date">Due Date</label>
                            <input type="date" class="form-control" name="due_date" id="due_date"
                                value="">
                            <span id="dateError" class="text-danger"></span>
                        </div>
                        <div class="col-md-4 form-group">
                            <label for="estimated_hours">Estimated Hours</label>
                            <input type="number" class="form-control" name="estimated_hours"
                                value="{{ old('estimated_hours') }}" step="0.01" placeholder="Estimated Hours">
                        </div>
                        <div class="col-md-4 form-group">
                            <label for="assigned_to">Assigned To</label>
                            <select class="form-control" name="assigned_to[]" multiple required id="staffOption">
                                <option value="">Select Project First</option>
                            </select>
                        </div>
                        <div class="col-md-4 form-group">
                            <label for="milestone">Milestone</label>
                            <select class="form-control" name="milestone" id="milestoneOption">
                                <option value="">Select Project First</option>
                            </select>
                        </div>
                        <div class="col-md-4 form-group">
                            <label for="milestone">Status</label>
                            <select class="form-control" name="status" required>
                                <option value="not_started" {{ old('status') == 'not_started' ? 'selected' : '' }}>Not
                                    Started</option>
                                <option value="in_progress" {{ old('status') == 'in_progress' ? 'selected' : '' }}>In
                                    Progress</option>
                                <option value="completed" {{ old('status') == 'completed' ? 'selected' : '' }}>Completed
                                </option>
                                <option value="on_hold" {{ old('status') == 'on_hold' ? 'selected' : '' }}>On Hold</option>
                                <option value="pending" {{ old('status') == 'pending' ? 'selected' : '' }}>Pending</option>
                            </select>

                        </div>
                        <div class="col-md-12 form-group">
                            <label for="description">Description</label>
                            <textarea name="description" class="form-control">{{ old('description') }}</textarea>
                        </div>
                    </div>
                </form>
            </div>
            <div class="card-footer text-right">
                <a class="btn btn-secondary btn-sm" href="{{ route('admin.task.index') }}">Cancel </a>
                <button class="btn btn-primary btn-sm" form="addTasksForm" type="submit">Add Task</button>
            </div>
        </div>
    </div>
@endsection
@section('push_script')
    <script src="https://cdn.jsdelivr.net/npm/select2@4.0.13/dist/js/select2.min.js"></script>
    <script>

    const recursiveTaskSelect = document.getElementById('recursive_task');
    const recursiveInterval = document.getElementById('recursive_repeat');
    const recursiveDate = document.getElementById('recursive_interval');
    const recursivemanuals = document.getElementById('recursive_manualy');

    recursiveTaskSelect.addEventListener('change', function () {
        if (this.value === 'yes') {
            // Enable fields
            recursiveInterval.disabled = false;
            recursiveDate.disabled = false;
        } else {
            // Disable fields
            recursiveInterval.disabled = true;
            recursiveDate.disabled = true;
            recursivemanuals.disabled = true;
        }
    });


    const recursiveRepeat = document.getElementById('recursive_repeat');
    const recursiveManualy = document.getElementById('recursive_manualy');

    recursiveRepeat.addEventListener('change', function () {
        if (this.value === 'Manually') {
            recursiveManualy.disabled = false; // Enable the manual select dropdown
        } else {
            recursiveManualy.disabled = true;  // Disable the manual select dropdown
        }
    });
        $(document).ready(function() {
            $('#staffOption').select2({
                theme: 'bootstrap4',
            });
        });
        $('#projectID').on('change', function(e) {
            e.preventDefault();
            $.ajax({
                type: 'POST',
                url: "{{ route('admin.task.get_staff_milestone') }}",
                data: {
                    'project_id': $(this).val(),
                    "_token": "{{ csrf_token() }}",
                },
                success: function(response) {
                    $('#staffOption').html('');
                    $('#milestoneOption').html('');
                    if (response.status == 1) {
                        const staff = response.staff;
                        const milestone = response.milestone;
                        // ------------------ Staff option below
                        $.each(staff, function(key, value) {
                            $('#staffOption')
                                .append(
                                    `<option value="${value.id}" >${value.name}</option>`
                                )
                        });
                        // ------------------ Milestone option below
                        $('#milestoneOption').append('<option value=""></option>');
                        $.each(milestone, function(key, value) {
                            $('#milestoneOption')
                                .append(
                                    `<option value="${value.id}" >${value.title}</option>`
                                )
                        });
                    } else if (response.status == 0) {
                        toastr["error"](response.message, "Error");
                    }
                },
            });
        })
        // ----------------------------- jQuery end here
        function validateDates() {
            var startDate = $('#start_date').val();
            var dueDate = $('#due_date').val();
            if (dueDate && startDate && new Date(dueDate) < new Date(startDate)) {
                $('#dateError').text('Due Date must be equal or later than the Start Date');
            } else {
                $('#dateError').text('');
            }
        }
        // Trigger validation when either date is changed
        $('#start_date, #due_date').on('change', function() {
            validateDates();
        });
        validateDates();
    </script>
@endsection

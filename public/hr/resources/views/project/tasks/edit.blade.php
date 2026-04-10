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
                <h6 class="m-0 font-weight-bold text-primary">Edit Task</h6>
            </div>
            <div class="card-body">
                <form id="updateTaskForm" action="{{ route('admin.task.update', encrypt($task->id)) }}" method="POST">
                    @csrf
                    @method('PATCH')
                    <div class="row">
                        <div class="col-md-6 form-group">
                            <label for="title">Title</label>
                            <input type="text" class="form-control" name="title"
                                value="{{ old('title') ?? $task->title }}" placeholder="Title" required>
                        </div>
                        <div class="col-md-3 form-group">
                            <label for="project_id">Project</label>
                            <select class="form-control" name="project_id" id="projectID" required readonly>
                                <option value="{{ $project->id }}"
                                    {{ old('project_id') ?? $task->project_id == $project->id ? 'selected' : '' }}>
                                    {{ $project->title }}
                                </option>
                            </select>
                        </div>
                        <div class="col-md-3 form-group">
                            <label for="priority">Priority</label>
                            <select class="form-control" name="priority" required>
                                <option value="low" {{ old('priority') ?? $task->priority == 'low' ? 'selected' : '' }}>
                                    Low</option>
                                <option value="medium"
                                    {{ old('priority') ?? $task->priority == 'medium' ? 'selected' : '' }}>Medium</option>
                                <option value="high" {{ old('priority') ?? $task->priority == 'high' ? 'selected' : '' }}>
                                    High</option>
                            </select>
                        </div>
                        <div class="col-md-3 form-group">
                            <label for="priority">Is Recursive Task</label>
                            <select class="form-control" name="recursive_task" id="recursive_task" required>
                            <option value="no"  {{ old('priority') == 'no' || $task->id_recursive_task == 'no' ? 'selected' : '' }}>No</option>
                                <option value="yes"  {{ old('priority') == 'yes' || $task->id_recursive_task == 'yes' ? 'selected' : '' }}>Yes</option>
                            </select>
                        </div>
                        <div class="col-md-3 form-group">
                            <label for="start_date">Recursive Repeat</label>
                            <select class="form-control" name="recursive_repeat" id="recursive_repeat"  {{ $task->id_recursive_task == 'yes' ? '' : 'disabled' }}  required>
                            <option value="Daily" {{ old('recursive_repeat', $task->recursive_repeat) == 'Daily' ? 'selected' : '' }}>Daily</option>
                                <option value="Weekly" {{ old('recursive_repeat', $task->recursive_repeat) == 'Weekly' ? 'selected' : '' }}>Weekly</option>
                                <option value="Monthly" {{ old('recursive_repeat', $task->recursive_repeat) == 'Monthly' ? 'selected' : '' }}>Monthly</option>
                                <option value="Yearly" {{ old('recursive_repeat', $task->recursive_repeat) == 'Yearly' ? 'selected' : '' }}>Yearly</option>
                                <option value="Manually" {{ old('recursive_repeat', $task->recursive_repeat) == 'Manually' ? 'selected' : '' }}>Choose Manually</option>
                            </select>
                        </div>
                        <div class="col-md-3 form-group">
                            <label for="manual_date">Recursive Manualy</label>
                            <input type="date" class="form-control" name="manual_date" id="recursive_interval"
                                value="{{$task->recursive_manualy}}"
                                {{ $task->id_recursive_task == 'yes' ? '' : 'disabled' }} required >
                        </div>
                        <div class="col-md-4 form-group">
                            <label for="start_date">Start Date</label>
                            <input type="date" class="form-control" name="start_date" id="start_date"
                                value="{{ old('start_date') ?? ($task->start_date ?? date('Y-m-d')) }}" required>
                        </div>
                        <div class="col-md-4 form-group">
                            <label for="due_date">Due Date</label>
                            <input type="date" class="form-control" name="due_date" id="due_date"
                                value="{{ old('due_date') ?? ($task->due_date ?? date('Y-m-d')) }}">
                            <span id="dateError" class="text-danger"></span>
                        </div>
                        <div class="col-md-4 form-group">
                            <label for="estimated_hours">Estimated Hours</label>
                            <input type="number" class="form-control" name="estimated_hours"
                                value="{{ old('estimated_hours') ?? $task->estimated_hours }}" step="0.01"
                                placeholder="Estimated Hours">
                        </div>
                        <div class="col-md-4 form-group">
                            <label for="assigned_to">Assigned To</label>
                            <select class="form-control" name="assigned_to[]" multiple required id="staffOption">
                                @foreach ($staff as $stf)
                                    <option value="{{ $stf->id }}"
                                        {{ in_array($stf->id, explode(',', $task->assigned_to)) ? 'selected' : '' }}>
                                        {{ $stf->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-4 form-group">
                            <label for="milestone">Milestone</label>
                            <select class="form-control" name="milestone" id="milestoneOption">
                                <option>Select Milestone</option>
                                @foreach ($milestone as $milestn)
                                    <option {{ $task->milestone == $milestn->id ? 'selected' : '' }}
                                        value="{{ $milestn->id }}">{{ $milestn->title }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-4 form-group">
                            <label for="milestone">Status</label>
                            <select class="form-control" name="status" required>
                                <option value="not_started"
                                    {{ old('status') ?? $task->status == 'not_started' ? 'selected' : '' }}>Not
                                    Started</option>
                                <option value="in_progress"
                                    {{ old('status') ?? $task->status == 'in_progress' ? 'selected' : '' }}>In
                                    Progress</option>
                                <option value="completed"
                                    {{ old('status') ?? $task->status == 'completed' ? 'selected' : '' }}>Completed
                                </option>
                                <option value="on_hold"
                                    {{ old('status') ?? $task->status == 'on_hold' ? 'selected' : '' }}>On Hold</option>
                                <option value="pending"
                                    {{ old('status') ?? $task->status == 'pending' ? 'selected' : '' }}>Pending</option>
                            </select>

                        </div>
                        <div class="col-md-12 form-group">
                            <label for="description">Description</label>
                            <textarea name="description" class="form-control">{{ old('description') ?? $task->description }}</textarea>
                        </div>
                    </div>
                </form>
            </div>
            <div class="card-footer text-right">
                <a class="btn btn-secondary btn-sm" href="{{ route('admin.task.index') }}">Cancel </a>
                <button class="btn btn-success btn-sm" form="updateTaskForm" type="submit">Update Task</button>
            </div>
        <div class="card-body mt-4">
            <div class="card-header py-3 d-flex justify-content-between">
                <h6 class="m-0 font-weight-bold text-primary">Comments</h6>
                <a class="btn btn-primary btn-sm" href="javascript:;" data-toggle="modal" data-target="#addInstitute">
                    <i class="fas fa-plus fa-sm text-white-50"></i> Add Comment
                </a>
            </div><br>
            <ul class="scrollable-comments">
                @foreach($taskcomments as $comments)
                <li>
                    <div class="comment">
                        <div class="row">
                            <div class="col-md-6">
                                <strong>{{ $comments->user->name }} ({{ $comments->created_at->diffForHumans() }} at {{ \Carbon\Carbon::parse($comments->created_at)->format('d F Y H:i') }}):</strong>
                                <p><b>Comment: </b> {{ $comments->comment }}</p>
                            </div>
                            <div class="col-md-6">
                                <div class="container">
                                    <div class="row">
                                            @php
                                                $docs = $comments->documents
                                                    ? array_filter(array_map('trim', explode(',', $comments->documents)))
                                                    : [];
                                            @endphp
                                            @foreach($docs as $file)

                                                @php
                                                    $extension = pathinfo($file, PATHINFO_EXTENSION);
                                                    $fileUrl = URL::asset($file);
                                                @endphp
                                                @if (in_array($extension, ['jpg', 'jpeg', 'png']))
                                                <div class="col-md-4">
                                                    <a href="{{ $fileUrl }}" target="_blank" class="btn btn-sm btn-primary">
                                                        <img src="{{ $fileUrl }}" alt="Image" style="width: 100%; max-height: 200px; object-fit: cover;">
                                                    </a>
                                                </div>
                                                @elseif ($extension == 'pdf')
                                                <div class="col-md-4">
                                                    <iframe src="{{ $fileUrl }}" frameborder="0" style="width:100%;"></iframe>
                                                    <a href="{{ $fileUrl }}" target="_blank" >View Document</a>
                                                </div>
                                                @else
                                                    <!-- Default case or unsupported file type -->
                                                    <p>Unsupported file type</p>
                                                @endif
                                            @endforeach
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </li>
                <br>
                @endforeach
            </ul>
            <hr>
        </div>
        </div>

<div class="card shadow my-4">
    <div class="card-header py-3 d-flex justify-content-between">
        <h6 class="m-0 font-weight-bold text-primary">Attachments</h6>
    </div>
    @can('staff-read')
    <div class="card-body">
        <form id="uploadDocument" method="POST"
            action="{{ route('admin.task.update_document', encrypt($task->id)) }}" enctype="multipart/form-data">
            @csrf
            <div class="row">
                <div class="col-md-12 form-group mb-4">
                    <label for="address">Upload Image or Documents (*PDF, *Docs)</label>
                    <input type="file" multiple class="form-control" name="files[]">
                </div>
            </div>
        </form>
        <div class="container">
            @php
            $docs = $task->documents
            ? array_filter(array_map('trim', explode(',', $task->documents)))
            : [];
            @endphp
            @if (count($docs) > 0)
            <div class="row">
                @foreach ($docs as $file)
                @php
                // Get the file extension
                $extension = pathinfo($file, PATHINFO_EXTENSION);
                // Prepare the file URL

                $fileUrl = URL::asset($file);
                @endphp
                <div class="col-lg-4 mb-4">
                    <div class="document-container">
                        <form action="{{ route('admin.task.destroy_doc', ['url' => basename($file)]) }}"
                            method="POST" onsubmit="return confirm('Are you sure you want to delete this file?');">
                            <input type="hidden" name="staff_id" value="{{ $task->id }}">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="btn btn-sm btn-danger mb-2 delete-button"
                                style="float: right">&times;</button>
                        </form>
                        @if (in_array($extension, ['jpg', 'jpeg', 'png']))
                        <!-- Image File -->
                        <a href="{{ $fileUrl }}" target="_blank" >
                        <img src="{{ $fileUrl }}" alt="Image" style="width: 90%;"></a>
                        @elseif ($extension == 'pdf')
                        <!-- PDF File -->
                        <iframe src="{{ $fileUrl }}" frameborder="0"></iframe>
                        <a href="{{ $fileUrl }}" target="_blank">View Document</a>
                        @elseif (in_array($extension, ['doc', 'docx']))
                        <!-- Document File -->
                        <a href="{{ $fileUrl }}" target="_blank" class="btn btn-sm btn-primary">View Document <i
                                class="fa fa-eye"></i></a>
                        @else
                        <!-- Default case or unsupported file type -->
                        <p>Unsupported file type</p>
                        @endif
                    </div>
                </div>
                @endforeach
            </div>
            @endif
        </div>
    </div>
    <div class="card-footer text-right">
        <button type="submit" class="btn btn-success" form="uploadDocument">Upload Doc</button>
    </div>
    @endcan
</div>
    </div>
<div class="modal fade" id="addInstitute" tabindex="-1" role="dialog" aria-labelledby="addInstitute" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content modal-l">
            <div class="modal-header">
                <h5 class="modal-title" id="exampleModalLabel">Add Comment</h5>
                <button class="close" type="button" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">×</span>
                </button>
            </div>
            <form id="updateTaskCommentForm" action="{{ url('admin/taskcomment/store/'.$task->id) }}" method="POST" enctype="multipart/form-data">
                <div class="modal-body">
                    @csrf
                    <div class="row">
                        <div class="col-md-12 form-group">
                            <label for="comment">Comment</label>
                            <textarea class="form-control" name="comment" id="comment"
                                placeholder="Add your comment here..." required></textarea>
                        </div>
                    <div class="col-md-12 form-group">
                        <label for="address">Upload Image or Documents (*PDF, *Docs)</label>
                        <input type="file" multiple class="form-control" name="files[]">
                    </div>
                    </div>
            </form>
        </div>
        <div class="modal-footer">
            <button class="btn btn-secondary" type="button" data-dismiss="modal">Cancel</button>
            <button class="btn btn-success btn-sm" form="updateTaskCommentForm" type="submit">Add Comment</button>
        </div>
    </div>
</div>
@endsection

@section('push_script')
    <script src="https://cdn.jsdelivr.net/npm/select2@4.0.13/dist/js/select2.min.js"></script>
    <script>


        // Load designations based on selected department
        $('#departmentSelect').on('change', function() {
            var departmentId = $(this).val(); // Get the selected department ID

            if (departmentId) {
                $.ajax({
                    url: '/admin/roless/get-designations/' + departmentId,// URL to fetch designations
                    method: 'GET',
                    success: function(data) {
                        // Clear current options
                        $('#designationSelect').html('<option selected disabled>Select Designation</option>');

                        // Add new options
                        data.designations.forEach(function(designation) {
                            $('#designationSelect').append('<option value="' + designation.id + '">' + designation.name + '</option>');
                        });
                    }
                });
            }
        });

        // Load users based on selected designation
        $('#designationSelect').on('change', function() {
            var designationId = $(this).val(); // Get the selected designation ID

            if (designationId) {
                $.ajax({
                    url: '/admin/roless/get-users/' + designationId, // URL to fetch users
                    method: 'GET',
                    success: function(data) {
                        // Clear current options
                        $('#workingDays').html('');

                        // Add new options
                        data.users.forEach(function(user) {
                            $('#workingDays').append('<option value="' + user.id + '">' + user.name + '</option>');
                        });
                    }
                });
            }
        });

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
        });
    </script>
@endsection

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
                <h6 class="m-0 font-weight-bold text-primary">Edit Project </h6>
            </div>
            <div class="card-body">
                <form id="updateProjectForm" action="{{ route('admin.projects.update', encrypt($project->id)) }}"
                    method="POST">
                    @csrf
                    @method('PATCH')
                    <div class="row">
                        <div class="col-md-6 form-group">
                            <label for="title">Title</label>
                            <input type="text" class="form-control" name="title"
                                value="{{ old('title') ?? $project->title }}" placeholder="Title" required>
                        </div>
                        <div class="col-md-3 form-group">
                            <label for="category_id">Category</label>
                            <select class="form-control" name="category_id" id="category_id" required>
                                @foreach ($projectCategory as $category)
                                    <option
                                        {{ old('category_id') ?? $project->category_id == $category->id ? 'selected' : '' }}
                                        value="{{ $category->id }}">{{ $category->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-3 form-group">
                            <label for="price">Price</label>
                            <input type="number" class="form-control" name="price"
                                value="{{ old('price') ?? $project->price }}" placeholder="Price">
                        </div>
                        <div class="col-md-4 form-group">
                            <label for="start_date">Start Date</label>
                            <input type="date" class="form-control" name="start_date" id="start_date"
                                value="{{ old('start_date') ?? ($project->start_date ?? date('Y-m-d')) }}" required>
                        </div>
                        <div class="col-md-4 form-group">
                            <label for="due_date">Due Date</label>
                            <input type="date" class="form-control" name="due_date" id="due_date"
                                value="{{ old('due_date') ?? ($project->due_date ?? date('Y-m-d')) }}">
                            <span id="dateError" class="text-danger"></span>
                        </div>
                        <div class="col-md-4 form-group">
                            <label for="lead_source_id">Lead Source</label>
                            <select class="form-control" name="lead_source_id" id="lead_source_id">
                                @foreach ($leadSource as $source)
                                    <option
                                        {{ old('lead_source_id') ?? $project->lead_source_id == $source->id ? 'selected' : '' }}
                                        value="{{ $source->id }}">{{ $source->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-4 form-group">
                            <label for="client_id">Client ( *Default Personal )</label>
                            <select class="form-control" name="client_id" id="client_id">
                                <option value=""> Select Project User</option>
                                @foreach ($client as $client)
                                    <option {{ old('client_id') ?? $project->client_id == $client->id ? 'selected' : '' }}
                                        value="{{ $client->id }}">{{ $client->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-4 form-group">
                            <label for="status">Status</label>
                            <select class="form-control" name="status" required>
                                <option {{ old('status') ?? $project->status == 'not_started' ? 'selected' : '' }}
                                    value="not_started">Not
                                    Started</option>
                                <option {{ old('status') ?? $project->status == 'in_progress' ? 'selected' : '' }}
                                    value="in_progress">In
                                    Progress</option>
                                <option {{ old('status') ?? $project->status == 'completed' ? 'selected' : '' }}
                                    value="completed">Completed
                                </option>
                                <option {{ old('status') ?? $project->status == 'on_hold' ? 'selected' : '' }}
                                    value="on_hold">On Hold</option>
                                <option {{ old('status') ?? $project->status == 'pending' ? 'selected' : '' }}
                                    value="pending">Pending</option>
                            </select>
                        </div>
                        <div class="col-md-4 form-group">
                            <label for="employee_ids">Employees</label>
                            <select class="form-control" name="employee_ids[]" id="employee_ids" multiple="multiple"
                                required>
                                @php
                                    $selectedStaffIds = explode(',', $project->employee_ids); // Convert comma-separated values to an array
                                @endphp
                                @foreach ($staff as $staffMember)
                                    <option value="{{ $staffMember->id }}"
                                        {{ in_array($staffMember->id, $selectedStaffIds) ? 'selected' : '' }}>
                                        {{ $staffMember->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-12 form-group">
                            <label for="description">Description</label>
                            <textarea name="description" class="form-control">{{ old('description') ?? $project->description }}</textarea>
                        </div>
                    </div>
                </form>
            </div>
            <div class="card-footer text-right">
                <a class="btn btn-secondary btn-sm" href="{{ route('admin.projects.index') }}">Cancel </a>
                <button class="btn btn-success btn-sm" form="updateProjectForm" type="submit">Update Project</button>
            </div>
        </div>
    </div>
@endsection
@section('push_script')
    <script src="https://cdn.jsdelivr.net/npm/select2@4.0.13/dist/js/select2.min.js"></script>
    <script>
        $(document).ready(function() {

            $('#category_id').select2({
                theme: 'bootstrap4',
            });
            $('#lead_source_id').select2({
                theme: 'bootstrap4',
            });
            $('#client_id').select2({
                theme: 'bootstrap4',
            });
            $('#employee_ids').select2({
                theme: 'bootstrap4',
            });
            // -------------------------------
            $(document).ready(function() {
                // Function to check the dates
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
        });
    </script>
@endsection

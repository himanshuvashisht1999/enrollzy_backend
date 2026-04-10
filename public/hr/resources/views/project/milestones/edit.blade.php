@extends('layouts.app')
@section('content')
    <div class="container-fluid">
        <div class="card shadow mb-4">
            <div class="card-header py-3 d-flex justify-content-between">
                <h6 class="m-0 font-weight-bold text-primary">Edit Milestone</h6>
            </div>
            <div class="card-body">
                <form id="updateMilestoneForm" action="{{ route('admin.milestones.update', encrypt($milestone->id)) }}"
                    method="POST">
                    @csrf
                    @method('PATCH')
                    <div class="row">
                        <div class="col-md-4 form-group">
                            <label for="title">Title</label>
                            <input type="text" class="form-control" name="title"
                                value="{{ old('title') ?? $milestone->title }}" placeholder="Title" required>
                        </div>
                        <div class="col-md-4 form-group">
                            <label for="status">Select Project</label>
                            <select class="form-control" name="project_id" required>
                                @foreach ($project as $prjct)
                                    <option
                                        {{ old('project_id') ?? $milestone->project_id == $prjct->id ? 'selected' : '' }}
                                        value="{{ $prjct->id }}">{{ $prjct->title }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-4 form-group">
                            <label for="status">Status</label>
                            <select class="form-control" name="status" required>
                                <option value="incomplete"
                                    {{ old('status') ?? $milestone->status == 'incomplete' ? 'selected' : '' }}>IN Complete
                                </option>
                                <option value="complete"
                                    {{ old('status') ?? $milestone->status == 'complete' ? 'selected' : '' }}>Complete
                                </option>
                            </select>
                        </div>
                        <div class="col-md-4 form-group">
                            <label for="start_date">Start Date</label>
                            <input type="date" class="form-control" name="start_date" id="start_date"
                                value="{{ old('start_date') ?? ($milestone->start_date ?? date('Y-m-d')) }}" required>
                        </div>
                        <div class="col-md-4 form-group">
                            <label for="due_date">Due Date</label>
                            <input type="date" class="form-control" name="due_date" id="due_date"
                                value="{{ old('due_date') ?? ($milestone->due_date ?? date('Y-m-d')) }}">
                            <span id="dateError" class="text-danger"></span>
                        </div>
                        <div class="col-md-4 form-group">
                            <label for="price">Price</label>
                            <input type="number" step="0.01" class="form-control" name="price"
                                value="{{ old('price') ?? $milestone->price }}" placeholder="Price">
                        </div>
                        <div class="col-md-12 form-group">
                            <label for="description">Description</label>
                            <textarea name="description" class="form-control">{{ old('description') ?? $milestone->description }}</textarea>
                        </div>
                    </div>
                </form>
            </div>
            <div class="card-footer text-right">
                <a class="btn btn-secondary btn-sm" href="{{ route('admin.milestones.index') }}">Cancel </a>
                <button class="btn btn-success btn-sm" form="updateMilestoneForm" type="submit">Update Milestone</button>
            </div>
        </div>
    </div>
@endsection
@section('push_script')
    <script>
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
    </script>
@endsection

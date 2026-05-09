@extends('admin.layouts.master')

@section('title', 'Add New Project')

@push('css')
<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
<style>
    .select2-container--default .select2-selection--multiple { border-radius: 0.5rem; border: 1px solid #dee2e6; min-height: 38px; }
    .select2-container--default .select2-selection--single { border-radius: 0.5rem; border: 1px solid #dee2e6; height: 38px; }
</style>
@endpush

@section('content')
<div class="container-fluid">
    <div class="card shadow-sm border-0 rounded-4">
        <div class="card-header bg-white py-3">
            <h6 class="m-0 fw-bold text-primary">New Project Details</h6>
        </div>
        <div class="card-body">
            <form action="{{ route('admin.hr.projects.index.store') }}" method="POST">
                @csrf
                <div class="row g-3">
                    <div class="col-md-6">
                        <label class="form-label small fw-bold">Project Title <span class="text-danger">*</span></label>
                        <input type="text" name="title" class="form-control rounded-3" value="{{ old('title') }}" required>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label small fw-bold">Category <span class="text-danger">*</span></label>
                        <select name="category_id" class="form-select select2 rounded-3" required>
                            @foreach($projectCategory as $category)
                                <option value="{{ $category->id }}">{{ $category->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label small fw-bold">Quoted Price</label>
                        <div class="input-group">
                            <span class="input-group-text border-end-0 bg-transparent">₹</span>
                            <input type="number" name="price" class="form-control rounded-end-3" value="{{ old('price') }}">
                        </div>
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
                        <label class="form-label small fw-bold">Lead Source <span class="text-danger">*</span></label>
                        <select name="lead_source_id" class="form-select select2 rounded-3" required>
                            @foreach($leadSource as $source)
                                <option value="{{ $source->id }}">{{ $source->name }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="col-md-4">
                        <label class="form-label small fw-bold">Client (Project User)</label>
                        <select name="client_id" class="form-select select2 rounded-3">
                            <option value="">-- Personal Project --</option>
                            @foreach($client as $c)
                                <option value="{{ $c->id }}">{{ $c->name }}</option>
                            @endforeach
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
                    <div class="col-md-4">
                        <label class="form-label small fw-bold">Assign Staff <span class="text-danger">*</span></label>
                        <select name="employee_ids[]" class="form-select select2 rounded-3" multiple required>
                            @foreach($staff as $s)
                                <option value="{{ $s->id }}">{{ $s->name }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="col-12">
                        <label class="form-label small fw-bold">Project Description</label>
                        <textarea name="description" class="form-control rounded-4" rows="4">{{ old('description') }}</textarea>
                    </div>
                </div>

                <div class="text-end mt-4">
                    <a href="{{ route('admin.hr.projects.index.index') }}" class="btn btn-light rounded-pill px-4 me-2">Cancel</a>
                    <button type="submit" class="btn btn-primary rounded-pill px-5">Launch Project</button>
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

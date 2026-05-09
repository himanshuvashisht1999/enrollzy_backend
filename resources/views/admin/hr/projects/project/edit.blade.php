@extends('admin.layouts.master')

@section('title', 'Edit Project')

@push('css')
<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
@endpush

@section('content')
<div class="container-fluid">
    <div class="card shadow-sm border-0 rounded-4">
        <div class="card-header bg-white py-3">
            <h6 class="m-0 fw-bold text-primary">Edit Project: {{ $project->title }}</h6>
        </div>
        <div class="card-body">
            <form action="{{ route('admin.hr.projects.index.update', encrypt($project->id)) }}" method="POST">
                @csrf
                @method('PUT')
                <div class="row g-3">
                    <div class="col-md-6">
                        <label class="form-label small fw-bold">Project Title <span class="text-danger">*</span></label>
                        <input type="text" name="title" class="form-control rounded-3" value="{{ $project->title }}" required>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label small fw-bold">Category <span class="text-danger">*</span></label>
                        <select name="category_id" class="form-select select2 rounded-3" required>
                            @foreach($projectCategory as $category)
                                <option value="{{ $category->id }}" {{ $project->category_id == $category->id ? 'selected' : '' }}>{{ $category->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label small fw-bold">Quoted Price</label>
                        <input type="number" name="price" class="form-control rounded-3" value="{{ $project->price }}">
                    </div>

                    <div class="col-md-4">
                        <label class="form-label small fw-bold">Start Date <span class="text-danger">*</span></label>
                        <input type="date" name="start_date" id="start_date" class="form-control rounded-3" value="{{ $project->start_date }}" required>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label small fw-bold">Due Date</label>
                        <input type="date" name="due_date" id="due_date" class="form-control rounded-3" value="{{ $project->due_date }}">
                        <div id="dateError" class="text-danger small mt-1"></div>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label small fw-bold">Lead Source <span class="text-danger">*</span></label>
                        <select name="lead_source_id" class="form-select select2 rounded-3" required>
                            @foreach($leadSource as $source)
                                <option value="{{ $source->id }}" {{ $project->lead_source_id == $source->id ? 'selected' : '' }}>{{ $source->name }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="col-md-4">
                        <label class="form-label small fw-bold">Client (Project User)</label>
                        <select name="client_id" class="form-select select2 rounded-3">
                            <option value="">-- Personal Project --</option>
                            @foreach($client as $c)
                                <option value="{{ $c->id }}" {{ $project->client_id == $c->id ? 'selected' : '' }}>{{ $c->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label small fw-bold">Status <span class="text-danger">*</span></label>
                        <select name="status" class="form-select rounded-3" required>
                            @foreach(['not_started', 'in_progress', 'on_hold', 'completed'] as $st)
                                <option value="{{ $st }}" {{ $project->status == $st ? 'selected' : '' }}>{{ ucwords(str_replace('_', ' ', $st)) }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label small fw-bold">Assign Staff <span class="text-danger">*</span></label>
                        <select name="employee_ids[]" class="form-select select2 rounded-3" multiple required>
                            @php $assigned = explode(',', $project->employee_ids); @endphp
                            @foreach($staff as $s)
                                <option value="{{ $s->id }}" {{ in_array($s->id, $assigned) ? 'selected' : '' }}>{{ $s->name }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="col-12">
                        <label class="form-label small fw-bold">Project Description</label>
                        <textarea name="description" class="form-control rounded-4" rows="4">{{ $project->description }}</textarea>
                    </div>
                </div>

                <div class="text-end mt-4">
                    <a href="{{ route('admin.hr.projects.index.index') }}" class="btn btn-light rounded-pill px-4 me-2">Cancel</a>
                    <button type="submit" class="btn btn-primary rounded-pill px-5">Save Changes</button>
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
    });
</script>
@endpush

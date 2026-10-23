@extends('admin.layouts.master')

@section('title', 'Create Milestone')

@push('css')
<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
@endpush

@section('content')
<div class="container-fluid">
    <div class="card shadow-sm border-0 rounded-4">
        <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center">
            <div>
                <h5 class="m-0 fw-bold text-dark"><i class="fas fa-flag text-primary me-2"></i> Create Milestone</h5>
                <small class="text-muted">Define a key project phase or delivery target</small>
            </div>
            <a href="{{ route('admin.work_management.milestones.index') }}" class="btn btn-light btn-sm rounded-pill px-3">
                <i class="fas fa-arrow-left me-1"></i> Back to Milestones
            </a>
        </div>
        <div class="card-body p-4">
            <form action="{{ route('admin.work_management.milestones.store') }}" method="POST">
                @csrf
                <div class="row g-3">
                    <div class="col-md-6">
                        <label class="form-label small fw-bold">Milestone Title <span class="text-danger">*</span></label>
                        <input type="text" name="title" class="form-control rounded-3" placeholder="e.g. Technical SEO & Schema Implementation" value="{{ old('title') }}" required>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label small fw-bold">Associated Project <span class="text-danger">*</span></label>
                        <select name="project_id" class="form-select select2 rounded-3" required>
                            <option value="">Select Project</option>
                            @foreach($projects as $p)
                                <option value="{{ $p->id }}" {{ (isset($selectedProjectId) && $selectedProjectId == $p->id) ? 'selected' : '' }}>
                                    {{ $p->title }}{{ !empty($p->project_code ?: $p->code) ? ' (' . ($p->project_code ?: $p->code) . ')' : '' }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="col-md-4">
                        <label class="form-label small fw-bold">Assigned Team</label>
                        <select name="team_id" class="form-select select2 rounded-3">
                            <option value="">Select Team (Optional)</option>
                            @foreach($teams as $team)
                                <option value="{{ $team->id }}">{{ $team->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label small fw-bold">Milestone Owner / Lead Staff</label>
                        <select name="owner_id" class="form-select select2 rounded-3">
                            <option value="">Select Staff Owner</option>
                            @foreach($staff as $s)
                                <option value="{{ $s->id }}">{{ $s->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label small fw-bold">Initial Status <span class="text-danger">*</span></label>
                        <select name="status" class="form-select rounded-3" required>
                            <option value="pending" selected>Pending</option>
                            <option value="in_progress">In Progress</option>
                            <option value="completed">Completed</option>
                            <option value="delayed">Delayed</option>
                        </select>
                    </div>

                    <div class="col-md-4">
                        <label class="form-label small fw-bold">Start Date <span class="text-danger">*</span></label>
                        <input type="date" name="start_date" class="form-control rounded-3" value="{{ old('start_date', date('Y-m-d')) }}" required>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label small fw-bold">Target Due Date</label>
                        <input type="date" name="due_date" class="form-control rounded-3" value="{{ old('due_date') }}">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label small fw-bold">Budget / Cost (₹)</label>
                        <input type="number" step="0.01" name="price" class="form-control rounded-3" placeholder="e.g. 50000" value="{{ old('price') }}">
                    </div>

                    <div class="col-12">
                        <label class="form-label small fw-bold">Milestone Scope / Description</label>
                        <textarea name="description" class="form-control rounded-3" rows="3" placeholder="Key deliverables and milestones checklist...">{{ old('description') }}</textarea>
                    </div>
                </div>

                <div class="text-end mt-4">
                    <a href="{{ route('admin.work_management.milestones.index') }}" class="btn btn-light rounded-pill px-4 me-2">Cancel</a>
                    <button type="submit" class="btn btn-primary rounded-pill px-5 shadow-sm">Save Milestone</button>
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

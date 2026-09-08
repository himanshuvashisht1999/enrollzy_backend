@extends('admin.layouts.master')

@section('title', 'Edit Project - ' . $project->title)

@push('css')
<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
@endpush

@section('content')
<div class="container-fluid">
    <div class="card shadow-sm border-0 rounded-4">
        <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center">
            <div>
                <h5 class="m-0 fw-bold text-dark"><i class="fas fa-edit text-primary me-2"></i> Edit Project</h5>
                <small class="text-muted">{{ $project->title }} ({{ $project->project_code }})</small>
            </div>
            <a href="{{ route('admin.work_management.projects.show', encrypt($project->id)) }}" class="btn btn-light btn-sm rounded-pill px-3">
                <i class="fas fa-arrow-left me-1"></i> Project Hub
            </a>
        </div>
        <div class="card-body p-4">
            <form action="{{ route('admin.work_management.projects.update', encrypt($project->id)) }}" method="POST">
                @csrf
                @method('PUT')
                <div class="row g-3">
                    <div class="col-md-8">
                        <label class="form-label small fw-bold">Project Title <span class="text-danger">*</span></label>
                        <input type="text" name="title" class="form-control rounded-3" value="{{ old('title', $project->title) }}" required>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label small fw-bold">Project Code</label>
                        <input type="text" name="project_code" class="form-control rounded-3" value="{{ old('project_code', $project->project_code) }}">
                    </div>

                    <div class="col-md-3">
                        <label class="form-label small fw-bold">Project Category <span class="text-danger">*</span></label>
                        <select name="category_id" class="form-select select2 rounded-3" required>
                            @foreach($categories as $cat)
                                <option value="{{ $cat->id }}" {{ $project->category_id == $cat->id ? 'selected' : '' }}>{{ $cat->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label small fw-bold">Project Type</label>
                        <select name="project_type" class="form-select rounded-3">
                            <option value="Internal" {{ $project->project_type == 'Internal' ? 'selected' : '' }}>Internal</option>
                            <option value="Client" {{ $project->project_type == 'Client' ? 'selected' : '' }}>Client</option>
                            <option value="Marketing" {{ $project->project_type == 'Marketing' ? 'selected' : '' }}>Marketing Campaign</option>
                            <option value="Development" {{ $project->project_type == 'Development' ? 'selected' : '' }}>Technology / Dev</option>
                            <option value="Operations" {{ $project->project_type == 'Operations' ? 'selected' : '' }}>Operations</option>
                            <option value="Partner" {{ $project->project_type == 'Partner' ? 'selected' : '' }}>Partner Collaborative</option>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label small fw-bold">Priority <span class="text-danger">*</span></label>
                        <select name="priority" class="form-select rounded-3" required>
                            <option value="low" {{ $project->priority == 'low' ? 'selected' : '' }}>Low</option>
                            <option value="medium" {{ $project->priority == 'medium' ? 'selected' : '' }}>Medium</option>
                            <option value="high" {{ $project->priority == 'high' ? 'selected' : '' }}>High</option>
                            <option value="urgent" {{ $project->priority == 'urgent' ? 'selected' : '' }}>Urgent</option>
                            <option value="critical" {{ $project->priority == 'critical' ? 'selected' : '' }}>Critical</option>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label small fw-bold">Status <span class="text-danger">*</span></label>
                        <select name="status" class="form-select rounded-3" required>
                            <option value="planning" {{ $project->status == 'planning' ? 'selected' : '' }}>Planning</option>
                            <option value="active" {{ $project->status == 'active' ? 'selected' : '' }}>Active / In Progress</option>
                            <option value="on_hold" {{ $project->status == 'on_hold' ? 'selected' : '' }}>On Hold</option>
                            <option value="completed" {{ $project->status == 'completed' ? 'selected' : '' }}>Completed</option>
                            <option value="cancelled" {{ $project->status == 'cancelled' ? 'selected' : '' }}>Cancelled</option>
                        </select>
                    </div>

                    <div class="col-md-4">
                        <label class="form-label small fw-bold">Department</label>
                        <select name="department_id" class="form-select select2 rounded-3">
                            <option value="">Select Department (Optional)</option>
                            @foreach($departments as $dept)
                                <option value="{{ $dept->id }}" {{ $project->department_id == $dept->id ? 'selected' : '' }}>{{ $dept->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label small fw-bold">Primary Team</label>
                        <select name="team_id" class="form-select select2 rounded-3">
                            <option value="">Select Team (Optional)</option>
                            @foreach($teams as $team)
                                <option value="{{ $team->id }}" {{ $project->team_id == $team->id ? 'selected' : '' }}>{{ $team->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label small fw-bold">Project Owner / Lead Manager</label>
                        <select name="owner_id" class="form-select select2 rounded-3">
                            <option value="">Select Owner</option>
                            @foreach($staff as $s)
                                <option value="{{ $s->id }}" {{ $project->owner_id == $s->id ? 'selected' : '' }}>{{ $s->name }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="col-md-4">
                        <label class="form-label small fw-bold">Start Date <span class="text-danger">*</span></label>
                        <input type="date" name="start_date" class="form-control rounded-3" value="{{ old('start_date', $project->start_date ? date('Y-m-d', strtotime($project->start_date)) : '') }}" required>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label small fw-bold">Target Due Date</label>
                        <input type="date" name="due_date" class="form-control rounded-3" value="{{ old('due_date', $project->due_date ? date('Y-m-d', strtotime($project->due_date)) : '') }}">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label small fw-bold">Budget (₹)</label>
                        <input type="number" step="0.01" name="budget" class="form-control rounded-3" value="{{ old('budget', $project->budget) }}">
                    </div>

                    @php
                        $selectedInternal = $project->members->pluck('id')->toArray();
                        $selectedExternal = $project->externalMembers->pluck('external_organization_id')->filter()->toArray();
                    @endphp

                    <div class="col-md-6">
                        <label class="form-label small fw-bold">Internal Team Members</label>
                        <select name="internal_members[]" class="form-select select2 rounded-3" multiple>
                            @foreach($staff as $s)
                                <option value="{{ $s->id }}" {{ in_array($s->id, $selectedInternal) ? 'selected' : '' }}>{{ $s->name }} ({{ $s->department->name ?? 'Staff' }})</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label small fw-bold">External Agencies / Associate Partners</label>
                        <select name="external_org_ids[]" class="form-select select2 rounded-3" multiple>
                            @foreach($externalOrgs as $org)
                                <option value="{{ $org->id }}" {{ in_array($org->id, $selectedExternal) ? 'selected' : '' }}>{{ $org->name }} ({{ $org->organization_type }})</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="col-12">
                        <label class="form-label small fw-bold">Project Description</label>
                        <textarea name="description" class="form-control rounded-3" rows="4">{{ old('description', $project->description) }}</textarea>
                    </div>
                </div>

                <div class="text-end mt-4">
                    <a href="{{ route('admin.work_management.projects.show', encrypt($project->id)) }}" class="btn btn-light rounded-pill px-4 me-2">Cancel</a>
                    <button type="submit" class="btn btn-primary rounded-pill px-5 shadow-sm">Save Changes</button>
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

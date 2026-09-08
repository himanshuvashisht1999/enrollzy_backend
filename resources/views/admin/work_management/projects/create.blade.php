@extends('admin.layouts.master')

@section('title', 'Create Project')

@push('css')
<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
@endpush

@section('content')
<div class="container-fluid">
    <div class="card shadow-sm border-0 rounded-4">
        <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center">
            <div>
                <h5 class="m-0 fw-bold text-dark"><i class="fas fa-folder-plus text-primary me-2"></i> Create New Project</h5>
                <small class="text-muted">Define project scope, timelines, internal team allocation and external agency partners</small>
            </div>
            <a href="{{ route('admin.work_management.projects.index') }}" class="btn btn-light btn-sm rounded-pill px-3">
                <i class="fas fa-arrow-left me-1"></i> Back to Projects
            </a>
        </div>
        <div class="card-body p-4">
            <form action="{{ route('admin.work_management.projects.store') }}" method="POST">
                @csrf
                <div class="row g-3">
                    <div class="col-md-8">
                        <label class="form-label small fw-bold">Project Title <span class="text-danger">*</span></label>
                        <input type="text" name="title" class="form-control rounded-3" placeholder="e.g. Website SEO Launch or MBA Admissions Campaign" value="{{ old('title') }}" required>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label small fw-bold">Project Code</label>
                        <input type="text" name="project_code" class="form-control rounded-3" placeholder="Auto-generated if blank (e.g. PRJ-2609-0001)" value="{{ old('project_code') }}">
                    </div>

                    <div class="col-md-3">
                        <label class="form-label small fw-bold">Project Category <span class="text-danger">*</span></label>
                        <select name="category_id" class="form-select select2 rounded-3" required>
                            <option value="">Select Category</option>
                            @foreach($categories as $cat)
                                <option value="{{ $cat->id }}">{{ $cat->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label small fw-bold">Project Type</label>
                        <select name="project_type" class="form-select rounded-3">
                            <option value="Internal" selected>Internal</option>
                            <option value="Client">Client</option>
                            <option value="Marketing">Marketing Campaign</option>
                            <option value="Development">Technology / Dev</option>
                            <option value="Operations">Operations</option>
                            <option value="Partner">Partner Collaborative</option>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label small fw-bold">Priority <span class="text-danger">*</span></label>
                        <select name="priority" class="form-select rounded-3" required>
                            <option value="low">Low</option>
                            <option value="medium" selected>Medium</option>
                            <option value="high">High</option>
                            <option value="urgent">Urgent</option>
                            <option value="critical">Critical</option>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label small fw-bold">Status <span class="text-danger">*</span></label>
                        <select name="status" class="form-select rounded-3" required>
                            <option value="planning">Planning</option>
                            <option value="active" selected>Active / In Progress</option>
                            <option value="on_hold">On Hold</option>
                            <option value="completed">Completed</option>
                            <option value="cancelled">Cancelled</option>
                        </select>
                    </div>

                    <div class="col-md-4">
                        <label class="form-label small fw-bold">Department</label>
                        <select name="department_id" class="form-select select2 rounded-3">
                            <option value="">Select Department (Optional)</option>
                            @foreach($departments as $dept)
                                <option value="{{ $dept->id }}">{{ $dept->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label small fw-bold">Primary Team</label>
                        <select name="team_id" class="form-select select2 rounded-3">
                            <option value="">Select Team (Optional)</option>
                            @foreach($teams as $team)
                                <option value="{{ $team->id }}">{{ $team->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label small fw-bold">Project Owner / Lead Manager</label>
                        <select name="owner_id" class="form-select select2 rounded-3">
                            <option value="">Select Owner</option>
                            @foreach($staff as $s)
                                <option value="{{ $s->id }}">{{ $s->name }} ({{ $s->role ?? 'Staff' }})</option>
                            @endforeach
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
                        <label class="form-label small fw-bold">Budget (₹)</label>
                        <input type="number" step="0.01" name="budget" class="form-control rounded-3" placeholder="e.g. 500000" value="{{ old('budget') }}">
                    </div>

                    <!-- Cross-Functional Roster -->
                    <div class="col-md-6">
                        <label class="form-label small fw-bold">Internal Team Members (Multi-Department)</label>
                        <select name="internal_members[]" class="form-select select2 rounded-3" multiple data-placeholder="Select staff members from any department">
                            @foreach($staff as $s)
                                <option value="{{ $s->id }}">{{ $s->name }} ({{ $s->department->name ?? 'Staff' }})</option>
                            @endforeach
                        </select>
                        <small class="text-muted">Staff can collaborate from across all departments</small>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label small fw-bold">External Agencies / Associate Partners</label>
                        <select name="external_org_ids[]" class="form-select select2 rounded-3" multiple data-placeholder="Select external agencies / partners">
                            @foreach($externalOrgs as $org)
                                <option value="{{ $org->id }}">{{ $org->name }} ({{ $org->organization_type }})</option>
                            @endforeach
                        </select>
                        <small class="text-muted">External partners work with restricted data access</small>
                    </div>

                    <div class="col-12">
                        <label class="form-label small fw-bold">Project Description & Scope</label>
                        <textarea name="description" class="form-control rounded-3" rows="4" placeholder="Objectives, deliverables, milestones outline, and instructions...">{{ old('description') }}</textarea>
                    </div>
                </div>

                <div class="text-end mt-4">
                    <a href="{{ route('admin.work_management.projects.index') }}" class="btn btn-light rounded-pill px-4 me-2">Cancel</a>
                    <button type="submit" class="btn btn-primary rounded-pill px-5 shadow-sm">Create Project</button>
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

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
                        <div class="d-flex justify-content-between align-items-center mb-1">
                            <label class="form-label small fw-bold mb-0">Project Category <span class="text-danger">*</span></label>
                            <button type="button" class="btn btn-link p-0 text-decoration-none small text-primary fw-semibold" data-bs-toggle="modal" data-bs-target="#quickCategoryModal">
                                <i class="fas fa-plus-circle me-1"></i>New
                            </button>
                        </div>
                        <select name="category_id" id="category_select" class="form-select select2 rounded-3" required>
                            @foreach($categories as $cat)
                                <option value="{{ $cat->id }}" {{ old('category_id', $project->category_id) == $cat->id ? 'selected' : '' }}>{{ $cat->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-3">
                        <div class="d-flex justify-content-between align-items-center mb-1">
                            <label class="form-label small fw-bold mb-0">Project Type</label>
                            <button type="button" class="btn btn-link p-0 text-decoration-none small text-primary fw-semibold" data-bs-toggle="modal" data-bs-target="#quickTypeModal">
                                <i class="fas fa-plus-circle me-1"></i>New
                            </button>
                        </div>
                        <select name="project_type_id" id="project_type_select" class="form-select select2 rounded-3">
                            <option value="">Select Type</option>
                            @foreach($projectTypes as $type)
                                <option value="{{ $type->id }}" {{ (old('project_type_id', $project->project_type_id) == $type->id || $project->project_type == $type->name) ? 'selected' : '' }}>
                                    {{ $type->name }}
                                </option>
                            @endforeach
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

                    <!-- Cross-Functional Roster -->
                    <div class="col-md-6">
                        <label class="form-label small fw-bold">Internal Team Members (Multi-Department)</label>
                        @php
                            $assignedInternalIds = $project->members->pluck('id')->toArray();
                        @endphp
                        <select name="internal_members[]" class="form-select select2 rounded-3" multiple>
                            @foreach($staff as $s)
                                <option value="{{ $s->id }}" {{ in_array($s->id, $assignedInternalIds) ? 'selected' : '' }}>{{ $s->name }} ({{ $s->department->name ?? 'Staff' }})</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label small fw-bold">External Agencies / Associate Partners</label>
                        @php
                            $assignedOrgIds = $project->externalMembers->pluck('external_organization_id')->toArray();
                        @endphp
                        <select name="external_org_ids[]" class="form-select select2 rounded-3" multiple>
                            @foreach($externalOrgs as $org)
                                <option value="{{ $org->id }}" {{ in_array($org->id, $assignedOrgIds) ? 'selected' : '' }}>{{ $org->name }} ({{ $org->organization_type }})</option>
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

<!-- Quick Add Category Modal -->
<div class="modal fade" id="quickCategoryModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content rounded-4 border-0 shadow">
            <div class="modal-header border-0 pb-0">
                <h5 class="modal-title fw-bold"><i class="fas fa-tags text-primary me-2"></i> Quick Add Category</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="quickCategoryForm">
                @csrf
                <div class="modal-body py-3">
                    <div class="mb-3">
                        <label class="form-label small fw-bold">Category Name <span class="text-danger">*</span></label>
                        <input type="text" name="name" id="quick_cat_name" class="form-control rounded-3" placeholder="e.g. Digital Marketing, Infrastructure" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label small fw-bold">Description</label>
                        <textarea name="description" id="quick_cat_description" class="form-control rounded-3" rows="2" placeholder="Brief scope description..."></textarea>
                    </div>
                    <div id="quickCatError" class="alert alert-danger d-none py-2 small"></div>
                </div>
                <div class="modal-footer border-0 pt-0">
                    <button type="button" class="btn btn-light rounded-pill px-4" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" id="quickCatSubmitBtn" class="btn btn-primary rounded-pill px-4 shadow-sm">Save & Select</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Quick Add Project Type Modal -->
<div class="modal fade" id="quickTypeModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content rounded-4 border-0 shadow">
            <div class="modal-header border-0 pb-0">
                <h5 class="modal-title fw-bold"><i class="fas fa-layer-group text-primary me-2"></i> Quick Add Project Type</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="quickTypeForm">
                @csrf
                <div class="modal-body py-3">
                    <div class="row g-3">
                        <div class="col-md-8">
                            <label class="form-label small fw-bold">Type Name <span class="text-danger">*</span></label>
                            <input type="text" name="name" id="quick_type_name" class="form-control rounded-3" placeholder="e.g. Research & Dev" required>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label small fw-bold">Code</label>
                            <input type="text" name="code" id="quick_type_code" class="form-control rounded-3" placeholder="e.g. RND">
                        </div>
                        <div class="col-12">
                            <label class="form-label small fw-bold">Color Theme</label>
                            <select name="color" id="quick_type_color" class="form-select rounded-3">
                                <option value="primary" selected>Primary (Blue)</option>
                                <option value="success">Success (Green)</option>
                                <option value="info">Info (Cyan)</option>
                                <option value="warning">Warning (Amber)</option>
                                <option value="danger">Danger (Red)</option>
                                <option value="dark">Dark (Charcoal)</option>
                                <option value="secondary">Secondary (Gray)</option>
                            </select>
                        </div>
                    </div>
                    <div id="quickTypeError" class="alert alert-danger d-none py-2 mt-3 small"></div>
                </div>
                <div class="modal-footer border-0 pt-0">
                    <button type="button" class="btn btn-light rounded-pill px-4" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" id="quickTypeSubmitBtn" class="btn btn-primary rounded-pill px-4 shadow-sm">Save & Select</button>
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

        // Handle Quick Category AJAX
        $('#quickCategoryForm').on('submit', function(e) {
            e.preventDefault();
            var $btn = $('#quickCatSubmitBtn');
            var $err = $('#quickCatError');
            $btn.prop('disabled', true).text('Saving...');
            $err.addClass('d-none').text('');

            $.ajax({
                url: "{{ route('admin.work_management.categories.quick_store') }}",
                type: 'POST',
                data: $(this).serialize(),
                success: function(response) {
                    if (response.success) {
                        var newOption = new Option(response.category.name, response.category.id, true, true);
                        $('#category_select').append(newOption).trigger('change');
                        $('#quickCategoryModal').modal('hide');
                        $('#quickCategoryForm')[0].reset();
                    }
                },
                error: function(xhr) {
                    var msg = 'Failed to create category';
                    if (xhr.responseJSON && xhr.responseJSON.message) {
                        msg = xhr.responseJSON.message;
                    }
                    $err.removeClass('d-none').text(msg);
                },
                complete: function() {
                    $btn.prop('disabled', false).text('Save & Select');
                }
            });
        });

        // Handle Quick Type AJAX
        $('#quickTypeForm').on('submit', function(e) {
            e.preventDefault();
            var $btn = $('#quickTypeSubmitBtn');
            var $err = $('#quickTypeError');
            $btn.prop('disabled', true).text('Saving...');
            $err.addClass('d-none').text('');

            $.ajax({
                url: "{{ route('admin.work_management.types.quick_store') }}",
                type: 'POST',
                data: $(this).serialize(),
                success: function(response) {
                    if (response.success) {
                        var newOption = new Option(response.type.name, response.type.id, true, true);
                        $('#project_type_select').append(newOption).trigger('change');
                        $('#quickTypeModal').modal('hide');
                        $('#quickTypeForm')[0].reset();
                    }
                },
                error: function(xhr) {
                    var msg = 'Failed to create project type';
                    if (xhr.responseJSON && xhr.responseJSON.message) {
                        msg = xhr.responseJSON.message;
                    }
                    $err.removeClass('d-none').text(msg);
                },
                complete: function() {
                    $btn.prop('disabled', false).text('Save & Select');
                }
            });
        });
    });
</script>
@endpush

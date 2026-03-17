@extends('admin.layouts.master')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h4 class="card-title">Edit Department</h4>
                    <a href="{{ route('admin.departments.index', ['organisation_id' => $department->organisation_id, 'campus_id' => $department->campus_id]) }}" class="btn btn-secondary btn-sm float-end">Back</a>
                </div>
                <div class="card-body">
                    @if ($errors->any())
                        <div class="alert alert-danger alert-dismissible fade show" role="alert">
                            <ul class="mb-0">
                                @foreach ($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                        </div>
                    @endif

                    <form action="{{ route('admin.departments.update', $department->id) }}" method="POST">
                        @csrf
                        @method('PUT')
                        
                        <!-- Tabs Navigation -->
                        <ul class="nav nav-tabs mb-3" id="departmentTab" role="tablist">
                            <li class="nav-item" role="presentation">
                                <button class="nav-link active" id="core-tab" data-bs-toggle="tab" data-bs-target="#core" type="button" role="tab" aria-controls="core" aria-selected="true">Core Info</button>
                            </li>
                            <li class="nav-item" role="presentation">
                                <button class="nav-link" id="academic-tab" data-bs-toggle="tab" data-bs-target="#academic" type="button" role="tab" aria-controls="academic" aria-selected="false">Academic</button>
                            </li>
                            <li class="nav-item" role="presentation">
                                <button class="nav-link" id="governance-tab" data-bs-toggle="tab" data-bs-target="#governance" type="button" role="tab" aria-controls="governance" aria-selected="false">Governance</button>
                            </li>
                            <li class="nav-item" role="presentation">
                                <button class="nav-link" id="infrastructure-tab" data-bs-toggle="tab" data-bs-target="#infrastructure" type="button" role="tab" aria-controls="infrastructure" aria-selected="false">Infrastructure</button>
                            </li>
                            <li class="nav-item" role="presentation">
                                <button class="nav-link" id="digital-tab" data-bs-toggle="tab" data-bs-target="#digital" type="button" role="tab" aria-controls="digital" aria-selected="false">Digital & SEO</button>
                            </li>
                            <li class="nav-item" role="presentation">
                                <button class="nav-link" id="settings-tab" data-bs-toggle="tab" data-bs-target="#settings" type="button" role="tab" aria-controls="settings" aria-selected="false">Settings</button>
                            </li>
                        </ul>

                        <div class="tab-content" id="departmentTabContent">
                            
                            <div class="tab-pane fade show active" id="core" role="tabpanel" aria-labelledby="core-tab">
                                <div class="row g-3">
                                    <div class="col-12">
                                        <div class="alert alert-soft-primary border-primary d-flex align-items-center" role="alert">
                                            <i class="fas fa-university me-2 fs-4"></i>
                                            <div>
                                                Editing Department under: <strong>{{ $department->organisation->name ?? 'N/A' }}</strong> - <strong>{{ $department->campus->campus_name ?? 'N/A' }}</strong>
                                            </div>
                                        </div>
                                        <input type="hidden" name="organisation_id" value="{{ $department->organisation_id }}">
                                        <input type="hidden" name="campus_id" value="{{ $department->campus_id }}">
                                    </div>
                                    
                                    <div class="col-md-6">
                                        <label class="form-label">Department Name <span class="text-danger">*</span></label>
                                        <input type="text" name="department_name" class="form-control" value="{{ old('department_name', $department->department_name) }}" required placeholder="e.g. Department of Physics">
                                    </div>
                                    <div class="col-md-3">
                                        <label class="form-label">Department Code</label>
                                        <input type="text" name="department_code" class="form-control" value="{{ old('department_code', $department->department_code) }}" placeholder="e.g. PHY">
                                    </div>
                                    <div class="col-md-3">
                                        <label class="form-label">Slug</label>
                                        <input type="text" name="slug" class="form-control" value="{{ old('slug', $department->slug) }}" placeholder="Auto-generated if empty">
                                    </div>

                                    <div class="col-md-4">
                                        <label class="form-label">Department Type <span class="text-danger">*</span></label>
                                        <select name="department_type" class="form-select" required>
                                            <option value="Academic" {{ old('department_type', $department->department_type) == 'Academic' ? 'selected' : '' }}>Academic</option>
                                            <option value="Clinical" {{ old('department_type', $department->department_type) == 'Clinical' ? 'selected' : '' }}>Clinical</option>
                                            <option value="Research" {{ old('department_type', $department->department_type) == 'Research' ? 'selected' : '' }}>Research</option>
                                            <option value="Interdisciplinary" {{ old('department_type', $department->department_type) == 'Interdisciplinary' ? 'selected' : '' }}>Interdisciplinary</option>
                                        </select>
                                    </div>
                                    <div class="col-md-4">
                                        <label class="form-label">Established Year</label>
                                        <input type="number" name="established_year" class="form-control" value="{{ old('established_year', $department->established_year) }}">
                                    </div>
                                    <div class="col-md-12">
                                        <label class="form-label">About Department</label>
                                        <textarea name="about_department" class="form-control" rows="3">{{ old('about_department', $department->about_department) }}</textarea>
                                    </div>
                                </div>
                            </div>

                            <!-- 2. Academic -->
                            <div class="tab-pane fade" id="academic" role="tabpanel" aria-labelledby="academic-tab">
                                <div class="row g-3">
                                    <h6 class="text-muted">Domain & Scope</h6>
                                    <div class="col-md-4">
                                        <label class="form-label">Discipline Area</label>
                                        <select name="discipline_area" class="form-select">
                                            <option value="">Select Area</option>
                                            <option value="Engineering" {{ old('discipline_area', $department->discipline_area) == 'Engineering' ? 'selected' : '' }}>Engineering</option>
                                            <option value="Medical" {{ old('discipline_area', $department->discipline_area) == 'Medical' ? 'selected' : '' }}>Medical</option>
                                            <option value="Science" {{ old('discipline_area', $department->discipline_area) == 'Science' ? 'selected' : '' }}>Science</option>
                                            <option value="Arts" {{ old('discipline_area', $department->discipline_area) == 'Arts' ? 'selected' : '' }}>Arts</option>
                                            <option value="Commerce" {{ old('discipline_area', $department->discipline_area) == 'Commerce' ? 'selected' : '' }}>Commerce</option>
                                            <option value="Law" {{ old('discipline_area', $department->discipline_area) == 'Law' ? 'selected' : '' }}>Law</option>
                                            <option value="Management" {{ old('discipline_area', $department->discipline_area) == 'Management' ? 'selected' : '' }}>Management</option>
                                        </select>
                                    </div>
                                    <div class="col-md-8">
                                        <label class="form-label">Specializations Supported (Comma separated)</label>
                                        <input type="text" name="specializations_supported" class="form-control" value="{{ is_array(old('specializations_supported', $department->specializations_supported)) ? implode(', ', old('specializations_supported', $department->specializations_supported)) : old('specializations_supported', $department->specializations_supported) }}" placeholder="AI, Data Science, Nuclear Physics">
                                    </div>
                                    <div class="col-md-12">
                                        <label class="form-label d-block">Education Levels Supported</label>
                                        @php $levels = old('education_levels_supported', $department->education_levels_supported) ?? []; @endphp
                                        <div class="form-check form-check-inline">
                                            <input class="form-check-input" type="checkbox" name="education_levels_supported[]" value="UG" id="level_ug" {{ in_array('UG', $levels) ? 'checked' : '' }}>
                                            <label class="form-check-label" for="level_ug">UG</label>
                                        </div>
                                        <div class="form-check form-check-inline">
                                            <input class="form-check-input" type="checkbox" name="education_levels_supported[]" value="PG" id="level_pg" {{ in_array('PG', $levels) ? 'checked' : '' }}>
                                            <label class="form-check-label" for="level_pg">PG</label>
                                        </div>
                                        <div class="form-check form-check-inline">
                                            <input class="form-check-input" type="checkbox" name="education_levels_supported[]" value="Doctoral" id="level_doc" {{ in_array('Doctoral', $levels) ? 'checked' : '' }}>
                                            <label class="form-check-label" for="level_doc">Doctoral</label>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-check form-switch">
                                            <input class="form-check-input" type="checkbox" name="is_interdisciplinary" id="is_interdisciplinary" {{ old('is_interdisciplinary', $department->is_interdisciplinary) ? 'checked' : '' }}>
                                            <label class="form-check-label" for="is_interdisciplinary">Is Interdisciplinary?</label>
                                        </div>
                                    </div>

                                    <h6 class="text-muted mt-4">Academic Output</h6>
                                    <div class="col-md-3">
                                        <label class="form-label">Publications</label>
                                        <input type="number" name="research_publications_count" class="form-control" value="{{ old('research_publications_count', $department->research_publications_count ?? 0) }}">
                                    </div>
                                    <div class="col-md-3">
                                        <label class="form-label">Funded Projects</label>
                                        <input type="number" name="funded_projects_count" class="form-control" value="{{ old('funded_projects_count', $department->funded_projects_count ?? 0) }}">
                                    </div>
                                    <div class="col-md-3">
                                        <label class="form-label">Patents Filed</label>
                                        <input type="number" name="patents_filed_count" class="form-control" value="{{ old('patents_filed_count', $department->patents_filed_count ?? 0) }}">
                                    </div>
                                    <div class="col-md-3">
                                        <label class="form-label">Industry Projects</label>
                                        <input type="number" name="industry_projects_count" class="form-control" value="{{ old('industry_projects_count', $department->industry_projects_count ?? 0) }}">
                                    </div>
                                </div>
                            </div>

                            <!-- 3. Governance -->
                            <div class="tab-pane fade" id="governance" role="tabpanel" aria-labelledby="governance-tab">
                                <div class="row g-3">
                                    <h6 class="text-muted">Leadership</h6>
                                    <div class="col-md-4">
                                        <label class="form-label">HOD Name</label>
                                        <input type="text" name="head_of_department_name" class="form-control" value="{{ old('head_of_department_name', $department->head_of_department_name) }}">
                                    </div>
                                    <div class="col-md-4">
                                        <label class="form-label">HOD Designation</label>
                                        <input type="text" name="head_of_department_designation" class="form-control" value="{{ old('head_of_department_designation', $department->head_of_department_designation) }}">
                                    </div>
                                    <div class="col-md-4">
                                        <label class="form-label">Appointment Type</label>
                                        <select name="hod_appointment_type" class="form-select">
                                            <option value="">Select Type</option>
                                            <option value="Permanent" {{ old('hod_appointment_type', $department->hod_appointment_type) == 'Permanent' ? 'selected' : '' }}>Permanent</option>
                                            <option value="Acting" {{ old('hod_appointment_type', $department->hod_appointment_type) == 'Acting' ? 'selected' : '' }}>Acting</option>
                                        </select>
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label">HOD Email</label>
                                        <input type="email" name="hod_email" class="form-control" value="{{ old('hod_email', $department->hod_email) }}">
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label">Office Contact</label>
                                        <input type="text" name="department_office_contact" class="form-control" value="{{ old('department_office_contact', $department->department_office_contact) }}">
                                    </div>
                                    <div class="col-md-4">
                                        <label class="form-label">Total Faculty Count</label>
                                        <input type="number" name="faculty_count" class="form-control" value="{{ old('faculty_count', $department->faculty_count ?? 0) }}">
                                    </div>

                                    <h6 class="text-muted mt-4">Responsibilities</h6>
                                    @foreach([
                                        'curriculum_design_responsibility' => 'Curriculum Design',
                                        'exam_setting_responsibility' => 'Exam Setting',
                                        'research_programs_managed' => 'Research Programs',
                                        'phd_supervision_available' => 'PhD Supervision',
                                        'industry_collaboration_supported' => 'Industry Collaboration'
                                    ] as $field => $label)
                                    <div class="col-md-4">
                                        <div class="form-check form-switch">
                                            <input class="form-check-input" type="checkbox" name="{{ $field }}" id="{{ $field }}" {{ old($field, $department->$field) ? 'checked' : '' }}>
                                            <label class="form-check-label" for="{{ $field }}">{{ $label }}</label>
                                        </div>
                                    </div>
                                    @endforeach
                                </div>
                            </div>

                            <!-- 4. Infrastructure -->
                            <div class="tab-pane fade" id="infrastructure" role="tabpanel" aria-labelledby="infrastructure-tab">
                                <div class="row g-3">
                                    <div class="col-md-3">
                                        <label class="form-label">Labs Count</label>
                                        <input type="number" name="department_labs_count" class="form-control" value="{{ old('department_labs_count', $department->department_labs_count ?? 0) }}">
                                    </div>
                                    <div class="col-md-3">
                                        <label class="form-label">Research Centers</label>
                                        <input type="number" name="research_centers_under_department" class="form-control" value="{{ old('research_centers_under_department', $department->research_centers_under_department ?? 0) }}">
                                    </div>
                                    <div class="col-md-3">
                                        <label class="form-label">Classrooms</label>
                                        <input type="number" name="classrooms_count" class="form-control" value="{{ old('classrooms_count', $department->classrooms_count ?? 0) }}">
                                    </div>
                                     <div class="col-md-3">
                                        <div class="form-check form-switch mt-4">
                                            <input class="form-check-input" type="checkbox" name="specialized_labs_available" id="specialized_labs_available" {{ old('specialized_labs_available', $department->specialized_labs_available) ? 'checked' : '' }}>
                                            <label class="form-check-label" for="specialized_labs_available">Specialized Labs</label>
                                        </div>
                                        <div class="form-check form-switch">
                                            <input class="form-check-input" type="checkbox" name="department_library_section" id="department_library_section" {{ old('department_library_section', $department->department_library_section) ? 'checked' : '' }}>
                                            <label class="form-check-label" for="department_library_section">Dept Library Section</label>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- 5. Digital & SEO -->
                            <div class="tab-pane fade" id="digital" role="tabpanel" aria-labelledby="digital-tab">
                                <div class="row g-3">
                                    <h6 class="text-muted">Digital Channels</h6>
                                    <div class="col-md-6">
                                        <label class="form-label">Website URL</label>
                                        <input type="url" name="department_website_url" class="form-control" value="{{ old('department_website_url', $department->department_website_url) }}">
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label">Email</label>
                                        <input type="email" name="department_email" class="form-control" value="{{ old('department_email', $department->department_email) }}">
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label">Notice Board URL</label>
                                        <input type="url" name="department_notice_board_url" class="form-control" value="{{ old('department_notice_board_url', $department->department_notice_board_url) }}">
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label">Online Meeting Tools (Comma separated)</label>
                                        <input type="text" name="online_meeting_tools_used" class="form-control" value="{{ is_array(old('online_meeting_tools_used', $department->online_meeting_tools_used)) ? implode(', ', old('online_meeting_tools_used', $department->online_meeting_tools_used)) : old('online_meeting_tools_used', $department->online_meeting_tools_used) }}">
                                    </div>

                                    <h6 class="text-muted mt-4">SEO & Metadata</h6>
                                    <div class="col-md-6">
                                        <label class="form-label">Schema Type</label>
                                        <input type="text" name="schema_type" class="form-control" value="{{ old('schema_type', $department->schema_type ?? 'EducationalOrganization') }}">
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label">Meta Title</label>
                                        <input type="text" name="meta_title" class="form-control" value="{{ old('meta_title', $department->meta_title) }}">
                                    </div>
                                    <div class="col-md-12">
                                        <label class="form-label">Meta Description</label>
                                        <textarea name="meta_description" class="form-control" rows="2">{{ old('meta_description', $department->meta_description) }}</textarea>
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label">Focus Keywords (Comma separated)</label>
                                        <input type="text" name="focus_keywords" class="form-control" value="{{ is_array(old('focus_keywords', $department->focus_keywords)) ? implode(', ', old('focus_keywords', $department->focus_keywords)) : old('focus_keywords', $department->focus_keywords) }}">
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label">Canonical URL</label>
                                        <input type="url" name="canonical_url" class="form-control" value="{{ old('canonical_url', $department->canonical_url) }}">
                                    </div>
                                </div>
                            </div>

                            <!-- 6. Settings -->
                            <div class="tab-pane fade" id="settings" role="tabpanel" aria-labelledby="settings-tab">
                                <div class="row g-3">
                                    <div class="col-md-3">
                                        <label class="form-label">Status</label>
                                        <select name="status" class="form-select">
                                            <option value="Active" {{ old('status', $department->status) == 'Active' ? 'selected' : '' }}>Active</option>
                                            <option value="Inactive" {{ old('status', $department->status) == 'Inactive' ? 'selected' : '' }}>Inactive</option>
                                            <option value="Archived" {{ old('status', $department->status) == 'Archived' ? 'selected' : '' }}>Archived</option>
                                        </select>
                                    </div>
                                    <div class="col-md-3">
                                        <label class="form-label">Visibility</label>
                                        <select name="visibility" class="form-select">
                                            <option value="Public" {{ old('visibility', $department->visibility) == 'Public' ? 'selected' : '' }}>Public</option>
                                            <option value="Internal" {{ old('visibility', $department->visibility) == 'Internal' ? 'selected' : '' }}>Internal</option>
                                        </select>
                                    </div>
                                    <div class="col-md-3">
                                        <label class="form-label">Data Source</label>
                                        <input type="text" name="data_source" class="form-control" value="{{ old('data_source', $department->data_source) }}">
                                    </div>
                                    <div class="col-md-3">
                                        <label class="form-label">Confidence Score</label>
                                        <input type="number" step="0.01" name="confidence_score" class="form-control" value="{{ old('confidence_score', $department->confidence_score) }}">
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="mt-4">
                            <button type="submit" class="btn btn-primary">Update Department</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

@push('scripts')
@endpush
@endsection

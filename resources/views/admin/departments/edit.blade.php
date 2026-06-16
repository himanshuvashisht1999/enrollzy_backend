@extends('admin.layouts.master')

@section('content')

    @push('css')
        <style>
            /* Premium Tab Styles - Standardized */
            .nav-tabs-custom {
                border-bottom: 2px solid #ebeef1;
                background: #fff;
                padding: 0 10px;
                margin-bottom: 25px;
                display: flex;
                flex-wrap: wrap;
                list-style: none;
            }

            .nav-tabs-custom .nav-item {
                margin-bottom: -2px;
            }

            .nav-tabs-custom .nav-link {
                border: none;
                border-bottom: 2px solid transparent;
                color: #495057;
                font-weight: 500;
                padding: 12px 20px;
                transition: all 0.3s;
                text-decoration: none;
                display: block;
                font-size: 14px;
                background: none;
            }

            .nav-tabs-custom .nav-link:hover {
                color: #0d6efd;
                background: rgba(13, 110, 253, 0.05);
            }

            .nav-tabs-custom .nav-link.active {
                color: #0d6efd !important;
                border-bottom: 2px solid #0d6efd;
                background: none;
                font-weight: 600;
            }

            .nav-tabs-custom .nav-link.completed {
                color: #34c38f;
            }

            /* Sticky Footer for Navigation */
            .step-footer {
                position: sticky;
                bottom: 0;
                background: #fff;
                padding: 15px 25px;
                border-top: 1px solid #dee2e6;
                margin: 0 -25px -25px -25px;
                z-index: 99;
                box-shadow: 0 -5px 15px rgba(0, 0, 0, 0.05);
            }

            .tab-pane {
                display: none;
                padding: 20px 0;
            }

            .tab-pane.active {
                display: block;
            }

            #autosave-status {
                font-size: 11px;
                color: #6c757d;
            }
        </style>
    @endpush

    <div class="row">
        <div class="col-12">
            <div class="page-title-box d-sm-flex align-items-center justify-content-between">
                <h4 class="mb-sm-0">Edit Department</h4>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-12">
            @if ($errors->any())
                <div class="alert alert-danger">
                    <ul>
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <form action="{{ route('admin.departments.update', $department->id) }}" id="department-form" method="POST">
                @csrf
                @method('PUT')

                <div class="card">
                    <div class="card-header bg-white border-bottom-0 pb-0">
                        <div class="d-flex align-items-center justify-content-between mb-2">
                            <div>
                                <span class="badge bg-soft-primary text-primary px-3 py-2 rounded-pill">
                                    <i class="fas fa-building me-1"></i> Department Edit
                                </span>
                            </div>
                            <div id="autosave-status" class="small text-muted">
                                <i class="fas fa-check-circle text-success me-1"></i> Auto-saved
                            </div>
                        </div>

                        <ul class="nav nav-tabs-custom" id="departmentTabs" role="tablist">
                            <li class="nav-item">
                                <a class="nav-link active" href="#core" role="tab">Core Info</a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link" href="#academic" role="tab">Academic</a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link" href="#governance" role="tab">Governance</a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link" href="#infrastructure" role="tab">Infrastructure</a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link" href="#digital" role="tab">Digital & SEO</a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link" href="#reviews" role="tab">Reviews</a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link" href="#placement-stats" role="tab">Placement Stats</a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link" href="#settings" role="tab">Settings</a>
                            </li>
                        </ul>
                    </div>

                    <div class="card-body">
                        <div class="tab-content text-muted p-2">
                            <!-- 1. Core Info -->
                            <div class="tab-pane active" id="core" role="tabpanel">
                                <div class="row g-3">
                                    <div class="col-12">
                                        <div class="alert alert-soft-primary border-primary d-flex align-items-center"
                                            role="alert">
                                            <i class="fas fa-university me-2 fs-4"></i>
                                            <div>
                                                Editing Department under:
                                                <strong>{{ $department->organisation->name ?? 'N/A' }}</strong> -
                                                <strong>{{ $department->campus->campus_name ?? 'N/A' }}</strong>
                                            </div>
                                        </div>
                                        <input type="hidden" name="organisation_id"
                                            value="{{ $department->organisation_id }}">
                                        <input type="hidden" name="campus_id" value="{{ $department->campus_id }}">
                                    </div>

                                    <div class="col-md-6">
                                        <label class="form-label">Department Name <span class="text-danger">*</span></label>
                                        <input type="text" name="department_name" class="form-control"
                                            value="{{ old('department_name', $department->department_name) }}" required
                                            placeholder="e.g. Department of Physics">
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
                                        <input type="text" name="department_labs_count" class="form-control" value="{{ old('department_labs_count', $department->department_labs_count ?? 0) }}">
                                    </div>
                                    <div class="col-md-3">
                                        <label class="form-label">Research Centers</label>
                                        <input type="text" name="research_centers_under_department" class="form-control" value="{{ old('research_centers_under_department', $department->research_centers_under_department ?? 0) }}">
                                    </div>
                                    <div class="col-md-3">
                                        <label class="form-label">Classrooms</label>
                                        <input type="text" name="classrooms_count" class="form-control" value="{{ old('classrooms_count', $department->classrooms_count ?? 0) }}">
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

                            <!-- 6. Reviews & Perception -->
                            <div class="tab-pane fade" id="reviews" role="tabpanel" aria-labelledby="reviews-tab">
                                <div class="d-flex align-items-center justify-content-between mb-3">
                                    <h5 class="text-primary mb-0">College Reviews & Perception (Out of 5)</h5>
                                    <button type="button" class="btn btn-sm btn-outline-primary" id="add-review-btn">
                                        <i class="fas fa-plus"></i> Add Review
                                    </button>
                                </div>
                                <div id="reviews-container">
                                    @php 
                                        $existingReviews = old('college_reviews', $department->college_reviews ?? [[]]); 
                                        if (empty($existingReviews)) {
                                            $existingReviews = [[]];
                                        }
                                    @endphp
                                    @foreach($existingReviews as $index => $review)
                                    <div class="review-item border p-3 mb-3 rounded position-relative">
                                        @if($index > 0)
                                        <button type="button" class="btn btn-sm btn-danger position-absolute top-0 end-0 m-2 remove-review-btn"><i class="fas fa-times"></i></button>
                                        @endif
                                        <div class="row g-4 mt-1">
                                            <div class="col-md-6 col-lg-4">
                                                <label class="form-label">College Infrastructure</label>
                                                <div class="input-group">
                                                    <input type="number" step="0.1" min="0" max="5" oninput="if(this.value > 5) this.value = 5; if(this.value < 0) this.value = 0;" name="college_reviews[{{$index}}][infrastructure]" class="form-control" value="{{ $review['infrastructure'] ?? '' }}" placeholder="e.g. 4.5">
                                                    <span class="input-group-text text-warning"><i class="fas fa-star"></i></span>
                                                </div>
                                            </div>
                                            <div class="col-md-6 col-lg-4">
                                                <label class="form-label">Campus Life</label>
                                                <div class="input-group">
                                                    <input type="number" step="0.1" min="0" max="5" oninput="if(this.value > 5) this.value = 5; if(this.value < 0) this.value = 0;" name="college_reviews[{{$index}}][campus_life]" class="form-control" value="{{ $review['campus_life'] ?? '' }}" placeholder="e.g. 4.0">
                                                    <span class="input-group-text text-warning"><i class="fas fa-star"></i></span>
                                                </div>
                                            </div>
                                            <div class="col-md-6 col-lg-4">
                                                <label class="form-label">Academics</label>
                                                <div class="input-group">
                                                    <input type="number" step="0.1" min="0" max="5" oninput="if(this.value > 5) this.value = 5; if(this.value < 0) this.value = 0;" name="college_reviews[{{$index}}][academics]" class="form-control" value="{{ $review['academics'] ?? '' }}" placeholder="e.g. 4.8">
                                                    <span class="input-group-text text-warning"><i class="fas fa-star"></i></span>
                                                </div>
                                            </div>
                                            <div class="col-md-6 col-lg-4">
                                                <label class="form-label">Placements</label>
                                                <div class="input-group">
                                                    <input type="number" step="0.1" min="0" max="5" oninput="if(this.value > 5) this.value = 5; if(this.value < 0) this.value = 0;" name="college_reviews[{{$index}}][placements]" class="form-control" value="{{ $review['placements'] ?? '' }}" placeholder="e.g. 4.2">
                                                    <span class="input-group-text text-warning"><i class="fas fa-star"></i></span>
                                                </div>
                                            </div>
                                            <div class="col-md-6 col-lg-4">
                                                <label class="form-label">Value for Money</label>
                                                <div class="input-group">
                                                    <input type="number" step="0.1" min="0" max="5" oninput="if(this.value > 5) this.value = 5; if(this.value < 0) this.value = 0;" name="college_reviews[{{$index}}][value_for_money]" class="form-control" value="{{ $review['value_for_money'] ?? '' }}" placeholder="e.g. 4.6">
                                                    <span class="input-group-text text-warning"><i class="fas fa-star"></i></span>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    @endforeach
                                </div>
                            </div>
                            <!-- 7. Placement Stats -->
                            <div class="tab-pane fade" id="placement-stats" role="tabpanel" aria-labelledby="placement-stats-tab">
                                <div class="d-flex align-items-center justify-content-between mb-3">
                                    <h5 class="text-primary mb-0">Placement Statistics</h5>
                                    <button type="button" class="btn btn-sm btn-outline-primary" id="add-placement-stat-btn">
                                        <i class="fas fa-plus"></i> Add Year Stats
                                    </button>
                                </div>
                                <div id="placement-stats-container">
                                    @php 
                                        $placementStats = old('placement_statistics', $department->placement_statistics ?? [[]]); 
                                        if (empty($placementStats)) {
                                            $placementStats = [[]];
                                        }
                                    @endphp
                                    @foreach($placementStats as $index => $stat)
                                    <div class="placement-stat-item border p-4 mb-4 rounded position-relative bg-light">
                                        @if($index > 0)
                                        <button type="button" class="btn btn-sm btn-danger position-absolute top-0 end-0 m-2 remove-placement-stat-btn"><i class="fas fa-times"></i></button>
                                        @endif
                                        <div class="row g-3 mb-3">
                                            <div class="col-md-4">
                                                <label class="form-label fw-bold">Year</label>
                                                <input type="number" name="placement_statistics[{{$index}}][year]" class="form-control" value="{{ $stat['year'] ?? '' }}" placeholder="e.g. 2024">
                                            </div>
                                        </div>
                                        <div class="row g-4">
                                            <!-- Department Specific -->
                                            <div class="col-md-6">
                                                <h6 class="text-secondary border-bottom pb-2">Department Specific</h6>
                                                <div class="mb-3">
                                                    <label class="form-label">Total Students Placed</label>
                                                    <input type="number" min="0" name="placement_statistics[{{$index}}][dept_students_placed]" class="form-control" value="{{ $stat['dept_students_placed'] ?? '' }}">
                                                </div>
                                                <div class="mb-3">
                                                    <label class="form-label">Graduating Students</label>
                                                    <input type="number" min="0" name="placement_statistics[{{$index}}][dept_graduating_students]" class="form-control" value="{{ $stat['dept_graduating_students'] ?? '' }}">
                                                </div>
                                                <div class="mb-3">
                                                    <label class="form-label">Placement Percentage (%)</label>
                                                    <div class="input-group">
                                                        <input type="number" step="0.1" min="0" max="100" name="placement_statistics[{{$index}}][dept_placement_percentage]" class="form-control" value="{{ $stat['dept_placement_percentage'] ?? '' }}">
                                                        <span class="input-group-text">%</span>
                                                    </div>
                                                </div>
                                                <div class="mb-3">
                                                    <label class="form-label">Median Salary LPA</label>
                                                    <div class="input-group">
                                                        <span class="input-group-text">₹</span>
                                                        <input type="text" name="placement_statistics[{{$index}}][dept_median_salary]" class="form-control" value="{{ $stat['dept_median_salary'] ?? '' }}" placeholder="e.g. 8.99">
                                                        <span class="input-group-text">LPA</span>
                                                    </div>
                                                </div>
                                                <div class="mb-3">
                                                    <label class="form-label">Students Going Higher Studies</label>
                                                    <input type="number" min="0" name="placement_statistics[{{$index}}][dept_higher_studies]" class="form-control" value="{{ $stat['dept_higher_studies'] ?? '' }}">
                                                </div>
                                            </div>
                                            
                                            <!-- Overall (College level) -->
                                            <div class="col-md-6">
                                                <h6 class="text-secondary border-bottom pb-2">Overall (College Level)</h6>
                                                <div class="mb-3">
                                                    <label class="form-label">Total Students Placed</label>
                                                    <input type="number" min="0" name="placement_statistics[{{$index}}][overall_students_placed]" class="form-control" value="{{ $stat['overall_students_placed'] ?? '' }}">
                                                </div>
                                                <div class="mb-3">
                                                    <label class="form-label">Graduating Students</label>
                                                    <input type="number" min="0" name="placement_statistics[{{$index}}][overall_graduating_students]" class="form-control" value="{{ $stat['overall_graduating_students'] ?? '' }}">
                                                </div>
                                                <div class="mb-3">
                                                    <label class="form-label">Placement Percentage (%)</label>
                                                    <div class="input-group">
                                                        <input type="number" step="0.1" min="0" max="100" name="placement_statistics[{{$index}}][overall_placement_percentage]" class="form-control" value="{{ $stat['overall_placement_percentage'] ?? '' }}">
                                                        <span class="input-group-text">%</span>
                                                    </div>
                                                </div>
                                                <div class="mb-3">
                                                    <label class="form-label">Median Salary LPA</label>
                                                    <div class="input-group">
                                                        <span class="input-group-text">₹</span>
                                                        <input type="text" name="placement_statistics[{{$index}}][overall_median_salary]" class="form-control" value="{{ $stat['overall_median_salary'] ?? '' }}" placeholder="e.g. 8.87">
                                                        <span class="input-group-text">LPA</span>
                                                    </div>
                                                </div>
                                                <div class="mb-3">
                                                    <label class="form-label">Students Going Higher Studies</label>
                                                    <input type="number" min="0" name="placement_statistics[{{$index}}][overall_higher_studies]" class="form-control" value="{{ $stat['overall_higher_studies'] ?? '' }}">
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    @endforeach
                                </div>
                            </div>

                            <!-- 7. Settings -->
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
                        </div> <!-- End Tab Content -->

                        <!-- Footer Navigation -->
                        <div class="step-footer d-flex align-items-center">
                            <button type="button" class="btn btn-primary" id="prevBtn"
                                onclick="nextPrev(-1)">Previous</button>
                            <div class="ms-auto d-flex align-items-center">
                                <span id="save-message" class="text-muted small me-3" style="opacity: 0;">Saving...</span>
                                <button type="button" class="btn btn-primary" id="nextBtn" onclick="nextPrev(1)">Next
                                    Step</button>
                                <button type="submit" class="btn btn-success" id="submitBtn" style="display:none;">Finish &
                                    Save</button>
                            </div>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </div>

@push('js')
    <script>
        $(document).ready(function () {
            // STEP FORM & AUTO SAVE LOGIC
            let currentTab = 0;
            const tabs = $('#departmentTabs .nav-link');
            const departmentId = '{{ $department->id }}';
            let isSaving = false;

            // Allow direct tab clicking
            tabs.each(function (index) {
                $(this).on('click', function (e) {
                    e.preventDefault();
                    if (index > currentTab && !validateCurrentStep()) {
                        return false;
                    }
                    saveStepData();
                    currentTab = index;
                    showTab(currentTab);
                });
            });

            window.nextPrev = function (n) {
                if (n == 1 && !validateCurrentStep()) return false;
                saveStepData();
                currentTab = currentTab + n;
                showTab(currentTab);
            }

            function showTab(n) {
                const bootstrapTab = new bootstrap.Tab(tabs[n]);
                bootstrapTab.show();

                $('#prevBtn').css('display', n == 0 ? 'none' : 'inline');
                if (n == (tabs.length - 1)) {
                    $('#nextBtn').css('display', 'none');
                    $('#submitBtn').css('display', 'inline');
                } else {
                    $('#nextBtn').css('display', 'inline').text('Next Step');
                    $('#submitBtn').css('display', 'none');
                }
            }

            function validateCurrentStep() {
                const currentTabId = $(tabs[currentTab]).attr('href');
                let valid = true;
                $(currentTabId + ' [required]').each(function () {
                    if (!this.checkValidity()) {
                        this.reportValidity();
                        valid = false;
                        return false;
                    }
                });
                return valid;
            }

            function saveStepData() {
                if (isSaving) return;
                const currentTabId = $(tabs[currentTab]).attr('href');

                isSaving = true;
                showAutoSaveStatus('saving');
                $.ajax({
                    url: `/admin/departments/${departmentId}/autosave-tab`,
                    method: 'POST',
                    data: $(currentTabId + ' :input').serialize() + '&_token={{ csrf_token() }}',
                    success: function () {
                        showAutoSaveStatus('saved');
                        $(tabs[currentTab]).addClass('completed');
                    },
                    complete: function () {
                        isSaving = false;
                    }
                });
            }

            function getFieldsInStep(stepId) {
                const fields = [];
                $(`#${stepId} [name]`).each(function () {
                    const name = $(this).attr('name').split('[')[0]; // Get base name
                    if (!fields.includes(name)) fields.push(name);
                });
                return fields;
            }

            function getInputValue(fieldName) {
                const inputs = $(`[name^="${fieldName}"]`);
                if (inputs.length > 1 || (inputs.length === 1 && inputs.attr('name').includes('[]'))) {
                    // Array or multi-select
                    const values = [];
                    inputs.each(function () {
                        if ($(this).is(':checkbox')) {
                            if ($(this).is(':checked')) values.push($(this).val());
                        } else if ($(this).val()) {
                            values.push($(this).val());
                        }
                    });
                    return values;
                }

                const el = $(`[name="${fieldName}"]`);
                if (el.is(':checkbox')) {
                    return el.is(':checked') ? 1 : 0;
                }
                return el.val();
            }

            function showAutoSaveStatus(status) {
                const msgEl = $('#save-message');
                const statusIcon = $('#autosave-status i');
                if (status === 'saving') {
                    msgEl.css('opacity', 1).text('Saving...');
                    statusIcon.removeClass('fa-check-circle text-success').addClass('fa-spinner fa-spin');
                } else {
                    msgEl.text('Saved').animate({ opacity: 0 }, 2000);
                    statusIcon.removeClass('fa-spinner fa-spin').addClass('fa-check-circle text-success');
                }
            }

            showTab(0);
            // Reviews Repeater
            let reviewIndex = {{ count(old('college_reviews', $department->college_reviews ?? [[]])) }};
            $('#add-review-btn').on('click', function() {
                const template = `
                    <div class="review-item border p-3 mb-3 rounded position-relative">
                        <button type="button" class="btn btn-sm btn-danger position-absolute top-0 end-0 m-2 remove-review-btn"><i class="fas fa-times"></i></button>
                        <div class="row g-4 mt-1">
                            <div class="col-md-6 col-lg-4">
                                <label class="form-label">College Infrastructure</label>
                                <div class="input-group">
                                    <input type="number" step="0.1" min="0" max="5" oninput="if(this.value > 5) this.value = 5; if(this.value < 0) this.value = 0;" name="college_reviews[${reviewIndex}][infrastructure]" class="form-control" placeholder="e.g. 4.5">
                                    <span class="input-group-text text-warning"><i class="fas fa-star"></i></span>
                                </div>
                            </div>
                            <div class="col-md-6 col-lg-4">
                                <label class="form-label">Campus Life</label>
                                <div class="input-group">
                                    <input type="number" step="0.1" min="0" max="5" oninput="if(this.value > 5) this.value = 5; if(this.value < 0) this.value = 0;" name="college_reviews[${reviewIndex}][campus_life]" class="form-control" placeholder="e.g. 4.0">
                                    <span class="input-group-text text-warning"><i class="fas fa-star"></i></span>
                                </div>
                            </div>
                            <div class="col-md-6 col-lg-4">
                                <label class="form-label">Academics</label>
                                <div class="input-group">
                                    <input type="number" step="0.1" min="0" max="5" oninput="if(this.value > 5) this.value = 5; if(this.value < 0) this.value = 0;" name="college_reviews[${reviewIndex}][academics]" class="form-control" placeholder="e.g. 4.8">
                                    <span class="input-group-text text-warning"><i class="fas fa-star"></i></span>
                                </div>
                            </div>
                            <div class="col-md-6 col-lg-4">
                                <label class="form-label">Placements</label>
                                <div class="input-group">
                                    <input type="number" step="0.1" min="0" max="5" oninput="if(this.value > 5) this.value = 5; if(this.value < 0) this.value = 0;" name="college_reviews[${reviewIndex}][placements]" class="form-control" placeholder="e.g. 4.2">
                                    <span class="input-group-text text-warning"><i class="fas fa-star"></i></span>
                                </div>
                            </div>
                            <div class="col-md-6 col-lg-4">
                                <label class="form-label">Value for Money</label>
                                <div class="input-group">
                                    <input type="number" step="0.1" min="0" max="5" oninput="if(this.value > 5) this.value = 5; if(this.value < 0) this.value = 0;" name="college_reviews[${reviewIndex}][value_for_money]" class="form-control" placeholder="e.g. 4.6">
                                    <span class="input-group-text text-warning"><i class="fas fa-star"></i></span>
                                </div>
                            </div>
                        </div>
                    </div>
                `;
                $('#reviews-container').append(template);
                reviewIndex++;
            });

            $(document).on('click', '.remove-review-btn', function() {
                $(this).closest('.review-item').remove();
            });
            // Placement Stats Repeater
            let placementStatIndex = {{ count(old('placement_statistics', $department->placement_statistics ?? [[]])) }};
            $(document).on('click', '#add-placement-stat-btn', function() {
                const template = `
                    <div class="placement-stat-item border p-4 mb-4 rounded position-relative bg-light">
                        <button type="button" class="btn btn-sm btn-danger position-absolute top-0 end-0 m-2 remove-placement-stat-btn"><i class="fas fa-times"></i></button>
                        <div class="row g-3 mb-3">
                            <div class="col-md-4">
                                <label class="form-label fw-bold">Year</label>
                                <input type="number" name="placement_statistics[${placementStatIndex}][year]" class="form-control" placeholder="e.g. 2024">
                            </div>
                        </div>
                        <div class="row g-4">
                            <!-- Department Specific -->
                            <div class="col-md-6">
                                <h6 class="text-secondary border-bottom pb-2">Department Specific</h6>
                                <div class="mb-3">
                                    <label class="form-label">Total Students Placed</label>
                                    <input type="number" min="0" name="placement_statistics[${placementStatIndex}][dept_students_placed]" class="form-control">
                                </div>
                                <div class="mb-3">
                                    <label class="form-label">Graduating Students</label>
                                    <input type="number" min="0" name="placement_statistics[${placementStatIndex}][dept_graduating_students]" class="form-control">
                                </div>
                                <div class="mb-3">
                                    <label class="form-label">Placement Percentage (%)</label>
                                    <div class="input-group">
                                        <input type="number" step="0.1" min="0" max="100" name="placement_statistics[${placementStatIndex}][dept_placement_percentage]" class="form-control">
                                        <span class="input-group-text">%</span>
                                    </div>
                                </div>
                                <div class="mb-3">
                                    <label class="form-label">Median Salary LPA</label>
                                    <div class="input-group">
                                        <span class="input-group-text">₹</span>
                                        <input type="text" name="placement_statistics[${placementStatIndex}][dept_median_salary]" class="form-control" placeholder="e.g. 8.99">
                                        <span class="input-group-text">LPA</span>
                                    </div>
                                </div>
                                <div class="mb-3">
                                    <label class="form-label">Students Going Higher Studies</label>
                                    <input type="number" min="0" name="placement_statistics[${placementStatIndex}][dept_higher_studies]" class="form-control">
                                </div>
                            </div>
                            
                            <!-- Overall (College level) -->
                            <div class="col-md-6">
                                <h6 class="text-secondary border-bottom pb-2">Overall (College Level)</h6>
                                <div class="mb-3">
                                    <label class="form-label">Total Students Placed</label>
                                    <input type="number" min="0" name="placement_statistics[${placementStatIndex}][overall_students_placed]" class="form-control">
                                </div>
                                <div class="mb-3">
                                    <label class="form-label">Graduating Students</label>
                                    <input type="number" min="0" name="placement_statistics[${placementStatIndex}][overall_graduating_students]" class="form-control">
                                </div>
                                <div class="mb-3">
                                    <label class="form-label">Placement Percentage (%)</label>
                                    <div class="input-group">
                                        <input type="number" step="0.1" min="0" max="100" name="placement_statistics[${placementStatIndex}][overall_placement_percentage]" class="form-control">
                                        <span class="input-group-text">%</span>
                                    </div>
                                </div>
                                <div class="mb-3">
                                    <label class="form-label">Median Salary LPA</label>
                                    <div class="input-group">
                                        <span class="input-group-text">₹</span>
                                        <input type="text" name="placement_statistics[${placementStatIndex}][overall_median_salary]" class="form-control" placeholder="e.g. 8.87">
                                        <span class="input-group-text">LPA</span>
                                    </div>
                                </div>
                                <div class="mb-3">
                                    <label class="form-label">Students Going Higher Studies</label>
                                    <input type="number" min="0" name="placement_statistics[${placementStatIndex}][overall_higher_studies]" class="form-control">
                                </div>
                            </div>
                        </div>
                    </div>
                `;
                $('#placement-stats-container').append(template);
                placementStatIndex++;
            });

            $(document).on('click', '.remove-placement-stat-btn', function() {
                $(this).closest('.placement-stat-item').remove();
            });
        });
    </script>
@endpush
@endsection

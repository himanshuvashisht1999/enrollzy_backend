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
                <h4 class="mb-sm-0">Add New Department</h4>
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

            <form action="{{ route('admin.departments.store') }}" id="department-form" method="POST">
                @csrf

                <div class="card">
                    <div class="card-header bg-white border-bottom-0 pb-0">
                        <div class="d-flex align-items-center justify-content-between mb-2">
                            <div>
                                <span class="badge bg-soft-primary text-primary px-3 py-2 rounded-pill">
                                    <i class="fas fa-building me-1"></i> Department Setup
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
                                <a class="nav-link" href="#settings" role="tab">Settings</a>
                            </li>
                        </ul>
                    </div>

                    <div class="card-body">
                        <div class="tab-content text-muted p-2">
                            <!-- 1. Core Info -->
                            <div class="tab-pane active" id="core" role="tabpanel">
                                <div class="row g-3">
                                    @if(isset($selectedOrganisation) && isset($selectedCampus))
                                        <div class="col-12">
                                            <div class="alert alert-soft-primary border-primary d-flex align-items-center"
                                                role="alert">
                                                <i class="fas fa-university me-2 fs-4"></i>
                                                <div>
                                                    Creating Department under: <strong>{{ $selectedOrganisation->name }}</strong>
                                                    - <strong>{{ $selectedCampus->campus_name }}</strong>
                                                </div>
                                            </div>
                                            <input type="hidden" name="organisation_id" value="{{ $selectedOrganisation->id }}">
                                            <input type="hidden" name="campus_id" value="{{ $selectedCampus->id }}">
                                        </div>
                                    @else
                                        <div class="col-md-6">
                                            <label class="form-label">Organisation <span class="text-danger">*</span></label>
                                            <select name="organisation_id" id="organisation_id" class="form-select" required>
                                                <option value="">Select Organisation</option>
                                                @foreach($organisations as $org)
                                                    <option value="{{ $org->id }}" {{ (old('organisation_id') == $org->id) ? 'selected' : '' }}>{{ $org->name }}</option>
                                                @endforeach
                                            </select>
                                        </div>
                                        <div class="col-md-6">
                                            <label class="form-label">Campus <span class="text-danger">*</span></label>
                                            <select name="campus_id" id="campus_id" class="form-select" required
                                                {{ old('organisation_id') ? '' : 'disabled' }}>
                                                <option value="">Select Campus</option>
                                                @if(isset($campuses) && !isset($selectedCampus))
                                                    @foreach($campuses as $campus)
                                                        <option value="{{ $campus->id }}" {{ (old('campus_id') == $campus->id) ? 'selected' : '' }}>{{ $campus->campus_name }}</option>
                                                    @endforeach
                                                @endif
                                            </select>
                                        </div>
                                    @endif

                                    <div class="col-md-6">
                                        <label class="form-label">Department Name <span class="text-danger">*</span></label>
                                        <input type="text" name="department_name" class="form-control"
                                            value="{{ old('department_name') }}" required
                                            placeholder="e.g. Department of Physics">
                                    </div>
                                    <div class="col-md-3">
                                        <label class="form-label">Department Code</label>
                                        <input type="text" name="department_code" class="form-control" value="{{ old('department_code') }}" placeholder="e.g. PHY">
                                    </div>
                                    <div class="col-md-3">
                                        <label class="form-label">Slug</label>
                                        <input type="text" name="slug" class="form-control" value="{{ old('slug') }}" placeholder="Auto-generated if empty">
                                    </div>

                                    <div class="col-md-4">
                                        <label class="form-label">Department Type <span class="text-danger">*</span></label>
                                        <select name="department_type" class="form-select" required>
                                            <option value="Academic" {{ old('department_type') == 'Academic' ? 'selected' : '' }}>Academic</option>
                                            <option value="Clinical" {{ old('department_type') == 'Clinical' ? 'selected' : '' }}>Clinical</option>
                                            <option value="Research" {{ old('department_type') == 'Research' ? 'selected' : '' }}>Research</option>
                                            <option value="Interdisciplinary" {{ old('department_type') == 'Interdisciplinary' ? 'selected' : '' }}>Interdisciplinary</option>
                                        </select>
                                    </div>
                                    <div class="col-md-4">
                                        <label class="form-label">Established Year</label>
                                        <input type="number" name="established_year" class="form-control" value="{{ old('established_year') }}">
                                    </div>
                                    <div class="col-md-12">
                                        <label class="form-label">About Department</label>
                                        <textarea name="about_department" class="form-control" rows="3">{{ old('about_department') }}</textarea>
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
                                            <option value="Engineering" {{ old('discipline_area') == 'Engineering' ? 'selected' : '' }}>Engineering</option>
                                            <option value="Medical" {{ old('discipline_area') == 'Medical' ? 'selected' : '' }}>Medical</option>
                                            <option value="Science" {{ old('discipline_area') == 'Science' ? 'selected' : '' }}>Science</option>
                                            <option value="Arts" {{ old('discipline_area') == 'Arts' ? 'selected' : '' }}>Arts</option>
                                            <option value="Commerce" {{ old('discipline_area') == 'Commerce' ? 'selected' : '' }}>Commerce</option>
                                            <option value="Law" {{ old('discipline_area') == 'Law' ? 'selected' : '' }}>Law</option>
                                            <option value="Management" {{ old('discipline_area') == 'Management' ? 'selected' : '' }}>Management</option>
                                        </select>
                                    </div>
                                    <div class="col-md-8">
                                        <label class="form-label">Specializations Supported (Comma separated)</label>
                                        <input type="text" name="specializations_supported" class="form-control" value="{{ old('specializations_supported') }}" placeholder="AI, Data Science, Nuclear Physics">
                                    </div>
                                    <div class="col-md-12">
                                        <label class="form-label d-block">Education Levels Supported</label>
                                        <div class="form-check form-check-inline">
                                            <input class="form-check-input" type="checkbox" name="education_levels_supported[]" value="UG" id="level_ug">
                                            <label class="form-check-label" for="level_ug">UG</label>
                                        </div>
                                        <div class="form-check form-check-inline">
                                            <input class="form-check-input" type="checkbox" name="education_levels_supported[]" value="PG" id="level_pg">
                                            <label class="form-check-label" for="level_pg">PG</label>
                                        </div>
                                        <div class="form-check form-check-inline">
                                            <input class="form-check-input" type="checkbox" name="education_levels_supported[]" value="Doctoral" id="level_doc">
                                            <label class="form-check-label" for="level_doc">Doctoral</label>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-check form-switch">
                                            <input class="form-check-input" type="checkbox" name="is_interdisciplinary" id="is_interdisciplinary" {{ old('is_interdisciplinary') ? 'checked' : '' }}>
                                            <label class="form-check-label" for="is_interdisciplinary">Is Interdisciplinary?</label>
                                        </div>
                                    </div>

                                    <h6 class="text-muted mt-4">Academic Output</h6>
                                    <div class="col-md-3">
                                        <label class="form-label">Publications</label>
                                        <input type="number" name="research_publications_count" class="form-control" value="{{ old('research_publications_count', 0) }}">
                                    </div>
                                    <div class="col-md-3">
                                        <label class="form-label">Funded Projects</label>
                                        <input type="number" name="funded_projects_count" class="form-control" value="{{ old('funded_projects_count', 0) }}">
                                    </div>
                                    <div class="col-md-3">
                                        <label class="form-label">Patents Filed</label>
                                        <input type="number" name="patents_filed_count" class="form-control" value="{{ old('patents_filed_count', 0) }}">
                                    </div>
                                    <div class="col-md-3">
                                        <label class="form-label">Industry Projects</label>
                                        <input type="number" name="industry_projects_count" class="form-control" value="{{ old('industry_projects_count', 0) }}">
                                    </div>
                                </div>
                            </div>

                            <!-- 3. Governance -->
                            <div class="tab-pane fade" id="governance" role="tabpanel" aria-labelledby="governance-tab">
                                <div class="row g-3">
                                    <h6 class="text-muted">Leadership</h6>
                                    <div class="col-md-4">
                                        <label class="form-label">HOD Name</label>
                                        <input type="text" name="head_of_department_name" class="form-control" value="{{ old('head_of_department_name') }}">
                                    </div>
                                    <div class="col-md-4">
                                        <label class="form-label">HOD Designation</label>
                                        <input type="text" name="head_of_department_designation" class="form-control" value="{{ old('head_of_department_designation') }}">
                                    </div>
                                    <div class="col-md-4">
                                        <label class="form-label">Appointment Type</label>
                                        <select name="hod_appointment_type" class="form-select">
                                            <option value="">Select Type</option>
                                            <option value="Permanent" {{ old('hod_appointment_type') == 'Permanent' ? 'selected' : '' }}>Permanent</option>
                                            <option value="Acting" {{ old('hod_appointment_type') == 'Acting' ? 'selected' : '' }}>Acting</option>
                                        </select>
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label">HOD Email</label>
                                        <input type="email" name="hod_email" class="form-control" value="{{ old('hod_email') }}">
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label">Office Contact</label>
                                        <input type="text" name="department_office_contact" class="form-control" value="{{ old('department_office_contact') }}">
                                    </div>
                                    <div class="col-md-4">
                                        <label class="form-label">Total Faculty Count</label>
                                        <input type="number" name="faculty_count" class="form-control" value="{{ old('faculty_count', 0) }}">
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
                                            <input class="form-check-input" type="checkbox" name="{{ $field }}" id="{{ $field }}" {{ old($field) ? 'checked' : '' }}>
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
                                        <input type="text" name="department_labs_count" class="form-control" value="{{ old('department_labs_count', 0) }}">
                                    </div>
                                    <div class="col-md-3">
                                        <label class="form-label">Research Centers</label>
                                        <input type="text" name="research_centers_under_department" class="form-control" value="{{ old('research_centers_under_department', 0) }}">
                                    </div>
                                    <div class="col-md-3">
                                        <label class="form-label">Classrooms</label>
                                        <input type="text" name="classrooms_count" class="form-control" value="{{ old('classrooms_count', 0) }}">
                                    </div>
                                     <div class="col-md-3">
                                        <div class="form-check form-switch mt-4">
                                            <input class="form-check-input" type="checkbox" name="specialized_labs_available" id="specialized_labs_available" {{ old('specialized_labs_available') ? 'checked' : '' }}>
                                            <label class="form-check-label" for="specialized_labs_available">Specialized Labs</label>
                                        </div>
                                        <div class="form-check form-switch">
                                            <input class="form-check-input" type="checkbox" name="department_library_section" id="department_library_section" {{ old('department_library_section') ? 'checked' : '' }}>
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
                                        <input type="url" name="department_website_url" class="form-control" value="{{ old('department_website_url') }}">
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label">Email</label>
                                        <input type="email" name="department_email" class="form-control" value="{{ old('department_email') }}">
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label">Notice Board URL</label>
                                        <input type="url" name="department_notice_board_url" class="form-control" value="{{ old('department_notice_board_url') }}">
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label">Online Meeting Tools (Comma separated)</label>
                                        <input type="text" name="online_meeting_tools_used" class="form-control" value="{{ old('online_meeting_tools_used') }}">
                                    </div>

                                    <h6 class="text-muted mt-4">SEO & Metadata</h6>
                                    <div class="col-md-6">
                                        <label class="form-label">Schema Type</label>
                                        <input type="text" name="schema_type" class="form-control" value="{{ old('schema_type', 'EducationalOrganization') }}">
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label">Meta Title</label>
                                        <input type="text" name="meta_title" class="form-control" value="{{ old('meta_title') }}">
                                    </div>
                                    <div class="col-md-12">
                                        <label class="form-label">Meta Description</label>
                                        <textarea name="meta_description" class="form-control" rows="2">{{ old('meta_description') }}</textarea>
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label">Focus Keywords (Comma separated)</label>
                                        <input type="text" name="focus_keywords" class="form-control" value="{{ old('focus_keywords') }}">
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label">Canonical URL</label>
                                        <input type="url" name="canonical_url" class="form-control" value="{{ old('canonical_url') }}">
                                    </div>
                                </div>
                            </div>

                            <!-- 6. Settings -->
                            <div class="tab-pane fade" id="settings" role="tabpanel" aria-labelledby="settings-tab">
                                <div class="row g-3">
                                    <div class="col-md-3">
                                        <label class="form-label">Status</label>
                                        <select name="status" class="form-select">
                                            <option value="Active" {{ old('status', 'Active') == 'Active' ? 'selected' : '' }}>Active</option>
                                            <option value="Inactive" {{ old('status') == 'Inactive' ? 'selected' : '' }}>Inactive</option>
                                            <option value="Archived" {{ old('status') == 'Archived' ? 'selected' : '' }}>Archived</option>
                                        </select>
                                    </div>
                                    <div class="col-md-3">
                                        <label class="form-label">Visibility</label>
                                        <select name="visibility" class="form-select">
                                            <option value="Public" {{ old('visibility') == 'Public' ? 'selected' : '' }}>Public</option>
                                            <option value="Internal" {{ old('visibility') == 'Internal' ? 'selected' : '' }}>Internal</option>
                                        </select>
                                    </div>
                                    <div class="col-md-3">
                                        <label class="form-label">Data Source</label>
                                        <input type="text" name="data_source" class="form-control" value="{{ old('data_source') }}">
                                    </div>
                                    <div class="col-md-3">
                                        <label class="form-label">Confidence Score</label>
                                        <input type="number" step="0.01" name="confidence_score" class="form-control" value="{{ old('confidence_score') }}">
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
            // organisation context handled elsewhere or usually pre-filled
            @if(!isset($selectedOrganisation))
            $('#organisation_id').on('change', function () {
                const orgId = $(this).val();
                $('#campus_id').html('<option value="">Select Campus</option>').prop('disabled', true);
                if (orgId) {
                    $('#campus_id').prop('disabled', false);
                    $.get(`/admin/organisations/${orgId}/campuses-json`, function (data) {
                        data.forEach(campus => {
                            $('#campus_id').append(`<option value="${campus.id}">${campus.campus_name}</option>`);
                        });
                    });
                }
            });
            @endif

            // STEP FORM & AUTO SAVE LOGIC
            let currentTab = 0;
            const tabs = $('#departmentTabs .nav-link');
            let departmentId = null;
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
                const currentTabId = $(tabs[currentTab]).attr('href').substring(1);

                if (!departmentId && currentTabId === 'core') {
                    // Create Draft
                    const formData = {
                        organisation_id: $('[name="organisation_id"]').val(),
                        campus_id: $('[name="campus_id"]').val(),
                        department_name: $('input[name="department_name"]').val(),
                        department_type: $('select[name="department_type"]').val(),
                        _token: '{{ csrf_token() }}'
                    };

                    if (!formData.department_name || !formData.organisation_id || !formData.campus_id) return;

                    isSaving = true;
                    showAutoSaveStatus('saving');
                    $.ajax({
                        url: '{{ route("admin.departments.store-draft") }}',
                        method: 'POST',
                        data: formData,
                        success: function (response) {
                            departmentId = response.department_id;
                            $(tabs[currentTab]).addClass('completed');
                            showAutoSaveStatus('saved');

                            // Transform form to UPDATE mode
                            const updateUrl = `/admin/departments/${departmentId}`;
                            $('#department-form').attr('action', updateUrl);
                            if ($('#department-form input[name="_method"]').length === 0) {
                                $('#department-form').append('<input type="hidden" name="_method" value="PUT">');
                            }
                        },
                        complete: function () {
                            isSaving = false;
                        }
                    });
                } else if (departmentId) {
                    // Bulk save current tab
                    const formData = {};
                    const fieldsInStep = getFieldsInStep(currentTabId);

                    fieldsInStep.forEach(fieldName => {
                        formData[fieldName] = getInputValue(fieldName);
                    });

                    isSaving = true;
                    showAutoSaveStatus('saving');
                    $.ajax({
                        url: `/admin/departments/${departmentId}/autosave-tab`,
                        method: 'POST',
                        data: {
                            ...formData,
                            _token: '{{ csrf_token() }}'
                        },
                        success: function () {
                            showAutoSaveStatus('saved');
                            $(tabs[currentTab]).addClass('completed');
                        },
                        complete: function () {
                            isSaving = false;
                        }
                    });
                }
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
        });
    </script>
@endpush
@endsection

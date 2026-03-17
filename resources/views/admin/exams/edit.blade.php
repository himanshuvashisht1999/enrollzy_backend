@extends('admin.layouts.master')

@section('title', 'Edit Exam')

@section('content')
    <div class="row justify-content-center">
        <div class="col-md-11">
            @if ($errors->any())
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    <h5 class="alert-heading"><i class="fas fa-exclamation-triangle me-2"></i>Validation Errors</h5>
                    <ul class="mb-0">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            @endif
            <form action="{{ route('admin.exams.update', $exam->id) }}" method="POST" enctype="multipart/form-data">
                @csrf
                @method('PUT')

                <div class="card shadow mb-4">
                    <div class="card-body">
                        <!-- BASIC IDENTITY (always visible, no tab) -->
                        <h5 class="fw-bold mb-3 text-primary">Core Exam Identity</h5>
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label">Exam Name <span class="text-danger">*</span></label>
                                <input type="text" name="name" class="form-control" value="{{ old('name', $exam->name) }}"
                                    required>
                            </div>
                            <div class="col-md-3">
                                <label class="form-label">Short Name</label>
                                <input type="text" name="short_name" class="form-control"
                                    value="{{ old('short_name', $exam->short_name) }}">
                            </div>
                            <div class="col-md-3">
                                <label class="form-label">Exam Type</label>
                                <select name="exam_type" class="form-select select2">
                                    <option value="">Select Type</option>
                                    @foreach(['National', 'State', 'University-Level', 'International', 'School-Level'] as $opt)
                                        <option value="{{ $opt }}" {{ old('exam_type', $exam->exam_type) == $opt ? 'selected' : '' }}>{{ $opt }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-3">
                                <label class="form-label">Exam Category</label>
                                <select name="exam_category[]" class="form-select select2" multiple data-placeholder="Select Category">
                                    @php $selectedCategories = old('exam_category', $exam->exam_category ?? []); @endphp
                                    @foreach(['Engineering', 'Medical', 'Management', 'Law', 'School Admission'] as $opt)
                                        <option value="{{ $opt }}" {{ in_array($opt, is_array($selectedCategories) ? $selectedCategories : []) ? 'selected' : '' }}>{{ $opt }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-3">
                                <label class="form-label">Conducting Body Type</label>
                                <select name="conducting_body_type" class="form-select">
                                    <option value="">Select Body</option>
                                    @foreach(['Government', 'Private Body', 'University'] as $opt)
                                        <option value="{{ $opt }}" {{ old('conducting_body_type', $exam->conducting_body_type) == $opt ? 'selected' : '' }}>{{ $opt }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-3">
                                <label class="form-label">Exam Frequency</label>
                                <select name="exam_frequency" class="form-select">
                                    <option value="">Select Frequency</option>
                                    @foreach(['Once a Year', 'Twice a Year', 'Multiple Times', 'Other'] as $opt)
                                        <option value="{{ $opt }}" {{ old('exam_frequency', $exam->exam_frequency) == $opt ? 'selected' : '' }}>{{ $opt }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Conducting Authority Name</label>
                                <input type="text" name="conducting_authority_name" class="form-control"
                                    value="{{ old('conducting_authority_name', $exam->conducting_authority_name) }}">
                            </div>

                            <div class="col-md-6">
                                <label class="form-label">Exam Logo</label>
                                @if($exam->logo)
                                    <div class="mb-1"><img src="{{ asset($exam->logo) }}" height="40"></div>
                                @endif
                                <input type="file" name="logo" class="form-control" accept="image/*">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Cover Image</label>
                                @if($exam->cover_image)
                                    <div class="mb-1"><img src="{{ asset($exam->cover_image) }}" height="40"></div>
                                @endif
                                <input type="file" name="cover_image" class="form-control" accept="image/*">
                            </div>

                            <div class="col-md-12">
                                <hr>
                                <h6 class="fw-bold">Ownership (Internal vs External)</h6>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">Source Type</label>
                                <select name="exam_source_type" id="exam_source_type" class="form-select">
                                    <option value="External" {{ old('exam_source_type', $exam->exam_source_type) == 'External' ? 'selected' : '' }}>External (General)
                                    </option>
                                    <option value="Internal" {{ old('exam_source_type', $exam->exam_source_type) == 'Internal' ? 'selected' : '' }}>Internal (Owned by
                                        Org)</option>
                                </select>
                            </div>
                            <div class="col-md-8" id="owningOrgWrapper">
                                <label class="form-label">Owning Organisation (If Internal)</label>
                                <select name="owning_organisation_id" id="owning_organisation_id"
                                    class="form-select select2">
                                    <option value="">Select Organisation</option>
                                    @foreach($organisations as $org)
                                        <option value="{{ $org->id }}" {{ old('owning_organisation_id', $exam->owning_organisation_id) == $org->id ? 'selected' : '' }}>
                                            {{ $org->name }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="col-md-12 mt-3">
                                <label class="form-label">About Exam</label>
                                <textarea name="about_exam"
                                    class="form-control editor">{{ old('about_exam', $exam->about_exam) }}</textarea>
                            </div>

                            <div class="col-md-6 mt-3">
                                <label class="form-label">Official Website</label>
                                <input type="url" name="official_website" class="form-control"
                                    value="{{ old('official_website', $exam->official_website) }}">
                            </div>
                            <div class="col-md-3 mt-3">
                                <label class="form-label">Visibility</label>
                                <select name="visibility" class="form-select">
                                    @foreach(['Public', 'Draft', 'Private'] as $opt)
                                        <option value="{{ $opt }}" {{ old('visibility', $exam->visibility) == $opt ? 'selected' : '' }}>{{ $opt }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-3 mt-3">
                                <label class="form-label">Status</label>
                                <select name="status" class="form-select">
                                    @foreach(['Active', 'Upcoming', 'Archived'] as $opt)
                                        <option value="{{ $opt }}" {{ old('status', $exam->status) == $opt ? 'selected' : '' }}>
                                            {{ $opt }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-3 mt-4">
                                <div class="form-check form-switch">
                                    <input class="form-check-input" type="checkbox" name="featured_exam" value="1" {{ old('featured_exam', $exam->featured_exam) ? 'checked' : '' }}>
                                    <label class="form-check-label">Featured Exam</label>
                                </div>
                            </div>
                            <div class="col-md-3 mt-4">
                                <div class="form-check form-switch">
                                    <input class="form-check-input" type="checkbox" name="has_stages" id="has_stages"
                                        value="1" {{ old('has_stages', $exam->has_stages) ? 'checked' : '' }}>
                                    <label class="form-check-label" for="has_stages">Does this exam has stages?</label>
                                </div>
                            </div>
                        </div>

                        <div class="row mt-3" id="stages_selection_wrapper"
                            style="{{ old('has_stages', $exam->has_stages) ? '' : 'display:none;' }}">
                            <div class="col-md-12">
                                <label class="form-label fw-bold">Select Exam Stages <span
                                        class="text-danger">*</span></label>
                                <select name="selected_stages[]" id="selected_stages" class="form-control select2" multiple
                                    data-placeholder="Choose Stages (e.g. Prelims, Mains, Interview)">
                                    @foreach($allStages as $stage)
                                        <option value="{{ $stage->id }}" {{ (in_array($stage->id, old('selected_stages', $selectedStages))) ? 'selected' : '' }}>
                                            {{ $stage->title }}
                                        </option>
                                    @endforeach
                                </select>
                                <div class="form-text text-muted">Select stages in order. Manage detailed data for each
                                    stage from the <a href="{{ route('admin.exams.index') }}">Exam List</a> page after
                                    saving.</div>
                            </div>
                        </div>
                    </div>
                    <div class="card-header py-3">
                        <ul class="nav nav-tabs card-header-tabs" id="examTabs" role="tablist">
                            <li class="nav-item">
                                <button class="nav-link active" id="eligibility-tab" data-bs-toggle="tab"
                                    data-bs-target="#eligibility" type="button">Eligibility & Pattern</button>
                            </li>
                            <li class="nav-item">
                                <button class="nav-link" id="sessions-tab" data-bs-toggle="tab" data-bs-target="#sessions"
                                    type="button">Important Dates</button>
                            </li>
                            <li class="nav-item">
                                <button class="nav-link" id="result-tab" data-bs-toggle="tab" data-bs-target="#result"
                                    type="button">Result, SEO & Cutoffs</button>
                            </li>
                            <li class="nav-item">
                                <button class="nav-link" id="fees-tab" data-bs-toggle="tab" data-bs-target="#fees"
                                    type="button">Application & Fees</button>
                            </li>
                        </ul>
                    </div>
                    <div class="card-body">
                        <div class="tab-content" id="examTabsContent">
                            <!-- TAB: ELIGIBILITY & PATTERN -->
                            <div class="tab-pane fade show active" id="eligibility" role="tabpanel">
                                <h5 class="fw-bold mb-3 text-primary">Eligibility Criteria</h5>
                                <div class="row g-3">
                                    {{-- <div class="col-md-4">
                                        <label class="form-label">Min Qualification</label>
                                        <input type="text" name="minimum_qualification" class="form-control"
                                            value="{{ old('minimum_qualification', $exam->minimum_qualification) }}">
                                    </div>
                                    <div class="col-md-4">
                                        <label class="form-label">Min Marks Required</label>
                                        <input type="text" name="minimum_marks_required" class="form-control"
                                            value="{{ old('minimum_marks_required', $exam->minimum_marks_required) }}">
                                    </div> --}}
                                    <div class="col-md-4">
                                        <label class="form-label">Subjects Required (Json)</label>
                                        <input type="text" name="subjects_required[]" class="form-control"
                                            value="{{ old('subjects_required.0', is_array($exam->subjects_required) ? implode(',', $exam->subjects_required) : $exam->subjects_required) }}">
                                        <small class="text-muted">Enter core subjects.</small>
                                    </div>
                                    {{-- <div class="col-md-3">
                                        <label class="form-label">Min Age</label>
                                        <input type="number" name="minimum_age" class="form-control"
                                            value="{{ old('minimum_age', $exam->minimum_age) }}">
                                    </div>
                                    <div class="col-md-3">
                                        <label class="form-label">Max Age</label>
                                        <input type="number" name="maximum_age" class="form-control"
                                            value="{{ old('maximum_age', $exam->maximum_age) }}">
                                    </div> --}}
                                    <div class="col-md-3">
                                        <label class="form-label">Attempt Limit</label>
                                        <input type="number" name="attempt_limit" class="form-control"
                                            value="{{ old('attempt_limit', $exam->attempt_limit) }}">
                                    </div>
                                    <div class="col-md-3 pt-4">
                                        <div class="form-check">
                                            <input class="form-check-input" type="checkbox" name="gap_year_allowed"
                                                value="1" {{ old('gap_year_allowed', $exam->gap_year_allowed) ? 'checked' : '' }}>
                                            <label class="form-check-label">Gap Year Allowed</label>
                                        </div>
                                    </div>
                                    <div class="col-md-12">
                                        <label class="form-label">Eligibility Notes</label>
                                        <textarea name="eligibility_notes" class="form-control editor"
                                            rows="3">{{ old('eligibility_notes', $exam->eligibility_notes) }}</textarea>
                                    </div>
                                </div>

                                <hr class="my-4">
                                <h5 class="fw-bold mb-3 text-primary">Exam Pattern</h5>
                                <div class="row g-3">
                                    <div class="col-md-3">
                                        <label class="form-label">Mode</label>
                                        <select name="exam_mode" class="form-select">
                                            <option value="Online" {{ old('exam_mode', $exam->exam_mode) == 'Online' ? 'selected' : '' }}>Online (CBT)</option>
                                            <option value="Offline" {{ old('exam_mode', $exam->exam_mode) == 'Offline' ? 'selected' : '' }}>Offline (Pen & Paper)</option>
                                            <option value="Hybrid" {{ old('exam_mode', $exam->exam_mode) == 'Hybrid' ? 'selected' : '' }}>Hybrid</option>
                                        </select>
                                    </div>
                                    <div class="col-md-3">
                                        <label class="form-label">Format</label>
                                        <select name="exam_format" class="form-select">
                                            <option value="MCQ" {{ old('exam_format', $exam->exam_format) == 'MCQ' ? 'selected' : '' }}>MCQ</option>
                                            <option value="Descriptive" {{ old('exam_format', $exam->exam_format) == 'Descriptive' ? 'selected' : '' }}>Descriptive</option>
                                            <option value="Mixed" {{ old('exam_format', $exam->exam_format) == 'Mixed' ? 'selected' : '' }}>Mixed</option>
                                        </select>
                                    </div>
                                    <div class="col-md-3">
                                        <label class="form-label">Total Questions</label>
                                        <input type="number" name="total_questions" class="form-control"
                                            value="{{ old('total_questions', $exam->total_questions) }}">
                                    </div>
                                    <div class="col-md-3">
                                        <label class="form-label">Total Marks</label>
                                        <input type="number" name="total_marks" class="form-control"
                                            value="{{ old('total_marks', $exam->total_marks) }}">
                                    </div>
                                    <div class="col-md-3">
                                        <label class="form-label">Duration (Mins)</label>
                                        <input type="number" name="duration_minutes" class="form-control"
                                            value="{{ old('duration_minutes', $exam->duration_minutes) }}">
                                    </div>
                                    <div class="col-md-3 pt-4">
                                        <div class="form-check">
                                            <input class="form-check-input" type="checkbox" name="negative_marking"
                                                value="1" {{ old('negative_marking', $exam->negative_marking) ? 'checked' : '' }}>
                                            <label class="form-check-label">Negative Marking</label>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label">Marking Scheme</label>
                                        <input type="text" name="negative_marking_scheme" class="form-control"
                                            value="{{ old('negative_marking_scheme', $exam->negative_marking_scheme) }}">
                                    </div>
                                </div>

                                <hr class="my-4">
                                <h5 class="fw-bold mb-3 text-primary">Syllabus</h5>
                                <div class="row g-3">

                                    <div class="col-md-6">
                                        <label class="form-label">Syllabus URL</label>
                                        <input type="url" name="syllabus_url" class="form-control"
                                            value="{{ old('syllabus_url', $exam->syllabus_url) }}">
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label">Difficulty Level</label>
                                        <select name="difficulty_level" class="form-select">
                                            @foreach(['Easy', 'Moderate', 'Hard', 'Very Hard'] as $lvl)
                                                <option value="{{ $lvl }}" {{ old('difficulty_level', $exam->difficulty_level) == $lvl ? 'selected' : '' }}>{{ $lvl }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label">Syllabus Source</label>
                                        <textarea type="text" name="syllabus_source" class="form-control editor"
                                            rows="3">{{ old('syllabus_source', $exam->syllabus_source) }}</textarea>
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label">Subjects Covered (Comma separated)</label>
                                        <textarea type="text" name="subjects_covered_raw" class="form-control editor"
                                            rows="3">{{ old('subjects_covered_raw', is_array($exam->subjects_covered) ? implode(', ', $exam->subjects_covered) : $exam->subjects_covered) }}</textarea>
                                    </div>
                                </div>
                            </div>


                            <!-- TAB 4: IMPORTANT DATES -->
                            <div class="tab-pane fade" id="sessions" role="tabpanel">

                                <h5 class="fw-bold mb-3 text-primary">Exam Sessions & Dates</h5>
                                <div id="sessions-container">
                                    @forelse($exam->sessions as $index => $session)
                                        <div class="card mb-3 session-row bg-light border">
                                            <div class="card-body">
                                                <div class="d-flex justify-content-between mb-2">
                                                    <h6 class="fw-bold">Session {{ $session->academic_year }}</h6>
                                                    <button type="button" class="btn btn-sm btn-danger remove-session-btn"><i
                                                            class="fas fa-trash"></i> Remove</button>
                                                </div>
                                                <input type="hidden" name="sessions[{{ $index }}][id]"
                                                    value="{{ $session->id }}">
                                                <div class="row g-2">
                                                    <div class="col-md-2">
                                                        <label class="small">Year</label>
                                                        <input type="text" class="form-control form-control-sm"
                                                            name="sessions[{{ $index }}][academic_year]"
                                                            value="{{ $session->academic_year }}" required>
                                                    </div>
                                                    <div class="col-md-2">
                                                        <label class="small">Session Name</label>
                                                        <input type="text" class="form-control form-control-sm"
                                                            name="sessions[{{ $index }}][session_name]"
                                                            value="{{ $session->session_name }}">
                                                    </div>
                                                    <div class="col-md-2">
                                                        <label class="small">Status</label>
                                                        <select class="form-select form-select-sm"
                                                            name="sessions[{{ $index }}][status]">
                                                            <option value="Upcoming" {{ $session->status == 'Upcoming' ? 'selected' : '' }}>Upcoming</option>
                                                            <option value="Ongoing" {{ $session->status == 'Ongoing' ? 'selected' : '' }}>Ongoing</option>
                                                            <option value="Completed" {{ $session->status == 'Completed' ? 'selected' : '' }}>Completed</option>
                                                        </select>
                                                    </div>
                                                    <!-- Dates -->
                                                    <div class="col-md-3">
                                                        <label class="small">App. Start</label>
                                                        <input type="date" class="form-control form-control-sm"
                                                            name="sessions[{{ $index }}][application_start_date]"
                                                            value="{{ $session->application_start_date?->format('Y-m-d') }}">
                                                    </div>
                                                    <div class="col-md-3">
                                                        <label class="small">App. End</label>
                                                        <input type="date" class="form-control form-control-sm"
                                                            name="sessions[{{ $index }}][application_end_date]"
                                                            value="{{ $session->application_end_date?->format('Y-m-d') }}">
                                                    </div>

                                                    <!-- Admit Card -->
                                                    <div class="col-md-3">
                                                        <label class="small">Admit Card Date</label>
                                                        <input type="date" class="form-control form-control-sm"
                                                            name="sessions[{{ $index }}][admit_card_release_date]"
                                                            value="{{ $session->admit_card_release_date?->format('Y-m-d') }}">
                                                    </div>
                                                    <div class="col-md-3">
                                                        <label class="small">Admit Card URL</label>
                                                        <input type="url" class="form-control form-control-sm"
                                                            name="sessions[{{ $index }}][admit_card_url]"
                                                            value="{{ $session->admit_card_url }}" placeholder="https://...">
                                                    </div>

                                                    <!-- Exam Dates -->
                                                    <div class="col-md-3">
                                                        <label class="small">Exam Start Date</label>
                                                        <input type="date" class="form-control form-control-sm"
                                                            name="sessions[{{ $index }}][exam_start_date]"
                                                            value="{{ $session->exam_start_date?->format('Y-m-d') }}">
                                                    </div>
                                                    <div class="col-md-3">
                                                        <label class="small">Exam End Date</label>
                                                        <input type="date" class="form-control form-control-sm"
                                                            name="sessions[{{ $index }}][exam_end_date]"
                                                            value="{{ $session->exam_end_date?->format('Y-m-d') }}">
                                                    </div>

                                                    <!-- Result -->
                                                    <div class="col-md-3">
                                                        <label class="small">Result Date</label>
                                                        <input type="date" class="form-control form-control-sm"
                                                            name="sessions[{{ $index }}][result_declaration_date]"
                                                            value="{{ $session->result_declaration_date?->format('Y-m-d') }}">
                                                    </div>
                                                    <div class="col-md-12">
                                                        <label class="small">Result URL</label>
                                                        <input type="url" class="form-control form-control-sm"
                                                            name="sessions[{{ $index }}][result_url]"
                                                            value="{{ $session->result_url }}" placeholder="https://...">
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    @empty
                                        <p class="text-muted small" id="no-sessions-msg">No sessions found.</p>
                                    @endforelse
                                </div>

                                <button type="button" class="btn btn-outline-primary mt-2" id="add-session-btn">
                                    <i class="fas fa-plus me-1"></i> Add Exam Session
                                </button>
                                <hr>
                                <h5 class="fw-bold mb-3 text-primary">Important Procedures</h5>
                                <div class="row g-3 mb-4">
                                    <div class="col-md-6">
                                        <label class="form-label">How to Download Admit Card</label>
                                        <textarea name="admit_card_download_procedure" class="form-control editor"
                                            rows="3">{{ old('admit_card_download_procedure', $exam->admit_card_download_procedure) }}</textarea>
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label">How to Check Result</label>
                                        <textarea name="result_check_procedure" class="form-control editor"
                                            rows="3">{{ old('result_check_procedure', $exam->result_check_procedure) }}</textarea>
                                    </div>
                                </div>

                                <!-- Hidden Template -->
                                <template id="session-template">
                                    <div class="card mb-3 session-row bg-light border">
                                        <div class="card-body">
                                            <div class="d-flex justify-content-between mb-2">
                                                <h6 class="fw-bold">New Session</h6>
                                                <button type="button" class="btn btn-sm btn-danger remove-session-btn"><i
                                                        class="fas fa-trash"></i> Remove</button>
                                            </div>
                                            <div class="row g-2">
                                                <div class="col-md-2">
                                                    <label class="small">Year</label>
                                                    <input type="text" class="form-control form-control-sm"
                                                        name="sessions[INDEX][academic_year]" placeholder="e.g. 2024-25"
                                                        required>
                                                </div>
                                                <div class="col-md-2">
                                                    <label class="small">Session Name</label>
                                                    <input type="text" class="form-control form-control-sm"
                                                        name="sessions[INDEX][session_name]" placeholder="Jan Session">
                                                </div>
                                                <div class="col-md-2">
                                                    <label class="small">Status</label>
                                                    <select class="form-select form-select-sm"
                                                        name="sessions[INDEX][status]">
                                                        <option value="Upcoming">Upcoming</option>
                                                        <option value="Ongoing">Ongoing</option>
                                                        <option value="Completed">Completed</option>
                                                    </select>
                                                </div>
                                                <!-- Dates -->
                                                <div class="col-md-3">
                                                    <label class="small">App. Start</label>
                                                    <input type="date" class="form-control form-control-sm"
                                                        name="sessions[INDEX][application_start_date]">
                                                </div>
                                                <div class="col-md-3">
                                                    <label class="small">App. End</label>
                                                    <input type="date" class="form-control form-control-sm"
                                                        name="sessions[INDEX][application_end_date]">
                                                </div>

                                                <!-- Admit Card -->
                                                <div class="col-md-3">
                                                    <label class="small">Admit Card Date</label>
                                                    <input type="date" class="form-control form-control-sm"
                                                        name="sessions[INDEX][admit_card_release_date]">
                                                </div>
                                                <div class="col-md-3">
                                                    <label class="small">Admit Card URL</label>
                                                    <input type="url" class="form-control form-control-sm"
                                                        name="sessions[INDEX][admit_card_url]" placeholder="https://...">
                                                </div>

                                                <!-- Exam Dates -->
                                                <div class="col-md-3">
                                                    <label class="small">Exam Start Date</label>
                                                    <input type="date" class="form-control form-control-sm"
                                                        name="sessions[INDEX][exam_start_date]">
                                                </div>
                                                <div class="col-md-3">
                                                    <label class="small">Exam End Date</label>
                                                    <input type="date" class="form-control form-control-sm"
                                                        name="sessions[INDEX][exam_end_date]">
                                                </div>

                                                <!-- Result -->
                                                <div class="col-md-3">
                                                    <label class="small">Result Date</label>
                                                    <input type="date" class="form-control form-control-sm"
                                                        name="sessions[INDEX][result_declaration_date]">
                                                </div>
                                                <div class="col-md-12">
                                                    <label class="small">Result URL</label>
                                                    <input type="url" class="form-control form-control-sm"
                                                        name="sessions[INDEX][result_url]" placeholder="https://...">
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </template>
                            </div>

                            <!-- TAB 5: RESULT & SEO -->
                            <div class="tab-pane fade" id="result" role="tabpanel">
                                <h5 class="fw-bold mb-3 text-primary">Result & Scoring</h5>
                                <!-- Result Scoring content -->
                            </div>

                            <!-- TAB 5: APPLICATION & FEES -->
                            <div class="tab-pane fade" id="fees" role="tabpanel">
                                <h5 class="fw-bold mb-3 text-primary">Registration Fee Configuration</h5>
                                <div class="row g-3">
                                    <div class="col-md-12">
                                        <div class="form-check form-switch mb-3">
                                            <input class="form-check-input" type="checkbox" name="registration_fee_required"
                                                id="registration_fee_required" value="1" {{ old('registration_fee_required', $exam->registration_fee_required) ? 'checked' : '' }}>
                                            <label class="form-check-label" for="registration_fee_required">Registration Fee
                                                Required</label>
                                        </div>
                                    </div>
                                </div>

                                <div id="registration_fee_section"
                                    style="{{ old('registration_fee_required', $exam->registration_fee_required) ? '' : 'display:none;' }}">
                                    <div id="registration-fee-container">
                                        @php
                                            $regFees = old('registration_fee_structure', $exam->registration_fee_structure ?? [[]]);
                                        @endphp
                                        @foreach($regFees as $i => $fee)
                                            <div class="card border mb-2 registration-fee-item">
                                                <div class="card-body p-3">
                                                    <div class="row g-2">
                                                        <div class="col-md-3">
                                                             <label class="small">Categories</label>
                                                             <select name="registration_fee_structure[{{$i}}][categories][]"
                                                                 class="form-select form-select-sm select2-category" multiple
                                                                 data-placeholder="Select Categories">
                                                                  @foreach($casteCategories as $cat)
                                                                     <option value="{{$cat->name}}" {{ (isset($fee['categories']) && in_array($cat->name, $fee['categories'])) ? 'selected' : '' }}>
                                                                         {{$cat->name}}
                                                                     </option>
                                                                  @endforeach
                                                             </select>
                                                        </div>
                                                        <div class="col-md-3">
                                                            <label class="small">Amount</label>
                                                            <input type="number"
                                                                name="registration_fee_structure[{{$i}}][amount]"
                                                                class="form-control form-control-sm"
                                                                value="{{ $fee['amount'] ?? '' }}">
                                                        </div>
                                                        <div class="col-md-2">
                                                            <label class="small">Currency</label>
                                                            <input type="text"
                                                                name="registration_fee_structure[{{$i}}][currency]"
                                                                class="form-control form-control-sm"
                                                                value="{{ $fee['currency'] ?? 'INR' }}">
                                                        </div>
                                                        <div class="col-md-2">
                                                            <label class="small">Refundable</label>
                                                            <select name="registration_fee_structure[{{$i}}][refundable]"
                                                                class="form-select form-select-sm">
                                                                <option value="No" {{ (isset($fee['refundable']) && $fee['refundable'] == 'No') ? 'selected' : '' }}>No</option>
                                                                <option value="Yes" {{ (isset($fee['refundable']) && $fee['refundable'] == 'Yes') ? 'selected' : '' }}>Yes</option>
                                                            </select>
                                                        </div>
                                                        <div class="col-md-2 d-flex align-items-end">
                                                            <button type="button"
                                                                class="btn btn-sm btn-danger remove-reg-fee w-100 {{ $i == 0 ? 'disabled' : '' }}">Remove</button>
                                                        </div>
                                                        <div class="col-md-12 mt-2">
                                                            <input type="text"
                                                                name="registration_fee_structure[{{$i}}][remarks]"
                                                                class="form-control form-control-sm" placeholder="Remarks"
                                                                value="{{ $fee['remarks'] ?? '' }}">
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        @endforeach
                                    </div>
                                    <button type="button" class="btn btn-sm btn-outline-primary mb-4" id="add-reg-fee">+ Add
                                        Fee Row</button>
                                </div>

                                <hr>
                                <h5 class="fw-bold mb-3 text-danger">Late Application / Penalty Fees</h5>
                                <div class="form-check form-switch mb-3">
                                    <input class="form-check-input" type="checkbox" name="late_registration_allowed"
                                        id="late_registration_allowed" value="1" {{ old('late_registration_allowed', $exam->late_registration_allowed) ? 'checked' : '' }}>
                                    <label class="form-check-label" for="late_registration_allowed">Late Registration
                                        Allowed with Penalty</label>
                                </div>

                                <div id="late_fee_section"
                                    style="{{ old('late_registration_allowed', $exam->late_registration_allowed) ? '' : 'display:none;' }}">
                                    <div id="late-fee-container">
                                        @php
                                            $lateFees = old('late_fee_rules', $exam->late_fee_rules ?? [[]]);
                                        @endphp
                                        @foreach($lateFees as $i => $rule)
                                            <div class="card border mb-2 late-fee-item bg-light">
                                                <div class="card-body p-3">
                                                    <div class="row g-2">
                                                        <div class="col-md-3">
                                                            <label class="small">Condition</label>
                                                            <select name="late_fee_rules[{{$i}}][condition]"
                                                                class="form-select form-select-sm">
                                                                @foreach(['Late registration', 'Missed reporting', 'Choice not locked'] as $cond)
                                                                    <option value="{{$cond}}" {{ (isset($rule['condition']) && $rule['condition'] == $cond) ? 'selected' : '' }}>{{$cond}}
                                                                    </option>
                                                                @endforeach
                                                            </select>
                                                        </div>
                                                        <div class="col-md-2">
                                                            <label class="small">Type</label>
                                                            <select name="late_fee_rules[{{$i}}][penalty_type]"
                                                                class="form-select form-select-sm">
                                                                <option value="Flat" {{ (isset($rule['penalty_type']) && $rule['penalty_type'] == 'Flat') ? 'selected' : '' }}>Flat
                                                                </option>
                                                                <option value="Percentage" {{ (isset($rule['penalty_type']) && $rule['penalty_type'] == 'Percentage') ? 'selected' : '' }}>
                                                                    Percentage</option>
                                                            </select>
                                                        </div>
                                                        <div class="col-md-2">
                                                            <label class="small">Amount</label>
                                                            <input type="number" name="late_fee_rules[{{$i}}][penalty_amount]"
                                                                class="form-control form-control-sm"
                                                                value="{{ $rule['penalty_amount'] ?? '' }}">
                                                        </div>
                                                        <div class="col-md-2">
                                                            <label class="small">Cap</label>
                                                            <input type="number"
                                                                name="late_fee_rules[{{$i}}][maximum_penalty_cap]"
                                                                class="form-control form-control-sm" placeholder="Max Cap"
                                                                value="{{ $rule['maximum_penalty_cap'] ?? '' }}">
                                                        </div>
                                                        <div class="col-md-3 d-flex align-items-end">
                                                            <button type="button"
                                                                class="btn btn-sm btn-danger remove-late-fee w-100 {{ $i == 0 ? 'disabled' : '' }}">Remove</button>
                                                        </div>
                                                        <div class="col-md-12 mt-2">
                                                            <input type="text" name="late_fee_rules[{{$i}}][remarks]"
                                                                class="form-control form-control-sm" placeholder="Remarks"
                                                                value="{{ $rule['remarks'] ?? '' }}">
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        @endforeach
                                    </div>
                                    <button type="button" class="btn btn-sm btn-outline-danger mb-4" id="add-late-fee">+ Add
                                        Late Fee Rule</button>
                                </div>

                                <hr>
                                <h5 class="fw-bold mb-3 text-warning">Security Deposit Rules</h5>
                                <div class="form-check form-switch mb-3">
                                    <input class="form-check-input" type="checkbox" name="security_deposit_required"
                                        id="security_deposit_required" value="1" {{ old('security_deposit_required', $exam->security_deposit_required) ? 'checked' : '' }}>
                                    <label class="form-check-label" for="security_deposit_required">Security Deposit
                                        Required</label>
                                </div>

                                <div id="security_deposit_section"
                                    style="{{ old('security_deposit_required', $exam->security_deposit_required) ? '' : 'display:none;' }}">
                                    <div id="security-deposit-container">
                                        @php
                                            $sdRules = old('security_deposit_structure', $exam->security_deposit_structure ?? [[]]);
                                        @endphp
                                        @foreach($sdRules as $i => $rule)
                                            <div class="card border mb-2 security-deposit-item">
                                                <div class="card-body p-3">
                                                    <div class="row g-2">
                                                        <div class="col-md-2">
                                                             <label class="small">Categories</label>
                                                              <select
                                                                 name="security_deposit_structure[{{$i}}][candidate_categories][]"
                                                                 class="form-select form-select-sm select2-category" multiple
                                                                 data-placeholder="Select Categories">
                                                                  @foreach($casteCategories as $cat)
                                                                     <option value="{{$cat->name}}" {{ (isset($rule['candidate_categories']) && in_array($cat->name, $rule['candidate_categories'])) ? 'selected' : '' }}>
                                                                         {{$cat->name}}
                                                                     </option>
                                                                  @endforeach
                                                             </select>
                                                        </div>
                                                        <div class="col-md-2">
                                                            <label class="small">College Type</label>
                                                            <select name="security_deposit_structure[{{$i}}][college_type]"
                                                                class="form-select form-select-sm">
                                                                @foreach(['Government', 'Private', 'Deemed'] as $ct)
                                                                    <option value="{{$ct}}" {{ (isset($rule['college_type']) && $rule['college_type'] == $ct) ? 'selected' : '' }}>{{$ct}}
                                                                    </option>
                                                                @endforeach
                                                            </select>
                                                        </div>
                                                        <div class="col-md-2">
                                                            <label class="small">Quota</label>
                                                            <select name="security_deposit_structure[{{$i}}][quota_type]"
                                                                class="form-select form-select-sm">
                                                                @foreach(['All India', 'State'] as $qt)
                                                                    <option value="{{$qt}}" {{ (isset($rule['quota_type']) && $rule['quota_type'] == $qt) ? 'selected' : '' }}>{{$qt}}
                                                                    </option>
                                                                @endforeach
                                                            </select>
                                                        </div>
                                                        <div class="col-md-2">
                                                            <label class="small">Amount</label>
                                                            <input type="number"
                                                                name="security_deposit_structure[{{$i}}][amount]"
                                                                class="form-control form-control-sm"
                                                                value="{{ $rule['amount'] ?? '' }}">
                                                        </div>
                                                        <div class="col-md-2">
                                                            <label class="small">Refundable</label>
                                                            <select name="security_deposit_structure[{{$i}}][refundable]"
                                                                class="form-select form-select-sm">
                                                                <option value="Yes" {{ (isset($rule['refundable']) && $rule['refundable'] == 'Yes') ? 'selected' : '' }}>Yes
                                                                </option>
                                                                <option value="No" {{ (isset($rule['refundable']) && $rule['refundable'] == 'No') ? 'selected' : '' }}>No</option>
                                                            </select>
                                                        </div>
                                                        <div class="col-md-2 d-flex align-items-end">
                                                            <button type="button"
                                                                class="btn btn-sm btn-danger remove-sd w-100 {{ $i == 0 ? 'disabled' : '' }}">Remove</button>
                                                        </div>
                                                        <div class="col-md-6 mt-2">
                                                            <input type="text"
                                                                name="security_deposit_structure[{{$i}}][refund_conditions]"
                                                                class="form-control form-control-sm"
                                                                placeholder="Refund Conditions"
                                                                value="{{ $rule['refund_conditions'] ?? '' }}">
                                                        </div>
                                                        <div class="col-md-6 mt-2">
                                                            <input type="text"
                                                                name="security_deposit_structure[{{$i}}][forfeiture_conditions]"
                                                                class="form-control form-control-sm"
                                                                placeholder="Forfeiture Conditions"
                                                                value="{{ $rule['forfeiture_conditions'] ?? '' }}">
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        @endforeach
                                    </div>
                                    <button type="button" class="btn btn-sm btn-outline-warning mb-4" id="add-sd">+ Add
                                        Security Deposit Rule</button>
                                </div>


                                <hr>
                                <h5 class="fw-bold mb-3 text-primary">Payment & Transaction Rules</h5>
                                <div class="row g-3">
                                    <div class="col-md-12">
                                        <label class="form-label">Allowed Payment Modes</label>
                                        <select name="payment_modes_allowed[]" class="form-select select2" multiple>
                                            @foreach(['Debit Card', 'Credit Card', 'Net Banking', 'UPI', 'Wallet'] as $pm)
                                                <option value="{{ $pm }}" {{ in_array($pm, old('payment_modes_allowed', $exam->payment_modes_allowed ?? [])) ? 'selected' : '' }}>{{ $pm }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="form-check form-switch mt-4">
                                            <input class="form-check-input" type="checkbox"
                                                name="transaction_charges_applicable" id="tx_charges" value="1" {{ old('transaction_charges_applicable', $exam->transaction_charges_applicable) ? 'checked' : '' }}>
                                            <label class="form-check-label" for="tx_charges">Transaction Charges
                                                Applicable</label>
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <label class="form-label">Charges Borne By</label>
                                        <select name="transaction_charge_borne_by" class="form-select">
                                            <option value="Candidate" {{ old('transaction_charge_borne_by', $exam->transaction_charge_borne_by) == 'Candidate' ? 'selected' : '' }}>
                                                Candidate</option>
                                            <option value="Authority" {{ old('transaction_charge_borne_by', $exam->transaction_charge_borne_by) == 'Authority' ? 'selected' : '' }}>
                                                Authority</option>
                                        </select>
                                    </div>
                                    <div class="col-md-4">
                                        <label class="form-label">Payment Gateway Name</label>
                                        <input type="text" name="payment_gateway_name" class="form-control"
                                            placeholder="e.g. Razorpay, BillDesk"
                                            value="{{ old('payment_gateway_name', $exam->payment_gateway_name) }}">
                                    </div>
                                </div>
                            </div>

                        </div>
                    </div>
                </div>

                <div class="card-footer bg-white text-end py-3">
                    <button type="submit" class="btn btn-primary px-4"><i class="fas fa-save me-2"></i> Update
                        Exam</button>
                </div>
        </div>
        </form>
    </div>
    </div>

    <script>
        document.getElementById('has_stages').addEventListener('change', function () {
            const wrapper = document.getElementById('stages_selection_wrapper');
            wrapper.style.display = this.checked ? 'flex' : 'none';
        });
    </script>
@endsection

@push('css')
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
    <style>
        .select2-container .select2-selection--single,
        .select2-container--default .select2-selection--multiple {
            min-height: 38px;
            border: 1px solid #dee2e6;
        }
    </style>
    <script src="https://cdn.ckeditor.com/ckeditor5/36.0.1/classic/ckeditor.js"></script>
@endpush

@push('js')
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
    <script>
        $(document).ready(function () {
            $('.select2').select2({ width: '100%' });
            $('.select2-tags').select2({
                tags: true,
                tokenSeparators: [','],
                width: '100%'
            });

            // CKEditor
            document.querySelectorAll('.editor').forEach(elem => {
                ClassicEditor.create(elem).catch(error => console.error(error));
            });

            // Generic Repeater Logic
            function setupRepeater(config) {
                let index = config.initialIndex;
                $(config.addButton).on('click', function () {
                    let html = config.template(index);
                    let $newRow = $(html);
                    $(config.container).append($newRow);
                    index++;
                    updateRemoveButtons(config);
                    if (config.afterAdd) config.afterAdd($newRow);
                });

                $(config.container).on('click', config.removeButton, function () {
                    $(this).closest(config.itemClass).remove();
                    updateRemoveButtons(config);
                    if (config.afterRemove) config.afterRemove();
                });

                function updateRemoveButtons(config) {
                    let items = $(config.container).find(config.itemClass);
                    items.each(function (i) {
                        $(this).find(config.removeButton).toggleClass('disabled', items.length === 1 && config.requireOne);
                    });
                }
            }

            $('.select2').select2({ width: '100%' });
            $('.select2-category').select2({
                width: '100%',
                closeOnSelect: false
            });

            function refreshCategoryOptions(sectionSelector) {
                const allSelects = $(sectionSelector + ' .select2-category');
                const allSelectedValues = [];

                // Gather all selected values
                allSelects.each(function () {
                    const vals = $(this).val() || [];
                    vals.forEach(v => {
                        if (v) allSelectedValues.push(v);
                    });
                });

                // Update visibility/availability
                allSelects.each(function () {
                    const currentSelect = $(this);
                    const currentVals = currentSelect.val() || [];

                    currentSelect.find('option').each(function () {
                        const opt = $(this);
                        const val = opt.val();
                        if (allSelectedValues.includes(val) && !currentVals.includes(val)) {
                            opt.prop('disabled', true);
                        } else {
                            opt.prop('disabled', false);
                        }
                    });

                    if (currentSelect.data('select2')) {
                        currentSelect.select2('destroy').select2({
                            width: '100%',
                            closeOnSelect: false
                        });
                    }
                });
            }

            $(document).on('change', '#registration-fee-container .select2-category', function () {
                refreshCategoryOptions('#registration-fee-container');
            });

            $(document).on('change', '#security-deposit-container .select2-category', function () {
                refreshCategoryOptions('#security-deposit-container');
            });

            // Initial refresh
            refreshCategoryOptions('#registration-fee-container');
            refreshCategoryOptions('#security-deposit-container');

            // Toggles
            $('#registration_fee_required').on('change', function () {
                $('#registration_fee_section').toggle(this.checked);
            });
            $('#late_registration_allowed').on('change', function () {
                $('#late_fee_section').toggle(this.checked);
            });
            $('#security_deposit_required').on('change', function () {
                $('#security_deposit_section').toggle(this.checked);
            });

            // Registration Fee Repeater
            setupRepeater({
                addButton: '#add-reg-fee',
                removeButton: '.remove-reg-fee',
                container: '#registration-fee-container',
                itemClass: '.registration-fee-item',
                requireOne: true,
                initialIndex: {{ count(old('registration_fee_structure', $exam->registration_fee_structure ?? [[]])) }},
                template: (i) => `
                                                                <div class="card border mb-2 registration-fee-item">
                                                                    <div class="card-body p-3">
                                                                        <div class="row g-2">
                                                                            <div class="col-md-3">
                                                                                 <label class="small">Categories</label>
                                                                                 <select name="registration_fee_structure[${i}][categories][]" class="form-select form-select-sm select2-category" multiple data-placeholder="Select Categories">
                                                                                    ${@json($casteCategories->pluck('name')).map(c => `<option value="${c}">${c}</option>`).join('')}
                                                                                </select>
                                                                            </div>
                                                                            <div class="col-md-3">
                                                                                <label class="small">Amount</label>
                                                                                <input type="number" name="registration_fee_structure[${i}][amount]" class="form-control form-control-sm">
                                                                            </div>
                                                                            <div class="col-md-2">
                                                                                <label class="small">Currency</label>
                                                                                <input type="text" name="registration_fee_structure[${i}][currency]" class="form-control form-control-sm" value="INR">
                                                                            </div>
                                                                            <div class="col-md-2">
                                                                                <label class="small">Refundable</label>
                                                                                <select name="registration_fee_structure[${i}][refundable]" class="form-select form-select-sm">
                                                                                    <option value="No">No</option>
                                                                                    <option value="Yes">Yes</option>
                                                                                </select>
                                                                            </div>
                                                                            <div class="col-md-2 d-flex align-items-end">
                                                                                <button type="button" class="btn btn-sm btn-danger remove-reg-fee w-100">Remove</button>
                                                                            </div>
                                                                            <div class="col-md-12 mt-2">
                                                                                <input type="text" name="registration_fee_structure[${i}][remarks]" class="form-control form-control-sm" placeholder="Remarks">
                                                                            </div>
                                                                        </div>
                                                                    </div>
                                                                </div>`,
                afterAdd: ($row) => {
                    $row.find('.select2-category').select2({ width: '100%', closeOnSelect: false });
                    refreshCategoryOptions('#registration-fee-container');
                },
                afterRemove: () => refreshCategoryOptions('#registration-fee-container')
            });

            // Late Fee Repeater
            setupRepeater({
                addButton: '#add-late-fee',
                removeButton: '.remove-late-fee',
                container: '#late-fee-container',
                itemClass: '.late-fee-item',
                requireOne: true,
                initialIndex: {{ count(old('late_fee_rules', $exam->late_fee_rules ?? [[]])) }},
                template: (i) => `
                                                                <div class="card border mb-2 late-fee-item bg-light">
                                                                    <div class="card-body p-3">
                                                                        <div class="row g-2">
                                                                            <div class="col-md-3">
                                                                                <label class="small">Condition</label>
                                                                                <select name="late_fee_rules[${i}][condition]" class="form-select form-select-sm">
                                                                                    ${['Late registration', 'Missed reporting', 'Choice not locked'].map(c => `<option value="${c}">${c}</option>`).join('')}
                                                                                </select>
                                                                            </div>
                                                                            <div class="col-md-2">
                                                                                <label class="small">Type</label>
                                                                                <select name="late_fee_rules[${i}][penalty_type]" class="form-select form-select-sm">
                                                                                    <option value="Flat">Flat</option>
                                                                                    <option value="Percentage">Percentage</option>
                                                                                </select>
                                                                            </div>
                                                                            <div class="col-md-2">
                                                                                <label class="small">Amount</label>
                                                                                <input type="number" name="late_fee_rules[${i}][penalty_amount]" class="form-control form-control-sm">
                                                                            </div>
                                                                            <div class="col-md-2">
                                                                                <label class="small">Cap</label>
                                                                                <input type="number" name="late_fee_rules[${i}][maximum_penalty_cap]" class="form-control form-control-sm" placeholder="Max Cap">
                                                                            </div>
                                                                            <div class="col-md-3 d-flex align-items-end">
                                                                                <button type="button" class="btn btn-sm btn-danger remove-late-fee w-100">Remove</button>
                                                                            </div>
                                                                            <div class="col-md-12 mt-2">
                                                                                <input type="text" name="late_fee_rules[${i}][remarks]" class="form-control form-control-sm" placeholder="Remarks">
                                                                            </div>
                                                                        </div>
                                                                    </div>
                                                                </div>`
            });

            // Security Deposit Repeater
            setupRepeater({
                addButton: '#add-sd',
                removeButton: '.remove-sd',
                container: '#security-deposit-container',
                itemClass: '.security-deposit-item',
                requireOne: true,
                initialIndex: {{ count(old('security_deposit_structure', $exam->security_deposit_structure ?? [[]])) }},
                template: (i) => `
                                                                <div class="card border mb-2 security-deposit-item">
                                                                    <div class="card-body p-3">
                                                                        <div class="row g-2">
                                                                            <div class="col-md-2">
                                                                                <label class="small">Categories</label>
                                                                                 <select name="security_deposit_structure[${i}][candidate_categories][]" class="form-select form-select-sm select2-category" multiple data-placeholder="Select Categories">
                                                                                    ${@json($casteCategories->pluck('name')).map(c => `<option value="${c}">${c}</option>`).join('')}
                                                                                </select>
                                                                            </div>
                                                                            <div class="col-md-2">
                                                                                <label class="small">College Type</label>
                                                                                <select name="security_deposit_structure[${i}][college_type]" class="form-select form-select-sm">
                                                                                    ${['Government', 'Private', 'Deemed'].map(c => `<option value="${c}">${c}</option>`).join('')}
                                                                                </select>
                                                                            </div>
                                                                            <div class="col-md-2">
                                                                                <label class="small">Quota</label>
                                                                                <select name="security_deposit_structure[${i}][quota_type]" class="form-select form-select-sm">
                                                                                    ${['All India', 'State'].map(c => `<option value="${c}">${c}</option>`).join('')}
                                                                                </select>
                                                                            </div>
                                                                            <div class="col-md-2">
                                                                                <label class="small">Amount</label>
                                                                                <input type="number" name="security_deposit_structure[${i}][amount]" class="form-control form-control-sm">
                                                                            </div>
                                                                            <div class="col-md-2">
                                                                                <label class="small">Refundable</label>
                                                                                <select name="security_deposit_structure[${i}][refundable]" class="form-select form-select-sm">
                                                                                    <option value="Yes">Yes</option>
                                                                                    <option value="No">No</option>
                                                                                </select>
                                                                            </div>
                                                                            <div class="col-md-2 d-flex align-items-end">
                                                                                <button type="button" class="btn btn-sm btn-danger remove-sd w-100">Remove</button>
                                                                            </div>
                                                                            <div class="col-md-6 mt-2">
                                                                                <input type="text" name="security_deposit_structure[${i}][refund_conditions]" class="form-control form-control-sm" placeholder="Refund Conditions">
                                                                            </div>
                                                                            <div class="col-md-6 mt-2">
                                                                                <input type="text" name="security_deposit_structure[${i}][forfeiture_conditions]" class="form-control form-control-sm" placeholder="Forfeiture Conditions">
                                                                            </div>
                                                                        </div>
                                                                    </div>
                                                                </div>`,
                afterAdd: ($row) => {
                    $row.find('.select2-category').select2({ width: '100%', closeOnSelect: false });
                    refreshCategoryOptions('#security-deposit-container');
                },
                afterRemove: () => refreshCategoryOptions('#security-deposit-container')
            });



            // Sessions Repeater
            let sessionIndex = {{ $exam->sessions->count() }};
            const sessionContainer = $('#sessions-container');
            const sessionTemplate = document.getElementById('session-template').innerHTML;

            $('#add-session-btn').click(function () {
                let html = sessionTemplate.replace(/INDEX/g, sessionIndex++);
                sessionContainer.append(html);
                $('#no-sessions-msg').hide();
            });

            $(document).on('click', '.remove-session-btn', function () {
                $(this).closest('.session-row').remove();
            });
        });
    </script>
    <script>
        document.addEventListener('DOMContentLoaded', function () {

            const sourceType = document.getElementById('exam_source_type');
            const owningOrgWrapper = document.getElementById('owningOrgWrapper');
            const owningOrgSelect = document.getElementById('owning_organisation_id');

            function toggleOwningOrg() {
                if (sourceType.value === 'External') {
                    owningOrgWrapper.style.display = 'none';
                    owningOrgSelect.value = '';
                    owningOrgSelect.disabled = true;
                } else {
                    owningOrgWrapper.style.display = 'block';
                    owningOrgSelect.disabled = false;
                }
            }

            // Page load pe bhi check
            toggleOwningOrg();

            // Change pe
            sourceType.addEventListener('change', toggleOwningOrg);

            // Toggle Exam Tabs based on "has_stages"
            const hasStagesCheckbox = document.getElementById('has_stages');
            const examTabsHeader = document.querySelector('.card-header:has(#examTabs)');
            const examTabsContent = document.getElementById('examTabsContent');

            function toggleExamTabs() {
                const isChecked = hasStagesCheckbox.checked;
                // Tabs to toggle
                const standardTabs = ['eligibility-tab', 'sessions-tab', 'result-tab', 'fees-tab'];
                const stagesTab = document.getElementById('stages-tab');

                standardTabs.forEach(id => {
                    const btn = document.getElementById(id);
                    if (btn) {
                        btn.parentElement.style.display = isChecked ? 'none' : 'block';
                        const pane = document.querySelector(btn.dataset.bsTarget);
                        if (pane) {
                            // If hiding standard tabs, make sure we aren't on one
                            if (isChecked && btn.classList.contains('active')) {
                                btn.classList.remove('active');
                                pane.classList.remove('show', 'active');
                            }

                            // Disable inputs in hidden tabs to avoid validation issues
                            const inputs = pane.querySelectorAll('input, select, textarea');
                            inputs.forEach(input => {
                                input.disabled = isChecked;
                            });
                        }
                    }
                });

                if (stagesTab) {
                    stagesTab.parentElement.style.display = isChecked ? 'block' : 'none';
                    const stagesPane = document.getElementById('stages');
                    if (!isChecked && stagesTab.classList.contains('active')) {
                        stagesTab.classList.remove('active');
                        stagesPane.classList.remove('show', 'active');

                        // Default back to eligibility if nothing active
                        const eligibilityTab = document.getElementById('eligibility-tab');
                        if (eligibilityTab) {
                            eligibilityTab.classList.add('active');
                            document.getElementById('eligibility').classList.add('show', 'active');
                        }
                    } else if (isChecked && !document.querySelector('#examTabs .nav-link.active')) {
                        stagesTab.classList.add('active');
                        stagesPane.classList.add('show', 'active');
                    }

                    // Enable/Disable inputs in stages pane
                    const stagesInputs = stagesPane.querySelectorAll('input, select, textarea:not(.editor)');
                    stagesInputs.forEach(input => {
                        input.disabled = !isChecked;
                    });
                }
            }

            if (hasStagesCheckbox) {
                hasStagesCheckbox.addEventListener('change', toggleExamTabs);
                toggleExamTabs(); // Initial check
            }
        });
    </script>
@endpush
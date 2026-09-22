@extends('admin.layouts.master')

@section('content')
<style>
/* Highlight styling for fields NOT auto-filled by AI */
.field-not-autofilled {
    background-color: #fffdf0 !important;
    border: 1.5px solid #f59e0b !important;
    box-shadow: 0 0 0 0.15rem rgba(245, 158, 11, 0.18) !important;
    transition: all 0.2s ease-in-out;
}
.field-not-autofilled:focus {
    background-color: #ffffff !important;
    border-color: #d97706 !important;
    box-shadow: 0 0 0 0.25rem rgba(245, 158, 11, 0.3) !important;
}
.badge-not-autofilled {
    font-size: 0.68rem !important;
    font-weight: 600 !important;
    vertical-align: middle;
    letter-spacing: 0.02em;
    background-color: #fef3c7 !important;
    color: #92400e !important;
    border: 1px solid #fcd34d !important;
    padding: 2px 7px !important;
    border-radius: 4px;
}
.tab-unfilled-pill {
    font-size: 0.68rem !important;
    padding: 0.22em 0.55em !important;
    border: 1px solid rgba(0, 0, 0, 0.1);
}
</style>
<div class="container-fluid">
    <!-- Page Header -->
    <div class="row mb-3">
        <div class="col-12 d-flex justify-content-between align-items-center flex-wrap gap-2">
            <div>
                <h4 class="mb-1">AI Organisation Auto-Creation</h4>
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb mb-0">
                        <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Dashboard</a></li>
                        <li class="breadcrumb-item"><a href="{{ route('admin.organisations.index') }}">Organisations</a></li>
                        <li class="breadcrumb-item active">AI Auto-Creation</li>
                    </ol>
                </nav>
            </div>
            <div class="d-flex gap-2">
                <a href="{{ route('admin.organisations.index') }}" class="btn btn-secondary btn-sm">
                    <i class="fas fa-arrow-left me-1"></i> Back to Organisations
                </a>
                <a href="{{ route('admin.organisations.create') }}" class="btn btn-outline-primary btn-sm">
                    <i class="fas fa-edit me-1"></i> Manual Form
                </a>
            </div>
        </div>
    </div>

    <!-- STEP 1: URL INPUT CARD -->
    <div id="aiStepInput" class="card shadow-sm mb-4 border-0">
        <div class="card-header bg-white py-3 border-bottom">
            <h5 class="card-title mb-0 fw-bold"><i class="fas fa-magic text-primary me-2"></i>AI Auto-Creation</h5>
        </div>
        <div class="card-body">
            <!-- Mode Selector Bar -->
            <div class="mb-4">
                <label class="form-label fw-bold text-dark mb-2">
                    <i class="fas fa-layer-group text-primary me-1"></i> What do you want to import / create? <span class="text-danger">*</span>
                </label>
                <div class="row g-2">
                    <div class="col-md-3 col-6">
                        <input type="radio" class="btn-check entity-mode-radio" name="aiEntityMode" id="modeOrganisation" value="organisation" checked autocomplete="off">
                        <label class="btn btn-outline-primary w-100 p-3 text-start rounded-3 h-100 shadow-sm" for="modeOrganisation">
                            <div class="d-flex align-items-center mb-1">
                                <i class="fas fa-university fa-lg me-2"></i>
                                <span class="fw-bold">Organisation</span>
                            </div>
                            <div class="small text-muted" style="font-size: 0.75rem;">New Institution Profile</div>
                        </label>
                    </div>
                    <div class="col-md-3 col-6">
                        <input type="radio" class="btn-check entity-mode-radio" name="aiEntityMode" id="modeCampus" value="campus" autocomplete="off">
                        <label class="btn btn-outline-primary w-100 p-3 text-start rounded-3 h-100 shadow-sm" for="modeCampus">
                            <div class="d-flex align-items-center mb-1">
                                <i class="fas fa-city fa-lg me-2"></i>
                                <span class="fw-bold">Campus</span>
                            </div>
                            <div class="small text-muted" style="font-size: 0.75rem;">Add Campus Locations</div>
                        </label>
                    </div>
                    <div class="col-md-3 col-6">
                        <input type="radio" class="btn-check entity-mode-radio" name="aiEntityMode" id="modeDepartment" value="department" autocomplete="off">
                        <label class="btn btn-outline-primary w-100 p-3 text-start rounded-3 h-100 shadow-sm" for="modeDepartment">
                            <div class="d-flex align-items-center mb-1">
                                <i class="fas fa-building fa-lg me-2"></i>
                                <span class="fw-bold">Department</span>
                            </div>
                            <div class="small text-muted" style="font-size: 0.75rem;">Add Academic Faculties</div>
                        </label>
                    </div>
                    <div class="col-md-3 col-6">
                        <input type="radio" class="btn-check entity-mode-radio" name="aiEntityMode" id="modeCourse" value="course" autocomplete="off">
                        <label class="btn btn-outline-primary w-100 p-3 text-start rounded-3 h-100 shadow-sm" for="modeCourse">
                            <div class="d-flex align-items-center mb-1">
                                <i class="fas fa-graduation-cap fa-lg me-2"></i>
                                <span class="fw-bold">Course / Program</span>
                            </div>
                            <div class="small text-muted" style="font-size: 0.75rem;">Add Degrees & Programs</div>
                        </label>
                    </div>
                </div>
            </div>

            <!-- Dynamic Cascading Selectors and URL Inputs -->
            <div class="row g-3 align-items-start">
                <!-- Target Organisation (for Campus, Department, Course) -->
                <div class="col-md-4 d-none" id="groupTargetOrg">
                    <label class="form-label fw-bold" id="labelTargetOrg">
                        Target Organisation <span class="text-danger">*</span>
                    </label>
                    <select id="aiInputTargetOrg" class="form-select">
                        <option value="">-- Select Organisation * --</option>
                        @if(isset($organisations))
                            @foreach($organisations as $org)
                                <option value="{{ $org->id }}" data-type-id="{{ $org->organisation_type_id }}" data-website="{{ $org->official_website }}">{{ $org->name }}</option>
                            @endforeach
                        @endif
                    </select>
                </div>

                <!-- Target Campus (for Department, Course) -->
                <div class="col-md-4 d-none" id="groupTargetCampus">
                    <label class="form-label fw-bold" id="labelTargetCampus">
                        Target Campus <span class="text-danger">*</span>
                    </label>
                    <select id="aiInputTargetCampus" class="form-select">
                        <option value="">-- Select Campus * --</option>
                    </select>
                </div>

                <!-- Target Department (for Course) -->
                <div class="col-md-4 d-none" id="groupTargetDept">
                    <label class="form-label fw-bold" id="labelTargetDept">
                        Target Department <span class="text-danger">*</span>
                    </label>
                    <select id="aiInputTargetDept" class="form-select">
                        <option value="">-- Select Department * --</option>
                    </select>
                </div>

                <!-- Organisation Type (for Organisation) -->
                <div class="col-md-4" id="groupOrgType">
                    <label class="form-label fw-bold">Organisation Type <span class="text-danger">*</span></label>
                    <select id="aiInputOrgType" class="form-select" required>
                        <option value="">-- Select Type * --</option>
                        @if(isset($organisationTypes))
                            @foreach($organisationTypes as $type)
                                @if(in_array(strtolower($type->title), ['university', 'college', 'school']))
                                    <option value="{{ $type->id }}" {{ $loop->first ? 'selected' : '' }}>{{ $type->title }}</option>
                                @endif
                            @endforeach
                        @endif
                    </select>
                </div>

                <!-- Website URL -->
                <div class="col-md-8" id="groupWebsiteUrl">
                    <label class="form-label fw-bold" id="labelWebsiteUrl">Official Website URL <span class="text-danger">*</span></label>
                    <div class="input-group">
                        <span class="input-group-text"><i class="fas fa-globe text-muted"></i></span>
                        <input type="url" id="aiInputUrl" class="form-control" placeholder="https://www.example.edu.in" required>
                    </div>
                </div>

                <!-- Google Search Grounding Option -->
                <div class="col-12 mt-2">
                    <div class="card bg-light border-0 shadow-none p-2 rounded-3">
                        <div class="form-check form-switch d-flex align-items-center gap-2 mb-0 ps-0">
                            <input class="form-check-input ms-0 me-2" type="checkbox" role="switch" id="aiSearchGoogleCheck" checked style="width: 2.3em; height: 1.25em; cursor: pointer;">
                            <div>
                                <label class="form-check-label fw-bold text-dark small mb-0" for="aiSearchGoogleCheck" style="cursor: pointer;">
                                    <i class="fab fa-google text-primary me-1"></i> Search Google for missing/additional details
                                </label>
                                <div class="text-muted" style="font-size: 0.75rem;">
                                    When enabled, AI will query Google for missing rankings and address data. Keep unchecked for fast extraction from department / course directories to prevent gateway timeouts.
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Additional Reference URLs Section -->
            <div class="mt-3 pt-3 border-top">
                <div class="d-flex justify-content-between align-items-center mb-2">
                    <label class="form-label fw-bold mb-0 text-dark small">
                        <i class="fas fa-link text-primary me-1"></i> Additional Reference URLs <span class="text-muted fw-normal">(Optional: Wikipedia, NIRF, Admissions page)</span>
                    </label>
                    <button type="button" class="btn btn-sm btn-outline-primary" id="btnAddReferenceUrl">
                        <i class="fas fa-plus me-1"></i> Add URL
                    </button>
                </div>

                <div id="referenceUrlsContainer" class="d-flex flex-column gap-2">
                    <div class="input-group input-group-sm reference-url-row">
                        <span class="input-group-text bg-light text-muted"><i class="fas fa-external-link-alt"></i></span>
                        <input type="url" class="form-control ref-url-input" placeholder="e.g. Wikipedia, NAAC or campus brochure link">
                        <button type="button" class="btn btn-outline-danger btn-remove-ref-url" title="Remove URL">
                            <i class="fas fa-trash-alt"></i>
                        </button>
                    </div>
                </div>
            </div>

            <!-- AI Prompt Preview & Customization Section -->
            <div class="mt-3 pt-3 border-top">
                <div class="d-flex justify-content-between align-items-center mb-2 flex-wrap gap-2">
                    <label class="form-label fw-bold mb-0 text-dark small">
                        <i class="fas fa-terminal text-primary me-1"></i> AI Prompt & Custom Instructions
                        <span class="badge bg-light text-dark border ms-1 fw-normal" id="badgePromptMode">Organisation Only</span>
                    </label>
                    <div class="d-flex gap-2 align-items-center">
                        <button type="button" class="btn btn-sm btn-link text-decoration-none text-danger py-0 d-none" id="btnResetPrompt" title="Reset prompt to default">
                            <i class="fas fa-undo me-1"></i> Reset Prompt
                        </button>
                        <button type="button" class="btn btn-sm btn-outline-secondary" id="btnTogglePromptView">
                            <i class="fas fa-code me-1"></i> <span id="textTogglePrompt">View / Edit Prompt</span>
                        </button>
                    </div>
                </div>

                <div id="promptContainer" class="d-none mt-2">
                    <div class="position-relative">
                        <textarea id="aiCustomPrompt" class="form-control font-monospace small bg-light" rows="9" placeholder="AI prompt will be generated here automatically..."></textarea>
                        <div id="promptLoadingSpinner" class="position-absolute top-50 start-50 translate-middle d-none">
                            <div class="spinner-border spinner-border-sm text-primary" role="status"></div>
                        </div>
                    </div>
                    <div class="form-text small mt-1 text-muted d-flex justify-content-between">
                        <span><i class="fas fa-info-circle me-1"></i> You can edit this prompt or add custom rules before clicking Extract Data.</span>
                        <span id="promptCharCount" class="text-secondary">0 chars</span>
                    </div>
                </div>
            </div>

            <div class="mt-3 text-end">
                <button type="button" id="btnRunAiExtraction" class="btn btn-primary px-4 fw-bold shadow-sm">
                    <i class="fas fa-bolt me-1"></i> Extract Data
                </button>
            </div>
        </div>
    </div>

    <!-- STEP 2: LOADING CARD -->
    <div id="aiStepLoading" class="card shadow-sm mb-4 p-5 d-none text-center">
        <div class="py-4">
            <div class="spinner-border text-primary mb-3" style="width: 3rem; height: 3rem;" role="status">
                <span class="visually-hidden">Loading...</span>
            </div>
            <h5 class="fw-bold mb-2">Extracting Institutional Data...</h5>
            <p class="text-muted mb-0" id="aiLoadingStatusText">
                Researching website, extracting departments, campuses, rankings, and matching master degree courses. Please wait...
            </p>
        </div>
    </div>

    <!-- STEP 3: PREVIEW & REVIEW WORKSPACE -->
    <div id="aiStepPreview" class="d-none">
        <div class="card shadow-sm mb-4">
            <!-- Card Header Actions -->
            <div class="card-header bg-white d-flex justify-content-between align-items-center flex-wrap gap-2 py-3">
                <div>
                    <h5 class="card-title mb-0" id="previewOrgHeaderTitle">Institutional Details Preview</h5>
                    <small class="text-muted" id="previewOrgNameBadge"></small>
                </div>
                <div class="d-flex gap-2">
                    <button type="button" id="btnRestartExtraction" class="btn btn-outline-secondary btn-sm">
                        <i class="fas fa-redo me-1"></i> Extract Another URL
                    </button>
                    <button type="button" class="btn btn-success btn-sm px-3 btn-confirm-save-action">
                        <i class="fas fa-save me-1"></i> Save All Records
                    </button>
                </div>
            </div>

            <!-- Card Header Tabs -->
            <div class="card-header bg-light border-bottom px-3 pt-2">
                <ul class="nav nav-tabs card-header-tabs" id="aiReviewTabs" role="tablist">
                    <li class="nav-item" id="tab-item-org">
                        <button class="nav-link active fw-bold" id="tab-org-btn" data-bs-toggle="tab" data-bs-target="#tab-org" type="button" role="tab">
                            <i class="fas fa-university me-1"></i> Organisation <span class="badge bg-warning text-dark ms-1 d-none" id="badgeOrgUnfilledCount">0</span>
                        </button>
                    </li>
                    <li class="nav-item" id="tab-item-campuses">
                        <button class="nav-link fw-bold" id="tab-campuses-btn" data-bs-toggle="tab" data-bs-target="#tab-campuses" type="button" role="tab">
                            <i class="fas fa-city me-1"></i> Campuses <span class="badge bg-secondary ms-1" id="badgeCampusesCount">0</span>
                        </button>
                    </li>
                    <li class="nav-item" id="tab-item-depts">
                        <button class="nav-link fw-bold" id="tab-depts-btn" data-bs-toggle="tab" data-bs-target="#tab-depts" type="button" role="tab">
                            <i class="fas fa-building me-1"></i> Departments <span class="badge bg-secondary ms-1" id="badgeDeptsCount">0</span>
                        </button>
                    </li>
                    <li class="nav-item" id="tab-item-courses">
                        <button class="nav-link fw-bold" id="tab-courses-btn" data-bs-toggle="tab" data-bs-target="#tab-courses" type="button" role="tab">
                            <i class="fas fa-graduation-cap me-1"></i> Courses <span class="badge bg-secondary ms-1" id="badgeCoursesCount">0</span>
                        </button>
                    </li>
                </ul>
            </div>

            <div class="card-body p-4">
                <!-- Alert Banner for Unfilled / Highlighted Fields -->
                <div class="alert alert-warning border-0 shadow-sm d-flex justify-content-between align-items-center flex-wrap gap-2 py-2 px-3 mb-4 d-none" id="unfilledFieldsBanner" style="background-color: #fef3c7; border-left: 4px solid #f59e0b !important;">
                    <div class="d-flex align-items-center">
                        <i class="fas fa-exclamation-triangle fa-lg me-2 text-warning"></i>
                        <span class="small text-dark">
                            <strong>Manual Entry Needed:</strong> Fields highlighted in yellow with <span class="badge badge-not-autofilled mx-1"><i class="fas fa-pen-nib me-1"></i>Not auto-filled</span> were not found by AI and can be filled manually.
                        </span>
                    </div>
                    <div class="d-flex align-items-center gap-2">
                        <span class="badge bg-warning text-dark fw-bold px-3 py-1 shadow-sm" id="unfilledCountBadge">0 fields to fill manually</span>
                        <button type="button" class="btn btn-sm btn-dark py-1 px-2 shadow-sm" id="btnJumpNextUnfilled" style="font-size: 0.78rem;">
                            <i class="fas fa-arrow-down me-1"></i> Jump to Next Empty
                        </button>
                    </div>
                </div>

                <div class="tab-content" id="aiReviewTabContent">
                    <!-- ==================== TAB 1: ORGANISATION ==================== -->
                    <div class="tab-pane fade show active" id="tab-org" role="tabpanel">
                        <!-- Linked Org Notice -->
                        <div id="mode2OrgNotice" class="alert alert-success border-0 shadow-sm py-2 px-3 mb-3 d-none">
                            <i class="fas fa-check-circle me-1 text-success"></i>
                            <span class="small">Target: <strong id="mode2OrgNameText"></strong>. Review and edit campuses, departments, and courses in Tabs 2, 3, and 4.</span>
                        </div>

                        <!-- Common Basic Fields -->
                        <h6 class="border-bottom pb-2 mb-3 fw-bold text-secondary">
                            <i class="fas fa-id-card me-1"></i> Core Institutional Identity
                        </h6>
                        <div class="row g-3 mb-4">
                            <div class="col-md-6">
                                <label class="form-label fw-bold">Organisation Name <span class="text-danger">*</span></label>
                                <input type="text" name="name" id="aiOrgName" class="form-control" placeholder="Full name of organisation" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-bold">Organisation Type <span class="text-danger">*</span></label>
                                <select name="organisation_type_id" id="aiOrgTypeMaster" class="form-select" required>
                                    <option value="">-- Select Master Type --</option>
                                    @if(isset($organisationTypes))
                                        @foreach($organisationTypes as $ot)
                                            @if(in_array(strtolower($ot->title), ['university', 'college', 'school']))
                                                <option value="{{ $ot->id }}">{{ $ot->title }}</option>
                                            @endif
                                        @endforeach
                                    @endif
                                </select>
                            </div>

                            <div class="col-md-6">
                                <label class="form-label fw-bold">Central Authority</label>
                                <input type="text" name="central_authority" id="aiOrgCentralAuth" class="form-control" placeholder="Central authority, trust or board">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-bold">Head Office Location</label>
                                <input type="text" name="head_office_location" id="aiOrgLocation" class="form-control" placeholder="City, State">
                            </div>

                            <div class="col-md-6">
                                <label class="form-label fw-bold">Is Top Institution?</label>
                                <select name="is_top" id="aiOrgIsTop" class="form-select">
                                    <option value="0">No</option>
                                    <option value="1">Yes (Top Institution)</option>
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-bold">Core Values (Comma separated)</label>
                                <input type="text" name="core_values_csv" id="aiOrgCoreValues" class="form-control" placeholder="Excellence, Integrity, Innovation, Diversity">
                            </div>
                        </div>

                        <!-- Dynamic Type-Specific Fields from edit.blade.php -->
                        @include('admin.organisations.partials.type_specific_fields')
                    </div>

                    <!-- ==================== TAB 2: CAMPUSES ==================== -->
                    <div class="tab-pane fade" id="tab-campuses" role="tabpanel">
                        <!-- Mode 1 Notice -->
                        <div class="alert alert-info border-0 shadow-sm py-2 px-3 mb-3 d-none mode1-skipped-notice">
                            <i class="fas fa-info-circle me-1"></i> Campuses are skipped (Organisation Only mode).
                        </div>
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <div>
                                <h6 class="fw-bold mb-0 text-dark">Physical Campuses & Branches</h6>
                                <small class="text-muted">Manage campus locations, infrastructure, and facilities.</small>
                            </div>
                            <button type="button" class="btn btn-outline-primary btn-sm mode2-only-btn" id="btnAddCampusCard">
                                <i class="fas fa-plus me-1"></i> Add Campus
                            </button>
                        </div>
                        <div id="campusesContainer"></div>
                    </div>

                    <!-- ==================== TAB 3: DEPARTMENTS ==================== -->
                    <div class="tab-pane fade" id="tab-depts" role="tabpanel">
                        <!-- Mode 1 Notice -->
                        <div class="alert alert-info border-0 shadow-sm py-2 px-3 mb-3 d-none mode1-skipped-notice">
                            <i class="fas fa-info-circle me-1"></i> Departments are skipped (Organisation Only mode).
                        </div>
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <div>
                                <h6 class="fw-bold mb-0 text-dark">Faculties & Departments</h6>
                                <small class="text-muted">Manage academic departments, faculty details, and research.</small>
                            </div>
                            <button type="button" class="btn btn-outline-primary btn-sm mode2-only-btn" id="btnAddDeptCard">
                                <i class="fas fa-plus me-1"></i> Add Department
                            </button>
                        </div>
                        <div id="deptsContainer"></div>
                    </div>

                    <!-- ==================== TAB 4: COURSES ==================== -->
                    <div class="tab-pane fade" id="tab-courses" role="tabpanel">
                        <!-- Mode 1 Notice -->
                        <div class="alert alert-info border-0 shadow-sm py-2 px-3 mb-3 d-none mode1-skipped-notice">
                            <i class="fas fa-info-circle me-1"></i> Courses are skipped (Organisation Only mode).
                        </div>
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <div>
                                <h6 class="fw-bold mb-0 text-dark">Degree & Diploma Programs</h6>
                                <small class="text-muted">Programs are automatically matched with your Master Courses list.</small>
                            </div>
                            <button type="button" class="btn btn-outline-primary btn-sm mode2-only-btn" id="btnAddCourseCard">
                                <i class="fas fa-plus me-1"></i> Add Course
                            </button>
                        </div>
                        <div id="coursesContainer"></div>
                    </div>
                </div>
            </div>

            <!-- Card Footer Actions -->
            <div class="card-footer bg-white d-flex justify-content-between align-items-center py-3">
                <span class="text-muted small"><i class="fas fa-info-circle me-1"></i> All changes made across tabs will be saved simultaneously.</span>
                <button type="button" class="btn btn-success px-4 btn-confirm-save-action">
                    <i class="fas fa-save me-1"></i> Save All Records
                </button>
            </div>
        </div>
    </div>
</div>
@endsection

@push('js')
<script>
document.addEventListener('DOMContentLoaded', function () {
    let currentExtractedData = null;
    let currentExtractionMode = 'organisation';
    let currentTargetOrgId = null;
    let currentTargetOrgName = '';
    let currentTargetCampusId = null;
    let currentTargetCampusName = '';
    let currentTargetDeptId = null;
    let currentTargetDeptName = '';

    const allOrganisations = @json($organisations ?? []);
    let globalMasters = {
        courses: @json($courses ?? []),
        program_levels: @json($programLevels ?? []),
        streams: @json($streams ?? []),
        disciplines: @json($disciplines ?? []),
        organisation_types: @json($organisationTypes ?? [])
    };

    function normalizeStr(str) {
        if (!str) return '';
        return str.toString()
            .toLowerCase()
            .replace(/\b(in|of|and|&|the|for|with|a|an|program|programs|course|courses|degree|honors|hons)\b/gi, '')
            .replace(/[^a-z0-9]/gi, '')
            .trim();
    }

    function levenshteinDistance(s, t) {
        if (!s.length) return t.length;
        if (!t.length) return s.length;
        const arr = [];
        for (let i = 0; i <= t.length; i++) {
            arr[i] = [i];
            for (let j = 1; j <= s.length; j++) {
                arr[i][j] =
                    i === 0
                        ? j
                        : Math.min(
                              arr[i - 1][j] + 1,
                              arr[i][j - 1] + 1,
                              arr[i - 1][j - 1] + (s[j - 1] === t[i - 1] ? 0 : 1)
                          );
            }
        }
        return arr[t.length][s.length];
    }

    function stringSimilarity(s1, s2) {
        let longer = s1.length >= s2.length ? s1 : s2;
        let shorter = s1.length < s2.length ? s1 : s2;
        if (longer.length === 0) return 1.0;
        let longerLength = longer.length;
        let editDistance = levenshteinDistance(longer, shorter);
        return (longerLength - editDistance) / parseFloat(longerLength);
    }

    function extractDegreePrefix(str) {
        if (!str) return '';
        const s = ' ' + str.toString().toLowerCase().replace(/[^a-z0-9]/g, ' ') + ' ';

        if (/\b(ph\s*d|doctorate|doctor of philosophy|d\s*phil)\b/.test(s)) return 'phd';
        if (/\b(b\s*tech|bachelor of technology|b\s*e|bachelor of engineering)\b/.test(s)) return 'btech';
        if (/\b(m\s*tech|master of technology|m\s*e|master of engineering)\b/.test(s)) return 'mtech';
        if (/\b(b\s*pharm|b\s*pharma|bachelor of pharmacy|b\s*pharmacy)\b/.test(s)) return 'bpharm';
        if (/\b(m\s*pharm|m\s*pharma|master of pharmacy|m\s*pharmacy)\b/.test(s)) return 'mpharm';
        if (/\b(d\s*pharm|d\s*pharma|diploma in pharmacy|diploma pharmacy)\b/.test(s)) return 'dpharm';
        if (/\b(diploma|polytechnic)\b/.test(s)) return 'diploma';
        if (/\b(bca|bachelor of computer application|bachelor of computer applications)\b/.test(s)) return 'bca';
        if (/\b(mca|master of computer application|master of computer applications)\b/.test(s)) return 'mca';
        if (/\b(bba|bachelor of business administration|bms|bbs)\b/.test(s)) return 'bba';
        if (/\b(mba|master of business administration|pgdm)\b/.test(s)) return 'mba';
        if (/\b(b\s*sc|bachelor of science|b\s*s)\b/.test(s)) return 'bsc';
        if (/\b(m\s*sc|master of science|m\s*s)\b/.test(s) && !/\b(master of surgery)\b/.test(s)) return 'msc';
        if (/\b(b\s*com|bachelor of commerce)\b/.test(s)) return 'bcom';
        if (/\b(m\s*com|master of commerce)\b/.test(s)) return 'mcom';
        if (/\b(b\s*a|bachelor of arts)\b/.test(s)) return 'ba';
        if (/\b(m\s*a|master of arts)\b/.test(s)) return 'ma';
        if (/\b(llb|bachelor of law|bachelor of laws)\b/.test(s)) return 'llb';
        if (/\b(llm|master of law|master of laws)\b/.test(s)) return 'llm';
        if (/\b(mbbs)\b/.test(s)) return 'mbbs';
        if (/\b(bds|bachelor of dental surgery)\b/.test(s)) return 'bds';
        if (/\b(md|doctor of medicine)\b/.test(s)) return 'md';
        if (/\b(ms|master of surgery)\b/.test(s)) return 'ms';
        if (/\b(b\s*arch|bachelor of architecture)\b/.test(s)) return 'barch';
        if (/\b(m\s*arch|master of architecture)\b/.test(s)) return 'march';
        if (/\b(b\s*des|bachelor of design)\b/.test(s)) return 'bdes';
        if (/\b(m\s*des|master of design)\b/.test(s)) return 'mdes';
        if (/\b(b\s*ed|bachelor of education)\b/.test(s)) return 'bed';
        if (/\b(m\s*ed|master of education)\b/.test(s)) return 'med';
        if (/\b(bpt|bachelor of physiotherapy|bachelors in physiotherapy)\b/.test(s)) return 'bpt';
        if (/\b(mpt|master of physiotherapy|masters in physiotherapy)\b/.test(s)) return 'mpt';

        return '';
    }

    function extractSubjectCore(str) {
        if (!str) return '';
        return str.toString()
            .toLowerCase()
            .replace(/\b(\d+\s*(years?|yrs?|semesters?|sems?))\b/gi, '')
            .replace(/\b(b\.?tech|m\.?tech|b\.?e|m\.?e|b\.?sc|m\.?sc|bca|mca|bba|mba|b\.?com|m\.?com|b\.?a|m\.?a|b\.?pharm|m\.?pharm|d\.?pharm|b\.?des|m\.?des|b\.?arch|m\.?arch|b\.?ed|m\.?ed|ph\.?d|phd|doctorate|doctor of philosophy|diploma|bpt|mpt|bachelor of technology|master of technology|bachelor of engineering|master of engineering|bachelor of science|master of science|bachelor of commerce|master of commerce|bachelor of arts|master of arts|bachelor of pharmacy|master of pharmacy|bachelor of design|master of design|bachelor of architecture|master of architecture|bachelor of business administration|master of business administration|bachelor of computer applications|master of computer applications)\b/gi, '')
            .replace(/\b(in|of|and|&|the|for|with|a|an|program|programs|course|courses|degree|honors|hons|engineering|technology|studies|science|sciences)\b/gi, '')
            .replace(/[^a-z0-9]/gi, '')
            .trim();
    }

    function findBestCourseMatch(aiName, aiShortName, aiLevel, aiStream, aiDiscipline) {
        if (!globalMasters.courses || globalMasters.courses.length === 0 || (!aiName && !aiShortName)) return null;

        const aiDegree = extractDegreePrefix(aiName) || extractDegreePrefix(aiShortName) || extractDegreePrefix(aiLevel);
        const aiSubject = extractSubjectCore(aiName) || extractSubjectCore(aiShortName) || extractSubjectCore(aiDiscipline);
        const normName = normalizeStr(aiName);
        const normShort = normalizeStr(aiShortName);

        // 1. Direct exact full string match
        for (const c of globalMasters.courses) {
            const normC = normalizeStr(c.name);
            if (normName !== '' && normName === normC) return c;
            if (normShort !== '' && normShort === normC) return c;
        }

        // 2. Degree Prefix + Subject Matching
        let bestCandidate = null;
        let bestScore = -1;

        for (const c of globalMasters.courses) {
            const cDegree = extractDegreePrefix(c.name);
            const cSubject = extractSubjectCore(c.name);
            const normC = normalizeStr(c.name);

            // If AI extracted a degree prefix, candidate MUST match it!
            if (aiDegree !== '') {
                if (cDegree !== '' && cDegree !== aiDegree) {
                    continue; // Strict degree mismatch: B.Tech must NEVER match M.Tech, Ph.D, etc.
                }
            }

            let score = 0;

            // Degree Match Bonus
            if (aiDegree !== '' && cDegree === aiDegree) {
                score += 10.0;
            }

            // Subject Core Comparison
            if (aiSubject !== '' && cSubject !== '') {
                if (aiSubject === cSubject) {
                    score += 50.0; // Perfect subject match
                } else if (cSubject.startsWith(aiSubject) || aiSubject.startsWith(cSubject)) {
                    score += 25.0 + (Math.min(aiSubject.length, cSubject.length) / Math.max(aiSubject.length, cSubject.length) * 10);
                } else if (cSubject.includes(aiSubject) || aiSubject.includes(cSubject)) {
                    score += 15.0 + (Math.min(aiSubject.length, cSubject.length) / Math.max(aiSubject.length, cSubject.length) * 10);
                } else {
                    const sim = stringSimilarity(aiSubject, cSubject);
                    if (sim >= 0.6) {
                        score += sim * 15;
                    }
                }
            } else if (aiSubject === '' && cSubject === '') {
                score += 30.0;
            }

            // Full normalized string similarity fallback
            const fullSim = stringSimilarity(normName, normC);
            score += fullSim * 5.0;

            if (score > bestScore && score >= 10.0) {
                bestScore = score;
                bestCandidate = c;
            }
        }

        return bestCandidate;
    }

    function findBestLevelMatch(aiLevel) {
        if (!globalMasters.program_levels || !aiLevel) return '';
        const norm = normalizeStr(aiLevel);
        for (const l of globalMasters.program_levels) {
            const normL = normalizeStr(l.title);
            if (norm === normL || normL.includes(norm) || norm.includes(normL)) return l.id;
        }
        if (norm.includes('undergraduate') || norm.includes('ug') || norm.includes('bachelor')) {
            const m = globalMasters.program_levels.find(l => normalizeStr(l.title).includes('undergraduate') || normalizeStr(l.title).includes('ug'));
            if (m) return m.id;
        }
        if (norm.includes('postgraduate') || norm.includes('pg') || norm.includes('master')) {
            const m = globalMasters.program_levels.find(l => normalizeStr(l.title).includes('postgraduate') || normalizeStr(l.title).includes('pg'));
            if (m) return m.id;
        }
        if (norm.includes('diploma') || norm.includes('polytechnic')) {
            const m = globalMasters.program_levels.find(l => normalizeStr(l.title).includes('diploma'));
            if (m) return m.id;
        }
        if (norm.includes('phd') || norm.includes('doctor') || norm.includes('research')) {
            const m = globalMasters.program_levels.find(l => normalizeStr(l.title).includes('phd') || normalizeStr(l.title).includes('ph.d') || normalizeStr(l.title).includes('doctor'));
            if (m) return m.id;
        }
        if (norm.includes('certif')) {
            const m = globalMasters.program_levels.find(l => normalizeStr(l.title).includes('certif'));
            if (m) return m.id;
        }
        if (norm.includes('school') || norm.includes('k12')) {
            const m = globalMasters.program_levels.find(l => normalizeStr(l.title).includes('school'));
            if (m) return m.id;
        }
        return '';
    }

    function findBestStreamMatch(aiStream) {
        if (!globalMasters.streams || !aiStream) return '';
        const norm = normalizeStr(aiStream);
        let best = '';
        let maxScore = 0;
        for (const s of globalMasters.streams) {
            const normS = normalizeStr(s.title);
            if (norm === normS) return s.id;
            if (normS.includes(norm) || norm.includes(normS)) {
                const score = Math.min(norm.length, normS.length) / Math.max(norm.length, normS.length);
                if (score > maxScore) {
                    maxScore = score;
                    best = s.id;
                }
            }
        }
        return best;
    }

    function findBestDisciplineMatch(aiDiscipline) {
        if (!globalMasters.disciplines || !aiDiscipline) return '';
        const norm = normalizeStr(aiDiscipline);
        let best = '';
        let maxScore = 0;
        for (const d of globalMasters.disciplines) {
            const normD = normalizeStr(d.title);
            if (norm === normD) return d.id;
            if (normD.includes(norm) || norm.includes(normD)) {
                const score = Math.min(norm.length, normD.length) / Math.max(norm.length, normD.length);
                if (score > maxScore) {
                    maxScore = score;
                    best = d.id;
                }
            }
        }
        return best;
    }

    function getAvailableCampuses() {
        const campuses = [];
        document.querySelectorAll('.campus-item .c-name').forEach(input => {
            const val = input.value.trim();
            if (val && !campuses.includes(val)) campuses.push(val);
        });

        const campusSelectEl = document.getElementById('aiInputTargetCampus');
        if (campusSelectEl) {
            Array.from(campusSelectEl.options).forEach(opt => {
                const val = opt.text ? opt.text.replace(/\s*\(.*?\)\s*$/, '').trim() : '';
                if (val && !val.startsWith('--') && !campuses.includes(val)) {
                    campuses.push(val);
                }
            });
        }

        if (currentTargetCampusName && !campuses.includes(currentTargetCampusName)) {
            campuses.unshift(currentTargetCampusName);
        }

        return campuses.length ? campuses : ['Main Campus'];
    }

    function getAvailableDepts() {
        const depts = [];
        document.querySelectorAll('.dept-item .d-name').forEach(input => {
            const val = input.value.trim();
            if (val && !depts.includes(val)) depts.push(val);
        });

        const deptSelectEl = document.getElementById('aiInputTargetDept');
        if (deptSelectEl) {
            Array.from(deptSelectEl.options).forEach(opt => {
                const val = opt.text ? opt.text.replace(/\s*\[.*?\]\s*$/, '').trim() : '';
                if (val && !val.startsWith('--') && !depts.includes(val)) {
                    depts.push(val);
                }
            });
        }

        if (currentTargetDeptName && !depts.includes(currentTargetDeptName)) {
            depts.unshift(currentTargetDeptName);
        }

        return depts.length ? depts : ['General Faculty'];
    }

    function refreshCourseCampusAndDeptDropdowns() {
        const campuses = getAvailableCampuses();
        const depts = getAvailableDepts();

        document.querySelectorAll('.course-item').forEach(card => {
            const campusSelect = card.querySelector('.cr-campus-select');
            if (campusSelect) {
                const curVal = campusSelect.value || currentTargetCampusName;
                campusSelect.innerHTML = campuses.map(c => `<option value="${c}" ${c === curVal ? 'selected' : ''}>${c}</option>`).join('');
            }
            const deptSelect = card.querySelector('.cr-dept-select');
            if (deptSelect) {
                const curVal = deptSelect.value || currentTargetDeptName;
                deptSelect.innerHTML = depts.map(d => `<option value="${d}" ${d === curVal ? 'selected' : ''}>${d}</option>`).join('');
            }
        });
    }

    const btnRunAiExtraction = document.getElementById('btnRunAiExtraction');
    const btnRestartExtraction = document.getElementById('btnRestartExtraction');
    const aiStepInput = document.getElementById('aiStepInput');
    const aiStepLoading = document.getElementById('aiStepLoading');
    const aiStepPreview = document.getElementById('aiStepPreview');

    // Toggle Type-Specific Field Containers
    function toggleOrgTypeFields(typeId) {
        const containers = {
            'university-fields': [1, 2],
            'institute-fields': [3],
            'school-fields': [4],
            'exam-conducting-body-fields': [5],
            'counselling-body-fields': [6],
            'regulatory-body-fields': [7, 8]
        };

        const parsedId = parseInt(typeId) || 1;
        Object.keys(containers).forEach(containerId => {
            const el = document.getElementById(containerId);
            if (el) {
                const isVisible = containers[containerId].includes(parsedId);
                el.style.display = isVisible ? 'block' : 'none';
                el.querySelectorAll('input, select, textarea').forEach(input => {
                    input.disabled = !isVisible;
                });
            }
        });

        if (typeof highlightUnfilledFields === 'function') {
            highlightUnfilledFields();
        }
    }

    // Master Type Selector Change Listener
    const aiOrgTypeMaster = document.getElementById('aiOrgTypeMaster');
    if (aiOrgTypeMaster) {
        aiOrgTypeMaster.addEventListener('change', function () {
            toggleOrgTypeFields(this.value);
        });
    }

    // ----------------------------------------------------
    // Entity Mode Radio Switching & Dynamic Containers
    // ----------------------------------------------------
    function getSelectedEntityMode() {
        const checked = document.querySelector('input[name="aiEntityMode"]:checked');
        return checked ? checked.value : 'organisation';
    }

    function handleEntityModeChange() {
        const mode = getSelectedEntityMode();
        const groupOrgType = document.getElementById('groupOrgType');
        const groupTargetOrg = document.getElementById('groupTargetOrg');
        const groupTargetCampus = document.getElementById('groupTargetCampus');
        const groupTargetDept = document.getElementById('groupTargetDept');
        const labelWebsiteUrl = document.getElementById('labelWebsiteUrl');
        const aiInputUrl = document.getElementById('aiInputUrl');
        const badgePromptMode = document.getElementById('badgePromptMode');

        if (mode === 'organisation') {
            if (groupOrgType) groupOrgType.classList.remove('d-none');
            if (groupTargetOrg) groupTargetOrg.classList.add('d-none');
            if (groupTargetCampus) groupTargetCampus.classList.add('d-none');
            if (groupTargetDept) groupTargetDept.classList.add('d-none');
            if (labelWebsiteUrl) labelWebsiteUrl.innerHTML = 'Official Website URL <span class="text-danger">*</span>';
            if (aiInputUrl) aiInputUrl.placeholder = 'https://www.example.edu.in';
            if (badgePromptMode) {
                badgePromptMode.innerText = 'Organisation Only';
                badgePromptMode.className = 'badge bg-light text-dark border ms-1 fw-normal';
            }
        } else if (mode === 'campus') {
            if (groupOrgType) groupOrgType.classList.add('d-none');
            if (groupTargetOrg) groupTargetOrg.classList.remove('d-none');
            if (groupTargetCampus) groupTargetCampus.classList.add('d-none');
            if (groupTargetDept) groupTargetDept.classList.add('d-none');
            if (labelWebsiteUrl) labelWebsiteUrl.innerHTML = 'Campus Website / Page URL <span class="text-danger">*</span>';
            if (aiInputUrl) aiInputUrl.placeholder = 'https://www.example.edu.in/campuses/city-campus';
            if (badgePromptMode) {
                badgePromptMode.innerText = 'Campus Only';
                badgePromptMode.className = 'badge bg-info-subtle text-info border border-info ms-1 fw-normal';
            }
        } else if (mode === 'department') {
            if (groupOrgType) groupOrgType.classList.add('d-none');
            if (groupTargetOrg) groupTargetOrg.classList.remove('d-none');
            if (groupTargetCampus) groupTargetCampus.classList.remove('d-none');
            if (groupTargetDept) groupTargetDept.classList.add('d-none');
            if (labelWebsiteUrl) labelWebsiteUrl.innerHTML = 'Department Website / Faculty URL <span class="text-danger">*</span>';
            if (aiInputUrl) aiInputUrl.placeholder = 'https://www.example.edu.in/department/computer-science';
            if (badgePromptMode) {
                badgePromptMode.innerText = 'Department Only';
                badgePromptMode.className = 'badge bg-warning-subtle text-warning border border-warning ms-1 fw-normal';
            }
        } else if (mode === 'course') {
            if (groupOrgType) groupOrgType.classList.add('d-none');
            if (groupTargetOrg) groupTargetOrg.classList.remove('d-none');
            if (groupTargetCampus) groupTargetCampus.classList.remove('d-none');
            if (groupTargetDept) groupTargetDept.classList.remove('d-none');
            if (labelWebsiteUrl) labelWebsiteUrl.innerHTML = 'Course / Admissions Website URL <span class="text-danger">*</span>';
            if (aiInputUrl) aiInputUrl.placeholder = 'https://www.example.edu.in/admissions/btech-cse';
            if (badgePromptMode) {
                badgePromptMode.innerText = 'Course / Program Only';
                badgePromptMode.className = 'badge bg-success-subtle text-success border border-success ms-1 fw-normal';
            }
        }

        const searchGoogleCheck = document.getElementById('aiSearchGoogleCheck');
        if (searchGoogleCheck) {
            if (mode === 'organisation') {
                searchGoogleCheck.checked = true;
            } else {
                searchGoogleCheck.checked = false;
            }
        }

        fetchAndRefreshPrompt(false);
    }

    document.querySelectorAll('.entity-mode-radio').forEach(radio => {
        radio.addEventListener('change', handleEntityModeChange);
    });

    // ----------------------------------------------------
    // Cascading Dropdowns: Org -> Campuses -> Departments
    // ----------------------------------------------------
    $('#aiInputTargetOrg').on('change', function () {
        const orgId = $(this).val();
        const selOrg = (allOrganisations || []).find(o => String(o.id) === String(orgId));
        const urlInput = document.getElementById('aiInputUrl');
        if (urlInput && !urlInput.value && selOrg && selOrg.official_website) {
            urlInput.value = selOrg.official_website;
        }

        const $campusSelect = $('#aiInputTargetCampus');
        const $deptSelect = $('#aiInputTargetDept');
        $campusSelect.empty().append(new Option('-- Select Campus * --', ''));
        $deptSelect.empty().append(new Option('-- Select Department * --', ''));

        if (orgId) {
            fetch(`{{ route('admin.ai-organisations.cascading-options') }}?organisation_id=${orgId}`, {
                headers: {
                    'Accept': 'application/json'
                }
            })
                .then(res => res.ok ? res.json() : null)
                .then(data => {
                    if (data && data.campuses && Array.isArray(data.campuses)) {
                        data.campuses.forEach(c => {
                            $campusSelect.append(new Option(c.campus_name + (c.city ? ' (' + c.city + ')' : ''), c.id));
                        });
                    }
                    if (data && data.departments && Array.isArray(data.departments)) {
                        data.departments.forEach(d => {
                            $deptSelect.append(new Option(d.department_name + (d.department_code ? ' [' + d.department_code + ']' : ''), d.id));
                        });
                    }
                })
                .catch(err => console.error('Error fetching cascading options:', err));
        }

        fetchAndRefreshPrompt(false);
    });

    $('#aiInputTargetCampus').on('change', function () {
        const orgId = $('#aiInputTargetOrg').val();
        const campusId = $(this).val();
        const $deptSelect = $('#aiInputTargetDept');
        $deptSelect.empty().append(new Option('-- Select Department * --', ''));

        if (orgId || campusId) {
            const params = new URLSearchParams();
            if (orgId) params.append('organisation_id', orgId);
            if (campusId) params.append('campus_id', campusId);

            fetch(`{{ route('admin.ai-organisations.cascading-options') }}?${params.toString()}`, {
                headers: {
                    'Accept': 'application/json'
                }
            })
                .then(res => res.ok ? res.json() : null)
                .then(data => {
                    if (data && data.departments && Array.isArray(data.departments)) {
                        data.departments.forEach(d => {
                            $deptSelect.append(new Option(d.department_name + (d.department_code ? ' [' + d.department_code + ']' : ''), d.id));
                        });
                    }
                })
                .catch(err => console.error('Error fetching cascading departments:', err));
        }

        fetchAndRefreshPrompt(false);
    });

    $('#aiInputTargetDept').on('change', function () {
        fetchAndRefreshPrompt(false);
    });

    $('#aiInputOrgType').on('change', function () {
        fetchAndRefreshPrompt(false);
    });

    $('#aiSearchGoogleCheck').on('change', function () {
        fetchAndRefreshPrompt(false);
    });

    const aiInputUrlEl = document.getElementById('aiInputUrl');
    if (aiInputUrlEl) {
        aiInputUrlEl.addEventListener('blur', function () {
            fetchAndRefreshPrompt(false);
        });
    }

    // ----------------------------------------------------
    // Prompt Preview & Editing Logic
    // ----------------------------------------------------
    let promptIsManuallyEdited = false;
    let isPromptOpen = false;
    let promptFetchDebounce = null;

    const btnTogglePromptView = document.getElementById('btnTogglePromptView');
    const btnResetPrompt = document.getElementById('btnResetPrompt');
    const promptContainer = document.getElementById('promptContainer');
    const aiCustomPrompt = document.getElementById('aiCustomPrompt');
    const textTogglePrompt = document.getElementById('textTogglePrompt');
    const badgePromptMode = document.getElementById('badgePromptMode');
    const promptCharCount = document.getElementById('promptCharCount');
    const promptLoadingSpinner = document.getElementById('promptLoadingSpinner');

    function updatePromptCharCount() {
        if (aiCustomPrompt && promptCharCount) {
            promptCharCount.innerText = `${aiCustomPrompt.value.length} chars`;
        }
    }

    if (aiCustomPrompt) {
        aiCustomPrompt.addEventListener('input', function () {
            promptIsManuallyEdited = true;
            if (btnResetPrompt) btnResetPrompt.classList.remove('d-none');
            updatePromptCharCount();
        });
    }

    if (btnResetPrompt) {
        btnResetPrompt.addEventListener('click', function () {
            promptIsManuallyEdited = false;
            btnResetPrompt.classList.add('d-none');
            fetchAndRefreshPrompt(true);
        });
    }

    if (btnTogglePromptView) {
        btnTogglePromptView.addEventListener('click', function () {
            isPromptOpen = !isPromptOpen;
            if (isPromptOpen) {
                promptContainer.classList.remove('d-none');
                textTogglePrompt.innerText = 'Hide Prompt';
                if (!aiCustomPrompt.value || !promptIsManuallyEdited) {
                    fetchAndRefreshPrompt(false);
                }
            } else {
                promptContainer.classList.add('d-none');
                textTogglePrompt.innerText = 'View / Edit Prompt';
            }
        });
    }

    function fetchAndRefreshPrompt(force = false) {
        const mode = getSelectedEntityMode();
        const targetOrgId = document.getElementById('aiInputTargetOrg') ? document.getElementById('aiInputTargetOrg').value : '';
        const targetCampusId = document.getElementById('aiInputTargetCampus') ? document.getElementById('aiInputTargetCampus').value : '';
        const targetDeptId = document.getElementById('aiInputTargetDept') ? document.getElementById('aiInputTargetDept').value : '';
        const searchGoogle = document.getElementById('aiSearchGoogleCheck') ? (document.getElementById('aiSearchGoogleCheck').checked ? 1 : 0) : 1;

        // Update badge immediately
        if (badgePromptMode) {
            if (mode === 'organisation') {
                badgePromptMode.innerText = 'Organisation Only';
                badgePromptMode.className = 'badge bg-light text-dark border ms-1 fw-normal';
            } else if (mode === 'campus') {
                badgePromptMode.innerText = 'Campus Only';
                badgePromptMode.className = 'badge bg-info-subtle text-info border border-info ms-1 fw-normal';
            } else if (mode === 'department') {
                badgePromptMode.innerText = 'Department Only';
                badgePromptMode.className = 'badge bg-warning-subtle text-warning border border-warning ms-1 fw-normal';
            } else if (mode === 'course') {
                badgePromptMode.innerText = 'Course / Program Only';
                badgePromptMode.className = 'badge bg-success-subtle text-success border border-success ms-1 fw-normal';
            }
        }

        if (promptIsManuallyEdited && !force) return;

        const url = document.getElementById('aiInputUrl')?.value.trim() || '';
        const orgTypeId = document.getElementById('aiInputOrgType')?.value || '1';

        const referenceUrls = [];
        document.querySelectorAll('#referenceUrlsContainer .ref-url-input').forEach(input => {
            const val = input.value.trim();
            if (val && !referenceUrls.includes(val)) {
                referenceUrls.push(val);
            }
        });

        if (promptLoadingSpinner) promptLoadingSpinner.classList.remove('d-none');

        clearTimeout(promptFetchDebounce);
        promptFetchDebounce = setTimeout(function () {
            fetch("{{ route('admin.ai-organisations.preview-prompt') }}", {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': "{{ csrf_token() }}"
                },
                body: JSON.stringify({
                    mode: mode,
                    url: url || 'https://www.example.edu',
                    organisation_type_id: orgTypeId,
                    target_organisation_id: targetOrgId || null,
                    target_campus_id: targetCampusId || null,
                    target_department_id: targetDeptId || null,
                    reference_urls: referenceUrls,
                    search_google: searchGoogle
                })
            })
            .then(async res => {
                const contentType = res.headers.get('content-type') || '';
                if (!contentType.includes('application/json')) return null;
                return res.json();
            })
            .then(result => {
                if (promptLoadingSpinner) promptLoadingSpinner.classList.add('d-none');
                if (result && result.success && result.prompt) {
                    if (aiCustomPrompt) {
                        aiCustomPrompt.value = result.prompt;
                        updatePromptCharCount();
                    }
                }
            })
            .catch(() => {
                if (promptLoadingSpinner) promptLoadingSpinner.classList.add('d-none');
            });
        }, 150);
    }

    // Reference URLs Dynamic Management
    const refContainer = document.getElementById('referenceUrlsContainer');
    const btnAddRef = document.getElementById('btnAddReferenceUrl');
    if (btnAddRef && refContainer) {
        btnAddRef.addEventListener('click', function () {
            const row = document.createElement('div');
            row.className = 'input-group reference-url-row';
            row.innerHTML = `
                <span class="input-group-text bg-light text-muted"><i class="fas fa-external-link-alt"></i></span>
                <input type="url" class="form-control ref-url-input" placeholder="e.g. Wikipedia, NIRF, NAAC, or Admission page URL">
                <button type="button" class="btn btn-outline-danger btn-remove-ref-url" title="Clear / Remove URL">
                    <i class="fas fa-trash-alt"></i>
                </button>
            `;
            refContainer.appendChild(row);
            const inputEl = row.querySelector('.ref-url-input');
            if (inputEl) {
                inputEl.focus();
                inputEl.addEventListener('blur', function () { fetchAndRefreshPrompt(false); });
            }
        });

        refContainer.addEventListener('click', function (e) {
            const removeBtn = e.target.closest('.btn-remove-ref-url');
            if (removeBtn) {
                const row = removeBtn.closest('.reference-url-row');
                if (refContainer.querySelectorAll('.reference-url-row').length > 1) {
                    row.remove();
                } else {
                    const inputEl = row.querySelector('.ref-url-input');
                    if (inputEl) inputEl.value = '';
                }
                fetchAndRefreshPrompt(false);
            }
        });

        refContainer.querySelectorAll('.ref-url-input').forEach(input => {
            input.addEventListener('blur', function () { fetchAndRefreshPrompt(false); });
        });
    }

    // ----------------------------------------------------
    // Trigger AI Extraction
    // ----------------------------------------------------
    btnRunAiExtraction.addEventListener('click', function () {
        const mode = getSelectedEntityMode();
        const url = document.getElementById('aiInputUrl').value.trim();
        const orgTypeId = document.getElementById('aiInputOrgType') ? document.getElementById('aiInputOrgType').value : '1';
        const targetOrgId = document.getElementById('aiInputTargetOrg') ? document.getElementById('aiInputTargetOrg').value : '';
        const targetCampusId = document.getElementById('aiInputTargetCampus') ? document.getElementById('aiInputTargetCampus').value : '';
        const targetDeptId = document.getElementById('aiInputTargetDept') ? document.getElementById('aiInputTargetDept').value : '';
        const searchGoogle = document.getElementById('aiSearchGoogleCheck') ? (document.getElementById('aiSearchGoogleCheck').checked ? 1 : 0) : 1;

        if (!url) {
            alert('Please enter a valid website URL.');
            document.getElementById('aiInputUrl').focus();
            return;
        }

        if (mode === 'organisation' && !orgTypeId) {
            alert('Please select an Organisation Type before extracting data.');
            document.getElementById('aiInputOrgType').focus();
            return;
        }

        if (mode !== 'organisation' && !targetOrgId) {
            alert('Please select a Target Organisation before proceeding.');
            document.getElementById('aiInputTargetOrg').focus();
            return;
        }

        if ((mode === 'department' || mode === 'course') && !targetCampusId) {
            alert('Please select a Target Campus before proceeding.');
            document.getElementById('aiInputTargetCampus').focus();
            return;
        }

        if (mode === 'course' && !targetDeptId) {
            alert('Please select a Target Department before proceeding.');
            document.getElementById('aiInputTargetDept').focus();
            return;
        }

        const referenceUrls = [];
        document.querySelectorAll('#referenceUrlsContainer .ref-url-input').forEach(input => {
            const val = input.value.trim();
            if (val && !referenceUrls.includes(val)) {
                referenceUrls.push(val);
            }
        });

        const customPromptVal = (promptIsManuallyEdited && aiCustomPrompt && aiCustomPrompt.value.trim()) ? aiCustomPrompt.value.trim() : null;

        const loadingStatusEl = document.getElementById('aiLoadingStatusText');
        if (loadingStatusEl) {
            const selOrg = (allOrganisations || []).find(o => String(o.id) === String(targetOrgId));
            const orgName = selOrg ? selOrg.name : 'the organisation';

            if (mode === 'campus') {
                loadingStatusEl.innerText = `Extracting campus details for '${orgName}'... Please wait.`;
            } else if (mode === 'department') {
                loadingStatusEl.innerText = `Extracting academic faculties and departments for '${orgName}'... Please wait.`;
            } else if (mode === 'course') {
                loadingStatusEl.innerText = `Extracting degrees and matching master programs for '${orgName}'... Please wait.`;
            } else {
                loadingStatusEl.innerText = 'Extracting institutional identity profile... Please wait.';
            }
        }

        aiStepInput.classList.add('d-none');
        aiStepLoading.classList.remove('d-none');
        aiStepPreview.classList.add('d-none');

        fetch("{{ route('admin.ai-organisations.extract') }}", {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json',
                'X-CSRF-TOKEN': "{{ csrf_token() }}"
            },
            body: JSON.stringify({
                mode: mode,
                url: url,
                organisation_type_id: orgTypeId,
                target_organisation_id: targetOrgId || null,
                target_campus_id: targetCampusId || null,
                target_department_id: targetDeptId || null,
                reference_urls: referenceUrls,
                custom_prompt: customPromptVal,
                search_google: searchGoogle
            })
        })
        .then(async response => {
            const contentType = response.headers.get('content-type') || '';
            if (!contentType.includes('application/json')) {
                const text = await response.text();
                if (response.status === 504 || response.status === 502) {
                    throw new Error('Server Gateway Timeout (' + response.status + '). The website may have taken too long to respond. Please try again or uncheck Search Grounding to speed up extraction.');
                }
                throw new Error(`Server returned HTTP ${response.status} (non-JSON response). Please check server logs.`);
            }
            const data = await response.json();
            if (!response.ok || !data.success) {
                throw new Error(data.message || `Server error (${response.status})`);
            }
            return data;
        })
        .then(result => {
            if (result.success && result.data) {
                currentExtractedData = result.data;
                currentExtractionMode = result.mode || mode;
                currentTargetOrgId = result.target_organisation_id || (targetOrgId ? targetOrgId : null);
                currentTargetOrgName = result.target_organisation_name || '';
                currentTargetCampusId = result.target_campus_id || null;
                currentTargetCampusName = result.target_campus_name || '';
                currentTargetDeptId = result.target_department_id || null;
                currentTargetDeptName = result.target_department_name || '';

                if (result.masters) {
                    globalMasters = result.masters;
                    populateMasterSelectors(result.masters);
                }
                renderAllFieldsPreview(currentExtractedData, result.selected_type_id || orgTypeId);

                aiStepLoading.classList.add('d-none');
                aiStepPreview.classList.remove('d-none');
            } else {
                throw new Error(result.message || 'Failed to extract data.');
            }
        })
        .catch(error => {
            alert('Error during extraction: ' + error.message);
            aiStepLoading.classList.add('d-none');
            aiStepInput.classList.remove('d-none');
        });
    });

    function populateMasterSelectors(masters) {
        const orgTypeSelect = document.getElementById('aiOrgTypeMaster');
        if (orgTypeSelect && masters.organisation_types) {
            const currentVal = orgTypeSelect.value;
            const allowedTypes = ['university', 'college', 'school'];
            orgTypeSelect.innerHTML = '<option value="">-- Select Master Type --</option>' +
                masters.organisation_types
                    .filter(ot => allowedTypes.includes(ot.title.toLowerCase()))
                    .map(ot => `<option value="${ot.id}">${ot.title}</option>`).join('');
            if (currentVal) orgTypeSelect.value = currentVal;
        }
    }

    // Restart Extraction
    btnRestartExtraction.addEventListener('click', function () {
        aiStepPreview.classList.add('d-none');
        aiStepInput.classList.remove('d-none');
        window.scrollTo({ top: 0, behavior: 'smooth' });
    });

    function toCsv(val) {
        if (!val) return '';
        if (Array.isArray(val)) return val.join(', ');
        return String(val);
    }

    // ----------------------------------------------------
    // Render Review Workspace (Isolated Single-Entity Display)
    // ----------------------------------------------------
    function renderAllFieldsPreview(data, selectedTypeId) {
        const org = data.organisation || {};
        const campuses = data.campuses || [];
        const departments = data.departments || [];
        const courses = data.courses || [];

        const activeTypeId = selectedTypeId || org.organisation_type_id || document.getElementById('aiInputOrgType')?.value || 1;

        // Set Master Type and Toggle Container
        const orgTypeMasterEl = document.getElementById('aiOrgTypeMaster');
        if (orgTypeMasterEl) {
            orgTypeMasterEl.value = activeTypeId;
        }
        toggleOrgTypeFields(activeTypeId);

        // Common Fields
        const orgName = org.name || '';
        if (document.getElementById('aiOrgName')) {
            document.getElementById('aiOrgName').value = orgName;
        }

        const headerTitleEl = document.getElementById('previewOrgHeaderTitle');
        const headerBadgeEl = document.getElementById('previewOrgNameBadge');
        const mode2NoticeEl = document.getElementById('mode2OrgNotice');
        const saveButtons = document.querySelectorAll('.btn-confirm-save-action');

        const tabItemOrg = document.getElementById('tab-item-org');
        const tabItemCampuses = document.getElementById('tab-item-campuses');
        const tabItemDepts = document.getElementById('tab-item-depts');
        const tabItemCourses = document.getElementById('tab-item-courses');
        const tabOrgPane = document.getElementById('tab-org');
        const tabCampusesPane = document.getElementById('tab-campuses');
        const tabDeptsPane = document.getElementById('tab-depts');
        const tabCoursesPane = document.getElementById('tab-courses');
        const unfilledBanner = document.getElementById('unfilledFieldsBanner');

        const tabOrgBtn = document.getElementById('tab-org-btn');
        const tabCampusesBtn = document.getElementById('tab-campuses-btn');
        const tabDeptsBtn = document.getElementById('tab-depts-btn');
        const tabCoursesBtn = document.getElementById('tab-courses-btn');

        // Hide all tabs and panes first
        [tabItemOrg, tabItemCampuses, tabItemDepts, tabItemCourses].forEach(t => t && t.classList.add('d-none'));
        [tabOrgPane, tabCampusesPane, tabDeptsPane, tabCoursesPane].forEach(p => {
            if (p) {
                p.classList.add('d-none');
                p.classList.remove('active', 'show');
            }
        });
        [tabOrgBtn, tabCampusesBtn, tabDeptsBtn, tabCoursesBtn].forEach(b => b && b.classList.remove('active'));

        if (mode2NoticeEl) mode2NoticeEl.classList.add('d-none');
        if (unfilledBanner) unfilledBanner.classList.add('d-none');

        const orgDisplayName = currentTargetOrgName || org.name || 'Selected Organisation';

        // 1. ORGANISATION MODE
        if (currentExtractionMode === 'organisation') {
            if (headerTitleEl) headerTitleEl.innerHTML = `<i class="fas fa-university text-primary me-2"></i>Organisation Profile Preview`;
            if (headerBadgeEl) headerBadgeEl.innerText = (org.short_name || orgName || '') + (org.established_year ? ' (Est. ' + org.established_year + ')' : '');

            if (tabItemOrg) tabItemOrg.classList.remove('d-none');
            if (tabOrgPane) {
                tabOrgPane.classList.remove('d-none');
                tabOrgPane.classList.add('active', 'show');
            }
            if (tabOrgBtn) tabOrgBtn.classList.add('active');

            saveButtons.forEach(b => {
                b.innerHTML = '<i class="fas fa-save me-1"></i> Save Organisation';
            });

            // Populate Org Fields
            if (document.getElementById('aiOrgCentralAuth')) {
                document.getElementById('aiOrgCentralAuth').value = org.central_authority || org.managing_trust_or_society_name || '';
            }
            if (document.getElementById('aiOrgLocation')) {
                document.getElementById('aiOrgLocation').value = org.head_office_location || org.headquarters_location || '';
            }
            if (document.getElementById('aiOrgIsTop')) {
                document.getElementById('aiOrgIsTop').value = (org.is_top !== undefined && org.is_top !== null) ? org.is_top : 0;
            }
            if (document.getElementById('aiOrgCoreValues')) {
                document.getElementById('aiOrgCoreValues').value = toCsv(org.core_values);
            }

            const visibleContainer = document.querySelector('#tab-org .col-12[id$="-fields"][style*="display: block"]') ||
                                     document.querySelector('#tab-org .col-12[id$="-fields"]:not([style*="display: none"])');
            if (visibleContainer) {
                visibleContainer.querySelectorAll('input, select, textarea').forEach(input => {
                    const name = input.getAttribute('name');
                    if (!name || input.type === 'file') return;

                    const baseName = name.replace(/\[\]$/, '');

                    if (org[baseName] !== undefined && org[baseName] !== null) {
                        const val = org[baseName];
                        if (input.type === 'checkbox') {
                            if (name.endsWith('[]')) {
                                const arr = Array.isArray(val) ? val.map(String) : (typeof val === 'string' ? val.split(',').map(s => s.trim()) : []);
                                if (baseName === 'levels_offered') {
                                    const valLower = String(input.value).toLowerCase();
                                    input.checked = arr.some(lvl => {
                                        const l = String(lvl).toLowerCase();
                                        if (valLower === 'ug') return l.includes('undergrad') || l.includes('bachelor') || l === 'ug';
                                        if (valLower === 'pg') return l.includes('postgrad') || l.includes('master') || l === 'pg';
                                        if (valLower === 'doctoral') return l.includes('phd') || l.includes('ph.d') || l.includes('doc') || l.includes('research');
                                        if (valLower === 'diploma') return l.includes('diploma') || l.includes('polytechnic');
                                        return l === valLower;
                                    });
                                } else {
                                    input.checked = arr.some(item => item.toLowerCase() === input.value.toLowerCase());
                                }
                            } else {
                                input.checked = Boolean(val == 1 || val === true || val === '1' || val === 'true');
                            }
                        } else if (input.tagName === 'SELECT') {
                            input.value = String(val);
                            // If direct match failed, try fuzzy/case-insensitive match
                            if (!input.value && input.options) {
                                const valStr = String(val).toLowerCase().trim();
                                for (let i = 0; i < input.options.length; i++) {
                                    const optVal = input.options[i].value.toLowerCase().trim();
                                    const optText = input.options[i].text.toLowerCase().trim();
                                    if (optVal && (optVal === valStr || valStr.includes(optVal) || optVal.includes(valStr) || optText.includes(valStr) || valStr.includes(optText))) {
                                        input.value = input.options[i].value;
                                        break;
                                    }
                                }
                            }
                            if (window.jQuery) {
                                $(input).val(input.value).trigger('change.select2');
                            }
                        } else if (input.tagName === 'TEXTAREA') {
                            input.value = typeof val === 'object' ? JSON.stringify(val) : String(val);
                        } else {
                            if (name.endsWith('[]')) {
                                input.value = toCsv(val);
                            } else {
                                input.value = typeof val === 'object' ? toCsv(val) : String(val);
                            }
                        }
                    }
                });

                // Display Image Previews for Logo & Cover Image
                const logoPreview = visibleContainer.querySelector('.file-preview[data-preview="logo_url"]');
                if (logoPreview && org.logo_url) {
                    logoPreview.innerHTML = `
                        <div class="d-flex align-items-center p-2 border rounded bg-light mb-2">
                            <img src="${org.logo_url}" class="img-thumbnail me-2" style="height: 55px; max-width: 90px; object-fit: contain;" alt="Logo" onerror="this.parentElement.remove()">
                            <div>
                                <span class="badge bg-success-subtle text-success border border-success mb-1" style="font-size: 0.7rem;"><i class="fas fa-check-circle me-1"></i>AI Found Official Logo</span>
                                <div class="small text-muted text-truncate" style="max-width: 250px; font-size: 0.75rem;">${org.logo_url}</div>
                                <input type="hidden" name="ai_extracted_logo_url" value="${org.logo_url}">
                            </div>
                        </div>
                    `;
                }

                const coverPreview = visibleContainer.querySelector('.file-preview[data-preview="cover_image_url"]');
                if (coverPreview && org.cover_image_url) {
                    coverPreview.innerHTML = `
                        <div class="d-flex align-items-center p-2 border rounded bg-light mb-2">
                            <img src="${org.cover_image_url}" class="img-thumbnail me-2" style="height: 55px; max-width: 110px; object-fit: cover;" alt="Cover" onerror="this.parentElement.remove()">
                            <div>
                                <span class="badge bg-success-subtle text-success border border-success mb-1" style="font-size: 0.7rem;"><i class="fas fa-check-circle me-1"></i>AI Found Campus Photo</span>
                                <div class="small text-muted text-truncate" style="max-width: 250px; font-size: 0.75rem;">${org.cover_image_url}</div>
                                <input type="hidden" name="ai_extracted_cover_image_url" value="${org.cover_image_url}">
                            </div>
                        </div>
                    `;
                }
            }

            highlightUnfilledFields();

        // 2. CAMPUS MODE
        } else if (currentExtractionMode === 'campus') {
            if (headerTitleEl) headerTitleEl.innerHTML = `<i class="fas fa-city text-primary me-2"></i>Campuses for <strong>${orgDisplayName}</strong>`;
            if (headerBadgeEl) headerBadgeEl.innerHTML = `<span class="badge bg-info-subtle text-info border border-info px-2 py-1"><i class="fas fa-check-circle me-1"></i>${orgDisplayName}</span>`;

            if (tabItemCampuses) tabItemCampuses.classList.remove('d-none');
            if (tabCampusesPane) {
                tabCampusesPane.classList.remove('d-none');
                tabCampusesPane.classList.add('active', 'show');
            }
            if (tabCampusesBtn) tabCampusesBtn.classList.add('active');

            saveButtons.forEach(b => {
                b.innerHTML = '<i class="fas fa-save me-1"></i> Save Campuses';
            });

            const campusesContainer = document.getElementById('campusesContainer');
            campusesContainer.innerHTML = '';

            const cList = campuses.length > 0 ? campuses : [{ campus_name: orgDisplayName + ' - Main Campus', campus_type: 'Main' }];
            cList.forEach((c, idx) => {
                appendCampusCard(c, idx);
            });

        // 3. DEPARTMENT MODE
        } else if (currentExtractionMode === 'department') {
            const subLabel = currentTargetCampusName ? ` (${currentTargetCampusName})` : '';
            if (headerTitleEl) headerTitleEl.innerHTML = `<i class="fas fa-building text-primary me-2"></i>Departments for <strong>${orgDisplayName}${subLabel}</strong>`;
            if (headerBadgeEl) headerBadgeEl.innerHTML = `<span class="badge bg-warning-subtle text-warning border border-warning px-2 py-1"><i class="fas fa-check-circle me-1"></i>${orgDisplayName}${subLabel}</span>`;

            if (tabItemDepts) tabItemDepts.classList.remove('d-none');
            if (tabDeptsPane) {
                tabDeptsPane.classList.remove('d-none');
                tabDeptsPane.classList.add('active', 'show');
            }
            if (tabDeptsBtn) tabDeptsBtn.classList.add('active');

            saveButtons.forEach(b => {
                b.innerHTML = '<i class="fas fa-save me-1"></i> Save Departments';
            });

            const deptsContainer = document.getElementById('deptsContainer');
            deptsContainer.innerHTML = '';

            const dList = departments.length > 0 ? departments : [{ department_name: 'Department of Computer Science & Engineering' }];
            dList.forEach((d, idx) => {
                appendDeptCard(d, idx);
            });

        // 4. COURSE MODE
        } else if (currentExtractionMode === 'course') {
            const subLabel = (currentTargetDeptName ? ` - ${currentTargetDeptName}` : '') + (currentTargetCampusName ? ` (${currentTargetCampusName})` : '');
            if (headerTitleEl) headerTitleEl.innerHTML = `<i class="fas fa-graduation-cap text-success me-2"></i>Courses & Programs for <strong>${orgDisplayName}${subLabel}</strong>`;
            if (headerBadgeEl) headerBadgeEl.innerHTML = `<span class="badge bg-success-subtle text-success border border-success px-2 py-1"><i class="fas fa-check-circle me-1"></i>${orgDisplayName}</span>`;

            if (tabItemCourses) tabItemCourses.classList.remove('d-none');
            if (tabCoursesPane) {
                tabCoursesPane.classList.remove('d-none');
                tabCoursesPane.classList.add('active', 'show');
            }
            if (tabCoursesBtn) tabCoursesBtn.classList.add('active');

            saveButtons.forEach(b => {
                b.innerHTML = '<i class="fas fa-save me-1"></i> Save Courses';
            });

            const coursesContainer = document.getElementById('coursesContainer');
            coursesContainer.innerHTML = '';

            const crList = courses.length > 0 ? courses : [{ course_name: 'Bachelor of Technology (B.Tech)' }];
            crList.forEach((cr, idx) => {
                appendCourseCard(cr, idx);
            });
        }

        updateAllBadges();
        refreshCourseCampusAndDeptDropdowns();
    }

    // ----------------------------------------------------
    // Unfilled / Non-Autofilled Fields Highlighting System
    // ----------------------------------------------------
    function findLabelForInput(el) {
        if (!el) return null;
        if (el.id) {
            const lbl = document.querySelector(`label[for="${el.id}"]`);
            if (lbl) return lbl;
        }
        const parent = el.closest('.col-md-6, .col-md-12, .col-md-3, .col-md-4, .col-md-5, .col-md-2, .col-md-8, .col-12, .mb-3, .mb-4');
        if (parent) {
            const lbl = parent.querySelector('label');
            if (lbl) return lbl;
        }
        return null;
    }

    function isFieldEmpty(el) {
        if (!el || el.disabled) return false;
        if (el.type === 'hidden' || el.type === 'file' || el.type === 'checkbox' || el.type === 'radio') return false;

        const parentSpecific = el.closest('.col-12[id$="-fields"]');
        if (parentSpecific && (parentSpecific.style.display === 'none' || window.getComputedStyle(parentSpecific).display === 'none')) {
            return false;
        }

        if (el.tagName === 'SELECT') {
            return !el.value || el.value === '';
        }
        return el.value.trim() === '';
    }

    function updateFieldHighlight(el) {
        if (!el) return;
        const empty = isFieldEmpty(el);
        const label = findLabelForInput(el);
        const $select2Selection = window.jQuery ? $(el).next('.select2-container').find('.select2-selection') : null;

        if (empty) {
            el.classList.add('field-not-autofilled');
            if ($select2Selection && $select2Selection.length) {
                $select2Selection.addClass('field-not-autofilled');
            }
            if (label && !label.querySelector('.badge-not-autofilled')) {
                const badge = document.createElement('span');
                badge.className = 'badge badge-not-autofilled ms-2';
                badge.innerHTML = '<i class="fas fa-pen-nib me-1"></i>Not auto-filled';
                label.appendChild(badge);
            }
        } else {
            el.classList.remove('field-not-autofilled');
            if ($select2Selection && $select2Selection.length) {
                $select2Selection.removeClass('field-not-autofilled');
            }
            if (label) {
                const badge = label.querySelector('.badge-not-autofilled');
                if (badge) badge.remove();
            }
        }
    }

    function highlightUnfilledFields() {
        if (currentExtractionMode !== 'organisation') return;

        const tabOrg = document.getElementById('tab-org');
        if (!tabOrg) return;

        const visibleTypeContainer = tabOrg.querySelector('.col-12[id$="-fields"][style*="display: block"]') ||
                                     tabOrg.querySelector('.col-12[id$="-fields"]:not([style*="display: none"])');

        const inputsToCheck = tabOrg.querySelectorAll(
            'input:not([type="hidden"]):not([type="file"]):not([type="checkbox"]):not([type="radio"]), select, textarea'
        );

        inputsToCheck.forEach(el => {
            if (el.disabled) {
                el.classList.remove('field-not-autofilled');
                const lbl = findLabelForInput(el);
                if (lbl) {
                    const b = lbl.querySelector('.badge-not-autofilled');
                    if (b) b.remove();
                }
                return;
            }

            const parentType = el.closest('.col-12[id$="-fields"]');
            if (parentType && visibleTypeContainer && parentType !== visibleTypeContainer) {
                el.classList.remove('field-not-autofilled');
                const lbl = findLabelForInput(el);
                if (lbl) {
                    const b = lbl.querySelector('.badge-not-autofilled');
                    if (b) b.remove();
                }
                return;
            }

            updateFieldHighlight(el);
        });

        updateAllUnfilledCounters();
    }

    function updateAllUnfilledCounters() {
        if (currentExtractionMode !== 'organisation') {
            const banner = document.getElementById('unfilledFieldsBanner');
            const badgeOrg = document.getElementById('badgeOrgUnfilledCount');
            if (banner) banner.classList.add('d-none');
            if (badgeOrg) badgeOrg.classList.add('d-none');
            return;
        }

        const tabOrg = document.getElementById('tab-org');
        if (!tabOrg) return;

        const unfilledInOrg = tabOrg.querySelectorAll('.field-not-autofilled').length;

        const banner = document.getElementById('unfilledFieldsBanner');
        const badge = document.getElementById('unfilledCountBadge');
        if (banner && badge) {
            if (unfilledInOrg > 0) {
                banner.classList.remove('d-none');
                badge.innerText = `${unfilledInOrg} field${unfilledInOrg > 1 ? 's' : ''} to fill manually`;
            } else {
                banner.classList.add('d-none');
            }
        }

        const badgeOrg = document.getElementById('badgeOrgUnfilledCount');
        if (badgeOrg) {
            if (unfilledInOrg > 0) {
                badgeOrg.classList.remove('d-none');
                badgeOrg.innerText = `${unfilledInOrg} missing`;
            } else {
                badgeOrg.classList.add('d-none');
            }
        }

        const subTabButtons = tabOrg.querySelectorAll('.nav-tabs button[data-bs-toggle="tab"]');
        subTabButtons.forEach(btn => {
            const targetSelector = btn.getAttribute('data-bs-target');
            if (!targetSelector) return;
            const pane = tabOrg.querySelector(targetSelector);
            if (!pane) return;

            const paneUnfilled = pane.querySelectorAll('.field-not-autofilled').length;
            let pill = btn.querySelector('.tab-unfilled-pill');
            if (paneUnfilled > 0) {
                if (!pill) {
                    pill = document.createElement('span');
                    pill.className = 'badge rounded-pill bg-warning text-dark ms-2 tab-unfilled-pill';
                    btn.appendChild(pill);
                }
                pill.innerText = paneUnfilled;
                pill.title = `${paneUnfilled} fields need manual entry`;
            } else if (pill) {
                pill.remove();
            }
        });
    }

    // Real-time listener: remove highlight & badge as user fills the fields
    const tabContent = document.getElementById('aiReviewTabContent');
    if (tabContent) {
        ['input', 'change'].forEach(evtType => {
            tabContent.addEventListener(evtType, function (e) {
                const el = e.target;
                if (!el || !el.matches('input:not([type="hidden"]):not([type="file"]):not([type="checkbox"]):not([type="radio"]), select, textarea')) {
                    return;
                }
                updateFieldHighlight(el);
                updateAllUnfilledCounters();
            });
        });
    }

    if (window.jQuery) {
        $(document).on('change select2:select select2:clear', '#tab-org select', function () {
            updateFieldHighlight(this);
            updateAllUnfilledCounters();
        });
    }

    // Jump to next unfilled field button
    const btnJump = document.getElementById('btnJumpNextUnfilled');
    if (btnJump) {
        btnJump.addEventListener('click', function () {
            const nextEl = document.querySelector('#tab-org .field-not-autofilled');
            if (nextEl) {
                const parentPane = nextEl.closest('.tab-pane');
                if (parentPane && !parentPane.classList.contains('active')) {
                    const paneId = parentPane.id;
                    const tabBtn = document.querySelector(`button[data-bs-target="#${paneId}"]`);
                    if (tabBtn) {
                        tabBtn.click();
                    }
                }
                setTimeout(() => {
                    nextEl.scrollIntoView({ behavior: 'smooth', block: 'center' });
                    nextEl.focus();
                }, 150);
            } else {
                alert('All organisation fields have been filled!');
            }
        });
    }

    // Append Campus Card
    function appendCampusCard(c = {}, idx = Date.now()) {
        const div = document.createElement('div');
        div.className = 'card border mb-3 campus-item';
        div.innerHTML = `
            <div class="card-header bg-light d-flex justify-content-between align-items-center py-2">
                <span class="fw-bold"><i class="fas fa-map-marker-alt text-primary me-2"></i>Campus: <span class="campus-title-preview">${c.campus_name || 'Campus'}</span></span>
                <button type="button" class="btn btn-sm btn-outline-danger btn-remove-campus">
                    <i class="fas fa-trash me-1"></i> Remove
                </button>
            </div>
            <div class="card-body">
                <div class="row g-3">
                    <div class="col-md-5">
                        <label class="form-label fw-bold small">Campus Name</label>
                        <input type="text" class="form-control c-name" value="${c.campus_name || ''}">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label fw-bold small">Campus Type</label>
                        <select class="form-select c-type">
                            <option value="Main" ${c.campus_type === 'Main' ? 'selected' : ''}>Main</option>
                            <option value="Regional" ${c.campus_type === 'Regional' ? 'selected' : ''}>Regional</option>
                            <option value="Satellite" ${c.campus_type === 'Satellite' ? 'selected' : ''}>Satellite</option>
                        </select>
                    </div>
                    <div class="col-md-2">
                        <label class="form-label fw-bold small">Established Year</label>
                        <input type="number" class="form-control c-est" value="${c.established_year || ''}">
                    </div>
                    <div class="col-md-2">
                        <label class="form-label fw-bold small">Area (Acres)</label>
                        <input type="number" step="0.1" class="form-control c-acres" value="${c.campus_area_acres || ''}">
                    </div>

                    <div class="col-md-3">
                        <label class="form-label fw-bold small">City</label>
                        <input type="text" class="form-control c-city" value="${c.city || ''}">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label fw-bold small">State</label>
                        <input type="text" class="form-control c-state" value="${c.state || ''}">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label fw-bold small">Country</label>
                        <input type="text" class="form-control c-country" value="${c.country || 'India'}">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label fw-bold small">Pincode</label>
                        <input type="text" class="form-control c-pincode" value="${c.pincode || ''}">
                    </div>

                    <div class="col-md-6">
                        <label class="form-label fw-bold small">Full Address</label>
                        <input type="text" class="form-control c-address" value="${c.full_address || ''}">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label fw-bold small">Nearest Transport Hub</label>
                        <input type="text" class="form-control c-hub" value="${c.nearest_transport_hub || ''}">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label fw-bold small">Google Maps URL</label>
                        <input type="url" class="form-control c-map" value="${c.google_map_url || ''}">
                    </div>

                    <div class="col-md-2">
                        <label class="form-label fw-bold small">Classrooms Count</label>
                        <input type="number" class="form-control c-classrooms" value="${c.classrooms_count || ''}">
                    </div>
                    <div class="col-md-2">
                        <label class="form-label fw-bold small">Academic Blocks</label>
                        <input type="number" class="form-control c-blocks" value="${c.academic_blocks_count || ''}">
                    </div>
                    <div class="col-md-2">
                        <label class="form-label fw-bold small">Labs Count</label>
                        <input type="number" class="form-control c-labs" value="${c.laboratories_count || ''}">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label fw-bold small">Hostel Type</label>
                        <select class="form-select c-hostel-type">
                            <option value="">None / Not specified</option>
                            <option value="Both" ${c.hostel_type === 'Both' ? 'selected' : ''}>Both (Boys & Girls)</option>
                            <option value="Boys" ${c.hostel_type === 'Boys' ? 'selected' : ''}>Boys Only</option>
                            <option value="Girls" ${c.hostel_type === 'Girls' ? 'selected' : ''}>Girls Only</option>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label fw-bold small">Hostel Capacity</label>
                        <input type="number" class="form-control c-hostel-cap" value="${c.hostel_capacity || ''}">
                    </div>

                    <div class="col-12 py-2 px-3 border rounded bg-light my-2">
                        <div class="d-flex flex-wrap gap-4">
                            <div class="form-check form-switch">
                                <input class="form-check-input c-smart-class" type="checkbox" ${c.smart_classrooms ? 'checked' : ''}>
                                <label class="form-check-label small fw-bold">Smart Classrooms</label>
                            </div>
                            <div class="form-check form-switch">
                                <input class="form-check-input c-library" type="checkbox" ${c.library_available ? 'checked' : ''}>
                                <label class="form-check-label small fw-bold">Library Available</label>
                            </div>
                            <div class="form-check form-switch">
                                <input class="form-check-input c-digital-lib" type="checkbox" ${c.digital_library_access ? 'checked' : ''}>
                                <label class="form-check-label small fw-bold">Digital Library</label>
                            </div>
                            <div class="form-check form-switch">
                                <input class="form-check-input c-hostel-avail" type="checkbox" ${c.hostel_available ? 'checked' : ''}>
                                <label class="form-check-label small fw-bold">Hostel Available</label>
                            </div>
                            <div class="form-check form-switch">
                                <input class="form-check-input c-transport" type="checkbox" ${c.transport_available ? 'checked' : ''}>
                                <label class="form-check-label small fw-bold">Transport Available</label>
                            </div>
                            <div class="form-check form-switch">
                                <input class="form-check-input c-medical" type="checkbox" ${c.medical_facility_available ? 'checked' : ''}>
                                <label class="form-check-label small fw-bold">Medical Facility</label>
                            </div>
                            <div class="form-check form-switch">
                                <input class="form-check-input c-cctv" type="checkbox" ${c.cctv_coverage ? 'checked' : ''}>
                                <label class="form-check-label small fw-bold">CCTV Coverage</label>
                            </div>
                            <div class="form-check form-switch">
                                <input class="form-check-input c-fire" type="checkbox" ${c.fire_safety_certified ? 'checked' : ''}>
                                <label class="form-check-label small fw-bold">Fire Safety Certified</label>
                            </div>
                        </div>
                    </div>

                    <div class="col-md-4">
                        <label class="form-label fw-bold small">Sports Facilities</label>
                        <input type="text" class="form-control c-sports" value="${toCsv(c.sports_facilities)}" placeholder="Cricket, Gym, Pool...">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label fw-bold small">Campus Email</label>
                        <input type="email" class="form-control c-email" value="${c.campus_email || ''}">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label fw-bold small">Campus Contact Numbers</label>
                        <input type="text" class="form-control c-phones" value="${toCsv(c.campus_contact_numbers)}">
                    </div>
                </div>
            </div>
        `;

        div.querySelector('.btn-remove-campus').addEventListener('click', function () {
            div.remove();
            updateAllBadges();
            refreshCourseCampusAndDeptDropdowns();
        });

        div.querySelector('.c-name').addEventListener('input', function (e) {
            div.querySelector('.campus-title-preview').innerText = e.target.value || 'Campus';
            refreshCourseCampusAndDeptDropdowns();
        });

        document.getElementById('campusesContainer').appendChild(div);
    }

    // Append Department Card
    function appendDeptCard(d = {}, idx = Date.now()) {
        const div = document.createElement('div');
        div.className = 'card border mb-3 dept-item';
        div.innerHTML = `
            <div class="card-header bg-light d-flex justify-content-between align-items-center py-2">
                <span class="fw-bold"><i class="fas fa-building text-primary me-2"></i>Department: <span class="dept-title-preview">${d.department_name || 'Department'}</span></span>
                <button type="button" class="btn btn-sm btn-outline-danger btn-remove-dept">
                    <i class="fas fa-trash me-1"></i> Remove
                </button>
            </div>
            <div class="card-body">
                <div class="row g-3">
                    <div class="col-md-5">
                        <label class="form-label fw-bold small">Department / Faculty Name</label>
                        <input type="text" class="form-control d-name" value="${d.department_name || ''}">
                    </div>
                    <div class="col-md-2">
                        <label class="form-label fw-bold small">Code</label>
                        <input type="text" class="form-control d-code" value="${d.department_code || ''}">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label fw-bold small">Department Type</label>
                        <select class="form-select d-type">
                            <option value="Academic" ${(!d.department_type || d.department_type === 'Academic') ? 'selected' : ''}>Academic</option>
                            <option value="Clinical" ${d.department_type === 'Clinical' ? 'selected' : ''}>Clinical</option>
                            <option value="Research" ${d.department_type === 'Research' ? 'selected' : ''}>Research</option>
                            <option value="Interdisciplinary" ${d.department_type === 'Interdisciplinary' ? 'selected' : ''}>Interdisciplinary</option>
                        </select>
                    </div>
                    <div class="col-md-2">
                        <label class="form-label fw-bold small">Established Year</label>
                        <input type="number" class="form-control d-est" value="${d.established_year || ''}">
                    </div>

                    <div class="col-md-4">
                        <label class="form-label fw-bold small">HOD Name</label>
                        <input type="text" class="form-control d-hod-name" value="${d.head_of_department_name || ''}">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label fw-bold small">HOD Designation</label>
                        <input type="text" class="form-control d-hod-desig" value="${d.head_of_department_designation || 'Professor & Head'}">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label fw-bold small">HOD Email</label>
                        <input type="email" class="form-control d-hod-email" value="${d.hod_email || ''}">
                    </div>
                    <div class="col-md-2">
                        <label class="form-label fw-bold small">Faculty Count</label>
                        <input type="number" class="form-control d-faculty-count" value="${d.faculty_count || ''}">
                    </div>

                    <div class="col-md-3">
                        <label class="form-label fw-bold small">Discipline Area</label>
                        <input type="text" class="form-control d-discipline-area" value="${d.discipline_area || ''}">
                    </div>
                    <div class="col-md-5">
                        <label class="form-label fw-bold small">Specializations Supported</label>
                        <input type="text" class="form-control d-specs" value="${toCsv(d.specializations_supported)}">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label fw-bold small">Education Levels</label>
                        <input type="text" class="form-control d-levels" value="${toCsv(d.education_levels_supported)}">
                    </div>

                    <div class="col-md-3">
                        <label class="form-label fw-bold small">Labs Count</label>
                        <input type="number" class="form-control d-labs" value="${d.department_labs_count || ''}">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label fw-bold small">Publications Count</label>
                        <input type="number" class="form-control d-pubs" value="${d.research_publications_count || ''}">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label fw-bold small">Funded Projects</label>
                        <input type="number" class="form-control d-projects" value="${d.funded_projects_count || ''}">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label fw-bold small">Patents Filed</label>
                        <input type="number" class="form-control d-patents" value="${d.patents_filed_count || ''}">
                    </div>

                    <div class="col-12 py-2 px-3 border rounded bg-light my-2">
                        <div class="d-flex flex-wrap gap-4">
                            <div class="form-check form-switch">
                                <input class="form-check-input d-phd" type="checkbox" ${d.phd_supervision_available ? 'checked' : ''}>
                                <label class="form-check-label small fw-bold">PhD Supervision Available</label>
                            </div>
                            <div class="form-check form-switch">
                                <input class="form-check-input d-industry" type="checkbox" ${d.industry_collaboration_supported ? 'checked' : ''}>
                                <label class="form-check-label small fw-bold">Industry Collaboration</label>
                            </div>
                            <div class="form-check form-switch">
                                <input class="form-check-input d-interdisc" type="checkbox" ${d.is_interdisciplinary ? 'checked' : ''}>
                                <label class="form-check-label small fw-bold">Interdisciplinary</label>
                            </div>
                            <div class="form-check form-switch">
                                <input class="form-check-input d-spec-labs" type="checkbox" ${d.specialized_labs_available ? 'checked' : ''}>
                                <label class="form-check-label small fw-bold">Specialized Labs</label>
                            </div>
                        </div>
                    </div>

                    <div class="col-md-12">
                        <label class="form-label fw-bold small">About Department</label>
                        <textarea class="form-control d-about" rows="2">${d.about_department || ''}</textarea>
                    </div>
                </div>
            </div>
        `;

        div.querySelector('.btn-remove-dept').addEventListener('click', function () {
            div.remove();
            updateAllBadges();
            refreshCourseCampusAndDeptDropdowns();
        });

        div.querySelector('.d-name').addEventListener('input', function (e) {
            div.querySelector('.dept-title-preview').innerText = e.target.value || 'Department';
            refreshCourseCampusAndDeptDropdowns();
        });

        document.getElementById('deptsContainer').appendChild(div);
    }

    // Append Course Card with Master Auto-Selection
    function appendCourseCard(cr = {}, idx = Date.now()) {
        const rawAiName = cr.course_name || '';
        const rawAiShort = cr.short_name || '';
        const rawAiLevel = cr.program_level || '';
        const rawAiStream = cr.stream || '';
        const rawAiDiscipline = cr.discipline || '';

        const matchedCourse = findBestCourseMatch(rawAiName, rawAiShort, rawAiLevel, rawAiStream, rawAiDiscipline);
        const selectedCourseId = matchedCourse ? matchedCourse.id : (cr.course_id || '');

        const defaultLevelId = matchedCourse && matchedCourse.program_level_id 
            ? matchedCourse.program_level_id 
            : findBestLevelMatch(rawAiLevel);

        const defaultStreamId = matchedCourse && matchedCourse.stream_offered_id 
            ? matchedCourse.stream_offered_id 
            : findBestStreamMatch(rawAiStream);

        const defaultDisciplineId = matchedCourse && matchedCourse.discipline_id 
            ? matchedCourse.discipline_id 
            : findBestDisciplineMatch(rawAiDiscipline);

        const defaultDuration = (matchedCourse && matchedCourse.duration) ? matchedCourse.duration : (cr.duration || '3 Years');

        const availableCampuses = getAvailableCampuses();
        const availableDepts = getAvailableDepts();
        let selectedCampus = cr.campus_name || currentTargetCampusName || availableCampuses[0] || 'Main Campus';
        let selectedDept = cr.department_name || currentTargetDeptName || availableDepts[0] || 'General Faculty';

        if (!availableCampuses.includes(selectedCampus)) {
            const mC = availableCampuses.find(c => normalizeStr(c).includes(normalizeStr(selectedCampus)) || normalizeStr(selectedCampus).includes(normalizeStr(c)));
            if (mC) selectedCampus = mC;
            else availableCampuses.unshift(selectedCampus);
        }

        if (!availableDepts.includes(selectedDept)) {
            const mD = availableDepts.find(d => normalizeStr(d).includes(normalizeStr(selectedDept)) || normalizeStr(selectedDept).includes(normalizeStr(d)));
            if (mD) selectedDept = mD;
            else if (currentTargetDeptName && availableDepts.includes(currentTargetDeptName)) selectedDept = currentTargetDeptName;
            else availableDepts.unshift(selectedDept);
        }

        const div = document.createElement('div');
        div.className = 'card border mb-3 course-item';
        div.innerHTML = `
            <div class="card-header bg-light d-flex justify-content-between align-items-center py-2">
                <div class="d-flex align-items-center gap-2">
                    <span class="fw-bold"><i class="fas fa-graduation-cap text-success me-1"></i>Course: <span class="course-title-preview">${matchedCourse ? matchedCourse.name : (rawAiName || 'Course')}</span></span>
                    <span class="match-badge-container">
                        ${selectedCourseId 
                            ? '<span class="badge bg-success-subtle text-success border border-success-subtle px-2 py-1"><i class="fas fa-check-circle me-1"></i>Master Auto-Selected</span>' 
                            : '<span class="badge bg-warning-subtle text-warning border border-warning-subtle px-2 py-1"><i class="fas fa-exclamation-triangle me-1"></i>Select Master Course</span>'}
                    </span>
                </div>
                <button type="button" class="btn btn-sm btn-outline-danger btn-remove-course">
                    <i class="fas fa-trash me-1"></i> Remove
                </button>
            </div>
            <div class="card-body">
                <div class="row g-3">
                    <div class="col-md-6">
                        <label class="form-label fw-bold small">
                            Master Course <span class="text-danger">*</span> (From Database Masters)
                        </label>
                        <select class="form-select cr-course-id">
                            <option value="">-- Select Master Course --</option>
                            ${(globalMasters.courses || []).map(c => `
                                <option value="${c.id}" ${String(c.id) === String(selectedCourseId) ? 'selected' : ''}>
                                    ${c.name}
                                </option>
                            `).join('')}
                        </select>
                        <div class="mt-1 small text-muted">
                            AI Extracted Name: <span class="fw-bold cr-raw-name">${rawAiName || 'N/A'}</span>
                        </div>
                    </div>

                    <div class="col-md-3">
                        <label class="form-label fw-bold small">Short Name / Abbr</label>
                        <input type="text" class="form-control cr-short-name" value="${rawAiShort}">
                    </div>

                    <div class="col-md-3">
                        <label class="form-label fw-bold small">Program Level</label>
                        <select class="form-select cr-level-id">
                            <option value="">-- Select Program Level --</option>
                            ${(globalMasters.program_levels || []).map(l => `
                                <option value="${l.id}" ${String(l.id) === String(defaultLevelId) ? 'selected' : ''}>
                                    ${l.title}
                                </option>
                            `).join('')}
                        </select>
                    </div>

                    <div class="col-md-4">
                        <label class="form-label fw-bold small">Stream Offered</label>
                        <select class="form-select cr-stream-id">
                            <option value="">-- Select Stream --</option>
                            ${(globalMasters.streams || []).map(s => `
                                <option value="${s.id}" ${String(s.id) === String(defaultStreamId) ? 'selected' : ''}>
                                    ${s.title}
                                </option>
                            `).join('')}
                        </select>
                    </div>

                    <div class="col-md-4">
                        <label class="form-label fw-bold small">Discipline</label>
                        <select class="form-select cr-discipline-id">
                            <option value="">-- Select Discipline --</option>
                            ${(globalMasters.disciplines || []).map(d => `
                                <option value="${d.id}" ${String(d.id) === String(defaultDisciplineId) ? 'selected' : ''}>
                                    ${d.title}
                                </option>
                            `).join('')}
                        </select>
                    </div>

                    <div class="col-md-4">
                        <label class="form-label fw-bold small">Specialization</label>
                        <input type="text" class="form-control cr-spec" value="${cr.specialization || ''}">
                    </div>

                    <div class="col-md-4">
                        <label class="form-label fw-bold small">Campus</label>
                        <select class="form-select cr-campus-select">
                            ${availableCampuses.map(c => `<option value="${c}" ${c === selectedCampus ? 'selected' : ''}>${c}</option>`).join('')}
                        </select>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label fw-bold small">Department</label>
                        <select class="form-select cr-dept-select">
                            ${availableDepts.map(d => `<option value="${d}" ${d === selectedDept ? 'selected' : ''}>${d}</option>`).join('')}
                        </select>
                    </div>

                    <div class="col-md-2">
                        <label class="form-label fw-bold small">Duration</label>
                        <input type="text" class="form-control cr-duration" value="${defaultDuration}">
                    </div>
                    <div class="col-md-2">
                        <label class="form-label fw-bold small">Mode</label>
                        <select class="form-select cr-mode">
                            <option value="Regular" ${cr.mode === 'Regular' || !cr.mode ? 'selected' : ''}>Regular</option>
                            <option value="Online" ${cr.mode === 'Online' ? 'selected' : ''}>Online</option>
                            <option value="Distance" ${cr.mode === 'Distance' ? 'selected' : ''}>Distance</option>
                            <option value="Part-time" ${cr.mode === 'Part-time' ? 'selected' : ''}>Part-time</option>
                        </select>
                    </div>

                    <div class="col-md-3">
                        <label class="form-label fw-bold small">Fees (Per Year ₹)</label>
                        <input type="text" class="form-control cr-fees" value="${cr.fees || ''}">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label fw-bold small">Total Fees (₹)</label>
                        <input type="text" class="form-control cr-total-fees" value="${cr.total_fees || ''}">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label fw-bold small">Admission Fee (₹)</label>
                        <input type="text" class="form-control cr-adm-fee" value="${cr.admission_fee || ''}">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label fw-bold small">Annual Fee Range</label>
                        <input type="text" class="form-control cr-fee-range" value="${cr.annual_fee_range || ''}">
                    </div>

                    <div class="col-md-2">
                        <label class="form-label fw-bold small">Rating</label>
                        <input type="text" class="form-control cr-rating" value="${cr.rating || '4.5'}">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label fw-bold small">ROI / Package</label>
                        <input type="text" class="form-control cr-roi" value="${cr.roi || ''}">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label fw-bold small">Entrance Exams</label>
                        <input type="text" class="form-control cr-exams" value="${cr.entrance_exams || ''}">
                    </div>

                    <div class="col-md-6">
                        <label class="form-label fw-bold small">Eligibility Criteria</label>
                        <textarea class="form-control cr-eligibility" rows="2">${cr.eligibility || ''}</textarea>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label fw-bold small">Admission Process</label>
                        <textarea class="form-control cr-adm-process" rows="2">${cr.admission_process || ''}</textarea>
                    </div>

                    <div class="col-md-12">
                        <label class="form-label fw-bold small">Placement Details</label>
                        <input type="text" class="form-control cr-placements" value="${cr.placement_details || ''}">
                    </div>

                    <div class="col-12 py-2 px-3 border rounded bg-light my-2">
                        <div class="d-flex flex-wrap gap-4">
                            <div class="form-check form-switch">
                                <input class="form-check-input cr-installment" type="checkbox" ${cr.installment_available ? 'checked' : ''}>
                                <label class="form-check-label small fw-bold">Installment Available</label>
                            </div>
                            <div class="form-check form-switch">
                                <input class="form-check-input cr-scholarship" type="checkbox" ${cr.scholarship_available ? 'checked' : ''}>
                                <label class="form-check-label small fw-bold">Scholarship Available</label>
                            </div>
                            <div class="form-check form-switch">
                                <input class="form-check-input cr-refund" type="checkbox" ${cr.refund_policy_available ? 'checked' : ''}>
                                <label class="form-check-label small fw-bold">Refund Policy Available</label>
                            </div>
                            <div class="form-check form-switch">
                                <input class="form-check-input cr-provisional" type="checkbox" ${cr.provisional_admission ? 'checked' : ''}>
                                <label class="form-check-label small fw-bold">Provisional Admission</label>
                            </div>
                        </div>
                    </div>

                    <div class="col-md-12">
                        <label class="form-label fw-bold small">Course Overview</label>
                        <textarea class="form-control cr-overview" rows="2">${cr.overview || ''}</textarea>
                    </div>
                </div>
            </div>
        `;

        const courseSelect = div.querySelector('.cr-course-id');
        courseSelect.addEventListener('change', function () {
            const chosenId = this.value;
            const chosen = (globalMasters.courses || []).find(c => String(c.id) === String(chosenId));
            const badgeContainer = div.querySelector('.match-badge-container');
            const previewTitle = div.querySelector('.course-title-preview');

            if (chosen) {
                previewTitle.innerText = chosen.name;
                badgeContainer.innerHTML = '<span class="badge bg-success-subtle text-success border border-success-subtle px-2 py-1"><i class="fas fa-check-circle me-1"></i>Master Selected</span>';

                if (chosen.program_level_id) {
                    div.querySelector('.cr-level-id').value = chosen.program_level_id;
                }
                if (chosen.stream_offered_id) {
                    div.querySelector('.cr-stream-id').value = chosen.stream_offered_id;
                }
                if (chosen.discipline_id) {
                    div.querySelector('.cr-discipline-id').value = chosen.discipline_id;
                }
                if (chosen.duration) {
                    div.querySelector('.cr-duration').value = chosen.duration;
                }
            } else {
                previewTitle.innerText = div.querySelector('.cr-raw-name').innerText || 'Course';
                badgeContainer.innerHTML = '<span class="badge bg-warning-subtle text-warning border border-warning-subtle px-2 py-1"><i class="fas fa-exclamation-triangle me-1"></i>Select Master Course</span>';
            }
        });

        div.querySelector('.btn-remove-course').addEventListener('click', function () {
            div.remove();
            updateAllBadges();
        });

        document.getElementById('coursesContainer').appendChild(div);
    }

    // Add buttons
    document.getElementById('btnAddCampusCard').addEventListener('click', function () {
        appendCampusCard({ campus_name: 'New Campus' });
        updateAllBadges();
        refreshCourseCampusAndDeptDropdowns();
    });
    document.getElementById('btnAddDeptCard').addEventListener('click', function () {
        appendDeptCard({ department_name: 'New Department' });
        updateAllBadges();
        refreshCourseCampusAndDeptDropdowns();
    });
    document.getElementById('btnAddCourseCard').addEventListener('click', function () {
        appendCourseCard({ course_name: 'New Degree Course' });
        updateAllBadges();
    });

    function updateAllBadges() {
        const cCount = document.querySelectorAll('.campus-item').length;
        const dCount = document.querySelectorAll('.dept-item').length;
        const crCount = document.querySelectorAll('.course-item').length;

        const bCampuses = document.getElementById('badgeCampusesCount');
        const bDepts = document.getElementById('badgeDeptsCount');
        const bCourses = document.getElementById('badgeCoursesCount');

        if (bCampuses) bCampuses.innerText = cCount;
        if (bDepts) bDepts.innerText = dCount;
        if (bCourses) bCourses.innerText = crCount;
    }

    // ----------------------------------------------------
    // Confirm & Save Records
    // ----------------------------------------------------
    document.querySelectorAll('.btn-confirm-save-action').forEach(btn => {
        btn.addEventListener('click', function () {
            const saveButtons = document.querySelectorAll('.btn-confirm-save-action');
            saveButtons.forEach(b => {
                b.disabled = true;
                b.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span> Saving...';
            });

            const mode = currentExtractionMode || 'organisation';
            let payload = {
                mode: mode,
                target_organisation_id: currentTargetOrgId || null,
                target_campus_id: currentTargetCampusId || null,
                target_department_id: currentTargetDeptId || null,
                organisation: {},
                campuses: [],
                departments: [],
                courses: []
            };

            // 1. ORGANISATION MODE SAVE
            if (mode === 'organisation') {
                const orgName = document.getElementById('aiOrgName') ? document.getElementById('aiOrgName').value.trim() : '';
                if (!orgName) {
                    alert('Please enter an Organisation Name before saving.');
                    saveButtons.forEach(b => {
                        b.disabled = false;
                        b.innerHTML = '<i class="fas fa-save me-1"></i> Save Organisation';
                    });
                    return;
                }

                const orgTypeSelect = document.getElementById('aiOrgTypeMaster');
                const selectedOrgTypeId = orgTypeSelect ? orgTypeSelect.value : '';
                const selectedOrgTypeName = orgTypeSelect && orgTypeSelect.selectedIndex >= 0 
                    ? orgTypeSelect.options[orgTypeSelect.selectedIndex].text 
                    : 'University';

                const orgPayload = {
                    name: orgName,
                    organisation_type_id: selectedOrgTypeId || null,
                    organisation_type: selectedOrgTypeName,
                    central_authority: document.getElementById('aiOrgCentralAuth') ? document.getElementById('aiOrgCentralAuth').value.trim() : '',
                    head_office_location: document.getElementById('aiOrgLocation') ? document.getElementById('aiOrgLocation').value.trim() : '',
                    is_top: document.getElementById('aiOrgIsTop') ? document.getElementById('aiOrgIsTop').value : 0,
                    core_values: document.getElementById('aiOrgCoreValues') ? document.getElementById('aiOrgCoreValues').value.split(',').map(s => s.trim()).filter(Boolean) : []
                };

                const activeContainer = document.querySelector('#tab-org .col-12[id$="-fields"][style*="display: block"]') ||
                                        document.querySelector('#tab-org .col-12[id$="-fields"]:not([style*="display: none"])');
                if (activeContainer) {
                    activeContainer.querySelectorAll('input, select, textarea').forEach(input => {
                        const name = input.getAttribute('name');
                        if (!name || input.type === 'file' || input.disabled) return;

                        const isArray = name.endsWith('[]');
                        const baseName = name.replace(/\[\]$/, '');

                        if (input.type === 'checkbox') {
                            if (isArray) {
                                if (!orgPayload[baseName]) orgPayload[baseName] = [];
                                if (input.checked) {
                                    orgPayload[baseName].push(input.value);
                                }
                            } else {
                                orgPayload[baseName] = input.checked;
                            }
                        } else if (input.type === 'radio') {
                            if (input.checked) {
                                orgPayload[baseName] = input.value;
                            }
                        } else {
                            const val = input.value.trim();
                            if (isArray) {
                                orgPayload[baseName] = val ? val.split(',').map(s => s.trim()).filter(Boolean) : [];
                            } else {
                                orgPayload[baseName] = val;
                            }
                        }
                    });
                }

                const extractedLogo = document.querySelector('input[name="ai_extracted_logo_url"]')?.value || currentExtractedData?.organisation?.logo_url;
                if (extractedLogo && !orgPayload.logo_url) {
                    orgPayload.logo_url = extractedLogo;
                }
                const extractedCover = document.querySelector('input[name="ai_extracted_cover_image_url"]')?.value || currentExtractedData?.organisation?.cover_image_url;
                if (extractedCover && !orgPayload.cover_image_url) {
                    orgPayload.cover_image_url = extractedCover;
                }

                payload.organisation = orgPayload;

            // 2. CAMPUS MODE SAVE
            } else if (mode === 'campus') {
                document.querySelectorAll('.campus-item').forEach(el => {
                    const name = el.querySelector('.c-name')?.value.trim();
                    if (name) {
                        payload.campuses.push({
                            campus_name: name,
                            campus_type: el.querySelector('.c-type')?.value || 'Main',
                            established_year: el.querySelector('.c-est')?.value.trim(),
                            campus_area_acres: el.querySelector('.c-acres')?.value.trim(),
                            city: el.querySelector('.c-city')?.value.trim(),
                            state: el.querySelector('.c-state')?.value.trim(),
                            country: el.querySelector('.c-country')?.value.trim(),
                            pincode: el.querySelector('.c-pincode')?.value.trim(),
                            full_address: el.querySelector('.c-address')?.value.trim(),
                            nearest_transport_hub: el.querySelector('.c-hub')?.value.trim(),
                            google_map_url: el.querySelector('.c-map')?.value.trim(),
                            classrooms_count: el.querySelector('.c-classrooms')?.value.trim(),
                            academic_blocks_count: el.querySelector('.c-blocks')?.value.trim(),
                            laboratories_count: el.querySelector('.c-labs')?.value.trim(),
                            hostel_type: el.querySelector('.c-hostel-type')?.value,
                            hostel_capacity: el.querySelector('.c-hostel-cap')?.value.trim(),
                            smart_classrooms: el.querySelector('.c-smart-class')?.checked || false,
                            library_available: el.querySelector('.c-library')?.checked || false,
                            digital_library_access: el.querySelector('.c-digital-lib')?.checked || false,
                            hostel_available: el.querySelector('.c-hostel-avail')?.checked || false,
                            transport_available: el.querySelector('.c-transport')?.checked || false,
                            medical_facility_available: el.querySelector('.c-medical')?.checked || false,
                            cctv_coverage: el.querySelector('.c-cctv')?.checked || false,
                            fire_safety_certified: el.querySelector('.c-fire')?.checked || false,
                            sports_facilities: el.querySelector('.c-sports')?.value.split(',').map(s => s.trim()).filter(Boolean),
                            campus_email: el.querySelector('.c-email')?.value.trim(),
                            campus_contact_numbers: el.querySelector('.c-phones')?.value.split(',').map(s => s.trim()).filter(Boolean)
                        });
                    }
                });

            // 3. DEPARTMENT MODE SAVE
            } else if (mode === 'department') {
                document.querySelectorAll('.dept-item').forEach(el => {
                    const name = el.querySelector('.d-name')?.value.trim();
                    if (name) {
                        payload.departments.push({
                            department_name: name,
                            department_code: el.querySelector('.d-code')?.value.trim(),
                            department_type: el.querySelector('.d-type')?.value.trim(),
                            established_year: el.querySelector('.d-est')?.value.trim(),
                            head_of_department_name: el.querySelector('.d-hod-name')?.value.trim(),
                            head_of_department_designation: el.querySelector('.d-hod-desig')?.value.trim(),
                            hod_email: el.querySelector('.d-hod-email')?.value.trim(),
                            faculty_count: el.querySelector('.d-faculty-count')?.value.trim(),
                            discipline_area: el.querySelector('.d-discipline-area')?.value.trim(),
                            specializations_supported: el.querySelector('.d-specs')?.value.split(',').map(s => s.trim()).filter(Boolean),
                            education_levels_supported: el.querySelector('.d-levels')?.value.split(',').map(s => s.trim()).filter(Boolean),
                            department_labs_count: el.querySelector('.d-labs')?.value.trim(),
                            research_publications_count: el.querySelector('.d-pubs')?.value.trim(),
                            funded_projects_count: el.querySelector('.d-projects')?.value.trim(),
                            patents_filed_count: el.querySelector('.d-patents')?.value.trim(),
                            phd_supervision_available: el.querySelector('.d-phd')?.checked || false,
                            industry_collaboration_supported: el.querySelector('.d-industry')?.checked || false,
                            is_interdisciplinary: el.querySelector('.d-interdisc')?.checked || false,
                            specialized_labs_available: el.querySelector('.d-spec-labs')?.checked || false,
                            about_department: el.querySelector('.d-about')?.value.trim()
                        });
                    }
                });

            // 4. COURSE MODE SAVE
            } else if (mode === 'course') {
                document.querySelectorAll('.course-item').forEach(el => {
                    const courseId = el.querySelector('.cr-course-id')?.value;
                    const selectedMaster = (globalMasters.courses || []).find(c => String(c.id) === String(courseId));
                    const courseName = selectedMaster ? selectedMaster.name : (el.querySelector('.cr-raw-name')?.innerText || 'Degree Course');

                    payload.courses.push({
                        course_id: courseId || null,
                        course_name: courseName,
                        short_name: el.querySelector('.cr-short-name')?.value.trim(),
                        program_level_id: el.querySelector('.cr-level-id')?.value || null,
                        stream_offered_id: el.querySelector('.cr-stream-id')?.value || null,
                        discipline_id: el.querySelector('.cr-discipline-id')?.value || null,
                        specialization: el.querySelector('.cr-spec')?.value.trim(),
                        campus_name: el.querySelector('.cr-campus-select')?.value || '',
                        department_name: el.querySelector('.cr-dept-select')?.value || '',
                        duration: el.querySelector('.cr-duration')?.value.trim(),
                        mode: el.querySelector('.cr-mode')?.value.trim() || 'Regular',
                        fees: el.querySelector('.cr-fees')?.value.trim(),
                        total_fees: el.querySelector('.cr-total-fees')?.value.trim(),
                        admission_fee: el.querySelector('.cr-adm-fee')?.value.trim(),
                        annual_fee_range: el.querySelector('.cr-fee-range')?.value.trim(),
                        rating: el.querySelector('.cr-rating')?.value.trim(),
                        roi: el.querySelector('.cr-roi')?.value.trim(),
                        eligibility: el.querySelector('.cr-eligibility')?.value.trim(),
                        admission_process: el.querySelector('.cr-adm-process')?.value.trim(),
                        entrance_exams: el.querySelector('.cr-exams')?.value.trim(),
                        placement_details: el.querySelector('.cr-placements')?.value.trim(),
                        installment_available: el.querySelector('.cr-installment')?.checked || false,
                        scholarship_available: el.querySelector('.cr-scholarship')?.checked || false,
                        refund_policy_available: el.querySelector('.cr-refund')?.checked || false,
                        provisional_admission: el.querySelector('.cr-provisional')?.checked || false,
                        overview: el.querySelector('.cr-overview')?.value.trim()
                    });
                });
            }

            fetch("{{ route('admin.ai-organisations.store') }}", {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': "{{ csrf_token() }}"
                },
                body: JSON.stringify({
                    extracted_json: payload
                })
            })
            .then(async response => {
                const contentType = response.headers.get('content-type') || '';
                if (!contentType.includes('application/json')) {
                    const text = await response.text();
                    throw new Error(`Server returned HTTP ${response.status} (non-JSON response). Please check server logs.`);
                }
                const res = await response.json();
                if (!response.ok || !res.success) {
                    throw new Error(res.message || `Failed to save data (HTTP ${response.status})`);
                }
                return res;
            })
            .then(res => {
                if (res.success) {
                    alert(res.message);
                    if (res.redirect_url) {
                        window.location.href = res.redirect_url;
                    } else {
                        window.location.href = "{{ route('admin.organisations.index') }}";
                    }
                } else {
                    throw new Error(res.message || 'Failed to save data.');
                }
            })
            .catch(err => {
                alert('Error saving data: ' + err.message);
                saveButtons.forEach(b => {
                    b.disabled = false;
                    b.innerHTML = '<i class="fas fa-save me-1"></i> Save Records';
                });
            });
        });
    });

    // Initial setup
    handleEntityModeChange();
    const initialTypeId = (aiOrgTypeMaster && aiOrgTypeMaster.value) || 1;
    toggleOrgTypeFields(initialTypeId);
});
</script>
@endpush
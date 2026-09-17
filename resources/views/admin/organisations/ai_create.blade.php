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
            <div class="row g-3 align-items-start">
                <div class="col-md-5">
                    <label class="form-label fw-bold">Website URL <span class="text-danger">*</span></label>
                    <div class="input-group">
                        <span class="input-group-text"><i class="fas fa-globe text-muted"></i></span>
                        <input type="url" id="aiInputUrl" class="form-control" placeholder="https://www.example.edu.in" required>
                    </div>
                </div>
                <div class="col-md-3">
                    <label class="form-label fw-bold">Organisation Type <span class="text-danger">*</span></label>
                    <select id="aiInputOrgType" class="form-select" required>
                        <option value="">-- Select Type * --</option>
                        @if(isset($organisationTypes))
                            @foreach($organisationTypes as $type)
                                @if(in_array(strtolower($type->title), ['college', 'university']))
                                    <option value="{{ $type->id }}">{{ $type->title }}</option>
                                @endif
                            @endforeach
                        @endif
                    </select>
                </div>
                <div class="col-md-4">
                    <label class="form-label fw-bold">
                        Target Organisation <span class="text-muted fw-normal">(Optional)</span>
                    </label>
                    <select id="aiInputTargetOrg" class="form-select">
                        <option value="">-- Create New Organisation --</option>
                    </select>
                    <div id="targetOrgHelp" class="small mt-1 text-muted">
                        <i class="fas fa-info-circle me-1"></i> Creating a new organisation only.
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
                                            @if(in_array(strtolower($ot->title), ['college', 'university']))
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
    let currentExtractionMode = 'organisation_only';
    let currentTargetOrgId = null;
    let currentTargetOrgName = '';
    const allOrganisations = @json($organisations ?? []);
    let globalMasters = {
        courses: @json($courses ?? []),
        program_levels: @json($programLevels ?? []),
        streams: @json($streams ?? []),
        disciplines: @json($disciplines ?? []),
        organisation_types: @json($organisationTypes ?? [])
    };

    const DEGREE_ALIASES = {
        'diploma in pharmacy': ['d.pharm', 'd pharm', 'dpharm', 'diploma pharmacy', 'd pharma', 'd. pharma'],
        'bachelor of pharmacy': ['b.pharm', 'b pharm', 'bpharm', 'b pharmacy', 'b pharma', 'b. pharma'],
        'master of pharmacy': ['m.pharm', 'm pharm', 'mpharm', 'm pharmacy', 'm pharma', 'm. pharma'],
        'bachelor of technology': ['b.tech', 'b tech', 'btech', 'b.e', 'be', 'bachelor of engineering'],
        'master of technology': ['m.tech', 'm tech', 'mtech', 'm.e', 'me', 'master of engineering'],
        'master of business administration': ['mba'],
        'bachelor of business administration': ['bba'],
        'bachelor of computer applications': ['bca'],
        'master of computer applications': ['mca'],
        'bachelor of commerce': ['b.com', 'bcom', 'b. com'],
        'master of commerce': ['m.com', 'mcom', 'm. com'],
        'bachelor of science': ['b.sc', 'bsc', 'b. sc'],
        'master of science': ['m.sc', 'msc', 'm. sc'],
        'bachelor of arts': ['b.a', 'ba', 'b. a'],
        'master of arts': ['m.a', 'ma', 'm. a'],
        'bachelor of laws': ['llb', 'll.b', 'bachelor of law'],
        'master of laws': ['llm', 'll.m'],
        'bachelor of medicine and bachelor of surgery': ['mbbs', 'm.b.b.s'],
        'bachelor of dental surgery': ['bds', 'b.d.s'],
        'doctor of medicine': ['md', 'm.d'],
        'master of surgery': ['ms', 'm.s'],
        'bachelor of architecture': ['b.arch', 'b arch', 'barch'],
        'bachelor of design': ['b.des', 'b design', 'bdesign', 'b des'],
        'bachelor of education': ['b.ed', 'b ed', 'bed'],
        'master of education': ['m.ed', 'm ed', 'med'],
        'doctor of philosophy': ['ph.d', 'phd', 'doctorate']
    };

    function normalizeStr(str) {
        if (!str) return '';
        return str.toString()
            .toLowerCase()
            .replace(/\b(in|of|and|&|the|for|with|a|an|program|course|degree|honors|hons)\b/gi, '')
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

    function findBestCourseMatch(aiName, aiShortName) {
        if (!globalMasters.courses || globalMasters.courses.length === 0) return null;
        const normName = normalizeStr(aiName);
        const normShort = normalizeStr(aiShortName);

        // 1. Direct normalized match
        for (const c of globalMasters.courses) {
            const normC = normalizeStr(c.name);
            if (normName !== '' && normName === normC) return c;
            if (normShort !== '' && normShort === normC) return c;
        }

        // 2. Alias mapping lookup
        for (const [longForm, aliasList] of Object.entries(DEGREE_ALIASES)) {
            const normLong = normalizeStr(longForm);
            const matchesLong = (normName === normLong || normName.includes(normLong));
            let matchesShort = false;
            for (const al of aliasList) {
                const normAl = normalizeStr(al);
                if ((normShort !== '' && normShort === normAl) || (normName !== '' && normName === normAl)) {
                    matchesShort = true;
                    break;
                }
            }

            if (matchesLong || matchesShort) {
                for (const c of globalMasters.courses) {
                    const normC = normalizeStr(c.name);
                    if (normC === normLong) return c;
                    for (const al of aliasList) {
                        if (normC === normalizeStr(al)) return c;
                    }
                }
            }
        }

        // 3. Substring & Similarity score
        let best = null;
        let maxScore = 0;
        for (const c of globalMasters.courses) {
            const normC = normalizeStr(c.name);
            if (normName !== '' && (normC.includes(normName) || normName.includes(normC))) {
                const score = Math.min(normName.length, normC.length) / Math.max(normName.length, normC.length);
                if (score > maxScore) {
                    maxScore = score;
                    best = c;
                }
            } else {
                const score = stringSimilarity(normName, normC);
                if (score > maxScore && score >= 0.55) {
                    maxScore = score;
                    best = c;
                }
            }
        }

        return best;
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
            const m = globalMasters.program_levels.find(l => normalizeStr(l.title).includes('phd') || normalizeStr(l.title).includes('ph.d'));
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

    function findBestOrgTypeMatch(aiOrgType) {
        if (!globalMasters.organisation_types || !aiOrgType) return '1';
        const norm = normalizeStr(aiOrgType);
        for (const ot of globalMasters.organisation_types) {
            const normOt = normalizeStr(ot.title);
            if (norm === normOt || normOt.includes(norm) || norm.includes(normOt)) {
                return ot.id;
            }
        }
        return '1';
    }

    function getAvailableCampuses() {
        const campuses = [];
        document.querySelectorAll('.campus-item .c-name').forEach(input => {
            const val = input.value.trim();
            if (val) campuses.push(val);
        });
        return campuses.length ? campuses : ['Main Campus'];
    }

    function getAvailableDepts() {
        const depts = [];
        document.querySelectorAll('.dept-item .d-name').forEach(input => {
            const val = input.value.trim();
            if (val) depts.push(val);
        });
        return depts.length ? depts : ['General Faculty'];
    }

    function refreshCourseCampusAndDeptDropdowns() {
        const campuses = getAvailableCampuses();
        const depts = getAvailableDepts();

        document.querySelectorAll('.course-item').forEach(card => {
            const campusSelect = card.querySelector('.cr-campus-select');
            if (campusSelect) {
                const curVal = campusSelect.value;
                campusSelect.innerHTML = campuses.map(c => `<option value="${c}" ${c === curVal ? 'selected' : ''}>${c}</option>`).join('');
            }
            const deptSelect = card.querySelector('.cr-dept-select');
            if (deptSelect) {
                const curVal = deptSelect.value;
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

    // Step 1: Dynamic Target Organisation Filtering & Helper with Select2 compatibility
    function filterTargetOrganisations() {
        const $orgType = $('#aiInputOrgType');
        const $targetOrg = $('#aiInputTargetOrg');
        if (!$targetOrg.length) return;

        const selectedType = $orgType.val();
        const currentVal = $targetOrg.val();

        if ($targetOrg.hasClass("select2-hidden-accessible")) {
            $targetOrg.select2('destroy');
        }

        $targetOrg.empty();
        $targetOrg.append(new Option('-- Create New Organisation --', ''));

        let matchFound = false;
        if (selectedType) {
            const matching = (allOrganisations || []).filter(function (org) {
                return String(org.organisation_type_id) === String(selectedType);
            });

            matching.forEach(function (org) {
                const label = org.name + (org.short_name ? ' (' + org.short_name + ')' : '');
                const isSelected = (String(org.id) === String(currentVal));
                if (isSelected) matchFound = true;
                $targetOrg.append(new Option(label, org.id, isSelected, isSelected));
            });
        }

        if (!matchFound) {
            $targetOrg.val('');
        }

        if (typeof $.fn.select2 !== 'undefined') {
            $targetOrg.select2({ width: '100%' });
        }

        updateTargetOrgHelper();
    }

    function updateTargetOrgHelper() {
        const $targetOrg = $('#aiInputTargetOrg');
        const targetOrgHelp = document.getElementById('targetOrgHelp');
        if (!targetOrgHelp || !$targetOrg.length) return;

        const val = $targetOrg.val();
        if (val && val !== '') {
            const selectedText = $targetOrg.find('option:selected').text();
            targetOrgHelp.innerHTML = `<span class="text-success fw-bold"><i class="fas fa-check-circle me-1"></i> Adding campuses & courses to <u>${selectedText}</u></span>`;

            const selOrg = (allOrganisations || []).find(o => String(o.id) === String(val));
            const urlInput = document.getElementById('aiInputUrl');
            if (urlInput && !urlInput.value && selOrg && selOrg.official_website) {
                urlInput.value = selOrg.official_website;
            }
        } else {
            targetOrgHelp.innerHTML = `<span class="text-muted"><i class="fas fa-info-circle me-1"></i> Creating a new organisation only.</span>`;
        }

        fetchAndRefreshPrompt(false);
    }

    $(document).on('change', '#aiInputOrgType', function () {
        filterTargetOrganisations();
        fetchAndRefreshPrompt(false);
    });

    $(document).on('change', '#aiInputTargetOrg', function () {
        updateTargetOrgHelper();
        fetchAndRefreshPrompt(false);
    });

    const aiInputUrlEl = document.getElementById('aiInputUrl');
    if (aiInputUrlEl) {
        aiInputUrlEl.addEventListener('blur', function () {
            fetchAndRefreshPrompt(false);
        });
    }

    setTimeout(function () {
        filterTargetOrganisations();
        fetchAndRefreshPrompt(false);
    }, 150);

    // Prompt Preview & Editing Logic
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
        const targetOrgId = document.getElementById('aiInputTargetOrg') ? document.getElementById('aiInputTargetOrg').value : '';

        // Update badge immediately
        if (badgePromptMode) {
            badgePromptMode.innerText = targetOrgId ? 'Campuses & Courses' : 'Organisation Only';
            badgePromptMode.className = targetOrgId ? 'badge bg-success-subtle text-success border border-success ms-1 fw-normal' : 'badge bg-light text-dark border ms-1 fw-normal';
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
                    'X-CSRF-TOKEN': "{{ csrf_token() }}"
                },
                body: JSON.stringify({
                    url: url || 'https://www.example.edu',
                    organisation_type_id: orgTypeId,
                    target_organisation_id: targetOrgId || null,
                    reference_urls: referenceUrls
                })
            })
            .then(res => res.json())
            .then(result => {
                if (promptLoadingSpinner) promptLoadingSpinner.classList.add('d-none');
                if (result.success && result.prompt) {
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

    // 1. Trigger AI Extraction
    btnRunAiExtraction.addEventListener('click', function () {
        const url = document.getElementById('aiInputUrl').value.trim();
        const orgTypeId = document.getElementById('aiInputOrgType').value;
        const targetOrgId = document.getElementById('aiInputTargetOrg') ? document.getElementById('aiInputTargetOrg').value : '';

        if (!url) {
            alert('Please enter a valid website URL.');
            document.getElementById('aiInputUrl').focus();
            return;
        }

        if (!orgTypeId) {
            alert('Please select an Organisation Type before extracting data.');
            document.getElementById('aiInputOrgType').focus();
            return;
        }

        // Collect all non-empty reference URLs
        const referenceUrls = [];
        document.querySelectorAll('#referenceUrlsContainer .ref-url-input').forEach(input => {
            const val = input.value.trim();
            if (val && !referenceUrls.includes(val)) {
                referenceUrls.push(val);
            }
        });

        const customPromptVal = (aiCustomPrompt && aiCustomPrompt.value.trim()) ? aiCustomPrompt.value.trim() : null;

        const loadingStatusEl = document.getElementById('aiLoadingStatusText');
        if (loadingStatusEl) {
            if (targetOrgId) {
                const selOrg = allOrganisations.find(o => String(o.id) === String(targetOrgId));
                const orgName = selOrg ? selOrg.name : 'the organisation';
                loadingStatusEl.innerText = `Extracting campuses, departments, and courses for '${orgName}'... Please wait.`;
            } else {
                loadingStatusEl.innerText = 'Extracting organisation profile... Please wait.';
            }
        }

        aiStepInput.classList.add('d-none');
        aiStepLoading.classList.remove('d-none');
        aiStepPreview.classList.add('d-none');

        fetch("{{ route('admin.ai-organisations.extract') }}", {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': "{{ csrf_token() }}"
            },
            body: JSON.stringify({
                url: url,
                organisation_type_id: orgTypeId,
                target_organisation_id: targetOrgId || null,
                reference_urls: referenceUrls,
                custom_prompt: customPromptVal
            })
        })
        .then(response => response.json())
        .then(result => {
            if (result.success && result.data) {
                currentExtractedData = result.data;
                currentExtractionMode = result.data.mode || (targetOrgId ? 'campuses_and_courses' : 'organisation_only');
                currentTargetOrgId = result.data.target_organisation_id || (targetOrgId ? targetOrgId : null);
                currentTargetOrgName = result.data.target_organisation_name || '';

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
            const allowedTypes = ['college', 'university'];
            orgTypeSelect.innerHTML = '<option value="">-- Select Master Type --</option>' +
                masters.organisation_types
                    .filter(ot => allowedTypes.includes(ot.title.toLowerCase()))
                    .map(ot => `<option value="${ot.id}">${ot.title}</option>`).join('');
            if (currentVal) orgTypeSelect.value = currentVal;
        }
    }

    // 2. Restart Extraction
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

    // 3. Render Complete All Fields Preview
    function renderAllFieldsPreview(data, selectedTypeId) {
        const org = data.organisation || {};
        const campuses = data.campuses || [];
        const departments = data.departments || [];
        const courses = data.courses || [];

        const activeTypeId = selectedTypeId || org.organisation_type_id || document.getElementById('aiInputOrgType').value || 1;

        // Set Master Type and Toggle Container
        const orgTypeMasterEl = document.getElementById('aiOrgTypeMaster');
        if (orgTypeMasterEl) {
            orgTypeMasterEl.value = activeTypeId;
        }
        toggleOrgTypeFields(activeTypeId);

        // Common Fields
        const orgName = org.name || '';
        document.getElementById('aiOrgName').value = orgName;

        // Update header & badges according to Mode
        const headerTitleEl = document.getElementById('previewOrgHeaderTitle');
        const headerBadgeEl = document.getElementById('previewOrgNameBadge');
        const mode1Notices = document.querySelectorAll('.mode1-skipped-notice');
        const mode2NoticeEl = document.getElementById('mode2OrgNotice');
        const mode2OrgNameTextEl = document.getElementById('mode2OrgNameText');
        const mode2OnlyBtns = document.querySelectorAll('.mode2-only-btn');
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

        if (currentExtractionMode === 'campuses_and_courses') {
            const displayName = currentTargetOrgName || org.name || 'Selected Organisation';
            if (headerTitleEl) headerTitleEl.innerHTML = `<i class="fas fa-layer-group text-success me-2"></i>Campuses & Courses for <strong>${displayName}</strong>`;
            if (headerBadgeEl) headerBadgeEl.innerHTML = `<span class="badge bg-success-subtle text-success border border-success px-2 py-1"><i class="fas fa-check-circle me-1"></i>${displayName}</span>`;
            
            // Hide Organisation Tab & Pane completely
            if (tabItemOrg) tabItemOrg.classList.add('d-none');
            if (tabOrgPane) {
                tabOrgPane.classList.add('d-none');
                tabOrgPane.classList.remove('active', 'show');
            }

            // Show Campus, Dept, Courses tabs & panes
            if (tabItemCampuses) tabItemCampuses.classList.remove('d-none');
            if (tabItemDepts) tabItemDepts.classList.remove('d-none');
            if (tabItemCourses) tabItemCourses.classList.remove('d-none');
            if (tabCampusesPane) tabCampusesPane.classList.remove('d-none');
            if (tabDeptsPane) tabDeptsPane.classList.remove('d-none');
            if (tabCoursesPane) tabCoursesPane.classList.remove('d-none');

            // Hide Unfilled Warning Banner
            if (unfilledBanner) unfilledBanner.classList.add('d-none');

            mode1Notices.forEach(n => n.classList.add('d-none'));
            if (mode2NoticeEl) {
                mode2NoticeEl.classList.remove('d-none');
                if (mode2OrgNameTextEl) mode2OrgNameTextEl.innerText = displayName;
            }
            mode2OnlyBtns.forEach(b => b.classList.remove('d-none'));
            saveButtons.forEach(b => {
                b.innerHTML = '<i class="fas fa-save me-1"></i> Save Campuses & Courses';
            });
        } else {
            // Mode: Organisation Only
            if (headerTitleEl) headerTitleEl.innerHTML = `<i class="fas fa-university text-primary me-2"></i>Organisation Preview`;
            if (headerBadgeEl) headerBadgeEl.innerText = (org.short_name || orgName || '') + (org.established_year ? ' (Est. ' + org.established_year + ')' : '');
            
            // Show Organisation Tab & Pane
            if (tabItemOrg) tabItemOrg.classList.remove('d-none');
            if (tabOrgPane) tabOrgPane.classList.remove('d-none');

            // Hide Campus, Dept, Courses tabs & panes
            if (tabItemCampuses) tabItemCampuses.classList.add('d-none');
            if (tabItemDepts) tabItemDepts.classList.add('d-none');
            if (tabItemCourses) tabItemCourses.classList.add('d-none');
            if (tabCampusesPane) {
                tabCampusesPane.classList.add('d-none');
                tabCampusesPane.classList.remove('active', 'show');
            }
            if (tabDeptsPane) {
                tabDeptsPane.classList.add('d-none');
                tabDeptsPane.classList.remove('active', 'show');
            }
            if (tabCoursesPane) {
                tabCoursesPane.classList.add('d-none');
                tabCoursesPane.classList.remove('active', 'show');
            }

            mode1Notices.forEach(n => n.classList.remove('d-none'));
            if (mode2NoticeEl) mode2NoticeEl.classList.add('d-none');
            mode2OnlyBtns.forEach(b => b.classList.add('d-none'));
            saveButtons.forEach(b => {
                b.innerHTML = '<i class="fas fa-save me-1"></i> Save Organisation';
            });
        }

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

        // Auto-fill active category container inputs dynamically
        const visibleContainer = document.querySelector('#tab-org .col-12[id$="-fields"][style*="display: block"]') ||
                                 document.querySelector('#tab-org .col-12[id$="-fields"]:not([style*="display: none"])');
        if (visibleContainer) {
            // Fill inputs and selects
            visibleContainer.querySelectorAll('input, select, textarea').forEach(input => {
                const name = input.getAttribute('name');
                if (!name || input.type === 'file') return;

                const baseName = name.replace(/\[\]$/, '');

                if (org[baseName] !== undefined && org[baseName] !== null) {
                    const val = org[baseName];
                    if (input.type === 'checkbox') {
                        if (name.endsWith('[]')) {
                            // Multi-checkbox (e.g. levels_offered[], education_boards_supported[], functions[], etc.)
                            const arr = Array.isArray(val) ? val.map(String) : (typeof val === 'string' ? val.split(',').map(s => s.trim()) : []);
                            input.checked = arr.includes(String(input.value));
                        } else {
                            // Boolean switch
                            input.checked = Boolean(val == 1 || val === true || val === '1' || val === 'true');
                        }
                    } else if (input.tagName === 'SELECT') {
                        input.value = String(val);
                    } else if (input.tagName === 'TEXTAREA') {
                        input.value = typeof val === 'object' ? JSON.stringify(val) : String(val);
                    } else {
                        // Text / Number / URL / Email input
                        if (name.endsWith('[]')) {
                            input.value = toCsv(val);
                        } else {
                            input.value = typeof val === 'object' ? toCsv(val) : String(val);
                        }
                    }
                }
            });
        }

        // Tab 2: Campuses Cards
        const campusesContainer = document.getElementById('campusesContainer');
        campusesContainer.innerHTML = '';

        // Tab 3: Departments Cards
        const deptsContainer = document.getElementById('deptsContainer');
        deptsContainer.innerHTML = '';

        // Tab 4: Courses Cards
        const coursesContainer = document.getElementById('coursesContainer');
        coursesContainer.innerHTML = '';

        if (currentExtractionMode === 'organisation_only') {
            campusesContainer.innerHTML = '<div class="alert alert-light text-center py-4 border text-muted"><i class="fas fa-ban fa-2x mb-2 text-secondary d-block"></i>Campuses are skipped in Organisation Only mode.</div>';
            deptsContainer.innerHTML = '<div class="alert alert-light text-center py-4 border text-muted"><i class="fas fa-ban fa-2x mb-2 text-secondary d-block"></i>Departments are skipped in Organisation Only mode.</div>';
            coursesContainer.innerHTML = '<div class="alert alert-light text-center py-4 border text-muted"><i class="fas fa-ban fa-2x mb-2 text-secondary d-block"></i>Courses are skipped in Organisation Only mode.</div>';
        } else {
            if (campuses.length === 0) {
                campuses.push({ campus_name: (displayName || 'Main') + ' - Campus', campus_type: 'Main' });
            }
            campuses.forEach((c, idx) => {
                appendCampusCard(c, idx);
            });

            if (departments.length === 0) {
                departments.push({ department_name: 'General Faculty' });
            }
            departments.forEach((d, idx) => {
                appendDeptCard(d, idx);
            });

            courses.forEach((cr, idx) => {
                appendCourseCard(cr, idx);
            });
        }

        updateAllBadges();
        refreshCourseCampusAndDeptDropdowns();
        highlightUnfilledFields();

        // Default tab selection
        if (currentExtractionMode === 'campuses_and_courses') {
            const campusesTabBtn = document.getElementById('tab-campuses-btn');
            const orgTabBtn = document.getElementById('tab-org-btn');
            if (orgTabBtn) orgTabBtn.classList.remove('active');
            if (tabOrgPane) tabOrgPane.classList.remove('active', 'show');

            if (campusesTabBtn) {
                campusesTabBtn.classList.add('active');
                if (tabCampusesPane) tabCampusesPane.classList.add('active', 'show');
                if (typeof bootstrap !== 'undefined' && bootstrap.Tab) {
                    const bsTab = bootstrap.Tab.getOrCreateInstance(campusesTabBtn);
                    bsTab.show();
                } else {
                    campusesTabBtn.click();
                }
            }
        } else {
            const orgTabBtn = document.getElementById('tab-org-btn');
            const campusesTabBtn = document.getElementById('tab-campuses-btn');
            if (campusesTabBtn) campusesTabBtn.classList.remove('active');
            if (tabCampusesPane) tabCampusesPane.classList.remove('active', 'show');

            if (orgTabBtn) {
                orgTabBtn.classList.add('active');
                if (tabOrgPane) tabOrgPane.classList.add('active', 'show');
                if (typeof bootstrap !== 'undefined' && bootstrap.Tab) {
                    const bsTab = bootstrap.Tab.getOrCreateInstance(orgTabBtn);
                    bsTab.show();
                } else {
                    orgTabBtn.click();
                }
            }
        }
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

        // Skip inputs inside non-displayed category container
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

        if (empty) {
            el.classList.add('field-not-autofilled');
            if (label && !label.querySelector('.badge-not-autofilled')) {
                const badge = document.createElement('span');
                badge.className = 'badge badge-not-autofilled ms-2';
                badge.innerHTML = '<i class="fas fa-pen-nib me-1"></i>Not auto-filled';
                label.appendChild(badge);
            }
        } else {
            el.classList.remove('field-not-autofilled');
            if (label) {
                const badge = label.querySelector('.badge-not-autofilled');
                if (badge) badge.remove();
            }
        }
    }

    function highlightUnfilledFields() {
        const tabOrg = document.getElementById('tab-org');
        if (!tabOrg) return;

        const visibleTypeContainer = tabOrg.querySelector('.col-12[id$="-fields"][style*="display: block"]') ||
                                     tabOrg.querySelector('.col-12[id$="-fields"]:not([style*="display: none"])');

        // Check common & specific inputs in Tab 1
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

        // Also highlight empty inputs in Campuses, Departments, Courses
        const cardInputs = document.querySelectorAll('#tab-campuses input, #tab-campuses select, #tab-depts input, #tab-depts select, #tab-courses input, #tab-courses select');
        cardInputs.forEach(el => {
            if (el.type === 'hidden' || el.type === 'checkbox' || el.type === 'file' || el.type === 'radio') return;
            const empty = (el.tagName === 'SELECT') ? (!el.value || el.value === '') : (el.value.trim() === '');
            if (empty) {
                el.classList.add('field-not-autofilled');
            } else {
                el.classList.remove('field-not-autofilled');
            }
        });

        updateAllUnfilledCounters();
    }

    function updateAllUnfilledCounters() {
        if (currentExtractionMode === 'campuses_and_courses') {
            const banner = document.getElementById('unfilledFieldsBanner');
            const badgeOrg = document.getElementById('badgeOrgUnfilledCount');
            if (banner) banner.classList.add('d-none');
            if (badgeOrg) badgeOrg.classList.add('d-none');
            return;
        }

        const tabOrg = document.getElementById('tab-org');
        if (!tabOrg) return;

        const unfilledInOrg = tabOrg.querySelectorAll('.field-not-autofilled').length;

        // 1. Alert Banner
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

        // 2. Tab 1 button badge
        const badgeOrg = document.getElementById('badgeOrgUnfilledCount');
        if (badgeOrg) {
            if (unfilledInOrg > 0) {
                badgeOrg.classList.remove('d-none');
                badgeOrg.innerText = `${unfilledInOrg} missing`;
            } else {
                badgeOrg.classList.add('d-none');
            }
        }

        // 3. Sub-tabs inside type-specific containers (e.g. #uniTab)
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
                const empty = (el.tagName === 'SELECT') ? (!el.value || el.value === '') : (el.value.trim() === '');
                if (empty) {
                    el.classList.add('field-not-autofilled');
                } else {
                    el.classList.remove('field-not-autofilled');
                }

                if (el.closest('#tab-org')) {
                    const label = findLabelForInput(el);
                    if (empty) {
                        if (label && !label.querySelector('.badge-not-autofilled')) {
                            const badge = document.createElement('span');
                            badge.className = 'badge badge-not-autofilled ms-2';
                            badge.innerHTML = '<i class="fas fa-pen-nib me-1"></i>Not auto-filled';
                            label.appendChild(badge);
                        }
                    } else if (label) {
                        const badge = label.querySelector('.badge-not-autofilled');
                        if (badge) badge.remove();
                    }
                }
                updateAllUnfilledCounters();
            });
        });
    }

    // Jump to next unfilled field button
    const btnJump = document.getElementById('btnJumpNextUnfilled');
    if (btnJump) {
        btnJump.addEventListener('click', function () {
            const nextEl = document.querySelector('#tab-org .field-not-autofilled');
            if (nextEl) {
                // If it's inside an inactive sub-tab pane, activate that sub-tab
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

                    <!-- Amenities & Counts -->
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

                    <!-- Checkbox Switches -->
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

                    <!-- Switches -->
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

        // Auto-match Master Course
        const matchedCourse = findBestCourseMatch(rawAiName, rawAiShort);
        const selectedCourseId = matchedCourse ? matchedCourse.id : (cr.course_id || '');

        // Auto-match Masters: Program Level, Stream, Discipline
        const defaultLevelId = matchedCourse && matchedCourse.program_level_id 
            ? matchedCourse.program_level_id 
            : findBestLevelMatch(cr.program_level);

        const defaultStreamId = matchedCourse && matchedCourse.stream_offered_id 
            ? matchedCourse.stream_offered_id 
            : findBestStreamMatch(cr.stream);

        const defaultDisciplineId = matchedCourse && matchedCourse.discipline_id 
            ? matchedCourse.discipline_id 
            : findBestDisciplineMatch(cr.discipline);

        const defaultDuration = (matchedCourse && matchedCourse.duration) ? matchedCourse.duration : (cr.duration || '3 Years');

        // Available campuses & departments
        const availableCampuses = getAvailableCampuses();
        const availableDepts = getAvailableDepts();
        const selectedCampus = cr.campus_name || availableCampuses[0] || 'Main Campus';
        const selectedDept = cr.department_name || availableDepts[0] || 'General Faculty';

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
                    <!-- Master Course Select -->
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

                    <!-- Program Level Master Select -->
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

                    <!-- Stream Master Select -->
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

                    <!-- Discipline Master Select -->
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

                    <!-- Campus & Department Association -->
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

        // Handle Course Master Dropdown Change Event
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

        if (currentExtractionMode === 'organisation_only') {
            if (bCampuses) bCampuses.innerHTML = '0';
            if (bDepts) bDepts.innerHTML = '0';
            if (bCourses) bCourses.innerHTML = '0';
        } else {
            if (bCampuses) bCampuses.innerText = cCount;
            if (bDepts) bDepts.innerText = dCount;
            if (bCourses) bCourses.innerText = crCount;
        }
    }

    // Confirm & Save All Records
    document.querySelectorAll('.btn-confirm-save-action').forEach(btn => {
        btn.addEventListener('click', function () {
            const saveButtons = document.querySelectorAll('.btn-confirm-save-action');
            saveButtons.forEach(b => {
                b.disabled = true;
                b.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span> Saving...';
            });

            let orgPayload = {};
            if (currentExtractionMode === 'organisation_only') {
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

                orgPayload = {
                    name: orgName,
                    organisation_type_id: selectedOrgTypeId || null,
                    organisation_type: selectedOrgTypeName,
                    central_authority: document.getElementById('aiOrgCentralAuth') ? document.getElementById('aiOrgCentralAuth').value.trim() : '',
                    head_office_location: document.getElementById('aiOrgLocation') ? document.getElementById('aiOrgLocation').value.trim() : '',
                    is_top: document.getElementById('aiOrgIsTop') ? document.getElementById('aiOrgIsTop').value : 0,
                    core_values: document.getElementById('aiOrgCoreValues') ? document.getElementById('aiOrgCoreValues').value.split(',').map(s => s.trim()).filter(Boolean) : []
                };

                // Dynamically collect all inputs from the visible type-specific container
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
            }

            const payload = {
                target_organisation_id: currentTargetOrgId || null,
                mode: currentExtractionMode || 'organisation_only',
                organisation: orgPayload,
                campuses: [],
                departments: [],
                courses: []
            };

            // Only collect Campuses, Departments, Courses if Mode 2 (campuses_and_courses)
            if (currentExtractionMode !== 'organisation_only') {
                // Collect Campuses
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

                // Collect Departments
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

                // Collect Courses with Selected Master IDs
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
                    'X-CSRF-TOKEN': "{{ csrf_token() }}"
                },
                body: JSON.stringify({
                    extracted_json: payload
                })
            })
            .then(response => response.json())
            .then(res => {
                if (res.success) {
                    alert(res.message);
                    if (res.redirect_url) {
                        window.location.href = res.redirect_url;
                    } else {
                        window.location.href = "{{ route('admin.organisations.index') }}";
                    }
                } else {
                    throw new Error(res.message || 'Failed to save organisation.');
                }
            })
            .catch(err => {
                alert('Error saving data: ' + err.message);
                saveButtons.forEach(b => {
                    b.disabled = false;
                    b.innerHTML = currentExtractionMode === 'campuses_and_courses'
                        ? '<i class="fas fa-save me-1"></i> Save Campuses, Departments & Courses'
                        : '<i class="fas fa-save me-1"></i> Save Organisation';
                });
            });
        });
    });

    // Initial toggle
    const initialTypeId = (aiOrgTypeMaster && aiOrgTypeMaster.value) || 1;
    toggleOrgTypeFields(initialTypeId);
});
</script>
@endpush
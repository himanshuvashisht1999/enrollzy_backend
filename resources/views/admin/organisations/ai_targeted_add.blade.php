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
.target-name-row {
    animation: fadeIn 0.2s ease-in-out;
}
@keyframes fadeIn {
    from { opacity: 0; transform: translateY(-5px); }
    to { opacity: 1; transform: translateY(0); }
}
</style>

<div class="container-fluid">
    <!-- Page Header -->
    <div class="row mb-3">
        <div class="col-12 d-flex justify-content-between align-items-center flex-wrap gap-2">
            <div>
                <h4 class="mb-1">AI Targeted Incremental Add</h4>
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb mb-0">
                        <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Dashboard</a></li>
                        <li class="breadcrumb-item"><a href="{{ route('admin.organisations.index') }}">Organisations</a></li>
                        <li class="breadcrumb-item active">Add via AI</li>
                    </ol>
                </nav>
            </div>
            <div class="d-flex gap-2">
                <a href="{{ route('admin.organisations.index') }}" class="btn btn-secondary btn-sm">
                    <i class="fas fa-arrow-left me-1"></i> Back to Organisations
                </a>
                <a href="{{ route('admin.ai-organisations.create') }}" class="btn btn-outline-primary btn-sm">
                    <i class="fas fa-magic me-1"></i> Full Auto-Create
                </a>
            </div>
        </div>
    </div>

    <!-- STEP 1: TARGETED INPUT CONFIGURATION CARD -->
    <div id="aiStepInput" class="card shadow-sm mb-4 border-0">
        <div class="card-header bg-white py-3 border-bottom d-flex justify-content-between align-items-center flex-wrap gap-2">
            <h5 class="card-title mb-0 fw-bold">
                <i class="fas fa-layer-group text-success me-2"></i>Add Campuses, Departments, or Courses via AI
            </h5>
            <span class="badge bg-success-subtle text-success border border-success-subtle px-3 py-2 fs-7">
                <i class="fas fa-check-circle me-1"></i>Targeted Incremental Mode
            </span>
        </div>
        <div class="card-body">
            <!-- Mode Selector Bar (Campus, Department, Course) -->
            <div class="mb-4">
                <label class="form-label fw-bold text-dark mb-2">
                    <i class="fas fa-bullseye text-primary me-1"></i> What do you want to add? <span class="text-danger">*</span>
                </label>
                <div class="row g-2">
                    <div class="col-md-4 col-12">
                        <input type="radio" class="btn-check entity-mode-radio" name="aiEntityMode" id="modeCampus" value="campus" {{ ($preSelectedMode ?? 'campus') === 'campus' ? 'checked' : '' }} autocomplete="off">
                        <label class="btn btn-outline-primary w-100 p-3 text-start rounded-3 h-100 shadow-sm" for="modeCampus">
                            <div class="d-flex align-items-center mb-1">
                                <i class="fas fa-city fa-lg me-2 text-info"></i>
                                <span class="fw-bold">Campus / Branch</span>
                            </div>
                            <div class="small text-muted" style="font-size: 0.78rem;">Add new campus branches to an organisation</div>
                        </label>
                    </div>
                    <div class="col-md-4 col-12">
                        <input type="radio" class="btn-check entity-mode-radio" name="aiEntityMode" id="modeDepartment" value="department" {{ ($preSelectedMode ?? '') === 'department' ? 'checked' : '' }} autocomplete="off">
                        <label class="btn btn-outline-primary w-100 p-3 text-start rounded-3 h-100 shadow-sm" for="modeDepartment">
                            <div class="d-flex align-items-center mb-1">
                                <i class="fas fa-building fa-lg me-2 text-warning"></i>
                                <span class="fw-bold">Department / Faculty</span>
                            </div>
                            <div class="small text-muted" style="font-size: 0.78rem;">Add academic faculties or school wings</div>
                        </label>
                    </div>
                    <div class="col-md-4 col-12">
                        <input type="radio" class="btn-check entity-mode-radio" name="aiEntityMode" id="modeCourse" value="course" {{ ($preSelectedMode ?? '') === 'course' ? 'checked' : '' }} autocomplete="off">
                        <label class="btn btn-outline-primary w-100 p-3 text-start rounded-3 h-100 shadow-sm" for="modeCourse">
                            <div class="d-flex align-items-center mb-1">
                                <i class="fas fa-graduation-cap fa-lg me-2 text-success"></i>
                                <span class="fw-bold">Course / Degree Program</span>
                            </div>
                            <div class="small text-muted" style="font-size: 0.78rem;">Add degrees, specializations, or school classes</div>
                        </label>
                    </div>
                </div>
            </div>

            <!-- Cascading Hierarchy Dropdowns -->
            <div class="row g-3 align-items-start mb-4 p-3 bg-light rounded-3 border">
                <!-- 1. Target Organisation (Only University, College, School) -->
                <div class="col-md-4" id="groupTargetOrg">
                    <label class="form-label fw-bold" id="labelTargetOrg">
                        Target Organisation <span class="text-danger">*</span>
                    </label>
                    <select id="aiInputTargetOrg" class="form-select select2-static" required>
                        <option value="">-- Select Organisation * --</option>
                        @if(isset($organisations))
                            @foreach($organisations as $org)
                                <option value="{{ $org->id }}" 
                                    data-type-id="{{ $org->organisation_type_id }}" 
                                    data-website="{{ $org->official_website }}"
                                    {{ (isset($preSelectedOrgId) && (string)$preSelectedOrgId === (string)$org->id) ? 'selected' : '' }}>
                                    {{ $org->name }}
                                </option>
                            @endforeach
                        @endif
                    </select>
                    <div class="form-text small text-muted">Limited to Universities, Colleges, and Schools.</div>
                </div>

                <!-- 2. Target Campus (Required for Department & Course) -->
                <div class="col-md-4 d-none" id="groupTargetCampus">
                    <label class="form-label fw-bold" id="labelTargetCampus">
                        Target Campus <span class="text-danger">*</span>
                    </label>
                    <select id="aiInputTargetCampus" class="form-select select2-static">
                        <option value="">-- Select Campus * --</option>
                    </select>
                    <div class="form-text small text-muted">Campus under which to add the items.</div>
                </div>

                <!-- 3. Target Department (Required for Course) -->
                <div class="col-md-4 d-none" id="groupTargetDept">
                    <label class="form-label fw-bold" id="labelTargetDept">
                        Target Department <span class="text-danger">*</span>
                    </label>
                    <select id="aiInputTargetDept" class="form-select select2-static">
                        <option value="">-- Select Department * --</option>
                    </select>
                    <div class="form-text small text-muted">Department under which to add the courses.</div>
                </div>

                <!-- 4. Website / Source URL -->
                <div class="col-12" id="groupWebsiteUrl">
                    <label class="form-label fw-bold" id="labelWebsiteUrl">
                        Website / Source Page URL <span class="text-danger">*</span>
                    </label>
                    <div class="input-group">
                        <span class="input-group-text bg-white"><i class="fas fa-globe text-primary"></i></span>
                        <input type="url" id="aiInputUrl" class="form-control" placeholder="https://www.example.edu.in/programmes" required>
                    </div>
                    <div class="form-text small text-muted">Enter the website or directory link where these items and their details are listed.</div>
                </div>
            </div>

            <!-- DYNAMIC TARGET NAMES REPEATER ("Add More") -->
            <div class="mb-4 p-3 border rounded-3 bg-white shadow-sm">
                <div class="d-flex justify-content-between align-items-center mb-3 flex-wrap gap-2">
                    <div>
                        <label class="form-label fw-bold mb-0 text-dark">
                            <i class="fas fa-list-ol text-success me-1"></i> <span id="labelTargetNamesSection">Names of Items to Add</span> <span class="text-danger">*</span>
                        </label>
                        <div class="text-muted small" id="subLabelTargetNamesSection">
                            Add only the names of the items you want AI to fetch. AI will ignore everything else on the site and extract full details exclusively for these names.
                        </div>
                    </div>
                    <button type="button" class="btn btn-sm btn-outline-success fw-bold" id="btnAddTargetName">
                        <i class="fas fa-plus me-1"></i> Add More Name
                    </button>
                </div>

                <div id="targetNamesContainer" class="d-flex flex-column gap-2">
                    <!-- Row 1 (Default) -->
                    <div class="input-group target-name-row">
                        <span class="input-group-text bg-light text-muted fw-bold row-index-badge">1</span>
                        <input type="text" class="form-control target-name-input" placeholder="Enter name (e.g. North Campus / Faculty of Nursing / B.Tech Robotics)" required>
                        <button type="button" class="btn btn-outline-danger btn-remove-target-name" title="Remove Name">
                            <i class="fas fa-trash-alt"></i>
                        </button>
                    </div>
                </div>
            </div>

            <!-- Optional Additional Reference URLs (Collapsible) -->
            <div class="mb-3">
                <div class="d-flex justify-content-between align-items-center mb-2">
                    <button class="btn btn-link p-0 text-decoration-none small text-secondary" type="button" data-bs-toggle="collapse" data-bs-target="#refUrlsCollapse">
                        <i class="fas fa-chevron-down me-1"></i> + Additional Reference URLs (Optional: Fee pdf, prospectus, brochure)
                    </button>
                    <button type="button" class="btn btn-sm btn-outline-secondary py-0 px-2" id="btnAddReferenceUrl" style="font-size: 0.75rem;">
                        <i class="fas fa-plus me-1"></i> Add URL
                    </button>
                </div>
                <div class="collapse" id="refUrlsCollapse">
                    <div id="referenceUrlsContainer" class="d-flex flex-column gap-2 pt-2">
                        <div class="input-group input-group-sm reference-url-row">
                            <span class="input-group-text bg-light text-muted"><i class="fas fa-external-link-alt"></i></span>
                            <input type="url" class="form-control ref-url-input" placeholder="e.g. Brochure PDF, Fee Table page, or Wikipedia link">
                            <button type="button" class="btn btn-outline-danger btn-remove-ref-url" title="Remove URL">
                                <i class="fas fa-trash-alt"></i>
                            </button>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Search Google Toggle -->
            <div class="card bg-light border-0 shadow-none p-2 rounded-3 mb-3">
                <div class="form-check form-switch d-flex align-items-center gap-2 mb-0 ps-0">
                    <input class="form-check-input ms-0 me-2" type="checkbox" role="switch" id="aiSearchGoogleCheck" checked style="width: 2.3em; height: 1.25em; cursor: pointer;">
                    <div>
                        <label class="form-check-label fw-bold text-dark small mb-0" for="aiSearchGoogleCheck" style="cursor: pointer;">
                            <i class="fab fa-google text-primary me-1"></i> Enable Deep Search Grounding
                        </label>
                        <div class="text-muted" style="font-size: 0.75rem;">
                            Searches institutional disclosures and Google to auto-verify missing fees, eligibility, and facilities for the requested names.
                        </div>
                    </div>
                </div>
            </div>

            <!-- Action Button -->
            <div class="text-end pt-2 border-top">
                <button type="button" id="btnRunAiExtraction" class="btn btn-success px-4 py-2 fw-bold shadow-sm">
                    <i class="fas fa-bolt me-1"></i> Fetch & Create Details
                </button>
            </div>
        </div>
    </div>

    <!-- STEP 2: LOADING CARD -->
    <div id="aiStepLoading" class="card shadow-sm mb-4 p-5 d-none text-center">
        <div class="py-4">
            <div class="spinner-border text-success mb-3" style="width: 3.5rem; height: 3.5rem;" role="status">
                <span class="visually-hidden">Loading...</span>
            </div>
            <h5 class="fw-bold mb-2">Fetching Targeted Institutional Details...</h5>
            <p class="text-muted mb-0" id="aiLoadingStatusText">
                Searching provided URL and official catalogues exclusively for your requested items. Please wait...
            </p>
        </div>
    </div>

    <!-- STEP 3: REVIEW & EDIT WORKSPACE -->
    <div id="aiStepPreview" class="d-none">
        <div class="card shadow-sm mb-4">
            <!-- Header Actions -->
            <div class="card-header bg-white d-flex justify-content-between align-items-center flex-wrap gap-2 py-3 border-bottom">
                <div>
                    <h5 class="card-title mb-0" id="previewOrgHeaderTitle">Targeted Review Workspace</h5>
                    <small class="text-muted" id="previewOrgNameBadge"></small>
                </div>
                <div class="d-flex gap-2">
                    <button type="button" id="btnRestartExtraction" class="btn btn-outline-secondary btn-sm">
                        <i class="fas fa-arrow-left me-1"></i> Change Target List
                    </button>
                    <button type="button" class="btn btn-success btn-sm px-3 btn-confirm-save-action">
                        <i class="fas fa-save me-1"></i> Save Records
                    </button>
                </div>
            </div>

            <div class="card-body p-4">
                <!-- Tab Pane Container -->
                <div class="tab-content" id="aiReviewTabContent">
                    <!-- ==================== TAB 2: CAMPUSES ==================== -->
                    <div class="tab-pane fade" id="tab-campuses" role="tabpanel">
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <div>
                                <h6 class="fw-bold mb-0 text-dark" id="headerCampusesSection">Campuses & Branches</h6>
                                <small class="text-muted">Review addresses, facilities, laboratories, and amenities.</small>
                            </div>
                            <button type="button" class="btn btn-outline-primary btn-sm" id="btnAddCampusCard">
                                <i class="fas fa-plus me-1"></i> Add Another Campus
                            </button>
                        </div>
                        <div id="campusesContainer"></div>
                    </div>

                    <!-- ==================== TAB 3: DEPARTMENTS ==================== -->
                    <div class="tab-pane fade" id="tab-depts" role="tabpanel">
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <div>
                                <h6 class="fw-bold mb-0 text-dark" id="headerDeptsSection">Faculties & Academic Departments</h6>
                                <small class="text-muted">Review HOD, faculty count, labs, and research information.</small>
                            </div>
                            <button type="button" class="btn btn-outline-primary btn-sm" id="btnAddDeptCard">
                                <i class="fas fa-plus me-1"></i> Add Another Department
                            </button>
                        </div>
                        <div id="deptsContainer"></div>
                    </div>

                    <!-- ==================== TAB 4: COURSES ==================== -->
                    <div class="tab-pane fade" id="tab-courses" role="tabpanel">
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <div>
                                <h6 class="fw-bold mb-0 text-dark" id="headerCoursesSection">Degree & Diploma Programs</h6>
                                <small class="text-muted">Auto-matched with Database Master Courses. All selects feature Select2 search.</small>
                            </div>
                            <button type="button" class="btn btn-outline-primary btn-sm" id="btnAddCourseCard">
                                <i class="fas fa-plus me-1"></i> Add Another Course
                            </button>
                        </div>
                        <div id="coursesContainer"></div>
                    </div>
                </div>
            </div>

            <!-- Footer Save -->
            <div class="card-footer bg-white d-flex justify-content-between align-items-center py-3 border-top">
                <span class="text-muted small"><i class="fas fa-info-circle me-1"></i> Verified records will be created directly under the target organisation.</span>
                <button type="button" class="btn btn-success px-4 btn-confirm-save-action">
                    <i class="fas fa-save me-1"></i> Save Records
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
    let currentExtractionMode = '{{ $preSelectedMode ?? "campus" }}';
    let currentTargetOrgId = '{{ $preSelectedOrgId ?? "" }}';
    let currentTargetOrgName = '';
    let currentTargetCampusId = '{{ $preSelectedCampusId ?? "" }}';
    let currentTargetCampusName = '';
    let currentTargetDeptId = '{{ $preSelectedDeptId ?? "" }}';
    let currentTargetDeptName = '';

    const allOrganisations = @json($organisations ?? []);
    let globalMasters = {
        courses: @json($courses ?? []),
        program_levels: @json($programLevels ?? []),
        streams: @json($streams ?? []),
        disciplines: @json($disciplines ?? []),
        organisation_types: @json($organisationTypes ?? [])
    };

    // Initialize static top Select2 elements
    if (window.jQuery && $.fn.select2) {
        $('.select2-static').select2({ width: '100%' });
    }

    // Helper functions for string matching
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
        if (/\b(bpt|bachelor of physiotherapy)\b/.test(s)) return 'bpt';
        if (/\b(mpt|master of physiotherapy)\b/.test(s)) return 'mpt';
        return '';
    }

    function extractSubjectCore(str) {
        if (!str) return '';
        return str.toString()
            .toLowerCase()
            .replace(/\b(\d+\s*(years?|yrs?|semesters?|sems?))\b/gi, '')
            .replace(/\b(b\.?tech|m\.?tech|b\.?e|m\.?e|b\.?sc|m\.?sc|bca|mca|bba|mba|b\.?com|m\.?com|b\.?a|m\.?a|b\.?pharm|m\.?pharm|d\.?pharm|b\.?des|m\.?des|b\.?arch|m\.?arch|b\.?ed|m\.?ed|ph\.?d|phd|doctorate|doctor of philosophy|diploma|bpt|mpt)\b/gi, '')
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

        for (const c of globalMasters.courses) {
            const normC = normalizeStr(c.name);
            if (normName !== '' && normName === normC) return c;
            if (normShort !== '' && normShort === normC) return c;
        }

        let bestCandidate = null;
        let bestScore = -1;

        for (const c of globalMasters.courses) {
            const cDegree = extractDegreePrefix(c.name);
            const cSubject = extractSubjectCore(c.name);
            const normC = normalizeStr(c.name);

            if (aiDegree !== '') {
                if (cDegree !== '' && cDegree !== aiDegree) {
                    continue;
                }
            }

            let score = 0;
            if (aiDegree !== '' && cDegree === aiDegree) score += 10.0;

            if (aiSubject !== '' && cSubject !== '') {
                if (aiSubject === cSubject) {
                    score += 50.0;
                } else if (cSubject.startsWith(aiSubject) || aiSubject.startsWith(cSubject)) {
                    score += 25.0 + (Math.min(aiSubject.length, cSubject.length) / Math.max(aiSubject.length, cSubject.length) * 10);
                } else if (cSubject.includes(aiSubject) || aiSubject.includes(cSubject)) {
                    score += 15.0 + (Math.min(aiSubject.length, cSubject.length) / Math.max(aiSubject.length, cSubject.length) * 10);
                } else {
                    const sim = stringSimilarity(aiSubject, cSubject);
                    if (sim >= 0.6) score += sim * 15;
                }
            } else if (aiSubject === '' && cSubject === '') {
                score += 30.0;
            }

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

    function toCsv(val) {
        if (!val) return '';
        if (Array.isArray(val)) return val.join(', ');
        return String(val);
    }

    // Select2 card helpers
    function initSelect2OnCard(container) {
        if (!window.jQuery || !$.fn.select2) return;
        $(container).find('select.form-select').each(function () {
            const $sel = $(this);
            if (!$sel.data('select2')) {
                $sel.select2({ width: '100%' });
            }
        });
    }

    function getSelectedEntityMode() {
        const checked = document.querySelector('input[name="aiEntityMode"]:checked');
        return checked ? checked.value : 'campus';
    }

    function isCurrentEntitySchool() {
        const targetOrgId = currentTargetOrgId || $('#aiInputTargetOrg').val();
        if (!targetOrgId) return false;
        const org = (allOrganisations || []).find(o => String(o.id) === String(targetOrgId));
        if (org) {
            if (org.organisation_type_id == 4) return true;
            const orgTypeObj = (globalMasters.organisation_types || []).find(t => String(t.id) === String(org.organisation_type_id));
            if (orgTypeObj && /school/i.test(orgTypeObj.title)) return true;
        }
        return false;
    }

    // Dynamic Cascading Dropdowns UI & Labels
    function handleEntityModeChange() {
        const mode = getSelectedEntityMode();
        currentExtractionMode = mode;
        const isSchool = isCurrentEntitySchool();

        const groupTargetCampus = document.getElementById('groupTargetCampus');
        const groupTargetDept = document.getElementById('groupTargetDept');
        const labelTargetOrg = document.getElementById('labelTargetOrg');
        const labelTargetCampus = document.getElementById('labelTargetCampus');
        const labelTargetDept = document.getElementById('labelTargetDept');
        const labelWebsiteUrl = document.getElementById('labelWebsiteUrl');
        const aiInputUrl = document.getElementById('aiInputUrl');
        const labelTargetNamesSection = document.getElementById('labelTargetNamesSection');
        const subLabelTargetNamesSection = document.getElementById('subLabelTargetNamesSection');

        if (labelTargetOrg) {
            labelTargetOrg.innerHTML = isSchool ? 'Target School <span class="text-danger">*</span>' : 'Target Organisation <span class="text-danger">*</span>';
        }
        if (labelTargetCampus) {
            labelTargetCampus.innerHTML = isSchool ? 'Target School Campus / Branch <span class="text-danger">*</span>' : 'Target Campus <span class="text-danger">*</span>';
        }
        if (labelTargetDept) {
            labelTargetDept.innerHTML = isSchool ? 'Target Academic Wing / Dept <span class="text-danger">*</span>' : 'Target Department <span class="text-danger">*</span>';
        }

        if (mode === 'campus') {
            if (groupTargetCampus) groupTargetCampus.classList.add('d-none');
            if (groupTargetDept) groupTargetDept.classList.add('d-none');
            if (labelWebsiteUrl) labelWebsiteUrl.innerHTML = isSchool ? 'School Campus / Branch Page URL <span class="text-danger">*</span>' : 'Campus / Locations Webpage URL <span class="text-danger">*</span>';
            if (aiInputUrl) aiInputUrl.placeholder = isSchool ? 'https://www.dpsrkp.net/branch/junior-wing' : 'https://www.example.edu.in/campuses';
            if (labelTargetNamesSection) labelTargetNamesSection.innerText = isSchool ? 'Names of School Campuses / Branches to Add' : 'Names of Campuses / Locations to Add';
            if (subLabelTargetNamesSection) subLabelTargetNamesSection.innerText = 'Enter names of the specific campuses to add (e.g. North Campus, City Centre Campus). AI will extract address, labs, and facilities for each.';
        } else if (mode === 'department') {
            if (groupTargetCampus) groupTargetCampus.classList.remove('d-none');
            if (groupTargetDept) groupTargetDept.classList.add('d-none');
            if (labelWebsiteUrl) labelWebsiteUrl.innerHTML = isSchool ? 'School Academic Wings / Faculties URL <span class="text-danger">*</span>' : 'Faculties / Departments Webpage URL <span class="text-danger">*</span>';
            if (aiInputUrl) aiInputUrl.placeholder = isSchool ? 'https://www.dpsrkp.net/academics' : 'https://www.example.edu.in/departments';
            if (labelTargetNamesSection) labelTargetNamesSection.innerText = isSchool ? 'Names of Academic Wings / Departments to Add' : 'Names of Departments / Faculties to Add';
            if (subLabelTargetNamesSection) subLabelTargetNamesSection.innerText = 'Enter names of the specific departments to add (e.g. Faculty of Law, Dept of Computer Science). AI will extract HOD, faculty count, and labs for each.';
        } else if (mode === 'course') {
            if (groupTargetCampus) groupTargetCampus.classList.remove('d-none');
            if (groupTargetDept) groupTargetDept.classList.remove('d-none');
            if (labelWebsiteUrl) labelWebsiteUrl.innerHTML = isSchool ? 'Curriculum / Classes / Admissions URL <span class="text-danger">*</span>' : 'Course Catalog / Admissions Webpage URL <span class="text-danger">*</span>';
            if (aiInputUrl) aiInputUrl.placeholder = isSchool ? 'https://www.dpsrkp.net/admissions/curriculum' : 'https://www.example.edu.in/programmes';
            if (labelTargetNamesSection) labelTargetNamesSection.innerText = isSchool ? 'Names of Classes / Curriculums to Add' : 'Names of Courses / Degrees to Add';
            if (subLabelTargetNamesSection) subLabelTargetNamesSection.innerText = 'Enter names of the specific courses to add (e.g. B.Tech in Artificial Intelligence, MBA in Finance). AI will extract fees, duration, and match master degree.';
        }
    }

    document.querySelectorAll('.entity-mode-radio').forEach(radio => {
        radio.addEventListener('change', handleEntityModeChange);
    });

    // Cascading Dropdown Handlers
    $('#aiInputTargetOrg').on('change', function () {
        handleEntityModeChange();
        const orgId = $(this).val();
        currentTargetOrgId = orgId;
        const selOrg = (allOrganisations || []).find(o => String(o.id) === String(orgId));
        currentTargetOrgName = selOrg ? selOrg.name : '';

        const urlInput = document.getElementById('aiInputUrl');
        if (urlInput && !urlInput.value && selOrg && selOrg.official_website) {
            urlInput.value = selOrg.official_website;
        }

        const $campusSelect = $('#aiInputTargetCampus');
        const $deptSelect = $('#aiInputTargetDept');
        $campusSelect.empty().append(new Option('-- Select Campus * --', ''));
        $deptSelect.empty().append(new Option('-- Select Department * --', ''));

        if (orgId) {
            fetch(`{{ route('admin.ai-organisations.cascading-options') }}?organisation_id=${orgId}`)
                .then(res => res.ok ? res.json() : null)
                .then(data => {
                    if (data && data.campuses && Array.isArray(data.campuses)) {
                        data.campuses.forEach(c => {
                            $campusSelect.append(new Option(c.campus_name + (c.city ? ' (' + c.city + ')' : ''), c.id));
                        });
                        if (currentTargetCampusId) {
                            $campusSelect.val(currentTargetCampusId);
                        }
                        $campusSelect.trigger('change.select2');
                    }
                    if (data && data.departments && Array.isArray(data.departments)) {
                        data.departments.forEach(d => {
                            $deptSelect.append(new Option(d.department_name + (d.department_code ? ' [' + d.department_code + ']' : ''), d.id));
                        });
                        if (currentTargetDeptId) {
                            $deptSelect.val(currentTargetDeptId);
                        }
                        $deptSelect.trigger('change.select2');
                    }
                })
                .catch(err => console.error('Error fetching cascading options:', err));
        }
    });

    $('#aiInputTargetCampus').on('change', function () {
        const orgId = $('#aiInputTargetOrg').val();
        const campusId = $(this).val();
        currentTargetCampusId = campusId;
        const selCampusText = $(this).find('option:selected').text();
        currentTargetCampusName = (campusId && !selCampusText.startsWith('--')) ? selCampusText.replace(/\s*\(.*?\)\s*$/, '').trim() : '';

        const $deptSelect = $('#aiInputTargetDept');
        $deptSelect.empty().append(new Option('-- Select Department * --', ''));

        if (orgId || campusId) {
            const params = new URLSearchParams();
            if (orgId) params.append('organisation_id', orgId);
            if (campusId) params.append('campus_id', campusId);

            fetch(`{{ route('admin.ai-organisations.cascading-options') }}?${params.toString()}`)
                .then(res => res.ok ? res.json() : null)
                .then(data => {
                    if (data && data.departments && Array.isArray(data.departments)) {
                        data.departments.forEach(d => {
                            $deptSelect.append(new Option(d.department_name + (d.department_code ? ' [' + d.department_code + ']' : ''), d.id));
                        });
                        $deptSelect.trigger('change.select2');
                    }
                })
                .catch(err => console.error('Error fetching cascading departments:', err));
        }
    });

    $('#aiInputTargetDept').on('change', function () {
        const deptId = $(this).val();
        currentTargetDeptId = deptId;
        const selDeptText = $(this).find('option:selected').text();
        currentTargetDeptName = (deptId && !selDeptText.startsWith('--')) ? selDeptText.replace(/\s*\[.*?\]\s*$/, '').trim() : '';
    });

    // ----------------------------------------------------
    // Target Names Dynamic Repeater ("Add More")
    // ----------------------------------------------------
    const targetNamesContainer = document.getElementById('targetNamesContainer');
    const btnAddTargetName = document.getElementById('btnAddTargetName');

    function updateTargetNameIndices() {
        const rows = targetNamesContainer.querySelectorAll('.target-name-row');
        rows.forEach((row, idx) => {
            const badge = row.querySelector('.row-index-badge');
            if (badge) badge.innerText = idx + 1;
        });
    }

    if (btnAddTargetName && targetNamesContainer) {
        btnAddTargetName.addEventListener('click', function () {
            const mode = getSelectedEntityMode();
            let placeholder = 'Enter name';
            if (mode === 'campus') placeholder = 'e.g. South Campus / Medical City Branch';
            else if (mode === 'department') placeholder = 'e.g. Faculty of Allied Health Sciences';
            else if (mode === 'course') placeholder = 'e.g. Master of Business Administration (Data Analytics)';

            const row = document.createElement('div');
            row.className = 'input-group target-name-row';
            row.innerHTML = `
                <span class="input-group-text bg-light text-muted fw-bold row-index-badge"></span>
                <input type="text" class="form-control target-name-input" placeholder="${placeholder}" required>
                <button type="button" class="btn btn-outline-danger btn-remove-target-name" title="Remove Name">
                    <i class="fas fa-trash-alt"></i>
                </button>
            `;
            targetNamesContainer.appendChild(row);
            updateTargetNameIndices();
            const input = row.querySelector('.target-name-input');
            if (input) input.focus();
        });

        targetNamesContainer.addEventListener('click', function (e) {
            const removeBtn = e.target.closest('.btn-remove-target-name');
            if (removeBtn) {
                const row = removeBtn.closest('.target-name-row');
                if (targetNamesContainer.querySelectorAll('.target-name-row').length > 1) {
                    row.remove();
                    updateTargetNameIndices();
                } else {
                    const input = row.querySelector('.target-name-input');
                    if (input) input.value = '';
                }
            }
        });
    }

    // Additional Reference URLs management
    const refContainer = document.getElementById('referenceUrlsContainer');
    const btnAddRef = document.getElementById('btnAddReferenceUrl');
    if (btnAddRef && refContainer) {
        btnAddRef.addEventListener('click', function () {
            const row = document.createElement('div');
            row.className = 'input-group input-group-sm reference-url-row';
            row.innerHTML = `
                <span class="input-group-text bg-light text-muted"><i class="fas fa-external-link-alt"></i></span>
                <input type="url" class="form-control ref-url-input" placeholder="e.g. Brochure PDF, Fee Table page, or Wikipedia link">
                <button type="button" class="btn btn-outline-danger btn-remove-ref-url" title="Remove URL">
                    <i class="fas fa-trash-alt"></i>
                </button>
            `;
            refContainer.appendChild(row);
            const input = row.querySelector('.ref-url-input');
            if (input) input.focus();
        });

        refContainer.addEventListener('click', function (e) {
            const removeBtn = e.target.closest('.btn-remove-ref-url');
            if (removeBtn) {
                const row = removeBtn.closest('.reference-url-row');
                if (refContainer.querySelectorAll('.reference-url-row').length > 1) {
                    row.remove();
                } else {
                    const input = row.querySelector('.ref-url-input');
                    if (input) input.value = '';
                }
            }
        });
    }

    // Helper functions to get available campuses/depts from DOM/selects
    function getAvailableCampuses() {
        const campuses = [];
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

    // ----------------------------------------------------
    // Trigger Targeted AI Extraction
    // ----------------------------------------------------
    const btnRunAiExtraction = document.getElementById('btnRunAiExtraction');
    const btnRestartExtraction = document.getElementById('btnRestartExtraction');
    const aiStepInput = document.getElementById('aiStepInput');
    const aiStepLoading = document.getElementById('aiStepLoading');
    const aiStepPreview = document.getElementById('aiStepPreview');

    btnRunAiExtraction.addEventListener('click', function () {
        const mode = getSelectedEntityMode();
        const url = document.getElementById('aiInputUrl').value.trim();
        const targetOrgId = document.getElementById('aiInputTargetOrg') ? document.getElementById('aiInputTargetOrg').value : '';
        const targetCampusId = document.getElementById('aiInputTargetCampus') ? document.getElementById('aiInputTargetCampus').value : '';
        const targetDeptId = document.getElementById('aiInputTargetDept') ? document.getElementById('aiInputTargetDept').value : '';
        const searchGoogle = document.getElementById('aiSearchGoogleCheck') ? (document.getElementById('aiSearchGoogleCheck').checked ? 1 : 0) : 1;

        if (!targetOrgId) {
            alert('Please select a Target Organisation.');
            document.getElementById('aiInputTargetOrg').focus();
            return;
        }

        if (mode === 'department' && !targetCampusId) {
            alert('Please select a Target Campus under which to add the department(s).');
            document.getElementById('aiInputTargetCampus').focus();
            return;
        }

        if (mode === 'course') {
            if (!targetCampusId) {
                alert('Please select a Target Campus under which to add the course(s).');
                document.getElementById('aiInputTargetCampus').focus();
                return;
            }
            if (!targetDeptId) {
                alert('Please select a Target Department under which to add the course(s).');
                document.getElementById('aiInputTargetDept').focus();
                return;
            }
        }

        if (!url) {
            alert('Please enter a valid website / source URL.');
            document.getElementById('aiInputUrl').focus();
            return;
        }

        // Collect Target Names from dynamic repeater
        const targetNames = [];
        document.querySelectorAll('#targetNamesContainer .target-name-input').forEach(input => {
            const val = input.value.trim();
            if (val && !targetNames.includes(val)) {
                targetNames.push(val);
            }
        });

        if (targetNames.length === 0) {
            alert('Please enter at least one target name to add.');
            const firstInput = document.querySelector('#targetNamesContainer .target-name-input');
            if (firstInput) firstInput.focus();
            return;
        }

        const referenceUrls = [];
        document.querySelectorAll('#referenceUrlsContainer .ref-url-input').forEach(input => {
            const val = input.value.trim();
            if (val && !referenceUrls.includes(val)) {
                referenceUrls.push(val);
            }
        });

        const loadingStatusEl = document.getElementById('aiLoadingStatusText');
        const selOrg = (allOrganisations || []).find(o => String(o.id) === String(targetOrgId));
        const orgName = selOrg ? selOrg.name : 'the organisation';

        if (loadingStatusEl) {
            loadingStatusEl.innerText = `Deep-scanning ${url} specifically for: ${targetNames.join(', ')} under '${orgName}'... Please wait.`;
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
                target_organisation_id: targetOrgId,
                target_campus_id: targetCampusId || null,
                target_department_id: targetDeptId || null,
                target_names: targetNames,
                reference_urls: referenceUrls,
                search_google: searchGoogle
            })
        })
        .then(async response => {
            const contentType = response.headers.get('content-type') || '';
            if (!contentType.includes('application/json')) {
                const text = await response.text();
                if (response.status === 504 || response.status === 502) {
                    throw new Error('Server Gateway Timeout (' + response.status + '). Please try again or uncheck Search Grounding to speed up.');
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
                currentTargetOrgId = result.target_organisation_id || targetOrgId;
                currentTargetOrgName = result.target_organisation_name || orgName;
                currentTargetCampusId = result.target_campus_id || targetCampusId;
                currentTargetCampusName = result.target_campus_name || currentTargetCampusName;
                currentTargetDeptId = result.target_department_id || targetDeptId;
                currentTargetDeptName = result.target_department_name || currentTargetDeptName;

                if (result.masters) {
                    globalMasters = result.masters;
                }

                renderTargetedPreview(currentExtractedData, targetNames);

                aiStepLoading.classList.add('d-none');
                aiStepPreview.classList.remove('d-none');
                window.scrollTo({ top: 0, behavior: 'smooth' });
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

    btnRestartExtraction.addEventListener('click', function () {
        aiStepPreview.classList.add('d-none');
        aiStepInput.classList.remove('d-none');
        window.scrollTo({ top: 0, behavior: 'smooth' });
    });

    // ----------------------------------------------------
    // Render Targeted Preview Workspace
    // ----------------------------------------------------
    function renderTargetedPreview(data, targetNames) {
        const mode = currentExtractionMode;
        const isSchool = isCurrentEntitySchool();
        const orgDisplayName = currentTargetOrgName || 'Selected Organisation';

        const headerTitleEl = document.getElementById('previewOrgHeaderTitle');
        const headerBadgeEl = document.getElementById('previewOrgNameBadge');
        const saveButtons = document.querySelectorAll('.btn-confirm-save-action');

        const tabCampusesPane = document.getElementById('tab-campuses');
        const tabDeptsPane = document.getElementById('tab-depts');
        const tabCoursesPane = document.getElementById('tab-courses');

        [tabCampusesPane, tabDeptsPane, tabCoursesPane].forEach(p => {
            if (p) {
                p.classList.add('d-none');
                p.classList.remove('active', 'show');
            }
        });

        if (mode === 'campus') {
            if (headerTitleEl) headerTitleEl.innerHTML = `<i class="fas fa-city text-info me-2"></i>Review ${isSchool ? 'School Campuses' : 'Campuses'} for <strong>${orgDisplayName}</strong>`;
            if (headerBadgeEl) headerBadgeEl.innerHTML = `<span class="badge bg-info-subtle text-info border border-info px-2 py-1">${orgDisplayName}</span>`;
            if (tabCampusesPane) {
                tabCampusesPane.classList.remove('d-none');
                tabCampusesPane.classList.add('active', 'show');
            }
            saveButtons.forEach(b => {
                b.innerHTML = `<i class="fas fa-save me-1"></i> Save ${isSchool ? 'School Campuses' : 'Campuses'}`;
            });

            const campusesContainer = document.getElementById('campusesContainer');
            campusesContainer.innerHTML = '';
            const campuses = data.campuses || [];
            
            // If empty, create fallback templates matching target names
            const cList = campuses.length > 0 ? campuses : targetNames.map(name => ({ campus_name: name, campus_type: 'Regional' }));
            cList.forEach((c, idx) => {
                appendCampusCard(c, idx);
            });

        } else if (mode === 'department') {
            const subLabel = currentTargetCampusName ? ` (${currentTargetCampusName})` : '';
            if (headerTitleEl) headerTitleEl.innerHTML = `<i class="fas fa-building text-warning me-2"></i>Review ${isSchool ? 'Academic Wings' : 'Departments'} for <strong>${orgDisplayName}${subLabel}</strong>`;
            if (headerBadgeEl) headerBadgeEl.innerHTML = `<span class="badge bg-warning-subtle text-warning border border-warning px-2 py-1">${orgDisplayName}${subLabel}</span>`;
            if (tabDeptsPane) {
                tabDeptsPane.classList.remove('d-none');
                tabDeptsPane.classList.add('active', 'show');
            }
            saveButtons.forEach(b => {
                b.innerHTML = `<i class="fas fa-save me-1"></i> Save ${isSchool ? 'Academic Wings' : 'Departments'}`;
            });

            const deptsContainer = document.getElementById('deptsContainer');
            deptsContainer.innerHTML = '';
            const depts = data.departments || [];
            const dList = depts.length > 0 ? depts : targetNames.map(name => ({ department_name: name }));
            dList.forEach((d, idx) => {
                appendDeptCard(d, idx);
            });

        } else if (mode === 'course') {
            const subLabel = (currentTargetDeptName ? ` - ${currentTargetDeptName}` : '') + (currentTargetCampusName ? ` (${currentTargetCampusName})` : '');
            if (headerTitleEl) headerTitleEl.innerHTML = `<i class="fas fa-graduation-cap text-success me-2"></i>Review ${isSchool ? 'School Classes' : 'Courses'} for <strong>${orgDisplayName}${subLabel}</strong>`;
            if (headerBadgeEl) headerBadgeEl.innerHTML = `<span class="badge bg-success-subtle text-success border border-success px-2 py-1">${orgDisplayName}</span>`;
            if (tabCoursesPane) {
                tabCoursesPane.classList.remove('d-none');
                tabCoursesPane.classList.add('active', 'show');
            }
            saveButtons.forEach(b => {
                b.innerHTML = `<i class="fas fa-save me-1"></i> Save ${isSchool ? 'School Classes' : 'Courses'}`;
            });

            const coursesContainer = document.getElementById('coursesContainer');
            coursesContainer.innerHTML = '';
            const courses = data.courses || [];
            const crList = courses.length > 0 ? courses : targetNames.map(name => (isSchool ? { academic_unit_name: name } : { course_name: name }));
            crList.forEach((cr, idx) => {
                appendCourseCard(cr, idx);
            });
        }
    }

    // ----------------------------------------------------
    // Card Rendering Functions (Campus, Department, Course)
    // ----------------------------------------------------
    function appendCampusCard(c = {}, idx = Date.now()) {
        const isSchool = isCurrentEntitySchool();
        const div = document.createElement('div');
        div.className = 'card border mb-3 campus-item';
        div.innerHTML = `
            <div class="card-header bg-light d-flex justify-content-between align-items-center py-2">
                <span class="fw-bold"><i class="fas fa-map-marker-alt text-primary me-2"></i>${isSchool ? 'School Campus / Branch' : 'Campus'}: <span class="campus-title-preview">${c.campus_name || (isSchool ? 'School Branch' : 'Campus')}</span></span>
                <button type="button" class="btn btn-sm btn-outline-danger btn-remove-campus">
                    <i class="fas fa-trash me-1"></i> Remove
                </button>
            </div>
            <div class="card-body">
                <div class="row g-3">
                    <div class="col-md-5">
                        <label class="form-label fw-bold small">Campus / Branch Name <span class="text-danger">*</span></label>
                        <input type="text" class="form-control c-name" value="${c.campus_name || ''}">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label fw-bold small">Campus Type</label>
                        <select class="form-select c-type">
                            <option value="Main" ${c.campus_type === 'Main' ? 'selected' : ''}>Main Campus</option>
                            <option value="Regional" ${c.campus_type === 'Regional' || !c.campus_type ? 'selected' : ''}>Regional / Branch</option>
                            <option value="Satellite" ${c.campus_type === 'Satellite' ? 'selected' : ''}>Satellite Branch</option>
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
                                <input class="form-check-input c-science-labs" type="checkbox" ${c.science_labs_available ? 'checked' : ''}>
                                <label class="form-check-label small fw-bold">Science Labs</label>
                            </div>
                            <div class="form-check form-switch">
                                <input class="form-check-input c-comp-labs" type="checkbox" ${c.computer_labs_available ? 'checked' : ''}>
                                <label class="form-check-label small fw-bold">Computer Labs</label>
                            </div>
                            <div class="form-check form-switch">
                                <input class="form-check-input c-playground" type="checkbox" ${c.playground_available ? 'checked' : ''}>
                                <label class="form-check-label small fw-bold">Playground</label>
                            </div>
                            <div class="form-check form-switch">
                                <input class="form-check-input c-gps-buses" type="checkbox" ${c.gps_enabled_buses ? 'checked' : ''}>
                                <label class="form-check-label small fw-bold">GPS Buses</label>
                            </div>
                            <div class="form-check form-switch">
                                <input class="form-check-input c-visitor-sys" type="checkbox" ${c.visitor_management_system ? 'checked' : ''}>
                                <label class="form-check-label small fw-bold">Gate Visitor System</label>
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
                                <input class="form-check-input c-cctv" type="checkbox" ${c.cctv_coverage ? 'checked' : ''}>
                                <label class="form-check-label small fw-bold">CCTV Coverage</label>
                            </div>
                        </div>
                    </div>

                    <div class="col-md-4">
                        <label class="form-label fw-bold small">Sports Facilities</label>
                        <input type="text" class="form-control c-sports" value="${toCsv(c.sports_facilities)}" placeholder="Cricket, Football, Badminton...">
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
        });

        div.querySelector('.c-name').addEventListener('input', function (e) {
            div.querySelector('.campus-title-preview').innerText = e.target.value || (isSchool ? 'School Branch' : 'Campus');
        });

        document.getElementById('campusesContainer').appendChild(div);
        initSelect2OnCard(div);
    }

    function appendDeptCard(d = {}, idx = Date.now()) {
        const isSchool = isCurrentEntitySchool();
        const div = document.createElement('div');
        div.className = 'card border mb-3 dept-item';
        div.innerHTML = `
            <div class="card-header bg-light d-flex justify-content-between align-items-center py-2">
                <span class="fw-bold"><i class="fas fa-building text-warning me-2"></i>${isSchool ? 'Academic Wing' : 'Department'}: <span class="dept-title-preview">${d.department_name || (isSchool ? 'Academic Wing' : 'Department')}</span></span>
                <button type="button" class="btn btn-sm btn-outline-danger btn-remove-dept">
                    <i class="fas fa-trash me-1"></i> Remove
                </button>
            </div>
            <div class="card-body">
                <div class="row g-3">
                    <div class="col-md-5">
                        <label class="form-label fw-bold small">${isSchool ? 'Academic Wing / Department Name' : 'Department / Faculty Name'} <span class="text-danger">*</span></label>
                        <input type="text" class="form-control d-name" value="${d.department_name || ''}">
                    </div>
                    <div class="col-md-2">
                        <label class="form-label fw-bold small">Code</label>
                        <input type="text" class="form-control d-code" value="${d.department_code || ''}">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label fw-bold small">Type</label>
                        <select class="form-select d-type">
                            <option value="Academic Wing" ${d.department_type === 'Academic Wing' ? 'selected' : ''}>Academic Wing</option>
                            <option value="Pre-Primary" ${d.department_type === 'Pre-Primary' ? 'selected' : ''}>Pre-Primary / Kindergarten</option>
                            <option value="Primary" ${d.department_type === 'Primary' ? 'selected' : ''}>Primary Wing</option>
                            <option value="Middle" ${d.department_type === 'Middle' ? 'selected' : ''}>Middle School</option>
                            <option value="Secondary" ${d.department_type === 'Secondary' ? 'selected' : ''}>Secondary Wing</option>
                            <option value="Senior Secondary" ${d.department_type === 'Senior Secondary' ? 'selected' : ''}>Senior Secondary Wing</option>
                            <option value="Academic" ${(!d.department_type || d.department_type === 'Academic') ? 'selected' : ''}>Academic Faculty</option>
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
                        <label class="form-label fw-bold small">${isSchool ? 'Wing Head / Principal' : 'HOD Name'}</label>
                        <input type="text" class="form-control d-hod-name" value="${d.head_of_department_name || ''}">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label fw-bold small">Designation</label>
                        <input type="text" class="form-control d-hod-desig" value="${d.head_of_department_designation || (isSchool ? 'Wing Coordinator' : 'Professor & Head')}">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label fw-bold small">Email</label>
                        <input type="email" class="form-control d-hod-email" value="${d.hod_email || ''}">
                    </div>
                    <div class="col-md-2">
                        <label class="form-label fw-bold small">Faculty Count</label>
                        <input type="number" class="form-control d-faculty-count" value="${d.faculty_count || ''}">
                    </div>

                    <div class="col-md-4">
                        <label class="form-label fw-bold small">Discipline Area</label>
                        <input type="text" class="form-control d-discipline-area" value="${d.discipline_area || ''}">
                    </div>
                    <div class="col-md-5">
                        <label class="form-label fw-bold small">Specializations Supported</label>
                        <input type="text" class="form-control d-specs" value="${toCsv(d.specializations_supported)}">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label fw-bold small">Labs Count</label>
                        <input type="number" class="form-control d-labs" value="${d.department_labs_count || ''}">
                    </div>

                    <div class="col-12">
                        <label class="form-label fw-bold small">About Department / Wing</label>
                        <textarea class="form-control d-about" rows="2">${d.about_department || ''}</textarea>
                    </div>
                </div>
            </div>
        `;

        div.querySelector('.btn-remove-dept').addEventListener('click', function () {
            div.remove();
        });

        div.querySelector('.d-name').addEventListener('input', function (e) {
            div.querySelector('.dept-title-preview').innerText = e.target.value || (isSchool ? 'Academic Wing' : 'Department');
        });

        document.getElementById('deptsContainer').appendChild(div);
        initSelect2OnCard(div);
    }

    function appendCourseCard(cr = {}, idx = Date.now()) {
        const isSchool = isCurrentEntitySchool() || !!cr.academic_unit_name || !!cr.grade_range;
        const availableCampuses = getAvailableCampuses();
        const availableDepts = getAvailableDepts();
        let selectedCampus = cr.campus_name || currentTargetCampusName || availableCampuses[0];
        let selectedDept = cr.department_name || currentTargetDeptName || availableDepts[0];

        const div = document.createElement('div');
        div.className = 'card border mb-3 course-item' + (isSchool ? ' cr-school-card' : '');

        if (isSchool) {
            const displayTitle = cr.academic_unit_name || cr.course_name || 'School Class / Curriculum';
            div.innerHTML = `
                <div class="card-header bg-light d-flex justify-content-between align-items-center py-2">
                    <span class="fw-bold"><i class="fas fa-school text-primary me-2"></i>School Class: <span class="course-title-preview">${displayTitle}</span></span>
                    <button type="button" class="btn btn-sm btn-outline-danger btn-remove-course">
                        <i class="fas fa-trash me-1"></i> Remove
                    </button>
                </div>
                <div class="card-body">
                    <div class="row g-3">
                        <div class="col-md-5">
                            <label class="form-label fw-bold small">Academic Unit / Class Name <span class="text-danger">*</span></label>
                            <input type="text" class="form-control cr-academic-name" value="${cr.academic_unit_name || cr.course_name || ''}">
                        </div>
                        <div class="col-md-3">
                            <label class="form-label fw-bold small">School Type</label>
                            <select class="form-select cr-school-type">
                                <option value="Day School" ${cr.school_type === 'Day School' || !cr.school_type ? 'selected' : ''}>Day School</option>
                                <option value="Co-educational" ${cr.school_type === 'Co-educational' ? 'selected' : ''}>Co-educational</option>
                                <option value="Day-cum-Boarding" ${cr.school_type === 'Day-cum-Boarding' ? 'selected' : ''}>Day-cum-Boarding</option>
                                <option value="Boarding School" ${cr.school_type === 'Boarding School' ? 'selected' : ''}>Boarding School</option>
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw-bold small">Education Board</label>
                            <select class="form-select cr-education-board">
                                <option value="CBSE" ${cr.education_board === 'CBSE' || !cr.education_board ? 'selected' : ''}>CBSE</option>
                                <option value="CISCE (ICSE / ISC)" ${cr.education_board === 'CISCE (ICSE / ISC)' || cr.education_board === 'ICSE' ? 'selected' : ''}>CISCE (ICSE / ISC)</option>
                                <option value="State Board" ${cr.education_board === 'State Board' ? 'selected' : ''}>State Board</option>
                                <option value="IB (International Baccalaureate)" ${cr.education_board === 'IB (International Baccalaureate)' || cr.education_board === 'IB' ? 'selected' : ''}>IB</option>
                                <option value="Cambridge (IGCSE)" ${cr.education_board === 'Cambridge (IGCSE)' || cr.education_board === 'IGCSE' ? 'selected' : ''}>Cambridge</option>
                            </select>
                        </div>

                        <div class="col-md-3">
                            <label class="form-label fw-bold small">Grade / Class Range</label>
                            <input type="text" class="form-control cr-grade-range" value="${cr.grade_range || ''}" placeholder="Class 11 - 12">
                        </div>
                        <div class="col-md-3">
                            <label class="form-label fw-bold small">Fee Frequency</label>
                            <select class="form-select cr-fee-freq">
                                <option value="Quarterly" ${cr.fee_payment_frequency === 'Quarterly' || !cr.fee_payment_frequency ? 'selected' : ''}>Quarterly</option>
                                <option value="Monthly" ${cr.fee_payment_frequency === 'Monthly' ? 'selected' : ''}>Monthly</option>
                                <option value="Annually" ${cr.fee_payment_frequency === 'Annually' ? 'selected' : ''}>Annually</option>
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label fw-bold small">Tuition / Term Fee (₹)</label>
                            <input type="text" class="form-control cr-fees" value="${cr.fees || ''}">
                        </div>
                        <div class="col-md-3">
                            <label class="form-label fw-bold small">Total Annual Fee (₹)</label>
                            <input type="text" class="form-control cr-total-fees" value="${cr.total_fees || ''}">
                        </div>

                        <div class="col-md-6">
                            <label class="form-label fw-bold small">Streams Offered</label>
                            <input type="text" class="form-control cr-streams" value="${toCsv(cr.streams_offered)}" placeholder="Science, Commerce, Humanities">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-bold small">Eligibility / Age Criteria</label>
                            <input type="text" class="form-control cr-eligibility" value="${cr.eligibility || ''}">
                        </div>
                        <div class="col-md-12">
                            <label class="form-label fw-bold small">Overview</label>
                            <textarea class="form-control cr-overview" rows="2">${cr.overview || ''}</textarea>
                        </div>
                    </div>
                </div>
            `;
            div.querySelector('.cr-academic-name').addEventListener('input', function (e) {
                div.querySelector('.course-title-preview').innerText = e.target.value || 'School Class';
            });
            div.querySelector('.btn-remove-course').addEventListener('click', function () {
                div.remove();
            });

        } else {
            // HIGHER-ED COURSE
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

                        <div class="col-md-4">
                            <label class="form-label fw-bold small">Fees (Per Year ₹)</label>
                            <input type="text" class="form-control cr-fees" value="${cr.fees || ''}">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw-bold small">Total Fees (₹)</label>
                            <input type="text" class="form-control cr-total-fees" value="${cr.total_fees || ''}">
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
                        const $lvl = $(div).find('.cr-level-id');
                        $lvl.val(chosen.program_level_id).trigger('change.select2');
                    }
                    if (chosen.stream_offered_id) {
                        const $str = $(div).find('.cr-stream-id');
                        $str.val(chosen.stream_offered_id).trigger('change.select2');
                    }
                    if (chosen.discipline_id) {
                        const $disc = $(div).find('.cr-discipline-id');
                        $disc.val(chosen.discipline_id).trigger('change.select2');
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
            });
        }

        document.getElementById('coursesContainer').appendChild(div);
        initSelect2OnCard(div);

        if (!isSchool && window.jQuery && $.fn.select2) {
            const $courseSelect = $(div).find('.cr-course-id');
            $courseSelect.on('select2:select select2:clear', function () {
                this.dispatchEvent(new Event('change'));
            });
        }
    }

    // Add buttons in preview
    document.getElementById('btnAddCampusCard').addEventListener('click', function () {
        appendCampusCard({ campus_name: 'New Campus Branch', campus_type: 'Regional' });
    });
    document.getElementById('btnAddDeptCard').addEventListener('click', function () {
        appendDeptCard({ department_name: 'New Department / Academic Wing' });
    });
    document.getElementById('btnAddCourseCard').addEventListener('click', function () {
        appendCourseCard({ course_name: 'New Degree Course' });
    });

    // ----------------------------------------------------
    // Save Records Handler
    // ----------------------------------------------------
    document.querySelectorAll('.btn-confirm-save-action').forEach(btn => {
        btn.addEventListener('click', function () {
            const saveButtons = document.querySelectorAll('.btn-confirm-save-action');
            saveButtons.forEach(b => {
                b.disabled = true;
                b.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span> Saving...';
            });

            const mode = currentExtractionMode;
            let payload = {
                mode: mode,
                target_organisation_id: currentTargetOrgId || null,
                target_campus_id: currentTargetCampusId || null,
                target_department_id: currentTargetDeptId || null,
                campuses: [],
                departments: [],
                courses: []
            };

            if (mode === 'campus') {
                document.querySelectorAll('.campus-item').forEach(el => {
                    const name = el.querySelector('.c-name')?.value.trim();
                    if (name) {
                        payload.campuses.push({
                            campus_name: name,
                            campus_type: el.querySelector('.c-type')?.value || 'Regional',
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
                            science_labs_available: el.querySelector('.c-science-labs')?.checked || false,
                            computer_labs_available: el.querySelector('.c-comp-labs')?.checked || false,
                            playground_available: el.querySelector('.c-playground')?.checked || false,
                            gps_enabled_buses: el.querySelector('.c-gps-buses')?.checked || false,
                            visitor_management_system: el.querySelector('.c-visitor-sys')?.checked || false,
                            hostel_available: el.querySelector('.c-hostel-avail')?.checked || false,
                            transport_available: el.querySelector('.c-transport')?.checked || false,
                            cctv_coverage: el.querySelector('.c-cctv')?.checked || false,
                            sports_facilities: el.querySelector('.c-sports')?.value.split(',').map(s => s.trim()).filter(Boolean),
                            campus_email: el.querySelector('.c-email')?.value.trim(),
                            campus_contact_numbers: el.querySelector('.c-phones')?.value.split(',').map(s => s.trim()).filter(Boolean)
                        });
                    }
                });
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
                            department_labs_count: el.querySelector('.d-labs')?.value.trim(),
                            about_department: el.querySelector('.d-about')?.value.trim()
                        });
                    }
                });
            } else if (mode === 'course') {
                document.querySelectorAll('.course-item').forEach(el => {
                    const isSchoolCard = el.classList.contains('cr-school-card') || !!el.querySelector('.cr-academic-name');
                    if (isSchoolCard) {
                        const name = el.querySelector('.cr-academic-name')?.value.trim();
                        if (name) {
                            payload.courses.push({
                                academic_unit_name: name,
                                course_name: name,
                                school_type: el.querySelector('.cr-school-type')?.value,
                                education_board: el.querySelector('.cr-education-board')?.value,
                                grade_range: el.querySelector('.cr-grade-range')?.value.trim(),
                                fee_payment_frequency: el.querySelector('.cr-fee-freq')?.value || 'Quarterly',
                                fees: el.querySelector('.cr-fees')?.value.trim(),
                                total_fees: el.querySelector('.cr-total-fees')?.value.trim(),
                                streams_offered: el.querySelector('.cr-streams')?.value.split(',').map(s => s.trim()).filter(Boolean),
                                eligibility: el.querySelector('.cr-eligibility')?.value.trim(),
                                overview: el.querySelector('.cr-overview')?.value.trim()
                            });
                        }
                    } else {
                        const courseId = el.querySelector('.cr-course-id')?.value;
                        const selectedMaster = (globalMasters.courses || []).find(c => String(c.id) === String(courseId));
                        const courseName = selectedMaster ? selectedMaster.name : (el.querySelector('.cr-raw-name')?.innerText || 'Course');

                        payload.courses.push({
                            course_id: courseId || null,
                            course_name: courseName,
                            short_name: el.querySelector('.cr-short-name')?.value.trim(),
                            program_level_id: el.querySelector('.cr-level-id')?.value || null,
                            stream_offered_id: el.querySelector('.cr-stream-id')?.value || null,
                            discipline_id: el.querySelector('.cr-discipline-id')?.value || null,
                            specialization: el.querySelector('.cr-spec')?.value.trim(),
                            duration: el.querySelector('.cr-duration')?.value.trim(),
                            mode: el.querySelector('.cr-mode')?.value.trim() || 'Regular',
                            fees: el.querySelector('.cr-fees')?.value.trim(),
                            total_fees: el.querySelector('.cr-total-fees')?.value.trim(),
                            eligibility: el.querySelector('.cr-eligibility')?.value.trim(),
                            admission_process: el.querySelector('.cr-adm-process')?.value.trim(),
                            overview: el.querySelector('.cr-overview')?.value.trim()
                        });
                    }
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
                    throw new Error(`Server returned HTTP ${response.status} (non-JSON response).`);
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
    if (currentTargetOrgId) {
        $('#aiInputTargetOrg').val(currentTargetOrgId).trigger('change');
    }
});
</script>
@endpush

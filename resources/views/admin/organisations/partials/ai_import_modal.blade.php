<!-- AI Organisation Auto-Import Modal (Complete All Fields) -->
<div class="modal fade" id="aiOrganisationImportModal" tabindex="-1" aria-labelledby="aiOrganisationImportModalLabel" aria-hidden="true" data-bs-backdrop="static">
    <div class="modal-dialog modal-fullscreen-lg-down modal-xl modal-dialog-scrollable">
        <div class="modal-content border-0 shadow-lg">
            <!-- Modal Header -->
            <div class="modal-header text-white" style="background: linear-gradient(135deg, #1e3c72 0%, #2a5298 100%);">
                <div class="d-flex align-items-center">
                    <div class="rounded-circle bg-white text-primary p-2 me-3 d-flex align-items-center justify-content-center shadow-sm" style="width: 44px; height: 44px;">
                        <i class="fas fa-magic fa-lg"></i>
                    </div>
                    <div>
                        <h5 class="modal-title fw-bold mb-0 text-white" id="aiOrganisationImportModalLabel">AI Organisation Auto-Creation (All Fields)</h5>
                        <small class="text-white-50">Extracts complete institutional hierarchy & leaves missing fields ready for manual review</small>
                    </div>
                </div>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>

            <!-- Modal Body -->
            <div class="modal-body p-4 bg-light">
                <!-- STEP 1: Input URL -->
                <div id="aiStepInput">
                    <div class="card border-0 shadow-sm p-4 rounded-3 mb-3 bg-white">
                        <div class="row g-3">
                            <div class="col-md-8">
                                <label class="form-label fw-bold text-dark">
                                    <i class="fas fa-globe text-primary me-1"></i> Organisation / University Website URL <span class="text-danger">*</span>
                                </label>
                                <div class="input-group input-group-lg">
                                    <span class="input-group-text bg-light border-end-0"><i class="fas fa-link text-primary"></i></span>
                                    <input type="url" id="aiInputUrl" class="form-control border-start-0" 
                                           placeholder="e.g. https://www.thapar.edu or https://amity.edu" required>
                                </div>
                                <div class="form-text mt-1 text-muted">
                                    Gemini will inspect official webpages and execute Google Search Grounding to extract full details across all fields.
                                </div>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label fw-bold text-dark">
                                    <i class="fas fa-layer-group text-primary me-1"></i> Organisation Type Hint
                                </label>
                                <select id="aiInputOrgType" class="form-select form-select-lg">
                                    <option value="">Auto-Detect (Recommended)</option>
                                    <option value="University">University</option>
                                    <option value="College">College / Institute</option>
                                    {{-- Commented out other organisation types for now:
                                    <option value="School">School / Chain of Schools</option>
                                    <option value="Exam Conducting Body">Exam Conducting Body</option>
                                    --}}
                                </select>
                            </div>
                        </div>

                        <div class="mt-4 text-end">
                            <button type="button" id="btnRunAiExtraction" class="btn btn-primary btn-lg px-4 shadow-sm">
                                <i class="fas fa-robot me-2"></i> Extract All Fields with AI
                            </button>
                        </div>
                    </div>

                    <div class="alert alert-primary border-0 shadow-sm d-flex align-items-center mb-0 bg-white">
                        <i class="fas fa-info-circle fa-2x me-3 text-primary"></i>
                        <div>
                            <strong>All Fields Guarantee:</strong> All institutional fields (Core, Legal, NAAC, NIRF, Governance, Campuses, Departments, and Courses) will be populated. If any specific data point is not available on the web, it will be left blank in the form for you to review or edit.
                        </div>
                    </div>
                </div>

                <!-- STEP 2: Extraction in Progress (Loading State) -->
                <div id="aiStepLoading" class="d-none text-center py-5">
                    <div class="spinner-grow text-primary mb-3" style="width: 4rem; height: 4rem;" role="status">
                        <span class="visually-hidden">Loading...</span>
                    </div>
                    <h4 class="fw-bold text-dark mb-2">Extracting Complete Institutional Data...</h4>
                    <p class="text-muted mb-4" id="aiLoadingStatusText">Crawling website pages & cross-referencing NAAC grades, NIRF ranks, faculties, and courses...</p>
                    
                    <div class="progress mx-auto shadow-sm" style="height: 10px; max-width: 480px; border-radius: 5px;">
                        <div class="progress-bar progress-bar-striped progress-bar-animated bg-primary" style="width: 100%"></div>
                    </div>
                    <small class="text-muted d-block mt-3">This typically takes 15 - 30 seconds.</small>
                </div>

                <!-- STEP 3: Interactive Multi-Tab Review Screen -->
                <div id="aiStepPreview" class="d-none">
                    <div class="d-flex justify-content-between align-items-center mb-3 bg-white p-3 rounded-3 shadow-sm">
                        <div>
                            <h5 class="fw-bold text-dark mb-0">
                                <i class="fas fa-check-circle text-success me-2"></i> Review All Extracted Fields
                            </h5>
                            <small class="text-muted">You can review or adjust any field before final database creation.</small>
                        </div>
                        <button type="button" id="btnRestartExtraction" class="btn btn-sm btn-outline-secondary">
                            <i class="fas fa-redo me-1"></i> Re-Extract Another URL
                        </button>
                    </div>

                    <!-- Tabs -->
                    <ul class="nav nav-pills mb-3 bg-white p-2 rounded-3 shadow-sm" id="aiReviewTabs" role="tablist">
                        <li class="nav-item" role="presentation">
                            <button class="nav-link active fw-bold" id="tab-org-btn" data-bs-toggle="pill" data-bs-target="#tab-org" type="button" role="tab">
                                <i class="fas fa-university me-1"></i> 1. Organisation (<span id="previewOrgNameBadge">Org</span>)
                            </button>
                        </li>
                        <li class="nav-item" role="presentation">
                            <button class="nav-link fw-bold" id="tab-campuses-btn" data-bs-toggle="pill" data-bs-target="#tab-campuses" type="button" role="tab">
                                <i class="fas fa-city me-1"></i> 2. Campuses <span class="badge bg-primary ms-1" id="badgeCampusesCount">0</span>
                            </button>
                        </li>
                        <li class="nav-item" role="presentation">
                            <button class="nav-link fw-bold" id="tab-depts-btn" data-bs-toggle="pill" data-bs-target="#tab-depts" type="button" role="tab">
                                <i class="fas fa-building me-1"></i> 3. Departments <span class="badge bg-primary ms-1" id="badgeDeptsCount">0</span>
                            </button>
                        </li>
                        <li class="nav-item" role="presentation">
                            <button class="nav-link fw-bold" id="tab-courses-btn" data-bs-toggle="pill" data-bs-target="#tab-courses" type="button" role="tab">
                                <i class="fas fa-graduation-cap me-1"></i> 4. Courses <span class="badge bg-primary ms-1" id="badgeCoursesCount">0</span>
                            </button>
                        </li>
                    </ul>

                    <!-- Tab Contents -->
                    <div class="tab-content" id="aiReviewTabContent">
                        <!-- ==================== TAB 1: ORGANISATION ==================== -->
                        <div class="tab-pane fade show active" id="tab-org" role="tabpanel">
                            <!-- Section A: Core Identity -->
                            <div class="card border-0 shadow-sm mb-3">
                                <div class="card-header bg-white py-3">
                                    <h6 class="fw-bold mb-0 text-primary"><i class="fas fa-id-card me-2"></i>A. Core Institutional Identity</h6>
                                </div>
                                <div class="card-body">
                                    <div class="row g-3">
                                        <div class="col-md-6">
                                            <label class="form-label fw-bold small">Organisation Name <span class="text-danger">*</span></label>
                                            <input type="text" id="aiOrgName" class="form-control" placeholder="Full name of institution">
                                        </div>
                                        <div class="col-md-3">
                                            <label class="form-label fw-bold small">Short Name / Abbr</label>
                                            <input type="text" id="aiOrgShortName" class="form-control" placeholder="e.g. TIET / Amity">
                                        </div>
                                        <div class="col-md-3">
                                            <label class="form-label fw-bold small">Brand Name</label>
                                            <input type="text" id="aiOrgBrandName" class="form-control" placeholder="Brand name">
                                        </div>

                                        <div class="col-md-3">
                                            <label class="form-label fw-bold small">Organisation Type Master <span class="text-danger">*</span></label>
                                            <select id="aiOrgTypeMaster" class="form-select">
                                                <option value="">-- Select Master Type --</option>
                                            </select>
                                        </div>
                                        <div class="col-md-3">
                                            <label class="form-label fw-bold small">Established Year</label>
                                            <input type="number" id="aiOrgEstYear" class="form-control" placeholder="e.g. 1956">
                                        </div>
                                        <div class="col-md-3">
                                            <label class="form-label fw-bold small">Ownership Type</label>
                                            <input type="text" id="aiOrgOwnership" class="form-control" placeholder="e.g. Private / Deemed / Public">
                                        </div>
                                        <div class="col-md-3">
                                            <label class="form-label fw-bold small">University Type</label>
                                            <input type="text" id="aiOrgUniType" class="form-control" placeholder="e.g. Deemed-to-be-University">
                                        </div>
                                        <div class="col-md-3">
                                            <label class="form-label fw-bold small">Brand Type</label>
                                            <select id="aiOrgBrandType" class="form-select">
                                                <option value="Independent">Independent</option>
                                                <option value="Chain">Chain</option>
                                                <option value="Franchise">Franchise</option>
                                            </select>
                                        </div>

                                        <div class="col-md-4">
                                            <label class="form-label fw-bold small">Head Office / Location</label>
                                            <input type="text" id="aiOrgLocation" class="form-control" placeholder="City, State">
                                        </div>
                                        <div class="col-md-4">
                                            <label class="form-label fw-bold small">Central Authority / Trust</label>
                                            <input type="text" id="aiOrgCentralAuth" class="form-control" placeholder="Trust or Society Name">
                                        </div>
                                        <div class="col-md-4">
                                            <label class="form-label fw-bold small">Official Website</label>
                                            <input type="url" id="aiOrgWebsite" class="form-control" placeholder="https://...">
                                        </div>

                                        <div class="col-md-4">
                                            <label class="form-label fw-bold small">Admission Portal URL</label>
                                            <input type="url" id="aiOrgAdmissionUrl" class="form-control" placeholder="https://admissions...">
                                        </div>
                                        <div class="col-md-4">
                                            <label class="form-label fw-bold small">Student Portal URL</label>
                                            <input type="url" id="aiOrgStudentUrl" class="form-control" placeholder="https://portal...">
                                        </div>
                                        <div class="col-md-4">
                                            <label class="form-label fw-bold small">Parent Portal URL</label>
                                            <input type="url" id="aiOrgParentUrl" class="form-control" placeholder="https://parent...">
                                        </div>

                                        <div class="col-md-6">
                                            <label class="form-label fw-bold small">Official Email</label>
                                            <input type="email" id="aiOrgEmail" class="form-control" placeholder="info@institution.edu">
                                        </div>
                                        <div class="col-md-6">
                                            <label class="form-label fw-bold small">Contact Phone</label>
                                            <input type="text" id="aiOrgPhone" class="form-control" placeholder="+91 XXXXXXXXXX">
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Section B: Legal, Regulatory & Accreditations -->
                            <div class="card border-0 shadow-sm mb-3">
                                <div class="card-header bg-white py-3">
                                    <h6 class="fw-bold mb-0 text-success"><i class="fas fa-certificate me-2"></i>B. Legal, Regulatory & Accreditations</h6>
                                </div>
                                <div class="card-body">
                                    <div class="row g-3">
                                        <div class="col-md-3">
                                            <label class="form-label fw-bold small">NAAC Grade</label>
                                            <input type="text" id="aiOrgNaacGrade" class="form-control" placeholder="e.g. A++">
                                        </div>
                                        <div class="col-md-3">
                                            <label class="form-label fw-bold small">NIRF Rank (Overall)</label>
                                            <input type="text" id="aiOrgNirfRank" class="form-control" placeholder="e.g. 26">
                                        </div>
                                        <div class="col-md-3">
                                            <label class="form-label fw-bold small">NIRF Category Rank</label>
                                            <input type="text" id="aiOrgNirfCategory" class="form-control" placeholder="e.g. 29 in Engineering">
                                        </div>
                                        <div class="col-md-3">
                                            <label class="form-label fw-bold small">UGC Approval Number</label>
                                            <input type="text" id="aiOrgUgcApprovalNo" class="form-control" placeholder="UGC Notification No.">
                                        </div>

                                        <div class="col-md-3">
                                            <div class="form-check form-switch mt-3">
                                                <input class="form-check-input" type="checkbox" id="aiOrgUgc">
                                                <label class="form-check-label fw-bold small" for="aiOrgUgc">UGC Recognized</label>
                                            </div>
                                        </div>
                                        <div class="col-md-3">
                                            <div class="form-check form-switch mt-3">
                                                <input class="form-check-input" type="checkbox" id="aiOrgAicte">
                                                <label class="form-check-label fw-bold small" for="aiOrgAicte">AICTE Approved</label>
                                            </div>
                                        </div>
                                        <div class="col-md-3">
                                            <div class="form-check form-switch mt-3">
                                                <input class="form-check-input" type="checkbox" id="aiOrgNaac">
                                                <label class="form-check-label fw-bold small" for="aiOrgNaac">NAAC Accredited</label>
                                            </div>
                                        </div>
                                        <div class="col-md-3">
                                            <div class="form-check form-switch mt-3">
                                                <input class="form-check-input" type="checkbox" id="aiOrgDegreeAuth">
                                                <label class="form-check-label fw-bold small" for="aiOrgDegreeAuth">Degree Awarding Auth.</label>
                                            </div>
                                        </div>

                                        <div class="col-md-6">
                                            <label class="form-label fw-bold small">Statutory Approvals (Comma separated)</label>
                                            <input type="text" id="aiOrgStatutory" class="form-control" placeholder="e.g. UGC, AICTE, NBA, BCI, PCI">
                                        </div>
                                        <div class="col-md-6">
                                            <label class="form-label fw-bold small">International Accreditations</label>
                                            <input type="text" id="aiOrgIntAccreditations" class="form-control" placeholder="e.g. ABET, AACSB, QS 5-Star">
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Section C: Governance & Campus Stats -->
                            <div class="card border-0 shadow-sm mb-3">
                                <div class="card-header bg-white py-3">
                                    <h6 class="fw-bold mb-0 text-info"><i class="fas fa-sitemap me-2"></i>C. Governance & Statistics</h6>
                                </div>
                                <div class="card-body">
                                    <div class="row g-3">
                                        <div class="col-md-4">
                                            <label class="form-label fw-bold small">Chancellor Name</label>
                                            <input type="text" id="aiOrgChancellor" class="form-control" placeholder="Chancellor / Founder">
                                        </div>
                                        <div class="col-md-4">
                                            <label class="form-label fw-bold small">Vice Chancellor / Principal</label>
                                            <input type="text" id="aiOrgViceChancellor" class="form-control" placeholder="Vice Chancellor">
                                        </div>
                                        <div class="col-md-4">
                                            <label class="form-label fw-bold small">Governing Body Name</label>
                                            <input type="text" id="aiOrgGovBody" class="form-control" placeholder="Board of Governors">
                                        </div>

                                        <div class="col-md-3">
                                            <label class="form-label fw-bold small">Number of Campuses</label>
                                            <input type="number" id="aiOrgCampusesCount" class="form-control" placeholder="e.g. 2">
                                        </div>
                                        <div class="col-md-3">
                                            <label class="form-label fw-bold small">Constituent Colleges</label>
                                            <input type="number" id="aiOrgConstituentCount" class="form-control" placeholder="e.g. 5">
                                        </div>
                                        <div class="col-md-3">
                                            <label class="form-label fw-bold small">Affiliated Colleges</label>
                                            <input type="number" id="aiOrgAffiliatedCount" class="form-control" placeholder="e.g. 0">
                                        </div>
                                        <div class="col-md-3">
                                            <div class="form-check form-switch mt-4">
                                                <input class="form-check-input" type="checkbox" id="aiOrgAutonomous">
                                                <label class="form-check-label fw-bold small" for="aiOrgAutonomous">Autonomous Status</label>
                                            </div>
                                        </div>

                                        <div class="col-md-12">
                                            <label class="form-label fw-bold small">Levels Offered (Comma separated)</label>
                                            <input type="text" id="aiOrgLevelsOffered" class="form-control" placeholder="Undergraduate, Postgraduate, Doctoral, Diploma">
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Section D: Content & Overview -->
                            <div class="card border-0 shadow-sm mb-3">
                                <div class="card-header bg-white py-3">
                                    <h6 class="fw-bold mb-0 text-dark"><i class="fas fa-align-left me-2"></i>D. Overview, Vision & Core Values</h6>
                                </div>
                                <div class="card-body">
                                    <div class="row g-3">
                                        <div class="col-md-12">
                                            <label class="form-label fw-bold small">About Organisation / University</label>
                                            <textarea id="aiOrgAbout" rows="3" class="form-control" placeholder="Comprehensive background..."></textarea>
                                        </div>
                                        <div class="col-md-12">
                                            <label class="form-label fw-bold small">Vision & Mission</label>
                                            <textarea id="aiOrgVision" rows="2" class="form-control" placeholder="Vision and mission statements..."></textarea>
                                        </div>
                                        <div class="col-md-12">
                                            <label class="form-label fw-bold small">Core Values (Comma separated)</label>
                                            <input type="text" id="aiOrgCoreValues" class="form-control" placeholder="Excellence, Integrity, Innovation, Diversity">
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- ==================== TAB 2: CAMPUSES ==================== -->
                        <div class="tab-pane fade" id="tab-campuses" role="tabpanel">
                            <div class="d-flex justify-content-between align-items-center mb-3">
                                <span class="fw-bold text-dark">Campuses & Physical Branches</span>
                                <button type="button" class="btn btn-sm btn-primary" id="btnAddCampusCard">
                                    <i class="fas fa-plus me-1"></i> Add Campus
                                </button>
                            </div>
                            <div id="campusesContainer">
                                <!-- Dynamic Detailed Campus Cards -->
                            </div>
                        </div>

                        <!-- ==================== TAB 3: DEPARTMENTS ==================== -->
                        <div class="tab-pane fade" id="tab-depts" role="tabpanel">
                            <div class="d-flex justify-content-between align-items-center mb-3">
                                <span class="fw-bold text-dark">Faculties, Schools & Departments</span>
                                <button type="button" class="btn btn-sm btn-primary" id="btnAddDeptCard">
                                    <i class="fas fa-plus me-1"></i> Add Department
                                </button>
                            </div>
                            <div id="deptsContainer">
                                <!-- Dynamic Detailed Department Cards -->
                            </div>
                        </div>

                        <!-- ==================== TAB 4: COURSES ==================== -->
                        <div class="tab-pane fade" id="tab-courses" role="tabpanel">
                            <div class="d-flex justify-content-between align-items-center mb-3">
                                <span class="fw-bold text-dark">Degree & Diploma Programs Offered</span>
                                <button type="button" class="btn btn-sm btn-primary" id="btnAddCourseCard">
                                    <i class="fas fa-plus me-1"></i> Add Course
                                </button>
                            </div>
                            <div id="coursesContainer">
                                <!-- Dynamic Detailed Course Cards -->
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Modal Footer -->
            <div class="modal-footer bg-white border-top" id="aiModalFooter">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                <button type="button" id="btnConfirmAndSaveAi" class="btn btn-success px-4 d-none shadow-sm">
                    <i class="fas fa-check-circle me-1"></i> Confirm & Save All Records
                </button>
            </div>
        </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
    let currentExtractedData = null;
    let globalMasters = {
        courses: [],
        program_levels: [],
        streams: [],
        disciplines: [],
        organisation_types: []
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
    const btnConfirmAndSaveAi = document.getElementById('btnConfirmAndSaveAi');
    
    const aiStepInput = document.getElementById('aiStepInput');
    const aiStepLoading = document.getElementById('aiStepLoading');
    const aiStepPreview = document.getElementById('aiStepPreview');

    // 1. Trigger AI Extraction
    btnRunAiExtraction.addEventListener('click', function () {
        const url = document.getElementById('aiInputUrl').value.trim();
        const orgType = document.getElementById('aiInputOrgType').value;

        if (!url) {
            alert('Please enter a valid website URL.');
            return;
        }

        aiStepInput.classList.add('d-none');
        aiStepLoading.classList.remove('d-none');
        aiStepPreview.classList.add('d-none');
        btnConfirmAndSaveAi.classList.add('d-none');

        fetch("{{ route('admin.ai-organisations.extract') }}", {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': "{{ csrf_token() }}"
            },
            body: JSON.stringify({
                url: url,
                organisation_type: orgType
            })
        })
        .then(response => response.json())
        .then(result => {
            if (result.success && result.data) {
                currentExtractedData = result.data;
                if (result.masters) {
                    globalMasters = result.masters;
                    populateMasterSelectors(result.masters);
                }
                renderAllFieldsPreview(currentExtractedData);

                aiStepLoading.classList.add('d-none');
                aiStepPreview.classList.remove('d-none');
                btnConfirmAndSaveAi.classList.remove('d-none');
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
            const allowedTypes = ['college', 'university'];
            orgTypeSelect.innerHTML = '<option value="">-- Select Master Type --</option>' +
                masters.organisation_types
                    .filter(ot => allowedTypes.includes(ot.title.toLowerCase()))
                    .map(ot => `<option value="${ot.id}">${ot.title}</option>`).join('');
        }
    }

    // 2. Restart Extraction
    btnRestartExtraction.addEventListener('click', function () {
        aiStepPreview.classList.add('d-none');
        btnConfirmAndSaveAi.classList.add('d-none');
        aiStepInput.classList.remove('d-none');
    });

    // Helper array to comma-string
    function toCsv(val) {
        if (!val) return '';
        if (Array.isArray(val)) return val.join(', ');
        return String(val);
    }

    // 3. Render Complete All Fields Preview
    function renderAllFieldsPreview(data) {
        const org = data.organisation || {};
        const campuses = data.campuses || [];
        const departments = data.departments || [];
        const courses = data.courses || [];

        // Tab 1: Organisation Fields
        document.getElementById('aiOrgName').value = org.name || '';
        document.getElementById('previewOrgNameBadge').innerText = org.short_name || org.name || 'Organisation';
        document.getElementById('aiOrgShortName').value = org.short_name || '';
        document.getElementById('aiOrgBrandName').value = org.brand_name || org.short_name || '';
        
        const matchedOrgTypeId = findBestOrgTypeMatch(org.organisation_type || document.getElementById('aiInputOrgType').value);
        if (document.getElementById('aiOrgTypeMaster')) {
            document.getElementById('aiOrgTypeMaster').value = matchedOrgTypeId;
        }

        document.getElementById('aiOrgEstYear').value = org.established_year || '';
        document.getElementById('aiOrgOwnership').value = org.ownership_type || '';
        document.getElementById('aiOrgUniType').value = org.university_type || org.ownership_type || '';
        document.getElementById('aiOrgBrandType').value = org.brand_type || 'Independent';
        document.getElementById('aiOrgLocation').value = org.head_office_location || '';
        document.getElementById('aiOrgCentralAuth').value = org.central_authority || '';
        document.getElementById('aiOrgWebsite').value = org.official_website || '';
        document.getElementById('aiOrgAdmissionUrl').value = org.admission_portal_url || '';
        document.getElementById('aiOrgStudentUrl').value = org.student_portal_url || '';
        document.getElementById('aiOrgParentUrl').value = org.parent_portal_url || '';
        document.getElementById('aiOrgEmail').value = org.email || '';
        document.getElementById('aiOrgPhone').value = org.phone || '';

        document.getElementById('aiOrgNaacGrade').value = org.naac_grade || '';
        document.getElementById('aiOrgNirfRank').value = org.nirf_rank_overall || '';
        document.getElementById('aiOrgNirfCategory').value = org.nirf_rank_category || '';
        document.getElementById('aiOrgUgcApprovalNo').value = org.ugc_approval_number || '';
        document.getElementById('aiOrgUgc').checked = !!org.ugc_recognized;
        document.getElementById('aiOrgAicte').checked = !!org.aicte_approved;
        document.getElementById('aiOrgNaac').checked = !!(org.naac_accredited || org.naac_grade);
        document.getElementById('aiOrgDegreeAuth').checked = !!org.degree_awarding_authority;
        document.getElementById('aiOrgStatutory').value = toCsv(org.statutory_approvals);
        document.getElementById('aiOrgIntAccreditations').value = toCsv(org.international_accreditations);

        document.getElementById('aiOrgChancellor').value = org.chancellor_name || '';
        document.getElementById('aiOrgViceChancellor').value = org.vice_chancellor_name || '';
        document.getElementById('aiOrgGovBody').value = org.governing_body_name || '';
        document.getElementById('aiOrgCampusesCount').value = org.number_of_campuses || campuses.length || '';
        document.getElementById('aiOrgConstituentCount').value = org.number_of_constituent_colleges || '';
        document.getElementById('aiOrgAffiliatedCount').value = org.number_of_affiliated_colleges || '';
        document.getElementById('aiOrgAutonomous').checked = !!org.autonomous_status;
        document.getElementById('aiOrgLevelsOffered').value = toCsv(org.levels_offered);

        document.getElementById('aiOrgAbout').value = org.about_university || org.about_organisation || '';
        document.getElementById('aiOrgVision').value = org.vision_mission || '';
        document.getElementById('aiOrgCoreValues').value = toCsv(org.core_values);

        // Tab 2: Campuses Cards
        const campusesContainer = document.getElementById('campusesContainer');
        campusesContainer.innerHTML = '';
        if (campuses.length === 0) {
            campuses.push({ campus_name: (org.name || 'Main') + ' - Main Campus', campus_type: 'Main' });
        }
        campuses.forEach((c, idx) => {
            appendCampusCard(c, idx);
        });

        // Tab 3: Departments Cards
        const deptsContainer = document.getElementById('deptsContainer');
        deptsContainer.innerHTML = '';
        if (departments.length === 0) {
            departments.push({ department_name: 'General Faculty' });
        }
        departments.forEach((d, idx) => {
            appendDeptCard(d, idx);
        });

        // Tab 4: Courses Cards
        const coursesContainer = document.getElementById('coursesContainer');
        coursesContainer.innerHTML = '';
        courses.forEach((cr, idx) => {
            appendCourseCard(cr, idx);
        });

        updateAllBadges();
        refreshCourseCampusAndDeptDropdowns();
    }

    // Append Campus Card with ALL Fields
    function appendCampusCard(c = {}, idx = Date.now()) {
        const div = document.createElement('div');
        div.className = 'card border shadow-sm mb-3 campus-item';
        div.innerHTML = `
            <div class="card-header bg-white d-flex justify-content-between align-items-center py-2">
                <span class="fw-bold text-dark"><i class="fas fa-map-marker-alt text-primary me-2"></i>Campus: <span class="campus-title-preview">${c.campus_name || 'Campus'}</span></span>
                <button type="button" class="btn btn-sm btn-outline-danger btn-remove-campus">
                    <i class="fas fa-trash me-1"></i> Remove
                </button>
            </div>
            <div class="card-body">
                <div class="row g-2">
                    <div class="col-md-5">
                        <label class="form-label fw-bold small">Campus Name</label>
                        <input type="text" class="form-control form-control-sm c-name" value="${c.campus_name || ''}">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label fw-bold small">Type</label>
                        <select class="form-select form-select-sm c-type">
                            <option value="Main" ${c.campus_type === 'Main' ? 'selected' : ''}>Main</option>
                            <option value="Regional" ${c.campus_type === 'Regional' ? 'selected' : ''}>Regional</option>
                            <option value="Satellite" ${c.campus_type === 'Satellite' ? 'selected' : ''}>Satellite</option>
                        </select>
                    </div>
                    <div class="col-md-2">
                        <label class="form-label fw-bold small">Established Year</label>
                        <input type="number" class="form-control form-control-sm c-est" value="${c.established_year || ''}">
                    </div>
                    <div class="col-md-2">
                        <label class="form-label fw-bold small">Area (Acres)</label>
                        <input type="number" step="0.1" class="form-control form-control-sm c-acres" value="${c.campus_area_acres || ''}">
                    </div>

                    <div class="col-md-3">
                        <label class="form-label fw-bold small">City</label>
                        <input type="text" class="form-control form-control-sm c-city" value="${c.city || ''}">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label fw-bold small">State</label>
                        <input type="text" class="form-control form-control-sm c-state" value="${c.state || ''}">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label fw-bold small">Country</label>
                        <input type="text" class="form-control form-control-sm c-country" value="${c.country || 'India'}">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label fw-bold small">Pincode</label>
                        <input type="text" class="form-control form-control-sm c-pincode" value="${c.pincode || ''}">
                    </div>

                    <div class="col-md-6">
                        <label class="form-label fw-bold small">Full Address</label>
                        <input type="text" class="form-control form-control-sm c-address" value="${c.full_address || ''}">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label fw-bold small">Nearest Transport Hub</label>
                        <input type="text" class="form-control form-control-sm c-hub" value="${c.nearest_transport_hub || ''}">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label fw-bold small">Google Maps URL</label>
                        <input type="url" class="form-control form-control-sm c-map" value="${c.google_map_url || ''}">
                    </div>

                    <!-- Amenities & Counts -->
                    <div class="col-md-2">
                        <label class="form-label fw-bold small">Classrooms Count</label>
                        <input type="number" class="form-control form-control-sm c-classrooms" value="${c.classrooms_count || ''}">
                    </div>
                    <div class="col-md-2">
                        <label class="form-label fw-bold small">Academic Blocks</label>
                        <input type="number" class="form-control form-control-sm c-blocks" value="${c.academic_blocks_count || ''}">
                    </div>
                    <div class="col-md-2">
                        <label class="form-label fw-bold small">Labs Count</label>
                        <input type="number" class="form-control form-control-sm c-labs" value="${c.laboratories_count || ''}">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label fw-bold small">Hostel Type</label>
                        <select class="form-select form-select-sm c-hostel-type">
                            <option value="">None / Not specified</option>
                            <option value="Both" ${c.hostel_type === 'Both' ? 'selected' : ''}>Both (Boys & Girls)</option>
                            <option value="Boys" ${c.hostel_type === 'Boys' ? 'selected' : ''}>Boys Only</option>
                            <option value="Girls" ${c.hostel_type === 'Girls' ? 'selected' : ''}>Girls Only</option>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label fw-bold small">Hostel Capacity</label>
                        <input type="number" class="form-control form-control-sm c-hostel-cap" value="${c.hostel_capacity || ''}">
                    </div>

                    <!-- Checkbox Switches -->
                    <div class="col-12 py-2 border-top border-bottom my-1 bg-light rounded">
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
                        <input type="text" class="form-control form-control-sm c-sports" value="${toCsv(c.sports_facilities)}" placeholder="Cricket, Gym, Pool...">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label fw-bold small">Campus Email</label>
                        <input type="email" class="form-control form-control-sm c-email" value="${c.campus_email || ''}">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label fw-bold small">Campus Contact Numbers</label>
                        <input type="text" class="form-control form-control-sm c-phones" value="${toCsv(c.campus_contact_numbers)}">
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

    // Append Department Card with ALL Fields
    function appendDeptCard(d = {}, idx = Date.now()) {
        const div = document.createElement('div');
        div.className = 'card border shadow-sm mb-3 dept-item';
        div.innerHTML = `
            <div class="card-header bg-white d-flex justify-content-between align-items-center py-2">
                <span class="fw-bold text-dark"><i class="fas fa-building text-info me-2"></i>Department: <span class="dept-title-preview">${d.department_name || 'Department'}</span></span>
                <button type="button" class="btn btn-sm btn-outline-danger btn-remove-dept">
                    <i class="fas fa-trash me-1"></i> Remove
                </button>
            </div>
            <div class="card-body">
                <div class="row g-2">
                    <div class="col-md-5">
                        <label class="form-label fw-bold small">Department / Faculty Name</label>
                        <input type="text" class="form-control form-control-sm d-name" value="${d.department_name || ''}">
                    </div>
                    <div class="col-md-2">
                        <label class="form-label fw-bold small">Code</label>
                        <input type="text" class="form-control form-control-sm d-code" value="${d.department_code || ''}">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label fw-bold small">Department Type</label>
                        <input type="text" class="form-control form-control-sm d-type" value="${d.department_type || 'Academic Faculty'}">
                    </div>
                    <div class="col-md-2">
                        <label class="form-label fw-bold small">Established Year</label>
                        <input type="number" class="form-control form-control-sm d-est" value="${d.established_year || ''}">
                    </div>

                    <div class="col-md-4">
                        <label class="form-label fw-bold small">HOD Name</label>
                        <input type="text" class="form-control form-control-sm d-hod-name" value="${d.head_of_department_name || ''}">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label fw-bold small">HOD Designation</label>
                        <input type="text" class="form-control form-control-sm d-hod-desig" value="${d.head_of_department_designation || 'Professor & Head'}">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label fw-bold small">HOD Email</label>
                        <input type="email" class="form-control form-control-sm d-hod-email" value="${d.hod_email || ''}">
                    </div>
                    <div class="col-md-2">
                        <label class="form-label fw-bold small">Faculty Count</label>
                        <input type="number" class="form-control form-control-sm d-faculty-count" value="${d.faculty_count || ''}">
                    </div>

                    <div class="col-md-3">
                        <label class="form-label fw-bold small">Discipline Area</label>
                        <input type="text" class="form-control form-control-sm d-discipline-area" value="${d.discipline_area || ''}">
                    </div>
                    <div class="col-md-5">
                        <label class="form-label fw-bold small">Specializations Supported</label>
                        <input type="text" class="form-control form-control-sm d-specs" value="${toCsv(d.specializations_supported)}">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label fw-bold small">Education Levels</label>
                        <input type="text" class="form-control form-control-sm d-levels" value="${toCsv(d.education_levels_supported)}">
                    </div>

                    <div class="col-md-3">
                        <label class="form-label fw-bold small">Labs Count</label>
                        <input type="number" class="form-control form-control-sm d-labs" value="${d.department_labs_count || ''}">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label fw-bold small">Publications Count</label>
                        <input type="number" class="form-control form-control-sm d-pubs" value="${d.research_publications_count || ''}">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label fw-bold small">Funded Projects</label>
                        <input type="number" class="form-control form-control-sm d-projects" value="${d.funded_projects_count || ''}">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label fw-bold small">Patents Filed</label>
                        <input type="number" class="form-control form-control-sm d-patents" value="${d.patents_filed_count || ''}">
                    </div>

                    <!-- Switches -->
                    <div class="col-12 py-2 border-top border-bottom my-1 bg-light rounded">
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
                        <textarea class="form-control form-control-sm d-about" rows="2">${d.about_department || ''}</textarea>
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

    // Append Course Card with ALL Master Dropdowns (Auto-Selected)
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
        div.className = 'card border shadow-sm mb-3 course-item';
        div.innerHTML = `
            <div class="card-header bg-white d-flex justify-content-between align-items-center py-2">
                <div class="d-flex align-items-center gap-2">
                    <span class="fw-bold text-dark"><i class="fas fa-graduation-cap text-success me-2"></i>Course: <span class="course-title-preview">${matchedCourse ? matchedCourse.name : (rawAiName || 'Course')}</span></span>
                    <span class="match-badge-container">
                        ${selectedCourseId 
                            ? '<span class="badge bg-success text-white"><i class="fas fa-check-circle me-1"></i>Master Auto-Selected</span>' 
                            : '<span class="badge bg-warning text-dark"><i class="fas fa-exclamation-triangle me-1"></i>Select Master Course</span>'}
                    </span>
                </div>
                <button type="button" class="btn btn-sm btn-outline-danger btn-remove-course">
                    <i class="fas fa-trash me-1"></i> Remove
                </button>
            </div>
            <div class="card-body">
                <div class="row g-2">
                    <!-- Master Course Select -->
                    <div class="col-md-6">
                        <label class="form-label fw-bold small">
                            <i class="fas fa-database text-primary me-1"></i> Master Course <span class="text-danger">*</span> (From Masters)
                        </label>
                        <select class="form-select form-select-sm cr-course-id fw-bold border-primary shadow-sm">
                            <option value="">-- Select Master Course --</option>
                            ${(globalMasters.courses || []).map(c => `
                                <option value="${c.id}" ${String(c.id) === String(selectedCourseId) ? 'selected' : ''}>
                                    ${c.name}
                                </option>
                            `).join('')}
                        </select>
                        <div class="mt-1 small text-muted">
                            <i class="fas fa-robot text-primary me-1"></i> AI Extracted: <strong class="badge bg-light text-dark border cr-raw-name">${rawAiName || 'N/A'}</strong>
                        </div>
                    </div>

                    <div class="col-md-3">
                        <label class="form-label fw-bold small">Short Name / Abbr</label>
                        <input type="text" class="form-control form-control-sm cr-short-name" value="${rawAiShort}">
                    </div>

                    <!-- Program Level Master Select -->
                    <div class="col-md-3">
                        <label class="form-label fw-bold small"><i class="fas fa-layer-group text-info me-1"></i> Program Level</label>
                        <select class="form-select form-select-sm cr-level-id">
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
                        <label class="form-label fw-bold small"><i class="fas fa-stream text-secondary me-1"></i> Stream</label>
                        <select class="form-select form-select-sm cr-stream-id">
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
                        <label class="form-label fw-bold small"><i class="fas fa-book text-secondary me-1"></i> Discipline</label>
                        <select class="form-select form-select-sm cr-discipline-id">
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
                        <input type="text" class="form-control form-control-sm cr-spec" value="${cr.specialization || ''}">
                    </div>

                    <!-- Campus & Department Association -->
                    <div class="col-md-4">
                        <label class="form-label fw-bold small"><i class="fas fa-map-marker-alt text-primary me-1"></i> Campus</label>
                        <select class="form-select form-select-sm cr-campus-select">
                            ${availableCampuses.map(c => `<option value="${c}" ${c === selectedCampus ? 'selected' : ''}>${c}</option>`).join('')}
                        </select>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label fw-bold small"><i class="fas fa-building text-info me-1"></i> Department</label>
                        <select class="form-select form-select-sm cr-dept-select">
                            ${availableDepts.map(d => `<option value="${d}" ${d === selectedDept ? 'selected' : ''}>${d}</option>`).join('')}
                        </select>
                    </div>

                    <div class="col-md-2">
                        <label class="form-label fw-bold small">Duration</label>
                        <input type="text" class="form-control form-control-sm cr-duration" value="${defaultDuration}">
                    </div>
                    <div class="col-md-2">
                        <label class="form-label fw-bold small">Mode</label>
                        <select class="form-select form-select-sm cr-mode">
                            <option value="Regular" ${cr.mode === 'Regular' || !cr.mode ? 'selected' : ''}>Regular</option>
                            <option value="Online" ${cr.mode === 'Online' ? 'selected' : ''}>Online</option>
                            <option value="Distance" ${cr.mode === 'Distance' ? 'selected' : ''}>Distance</option>
                            <option value="Part-time" ${cr.mode === 'Part-time' ? 'selected' : ''}>Part-time</option>
                        </select>
                    </div>

                    <div class="col-md-2">
                        <label class="form-label fw-bold small">Fees (Per Year ₹)</label>
                        <input type="text" class="form-control form-control-sm cr-fees" value="${cr.fees || ''}">
                    </div>
                    <div class="col-md-2">
                        <label class="form-label fw-bold small">Total Fees (₹)</label>
                        <input type="text" class="form-control form-control-sm cr-total-fees" value="${cr.total_fees || ''}">
                    </div>
                    <div class="col-md-2">
                        <label class="form-label fw-bold small">Admission Fee (₹)</label>
                        <input type="text" class="form-control form-control-sm cr-adm-fee" value="${cr.admission_fee || ''}">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label fw-bold small">Annual Fee Range</label>
                        <input type="text" class="form-control form-control-sm cr-fee-range" value="${cr.annual_fee_range || ''}">
                    </div>
                    <div class="col-md-1">
                        <label class="form-label fw-bold small">Rating</label>
                        <input type="text" class="form-control form-control-sm cr-rating" value="${cr.rating || '4.5'}">
                    </div>
                    <div class="col-md-2">
                        <label class="form-label fw-bold small">ROI / Package</label>
                        <input type="text" class="form-control form-control-sm cr-roi" value="${cr.roi || ''}">
                    </div>

                    <div class="col-md-6">
                        <label class="form-label fw-bold small">Eligibility</label>
                        <textarea class="form-control form-control-sm cr-eligibility" rows="2">${cr.eligibility || ''}</textarea>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label fw-bold small">Admission Process</label>
                        <textarea class="form-control form-control-sm cr-adm-process" rows="2">${cr.admission_process || ''}</textarea>
                    </div>

                    <div class="col-md-6">
                        <label class="form-label fw-bold small">Entrance Exams</label>
                        <input type="text" class="form-control form-control-sm cr-exams" value="${cr.entrance_exams || ''}">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label fw-bold small">Placement Details</label>
                        <input type="text" class="form-control form-control-sm cr-placements" value="${cr.placement_details || ''}">
                    </div>

                    <div class="col-12 py-2 border-top border-bottom my-1 bg-light rounded">
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
                        <textarea class="form-control form-control-sm cr-overview" rows="2">${cr.overview || ''}</textarea>
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
                badgeContainer.innerHTML = '<span class="badge bg-success text-white"><i class="fas fa-check-circle me-1"></i>Master Selected</span>';

                // Auto sync level, stream, discipline if available
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
                badgeContainer.innerHTML = '<span class="badge bg-warning text-dark"><i class="fas fa-exclamation-triangle me-1"></i>Select Master Course</span>';
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
        document.getElementById('badgeCampusesCount').innerText = document.querySelectorAll('.campus-item').length;
        document.getElementById('badgeDeptsCount').innerText = document.querySelectorAll('.dept-item').length;
        document.getElementById('badgeCoursesCount').innerText = document.querySelectorAll('.course-item').length;
    }

    // 4. Confirm & Save Complete Data Payload (Strictly links with existing Masters)
    btnConfirmAndSaveAi.addEventListener('click', function () {
        btnConfirmAndSaveAi.disabled = true;
        btnConfirmAndSaveAi.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span> Saving All Records...';

        const orgTypeSelect = document.getElementById('aiOrgTypeMaster');
        const selectedOrgTypeId = orgTypeSelect ? orgTypeSelect.value : '';
        const selectedOrgTypeName = orgTypeSelect && orgTypeSelect.selectedIndex >= 0 
            ? orgTypeSelect.options[orgTypeSelect.selectedIndex].text 
            : 'University';

        const payload = {
            organisation: {
                name: document.getElementById('aiOrgName').value.trim(),
                short_name: document.getElementById('aiOrgShortName').value.trim(),
                brand_name: document.getElementById('aiOrgBrandName').value.trim(),
                organisation_type_id: selectedOrgTypeId || null,
                organisation_type: selectedOrgTypeName,
                established_year: document.getElementById('aiOrgEstYear').value.trim(),
                ownership_type: document.getElementById('aiOrgOwnership').value.trim(),
                university_type: document.getElementById('aiOrgUniType').value.trim(),
                brand_type: document.getElementById('aiOrgBrandType').value,
                head_office_location: document.getElementById('aiOrgLocation').value.trim(),
                central_authority: document.getElementById('aiOrgCentralAuth').value.trim(),
                official_website: document.getElementById('aiOrgWebsite').value.trim(),
                admission_portal_url: document.getElementById('aiOrgAdmissionUrl').value.trim(),
                student_portal_url: document.getElementById('aiOrgStudentUrl').value.trim(),
                parent_portal_url: document.getElementById('aiOrgParentUrl').value.trim(),
                email: document.getElementById('aiOrgEmail').value.trim(),
                phone: document.getElementById('aiOrgPhone').value.trim(),
                naac_grade: document.getElementById('aiOrgNaacGrade').value.trim(),
                naac_accredited: document.getElementById('aiOrgNaac').checked,
                nirf_rank_overall: document.getElementById('aiOrgNirfRank').value.trim(),
                nirf_rank_category: document.getElementById('aiOrgNirfCategory').value.trim(),
                ugc_recognized: document.getElementById('aiOrgUgc').checked,
                ugc_approval_number: document.getElementById('aiOrgUgcApprovalNo').value.trim(),
                aicte_approved: document.getElementById('aiOrgAicte').checked,
                degree_awarding_authority: document.getElementById('aiOrgDegreeAuth').checked,
                statutory_approvals: document.getElementById('aiOrgStatutory').value.split(',').map(s => s.trim()).filter(Boolean),
                international_accreditations: document.getElementById('aiOrgIntAccreditations').value.split(',').map(s => s.trim()).filter(Boolean),
                chancellor_name: document.getElementById('aiOrgChancellor').value.trim(),
                vice_chancellor_name: document.getElementById('aiOrgViceChancellor').value.trim(),
                governing_body_name: document.getElementById('aiOrgGovBody').value.trim(),
                number_of_campuses: document.getElementById('aiOrgCampusesCount').value.trim(),
                number_of_constituent_colleges: document.getElementById('aiOrgConstituentCount').value.trim(),
                number_of_affiliated_colleges: document.getElementById('aiOrgAffiliatedCount').value.trim(),
                autonomous_status: document.getElementById('aiOrgAutonomous').checked,
                levels_offered: document.getElementById('aiOrgLevelsOffered').value.split(',').map(s => s.trim()).filter(Boolean),
                about_university: document.getElementById('aiOrgAbout').value.trim(),
                about_organisation: document.getElementById('aiOrgAbout').value.trim(),
                vision_mission: document.getElementById('aiOrgVision').value.trim(),
                core_values: document.getElementById('aiOrgCoreValues').value.split(',').map(s => s.trim()).filter(Boolean)
            },
            campuses: [],
            departments: [],
            courses: []
        };

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
                    window.location.reload();
                }
            } else {
                throw new Error(res.message || 'Failed to save organisation.');
            }
        })
        .catch(err => {
            alert('Error saving data: ' + err.message);
            btnConfirmAndSaveAi.disabled = false;
            btnConfirmAndSaveAi.innerHTML = '<i class="fas fa-check-circle me-1"></i> Confirm & Save All Records';
        });
    });
});
</script>

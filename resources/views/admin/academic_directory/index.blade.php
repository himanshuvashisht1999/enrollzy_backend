@extends('admin.layouts.master')

@section('title', 'Academic Master Directory')

@push('css')
<style>
    /* ==========================================================================
       EXECUTIVE DIRECTORY DESIGN SYSTEM (Modern, Minimalist, Pro UI/UX)
       ========================================================================== */
    :root {
        --dir-primary: #4f46e5;
        --dir-primary-hover: #4338ca;
        --dir-primary-light: #eef2ff;
        --dir-cyan: #0284c7;
        --dir-cyan-light: #f0f9ff;
        --dir-purple: #7c3aed;
        --dir-purple-light: #f5f3ff;
        --dir-emerald: #059669;
        --dir-emerald-light: #ecfdf5;
        --dir-surface: #ffffff;
        --dir-bg: #f8fafc;
        --dir-border: #e2e8f0;
        --dir-text-main: #0f172a;
        --dir-text-muted: #64748b;
    }

    body {
        background-color: #f8fafc !important;
    }

    /* 1. TOP STARTING FILTERS BAR */
    .filter-bar-card {
        background: #ffffff;
        border-radius: 14px;
        border: 1px solid var(--dir-border);
        padding: 16px 20px;
        box-shadow: 0 1px 3px rgba(0,0,0,0.02), 0 4px 12px rgba(0,0,0,0.02);
    }
    .filter-label {
        font-size: 0.73rem;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: 0.5px;
        color: var(--dir-text-muted);
        margin-bottom: 5px;
        display: block;
    }

    /* Select2 Custom Polish */
    .select2-container--default .select2-selection--single {
        height: 38px !important;
        border: 1px solid #cbd5e1 !important;
        border-radius: 8px !important;
        padding: 4px 10px !important;
        background-color: #ffffff !important;
        transition: all 0.2s ease;
    }
    .select2-container--default .select2-selection--single .select2-selection__rendered {
        line-height: 28px !important;
        font-size: 0.86rem !important;
        color: #1e293b !important;
        font-weight: 500 !important;
        padding-left: 0 !important;
    }
    .select2-container--default .select2-selection--single .select2-selection__arrow {
        height: 36px !important;
        right: 8px !important;
    }
    .select2-container--default.select2-container--focus .select2-selection--single,
    .select2-container--default.select2-container--open .select2-selection--single {
        border-color: var(--dir-primary) !important;
        box-shadow: 0 0 0 3px rgba(79, 70, 229, 0.12) !important;
    }
    .select2-dropdown {
        border-radius: 10px !important;
        border: 1px solid #cbd5e1 !important;
        box-shadow: 0 10px 25px rgba(0,0,0,0.1) !important;
        overflow: hidden !important;
        z-index: 1060 !important;
    }
    .select2-results__option {
        padding: 8px 12px !important;
        font-size: 0.86rem !important;
    }
    .select2-results__option--highlighted[aria-selected] {
        background-color: var(--dir-primary) !important;
    }

    /* 2. DYNAMIC METRIC CARDS (TAB SELECTORS) */
    .metric-deck {
        display: grid;
        grid-template-columns: repeat(4, 1fr);
        gap: 14px;
    }
    @media (max-width: 992px) {
        .metric-deck { grid-template-columns: repeat(2, 1fr); }
    }
    @media (max-width: 576px) {
        .metric-deck { grid-template-columns: 1fr; }
    }

    .metric-card {
        background: #ffffff;
        border-radius: 12px;
        padding: 14px 18px;
        border: 1.5px solid var(--dir-border);
        box-shadow: 0 1px 3px rgba(0,0,0,0.02);
        cursor: pointer;
        transition: all 0.2s cubic-bezier(0.4, 0, 0.2, 1);
        position: relative;
        overflow: hidden;
        user-select: none;
    }
    .metric-card:hover {
        transform: translateY(-2px);
        box-shadow: 0 8px 16px -4px rgba(15, 23, 42, 0.08);
        border-color: #cbd5e1;
    }
    .metric-card.active-card {
        border-color: var(--dir-primary) !important;
        background: #fcfdff !important;
        box-shadow: 0 0 0 2px rgba(79, 70, 229, 0.15), 0 8px 20px -4px rgba(79, 70, 229, 0.1) !important;
    }
    .metric-card::before {
        content: '';
        position: absolute;
        bottom: 0;
        left: 0;
        right: 0;
        height: 3px;
        opacity: 0;
        transition: opacity 0.2s ease;
    }
    .metric-card.active-card::before {
        opacity: 1;
    }
    .metric-card.card-orgs::before { background: var(--dir-primary); }
    .metric-card.card-campuses::before { background: var(--dir-cyan); }
    .metric-card.card-depts::before { background: var(--dir-purple); }
    .metric-card.card-courses::before { background: var(--dir-emerald); }

    .metric-icon-box {
        width: 36px;
        height: 36px;
        border-radius: 8px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1rem;
        flex-shrink: 0;
    }
    .metric-title {
        font-size: 0.72rem;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: 0.5px;
        color: var(--dir-text-muted);
    }
    .metric-number {
        font-size: 1.65rem;
        font-weight: 800;
        line-height: 1.1;
        letter-spacing: -0.02em;
        color: var(--dir-text-main);
    }
    .metric-subtext {
        font-size: 0.72rem;
        color: var(--dir-text-muted);
        font-weight: 500;
    }

    /* 3. BOTTOM DATA TABLE CARD */
    .table-surface {
        background: #ffffff;
        border-radius: 14px;
        border: 1px solid var(--dir-border);
        box-shadow: 0 1px 3px rgba(0,0,0,0.02), 0 4px 12px rgba(0,0,0,0.02);
        overflow: hidden;
    }
    .table-topbar {
        padding: 14px 20px;
        border-bottom: 1px solid var(--dir-border);
        background: #ffffff;
    }
    .table-search-input {
        height: 38px;
        border-radius: 8px;
        border: 1px solid #cbd5e1;
        padding: 6px 14px;
        font-size: 0.86rem;
        width: 280px;
        transition: all 0.2s ease;
    }
    .table-search-input:focus {
        border-color: var(--dir-primary);
        box-shadow: 0 0 0 3px rgba(79, 70, 229, 0.12);
        outline: none;
    }

    /* Custom Table Styling */
    .dir-table {
        width: 100%;
        margin-bottom: 0;
        border-collapse: separate;
        border-spacing: 0;
    }
    .dir-table thead th {
        background: #f8fafc;
        color: #475569;
        font-size: 0.72rem;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: 0.6px;
        border-bottom: 1px solid var(--dir-border);
        padding: 12px 18px;
        white-space: nowrap;
    }
    .dir-table tbody td {
        padding: 13px 18px;
        vertical-align: middle;
        font-size: 0.87rem;
        border-bottom: 1px solid #f1f5f9;
        color: #334155;
    }
    .dir-table tbody tr:hover td {
        background-color: #f8faff;
    }

    /* Avatars, Badges, Chips */
    .org-avatar-box {
        width: 36px;
        height: 36px;
        min-width: 36px;
        border-radius: 8px;
        display: flex;
        align-items: center;
        justify-content: center;
        color: #ffffff;
        font-weight: 700;
        font-size: 0.9rem;
        flex-shrink: 0;
    }
    .entity-title {
        font-size: 0.9rem;
        font-weight: 700;
        color: #0f172a;
        text-decoration: none;
        transition: color 0.15s ease;
    }
    .entity-title:hover {
        color: var(--dir-primary);
    }
    .code-pill {
        background: #f1f5f9;
        color: #64748b;
        font-family: monospace;
        font-size: 0.7rem;
        font-weight: 600;
        padding: 1px 6px;
        border-radius: 5px;
        border: 1px solid #e2e8f0;
    }
    .badge-pill-indigo {
        background: #eef2ff;
        color: #4f46e5;
        border: 1px solid #c7d2fe;
        font-size: 0.72rem;
        font-weight: 600;
        padding: 2px 8px;
        border-radius: 6px;
    }
    .badge-pill-gold {
        background: #fef3c7;
        color: #92400e;
        border: 1px solid #fde68a;
        font-size: 0.72rem;
        font-weight: 600;
        padding: 2px 7px;
        border-radius: 6px;
    }
    .badge-pill-neutral {
        background: #f8fafc;
        color: #475569;
        border: 1px solid #e2e8f0;
        font-size: 0.72rem;
        font-weight: 600;
        padding: 2px 7px;
        border-radius: 6px;
    }
    
    /* Interactive Stat Chips */
    .stat-chip {
        display: inline-flex;
        align-items: center;
        font-size: 0.74rem;
        font-weight: 600;
        padding: 3px 9px;
        border-radius: 16px;
        text-decoration: none;
        transition: all 0.2s ease;
        cursor: pointer;
    }
    .chip-cyan {
        background: #f0f9ff;
        color: #0284c7;
        border: 1px solid #bae6fd;
    }
    .chip-cyan:hover {
        background: #0284c7;
        color: #ffffff;
    }
    .chip-indigo {
        background: #eef2ff;
        color: #4f46e5;
        border: 1px solid #c7d2fe;
    }
    .chip-indigo:hover {
        background: #4f46e5;
        color: #ffffff;
    }
    .chip-emerald {
        background: #ecfdf5;
        color: #059669;
        border: 1px solid #a7f3d0;
    }
    .chip-emerald:hover {
        background: #059669;
        color: #ffffff;
    }

    /* Status Pills */
    .status-pill {
        display: inline-flex;
        align-items: center;
        gap: 5px;
        font-size: 0.73rem;
        font-weight: 600;
        padding: 3px 9px;
        border-radius: 16px;
    }
    .status-active {
        background: #ecfdf5;
        color: #059669;
        border: 1px solid #a7f3d0;
    }
    .status-draft {
        background: #f1f5f9;
        color: #64748b;
        border: 1px solid #e2e8f0;
    }
    .status-dot {
        width: 6px;
        height: 6px;
        border-radius: 50%;
        background: currentColor;
    }

    /* Action Buttons */
    .action-btn {
        width: 30px;
        height: 30px;
        border-radius: 7px;
        border: 1px solid #e2e8f0;
        background: #ffffff;
        color: #64748b;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        transition: all 0.15s ease;
        font-size: 0.78rem;
        cursor: pointer;
        text-decoration: none;
    }
    .action-btn:hover {
        transform: translateY(-1px);
        box-shadow: 0 2px 5px rgba(0,0,0,0.08);
    }
    .action-view:hover { background: #f8fafc; color: #0f172a; border-color: #cbd5e1; }
    .action-campus:hover { background: #fef3c7; color: #d97706; border-color: #fde68a; }
    .action-add:hover { background: #ecfdf5; color: #059669; border-color: #a7f3d0; }
    .action-edit:hover { background: #eef2ff; color: #4f46e5; border-color: #c7d2fe; }

    /* Infinite Scroll Spinner & End Marker */
    .scroll-loader {
        padding: 24px;
        text-align: center;
        color: var(--dir-text-muted);
        font-size: 0.85rem;
    }
    .scroll-end-marker {
        padding: 16px;
        text-align: center;
        color: #94a3b8;
        font-size: 0.8rem;
        font-weight: 500;
        border-top: 1px dashed #e2e8f0;
    }
</style>
@endpush

@section('content')
<div class="container-fluid py-3">

    <!-- ========================================================================= -->
    <!-- 1. TOP STARTING FILTERS BAR (Zero Bloat, 5 Cascading Dropdowns + Reset)  -->
    <!-- ========================================================================= -->
    <div class="filter-bar-card mb-3">
        <div class="row g-2.5 align-items-end">
            <!-- 1. Organisation Type -->
            <div class="col-xl col-lg-4 col-md-6">
                <label class="filter-label">Organisation Type</label>
                <select id="filter_organisation_type_id" class="form-control select2-filter">
                    <option value="">All Org Types</option>
                    @foreach($organisationTypes as $type)
                        <option value="{{ $type->id }}">{{ $type->title }}</option>
                    @endforeach
                </select>
            </div>

            <!-- 2. Organisation / University -->
            <div class="col-xl-3 col-lg-4 col-md-6">
                <label class="filter-label">Organisation / University</label>
                <select id="filter_organisation_id" class="form-control select2-filter">
                    <option value="">All Organisations</option>
                    @foreach($organisationsList as $org)
                        <option value="{{ $org->id }}" data-type-id="{{ $org->organisation_type_id }}">{{ $org->name }}</option>
                    @endforeach
                </select>
            </div>

            <!-- 3. Campus Location -->
            <div class="col-xl col-lg-4 col-md-6">
                <label class="filter-label">Campus</label>
                <select id="filter_campus_id" class="form-control select2-filter">
                    <option value="">All Campuses</option>
                    @foreach($campusesList as $campus)
                        @php $city = $campus->city ? " ({$campus->city})" : ""; @endphp
                        <option value="{{ $campus->id }}" data-org-id="{{ $campus->organisation_id }}">{{ $campus->campus_name }}{{ $city }}</option>
                    @endforeach
                </select>
            </div>

            <!-- 4. Academic Department -->
            <div class="col-xl col-lg-4 col-md-6">
                <label class="filter-label">Department</label>
                <select id="filter_department_id" class="form-control select2-filter">
                    <option value="">All Departments</option>
                    @foreach($departmentsList as $dept)
                        @php $code = $dept->department_code ? " [{$dept->department_code}]" : ""; @endphp
                        <option value="{{ $dept->id }}" data-campus-id="{{ $dept->campus_id }}" data-org-id="{{ $dept->organisation_id }}">{{ $dept->department_name }}{{ $code }}</option>
                    @endforeach
                </select>
            </div>

            <!-- 5. Offered Course / Program -->
            <div class="col-xl col-lg-4 col-md-6">
                <label class="filter-label">Course</label>
                <select id="filter_course_id" class="form-control select2-filter">
                    <option value="">All Courses</option>
                    @foreach($coursesList as $crs)
                        <option value="{{ $crs->id }}">{{ $crs->name }}</option>
                    @endforeach
                </select>
            </div>

            <!-- Reset Button -->
            <div class="col-auto">
                <button type="button" class="btn btn-light border d-flex align-items-center gap-1.5 px-3 rounded-2 fw-semibold text-muted" id="btn-reset-filters" style="height: 38px; font-size: 0.85rem;" title="Reset all filters">
                    <i class="fas fa-undo-alt"></i>
                    <span>Reset</span>
                </button>
            </div>
        </div>
    </div>

    <!-- ========================================================================= -->
    <!-- 2. DYNAMIC METRIC CARDS (Tab Selectors with Live Counts)                  -->
    <!-- ========================================================================= -->
    <div class="metric-deck mb-3">
        <!-- Card 1: Organisations -->
        <div class="metric-card card-orgs active-card" id="card-tab-organisations" onclick="selectDirectoryTab('organisations')">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <div class="metric-title">Total Organisations</div>
                    <div class="metric-number" id="count-orgs">{{ number_format($totalOrgs) }}</div>
                    <div class="metric-subtext">Institutions & Universities</div>
                </div>
                <div class="metric-icon-box" style="background: var(--dir-primary-light); color: var(--dir-primary);">
                    <i class="fas fa-university"></i>
                </div>
            </div>
        </div>

        <!-- Card 2: Campuses -->
        <div class="metric-card card-campuses" id="card-tab-campuses" onclick="selectDirectoryTab('campuses')">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <div class="metric-title">Total Campuses</div>
                    <div class="metric-number" id="count-campuses">{{ number_format($totalCampuses) }}</div>
                    <div class="metric-subtext">Branch Locations</div>
                </div>
                <div class="metric-icon-box" style="background: var(--dir-cyan-light); color: var(--dir-cyan);">
                    <i class="fas fa-city"></i>
                </div>
            </div>
        </div>

        <!-- Card 3: Departments -->
        <div class="metric-card card-depts" id="card-tab-departments" onclick="selectDirectoryTab('departments')">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <div class="metric-title">Total Departments</div>
                    <div class="metric-number" id="count-depts">{{ number_format($totalDepartments) }}</div>
                    <div class="metric-subtext">Academic Faculties</div>
                </div>
                <div class="metric-icon-box" style="background: var(--dir-purple-light); color: var(--dir-purple);">
                    <i class="fas fa-building"></i>
                </div>
            </div>
        </div>

        <!-- Card 4: Courses -->
        <div class="metric-card card-courses" id="card-tab-courses" onclick="selectDirectoryTab('courses')">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <div class="metric-title">Total Courses</div>
                    <div class="metric-number" id="count-courses">{{ number_format($totalCourses) }}</div>
                    <div class="metric-subtext">Offered Programs</div>
                </div>
                <div class="metric-icon-box" style="background: var(--dir-emerald-light); color: var(--dir-emerald);">
                    <i class="fas fa-graduation-cap"></i>
                </div>
            </div>
        </div>
    </div>

    <!-- ========================================================================= -->
    <!-- 3. BOTTOM DATA TABLE (Infinite Scroll on Scroll, No Pagination Buttons)   -->
    <!-- ========================================================================= -->
    <div class="table-surface">
        <!-- Top Toolbar -->
        <div class="table-topbar d-flex flex-wrap justify-content-between align-items-center gap-2">
            <div class="d-flex align-items-center gap-2.5">
                <div class="position-relative">
                    <i class="fas fa-search position-absolute top-50 translate-middle-y text-muted" style="left: 12px; font-size: 0.8rem;"></i>
                    <input type="text" id="dir-search-input" class="table-search-input ps-4" placeholder="Search organisations..." autocomplete="off">
                </div>
                <span class="badge bg-light text-dark border px-2.5 py-1.5 rounded-pill fw-semibold" id="table-record-indicator" style="font-size: 0.76rem;">
                    Loading records...
                </span>
            </div>
            <div class="d-flex align-items-center gap-2">
                <button type="button" class="btn btn-sm btn-outline-secondary d-flex align-items-center gap-1.5 px-3 py-1.5 rounded-2 fw-semibold" id="btn-export-current" style="border-color: #cbd5e1; font-size: 0.82rem;">
                    <i class="fas fa-file-export text-secondary"></i>
                    <span>Export CSV</span>
                </button>
            </div>
        </div>

        <!-- Table Responsive Container -->
        <div class="table-responsive" id="table-container" style="max-height: 65vh; overflow-y: auto;">
            <table class="dir-table" id="directory-table">
                <thead id="directory-table-head">
                    <!-- Dynamic Headers Rendered by JS -->
                </thead>
                <tbody id="directory-table-body">
                    <!-- Rows Appended Dynamically by Infinite Scroll -->
                </tbody>
            </table>

            <!-- Infinite Scroll Bottom Loader -->
            <div id="scroll-loader" class="scroll-loader d-none">
                <div class="spinner-border spinner-border-sm text-primary me-1.5" role="status"></div>
                <span>Loading more records...</span>
            </div>

            <!-- All Records Loaded Marker -->
            <div id="scroll-end-marker" class="scroll-end-marker d-none">
                <i class="fas fa-check-circle text-success me-1"></i> All matching records loaded
            </div>

            <!-- Empty State -->
            <div id="scroll-empty-state" class="p-5 text-center d-none">
                <div class="text-muted mb-2"><i class="fas fa-folder-open fa-2x opacity-50"></i></div>
                <div class="fw-semibold text-dark">No records found</div>
                <div class="text-muted small">Try adjusting your filters or search term.</div>
            </div>
        </div>
    </div>

</div>

<!-- Quick View Drawer Modal -->
<div class="modal fade" id="quickViewModal" tabindex="-1" aria-labelledby="quickViewModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-scrollable">
        <div class="modal-content border-0 shadow-lg" style="border-radius: 16px; overflow: hidden;">
            <div class="modal-header bg-light py-3 px-4 border-bottom">
                <h6 class="modal-title fw-bold text-dark" id="quickViewModalLabel">
                    <i class="fas fa-eye me-2 text-primary"></i> Detail Inspection
                </h6>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body p-4" id="quickViewModalBody">
                <div class="text-center py-5">
                    <div class="spinner-border text-primary" role="status">
                        <span class="visually-hidden">Loading...</span>
                    </div>
                    <div class="text-muted small mt-2">Loading details...</div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('js')
<script>
(function($) {
    'use strict';

    // Application State
    let activeTab = 'organisations';
    let currentSearch = '';
    let searchTimeout = null;

    // Table Infinite Scroll Pagination State
    let state = {
        start: 0,
        length: 25,
        loading: false,
        hasMore: true,
        totalRecords: 0,
        filteredRecords: 0,
        loadedCount: 0
    };

    // Table Header Definitions
    const headers = {
        organisations: `
            <tr>
                <th style="width: 50px;">#</th>
                <th style="width: 75px;">Sort</th>
                <th>Organisation Name</th>
                <th>Central Authority / Location</th>
                <th>Hierarchy Summary</th>
                <th>Status</th>
                <th class="text-end" style="width: 130px;">Action</th>
            </tr>
        `,
        campuses: `
            <tr>
                <th style="width: 50px;">#</th>
                <th style="width: 75px;">Sort</th>
                <th>Campus Name</th>
                <th>Parent Organisation</th>
                <th>City & State</th>
                <th>Facilities</th>
                <th>Hierarchy</th>
                <th>Verification</th>
                <th class="text-end" style="width: 130px;">Action</th>
            </tr>
        `,
        departments: `
            <tr>
                <th style="width: 50px;">#</th>
                <th style="width: 75px;">Sort</th>
                <th>Department Name</th>
                <th>Organisation & Campus</th>
                <th>Discipline</th>
                <th>HOD & Contact</th>
                <th>Faculty / Labs</th>
                <th>Offered Programs</th>
                <th class="text-end" style="width: 130px;">Action</th>
            </tr>
        `,
        courses: `
            <tr>
                <th style="width: 50px;">#</th>
                <th style="width: 75px;">Sort</th>
                <th>Offered Program / Course</th>
                <th>Organisation, Campus & Dept</th>
                <th>Duration & Mode</th>
                <th>Total Fees</th>
                <th>Status</th>
                <th class="text-end" style="width: 110px;">Action</th>
            </tr>
        `
    };

    // Global Tab Selection Handler
    window.selectDirectoryTab = function(tabName) {
        if (activeTab === tabName) return;
        activeTab = tabName;

        // Update active class on metric cards
        $('.metric-card').removeClass('active-card');
        $('#card-tab-' + tabName).addClass('active-card');

        // Update search placeholder
        const placeholders = {
            organisations: 'Search organisations...',
            campuses: 'Search campuses...',
            departments: 'Search departments...',
            courses: 'Search offered courses...'
        };
        $('#dir-search-input').attr('placeholder', placeholders[tabName] || 'Search records...');

        // Render Table Headers
        $('#directory-table-head').html(headers[tabName]);

        // Reset and load table data from start
        resetAndLoadTable();
    };

    // Reset pagination state and fetch batch 0
    function resetAndLoadTable() {
        state.start = 0;
        state.loading = false;
        state.hasMore = true;
        state.loadedCount = 0;
        $('#directory-table-body').empty();
        $('#scroll-empty-state').addClass('d-none');
        $('#scroll-end-marker').addClass('d-none');
        loadNextBatch();
    }

    // Load next batch via AJAX (Infinite Scroll)
    function loadNextBatch() {
        if (state.loading || !state.hasMore) return;

        state.loading = true;
        $('#scroll-loader').removeClass('d-none');

        const endpointMap = {
            organisations: "{{ route('admin.academic-directory.data.organisations') }}",
            campuses: "{{ route('admin.academic-directory.data.campuses') }}",
            departments: "{{ route('admin.academic-directory.data.departments') }}",
            courses: "{{ route('admin.academic-directory.data.courses') }}"
        };

        const postData = {
            start: state.start,
            length: state.length,
            search: { value: currentSearch },
            organisation_type_id: $('#filter_organisation_type_id').val() || '',
            organisation_id: $('#filter_organisation_id').val() || '',
            campus_id: $('#filter_campus_id').val() || '',
            department_id: $('#filter_department_id').val() || '',
            course_id: $('#filter_course_id').val() || ''
        };

        $.ajax({
            url: endpointMap[activeTab],
            type: 'GET',
            data: postData,
            success: function(res) {
                state.loading = false;
                $('#scroll-loader').addClass('d-none');

                const data = res.data || [];
                state.totalRecords = res.recordsTotal || 0;
                state.filteredRecords = res.recordsFiltered || 0;
                state.loadedCount += data.length;

                // Render rows
                if (data.length > 0) {
                    let html = '';
                    data.forEach(function(row) {
                        html += renderTableRow(activeTab, row);
                    });
                    $('#directory-table-body').append(html);
                    $('#scroll-empty-state').addClass('d-none');
                } else if (state.start === 0) {
                    $('#scroll-empty-state').removeClass('d-none');
                }

                // Update Indicator
                $('#table-record-indicator').text(`Showing ${state.loadedCount} of ${state.filteredRecords} ${activeTab}`);

                // Check if more data exists
                if (state.loadedCount >= state.filteredRecords || data.length < state.length) {
                    state.hasMore = false;
                    if (state.loadedCount > 0) {
                        $('#scroll-end-marker').removeClass('d-none');
                    }
                } else {
                    state.start += state.length;
                    state.hasMore = true;
                }
            },
            error: function() {
                state.loading = false;
                $('#scroll-loader').addClass('d-none');
            }
        });
    }

    // Render individual row based on active tab
    function renderTableRow(tab, row) {
        if (tab === 'organisations') {
            return `
                <tr>
                    <td class="text-muted small fw-semibold">${row.DT_RowIndex || ''}</td>
                    <td>${row.sort_order_html || ''}</td>
                    <td>${row.name_html || ''}</td>
                    <td>${row.location_html || ''}</td>
                    <td>${row.hierarchy_chips || ''}</td>
                    <td>${row.status_html || ''}</td>
                    <td class="text-end">${row.action || ''}</td>
                </tr>
            `;
        } else if (tab === 'campuses') {
            return `
                <tr>
                    <td class="text-muted small fw-semibold">${row.DT_RowIndex || ''}</td>
                    <td>${row.sort_order_html || ''}</td>
                    <td>${row.campus_name_html || ''}</td>
                    <td>${row.organisation_html || ''}</td>
                    <td>${row.location_html || ''}</td>
                    <td>${row.facilities_badges || ''}</td>
                    <td>${row.hierarchy_chips || ''}</td>
                    <td>${row.verification_badge || ''}</td>
                    <td class="text-end">${row.action || ''}</td>
                </tr>
            `;
        } else if (tab === 'departments') {
            return `
                <tr>
                    <td class="text-muted small fw-semibold">${row.DT_RowIndex || ''}</td>
                    <td>${row.sort_order_html || ''}</td>
                    <td>${row.department_name_html || ''}</td>
                    <td>${row.hierarchy_html || ''}</td>
                    <td>${row.discipline_badge || ''}</td>
                    <td>${row.hod_info || ''}</td>
                    <td>${row.faculty_labs || ''}</td>
                    <td>${row.courses_count || ''}</td>
                    <td class="text-end">${row.action || ''}</td>
                </tr>
            `;
        } else if (tab === 'courses') {
            return `
                <tr>
                    <td class="text-muted small fw-semibold">${row.DT_RowIndex || ''}</td>
                    <td>${row.sort_order_html || ''}</td>
                    <td>${row.course_name_html || ''}</td>
                    <td>${row.hierarchy_html || ''}</td>
                    <td>${row.mode_duration || ''}</td>
                    <td>${row.fees_html || ''}</td>
                    <td>${row.status_html || ''}</td>
                    <td class="text-end">${row.action || ''}</td>
                </tr>
            `;
        }
        return '';
    }

    // Dynamic Filter Counts and Cascades AJAX
    function refreshFilterCountsAndCascades(triggeredSource) {
        const filters = {
            organisation_type_id: $('#filter_organisation_type_id').val() || '',
            organisation_id: $('#filter_organisation_id').val() || '',
            campus_id: $('#filter_campus_id').val() || '',
            department_id: $('#filter_department_id').val() || '',
            course_id: $('#filter_course_id').val() || ''
        };

        $.ajax({
            url: "{{ route('admin.academic-directory.filter-counts') }}",
            type: 'GET',
            data: filters,
            success: function(res) {
                if (res.status === 1) {
                    // Update 4 Metric Card Counts
                    if (res.counts) {
                        $('#count-orgs').text(Number(res.counts.organisations || 0).toLocaleString());
                        $('#count-campuses').text(Number(res.counts.campuses || 0).toLocaleString());
                        $('#count-depts').text(Number(res.counts.departments || 0).toLocaleString());
                        $('#count-courses').text(Number(res.counts.courses || 0).toLocaleString());
                    }

                    // Update Downstream Cascades if triggered by an upstream dropdown
                    if (res.cascades) {
                        if (triggeredSource === 'org_type' || triggeredSource === 'reset') {
                            // Update Organisations dropdown
                            const orgSelect = $('#filter_organisation_id');
                            const currentOrg = orgSelect.val();
                            orgSelect.empty().append('<option value="">All Organisations</option>');
                            if (res.cascades.organisations) {
                                res.cascades.organisations.forEach(o => {
                                    orgSelect.append(`<option value="${o.id}">${o.name}</option>`);
                                });
                            }
                            orgSelect.val(currentOrg).trigger('change.select2');
                        }

                        if (triggeredSource === 'org_type' || triggeredSource === 'org' || triggeredSource === 'reset') {
                            // Update Campuses dropdown
                            const campusSelect = $('#filter_campus_id');
                            const currentCampus = campusSelect.val();
                            campusSelect.empty().append('<option value="">All Campuses</option>');
                            if (res.cascades.campuses) {
                                res.cascades.campuses.forEach(c => {
                                    const loc = c.city ? ` (${c.city})` : '';
                                    campusSelect.append(`<option value="${c.id}">${c.campus_name}${loc}</option>`);
                                });
                            }
                            campusSelect.val(currentCampus).trigger('change.select2');
                        }

                        if (triggeredSource === 'org_type' || triggeredSource === 'org' || triggeredSource === 'campus' || triggeredSource === 'reset') {
                            // Update Departments dropdown
                            const deptSelect = $('#filter_department_id');
                            const currentDept = deptSelect.val();
                            deptSelect.empty().append('<option value="">All Departments</option>');
                            if (res.cascades.departments) {
                                res.cascades.departments.forEach(d => {
                                    const code = d.department_code ? ` [${d.department_code}]` : '';
                                    deptSelect.append(`<option value="${d.id}">${d.department_name}${code}</option>`);
                                });
                            }
                            deptSelect.val(currentDept).trigger('change.select2');
                        }

                        if (triggeredSource === 'org_type' || triggeredSource === 'org' || triggeredSource === 'campus' || triggeredSource === 'dept' || triggeredSource === 'reset') {
                            // Update Courses dropdown
                            const courseSelect = $('#filter_course_id');
                            const currentCourse = courseSelect.val();
                            courseSelect.empty().append('<option value="">All Courses</option>');
                            if (res.cascades.courses) {
                                res.cascades.courses.forEach(crs => {
                                    courseSelect.append(`<option value="${crs.id}">${crs.name}</option>`);
                                });
                            }
                            courseSelect.val(currentCourse).trigger('change.select2');
                        }
                    }
                }
            }
        });
    }

    $(document).ready(function() {
        // Initialize Select2 with full width
        $('.select2-filter').select2({
            width: '100%',
            dropdownAutoWidth: true
        });

        // Set default table header and load initial data
        $('#directory-table-head').html(headers[activeTab]);
        resetAndLoadTable();

        // Infinite Scroll Listener on #table-container
        $('#table-container').on('scroll', function() {
            const container = $(this);
            const scrollHeight = container.prop('scrollHeight');
            const scrollTop = container.scrollTop();
            const clientHeight = container.innerHeight();

            // When scrolled within 150px of bottom, trigger next batch
            if (scrollTop + clientHeight >= scrollHeight - 150) {
                loadNextBatch();
            }
        });

        // Live Search Input with 300ms Debounce
        $('#dir-search-input').on('keyup input', function() {
            clearTimeout(searchTimeout);
            const val = $(this).val();
            searchTimeout = setTimeout(function() {
                currentSearch = val;
                resetAndLoadTable();
            }, 300);
        });

        // Filter Change Events
        $('#filter_organisation_type_id').on('change', function() {
            refreshFilterCountsAndCascades('org_type');
            resetAndLoadTable();
        });

        $('#filter_organisation_id').on('change', function() {
            refreshFilterCountsAndCascades('org');
            resetAndLoadTable();
        });

        $('#filter_campus_id').on('change', function() {
            refreshFilterCountsAndCascades('campus');
            resetAndLoadTable();
        });

        $('#filter_department_id').on('change', function() {
            refreshFilterCountsAndCascades('dept');
            resetAndLoadTable();
        });

        $('#filter_course_id').on('change', function() {
            refreshFilterCountsAndCascades('course');
            resetAndLoadTable();
        });

        // Reset Filters Button
        $('#btn-reset-filters').on('click', function() {
            $('#filter_organisation_type_id').val('').trigger('change.select2');
            $('#filter_organisation_id').val('').trigger('change.select2');
            $('#filter_campus_id').val('').trigger('change.select2');
            $('#filter_department_id').val('').trigger('change.select2');
            $('#filter_course_id').val('').trigger('change.select2');
            $('#dir-search-input').val('');
            currentSearch = '';

            refreshFilterCountsAndCascades('reset');
            resetAndLoadTable();
        });

        // Hierarchy Chips Navigation from Table Rows
        $(document).on('click', '.switch-to-tab', function(e) {
            e.preventDefault();
            const targetTab = $(this).data('tab');
            const orgId = $(this).data('org-id');
            const campusId = $(this).data('campus-id');
            const deptId = $(this).data('dept-id');

            if (orgId) {
                $('#filter_organisation_id').val(orgId).trigger('change.select2');
            }
            if (campusId) {
                setTimeout(function() {
                    $('#filter_campus_id').val(campusId).trigger('change.select2');
                }, 300);
            }
            if (deptId) {
                setTimeout(function() {
                    $('#filter_department_id').val(deptId).trigger('change.select2');
                }, 600);
            }

            selectDirectoryTab(targetTab);
        });

        // Quick View Modal Drawer Trigger
        $(document).on('click', '.view-quick-drawer', function(e) {
            e.preventDefault();
            const type = $(this).data('type');
            const id = $(this).data('id');

            $('#quickViewModalBody').html(`
                <div class="text-center py-5">
                    <div class="spinner-border text-primary" role="status">
                        <span class="visually-hidden">Loading...</span>
                    </div>
                    <div class="text-muted small mt-2">Loading details...</div>
                </div>
            `);
            $('#quickViewModal').modal('show');

            const url = "{{ url('admin/academic-directory/quick-view') }}/" + type + "/" + id;
            $.get(url, function(html) {
                $('#quickViewModalBody').html(html);
            }).fail(function() {
                $('#quickViewModalBody').html(`
                    <div class="alert alert-danger mb-0">
                        <i class="fas fa-exclamation-triangle me-2"></i> Failed to load item details. Please try again.
                    </div>
                `);
            });
        });

        // Inline Sort Order Update Handler
        $(document).on('change', '.dir-sort-input', function() {
            const input = $(this);
            const type = input.data('type');
            const id = input.data('id');
            const sortOrder = parseInt(input.val()) || 1;

            input.prop('disabled', true).css('opacity', '0.6');

            $.ajax({
                url: "{{ route('admin.academic-directory.update-sort-order') }}",
                type: 'POST',
                data: {
                    _token: "{{ csrf_token() }}",
                    type: type,
                    id: id,
                    sort_order: sortOrder
                },
                success: function(res) {
                    input.prop('disabled', false).css('opacity', '1');
                    if (res.status === 1) {
                        input.css({
                            'border-color': '#10b981',
                            'box-shadow': '0 0 0 2px rgba(16, 185, 129, 0.25)'
                        });
                        setTimeout(() => {
                            input.css({
                                'border-color': '',
                                'box-shadow': ''
                            });
                        }, 1200);
                    }
                },
                error: function() {
                    input.prop('disabled', false).css('opacity', '1');
                    input.css({
                        'border-color': '#ef4444',
                        'box-shadow': '0 0 0 2px rgba(239, 68, 68, 0.25)'
                    });
                }
            });
        });

        // Export CSV Handler
        $('#btn-export-current').on('click', function(e) {
            e.preventDefault();
            const orgTypeId = $('#filter_organisation_type_id').val() || '';
            const orgId = $('#filter_organisation_id').val() || '';
            const campusId = $('#filter_campus_id').val() || '';
            const deptId = $('#filter_department_id').val() || '';
            const courseId = $('#filter_course_id').val() || '';

            const exportUrl = "{{ route('admin.academic-directory.export') }}?tab=" + activeTab +
                "&organisation_type_id=" + encodeURIComponent(orgTypeId) +
                "&organisation_id=" + encodeURIComponent(orgId) +
                "&campus_id=" + encodeURIComponent(campusId) +
                "&department_id=" + encodeURIComponent(deptId) +
                "&course_id=" + encodeURIComponent(courseId);

            window.location.href = exportUrl;
        });

    });

})(jQuery);
</script>
@endpush

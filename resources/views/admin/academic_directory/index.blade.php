@extends('admin.layouts.master')

@section('title', 'Academic Master Directory & Reports')

@push('css')
<link rel="stylesheet" href="https://cdn.datatables.net/1.13.7/css/dataTables.bootstrap5.min.css">
<style>
    /* ==========================================================================
       PRO EXECUTIVE COCKPIT DESIGN SYSTEM (20+ Years UI/UX Grade)
       ========================================================================== */
    
    :root {
        --primary-indigo: #4f46e5;
        --primary-hover: #4338ca;
        --accent-cyan: #0284c7;
        --accent-emerald: #059669;
        --accent-amber: #d97706;
        --surface-card: #ffffff;
        --surface-ground: #f8fafc;
        --border-subtle: #e2e8f0;
        --text-headline: #0f172a;
        --text-body: #334155;
        --text-muted: #64748b;
    }

    body {
        background-color: #f8fafc !important;
    }

    /* Cockpit Header Banner */
    .cockpit-header {
        background: #ffffff;
        border-radius: 16px;
        padding: 22px 28px;
        border: 1px solid var(--border-subtle);
        box-shadow: 0 1px 3px rgba(0,0,0,0.03), 0 4px 12px rgba(0,0,0,0.02);
    }
    
    /* Top KPI Metric Cards */
    .kpi-deck {
        display: grid;
        grid-template-columns: repeat(4, 1fr);
        gap: 18px;
    }
    @media (max-width: 1200px) {
        .kpi-deck { grid-template-columns: repeat(2, 1fr); }
    }
    @media (max-width: 768px) {
        .kpi-deck { grid-template-columns: 1fr; }
    }

    .kpi-card {
        background: #ffffff;
        border-radius: 16px;
        padding: 22px 24px;
        border: 1px solid var(--border-subtle);
        box-shadow: 0 1px 3px rgba(0,0,0,0.02), 0 4px 12px rgba(0,0,0,0.02);
        transition: all 0.25s cubic-bezier(0.4, 0, 0.2, 1);
        cursor: pointer;
        position: relative;
        display: flex;
        flex-direction: column;
        justify-content: space-between;
    }
    .kpi-card::before {
        content: '';
        position: absolute;
        top: 0;
        left: 0;
        right: 0;
        height: 4px;
        border-radius: 16px 16px 0 0;
    }
    .kpi-card.kpi-orgs::before { background: linear-gradient(90deg, #4f46e5, #6366f1); }
    .kpi-card.kpi-campuses::before { background: linear-gradient(90deg, #0284c7, #38bdf8); }
    .kpi-card.kpi-depts::before { background: linear-gradient(90deg, #7c3aed, #a855f7); }
    .kpi-card.kpi-courses::before { background: linear-gradient(90deg, #059669, #34d399); }
    
    .kpi-card:hover {
        transform: translateY(-3px);
        box-shadow: 0 12px 24px -6px rgba(15, 23, 42, 0.08);
        border-color: #cbd5e1;
    }
    .kpi-card.active-kpi {
        border-color: var(--primary-indigo);
        background: #fcfdff;
        box-shadow: 0 0 0 2px rgba(79, 70, 229, 0.15), 0 8px 20px -4px rgba(79, 70, 229, 0.1);
    }
    
    .kpi-label {
        font-size: 0.76rem;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: 0.6px;
        color: var(--text-muted);
        margin-bottom: 8px;
    }
    .kpi-value {
        font-size: 2.25rem;
        font-weight: 800;
        line-height: 1.1;
        letter-spacing: -0.03em;
        color: var(--text-headline);
        margin-bottom: 12px;
    }
    .kpi-icon-wrap {
        width: 46px;
        height: 46px;
        border-radius: 12px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1.25rem;
        flex-shrink: 0;
    }

    /* Universal Filter Hub Card */
    .filter-hub-card {
        background: #ffffff;
        border-radius: 16px;
        border: 1px solid var(--border-subtle);
        padding: 22px 26px;
        box-shadow: 0 1px 3px rgba(0,0,0,0.02), 0 4px 12px rgba(0,0,0,0.02);
    }
    .filter-label {
        font-size: 0.74rem;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: 0.5px;
        color: var(--text-muted);
        margin-bottom: 6px;
        display: block;
    }

    /* Custom Select2 Styling */
    .select2-container--default .select2-selection--single {
        height: 42px !important;
        border: 1px solid #cbd5e1 !important;
        border-radius: 10px !important;
        padding: 6px 12px !important;
        background-color: #ffffff !important;
        transition: all 0.2s ease;
    }
    .select2-container--default .select2-selection--single .select2-selection__rendered {
        line-height: 28px !important;
        font-size: 0.88rem !important;
        color: #1e293b !important;
        font-weight: 500 !important;
        padding-left: 0 !important;
    }
    .select2-container--default .select2-selection--single .select2-selection__arrow {
        height: 40px !important;
        right: 8px !important;
    }
    .select2-container--default.select2-container--focus .select2-selection--single,
    .select2-container--default.select2-container--open .select2-selection--single {
        border-color: var(--primary-indigo) !important;
        box-shadow: 0 0 0 3px rgba(79, 70, 229, 0.12) !important;
    }
    .select2-dropdown {
        border-radius: 12px !important;
        border: 1px solid #cbd5e1 !important;
        box-shadow: 0 10px 25px rgba(0,0,0,0.1) !important;
        overflow: hidden !important;
    }
    .select2-results__option {
        padding: 9px 14px !important;
        font-size: 0.88rem !important;
    }
    .select2-results__option--highlighted[aria-selected] {
        background-color: var(--primary-indigo) !important;
    }

    .form-select-custom {
        height: 42px;
        border: 1px solid #cbd5e1;
        border-radius: 10px;
        font-size: 0.88rem;
        font-weight: 500;
        color: #1e293b;
        padding: 6px 12px;
        background-color: #ffffff;
        transition: all 0.2s ease;
    }
    .form-select-custom:focus {
        border-color: var(--primary-indigo);
        box-shadow: 0 0 0 3px rgba(79, 70, 229, 0.12);
    }

    /* Segmented Navigation Control Bar (Linear / Apple Style) */
    .segmented-tabs-wrapper {
        background: #f1f5f9;
        padding: 5px;
        border-radius: 14px;
        border: 1px solid #e2e8f0;
        display: inline-flex;
        flex-wrap: wrap;
        gap: 4px;
    }
    .segmented-tabs-wrapper .nav-link {
        color: #64748b;
        font-weight: 600;
        font-size: 0.88rem;
        padding: 10px 18px;
        border-radius: 10px;
        transition: all 0.2s cubic-bezier(0.4, 0, 0.2, 1);
        border: none;
        background: transparent;
        display: inline-flex;
        align-items: center;
        gap: 8px;
    }
    .segmented-tabs-wrapper .nav-link:hover {
        color: #0f172a;
        background: rgba(255, 255, 255, 0.6);
    }
    .segmented-tabs-wrapper .nav-link.active {
        color: #0f172a;
        background: #ffffff;
        box-shadow: 0 2px 8px rgba(0, 0, 0, 0.08);
        font-weight: 700;
    }
    .segmented-tabs-wrapper .nav-link .pill-counter {
        font-size: 0.72rem;
        padding: 2px 8px;
        border-radius: 12px;
        background: #e2e8f0;
        color: #475569;
        font-weight: 700;
        transition: all 0.2s ease;
    }
    .segmented-tabs-wrapper .nav-link.active .pill-counter {
        background: #eef2ff;
        color: var(--primary-indigo);
    }

    /* Table Surface Card */
    .table-surface-card {
        background: #ffffff;
        border-radius: 16px;
        border: 1px solid var(--border-subtle);
        box-shadow: 0 1px 3px rgba(0,0,0,0.02), 0 4px 12px rgba(0,0,0,0.02);
        overflow: hidden;
    }
    table.dataTable {
        border-collapse: separate !important;
        border-spacing: 0 !important;
        margin-top: 0 !important;
        margin-bottom: 0 !important;
    }
    table.dataTable thead th {
        background: #f8fafc;
        color: #475569;
        font-size: 0.72rem;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: 0.6px;
        border-top: none;
        border-bottom: 1px solid #e2e8f0 !important;
        padding: 14px 18px !important;
        white-space: nowrap;
    }
    table.dataTable tbody td {
        padding: 14px 18px !important;
        vertical-align: middle;
        font-size: 0.88rem;
        border-bottom: 1px solid #f1f5f9 !important;
    }
    table.dataTable tbody tr:hover td {
        background-color: #f8faff !important;
    }

    .dataTables_wrapper .dataTables_length select {
        border-radius: 8px;
        border: 1px solid #cbd5e1;
        padding: 4px 10px;
        font-size: 0.84rem;
    }
    .dataTables_wrapper .dataTables_filter input {
        border-radius: 8px;
        border: 1px solid #cbd5e1;
        padding: 6px 14px;
        font-size: 0.86rem;
        min-width: 220px;
    }
    .dataTables_wrapper .dataTables_filter input:focus {
        border-color: var(--primary-indigo);
        box-shadow: 0 0 0 3px rgba(79, 70, 229, 0.12);
        outline: none;
    }

    /* Component Badges, Avatars & Chips */
    .org-avatar-box {
        width: 40px;
        height: 40px;
        min-width: 40px;
        border-radius: 10px;
        display: flex;
        align-items: center;
        justify-content: center;
        color: #ffffff;
        font-weight: 700;
        font-size: 0.95rem;
        box-shadow: 0 2px 6px rgba(0,0,0,0.12);
        flex-shrink: 0;
    }
    .entity-title {
        font-size: 0.92rem;
        font-weight: 700;
        color: #0f172a;
        text-decoration: none;
        transition: color 0.15s ease;
    }
    .entity-title:hover {
        color: var(--primary-indigo);
    }
    .code-pill {
        background: #f1f5f9;
        color: #64748b;
        font-family: monospace;
        font-size: 0.72rem;
        font-weight: 600;
        padding: 2px 7px;
        border-radius: 6px;
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
        padding: 2px 8px;
        border-radius: 6px;
    }
    .badge-pill-neutral {
        background: #f8fafc;
        color: #475569;
        border: 1px solid #e2e8f0;
        font-size: 0.72rem;
        font-weight: 600;
        padding: 2px 8px;
        border-radius: 6px;
    }
    
    /* Interactive Stat Chips */
    .stat-chip {
        display: inline-flex;
        align-items: center;
        font-size: 0.74rem;
        font-weight: 600;
        padding: 4px 10px;
        border-radius: 20px;
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
        transform: translateY(-1px);
        box-shadow: 0 2px 6px rgba(2,132,199,0.25);
    }
    .chip-indigo {
        background: #eef2ff;
        color: #4f46e5;
        border: 1px solid #c7d2fe;
    }
    .chip-indigo:hover {
        background: #4f46e5;
        color: #ffffff;
        transform: translateY(-1px);
        box-shadow: 0 2px 6px rgba(79,70,229,0.25);
    }
    .chip-emerald {
        background: #ecfdf5;
        color: #059669;
        border: 1px solid #a7f3d0;
    }
    .chip-emerald:hover {
        background: #059669;
        color: #ffffff;
        transform: translateY(-1px);
        box-shadow: 0 2px 6px rgba(5,150,105,0.25);
    }

    /* Glowing Status Pills */
    .status-pill {
        display: inline-flex;
        align-items: center;
        gap: 5px;
        font-size: 0.74rem;
        font-weight: 600;
        padding: 4px 10px;
        border-radius: 20px;
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
        width: 32px;
        height: 32px;
        border-radius: 8px;
        border: 1px solid #e2e8f0;
        background: #ffffff;
        color: #64748b;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        transition: all 0.15s ease;
        font-size: 0.8rem;
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

    /* Active Filter Banner */
    .active-filter-banner {
        background: linear-gradient(135deg, #eff6ff, #dbeafe);
        border: 1px solid #bfdbfe;
        border-radius: 12px;
        padding: 12px 20px;
    }
</style>
@endpush

@section('content')
<div class="container-fluid py-3">

    <!-- 1. Cockpit Header Zone -->
    <div class="cockpit-header d-flex flex-wrap justify-content-between align-items-center gap-3 mb-4">
        <div>
            <div class="d-flex align-items-center gap-2 mb-1.5">
                <span class="badge bg-primary bg-opacity-10 text-primary fw-bold px-2.5 py-1 rounded-pill" style="font-size: 0.75rem;">
                    <i class="fas fa-sitemap me-1"></i> ACADEMIC MASTER DIRECTORY
                </span>
                <span class="text-muted small">&bull; Enterprise Hierarchy Explorer & Analytics</span>
            </div>
            <h2 class="fw-bold text-dark mb-0" style="letter-spacing: -0.02em;">
                Directory Cockpit & Reports
            </h2>
            <p class="text-muted small mb-0 mt-1">
                Unified cross-hierarchy management across all 159 Organisations, 123 Campuses, 50 Departments & 439 Offered Courses.
            </p>
        </div>
        <div class="d-flex align-items-center gap-2.5 flex-wrap">
            <!-- Export CSV Dropdown -->
            <div class="dropdown">
                <button class="btn btn-outline-secondary dropdown-toggle d-flex align-items-center gap-2 px-3.5 py-2 rounded-3 fw-semibold" type="button" data-bs-toggle="dropdown" aria-expanded="false" style="border-color: #cbd5e1; font-size: 0.88rem;">
                    <i class="fas fa-file-export text-secondary"></i>
                    <span>Export CSV</span>
                </button>
                <ul class="dropdown-menu dropdown-menu-end shadow-sm border-0 rounded-3 p-2">
                    <li><h6 class="dropdown-header text-uppercase small fw-bold text-muted">Direct CSV Downloads</h6></li>
                    <li><a class="dropdown-item py-2 rounded-2 export-trigger" href="javascript:void(0)" data-export-tab="organisations"><i class="fas fa-university text-primary me-2"></i>Organisations CSV</a></li>
                    <li><a class="dropdown-item py-2 rounded-2 export-trigger" href="javascript:void(0)" data-export-tab="campuses"><i class="fas fa-city text-info me-2"></i>Campuses CSV</a></li>
                    <li><a class="dropdown-item py-2 rounded-2 export-trigger" href="javascript:void(0)" data-export-tab="departments"><i class="fas fa-building text-warning me-2"></i>Departments CSV</a></li>
                    <li><a class="dropdown-item py-2 rounded-2 export-trigger" href="javascript:void(0)" data-export-tab="courses"><i class="fas fa-graduation-cap text-success me-2"></i>Offered Courses CSV</a></li>
                </ul>
            </div>

            <!-- Add New Dropdown -->
            <div class="dropdown">
                <button class="btn btn-primary dropdown-toggle d-flex align-items-center gap-2 px-4 py-2 rounded-3 fw-semibold shadow-sm" type="button" data-bs-toggle="dropdown" aria-expanded="false" style="background: var(--primary-indigo); border-color: var(--primary-indigo); font-size: 0.88rem;">
                    <i class="fas fa-plus-circle"></i>
                    <span>Create New</span>
                </button>
                <ul class="dropdown-menu dropdown-menu-end shadow-sm border-0 rounded-3 p-2">
                    <li><a class="dropdown-item py-2 rounded-2 fw-medium" href="{{ route('admin.organisations.create') }}"><i class="fas fa-university text-primary me-2"></i>New Organisation</a></li>
                    <li><hr class="dropdown-divider my-1"></li>
                    <li><a class="dropdown-item py-2 rounded-2 fw-medium" href="{{ route('admin.departments.create') }}"><i class="fas fa-building text-info me-2"></i>New Department</a></li>
                    <li><a class="dropdown-item py-2 rounded-2 fw-medium" href="{{ route('admin.organisation-courses.create') }}"><i class="fas fa-graduation-cap text-success me-2"></i>New Offered Course</a></li>
                </ul>
            </div>
        </div>
    </div>

    <!-- 2. Top Executive KPI Metric Deck -->
    <div class="kpi-deck mb-4">
        <!-- KPI 1: Organisations -->
        <div class="kpi-card kpi-orgs active-kpi" id="kpi-card-organisations" onclick="switchMainTab('organisations')">
            <div class="d-flex justify-content-between align-items-start">
                <div>
                    <div class="kpi-label">Total Organisations</div>
                    <div class="kpi-value">{{ number_format($totalOrgs) }}</div>
                </div>
                <div class="kpi-icon-wrap bg-primary bg-opacity-10 text-primary">
                    <i class="fas fa-university"></i>
                </div>
            </div>
            <div class="d-flex align-items-center gap-1.5 pt-1">
                <span class="status-pill status-active"><span class="status-dot"></span>{{ $publishedOrgs }} Published</span>
                <span class="status-pill status-draft"><span class="status-dot"></span>{{ $draftOrgs }} Draft</span>
            </div>
        </div>

        <!-- KPI 2: Campuses -->
        <div class="kpi-card kpi-campuses" id="kpi-card-campuses" onclick="switchMainTab('campuses')">
            <div class="d-flex justify-content-between align-items-start">
                <div>
                    <div class="kpi-label">Total Campuses</div>
                    <div class="kpi-value">{{ number_format($totalCampuses) }}</div>
                </div>
                <div class="kpi-icon-wrap bg-info bg-opacity-10 text-info">
                    <i class="fas fa-city"></i>
                </div>
            </div>
            <div class="d-flex align-items-center gap-1.5 pt-1">
                <span class="status-pill status-active"><i class="fas fa-check-circle me-1"></i>{{ $verifiedCampuses }} Verified</span>
                <span class="text-muted small" style="font-size: 0.75rem;">across all orgs</span>
            </div>
        </div>

        <!-- KPI 3: Departments -->
        <div class="kpi-card kpi-depts" id="kpi-card-departments" onclick="switchMainTab('departments')">
            <div class="d-flex justify-content-between align-items-start">
                <div>
                    <div class="kpi-label">Academic Departments</div>
                    <div class="kpi-value">{{ number_format($totalDepartments) }}</div>
                </div>
                <div class="kpi-icon-wrap bg-purple bg-opacity-10 text-purple" style="color: #7c3aed; background: #f3e8ff;">
                    <i class="fas fa-building"></i>
                </div>
            </div>
            <div class="d-flex align-items-center gap-1.5 pt-1">
                <span class="badge-pill-indigo">Specialized Faculties</span>
                <span class="text-muted small" style="font-size: 0.75rem;">Multiple streams</span>
            </div>
        </div>

        <!-- KPI 4: Courses -->
        <div class="kpi-card kpi-courses" id="kpi-card-courses" onclick="switchMainTab('courses')">
            <div class="d-flex justify-content-between align-items-start">
                <div>
                    <div class="kpi-label">Offered Programs</div>
                    <div class="kpi-value">{{ number_format($totalCourses) }}</div>
                </div>
                <div class="kpi-icon-wrap bg-success bg-opacity-10 text-success">
                    <i class="fas fa-graduation-cap"></i>
                </div>
            </div>
            <div class="d-flex align-items-center gap-1.5 pt-1">
                <span class="status-pill status-active"><i class="fas fa-receipt me-1"></i>With Fee Details</span>
                <span class="text-muted small" style="font-size: 0.75rem;">Degrees & Diplomas</span>
            </div>
        </div>
    </div>

    <!-- Active Filter Indicator Banner -->
    <div id="active-filter-indicator" class="active-filter-banner mb-3 d-none align-items-center justify-content-between">
        <div class="d-flex align-items-center gap-2">
            <i class="fas fa-filter text-primary fs-6"></i>
            <span class="fw-semibold text-dark">Filtered by Institution:</span>
            <span class="badge bg-primary text-white fs-7 px-3 py-1.5 rounded-pill" id="active-filter-org-name">—</span>
            <span class="text-muted small">(All tabs are synchronized to this selection)</span>
        </div>
        <div>
            <button type="button" class="btn btn-sm btn-outline-primary bg-white rounded-pill px-3 fw-semibold shadow-sm" id="btn-clear-hierarchy-filter">
                <i class="fas fa-times me-1"></i> Clear Filter & View All Globally
            </button>
        </div>
    </div>

    <!-- 3. Universal Cascading Filter Hub -->
    <div class="filter-hub-card mb-4">
        <div class="d-flex justify-content-between align-items-center pb-3 mb-3 border-bottom">
            <div class="d-flex align-items-center gap-2">
                <i class="fas fa-sliders-h text-primary"></i>
                <span class="fw-bold text-dark small text-uppercase" style="letter-spacing: 0.8px;">Universal Hierarchy Filter & Search Hub</span>
            </div>
            <div>
                <button type="button" class="btn btn-sm btn-light border px-3 rounded-3 text-muted fw-semibold" id="btn-reset-filters">
                    <i class="fas fa-undo-alt me-1"></i> Reset All Filters
                </button>
            </div>
        </div>

        <div class="row g-3">
            <!-- 1. Organisation Type -->
            <div class="col-lg-3 col-md-6">
                <label class="filter-label">Organisation Type</label>
                <select id="filter_organisation_type_id" class="form-select-custom w-100 select2-filter">
                    <option value="">All Org Types</option>
                    @foreach($organisationTypes as $type)
                        <option value="{{ $type->id }}">{{ $type->title }}</option>
                    @endforeach
                </select>
            </div>

            <!-- 2. Organisation Dropdown -->
            <div class="col-lg-3 col-md-6">
                <label class="filter-label">Organisation / University</label>
                <select id="filter_organisation_id" class="form-select-custom w-100 select2-filter">
                    <option value="">All Organisations (Global)</option>
                    @foreach($organisationsList as $org)
                        <option value="{{ $org->id }}">{{ $org->name }}</option>
                    @endforeach
                </select>
            </div>

            <!-- 3. Campus Location Dropdown -->
            <div class="col-lg-3 col-md-6">
                <label class="filter-label">Campus Location</label>
                <select id="filter_campus_id" class="form-select-custom w-100 select2-filter">
                    <option value="">All Campuses (Global)</option>
                </select>
            </div>

            <!-- 4. Academic Department Dropdown -->
            <div class="col-lg-3 col-md-6">
                <label class="filter-label">Academic Department</label>
                <select id="filter_department_id" class="form-select-custom w-100 select2-filter">
                    <option value="">All Departments (Global)</option>
                </select>
            </div>

            <!-- 5. Status Filter -->
            <div class="col-lg-3 col-md-6">
                <label class="filter-label">Publishing Status</label>
                <select id="filter_status" class="form-select-custom w-100">
                    <option value="">All Statuses</option>
                    <option value="published">Published</option>
                    <option value="draft">Draft</option>
                    <option value="top">Top Ranked Only</option>
                </select>
            </div>

            <!-- 6. Discipline Filter -->
            <div class="col-lg-3 col-md-6">
                <label class="filter-label">Discipline / Subject Area</label>
                <select id="filter_discipline_area" class="form-select-custom w-100">
                    <option value="">All Disciplines</option>
                    @foreach($disciplinesList as $disc)
                        <option value="{{ $disc }}">{{ $disc }}</option>
                    @endforeach
                </select>
            </div>
        </div>
    </div>

    <!-- 4. Segmented Control Navigation Tabs (Linear / Apple Style) -->
    <div class="d-flex justify-content-between align-items-center flex-wrap gap-2 mb-3">
        <div class="segmented-tabs-wrapper" id="cockpitTab" role="tablist">
            <button class="nav-link active" id="tab-btn-organisations" data-bs-toggle="pill" data-bs-target="#tab-pane-organisations" type="button" role="tab">
                <i class="fas fa-university text-primary"></i>
                <span>Organisations</span>
                <span class="pill-counter">{{ $totalOrgs }}</span>
            </button>
            <button class="nav-link" id="tab-btn-campuses" data-bs-toggle="pill" data-bs-target="#tab-pane-campuses" type="button" role="tab">
                <i class="fas fa-city text-info"></i>
                <span>Campuses</span>
                <span class="pill-counter">{{ $totalCampuses }}</span>
            </button>
            <button class="nav-link" id="tab-btn-departments" data-bs-toggle="pill" data-bs-target="#tab-pane-departments" type="button" role="tab">
                <i class="fas fa-building text-purple" style="color: #7c3aed;"></i>
                <span>Departments</span>
                <span class="pill-counter">{{ $totalDepartments }}</span>
            </button>
            <button class="nav-link" id="tab-btn-courses" data-bs-toggle="pill" data-bs-target="#tab-pane-courses" type="button" role="tab">
                <i class="fas fa-graduation-cap text-success"></i>
                <span>Offered Courses</span>
                <span class="pill-counter">{{ $totalCourses }}</span>
            </button>
            <button class="nav-link" id="tab-btn-analytics" data-bs-toggle="pill" data-bs-target="#tab-pane-analytics" type="button" role="tab">
                <i class="fas fa-chart-pie text-warning"></i>
                <span>Analytics & Reports</span>
            </button>
        </div>
    </div>

    <!-- 5. Tab Panes & Tables -->
    <div class="tab-content" id="cockpitTabContent">
        
        <!-- Tab 1: Organisations -->
        <div class="tab-pane fade show active" id="tab-pane-organisations" role="tabpanel">
            <div class="table-surface-card p-3.5">
                <div class="table-responsive">
                    <table class="table table-hover align-middle w-100" id="table-organisations">
                        <thead>
                            <tr>
                                <th style="width: 50px;">#</th>
                                <th>Organisation Name</th>
                                <th>Central Authority / Location</th>
                                <th>Hierarchy Summary</th>
                                <th>Status</th>
                                <th class="text-end" style="width: 140px;">Action</th>
                            </tr>
                        </thead>
                        <tbody></tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- Tab 2: Campuses -->
        <div class="tab-pane fade" id="tab-pane-campuses" role="tabpanel">
            <div class="table-surface-card p-3.5">
                <div class="table-responsive">
                    <table class="table table-hover align-middle w-100" id="table-campuses">
                        <thead>
                            <tr>
                                <th style="width: 50px;">#</th>
                                <th>Campus Name</th>
                                <th>Parent Organisation</th>
                                <th>City & State</th>
                                <th>Facilities</th>
                                <th>Hierarchy</th>
                                <th>Verification</th>
                                <th class="text-end" style="width: 140px;">Action</th>
                            </tr>
                        </thead>
                        <tbody></tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- Tab 3: Departments -->
        <div class="tab-pane fade" id="tab-pane-departments" role="tabpanel">
            <div class="table-surface-card p-3.5">
                <div class="table-responsive">
                    <table class="table table-hover align-middle w-100" id="table-departments">
                        <thead>
                            <tr>
                                <th style="width: 50px;">#</th>
                                <th>Department Name</th>
                                <th>Organisation & Campus</th>
                                <th>Discipline</th>
                                <th>HOD & Contact</th>
                                <th>Faculty / Labs</th>
                                <th>Offered Programs</th>
                                <th class="text-end" style="width: 140px;">Action</th>
                            </tr>
                        </thead>
                        <tbody></tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- Tab 4: Courses -->
        <div class="tab-pane fade" id="tab-pane-courses" role="tabpanel">
            <div class="table-surface-card p-3.5">
                <div class="table-responsive">
                    <table class="table table-hover align-middle w-100" id="table-courses">
                        <thead>
                            <tr>
                                <th style="width: 50px;">#</th>
                                <th>Offered Program / Course</th>
                                <th>Organisation, Campus & Dept</th>
                                <th>Duration & Mode</th>
                                <th>Total / Annual Fees</th>
                                <th>Status</th>
                                <th class="text-end" style="width: 120px;">Action</th>
                            </tr>
                        </thead>
                        <tbody></tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- Tab 5: Reports & Analytics -->
        <div class="tab-pane fade" id="tab-pane-analytics" role="tabpanel">
            <div class="row g-3">
                <!-- Org Types Distribution -->
                <div class="col-lg-6">
                    <div class="card h-100 border-0 shadow-sm rounded-4">
                        <div class="card-header bg-white py-3 border-bottom d-flex justify-content-between align-items-center">
                            <h6 class="fw-bold text-dark mb-0"><i class="fas fa-chart-pie me-2 text-primary"></i> Organisation Type Breakdown</h6>
                            <span class="badge-pill-neutral">{{ count($orgTypeBreakdown) }} Types</span>
                        </div>
                        <div class="card-body p-4">
                            <ul class="list-group list-group-flush">
                                @foreach($orgTypeBreakdown as $ot)
                                    @php
                                        $percent = $totalOrgs > 0 ? round(($ot->total / $totalOrgs) * 100, 1) : 0;
                                    @endphp
                                    <li class="list-group-item px-0 py-3 border-light">
                                        <div class="d-flex justify-content-between align-items-center mb-1.5">
                                            <span class="fw-semibold text-dark">{{ $ot->title }}</span>
                                            <span class="fw-bold text-primary">{{ $ot->total }} <span class="text-muted small fw-normal">({{ $percent }}%)</span></span>
                                        </div>
                                        <div class="progress" style="height: 6px; border-radius: 4px; background: #e2e8f0;">
                                            <div class="progress-bar" role="progressbar" style="width: {{ $percent }}%; background: var(--primary-indigo);"></div>
                                        </div>
                                    </li>
                                @endforeach
                            </ul>
                        </div>
                    </div>
                </div>

                <!-- Top Campus Locations -->
                <div class="col-lg-6">
                    <div class="card h-100 border-0 shadow-sm rounded-4">
                        <div class="card-header bg-white py-3 border-bottom d-flex justify-content-between align-items-center">
                            <h6 class="fw-bold text-dark mb-0"><i class="fas fa-map-marked-alt me-2 text-info"></i> Top Campus Hubs by State</h6>
                            <span class="badge-pill-neutral">Geographic Spread</span>
                        </div>
                        <div class="card-body p-4">
                            <ul class="list-group list-group-flush">
                                @foreach($stateCampuses as $st)
                                    @php
                                        $percent = $totalCampuses > 0 ? round(($st->total / $totalCampuses) * 100, 1) : 0;
                                    @endphp
                                    <li class="list-group-item px-0 py-3 border-light">
                                        <div class="d-flex justify-content-between align-items-center mb-1.5">
                                            <span class="fw-semibold text-dark"><i class="fas fa-map-marker-alt text-danger me-1.5 opacity-75"></i>{{ $st->state }}</span>
                                            <span class="fw-bold text-info">{{ $st->total }} <span class="text-muted small fw-normal">Campuses ({{ $percent }}%)</span></span>
                                        </div>
                                        <div class="progress" style="height: 6px; border-radius: 4px; background: #e2e8f0;">
                                            <div class="progress-bar bg-info" role="progressbar" style="width: {{ $percent }}%"></div>
                                        </div>
                                    </li>
                                @endforeach
                            </ul>
                        </div>
                    </div>
                </div>

                <!-- 1-Click Quick Exports Suite -->
                <div class="col-12">
                    <div class="card border-0 shadow-sm rounded-4" style="background: linear-gradient(135deg, #0f172a, #1e293b); color: #fff;">
                        <div class="card-body p-4">
                            <div class="row align-items-center">
                                <div class="col-lg-7">
                                    <h5 class="fw-bold text-white mb-1"><i class="fas fa-cloud-download-alt me-2 text-warning"></i> Academic Master Data Export Suite</h5>
                                    <p class="text-white-50 small mb-0">Generate comprehensive CSV spreadsheets directly streamed from the database for reporting, auditing, or backup purposes.</p>
                                </div>
                                <div class="col-lg-5 text-lg-end mt-3 mt-lg-0 d-flex flex-wrap gap-2 justify-content-lg-end">
                                    <a href="{{ route('admin.academic-directory.export', ['tab' => 'organisations']) }}" class="btn btn-sm btn-outline-light rounded-pill px-3 py-1.5 fw-semibold">
                                        <i class="fas fa-download me-1"></i> All Orgs
                                    </a>
                                    <a href="{{ route('admin.academic-directory.export', ['tab' => 'campuses']) }}" class="btn btn-sm btn-outline-light rounded-pill px-3 py-1.5 fw-semibold">
                                        <i class="fas fa-download me-1"></i> All Campuses
                                    </a>
                                    <a href="{{ route('admin.academic-directory.export', ['tab' => 'departments']) }}" class="btn btn-sm btn-outline-light rounded-pill px-3 py-1.5 fw-semibold">
                                        <i class="fas fa-download me-1"></i> All Depts
                                    </a>
                                    <a href="{{ route('admin.academic-directory.export', ['tab' => 'courses']) }}" class="btn btn-sm btn-outline-light rounded-pill px-3 py-1.5 fw-semibold">
                                        <i class="fas fa-download me-1"></i> All Courses
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

            </div>
        </div>

    </div>

</div>

<!-- Quick View Modal Drawer -->
<div class="modal fade" id="quickViewModal" tabindex="-1" aria-labelledby="quickViewModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-scrollable">
        <div class="modal-content border-0 shadow-lg" style="border-radius: 18px; overflow: hidden;">
            <div class="modal-header bg-light py-3 px-4 border-bottom">
                <h6 class="modal-title fw-bold text-dark" id="quickViewModalLabel">
                    <i class="fas fa-eye me-2 text-primary"></i> Quick Detail Inspection
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
<script src="https://cdn.datatables.net/1.13.7/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.13.7/js/dataTables.bootstrap5.min.js"></script>
<script>
    let tableOrgs = null;
    let tableCampuses = null;
    let tableDepts = null;
    let tableCourses = null;
    let currentTab = 'organisations';

    function switchMainTab(tabName) {
        currentTab = tabName;
        $('#cockpitTab button[data-bs-target="#tab-pane-' + tabName + '"]').tab('show');
    }

    $(document).ready(function() {

        // Setup Select2 for Filter Hub with 100% width
        $('.select2-filter').select2({
            width: '100%',
            dropdownAutoWidth: true
        });

        // Initialize Organisations DataTable
        function initOrganisationsTable() {
            if (tableOrgs) return;
            tableOrgs = $('#table-organisations').DataTable({
                processing: true,
                serverSide: true,
                pageLength: 25,
                ajax: {
                    url: "{{ route('admin.academic-directory.data.organisations') }}",
                    data: function(d) {
                        d.organisation_type_id = $('#filter_organisation_type_id').val();
                        d.status = $('#filter_status').val();
                    }
                },
                columns: [
                    { data: 'DT_RowIndex', name: 'DT_RowIndex', orderable: false, searchable: false },
                    { data: 'name_html', name: 'name' },
                    { data: 'location_html', name: 'head_office_location' },
                    { data: 'hierarchy_chips', name: 'hierarchy_chips', orderable: false, searchable: false },
                    { data: 'status_html', name: 'status' },
                    { data: 'action', name: 'action', orderable: false, searchable: false, className: 'text-end' }
                ],
                language: {
                    search: "_INPUT_",
                    searchPlaceholder: "Search organisations...",
                    processing: '<div class="spinner-border spinner-border-sm text-primary"></div> Loading...'
                }
            });
        }

        // Initialize Campuses DataTable
        function initCampusesTable() {
            if (tableCampuses) return;
            tableCampuses = $('#table-campuses').DataTable({
                processing: true,
                serverSide: true,
                pageLength: 25,
                ajax: {
                    url: "{{ route('admin.academic-directory.data.campuses') }}",
                    data: function(d) {
                        d.organisation_id = $('#filter_organisation_id').val();
                        d.status = $('#filter_status').val();
                    }
                },
                columns: [
                    { data: 'DT_RowIndex', name: 'DT_RowIndex', orderable: false, searchable: false },
                    { data: 'campus_name_html', name: 'campus_name' },
                    { data: 'organisation_html', name: 'organisation.name' },
                    { data: 'location_html', name: 'city' },
                    { data: 'facilities_badges', name: 'facilities', orderable: false, searchable: false },
                    { data: 'hierarchy_chips', name: 'hierarchy_chips', orderable: false, searchable: false },
                    { data: 'verification_badge', name: 'verification_status' },
                    { data: 'action', name: 'action', orderable: false, searchable: false, className: 'text-end' }
                ],
                language: {
                    search: "_INPUT_",
                    searchPlaceholder: "Search campuses...",
                    processing: '<div class="spinner-border spinner-border-sm text-info"></div> Loading...'
                }
            });
        }

        // Initialize Departments DataTable
        function initDepartmentsTable() {
            if (tableDepts) return;
            tableDepts = $('#table-departments').DataTable({
                processing: true,
                serverSide: true,
                pageLength: 25,
                ajax: {
                    url: "{{ route('admin.academic-directory.data.departments') }}",
                    data: function(d) {
                        d.organisation_id = $('#filter_organisation_id').val();
                        d.campus_id = $('#filter_campus_id').val();
                        d.discipline_area = $('#filter_discipline_area').val();
                    }
                },
                columns: [
                    { data: 'DT_RowIndex', name: 'DT_RowIndex', orderable: false, searchable: false },
                    { data: 'department_name_html', name: 'department_name' },
                    { data: 'hierarchy_html', name: 'organisation.name' },
                    { data: 'discipline_badge', name: 'discipline_area' },
                    { data: 'hod_info', name: 'head_of_department_name' },
                    { data: 'faculty_labs', name: 'faculty_count' },
                    { data: 'courses_count', name: 'courses_count', orderable: false, searchable: false },
                    { data: 'action', name: 'action', orderable: false, searchable: false, className: 'text-end' }
                ],
                language: {
                    search: "_INPUT_",
                    searchPlaceholder: "Search departments...",
                    processing: '<div class="spinner-border spinner-border-sm text-primary"></div> Loading...'
                }
            });
        }

        // Initialize Courses DataTable
        function initCoursesTable() {
            if (tableCourses) return;
            tableCourses = $('#table-courses').DataTable({
                processing: true,
                serverSide: true,
                pageLength: 25,
                ajax: {
                    url: "{{ route('admin.academic-directory.data.courses') }}",
                    data: function(d) {
                        d.organisation_id = $('#filter_organisation_id').val();
                        d.campus_id = $('#filter_campus_id').val();
                        d.department_id = $('#filter_department_id').val();
                        d.status = $('#filter_status').val();
                    }
                },
                columns: [
                    { data: 'DT_RowIndex', name: 'DT_RowIndex', orderable: false, searchable: false },
                    { data: 'course_name_html', name: 'course.name' },
                    { data: 'hierarchy_html', name: 'organisation.name' },
                    { data: 'mode_duration', name: 'duration' },
                    { data: 'fees_html', name: 'total_fees' },
                    { data: 'status_html', name: 'status' },
                    { data: 'action', name: 'action', orderable: false, searchable: false, className: 'text-end' }
                ],
                language: {
                    search: "_INPUT_",
                    searchPlaceholder: "Search offered courses...",
                    processing: '<div class="spinner-border spinner-border-sm text-success"></div> Loading...'
                }
            });
        }

        // Initialize default tab (Organisations)
        initOrganisationsTable();

        // Handle Tab Switches
        $('button[data-bs-toggle="pill"]').on('shown.bs.tab', function(e) {
            let targetPane = $(e.target).attr('data-bs-target');
            $('.kpi-card').removeClass('active-kpi');

            if (targetPane === '#tab-pane-organisations') {
                currentTab = 'organisations';
                $('#kpi-card-organisations').addClass('active-kpi');
                if (tableOrgs) tableOrgs.columns.adjust().draw(false);
            } else if (targetPane === '#tab-pane-campuses') {
                currentTab = 'campuses';
                $('#kpi-card-campuses').addClass('active-kpi');
                if (!tableCampuses) initCampusesTable();
                else tableCampuses.columns.adjust().draw(false);
            } else if (targetPane === '#tab-pane-departments') {
                currentTab = 'departments';
                $('#kpi-card-departments').addClass('active-kpi');
                if (!tableDepts) initDepartmentsTable();
                else tableDepts.columns.adjust().draw(false);
            } else if (targetPane === '#tab-pane-courses') {
                currentTab = 'courses';
                $('#kpi-card-courses').addClass('active-kpi');
                if (!tableCourses) initCoursesTable();
                else tableCourses.columns.adjust().draw(false);
            } else if (targetPane === '#tab-pane-analytics') {
                currentTab = 'analytics';
            }
        });

        // Function to reload all active tables based on new filters
        function reloadCurrentData() {
            if (tableOrgs && currentTab === 'organisations') tableOrgs.ajax.reload();
            if (tableCampuses && currentTab === 'campuses') tableCampuses.ajax.reload();
            if (tableDepts && currentTab === 'departments') tableDepts.ajax.reload();
            if (tableCourses && currentTab === 'courses') tableCourses.ajax.reload();
        }

        // Cascading Dropdown Handler: When Organisation changes
        $('#filter_organisation_id').on('change', function() {
            let orgId = $(this).val();
            let orgName = $(this).find('option:selected').text();

            if (orgId) {
                $('#active-filter-indicator').removeClass('d-none').addClass('d-flex');
                $('#active-filter-org-name').text(orgName);
            } else {
                $('#active-filter-indicator').addClass('d-none').removeClass('d-flex');
            }

            // Fetch cascading campuses and departments
            $.ajax({
                url: "{{ route('admin.academic-directory.cascading-options') }}",
                type: 'GET',
                data: { organisation_id: orgId },
                success: function(res) {
                    // Update Campuses dropdown
                    let campusSelect = $('#filter_campus_id');
                    campusSelect.empty().append('<option value="">All Campuses (Global)</option>');
                    if (res.campuses && res.campuses.length > 0) {
                        $.each(res.campuses, function(i, c) {
                            let loc = c.city ? ` (${c.city})` : '';
                            campusSelect.append(`<option value="${c.id}">${c.campus_name}${loc}</option>`);
                        });
                    }

                    // Update Departments dropdown
                    let deptSelect = $('#filter_department_id');
                    deptSelect.empty().append('<option value="">All Departments (Global)</option>');
                    if (res.departments && res.departments.length > 0) {
                        $.each(res.departments, function(i, d) {
                            let code = d.department_code ? ` [${d.department_code}]` : '';
                            deptSelect.append(`<option value="${d.id}">${d.department_name}${code}</option>`);
                        });
                    }

                    reloadCurrentData();
                }
            });
        });

        // Cascading Dropdown Handler: When Campus changes
        $('#filter_campus_id').on('change', function() {
            let campusId = $(this).val();
            let orgId = $('#filter_organisation_id').val();

            $.ajax({
                url: "{{ route('admin.academic-directory.cascading-options') }}",
                type: 'GET',
                data: { organisation_id: orgId, campus_id: campusId },
                success: function(res) {
                    let deptSelect = $('#filter_department_id');
                    deptSelect.empty().append('<option value="">All Departments (Global)</option>');
                    if (res.departments && res.departments.length > 0) {
                        $.each(res.departments, function(i, d) {
                            let code = d.department_code ? ` [${d.department_code}]` : '';
                            deptSelect.append(`<option value="${d.id}">${d.department_name}${code}</option>`);
                        });
                    }
                    reloadCurrentData();
                }
            });
        });

        // Trigger reload on other filter changes
        $('#filter_organisation_type_id, #filter_department_id, #filter_status, #filter_discipline_area').on('change', function() {
            reloadCurrentData();
        });

        // Clear Hierarchy Filter button
        $('#btn-clear-hierarchy-filter, #btn-reset-filters').on('click', function() {
            $('#filter_organisation_type_id').val('').trigger('change.select2');
            $('#filter_organisation_id').val('').trigger('change.select2');
            $('#filter_campus_id').empty().append('<option value="">All Campuses (Global)</option>').val('').trigger('change.select2');
            $('#filter_department_id').empty().append('<option value="">All Departments (Global)</option>').val('').trigger('change.select2');
            $('#filter_status').val('');
            $('#filter_discipline_area').val('');
            $('#active-filter-indicator').addClass('d-none').removeClass('d-flex');

            if (tableOrgs) tableOrgs.ajax.reload();
            if (tableCampuses) tableCampuses.ajax.reload();
            if (tableDepts) tableDepts.ajax.reload();
            if (tableCourses) tableCourses.ajax.reload();
        });

        // Interactive Hierarchy Chips (e.g. from Org row clicking "4 Campuses" or "12 Depts")
        $(document).on('click', '.switch-to-tab', function(e) {
            e.preventDefault();
            let targetTab = $(this).data('tab');
            let orgId = $(this).data('org-id');
            let campusId = $(this).data('campus-id');
            let deptId = $(this).data('dept-id');

            if (orgId) {
                $('#filter_organisation_id').val(orgId).trigger('change');
            }
            if (campusId) {
                setTimeout(function() {
                    $('#filter_campus_id').val(campusId).trigger('change');
                }, 400);
            }
            if (deptId) {
                setTimeout(function() {
                    $('#filter_department_id').val(deptId).trigger('change');
                }, 700);
            }

            switchMainTab(targetTab);
        });

        // Quick View Modal Drawer Trigger
        $(document).on('click', '.view-quick-drawer', function(e) {
            e.preventDefault();
            let type = $(this).data('type');
            let id = $(this).data('id');

            $('#quickViewModalBody').html(`
                <div class="text-center py-5">
                    <div class="spinner-border text-primary" role="status">
                        <span class="visually-hidden">Loading...</span>
                    </div>
                    <div class="text-muted small mt-2">Loading details...</div>
                </div>
            `);
            $('#quickViewModal').modal('show');

            let url = "{{ url('admin/academic-directory/quick-view') }}/" + type + "/" + id;
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

        // Dynamic Export Trigger
        $('.export-trigger').on('click', function(e) {
            e.preventDefault();
            let exportTab = $(this).data('export-tab') || currentTab;
            let orgId = $('#filter_organisation_id').val() || '';
            let campusId = $('#filter_campus_id').val() || '';
            let deptId = $('#filter_department_id').val() || '';
            let status = $('#filter_status').val() || '';

            let exportUrl = "{{ route('admin.academic-directory.export') }}?tab=" + exportTab +
                "&organisation_id=" + encodeURIComponent(orgId) +
                "&campus_id=" + encodeURIComponent(campusId) +
                "&department_id=" + encodeURIComponent(deptId) +
                "&status=" + encodeURIComponent(status);

            window.location.href = exportUrl;
        });

    });
</script>
@endpush

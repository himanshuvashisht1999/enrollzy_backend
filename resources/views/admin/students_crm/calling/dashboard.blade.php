@extends('admin.layouts.master')

@section('title', 'Calling Dashboard')

@push('css')
<style>
    /* Suppress generic master header */
    .admin-navbar {
        display: none !important;
    }
    
    #content {
        padding: 0 !important;
        background-color: #f4f6f9 !important;
        min-height: 100vh;
    }

    .telecaller-app {
        font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, Helvetica, Arial, sans-serif;
        color: #1e293b;
        padding-bottom: 4rem;
    }

    /* 1. App Top Header */
    /* 1. Header */
    .app-header {
        background: #ffffff;
        border-bottom: 1px solid #e2e8f0;
        padding: 0.85rem 1.5rem;
    }

    /* 2. Enterprise KPI Tiles */
    .kpi-card {
        background: #ffffff;
        border: 1px solid #e2e8f0;
        border-radius: 12px;
        padding: 1rem 1.25rem;
        box-shadow: 0 1px 3px rgba(0,0,0,0.02);
        transition: all 0.2s ease;
        position: relative;
        overflow: hidden;
    }
    .kpi-card:hover {
        transform: translateY(-2px);
        box-shadow: 0 4px 12px rgba(0,0,0,0.05);
    }
    .kpi-card-blue { border-top: 3px solid #2563eb; }
    .kpi-card-rose { border-top: 3px solid #e11d48; }
    .kpi-card-indigo { border-top: 3px solid #6366f1; }
    .kpi-card-emerald { border-top: 3px solid #10b981; }

    .kpi-header {
        display: flex;
        align-items: center;
        justify-content: space-between;
        margin-bottom: 0.35rem;
    }
    .kpi-label {
        font-size: 0.72rem;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: 0.05em;
        color: #64748b;
    }
    .kpi-icon {
        width: 28px;
        height: 28px;
        border-radius: 6px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 0.82rem;
    }
    .kpi-val {
        font-size: 1.85rem;
        font-weight: 800;
        line-height: 1.1;
        color: #0f172a;
    }
    .kpi-sub {
        font-size: 0.75rem;
        color: #94a3b8;
        margin-top: 0.25rem;
    }

    /* 3. Universal Filter Hub */
    .filter-hub {
        background: #ffffff;
        border: 1px solid #e2e8f0;
        border-radius: 12px;
        box-shadow: 0 1px 3px rgba(0, 0, 0, 0.02);
        overflow: hidden;
    }
    .filter-top-bar {
        padding: 0.85rem 1.25rem;
        background: #ffffff;
        border-bottom: 1px solid #f1f5f9;
        display: flex;
        align-items: center;
        gap: 12px;
        flex-wrap: wrap;
    }
    .filter-search-box {
        position: relative;
        flex: 1 1 280px;
    }
    .filter-search-icon {
        position: absolute;
        left: 12px;
        top: 50%;
        transform: translateY(-50%);
        color: #94a3b8;
        font-size: 0.85rem;
    }
    .filter-search-input {
        padding-left: 36px;
        height: 38px;
        border-radius: 7px;
        border: 1px solid #cbd5e1;
        background: #f8fafc;
        font-size: 0.86rem;
        font-weight: 500;
        color: #0f172a;
        transition: all 0.15s ease;
        width: 100%;
    }
    .filter-search-input:focus {
        background: #ffffff;
        border-color: #2563eb;
        box-shadow: 0 0 0 3px rgba(37, 99, 235, 0.12);
        outline: none;
    }
    .filter-date-badge {
        background: #f8fafc;
        border: 1px solid #cbd5e1;
        border-radius: 7px;
        padding: 0 10px;
        height: 38px;
        display: inline-flex;
        align-items: center;
        gap: 6px;
    }
    .filter-date-input {
        border: none;
        background: transparent;
        font-size: 0.82rem;
        font-weight: 600;
        color: #1e293b;
        outline: none;
        width: 115px;
        padding: 0;
    }
    .filter-bottom-grid {
        padding: 0.85rem 1.25rem;
        background: #ffffff;
    }
    .filter-micro-label {
        font-size: 0.68rem;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: 0.05em;
        color: #64748b;
        margin-bottom: 0.3rem;
        display: block;
    }
    .filter-select-pro {
        height: 38px;
        border-radius: 7px;
        border: 1px solid #cbd5e1;
        background-color: #f8fafc;
        font-size: 0.85rem;
        font-weight: 500;
        color: #0f172a;
        transition: all 0.15s ease;
        width: 100%;
        padding: 0.45rem 0.75rem;
    }
    .filter-select-pro:focus {
        background-color: #ffffff;
        border-color: #2563eb;
        box-shadow: 0 0 0 3px rgba(37, 99, 235, 0.12);
        outline: none;
    }

    .btn-filter-apply {
        background: #0f172a;
        color: #ffffff;
        border: 1px solid #0f172a;
        border-radius: 8px;
        height: 38px;
        padding: 0 1.25rem;
        font-size: 0.84rem;
        font-weight: 600;
        letter-spacing: 0.2px;
        display: inline-flex;
        align-items: center;
        gap: 6px;
        transition: all 0.15s ease;
        box-shadow: 0 1px 2px rgba(15, 23, 42, 0.08);
    }
    .btn-filter-apply:hover {
        background: #1e293b;
        border-color: #1e293b;
        color: #ffffff;
        box-shadow: 0 4px 12px rgba(15, 23, 42, 0.15);
        transform: translateY(-1px);
    }
    .btn-filter-reset {
        background: #f8fafc;
        border: 1px solid #cbd5e1;
        color: #64748b;
        border-radius: 8px;
        height: 38px;
        width: 38px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        transition: all 0.15s ease;
        text-decoration: none;
    }
    .btn-filter-reset:hover {
        background: #f1f5f9;
        color: #0f172a;
        border-color: #94a3b8;
    }

    /* Hide CKEditor 4 Update/Security Warning Banner */
    .cke_notification_warning, 
    .cke_notification, 
    .cke_notifications_area {
        display: none !important;
        visibility: hidden !important;
        opacity: 0 !important;
        height: 0 !important;
        pointer-events: none !important;
    }

    /* 4. Segmented Control Navigation */
    .segmented-control {
        background: #f1f5f9;
        padding: 3px;
        border-radius: 9px;
        display: inline-flex;
        gap: 3px;
        border: 1px solid #e2e8f0;
    }
    .segmented-control .nav-link {
        border: none;
        border-radius: 6px;
        padding: 0.45rem 1rem;
        font-size: 0.84rem;
        font-weight: 600;
        color: #64748b;
        background: transparent;
        transition: all 0.15s ease;
        display: flex;
        align-items: center;
        gap: 6px;
    }
    .segmented-control .nav-link:hover {
        color: #0f172a;
    }
    .segmented-control .nav-link.active {
        background: #ffffff;
        color: #0f172a;
        box-shadow: 0 1px 3px rgba(0, 0, 0, 0.08);
    }
    .pill-count {
        font-size: 0.7rem;
        font-weight: 700;
        padding: 0.1rem 0.45rem;
        border-radius: 9999px;
        background: #e2e8f0;
        color: #475569;
    }
    .segmented-control .nav-link.active .pill-count {
        background: #0f172a;
        color: #ffffff;
    }

    /* 5. Clean Lead Table */
    .workspace-card {
        background: #ffffff;
        border: 1px solid #e2e8f0;
        border-radius: 12px;
        box-shadow: 0 1px 3px rgba(0, 0, 0, 0.02);
        overflow: hidden;
    }
    .lead-table {
        margin-bottom: 0;
    }
    .lead-table thead th {
        background-color: #f8fafc;
        color: #475569;
        font-size: 0.72rem;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: 0.05em;
        padding: 0.85rem 1rem;
        border-bottom: 1px solid #e2e8f0;
        border-top: none;
    }
    .lead-table tbody td {
        padding: 0.9rem 1rem;
        vertical-align: middle;
        border-bottom: 1px solid #f1f5f9;
        font-size: 0.88rem;
    }
    .lead-table tbody tr:hover td {
        background-color: #f8fafc;
    }
    .lead-table tbody tr:last-child td {
        border-bottom: none;
    }

    /* Status Tags */
    .status-tag {
        font-size: 0.75rem;
        font-weight: 600;
        padding: 0.25rem 0.6rem;
        border-radius: 6px;
        display: inline-flex;
        align-items: center;
        gap: 4px;
    }
    .tag-overdue {
        background: #fef2f2;
        color: #dc2626;
        border: 1px solid #fecaca;
    }
    .tag-due {
        background: #fffbeb;
        color: #d97706;
        border: 1px solid #fde68a;
    }
    .tag-new {
        background: #eff6ff;
        color: #2563eb;
        border: 1px solid #bfdbfe;
    }
    .tag-reassigned {
        background: #f5f3ff;
        color: #7c3aed;
        border: 1px solid #ddd6fe;
    }
    .tag-completed {
        background: #f0fdf4;
        color: #16a34a;
        border: 1px solid #bbf7d0;
    }

    /* Buttons */
    .btn-call-action {
        background: #0f172a;
        color: #ffffff;
        border: none;
        border-radius: 7px;
        font-weight: 600;
        font-size: 0.82rem;
        padding: 0.45rem 0.95rem;
        transition: all 0.15s ease;
    }
    .btn-call-action:hover {
        background: #1e293b;
        color: #ffffff;
        transform: translateY(-1px);
    }
        padding: 0.5rem 1.15rem;
        transition: all 0.15s ease;
        display: inline-flex;
        align-items: center;
        gap: 6px;
    }
    .btn-call-action:hover {
        background: #2563eb;
        color: #ffffff;
        transform: translateY(-1px);
        box-shadow: 0 3px 8px rgba(37, 99, 235, 0.3);
    }

    .btn-reassign-action {
        background: #ffffff;
        color: #475569;
        border: 1px solid #cbd5e1;
        border-radius: 8px;
        font-weight: 600;
        font-size: 0.85rem;
        padding: 0.5rem 0.85rem;
        transition: all 0.15s ease;
    }
    .btn-reassign-action:hover {
        background: #f8fafc;
        border-color: #94a3b8;
        color: #0f172a;
    }

    /* Team Collapsible Card */
    .team-accordion-btn {
        background: #ffffff;
        border: 1px solid #e2e8f0;
        border-radius: 12px;
        padding: 0.85rem 1.25rem;
        width: 100%;
        text-align: left;
        display: flex;
        align-items: center;
        justify-content: space-between;
        font-weight: 700;
        font-size: 0.9rem;
        color: #1e293b;
        box-shadow: 0 1px 2px rgba(0,0,0,0.02);
    }
    .team-accordion-btn:hover {
        background: #f8fafc;
    }

    /* ============================================================ */
    /* ENTERPRISE CALLING COCKPIT MODAL (SPACIOUS & ELEGANT UI/UX) */
    /* ============================================================ */
    .cockpit-dialog {
        max-width: 1400px !important;
        width: 95vw !important;
        height: calc(100vh - 24px) !important;
        margin: 12px auto !important;
    }
    .cockpit-modal-content {
        border-radius: 14px !important;
        height: calc(100vh - 24px) !important;
        max-height: calc(100vh - 24px) !important;
        display: flex;
        flex-direction: column;
        overflow: hidden;
        background: #f8fafc;
        box-shadow: 0 25px 50px -12px rgba(15, 23, 42, 0.25) !important;
    }
    .cockpit-header {
        background: #ffffff !important;
        border-bottom: 1px solid #e2e8f0 !important;
        min-height: 64px;
        padding: 0.65rem 1.5rem !important;
        flex-shrink: 0;
    }
    .cockpit-close-btn {
        border-radius: 50%;
        padding: 0.65rem;
        transition: all 0.2s ease;
        background-color: #f1f5f9;
    }
    .cockpit-close-btn:hover {
        background-color: #e2e8f0;
        transform: rotate(90deg);
    }
    .cockpit-body {
        flex: 1 1 auto;
        min-height: 0;
        overflow: hidden;
    }
    .cockpit-grid {
        display: grid;
        grid-template-columns: 1.35fr 1fr;
        height: 100%;
        min-height: 0;
        gap: 1rem;
        padding: 0.85rem 1.25rem;
    }
    @media (max-width: 1100px) {
        .cockpit-grid {
            grid-template-columns: 1fr;
            overflow-y: auto;
        }
        .cockpit-modal-content {
            height: auto;
            max-height: none;
        }
    }
    .cockpit-left-pane {
        background: #ffffff;
        border: 1px solid #e2e8f0;
        border-radius: 12px;
        box-shadow: 0 1px 3px rgba(0, 0, 0, 0.02);
        display: flex;
        flex-direction: column;
        height: 100%;
        min-height: 0;
        overflow: hidden;
    }
    .cockpit-pane-header {
        background: #ffffff !important;
        border-bottom: 1px solid #f1f5f9;
        flex-shrink: 0;
        padding: 0.65rem 1.25rem !important;
    }
    .cockpit-form-scroll {
        flex: 1 1 auto;
        min-height: 0;
        overflow-y: auto;
        padding: 1rem 1.25rem !important;
    }
    .cockpit-form-scroll::-webkit-scrollbar,
    .cockpit-timeline-body::-webkit-scrollbar {
        width: 6px;
    }
    .cockpit-form-scroll::-webkit-scrollbar-track,
    .cockpit-timeline-body::-webkit-scrollbar-track {
        background: transparent;
    }
    .cockpit-form-scroll::-webkit-scrollbar-thumb,
    .cockpit-timeline-body::-webkit-scrollbar-thumb {
        background: #cbd5e1;
        border-radius: 4px;
    }
    .cockpit-form-scroll::-webkit-scrollbar-thumb:hover,
    .cockpit-timeline-body::-webkit-scrollbar-thumb:hover {
        background: #94a3b8;
    }
    
    /* Clean Sub-Cards for Visual Spacing & Non-Crowded Flow */
    .cockpit-subcard {
        background: #f8fafc;
        border: 1px solid #e2e8f0;
        border-radius: 10px;
        padding: 1rem 1.15rem;
        margin-bottom: 1rem;
    }
    .cockpit-subcard-title {
        display: flex;
        align-items: center;
        font-size: 0.8rem;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: 0.04em;
        color: #334155;
        margin-bottom: 0.85rem;
        padding-bottom: 0.5rem;
        border-bottom: 1px dashed #cbd5e1;
    }
    .cockpit-label {
        font-size: 0.76rem;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: 0.03em;
        color: #475569;
        margin-bottom: 0.35rem;
        display: block;
    }
    .cockpit-input-group {
        position: relative;
    }
    .cockpit-input-icon {
        position: absolute;
        left: 12px;
        top: 50%;
        transform: translateY(-50%);
        color: #94a3b8;
        font-size: 0.85rem;
        z-index: 4;
        pointer-events: none;
    }
    .cockpit-input-with-icon {
        padding-left: 36px !important;
    }
    .cockpit-input,
    .cockpit-select {
        border-radius: 8px !important;
        border: 1px solid #cbd5e1 !important;
        font-size: 0.88rem !important;
        padding: 0.5rem 0.75rem !important;
        height: 42px;
        color: #0f172a !important;
        background-color: #ffffff;
        transition: all 0.15s ease !important;
    }
    .cockpit-input:focus,
    .cockpit-select:focus {
        border-color: #2563eb !important;
        box-shadow: 0 0 0 3px rgba(37, 99, 235, 0.12) !important;
        outline: none !important;
    }
    #callModal .select2-container {
        width: 100% !important;
    }
    #callModal .select2-container--default .select2-selection--single {
        height: 42px !important;
        border: 1px solid #cbd5e1 !important;
        border-radius: 8px !important;
        padding: 6px 10px !important;
        display: flex !important;
        align-items: center !important;
        background-color: #ffffff !important;
    }
    #callModal .select2-container--default .select2-selection--single .select2-selection__rendered {
        line-height: 30px !important;
        font-size: 0.88rem !important;
        color: #0f172a !important;
        padding-left: 2px !important;
    }
    #callModal .select2-container--default .select2-selection--single .select2-selection__arrow {
        height: 40px !important;
        right: 8px !important;
    }
    #callModal .select2-container--default.select2-container--focus .select2-selection--single {
        border-color: #2563eb !important;
        box-shadow: 0 0 0 3px rgba(37, 99, 235, 0.12) !important;
    }
    .cockpit-sticky-footer {
        border-top: 1px solid #e2e8f0;
        background: #ffffff !important;
        flex-shrink: 0;
        padding: 0.75rem 1.5rem !important;
        box-shadow: 0 -4px 12px rgba(0, 0, 0, 0.02);
    }
    .cockpit-right-pane {
        background: #ffffff;
        border: 1px solid #e2e8f0;
        border-radius: 14px;
        box-shadow: 0 1px 3px rgba(0, 0, 0, 0.02);
        height: 100%;
        overflow: hidden;
        display: flex;
        flex-direction: column;
    }
    .cockpit-timeline-header {
        background: #ffffff !important;
        border-bottom: 1px solid #f1f5f9;
    }
    .cockpit-timeline-body {
        background: #f8fafc;
    }
    .timeline-stepper {
        position: relative;
        padding-left: 6px;
    }
    .timeline-stepper::before {
        content: '';
        position: absolute;
        top: 12px;
        bottom: 12px;
        left: 17px;
        width: 2px;
        background: #e2e8f0;
    }
    .timeline-item {
        position: relative;
        padding-left: 38px;
        margin-bottom: 1rem;
    }
    .timeline-dot {
        position: absolute;
        left: 6px;
        top: 6px;
        width: 24px;
        height: 24px;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 0.65rem;
        color: #ffffff;
        z-index: 2;
    }
    .timeline-card {
        background: #ffffff;
        border: 1px solid #e2e8f0;
        border-radius: 10px;
        box-shadow: 0 1px 2px rgba(0, 0, 0, 0.03);
        transition: transform 0.15s ease, box-shadow 0.15s ease;
    }
    .timeline-card:hover {
        box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.05);
    }
</style>
@endpush

@section('content')
<div class="telecaller-app">

    <!-- 1. Top Workspace Header -->
    <header class="app-header mb-4">
        <div class="d-flex flex-wrap align-items-center justify-content-between gap-3">
            <div class="d-flex align-items-center gap-3">
                <div class="bg-primary text-white rounded-3 d-flex align-items-center justify-content-center shadow-sm" style="width: 40px; height: 40px;">
                    <i class="fas fa-headset fs-5"></i>
                </div>
                <div>
                    <h5 class="fw-bold mb-0 text-dark">Calling Dashboard</h5>
                    <div class="text-muted" style="font-size: 0.8rem;">Your daily calling list & follow-ups manager</div>
                </div>
            </div>
            
            <div class="d-flex align-items-center gap-3">
                <div class="d-flex align-items-center gap-2 bg-light rounded-pill px-3 py-1.5 border">
                    <div class="rounded-circle bg-primary text-white d-flex align-items-center justify-content-center fw-bold" style="width: 28px; height: 28px; font-size: 0.8rem;">
                        {{ strtoupper(substr(auth()->user()->name ?? 'U', 0, 1)) }}
                    </div>
                    <span class="fw-bold text-dark small">{{ auth()->user()->name }}</span>
                    <span class="badge bg-secondary text-white rounded-pill px-2 py-0.5" style="font-size: 0.7rem;">{{ auth()->user()->role ?? 'Staff' }}</span>
                </div>
                <button type="button" class="btn btn-outline-secondary btn-sm rounded-pill px-3 py-1.5" onclick="window.location.reload();">
                    <i class="fas fa-sync-alt me-1"></i> Refresh
                </button>
            </div>
        </div>
    </header>

    <div class="container-fluid px-4">

        <!-- 2. Enterprise KPI Deck -->
        <div class="row g-3 mb-3">
            <div class="col-xl-3 col-md-6">
                <div class="kpi-card kpi-card-blue">
                    <div class="kpi-header">
                        <span class="kpi-label">Calls Waiting</span>
                        <span class="kpi-icon bg-primary bg-opacity-10 text-primary">
                            <i class="fas fa-headset"></i>
                        </span>
                    </div>
                    <div class="kpi-val">{{ $pendingInQueueCount }}</div>
                    <div class="kpi-sub">Leads waiting in personal queue</div>
                </div>
            </div>

            <div class="col-xl-3 col-md-6">
                <div class="kpi-card kpi-card-rose">
                    <div class="kpi-header">
                        <span class="kpi-label text-danger">Follow-ups Due</span>
                        <span class="kpi-icon bg-danger bg-opacity-10 text-danger">
                            <i class="fas fa-phone-volume"></i>
                        </span>
                    </div>
                    <div class="kpi-val text-danger">{{ $followUpsDueTodayCount }}</div>
                    <div class="kpi-sub">Scheduled callbacks / overdue</div>
                </div>
            </div>

            <div class="col-xl-3 col-md-6">
                <div class="kpi-card kpi-card-indigo">
                    <div class="kpi-header">
                        <span class="kpi-label" style="color: #4f46e5;">Total Assigned</span>
                        <span class="kpi-icon text-white" style="background: #6366f1;">
                            <i class="fas fa-users"></i>
                        </span>
                    </div>
                    <div class="kpi-val" style="color: #3730a3;">{{ $leadsAssignedTodayCount }}</div>
                    <div class="kpi-sub">Assigned in selected range</div>
                </div>
            </div>

            <div class="col-xl-3 col-md-6">
                <div class="kpi-card kpi-card-emerald">
                    <div class="kpi-header">
                        <span class="kpi-label text-success">Admissions Target</span>
                        <span class="kpi-icon bg-success bg-opacity-10 text-success">
                            <i class="fas fa-trophy"></i>
                        </span>
                    </div>
                    <div class="d-flex align-items-baseline gap-2">
                        <span class="kpi-val text-success">{{ $admissionsThisMonthCount }}</span>
                        <span class="text-muted small fw-semibold">/ {{ $admissionsTarget }} Target ({{ $targetProgress }}%)</span>
                    </div>
                    <div class="progress mt-2" style="height: 4px; border-radius: 9999px; background-color: #d1fae5;">
                        <div class="progress-bar bg-success rounded-pill" style="width: {{ min(100, $targetProgress) }}%"></div>
                    </div>
                </div>
            </div>
        </div>

        <!-- 3. Team Performance (Collapsible for Managers) -->
        @if($hasSubordinates)
        <div class="mb-3">
            <button class="team-accordion-btn py-2 px-3" type="button" data-bs-toggle="collapse" data-bs-target="#teamPerformanceCollapse">
                <div class="d-flex align-items-center gap-2">
                    <i class="fas fa-users-cog text-primary"></i>
                    <span class="fw-bold" style="font-size: 0.84rem;">Team Performance Breakdown</span>
                    <span class="badge bg-primary bg-opacity-10 text-primary ms-1" style="font-size: 0.72rem;">{{ count($teamMetrics) }} Subordinates</span>
                </div>
                <div class="d-flex align-items-center gap-2">
                    <span class="small text-muted fw-normal" style="font-size: 0.76rem;">View Team Stats</span>
                    <i class="fas fa-chevron-down text-muted" style="font-size: 0.75rem;"></i>
                </div>
            </button>
            
            <div class="collapse mt-2" id="teamPerformanceCollapse">
                <div class="workspace-card p-0">
                    <div class="table-responsive">
                        <table class="table lead-table align-middle mb-0">
                            <thead>
                                <tr>
                                    <th>Staff Member</th>
                                    <th>Role</th>
                                    <th class="text-center">Assigned Leads</th>
                                    <th class="text-center">Calls Made</th>
                                    <th class="text-center">Calls Pending</th>
                                    <th class="text-center">Follow-ups Due</th>
                                    <th class="text-center text-success">Admissions Closed</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($teamMetrics as $sub)
                                <tr>
                                    <td class="fw-bold text-dark">{{ $sub['name'] }}</td>
                                    <td><span class="badge bg-light text-secondary border">{{ $sub['role'] }}</span></td>
                                    <td class="text-center fw-semibold">{{ $sub['leads_assigned'] }}</td>
                                    <td class="text-center text-primary fw-semibold">{{ $sub['leads_worked'] }}</td>
                                    <td class="text-center text-danger fw-bold">{{ $sub['leads_pending'] }}</td>
                                    <td class="text-center text-warning fw-bold">{{ $sub['followups_due'] }}</td>
                                    <td class="text-center text-success fw-bold">{{ $sub['admissions'] }}</td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
        @endif

        <!-- 4. Universal Command & Filter Hub -->
        <form method="GET" action="{{ route('admin.students-crm.calling-dashboard.index') }}" class="filter-hub mb-3" id="filterForm">
            <!-- Top Command Bar: Universal Search, Date Range, & Actions -->
            <div class="filter-top-bar">
                <!-- Universal Search (Name, Phone, ID) -->
                <div class="filter-search-box">
                    <i class="fas fa-search filter-search-icon"></i>
                    <input type="text" name="filter_name" id="nameFilter" class="filter-search-input" 
                        placeholder="Search student by name, phone number, or student ID..." 
                        value="{{ request('filter_name') }}">
                </div>

                <!-- Date Range Group -->
                <div class="filter-date-badge">
                    <i class="far fa-calendar-alt text-muted small"></i>
                    <input type="date" name="start_date" id="start_date" class="filter-date-input" value="{{ request('start_date', $startDate) }}" required title="Start Date">
                    <span class="text-muted small px-0.5">→</span>
                    <input type="date" name="end_date" id="end_date" class="filter-date-input" value="{{ request('end_date', $endDate) }}" required title="End Date">
                </div>

                <!-- Action Buttons -->
                <div class="d-flex align-items-center gap-2 ms-auto">
                    <a href="{{ route('admin.students-crm.calling-dashboard.index') }}" class="btn-filter-reset" title="Reset All Filters">
                        <i class="fas fa-undo-alt"></i>
                    </a>
                    <button type="submit" class="btn-filter-apply" id="submitSearchButton">
                        <i class="fas fa-search"></i> Search Leads
                    </button>
                </div>
            </div>

            <!-- Bottom Attribute Selectors Ribbon (Equal Width Grid) -->
            <div class="filter-bottom-grid">
                <div class="row row-cols-1 row-cols-sm-2 row-cols-md-3 row-cols-lg-{{ count($staffs) > 0 ? '6' : '5' }} g-2.5">
                    <!-- 1. Call Status -->
                    <div class="col">
                        <label class="filter-micro-label">Call Status</label>
                        <select name="call_status_id" class="form-select filter-select-pro" id="callStatusFilter">
                            <option value="">All Call Statuses</option>
                            <option value="new" {{ request('call_status_id') == 'new' ? 'selected' : '' }}>New Leads</option>
                            <option value="reassigned" {{ request('call_status_id') == 'reassigned' ? 'selected' : '' }}>Reassigned Leads</option>
                            <option value="all" {{ request('call_status_id') == 'all' ? 'selected' : '' }}>All Attempted</option>
                            @foreach($statuses as $st)
                                <option value="{{ $st->id }}" {{ request('call_status_id') == $st->id ? 'selected' : '' }}>
                                    {{ $st->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <!-- 2. Category -->
                    <div class="col">
                        <label class="filter-micro-label">Category</label>
                        <select name="category" class="form-select filter-select-pro" id="categoryFilter">
                            <option value="">All Categories</option>
                            @php
                                if(!function_exists('renderCategoryOptionsDashboard')) {
                                    function renderCategoryOptionsDashboard($categories, $level = 0) {
                                        foreach ($categories as $cat) {
                                            echo '<option value="'.$cat->id.'"'.
                                                (request('category') == $cat->id ? ' selected' : '').
                                                '>';
                                            echo str_repeat("— ", $level).$cat->name;
                                            echo '</option>';
                                            if ($cat->childrenRecursive && $cat->childrenRecursive->count()) {
                                                renderCategoryOptionsDashboard($cat->childrenRecursive, $level + 1);
                                            }
                                        }
                                    }
                                }
                            @endphp
                            @php renderCategoryOptionsDashboard($categories); @endphp
                        </select>
                    </div>

                    @if(count($staffs) > 0)
                    <!-- 3. Staff Filter -->
                    <div class="col">
                        <label class="filter-micro-label">Assigned Staff</label>
                        <select name="staff_id" class="form-select filter-select-pro" id="staffFilter">
                            <option value="{{ auth()->id() }}">Me ({{ auth()->user()->name }})</option>
                            <option value="all" {{ request('staff_id') == 'all' ? 'selected' : '' }}>All Staff</option>
                            @foreach($staffs as $staff)
                                @if($staff->id != auth()->id())
                                    <option value="{{ $staff->id }}" {{ request('staff_id') == $staff->id ? 'selected' : '' }}>
                                        {{ $staff->name }}
                                    </option>
                                @endif
                            @endforeach
                        </select>
                    </div>
                    @endif

                    <!-- 4. Country -->
                    <div class="col">
                        <label class="filter-micro-label">Country</label>
                        <select name="country" class="form-select filter-select-pro" id="countryFilter">
                            <option value="" {{ request('country') == '' ? 'selected' : '' }}>All Countries</option>
                            <option value="India" {{ request('country') == 'India' ? 'selected' : '' }}>India</option>
                            @if(isset($dbCountries))
                                @foreach($dbCountries as $dbc)
                                    @if(strtolower($dbc) != 'india')
                                        <option value="{{ $dbc }}" {{ request('country') == $dbc ? 'selected' : '' }}>{{ $dbc }}</option>
                                    @endif
                                @endforeach
                            @endif
                        </select>
                    </div>

                    <!-- 5. State -->
                    <div class="col">
                        <label class="filter-micro-label">State</label>
                        <select name="state" class="form-select filter-select-pro" id="stateFilter">
                            <option value="">All States</option>
                            @if(request('state'))
                                <option value="{{ request('state') }}" selected>{{ request('state') }}</option>
                            @endif
                            @if(isset($dbStates))
                                @foreach($dbStates as $dbs)
                                    @if(request('state') != $dbs)
                                        <option value="{{ $dbs }}">{{ $dbs }}</option>
                                    @endif
                                @endforeach
                            @endif
                        </select>
                    </div>

                    <!-- 6. City -->
                    <div class="col">
                        <label class="filter-micro-label">City</label>
                        <select name="city" class="form-select filter-select-pro" id="cityFilter">
                            <option value="">All Cities</option>
                            @if(request('city'))
                                <option value="{{ request('city') }}" selected>{{ request('city') }}</option>
                            @endif
                            @if(isset($dbCities))
                                @foreach($dbCities as $dbct)
                                    @if(request('city') != $dbct)
                                        <option value="{{ $dbct }}">{{ $dbct }}</option>
                                    @endif
                                @endforeach
                            @endif
                        </select>
                    </div>
                </div>
            </div>
        </form>

        <!-- 5. Main Lead Calling Workspace -->
        <div class="workspace-card mb-5">
            
            <!-- Clean Segmented Navigation Header -->
            <div class="p-3 border-bottom d-flex flex-wrap justify-content-between align-items-center gap-3">
                <div class="segmented-control" role="tablist">
                    <button class="nav-link active" data-bs-toggle="pill" data-bs-target="#my-queue-tab" type="button">
                        <i class="fas fa-phone-volume"></i> Your Calling Queue
                        <span class="pill-count">{{ $queue->count() }}</span>
                    </button>
                    
                    @if(count($staffs) > 0)
                    <button class="nav-link" data-bs-toggle="pill" data-bs-target="#delegated-queue-tab" type="button">
                        <i class="fas fa-users"></i> Team Assigned Leads
                        <span class="pill-count">{{ count($delegatedLeads) }}</span>
                    </button>
                    @endif

                    <button class="nav-link" data-bs-toggle="pill" data-bs-target="#history-queue-tab" type="button">
                        <i class="fas fa-history"></i> Completed Calls
                        <span class="pill-count">{{ count($workedHistory) }}</span>
                    </button>
                </div>

                <!-- Priority Status Indicators -->
                <div class="d-flex align-items-center gap-3 small" style="font-size: 0.78rem;">
                    <span class="text-danger fw-bold"><i class="fas fa-circle me-1" style="font-size: 8px;"></i> Overdue Call</span>
                    <span class="text-warning fw-bold"><i class="fas fa-circle me-1" style="font-size: 8px;"></i> Due Today</span>
                    <span class="text-primary fw-bold"><i class="fas fa-circle me-1" style="font-size: 8px;"></i> New Lead</span>
                    <span class="fw-bold" style="color: #7c3aed;"><i class="fas fa-circle me-1" style="font-size: 8px;"></i> Reassigned Lead</span>
                </div>
            </div>

            <!-- Tab Contents -->
            <div class="tab-content">

                <!-- TAB 1: Your Calling Queue -->
                <div class="tab-pane fade show active" id="my-queue-tab">
                    <div class="table-responsive">
                        <table class="table lead-table align-middle">
                            <thead>
                                <tr>
                                    <th>#</th>
                                    <th>Student Details</th>
                                    <th>Phone Number</th>
                                    <th>Lead Status</th>
                                    <th>Follow-up Due</th>
                                    <th>Assigned By</th>
                                    <th class="text-end pe-4">Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($queue as $item)
                                    @php 
                                        $customer = $item['customer'];
                                        $history = $item['history'];
                                        $type = $item['type'];
                                        
                                        $statusTag = '<span class="status-tag tag-new"><i class="fas fa-sparkles"></i> New Lead</span>';
                                        if ($type === 'overdue') {
                                            $statusTag = '<span class="status-tag tag-overdue"><i class="fas fa-exclamation-circle"></i> Overdue Call</span>';
                                        } elseif ($type === 'due_today') {
                                            $statusTag = '<span class="status-tag tag-due"><i class="fas fa-clock"></i> Due Today</span>';
                                        } elseif ($type === 'reassigned') {
                                            $statusTag = '<span class="status-tag tag-reassigned"><i class="fas fa-random"></i> Reassigned Lead</span>';
                                        }
                                        
                                        $maskedPhone = substr($customer->phone, 0, 2) . 'XXXXX' . substr($customer->phone, -3);
                                        $isUnlocked = (isset($unlocked_lead_id) && $unlocked_lead_id == $customer->id);
                                    @endphp
                                    <tr>
                                        <td class="text-muted fw-bold small" style="width: 40px;">
                                             #{{ $loop->iteration }}
                                        </td>
                                        <td>
                                            <div class="fw-bold text-dark fs-6">{{ $customer->name }} <span class="badge bg-light text-secondary border fw-normal ms-1" style="font-size: 0.7rem;">ID #{{ $customer->id }}</span></div>
                                            <div class="text-muted small mt-0.5">
                                                @if(!empty($customer->interested_in_course))
                                                    <span class="text-primary fw-semibold">{{ $customer->interested_in_course }}</span>
                                                @endif
                                                @if(!empty($customer->city))
                                                    &middot; {{ $customer->city }}
                                                @endif
                                            </div>
                                        </td>
                                        <td>
                                            <div class="phone-container-{{ $customer->id }}">
                                                @if($isUnlocked)
                                                    <span class="badge bg-success bg-opacity-10 text-success border border-success border-opacity-25 px-2.5 py-1.5 fw-bold fs-7">
                                                        <i class="fas fa-phone-alt me-1"></i> {{ $customer->phone }}
                                                    </span>
                                                @else
                                                    <span class="text-dark fw-bold">{{ $maskedPhone }}</span>
                                                    <a href="javascript:void(0);" class="unlock-phone-btn ms-2 text-primary fw-bold text-decoration-none small" data-id="{{ $customer->id }}">
                                                        <i class="fas fa-eye me-0.5"></i> Show
                                                    </a>
                                                @endif
                                            </div>
                                        </td>
                                        <td>
                                            {!! $statusTag !!}
                                            @if($history && $history->calling_status)
                                                <div class="text-muted small mt-1 fw-medium">
                                                    {{ $history->calling_status->name }}
                                                </div>
                                            @endif
                                        </td>
                                        <td>
                                            @if($history && $history->date_required)
                                                <div class="fw-bold {{ $type === 'overdue' ? 'text-danger' : 'text-dark' }}">
                                                    <i class="far fa-calendar-alt me-1"></i> {{ \Carbon\Carbon::parse($history->date_required)->format('d M, Y') }}
                                                </div>
                                            @else
                                                <span class="text-muted">—</span>
                                            @endif
                                        </td>
                                        <td>
                                            <div class="fw-semibold text-dark small">{{ $assignmentsLookup[$customer->id]->assigner->name ?? 'System' }}</div>
                                            @if(isset($assignmentsLookup[$customer->id]->updated_at))
                                                <div class="text-muted small" style="font-size: 0.72rem;">
                                                    <i class="far fa-clock me-0.5"></i> {{ \Carbon\Carbon::parse($assignmentsLookup[$customer->id]->updated_at)->format('d M, Y h:i A') }}
                                                </div>
                                            @endif
                                        </td>
                                        <td class="text-end pe-4">
                                            <div class="d-flex align-items-center justify-content-end gap-2">
                                                @if(isset($assignmentsLookup[$customer->id]) && ($assignmentsLookup[$customer->id]->assigned_by == auth()->id() || auth()->user()->is_admin || auth()->user()->hasRole('superadmin')))
                                                    <button type="button" class="btn btn-reassign-action open-reassign-modal" data-id="{{ $customer->id }}" data-name="{{ $customer->name }}" title="Reassign Lead">
                                                        <i class="fas fa-user-edit"></i>
                                                    </button>
                                                @endif

                                                <button type="button" class="btn btn-call-action open-calling-modal call-btn-{{ $customer->id }}" 
                                                    data-id="{{ $customer->id }}" 
                                                    data-name="{{ $customer->name }}" 
                                                    data-phone="{{ $isUnlocked ? $customer->phone : $maskedPhone }}" 
                                                    data-category="{{ $customer->category_id }}" 
                                                    data-lead-quality="{{ $customer->lead_quality_id }}">
                                                    <i class="fas fa-phone-alt"></i> Call Student
                                                </button>
                                            </div>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="7" class="text-center py-5 text-muted">
                                            <i class="fas fa-check-circle text-success fs-1 mb-3 d-block"></i>
                                            <h5 class="fw-bold text-dark">You're all caught up!</h5>
                                            <p class="mb-0">There are no leads pending in your personal call queue right now.</p>
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>

                <!-- TAB 2: Team Assigned Leads -->
                @if(count($staffs) > 0)
                <div class="tab-pane fade" id="delegated-queue-tab">
                    <div class="table-responsive">
                        <table class="table lead-table align-middle">
                            <thead>
                                <tr>
                                    <th>#</th>
                                    <th>Student Lead</th>
                                    <th>Assigned Staff</th>
                                    <th>Call Status</th>
                                    <th>Assigned On</th>
                                    <th class="text-end pe-4">Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($delegatedLeads as $assignment)
                                    @php 
                                        $customer = $assignment->customer;
                                        $leadType = $assignment->lead_type ?? 'new';
                                        
                                        $statusTag = '<span class="status-tag tag-new"><i class="fas fa-sparkles"></i> New Lead</span>';
                                        if ($leadType === 'overdue') {
                                            $statusTag = '<span class="status-tag tag-overdue"><i class="fas fa-exclamation-circle"></i> Overdue</span>';
                                        } elseif ($leadType === 'due_today') {
                                            $statusTag = '<span class="status-tag tag-due"><i class="fas fa-clock"></i> Due Today</span>';
                                        } elseif ($leadType === 'reassigned') {
                                            $statusTag = '<span class="status-tag tag-reassigned"><i class="fas fa-random"></i> Reassigned Lead</span>';
                                        }
                                    @endphp
                                    @if($customer)
                                    <tr>
                                        <td class="text-muted fw-bold small" style="width: 40px;">
                                            #{{ $loop->iteration }}
                                        </td>
                                        <td>
                                            <div class="fw-bold text-dark fs-6">{{ $customer->name }} <span class="badge bg-light text-secondary border fw-normal ms-1" style="font-size: 0.7rem;">ID #{{ $customer->id }}</span></div>
                                            <div class="text-muted small">{{ $customer->city ?? 'Unknown' }}</div>
                                        </td>
                                        <td>
                                            <div class="fw-bold text-primary">{{ $assignment->staff->name ?? 'Unknown' }}</div>
                                        </td>
                                        <td>
                                            {!! $statusTag !!}
                                        </td>
                                        <td class="text-muted small">
                                            {{ \Carbon\Carbon::parse($assignment->updated_at)->format('d M Y, h:i A') }}
                                        </td>
                                        <td class="text-end pe-4">
                                            <button type="button" class="btn btn-reassign-action open-reassign-modal" data-id="{{ $customer->id }}" data-name="{{ $customer->name }}">
                                                <i class="fas fa-user-edit me-1"></i> Reassign Lead
                                            </button>
                                        </td>
                                    </tr>
                                    @endif
                                @empty
                                    <tr>
                                        <td colspan="6" class="text-center py-5 text-muted">
                                            <i class="fas fa-users text-primary fs-1 mb-3 d-block"></i>
                                            <h5 class="fw-bold text-dark">No Delegated Leads</h5>
                                            <p class="mb-0">You have not assigned any leads to your team in this date range.</p>
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
                @endif

                <!-- TAB 3: Completed Calls History -->
                <div class="tab-pane fade" id="history-queue-tab">
                    <div class="table-responsive">
                        <table class="table lead-table align-middle">
                            <thead>
                                <tr>
                                    <th>#</th>
                                    <th>Student Lead</th>
                                    <th>Call Result</th>
                                    <th>Called By</th>
                                    <th>Assigned By</th>
                                    <th>Call Timestamp</th>
                                    <th class="text-end pe-4">Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($workedHistory as $hItem)
                                    @php 
                                        $customer = $hItem->customer;
                                        $maskedPhone = $customer ? substr($customer->phone, 0, 2) . 'XXXXX' . substr($customer->phone, -3) : '';
                                        $isUnlocked = ($customer && isset($unlocked_lead_id) && $unlocked_lead_id == $customer->id);
                                    @endphp
                                    @if($customer)
                                    <tr>
                                        <td class="text-muted fw-bold small" style="width: 40px;">
                                            #{{ $loop->iteration }}
                                        </td>
                                        <td>
                                            <div class="fw-bold text-dark fs-6">{{ $customer->name }} <span class="badge bg-light text-secondary border fw-normal ms-1" style="font-size: 0.7rem;">ID #{{ $customer->id }}</span></div>
                                            <div class="text-muted small">{{ $customer->city ?? 'Unknown' }}</div>
                                        </td>
                                        <td>
                                            <span class="status-tag tag-completed">
                                                <i class="fas fa-check-circle"></i> {{ $hItem->calling_status->name ?? 'Contacted' }}
                                            </span>
                                        </td>
                                        <td class="fw-bold text-dark">
                                            {{ $hItem->staff->name ?? 'System' }}
                                        </td>
                                        <td>
                                            <div class="fw-semibold text-dark small">{{ $historyAssignmentsLookup[$customer->id]->assigner->name ?? '—' }}</div>
                                            @if(isset($historyAssignmentsLookup[$customer->id]->updated_at))
                                                <div class="text-muted small" style="font-size: 0.72rem;">
                                                    <i class="far fa-clock me-0.5"></i> {{ \Carbon\Carbon::parse($historyAssignmentsLookup[$customer->id]->updated_at)->format('d M, Y h:i A') }}
                                                </div>
                                            @endif
                                        </td>
                                        <td class="text-muted small">
                                            <i class="far fa-clock me-1"></i> {{ \Carbon\Carbon::parse($hItem->created_at)->format('d M Y, h:i A') }}
                                        </td>
                                        <td class="text-end pe-4">
                                            <div class="d-flex align-items-center justify-content-end gap-2">
                                                @if(isset($historyAssignmentsLookup[$customer->id]) && ($historyAssignmentsLookup[$customer->id]->assigned_by == auth()->id() || auth()->user()->is_admin || auth()->user()->hasRole('superadmin')))
                                                    <button type="button" class="btn btn-reassign-action open-reassign-modal" data-id="{{ $customer->id }}" data-name="{{ $customer->name }}" title="Reassign">
                                                        <i class="fas fa-user-edit"></i>
                                                    </button>
                                                @endif
                                                <button type="button" class="btn btn-call-action open-calling-modal call-btn-{{ $customer->id }}" 
                                                    data-id="{{ $customer->id }}" 
                                                    data-name="{{ $customer->name }}" 
                                                    data-phone="{{ $isUnlocked ? $customer->phone : $maskedPhone }}" 
                                                    data-category="{{ $customer->category_id }}" 
                                                    data-lead-quality="{{ $customer->lead_quality_id }}">
                                                    <i class="fas fa-phone-alt"></i> Call Again
                                                </button>
                                            </div>
                                        </td>
                                    </tr>
                                    @endif
                                @empty
                                    <tr>
                                        <td colspan="7" class="text-center py-5 text-muted">
                                            <i class="fas fa-history text-secondary fs-1 mb-3 d-block"></i>
                                            <h5 class="fw-bold text-dark">No History Recorded</h5>
                                            <p class="mb-0">No calls have been logged in the selected date range.</p>
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>

            </div>
        </div>

    </div>
</div>

<!-- ========================================== -->
<!-- MODAL: Reassign Lead                       -->
<!-- ========================================== -->
<div class="modal fade" id="reassignModal">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 rounded-4 shadow-lg">
            <div class="modal-header border-0 pb-0">
                <h5 class="fw-bold text-dark"><i class="fas fa-user-tag text-primary me-2"></i>Reassign Lead</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form id="reassignForm">
                @csrf
                <div class="modal-body p-4">
                    <input type="hidden" name="customer_id" id="reassign_customer_id">
                    <div class="mb-3">
                        <label class="filter-label">Student Name</label>
                        <input type="text" class="form-control filter-input bg-light" id="reassign_customer_name" readonly>
                    </div>
                    <div class="mb-3">
                        <label class="filter-label">Assign To Staff <span class="text-danger">*</span></label>
                        <select name="staff_id" class="form-select filter-select" required>
                            <option value="">Choose Staff Member</option>
                            @foreach($staffs as $staff)
                                <option value="{{ $staff->id }}">{{ $staff->name }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
                <div class="modal-footer border-0 pt-0">
                    <button type="button" class="btn btn-light rounded-pill px-4" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary rounded-pill px-5 fw-bold shadow-sm">Confirm Reassignment</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- ========================================== -->
<!-- MODAL: Calling Cockpit (Call & Update)    -->
<!-- ========================================== -->
<div class="modal fade" id="callModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered cockpit-dialog">
        <div class="modal-content cockpit-modal-content border-0 shadow-2xl">
            
            <!-- Modal Header Bar (Integrated Lead Identity Banner) -->
            <div class="modal-header cockpit-header bg-white px-4 py-3 border-bottom d-flex align-items-center justify-content-between">
                <div id="callModalHeaderContent" class="flex-grow-1">
                    <div class="d-flex align-items-center gap-3">
                        <div class="d-flex align-items-center justify-content-center fw-bold rounded-4 text-white flex-shrink-0" style="width: 48px; height: 48px; font-size: 1.15rem; background: linear-gradient(135deg, #3b82f6 0%, #1d4ed8 100%);">
                            <i class="fas fa-headset"></i>
                        </div>
                        <div>
                            <h5 class="fw-bold mb-0 text-dark">Update Calling Disposition</h5>
                            <span class="text-muted small">Record student interactions and update CRM profile</span>
                        </div>
                    </div>
                </div>
                <button type="button" class="btn-close cockpit-close-btn ms-3" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>

            <!-- Modal Body: 2 Balanced Viewport Panes -->
            <div class="modal-body cockpit-body p-0">
                <div class="cockpit-grid">
                    
                    <!-- LEFT PANE: Action Console Form -->
                    <div class="cockpit-left-pane">
                        <div class="cockpit-pane-header px-4 py-3 border-bottom bg-light d-flex justify-content-between align-items-center">
                            <div class="d-flex align-items-center gap-2">
                                <i class="fas fa-edit text-primary"></i>
                                <span class="fw-bold text-dark fs-6">Lead Disposition Console</span>
                            </div>
                            <span class="badge bg-white text-secondary border px-2.5 py-1 small fw-semibold">Active Call</span>
                        </div>

                        <form id="callForm" enctype="multipart/form-data" class="d-flex flex-column flex-grow-1 overflow-hidden" style="min-height: 0;">
                            @csrf
                            <input type="hidden" name="group_id" value="{{ request('group', 1) }}">
                            <input type="hidden" id="customer_id" name="customer_id">
                            <input type="hidden" id="user_phone" name="user_phone">
                            <input type="hidden" id="category_val" name="category">

                            <div class="cockpit-form-scroll flex-grow-1 p-3.5">
                                
                                <!-- Core Section: Contact & Status (Spacious 2-column + Full Width Status) -->
                                <div class="mb-3">
                                    <div class="row g-3">
                                        <div class="col-md-6">
                                            <label class="cockpit-label">Student Name</label>
                                            <div class="cockpit-input-group">
                                                <!-- <i class="far fa-user cockpit-input-icon"></i> -->
                                                <input type="text" class="form-control cockpit-input cockpit-input-with-icon bg-light fw-bold text-dark" name="name" id="user_name" readonly>
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <label class="cockpit-label">Student Email</label>
                                            <div class="cockpit-input-group">
                                                <!-- <i class="far fa-envelope cockpit-input-icon"></i> -->
                                                <input type="text" class="form-control cockpit-input cockpit-input-with-icon" name="email" id="user_email" placeholder="student@example.com">
                                            </div>
                                        </div>
                                        <div class="col-12">
                                            <label class="cockpit-label text-primary"><i class="fas fa-phone-alt me-1"></i> Call Status <span class="text-danger">*</span></label>
                                            <select name="status_id" class="form-select cockpit-select custom-select2 fw-bold" id="status_id" required>
                                                <option value="" selected disabled>Select Call Status</option>
                                                @foreach($statuses as $status)
                                                    <option value="{{ $status->id }}" 
                                                        data-action="{{ $status->calling_action_id }}" 
                                                        data-more-details="{{ $status->is_more_details }}" 
                                                        data-current-academic-details="{{ $status->current_academic_details ?? 'no' }}" 
                                                        data-date-require="{{ $status->date_require }}" 
                                                        data-comment-require="{{ $status->comment_require ?? 'no' }}">
                                                        {{ $status->name }}
                                                    </option>
                                                @endforeach
                                            </select>
                                        </div>
                                    </div>
                                </div>

                                <!-- Section 2: Current Academic Details (Dynamic Spacious Sub-Card) -->
                                <div id="current-academic-details-container" class="cockpit-subcard" style="display:none;">
                                    <div class="cockpit-subcard-title">
                                        <i class="fas fa-graduation-cap text-info me-2"></i> Current Academic Background
                                    </div>
                                    <div class="row g-3">
                                        <div class="col-md-6">
                                            <label class="cockpit-label">Current Course</label>
                                            <select name="current_course" id="current_course" class="form-select cockpit-select custom-select2">
                                                <option value="">Select or Type Course</option>
                                                @if(isset($courses))
                                                    @foreach($courses as $course)
                                                        <option value="{{ $course->id }}">{{ $course->name }}</option>
                                                    @endforeach
                                                @endif
                                            </select>
                                        </div>
                                        <div class="col-md-6">
                                            <label class="cockpit-label">Passing Year</label>
                                            <select name="current_session" id="current_session" class="form-select cockpit-select custom-select2">
                                                <option value="">Select Passing Year</option>
                                                @if(isset($sessions))
                                                    @foreach($sessions as $session)
                                                        <option value="{{ $session->id }}">{{ $session->name }}</option>
                                                    @endforeach
                                                @endif
                                            </select>
                                        </div>
                                        <div class="col-md-6">
                                            <label class="cockpit-label">Current University</label>
                                            <select name="current_university" id="current_university" class="form-select cockpit-select custom-select2">
                                                <option value="">Select or Type University</option>
                                                @if(isset($universities))
                                                    @foreach($universities as $uni)
                                                        <option value="{{ $uni->id }}">{{ $uni->name }}</option>
                                                    @endforeach
                                                @endif
                                            </select>
                                        </div>
                                        <div class="col-md-6">
                                            <label class="cockpit-label">Current Program Mode</label>
                                            <select name="current_program_mode" id="current_program_mode" class="form-select cockpit-select custom-select2">
                                                <option value="">Select Mode</option>
                                                @if(isset($program_types))
                                                    @foreach($program_types as $pt)
                                                        <option value="{{ $pt->title }}">{{ $pt->title }}</option>
                                                    @endforeach
                                                @endif
                                            </select>
                                        </div>
                                    </div>
                                </div>

                                <!-- Section 3: Interested Program / Academic Preferences (Dynamic Spacious Sub-Card) -->
                                <div id="more-details-container" class="cockpit-subcard" style="display:none;">
                                    <div class="cockpit-subcard-title">
                                        <i class="fas fa-bullseye text-indigo me-2"></i> Target Program of Interest
                                    </div>
                                    <div class="row g-3">
                                        <div class="col-md-6">
                                            <label class="cockpit-label">Program Level</label>
                                            <select name="program_level_id" id="program_level_id" class="form-select cockpit-select custom-select2">
                                                <option value="">Select Program Level</option>
                                                <option value="Not decided yet">Not decided yet</option>
                                                @if(isset($program_levels))
                                                    @foreach($program_levels as $pl)
                                                        <option value="{{ $pl->id }}">{{ $pl->title }}</option>
                                                    @endforeach
                                                @endif
                                            </select>
                                        </div>
                                        <div class="col-md-6" id="course_container">
                                            <label class="cockpit-label" id="course_label">Interested Course</label>
                                            <select name="course_input" id="course_input" class="form-select cockpit-select custom-select2">
                                                <option value="">Select Course</option>
                                                <option value="Not decided yet">Not decided yet</option>
                                                @foreach($courses as $course)
                                                    <option value="{{ $course->id }}">{{ $course->name }}</option>
                                                @endforeach
                                            </select>
                                        </div>
                                        <div class="col-md-6" id="university_container">
                                            <label class="cockpit-label" id="university_label">University / Institute</label>
                                            <select name="university_input" id="university_input" class="form-select cockpit-select custom-select2">
                                                <option value="">Select University</option>
                                                <option value="Not decided yet">Not decided yet</option>
                                                @foreach($universities as $uni)
                                                    @php
                                                        $types = [];
                                                        $orgType = is_array($uni->campus_type_new_id) ? $uni->campus_type_new_id : json_decode($uni->campus_type_new_id, true) ?? [$uni->campus_type_new_id];
                                                        if(is_array($orgType)) {
                                                            $types = array_merge($types, $orgType);
                                                        }
                                                        if ($uni->campuses) {
                                                            foreach($uni->campuses as $campus) {
                                                                $campType = is_array($campus->campus_type_new_id) ? $campus->campus_type_new_id : json_decode($campus->campus_type_new_id, true) ?? [$campus->campus_type_new_id];
                                                                if(is_array($campType)) {
                                                                    $types = array_merge($types, $campType);
                                                                }
                                                            }
                                                        }
                                                        $types = array_values(array_unique(array_filter($types)));
                                                    @endphp
                                                    <option value="{{ $uni->id }}" data-type-id="{{ $uni->organisation_type_id }}" data-school-type-id="{{ json_encode($types) }}">{{ $uni->name }}</option>
                                                @endforeach
                                            </select>
                                        </div>
                                        <div class="col-md-6">
                                            <label class="cockpit-label">Session</label>
                                            <select name="session" id="session_input" class="form-select cockpit-select custom-select2">
                                                <option value="">Select Session</option>
                                                @if(isset($sessions))
                                                    @foreach($sessions as $session)
                                                        <option value="{{ $session->id }}">{{ $session->name }}</option>
                                                    @endforeach
                                                @endif
                                            </select>
                                        </div>
                                        <div class="col-md-6" id="course_type_container">
                                            <label class="cockpit-label">Program Mode</label>
                                            <select name="course_type" id="course_type" class="form-select cockpit-select custom-select2">
                                                <option value="">Select Mode</option>
                                                <option value="Not decided yet">Not decided yet</option>
                                                @if(isset($program_types))
                                                    @foreach($program_types as $pt)
                                                        <option value="{{ $pt->title }}" data-db-id="{{ $pt->id }}">{{ $pt->title }}</option>
                                                    @endforeach
                                                @endif
                                            </select>
                                        </div>
                                        <div class="col-md-6" id="school_type_container" style="display:none;">
                                            <label class="cockpit-label">School Type</label>
                                            <select name="school_type" id="school_type" class="form-select cockpit-select custom-select2">
                                                <option value="">Select School Type</option>
                                                @if(isset($school_types))
                                                    @foreach($school_types as $st)
                                                        <option value="{{ $st->id }}">{{ $st->title }}</option>
                                                    @endforeach
                                                @endif
                                            </select>
                                        </div>
                                    </div>
                                </div>

                                <!-- Section 4: Action & Follow-up Plan (Spacious Sub-Card) -->
                                <div class="cockpit-subcard">
                                    <div class="cockpit-subcard-title">
                                        <i class="fas fa-tasks text-warning me-2"></i> Next Action & Follow-up
                                    </div>
                                    <div class="row g-3">
                                        <div class="col-md-6">
                                            <label class="cockpit-label">Action Taken</label>
                                            <select name="action_id" id="action_id" class="form-select cockpit-select custom-select2">
                                                <option value="">Select Action</option>
                                                @foreach($actions as $action)
                                                    <option value="{{ $action->id }}">{{ $action->name }}</option>
                                                @endforeach
                                            </select>
                                        </div>

                                        <div class="col-md-6">
                                            <label class="cockpit-label">Lead Quality</label>
                                            <select name="lead_quality_id" id="lead_quality_id" class="form-select cockpit-select custom-select2">
                                                <option value="">Select Lead Quality</option>
                                                @if(isset($lead_qualities))
                                                    @foreach($lead_qualities as $lq)
                                                        <option value="{{ $lq->id }}">{{ $lq->name }}</option>
                                                    @endforeach
                                                @endif
                                            </select>
                                        </div>

                                        <div class="col-12" id="date-field" style="display:none;">
                                            <div class="p-3 bg-danger bg-opacity-10 border border-danger border-opacity-25 rounded-3">
                                                <label class="cockpit-label text-danger fw-bold"><i class="far fa-calendar-alt me-1"></i> Reminder / Next Call Date <span class="text-danger">*</span></label>
                                                <input type="date" name="next_call_date" class="form-control cockpit-input border-danger" id="call_date">
                                            </div>
                                        </div>

                                        <!-- Video Meeting Container -->
                                        <div id="video-meeting-container" class="col-12" style="display:none;">
                                            <div class="p-3 bg-light rounded-3 border border-primary border-opacity-25">
                                                <h6 class="fw-bold text-primary mb-3" style="font-size: 0.85rem;"><i class="fas fa-video me-1"></i> Google Meet Scheduler</h6>
                                                <div class="row g-3">
                                                    <div class="col-md-6">
                                                        <label class="cockpit-label">Meeting Date</label>
                                                        <input type="date" name="meeting_date" id="meeting_date" class="form-control cockpit-input">
                                                    </div>
                                                    <div class="col-md-6">
                                                        <label class="cockpit-label">Time Slot</label>
                                                        <input type="time" name="time_slot" id="time_slot" class="form-control cockpit-input">
                                                    </div>
                                                    <div class="col-md-6">
                                                        <label class="cockpit-label">Meet Link</label>
                                                        <input type="url" name="meeting_link" id="meeting_link" class="form-control cockpit-input" placeholder="https://meet.google.com/...">
                                                    </div>
                                                    <div class="col-md-6">
                                                        <label class="cockpit-label">Staff Host</label>
                                                        <select name="assign_to_staff_id" id="assign_to_staff_id" class="form-select cockpit-select custom-select2">
                                                            <option value="">Select Staff</option>
                                                            @foreach($staffs as $staff)
                                                                <option value="{{ $staff->id }}">{{ $staff->name }}</option>
                                                            @endforeach
                                                        </select>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>

                                        <div class="col-12">
                                            <label class="cockpit-label">Conversation Remarks & Notes</label>
                                            <textarea id="message" name="remark" class="form-control cockpit-input" rows="3" placeholder="Enter notes from the call, student responses, details..."></textarea>
                                        </div>

                                        <div class="col-12">
                                            <div class="form-check form-switch p-2.5 bg-white rounded-3 border d-flex align-items-center justify-content-between ps-3 pe-3">
                                                <label class="form-check-label fw-bold text-success mb-0 d-flex align-items-center cursor-pointer" for="is_whatsapp_message">
                                                    <i class="fab fa-whatsapp fs-5 me-2"></i> Send Instant WhatsApp Message
                                                </label>
                                                <input class="form-check-input ms-0 cursor-pointer" type="checkbox" id="is_whatsapp_message" name="is_whatsapp_message" value="1" style="width: 2.2em; height: 1.2em;">
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <!-- Section 5: WhatsApp Outreach -->
                                <div id="whatsapp_fields" class="cockpit-subcard" style="display:none; background: #f0fdf4 !important; border-color: #bbf7d0 !important;">
                                    <div class="cockpit-subcard-title text-success border-success border-opacity-25">
                                        <i class="fab fa-whatsapp text-success me-2"></i> WhatsApp Message Composer
                                    </div>
                                    <div class="row g-3">
                                        <div class="col-md-6">
                                            <label class="cockpit-label">Template</label>
                                            <select name="whatsapp_template_id" class="form-select cockpit-select custom-select2" id="whatsapp_template_id">
                                                <option value="">Select Template</option>
                                                @foreach($templates as $template)
                                                    <option value="{{ $template->id }}" data-caption="{{ $template->caption }}" data-message="{{ $template->message }}">{{ $template->name }}</option>
                                                @endforeach
                                            </select>
                                        </div>
                                        <div class="col-md-6">
                                            <label class="cockpit-label">Caption</label>
                                            <input type="text" class="form-control cockpit-input" name="caption" id="caption">
                                        </div>
                                        <div class="col-12">
                                            <label class="cockpit-label">Attach Image</label>
                                            <input type="file" class="form-control cockpit-input" name="image_whatsapp" accept=".jpg, .jpeg, .png">
                                        </div>
                                        <div class="col-12">
                                            <label class="cockpit-label">Message Text</label>
                                            <textarea name="whatsapp_message" class="form-control cockpit-input" id="message-editor" rows="3" placeholder="Enter message"></textarea>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- DOCKED STICKY FOOTER -->
                            <div class="cockpit-sticky-footer px-4 py-3 bg-white border-top d-flex justify-content-between align-items-center">
                                <div class="text-muted small d-flex align-items-center gap-1.5">
                                    <i class="fas fa-shield-alt text-primary"></i>
                                    <span>Ready to save disposition</span>
                                </div>
                                <div class="d-flex gap-2">
                                    <button type="button" class="btn btn-light rounded-pill px-4 fw-semibold border text-secondary" data-bs-dismiss="modal">Cancel</button>
                                    <button type="submit" class="btn btn-primary rounded-pill px-5 fw-bold shadow-sm" id="save-log-btn">
                                        <i class="fas fa-check-circle me-1.5"></i> Save & Update
                                    </button>
                                </div>
                            </div>
                        </form>
                    </div>

                    <!-- RIGHT PANE: Timeline & History -->
                    <div class="cockpit-right-pane bg-white border rounded-3 d-flex flex-column">
                        <div class="cockpit-timeline-header px-3.5 py-3 border-bottom bg-light d-flex justify-content-between align-items-center">
                            <div class="d-flex align-items-center gap-2">
                                <i class="fas fa-history text-primary"></i>
                                <span class="fw-bold text-dark fs-6">Interaction Timeline</span>
                            </div>
                            <span class="badge bg-white text-secondary border px-2.5 py-1 small fw-semibold" id="history-badge">History</span>
                        </div>
                        <div id="history-content" class="cockpit-timeline-body flex-grow-1 p-3.5 overflow-auto">
                            <div class="text-center text-muted py-5">
                                <i class="fas fa-spinner fa-spin fs-4 mb-2 text-primary"></i>
                                <div>Loading interaction timeline...</div>
                            </div>
                        </div>
                    </div>

                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('js')
<script src="https://cdn.datatables.net/1.13.7/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.13.7/js/dataTables.bootstrap5.min.js"></script>
<script src="https://cdn.ckeditor.com/4.22.1/standard/ckeditor.js"></script>
<script>
    $(document).ready(function() {
        function setSelect2Value(selectId, value) {
            if (value === undefined || value === null || value === '') {
                $('#' + selectId).val('').trigger('change');
                return;
            }
            let selectEl = $('#' + selectId);
            let optionByVal = selectEl.find("option[value='" + value + "']");
            if (optionByVal.length > 0) {
                selectEl.val(value).trigger('change');
                return;
            }

            let matchedValue = null;
            selectEl.find("option").each(function() {
                let optText = $(this).text().trim().toLowerCase();
                let searchVal = String(value).trim().toLowerCase();
                if (optText === searchVal || optText === searchVal + ' (default)' || optText.indexOf(searchVal) === 0) {
                    matchedValue = $(this).val();
                    return false;
                }
            });

            if (matchedValue !== null) {
                selectEl.val(matchedValue).trigger('change');
            } else {
                let newOption = new Option(value, value, true, true);
                selectEl.append(newOption).trigger('change');
            }
        }
        window.pendingAutofill = null;

        $('#restartBtn').on('click', function() {
            let cat = $('#categoryFilter').val();
            let group = '{{ request('group', 1) }}';
            let baseUrl = '{{ route('admin.students-crm.calling-module.restart') }}';
            
            if(!cat) {
                Swal.fire('Warning', 'Please select a category first before clicking Re-Start.', 'warning');
                return;
            }
            
            var params = new URLSearchParams();
            params.set('group', group);
            params.set('category', cat);
            
            window.location.href = baseUrl + '?' + params.toString();
        });

        $(document).on('click', '.unlock-phone-btn', function() {
            let id = $(this).data('id');
            let btn = $(this);
            
            $.ajax({
                url: "{{ route('admin.students-crm.calling-dashboard.unlock') }}",
                type: 'POST',
                data: {
                    customer_id: id,
                    _token: "{{ csrf_token() }}"
                },
                success: function(res) {
                    if (res.status == 1) {
                        $('.phone-container-' + id).html('<span class="badge bg-success bg-opacity-10 text-success border border-success border-opacity-25 px-2.5 py-1.5 fw-bold"><i class="fas fa-phone-alt me-1"></i> ' + res.phone + '</span>');
                        $('.call-btn-' + id).attr('data-phone', res.phone);
                        $('.call-btn-' + id).data('phone', res.phone);
                    } else {
                        Swal.fire('Locked', res.message, 'warning');
                    }
                },
                error: function() {
                    Swal.fire('Error', 'Something went wrong', 'error');
                }
            });
        });

        $(document).on('click', '.open-reassign-modal', function() {
            let id = $(this).data('id');
            let name = $(this).data('name');

            $('#reassign_customer_id').val(id);
            $('#reassign_customer_name').val(name);
            $('#reassignModal').modal('show');
        });

        $('#reassignForm').on('submit', function(e) {
            e.preventDefault();
            let btn = $(this).find('button[type="submit"]');
            let originalText = btn.text();
            btn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i> Reassigning...');

            $.ajax({
                url: "{{ route('admin.students-crm.calling-dashboard.reassign') }}",
                type: 'POST',
                data: $(this).serialize(),
                success: function(res) {
                    btn.prop('disabled', false).html(originalText);
                    if (res.status == 1) {
                        $('#reassignModal').modal('hide');
                        Swal.fire('Success', res.message, 'success').then(() => {
                            window.location.reload();
                        });
                    } else {
                        Swal.fire('Error', res.message, 'error');
                    }
                },
                error: function(err) {
                    btn.prop('disabled', false).html(originalText);
                    Swal.fire('Error', 'Something went wrong', 'error');
                }
            });
        });

        $(document).on('click', '.open-calling-modal', function() {
            let id = $(this).data('id');
            let name = $(this).data('name');
            let phone = $(this).data('phone');
            let cat = $(this).data('category');
            let lq = $(this).data('lead-quality');

            // Reset form fields and select2 dropdowns
            $('#callForm')[0].reset();
            $('#user_email').val('');
            $('#university_input').val(null).trigger('change');
            $('#course_input').val(null).trigger('change');
            $('#current_course').val(null).trigger('change');
            $('#current_session').val(null).trigger('change');
            $('#current_university').val(null).trigger('change');
            $('#current_program_mode').val(null).trigger('change');
            $('#program_level_id').val(null).trigger('change');
            $('#school_type').val(null).trigger('change');
            $('#course_type').val(null).trigger('change');
            $('#session_input').val(null).trigger('change');
            $('#more-details-container').show();
            $('#current-academic-details-container').show();

            $('#customer_id').val(id);
            $('#user_name').val(name);
            $('#user_phone').val(phone);
            $('#category_val').val(cat);
            if(lq) {
                $('#lead_quality_id').val(lq);
            } else {
                $('#lead_quality_id').val('');
            }
            
            $('#save-log-btn').show();
            
            // Fetch History & Header Details
            $('#history-content').html('<div class="text-center text-muted py-5"><i class="fas fa-spinner fa-spin fs-4 mb-2 text-primary"></i><div>Loading timeline history...</div></div>');
            $('#callModalHeaderContent').html('<div class="d-flex align-items-center gap-2 text-muted py-1"><i class="fas fa-spinner fa-spin text-primary"></i> <span>Loading student profile...</span></div>');
            $.ajax({
                url: "{{ url('admin/students-crm/calling-dashboard/customer-history') }}/" + id,
                type: 'GET',
                success: function(res) {
                    $('#history-content').html(res.html);
                    if(res.headerHtml) {
                        $('#callModalHeaderContent').html(res.headerHtml);
                    }

                    // Auto-fill Current Academic Details
                    if (res.customer) {
                        $('#user_email').val(res.customer.email || '');
                        setSelect2Value('current_course', res.customer.current_course);
                        setSelect2Value('current_session', res.customer.current_session);
                        setSelect2Value('current_university', res.customer.current_university);
                        setSelect2Value('current_program_mode', res.customer.current_program_mode);

                        $('#current-academic-details-container').show();

                        // Auto-fill Program of Interest
                        $('#more-details-container').show();
                        
                        window.pendingAutofill = {
                            school_type: res.customer.school_type,
                            course_input: res.customer.course_input,
                            course_type: res.customer.course_type,
                            university_input: res.customer.university_input,
                            session_id: res.customer.session_id
                        };

                        setSelect2Value('session_input', res.customer.session_id);
                        setSelect2Value('program_level_id', res.customer.program_level_id);

                        if (!res.customer.program_level_id) {
                            if (res.customer.university_input) {
                                setSelect2Value('university_input', res.customer.university_input);
                            }
                            if (res.customer.course_input) {
                                setSelect2Value('course_input', res.customer.course_input);
                            }
                            if (res.customer.course_type) {
                                setSelect2Value('course_type', res.customer.course_type);
                            }
                        }
                    } else {
                        $('#current-academic-details-container').show();
                        $('#more-details-container').show();
                        window.pendingAutofill = null;
                    }
                },
                error: function() {
                    $('#history-content').html('<div class="text-center text-danger py-4"><i class="fas fa-exclamation-triangle fs-3 mb-2 d-block"></i>Failed to load interaction timeline</div>');
                    $('#callModalHeaderContent').html('<div class="d-flex align-items-center gap-2"><i class="fas fa-headset text-primary me-1"></i><h5 class="fw-bold mb-0 text-dark">Update Calling Disposition</h5></div>');
                }
            });

            $('#callModal').modal('show');
        });

        $('#callForm').on('submit', function(e) {
            e.preventDefault();
            
            // Sync CKEditor
            if (CKEDITOR.instances['message-editor']) {
                CKEDITOR.instances['message-editor'].updateElement();
            }

            let btn = $('#save-log-btn');
            let origHtml = btn.html();
            btn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin me-1"></i> Saving...');

            $.ajax({
                url: "{{ route('admin.students-crm.calling-module.store') }}",
                type: "POST",
                data: new FormData(this),
                processData: false,
                contentType: false,
                success: function(res) {
                    btn.prop('disabled', false).html(origHtml);
                    if(res.status == 1) {
                        $('#callModal').modal('hide');
                        $('#callForm')[0].reset();
                        $('#user_email').val('');
                        $('#university_input').val(null).trigger('change');
                        $('#course_input').val(null).trigger('change');
                        $('#assign_to_staff_id').val(null).trigger('change');
                        $('#current_course').val(null).trigger('change');
                        $('#current_session').val(null).trigger('change');
                        $('#current_university').val(null).trigger('change');
                        $('#current_program_mode').val(null).trigger('change');
                        $('#more-details-container').hide();
                        $('#current-academic-details-container').hide();
                        $('#video-meeting-container').hide();
                        $('#date-field').hide();
                        $('#call_date').prop('required', false);
                        Swal.fire('Interaction Logged', res.message, 'success').then(() => {
                            window.location.reload();
                        });
                    } else {
                        Swal.fire('Error', res.message, 'error');
                    }
                },
                error: function(err) {
                    btn.prop('disabled', false).html(origHtml);
                    Swal.fire('Error', 'Something went wrong while saving.', 'error');
                }
            });
        });

        // WhatsApp Message Toggle
        $('#is_whatsapp_message').on('change', function() {
            if(this.checked) {
                $('#whatsapp_fields').slideDown(200);
            } else {
                $('#whatsapp_fields').slideUp(200);
                $('#caption').val('');
                if (CKEDITOR.instances['message-editor']) {
                    CKEDITOR.instances['message-editor'].setData('');
                }
            }
        });

        // CKEditor Init
        if (typeof CKEDITOR !== 'undefined') {
            CKEDITOR.config.versionCheck = false;
            CKEDITOR.replace('message-editor', {
                versionCheck: false
            });
        }

        $('#whatsapp_template_id').on('change', function() {
            let selected = $(this).find('option:selected');
            let caption = selected.data('caption') || '';
            let msg = selected.data('message') || '';
            
            $('#caption').val(caption);
            if (CKEDITOR.instances['message-editor']) {
                CKEDITOR.instances['message-editor'].setData(msg);
            }
        });

        $('#status_id').on('change', function() {
            let selected = $(this).find('option:selected');
            let actionId = selected.data('action');
            let moreDetails = selected.data('more-details');
            let dateRequire = selected.data('date-require');
            let commentRequire = selected.data('comment-require');
            let currentAcademicDetails = selected.data('current-academic-details');
            
            if(actionId) {
                $('#action_id').val(actionId).trigger('change');
            } else {
                $('#action_id').val('').trigger('change');
            }
            
            if(moreDetails === 'yes') {
                $('#more-details-container').show();
            } else {
                $('#more-details-container').hide();
            }

            if(currentAcademicDetails === 'yes') {
                $('#current-academic-details-container').show();
            } else {
                $('#current-academic-details-container').hide();
            }
            
            if(dateRequire === 'yes') {
                $('#date-field').show();
                $('#call_date').prop('required', true);
            } else {
                $('#date-field').hide();
                $('#call_date').prop('required', false);
            }

            if(commentRequire === 'yes') {
                $('#message').prop('required', true);
            } else {
                $('#message').prop('required', false);
            }
        });
        
        $('#university_input').select2({
            tags: true,
            dropdownParent: $('#callModal .modal-content'),
            placeholder: "Select or Type University",
            allowClear: true,
            width: '100%'
        });

        $('#course_input').select2({
            tags: true,
            dropdownParent: $('#callModal .modal-content'),
            placeholder: "Select or Type Course",
            allowClear: true,
            width: '100%'
        });

        $('#program_level_id').select2({
            tags: true,
            dropdownParent: $('#callModal .modal-content'),
            placeholder: "Select or Type Program Level",
            allowClear: true,
            width: '100%'
        });

        $('#course_type').select2({
            tags: true,
            dropdownParent: $('#callModal .modal-content'),
            placeholder: "Select or Type Course Type",
            allowClear: true,
            width: '100%'
        });

        $('#current_course').select2({
            tags: true,
            dropdownParent: $('#callModal .modal-content'),
            placeholder: "Select or Type Course",
            allowClear: true,
            width: '100%'
        });

        $('#current_session').select2({
            tags: true,
            dropdownParent: $('#callModal .modal-content'),
            placeholder: "Select Session",
            allowClear: true,
            width: '100%'
        });

        $('#current_university').select2({
            tags: true,
            dropdownParent: $('#callModal .modal-content'),
            placeholder: "Select or Type University",
            allowClear: true,
            width: '100%'
        });

        $('#current_program_mode').select2({
            tags: true,
            dropdownParent: $('#callModal .modal-content'),
            placeholder: "Select Program Mode",
            allowClear: true,
            width: '100%'
        });

        $('#assign_to_staff_id').select2({
            dropdownParent: $('#callModal .modal-content'),
            placeholder: "Select Staff",
            allowClear: true,
            width: '100%'
        });

        $('#action_id').select2({
            dropdownParent: $('#callModal .modal-content'),
            width: '100%'
        });

        $('#lead_quality_id').select2({
            dropdownParent: $('#callModal .modal-content'),
            width: '100%'
        });

        $('#status_id').select2({
            dropdownParent: $('#callModal .modal-content'),
            width: '100%'
        });

        $('#whatsapp_template_id').select2({
            dropdownParent: $('#callModal .modal-content'),
            width: '100%'
        });

        $('#action_id').on('change', function() {
            let actionText = $(this).find('option:selected').text().trim().toLowerCase();
            if(actionText === 'arrange video meeting') {
                $('#video-meeting-container').show();
            } else {
                $('#video-meeting-container').hide();
            }
        });
        
        let allUniversities = [];
        let allCourseTypes = [];
        let allCourses = [];
        let courseProgramTypes = @json($course_program_types ?? []);
        
        $(document).ready(function() {
            $('#university_input option').each(function() {
                let stid = $(this).attr('data-school-type-id');
                try {
                    stid = JSON.parse(stid);
                } catch(e) {}
                allUniversities.push({
                    id: $(this).val(),
                    text: $(this).text(),
                    typeId: $(this).data('type-id'),
                    schoolTypeId: stid
                });
            });
            
            $('#course_type option').each(function() {
                allCourseTypes.push({
                    id: $(this).val(),
                    text: $(this).text(),
                    dbId: $(this).data('db-id')
                });
            });

            $('#course_input option').each(function() {
                allCourses.push({
                    id: $(this).val(),
                    text: $(this).text()
                });
            });
        });

        $('#school_type').select2({
            tags: true,
            dropdownParent: $('#callModal .modal-content'),
            placeholder: "Select or Type School Type",
            allowClear: true,
            width: '100%'
        });

        $('#program_level_id').on('change', function() {
            let levelId = $(this).val();
            let selectedText = $(this).find('option:selected').text().trim().toLowerCase();
            
            let universitySelect = $('#university_input');
            universitySelect.empty();

            if (selectedText === 'school') {
                $('#school_type_container').show();
                $('#course_label').text('Choose Class');
                $('#course_type_container').hide();
                $('#university_label').text('School Name');
                
                if (window.pendingAutofill && window.pendingAutofill.school_type) {
                    setSelect2Value('school_type', window.pendingAutofill.school_type);
                } else {
                    $('#school_type').val('');
                }
                setTimeout(function() {
                    $('#school_type').trigger('change');
                }, 10);
            } else if (selectedText === 'competetive coaching' || selectedText === 'competitive coaching') {
                $('#school_type_container').hide();
                $('#course_label').text('Course');
                $('#course_type_container').show();
                $('#university_label').text('Choose institute');
                
                allUniversities.forEach(function(u) {
                    if (!u.id || u.id === 'Not decided yet' || u.typeId == 3) {
                        let option = new Option(u.text, u.id, false, false);
                        $(option).attr('data-type-id', u.typeId);
                        universitySelect.append(option);
                    }
                });
                
                if (window.pendingAutofill && window.pendingAutofill.university_input) {
                    setSelect2Value('university_input', window.pendingAutofill.university_input);
                }
            } else {
                $('#school_type_container').hide();
                $('#course_label').text('Course');
                $('#course_type_container').show();
                $('#university_label').text('University / Organization');
                
                allUniversities.forEach(function(u) {
                    if (u.typeId != 4 || !u.id || u.id === 'Not decided yet') {
                        let option = new Option(u.text, u.id, false, false);
                        $(option).attr('data-type-id', u.typeId);
                        universitySelect.append(option);
                    }
                });
                
                if (window.pendingAutofill && window.pendingAutofill.university_input) {
                    setSelect2Value('university_input', window.pendingAutofill.university_input);
                }
            }
            universitySelect.trigger('change');

            let courseSelect = $('#course_input');
            courseSelect.html('<option value="">Loading...</option>');
            
            $.ajax({
                url: '{{ route("admin.students-crm.calling-module.get-courses") }}',
                type: 'GET',
                data: { program_level_id: levelId },
                success: function(res) {
                    let html = '<option value="">Select or Type Course</option>';
                    html += '<option value="Not decided yet">Not decided yet</option>';
                    if(res && res.length > 0) {
                        res.forEach(c => {
                            html += `<option value="${c.id}">${c.name}</option>`;
                        });
                    } else {
                        allCourses.forEach(function(c) {
                            if (c.id && c.id !== 'Not decided yet') {
                                html += `<option value="${c.id}">${c.text}</option>`;
                            }
                        });
                    }
                    courseSelect.html(html);
                    
                    if (window.pendingAutofill && window.pendingAutofill.course_input) {
                        setSelect2Value('course_input', window.pendingAutofill.course_input);
                    } else {
                        courseSelect.trigger('change');
                    }

                    if (window.pendingAutofill && window.pendingAutofill.course_type) {
                        setSelect2Value('course_type', window.pendingAutofill.course_type);
                    }
                },
                error: function() {
                    courseSelect.html('<option value="">Select or Type Course</option><option value="Not decided yet">Not decided yet</option>').trigger('change');
                }
            });
        });

        $('#school_type').on('change', function() {
            let schoolTypeId = $(this).val();
            let universitySelect = $('#university_input');
            let currentVal = universitySelect.val();
            universitySelect.empty();
            
            allUniversities.forEach(function(u) {
                if (!u.id || u.id === 'Not decided yet' || u.typeId == 4) {
                    if (!schoolTypeId || !u.id || u.id === 'Not decided yet') {
                        let option = new Option(u.text, u.id, false, false);
                        $(option).attr('data-type-id', u.typeId);
                        universitySelect.append(option);
                    } else {
                        let sTypes = Array.isArray(u.schoolTypeId) ? u.schoolTypeId.map(String) : (u.schoolTypeId ? [String(u.schoolTypeId)] : []);
                        if (sTypes.includes(String(schoolTypeId))) {
                            let option = new Option(u.text, u.id, false, false);
                            $(option).attr('data-type-id', u.typeId);
                            universitySelect.append(option);
                        }
                    }
                }
            });
            if (window.pendingAutofill && window.pendingAutofill.university_input) {
                setSelect2Value('university_input', window.pendingAutofill.university_input);
            } else {
                universitySelect.val(currentVal).trigger('change');
            }
        });

        $('#course_input').on('change', function() {
            let courseId = $(this).val();
            let programLevelText = $('#program_level_id').find('option:selected').text().trim().toLowerCase();
            let courseTypeSelect = $('#course_type');
            let currentModeVal = courseTypeSelect.val();
            
            if (programLevelText === 'competetive coaching' || programLevelText === 'competitive coaching') {
                courseTypeSelect.empty();
                
                let option1 = new Option('Select or Type Program Mode', '', false, false);
                let option2 = new Option('Not decided yet', 'Not decided yet', false, false);
                courseTypeSelect.append(option1).append(option2);

                if (courseId && courseId !== 'Not decided yet') {
                    let allowedTypeIds = courseProgramTypes
                        .filter(cpt => cpt.course_id == courseId)
                        .map(cpt => parseInt(cpt.program_type_id));
                        
                    allCourseTypes.forEach(function(ct) {
                        if (ct.id && ct.id !== 'Not decided yet' && allowedTypeIds.includes(parseInt(ct.dbId))) {
                            let option = new Option(ct.text, ct.id, false, false);
                            $(option).attr('data-db-id', ct.dbId);
                            courseTypeSelect.append(option);
                        }
                    });
                }
            } else {
                courseTypeSelect.empty();
                allCourseTypes.forEach(function(ct) {
                    let option = new Option(ct.text, ct.id, false, false);
                    if (ct.dbId) $(option).attr('data-db-id', ct.dbId);
                    courseTypeSelect.append(option);
                });
            }

            let modeToSet = (window.pendingAutofill && window.pendingAutofill.course_type) ? window.pendingAutofill.course_type : currentModeVal;
            if (modeToSet) {
                setSelect2Value('course_type', modeToSet);
            } else {
                courseTypeSelect.trigger('change');
            }
        });

        // Country, State, City Cascades
        const indianStates = [
            "Andhra Pradesh", "Arunachal Pradesh", "Assam", "Bihar", "Chhattisgarh", "Goa", "Gujarat",
            "Haryana", "Himachal Pradesh", "Jharkhand", "Karnataka", "Kerala", "Madhya Pradesh",
            "Maharashtra", "Manipur", "Meghalaya", "Mizoram", "Nagaland", "Odisha", "Punjab",
            "Rajasthan", "Sikkim", "Tamil Nadu", "Telangana", "Tripura", "Uttar Pradesh", "Uttarakhand",
            "West Bengal", "Andaman and Nicobar Islands", "Chandigarh", "Dadra and Nagar Haveli and Daman and Diu",
            "Delhi", "Jammu and Kashmir", "Ladakh", "Lakshadweep", "Puducherry"
        ];

        let preselectedCountry = "{{ request('country') }}";
        let preselectedState = "{{ request('state') }}";
        let preselectedCity = "{{ request('city') }}";
        const API_BASE = 'https://countriesnow.space/api/v0.1';

        // Load country list from API in background to supplement dropdown
        $.get(API_BASE + '/countries', function(res){
            if(res && res.data) {
                let currentVal = $('#countryFilter').val() || preselectedCountry;
                let existingVals = [];
                $('#countryFilter option').each(function() { existingVals.push($(this).val()); });
                
                res.data.forEach(c => {
                    if(c.country && !existingVals.includes(c.country)) {
                        let opt = new Option(c.country, c.country, false, (currentVal === c.country));
                        $('#countryFilter').append(opt);
                    }
                });
            }
        });

        $('#countryFilter').on('change', function () {
            const country = $(this).val();
            $('#stateFilter').html('<option value="">All States</option>');
            $('#cityFilter').html('<option value="">All Cities</option>');
            if (country) {
                loadStates(country);
            }
        });

        $('#stateFilter').on('change', function () {
            const country = $('#countryFilter').val();
            const state   = $(this).val();
            $('#cityFilter').html('<option value="">All Cities</option>');
            if (state) {
                loadCities(country, state);
            }
        });

        function loadStates(country) {
            let statesList = [];
            
            // 1. If India, populate immediately
            if (country && country.toLowerCase() === 'india') {
                statesList = [...indianStates];
                renderStateOptions(statesList);
            }

            // 2. Query our internal database locations
            $.get("{{ route('admin.students-crm.calling-dashboard.get-locations') }}", { country: country }, function(res) {
                if (res && res.states && res.states.length > 0) {
                    res.states.forEach(s => {
                        if (!statesList.includes(s)) statesList.push(s);
                    });
                    renderStateOptions(statesList);
                }
            });

            // 3. Query external API for global coverage
            $.ajax({
                type: 'POST',
                url: API_BASE + '/countries/states',
                contentType: 'application/json',
                data: JSON.stringify({ country: country }),
                success: function(res) {
                    if (res && res.data && res.data.states) {
                        res.data.states.forEach(s => {
                            if (!statesList.includes(s.name)) statesList.push(s.name);
                        });
                        renderStateOptions(statesList);
                    }
                }
            });
        }

        function renderStateOptions(statesList) {
            let currentVal = $('#stateFilter').val() || preselectedState;
            let html = '<option value="">All States</option>';
            statesList.sort().forEach(s => {
                let sel = (currentVal && currentVal.toLowerCase() === s.toLowerCase()) ? 'selected' : '';
                html += `<option value="${s}" ${sel}>${s}</option>`;
            });
            $('#stateFilter').html(html);

            if (currentVal && statesList.some(s => s.toLowerCase() === currentVal.toLowerCase())) {
                loadCities($('#countryFilter').val(), currentVal);
            }
        }

        function loadCities(country, state) {
            let citiesList = [];

            // 1. Query internal database locations
            $.get("{{ route('admin.students-crm.calling-dashboard.get-locations') }}", { country: country, state: state }, function(res) {
                if (res && res.cities && res.cities.length > 0) {
                    res.cities.forEach(c => {
                        if (!citiesList.includes(c)) citiesList.push(c);
                    });
                    renderCityOptions(citiesList);
                }
            });

            // 2. Query external API
            if (country && state) {
                $.ajax({
                    type: 'POST',
                    url: API_BASE + '/countries/state/cities',
                    contentType: 'application/json',
                    data: JSON.stringify({ country: country, state: state }),
                    success: function(res) {
                        if (res && res.data) {
                            res.data.forEach(c => {
                                if (!citiesList.includes(c)) citiesList.push(c);
                            });
                            renderCityOptions(citiesList);
                        }
                    }
                });
            }
        }

        function renderCityOptions(citiesList) {
            let currentVal = $('#cityFilter').val() || preselectedCity;
            let html = '<option value="">All Cities</option>';
            citiesList.sort().forEach(c => {
                let sel = (currentVal && currentVal.toLowerCase() === c.toLowerCase()) ? 'selected' : '';
                html += `<option value="${c}" ${sel}>${c}</option>`;
            });
            $('#cityFilter').html(html);
        }

        // Auto trigger initial load if preselected country
        if (preselectedCountry) {
            loadStates(preselectedCountry);
        }

        // Date Filter Restrictions
        $('#start_date').on('change', function() {
            var startDateVal = $(this).val();
            if (startDateVal) {
                var parts = startDateVal.split('-');
                var year = parts[0];
                var month = parts[1];
                
                var lastDay = new Date(year, parseInt(month), 0).getDate();
                var firstDayStr = year + "-" + month + "-01";
                var lastDayStr = year + "-" + month + "-" + (lastDay < 10 ? '0' + lastDay : lastDay);
                
                $('#end_date').attr('min', startDateVal);
                $('#end_date').attr('max', lastDayStr);
                
                var currentEndDate = $('#end_date').val();
                if (currentEndDate && (currentEndDate < startDateVal || currentEndDate > lastDayStr)) {
                    $('#end_date').val(startDateVal);
                }
            } else {
                $('#end_date').removeAttr('min').removeAttr('max');
            }
        });
        
        $('#start_date').trigger('change');
    });
</script>
@endpush

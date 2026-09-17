@extends('admin.layouts.master')

@section('title', 'Update Organisation by Bot')

@section('content')
<div class="container-fluid">
    <!-- Header -->
    <div class="row mb-3">
        <div class="col-12 d-flex justify-content-between align-items-center flex-wrap gap-2">
            <div>
                <h4 class="mb-1 text-dark fw-bold">
                    <i class="fas fa-robot text-primary me-2"></i>Update Organisation by Bot
                </h4>
                <p class="text-muted small mb-0">
                    Fetch live institutional updates strictly for fields configured in <strong>Organisation Fields</strong>, review side-by-side (Old vs New), and approve updates.
                </p>
            </div>
            <div class="d-flex gap-2">
                <a href="{{ route('admin.organisation-fields.index') }}" class="btn btn-outline-secondary btn-sm">
                    <i class="fas fa-sliders-h me-1"></i> Configure Fields
                </a>
                <a href="{{ route('admin.organisations.index') }}" class="btn btn-secondary btn-sm">
                    <i class="fas fa-arrow-left me-1"></i> Back to Organisations
                </a>
            </div>
        </div>
    </div>

    <!-- STEP 1: SELECT ORGANISATION CARD -->
    <div class="card shadow-sm border-0 mb-4" id="cardOrgSelector">
        <div class="card-header bg-white py-3 border-bottom d-flex justify-content-between align-items-center flex-wrap gap-2">
            <h5 class="card-title mb-0 text-dark fw-bold">
                <span class="badge bg-primary rounded-pill me-2">1</span> Select Organisation to Update
            </h5>
            <span id="badgeConfiguredFields" class="badge bg-light text-primary border px-3 py-2 d-none">
                <i class="fas fa-check-double me-1"></i> <span id="configuredFieldsCount">0</span> Fields Configured for this Type
            </span>
        </div>
        <div class="card-body p-4">
            <div class="row g-3 align-items-end">
                <div class="col-lg-5 col-md-6">
                    <label class="form-label fw-bold text-dark">Select Organisation <span class="text-danger">*</span></label>
                    <select id="selectOrganisation" class="form-select select2" required>
                        <option value="">-- Choose an Organisation --</option>
                        @foreach($organisations as $org)
                            <option value="{{ $org->id }}"
                                    data-url="{{ $org->official_website }}"
                                    data-type="{{ $org->organisationType?->title }}"
                                    data-type-id="{{ $org->organisation_type_id }}"
                                    {{ (isset($selectedOrgId) && (int)$selectedOrgId === (int)$org->id) ? 'selected' : '' }}>
                                {{ $org->name }} ({{ $org->organisationType?->title ?? 'N/A' }})
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="col-lg-5 col-md-6">
                    <label class="form-label fw-bold text-dark">Official Website / Verification URL <span class="text-danger">*</span></label>
                    <div class="input-group">
                        <span class="input-group-text"><i class="fas fa-globe"></i></span>
                        <input type="url" id="inputOrgUrl" class="form-control" placeholder="https://www.institution.edu.in" required>
                    </div>
                </div>

                <div class="col-lg-2 col-md-12">
                    <button type="button" id="btnFetchBotUpdates" class="btn btn-primary w-100 py-2 shadow-sm" disabled>
                        <i class="fas fa-bolt me-1"></i> <span id="btnFetchText">Fetch Updates</span>
                    </button>
                </div>
            </div>

            <div id="orgInfoAlert" class="alert alert-light border mt-3 mb-0 d-none">
                <div class="d-flex align-items-center justify-content-between flex-wrap gap-2">
                    <div class="d-flex align-items-center gap-3">
                        <div>
                            <span class="text-muted small">Organisation Type:</span>
                            <strong class="text-dark d-block" id="infoOrgType">-</strong>
                        </div>
                        <div class="border-start ps-3">
                            <span class="text-muted small">Official Domain:</span>
                            <strong class="text-dark d-block" id="infoOrgDomain">-</strong>
                        </div>
                    </div>
                    <a href="#" id="linkOrgEditFields" class="btn btn-sm btn-link text-decoration-none">
                        <i class="fas fa-cog me-1"></i> Modify checked fields for this type &raquo;
                    </a>
                </div>
            </div>
        </div>
    </div>

    <!-- STEP 2: LOADING CARD -->
    <div id="cardBotLoading" class="card shadow-sm border-0 mb-4 p-5 d-none text-center">
        <div class="py-4">
            <div class="spinner-border text-primary mb-3" style="width: 3.5rem; height: 3.5rem;" role="status">
                <span class="visually-hidden">Loading...</span>
            </div>
            <h4 class="fw-bold mb-2 text-dark">Bot is Researching & Comparing Data...</h4>
            <p class="text-muted mb-0 mx-auto" style="max-width: 650px;" id="botLoadingStatusText">
                Fetching latest institutional website pages, querying Google search grounding, and calculating Old vs New diffs strictly for your enabled fields. Please wait...
            </p>
        </div>
    </div>

    <!-- STEP 3: INTERACTIVE DIFF & APPROVAL WORKSPACE -->
    <div id="cardDiffWorkspace" class="d-none">
        <div class="card shadow-sm border-0 mb-4">
            <!-- Workspace Header with Summary & Actions -->
            <div class="card-header bg-white py-3 border-bottom d-flex justify-content-between align-items-center flex-wrap gap-3">
                <div>
                    <h5 class="card-title mb-0 fw-bold text-dark">
                        <span class="badge bg-success rounded-pill me-2">2</span> Review & Approve Bot Updates
                    </h5>
                    <small class="text-muted">
                        Comparing current database values with latest bot findings. Review items below before applying.
                    </small>
                </div>
                <div class="d-flex align-items-center flex-wrap gap-2">
                    <div class="btn-group btn-group-sm" role="group">
                        <button type="button" class="btn btn-outline-secondary" id="btnAcceptAllChanges">
                            <i class="fas fa-check-double me-1 text-success"></i> Accept All Changes
                        </button>
                        <button type="button" class="btn btn-outline-secondary" id="btnKeepAllOld">
                            <i class="fas fa-times me-1 text-danger"></i> Keep All Old
                        </button>
                    </div>
                    <button type="button" class="btn btn-success btn-sm px-3 shadow-sm btn-apply-approved">
                        <i class="fas fa-save me-1"></i> <span class="apply-btn-text">Approve & Update Organisation</span>
                    </button>
                </div>
            </div>

            <!-- Stats Bar -->
            <div class="bg-light px-4 py-2 border-bottom d-flex flex-wrap align-items-center justify-content-between gap-2">
                <div class="d-flex align-items-center gap-3 small">
                    <span><strong>Legend:</strong></span>
                    <span class="badge bg-warning text-dark"><i class="fas fa-exchange-alt me-1"></i> Changed Value</span>
                    <span class="badge bg-info text-white"><i class="fas fa-plus me-1"></i> New Data</span>
                    <span class="badge bg-secondary"><i class="fas fa-check me-1"></i> Unchanged</span>
                </div>
                <div class="small fw-bold text-muted">
                    <span id="summaryChangedCount" class="text-warning">0</span> changed,
                    <span id="summaryNewCount" class="text-info">0</span> new,
                    <span id="summaryApprovedCount" class="text-success">0</span> selected to apply
                </div>
            </div>

            <!-- Workspace Tabs -->
            <div class="card-body p-4">
                <ul class="nav nav-tabs-custom mb-4" id="diffTabs" role="tablist">
                    <li class="nav-item" role="presentation">
                        <button class="nav-link active" id="tab-diff-org-btn" data-bs-toggle="tab" data-bs-target="#tab-diff-org" type="button" role="tab">
                            <i class="fas fa-university me-1"></i> 1. Organisation
                            <span class="badge bg-light text-dark ms-1 border" id="badgeOrgDiffCount">0</span>
                        </button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link" id="tab-diff-campuses-btn" data-bs-toggle="tab" data-bs-target="#tab-diff-campuses" type="button" role="tab">
                            <i class="fas fa-city me-1"></i> 2. Campuses
                            <span class="badge bg-light text-dark ms-1 border" id="badgeCampusesDiffCount">0</span>
                        </button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link" id="tab-diff-depts-btn" data-bs-toggle="tab" data-bs-target="#tab-diff-depts" type="button" role="tab">
                            <i class="fas fa-building me-1"></i> 3. Departments
                            <span class="badge bg-light text-dark ms-1 border" id="badgeDeptsDiffCount">0</span>
                        </button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link" id="tab-diff-courses-btn" data-bs-toggle="tab" data-bs-target="#tab-diff-courses" type="button" role="tab">
                            <i class="fas fa-graduation-cap me-1"></i> 4. Courses
                            <span class="badge bg-light text-dark ms-1 border" id="badgeCoursesDiffCount">0</span>
                        </button>
                    </li>
                </ul>

                <div class="tab-content" id="diffTabContent">
                    <!-- ================= TAB 1: ORGANISATION DIFF ================= -->
                    <div class="tab-pane fade show active" id="tab-diff-org" role="tabpanel">
                        <div class="table-responsive">
                            <table class="table table-bordered align-middle mb-0" id="tableOrgDiff">
                                <thead class="table-light">
                                    <tr>
                                        <th style="width: 50px;" class="text-center">Apply</th>
                                        <th style="width: 25%;">Field</th>
                                        <th style="width: 35%;">Current DB Value (OLD)</th>
                                        <th style="width: 40%;">Bot Fetched Value (NEW)</th>
                                    </tr>
                                </thead>
                                <tbody id="tbodyOrgDiff">
                                    <!-- Populated via JS -->
                                </tbody>
                            </table>
                        </div>
                    </div>

                    <!-- ================= TAB 2: CAMPUSES DIFF ================= -->
                    <div class="tab-pane fade" id="tab-diff-campuses" role="tabpanel">
                        <div id="campusesDiffContainer">
                            <!-- Populated via JS -->
                        </div>
                    </div>

                    <!-- ================= TAB 3: DEPARTMENTS DIFF ================= -->
                    <div class="tab-pane fade" id="tab-diff-depts" role="tabpanel">
                        <div id="deptsDiffContainer">
                            <!-- Populated via JS -->
                        </div>
                    </div>

                    <!-- ================= TAB 4: COURSES DIFF ================= -->
                    <div class="tab-pane fade" id="tab-diff-courses" role="tabpanel">
                        <div id="coursesDiffContainer">
                            <!-- Populated via JS -->
                        </div>
                    </div>
                </div>

                <!-- Bottom Action Bar -->
                <div class="mt-4 pt-3 border-top d-flex justify-content-between align-items-center flex-wrap gap-2">
                    <div class="text-muted small">
                        <i class="fas fa-info-circle me-1"></i> Unchecked fields will remain untouched in the database.
                    </div>
                    <div class="d-flex gap-2">
                        <button type="button" class="btn btn-outline-secondary btn-sm" id="btnRestartReview">
                            <i class="fas fa-undo me-1"></i> Choose Another Organisation
                        </button>
                        <button type="button" class="btn btn-success px-4 shadow-sm btn-apply-approved">
                            <i class="fas fa-save me-1"></i> <span class="apply-btn-text">Approve & Update Organisation</span>
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<style>
.nav-tabs-custom {
    border-bottom: 2px solid #e9ecef;
}
.nav-tabs-custom .nav-link {
    color: #495057;
    border: none;
    border-bottom: 2px solid transparent;
    padding: 0.65rem 1.25rem;
    font-weight: 600;
    transition: all 0.2s ease;
}
.nav-tabs-custom .nav-link.active {
    color: #0d6efd;
    border-bottom: 2px solid #0d6efd;
    background: transparent;
}
.diff-row-changed {
    background-color: #fffdf5;
}
.diff-row-new {
    background-color: #f6fbff;
}
.diff-row-unchanged {
    opacity: 0.8;
}
.old-val-box {
    background-color: #f8f9fa;
    border: 1px solid #dee2e6;
    border-radius: 4px;
    padding: 6px 10px;
    font-size: 0.9rem;
    color: #6c757d;
    word-break: break-word;
}
.old-val-highlight {
    color: #d9534f;
    font-weight: 500;
    text-decoration: line-through;
}
.new-val-box {
    background-color: #ffffff;
    border: 1px solid #198754;
    border-radius: 4px;
    padding: 6px 10px;
    font-size: 0.9rem;
    color: #198754;
    word-break: break-word;
}
.new-val-input {
    border: 1px solid #b6d4fe;
    background-color: #f8faff;
    font-size: 0.9rem;
}
.new-val-input:focus {
    border-color: #0d6efd;
    background-color: #ffffff;
    box-shadow: 0 0 0 0.2rem rgba(13,110,253,0.15);
}
</style>

<script>
$(document).ready(function () {
    let currentDiffData = null;
    let selectedOrgDetails = null;

    // Initialize Select2
    if ($.fn.select2) {
        $('.select2').select2({ width: '100%' });
    }

    // When organisation is selected
    $('#selectOrganisation').on('change', function () {
        const orgId = $(this).val();
        if (!orgId) {
            $('#btnFetchBotUpdates').prop('disabled', true);
            $('#orgInfoAlert').addClass('d-none');
            $('#badgeConfiguredFields').addClass('d-none');
            return;
        }

        $('#btnFetchBotUpdates').prop('disabled', false);

        // Fetch details & configured fields via AJAX
        $.ajax({
            url: `{{ url('admin/ai-organisations') }}/${orgId}/data`,
            type: "GET",
            success: function (res) {
                if (!res.success) return;
                selectedOrgDetails = res;
                $('#inputOrgUrl').val(res.official_website || '');
                $('#infoOrgType').text(res.type_title);
                $('#infoOrgDomain').text(res.official_website ? (new URL(res.official_website)).hostname : 'Not Provided');
                $('#linkOrgEditFields').attr('href', `{{ route('admin.organisation-fields.index') }}?type_id=${res.type_id}`);
                $('#orgInfoAlert').removeClass('d-none');

                // Count configured fields
                let totalConfigured = 0;
                if (res.enabled_fields) {
                    Object.values(res.enabled_fields).forEach(arr => {
                        if (Array.isArray(arr)) totalConfigured += arr.length;
                    });
                }
                $('#configuredFieldsCount').text(totalConfigured);
                $('#badgeConfiguredFields').removeClass('d-none');
            },
            error: function () {
                console.error('Could not fetch organisation details.');
            }
        });
    });

    // If pre-selected on page load
    if ($('#selectOrganisation').val()) {
        $('#selectOrganisation').trigger('change');
    }

    // Trigger Bot Fetch
    $('#btnFetchBotUpdates').on('click', function (e) {
        e.preventDefault();
        const orgId = $('#selectOrganisation').val();
        const url = $('#inputOrgUrl').val();

        if (!orgId) {
            alert('Please select an organisation.');
            return;
        }
        if (!url) {
            alert('Please enter a valid official website URL.');
            return;
        }

        $('#cardOrgSelector').addClass('d-none');
        $('#cardDiffWorkspace').addClass('d-none');
        $('#cardBotLoading').removeClass('d-none');

        $.ajax({
            url: "{{ route('admin.ai-organisations.fetch-updates') }}",
            type: "POST",
            data: JSON.stringify({
                organisation_id: orgId,
                url: url
            }),
            contentType: "application/json",
            headers: {
                'X-CSRF-TOKEN': "{{ csrf_token() }}"
            },
            success: function (res) {
                $('#cardBotLoading').addClass('d-none');
                $('#cardOrgSelector').removeClass('d-none');
                if (res.success && res.diff) {
                    currentDiffData = res.diff;
                    renderDiffWorkspace(res.diff);
                    $('#cardDiffWorkspace').removeClass('d-none');
                    if (typeof toastr !== 'undefined') {
                        toastr.success(res.message || 'Updates fetched!');
                    }
                } else {
                    alert(res.message || 'Error fetching updates.');
                }
            },
            error: function (xhr) {
                $('#cardBotLoading').addClass('d-none');
                $('#cardOrgSelector').removeClass('d-none');
                const msg = xhr.responseJSON && xhr.responseJSON.message ? xhr.responseJSON.message : 'Error extracting updates from website.';
                if (typeof toastr !== 'undefined') {
                    toastr.error(msg);
                } else {
                    alert(msg);
                }
            }
        });
    });

    // Helper: Escape HTML
    function escapeHtml(text) {
        if (text === null || text === undefined) return '';
        if (typeof text === 'object') return JSON.stringify(text);
        return String(text)
            .replace(/&/g, "&amp;")
            .replace(/</g, "&lt;")
            .replace(/>/g, "&gt;")
            .replace(/"/g, "&quot;")
            .replace(/'/g, "&#039;");
    }

    // Render Diff Workspace
    function renderDiffWorkspace(diff) {
        // 1. Organisation Tab
        const $orgBody = $('#tbodyOrgDiff');
        $orgBody.empty();
        let orgChanged = 0;

        if (diff.organisation && diff.organisation.length > 0) {
            diff.organisation.forEach((row, idx) => {
                const isChanged = (row.status === 'changed');
                const isNew = (row.status === 'new');
                if (isChanged || isNew) orgChanged++;

                const rowClass = isChanged ? 'diff-row-changed' : (isNew ? 'diff-row-new' : 'diff-row-unchanged');
                const badgeHtml = isChanged 
                    ? '<span class="badge bg-warning text-dark me-1">OLD</span>' 
                    : (isNew ? '<span class="badge bg-info text-white me-1">EMPTY</span>' : '');

                const newBadgeHtml = isChanged 
                    ? '<span class="badge bg-success me-1">NEW</span>' 
                    : (isNew ? '<span class="badge bg-primary me-1">NEW DATA</span>' : '');

                const tr = `
                    <tr class="${rowClass}">
                        <td class="text-center">
                            <div class="form-check d-inline-block">
                                <input class="form-check-input diff-chk-apply" type="checkbox"
                                       data-entity="organisation"
                                       data-field="${row.field_key}"
                                       ${row.approved ? 'checked' : ''}>
                            </div>
                        </td>
                        <td>
                            <strong class="text-dark">${escapeHtml(row.label)}</strong>
                            <small class="text-muted d-block font-monospace">${row.field_key}</small>
                        </td>
                        <td>
                            <div class="old-val-box">
                                ${badgeHtml}
                                <span class="${isChanged ? 'old-val-highlight' : ''}">${row.old_value !== null && row.old_value !== '' ? escapeHtml(row.old_value) : '<em class="text-muted">[Empty]</em>'}</span>
                            </div>
                        </td>
                        <td>
                            <div class="d-flex align-items-center gap-2">
                                ${newBadgeHtml}
                                <input type="text" class="form-control form-control-sm new-val-input diff-input-val"
                                       data-entity="organisation"
                                       data-field="${row.field_key}"
                                       value="${escapeHtml(row.new_value ?? '')}">
                            </div>
                        </td>
                    </tr>
                `;
                $orgBody.append(tr);
            });
        } else {
            $orgBody.append('<tr><td colspan="4" class="text-center py-4 text-muted">No fields configured for update in Organisation.</td></tr>');
        }
        $('#badgeOrgDiffCount').text(orgChanged);

        // 2. Campuses Tab
        const $campContainer = $('#campusesDiffContainer');
        $campContainer.empty();
        let campChanged = 0;

        if (diff.campuses && diff.campuses.length > 0) {
            diff.campuses.forEach((camp, cIdx) => {
                let campRows = '';
                (camp.fields || []).forEach(f => {
                    const isChanged = (f.status === 'changed');
                    const isNew = (f.status === 'new');
                    if (isChanged || isNew) campChanged++;

                    campRows += `
                        <tr class="${isChanged ? 'diff-row-changed' : (isNew ? 'diff-row-new' : '')}">
                            <td class="text-center" style="width: 50px;">
                                <div class="form-check d-inline-block">
                                    <input class="form-check-input diff-chk-apply" type="checkbox"
                                           data-entity="campuses"
                                           data-idx="${cIdx}"
                                           data-field="${f.field_key}"
                                           ${f.approved ? 'checked' : ''}>
                                </div>
                            </td>
                            <td style="width: 25%;">
                                <strong>${escapeHtml(f.label)}</strong>
                                <small class="text-muted d-block font-monospace">${f.field_key}</small>
                            </td>
                            <td style="width: 35%;">
                                <div class="old-val-box">
                                    <span class="${isChanged ? 'old-val-highlight' : ''}">${f.old_value !== null && f.old_value !== '' ? escapeHtml(f.old_value) : '<em class="text-muted">[Empty]</em>'}</span>
                                </div>
                            </td>
                            <td style="width: 40%;">
                                <input type="text" class="form-control form-control-sm new-val-input diff-input-val"
                                       data-entity="campuses"
                                       data-idx="${cIdx}"
                                       data-field="${f.field_key}"
                                       value="${escapeHtml(f.new_value ?? '')}">
                            </td>
                        </tr>
                    `;
                });

                const campCard = `
                    <div class="card border mb-3">
                        <div class="card-header bg-light py-2 d-flex justify-content-between align-items-center">
                            <h6 class="mb-0 fw-bold text-dark">
                                <i class="fas fa-city text-warning me-1"></i> ${escapeHtml(camp.campus_name)}
                                ${camp.is_new_campus ? '<span class="badge bg-success ms-2">New Campus</span>' : ''}
                            </h6>
                            <input type="hidden" class="camp-meta-id" data-idx="${cIdx}" value="${camp.id || ''}">
                            <input type="hidden" class="camp-meta-name" data-idx="${cIdx}" value="${escapeHtml(camp.campus_name)}">
                        </div>
                        <div class="card-body p-0 table-responsive">
                            <table class="table table-bordered align-middle mb-0">
                                <tbody>${campRows}</tbody>
                            </table>
                        </div>
                    </div>
                `;
                $campContainer.append(campCard);
            });
        } else {
            $campContainer.html('<div class="alert alert-light text-center py-4 border text-muted">No campus updates found or no campus fields configured.</div>');
        }
        $('#badgeCampusesDiffCount').text(campChanged);

        // 3. Departments Tab (e.g. HOD: Atul -> HOD: Mukesh)
        const $deptContainer = $('#deptsDiffContainer');
        $deptContainer.empty();
        let deptChanged = 0;

        if (diff.departments && diff.departments.length > 0) {
            diff.departments.forEach((dept, dIdx) => {
                let deptRows = '';
                (dept.fields || []).forEach(f => {
                    const isChanged = (f.status === 'changed');
                    const isNew = (f.status === 'new');
                    if (isChanged || isNew) deptChanged++;

                    deptRows += `
                        <tr class="${isChanged ? 'diff-row-changed' : (isNew ? 'diff-row-new' : '')}">
                            <td class="text-center" style="width: 50px;">
                                <div class="form-check d-inline-block">
                                    <input class="form-check-input diff-chk-apply" type="checkbox"
                                           data-entity="departments"
                                           data-idx="${dIdx}"
                                           data-field="${f.field_key}"
                                           ${f.approved ? 'checked' : ''}>
                                </div>
                            </td>
                            <td style="width: 25%;">
                                <strong>${escapeHtml(f.label)}</strong>
                                <small class="text-muted d-block font-monospace">${f.field_key}</small>
                            </td>
                            <td style="width: 35%;">
                                <div class="old-val-box">
                                    <span class="badge bg-secondary me-1">OLD</span>
                                    <span class="${isChanged ? 'old-val-highlight' : ''}">${f.old_value !== null && f.old_value !== '' ? escapeHtml(f.old_value) : '<em class="text-muted">[Empty]</em>'}</span>
                                </div>
                            </td>
                            <td style="width: 40%;">
                                <div class="d-flex align-items-center gap-2">
                                    <span class="badge bg-success me-1">NEW</span>
                                    <input type="text" class="form-control form-control-sm new-val-input diff-input-val"
                                           data-entity="departments"
                                           data-idx="${dIdx}"
                                           data-field="${f.field_key}"
                                           value="${escapeHtml(f.new_value ?? '')}">
                                </div>
                            </td>
                        </tr>
                    `;
                });

                const deptCard = `
                    <div class="card border mb-3">
                        <div class="card-header bg-light py-2 d-flex justify-content-between align-items-center">
                            <h6 class="mb-0 fw-bold text-dark">
                                <i class="fas fa-building text-info me-1"></i> ${escapeHtml(dept.department_name)}
                                ${dept.is_new_department ? '<span class="badge bg-success ms-2">New Department</span>' : ''}
                            </h6>
                            <input type="hidden" class="dept-meta-id" data-idx="${dIdx}" value="${dept.id || ''}">
                            <input type="hidden" class="dept-meta-name" data-idx="${dIdx}" value="${escapeHtml(dept.department_name)}">
                        </div>
                        <div class="card-body p-0 table-responsive">
                            <table class="table table-bordered align-middle mb-0">
                                <tbody>${deptRows}</tbody>
                            </table>
                        </div>
                    </div>
                `;
                $deptContainer.append(deptCard);
            });
        } else {
            $deptContainer.html('<div class="alert alert-light text-center py-4 border text-muted">No department updates found or no department fields configured.</div>');
        }
        $('#badgeDeptsDiffCount').text(deptChanged);

        // 4. Courses Tab
        const $courseContainer = $('#coursesDiffContainer');
        $courseContainer.empty();
        let courseChanged = 0;

        if (diff.courses && diff.courses.length > 0) {
            diff.courses.forEach((course, cIdx) => {
                let courseRows = '';
                (course.fields || []).forEach(f => {
                    const isChanged = (f.status === 'changed');
                    const isNew = (f.status === 'new');
                    if (isChanged || isNew) courseChanged++;

                    courseRows += `
                        <tr class="${isChanged ? 'diff-row-changed' : (isNew ? 'diff-row-new' : '')}">
                            <td class="text-center" style="width: 50px;">
                                <div class="form-check d-inline-block">
                                    <input class="form-check-input diff-chk-apply" type="checkbox"
                                           data-entity="courses"
                                           data-idx="${cIdx}"
                                           data-field="${f.field_key}"
                                           ${f.approved ? 'checked' : ''}>
                                </div>
                            </td>
                            <td style="width: 25%;">
                                <strong>${escapeHtml(f.label)}</strong>
                                <small class="text-muted d-block font-monospace">${f.field_key}</small>
                            </td>
                            <td style="width: 35%;">
                                <div class="old-val-box">
                                    <span class="${isChanged ? 'old-val-highlight' : ''}">${f.old_value !== null && f.old_value !== '' ? escapeHtml(f.old_value) : '<em class="text-muted">[Empty]</em>'}</span>
                                </div>
                            </td>
                            <td style="width: 40%;">
                                <input type="text" class="form-control form-control-sm new-val-input diff-input-val"
                                       data-entity="courses"
                                       data-idx="${cIdx}"
                                       data-field="${f.field_key}"
                                       value="${escapeHtml(f.new_value ?? '')}">
                            </td>
                        </tr>
                    `;
                });

                const courseCard = `
                    <div class="card border mb-3">
                        <div class="card-header bg-light py-2 d-flex justify-content-between align-items-center">
                            <h6 class="mb-0 fw-bold text-dark">
                                <i class="fas fa-graduation-cap text-success me-1"></i> ${escapeHtml(course.course_name)}
                                ${course.is_new_course ? '<span class="badge bg-success ms-2">New Course</span>' : ''}
                            </h6>
                            <input type="hidden" class="course-meta-id" data-idx="${cIdx}" value="${course.id || ''}">
                            <input type="hidden" class="course-meta-name" data-idx="${cIdx}" value="${escapeHtml(course.course_name)}">
                        </div>
                        <div class="card-body p-0 table-responsive">
                            <table class="table table-bordered align-middle mb-0">
                                <tbody>${courseRows}</tbody>
                            </table>
                        </div>
                    </div>
                `;
                $courseContainer.append(courseCard);
            });
        } else {
            $courseContainer.html('<div class="alert alert-light text-center py-4 border text-muted">No course updates found or no course fields configured.</div>');
        }
        $('#badgeCoursesDiffCount').text(courseChanged);

        updateDiffSummary();
    }

    // Update Live Count Summary
    function updateDiffSummary() {
        const approvedCount = $('.diff-chk-apply:checked').length;
        const changedCount = $('.diff-row-changed').length;
        const newCount = $('.diff-row-new').length;

        $('#summaryChangedCount').text(changedCount);
        $('#summaryNewCount').text(newCount);
        $('#summaryApprovedCount').text(approvedCount);
    }

    $(document).on('change', '.diff-chk-apply', function () {
        updateDiffSummary();
    });

    // Accept All Changes
    $('#btnAcceptAllChanges').on('click', function () {
        $('.diff-row-changed .diff-chk-apply, .diff-row-new .diff-chk-apply').prop('checked', true);
        updateDiffSummary();
    });

    // Keep All Old
    $('#btnKeepAllOld').on('click', function () {
        $('.diff-chk-apply').prop('checked', false);
        updateDiffSummary();
    });

    // Restart / Choose Another
    $('#btnRestartReview').on('click', function () {
        $('#cardDiffWorkspace').addClass('d-none');
        window.scrollTo({ top: 0, behavior: 'smooth' });
    });

    // Apply Approved Updates AJAX
    $(document).on('click', '.btn-apply-approved', function (e) {
        e.preventDefault();
        const orgId = $('#selectOrganisation').val();
        if (!orgId) return;

        const checkedBoxes = $('.diff-chk-apply:checked');
        if (checkedBoxes.length === 0) {
            alert('Please select at least one field update to apply.');
            return;
        }

        if (!confirm(`Are you sure you want to approve and update ${checkedBoxes.length} field(s) into the database?`)) {
            return;
        }

        // Build Payload
        const approvedPayload = {
            organisation: {},
            campuses: [],
            departments: [],
            courses: []
        };

        // 1. Organisation fields
        $('.diff-chk-apply[data-entity="organisation"]:checked').each(function () {
            const field = $(this).data('field');
            const val = $(`.diff-input-val[data-entity="organisation"][data-field="${field}"]`).val();
            approvedPayload.organisation[field] = val;
        });

        // 2. Campuses
        const campIndices = {};
        $('.diff-chk-apply[data-entity="campuses"]:checked').each(function () {
            const idx = $(this).data('idx');
            const field = $(this).data('field');
            const val = $(`.diff-input-val[data-entity="campuses"][data-idx="${idx}"][data-field="${field}"]`).val();

            if (!campIndices[idx]) {
                campIndices[idx] = {
                    id: $(`.camp-meta-id[data-idx="${idx}"]`).val() || null,
                    campus_name: $(`.camp-meta-name[data-idx="${idx}"]`).val() || 'Main Campus',
                    fields: {}
                };
            }
            campIndices[idx].fields[field] = val;
        });
        approvedPayload.campuses = Object.values(campIndices);

        // 3. Departments
        const deptIndices = {};
        $('.diff-chk-apply[data-entity="departments"]:checked').each(function () {
            const idx = $(this).data('idx');
            const field = $(this).data('field');
            const val = $(`.diff-input-val[data-entity="departments"][data-idx="${idx}"][data-field="${field}"]`).val();

            if (!deptIndices[idx]) {
                deptIndices[idx] = {
                    id: $(`.dept-meta-id[data-idx="${idx}"]`).val() || null,
                    department_name: $(`.dept-meta-name[data-idx="${idx}"]`).val() || 'Department',
                    fields: {}
                };
            }
            deptIndices[idx].fields[field] = val;
        });
        approvedPayload.departments = Object.values(deptIndices);

        // 4. Courses
        const courseIndices = {};
        $('.diff-chk-apply[data-entity="courses"]:checked').each(function () {
            const idx = $(this).data('idx');
            const field = $(this).data('field');
            const val = $(`.diff-input-val[data-entity="courses"][data-idx="${idx}"][data-field="${field}"]`).val();

            if (!courseIndices[idx]) {
                courseIndices[idx] = {
                    id: $(`.course-meta-id[data-idx="${idx}"]`).val() || null,
                    course_name: $(`.course-meta-name[data-idx="${idx}"]`).val() || 'Course',
                    fields: {}
                };
            }
            courseIndices[idx].fields[field] = val;
        });
        approvedPayload.courses = Object.values(courseIndices);

        const $btns = $('.btn-apply-approved');
        $btns.prop('disabled', true).find('.apply-btn-text').text('Updating...');

        $.ajax({
            url: "{{ route('admin.ai-organisations.apply-updates') }}",
            type: "POST",
            data: JSON.stringify({
                organisation_id: orgId,
                approved_updates: approvedPayload
            }),
            contentType: "application/json",
            headers: {
                'X-CSRF-TOKEN': "{{ csrf_token() }}"
            },
            success: function (res) {
                $btns.prop('disabled', false).find('.apply-btn-text').text('Approve & Update Organisation');
                if (res.success) {
                    if (typeof toastr !== 'undefined') {
                        toastr.success(res.message);
                    } else {
                        alert(res.message);
                    }
                    setTimeout(function () {
                        window.location.href = "{{ route('admin.organisations.index') }}";
                    }, 1200);
                } else {
                    alert(res.message || 'Error updating data.');
                }
            },
            error: function (xhr) {
                $btns.prop('disabled', false).find('.apply-btn-text').text('Approve & Update Organisation');
                const msg = xhr.responseJSON && xhr.responseJSON.message ? xhr.responseJSON.message : 'Error updating database.';
                if (typeof toastr !== 'undefined') {
                    toastr.error(msg);
                } else {
                    alert(msg);
                }
            }
        });
    });
});
</script>
@endsection
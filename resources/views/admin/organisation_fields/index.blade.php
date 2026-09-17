@extends('admin.layouts.master')

@section('title', 'Organisation Fields')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card shadow-sm border-0">
                <div class="card-header bg-white py-3 border-bottom d-flex flex-wrap justify-content-between align-items-center gap-2">
                    <div>
                        <h4 class="card-title mb-0 fw-bold text-dark">
                            <i class="fas fa-sliders-h text-primary me-2"></i>Organisation Fields Configuration
                        </h4>
                        <small class="text-muted">Select database fields to fetch and update for each organisation type.</small>
                    </div>
                    <div class="d-flex align-items-center gap-2">
                        <button type="button" class="btn btn-outline-secondary btn-sm" id="btnSelectAll">
                            <i class="fas fa-check-square me-1"></i> Select All
                        </button>
                        <button type="button" class="btn btn-outline-secondary btn-sm" id="btnDeselectAll">
                            <i class="fas fa-square me-1"></i> Deselect All
                        </button>
                        <button type="button" class="btn btn-outline-danger btn-sm" id="btnResetDefault">
                            <i class="fas fa-undo me-1"></i> Reset Default
                        </button>
                        <button type="button" class="btn btn-primary btn-sm px-3 shadow-sm btn-save-config">
                            <i class="fas fa-save me-1"></i> <span class="save-btn-text">Save Fields</span>
                        </button>
                    </div>
                </div>

                <div class="card-body p-4">
                    <!-- TOP TOOLBAR: Organisation Types Segmented Pills -->
                    <div class="bg-light p-2 rounded border mb-4 d-flex flex-wrap align-items-center justify-content-between gap-2">
                        <div class="d-flex flex-wrap align-items-center gap-1">
                            <span class="text-muted small fw-bold text-uppercase px-2">Organisation Type:</span>
                            @foreach($organisationTypes as $ot)
                                @php $isActive = ($ot->id === $activeTypeId); @endphp
                                <a href="{{ route('admin.organisation-fields.index', ['type_id' => $ot->id]) }}"
                                   class="btn btn-sm {{ $isActive ? 'btn-primary fw-bold shadow-sm' : 'btn-white bg-white text-secondary border' }} px-3 rounded-pill">
                                    {{ $ot->title }}
                                </a>
                            @endforeach
                        </div>
                    </div>

                    <form id="formFieldConfig">
                        <input type="hidden" name="organisation_type_id" id="organisation_type_id" value="{{ $activeTypeId }}">

                        <!-- MAIN TABS: 1. Organisation, 2. Campuses, 3. Departments, 4. Courses -->
                        <ul class="nav nav-tabs-custom mb-4" id="mainEntityTabs" role="tablist">
                            <li class="nav-item" role="presentation">
                                <button class="nav-link active" id="tab-org-btn" data-bs-toggle="tab" data-bs-target="#tab-org" type="button" role="tab">
                                    <i class="fas fa-university me-1"></i> 1. Organisation
                                </button>
                            </li>
                            <li class="nav-item" role="presentation">
                                <button class="nav-link" id="tab-campuses-btn" data-bs-toggle="tab" data-bs-target="#tab-campuses" type="button" role="tab">
                                    <i class="fas fa-city me-1"></i> 2. Campuses
                                </button>
                            </li>
                            <li class="nav-item" role="presentation">
                                <button class="nav-link" id="tab-depts-btn" data-bs-toggle="tab" data-bs-target="#tab-depts" type="button" role="tab">
                                    <i class="fas fa-building me-1"></i> 3. Departments
                                </button>
                            </li>
                            <li class="nav-item" role="presentation">
                                <button class="nav-link" id="tab-courses-btn" data-bs-toggle="tab" data-bs-target="#tab-courses" type="button" role="tab">
                                    <i class="fas fa-graduation-cap me-1"></i> 4. Courses
                                </button>
                            </li>
                        </ul>

                        <div class="tab-content" id="mainEntityTabContent">
                            <!-- ==================== 1. ORGANISATION ==================== -->
                            <div class="tab-pane fade show active" id="tab-org" role="tabpanel">
                                @foreach($orgTabs as $tab)
                                    <div class="mb-4 pb-2">
                                        <div class="d-flex justify-content-between align-items-center mb-3 pb-2 border-bottom">
                                            <h6 class="fw-bold mb-0 text-dark">
                                                <i class="{{ $tab['icon'] ?? 'fas fa-folder' }} text-primary me-1"></i> {{ $tab['title'] }}
                                            </h6>
                                            <div>
                                                <button type="button" class="btn btn-sm btn-link text-decoration-none py-0 btn-sec-all" data-target="#sec-{{ $tab['id'] }}">Select Group</button>
                                                <span class="text-muted">|</span>
                                                <button type="button" class="btn btn-sm btn-link text-decoration-none py-0 text-muted btn-sec-none" data-target="#sec-{{ $tab['id'] }}">Deselect Group</button>
                                            </div>
                                        </div>

                                        <div class="row g-3" id="sec-{{ $tab['id'] }}">
                                            @foreach($tab['fields'] as $field)
                                                @php
                                                    $fn = $field['name'];
                                                    $isChecked = ($activeFieldsConfig === null) || in_array($fn, $activeFieldsConfig['organisation'] ?? []);
                                                @endphp
                                                <div class="col-lg-4 col-md-6">
                                                    <div class="form-check">
                                                        <input class="form-check-input field-chk"
                                                               type="checkbox"
                                                               name="fields[organisation][]"
                                                               value="{{ $fn }}"
                                                               id="org_{{ $fn }}"
                                                               data-entity="organisation"
                                                               {{ $isChecked ? 'checked' : '' }}>
                                                        <label class="form-check-label" for="org_{{ $fn }}">
                                                            {{ $field['label'] }}
                                                            <small class="text-muted d-block font-monospace">({{ $fn }})</small>
                                                        </label>
                                                    </div>
                                                </div>
                                            @endforeach
                                        </div>
                                    </div>
                                @endforeach
                            </div>

                            <!-- ==================== 2. CAMPUSES ==================== -->
                            <div class="tab-pane fade" id="tab-campuses" role="tabpanel">
                                @foreach($campusSections as $sec)
                                    <div class="mb-4 pb-2">
                                        <div class="d-flex justify-content-between align-items-center mb-3 pb-2 border-bottom">
                                            <h6 class="fw-bold mb-0 text-dark">
                                                <i class="{{ $sec['icon'] ?? 'fas fa-cube' }} text-primary me-1"></i> {{ $sec['title'] }}
                                            </h6>
                                            <div>
                                                <button type="button" class="btn btn-sm btn-link text-decoration-none py-0 btn-sec-all" data-target="#sec-{{ $sec['id'] }}">Select Group</button>
                                                <span class="text-muted">|</span>
                                                <button type="button" class="btn btn-sm btn-link text-decoration-none py-0 text-muted btn-sec-none" data-target="#sec-{{ $sec['id'] }}">Deselect Group</button>
                                            </div>
                                        </div>

                                        <div class="row g-3" id="sec-{{ $sec['id'] }}">
                                            @foreach($sec['fields'] as $field)
                                                @php
                                                    $fn = $field['name'];
                                                    $isChecked = ($activeFieldsConfig === null) || in_array($fn, $activeFieldsConfig['campus'] ?? []);
                                                @endphp
                                                <div class="col-lg-4 col-md-6">
                                                    <div class="form-check">
                                                        <input class="form-check-input field-chk"
                                                               type="checkbox"
                                                               name="fields[campus][]"
                                                               value="{{ $fn }}"
                                                               id="campus_{{ $fn }}"
                                                               data-entity="campus"
                                                               {{ $isChecked ? 'checked' : '' }}>
                                                        <label class="form-check-label" for="campus_{{ $fn }}">
                                                            {{ $field['label'] }}
                                                            <small class="text-muted d-block font-monospace">({{ $fn }})</small>
                                                        </label>
                                                    </div>
                                                </div>
                                            @endforeach
                                        </div>
                                    </div>
                                @endforeach
                            </div>

                            <!-- ==================== 3. DEPARTMENTS ==================== -->
                            <div class="tab-pane fade" id="tab-depts" role="tabpanel">
                                @foreach($deptSections as $sec)
                                    <div class="mb-4 pb-2">
                                        <div class="d-flex justify-content-between align-items-center mb-3 pb-2 border-bottom">
                                            <h6 class="fw-bold mb-0 text-dark">
                                                <i class="{{ $sec['icon'] ?? 'fas fa-cube' }} text-primary me-1"></i> {{ $sec['title'] }}
                                            </h6>
                                            <div>
                                                <button type="button" class="btn btn-sm btn-link text-decoration-none py-0 btn-sec-all" data-target="#sec-{{ $sec['id'] }}">Select Group</button>
                                                <span class="text-muted">|</span>
                                                <button type="button" class="btn btn-sm btn-link text-decoration-none py-0 text-muted btn-sec-none" data-target="#sec-{{ $sec['id'] }}">Deselect Group</button>
                                            </div>
                                        </div>

                                        <div class="row g-3" id="sec-{{ $sec['id'] }}">
                                            @foreach($sec['fields'] as $field)
                                                @php
                                                    $fn = $field['name'];
                                                    $isChecked = ($activeFieldsConfig === null) || in_array($fn, $activeFieldsConfig['department'] ?? []);
                                                @endphp
                                                <div class="col-lg-4 col-md-6">
                                                    <div class="form-check">
                                                        <input class="form-check-input field-chk"
                                                               type="checkbox"
                                                               name="fields[department][]"
                                                               value="{{ $fn }}"
                                                               id="dept_{{ $fn }}"
                                                               data-entity="department"
                                                               {{ $isChecked ? 'checked' : '' }}>
                                                        <label class="form-check-label" for="dept_{{ $fn }}">
                                                            {{ $field['label'] }}
                                                            <small class="text-muted d-block font-monospace">({{ $fn }})</small>
                                                        </label>
                                                    </div>
                                                </div>
                                            @endforeach
                                        </div>
                                    </div>
                                @endforeach
                            </div>

                            <!-- ==================== 4. COURSES ==================== -->
                            <div class="tab-pane fade" id="tab-courses" role="tabpanel">
                                @foreach($courseSections as $sec)
                                    <div class="mb-4 pb-2">
                                        <div class="d-flex justify-content-between align-items-center mb-3 pb-2 border-bottom">
                                            <h6 class="fw-bold mb-0 text-dark">
                                                <i class="{{ $sec['icon'] ?? 'fas fa-cube' }} text-primary me-1"></i> {{ $sec['title'] }}
                                            </h6>
                                            <div>
                                                <button type="button" class="btn btn-sm btn-link text-decoration-none py-0 btn-sec-all" data-target="#sec-{{ $sec['id'] }}">Select Group</button>
                                                <span class="text-muted">|</span>
                                                <button type="button" class="btn btn-sm btn-link text-decoration-none py-0 text-muted btn-sec-none" data-target="#sec-{{ $sec['id'] }}">Deselect Group</button>
                                            </div>
                                        </div>

                                        <div class="row g-3" id="sec-{{ $sec['id'] }}">
                                            @foreach($sec['fields'] as $field)
                                                @php
                                                    $fn = $field['name'];
                                                    $isChecked = ($activeFieldsConfig === null) || in_array($fn, $activeFieldsConfig['course'] ?? []);
                                                @endphp
                                                <div class="col-lg-4 col-md-6">
                                                    <div class="form-check">
                                                        <input class="form-check-input field-chk"
                                                               type="checkbox"
                                                               name="fields[course][]"
                                                               value="{{ $fn }}"
                                                               id="course_{{ $fn }}"
                                                               data-entity="course"
                                                               {{ $isChecked ? 'checked' : '' }}>
                                                        <label class="form-check-label" for="course_{{ $fn }}">
                                                            {{ $field['label'] }}
                                                            <small class="text-muted d-block font-monospace">({{ $fn }})</small>
                                                        </label>
                                                    </div>
                                                </div>
                                            @endforeach
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        </div>

                        <!-- Card Bottom Actions -->
                        <div class="mt-4 pt-3 border-top d-flex justify-content-between align-items-center">
                            <span class="text-muted small" id="statusSummary">0 fields checked</span>
                            <button type="button" class="btn btn-primary px-4 shadow-sm btn-save-config">
                                <i class="fas fa-save me-1"></i> <span class="save-btn-text">Save Fields</span>
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
.nav-tabs-custom {
    border-bottom: 2px solid #e9ecef;
    margin-bottom: 25px;
    display: flex;
    flex-wrap: wrap;
    list-style: none;
    padding-left: 0;
}
.nav-tabs-custom .nav-link {
    border: none;
    border-bottom: 2px solid transparent;
    color: #495057;
    font-weight: 500;
    padding: 10px 20px;
    font-size: 15px;
    transition: all 0.2s ease-in-out;
    background: transparent;
    border-radius: 0;
}
.nav-tabs-custom .nav-link:hover {
    color: #0d6efd;
    border-bottom-color: #adb5bd;
}
.nav-tabs-custom .nav-link.active {
    color: #0d6efd;
    border-bottom: 2px solid #0d6efd;
    font-weight: 600;
    background: transparent;
}
.form-check-input {
    cursor: pointer;
}
.form-check-label {
    cursor: pointer;
    line-height: 1.3;
}
</style>
@endsection

@section('scripts')
<script>
$(document).ready(function () {
    const orgTypeId = $('#organisation_type_id').val();

    function updateSummary() {
        const total = $('.field-chk').length;
        const checked = $('.field-chk:checked').length;
        $('#statusSummary').text(`${checked} of ${total} fields checked`);
    }

    // Checkbox change listener
    $(document).on('change', '.field-chk', function () {
        updateSummary();
    });

    // Select All (All fields across all tabs)
    $('#btnSelectAll').on('click', function (e) {
        e.preventDefault();
        $('.field-chk').prop('checked', true).trigger('change');
    });

    // Deselect All
    $('#btnDeselectAll').on('click', function (e) {
        e.preventDefault();
        $('.field-chk').prop('checked', false).trigger('change');
    });

    // Group-level Select All
    $(document).on('click', '.btn-sec-all', function (e) {
        e.preventDefault();
        const targetSelector = $(this).data('target');
        $(targetSelector).find('.field-chk').prop('checked', true).trigger('change');
    });

    // Group-level Deselect All
    $(document).on('click', '.btn-sec-none', function (e) {
        e.preventDefault();
        const targetSelector = $(this).data('target');
        $(targetSelector).find('.field-chk').prop('checked', false).trigger('change');
    });

    // Save configuration
    function saveFields() {
        const payload = {
            organisation_type_id: orgTypeId,
            fields_config: {
                organisation: [],
                campus: [],
                department: [],
                course: []
            }
        };

        $('.field-chk:checked').each(function () {
            const entity = $(this).data('entity');
            const val = $(this).val();
            if (payload.fields_config[entity]) {
                payload.fields_config[entity].push(val);
            }
        });

        const $saveBtns = $('.btn-save-config');
        $saveBtns.prop('disabled', true).find('.save-btn-text').text('Saving...');

        $.ajax({
            url: "{{ route('admin.organisation-fields.store') }}",
            type: "POST",
            data: JSON.stringify(payload),
            contentType: "application/json",
            headers: {
                'X-CSRF-TOKEN': "{{ csrf_token() }}"
            },
            success: function (res) {
                $saveBtns.prop('disabled', false).find('.save-btn-text').text('Save Fields');
                if (typeof toastr !== 'undefined') {
                    toastr.success(res.message || 'Saved successfully!');
                } else {
                    alert(res.message || 'Saved successfully!');
                }
            },
            error: function (xhr) {
                $saveBtns.prop('disabled', false).find('.save-btn-text').text('Save Fields');
                const msg = xhr.responseJSON && xhr.responseJSON.message ? xhr.responseJSON.message : 'Error saving fields.';
                if (typeof toastr !== 'undefined') {
                    toastr.error(msg);
                } else {
                    alert(msg);
                }
            }
        });
    }

    $(document).on('click', '.btn-save-config', function (e) {
        e.preventDefault();
        saveFields();
    });

    // Reset default
    $('#btnResetDefault').on('click', function (e) {
        e.preventDefault();
        if (!confirm('Reset all fields for this organisation type to checked?')) return;

        $.ajax({
            url: `{{ url('admin/organisation-fields') }}/${orgTypeId}/reset`,
            type: "POST",
            headers: {
                'X-CSRF-TOKEN': "{{ csrf_token() }}"
            },
            success: function (res) {
                if (typeof toastr !== 'undefined') {
                    toastr.success(res.message || 'Reset successfully!');
                } else {
                    alert(res.message || 'Reset successfully!');
                }
                setTimeout(function () {
                    window.location.reload();
                }, 800);
            },
            error: function () {
                alert('Error resetting configuration.');
            }
        });
    });

    updateSummary();
});
</script>
@endsection
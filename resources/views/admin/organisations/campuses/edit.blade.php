@extends('admin.layouts.master')

@section('content')

    @push('css')
        <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
        <style>
            .ck-editor__editable {
                min-height: 200px;
            }

            .select2-container .select2-selection--single {
                height: 38px;
                border: 1px solid #dee2e6;
            }

            .select2-container--default .select2-selection--single .select2-selection__rendered {
                line-height: 38px;
            }

            /* Traditional Tab Styles - Standardized */
            .nav-tabs-custom {
                border-bottom: 2px solid #ebeef1;
                background: #fff;
                padding: 0 10px;
                margin-bottom: 25px;
                display: flex;
                flex-wrap: wrap;
                list-style: none;
            }

            .nav-tabs-custom .nav-item {
                margin-bottom: -2px;
            }

            .nav-tabs-custom .nav-link {
                border: none;
                border-bottom: 2px solid transparent;
                color: #495057;
                font-weight: 500;
                padding: 12px 20px;
                transition: all 0.3s;
                text-decoration: none;
                display: block;
                font-size: 14px;
            }

            .nav-tabs-custom .nav-link:hover {
                color: #0d6efd;
                background: rgba(13, 110, 253, 0.05);
            }

            .nav-tabs-custom .nav-link.active {
                color: #0d6efd !important;
                border-bottom: 2px solid #0d6efd;
                background: none;
                font-weight: 600;
            }

            .nav-tabs-custom .nav-link.completed {
                color: #34c38f;
            }

            /* Sticky Footer for Navigation */
            .step-footer {
                position: sticky;
                bottom: 0;
                background: #fff;
                padding: 15px 25px;
                border-top: 1px solid #dee2e6;
                margin: 0 -25px -25px -25px;
                z-index: 99;
                box-shadow: 0 -5px 15px rgba(0, 0, 0, 0.05);
            }

            .tab-pane {
                display: none;
                padding: 20px 0;
            }

            .tab-pane.active {
                display: block;
            }

            #autosave-status {
                font-size: 11px;
                color: #6c757d;
            }
        </style>
    @endpush



    <div class="row">
        <div class="col-12">
            <div class="page-title-box d-sm-flex align-items-center justify-content-between">
                <h4 class="mb-sm-0">Edit Campus: {{ $campus->campus_name }}</h4>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-12">
            <form action="{{ route('admin.organisations.campuses.update', [$organisation->id, $campus->id]) }}"
                id="campus-form" method="POST">
                @csrf
                @method('PUT')

                <div class="card">
                    <div class="card-header bg-white border-bottom-0 pb-0">
                        <div class="d-flex align-items-center justify-content-between mb-2">
                            <div>
                                <span class="badge bg-soft-primary text-primary px-3 py-2 rounded-pill">
                                    <i class="fas fa-university me-1"></i> {{ $organisation->organisation_name }}
                                </span>
                            </div>
                            <div id="autosave-status" class="small text-muted">
                                <i class="fas fa-check-circle text-success me-1"></i> Changes saved
                            </div>
                        </div>

                        <ul class="nav nav-tabs-custom" id="campusTabs" role="tablist">
                            <li class="nav-item"><a class="nav-link active" href="#identity" role="tab">Identity</a></li>
                            <li class="nav-item"><a class="nav-link" href="#location" role="tab">Location</a></li>
                            <li class="nav-item"><a class="nav-link" href="#infra" role="tab">Infrastructure</a></li>
                            <li class="nav-item"><a class="nav-link" href="#academic" role="tab">Academic Focus</a></li>
                            <li class="nav-item"><a class="nav-link" href="#facilities" role="tab">Facilities</a></li>
                            <li class="nav-item"><a class="nav-link" href="#transport" role="tab">Transport</a></li>
                            <li class="nav-item"><a class="nav-link" href="#safety" role="tab">Safety</a></li>
                            <li class="nav-item"><a class="nav-link" href="#contact" role="tab">Contact</a></li>
                            <li class="nav-item"><a class="nav-link" href="#class-profile" role="tab">Class Profile</a></li>
                        </ul>
                    </div>

                    <div class="card-body">
                        <div class="tab-content text-muted p-2">
                            <!-- 1. Identity -->
                            <div class="tab-pane active" id="identity" role="tabpanel">
                                <div class="row g-3">
                                    <div class="col-md-6">
                                        <label class="form-label">Campus Name *</label>
                                        <input type="text" name="campus_name" class="form-control"
                                            value="{{ $campus->campus_name }}" required>
                                    </div>
                                    {{-- <div class="col-md-6">
                                        <label class="form-label">Campus Type *</label>
                                        <select name="campus_type" class="form-select" required>
                                            @foreach(['Main', 'Regional', 'Satellite', 'Centre', 'Branch'] as $type)
                                                <option value="{{ $type }}" {{ $campus->campus_type == $type ? 'selected' : '' }}>
                                                    {{ $type }} Campus
                                                </option>
                                            @endforeach
                                        </select>
                                    </div> --}}
                                    @if($organisation->organisation_type_id == 4)
                                    <div class="col-md-6">
                                        <label class="form-label">School Type</label>
                                        <select name="campus_type_new_id" class="form-select">
                                            <option value="">Select School Type</option>
                                            @if(isset($schoolTypes))
                                                @foreach($schoolTypes as $st)
                                                    <option value="{{ $st->id }}" {{ (old('campus_type_new_id') ?? $campus->campus_type_new_id) == $st->id ? 'selected' : '' }}>
                                                        {{ $st->title }}
                                                    </option>
                                                @endforeach
                                            @endif
                                        </select>
                                    </div>
                                    @endif
                                    <div class="col-md-6">
                                        <label class="form-label">Established Year</label>
                                        <input type="number" name="established_year" class="form-control"
                                            value="{{ $campus->established_year }}">
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label">Brand Type</label>
                                        <select name="brand_type" id="brand_type" class="form-select">
                                            <option value="">Select Brand Type</option>
                                            @foreach($brandTypes as $brand)
                                                <option value="{{ $brand }}" {{ $campus->brand_type == $brand ? 'selected' : '' }}>{{ $brand }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-check form-switch mt-4">
                                            <input class="form-check-input" type="checkbox" name="status" id="status"
                                                value="1" {{ $campus->status ? 'checked' : '' }}>
                                            <label class="form-check-label fw-bold" for="status">Active Status</label>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- 2. Location -->
                            <div class="tab-pane" id="location" role="tabpanel">
                                <div class="row g-3">
                                    <div class="col-md-4">
                                        <label class="form-label">Pincode</label>
                                        <input type="text" name="pincode" id="pincode" class="form-control"
                                            value="{{ $campus->pincode }}">
                                    </div>
                                    <div class="col-md-4">
                                        <label class="form-label">City</label>
                                        <input type="text" name="city" id="city" class="form-control" value="{{ $campus->city }}">
                                    </div>
                                    <div class="col-md-4">
                                        <label class="form-label">State</label>
                                        <input type="text" name="state" id="state" class="form-control" value="{{ $campus->state }}">
                                    </div>
                                    <div class="col-12">
                                        <label class="form-label">Full Address</label>
                                        <textarea name="full_address" class="form-control"
                                            rows="2">{{ $campus->full_address }}</textarea>
                                    </div>
                                </div>
                            </div>

                            <!-- 3. Infrastructure -->
                            <div class="tab-pane" id="infra" role="tabpanel">
                                <div class="row g-3">
                                    <div class="col-md-4">
                                        <label class="form-label">Campus Area</label>
                                        <div class="input-group">
                                            <input type="number" step="0.01" name="campus_area_acres" class="form-control"
                                                value="{{ $campus->campus_area_acres }}">
                                            <select name="campus_area_unit" class="form-select">
                                                <option value="Acres" {{ $campus->campus_area_unit == 'Acres' ? 'selected' : '' }}>Acres</option>
                                                <option value="Square Yard" {{ $campus->campus_area_unit == 'Square Yard' ? 'selected' : '' }}>Sq Yard</option>
                                            </select>
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <label class="form-label">Classrooms Count</label>
                                        <input type="number" name="classrooms_count" class="form-control"
                                            value="{{ $campus->classrooms_count }}">
                                    </div>
                                    <div class="col-md-12">
                                        <div class="form-check form-switch">
                                            <input class="form-check-input" type="checkbox" name="smart_classrooms"
                                                value="1" id="smart_class" {{ $campus->smart_classrooms ? 'checked' : '' }}>
                                            <label class="form-check-label" for="smart_class">Smart Classrooms
                                                Available</label>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- 4. Academic -->
                            <div class="tab-pane" id="academic" role="tabpanel">
                                <div class="row g-3">
                                    @if($organisation->organisation_type_id == 3)
                                    <div class="col-md-6">
                                        <label class="form-label">Exams Prepared For</label>
                                        <select name="exams_prepared_for[]" class="form-select select2" multiple>
                                            @foreach($exams as $exam)
                                                <option value="{{ $exam->name }}" {{ is_array($campus->exams_prepared_for) && in_array($exam->name, $campus->exams_prepared_for) ? 'selected' : '' }}>
                                                    {{ $exam->name }}
                                                </option>
                                            @endforeach
                                        </select>
                                    </div>
                                    @endif
                                    <div class="col-12">
                                        <label class="form-label">About Campus</label>
                                        <textarea name="about_institute" id="about_institute"
                                            class="editor">{{ $campus->about_institute }}</textarea>
                                    </div>
                                </div>
                            </div>

                            <!-- 5. Facilities -->
                            <div class="tab-pane" id="facilities" role="tabpanel">
                                <p class="text-muted mb-3">Select the facilities available at this campus.</p>
                                <div class="row g-3">
                                    @foreach($facilitiesMaster as $facility)
                                        @php
                                            $isChecked = false;
                                            if (is_array(old('facilities'))) {
                                                $isChecked = in_array($facility->id, old('facilities'));
                                            } elseif (is_array($campus->facilities)) {
                                                $isChecked = in_array($facility->id, $campus->facilities);
                                            }
                                        @endphp
                                        <div class="col-md-3 col-sm-4 col-6">
                                            <div class="facility-card text-center border rounded p-3 h-100 position-relative" style="cursor: pointer;" onclick="toggleFacility('{{ $facility->id }}')">
                                                <input type="checkbox" name="facilities[]" value="{{ $facility->id }}" id="facility_{{ $facility->id }}" class="d-none" {{ $isChecked ? 'checked' : '' }}>
                                                <div class="facility-icon mb-2">
                                                    @if($facility->icon)
                                                        <i class="{{ $facility->icon }} fa-2x text-muted"></i>
                                                    @else
                                                        <i class="fas fa-building fa-2x text-muted"></i>
                                                    @endif
                                                </div>
                                                <h6 class="mb-0 facility-name" style="font-size: 0.9rem;">{{ $facility->name }}</h6>
                                                <div class="facility-check position-absolute top-0 end-0 m-2" style="display: none;">
                                                    <i class="fas fa-check-circle text-success"></i>
                                                </div>
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                            </div>

                            <!-- 6. Transport -->
                            <div class="tab-pane" id="transport" role="tabpanel">
                                <div class="row g-3">
                                    <div class="col-md-12">
                                        <div class="form-check form-switch">
                                            <input class="form-check-input" type="checkbox" name="transport_available"
                                                id="transport_sw" value="1" {{ $campus->transport_available ? 'checked' : '' }}>
                                            <label class="form-check-label" for="transport_sw">Transport Available</label>
                                        </div>
                                    </div>
                                    <div class="col-12" id="bus-routes-box" style="display:none;">
                                        <label class="form-label">Bus Routes</label>
                                        <div id="bus-routes-container">
                                            @if(is_array($campus->bus_routes) && count($campus->bus_routes) > 0)
                                                @foreach($campus->bus_routes as $route)
                                                    <div class="input-group mb-2">
                                                        <input type="text" name="bus_routes[]" class="form-control"
                                                            value="{{ $route }}">
                                                        <button type="button" class="btn btn-outline-danger remove-route"
                                                            onclick="$(this).parent().remove()">Remove</button>
                                                    </div>
                                                @endforeach
                                            @else
                                                <div class="input-group mb-2">
                                                    <input type="text" name="bus_routes[]" class="form-control"
                                                        placeholder="Enter route">
                                                    <button type="button" class="btn btn-outline-danger remove-route"
                                                        onclick="$(this).parent().remove()">Remove</button>
                                                </div>
                                            @endif
                                        </div>
                                        <button type="button" class="btn btn-sm btn-outline-primary"
                                            onclick="addRoute()">Add Route</button>
                                    </div>
                                </div>
                            </div>

                            <!-- 7. Safety -->
                            <div class="tab-pane" id="safety" role="tabpanel">
                                <div class="row g-3">
                                    <div class="col-md-4">
                                        <div class="form-check form-switch">
                                            <input class="form-check-input" type="checkbox" name="cctv_coverage" value="1"
                                                id="cctv" {{ $campus->cctv_coverage ? 'checked' : '' }}>
                                            <label class="form-check-label" for="cctv">CCTV Coverage</label>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- 8. Contact -->
                            <div class="tab-pane" id="contact" role="tabpanel">
                                <div class="row g-3">
                                    <div class="col-md-6">
                                        <label class="form-label">Campus Email</label>
                                        <input type="email" name="campus_email" class="form-control"
                                            value="{{ $campus->campus_email }}">
                                    </div>
                                    <div class="col-12">
                                        <label class="form-label">Contact Numbers (Comma separated)</label>
                                        <input type="text" name="campus_contact_numbers" class="form-control"
                                            value="{{ is_array($campus->campus_contact_numbers) ? implode(', ', $campus->campus_contact_numbers) : $campus->campus_contact_numbers }}">
                                    </div>
                                </div>
                            </div>
                            
                            <!-- 9. Class Profile -->
                            <div class="tab-pane fade" id="class-profile" role="tabpanel">
                                <div class="d-flex align-items-center justify-content-between mb-3">
                                    <h5 class="text-primary mb-0">Class Profile</h5>
                                    <button type="button" class="btn btn-sm btn-outline-primary" id="add-class-profile-btn">
                                        <i class="fas fa-plus"></i> Add Year Stats
                                    </button>
                                </div>
                                <div id="class-profile-container">
                                    @php $classProfiles = old('class_profile', is_array($campus->class_profile) ? $campus->class_profile : [[]]); @endphp
                                    @foreach($classProfiles as $index => $stat)
                                    <div class="class-profile-item border p-4 mb-4 rounded position-relative bg-light">
                                        @if($index > 0)
                                        <button type="button" class="btn btn-sm btn-danger position-absolute top-0 end-0 m-2 remove-class-profile-btn"><i class="fas fa-times"></i></button>
                                        @endif
                                        <div class="row g-3">
                                            <div class="col-md-4">
                                                <label class="form-label fw-bold">Year</label>
                                                <input type="number" name="class_profile[{{$index}}][year]" class="form-control" value="{{ $stat['year'] ?? '' }}" placeholder="e.g. 2024">
                                            </div>
                                            <div class="col-md-4">
                                                <label class="form-label">Total Students</label>
                                                <input type="number" min="0" name="class_profile[{{$index}}][total_students]" class="form-control" value="{{ $stat['total_students'] ?? '' }}">
                                            </div>
                                            <div class="col-md-4">
                                                <label class="form-label">Total Faculty</label>
                                                <input type="number" min="0" name="class_profile[{{$index}}][total_faculty]" class="form-control" value="{{ $stat['total_faculty'] ?? '' }}">
                                            </div>
                                            <div class="col-md-4">
                                                <label class="form-label">Total Male Students</label>
                                                <input type="number" min="0" name="class_profile[{{$index}}][total_male_students]" class="form-control" value="{{ $stat['total_male_students'] ?? '' }}">
                                            </div>
                                            <div class="col-md-4">
                                                <label class="form-label">Total Female Students</label>
                                                <input type="number" min="0" name="class_profile[{{$index}}][total_female_students]" class="form-control" value="{{ $stat['total_female_students'] ?? '' }}">
                                            </div>
                                            <div class="col-md-4">
                                                <label class="form-label">Total Students Outside State</label>
                                                <input type="number" min="0" name="class_profile[{{$index}}][total_outside_state]" class="form-control" value="{{ $stat['total_outside_state'] ?? '' }}">
                                            </div>
                                        </div>
                                    </div>
                                    @endforeach
                                </div>
                            </div>
                        </div>

                        <div class="step-footer d-flex align-items-center">
                            <button type="button" class="btn btn-primary" id="prevBtn"
                                onclick="nextPrev(-1)">Previous</button>
                            <div class="ms-auto d-flex align-items-center">
                                <span id="save-message" class="text-muted small me-3" style="opacity: 0;">Saving...</span>
                                <button type="button" class="btn btn-primary" id="nextBtn" onclick="nextPrev(1)">Next
                                    Step</button>
                                <button type="submit" class="btn btn-success" id="submitBtn" style="display:none;">Finish &
                                    Update</button>
                            </div>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </div>

@endsection

@push('js')
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
    <script>
        $(document).ready(function () {
            $('.select2').select2({ width: '100%' });
            initializeTinyMCE();

            // Toggle Franchise
            $('#brand_type').on('change', function () {
                $('#franchiseFields').toggle($(this).val() === 'Franchise');
            }).trigger('change');

            // Toggle Transport
            $('#transport_sw').on('change', function () {
                $('#bus-routes-box').toggle(this.checked);
            }).trigger('change');

            // STEP FORM & BATCH SAVE LOGIC
            let currentTab = 0;
            const tabs = $('#campusTabs .nav-link');
            const campusId = '{{ $campus->id }}';

            tabs.each(function (index) {
                $(this).on('click', function (e) {
                    e.preventDefault();
                    if (index > currentTab && !validateCurrentStep()) {
                        return false;
                    }
                    saveStepData();
                    currentTab = index;
                    showTab(currentTab);
                });
            });

            window.nextPrev = function (n) {
                if (n == 1 && !validateCurrentStep()) return false;
                saveStepData();
                currentTab = currentTab + n;
                showTab(currentTab);
            }

            function showTab(n) {
                const bootstrapTab = new bootstrap.Tab(tabs[n]);
                bootstrapTab.show();
                $('#prevBtn').css('display', n == 0 ? 'none' : 'inline');
                if (n == (tabs.length - 1)) {
                    $('#nextBtn').css('display', 'none');
                    $('#submitBtn').css('display', 'inline');
                } else {
                    $('#nextBtn').css('display', 'inline').text('Next Step');
                    $('#submitBtn').css('display', 'none');
                }
                // window.scrollTo(0, 0);
            }

            function validateCurrentStep() {
                const currentTabId = $(tabs[currentTab]).attr('href');
                let valid = true;
                $(currentTabId + ' [required]').each(function () {
                    if (!this.checkValidity()) {
                        this.reportValidity();
                        valid = false;
                        return false;
                    }
                });
                return valid;
            }

            function saveStepData() {
                const currentTabId = $(tabs[currentTab]).attr('href').substring(1);

                if (campusId) {
                    const formData = {};
                    const fieldsInStep = getFieldsInStep(currentTabId);

                    fieldsInStep.forEach(fieldName => {
                        formData[fieldName] = getInputValue(fieldName);
                    });

                    showAutoSaveStatus('saving');
                    $.ajax({
                        url: `/admin/organisations/{{ $organisation->id }}/campuses/${campusId}/autosave-tab`,
                        method: 'POST',
                        data: {
                            ...formData,
                            _token: '{{ csrf_token() }}'
                        },
                        success: function () {
                            showAutoSaveStatus('saved');
                            $(tabs[currentTab]).addClass('completed');
                        }
                    });
                }
            }

            function getFieldsInStep(stepId) {
                const fields = [];
                $(`#${stepId} [name]`).each(function () {
                    const name = $(this).attr('name').split('[')[0]; // Get base name
                    if (!fields.includes(name)) fields.push(name);
                });
                return fields;
            }

            function getInputValue(fieldName) {
                // For array/repeater fields
                if ($(`[name^="${fieldName}["]`).length || fieldName === 'bus_routes' || $(`[name="${fieldName}[]"]`).length) {
                    const values = {};
                    const selector = fieldName === 'bus_routes' ? 'input[name="bus_routes[]"]' : `[name^="${fieldName}"]`;

                    $(selector).each(function () {
                        const fullPath = $(this).attr('name');
                        const value = getSingleInputValue($(this));

                        if (fullPath.includes('[')) {
                            const matches = fullPath.match(/(\w+)\[(\d*)\]\[?(\w+)?\]?/);
                            if (matches) {
                                const [_, base, index, key] = matches;
                                if (index === "") {
                                    // Simple array like bus_routes[]
                                    if (!Array.isArray(values[base] || null)) values[base] = [];
                                    if (value) values[base].push(value);
                                } else {
                                    if (!values[index]) values[index] = {};
                                    if (key) {
                                        values[index][key] = value;
                                    } else {
                                        values[index] = value;
                                    }
                                }
                            }
                        } else {
                            values[fullPath] = value;
                        }
                    });

                    if (fieldName === 'bus_routes') return Object.values(values).flat();

                    const multSelect = $(`select[name="${fieldName}[]"]`);
                    if (multSelect.length) return multSelect.val();

                    return Object.keys(values).length > 0 ? (isNaN(Object.keys(values)[0]) ? values[fieldName] : Object.values(values)) : null;
                }

                return getSingleInputValue($(`[name="${fieldName}"]`));
            }

            function getSingleInputValue(el) {
                if (el.is(':checkbox')) {
                    if (el.attr('name').endsWith('[]')) {
                        return el.is(':checked') ? el.val() : null;
                    }
                    return el.is(':checked') ? 1 : 0;
                }
                if (el.is('select[multiple]')) {
                    return el.val();
                }
                if (el.hasClass('editor')) {
                    const editor = tinymce.get(el.attr('id'));
                    return editor ? editor.getContent() : el.val();
                }
                return el.val();
            }

            function showAutoSaveStatus(status) {
                const msgEl = $('#save-message');
                const statusIcon = $('#autosave-status i');
                if (status === 'saving') {
                    msgEl.css('opacity', 1).text('Saving...');
                    statusIcon.removeClass('fa-check-circle text-success').addClass('fa-spinner fa-spin');
                } else {
                    msgEl.text('Saved').animate({ opacity: 0 }, 2000);
                    statusIcon.removeClass('fa-spinner fa-spin').addClass('fa-check-circle text-success');
                }
            }

            window.addRoute = function () {
                $('#bus-routes-container').append(`
                                <div class="input-group mb-2">
                                    <input type="text" name="bus_routes[]" class="form-control" placeholder="Enter route">
                                    <button type="button" class="btn btn-outline-danger remove-route" onclick="$(this).parent().remove()">Remove</button>
                                </div>
                            `);
            }

            // Facility Selection
            window.toggleFacility = function(id) {
                const checkbox = $(`#facility_${id}`);
                const card = checkbox.closest('.facility-card');
                const checkIcon = card.find('.facility-check');

                checkbox.prop('checked', !checkbox.prop('checked'));

                if (checkbox.prop('checked')) {
                    card.addClass('border-primary bg-soft-primary');
                    card.find('.facility-icon i').removeClass('text-muted').addClass('text-primary');
                    card.find('.facility-name').addClass('text-primary fw-bold');
                    checkIcon.show();
                } else {
                    card.removeClass('border-primary bg-soft-primary');
                    card.find('.facility-icon i').addClass('text-muted').removeClass('text-primary');
                    card.find('.facility-name').removeClass('text-primary fw-bold');
                    checkIcon.hide();
                }
            };

            // Initialize checked facilities
            $('input[name="facilities[]"]:checked').each(function() {
                const id = $(this).val();
                $(`#facility_${id}`).prop('checked', false); // temporarily uncheck to let toggle do the UI work
                toggleFacility(id);
            });

            // Class Profile Repeater
            let classProfileIndex = {{ count(old('class_profile', is_array($campus->class_profile) ? $campus->class_profile : [[]])) }};
            $(document).on('click', '#add-class-profile-btn', function() {
                const template = `
                    <div class="class-profile-item border p-4 mb-4 rounded position-relative bg-light">
                        <button type="button" class="btn btn-sm btn-danger position-absolute top-0 end-0 m-2 remove-class-profile-btn"><i class="fas fa-times"></i></button>
                        <div class="row g-3">
                            <div class="col-md-4">
                                <label class="form-label fw-bold">Year</label>
                                <input type="number" name="class_profile[${classProfileIndex}][year]" class="form-control" placeholder="e.g. 2024">
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">Total Students</label>
                                <input type="number" min="0" name="class_profile[${classProfileIndex}][total_students]" class="form-control">
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">Total Faculty</label>
                                <input type="number" min="0" name="class_profile[${classProfileIndex}][total_faculty]" class="form-control">
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">Total Male Students</label>
                                <input type="number" min="0" name="class_profile[${classProfileIndex}][total_male_students]" class="form-control">
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">Total Female Students</label>
                                <input type="number" min="0" name="class_profile[${classProfileIndex}][total_female_students]" class="form-control">
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">Total Students Outside State</label>
                                <input type="number" min="0" name="class_profile[${classProfileIndex}][total_outside_state]" class="form-control">
                            </div>
                        </div>
                    </div>
                `;
                $('#class-profile-container').append(template);
                classProfileIndex++;
            });

            $(document).on('click', '.remove-class-profile-btn', function() {
                $(this).closest('.class-profile-item').remove();
            });

            // Pincode Lookup
            $('#pincode').on('keyup', function () {
                let pin = $(this).val();
                if (pin.length === 6) {
                    $.getJSON(`https://api.postalpincode.in/pincode/${pin}`, function (res) {
                        if (res[0].Status === 'Success') {
                            $('#city').val(res[0].PostOffice[0].District);
                            $('#state').val(res[0].PostOffice[0].State);
                        }
                    });
                }
            });

            showTab(0);
        });
    </script>
@endpush
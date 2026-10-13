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



    @php
        $isSchool = ($organisation->organisation_type_id == 4) || strtolower($organisation->organisationType->title ?? '') === 'school';
    @endphp

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
                id="campus-form" method="POST" enctype="multipart/form-data">
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
                            @if($isSchool)
                                <li class="nav-item"><a class="nav-link" href="#staff-details" role="tab"><i class="fas fa-chalkboard-teacher text-info me-1"></i> Staff Details</a></li>
                                <li class="nav-item"><a class="nav-link" href="#classes" role="tab"><i class="fas fa-chalkboard text-primary me-1"></i> Classes & Fees</a></li>
                                <li class="nav-item"><a class="nav-link" href="#reviews" role="tab"><i class="fas fa-comments text-warning me-1"></i> Reviews</a></li>
                                <li class="nav-item"><a class="nav-link" href="#events-activities" role="tab"><i class="fas fa-calendar-alt text-success me-1"></i> Events & Activities</a></li>
                                <li class="nav-item"><a class="nav-link" href="#alumni" role="tab"><i class="fas fa-user-friends text-secondary me-1"></i> Alumni</a></li>
                                <li class="nav-item"><a class="nav-link" href="#clubs-committees" role="tab"><i class="fas fa-users-cog text-danger me-1"></i> Clubs & Committees</a></li>
                                <li class="nav-item"><a class="nav-link" href="#faqs" role="tab"><i class="fas fa-question-circle text-primary me-1"></i> FAQs</a></li>
                            @endif
                            <li class="nav-item"><a class="nav-link" href="#documents-tab" role="tab"><i class="fas fa-file-pdf text-danger me-1"></i> Documents</a></li>
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
                                    @if($isSchool)
                                    <div class="col-md-6">
                                        <label class="form-label">School Type</label>
                                        <select name="campus_type_new_id[]" class="form-select select2" multiple data-placeholder="Select School Type(s)">
                                            @if(isset($schoolTypes))
                                                @foreach($schoolTypes as $st)
                                                    <option value="{{ $st->id }}" {{ in_array($st->id, old('campus_type_new_id', is_array($campus->campus_type_new_id) ? $campus->campus_type_new_id : (is_string($campus->campus_type_new_id) ? json_decode($campus->campus_type_new_id, true) ?? [] : [$campus->campus_type_new_id]))) ? 'selected' : '' }}>
                                                        {{ $st->title }}
                                                    </option>
                                                @endforeach
                                            @endif
                                        </select>
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label">UDISE Code</label>
                                        <input type="text" name="udise_code" class="form-control" placeholder="e.g. 04010100101"
                                            value="{{ old('udise_code', $campus->udise_code) }}">
                                        <small class="text-muted">Unified District Information System for Education code</small>
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label">Affiliation Board</label>
                                        <select name="affiliation_board" class="form-select select2" data-placeholder="Select Affiliation Board">
                                            <option value="">Select Affiliation Board</option>
                                            @foreach(['CBSE', 'CISCE (ICSE / ISC)', 'State Board', 'IB (International Baccalaureate)', 'Cambridge (IGCSE)', 'NIOS', 'Other'] as $board)
                                                <option value="{{ $board }}" {{ old('affiliation_board', $campus->affiliation_board) == $board ? 'selected' : '' }}>{{ $board }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                    @endif
                                    @if($organisation->organisation_type_id == 3)
                                    <div class="col-md-6">
                                        <label class="form-label">Coaching Category</label>
                                        <select name="coaching_category_ids[]" class="form-select select2" multiple data-placeholder="Select Coaching Category">
                                            @if(isset($coachingCategories))
                                                @php
                                                    $selectedCategories = old('coaching_category_ids', is_array($campus->coaching_category_ids) ? $campus->coaching_category_ids : (is_string($campus->coaching_category_ids) ? json_decode($campus->coaching_category_ids, true) ?? [] : [$campus->coaching_category_ids]));
                                                    if (!is_array($selectedCategories)) {
                                                        $selectedCategories = [];
                                                    }
                                                @endphp
                                                @foreach($coachingCategories as $cc)
                                                    <option value="{{ $cc->id }}" {{ in_array($cc->id, $selectedCategories) ? 'selected' : '' }}>
                                                        {{ $cc->title }}
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
                                    @if($isSchool)
                                    <div class="col-md-4">
                                        <label class="form-label">Location Type</label>
                                        <select name="location_type" class="form-select">
                                            <option value="">Select Location Type</option>
                                            @foreach(['Urban', 'Semi-Urban', 'Rural'] as $lType)
                                                <option value="{{ $lType }}" {{ old('location_type', $campus->location_type) == $lType ? 'selected' : '' }}>{{ $lType }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                    @endif
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
                                    <div class="col-md-6">
                                        <label class="form-label">Target Classes</label>
                                        <select name="target_classes[]" class="form-select select2" multiple>
                                            @php
                                                $selectedClasses = is_array($campus->target_classes) ? $campus->target_classes : (json_decode($campus->target_classes, true) ?: []);
                                                $targetClassList = isset($academicClasses) && $academicClasses->isNotEmpty()
                                                    ? $academicClasses->pluck('name')->toArray()
                                                    : \App\Models\AcademicClass::where('status', 1)->orderBy('sort_order')->orderBy('id')->pluck('name')->toArray();
                                                if (empty($targetClassList)) {
                                                    $targetClassList = ['Playgroup', 'Nursery', 'LKG', 'UKG', 'Class 1', 'Class 2', 'Class 3', 'Class 4', 'Class 5', 'Class 6', 'Class 7', 'Class 8', 'Class 9', 'Class 10', 'Class 11', 'Class 12', 'Dropper'];
                                                }
                                            @endphp
                                            @foreach($targetClassList as $cls)
                                                @php
                                                    $numOnly = str_replace('Class ', '', $cls);
                                                    $isSel = in_array($cls, (array)$selectedClasses) || in_array((string)$numOnly, (array)$selectedClasses);
                                                @endphp
                                                <option value="{{ $cls }}" {{ $isSel ? 'selected' : '' }}>{{ $cls }}</option>
                                            @endforeach
                                        </select>
                                    </div>
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
                                                $isChecked = in_array($facility->id, $campus->facilities) || in_array($facility->name, $campus->facilities);
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
                                            value="{{ $campus->campus_email }}" placeholder="e.g. info@school.edu">
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label">Campus Website</label>
                                        <input type="url" name="campus_website" class="form-control"
                                            value="{{ $campus->campus_website }}" placeholder="https://example.com">
                                    </div>
                                    <div class="col-12">
                                        <label class="form-label">Contact Numbers (Comma separated)</label>
                                        <input type="text" name="campus_contact_numbers" class="form-control"
                                            value="{{ is_array($campus->campus_contact_numbers) ? implode(', ', $campus->campus_contact_numbers) : $campus->campus_contact_numbers }}"
                                            placeholder="e.g. +91 9876543210, 011-23456789">
                                    </div>

                                    @if($isSchool)
                                    <div class="col-12 mt-4">
                                        <h6 class="text-primary fw-bold mb-2 border-bottom pb-2">
                                            <i class="fas fa-share-alt me-1"></i> Social Media Profiles
                                        </h6>
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label">Facebook URL</label>
                                        <div class="input-group">
                                            <span class="input-group-text bg-white"><i class="fab fa-facebook text-primary"></i></span>
                                            <input type="url" name="facebook_url" class="form-control"
                                                value="{{ old('facebook_url', $campus->facebook_url) }}"
                                                placeholder="https://facebook.com/your-school">
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label">Instagram URL</label>
                                        <div class="input-group">
                                            <span class="input-group-text bg-white"><i class="fab fa-instagram text-danger"></i></span>
                                            <input type="url" name="instagram_url" class="form-control"
                                                value="{{ old('instagram_url', $campus->instagram_url) }}"
                                                placeholder="https://instagram.com/your-school">
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label">LinkedIn URL</label>
                                        <div class="input-group">
                                            <span class="input-group-text bg-white"><i class="fab fa-linkedin text-info"></i></span>
                                            <input type="url" name="linkedin_url" class="form-control"
                                                value="{{ old('linkedin_url', $campus->linkedin_url) }}"
                                                placeholder="https://linkedin.com/school/your-school">
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label">YouTube URL</label>
                                        <div class="input-group">
                                            <span class="input-group-text bg-white"><i class="fab fa-youtube text-danger"></i></span>
                                            <input type="url" name="youtube_url" class="form-control"
                                                value="{{ old('youtube_url', $campus->youtube_url) }}"
                                                placeholder="https://youtube.com/@your-school">
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label">Twitter / X URL</label>
                                        <div class="input-group">
                                            <span class="input-group-text bg-white"><i class="fab fa-x-twitter"></i></span>
                                            <input type="url" name="twitter_url" class="form-control"
                                                value="{{ old('twitter_url', $campus->twitter_url) }}"
                                                placeholder="https://x.com/your-school">
                                        </div>
                                    </div>
                                    @endif
                                </div>
                            </div>
                            
                            <!-- 9. Class Profile -->
                            <div class="tab-pane fade" id="class-profile" role="tabpanel">
                                @if($isSchool)
                                <div class="card border mb-4 p-3 bg-white shadow-sm">
                                    <div class="d-flex align-items-center justify-content-between mb-3 border-bottom pb-2">
                                        <h6 class="text-primary fw-bold mb-0">
                                            <i class="fas fa-chart-pie me-1"></i> Student & Teacher Statistics (School Level)
                                        </h6>
                                        <span class="badge bg-soft-success text-success px-2 py-1">
                                            <i class="fas fa-check-circle me-1"></i> RTE Standard: 30:1 - 35:1
                                        </span>
                                    </div>
                                    <div class="row g-3">
                                        <div class="col-md-3">
                                            <label class="form-label fw-bold">Total Enrolled Students</label>
                                            <input type="number" min="0" name="total_enrolled_students" id="school_total_students" class="form-control"
                                                value="{{ old('total_enrolled_students', $campus->total_enrolled_students) }}" placeholder="e.g. 1242">
                                        </div>
                                        <div class="col-md-3">
                                            <label class="form-label">Enrolled Boys</label>
                                            <input type="number" min="0" name="enrolled_boys" id="school_enrolled_boys" class="form-control"
                                                value="{{ old('enrolled_boys', $campus->enrolled_boys) }}" placeholder="e.g. 659">
                                        </div>
                                        <div class="col-md-3">
                                            <label class="form-label">Enrolled Girls</label>
                                            <input type="number" min="0" name="enrolled_girls" id="school_enrolled_girls" class="form-control"
                                                value="{{ old('enrolled_girls', $campus->enrolled_girls) }}" placeholder="e.g. 583">
                                        </div>
                                        <div class="col-md-3">
                                            <label class="form-label fw-bold">Pupil-Teacher Ratio (PTR)</label>
                                            <input type="text" name="pupil_teacher_ratio" id="school_ptr" class="form-control"
                                                value="{{ old('pupil_teacher_ratio', $campus->pupil_teacher_ratio) }}" placeholder="e.g. 5:1">
                                            <small class="text-muted">RTE standard: 30-35 students per teacher</small>
                                        </div>
                                    </div>
                                </div>
                                @endif

                                <div class="d-flex align-items-center justify-content-between mb-3">
                                    <h5 class="text-primary mb-0">Class Profile (Year-wise)</h5>
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

                            @if($isSchool)
                            <!-- 10. Staff Details -->
                            <div class="tab-pane" id="staff-details" role="tabpanel">
                                <div class="d-flex align-items-center justify-content-between mb-3 border-bottom pb-2">
                                    <div>
                                        <h5 class="text-primary mb-1"><i class="fas fa-chalkboard-teacher me-1"></i> School Teacher Details & Teaching Staff</h5>
                                        <p class="text-muted small mb-0">Under official UDISE+ and RTE guidelines, enter institutional leadership and staff counts.</p>
                                    </div>
                                    @if(!empty($campus->pupil_teacher_ratio))
                                    <span class="badge bg-soft-primary text-primary px-3 py-2 fs-6">
                                        PTR {{ $campus->pupil_teacher_ratio }} <i class="fas fa-check-circle text-success ms-1"></i> RTE Compliant
                                    </span>
                                    @endif
                                </div>

                                <div class="row g-3">
                                    <div class="col-md-6">
                                        <label class="form-label fw-bold">Headmaster / Principal</label>
                                        <input type="text" name="headmaster_principal" class="form-control"
                                            value="{{ old('headmaster_principal', $campus->headmaster_principal) }}"
                                            placeholder="e.g. Mamta Mehta">
                                        <small class="text-muted">Institutional leader name</small>
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label fw-bold">Total Appointed Teachers</label>
                                        <input type="number" min="0" name="total_appointed_teachers" id="total_appointed_teachers" class="form-control"
                                            value="{{ old('total_appointed_teachers', $campus->total_appointed_teachers) }}"
                                            placeholder="e.g. 245">
                                        <small class="text-muted">Total number of appointed teaching staff</small>
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label">Male Staff (Teachers)</label>
                                        <input type="number" min="0" name="male_staff" id="male_staff" class="form-control"
                                            value="{{ old('male_staff', $campus->male_staff) }}"
                                            placeholder="e.g. 110">
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label">Female Staff (Teachers)</label>
                                        <input type="number" min="0" name="female_staff" id="female_staff" class="form-control"
                                            value="{{ old('female_staff', $campus->female_staff) }}"
                                            placeholder="e.g. 135">
                                    </div>
                                    <div class="col-12 mt-4">
                                        <div class="alert alert-info d-flex align-items-center mb-0" role="alert">
                                            <i class="fas fa-info-circle fa-2x me-3"></i>
                                            <div>
                                                <strong>Teacher Details & Verification:</strong> Under official UDISE+ and RTE guidelines, institutional leadership and verified teacher counts are listed above. Ensure values align with state HRMS portals or school records.
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- 11. Classes & Academics -->
                            <div class="tab-pane" id="classes" role="tabpanel">
                                <div class="d-flex align-items-center justify-content-between mb-3 border-bottom pb-2">
                                    <div>
                                        <h5 class="text-primary mb-1"><i class="fas fa-graduation-cap me-1"></i> Class Offerings & Fees</h5>
                                        <p class="text-muted small mb-0">Add class-wise details including curriculum, fee structure, admission process, and exam preparation.</p>
                                    </div>
                                    <button type="button" class="btn btn-sm btn-primary" id="add-class-btn">
                                        <i class="fas fa-plus me-1"></i> Add Class
                                    </button>
                                </div>

                                <div id="classes-container">
                                    @php $classes = old('school_classes', is_array($campus->school_classes) ? $campus->school_classes : []); @endphp
                                    @forelse($classes as $cIndex => $sClass)
                                    <div class="class-item card border mb-3 bg-light position-relative">
                                        <button type="button" class="btn btn-sm btn-danger position-absolute top-0 end-0 m-2 remove-class-btn">
                                            <i class="fas fa-times"></i>
                                        </button>
                                        <div class="card-body">
                                            <div class="row g-3">
                                                <div class="col-md-6">
                                                    <label class="form-label fw-bold">Class / Grade <span class="text-danger">*</span></label>
                                                    @php
                                                        $masterClasses = isset($academicClasses) && $academicClasses->isNotEmpty()
                                                            ? $academicClasses->pluck('name')->toArray()
                                                            : \App\Models\AcademicClass::where('status', 1)->orderBy('sort_order')->orderBy('id')->pluck('name')->toArray();
                                                        if (empty($masterClasses)) {
                                                            $masterClasses = [
                                                                'Playgroup', 'Nursery', 'LKG', 'UKG',
                                                                'Class 1', 'Class 2', 'Class 3', 'Class 4', 'Class 5',
                                                                'Class 6', 'Class 7', 'Class 8', 'Class 9', 'Class 10',
                                                                'Class 11', 'Class 12', 'Dropper'
                                                            ];
                                                        }
                                                        $currentVal = $sClass['class_name'] ?? '';
                                                    @endphp
                                                    <select name="school_classes[{{$cIndex}}][class_name]" class="form-select select2-class-item">
                                                        <option value="">-- Select Class / Grade --</option>
                                                        @foreach($masterClasses as $mClass)
                                                            <option value="{{ $mClass }}" {{ $currentVal == $mClass ? 'selected' : '' }}>{{ $mClass }}</option>
                                                        @endforeach
                                                        @if(!empty($currentVal) && !in_array($currentVal, $masterClasses))
                                                            <option value="{{ $currentVal }}" selected>{{ $currentVal }}</option>
                                                        @endif
                                                    </select>
                                                </div>
                                                <div class="col-12">
                                                    <label class="form-label fw-bold">Curriculum / Board</label>
                                                    <textarea name="school_classes[{{$cIndex}}][curriculum]" id="curriculum_{{$cIndex}}" class="form-control editor" rows="4"
                                                        placeholder="e.g. CBSE, ICSE, Cambridge / IGCSE, State Board">{{ $sClass['curriculum'] ?? '' }}</textarea>
                                                </div>
                                                <div class="col-12">
                                                    <label class="form-label fw-bold">Fee & Other Charges</label>
                                                    <textarea name="school_classes[{{$cIndex}}][fee_structure]" id="fee_structure_{{$cIndex}}" class="form-control editor" rows="4"
                                                        placeholder="e.g. Tuition Fee: ₹45,000/yr, Admission Fee: ₹5,000, Activity/Lab Charges: ₹3,000...">{{ $sClass['fee_structure'] ?? '' }}</textarea>
                                                </div>
                                                <div class="col-12">
                                                    <label class="form-label fw-bold">Admission Process</label>
                                                    <textarea name="school_classes[{{$cIndex}}][admission_process]" id="admission_process_{{$cIndex}}" class="form-control editor" rows="4"
                                                        placeholder="Eligibility, age criteria, entrance test/interview, documents required...">{{ $sClass['admission_process'] ?? '' }}</textarea>
                                                </div>
                                                <div class="col-12">
                                                    <label class="form-label fw-bold">Exam Preparation</label>
                                                    <textarea name="school_classes[{{$cIndex}}][exam_preparation]" id="exam_preparation_{{$cIndex}}" class="form-control editor" rows="4"
                                                        placeholder="e.g. Olympiads, NTSE, Board Exam Mentorship, JEE/NEET Foundation...">{{ $sClass['exam_preparation'] ?? '' }}</textarea>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    @empty
                                    <div class="text-center py-4 border rounded bg-white" id="no-classes-msg">
                                        <i class="fas fa-chalkboard fa-3x text-muted mb-2"></i>
                                        <p class="text-muted mb-0">No classes added yet. Click <strong>"Add Class"</strong> to add grades, fees, curriculum, and admission details.</p>
                                    </div>
                                    @endforelse
                                </div>
                            </div>

                            <!-- 12. Reviews -->
                            <div class="tab-pane" id="reviews" role="tabpanel">
                                <div class="d-flex align-items-center justify-content-between mb-3 border-bottom pb-2">
                                    <div>
                                        <h5 class="text-primary mb-1"><i class="fas fa-comments me-1"></i> Parent & Alumni Reviews</h5>
                                        <p class="text-muted small mb-0">Record testimonials and verified reviews from parents, students, and alumni.</p>
                                    </div>
                                    <button type="button" class="btn btn-sm btn-primary" id="add-review-btn">
                                        <i class="fas fa-plus me-1"></i> Add Review
                                    </button>
                                </div>

                                <div id="reviews-container">
                                    @php $reviews = old('reviews', is_array($campus->reviews) ? $campus->reviews : []); @endphp
                                    @forelse($reviews as $rIndex => $rev)
                                    <div class="review-item card border mb-3 bg-light position-relative">
                                        <button type="button" class="btn btn-sm btn-danger position-absolute top-0 end-0 m-2 remove-review-btn">
                                            <i class="fas fa-times"></i>
                                        </button>
                                        <div class="card-body">
                                            <div class="row g-3">
                                                <div class="col-md-4">
                                                    <label class="form-label fw-bold">Reviewer Name</label>
                                                    <input type="text" name="reviews[{{$rIndex}}][reviewer_name]" class="form-control"
                                                        value="{{ $rev['reviewer_name'] ?? '' }}" placeholder="e.g. Ramesh Kumar">
                                                </div>
                                                <div class="col-md-3">
                                                    <label class="form-label">Reviewer Role</label>
                                                    <select name="reviews[{{$rIndex}}][reviewer_type]" class="form-select">
                                                        @foreach(['Parent', 'Alumni', 'Student', 'Guardian', 'Other'] as $rType)
                                                            <option value="{{ $rType }}" {{ ($rev['reviewer_type'] ?? '') == $rType ? 'selected' : '' }}>{{ $rType }}</option>
                                                        @endforeach
                                                    </select>
                                                </div>
                                                <div class="col-md-2">
                                                    <label class="form-label">Rating (1 to 5)</label>
                                                    <select name="reviews[{{$rIndex}}][rating]" class="form-select">
                                                        @for($rate = 5; $rate >= 1; $rate--)
                                                            <option value="{{ $rate }}" {{ ($rev['rating'] ?? 5) == $rate ? 'selected' : '' }}>{{ $rate }} ★</option>
                                                        @endfor
                                                    </select>
                                                </div>
                                                <div class="col-md-3">
                                                    <label class="form-label">Review Date / Year</label>
                                                    <input type="text" name="reviews[{{$rIndex}}][date]" class="form-control"
                                                        value="{{ $rev['date'] ?? '' }}" placeholder="e.g. Oct 2024">
                                                </div>
                                                <div class="col-md-12">
                                                    <label class="form-label">Review Title (Optional)</label>
                                                    <input type="text" name="reviews[{{$rIndex}}][title]" class="form-control"
                                                        value="{{ $rev['title'] ?? '' }}" placeholder="e.g. Excellent academic support and caring teachers">
                                                </div>
                                                <div class="col-12">
                                                    <label class="form-label fw-bold">Review Content</label>
                                                    <textarea name="reviews[{{$rIndex}}][comment]" class="form-control" rows="2"
                                                        placeholder="Share experience or feedback about the school...">{{ $rev['comment'] ?? '' }}</textarea>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    @empty
                                    <div class="text-center py-4 border rounded bg-white" id="no-reviews-msg">
                                        <i class="fas fa-comment-dots fa-3x text-muted mb-2"></i>
                                        <p class="text-muted mb-0">No reviews submitted yet. Click <strong>"Add Review"</strong> to create one.</p>
                                    </div>
                                    @endforelse
                                </div>
                            </div>

                            <!-- 13. Events & Activities -->
                            <div class="tab-pane" id="events-activities" role="tabpanel">
                                <div class="d-flex align-items-center justify-content-between mb-3 border-bottom pb-2">
                                    <div>
                                        <h5 class="text-primary mb-1"><i class="fas fa-calendar-alt me-1"></i> Events & Activities</h5>
                                        <p class="text-muted small mb-0">Record co-curricular events, annual functions, sports meets, and student activities.</p>
                                    </div>
                                    <button type="button" class="btn btn-sm btn-primary" id="add-event-btn">
                                        <i class="fas fa-plus me-1"></i> Add Event / Activity
                                    </button>
                                </div>

                                <div id="events-container">
                                    @php $events = old('events_and_activities', is_array($campus->events_and_activities) ? $campus->events_and_activities : []); @endphp
                                    @forelse($events as $eIndex => $event)
                                    <div class="event-item card border mb-3 bg-light position-relative">
                                        <button type="button" class="btn btn-sm btn-danger position-absolute top-0 end-0 m-2 remove-event-btn">
                                            <i class="fas fa-times"></i>
                                        </button>
                                        <div class="card-body">
                                            <div class="row g-3">
                                                <div class="col-md-5">
                                                    <label class="form-label fw-bold">Event Title</label>
                                                    <input type="text" name="events_and_activities[{{$eIndex}}][title]" class="form-control"
                                                        value="{{ $event['title'] ?? '' }}" placeholder="e.g. Annual Sports Day 2024">
                                                </div>
                                                <div class="col-md-4">
                                                    <label class="form-label">Category</label>
                                                    <select name="events_and_activities[{{$eIndex}}][category]" class="form-select">
                                                        @foreach(['Sports', 'Cultural', 'Academic', 'Annual Day', 'Science Exhibition', 'Workshop', 'Competition', 'Community Outreach'] as $eCat)
                                                            <option value="{{ $eCat }}" {{ ($event['category'] ?? '') == $eCat ? 'selected' : '' }}>{{ $eCat }}</option>
                                                        @endforeach
                                                    </select>
                                                </div>
                                                <div class="col-md-3">
                                                    <label class="form-label">Event Date / Frequency</label>
                                                    <input type="text" name="events_and_activities[{{$eIndex}}][date]" class="form-control"
                                                        value="{{ $event['date'] ?? '' }}" placeholder="e.g. November 2024 / Annual">
                                                </div>
                                                <div class="col-12">
                                                    <label class="form-label">Description & Highlights</label>
                                                    <textarea name="events_and_activities[{{$eIndex}}][description]" class="form-control" rows="2"
                                                        placeholder="Brief summary of activities, participants, and highlights...">{{ $event['description'] ?? '' }}</textarea>
                                                </div>
                                                <div class="col-12">
                                                    <label class="form-label fw-bold"><i class="fas fa-images text-primary me-1"></i> Event Photos & Images</label>
                                                    <input type="file" name="events_and_activities[{{$eIndex}}][images][]" multiple accept="image/*" class="form-control event-images-input">
                                                    <small class="text-muted d-block mt-1"><i class="fas fa-info-circle me-1"></i>You can select multiple photos (JPG, PNG, WebP) for this event.</small>
                                                    
                                                    <div class="event-image-previews d-flex flex-wrap gap-2 mt-2">
                                                        @php $eventImgs = isset($event['images']) && is_array($event['images']) ? $event['images'] : []; @endphp
                                                        @foreach($eventImgs as $img)
                                                            @if(!empty($img))
                                                            <div class="position-relative d-inline-block border rounded p-1 bg-white shadow-sm event-img-thumb-box" style="width: 85px; height: 85px;">
                                                                <img src="{{ \Illuminate\Support\Str::startsWith($img, 'http') ? $img : asset($img) }}" class="rounded w-100 h-100" style="object-fit: cover;" alt="Event Photo">
                                                                <input type="hidden" name="events_and_activities[{{$eIndex}}][existing_images][]" value="{{ $img }}">
                                                                <button type="button" class="btn btn-danger btn-xs position-absolute top-0 end-0 m-1 p-0 rounded-circle remove-event-img-btn" style="width: 20px; height: 20px; font-size: 10px; line-height: 1;" title="Remove image">
                                                                    <i class="fas fa-times"></i>
                                                                </button>
                                                            </div>
                                                            @endif
                                                        @endforeach
                                                    </div>
                                                    <div class="selected-files-preview d-flex flex-wrap gap-2 mt-2"></div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    @empty
                                    <div class="text-center py-4 border rounded bg-white" id="no-events-msg">
                                        <i class="fas fa-calendar-times fa-3x text-muted mb-2"></i>
                                        <p class="text-muted mb-0">No events added yet. Click <strong>"Add Event / Activity"</strong> to create one.</p>
                                    </div>
                                    @endforelse
                                </div>
                            </div>

                            <!-- 14. Alumni -->
                            <div class="tab-pane" id="alumni" role="tabpanel">
                                <div class="mb-3 border-bottom pb-2">
                                    <h5 class="text-primary mb-1"><i class="fas fa-user-friends me-1"></i> Alumni Network</h5>
                                    <p class="text-muted small mb-0">Manage alumni association details, engagement network, and distinguished alumni profiles.</p>
                                </div>

                                <div class="row g-3 mb-4">
                                    <div class="col-12">
                                        <label class="form-label fw-bold">Alumni Association / Network Details</label>
                                        <textarea name="alumni_details" id="alumni_details" class="editor"
                                            placeholder="Describe alumni association activities, reunion events, engagement network, and contact channels...">{{ old('alumni_details', $campus->alumni_details) }}</textarea>
                                    </div>
                                </div>

                                <div class="d-flex align-items-center justify-content-between mb-3 border-bottom pb-2">
                                    <h6 class="text-primary fw-bold mb-0"><i class="fas fa-award me-1"></i> Notable Alumni</h6>
                                    <button type="button" class="btn btn-sm btn-outline-primary" id="add-alumni-btn">
                                        <i class="fas fa-plus me-1"></i> Add Notable Alumni
                                    </button>
                                </div>

                                <div id="alumni-container">
                                    @php $alumniList = old('notable_alumni', is_array($campus->notable_alumni) ? $campus->notable_alumni : []); @endphp
                                    @forelse($alumniList as $aIndex => $alum)
                                    <div class="alumni-item card border mb-3 bg-light position-relative">
                                        <button type="button" class="btn btn-sm btn-danger position-absolute top-0 end-0 m-2 remove-alumni-btn">
                                            <i class="fas fa-times"></i>
                                        </button>
                                        <div class="card-body">
                                            <div class="row g-3">
                                                <div class="col-md-4">
                                                    <label class="form-label fw-bold">Alumni Name</label>
                                                    <input type="text" name="notable_alumni[{{$aIndex}}][name]" class="form-control"
                                                        value="{{ $alum['name'] ?? '' }}" placeholder="e.g. Dr. Anjali Sharma">
                                                </div>
                                                <div class="col-md-3">
                                                    <label class="form-label">Batch / Passing Year</label>
                                                    <input type="text" name="notable_alumni[{{$aIndex}}][batch]" class="form-control"
                                                        value="{{ $alum['batch'] ?? '' }}" placeholder="e.g. Batch of 2015">
                                                </div>
                                                <div class="col-md-5">
                                                    <label class="form-label">Profession / Notable Achievement</label>
                                                    <input type="text" name="notable_alumni[{{$aIndex}}][profession]" class="form-control"
                                                        value="{{ $alum['profession'] ?? '' }}" placeholder="e.g. IAS Officer / Scientist at ISRO">
                                                </div>
                                                <div class="col-12">
                                                    <label class="form-label">Testimonial / Achievement Details</label>
                                                    <textarea name="notable_alumni[{{$aIndex}}][testimonial]" class="form-control" rows="2"
                                                        placeholder="Brief words or achievements...">{{ $alum['testimonial'] ?? '' }}</textarea>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    @empty
                                    <div class="text-center py-4 border rounded bg-white" id="no-alumni-msg">
                                        <i class="fas fa-user-graduate fa-3x text-muted mb-2"></i>
                                        <p class="text-muted mb-0">No notable alumni added yet. Click <strong>"Add Notable Alumni"</strong> to create one.</p>
                                    </div>
                                    @endforelse
                                </div>
                            </div>

                            <!-- 15. Clubs & Committees -->
                            <div class="tab-pane" id="clubs-committees" role="tabpanel">
                                <div class="d-flex align-items-center justify-content-between mb-3 border-bottom pb-2">
                                    <div>
                                        <h5 class="text-primary mb-1"><i class="fas fa-users-cog me-1"></i> Clubs & Committees</h5>
                                        <p class="text-muted small mb-0">Manage school student clubs, academic societies, and statutory institutional committees.</p>
                                    </div>
                                    <button type="button" class="btn btn-sm btn-primary" id="add-club-btn">
                                        <i class="fas fa-plus me-1"></i> Add Club / Committee
                                    </button>
                                </div>

                                <div id="clubs-container">
                                    @php $clubs = old('clubs_and_committees', is_array($campus->clubs_and_committees) ? $campus->clubs_and_committees : []); @endphp
                                    @forelse($clubs as $cIndex => $club)
                                    <div class="club-item card border mb-3 bg-light position-relative">
                                        <button type="button" class="btn btn-sm btn-danger position-absolute top-0 end-0 m-2 remove-club-btn">
                                            <i class="fas fa-times"></i>
                                        </button>
                                        <div class="card-body">
                                            <div class="row g-3">
                                                <div class="col-md-4">
                                                    <label class="form-label fw-bold">Club / Committee Name</label>
                                                    <input type="text" name="clubs_and_committees[{{$cIndex}}][name]" class="form-control"
                                                        value="{{ $club['name'] ?? '' }}" placeholder="e.g. Eco Club / POSH Committee">
                                                </div>
                                                <div class="col-md-4">
                                                    <label class="form-label">Type / Category</label>
                                                    <select name="clubs_and_committees[{{$cIndex}}][type]" class="form-select">
                                                        @foreach(['Student Club', 'Statutory Committee', 'Academic Society', 'Sports Committee', 'Cultural Society', 'Safety & Discipline Committee'] as $cType)
                                                            <option value="{{ $cType }}" {{ ($club['type'] ?? '') == $cType ? 'selected' : '' }}>{{ $cType }}</option>
                                                        @endforeach
                                                    </select>
                                                </div>
                                                <div class="col-md-4">
                                                    <label class="form-label">Coordinator / Faculty In-Charge</label>
                                                    <input type="text" name="clubs_and_committees[{{$cIndex}}][coordinator]" class="form-control"
                                                        value="{{ $club['coordinator'] ?? '' }}" placeholder="e.g. Mrs. Sunita Sharma">
                                                </div>
                                                <div class="col-12">
                                                    <label class="form-label">Purpose & Key Activities</label>
                                                    <textarea name="clubs_and_committees[{{$cIndex}}][description]" class="form-control" rows="2"
                                                        placeholder="Brief purpose, roles, and regular activities...">{{ $club['description'] ?? '' }}</textarea>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    @empty
                                    <div class="text-center py-4 border rounded bg-white" id="no-clubs-msg">
                                        <i class="fas fa-users-slash fa-3x text-muted mb-2"></i>
                                        <p class="text-muted mb-0">No clubs or committees added yet. Click <strong>"Add Club / Committee"</strong> to create one.</p>
                                    </div>
                                    @endforelse
                                </div>
                            </div>

                            <!-- 16. FAQs -->
                            <div class="tab-pane" id="faqs" role="tabpanel">
                                <div class="d-flex align-items-center justify-content-between mb-3 border-bottom pb-2">
                                    <div>
                                        <h5 class="text-primary mb-1"><i class="fas fa-question-circle me-1"></i> Frequently Asked Questions (FAQ Section)</h5>
                                        <p class="text-muted small mb-0">Provide answers to common queries regarding UDISE code, medium of instruction, fees, and admissions.</p>
                                    </div>
                                    <button type="button" class="btn btn-sm btn-primary" id="add-faq-btn">
                                        <i class="fas fa-plus me-1"></i> Add FAQ
                                    </button>
                                </div>

                                <div id="faqs-container">
                                    @php $faqs = old('faqs', is_array($campus->faqs) ? $campus->faqs : []); @endphp
                                    @forelse($faqs as $fIndex => $faq)
                                    <div class="faq-item card border mb-3 bg-light position-relative">
                                        <button type="button" class="btn btn-sm btn-danger position-absolute top-0 end-0 m-2 remove-faq-btn">
                                            <i class="fas fa-times"></i>
                                        </button>
                                        <div class="card-body">
                                            <div class="row g-3">
                                                <div class="col-12">
                                                    <label class="form-label fw-bold">Question</label>
                                                    <input type="text" name="faqs[{{$fIndex}}][question]" class="form-control"
                                                        value="{{ $faq['question'] ?? '' }}"
                                                        placeholder="e.g. What is the official UDISE code and status of the school?">
                                                </div>
                                                <div class="col-12">
                                                    <label class="form-label fw-bold">Answer</label>
                                                    <textarea name="faqs[{{$fIndex}}][answer]" class="form-control" rows="3"
                                                        placeholder="Provide a clear, detailed answer...">{{ $faq['answer'] ?? '' }}</textarea>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    @empty
                                    <div class="text-center py-4 border rounded bg-white" id="no-faqs-msg">
                                        <i class="fas fa-question fa-3x text-muted mb-2"></i>
                                        <p class="text-muted mb-0">No FAQs added yet. Click <strong>"Add FAQ"</strong> to create common questions and answers.</p>
                                    </div>
                                    @endforelse
                                </div>
                            </div>
                            @endif

                            <!-- 10. Documents -->
                            <div class="tab-pane" id="documents-tab" role="tabpanel">
                                @include('admin.organisations.campuses.partials.documents')
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
                    if (typeof tinymce !== 'undefined') tinymce.triggerSave();
                    if (index > currentTab && !validateCurrentStep()) {
                        return false;
                    }
                    saveStepData();
                    currentTab = index;
                    showTab(currentTab);
                });
            });

            window.nextPrev = function (n) {
                if (typeof tinymce !== 'undefined') tinymce.triggerSave();
                if (n == 1 && !validateCurrentStep()) return false;
                saveStepData();
                currentTab = currentTab + n;
                showTab(currentTab);
            }

            $('#campus-form').on('submit', function () {
                if (typeof tinymce !== 'undefined') tinymce.triggerSave();
            });

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
                if (currentTabId === 'documents-tab') return;

                if (campusId) {
                    if (currentTabId === 'events-activities') {
                        const fd = new FormData();
                        fd.append('_token', '{{ csrf_token() }}');

                        $('#events-activities').find('input, select, textarea').each(function () {
                            const name = $(this).attr('name');
                            if (!name) return;

                            if ($(this).is(':file')) {
                                const files = this.files;
                                if (files && files.length > 0) {
                                    for (let i = 0; i < files.length; i++) {
                                        fd.append(name, files[i]);
                                    }
                                }
                            } else {
                                fd.append(name, $(this).val());
                            }
                        });

                        showAutoSaveStatus('saving');
                        $.ajax({
                            url: `/admin/organisations/{{ $organisation->id }}/campuses/${campusId}/autosave-tab`,
                            method: 'POST',
                            data: fd,
                            processData: false,
                            contentType: false,
                            success: function (response) {
                                showAutoSaveStatus('saved');
                                $(tabs[currentTab]).addClass('completed');
                                if (response && response.events) {
                                    syncEventImagesUI(response.events);
                                }
                            },
                            error: function () {
                                showAutoSaveStatus('saved');
                            }
                        });
                        return;
                    }

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
                // Ensure repeater base names are included even if all cards are removed
                if (stepId === 'classes' && !fields.includes('school_classes')) fields.push('school_classes');
                if (stepId === 'reviews' && !fields.includes('reviews')) fields.push('reviews');
                if (stepId === 'events-activities' && !fields.includes('events_and_activities')) fields.push('events_and_activities');
                if (stepId === 'alumni' && !fields.includes('notable_alumni')) fields.push('notable_alumni');
                if (stepId === 'clubs-committees' && !fields.includes('clubs_and_committees')) fields.push('clubs_and_committees');
                if (stepId === 'faqs' && !fields.includes('faqs')) fields.push('faqs');
                return fields;
            }

            function getInputValue(fieldName) {
                const repeaterKeys = ['school_classes', 'reviews', 'events_and_activities', 'notable_alumni', 'clubs_and_committees', 'faqs'];
                // For array/repeater fields
                if ($(`[name^="${fieldName}["]`).length || fieldName === 'bus_routes' || $(`[name="${fieldName}[]"]`).length || repeaterKeys.includes(fieldName)) {
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

                    if (repeaterKeys.includes(fieldName) && Object.keys(values).length === 0) {
                        return [];
                    }

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

            // Form submit handler - trigger TinyMCE save
            $('#campus-form').on('submit', function () {
                if (typeof tinymce !== 'undefined') {
                    tinymce.triggerSave();
                }
            });

            @if($isSchool)
            // Auto calculate PTR if total students and total appointed teachers are entered
            function updatePTR() {
                const totalStudents = parseInt($('#school_total_students').val()) || 0;
                const totalTeachers = parseInt($('#total_appointed_teachers').val()) || 0;
                if (totalStudents > 0 && totalTeachers > 0) {
                    const ratio = Math.round(totalStudents / totalTeachers);
                    if (!$('#school_ptr').val() || $('#school_ptr').data('auto')) {
                        $('#school_ptr').val(ratio + ':1').data('auto', true);
                    }
                }
            }
            $('#school_total_students, #total_appointed_teachers').on('input', updatePTR);
            $('#school_ptr').on('input', function() {
                $(this).data('auto', false);
            });

            // Classes Repeater
            let classRowIndex = {{ count(old('school_classes', is_array($campus->school_classes) ? $campus->school_classes : [])) }};
            @php
                $classesForJs = isset($academicClasses) && $academicClasses->isNotEmpty()
                    ? $academicClasses->pluck('name')->toArray()
                    : \App\Models\AcademicClass::where('status', 1)->orderBy('sort_order')->orderBy('id')->pluck('name')->toArray();
                if (empty($classesForJs)) {
                    $classesForJs = ['Playgroup', 'Nursery', 'LKG', 'UKG', 'Class 1', 'Class 2', 'Class 3', 'Class 4', 'Class 5', 'Class 6', 'Class 7', 'Class 8', 'Class 9', 'Class 10', 'Class 11', 'Class 12', 'Dropper'];
                }
            @endphp
            const classMasterOptionsHtml = `
                <option value="">-- Select Class / Grade --</option>
                @foreach($classesForJs as $clsOption)
                    <option value="{{ $clsOption }}">{{ $clsOption }}</option>
                @endforeach
            `;
            $('#add-class-btn').on('click', function() {
                $('#no-classes-msg').hide();
                const template = `
                    <div class="class-item card border mb-3 bg-light position-relative">
                        <button type="button" class="btn btn-sm btn-danger position-absolute top-0 end-0 m-2 remove-class-btn"><i class="fas fa-times"></i></button>
                        <div class="card-body">
                            <div class="row g-3">
                                <div class="col-md-6">
                                    <label class="form-label fw-bold">Class / Grade <span class="text-danger">*</span></label>
                                    <select name="school_classes[${classRowIndex}][class_name]" class="form-select select2-class-item">
                                        ${classMasterOptionsHtml}
                                    </select>
                                </div>
                                <div class="col-12">
                                    <label class="form-label fw-bold">Curriculum / Board</label>
                                    <textarea name="school_classes[${classRowIndex}][curriculum]" id="curriculum_${classRowIndex}" class="form-control editor" rows="4" placeholder="e.g. CBSE, ICSE, Cambridge / IGCSE, State Board"></textarea>
                                </div>
                                <div class="col-12">
                                    <label class="form-label fw-bold">Fee & Other Charges</label>
                                    <textarea name="school_classes[${classRowIndex}][fee_structure]" id="fee_structure_${classRowIndex}" class="form-control editor" rows="4" placeholder="e.g. Tuition Fee: ₹45,000/yr, Admission Fee: ₹5,000, Activity/Lab Charges: ₹3,000..."></textarea>
                                </div>
                                <div class="col-12">
                                    <label class="form-label fw-bold">Admission Process</label>
                                    <textarea name="school_classes[${classRowIndex}][admission_process]" id="admission_process_${classRowIndex}" class="form-control editor" rows="4" placeholder="Eligibility, age criteria, entrance test/interview, documents required..."></textarea>
                                </div>
                                <div class="col-12">
                                    <label class="form-label fw-bold">Exam Preparation</label>
                                    <textarea name="school_classes[${classRowIndex}][exam_preparation]" id="exam_preparation_${classRowIndex}" class="form-control editor" rows="4" placeholder="e.g. Olympiads, NTSE, Board Exam Mentorship, JEE/NEET Foundation..."></textarea>
                                </div>
                            </div>
                        </div>
                    </div>
                `;
                $('#classes-container').append(template);
                if (typeof initializeTinyMCE === 'function') {
                    initializeTinyMCE(`#curriculum_${classRowIndex}`, 200);
                    initializeTinyMCE(`#fee_structure_${classRowIndex}`, 200);
                    initializeTinyMCE(`#admission_process_${classRowIndex}`, 200);
                    initializeTinyMCE(`#exam_preparation_${classRowIndex}`, 200);
                }
                classRowIndex++;
            });
            $(document).on('click', '.remove-class-btn', function() {
                const card = $(this).closest('.class-item');
                card.find('textarea.editor').each(function() {
                    const id = $(this).attr('id');
                    if (id && tinymce.get(id)) {
                        tinymce.get(id).remove();
                    }
                });
                card.remove();
                if ($('#classes-container .class-item').length === 0) {
                    $('#no-classes-msg').show();
                }
            });

            // Reviews Repeater
            let reviewIndex = {{ count(old('reviews', is_array($campus->reviews) ? $campus->reviews : [])) }};
            $('#add-review-btn').on('click', function() {
                $('#no-reviews-msg').hide();
                const template = `
                    <div class="review-item card border mb-3 bg-light position-relative">
                        <button type="button" class="btn btn-sm btn-danger position-absolute top-0 end-0 m-2 remove-review-btn"><i class="fas fa-times"></i></button>
                        <div class="card-body">
                            <div class="row g-3">
                                <div class="col-md-4">
                                    <label class="form-label fw-bold">Reviewer Name</label>
                                    <input type="text" name="reviews[${reviewIndex}][reviewer_name]" class="form-control" placeholder="e.g. Ramesh Kumar">
                                </div>
                                <div class="col-md-3">
                                    <label class="form-label">Reviewer Role</label>
                                    <select name="reviews[${reviewIndex}][reviewer_type]" class="form-select">
                                        <option value="Parent">Parent</option>
                                        <option value="Alumni">Alumni</option>
                                        <option value="Student">Student</option>
                                        <option value="Guardian">Guardian</option>
                                        <option value="Other">Other</option>
                                    </select>
                                </div>
                                <div class="col-md-2">
                                    <label class="form-label">Rating (1 to 5)</label>
                                    <select name="reviews[${reviewIndex}][rating]" class="form-select">
                                        <option value="5" selected>5 ★</option>
                                        <option value="4">4 ★</option>
                                        <option value="3">3 ★</option>
                                        <option value="2">2 ★</option>
                                        <option value="1">1 ★</option>
                                    </select>
                                </div>
                                <div class="col-md-3">
                                    <label class="form-label">Review Date / Year</label>
                                    <input type="text" name="reviews[${reviewIndex}][date]" class="form-control" placeholder="e.g. Oct 2024">
                                </div>
                                <div class="col-md-12">
                                    <label class="form-label">Review Title (Optional)</label>
                                    <input type="text" name="reviews[${reviewIndex}][title]" class="form-control" placeholder="e.g. Excellent academic support and caring teachers">
                                </div>
                                <div class="col-12">
                                    <label class="form-label fw-bold">Review Content</label>
                                    <textarea name="reviews[${reviewIndex}][comment]" class="form-control" rows="2" placeholder="Share experience or feedback about the school..."></textarea>
                                </div>
                            </div>
                        </div>
                    </div>
                `;
                $('#reviews-container').append(template);
                reviewIndex++;
            });
            $(document).on('click', '.remove-review-btn', function() {
                $(this).closest('.review-item').remove();
                if ($('#reviews-container .review-item').length === 0) {
                    $('#no-reviews-msg').show();
                }
            });

            // Events Repeater
            let eventIndex = {{ count(old('events_and_activities', is_array($campus->events_and_activities) ? $campus->events_and_activities : [])) }};
            $('#add-event-btn').on('click', function() {
                $('#no-events-msg').hide();
                const template = `
                    <div class="event-item card border mb-3 bg-light position-relative">
                        <button type="button" class="btn btn-sm btn-danger position-absolute top-0 end-0 m-2 remove-event-btn"><i class="fas fa-times"></i></button>
                        <div class="card-body">
                            <div class="row g-3">
                                <div class="col-md-5">
                                    <label class="form-label fw-bold">Event Title</label>
                                    <input type="text" name="events_and_activities[${eventIndex}][title]" class="form-control" placeholder="e.g. Annual Sports Day 2024">
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label">Category</label>
                                    <select name="events_and_activities[${eventIndex}][category]" class="form-select">
                                        <option value="Sports">Sports</option>
                                        <option value="Cultural">Cultural</option>
                                        <option value="Academic">Academic</option>
                                        <option value="Annual Day">Annual Day</option>
                                        <option value="Science Exhibition">Science Exhibition</option>
                                        <option value="Workshop">Workshop</option>
                                        <option value="Competition">Competition</option>
                                        <option value="Community Outreach">Community Outreach</option>
                                    </select>
                                </div>
                                <div class="col-md-3">
                                    <label class="form-label">Event Date / Frequency</label>
                                    <input type="text" name="events_and_activities[${eventIndex}][date]" class="form-control" placeholder="e.g. November 2024 / Annual">
                                </div>
                                <div class="col-12">
                                    <label class="form-label">Description & Highlights</label>
                                    <textarea name="events_and_activities[${eventIndex}][description]" class="form-control" rows="2" placeholder="Brief summary of activities, participants, and highlights..."></textarea>
                                </div>
                                <div class="col-12">
                                    <label class="form-label fw-bold"><i class="fas fa-images text-primary me-1"></i> Event Photos & Images</label>
                                    <input type="file" name="events_and_activities[${eventIndex}][images][]" multiple accept="image/*" class="form-control event-images-input">
                                    <small class="text-muted d-block mt-1"><i class="fas fa-info-circle me-1"></i>You can select multiple photos (JPG, PNG, WebP) for this event.</small>
                                    <div class="event-image-previews d-flex flex-wrap gap-2 mt-2"></div>
                                    <div class="selected-files-preview d-flex flex-wrap gap-2 mt-2"></div>
                                </div>
                            </div>
                        </div>
                    </div>
                `;
                $('#events-container').append(template);
                eventIndex++;
            });
            $(document).on('click', '.remove-event-btn', function() {
                $(this).closest('.event-item').remove();
                if ($('#events-container .event-item').length === 0) {
                    $('#no-events-msg').show();
                }
            });

            // Event multiple images preview on file selection
            $(document).on('change', '.event-images-input', function() {
                const previewContainer = $(this).siblings('.selected-files-preview');
                previewContainer.empty();
                const files = this.files;
                if (files && files.length > 0) {
                    Array.from(files).forEach(file => {
                        if (file.type.startsWith('image/')) {
                            const reader = new FileReader();
                            reader.onload = function(e) {
                                previewContainer.append(`
                                    <div class="position-relative d-inline-block border border-primary rounded p-1 bg-white shadow-sm" style="width: 85px; height: 85px;">
                                        <img src="${e.target.result}" class="rounded w-100 h-100" style="object-fit: cover;" alt="Preview">
                                        <span class="badge bg-primary position-absolute bottom-0 start-50 translate-middle-x mb-1" style="font-size: 8px;">New</span>
                                    </div>
                                `);
                            };
                            reader.readAsDataURL(file);
                        }
                    });
                }
            });

            // Remove existing event image thumbnail
            $(document).on('click', '.remove-event-img-btn', function(e) {
                e.preventDefault();
                e.stopPropagation();
                $(this).closest('.event-img-thumb-box').remove();
            });

            // Synchronize event image thumbnails after autosave
            window.syncEventImagesUI = function(events) {
                if (!Array.isArray(events)) return;
                $('#events-container .event-item').each(function(idx) {
                    const eventData = events[idx];
                    if (!eventData) return;

                    // Clear file input & temporary previews
                    $(this).find('.event-images-input').val('');
                    $(this).find('.selected-files-preview').empty();

                    // Rebuild saved images thumbnail list
                    const previewsBox = $(this).find('.event-image-previews');
                    previewsBox.empty();

                    if (Array.isArray(eventData.images)) {
                        eventData.images.forEach(imgPath => {
                            const src = imgPath.startsWith('http') ? imgPath : `/${imgPath}`;
                            previewsBox.append(`
                                <div class="position-relative d-inline-block border rounded p-1 bg-white shadow-sm event-img-thumb-box" style="width: 85px; height: 85px;">
                                    <img src="${src}" class="rounded w-100 h-100" style="object-fit: cover;" alt="Event Photo">
                                    <input type="hidden" name="events_and_activities[${idx}][existing_images][]" value="${imgPath}">
                                    <button type="button" class="btn btn-danger btn-xs position-absolute top-0 end-0 m-1 p-0 rounded-circle remove-event-img-btn" style="width: 20px; height: 20px; font-size: 10px; line-height: 1;" title="Remove image">
                                        <i class="fas fa-times"></i>
                                    </button>
                                </div>
                            `);
                        });
                    }
                });
            };

            // Alumni Repeater
            let alumniIndex = {{ count(old('notable_alumni', is_array($campus->notable_alumni) ? $campus->notable_alumni : [])) }};
            $('#add-alumni-btn').on('click', function() {
                $('#no-alumni-msg').hide();
                const template = `
                    <div class="alumni-item card border mb-3 bg-light position-relative">
                        <button type="button" class="btn btn-sm btn-danger position-absolute top-0 end-0 m-2 remove-alumni-btn"><i class="fas fa-times"></i></button>
                        <div class="card-body">
                            <div class="row g-3">
                                <div class="col-md-4">
                                    <label class="form-label fw-bold">Alumni Name</label>
                                    <input type="text" name="notable_alumni[${alumniIndex}][name]" class="form-control" placeholder="e.g. Dr. Anjali Sharma">
                                </div>
                                <div class="col-md-3">
                                    <label class="form-label">Batch / Passing Year</label>
                                    <input type="text" name="notable_alumni[${alumniIndex}][batch]" class="form-control" placeholder="e.g. Batch of 2015">
                                </div>
                                <div class="col-md-5">
                                    <label class="form-label">Profession / Notable Achievement</label>
                                    <input type="text" name="notable_alumni[${alumniIndex}][profession]" class="form-control" placeholder="e.g. IAS Officer / Scientist at ISRO">
                                </div>
                                <div class="col-12">
                                    <label class="form-label">Testimonial / Achievement Details</label>
                                    <textarea name="notable_alumni[${alumniIndex}][testimonial]" class="form-control" rows="2" placeholder="Brief words or achievements..."></textarea>
                                </div>
                            </div>
                        </div>
                    </div>
                `;
                $('#alumni-container').append(template);
                alumniIndex++;
            });
            $(document).on('click', '.remove-alumni-btn', function() {
                $(this).closest('.alumni-item').remove();
                if ($('#alumni-container .alumni-item').length === 0) {
                    $('#no-alumni-msg').show();
                }
            });

            // Clubs Repeater
            let clubIndex = {{ count(old('clubs_and_committees', is_array($campus->clubs_and_committees) ? $campus->clubs_and_committees : [])) }};
            $('#add-club-btn').on('click', function() {
                $('#no-clubs-msg').hide();
                const template = `
                    <div class="club-item card border mb-3 bg-light position-relative">
                        <button type="button" class="btn btn-sm btn-danger position-absolute top-0 end-0 m-2 remove-club-btn"><i class="fas fa-times"></i></button>
                        <div class="card-body">
                            <div class="row g-3">
                                <div class="col-md-4">
                                    <label class="form-label fw-bold">Club / Committee Name</label>
                                    <input type="text" name="clubs_and_committees[${clubIndex}][name]" class="form-control" placeholder="e.g. Eco Club / POSH Committee">
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label">Type / Category</label>
                                    <select name="clubs_and_committees[${clubIndex}][type]" class="form-select">
                                        <option value="Student Club">Student Club</option>
                                        <option value="Statutory Committee">Statutory Committee</option>
                                        <option value="Academic Society">Academic Society</option>
                                        <option value="Sports Committee">Sports Committee</option>
                                        <option value="Cultural Society">Cultural Society</option>
                                        <option value="Safety & Discipline Committee">Safety & Discipline Committee</option>
                                    </select>
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label">Coordinator / Faculty In-Charge</label>
                                    <input type="text" name="clubs_and_committees[${clubIndex}][coordinator]" class="form-control" placeholder="e.g. Mrs. Sunita Sharma">
                                </div>
                                <div class="col-12">
                                    <label class="form-label">Purpose & Key Activities</label>
                                    <textarea name="clubs_and_committees[${clubIndex}][description]" class="form-control" rows="2" placeholder="Brief purpose, roles, and regular activities..."></textarea>
                                </div>
                            </div>
                        </div>
                    </div>
                `;
                $('#clubs-container').append(template);
                clubIndex++;
            });
            $(document).on('click', '.remove-club-btn', function() {
                $(this).closest('.club-item').remove();
                if ($('#clubs-container .club-item').length === 0) {
                    $('#no-clubs-msg').show();
                }
            });

            // FAQs Repeater
            let faqIndex = {{ count(old('faqs', is_array($campus->faqs) ? $campus->faqs : [])) }};
            $('#add-faq-btn').on('click', function() {
                $('#no-faqs-msg').hide();
                const template = `
                    <div class="faq-item card border mb-3 bg-light position-relative">
                        <button type="button" class="btn btn-sm btn-danger position-absolute top-0 end-0 m-2 remove-faq-btn"><i class="fas fa-times"></i></button>
                        <div class="card-body">
                            <div class="row g-3">
                                <div class="col-12">
                                    <label class="form-label fw-bold">Question</label>
                                    <input type="text" name="faqs[${faqIndex}][question]" class="form-control" placeholder="e.g. What is the official UDISE code and status of the school?">
                                </div>
                                <div class="col-12">
                                    <label class="form-label fw-bold">Answer</label>
                                    <textarea name="faqs[${faqIndex}][answer]" class="form-control" rows="3" placeholder="Provide a clear, detailed answer..."></textarea>
                                </div>
                            </div>
                        </div>
                    </div>
                `;
                $('#faqs-container').append(template);
                faqIndex++;
            });
            $(document).on('click', '.remove-faq-btn', function() {
                $(this).closest('.faq-item').remove();
                if ($('#faqs-container .faq-item').length === 0) {
                    $('#no-faqs-msg').show();
                }
            });
            @endif

            showTab(0);
        });
    </script>
@endpush
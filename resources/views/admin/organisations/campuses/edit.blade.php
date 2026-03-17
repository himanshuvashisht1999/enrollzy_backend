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
            @if ($errors->any())
                <div class="alert alert-danger">
                    <ul>
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <form action="{{ route('admin.organisations.campuses.update', [$organisation->id, $campus->id]) }}"
                method="POST">
                @csrf
                @method('PUT')

                <div class="card">
                    <div class="card-body">

                        <!-- Nav tabs -->
                        <ul class="nav nav-tabs nav-tabs-custom nav-justified" role="tablist">
                            <li class="nav-item"><a class="nav-link active" data-bs-toggle="tab" href="#tab-identity"
                                    role="tab">Identity</a></li>
                            <li class="nav-item"><a class="nav-link" data-bs-toggle="tab" href="#tab-location"
                                    role="tab">Location</a></li>
                            <li class="nav-item"><a class="nav-link" data-bs-toggle="tab" href="#tab-infra"
                                    role="tab">Infrastructure</a></li>
                            <li class="nav-item"><a class="nav-link" data-bs-toggle="tab" href="#tab-academic"
                                    role="tab">Academic Focus</a></li>
                            <li class="nav-item"><a class="nav-link" data-bs-toggle="tab" href="#tab-amenities"
                                    role="tab">Amenities</a></li>
                            <li class="nav-item"><a class="nav-link" data-bs-toggle="tab" href="#tab-transport"
                                    role="tab">Transport</a></li>
                            <li class="nav-item"><a class="nav-link" data-bs-toggle="tab" href="#tab-safety"
                                    role="tab">Safety</a></li>
                            <li class="nav-item"><a class="nav-link" data-bs-toggle="tab" href="#tab-contact"
                                    role="tab">Contact</a></li>
                        </ul>

                        <!-- Tab panes -->
                        <div class="tab-content p-3 text-muted">

                            <!-- A. Identity -->
                            <div class="tab-pane active" id="tab-identity" role="tabpanel">
                                <div class="row g-3">
                                    @if($organisation->organisation_type_id == 3)
                                        <!-- Type 3: Institute Fields -->
                                        <div class="col-md-6">
                                            <label class="form-label">Location Unit Name *</label>
                                            <input type="text" name="campus_name" class="form-control"
                                                value="{{ old('campus_name', $campus->campus_name) }}" required>
                                        </div>
                                        <div class="col-md-6">
                                            <label class="form-label">Location Type *</label>
                                            <select name="campus_type" class="form-select" required>
                                                <option value="Centre" {{ $campus->campus_type == 'Centre' ? 'selected' : '' }}>
                                                    Centre</option>
                                                <option value="Branch" {{ $campus->campus_type == 'Branch' ? 'selected' : '' }}>
                                                    Branch</option>
                                                <option value="Campus" {{ $campus->campus_type == 'Campus' ? 'selected' : '' }}>
                                                    Campus</option>
                                            </select>
                                        </div>

                                        <div class="col-md-6">
                                            <label class="form-label">Established Year</label>
                                            <input type="number" name="established_year" class="form-control"
                                                value="{{ old('established_year', $campus->established_year) }}" min="1900"
                                                max="2100">
                                        </div>
                                    @else
                                        <div class="col-md-6">
                                            <label class="form-label">
                                                @if($organisation->organisation_type_id == 4) School Name * @else Campus Name *
                                                @endif
                                            </label>
                                            <input type="text" name="campus_name" class="form-control"
                                                value="{{ old('campus_name', $campus->campus_name) }}" required>
                                        </div>
                                        <div class="col-md-6">
                                            <label class="form-label">
                                                @if($organisation->organisation_type_id == 4) School Type * @else Campus Type *
                                                @endif
                                            </label>
                                            <select name="campus_type" class="form-select" required>
                                                <option value="Main" {{ $campus->campus_type == 'Main' ? 'selected' : '' }}>Main
                                                    Campus</option>
                                                <option value="Regional" {{ $campus->campus_type == 'Regional' ? 'selected' : '' }}>Regional Campus</option>
                                                <option value="Satellite" {{ $campus->campus_type == 'Satellite' ? 'selected' : '' }}>Satellite Campus</option>
                                            </select>
                                        </div>
                                        <div class="col-md-6">
                                            <label class="form-label">Established Year</label>
                                            <input type="number" name="established_year" class="form-control"
                                                value="{{ old('established_year', $campus->established_year) }}" min="1900"
                                                max="2100">
                                        </div>
                                    @endif

                                    <div class="col-md-6">
                                        <label class="form-label">Brand Type</label>
                                        <select name="brand_type" id="brand_type" class="form-select">
                                            <option value="">Select Brand Type</option>
                                            @foreach($brandTypes as $brand)
                                                <option value="{{ $brand }}" {{ old('brand_type', $campus->brand_type ?? '') == $brand ? 'selected' : '' }}>{{ $brand }}</option>
                                            @endforeach
                                        </select>
                                    </div>

                                    <div class="col-12" id="franchiseFields" style="display: none;">
                                        <div class="row g-3">
                                            <div class="col-md-6">
                                                <label class="form-label">Franchise Partner Name</label>
                                                <input type="text" name="franchise_partner_name" class="form-control"
                                                    value="{{ old('franchise_partner_name', $campus->franchise_partner_name) }}">
                                            </div>
                                            <div class="col-md-6">
                                                <label class="form-label">Franchise Start Year</label>
                                                <input type="number" name="franchise_start_year" class="form-control"
                                                    value="{{ old('franchise_start_year', $campus->franchise_start_year) }}"
                                                    min="1900" max="2100">
                                            </div>
                                            <div class="col-md-6">
                                                <div class="form-check form-switch mt-4">
                                                    <input class="form-check-input" type="checkbox"
                                                        name="brand_compliance_verified" id="brand_compliance_verified"
                                                        value="1" {{ old('brand_compliance_verified', $campus->brand_compliance_verified) ? 'checked' : '' }}>
                                                    <label class="form-check-label" for="brand_compliance_verified">Brand
                                                        Compliance Verified</label>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label">Status</label>
                                        <div class="form-check form-switch mt-2">
                                            <input class="form-check-input" type="checkbox" name="status" id="status"
                                                value="1" {{ old('status', $campus->status) ? 'checked' : '' }}>
                                            <label class="form-check-label" for="status">Active</label>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- B. Location -->
                            <div class="tab-pane" id="tab-location" role="tabpanel">
                                <div class="row g-3">
                                    <div class="col-md-4">
                                        <label class="form-label">City</label>
                                        <input type="text" name="city" class="form-control"
                                            value="{{ old('city', $campus->city) }}">
                                    </div>
                                    <div class="col-md-4">
                                        <label class="form-label">State</label>
                                        <input type="text" name="state" class="form-control"
                                            value="{{ old('state', $campus->state) }}">
                                    </div>
                                    <div class="col-md-4">
                                        <label class="form-label">Country</label>
                                        <input type="text" name="country" class="form-control"
                                            value="{{ old('country', $campus->country ?? 'India') }}">
                                    </div>
                                    <div class="col-md-4">
                                        <label class="form-label">Pincode</label>
                                        <input type="text" name="pincode" class="form-control"
                                            value="{{ old('pincode', $campus->pincode) }}">
                                    </div>
                                    <div class="col-md-8">
                                        <label class="form-label">Full Address</label>
                                        <textarea name="full_address" class="form-control"
                                            rows="2">{{ old('full_address', $campus->full_address) }}</textarea>
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label">Google Maps URL</label>
                                        <input type="url" name="google_map_url" class="form-control"
                                            value="{{ old('google_map_url', $campus->google_map_url) }}">
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label">Nearest Transport Hub</label>
                                        <input type="text" name="nearest_transport_hub" class="form-control"
                                            value="{{ old('nearest_transport_hub', $campus->nearest_transport_hub) }}">
                                    </div>
                                    @if($organisation->organisation_type_id == 4)
                                        <div class="col-md-6">
                                            <label class="form-label">Nearest Landmark</label>
                                            <input type="text" name="nearest_landmark" class="form-control"
                                                value="{{ old('nearest_landmark', $campus->nearest_landmark) }}">
                                        </div>
                                    @endif
                                </div>
                            </div>

                            <!-- C. Infrastructure -->
                            <div class="tab-pane" id="tab-infra" role="tabpanel">
                                <div class="row g-3">
                                    <div class="col-md-4">
                                        <label class="form-label">Campus Area</label>
                                        <div class="input-group">
                                            <input type="number" step="0.01" name="campus_area_acres" class="form-control"
                                                value="{{ old('campus_area_acres', $campus->campus_area_acres) }}">
                                            <select name="campus_area_unit" class="form-select" style="max-width: 120px;">
                                                @php
                                                    $currentUnit = old('campus_area_unit', $campus->campus_area_unit ?? 'Acres');
                                                @endphp
                                                <option value="Acres" {{ $currentUnit == 'Acres' ? 'selected' : '' }}>Acres
                                                </option>
                                                <option value="Square Yard" {{ $currentUnit == 'Square Yard' ? 'selected' : '' }}>Square Yard</option>
                                                <option value="Square Meter" {{ $currentUnit == 'Square Meter' ? 'selected' : '' }}>Square Meter</option>
                                            </select>
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <label class="form-label">
                                            @if($organisation->organisation_type_id == 4) Total Classrooms @else Academic
                                            Blocks @endif
                                        </label>
                                        <input type="number" name="academic_blocks_count" class="form-control"
                                            value="{{ old('academic_blocks_count', $campus->academic_blocks_count) }}">
                                    </div>
                                    <div class="col-md-4">
                                        <label class="form-label">
                                            @if($organisation->organisation_type_id == 4) Labs (Optional) @else Classrooms
                                            @endif
                                        </label>
                                        <input type="number" name="classrooms_count" class="form-control"
                                            value="{{ old('classrooms_count', $campus->classrooms_count) }}">
                                    </div>
                                    <div class="col-md-4">
                                        <div class="form-check form-switch mt-4">
                                            <input class="form-check-input" type="checkbox" name="smart_classrooms"
                                                id="smart_classrooms" value="1" {{ old('smart_classrooms', $campus->smart_classrooms) ? 'checked' : '' }}>
                                            <label class="form-check-label" for="smart_classrooms">Smart Classrooms
                                                Available</label>
                                        </div>
                                    </div>

                                    @if($organisation->organisation_type_id == 4)
                                        <!-- School Specific Infra -->
                                        <div class="col-md-4">
                                            <div class="form-check form-switch mt-4">
                                                <input class="form-check-input" type="checkbox" name="science_labs_available"
                                                    id="science_labs_available" value="1" {{ old('science_labs_available', $campus->science_labs_available) ? 'checked' : '' }}>
                                                <label class="form-check-label" for="science_labs_available">Science Labs
                                                    Available</label>
                                            </div>
                                        </div>
                                        <div class="col-md-4">
                                            <div class="form-check form-switch mt-4">
                                                <input class="form-check-input" type="checkbox" name="computer_labs_available"
                                                    id="computer_labs_available" value="1" {{ old('computer_labs_available', $campus->computer_labs_available) ? 'checked' : '' }}>
                                                <label class="form-check-label" for="computer_labs_available">Computer Labs
                                                    Available</label>
                                            </div>
                                        </div>
                                        <div class="col-md-4">
                                            <div class="form-check form-switch mt-4">
                                                <input class="form-check-input" type="checkbox" name="playground_available"
                                                    id="playground_available" value="1" {{ old('playground_available', $campus->playground_available) ? 'checked' : '' }}>
                                                <label class="form-check-label" for="playground_available">Playground
                                                    Available</label>
                                            </div>
                                        </div>
                                    @else
                                        <div class="col-md-4">
                                            <label class="form-label">Laboratories</label>
                                            <input type="number" name="laboratories_count" class="form-control"
                                                value="{{ old('laboratories_count', $campus->laboratories_count) }}">
                                        </div>
                                        <div class="col-md-4">
                                            <label class="form-label">Research Centers</label>
                                            <input type="number" name="research_centers_count" class="form-control"
                                                value="{{ old('research_centers_count', $campus->research_centers_count) }}">
                                        </div>
                                    @endif

                                    <div class="col-12">
                                        <hr>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="form-check form-switch mt-4">
                                            <input class="form-check-input" type="checkbox" name="library_available"
                                                id="library_available" value="1" {{ old('library_available', $campus->library_available) ? 'checked' : '' }}>
                                            <label class="form-check-label" for="library_available">Library
                                                Available</label>
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <label class="form-label">Library Books Count</label>
                                        <input type="number" name="library_books_count" class="form-control"
                                            value="{{ old('library_books_count', $campus->library_books_count) }}">
                                    </div>
                                    <div class="col-md-4">
                                        <div class="form-check form-switch mt-4">
                                            <input class="form-check-input" type="checkbox" name="digital_library_access"
                                                id="digital_library_access" value="1" {{ old('digital_library_access', $campus->digital_library_access) ? 'checked' : '' }}>
                                            <label class="form-check-label" for="digital_library_access">Digital Library
                                                Access</label>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- D. Academic Focus -->
                            <div class="tab-pane" id="tab-academic" role="tabpanel">
                                <div class="row g-3">
                                    <div class="col-md-6">
                                        <label class="form-label fw-bold">Exam Category (Filter)</label>
                                        <select class="form-select select2 exam-category-filter">
                                            <option value="">-- All Categories --</option>
                                            @foreach($examCategories as $cat)
                                                <option value="{{ $cat }}">{{ $cat }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label fw-bold">Exams Prepared For (Multiple)</label>
                                        <select name="exams_prepared_for[]" class="form-select select2 entrance-exam-ids-select" multiple>
                                            @foreach($exams as $exam)
                                                <option value="{{ $exam->name }}" data-category="{{ is_array($exam->exam_category) ? implode(',', $exam->exam_category) : ($exam->exam_category ?? '') }}" {{ in_array($exam->name, old('exams_prepared_for', $campus->exams_prepared_for ?? [])) ? 'selected' : '' }}>
                                                    {{ $exam->name }}
                                                </option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label fw-bold">Target Classes (Multiple)</label>
                                        <select name="target_classes[]" class="form-select select2" multiple>
                                            @for($i = 6; $i <= 12; $i++)
                                                <option value="{{ $i }}" {{ in_array($i, old('target_classes', $campus->target_classes ?? [])) ? 'selected' : '' }}>
                                                    Class {{ $i }}
                                                </option>
                                            @endfor
                                            <option value="Dropper" {{ in_array('Dropper', old('target_classes', $campus->target_classes ?? [])) ? 'selected' : '' }}>Dropper</option>
                                        </select>
                                    </div>
                                    <div class="col-12 mt-3">
                                        <label class="form-label fw-bold">About Institute / Campus</label>
                                        <textarea name="about_institute" class="editor" rows="4">{{ old('about_institute', $campus->about_institute) }}</textarea>
                                    </div>
                                </div>
                            </div>

                            <!-- D. Hostel & Amenities -->
                            <div class="tab-pane" id="tab-amenities" role="tabpanel">
                                <div class="row g-3">
                                    <div class="col-md-12">
                                        <div class="form-check form-switch">
                                            <input class="form-check-input" type="checkbox" name="hostel_available"
                                                id="hostel_available" value="1" {{ old('hostel_available', $campus->hostel_available) ? 'checked' : '' }}>
                                            <label class="form-check-label" for="hostel_available">Hostel Facility
                                                Available</label>
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <label class="form-label">Hostel Type</label>
                                        <select name="hostel_type" class="form-select">
                                            <option value="">Select Type</option>
                                            <option value="Boys" {{ $campus->hostel_type == 'Boys' ? 'selected' : '' }}>Boys
                                            </option>
                                            <option value="Girls" {{ $campus->hostel_type == 'Girls' ? 'selected' : '' }}>
                                                Girls</option>
                                            <option value="Both" {{ $campus->hostel_type == 'Both' ? 'selected' : '' }}>Both
                                            </option>
                                            <option value="None" {{ $campus->hostel_type == 'None' ? 'selected' : '' }}>None
                                            </option>
                                        </select>
                                    </div>
                                    <div class="col-md-4">
                                        <label class="form-label">Hostel Capacity</label>
                                        <input type="number" name="hostel_capacity" class="form-control"
                                            value="{{ old('hostel_capacity', $campus->hostel_capacity) }}">
                                    </div>
                                    <div class="col-md-4">
                                        <label class="form-label">Food Facility</label>
                                        <input type="text" name="food_facility" class="form-control"
                                            value="{{ old('food_facility', $campus->food_facility) }}"
                                            placeholder="e.g. Mess, Canteen">
                                    </div>
                                    <div class="col-md-12">
                                        <div class="form-check form-switch">
                                            <input class="form-check-input" type="checkbox"
                                                name="medical_facility_available" id="medical_facility_available" value="1"
                                                {{ old('medical_facility_available', $campus->medical_facility_available) ? 'checked' : '' }}>
                                            <label class="form-check-label" for="medical_facility_available">
                                                @if($organisation->organisation_type_id == 4) Medical Room Available @else
                                                Medical Facility Available @endif
                                            </label>
                                        </div>
                                    </div>
                                    <!-- Sports -->
                                    @php
                                        $sports = $campus->sports_facilities ?? [];
                                        if (is_array($sports))
                                            $sports = implode(',', $sports); // Convert to string if array
                                    @endphp
                                    <div class="col-md-12">
                                        <label class="form-label">Sports Facilities (Comma separated)</label>
                                        <input type="text" name="sports_facilities[]" class="form-control"
                                            value="{{ old('sports_facilities', $sports) }}"
                                            placeholder="Cricket, Football, Swimming...">
                                    </div>
                                </div>
                            </div>

                            <!-- E. Transport -->
                            <div class="tab-pane" id="tab-transport" role="tabpanel">
                                <div class="row g-3">
                                    <div class="col-md-6">
                                        <div class="form-check form-switch mt-4">
                                            <input class="form-check-input" type="checkbox" name="transport_available"
                                                id="transport_available" value="1" {{ old('transport_available', $campus->transport_available) ? 'checked' : '' }}>
                                            <label class="form-check-label" for="transport_available">
                                                @if($organisation->organisation_type_id == 4) School Transport Available
                                                @else Transport Available @endif
                                            </label>
                                        </div>
                                    </div>
                                    @if($organisation->organisation_type_id == 4)
                                        <div class="col-md-6">
                                            <label class="form-label">Bus Fleet Size</label>
                                            <input type="number" name="bus_fleet_size" class="form-control"
                                                value="{{ old('bus_fleet_size', $campus->bus_fleet_size) }}">
                                        </div>
                                        <div class="col-md-6">
                                            <div class="form-check form-switch mt-4">
                                                <input class="form-check-input" type="checkbox" name="gps_enabled_buses"
                                                    id="gps_enabled_buses" value="1" {{ old('gps_enabled_buses', $campus->gps_enabled_buses) ? 'checked' : '' }}>
                                                <label class="form-check-label" for="gps_enabled_buses">GPS Enabled
                                                    Buses</label>
                                            </div>
                                        </div>
                                    @else
                                        <div class="col-md-12">
                                            <label class="form-label fw-bold">Bus Routes</label>
                                            <div id="bus-routes-container">
                                                @php
                                                    $busRoutes = $campus->bus_routes ?? [''];
                                                    if (!is_array($busRoutes))
                                                        $busRoutes = [''];
                                                @endphp
                                                @foreach($busRoutes as $route)
                                                    <div class="input-group mb-2 bus-route-item">
                                                        <input type="text" name="bus_routes[]" class="form-control"
                                                            value="{{ old('bus_routes.' . $loop->index, $route) }}"
                                                            placeholder="Enter bus route details">
                                                        <button type="button" class="btn btn-outline-danger remove-bus-route">
                                                            <i class="fas fa-trash"></i>
                                                        </button>
                                                    </div>
                                                @endforeach
                                            </div>
                                            <button type="button" class="btn btn-sm btn-outline-primary" id="add-bus-route">
                                                <i class="fas fa-plus me-1"></i> Add Another Route
                                            </button>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="form-check form-switch mt-4">
                                                <input class="form-check-input" type="checkbox" name="parking_available"
                                                    id="parking_available" value="1" {{ old('parking_available', $campus->parking_available) ? 'checked' : '' }}>
                                                <label class="form-check-label" for="parking_available">Parking
                                                    Available</label>
                                            </div>
                                        </div>
                                    @endif
                                </div>
                            </div>

                            <!-- F. Safety -->
                            <div class="tab-pane" id="tab-safety" role="tabpanel">
                                <div class="row g-3">
                                    <div class="col-md-6">
                                        <div class="form-check form-switch">
                                            <input class="form-check-input" type="checkbox" name="cctv_coverage"
                                                id="cctv_coverage" value="1" {{ old('cctv_coverage', $campus->cctv_coverage) ? 'checked' : '' }}>
                                            <label class="form-check-label" for="cctv_coverage">CCTV Coverage</label>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label">
                                            @if($organisation->organisation_type_id == 4) Security Guards Count @else
                                            Security Staff Count @endif
                                        </label>
                                        <input type="number" name="security_staff_count" class="form-control"
                                            value="{{ old('security_staff_count', $campus->security_staff_count) }}">
                                    </div>

                                    <div class="col-md-6">
                                        <div class="form-check form-switch">
                                            <input class="form-check-input" type="checkbox" name="fire_safety_certified"
                                                id="fire_safety_certified" value="1" {{ old('fire_safety_certified', $campus->fire_safety_certified) ? 'checked' : '' }}>
                                            <label class="form-check-label" for="fire_safety_certified">Fire Safety
                                                Certified</label>
                                        </div>
                                    </div>

                                    @if($organisation->organisation_type_id == 4)
                                        <div class="col-md-6">
                                            <div class="form-check form-switch">
                                                <input class="form-check-input" type="checkbox" name="visitor_management_system"
                                                    id="visitor_management_system" value="1" {{ old('visitor_management_system', $campus->visitor_management_system) ? 'checked' : '' }}>
                                                <label class="form-check-label" for="visitor_management_system">Visitor
                                                    Management System</label>
                                            </div>
                                        </div>
                                    @else
                                        <div class="col-md-6">
                                            <div class="form-check form-switch">
                                                <input class="form-check-input" type="checkbox" name="disaster_management_plan"
                                                    id="disaster_management_plan" value="1" {{ old('disaster_management_plan', $campus->disaster_management_plan) ? 'checked' : '' }}>
                                                <label class="form-check-label" for="disaster_management_plan">Disaster
                                                    Management Plan</label>
                                            </div>
                                        </div>
                                    @endif
                                </div>
                            </div>

                            <!-- G. Contact -->
                            <div class="tab-pane" id="tab-contact" role="tabpanel">
                                <div class="row g-3">
                                    <div class="col-md-6">
                                        <label class="form-label">Campus Email</label>
                                        <input type="email" name="campus_email" class="form-control"
                                            value="{{ old('campus_email', $campus->campus_email) }}">
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label">Campus Website</label>
                                        <input type="url" name="campus_website" class="form-control"
                                            value="{{ old('campus_website', $campus->campus_website) }}">
                                    </div>
                                    @php
                                        $contacts = $campus->campus_contact_numbers ?? [];
                                        if (is_array($contacts))
                                            $contacts = implode(',', $contacts);
                                    @endphp
                                    <div class="col-md-12">
                                        <label class="form-label">Contact Numbers (Comma separated)</label>
                                        <input type="text" name="campus_contact_numbers[]" class="form-control"
                                            value="{{ old('campus_contact_numbers', $contacts) }}">
                                    </div>
                                </div>
                            </div>

                        </div> <!-- Tab Content End -->
                    </div>
                    <div class="card-footer text-end">
                        <button type="submit" class="btn btn-primary">Update Campus</button>
                        <a href="{{ route('admin.organisations.campuses.index', $organisation->id) }}"
                            class="btn btn-light">Cancel</a>
                    </div>
                </div>
            </form>
        </div>
    </div>
@endsection


@push('js')
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
    <script src="https://cdn.ckeditor.com/ckeditor5/36.0.1/classic/ckeditor.js"></script>
    <script>
        $(document).ready(function () {
            // Initialize Select2
            $('.select2').select2({
                width: '100%',
                placeholder: "Select an option",
                allowClear: true
            });

            // Initialize CKEditor
            document.querySelectorAll('.editor').forEach((element) => {
                ClassicEditor
                    .create(element)
                    .catch(error => {
                        console.error(error);
                    });
            });

            const brandTypeSelect = document.getElementById('brand_type');
            const franchiseFields = document.getElementById('franchiseFields');

            function toggleFranchiseFields() {
                if (brandTypeSelect.value === 'Franchise') {
                    franchiseFields.style.display = 'block';
                } else {
                    franchiseFields.style.display = 'none';
                }
            }

            if (brandTypeSelect) {
                brandTypeSelect.addEventListener('change', toggleFranchiseFields);
                toggleFranchiseFields(); // Init
            }

            // Bus Routes Repeater
            const busRoutesContainer = document.getElementById('bus-routes-container');
            const addBusRouteBtn = document.getElementById('add-bus-route');

            if (addBusRouteBtn) {
                addBusRouteBtn.addEventListener('click', function () {
                    const newItem = document.createElement('div');
                    newItem.className = 'input-group mb-2 bus-route-item';
                    newItem.innerHTML = `
                                <input type="text" name="bus_routes[]" class="form-control" placeholder="Enter bus route details">
                                <button type="button" class="btn btn-outline-danger remove-bus-route">
                                    <i class="fas fa-trash"></i>
                                </button>
                            `;
                    busRoutesContainer.appendChild(newItem);
                });

                busRoutesContainer.addEventListener('click', function (e) {
                    if (e.target.closest('.remove-bus-route')) {
                        const items = busRoutesContainer.querySelectorAll('.bus-route-item');
                        if (items.length > 1) {
                            e.target.closest('.bus-route-item').remove();
                        } else {
                            items[0].querySelector('input').value = '';
                        }
                    }
            // Exam filtering logic
            $('.exam-category-filter').each(function() {
                const categorySelect = $(this);
                const examSelect = categorySelect.closest('.row').find('.entrance-exam-ids-select');
                
                if (examSelect.length === 0) return;

                // Store original options for this specific pair
                const allExamOptions = examSelect.find('option').clone();

                function filterExams(selectedCategory) {
                    const currentSelections = examSelect.val() || [];
                    examSelect.empty();

                    if (selectedCategory) {
                        allExamOptions.each(function () {
                            let catData = $(this).data('category');
                            if (catData && String(catData).split(',').includes(selectedCategory)) {
                                examSelect.append($(this).clone());
                            }
                        });
                    } else {
                        allExamOptions.each(function () {
                            if ($(this).val() !== "") {
                                examSelect.append($(this).clone());
                            }
                        });
                    }

                    examSelect.val(currentSelections);
                    examSelect.trigger('change');
                }

                categorySelect.on('change', function () {
                    filterExams($(this).val());
                });

                // Initial Filter on Load
                if (categorySelect.val()) {
                    filterExams(categorySelect.val());
                }
            });
        });
    </script>
@endpush
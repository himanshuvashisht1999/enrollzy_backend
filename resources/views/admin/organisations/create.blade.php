@extends('admin.layouts.master')

@section('content')
    <div class="container-fluid">
        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-header">
                        <h4 class="card-title">Add New Organisation</h4>
                        <a href="{{ route('admin.organisations.index') }}"
                            class="btn btn-secondary btn-sm float-end">Back</a>
                    </div>
                    <div class="card-body">
                        @if ($errors->any())
                            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                                <h5 class="alert-heading"><i class="fas fa-exclamation-triangle me-2"></i>Validation Errors</h5>
                                <ul class="mb-0">
                                    @foreach ($errors->all() as $error)
                                        <li>{{ $error }}</li>
                                    @endforeach
                                </ul>
                                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                            </div>
                        @endif
                        <form action="{{ route('admin.organisations.store') }}" method="POST" enctype="multipart/form-data">
                            @csrf
                            <input type="hidden" id="organisation_id" name="organisation_id">
                            <div id="autosave-status" class="position-fixed bottom-0 end-0 p-3"
                                style="z-index: 1050; pointer-events: none;"></div>
                            <div class="row g-3">
                                <!-- Basic Common Fields -->
                                <div class="col-md-6">
                                    <label class="form-label fw-bold">Organisation Name <span
                                            class="text-danger">*</span></label>
                                    <input type="text" name="name" class="form-control" value="{{ old('name') }}" required>
                                </div>



                                <div class="col-md-6">
                                    <label class="form-label fw-bold">Organisation Type <span
                                            class="text-danger">*</span></label>
                                    <select name="organisation_type_id" id="organisation_type_id" class="form-select"
                                        required>
                                        <option value="">Select Type</option>
                                        @foreach ($organisationTypes as $type)
                                            @php
                                                $title = strtolower($type->title);
                                                $category = 'other';
                                                if (str_contains($title, 'university')) {
                                                    $category = 'university';
                                                } elseif (str_contains($title, 'college') || str_contains($title, 'institute')) {
                                                    $category = 'institute';
                                                } elseif (str_contains($title, 'school')) {
                                                    $category = 'school';
                                                } elseif (str_contains($title, 'exam') || str_contains($title, 'conducting')) {
                                                    $category = 'ecb';
                                                } elseif (str_contains($title, 'counselling') || $type->id == 6) {
                                                    $category = 'counselling';
                                                } elseif (str_contains($title, 'regulatory') || $type->id == 7) {
                                                    $category = 'regulatory';
                                                }
                                            @endphp
                                            <option value="{{ $type->id }}" data-category="{{ $category }}"
                                                data-title="{{ $type->title }}" {{ old('organisation_type_id') == $type->id ? 'selected' : '' }}>
                                                {{ $type->title }} (ID: {{ $type->id }})
                                            </option>
                                        @endforeach
                                    </select>
                                </div>

                                {{-- <div class="col-md-6">
                                    <label class="form-label fw-bold">Brand Type</label>
                                    <select name="brand_type" class="form-select">
                                        <option value="">Select Brand Type</option>
                                        @foreach($brandTypes as $brand)
                                        <option value="{{ $brand }}" {{ old('brand_type')==$brand ? 'selected' : '' }}>{{
                                            $brand }}</option>
                                        @endforeach
                                    </select>
                                </div> --}}


                                <div class="col-md-6">
                                    <label class="form-label fw-bold">Central Authority</label>
                                    <input type="text" name="central_authority" class="form-control"
                                        value="{{ old('central_authority') }}">
                                </div>

                                <div class="col-md-6">
                                    <label class="form-label fw-bold">Head Office Location</label>
                                    <input type="text" name="head_office_location" class="form-control"
                                        value="{{ old('head_office_location') }}">
                                </div>

                                <div class="col-md-6">
                                    <label class="form-label fw-bold">Is Top Institution?</label>
                                    <select name="is_top" class="form-select">
                                        <option value="0" {{ old('is_top', 0) == 0 ? 'selected' : '' }}>No</option>
                                        <option value="1" {{ old('is_top') == 1 ? 'selected' : '' }}>Yes (Top Institution)</option>
                                    </select>
                                </div>

                                <div class="col-md-12">
                                    <label class="form-label fw-bold">Core Values</label>
                                    <div class="core-values-container">
                                        <div class="input-group mb-2">
                                            <input type="text" name="core_values[]" class="form-control"
                                                placeholder="Enter core value">
                                            <button type="button" class="btn btn-outline-success add-core-value">+</button>
                                        </div>
                                    </div>
                                </div>

                                <!-- University Specific Fields Section -->
                                <div id="university-fields" class="col-12" style="display: none;">
                                    <hr class="my-4">
                                    <h5 class="mb-3 text-primary">University Details</h5>

                                    <ul class="nav nav-tabs mb-3" id="uniTab" role="tablist">
                                        <li class="nav-item" role="presentation">
                                            <button class="nav-link active" id="core-tab" data-bs-toggle="tab"
                                                data-bs-target="#core" type="button" role="tab" aria-controls="core"
                                                aria-selected="true">Core Identity</button>
                                        </li>
                                        <li class="nav-item" role="presentation">
                                            <button class="nav-link" id="legal-tab" data-bs-toggle="tab"
                                                data-bs-target="#legal" type="button" role="tab" aria-controls="legal"
                                                aria-selected="false">Legal & Regulatory</button>
                                        </li>
                                        <li class="nav-item" role="presentation">
                                            <button class="nav-link" id="governance-tab" data-bs-toggle="tab"
                                                data-bs-target="#governance" type="button" role="tab"
                                                aria-controls="governance" aria-selected="false">Governance</button>
                                        </li>
                                        <li class="nav-item" role="presentation">
                                            <button class="nav-link" id="academic-tab" data-bs-toggle="tab"
                                                data-bs-target="#academic" type="button" role="tab" aria-controls="academic"
                                                aria-selected="false">Academic Scope</button>
                                        </li>
                                    </ul>

                                    <div class="tab-content" id="uniTabContent">
                                        <!-- Core Identity -->
                                        <div class="tab-pane fade show active" id="core" role="tabpanel"
                                            aria-labelledby="core-tab">
                                            <div class="row g-3">
                                                <div class="col-md-6">
                                                    <label class="form-label">Brand Name</label>
                                                    <input type="text" name="brand_name" class="form-control"
                                                        value="{{ old('brand_name') }}">
                                                </div>
                                                <div class="col-md-6">
                                                    <label class="form-label">Short Name</label>
                                                    <input type="text" name="short_name" class="form-control"
                                                        value="{{ old('short_name') }}">
                                                </div>
                                                <div class="col-md-6">
                                                    <label class="form-label">Established Year</label>
                                                    <input type="number" name="established_year" class="form-control"
                                                        value="{{ old('established_year') }}">
                                                </div>
                                                <div class="col-md-6">
                                                    <label class="form-label">University Type</label>
                                                    <select name="university_type" class="form-select">
                                                        <option value="">Select Type</option>
                                                        <option value="Central">Central</option>
                                                        <option value="State">State</option>
                                                        <option value="Deemed">Deemed</option>
                                                        <option value="Private">Private</option>
                                                        <option value="International">International</option>
                                                    </select>
                                                </div>
                                                <div class="col-md-6">
                                                    <label class="form-label">Ownership Type</label>
                                                    <select name="ownership_type" class="form-select">
                                                        <option value="">Select Ownership</option>
                                                        <option value="Government">Government</option>
                                                        <option value="Private">Private</option>
                                                        <option value="Trust">Trust</option>
                                                        <option value="PPP">PPP</option>
                                                    </select>
                                                </div>
                                                <div class="col-md-12">
                                                    <label class="form-label">About University</label>
                                                    <textarea name="about_university" class="form-control editor"
                                                        rows="3">{{ old('about_university') }}</textarea>
                                                </div>
                                                <div class="col-md-12">
                                                    <label class="form-label">Vision & Mission</label>
                                                    <textarea name="vision_mission" class="form-control editor"
                                                        rows="3">{{ old('vision_mission') }}</textarea>
                                                </div>
                                                <div class="col-md-6">
                                                    <label class="form-label">Logo</label>
                                                    <input type="file" name="logo_url"
                                                        class="form-control file-preview-input" accept="image/*">
                                                    <div class="form-text text-muted">Image size should not exceed 2MB.</div>
                                                    <div class="file-preview mt-2" data-preview="logo_url"></div>
                                                </div>
                                                <div class="col-md-6">
                                                    <label class="form-label">Cover Image</label>
                                                    <input type="file" name="cover_image_url"
                                                        class="form-control file-preview-input" accept="image/*">
                                                    <div class="form-text text-muted">Image size should not exceed 2MB.</div>
                                                    <div class="file-preview mt-2" data-preview="cover_image_url"></div>
                                                </div>
                                            </div>
                                        </div>

                                        <!-- Legal & Regulatory -->
                                        <div class="tab-pane fade" id="legal" role="tabpanel" aria-labelledby="legal-tab">
                                            <div class="row g-3">
                                                <div class="col-md-3">
                                                    <div class="form-check form-switch mt-4">
                                                        <input class="form-check-input" type="checkbox"
                                                            name="degree_awarding_authority" id="degree_awarding_authority"
                                                            {{ old('degree_awarding_authority') ? 'checked' : '' }}>
                                                        <label class="form-check-label"
                                                            for="degree_awarding_authority">Degree Awarding
                                                            Authority</label>
                                                    </div>
                                                </div>
                                                <div class="col-md-3">
                                                    <div class="form-check form-switch mt-4">
                                                        <input class="form-check-input" type="checkbox"
                                                            name="ugc_recognized" id="ugc_recognized" {{ old('ugc_recognized') ? 'checked' : '' }}>
                                                        <label class="form-check-label" for="ugc_recognized">UGC
                                                            Recognized</label>
                                                    </div>
                                                </div>
                                                <div class="col-md-6">
                                                    <label class="form-label">UGC Approval Number</label>
                                                    <input type="text" name="ugc_approval_number" class="form-control"
                                                        value="{{ old('ugc_approval_number') }}">
                                                </div>
                                                <div class="col-md-3">
                                                    <div class="form-check form-switch mt-4">
                                                        <input class="form-check-input" type="checkbox"
                                                            name="aicte_approved" id="aicte_approved" {{ old('aicte_approved') ? 'checked' : '' }}>
                                                        <label class="form-check-label" for="aicte_approved">AICTE
                                                            Approved</label>
                                                    </div>
                                                </div>
                                                <div class="col-md-3">
                                                    <div class="form-check form-switch mt-4">
                                                        <input class="form-check-input" type="checkbox"
                                                            name="naac_accredited" id="naac_accredited" {{ old('naac_accredited') ? 'checked' : '' }}>
                                                        <label class="form-check-label" for="naac_accredited">NAAC
                                                            Accredited</label>
                                                    </div>
                                                </div>
                                                <div class="col-md-6">
                                                    <label class="form-label">NAAC Grade</label>
                                                    <input type="text" name="naac_grade" class="form-control"
                                                        value="{{ old('naac_grade') }}">
                                                </div>
                                                <div class="col-md-6">
                                                    <label class="form-label">NIRF Rank (Overall)</label>
                                                    <input type="number" name="nirf_rank_overall" class="form-control"
                                                        value="{{ old('nirf_rank_overall') }}">
                                                </div>
                                                <div class="col-md-6">
                                                    <label class="form-label">NIRF Rank (Category)</label>
                                                    <input type="number" name="nirf_rank_category" class="form-control"
                                                        value="{{ old('nirf_rank_category') }}">
                                                </div>
                                                <div class="col-md-12">
                                                    <label class="form-label">Recognition Documents (Multiple)</label>
                                                    <div class="file-repeater-container"
                                                        data-name="recognition_documents[]">
                                                        <div class="file-repeater-item mb-2">
                                                            <div class="input-group">
                                                                <input type="file" name="recognition_documents[]"
                                                                    class="form-control file-preview-input">
                                                                <button type="button"
                                                                    class="btn btn-outline-danger remove-file-item"
                                                                    style="display:none;">x</button>
                                                            </div>
                                                            <div class="form-text text-muted">Image size should not exceed 2MB.</div>
                                                            <div class="file-preview mt-2"></div>
                                                        </div>
                                                        <button type="button"
                                                            class="btn btn-sm btn-outline-primary add-file-item">+ Add More
                                                            Document</button>
                                                    </div>
                                                    <small class="text-muted">Upload recognition documents one by
                                                        one</small>
                                                </div>
                                                <div class="col-md-6">
                                                    <label class="form-label">International Accreditations (Comma
                                                        separated)</label>
                                                    <input type="text" name="international_accreditations[]"
                                                        class="form-control" placeholder="e.g. ABET, AACSB">
                                                </div>
                                                <div class="col-md-6">
                                                    <label class="form-label">Statutory Approvals (Comma separated)</label>
                                                    <input type="text" name="statutory_approvals[]" class="form-control"
                                                        placeholder="e.g. BCI, PCI, COA">
                                                </div>
                                            </div>
                                        </div>

                                        <!-- Governance -->
                                        <div class="tab-pane fade" id="governance" role="tabpanel"
                                            aria-labelledby="governance-tab">
                                            <div class="row g-3">
                                                <div class="col-md-6">
                                                    <label class="form-label">Governing Body Name</label>
                                                    <input type="text" name="governing_body_name" class="form-control"
                                                        value="{{ old('governing_body_name') }}">
                                                </div>
                                                <div class="col-md-6">
                                                    <label class="form-label">Chancellor Name</label>
                                                    <input type="text" name="chancellor_name" class="form-control"
                                                        value="{{ old('chancellor_name') }}">
                                                </div>
                                                <div class="col-md-6">
                                                    <label class="form-label">Vice Chancellor Name</label>
                                                    <input type="text" name="vice_chancellor_name" class="form-control"
                                                        value="{{ old('vice_chancellor_name') }}">
                                                </div>
                                                <div class="col-md-6">
                                                    <label class="form-label">University Category</label>
                                                    <select name="university_category" class="form-select">
                                                        <option value="">Select Category</option>
                                                        <option value="Teaching">Teaching</option>
                                                        <option value="Research">Research</option>
                                                        <option value="Teaching + Research">Teaching + Research</option>
                                                    </select>
                                                </div>
                                                <div class="col-md-4">
                                                    <label class="form-label">No. of Campuses</label>
                                                    <input type="number" name="number_of_campuses" class="form-control"
                                                        value="{{ old('number_of_campuses') }}">
                                                </div>
                                                <div class="col-md-4">
                                                    <label class="form-label">No. of Constituent Colleges</label>
                                                    <input type="number" name="number_of_constituent_colleges"
                                                        class="form-control"
                                                        value="{{ old('number_of_constituent_colleges') }}">
                                                </div>
                                                <div class="col-md-4">
                                                    <label class="form-label">No. of Affiliated Colleges</label>
                                                    <input type="number" name="number_of_affiliated_colleges"
                                                        class="form-control"
                                                        value="{{ old('number_of_affiliated_colleges') }}">
                                                </div>
                                                <div class="col-md-4">
                                                    <div class="form-check form-switch mt-4">
                                                        <input class="form-check-input" type="checkbox"
                                                            name="autonomous_status" id="autonomous_status" {{ old('autonomous_status') ? 'checked' : '' }}>
                                                        <label class="form-check-label" for="autonomous_status">Autonomous
                                                            Status</label>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>

                                        <!-- Academic Scope -->
                                        <div class="tab-pane fade" id="academic" role="tabpanel"
                                            aria-labelledby="academic-tab">
                                            <div class="row g-3">
                                                <div class="col-12">
                                                    <label class="form-label">Levels Offered</label>
                                                    <div class="d-flex gap-3 flex-wrap">
                                                        <div class="form-check">
                                                            <input class="form-check-input" type="checkbox"
                                                                name="levels_offered[]" value="Diploma" id="level_diploma">
                                                            <label class="form-check-label"
                                                                for="level_diploma">Diploma</label>
                                                        </div>
                                                        <div class="form-check">
                                                            <input class="form-check-input" type="checkbox"
                                                                name="levels_offered[]" value="UG" id="level_ug">
                                                            <label class="form-check-label" for="level_ug">UG</label>
                                                        </div>
                                                        <div class="form-check">
                                                            <input class="form-check-input" type="checkbox"
                                                                name="levels_offered[]" value="PG" id="level_pg">
                                                            <label class="form-check-label" for="level_pg">PG</label>
                                                        </div>
                                                        <div class="form-check">
                                                            <input class="form-check-input" type="checkbox"
                                                                name="levels_offered[]" value="Doctoral"
                                                                id="level_doctoral">
                                                            <label class="form-check-label"
                                                                for="level_doctoral">Doctoral</label>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <!-- Institute Specific Fields Section -->
                                <div id="institute-fields" class="col-12" style="display: none;">
                                    <hr class="my-4">
                                    <h5 class="mb-3 text-secondary">Institute Details</h5>

                                    <ul class="nav nav-tabs mb-3" id="instTab" role="tablist">
                                        <li class="nav-item" role="presentation">
                                            <button class="nav-link active" id="inst-core-tab" data-bs-toggle="tab"
                                                data-bs-target="#inst-core" type="button" role="tab"
                                                aria-controls="inst-core" aria-selected="true">Core Identity</button>
                                        </li>
                                        <li class="nav-item" role="presentation">
                                            <button class="nav-link" id="inst-legal-tab" data-bs-toggle="tab"
                                                data-bs-target="#inst-legal" type="button" role="tab"
                                                aria-controls="inst-legal" aria-selected="false">Ownership & Legal</button>
                                        </li>
                                    </ul>

                                    <div class="tab-content" id="instTabContent">
                                        <!-- Core Identity (Reusing some names but distinct scope) -->
                                        <div class="tab-pane fade show active" id="inst-core" role="tabpanel"
                                            aria-labelledby="inst-core-tab">
                                            <div class="row g-3">
                                                <!-- Note: Reusing brand_name, short_name, established_year, logo/cover from common table if desired, 
                                                                                                                                                     or separate inputs if we want user to re-enter. 
                                                                                                                                                     Since we use same form submit, we must use SAME name attributes for overlapping fields. -->

                                                <div class="col-md-6">
                                                    <label class="form-label">Brand Name</label>
                                                    <!-- Same name attribute 'brand_name' reuses the DB column -->
                                                    <input type="text" name="brand_name" class="form-control"
                                                        value="{{ old('brand_name') }}" id="inst_brand_name">
                                                </div>
                                                <div class="col-md-6">
                                                    <label class="form-label">Short Name</label>
                                                    <input type="text" name="short_name" class="form-control"
                                                        value="{{ old('short_name') }}" id="inst_short_name">
                                                </div>
                                                <div class="col-md-6">
                                                    <label class="form-label">Established Year</label>
                                                    <input type="number" name="established_year" class="form-control"
                                                        value="{{ old('established_year') }}" id="inst_established_year">
                                                </div>

                                                <div class="col-md-12">
                                                    <label class="form-label">About Organization</label>
                                                    <textarea name="about_organisation" class="form-control editor"
                                                        rows="3">{{ old('about_organisation') }}</textarea>
                                                </div>
                                                <div class="col-md-12">
                                                    <label class="form-label">Vision & Mission</label>
                                                    <textarea name="vision_mission" class="form-control editor" rows="3"
                                                        id="inst_vision_mission">{{ old('vision_mission') }}</textarea>
                                                </div>

                                                <div class="col-md-6">
                                                    <label class="form-label">Logo</label>
                                                    <input type="file" name="logo_url"
                                                        class="form-control file-preview-input" accept="image/*"
                                                        id="inst_logo_url">
                                                    <div class="form-text text-muted">Image size should not exceed 2MB.</div>
                                                    <div class="file-preview mt-2" data-preview="logo_url"></div>
                                                </div>
                                                <div class="col-md-6">
                                                    <label class="form-label">Cover Image</label>
                                                    <input type="file" name="cover_image_url"
                                                        class="form-control file-preview-input" accept="image/*"
                                                        id="inst_cover_image_url">
                                                    <div class="form-text text-muted">Image size should not exceed 2MB.</div>
                                                    <div class="file-preview mt-2" data-preview="cover_image_url"></div>
                                                </div>
                                            </div>
                                        </div>

                                        <!-- Ownership & Legal -->
                                        <div class="tab-pane fade" id="inst-legal" role="tabpanel"
                                            aria-labelledby="inst-legal-tab">
                                            <div class="row g-3">
                                                <div class="col-md-6">
                                                    <label class="form-label">Ownership Type</label>
                                                    <!-- Reusing ownership_type column -->
                                                    <select name="ownership_type" class="form-select"
                                                        id="inst_ownership_type">
                                                        <option value="">Select Ownership</option>
                                                        <option value="Private">Private</option>
                                                        <option value="Trust">Trust</option>
                                                        <option value="LLP">LLP</option>
                                                        <option value="Partnership">Partnership</option>
                                                    </select>
                                                </div>
                                                <div class="col-md-6">
                                                    <label class="form-label">Registered Entity Name</label>
                                                    <input type="text" name="registered_entity_name" class="form-control"
                                                        value="{{ old('registered_entity_name') }}">
                                                </div>
                                                <div class="col-md-6">
                                                    <label class="form-label">Registration Number</label>
                                                    <input type="text" name="registration_number" class="form-control"
                                                        value="{{ old('registration_number') }}">
                                                </div>
                                                <div class="col-md-6">
                                                    <label class="form-label">GST Number</label>
                                                    <input type="text" name="gst_number" class="form-control"
                                                        value="{{ old('gst_number') }}">
                                                </div>
                                                <div class="col-md-6">
                                                    <label class="form-label">PAN Number</label>
                                                    <input type="text" name="pan_number" class="form-control"
                                                        value="{{ old('pan_number') }}">
                                                </div>
                                                <div class="col-md-6">
                                                    <div class="form-check form-switch mt-4">
                                                        <input class="form-check-input" type="checkbox"
                                                            name="gst_registered" id="gst_registered" {{ old('gst_registered') ? 'checked' : '' }}>
                                                        <label class="form-check-label" for="gst_registered">GST
                                                            Registered</label>
                                                    </div>
                                                </div>
                                                <div class="col-md-12">
                                                    <label class="form-label">Legal Documents (Multiple)</label>
                                                    <div class="file-repeater-container" data-name="legal_documents_urls[]">
                                                        <div class="file-repeater-item mb-2">
                                                            <div class="input-group">
                                                                <input type="file" name="legal_documents_urls[]"
                                                                    class="form-control file-preview-input">
                                                                <button type="button"
                                                                    class="btn btn-outline-danger remove-file-item"
                                                                    style="display:none;">x</button>
                                                            </div>
                                                            <div class="form-text text-muted">Image size should not exceed 2MB.</div>
                                                            <div class="file-preview mt-2"></div>
                                                        </div>
                                                        <button type="button"
                                                            class="btn btn-sm btn-outline-primary add-file-item">+ Add More
                                                            Document</button>
                                                    </div>
                                                    <small class="text-muted">Upload registration certs, PAN card, GST cert
                                                        etc. one by one</small>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <div id="school-fields" class="col-12" style="display: none;">
                                    <hr class="my-4">
                                    <h5 class="mb-3 text-success">School Details (Type 4)</h5>

                                    <ul class="nav nav-tabs mb-3" id="schTab" role="tablist">
                                        <li class="nav-item" role="presentation"><button class="nav-link active"
                                                id="sch-core-tab" data-bs-toggle="tab" data-bs-target="#sch-core"
                                                type="button" role="tab">Core Identity</button></li>
                                        <li class="nav-item" role="presentation"><button class="nav-link"
                                                id="sch-ownership-tab" data-bs-toggle="tab" data-bs-target="#sch-ownership"
                                                type="button" role="tab">Ownership</button></li>
                                        <li class="nav-item" role="presentation"><button class="nav-link"
                                                id="sch-boards-tab" data-bs-toggle="tab" data-bs-target="#sch-boards"
                                                type="button" role="tab">Boards & Academic</button></li>
                                        <li class="nav-item" role="presentation"><button class="nav-link"
                                                id="sch-policies-tab" data-bs-toggle="tab" data-bs-target="#sch-policies"
                                                type="button" role="tab">Policies</button></li>
                                        <li class="nav-item" role="presentation"><button class="nav-link"
                                                id="sch-footprint-tab" data-bs-toggle="tab" data-bs-target="#sch-footprint"
                                                type="button" role="tab">Footprint & Digital</button></li>
                                        <li class="nav-item" role="presentation"><button class="nav-link" id="sch-seo-tab"
                                                data-bs-toggle="tab" data-bs-target="#sch-seo" type="button"
                                                role="tab">Trust & SEO</button></li>
                                    </ul>

                                    <div class="tab-content" id="schTabContent">
                                        <!-- A. Core Identity -->
                                        <div class="tab-pane fade show active" id="sch-core" role="tabpanel">
                                            <div class="row g-3">
                                                <div class="col-md-6"><label class="form-label">Brand Name</label><input
                                                        type="text" name="brand_name" class="form-control"
                                                        id="sch_brand_name" value="{{ old('brand_name') }}"></div>
                                                <div class="col-md-6"><label class="form-label">Short Name</label><input
                                                        type="text" name="short_name" class="form-control"
                                                        id="sch_short_name" value="{{ old('short_name') }}"></div>
                                                <div class="col-md-6"><label class="form-label">Established
                                                        Year</label><input type="number" name="established_year"
                                                        class="form-control" id="sch_established_year"
                                                        value="{{ old('established_year') }}"></div>
                                                <div class="col-md-12"><label class="form-label">About
                                                        Organization</label><textarea name="about_organisation"
                                                        class="form-control editor" rows="3"
                                                        id="sch_about">{{ old('about_organisation') }}</textarea></div>
                                                <div class="col-md-12"><label class="form-label">Vision &
                                                        Mission</label><textarea name="vision_mission" class="form-control editor"
                                                        rows="3" id="sch_vision">{{ old('vision_mission') }}</textarea>
                                                </div>

                                                <!-- Core Values Repeater (Reused Logic) -->
                                                <div class="col-md-6"><label class="form-label">Logo</label><input
                                                        type="file" name="logo_url" class="form-control file-preview-input"
                                                        accept="image/*" id="sch_logo">
                                                    <div class="form-text text-muted">Image size should not exceed 2MB.</div>
                                                    <div class="file-preview mt-2" data-preview="logo_url"></div>
                                                </div>
                                                <div class="col-md-6"><label class="form-label">Cover Image</label><input
                                                        type="file" name="cover_image_url"
                                                        class="form-control file-preview-input" accept="image/*"
                                                        id="sch_cover">
                                                    <div class="form-text text-muted">Image size should not exceed 2MB.</div>
                                                    <div class="file-preview mt-2" data-preview="cover_image_url"></div>
                                                </div>
                                            </div>
                                        </div>

                                        <!-- B. Ownership & Legal -->
                                        <div class="tab-pane fade" id="sch-ownership" role="tabpanel">
                                            <div class="row g-3">
                                                <div class="col-md-6">
                                                    <label class="form-label">Ownership Type</label>
                                                    <select name="ownership_type" class="form-select" id="sch_ownership">
                                                        <option value="">Select</option>
                                                        <option value="Government">Government</option>
                                                        <option value="Private">Private</option>
                                                        <option value="Trust / Society">Trust / Society</option>
                                                        <option value="Minority">Minority</option>
                                                    </select>
                                                </div>
                                                <div class="col-md-6"><label class="form-label">Registered Entity
                                                        Name</label><input type="text" name="registered_entity_name"
                                                        class="form-control" id="sch_reg_name"
                                                        value="{{ old('registered_entity_name') }}"></div>
                                                <div class="col-md-6"><label class="form-label">Registration
                                                        Number</label><input type="text" name="registration_number"
                                                        class="form-control" id="sch_reg_no"
                                                        value="{{ old('registration_number') }}"></div>
                                                <div class="col-md-6"><label class="form-label">Managing Trust/Society
                                                        Name</label><input type="text" name="managing_trust_or_society_name"
                                                        class="form-control"
                                                        value="{{ old('managing_trust_or_society_name') }}"></div>

                                                <div class="col-md-3">
                                                    <div class="form-check form-switch mt-4">
                                                        <input class="form-check-input" type="checkbox"
                                                            name="minority_status" id="minority_status" {{ old('minority_status') ? 'checked' : '' }}>
                                                        <label class="form-check-label" for="minority_status">Minority
                                                            Status</label>
                                                    </div>
                                                </div>
                                                <div class="col-md-3"><label class="form-label">Minority Type</label><input
                                                        type="text" name="minority_type" class="form-control"
                                                        value="{{ old('minority_type') }}"></div>

                                                <div class="col-md-12">
                                                    <label class="form-label">Legal Documents</label>
                                                    <div class="file-repeater-container" data-name="legal_documents_urls[]">
                                                        <div class="file-repeater-item mb-2">
                                                            <div class="input-group">
                                                                <input type="file" name="legal_documents_urls[]"
                                                                    class="form-control file-preview-input"
                                                                    id="sch_legal_docs">
                                                                <button type="button"
                                                                    class="btn btn-outline-danger remove-file-item"
                                                                    style="display:none;">x</button>
                                                            </div>
                                                            <div class="form-text text-muted">Image size should not exceed 2MB.</div>
                                                            <div class="file-preview mt-2"></div>
                                                        </div>
                                                        <button type="button"
                                                            class="btn btn-sm btn-outline-primary add-file-item">+ Add More
                                                            Document</button>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>

                                        <!-- C & D. Boards & Academic -->
                                        <div class="tab-pane fade" id="sch-boards" role="tabpanel">
                                            <div class="row g-3">
                                                <div class="col-12">
                                                    <h6 class="text-muted">Board Scope</h6>
                                                </div>
                                                <div class="col-md-12">
                                                    <label class="form-label">Education Boards Supported</label>
                                                    <div class="d-flex gap-3 flex-wrap">
                                                        @foreach(['CBSE', 'ICSE', 'State Board', 'IB', 'IGCSE'] as $board)
                                                            <div class="form-check">
                                                                <input class="form-check-input" type="checkbox"
                                                                    name="education_boards_supported[]" value="{{ $board }}"
                                                                    id="brd_{{ $board }}">
                                                                <label class="form-check-label"
                                                                    for="brd_{{ $board }}">{{ $board }}</label>
                                                            </div>
                                                        @endforeach
                                                    </div>
                                                </div>
                                                <div class="col-md-12">
                                                    <label class="form-label">Medium of Instruction</label>
                                                    <div class="input-group mb-2"><input type="text"
                                                            name="medium_of_instruction_supported[]" class="form-control"
                                                            placeholder="e.g. English, Hindi" value="English"><button
                                                            type="button" class="btn btn-outline-secondary"
                                                            onclick="alert('Use comma separated logic in backend if simplified or implementation repeater later')">Clone
                                                            logic implied</button></div>
                                                    <small class="text-muted">For demo, entering single value. Use standard
                                                        array logic if strictly required.</small>
                                                </div>
                                                <div class="col-md-12">
                                                    <div class="form-check form-switch">
                                                        <input class="form-check-input" type="checkbox"
                                                            name="international_curriculum_supported" id="intl_curr">
                                                        <label class="form-check-label" for="intl_curr">International
                                                            Curriculum Supported</label>
                                                    </div>
                                                </div>

                                                <div class="col-12 mt-3">
                                                    <h6 class="text-muted">Academic Philosophy</h6>
                                                </div>
                                                <div class="col-md-12">
                                                    <label class="form-label">Education Levels</label>
                                                    <div class="d-flex gap-2 flex-wrap">
                                                        @foreach(['Pre-Primary', 'Primary', 'Middle', 'Secondary', 'Senior Secondary'] as $lvl)
                                                            <div class="form-check">
                                                                <input class="form-check-input" type="checkbox"
                                                                    name="education_levels_supported[]" value="{{ $lvl }}"
                                                                    id="lvl_{{ Str::slug($lvl) }}">
                                                                <label class="form-check-label"
                                                                    for="lvl_{{ Str::slug($lvl) }}">{{ $lvl }}</label>
                                                            </div>
                                                        @endforeach
                                                    </div>
                                                </div>
                                                <div class="col-md-12">
                                                    <label class="form-label">Streams (for Senior Secondary)</label>
                                                    <div class="d-flex gap-2 flex-wrap">
                                                        @foreach(['Science', 'Commerce', 'Humanities', 'Vocational'] as $strm)
                                                            <div class="form-check">
                                                                <input class="form-check-input" type="checkbox"
                                                                    name="streams_supported[]" value="{{ $strm }}"
                                                                    id="strm_{{ $strm }}">
                                                                <label class="form-check-label"
                                                                    for="strm_{{ $strm }}">{{ $strm }}</label>
                                                            </div>
                                                        @endforeach
                                                    </div>
                                                </div>
                                                <div class="col-md-6"><label class="form-label">Pedagogy Model</label><input
                                                        type="text" name="pedagogy_model" class="form-control"
                                                        placeholder="e.g. Experiential Learning"></div>
                                                <div class="col-md-6"><label class="form-label">Focus Areas (Comma
                                                        separated)</label><input type="text" name="focus_areas[]"
                                                        class="form-control" placeholder="e.g. STEM, Sports, Arts"></div>
                                            </div>
                                        </div>

                                        <!-- E & F. Policies -->
                                        <div class="tab-pane fade" id="sch-policies" role="tabpanel">
                                            <div class="row g-3">
                                                <div class="col-12">
                                                    <h6 class="text-muted">Central Policies & Systems</h6>
                                                </div>
                                                @php
                                                    $centralPolicies = [
                                                        'centralized_curriculum_framework' => 'Centralized Curriculum Framework',
                                                        'centralized_teacher_training' => 'Centralized Teacher Training',
                                                        'centralized_assessment_policy' => 'Centralized Assessment Policy',
                                                        'centralized_lms_available' => 'Centralized LMS Available',
                                                        'centralized_parent_communication_system' => 'Centralized Parent Comm. System'
                                                    ];
                                                @endphp
                                                @foreach($centralPolicies as $key => $label)
                                                    <div class="col-md-4">
                                                        <div class="form-check form-switch">
                                                            <input class="form-check-input" type="checkbox" name="{{ $key }}"
                                                                id="{{ $key }}" {{ old($key) ? 'checked' : '' }}>
                                                            <label class="form-check-label"
                                                                for="{{ $key }}">{{ $label }}</label>
                                                        </div>
                                                    </div>
                                                @endforeach

                                                <div class="col-12 mt-3">
                                                    <h6 class="text-muted">Safety Policies</h6>
                                                </div>
                                                @php
                                                    $safetyPolicies = [
                                                        'child_safety_policy_available' => 'Child Safety Policy',
                                                        'posco_compliance_policy' => 'POSCO Compliance',
                                                        'anti_bullying_policy' => 'Anti-Bullying Policy',
                                                        'mental_health_policy' => 'Mental Health Policy',
                                                        'teacher_background_verification_policy' => 'Teacher Background Verification'
                                                    ];
                                                @endphp
                                                @foreach($safetyPolicies as $key => $label)
                                                    <div class="col-md-4">
                                                        <div class="form-check form-switch">
                                                            <input class="form-check-input" type="checkbox" name="{{ $key }}"
                                                                id="{{ $key }}" {{ old($key) ? 'checked' : '' }}>
                                                            <label class="form-check-label"
                                                                for="{{ $key }}">{{ $label }}</label>
                                                        </div>
                                                    </div>
                                                @endforeach
                                            </div>
                                        </div>

                                        <!-- G & H. Footprint & Digital -->
                                        <div class="tab-pane fade" id="sch-footprint" role="tabpanel">
                                            <div class="row g-3">
                                                <div class="col-12">
                                                    <h6 class="text-muted">Brand Footprint</h6>
                                                </div>
                                                <div class="col-md-4"><label class="form-label">Total Schools
                                                        Count</label><input type="number" name="total_schools_count"
                                                        class="form-control" value="{{ old('total_schools_count') }}"></div>
                                                <div class="col-md-4">
                                                    <div class="form-check form-switch mt-4">
                                                        <input class="form-check-input" type="checkbox"
                                                            name="national_presence" id="national_presence">
                                                        <label class="form-check-label" for="national_presence">National
                                                            Presence</label>
                                                    </div>
                                                </div>
                                                <div class="col-md-4">
                                                    <div class="form-check form-switch mt-4">
                                                        <input class="form-check-input" type="checkbox"
                                                            name="international_presence" id="international_presence">
                                                        <label class="form-check-label"
                                                            for="international_presence">International Presence</label>
                                                    </div>
                                                </div>
                                                <!-- Simplified Input for Arrays for Demo -->
                                                <div class="col-md-12"><label class="form-label">Cities Present In (Comma
                                                        separated)</label><input type="text" name="cities_present_in[]"
                                                        class="form-control" placeholder="Delhi, Mumbai"></div>
                                                <div class="col-md-12"><label class="form-label">States Present In (Comma
                                                        separated)</label><input type="text" name="states_present_in[]"
                                                        class="form-control" placeholder="Delhi, Maharashtra"></div>
                                                <div class="col-md-12"><label class="form-label">Flagship Schools (Comma
                                                        separated)</label><input type="text" name="flagship_schools[]"
                                                        class="form-control"
                                                        placeholder="e.g. DPS R.K. Puram, DPS Vasant Kunj"></div>

                                                <div class="col-12 mt-3">
                                                    <h6 class="text-muted">Digital Presence</h6>
                                                </div>
                                                <div class="col-md-6"><label class="form-label">Official
                                                        Website</label><input type="url" name="official_website"
                                                        class="form-control"></div>
                                                <div class="col-md-6"><label class="form-label">Admission Portal
                                                        URL</label><input type="url" name="admission_portal_url"
                                                        class="form-control"></div>
                                                <div class="col-md-6"><label class="form-label">Parent Portal
                                                        URL</label><input type="url" name="parent_portal_url"
                                                        class="form-control"></div>
                                                <div class="col-md-6"><label class="form-label">Student Portal
                                                        URL</label><input type="url" name="student_portal_url"
                                                        class="form-control"></div>
                                                <div class="col-md-12">
                                                    <div class="form-check form-switch">
                                                        <input class="form-check-input" type="checkbox"
                                                            name="mobile_app_available" id="mobile_app_available">
                                                        <label class="form-check-label" for="mobile_app_available">Mobile
                                                            App Available</label>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>

                                        <!-- I. Trust & SEO -->
                                        <div class="tab-pane fade" id="sch-seo" role="tabpanel">
                                            <div class="row g-3">
                                                <div class="col-md-6"><label class="form-label">Average Rating</label><input
                                                        type="number" step="0.1" name="average_rating" class="form-control"
                                                        max="5"></div>
                                                <div class="col-md-6"><label class="form-label">Total Reviews</label><input
                                                        type="number" name="total_reviews" class="form-control"></div>
                                                <div class="col-md-12"><label class="form-label">Awards & Recognition (Comma
                                                        separated)</label><input type="text" name="awards_and_recognition[]"
                                                        class="form-control"
                                                        placeholder="e.g. Best School 2024, Green Campus Award"></div>
                                                <div class="col-md-6"><label class="form-label">Meta Title</label><input
                                                        type="text" name="meta_title" class="form-control"></div>
                                                <div class="col-md-6"><label class="form-label">Canonical URL</label><input
                                                        type="text" name="canonical_url" class="form-control"></div>
                                                <div class="col-md-12"><label class="form-label">Meta
                                                        Description</label><textarea name="meta_description"
                                                        class="form-control" rows="2"></textarea></div>
                                                <div class="col-md-6"><label class="form-label">Schema Type</label><input
                                                        type="text" name="schema_type" class="form-control" value="School">
                                                </div>
                                                <div class="col-md-6">
                                                    <div class="form-check form-switch mt-4">
                                                        <input class="form-check-input" type="checkbox"
                                                            name="claimed_by_organization" id="claimed_by_organization">
                                                        <label class="form-check-label"
                                                            for="claimed_by_organization">Claimed by Organization</label>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                            </div>
                    </div>

                    <!-- Counselling Body Specific Fields Section -->
                    <div id="counselling-body-fields" class="col-12" style="display: none;">
                        <hr class="my-4">
                        <h5 class="mb-3 text-warning">Counselling Body Details (ID: 6)</h5>

                        <ul class="nav nav-tabs mb-3" id="cbTab" role="tablist">
                            <li class="nav-item"><button class="nav-link active" id="cb-core-tab" data-bs-toggle="tab"
                                    data-bs-target="#cb-core" type="button" role="tab">1. Core Identity</button></li>
                            <li class="nav-item"><button class="nav-link" id="cb-legal-tab" data-bs-toggle="tab"
                                    data-bs-target="#cb-legal" type="button" role="tab">2. Legal Status</button></li>
                            <li class="nav-item"><button class="nav-link" id="cb-scope-tab" data-bs-toggle="tab"
                                    data-bs-target="#cb-scope" type="button" role="tab">3. Scope</button></li>
                            <li class="nav-item"><button class="nav-link" id="cb-exams-tab" data-bs-toggle="tab"
                                    data-bs-target="#cb-exams" type="button" role="tab">4. Exams</button></li>
                            <li class="nav-item"><button class="nav-link" id="cb-seats-tab" data-bs-toggle="tab"
                                    data-bs-target="#cb-seats" type="button" role="tab">5. Seat Mgmt</button></li>
                            <li class="nav-item"><button class="nav-link" id="cb-gov-tab" data-bs-toggle="tab"
                                    data-bs-target="#cb-gov" type="button" role="tab">6. Governance</button></li>
                            <li class="nav-item"><button class="nav-link" id="cb-fees-tab" data-bs-toggle="tab"
                                    data-bs-target="#cb-fees" type="button" role="tab">7. Fees</button></li>
                            <li class="nav-item"><button class="nav-link" id="cb-tech-tab" data-bs-toggle="tab"
                                    data-bs-target="#cb-tech" type="button" role="tab">8. Tech</button></li>
                            <li class="nav-item"><button class="nav-link" id="cb-reporting-tab" data-bs-toggle="tab"
                                    data-bs-target="#cb-reporting" type="button" role="tab">9. Reporting</button></li>
                            <li class="nav-item"><button class="nav-link" id="cb-grievance-tab" data-bs-toggle="tab"
                                    data-bs-target="#cb-grievance" type="button" role="tab">10. Grievance</button></li>
                            <li class="nav-item"><button class="nav-link" id="cb-support-tab" data-bs-toggle="tab"
                                    data-bs-target="#cb-support" type="button" role="tab">11. Support</button></li>
                            <li class="nav-item"><button class="nav-link" id="cb-history-tab" data-bs-toggle="tab"
                                    data-bs-target="#cb-history" type="button" role="tab">12. History</button></li>
                            <li class="nav-item"><button class="nav-link" id="cb-seo-tab" data-bs-toggle="tab"
                                    data-bs-target="#cb-seo" type="button" role="tab">13. SEO</button></li>
                            <li class="nav-item"><button class="nav-link" id="cb-admin-tab" data-bs-toggle="tab"
                                    data-bs-target="#cb-admin" type="button" role="tab">14. Admin</button></li>
                        </ul>

                        <div class="tab-content" id="cbTabContent">
                            <!-- 1. CORE IDENTITY -->
                            <div class="tab-pane fade show active" id="cb-core" role="tabpanel">
                                <div class="row g-3">
                                    <div class="col-md-6"><label class="form-label">Organisation Name</label><input
                                            type="text" name="name" class="form-control"
                                            placeholder="e.g. Medical Counselling Committee" id="cb_name_sync"></div>
                                    <div class="col-md-6"><label class="form-label">Short Name</label><input type="text"
                                            name="short_name" class="form-control" placeholder="JoSAA, MCC"
                                            id="cb_short_sync"></div>
                                    <div class="col-md-6"><label class="form-label">Logo URL</label><input type="file"
                                            name="logo_url" class="form-control file-preview-input" accept="image/*">
                                        <div class="form-text text-muted">Image size should not exceed 2MB.</div>
                                        <div class="file-preview mt-2"></div>
                                    </div>
                                    <div class="col-md-6"><label class="form-label">Established Year</label><input
                                            type="number" name="established_year" class="form-control" placeholder="YYYY"
                                            id="cb_est_sync"></div>
                                    <div class="col-md-12"><label class="form-label">About Organization</label><textarea
                                            name="about_organisation" class="form-control" rows="3"></textarea></div>
                                    <div class="col-md-12"><label class="form-label">Mandate Description</label><textarea
                                            name="mandate_description" class="form-control" rows="3"></textarea></div>
                                </div>
                            </div>

                            <!-- 2. LEGAL & ADMINISTRATIVE STATUS -->
                            <div class="tab-pane fade" id="cb-legal" role="tabpanel">
                                <div class="row g-3">
                                    <div class="col-md-6"><label class="form-label">Authority Type</label><select
                                            name="authority_type" class="form-select">
                                            <option value="">Select</option>
                                            <option value="Statutory Body">Statutory Body</option>
                                            <option value="Government Committee">Government Committee</option>
                                            <option value="Autonomous Body">Autonomous Body</option>
                                        </select></div>
                                    <div class="col-md-6"><label class="form-label">Parent Ministry or
                                            Department</label><input type="text" name="parent_ministry_or_department"
                                            class="form-control" placeholder="Ministry of Health"></div>
                                    <div class="col-md-6"><label class="form-label">Established By</label><select
                                            name="established_by" class="form-select">
                                            <option value="">Select</option>
                                            <option value="Government Notification">Government Notification</option>
                                            <option value="Act / Resolution">Act / Resolution</option>
                                        </select></div>
                                    <div class="col-md-6"><label class="form-label">Legal Reference Document
                                            URL</label><input type="text" name="legal_reference_document_url"
                                            class="form-control"></div>
                                    <div class="col-md-6"><label class="form-label">Jurisdiction Scope</label><select
                                            name="jurisdiction_scope" class="form-select" id="cb_jurisdiction">
                                            <option value="">Select</option>
                                            <option value="National">National</option>
                                            <option value="State">State</option>
                                            <option value="Regional">Regional</option>
                                        </select></div>
                                    <div class="col-md-6" id="cb_juris_states_div" style="display:none;"><label
                                            class="form-label">Jurisdiction States</label><input type="text"
                                            name="jurisdiction_states" class="form-control" placeholder="Comma separated">
                                    </div>
                                </div>
                            </div>

                            <!-- 3. COUNSELLING FUNCTIONS & SCOPE -->
                            <div class="tab-pane fade" id="cb-scope" role="tabpanel">
                                <div class="row g-3">
                                    <div class="col-md-12"><label
                                            class="form-label d-block">Functions</label>@foreach(['Registration Management', 'Choice Filling', 'Seat Allocation', 'Reporting & Joining', 'Admission Confirmation'] as $f)
                                                <div class="form-check form-check-inline"><input class="form-check-input"
                                                        type="checkbox" name="counselling_functions[]" value="{{ $f }}"
                                                        id="cbf_{{ Str::slug($f) }}"><label class="form-check-label"
                                            for="cbf_{{ Str::slug($f) }}">{{ $f }}</label></div>@endforeach
                                    </div>
                                    <div class="col-md-12"><label class="form-label d-block">Counselling Types
                                            Supported</label>@foreach(['Centralised Counselling', 'State Counselling', 'Institutional Counselling'] as $t)
                                                <div class="form-check form-check-inline"><input class="form-check-input"
                                                        type="checkbox" name="counselling_types_supported[]" value="{{ $t }}"
                                                        id="cbt_{{ Str::slug($t) }}"><label class="form-check-label"
                                            for="cbt_{{ Str::slug($t) }}">{{ $t }}</label></div>@endforeach
                                    </div>
                                    <div class="col-md-12"><label class="form-label d-block">Education Domains
                                            Supported</label>@foreach(['Medical', 'Engineering', 'Law', 'Architecture'] as $d)
                                                <div class="form-check form-check-inline"><input class="form-check-input"
                                                        type="checkbox" name="education_domains_supported[]" value="{{ $d }}"
                                                        id="cbd_{{ Str::slug($d) }}"><label class="form-check-label"
                                            for="cbd_{{ Str::slug($d) }}">{{ $d }}</label></div>@endforeach
                                    </div>
                                    <div class="col-md-12"><label class="form-label d-block">Levels
                                            Supported</label>@foreach(['UG', 'PG', 'Diploma'] as $l)<div
                                                class="form-check form-check-inline"><input class="form-check-input"
                                                    type="checkbox" name="counselling_levels_supported[]" value="{{ $l }}"
                                                    id="cbl_{{ Str::slug($l) }}"><label class="form-check-label"
                                            for="cbl_{{ Str::slug($l) }}">{{ $l }}</label></div>@endforeach</div>
                                </div>
                            </div>

                            <!-- 4. EXAMS & ALLOCATION BASIS -->
                            <div class="tab-pane fade" id="cb-exams" role="tabpanel">
                                <div class="row g-3">
                                    <div class="col-md-12"><label class="form-label">Exams Used for Counselling
                                            (IDs)</label><input type="text" name="exams_used_for_counselling_ids"
                                            class="form-control" placeholder="Comma separated IDs"></div>
                                    <div class="col-md-6"><label class="form-label">Allocation Basis</label><select
                                            name="allocation_basis" class="form-select">
                                            <option value="">Select</option>
                                            <option value="Rank">Rank</option>
                                            <option value="Score">Score</option>
                                            <option value="Composite Merit">Composite Merit</option>
                                        </select></div>
                                    <div class="col-md-3">
                                        <div class="form-check form-switch mt-4"><input class="form-check-input"
                                                type="checkbox" name="rank_source_validation_required"
                                                id="cb_rank_val"><label class="form-check-label" for="cb_rank_val">Rank
                                                Source Validation Required</label></div>
                                    </div>
                                    <div class="col-md-3">
                                        <div class="form-check form-switch mt-4"><input class="form-check-input"
                                                type="checkbox" name="multiple_exam_support" id="cb_multi_exam"><label
                                                class="form-check-label" for="cb_multi_exam">Multiple Exam Support</label>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- 5. SEAT MANAGEMENT CAPABILITIES -->
                            <div class="tab-pane fade" id="cb-seats" role="tabpanel">
                                <div class="row g-3">
                                    <div class="col-md-3">
                                        <div class="form-check form-switch mt-4"><input class="form-check-input"
                                                type="checkbox" name="seat_matrix_management" id="cb_seat_mgmt"><label
                                                class="form-check-label" for="cb_seat_mgmt">Seat Matrix Management</label>
                                        </div>
                                    </div>
                                    <div class="col-md-6"><label class="form-label">Seat Matrix Source</label><select
                                            name="seat_matrix_source" class="form-select">
                                            <option value="">Select</option>
                                            <option value="Institutions">Institutions</option>
                                            <option value="Regulatory Body">Regulatory Body</option>
                                            <option value="Combined">Combined</option>
                                        </select></div>
                                    <div class="col-md-12"><label class="form-label d-block">Quota Types
                                            Managed</label>@foreach(['AIQ', 'State Quota', 'Institutional Quota', 'Management Quota'] as $q)
                                                <div class="form-check form-check-inline"><input class="form-check-input"
                                                        type="checkbox" name="quota_types_managed[]" value="{{ $q }}"
                                                        id="cbq_{{ Str::slug($q) }}"><label class="form-check-label"
                                            for="cbq_{{ Str::slug($q) }}">{{ $q }}</label></div>@endforeach
                                    </div>
                                    <div class="col-md-12"><label class="form-label">Reservation Policy
                                            Reference</label><textarea name="reservation_policy_reference"
                                            class="form-control" rows="2"></textarea></div>
                                    <div class="col-md-6">
                                        <div class="form-check form-switch"><input class="form-check-input" type="checkbox"
                                                name="seat_conversion_rules_supported" id="cb_seat_conv"><label
                                                class="form-check-label" for="cb_seat_conv">Seat Conversion Rules
                                                Supported</label></div>
                                    </div>
                                </div>
                            </div>

                            <!-- 6. COUNSELLING PROCESS GOVERNANCE -->
                            <div class="tab-pane fade" id="cb-gov" role="tabpanel">
                                <div class="row g-3">
                                    <div class="col-md-6"><label class="form-label">Rounds Supported</label><input
                                            type="text" name="rounds_supported" class="form-control"></div>
                                    <div class="col-md-12"><label class="form-label d-block">Round
                                            Types</label>@foreach(['Regular', 'Mop-up', 'Stray Vacancy'] as $rt)<div
                                                class="form-check form-check-inline"><input class="form-check-input"
                                                    type="checkbox" name="round_types[]" value="{{ $rt }}"
                                                    id="cbrt_{{ Str::slug($rt) }}"><label class="form-check-label"
                                            for="cbrt_{{ Str::slug($rt) }}">{{ $rt }}</label></div>@endforeach</div>
                                    <div class="col-md-4">
                                        <div class="form-check form-switch"><input class="form-check-input" type="checkbox"
                                                name="choice_locking_mandatory" id="cb_choice_lock"><label
                                                class="form-check-label" for="cb_choice_lock">Choice Locking
                                                Mandatory</label></div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="form-check form-switch"><input class="form-check-input" type="checkbox"
                                                name="seat_upgradation_allowed" id="cb_seat_up"><label
                                                class="form-check-label" for="cb_seat_up">Seat Upgradation Allowed</label>
                                        </div>
                                    </div>
                                    <div class="col-md-12"><label class="form-label">Withdrawal Rules
                                            Summary</label><textarea name="withdrawal_rules_summary" class="form-control"
                                            rows="2"></textarea></div>
                                    <div class="col-md-12"><label class="form-label">Exit Rules Summary</label><textarea
                                            name="exit_rules_summary" class="form-control" rows="2"></textarea></div>
                                </div>
                            </div>

                            <!-- 7. FEES & FINANCIAL HANDLING -->
                            <div class="tab-pane fade" id="cb-fees" role="tabpanel">
                                <div class="row g-3">
                                    <div class="col-md-4">
                                        <div class="form-check form-switch mt-4"><input class="form-check-input"
                                                type="checkbox" name="counselling_fee_collection_supported"
                                                id="cb_fee_coll"><label class="form-check-label"
                                                for="cb_fee_coll">Counselling Fee Collection Supported</label></div>
                                    </div>
                                    <div class="col-md-4"><label class="form-label">Fee Collection Mode</label><select
                                            name="fee_collection_mode" class="form-select">
                                            <option value="">Select</option>
                                            <option value="Direct to Authority">Direct to Authority</option>
                                            <option value="Through Exam Portal">Through Exam Portal</option>
                                        </select></div>
                                    <div class="col-md-4"><label class="form-label">Refund Processing
                                            Responsibility</label><select name="refund_processing_responsibility"
                                            class="form-select">
                                            <option value="">Select</option>
                                            <option value="Authority">Authority</option>
                                            <option value="Exam Body">Exam Body</option>
                                        </select></div>
                                    <div class="col-md-4">
                                        <div class="form-check form-switch"><input class="form-check-input" type="checkbox"
                                                name="security_deposit_handling" id="cb_sec_dep"><label
                                                class="form-check-label" for="cb_sec_dep">Security Deposit Handling</label>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- 8. TECHNOLOGY & INFRASTRUCTURE -->
                            <div class="tab-pane fade" id="cb-tech" role="tabpanel">
                                <div class="row g-3">
                                    <div class="col-md-6"><label class="form-label">Counselling Portal URL</label><input
                                            type="url" name="counselling_portal_url" class="form-control"></div>
                                    <div class="col-md-3">
                                        <div class="form-check form-switch mt-4"><input class="form-check-input"
                                                type="checkbox" name="candidate_login_system_available" id="cb_login"><label
                                                class="form-check-label" for="cb_login">Candidate Login Available</label>
                                        </div>
                                    </div>
                                    <div class="col-md-3">
                                        <div class="form-check form-switch mt-4"><input class="form-check-input"
                                                type="checkbox" name="choice_filling_system_available" id="cb_choice"><label
                                                class="form-check-label" for="cb_choice">Choice Filling Available</label>
                                        </div>
                                    </div>
                                    <div class="col-md-3">
                                        <div class="form-check form-switch"><input class="form-check-input" type="checkbox"
                                                name="auto_seat_allocation_engine" id="cb_auto_seat"><label
                                                class="form-check-label" for="cb_auto_seat">Auto Seat Allocation
                                                Engine</label></div>
                                    </div>
                                    <div class="col-md-3">
                                        <div class="form-check form-switch"><input class="form-check-input" type="checkbox"
                                                name="api_integration_supported" id="cb_api"><label class="form-check-label"
                                                for="cb_api">API Integration Supported</label></div>
                                    </div>
                                    <div class="col-md-6"><label class="form-label">Data Security Standards</label><input
                                            type="text" name="data_security_standards" class="form-control"></div>
                                </div>
                            </div>

                            <!-- 9. REPORTING, VERIFICATION & INSTITUTION INTERFACE -->
                            <div class="tab-pane fade" id="cb-reporting" role="tabpanel">
                                <div class="row g-3">
                                    <div class="col-md-4">
                                        <div class="form-check form-switch mt-4"><input class="form-check-input"
                                                type="checkbox" name="institution_reporting_interface_available"
                                                id="cb_inst_rep"><label class="form-check-label"
                                                for="cb_inst_rep">Institution Reporting Interface</label></div>
                                    </div>
                                    <div class="col-md-4"><label class="form-label">Document Verification
                                            Mode</label><select name="document_verification_mode" class="form-select">
                                            <option value="">Select</option>
                                            <option value="Online">Online</option>
                                            <option value="Physical">Physical</option>
                                            <option value="Hybrid">Hybrid</option>
                                        </select></div>
                                    <div class="col-md-12"><label class="form-label">Institution Confirmation Process
                                            Summary</label><textarea name="institution_confirmation_process_summary"
                                            class="form-control" rows="2"></textarea></div>
                                    <div class="col-md-12"><label class="form-label">MIS Reporting Controls</label><textarea
                                            name="mis_reporting_controls" class="form-control" rows="2"></textarea></div>
                                </div>
                            </div>

                            <!-- 10. GRIEVANCE, APPEALS & TRANSPARENCY -->
                            <div class="tab-pane fade" id="cb-grievance" role="tabpanel">
                                <div class="row g-3">
                                    <div class="col-md-12"><label class="form-label">Grievance Redressal
                                            Mechanism</label><textarea name="grievance_redressal_mechanism"
                                            class="form-control" rows="2"></textarea></div>
                                    <div class="col-md-12"><label class="form-label">Appeal Process Summary</label><textarea
                                            name="appeal_process_summary" class="form-control" rows="2"></textarea></div>
                                    <div class="col-md-12"><label class="form-label">Grievance Contact
                                            Details</label><textarea name="grievance_contact_details" class="form-control"
                                            rows="2"></textarea></div>
                                    <div class="col-md-3">
                                        <div class="form-check form-switch"><input class="form-check-input" type="checkbox"
                                                name="rti_applicable" id="cb_rti"><label class="form-check-label"
                                                for="cb_rti">RTI Applicable</label></div>
                                    </div>
                                    <div class="col-md-3">
                                        <div class="form-check form-switch"><input class="form-check-input" type="checkbox"
                                                name="audit_conducted" id="cb_audit"><label class="form-check-label"
                                                for="cb_audit">Audit Conducted</label></div>
                                    </div>
                                </div>
                            </div>

                            <!-- 11. COMMUNICATION & SUPPORT -->
                            <div class="tab-pane fade" id="cb-support" role="tabpanel">
                                <div class="row g-3">
                                    <div class="col-md-6"><label class="form-label">Candidate Support URL</label><input
                                            type="url" name="candidate_support_url" class="form-control"></div>
                                    <div class="col-md-6"><label class="form-label">Candidate Handbook URL</label><input
                                            type="url" name="candidate_handbook_url" class="form-control"></div>
                                    <div class="col-md-6"><label class="form-label">Helpdesk Toll-free Number</label><input
                                            type="text" name="helpdesk_toll_free_number" class="form-control"></div>
                                    <div class="col-md-6"><label class="form-label">Helpdesk Operational Hours</label><input
                                            type="text" name="helpdesk_operational_hours" class="form-control"></div>
                                    <div class="col-md-12"><label class="form-label">Official Notifications
                                            URLs</label><input type="text" name="official_notifications_urls"
                                            class="form-control" placeholder="Comma separated URLs"></div>
                                    <div class="col-md-12"><label class="form-label">Social Media Handles</label><input
                                            type="text" name="social_media_handles" class="form-control"
                                            placeholder="Comma separated URLs"></div>
                                </div>
                            </div>

                            <!-- 12. TRUST, HISTORY & SCALE -->
                            <div class="tab-pane fade" id="cb-history" role="tabpanel">
                                <div class="row g-3">
                                    <div class="col-md-6"><label class="form-label">Years of Operation</label><input
                                            type="number" name="years_of_operation" class="form-control"></div>
                                    <div class="col-md-6"><label class="form-label">Total Candidates Handled
                                            Estimate</label><input type="text" name="total_candidates_handled_estimate"
                                            class="form-control"></div>
                                    <div class="col-md-6"><label class="form-label">Total Seats Allocated
                                            Estimate</label><input type="text" name="total_seats_allocated_estimate"
                                            class="form-control"></div>
                                    <div class="col-md-6"><label class="form-label">Public Trust Rating</label><input
                                            type="number" step="0.1" name="public_trust_rating" class="form-control"></div>
                                </div>
                            </div>

                            <!-- 13. DIGITAL & SEO -->
                            <div class="tab-pane fade" id="cb-seo" role="tabpanel">
                                <div class="row g-3">
                                    <div class="col-md-6"><label class="form-label">Schema Type</label><select
                                            name="schema_type" class="form-select">
                                            <option value="GovernmentOrganization">GovernmentOrganization</option>
                                        </select></div>
                                    <div class="col-md-6"><label class="form-label">Meta Title</label><input type="text"
                                            name="meta_title" class="form-control"></div>
                                    <div class="col-md-12"><label class="form-label">Meta Description</label><textarea
                                            name="meta_description" class="form-control" rows="2"></textarea></div>
                                    <div class="col-md-12"><label class="form-label">Focus Keywords</label><input
                                            type="text" name="focus_keywords" class="form-control"
                                            placeholder="Comma separated"></div>
                                    <div class="col-md-6"><label class="form-label">Canonical URL</label><input type="url"
                                            name="canonical_url" class="form-control"></div>
                                </div>
                            </div>

                            <!-- 14. ADMIN & PLATFORM CONTROL -->
                            <div class="tab-pane fade" id="cb-admin" role="tabpanel">
                                <div class="row g-3">
                                    <div class="col-md-6"><label class="form-label">Verification Status</label><select
                                            name="verification_status" class="form-select">
                                            <option value="Pending">Pending</option>
                                            <option value="Verified">Verified</option>
                                            <option value="Rejected">Rejected</option>
                                        </select></div>
                                    <div class="col-md-3">
                                        <div class="form-check form-switch mt-4"><input class="form-check-input"
                                                type="checkbox" name="claimed_by_authority" id="cb_claimed"><label
                                                class="form-check-label" for="cb_claimed">Claimed by Authority</label></div>
                                    </div>
                                    <div class="col-md-6"><label class="form-label">Data Source</label><input type="text"
                                            name="data_source" class="form-control"></div>
                                    <div class="col-md-3"><label class="form-label">Confidence Score</label><input
                                            type="number" name="confidence_score" class="form-control"></div>
                                    <div class="col-md-6"><label class="form-label">Status</label><select name="status"
                                            class="form-select">
                                            <option value="1">Active</option>
                                            <option value="0">Inactive</option>
                                        </select></div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Regulatory Body Specific Fields Section -->
                    <div id="regulatory-body-fields" class="col-12" style="display: none;">
                        <hr class="my-4">
                        <h5 class="mb-3 text-danger">Regulatory Body Details (ID: 7)</h5>

                        <ul class="nav nav-tabs mb-3" id="rbTab" role="tablist">
                            <li class="nav-item"><button class="nav-link active" id="rb-core-tab" data-bs-toggle="tab"
                                    data-bs-target="#rb-core" type="button" role="tab">1. Core Identity</button></li>
                            <li class="nav-item"><button class="nav-link" id="rb-legal-tab" data-bs-toggle="tab"
                                    data-bs-target="#rb-legal" type="button" role="tab">2. Legal Status</button></li>
                            <li class="nav-item"><button class="nav-link" id="rb-scope-tab" data-bs-toggle="tab"
                                    data-bs-target="#rb-scope" type="button" role="tab">3. Scope</button></li>
                            <li class="nav-item"><button class="nav-link" id="rb-standards-tab" data-bs-toggle="tab"
                                    data-bs-target="#rb-standards" type="button" role="tab">4. Standards</button></li>
                            <li class="nav-item"><button class="nav-link" id="rb-affiliation-tab" data-bs-toggle="tab"
                                    data-bs-target="#rb-affiliation" type="button" role="tab">5. Affiliation</button></li>
                            <li class="nav-item"><button class="nav-link" id="rb-monitoring-tab" data-bs-toggle="tab"
                                    data-bs-target="#rb-monitoring" type="button" role="tab">6. Monitoring</button></li>
                            <li class="nav-item"><button class="nav-link" id="rb-fees-tab" data-bs-toggle="tab"
                                    data-bs-target="#rb-fees" type="button" role="tab">7. Fees</button></li>
                            <li class="nav-item"><button class="nav-link" id="rb-tech-tab" data-bs-toggle="tab"
                                    data-bs-target="#rb-tech" type="button" role="tab">8. Tech</button></li>
                            <li class="nav-item"><button class="nav-link" id="rb-reporting-tab" data-bs-toggle="tab"
                                    data-bs-target="#rb-reporting" type="button" role="tab">9. Reporting</button></li>
                            <li class="nav-item"><button class="nav-link" id="rb-grievance-tab" data-bs-toggle="tab"
                                    data-bs-target="#rb-grievance" type="button" role="tab">10. Grievance</button></li>
                            <li class="nav-item"><button class="nav-link" id="rb-support-tab" data-bs-toggle="tab"
                                    data-bs-target="#rb-support" type="button" role="tab">11. Support</button></li>
                            <li class="nav-item"><button class="nav-link" id="rb-history-tab" data-bs-toggle="tab"
                                    data-bs-target="#rb-history" type="button" role="tab">12. Scale</button></li>
                            <li class="nav-item"><button class="nav-link" id="rb-seo-tab" data-bs-toggle="tab"
                                    data-bs-target="#rb-seo" type="button" role="tab">13. SEO</button></li>
                            <li class="nav-item"><button class="nav-link" id="rb-admin-tab" data-bs-toggle="tab"
                                    data-bs-target="#rb-admin" type="button" role="tab">14. Admin</button></li>
                        </ul>

                        <div class="tab-content" id="rbTabContent">
                            <!-- 1. CORE IDENTITY -->
                            <div class="tab-pane fade show active" id="rb-core" role="tabpanel">
                                <div class="row g-3">
                                    <div class="col-md-6"><label class="form-label">Organisation Name</label><input
                                            type="text" name="name" class="form-control"
                                            placeholder="e.g. University Grants Commission" id="rb_name_sync"></div>
                                    <div class="col-md-6"><label class="form-label">Abbreviation</label><input type="text"
                                            name="abbreviation" class="form-control" placeholder="UGC, AICTE"></div>
                                    <div class="col-md-6"><label class="form-label">Short Name</label><input type="text"
                                            name="short_name" class="form-control" id="rb_short_sync"></div>
                                    <div class="col-md-6"><label class="form-label">Logo URL</label><input type="file"
                                            name="logo_url" class="form-control file-preview-input" accept="image/*">
                                        <div class="form-text text-muted">Image size should not exceed 2MB.</div></div>
                                    <div class="col-md-6"><label class="form-label">Established Year</label><input
                                            type="number" name="established_year" class="form-control" id="rb_est_sync">
                                    </div>
                                    <div class="col-md-12"><label class="form-label">About Organization</label><textarea
                                            name="about_organisation" class="form-control" rows="3"></textarea></div>
                                    <div class="col-md-12"><label class="form-label">Mandate Description</label><textarea
                                            name="mandate_description" class="form-control" rows="3"></textarea></div>
                                </div>
                            </div>

                            <!-- 2. LEGAL & ADMINISTRATIVE STATUS -->
                            <div class="tab-pane fade" id="rb-legal" role="tabpanel">
                                <div class="row g-3">
                                    <div class="col-md-6"><label class="form-label">Authority Type</label><select
                                            name="authority_type" class="form-select">
                                            <option value="">Select</option>
                                            <option value="Constitutional Body">Constitutional Body</option>
                                            <option value="Statutory Body">Statutory Body</option>
                                            <option value="Government Agency">Government Agency</option>
                                            <option value="Autonomous Body">Autonomous Body</option>
                                        </select></div>
                                    <div class="col-md-6"><label class="form-label">Parent Ministry/Department</label><input
                                            type="text" name="parent_ministry_or_department" class="form-control"></div>
                                    <div class="col-md-6"><label class="form-label">Established By</label><select
                                            name="established_by" class="form-select">
                                            <option value="">Select</option>
                                            <option value="Act of Parliament">Act of Parliament</option>
                                            <option value="Government Resolution">Government Resolution</option>
                                            <option value="Government Notification">Government Notification</option>
                                            <option value="Act / Resolution">Act / Resolution</option>
                                        </select></div>
                                    <div class="col-md-6"><label class="form-label">Legal Reference Document
                                            URL</label><input type="text" name="legal_reference_document_url"
                                            class="form-control"></div>
                                    <div class="col-md-6"><label class="form-label">Jurisdiction Scope</label><select
                                            name="jurisdiction_scope" class="form-select" id="rb_jurisdiction">
                                            <option value="">Select</option>
                                            <option value="National">National</option>
                                            <option value="State">State</option>
                                            <option value="Multi-State">Multi-State</option>
                                            <option value="Regional">Regional</option>
                                        </select></div>
                                    <div class="col-md-6" id="rb_juris_states_div" style="display:none;"><label
                                            class="form-label">Jurisdiction States</label><input type="text"
                                            name="jurisdiction_states" class="form-control" placeholder="Comma separated">
                                    </div>
                                </div>
                            </div>

                            <!-- 3. SCOPE & FUNCTIONS -->
                            <div class="tab-pane fade" id="rb-scope" role="tabpanel">
                                <div class="row g-3">
                                    <div class="col-md-12"><label class="form-label d-block">Regulatory
                                            Functions</label>@foreach(['Policy Making', 'Accreditation', 'Funding', 'Inspection', 'Standard Setting', 'curriculum Framework'] as $f)
                                                <div class="form-check form-check-inline"><input class="form-check-input"
                                                        type="checkbox" name="functions[]" value="{{ $f }}"
                                                        id="rbf_{{ Str::slug($f) }}"><label class="form-check-label"
                                            for="rbf_{{ Str::slug($f) }}">{{ $f }}</label></div>@endforeach
                                    </div>
                                    <div class="col-md-12"><label class="form-label d-block">Education Domains
                                            Supported</label>@foreach(['Higher Education', 'Technical', 'Medical', 'Teacher Training', 'Vocational'] as $d)
                                                <div class="form-check form-check-inline"><input class="form-check-input"
                                                        type="checkbox" name="education_domains_supported[]" value="{{ $d }}"
                                                        id="rbd_{{ Str::slug($d) }}"><label class="form-check-label"
                                            for="rbd_{{ Str::slug($d) }}">{{ $d }}</label></div>@endforeach
                                    </div>
                                    <div class="col-md-12"><label class="form-label d-block">Counselling
                                            Roles</label>@foreach(['Regulation', 'Advisory', 'Direct Control', 'Grievance Redressal'] as $r)
                                                <div class="form-check form-check-inline"><input class="form-check-input"
                                                        type="checkbox" name="counselling_functions[]" value="{{ $r }}"
                                                        id="rbcf_{{ Str::slug($r) }}"><label class="form-check-label"
                                            for="rbcf_{{ Str::slug($r) }}">{{ $r }}</label></div>@endforeach
                                    </div>
                                </div>
                            </div>

                            <!-- 4. STANDARDS & COMPLIANCE -->
                            <div class="tab-pane fade" id="rb-standards" role="tabpanel">
                                <div class="row g-3">
                                    <div class="col-md-6"><label class="form-label">Allocation Basis</label><select
                                            name="allocation_basis" class="form-select">
                                            <option value="">Select</option>
                                            <option value="Rank">Rank</option>
                                            <option value="Score">Score</option>
                                            <option value="Composite Merit">Composite Merit</option>
                                        </select></div>
                                    <div class="col-md-3">
                                        <div class="form-check form-switch mt-4"><input class="form-check-input"
                                                type="checkbox" name="rank_source_validation_required"
                                                id="rb_rank_val"><label class="form-check-label" for="rb_rank_val">Rank
                                                Validation</label></div>
                                    </div>
                                    <div class="col-md-3">
                                        <div class="form-check form-switch mt-4"><input class="form-check-input"
                                                type="checkbox" name="multiple_exam_support" id="rb_multi_exam"><label
                                                class="form-check-label" for="rb_multi_exam">Multi-Exam Support</label>
                                        </div>
                                    </div>
                                    <div class="col-md-6"><label class="form-label">Seat Matrix Source</label><select
                                            name="seat_matrix_source" class="form-select">
                                            <option value="">Select</option>
                                            <option value="Institutions">Institutions</option>
                                            <option value="Regulatory Body">Regulatory Body</option>
                                            <option value="Combined">Combined</option>
                                        </select></div>
                                    <div class="col-md-6"><label class="form-label">Data Security Standards</label><input
                                            type="text" name="data_security_standards" class="form-control"></div>
                                </div>
                            </div>

                            <!-- 5. AFFILIATION & RECOGNITION -->
                            <div class="tab-pane fade" id="rb-affiliation" role="tabpanel">
                                <div class="row g-3">
                                    <div class="col-md-6"><label class="form-label">Institutions Covered
                                            (Count)</label><input type="number" name="institutions_covered_count"
                                            class="form-control"></div>
                                    <div class="col-md-6"><label class="form-label">States Covered (Count)</label><input
                                            type="number" name="states_covered_count" class="form-control"></div>
                                    <div class="col-md-12"><label class="form-label">Quota Types
                                            Managed</label>@foreach(['AIQ', 'State Quota', 'NRI', 'Management'] as $q)
                                                <div class="form-check form-check-inline"><input class="form-check-input"
                                                        type="checkbox" name="quota_types_managed[]" value="{{ $q }}"
                                                        id="rbq_{{ Str::slug($q) }}"><label class="form-check-label"
                                            for="rbq_{{ Str::slug($q) }}">{{ $q }}</label></div>@endforeach
                                    </div>
                                    <div class="col-md-12"><label class="form-label">Reservation Policy
                                            Reference</label><textarea name="reservation_policy_reference"
                                            class="form-control" rows="2"></textarea></div>
                                </div>
                            </div>

                            <!-- 6. MONITORING & INSPECTIONS -->
                            <div class="tab-pane fade" id="rb-monitoring" role="tabpanel">
                                <div class="row g-3">
                                    <div class="col-md-3">
                                        <div class="form-check form-switch mt-4"><input class="form-check-input"
                                                type="checkbox" name="audit_conducted" id="rb_audit"><label
                                                class="form-check-label" for="rb_audit">Audit Conducted</label></div>
                                    </div>
                                    <div class="col-md-3">
                                        <div class="form-check form-switch mt-4"><input class="form-check-input"
                                                type="checkbox" name="seat_matrix_management" id="rb_seat_mgmt"><label
                                                class="form-check-label" for="rb_seat_mgmt">Seat Mgmt</label></div>
                                    </div>
                                    <div class="col-md-6"><label class="form-label">MIS Reporting Controls</label><textarea
                                            name="mis_reporting_controls" class="form-control" rows="2"></textarea></div>
                                    <div class="col-md-12"><label class="form-label">Document Verification
                                            Mode</label><select name="document_verification_mode" class="form-select">
                                            <option value="Online">Online</option>
                                            <option value="Physical">Physical</option>
                                            <option value="Hybrid">Hybrid</option>
                                        </select></div>
                                </div>
                            </div>

                            <!-- 7. FEES & FINANCIALS -->
                            <div class="tab-pane fade" id="rb-fees" role="tabpanel">
                                <div class="row g-3">
                                    <div class="col-md-4">
                                        <div class="form-check form-switch mt-4"><input class="form-check-input"
                                                type="checkbox" name="counselling_fee_collection_supported"
                                                id="rb_fee_coll"><label class="form-check-label" for="rb_fee_coll">Fee
                                                Collection</label></div>
                                    </div>
                                    <div class="col-md-4"><label class="form-label">Fee Mode</label><select
                                            name="fee_collection_mode" class="form-select">
                                            <option value="Direct to Authority">Direct to Authority</option>
                                            <option value="Through Exam Portal">Through Exam Portal</option>
                                        </select></div>
                                    <div class="col-md-4"><label class="form-label">Refund Responsibility</label><select
                                            name="refund_processing_responsibility" class="form-select">
                                            <option value="Authority">Authority</option>
                                            <option value="Exam Body">Exam Body</option>
                                        </select></div>
                                </div>
                            </div>

                            <!-- 8. TECH & DIGITAL -->
                            <div class="tab-pane fade" id="rb-tech" role="tabpanel">
                                <div class="row g-3">
                                    <div class="col-md-6"><label class="form-label">Portal URL</label><input type="url"
                                            name="counselling_portal_url" class="form-control"></div>
                                    <div class="col-md-3">
                                        <div class="form-check form-switch mt-4"><input class="form-check-input"
                                                type="checkbox" name="candidate_login_system_available" id="rb_login"><label
                                                class="form-check-label" for="rb_login">Login Avail.</label></div>
                                    </div>
                                    <div class="col-md-3">
                                        <div class="form-check form-switch mt-4"><input class="form-check-input"
                                                type="checkbox" name="api_integration_supported" id="rb_api"><label
                                                class="form-check-label" for="rb_api">API Support</label></div>
                                    </div>
                                </div>
                            </div>

                            <!-- 9. REPORTING -->
                            <div class="tab-pane fade" id="rb-reporting" role="tabpanel">
                                <div class="row g-3">
                                    <div class="col-md-6">
                                        <div class="form-check form-switch mt-4"><input class="form-check-input"
                                                type="checkbox" name="institution_reporting_interface_available"
                                                id="rb_inst_rep"><label class="form-check-label" for="rb_inst_rep">Inst.
                                                Reporting</label></div>
                                    </div>
                                    <div class="col-md-12"><label class="form-label">Institution Confirmation
                                            Process</label><textarea name="institution_confirmation_process_summary"
                                            class="form-control" rows="2"></textarea></div>
                                </div>
                            </div>

                            <!-- 10. GRIEVANCE -->
                            <div class="tab-pane fade" id="rb-grievance" role="tabpanel">
                                <div class="row g-3">
                                    <div class="col-md-12"><label class="form-label">Grievance Mechanism</label><textarea
                                            name="grievance_redressal_mechanism" class="form-control" rows="2"></textarea>
                                    </div>
                                    <div class="col-md-12"><label class="form-label">Appeal Process</label><textarea
                                            name="appeal_process_summary" class="form-control" rows="2"></textarea></div>
                                    <div class="col-md-3">
                                        <div class="form-check form-switch mt-4"><input class="form-check-input"
                                                type="checkbox" name="rti_applicable" id="rb_rti"><label
                                                class="form-check-label" for="rb_rti">RTI App.</label></div>
                                    </div>
                                </div>
                            </div>

                            <!-- 11. SUPPORT -->
                            <div class="tab-pane fade" id="rb-support" role="tabpanel">
                                <div class="row g-3">
                                    <div class="col-md-6"><label class="form-label">Support URL</label><input type="url"
                                            name="candidate_support_url" class="form-control"></div>
                                    <div class="col-md-6"><label class="form-label">Handbook URL</label><input type="url"
                                            name="candidate_handbook_url" class="form-control"></div>
                                    <div class="col-md-12"><label class="form-label">Media Mentions</label><input
                                            type="text" name="media_mentions" class="form-control"
                                            placeholder="Comma separated"></div>
                                </div>
                            </div>

                            <!-- 12. SCALE & IMPACT -->
                            <div class="tab-pane fade" id="rb-history" role="tabpanel">
                                <div class="row g-3">
                                    <div class="col-md-6"><label class="form-label">Years of Operation</label><input
                                            type="number" name="years_of_operation" class="form-control"></div>
                                    <div class="col-md-6"><label class="form-label">Annual Candidate Volume</label><input
                                            type="text" name="annual_candidate_volume" class="form-control"></div>
                                    <div class="col-md-4"><label class="form-label">Trust Rating</label><input type="number"
                                            step="0.1" name="public_trust_rating" class="form-control"></div>
                                </div>
                            </div>

                            <!-- 13. SEO -->
                            <div class="tab-pane fade" id="rb-seo" role="tabpanel">
                                <div class="row g-3">
                                    <div class="col-md-6"><label class="form-label">Meta Title</label><input type="text"
                                            name="meta_title" class="form-control"></div>
                                    <div class="col-md-6"><label class="form-label">Canonical URL</label><input type="url"
                                            name="canonical_url" class="form-control"></div>
                                    <div class="col-md-12"><label class="form-label">Meta Description</label><textarea
                                            name="meta_description" class="form-control" rows="2"></textarea></div>
                                </div>
                            </div>

                            <!-- 14. ADMIN -->
                            <div class="tab-pane fade" id="rb-admin" role="tabpanel">
                                <div class="row g-3">
                                    <div class="col-md-6"><label class="form-label">Verification Status</label><select
                                            name="verification_status" class="form-select">
                                            <option value="Pending">Pending</option>
                                            <option value="Verified">Verified</option>
                                            <option value="Rejected">Rejected</option>
                                        </select></div>
                                    <div class="col-md-3">
                                        <div class="form-check form-switch mt-4"><input class="form-check-input"
                                                type="checkbox" name="claimed_by_authority" id="rb_claimed"><label
                                                class="form-check-label" for="rb_claimed">Claimed</label></div>
                                    </div>
                                    <div class="col-md-3"><label class="form-label">Status</label><select name="status"
                                            class="form-select">
                                            <option value="Active">Active</option>
                                            <option value="Inactive">Inactive</option>
                                        </select></div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div> <!-- End Row -->

                <div class="col-12 mt-4 mb-4 text-end">
                    <button type="submit" class="btn btn-primary btn-lg"><i class="fas fa-save me-2"></i> Save Organisation</button>
                </div>
            </div>
            </form>
        </div>
    </div>
    </div>
    </div>
    </div>

    @push('js')
        <script>
            document.addEventListener('DOMContentLoaded', function () {
                initializeTinyMCE('.editor');
                const typeSelect = document.getElementById('organisation_type_id');
                const uniFields = document.getElementById('university-fields');
                const instFields = document.getElementById('institute-fields');
                const schoolFields = document.getElementById('school-fields');
                const ecbFields = document.getElementById('exam-conducting-body-fields');

                function toggleFields() {
                    if (!typeSelect) return;
                    const selectedOption = typeSelect.options[typeSelect.selectedIndex];
                    if (!selectedOption) return;

                    const val = typeSelect.value;
                    const title = (selectedOption.getAttribute('data-title') || selectedOption.text).toLowerCase();
                    const category = selectedOption.getAttribute('data-category');

                    console.log("--- Create Toggle Debug ---");
                    console.log("Selected ID:", val);
                    console.log("Selected Title:", title);
                    console.log("Selected Category:", category);

                    // Map categories to IDs
                    const activeIdMap = {
                        'university': 'university-fields',
                        'institute': 'institute-fields',
                        'school': 'school-fields',
                        'ecb': 'exam-conducting-body-fields',
                        'counselling': 'counselling-body-fields',
                        'regulatory': 'regulatory-body-fields'
                    };

                    // Determine active section
                    let activeSectionId = activeIdMap[category];
                    if (!activeSectionId) {
                        if (title.includes('university')) activeSectionId = 'university-fields';
                        else if (title.includes('college') || title.includes('institute')) activeSectionId = 'institute-fields';
                        else if (title.includes('school')) activeSectionId = 'school-fields';
                        else if (title.includes('exam') || title.includes('conducting')) activeSectionId = 'exam-conducting-body-fields';
                        else if (title.includes('counselling') || val == '6') activeSectionId = 'counselling-body-fields';
                        else if (title.includes('regulatory') || val == '7') activeSectionId = 'regulatory-body-fields';
                    }

                    const sections = [
                        'university-fields',
                        'institute-fields',
                        'school-fields',
                        'exam-conducting-body-fields',
                        'counselling-body-fields',
                        'regulatory-body-fields'
                    ];

                    sections.forEach(id => {
                        const el = document.getElementById(id);
                        if (el) {
                            const shouldShow = (id === activeSectionId);
                            el.style.display = shouldShow ? 'block' : 'none';
                            // Disable inputs if hidden to prevent submitting duplicate empty names
                            el.querySelectorAll('input, select, textarea').forEach(input => {
                                input.disabled = !shouldShow;
                            });
                            if (shouldShow) console.log(`👉 Showing section: ${id}`);
                        }
                    });
                    console.log("--------------------");
                }

                // ------------------------------------------------------------------
                // Feature: File Preview & Repeater Logic
                // ------------------------------------------------------------------
                const previewStyles = `
                                                                                                                                                                    <style>
                                                                                                                                                                        .file-preview img { max-height: 150px; border-radius: 8px; box-shadow: 0 2px 5px rgba(0,0,0,0.1); }
                                                                                                                                                                        .file-preview .file-icon { font-size: 2rem; color: #6c757d; }
                                                                                                                                                                        .file-preview-item { display: inline-block; position: relative; margin-right: 10px; margin-bottom: 10px; }
                                                                                                                                                                        .file-repeater-item { border: 1px dashed #dee2e6; padding: 15px; border-radius: 8px; background: #f8f9fa; }
                                                                                                                                                                    </style>
                                                                                                                                                                `;
                document.head.insertAdjacentHTML('beforeend', previewStyles);

                function handleFilePreview(input) {
                    const container = input.closest('.file-preview-container') || input.parentElement;
                    let previewDiv = container.querySelector('.file-preview');
                    if (!previewDiv) {
                        // Fallback for single inputs with sibling div
                        const name = input.name.replace('[]', '');
                        previewDiv = document.querySelector(`.file-preview[data-preview="${name}"]`);
                    }

                    if (!previewDiv) return;

                    previewDiv.innerHTML = ''; // Clear
                    if (input.files && input.files[0]) {
                        const file = input.files[0];
                        const reader = new FileReader();

                        if (file.type.startsWith('image/')) {
                            reader.onload = function (e) {
                                previewDiv.innerHTML = `<img src="${e.target.result}" class="img-thumbnail mt-2">`;
                            }
                            reader.readAsDataURL(file);
                        } else {
                            // Non-image icon
                            let icon = 'bi-file-earmark';
                            if (file.type === 'application/pdf') icon = 'bi-file-earmark-pdf text-danger';
                            else if (file.type.includes('word')) icon = 'bi-file-earmark-word text-primary';

                            previewDiv.innerHTML = `
                                                                                                                                                                                <div class="d-flex align-items-center mt-2 p-2 border rounded bg-light">
                                                                                                                                                                                    <i class="bi ${icon} fs-2 me-2"></i>
                                                                                                                                                                                    <span class="text-truncate">${file.name}</span>
                                                                                                                                                                                </div>
                                                                                                                                                                            `;
                        }
                    }
                }

                // Global listener for file previews
                document.addEventListener('change', function (e) {
                    if (e.target.classList.contains('file-preview-input')) {
                        handleFilePreview(e.target);
                    }
                });

                // File Repeater Logic
                document.querySelectorAll('.file-repeater-container').forEach(container => {
                    container.addEventListener('click', function (e) {
                        const addBtn = e.target.closest('.add-file-item');
                        const removeBtn = e.target.closest('.remove-file-item');

                        if (addBtn) {
                            const name = container.dataset.name;
                            const newItem = document.createElement('div');
                            newItem.className = 'file-repeater-item mb-2';
                            newItem.innerHTML = `
                                                                                                                                                                                <div class="input-group">
                                                                                                                                                                                    <input type="file" name="${name}" class="form-control file-preview-input">
                                                                                                                                                                                    <div class="form-text text-muted">Image size should not exceed 2MB.</div>
                                                                                                                                                                                    <button type="button" class="btn btn-outline-danger remove-file-item">x</button>
                                                                                                                                                                                </div>
                                                                                                                                                                                <div class="file-preview mt-2"></div>
                                                                                                                                                                            `;
                            container.insertBefore(newItem, addBtn);
                        }

                        if (removeBtn) {
                            removeBtn.closest('.file-repeater-item').remove();
                        }
                    });
                });

                // Core Values Repeater Logic
                document.querySelectorAll('.core-values-container').forEach(cv_container => {
                    cv_container.addEventListener('click', function (e) {
                        const addBtn = e.target.closest('.add-core-value');
                        const removeBtn = e.target.closest('.remove-core-value');

                        if (addBtn) {
                            const group = document.createElement('div');
                            group.className = 'input-group mb-2';
                            group.innerHTML = `
                                <input type="text" name="core_values[]" class="form-control" placeholder="Enter core value">
                                <button type="button" class="btn btn-outline-danger remove-core-value">x</button>
                            `;
                            cv_container.appendChild(group);
                            
                            // Trigger Auto-save if applicable
                            const emptyInput = group.querySelector('input');
                            if (emptyInput) emptyInput.dispatchEvent(new Event('change', { bubbles: true }));
                        }

                        if (removeBtn) {
                            removeBtn.closest('.input-group').remove();
                            // Trigger Auto-save
                            cv_container.dispatchEvent(new Event('change', { bubbles: true }));
                        }
                    });
                });

                typeSelect.addEventListener('change', toggleFields);
                toggleFields(); // Init

                // Sync basic fields for Counselling/Regulatory
                const cbNameSyncInput = document.getElementById('cb_name_sync');
                const rbNameSyncInput = document.getElementById('rb_name_sync');
                const cbShortSyncInput = document.getElementById('cb_short_sync');
                const rbShortSyncInput = document.getElementById('rb_short_sync');
                const cbEstSyncInput = document.getElementById('cb_est_sync');
                const rbEstSyncInput = document.getElementById('rb_est_sync');

                const commonNameInput = document.getElementsByName('name')[0];
                const commonShortInput = document.getElementsByName('short_name')[0];
                const commonEstInput = document.getElementsByName('established_year')[0];

                if (commonNameInput) {
                    commonNameInput.addEventListener('input', () => {
                        if (cbNameSyncInput) cbNameSyncInput.value = commonNameInput.value;
                        if (rbNameSyncInput) rbNameSyncInput.value = commonNameInput.value;
                    });
                }
                if (commonShortInput) {
                    commonShortInput.addEventListener('input', () => {
                        if (cbShortSyncInput) cbShortSyncInput.value = commonShortInput.value;
                        if (rbShortSyncInput) rbShortSyncInput.value = commonShortInput.value;
                    });
                }
                if (commonEstInput) {
                    commonEstInput.addEventListener('input', () => {
                        if (cbEstSyncInput) cbEstSyncInput.value = commonEstInput.value;
                        if (rbEstSyncInput) rbEstSyncInput.value = commonEstInput.value;
                    });
                }

                // Jurisdiction States Toggle
                const cbJurisScopeSelect = document.getElementById('cb_jurisdiction');
                const cbJurisStatesDiv = document.getElementById('cb_juris_states_div');
                if (cbJurisScopeSelect && cbJurisStatesDiv) {
                    cbJurisScopeSelect.addEventListener('change', () => {
                        cbJurisStatesDiv.style.display = (cbJurisScopeSelect.value === 'State') ? 'block' : 'none';
                    });
                }
                const rbJurisScopeSelect = document.getElementById('rb_jurisdiction');
                const rbJurisStatesDiv = document.getElementById('rb_juris_states_div');
                if (rbJurisScopeSelect && rbJurisStatesDiv) {
                    rbJurisScopeSelect.addEventListener('change', () => {
                        rbJurisStatesDiv.style.display = (rbJurisScopeSelect.value === 'State') ? 'block' : 'none';
                    });
                }
            });

            // Auto-save Logic
            document.addEventListener('DOMContentLoaded', function () {
                const orgIdInput = document.getElementById('organisation_id');
                const nameInput = document.getElementsByName('name')[0];
                const typeInput = document.getElementById('organisation_type_id');
                const statusDiv = document.getElementById('autosave-status');
                const autosaveRouteTemplate = "{{ route('admin.organisations.autosave', ':id') }}";

                function showStatus(msg, type = 'info') {
                    statusDiv.innerHTML = `<span class="badge bg-${type}">${msg}</span>`;
                    setTimeout(() => statusDiv.innerHTML = '', 3000);
                }

                function tryCreateDraft() {
                    if (!orgIdInput.value && nameInput.value && typeInput.value) {
                        showStatus('Creating Draft...', 'warning');
                        fetch("{{ route('admin.organisations.store-draft') }}", {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'X-CSRF-TOKEN': "{{ csrf_token() }}"
                            },
                            body: JSON.stringify({
                                name: nameInput.value,
                                organisation_type_id: typeInput.value
                            })
                        })
                            .then(response => response.json())
                            .then(data => {
                                if (data.status === 'success') {
                                    orgIdInput.value = data.organisation_id;
                                    showStatus('Draft Created', 'success');
                                }
                            })
                            .catch(err => console.error(err));
                    }
                }

                // Create Draft Triggers
                nameInput.addEventListener('blur', tryCreateDraft);
                typeInput.addEventListener('change', tryCreateDraft);

                // Auto-update Fields
                const inputs = document.querySelectorAll('input, select, textarea');
                inputs.forEach(input => {
                    input.addEventListener('change', function () {
                        const orgId = orgIdInput.value;
                        if (!orgId) return;

                        if (this.type === 'file') {
                            // File Upload
                            const formData = new FormData();
                            formData.append('field', this.name.replace('[]', ''));
                            if (this.files.length > 0) {
                                formData.append(this.name.replace('[]', ''), this.files[0]);
                            }

                            showStatus('Uploading...', 'info');
                            fetch(autosaveRouteTemplate.replace(':id', orgId), {
                                method: 'POST',
                                headers: {
                                    'X-CSRF-TOKEN': "{{ csrf_token() }}"
                                },
                                body: formData
                            })
                                .then(res => res.json())
                                .then(data => showStatus('File Saved', 'success'))
                                .catch(err => showStatus('Upload Failed', 'danger'));
                        } else {
                            // Regular Fields
                            let value = this.value;
                            let fieldName = this.name.replace('[]', '');

                            if (this.type === 'checkbox') {
                                value = this.checked ? 1 : 0;
                            } else if (this.name.includes('[]') && this.type === 'text') {
                                // Array Text: Collect ALL inputs of this name
                                const allInputs = document.querySelectorAll(`input[name="${this.name}"]`);
                                value = Array.from(allInputs).map(i => i.value).filter(v => v.trim() !== '');
                            }

                            showStatus('Saving...', 'info');
                            fetch(autosaveRouteTemplate.replace(':id', orgId), {
                                method: 'POST',
                                headers: {
                                    'Content-Type': 'application/json',
                                    'X-CSRF-TOKEN': "{{ csrf_token() }}"
                                },
                                body: JSON.stringify({
                                    field: fieldName,
                                    value: value
                                })
                            })
                                .then(res => res.json())
                                .then(data => showStatus('Saved', 'success'))
                                .catch(err => showStatus('Error', 'danger'));
                        }
                    });
                });
            });

        </script>


    @endpush
@endsection
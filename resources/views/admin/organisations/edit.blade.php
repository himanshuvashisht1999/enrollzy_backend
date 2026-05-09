@extends('admin.layouts.master')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h4 class="card-title">Edit Organisation</h4>
                    <div class="float-end">
                        <a href="{{ route('admin.organisations.campuses.index', $organisation->id) }}" class="btn btn-info btn-sm me-2">Manage Campuses</a>
                        <a href="{{ route('admin.organisations.index') }}" class="btn btn-secondary btn-sm">Back</a>
                    </div>
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
                    <form action="{{ route('admin.organisations.update', $organisation->id) }}" method="POST" enctype="multipart/form-data">
                        @csrf
                        @method('PUT')
                        <!-- Organisation ID for JS -->
                        <input type="hidden" id="organisation_id" value="{{ $organisation->id }}">
                        <div id="autosave-status" class="position-fixed bottom-0 end-0 p-3" style="z-index: 1050; pointer-events: none;"></div>
                        <div class="row g-3">
                            <!-- Basic Common Fields -->
                            <div class="col-md-6">
                                <label class="form-label fw-bold">Organisation Name <span class="text-danger">*</span></label>
                                <input type="text" name="name" class="form-control" value="{{ old('name', $organisation->name) }}" required>
                            </div>
                            
                            <div class="col-md-6">
                                <label class="form-label fw-bold">Organisation Type <span class="text-danger">*</span></label>
                                <select name="organisation_type_id" id="organisation_type_id" class="form-select" required>
                                    <option value="">Select Type</option>
                                    @foreach($organisationTypes as $type)
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
                                        <option value="{{ $type->id }}" data-category="{{ $category }}" data-title="{{ $type->title }}" {{ old('organisation_type_id', $organisation->organisation_type_id) == $type->id ? 'selected' : '' }}>{{ $type->title }} (ID: {{ $type->id }})</option>
                                    @endforeach
                                </select>
                            </div>

                            {{-- <div class="col-md-6">
                                <label class="form-label fw-bold">Brand Type</label>
                                <select name="brand_type" class="form-select">
                                    <option value="">Select Brand Type</option>
                                    @foreach($brandTypes as $brand)
                                        <option value="{{ $brand }}" {{ old('brand_type', $organisation->brand_type) == $brand ? 'selected' : '' }}>{{ $brand }}</option>
                                    @endforeach
                                </select>
                            </div> --}}
                         






                            <div class="col-md-6">
                                <label class="form-label fw-bold">Central Authority</label>
                                <input type="text" name="central_authority" class="form-control" value="{{ old('central_authority', $organisation->central_authority) }}">
                            </div>

                            <div class="col-md-6">
                                <label class="form-label fw-bold">Head Office Location</label>
                                <input type="text" name="head_office_location" class="form-control" value="{{ old('head_office_location', $organisation->head_office_location) }}">
                            </div>

                            <div class="col-md-12">
                                <label class="form-label fw-bold">Core Values</label>
                                <div class="core-values-container">
                                    @php $coreValues = is_array($organisation->core_values) ? $organisation->core_values : []; @endphp
                                    @if(count($coreValues) > 0)
                                        @foreach($coreValues as $value)
                                            <div class="input-group mb-2">
                                                <input type="text" name="core_values[]" class="form-control" value="{{ $value }}">
                                                <button type="button" class="btn btn-outline-danger remove-core-value">x</button>
                                            </div>
                                        @endforeach
                                        <button type="button" class="btn btn-sm btn-outline-success mt-1 add-core-value">+ Add More</button>
                                    @else
                                        <div class="input-group mb-2">
                                            <input type="text" name="core_values[]" class="form-control" placeholder="Enter core value">
                                            <button type="button" class="btn btn-outline-success add-core-value">+</button>
                                        </div>
                                    @endif
                                </div>
                            </div>

                             <!-- University Specific Fields Section -->
                             <div id="university-fields" class="col-12" style="display: none;">
                                <hr class="my-4">
                                <h5 class="mb-3 text-primary">University Details</h5>
                                
                                <ul class="nav nav-tabs mb-3" id="uniTab" role="tablist">
                                    <li class="nav-item" role="presentation">
                                        <button class="nav-link active" id="core-tab" data-bs-toggle="tab" data-bs-target="#core" type="button" role="tab" aria-controls="core" aria-selected="true">Core Identity</button>
                                    </li>
                                    <li class="nav-item" role="presentation">
                                        <button class="nav-link" id="legal-tab" data-bs-toggle="tab" data-bs-target="#legal" type="button" role="tab" aria-controls="legal" aria-selected="false">Legal & Regulatory</button>
                                    </li>
                                    <li class="nav-item" role="presentation">
                                        <button class="nav-link" id="governance-tab" data-bs-toggle="tab" data-bs-target="#governance" type="button" role="tab" aria-controls="governance" aria-selected="false">Governance</button>
                                    </li>
                                    <li class="nav-item" role="presentation">
                                        <button class="nav-link" id="academic-tab" data-bs-toggle="tab" data-bs-target="#academic" type="button" role="tab" aria-controls="academic" aria-selected="false">Academic Scope</button>
                                    </li>
                                </ul>
                                
                                <div class="tab-content" id="uniTabContent">
                                    <!-- Core Identity -->
                                    <div class="tab-pane fade show active" id="core" role="tabpanel" aria-labelledby="core-tab">
                                        <div class="row g-3">
                                            <div class="col-md-6">
                                                <label class="form-label">Brand Name</label>
                                                <input type="text" name="brand_name" class="form-control" value="{{ old('brand_name', $organisation->brand_name) }}">
                                            </div>
                                            <div class="col-md-6">
                                                <label class="form-label">Short Name</label>
                                                <input type="text" name="short_name" class="form-control" value="{{ old('short_name', $organisation->short_name) }}">
                                            </div>
                                            <div class="col-md-6">
                                                <label class="form-label">Established Year</label>
                                                <input type="number" name="established_year" class="form-control" value="{{ old('established_year', $organisation->established_year) }}">
                                            </div>
                                            <div class="col-md-6">
                                                <label class="form-label">University Type</label>
                                                <select name="university_type" class="form-select">
                                                    <option value="">Select Type</option>
                                                    @foreach(['Central', 'State', 'Deemed', 'Private', 'International'] as $opt)
                                                        <option value="{{ $opt }}" {{ old('university_type', $organisation->university_type) == $opt ? 'selected' : '' }}>{{ $opt }}</option>
                                                    @endforeach
                                                </select>
                                            </div>
                                            <div class="col-md-6">
                                                <label class="form-label">Ownership Type</label>
                                                <select name="ownership_type" class="form-select">
                                                    <option value="">Select Ownership</option>
                                                    @foreach(['Government', 'Private', 'Trust', 'PPP'] as $opt)
                                                        <option value="{{ $opt }}" {{ old('ownership_type', $organisation->ownership_type) == $opt ? 'selected' : '' }}>{{ $opt }}</option>
                                                    @endforeach
                                                </select>
                                            </div>
                                            <div class="col-md-12">
                                                 <label class="form-label">About University</label>
                                                 <textarea name="about_university" class="form-control editor" rows="3">{{ old('about_university', $organisation->about_university) }}</textarea>
                                             </div>
                                            <div class="col-md-12">
                                                 <label class="form-label">Vision & Mission</label>
                                                 <textarea name="vision_mission" class="form-control editor" rows="3">{{ old('vision_mission', $organisation->vision_mission) }}</textarea>
                                             </div>
                                            <div class="col-md-6">
                                                <label class="form-label">Logo</label>
                                                <input type="file" name="logo_url" class="form-control file-preview-input" accept="image/*">
                                                <div class="form-text text-muted">Image size should not exceed 2MB.</div>
                                                <div class="file-preview mt-2" data-preview="logo_url">
                                                    @if($organisation->logo_url)
                                                        <img src="{{ asset($organisation->logo_url) }}" class="img-thumbnail" style="height: 100px;">
                                                    @endif
                                                </div>
                                            </div>
                                            <div class="col-md-6">
                                                <label class="form-label">Cover Image</label>
                                                <input type="file" name="cover_image_url" class="form-control file-preview-input" accept="image/*">
                                                <div class="form-text text-muted">Image size should not exceed 2MB.</div>
                                                <div class="file-preview mt-2" data-preview="cover_image_url">
                                                    @if($organisation->cover_image_url)
                                                        <img src="{{ asset($organisation->cover_image_url) }}" class="img-thumbnail" style="height: 100px;">
                                                    @endif
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    
                                    <!-- Legal & Regulatory -->
                                    <div class="tab-pane fade" id="legal" role="tabpanel" aria-labelledby="legal-tab">
                                        <div class="row g-3">
                                            <div class="col-md-3">
                                                <div class="form-check form-switch mt-4">
                                                    <input class="form-check-input" type="checkbox" name="degree_awarding_authority" id="degree_awarding_authority" {{ old('degree_awarding_authority', $organisation->degree_awarding_authority) ? 'checked' : '' }}>
                                                    <label class="form-check-label" for="degree_awarding_authority">Degree Awarding Authority</label>
                                                </div>
                                            </div>
                                            <div class="col-md-3">
                                                <div class="form-check form-switch mt-4">
                                                    <input class="form-check-input" type="checkbox" name="ugc_recognized" id="ugc_recognized" {{ old('ugc_recognized', $organisation->ugc_recognized) ? 'checked' : '' }}>
                                                    <label class="form-check-label" for="ugc_recognized">UGC Recognized</label>
                                                </div>
                                            </div>
                                            <div class="col-md-6">
                                                <label class="form-label">UGC Approval Number</label>
                                                <input type="text" name="ugc_approval_number" class="form-control" value="{{ old('ugc_approval_number', $organisation->ugc_approval_number) }}">
                                            </div>
                                            <div class="col-md-3">
                                                <div class="form-check form-switch mt-4">
                                                    <input class="form-check-input" type="checkbox" name="aicte_approved" id="aicte_approved" {{ old('aicte_approved', $organisation->aicte_approved) ? 'checked' : '' }}>
                                                    <label class="form-check-label" for="aicte_approved">AICTE Approved</label>
                                                </div>
                                            </div>
                                            <div class="col-md-3">
                                                <div class="form-check form-switch mt-4">
                                                    <input class="form-check-input" type="checkbox" name="naac_accredited" id="naac_accredited" {{ old('naac_accredited', $organisation->naac_accredited) ? 'checked' : '' }}>
                                                    <label class="form-check-label" for="naac_accredited">NAAC Accredited</label>
                                                </div>
                                            </div>
                                            <div class="col-md-6">
                                                <label class="form-label">NAAC Grade</label>
                                                <input type="text" name="naac_grade" class="form-control" value="{{ old('naac_grade', $organisation->naac_grade) }}">
                                            </div>
                                            <div class="col-md-6">
                                                <label class="form-label">NIRF Rank (Overall)</label>
                                                <input type="number" name="nirf_rank_overall" class="form-control" value="{{ old('nirf_rank_overall', $organisation->nirf_rank_overall) }}">
                                            </div>
                                            <div class="col-md-6">
                                                <label class="form-label">NIRF Rank (Category)</label>
                                                <input type="number" name="nirf_rank_category" class="form-control" value="{{ old('nirf_rank_category', $organisation->nirf_rank_category) }}">
                                            </div>
                                            <div class="col-md-12">
                                                <label class="form-label">Recognition Documents (Multiple)</label>
                                                <div class="file-repeater-container" data-name="recognition_documents[]">
                                                    @if($organisation->recognition_documents)
                                                        @foreach($organisation->recognition_documents as $doc)
                                                            <div class="file-repeater-item mb-2 existing-file">
                                                                <div class="d-flex align-items-center p-2 border rounded bg-light">
                                                                    <i class="bi {{ str_ends_with($doc, '.pdf') ? 'bi-file-earmark-pdf text-danger' : 'bi-file-earmark-image' }} fs-4 me-2"></i>
                                                                    <span class="text-truncate flex-grow-1"><a href="{{ asset($doc) }}" target="_blank">View Existing Doc</a></span>
                                                                    <button type="button" class="btn btn-sm btn-outline-danger remove-existing-file" data-file="{{ $doc }}">x</button>
                                                                    <input type="hidden" name="existing_recognition_documents[]" value="{{ $doc }}">
                                                                </div>
                                                            </div>
                                                        @endforeach
                                                    @endif
                                                    <div class="file-repeater-item mb-2">
                                                        <div class="input-group">
                                                            <input type="file" name="recognition_documents[]" class="form-control file-preview-input">
                                                            <div class="form-text text-muted">Image size should not exceed 2MB.</div>
                                                            <button type="button" class="btn btn-outline-danger remove-file-item" style="display:none;">x</button>
                                                        </div>
                                                        <div class="form-text text-muted">Image size should not exceed 2MB.</div>
                                                        <div class="file-preview mt-2"></div>
                                                    </div>
                                                    <button type="button" class="btn btn-sm btn-outline-primary add-file-item">+ Add More Document</button>
                                                </div>
                                            </div>
                                            <div class="col-md-6">
                                                <label class="form-label">International Accreditations (Comma separated)</label>
                                                <input type="text" name="international_accreditations[]" class="form-control" value="{{ implode(',', is_array($organisation->international_accreditations) ? $organisation->international_accreditations : []) }}">
                                            </div>
                                            <div class="col-md-6">
                                                <label class="form-label">Statutory Approvals (Comma separated)</label>
                                                <input type="text" name="statutory_approvals[]" class="form-control" value="{{ implode(',', is_array($organisation->statutory_approvals) ? $organisation->statutory_approvals : []) }}">
                                            </div>
                                        </div>
                                    </div>
                                    
                                    <!-- Governance -->
                                    <div class="tab-pane fade" id="governance" role="tabpanel" aria-labelledby="governance-tab">
                                        <div class="row g-3">
                                            <div class="col-md-6">
                                                <label class="form-label">Governing Body Name</label>
                                                <input type="text" name="governing_body_name" class="form-control" value="{{ old('governing_body_name', $organisation->governing_body_name) }}">
                                            </div>
                                            <div class="col-md-6">
                                                <label class="form-label">Chancellor Name</label>
                                                <input type="text" name="chancellor_name" class="form-control" value="{{ old('chancellor_name', $organisation->chancellor_name) }}">
                                            </div>
                                            <div class="col-md-6">
                                                <label class="form-label">Vice Chancellor Name</label>
                                                <input type="text" name="vice_chancellor_name" class="form-control" value="{{ old('vice_chancellor_name', $organisation->vice_chancellor_name) }}">
                                            </div>
                                            <div class="col-md-6">
                                                <label class="form-label">University Category</label>
                                                <select name="university_category" class="form-select">
                                                    <option value="">Select Category</option>
                                                    @foreach(['Teaching', 'Research', 'Teaching + Research'] as $opt)
                                                        <option value="{{ $opt }}" {{ old('university_category', $organisation->university_category) == $opt ? 'selected' : '' }}>{{ $opt }}</option>
                                                    @endforeach
                                                </select>
                                            </div>
                                            <div class="col-md-4">
                                                <label class="form-label">No. of Campuses</label>
                                                <input type="number" name="number_of_campuses" class="form-control" value="{{ old('number_of_campuses', $organisation->number_of_campuses) }}">
                                            </div>
                                            <div class="col-md-4">
                                                <label class="form-label">No. of Constituent Colleges</label>
                                                <input type="number" name="number_of_constituent_colleges" class="form-control" value="{{ old('number_of_constituent_colleges', $organisation->number_of_constituent_colleges) }}">
                                            </div>
                                            <div class="col-md-4">
                                                <label class="form-label">No. of Affiliated Colleges</label>
                                                <input type="number" name="number_of_affiliated_colleges" class="form-control" value="{{ old('number_of_affiliated_colleges', $organisation->number_of_affiliated_colleges) }}">
                                            </div>
                                            <div class="col-md-4">
                                                <div class="form-check form-switch mt-4">
                                                    <input class="form-check-input" type="checkbox" name="autonomous_status" id="autonomous_status" {{ old('autonomous_status', $organisation->autonomous_status) ? 'checked' : '' }}>
                                                    <label class="form-check-label" for="autonomous_status">Autonomous Status</label>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    
                                    <!-- Academic Scope -->
                                    <div class="tab-pane fade" id="academic" role="tabpanel" aria-labelledby="academic-tab">
                                        <div class="row g-3">
                                            <div class="col-12">
                                                <label class="form-label">Levels Offered</label>
                                                <div class="d-flex gap-3 flex-wrap">
                                                    @php 
                                                        $rawLevels = $organisation->levels_offered;
                                                        if (is_array($rawLevels)) {
                                                            $existingLevels = $rawLevels;
                                                        } elseif (is_string($rawLevels)) {
                                                            $json = json_decode($rawLevels, true);
                                                            $existingLevels = (json_last_error() === JSON_ERROR_NONE && is_array($json)) ? $json : array_map('trim', explode(',', $rawLevels));
                                                        } else {
                                                            $existingLevels = [];
                                                        }
                                                    @endphp
                                                    <div class="form-check">
                                                        <input class="form-check-input" type="checkbox" name="levels_offered[]" value="Diploma" id="level_diploma" {{ in_array('Diploma', $existingLevels) ? 'checked' : '' }}>
                                                        <label class="form-check-label" for="level_diploma">Diploma</label>
                                                    </div>
                                                    <div class="form-check">
                                                        <input class="form-check-input" type="checkbox" name="levels_offered[]" value="UG" id="level_ug" {{ in_array('UG', $existingLevels) ? 'checked' : '' }}>
                                                        <label class="form-check-label" for="level_ug">UG</label>
                                                    </div>
                                                    <div class="form-check">
                                                        <input class="form-check-input" type="checkbox" name="levels_offered[]" value="PG" id="level_pg" {{ in_array('PG', $existingLevels) ? 'checked' : '' }}>
                                                        <label class="form-check-label" for="level_pg">PG</label>
                                                    </div>
                                                    <div class="form-check">
                                                        <input class="form-check-input" type="checkbox" name="levels_offered[]" value="Doctoral" id="level_doctoral" {{ in_array('Doctoral', $existingLevels) ? 'checked' : '' }}>
                                                        <label class="form-check-label" for="level_doctoral">Doctoral</label>
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
                                        <button class="nav-link active" id="inst-core-tab" data-bs-toggle="tab" data-bs-target="#inst-core" type="button" role="tab" aria-controls="inst-core" aria-selected="true">Core Identity</button>
                                    </li>
                                    <li class="nav-item" role="presentation">
                                        <button class="nav-link" id="inst-legal-tab" data-bs-toggle="tab" data-bs-target="#inst-legal" type="button" role="tab" aria-controls="inst-legal" aria-selected="false">Ownership & Legal</button>
                                    </li>
                                </ul>

                                <div class="tab-content" id="instTabContent">
                                    <!-- Core Identity -->
                                    <div class="tab-pane fade show active" id="inst-core" role="tabpanel" aria-labelledby="inst-core-tab">
                                         <div class="row g-3">
                                            <div class="col-md-6">
                                                <label class="form-label">Brand Name</label>
                                                <!-- Same name attribute because it goes to same column -->
                                                <input type="text" name="brand_name" class="form-control" value="{{ old('brand_name', $organisation->brand_name) }}" id="inst_brand_name">
                                            </div>
                                            <div class="col-md-6">
                                                <label class="form-label">Short Name</label>
                                                <input type="text" name="short_name" class="form-control" value="{{ old('short_name', $organisation->short_name) }}" id="inst_short_name">
                                            </div>
                                            <div class="col-md-6">
                                                <label class="form-label">Established Year</label>
                                                <input type="number" name="established_year" class="form-control" value="{{ old('established_year', $organisation->established_year) }}" id="inst_established_year">
                                            </div>
                                            
                                            <div class="col-md-12">
                                                 <label class="form-label">About Organization</label>
                                                 <textarea name="about_organisation" class="form-control editor" rows="3">{{ old('about_organisation', $organisation->about_organisation) }}</textarea>
                                             </div>
                                            <div class="col-md-12">
                                                 <label class="form-label">Vision & Mission</label>
                                                 <textarea name="vision_mission" class="form-control editor" rows="3" id="inst_vision_mission">{{ old('vision_mission', $organisation->vision_mission) }}</textarea>
                                             </div>

                                            <div class="col-md-6">
                                                <label class="form-label">Logo</label>
                                                <input type="file" name="logo_url" class="form-control file-preview-input" accept="image/*" id="inst_logo_url">
                                                <div class="form-text text-muted">Image size should not exceed 2MB.</div>
                                                 <div class="form-text text-muted">Image size should not exceed 2MB.</div>
                                                <div class="file-preview mt-2" data-preview="logo_url">
                                                    @if($organisation->logo_url)
                                                        <img src="{{ asset($organisation->logo_url) }}" class="img-thumbnail" style="height: 100px;">
                                                    @endif
                                                </div>
                                            </div>
                                            <div class="col-md-6">
                                                <label class="form-label">Cover Image</label>
                                                <input type="file" name="cover_image_url" class="form-control file-preview-input" accept="image/*" id="inst_cover_image_url">
                                                <div class="form-text text-muted">Image size should not exceed 2MB.</div>
                                                <div class="file-preview mt-2" data-preview="cover_image_url">
                                                    @if($organisation->cover_image_url)
                                                        <img src="{{ asset($organisation->cover_image_url) }}" class="img-thumbnail" style="height: 100px;">
                                                    @endif
                                                </div>
                                            </div>
                                         </div>
                                    </div>

                                    <!-- Ownership & Legal -->
                                    <div class="tab-pane fade" id="inst-legal" role="tabpanel" aria-labelledby="inst-legal-tab">
                                        <div class="row g-3">
                                            <div class="col-md-6">
                                                <label class="form-label">Ownership Type</label>
                                                <select name="ownership_type" class="form-select" id="inst_ownership_type">
                                                    <option value="">Select Ownership</option>
                                                    @foreach(['Private', 'Trust', 'LLP', 'Partnership'] as $opt)
                                                        <option value="{{ $opt }}" {{ old('ownership_type', $organisation->ownership_type) == $opt ? 'selected' : '' }}>{{ $opt }}</option>
                                                    @endforeach
                                                </select>
                                            </div>
                                            <div class="col-md-6">
                                                <label class="form-label">Registered Entity Name</label>
                                                <input type="text" name="registered_entity_name" class="form-control" value="{{ old('registered_entity_name', $organisation->registered_entity_name) }}">
                                            </div>
                                            <div class="col-md-6">
                                                <label class="form-label">Registration Number</label>
                                                <input type="text" name="registration_number" class="form-control" value="{{ old('registration_number', $organisation->registration_number) }}">
                                            </div>
                                            <div class="col-md-6">
                                                <label class="form-label">GST Number</label>
                                                <input type="text" name="gst_number" class="form-control" value="{{ old('gst_number', $organisation->gst_number) }}">
                                            </div>
                                            <div class="col-md-6">
                                                <label class="form-label">PAN Number</label>
                                                <input type="text" name="pan_number" class="form-control" value="{{ old('pan_number', $organisation->pan_number) }}">
                                            </div>
                                            <div class="col-md-6">
                                                <div class="form-check form-switch mt-4">
                                                    <input class="form-check-input" type="checkbox" name="gst_registered" id="gst_registered" {{ old('gst_registered', $organisation->gst_registered) ? 'checked' : '' }}>
                                                    <label class="form-check-label" for="gst_registered">GST Registered</label>
                                                </div>
                                            </div>
                                            <div class="col-md-12">
                                                <label class="form-label">Legal Documents (Multiple)</label>
                                                <div class="file-repeater-container" data-name="legal_documents_urls[]">
                                                    @if($organisation->legal_documents_urls)
                                                        @foreach($organisation->legal_documents_urls as $doc)
                                                            <div class="file-repeater-item mb-2 existing-file">
                                                                <div class="d-flex align-items-center p-2 border rounded bg-light">
                                                                    <i class="bi {{ str_ends_with($doc, '.pdf') ? 'bi-file-earmark-pdf text-danger' : 'bi-file-earmark-text' }} fs-4 me-2"></i>
                                                                    <span class="text-truncate flex-grow-1"><a href="{{ asset($doc) }}" target="_blank">View Existing Doc</a></span>
                                                                    <button type="button" class="btn btn-sm btn-outline-danger remove-existing-file" data-file="{{ $doc }}">x</button>
                                                                    <input type="hidden" name="existing_legal_documents_urls[]" value="{{ $doc }}">
                                                                </div>
                                                            </div>
                                                        @endforeach
                                                    @endif
                                                    <div class="file-repeater-item mb-2">
                                                        <div class="input-group">
                                                            <input type="file" name="legal_documents_urls[]" class="form-control file-preview-input">
                                                            <div class="form-text text-muted">Image size should not exceed 2MB.</div>
                                                            <button type="button" class="btn btn-outline-danger remove-file-item" style="display:none;">x</button>
                                                        </div>
                                                        <div class="file-preview mt-2"></div>
                                                    </div>
                                                    <button type="button" class="btn btn-sm btn-outline-primary add-file-item">+ Add More Document</button>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                            </div>

                            </div>
                            <div id="school-fields" class="col-12" style="display: none;">
                                <hr class="my-4">
                                <h5 class="mb-3 text-success">School Details (Type 4)</h5>
                                
                                <ul class="nav nav-tabs mb-3" id="schEditTab" role="tablist">
                                    <li class="nav-item" role="presentation"><button class="nav-link active" id="sch-core-tab" data-bs-toggle="tab" data-bs-target="#sch-core" type="button" role="tab">Core Identity</button></li>
                                    <li class="nav-item" role="presentation"><button class="nav-link" id="sch-ownership-tab" data-bs-toggle="tab" data-bs-target="#sch-ownership" type="button" role="tab">Ownership</button></li>
                                    <li class="nav-item" role="presentation"><button class="nav-link" id="sch-boards-tab" data-bs-toggle="tab" data-bs-target="#sch-boards" type="button" role="tab">Boards & Academic</button></li>
                                    <li class="nav-item" role="presentation"><button class="nav-link" id="sch-policies-tab" data-bs-toggle="tab" data-bs-target="#sch-policies" type="button" role="tab">Policies</button></li>
                                    <li class="nav-item" role="presentation"><button class="nav-link" id="sch-footprint-tab" data-bs-toggle="tab" data-bs-target="#sch-footprint" type="button" role="tab">Footprint & Digital</button></li>
                                    <li class="nav-item" role="presentation"><button class="nav-link" id="sch-seo-tab" data-bs-toggle="tab" data-bs-target="#sch-seo" type="button" role="tab">Trust & SEO</button></li>
                                </ul>

                                <div class="tab-content" id="schEditTabContent">
                                    <!-- A. Core Identity -->
                                    <div class="tab-pane fade show active" id="sch-core" role="tabpanel">
                                        <div class="row g-3">
                                            <div class="col-md-6"><label class="form-label">Brand Name</label><input type="text" name="brand_name" class="form-control" id="sch_brand_name" value="{{ old('brand_name', $organisation->brand_name) }}"></div>
                                            <div class="col-md-6"><label class="form-label">Short Name</label><input type="text" name="short_name" class="form-control" id="sch_short_name" value="{{ old('short_name', $organisation->short_name) }}"></div>
                                            <div class="col-md-6"><label class="form-label">Established Year</label><input type="number" name="established_year" class="form-control" id="sch_established_year" value="{{ old('established_year', $organisation->established_year) }}"></div>
                                            <div class="col-md-12"><label class="form-label">About Organization</label><textarea name="about_organisation" class="form-control editor" rows="3" id="sch_about">{{ old('about_organisation', $organisation->about_organisation) }}</textarea></div>
                                            <div class="col-md-12"><label class="form-label">Vision & Mission</label><textarea name="vision_mission" class="form-control editor" rows="3" id="sch_vision">{{ old('vision_mission', $organisation->vision_mission) }}</textarea></div>
                                            
                                            <div class="col-md-6">
                                                <label class="form-label">Logo</label>
                                                <input type="file" name="logo_url" class="form-control" accept="image/*" id="sch_logo">
                                                <div class="form-text text-muted">Image size should not exceed 2MB.</div>
                                                @if($organisation->logo_url) <div class="mt-2"><img src="{{ asset($organisation->logo_url) }}" height="50"></div> @endif
                                            </div>
                                            <div class="col-md-6">
                                                <label class="form-label">Cover Image</label>
                                                <input type="file" name="cover_image_url" class="form-control" accept="image/*" id="sch_cover">
                                                <div class="form-text text-muted">Image size should not exceed 2MB.</div>
                                                @if($organisation->cover_image_url) <div class="mt-2"><img src="{{ asset($organisation->cover_image_url) }}" height="50"></div> @endif
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
                                                    @foreach(['Government', 'Private', 'Trust / Society', 'Minority'] as $opt)
                                                        <option value="{{ $opt }}" {{ old('ownership_type', $organisation->ownership_type) == $opt ? 'selected' : '' }}>{{ $opt }}</option>
                                                    @endforeach
                                                </select>
                                            </div>
                                            <div class="col-md-6"><label class="form-label">Registered Entity Name</label><input type="text" name="registered_entity_name" class="form-control" id="sch_reg_name" value="{{ old('registered_entity_name', $organisation->registered_entity_name) }}"></div>
                                            <div class="col-md-6"><label class="form-label">Registration Number</label><input type="text" name="registration_number" class="form-control" id="sch_reg_no" value="{{ old('registration_number', $organisation->registration_number) }}"></div>
                                            <div class="col-md-6"><label class="form-label">Managing Trust/Society Name</label><input type="text" name="managing_trust_or_society_name" class="form-control" value="{{ old('managing_trust_or_society_name', $organisation->managing_trust_or_society_name) }}"></div>
                                            
                                            <div class="col-md-3">
                                                <div class="form-check form-switch mt-4">
                                                    <input class="form-check-input" type="checkbox" name="minority_status" id="minority_status" {{ old('minority_status', $organisation->minority_status) ? 'checked' : '' }}>
                                                    <label class="form-check-label" for="minority_status">Minority Status</label>
                                                </div>
                                            </div>
                                            <div class="col-md-3"><label class="form-label">Minority Type</label><input type="text" name="minority_type" class="form-control" value="{{ old('minority_type', $organisation->minority_type) }}"></div>

                                            <div class="col-md-12">
                                                <label class="form-label">Legal Documents</label>
                                                <div class="file-repeater-container" data-name="legal_documents_urls[]">
                                                    @if($organisation->legal_documents_urls)
                                                        @foreach($organisation->legal_documents_urls as $doc)
                                                            <div class="file-repeater-item mb-2 existing-file">
                                                                <div class="d-flex align-items-center p-2 border rounded bg-light">
                                                                    <i class="bi {{ str_ends_with($doc, '.pdf') ? 'bi-file-earmark-pdf text-danger' : 'bi-file-earmark-text' }} fs-4 me-2"></i>
                                                                    <span class="text-truncate flex-grow-1"><a href="{{ asset($doc) }}" target="_blank">View Existing Doc</a></span>
                                                                    <button type="button" class="btn btn-sm btn-outline-danger remove-existing-file" data-file="{{ $doc }}">x</button>
                                                                    <input type="hidden" name="existing_legal_documents_urls[]" value="{{ $doc }}">
                                                                </div>
                                                            </div>
                                                        @endforeach
                                                    @endif
                                                    <div class="file-repeater-item mb-2">
                                                        <div class="input-group">
                                                            <input type="file" name="legal_documents_urls[]" class="form-control file-preview-input" id="sch_legal_docs">
                                                            <div class="form-text text-muted">Image size should not exceed 2MB.</div>
                                                            <button type="button" class="btn btn-outline-danger remove-file-item" style="display:none;">x</button>
                                                        </div>
                                                        <div class="file-preview mt-2"></div>
                                                    </div>
                                                    <button type="button" class="btn btn-sm btn-outline-primary add-file-item">+ Add More Document</button>
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- C & D. Boards & Academic -->
                                    <div class="tab-pane fade" id="sch-boards" role="tabpanel">
                                        <div class="row g-3">
                                            <div class="col-12"><h6 class="text-muted">Board Scope</h6></div>
                                            <div class="col-md-12">
                                                <label class="form-label">Education Boards Supported</label>
                                                <div class="d-flex gap-3 flex-wrap">
                                                    @foreach(['CBSE', 'ICSE', 'State Board', 'IB', 'IGCSE'] as $board)
                                                    @php
                                                        $rawBoards = old('education_boards_supported', $organisation->education_boards_supported);
                                                        if (is_array($rawBoards)) {
                                                            $existingBoards = $rawBoards;
                                                        } elseif (is_string($rawBoards)) {
                                                            $json = json_decode($rawBoards, true);
                                                            $existingBoards = (json_last_error() === JSON_ERROR_NONE && is_array($json)) ? $json : array_map('trim', explode(',', $rawBoards));
                                                        } else {
                                                            $existingBoards = [];
                                                        }
                                                    @endphp
                                                    <div class="form-check">
                                                        <input class="form-check-input" type="checkbox" name="education_boards_supported[]" value="{{ $board }}" id="brd_{{ $board }}" {{ in_array($board, $existingBoards) ? 'checked' : '' }}>
                                                        <label class="form-check-label" for="brd_{{ $board }}">{{ $board }}</label>
                                                    </div>
                                                    @endforeach
                                                </div>
                                            </div>
                                            <div class="col-md-12">
                                                <label class="form-label">Medium of Instruction</label>
                                                <div class="input-group mb-2"><input type="text" name="medium_of_instruction_supported[]" class="form-control" placeholder="e.g. English, Hindi" value="English"><button type="button" class="btn btn-outline-secondary">Clone logic implied</button></div>
                                            </div>
                                            <div class="col-md-12">
                                                <div class="form-check form-switch">
                                                    <input class="form-check-input" type="checkbox" name="international_curriculum_supported" id="intl_curr" {{ old('international_curriculum_supported', $organisation->international_curriculum_supported) ? 'checked' : '' }}>
                                                    <label class="form-check-label" for="intl_curr">International Curriculum Supported</label>
                                                </div>
                                            </div>

                                            <div class="col-12 mt-3"><h6 class="text-muted">Academic Philosophy</h6></div>
                                            <div class="col-md-12">
                                                <label class="form-label">Education Levels</label>
                                                <div class="d-flex gap-2 flex-wrap">
                                                    @php
                                                        $rawLevelsEdu = old('education_levels_supported', $organisation->education_levels_supported);
                                                        if (is_array($rawLevelsEdu)) {
                                                            $existingLevelsEdu = $rawLevelsEdu;
                                                        } elseif (is_string($rawLevelsEdu)) {
                                                            $json = json_decode($rawLevelsEdu, true);
                                                            $existingLevelsEdu = (json_last_error() === JSON_ERROR_NONE && is_array($json)) ? $json : array_map('trim', explode(',', $rawLevelsEdu));
                                                        } else {
                                                            $existingLevelsEdu = [];
                                                        }
                                                    @endphp
                                                    @foreach(['Pre-Primary', 'Primary', 'Middle', 'Secondary', 'Senior Secondary'] as $lvl)
                                                    <div class="form-check">
                                                        <input class="form-check-input" type="checkbox" name="education_levels_supported[]" value="{{ $lvl }}" id="lvl_{{ Str::slug($lvl) }}" {{ in_array($lvl, $existingLevelsEdu) ? 'checked' : '' }}>
                                                        <label class="form-check-label" for="lvl_{{ Str::slug($lvl) }}">{{ $lvl }}</label>
                                                    </div>
                                                    @endforeach
                                                </div>
                                            </div>
                                            <div class="col-md-12">
                                                <label class="form-label">Streams (for Senior Secondary)</label>
                                                <div class="d-flex gap-2 flex-wrap">
                                                    @php
                                                        $rawStreams = old('streams_supported', $organisation->streams_supported);
                                                        if (is_array($rawStreams)) {
                                                            $existingStreams = $rawStreams;
                                                        } elseif (is_string($rawStreams)) {
                                                            $json = json_decode($rawStreams, true);
                                                            $existingStreams = (json_last_error() === JSON_ERROR_NONE && is_array($json)) ? $json : array_map('trim', explode(',', $rawStreams));
                                                        } else {
                                                            $existingStreams = [];
                                                        }
                                                    @endphp
                                                    @foreach(['Science', 'Commerce', 'Humanities', 'Vocational'] as $strm)
                                                    <div class="form-check">
                                                        <input class="form-check-input" type="checkbox" name="streams_supported[]" value="{{ $strm }}" id="strm_{{ $strm }}" {{ in_array($strm, $existingStreams) ? 'checked' : '' }}>
                                                        <label class="form-check-label" for="strm_{{ $strm }}">{{ $strm }}</label>
                                                    </div>
                                                    @endforeach
                                                </div>
                                            </div>
                                            <div class="col-md-6"><label class="form-label">Pedagogy Model</label><input type="text" name="pedagogy_model" class="form-control" value="{{ old('pedagogy_model', $organisation->pedagogy_model) }}"></div>
                                            <div class="col-md-6"><label class="form-label">Focus Areas (Comma separated)</label><input type="text" name="focus_areas[]" class="form-control" value="{{ implode(',', is_array($organisation->focus_areas) ? $organisation->focus_areas : []) }}"></div>
                                        </div>
                                    </div>

                                    <!-- E & F. Policies -->
                                    <div class="tab-pane fade" id="sch-policies" role="tabpanel">
                                        <div class="row g-3">
                                            <div class="col-12"><h6 class="text-muted">Central Policies & Systems</h6></div>
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
                                                    <input class="form-check-input" type="checkbox" name="{{ $key }}" id="{{ $key }}" {{ old($key, $organisation->$key) ? 'checked' : '' }}>
                                                    <label class="form-check-label" for="{{ $key }}">{{ $label }}</label>
                                                </div>
                                            </div>
                                            @endforeach

                                            <div class="col-12 mt-3"><h6 class="text-muted">Safety Policies</h6></div>
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
                                                    <input class="form-check-input" type="checkbox" name="{{ $key }}" id="{{ $key }}" {{ old($key, $organisation->$key) ? 'checked' : '' }}>
                                                    <label class="form-check-label" for="{{ $key }}">{{ $label }}</label>
                                                </div>
                                            </div>
                                            @endforeach
                                        </div>
                                    </div>

                                    <!-- G & H. Footprint & Digital -->
                                    <div class="tab-pane fade" id="sch-footprint" role="tabpanel">
                                        <div class="row g-3">
                                            <div class="col-12"><h6 class="text-muted">Brand Footprint</h6></div>
                                            <div class="col-md-4"><label class="form-label">Total Schools Count</label><input type="number" name="total_schools_count" class="form-control" value="{{ old('total_schools_count', $organisation->total_schools_count) }}"></div>
                                            <div class="col-md-4">
                                                 <div class="form-check form-switch mt-4">
                                                    <input class="form-check-input" type="checkbox" name="national_presence" id="national_presence" {{ old('national_presence', $organisation->national_presence) ? 'checked' : '' }}>
                                                    <label class="form-check-label" for="national_presence">National Presence</label>
                                                 </div>
                                            </div>
                                            <div class="col-md-4">
                                                 <div class="form-check form-switch mt-4">
                                                    <input class="form-check-input" type="checkbox" name="international_presence" id="international_presence" {{ old('international_presence', $organisation->international_presence) ? 'checked' : '' }}>
                                                    <label class="form-check-label" for="international_presence">International Presence</label>
                                                 </div>
                                            </div>
                                            <!-- Simplified Input for Arrays for Demo -->
                                            <div class="col-md-12"><label class="form-label">Cities Present In (Comma separated)</label><input type="text" name="cities_present_in[]" class="form-control" value="{{ implode(',', is_array($organisation->cities_present_in) ? $organisation->cities_present_in : []) }}"></div>
                                            <div class="col-md-12"><label class="form-label">States Present In (Comma separated)</label><input type="text" name="states_present_in[]" class="form-control" value="{{ implode(',', is_array($organisation->states_present_in) ? $organisation->states_present_in : []) }}"></div>
                                            <div class="col-md-12"><label class="form-label">Flagship Schools (Comma separated)</label><input type="text" name="flagship_schools[]" class="form-control" value="{{ implode(',', is_array($organisation->flagship_schools) ? $organisation->flagship_schools : []) }}"></div>

                                            <div class="col-12 mt-3"><h6 class="text-muted">Digital Presence</h6></div>
                                            <div class="col-md-6"><label class="form-label">Official Website</label><input type="url" name="official_website" class="form-control" value="{{ old('official_website', $organisation->official_website) }}"></div>
                                            <div class="col-md-6"><label class="form-label">Admission Portal URL</label><input type="url" name="admission_portal_url" class="form-control" value="{{ old('admission_portal_url', $organisation->admission_portal_url) }}"></div>
                                            <div class="col-md-6"><label class="form-label">Parent Portal URL</label><input type="url" name="parent_portal_url" class="form-control" value="{{ old('parent_portal_url', $organisation->parent_portal_url) }}"></div>
                                            <div class="col-md-6"><label class="form-label">Student Portal URL</label><input type="url" name="student_portal_url" class="form-control" value="{{ old('student_portal_url', $organisation->student_portal_url) }}"></div>
                                            <div class="col-md-12">
                                                 <div class="form-check form-switch">
                                                    <input class="form-check-input" type="checkbox" name="mobile_app_available" id="mobile_app_available" {{ old('mobile_app_available', $organisation->mobile_app_available) ? 'checked' : '' }}>
                                                    <label class="form-check-label" for="mobile_app_available">Mobile App Available</label>
                                                 </div>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- I. Trust & SEO -->
                                    <div class="tab-pane fade" id="sch-seo" role="tabpanel">
                                        <div class="row g-3">
                                            <div class="col-md-6"><label class="form-label">Average Rating</label><input type="number" step="0.1" name="average_rating" class="form-control" max="5" value="{{ old('average_rating', $organisation->average_rating) }}"></div>
                                            <div class="col-md-6"><label class="form-label">Total Reviews</label><input type="number" name="total_reviews" class="form-control" value="{{ old('total_reviews', $organisation->total_reviews) }}"></div>
                                            <div class="col-md-12"><label class="form-label">Awards & Recognition (Comma separated)</label><input type="text" name="awards_and_recognition[]" class="form-control" value="{{ implode(',', is_array($organisation->awards_and_recognition) ? $organisation->awards_and_recognition : []) }}"></div>
                                            <div class="col-md-6"><label class="form-label">Meta Title</label><input type="text" name="meta_title" class="form-control" value="{{ old('meta_title', $organisation->meta_title) }}"></div>
                                            <div class="col-md-6"><label class="form-label">Canonical URL</label><input type="text" name="canonical_url" class="form-control" value="{{ old('canonical_url', $organisation->canonical_url) }}"></div>
                                            <div class="col-md-12"><label class="form-label">Meta Description</label><textarea name="meta_description" class="form-control" rows="2">{{ old('meta_description', $organisation->meta_description) }}</textarea></div>
                                            <div class="col-md-6"><label class="form-label">Schema Type</label><input type="text" name="schema_type" class="form-control" value="{{ old('schema_type', $organisation->schema_type ?? 'School') }}"></div>
                                            <div class="col-md-6">
                                                 <div class="form-check form-switch mt-4">
                                                    <input class="form-check-input" type="checkbox" name="claimed_by_organization" id="claimed_by_organization" {{ old('claimed_by_organization', $organisation->claimed_by_organization) ? 'checked' : '' }}>
                                                    <label class="form-check-label" for="claimed_by_organization">Claimed by Organization</label>
                                                 </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                </div>
                                <!-- Exam Conducting Body Specific Fields Section -->
                                <div id="exam-conducting-body-fields" class="col-12" style="display: none;">
                                    <hr class="my-4">
                                    <h5 class="mb-3 text-info">Exam Conducting Body Details</h5>

                                    <ul class="nav nav-tabs mb-3" id="ecbTab" role="tablist">
                                        <li class="nav-item" role="presentation"><button class="nav-link active" id="ecb-identity-tab" data-bs-toggle="tab" data-bs-target="#ecb-identity" type="button" role="tab">Exam Identity</button></li>
                                        <li class="nav-item" role="presentation"><button class="nav-link" id="ecb-legal-tab" data-bs-toggle="tab" data-bs-target="#ecb-legal" type="button" role="tab">Legal Status</button></li>
                                        <li class="nav-item" role="presentation"><button class="nav-link" id="ecb-responsibilities-tab" data-bs-toggle="tab" data-bs-target="#ecb-responsibilities" type="button" role="tab">Responsibilities</button></li>
                                        <li class="nav-item" role="presentation"><button class="nav-link" id="ecb-exams-tab" data-bs-toggle="tab" data-bs-target="#ecb-exams" type="button" role="tab">Exams & Volume</button></li>
                                        <li class="nav-item" role="presentation"><button class="nav-link" id="ecb-capabilities-tab" data-bs-toggle="tab" data-bs-target="#ecb-capabilities" type="button" role="tab">Capabilities</button></li>
                                        <li class="nav-item" role="presentation"><button class="nav-link" id="ecb-partners-tab" data-bs-toggle="tab" data-bs-target="#ecb-partners" type="button" role="tab">Partners & Security</button></li>
                                        <li class="nav-item" role="presentation"><button class="nav-link" id="ecb-policies-tab" data-bs-toggle="tab" data-bs-target="#ecb-policies" type="button" role="tab">Policies & Data</button></li>
                                        <li class="nav-item" role="presentation"><button class="nav-link" id="ecb-support-tab" data-bs-toggle="tab" data-bs-target="#ecb-support" type="button" role="tab">Support & Transparency</button></li>
                                        <li class="nav-item" role="presentation"><button class="nav-link" id="ecb-reputation-tab" data-bs-toggle="tab" data-bs-target="#ecb-reputation" type="button" role="tab">Reputation & Trust</button></li>
                                        <li class="nav-item" role="presentation"><button class="nav-link" id="ecb-admin-tab" data-bs-toggle="tab" data-bs-target="#ecb-admin" type="button" role="tab">Administrative</button></li>
                                    </ul>

                                    <div class="tab-content" id="ecbTabContent">
                                        <!-- 1. Exam Identity -->
                                        <div class="tab-pane fade show active" id="ecb-identity" role="tabpanel">
                                            <div class="row g-3">
                                                <div class="col-md-6"><label class="form-label">Abbreviation</label><input type="text" name="abbreviation" class="form-control" value="{{ old('abbreviation', $organisation->abbreviation) }}"></div>
                                                <div class="col-md-6"><label class="form-label">Logo</label><input type="file" name="logo_url" class="form-control file-preview-input" accept="image/*" id="ecb_logo">
                                                <div class="form-text text-muted">Image size should not exceed 2MB.</div>
                                                    <div class="file-preview mt-2" data-preview="logo_url">
                                                        @if($organisation->logo_url) <img src="{{ asset($organisation->logo_url) }}" class="img-thumbnail" style="height: 100px;"> @endif
                                                    </div>
                                                </div>
                                                <div class="col-md-12"><label class="form-label">Mandate Description</label><textarea name="mandate_description" class="form-control" rows="3">{{ old('mandate_description', $organisation->mandate_description) }}</textarea></div>
                                                <div class="col-md-6"><label class="form-label">Public Trust Score (1-100)</label><input type="number" name="public_trust_score" class="form-control" value="{{ old('public_trust_score', $organisation->public_trust_score) }}" min="0" max="100"></div>
                                                <div class="col-md-6"><label class="form-label">Focus Keywords (Comma separated)</label><input type="text" name="focus_keywords" class="form-control" value="{{ implode(',', is_array($organisation->focus_keywords) ? $organisation->focus_keywords : []) }}"></div>
                                            </div>
                                        </div>

                                        <!-- 2. Legal Status -->
                                        <div class="tab-pane fade" id="ecb-legal" role="tabpanel">
                                            <div class="row g-3">
                                                <div class="col-md-6">
                                                    <label class="form-label">Authority Type</label>
                                                    <select name="authority_type" class="form-select">
                                                        <option value="">Select Authority Type</option>
                                                        @foreach(['Constitutional Body', 'Statutory Body', 'Government Agency', 'Autonomous Body'] as $opt)
                                                            <option value="{{ $opt }}" {{ old('authority_type', $organisation->authority_type) == $opt ? 'selected' : '' }}>{{ $opt }}</option>
                                                        @endforeach
                                                    </select>
                                                </div>
                                                <div class="col-md-6">
                                                    <label class="form-label">Parent Ministry</label>
                                                    <select name="parent_ministry" class="form-select">
                                                        <option value="">Select Parent Ministry</option>
                                                        @foreach(['Ministry of Education', 'DoPT', 'Ministry of Defence'] as $opt)
                                                            <option value="{{ $opt }}" {{ old('parent_ministry', $organisation->parent_ministry) == $opt ? 'selected' : '' }}>{{ $opt }}</option>
                                                        @endforeach
                                                    </select>
                                                </div>
                                                <div class="col-md-6">
                                                    <label class="form-label">Established By</label>
                                                    <select name="established_by" class="form-select">
                                                        <option value="">Select Established By</option>
                                                        @foreach(['Act of Parliament', 'Government Resolution'] as $opt)
                                                            <option value="{{ $opt }}" {{ old('established_by', $organisation->established_by) == $opt ? 'selected' : '' }}>{{ $opt }}</option>
                                                        @endforeach
                                                    </select>
                                                </div>
                                                <div class="col-md-6"><label class="form-label">Legal Act Reference</label><input type="text" name="legal_act_reference" class="form-control" value="{{ old('legal_act_reference', $organisation->legal_act_reference) }}"></div>
                                                <div class="col-md-6"><label class="form-label">Headquarters Location</label><input type="text" name="headquarters_location" class="form-control" value="{{ old('headquarters_location', $organisation->headquarters_location) }}"></div>
                                                <div class="col-md-6">
                                                    <label class="form-label">Jurisdiction Scope</label>
                                                    <select name="jurisdiction_scope" class="form-select">
                                                        <option value="">Select Jurisdiction Scope</option>
                                                        @foreach(['National', 'State', 'Multi-State'] as $opt)
                                                            <option value="{{ $opt }}" {{ old('jurisdiction_scope', $organisation->jurisdiction_scope) == $opt ? 'selected' : '' }}>{{ $opt }}</option>
                                                        @endforeach
                                                    </select>
                                                </div>
                                                <div class="col-md-6">
                                                    <div class="form-check form-switch mt-4"><input class="form-check-input" type="checkbox" name="claimed_by_authority" id="claimed_auth" {{ old('claimed_by_authority', $organisation->claimed_by_authority) ? 'checked' : '' }}><label class="form-check-label" for="claimed_auth">Claimed by Authority</label></div>
                                                </div>
                                            </div>
                                        </div>

                                        <!-- 3. Responsibilities -->
                                        <div class="tab-pane fade" id="ecb-responsibilities" role="tabpanel">
                                            <div class="row g-3">
                                                <div class="col-md-12">
                                                    <label class="form-label d-block">Functions</label>
                                                    @php $currentFunctions = is_array($organisation->functions) ? $organisation->functions : [] @endphp
                                                    @foreach(['Exam Conduct', 'Question Paper Design', 'Result Declaration', 'Scorecard Issuance', 'Rank List Preparation'] as $func)
                                                        <div class="form-check form-check-inline">
                                                            <input class="form-check-input" type="checkbox" name="functions[]" value="{{ $func }}" id="edit_func_{{ Str::slug($func) }}" {{ in_array($func, old('functions', $currentFunctions)) ? 'checked' : '' }}>
                                                            <label class="form-check-label" for="edit_func_{{ Str::slug($func) }}">{{ $func }}</label>
                                                        </div>
                                                    @endforeach
                                                </div>
                                                <div class="col-md-12">
                                                    <label class="form-label d-block">Exam Types Conducted</label>
                                                    @php $currentETypes = is_array($organisation->exam_types_conducted) ? $organisation->exam_types_conducted : [] @endphp
                                                    @foreach(['Entrance', 'Recruitment', 'Eligibility', 'Board Examination'] as $etype)
                                                        <div class="form-check form-check-inline">
                                                            <input class="form-check-input" type="checkbox" name="exam_types_conducted[]" value="{{ $etype }}" id="edit_etype_{{ Str::slug($etype) }}" {{ in_array($etype, old('exam_types_conducted', $currentETypes)) ? 'checked' : '' }}>
                                                            <label class="form-check-label" for="edit_etype_{{ Str::slug($etype) }}">{{ $etype }}</label>
                                                        </div>
                                                    @endforeach
                                                </div>
                                                <div class="col-md-12">
                                                    <label class="form-label d-block">Evaluation Methods</label>
                                                    @php $currentMethods = is_array($organisation->evaluation_methods) ? $organisation->evaluation_methods : [] @endphp
                                                    @foreach(['CBT', 'OMR', 'Descriptive', 'Hybrid'] as $emethod)
                                                        <div class="form-check form-check-inline">
                                                            <input class="form-check-input" type="checkbox" name="evaluation_methods[]" value="{{ $emethod }}" id="edit_emethod_{{ Str::slug($emethod) }}" {{ in_array($emethod, old('evaluation_methods', $currentMethods)) ? 'checked' : '' }}>
                                                            <label class="form-check-label" for="edit_emethod_{{ Str::slug($emethod) }}">{{ $emethod }}</label>
                                                        </div>
                                                    @endforeach
                                                </div>
                                            </div>
                                        </div>

                                        <!-- 4. Exams & Volume -->
                                        <div class="tab-pane fade" id="ecb-exams" role="tabpanel">
                                            <div class="row g-3">
                                                <div class="col-md-12"><label class="form-label">Exams Owned / Conducted (IDs)</label><input type="text" name="exams_conducted_ids" class="form-control" value="{{ implode(',', is_array($organisation->exams_conducted_ids) ? $organisation->exams_conducted_ids : []) }}"></div>
                                                <div class="col-md-6"><label class="form-label">Annual Exam Volume Estimate</label><input type="text" name="annual_exam_volume_estimate" class="form-control" value="{{ old('annual_exam_volume_estimate', $organisation->annual_exam_volume_estimate) }}"></div>
                                                <div class="col-md-6"><label class="form-label">Average Candidates Per Year</label><input type="text" name="average_candidates_per_year" class="form-control" value="{{ old('average_candidates_per_year', $organisation->average_candidates_per_year) }}"></div>
                                            </div>
                                        </div>

                                        <!-- 5. Capabilities -->
                                        <div class="tab-pane fade" id="ecb-capabilities" role="tabpanel">
                                            <div class="row g-3">
                                                <div class="col-md-12">
                                                    <label class="form-label d-block">Exam Modes Supported</label>
                                                    @php $currentModes = is_array($organisation->exam_modes_supported) ? $organisation->exam_modes_supported : [] @endphp
                                                    @foreach(['Online', 'Offline'] as $mode)
                                                        <div class="form-check form-check-inline">
                                                            <input class="form-check-input" type="checkbox" name="exam_modes_supported[]" value="{{ $mode }}" id="edit_mode_{{ Str::slug($mode) }}" {{ in_array($mode, old('exam_modes_supported', $currentModes)) ? 'checked' : '' }}>
                                                            <label class="form-check-label" for="edit_mode_{{ Str::slug($mode) }}">{{ $mode }}</label>
                                                        </div>
                                                    @endforeach
                                                </div>
                                                <div class="col-md-6"><div class="form-check form-switch"><input class="form-check-input" type="checkbox" name="question_bank_managed" id="q_bank" value="1" {{ old('question_bank_managed', $organisation->question_bank_managed) ? 'checked' : '' }}><label class="form-check-label" for="q_bank">Question Bank Managed</label></div></div>
                                                <div class="col-md-6"><div class="form-check form-switch"><input class="form-check-input" type="checkbox" name="normalization_process_available" id="norm_proc" value="1" {{ old('normalization_process_available', $organisation->normalization_process_available) ? 'checked' : '' }}><label class="form-check-label" for="norm_proc">Normalization Process Available</label></div></div>
                                                <div class="col-md-6"><div class="form-check form-switch"><input class="form-check-input" type="checkbox" name="multi_language_support" id="multi_lang" value="1" {{ old('multi_language_support', $organisation->multi_language_support) ? 'checked' : '' }}><label class="form-check-label" for="multi_lang">Multi-language Support</label></div></div>
                                                <div class="col-md-6"><div class="form-check form-switch"><input class="form-check-input" type="checkbox" name="remote_proctoring_supported" id="remote_proc" value="1" {{ old('remote_proctoring_supported', $organisation->remote_proctoring_supported) ? 'checked' : '' }}><label class="form-check-label" for="remote_proc">Remote Proctoring Supported</label></div></div>
                                            </div>
                                        </div>

                                        <!-- 6. Partners & Security -->
                                        <div class="tab-pane fade" id="ecb-partners" role="tabpanel">
                                            <div class="row g-3">
                                                <div class="col-md-6">
                                                    <label class="form-label">Exam Centres Management Type</label>
                                                    <select name="exam_centres_management_type" class="form-select">
                                                        <option value="">Select Management Type</option>
                                                        @foreach(['In-house', 'Outsourced', 'Hybrid'] as $opt)
                                                            <option value="{{ $opt }}" {{ old('exam_centres_management_type', $organisation->exam_centres_management_type) == $opt ? 'selected' : '' }}>{{ $opt }}</option>
                                                        @endforeach
                                                    </select>
                                                </div>
                                                <div class="col-md-6">
                                                    <label class="form-label">Data Security Standards</label>
                                                    <select name="data_security_standards" class="form-select">
                                                        <option value="">Select Security Standard</option>
                                                        @foreach(['ISO', 'Government Norms'] as $opt)
                                                            <option value="{{ $opt }}" {{ old('data_security_standards', $organisation->data_security_standards) == $opt ? 'selected' : '' }}>{{ $opt }}</option>
                                                        @endforeach
                                                    </select>
                                                </div>
                                                <div class="col-md-12"><label class="form-label">Anti-Malpractice Measures (Comma separated)</label><input type="text" name="anti_malpractice_measures" class="form-control" value="{{ implode(',', is_array($organisation->anti_malpractice_measures) ? $organisation->anti_malpractice_measures : []) }}"></div>
                                                <div class="col-md-12"><label class="form-label">Technology Partners</label><input type="text" name="technology_partners" class="form-control" value="{{ implode(',', is_array($organisation->technology_partners) ? $organisation->technology_partners : []) }}"></div>
                                                <div class="col-md-12"><label class="form-label">Logistics Partners</label><input type="text" name="logistics_partners" class="form-control" value="{{ implode(',', is_array($organisation->logistics_partners) ? $organisation->logistics_partners : []) }}"></div>
                                            </div>
                                        </div>

                                        <!-- 7. Policies & Data -->
                                        <div class="tab-pane fade" id="ecb-policies" role="tabpanel">
                                            <div class="row g-3">
                                                <div class="col-md-12"><label class="form-label">Result Declaration Policy Summary</label><textarea name="result_declaration_policy_summary" class="form-control" rows="2">{{ old('result_declaration_policy_summary', $organisation->result_declaration_policy_summary) }}</textarea></div>
                                                <div class="col-md-6"><label class="form-label">Score Validity Period</label><input type="text" name="score_validity_period" class="form-control" value="{{ old('score_validity_period', $organisation->score_validity_period) }}"></div>
                                                <div class="col-md-6"><div class="form-check form-switch mt-4"><input class="form-check-input" type="checkbox" name="re_evaluation_allowed" id="re_eval" {{ old('re_evaluation_allowed', $organisation->re_evaluation_allowed) ? 'checked' : '' }}><label class="form-check-label" for="re_eval">Re-evaluation Allowed</label></div></div>
                                                <div class="col-md-12"><label class="form-label">Exam Fairness Policy Summary</label><textarea name="exam_fairness_policy" class="form-control" rows="2">{{ old('exam_fairness_policy', $organisation->exam_fairness_policy) }}</textarea></div>
                                                <div class="col-md-12"><label class="form-label">Re-evaluation Process Summary</label><textarea name="re_evaluation_process_summary" class="form-control" rows="2">{{ old('re_evaluation_process_summary', $organisation->re_evaluation_process_summary) }}</textarea></div>
                                                <div class="col-md-12"><label class="form-label">Data Retention Policy</label><textarea name="data_retention_policy" class="form-control" rows="2">{{ old('data_retention_policy', $organisation->data_retention_policy) }}</textarea></div>
                                                <div class="col-md-12"><label class="form-label">Grievance Redressal Mechanism</label><textarea name="grievance_redressal_mechanism" class="form-control" rows="2">{{ old('grievance_redressal_mechanism', $organisation->grievance_redressal_mechanism) }}</textarea></div>
                                            </div>
                                        </div>

                                        <!-- 8. Support & Transparency -->
                                        <div class="tab-pane fade" id="ecb-support" role="tabpanel">
                                            <div class="row g-3">
                                                <div class="col-md-6"><label class="form-label">Candidate Portal URL</label><input type="url" name="candidate_portal_url" class="form-control" value="{{ old('candidate_portal_url', $organisation->candidate_portal_url) }}"></div>
                                                <div class="col-md-6"><label class="form-label">Helpdesk Contact Number</label><input type="text" name="helpdesk_contact_number" class="form-control" value="{{ old('helpdesk_contact_number', $organisation->helpdesk_contact_number) }}"></div>
                                                <div class="col-md-6"><label class="form-label">Helpdesk Email</label><input type="email" name="helpdesk_email" class="form-control" value="{{ old('helpdesk_email', $organisation->helpdesk_email) }}"></div>
                                                <div class="col-md-6"><label class="form-label">FAQ URL</label><input type="url" name="faq_url" class="form-control" value="{{ old('faq_url', $organisation->faq_url) }}"></div>
                                                <div class="col-md-6"><div class="form-check form-switch"><input class="form-check-input" type="checkbox" name="rti_applicable" id="rti" {{ old('rti_applicable', $organisation->rti_applicable) ? 'checked' : '' }}><label class="form-check-label" for="rti">RTI Applicable</label></div></div>
                                                <div class="col-md-6"><div class="form-check form-switch"><input class="form-check-input" type="checkbox" name="audit_conducted" id="audit" {{ old('audit_conducted', $organisation->audit_conducted) ? 'checked' : '' }}><label class="form-check-label" for="audit">Audit Conducted</label></div></div>
                                                <div class="col-md-6"><div class="form-check form-switch"><input class="form-check-input" type="checkbox" name="whistleblower_policy_available" id="whistle" {{ old('whistleblower_policy_available', $organisation->whistleblower_policy_available) ? 'checked' : '' }}><label class="form-check-label" for="whistle">Whistleblower Policy Available</label></div></div>
                                                <div class="col-md-12"><label class="form-label">Official Notifications URLs (Comma separated)</label><input type="text" name="official_notifications_urls" class="form-control" value="{{ implode(',', is_array($organisation->official_notifications_urls) ? $organisation->official_notifications_urls : []) }}"></div>
                                            </div>
                                        </div>

                                        <!-- 9. Reputation & Trust -->
                                        <div class="tab-pane fade" id="ecb-reputation" role="tabpanel">
                                            <div class="row g-3">
                                                <div class="col-md-12"><label class="form-label">Awards or Recognition (Comma separated)</label><input type="text" name="awards_or_recognition" class="form-control" value="{{ implode(',', is_array($organisation->awards_or_recognition) ? $organisation->awards_or_recognition : []) }}"></div>
                                                <div class="col-md-12"><label class="form-label">Media Mentions (Comma separated)</label><input type="text" name="media_mentions" class="form-control" value="{{ implode(',', is_array($organisation->media_mentions) ? $organisation->media_mentions : []) }}"></div>
                                            </div>
                                        </div>

                                        <!-- 10. Administrative -->
                                        <div class="tab-pane fade" id="ecb-admin" role="tabpanel">
                                            <div class="row g-3">
                                                <div class="col-md-6"><label class="form-label">Data Source</label><input type="text" name="data_source" class="form-control" value="{{ old('data_source', $organisation->data_source) }}"></div>
                                                <div class="col-md-6"><label class="form-label">Confidence Score</label><input type="number" name="confidence_score" class="form-control" value="{{ old('confidence_score', $organisation->confidence_score) }}"></div>
                                                <div class="col-md-6">
                                                    <label class="form-label">Verification Status</label>
                                                    <select name="verification_status" class="form-select">
                                                        <option value="Pending" {{ old('verification_status', $organisation->verification_status) == 'Pending' ? 'selected' : '' }}>Pending</option>
                                                        <option value="Verified" {{ old('verification_status', $organisation->verification_status) == 'Verified' ? 'selected' : '' }}>Verified</option>
                                                        <option value="Rejected" {{ old('verification_status', $organisation->verification_status) == 'Rejected' ? 'selected' : '' }}>Rejected</option>
                                                    </select>
                                                </div>
                                                <div class="col-md-6">
                                                    <label class="form-label">Status</label>
                                                    <select name="status" class="form-select">
                                                        <option value="Active" {{ old('status', $organisation->status) == 'Active' ? 'selected' : '' }}>Active</option>
                                                        <option value="Inactive" {{ old('status', $organisation->status) == 'Inactive' ? 'selected' : '' }}>Inactive</option>
                                                        <option value="Archived" {{ old('status', $organisation->status) == 'Archived' ? 'selected' : '' }}>Archived</option>
                                                    </select>
                                                </div>
                                            </div>
                                        </div>
                                </div>
                            </div> <!-- End Row -->

                            </div>
                            <!-- Counselling Body Specific Fields Section -->
                            <div id="counselling-body-fields" class="col-12" style="display: none;">
                                <hr class="my-4">
                                <h5 class="mb-3 text-warning">Counselling Body Details (ID: 6)</h5>

                                <ul class="nav nav-tabs mb-3" id="cbTab" role="tablist">
                                    <li class="nav-item"><button class="nav-link active" id="cb-core-tab" data-bs-toggle="tab" data-bs-target="#cb-core" type="button" role="tab">1. Core Identity</button></li>
                                    <li class="nav-item"><button class="nav-link" id="cb-legal-tab" data-bs-toggle="tab" data-bs-target="#cb-legal" type="button" role="tab">2. Legal Status</button></li>
                                    <li class="nav-item"><button class="nav-link" id="cb-scope-tab" data-bs-toggle="tab" data-bs-target="#cb-scope" type="button" role="tab">3. Scope</button></li>
                                    <li class="nav-item"><button class="nav-link" id="cb-exams-tab" data-bs-toggle="tab" data-bs-target="#cb-exams" type="button" role="tab">4. Exams</button></li>
                                    <li class="nav-item"><button class="nav-link" id="cb-seats-tab" data-bs-toggle="tab" data-bs-target="#cb-seats" type="button" role="tab">5. Seat Mgmt</button></li>
                                    <li class="nav-item"><button class="nav-link" id="cb-gov-tab" data-bs-toggle="tab" data-bs-target="#cb-gov" type="button" role="tab">6. Governance</button></li>
                                    <li class="nav-item"><button class="nav-link" id="cb-fees-tab" data-bs-toggle="tab" data-bs-target="#cb-fees" type="button" role="tab">7. Fees</button></li>
                                    <li class="nav-item"><button class="nav-link" id="cb-tech-tab" data-bs-toggle="tab" data-bs-target="#cb-tech" type="button" role="tab">8. Tech</button></li>
                                    <li class="nav-item"><button class="nav-link" id="cb-reporting-tab" data-bs-toggle="tab" data-bs-target="#cb-reporting" type="button" role="tab">9. Reporting</button></li>
                                    <li class="nav-item"><button class="nav-link" id="cb-grievance-tab" data-bs-toggle="tab" data-bs-target="#cb-grievance" type="button" role="tab">10. Grievance</button></li>
                                    <li class="nav-item"><button class="nav-link" id="cb-support-tab" data-bs-toggle="tab" data-bs-target="#cb-support" type="button" role="tab">11. Support</button></li>
                                    <li class="nav-item"><button class="nav-link" id="cb-history-tab" data-bs-toggle="tab" data-bs-target="#cb-history" type="button" role="tab">12. History</button></li>
                                    <li class="nav-item"><button class="nav-link" id="cb-seo-tab" data-bs-toggle="tab" data-bs-target="#cb-seo" type="button" role="tab">13. SEO</button></li>
                                    <li class="nav-item"><button class="nav-link" id="cb-admin-tab" data-bs-toggle="tab" data-bs-target="#cb-admin" type="button" role="tab">14. Admin</button></li>
                                </ul>

                                <div class="tab-content" id="cbTabContent">
                                    <!-- 1. CORE IDENTITY -->
                                    <div class="tab-pane fade show active" id="cb-core" role="tabpanel">
                                        <div class="row g-3">
                                            <div class="col-md-6"><label class="form-label">Organisation Name</label><input type="text" name="name" class="form-control" value="{{ old('name', $organisation->name) }}" id="cb_name_sync"></div>
                                            <div class="col-md-6"><label class="form-label">Short Name</label><input type="text" name="short_name" class="form-control" value="{{ old('short_name', $organisation->short_name) }}" id="cb_short_sync"></div>
                                            <div class="col-md-6"><label class="form-label">Logo</label>
                                                <input type="file" name="logo_url" class="form-control file-preview-input" accept="image/*" id="cb_logo">
                                                <div class="form-text text-muted">Image size should not exceed 2MB.</div>
                                                <div class="file-preview mt-2" data-preview="logo_url">
                                                    @if($organisation->logo_url) <img src="{{ asset($organisation->logo_url) }}" class="img-thumbnail" style="height: 100px;"> @endif
                                                </div>
                                            </div>
                                            <div class="col-md-6"><label class="form-label">Established Year</label><input type="number" name="established_year" class="form-control" value="{{ old('established_year', $organisation->established_year) }}" id="cb_est_sync"></div>
                                            <div class="col-md-12"><label class="form-label">About Organization</label><textarea name="about_organisation" class="form-control" rows="3">{{ old('about_organisation', $organisation->about_organisation) }}</textarea></div>
                                            <div class="col-md-12"><label class="form-label">Mandate Description</label><textarea name="mandate_description" class="form-control" rows="3">{{ old('mandate_description', $organisation->mandate_description) }}</textarea></div>
                                        </div>
                                    </div>

                                    <!-- 2. LEGAL & ADMINISTRATIVE STATUS -->
                                    <div class="tab-pane fade" id="cb-legal" role="tabpanel">
                                        <div class="row g-3">
                                            <div class="col-md-6"><label class="form-label">Authority Type</label><select name="authority_type" class="form-select">
                                                    <option value="">Select</option>
                                                    @foreach(['Statutory Body', 'Government Committee', 'Autonomous Body'] as $opt)
                                                        <option value="{{ $opt }}" {{ old('authority_type', $organisation->authority_type) == $opt ? 'selected' : '' }}>{{ $opt }}</option>
                                                    @endforeach
                                                </select></div>
                                            <div class="col-md-6"><label class="form-label">Parent Ministry or Department</label><input type="text" name="parent_ministry_or_department" class="form-control" value="{{ old('parent_ministry_or_department', $organisation->parent_ministry_or_department) }}"></div>
                                            <div class="col-md-6"><label class="form-label">Established By</label><select name="established_by" class="form-select">
                                                    <option value="">Select</option>
                                                    @foreach(['Government Notification', 'Act / Resolution'] as $opt)
                                                        <option value="{{ $opt }}" {{ old('established_by', $organisation->established_by) == $opt ? 'selected' : '' }}>{{ $opt }}</option>
                                                    @endforeach
                                                </select></div>
                                            <div class="col-md-6"><label class="form-label">Legal Reference Document URL</label><input type="text" name="legal_reference_document_url" class="form-control" value="{{ old('legal_reference_document_url', $organisation->legal_reference_document_url) }}"></div>
                                            <div class="col-md-6"><label class="form-label">Jurisdiction Scope</label><select name="jurisdiction_scope" class="form-select" id="cb_jurisdiction">
                                                    <option value="">Select</option>
                                                    @foreach(['National', 'State', 'Regional'] as $opt)
                                                        <option value="{{ $opt }}" {{ old('jurisdiction_scope', $organisation->jurisdiction_scope) == $opt ? 'selected' : '' }}>{{ $opt }}</option>
                                                    @endforeach
                                                </select></div>
                                            <div class="col-md-6" id="cb_juris_states_div" style="display:{{ old('jurisdiction_scope', $organisation->jurisdiction_scope) == 'State' ? 'block' : 'none' }};"><label class="form-label">Jurisdiction States</label><input type="text" name="jurisdiction_states" class="form-control" value="{{ implode(',', is_array($organisation->jurisdiction_states) ? $organisation->jurisdiction_states : []) }}" placeholder="Comma separated"></div>
                                        </div>
                                    </div>

                                    <!-- 3. COUNSELLING FUNCTIONS & SCOPE -->
                                    <div class="tab-pane fade" id="cb-scope" role="tabpanel">
                                        <div class="row g-3">
                                            <div class="col-md-12"><label class="form-label d-block">Functions</label>
                                                @php $currentCFunctions = is_array($organisation->counselling_functions) ? $organisation->counselling_functions : [] @endphp
                                                @foreach(['Registration Management', 'Choice Filling', 'Seat Allocation', 'Reporting & Joining', 'Admission Confirmation'] as $f)
                                                    <div class="form-check form-check-inline"><input class="form-check-input" type="checkbox" name="counselling_functions[]" value="{{ $f }}" id="cbf_{{ Str::slug($f) }}" {{ in_array($f, old('counselling_functions', $currentCFunctions)) ? 'checked' : '' }}><label class="form-check-label" for="cbf_{{ Str::slug($f) }}">{{ $f }}</label></div>
                                                @endforeach
                                            </div>
                                            <div class="col-md-12"><label class="form-label d-block">Counselling Types Supported</label>
                                                @php $currentCTypes = is_array($organisation->counselling_types_supported) ? $organisation->counselling_types_supported : [] @endphp
                                                @foreach(['Centralised Counselling', 'State Counselling', 'Institutional Counselling'] as $t)
                                                    <div class="form-check form-check-inline"><input class="form-check-input" type="checkbox" name="counselling_types_supported[]" value="{{ $t }}" id="cbt_{{ Str::slug($t) }}" {{ in_array($t, old('counselling_types_supported', $currentCTypes)) ? 'checked' : '' }}><label class="form-check-label" for="cbt_{{ Str::slug($t) }}">{{ $t }}</label></div>
                                                @endforeach
                                            </div>
                                            <div class="col-md-12"><label class="form-label d-block">Education Domains Supported</label>
                                                @php $currentDomains = is_array($organisation->education_domains_supported) ? $organisation->education_domains_supported : [] @endphp
                                                @foreach(['Medical', 'Engineering', 'Law', 'Architecture'] as $d)
                                                    <div class="form-check form-check-inline"><input class="form-check-input" type="checkbox" name="education_domains_supported[]" value="{{ $d }}" id="cbd_{{ Str::slug($d) }}" {{ in_array($d, old('education_domains_supported', $currentDomains)) ? 'checked' : '' }}><label class="form-check-label" for="cbd_{{ Str::slug($d) }}">{{ $d }}</label></div>
                                                @endforeach
                                            </div>
                                            <div class="col-md-12"><label class="form-label d-block">Levels Supported</label>
                                                @php $currentLevels = is_array($organisation->counselling_levels_supported) ? $organisation->counselling_levels_supported : [] @endphp
                                                @foreach(['UG', 'PG', 'Diploma'] as $l)
                                                    <div class="form-check form-check-inline"><input class="form-check-input" type="checkbox" name="counselling_levels_supported[]" value="{{ $l }}" id="cbl_{{ Str::slug($l) }}" {{ in_array($l, old('counselling_levels_supported', $currentLevels)) ? 'checked' : '' }}><label class="form-check-label" for="cbl_{{ Str::slug($l) }}">{{ $l }}</label></div>
                                                @endforeach
                                            </div>
                                        </div>
                                    </div>

                                    <!-- 4. EXAMS & ALLOCATION BASIS -->
                                    <div class="tab-pane fade" id="cb-exams" role="tabpanel">
                                        <div class="row g-3">
                                            <div class="col-md-12"><label class="form-label">Exams Used for Counselling (IDs)</label><input type="text" name="exams_used_for_counselling_ids" class="form-control" value="{{ implode(',', is_array($organisation->exams_used_for_counselling_ids) ? $organisation->exams_used_for_counselling_ids : []) }}" placeholder="Comma separated IDs"></div>
                                            <div class="col-md-6"><label class="form-label">Allocation Basis</label><select name="allocation_basis" class="form-select">
                                                    <option value="">Select</option>
                                                    @foreach(['Rank', 'Score', 'Composite Merit'] as $opt)
                                                        <option value="{{ $opt }}" {{ old('allocation_basis', $organisation->allocation_basis) == $opt ? 'selected' : '' }}>{{ $opt }}</option>
                                                    @endforeach
                                                </select></div>
                                            <div class="col-md-3">
                                                <div class="form-check form-switch mt-4"><input class="form-check-input" type="checkbox" name="rank_source_validation_required" id="cb_rank_val" {{ old('rank_source_validation_required', $organisation->rank_source_validation_required) ? 'checked' : '' }}><label class="form-check-label" for="cb_rank_val">Rank Source Validation Required</label></div>
                                            </div>
                                            <div class="col-md-3">
                                                <div class="form-check form-switch mt-4"><input class="form-check-input" type="checkbox" name="multiple_exam_support" id="cb_multi_exam" {{ old('multiple_exam_support', $organisation->multiple_exam_support) ? 'checked' : '' }}><label class="form-check-label" for="cb_multi_exam">Multiple Exam Support</label></div>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- 5. SEAT MANAGEMENT CAPABILITIES -->
                                    <div class="tab-pane fade" id="cb-seats" role="tabpanel">
                                        <div class="row g-3">
                                            <div class="col-md-3">
                                                <div class="form-check form-switch mt-4"><input class="form-check-input" type="checkbox" name="seat_matrix_management" id="cb_seat_mgmt" {{ old('seat_matrix_management', $organisation->seat_matrix_management) ? 'checked' : '' }}><label class="form-check-label" for="cb_seat_mgmt">Seat Matrix Management</label></div>
                                            </div>
                                            <div class="col-md-6"><label class="form-label">Seat Matrix Source</label><select name="seat_matrix_source" class="form-select">
                                                    <option value="">Select</option>
                                                    @foreach(['Institutions', 'Regulatory Body', 'Combined'] as $opt)
                                                        <option value="{{ $opt }}" {{ old('seat_matrix_source', $organisation->seat_matrix_source) == $opt ? 'selected' : '' }}>{{ $opt }}</option>
                                                    @endforeach
                                                </select></div>
                                            <div class="col-md-12"><label class="form-label d-block">Quota Types Managed</label>
                                                @php $currentQuotas = is_array($organisation->quota_types_managed) ? $organisation->quota_types_managed : [] @endphp
                                                @foreach(['AIQ', 'State Quota', 'Institutional Quota', 'Management Quota'] as $q)
                                                    <div class="form-check form-check-inline"><input class="form-check-input" type="checkbox" name="quota_types_managed[]" value="{{ $q }}" id="cbq_{{ Str::slug($q) }}" {{ in_array($q, old('quota_types_managed', $currentQuotas)) ? 'checked' : '' }}><label class="form-check-label" for="cbq_{{ Str::slug($q) }}">{{ $q }}</label></div>
                                                @endforeach
                                            </div>
                                            <div class="col-md-12"><label class="form-label">Reservation Policy Reference</label><textarea name="reservation_policy_reference" class="form-control" rows="2">{{ old('reservation_policy_reference', $organisation->reservation_policy_reference) }}</textarea></div>
                                            <div class="col-md-6">
                                                <div class="form-check form-switch"><input class="form-check-input" type="checkbox" name="seat_conversion_rules_supported" id="cb_seat_conv" {{ old('seat_conversion_rules_supported', $organisation->seat_conversion_rules_supported) ? 'checked' : '' }}><label class="form-check-label" for="cb_seat_conv">Seat Conversion Rules Supported</label></div>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- 6. COUNSELLING PROCESS GOVERNANCE -->
                                    <div class="tab-pane fade" id="cb-gov" role="tabpanel">
                                        <div class="row g-3">
                                            <div class="col-md-6"><label class="form-label">Rounds Supported</label><input type="text" name="rounds_supported" class="form-control" value="{{ old('rounds_supported', $organisation->rounds_supported) }}"></div>
                                            <div class="col-md-12"><label class="form-label d-block">Round Types</label>
                                                @php $currentRTypes = is_array($organisation->round_types) ? $organisation->round_types : [] @endphp
                                                @foreach(['Regular', 'Mop-up', 'Stray Vacancy'] as $rt)
                                                    <div class="form-check form-check-inline"><input class="form-check-input" type="checkbox" name="round_types[]" value="{{ $rt }}" id="cbrt_{{ Str::slug($rt) }}" {{ in_array($rt, old('round_types', $currentRTypes)) ? 'checked' : '' }}><label class="form-check-label" for="cbrt_{{ Str::slug($rt) }}">{{ $rt }}</label></div>
                                                @endforeach
                                            </div>
                                            <div class="col-md-4">
                                                <div class="form-check form-switch"><input class="form-check-input" type="checkbox" name="choice_locking_mandatory" id="cb_choice_lock" {{ old('choice_locking_mandatory', $organisation->choice_locking_mandatory) ? 'checked' : '' }}><label class="form-check-label" for="cb_choice_lock">Choice Locking Mandatory</label></div>
                                            </div>
                                            <div class="col-md-4">
                                                <div class="form-check form-switch"><input class="form-check-input" type="checkbox" name="seat_upgradation_allowed" id="cb_seat_up" {{ old('seat_upgradation_allowed', $organisation->seat_upgradation_allowed) ? 'checked' : '' }}><label class="form-check-label" for="cb_seat_up">Seat Upgradation Allowed</label></div>
                                            </div>
                                            <div class="col-md-12"><label class="form-label">Withdrawal Rules Summary</label><textarea name="withdrawal_rules_summary" class="form-control" rows="2">{{ old('withdrawal_rules_summary', $organisation->withdrawal_rules_summary) }}</textarea></div>
                                            <div class="col-md-12"><label class="form-label">Exit Rules Summary</label><textarea name="exit_rules_summary" class="form-control" rows="2">{{ old('exit_rules_summary', $organisation->exit_rules_summary) }}</textarea></div>
                                        </div>
                                    </div>

                                    <!-- 7. FEES & FINANCIAL HANDLING -->
                                    <div class="tab-pane fade" id="cb-fees" role="tabpanel">
                                        <div class="row g-3">
                                            <div class="col-md-4">
                                                <div class="form-check form-switch mt-4"><input class="form-check-input" type="checkbox" name="counselling_fee_collection_supported" id="cb_fee_coll" {{ old('counselling_fee_collection_supported', $organisation->counselling_fee_collection_supported) ? 'checked' : '' }}><label class="form-check-label" for="cb_fee_coll">Counselling Fee Collection Supported</label></div>
                                            </div>
                                            <div class="col-md-4"><label class="form-label">Fee Collection Mode</label><select name="fee_collection_mode" class="form-select">
                                                    <option value="">Select</option>
                                                    @foreach(['Direct to Authority', 'Through Exam Portal'] as $opt)
                                                        <option value="{{ $opt }}" {{ old('fee_collection_mode', $organisation->fee_collection_mode) == $opt ? 'selected' : '' }}>{{ $opt }}</option>
                                                    @endforeach
                                                </select></div>
                                            <div class="col-md-4"><label class="form-label">Refund Processing Responsibility</label><select name="refund_processing_responsibility" class="form-select">
                                                    <option value="">Select</option>
                                                    @foreach(['Authority', 'Exam Body'] as $opt)
                                                        <option value="{{ $opt }}" {{ old('refund_processing_responsibility', $organisation->refund_processing_responsibility) == $opt ? 'selected' : '' }}>{{ $opt }}</option>
                                                    @endforeach
                                                </select></div>
                                            <div class="col-md-4">
                                                <div class="form-check form-switch"><input class="form-check-input" type="checkbox" name="security_deposit_handling" id="cb_sec_dep" {{ old('security_deposit_handling', $organisation->security_deposit_handling) ? 'checked' : '' }}><label class="form-check-label" for="cb_sec_dep">Security Deposit Handling</label></div>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- 8. TECHNOLOGY & INFRASTRUCTURE -->
                                    <div class="tab-pane fade" id="cb-tech" role="tabpanel">
                                        <div class="row g-3">
                                            <div class="col-md-6"><label class="form-label">Counselling Portal URL</label><input type="url" name="counselling_portal_url" class="form-control" value="{{ old('counselling_portal_url', $organisation->counselling_portal_url) }}"></div>
                                            <div class="col-md-3">
                                                <div class="form-check form-switch mt-4"><input class="form-check-input" type="checkbox" name="candidate_login_system_available" id="cb_login" {{ old('candidate_login_system_available', $organisation->candidate_login_system_available) ? 'checked' : '' }}><label class="form-check-label" for="cb_login">Candidate Login Available</label></div>
                                            </div>
                                            <div class="col-md-3">
                                                <div class="form-check form-switch mt-4"><input class="form-check-input" type="checkbox" name="choice_filling_system_available" id="cb_choice" {{ old('choice_filling_system_available', $organisation->choice_filling_system_available) ? 'checked' : '' }}><label class="form-check-label" for="cb_choice">Choice Filling Available</label></div>
                                            </div>
                                            <div class="col-md-3">
                                                <div class="form-check form-switch"><input class="form-check-input" type="checkbox" name="auto_seat_allocation_engine" id="cb_auto_seat" {{ old('auto_seat_allocation_engine', $organisation->auto_seat_allocation_engine) ? 'checked' : '' }}><label class="form-check-label" for="cb_auto_seat">Auto Seat Allocation Engine</label></div>
                                            </div>
                                            <div class="col-md-3">
                                                <div class="form-check form-switch"><input class="form-check-input" type="checkbox" name="api_integration_supported" id="cb_api" {{ old('api_integration_supported', $organisation->api_integration_supported) ? 'checked' : '' }}><label class="form-check-label" for="cb_api">API Integration Supported</label></div>
                                            </div>
                                            <div class="col-md-6"><label class="form-label">Data Security Standards</label><input type="text" name="data_security_standards" class="form-control" value="{{ old('data_security_standards', $organisation->data_security_standards) }}"></div>
                                        </div>
                                    </div>

                                    <!-- 9. REPORTING, VERIFICATION & INSTITUTION INTERFACE -->
                                    <div class="tab-pane fade" id="cb-reporting" role="tabpanel">
                                        <div class="row g-3">
                                            <div class="col-md-4">
                                                <div class="form-check form-switch mt-4"><input class="form-check-input" type="checkbox" name="institution_reporting_interface_available" id="cb_inst_rep" {{ old('institution_reporting_interface_available', $organisation->institution_reporting_interface_available) ? 'checked' : '' }}><label class="form-check-label" for="cb_inst_rep">Institution Reporting Interface</label></div>
                                            </div>
                                            <div class="col-md-4"><label class="form-label">Document Verification Mode</label><select name="document_verification_mode" class="form-select">
                                                    <option value="">Select</option>
                                                    @foreach(['Online', 'Physical', 'Hybrid'] as $opt)
                                                        <option value="{{ $opt }}" {{ old('document_verification_mode', $organisation->document_verification_mode) == $opt ? 'selected' : '' }}>{{ $opt }}</option>
                                                    @endforeach
                                                </select></div>
                                            <div class="col-md-12"><label class="form-label">Institution Confirmation Process Summary</label><textarea name="institution_confirmation_process_summary" class="form-control" rows="2">{{ old('institution_confirmation_process_summary', $organisation->institution_confirmation_process_summary) }}</textarea></div>
                                            <div class="col-md-12"><label class="form-label">MIS Reporting Controls</label><textarea name="mis_reporting_controls" class="form-control" rows="2">{{ old('mis_reporting_controls', $organisation->mis_reporting_controls) }}</textarea></div>
                                        </div>
                                    </div>

                                    <!-- 10. GRIEVANCE, APPEALS & TRANSPARENCY -->
                                    <div class="tab-pane fade" id="cb-grievance" role="tabpanel">
                                        <div class="row g-3">
                                            <div class="col-md-12"><label class="form-label">Grievance Redressal Mechanism</label><textarea name="grievance_redressal_mechanism" class="form-control" rows="2">{{ old('grievance_redressal_mechanism', $organisation->grievance_redressal_mechanism) }}</textarea></div>
                                            <div class="col-md-12"><label class="form-label">Appeal Process Summary</label><textarea name="appeal_process_summary" class="form-control" rows="2">{{ old('appeal_process_summary', $organisation->appeal_process_summary) }}</textarea></div>
                                            <div class="col-md-12"><label class="form-label">Grievance Contact Details</label><textarea name="grievance_contact_details" class="form-control" rows="2">{{ old('grievance_contact_details', $organisation->grievance_contact_details) }}</textarea></div>
                                            <div class="col-md-3">
                                                <div class="form-check form-switch"><input class="form-check-input" type="checkbox" name="rti_applicable" id="cb_rti" {{ old('rti_applicable', $organisation->rti_applicable) ? 'checked' : '' }}><label class="form-check-label" for="cb_rti">RTI Applicable</label></div>
                                            </div>
                                            <div class="col-md-3">
                                                <div class="form-check form-switch"><input class="form-check-input" type="checkbox" name="audit_conducted" id="cb_audit" {{ old('audit_conducted', $organisation->audit_conducted) ? 'checked' : '' }}><label class="form-check-label" for="cb_audit">Audit Conducted</label></div>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- 11. CANDIDATE SUPPORT & INFORMATION -->
                                    <div class="tab-pane fade" id="cb-support" role="tabpanel">
                                        <div class="row g-3">
                                            <div class="col-md-6"><label class="form-label">Candidate Support URL</label><input type="url" name="candidate_support_url" class="form-control" value="{{ old('candidate_support_url', $organisation->candidate_support_url) }}"></div>
                                            <div class="col-md-6"><label class="form-label">Candidate Handbook URL</label><input type="url" name="candidate_handbook_url" class="form-control" value="{{ old('candidate_handbook_url', $organisation->candidate_handbook_url) }}"></div>
                                            <div class="col-md-6"><label class="form-label">Helpdesk Toll-free Number</label><input type="text" name="helpdesk_toll_free_number" class="form-control" value="{{ old('helpdesk_toll_free_number', $organisation->helpdesk_toll_free_number) }}"></div>
                                            <div class="col-md-6"><label class="form-label">Helpdesk Operational Hours</label><input type="text" name="helpdesk_operational_hours" class="form-control" value="{{ old('helpdesk_operational_hours', $organisation->helpdesk_operational_hours) }}"></div>
                                            <div class="col-md-12"><label class="form-label">Official Notifications URLs</label><input type="text" name="official_notifications_urls" class="form-control" value="{{ implode(',', is_array($organisation->official_notifications_urls) ? $organisation->official_notifications_urls : []) }}" placeholder="Comma separated URLs"></div>
                                            <div class="col-md-12"><label class="form-label">Social Media Handles</label><input type="text" name="social_media_handles" class="form-control" value="{{ implode(',', is_array($organisation->social_media_handles) ? $organisation->social_media_handles : []) }}" placeholder="Comma separated"></div>
                                        </div>
                                    </div>

                                    <!-- 12. HISTORICAL PERFORMANCE & TRUST -->
                                    <div class="tab-pane fade" id="cb-history" role="tabpanel">
                                        <div class="row g-3">
                                            <div class="col-md-6"><label class="form-label">Years of Operation</label><input type="number" name="years_of_operation" class="form-control" value="{{ old('years_of_operation', $organisation->years_of_operation) }}"></div>
                                            <div class="col-md-6"><label class="form-label">Total Candidates Handled Estimate</label><input type="text" name="total_candidates_handled_estimate" class="form-control" value="{{ old('total_candidates_handled_estimate', $organisation->total_candidates_handled_estimate) }}"></div>
                                            <div class="col-md-6"><label class="form-label">Total Seats Allocated Estimate</label><input type="text" name="total_seats_allocated_estimate" class="form-control" value="{{ old('total_seats_allocated_estimate', $organisation->total_seats_allocated_estimate) }}"></div>
                                            <div class="col-md-6"><label class="form-label">Public Trust Rating</label><input type="number" step="0.1" name="public_trust_rating" class="form-control" value="{{ old('public_trust_rating', $organisation->public_trust_rating) }}"></div>
                                        </div>
                                    </div>

                                    <!-- 13. SEO & PUBLIC FOOTPRINT -->
                                    <div class="tab-pane fade" id="cb-seo" role="tabpanel">
                                        <div class="row g-3">
                                            <div class="col-md-6"><label class="form-label">Meta Title</label><input type="text" name="meta_title" class="form-control" value="{{ old('meta_title', $organisation->meta_title) }}"></div>
                                            <div class="col-md-6"><label class="form-label">Canonical URL</label><input type="url" name="canonical_url" class="form-control" value="{{ old('canonical_url', $organisation->canonical_url) }}"></div>
                                            <div class="col-md-12"><label class="form-label">Meta Description</label><textarea name="meta_description" class="form-control" rows="2">{{ old('meta_description', $organisation->meta_description) }}</textarea></div>
                                            <div class="col-md-12"><label class="form-label">Keywords (Comma separated)</label><input type="text" name="focus_keywords" class="form-control" value="{{ implode(',', is_array($organisation->focus_keywords) ? $organisation->focus_keywords : []) }}"></div>
                                        </div>
                                    </div>

                                    <!-- 14. ADMINISTRATIVE & DATA SOURCE -->
                                    <div class="tab-pane fade" id="cb-admin" role="tabpanel">
                                        <div class="row g-3">
                                            <div class="col-md-6"><label class="form-label">Data Source</label><input type="text" name="data_source" class="form-control" value="{{ old('data_source', $organisation->data_source) }}"></div>
                                            <div class="col-md-6"><label class="form-label">Confidence Score</label><input type="number" name="confidence_score" class="form-control" value="{{ old('confidence_score', $organisation->confidence_score) }}"></div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            </div>
                            <!-- Regulatory Body Specific Fields Section -->
                            <div id="regulatory-body-fields" class="col-12" style="display: none;">
                                <hr class="my-4">
                                <h5 class="mb-3 text-danger">Regulatory Body Details (ID: 7)</h5>

                                <ul class="nav nav-tabs mb-3" id="rbTab" role="tablist">
                                    <li class="nav-item"><button class="nav-link active" id="rb-core-tab" data-bs-toggle="tab" data-bs-target="#rb-core" type="button" role="tab">1. Core Identity</button></li>
                                    <li class="nav-item"><button class="nav-link" id="rb-legal-tab" data-bs-toggle="tab" data-bs-target="#rb-legal" type="button" role="tab">2. Legal Status</button></li>
                                    <li class="nav-item"><button class="nav-link" id="rb-scope-tab" data-bs-toggle="tab" data-bs-target="#rb-scope" type="button" role="tab">3. Scope</button></li>
                                    <li class="nav-item"><button class="nav-link" id="rb-standards-tab" data-bs-toggle="tab" data-bs-target="#rb-standards" type="button" role="tab">4. Standards</button></li>
                                    <li class="nav-item"><button class="nav-link" id="rb-affiliation-tab" data-bs-toggle="tab" data-bs-target="#rb-affiliation" type="button" role="tab">5. Affiliation</button></li>
                                    <li class="nav-item"><button class="nav-link" id="rb-monitoring-tab" data-bs-toggle="tab" data-bs-target="#rb-monitoring" type="button" role="tab">6. Monitoring</button></li>
                                    <li class="nav-item"><button class="nav-link" id="rb-fees-tab" data-bs-toggle="tab" data-bs-target="#rb-fees" type="button" role="tab">7. Fees</button></li>
                                    <li class="nav-item"><button class="nav-link" id="rb-tech-tab" data-bs-toggle="tab" data-bs-target="#rb-tech" type="button" role="tab">8. Tech</button></li>
                                    <li class="nav-item"><button class="nav-link" id="rb-reporting-tab" data-bs-toggle="tab" data-bs-target="#rb-reporting" type="button" role="tab">9. Reporting</button></li>
                                    <li class="nav-item"><button class="nav-link" id="rb-grievance-tab" data-bs-toggle="tab" data-bs-target="#rb-grievance" type="button" role="tab">10. Grievance</button></li>
                                    <li class="nav-item"><button class="nav-link" id="rb-support-tab" data-bs-toggle="tab" data-bs-target="#rb-support" type="button" role="tab">11. Support</button></li>
                                    <li class="nav-item"><button class="nav-link" id="rb-history-tab" data-bs-toggle="tab" data-bs-target="#rb-history" type="button" role="tab">12. Scale</button></li>
                                    <li class="nav-item"><button class="nav-link" id="rb-seo-tab" data-bs-toggle="tab" data-bs-target="#rb-seo" type="button" role="tab">13. SEO</button></li>
                                    <li class="nav-item"><button class="nav-link" id="rb-admin-tab" data-bs-toggle="tab" data-bs-target="#rb-admin" type="button" role="tab">14. Admin</button></li>
                                </ul>

                                <div class="tab-content" id="rbTabContent">
                                    <!-- 1. CORE IDENTITY -->
                                    <div class="tab-pane fade show active" id="rb-core" role="tabpanel">
                                        <div class="row g-3">
                                            <div class="col-md-6"><label class="form-label">Organisation Name</label><input type="text" name="name" class="form-control" value="{{ old('name', $organisation->name) }}" id="rb_name_sync"></div>
                                            <div class="col-md-6"><label class="form-label">Abbreviation</label><input type="text" name="abbreviation" class="form-control" value="{{ old('abbreviation', $organisation->abbreviation) }}" placeholder="UGC, AICTE"></div>
                                            <div class="col-md-6"><label class="form-label">Short Name</label><input type="text" name="short_name" class="form-control" value="{{ old('short_name', $organisation->short_name) }}" id="rb_short_sync"></div>
                                            <div class="col-md-6"><label class="form-label">Logo</label>
                                                <input type="file" name="logo_url" class="form-control file-preview-input" accept="image/*">
                                                <div class="form-text text-muted">Image size should not exceed 2MB.</div>
                                                <div class="file-preview mt-2" data-preview="logo_url">
                                                    @if($organisation->logo_url) <img src="{{ asset($organisation->logo_url) }}" class="img-thumbnail" style="height: 100px;"> @endif
                                                </div>
                                            </div>
                                            <div class="col-md-6"><label class="form-label">Established Year</label><input type="number" name="established_year" class="form-control" value="{{ old('established_year', $organisation->established_year) }}" id="rb_est_sync"></div>
                                            <div class="col-md-12"><label class="form-label">About Organization</label><textarea name="about_organisation" class="form-control" rows="3">{{ old('about_organisation', $organisation->about_organisation) }}</textarea></div>
                                            <div class="col-md-12"><label class="form-label">Mandate Description</label><textarea name="mandate_description" class="form-control" rows="3">{{ old('mandate_description', $organisation->mandate_description) }}</textarea></div>
                                        </div>
                                    </div>

                                    <!-- 2. LEGAL & ADMINISTRATIVE STATUS -->
                                    <div class="tab-pane fade" id="rb-legal" role="tabpanel">
                                        <div class="row g-3">
                                            <div class="col-md-6"><label class="form-label">Authority Type</label><select name="authority_type" class="form-select">
                                                    <option value="">Select</option>
                                                    @foreach(['Constitutional Body', 'Statutory Body', 'Government Agency', 'Autonomous Body'] as $opt)
                                                        <option value="{{ $opt }}" {{ old('authority_type', $organisation->authority_type) == $opt ? 'selected' : '' }}>{{ $opt }}</option>
                                                    @endforeach
                                                </select></div>
                                            <div class="col-md-6"><label class="form-label">Parent Ministry/Department</label><input type="text" name="parent_ministry_or_department" class="form-control" value="{{ old('parent_ministry_or_department', $organisation->parent_ministry_or_department) }}"></div>
                                            <div class="col-md-6"><label class="form-label">Established By</label><select name="established_by" class="form-select">
                                                    <option value="">Select</option>
                                                    @foreach(['Act of Parliament', 'Government Resolution', 'Government Notification', 'Act / Resolution'] as $opt)
                                                        <option value="{{ $opt }}" {{ old('established_by', $organisation->established_by) == $opt ? 'selected' : '' }}>{{ $opt }}</option>
                                                    @endforeach
                                                </select></div>
                                            <div class="col-md-6"><label class="form-label">Legal Reference Document URL</label><input type="text" name="legal_reference_document_url" class="form-control" value="{{ old('legal_reference_document_url', $organisation->legal_reference_document_url) }}"></div>
                                            <div class="col-md-6"><label class="form-label">Jurisdiction Scope</label><select name="jurisdiction_scope" class="form-select" id="rb_jurisdiction">
                                                    <option value="">Select</option>
                                                    @foreach(['National', 'State', 'Multi-State', 'Regional'] as $opt)
                                                        <option value="{{ $opt }}" {{ old('jurisdiction_scope', $organisation->jurisdiction_scope) == $opt ? 'selected' : '' }}>{{ $opt }}</option>
                                                    @endforeach
                                                </select></div>
                                            <div class="col-md-6" id="rb_juris_states_div" style="display:{{ old('jurisdiction_scope', $organisation->jurisdiction_scope) == 'State' ? 'block' : 'none' }};"><label class="form-label">Jurisdiction States</label><input type="text" name="jurisdiction_states" class="form-control" value="{{ implode(',', is_array($organisation->jurisdiction_states) ? $organisation->jurisdiction_states : []) }}" placeholder="Comma separated"></div>
                                        </div>
                                    </div>

                                    <!-- 3. SCOPE & FUNCTIONS -->
                                    <div class="tab-pane fade" id="rb-scope" role="tabpanel">
                                        <div class="row g-3">
                                            <div class="col-md-12"><label class="form-label d-block">Regulatory Functions</label>
                                                @php $currentFunctions = is_array($organisation->functions) ? $organisation->functions : [] @endphp
                                                @foreach(['Policy Making', 'Accreditation', 'Funding', 'Inspection', 'Standard Setting', 'curriculum Framework'] as $f)
                                                    <div class="form-check form-check-inline"><input class="form-check-input" type="checkbox" name="functions[]" value="{{ $f }}" id="edit_rbf_{{ Str::slug($f) }}" {{ in_array($f, old('functions', $currentFunctions)) ? 'checked' : '' }}><label class="form-check-label" for="edit_rbf_{{ Str::slug($f) }}">{{ $f }}</label></div>
                                                @endforeach
                                            </div>
                                            <div class="col-md-12"><label class="form-label d-block">Education Domains Supported</label>
                                                @php $currentDomains = is_array($organisation->education_domains_supported) ? $organisation->education_domains_supported : [] @endphp
                                                @foreach(['Higher Education', 'Technical', 'Medical', 'Teacher Training', 'Vocational'] as $d)
                                                    <div class="form-check form-check-inline"><input class="form-check-input" type="checkbox" name="education_domains_supported[]" value="{{ $d }}" id="edit_rbd_{{ Str::slug($d) }}" {{ in_array($d, old('education_domains_supported', $currentDomains)) ? 'checked' : '' }}><label class="form-check-label" for="edit_rbd_{{ Str::slug($d) }}">{{ $d }}</label></div>
                                                @endforeach
                                            </div>
                                            <div class="col-md-12"><label class="form-label d-block">Counselling Roles</label>
                                                @php $currentCRoles = is_array($organisation->counselling_functions) ? $organisation->counselling_functions : [] @endphp
                                                @foreach(['Regulation', 'Advisory', 'Direct Control', 'Grievance Redressal'] as $r)
                                                    <div class="form-check form-check-inline"><input class="form-check-input" type="checkbox" name="counselling_functions[]" value="{{ $r }}" id="edit_rbcf_{{ Str::slug($r) }}" {{ in_array($r, old('counselling_functions', $currentCRoles)) ? 'checked' : '' }}><label class="form-check-label" for="edit_rbcf_{{ Str::slug($r) }}">{{ $r }}</label></div>
                                                @endforeach
                                            </div>
                                        </div>
                                    </div>

                                    <!-- 4. STANDARDS & COMPLIANCE -->
                                    <div class="tab-pane fade" id="rb-standards" role="tabpanel">
                                        <div class="row g-3">
                                            <div class="col-md-6"><label class="form-label">Allocation Basis</label><select name="allocation_basis" class="form-select">
                                                    <option value="">Select</option>
                                                    @foreach(['Rank', 'Score', 'Composite Merit'] as $opt)
                                                        <option value="{{ $opt }}" {{ old('allocation_basis', $organisation->allocation_basis) == $opt ? 'selected' : '' }}>{{ $opt }}</option>
                                                    @endforeach
                                                </select></div>
                                            <div class="col-md-3">
                                                <div class="form-check form-switch mt-4"><input class="form-check-input" type="checkbox" name="rank_source_validation_required" id="rb_rank_val" {{ old('rank_source_validation_required', $organisation->rank_source_validation_required) ? 'checked' : '' }}><label class="form-check-label" for="rb_rank_val">Rank Validation</label></div>
                                            </div>
                                            <div class="col-md-3">
                                                <div class="form-check form-switch mt-4"><input class="form-check-input" type="checkbox" name="multiple_exam_support" id="rb_multi_exam" {{ old('multiple_exam_support', $organisation->multiple_exam_support) ? 'checked' : '' }}><label class="form-check-label" for="rb_multi_exam">Multi-Exam Support</label></div>
                                            </div>
                                            <div class="col-md-6"><label class="form-label">Seat Matrix Source</label><select name="seat_matrix_source" class="form-select">
                                                    <option value="">Select</option>
                                                    @foreach(['Institutions', 'Regulatory Body', 'Combined'] as $opt)
                                                        <option value="{{ $opt }}" {{ old('seat_matrix_source', $organisation->seat_matrix_source) == $opt ? 'selected' : '' }}>{{ $opt }}</option>
                                                    @endforeach
                                                </select></div>
                                            <div class="col-md-6"><label class="form-label">Data Security Standards</label><input type="text" name="data_security_standards" class="form-control" value="{{ old('data_security_standards', $organisation->data_security_standards) }}"></div>
                                        </div>
                                    </div>

                                    <!-- 5. AFFILIATION & RECOGNITION -->
                                    <div class="tab-pane fade" id="rb-affiliation" role="tabpanel">
                                        <div class="row g-3">
                                            <div class="col-md-6"><label class="form-label">Institutions Covered (Count)</label><input type="number" name="institutions_covered_count" class="form-control" value="{{ old('institutions_covered_count', $organisation->institutions_covered_count) }}"></div>
                                            <div class="col-md-6"><label class="form-label">States Covered (Count)</label><input type="number" name="states_covered_count" class="form-control" value="{{ old('states_covered_count', $organisation->states_covered_count) }}"></div>
                                            <div class="col-md-12"><label class="form-label d-block">Quota Types Managed</label>
                                                @php $currentQuotas = is_array($organisation->quota_types_managed) ? $organisation->quota_types_managed : [] @endphp
                                                @foreach(['AIQ', 'State Quota', 'NRI', 'Management'] as $q)
                                                    <div class="form-check form-check-inline"><input class="form-check-input" type="checkbox" name="quota_types_managed[]" value="{{ $q }}" id="edit_rbq_{{ Str::slug($q) }}" {{ in_array($q, old('quota_types_managed', $currentQuotas)) ? 'checked' : '' }}><label class="form-check-label" for="edit_rbq_{{ Str::slug($q) }}">{{ $q }}</label></div>
                                                @endforeach
                                            </div>
                                            <div class="col-md-12"><label class="form-label">Reservation Policy Reference</label><textarea name="reservation_policy_reference" class="form-control" rows="2">{{ old('reservation_policy_reference', $organisation->reservation_policy_reference) }}</textarea></div>
                                        </div>
                                    </div>

                                    <!-- 6. MONITORING & INSPECTIONS -->
                                    <div class="tab-pane fade" id="rb-monitoring" role="tabpanel">
                                        <div class="row g-3">
                                            <div class="col-md-3">
                                                <div class="form-check form-switch mt-4"><input class="form-check-input" type="checkbox" name="audit_conducted" id="rb_audit" {{ old('audit_conducted', $organisation->audit_conducted) ? 'checked' : '' }}><label class="form-check-label" for="rb_audit">Audit Conducted</label></div>
                                            </div>
                                            <div class="col-md-3">
                                                <div class="form-check form-switch mt-4"><input class="form-check-input" type="checkbox" name="seat_matrix_management" id="rb_seat_mgmt" {{ old('seat_matrix_management', $organisation->seat_matrix_management) ? 'checked' : '' }}><label class="form-check-label" for="rb_seat_mgmt">Seat Mgmt</label></div>
                                            </div>
                                            <div class="col-md-6"><label class="form-label">MIS Reporting Controls</label><textarea name="mis_reporting_controls" class="form-control" rows="2">{{ old('mis_reporting_controls', $organisation->mis_reporting_controls) }}</textarea></div>
                                            <div class="col-md-12"><label class="form-label">Document Verification Mode</label><select name="document_verification_mode" class="form-select">
                                                    @foreach(['Online', 'Physical', 'Hybrid'] as $opt)
                                                        <option value="{{ $opt }}" {{ old('document_verification_mode', $organisation->document_verification_mode) == $opt ? 'selected' : '' }}>{{ $opt }}</option>
                                                    @endforeach
                                                </select></div>
                                        </div>
                                    </div>

                                    <!-- 7. FEES & FINANCIALS -->
                                    <div class="tab-pane fade" id="rb-fees" role="tabpanel">
                                        <div class="row g-3">
                                            <div class="col-md-4">
                                                <div class="form-check form-switch mt-4"><input class="form-check-input" type="checkbox" name="counselling_fee_collection_supported" id="rb_fee_coll" {{ old('counselling_fee_collection_supported', $organisation->counselling_fee_collection_supported) ? 'checked' : '' }}><label class="form-check-label" for="rb_fee_coll">Fee Collection</label></div>
                                            </div>
                                            <div class="col-md-4"><label class="form-label">Fee Mode</label><select name="fee_collection_mode" class="form-select">
                                                    @foreach(['Direct to Authority', 'Through Exam Portal'] as $opt)
                                                        <option value="{{ $opt }}" {{ old('fee_collection_mode', $organisation->fee_collection_mode) == $opt ? 'selected' : '' }}>{{ $opt }}</option>
                                                    @endforeach
                                                </select></div>
                                            <div class="col-md-4"><label class="form-label">Refund Responsibility</label><select name="refund_processing_responsibility" class="form-select">
                                                    @foreach(['Authority', 'Exam Body'] as $opt)
                                                        <option value="{{ $opt }}" {{ old('refund_processing_responsibility', $organisation->refund_processing_responsibility) == $opt ? 'selected' : '' }}>{{ $opt }}</option>
                                                    @endforeach
                                                </select></div>
                                        </div>
                                    </div>

                                    <!-- 8. TECH & DIGITAL -->
                                    <div class="tab-pane fade" id="rb-tech" role="tabpanel">
                                        <div class="row g-3">
                                            <div class="col-md-6"><label class="form-label">Portal URL</label><input type="url" name="counselling_portal_url" class="form-control" value="{{ old('counselling_portal_url', $organisation->counselling_portal_url) }}"></div>
                                            <div class="col-md-3"><div class="form-check form-switch mt-4"><input class="form-check-input" type="checkbox" name="candidate_login_system_available" id="rb_login" {{ old('candidate_login_system_available', $organisation->candidate_login_system_available) ? 'checked' : '' }}><label class="form-check-label" for="rb_login">Login Avail.</label></div></div>
                                            <div class="col-md-3"><div class="form-check form-switch mt-4"><input class="form-check-input" type="checkbox" name="api_integration_supported" id="rb_api" {{ old('api_integration_supported', $organisation->api_integration_supported) ? 'checked' : '' }}><label class="form-check-label" for="rb_api">API Support</label></div></div>
                                        </div>
                                    </div>

                                    <!-- 9. REPORTING -->
                                    <div class="tab-pane fade" id="rb-reporting" role="tabpanel">
                                        <div class="row g-3">
                                            <div class="col-md-6"><div class="form-check form-switch mt-4"><input class="form-check-input" type="checkbox" name="institution_reporting_interface_available" id="rb_inst_rep" {{ old('institution_reporting_interface_available', $organisation->institution_reporting_interface_available) ? 'checked' : '' }}><label class="form-check-label" for="rb_inst_rep">Inst. Reporting</label></div></div>
                                            <div class="col-md-12"><label class="form-label">Institution Confirmation Process</label><textarea name="institution_confirmation_process_summary" class="form-control" rows="2">{{ old('institution_confirmation_process_summary', $organisation->institution_confirmation_process_summary) }}</textarea></div>
                                        </div>
                                    </div>

                                    <!-- 10. GRIEVANCE -->
                                    <div class="tab-pane fade" id="rb-grievance" role="tabpanel">
                                        <div class="row g-3">
                                            <div class="col-md-12"><label class="form-label">Grievance Mechanism</label><textarea name="grievance_redressal_mechanism" class="form-control" rows="2">{{ old('grievance_redressal_mechanism', $organisation->grievance_redressal_mechanism) }}</textarea></div>
                                            <div class="col-md-12"><label class="form-label">Appeal Process</label><textarea name="appeal_process_summary" class="form-control" rows="2">{{ old('appeal_process_summary', $organisation->appeal_process_summary) }}</textarea></div>
                                            <div class="col-md-3"><div class="form-check form-switch mt-4"><input class="form-check-input" type="checkbox" name="rti_applicable" id="rb_rti" {{ old('rti_applicable', $organisation->rti_applicable) ? 'checked' : '' }}><label class="form-check-label" for="rb_rti">RTI App.</label></div></div>
                                        </div>
                                    </div>

                                    <!-- 11. SUPPORT -->
                                    <div class="tab-pane fade" id="rb-support" role="tabpanel">
                                        <div class="row g-3">
                                            <div class="col-md-6"><label class="form-label">Support URL</label><input type="url" name="candidate_support_url" class="form-control" value="{{ old('candidate_support_url', $organisation->candidate_support_url) }}"></div>
                                            <div class="col-md-6"><label class="form-label">Handbook URL</label><input type="url" name="candidate_handbook_url" class="form-control" value="{{ old('candidate_handbook_url', $organisation->candidate_handbook_url) }}"></div>
                                            <div class="col-md-12"><label class="form-label">Media Mentions</label><input type="text" name="media_mentions" class="form-control" value="{{ implode(',', is_array($organisation->media_mentions) ? $organisation->media_mentions : []) }}" placeholder="Comma separated"></div>
                                        </div>
                                    </div>

                                    <!-- 12. SCALE & IMPACT -->
                                    <div class="tab-pane fade" id="rb-history" role="tabpanel">
                                        <div class="row g-3">
                                            <div class="col-md-6"><label class="form-label">Years of Operation</label><input type="number" name="years_of_operation" class="form-control" value="{{ old('years_of_operation', $organisation->years_of_operation) }}"></div>
                                            <div class="col-md-6"><label class="form-label">Annual Candidate Volume</label><input type="text" name="annual_candidate_volume" class="form-control" value="{{ old('annual_candidate_volume', $organisation->annual_candidate_volume) }}"></div>
                                            <div class="col-md-4"><label class="form-label">Trust Rating</label><input type="number" step="0.1" name="public_trust_rating" class="form-control" value="{{ old('public_trust_rating', $organisation->public_trust_rating) }}"></div>
                                        </div>
                                    </div>

                                    <!-- 13. SEO -->
                                    <div class="tab-pane fade" id="rb-seo" role="tabpanel">
                                        <div class="row g-3">
                                            <div class="col-md-6"><label class="form-label">Meta Title</label><input type="text" name="meta_title" class="form-control" value="{{ old('meta_title', $organisation->meta_title) }}"></div>
                                            <div class="col-md-6"><label class="form-label">Canonical URL</label><input type="url" name="canonical_url" class="form-control" value="{{ old('canonical_url', $organisation->canonical_url) }}"></div>
                                            <div class="col-md-12"><label class="form-label">Meta Description</label><textarea name="meta_description" class="form-control" rows="2">{{ old('meta_description', $organisation->meta_description) }}</textarea></div>
                                        </div>
                                    </div>

                                    <!-- 14. ADMIN -->
                                    <div class="tab-pane fade" id="rb-admin" role="tabpanel">
                                        <div class="row g-3">
                                            <div class="col-md-6"><label class="form-label">Verification Status</label><select name="verification_status" class="form-select">
                                                    @foreach(['Pending', 'Verified', 'Rejected'] as $opt)
                                                        <option value="{{ $opt }}" {{ old('verification_status', $organisation->verification_status) == $opt ? 'selected' : '' }}>{{ $opt }}</option>
                                                    @endforeach
                                                </select></div>
                                            <div class="col-md-3"><div class="form-check form-switch mt-4"><input class="form-check-input" type="checkbox" name="claimed_by_authority" id="rb_claimed" {{ old('claimed_by_authority', $organisation->claimed_by_authority) ? 'checked' : '' }}><label class="form-check-label" for="rb_claimed">Claimed</label></div></div>
                                            <div class="col-md-3"><label class="form-label">Status</label><select name="status" class="form-select">
                                                    @foreach(['Active', 'Inactive', 'Archived'] as $opt)
                                                        <option value="{{ $opt }}" {{ old('status', $organisation->status) == $opt ? 'selected' : '' }}>{{ $opt }}</option>
                                                    @endforeach
                                                </select></div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="col-12 mt-4 text-end">
                                <button type="submit" class="btn btn-primary">Update Organisation</button>
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
    document.addEventListener('DOMContentLoaded', function() {
        initializeTinyMCE('.editor');
        console.log("Edit Page: JS Loaded");
        
        // ------------------------------------------------------------------
        // DOM Elements
        // ------------------------------------------------------------------
        const typeSelect = document.getElementById('organisation_type_id');
        const uniFields = document.getElementById('university-fields');
        const instFields = document.getElementById('institute-fields');
        const schoolFields = document.getElementById('school-fields-container');
        
        const orgIdInput = document.getElementById('organisation_id');
        const statusDiv = document.getElementById('autosave-status');
        const container = document.getElementById('inst-core-values-container');
        const autosaveRouteTemplate = "{{ route('admin.organisations.autosave', ':id') }}";
        

        // ------------------------------------------------------------------
        // Helper: Status Display
        // ------------------------------------------------------------------
        function showStatus(msg, type = 'info') {
            if (statusDiv) {
                statusDiv.innerHTML = `<span class="badge bg-${type}">${msg}</span>`;
                setTimeout(() => statusDiv.innerHTML = '', 3000);
            }
        }

        // ------------------------------------------------------------------
        // Feature 1: Toggle Fields Visibility (ID Based - Synced with Create)
        // ------------------------------------------------------------------
        function toggleFields() {
            if (!typeSelect) return;
            
            const val = typeSelect.value;
            console.log("Organisation Type Selected:", val);
            
            // Containers to toggle
            const containers = {
                'university-fields': [1, 2],
                'institute-fields': [3],
                'school-fields': [4],
                'exam-conducting-body-fields': [5],
                'counselling-body-fields': [6],
                'regulatory-body-fields': [7]
            };

            // Hide all first and show selected
            Object.keys(containers).forEach(id => {
                const el = document.getElementById(id);
                if (el) {
                    const isVisible = containers[id].includes(parseInt(val));
                    el.style.display = isVisible ? 'block' : 'none';
                    console.log(`Setting ${id} display to: ${el.style.display}`);
                }
            });
        }

        if (typeSelect) {
            typeSelect.addEventListener('change', toggleFields);
            // Run once on load
            toggleFields();
        }

        // Feature: Counselling/Regulatory Body Sync & Jurisdiction
        const cb_name = document.getElementById('cb_name_sync');
        const rb_name = document.getElementById('rb_name_sync');
        const cb_short = document.getElementById('cb_short_sync');
        const rb_short = document.getElementById('rb_short_sync');
        const cb_est = document.getElementById('cb_est_sync');
        const rb_est = document.getElementById('rb_est_sync');
        const main_name = document.querySelector('input[name="name"]');
        const main_type = document.getElementById('organisation_type_id');
        const cb_juris = document.getElementById('cb_jurisdiction');
        const cb_juris_div = document.getElementById('cb_juris_states_div');
        const rb_juris = document.getElementById('rb_jurisdiction');
        const rb_juris_div = document.getElementById('rb_juris_states_div');

        if(main_name) {
            main_name.addEventListener('input', () => {
                if(main_type.value == '6' && cb_name) cb_name.value = main_name.value;
                if(main_type.value == '7' && rb_name) rb_name.value = main_name.value;
            });
            if(cb_name) cb_name.addEventListener('input', () => { if(main_type.value == '6') main_name.value = cb_name.value; });
            if(rb_name) rb_name.addEventListener('input', () => { if(main_type.value == '7') main_name.value = rb_name.value; });
        }
        if(cb_juris) {
            cb_juris.addEventListener('change', () => {
                cb_juris_div.style.display = cb_juris.value === 'State' ? 'block' : 'none';
            });
        }
        if(rb_juris) {
            rb_juris.addEventListener('change', () => {
                rb_juris_div.style.display = rb_juris.value === 'State' ? 'block' : 'none';
            });
        }

        // Feature 2: Core Values Repeater
        // ------------------------------------------------------------------
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
                    // If there's an 'Add More' button at the bottom, insert before it
                    const addMoreBtn = cv_container.querySelector('.btn.add-core-value');
                    if (addMoreBtn && addMoreBtn !== addBtn) {
                        cv_container.insertBefore(group, addMoreBtn);
                    } else {
                        // If addBtn itself is the 'Add More' button at the bottom
                        if (addBtn.classList.contains('mt-1')) {
                            cv_container.insertBefore(group, addBtn);
                        } else {
                            cv_container.appendChild(group);
                        }
                    }
                    // Trigger Auto-save
                    const emptyInput = group.querySelector('input');
                    if (emptyInput) emptyInput.dispatchEvent(new Event('change', {
                        bubbles: true
                    }));
                }

                if (removeBtn) {
                    const items = cv_container.querySelectorAll('.input-group');
                    const inputToClear = removeBtn.previousElementSibling;
                    if (items.length > 1) {
                        removeBtn.closest('.input-group').remove();
                    } else {
                        if (inputToClear) inputToClear.value = '';
                    }
                    // Trigger Auto-save on the remaining inputs or the parent
                    cv_container.dispatchEvent(new Event('change', {
                        bubbles: true
                    }));
                }
            });
        });

        // ------------------------------------------------------------------
        // Feature: File Preview & Repeater Logic
        // ------------------------------------------------------------------
        const previewStyles = `
            <style>
                .file-preview img { max-height: 150px; border-radius: 8px; box-shadow: 0 2px 5px rgba(0,0,0,0.1); }
                .file-preview .file-icon { font-size: 2rem; color: #6c757d; }
                .file-preview-item { display: inline-block; position: relative; margin-right: 10px; margin-bottom: 10px; }
                .file-repeater-item { border: 1px dashed #dee2e6; padding: 15px; border-radius: 8px; background: #f8f9fa; }
                .existing-file { border-style: solid; border-color: #e9ecef; }
            </style>
        `;
        document.head.insertAdjacentHTML('beforeend', previewStyles);

        function handleFilePreview(input) {
            const container = input.closest('.file-repeater-item') || input.parentElement;
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
                    reader.onload = function(e) {
                        previewDiv.innerHTML = `<img src="${e.target.result}" class="img-thumbnail mt-2" style="height: 100px;">`;
                    }
                    reader.readAsDataURL(file);
                } else {
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
        document.addEventListener('change', function(e) {
            if (e.target.classList.contains('file-preview-input')) {
                handleFilePreview(e.target);
                
                // If it's a new file in a repeater, ensure the "remove" button is visible
                const removeBtn = e.target.nextElementSibling;
                if (removeBtn && removeBtn.classList.contains('remove-file-item')) {
                    removeBtn.style.display = 'block';
                }
            }
        });

        // File Repeater Logic
        document.querySelectorAll('.file-repeater-container').forEach(container => {
            container.addEventListener('click', function(e) {
                const addBtn = e.target.closest('.add-file-item');
                const removeBtn = e.target.closest('.remove-file-item');
                const removeExistingBtn = e.target.closest('.remove-existing-file');

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

                if (removeExistingBtn) {
                    if (confirm('Are you sure you want to remove this existing document? This will take effect upon saving.')) {
                        removeExistingBtn.closest('.file-repeater-item').remove();
                    }
                }
            });
        });

        // ------------------------------------------------------------------
        // Feature 3: Auto-save Logic
        // ------------------------------------------------------------------
        const inputs = document.querySelectorAll('input:not([type=hidden]), select, textarea');
        
        inputs.forEach(input => {
            input.addEventListener('change', function(e) {
                // If this is the type select, ensure toggle runs (it has its own listener, but safe to verify)
                if (this.id === 'organisation_type_id') {
                    toggleFields();
                }

                const orgId = orgIdInput ? orgIdInput.value : null;
                if (!orgId) return;

                // Prepare Data
                let fieldName = this.name.replace('[]', '');
                let value = this.value;
                let isFile = (this.type === 'file');

                // Determine payload
                let bodyData;
                let headers = { 'X-CSRF-TOKEN': "{{ csrf_token() }}" };

                if (isFile) {
                    const formData = new FormData();
                    formData.append('field', fieldName);
                    if (this.files.length > 0) {
                        formData.append(fieldName, this.files[0]);
                    }
                    bodyData = formData;
                    // Don't set Content-Type for FormData, browser does it with boundary
                } else {
                    headers['Content-Type'] = 'application/json';
                    
                    // Special Handling for Checkboxes
                    if (this.type === 'checkbox') {
                         if (this.name.includes('[]')) {
                             // Array Checkbox: Collect ALL checked inputs of this name
                             const allChecked = document.querySelectorAll(`input[name="${this.name}"]:checked`);
                             const values = Array.from(allChecked).map(cb => cb.value);
                             value = values.join(',');
                         } else {
                             // Boolean Toggle
                             value = this.checked ? 1 : 0;
                         }
                    } else if (this.name.includes('[]') && this.type === 'text') {
                        // Array Text: Collect ALL inputs of this name
                        const allInputs = document.querySelectorAll(`input[name="${this.name}"]`);
                        value = Array.from(allInputs).map(i => i.value).filter(v => v.trim() !== '');
                    }
                    
                    bodyData = JSON.stringify({
                        field: fieldName,
                        value: value
                    });
                }

                // UI Feedback
                showStatus(isFile ? 'Uploading...' : 'Saving...', 'info');

                // API Call
                fetch(autosaveRouteTemplate.replace(':id', orgId), {
                    method: 'POST',
                    headers: headers,
                    body: bodyData
                })
                .then(res => res.json())
                .then(data => {
                    if (data.status === 'success') {
                        showStatus('Saved', 'success');
                    } else {
                        showStatus('Error', 'danger');
                    }
                })
                .catch(err => {
                    console.error("Autosave Error:", err);
                    showStatus('Error', 'danger');
                });
            });
        });
    });
</script>


@endpush
@endsection

@extends('admin.layouts.master')

@section('title', 'Add New Scholarship')

@push('css')
<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
<style>
    .select2-container { width: 100% !important; }
    .select2-container--default .select2-selection--multiple {
        border: 1px solid #ced4da;
        border-radius: 0.375rem;
        min-height: 38px;
        padding: 3px 6px;
    }
    .select2-container--default.select2-container--focus .select2-selection--multiple {
        border-color: #86b7fe;
        outline: 0;
        box-shadow: 0 0 0 0.25rem rgba(13, 110, 253, 0.25);
    }
    .select2-container--default .select2-selection--multiple .select2-selection__choice {
        background-color: #e0e7ff;
        border: 1px solid #c7d2fe;
        color: #3730a3;
        border-radius: 4px;
        padding: 2px 8px;
        font-size: 0.82rem;
        font-weight: 600;
    }
    .select2-container--default .select2-selection--multiple .select2-selection__choice__remove {
        color: #4338ca;
        margin-right: 5px;
        font-weight: bold;
    }
</style>
@endpush

@section('content')
<div class="mb-4 d-flex justify-content-between align-items-center">
    <a href="{{ route('admin.scholarships.index') }}" class="btn btn-outline-secondary">
        <i class="fas fa-arrow-left"></i> Back to List
    </a>
    <span id="autosave-status" class="text-muted small"></span>
</div>

<div class="card border-0 shadow-sm">
    <div class="card-body p-4">
        <form action="{{ route('admin.scholarships.store') }}" method="POST" enctype="multipart/form-data" id="scholarship-form">
            @csrf

            <!-- Tabs Navigation -->
            <ul class="nav nav-tabs mb-4 flex-wrap" id="scholarshipTabs" role="tablist">
                <li class="nav-item" role="presentation">
                    <button class="nav-link active fw-bold" data-bs-toggle="tab" data-bs-target="#basic" type="button">Basic Info</button>
                </li>
                <li class="nav-item" role="presentation">
                    <button class="nav-link fw-bold" data-bs-toggle="tab" data-bs-target="#homepage-card" type="button">Homepage Card</button>
                </li>
                <li class="nav-item" role="presentation">
                    <button class="nav-link fw-bold" data-bs-toggle="tab" data-bs-target="#banner" type="button">Banner</button>
                </li>
                <li class="nav-item" role="presentation">
                    <button class="nav-link fw-bold" data-bs-toggle="tab" data-bs-target="#description" type="button">Description</button>
                </li>
                <li class="nav-item" role="presentation">
                    <button class="nav-link fw-bold" data-bs-toggle="tab" data-bs-target="#eligibility" type="button">Eligibility</button>
                </li>
                <li class="nav-item" role="presentation">
                    <button class="nav-link fw-bold" data-bs-toggle="tab" data-bs-target="#highlights" type="button">Highlights</button>
                </li>
                <li class="nav-item" role="presentation">
                    <button class="nav-link fw-bold" data-bs-toggle="tab" data-bs-target="#benefits" type="button">Benefits</button>
                </li>
                <li class="nav-item" role="presentation">
                    <button class="nav-link fw-bold" data-bs-toggle="tab" data-bs-target="#dates" type="button">Key Dates</button>
                </li>
                <li class="nav-item" role="presentation">
                    <button class="nav-link fw-bold" data-bs-toggle="tab" data-bs-target="#documents" type="button">Documents</button>
                </li>
                <li class="nav-item" role="presentation">
                    <button class="nav-link fw-bold" data-bs-toggle="tab" data-bs-target="#courses" type="button">Courses</button>
                </li>
                <li class="nav-item" role="presentation">
                    <button class="nav-link fw-bold" data-bs-toggle="tab" data-bs-target="#universities" type="button">Universities</button>
                </li>
                <li class="nav-item" role="presentation">
                    <button class="nav-link fw-bold" data-bs-toggle="tab" data-bs-target="#faqs" type="button">FAQs</button>
                </li>
                <li class="nav-item" role="presentation">
                    <button class="nav-link fw-bold" data-bs-toggle="tab" data-bs-target="#gallery" type="button">Gallery</button>
                </li>
                <li class="nav-item" role="presentation">
                    <button class="nav-link fw-bold" data-bs-toggle="tab" data-bs-target="#seo" type="button"><i class="fas fa-search me-1"></i>SEO</button>
                </li>
                <li class="nav-item" role="presentation">
                    <button class="nav-link fw-bold" data-bs-toggle="tab" data-bs-target="#advanced" type="button"><i class="fas fa-cog me-1"></i>Advanced</button>
                </li>
            </ul>

            <div class="tab-content" id="scholarshipTabsContent">

                <!-- ─── Tab 1: Basic Info ─────────────────────────────────── -->
                <div class="tab-pane fade show active" id="basic" role="tabpanel">
                    <div class="row g-3">
                        <div class="col-md-8">
                            <label class="form-label fw-bold">Scholarship Title <span class="text-danger">*</span></label>
                            <input type="text" name="title" id="title" class="form-control" required placeholder="e.g. Merit Based Scholarship for IT Students">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw-bold">Short Name</label>
                            <input type="text" name="short_name" class="form-control" placeholder="e.g. Merit IT 2026">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-bold">Slug <small class="text-muted">(auto-generated)</small></label>
                            <input type="text" name="slug" class="form-control" placeholder="Auto-generated from title">
                        </div>
                        <div class="col-md-3">
                            <label class="form-label fw-bold">Scholarship Code</label>
                            <input type="text" name="scholarship_code" class="form-control" placeholder="e.g. SCH-MIT-01">
                        </div>
                        <div class="col-md-3">
                            <label class="form-label fw-bold">Sort Order</label>
                            <input type="number" name="sort_order" class="form-control" value="0" required>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label fw-bold">Status</label>
                            <select name="status" class="form-select">
                                <option value="1">Active</option>
                                <option value="0">Draft / Inactive</option>
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label fw-bold">Featured</label>
                            <select name="featured" class="form-select">
                                <option value="0">No</option>
                                <option value="1">Yes</option>
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label fw-bold">Featured on Homepage</label>
                            <select name="featured_on_homepage" class="form-select">
                                <option value="0">No</option>
                                <option value="1">Yes</option>
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label fw-bold">Application Mode</label>
                            <select name="application_mode" class="form-select">
                                <option value="Online">Online</option>
                                <option value="Offline">Offline</option>
                                <option value="Hybrid">Hybrid</option>
                            </select>
                        </div>
                        <div class="col-12">
                            <label class="form-label fw-bold">Short Description <small class="text-muted">(120–180 characters — shown on cards & search)</small></label>
                            <textarea name="short_description" class="form-control" rows="2" placeholder="Brief summary for listing preview..."></textarea>
                        </div>
                    </div>
                </div>

                <!-- ─── Tab 2: Homepage Card ──────────────────────────────── -->
                <div class="tab-pane fade" id="homepage-card" role="tabpanel">
                    <p class="text-muted small mb-3"><i class="fas fa-info-circle me-1"></i> These fields control how the scholarship appears on the homepage carousel and listing cards.</p>
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label fw-bold">Display Title <small class="text-muted">(alternate homepage title)</small></label>
                            <input type="text" name="display_title" class="form-control" placeholder="e.g. Online PG Scholarships (shorter than full title)">
                        </div>
                        <div class="col-md-3">
                            <label class="form-label fw-bold">Scholarship Type <small class="text-muted">(badge)</small></label>
                            @php
                                $selectedTypes = (array) old('scholarship_type', []);
                            @endphp
                            <select name="scholarship_type[]" class="form-select select2-type" multiple="multiple" data-placeholder="Select Scholarship Types">
                                @foreach(['Merit' => 'Merit Based', 'Need Based' => 'Need Based', 'Sports' => 'Sports Based', 'Minority' => 'Minority Based', 'Government' => 'Government', 'Private' => 'Private / CSR', 'Corporate' => 'Corporate', 'International' => 'International'] as $val => $label)
                                    <option value="{{ $val }}" {{ in_array($val, $selectedTypes) ? 'selected' : '' }}>{{ $label }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label fw-bold">Category <small class="text-muted">(chip label)</small></label>
                            <input type="text" name="category" class="form-control" placeholder="e.g. Science / Tech">
                        </div>
                        <div class="col-md-3">
                            <label class="form-label fw-bold">Reward Amount (Max INR)</label>
                            <input type="number" name="max_amount" class="form-control" placeholder="e.g. 50000">
                        </div>
                        <div class="col-md-3">
                            <label class="form-label fw-bold">Amount Prefix</label>
                            <input type="text" name="amount_prefix" class="form-control" value="Upto INR" placeholder="e.g. Upto INR">
                        </div>
                        <div class="col-md-3">
                            <label class="form-label fw-bold">Amount Suffix</label>
                            <input type="text" name="amount_suffix" class="form-control" placeholder="e.g. Only / Per Year">
                        </div>
                        <div class="col-md-3">
                            <label class="form-label fw-bold">Card Icon <small class="text-muted">(FontAwesome class)</small></label>
                            <input type="text" name="card_icon" class="form-control" placeholder="e.g. fas fa-graduation-cap">
                        </div>
                        <div class="col-md-3">
                            <label class="form-label fw-bold">Card Background Color</label>
                            <div class="input-group">
                                <input type="color" name="card_background_color" class="form-control form-control-color" value="#1a73e8" style="max-width:50px;">
                                <input type="text" id="card_bg_hex" class="form-control" value="#1a73e8" placeholder="#1a73e8">
                            </div>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label fw-bold">Card Text Color</label>
                            <div class="input-group">
                                <input type="color" name="card_text_color" class="form-control form-control-color" value="#ffffff" style="max-width:50px;">
                                <input type="text" id="card_text_hex" class="form-control" value="#ffffff" placeholder="#ffffff">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-bold">Featured Image <small class="text-muted">(card preview)</small></label>
                            <input type="file" name="featured_image" class="form-control" accept="image/*">
                        </div>
                        <div class="col-md-3">
                            <label class="form-label fw-bold">CTA Button Text</label>
                            <input type="text" name="cta_text" class="form-control" value="Check Eligibility">
                        </div>
                        <div class="col-md-3">
                            <label class="form-label fw-bold">CTA Link URL</label>
                            <input type="text" name="cta_url" class="form-control" placeholder="e.g. https://provider.com/apply">
                        </div>
                    </div>
                </div>

                <!-- ─── Tab 3: Banner ─────────────────────────────────────── -->
                <div class="tab-pane fade" id="banner" role="tabpanel">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label fw-bold">Banner Title <small class="text-muted">(detail page heading)</small></label>
                            <input type="text" name="banner_title" class="form-control" placeholder="e.g. Merit Scholarship 2026 — Apply Now">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-bold">Banner Subtitle</label>
                            <input type="text" name="banner_subtitle" class="form-control" placeholder="e.g. For Science & Tech Students | Upto ₹50,000">
                        </div>
                        <div class="col-12">
                            <label class="form-label fw-bold">Banner Image <small class="text-muted">(detail page full-width banner)</small></label>
                            <input type="file" name="banner_image" class="form-control" accept="image/*">
                            <small class="text-muted">Recommended: 1400×400px</small>
                        </div>
                        <hr>
                        <div class="col-md-4">
                            <label class="form-label fw-bold">Provider Name</label>
                            <input type="text" name="provider_name" class="form-control" placeholder="e.g. Uniband8 Foundation">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw-bold">Provider Logo</label>
                            <input type="file" name="provider_logo" class="form-control" accept="image/*">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw-bold">Provider Website URL</label>
                            <input type="url" name="provider_url" class="form-control" placeholder="e.g. https://provider.com">
                        </div>
                    </div>
                </div>

                <!-- ─── Tab 4: Description ────────────────────────────────── -->
                <div class="tab-pane fade" id="description" role="tabpanel">
                    <div class="row g-3">
                        <div class="col-12">
                            <label class="form-label fw-bold">Overview / Summary</label>
                            <textarea name="overview" class="form-control editor" rows="4"></textarea>
                        </div>
                        <div class="col-12">
                            <label class="form-label fw-bold">About Scholarship</label>
                            <textarea name="about_scholarship" class="form-control editor" rows="4"></textarea>
                        </div>
                        <div class="col-12">
                            <label class="form-label fw-bold">Why Apply?</label>
                            <textarea name="why_apply" class="form-control editor" rows="4"></textarea>
                        </div>
                        <div class="col-12">
                            <label class="form-label fw-bold">Selection Process</label>
                            <textarea name="selection_process" class="form-control editor" rows="4"></textarea>
                        </div>
                        <div class="col-12">
                            <label class="form-label fw-bold">Terms & Conditions</label>
                            <textarea name="terms_conditions" class="form-control editor" rows="4"></textarea>
                        </div>
                        <div class="col-12">
                            <label class="form-label fw-bold">Important Notes</label>
                            <textarea name="important_notes" class="form-control editor" rows="3"></textarea>
                        </div>
                        <div class="col-12">
                            <label class="form-label fw-bold">Additional Information</label>
                            <textarea name="additional_information" class="form-control editor" rows="3"></textarea>
                        </div>
                    </div>
                </div>

                <!-- ─── Tab 5: Eligibility ────────────────────────────────── -->
                <div class="tab-pane fade" id="eligibility" role="tabpanel">
                    <div class="row g-3">
                        <div class="col-md-4">
                            <label class="form-label fw-bold">Minimum Class / Standard</label>
                            <input type="text" name="eligibility[minimum_class]" class="form-control" placeholder="e.g. Class 10 or Undergraduate">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw-bold">Maximum Class / Standard</label>
                            <input type="text" name="eligibility[maximum_class]" class="form-control" placeholder="e.g. Post Graduate">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw-bold">Minimum Percentage (%)</label>
                            <input type="number" name="eligibility[minimum_percentage]" class="form-control" step="0.01" placeholder="e.g. 60.00">
                        </div>
                        <div class="col-md-3">
                            <label class="form-label fw-bold">Maximum Age</label>
                            <input type="number" name="eligibility[maximum_age]" class="form-control" placeholder="e.g. 25">
                        </div>
                        <div class="col-md-3">
                            <label class="form-label fw-bold">Gender</label>
                            <select name="eligibility[gender]" class="form-select">
                                <option value="Any">Any / All Genders</option>
                                <option value="Male">Male</option>
                                <option value="Female">Female</option>
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label fw-bold">Category (Caste)</label>
                            <input type="text" name="eligibility[category]" class="form-control" placeholder="e.g. General / SC / ST / OBC">
                        </div>
                        <div class="col-md-3">
                            <label class="form-label fw-bold">Annual Family Income</label>
                            <input type="text" name="eligibility[annual_family_income]" class="form-control" placeholder="e.g. Below 8 Lakhs">
                        </div>
                        <div class="col-md-3">
                            <label class="form-label fw-bold">Academic Stream</label>
                            <input type="text" name="eligibility[academic_stream]" class="form-control" placeholder="e.g. Engineering / Science">
                        </div>
                        <div class="col-md-3">
                            <label class="form-label fw-bold">Required Entrance Exam</label>
                            <input type="text" name="eligibility[entrance_exam]" class="form-control" placeholder="e.g. JEE / NEET / CAT">
                        </div>
                        <div class="col-md-3">
                            <label class="form-label fw-bold">Min Exam Score / Rank</label>
                            <input type="number" name="eligibility[minimum_exam_score]" class="form-control" step="0.01" placeholder="e.g. 95.00">
                        </div>
                        <div class="col-md-3">
                            <label class="form-label fw-bold">Currently Studying</label>
                            <input type="text" name="eligibility[currently_studying]" class="form-control" placeholder="e.g. B.Tech 1st Year">
                        </div>
                        <div class="col-md-3">
                            <label class="form-label fw-bold">Nationality</label>
                            <input type="text" name="eligibility[nationality]" class="form-control" value="Indian">
                        </div>
                        <div class="col-md-3">
                            <label class="form-label fw-bold">State Restriction</label>
                            <input type="text" name="eligibility[state]" class="form-control" placeholder="e.g. Uttarakhand or Punjab">
                        </div>
                        <div class="col-md-3">
                            <label class="form-label fw-bold">City Restriction</label>
                            <input type="text" name="eligibility[city]" class="form-control" placeholder="e.g. Dehradun">
                        </div>
                        <div class="col-md-3">
                            <label class="form-label fw-bold">Work Experience</label>
                            <input type="text" name="eligibility[work_experience]" class="form-control" placeholder="e.g. Minimum 2 Years">
                        </div>
                        <div class="col-md-3 d-flex align-items-center mt-4">
                            <div class="form-check">
                                <input type="checkbox" name="eligibility[graduation_required]" value="1" class="form-check-input" id="grad_req">
                                <label class="form-check-label fw-bold" for="grad_req">Graduation Required</label>
                            </div>
                        </div>
                        <div class="col-12">
                            <label class="form-label fw-bold">Other Conditions / Notes</label>
                            <textarea name="eligibility[other_conditions]" class="form-control" rows="3" placeholder="Any additional criteria..."></textarea>
                        </div>
                    </div>
                </div>

                <!-- ─── Tab 6: Highlights ─────────────────────────────────── -->
                <div class="tab-pane fade" id="highlights" role="tabpanel">
                    <p class="text-muted small mb-3"><i class="fas fa-info-circle me-1"></i> Add highlight chips/badges shown on cards and detail page (e.g. "₹30,000 Assistance", "Merit Based", "No Entrance Exam").</p>
                    <table class="table table-bordered align-middle" id="highlights-table">
                        <thead class="table-light">
                            <tr>
                                <th>Highlight Text <span class="text-danger">*</span></th>
                                <th style="width:220px">Icon <small class="text-muted">(FontAwesome)</small></th>
                                <th style="width:120px">Sort Order</th>
                                <th style="width:80px" class="text-center">Action</th>
                            </tr>
                        </thead>
                        <tbody id="highlights-rows">
                            <tr class="highlight-row">
                                <td><input type="text" name="highlights[0][highlight_text]" class="form-control" placeholder="e.g. ₹30,000 Assistance"></td>
                                <td><input type="text" name="highlights[0][highlight_icon]" class="form-control" placeholder="fas fa-rupee-sign"></td>
                                <td><input type="number" name="highlights[0][sort_order]" class="form-control" value="0"></td>
                                <td class="text-center"><button type="button" class="btn btn-outline-danger btn-sm remove-row-btn"><i class="fas fa-trash"></i></button></td>
                            </tr>
                        </tbody>
                    </table>
                    <button type="button" class="btn btn-success btn-sm mt-2" id="add-highlight-btn">
                        <i class="fas fa-plus"></i> Add Highlight
                    </button>
                </div>

                <!-- ─── Tab 7: Benefits ───────────────────────────────────── -->
                <div class="tab-pane fade" id="benefits" role="tabpanel">
                    <p class="text-muted small mb-3">Configure reward breakups (e.g. Tuition fee waiver, Monthly allowance, Hostel support etc.)</p>
                    <table class="table table-bordered align-middle" id="benefits-table">
                        <thead class="table-light">
                            <tr>
                                <th>Benefit Title <span class="text-danger">*</span></th>
                                <th style="width:200px">Amount (INR)</th>
                                <th>Description</th>
                                <th style="width:120px">Sort Order</th>
                                <th style="width:80px" class="text-center">Action</th>
                            </tr>
                        </thead>
                        <tbody id="benefits-rows">
                            <tr class="benefit-row">
                                <td><input type="text" name="benefits[0][benefit_title]" class="form-control" placeholder="e.g. Tuition Fee Waiver"></td>
                                <td><input type="number" name="benefits[0][benefit_amount]" class="form-control" placeholder="e.g. 30000"></td>
                                <td><input type="text" name="benefits[0][benefit_description]" class="form-control" placeholder="e.g. Covers 50% tuition"></td>
                                <td><input type="number" name="benefits[0][sort_order]" class="form-control" value="0"></td>
                                <td class="text-center"><button type="button" class="btn btn-outline-danger btn-sm remove-row-btn"><i class="fas fa-trash"></i></button></td>
                            </tr>
                        </tbody>
                    </table>
                    <button type="button" class="btn btn-success btn-sm mt-2" id="add-benefit-btn">
                        <i class="fas fa-plus"></i> Add Benefit
                    </button>
                </div>

                <!-- ─── Tab 8: Key Dates ──────────────────────────────────── -->
                <div class="tab-pane fade" id="dates" role="tabpanel">
                    <div class="row g-3">
                        <div class="col-md-4">
                            <label class="form-label fw-bold">Application Start Date</label>
                            <input type="date" name="dates[application_start_date]" class="form-control">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw-bold">Application Deadline</label>
                            <input type="date" name="dates[application_end_date]" class="form-control">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw-bold">Exam Date (If any)</label>
                            <input type="date" name="dates[exam_date]" class="form-control">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw-bold">Result Announcement Date</label>
                            <input type="date" name="dates[result_date]" class="form-control">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw-bold">Document Verification Date</label>
                            <input type="date" name="dates[document_verification_date]" class="form-control">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw-bold">Admission Date</label>
                            <input type="date" name="dates[admission_date]" class="form-control">
                        </div>
                    </div>
                </div>

                <!-- ─── Tab 9: Required Documents ─────────────────────────── -->
                <div class="tab-pane fade" id="documents" role="tabpanel">
                    <table class="table table-bordered align-middle" id="documents-table">
                        <thead class="table-light">
                            <tr>
                                <th>Document Name <span class="text-danger">*</span></th>
                                <th style="width:200px" class="text-center">Is Mandatory?</th>
                                <th style="width:80px" class="text-center">Action</th>
                            </tr>
                        </thead>
                        <tbody id="documents-rows">
                            <tr class="document-row">
                                <td><input type="text" name="documents[0][document_name]" class="form-control" placeholder="e.g. Aadhar Card"></td>
                                <td class="text-center"><input type="checkbox" name="documents[0][is_mandatory]" value="1" checked class="form-check-input"></td>
                                <td class="text-center"><button type="button" class="btn btn-outline-danger btn-sm remove-row-btn"><i class="fas fa-trash"></i></button></td>
                            </tr>
                        </tbody>
                    </table>
                    <button type="button" class="btn btn-success btn-sm mt-2" id="add-document-btn">
                        <i class="fas fa-plus"></i> Add Document
                    </button>
                </div>

                <!-- ─── Tab 10: Courses ───────────────────────────────────── -->
                <div class="tab-pane fade" id="courses" role="tabpanel">
                    <label class="form-label fw-bold">Applicable Courses</label>
                    <select name="courses[]" class="form-select" multiple style="height:300px;">
                        @foreach($courses as $course)
                            <option value="{{ $course->id }}">{{ $course->name }}</option>
                        @endforeach
                    </select>
                    <small class="text-muted">Hold Ctrl / Cmd to select multiple.</small>
                </div>

                <!-- ─── Tab 11: Universities ──────────────────────────────── -->
                <div class="tab-pane fade" id="universities" role="tabpanel">
                    <label class="form-label fw-bold">Applicable Universities / Institutes</label>
                    <select name="universities[]" class="form-select" multiple style="height:300px;">
                        @foreach($organisations as $org)
                            <option value="{{ $org->id }}">{{ $org->name }}</option>
                        @endforeach
                    </select>
                    <small class="text-muted">Hold Ctrl / Cmd to select multiple.</small>
                </div>

                <!-- ─── Tab 12: FAQs ─────────────────────────────────────── -->
                <div class="tab-pane fade" id="faqs" role="tabpanel">
                    <div id="faqs-container">
                        <div class="faq-row card border shadow-sm p-3 mb-3">
                            <div class="row g-3">
                                <div class="col-md-6">
                                    <label class="form-label fw-bold">Question</label>
                                    <input type="text" name="faqs[0][question]" class="form-control" placeholder="e.g. Who can apply?">
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label fw-bold">Sort Order</label>
                                    <input type="number" name="faqs[0][sort_order]" class="form-control" value="0">
                                </div>
                                <div class="col-md-2 d-flex align-items-end justify-content-end">
                                    <button type="button" class="btn btn-outline-danger w-100 remove-faq-btn"><i class="fas fa-trash"></i> Remove</button>
                                </div>
                                <div class="col-12">
                                    <label class="form-label fw-bold">Answer</label>
                                    <textarea name="faqs[0][answer]" class="form-control" rows="2" placeholder="Provide answer details..."></textarea>
                                </div>
                            </div>
                        </div>
                    </div>
                    <button type="button" class="btn btn-success btn-sm" id="add-faq-btn">
                        <i class="fas fa-plus"></i> Add FAQ
                    </button>
                </div>

                <!-- ─── Tab 13: Gallery ──────────────────────────────────── -->
                <div class="tab-pane fade" id="gallery" role="tabpanel">
                    <div class="col-12">
                        <label class="form-label fw-bold">Upload Gallery Images</label>
                        <input type="file" name="gallery_images[]" class="form-control" multiple accept="image/*">
                        <small class="text-muted">Select multiple images at once.</small>
                    </div>
                </div>

                <!-- ─── Tab 14: SEO ──────────────────────────────────────── -->
                <div class="tab-pane fade" id="seo" role="tabpanel">
                    <div class="row g-3">
                        <div class="col-md-8">
                            <label class="form-label fw-bold">Meta Title</label>
                            <input type="text" name="seo[meta_title]" class="form-control" placeholder="e.g. Merit Scholarship 2026 | Enrollzy">
                            <small class="text-muted">Recommended: 50–60 characters</small>
                        </div>
                        <div class="col-md-4 d-flex gap-4 align-items-center pt-4">
                            <div class="form-check">
                                <input type="checkbox" name="seo[no_index]" value="1" class="form-check-input" id="no_index">
                                <label class="form-check-label" for="no_index">No Index</label>
                            </div>
                            <div class="form-check">
                                <input type="checkbox" name="seo[no_follow]" value="1" class="form-check-input" id="no_follow">
                                <label class="form-check-label" for="no_follow">No Follow</label>
                            </div>
                        </div>
                        <div class="col-12">
                            <label class="form-label fw-bold">Meta Description</label>
                            <textarea name="seo[meta_description]" class="form-control" rows="2" placeholder="160 character summary for search engines..."></textarea>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-bold">Meta Keywords</label>
                            <input type="text" name="seo[meta_keywords]" class="form-control" placeholder="e.g. scholarship, merit, 2026, engineering">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-bold">Canonical URL</label>
                            <input type="url" name="seo[canonical_url]" class="form-control" placeholder="https://enrollzy.com/scholarships/...">
                        </div>
                        <div class="col-12"><hr><p class="fw-bold mb-2">Open Graph (Social Media Preview)</p></div>
                        <div class="col-md-6">
                            <label class="form-label fw-bold">OG Title</label>
                            <input type="text" name="seo[og_title]" class="form-control" placeholder="Title for Facebook/Twitter share">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-bold">OG Image</label>
                            <input type="text" name="seo[og_image]" class="form-control" placeholder="https://... (URL of share image)">
                        </div>
                        <div class="col-12">
                            <label class="form-label fw-bold">OG Description</label>
                            <textarea name="seo[og_description]" class="form-control" rows="2" placeholder="Description for social media preview..."></textarea>
                        </div>
                    </div>
                </div>

                <!-- ─── Tab 15: Advanced Settings ─────────────────────────── -->
                <div class="tab-pane fade" id="advanced" role="tabpanel">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label fw-bold">Provider Website URL</label>
                            <input type="url" name="provider_url" class="form-control" placeholder="https://provider.com">
                        </div>
                        <div class="col-md-3">
                            <label class="form-label fw-bold">Sort Order</label>
                            <input type="number" name="sort_order" class="form-control" value="0">
                        </div>
                        <div class="col-12">
                            <div class="alert alert-info">
                                <i class="fas fa-info-circle me-1"></i>
                                Soft delete is enabled. When you delete a scholarship it goes to <strong>Trash</strong> and can be restored.
                            </div>
                        </div>
                    </div>
                </div>

            </div><!-- end tab-content -->

            <div class="mt-5 pt-3 border-top d-flex justify-content-between align-items-center">
                <span id="autosave-status-bottom" class="text-muted small"></span>
                <button type="submit" class="btn btn-primary px-5 py-2 fw-bold">
                    <i class="fas fa-save me-1"></i> Save Scholarship
                </button>
            </div>
        </form>
    </div>
</div>
@endsection

@section('scripts')
<script>
$(document).ready(function () {

    // Auto-generate slug from title
    $('#title').on('input', function () {
        let slug = $(this).val().toLowerCase()
            .replace(/[^a-z0-9\s-]/g, '')
            .trim().replace(/\s+/g, '-');
        $('input[name="slug"]').val(slug);
    });

    // Sync color picker with text input
    $('input[name="card_background_color"]').on('input', function () {
        $('#card_bg_hex').val($(this).val());
    });
    $('input[name="card_text_color"]').on('input', function () {
        $('#card_text_hex').val($(this).val());
    });
    $('#card_bg_hex').on('input', function () {
        $('input[name="card_background_color"]').val($(this).val());
    });
    $('#card_text_hex').on('input', function () {
        $('input[name="card_text_color"]').val($(this).val());
    });

    // ── Dynamic Highlights ─────────────────────────────────────
    let highlightCount = 1;
    $('#add-highlight-btn').on('click', function () {
        $('#highlights-rows').append(`
            <tr class="highlight-row">
                <td><input type="text" name="highlights[${highlightCount}][highlight_text]" class="form-control" placeholder="e.g. No Entrance Exam"></td>
                <td><input type="text" name="highlights[${highlightCount}][highlight_icon]" class="form-control" placeholder="fas fa-check"></td>
                <td><input type="number" name="highlights[${highlightCount}][sort_order]" class="form-control" value="0"></td>
                <td class="text-center"><button type="button" class="btn btn-outline-danger btn-sm remove-row-btn"><i class="fas fa-trash"></i></button></td>
            </tr>`);
        highlightCount++;
    });

    // ── Dynamic Benefits ───────────────────────────────────────
    let benefitCount = 1;
    $('#add-benefit-btn').on('click', function () {
        $('#benefits-rows').append(`
            <tr class="benefit-row">
                <td><input type="text" name="benefits[${benefitCount}][benefit_title]" class="form-control" placeholder="e.g. Tuition Fee Waiver"></td>
                <td><input type="number" name="benefits[${benefitCount}][benefit_amount]" class="form-control" placeholder="e.g. 30000"></td>
                <td><input type="text" name="benefits[${benefitCount}][benefit_description]" class="form-control" placeholder="e.g. Covers 50% tuition"></td>
                <td><input type="number" name="benefits[${benefitCount}][sort_order]" class="form-control" value="0"></td>
                <td class="text-center"><button type="button" class="btn btn-outline-danger btn-sm remove-row-btn"><i class="fas fa-trash"></i></button></td>
            </tr>`);
        benefitCount++;
    });

    // ── Dynamic Documents ──────────────────────────────────────
    let docCount = 1;
    $('#add-document-btn').on('click', function () {
        $('#documents-rows').append(`
            <tr class="document-row">
                <td><input type="text" name="documents[${docCount}][document_name]" class="form-control" placeholder="e.g. Aadhar Card"></td>
                <td class="text-center"><input type="checkbox" name="documents[${docCount}][is_mandatory]" value="1" checked class="form-check-input"></td>
                <td class="text-center"><button type="button" class="btn btn-outline-danger btn-sm remove-row-btn"><i class="fas fa-trash"></i></button></td>
            </tr>`);
        docCount++;
    });

    // ── Dynamic FAQs ───────────────────────────────────────────
    let faqCount = 1;
    $('#add-faq-btn').on('click', function () {
        $('#faqs-container').append(`
            <div class="faq-row card border shadow-sm p-3 mb-3">
                <div class="row g-3">
                    <div class="col-md-6">
                        <label class="form-label fw-bold">Question</label>
                        <input type="text" name="faqs[${faqCount}][question]" class="form-control" placeholder="e.g. Who can apply?">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label fw-bold">Sort Order</label>
                        <input type="number" name="faqs[${faqCount}][sort_order]" class="form-control" value="0">
                    </div>
                    <div class="col-md-2 d-flex align-items-end justify-content-end">
                        <button type="button" class="btn btn-outline-danger w-100 remove-faq-btn"><i class="fas fa-trash"></i> Remove</button>
                    </div>
                    <div class="col-12">
                        <label class="form-label fw-bold">Answer</label>
                        <textarea name="faqs[${faqCount}][answer]" class="form-control" rows="2"></textarea>
                    </div>
                </div>
            </div>`);
        faqCount++;
    });

    // ── Remove handlers ────────────────────────────────────────
    $(document).on('click', '.remove-row-btn', function () {
        $(this).closest('tr').remove();
    });
    $(document).on('click', '.remove-faq-btn', function () {
        if (confirm('Remove this FAQ?')) $(this).closest('.faq-row').remove();
    });

    // Initialize TinyMCE
    if (typeof initializeTinyMCE === 'function') {
        initializeTinyMCE('.editor', 250);
    }

    // ── AUTOSAVE ───────────────────────────────────────────────
    let autosaveScholarshipId = null;
    let autosaveTimer = null;
    const AUTOSAVE_URL      = '{{ route("admin.scholarships.autosave") }}';
    const AUTOSAVE_UPD_BASE = '{{ url("admin/scholarships") }}';
    const CSRF              = '{{ csrf_token() }}';

    $.ajaxSetup({
        headers: { 'X-CSRF-TOKEN': CSRF, 'X-Requested-With': 'XMLHttpRequest' }
    });

    function setStatus(msg, cls) {
        $('#autosave-status, #autosave-status-bottom')
            .removeClass('text-muted text-success text-danger').addClass(cls).text(msg);
    }

    function doAutosave() {
        // Don't trigger autosave until user has typed a title
        const titleVal = $('#scholarship-form input[name="title"]').val();
        if (!titleVal || titleVal.trim() === '') {
            setStatus('Title likhne ke baad autosave shuru hoga…', 'text-muted');
            return;
        }

        if (typeof tinymce !== 'undefined') tinymce.triggerSave();
        setStatus('Saving…', 'text-muted');

        const url = autosaveScholarshipId
            ? AUTOSAVE_UPD_BASE + '/' + autosaveScholarshipId + '/autosave'
            : AUTOSAVE_URL;

        $.ajax({
            url: url,
            method: 'POST',
            data: $('#scholarship-form').serialize(),
            success: function (res) {
                if (res.status === 'success') {
                    setStatus('Saved ✓ ' + new Date().toLocaleTimeString(), 'text-success');
                    if (res.scholarship_id && !autosaveScholarshipId) {
                        autosaveScholarshipId = res.scholarship_id;
                    }
                } else if (res.status === 'skipped') {
                    setStatus('Title likhne ke baad autosave shuru hoga…', 'text-muted');
                } else {
                    setStatus('Save failed', 'text-danger');
                }
            },
            error: function () { setStatus('Save failed', 'text-danger'); }
        });
    }

    // Initialize Select2 for multiple scholarship type badges
    $('.select2-type').select2({
        placeholder: 'Select Scholarship Types',
        allowClear: true,
        width: '100%'
    });

    $('button[data-bs-toggle="tab"]').on('shown.bs.tab', function () {
        $('.select2-type').select2({
            placeholder: 'Select Scholarship Types',
            allowClear: true,
            width: '100%'
        });
        clearTimeout(autosaveTimer);
        autosaveTimer = setTimeout(doAutosave, 600);
    });

    $(document).on('input change', '#scholarship-form input, #scholarship-form select, #scholarship-form textarea', function () {
        clearTimeout(autosaveTimer);
        autosaveTimer = setTimeout(doAutosave, 2000);
    });

});
</script>
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
@endsection

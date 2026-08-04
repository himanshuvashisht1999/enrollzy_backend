@extends('admin.layouts.master')

@section('title', 'Edit Scholarship — ' . $scholarship->title)

@section('content')
<div class="mb-4 d-flex justify-content-between align-items-center">
    <a href="{{ route('admin.scholarships.index') }}" class="btn btn-outline-secondary">
        <i class="fas fa-arrow-left"></i> Back to List
    </a>
    <span id="autosave-status" class="text-muted small"></span>
</div>

<div class="card border-0 shadow-sm">
    <div class="card-body p-4">
        <form action="{{ route('admin.scholarships.update', $scholarship->id) }}" method="POST" enctype="multipart/form-data" id="scholarship-form">
            @csrf
            @method('PUT')

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
                            <input type="text" name="title" id="title" class="form-control" required value="{{ old('title', $scholarship->title) }}">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw-bold">Short Name</label>
                            <input type="text" name="short_name" class="form-control" value="{{ old('short_name', $scholarship->short_name) }}" placeholder="e.g. Merit IT 2026">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-bold">Slug</label>
                            <input type="text" name="slug" class="form-control" value="{{ old('slug', $scholarship->slug) }}">
                        </div>
                        <div class="col-md-3">
                            <label class="form-label fw-bold">Scholarship Code</label>
                            <input type="text" name="scholarship_code" class="form-control" value="{{ old('scholarship_code', $scholarship->scholarship_code) }}" placeholder="e.g. SCH-MIT-01">
                        </div>
                        <div class="col-md-3">
                            <label class="form-label fw-bold">Sort Order</label>
                            <input type="number" name="sort_order" class="form-control" value="{{ old('sort_order', $scholarship->sort_order) }}" required>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label fw-bold">Status</label>
                            <select name="status" class="form-select">
                                <option value="1" {{ old('status', $scholarship->status) == 1 ? 'selected' : '' }}>Active</option>
                                <option value="0" {{ old('status', $scholarship->status) == 0 ? 'selected' : '' }}>Draft / Inactive</option>
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label fw-bold">Featured</label>
                            <select name="featured" class="form-select">
                                <option value="0" {{ old('featured', $scholarship->featured) == 0 ? 'selected' : '' }}>No</option>
                                <option value="1" {{ old('featured', $scholarship->featured) == 1 ? 'selected' : '' }}>Yes</option>
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label fw-bold">Featured on Homepage</label>
                            <select name="featured_on_homepage" class="form-select">
                                <option value="0" {{ old('featured_on_homepage', $scholarship->featured_on_homepage) == 0 ? 'selected' : '' }}>No</option>
                                <option value="1" {{ old('featured_on_homepage', $scholarship->featured_on_homepage) == 1 ? 'selected' : '' }}>Yes</option>
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label fw-bold">Application Mode</label>
                            <select name="application_mode" class="form-select">
                                @foreach(['Online', 'Offline', 'Hybrid'] as $mode)
                                    <option value="{{ $mode }}" {{ old('application_mode', $scholarship->application_mode) == $mode ? 'selected' : '' }}>{{ $mode }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-12">
                            <label class="form-label fw-bold">Short Description <small class="text-muted">(120–180 characters — shown on cards & search)</small></label>
                            <textarea name="short_description" class="form-control" rows="2">{{ old('short_description', $scholarship->short_description) }}</textarea>
                        </div>
                    </div>
                </div>

                <!-- ─── Tab 2: Homepage Card ──────────────────────────────── -->
                <div class="tab-pane fade" id="homepage-card" role="tabpanel">
                    <p class="text-muted small mb-3"><i class="fas fa-info-circle me-1"></i> These fields control how the scholarship appears on the homepage carousel and listing cards.</p>
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label fw-bold">Display Title <small class="text-muted">(alternate homepage title)</small></label>
                            <input type="text" name="display_title" class="form-control" value="{{ old('display_title', $scholarship->display_title) }}" placeholder="e.g. Online PG Scholarships">
                        </div>
                        <div class="col-md-3">
                            <label class="form-label fw-bold">Scholarship Type <small class="text-muted">(badge)</small></label>
                            <select name="scholarship_type" class="form-select">
                                <option value="">Select Type</option>
                                @foreach(['Merit' => 'Merit Based', 'Need Based' => 'Need Based', 'Sports' => 'Sports Based', 'Minority' => 'Minority Based', 'Government' => 'Government', 'Private' => 'Private / CSR', 'Corporate' => 'Corporate', 'International' => 'International'] as $val => $label)
                                    <option value="{{ $val }}" {{ old('scholarship_type', $scholarship->scholarship_type) == $val ? 'selected' : '' }}>{{ $label }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label fw-bold">Category <small class="text-muted">(chip label)</small></label>
                            <input type="text" name="category" class="form-control" value="{{ old('category', $scholarship->category) }}" placeholder="e.g. Science / Tech">
                        </div>
                        <div class="col-md-3">
                            <label class="form-label fw-bold">Reward Amount (Max INR)</label>
                            <input type="number" name="max_amount" class="form-control" value="{{ old('max_amount', $scholarship->max_amount) }}" placeholder="e.g. 50000">
                        </div>
                        <div class="col-md-3">
                            <label class="form-label fw-bold">Amount Prefix</label>
                            <input type="text" name="amount_prefix" class="form-control" value="{{ old('amount_prefix', $scholarship->amount_prefix) }}" placeholder="e.g. Upto INR">
                        </div>
                        <div class="col-md-3">
                            <label class="form-label fw-bold">Amount Suffix</label>
                            <input type="text" name="amount_suffix" class="form-control" value="{{ old('amount_suffix', $scholarship->amount_suffix) }}" placeholder="e.g. Only / Per Year">
                        </div>
                        <div class="col-md-3">
                            <label class="form-label fw-bold">Card Icon <small class="text-muted">(FontAwesome class)</small></label>
                            <input type="text" name="card_icon" class="form-control" value="{{ old('card_icon', $scholarship->card_icon) }}" placeholder="fas fa-graduation-cap">
                        </div>
                        <div class="col-md-3">
                            <label class="form-label fw-bold">Card Background Color</label>
                            <div class="input-group">
                                <input type="color" name="card_background_color" class="form-control form-control-color" value="{{ old('card_background_color', $scholarship->card_background_color ?? '#1a73e8') }}" style="max-width:50px;" id="bg_color_picker">
                                <input type="text" id="card_bg_hex" class="form-control" value="{{ old('card_background_color', $scholarship->card_background_color ?? '#1a73e8') }}" placeholder="#1a73e8">
                            </div>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label fw-bold">Card Text Color</label>
                            <div class="input-group">
                                <input type="color" name="card_text_color" class="form-control form-control-color" value="{{ old('card_text_color', $scholarship->card_text_color ?? '#ffffff') }}" style="max-width:50px;" id="text_color_picker">
                                <input type="text" id="card_text_hex" class="form-control" value="{{ old('card_text_color', $scholarship->card_text_color ?? '#ffffff') }}" placeholder="#ffffff">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-bold">Featured Image <small class="text-muted">(card preview)</small></label>
                            @if($scholarship->featured_image)
                                <div class="mb-2"><img src="{{ asset($scholarship->featured_image) }}" height="60" class="rounded border"> <small class="text-muted ms-2">Current image</small></div>
                            @endif
                            <input type="file" name="featured_image" class="form-control" accept="image/*">
                        </div>
                        <div class="col-md-3">
                            <label class="form-label fw-bold">CTA Button Text</label>
                            <input type="text" name="cta_text" class="form-control" value="{{ old('cta_text', $scholarship->cta_text ?? 'Check Eligibility') }}">
                        </div>
                        <div class="col-md-3">
                            <label class="form-label fw-bold">CTA Link URL</label>
                            <input type="text" name="cta_url" class="form-control" value="{{ old('cta_url', $scholarship->cta_url) }}" placeholder="https://...">
                        </div>
                    </div>
                </div>

                <!-- ─── Tab 3: Banner ─────────────────────────────────────── -->
                <div class="tab-pane fade" id="banner" role="tabpanel">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label fw-bold">Banner Title</label>
                            <input type="text" name="banner_title" class="form-control" value="{{ old('banner_title', $scholarship->banner_title) }}" placeholder="e.g. Merit Scholarship 2026 — Apply Now">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-bold">Banner Subtitle</label>
                            <input type="text" name="banner_subtitle" class="form-control" value="{{ old('banner_subtitle', $scholarship->banner_subtitle) }}" placeholder="e.g. For Science & Tech Students">
                        </div>
                        <div class="col-12">
                            <label class="form-label fw-bold">Banner Image</label>
                            @if($scholarship->banner_image)
                                <div class="mb-2"><img src="{{ asset($scholarship->banner_image) }}" height="60" class="rounded border"> <small class="text-muted ms-2">Current banner</small></div>
                            @endif
                            <input type="file" name="banner_image" class="form-control" accept="image/*">
                            <small class="text-muted">Recommended: 1400×400px</small>
                        </div>
                        <hr>
                        <div class="col-md-4">
                            <label class="form-label fw-bold">Provider Name</label>
                            <input type="text" name="provider_name" class="form-control" value="{{ old('provider_name', $scholarship->provider_name) }}">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw-bold">Provider Logo</label>
                            @if($scholarship->provider_logo)
                                <div class="mb-2"><img src="{{ asset($scholarship->provider_logo) }}" height="40" class="rounded border"></div>
                            @endif
                            <input type="file" name="provider_logo" class="form-control" accept="image/*">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw-bold">Provider Website URL</label>
                            <input type="url" name="provider_url" class="form-control" value="{{ old('provider_url', $scholarship->provider_url) }}" placeholder="https://...">
                        </div>
                    </div>
                </div>

                <!-- ─── Tab 4: Description ────────────────────────────────── -->
                <div class="tab-pane fade" id="description" role="tabpanel">
                    <div class="row g-3">
                        <div class="col-12">
                            <label class="form-label fw-bold">Overview / Summary</label>
                            <textarea name="overview" class="form-control editor" rows="4">{{ old('overview', $scholarship->overview) }}</textarea>
                        </div>
                        <div class="col-12">
                            <label class="form-label fw-bold">About Scholarship</label>
                            <textarea name="about_scholarship" class="form-control editor" rows="4">{{ old('about_scholarship', $scholarship->about_scholarship) }}</textarea>
                        </div>
                        <div class="col-12">
                            <label class="form-label fw-bold">Why Apply?</label>
                            <textarea name="why_apply" class="form-control editor" rows="4">{{ old('why_apply', $scholarship->why_apply) }}</textarea>
                        </div>
                        <div class="col-12">
                            <label class="form-label fw-bold">Selection Process</label>
                            <textarea name="selection_process" class="form-control editor" rows="4">{{ old('selection_process', $scholarship->selection_process) }}</textarea>
                        </div>
                        <div class="col-12">
                            <label class="form-label fw-bold">Terms & Conditions</label>
                            <textarea name="terms_conditions" class="form-control editor" rows="4">{{ old('terms_conditions', $scholarship->terms_conditions) }}</textarea>
                        </div>
                        <div class="col-12">
                            <label class="form-label fw-bold">Important Notes</label>
                            <textarea name="important_notes" class="form-control editor" rows="3">{{ old('important_notes', $scholarship->important_notes) }}</textarea>
                        </div>
                        <div class="col-12">
                            <label class="form-label fw-bold">Additional Information</label>
                            <textarea name="additional_information" class="form-control editor" rows="3">{{ old('additional_information', $scholarship->additional_information) }}</textarea>
                        </div>
                    </div>
                </div>

                <!-- ─── Tab 5: Eligibility ────────────────────────────────── -->
                <div class="tab-pane fade" id="eligibility" role="tabpanel">
                    @php $elig = $scholarship->eligibility; @endphp
                    <div class="row g-3">
                        <div class="col-md-4">
                            <label class="form-label fw-bold">Minimum Class / Standard</label>
                            <input type="text" name="eligibility[minimum_class]" class="form-control" value="{{ old('eligibility.minimum_class', $elig->minimum_class ?? '') }}" placeholder="e.g. Class 10">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw-bold">Maximum Class / Standard</label>
                            <input type="text" name="eligibility[maximum_class]" class="form-control" value="{{ old('eligibility.maximum_class', $elig->maximum_class ?? '') }}" placeholder="e.g. Post Graduate">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw-bold">Minimum Percentage (%)</label>
                            <input type="number" name="eligibility[minimum_percentage]" class="form-control" step="0.01" value="{{ old('eligibility.minimum_percentage', $elig->minimum_percentage ?? '') }}" placeholder="e.g. 60.00">
                        </div>
                        <div class="col-md-3">
                            <label class="form-label fw-bold">Maximum Age</label>
                            <input type="number" name="eligibility[maximum_age]" class="form-control" value="{{ old('eligibility.maximum_age', $elig->maximum_age ?? '') }}" placeholder="e.g. 25">
                        </div>
                        <div class="col-md-3">
                            <label class="form-label fw-bold">Gender</label>
                            <select name="eligibility[gender]" class="form-select">
                                @foreach(['Any' => 'Any / All Genders', 'Male' => 'Male', 'Female' => 'Female'] as $val => $label)
                                    <option value="{{ $val }}" {{ old('eligibility.gender', $elig->gender ?? 'Any') == $val ? 'selected' : '' }}>{{ $label }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label fw-bold">Category (Caste)</label>
                            <input type="text" name="eligibility[category]" class="form-control" value="{{ old('eligibility.category', $elig->category ?? '') }}" placeholder="General / SC / ST / OBC">
                        </div>
                        <div class="col-md-3">
                            <label class="form-label fw-bold">Annual Family Income</label>
                            <input type="text" name="eligibility[annual_family_income]" class="form-control" value="{{ old('eligibility.annual_family_income', $elig->annual_family_income ?? '') }}" placeholder="e.g. Below 8 Lakhs">
                        </div>
                        <div class="col-md-3">
                            <label class="form-label fw-bold">Academic Stream</label>
                            <input type="text" name="eligibility[academic_stream]" class="form-control" value="{{ old('eligibility.academic_stream', $elig->academic_stream ?? '') }}" placeholder="e.g. Engineering">
                        </div>
                        <div class="col-md-3">
                            <label class="form-label fw-bold">Required Entrance Exam</label>
                            <input type="text" name="eligibility[entrance_exam]" class="form-control" value="{{ old('eligibility.entrance_exam', $elig->entrance_exam ?? '') }}" placeholder="e.g. JEE / NEET">
                        </div>
                        <div class="col-md-3">
                            <label class="form-label fw-bold">Min Exam Score / Rank</label>
                            <input type="number" name="eligibility[minimum_exam_score]" class="form-control" step="0.01" value="{{ old('eligibility.minimum_exam_score', $elig->minimum_exam_score ?? '') }}">
                        </div>
                        <div class="col-md-3">
                            <label class="form-label fw-bold">Currently Studying</label>
                            <input type="text" name="eligibility[currently_studying]" class="form-control" value="{{ old('eligibility.currently_studying', $elig->currently_studying ?? '') }}" placeholder="e.g. B.Tech 1st Year">
                        </div>
                        <div class="col-md-3">
                            <label class="form-label fw-bold">Nationality</label>
                            <input type="text" name="eligibility[nationality]" class="form-control" value="{{ old('eligibility.nationality', $elig->nationality ?? 'Indian') }}">
                        </div>
                        <div class="col-md-3">
                            <label class="form-label fw-bold">State Restriction</label>
                            <input type="text" name="eligibility[state]" class="form-control" value="{{ old('eligibility.state', $elig->state ?? '') }}" placeholder="e.g. Punjab">
                        </div>
                        <div class="col-md-3">
                            <label class="form-label fw-bold">City Restriction</label>
                            <input type="text" name="eligibility[city]" class="form-control" value="{{ old('eligibility.city', $elig->city ?? '') }}" placeholder="e.g. Dehradun">
                        </div>
                        <div class="col-md-3">
                            <label class="form-label fw-bold">Work Experience</label>
                            <input type="text" name="eligibility[work_experience]" class="form-control" value="{{ old('eligibility.work_experience', $elig->work_experience ?? '') }}" placeholder="e.g. Minimum 2 Years">
                        </div>
                        <div class="col-md-3 d-flex align-items-center mt-4">
                            <div class="form-check">
                                <input type="checkbox" name="eligibility[graduation_required]" value="1" class="form-check-input" id="grad_req"
                                    {{ old('eligibility.graduation_required', $elig->graduation_required ?? 0) ? 'checked' : '' }}>
                                <label class="form-check-label fw-bold" for="grad_req">Graduation Required</label>
                            </div>
                        </div>
                        <div class="col-12">
                            <label class="form-label fw-bold">Other Conditions / Notes</label>
                            <textarea name="eligibility[other_conditions]" class="form-control" rows="3" placeholder="Any additional criteria...">{{ old('eligibility.other_conditions', $elig->other_conditions ?? '') }}</textarea>
                        </div>
                    </div>
                </div>

                <!-- ─── Tab 6: Highlights ─────────────────────────────────── -->
                <div class="tab-pane fade" id="highlights" role="tabpanel">
                    <p class="text-muted small mb-3"><i class="fas fa-info-circle me-1"></i> Highlight chips/badges shown on cards and detail page.</p>
                    <table class="table table-bordered align-middle">
                        <thead class="table-light">
                            <tr>
                                <th>Highlight Text <span class="text-danger">*</span></th>
                                <th style="width:220px">Icon <small class="text-muted">(FontAwesome)</small></th>
                                <th style="width:120px">Sort Order</th>
                                <th style="width:80px" class="text-center">Action</th>
                            </tr>
                        </thead>
                        <tbody id="highlights-rows">
                            @forelse($scholarship->highlights as $i => $h)
                            <tr class="highlight-row">
                                <td><input type="text" name="highlights[{{ $i }}][highlight_text]" class="form-control" value="{{ $h->highlight_text }}"></td>
                                <td><input type="text" name="highlights[{{ $i }}][highlight_icon]" class="form-control" value="{{ $h->highlight_icon }}"></td>
                                <td><input type="number" name="highlights[{{ $i }}][sort_order]" class="form-control" value="{{ $h->sort_order }}"></td>
                                <td class="text-center"><button type="button" class="btn btn-outline-danger btn-sm remove-row-btn"><i class="fas fa-trash"></i></button></td>
                            </tr>
                            @empty
                            <tr class="highlight-row">
                                <td><input type="text" name="highlights[0][highlight_text]" class="form-control" placeholder="e.g. ₹30,000 Assistance"></td>
                                <td><input type="text" name="highlights[0][highlight_icon]" class="form-control" placeholder="fas fa-rupee-sign"></td>
                                <td><input type="number" name="highlights[0][sort_order]" class="form-control" value="0"></td>
                                <td class="text-center"><button type="button" class="btn btn-outline-danger btn-sm remove-row-btn"><i class="fas fa-trash"></i></button></td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                    <button type="button" class="btn btn-success btn-sm mt-2" id="add-highlight-btn">
                        <i class="fas fa-plus"></i> Add Highlight
                    </button>
                </div>

                <!-- ─── Tab 7: Benefits ───────────────────────────────────── -->
                <div class="tab-pane fade" id="benefits" role="tabpanel">
                    <table class="table table-bordered align-middle">
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
                            @forelse($scholarship->benefits as $i => $benefit)
                            <tr class="benefit-row">
                                <td><input type="text" name="benefits[{{ $i }}][benefit_title]" class="form-control" value="{{ $benefit->benefit_title }}"></td>
                                <td><input type="number" name="benefits[{{ $i }}][benefit_amount]" class="form-control" value="{{ $benefit->benefit_amount }}"></td>
                                <td><input type="text" name="benefits[{{ $i }}][benefit_description]" class="form-control" value="{{ $benefit->benefit_description }}"></td>
                                <td><input type="number" name="benefits[{{ $i }}][sort_order]" class="form-control" value="{{ $benefit->sort_order }}"></td>
                                <td class="text-center"><button type="button" class="btn btn-outline-danger btn-sm remove-row-btn"><i class="fas fa-trash"></i></button></td>
                            </tr>
                            @empty
                            <tr class="benefit-row">
                                <td><input type="text" name="benefits[0][benefit_title]" class="form-control" placeholder="e.g. Tuition Fee Waiver"></td>
                                <td><input type="number" name="benefits[0][benefit_amount]" class="form-control" placeholder="e.g. 30000"></td>
                                <td><input type="text" name="benefits[0][benefit_description]" class="form-control" placeholder="e.g. Covers 50% tuition"></td>
                                <td><input type="number" name="benefits[0][sort_order]" class="form-control" value="0"></td>
                                <td class="text-center"><button type="button" class="btn btn-outline-danger btn-sm remove-row-btn"><i class="fas fa-trash"></i></button></td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                    <button type="button" class="btn btn-success btn-sm mt-2" id="add-benefit-btn">
                        <i class="fas fa-plus"></i> Add Benefit
                    </button>
                </div>

                <!-- ─── Tab 8: Key Dates ──────────────────────────────────── -->
                <div class="tab-pane fade" id="dates" role="tabpanel">
                    @php $d = $scholarship->dates; @endphp
                    <div class="row g-3">
                        <div class="col-md-4">
                            <label class="form-label fw-bold">Application Start Date</label>
                            <input type="date" name="dates[application_start_date]" class="form-control" value="{{ old('dates.application_start_date', $d->application_start_date ?? '') }}">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw-bold">Application Deadline</label>
                            <input type="date" name="dates[application_end_date]" class="form-control" value="{{ old('dates.application_end_date', $d->application_end_date ?? '') }}">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw-bold">Exam Date</label>
                            <input type="date" name="dates[exam_date]" class="form-control" value="{{ old('dates.exam_date', $d->exam_date ?? '') }}">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw-bold">Result Announcement Date</label>
                            <input type="date" name="dates[result_date]" class="form-control" value="{{ old('dates.result_date', $d->result_date ?? '') }}">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw-bold">Document Verification Date</label>
                            <input type="date" name="dates[document_verification_date]" class="form-control" value="{{ old('dates.document_verification_date', $d->document_verification_date ?? '') }}">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw-bold">Admission Date</label>
                            <input type="date" name="dates[admission_date]" class="form-control" value="{{ old('dates.admission_date', $d->admission_date ?? '') }}">
                        </div>
                    </div>
                </div>

                <!-- ─── Tab 9: Required Documents ─────────────────────────── -->
                <div class="tab-pane fade" id="documents" role="tabpanel">
                    <table class="table table-bordered align-middle">
                        <thead class="table-light">
                            <tr>
                                <th>Document Name <span class="text-danger">*</span></th>
                                <th style="width:200px" class="text-center">Is Mandatory?</th>
                                <th style="width:80px" class="text-center">Action</th>
                            </tr>
                        </thead>
                        <tbody id="documents-rows">
                            @forelse($scholarship->documents as $i => $doc)
                            <tr class="document-row">
                                <td><input type="text" name="documents[{{ $i }}][document_name]" class="form-control" value="{{ $doc->document_name }}"></td>
                                <td class="text-center"><input type="checkbox" name="documents[{{ $i }}][is_mandatory]" value="1" class="form-check-input" {{ $doc->is_mandatory ? 'checked' : '' }}></td>
                                <td class="text-center"><button type="button" class="btn btn-outline-danger btn-sm remove-row-btn"><i class="fas fa-trash"></i></button></td>
                            </tr>
                            @empty
                            <tr class="document-row">
                                <td><input type="text" name="documents[0][document_name]" class="form-control" placeholder="e.g. Aadhar Card"></td>
                                <td class="text-center"><input type="checkbox" name="documents[0][is_mandatory]" value="1" checked class="form-check-input"></td>
                                <td class="text-center"><button type="button" class="btn btn-outline-danger btn-sm remove-row-btn"><i class="fas fa-trash"></i></button></td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                    <button type="button" class="btn btn-success btn-sm mt-2" id="add-document-btn">
                        <i class="fas fa-plus"></i> Add Document
                    </button>
                </div>

                <!-- ─── Tab 10: Courses ───────────────────────────────────── -->
                <div class="tab-pane fade" id="courses" role="tabpanel">
                    <label class="form-label fw-bold">Applicable Courses</label>
                    @php $selectedCourses = $scholarship->courses->pluck('id')->toArray(); @endphp
                    <select name="courses[]" class="form-select" multiple style="height:300px;">
                        @foreach($courses as $course)
                            <option value="{{ $course->id }}" {{ in_array($course->id, $selectedCourses) ? 'selected' : '' }}>
                                {{ $course->name }}
                            </option>
                        @endforeach
                    </select>
                    <small class="text-muted">Hold Ctrl / Cmd to select multiple.</small>
                </div>

                <!-- ─── Tab 11: Universities ──────────────────────────────── -->
                <div class="tab-pane fade" id="universities" role="tabpanel">
                    <label class="form-label fw-bold">Applicable Universities / Institutes</label>
                    @php $selectedOrgs = $scholarship->universities->pluck('id')->toArray(); @endphp
                    <select name="universities[]" class="form-select" multiple style="height:300px;">
                        @foreach($organisations as $org)
                            <option value="{{ $org->id }}" {{ in_array($org->id, $selectedOrgs) ? 'selected' : '' }}>
                                {{ $org->name }}
                            </option>
                        @endforeach
                    </select>
                    <small class="text-muted">Hold Ctrl / Cmd to select multiple.</small>
                </div>

                <!-- ─── Tab 12: FAQs ─────────────────────────────────────── -->
                <div class="tab-pane fade" id="faqs" role="tabpanel">
                    <div id="faqs-container">
                        @forelse($scholarship->faqs as $i => $faq)
                        <div class="faq-row card border shadow-sm p-3 mb-3">
                            <div class="row g-3">
                                <div class="col-md-6">
                                    <label class="form-label fw-bold">Question</label>
                                    <input type="text" name="faqs[{{ $i }}][question]" class="form-control" value="{{ $faq->question }}">
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label fw-bold">Sort Order</label>
                                    <input type="number" name="faqs[{{ $i }}][sort_order]" class="form-control" value="{{ $faq->sort_order }}">
                                </div>
                                <div class="col-md-2 d-flex align-items-end justify-content-end">
                                    <button type="button" class="btn btn-outline-danger w-100 remove-faq-btn"><i class="fas fa-trash"></i> Remove</button>
                                </div>
                                <div class="col-12">
                                    <label class="form-label fw-bold">Answer</label>
                                    <textarea name="faqs[{{ $i }}][answer]" class="form-control" rows="2">{{ $faq->answer }}</textarea>
                                </div>
                            </div>
                        </div>
                        @empty
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
                        @endforelse
                    </div>
                    <button type="button" class="btn btn-success btn-sm" id="add-faq-btn">
                        <i class="fas fa-plus"></i> Add FAQ
                    </button>
                </div>

                <!-- ─── Tab 13: Gallery ──────────────────────────────────── -->
                <div class="tab-pane fade" id="gallery" role="tabpanel">
                    @if($scholarship->gallery->count())
                        <div class="row g-3 mb-4">
                            @foreach($scholarship->gallery as $img)
                            <div class="col-md-3 text-center">
                                <img src="{{ asset($img->image) }}" class="img-fluid rounded border mb-2" style="max-height:120px;object-fit:cover;">
                                <div>
                                    <label class="text-muted small">
                                        <input type="checkbox" name="existing_gallery_delete[]" value="{{ $img->id }}"> Delete
                                    </label>
                                </div>
                            </div>
                            @endforeach
                        </div>
                    @endif
                    <label class="form-label fw-bold">Upload New Gallery Images</label>
                    <input type="file" name="gallery_images[]" class="form-control" multiple accept="image/*">
                    <small class="text-muted">Select multiple images at once.</small>
                </div>

                <!-- ─── Tab 14: SEO ──────────────────────────────────────── -->
                <div class="tab-pane fade" id="seo" role="tabpanel">
                    @php $seo = $scholarship->seo; @endphp
                    <div class="row g-3">
                        <div class="col-md-8">
                            <label class="form-label fw-bold">Meta Title</label>
                            <input type="text" name="seo[meta_title]" class="form-control" value="{{ old('seo.meta_title', $seo->meta_title ?? '') }}" placeholder="e.g. Merit Scholarship 2026 | Enrollzy">
                            <small class="text-muted">Recommended: 50–60 characters</small>
                        </div>
                        <div class="col-md-4 d-flex gap-4 align-items-center pt-4">
                            <div class="form-check">
                                <input type="checkbox" name="seo[no_index]" value="1" class="form-check-input" id="no_index" {{ ($seo->no_index ?? 0) ? 'checked' : '' }}>
                                <label class="form-check-label" for="no_index">No Index</label>
                            </div>
                            <div class="form-check">
                                <input type="checkbox" name="seo[no_follow]" value="1" class="form-check-input" id="no_follow" {{ ($seo->no_follow ?? 0) ? 'checked' : '' }}>
                                <label class="form-check-label" for="no_follow">No Follow</label>
                            </div>
                        </div>
                        <div class="col-12">
                            <label class="form-label fw-bold">Meta Description</label>
                            <textarea name="seo[meta_description]" class="form-control" rows="2" placeholder="160 character summary for search engines...">{{ old('seo.meta_description', $seo->meta_description ?? '') }}</textarea>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-bold">Meta Keywords</label>
                            <input type="text" name="seo[meta_keywords]" class="form-control" value="{{ old('seo.meta_keywords', $seo->meta_keywords ?? '') }}" placeholder="e.g. scholarship, merit, 2026">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-bold">Canonical URL</label>
                            <input type="url" name="seo[canonical_url]" class="form-control" value="{{ old('seo.canonical_url', $seo->canonical_url ?? '') }}" placeholder="https://enrollzy.com/scholarships/...">
                        </div>
                        <div class="col-12"><hr><p class="fw-bold mb-2">Open Graph (Social Media Preview)</p></div>
                        <div class="col-md-6">
                            <label class="form-label fw-bold">OG Title</label>
                            <input type="text" name="seo[og_title]" class="form-control" value="{{ old('seo.og_title', $seo->og_title ?? '') }}" placeholder="Title for Facebook/Twitter share">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-bold">OG Image URL</label>
                            <input type="text" name="seo[og_image]" class="form-control" value="{{ old('seo.og_image', $seo->og_image ?? '') }}" placeholder="https://... (URL of share image)">
                        </div>
                        <div class="col-12">
                            <label class="form-label fw-bold">OG Description</label>
                            <textarea name="seo[og_description]" class="form-control" rows="2" placeholder="Description for social media preview...">{{ old('seo.og_description', $seo->og_description ?? '') }}</textarea>
                        </div>
                    </div>
                </div>

                <!-- ─── Tab 15: Advanced Settings ─────────────────────────── -->
                <div class="tab-pane fade" id="advanced" role="tabpanel">
                    <div class="row g-3">
                        <div class="col-12">
                            <div class="alert alert-info">
                                <strong>Record Info:</strong>
                                ID: <code>{{ $scholarship->id }}</code> |
                                Created: {{ $scholarship->created_at?->format('d M Y H:i') }} |
                                Updated: {{ $scholarship->updated_at?->format('d M Y H:i') }}
                                @if($scholarship->creator) | By: {{ $scholarship->creator->name }} @endif
                            </div>
                        </div>
                        <div class="col-12">
                            <div class="alert alert-warning">
                                <i class="fas fa-trash-alt me-1"></i>
                                <strong>Danger Zone:</strong> Use the Delete button on the list page to soft-delete this scholarship. It can be restored from Trash.
                            </div>
                        </div>
                    </div>
                </div>

            </div><!-- end tab-content -->

            <div class="mt-5 pt-3 border-top d-flex justify-content-between align-items-center">
                <span id="autosave-status-bottom" class="text-muted small"></span>
                <button type="submit" class="btn btn-primary px-5 py-2 fw-bold">
                    <i class="fas fa-save me-1"></i> Update Scholarship
                </button>
            </div>
        </form>
    </div>
</div>
@endsection

@section('scripts')
<script>
$(document).ready(function () {

    // Sync color pickers
    $('#bg_color_picker').on('input', function () { $('#card_bg_hex').val($(this).val()); });
    $('#text_color_picker').on('input', function () { $('#card_text_hex').val($(this).val()); });
    $('#card_bg_hex').on('input', function () { $('#bg_color_picker').val($(this).val()); });
    $('#card_text_hex').on('input', function () { $('#text_color_picker').val($(this).val()); });

    // ── Dynamic Highlights ─────────────────────────────────────
    let highlightCount = {{ $scholarship->highlights->count() ?: 1 }};
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
    let benefitCount = {{ $scholarship->benefits->count() ?: 1 }};
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
    let docCount = {{ $scholarship->documents->count() ?: 1 }};
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
    let faqCount = {{ $scholarship->faqs->count() ?: 1 }};
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
    let autosaveTimer = null;
    const AUTOSAVE_URL = '{{ route("admin.scholarships.autosaveUpdate", $scholarship->id) }}';
    const CSRF         = '{{ csrf_token() }}';

    $.ajaxSetup({
        headers: { 'X-CSRF-TOKEN': CSRF, 'X-Requested-With': 'XMLHttpRequest' }
    });

    function setStatus(msg, cls) {
        $('#autosave-status, #autosave-status-bottom')
            .removeClass('text-muted text-success text-danger').addClass(cls).text(msg);
    }

    function getFormDataWithoutMethod() {
        // Exclude _method=PUT so autosave POST route doesn't get 405
        return $('#scholarship-form').serializeArray()
            .filter(function (f) { return f.name !== '_method'; })
            .map(function (f) { return encodeURIComponent(f.name) + '=' + encodeURIComponent(f.value); })
            .join('&');
    }

    function doAutosave() {
        if (typeof tinymce !== 'undefined') tinymce.triggerSave();
        setStatus('Saving…', 'text-muted');
        $.ajax({
            url: AUTOSAVE_URL,
            method: 'POST',
            data: getFormDataWithoutMethod(),
            success: function (res) {
                if (res.status === 'success') {
                    setStatus('Saved ✓ ' + new Date().toLocaleTimeString(), 'text-success');
                } else {
                    setStatus('Save failed', 'text-danger');
                }
            },
            error: function () { setStatus('Save failed', 'text-danger'); }
        });
    }

    $('button[data-bs-toggle="tab"]').on('shown.bs.tab', function () {
        clearTimeout(autosaveTimer);
        autosaveTimer = setTimeout(doAutosave, 600);
    });

    $(document).on('input change', '#scholarship-form input, #scholarship-form select, #scholarship-form textarea', function () {
        clearTimeout(autosaveTimer);
        autosaveTimer = setTimeout(doAutosave, 2000);
    });

});
</script>
@endsection

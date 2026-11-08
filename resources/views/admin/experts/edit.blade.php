@extends('admin.layouts.master')

@section('title', 'Edit Expert Profile')

@section('content')
    <div class="row justify-content-center">
        <div class="col-md-11">
            @php $docCounts = \App\Models\ExpertDocument::where('expert_id', $expert->id)->selectRaw('count(*) n, sum(status = \'pending\') p')->first(); @endphp
            <div class="d-flex justify-content-end mb-2">
                <a href="{{ route('experts.documents', $expert->id) }}" class="btn btn-sm btn-light border"><i class="fas fa-file-alt me-1 text-success"></i> Documents ({{ (int) $docCounts->n }})@if($docCounts->p)<span class="badge bg-warning text-dark ms-1">{{ (int) $docCounts->p }} to check</span>@endif</a>
            </div>
            @php $waitingChange = \App\Models\ExpertProfileChange::where('expert_id', $expert->id)->where('status', 'pending')->latest('id')->first(); @endphp
            @if($waitingChange)
                <div class="alert alert-warning d-flex justify-content-between align-items-center py-2">
                    <span><i class="fas fa-user-edit me-1"></i> This expert has sent profile changes for approval ({{ $waitingChange->updated_at->format('d M Y') }}). Saving this form does not cancel them.</span>
                    <a href="{{ route('expert-profile-changes.show', $waitingChange) }}" class="btn btn-sm btn-warning">Review</a>
                </div>
            @endif
            <form action="{{ route('experts.update', $expert->id) }}" method="POST" enctype="multipart/form-data">
                @csrf
                @method('PUT')

                <div class="card shadow-sm border-0">
                    <div class="card-header bg-white border-bottom-0">
                        <ul class="nav nav-tabs card-header-tabs" id="expertTabs" role="tablist">
                            <li class="nav-item"><a class="nav-link active" data-bs-toggle="tab" href="#ex-basic">1.
                                    Identity</a></li>
                            <li class="nav-item"><a class="nav-link" data-bs-toggle="tab" href="#ex-career">Career pages</a></li>
                            <li class="nav-item"><a class="nav-link" data-bs-toggle="tab" href="#ex-listing">Experts page</a></li>
                            <li class="nav-item"><a class="nav-link" data-bs-toggle="tab" href="#ex-profile">Profile page</a></li>
                            <li class="nav-item"><a class="nav-link" data-bs-toggle="tab" href="#ex-credentials">2.
                                    Credentials</a></li>
                            <li class="nav-item"><a class="nav-link" data-bs-toggle="tab" href="#ex-content">3. Content</a>
                            </li>
                            <li class="nav-item"><a class="nav-link" data-bs-toggle="tab" href="#ex-ratings">4. Ratings</a>
                            </li>
                            <li class="nav-item"><a class="nav-link" data-bs-toggle="tab" href="#ex-contact">5. Contact &
                                    SEO</a></li>
                            <li class="nav-item"><a class="nav-link" data-bs-toggle="tab" href="#ex-admin">6. Admin</a></li>
                        </ul>
                    </div>

                    <div class="card-body">
                        <div class="tab-content mt-3">

                            <!-- 1. BASIC IDENTITY -->
                            <div class="tab-pane fade show active" id="ex-basic">
                                <h5 class="mb-3 text-primary">Basic Identity</h5>
                                <div class="row g-3">
                                    <div class="col-md-6">
                                        <label class="form-label">Full Name <span class="text-danger">*</span></label>
                                        <input type="text" class="form-control" name="name"
                                            value="{{ old('name', $expert->name) }}" required>
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label">Email <span class="text-danger">*</span></label>
                                        <input type="email" class="form-control" name="email"
                                            value="{{ old('email', $expert->email) }}" required>
                                    </div>
                                    <div class="col-md-4">
                                        <label class="form-label">Role (Category) <span class="text-danger">*</span></label>
                                        <select name="expert_category_id" class="form-select" required>
                                            <option value="">Select Category</option>
                                            @foreach(\App\Models\ExpertCategory::where('is_active', true)->get() as $cat)
                                                <option value="{{ $cat->id }}" {{ (old('expert_category_id') ?? $expert->expert_category_id) == $cat->id ? 'selected' : '' }}>{{ $cat->name }}
                                                </option>
                                            @endforeach
                                        </select>
                                    </div>
                                    @include('admin.experts._identity', ['expert' => $expert])
                                    <div class="col-md-6">
                                        <label class="form-label">Password (Leave empty to keep current)</label>
                                        <input type="password" class="form-control" name="password">
                                    </div>
                                    <div class="col-md-3">
                                        <label class="form-label">Price Per Minute (₹)</label>
                                        <input type="number" step="0.01" class="form-control" name="price_per_min"
                                            value="{{ old('price_per_min', $expert->price_per_min ?? 10.00) }}"
                                            placeholder="e.g. 10.00">
                                    </div>
                                    <div class="col-md-3">
                                        <label class="form-label fw-bold">Featured on Homepage <span
                                                class="text-danger">*</span></label>
                                        <select name="is_featured" class="form-select">
                                            <option value="0" {{ old('is_featured', $expert->is_featured ?? 0) == 0 ? 'selected' : '' }}>No</option>
                                            <option value="1" {{ old('is_featured', $expert->is_featured ?? 0) == 1 ? 'selected' : '' }}>Yes</option>
                                        </select>
                                    </div>
                                    <div class="col-md-12">
                                        <label class="form-label">Profile Photo</label>
                                        @if($expert->img)
                                            <div class="mb-2">
                                                <br><img
                                                    src="{{ str_starts_with($expert->img, 'http') ? $expert->img : asset($expert->img) }}"
                                                    width="60" class="rounded">
                                            </div>
                                        @endif
                                        <input type="file" class="form-control" name="img">
                                        <div class="form-text text-muted">Image size should not exceed 2MB.</div>
                                    </div>
                                    <div class="col-md-12">
                                        <label class="form-label">Short Bio</label>
                                        <textarea class="form-control" name="short_bio" rows="2"
                                            maxlength="255">{{ old('short_bio', $expert->short_bio) }}</textarea>
                                    </div>
                                    <div class="col-md-12">
                                        <label class="form-label">Detailed Bio</label>
                                        <textarea class="form-control" name="detailed_bio"
                                            rows="4">{{ old('detailed_bio', $expert->detailed_bio) }}</textarea>
                                    </div>
                                </div>
                            </div>

                            <!-- CAREER ROADMAP: where the expert shows on the career pages -->
                            <div class="tab-pane fade" id="ex-career">
                                @php
                                    $ccats = \App\Models\CareerCategory::orderBy('sort_order')->orderBy('name')->get(['id', 'name']);
                                    $cclasses = \App\Models\CareerRoadmapStage::orderBy('sort_order')->orderBy('id')->get(['id', 'title']);
                                    $selCats = collect(old('career_category_ids', $expert->career_category_ids ?? []))->map(fn ($x) => (int) $x)->all();
                                    $selCls = collect(old('career_class_ids', $expert->career_class_ids ?? []))->map(fn ($x) => (int) $x)->all();
                                @endphp
                                <h5 class="mb-1 text-primary">Career roadmap pages</h5>
                                <p class="text-muted small">The website shows experts on the career roadmap, careers and roadmap pages, matched to the page's career category and class. Leave a list empty to cover all.</p>
                                <div class="row g-3">
                                    <div class="col-md-12">
                                        <div class="form-check form-switch">
                                            <input class="form-check-input" type="checkbox" name="show_on_career_pages" id="show_on_career_pages" value="1" {{ old('show_on_career_pages', $expert->show_on_career_pages ?? true) ? 'checked' : '' }}>
                                            <label class="form-check-label" for="show_on_career_pages">Show on the career pages</label>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label">Career categories they advise on</label>
                                        <select name="career_category_ids[]" class="form-select" multiple>
                                            @foreach($ccats as $cc)<option value="{{ $cc->id }}" {{ in_array($cc->id, $selCats, true) ? 'selected' : '' }}>{{ $cc->name }}</option>@endforeach
                                        </select>
                                        <small class="text-muted">Empty: a general adviser, shown for every category.</small>
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label">Classes they counsel</label>
                                        <select name="career_class_ids[]" class="form-select" multiple>
                                            @foreach($cclasses as $cl)<option value="{{ $cl->id }}" {{ in_array($cl->id, $selCls, true) ? 'selected' : '' }}>{{ $cl->title }}</option>@endforeach
                                        </select>
                                        <small class="text-muted">Empty: every class.</small>
                                    </div>
                                </div>
                            </div>
                            @include('admin.experts._listing', ['expert' => $expert ?? null])
                            @include('admin.experts._profile', ['expert' => $expert])
                            <!-- 2. CREDENTIALS -->
                            <div class="tab-pane fade" id="ex-credentials">
                                <h5 class="mb-3 text-primary">Professional Credentials</h5>
                                <div class="row g-3">
                                    <div class="col-md-6">
                                        <label class="form-label">Highest Qualification</label>
                                        <input type="text" class="form-control" name="highest_qualification"
                                            value="{{ $expert->highest_qualification }}">
                                    </div>
                                    <div class="col-md-3">
                                        <label class="form-label">Degree</label>
                                        <input type="text" class="form-control" name="degree" value="{{ $expert->degree }}">
                                    </div>
                                    <div class="col-md-3">
                                        <label class="form-label">Experience (General)</label>
                                        <input type="text" class="form-control" name="exp" value="{{ $expert->exp }}">
                                    </div>
                                    <div class="col-md-3">
                                        <label class="form-label">Total Exp</label>
                                        <input type="number" step="0.1" class="form-control"
                                            name="years_of_experience_total"
                                            value="{{ $expert->years_of_experience_total }}">
                                    </div>
                                    <div class="col-md-3">
                                        <label class="form-label">Current Inst. Exp</label>
                                        <input type="number" step="0.1" class="form-control"
                                            name="years_of_experience_current_institute"
                                            value="{{ $expert->years_of_experience_current_institute }}">
                                    </div>
                                    <div class="col-md-12">
                                        <label class="form-label">Other Qualifications</label>
                                        <div id="qualifications-container">
                                            @if($expert->other_qualifications && is_array($expert->other_qualifications))
                                                @foreach($expert->other_qualifications as $index => $qual)
                                                    @php
                                                        // Handle Legacy String format vs New Object format
                                                        $name = is_array($qual) ? ($qual['name'] ?? '') : $qual;
                                                        $institute = is_array($qual) ? ($qual['institute'] ?? '') : '';
                                                        $year = is_array($qual) ? ($qual['year'] ?? '') : '';
                                                    @endphp
                                                    <div class="row g-2 mb-2 qualification-row align-items-end">
                                                        <div class="col-md-4">
                                                            <input type="text" name="other_qualifications[{{ $index }}][name]"
                                                                class="form-control form-control-sm" value="{{ $name }}"
                                                                placeholder="Degree">
                                                        </div>
                                                        <div class="col-md-4">
                                                            <input type="text" name="other_qualifications[{{ $index }}][institute]"
                                                                class="form-control form-control-sm" value="{{ $institute }}"
                                                                placeholder="Institute">
                                                        </div>
                                                        <div class="col-md-2">
                                                            <input type="number" name="other_qualifications[{{ $index }}][year]"
                                                                class="form-control form-control-sm" value="{{ $year }}"
                                                                placeholder="Year">
                                                        </div>
                                                        <div class="col-md-2">
                                                            <button type="button"
                                                                class="btn btn-sm btn-danger remove-qualification-btn"><i
                                                                    class="fas fa-times"></i></button>
                                                        </div>
                                                    </div>
                                                @endforeach
                                            @endif
                                        </div>
                                        <button type="button" class="btn btn-sm btn-outline-primary mt-2"
                                            id="add-qualification-btn">
                                            <i class="fas fa-plus me-1"></i> Add Qualification
                                        </button>

                                        <!-- Template -->
                                        <template id="qualification-row-template">
                                            <div class="row g-2 mb-2 qualification-row align-items-end">
                                                <div class="col-md-4">
                                                    <input type="text" name="other_qualifications[INDEX][name]"
                                                        class="form-control form-control-sm" placeholder="Degree">
                                                </div>
                                                <div class="col-md-4">
                                                    <input type="text" name="other_qualifications[INDEX][institute]"
                                                        class="form-control form-control-sm" placeholder="Institute">
                                                </div>
                                                <div class="col-md-2">
                                                    <input type="number" name="other_qualifications[INDEX][year]"
                                                        class="form-control form-control-sm" placeholder="Year">
                                                </div>
                                                <div class="col-md-2">
                                                    <button type="button"
                                                        class="btn btn-sm btn-danger remove-qualification-btn"><i
                                                            class="fas fa-times"></i></button>
                                                </div>
                                            </div>
                                        </template>
                                    </div>
                                    <div class="col-md-12">
                                        <label class="form-label">Certifications</label>
                                        <select class="form-control select2-tags" name="certifications[]" multiple>
                                            @if($expert->certifications)
                                                @foreach($expert->certifications as $cert)
                                                    <option value="{{ $cert }}" selected>{{ $cert }}</option>
                                                @endforeach
                                            @endif
                                        </select>
                                    </div>
                                    <div class="col-md-12">
                                        <label class="form-label">Exams Cleared by Students</label>
                                        <select class="form-control select2-tags" name="exams_cleared[]" multiple>
                                            @if($expert->exams_cleared)
                                                @foreach($expert->exams_cleared as $ex)
                                                    <option value="{{ $ex }}" selected>{{ $ex }}</option>
                                                @endforeach
                                            @endif
                                            <option value="IIT-JEE">IIT-JEE</option>
                                            <option value="NEET">NEET</option>
                                        </select>
                                    </div>
                                    <div class="col-md-12">
                                        <label class="form-label">Notable Achievements</label>
                                        <select class="form-control select2-tags" name="notable_achievements[]" multiple>
                                            @if($expert->notable_achievements)
                                                @foreach($expert->notable_achievements as $ach)
                                                    <option value="{{ $ach }}" selected>{{ $ach }}</option>
                                                @endforeach
                                            @endif
                                        </select>
                                    </div>
                                    <div class="col-md-12">
                                        <div class="form-check form-switch mt-2">
                                            <input class="form-check-input" type="checkbox" name="industry_experience"
                                                value="1" {{ $expert->industry_experience ? 'checked' : '' }}>
                                            <label class="form-check-label">Has Industry Experience?</label>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            @include('admin.experts._content', ['expert' => $expert ?? null])

                            <!-- 7. RATINGS -->
                            <div class="tab-pane fade" id="ex-ratings">
                                <h5 class="mb-3 text-primary">Ratings & Reviews</h5>
                                <div class="row g-3">
                                    <div class="col-md-6">
                                        <label class="form-label">Average Rating</label>
                                        <input type="number" step="0.1" class="form-control" name="rating"
                                            value="{{ $expert->rating }}">
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label">Total Reviews</label>
                                        <input type="number" class="form-control" name="total_reviews"
                                            value="{{ $expert->total_reviews }}">
                                    </div>
                                    <div class="col-md-12">
                                        <div class="form-check form-switch">
                                            <input class="form-check-input" type="checkbox"
                                                name="verified_student_reviews_only" value="1" {{ $expert->verified_student_reviews_only ? 'checked' : '' }}>
                                            <label class="form-check-label">Verified Student Reviews Only</label>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- 8. CONTACT -->
                            <div class="tab-pane fade" id="ex-contact">
                                <h5 class="mb-3 text-primary">Contact & SEO</h5>
                                <div class="row g-3">
                                    <div class="col-md-6">
                                        <label class="form-label">Mobile number <span class="text-danger">*</span> <small class="text-muted">(private; the expert signs in on the website with OTP to it)</small></label>
                                        <input type="tel" class="form-control" name="contact_number" value="{{ old('contact_number', $expert->contact_number) }}" inputmode="numeric" maxlength="10" pattern="[6-9][0-9]{9}" placeholder="10-digit mobile" required>
                                        @if($expert->user_id)<small class="text-success d-block">Linked to a website account: this expert has signed in on the website.</small>@else<small class="text-muted d-block">Not signed in on the website yet.</small>@endif
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label">Visibility</label>
                                        <select name="profile_visibility" class="form-select">
                                            @foreach(['Public', 'Institute-only', 'Private'] as $vis)
                                                <option value="{{ $vis }}" {{ $expert->profile_visibility == $vis ? 'selected' : '' }}>{{ $vis }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-check form-switch mt-4">
                                            <input class="form-check-input" type="checkbox" name="public_contact_allowed"
                                                value="1" {{ $expert->public_contact_allowed ? 'checked' : '' }}>
                                            <label class="form-check-label">Allow Public Contact</label>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-check form-switch mt-4">
                                            <input class="form-check-input" type="checkbox" name="profile_claimed" value="1"
                                                {{ $expert->profile_claimed ? 'checked' : '' }}>
                                            <label class="form-check-label">Profile Claimed</label>
                                        </div>
                                    </div>

                                    <div class="col-12">
                                        <hr>
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label">Meta Title</label>
                                        <input type="text" class="form-control" name="meta_title"
                                            value="{{ $expert->meta_title }}">
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label">Canonical URL</label>
                                        <input type="text" class="form-control" name="canonical_url"
                                            value="{{ $expert->canonical_url }}">
                                    </div>
                                    <div class="col-md-12">
                                        <label class="form-label">Meta Description</label>
                                        <textarea class="form-control" name="meta_description"
                                            rows="2">{{ $expert->meta_description }}</textarea>
                                    </div>
                                    <div class="col-md-12">
                                        <label class="form-label">Focus Keywords</label>
                                        <select class="form-control select2-tags" name="focus_keywords[]" multiple>
                                            @if($expert->focus_keywords)
                                                @foreach($expert->focus_keywords as $fk)
                                                    <option value="{{ $fk }}" selected>{{ $fk }}</option>
                                                @endforeach
                                            @endif
                                        </select>
                                    </div>
                                </div>
                            </div>

                            <!-- 9. ADMIN -->
                            <div class="tab-pane fade" id="ex-admin">
                                <h5 class="mb-3 text-primary">Admin System</h5>
                                <div class="row g-3">
                                    <div class="col-md-6">
                                        <label class="form-label">Data Source</label>
                                        <input type="text" class="form-control" name="data_source"
                                            value="{{ $expert->data_source }}">
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label">Confidence Score</label>
                                        <input type="number" step="0.1" class="form-control" name="confidence_score"
                                            value="{{ $expert->confidence_score }}">
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label">Verification</label>
                                        <select name="verification_status" class="form-select">
                                            @foreach(['Pending', 'Verified', 'Rejected'] as $v)
                                                <option value="{{ $v }}" {{ $expert->verification_status == $v ? 'selected' : '' }}>{{ $v }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label">Status</label>
                                        <select name="status" class="form-select">
                                            @foreach(['Active', 'Inactive', 'Archived'] as $s)
                                                <option value="{{ $s }}" {{ $expert->status == $s ? 'selected' : '' }}>{{ $s }}
                                                </option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- 10. COMMISSION -->
                        <div class="tab-pane fade" id="ex-commission">
                            <h5 class="mb-3 text-primary">Commission Settings</h5>

                            <div class="row">
                                <div class="col-md-12">
                                    <div class="alert alert-info border-0 shadow-sm">
                                        <h6 class="alert-heading fw-bold"><i class="fas fa-info-circle me-2"></i> How
                                            Commission Works</h6>
                                        <p class="mb-0 small">
                                            The system resolves commission in this order: <br>
                                            <strong>Booking Override > Expert Specific > Category Specific > Global
                                                Default</strong>.
                                        </p>
                                    </div>
                                </div>

                                <div class="col-md-6">
                                    <div class="card bg-light border-0 mb-3">
                                        <div class="card-body">
                                            <h6 class="text-uppercase text-muted small fw-bold">Current Expert Configuration
                                            </h6>
                                            @if($activeCommission)
                                                <h3 class="text-primary fw-bold mt-2">
                                                    {{ $activeCommission->commission_type == 'percentage' ? floatval($activeCommission->commission_value) . '%' : '₹' . floatval($activeCommission->commission_value) }}
                                                </h3>
                                                <span class="badge bg-success mb-2">Active Override</span>
                                                <p class="small text-muted mb-0">Reason:
                                                    {{ $activeCommission->reason ?? 'N/A' }}</p>
                                                <p class="small text-muted">Created:
                                                    {{ $activeCommission->created_at->format('d M, Y') }}</p>
                                            @else
                                                <h3 class="text-secondary fw-bold mt-2">Default</h3>
                                                <span class="badge bg-secondary mb-2">Using Global/Category Policy</span>
                                                <p class="small text-muted mb-0">No specific override set for this expert.</p>
                                            @endif

                                            <button type="button" class="btn btn-outline-primary btn-sm mt-3"
                                                data-bs-toggle="modal" data-bs-target="#commissionModal">
                                                <i class="fas fa-cog me-1"></i> Configure Commission
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                    </div>
                </div>

                <div class="card-footer bg-white text-end py-3">
                    <a href="{{ route('experts.index') }}" class="btn btn-light me-2">Cancel</a>
                    <button type="submit" class="btn btn-primary px-4"><i class="fas fa-save me-2"></i> Update
                        Profile</button>
                </div>
        </div>

        @if ($errors->any())
            <div class="alert alert-danger mt-3">
                <ul class="mb-0">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif
        </form>
    </div>
    </div>
    <!-- Commission Modal -->
    <div class="modal fade" id="commissionModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title fw-bold">Configure Expert Commission</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form action="{{ route('experts.commission.update', $expert->id) }}" method="POST">
                    @csrf
                    <div class="modal-body">
                        <p class="small text-muted mb-3">
                            Define a specific commission rule for this expert. This will <strong>override</strong> Global
                            and Category policies.
                        </p>

                        <div class="mb-3">
                            <label class="form-label fw-bold">Commission Type</label>
                            <select name="commission_type" class="form-select">
                                <option value="percentage">Percentage (%)</option>
                                <option value="flat_fee">Flat Fee (₹)</option>
                            </select>
                        </div>

                        <div class="mb-3">
                            <label class="form-label fw-bold">Value</label>
                            <input type="number" step="0.01" name="commission_value" class="form-control" required
                                placeholder="e.g. 10">
                        </div>

                        <div class="mb-3">
                            <label class="form-label fw-bold">Reason / Note</label>
                            <input type="text" name="reason" class="form-control" placeholder="e.g. Contract 2024">
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-primary">Save Commission</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection

@push('css')
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
    <style>
        .select2-container .select2-selection--single,
        .select2-container--default .select2-selection--multiple {
            min-height: 38px;
            border: 1px solid #dee2e6;
        }
    </style>
@endpush

@push('js')
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
    <script>
        $(document).ready(function () {
            $('.select2-tags').select2({
                tags: true,
                tokenSeparators: [','],
                placeholder: "Type and enter to add",
                width: '100%'
            });

            // Article repeater (title + link)
            let artIndex = {{ is_array($expert->articles_written) ? count($expert->articles_written) : 0 }};
            const artTpl = document.getElementById('article-row-template').innerHTML;
            $('#add-article-btn').click(function () { $('#articles-container').append(artTpl.replace(/INDEX/g, artIndex++)); });
            $(document).on('click', '.remove-article-btn', function () { $(this).closest('.article-row').remove(); });

            // Qualification Repeater Logic
            let qualIndex = {{ is_array($expert->other_qualifications) ? count($expert->other_qualifications) : 0 }};
            const container = $('#qualifications-container');
            const template = document.getElementById('qualification-row-template').innerHTML;

            $('#add-qualification-btn').click(function () {
                let rowHtml = template.replace(/INDEX/g, qualIndex++);
                container.append(rowHtml);
            });

            $(document).on('click', '.remove-qualification-btn', function () {
                $(this).closest('.qualification-row').remove();
            });
        });
    </script>
@endpush
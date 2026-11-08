@extends('admin.layouts.master')

@section('title', 'Add New Expert')

@section('content')
    <div class="row justify-content-center">
        <div class="col-md-11">
            <form action="{{ route('experts.store') }}" method="POST" enctype="multipart/form-data">
                @csrf

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
                                        <input type="text" class="form-control" name="name" value="{{ old('name') }}"
                                            required>
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label">Email <span class="text-danger">*</span></label>
                                        <input type="email" class="form-control" name="email" value="{{ old('email') }}"
                                            required>
                                    </div>
                                    <div class="col-md-4">
                                        <label class="form-label">Role (Category) <span class="text-danger">*</span></label>
                                        <select name="expert_category_id" class="form-select" required>
                                            <option value="">Select Category</option>
                                            @foreach(\App\Models\ExpertCategory::where('is_active', true)->get() as $cat)
                                                <option value="{{ $cat->id }}" {{ old('expert_category_id') == $cat->id ? 'selected' : '' }}>{{ $cat->name }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                    @include('admin.experts._identity', ['expert' => null])
                                    <div class="col-md-6">
                                        <label class="form-label">Password <span class="text-danger">*</span></label>
                                        <input type="password" class="form-control" name="password" required>
                                    </div>
                                    <div class="col-md-3">
                                        <label class="form-label">Price Per Minute (₹)</label>
                                        <input type="number" step="0.01" class="form-control" name="price_per_min"
                                            value="{{ old('price_per_min', '10.00') }}" placeholder="e.g. 10.00">
                                    </div>
                                    <div class="col-md-3">
                                        <label class="form-label fw-bold">Featured on Homepage <span
                                                class="text-danger">*</span></label>
                                        <select name="is_featured" class="form-select">
                                            <option value="0" {{ old('is_featured', '0') == '0' ? 'selected' : '' }}>No</option>
                                            <option value="1" {{ old('is_featured') === '1' ? 'selected' : '' }}>Yes</option>
                                        </select>
                                    </div>
                                    <div class="col-md-12">
                                        <label class="form-label">Profile Photo <span class="text-danger">*</span></label>
                                        <input type="file" class="form-control" name="img" required>
                                        <div class="form-text text-muted">Image size should not exceed 2MB.</div>
                                    </div>
                                    <div class="col-md-12">
                                        <label class="form-label">Short Bio (150-200 chars)</label>
                                        <textarea class="form-control" name="short_bio" rows="2" maxlength="255"></textarea>
                                    </div>
                                    <div class="col-md-12">
                                        <label class="form-label">Detailed Bio</label>
                                        <textarea class="form-control" name="detailed_bio" rows="4"></textarea>
                                    </div>
                                </div>
                            </div>

                            <!-- CAREER ROADMAP: where the expert shows on the career pages -->
                            <div class="tab-pane fade" id="ex-career">
                                @php
                                    $ccats = \App\Models\CareerCategory::orderBy('sort_order')->orderBy('name')->get(['id', 'name']);
                                    $cclasses = \App\Models\CareerRoadmapStage::orderBy('sort_order')->orderBy('id')->get(['id', 'title']);
                                    $selCats = collect(old('career_category_ids', []))->map(fn ($x) => (int) $x)->all();
                                    $selCls = collect(old('career_class_ids', []))->map(fn ($x) => (int) $x)->all();
                                @endphp
                                <h5 class="mb-1 text-primary">Career roadmap pages</h5>
                                <p class="text-muted small">The website shows experts on the career roadmap, careers and roadmap pages, matched to the page's career category and class. Leave a list empty to cover all.</p>
                                <div class="row g-3">
                                    <div class="col-md-12">
                                        <div class="form-check form-switch">
                                            <input class="form-check-input" type="checkbox" name="show_on_career_pages" id="show_on_career_pages" value="1" {{ old('show_on_career_pages', true) ? 'checked' : '' }}>
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
                            @include('admin.experts._listing', ['expert' => null])
                            @include('admin.experts._profile', ['expert' => null])

                            <!-- 2. PROFESSIONAL CREDENTIALS -->
                            <div class="tab-pane fade" id="ex-credentials">
                                <h5 class="mb-3 text-primary">Professional Credentials</h5>
                                <div class="row g-3">
                                    <div class="col-md-6">
                                        <label class="form-label">Highest Qualification</label>
                                        <input type="text" class="form-control" name="highest_qualification"
                                            placeholder="e.g. PhD in Physics" value="{{ old('highest_qualification') }}">
                                    </div>
                                    <div class="col-md-3">
                                        <label class="form-label">Degree</label>
                                        <input type="text" class="form-control" name="degree" placeholder="e.g. B.Tech"
                                            value="{{ old('degree') }}">
                                    </div>
                                    <div class="col-md-3">
                                        <label class="form-label">Experience (General)</label>
                                        <input type="text" class="form-control" name="exp" placeholder="e.g. 5+ Years"
                                            value="{{ old('exp') }}">
                                    </div>
                                    <div class="col-md-3">
                                        <label class="form-label">Total Experience (Years)</label>
                                        <input type="number" step="0.1" class="form-control"
                                            name="years_of_experience_total">
                                    </div>
                                    <div class="col-md-3">
                                        <label class="form-label">Exp. Current Institute (Years)</label>
                                        <input type="number" step="0.1" class="form-control"
                                            name="years_of_experience_current_institute">
                                    </div>
                                    <div class="col-md-12">
                                        <label class="form-label">Other Qualifications (Add Multiple)</label>
                                        <div id="qualifications-container">
                                            <!-- Dynamic Rows -->
                                        </div>
                                        <button type="button" class="btn btn-sm btn-outline-primary mt-2"
                                            id="add-qualification-btn">
                                            <i class="fas fa-plus me-1"></i> Add Qualification
                                        </button>
                                    </div>

                                    <!-- Template for Qualification Row -->
                                    <template id="qualification-row-template">
                                        <div class="row g-2 mb-2 qualification-row align-items-end">
                                            <div class="col-md-4">
                                                <input type="text" name="other_qualifications[INDEX][name]"
                                                    class="form-control form-control-sm"
                                                    placeholder="Degree/Course (e.g. MBA)">
                                            </div>
                                            <div class="col-md-4">
                                                <input type="text" name="other_qualifications[INDEX][institute]"
                                                    class="form-control form-control-sm" placeholder="Institute/University">
                                            </div>
                                            <div class="col-md-2">
                                                <input type="number" name="other_qualifications[INDEX][year]"
                                                    class="form-control form-control-sm" placeholder="Year">
                                            </div>
                                            <div class="col-md-2">
                                                <button type="button" class="btn btn-sm btn-danger remove-qualification-btn"
                                                    title="Remove">
                                                    <i class="fas fa-times"></i>
                                                </button>
                                            </div>
                                        </div>
                                    </template>
                                    <div class="col-md-12">
                                        <label class="form-label">Certifications (Tag)</label>
                                        <select class="form-control select2-tags" name="certifications[]" multiple></select>
                                    </div>
                                    <div class="col-md-12">
                                        <label class="form-label">Exams Cleared by Students (Tag)</label>
                                        <select class="form-control select2-tags" name="exams_cleared[]" multiple>
                                            <option value="IIT-JEE">IIT-JEE</option>
                                            <option value="NEET">NEET</option>
                                            <option value="GATE">GATE</option>
                                            <option value="NDA">NDA</option>
                                        </select>
                                    </div>
                                    <div class="col-md-12">
                                        <label class="form-label">Notable Achievements (Array)</label>
                                        <select class="form-control select2-tags" name="notable_achievements[]"
                                            multiple></select>
                                    </div>
                                    <div class="col-md-12">
                                        <div class="form-check form-switch mt-2">
                                            <input class="form-check-input" type="checkbox" name="industry_experience"
                                                value="1">
                                            <label class="form-check-label">Has Industry Experience?</label>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            @include('admin.experts._content', ['expert' => null])

                            <!-- 7. RATINGS -->
                            <div class="tab-pane fade" id="ex-ratings">
                                <h5 class="mb-3 text-primary">Ratings, Reviews & Trust</h5>
                                <div class="row g-3">
                                    <div class="col-md-6">
                                        <label class="form-label">Average Rating</label>
                                        <input type="number" step="0.1" class="form-control" name="rating" value="5">
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label">Total Reviews</label>
                                        <input type="number" class="form-control" name="total_reviews" value="0">
                                    </div>
                                    <div class="col-md-12">
                                        <div class="form-check form-switch">
                                            <input class="form-check-input" type="checkbox"
                                                name="verified_student_reviews_only" value="1">
                                            <label class="form-check-label">Verified Student Reviews Only</label>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- 8. CONTACT -->
                            <div class="tab-pane fade" id="ex-contact">
                                <h5 class="mb-3 text-primary">Contact & Visibility</h5>
                                <div class="row g-3">
                                    <div class="col-md-6">
                                        <label class="form-label">Mobile number <span class="text-danger">*</span> <small class="text-muted">(private; the expert signs in on the website with OTP to it)</small></label>
                                        <input type="tel" class="form-control" name="contact_number" value="{{ old('contact_number') }}" inputmode="numeric" maxlength="10" pattern="[6-9][0-9]{9}" placeholder="10-digit mobile" required>
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label">Profile Visibility</label>
                                        <select name="profile_visibility" class="form-select">
                                            <option value="Public">Public</option>
                                            <option value="Institute-only">Institute-only</option>
                                            <option value="Private">Private</option>
                                        </select>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-check form-switch mt-4">
                                            <input class="form-check-input" type="checkbox" name="public_contact_allowed"
                                                value="1">
                                            <label class="form-check-label">Allow Public Contact</label>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-check form-switch mt-4">
                                            <input class="form-check-input" type="checkbox" name="profile_claimed"
                                                value="1">
                                            <label class="form-check-label">Profile Claimed</label>
                                        </div>
                                    </div>

                                    <div class="col-12">
                                        <hr>
                                    </div>
                                    <h6 class="text-secondary">SEO META</h6>
                                    <div class="col-md-6">
                                        <label class="form-label">Meta Title</label>
                                        <input type="text" class="form-control" name="meta_title">
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label">Canonical URL</label>
                                        <input type="text" class="form-control" name="canonical_url">
                                    </div>
                                    <div class="col-md-12">
                                        <label class="form-label">Meta Description</label>
                                        <textarea class="form-control" name="meta_description" rows="2"></textarea>
                                    </div>
                                    <div class="col-md-12">
                                        <label class="form-label">Focus Keywords (Tag)</label>
                                        <select class="form-control select2-tags" name="focus_keywords[]" multiple></select>
                                    </div>
                                </div>
                            </div>

                            <!-- 9. ADMIN -->
                            <div class="tab-pane fade" id="ex-admin">
                                <h5 class="mb-3 text-primary">Admin & System</h5>
                                <div class="row g-3">
                                    <div class="col-md-6">
                                        <label class="form-label">Data Source</label>
                                        <input type="text" class="form-control" name="data_source" value="Manual">
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label">Confidence Score</label>
                                        <input type="number" step="0.1" class="form-control" name="confidence_score">
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label">Verification Status</label>
                                        <select name="verification_status" class="form-select">
                                            <option value="Pending">Pending</option>
                                            <option value="Verified">Verified</option>
                                            <option value="Rejected">Rejected</option>
                                        </select>
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label">Status</label>
                                        <select name="status" class="form-select">
                                            <option value="Active">Active</option>
                                            <option value="Inactive">Inactive</option>
                                            <option value="Archived">Archived</option>
                                        </select>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="card-footer bg-white text-end py-3">
                        <a href="{{ route('experts.index') }}" class="btn btn-light me-2">Cancel</a>
                        <button type="submit" class="btn btn-primary px-4"><i class="fas fa-save me-2"></i> Create
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
            let artIndex = 0;
            const artTpl = document.getElementById('article-row-template').innerHTML;
            $('#add-article-btn').click(function () { $('#articles-container').append(artTpl.replace(/INDEX/g, artIndex++)); });
            $(document).on('click', '.remove-article-btn', function () { $(this).closest('.article-row').remove(); });

            // Qualification Repeater Logic
            let qualIndex = 0;
            const container = $('#qualifications-container');
            const template = document.getElementById('qualification-row-template').innerHTML;

            $('#add-qualification-btn').click(function () {
                let rowHtml = template.replace(/INDEX/g, qualIndex++);
                container.append(rowHtml);
            });

            $(document).on('click', '.remove-qualification-btn', function () {
                $(this).closest('.qualification-row').remove();
            });

            // Add one empty row by default
            $('#add-qualification-btn').trigger('click');
        });
    </script>
@endpush
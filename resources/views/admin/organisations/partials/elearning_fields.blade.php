@php
    $elearn = $organisation->elearning ?? new \App\Models\OrganisationElearning();
    $org = $organisation ?? new \App\Models\Organisation();
    $partners = $org->partners ?? collect();
    $instructors = $org->instructors ?? collect();
    $leaders = $org->leaders ?? collect();
    $domains = $org->elearningDomains ?? collect();

    // Arrays
    $platformTypes = old('platform_types', $elearn->platform_types ?? []);
    if (!is_array($platformTypes)) $platformTypes = [];
    $targetAudiences = old('target_audiences', $elearn->target_audiences ?? []);
    if (!is_array($targetAudiences)) $targetAudiences = [];
    $credentialTypes = old('credential_types_offered', $elearn->credential_types_offered ?? []);
    if (!is_array($credentialTypes)) $credentialTypes = [];
    $deliveryModes = old('delivery_modes', $elearn->delivery_modes ?? []);
    if (!is_array($deliveryModes)) $deliveryModes = [];
    $learningFeatures = old('learning_features', $elearn->learning_features ?? []);
    if (!is_array($learningFeatures)) $learningFeatures = [];
    $platformFeatures = old('platform_features', $elearn->platform_features ?? []);
    if (!is_array($platformFeatures)) $platformFeatures = [];
    $careerServices = old('career_services', $elearn->career_services ?? []);
    if (!is_array($careerServices)) $careerServices = [];
    $instructionLanguages = old('instruction_languages', $elearn->instruction_languages ?? []);
    if (!is_array($instructionLanguages)) $instructionLanguages = [];
    $pricingOptions = old('pricing_options', $elearn->pricing_options ?? []);
    if (!is_array($pricingOptions)) $pricingOptions = [];
    $corporateServices = old('corporate_services', $elearn->corporate_services ?? []);
    if (!is_array($corporateServices)) $corporateServices = [];
    $industryRecognitions = old('industry_recognitions', $elearn->industry_recognitions ?? []);
    if (!is_array($industryRecognitions)) $industryRecognitions = [];

    // JSON objects / associative arrays
    $careerStats = old('career_stats', $elearn->career_stats ?? []);
    if (!is_array($careerStats)) $careerStats = [];
    $learnerStats = old('learner_stats', $elearn->learner_stats ?? []);
    if (!is_array($learnerStats)) $learnerStats = [];
    $geoReach = old('geographic_reach', $elearn->geographic_reach ?? []);
    if (!is_array($geoReach)) $geoReach = [];
    $thirdPartyRatings = old('third_party_ratings', $elearn->third_party_ratings ?? []);
    if (!is_array($thirdPartyRatings)) $thirdPartyRatings = [];
    $contactDetails = old('contact_details', $elearn->contact_details ?? []);
    if (!is_array($contactDetails)) $contactDetails = [];
    $socialLinks = old('social_media_links', $elearn->social_media_links ?? []);
    if (!is_array($socialLinks)) $socialLinks = [];
    $appDetails = old('app_details', $elearn->app_details ?? []);
    if (!is_array($appDetails)) $appDetails = [];
    $commDetails = old('community_details', $elearn->community_details ?? []);
    if (!is_array($commDetails)) $commDetails = [];
    $finAid = old('financial_aid_details', $elearn->financial_aid_details ?? []);
    if (!is_array($finAid)) $finAid = [];
    $enrollDetails = old('enrollment_details', $elearn->enrollment_details ?? []);
    if (!is_array($enrollDetails)) $enrollDetails = [];
    $docUrls = old('document_urls', $elearn->document_urls ?? []);
    if (!is_array($docUrls)) $docUrls = [];
@endphp

<!-- E-Learning Platform Specific Fields Section (Type ID: 9) -->
<div id="elearning-fields" class="col-12" style="display: none;">
    <hr class="my-4">
    <div class="d-flex align-items-center mb-3">
        <span class="badge bg-primary fs-6 me-2"><i class="fas fa-laptop-code me-1"></i>E-Learning Platform</span>
        <h5 class="mb-0 text-primary">Platform Specifications & Curriculum Architecture</h5>
    </div>

    <ul class="nav nav-tabs mb-3" id="elearnTab" role="tablist">
        <li class="nav-item">
            <button class="nav-link active" id="el-identity-tab" data-bs-toggle="tab" data-bs-target="#el-identity" type="button" role="tab">
                <i class="fas fa-id-card me-1"></i>1. Identity & Positioning
            </button>
        </li>
        <li class="nav-item">
            <button class="nav-link" id="el-delivery-tab" data-bs-toggle="tab" data-bs-target="#el-delivery" type="button" role="tab">
                <i class="fas fa-chalkboard-teacher me-1"></i>2. Delivery & Learning
            </button>
        </li>
        <li class="nav-item">
            <button class="nav-link" id="el-partners-tab" data-bs-toggle="tab" data-bs-target="#el-partners" type="button" role="tab">
                <i class="fas fa-handshake me-1"></i>3. Domains & Partners
            </button>
        </li>
        <li class="nav-item">
            <button class="nav-link" id="el-instructors-tab" data-bs-toggle="tab" data-bs-target="#el-instructors" type="button" role="tab">
                <i class="fas fa-user-graduate me-1"></i>4. Instructors & Mentors
            </button>
        </li>
        <li class="nav-item">
            <button class="nav-link" id="el-career-tab" data-bs-toggle="tab" data-bs-target="#el-career" type="button" role="tab">
                <i class="fas fa-chart-line me-1"></i>5. Career & Statistics
            </button>
        </li>
        <li class="nav-item">
            <button class="nav-link" id="el-pricing-tab" data-bs-toggle="tab" data-bs-target="#el-pricing" type="button" role="tab">
                <i class="fas fa-tags me-1"></i>6. Pricing & Enterprise
            </button>
        </li>
        <li class="nav-item">
            <button class="nav-link" id="el-admissions-tab" data-bs-toggle="tab" data-bs-target="#el-admissions" type="button" role="tab">
                <i class="fas fa-door-open me-1"></i>7. Admissions & Support
            </button>
        </li>
        <li class="nav-item">
            <button class="nav-link" id="el-leadership-tab" data-bs-toggle="tab" data-bs-target="#el-leadership" type="button" role="tab">
                <i class="fas fa-users-cog me-1"></i>8. Leadership & Docs
            </button>
        </li>
    </ul>

    <div class="tab-content border rounded-bottom p-4 bg-light bg-opacity-25" id="elearnTabContent">

        <!-- ============================================================== -->
        <!-- 1. IDENTITY & POSITIONING (Sections 1, 2, 4, 5) -->
        <!-- ============================================================== -->
        <div class="tab-pane fade show active" id="el-identity" role="tabpanel">
            <div class="row g-3">
                <div class="col-md-6">
                    <label class="form-label fw-bold">Tagline / Slogan</label>
                    <input type="text" name="tagline" class="form-control" value="{{ old('tagline', $elearn->tagline) }}" placeholder="e.g. Building Careers of Tomorrow">
                </div>
                <div class="col-md-6">
                    <label class="form-label fw-bold">Legal Entity Name</label>
                    <input type="text" name="legal_name" class="form-control" value="{{ old('legal_name', $elearn->legal_name) }}" placeholder="e.g. Mayank Kumar EdTech Private Limited">
                </div>
                <div class="col-md-6">
                    <label class="form-label fw-bold">Parent / Holding Organisation</label>
                    <input type="text" name="parent_organisation_name" class="form-control" value="{{ old('parent_organisation_name', $elearn->parent_organisation_name) }}" placeholder="e.g. upGrad Education Private Limited">
                </div>
                <div class="col-md-6">
                    <label class="form-label fw-bold">Platform / Operating Model</label>
                    <select name="organisation_model" class="form-select">
                        <option value="">Select Platform Model</option>
                        @foreach(['MOOC (Massive Open Online Courses)', 'Bootcamp / Cohort-Based', 'Degree Partner / OPM', 'Microlearning / Bite-sized', 'Corporate / Enterprise Training', 'Course Marketplace', 'Hybrid Platform'] as $opt)
                            <option value="{{ $opt }}" {{ old('organisation_model', $elearn->organisation_model) == $opt ? 'selected' : '' }}>{{ $opt }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-6">
                    <label class="form-label fw-bold d-block">Platform Types Offered</label>
                    @foreach(['B2C (Direct to Consumer)', 'B2B (Enterprise / Workforce)', 'B2G (Government Initiatives)'] as $pType)
                        <div class="form-check form-check-inline">
                            <input class="form-check-input" type="checkbox" name="platform_types[]" id="pt_{{ Str::slug($pType) }}" value="{{ $pType }}" {{ in_array($pType, $platformTypes) ? 'checked' : '' }}>
                            <label class="form-check-label" for="pt_{{ Str::slug($pType) }}">{{ $pType }}</label>
                        </div>
                    @endforeach
                </div>
                <div class="col-md-6">
                    <label class="form-label fw-bold">Target Audiences</label>
                    <select name="target_audiences[]" class="form-select select2" multiple data-placeholder="Select target audiences">
                        @foreach(['College Students', 'Working Professionals', 'Career Switchers', 'School Students (K-12)', 'Enterprises / Teams', 'Lifelong Learners', 'Test Prep Aspirants'] as $aud)
                            <option value="{{ $aud }}" {{ in_array($aud, $targetAudiences) ? 'selected' : '' }}>{{ $aud }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="col-md-6">
                    <label class="form-label fw-bold">Credential Types Offered</label>
                    <select name="credential_types_offered[]" class="form-select select2" multiple data-placeholder="Select credentials offered">
                        @foreach(['Free Courses / Audit', 'Verified Completion Certificates', 'Executive Degrees (MBA / PG)', 'Full Online Degrees (UGC Recognised)', 'Executive Post Graduate Diplomas', 'Micro-credentials / Badges', 'Job-ready Bootcamps'] as $cred)
                            <option value="{{ $cred }}" {{ in_array($cred, $credentialTypes) ? 'selected' : '' }}>{{ $cred }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="col-md-3">
                    <label class="form-label fw-bold">Provides Own Programs?</label>
                    <select name="provides_own_programs" class="form-select">
                        <option value="">Select Option</option>
                        @foreach(['Yes (Only Own)', 'No (Only Partner Programs)', 'Both (Own & Partnered)'] as $opt)
                            <option value="{{ $opt }}" {{ old('provides_own_programs', $elearn->provides_own_programs) == $opt ? 'selected' : '' }}>{{ $opt }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="col-md-3">
                    <label class="form-label fw-bold">Program Provider Model</label>
                    <select name="program_provider_model" class="form-select">
                        <option value="">Select Model</option>
                        @foreach(['Self-Created Programs', 'University-Partnered Programs', 'Industry-Partnered Programs', 'Marketplace / Third-Party Instructors', 'Mixed / Multi-Partner Ecosystem'] as $ppm)
                            <option value="{{ $ppm }}" {{ old('program_provider_model', $elearn->program_provider_model) == $ppm ? 'selected' : '' }}>{{ $ppm }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="col-md-6">
                    <label class="form-label fw-bold">Established Year</label>
                    <input type="number" name="established_year" class="form-control" value="{{ old('established_year', $org->established_year) }}" placeholder="e.g. 2015">
                </div>

                <div class="col-md-6">
                    <label class="form-label fw-bold">Official Website URL</label>
                    <input type="url" name="official_website" class="form-control" value="{{ old('official_website', $org->official_website) }}" placeholder="https://www.upgrad.com">
                </div>

                <div class="col-md-12">
                    <label class="form-label fw-bold">Head Office Address / Location</label>
                    <input type="text" name="head_office_location" class="form-control" value="{{ old('head_office_location', $org->head_office_location) }}" placeholder="e.g. Ground Floor, Nishuvi Building, Worli, Mumbai, Maharashtra 400018">
                </div>
            </div>
        </div>

        <!-- ============================================================== -->
        <!-- 2. DELIVERY & LEARNING METHODOLOGY (Sections 7, 8, 13, 19) -->
        <!-- ============================================================== -->
        <div class="tab-pane fade" id="el-delivery" role="tabpanel">
            <div class="row g-3">
                <div class="col-md-6">
                    <label class="form-label fw-bold">Delivery Modes</label>
                    <select name="delivery_modes[]" class="form-select select2" multiple data-placeholder="Select delivery modes">
                        @foreach(['100% Self-paced / Asynchronous', 'Live Online / Interactive Sessions', 'Cohort-based with Fixed Deadlines', 'Blended / Hybrid (Online + Immersion)', 'Weekend Live Batches'] as $dm)
                            <option value="{{ $dm }}" {{ in_array($dm, $deliveryModes) ? 'selected' : '' }}>{{ $dm }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="col-md-6">
                    <label class="form-label fw-bold">Instruction Languages</label>
                    <select name="instruction_languages[]" class="form-select select2" multiple data-placeholder="Select instruction languages">
                        @foreach(['English', 'Hindi', 'Hinglish / Bilingual', 'Spanish', 'French', 'German', 'Subtitles / Captions Available'] as $lang)
                            <option value="{{ $lang }}" {{ in_array($lang, $instructionLanguages) ? 'selected' : '' }}>{{ $lang }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="col-md-12">
                    <label class="form-label fw-bold">Learning Features & Pedagogical Tools</label>
                    <select name="learning_features[]" class="form-select select2" multiple data-placeholder="Select learning features">
                        @foreach(['1:1 Industry Mentorship', 'Live Doubt Clearing Sessions', 'Hands-on Capstone Projects', 'Simulated Real-World Case Studies', 'Graded Assignments & Code Reviews', 'Peer-to-Peer Discussion Forums', 'Cloud Sandbox / Virtual Labs', 'AI-Powered Practice Quizzes'] as $lf)
                            <option value="{{ $lf }}" {{ in_array($lf, $learningFeatures) ? 'selected' : '' }}>{{ $lf }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="col-md-12">
                    <label class="form-label fw-bold">Platform Features & Technology</label>
                    <select name="platform_features[]" class="form-select select2" multiple data-placeholder="Select platform features">
                        @foreach(['AI Tutor / Learning Assistant', 'Gamification & Streaks / Badges', 'Offline Video Downloads (Mobile App)', 'In-browser Coding Sandbox (IDE)', 'Live Class Recording Archive', 'Cross-Device Progress Sync', 'Detailed Learner Analytics Dashboard', 'Interactive Transcripts & Notes'] as $pf)
                            <option value="{{ $pf }}" {{ in_array($pf, $platformFeatures) ? 'selected' : '' }}>{{ $pf }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="col-md-12">
                    <label class="form-label fw-bold">About the Platform</label>
                    <textarea name="about_organisation" class="form-control editor" rows="4">{{ old('about_organisation', $org->about_organisation) }}</textarea>
                </div>

                <div class="col-md-6">
                    <label class="form-label fw-bold">Vision & Mission</label>
                    <textarea name="vision_mission" class="form-control" rows="3">{{ old('vision_mission', $org->vision_mission) }}</textarea>
                </div>

                <div class="col-md-6">
                    <label class="form-label fw-bold">Core Values</label>
                    <div class="core-values-container">
                        @php
                            $coreValues = old('core_values', is_array($org->core_values) ? $org->core_values : []);
                            if (!is_array($coreValues)) {
                                $coreValues = !empty($coreValues) ? [$coreValues] : [];
                            }
                        @endphp
                        @if(count($coreValues) > 0)
                            @foreach($coreValues as $value)
                                <div class="input-group mb-2">
                                    <input type="text" name="core_values[]" class="form-control" value="{{ is_string($value) ? trim($value) : $value }}" placeholder="Enter core value">
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
            </div>
        </div>

        <!-- ============================================================== -->
        <!-- 3. DOMAINS & PARTNERS (Sections 3, 6, 10) -->
        <!-- ============================================================== -->
        <div class="tab-pane fade" id="el-partners" role="tabpanel">
            <!-- 3.1 Domains Covered -->
            <div class="card border mb-4">
                <div class="card-header bg-white d-flex justify-content-between align-items-center">
                    <h6 class="fw-bold mb-0 text-primary"><i class="fas fa-layer-group me-2"></i>Categories / Domains Covered</h6>
                    <button type="button" class="btn btn-sm btn-outline-primary" id="btn-add-domain">
                        <i class="fas fa-plus me-1"></i>Add Domain
                    </button>
                </div>
                <div class="card-body p-3">
                    <div class="table-responsive">
                        <table class="table table-bordered table-sm align-middle" id="table-domains">
                            <thead class="table-light">
                                <tr>
                                    <th>Domain / Category Name</th>
                                    <th width="150" class="text-center">Primary Domain?</th>
                                    <th width="80" class="text-center">Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($domains as $index => $dom)
                                    <tr>
                                        <td>
                                            <input type="text" name="elearning_domains[{{ $index }}][name]" class="form-control form-control-sm" value="{{ $dom->domain_name }}" placeholder="e.g. Data Science, AI & Machine Learning, Management">
                                        </td>
                                        <td class="text-center">
                                            <div class="form-check d-inline-block">
                                                <input class="form-check-input" type="checkbox" name="elearning_domains[{{ $index }}][is_primary]" value="1" {{ $dom->is_primary ? 'checked' : '' }}>
                                            </div>
                                        </td>
                                        <td class="text-center">
                                            <button type="button" class="btn btn-sm btn-danger btn-remove-row"><i class="fas fa-trash"></i></button>
                                        </td>
                                    </tr>
                                @empty
                                    @foreach(['Data Science & AI', 'Management & Leadership', 'Software & Technology', 'Digital Marketing'] as $idx => $sampleDom)
                                        <tr>
                                            <td>
                                                <input type="text" name="elearning_domains[{{ $idx }}][name]" class="form-control form-control-sm" value="{{ $sampleDom }}">
                                            </td>
                                            <td class="text-center">
                                                <div class="form-check d-inline-block">
                                                    <input class="form-check-input" type="checkbox" name="elearning_domains[{{ $idx }}][is_primary]" value="1" {{ $idx == 0 ? 'checked' : '' }}>
                                                </div>
                                            </td>
                                            <td class="text-center">
                                                <button type="button" class="btn btn-sm btn-danger btn-remove-row"><i class="fas fa-trash"></i></button>
                                            </td>
                                        </tr>
                                    @endforeach
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <!-- 3.2 Academic & Industry Partners -->
            <div class="card border">
                <div class="card-header bg-white d-flex justify-content-between align-items-center">
                    <h6 class="fw-bold mb-0 text-primary"><i class="fas fa-university me-2"></i>Academic & Industry Partners (IITs, IIMs, Tech Giants, etc.)</h6>
                    <button type="button" class="btn btn-sm btn-outline-primary" id="btn-add-partner">
                        <i class="fas fa-plus me-1"></i>Add Partner
                    </button>
                </div>
                <div class="card-body p-3">
                    <div id="partners-container">
                        @forelse($partners as $pIndex => $partner)
                            <div class="border rounded p-3 mb-3 bg-white partner-card position-relative">
                                <button type="button" class="btn btn-sm btn-danger position-absolute top-0 end-0 m-2 btn-remove-partner"><i class="fas fa-times"></i></button>
                                <div class="row g-2">
                                    <div class="col-md-4">
                                        <label class="form-label form-label-sm fw-bold">Partner Name</label>
                                        <input type="text" name="partners[{{ $pIndex }}][partner_name]" class="form-control form-control-sm" value="{{ $partner->partner_name }}" placeholder="e.g. IIT Madras, Microsoft, Wharton Online">
                                    </div>
                                    <div class="col-md-3">
                                        <label class="form-label form-label-sm fw-bold">Partner Type</label>
                                        <select name="partners[{{ $pIndex }}][partner_type]" class="form-select form-select-sm">
                                            @foreach(['University', 'Institute', 'Corporate / Tech Giant', 'Certification Body', 'Government Body', 'Hiring Partner'] as $pType)
                                                <option value="{{ $pType }}" {{ $partner->partner_type == $pType ? 'selected' : '' }}>{{ $pType }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div class="col-md-3">
                                        <label class="form-label form-label-sm fw-bold">Relationship Type</label>
                                        <select name="partners[{{ $pIndex }}][relationship_type]" class="form-select form-select-sm">
                                            @foreach(['Degree Partner', 'Certificate Partner', 'Curriculum Partner', 'Hiring Partner', 'Accreditation Partner'] as $rType)
                                                <option value="{{ $rType }}" {{ $partner->relationship_type == $rType ? 'selected' : '' }}>{{ $rType }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div class="col-md-2">
                                        <label class="form-label form-label-sm fw-bold">Programs Count</label>
                                        <input type="number" name="partners[{{ $pIndex }}][programs_count]" class="form-control form-control-sm" value="{{ $partner->programs_count }}" placeholder="e.g. 5">
                                    </div>
                                    <div class="col-md-5">
                                        <label class="form-label form-label-sm fw-bold">Partner Website URL</label>
                                        <input type="url" name="partners[{{ $pIndex }}][partner_website]" class="form-control form-control-sm" value="{{ $partner->partner_website }}" placeholder="https://...">
                                    </div>
                                    <div class="col-md-4">
                                        <label class="form-label form-label-sm fw-bold">Co-branded Credential?</label>
                                        <div class="form-check form-switch mt-1">
                                            <input class="form-check-input" type="checkbox" name="partners[{{ $pIndex }}][co_branded_credential]" value="1" {{ $partner->co_branded_credential ? 'checked' : '' }}>
                                            <label class="form-check-label small">Issued with Partner Logo</label>
                                        </div>
                                    </div>
                                    <div class="col-md-12">
                                        <label class="form-label form-label-sm fw-bold">Brief Description / Partnership Scope</label>
                                        <input type="text" name="partners[{{ $pIndex }}][description]" class="form-control form-control-sm" value="{{ $partner->description }}" placeholder="e.g. Offering co-certified Advanced Executive Program in Cloud & DevOps">
                                    </div>
                                </div>
                            </div>
                        @empty
                            <p class="text-muted small mb-0 p-2 text-center" id="no-partners-msg">No partners added yet. Click "Add Partner" to link universities or tech companies.</p>
                        @endforelse
                    </div>
                </div>
            </div>
        </div>

        <!-- ============================================================== -->
        <!-- 4. INSTRUCTORS & MENTORS (Section 20) -->
        <!-- ============================================================== -->
        <div class="tab-pane fade" id="el-instructors" role="tabpanel">
            <div class="card border">
                <div class="card-header bg-white d-flex justify-content-between align-items-center">
                    <h6 class="fw-bold mb-0 text-primary"><i class="fas fa-chalkboard-teacher me-2"></i>Instructors, Mentors & Industry Experts</h6>
                    <button type="button" class="btn btn-sm btn-outline-primary" id="btn-add-instructor">
                        <i class="fas fa-plus me-1"></i>Add Instructor / Mentor
                    </button>
                </div>
                <div class="card-body p-3">
                    <div id="instructors-container">
                        @forelse($instructors as $insIdx => $instructor)
                            <div class="border rounded p-3 mb-3 bg-white instructor-card position-relative">
                                <button type="button" class="btn btn-sm btn-danger position-absolute top-0 end-0 m-2 btn-remove-instructor"><i class="fas fa-times"></i></button>
                                <div class="row g-2">
                                    <div class="col-md-4">
                                        <label class="form-label form-label-sm fw-bold">Full Name</label>
                                        <input type="text" name="instructors[{{ $insIdx }}][name]" class="form-control form-control-sm" value="{{ $instructor->name }}" placeholder="e.g. Dr. Jane Doe">
                                    </div>
                                    <div class="col-md-4">
                                        <label class="form-label form-label-sm fw-bold">Designation / Role</label>
                                        <input type="text" name="instructors[{{ $insIdx }}][designation]" class="form-control form-control-sm" value="{{ $instructor->designation }}" placeholder="e.g. Principal AI Scientist / Adjunct Professor">
                                    </div>
                                    <div class="col-md-4">
                                        <label class="form-label form-label-sm fw-bold">Company / Institution</label>
                                        <input type="text" name="instructors[{{ $insIdx }}][current_company_or_institution]" class="form-control form-control-sm" value="{{ $instructor->current_company_or_institution }}" placeholder="e.g. Google / Stanford">
                                    </div>
                                    <div class="col-md-3">
                                        <label class="form-label form-label-sm fw-bold">Experience (Years)</label>
                                        <input type="number" name="instructors[{{ $insIdx }}][experience_years]" class="form-control form-control-sm" value="{{ $instructor->experience_years }}" placeholder="12">
                                    </div>
                                    <div class="col-md-3">
                                        <label class="form-label form-label-sm fw-bold">Rating (0 - 5.0)</label>
                                        <input type="number" step="0.1" min="0" max="5" name="instructors[{{ $insIdx }}][rating]" class="form-control form-control-sm" value="{{ $instructor->rating }}" placeholder="4.9">
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label form-label-sm fw-bold">LinkedIn Profile URL</label>
                                        <input type="url" name="instructors[{{ $insIdx }}][linkedin_url]" class="form-control form-control-sm" value="{{ $instructor->linkedin_url }}" placeholder="https://linkedin.com/in/...">
                                    </div>
                                    <div class="col-md-12">
                                        <label class="form-label form-label-sm fw-bold">Short Bio</label>
                                        <input type="text" name="instructors[{{ $insIdx }}][bio]" class="form-control form-control-sm" value="{{ $instructor->bio }}" placeholder="Brief summary of expertise, past teaching, research papers...">
                                    </div>
                                </div>
                            </div>
                        @empty
                            <p class="text-muted small mb-0 p-2 text-center" id="no-instructors-msg">No instructors listed yet. Click "Add Instructor / Mentor" to add top faculty.</p>
                        @endforelse
                    </div>
                </div>
            </div>
        </div>

        <!-- ============================================================== -->
        <!-- 5. CAREER & SCALE STATISTICS (Sections 9, 11, 16) -->
        <!-- ============================================================== -->
        <div class="tab-pane fade" id="el-career" role="tabpanel">
            <div class="row g-3">
                <div class="col-12">
                    <h6 class="fw-bold text-primary mb-2"><i class="fas fa-briefcase me-2"></i>Career Support & Placement Services</h6>
                    <label class="form-label fw-bold">Services Provided</label>
                    <select name="career_services[]" class="form-select select2" multiple data-placeholder="Select career services">
                        @foreach(['1:1 Resume & LinkedIn Review', 'Mock Behavioral & Technical Interviews', 'Dedicated Career Coach / Mentor', 'Exclusive Placement Portal & Job Board', 'Hiring Hackathons & Live Pitch Days', 'Interview Guarantee / Assured Drives', 'Salary Negotiation Support', 'Alumni Placement Networking'] as $cs)
                            <option value="{{ $cs }}" {{ in_array($cs, $careerServices) ? 'selected' : '' }}>{{ $cs }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="col-md-3">
                    <label class="form-label fw-bold">Learners Placed Count</label>
                    <input type="text" name="career_stats[learners_placed]" class="form-control" value="{{ $careerStats['learners_placed'] ?? '' }}" placeholder="e.g. 50,000+">
                </div>
                <div class="col-md-3">
                    <label class="form-label fw-bold">Highest Package (LPA / $)</label>
                    <input type="text" name="career_stats[highest_salary]" class="form-control" value="{{ $careerStats['highest_salary'] ?? '' }}" placeholder="e.g. ₹73 LPA or $150K">
                </div>
                <div class="col-md-3">
                    <label class="form-label fw-bold">Average Package</label>
                    <input type="text" name="career_stats[average_salary]" class="form-control" value="{{ $careerStats['average_salary'] ?? '' }}" placeholder="e.g. ₹12.5 LPA">
                </div>
                <div class="col-md-3">
                    <label class="form-label fw-bold">Average Salary Hike (%)</label>
                    <input type="text" name="career_stats[average_hike_percentage]" class="form-control" value="{{ $careerStats['average_hike_percentage'] ?? '' }}" placeholder="e.g. 55%">
                </div>
                <div class="col-md-3">
                    <label class="form-label fw-bold">Hiring Partners Count</label>
                    <input type="text" name="career_stats[hiring_partners_count]" class="form-control" value="{{ $careerStats['hiring_partners_count'] ?? '' }}" placeholder="e.g. 1,400+">
                </div>
                <div class="col-md-3">
                    <label class="form-label fw-bold">Stats Source Year</label>
                    <input type="text" name="career_stats[stats_year]" class="form-control" value="{{ $careerStats['stats_year'] ?? date('Y') }}" placeholder="e.g. 2024">
                </div>

                <div class="col-12"><hr class="my-2"></div>

                <div class="col-12">
                    <h6 class="fw-bold text-primary mb-2"><i class="fas fa-globe me-2"></i>Platform Scale & Learner Statistics</h6>
                </div>
                <div class="col-md-3">
                    <label class="form-label fw-bold">Total Learners Enrolled</label>
                    <input type="text" name="learner_stats[total_learners]" class="form-control" value="{{ $learnerStats['total_learners'] ?? '' }}" placeholder="e.g. 3 Million+">
                </div>
                <div class="col-md-3">
                    <label class="form-label fw-bold">Monthly Active Learners</label>
                    <input type="text" name="learner_stats[active_learners]" class="form-control" value="{{ $learnerStats['active_learners'] ?? '' }}" placeholder="e.g. 500,000+">
                </div>
                <div class="col-md-3">
                    <label class="form-label fw-bold">Total Courses Count</label>
                    <input type="text" name="learner_stats[total_courses]" class="form-control" value="{{ $learnerStats['total_courses'] ?? '' }}" placeholder="e.g. 250+">
                </div>
                <div class="col-md-3">
                    <label class="form-label fw-bold">Degree / Bootcamps Count</label>
                    <input type="text" name="learner_stats[total_programs]" class="form-control" value="{{ $learnerStats['total_programs'] ?? '' }}" placeholder="e.g. 45+">
                </div>
                <div class="col-md-3">
                    <label class="form-label fw-bold">Certificates Issued</label>
                    <input type="text" name="learner_stats[certificates_issued]" class="form-control" value="{{ $learnerStats['certificates_issued'] ?? '' }}" placeholder="e.g. 1.2M+">
                </div>
                <div class="col-md-3">
                    <label class="form-label fw-bold">Alumni Network Size</label>
                    <input type="text" name="learner_stats[alumni_network_size]" class="form-control" value="{{ $learnerStats['alumni_network_size'] ?? '' }}" placeholder="e.g. 85,000+">
                </div>

                <div class="col-12"><hr class="my-2"></div>

                <div class="col-12">
                    <h6 class="fw-bold text-primary mb-2"><i class="fas fa-star me-2"></i>Ratings & Trust Score</h6>
                </div>
                <div class="col-md-3">
                    <label class="form-label fw-bold">Platform Rating (0 - 5.0)</label>
                    <input type="number" step="0.1" min="0" max="5" name="average_rating" class="form-control" value="{{ old('average_rating', $org->average_rating) }}" placeholder="4.7">
                </div>
                <div class="col-md-3">
                    <label class="form-label fw-bold">Total Reviews Count</label>
                    <input type="number" name="total_reviews" class="form-control" value="{{ old('total_reviews', $org->total_reviews) }}" placeholder="25000">
                </div>
                <div class="col-md-3">
                    <label class="form-label fw-bold">Trustpilot Rating</label>
                    <input type="text" name="third_party_ratings[trustpilot_score]" class="form-control" value="{{ $thirdPartyRatings['trustpilot_score'] ?? '' }}" placeholder="4.6 / 5">
                </div>
                <div class="col-md-3">
                    <label class="form-label fw-bold">CourseReport Score</label>
                    <input type="text" name="third_party_ratings[coursereport_score]" class="form-control" value="{{ $thirdPartyRatings['coursereport_score'] ?? '' }}" placeholder="4.8 / 5">
                </div>
            </div>
        </div>

        <!-- ============================================================== -->
        <!-- 6. PRICING, CORPORATE & FINANCIAL AID (Sections 14, 15, 24) -->
        <!-- ============================================================== -->
        <div class="tab-pane fade" id="el-pricing" role="tabpanel">
            <div class="row g-3">
                <div class="col-md-6">
                    <label class="form-label fw-bold">Business / Monetization Model</label>
                    <select name="business_model" class="form-select">
                        <option value="">Select Business Model</option>
                        @foreach(['Free (Ad-supported / Non-profit)', 'Freemium (Free audit, paid certificate)', 'Direct Paid Per Course / Program', 'Subscription (Monthly / Annual All-Access)', 'Pay After Placement / Income Share Agreement', 'Enterprise License (B2B)'] as $bm)
                            <option value="{{ $bm }}" {{ old('business_model', $elearn->business_model) == $bm ? 'selected' : '' }}>{{ $bm }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="col-md-6">
                    <label class="form-label fw-bold">Pricing Options Available</label>
                    <select name="pricing_options[]" class="form-select select2" multiple data-placeholder="Select pricing options">
                        @foreach(['Free Audit Mode', 'One-time Course Purchase', 'Monthly All-Access Subscription', 'Annual Platform Pass', 'Program-Based Tuition Fee', '0% EMI Options', 'Income Share Agreement (ISA)'] as $po)
                            <option value="{{ $po }}" {{ in_array($po, $pricingOptions) ? 'selected' : '' }}>{{ $po }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="col-12"><hr class="my-2"></div>

                <div class="col-12">
                    <h6 class="fw-bold text-primary mb-2"><i class="fas fa-building me-2"></i>Corporate & Enterprise Services (B2B)</h6>
                </div>
                <div class="col-md-4">
                    <div class="form-check form-switch mt-2">
                        <input class="form-check-input" type="checkbox" name="has_enterprise_offering" id="has_ent" value="1" {{ old('has_enterprise_offering', $elearn->has_enterprise_offering) ? 'checked' : '' }}>
                        <label class="form-check-label fw-bold" for="has_ent">Offers Corporate Upskilling (Enterprise B2B)</label>
                    </div>
                </div>
                <div class="col-md-8">
                    <label class="form-label fw-bold">Corporate Services Offered</label>
                    <select name="corporate_services[]" class="form-select select2" multiple data-placeholder="Select corporate offerings">
                        @foreach(['Custom Workforce Upskilling Pathways', 'LMS Integration (SCORM / xAPI / SSO)', 'Manager & Executive Analytics Dashboards', 'Pre-hiring Technical Skill Assessments', 'Tailored Corporate Content Co-creation', 'Dedicated Enterprise Customer Success Manager'] as $csrv)
                            <option value="{{ $csrv }}" {{ in_array($csrv, $corporateServices) ? 'selected' : '' }}>{{ $csrv }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="col-12"><hr class="my-2"></div>

                <div class="col-12">
                    <h6 class="fw-bold text-primary mb-2"><i class="fas fa-hand-holding-usd me-2"></i>Scholarships, Financial Aid & Financing</h6>
                </div>
                <div class="col-md-3">
                    <div class="form-check form-switch mt-4">
                        <input class="form-check-input" type="checkbox" name="financial_aid_details[offers_aid]" id="offers_aid" value="1" {{ !empty($finAid['offers_aid']) ? 'checked' : '' }}>
                        <label class="form-check-label fw-bold" for="offers_aid">Financial Aid Available</label>
                    </div>
                </div>
                <div class="col-md-3">
                    <label class="form-label fw-bold">Max Scholarship Percentage</label>
                    <input type="text" name="financial_aid_details[max_scholarship_percent]" class="form-control" value="{{ $finAid['max_scholarship_percent'] ?? '' }}" placeholder="e.g. Up to 50%">
                </div>
                <div class="col-md-3">
                    <label class="form-label fw-bold">EMI & Loan Partners</label>
                    <input type="text" name="financial_aid_details[emi_partners]" class="form-control" value="{{ $finAid['emi_partners'] ?? '' }}" placeholder="e.g. Propelld, LiquiLoans, Eduvanz">
                </div>
                <div class="col-md-3">
                    <div class="form-check form-switch mt-4">
                        <input class="form-check-input" type="checkbox" name="financial_aid_details[isa_available]" id="isa_avail" value="1" {{ !empty($finAid['isa_available']) ? 'checked' : '' }}>
                        <label class="form-check-label fw-bold" for="isa_avail">ISA (Pay After Placement) Available</label>
                    </div>
                </div>
            </div>
        </div>

        <!-- ============================================================== -->
        <!-- 7. ADMISSIONS, REACH & APP (Sections 12, 21, 22, 23, 25, 26) -->
        <!-- ============================================================== -->
        <div class="tab-pane fade" id="el-admissions" role="tabpanel">
            <div class="row g-3">
                <div class="col-12">
                    <h6 class="fw-bold text-primary mb-2"><i class="fas fa-user-check me-2"></i>Admissions & Enrollment Process</h6>
                </div>
                <div class="col-md-3">
                    <label class="form-label fw-bold">Typical Enrollment Route</label>
                    <select name="enrollment_details[enrollment_type]" class="form-select">
                        <option value="">Select Route</option>
                        @foreach(['Direct Enrollment / Instant Access', 'Online Application & Profile Screening', 'Entrance / Aptitude Assessment Required', 'Panel Interview / Cohort Selection'] as $er)
                            <option value="{{ $er }}" {{ ($enrollDetails['enrollment_type'] ?? '') == $er ? 'selected' : '' }}>{{ $er }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-3">
                    <label class="form-label fw-bold">Intake Frequency</label>
                    <select name="enrollment_details[typical_intakes]" class="form-select">
                        <option value="">Select Intake</option>
                        @foreach(['Rolling / Start Anytime', 'Monthly Cohorts', 'Quarterly Cohorts', 'Fixed Academic Dates (Spring / Fall)'] as $intk)
                            <option value="{{ $intk }}" {{ ($enrollDetails['typical_intakes'] ?? '') == $intk ? 'selected' : '' }}>{{ $intk }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-3">
                    <label class="form-label fw-bold">Application Fee</label>
                    <input type="text" name="enrollment_details[application_fee]" class="form-control" value="{{ $enrollDetails['application_fee'] ?? 'Free' }}" placeholder="Free / ₹1,000">
                </div>
                <div class="col-md-3">
                    <label class="form-label fw-bold">Refund Window (Days)</label>
                    <input type="text" name="enrollment_details[refund_window_days]" class="form-control" value="{{ $enrollDetails['refund_window_days'] ?? '' }}" placeholder="e.g. 7 or 14 Days">
                </div>
                <div class="col-md-6">
                    <label class="form-label fw-bold">Eligibility Criteria Summary</label>
                    <textarea name="enrollment_details[eligibility_summary]" class="form-control" rows="2" placeholder="e.g. Bachelor's degree with min 50% marks; coding background preferred for Advanced Bootcamps">{{ $enrollDetails['eligibility_summary'] ?? '' }}</textarea>
                </div>
                <div class="col-md-6">
                    <label class="form-label fw-bold">Refund Policy Summary</label>
                    <textarea name="enrollment_details[refund_policy_summary]" class="form-control" rows="2" placeholder="e.g. 100% refund within 14 days of cohort start, subject to admin fee">{{ $enrollDetails['refund_policy_summary'] ?? '' }}</textarea>
                </div>

                <div class="col-12"><hr class="my-2"></div>

                <div class="col-12">
                    <h6 class="fw-bold text-primary mb-2"><i class="fas fa-headset me-2"></i>Contact & Student Support</h6>
                </div>
                <div class="col-md-4">
                    <label class="form-label fw-bold">Support Email</label>
                    <input type="email" name="contact_details[support_email]" class="form-control" value="{{ $contactDetails['support_email'] ?? '' }}" placeholder="support@upgrad.com">
                </div>
                <div class="col-md-4">
                    <label class="form-label fw-bold">Admissions Hotline / Phone</label>
                    <input type="text" name="contact_details[admissions_phone]" class="form-control" value="{{ $contactDetails['admissions_phone'] ?? '' }}" placeholder="1800-210-2020">
                </div>
                <div class="col-md-4">
                    <label class="form-label fw-bold">WhatsApp Support Number</label>
                    <input type="text" name="contact_details[whatsapp_number]" class="form-control" value="{{ $contactDetails['whatsapp_number'] ?? '' }}" placeholder="+91 9876543210">
                </div>
                <div class="col-md-4">
                    <label class="form-label fw-bold">Support Operational Hours</label>
                    <input type="text" name="contact_details[support_hours]" class="form-control" value="{{ $contactDetails['support_hours'] ?? '' }}" placeholder="Mon - Sat, 9:00 AM - 9:00 PM IST">
                </div>
                <div class="col-md-4">
                    <label class="form-label fw-bold">Help Centre / FAQ URL</label>
                    <input type="url" name="contact_details[help_center_url]" class="form-control" value="{{ $contactDetails['help_center_url'] ?? '' }}" placeholder="https://help.upgrad.com">
                </div>
                <div class="col-md-4">
                    <div class="form-check form-switch mt-4">
                        <input class="form-check-input" type="checkbox" name="contact_details[live_chat_available]" id="live_chat" value="1" {{ !empty($contactDetails['live_chat_available']) ? 'checked' : '' }}>
                        <label class="form-check-label fw-bold" for="live_chat">24/7 Live Chat Support Available</label>
                    </div>
                </div>

                <div class="col-12"><hr class="my-2"></div>

                <div class="col-12">
                    <h6 class="fw-bold text-primary mb-2"><i class="fas fa-mobile-alt me-2"></i>Mobile Apps & Digital Platforms</h6>
                </div>
                <div class="col-md-6">
                    <label class="form-label fw-bold">Android App (Play Store) URL</label>
                    <input type="url" name="app_details[android_app_url]" class="form-control" value="{{ $appDetails['android_app_url'] ?? '' }}" placeholder="https://play.google.com/store/apps/details?id=...">
                </div>
                <div class="col-md-3">
                    <label class="form-label fw-bold">Play Store Rating</label>
                    <input type="text" name="app_details[android_rating]" class="form-control" value="{{ $appDetails['android_rating'] ?? '' }}" placeholder="4.5 ★">
                </div>
                <div class="col-md-3">
                    <label class="form-label fw-bold">Downloads Count</label>
                    <input type="text" name="app_details[android_downloads]" class="form-control" value="{{ $appDetails['android_downloads'] ?? '' }}" placeholder="1M+ Downloads">
                </div>
                <div class="col-md-6">
                    <label class="form-label fw-bold">iOS App (App Store) URL</label>
                    <input type="url" name="app_details[ios_app_url]" class="form-control" value="{{ $appDetails['ios_app_url'] ?? '' }}" placeholder="https://apps.apple.com/app/...">
                </div>
                <div class="col-md-3">
                    <label class="form-label fw-bold">App Store Rating</label>
                    <input type="text" name="app_details[ios_rating]" class="form-control" value="{{ $appDetails['ios_rating'] ?? '' }}" placeholder="4.7 ★">
                </div>
                <div class="col-md-3">
                    <div class="form-check form-switch mt-4">
                        <input class="form-check-input" type="checkbox" name="app_details[offline_downloads]" id="app_offl" value="1" {{ !empty($appDetails['offline_downloads']) ? 'checked' : '' }}>
                        <label class="form-check-label fw-bold" for="app_offl">Supports Offline Viewing</label>
                    </div>
                </div>

                <div class="col-12"><hr class="my-2"></div>

                <div class="col-12">
                    <h6 class="fw-bold text-primary mb-2"><i class="fas fa-comments me-2"></i>Community & Social Presence</h6>
                </div>
                <div class="col-md-3">
                    <label class="form-label fw-bold">Community Platform</label>
                    <select name="community_details[platform_type]" class="form-select">
                        <option value="">Select Platform</option>
                        @foreach(['Discord Server', 'Slack Community', 'Discourse Forum', 'In-App Custom Forum', 'WhatsApp / Telegram Groups'] as $cp)
                            <option value="{{ $cp }}" {{ ($commDetails['platform_type'] ?? '') == $cp ? 'selected' : '' }}>{{ $cp }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-3">
                    <label class="form-label fw-bold">Community Members Count</label>
                    <input type="text" name="community_details[member_count]" class="form-control" value="{{ $commDetails['member_count'] ?? '' }}" placeholder="e.g. 150,000+">
                </div>
                <div class="col-md-6">
                    <label class="form-label fw-bold">Community Invitation URL</label>
                    <input type="url" name="community_details[community_url]" class="form-control" value="{{ $commDetails['community_url'] ?? '' }}" placeholder="https://discord.gg/...">
                </div>

                <div class="col-md-4">
                    <label class="form-label fw-bold">LinkedIn Page URL</label>
                    <input type="url" name="social_media_links[linkedin]" class="form-control" value="{{ $socialLinks['linkedin'] ?? '' }}" placeholder="https://linkedin.com/company/...">
                </div>
                <div class="col-md-4">
                    <label class="form-label fw-bold">YouTube Channel URL</label>
                    <input type="url" name="social_media_links[youtube]" class="form-control" value="{{ $socialLinks['youtube'] ?? '' }}" placeholder="https://youtube.com/@...">
                </div>
                <div class="col-md-4">
                    <label class="form-label fw-bold">Instagram URL</label>
                    <input type="url" name="social_media_links[instagram]" class="form-control" value="{{ $socialLinks['instagram'] ?? '' }}" placeholder="https://instagram.com/...">
                </div>
            </div>
        </div>

        <!-- ============================================================== -->
        <!-- 8. LEADERSHIP, MEDIA, SEO & DOCS (Sections 17, 18, 27, 28, 30) -->
        <!-- ============================================================== -->
        <div class="tab-pane fade" id="el-leadership" role="tabpanel">
            <!-- 8.1 Leadership Team -->
            <div class="card border mb-4">
                <div class="card-header bg-white d-flex justify-content-between align-items-center">
                    <h6 class="fw-bold mb-0 text-primary"><i class="fas fa-user-tie me-2"></i>Founders, Executives & Leadership Team</h6>
                    <button type="button" class="btn btn-sm btn-outline-primary" id="btn-add-leader">
                        <i class="fas fa-plus me-1"></i>Add Executive
                    </button>
                </div>
                <div class="card-body p-3">
                    <div id="leaders-container">
                        @forelse($leaders as $lIdx => $leader)
                            <div class="border rounded p-3 mb-3 bg-white leader-card position-relative">
                                <button type="button" class="btn btn-sm btn-danger position-absolute top-0 end-0 m-2 btn-remove-leader"><i class="fas fa-times"></i></button>
                                <div class="row g-2">
                                    <div class="col-md-4">
                                        <label class="form-label form-label-sm fw-bold">Executive Name</label>
                                        <input type="text" name="leaders[{{ $lIdx }}][name]" class="form-control form-control-sm" value="{{ $leader->name }}" placeholder="e.g. Ronnie Screwvala">
                                    </div>
                                    <div class="col-md-4">
                                        <label class="form-label form-label-sm fw-bold">Designation</label>
                                        <input type="text" name="leaders[{{ $lIdx }}][designation]" class="form-control form-control-sm" value="{{ $leader->designation }}" placeholder="e.g. Co-Founder & Chairperson">
                                    </div>
                                    <div class="col-md-4">
                                        <label class="form-label form-label-sm fw-bold">Is Founder / Co-Founder?</label>
                                        <div class="form-check form-switch mt-1">
                                            <input class="form-check-input" type="checkbox" name="leaders[{{ $lIdx }}][is_founder]" value="1" {{ $leader->is_founder ? 'checked' : '' }}>
                                            <label class="form-check-label small">Yes, Founder</label>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label form-label-sm fw-bold">LinkedIn Profile URL</label>
                                        <input type="url" name="leaders[{{ $lIdx }}][linkedin_url]" class="form-control form-control-sm" value="{{ $leader->linkedin_url }}" placeholder="https://linkedin.com/in/...">
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label form-label-sm fw-bold">Short Executive Bio</label>
                                        <input type="text" name="leaders[{{ $lIdx }}][bio]" class="form-control form-control-sm" value="{{ $leader->bio }}" placeholder="e.g. Serial entrepreneur, pioneer in media and higher education technology.">
                                    </div>
                                </div>
                            </div>
                        @empty
                            <p class="text-muted small mb-0 p-2 text-center" id="no-leaders-msg">No leadership profiles added yet. Click "Add Executive" to highlight founders and leaders.</p>
                        @endforelse
                    </div>
                </div>
            </div>

            <!-- 8.2 Video & Standards -->
            <div class="row g-3">
                <div class="col-md-6">
                    <label class="form-label fw-bold">Promotional Video URL (YouTube / Vimeo)</label>
                    <input type="url" name="promotional_video_url" class="form-control" value="{{ old('promotional_video_url', $elearn->promotional_video_url) }}" placeholder="https://www.youtube.com/watch?v=...">
                </div>

                <div class="col-md-6">
                    <label class="form-label fw-bold">Industry Recognitions & Standards</label>
                    <select name="industry_recognitions[]" class="form-select select2" multiple data-placeholder="Select recognitions">
                        @foreach(['ISO 9001 Certified', 'NSDC Partner', 'Skill India Affiliated', 'NASSCOM FutureSkills Partner', 'AWS Training Partner', 'Google Cloud Authorized Partner', 'Microsoft Learning Partner'] as $ir)
                            <option value="{{ $ir }}" {{ in_array($ir, $industryRecognitions) ? 'selected' : '' }}>{{ $ir }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="col-12"><hr class="my-2"></div>

                <div class="col-12">
                    <h6 class="fw-bold text-primary mb-2"><i class="fas fa-file-pdf me-2"></i>Documents, Policies & Downloads</h6>
                </div>
                <div class="col-md-3">
                    <label class="form-label fw-bold">Platform Brochure URL</label>
                    <input type="url" name="document_urls[brochure_url]" class="form-control" value="{{ $docUrls['brochure_url'] ?? '' }}" placeholder="https://...">
                </div>
                <div class="col-md-3">
                    <label class="form-label fw-bold">Annual Placement Report URL</label>
                    <input type="url" name="document_urls[placement_report_url]" class="form-control" value="{{ $docUrls['placement_report_url'] ?? '' }}" placeholder="https://...">
                </div>
                <div class="col-md-3">
                    <label class="form-label fw-bold">Enterprise / B2B Catalog URL</label>
                    <input type="url" name="document_urls[corporate_catalog_url]" class="form-control" value="{{ $docUrls['corporate_catalog_url'] ?? '' }}" placeholder="https://...">
                </div>
                <div class="col-md-3">
                    <label class="form-label fw-bold">Terms & Privacy URL</label>
                    <input type="url" name="document_urls[terms_privacy_url]" class="form-control" value="{{ $docUrls['terms_privacy_url'] ?? '' }}" placeholder="https://...">
                </div>

                <div class="col-12"><hr class="my-2"></div>

                <div class="col-12">
                    <h6 class="fw-bold text-primary mb-2"><i class="fas fa-search me-2"></i>SEO & Verification</h6>
                </div>
                <div class="col-md-6">
                    <label class="form-label fw-bold">Meta Title</label>
                    <input type="text" name="meta_title" class="form-control" value="{{ old('meta_title', $org->meta_title) }}">
                </div>
                <div class="col-md-6">
                    <label class="form-label fw-bold">Canonical URL</label>
                    <input type="url" name="canonical_url" class="form-control" value="{{ old('canonical_url', $org->canonical_url) }}">
                </div>
                <div class="col-md-12">
                    <label class="form-label fw-bold">Meta Description</label>
                    <textarea name="meta_description" class="form-control" rows="2">{{ old('meta_description', $org->meta_description) }}</textarea>
                </div>
                <div class="col-md-6">
                    <label class="form-label fw-bold">Focus Keywords</label>
                    <input type="text" name="focus_keywords" class="form-control" value="{{ old('focus_keywords', is_array($org->focus_keywords) ? implode(',', $org->focus_keywords) : $org->focus_keywords) }}" placeholder="e.g. online mba, data science bootcamp, machine learning course">
                </div>
                <div class="col-md-3">
                    <label class="form-label fw-bold">Verification Status</label>
                    <select name="verification_status" class="form-select">
                        @foreach(['Pending', 'Verified', 'Rejected'] as $opt)
                            <option value="{{ $opt }}" {{ old('verification_status', $org->verification_status) == $opt ? 'selected' : '' }}>{{ $opt }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-3">
                    <label class="form-label fw-bold">Status</label>
                    <select name="status" class="form-select">
                        @foreach(['Active', 'Inactive', 'Archived'] as $opt)
                            <option value="{{ $opt }}" {{ old('status', $org->status) == $opt ? 'selected' : '' }}>{{ $opt }}</option>
                        @endforeach
                    </select>
                </div>
            </div>
        </div>

    </div>
</div>

{{-- Dynamic Row Scripts for E-Learning Repeatables --}}
@push('js')
<script>
document.addEventListener('DOMContentLoaded', function () {
    // 1. Domains Dynamic Rows
    const btnAddDomain = document.getElementById('btn-add-domain');
    const tableDomainsBody = document.querySelector('#table-domains tbody');
    if (btnAddDomain && tableDomainsBody) {
        btnAddDomain.addEventListener('click', function () {
            const index = tableDomainsBody.querySelectorAll('tr').length;
            const tr = document.createElement('tr');
            tr.innerHTML = `
                <td>
                    <input type="text" name="elearning_domains[${index}][name]" class="form-control form-control-sm" placeholder="e.g. Cloud Computing">
                </td>
                <td class="text-center">
                    <div class="form-check d-inline-block">
                        <input class="form-check-input" type="checkbox" name="elearning_domains[${index}][is_primary]" value="1">
                    </div>
                </td>
                <td class="text-center">
                    <button type="button" class="btn btn-sm btn-danger btn-remove-row"><i class="fas fa-trash"></i></button>
                </td>
            `;
            tableDomainsBody.appendChild(tr);
        });
    }

    document.addEventListener('click', function (e) {
        if (e.target.closest('.btn-remove-row')) {
            e.target.closest('tr').remove();
        }
    });

    // 2. Partners Dynamic Rows
    const btnAddPartner = document.getElementById('btn-add-partner');
    const partnersContainer = document.getElementById('partners-container');
    if (btnAddPartner && partnersContainer) {
        btnAddPartner.addEventListener('click', function () {
            const noMsg = document.getElementById('no-partners-msg');
            if (noMsg) noMsg.remove();

            const pIdx = partnersContainer.querySelectorAll('.partner-card').length + Date.now();
            const div = document.createElement('div');
            div.className = 'border rounded p-3 mb-3 bg-white partner-card position-relative';
            div.innerHTML = `
                <button type="button" class="btn btn-sm btn-danger position-absolute top-0 end-0 m-2 btn-remove-partner"><i class="fas fa-times"></i></button>
                <div class="row g-2">
                    <div class="col-md-4">
                        <label class="form-label form-label-sm fw-bold">Partner Name</label>
                        <input type="text" name="partners[${pIdx}][partner_name]" class="form-control form-control-sm" placeholder="e.g. IIT Madras, Microsoft, Wharton">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label form-label-sm fw-bold">Partner Type</label>
                        <select name="partners[${pIdx}][partner_type]" class="form-select form-select-sm">
                            <option value="University">University</option>
                            <option value="Institute">Institute</option>
                            <option value="Corporate / Tech Giant">Corporate / Tech Giant</option>
                            <option value="Certification Body">Certification Body</option>
                            <option value="Government Body">Government Body</option>
                            <option value="Hiring Partner">Hiring Partner</option>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label form-label-sm fw-bold">Relationship Type</label>
                        <select name="partners[${pIdx}][relationship_type]" class="form-select form-select-sm">
                            <option value="Degree Partner">Degree Partner</option>
                            <option value="Certificate Partner">Certificate Partner</option>
                            <option value="Curriculum Partner">Curriculum Partner</option>
                            <option value="Hiring Partner">Hiring Partner</option>
                        </select>
                    </div>
                    <div class="col-md-2">
                        <label class="form-label form-label-sm fw-bold">Programs Count</label>
                        <input type="number" name="partners[${pIdx}][programs_count]" class="form-control form-control-sm" value="1">
                    </div>
                    <div class="col-md-5">
                        <label class="form-label form-label-sm fw-bold">Partner Website URL</label>
                        <input type="url" name="partners[${pIdx}][partner_website]" class="form-control form-control-sm" placeholder="https://...">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label form-label-sm fw-bold">Co-branded Credential?</label>
                        <div class="form-check form-switch mt-1">
                            <input class="form-check-input" type="checkbox" name="partners[${pIdx}][co_branded_credential]" value="1" checked>
                            <label class="form-check-label small">Issued with Partner Logo</label>
                        </div>
                    </div>
                    <div class="col-md-12">
                        <label class="form-label form-label-sm fw-bold">Brief Description / Partnership Scope</label>
                        <input type="text" name="partners[${pIdx}][description]" class="form-control form-control-sm" placeholder="Partnership details...">
                    </div>
                </div>
            `;
            partnersContainer.appendChild(div);
        });
    }

    document.addEventListener('click', function (e) {
        if (e.target.closest('.btn-remove-partner')) {
            e.target.closest('.partner-card').remove();
        }
    });

    // 3. Instructors Dynamic Rows
    const btnAddInstructor = document.getElementById('btn-add-instructor');
    const instructorsContainer = document.getElementById('instructors-container');
    if (btnAddInstructor && instructorsContainer) {
        btnAddInstructor.addEventListener('click', function () {
            const noMsg = document.getElementById('no-instructors-msg');
            if (noMsg) noMsg.remove();

            const insIdx = instructorsContainer.querySelectorAll('.instructor-card').length + Date.now();
            const div = document.createElement('div');
            div.className = 'border rounded p-3 mb-3 bg-white instructor-card position-relative';
            div.innerHTML = `
                <button type="button" class="btn btn-sm btn-danger position-absolute top-0 end-0 m-2 btn-remove-instructor"><i class="fas fa-times"></i></button>
                <div class="row g-2">
                    <div class="col-md-4">
                        <label class="form-label form-label-sm fw-bold">Full Name</label>
                        <input type="text" name="instructors[${insIdx}][name]" class="form-control form-control-sm" placeholder="e.g. Dr. Jane Doe">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label form-label-sm fw-bold">Designation / Role</label>
                        <input type="text" name="instructors[${insIdx}][designation]" class="form-control form-control-sm" placeholder="e.g. Principal AI Scientist">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label form-label-sm fw-bold">Company / Institution</label>
                        <input type="text" name="instructors[${insIdx}][current_company_or_institution]" class="form-control form-control-sm" placeholder="e.g. Google / Stanford">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label form-label-sm fw-bold">Experience (Years)</label>
                        <input type="number" name="instructors[${insIdx}][experience_years]" class="form-control form-control-sm" placeholder="10">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label form-label-sm fw-bold">Rating (0 - 5.0)</label>
                        <input type="number" step="0.1" min="0" max="5" name="instructors[${insIdx}][rating]" class="form-control form-control-sm" value="4.8">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label form-label-sm fw-bold">LinkedIn Profile URL</label>
                        <input type="url" name="instructors[${insIdx}][linkedin_url]" class="form-control form-control-sm" placeholder="https://linkedin.com/in/...">
                    </div>
                    <div class="col-md-12">
                        <label class="form-label form-label-sm fw-bold">Short Bio</label>
                        <input type="text" name="instructors[${insIdx}][bio]" class="form-control form-control-sm" placeholder="Short bio...">
                    </div>
                </div>
            `;
            instructorsContainer.appendChild(div);
        });
    }

    document.addEventListener('click', function (e) {
        if (e.target.closest('.btn-remove-instructor')) {
            e.target.closest('.instructor-card').remove();
        }
    });

    // 4. Leaders Dynamic Rows
    const btnAddLeader = document.getElementById('btn-add-leader');
    const leadersContainer = document.getElementById('leaders-container');
    if (btnAddLeader && leadersContainer) {
        btnAddLeader.addEventListener('click', function () {
            const noMsg = document.getElementById('no-leaders-msg');
            if (noMsg) noMsg.remove();

            const lIdx = leadersContainer.querySelectorAll('.leader-card').length + Date.now();
            const div = document.createElement('div');
            div.className = 'border rounded p-3 mb-3 bg-white leader-card position-relative';
            div.innerHTML = `
                <button type="button" class="btn btn-sm btn-danger position-absolute top-0 end-0 m-2 btn-remove-leader"><i class="fas fa-times"></i></button>
                <div class="row g-2">
                    <div class="col-md-4">
                        <label class="form-label form-label-sm fw-bold">Executive Name</label>
                        <input type="text" name="leaders[${lIdx}][name]" class="form-control form-control-sm" placeholder="e.g. Mayank Kumar">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label form-label-sm fw-bold">Designation</label>
                        <input type="text" name="leaders[${lIdx}][designation]" class="form-control form-control-sm" placeholder="e.g. Co-Founder & MD">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label form-label-sm fw-bold">Is Founder?</label>
                        <div class="form-check form-switch mt-1">
                            <input class="form-check-input" type="checkbox" name="leaders[${lIdx}][is_founder]" value="1" checked>
                            <label class="form-check-label small">Yes, Founder</label>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label form-label-sm fw-bold">LinkedIn Profile URL</label>
                        <input type="url" name="leaders[${lIdx}][linkedin_url]" class="form-control form-control-sm" placeholder="https://linkedin.com/in/...">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label form-label-sm fw-bold">Short Bio</label>
                        <input type="text" name="leaders[${lIdx}][bio]" class="form-control form-control-sm" placeholder="Short bio...">
                    </div>
                </div>
            `;
            leadersContainer.appendChild(div);
        });
    }

    document.addEventListener('click', function (e) {
        if (e.target.closest('.btn-remove-leader')) {
            e.target.closest('.leader-card').remove();
        }
    });
});
</script>
@endpush

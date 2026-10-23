@extends('admin.layouts.master')

@section('content')
<div class="container-fluid">
    {{-- Header --}}
    <div class="row mb-3">
        <div class="col-12 d-flex justify-content-between align-items-center flex-wrap gap-2">
            <div>
                <h4 class="mb-1 text-dark fw-bold">
                    <i class="fas fa-id-card text-primary me-2"></i>Faculty Profile Preview
                </h4>
                <p class="text-muted small mb-0">Detailed view reflecting the Enrollzy directory layout and academic profile.</p>
            </div>
            <div class="d-flex gap-2">
                <a href="{{ route('admin.faculties.edit', $faculty->id) }}" class="btn btn-primary btn-sm">
                    <i class="fas fa-edit me-1"></i> Edit Profile
                </a>
                <a href="{{ route('admin.faculties.index') }}" class="btn btn-outline-secondary btn-sm">
                    <i class="fas fa-arrow-left me-1"></i> Back to Faculty List
                </a>
            </div>
        </div>
    </div>

    {{-- Main Profile Card --}}
    <div class="card shadow-sm border-0 mb-4 overflow-hidden">
        {{-- Profile Hero / Banner --}}
        <div class="p-4 bg-primary text-white position-relative" style="background: linear-gradient(135deg, #0d6efd 0%, #0a58ca 100%);">
            <div class="row align-items-center">
                <div class="col-auto">
                    <img src="{{ $faculty->photo_url }}" alt="{{ $faculty->full_name }}" class="rounded-circle border border-4 border-white shadow" style="width: 110px; height: 110px; object-fit: cover;">
                </div>
                <div class="col">
                    <div class="d-flex align-items-center gap-2 flex-wrap mb-1">
                        <h2 class="mb-0 fw-bold">{{ $faculty->full_name }}</h2>
                        @if($faculty->verification_status === 'verified')
                            <span class="badge bg-success text-white px-2 py-1 fs-6">
                                <i class="fas fa-check-circle me-1"></i> Verified Faculty
                            </span>
                        @elseif($faculty->verification_status === 'pending')
                            <span class="badge bg-warning text-dark px-2 py-1 fs-6">
                                <i class="fas fa-clock me-1"></i> Pending Verification
                            </span>
                        @endif
                        <span class="badge bg-white text-primary px-2 py-1 font-monospace">
                            {{ $faculty->faculty_code }}
                        </span>
                    </div>

                    <div class="fs-5 text-white-50 mb-2">
                        {{ $faculty->effective_designation }}
                        @if($faculty->primaryDepartmentMapping && $faculty->primaryDepartmentMapping->department)
                            — {{ $faculty->primaryDepartmentMapping->department->department_name }}
                        @endif
                        @if($faculty->primaryDepartmentMapping && $faculty->primaryDepartmentMapping->organisation)
                            | <strong>{{ $faculty->primaryDepartmentMapping->organisation->name }}</strong>
                            @if($faculty->primaryDepartmentMapping->campus)
                                ({{ $faculty->primaryDepartmentMapping->campus->campus_name }})
                            @endif
                        @endif
                    </div>

                    <div class="d-flex align-items-center gap-3 text-white small flex-wrap">
                        @if($faculty->experience_years > 0)
                            <div><i class="fas fa-briefcase me-1"></i> <strong>{{ $faculty->experience_years }}+</strong> Years Experience</div>
                        @endif
                        @if($faculty->facultyType)
                            <div><i class="fas fa-user-tag me-1"></i> {{ $faculty->facultyType->name }}</div>
                        @endif
                        @if($faculty->date_of_joining)
                            <div><i class="fas fa-calendar-alt me-1"></i> Joined: {{ $faculty->date_of_joining->format('M Y') }}</div>
                        @endif
                    </div>
                </div>
            </div>
        </div>

        {{-- Quick Facts Bar --}}
        <div class="bg-light p-3 border-bottom">
            <div class="row g-2 text-center text-md-start">
                <div class="col-md-3 col-6 border-end">
                    <small class="text-muted d-block text-uppercase fw-bold" style="font-size: 11px;">Primary Department</small>
                    <span class="fw-bold text-dark">{{ $faculty->primaryDepartmentMapping->department->department_name ?? 'Interdisciplinary' }}</span>
                </div>
                <div class="col-md-3 col-6 border-end">
                    <small class="text-muted d-block text-uppercase fw-bold" style="font-size: 11px;">Highest Degree</small>
                    <span class="fw-bold text-dark">{{ $faculty->qualifications->first()->degree ?? 'N/A' }}</span>
                </div>
                <div class="col-md-3 col-6 border-end">
                    <small class="text-muted d-block text-uppercase fw-bold" style="font-size: 11px;">Publications / Citations</small>
                    <span class="fw-bold text-dark">{{ $faculty->total_publications }} Publications</span>
                </div>
                <div class="col-md-3 col-6">
                    <small class="text-muted d-block text-uppercase fw-bold" style="font-size: 11px;">Status</small>
                    <span class="badge {{ $faculty->status ? 'bg-success' : 'bg-secondary' }}">
                        {{ $faculty->status ? 'Active' : 'Inactive' }}
                    </span>
                </div>
            </div>
        </div>

        <div class="card-body p-4">
            <div class="row g-4">
                {{-- Left Column: Bio, Qualifications, Research, Experience --}}
                <div class="col-lg-8">
                    {{-- About & Bio --}}
                    <div class="mb-4">
                        <h5 class="fw-bold text-dark border-bottom pb-2 mb-2">About & Biography</h5>
                        <p class="lead fs-6 text-secondary mb-3">{{ $faculty->short_bio }}</p>
                        @if($faculty->detailed_biography)
                            <div class="text-dark" style="white-space: pre-line;">
                                {!! nl2br(e($faculty->detailed_biography)) !!}
                            </div>
                        @endif
                    </div>

                    {{-- Areas of Specialization & Tags --}}
                    <div class="mb-4">
                        <h5 class="fw-bold text-dark border-bottom pb-2 mb-3">Areas of Expertise & Specialization</h5>
                        @if(!empty($faculty->specializations))
                            <div class="mb-2">
                                <span class="small fw-bold text-muted d-block mb-1">Specializations:</span>
                                @foreach($faculty->specializations as $tag)
                                    <span class="badge bg-primary-subtle text-primary border border-primary px-2 py-1 me-1 mb-1">{{ $tag }}</span>
                                @endforeach
                            </div>
                        @endif

                        @if(!empty($faculty->research_areas_tags))
                            <div class="mb-2">
                                <span class="small fw-bold text-muted d-block mb-1">Research Areas:</span>
                                @foreach($faculty->research_areas_tags as $tag)
                                    <span class="badge bg-info-subtle text-dark border px-2 py-1 me-1 mb-1">{{ $tag }}</span>
                                @endforeach
                            </div>
                        @endif

                        @if(!empty($faculty->subjects_taught))
                            <div>
                                <span class="small fw-bold text-muted d-block mb-1">Subjects Taught:</span>
                                @foreach($faculty->subjects_taught as $tag)
                                    <span class="badge bg-light text-dark border px-2 py-1 me-1 mb-1">{{ $tag }}</span>
                                @endforeach
                            </div>
                        @endif
                    </div>

                    {{-- Associated Departments & Campuses --}}
                    <div class="mb-4">
                        <h5 class="fw-bold text-dark border-bottom pb-2 mb-3">Institutional Mappings</h5>
                        <div class="table-responsive">
                            <table class="table table-sm table-bordered">
                                <thead class="table-light small">
                                    <tr>
                                        <th>Organisation</th>
                                        <th>Campus</th>
                                        <th>Department</th>
                                        <th>Designation</th>
                                        <th>Primary?</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($faculty->departmentMappings as $map)
                                    <tr>
                                        <td><strong>{{ $map->organisation->name ?? 'N/A' }}</strong></td>
                                        <td>{{ $map->campus->campus_name ?? 'Main Campus' }}</td>
                                        <td>{{ $map->department->department_name ?? 'N/A' }}</td>
                                        <td>{{ $map->designation->name ?? $faculty->effective_designation }}</td>
                                        <td>
                                            @if($map->is_primary_department)
                                                <span class="badge bg-primary">Yes</span>
                                            @else
                                                <span class="text-muted">No</span>
                                            @endif
                                        </td>
                                    </tr>
                                    @empty
                                    <tr><td colspan="5" class="text-muted text-center">No institutional mappings added.</td></tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>

                    {{-- Courses Taught --}}
                    @if($faculty->courseMappings->count() > 0)
                    <div class="mb-4">
                        <h5 class="fw-bold text-dark border-bottom pb-2 mb-3">Courses & Subjects Taught</h5>
                        <div class="row g-2">
                            @foreach($faculty->courseMappings as $cMap)
                            <div class="col-md-6">
                                <div class="border rounded p-2 bg-light">
                                    <div class="fw-bold text-dark">{{ $cMap->course->name ?? $cMap->subject_name }}</div>
                                    @if($cMap->subject_name)
                                        <div class="small text-muted">Subject: <strong>{{ $cMap->subject_name }}</strong></div>
                                    @endif
                                    <div class="small text-muted">
                                        {{ $cMap->academic_year ?? '' }} {{ $cMap->semester ? '• ' . $cMap->semester : '' }}
                                        <span class="badge bg-secondary-subtle text-secondary ms-1">{{ $cMap->role ?? 'Instructor' }}</span>
                                    </div>
                                </div>
                            </div>
                            @endforeach
                        </div>
                    </div>
                    @endif

                    {{-- Academic Qualifications --}}
                    <div class="mb-4">
                        <h5 class="fw-bold text-dark border-bottom pb-2 mb-3">Academic Qualifications</h5>
                        @forelse($faculty->qualifications as $qual)
                        <div class="d-flex align-items-start gap-3 mb-3 pb-2 border-bottom">
                            <div class="avatar bg-primary text-white rounded p-2 text-center" style="width: 44px; height: 44px;">
                                <i class="fas fa-graduation-cap fa-lg"></i>
                            </div>
                            <div class="flex-grow-1">
                                <div class="fw-bold text-dark">{{ $qual->degree }} {{ $qual->specialization ? 'in ' . $qual->specialization : '' }}</div>
                                <div class="text-secondary small">{{ $qual->institution ?? $qual->university }}</div>
                                <div class="text-muted small">
                                    Year: {{ $qual->year ?? 'N/A' }} 
                                    @if($qual->grade_percentage)
                                        | Grade: <strong>{{ $qual->grade_percentage }}</strong>
                                    @endif
                                </div>
                            </div>
                            @if($qual->certificate_document)
                            <div>
                                <a href="{{ asset($qual->certificate_document) }}" target="_blank" class="btn btn-outline-secondary btn-sm">
                                    <i class="fas fa-file-pdf me-1"></i> Doc
                                </a>
                            </div>
                            @endif
                        </div>
                        @empty
                        <p class="text-muted small">No qualifications added.</p>
                        @endforelse
                    </div>

                    {{-- Professional Experience --}}
                    @if($faculty->experiences->count() > 0)
                    <div class="mb-4">
                        <h5 class="fw-bold text-dark border-bottom pb-2 mb-3">Professional Experience Timeline</h5>
                        <ul class="list-unstyled position-relative ps-3 border-start border-2 border-primary">
                            @foreach($faculty->experiences as $exp)
                            <li class="mb-3 position-relative">
                                <div class="fw-bold text-dark">{{ $exp->designation }}</div>
                                <div class="text-primary small fw-semibold">{{ $exp->organization }}</div>
                                <div class="text-muted small">
                                    {{ $exp->start_date ? $exp->start_date->format('M Y') : 'N/A' }} — 
                                    {{ $exp->currently_working ? 'Present' : ($exp->end_date ? $exp->end_date->format('M Y') : 'N/A') }}
                                </div>
                                @if($exp->description)
                                    <div class="small text-secondary mt-1">{{ $exp->description }}</div>
                                @endif
                            </li>
                            @endforeach
                        </ul>
                    </div>
                    @endif

                    {{-- Research & Publications --}}
                    @if($faculty->publications->count() > 0)
                    <div class="mb-4">
                        <h5 class="fw-bold text-dark border-bottom pb-2 mb-3">Research Papers & Publications</h5>
                        <div class="list-group list-group-flush">
                            @foreach($faculty->publications as $pub)
                            <div class="list-group-item px-0 py-2">
                                <div class="d-flex justify-content-between align-items-start">
                                    <div>
                                        <div class="fw-bold text-dark mb-1">{{ $pub->title }}</div>
                                        <div class="text-muted small">
                                            <span class="badge bg-light text-dark border me-1">{{ $pub->publication_type }}</span>
                                            {{ $pub->journal_conference ?? $pub->publisher }} 
                                            @if($pub->publication_date)
                                                ({{ $pub->publication_date->format('Y') }})
                                            @endif
                                            @if($pub->doi)
                                                | DOI: <a href="https://doi.org/{{ $pub->doi }}" target="_blank">{{ $pub->doi }}</a>
                                            @endif
                                        </div>
                                    </div>
                                    @if($pub->citation_count > 0)
                                        <span class="badge bg-secondary-subtle text-secondary">{{ $pub->citation_count }} citations</span>
                                    @endif
                                </div>
                            </div>
                            @endforeach
                        </div>
                    </div>
                    @endif

                    {{-- Achievements & Awards --}}
                    @if($faculty->achievements->count() > 0)
                    <div class="mb-4">
                        <h5 class="fw-bold text-dark border-bottom pb-2 mb-3">Achievements & Awards</h5>
                        <div class="row g-2">
                            @foreach($faculty->achievements as $ach)
                            <div class="col-md-6">
                                <div class="border rounded p-2 bg-light d-flex align-items-center gap-2">
                                    <i class="fas fa-trophy text-warning fa-2x"></i>
                                    <div>
                                        <div class="fw-bold text-dark">{{ $ach->title }}</div>
                                        <small class="text-muted">{{ $ach->organization }} ({{ $ach->year }})</small>
                                    </div>
                                </div>
                            </div>
                            @endforeach
                        </div>
                    </div>
                    @endif

                    {{-- Certifications --}}
                    @if($faculty->certifications->count() > 0)
                    <div class="mb-4">
                        <h5 class="fw-bold text-dark border-bottom pb-2 mb-3">Certifications</h5>
                        <div class="row g-2">
                            @foreach($faculty->certifications as $cert)
                            <div class="col-md-6">
                                <div class="border rounded p-2 bg-light">
                                    <div class="fw-bold text-dark">{{ $cert->certification_name }}</div>
                                    <small class="text-muted">{{ $cert->issuing_organization }}</small>
                                    @if($cert->credential_id)
                                        <div class="small font-monospace text-secondary">ID: {{ $cert->credential_id }}</div>
                                    @endif
                                </div>
                            </div>
                            @endforeach
                        </div>
                    </div>
                    @endif
                </div>

                {{-- Right Column: Sidebars, Internal Contacts, Profiles, Dynamic Fields, CTA --}}
                <div class="col-lg-4">
                    {{-- Student Discovery Lead CTA (Enrollzy Requirement) --}}
                    <div class="card border-primary border-2 bg-primary-subtle shadow-sm mb-4">
                        <div class="card-body text-center p-3">
                            <i class="fas fa-comments text-primary fa-3x mb-2"></i>
                            <h5 class="fw-bold text-dark mb-1">Interested in this Faculty's Programs?</h5>
                            <p class="small text-muted mb-3">Discover admissions, course curriculum, and mentorship opportunities.</p>
                            <button type="button" class="btn btn-primary btn-sm w-100 fw-bold">
                                <i class="fas fa-phone-alt me-1"></i> Talk to a Counselor
                            </button>
                        </div>
                    </div>

                    {{-- Professional & Academic Profiles --}}
                    <div class="card shadow-sm border-0 mb-4">
                        <div class="card-header bg-white py-2 fw-bold text-dark">
                            <i class="fas fa-globe text-primary me-1"></i> Academic Profiles
                        </div>
                        <ul class="list-group list-group-flush small">
                            @if($faculty->linkedin_url)
                                <li class="list-group-item d-flex justify-content-between align-items-center">
                                    <span><i class="fab fa-linkedin text-primary me-2"></i>LinkedIn</span>
                                    <a href="{{ $faculty->linkedin_url }}" target="_blank" class="text-truncate" style="max-width: 150px;">Profile</a>
                                </li>
                            @endif
                            @if($faculty->google_scholar_url)
                                <li class="list-group-item d-flex justify-content-between align-items-center">
                                    <span><i class="fas fa-graduation-cap text-success me-2"></i>Google Scholar</span>
                                    <a href="{{ $faculty->google_scholar_url }}" target="_blank">Scholar</a>
                                </li>
                            @endif
                            @if($faculty->orcid_id)
                                <li class="list-group-item d-flex justify-content-between align-items-center">
                                    <span><i class="fas fa-id-badge text-warning me-2"></i>ORCID</span>
                                    <span class="font-monospace">{{ $faculty->orcid_id }}</span>
                                </li>
                            @endif
                            @if($faculty->researchgate_url)
                                <li class="list-group-item d-flex justify-content-between align-items-center">
                                    <span><i class="fas fa-flask text-teal me-2"></i>ResearchGate</span>
                                    <a href="{{ $faculty->researchgate_url }}" target="_blank">ResearchGate</a>
                                </li>
                            @endif
                            @if($faculty->personal_website_url)
                                <li class="list-group-item d-flex justify-content-between align-items-center">
                                    <span><i class="fas fa-link text-secondary me-2"></i>Website</span>
                                    <a href="{{ $faculty->personal_website_url }}" target="_blank">Portfolio</a>
                                </li>
                            @endif
                            @if($faculty->scopus_id)
                                <li class="list-group-item d-flex justify-content-between align-items-center">
                                    <span>Scopus ID</span>
                                    <span class="font-monospace">{{ $faculty->scopus_id }}</span>
                                </li>
                            @endif
                            @if($faculty->vidwan_id)
                                <li class="list-group-item d-flex justify-content-between align-items-center">
                                    <span>Vidwan ID</span>
                                    <span class="font-monospace">{{ $faculty->vidwan_id }}</span>
                                </li>
                            @endif
                        </ul>
                    </div>

                    {{-- Internal Contact & Privacy Card (Admin Only) --}}
                    <div class="card shadow-sm border-0 border-start border-warning border-4 mb-4">
                        <div class="card-header bg-white py-2 fw-bold text-dark d-flex justify-content-between align-items-center">
                            <span><i class="fas fa-lock text-warning me-1"></i> Internal Contact</span>
                            <span class="badge bg-warning-subtle text-dark border">Private</span>
                        </div>
                        <div class="card-body p-3 small">
                            <div class="mb-2">
                                <span class="text-muted d-block">Official Email:</span>
                                <strong class="text-dark">{{ $faculty->official_email ?? 'Not provided' }}</strong>
                            </div>
                            <div class="mb-2">
                                <span class="text-muted d-block">Official Phone:</span>
                                <strong class="text-dark">{{ $faculty->official_phone ?? 'Not provided' }}</strong>
                                @if($faculty->extension)
                                    <span class="text-muted">({{ $faculty->extension }})</span>
                                @endif
                            </div>
                            <div class="mb-2">
                                <span class="text-muted d-block">Office Location:</span>
                                <span class="text-dark">{{ $faculty->office_location ?? 'Main Campus Block' }}</span>
                            </div>
                            <div>
                                <span class="text-muted d-block">Office Room:</span>
                                <span class="text-dark">{{ $faculty->office_room_number ?? 'N/A' }}</span>
                            </div>
                        </div>
                    </div>

                    {{-- Institution Custom Fields --}}
                    @if($faculty->customFields->count() > 0)
                    <div class="card shadow-sm border-0 mb-4">
                        <div class="card-header bg-white py-2 fw-bold text-dark">
                            <i class="fas fa-sliders-h text-primary me-1"></i> Custom Attributes
                        </div>
                        <ul class="list-group list-group-flush small">
                            @foreach($faculty->customFields as $cf)
                            <li class="list-group-item d-flex justify-content-between align-items-center">
                                <div>
                                    <span class="fw-semibold text-dark">{{ $cf->field_label }}</span>
                                    <span class="badge bg-light text-muted border ms-1" style="font-size: 10px;">{{ ucfirst($cf->institution_type) }}</span>
                                </div>
                                <span class="text-primary fw-bold">{{ $cf->field_value }}</span>
                            </li>
                            @endforeach
                        </ul>
                    </div>
                    @endif

                    {{-- Verification & SEO Metadata --}}
                    <div class="card shadow-sm border-0 mb-4">
                        <div class="card-header bg-white py-2 fw-bold text-dark">
                            <i class="fas fa-shield-alt text-success me-1"></i> Verification & SEO
                        </div>
                        <div class="card-body p-3 small">
                            <div class="mb-2">
                                <span class="text-muted d-block">Verification Status:</span>
                                <span class="badge {{ $faculty->verification_status === 'verified' ? 'bg-success' : 'bg-warning text-dark' }}">
                                    {{ ucfirst($faculty->verification_status) }}
                                </span>
                            </div>
                            @if($faculty->verification_source)
                            <div class="mb-2">
                                <span class="text-muted d-block">Verification Source:</span>
                                <span>{{ $faculty->verification_source }}</span>
                            </div>
                            @endif
                            <div class="mb-2">
                                <span class="text-muted d-block">Slug:</span>
                                <span class="font-monospace text-primary">/faculty/{{ $faculty->slug }}</span>
                            </div>
                            <div>
                                <span class="text-muted d-block">Search Indexing:</span>
                                <span>{{ $faculty->is_indexed ? 'Allowed (index, follow)' : 'No-index' }}</span>
                            </div>
                        </div>
                    </div>

                </div>
            </div>
        </div>
    </div>
</div>
@endsection

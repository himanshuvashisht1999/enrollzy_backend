@extends('admin.layouts.master')

@section('title', 'Edit Alumni')

@section('content')
<div class="row justify-content-center">
    <div class="col-md-11">
        <form action="{{ route('admin.alumni.update', $alumnus->id) }}" method="POST" enctype="multipart/form-data">
            @csrf
            @method('PUT')
            
            <div class="card">
                <div class="card-header bg-white border-bottom-0">
                    <ul class="nav nav-tabs card-header-tabs" id="alumniTabs" role="tablist">
                        <li class="nav-item">
                            <button class="nav-link active" id="basic-tab" data-bs-toggle="tab" data-bs-target="#basic" type="button" role="tab">Basic & Auth</button>
                        </li>
                        <li class="nav-item">
                            <button class="nav-link" id="network-tab" data-bs-toggle="tab" data-bs-target="#network" type="button" role="tab">Network & Global</button>
                        </li>
                        <li class="nav-item">
                            <button class="nav-link" id="outcomes-tab" data-bs-toggle="tab" data-bs-target="#outcomes" type="button" role="tab">Career & Industry</button>
                        </li>
                        <li class="nav-item">
                            <button class="nav-link" id="mentorship-tab" data-bs-toggle="tab" data-bs-target="#mentorship" type="button" role="tab">Mentorship & Referral</button>
                        </li>
                        <li class="nav-item">
                            <button class="nav-link" id="contribution-tab" data-bs-toggle="tab" data-bs-target="#contribution" type="button" role="tab">Giving Back & Accessibility</button>
                        </li>
                    </ul>
                </div>
                
                <div class="card-body">
                    <div class="tab-content mt-3" id="alumniTabsContent">
                        
                        <!-- TAB 1: BASIC & AUTH -->
                        <div class="tab-pane fade show active" id="basic" role="tabpanel">
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Full Name <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control @error('name') is-invalid @enderror" name="name" value="{{ old('name', $alumnus->name) }}" required>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Email <span class="text-danger">*</span></label>
                                    <input type="email" class="form-control @error('email') is-invalid @enderror" name="email" value="{{ old('email', $alumnus->email) }}" required>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Password (leave blank to keep current)</label>
                                    <input type="password" class="form-control @error('password') is-invalid @enderror" name="password">
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Designation</label>
                                    <input type="text" class="form-control" name="designation" value="{{ old('designation', $alumnus->designation) }}">
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Current Company</label>
                                    <input type="text" class="form-control" name="company" value="{{ old('company', $alumnus->company) }}">
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Experience (Years)</label>
                                    <input type="text" class="form-control" name="experience_years" value="{{ old('experience_years', $alumnus->experience_years) }}">
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">LinkedIn URL</label>
                                    <input type="url" class="form-control" name="linkedin_url" value="{{ old('linkedin_url', $alumnus->linkedin_url) }}">
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Image</label>
                                    @if($alumnus->image)
                                        <div class="mb-2">
                                            <img src="{{ asset($alumnus->image) }}" style="height: 50px; border-radius: 5px;">
                                        </div>
                                    @endif
                                    <input type="file" class="form-control" name="image">
                                </div>
                                <div class="col-md-3 mb-3">
                                    <label class="form-label">Sort Order</label>
                                    <input type="number" class="form-control" name="sort_order" value="{{ old('sort_order', $alumnus->sort_order) }}">
                                </div>
                                <div class="col-md-3 mb-3">
                                    <div class="form-check mt-4">
                                        <input class="form-check-input" type="checkbox" name="status" id="status" {{ $alumnus->status ? 'checked' : '' }}>
                                        <label class="form-check-label" for="status">Active</label>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- TAB 2: NETWORK & GLOBAL -->
                        <div class="tab-pane fade" id="network" role="tabpanel">
                            <div class="row">
                                <div class="col-md-4 mb-3">
                                    <label class="form-label">Total Alumni Count</label>
                                    <input type="text" class="form-control" name="total_alumni_count" value="{{ old('total_alumni_count', $alumnus->total_alumni_count) }}">
                                </div>
                                <div class="col-md-4 mb-3">
                                    <label class="form-label">Alumni per Graduation Batch</label>
                                    <input type="text" class="form-control" name="alumni_per_graduation_batch" value="{{ old('alumni_per_graduation_batch', $alumnus->alumni_per_graduation_batch) }}">
                                </div>
                                <div class="col-md-4 mb-3">
                                    <label class="form-label">Alumni Growth Rate</label>
                                    <input type="text" class="form-control" name="alumni_growth_rate" value="{{ old('alumni_growth_rate', $alumnus->alumni_growth_rate) }}">
                                </div>
                                <hr>
                                <div class="col-md-4 mb-3">
                                    <label class="form-label">Active Alumni Countries</label>
                                    <input type="text" class="form-control" name="active_alumni_countries_count" value="{{ old('active_alumni_countries_count', $alumnus->active_alumni_countries_count) }}">
                                </div>
                                <div class="col-md-4 mb-3">
                                    <label class="form-label">% Alumni Working Abroad</label>
                                    <input type="text" class="form-control" name="percent_alumni_working_abroad" value="{{ old('percent_alumni_working_abroad', $alumnus->percent_alumni_working_abroad) }}">
                                </div>
                            </div>
                        </div>

                        <!-- TAB 3: CAREER & INDUSTRY -->
                        <div class="tab-pane fade" id="outcomes" role="tabpanel">
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <div class="form-check mb-2">
                                        <input class="form-check-input" type="checkbox" name="placed_in_top_companies" id="top_companies" {{ $alumnus->placed_in_top_companies ? 'checked' : '' }}>
                                        <label class="form-check-label" for="top_companies">Placed in Top Companies</label>
                                    </div>
                                    <div class="form-check mb-3">
                                        <input class="form-check-input" type="checkbox" name="alumni_in_govt_civil_services" id="govt_services" {{ $alumnus->alumni_in_govt_civil_services ? 'checked' : '' }}>
                                        <label class="form-check-label" for="govt_services">In Govt/Civil Services/Academia</label>
                                    </div>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">In Leadership Roles</label>
                                    <input type="text" class="form-control" name="leadership_roles_count" value="{{ old('leadership_roles_count', $alumnus->leadership_roles_count) }}">
                                </div>
                                <div class="col-md-12 mb-3">
                                    <label class="form-label">Average Salary Bands</label>
                                    <input type="text" class="form-control" name="average_salary_bands" value="{{ old('average_salary_bands', $alumnus->average_salary_bands) }}">
                                </div>
                                <hr>
                                <label class="form-label fw-bold">Industry Distribution (%)</label>
                                <div class="col-md-3 mb-3">
                                    <label class="form-label">Tech</label>
                                    <input type="text" class="form-control" name="tech_industry_percent" value="{{ old('tech_industry_percent', $alumnus->tech_industry_percent) }}">
                                </div>
                                <div class="col-md-3 mb-3">
                                    <label class="form-label">Finance</label>
                                    <input type="text" class="form-control" name="finance_industry_percent" value="{{ old('finance_industry_percent', $alumnus->finance_industry_percent) }}">
                                </div>
                                <div class="col-md-3 mb-3">
                                    <label class="form-label">Healthcare</label>
                                    <input type="text" class="form-control" name="healthcare_industry_percent" value="{{ old('healthcare_industry_percent', $alumnus->healthcare_industry_percent) }}">
                                </div>
                                <div class="col-md-3 mb-3">
                                    <label class="form-label">Law</label>
                                    <input type="text" class="form-control" name="law_industry_percent" value="{{ old('law_industry_percent', $alumnus->law_industry_percent) }}">
                                </div>
                                <div class="col-md-3 mb-3">
                                    <label class="form-label">Consulting</label>
                                    <input type="text" class="form-control" name="consulting_industry_percent" value="{{ old('consulting_industry_percent', $alumnus->consulting_industry_percent) }}">
                                </div>
                                <div class="col-md-3 mb-3">
                                    <label class="form-label">Entrepreneurship</label>
                                    <input type="text" class="form-control" name="entrepreneurship_industry_percent" value="{{ old('entrepreneurship_industry_percent', $alumnus->entrepreneurship_industry_percent) }}">
                                </div>
                                <div class="col-md-3 mb-3">
                                    <label class="form-label">Sports/Arts</label>
                                    <input type="text" class="form-control" name="sports_arts_industry_percent" value="{{ old('sports_arts_industry_percent', $alumnus->sports_arts_industry_percent) }}">
                                </div>
                            </div>
                        </div>

                        <!-- TAB 4: MENTORSHIP & REFERRAL -->
                        <div class="tab-pane fade" id="mentorship" role="tabpanel">
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <div class="form-check mb-2">
                                        <input class="form-check-input" type="checkbox" name="is_mentor" id="is_mentor" {{ $alumnus->is_mentor ? 'checked' : '' }}>
                                        <label class="form-check-label" for="is_mentor">Alumni Mentorship (Active)</label>
                                    </div>
                                    <label class="form-label">Frequency of Interaction</label>
                                    <input type="text" class="form-control" name="alumni_interaction_frequency" value="{{ old('alumni_interaction_frequency', $alumnus->alumni_interaction_frequency) }}">
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Alumni Participation Rate</label>
                                    <input type="text" class="form-control" name="participation_rate" value="{{ old('participation_rate', $alumnus->participation_rate) }}">
                                    <label class="form-label mt-2">Student Mentorship Ratio</label>
                                    <input type="text" class="form-control" name="student_mentorship_ratio" value="{{ old('student_mentorship_ratio', $alumnus->student_mentorship_ratio) }}">
                                </div>
                                <hr>
                                <div class="col-md-12 mb-3">
                                    <label class="form-label d-block">Mentorship & Guidance Availability</label>
                                    <div class="form-check form-check-inline">
                                        <input class="form-check-input" type="checkbox" name="formal_mentorship_available" id="formal_m" {{ $alumnus->formal_mentorship_available ? 'checked' : '' }}>
                                        <label class="form-check-label" for="formal_m">Formal Mentorship</label>
                                    </div>
                                    <div class="form-check form-check-inline">
                                        <input class="form-check-input" type="checkbox" name="career_guidance_sessions" id="career_g" {{ $alumnus->career_guidance_sessions ? 'checked' : '' }}>
                                        <label class="form-check-label" for="career_g">Career Guidance</label>
                                    </div>
                                    <div class="form-check form-check-inline">
                                        <input class="form-check-input" type="checkbox" name="academic_mentoring" id="academic_m" {{ $alumnus->academic_mentoring ? 'checked' : '' }}>
                                        <label class="form-check-label" for="academic_m">Academic Mentoring</label>
                                    </div>
                                    <div class="form-check form-check-inline">
                                        <input class="form-check-input" type="checkbox" name="startup_mentoring" id="startup_m" {{ $alumnus->startup_mentoring ? 'checked' : '' }}>
                                        <label class="form-check-label" for="startup_m">Startup Mentoring</label>
                                    </div>
                                </div>
                                <hr>
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Alumni Driven Placements</label>
                                    <input type="text" class="form-control" name="alumni_driven_placements_count" value="{{ old('alumni_driven_placements_count', $alumnus->alumni_driven_placements_count) }}">
                                    <div class="form-check mt-3 mb-2">
                                        <input class="form-check-input" type="checkbox" name="referral_programs_active" id="referral_p" {{ $alumnus->referral_programs_active ? 'checked' : '' }}>
                                        <label class="form-check-label" for="referral_p">Alumni Referral Programs</label>
                                    </div>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <div class="form-check mt-2 mb-2">
                                        <input class="form-check-input" type="checkbox" name="internship_support_via_alumni" id="internship_s" {{ $alumnus->internship_support_via_alumni ? 'checked' : '' }}>
                                        <label class="form-check-label" for="internship_s">Internship Support</label>
                                    </div>
                                    <div class="form-check mb-2">
                                        <input class="form-check-input" type="checkbox" name="campus_hiring_initiated_by_alumni" id="campus_h" {{ $alumnus->campus_hiring_initiated_by_alumni ? 'checked' : '' }}>
                                        <label class="form-check-label" for="campus_h">Campus Hiring Initiated by Alumni</label>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- TAB 5: GIVING BACK & ACCESSIBILITY -->
                        <div class="tab-pane fade" id="contribution" role="tabpanel">
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Total Alumni Donations</label>
                                    <input type="text" class="form-control" name="total_alumni_donations" value="{{ old('total_alumni_donations', $alumnus->total_alumni_donations) }}">
                                    <label class="form-label mt-2">Scholarships Funded</label>
                                    <input type="text" class="form-control" name="scholarships_funded_by_alumni" value="{{ old('scholarships_funded_by_alumni', $alumnus->scholarships_funded_by_alumni) }}">
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Infrastructure Funded</label>
                                    <input type="text" class="form-control" name="infrastructure_funded_by_alumni" value="{{ old('infrastructure_funded_by_alumni', $alumnus->infrastructure_funded_by_alumni) }}">
                                    <label class="form-label mt-2">Endowment Contributions</label>
                                    <input type="text" class="form-control" name="endowment_contributions" value="{{ old('endowment_contributions', $alumnus->endowment_contributions) }}">
                                </div>
                                <hr>
                                <div class="col-md-12 mb-3">
                                    <label class="form-label d-block">Accessibility</label>
                                    <div class="form-check form-check-inline">
                                        <input class="form-check-input" type="checkbox" name="directory_access" id="dir_acc" {{ $alumnus->directory_access ? 'checked' : '' }}>
                                        <label class="form-check-label" for="dir_acc">Directory Access</label>
                                    </div>
                                    <div class="form-check form-check-inline">
                                        <input class="form-check-input" type="checkbox" name="network_platform_available" id="net_plat" {{ $alumnus->network_platform_available ? 'checked' : '' }}>
                                        <label class="form-check-label" for="net_plat">Network Platform</label>
                                    </div>
                                    <div class="form-check form-check-inline">
                                        <input class="form-check-input" type="checkbox" name="linkedin_integration_active" id="link_int" {{ $alumnus->linkedin_integration_active ? 'checked' : '' }}>
                                        <label class="form-check-label" for="link_int">LinkedIn Integration</label>
                                    </div>
                                    <div class="form-check form-check-inline">
                                        <input class="form-check-input" type="checkbox" name="contact_via_portal_active" id="cont_por" {{ $alumnus->contact_via_portal_active ? 'checked' : '' }}>
                                        <label class="form-check-label" for="cont_por">Contact via Portal</label>
                                    </div>
                                </div>
                                <hr>
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Network Strength Score</label>
                                    <select class="form-select" name="network_strength_score">
                                        <option value="">Select Score</option>
                                        <option value="Weak" {{ $alumnus->network_strength_score == 'Weak' ? 'selected' : '' }}>Weak</option>
                                        <option value="Moderate" {{ $alumnus->network_strength_score == 'Moderate' ? 'selected' : '' }}>Moderate</option>
                                        <option value="Strong" {{ $alumnus->network_strength_score == 'Strong' ? 'selected' : '' }}>Strong</option>
                                        <option value="Elite" {{ $alumnus->network_strength_score == 'Elite' ? 'selected' : '' }}>Elite</option>
                                    </select>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Mentorship Effectiveness Score</label>
                                    <input type="text" class="form-control" name="mentorship_effectiveness_score" value="{{ old('mentorship_effectiveness_score', $alumnus->mentorship_effectiveness_score) }}">
                                </div>
                            </div>
                        </div>

                    </div>
                </div>

                <div class="card-footer bg-white text-end py-3">
                    <a href="{{ route('admin.alumni.index') }}" class="btn btn-light me-2">Cancel</a>
                    <button type="submit" class="btn btn-primary px-4">Update Alumni Profile</button>
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

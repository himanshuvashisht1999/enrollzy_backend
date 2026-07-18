@extends('admin.layouts.master')

@section('title', 'Mentor Details')

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h4 class="mb-0">
            <a href="{{ route('admin.mentor.profiles.index') }}" class="text-decoration-none text-muted me-2"><i class="bi bi-arrow-left"></i></a> 
            Mentor Profile: {{ $profile->user->name ?? 'Unknown' }}
        </h4>
    </div>



    <div class="row">
        <!-- Main Details -->
        <div class="col-md-8">
            <!-- Basic Profile -->
            <div class="card shadow-sm border-0 mb-4 rounded-4">
                <div class="card-body p-4">
                    <h5 class="fw-bold mb-3 border-bottom pb-2">Basic Info</h5>
                    <div class="d-flex mb-3">
                        @if($profile->profile_photo)
                            <img src="http://127.0.0.1:8000/{{ $profile->profile_photo }}" class="rounded-circle me-4" style="width: 80px; height: 80px; object-fit: cover;">
                        @else
                            <div class="rounded-circle d-flex align-items-center justify-content-center bg-primary text-white fw-bold me-4" style="width: 80px; height: 80px; font-size: 2rem;">
                                {{ substr($profile->user->name ?? 'M', 0, 1) }}
                            </div>
                        @endif
                        <div>
                            <h5 class="fw-bold mb-1">{{ $profile->first_name }} {{ $profile->last_name }}</h5>
                            <p class="text-muted mb-1">{{ $profile->professional_headline }}</p>
                            <p class="text-muted small"><i class="bi bi-geo-alt"></i> {{ $profile->city }}, {{ $profile->state_country }}</p>
                        </div>
                    </div>
                    <p class="text-dark">{{ $profile->short_bio }}</p>
                </div>
            </div>

            <!-- Education & Professional -->
            <div class="card shadow-sm border-0 mb-4 rounded-4">
                <div class="card-body p-4">
                    <h5 class="fw-bold mb-3 border-bottom pb-2">Education & Experience</h5>
                    
                    <h6 class="fw-bold text-primary mb-2 mt-3">Education</h6>
                    @if($educations->count() > 0)
                        <ul class="list-group list-group-flush mb-4">
                            @foreach($educations as $edu)
                            <li class="list-group-item px-0">
                                <div class="fw-bold">{{ $edu->degree_type }} - {{ $edu->specialisation }}</div>
                                <div class="text-muted">{{ $edu->institution }} (Class of {{ $edu->year_of_graduation }})</div>
                                @if($edu->degree_certificate)
                                    <a href="http://127.0.0.1:8000/{{ $edu->degree_certificate }}" target="_blank" class="badge bg-light text-dark border mt-1"><i class="bi bi-file-earmark"></i> View Certificate</a>
                                @endif
                            </li>
                            @endforeach
                        </ul>
                    @else
                        <p class="text-muted">No education added.</p>
                    @endif

                    <h6 class="fw-bold text-primary mb-2">Experience</h6>
                    @if($experiences->count() > 0)
                        <ul class="list-group list-group-flush">
                            @foreach($experiences as $exp)
                            <li class="list-group-item px-0">
                                <div class="fw-bold">{{ $exp->job_title }}</div>
                                <div class="text-muted">{{ $exp->company }} ({{ $exp->start_year }} - {{ $exp->is_current ? 'Present' : $exp->end_year }})</div>
                                <p class="small text-muted mt-1 mb-0">{{ $exp->achievements }}</p>
                            </li>
                            @endforeach
                        </ul>
                    @else
                        <p class="text-muted">No experience added.</p>
                    @endif
                </div>
            </div>
            
            <!-- Mentorship & Pricing -->
            <div class="card shadow-sm border-0 mb-4 rounded-4">
                <div class="card-body p-4">
                    <h5 class="fw-bold mb-3 border-bottom pb-2">Mentorship & Pricing</h5>
                    <div class="row">
                        <div class="col-md-6 border-end">
                            <h6 class="fw-bold mb-2">Mentorship Details</h6>
                            @if($mentorship)
                                <p class="text-muted mb-1 small"><strong>Style:</strong> {{ $mentorship->mentoring_style ?? '-' }}</p>
                                <p class="text-muted mb-2 small"><strong>Platform:</strong> {{ $mentorship->preferred_platform ?? '-' }}</p>
                                
                                <strong>Areas:</strong><br>
                                @php $areas = json_decode($mentorship->areas_of_mentorship, true) ?? []; @endphp
                                @foreach($areas as $area)
                                    <span class="badge bg-light text-dark border">{{ $area }}</span>
                                @endforeach
                            @else
                                <p class="text-muted small">Not set up.</p>
                            @endif
                        </div>
                        <div class="col-md-6 ps-4">
                            <h6 class="fw-bold mb-2">Pricing</h6>
                            @if($pricing)
                                <h3 class="fw-bold text-success mb-1">₹{{ $pricing->fee_30_min ?? 0 }} <span class="fs-6 text-muted fw-normal">/ 30 min</span></h3>
                                <p class="mb-1 small text-muted">Pro-bono matching: <strong>{{ $pricing->pro_bono_sessions ? 'Enabled' : 'Disabled' }}</strong></p>
                                <p class="small text-muted mb-0">Free introduction: <strong>{{ $pricing->offer_free_first_session ? 'Enabled' : 'Disabled' }}</strong></p>
                            @else
                                <p class="text-muted small">Not set up.</p>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Verification Sidebar -->
        <div class="col-md-4">
            <div class="card shadow-sm border-0 rounded-4 sticky-top" style="top: 20px;">
                <div class="card-body p-4">
                    <h5 class="fw-bold mb-3 border-bottom pb-2">Verification Actions</h5>
                    
                    @php $v = $profile->verification; @endphp
                    
                    @if(!$v)
                        <p class="text-muted">No verification data submitted yet.</p>
                    @else
                        <!-- Gov ID -->
                        <div class="mb-4">
                            <div class="d-flex justify-content-between align-items-center mb-1">
                                <span class="fw-bold">Government ID</span>
                                <span class="badge bg-{{ $v->gov_id_status == 'verified' ? 'success' : ($v->gov_id_status == 'pending' ? 'warning' : 'secondary') }}">{{ ucfirst($v->gov_id_status) }}</span>
                            </div>
                            @if($v->gov_id_path)
                                <a href="http://127.0.0.1:8000/{{ $v->gov_id_path }}" target="_blank" class="small d-block mb-2 text-decoration-none"><i class="bi bi-file-earmark"></i> View Uploaded ID</a>
                            @endif
                            <div class="d-flex gap-2">
                                <form action="{{ route('admin.mentor.verifications.update', [$v->id, 'gov_id']) }}" method="POST" class="flex-fill d-flex flex-column gap-2">
                                    @csrf
                                    <input type="text" name="comment" class="form-control form-control-sm" placeholder="Optional comment / reason">
                                    <div class="d-flex gap-2">
                                        <button type="submit" name="status" value="verified" class="btn btn-sm btn-outline-success flex-fill">Approve</button>
                                        <button type="submit" name="status" value="rejected" class="btn btn-sm btn-outline-danger flex-fill">Reject</button>
                                    </div>
                                </form>
                            </div>
                        </div>

                        <!-- LinkedIn -->
                        <div class="mb-4">
                            <div class="d-flex justify-content-between align-items-center mb-1">
                                <span class="fw-bold">LinkedIn Profile</span>
                                <span class="badge bg-{{ $v->linkedin_status == 'verified' ? 'success' : ($v->linkedin_status == 'pending' ? 'warning' : 'secondary') }}">{{ ucfirst($v->linkedin_status) }}</span>
                            </div>
                            @if($experiences->count() > 0)
                                <a href="{{ $experiences->first()->linkedin_url ?? '#' }}" target="_blank" class="small d-block mb-2 text-decoration-none"><i class="bi bi-linkedin"></i> View Profile</a>
                            @endif
                            <div class="d-flex gap-2">
                                <form action="{{ route('admin.mentor.verifications.update', [$v->id, 'linkedin']) }}" method="POST" class="flex-fill d-flex flex-column gap-2">
                                    @csrf
                                    <input type="text" name="comment" class="form-control form-control-sm" placeholder="Optional comment / reason">
                                    <div class="d-flex gap-2">
                                        <button type="submit" name="status" value="verified" class="btn btn-sm btn-outline-success flex-fill">Approve</button>
                                        <button type="submit" name="status" value="rejected" class="btn btn-sm btn-outline-danger flex-fill">Reject</button>
                                    </div>
                                </form>
                            </div>
                        </div>

                        <!-- Background Check -->
                        <div class="mb-4">
                            <div class="d-flex justify-content-between align-items-center mb-2">
                                <span class="fw-bold">Background Check</span>
                                <span class="badge bg-{{ $v->background_check_status == 'verified' ? 'success' : ($v->background_check_status == 'pending' ? 'warning' : 'secondary') }}">{{ ucfirst($v->background_check_status) }}</span>
                            </div>
                            <div class="d-flex gap-2">
                                <form action="{{ route('admin.mentor.verifications.update', [$v->id, 'background_check']) }}" method="POST" class="flex-fill d-flex flex-column gap-2">
                                    @csrf
                                    <input type="text" name="comment" class="form-control form-control-sm" placeholder="Optional comment / reason">
                                    <div class="d-flex gap-2">
                                        <button type="submit" name="status" value="verified" class="btn btn-sm btn-outline-success flex-fill">Approve</button>
                                        <button type="submit" name="status" value="rejected" class="btn btn-sm btn-outline-danger flex-fill">Reject</button>
                                    </div>
                                </form>
                            </div>
                        </div>

                        <!-- Degrees -->
                        <div class="mb-4">
                            <div class="d-flex justify-content-between align-items-center mb-2">
                                <span class="fw-bold">Degree Certificates</span>
                                <span class="badge bg-{{ $v->degree_status == 'verified' ? 'success' : ($v->degree_status == 'pending' ? 'warning' : 'secondary') }}">{{ ucfirst($v->degree_status) }}</span>
                            </div>
                            <div class="d-flex gap-2">
                                <form action="{{ route('admin.mentor.verifications.update', [$v->id, 'degree']) }}" method="POST" class="flex-fill d-flex flex-column gap-2">
                                    @csrf
                                    <input type="text" name="comment" class="form-control form-control-sm" placeholder="Optional comment / reason">
                                    <div class="d-flex gap-2">
                                        <button type="submit" name="status" value="verified" class="btn btn-sm btn-outline-success flex-fill">Approve</button>
                                        <button type="submit" name="status" value="rejected" class="btn btn-sm btn-outline-danger flex-fill">Reject</button>
                                    </div>
                                </form>
                            </div>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

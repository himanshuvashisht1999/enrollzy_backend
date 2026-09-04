<div class="quick-view-content">
    <!-- Header Summary Card -->
    <div class="d-flex align-items-start justify-content-between p-3 rounded-3 mb-3" style="background: linear-gradient(135deg, #4f46e5, #4338ca); color: #fff;">
        <div class="d-flex align-items-center gap-3">
            <div class="d-flex align-items-center justify-content-center fw-bold rounded-3 text-white flex-shrink-0 shadow" 
                 style="width: 52px; height: 52px; font-size: 1.4rem; background: rgba(255,255,255,0.2);">
                <i class="fas fa-building"></i>
            </div>
            <div>
                <h5 class="mb-1 text-white fw-bold d-flex align-items-center gap-2">
                    {{ $data->department_name }}
                    @if($data->department_code)
                        <span class="badge bg-light text-dark font-monospace fs-7">{{ $data->department_code }}</span>
                    @endif
                </h5>
                <div class="d-flex align-items-center gap-2 flex-wrap text-white-50 small">
                    <span class="text-white fw-medium"><i class="fas fa-university me-1"></i>{{ $data->organisation->name ?? 'Organisation' }}</span>
                    <span>&middot;</span>
                    <span class="text-white-75"><i class="fas fa-city me-1"></i>{{ $data->campus->campus_name ?? 'All Campuses' }}</span>
                </div>
            </div>
        </div>
        <div>
            <span class="badge bg-white text-indigo border px-2.5 py-1.5"><i class="fas fa-tag me-1"></i>{{ $data->discipline_area ?: 'General' }}</span>
        </div>
    </div>

    <!-- Quick Metrics Counter Strip -->
    <div class="row g-2 mb-3">
        <div class="col-4">
            <div class="p-2.5 rounded-3 bg-light border text-center">
                <div class="text-muted small text-uppercase fw-semibold" style="font-size: 0.72rem;">Faculty Count</div>
                <div class="fs-4 fw-bold text-dark">{{ $data->faculty_count ?? 0 }}</div>
            </div>
        </div>
        <div class="col-4">
            <div class="p-2.5 rounded-3 bg-light border text-center">
                <div class="text-muted small text-uppercase fw-semibold" style="font-size: 0.72rem;">Laboratories</div>
                <div class="fs-4 fw-bold text-info">{{ $data->department_labs_count ?? 0 }}</div>
            </div>
        </div>
        <div class="col-4">
            <div class="p-2.5 rounded-3 bg-light border text-center">
                <div class="text-muted small text-uppercase fw-semibold" style="font-size: 0.72rem;">Courses Offered</div>
                <div class="fs-4 fw-bold text-success">{{ $data->courses->count() }}</div>
            </div>
        </div>
    </div>

    <!-- HOD & Staff Information -->
    <div class="card border mb-3 shadow-none">
        <div class="card-header bg-white py-2 px-3 fw-bold small text-uppercase text-muted border-bottom">
            <i class="fas fa-user-tie me-1 text-primary"></i> Head of Department & Contact
        </div>
        <div class="card-body p-3">
            <div class="row g-3 small">
                <div class="col-md-6">
                    <div class="text-muted">Head of Department (HOD):</div>
                    <div class="fw-semibold text-dark">{{ $data->head_of_department_name ?: 'Not Assigned' }}</div>
                </div>
                <div class="col-md-6">
                    <div class="text-muted">HOD Email:</div>
                    <div class="fw-semibold text-dark">{{ $data->hod_email ?: '—' }}</div>
                </div>
                <div class="col-md-6">
                    <div class="text-muted">HOD Phone:</div>
                    <div class="fw-semibold text-dark">{{ $data->hod_phone ?: '—' }}</div>
                </div>
                <div class="col-md-6">
                    <div class="text-muted">Discipline / Subject Area:</div>
                    <div class="fw-semibold text-dark">{{ $data->discipline_area ?: 'General' }}</div>
                </div>
                @if($data->description)
                    <div class="col-12">
                        <div class="text-muted">Overview / Description:</div>
                        <div class="text-muted mt-1">{{ Str::limit(strip_tags($data->description), 200) }}</div>
                    </div>
                @endif
            </div>
        </div>
    </div>

    <!-- Offered Courses in this Department -->
    <div class="card border mb-3 shadow-none">
        <div class="card-header bg-white py-2 px-3 d-flex justify-content-between align-items-center border-bottom">
            <span class="fw-bold small text-uppercase text-muted"><i class="fas fa-graduation-cap me-1 text-success"></i> Offered Programs / Courses ({{ $data->courses->count() }})</span>
            <a href="{{ route('admin.organisation-courses.create', ['organisation_id' => $data->organisation_id, 'campus_id' => $data->campus_id, 'department_id' => $data->id]) }}" class="btn btn-xs btn-outline-primary py-0 px-2" style="font-size: 0.75rem;">
                <i class="fas fa-plus me-1"></i>Add Course
            </a>
        </div>
        <div class="card-body p-0">
            @if($data->courses->count() > 0)
                <ul class="list-group list-group-flush small">
                    @foreach($data->courses as $c)
                        <li class="list-group-item d-flex justify-content-between align-items-center px-3 py-2">
                            <div>
                                <div class="fw-bold text-dark">{{ $c->course->name ?? 'Course' }}</div>
                                <div class="text-muted small">
                                    {{ $c->program_type ?? 'Full-time' }} &middot; {{ $c->duration ?? 'N/A' }} 
                                    @if($c->total_fees)
                                        &middot; <span class="fw-bold text-dark">₹{{ number_format($c->total_fees) }}</span>
                                    @endif
                                </div>
                            </div>
                            <div class="d-flex gap-1">
                                <a href="{{ route('admin.organisation-courses.edit', ['organisation_course' => $c->id, 'organisation_id' => $c->organisation_id]) }}" class="btn btn-sm btn-light border rounded px-2" title="Edit">
                                    <i class="fas fa-edit text-primary"></i>
                                </a>
                            </div>
                        </li>
                    @endforeach
                </ul>
            @else
                <div class="p-3 text-center text-muted small">No courses linked to this department yet.</div>
            @endif
        </div>
    </div>

    <!-- Modal Footer Actions -->
    <div class="d-flex justify-content-between align-items-center pt-2 border-top">
        <button type="button" class="btn btn-light border px-3" data-bs-dismiss="modal">Close</button>
        <div class="d-flex gap-2">
            <a href="{{ route('admin.departments.edit', $data->id) }}" class="btn btn-primary">
                <i class="fas fa-edit me-1"></i>Edit Department
            </a>
        </div>
    </div>
</div>

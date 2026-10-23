<div class="quick-view-content">
    <!-- Header Summary Card -->
    <div class="d-flex align-items-start justify-content-between p-3 rounded-3 mb-3" style="background: linear-gradient(135deg, #059669, #047857); color: #fff;">
        <div class="d-flex align-items-center gap-3">
            <div class="d-flex align-items-center justify-content-center fw-bold rounded-3 text-white flex-shrink-0 shadow" 
                 style="width: 52px; height: 52px; font-size: 1.4rem; background: rgba(255,255,255,0.2);">
                <i class="fas fa-graduation-cap"></i>
            </div>
            <div>
                <h5 class="mb-1 text-white fw-bold d-flex align-items-center gap-2">
                    {{ $data->course->name ?? 'Academic Program' }}
                </h5>
                <div class="d-flex align-items-center gap-2 flex-wrap text-white-50 small">
                    <span class="text-white fw-medium"><i class="fas fa-university me-1"></i>{{ $data->organisation->name ?? 'Organisation' }}</span>
                    @if($data->campus)
                        <span>&middot; <i class="fas fa-city me-1"></i>{{ $data->campus->campus_name }}</span>
                    @endif
                    @if($data->department)
                        <span>&middot; <i class="fas fa-building me-1"></i>{{ $data->department->department_name }}</span>
                    @endif
                </div>
            </div>
        </div>
        <div>
            @if($data->status)
                <span class="badge bg-white text-success border px-2.5 py-1.5"><i class="fas fa-check-circle me-1"></i>Active</span>
            @else
                <span class="badge bg-secondary bg-opacity-25 text-white border border-white-20 px-2.5 py-1.5"><i class="fas fa-clock me-1"></i>Inactive</span>
            @endif
        </div>
    </div>

    <!-- Quick Metrics Counter Strip -->
    <div class="row g-2 mb-3">
        <div class="col-4">
            <div class="p-2.5 rounded-3 bg-light border text-center">
                <div class="text-muted small text-uppercase fw-semibold" style="font-size: 0.72rem;">Program Level</div>
                <div class="fw-bold text-dark text-truncate" title="{{ $data->course->programLevel->name ?? 'General' }}">
                    {{ $data->course->programLevel->name ?? 'General' }}
                </div>
            </div>
        </div>
        <div class="col-4">
            <div class="p-2.5 rounded-3 bg-light border text-center">
                <div class="text-muted small text-uppercase fw-semibold" style="font-size: 0.72rem;">Duration / Mode</div>
                <div class="fw-bold text-primary">{{ $data->duration ?: 'N/A' }}</div>
            </div>
        </div>
        <div class="col-4">
            <div class="p-2.5 rounded-3 bg-light border text-center">
                <div class="text-muted small text-uppercase fw-semibold" style="font-size: 0.72rem;">Intake Capacity</div>
                <div class="fw-bold text-success">{{ $data->intake_capacity ? $data->intake_capacity . ' Seats' : 'N/A' }}</div>
            </div>
        </div>
    </div>

    <!-- Fees Breakdown -->
    <div class="card border mb-3 shadow-none">
        <div class="card-header bg-white py-2 px-3 fw-bold small text-uppercase text-muted border-bottom">
            <i class="fas fa-receipt me-1 text-success"></i> Fee Structure
        </div>
        <div class="card-body p-3">
            <div class="row g-3 small">
                <div class="col-md-6">
                    <div class="text-muted">Total / Annual Fees:</div>
                    <div class="fs-5 fw-bold text-dark">
                        @if($data->total_fees)
                            ₹{{ number_format($data->total_fees) }}
                        @elseif($data->fee)
                            ₹{{ is_numeric($data->fee) ? number_format($data->fee) : $data->fee }}
                        @else
                            <span class="text-muted fs-6 font-normal">Not Specified</span>
                        @endif
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="text-muted">Tuition Fee:</div>
                    <div class="fw-semibold text-dark">{{ $data->tuition_fee ? '₹' . number_format($data->tuition_fee) : '—' }}</div>
                </div>
                @if($data->admission_fee || $data->other_fee)
                    <div class="col-md-6">
                        <div class="text-muted">Admission Fee:</div>
                        <div class="fw-semibold text-dark">{{ $data->admission_fee ? '₹' . number_format($data->admission_fee) : '—' }}</div>
                    </div>
                    <div class="col-md-6">
                        <div class="text-muted">Other / Exam Fees:</div>
                        <div class="fw-semibold text-dark">{{ $data->other_fee ? '₹' . number_format($data->other_fee) : '—' }}</div>
                    </div>
                @endif
            </div>
        </div>
    </div>

    <!-- Eligibility & Requirements -->
    <div class="card border mb-3 shadow-none">
        <div class="card-header bg-white py-2 px-3 fw-bold small text-uppercase text-muted border-bottom">
            <i class="fas fa-clipboard-check me-1 text-primary"></i> Eligibility & Requirements
        </div>
        <div class="card-body p-3 small">
            <div class="mb-2">
                <div class="text-muted fw-semibold">Eligibility Criteria:</div>
                <div class="text-dark mt-1">{{ $data->eligibility_criteria ?: 'Direct Admission / Merit Based' }}</div>
            </div>
            @if($data->entrance_exams)
                <div class="mt-3">
                    <div class="text-muted fw-semibold">Accepted Entrance Exams:</div>
                    <div class="text-dark mt-1">{{ $data->entrance_exams }}</div>
                </div>
            @endif
        </div>
    </div>

    <!-- Modal Footer Actions -->
    <div class="d-flex justify-content-between align-items-center pt-2 border-top">
        <button type="button" class="btn btn-light border px-3" data-bs-dismiss="modal">Close</button>
        <div class="d-flex gap-2">
            <a href="{{ route('admin.organisation-courses.edit', ['organisation_course' => $data->id, 'organisation_id' => $data->organisation_id]) }}" class="btn btn-primary">
                <i class="fas fa-edit me-1"></i>Edit Offered Course
            </a>
        </div>
    </div>
</div>

<div class="quick-view-content">
    <!-- Header Summary Card -->
    <div class="d-flex align-items-start justify-content-between p-3 rounded-3 mb-3" style="background: linear-gradient(135deg, #0ea5e9, #0284c7); color: #fff;">
        <div class="d-flex align-items-center gap-3">
            <div class="d-flex align-items-center justify-content-center fw-bold rounded-3 text-white flex-shrink-0 shadow" 
                 style="width: 52px; height: 52px; font-size: 1.4rem; background: rgba(255,255,255,0.2);">
                <i class="fas fa-city"></i>
            </div>
            <div>
                <h5 class="mb-1 text-white fw-bold d-flex align-items-center gap-2">
                    {{ $data->campus_name }}
                    @if($data->verification_status)
                        <span class="badge bg-success text-white fs-7"><i class="fas fa-check-circle me-1"></i>Verified</span>
                    @endif
                </h5>
                <div class="d-flex align-items-center gap-2 flex-wrap text-white-50 small">
                    <span class="text-white fw-medium"><i class="fas fa-university me-1"></i>{{ $data->organisation->name ?? 'Organisation' }}</span>
                    @if($data->campus_code)
                        <span>&middot; Code: {{ $data->campus_code }}</span>
                    @endif
                </div>
            </div>
        </div>
        <div>
            @if($data->status)
                <span class="badge bg-success bg-opacity-25 text-white border border-white-20 px-2.5 py-1.5"><i class="fas fa-check-circle me-1"></i>Active</span>
            @else
                <span class="badge bg-secondary bg-opacity-25 text-white border border-white-20 px-2.5 py-1.5"><i class="fas fa-clock me-1"></i>Inactive</span>
            @endif
        </div>
    </div>

    <!-- Quick Metrics Counter Strip -->
    <div class="row g-2 mb-3">
        <div class="col-6">
            <div class="p-2.5 rounded-3 bg-light border text-center">
                <div class="text-muted small text-uppercase fw-semibold" style="font-size: 0.72rem;">Academic Departments</div>
                <div class="fs-4 fw-bold text-primary">{{ $data->departments->count() }}</div>
            </div>
        </div>
        <div class="col-6">
            <div class="p-2.5 rounded-3 bg-light border text-center">
                <div class="text-muted small text-uppercase fw-semibold" style="font-size: 0.72rem;">Offered Courses</div>
                <div class="fs-4 fw-bold text-success">{{ $data->courses->count() }}</div>
            </div>
        </div>
    </div>

    <!-- Location & Contact Grid -->
    <div class="card border mb-3 shadow-none">
        <div class="card-header bg-white py-2 px-3 fw-bold small text-uppercase text-muted border-bottom">
            <i class="fas fa-map-marked-alt me-1 text-primary"></i> Location & Contact
        </div>
        <div class="card-body p-3">
            <div class="row g-3 small">
                <div class="col-md-6">
                    <div class="text-muted">City / State:</div>
                    <div class="fw-semibold text-dark">{{ $data->city ?: 'N/A' }}{{ $data->state ? ', ' . $data->state : '' }}</div>
                </div>
                <div class="col-md-6">
                    <div class="text-muted">Pincode / Country:</div>
                    <div class="fw-semibold text-dark">{{ $data->pincode ?: '—' }} ({{ $data->country ?: 'India' }})</div>
                </div>
                <div class="col-12">
                    <div class="text-muted">Full Address:</div>
                    <div class="fw-medium text-dark">{{ $data->address ?: 'Address not specified' }}</div>
                </div>
                @if($data->phone || $data->email || $data->website)
                    <div class="col-md-6">
                        <div class="text-muted">Contact Email:</div>
                        <div class="fw-semibold text-dark">{{ $data->email ?: '—' }}</div>
                    </div>
                    <div class="col-md-6">
                        <div class="text-muted">Contact Phone:</div>
                        <div class="fw-semibold text-dark">{{ $data->phone ?: '—' }}</div>
                    </div>
                @endif
            </div>
        </div>
    </div>

    <!-- Facilities -->
    @php
        $facilities = [];
        if (!empty($data->facilities)) {
            $facilities = is_array($data->facilities) ? $data->facilities : (json_decode($data->facilities, true) ?: explode(',', $data->facilities));
        }
    @endphp
    @if(count($facilities) > 0)
        <div class="card border mb-3 shadow-none">
            <div class="card-header bg-white py-2 px-3 fw-bold small text-uppercase text-muted border-bottom">
                <i class="fas fa-cubes me-1 text-warning"></i> Campus Facilities
            </div>
            <div class="card-body p-3">
                <div class="d-flex flex-wrap gap-1.5">
                    @foreach($facilities as $fac)
                        <span class="badge bg-light text-dark border px-2.5 py-1.5"><i class="fas fa-check text-success me-1"></i>{{ trim(is_string($fac) ? $fac : (isset($fac['name']) ? $fac['name'] : 'Facility')) }}</span>
                    @endforeach
                </div>
            </div>
        </div>
    @endif

    <!-- Departments in this Campus -->
    <div class="card border mb-3 shadow-none">
        <div class="card-header bg-white py-2 px-3 d-flex justify-content-between align-items-center border-bottom">
            <span class="fw-bold small text-uppercase text-muted"><i class="fas fa-building me-1 text-primary"></i> Departments ({{ $data->departments->count() }})</span>
            <a href="{{ route('admin.departments.create', ['organisation_id' => $data->organisation_id, 'campus_id' => $data->id]) }}" class="btn btn-xs btn-outline-primary py-0 px-2" style="font-size: 0.75rem;">
                <i class="fas fa-plus me-1"></i>Add Department
            </a>
        </div>
        <div class="card-body p-0">
            @if($data->departments->count() > 0)
                <ul class="list-group list-group-flush small">
                    @foreach($data->departments as $dept)
                        <li class="list-group-item d-flex justify-content-between align-items-center px-3 py-2">
                            <div>
                                <div class="fw-bold text-dark">{{ $dept->department_name }}</div>
                                <div class="text-muted small">{{ $dept->discipline_area ?: 'General' }} &middot; {{ $dept->courses->count() }} Courses</div>
                            </div>
                            <div class="d-flex gap-1">
                                <a href="{{ route('admin.departments.edit', $dept->id) }}" class="btn btn-sm btn-light border rounded px-2" title="Edit">
                                    <i class="fas fa-edit text-primary"></i>
                                </a>
                            </div>
                        </li>
                    @endforeach
                </ul>
            @else
                <div class="p-3 text-center text-muted small">No departments configured yet.</div>
            @endif
        </div>
    </div>

    <!-- Modal Footer Actions -->
    <div class="d-flex justify-content-between align-items-center pt-2 border-top">
        <button type="button" class="btn btn-light border px-3" data-bs-dismiss="modal">Close</button>
        <div class="d-flex gap-2">
            <a href="{{ route('admin.organisations.campuses.edit', [$data->organisation_id, $data->id]) }}" class="btn btn-primary">
                <i class="fas fa-edit me-1"></i>Edit Campus
            </a>
        </div>
    </div>
</div>

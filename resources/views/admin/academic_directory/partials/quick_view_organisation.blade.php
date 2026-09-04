<div class="quick-view-content">
    <!-- Header Summary Card -->
    <div class="d-flex align-items-start justify-content-between p-3 rounded-3 mb-3" style="background: linear-gradient(135deg, #1e293b, #0f172a); color: #fff;">
        <div class="d-flex align-items-center gap-3">
            <div class="d-flex align-items-center justify-content-center fw-bold rounded-3 text-white flex-shrink-0 shadow" 
                 style="width: 52px; height: 52px; font-size: 1.4rem; background: linear-gradient(135deg, #2563eb, #3b82f6);">
                {{ strtoupper(substr($data->name, 0, 1)) }}
            </div>
            <div>
                <h5 class="mb-1 text-white fw-bold d-flex align-items-center gap-2">
                    {{ $data->name }}
                    @if($data->is_top)
                        <span class="badge bg-warning text-dark fs-7"><i class="fas fa-star me-1"></i>Top Ranked</span>
                    @endif
                </h5>
                <div class="d-flex align-items-center gap-2 flex-wrap text-white-50 small">
                    @if($data->organisation_id_number)
                        <span><i class="fas fa-hashtag me-1"></i>{{ $data->organisation_id_number }}</span>
                    @endif
                    <span class="badge bg-primary bg-opacity-25 text-white border border-white-20">{{ $data->organisationType->title ?? 'Organisation' }}</span>
                    @if($data->brand_type)
                        <span class="badge bg-info bg-opacity-25 text-white border border-white-20">{{ $data->brand_type }}</span>
                    @endif
                </div>
            </div>
        </div>
        <div>
            @if($data->status)
                <span class="badge bg-success bg-opacity-25 text-success border border-success border-opacity-50 px-2.5 py-1.5"><i class="fas fa-check-circle me-1"></i>Published</span>
            @else
                <span class="badge bg-secondary bg-opacity-25 text-white border border-white-20 px-2.5 py-1.5"><i class="fas fa-clock me-1"></i>Draft</span>
            @endif
        </div>
    </div>

    <!-- Quick Metrics Counter Strip -->
    <div class="row g-2 mb-3">
        <div class="col-4">
            <div class="p-2.5 rounded-3 bg-light border text-center">
                <div class="text-muted small text-uppercase fw-semibold" style="font-size: 0.72rem;">Campuses</div>
                <div class="fs-4 fw-bold text-dark">{{ $data->campuses->count() }}</div>
            </div>
        </div>
        <div class="col-4">
            <div class="p-2.5 rounded-3 bg-light border text-center">
                <div class="text-muted small text-uppercase fw-semibold" style="font-size: 0.72rem;">Departments</div>
                <div class="fs-4 fw-bold text-primary">
                    {{ $data->campuses->sum(function($c) { return $c->departments->count(); }) }}
                </div>
            </div>
        </div>
        <div class="col-4">
            <div class="p-2.5 rounded-3 bg-light border text-center">
                <div class="text-muted small text-uppercase fw-semibold" style="font-size: 0.72rem;">Offered Courses</div>
                <div class="fs-4 fw-bold text-success">{{ $data->courses->count() }}</div>
            </div>
        </div>
    </div>

    <!-- Details Grid -->
    <div class="card border mb-3 shadow-none">
        <div class="card-header bg-white py-2 px-3 fw-bold small text-uppercase text-muted border-bottom">
            <i class="fas fa-info-circle me-1 text-primary"></i> Key Details
        </div>
        <div class="card-body p-3">
            <div class="row g-3 small">
                <div class="col-md-6">
                    <div class="text-muted">Head Office / Location:</div>
                    <div class="fw-semibold text-dark">{{ $data->head_office_location ?: '—' }}</div>
                </div>
                <div class="col-md-6">
                    <div class="text-muted">Central Authority:</div>
                    <div class="fw-semibold text-dark">{{ $data->central_authority ?: '—' }}</div>
                </div>
                <div class="col-md-6">
                    <div class="text-muted">Official Website:</div>
                    <div class="fw-semibold">
                        @if($data->website)
                            <a href="{{ $data->website }}" target="_blank" class="text-primary text-decoration-none"><i class="fas fa-external-link-alt me-1"></i>{{ $data->website }}</a>
                        @else
                            <span class="text-muted">—</span>
                        @endif
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="text-muted">Established / Accreditation:</div>
                    <div class="fw-semibold text-dark">{{ $data->established_year ? 'Est. ' . $data->established_year : '—' }}</div>
                </div>
            </div>
        </div>
    </div>

    <!-- Campuses List Accordion/List -->
    <div class="card border mb-3 shadow-none">
        <div class="card-header bg-white py-2 px-3 d-flex justify-content-between align-items-center border-bottom">
            <span class="fw-bold small text-uppercase text-muted"><i class="fas fa-city me-1 text-info"></i> Associated Campuses ({{ $data->campuses->count() }})</span>
            <a href="{{ route('admin.organisations.campuses.create', $data->id) }}" class="btn btn-xs btn-outline-primary py-0 px-2" style="font-size: 0.75rem;">
                <i class="fas fa-plus me-1"></i>Add Campus
            </a>
        </div>
        <div class="card-body p-0">
            @if($data->campuses->count() > 0)
                <ul class="list-group list-group-flush small">
                    @foreach($data->campuses as $campus)
                        <li class="list-group-item d-flex justify-content-between align-items-center px-3 py-2.5">
                            <div>
                                <div class="fw-bold text-dark">{{ $campus->campus_name }}</div>
                                <div class="text-muted small">
                                    <i class="fas fa-map-marker-alt text-danger me-1"></i>{{ $campus->city ?: 'N/A' }}, {{ $campus->state ?: '' }}
                                    &middot; <span class="badge bg-light text-dark border">{{ $campus->departments->count() }} Depts</span>
                                </div>
                            </div>
                            <div class="d-flex gap-1">
                                <a href="{{ route('admin.organisations.campuses.edit', [$data->id, $campus->id]) }}" class="btn btn-sm btn-light border rounded px-2" title="Edit Campus">
                                    <i class="fas fa-edit text-primary"></i>
                                </a>
                            </div>
                        </li>
                    @endforeach
                </ul>
            @else
                <div class="p-3 text-center text-muted small">No campuses registered yet.</div>
            @endif
        </div>
    </div>

    <!-- Modal Footer Actions -->
    <div class="d-flex justify-content-between align-items-center pt-2 border-top">
        <button type="button" class="btn btn-light border px-3" data-bs-dismiss="modal">Close</button>
        <div class="d-flex gap-2">
            <a href="{{ route('admin.organisations.campuses.index', $data->id) }}" class="btn btn-info text-white">
                <i class="fas fa-city me-1"></i>Manage Campuses
            </a>
            <a href="{{ route('admin.organisations.edit', $data->id) }}" class="btn btn-primary">
                <i class="fas fa-edit me-1"></i>Edit Organisation
            </a>
        </div>
    </div>
</div>

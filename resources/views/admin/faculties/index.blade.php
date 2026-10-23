@extends('admin.layouts.master')

@section('content')
<div class="container-fluid">
    {{-- Header --}}
    <div class="row mb-3">
        <div class="col-12 d-flex justify-content-between align-items-center flex-wrap gap-2">
            <div>
                <h4 class="mb-1 text-dark fw-bold">
                    <i class="fas fa-chalkboard-teacher text-primary me-2"></i>Faculty Management
                </h4>
                <p class="text-muted small mb-0">Manage faculty profiles across universities, colleges, schools, and coaching institutes.</p>
            </div>
            <div class="d-flex gap-2">
                <a href="{{ route('admin.academic-directory.index') }}" class="btn btn-outline-secondary btn-sm">
                    <i class="fas fa-sitemap me-1"></i> Academic Directory
                </a>
                <a href="{{ route('admin.faculties.create') }}" class="btn btn-primary btn-sm">
                    <i class="fas fa-plus-circle me-1"></i> Add Faculty Profile
                </a>
            </div>
        </div>
    </div>

    {{-- Stats Cards --}}
    <div class="row g-3 mb-4">
        <div class="col-xl-3 col-sm-6">
            <div class="card shadow-sm border-0 border-start border-primary border-4 h-100">
                <div class="card-body d-flex justify-content-between align-items-center py-3">
                    <div>
                        <div class="text-muted small text-uppercase fw-bold">Total Faculties</div>
                        <h3 class="mb-0 fw-bold text-dark">{{ $stats['total'] }}</h3>
                    </div>
                    <div class="avatar bg-primary-subtle text-primary rounded-circle p-3">
                        <i class="fas fa-users fa-lg text-primary"></i>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-xl-3 col-sm-6">
            <div class="card shadow-sm border-0 border-start border-success border-4 h-100">
                <div class="card-body d-flex justify-content-between align-items-center py-3">
                    <div>
                        <div class="text-muted small text-uppercase fw-bold">Verified Profiles</div>
                        <h3 class="mb-0 fw-bold text-success">{{ $stats['verified'] }}</h3>
                    </div>
                    <div class="avatar bg-success-subtle text-success rounded-circle p-3">
                        <i class="fas fa-check-circle fa-lg text-success"></i>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-xl-3 col-sm-6">
            <div class="card shadow-sm border-0 border-start border-warning border-4 h-100">
                <div class="card-body d-flex justify-content-between align-items-center py-3">
                    <div>
                        <div class="text-muted small text-uppercase fw-bold">Pending Verification</div>
                        <h3 class="mb-0 fw-bold text-warning">{{ $stats['pending'] }}</h3>
                    </div>
                    <div class="avatar bg-warning-subtle text-warning rounded-circle p-3">
                        <i class="fas fa-clock fa-lg text-warning"></i>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-xl-3 col-sm-6">
            <div class="card shadow-sm border-0 border-start border-info border-4 h-100">
                <div class="card-body d-flex justify-content-between align-items-center py-3">
                    <div>
                        <div class="text-muted small text-uppercase fw-bold">Active Status</div>
                        <h3 class="mb-0 fw-bold text-info">{{ $stats['active'] }}</h3>
                    </div>
                    <div class="avatar bg-info-subtle text-info rounded-circle p-3">
                        <i class="fas fa-user-check fa-lg text-info"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Filters Card --}}
    <div class="card shadow-sm border-0 mb-4">
        <div class="card-body">
            <form action="{{ route('admin.faculties.index') }}" method="GET" class="row g-2 align-items-end">
                <div class="col-md-3">
                    <label class="form-label small fw-bold">Search Faculty</label>
                    <div class="input-group input-group-sm">
                        <span class="input-group-text bg-white"><i class="fas fa-search text-muted"></i></span>
                        <input type="text" name="search" class="form-control" placeholder="Name, Code, Email..." value="{{ request('search') }}">
                    </div>
                </div>
                <div class="col-md-2">
                    <label class="form-label small fw-bold">Organisation</label>
                    <select name="organisation_id" class="form-select form-select-sm">
                        <option value="">All Organisations</option>
                        @foreach($organisations as $org)
                            <option value="{{ $org->id }}" {{ request('organisation_id') == $org->id ? 'selected' : '' }}>{{ $org->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2">
                    <label class="form-label small fw-bold">Designation</label>
                    <select name="designation_id" class="form-select form-select-sm">
                        <option value="">All Designations</option>
                        @foreach($designations as $desig)
                            <option value="{{ $desig->id }}" {{ request('designation_id') == $desig->id ? 'selected' : '' }}>{{ $desig->name }} ({{ ucfirst($desig->institution_category) }})</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2">
                    <label class="form-label small fw-bold">Verification</label>
                    <select name="verification_status" class="form-select form-select-sm">
                        <option value="">All Statuses</option>
                        <option value="verified" {{ request('verification_status') == 'verified' ? 'selected' : '' }}>Verified</option>
                        <option value="pending" {{ request('verification_status') == 'pending' ? 'selected' : '' }}>Pending</option>
                        <option value="unverified" {{ request('verification_status') == 'unverified' ? 'selected' : '' }}>Unverified</option>
                        <option value="rejected" {{ request('verification_status') == 'rejected' ? 'selected' : '' }}>Rejected</option>
                    </select>
                </div>
                <div class="col-md-1">
                    <label class="form-label small fw-bold">Status</label>
                    <select name="status" class="form-select form-select-sm">
                        <option value="">All</option>
                        <option value="1" {{ request('status') === '1' ? 'selected' : '' }}>Active</option>
                        <option value="0" {{ request('status') === '0' ? 'selected' : '' }}>Inactive</option>
                    </select>
                </div>
                <div class="col-md-2 d-flex gap-1">
                    <button type="submit" class="btn btn-primary btn-sm flex-grow-1">
                        <i class="fas fa-filter me-1"></i> Filter
                    </button>
                    <a href="{{ route('admin.faculties.index') }}" class="btn btn-outline-secondary btn-sm" title="Reset Filters">
                        <i class="fas fa-undo"></i>
                    </a>
                </div>
            </form>
        </div>
    </div>

    {{-- Data Table Card --}}
    <div class="card shadow-sm border-0">
        <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center">
            <h6 class="mb-0 fw-bold text-dark">
                Faculty List <span class="badge bg-secondary ms-1">{{ $faculties->total() }}</span>
            </h6>
            <div>
                <button type="button" id="bulkDeleteBtn" class="btn btn-outline-danger btn-sm" disabled onclick="confirmBulkDelete()">
                    <i class="fas fa-trash-alt me-1"></i> Delete Selected (<span id="selectedCount">0</span>)
                </button>
            </div>
        </div>

        <form id="bulkDeleteForm" action="{{ route('admin.faculties.bulk-destroy') }}" method="POST" style="display: none;">
            @csrf
            <div id="bulkDeleteIdsContainer"></div>
        </form>

        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light text-muted small text-uppercase">
                    <tr>
                        <th width="40" class="text-center">
                            <input type="checkbox" class="form-check-input" id="selectAll">
                        </th>
                        <th>Faculty Member</th>
                        <th>Designation & Type</th>
                        <th>Associated Organisations & Depts</th>
                        <th>Experience</th>
                        <th class="text-center">Verification</th>
                        <th class="text-center">Status</th>
                        <th width="150" class="text-end">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($faculties as $faculty)
                    <tr>
                        <td class="text-center">
                            <input type="checkbox" class="form-check-input row-checkbox" value="{{ $faculty->id }}">
                        </td>
                        <td>
                            <div class="d-flex align-items-center gap-3">
                                <img src="{{ $faculty->photo_url }}" alt="{{ $faculty->full_name }}" class="rounded-circle border" style="width: 48px; height: 48px; object-fit: cover;">
                                <div>
                                    <a href="{{ route('admin.faculties.show', $faculty->id) }}" class="fw-bold text-dark text-decoration-none">
                                        {{ $faculty->full_name }}
                                    </a>
                                    @if($faculty->display_name)
                                        <small class="text-muted d-block">({{ $faculty->display_name }})</small>
                                    @endif
                                    <div class="small text-muted font-monospace">{{ $faculty->faculty_code }}</div>
                                </div>
                            </div>
                        </td>
                        <td>
                            <div class="fw-semibold text-dark">{{ $faculty->effective_designation }}</div>
                            <small class="badge bg-light text-dark border">
                                {{ $faculty->facultyType->name ?? 'Standard' }}
                            </small>
                        </td>
                        <td>
                            @if($faculty->departmentMappings->count() > 0)
                                <div class="d-flex flex-column gap-1">
                                    @foreach($faculty->departmentMappings->take(2) as $mapping)
                                        <div class="small">
                                            <i class="fas fa-university text-muted me-1"></i>
                                            <strong>{{ $mapping->organisation->name ?? 'N/A' }}</strong>
                                            @if($mapping->department)
                                                <span class="text-muted">/ {{ $mapping->department->department_name }}</span>
                                            @endif
                                            @if($mapping->is_primary_department)
                                                <span class="badge bg-primary-subtle text-primary border ms-1" style="font-size: 10px;">Primary</span>
                                            @endif
                                        </div>
                                    @endforeach
                                    @if($faculty->departmentMappings->count() > 2)
                                        <small class="text-primary">+{{ $faculty->departmentMappings->count() - 2 }} more</small>
                                    @endif
                                </div>
                            @else
                                <span class="text-muted small">Not mapped yet</span>
                            @endif
                        </td>
                        <td>
                            <span class="fw-semibold">{{ $faculty->experience_years }}</span> <small class="text-muted">Years</small>
                        </td>
                        <td class="text-center">
                            @if($faculty->verification_status === 'verified')
                                <span class="badge bg-success-subtle text-success border border-success px-2 py-1">
                                    <i class="fas fa-check-circle me-1"></i> Verified
                                </span>
                            @elseif($faculty->verification_status === 'pending')
                                <span class="badge bg-warning-subtle text-warning border border-warning px-2 py-1">
                                    <i class="fas fa-clock me-1"></i> Pending
                                </span>
                            @elseif($faculty->verification_status === 'rejected')
                                <span class="badge bg-danger-subtle text-danger border border-danger px-2 py-1">
                                    <i class="fas fa-times-circle me-1"></i> Rejected
                                </span>
                            @else
                                <span class="badge bg-secondary-subtle text-secondary border px-2 py-1">
                                    Unverified
                                </span>
                            @endif
                            <br>
                            <button type="button" class="btn btn-link btn-sm p-0 mt-1 text-decoration-none" style="font-size: 11px;"
                                onclick="openVerifyModal('{{ $faculty->id }}', '{{ $faculty->full_name }}', '{{ $faculty->verification_status }}', '{{ addslashes($faculty->verification_notes) }}')">
                                Change
                            </button>
                        </td>
                        <td class="text-center">
                            <div class="form-check form-switch d-inline-block">
                                <input class="form-check-input status-toggle" type="checkbox" role="switch"
                                    data-id="{{ $faculty->id }}" {{ $faculty->status ? 'checked' : '' }}>
                            </div>
                        </td>
                        <td class="text-end">
                            <div class="btn-group btn-group-sm">
                                <a href="{{ route('admin.faculties.show', $faculty->id) }}" class="btn btn-outline-info" title="View Profile">
                                    <i class="fas fa-eye"></i>
                                </a>
                                <a href="{{ route('admin.faculties.edit', $faculty->id) }}" class="btn btn-outline-primary" title="Edit Profile">
                                    <i class="fas fa-edit"></i>
                                </a>
                                <form action="{{ route('admin.faculties.destroy', $faculty->id) }}" method="POST" class="d-inline" onsubmit="return confirm('Delete this faculty profile?');">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-outline-danger" title="Delete Profile">
                                        <i class="fas fa-trash-alt"></i>
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="8" class="text-center py-5">
                            <div class="text-muted">
                                <i class="fas fa-user-graduate fa-3x mb-3 text-secondary"></i>
                                <h5 class="fw-bold">No Faculty Profiles Found</h5>
                                <p class="small text-muted mb-3">Get started by creating your first faculty profile or adjust your search filters.</p>
                                <a href="{{ route('admin.faculties.create') }}" class="btn btn-primary btn-sm">
                                    <i class="fas fa-plus-circle me-1"></i> Add Faculty Profile
                                </a>
                            </div>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($faculties->hasPages())
        <div class="card-footer bg-white d-flex justify-content-between align-items-center py-3">
            <div class="small text-muted">
                Showing {{ $faculties->firstItem() ?? 0 }} to {{ $faculties->lastItem() ?? 0 }} of {{ $faculties->total() }} faculties
            </div>
            <div>
                {{ $faculties->links('pagination::bootstrap-5') }}
            </div>
        </div>
        @endif
    </div>
</div>

{{-- Quick Verification Modal --}}
<div class="modal fade" id="verifyModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <form id="verifyForm" method="POST">
            @csrf
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title fw-bold">Update Faculty Verification</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label fw-bold">Faculty</label>
                        <p id="modalFacultyName" class="text-dark fw-semibold mb-0"></p>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-bold">Verification Status <span class="text-danger">*</span></label>
                        <select name="verification_status" id="modalVerifyStatus" class="form-select" required>
                            <option value="pending">Pending</option>
                            <option value="verified">Verified (Show Verified Badge)</option>
                            <option value="unverified">Unverified</option>
                            <option value="rejected">Rejected</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-bold">Verification Notes / Remarks</label>
                        <textarea name="verification_notes" id="modalVerifyNotes" class="form-control" rows="3" placeholder="Source documents checked, employee ID verified, etc."></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary btn-sm">Save Verification Status</button>
                </div>
            </div>
        </form>
    </div>
</div>

@push('js')
<script>
    // Selection & Bulk Delete
    const selectAll = document.getElementById('selectAll');
    const rowCheckboxes = document.querySelectorAll('.row-checkbox');
    const bulkDeleteBtn = document.getElementById('bulkDeleteBtn');
    const selectedCount = document.getElementById('selectedCount');

    function updateBulkDeleteState() {
        const checked = document.querySelectorAll('.row-checkbox:checked');
        if (selectedCount) selectedCount.textContent = checked.length;
        if (bulkDeleteBtn) bulkDeleteBtn.disabled = checked.length === 0;
    }

    if (selectAll) {
        selectAll.addEventListener('change', function() {
            rowCheckboxes.forEach(cb => cb.checked = selectAll.checked);
            updateBulkDeleteState();
        });
    }

    rowCheckboxes.forEach(cb => {
        cb.addEventListener('change', updateBulkDeleteState);
    });

    window.confirmBulkDelete = function() {
        const checked = document.querySelectorAll('.row-checkbox:checked');
        if (checked.length === 0) return;
        if (confirm(`Are you sure you want to delete ${checked.length} selected faculties?`)) {
            const form = document.getElementById('bulkDeleteForm');
            const container = document.getElementById('bulkDeleteIdsContainer');
            container.innerHTML = '';
            checked.forEach(cb => {
                const input = document.createElement('input');
                input.type = 'hidden';
                input.name = 'ids[]';
                input.value = cb.value;
                container.appendChild(input);
            });
            form.submit();
        }
    };

    // Toggle Status
    document.querySelectorAll('.status-toggle').forEach(el => {
        el.addEventListener('change', function() {
            const facultyId = this.dataset.id;
            fetch(`/admin/faculties/${facultyId}/toggle-status`, {
                method: 'PATCH',
                headers: {
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    'Accept': 'application/json'
                }
            })
            .then(res => res.json())
            .then(data => {
                if (!data.success) {
                    alert('Error changing status');
                    this.checked = !this.checked;
                }
            })
            .catch(() => {
                alert('Request failed');
                this.checked = !this.checked;
            });
        });
    });

    // Verification Modal
    window.openVerifyModal = function(id, name, status, notes) {
        const modal = new bootstrap.Modal(document.getElementById('verifyModal'));
        document.getElementById('modalFacultyName').textContent = name;
        document.getElementById('modalVerifyStatus').value = status;
        document.getElementById('modalVerifyNotes').value = notes || '';
        document.getElementById('verifyForm').action = `/admin/faculties/${id}/verification`;
        modal.show();
    };
</script>
@endpush
@endsection

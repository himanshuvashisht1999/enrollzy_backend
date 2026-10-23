@extends('admin.layouts.master')

@section('content')
<div class="container-fluid">
    {{-- Header --}}
    <div class="row mb-3">
        <div class="col-12 d-flex justify-content-between align-items-center flex-wrap gap-2">
            <div>
                <h4 class="mb-1 text-dark fw-bold">
                    <i class="fas fa-id-badge text-primary me-2"></i>Faculty Designations Master
                </h4>
                <p class="text-muted small mb-0">Manage reusable designations categorized across universities, colleges, schools, and coaching institutes.</p>
            </div>
            <div class="d-flex gap-2">
                <a href="{{ route('admin.faculties.index') }}" class="btn btn-outline-secondary btn-sm">
                    <i class="fas fa-users me-1"></i> Faculty Master
                </a>
                <button type="button" class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#createDesignationModal">
                    <i class="fas fa-plus-circle me-1"></i> Add Designation
                </button>
            </div>
        </div>
    </div>

    {{-- Category Filter Pills --}}
    <div class="card shadow-sm border-0 mb-4">
        <div class="card-body p-2 d-flex gap-2 flex-wrap align-items-center">
            <span class="small fw-bold text-muted ms-2 me-1">Filter by Category:</span>
            <a href="{{ route('admin.faculty-designations.index') }}" class="btn btn-sm {{ !request('category') || request('category') == 'all' ? 'btn-primary' : 'btn-light border' }}">
                All ({{ $counts['total'] }})
            </a>
            <a href="{{ route('admin.faculty-designations.index', ['category' => 'university']) }}" class="btn btn-sm {{ request('category') == 'university' ? 'btn-primary' : 'btn-light border' }}">
                <i class="fas fa-university me-1"></i> University / Higher Ed ({{ $counts['university'] }})
            </a>
            <a href="{{ route('admin.faculty-designations.index', ['category' => 'school']) }}" class="btn btn-sm {{ request('category') == 'school' ? 'btn-primary' : 'btn-light border' }}">
                <i class="fas fa-school me-1"></i> School ({{ $counts['school'] }})
            </a>
            <a href="{{ route('admin.faculty-designations.index', ['category' => 'coaching']) }}" class="btn btn-sm {{ request('category') == 'coaching' ? 'btn-primary' : 'btn-light border' }}">
                <i class="fas fa-chalkboard me-1"></i> Coaching & Test Prep ({{ $counts['coaching'] }})
            </a>
        </div>
    </div>

    {{-- Table Card --}}
    <div class="card shadow-sm border-0">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light text-muted small text-uppercase">
                    <tr>
                        <th width="60" class="text-center">#</th>
                        <th>Designation Title</th>
                        <th>Institution Category</th>
                        <th class="text-center">Hierarchy Order</th>
                        <th class="text-center">Assigned Faculty</th>
                        <th class="text-center">Status</th>
                        <th width="120" class="text-end">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($designations as $desig)
                    <tr>
                        <td class="text-center text-muted small">{{ $loop->iteration + ($designations->currentPage() - 1) * $designations->perPage() }}</td>
                        <td>
                            <strong class="text-dark">{{ $desig->name }}</strong>
                            <div class="small font-monospace text-muted">{{ $desig->slug }}</div>
                        </td>
                        <td>
                            @if($desig->institution_category === 'university')
                                <span class="badge bg-primary-subtle text-primary border">University</span>
                            @elseif($desig->institution_category === 'school')
                                <span class="badge bg-success-subtle text-success border">School</span>
                            @elseif($desig->institution_category === 'coaching')
                                <span class="badge bg-warning-subtle text-warning border">Coaching</span>
                            @else
                                <span class="badge bg-secondary-subtle text-secondary border">General / All</span>
                            @endif
                        </td>
                        <td class="text-center">
                            <span class="badge bg-light text-dark border">{{ $desig->hierarchy_order }}</span>
                        </td>
                        <td class="text-center">
                            <span class="badge bg-info-subtle text-dark border">{{ $desig->profiles_count }} Profiles</span>
                        </td>
                        <td class="text-center">
                            <div class="form-check form-switch d-inline-block">
                                <input class="form-check-input status-toggle" type="checkbox" role="switch"
                                    data-id="{{ $desig->id }}" {{ $desig->status ? 'checked' : '' }}>
                            </div>
                        </td>
                        <td class="text-end">
                            <div class="btn-group btn-group-sm">
                                <button type="button" class="btn btn-outline-primary"
                                    onclick="editDesignation({{ json_encode($desig) }})" title="Edit">
                                    <i class="fas fa-edit"></i>
                                </button>
                                <form action="{{ route('admin.faculty-designations.destroy', $desig->id) }}" method="POST" class="d-inline" onsubmit="return confirm('Delete this designation?');">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-outline-danger" title="Delete" {{ $desig->profiles_count > 0 ? 'disabled' : '' }}>
                                        <i class="fas fa-trash-alt"></i>
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="7" class="text-center py-4 text-muted">No designations found.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($designations->hasPages())
        <div class="card-footer bg-white d-flex justify-content-between align-items-center py-3">
            <div class="small text-muted">
                Showing {{ $designations->firstItem() }} to {{ $designations->lastItem() }} of {{ $designations->total() }} designations
            </div>
            <div>
                {{ $designations->links('pagination::bootstrap-5') }}
            </div>
        </div>
        @endif
    </div>
</div>

{{-- Create Modal --}}
<div class="modal fade" id="createDesignationModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <form action="{{ route('admin.faculty-designations.store') }}" method="POST">
            @csrf
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title fw-bold">Add New Designation</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label fw-bold">Designation Title <span class="text-danger">*</span></label>
                        <input type="text" name="name" class="form-control" placeholder="e.g. Associate Professor / PGT / Senior Faculty" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-bold">Institution Category <span class="text-danger">*</span></label>
                        <select name="institution_category" class="form-select" required>
                            <option value="university">University / Higher Education</option>
                            <option value="school">School Education</option>
                            <option value="coaching">Coaching & Competitive Institute</option>
                            <option value="all">General / Cross-institution</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-bold">Hierarchy Order</label>
                        <input type="number" name="hierarchy_order" class="form-control" value="0">
                        <small class="text-muted">Lower number ranks higher in profile listings.</small>
                    </div>
                    <div class="form-check form-switch">
                        <input class="form-check-input" type="checkbox" name="status" value="1" id="createStatusSwitch" checked>
                        <label class="form-check-label fw-bold" for="createStatusSwitch">Active</label>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary btn-sm">Save Designation</button>
                </div>
            </div>
        </form>
    </div>
</div>

{{-- Edit Modal --}}
<div class="modal fade" id="editDesignationModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <form id="editDesignationForm" method="POST">
            @csrf
            @method('PUT')
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title fw-bold">Edit Designation</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label fw-bold">Designation Title <span class="text-danger">*</span></label>
                        <input type="text" name="name" id="editName" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-bold">Institution Category <span class="text-danger">*</span></label>
                        <select name="institution_category" id="editCategory" class="form-select" required>
                            <option value="university">University / Higher Education</option>
                            <option value="school">School Education</option>
                            <option value="coaching">Coaching & Competitive Institute</option>
                            <option value="all">General / Cross-institution</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-bold">Hierarchy Order</label>
                        <input type="number" name="hierarchy_order" id="editOrder" class="form-control">
                    </div>
                    <div class="form-check form-switch">
                        <input class="form-check-input" type="checkbox" name="status" value="1" id="editStatus">
                        <label class="form-check-label fw-bold" for="editStatus">Active</label>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary btn-sm">Update Designation</button>
                </div>
            </div>
        </form>
    </div>
</div>

@push('js')
<script>
    window.editDesignation = function(item) {
        const form = document.getElementById('editDesignationForm');
        form.action = `/admin/faculty-designations/${item.id}`;
        document.getElementById('editName').value = item.name;
        document.getElementById('editCategory').value = item.institution_category;
        document.getElementById('editOrder').value = item.hierarchy_order;
        document.getElementById('editStatus').checked = item.status ? true : false;

        const modal = new bootstrap.Modal(document.getElementById('editDesignationModal'));
        modal.show();
    };

    document.querySelectorAll('.status-toggle').forEach(el => {
        el.addEventListener('change', function() {
            const desigId = this.dataset.id;
            fetch(`/admin/faculty-designations/${desigId}/toggle-status`, {
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
</script>
@endpush
@endsection

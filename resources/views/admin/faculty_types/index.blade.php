@extends('admin.layouts.master')

@section('content')
<div class="container-fluid">
    {{-- Header --}}
    <div class="row mb-3">
        <div class="col-12 d-flex justify-content-between align-items-center flex-wrap gap-2">
            <div>
                <h4 class="mb-1 text-dark fw-bold">
                    <i class="fas fa-user-clock text-primary me-2"></i>Faculty Types Master
                </h4>
                <p class="text-muted small mb-0">Manage faculty employment types (Permanent, Visiting, Adjunct, Guest, Fellow, etc.).</p>
            </div>
            <div class="d-flex gap-2">
                <a href="{{ route('admin.faculties.index') }}" class="btn btn-outline-secondary btn-sm">
                    <i class="fas fa-users me-1"></i> Faculty Master
                </a>
                <button type="button" class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#createTypeModal">
                    <i class="fas fa-plus-circle me-1"></i> Add Faculty Type
                </button>
            </div>
        </div>
    </div>

    {{-- Table Card --}}
    <div class="card shadow-sm border-0">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light text-muted small text-uppercase">
                    <tr>
                        <th width="60" class="text-center">#</th>
                        <th>Type Name</th>
                        <th class="text-center">Assigned Faculty</th>
                        <th class="text-center">Status</th>
                        <th width="120" class="text-end">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($types as $type)
                    <tr>
                        <td class="text-center text-muted small">{{ $loop->iteration + ($types->currentPage() - 1) * $types->perPage() }}</td>
                        <td>
                            <strong class="text-dark">{{ $type->name }}</strong>
                        </td>
                        <td class="text-center">
                            <span class="badge bg-info-subtle text-dark border">{{ $type->profiles_count }} Profiles</span>
                        </td>
                        <td class="text-center">
                            <div class="form-check form-switch d-inline-block">
                                <input class="form-check-input status-toggle" type="checkbox" role="switch"
                                    data-id="{{ $type->id }}" {{ $type->status ? 'checked' : '' }}>
                            </div>
                        </td>
                        <td class="text-end">
                            <div class="btn-group btn-group-sm">
                                <button type="button" class="btn btn-outline-primary"
                                    onclick="editType({{ json_encode($type) }})" title="Edit">
                                    <i class="fas fa-edit"></i>
                                </button>
                                <form action="{{ route('admin.faculty-types.destroy', $type->id) }}" method="POST" class="d-inline" onsubmit="return confirm('Delete this faculty type?');">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-outline-danger" title="Delete" {{ $type->profiles_count > 0 ? 'disabled' : '' }}>
                                        <i class="fas fa-trash-alt"></i>
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="5" class="text-center py-4 text-muted">No faculty types found.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($types->hasPages())
        <div class="card-footer bg-white d-flex justify-content-between align-items-center py-3">
            <div class="small text-muted">
                Showing {{ $types->firstItem() }} to {{ $types->lastItem() }} of {{ $types->total() }} types
            </div>
            <div>
                {{ $types->links('pagination::bootstrap-5') }}
            </div>
        </div>
        @endif
    </div>
</div>

{{-- Create Modal --}}
<div class="modal fade" id="createTypeModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <form action="{{ route('admin.faculty-types.store') }}" method="POST">
            @csrf
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title fw-bold">Add Faculty Type</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label fw-bold">Faculty Type Name <span class="text-danger">*</span></label>
                        <input type="text" name="name" class="form-control" placeholder="e.g. Permanent / Visiting / Adjunct" required>
                    </div>
                    <div class="form-check form-switch">
                        <input class="form-check-input" type="checkbox" name="status" value="1" id="createTypeStatus" checked>
                        <label class="form-check-label fw-bold" for="createTypeStatus">Active</label>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary btn-sm">Save Type</button>
                </div>
            </div>
        </form>
    </div>
</div>

{{-- Edit Modal --}}
<div class="modal fade" id="editTypeModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <form id="editTypeForm" method="POST">
            @csrf
            @method('PUT')
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title fw-bold">Edit Faculty Type</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label fw-bold">Faculty Type Name <span class="text-danger">*</span></label>
                        <input type="text" name="name" id="editTypeName" class="form-control" required>
                    </div>
                    <div class="form-check form-switch">
                        <input class="form-check-input" type="checkbox" name="status" value="1" id="editTypeStatus">
                        <label class="form-check-label fw-bold" for="editTypeStatus">Active</label>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary btn-sm">Update Type</button>
                </div>
            </div>
        </form>
    </div>
</div>

@push('js')
<script>
    window.editType = function(item) {
        const form = document.getElementById('editTypeForm');
        form.action = `/admin/faculty-types/${item.id}`;
        document.getElementById('editTypeName').value = item.name;
        document.getElementById('editTypeStatus').checked = item.status ? true : false;

        const modal = new bootstrap.Modal(document.getElementById('editTypeModal'));
        modal.show();
    };

    document.querySelectorAll('.status-toggle').forEach(el => {
        el.addEventListener('change', function() {
            const typeId = this.dataset.id;
            fetch(`/admin/faculty-types/${typeId}/toggle-status`, {
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

@extends('admin.layouts.master')

@section('title', 'Facilities Master')

@section('content')
<div class="row mb-3">
    <div class="col-12 d-flex justify-content-between align-items-center">
        <h4 class="mb-0">Facilities</h4>
        <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#facilityModal" onclick="resetForm()">
            <i class="fas fa-plus me-1"></i> Add Facility
        </button>
    </div>
</div>

<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-body">
                @if(session('success'))
                    <div class="alert alert-success alert-dismissible fade show" role="alert">
                        {{ session('success') }}
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                @endif
                @if($errors->any())
                    <div class="alert alert-danger alert-dismissible fade show" role="alert">
                        <ul class="mb-0">
                            @foreach($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                @endif

                <div class="table-responsive">
                    <table class="table table-bordered table-striped">
                        <thead>
                            <tr>
                                <th style="width: 50px;">#</th>
                                <th style="width: 100px;">Icon</th>
                                <th>Name</th>
                                <th style="width: 100px;">Status</th>
                                <th style="width: 150px;">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($facilities as $facility)
                                <tr>
                                    <td>{{ $loop->iteration }}</td>
                                    <td class="text-center">
                                        @if($facility->icon)
                                            <i class="{{ $facility->icon }} fa-2x text-muted"></i>
                                        @else
                                            <span class="text-muted">-</span>
                                        @endif
                                    </td>
                                    <td>{{ $facility->name }}</td>
                                    <td>
                                        @if($facility->status)
                                            <span class="badge bg-success">Active</span>
                                        @else
                                            <span class="badge bg-danger">Inactive</span>
                                        @endif
                                    </td>
                                    <td>
                                        <button class="btn btn-sm btn-info text-white" onclick="editFacility({{ json_encode($facility) }})">
                                            <i class="fas fa-edit"></i>
                                        </button>
                                        <form action="{{ route('admin.facilities.destroy', $facility->id) }}" method="POST" class="d-inline" onsubmit="return confirm('Are you sure you want to delete this facility?');">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-sm btn-danger">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        </form>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="text-center py-4">No facilities added yet.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal -->
<div class="modal fade" id="facilityModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <form id="facilityForm" method="POST" action="{{ route('admin.facilities.store') }}">
            @csrf
            <input type="hidden" name="_method" id="formMethod" value="POST">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="modalTitle">Add Facility</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Name <span class="text-danger">*</span></label>
                        <input type="text" name="name" id="name" class="form-control" required placeholder="e.g. Wifi, Library">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Icon Class</label>
                        <input type="text" name="icon" id="icon" class="form-control" placeholder="e.g. fas fa-wifi">
                        <small class="text-muted">Use FontAwesome 5 class names.</small>
                    </div>
                    <div class="mb-3 form-check form-switch">
                        <input class="form-check-input" type="checkbox" name="status" id="status" value="1" checked>
                        <label class="form-check-label" for="status">Active</label>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Save Facility</button>
                </div>
            </div>
        </form>
    </div>
</div>
@endsection

@push('js')
<script>
    function resetForm() {
        $('#facilityForm').attr('action', '{{ route('admin.facilities.store') }}');
        $('#formMethod').val('POST');
        $('#modalTitle').text('Add Facility');
        $('#name').val('');
        $('#icon').val('');
        $('#status').prop('checked', true);
    }

    function editFacility(facility) {
        $('#facilityForm').attr('action', `/masters/facilities/${facility.id}`);
        $('#formMethod').val('PUT');
        $('#modalTitle').text('Edit Facility');
        $('#name').val(facility.name);
        $('#icon').val(facility.icon);
        $('#status').prop('checked', facility.status);
        $('#facilityModal').modal('show');
    }
</script>
@endpush

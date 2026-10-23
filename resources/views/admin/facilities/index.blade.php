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
                    <table class="table table-bordered table-striped align-middle">
                        <thead>
                            <tr>
                                <th style="width: 50px;">#</th>
                                <th style="width: 80px;">Icon</th>
                                <th style="min-width: 150px;">Name</th>
                                <th>Organisation Types</th>
                                <th style="width: 100px;">Status</th>
                                <th style="width: 130px;">Actions</th>
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
                                    <td class="fw-bold">{{ $facility->name }}</td>
                                    <td>
                                        @if($facility->organisationTypes->count() === $organisationTypes->count() && $organisationTypes->count() > 0)
                                            <span class="badge bg-primary">All Organisation Types</span>
                                        @else
                                            <div class="d-flex flex-wrap gap-1">
                                                @forelse($facility->organisationTypes as $type)
                                                    <span class="badge bg-light text-dark border">{{ $type->title }}</span>
                                                @empty
                                                    <span class="badge bg-secondary">None</span>
                                                @endforelse
                                            </div>
                                        @endif
                                    </td>
                                    <td>
                                        @if($facility->status)
                                            <span class="badge bg-success">Active</span>
                                        @else
                                            <span class="badge bg-danger">Inactive</span>
                                        @endif
                                    </td>
                                    <td>
                                        <button class="btn btn-sm btn-info text-white me-1" data-facility='@json($facility)' onclick="editFacility(this)">
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
                                    <td colspan="6" class="text-center py-4">No facilities added yet.</td>
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
    <div class="modal-dialog modal-lg">
        <form id="facilityForm" method="POST" action="{{ route('admin.facilities.store') }}">
            @csrf
            <input type="hidden" name="_method" id="formMethod" value="POST">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="modalTitle">Add Facility</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-7 mb-3">
                            <label class="form-label fw-semibold">Name <span class="text-danger">*</span></label>
                            <input type="text" name="name" id="name" class="form-control" required placeholder="e.g. Wifi, Library">
                        </div>
                        <div class="col-md-5 mb-3">
                            <label class="form-label fw-semibold">Icon Class</label>
                            <input type="text" name="icon" id="icon" class="form-control" placeholder="e.g. fas fa-wifi">
                            <small class="text-muted">Use FontAwesome 5 class names.</small>
                        </div>
                    </div>

                    <div class="mb-3">
                        <div class="d-flex justify-content-between align-items-center mb-2">
                            <label class="form-label fw-semibold mb-0">Show in Organisation Types</label>
                            <div class="btn-group btn-group-sm">
                                <button type="button" class="btn btn-outline-primary btn-sm py-0 px-2" id="btnSelectAllOrgTypes">
                                    <i class="fas fa-check-double me-1"></i>Select All
                                </button>
                                <button type="button" class="btn btn-outline-secondary btn-sm py-0 px-2" id="btnDeselectAllOrgTypes">
                                    <i class="fas fa-times me-1"></i>Clear All
                                </button>
                            </div>
                        </div>
                        <div class="card p-3 bg-light border">
                            <div class="row g-2">
                                @foreach($organisationTypes as $orgType)
                                    <div class="col-md-4 col-sm-6">
                                        <div class="form-check">
                                            <input class="form-check-input org-type-check" type="checkbox" name="organisation_type_ids[]" value="{{ $orgType->id }}" id="org_type_{{ $orgType->id }}">
                                            <label class="form-check-label cursor-pointer" for="org_type_{{ $orgType->id }}">
                                                {{ $orgType->title }}
                                            </label>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                        <small class="text-muted">This facility will only be available for selected organisation types when adding or editing campuses.</small>
                    </div>

                    <div class="mb-3 form-check form-switch">
                        <input class="form-check-input" type="checkbox" name="status" id="status" value="1" checked>
                        <label class="form-check-label fw-semibold" for="status">Active</label>
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
        $('.org-type-check').prop('checked', true);
    }

    function editFacility(element) {
        const facility = typeof element === 'object' && element.dataset ? JSON.parse(element.dataset.facility) : element;
        $('#facilityForm').attr('action', `/masters/facilities/${facility.id}`);
        $('#formMethod').val('PUT');
        $('#modalTitle').text('Edit Facility');
        $('#name').val(facility.name);
        $('#icon').val(facility.icon);
        $('#status').prop('checked', !!facility.status);

        const assignedIds = (facility.organisation_types || []).map(function(item) {
            return String(item.id);
        });

        $('.org-type-check').each(function() {
            const val = String($(this).val());
            $(this).prop('checked', assignedIds.includes(val));
        });

        $('#facilityModal').modal('show');
    }

    $(document).on('click', '#btnSelectAllOrgTypes', function() {
        $('.org-type-check').prop('checked', true);
    });

    $(document).on('click', '#btnDeselectAllOrgTypes', function() {
        $('.org-type-check').prop('checked', false);
    });
</script>
@endpush

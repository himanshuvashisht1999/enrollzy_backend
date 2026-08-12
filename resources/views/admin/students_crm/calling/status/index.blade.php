@extends('admin.layouts.master')

@section('title', 'Calling Status')

@push('css')
<link rel="stylesheet" href="https://cdn.datatables.net/1.13.7/css/dataTables.bootstrap5.min.css">
@endpush

@section('content')
<div class="container-fluid">
    <div class="card shadow-sm border-0 rounded-4">
        <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center">
            <h6 class="m-0 fw-bold text-primary">Student Calling Statuses</h6>
            <button class="btn btn-primary btn-sm rounded-pill px-3" data-bs-toggle="modal" data-bs-target="#createModal">
                <i class="fas fa-plus me-1"></i> Add Status
            </button>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover align-middle" id="statusTable" width="100%">
                    <thead class="bg-light">
                        <tr>
                            <th>#</th>
                            <th>ID</th>
                            <th>Status Name</th>
                            <th>Date Required</th>
                            <th>More Details?</th>
                            <th class="text-center">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<!-- Modal -->
<div class="modal fade" id="createModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 rounded-4 shadow-lg">
            <div class="modal-header border-0 pb-0">
                <h5 class="fw-bold" id="modalTitle">New Calling Status</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form id="statusForm">
                @csrf
                <input type="hidden" id="status_id" name="status_id">
                <div class="modal-body pb-0">
                    <div class="mb-3">
                        <label class="form-label small fw-bold">Status Label <span class="text-danger">*</span></label>
                        <input type="text" name="name" id="name" class="form-control rounded-3" placeholder="e.g. Interested, Callback" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label small fw-bold">Calling Action (Optional)</label>
                        <select name="calling_action_id" id="calling_action_id" class="form-select rounded-3">
                            <option value="">-- Select Action --</option>
                            @foreach($actions as $action)
                                <option value="{{ $action->id }}">{{ $action->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label small fw-bold">Requires Date? <span class="text-danger">*</span></label>
                        <select name="date_require" id="date_require" class="form-select rounded-3" required>
                            <option value="no">No</option>
                            <option value="yes">Yes</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label small fw-bold">More Details? <span class="text-danger">*</span></label>
                        <select name="is_more_details" id="is_more_details" class="form-select rounded-3" required>
                            <option value="no">No</option>
                            <option value="yes">Yes</option>
                        </select>
                    </div>
                </div>
                <div class="modal-footer border-0">
                    <button type="button" class="btn btn-light rounded-pill px-4" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary rounded-pill px-4">Save Changes</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@push('js')
<script src="https://cdn.datatables.net/1.13.7/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.13.7/js/dataTables.bootstrap5.min.js"></script>
<script>
    $(document).ready(function() {
        let table = $('#statusTable').DataTable({
            processing: true,
            serverSide: true,
            ajax: "{{ route('admin.students-crm.calling-statuses.index') }}",
            columns: [
                { data: 'DT_RowIndex', name: 'DT_RowIndex', orderable: false, searchable: false },
                { data: 'id', name: 'id' },
                { data: 'name', name: 'name' },
                { data: 'date_require', name: 'date_require' },
                { data: 'is_more_details', name: 'is_more_details' },
                { data: 'action', name: 'action', orderable: false, searchable: false }
            ]
        });

        $('#statusForm').on('submit', function(e) {
            e.preventDefault();
            let id = $('#status_id').val();
            let url = id ? "{{ url('admin/students-crm/calling-statuses') }}/" + id : "{{ route('admin.students-crm.calling-statuses.store') }}";
            let type = id ? "PUT" : "POST";

            $.ajax({
                url: url,
                type: type,
                data: $(this).serialize(),
                success: function(res) {
                    if(res.status == 1) {
                        $('#createModal').modal('hide');
                        table.ajax.reload();
                        Swal.fire('Success', res.message, 'success');
                    } else {
                        Swal.fire('Error', res.message, 'error');
                    }
                }
            });
        });

        $(document).on('click', '.edit-status', function() {
            let id = $(this).data('id');
            $.get("{{ url('admin/students-crm/calling-statuses') }}/" + id, function(res) {
                if(res.status == 1) {
                    $('#status_id').val(res.data.id);
                    $('#name').val(res.data.name);
                    $('#calling_action_id').val(res.data.calling_action_id).trigger('change');
                    let dr = res.data.date_require ? res.data.date_require.toString().toLowerCase().trim() : 'no';
                    $('#date_require').val(dr).trigger('change');
                    let imd = res.data.is_more_details ? res.data.is_more_details.toString().toLowerCase().trim() : 'no';
                    $('#is_more_details').val(imd).trigger('change');
                    $('#modalTitle').text('Edit Calling Status');
                    $('#createModal').modal('show');
                }
            });
        });

        $('#createModal').on('hidden.bs.modal', function() {
            $('#statusForm')[0].reset();
            $('#status_id').val('');
            $('#calling_action_id').val('').trigger('change');
            $('#date_require').val('no').trigger('change');
            $('#is_more_details').val('no').trigger('change');
            $('#modalTitle').text('New Calling Status');
        });
    });
</script>
@endpush


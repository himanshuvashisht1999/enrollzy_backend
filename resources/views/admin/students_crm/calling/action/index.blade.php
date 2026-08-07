@extends('admin.layouts.master')

@section('title', 'Calling Action')

@push('css')
<link rel="stylesheet" href="https://cdn.datatables.net/1.13.7/css/dataTables.bootstrap5.min.css">
@endpush

@section('content')
<div class="container-fluid">
    <div class="card shadow-sm border-0 rounded-4">
        <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center">
            <h6 class="m-0 fw-bold text-primary">Calling Next Steps (Actions)</h6>
            <button class="btn btn-primary btn-sm rounded-pill px-3" data-bs-toggle="modal" data-bs-target="#createModal">
                <i class="fas fa-plus me-1"></i> Add Action
            </button>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover align-middle" id="actionTable" width="100%">
                    <thead class="bg-light">
                        <tr>
                            <th>#</th>
                            <th>ID</th>
                            <th>Action Name</th>
                            <th>Current State</th>
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
                <h5 class="fw-bold" id="modalTitle">New Calling Action</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form id="actionForm">
                @csrf
                <input type="hidden" id="action_id" name="action_id">
                <div class="modal-body pb-0">
                    <div class="mb-3">
                        <label class="form-label small fw-bold">Action Label <span class="text-danger">*</span></label>
                        <input type="text" name="name" id="name" class="form-control rounded-3" placeholder="e.g. Schedule Meeting, Email Course Info" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label small fw-bold">Active Status <span class="text-danger">*</span></label>
                        <select name="status" id="is_active" class="form-select rounded-3" required>
                            <option value="1">Active</option>
                            <option value="0">Inactive</option>
                        </select>
                    </div>
                </div>
                <div class="modal-footer border-0">
                    <button type="button" class="btn btn-light rounded-pill px-4" data-bs-modal="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary rounded-pill px-4">Save Action</button>
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
        let table = $('#actionTable').DataTable({
            processing: true,
            serverSide: true,
            ajax: "{{ route('admin.students-crm.calling-actions.index') }}",
            columns: [
                { data: 'DT_RowIndex', name: 'DT_RowIndex', orderable: false, searchable: false },
                { data: 'id', name: 'id' },
                { data: 'name', name: 'name' },
                { data: 'status', name: 'status' },
                { data: 'action', name: 'action', orderable: false, searchable: false }
            ]
        });

        $('#actionForm').on('submit', function(e) {
            e.preventDefault();
            let id = $('#action_id').val();
            let url = id ? "{{ url('admin/students-crm/calling-actions') }}/" + id : "{{ route('admin.students-crm.calling-actions.store') }}";
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

        $(document).on('click', '.edit-action', function() {
            let id = $(this).data('id');
            $.get("{{ url('admin/students-crm/calling-actions') }}/" + id, function(res) {
                if(res.status == 1) {
                    $('#action_id').val(res.data.id);
                    $('#name').val(res.data.name);
                    $('#is_active').val(res.data.status);
                    $('#modalTitle').text('Edit Calling Action');
                    $('#createModal').modal('show');
                }
            });
        });

        $('#createModal').on('hidden.bs.modal', function() {
            $('#actionForm')[0].reset();
            $('#action_id').val('');
            $('#modalTitle').text('New Calling Action');
        });
    });
</script>
@endpush


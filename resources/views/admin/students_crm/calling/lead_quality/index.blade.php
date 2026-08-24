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
                            <th>Lead Quality Name</th>
                            <th>Status</th>
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
                        <label class="form-label small fw-bold">Active Status <span class="text-danger">*</span></label>
                        <select name="status" id="status" class="form-select rounded-3" required>
                            <option value="1">Active</option>
                            <option value="0">Inactive</option>
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
            ajax: "{{ route('admin.students-crm.lead-qualities.index') }}",
            columns: [
                { data: 'DT_RowIndex', name: 'DT_RowIndex', orderable: false, searchable: false },
                { data: 'id', name: 'id' },
                { data: 'name', name: 'name' },
                { data: 'status', name: 'status' },
                { data: 'action', name: 'action', orderable: false, searchable: false }
            ]
        });

        $('#statusForm').on('submit', function(e) {
            e.preventDefault();
            let id = $('#status_id').val();
            let url = id ? "{{ url('admin/students-crm/lead-qualities') }}/" + id : "{{ route('admin.students-crm.lead-qualities.store') }}";
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

        $(document).on('click', '.edit-btn', function() {
            let id = $(this).data('id');
            let name = $(this).data('name');
            let status = $(this).data('status');
            
            $('#status_id').val(id);
            $('#name').val(name);
            $('#status').val(status).trigger('change');
            
            $('#modalTitle').text('Edit Lead Quality');
            $('#createModal').modal('show');
        });

        $(document).on('change', '.status-toggle', function() {
            let id = $(this).data('id');
            let status = $(this).is(':checked') ? 1 : 0;
            
            $.post("{{ route('admin.students-crm.lead-qualities.toggle-status') }}", {
                _token: "{{ csrf_token() }}",
                id: id,
                status: status
            }, function(res) {
                if(res.status == 1) {
                    toastr.success(res.message);
                } else {
                    toastr.error(res.message);
                    table.ajax.reload(null, false);
                }
            });
        });

        $(document).on('click', '.delete-btn', function() {
            let id = $(this).data('id');
            
            Swal.fire({
                title: 'Are you sure?',
                text: "You won't be able to revert this!",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#d33',
                cancelButtonColor: '#3085d6',
                confirmButtonText: 'Yes, delete it!'
            }).then((result) => {
                if (result.isConfirmed) {
                    $.ajax({
                        url: "{{ url('admin/students-crm/lead-qualities') }}/" + id,
                        type: 'DELETE',
                        data: {
                            _token: "{{ csrf_token() }}"
                        },
                        success: function(res) {
                            if(res.status == 1) {
                                Swal.fire('Deleted!', res.message, 'success');
                                table.ajax.reload();
                            } else {
                                Swal.fire('Error!', res.message, 'error');
                            }
                        }
                    });
                }
            });
        });

        $('#createModal').on('hidden.bs.modal', function() {
            $('#statusForm')[0].reset();
            $('#status_id').val('');
            $('#status').val('1').trigger('change');
            $('#modalTitle').text('New Lead Quality');
        });
    });
</script>
@endpush


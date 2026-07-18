@extends('admin.layouts.master')

@section('title', 'Customer Categories')

@push('css')
<link rel="stylesheet" href="https://cdn.datatables.net/1.13.7/css/dataTables.bootstrap5.min.css">
@endpush

@section('content')
<div class="container-fluid">
    <div class="card shadow-sm border-0 rounded-4">
        <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center">
            <h6 class="m-0 fw-bold text-primary">Managed Customer Categories</h6>
            <button class="btn btn-primary btn-sm rounded-pill px-3" data-bs-toggle="modal" data-bs-target="#createModal">
                <i class="fas fa-plus me-1"></i> Add Category
            </button>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover align-middle" id="categoryTable" width="100%">
                    <thead class="bg-light">
                        <tr>
                            <th>#</th>
                            <th>Category Name</th>
                            <th>Parent</th>
                            <th>Type</th>
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
                <h5 class="fw-bold" id="modalTitle">New Category</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form id="categoryForm">
                @csrf
                <input type="hidden" id="category_id" name="category_id">
                <div class="modal-body pb-0">
                    <div class="mb-3">
                        <label class="form-label small fw-bold">Name <span class="text-danger">*</span></label>
                        <input type="text" name="name" id="name" class="form-control rounded-3" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label small fw-bold">Parent Category</label>
                        <select name="parent_id" id="parent_id" class="form-select rounded-3">
                            <option value="0">No Parent</option>
                            @foreach($parentCategories as $parent)
                                <option value="{{ $parent->id }}">{{ $parent->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label small fw-bold">Type <span class="text-danger">*</span></label>
                        <select name="customer_type" id="customer_type" class="form-select rounded-3" required>
                            <option value="Standard">Standard</option>
                            <option value="Credit">Credit</option>
                            <option value="Manual">Manual</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label small fw-bold">Status <span class="text-danger">*</span></label>
                        <select name="status" id="status" class="form-select rounded-3" required>
                            <option value="active">Active</option>
                            <option value="inactive">Inactive</option>
                        </select>
                    </div>
                </div>
                <div class="modal-footer border-0">
                    <button type="button" class="btn btn-light rounded-pill px-4" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary rounded-pill px-4" id="saveBtn">Save Category</button>
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
        let table = $('#categoryTable').DataTable({
            processing: true,
            serverSide: true,
            ajax: "{{ route('admin.customer-categories.index') }}",
            columns: [
                { data: 'DT_RowIndex', name: 'DT_RowIndex', orderable: false, searchable: false },
                { data: 'name', name: 'name' },
                { data: 'parent_name', name: 'parent_name' },
                { data: 'customer_type', name: 'customer_type' },
                { data: 'status', name: 'status' },
                { data: 'action', name: 'action', orderable: false, searchable: false }
            ]
        });

        $('#categoryForm').on('submit', function(e) {
            e.preventDefault();
            let id = $('#category_id').val();
            let url = id ? "{{ url('admin.customer-categories') }}/" + id : "{{ route('admin.customer-categories.store') }}";
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

        $(document).on('click', '.edit-category', function() {
            let id = $(this).data('id');
            $.get("{{ url('admin.customer-categories') }}/" + id, function(res) {
                if(res.status == 1) {
                    $('#category_id').val(res.data.id);
                    $('#name').val(res.data.name);
                    $('#parent_id').val(res.data.parent_id);
                    $('#customer_type').val(res.data.customer_type);
                    $('#status').val(res.data.status);
                    $('#modalTitle').text('Edit Category');
                    $('#createModal').modal('show');
                }
            });
        });

        $('#createModal').on('hidden.bs.modal', function() {
            $('#categoryForm')[0].reset();
            $('#category_id').val('');
            $('#modalTitle').text('New Category');
        });
    });
</script>
@endpush


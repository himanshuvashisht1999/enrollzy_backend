@extends('admin.layouts.master')

@section('title', 'Students List')

@push('css')
<link rel="stylesheet" href="https://cdn.datatables.net/1.13.7/css/dataTables.bootstrap5.min.css">
@endpush

@section('content')
<div class="container-fluid">
    <div class="card shadow-sm border-0 rounded-4">
        <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center">
            <h6 class="m-0 fw-bold text-primary">All Students</h6>
            <div>
                <button class="btn btn-success btn-sm rounded-pill px-3 me-2" data-bs-toggle="modal" data-bs-target="#importModal">
                    <i class="fas fa-file-excel me-1"></i> Import Data
                </button>
                <a class="btn btn-primary btn-sm rounded-pill px-3" href="{{ route('admin.customers.main.index.create') }}">
                    <i class="fas fa-plus me-1"></i> New Customer
                </a>
            </div>
        </div>
        <div class="card-body border-bottom bg-light pb-3 pt-3">
            <form id="filterForm" class="row g-3 align-items-end">
                <div class="col-md-4">
                    <label class="form-label small fw-bold">Name</label>
                    <input type="text" id="filter_name" class="form-control rounded-3" placeholder="Filter by name...">
                </div>
                <div class="col-md-4">
                    <label class="form-label small fw-bold">Phone</label>
                    <input type="text" id="filter_phone" class="form-control rounded-3" placeholder="Filter by phone...">
                </div>
                <div class="col-md-4">
                    <button type="submit" class="btn btn-primary rounded-pill px-4"><i class="fas fa-search me-1"></i> Search</button>
                    <button type="button" id="clearBtn" class="btn btn-light rounded-pill px-4 ms-2">Clear</button>
                </div>
            </form>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover align-middle" id="customerTable" width="100%">
                    <thead class="bg-light">
                        <tr>
                            <th>#</th>
                            <th>Name</th>
                            <th>Contact Info</th>
                            <th>Category</th>
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

<!-- Import Modal -->
<div class="modal fade" id="importModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 rounded-4 shadow-lg">
            <div class="modal-header border-0 pb-0">
                <h5 class="fw-bold">Import Students</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form id="importForm" action="{{ route('admin.customers.main.import') }}" method="POST" enctype="multipart/form-data">
                @csrf
                <div class="modal-body pb-0">
                    <div class="mb-3 d-flex justify-content-between">
                        <label class="form-label small fw-bold">Upload Excel File <span class="text-danger">*</span></label>
                        <a href="{{ route('admin.customers.main.sample-download') }}" class="text-success small fw-bold"><i class="fas fa-download"></i> Download Sample</a>
                    </div>
                    <div class="mb-3">
                        <input type="file" name="file" id="file" class="form-control rounded-3" accept=".xlsx,.xls,.csv" required>
                    </div>
                    <div class="alert alert-info small rounded-3 mt-3">
                        <i class="fas fa-info-circle me-1"></i> The Excel file must contain these columns: <b>name, phone, email, category_id</b>.<br>
                        If a phone number already exists in the database, the record will be updated.
                    </div>
                </div>
                <div class="modal-footer border-0">
                    <button type="button" class="btn btn-light rounded-pill px-4" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-success rounded-pill px-4" id="importBtn">Import</button>
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
        $('#customerTable').DataTable({
            processing: true,
            serverSide: true,
            ajax: {
                url: "{{ route('admin.customers.main.index.index') }}",
                data: function(d) {
                    d.filter_name = $('#filter_name').val();
                    d.filter_phone = $('#filter_phone').val();
                }
            },
            columns: [
                { data: 'DT_RowIndex', name: 'DT_RowIndex', orderable: false, searchable: false },
                { data: 'name', name: 'name' },
                { 
                    data: null, 
                    render: function(data) {
                        return `<div class="small fw-bold">${data.email || 'No email'}</div><div class="small text-muted">${data.phone || 'No phone'}</div>`;
                    }
                },
                { data: 'category_name', name: 'category_name' },
                { data: 'status', name: 'status' },
                { data: 'action', name: 'action', orderable: false, searchable: false }
            ],
            language: { search: "_INPUT_", searchPlaceholder: "Search customers..." }
        });

        $('#filterForm').on('submit', function(e) {
            e.preventDefault();
            $('#customerTable').DataTable().ajax.reload();
        });

        $('#clearBtn').on('click', function() {
            $('#filterForm')[0].reset();
            $('#customerTable').DataTable().ajax.reload();
        });

        $('#importForm').on('submit', function(e) {
            e.preventDefault();
            let formData = new FormData(this);
            $('#importBtn').prop('disabled', true).text('Importing...');

            $.ajax({
                url: $(this).attr('action'),
                type: "POST",
                data: formData,
                processData: false,
                contentType: false,
                success: function(res) {
                    $('#importBtn').prop('disabled', false).text('Import');
                    if(res.status == 1) {
                        $('#importModal').modal('hide');
                        $('#importForm')[0].reset();
                        $('#customerTable').DataTable().ajax.reload();
                        Swal.fire('Success', res.message, 'success');
                    } else {
                        Swal.fire('Error', res.message, 'error');
                    }
                },
                error: function(err) {
                    $('#importBtn').prop('disabled', false).text('Import');
                    Swal.fire('Error', 'Something went wrong', 'error');
                }
            });
        });
    });
</script>
@endpush


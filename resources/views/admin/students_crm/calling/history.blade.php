@extends('admin.layouts.master')

@section('title', 'Calling History')

@push('css')
<link rel="stylesheet" href="https://cdn.datatables.net/1.13.7/css/dataTables.bootstrap5.min.css">
@endpush

@section('content')
<div class="container-fluid">
    <div class="card shadow-sm border-0 rounded-4">
        <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center">
            <h6 class="m-0 fw-bold text-primary">Student Outreach History & Timeline</h6>
            <button class="btn btn-success btn-sm rounded-pill px-3" data-bs-toggle="modal" data-bs-target="#importModal">
                <i class="fas fa-file-excel me-1"></i> Import Data
            </button>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover align-middle" id="historyTable" width="100%">
                    <thead class="bg-light">
                        <tr>
                            <th>#</th>
                            <th>Student</th>
                            <th>Status/Action</th>
                            <th>Staff Member</th>
                            <th>Call Date</th>
                            <th>Next Call</th>
                            <th>Remarks</th>
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
                <h5 class="fw-bold">Import Calling History</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form id="importForm" action="{{ route('admin.students-crm.calling-history.import') }}" method="POST" enctype="multipart/form-data">
                @csrf
                <div class="modal-body pb-0">
                    <div class="mb-3 d-flex justify-content-between">
                        <label class="form-label small fw-bold">Upload Excel File <span class="text-danger">*</span></label>
                        <a href="{{ route('admin.students-crm.calling-history.sample') }}" class="text-success small fw-bold"><i class="fas fa-download"></i> Download Sample</a>
                    </div>
                    <div class="mb-3">
                        <input type="file" name="file" id="file" class="form-control rounded-3" accept=".xlsx,.xls,.csv" required>
                    </div>
                    <div class="alert alert-info small rounded-3 mt-3">
                        <i class="fas fa-info-circle me-1"></i> Ensure columns: <b>phone_number, student_name, category_id, call_status_id, action_taken_id, reminder_date, comment</b>.<br>
                        Students will be automatically created or updated based on the phone number.
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
        $('#historyTable').DataTable({
            processing: true,
            serverSide: true,
            ajax: "{{ route('admin.students-crm.calling-history.index') }}",
            columns: [
                { data: 'DT_RowIndex', name: 'DT_RowIndex', orderable: false, searchable: false },
                { data: 'customer_info', name: 'customer_info' },
                { 
                    data: null, 
                    render: function(data) {
                        return `<span class="badge bg-soft-info">${data.status_info}</span><br><small>${data.action_info}</small>`;
                    }
                },
                { data: 'staff_info', name: 'staff_info' },
                { data: 'call_date', name: 'call_date' },
                { data: 'date_required', name: 'date_required' },
                { data: 'comment', name: 'comment' }
            ],
            language: { search: "_INPUT_", searchPlaceholder: "Search history..." }
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
                        $('#historyTable').DataTable().ajax.reload();
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


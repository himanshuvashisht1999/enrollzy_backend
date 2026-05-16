@extends('admin.layouts.master')

@section('title', 'Calling Module')

@push('css')
<link rel="stylesheet" href="https://cdn.datatables.net/1.13.7/css/dataTables.bootstrap5.min.css">
@endpush

@section('content')
<div class="container-fluid">
    <div class="card shadow-sm border-0 rounded-4">
        <div class="card-header bg-white py-3">
            <h6 class="m-0 fw-bold text-primary">Active Outreach: Students to Call</h6>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover align-middle" id="callingTable" width="100%">
                    <thead class="bg-light">
                        <tr>
                            <th>#</th>
                            <th>Student / Contact</th>
                            <th>Category</th>
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

<!-- Log Call Modal -->
<div class="modal fade" id="callModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 rounded-4 shadow-lg">
            <div class="modal-header border-0 pb-0">
                <h5 class="fw-bold">Log Student Interaction</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form id="callForm">
                @csrf
                <input type="hidden" id="customer_id" name="customer_id">
                <div class="modal-body">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label small fw-bold">Current Status <span class="text-danger">*</span></label>
                            <select name="status_id" class="form-select rounded-3" required>
                                <option value="">Select Status</option>
                                @foreach($statuses as $status)
                                    <option value="{{ $status->id }}">{{ $status->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label small fw-bold">Action Taken <span class="text-danger">*</span></label>
                            <select name="action_id" class="form-select rounded-3" required>
                                <option value="">Select Action</option>
                                @foreach($actions as $action)
                                    <option value="{{ $action->id }}">{{ $action->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label small fw-bold">Call Date <span class="text-danger">*</span></label>
                            <input type="date" name="call_date" class="form-control rounded-3" value="{{ date('Y-m-d') }}" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label small fw-bold">Follow-up Date</label>
                            <input type="date" name="next_call_date" class="form-control rounded-3">
                        </div>
                        <div class="col-12">
                            <label class="form-label small fw-bold">Discussion / Remark</label>
                            <textarea name="remark" class="form-control rounded-3" rows="3" placeholder="Briefly summarize the conversation..."></textarea>
                        </div>
                    </div>
                </div>
                <div class="modal-footer border-0">
                    <button type="button" class="btn btn-light rounded-pill px-4" data-bs-dismiss="modal">Close</button>
                    <button type="submit" class="btn btn-primary rounded-pill px-5">Save Log</button>
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
        let table = $('#callingTable').DataTable({
            processing: true,
            serverSide: true,
            ajax: "{{ route('admin.students-crm.calling-module.index') }}",
            columns: [
                { data: 'DT_RowIndex', name: 'DT_RowIndex', orderable: false, searchable: false },
                { data: 'contact', name: 'contact' },
                { data: 'category_name', name: 'category_name' },
                { data: 'action', name: 'action', orderable: false, searchable: false }
            ]
        });

        $(document).on('click', '.open-calling-modal', function() {
            let id = $(this).data('id');
            $('#customer_id').val(id);
            $('#callModal').modal('show');
        });

        $('#callForm').on('submit', function(e) {
            e.preventDefault();
            $.ajax({
                url: "{{ route('admin.students-crm.calling-module.store') }}",
                type: "POST",
                data: $(this).serialize(),
                success: function(res) {
                    if(res.status == 1) {
                        $('#callModal').modal('hide');
                        $('#callForm')[0].reset();
                        Swal.fire('Interactions Logged', res.message, 'success');
                    } else {
                        Swal.fire('Error', res.message, 'error');
                    }
                }
            });
        });
    });
</script>
@endpush


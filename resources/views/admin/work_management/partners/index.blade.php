@extends('admin.layouts.master')

@section('title', 'External Partners & Organizations')

@push('css')
<link rel="stylesheet" href="https://cdn.datatables.net/1.13.7/css/dataTables.bootstrap5.min.css">
@endpush

@section('content')
<div class="container-fluid">
    <div class="card shadow-sm border-0 rounded-4">
        <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center flex-wrap gap-2">
            <div>
                <h5 class="m-0 fw-bold text-dark"><i class="fas fa-handshake text-primary me-2"></i> External Partners & Organizations</h5>
                <small class="text-muted">Manage 3rd-party agencies, contractors, vendor teams & external collaborator assignments</small>
            </div>
            <a href="{{ route('admin.work_management.partners.create') }}" class="btn btn-primary btn-sm rounded-pill px-4 shadow-sm">
                <i class="fas fa-plus me-1"></i> New Partner Organization
            </a>
        </div>
        <div class="card-body">
            <!-- Filter Bar -->
            <div class="row g-3 mb-4 bg-light p-3 rounded-4">
                <div class="col-md-4">
                    <label class="form-label small fw-semibold">Organization Type</label>
                    <select id="filter_type" class="form-select form-select-sm rounded-3">
                        <option value="">All Partner Types</option>
                        <option value="agency">Agency</option>
                        <option value="contractor">Contractor</option>
                        <option value="consultant">Consultant</option>
                        <option value="vendor">Vendor</option>
                        <option value="client">Client</option>
                        <option value="partner">Partner</option>
                    </select>
                </div>
                <div class="col-md-4">
                    <label class="form-label small fw-semibold">Status</label>
                    <select id="filter_status" class="form-select form-select-sm rounded-3">
                        <option value="">All Statuses</option>
                        <option value="active">Active</option>
                        <option value="inactive">Inactive</option>
                    </select>
                </div>
            </div>

            <div class="table-responsive">
                <table class="table table-hover align-middle" id="partnersTable" width="100%">
                    <thead class="bg-light small">
                        <tr>
                            <th>Sr. No.</th>
                            <th>Organization</th>
                            <th>Contact Info</th>
                            <th>Contacts</th>
                            <th>External Teams</th>
                            <th>Status</th>
                            <th class="text-end">Action</th>
                        </tr>
                    </thead>
                    <tbody></tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection

@push('js')
<script src="https://cdn.datatables.net/1.13.7/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.13.7/js/dataTables.bootstrap5.min.js"></script>
<script>
    $(document).ready(function() {
        var table = $('#partnersTable').DataTable({
            processing: true,
            serverSide: true,
            ajax: {
                url: "{{ route('admin.work_management.partners.index') }}",
                data: function(d) {
                    d.organization_type = $('#filter_type').val();
                    d.status = $('#filter_status').val();
                }
            },
            columns: [
                { data: 'DT_RowIndex', name: 'DT_RowIndex', orderable: false, searchable: false },
                { data: 'name_type', name: 'name' },
                { data: 'contact_info', name: 'email', orderable: false, searchable: false },
                { data: 'contacts_count', name: 'contacts_count', orderable: false, searchable: false },
                { data: 'teams_count', name: 'teams_count', orderable: false, searchable: false },
                { data: 'status', name: 'status' },
                { data: 'action', name: 'action', orderable: false, searchable: false, className: 'text-end' }
            ],
            order: [[1, 'asc']]
        });

        $('#filter_type, #filter_status').on('change', function() {
            table.draw();
        });
    });
</script>
@endpush

@extends('admin.layouts.master')

@section('title', 'Project Types')

@push('css')
<link rel="stylesheet" href="https://cdn.datatables.net/1.13.7/css/dataTables.bootstrap5.min.css">
<style>
    .bg-soft-primary { background-color: rgba(13, 110, 253, 0.12) !important; }
    .bg-soft-success { background-color: rgba(25, 135, 84, 0.12) !important; }
    .bg-soft-info { background-color: rgba(13, 202, 240, 0.15) !important; }
    .bg-soft-warning { background-color: rgba(255, 193, 7, 0.15) !important; }
    .bg-soft-danger { background-color: rgba(220, 53, 69, 0.12) !important; }
    .bg-soft-dark { background-color: rgba(33, 37, 41, 0.12) !important; }
    .bg-soft-secondary { background-color: rgba(108, 117, 125, 0.12) !important; }
    .btn-soft-primary { background-color: rgba(13, 110, 253, 0.1); color: #0d6efd; border: none; }
    .btn-soft-primary:hover { background-color: #0d6efd; color: #fff; }
    .btn-soft-danger { background-color: rgba(220, 53, 69, 0.1); color: #dc3545; border: none; }
    .btn-soft-danger:hover { background-color: #dc3545; color: #fff; }
</style>
@endpush

@section('content')
<div class="container-fluid">
    <div class="card shadow-sm border-0 rounded-4">
        <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center flex-wrap gap-2">
            <div>
                <h5 class="m-0 fw-bold text-dark"><i class="fas fa-layer-group text-primary me-2"></i> Project Types</h5>
                <small class="text-muted">Configure delivery methodologies, operational nature & governance types for projects</small>
            </div>
            <div class="d-flex gap-2">
                <a href="{{ route('admin.work_management.projects.index') }}" class="btn btn-outline-secondary btn-sm rounded-pill px-3">
                    <i class="fas fa-arrow-left me-1"></i> Back to Projects
                </a>
                <button type="button" class="btn btn-primary btn-sm rounded-pill px-4 shadow-sm" data-bs-toggle="modal" data-bs-target="#createTypeModal">
                    <i class="fas fa-plus me-1"></i> New Project Type
                </button>
            </div>
        </div>
        <div class="card-body">
            @if(session('success'))
                <div class="alert alert-success alert-dismissible fade show rounded-3" role="alert">
                    <i class="fas fa-check-circle me-1"></i> {{ session('success') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            @endif
            @if(session('error'))
                <div class="alert alert-danger alert-dismissible fade show rounded-3" role="alert">
                    <i class="fas fa-exclamation-triangle me-1"></i> {{ session('error') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            @endif

            <div class="table-responsive">
                <table class="table table-hover align-middle" id="typesTable" width="100%">
                    <thead class="bg-light small">
                        <tr>
                            <th style="width: 70px;">#</th>
                            <th>Project Type</th>
                            <th>Color Theme</th>
                            <th>Description</th>
                            <th>Projects Count</th>
                            <th>Status</th>
                            <th class="text-end" style="width: 120px;">Action</th>
                        </tr>
                    </thead>
                    <tbody></tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<!-- Create Type Modal -->
<div class="modal fade" id="createTypeModal" tabindex="-1" aria-labelledby="createTypeModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content rounded-4 border-0 shadow">
            <div class="modal-header border-0 pb-0">
                <h5 class="modal-title fw-bold" id="createTypeModalLabel"><i class="fas fa-plus-circle text-primary me-2"></i> Create Project Type</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form action="{{ route('admin.work_management.types.store') }}" method="POST">
                @csrf
                <div class="modal-body py-3">
                    <div class="row g-3">
                        <div class="col-md-8">
                            <label class="form-label small fw-bold">Type Name <span class="text-danger">*</span></label>
                            <input type="text" name="name" class="form-control rounded-3" placeholder="e.g. Research & Dev, High Priority Retainer" required>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label small fw-bold">Code / Abbr</label>
                            <input type="text" name="code" class="form-control rounded-3" placeholder="e.g. RND, RET">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label small fw-bold">Color Theme</label>
                            <select name="color" class="form-select rounded-3">
                                <option value="primary" selected>Primary (Blue)</option>
                                <option value="success">Success (Green)</option>
                                <option value="info">Info (Cyan)</option>
                                <option value="warning">Warning (Amber)</option>
                                <option value="danger">Danger (Red)</option>
                                <option value="dark">Dark (Charcoal)</option>
                                <option value="secondary">Secondary (Gray)</option>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label small fw-bold">Status</label>
                            <select name="status" class="form-select rounded-3">
                                <option value="active" selected>Active</option>
                                <option value="inactive">Inactive</option>
                            </select>
                        </div>
                        <div class="col-12">
                            <label class="form-label small fw-bold">Description</label>
                            <textarea name="description" class="form-control rounded-3" rows="3" placeholder="Purpose, delivery scope and typical operational nature..."></textarea>
                        </div>
                    </div>
                </div>
                <div class="modal-footer border-0 pt-0">
                    <button type="button" class="btn btn-light rounded-pill px-4" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary rounded-pill px-4 shadow-sm">Save Project Type</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Edit Type Modal -->
<div class="modal fade" id="editTypeModal" tabindex="-1" aria-labelledby="editTypeModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content rounded-4 border-0 shadow">
            <div class="modal-header border-0 pb-0">
                <h5 class="modal-title fw-bold" id="editTypeModalLabel"><i class="fas fa-edit text-primary me-2"></i> Edit Project Type</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="editTypeForm" method="POST">
                @csrf
                @method('PUT')
                <div class="modal-body py-3">
                    <div class="row g-3">
                        <div class="col-md-8">
                            <label class="form-label small fw-bold">Type Name <span class="text-danger">*</span></label>
                            <input type="text" name="name" id="edit_type_name" class="form-control rounded-3" required>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label small fw-bold">Code / Abbr</label>
                            <input type="text" name="code" id="edit_type_code" class="form-control rounded-3">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label small fw-bold">Color Theme</label>
                            <select name="color" id="edit_type_color" class="form-select rounded-3">
                                <option value="primary">Primary (Blue)</option>
                                <option value="success">Success (Green)</option>
                                <option value="info">Info (Cyan)</option>
                                <option value="warning">Warning (Amber)</option>
                                <option value="danger">Danger (Red)</option>
                                <option value="dark">Dark (Charcoal)</option>
                                <option value="secondary">Secondary (Gray)</option>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label small fw-bold">Status</label>
                            <select name="status" id="edit_type_status" class="form-select rounded-3">
                                <option value="active">Active</option>
                                <option value="inactive">Inactive</option>
                            </select>
                        </div>
                        <div class="col-12">
                            <label class="form-label small fw-bold">Description</label>
                            <textarea name="description" id="edit_type_description" class="form-control rounded-3" rows="3"></textarea>
                        </div>
                    </div>
                </div>
                <div class="modal-footer border-0 pt-0">
                    <button type="button" class="btn btn-light rounded-pill px-4" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary rounded-pill px-4 shadow-sm">Update Project Type</button>
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
    var table = $('#typesTable').DataTable({
        processing: true,
        serverSide: true,
        ajax: {
            url: "{{ route('admin.work_management.types.index') }}",
        },
        columns: [
            { data: 'DT_RowIndex', name: 'DT_RowIndex', orderable: false, searchable: false },
            { data: 'name', name: 'name' },
            { data: 'color', name: 'color', searchable: false },
            { data: 'description', name: 'description' },
            { data: 'projects_count', name: 'projects_count', searchable: false },
            { data: 'status', name: 'status', searchable: false },
            { data: 'action', name: 'action', orderable: false, searchable: false, className: 'text-end' }
        ],
        order: [[1, 'asc']],
        language: {
            search: "_INPUT_",
            searchPlaceholder: "Search project types...",
            paginate: {
                previous: "<i class='fas fa-chevron-left'></i>",
                next: "<i class='fas fa-chevron-right'></i>"
            }
        }
    });

    $(document).on('click', '.edit-type-btn', function() {
        var id = $(this).data('id');
        var name = $(this).data('name');
        var code = $(this).data('code');
        var color = $(this).data('color');
        var status = $(this).data('status');
        var description = $(this).data('description');

        var url = "{{ route('admin.work_management.types.update', ':id') }}";
        url = url.replace(':id', id);

        $('#editTypeForm').attr('action', url);
        $('#edit_type_name').val(name);
        $('#edit_type_code').val(code);
        $('#edit_type_color').val(color);
        $('#edit_type_status').val(status);
        $('#edit_type_description').val(description);

        $('#editTypeModal').modal('show');
    });
});
</script>
@endpush

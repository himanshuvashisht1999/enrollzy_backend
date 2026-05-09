@extends('admin.layouts.master')

@section('title', 'WhatsApp Templates')

@push('css')
<link rel="stylesheet" href="https://cdn.datatables.net/1.13.7/css/dataTables.bootstrap5.min.css">
@endpush

@section('content')
<div class="container-fluid">
    <div class="card shadow-sm border-0 rounded-4 mb-4">
        <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center">
            <h6 class="m-0 fw-bold text-primary">WhatsApp Marketing Templates</h6>
            <a class="btn btn-primary btn-sm rounded-pill px-3" href="{{ route('admin.hr.whatsapp_template.create') }}">
                <i class="fas fa-plus me-1"></i> New Template
            </a>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover align-middle" id="templateTable" width="100%">
                    <thead class="bg-light">
                        <tr>
                            <th>#</th>
                            <th>Template Name</th>
                            <th>Status</th>
                            <th>Added Date</th>
                            <th class="text-center">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($data as $item)
                        <tr>
                            <td>{{ $loop->iteration }}</td>
                            <td class="fw-bold">{{ $item->name }}</td>
                            <td>{!! GetStatusBadge($item->status) !!}</td>
                            <td>{{ date('d M, Y', strtotime($item->created_at)) }}</td>
                            <td class="text-center">
                                <div class="d-flex justify-content-center gap-2">
                                    <a href="{{ route('admin.hr.whatsapp_template.sendMessage', encrypt($item->id)) }}" 
                                       class="btn btn-soft-success btn-sm rounded-circle" title="Broadcast Message">
                                        <i class="fas fa-paper-plane"></i>
                                    </a>
                                    <a href="{{ route('admin.hr.whatsapp_template.edit', encrypt($item->id)) }}" 
                                       class="btn btn-soft-primary btn-sm rounded-circle" title="Edit">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                    <form method="POST" action="{{ route('admin.hr.whatsapp_template.destroy', encrypt($item->id)) }}" class="d-inline delete-form">
                                        @csrf
                                        @method('DELETE')
                                        <button type="button" class="btn btn-soft-danger btn-sm rounded-circle delete-btn" title="Delete">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
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
        $('#templateTable').DataTable({
            language: { search: "_INPUT_", searchPlaceholder: "Search templates..." }
        });

        $('.delete-btn').click(function() {
            let form = $(this).closest('form');
            Swal.fire({
                title: 'Delete Template?',
                text: "This action cannot be undone!",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#3085d6',
                cancelButtonColor: '#d33',
                confirmButtonText: 'Yes, delete it!'
            }).then((result) => {
                if (result.isConfirmed) form.submit();
            });
        });
    });
</script>
@endpush

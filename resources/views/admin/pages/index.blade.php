@extends('admin.layouts.master')

@section('title', 'Manage Dynamic Pages')

@section('content')
<div class="row">
    <div class="col-md-12">
        <div class="card shadow-sm border-0 rounded-4">
            <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center">
                <div>
                    <h5 class="mb-0 fw-bold text-primary"><i class="fas fa-file-alt me-2"></i> Dynamic Pages</h5>
                    <small class="text-muted">Manage your static content pages like Privacy Policy, Terms & Conditions.</small>
                </div>
                <a href="{{ route('admin.pages.create') }}" class="btn btn-primary rounded-pill px-4 shadow-sm">
                    <i class="fas fa-plus me-2"></i> Create Page
                </a>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table align-middle table-hover" id="dataTable">
                        <thead class="bg-light">
                            <tr>
                                <th>#</th>
                                <th>Title</th>
                                <th>URL Slug</th>
                                <th class="text-center">Status</th>
                                <th class="text-center" width="150">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($pages as $page)
                                <tr>
                                    <td class="text-muted">{{ $loop->iteration }}</td>
                                    <td class="fw-bold">{{ $page->title }}</td>
                                    <td><a href="{{ env('FRONTEND_URL', 'http://127.0.0.1:8000') }}/page/{{ $page->slug }}" target="_blank" class="text-decoration-none text-primary"><i class="fas fa-external-link-alt me-1 small"></i> /page/{{ $page->slug }}</a></td>
                                    <td class="text-center">
                                        @if($page->status)
                                            <span class="badge bg-success-subtle text-success border border-success-subtle rounded-pill px-3">Active</span>
                                        @else
                                            <span class="badge bg-danger-subtle text-danger border border-danger-subtle rounded-pill px-3">Inactive</span>
                                        @endif
                                    </td>
                                    <td class="text-center">
                                        <div class="btn-group shadow-sm">
                                            <a href="{{ env('FRONTEND_URL', 'http://127.0.0.1:8000') }}/page/{{ $page->slug }}" target="_blank" class="btn btn-sm btn-info text-white" title="View Page">
                                                <i class="fas fa-eye"></i>
                                            </a>
                                            <a href="{{ route('admin.pages.edit', $page->id) }}" class="btn btn-light btn-sm text-primary" data-bs-toggle="tooltip" title="Edit">
                                                <i class="fas fa-edit"></i>
                                            </a>
                                            <form action="{{ route('admin.pages.destroy', $page->id) }}" method="POST" class="d-inline" onsubmit="return confirm('Are you sure you want to delete this page?');">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="btn btn-light btn-sm text-danger" data-bs-toggle="tooltip" title="Delete">
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
</div>
@endsection

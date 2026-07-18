@extends('admin.layouts.master')

@section('title', 'Mentor Languages')

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h4 class="mb-0">Mentor Languages</h4>
        <a href="{{ route('admin.mentor.languages.create') }}" class="btn btn-primary">
            <i class="fas fa-plus me-1"></i> Add Language
        </a>
    </div>

    <div class="card shadow-sm">
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-bordered table-hover">
                    <thead class="table-light">
                        <tr>
                            <th width="50">ID</th>
                            <th>Name</th>
                            <th width="100">Status</th>
                            <th width="150">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($languages as $language)
                            <tr>
                                <td>{{ $language->id }}</td>
                                <td>{{ $language->name }}</td>
                                <td>
                                    @if($language->status)
                                        <span class="badge bg-success">Active</span>
                                    @else
                                        <span class="badge bg-danger">Inactive</span>
                                    @endif
                                </td>
                                <td>
                                    <div class="d-flex gap-2">
                                        <a href="{{ route('admin.mentor.languages.edit', $language->id) }}" class="btn btn-sm btn-info text-white">
                                            <i class="fas fa-edit"></i>
                                        </a>
                                        <form action="{{ route('admin.mentor.languages.destroy', $language->id) }}" method="POST" onsubmit="return confirm('Are you sure you want to delete this language?');">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-sm btn-danger">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="4" class="text-center py-4">No mentor languages found.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection

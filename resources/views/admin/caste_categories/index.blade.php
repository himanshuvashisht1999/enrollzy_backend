@extends('admin.layouts.master')

@section('title', 'Manage Caste Categories')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <h4 class="mb-0">Caste Categories</h4>
    <a href="{{ route('admin.caste-categories.create') }}" class="btn btn-primary">
        <i class="fas fa-plus"></i> Add New Caste Category
    </a>
</div>

<div class="card border-0 shadow-sm">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover mb-0">
                <thead class="bg-light">
                    <tr>
                        <th class="ps-4">ID</th>
                        <th>Name</th>
                        <th>Slug</th>
                        <th>Status</th>
                        <th>Created At</th>
                        <th class="text-end pe-4">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($casteCategories as $category)
                    <tr>
                        <td class="ps-4 text-muted">{{ $category->id }}</td>
                        <td><span class="fw-bold">{{ $category->name }}</span></td>
                        <td><code>{{ $category->slug }}</code></td>
                        <td>
                            <span class="badge {{ $category->status ? 'bg-success' : 'bg-danger' }}">
                                {{ $category->status ? 'Active' : 'Inactive' }}
                            </span>
                        </td>
                        <td>{{ $category->created_at->format('d M, Y') }}</td>
                        <td class="text-end pe-4">
                            <a href="{{ route('admin.caste-categories.edit', $category->id) }}" class="btn btn-sm btn-outline-info me-2">
                                <i class="fas fa-edit"></i>
                            </a>
                            <form action="{{ route('admin.caste-categories.destroy', $category->id) }}" method="POST" class="d-inline">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-sm btn-outline-danger" onclick="return confirm('Are you sure you want to delete this category?')">
                                    <i class="fas fa-trash"></i>
                                </button>
                            </form>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6" class="text-center py-5 text-muted">No caste categories found.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
    <div class="card-footer bg-white pt-3">
        {{ $casteCategories->links() }}
    </div>
</div>
@endsection

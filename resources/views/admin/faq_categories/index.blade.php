@extends('admin.layouts.master')

@section('title', 'FAQ Categories')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <h4 class="mb-0">FAQ Categories</h4>
    <a href="{{ route('admin.faq-categories.create') }}" class="btn btn-primary">
        <i class="fas fa-plus"></i> Add Category
    </a>
</div>

<div class="card border-0 shadow-sm">
    <div class="card-body">
        <form method="GET" action="{{ route('admin.faq-categories.index') }}" class="mb-4">
            <div class="input-group" style="max-width: 400px;">
                <input type="text" name="search" class="form-control" placeholder="Search categories..." value="{{ request('search') }}">
                <button class="btn btn-outline-secondary" type="submit"><i class="fas fa-search"></i></button>
            </div>
        </form>

        <div class="table-responsive">
            <table class="table table-hover align-middle">
                <thead class="table-light">
                    <tr>
                        <th>ID</th>
                        <th>Name</th>
                        <th>Parent Category</th>
                        <th>Status</th>
                        <th class="text-end">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($categories as $category)
                        <tr>
                            <td>{{ $category->id }}</td>
                            <td>{{ $category->name }}</td>
                            <td>
                                @if($category->parent)
                                    <span class="badge bg-secondary">{{ $category->parent->name }}</span>
                                @else
                                    <span class="text-muted">None</span>
                                @endif
                            </td>
                            <td>
                                @if($category->status)
                                    <span class="badge bg-success">Active</span>
                                @else
                                    <span class="badge bg-danger">Inactive</span>
                                @endif
                            </td>
                            <td class="text-end">
                                <a href="{{ route('admin.faq-categories.edit', $category->id) }}" class="btn btn-sm btn-outline-primary">
                                    <i class="fas fa-edit"></i> Edit
                                </a>
                                <form action="{{ route('admin.faq-categories.destroy', $category->id) }}" method="POST" class="d-inline-block" onsubmit="return confirm('Are you sure you want to delete this category?');">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-sm btn-outline-danger">
                                        <i class="fas fa-trash"></i> Delete
                                    </button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="text-center py-4 text-muted">No categories found.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        
        <div class="d-flex justify-content-end mt-3">
            {{ $categories->links() }}
        </div>
    </div>
</div>
@endsection

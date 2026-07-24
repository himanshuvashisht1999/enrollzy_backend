@extends('admin.layouts.master')

@section('title', 'Community Categories')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <h3 class="fw-bold">Community Categories</h3>
    <a href="{{ route('admin.community-categories.create') }}" class="btn btn-primary">
        <i class="fas fa-plus me-1"></i> Add Category
    </a>
</div>

<div class="card border-0 shadow-sm">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="bg-light">
                    <tr>
                        <th class="ps-4">Image</th>
                        <th>Name</th>
                        <th>Slug</th>
                        <th>Description</th>
                        <th class="text-end pe-4">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($categories as $category)
                    <tr>
                        <td class="ps-4">
                            @if(!empty($category->image))
                                @php
                                    $catThumb = str_starts_with($category->image, 'http') ? $category->image : asset('storage/' . $category->image);
                                @endphp
                                <img src="{{ $catThumb }}" class="rounded-circle border" style="width: 40px; height: 40px; object-fit: cover;" alt="Avatar">
                            @else
                                <div class="rounded-circle bg-light d-inline-flex align-items-center justify-content-center text-muted fw-bold" style="width: 40px; height: 40px; font-size: 14px;">
                                    {{ strtoupper(substr($category->name, 0, 1)) }}
                                </div>
                            @endif
                        </td>
                        <td>
                            <span class="fw-bold">{{ $category->name }}</span>
                        </td>
                        <td><code>{{ $category->slug }}</code></td>
                        <td>{{ Str::limit($category->description, 50) }}</td>
                        <td class="text-end pe-4">
                            <a href="{{ route('admin.community-categories.edit', $category->id) }}" class="btn btn-sm btn-outline-primary me-1">
                                <i class="fas fa-edit"></i>
                            </a>
                            <form action="{{ route('admin.community-categories.destroy', $category->id) }}" method="POST" class="d-inline">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-sm btn-outline-danger" onclick="return confirm('Are you sure?')">
                                    <i class="fas fa-trash"></i>
                                </button>
                            </form>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="4" class="text-center py-5 text-muted">No categories found.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
    @if($categories->hasPages())
    <div class="card-footer bg-white">
        {{ $categories->links() }}
    </div>
    @endif
</div>
@endsection

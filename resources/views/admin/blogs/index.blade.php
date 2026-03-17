@extends('admin.layouts.master')

@section('title', 'Manage Blogs')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <h4 class="mb-0 text-dark fw-bold">Blog Management</h4>
    <a href="{{ route('blogs.create') }}" class="btn btn-primary px-4 shadow-sm">
        <i class="fas fa-plus-circle me-1"></i> New Blog Post
    </a>
</div>

<!-- Filters -->
<div class="card border-0 shadow-sm mb-4">
    <div class="card-body">
        <form action="{{ route('blogs.index') }}" method="GET" class="row g-3">
            <div class="col-md-4">
                <input type="text" name="search" class="form-control" placeholder="Search by title..." value="{{ request('search') }}">
            </div>
            <div class="col-md-3">
                <select name="category_id" class="form-select">
                    <option value="">All Categories</option>
                    @foreach($categories as $cat)
                        <option value="{{ $cat->id }}" {{ request('category_id') == $cat->id ? 'selected' : '' }}>
                            {{ $cat->name }}
                        </option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-2">
                <button type="submit" class="btn btn-secondary w-100">
                    <i class="fas fa-filter me-1"></i> Filter
                </button>
            </div>
            @if(request()->anyFilled(['search', 'category_id']))
                <div class="col-md-2">
                    <a href="{{ route('blogs.index') }}" class="btn btn-outline-danger w-100">Clear</a>
                </div>
            @endif
        </form>
    </div>
</div>

<div class="card border-0 shadow-sm">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="bg-light">
                    <tr>
                        <th class="ps-4">Image</th>
                        <th>Title & Category</th>
                        <th>Author</th>
                        <th>Date</th>
                        <th class="text-end pe-4">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($blogs as $blog)
                    <tr>
                        <td class="ps-4">
                            @if($blog->image)
                                <img src="{{ asset($blog->image) }}" alt="" class="rounded" style="width: 50px; height: 50px; object-fit: cover;">
                            @else
                                <div class="rounded bg-light d-flex align-items-center justify-content-center text-muted" style="width: 50px; height: 50px;">
                                    <i class="fas fa-image"></i>
                                </div>
                            @endif
                        </td>
                        <td>
                            <div class="fw-bold text-dark">{{ $blog->title }}</div>
                            <span class="badge bg-soft-primary text-primary border border-primary-subtle rounded-pill small">
                                {{ $blog->category->name ?? 'Uncategorized' }}
                            </span>
                        </td>
                        <td><div class="text-muted small">{{ $blog->author }}</div></td>
                        <td><div class="text-muted small">{{ $blog->created_at->format('d M, Y') }}</div></td>
                        <td class="text-end pe-4">
                            <a href="{{ route('blogs.edit', $blog->id) }}" class="btn btn-sm btn-light border-0 me-2 text-info shadow-none" title="Edit">
                                <i class="fas fa-edit"></i>
                            </a>
                            <form action="{{ route('blogs.destroy', $blog->id) }}" method="POST" class="d-inline">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-sm btn-light border-0 text-danger shadow-none" onclick="return confirm('Delete this blog post?')" title="Delete">
                                    <i class="fas fa-trash"></i>
                                </button>
                            </form>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="5" class="text-center py-5 text-muted">No blog posts found matching your criteria.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
    <div class="card-footer bg-white pt-3 border-0">
        {{ $blogs->appends(request()->query())->links() }}
    </div>
</div>

<style>
    .bg-soft-primary { background-color: rgba(13, 110, 253, 0.1); }
</style>
@endsection

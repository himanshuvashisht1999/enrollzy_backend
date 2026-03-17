@extends('admin.layouts.master')

@section('title', 'Manage FAQs')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <h4 class="mb-0 text-dark fw-bold">FAQ Management</h4>
    <a href="{{ route('faqs.create') }}" class="btn btn-primary px-4 shadow-sm">
        <i class="fas fa-plus-circle me-1"></i> Add Question
    </a>
</div>

<!-- Filters -->
<div class="card border-0 shadow-sm mb-4">
    <div class="card-body">
        <form action="{{ route('faqs.index') }}" method="GET" class="row g-3">
            <div class="col-md-5">
                <input type="text" name="search" class="form-control" placeholder="Search in questions or answers..." value="{{ request('search') }}">
            </div>
            <div class="col-md-3">
                <select name="category" class="form-select">
                    <option value="">All Categories</option>
                    @foreach($categories as $cat)
                        <option value="{{ $cat }}" {{ request('category') == $cat ? 'selected' : '' }}>{{ $cat }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-2">
                <button type="submit" class="btn btn-secondary w-100">Filter</button>
            </div>
            @if(request()->anyFilled(['search', 'category']))
                <div class="col-md-2">
                    <a href="{{ route('faqs.index') }}" class="btn btn-outline-danger w-100">Clear</a>
                </div>
            @endif
        </form>
    </div>
</div>

<div class="card border-0 shadow-sm">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="bg-light text-muted small text-uppercase">
                    <tr>
                        <th class="ps-4" style="width: 50%;">Question</th>
                        <th>Category</th>
                        <th>Order</th>
                        <th class="text-end pe-4">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($faqs as $faq)
                    <tr>
                        <td class="ps-4">
                            <div class="fw-bold text-dark">{{ $faq->question }}</div>
                            <div class="text-muted small text-truncate" style="max-width: 400px;">{{ $faq->answer }}</div>
                        </td>
                        <td>
                            <span class="badge bg-light text-dark border">{{ $faq->category }}</span>
                        </td>
                        <td><span class="text-muted">{{ $faq->sort_order }}</span></td>
                        <td class="text-end pe-4">
                            <a href="{{ route('faqs.edit', $faq->id) }}" class="btn btn-sm btn-outline-info me-2 shadow-none">
                                <i class="fas fa-edit"></i>
                            </a>
                            <form action="{{ route('faqs.destroy', $faq->id) }}" method="POST" class="d-inline">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-sm btn-outline-danger shadow-none" onclick="return confirm('Remove this FAQ?')">
                                    <i class="fas fa-trash"></i>
                                </button>
                            </form>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="4" class="text-center py-5 text-muted">No FAQs found.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
    <div class="card-footer bg-white pt-3 border-0">
        {{ $faqs->appends(request()->query())->links() }}
    </div>
</div>
@endsection

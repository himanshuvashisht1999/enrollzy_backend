@extends('admin.layouts.master')

@section('title', 'Manage Testimonials')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <h4 class="mb-0 text-dark fw-bold">Testimonial Management</h4>
    <a href="{{ route('testimonials.create') }}" class="btn btn-primary px-4 shadow-sm">
        <i class="fas fa-plus-circle me-1"></i> Add Testimonial
    </a>
</div>

<!-- Filters -->
<div class="card border-0 shadow-sm mb-4">
    <div class="card-body">
        <form action="{{ route('testimonials.index') }}" method="GET" class="row g-3">
            <div class="col-md-5">
                <input type="text" name="search" class="form-control" placeholder="Search by name or role..." value="{{ request('search') }}">
            </div>
            <div class="col-md-3">
                <select name="rating" class="form-select">
                    <option value="">All Ratings</option>
                    @for($i=5; $i>=1; $i--)
                        <option value="{{ $i }}" {{ request('rating') == $i ? 'selected' : '' }}>{{ $i }} Stars</option>
                    @endfor
                </select>
            </div>
            <div class="col-md-2">
                <button type="submit" class="btn btn-secondary w-100">Filter</button>
            </div>
            @if(request()->anyFilled(['search', 'rating']))
                <div class="col-md-2">
                    <a href="{{ route('testimonials.index') }}" class="btn btn-outline-danger w-100">Clear</a>
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
                        <th class="ps-4">User</th>
                        <th>Testimonial</th>
                        <th>Rating</th>
                        <th class="text-end pe-4">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($testimonials as $testimonial)
                    <tr>
                        <td class="ps-4">
                            <div class="d-flex align-items-center">
                                @if($testimonial->image)
                                    <img src="{{ (str_starts_with($testimonial->image, 'http')) ? $testimonial->image : asset($testimonial->image) }}" alt="" class="rounded-circle me-3" style="width: 45px; height: 45px; object-fit: cover;">
                                @else
                                    <div class="rounded-circle bg-light d-flex align-items-center justify-content-center text-muted me-3" style="width: 45px; height: 45px;">
                                        <i class="fas fa-user"></i>
                                    </div>
                                @endif
                                <div>
                                    <div class="fw-bold text-dark">{{ $testimonial->name }}</div>
                                    <small class="text-muted">{{ $testimonial->role }}</small>
                                </div>
                            </div>
                        </td>
                        <td>
                            <div class="text-muted small text-truncate" style="max-width: 400px;" title="{{ $testimonial->content }}">
                                "{{ $testimonial->content }}"
                            </div>
                        </td>
                        <td>
                            <div class="text-warning small">
                                @for($i=0; $i<5; $i++)
                                    <i class="fas fa-star {{ $i < $testimonial->rating ? '' : 'text-muted opacity-25' }}"></i>
                                @endfor
                            </div>
                        </td>
                        <td class="text-end pe-4">
                            <a href="{{ route('testimonials.edit', $testimonial->id) }}" class="btn btn-sm btn-outline-info me-2 shadow-none">
                                <i class="fas fa-edit"></i>
                            </a>
                            <form action="{{ route('testimonials.destroy', $testimonial->id) }}" method="POST" class="d-inline">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-sm btn-outline-danger shadow-none" onclick="return confirm('Remove this testimonial?')">
                                    <i class="fas fa-trash"></i>
                                </button>
                            </form>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="4" class="text-center py-5 text-muted">No testimonials found.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
    <div class="card-footer bg-white pt-3 border-0">
        {{ $testimonials->appends(request()->query())->links() }}
    </div>
</div>
@endsection

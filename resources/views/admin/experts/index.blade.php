@extends('admin.layouts.master')

@section('title', 'Manage Experts')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <h4 class="mb-0 text-dark fw-bold">Expert Counselors</h4>
    <a href="{{ route('experts.create') }}" class="btn btn-primary px-4 shadow-sm">
        <i class="fas fa-plus-circle me-1"></i> Add Counselor
    </a>
</div>

<!-- Filters -->
<div class="card border-0 shadow-sm mb-4">
    <div class="card-body">
        <form action="{{ route('experts.index') }}" method="GET" class="row g-3">
            <div class="col-md-4">
                <input type="text" name="search" class="form-control" placeholder="Search by name or degree..." value="{{ request('search') }}">
            </div>
            <div class="col-md-3">
                <select name="role" class="form-select">
                    <option value="">All Roles</option>
                    @foreach($roles as $role)
                        <option value="{{ $role }}" {{ request('role') == $role ? 'selected' : '' }}>
                            {{ $role }}
                        </option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-2">
                <button type="submit" class="btn btn-secondary w-100">
                    <i class="fas fa-filter me-1"></i> Filter
                </button>
            </div>
            @if(request()->anyFilled(['search', 'role']))
                <div class="col-md-2">
                    <a href="{{ route('experts.index') }}" class="btn btn-outline-danger w-100">Clear</a>
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
                        <th class="ps-4">Counselor</th>
                        <th>Role & Degree</th>
                        <th>Experience</th>
                        <th>Rating</th>
                        <th class="text-end pe-4">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($experts as $expert)
                    <tr>
                        <td class="ps-4">
                            <div class="d-flex align-items-center">
                                @if($expert->img)
                                    <img src="{{ (str_starts_with($expert->img, 'http')) ? $expert->img : asset($expert->img) }}" alt="" class="rounded-circle me-3" style="width: 45px; height: 45px; object-fit: cover;">
                                @else
                                    <div class="rounded-circle bg-light d-flex align-items-center justify-content-center text-muted me-3" style="width: 45px; height: 45px;">
                                        <i class="fas fa-user"></i>
                                    </div>
                                @endif
                                <div>
                                    <div class="fw-bold text-dark">{{ $expert->name }}</div>
                                    <small class="text-muted">ID: #{{ $expert->id }} | {{ $expert->email }}</small>
                                </div>
                            </div>
                        </td>
                        </td>
                        <td>
                            <div class="text-dark fw-bold">{{ $expert->designation ?? $expert->role }}</div>
                            <div class="text-muted small">{{ $expert->highest_qualification ?? $expert->degree }}</div>
                             @if($expert->verification_status == 'Verified')
                                <span class="badge bg-success-subtle text-success border border-success-subtle rounded-pill" style="font-size: 0.65rem;">Verified</span>
                            @elseif($expert->verification_status == 'Pending')
                                <span class="badge bg-warning-subtle text-warning border border-warning-subtle rounded-pill" style="font-size: 0.65rem;">Pending</span>
                            @endif
                        </td>
                        <td>
                            <div class="text-dark">{{ $expert->years_of_experience_total ?? $expert->exp }} Years</div>
                            <span class="badge {{ $expert->status == 'Active' ? 'bg-success' : 'bg-secondary' }} rounded-pill" style="font-size: 0.7rem;">{{ $expert->status }}</span>
                        </td>
                        <td>
                            <div class="text-warning small mb-1">
                                @for($i=0; $i<5; $i++)
                                    <i class="fas fa-star {{ $i < ($expert->rating ?? 0) ? '' : 'text-muted opacity-25' }}"></i>
                                @endfor
                                <span class="ms-1 text-muted text-nowrap">({{ $expert->total_reviews ?? 0 }})</span>
                            </div>
                        </td>
                        <td class="text-end pe-4">
                            <div class="dropdown">
                                <button class="btn btn-light btn-sm dropdown-toggle border-0" type="button" data-bs-toggle="dropdown">
                                    <i class="fas fa-ellipsis-v"></i>
                                </button>
                                <ul class="dropdown-menu dropdown-menu-end shadow-sm border-0">
                                    <li><a class="dropdown-item" href="{{ route('experts.edit', $expert->id) }}"><i class="fas fa-edit me-2 text-info"></i> Edit</a></li>
                                    <li><hr class="dropdown-divider"></li>
                                    <li>
                                        <form action="{{ route('experts.destroy', $expert->id) }}" method="POST" class="d-inline">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="dropdown-item text-danger" onclick="return confirm('Remove this expert?')">
                                                <i class="fas fa-trash me-2"></i> Delete
                                            </button>
                                        </form>
                                    </li>
                                </ul>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="5" class="text-center py-5 text-muted">No counselors found.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
    <div class="card-footer bg-white pt-3 border-0">
        {{ $experts->appends(request()->query())->links() }}
    </div>
</div>
@endsection

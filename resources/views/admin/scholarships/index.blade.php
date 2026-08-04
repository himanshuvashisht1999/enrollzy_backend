@extends('admin.layouts.master')

@section('title', 'Manage Scholarships')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <h4 class="mb-0">Scholarships</h4>
    <a href="{{ route('admin.scholarships.create') }}" class="btn btn-primary">
        <i class="fas fa-plus"></i> Add New Scholarship
    </a>
</div>

<div class="card border-0 shadow-sm">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover mb-0">
                <thead class="bg-light">
                    <tr>
                        <th class="ps-4">ID</th>
                        <th>Title</th>
                        <th>Type</th>
                        <th>Category</th>
                        <th>Amount</th>
                        <th>Deadline</th>
                        <th>Status</th>
                        <th class="text-end pe-4">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($scholarships as $scholarship)
                    <tr>
                        <td class="ps-4 text-muted">{{ $scholarship->id }}</td>
                        <td>
                            <div class="d-flex align-items-center">
                                @if($scholarship->provider_logo)
                                    <img src="{{ asset($scholarship->provider_logo) }}" alt="Logo" class="rounded me-2" style="width: 32px; height: 32px; object-fit: contain;">
                                @endif
                                <div>
                                    <span class="fw-bold d-block">{{ $scholarship->title }}</span>
                                    <small class="text-muted">{{ $scholarship->scholarship_code }}</small>
                                </div>
                            </div>
                        </td>
                        <td><span class="badge bg-secondary">{{ $scholarship->scholarship_type ?: 'N/A' }}</span></td>
                        <td>{{ $scholarship->category ?: 'N/A' }}</td>
                        <td>
                            @if($scholarship->max_amount)
                                <span class="fw-bold">{{ $scholarship->amount_prefix }} ₹{{ number_format($scholarship->max_amount, 0) }} {{ $scholarship->amount_suffix }}</span>
                            @else
                                <span class="text-muted">N/A</span>
                            @endif
                        </td>
                        <td>
                            @if($scholarship->dates && $scholarship->dates->application_end_date)
                                {{ $scholarship->dates->application_end_date->format('d M, Y') }}
                            @else
                                <span class="text-muted">N/A</span>
                            @endif
                        </td>
                        <td>
                            <span class="badge bg-{{ $scholarship->status ? 'success' : 'danger' }}">
                                {{ $scholarship->status ? 'Active' : 'Draft' }}
                            </span>
                        </td>
                        <td class="text-end pe-4">
                            <a href="{{ route('admin.scholarships.edit', $scholarship->id) }}" class="btn btn-sm btn-outline-info me-2">
                                <i class="fas fa-edit"></i>
                            </a>
                            <form action="{{ route('admin.scholarships.destroy', $scholarship->id) }}" method="POST" class="d-inline">
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
                        <td colspan="8" class="text-center py-5 text-muted">No scholarships found.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection

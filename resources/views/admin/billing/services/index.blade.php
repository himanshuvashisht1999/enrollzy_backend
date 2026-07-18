@extends('admin.layouts.master')

@section('title', 'Manage Billing Services')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <h4 class="mb-0">Billing Services</h4>
    <a href="{{ route('admin.billing.services.create') }}" class="btn btn-primary">
        <i class="fas fa-plus"></i> Add New Service
    </a>
</div>

@if(session('success'))
<div class="alert alert-success">{{ session('success') }}</div>
@endif

<div class="card border-0 shadow-sm">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover mb-0">
                <thead class="bg-light">
                    <tr>
                        <th>Name</th>
                        <th>HSN/SAC</th>
                        <th>Price</th>
                        <th>Status</th>
                        <th class="text-end pe-4">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($services as $service)
                    <tr>
                        <td>{{ $service->name }}</td>
                        <td>{{ $service->hsn_sac_code ?? 'N/A' }}</td>
                        <td>
                            @if($service->sale_price)
                                <span class="text-decoration-line-through text-muted">{{ number_format($service->price, 2) }}</span>
                                <strong>{{ number_format($service->sale_price, 2) }}</strong>
                            @else
                                {{ number_format($service->price, 2) }}
                            @endif
                        </td>
                        <td>
                            @if($service->status)
                                <span class="badge bg-success">Active</span>
                            @else
                                <span class="badge bg-danger">Inactive</span>
                            @endif
                        </td>
                        <td class="text-end pe-4">
                            <a href="{{ route('admin.billing.services.edit', $service->id) }}" class="btn btn-sm btn-outline-info me-2">
                                <i class="fas fa-edit"></i>
                            </a>
                            <form action="{{ route('admin.billing.services.destroy', $service->id) }}" method="POST" class="d-inline">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-sm btn-outline-danger" onclick="return confirm('Are you sure you want to delete this service?')">
                                    <i class="fas fa-trash"></i>
                                </button>
                            </form>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="7" class="text-center py-5 text-muted">No billing services found.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
    <div class="card-footer bg-white pt-3">
        {{ $services->links() }}
    </div>
</div>
@endsection

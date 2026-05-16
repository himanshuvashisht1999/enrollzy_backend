@extends('admin.layouts.master')

@section('title', 'Consultants List')

@section('content')
<div class="container-fluid py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h4 class="mb-1 fw-bold">Consultants</h4>
            <p class="text-muted small mb-0">Manage your business partners and independent consultants.</p>
        </div>
        <a href="{{ route('admin.consultants.create') }}" class="btn btn-primary rounded-pill px-4">
            <i class="fas fa-plus me-2"></i>Register New Consultant
        </a>
    </div>

    <div class="card border-0 shadow-sm">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="bg-light">
                        <tr>
                            <th class="ps-4">Consultant</th>
                            <th>ID</th>
                            <th>Business Name</th>
                            <th>Contact</th>
                            <th>Status</th>
                            <th class="text-end pe-4">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($consultants as $con)
                            <tr>
                                <td class="ps-4">
                                    <div class="d-flex align-items-center">
                                        <div class="avatar avatar-sm me-3">
                                            @if($con->image)
                                                <img src="{{ asset($con->image) }}" class="rounded-circle" style="width: 40px; height: 40px; object-fit: cover;">
                                            @else
                                                <div class="rounded-circle bg-soft-primary text-primary d-flex align-items-center justify-content-center" style="width: 40px; height: 40px;">
                                                    {{ substr($con->full_name, 0, 1) }}
                                                </div>
                                            @endif
                                        </div>
                                        <div>
                                            <h6 class="mb-0 text-sm fw-bold">{{ $con->full_name }}</h6>
                                            <span class="text-muted small">{{ $con->consultant_type }}</span>
                                        </div>
                                    </div>
                                </td>
                                <td><span class="badge bg-light text-dark border">{{ $con->consultant_id }}</span></td>
                                <td>{{ $con->business_name ?? 'N/A' }}</td>
                                <td>
                                    <div class="small">
                                        <i class="fas fa-phone-alt me-1 text-muted"></i> {{ $con->phone }}<br>
                                        <i class="fas fa-envelope me-1 text-muted"></i> {{ $con->email }}
                                    </div>
                                </td>
                                <td>
                                    @php
                                        $statusClass = [
                                            'active' => 'bg-success',
                                            'pending' => 'bg-warning',
                                            'inactive' => 'bg-secondary',
                                            'blocked' => 'bg-danger'
                                        ][$con->status] ?? 'bg-info';
                                    @endphp
                                    <span class="badge {{ $statusClass }} text-white text-capitalize">{{ $con->status }}</span>
                                </td>
                                <td class="text-end pe-4">
                                    <a href="{{ route('admin.consultants.edit', encrypt($con->id)) }}" class="btn btn-sm btn-light rounded-circle me-1" title="Edit">
                                        <i class="fas fa-pen text-primary"></i>
                                    </a>
                                    <button class="btn btn-sm btn-light rounded-circle" title="View Profile">
                                        <i class="fas fa-eye text-info"></i>
                                    </button>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="text-center py-5">
                                    <img src="https://illustrations.popsy.co/gray/teamwork.svg" alt="No Data" style="width: 150px;" class="mb-3"><br>
                                    <p class="text-muted">No consultants registered yet.</p>
                                    <a href="{{ route('admin.consultants.create') }}" class="btn btn-primary btn-sm rounded-pill">Start Onboarding</a>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection


@extends('expert.layouts.app')

@section('title', 'Expert Dashboard')
@section('page-title', 'Overview')

@section('content')
<div class="row g-4 mb-4">
    <div class="col-md-3">
        <div class="card card-custom p-3 border-start border-4 border-primary">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <h6 class="text-muted fw-normal mb-1">Available Slots</h6>
                    <h3 class="fw-bold mb-0 text-primary">{{ $availableSlots }}</h3>
                </div>
                <div class="bg-primary-subtle text-primary rounded-circle p-3">
                    <i class="bi bi-calendar-event fs-4"></i>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card card-custom p-3 border-start border-4 border-warning">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <h6 class="text-muted fw-normal mb-1">Pending Approvals</h6>
                    <h3 class="fw-bold mb-0 text-warning">{{ $pendingBookings }}</h3>
                </div>
                <div class="bg-warning-subtle text-warning rounded-circle p-3">
                    <i class="bi bi-clock-history fs-4"></i>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card card-custom p-3 border-start border-4 border-success">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <h6 class="text-muted fw-normal mb-1">Confirmed Sessions</h6>
                    <h3 class="fw-bold mb-0 text-success">{{ $confirmedBookings }}</h3>
                </div>
                <div class="bg-success-subtle text-success rounded-circle p-3">
                    <i class="bi bi-check-circle fs-4"></i>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card card-custom p-3 border-start border-4 border-info">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <h6 class="text-muted fw-normal mb-1">Total Earnings</h6>
                    <h3 class="fw-bold mb-0 text-info">₹{{ number_format($totalEarnings, 2) }}</h3>
                </div>
                <div class="bg-info-subtle text-info rounded-circle p-3">
                    <i class="bi bi-currency-rupee fs-4"></i>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="card card-custom p-4">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h5 class="fw-bold mb-0">Recent Booking Requests</h5>
        <a href="{{ route('expert.bookings.index') }}" class="btn btn-sm btn-outline-primary">View All</a>
    </div>

    <div class="table-responsive">
        <table class="table align-middle">
            <thead class="table-light">
                <tr>
                    <th>Booking ID</th>
                    <th>Student Name</th>
                    <th>Date & Time</th>
                    <th>Mode</th>
                    <th>Status</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
                @forelse($recentBookings as $booking)
                <tr>
                    <td class="fw-semibold text-primary">{{ $booking->booking_id }}</td>
                    <td>{{ $booking->user->name ?? 'Student' }}</td>
                    <td>
                        {{ \Carbon\Carbon::parse($booking->slot->date ?? $booking->booking_date)->format('M d, Y') }}
                        <br>
                        <small class="text-muted">{{ $booking->slot->start_time ?? '' }} - {{ $booking->slot->end_time ?? '' }}</small>
                    </td>
                    <td><span class="badge bg-secondary text-uppercase">{{ $booking->slot->mode ?? 'video' }}</span></td>
                    <td>
                        @if($booking->status === 'confirmed')
                            <span class="badge bg-success">Confirmed</span>
                        @elseif($booking->status === 'pending')
                            <span class="badge bg-warning text-dark">Pending Approval</span>
                        @elseif($booking->status === 'completed')
                            <span class="badge bg-info">Completed</span>
                        @else
                            <span class="badge bg-danger">{{ ucfirst($booking->status) }}</span>
                        @endif
                    </td>
                    <td>
                        <a href="{{ route('expert.bookings.index') }}" class="btn btn-sm btn-light border">Manage</a>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="6" class="text-center text-muted py-4">No recent session bookings found.</td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection

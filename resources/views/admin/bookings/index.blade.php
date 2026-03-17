@extends('admin.layouts.master')

@section('title', 'Manage Expert Bookings')

@section('content')
<div class="container-fluid px-0">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h4 class="mb-0 text-dark fw-bold">Expert Bookings</h4>
    </div>

    <!-- Filters -->
    <div class="card border-0 shadow-sm mb-4">
        <div class="card-body">
            <form action="{{ route('admin.bookings.index') }}" method="GET" class="row g-3">
                <div class="col-md-5">
                    <input type="text" name="search" class="form-control" placeholder="Search Booking ID, Student or Expert Name..." value="{{ request('search') }}">
                </div>
                <div class="col-md-3">
                    <select name="status" class="form-select">
                        <option value="">All Statuses</option>
                        @foreach($statuses as $status)
                            <option value="{{ $status }}" {{ request('status') == $status ? 'selected' : '' }}>{{ $status }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2">
                    <button type="submit" class="btn btn-secondary w-100">Filter</button>
                </div>
                @if(request()->anyFilled(['search', 'status']))
                    <div class="col-md-2">
                        <a href="{{ route('admin.bookings.index') }}" class="btn btn-outline-danger w-100">Clear</a>
                    </div>
                @endif
            </form>
        </div>
    </div>

    <!-- Bookings Table -->
    <div class="card border-0 shadow-sm">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="bg-light text-uppercase small text-muted">
                        <tr>
                            <th class="ps-4">Booking ID</th>
                            <th>Student</th>
                            <th>Expert</th>
                            <th>Session Details</th>
                            <th>Financials</th>
                            <th>Status</th>
                            {{-- <th class="text-end pe-4">Actions</th> --}}
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($bookings as $booking)
                            <tr>
                                <td class="ps-4 text-primary fw-bold">{{ $booking->booking_id }}</td>
                                <td>
                                    <div class="d-flex align-items-center">
                                        <div class="avatar-circle bg-light text-secondary me-2 rounded-circle d-flex align-items-center justify-content-center" style="width: 32px; height: 32px; font-size: 12px; font-weight: bold;">
                                            {{ strtoupper(substr($booking->user->name ?? 'U', 0, 1)) }}
                                        </div>
                                        <div>
                                            <div class="fw-bold text-dark small">{{ $booking->user->name ?? 'N/A' }}</div>
                                            <div class="text-muted" style="font-size: 11px;">{{ $booking->user->email ?? '' }}</div>
                                        </div>
                                    </div>
                                </td>
                                <td>
                                    <div class="d-flex align-items-center">
                                        <div class="avatar-circle bg-primary-subtle text-primary me-2 rounded-circle d-flex align-items-center justify-content-center" style="width: 32px; height: 32px; font-size: 12px; font-weight: bold;">
                                            {{ strtoupper(substr($booking->expert->name ?? 'E', 0, 1)) }}
                                        </div>
                                        <div>
                                            <div class="fw-bold text-dark small">{{ $booking->expert->name ?? 'Deleted Expert' }}</div>
                                            <div class="text-muted" style="font-size: 11px;">{{ $booking->expert->expertCategory->name ?? '' }}</div>
                                        </div>
                                    </div>
                                </td>
                                <td>
                                    @if($booking->booking_date)
                                      <div class="d-flex flex-column small">
                                          <span class="fw-bold text-dark"><i class="fas fa-calendar-day me-1 text-muted"></i> {{ $booking->booking_date->format('d M, Y') }}</span>
                                          @if($booking->slot)
                                            <span class="text-muted mt-1"><i class="fas fa-clock me-1"></i> {{ \Carbon\Carbon::parse($booking->slot->start_time)->format('h:i A') }} - {{ \Carbon\Carbon::parse($booking->slot->end_time)->format('h:i A') }}</span>
                                            <span class="badge bg-light text-dark border mt-1 w-auto align-self-start" style="font-size: 10px;">{{ ucfirst($booking->slot->mode) }}</span>
                                          @endif
                                      </div>
                                    @else
                                        <span class="text-muted small">Date Not Set</span>
                                    @endif
                                </td>
                                <td>
                                    <div class="bg-light p-2 rounded border" style="min-width: 160px; font-size: 0.85rem;">
                                        <div class="d-flex justify-content-between mb-1">
                                            <span class="text-muted">Total:</span>
                                            <span class="fw-bold">₹{{ number_format($booking->amount, 2) }}</span>
                                        </div>
                                        <div class="d-flex justify-content-between mb-1 text-success">
                                            <span class="" title="Expert Earnings">Expert:</span>
                                            <span class="fw-bold">₹{{ number_format($booking->expert_earning, 2) }}</span>
                                        </div>
                                        <div class="d-flex justify-content-between text-secondary border-top pt-1 mt-1">
                                            <span class="" title="Commission/Platform Fee">Comm.:</span>
                                            <span class="fw-bold">₹{{ number_format($booking->platform_fee, 2) }}</span>
                                        </div>
                                    </div>
                                    <div class="mt-1">
                                         @if($booking->payment_status == 'Paid')
                                            <span class="badge bg-success-subtle text-success border border-success-subtle"><i class="fas fa-check-circle me-1"></i> Paid</span>
                                         @else
                                            <span class="badge bg-warning-subtle text-warning border border-warning-subtle">{{ $booking->payment_status }}</span>
                                         @endif
                                    </div>
                                </td>
                                <td>
                                    <span class="badge rounded-pill {{ 
                                        $booking->status == 'Confirmed' ? 'bg-success' : 
                                        ($booking->status == 'Completed' ? 'bg-info' : 
                                        ($booking->status == 'Cancelled' ? 'bg-danger' : 'bg-warning')) 
                                    }} px-3 py-2">
                                        {{ $booking->status }}
                                    </span>
                                </td>
                                {{-- 
                                <td class="text-end pe-4">
                                    <a href="#" class="btn btn-sm btn-outline-primary"><i class="fas fa-eye"></i> Details</a>
                                </td> 
                                --}}
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="text-center py-5">
                                    <div class="text-muted">
                                        <i class="fas fa-calendar-times fa-3x mb-3 text-secondary opacity-50"></i>
                                        <p class="mb-0">No bookings found.</p>
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
        <div class="card-footer bg-white border-0 pt-3">
             {{ $bookings->appends(request()->query())->links() }}
        </div>
    </div>
</div>
@endsection

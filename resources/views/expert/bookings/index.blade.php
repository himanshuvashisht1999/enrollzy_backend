@extends('expert.layouts.app')

@section('title', 'Booked Sessions')
@section('page-title', 'Student Session Bookings')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h4 class="fw-bold mb-1">Session Bookings</h4>
        <p class="text-muted small mb-0">Approve, reject, or manage student consultation sessions</p>
    </div>
</div>

<div class="card card-custom p-3 mb-4">
    <form action="{{ route('expert.bookings.index') }}" method="GET" class="row g-3 align-items-center">
        <div class="col-md-6">
            <select name="status" class="form-select" onchange="this.form.submit()">
                <option value="">All Statuses</option>
                <option value="pending" {{ request('status') === 'pending' ? 'selected' : '' }}>Pending Approval</option>
                <option value="confirmed" {{ request('status') === 'confirmed' ? 'selected' : '' }}>Confirmed / Approved</option>
                <option value="completed" {{ request('status') === 'completed' ? 'selected' : '' }}>Completed</option>
                <option value="cancelled" {{ request('status') === 'cancelled' ? 'selected' : '' }}>Cancelled / Rejected</option>
            </select>
        </div>
        <div class="col-md-6 text-end">
            <a href="{{ route('expert.bookings.index') }}" class="btn btn-outline-secondary">Reset Filters</a>
        </div>
    </form>
</div>

<div class="card card-custom p-4">
    <div class="table-responsive">
        <table class="table align-middle">
            <thead class="table-light">
                <tr>
                    <th>Booking ID</th>
                    <th>Student Info</th>
                    <th>Slot Date & Time</th>
                    <th>Mode</th>
                    <th>Status</th>
                    <th>Meeting Link</th>
                    <th class="text-end">Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse($bookings as $booking)
                <tr>
                    <td class="fw-bold text-primary">{{ $booking->booking_id }}</td>
                    <td>
                        <div class="fw-semibold">{{ $booking->user->name ?? 'Student' }}</div>
                        <small class="text-muted">{{ $booking->user->email ?? '' }}</small>
                    </td>
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
                            <span class="badge bg-warning text-dark">Pending</span>
                        @elseif($booking->status === 'completed')
                            <span class="badge bg-info">Completed</span>
                        @else
                            <span class="badge bg-danger">{{ ucfirst($booking->status) }}</span>
                        @endif
                    </td>
                    <td>
                        @if($booking->meeting_link)
                            <a href="{{ $booking->meeting_link }}" target="_blank" class="btn btn-sm btn-outline-primary me-1"><i class="bi bi-camera-video me-1"></i> Join Call</a>
                            <button class="btn btn-sm btn-light border me-1" onclick="copyMeetingLink('{{ $booking->meeting_link }}', this)" title="Copy Link">
                                <i class="bi bi-clipboard text-secondary"></i> <span class="copy-text d-none d-xl-inline">Copy</span>
                            </button>
                            <button class="btn btn-sm btn-light border" data-bs-toggle="modal" data-bs-target="#editLinkModal{{ $booking->id }}" title="Edit Link">
                                <i class="bi bi-pencil text-secondary"></i>
                            </button>
                        @else
                            <span class="text-muted small me-1">Not provided</span>
                            <button class="btn btn-sm btn-outline-primary" data-bs-toggle="modal" data-bs-target="#editLinkModal{{ $booking->id }}">
                                <i class="bi bi-plus-lg me-1"></i> Add Link
                            </button>
                        @endif
                    </td>
                    <td class="text-end">
                        @if($booking->status === 'pending')
                            <!-- Approve Button Modal trigger -->
                            <button class="btn btn-sm btn-success me-1" data-bs-toggle="modal" data-bs-target="#approveModal{{ $booking->id }}">
                                <i class="bi bi-check-circle me-1"></i> Approve
                            </button>
                            <!-- Reject Button -->
                            <form action="{{ route('expert.bookings.reject', $booking->id) }}" method="POST" class="d-inline" onsubmit="return confirm('Reject this booking?');">
                                @csrf
                                <button type="submit" class="btn btn-sm btn-danger">
                                    <i class="bi bi-x-circle me-1"></i> Reject
                                </button>
                            </form>
                        @elseif($booking->status === 'confirmed')
                            <form action="{{ route('expert.bookings.complete', $booking->id) }}" method="POST" class="d-inline">
                                @csrf
                                <button type="submit" class="btn btn-sm btn-info text-white">
                                    <i class="bi bi-check-lg me-1"></i> Mark Complete
                                </button>
                            </form>
                        @else
                            <button class="btn btn-sm btn-outline-secondary" data-bs-toggle="modal" data-bs-target="#editLinkModal{{ $booking->id }}">
                                <i class="bi bi-link-45deg me-1"></i> Edit Link
                            </button>
                        @endif
                    </td>
                </tr>

                <!-- Edit Meeting Link Modal -->
                <div class="modal fade" id="editLinkModal{{ $booking->id }}" tabindex="-1">
                    <div class="modal-dialog">
                        <div class="modal-content">
                            <form action="{{ route('expert.bookings.update-link', $booking->id) }}" method="POST">
                                @csrf
                                <div class="modal-header">
                                    <h5 class="modal-title">Meeting Link - Booking #{{ $booking->booking_id }}</h5>
                                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                </div>
                                <div class="modal-body">
                                    <p class="small text-muted mb-3">Add or update the Google Meet, Zoom, or video call link for <strong>{{ $booking->user->name ?? 'Student' }}</strong>.</p>
                                    <div class="mb-3">
                                        <label class="form-label">Meeting / Video Link URL</label>
                                        <input type="url" name="meeting_link" class="form-control" placeholder="https://meet.google.com/xyz-abc" value="{{ $booking->meeting_link }}" required>
                                    </div>
                                </div>
                                <div class="modal-footer">
                                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancel</button>
                                    <button type="submit" class="btn btn-primary"><i class="bi bi-check-lg me-1"></i> Save Link</button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>

                <!-- Approve Modal -->
                <div class="modal fade" id="approveModal{{ $booking->id }}" tabindex="-1">
                    <div class="modal-dialog">
                        <div class="modal-content">
                            <form action="{{ route('expert.bookings.approve', $booking->id) }}" method="POST">
                                @csrf
                                <div class="modal-header">
                                    <h5 class="modal-title">Approve Booking #{{ $booking->booking_id }}</h5>
                                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                </div>
                                <div class="modal-body">
                                    <p class="small text-muted mb-3">Approve this session for <strong>{{ $booking->user->name ?? 'Student' }}</strong>.</p>
                                    <div class="mb-3">
                                        <label class="form-label">Meeting / Video Link (Google Meet, Zoom, etc.)</label>
                                        <input type="url" name="meeting_link" class="form-control" placeholder="https://meet.google.com/xyz-abc" value="{{ $booking->meeting_link }}">
                                    </div>
                                </div>
                                <div class="modal-footer">
                                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancel</button>
                                    <button type="submit" class="btn btn-success"><i class="bi bi-check-lg me-1"></i> Approve & Save Link</button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
                @empty
                <tr>
                    <td colspan="7" class="text-center text-muted py-4">No session bookings found.</td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="mt-3">
        {{ $bookings->withQueryString()->links() }}
    </div>
</div>

<script>
function copyMeetingLink(url, btn) {
    if (!url) return;
    navigator.clipboard.writeText(url).then(() => {
        const originalHtml = btn.innerHTML;
        btn.innerHTML = '<i class="bi bi-check2 text-success"></i> <span class="text-success small fw-bold">Copied!</span>';
        setTimeout(() => {
            btn.innerHTML = originalHtml;
        }, 2000);
    }).catch(err => {
        alert('Failed to copy link: ' + err);
    });
}
</script>
@endsection

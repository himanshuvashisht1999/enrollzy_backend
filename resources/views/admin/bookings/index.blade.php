@extends('admin.layouts.master')

@section('title', 'Manage Expert Bookings')

@section('content')
@php
    $statusBadge = ['pending' => 'bg-warning text-dark', 'confirmed' => 'bg-success', 'completed' => 'bg-info', 'cancelled' => 'bg-danger'];
    $payBadge = ['paid' => 'bg-success-subtle text-success border-success-subtle', 'pending' => 'bg-warning-subtle text-warning border-warning-subtle',
        'refund_due' => 'bg-danger-subtle text-danger border-danger-subtle', 'refunded' => 'bg-secondary-subtle text-secondary border-secondary-subtle'];
@endphp
<div class="container-fluid px-0">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h4 class="mb-0 text-dark fw-bold">Expert Bookings</h4>
        <a href="{{ route('admin.expert-payouts.index') }}" class="btn btn-outline-primary btn-sm"><i class="fas fa-wallet me-1"></i> Expert Payouts</a>
    </div>

    @if($errors->any())<div class="alert alert-danger">{!! implode('<br>', array_map('e', $errors->all())) !!}</div>@endif

    <!-- Filters -->
    <div class="card border-0 shadow-sm mb-4">
        <div class="card-body">
            <form action="{{ route('admin.bookings.index') }}" method="GET" class="row g-3">
                <div class="col-md-4">
                    <input type="text" name="search" class="form-control" placeholder="Search Booking ID, Student or Expert Name..." value="{{ request('search') }}">
                </div>
                <div class="col-md-2">
                    <select name="expert_id" class="form-select">
                        <option value="">All Experts</option>
                        @foreach($experts as $e)<option value="{{ $e->id }}" @selected(request('expert_id') == $e->id)>{{ $e->name }}</option>@endforeach
                    </select>
                </div>
                <div class="col-md-2">
                    <select name="status" class="form-select">
                        <option value="">All Statuses</option>
                        @foreach($statuses as $k => $label)<option value="{{ $k }}" @selected(request('status') === $k)>{{ $label }}</option>@endforeach
                    </select>
                </div>
                <div class="col-md-2">
                    <select name="payment_status" class="form-select">
                        <option value="">All Payments</option>
                        @foreach($paymentStatuses as $k => $label)<option value="{{ $k }}" @selected(request('payment_status') === $k)>{{ $label }}</option>@endforeach
                    </select>
                </div>
                <div class="col-md-1">
                    <button type="submit" class="btn btn-secondary w-100">Filter</button>
                </div>
                @if(request()->anyFilled(['search', 'status', 'payment_status', 'expert_id']))
                    <div class="col-md-1">
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
                            <th>Session</th>
                            <th>Financials</th>
                            <th>Status</th>
                            <th>Meeting Link</th>
                            <th class="text-end pe-4">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($bookings as $booking)
                            @php $start = $booking->sessionStart(); $end = $booking->sessionEnd(); @endphp
                            <tr>
                                <td class="ps-4">
                                    <div class="text-primary fw-bold">{{ $booking->booking_id }}</div>
                                    <div class="text-muted" style="font-size: 11px;">Booked {{ optional($booking->created_at)->format('d M Y, h:i A') }}</div>
                                </td>
                                <td>
                                    <div class="fw-bold text-dark small">{{ $booking->user->name ?? 'N/A' }}</div>
                                    <div class="text-muted" style="font-size: 11px;">{{ $booking->email ?: ($booking->user->email ?? '') }}</div>
                                    @if($booking->phone)<div class="text-muted" style="font-size: 11px;"><i class="fas fa-phone me-1"></i>{{ $booking->phone }}</div>@endif
                                    @if($booking->topic)<span class="badge bg-primary-subtle text-primary mt-1" style="font-size: 10px;">{{ $booking->topic }}</span>@endif
                                </td>
                                <td>
                                    <div class="fw-bold text-dark small">{{ $booking->expert->name ?? 'Deleted Expert' }}</div>
                                    <div class="text-muted" style="font-size: 11px;">{{ $booking->expert->expertCategory->name ?? '' }}</div>
                                </td>
                                <td>
                                    {{-- the session itself (slot date and time), not the day it was booked --}}
                                    @if($start)
                                        <div class="d-flex flex-column small">
                                            <span class="fw-bold text-dark"><i class="fas fa-calendar-day me-1 text-muted"></i> {{ $start->format('d M, Y') }}</span>
                                            <span class="text-muted mt-1"><i class="fas fa-clock me-1"></i> {{ $start->format('h:i A') }} - {{ $end->format('h:i A') }}</span>
                                            <span class="badge bg-light text-dark border mt-1 w-auto align-self-start" style="font-size: 10px;">{{ ucfirst($booking->slot->mode ?? 'video') }}</span>
                                            @if($booking->slot && $booking->slot->trashed())<span class="text-danger mt-1" style="font-size: 11px;">Slot deleted by the expert</span>@endif
                                            @if($booking->reschedule_count)<span class="text-primary mt-1" style="font-size: 11px;" title="Last moved {{ $booking->rescheduled_at?->format('d M Y, h:i A') }}"><i class="fas fa-history me-1"></i>Rescheduled by {{ $booking->rescheduled_by }}</span>@endif
                                        </div>
                                    @else
                                        <span class="text-muted small">No slot</span>
                                    @endif
                                </td>
                                <td>
                                    @php $bd = $booking->commission_breakdown ?? []; @endphp
                                    <div class="bg-light p-2 rounded border" style="min-width: 190px; font-size: 0.82rem;">
                                        <div class="d-flex justify-content-between mb-1"><span class="text-muted">Paid by student:</span><span class="fw-bold">₹{{ number_format($booking->amount, 2) }}</span></div>
                                        <div class="d-flex justify-content-between text-secondary"><span>Platform fee{{ isset($bd['applied_rate']) && ($bd['commission_type'] ?? 'percentage') === 'percentage' ? ' (' . (float) $bd['applied_rate'] . '%)' : '' }}:</span><span>₹{{ number_format($bd['platform_fee_base'] ?? 0, 2) }}</span></div>
                                        <div class="d-flex justify-content-between text-secondary"><span>GST on fee ({{ $bd['applied_gst_rate'] ?? 0 }}%):</span><span>₹{{ number_format($bd['gst_on_fee'] ?? 0, 2) }}</span></div>
                                        <div class="d-flex justify-content-between text-secondary"><span>TDS ({{ $bd['applied_tds_rate'] ?? 0 }}%):</span><span>₹{{ number_format($bd['tds_deduction'] ?? 0, 2) }}</span></div>
                                        <div class="d-flex justify-content-between text-success border-top pt-1 mt-1"><span class="fw-semibold">Expert net:</span><span class="fw-bold">₹{{ number_format($booking->expert_earning, 2) }}</span></div>
                                        @if($booking->commission_override_type)<div class="text-warning mt-1" style="font-size: 11px;" title="{{ $booking->override_reason }}"><i class="fas fa-exclamation-triangle me-1"></i>Commission override</div>@endif
                                    </div>
                                    <span class="badge border mt-1 {{ $payBadge[$booking->payment_status] ?? 'bg-light text-dark' }}">
                                        @if($booking->payment_status === 'paid')<i class="fas fa-check-circle me-1"></i>@endif{{ \App\Models\Booking::PAYMENT_STATUSES[$booking->payment_status] ?? ucfirst((string) $booking->payment_status) }}
                                    </span>
                                </td>
                                <td>
                                    <span class="badge rounded-pill {{ $statusBadge[$booking->status] ?? 'bg-secondary' }} px-3 py-2">{{ \App\Models\Booking::STATUSES[$booking->status] ?? ucfirst((string) $booking->status) }}</span>
                                    @if($booking->cancel_requested_at && in_array($booking->status, ['pending', 'confirmed'], true))<div class="text-danger mt-1" style="font-size: 11px;" title="{{ $booking->cancel_reason }}"><i class="fas fa-exclamation-circle me-1"></i>Cancel requested {{ $booking->cancel_requested_at->format('d M') }}</div>@endif
                                </td>
                                <td>
                                    @if($booking->meeting_link)
                                        <a href="{{ $booking->meeting_link }}" target="_blank" rel="noopener" class="btn btn-sm btn-outline-primary me-1"><i class="fas fa-video me-1"></i> Join Call</a>
                                        <button type="button" class="btn btn-sm btn-light border me-1" data-copy-link="{{ $booking->meeting_link }}" title="Copy Link">
                                            <i class="fas fa-copy text-secondary"></i> <span class="copy-text d-none d-xl-inline">Copy</span>
                                        </button>
                                    @else
                                        <span class="text-muted small">Not provided</span>
                                    @endif
                                </td>
                                <td class="text-end pe-4">
                                    <button class="btn btn-sm btn-outline-secondary" data-bs-toggle="modal" data-bs-target="#editAdminBookingModal{{ $booking->id }}">
                                        <i class="fas fa-cog me-1"></i> Manage
                                    </button>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="8" class="text-center py-5">
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
             {{ $bookings->links() }}
        </div>
    </div>
</div>

{{-- one dialog per booking, outside the table (a div is not allowed between table rows) --}}
@foreach($bookings as $booking)
<div class="modal fade" id="editAdminBookingModal{{ $booking->id }}" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow">
            <form action="{{ route('admin.bookings.update', $booking->id) }}" method="POST">
                @csrf
                @method('PUT')
                <div class="modal-header">
                    <h5 class="modal-title fw-bold">Manage Booking #{{ $booking->booking_id }}</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    @if($booking->cancel_requested_at && in_array($booking->status, ['pending', 'confirmed'], true))
                        <div class="alert alert-danger small py-2">
                            <b>The student asked to cancel</b> on {{ $booking->cancel_requested_at->format('d M Y, h:i A') }}@if($booking->cancel_reason): “{{ $booking->cancel_reason }}”@endif.
                            Set the status to Cancelled to accept it (a paid booking becomes Refund due), or tick below to turn it down.
                            <div class="form-check mt-1"><input class="form-check-input" type="checkbox" name="dismiss_cancel_request" value="1" id="dcr{{ $booking->id }}"><label class="form-check-label" for="dcr{{ $booking->id }}">Keep the session and clear the request</label></div>
                        </div>
                    @endif
                    @if($booking->topic || $booking->notes || $booking->attendee || $booking->class_level)
                        <div class="border rounded p-2 mb-3 small bg-light">
                            <div class="fw-semibold mb-1">From the student</div>
                            @if($booking->topic)<div><span class="text-muted">Help with:</span> {{ $booking->topic }}</div>@endif
                            @if($booking->attendee || $booking->class_level)<div><span class="text-muted">Who / class:</span> {{ collect([$booking->attendee, $booking->class_level])->filter()->implode(' · ') }}</div>@endif
                            @if($booking->notes)<div class="mt-1">“{{ $booking->notes }}”</div>@endif
                        </div>
                    @endif
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Meeting / Video Call Link</label>
                        <input type="url" name="meeting_link" class="form-control" placeholder="https://meet.google.com/xyz-abc" value="{{ $booking->meeting_link }}">
                    </div>
                    <div class="row g-3 mb-3">
                        <div class="col-6">
                            <label class="form-label fw-semibold">Booking Status</label>
                            <select name="status" class="form-select">
                                @foreach(\App\Models\Booking::STATUSES as $k => $label)<option value="{{ $k }}" @selected($booking->status === $k)>{{ $label }}</option>@endforeach
                            </select>
                            <div class="form-text">Cancelling frees the slot for other students.</div>
                        </div>
                        <div class="col-6">
                            <label class="form-label fw-semibold">Payment Status</label>
                            <select name="payment_status" class="form-select">
                                @foreach(\App\Models\Booking::PAYMENT_STATUSES as $k => $label)<option value="{{ $k }}" @selected($booking->payment_status === $k)>{{ $label }}</option>@endforeach
                            </select>
                            <div class="form-text">Mark "Paid" once the student's payment is received.</div>
                        </div>
                    </div>
                    @if($booking->canReschedule())
                        @php $options = $booking->rescheduleOptions($openSlots); @endphp
                        <div class="mb-3">
                            <label class="form-label fw-semibold">Reschedule <span class="text-muted fw-normal small">(optional)</span></label>
                            <select name="new_slot_id" class="form-select" @disabled($options->isEmpty())>
                                <option value="">{{ $options->isEmpty() ? 'No other open slot of this expert with the same fee' : 'Keep ' . $booking->sessionStart()?->format('D d M Y, h:i A') }}</option>
                                @foreach($options as $s)
                                    <option value="{{ $s->id }}">Move to {{ $s->date->format('D d M Y') }}, {{ \Carbon\Carbon::parse($s->start_time)->format('h:i A') }} - {{ \Carbon\Carbon::parse($s->end_time)->format('h:i A') }} ({{ strtoupper($s->mode ?? 'video') }})</option>
                                @endforeach
                            </select>
                            <div class="form-text">Same expert and fee (₹{{ number_format($booking->amount, 2) }}); payment and commission stay. The old slot opens again.</div>
                        </div>
                    @endif
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Session summary <span class="text-muted fw-normal small">(the expert writes it after the call; the student sees it)</span></label>
                        <textarea name="session_summary" class="form-control" rows="3" maxlength="3000">{{ $booking->session_summary }}</textarea>
                    </div>
                    @if($booking->reschedule_count)
                        <div class="small text-primary mb-3"><i class="fas fa-history me-1"></i>Rescheduled {{ $booking->reschedule_count }}x, last by {{ $booking->rescheduled_by }} on {{ $booking->rescheduled_at?->format('d M Y, h:i A') }}</div>
                    @endif
                    <div class="border rounded p-3 bg-light">
                        <div class="fw-semibold mb-2">Commission override <span class="text-muted fw-normal small">(optional, this booking only)</span></div>
                        <div class="row g-2">
                            <div class="col-5">
                                <select name="commission_override_type" class="form-select form-select-sm">
                                    <option value="">Use the rules</option>
                                    <option value="percentage" @selected($booking->commission_override_type === 'percentage')>Percentage</option>
                                    <option value="flat_fee" @selected($booking->commission_override_type === 'flat_fee')>Flat fee (₹)</option>
                                </select>
                            </div>
                            <div class="col-7"><input type="number" step="0.01" min="0" name="commission_override_value" class="form-control form-control-sm" placeholder="Value" value="{{ $booking->commission_override_value }}"></div>
                            <div class="col-12"><input type="text" name="override_reason" class="form-control form-control-sm" placeholder="Reason (required with an override)" value="{{ $booking->override_reason }}"></div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer bg-light">
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary px-4">Save Changes</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endforeach

<script>
// the link comes from a data attribute, never pasted into script (a quote in it would break the page)
document.addEventListener('click', function (e) {
    const btn = e.target.closest('[data-copy-link]'); if (!btn) return;
    navigator.clipboard.writeText(btn.dataset.copyLink).then(() => {
        const originalHtml = btn.innerHTML;
        btn.innerHTML = '<i class="fas fa-check text-success"></i> <span class="text-success small fw-bold">Copied!</span>';
        setTimeout(() => { btn.innerHTML = originalHtml; }, 2000);
    }).catch(err => alert('Failed to copy link: ' + err));
});
</script>
@endsection

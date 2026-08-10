@extends('expert.layouts.app')

@section('title', 'Slot Management')
@section('page-title', 'Manage Time Slots')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h4 class="fw-bold mb-1">Time Slots</h4>
        <p class="text-muted small mb-0">Create, update, and manage your available counseling time slots (Base Rate: ₹{{ number_format($expert->price_per_min ?? 10.00, 2) }}/min)</p>
    </div>
    <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#createSlotModal">
        <i class="bi bi-plus-lg me-1"></i> Add New Slot
    </button>
</div>

<div class="card card-custom p-3 mb-4">
    <form action="{{ route('expert.slots.index') }}" method="GET" class="row g-3 align-items-center">
        <div class="col-md-4">
            <label class="form-label small fw-semibold">Filter by Date</label>
            <input type="date" name="date" class="form-control" value="{{ request('date') }}">
        </div>
        <div class="col-md-4">
            <label class="form-label small fw-semibold">Filter by Status</label>
            <select name="status" class="form-select">
                <option value="">All Statuses</option>
                <option value="available" {{ request('status') === 'available' ? 'selected' : '' }}>Available</option>
                <option value="booked" {{ request('status') === 'booked' ? 'selected' : '' }}>Booked</option>
                <option value="blocked" {{ request('status') === 'blocked' ? 'selected' : '' }}>Blocked</option>
            </select>
        </div>
        <div class="col-md-4 d-flex align-items-end gap-2">
            <button type="submit" class="btn btn-secondary w-100"><i class="bi bi-funnel me-1"></i> Filter</button>
            <a href="{{ route('expert.slots.index') }}" class="btn btn-outline-secondary">Reset</a>
        </div>
    </form>
</div>

<div class="card card-custom p-4">
    <div class="table-responsive">
        <table class="table align-middle">
            <thead class="table-light">
                <tr>
                    <th>Date</th>
                    <th>Time Slot</th>
                    <th>Mode</th>
                    <th>Cost (₹)</th>
                    <th>Status</th>
                    <th class="text-end">Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse($slots as $slot)
                <tr>
                    <td class="fw-semibold">{{ \Carbon\Carbon::parse($slot->date)->format('D, M d, Y') }}</td>
                    <td>
                        <span class="badge bg-light text-dark border fs-6">
                            <i class="bi bi-clock me-1"></i>{{ \Carbon\Carbon::parse($slot->start_time)->format('h:i A') }} - {{ \Carbon\Carbon::parse($slot->end_time)->format('h:i A') }}
                        </span>
                    </td>
                    <td><span class="badge bg-info text-capitalize">{{ $slot->mode }}</span></td>
                    <td>₹{{ number_format($slot->cost, 2) }}</td>
                    <td>
                        @if($slot->status === 'available')
                            <span class="badge bg-success">Available</span>
                        @elseif($slot->status === 'booked')
                            <span class="badge bg-primary">Booked</span>
                        @else
                            <span class="badge bg-danger">Blocked</span>
                        @endif
                    </td>
                    <td class="text-end">
                        <button class="btn btn-sm btn-outline-secondary me-1" data-bs-toggle="modal" data-bs-target="#editSlotModal{{ $slot->id }}">
                            <i class="bi bi-pencil"></i>
                        </button>
                        <!-- Soft Delete Button -->
                        <form action="{{ route('expert.slots.destroy', $slot->id) }}" method="POST" class="d-inline" onsubmit="return confirm('Are you sure you want to soft-delete this slot?');">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="btn btn-sm btn-outline-danger" title="Soft Delete Slot">
                                <i class="bi bi-trash"></i>
                            </button>
                        </form>
                    </td>
                </tr>

                <!-- Edit Slot Modal -->
                <div class="modal fade slot-modal" id="editSlotModal{{ $slot->id }}" tabindex="-1">
                    <div class="modal-dialog">
                        <div class="modal-content">
                            <form action="{{ route('expert.slots.update', $slot->id) }}" method="POST">
                                @csrf
                                @method('PUT')
                                <div class="modal-header">
                                    <h5 class="modal-title">Edit Slot #{{ $slot->id }}</h5>
                                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                </div>
                                <div class="modal-body">
                                    <div class="mb-3">
                                        <label class="form-label">Date</label>
                                        <input type="date" name="date" class="form-control" value="{{ \Carbon\Carbon::parse($slot->date)->format('Y-m-d') }}" required>
                                    </div>
                                    <div class="row g-2 mb-3">
                                        <div class="col">
                                            <label class="form-label">Start Time</label>
                                            <input type="time" name="start_time" class="form-control start-time" value="{{ \Carbon\Carbon::parse($slot->start_time)->format('H:i') }}" required>
                                        </div>
                                        <div class="col">
                                            <label class="form-label">End Time</label>
                                            <input type="time" name="end_time" class="form-control end-time" value="{{ \Carbon\Carbon::parse($slot->end_time)->format('H:i') }}" required>
                                        </div>
                                    </div>
                                    <div class="mb-3">
                                        <label class="form-label">Mode</label>
                                        <select name="mode" class="form-select">
                                            <option value="video" {{ $slot->mode === 'video' ? 'selected' : '' }}>Video Call</option>
                                            <option value="audio" {{ $slot->mode === 'audio' ? 'selected' : '' }}>Audio Call</option>
                                            <option value="chat" {{ $slot->mode === 'chat' ? 'selected' : '' }}>Chat</option>
                                        </select>
                                    </div>
                                    <div class="mb-3">
                                        <label class="form-label">Status</label>
                                        <select name="status" class="form-select">
                                            <option value="available" {{ $slot->status === 'available' ? 'selected' : '' }}>Available</option>
                                            <option value="blocked" {{ $slot->status === 'blocked' ? 'selected' : '' }}>Blocked</option>
                                        </select>
                                    </div>
                                    <div class="mb-3">
                                        <label class="form-label">Price / Fee (₹)</label>
                                        <input type="number" step="0.01" name="cost" class="form-control cost-input" value="{{ $slot->cost }}">
                                        <small class="form-text text-muted calc-info">Rate: ₹{{ number_format($expert->price_per_min ?? 10.00, 2) }}/min. Editable.</small>
                                    </div>
                                </div>
                                <div class="modal-footer">
                                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancel</button>
                                    <button type="submit" class="btn btn-primary">Update Slot</button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
                @empty
                <tr>
                    <td colspan="6" class="text-center text-muted py-4">No slots found. Create your first time slot!</td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="mt-3">
        {{ $slots->withQueryString()->links() }}
    </div>
</div>

<!-- Create Slot Modal -->
<div class="modal fade slot-modal" id="createSlotModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title fw-bold"><i class="bi bi-clock me-2"></i>Add Availability Slot</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body p-4">
                <ul class="nav nav-pills nav-fill mb-4 border-bottom pb-3" id="expertSlotTabs" role="tablist">
                    <li class="nav-item" role="presentation">
                        <button class="nav-link active fw-bold" id="expert-single-tab" data-bs-toggle="pill" data-bs-target="#expert-single" type="button" role="tab">
                            <i class="bi bi-calendar-day me-2"></i>Single Slot
                        </button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link fw-bold" id="expert-bulk-tab" data-bs-toggle="pill" data-bs-target="#expert-bulk" type="button" role="tab">
                            <i class="bi bi-calendar-range me-2"></i>Bulk / Recurring Slots
                        </button>
                    </li>
                </ul>

                <div class="tab-content" id="expertSlotTabsContent">
                    <!-- Single Slot Tab -->
                    <div class="tab-pane fade show active" id="expert-single" role="tabpanel">
                        <form action="{{ route('expert.slots.store') }}" method="POST">
                            @csrf
                            <input type="hidden" name="type" value="single">
                            <div class="mb-3">
                                <label class="form-label fw-semibold">Date <span class="text-danger">*</span></label>
                                <input type="date" name="date" class="form-control" value="{{ date('Y-m-d') }}" min="{{ date('Y-m-d') }}" required>
                            </div>
                            <div class="row g-2 mb-3">
                                <div class="col">
                                    <label class="form-label fw-semibold">Start Time <span class="text-danger">*</span></label>
                                    <input type="time" name="start_time" class="form-control start-time" required>
                                </div>
                                <div class="col">
                                    <label class="form-label fw-semibold">End Time <span class="text-danger">*</span></label>
                                    <input type="time" name="end_time" class="form-control end-time" required>
                                </div>
                            </div>
                            <div class="mb-3">
                                <label class="form-label fw-semibold">Session Mode <span class="text-danger">*</span></label>
                                <select name="mode" class="form-select" required>
                                    <option value="video">Video Call</option>
                                    <option value="audio">Audio Call</option>
                                    <option value="chat">Chat</option>
                                </select>
                            </div>
                            <div class="mb-3">
                                <label class="form-label fw-semibold">Price / Fee (₹)</label>
                                <input type="number" step="0.01" name="cost" class="form-control cost-input" placeholder="0.00">
                                <small class="form-text text-muted calc-info">Rate: ₹{{ number_format($expert->price_per_min ?? 10.00, 2) }}/min. Auto-calculated if blank.</small>
                            </div>
                            <div class="text-end pt-2 border-top">
                                <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancel</button>
                                <button type="submit" class="btn btn-primary px-4">Create Single Slot</button>
                            </div>
                        </form>
                    </div>

                    <!-- Bulk / Recurring Tab -->
                    <div class="tab-pane fade" id="expert-bulk" role="tabpanel">
                        <form action="{{ route('expert.slots.store') }}" method="POST">
                            @csrf
                            <input type="hidden" name="type" value="bulk">
                            <div class="row g-2 mb-3">
                                <div class="col">
                                    <label class="form-label fw-semibold">From Date <span class="text-danger">*</span></label>
                                    <input type="date" name="start_date" class="form-control" value="{{ date('Y-m-d') }}" min="{{ date('Y-m-d') }}" required>
                                </div>
                                <div class="col">
                                    <label class="form-label fw-semibold">To Date <span class="text-danger">*</span></label>
                                    <input type="date" name="end_date" class="form-control" value="{{ date('Y-m-d', strtotime('+30 days')) }}" min="{{ date('Y-m-d') }}" required>
                                </div>
                            </div>
                            <div class="mb-3">
                                <label class="form-label fw-semibold">Repeat On Days <span class="text-danger">*</span></label>
                                <div class="d-flex flex-wrap gap-3 p-3 bg-light rounded border">
                                    @foreach(['Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat', 'Sun'] as $day)
                                        <div class="form-check">
                                            <input class="form-check-input" type="checkbox" name="days[]" value="{{ $day }}" id="exp-day-{{ $day }}" checked>
                                            <label class="form-check-label fw-semibold" for="exp-day-{{ $day }}">{{ $day }}</label>
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                            <div class="row g-2 mb-3">
                                <div class="col">
                                    <label class="form-label fw-semibold">Start Time <span class="text-danger">*</span></label>
                                    <input type="time" name="start_time" class="form-control start-time" required>
                                </div>
                                <div class="col">
                                    <label class="form-label fw-semibold">End Time <span class="text-danger">*</span></label>
                                    <input type="time" name="end_time" class="form-control end-time" required>
                                </div>
                            </div>
                            <div class="row g-2 mb-3">
                                <div class="col">
                                    <label class="form-label fw-semibold">Session Mode <span class="text-danger">*</span></label>
                                    <select name="mode" class="form-select" required>
                                        <option value="video">Video Call</option>
                                        <option value="audio">Audio Call</option>
                                        <option value="chat">Chat</option>
                                    </select>
                                </div>
                                <div class="col">
                                    <label class="form-label fw-semibold">Price / Fee (₹)</label>
                                    <input type="number" step="0.01" name="cost" class="form-control cost-input" placeholder="0.00">
                                </div>
                            </div>
                            <div class="text-end pt-2 border-top">
                                <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancel</button>
                                <button type="submit" class="btn btn-success px-4"><i class="bi bi-magic me-1"></i> Generate Bulk Slots</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const pricePerMin = parseFloat("{{ $expert->price_per_min ?? 10.00 }}");

    function calculateCost(modal) {
        const startTimeInput = modal.querySelector('.start-time');
        const endTimeInput = modal.querySelector('.end-time');
        const costInput = modal.querySelector('.cost-input');
        const calcInfo = modal.querySelector('.calc-info');

        if (!startTimeInput || !endTimeInput || !costInput) return;

        const startTime = startTimeInput.value;
        const endTime = endTimeInput.value;

        if (startTime && endTime) {
            const [startH, startM] = startTime.split(':').map(Number);
            const [endH, endM] = endTime.split(':').map(Number);

            const startMinutes = startH * 60 + startM;
            const endMinutes = endH * 60 + endM;

            if (endMinutes > startMinutes) {
                const duration = endMinutes - startMinutes;
                const calculatedCost = (duration * pricePerMin).toFixed(2);
                costInput.value = calculatedCost;
                if (calcInfo) {
                    calcInfo.textContent = `Auto-calculated: ₹${calculatedCost} (${duration} mins × ₹${pricePerMin}/min). You can manually edit this.`;
                }
            } else if (calcInfo) {
                calcInfo.textContent = `End time must be after start time.`;
            }
        }
    }

    document.querySelectorAll('.slot-modal').forEach(function(modal) {
        const startTimeInput = modal.querySelector('.start-time');
        const endTimeInput = modal.querySelector('.end-time');

        if (startTimeInput) {
            startTimeInput.addEventListener('change', function() { calculateCost(modal); });
        }
        if (endTimeInput) {
            endTimeInput.addEventListener('change', function() { calculateCost(modal); });
        }
    });
});
</script>
@endsection

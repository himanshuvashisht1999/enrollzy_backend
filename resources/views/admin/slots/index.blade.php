@extends('admin.layouts.master')

@section('title', 'Manage Expert Time Slots')

@section('content')
    <div class="container-fluid px-0">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h4 class="mb-1 text-dark fw-bold">Expert Time Slots</h4>
                <p class="text-muted small mb-0">Create, edit, update, or remove counselor availability slots</p>
            </div>
            <button class="btn btn-primary px-4 shadow-sm" data-bs-toggle="modal" data-bs-target="#createSlotModal">
                <i class="fas fa-plus-circle me-1"></i> Add New Slot
            </button>
        </div>

        <!-- Flash Messages -->
        @if(session('success'))
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                <i class="fas fa-check-circle me-2"></i>{{ session('success') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        @endif

        @if($errors->any())
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <i class="fas fa-exclamation-triangle me-2"></i>Please fix the following errors:
                <ul class="mb-0 mt-1">
                    @foreach($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        @endif

        <!-- Filters -->
        <div class="card border-0 shadow-sm mb-4">
            <div class="card-body">
                <form action="{{ route('admin.slots.index') }}" method="GET" class="row g-3 align-items-end">
                    <div class="col-md-4">
                        <label class="form-label small fw-semibold text-muted">Counselor / Expert</label>
                        <select name="expert_id" class="form-select">
                            <option value="">All Experts</option>
                            @foreach($experts as $expert)
                                <option value="{{ $expert->id }}" data-price-per-min="{{ $expert->price_per_min ?? 10.00 }}" {{ request('expert_id') == $expert->id ? 'selected' : '' }}>
                                    {{ $expert->name }} (₹{{ number_format($expert->price_per_min ?? 10.00, 2) }}/min)
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label small fw-semibold text-muted">Date</label>
                        <input type="date" name="date" class="form-control" value="{{ request('date') }}">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label small fw-semibold text-muted">Status</label>
                        <select name="status" class="form-select">
                            <option value="">All Statuses</option>
                            <option value="available" {{ request('status') === 'available' ? 'selected' : '' }}>Available
                            </option>
                            <option value="booked" {{ request('status') === 'booked' ? 'selected' : '' }}>Booked</option>
                            <option value="blocked" {{ request('status') === 'blocked' ? 'selected' : '' }}>Blocked</option>
                        </select>
                    </div>
                    <div class="col-md-2 d-flex gap-2">
                        <button type="submit" class="btn btn-secondary w-100">
                            <i class="fas fa-filter me-1"></i> Filter
                        </button>
                        @if(request()->anyFilled(['expert_id', 'date', 'status']))
                            <a href="{{ route('admin.slots.index') }}" class="btn btn-outline-danger">Clear</a>
                        @endif
                    </div>
                </form>
            </div>
        </div>

        <!-- Slots Table -->
        <div class="card border-0 shadow-sm">
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="bg-light text-uppercase small text-muted">
                            <tr>
                                <th class="ps-4">Counselor</th>
                                <th>Date</th>
                                <th>Time Slot</th>
                                <th>Mode</th>
                                <th>Fee (₹)</th>
                                <th>Status</th>
                                <th class="text-end pe-4">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($slots as $slot)
                                <tr>
                                    <td class="ps-4">
                                        <div class="fw-bold text-dark">{{ $slot->expert->name ?? 'N/A' }}</div>
                                        <small class="text-muted">{{ $slot->expert->email ?? '' }} | Rate:
                                            ₹{{ number_format($slot->expert->price_per_min ?? 10.00, 2) }}/min</small>
                                    </td>
                                    <td>
                                        <span class="fw-semibold text-dark">
                                            {{ \Carbon\Carbon::parse($slot->date)->format('D, M d, Y') }}
                                        </span>
                                    </td>
                                    <td>
                                        <span class="badge bg-light text-dark border fs-6">
                                            <i class="far fa-clock me-1"></i>
                                            {{ \Carbon\Carbon::parse($slot->start_time)->format('h:i A') }} -
                                            {{ \Carbon\Carbon::parse($slot->end_time)->format('h:i A') }}
                                        </span>
                                    </td>
                                    <td>
                                        <span class="badge bg-info text-capitalize">{{ $slot->mode }}</span>
                                    </td>
                                    <td>
                                        <span class="fw-bold text-dark">₹{{ number_format($slot->cost, 2) }}</span>
                                    </td>
                                    <td>
                                        @if($slot->status === 'available')
                                            <span class="badge bg-success">Available</span>
                                        @elseif($slot->status === 'booked')
                                            <span class="badge bg-primary">Booked</span>
                                        @else
                                            <span class="badge bg-danger">Blocked</span>
                                        @endif
                                    </td>
                                    <td class="text-end pe-4">
                                        <button class="btn btn-sm btn-outline-primary me-1" data-bs-toggle="modal"
                                            data-bs-target="#editSlotModal{{ $slot->id }}">
                                            <i class="fas fa-edit"></i> Edit
                                        </button>
                                        <form action="{{ route('admin.slots.destroy', $slot->id) }}" method="POST"
                                            class="d-inline"
                                            onsubmit="return confirm('Are you sure you want to delete this time slot?');">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-sm btn-outline-danger">
                                                <i class="fas fa-trash-alt"></i> Delete
                                            </button>
                                        </form>
                                    </td>
                                </tr>

                                <!-- Edit Slot Modal -->
                                <div class="modal fade slot-modal" id="editSlotModal{{ $slot->id }}" tabindex="-1"
                                    aria-hidden="true">
                                    <div class="modal-dialog modal-dialog-centered">
                                        <div class="modal-content border-0 shadow">
                                            <form action="{{ route('admin.slots.update', $slot->id) }}" method="POST">
                                                @csrf
                                                @method('PUT')
                                                <div class="modal-header">
                                                    <h5 class="modal-title fw-bold">Edit Time Slot #{{ $slot->id }}</h5>
                                                    <button type="button" class="btn-close" data-bs-dismiss="modal"
                                                        aria-label="Close"></button>
                                                </div>
                                                <div class="modal-body">
                                                    <div class="mb-3">
                                                        <label class="form-label fw-semibold">Select Counselor</label>
                                                        <select name="expert_id" class="form-select expert-select" required>
                                                            @foreach($experts as $expert)
                                                                <option value="{{ $expert->id }}"
                                                                    data-price-per-min="{{ $expert->price_per_min ?? 10.00 }}" {{ $slot->expert_id == $expert->id ? 'selected' : '' }}>
                                                                    {{ $expert->name }}
                                                                    (₹{{ number_format($expert->price_per_min ?? 10.00, 2) }}/min)
                                                                </option>
                                                            @endforeach
                                                        </select>
                                                    </div>
                                                    <div class="mb-3">
                                                        <label class="form-label fw-semibold">Date</label>
                                                        <input type="date" name="date" class="form-control"
                                                            value="{{ \Carbon\Carbon::parse($slot->date)->format('Y-m-d') }}"
                                                            required>
                                                    </div>
                                                    <div class="row g-2 mb-3">
                                                        <div class="col">
                                                            <label class="form-label fw-semibold">Start Time</label>
                                                            <input type="time" name="start_time" class="form-control start-time"
                                                                value="{{ \Carbon\Carbon::parse($slot->start_time)->format('H:i') }}"
                                                                required>
                                                        </div>
                                                        <div class="col">
                                                            <label class="form-label fw-semibold">End Time</label>
                                                            <input type="time" name="end_time" class="form-control end-time"
                                                                value="{{ \Carbon\Carbon::parse($slot->end_time)->format('H:i') }}"
                                                                required>
                                                        </div>
                                                    </div>
                                                    <div class="mb-3">
                                                        <label class="form-label fw-semibold">Session Mode</label>
                                                        <select name="mode" class="form-select" required>
                                                            <option value="video" {{ $slot->mode === 'video' ? 'selected' : '' }}>
                                                                Video Call</option>
                                                            <option value="audio" {{ $slot->mode === 'audio' ? 'selected' : '' }}>
                                                                Audio Call</option>
                                                            <option value="chat" {{ $slot->mode === 'chat' ? 'selected' : '' }}>
                                                                Chat</option>
                                                        </select>
                                                    </div>
                                                    <div class="mb-3">
                                                        <label class="form-label fw-semibold">Status</label>
                                                        <select name="status" class="form-select" required>
                                                            <option value="available" {{ $slot->status === 'available' ? 'selected' : '' }}>Available</option>
                                                            <option value="booked" {{ $slot->status === 'booked' ? 'selected' : '' }}>Booked</option>
                                                            <option value="blocked" {{ $slot->status === 'blocked' ? 'selected' : '' }}>Blocked</option>
                                                        </select>
                                                    </div>
                                                    <div class="mb-3">
                                                        <label class="form-label fw-semibold">Fee / Price (₹)</label>
                                                        <input type="number" step="0.01" name="cost"
                                                            class="form-control cost-input" value="{{ $slot->cost }}">
                                                        <small class="form-text text-muted calc-info">Calculated rate: Price Per
                                                            Minute
                                                            (₹{{ number_format($slot->expert->price_per_min ?? 10.00, 2) }}/min).
                                                            Editable.</small>
                                                    </div>
                                                </div>
                                                <div class="modal-footer bg-light">
                                                    <button type="button" class="btn btn-light"
                                                        data-bs-dismiss="modal">Cancel</button>
                                                    <button type="submit" class="btn btn-primary px-4">Update Slot</button>
                                                </div>
                                            </form>
                                        </div>
                                    </div>
                                </div>
                            @empty
                                <tr>
                                    <td colspan="7" class="text-center py-5 text-muted">
                                        <i class="fas fa-calendar-times fa-2x mb-3 d-block opacity-50"></i>
                                        No time slots found. Click <strong>Add New Slot</strong> above to create one.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
            <div class="card-footer bg-white pt-3 border-0">
                {{ $slots->appends(request()->query())->links() }}
            </div>
        </div>
    </div>

    <!-- Create Slot Modal -->
    <div class="modal fade slot-modal" id="createSlotModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-lg">
            <div class="modal-content border-0 shadow">
                <div class="modal-header">
                    <h5 class="modal-title fw-bold">Add New Expert Time Slot</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body p-4">
                    <ul class="nav nav-pills nav-fill mb-4 border-bottom pb-3" id="adminSlotTabs" role="tablist">
                        <li class="nav-item" role="presentation">
                            <button class="nav-link active fw-bold" id="admin-single-tab" data-bs-toggle="pill"
                                data-bs-target="#admin-single" type="button" role="tab">
                                <i class="fas fa-calendar-day me-2"></i>Single Slot
                            </button>
                        </li>
                        <li class="nav-item" role="presentation">
                            <button class="nav-link fw-bold" id="admin-bulk-tab" data-bs-toggle="pill"
                                data-bs-target="#admin-bulk" type="button" role="tab">
                                <i class="fas fa-calendar-alt me-2"></i>Bulk / Recurring Slots
                            </button>
                        </li>
                    </ul>

                    <div class="tab-content" id="adminSlotTabsContent">
                        <!-- Single Slot Tab -->
                        <div class="tab-pane fade show active" id="admin-single" role="tabpanel">
                            <form action="{{ route('admin.slots.store') }}" method="POST">
                                @csrf
                                <input type="hidden" name="type" value="single">
                                <div class="mb-3">
                                    <label class="form-label fw-semibold">Select Counselor <span
                                            class="text-danger">*</span></label>
                                    <select name="expert_id" class="form-select expert-select" required>
                                        <option value="">-- Choose Counselor --</option>
                                        @foreach($experts as $expert)
                                            <option value="{{ $expert->id }}"
                                                data-price-per-min="{{ $expert->price_per_min ?? 10.00 }}" {{ request('expert_id') == $expert->id ? 'selected' : '' }}>
                                                {{ $expert->name }}
                                                (₹{{ number_format($expert->price_per_min ?? 10.00, 2) }}/min)
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="mb-3">
                                    <label class="form-label fw-semibold">Date <span class="text-danger">*</span></label>
                                    <input type="date" name="date" class="form-control" value="{{ date('Y-m-d') }}"
                                        min="{{ date('Y-m-d') }}" required>
                                </div>
                                <div class="row g-2 mb-3">
                                    <div class="col">
                                        <label class="form-label fw-semibold">Start Time <span
                                                class="text-danger">*</span></label>
                                        <input type="time" name="start_time" class="form-control start-time" required>
                                    </div>
                                    <div class="col">
                                        <label class="form-label fw-semibold">End Time <span
                                                class="text-danger">*</span></label>
                                        <input type="time" name="end_time" class="form-control end-time" required>
                                    </div>
                                </div>
                                <div class="row g-2 mb-3">
                                    <div class="col">
                                        <label class="form-label fw-semibold">Session Mode <span
                                                class="text-danger">*</span></label>
                                        <select name="mode" class="form-select" required>
                                            <option value="video">Video Call</option>
                                            <option value="audio">Audio Call</option>
                                            <option value="chat">Chat</option>
                                        </select>
                                    </div>
                                    <div class="col">
                                        <label class="form-label fw-semibold">Initial Status</label>
                                        <select name="status" class="form-select">
                                            <option value="available">Available</option>
                                            <option value="blocked">Blocked</option>
                                        </select>
                                    </div>
                                </div>
                                <div class="mb-3">
                                    <label class="form-label fw-semibold">Fee / Price (₹)</label>
                                    <input type="number" step="0.01" name="cost" class="form-control cost-input"
                                        placeholder="0.00">
                                    <small class="form-text text-muted calc-info">Select counselor and start/end time to
                                        auto-calculate price. Editable.</small>
                                </div>
                                <div class="text-end pt-2 border-top">
                                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancel</button>
                                    <button type="submit" class="btn btn-primary px-4">Create Single Slot</button>
                                </div>
                            </form>
                        </div>

                        <!-- Bulk / Recurring Tab -->
                        <div class="tab-pane fade" id="admin-bulk" role="tabpanel">
                            <form action="{{ route('admin.slots.store') }}" method="POST">
                                @csrf
                                <input type="hidden" name="type" value="bulk">
                                <div class="mb-3">
                                    <label class="form-label fw-semibold">Select Counselor <span
                                            class="text-danger">*</span></label>
                                    <select name="expert_id" class="form-select expert-select" required>
                                        <option value="">-- Choose Counselor --</option>
                                        @foreach($experts as $expert)
                                            <option value="{{ $expert->id }}"
                                                data-price-per-min="{{ $expert->price_per_min ?? 10.00 }}" {{ request('expert_id') == $expert->id ? 'selected' : '' }}>
                                                {{ $expert->name }}
                                                (₹{{ number_format($expert->price_per_min ?? 10.00, 2) }}/min)
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="row g-2 mb-3">
                                    <div class="col">
                                        <label class="form-label fw-semibold">From Date <span
                                                class="text-danger">*</span></label>
                                        <input type="date" name="start_date" class="form-control"
                                            value="{{ date('Y-m-d') }}" min="{{ date('Y-m-d') }}" required>
                                    </div>
                                    <div class="col">
                                        <label class="form-label fw-semibold">To Date <span
                                                class="text-danger">*</span></label>
                                        <input type="date" name="end_date" class="form-control"
                                            value="{{ date('Y-m-d', strtotime('+30 days')) }}" min="{{ date('Y-m-d') }}"
                                            required>
                                    </div>
                                </div>
                                <div class="mb-3">
                                    <label class="form-label fw-semibold">Repeat On Days <span
                                            class="text-danger">*</span></label>
                                    <div class="d-flex flex-wrap gap-3 p-3 bg-light rounded border">
                                        @foreach(['Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat', 'Sun'] as $day)
                                            <div class="form-check">
                                                <input class="form-check-input" type="checkbox" name="days[]" value="{{ $day }}"
                                                    id="admin-day-{{ $day }}" checked>
                                                <label class="form-check-label fw-semibold"
                                                    for="admin-day-{{ $day }}">{{ $day }}</label>
                                            </div>
                                        @endforeach
                                    </div>
                                </div>
                                <div class="row g-2 mb-3">
                                    <div class="col">
                                        <label class="form-label fw-semibold">Start Time <span
                                                class="text-danger">*</span></label>
                                        <input type="time" name="start_time" class="form-control start-time" required>
                                    </div>
                                    <div class="col">
                                        <label class="form-label fw-semibold">End Time <span
                                                class="text-danger">*</span></label>
                                        <input type="time" name="end_time" class="form-control end-time" required>
                                    </div>
                                </div>
                                <div class="row g-2 mb-3">
                                    <div class="col">
                                        <label class="form-label fw-semibold">Session Mode <span
                                                class="text-danger">*</span></label>
                                        <select name="mode" class="form-select" required>
                                            <option value="video">Video Call</option>
                                            <option value="audio">Audio Call</option>
                                            <option value="chat">Chat</option>
                                        </select>
                                    </div>
                                    <div class="col">
                                        <label class="form-label fw-semibold">Cost / Slot (₹)</label>
                                        <input type="number" step="0.01" name="cost" class="form-control cost-input"
                                            placeholder="0.00">
                                    </div>
                                </div>
                                <div class="text-end pt-2 border-top">
                                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancel</button>
                                    <button type="submit" class="btn btn-success px-4"><i class="fas fa-magic me-1"></i>
                                        Generate Bulk Slots</button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            function calculateCost(modal) {
                const expertSelect = modal.querySelector('.expert-select');
                const startTimeInput = modal.querySelector('.start-time');
                const endTimeInput = modal.querySelector('.end-time');
                const costInput = modal.querySelector('.cost-input');
                const calcInfo = modal.querySelector('.calc-info');

                if (!expertSelect || !startTimeInput || !endTimeInput || !costInput) return;

                const selectedOption = expertSelect.options[expertSelect.selectedIndex];
                if (!selectedOption || !selectedOption.value) return;

                const pricePerMin = parseFloat(selectedOption.getAttribute('data-price-per-min') || 10.00);

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

            document.querySelectorAll('.slot-modal').forEach(function (modal) {
                const expertSelect = modal.querySelector('.expert-select');
                const startTimeInput = modal.querySelector('.start-time');
                const endTimeInput = modal.querySelector('.end-time');

                if (expertSelect) {
                    expertSelect.addEventListener('change', function () {
                        calculateCost(modal);
                    });
                }
                if (startTimeInput) {
                    startTimeInput.addEventListener('change', function () {
                        calculateCost(modal);
                    });
                }
                if (endTimeInput) {
                    endTimeInput.addEventListener('change', function () {
                        calculateCost(modal);
                    });
                }
            });
        });
    </script>
@endsection
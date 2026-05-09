@extends('admin.layouts.master')

@section('title', 'Admin Dashboard')

@section('content')
<div class="row g-4 mb-4">
    <!-- Experts Card -->
    <div class="col-md-3">
        <div class="card border-0 shadow-sm rounded-4 hover-lift">
            <div class="card-body p-4">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <div class="stats-icon bg-primary-subtle text-primary rounded-3 p-3">
                        <i class="fas fa-user-graduate fa-lg"></i>
                    </div>
                    <span class="badge bg-success-subtle text-success small">+12%</span>
                </div>
                <h3 class="fw-bold mb-1">{{ $stats['experts'] }}</h3>
                <p class="text-muted mb-0 small uppercase fw-bold ls-1">Total Experts</p>
            </div>
        </div>
    </div>

    <!-- Blogs Card -->
    <div class="col-md-3">
        <div class="card border-0 shadow-sm rounded-4 hover-lift">
            <div class="card-body p-4">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <div class="stats-icon bg-info-subtle text-info rounded-3 p-3">
                        <i class="fas fa-newspaper fa-lg"></i>
                    </div>
                    <span class="badge bg-info-subtle text-info small">{{ $stats['categories'] }} Cats</span>
                </div>
                <h3 class="fw-bold mb-1">{{ $stats['blogs'] }}</h3>
                <p class="text-muted mb-0 small uppercase fw-bold ls-1">Blog Posts</p>
            </div>
        </div>
    </div>

    <!-- Leads Card -->
    <div class="col-md-3">
        <div class="card border-0 shadow-sm rounded-4 hover-lift">
            <div class="card-body p-4">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <div class="stats-icon bg-warning-subtle text-warning rounded-3 p-3">
                        <i class="fas fa-envelope-open-text fa-lg"></i>
                    </div>
                    @if($stats['new_leads'] > 0)
                        <span class="badge bg-danger text-white pulse">New</span>
                    @endif
                </div>
                <h3 class="fw-bold mb-1">{{ $stats['leads'] }}</h3>
                <p class="text-muted mb-0 small uppercase fw-bold ls-1">Student Leads</p>
            </div>
        </div>
    </div>

    <!-- Testimonials Card -->
    <div class="col-md-3">
        <div class="card border-0 shadow-sm rounded-4 hover-lift">
            <div class="card-body p-4">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <div class="stats-icon bg-success-subtle text-success rounded-3 p-3">
                        <i class="fas fa-quote-left fa-lg"></i>
                    </div>
                    <div class="text-warning small">
                        <i class="fas fa-star"></i> 4.9
                    </div>
                </div>
                <h3 class="fw-bold mb-1">{{ $stats['testimonials'] }}</h3>
                <p class="text-muted mb-0 small uppercase fw-bold ls-1">Reviews</p>
            </div>
        </div>
    </div>
</div>

<div class="row g-4 mb-4">
    <div class="col-md-3">
        <div class="card border-0 shadow-sm rounded-4 hover-lift">
            <div class="card-body p-4">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <div class="stats-icon bg-info-subtle text-info rounded-3 p-3">
                        <i class="fas fa-users fa-lg"></i>
                    </div>
                </div>
                <h3 class="fw-bold mb-1">{{ $stats['total_staff'] }}</h3>
                <p class="text-muted mb-0 small uppercase fw-bold ls-1">Total Staff</p>
            </div>
        </div>
    </div>

    <div class="col-md-3">
        <div class="card border-0 shadow-sm rounded-4 hover-lift">
            <div class="card-body p-4">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <div class="stats-icon bg-danger-subtle text-danger rounded-3 p-3">
                        <i class="fas fa-tasks fa-lg"></i>
                    </div>
                </div>
                <h3 class="fw-bold mb-1 text-danger">{{ $stats['pending_tasks'] }}</h3>
                <p class="text-muted mb-0 small uppercase fw-bold ls-1">My Pending Tasks</p>
            </div>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-lg-12">
        <div class="card border-0 shadow-sm rounded-4 mb-4">
            <div class="card-header bg-white py-3 border-0 d-flex justify-content-between align-items-center">
                <h6 class="mb-0 fw-bold"><i class="fas fa-clock me-2 text-primary"></i>Attendance Tracking</h6>
                <div class="d-flex align-items-center gap-3">
                    <div id="digital_clock" class="fw-bold text-dark border-end pe-3" style="font-family: 'monospace'; font-size: 1.1rem;">00:00:00</div>
                    @if($attendance)
                        <span class="badge bg-primary text-white rounded-pill px-3 py-2" id="worked_time_display">
                            <i class="fas fa-hourglass-half me-1"></i> Worked: <span id="ticker">00:00:00</span>
                        </span>
                    @else
                        <span class="badge bg-secondary-subtle text-secondary rounded-pill px-3">Offline</span>
                    @endif
                </div>
            </div>
            <div class="card-body border-top border-light">
                @if($attendance)
                    <div class="alert alert-info border-0 bg-info-subtle rounded-3 d-flex align-items-center mb-4">
                        <i class="fas fa-user-clock me-3 fs-4"></i>
                        <div>
                            <div class="fw-bold text-info">Active Session</div>
                            <small class="text-info-emphasis">You clocked in at <strong>{{ date('h:i A', strtotime($attendance->check_in)) }}</strong> from <strong>{{ ucfirst($attendance->work_from) }}</strong>.</small>
                        </div>
                    </div>
                @endif

                <div class="d-flex flex-wrap gap-3">
                    @php
                        $cameraAction = $attendance ? 'check_out' : 'check_in';
                    @endphp
                    
                    <button class="btn btn-info btn-lg px-4 fw-bold text-white shadow-sm"
                            style="background-color: #3bc0c3; border: 0;"
                            data-bs-toggle="modal" 
                            data-bs-target="#cameraModal" 
                            onclick="setPunchType('{{ $cameraAction }}')">
                        <i class="fas fa-camera me-2"></i>{{ $cameraAction == 'check_out' ? 'Camera Punch Out' : 'Camera Punch In' }}
                    </button>

                    @if (!$attendance)
                        <button class="btn btn-success btn-lg px-4 fw-bold shadow-sm" data-bs-toggle="modal" data-bs-target="#checkInModal">
                            <i class="fas fa-sign-in-alt me-2"></i>Punch IN
                        </button>
                    @else
                        @if ($breaks)
                            @if ($breaks->end === null && $breaks->duration === null)
                                <button class="btn btn-warning btn-lg px-4 fw-bold text-white shadow-sm" data-bs-toggle="modal" data-bs-target="#endLunchBreak">
                                    <i class="fas fa-pause me-2"></i>End {{ ucfirst($breaks->type) }} Break
                                </button>
                            @else
                                <button class="btn btn-warning btn-lg px-4 fw-bold text-white shadow-sm" data-bs-toggle="modal" data-bs-target="#breakTimeModal">
                                    <i class="fas fa-coffee me-2"></i>Take a Break
                                </button>
                            @endif
                        @else
                            <button class="btn btn-warning btn-lg px-4 fw-bold text-white shadow-sm" data-bs-toggle="modal" data-bs-target="#breakTimeModal">
                                <i class="fas fa-coffee me-2"></i>Take a Break
                            </button>
                        @endif

                        <button class="btn btn-danger btn-lg px-4 fw-bold shadow-sm" data-bs-toggle="modal" data-bs-target="#logoutDaymodal">
                            <i class="fas fa-sign-out-alt me-2"></i>Punch Out
                        </button>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>

@include('admin.hr.attendance.modals')

<!-- Camera Modal -->
<div class="modal fade" id="cameraModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg rounded-4">
            <div class="modal-header border-0 pb-0">
                <h5 class="modal-title fw-bold text-primary"><i class="fas fa-camera me-2"></i>Verify Your Face</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close" onclick="stopCamera()"></button>
            </div>
            <div class="modal-body text-center p-4">
                <div id="my_camera" class="rounded-4 overflow-hidden border shadow-sm mx-auto mb-3" style="width: 320px; height: 240px; background: #000;"></div>
                <p class="text-muted small">Capture a clear photo to verify your identity and complete the punch.</p>
                <div class="d-grid mt-4">
                    <button type="button" class="btn btn-primary btn-lg rounded-pill fw-bold py-3" onclick="captureAndPunch()">
                        <i class="fas fa-shutter-speed me-2"></i>Capture & Confirm
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="row">
        </div>
    </div>
</div>

<div class="row">
    <div class="col-lg-8">
        <!-- Pending Tasks Card -->
        <div class="card border-0 shadow-sm rounded-4 mb-4">
            <div class="card-header bg-white py-3 border-0 d-flex justify-content-between align-items-center">
                <h6 class="mb-0 fw-bold">My Pending Tasks</h6>
                @if($stats['pending_tasks'] > 0)
                    <span class="badge bg-danger rounded-pill px-3">{{ $stats['pending_tasks'] }} Tasks</span>
                @endif
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="bg-light small text-uppercase">
                            <tr>
                                <th class="ps-4">Task Title</th>
                                <th class="text-end pe-4">Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($pendingTasks as $task)
                                <tr>
                                    <td class="ps-4">
                                        <div class="fw-bold">{{ $task->title }}</div>
                                        <small class="text-muted">Assigned recently</small>
                                    </td>
                                    <td class="text-end pe-4">
                                        <a href="{{ route('admin.hr.projects.tasks.edit', encrypt($task->id)) }}" class="btn btn-sm btn-soft-primary rounded-pill">
                                            <i class="fas fa-edit me-1"></i> View Task
                                        </a>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="2" class="text-center py-4 text-muted">
                                        <i class="fas fa-check-circle text-success me-2 fs-4"></i> Great job! No pending tasks.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <div class="card border-0 shadow-sm rounded-4 mb-4">
            <div class="card-header bg-white py-3 border-0">
                <h6 class="mb-0 fw-bold">Recent Inquiries</h6>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="bg-light small text-uppercase">
                            <tr>
                                <th class="ps-4">Student</th>
                                <th>Subject</th>
                                <th>Status</th>
                                <th class="text-end pe-4">Date</th>
                            </tr>
                        </thead>
                        <tbody>
                            {{-- This will be populated dynamically if we want --}}
                            <tr>
                                <td colspan="4" class="text-center py-4 text-muted">Go to <a href="{{ route('leads.index') }}">Leads</a> to see all inquiries.</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
    <div class="col-lg-4">
        <div class="card border-0 shadow-sm rounded-4">
            <div class="card-header bg-white py-3 border-0">
                <h6 class="mb-0 fw-bold">Quick Actions</h6>
            </div>
            <div class="card-body p-4">
                <div class="d-grid gap-2">
                    <a href="{{ route('blogs.create') }}" class="btn btn-primary py-2"><i class="fas fa-pen-nib me-2"></i> Write a Blog</a>
                    <a href="{{ route('experts.create') }}" class="btn btn-outline-secondary py-2"><i class="fas fa-plus me-2"></i> Add Expert</a>
                    <a href="{{ route('categories.create') }}" class="btn btn-light py-2 border"><i class="fas fa-tag me-2"></i> New Category</a>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
    .hover-lift { transition: transform 0.2s ease, box-shadow 0.2s ease; }
    .hover-lift:hover { transform: translateY(-5px); box-shadow: 0 10px 20px rgba(0,0,0,0.05) !important; }
    .ls-1 { letter-spacing: 0.5px; }
    .pulse { animation: pulse-animation 2s infinite; }
    @keyframes pulse-animation {
        0% { transform: scale(0.95); box-shadow: 0 0 0 0 rgba(220, 53, 69, 0.7); }
        70% { transform: scale(1); box-shadow: 0 0 0 10px rgba(220, 53, 69, 0); }
        100% { transform: scale(0.95); box-shadow: 0 0 0 0 rgba(220, 53, 69, 0); }
    }
</style>
@push('js')
<script src="https://cdnjs.cloudflare.com/ajax/libs/webcamjs/1.0.26/webcam.min.js"></script>
<script>
    Webcam.set({
        width: 320,
        height: 240,
        image_format: 'jpeg',
        jpeg_quality: 90
    });

    let currentPunchType = 'check-in';

    function setPunchType(type) {
        currentPunchType = type;
        Webcam.attach('#my_camera');
    }

    function stopCamera() {
        Webcam.reset();
    }

    function captureAndPunch() {
        Webcam.snap(function(data_uri) {
            let url = currentPunchType === 'check-in' ? "{{ route('admin.hr.clock.check_in') }}" : "{{ route('admin.hr.clock.check_out') }}";
            let payload = {
                _token: "{{ csrf_token() }}",
                image_data: data_uri,
                attendance_id: "{{ $attendance->id ?? '' }}",
                work_from: 'office', // Default for camera punch
                comment: 'Camera Punch'
            };

            $.post(url, payload, function(res) {
                if(res.status == 1) {
                    toastr.success(res.message);
                    setTimeout(() => location.reload(), 1000);
                } else {
                    toastr.error(res.message);
                }
            });
        });
    }

    // Reusable function for AJAX requests (Legacy Style)
    function handleAjaxRequest(buttonId, route, formId) {
        let isProcessing = false;
        $(document).on('click', buttonId, function(e) {
            e.preventDefault();
            if (isProcessing) return;
            isProcessing = true;
            
            $.ajax({
                type: 'POST',
                url: route,
                data: $(formId).serialize(),
                success: function(response) {
                    if (response.status == 1) {
                        toastr.success(response.message);
                        setTimeout(() => location.reload(), 1000);
                    } else {
                        toastr.error(response.message);
                    }
                    isProcessing = false;
                },
                error: function() {
                    isProcessing = false;
                    toastr.error('Something went wrong. Please try again.');
                }
            });
        });
    }

    handleAjaxRequest("#checkBtn", "{{ route('admin.hr.clock.check_in') }}", '#checkInForm');
    handleAjaxRequest("#breakBtn", "{{ route('admin.hr.clock.start_break') }}", '#breakForm');
    handleAjaxRequest("#endBreakBtn", "{{ route('admin.hr.clock.end_lunchBreak') }}", '#endLunchForm');
    handleAjaxRequest("#logoutBtn", "{{ route('admin.hr.clock.check_out') }}", '#endDayForm');

    // Real-time Digital Clock
    setInterval(() => {
        const now = new Date();
        const timeStr = now.getHours().toString().padStart(2, '0') + ":" + 
                        now.getMinutes().toString().padStart(2, '0') + ":" + 
                        now.getSeconds().toString().padStart(2, '0');
        $('#digital_clock').text(timeStr);
    }, 1000);

    // Worked Time Ticker
    @if($attendance)
        @php
            $startTime = strtotime($attendance->check_in);
        @endphp
        let startTime = {{ $startTime }} * 1000;
        
        setInterval(() => {
            let now = new Date().getTime();
            let diff = now - startTime;
            
            let h = Math.floor(diff / (1000 * 60 * 60));
            let m = Math.floor((diff % (1000 * 60 * 60)) / (1000 * 60));
            let s = Math.floor((diff % (1000 * 60)) / 1000);
            
            let formatted = h.toString().padStart(2, '0') + ":" + 
                            m.toString().padStart(2, '0') + ":" + 
                            s.toString().padStart(2, '0');
            $('#ticker').text(formatted);
        }, 1000);
    @endif

    function manageBreak(action) {
        if(action === 'start') {
            Swal.fire({
                title: 'Start Break',
                input: 'select',
                inputOptions: {
                    'lunch': 'Lunch',
                    'tea': 'Tea Break',
                    'other': 'Other'
                },
                inputPlaceholder: 'Select break type',
                showCancelButton: true
            }).then((result) => {
                if(result.isConfirmed) {
                    $.post("{{ route('admin.hr.clock.start_break') }}", {
                        _token: "{{ csrf_token() }}",
                        attendance_id: "{{ $attendance->id ?? '' }}",
                        break_for: result.value
                    }, function(res) {
                        if(res.status == 1) location.reload();
                        else Swal.fire('Error', res.message, 'error');
                    });
                }
            });
        } else {
            $.post("{{ route('admin.hr.clock.end_break') }}", {
                _token: "{{ csrf_token() }}",
                attendance_id: "{{ $attendance->id ?? '' }}",
                break_id: "{{ $activeBreak->id ?? '' }}",
                lunch_was: 'good',
                reason: 'Work resumption'
            }, function(res) {
                if(res.status == 1) location.reload();
                else Swal.fire('Error', res.message, 'error');
            });
        }
    }
</script>
@endpush
@endsection

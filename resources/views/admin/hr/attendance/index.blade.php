@extends('admin.layouts.master')

@section('title', 'Attendance Tracking')

@push('css')
<style>
    .attendance-table th, .attendance-table td { padding: 0.5rem; font-size: 0.75rem; vertical-align: middle; min-width: 40px; }
    .attendance-table th:first-child, .attendance-table td:first-child { position: sticky; left: 0; background: white; z-index: 10; min-width: 180px; box-shadow: 2px 0 5px rgba(0,0,0,0.05); }
    .status-icon { cursor: pointer; transition: transform 0.2s; }
    .status-icon:hover { transform: scale(1.2); }
    .legend-item { font-size: 0.8rem; display: inline-flex; align-items: center; margin-right: 1rem; }
</style>
@endpush

@section('content')
<div class="container-fluid">
    {{-- Search/Filter Card --}}
    <div class="card shadow-sm border-0 rounded-4 mb-4">
        <div class="card-body">
            <form action="{{ route('admin.hr.attendance.index') }}" method="GET" class="row g-3 align-items-end">
                @if (auth()->user()->role == 'superadmin' || auth()->user()->role == 'admin')
                <div class="col-lg-3">
                    <label class="form-label small fw-bold text-muted">Staff Member</label>
                    <select name="staff_id" class="form-select rounded-3">
                        <option value="all">All Employees</option>
                        @foreach ($staffAll as $staff)
                            <option value="{{ $staff->id }}" {{ request('staff_id') == $staff->id ? 'selected' : '' }}>
                                {{ $staff->name }} ({{ $staff->designation->name ?? 'N/A' }})
                            </option>
                        @endforeach
                    </select>
                </div>
                @endif
                <div class="col-lg-2">
                    <label class="form-label small fw-bold text-muted">Year</label>
                    <select name="year" class="form-select rounded-3">
                        @php $searchYear = date('Y'); @endphp
                        @for ($y = $searchYear; $y >= 2020; $y--)
                            <option value="{{ $y }}" {{ request('year', $currentYear) == $y ? 'selected' : '' }}>{{ $y }}</option>
                        @endfor
                    </select>
                </div>
                <div class="col-lg-2">
                    <label class="form-label small fw-bold text-muted">Month</label>
                    <select name="month" class="form-select rounded-3">
                        @for ($m = 1; $m <= 12; $m++)
                            <option value="{{ str_pad($m, 2, '0', STR_PAD_LEFT) }}" 
                                    {{ request('month', $month) == str_pad($m, 2, '0', STR_PAD_LEFT) ? 'selected' : '' }}>
                                {{ date('F', mktime(0, 0, 0, $m, 1)) }}
                            </option>
                        @endfor
                    </select>
                </div>
                <div class="col-lg-4 d-flex gap-2">
                    <button type="submit" class="btn btn-primary rounded-pill px-4 flex-grow-1">Search</button>
                    <a href="{{ route('admin.hr.attendance.index') }}" class="btn btn-light rounded-pill px-4 border">Reset</a>
                </div>
            </form>
        </div>
    </div>

    {{-- Main Attendance Sheet --}}
    <div class="card shadow-sm border-0 rounded-4">
        <div class="card-header bg-white py-3 border-0">
            <div class="d-flex justify-content-between align-items-center mb-2">
                <h6 class="m-0 fw-bold text-primary">
                    Attendance Registry: {{ date('F Y', mktime(0,0,0, $month, 1, $currentYear)) }}
                </h6>
                @if($hours !== "")
                <span class="badge bg-primary-soft text-primary rounded-pill px-3 py-2 fw-semibold">
                    Total: {{ $hours }}h {{ $minutes }}m
                </span>
                @endif
            </div>
            
            <div class="bg-light p-2 rounded-3 d-flex flex-wrap gap-3">
                <div class="legend-item"><i class="fa fa-check text-success me-1"></i> Present</div>
                <div class="legend-item"><i class="fa fa-exclamation-circle text-warning me-1"></i> Late</div>
                <div class="legend-item"><i class="fa fa-star-half-alt text-danger me-1"></i> Missed Out</div>
                <div class="legend-item"><i class="fa fa-calendar-week text-info me-1"></i> Day Off</div>
                <div class="legend-item"><i class="fa fa-plane-departure text-purple me-1"></i> On Leave</div>
                <div class="legend-item"><i class="fa fa-times text-muted me-1"></i> Absent</div>
            </div>
        </div>
        
        <div class="card-body p-0">
            <div class="table-responsive text-nowrap">
                <table class="table table-bordered attendance-table mb-0">
                    <thead class="bg-light">
                        <tr>
                            <th class="border-start-0">Employee</th>
                            @for ($day = 1; $day <= $daysInMonth; $day++)
                                <th class="text-center">
                                    {{ str_pad($day, 2, '0', STR_PAD_LEFT) }}<br>
                                    <small class="text-muted text-uppercase fw-normal">{{ date('D', mktime(0,0,0, $month, $day, $currentYear)) }}</small>
                                </th>
                            @endfor
                            <th class="text-center">Stats</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($employees as $employee)
                        <tr>
                            <td class="border-start-0">
                                <div class="d-flex align-items-center">
                                    <div class="avatar-sm me-2">
                                        <img src="{{ asset($employee->profile_image ?? 'admin/img/pp.jpg') }}" class="rounded-circle" width="30" height="30" style="object-fit: cover;">
                                    </div>
                                    <div>
                                        <div class="fw-bold small">{{ $employee->name }}</div>
                                        <div class="text-muted" style="font-size: 0.65rem;">{{ $employee->designation->name ?? 'N/A' }}</div>
                                    </div>
                                </div>
                            </td>
                            @for ($day = 1; $day <= $daysInMonth; $day++)
                                @php
                                    $currDate = \Carbon\Carbon::create($currentYear, $month, $day)->format('Y-m-d');
                                    $dayNameLower = strtolower(date('l', mktime(0,0,0, $month, $day, $currentYear)));
                                    
                                    $att = $employee->attendances->firstWhere('date', $currDate);
                                    
                                    $workingDays = explode(',', strtolower($employee->working_days));
                                    $isDayOff = !in_array($dayNameLower, $workingDays);
                                    
                                    $onLeave = $leaves->first(function($l) use ($currDate, $employee) {
                                        return $l->staff_id == $employee->id && $l->date_from <= $currDate && $l->date_till >= $currDate;
                                    });

                                    $shopTime = GlobalSetting('shop_time') ?? "10:00";
                                    $lateThreshold = \Carbon\Carbon::createFromFormat('H:i', $shopTime)->addMinutes(15);
                                @endphp
                                <td class="text-center p-1">
                                    @if ($att)
                                        <span class="status-icon attendance-details" 
                                              data-staff-id="{{ $employee->id }}" 
                                              data-date="{{ $currDate }}"
                                              title="Check-in: {{ date('H:i', strtotime($att->check_in)) }}">
                                            @if (!$att->check_out)
                                                <i class="fa fa-star-half-alt text-danger"></i>
                                            @else
                                                @php $isLate = \Carbon\Carbon::parse($att->check_in)->gt($lateThreshold); @endphp
                                                @if ($isLate)
                                                    <i class="fa fa-exclamation-circle text-warning"></i>
                                                @else
                                                    <i class="fa fa-check text-success"></i>
                                                @endif
                                                <div style="font-size: 0.6rem;" class="text-muted mt-n1">
                                                    @if($att->duration)
                                                        {{ floor($att->duration/60) }}.{{ $att->duration%60 }}h
                                                    @endif
                                                </div>
                                            @endif
                                        </span>
                                    @elseif ($onLeave)
                                        <i class="fa fa-plane-departure text-purple" title="Approved Leave"></i>
                                    @elseif ($isDayOff)
                                        <i class="fa fa-calendar-week text-info opacity-50" title="Weekly Off"></i>
                                    @else
                                        <i class="fa fa-times text-muted opacity-25" title="Absent"></i>
                                    @endif
                                </td>
                            @endfor
                            <td class="text-center fw-bold text-primary small">
                                @php $presentCount = $employee->attendances->where('date', '>=', "$currentYear-$month-01")->where('date', '<=', "$currentYear-$month-$daysInMonth")->count(); @endphp
                                {{ $presentCount }}/{{ $daysInMonth }}
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

{{-- Attendance Day Details Modal --}}
<div class="modal fade" id="attendanceModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content rounded-4 border-0 shadow">
            <div class="modal-header border-0 pb-0">
                <h5 class="modal-title fw-bold">Daily logs</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body py-4">
                <div id="modal-content-placeholder" class="text-center">
                    <div class="spinner-border text-primary" role="status"></div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('js')
<script>
    $(document).ready(function() {
        $('.attendance-details').on('click', function(e) {
            e.preventDefault();
            var staffId = $(this).data('staff-id');
            var date = $(this).data('date');
            
            $('#modal-content-placeholder').html('<div class="spinner-border text-primary" role="status"></div>');
            var modal = new bootstrap.Modal(document.getElementById('attendanceModal'));
            modal.show();
            
            $.ajax({
                url: "{{ route('admin.hr.attendance.details') }}",
                type: 'POST',
                data: {
                    _token: "{{ csrf_token() }}",
                    employee_id: staffId,
                    date: date
                },
                success: function(response) {
                    $('#modal-content-placeholder').html(response);
                },
                error: function() {
                    $('#modal-content-placeholder').html('<div class="text-danger small">Error loading details.</div>');
                }
            });
        });
    });
</script>
@endpush

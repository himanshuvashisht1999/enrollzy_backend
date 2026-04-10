@extends('layouts.app')
@section('push_css')
@endsection
@section('content')
    <div class="container-fluid">
        <!-- Content Row -->
        <div class="card shadow mb-4">
            <div class="card-body">
                <form action="" class="row">
                    @if (Auth::guard('admin')->user()->role == 'superadmin')
                        <div class="col-lg-3 form-group">
                            <select name="staff_id" id="" class="form-control">
                                <option value="">Select Staff</option>
                                @foreach ($staffAll as $staff)
                                    <option {{ request('staff_id') == $staff->id ? 'selected' : '' }}
                                        value="{{ $staff->id }}">
                                        {{ $staff->name }} -
                                        {{ $staff->designation == null ? '' : $staff->designation->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                    @endif
                    <div class="col-lg-3 form-group">
                        <select name="year" id="" class="form-control">
                            <option value="">Select Year</option>
                            @php
                                $searchYear = \Carbon\Carbon::now()->year;
                            @endphp
                            @for ($sYear = $searchYear; $sYear >= 2015; $sYear--)
                                <option value="{{ $sYear }}" {{ request('year') == $sYear ? 'selected' : '' }}>
                                    {{ $sYear }}</option>
                            @endfor

                        </select>
                        </select>
                    </div>
                    <div class="col-lg-3 form-group">
                        <select name="month" id="" class="form-control">
                            <option value="">Select Month</option>
                            @for ($sMonth = 1; $sMonth <= 12; $sMonth++)
                                @php
                                    $searchMonth = \Carbon\Carbon::createFromFormat('n', $sMonth)->format('F');
                                @endphp
                                <option value="{{ str_pad($sMonth, 2, '0', STR_PAD_LEFT) }}"
                                    {{ request('month') == str_pad($sMonth, 2, '0', STR_PAD_LEFT) ? 'selected' : '' }}>
                                    {{ $searchMonth }}
                                </option>
                            @endfor
                        </select>
                    </div>
                    <div class="col-lg-3 form-group text-center " style="align-content: center">
                        <button type="submit" class="btn btn-success">Search</button>
                        <a href="{{ route('admin.attendance.index') }}" class="btn btn-secondary">Reset</a>
                    </div>
                </form>
            </div>
        </div>
        @php


        @endphp
        <div class="card shadow mb-4">
            <div class="card-header py-3 d-flex justify-content-between">
                <h6 class="m-0 font-weight-bold text-primary">
                    {{ \Carbon\Carbon::createFromFormat('m', $month)->format('F') }} | Staff Attendance</h6>
                <div class="row">
                    <div class="col-md-12">
                        <span class="f-w-500 mr-1">Note:</span>
                        <i class="fa fa-star text-warning"></i>
                        <i class="fa fa-arrow-right text-lightest f-11 mx-1"></i> Holiday &nbsp;|&nbsp;<i
                            class="fa fa-calendar-week text-red"></i> <i
                            class="fa fa-arrow-right text-lightest f-11 mx-1"></i>
                        Day Off &nbsp;|
                        &nbsp;<i class="fa fa-check text-success"></i> <i
                            class="fa fa-arrow-right text-lightest f-11 mx-1"></i>
                        Present &nbsp;|&nbsp; <i class="fa fa-star-half-alt text-red"></i> <i
                            class="fa fa-arrow-right text-lightest f-11 mx-1"></i>
                        Half Day &nbsp;|&nbsp; <i class="fa fa-exclamation-circle text-warning"></i> <i
                            class="fa fa-arrow-right text-lightest f-11 mx-1"></i>
                        Late &nbsp;|&nbsp; <i class="fa fa-times text-lightest"></i> <i
                            class="fa fa-arrow-right text-lightest f-11 mx-1"></i>
                        Absent &nbsp;|&nbsp; <i class="fa fa-plane-departure text-danger"></i> <i
                            class="fa fa-arrow-right text-lightest f-11 mx-1"></i>
                        On Leave <br>
                        <span class="f-w-500 mr-1  text-primary">Total Working Hours : {{$hours}} Hours {{$minutes}} Minutes</span>
                    </div>
                </div>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-bordered">
                        <thead class="table-light">
                            <tr>
                                <th>Staff</th>
                                @for ($day = 1; $day <= $daysInMonth; $day++)
                                    @php
                                        $date = \Carbon\Carbon::createFromDate($currentYear, $month, $day);
                                        $dayName = $date->format('D'); // Get day name abbreviation (e.g., Mon, Tue)
                                        $formattedDate = $date->format('d'); // Get day of the month (e.g., 01, 02)
                                    @endphp
                                    <th>{{ $formattedDate }}<br><small class="text-muted">{{ $dayName }}</small>
                                    </th>
                                @endfor
                                <th>Total</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($employees as $employee)
                                <tr>
                                    <td>
                                        <div class="d-flex align-items-center">
                                            <img src="{{ $employee->photo }}" alt="Photo" class="rounded-circle me-2"
                                                width="40" height="40">
                                            <div>
                                                <div>{{ $employee->name }}</div>
                                                <div class="text-muted">{{ $employee->designation->name ?? 'not ass.' }}
                                                </div>
                                            </div>
                                        </div>
                                    </td>
                                    @for ($day = 1; $day <= $daysInMonth; $day++)
                                        @php
                                            $dateNCh = \Carbon\Carbon::createFromDate($currentYear, $month, $day);
                                            $dayCheck = strtolower($dateNCh->format('l')); // Get full day name (e.g., Sunday, Monday, etc.)
                                            $date =
                                                $currentYear .
                                                '-' .
                                                str_pad($month, 2, '0', STR_PAD_LEFT) .
                                                '-' .
                                                str_pad($day, 2, '0', STR_PAD_LEFT);
                                            $attendance = $employee->attendances->first(function ($att) use ($date) {
                                                return $att->date == $date;
                                            });
                                            $workingDays = explode(',', $employee->working_days); // Get the staff working days as an array
                                            $workingDays = array_map('strtolower', $workingDays);

                                            $leaveOnThisDay = $leaves->first(function ($leave) use ($date, $employee) {
                                                return $leave->staff_id == $employee->id &&
                                                    \Carbon\Carbon::parse($leave->date_from)->format('Y-m-d') <= $date &&
                                                    \Carbon\Carbon::parse($leave->date_till)->format('Y-m-d') >= $date;
                                            });
                                            $check_in_time = \Carbon\Carbon::parse($attendance->check_in ?? "");
                                            $check_out_time = \Carbon\Carbon::parse($attendance->check_out ?? "");
                                            $hoursDifference = $check_in_time->diffInHours($check_out_time);
                                            $minutesDifference = $check_in_time->diffInMinutes($check_out_time) % 60;


                                            if($attendance){
                                            $breaks = DB::table('breaks')->where('attendance_id',$attendance->id)->get();


                                            // Initialize total break time in minutes
$totalBreakTimeInMinutes = 0;

// Loop through each break and calculate its duration
foreach ($breaks as $break) {
    $break_start = \Carbon\Carbon::parse($break->start);
    $break_end = \Carbon\Carbon::parse($break->end);

    // Calculate break duration in minutes
    $totalBreakTimeInMinutes += $break_start->diffInMinutes($break_end);
}

// Calculate the net working time (subtract breaks from the total duration)
$totalMinutes = $check_in_time->diffInMinutes($check_out_time);
$netWorkingMinutes = $totalMinutes - $totalBreakTimeInMinutes;

// Calculate net working hours and minutes
$netWorkingHours = floor($netWorkingMinutes / 60);
$netWorkingMinutes = $netWorkingMinutes % 60;

$totalWorking =  $netWorkingHours.' :'.$netWorkingMinutes;
                                            }


                                        @endphp
                                        <td class="text-center">
                                            
                                            @if ($attendance)

                                                @php
                                                    // Get the shop open time from the global settings and convert it to a Carbon instance
                                                    $shopTime = GlobalSetting('shop_time'); // Shop open time, e.g., "10:00"
                                                    $shopOpen = \Carbon\Carbon::createFromFormat('H:i', $shopTime); // Carbon instance for shop open time
                                                    // Parse the check-in time from the attendance record
                                                    $checkInTime = \Carbon\Carbon::parse($attendance->check_in)->format(
                                                        'H:i',
                                                    ); // Extract time part only
                                                    // Calculate the "late" threshold time by adding 15 minutes to the shop open time
                                                    $lateThresholdTime = $shopOpen
                                                        ->copy()
                                                        ->addMinutes(15)
                                                        ->format('H:i'); // "Shop Open Time + 15 minutes"
                                                    // Compare the check-in time with the late threshold time to determine if the employee is late
                                                    $isLate = \Carbon\Carbon::parse($checkInTime)->gt(
                                                        \Carbon\Carbon::parse($lateThresholdTime),
                                                    ); // Check if check-in time is greater (late)
                                                @endphp
                                                <!-- Attendance details link -->
                                                <a href="javascript:;" class="attendance-details"
                                                    data-employee-id="{{ $employee->id }}" data-date="{{ $date }}"
                                                    data-toggle="modal" data-target="#attendanceModal">
                                                    @if ($attendance->check_out === null)
                                                        <i class="fa fa-star-half-alt text-red"></i>
                                                        <!-- Show when the employee hasn't checked out -->
                                                    @endif
                                                    @if ($attendance->duration !== null)
                                                        <!-- Show the corresponding status icon based on whether the employee is late or not -->
                                                        @if ($isLate)
                                                            <i class="fa fa-exclamation-circle text-warning"></i>
                                                            <!-- Late icon -->
                                                        @else
                                                            <i class="fa fa-check text-success"></i>
                                                            <!-- Early/On-time icon -->
                                                        @endif
                                                    @endif
                                                </a>
                                                @if($attendance->check_in && $attendance->check_out)
                                               {{$hoursDifference}}.{{$minutesDifference}}
                                               @endif
                                            @else
                                                @if (!in_array($dayCheck, $workingDays))
                                                    <i class="fa fa-calendar-week text-warning"></i>
                                                @else
                                                    @if ($leaveOnThisDay)
                                                        <i class="fa fa-plane-departure text-danger"></i>
                                                    @else
                                                        <i class="fa fa-times text-lightest"></i>
                                                    @endif
                                                @endif
                                            @endif
                                        </td>
                                    @endfor
                                    <td class="text-center">
                                        @php
                                            $uniqueAttendanceDays = $employee->attendances
                                                ->where('status', 'present')
                                                ->whereBetween('date', [
                                                    $currentYear . '-' . str_pad($month, 2, '0', STR_PAD_LEFT) . '-01',
                                                    $currentYear .
                                                    '-' .
                                                    str_pad($month, 2, '0', STR_PAD_LEFT) .
                                                    '-' .
                                                    str_pad($daysInMonth, 2, '0', STR_PAD_LEFT),
                                                ])
                                                ->groupBy(function ($att) {
                                                    return $att->date;
                                                })
                                                ->count();
                                        @endphp
                                        {{ $uniqueAttendanceDays }}/{{ $daysInMonth }}
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                <!-- Attendance Details Modal -->
                <div class="modal fade" id="attendanceModal" tabindex="-1" role="dialog" aria-labelledby="attendanceModal"
                    aria-hidden="true">
                    <div class="modal-dialog modal-lg" role="document">
                        <div class="modal-content">
                            <div class="modal-header">
                                <button class="close" type="button" data-dismiss="modal" aria-label="Close">
                                    <span aria-hidden="true">×</span>
                                </button>
                            </div>
                            <div class="modal-body">
                                <div id="modal-content-placeholder">
                                </div>
                            </div>
                            <div class="modal-footer">
                                <button class="btn btn-secondary" type="button" data-dismiss="modal">Cancel</button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
@section('push_script')
    <script>
        $(document).ready(function() {
            $('.attendance-details').on('click', function(e) {
                e.preventDefault();
                var employeeId = $(this).data('employee-id');
                var date = $(this).data('date');
                $.ajax({
                    url: "{{ route('admin.attendance.get_details') }}",
                    type: 'post',
                    data: {
                        _token: "{{ csrf_token() }}",
                        employee_id: employeeId,
                        date: date
                    },
                    success: function(response) {
                        $('#modal-content-placeholder').html(response);
                    }
                });
            });
        });
    </script>
@endsection

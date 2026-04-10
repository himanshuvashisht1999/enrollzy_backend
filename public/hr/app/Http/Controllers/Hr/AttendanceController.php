<?php

namespace App\Http\Controllers\Hr;

use Carbon\Carbon;
use App\Models\Admin;
use App\Models\Holiday;
use App\Models\Attendance;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Models\Leave;
use Illuminate\Support\Facades\Auth;


class AttendanceController extends Controller
{
    public function showAttendance(Request $request)
    {
        $currentYear = request()->input('year');
        $month = request()->input('month');
        $staff_id = request()->input('staff_id');
        if (is_null($currentYear) || $currentYear === '') {
            $currentYear = Carbon::now()->year;
        }
        if (is_null($month) || $month === '') {
            $month = Carbon::now()->month;
        }
        if (is_null($staff_id) || $staff_id === '') {
            $staff_id = 'all';
        }
        // Retrieve all employees with their related attendance data
        $query = Admin::with(['attendances', 'designation', 'department']);
        if (Auth::guard('admin')->user()->role == 'superadmin') {
            if (!empty($staff_id) && $staff_id !== 'all') {
                $query->where('id', $staff_id);
            }
        } elseif(Auth::guard('admin')->user()->role == 'admin'){
            $query->where('organization_id', Auth::guard('admin')->user()->organization_id);

        }else {
            $query->where('id', Auth::guard('admin')->id());
        }
        $employees = $query->where('status', 'active')->get();
        $daysInMonth = Carbon::createFromDate($currentYear, $month, 1)->daysInMonth;
        $staffAll = Admin::where('status','active')->get();
        $holiday = Holiday::get();
        $leaves = Leave::whereMonth('date_from', $month)
            ->whereYear('date_from', $currentYear)
            ->where('status', 'approved')
            ->get();
        $attendanceData = "";
        $hours = "";
        $minutes = "";
        if($request->staff_id){
            // $attendanceData = Attendance::with('breaks')->where('staff_id', $request->staff_id) ->where('date', $date)->orderBy('created_at', 'asc')->get();
            $attendanceData = Attendance::with('breaks')
            ->where('staff_id', $request->staff_id)
            ->whereYear('date', $request->year)
            ->whereMonth('date', $request->month)
            ->whereNotNull('check_in')
            ->whereNotNull('check_out')
            ->get();
            $totalAttendanceDuration = 0;
            $totalBreakDuration = 0;

            // Loop through each attendance record
            foreach ($attendanceData as $attendance) {
                // Calculate attendance duration (check_out - check_in)
                $checkIn = \Carbon\Carbon::parse($attendance->check_in);
                $checkOut = \Carbon\Carbon::parse($attendance->check_out);
                $attendanceDuration = $checkIn->diffInMinutes($checkOut);  // Duration in minutes
                $totalAttendanceDuration += $attendanceDuration;
            
                // Calculate total break duration for this attendance record
                foreach ($attendance->breaks as $break) {
                    $breakStart = \Carbon\Carbon::parse($break->start_time);
                    $breakEnd = \Carbon\Carbon::parse($break->end_time);
                    $breakDuration = $breakStart->diffInMinutes($breakEnd);  // Break duration in minutes
                    $totalBreakDuration += $breakDuration;
                }
            }
            
            // Calculate the final duration: total attendance minus total break time
            $totalDuration = $totalAttendanceDuration - $totalBreakDuration;
            
            // Optionally, you can convert it into hours and minutes
            $hours = floor($totalDuration / 60);
            $minutes = $totalDuration % 60;
            
        }
        return view('hr.attendance.index', compact('leaves', 'employees', 'daysInMonth', 'month', 'currentYear', 'staffAll', 'hours','minutes'));
    }

    public function getAtDetailsForDay(Request $request)
    {
        $employeeId = $request->get('employee_id');
        $date = $request->get('date');
        // Fetch attendance details for the date
        $attendance = Attendance::with('breaks')
            ->where('staff_id', $employeeId)
            ->where('date', $date)
            ->orderBy('created_at', 'asc')
            ->get();
        $totalBreak = $attendance->reduce(function ($carry, $attendance) {
            return $carry + $attendance->breaks->sum('duration');
        }, 0);
        $totalWork = $attendance->sum('duration');

        return view('hr.attendance.show', compact('attendance', 'totalWork', 'totalBreak'));
    }

    public function getAttandence(Request $request)
    {
        $employeeId = 2;
        $startDate = "2025-01-01";
        $endDate = "2025-01-31";

        $attendance = Attendance::with('breaks')
            ->where('staff_id', $employeeId)
            ->whereBetween('date', [$startDate, $endDate])  // Use whereBetween to filter dates
            ->orderBy('created_at', 'asc')
            ->get();

            $total_duration = 0;
            $total_break_duration = 0;

        foreach ($attendance as $entry) {
            if ($entry['duration'] !== null) {
                $total_duration += $entry['duration'];
                foreach($entry['breaks'] as $brake){

                $total_break_duration += $brake['duration'];
                }
                $total_duration = $total_duration - $total_break_duration;
            }
        }
        dd($total_duration);

        $hours = floor($total_duration / 60);
        $minutes = $total_duration % 60;
        $seconds = 0; 
        return  $hours.'.'.$minutes;
    }
}

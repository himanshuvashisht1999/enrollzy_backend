<?php

namespace App\Http\Controllers\Admin\Hr;

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
        $currentYear = $request->input('year', Carbon::now()->year);
        $month = $request->input('month', Carbon::now()->month);
        $staff_id = $request->input('staff_id', 'all');

        $query = Admin::with(['attendances', 'designation', 'department']);
        
        $user = auth()->user();
        if ($user->role == 'superadmin') {
            if ($staff_id !== 'all') {
                $query->where('id', $staff_id);
            }
        } elseif ($user->role == 'admin') {
            $query->where('organization_id', $user->organization_id);
            if ($staff_id !== 'all') {
                $query->where('id', $staff_id);
            }
        } else {
            $query->where('id', $user->id);
        }

        $employees = $query->where('status', 'active')->get();
        $daysInMonth = Carbon::createFromDate($currentYear, $month, 1)->daysInMonth;
        
        $staffAllQuery = Admin::where('status', 'active');
        if ($user->role == 'admin') {
            $staffAllQuery->where('organization_id', $user->organization_id);
        }
        $staffAll = $staffAllQuery->get();

        $holiday = Holiday::whereMonth('date', $month)->whereYear('date', $currentYear)->get();
        $leaves = Leave::whereMonth('date_from', $month)
            ->whereYear('date_from', $currentYear)
            ->where('status', 'approved')
            ->get();

        $hours = "";
        $minutes = "";
        
        if ($request->filled('staff_id') && $request->staff_id !== 'all') {
            $attendanceData = Attendance::with('breaks')
                ->where('staff_id', $request->staff_id)
                ->whereYear('date', $currentYear)
                ->whereMonth('date', $month)
                ->whereNotNull('check_in')
                ->whereNotNull('check_out')
                ->get();

            $totalAttendanceDuration = 0;
            $totalBreakDuration = 0;

            foreach ($attendanceData as $attendance) {
                $checkIn = Carbon::parse($attendance->check_in);
                $checkOut = Carbon::parse($attendance->check_out);
                $totalAttendanceDuration += $checkIn->diffInMinutes($checkOut);
            
                foreach ($attendance->breaks as $break) {
                    if ($break->start && $break->end) {
                        $breakStart = Carbon::parse($break->start);
                        $breakEnd = Carbon::parse($break->end);
                        $breakDuration = $breakStart->diffInMinutes($breakEnd);
                        
                        $totalBreakDuration += $breakDuration;
                    }
                }
            }
            
            $totalDuration = $totalAttendanceDuration - $totalBreakDuration;
            $hours = floor($totalDuration / 60);
            $minutes = $totalDuration % 60;
        }

        return view('admin.hr.attendance.index', compact('leaves', 'employees', 'daysInMonth', 'month', 'currentYear', 'staffAll', 'hours', 'minutes'));
    }

    public function getAtDetailsForDay(Request $request)
    {
        $employeeId = $request->get('employee_id');
        $date = $request->get('date');
        
        $attendance = Attendance::with('breaks')
            ->where('staff_id', $employeeId)
            ->where('date', $date)
            ->orderBy('created_at', 'asc')
            ->get();

        $totalBreak = $attendance->reduce(function ($carry, $attendance) {
            return $carry + $attendance->breaks->sum('duration');
        }, 0);

        $unpaidBreak = $attendance->reduce(function ($carry, $attendance) {
            return $carry + $attendance->breaks->sum(function ($break) {
                return $break->duration;
            });
        }, 0);

        $totalWork = $attendance->sum('duration') - $unpaidBreak;
        if ($totalWork < 0) {
            $totalWork = 0;
        }

        return view('admin.hr.attendance.show', compact('attendance', 'totalWork', 'totalBreak'));
    }
}

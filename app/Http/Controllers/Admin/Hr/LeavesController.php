<?php

namespace App\Http\Controllers\Admin\Hr;

use Exception;
use App\Models\Leave;
use App\Models\Admin;
use App\Models\EmployeeTxn;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Models\LeaveSetting;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class LeavesController extends Controller
{
    public function index(Request $request)
    {
        $user = auth()->user();
        if ($user->is_admin && !isset($user->organization_id)) {
            $leave = Leave::orderBy('created_at', 'desc')->get();
            $policies = DB::table('leave_policies')->get();
        } else {
            $leave = Leave::where('organization_id', $user->organization_id)->orderBy('created_at', 'desc')->get();
            
            $staffId = $user->id;
            $departmentId = $user->department_id ?? null;
            $designationId = $user->designation_id ?? null;

            if ($user->role === 'admin') {
                $policies = DB::table('leave_policies')->where('organization_id', $user->organization_id)->get();
            } else {
                $policies = DB::table('leave_policies')
                    ->where('organization_id', $user->organization_id)
                    ->where(function($query) use ($staffId, $departmentId, $designationId) {
                        if ($departmentId && $designationId) {
                            $query->where(function($q) use ($staffId, $departmentId, $designationId) {
                                $q->whereRaw('FIND_IN_SET(?, staff_ids)', [$staffId])
                                  ->whereRaw('FIND_IN_SET(?, department_ids)', [$departmentId])
                                  ->whereRaw('FIND_IN_SET(?, designation_ids)', [$designationId]);
                            });
                        }
                        if ($departmentId) {
                            $query->orWhereRaw('FIND_IN_SET(?, department_ids)', [$departmentId]);
                        }
                        if ($designationId) {
                            $query->orWhereRaw('FIND_IN_SET(?, designation_ids)', [$designationId]);
                        }
                        $query->orWhereRaw('FIND_IN_SET(?, staff_ids)', [$staffId]);
                    })
                    ->get();
            }
        }

        $leavePolicyText = GlobalSetting('leave_policy') ?? 'No policy now';
        return view('admin.hr.leaves.index', compact('leave', 'leavePolicyText', 'policies'));
    }

    public function create()
    {
        $user = auth()->user();
        if ($user->is_admin && !isset($user->organization_id)) {
            $leaveSetting = LeaveSetting::get();
            $policies = DB::table('leave_policies')->get();
        } else {
            $leaveSetting = LeaveSetting::where('organization_id', $user->organization_id)->get();
            
            $staffId = $user->id;
            $departmentId = $user->department_id ?? null;
            $designationId = $user->designation_id ?? null;

            if ($user->role === 'admin') {
                $policies = DB::table('leave_policies')->where('organization_id', $user->organization_id)->get();
            } else {
                $policies = DB::table('leave_policies')
                    ->where('organization_id', $user->organization_id)
                    ->where(function($query) use ($staffId, $departmentId, $designationId) {
                        if ($departmentId) $query->orWhereRaw('FIND_IN_SET(?, department_ids)', [$departmentId]);
                        if ($designationId) $query->orWhereRaw('FIND_IN_SET(?, designation_ids)', [$designationId]);
                        $query->orWhereRaw('FIND_IN_SET(?, staff_ids)', [$staffId]);
                    })
                    ->get();
            }
        }

        $leavePolicyText = GlobalSetting('leave_policy') ?? 'No policy now';
        return view('admin.hr.leaves.create', compact('leavePolicyText', 'leaveSetting', 'policies'));
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'subject' => 'required',
            'date_from' => 'required',
            'date_till' => 'required',
            'return_date' => 'required',
            'content' => 'required',
        ]);

        if ($validator->fails()) {
            return redirect()->back()->with('error', $validator->errors()->first())->withInput();
        }

        $dateFrom = $request->date_from;
        $dateTill = $request->date_till;
        $user = auth()->user();
        
        // Overlap check
        $leaves = Leave::join('admin', 'leaves.staff_id', '=', 'admin.id')
            ->where(function($query) use ($dateFrom, $dateTill) {
                $query->where('leaves.date_from', '<=', $dateTill)
                      ->where('leaves.date_till', '>=', $dateFrom);
            })
            ->where('admin.department_id', $user->department_id ?? 0)
            ->where('admin.designation_id', $user->designation_id ?? 0)
            ->select('leaves.*', 'admin.name')
            ->get();

        if ($leaves->isNotEmpty()) {
            return redirect()->back()->with('error', 'Some employees already have leaves on these dates.');
        }

        $filePaths = [];
        if ($request->hasFile('files')) {
            foreach ($request->file('files') as $file) {
                $fileName = time() . '-' . str_replace(' ', '_', $file->getClientOriginalName());
                $destinationPath = public_path('uploads/hr/leaves');
                if (!file_exists($destinationPath)) {
                    mkdir($destinationPath, 0777, true);
                }
                $file->move($destinationPath, $fileName);
                $filePaths[] = 'uploads/hr/leaves/' . $fileName;
            }
        }
        $filePathsString = implode(',', $filePaths);

        try {
            Leave::create([
                'staff_id' => auth()->id(),
                'subject' => $request->subject,
                'leave_type_id' => $request->leave_type_id,
                'date_from' => $request->date_from,
                'date_till' => $request->date_till,
                'return_date' => $request->return_date,
                'content' => $request->content,
                'status' => 'pending',
                'files' => $filePathsString,
                'apply_date' => date('Y-m-d'),
                'organization_id' => $user->organization_id,
            ]);
            return redirect(route('admin.hr.leaves.index'))->with('success', 'Leave applied successfully.');
        } catch (Exception $e) {
            return redirect()->back()->with('error', 'Something went wrong: ' . $e->getMessage())->withInput();
        }
    }

    public function edit($id)
    {
        $leaveId = decrypt($id);
        try {
            $leave = Leave::find($leaveId);
            $admindata = Admin::find($leave->staff_id);
            if (!$admindata) {
                // Fallback to User if not found in Admin
                $admindata = \App\Models\User::find($leave->staff_id);
            }
            
            $leaveSettings = LeaveSetting::where('department_ids', 'LIKE', '%' . ($admindata->department_id ?? -1) . '%')
                ->where('designation_ids', 'LIKE', '%' . ($admindata->designation_id ?? -1) . '%')
                ->first();
                
            $logData = $leave->log ? json_decode($leave->log, true) : [];
            $leavePolicyText = GlobalSetting('leave_policy') ?? 'No policy now';

            $dateFrom = Carbon::parse($leave->date_from);
            $dateTill = Carbon::parse($leave->date_till);
            $daysCount = $dateFrom->diffInDays($dateTill) + 1;

            if ($leaveSettings && $daysCount > $leaveSettings->monthly_leave) {
                $penaltyDays = $daysCount - $leaveSettings->monthly_leave;
                $penalty = $penaltyDays * $leaveSettings->penalty;
            } else {
                $penalty = '';
            }
            return view('admin.hr.leaves.edit', compact('leave', 'leavePolicyText', 'logData', 'penalty'));
        } catch (Exception $e) {
            return redirect()->back()->with('error', 'Something went wrong: ' . $e->getMessage())->withInput();
        }
    }

    public function update(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'status' => 'required',
            'log' => 'required',
        ]);
        if ($validator->fails()) {
            return redirect()->back()->with('error', $validator->errors()->first())->withInput();
        }
        $leaveId = decrypt($id);
        try {
            $exLeave = Leave::findOrFail($leaveId);
            if ($request->status == 'unapprove') {
                if ($request->fine == null || !is_numeric($request->fine)) {
                    return redirect()->back()->with('error', 'Unapproved leave requires a fine (minimum 0).');
                } else {
                    EmployeeTxn::create([
                        'employee_id' => $exLeave->staff_id,
                        'debit' => 0,
                        'credit' => $request->fine,
                        'debit_account' => 'leave_penalty',
                        'payment_method' => 'leave_penalty',
                        'bank_charges' => 0,
                        'clearance_date' => date('Y-m-d'),
                        'initiation_date' => date('Y-m-d'),
                        'transaction_for' => 'penalty',
                        'log' => 'Admin deducted money from staff salary as penalty for unapproved leave',
                        'comment' => 'Admin deducted money from staff salary as penalty for unapproved leave',
                        'txn_id' => 'leave_' . $exLeave->id,
                        'staff_id' => auth()->id(),
                    ]);
                }
            }
            $currentLogs = $exLeave->log ? json_decode($exLeave->log, true) : [];
            $newLogEntry = [
                'admin_id' => auth()->id(),
                'status' => $request->status,
                'admin_message' => $request->log,
                'fine' => $request->fine ?? 0,
                'timestamp' => now(),
            ];
            $updatedLogs = array_merge($currentLogs, [$newLogEntry]);
            $exLeave->update([
                'admin_id' => auth()->id(),
                'status' => $request->status,
                'admin_message' => $request->log,
                'fine' => $request->fine ?? 0,
                'log' => json_encode($updatedLogs),
            ]);
            return redirect(route('admin.hr.leaves.index'))->with('success', 'Leave status updated successfully.');
        } catch (Exception $e) {
            return redirect()->back()->with('error', 'Something went wrong: ' . $e->getMessage())->withInput();
        }
    }
}

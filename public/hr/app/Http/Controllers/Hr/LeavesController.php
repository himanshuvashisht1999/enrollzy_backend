<?php

namespace App\Http\Controllers\Hr;

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
use DB;

class LeavesController extends Controller
{
    public function index(Request $request)
    {
        if(Auth::guard('admin')->user()->role === 'superadmin'){
            $leave = Leave::orderBy('created_at', 'desc')->get();
        }else{
            $leave = Leave::where('organization_id', Auth::guard('admin')->user()->organization_id)->orderBy('created_at', 'desc')->get();
        }
        $user = auth()->user(); // Get the logged-in user
        $staffId = $user->id;
        $departmentId = $user->department_id;
        $designationId = $user->designation_id;

        // Retrieve the policy data

        if(Auth::guard('admin')->user()->role === 'admin' || Auth::guard('admin')->user()->role === 'superadmin'){
            $policies = DB::table('leave_policies')->where('organization_id', Auth::guard('admin')->user()->organization_id)->get();
        }else{
            $policies = DB::table('leave_policies') // assuming your table is named 'leave_policies'
            ->where(function($query) use ($staffId, $departmentId, $designationId) {
                // First condition: Match all three fields
                $query->whereRaw('FIND_IN_SET(?, staff_ids)', [$staffId])
                    ->whereRaw('FIND_IN_SET(?, department_ids)', [$departmentId])
                    ->whereRaw('FIND_IN_SET(?, designation_ids)', [$designationId]);
            })
            ->where(function($query) use ($departmentId, $designationId) {
                // First condition: Match all three fields
                $query->whereRaw('FIND_IN_SET(?, department_ids)', [$departmentId])
                    ->whereRaw('FIND_IN_SET(?, designation_ids)', [$designationId]);
            })
            ->orWhere(function($query) use ($departmentId) {
                // Second condition: Just match if the department_id exists, regardless of staff_id or designation_id
                $query->whereRaw('FIND_IN_SET(?, department_ids)', [$departmentId]);
            })
            ->orWhere(function($query) use ($designationId) {
                // Third condition: Just match if the designation_id exists, regardless of staff_id or department_id
                $query->whereRaw('FIND_IN_SET(?, designation_ids)', [$designationId]);
            })
            ->orWhere(function($query) use ($staffId) {
                // Third condition: Just match if the designation_id exists, regardless of staff_id or department_id
                $query->whereRaw('FIND_IN_SET(?, staff_ids)', [$staffId]);
            })
            ->get();
        }
        $leavePolicy = GlobalSetting('leave_policy') ??  'No policy now';
        return view('hr.leaves.index', compact('leave', 'leavePolicy','policies'));
    }

    public function create()
    {
        $leavePolicy = GlobalSetting('leave_policy') ??  'No policy now';

        if(Auth::guard('admin')->user()->role === 'superadmin'){
            $leaveSetting = LeaveSetting::get();
        }else{
            $leaveSetting = LeaveSetting::where('organization_id', Auth::guard('admin')->user()->organization_id)->get();
        }

        $user = auth()->user(); // Get the logged-in user
        $staffId = $user->id;
        $departmentId = $user->department_id;
        $designationId = $user->designation_id;

        
        // // Retrieve the policy data
        // $policies = DB::table('leave_policies') // assuming your table is named 'leave_policies'
        // ->whereRaw('FIND_IN_SET(?, staff_ids)', [$staffId]) // Check if the user ID is in staff_ids
        // ->whereRaw('FIND_IN_SET(?, department_ids)', [$departmentId]) // Check if the department ID is in department_ids
        // ->whereRaw('FIND_IN_SET(?, designation_ids)', [$designationId]) // Check if the designation ID is in designation_ids
        // ->get();

// Retrieve the policy data
        if(Auth::guard('admin')->user()->role === 'admin' || Auth::guard('admin')->user()->role === 'superadmin'){
            $policies = DB::table('leave_policies')->where('organization_id', Auth::guard('admin')->user()->organization_id)->get();
        }else{
$policies = DB::table('leave_policies') // assuming your table is named 'leave_policies'
->where(function($query) use ($staffId, $departmentId, $designationId) {
    // First condition: Match all three fields
    $query->whereRaw('FIND_IN_SET(?, staff_ids)', [$staffId])
          ->whereRaw('FIND_IN_SET(?, department_ids)', [$departmentId])
          ->whereRaw('FIND_IN_SET(?, designation_ids)', [$designationId]);
})
->where(function($query) use ($departmentId, $designationId) {
    // First condition: Match all three fields
    $query->whereRaw('FIND_IN_SET(?, department_ids)', [$departmentId])
          ->whereRaw('FIND_IN_SET(?, designation_ids)', [$designationId]);
})
->orWhere(function($query) use ($departmentId) {
    // Second condition: Just match if the department_id exists, regardless of staff_id or designation_id
    $query->whereRaw('FIND_IN_SET(?, department_ids)', [$departmentId]);
})
->orWhere(function($query) use ($designationId) {
    // Third condition: Just match if the designation_id exists, regardless of staff_id or department_id
    $query->whereRaw('FIND_IN_SET(?, designation_ids)', [$designationId]);
})
->orWhere(function($query) use ($staffId) {
    // Third condition: Just match if the designation_id exists, regardless of staff_id or department_id
    $query->whereRaw('FIND_IN_SET(?, staff_ids)', [$staffId]);
})
->get();
        }


        return view('hr.leaves.create', compact('leavePolicy', 'leaveSetting','policies'));
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
        $admin = Auth::guard('admin')->user();
        
        $leaves = Leave::join('admin', 'leaves.staff_id', '=', 'admin.id')
        ->where(function($query) use ($dateFrom, $dateTill) {
            // Check if the leave date range overlaps with the given range
            $query->where('leaves.date_from', '<=', $dateTill)
                ->where('leaves.date_till', '>=', $dateFrom);
        })
        ->where('admin.department_id', $admin->department_id)  // Filter by department_id
        ->where('admin.designation_id', $admin->designation_id) // Filter by designation_id
        ->select('leaves.*', 'admin.name', 'admin.department_id', 'admin.designation_id') // Select the desired columns
        ->get();

        // dd($leaves);

        if($leaves->isNotEmpty()){
            return redirect()->back()->with('error', 'Some Employees has already leaves on these dates.');
        }

        $filePaths = [];
        if ($request->hasFile('files')) {
            foreach ($request->file('files') as $file) {
                // Get the file name
                $fileName = $file->getClientOriginalName();
        
                // Define the public path where the files will be stored
                $destinationPath = public_path('leaves_file');
        
                // Ensure the directory exists
                if (!file_exists($destinationPath)) {
                    mkdir($destinationPath, 0777, true);
                }
        
                // Move the file to the public folder
                $file->move($destinationPath, $fileName);
        
                // Add the file path to the array
                $filePaths[] = 'leaves_file/' . $fileName;
            }
        }
        $filePathsString = implode(',', $filePaths);
        try {
            Leave::create([
                'staff_id' => Auth::guard('admin')->id(),
                'subject' => $request->subject,
                'leave_type_id' => $request->leave_type_id,
                'date_from' => $request->date_from,
                'date_till' => $request->date_till,
                'return_date' => $request->return_date,
                'content' => $request->content,
                'status' => 'pending',
                'files' => $filePathsString,
                'apply_date' => date('Y-m-d'),
                'organization_id' => Auth::guard('admin')->user()->organization_id,
            ]);
            return redirect(route('admin.leaves.index'))->with('success', 'Leave applied successfully.');
        } catch (Exception $e) {
            return redirect()->back()->with('error', 'something went wrong.  ' . $e->getMessage())->withInput();
        }
    }

    public function show($id)
    {
        // code here
    }

    public function edit($id)
    {
        $leaveId = decrypt($id);
        try {
            $leave = Leave::find($leaveId);
            $admindata = Admin::find($leave->staff_id);
            $leaveSettings = LeaveSetting::where('department_ids', $admindata->department_id)->where('designation_ids', $admindata->designation_id)->first();
            $logData = $currentLogs = $leave->log ? json_decode($leave->log, true) : [];
            $leavePolicy = GlobalSetting('leave_policy') ??  'No policy now';

            // Convert the date strings into Carbon instances
            $dateFrom = Carbon::parse($leave->date_from);
            $dateTill = Carbon::parse($leave->date_till);

            // Calculate the difference in days
            $daysCount = $dateFrom->diffInDays($dateTill) + 1;


            if($leaveSettings && $daysCount > $leaveSettings->monthly_leave	){

                $penaltyDays = $daysCount - $leaveSettings->monthly_leave;
                $penalty = $penaltyDays * $leaveSettings->penalty;
            }
            else{
                $penalty = '';
            }
            return view('hr.leaves.edit', compact('leave', 'leavePolicy', 'logData','penalty'));
        } catch (Exception $e) {
            return redirect()->back()->with('error', 'something went wrong.  ' . $e->getMessage())->withInput();
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
                    return redirect()->back()->with('error', 'Un Approve leave need Fine. minimum 0 ');
                } else {
                    EmployeeTxn::create([
                        'employee_id' => $exLeave->staff_id,
                        'debit' => 00,
                        'credit' => $request->fine,
                        'debit_account' => 'leave_penalty',
                        'payment_method' => 'leave_penalty',
                        'bank_charges' => 0,
                        'clearance_date' => date('Y-m-d'),
                        'initiation_date' => date('Y-m-d'),
                        'transaction_for' => 'penalty',
                        'log' => 'Admin Deduct money from staff`s salary as penalty for unApproved leave ',
                        'comment' => 'Admin Deduct money from staff`s salary as penalty for unApproved leave ',
                        'txn_id' => 'leave_' . $exLeave->id,
                        'staff_id' => Auth::guard('admin')->id(),
                    ]);
                }
            }
            $currentLogs = $exLeave->log ? json_decode($exLeave->log, true) : [];
            $newLogEntry = [
                'admin_id' => Auth::guard('admin')->id(),
                'status' => $request->status,
                'admin_message' => $request->log,
                'fine' => $request->fine ?? 0,
                'timestamp' => now(),
            ];
            $updatedLogs = array_merge($currentLogs, [$newLogEntry]);
            $exLeave->update([
                'admin_id' => Auth::guard('admin')->id(),
                'status' => $request->status,
                'admin_message' => $request->log,
                'fine' => $request->fine ?? 0,
                'log' => json_encode($updatedLogs),
            ]);
            return redirect(route('admin.leaves.index'))->with('success', 'Leave status updated successfully.');
        } catch (Exception $e) {
            return redirect()->back()->with('error', 'something went wrong.  ' . $e->getMessage())->withInput();
        }
    }

    public function destroy($id)
    {
        // code here
    }
}

<?php

namespace App\Http\Controllers\Hr;

use Exception;
use Carbon\Carbon;
use App\Models\Admin;
use App\Models\Department;
use App\Models\Organization;
use App\Models\OtpEntries;
use App\Models\Designation;
use App\Models\EmailEntries;
use Illuminate\Http\Request;
use App\Mail\VerifyUserEmail;
use Yajra\DataTables\DataTables;
use Spatie\Permission\Models\Role;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Auth;

class StaffController extends Controller
{
    public function index(Request $request)
    {
        if ($request->ajax()) {
            //$data = Admin::where('role', 'staff')->get();


            $user = auth()->user();
            if ($user->isSuperAdmin()) {
                // $data = Admin::get();
                $query = Admin::query();
            } else {
                //$data = Admin::where('organization_id', $user->organization_id)->get();
                $query = Admin::where('organization_id', $user->organization_id);
            }

            if ($request->filled('role')) {
                $role = $request->role;
                if($role == 'organisation'){
                    $query->where('organization_id','!=',NULL);
                }else{
                    $query->where('role',$role);
                }
            }
            if ($request->filled('organization_id')) {
                $organization_id = $request->organization_id;
                $query->where('organization_id',$organization_id);
            }

            $data = $query->get();
            
            return DataTables::of($data)->addIndexColumn()
                ->addColumn('name', function ($row) {
                    $name = '<p class="text-sm font-weight-bold mb-0">' . $row->name . '</p>';
                    return $name;
                })
                ->addColumn('roles', function ($row) {


                    if(auth()->user()->role == "superadmin"){
                        $roles = Role::pluck('name', 'id')->all();
                    }else{
                        $roles = Role::whereNot('name', 'superadmin')->pluck('name', 'id')->all();
                    }
                    $selectedRole = $row->roles->pluck('name')->first();
                    $select = '<select class="form-control text-capitalize role-change" data-user-id="' . $row->id . '">';
                    $select .= '<option value="">Select Role</option>';
                    foreach ($roles as $roleId => $roleName) {
                        $selected = ($selectedRole == $roleName) ? 'selected disabled' : '';
                        $select .= '<option value="' . $roleId . '" ' . $selected . '>' . $roleName . '</option>';
                    }
                    $select .= '</select>';
                    return $select;
                })
                ->addColumn('designation', function ($row) {
                    $designationName = $row->designation->name ?? 'Not Assigned';
                    $designation = '<p class="text-sm">' . $designationName . '</p>';
                    return $designation;
                })
                ->addColumn('department', function ($row) {
                    $departmentName = $row->department->name ?? 'Not Assigned';
                    $department = '<p class="text-sm">' . $departmentName . '</p>';
                    return $department;
                })
                ->addColumn('status', function ($row) {
                    $status = GetStatusBadge($row->status); //
                    return $status;
                })
                ->addColumn('created_at', function ($row) {
                    $created_at = '<p class="text-sm">' . date('h:i A - d M, Y ', strtotime($row->created_at)) . '</p>';
                    return $created_at;
                })
                ->addColumn('action', function ($row) {
                    $btn = '';
                    if (auth()->user()->can('staff-read') || auth()->user()->can('staff-edit')) {
                        $btn .= '<div class="d-flex"><a href="' . route('admin.staff.edit', encrypt($row->id)) . '" class="btn btn-sm">
                          <i class="fa fa-pen text-primary"></i></a>';
                    }
                    return $btn;
                })
                ->rawColumns(['name', 'roles', 'department', 'designation', 'status', 'created_at', 'action'])
                ->make(true);
        }
        return view('hr.staff.index');
    }

    public function create()
    {
        if(Auth::guard('admin')->user()->role === 'superadmin'){
            $department = Department::get();
            $designation = Designation::get();
            $Organization = Organization::get();
            $roles = Role::get();
        }else{
            $department = Department::where('organization_id', Auth::guard('admin')->user()->organization_id)->get();
            $designation = Designation::where('organization_id', Auth::guard('admin')->user()->organization_id)->get();
            $Organization = Organization::where('id', Auth::guard('admin')->user()->organization_id)->get();
            $roles = Role::whereNot('name', 'superadmin')->get();
        }
        return view('hr.staff.create', compact('department', 'designation','Organization','roles'));
    }

    public function store(Request $request)
    {
        $validator = Validator::make(
            $request->all(),
            [
                'username' => 'required|string|unique:admin,username',
                'name' => 'required',
                'shift_hours' => 'required',
                'email' => 'required|unique:admin,email',
                'phone' => 'required|unique:admin,phone',
                'working_days' => 'required',
                'salary' => 'nullable|min:1|numeric',
                'gender' => 'nullable',
                'password' => 'required|min:8|confirmed',
                'dob' => 'required|date|before:' . now()->subYears(16)->toDateString(),  // DOB should be at least 16 years ago
                'joining_date' => 'required|date|after_or_equal:dob',  // Joining Date must be after DOB
                'probation_end_date' => 'nullable|date|after:joining_date',  // Probation End Date must be at least 1 day after Joining Date
                'status' => 'required',
                'profile_image' => 'nullable',
                'address' => 'nullable',
                'about' => 'nullable',
                'department_id' => 'required|exists:department,id',
                'designation_id' => 'required|exists:designation,id',
                'marital_status' => 'required',
                'employment_type' => 'required',
                'rolename' => 'required',
            ],
            [
                'dob.before' => 'The Date of Birth must be at least 16 years ago.',
                'joining_date.after_or_equal' => 'The Date of Joining must be at least 16 years after the Date of Birth.',
                'probation_end_date.after' => 'The Probation End Date must be at least 1 day after the Date of Joining.',
            ]
        );
        if ($validator->fails()) {
            return redirect()->back()->with('error', $validator->errors()->first())->withInput();
        }

        $profileImagePath = null;

        // Handle file upload if profile_image is provided
        if ($request->hasFile('profile_image')) {
            // Get the uploaded file
            $file = $request->file('profile_image');
            
            // Generate a unique file name
            $fileName = time() . '-' . $file->getClientOriginalName();
            
            // Convert file name to lowercase and remove spaces
            $fileName = strtolower($fileName);
            $fileName = preg_replace('/\s+/', '', $fileName);
            
            // Define the destination path (public directory)
            $destinationPath = public_path('staff_images');
            
            // Ensure the directory exists
            if (!file_exists($destinationPath)) {
                mkdir($destinationPath, 0777, true); // Create the folder if it doesn't exist
            }
            
            // Move the file to the destination folder
            $file->move($destinationPath, $fileName);
            
            // Set the profile image path (relative to public folder)
            $profileImagePath = 'staff_images/' . $fileName;
        }
        $workingDay = implode(',', $request->working_days ?? []);
        $createStaff = [
            'name' => $request->name,
            'email' => $request->email,
            'phone' => $request->phone,
            'joining_date' => $request->joining_date,
            'pay_based' => 'hourly',
            'working_days' => $workingDay,
            'salary' => $request->salary,
            'gender' => $request->gender,
            'password' => Hash::make($request->password),
            'dob' => $request->dob,
            'status' => $request->status,
            'profile_image' => $profileImagePath,
            'address' => $request->address,
            'about' => $request->about,
            'role' => $request->rolename,
            'username' => $request->username,
            'department_id' => $request->department_id,
            'designation_id' => $request->designation_id,
            'shift_hours' => $request->shift_hours,
            'pay_based' => $request->pay_based,
            'marital_status' => $request->marital_status,
            'probation_end_date' => $request->probation_end_date,
            'employment_type' => $request->employment_type,
            'organization_id' => Auth::guard('admin')->user()->organization_id,
        ];
        try {
            $user = Admin::create($createStaff);

            $user->roles()->detach();
            $user->assignRole($request->rolename);
            return redirect(route('admin.staff.index'))->with('success', 'Staff Added Successfully');
        } catch (Exception $e) {
            return redirect()->back()->with('error', 'Something went wrong, ' . $e->getMessage())->withInput();
        }
    }

    public function show()
    {
        // code here
    }

    public function edit($id)
    {
        $staffId = decrypt($id);
        $staff = Admin::find($staffId);
        if(Auth::guard('admin')->user()->role === 'superadmin'){
            $department = Department::get();
            $designation = Designation::get();
            $Organization = Organization::get();
        }else{
            $department = Department::where('organization_id', Auth::guard('admin')->user()->organization_id)->get();
            $designation = Designation::where('organization_id', Auth::guard('admin')->user()->organization_id)->get();
            $Organization = Organization::where('id', Auth::guard('admin')->user()->organization_id)->get();
        }
        return view('hr.staff.edit', compact('staff', 'department', 'designation'));
    }

    public function update(Request $request, $id)
    {
        $staffId = decrypt($id);
        $staff = Admin::find($staffId);
        $validator = Validator::make(
            $request->all(),
            [
                'name' => 'required',
                'username' => 'required|string|unique:admin,username,' . $staffId,
                'email' => 'required|unique:admin,email,' . $staffId,
                'phone' => 'required|unique:admin,phone,' . $staffId,
                'working_days' => 'required|array',
                'salary' => 'nullable|min:1|numeric',
                'gender' => 'nullable',
                'password' => 'nullable|min:8|confirmed',
                'dob' => 'required|date|before:' . now()->subYears(16)->toDateString(),  // DOB should be at least 16 years ago
                'joining_date' => 'required|date|after_or_equal:dob',  // Joining Date must be after DOB
                'probation_end_date' => 'nullable|date|after:joining_date',  // Probation End Date must be at least 1 day after Joining Date            'status' => 'required',
                'profile_image' => 'nullable',
                'address' => 'nullable',
                'about' => 'nullable',
                'department_id' => 'required|exists:department,id',
                'designation_id' => 'required|exists:designation,id',
                'marital_status' => 'required',
                'employment_type' => 'required',
            ],
            [
                'dob.before' => 'The Date of Birth must be at least 16 years ago.',
                'joining_date.after_or_equal' => 'The Date of Joining must be at least 16 years after the Date of Birth.',
                'probation_end_date.after' => 'The Probation End Date must be at least 1 day after the Date of Joining.',
            ]
        );
        if ($validator->fails()) {
            return redirect()->back()->with('error', $validator->errors()->first())->withInput();
        }
        if (!$staff) {
            return redirect()->back()->with('error', 'Invalid activity, Staff not found')->withInput();
        }


        // Handle profile image upload if a new one is provided
        $profileImagePath = $staff->profile_image; // Default to existing image if no new one is uploaded
        if ($request->hasFile('profile_image')) {
            // Validate file
            $request->validate([
                'profile_image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048', // max size 2MB
            ]);
    
            // Delete old image if it exists
            if ($staff->profile_image && file_exists(public_path('documents/' . $staff->profile_image))) {
                unlink(public_path('staff_images/' . $staff->profile_image)); // Delete the old image
            }
    
            // Process the new uploaded file
            $file = $request->file('profile_image');
            
            // Generate a unique name for the file
            $fileName = time() . '-' . $file->getClientOriginalName();
            
            // Convert file name to lowercase and remove spaces
            $fileName = strtolower($fileName);
            $fileName = preg_replace('/\s+/', '', $fileName);
            
            // Define the destination path
            $destinationPath = public_path('staff_images');
            
            // Ensure the directory exists
            if (!file_exists($destinationPath)) {
                mkdir($destinationPath, 0777, true); // Create the folder if it doesn't exist
            }
    
            // Move the file to the destination folder
            $file->move($destinationPath, $fileName);
    
            // Save the file path in the database (relative path)
            $profileImagePath = 'staff_images/' . $fileName;
        }

        $updateData = [
            'name' => $request->name,
            'username' => $request->username,
            'email' => $request->email,
            'phone' => $request->phone,
            'joining_date' => $request->joining_date,
            'pay_based' => 'hourly',
            'working_days' => implode(',', $request->working_days),
            'salary' => $request->salary,
            'gender' => $request->gender,
            'dob' => $request->dob,
            'status' => $request->status,
            'address' => $request->address,
            'about' => $request->about,
            'department_id' => $request->department_id,
            'designation_id' => $request->designation_id,
            'shift_hours' => $request->shift_hours,
            'pay_based' => $request->pay_based,
            'marital_status' => $request->marital_status,
            'probation_end_date' => $request->probation_end_date,
            'notice_period_start' => $request->notice_period_start,
            'notice_period_end' => $request->notice_period_end,
            'employment_type' => $request->employment_type,
            //'organization_id' => Auth::guard('admin')->user()->organization_id,
            'profile_image' => $profileImagePath, // Save the path of the new image
        ];
        if ($request->filled('password')) {
            $updateData['password'] = Hash::make($request->password);
        }
        if ($request->filled('profile_image')) {
            $updateData['profile_image'] = $request->profile_image;
        }
        try {
            $staff->update($updateData);
            return redirect(route('admin.staff.index'))->with('success', 'Staff updated successfully');
        } catch (Exception $e) {
            return redirect()->back()->with('error', 'Something went wrong, ' . $e->getMessage())->withInput();
        }
    }

    public function destroy()
    {
        // code here
    }


    public function validateUsername(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'username' => 'required|string|unique:admin,username,' . $request->staff_id,
        ]);
        if ($validator->fails()) {
            return response()->json(['status' => 0, 'message' => $validator->errors()->first()]);
        }
        return response()->json(['status' => 1, 'message' => 'Available for Staff User Name']);
    }

    public function emailSendToStaff(Request $request)
    {
        $validator = Validator($request->all(), [
            'email' => 'required|email|unique:admin,email,' . $request->staff_id,
            'staff_id' => 'required',
        ]);
        if ($validator->fails()) {
            return response()->json(['status' => 0, 'message' => $validator->errors()->first()]);
        }
        $RandStr = rand(1, 999999);
        $OtpValue = str_pad($RandStr, 6, '0', STR_PAD_LEFT);
        $expiresAt = Carbon::now()->addMinutes(5);
        $verifyEmail = EmailEntries::create([
            'email_receiver' => $request->email,
            'template' => 'VerifyStaffEmail',
            'token' => $OtpValue,
            'type' => 'staff_email_verification',
            'valid_for' => $expiresAt,
        ]);
        try {
            Mail::to($request->email)->queue(new VerifyUserEmail(['token' => $OtpValue]));
            $request->session()->put('StaffEmail', $request->email);
            return response()->json(['status' => 1, 'message' => 'Verification email sent successfully.']);
        } catch (\Exception $e) {
            return response()->json(['status' => 0, 'message' => 'Failed to send email: ' . $e->getMessage()]);
        }
    }
    // Email OTP verify  function
    public function verifyStaffEmail(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'otp' => 'required|string|max:6',
            'staff_id' => 'required|integer',
        ]);
        if ($validator->fails()) {
            return response()->json(['status' => 0, 'message' => $validator->errors()->first()]);
        }
        $StaffEmail = session()->get('StaffEmail');
        $staffId = $request->input('staff_id');
        $otp = $request->input('otp');
        $otpRecord = EmailEntries::where('token', $otp)->where('email_receiver', $StaffEmail)->whereNull('used_at')->orderBy('created_at', 'desc')->first();
        if (!$otpRecord) {
            return response()->json(['status' => 0, 'message' => 'Invalid or already used OTP.']);
        }
        $currentTime = Carbon::now();
        if ($currentTime->greaterThan(Carbon::parse($otpRecord->valid_for))) {
            return response()->json(['status' => 0, 'message' => 'OTP has expired.']);
        }
        $staff = Admin::find($staffId);
        if (!$staff) {
            return response()->json(['status' => 0, 'message' => 'Staff not found.']);
        }
        $otpRecord->update(['used_at' => $currentTime]);
        $staff->email_verified_at = now();
        $staff->email = $StaffEmail;
        $staff->save();
        return response()->json(['status' => 1, 'message' => 'OTP verified successfully. Email updated successfully.']);
    }
    // Mobile Number verify function
    public function otpSendToStaff(Request $request)
    {
        $validator = Validator($request->all(), [
            'phone' => 'required|numeric|digits:10|unique:admin,phone,' . $request->staff_id,
            'staff_id' => 'required',
        ]);
        if ($validator->fails()) {
            return response()->json(['status' => 0, 'message' => $validator->errors()->first()]);
        }
        $otpResponse = SendSMS($request->phone);
        if ($otpResponse['status'] == 1) {
            $request->session()->put('StaffNumber', $request->phone);
            return response()->json(['status' => 1, 'message' => 'Otp sent successfully, ' . $otpResponse['message']]);
        }
        return response()->json(['status' => 0, 'message' => 'Something went wrong, ' . $otpResponse['message']]);
    }
    // Mobile Number OTP verify function
    public function otpVerifyToStaff(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'otp' => 'required|numeric|min:6|exists:otp_entries,otp,used_at,NULL',
            'staff_id' => 'required|exists:admin,id',
        ]);
        if ($validator->fails()) {
            return response()->json(['status' => 0, 'message' => $validator->errors()->first()]);
        }
        $phone = session()->get('StaffNumber');
        $lastOTP = OtpEntries::whereNull('used_at')
            ->where('phone', $phone)
            ->latest()
            ->first();
        if ($lastOTP->otp != $request->otp) {
            return response()->json(['status' => 0, 'message' => 'You are trying with an old OTP. Please enter a new one.']);
        }
        $user = Admin::find($request->staff_id);
        if ($user) {
            $updateMobile = [
                'phone' => $phone,
                'phone_verified_at' => now(),
            ];
            $user->update($updateMobile);
            $lastOTP->update(['used_at' => now()]);
            session()->forget(['StaffNumber']);
            return response()->json(['status' => 1, 'message' => 'Otp verified successfully, mobile number updated successfully']);
        }
        return response()->json(['status' => 0, 'message' => 'Some error occurred']);
    }

    public function changeStaffRole(Request $request)
    {
        $user = Admin::find($request->staff_id);
        $role = Role::find($request->role_id);
        if (!$user) {
            return response()->json(['status' => 0, 'message' => 'Staff not find']);
        }
        if (!$role) {
            return response()->json(['status' => 0, 'message' => 'Staff not find']);
        }
        try {
            $user->roles()->detach();
            $user->assignRole($role->name);
            $user->role = $role->name;
            $user->save();
            return response()->json(['status' => 1, 'message' => $role->name . ' Role Assigned to ' . $user->name]);
        } catch (\Exception $e) {
            return response()->json(['status' => 0, 'message' => $e->getMessage()]);
        }
    }

    public function updateDocuments(Request $request, $id)
    {
        $staffId = decrypt($id);
        try {
            // Validate the request to ensure files are of the allowed types
            $request->validate([
                'files.*' => 'file|mimes:png,jpg,jpeg,pdf,doc,docx|max:20480', // Max size: 20MB per file
            ]);
            // Find the staff by ID (assuming you have a model associated with this ID)
            $staff = Admin::find($staffId); // Replace `YourModel` with the actual model
            if (!$staff) {
                return redirect()->back()->with('error', 'staff not found');
            }
            // Check if a file is uploaded
            $existingDocs = $staff->documents;
            $existingDocsArray = $existingDocs ? explode(',', $existingDocs) : [];
            // Initialize an array to store new file paths
            $filePaths = $existingDocsArray;

            // Check if files are uploaded
            if ($request->hasFile('files')) {
                $files = $request->file('files');
                foreach ($files as $file) {
                    $fileName = time() . '-' . $file->getClientOriginalName();
                    // Convert file name to lowercase and remove spaces
                    $fileName = strtolower($fileName);
                    $fileName = preg_replace('/\s+/', '', $fileName);
            
                    // Define the public path where the files will be stored
                    $destinationPath = public_path('documents');
                    
                    // Ensure the directory exists
                    if (!file_exists($destinationPath)) {
                        mkdir($destinationPath, 0777, true);
                    }
            
                    // Move the file to the public/documents folder
                    $file->move($destinationPath, $fileName);
            
                    // Append file path to array
                    $filePaths[] = 'documents/' . $fileName;
                }
            
                // Convert file paths array to a comma-separated string
                $filePathsString = implode(',', $filePaths);
            
                // Update the staff with the file paths
                $staff->documents = $filePathsString;
                $staff->save();
            
                return redirect()->back()->with('success', 'Document uploaded successfully');
            }
            
            else {
                return redirect()->back()->with('error', 'No file was uploaded');
            }
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'An error occurred while uploading the document' . $e->getMessage());
        }
    }
    public function deleteStaffDocument(Request $request, $file)
    {
        // Find the staff record associated with the document
        $staff = Admin::find($request->staff_id);
        if (!$staff) {
            return redirect()->back()->with('error', 'Document not found.');
        }
        // Remove the file from storage
        if (Storage::exists('public/' . $file)) {
            Storage::delete('public/' . $file);
        }
        // Remove the file from the database field
        $documents = explode(',', $staff->documents);
        $documents = array_filter($documents, function ($item) use ($file) {
            return basename($item) !== $file;
        });
        $staff->documents = implode(',', $documents);
        $staff->save();
        return redirect()->back()->with('success', 'Document deleted successfully.');
    }
}

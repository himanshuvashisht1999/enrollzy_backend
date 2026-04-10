<?php

namespace App\Http\Controllers;

use App\Models\Admin;
use Illuminate\Http\Request;
use App\Mail\StaffForgotPassword;
use App\Http\Controllers\Controller;
use App\Models\EmailEntries;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Validator;

class AdminLoginController extends Controller
{
    public function showLoginForm()
    {
        if (auth()->guard('admin')->check()) {
            return redirect(route('admin.dashboard'))->with('error', 'already login.');
        }
        return view('auth.login');
    }

    public function tryLoginUsingCredentials(Request $request)
    {
        $validator = Validator($request->all(), [
            'username' => 'required|exists:admin,username',
            'password' => 'required|min:6',
        ]);
        if ($validator->fails()) {
            return redirect()->back()->with('error', $validator->errors()->first())->withInput();
        } else {
            $validateAdmin = Admin::where('username', $request->username)->first();
            if ($validateAdmin->status !== 'active') {
                return redirect()->back()->with('error', 'Account not active..')->withInput();
            }
            $loginAdmin = Auth::guard('admin')->attempt([
                'username' => $request->username,
                'password' => $request->password,
            ]);
            if ($loginAdmin) {
                staffLog('staff', Auth::guard('admin')->id(), null, ' Staff Login');
                return redirect(route('admin.dashboard'))->with('success', 'Login Success');
            } else {
                return redirect()->back()->with('error', 'Login Credentials Failed')->withInput();
            }
        }
    }

    public function checkAccountForForgotPassword(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email_or_mobile' => 'required',
        ]);
        if ($validator->fails()) {
            return redirect()->back()->with('error', $validator->errors()->first())->withInput();
        }
        $loginField = $request->input('email_or_mobile');
        $fieldType = filter_var($loginField, FILTER_VALIDATE_EMAIL) ? 'email' : 'phone';
        $staff = Admin::where($fieldType, $loginField)->first();
        if (!$staff) {
            return redirect()->back()->with('error', 'Admin account does not exist.')->withInput();
        }
        if ($fieldType === 'phone') {
            if (!$staff->phone) {
                return redirect()->back()->with('error', 'Mobile number does not exist.')->withInput();
            }
            // Send OTP SMS
            // $OTP = rand(100000, 999999); // Generate OTP
            // $smsstring = "Your OTP for password reset is $OTP";
            // $this->sendSMS($user->phone, $smsstring);
            // // Store OTP in session or database for verification
            // session()->put('otp', $OTP);
            return redirect()->back()->with('success', 'OTP sent to your mobile number.');
        } elseif ($fieldType === 'email') {
            $token = Hash::make(rand(100000, 999999));
            $emailData = [
                'email_receiver' => $staff->email,
                'token' => $token,
                'template' => 'password_reset',
                'type' => 'forgot_password',
                'valid_for' => 5,
            ];
            // EmailEntries::create($emailData);
            return view('emails.password_reset', compact('token'));
            // Mail::to($staff->email)->queue(new StaffForgotPassword([$token]));
            // return redirect()->back()->with('success', 'Password reset email sent.');
        }
        return redirect()->back()->with('error', 'Invalid input. Please enter a valid email or phone number.')->withInput();
    }
    public function logout()
    {
        staffLog('staff', Auth::guard('admin')->id(), null, ' Staff Logout');
        Auth::guard('admin')->logout();
        return redirect()->route('login')->with('success', 'Logout Success');
    }
}

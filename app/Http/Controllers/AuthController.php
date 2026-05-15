<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class AuthController extends Controller
{
    /**
     * Show the admin login form.
     */
    public function showAdminLoginForm()
    {
        return view('auth.admin-login');
    }

    /**
     * Handle admin login.
     */
    public function adminLogin(Request $request)
    {
        $credentials = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required'],
        ]);

        // Attempt login for staff (admin guard)
        if (Auth::guard('admin')->attempt($credentials + ['status' => 'active'])) {
            $request->session()->regenerate();
            return redirect()->intended(route('admin.dashboard'));
        }

        // Attempt login for legacy admins (web guard)
        if (Auth::guard('web')->attempt($credentials)) {
            if (Auth::user()->is_admin) {
                $request->session()->regenerate();
                return redirect()->intended(route('admin.dashboard'));
            }
            Auth::logout();
            return back()->withErrors(['email' => 'You do not have administrative access.'])->onlyInput('email');
        }

        return back()->withErrors([
            'email' => 'The provided credentials do not match our records.',
        ])->onlyInput('email');
    }

    /**
     * Handle logout.
     */
    public function logout(Request $request)
    {
        Auth::guard('admin')->logout();
        Auth::guard('web')->logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login');
    }
}

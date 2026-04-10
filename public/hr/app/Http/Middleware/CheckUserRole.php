<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CheckUserRole
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @param  string  $role
     * @return mixed
     */
    public function handle(Request $request, Closure $next, $role)
    {
        // Check if the user is authenticated and has the required role
        if (Auth::guard('admin')->check() && Auth::guard('admin')->user()->hasRole($role)) {
            return $next($request);
        }
        // If the user does not have the required role, redirect to home or show an error
        return redirect()->back()->with('error', 'You do not have permission to access this page.');
    }
}

<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Expert;
use App\Models\Blog;
use App\Models\Category;
use App\Models\Faq;
use App\Models\Testimonial;
use App\Models\Lead;
use App\Models\Attendance;
use App\Models\Breaks;
use App\Models\Admin;
use App\Models\Tasks;
use Illuminate\Support\Facades\Auth;

class DashboardController extends Controller
{
    /**
     * Display the admin dashboard.
     */
    public function index()
    {
        $user = Auth::user();
        $orgId = $user->organization_id;

        $stats = [
            'experts' => Expert::count(),
            'blogs' => Blog::count(),
            'categories' => Category::count(),
            'faqs' => Faq::count(),
            'testimonials' => Testimonial::count(),
            'leads' => Lead::count(),
            'new_leads' => Lead::where('status', 'New')->count(),
            // HR Stats
            'total_staff' => Admin::where('organization_id', $orgId)->where('id', '!=', $user->id)->count(),
            'pending_tasks' => Tasks::where('organization_id', $orgId)->where('assigned_to', $user->id)->where('status', '!=', 'completed')->count(),
        ];

        $attendance = Attendance::where('staff_id', $user->id)
            ->where('date', date('Y-m-d'))
            ->whereNull('check_out')
            ->first();
            
        $breaks = null;
        if ($attendance) {
            $breaks = Breaks::where('attendance_id', $attendance->id)
                ->whereNull('end')
                ->first();
        }

        $pendingTasks = Tasks::where('organization_id', $orgId)
            ->where('assigned_to', $user->id)
            ->where('status', '!=', 'completed')
            ->latest()
            ->take(5)
            ->get();

        return view('admin.dashboard', compact('stats', 'attendance', 'breaks', 'pendingTasks'));
    }
}

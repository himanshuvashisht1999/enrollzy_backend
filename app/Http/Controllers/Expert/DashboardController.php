<?php

namespace App\Http\Controllers\Expert;

use App\Http\Controllers\Controller;
use App\Models\Booking;
use App\Models\ExpertSlot;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class DashboardController extends Controller
{
    public function index()
    {
        $expert = Auth::guard('expert')->user();

        $totalSlots = ExpertSlot::where('expert_id', $expert->id)->count();
        $availableSlots = ExpertSlot::where('expert_id', $expert->id)->where('status', 'available')->count();
        $totalBookings = Booking::where('expert_id', $expert->id)->count();
        $pendingBookings = Booking::where('expert_id', $expert->id)->where('status', 'pending')->count();
        $confirmedBookings = Booking::where('expert_id', $expert->id)->where('status', 'confirmed')->count();
        $completedBookings = Booking::where('expert_id', $expert->id)->where('status', 'completed')->count();
        $totalEarnings = Booking::where('expert_id', $expert->id)->where('payment_status', 'paid')->sum('expert_earning');

        $recentBookings = Booking::with('user', 'slot')
            ->where('expert_id', $expert->id)
            ->orderBy('id', 'desc')
            ->take(5)
            ->get();

        return view('expert.dashboard', compact(
            'expert',
            'totalSlots',
            'availableSlots',
            'totalBookings',
            'pendingBookings',
            'confirmedBookings',
            'completedBookings',
            'totalEarnings',
            'recentBookings'
        ));
    }
}

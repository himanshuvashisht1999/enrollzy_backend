<?php

namespace App\Http\Controllers\Expert;

use App\Http\Controllers\Controller;
use App\Models\Booking;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class BookingController extends Controller
{
    public function index(Request $request)
    {
        $expert = Auth::guard('expert')->user();

        $query = Booking::with('user', 'slot')
            ->where('expert_id', $expert->id);

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $bookings = $query->orderBy('id', 'desc')->paginate(15);

        return view('expert.bookings.index', compact('expert', 'bookings'));
    }

    public function approve(Request $request, $id)
    {
        $expert = Auth::guard('expert')->user();
        $booking = Booking::where('expert_id', $expert->id)->findOrFail($id);

        $request->validate([
            'meeting_link' => ['nullable', 'url'],
        ]);

        $booking->update([
            'status' => 'confirmed',
            'meeting_link' => $request->meeting_link ?? $booking->meeting_link,
        ]);

        return redirect()->back()->with('success', 'Booking session approved successfully.');
    }

    public function reject(Request $request, $id)
    {
        $expert = Auth::guard('expert')->user();
        $booking = Booking::where('expert_id', $expert->id)->findOrFail($id);

        $booking->update([
            'status' => 'cancelled',
        ]);

        if ($booking->slot) {
            $booking->slot->update(['status' => 'available']);
        }

        return redirect()->back()->with('success', 'Booking session rejected.');
    }

    public function complete($id)
    {
        $expert = Auth::guard('expert')->user();
        $booking = Booking::where('expert_id', $expert->id)->findOrFail($id);

        $booking->update([
            'status' => 'completed',
        ]);

        return redirect()->back()->with('success', 'Session marked as completed.');
    }

    public function updateMeetingLink(Request $request, $id)
    {
        $expert = Auth::guard('expert')->user();
        $booking = Booking::where('expert_id', $expert->id)->findOrFail($id);

        $request->validate([
            'meeting_link' => ['nullable', 'url'],
        ]);

        $booking->update([
            'meeting_link' => $request->meeting_link,
        ]);

        return redirect()->back()->with('success', 'Meeting link updated successfully.');
    }
}

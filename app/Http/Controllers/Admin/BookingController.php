<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Booking;
use Illuminate\Http\Request;

class BookingController extends Controller
{
    public function index(Request $request)
    {
        $query = Booking::with(['user', 'expert', 'slot']);

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('booking_id', 'like', "%$search%")
                  ->orWhereHas('user', function($u) use ($search) {
                      $u->where('name', 'like', "%$search%")
                        ->orWhere('email', 'like', "%$search%");
                  })
                  ->orWhereHas('expert', function($e) use ($search) {
                      $e->where('name', 'like', "%$search%");
                  });
            });
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $bookings = $query->latest()->paginate(15);
        $statuses = ['Pending', 'Confirmed', 'Completed', 'Cancelled', 'Rescheduled'];

        return view('admin.bookings.index', compact('bookings', 'statuses'));
    }

    public function show(Booking $booking)
    {
        // For detailed view if needed, currently index covers requirements
        return view('admin.bookings.show', compact('booking'));
    }
}

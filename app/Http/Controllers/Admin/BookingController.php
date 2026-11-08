<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Booking;
use App\Models\Expert;
use App\Models\ExpertSlot;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class BookingController extends Controller
{
    public function index(Request $request)
    {
        // bookings made on the website are saved without a commission breakdown: work it out first
        Booking::fillMissingCommission();

        $query = Booking::with(['user', 'expert.expertCategory', 'slot']);

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

        foreach (['status', 'payment_status', 'expert_id'] as $f) {
            if ($request->filled($f)) {
                $query->where($f, $request->input($f));
            }
        }

        $bookings = $query->latest()->paginate(15)->withQueryString();
        $statuses = Booking::STATUSES;
        $paymentStatuses = Booking::PAYMENT_STATUSES;
        $experts = Expert::orderBy('name')->get(['id', 'name']);
        // open times of the experts on this page, offered when moving a booking (same fee only)
        $openSlots = ExpertSlot::open()->whereIn('expert_id', $bookings->pluck('expert_id')->unique())
            ->orderBy('date')->orderBy('start_time')->get();

        return view('admin.bookings.index', compact('bookings', 'statuses', 'paymentStatuses', 'experts', 'openSlots'));
    }

    public function update(Request $request, $id)
    {
        $booking = Booking::with(['slot', 'expert'])->findOrFail($id);

        $data = $request->validate([
            'meeting_link' => ['nullable', 'url', 'max:500'],
            'status' => ['required', Rule::in(array_keys(Booking::STATUSES))],
            'payment_status' => ['required', Rule::in(array_keys(Booking::PAYMENT_STATUSES))],
            'commission_override_type' => ['nullable', Rule::in(['percentage', 'flat_fee'])],
            'commission_override_value' => ['nullable', 'required_with:commission_override_type', 'numeric', 'min:0'],
            'override_reason' => ['nullable', 'required_with:commission_override_type', 'string', 'max:255'],
            'new_slot_id' => ['nullable', 'integer'],
            'session_summary' => ['nullable', 'string', 'max:3000'],
        ], ['override_reason.required_with' => 'Give a reason for the commission override.']);

        // moving to another slot first: a failed move changes nothing
        if (!empty($data['new_slot_id'])) {
            if ($data['status'] === 'cancelled') {
                return back()->with('error', 'Pick either a new time or Cancelled, not both.');
            }
            if ($error = $booking->moveToSlot((int) $data['new_slot_id'], 'admin')) {
                return back()->with('error', $error);
            }
            $booking->load('slot');
        }

        if ($data['status'] !== $booking->status && $booking->status === 'cancelled' && $booking->slot) {
            // re-opening a cancelled booking needs its slot back, unless someone else has booked it since
            $taken = Booking::where('slot_id', $booking->slot_id)->where('id', '!=', $booking->id)->where('status', '!=', 'cancelled')->exists();
            if ($taken || $booking->slot->trashed()) {
                return back()->with('error', 'That slot has been booked by someone else (or deleted), so this booking cannot be re-opened.');
            }
        }

        DB::transaction(function () use ($booking, $data) {
            $cancelling = $data['status'] === 'cancelled' && $booking->status !== 'cancelled';
            $reopening = $data['status'] !== 'cancelled' && $booking->status === 'cancelled';

            // cancelled after the student paid: the money is owed back (unless admin already chose a payment status)
            if ($cancelling && (float) $booking->amount > 0 && $booking->payment_status === 'paid' && $data['payment_status'] === 'paid') {
                $data['payment_status'] = 'refund_due';
            }

            $override = $data['commission_override_type'] ?? null;
            $booking->fill([
                'meeting_link' => $data['meeting_link'] ?? null,
                'status' => $data['status'],
                'payment_status' => $data['payment_status'],
                'commission_override_type' => $override,
                'commission_override_value' => $override ? $data['commission_override_value'] : null,
                'override_reason' => $override ? $data['override_reason'] : null,
                'override_by' => $override ? auth('admin')->id() : null,
                'session_summary' => trim((string) ($data['session_summary'] ?? '')) ?: null,
            ]);
            // the student's cancellation request: answered by cancelling, or turned down
            if (request()->boolean('dismiss_cancel_request') && $data['status'] !== 'cancelled') $booking->fill(['cancel_requested_at' => null, 'cancel_reason' => null]);
            $booking->save();
            $booking->applyCommission();   // the override, or the rules, decide the split

            if ($booking->slot && !$booking->slot->trashed()) {
                if ($cancelling) $booking->slot->update(['status' => 'available']);
                if ($reopening) $booking->slot->update(['status' => 'booked']);
            }
        });

        $moved = !empty($data['new_slot_id']) ? ' and moved to ' . $booking->sessionStart()->format('D d M Y, h:i A') . ' (let the student know)' : '';
        return redirect()->back()->with('success', 'Booking ' . $booking->booking_id . ' updated' . $moved . '.');
    }
}

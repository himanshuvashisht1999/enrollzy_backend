<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Expert;
use App\Models\ExpertSlot;
use Illuminate\Http\Request;

class SlotController extends Controller
{
    public function index(Request $request)
    {
        $experts = Expert::orderBy('name')->get();

        $query = ExpertSlot::with('expert');

        if ($request->filled('expert_id')) {
            $query->where('expert_id', $request->expert_id);
        }

        if ($request->filled('date')) {
            $query->whereDate('date', $request->date);
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $slots = $query->orderBy('date', 'desc')
                      ->orderBy('start_time', 'asc')
                      ->paginate(15);

        return view('admin.slots.index', compact('slots', 'experts'));
    }

    public function store(Request $request)
    {
        if ($request->type === 'bulk') {
            $request->validate([
                'expert_id'  => ['required', 'exists:experts,id'],
                'start_date' => ['required', 'date', 'after_or_equal:today'],
                'end_date'   => ['required', 'date', 'after_or_equal:start_date'],
                'days'       => ['required', 'array', 'min:1'],
                'start_time' => ['required'],
                'end_time'   => ['required', 'after:start_time'],
                'mode'       => ['required', 'in:video,audio,chat'],
                'cost'       => ['nullable', 'numeric', 'min:0'],
            ]);

            $expert = Expert::find($request->expert_id);
            $startDate = \Carbon\Carbon::parse($request->start_date);
            $endDate = \Carbon\Carbon::parse($request->end_date);
            $count = 0;

            $cost = $request->cost;
            if ($cost === null || $cost === '' || $cost == 0) {
                $start = strtotime($request->start_time);
                $end = strtotime($request->end_time);
                $durationMinutes = max(0, ($end - $start) / 60);
                $pricePerMin = $expert->price_per_min ?? 10.00;
                $cost = $durationMinutes * $pricePerMin;
            }

            for ($date = $startDate->copy(); $date->lte($endDate); $date->addDay()) {
                if (in_array($date->format('D'), $request->days)) {
                    $exists = (bool) ExpertSlot::overlapping($expert->id, $date->format('Y-m-d'), $request->start_time, $request->end_time);

                    if (!$exists) {
                        ExpertSlot::create([
                            'expert_id'    => $expert->id,
                            'date'         => $date->format('Y-m-d'),
                            'start_time'   => $request->start_time,
                            'end_time'     => $request->end_time,
                            'status'       => 'available',
                            'cost'         => $cost,
                            'mode'         => $request->mode ?? 'video',
                            'is_recurring' => true,
                            'recurring_day'=> $date->format('l'),
                        ]);
                        $count++;
                    }
                }
            }

            return redirect()->route('admin.slots.index', array_filter($request->only(['expert_id', 'status'])))
                ->with('success', "$count recurring time slots generated successfully.");
        } else {
            $request->validate([
                'expert_id'  => ['required', 'exists:experts,id'],
                'date'       => ['required', 'date'],
                'start_time' => ['required'],
                'end_time'   => ['required', 'after:start_time'],
                'mode'       => ['required', 'in:video,audio,chat'],
                'cost'       => ['nullable', 'numeric', 'min:0'],
                'status'     => ['nullable', 'in:available,blocked'],
            ]);

            if ($clash = ExpertSlot::overlapping((int) $request->expert_id, $request->date, $request->start_time, $request->end_time)) {
                return back()->withInput()->with('error', 'This overlaps another slot of this expert, ' . substr($clash->start_time, 0, 5) . '-' . substr($clash->end_time, 0, 5) . ' on that day.');
            }

            $cost = $request->cost;
            if ($cost === null || $cost === '' || $cost == 0) {
                $expert = Expert::find($request->expert_id);
                $start = strtotime($request->start_time);
                $end = strtotime($request->end_time);
                $durationMinutes = max(0, ($end - $start) / 60);
                $pricePerMin = $expert->price_per_min ?? 10.00;
                $cost = $durationMinutes * $pricePerMin;
            }

            ExpertSlot::create([
                'expert_id'    => $request->expert_id,
                'date'         => $request->date,
                'start_time'   => $request->start_time,
                'end_time'     => $request->end_time,
                'is_recurring' => $request->boolean('is_recurring'),
                'recurring_day'=> $request->recurring_day,
                'status'       => $request->status ?? 'available',
                'mode'         => $request->mode ?? 'video',
                'cost'         => $cost,
            ]);

            return redirect()->route('admin.slots.index', array_filter($request->only(['expert_id', 'date', 'status'])))
                ->with('success', 'Expert time slot created successfully.');
        }
    }

    public function update(Request $request, $id)
    {
        $slot = ExpertSlot::findOrFail($id);

        if ($booking = $slot->activeBooking()) {
            return back()->with('error', "This slot has booking {$booking->booking_id} ({$booking->status}). Cancel or complete that booking (Expert Bookings) before changing the slot.");
        }

        $request->validate([
            'expert_id'  => ['required', 'exists:experts,id'],
            'date'       => ['required', 'date'],
            'start_time' => ['required'],
            'end_time'   => ['required', 'after:start_time'],
            'status'     => ['required', 'in:available,blocked'],
            'mode'       => ['required', 'in:video,audio,chat'],
            'cost'       => ['nullable', 'numeric', 'min:0'],
        ]);

        if ($clash = ExpertSlot::overlapping((int) $request->expert_id, $request->date, $request->start_time, $request->end_time, $slot->id)) {
            return back()->with('error', 'This overlaps another slot of this expert, ' . substr($clash->start_time, 0, 5) . '-' . substr($clash->end_time, 0, 5) . ' on that day.');
        }

        $slot->update([
            'expert_id'  => $request->expert_id,
            'date'       => $request->date,
            'start_time' => $request->start_time,
            'end_time'   => $request->end_time,
            'status'     => $request->status,
            'mode'       => $request->mode,
            'cost'       => $request->filled('cost') && (float) $request->cost > 0 ? $request->cost
                : round(max(0, (strtotime($request->end_time) - strtotime($request->start_time)) / 60) * (float) (Expert::find($request->expert_id)->price_per_min ?? 10), 2),
        ]);

        return redirect()->back()->with('success', 'Expert slot updated successfully.');
    }

    public function destroy($id)
    {
        $slot = ExpertSlot::findOrFail($id);
        if ($booking = $slot->activeBooking()) {
            return back()->with('error', "This slot has booking {$booking->booking_id} ({$booking->status}). Cancel that booking first.");
        }
        $slot->delete();

        return redirect()->back()->with('success', 'Expert slot deleted successfully.');
    }
}

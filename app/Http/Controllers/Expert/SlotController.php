<?php

namespace App\Http\Controllers\Expert;

use App\Http\Controllers\Controller;
use App\Models\ExpertSlot;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class SlotController extends Controller
{
    public function index(Request $request)
    {
        $expert = Auth::guard('expert')->user();

        $query = ExpertSlot::where('expert_id', $expert->id);

        if ($request->filled('date')) {
            $query->whereDate('date', $request->date);
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $slots = $query->orderBy('date', 'asc')->orderBy('start_time', 'asc')->paginate(15);

        return view('expert.slots.index', compact('expert', 'slots'));
    }

    public function store(Request $request)
    {
        $expert = Auth::guard('expert')->user();

        if ($request->type === 'bulk') {
            $request->validate([
                'start_date' => ['required', 'date', 'after_or_equal:today'],
                'end_date'   => ['required', 'date', 'after_or_equal:start_date'],
                'days'       => ['required', 'array', 'min:1'],
                'start_time' => ['required'],
                'end_time'   => ['required'],
                'mode'       => ['required', 'in:video,audio,chat'],
                'cost'       => ['nullable', 'numeric', 'min:0'],
            ]);

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
                    $exists = ExpertSlot::where('expert_id', $expert->id)
                        ->where('date', $date->format('Y-m-d'))
                        ->where('start_time', $request->start_time)
                        ->exists();

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

            return redirect()->route('expert.slots.index')->with('success', "$count recurring availability slots generated successfully.");
        } else {
            $request->validate([
                'date' => ['required', 'date', 'after_or_equal:today'],
                'start_time' => ['required'],
                'end_time' => ['required'],
                'mode' => ['required', 'in:video,audio,chat'],
                'cost' => ['nullable', 'numeric', 'min:0'],
            ]);

            $cost = $request->cost;
            if ($cost === null || $cost === '' || $cost == 0) {
                $start = strtotime($request->start_time);
                $end = strtotime($request->end_time);
                $durationMinutes = max(0, ($end - $start) / 60);
                $pricePerMin = $expert->price_per_min ?? 10.00;
                $cost = $durationMinutes * $pricePerMin;
            }

            ExpertSlot::create([
                'expert_id' => $expert->id,
                'date' => $request->date,
                'start_time' => $request->start_time,
                'end_time' => $request->end_time,
                'is_recurring' => $request->boolean('is_recurring'),
                'recurring_day' => $request->recurring_day,
                'status' => 'available',
                'mode' => $request->mode ?? 'video',
                'cost' => $cost,
            ]);

            return redirect()->route('expert.slots.index')->with('success', 'Time slot created successfully.');
        }
    }

    public function update(Request $request, $id)
    {
        $expert = Auth::guard('expert')->user();
        $slot = ExpertSlot::where('expert_id', $expert->id)->findOrFail($id);

        $request->validate([
            'date' => ['required', 'date'],
            'start_time' => ['required'],
            'end_time' => ['required'],
            'status' => ['required', 'in:available,booked,blocked'],
            'mode' => ['required', 'in:video,audio,chat'],
            'cost' => ['nullable', 'numeric', 'min:0'],
        ]);

        $slot->update([
            'date' => $request->date,
            'start_time' => $request->start_time,
            'end_time' => $request->end_time,
            'status' => $request->status,
            'mode' => $request->mode,
            'cost' => $request->cost ?? 0,
        ]);

        return redirect()->route('expert.slots.index')->with('success', 'Slot updated successfully.');
    }

    public function destroy($id)
    {
        $expert = Auth::guard('expert')->user();
        $slot = ExpertSlot::where('expert_id', $expert->id)->findOrFail($id);

        // Soft delete using Eloquent trait
        $slot->delete();

        return redirect()->route('expert.slots.index')->with('success', 'Slot soft-deleted successfully.');
    }
}

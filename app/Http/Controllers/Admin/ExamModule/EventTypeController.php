<?php

namespace App\Http\Controllers\Admin\ExamModule;

use App\Http\Controllers\Controller;
use App\Models\ExamModule\EventType;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class EventTypeController extends Controller
{
    public function index()
    {
        $types = EventType::withCount('events')->orderBy('sort_order')->get();
        return view('admin.exam-module.event-types.index', compact('types'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => 'required|string|max:100|unique:exm_event_types,name',
            'status_source' => ['required', Rule::in(['dates', 'manual'])],
            'applies_to' => ['required', Rule::in(['exam', 'counselling', 'both'])],
        ]);
        EventType::create($data + ['slug' => Str::slug($data['name']), 'sort_order' => EventType::max('sort_order') + 1, 'status' => true]);
        return back()->with('success', 'Event type added.');
    }

    public function update(Request $request, EventType $eventType)
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:100', Rule::unique('exm_event_types', 'name')->ignore($eventType->id)],
            'sort_order' => 'nullable|integer|min:0',
        ]);
        // status_source is locked once events exist: changing it would reinterpret saved data.
        $eventType->update($data + ['status' => $request->boolean('status')]);
        return back()->with('success', 'Event type updated.');
    }

    public function destroy(EventType $eventType)
    {
        if ($eventType->events()->exists()) {
            return back()->with('error', 'Event type is in use. Mark it inactive instead.');
        }
        $eventType->delete();
        return back()->with('success', 'Event type deleted.');
    }
}

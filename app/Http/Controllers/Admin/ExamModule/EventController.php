<?php

namespace App\Http\Controllers\Admin\ExamModule;

use App\Http\Controllers\Controller;
use App\Models\ExamModule\Cycle;
use App\Models\ExamModule\CycleEvent;
use App\Models\ExamModule\EventType;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

class EventController extends Controller
{
    public function store(Request $request, Cycle $cycle)
    {
        $data = $this->validated($request, $cycle);
        $cycle->events()->create($data);
        return back()->with('success', 'Event added.');
    }

    public function update(Request $request, CycleEvent $event)
    {
        $event->update($this->validated($request, $event->cycle, $event));
        return back()->with('success', 'Event updated.');
    }

    public function destroy(CycleEvent $event)
    {
        $event->delete();
        return back()->with('success', 'Event deleted.');
    }

    public function verify(Request $request, CycleEvent $event)
    {
        $event->update(['last_verified_at' => now(), 'verified_by' => $request->user('admin')?->id]);
        return back()->with('success', 'Event marked as verified.');
    }

    /** Links must be absolute http(s) and must not point back at this CRM. */
    private function externalUrl(): \Closure
    {
        return function ($attribute, $value, $fail) {
            $host = strtolower((string) parse_url($value, PHP_URL_HOST));
            $scheme = strtolower((string) parse_url($value, PHP_URL_SCHEME));
            $own = array_filter([request()->getHost(), parse_url(config('app.url'), PHP_URL_HOST)]);
            if (!in_array($scheme, ['http', 'https'], true) || in_array($host, array_map('strtolower', $own), true)) {
                $fail('Enter the official external URL, not a page of this CRM.');
            }
        };
    }

    private function validated(Request $request, Cycle $cycle, ?CycleEvent $event = null): array
    {
        $cycle->loadMissing('exam');
        $usesStages = $cycle->usesStages();
        $allStages = $request->input('stage_id') === 'all';
        // "all" is a UI option, not a stage id.
        $request->merge(['stage_id' => $allStages ? null : $request->input('stage_id')]);

        $validator = Validator::make($request->all(), [
            'event_type_id' => ['required', Rule::exists('exm_event_types', 'id')->whereIn('applies_to', ['exam', 'both'])],
            'stage_id' => [$usesStages && !$allStages ? 'required' : 'nullable', Rule::exists('exm_stages', 'id')->where('cycle_id', $cycle->id)],
            'label' => 'nullable|string|max:150',
            'start_at' => 'nullable|date',
            'end_at' => 'nullable|date',
            'extended_end_at' => 'nullable|date',
            'date_precision' => ['required', Rule::in(array_keys(CycleEvent::PRECISIONS))],
            'manual_status' => ['nullable', Rule::in(array_keys(CycleEvent::MANUAL_STATUSES))],
            'on_demand_text' => 'nullable|string|max:255',
            'link_url' => ['nullable', 'url', 'max:500', $this->externalUrl()],
            'note' => 'nullable|string|max:1000',
            'source_url' => ['nullable', 'url', 'max:500', $this->externalUrl()],
        ], [
            'stage_id.required' => 'Pick a stage, or "All stages (common)" for events that apply to every stage.',
        ]);

        // Section 8 validations.
        $validator->after(function ($v) use ($request, $cycle) {
            $type = EventType::find($request->event_type_id);
            $tz = $cycle->timezone ?: 'Asia/Kolkata';
            $parse = fn ($k) => $request->filled($k) ? \Carbon\Carbon::parse($request->$k, $tz) : null;
            [$start, $end, $ext] = [$parse('start_at'), $parse('end_at'), $parse('extended_end_at')];

            if ($start && $end && $end->lt($start)) $v->errors()->add('end_at', 'End must be after start.');
            if ($ext && $end && $ext->lte($end)) $v->errors()->add('extended_end_at', 'Extended end must be after end.');
            if ($ext && !$end) $v->errors()->add('extended_end_at', 'Set an end date before extending.');

            if (($start || $end || $ext) && !$request->filled('source_url')) {
                $v->errors()->add('source_url', 'Source URL is required when a date is entered.');
            }
            if ($type?->isManual()) {
                if ($request->manual_status === 'declared') {
                    if (!$start) $v->errors()->add('start_at', 'A date is required when status is Declared.');
                    if (!$request->filled('link_url')) $v->errors()->add('link_url', 'A link is required when status is Declared.');
                }
                if ($request->manual_status === 'expected' && !$start) {
                    $v->errors()->add('start_at', 'A date or month is required when status is Expected.');
                }
            }
            if ($cycle->result_mode === 'on_demand' && $type?->slug === 'result' && !$request->filled('on_demand_text')) {
                $v->errors()->add('on_demand_text', 'Describe the result timing (cycle result mode is On demand).');
            }
        });

        $data = $validator->validate();
        $type = EventType::find($data['event_type_id']);

        // Manual types keep a manual status; date types never store one.
        $data['manual_status'] = $type->isManual() ? ($data['manual_status'] ?? 'not_announced') : null;
        $data['cycle_id'] = $cycle->id;
        // Stage scope only applies when the exam has stages and this cycle defines them.
        $data['applies_to_all_stages'] = $usesStages && $allStages;
        $data['stage_id'] = $usesStages && !$allStages ? ($data['stage_id'] ?? null) : null;
        // Interpret entered times in the cycle's timezone (model stores UTC).
        foreach (['start_at', 'end_at', 'extended_end_at'] as $k) {
            $data[$k] = filled($data[$k] ?? null) ? \Carbon\Carbon::parse($data[$k], $cycle->timezone ?: 'Asia/Kolkata') : null;
        }
        if (empty($data['start_at'])) {
            $data['date_precision'] = $data['date_precision'] === 'exact' ? 'tba' : $data['date_precision'];
        }
        // Editing a date invalidates the earlier verification.
        $data['last_verified_at'] = null;
        $data['verified_by'] = null;
        return $data;
    }
}

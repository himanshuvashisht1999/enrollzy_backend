<?php

namespace App\Http\Controllers\Admin\Counselling;

use App\Http\Controllers\Controller;
use App\Models\Counselling\CounsellingCycle;
use App\Models\Counselling\Round;
use App\Models\ExamModule\CycleEvent;
use App\Models\ExamModule\EventType;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

/**
 * Dates for a counselling cycle or one of its rounds. Uses the Exam Module's events table and status
 * logic; only the owner differs (counselling_cycle_id, plus round_id for round events).
 */
class EventController extends Controller
{
    public function storeForCycle(Request $request, CounsellingCycle $cycle)
    {
        $cycle->events()->create($this->validated($request, $cycle));
        return back()->with('success', 'Event added.');
    }

    public function storeForRound(Request $request, Round $round)
    {
        $data = $this->validated($request, $round->cycle, $round);
        $event = $round->cycle->allEvents()->create($data + ['round_id' => $round->id]);
        return back()->with('success', 'Event added.' . $this->overlapNote($event, $round));
    }

    public function update(Request $request, CycleEvent $event)
    {
        $this->ownedByCounselling($event);
        $round = $event->round_id ? $event->round : null;
        $event->update($this->validated($request, $event->counsellingCycle, $round));
        return back()->with('success', 'Event updated.' . ($round ? $this->overlapNote($event, $round) : ''));
    }

    public function destroy(CycleEvent $event)
    {
        $this->ownedByCounselling($event);
        $event->delete();
        return back()->with('success', 'Event deleted.');
    }

    public function verify(Request $request, CycleEvent $event)
    {
        $this->ownedByCounselling($event);
        $event->update(['last_verified_at' => now(), 'verified_by' => $request->user('admin')?->id]);
        return back()->with('success', 'Event marked as verified.');
    }

    /** These routes must never touch an exam's events. */
    private function ownedByCounselling(CycleEvent $event): void
    {
        abort_unless($event->counselling_cycle_id, 404);
    }

    private function validated(Request $request, CounsellingCycle $cycle, ?Round $round = null): array
    {
        $validator = Validator::make($request->all(), [
            'event_type_id' => ['required', Rule::exists('exm_event_types', 'id')->whereIn('applies_to', ['counselling', 'both'])],
            'quota_id' => ['nullable', Rule::exists('cns_quotas', 'id')],
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
        ]);

        $validator->after(function ($v) use ($request, $cycle) {
            $type = EventType::find($request->event_type_id);
            $tz = $cycle->timezone ?: 'Asia/Kolkata';
            $parse = fn ($k) => $request->filled($k) ? Carbon::parse($request->$k, $tz) : null;
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
        });

        $data = $validator->validate();
        $type = EventType::find($data['event_type_id']);

        $data['manual_status'] = $type->isManual() ? ($data['manual_status'] ?? 'not_announced') : null;
        $data['counselling_cycle_id'] = $cycle->id;
        $data['cycle_id'] = null;
        $data['round_id'] = $round?->id;
        foreach (['start_at', 'end_at', 'extended_end_at'] as $k) {
            $data[$k] = filled($data[$k] ?? null) ? Carbon::parse($data[$k], $cycle->timezone ?: 'Asia/Kolkata') : null;
        }
        if (empty($data['start_at']) && $data['date_precision'] === 'exact') {
            $data['date_precision'] = 'tba';
        }
        // Editing a date invalidates the earlier verification.
        $data['last_verified_at'] = null;
        $data['verified_by'] = null;
        return $data;
    }

    /** Links must be absolute http(s) and must not point back at this CRM. */
    private function externalUrl(): \Closure
    {
        return function ($attribute, $value, $fail) {
            $host = strtolower((string) parse_url($value, PHP_URL_HOST));
            $scheme = strtolower((string) parse_url($value, PHP_URL_SCHEME));
            $own = array_map('strtolower', array_filter([request()->getHost(), parse_url(config('app.url'), PHP_URL_HOST)]));
            if (!in_array($scheme, ['http', 'https'], true) || in_array($host, $own, true)) {
                $fail('Enter the official external URL, not a page of this CRM.');
            }
        };
    }

    /**
     * Some authorities overlap windows, so a round event starting before the previous round's
     * allotment result is a warning, never a block (spec section 15).
     */
    private function overlapNote(CycleEvent $event, Round $round): string
    {
        $event->refresh();
        $prev = $round->previous();
        if (!$prev || !$event->start_at) {
            return '';
        }
        $result = $prev->events()->whereHas('type', fn ($q) => $q->where('slug', 'allotment-result'))->get()
            ->filter(fn ($e) => $e->start_at)->sortByDesc('start_at')->first();

        return ($result && $event->start_at->lt($result->start_at))
            ? ' Note: this starts before ' . $prev->name . ' allotment result (' . $result->start_at->format('d M Y') . '). Check the dates.'
            : '';
    }
}

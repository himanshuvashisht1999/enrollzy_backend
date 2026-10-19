<?php

namespace App\Http\Controllers\Admin\Counselling;

use App\Http\Controllers\Controller;
use App\Models\Counselling\AllotmentResult;
use App\Models\Counselling\Round;
use App\Models\ExamModule\CycleEvent;
use App\Models\ExamModule\EventType;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;

/** Published allotment lists. Publishing one also declares the round's "Allotment result" event: one action, not two. */
class ResultController extends Controller
{
    public function store(Request $request, Round $round)
    {
        $data = $this->validated($request, null, $round);
        $result = DB::transaction(function () use ($data, $round) {
            $result = $round->results()->create($data);
            $this->declare($round, $result);
            return $result;
        });
        return back()->with('success', 'Result published. The round\'s allotment result is now marked as declared.');
    }

    public function update(Request $request, AllotmentResult $result)
    {
        $oldFile = $result->file;
        $data = $this->validated($request, $result, $result->round);
        DB::transaction(function () use ($result, $data) {
            $result->update($data);
            $this->declare($result->round, $result);
        });
        if ($oldFile && $oldFile !== $result->file) {
            Storage::disk('public')->delete($oldFile);
        }
        return back()->with('success', 'Result updated.');
    }

    public function destroy(AllotmentResult $result)
    {
        $file = $result->file;
        $result->delete();
        if ($file) {
            Storage::disk('public')->delete($file);
        }
        return back()->with('success', 'Result deleted. The round\'s allotment result status was not changed.');
    }

    private function validated(Request $request, ?AllotmentResult $existing, Round $round): array
    {
        $data = $request->validate([
            'title' => 'required|string|max:255',
            'result_type' => ['required', Rule::in(array_keys(AllotmentResult::TYPES))],
            'file' => 'nullable|file|mimes:pdf,xls,xlsx,csv,zip|max:20480',
            'url' => 'nullable|url|max:500',
            'published_at' => 'required|date',
            'source_url' => 'nullable|url|max:500',
        ]);
        // A result with nothing to open is not a result.
        if (!$request->hasFile('file') && !$request->filled('url') && !$existing?->file) {
            throw \Illuminate\Validation\ValidationException::withMessages(['url' => 'Upload the result file or give its URL.']);
        }
        foreach (['url', 'source_url'] as $f) {
            if (!empty($data[$f])) {
                $host = strtolower((string) parse_url($data[$f], PHP_URL_HOST));
                $own = array_map('strtolower', array_filter([$request->getHost(), parse_url(config('app.url'), PHP_URL_HOST)]));
                if (in_array($host, $own, true)) {
                    throw \Illuminate\Validation\ValidationException::withMessages([$f => 'Enter the official external URL, not a page of this CRM.']);
                }
            }
        }
        unset($data['file']);
        if ($request->hasFile('file')) {
            $data['file'] = $request->file('file')->store('cns/results', 'public');
        }
        $data['published_at'] = \Carbon\Carbon::parse($data['published_at'], $round->cycle->timezone ?: 'Asia/Kolkata');
        return $data;
    }

    /** Set (or create) the round's Allotment result event to Declared, pointing at this result. */
    private function declare(Round $round, AllotmentResult $result): void
    {
        $type = EventType::where('slug', 'allotment-result')->first();
        if (!$type) {
            return;
        }
        $link = $result->url ?: ($result->file ? asset('storage/' . $result->file) : null);
        $source = $result->source_url ?: $result->url;

        $event = $round->events()->where('event_type_id', $type->id)->orderByDesc('id')->first();
        $attrs = ['manual_status' => 'declared', 'date_precision' => 'exact', 'start_at' => $result->published_at, 'link_url' => $link,
            'source_url' => $source, 'note' => $result->title];
        if ($event) {
            $event->update($attrs);
        } else {
            $round->cycle->allEvents()->create($attrs + ['round_id' => $round->id, 'event_type_id' => $type->id]);
        }
    }
}

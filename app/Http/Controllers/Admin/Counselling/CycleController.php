<?php

namespace App\Http\Controllers\Admin\Counselling;

use App\Http\Controllers\Controller;
use App\Models\Counselling\Counselling;
use App\Models\Counselling\CounsellingCycle;
use App\Models\Counselling\Round;
use App\Models\Counselling\RoundType;
use App\Models\ExamModule\Cycle as ExamCycle;
use App\Models\ExamModule\EventType;
use App\Models\Counselling\Quota;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class CycleController extends Controller
{
    public function create(Counselling $counselling)
    {
        $cycle = new CounsellingCycle(['counselling_id' => $counselling->id, 'year' => now()->year, 'cycle_status' => 'scheduled', 'timezone' => 'Asia/Kolkata']);
        return view('admin.counselling.cycles.form', ['counselling' => $counselling, 'cycle' => $cycle, 'examCycles' => $this->examCycles($counselling), 'selected' => $this->suggestedExamCycles($counselling)]);
    }

    public function store(Request $request, Counselling $counselling)
    {
        $cycle = $counselling->cycles()->create($this->validated($request, $counselling));
        $cycle->examCycles()->sync($this->examCycleIds($request, $counselling));
        return redirect()->route('admin.counselling.cycles.show', $cycle)->with('success', 'Cycle created.');
    }

    /** Cycle workspace: status bar, cycle-level dates and rounds. (Further tabs arrive in Phase A2.) */
    public function show(CounsellingCycle $cycle)
    {
        $cycle->load(['counselling', 'rounds.type', 'examCycles.exam', 'events' => fn ($q) => $q->with(['type', 'quota'])->orderBy('start_at')]);
        return view('admin.counselling.cycles.show', [
            'cycle' => $cycle,
            'bar' => $cycle->statusBar(),
            'eventTypes' => EventType::where('status', true)->whereIn('applies_to', ['counselling', 'both'])->orderBy('sort_order')->get(),
            'quotas' => Quota::where('status', true)->orderBy('sort_order')->get(),
            'roundTypes' => RoundType::where('status', true)->orderBy('sort_order')->get(),
        ]);
    }

    public function edit(CounsellingCycle $cycle)
    {
        $counselling = $cycle->counselling;
        return view('admin.counselling.cycles.form', ['counselling' => $counselling, 'cycle' => $cycle, 'examCycles' => $this->examCycles($counselling),
            'selected' => $cycle->examCycles()->pluck('exm_cycles.id')->all()]);
    }

    public function update(Request $request, CounsellingCycle $cycle)
    {
        $cycle->update($this->validated($request, $cycle->counselling, $cycle));
        $cycle->examCycles()->sync($this->examCycleIds($request, $cycle->counselling));
        return redirect()->route('admin.counselling.cycles.show', $cycle)->with('success', 'Cycle updated.');
    }

    public function destroy(CounsellingCycle $cycle)
    {
        $counselling = $cycle->counselling;
        $cycle->delete();
        return redirect()->route('admin.counselling.counsellings.show', $counselling)->with('success', 'Cycle deleted.');
    }

    public function togglePublic(CounsellingCycle $cycle)
    {
        $cycle->update(['is_public' => !$cycle->is_public]);
        return back()->with('success', 'Cycle is now ' . ($cycle->is_public ? 'public' : 'admin only') . '.');
    }

    public function setCurrent(CounsellingCycle $cycle)
    {
        $cycle->update(['is_current' => true]);
        return back()->with('success', $cycle->title . ' is now the current cycle.');
    }

    public function copy(Request $request, CounsellingCycle $cycle)
    {
        $data = $request->validate(['year' => 'required|integer|min:2000|max:2100', 'session_label' => 'nullable|string|max:100']);
        $new = $cycle->copyForward((int) $data['year'], $data['session_label'] ?? null);

        $todo = $new->needsNewDates();
        return redirect()->route('admin.counselling.cycles.show', $new)
            ->with('success', 'Cycle copied without dates, bulletin or verification.' . ($todo ? ' ' . count($todo) . ' date(s) need a new value.' : ''));
    }

    public function verify(Request $request, CounsellingCycle $cycle)
    {
        $cycle->update(['last_verified_at' => now(), 'verified_by' => $request->user('admin')?->id]);
        return back()->with('success', 'Cycle marked as verified.');
    }

    // ---- Rounds ----

    public function storeRound(Request $request, CounsellingCycle $cycle)
    {
        $data = $this->roundData($request);
        $cycle->rounds()->create($data + ['sort_order' => $request->filled('sort_order') ? (int) $request->sort_order : ($cycle->rounds()->max('sort_order') ?? 0) + 1]);
        return back()->with('success', 'Round added.');
    }

    public function updateRound(Request $request, Round $round)
    {
        $round->update($this->roundData($request) + ['sort_order' => $request->filled('sort_order') ? (int) $request->sort_order : $round->sort_order]);
        return back()->with('success', 'Round updated.');
    }

    public function destroyRound(Round $round)
    {
        $cycle = $round->cycle;
        $round->delete();
        return redirect()->route('admin.counselling.cycles.show', $cycle)->with('success', 'Round deleted.');
    }

    /** Round workspace: dates now; rules, fees, documents, seats, results and cutoffs arrive in A2 and B. */
    public function showRound(Round $round)
    {
        $round->load(['cycle.counselling', 'type', 'events' => fn ($q) => $q->with(['type', 'quota'])->orderBy('start_at')]);
        return view('admin.counselling.rounds.show', [
            'round' => $round, 'cycle' => $round->cycle, 'previous' => $round->previous(), 'next' => $round->next(),
            'eventTypes' => EventType::where('status', true)->whereIn('applies_to', ['counselling', 'both'])->orderBy('sort_order')->get(),
            'quotas' => Quota::where('status', true)->orderBy('sort_order')->get(),
            'roundTypes' => RoundType::where('status', true)->orderBy('sort_order')->get(),
        ]);
    }

    // ---- internals ----

    private function roundData(Request $request): array
    {
        $data = $request->validate([
            'name' => 'required|string|max:150',
            'round_type_id' => ['required', Rule::exists('cns_round_types', 'id')],
            'quotas' => 'nullable|array',
            'quotas.*' => ['integer', Rule::exists('cns_quotas', 'id')],
            'round_status' => ['required', Rule::in(array_keys(CounsellingCycle::STATUSES))],
            'status_note' => 'nullable|string|max:255',
            'fresh_registration_allowed' => 'nullable|in:,0,1',
            'choices_carry_forward' => 'nullable|in:,0,1',
            'willingness_options' => 'nullable|array',
            'willingness_options.*' => [Rule::in(array_keys(CounsellingCycle::WILLINGNESS))],
            'instructions' => 'nullable|string',
            'notice_url' => 'nullable|url|max:500',
            'source_url' => 'nullable|url|max:500',
            'sort_order' => 'nullable|integer|min:0',
        ]);
        unset($data['sort_order']);
        // Blank = inherit (null); "0" / "1" = explicit override.
        foreach (['fresh_registration_allowed', 'choices_carry_forward'] as $f) {
            $data[$f] = ($data[$f] ?? '') === '' ? null : (bool) $data[$f];
        }
        $data['quotas'] = array_map('intval', $data['quotas'] ?? []) ?: null;
        $data['willingness_options'] = array_values($data['willingness_options'] ?? []) ?: null;
        return $data;
    }

    private function validated(Request $request, Counselling $counselling, ?CounsellingCycle $cycle = null): array
    {
        $data = $request->validate([
            'year' => 'required|integer|min:2000|max:2100',
            'session_label' => 'nullable|string|max:100',
            'cycle_status' => ['required', Rule::in(array_keys(CounsellingCycle::STATUSES))],
            'status_note' => 'nullable|string|max:255',
            'timezone' => ['required', Rule::in(\DateTimeZone::listIdentifiers())],
            'information_bulletin_url' => 'nullable|url|max:500',
            'information_bulletin_pdf' => 'nullable|file|mimes:pdf|max:20480',
            'reservation_policy' => 'nullable|string',
            'seat_allotment_method' => 'nullable|string',
            'willingness_options' => 'nullable|array',
            'willingness_options.*' => [Rule::in(array_keys(CounsellingCycle::WILLINGNESS))],
            'source_url' => 'nullable|url|max:500',
        ]);
        $data['is_current'] = $request->boolean('is_current');
        $data['willingness_options'] = array_values($data['willingness_options'] ?? []) ?: null;
        unset($data['information_bulletin_pdf']);
        if ($request->hasFile('information_bulletin_pdf')) {
            $data['information_bulletin_pdf'] = $request->file('information_bulletin_pdf')->store('cns/bulletins', 'public');
        }
        return $data;
    }

    /** Exam cycles of the exams mapped to this counselling. */
    private function examCycles(Counselling $counselling)
    {
        return ExamCycle::with('exam')->whereIn('exam_id', $counselling->mappings()->pluck('exam_id'))->orderByDesc('year')->get();
    }

    /** Pre-select each mapped exam's current cycle for a new counselling year. */
    private function suggestedExamCycles(Counselling $counselling): array
    {
        return ExamCycle::whereIn('exam_id', $counselling->mappings()->pluck('exam_id'))->where('is_current', true)->pluck('id')->all();
    }

    private function examCycleIds(Request $request, Counselling $counselling): array
    {
        $allowed = $counselling->mappings()->pluck('exam_id');
        return ExamCycle::whereIn('id', (array) $request->input('exam_cycle_ids', []))->whereIn('exam_id', $allowed)->pluck('id')->all();
    }
}

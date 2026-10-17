<?php

namespace App\Http\Controllers\Admin\ExamModule;

use App\Http\Controllers\Controller;
use App\Models\ExamModule\Cycle;
use App\Models\ExamModule\EventType;
use App\Models\ExamModule\Exam;
use App\Models\ExamModule\Stage;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class CycleController extends Controller
{
    public function create(Exam $exam)
    {
        $cycle = new Cycle(['exam_id' => $exam->id, 'year' => now()->year, 'registration_mode' => 'window',
            'result_mode' => 'fixed_date', 'cycle_status' => 'scheduled', 'timezone' => 'Asia/Kolkata']);
        return view('admin.exam-module.cycles.form', compact('exam', 'cycle'));
    }

    public function store(Request $request, Exam $exam)
    {
        $cycle = $exam->cycles()->create($this->validated($request));
        return redirect()->route('admin.exam-module.cycles.show', $cycle)->with('success', 'Cycle created.');
    }

    /** Cycle workspace: details, stages and the events table. */
    public function show(Cycle $cycle)
    {
        $cycle->load(['exam', 'stages', 'events' => fn ($q) => $q->with(['type', 'stage'])->orderBy('start_at')]);
        $eventTypes = EventType::where('status', true)->orderBy('sort_order')->get();
        return view('admin.exam-module.cycles.show', compact('cycle', 'eventTypes'));
    }

    public function edit(Cycle $cycle)
    {
        return view('admin.exam-module.cycles.form', ['exam' => $cycle->exam, 'cycle' => $cycle]);
    }

    public function update(Request $request, Cycle $cycle)
    {
        $cycle->update($this->validated($request, $cycle));
        return redirect()->route('admin.exam-module.cycles.show', $cycle)->with('success', 'Cycle updated.');
    }

    public function destroy(Cycle $cycle)
    {
        $exam = $cycle->exam;
        $cycle->delete();
        return redirect()->route('admin.exam-module.exams.show', $exam)->with('success', 'Cycle deleted.');
    }

    public function togglePublic(Cycle $cycle)
    {
        $cycle->update(['is_public' => !$cycle->is_public]);
        return back()->with('success', 'Cycle is now ' . ($cycle->is_public ? 'public' : 'admin only') . '.');
    }

    public function setCurrent(Cycle $cycle)
    {
        $cycle->update(['is_current' => true]);
        return back()->with('success', $cycle->title . ' is now the current cycle.');
    }

    public function copy(Request $request, Cycle $cycle)
    {
        $data = $request->validate(['year' => 'required|integer|min:2000|max:2100', 'session_label' => 'nullable|string|max:100']);
        $new = $cycle->copyForward((int) $data['year'], $data['session_label'] ?? null);
        return redirect()->route('admin.exam-module.cycles.show', $new)
            ->with('success', 'Cycle copied. Stages and events were copied without dates; update what changed.');
    }

    // ---- Stages ----

    public function storeStage(Request $request, Cycle $cycle)
    {
        $cycle->stages()->create($this->stageData($request) + ['sort_order' => $cycle->stages()->max('sort_order') + 1]);
        return back()->with('success', 'Stage added.');
    }

    public function updateStage(Request $request, Stage $stage)
    {
        $stage->update($this->stageData($request));
        return back()->with('success', 'Stage updated.');
    }

    public function destroyStage(Stage $stage)
    {
        $stage->delete();
        return back()->with('success', 'Stage deleted. Its events are kept without a stage.');
    }

    private function stageData(Request $request): array
    {
        $data = $request->validate(['name' => 'required|string|max:150', 'sort_order' => 'nullable|integer|min:0']);
        $data['is_qualifying'] = $request->boolean('is_qualifying');
        return array_filter($data, fn ($v) => $v !== null);
    }

    private function validated(Request $request, ?Cycle $cycle = null): array
    {
        $data = $request->validate([
            'year' => 'required|integer|min:2000|max:2100',
            'session_label' => 'nullable|string|max:100',
            'registration_mode' => ['required', Rule::in(array_keys(Cycle::REGISTRATION_MODES))],
            'result_mode' => ['required', Rule::in(array_keys(Cycle::RESULT_MODES))],
            'cycle_status' => ['required', Rule::in(array_keys(Cycle::STATUSES))],
            'status_note' => 'nullable|string|max:255',
            'timezone' => ['required', Rule::in(\DateTimeZone::listIdentifiers())],
        ]);
        $data['is_current'] = $request->boolean('is_current');
        return $data;
    }
}

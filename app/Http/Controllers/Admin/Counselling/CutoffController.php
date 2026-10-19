<?php

namespace App\Http\Controllers\Admin\Counselling;

use App\Http\Controllers\Controller;
use App\Models\Counselling\Category;
use App\Models\Counselling\Cutoff;
use App\Models\Counselling\Quota;
use App\Models\Counselling\Round;
use App\Models\ExamModule\Exam;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class CutoffController extends Controller
{
    public function index(Request $request, Round $round)
    {
        $round->load('cycle.counselling.mappings.exam');
        $cutoffs = Cutoff::with(['exam:id,name', 'organisation:id,name', 'course:id,name', 'quota:id,name', 'category:id,name'])
            ->where('round_id', $round->id)
            ->when($request->exam, fn ($q, $v) => $q->where('exam_id', $v))
            ->when($request->quota, fn ($q, $v) => $q->where('quota_id', $v))
            ->when($request->category, fn ($q, $v) => $q->where('category_id', $v))
            ->when($request->gender, fn ($q, $v) => $v === 'neutral' ? $q->whereNull('gender') : $q->where('gender', $v))
            ->when($request->search, fn ($q, $s) => $q->where(fn ($q) => $q
                ->whereIn('organisation_id', \App\Models\Organisation::where('name', 'like', "%$s%")->select('id'))
                ->orWhereIn('course_id', \App\Models\Course::where('name', 'like', "%$s%")->select('id'))))
            ->orderBy('organisation_id')->orderBy('course_id')->orderBy('category_id')->paginate(50)->withQueryString();

        return view('admin.counselling.cutoffs.index', [
            'round' => $round, 'cutoffs' => $cutoffs,
            'exams' => $round->cycle->counselling->mappings->pluck('exam')->filter(),
            'quotas' => Quota::where('status', true)->orderBy('sort_order')->get(['id', 'name']),
            'categories' => Category::where('status', true)->orderBy('sort_order')->get(['id', 'name']),
            'total' => Cutoff::where('round_id', $round->id)->count(),
        ]);
    }

    public function store(Request $request, Round $round)
    {
        Cutoff::create($this->validated($request, $round) + ['round_id' => $round->id]);
        return back()->with('success', 'Cutoff added.');
    }

    public function update(Request $request, Cutoff $cutoff)
    {
        $cutoff->update($this->validated($request, $cutoff->round, $cutoff));
        return back()->with('success', 'Cutoff updated.');
    }

    public function destroy(Cutoff $cutoff)
    {
        $cutoff->delete();
        return back()->with('success', 'Cutoff deleted.');
    }

    /** Delete every cutoff of the round that matches the current filters (after a confirmation in the UI). */
    public function clear(Round $round)
    {
        $n = Cutoff::where('round_id', $round->id)->count();
        Cutoff::where('round_id', $round->id)->delete();
        \App\Models\ExamModule\AuditLog::create(['auditable_type' => Cutoff::class, 'auditable_id' => $round->id, 'user_id' => auth('admin')->id(),
            'action' => 'deleted', 'old_values' => ['round_id' => $round->id, 'rows_deleted' => $n], 'new_values' => null]);
        return back()->with('success', "$n cutoffs deleted from this round.");
    }

    private function validated(Request $request, Round $round, ?Cutoff $existing = null): array
    {
        $mapped = $round->cycle->counselling->mappings()->pluck('exam_id');
        $data = $request->validate([
            // Cutoffs must belong to one of the counselling's mapped exams.
            'exam_id' => ['required', Rule::in($mapped->all())],
            'organisation_id' => ['required', Rule::exists('organisations', 'id')],
            'course_id' => ['required', Rule::exists('courses', 'id')],
            'quota_id' => ['required', Rule::exists('cns_quotas', 'id')],
            'category_id' => ['required', Rule::exists('cns_categories', 'id')],
            'gender' => ['nullable', Rule::in(['male', 'female'])],
            'rank_type' => ['required', Rule::in(array_keys(Cutoff::RANK_TYPES))],
            'opening_value' => 'nullable|numeric|min:0',
            'closing_value' => 'required|numeric|min:0',
        ], ['exam_id.in' => 'Pick one of the exams mapped to this counselling.']);

        $data['gender'] = $data['gender'] ?? null;
        $data['pwd'] = $request->boolean('pwd');

        if (isset($data['opening_value'])) {
            $worse = in_array($data['rank_type'], Cutoff::HIGHER_IS_WORSE);
            if ($worse && $data['closing_value'] < $data['opening_value']) {
                throw \Illuminate\Validation\ValidationException::withMessages(['closing_value' => 'Closing rank must be at least the opening rank.']);
            }
            if (!$worse && $data['closing_value'] > $data['opening_value']) {
                throw \Illuminate\Validation\ValidationException::withMessages(['closing_value' => 'Closing value must be at most the opening value for scores and percentiles.']);
            }
        }

        $clash = Cutoff::where('round_id', $round->id)->where($data['gender'] === null ? fn ($q) => $q->whereNull('gender') : fn ($q) => $q->where('gender', $data['gender']))
            ->where(array_intersect_key($data, array_flip(['exam_id', 'organisation_id', 'course_id', 'quota_id', 'category_id', 'rank_type'])) + ['pwd' => $data['pwd']])
            ->when($existing, fn ($q) => $q->where('id', '!=', $existing->id))->exists();
        if ($clash) {
            throw \Illuminate\Validation\ValidationException::withMessages(['category_id' => 'A cutoff for this exam, college, course, quota, category, gender and rank type already exists in the round.']);
        }
        return $data;
    }
}

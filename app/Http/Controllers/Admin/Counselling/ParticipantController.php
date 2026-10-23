<?php

namespace App\Http\Controllers\Admin\Counselling;

use App\Http\Controllers\Controller;
use App\Models\Counselling\CounsellingCycle;
use App\Models\Counselling\Participant;
use App\Models\Counselling\Quota;
use App\Support\Counselling\CnsBlocks;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

/** Participating institutes of a cycle: college + course + quota. Bulk data comes in through the import centre. */
class ParticipantController extends Controller
{
    public function index(Request $request, CounsellingCycle $cycle)
    {
        $participants = Participant::with(['organisation:id,name', 'course:id,name', 'quota:id,name'])
            ->where('cycle_id', $cycle->id)
            ->when($request->quota, fn ($q, $v) => $q->where('quota_id', $v))
            ->when($request->institute_type, fn ($q, $v) => $q->where('institute_type', $v))
            ->when($request->search, fn ($q, $s) => $q->where(fn ($q) => $q->where('institute_code', 'like', "%$s%")
                ->orWhereIn('organisation_id', \App\Models\Organisation::where('name', 'like', "%$s%")->select('id'))
                ->orWhereIn('course_id', \App\Models\Course::where('name', 'like', "%$s%")->select('id'))))
            ->orderBy('organisation_id')->orderBy('course_id')->paginate(40)->withQueryString();

        return view('admin.counselling.participants.index', [
            'cycle' => $cycle->load('counselling'), 'participants' => $participants,
            'quotas' => Quota::where('status', true)->orderBy('sort_order')->get(['id', 'name']),
            'total' => Participant::where('cycle_id', $cycle->id)->count(),
        ]);
    }

    public function store(Request $request, CounsellingCycle $cycle)
    {
        Participant::create($this->validated($request, $cycle) + ['cycle_id' => $cycle->id]);
        return back()->with('success', 'Participant added.');
    }

    public function update(Request $request, Participant $participant)
    {
        $participant->update($this->validated($request, $participant->cycle, $participant));
        return back()->with('success', 'Participant updated.');
    }

    public function destroy(Participant $participant)
    {
        $participant->delete();
        return back()->with('success', 'Participant removed.');
    }

    private function validated(Request $request, CounsellingCycle $cycle, ?Participant $existing = null): array
    {
        return $request->validate([
            'organisation_id' => ['required', Rule::exists('organisations', 'id')],
            'course_id' => ['required', Rule::exists('courses', 'id')],
            'quota_id' => ['required', Rule::exists('cns_quotas', 'id'),
                // one college + course + quota per cycle
                Rule::unique('cns_participants', 'quota_id')->where('cycle_id', $cycle->id)->where('organisation_id', $request->organisation_id)
                    ->where('course_id', $request->course_id)->ignore($existing?->id)],
            'institute_type' => ['nullable', Rule::in(array_keys(CnsBlocks::INSTITUTE_TYPES))],
            'institute_code' => 'nullable|string|max:60',
            'annual_tuition_fee' => 'nullable|numeric|min:0',
            'other_fees' => 'nullable|numeric|min:0',
            'bond_details' => 'nullable|string|max:2000',
            'total_seats' => 'nullable|integer|min:0',
        ], ['quota_id.unique' => 'This college, course and quota is already a participant of this cycle.']);
    }
}

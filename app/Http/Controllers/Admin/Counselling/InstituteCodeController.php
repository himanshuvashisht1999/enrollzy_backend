<?php

namespace App\Http\Controllers\Admin\Counselling;

use App\Http\Controllers\Controller;
use App\Models\Counselling\InstituteCode;
use App\Models\ExamModule\ConductingBody;
use App\Models\Organisation;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

/** Authority codes for colleges (MCC code, JoSAA code, state codes). Used to match CSV imports in Phase B. */
class InstituteCodeController extends Controller
{
    public function index(Request $request)
    {
        $codes = InstituteCode::with(['organisation:id,name', 'conductingBody:id,name'])
            ->when($request->body, fn ($q, $b) => $q->where('conducting_body_id', $b))
            ->when($request->search, fn ($q, $s) => $q->where(fn ($q) => $q->where('code', 'like', "%$s%")
                ->orWhereIn('organisation_id', Organisation::where('name', 'like', "%$s%")->select('id'))))
            ->orderBy('conducting_body_id')->orderBy('code')->paginate(30)->withQueryString();

        return view('admin.counselling.codes.index', [
            'codes' => $codes,
            'bodies' => ConductingBody::where('status', true)->orderBy('name')->get(['id', 'name']),
            'organisations' => Organisation::orderBy('name')->get(['id', 'name']),
        ]);
    }

    public function store(Request $request)
    {
        InstituteCode::create($this->validated($request));
        return back()->with('success', 'Code added.');
    }

    public function update(Request $request, InstituteCode $code)
    {
        $code->update($this->validated($request, $code));
        return back()->with('success', 'Code updated.');
    }

    public function destroy(InstituteCode $code)
    {
        $code->delete();
        return back()->with('success', 'Code deleted.');
    }

    private function validated(Request $request, ?InstituteCode $code = null): array
    {
        return $request->validate([
            'organisation_id' => ['required', Rule::exists('organisations', 'id')],
            'conducting_body_id' => ['required', Rule::exists('exm_conducting_bodies', 'id')],
            // A code identifies one college within an authority.
            'code' => ['required', 'string', 'max:60', Rule::unique('cns_institute_codes', 'code')
                ->where('conducting_body_id', $request->conducting_body_id)->ignore($code?->id)],
            'valid_from_year' => 'nullable|integer|min:1990|max:2100',
        ], ['code.unique' => 'This authority already uses this code for a college.']);
    }
}

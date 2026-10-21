<?php

namespace App\Http\Controllers\Admin\Counselling;

use App\Http\Controllers\Controller;
use App\Models\Counselling\InstituteCode;
use App\Models\Organisation;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

/** Authority codes for colleges (MCC code, JoSAA code, state codes). Used to match CSV imports in Phase B. */
class InstituteCodeController extends Controller
{
    public function index(Request $request)
    {
        $codes = InstituteCode::with(['organisation:id,name', 'conductingBody:id,name', 'legacyConductingBody:id,name'])
            ->when($request->body, fn ($q, $b) => $q->where('conducting_organisation_id', $b))
            ->when($request->search, fn ($q, $s) => $q->where(fn ($q) => $q->where('code', 'like', "%$s%")
                ->orWhereIn('organisation_id', Organisation::where('name', 'like', "%$s%")->select('id'))))
            ->orderBy('conducting_organisation_id')->orderBy('code')->paginate(30)->withQueryString();

        return view('admin.counselling.codes.index', [
            'codes' => $codes,
            // Counselling bodies = organisations of type "Counselling Body".
            'bodies' => Organisation::where('organisation_type_id', Organisation::TYPE_COUNSELLING_BODY)->orderBy('name')->get(['id', 'name']),
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
            'conducting_organisation_id' => ['required', Rule::exists('organisations', 'id')->where('organisation_type_id', Organisation::TYPE_COUNSELLING_BODY)],
            // A code identifies one college within an authority.
            'code' => ['required', 'string', 'max:60', Rule::unique('cns_institute_codes', 'code')
                ->where('conducting_organisation_id', $request->conducting_organisation_id)->ignore($code?->id)],
            'valid_from_year' => 'nullable|integer|min:1990|max:2100',
        ], ['code.unique' => 'This authority already uses this code for a college.', 'conducting_organisation_id.required' => 'Pick the authority (an organisation of type Counselling Body).']);
    }
}

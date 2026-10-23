<?php

namespace App\Http\Controllers\Admin\Quality;

use App\Http\Controllers\Controller;
use App\Models\Quality\OrganisationGroup;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

/** Brand groups (Amity, SRM...) that own several legally separate universities. */
class GroupController extends Controller
{
    public function index()
    {
        $groups = OrganisationGroup::withCount('organisations')->with(['organisations' => fn ($q) => $q->select('id', 'name', 'group_id')->orderBy('name')])->orderBy('name')->get();
        return view('admin.quality.groups', compact('groups'));
    }

    public function store(Request $request)
    {
        OrganisationGroup::create($this->validated($request));
        return back()->with('success', 'Group added.');
    }

    public function update(Request $request, OrganisationGroup $group)
    {
        $group->update($this->validated($request, $group));
        return back()->with('success', 'Group updated.');
    }

    public function destroy(OrganisationGroup $group)
    {
        if ($group->organisations()->exists()) {
            return back()->with('error', 'This group still has organisations. Move them out first, or mark it inactive.');
        }
        $group->delete();
        return back()->with('success', 'Group deleted.');
    }

    private function validated(Request $request, ?OrganisationGroup $group = null): array
    {
        $d = $request->validate([
            'name' => ['required', 'string', 'max:255', Rule::unique('organisation_groups', 'name')->ignore($group?->id)],
            'website' => ['nullable', 'url', 'max:255'],
            'notes' => ['nullable', 'string', 'max:2000'],
        ]);
        $d['status'] = $request->boolean('status', true);
        return $d;
    }
}

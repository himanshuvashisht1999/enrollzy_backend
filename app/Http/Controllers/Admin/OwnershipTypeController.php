<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Organisation;
use App\Models\OwnershipType;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

/**
 * Masters → Ownership Types: the choices for "Ownership Type" on the Institute form (one page: list, add, edit inline,
 * delete). Renaming a type also renames it on the organisations that use it.
 */
class OwnershipTypeController extends Controller
{
    public function index()
    {
        $types = OwnershipType::orderBy('sort_order')->orderBy('title')->get();
        $used = Organisation::whereIn('ownership_type', $types->pluck('title'))->selectRaw('ownership_type, COUNT(*) n')
            ->groupBy('ownership_type')->pluck('n', 'ownership_type');
        return view('admin.ownership_types.index', compact('types', 'used'));
    }

    public function store(Request $request)
    {
        $data = $this->validated($request);
        OwnershipType::create($data);
        return redirect()->route('admin.ownership-types.index')->with('success', 'Ownership type added.');
    }

    public function update(Request $request, OwnershipType $ownershipType)
    {
        $data = $this->validated($request, $ownershipType);
        if ($data['title'] !== $ownershipType->title) {
            Organisation::where('ownership_type', $ownershipType->title)->update(['ownership_type' => $data['title']]);
        }
        $ownershipType->update($data);
        return redirect()->route('admin.ownership-types.index')->with('success', 'Ownership type updated.');
    }

    public function destroy(OwnershipType $ownershipType)
    {
        $inUse = Organisation::where('ownership_type', $ownershipType->title)->count();
        if ($inUse) {
            return redirect()->route('admin.ownership-types.index')
                ->with('error', "\"{$ownershipType->title}\" is used by {$inUse} " . ($inUse === 1 ? 'organisation' : 'organisations') . '. Turn it off instead, or change those organisations first.');
        }
        $ownershipType->delete();
        return redirect()->route('admin.ownership-types.index')->with('success', 'Ownership type deleted.');
    }

    private function validated(Request $request, ?OwnershipType $type = null): array
    {
        $data = $request->validate([
            'title' => ['required', 'string', 'max:100', Rule::unique('ownership_types', 'title')->ignore($type?->id)],
            'sort_order' => 'nullable|integer|min:0|max:9999',
        ]);
        $data['title'] = trim($data['title']);
        $data['sort_order'] = (int) ($data['sort_order'] ?? 0);
        $data['status'] = $request->boolean('status');
        return $data;
    }
}

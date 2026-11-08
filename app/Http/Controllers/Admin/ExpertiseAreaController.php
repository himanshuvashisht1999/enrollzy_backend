<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ExpertiseArea;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

/**
 * Experts > Expertise areas: the list experts pick from (CRM expert form, the website's application form) and the
 * website's experts page filters by. Experts keep the names, so renaming an area renames it on every expert and
 * application; an area in use can be hidden, not deleted.
 */
class ExpertiseAreaController extends Controller
{
    public function index()
    {
        $areas = ExpertiseArea::orderBy('sort_order')->orderBy('name')->get()
            ->each(fn ($a) => $a->used = $this->usedBy($a->name));
        return view('admin.experts.expertise-areas', compact('areas'));
    }

    public function store(Request $request)
    {
        $data = $request->validate(['name' => 'required|string|max:100|unique:expert_expertise_areas,name'], ['name.unique' => 'This expertise area already exists.']);
        ExpertiseArea::create(['name' => trim($data['name']), 'sort_order' => (int) ExpertiseArea::max('sort_order') + 1, 'status' => true]);
        return back()->with('success', 'Expertise area added.');
    }

    public function update(Request $request, ExpertiseArea $area)
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:100', Rule::unique('expert_expertise_areas', 'name')->ignore($area->id)],
            'sort_order' => 'nullable|integer|min:0',
        ]);
        $old = $area->name;
        $new = trim($data['name']);
        DB::transaction(function () use ($area, $data, $old, $new, $request) {
            $area->update(['name' => $new, 'sort_order' => (int) ($data['sort_order'] ?? $area->sort_order), 'status' => $request->boolean('status')]);
            if ($old !== $new) {
                foreach (['experts', 'expert_applications'] as $table) $this->rename($table, $old, $new);
            }
        });
        return back()->with('success', $old !== $new ? "Renamed to \"$new\" on every expert and application." : 'Expertise area saved.');
    }

    public function destroy(ExpertiseArea $area)
    {
        if ($n = $this->usedBy($area->name)) {
            return back()->with('error', "$n " . ($n === 1 ? 'expert or application uses' : 'experts or applications use') . ' this area. Hide it instead (untick Active).');
        }
        $area->delete();
        return back()->with('success', 'Expertise area deleted.');
    }

    /** experts and applications that have this area */
    private function usedBy(string $name): int
    {
        // read in PHP: names may be stored with escaped characters, so a LIKE on the text could miss them
        return collect(['experts', 'expert_applications'])->sum(fn ($t) => DB::table($t)->whereNotNull('expertise_areas')->get(['expertise_areas'])
            ->filter(fn ($r) => in_array($name, (array) json_decode((string) $r->expertise_areas, true), true))->count());
    }

    private function rename(string $table, string $old, string $new): void
    {
        DB::table($table)->whereNotNull('expertise_areas')->get(['id', 'expertise_areas'])
            ->each(function ($r) use ($table, $old, $new) {
                $list = (array) json_decode((string) $r->expertise_areas, true);
                if (!in_array($old, $list, true)) return;
                $list = array_values(array_unique(array_map(fn ($x) => $x === $old ? $new : $x, $list)));
                DB::table($table)->where('id', $r->id)->update(['expertise_areas' => json_encode($list, JSON_UNESCAPED_UNICODE)]);
            });
    }
}

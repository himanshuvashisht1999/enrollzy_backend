<?php

namespace App\Http\Controllers\Admin\Quality;

use App\Http\Controllers\Controller;
use App\Models\Campus;
use App\Models\Organisation;
use App\Services\Quality\OrgMover;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

/** Split a multi-university brand: move chosen campuses (with departments and programmes) to another, or a new, organisation. */
class MoveController extends Controller
{
    public function index(Request $request)
    {
        $from = $request->filled('from') ? Organisation::find($request->query('from')) : null;
        $campuses = $from ? Campus::where('organisation_id', $from->id)->orderBy('campus_name')->get() : collect();
        $counts = [];
        if ($campuses->isNotEmpty()) {
            $ids = $campuses->pluck('id');
            $dep = DB::table('departments')->whereIn('campus_id', $ids)->selectRaw('campus_id, COUNT(*) n')->groupBy('campus_id')->pluck('n', 'campus_id');
            $crs = DB::table('organisation_courses')->whereIn('campus_id', $ids)->selectRaw('campus_id, COUNT(*) n')->groupBy('campus_id')->pluck('n', 'campus_id');
            foreach ($ids as $id) $counts[$id] = ['departments' => $dep[$id] ?? 0, 'courses' => $crs[$id] ?? 0];
        }
        return view('admin.quality.moves', [
            'from' => $from, 'campuses' => $campuses, 'counts' => $counts,
            'history' => DB::table('organisation_moves')->orderByDesc('id')->limit(20)->get(),
            'orgNames' => Organisation::whereIn('id', DB::table('organisation_moves')->pluck('from_organisation_id')->merge(DB::table('organisation_moves')->pluck('to_organisation_id')))->pluck('name', 'id'),
            'preview' => session('move_preview'),
        ]);
    }

    /** Organisation picker for select2. */
    public function search(Request $request)
    {
        $q = trim((string) $request->query('q'));
        if (mb_strlen($q) < 2) return response()->json([]);
        return response()->json(Organisation::where('name', 'like', '%' . $q . '%')->orderBy('name')->limit(30)->get(['id', 'name'])
            ->map(fn ($o) => ['id' => $o->id, 'text' => $o->name . ' (#' . $o->id . ')']));
    }

    /** Step 1: show exactly what would move, change nothing. */
    public function preview(Request $request, OrgMover $mover)
    {
        $d = $this->validated($request);
        $from = Organisation::findOrFail($d['from_organisation_id']);
        $to = $d['target_mode'] === 'existing' ? Organisation::findOrFail($d['to_organisation_id']) : null;
        $p = $mover->preview($from, $to, $d['campus_ids']);
        $p['request'] = $d + ['to_name' => $to?->name];
        return redirect()->route('admin.quality.moves.index', ['from' => $from->id])->with('move_preview', $p);
    }

    /** Step 2: do it. */
    public function apply(Request $request, OrgMover $mover)
    {
        $d = $this->validated($request);
        $from = Organisation::findOrFail($d['from_organisation_id']);
        $created = false;
        try {
            if ($d['target_mode'] === 'new') {
                $to = $this->createTarget($from, $d['new_name']);
                $created = true;
            } else {
                $to = Organisation::findOrFail($d['to_organisation_id']);
            }
            $id = $mover->apply($from, $to, $d['campus_ids'], auth('admin')->id(), $created, $d['note'] ?? null);
        } catch (\RuntimeException $e) {
            return redirect()->route('admin.quality.moves.index', ['from' => $from->id])->with('error', $e->getMessage());
        }
        return redirect()->route('admin.quality.moves.index', ['from' => $from->id])->with('success', "Move #$id applied: " . count($d['campus_ids']) . " campus(es) now belong to {$to->name}. You can undo it below.");
    }

    public function undo(int $move, OrgMover $mover)
    {
        try { $mover->undo($move); } catch (\RuntimeException $e) { return back()->with('error', $e->getMessage()); }
        return back()->with('success', "Move #$move undone.");
    }

    private function validated(Request $request): array
    {
        return $request->validate([
            'from_organisation_id' => ['required', 'exists:organisations,id'],
            'target_mode' => ['required', 'in:existing,new'],
            'to_organisation_id' => ['required_if:target_mode,existing', 'nullable', 'exists:organisations,id'],
            'new_name' => ['required_if:target_mode,new', 'nullable', 'string', 'max:255', 'unique:organisations,name'],
            'campus_ids' => ['required', 'array', 'min:1'],
            'campus_ids.*' => ['string'],
            'note' => ['nullable', 'string', 'max:1000'],
        ]);
    }

    /** A new legal entity starts as a copy of the source's type and group; every other field is left for the admin to fill. */
    private function createTarget(Organisation $from, string $name): Organisation
    {
        $o = new Organisation();
        $o->name = $name;
        $o->organisation_type_id = $from->organisation_type_id;
        $o->save();
        DB::table('organisations')->where('id', $o->id)->update(['group_id' => $from->group_id]);
        return $o->fresh();
    }
}

<?php

namespace App\Http\Controllers\Admin\Counselling;

use App\Http\Controllers\Controller;
use App\Models\Counselling\Category;
use App\Models\Counselling\Quota;
use App\Models\Counselling\Round;
use App\Models\Counselling\SeatMatrixRow;
use App\Models\Counselling\SeatMatrixVersion;
use Illuminate\Http\Request;

/** Seat matrix versions of a round. Rows arrive by CSV import; here you view, compare, set live or delete. */
class SeatMatrixController extends Controller
{
    public function index(Round $round)
    {
        $round->load('cycle.counselling');
        $versions = SeatMatrixVersion::where('round_id', $round->id)->orderByDesc('version_no')->get();
        // Each version is compared with the one before it.
        $diffs = [];
        foreach ($versions as $v) {
            $older = $versions->first(fn ($o) => $o->version_no < $v->version_no);
            $diffs[$v->id] = $older ? $this->counts($v->diffAgainst($older)) : null;
        }
        return view('admin.counselling.seats.index', compact('round', 'versions', 'diffs'));
    }

    public function show(Request $request, SeatMatrixVersion $version)
    {
        $version->load('round.cycle.counselling');
        $rows = SeatMatrixRow::with(['organisation:id,name', 'course:id,name', 'quota:id,name', 'category:id,name'])
            ->where('version_id', $version->id)
            ->when($request->quota, fn ($q, $v) => $q->where('quota_id', $v))
            ->when($request->category, fn ($q, $v) => $q->where('category_id', $v))
            ->when($request->search, fn ($q, $s) => $q->where(fn ($q) => $q
                ->whereIn('organisation_id', \App\Models\Organisation::where('name', 'like', "%$s%")->select('id'))
                ->orWhereIn('course_id', \App\Models\Course::where('name', 'like', "%$s%")->select('id'))))
            ->orderBy('organisation_id')->orderBy('course_id')->paginate(60)->withQueryString();

        return view('admin.counselling.seats.show', [
            'version' => $version, 'rows' => $rows, 'totalSeats' => (int) SeatMatrixRow::where('version_id', $version->id)->sum('seats'),
            'quotas' => Quota::orderBy('sort_order')->get(['id', 'name']), 'categories' => Category::orderBy('sort_order')->get(['id', 'name']),
        ]);
    }

    /** What changed between a version and the one before it. */
    public function diff(SeatMatrixVersion $version)
    {
        $version->load('round.cycle.counselling');
        $older = SeatMatrixVersion::where('round_id', $version->round_id)->where('version_no', '<', $version->version_no)->orderByDesc('version_no')->first();
        abort_unless($older, 404, 'There is no earlier version to compare with.');
        $diff = $version->diffAgainst($older);
        foreach (['added', 'removed'] as $k) {
            $diff[$k]->load(['organisation:id,name', 'course:id,name', 'quota:id,name', 'category:id,name']);
        }
        $diff['changed']->each(fn ($c) => $c['row']->load(['organisation:id,name', 'course:id,name', 'quota:id,name', 'category:id,name']));

        return view('admin.counselling.seats.diff', compact('version', 'older', 'diff'));
    }

    public function makeLive(SeatMatrixVersion $version)
    {
        $version->makeLive();
        return back()->with('success', 'Version ' . $version->version_no . ' is now the live seat matrix.');
    }

    public function destroy(SeatMatrixVersion $version)
    {
        $roundId = $version->round_id;
        $wasLive = $version->is_live;
        $version->delete();   // rows cascade
        if ($wasLive) {
            SeatMatrixVersion::where('round_id', $roundId)->orderByDesc('version_no')->first()?->makeLive();
        }
        return back()->with('success', 'Version deleted.' . ($wasLive ? ' The latest remaining version is now live.' : ''));
    }

    private function counts(array $d): array
    {
        return ['added' => $d['added']->count(), 'removed' => $d['removed']->count(), 'changed' => $d['changed']->count()];
    }
}

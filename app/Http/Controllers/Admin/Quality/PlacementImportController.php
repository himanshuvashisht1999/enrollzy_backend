<?php

namespace App\Http\Controllers\Admin\Quality;

use App\Http\Controllers\Controller;
use App\Models\Admin;
use App\Models\Organisation;
use App\Services\Quality\PlacementImporter;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;

/** Bulk entry of placement figures: download a pre-filled template, upload, preview, confirm, undo. */
class PlacementImportController extends Controller
{
    public function index(PlacementImporter $importer)
    {
        $batches = $importer->batches();
        $users = Admin::whereIn('id', collect($batches)->pluck('by_user')->filter()->all())->pluck('name', 'id');
        return view('admin.quality.placements-import', ['batches' => $batches, 'users' => $users, 'defaultYear' => $importer->defaultYear()]);
    }

    /** Organisation picker for the template (select2). */
    public function organisations(Request $request)
    {
        $q = trim((string) $request->query('q'));
        if (mb_strlen($q) < 2) return response()->json([]);
        return response()->json(Organisation::where('name', 'like', '%' . $q . '%')->orderBy('name')->limit(30)->get(['id', 'name'])
            ->map(fn ($o) => ['id' => $o->id, 'text' => $o->name . ' (#' . $o->id . ')']));
    }

    public function template(Request $request, PlacementImporter $importer)
    {
        $orgId = $request->filled('organisation') ? (int) $request->query('organisation') : null;
        $org = $orgId ? Organisation::find($orgId) : null;
        $year = $request->filled('year') && preg_match('/^\d{4}-\d{2}$/', $request->query('year')) ? $request->query('year') : null;
        $name = 'placements-' . ($org ? Str::slug($org->name) : 'all-programmes') . '-' . ($year ?: $importer->defaultYear()) . '.csv';
        return response()->streamDownload(function () use ($importer, $orgId, $year) {
            $out = fopen('php://output', 'w');
            fwrite($out, "\xEF\xBB\xBF");   // so Excel reads UTF-8 names correctly
            foreach ($importer->template($orgId, $year) as $line) fputcsv($out, $line);
            fclose($out);
        }, $name, ['Content-Type' => 'text/csv']);
    }

    public function preview(Request $request, PlacementImporter $importer)
    {
        $request->validate(['file' => ['required', 'file', 'mimes:csv,txt', 'max:8192']]);
        $res = $importer->preview($request->file('file')->getRealPath());
        if (!$res['ok']) return back()->with('error', $res['error']);

        $token = Str::random(24);
        Cache::put('quality-placement-import:' . $token, $res['rows'], now()->addHour());

        $pick = fn (string $status, int $n) => array_slice(array_values(array_filter($res['rows'], fn ($r) => $r['status'] === $status)), 0, $n);
        $show = array_merge($pick('error', 200), $pick('exists', 50), $pick('new', 50));
        return view('admin.quality.placements-import-preview', [
            'token' => $token, 'summary' => $res['summary'], 'show' => $show, 'names' => $importer->names($show), 'filename' => $request->file('file')->getClientOriginalName(),
        ]);
    }

    public function confirm(Request $request, PlacementImporter $importer)
    {
        $token = $request->validate(['token' => ['required', 'string']])['token'];
        $rows = Cache::pull('quality-placement-import:' . $token);   // single use
        if (!$rows) return redirect()->route('admin.quality.placements.import')->with('error', 'That preview has expired or was already applied. Upload the file again.');
        set_time_limit(300);
        $r = $importer->apply($rows, auth('admin')->id());
        return redirect()->route('admin.quality.placements.import')->with('success', "Imported {$r['created']} placement row(s) as batch {$r['batch']}." . ($r['skipped'] ? " {$r['skipped']} were skipped because they were added since the preview." : '') . ' They are unverified; you can undo the whole batch below.');
    }

    public function undo(Request $request, PlacementImporter $importer)
    {
        $batch = $request->validate(['batch' => ['required', 'string', 'max:40']])['batch'];
        [$removed, $verified] = $importer->undo($batch, auth('admin')->id());
        if ($removed === 0) return back()->with('error', 'Nothing to undo: that batch has no records left.');
        return back()->with('success', "Batch $batch undone: $removed placement row(s) removed" . ($verified ? " (including $verified that had been verified)." : '.'));
    }
}

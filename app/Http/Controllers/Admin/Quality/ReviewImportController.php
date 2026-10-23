<?php

namespace App\Http\Controllers\Admin\Quality;

use App\Http\Controllers\Controller;
use App\Models\Admin;
use App\Models\Organisation;
use App\Services\Quality\ReviewImporter;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;

/** Bulk entry of reviews / ratings: download a template (and an id list), upload, preview, confirm, undo. */
class ReviewImportController extends Controller
{
    public function index(ReviewImporter $importer)
    {
        $batches = $importer->batches();
        $users = Admin::whereIn('id', collect($batches)->pluck('by_user')->filter()->all())->pluck('name', 'id');
        return view('admin.quality.reviews-import', ['batches' => $batches, 'users' => $users]);
    }

    /** Organisation picker for the id list (select2). */
    public function organisations(Request $request)
    {
        $q = trim((string) $request->query('q'));
        if (mb_strlen($q) < 2) return response()->json([]);
        return response()->json(Organisation::where('name', 'like', '%' . $q . '%')->orderBy('name')->limit(30)->get(['id', 'name'])
            ->map(fn ($o) => ['id' => $o->id, 'text' => $o->name . ' (#' . $o->id . ')']));
    }

    public function template(ReviewImporter $importer)
    {
        return $this->csv('reviews-template.csv', $importer->template());
    }

    /** The ids of one organisation's campuses, departments and programmes, so a reviewer can paste them into the sheet. */
    public function ids(Request $request, ReviewImporter $importer)
    {
        $org = Organisation::find((int) $request->query('organisation'));
        if (!$org) return back()->with('error', 'Choose an organisation first.');
        return $this->csv('review-ids-' . Str::slug($org->name) . '.csv', $importer->idList($org->id));
    }

    public function preview(Request $request, ReviewImporter $importer)
    {
        $request->validate(['file' => ['required', 'file', 'mimes:csv,txt', 'max:8192']]);
        $res = $importer->preview($request->file('file')->getRealPath());
        if (!$res['ok']) return back()->with('error', $res['error']);

        $token = Str::random(24);
        Cache::put('quality-review-import:' . $token, $res['rows'], now()->addHour());

        $pick = fn (string $status, int $n) => array_slice(array_values(array_filter($res['rows'], fn ($r) => $r['status'] === $status)), 0, $n);
        $show = array_merge($pick('error', 200), $pick('exists', 50), $pick('new', 50));
        return view('admin.quality.reviews-import-preview', [
            'token' => $token, 'summary' => $res['summary'], 'show' => $show, 'names' => $importer->names($show), 'filename' => $request->file('file')->getClientOriginalName(),
        ]);
    }

    public function confirm(Request $request, ReviewImporter $importer)
    {
        $token = $request->validate(['token' => ['required', 'string']])['token'];
        $rows = Cache::pull('quality-review-import:' . $token);   // single use
        if (!$rows) return redirect()->route('admin.quality.reviews.import')->with('error', 'That preview has expired or was already applied. Upload the file again.');
        set_time_limit(300);
        $r = $importer->apply($rows, auth('admin')->id());
        return redirect()->route('admin.quality.reviews.import')->with('success', "Imported {$r['created']} review(s) as batch {$r['batch']}." . ($r['skipped'] ? " {$r['skipped']} were skipped because they were added since the preview." : '') . ' They are unverified; you can undo the whole batch below.');
    }

    public function undo(Request $request, ReviewImporter $importer)
    {
        $batch = $request->validate(['batch' => ['required', 'string', 'max:40']])['batch'];
        [$removed, $verified] = $importer->undo($batch, auth('admin')->id());
        if ($removed === 0) return back()->with('error', 'Nothing to undo: that batch has no records left.');
        return back()->with('success', "Batch $batch undone: $removed review(s) removed" . ($verified ? " (including $verified that had been verified)." : '.'));
    }

    private function csv(string $name, iterable $lines)
    {
        return response()->streamDownload(function () use ($lines) {
            $out = fopen('php://output', 'w');
            fwrite($out, "\xEF\xBB\xBF");   // so Excel reads UTF-8 names correctly
            foreach ($lines as $line) fputcsv($out, $line);
            fclose($out);
        }, $name, ['Content-Type' => 'text/csv']);
    }
}

<?php

namespace App\Http\Controllers\Admin\Quality;

use App\Http\Controllers\Controller;
use App\Models\Admin;
use App\Models\Quality\AccreditationBody;
use App\Services\Quality\ApprovalImporter;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;

/** Bulk entry of statutory approvals and sanctioned intake: download a pre-filled template, upload, preview, confirm, undo. */
class ApprovalImportController extends Controller
{
    public function index(ApprovalImporter $importer)
    {
        $bodies = AccreditationBody::where('status', true)->where('kind', '!=', 'accreditation')->orderBy('sort_order')->get()
            ->filter(fn ($b) => $b->allowsLevel('course'))->values();
        $batches = $importer->batches();
        $users = Admin::whereIn('id', collect($batches)->pluck('by_user')->filter()->all())->pluck('name', 'id');
        return view('admin.quality.approvals-import', ['bodies' => $bodies, 'batches' => $batches, 'users' => $users, 'defaultYear' => $importer->defaultYear()]);
    }

    public function template(Request $request, ApprovalImporter $importer)
    {
        $body = $request->filled('body') ? AccreditationBody::where('kind', '!=', 'accreditation')->find((int) $request->query('body')) : null;
        $year = $request->filled('year') && preg_match('/^\d{4}-\d{2}$/', $request->query('year')) ? $request->query('year') : null;
        $name = 'approvals-' . ($body ? Str::slug($body->short_name) . '-missing' : 'template') . '.csv';
        return response()->streamDownload(function () use ($importer, $body, $year) {
            $out = fopen('php://output', 'w');
            fwrite($out, "\xEF\xBB\xBF");   // so Excel reads UTF-8 names correctly
            foreach ($importer->template($body, $year) as $line) fputcsv($out, $line);
            fclose($out);
        }, $name, ['Content-Type' => 'text/csv']);
    }

    public function preview(Request $request, ApprovalImporter $importer)
    {
        $request->validate(['file' => ['required', 'file', 'mimes:csv,txt', 'max:8192']]);
        $res = $importer->preview($request->file('file')->getRealPath());
        if (!$res['ok']) return back()->with('error', $res['error']);

        $token = Str::random(24);
        Cache::put('quality-approval-import:' . $token, $res['rows'], now()->addHour());

        $show = array_merge(
            array_slice(array_values(array_filter($res['rows'], fn ($r) => $r['status'] === 'error')), 0, 200),
            array_slice(array_values(array_filter($res['rows'], fn ($r) => $r['status'] === 'exists')), 0, 50),
            array_slice(array_values(array_filter($res['rows'], fn ($r) => $r['status'] === 'new')), 0, 50),
        );
        return view('admin.quality.approvals-import-preview', [
            'token' => $token, 'summary' => $res['summary'], 'show' => $show, 'names' => $importer->names($show), 'filename' => $request->file('file')->getClientOriginalName(),
        ]);
    }

    public function confirm(Request $request, ApprovalImporter $importer)
    {
        $token = $request->validate(['token' => ['required', 'string']])['token'];
        $rows = Cache::pull('quality-approval-import:' . $token);   // single use
        if (!$rows) return redirect()->route('admin.quality.approvals.import')->with('error', 'That preview has expired or was already applied. Upload the file again.');
        set_time_limit(300);
        $r = $importer->apply($rows, auth('admin')->id());
        return redirect()->route('admin.quality.approvals.import')->with('success', "Imported {$r['created']} approval(s) as batch {$r['batch']}." . ($r['skipped'] ? " {$r['skipped']} were skipped because they were added since the preview." : '') . ' They are unverified; you can undo the whole batch below.');
    }

    public function undo(Request $request, ApprovalImporter $importer)
    {
        $batch = $request->validate(['batch' => ['required', 'string', 'max:40']])['batch'];
        [$removed, $verified] = $importer->undo($batch, auth('admin')->id());
        if ($removed === 0) return back()->with('error', 'Nothing to undo: that batch has no records left.');
        return back()->with('success', "Batch $batch undone: $removed approval(s) removed" . ($verified ? " (including $verified that had been verified)." : '.'));
    }
}

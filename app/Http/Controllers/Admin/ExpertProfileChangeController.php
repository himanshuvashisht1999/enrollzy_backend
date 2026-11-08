<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ExpertProfileChange;
use App\Support\Experts\Docs;
use App\Support\Experts\ProfileChanges;
use Illuminate\Http\Request;

/**
 * Expert Management > Profile changes: experts' edits from the website, each shown as before -> after. Approve writes
 * them to the expert's profile; reject sends the expert a reason.
 */
class ExpertProfileChangeController extends Controller
{
    public function index(Request $request)
    {
        $status = array_key_exists((string) $request->status, ExpertProfileChange::STATUSES) ? $request->status : 'pending';
        $changes = ExpertProfileChange::with(['expert', 'reviewer'])->where('status', $status)
            ->orderBy($status === 'pending' ? 'updated_at' : 'reviewed_at', $status === 'pending' ? 'asc' : 'desc')->paginate(20)->withQueryString();
        $counts = ExpertProfileChange::selectRaw('status, count(*) n')->groupBy('status')->pluck('n', 'status');
        // documents approved experts added from their dashboard, waiting for a check (application documents are checked on the application)
        $docs = \App\Models\ExpertDocument::with('expert')->whereNotNull('expert_id')->where('status', 'pending')->whereHas('expert')->orderBy('created_at')->get();
        return view('admin.experts.profile-changes.index', compact('changes', 'counts', 'status', 'docs'));
    }

    public function show(ExpertProfileChange $change)
    {
        $change->load(['expert', 'reviewer']);
        abort_unless($change->expert, 404);
        return view('admin.experts.profile-changes.show', ['c' => $change, 'e' => $change->expert]);
    }

    public function approve(Request $request, ExpertProfileChange $change)
    {
        abort_unless($change->status === 'pending' && $change->expert, 422, 'This request is no longer waiting.');
        $data = $request->validate(['note' => 'nullable|string|max:1000']);
        ProfileChanges::approve($change, $data['note'] ?? null, (int) auth('admin')->id());
        return redirect()->route('expert-profile-changes.index')->with('success', 'Approved: ' . $change->expert->name . "'s profile is updated.");
    }

    public function reject(Request $request, ExpertProfileChange $change)
    {
        abort_unless($change->status === 'pending', 422, 'This request is no longer waiting.');
        $data = $request->validate(['note' => 'required|string|max:1000'], ['note.required' => 'Tell the expert why, so they can correct it.']);
        ProfileChanges::reject($change, $data['note'], (int) auth('admin')->id());
        return redirect()->route('expert-profile-changes.index')->with('success', 'Rejected. The expert sees your reason in their dashboard.');
    }

    public function photo(ExpertProfileChange $change)
    {
        return Docs::response($change->photo, 'photo');
    }
}

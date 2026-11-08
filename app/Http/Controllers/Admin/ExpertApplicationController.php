<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ExpertApplication;
use App\Models\ExpertCategory;
use App\Support\Experts\Applications;
use App\Support\Experts\Docs;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

/**
 * Experts > Applications: requests to join as an expert, sent from the website (Become an expert). Staff read the
 * form and documents, check each document, write to the applicant (or a staff-only note), set the status, and approve
 * (which creates the expert) or reject. The re-apply rules are set at the top of the list.
 */
class ExpertApplicationController extends Controller
{
    public function index(Request $request)
    {
        $status = array_key_exists((string) $request->status, ExpertApplication::STATUSES) ? $request->status : null;
        $q = trim((string) $request->q);
        $apps = ExpertApplication::withCount(['documents', 'documents as pending_docs_count' => fn ($d) => $d->where('status', 'pending')])
            ->when($status, fn ($w) => $w->where('status', $status), fn ($w) => $request->has('status') ? $w->where('status', '!=', 'draft') : $w->whereIn('status', ExpertApplication::REVIEW))
            ->when($q, fn ($w) => $w->where(fn ($x) => $x->where('name', 'like', "%$q%")->orWhere('mobile', 'like', "%$q%")->orWhere('email', 'like', "%$q%")))
            ->orderByRaw("FIELD(status, 'needs_changes', 'pending', 'under_review') DESC")->orderByDesc('submitted_at')->orderByDesc('id')
            ->paginate(20)->withQueryString();
        $counts = ExpertApplication::selectRaw('status, count(*) n')->groupBy('status')->pluck('n', 'status');
        return view('admin.experts.applications.index', ['apps' => $apps, 'counts' => $counts, 'status' => $status, 'q' => $q, 'limits' => Applications::limits()]);
    }

    public function settings(Request $request)
    {
        $data = $request->validate(['expert_max_applications' => 'required|integer|min:1|max:20', 'expert_reapply_wait_days' => 'required|integer|min:0|max:365'], [],
            ['expert_max_applications' => 'applications per person', 'expert_reapply_wait_days' => 'days to wait']);
        DB::table('settings')->orderBy('id')->limit(1)->update($data + ['updated_at' => now()]);
        return back()->with('success', 'Re-apply rules saved.');
    }

    public function show(ExpertApplication $application)
    {
        $application->load(['documents.reviewer', 'notes.admin', 'expert', 'decider']);
        $others = ExpertApplication::where('user_id', $application->user_id)->where('id', '!=', $application->id)->orderByDesc('id')->get();
        return view('admin.experts.applications.show', [
            'app' => $application, 'others' => $others, 'eligibility' => Applications::eligibility($application->user_id),
            'categories' => ExpertCategory::orderBy('name')->pluck('name', 'id'),
        ]);
    }

    /** a status change (approve has its own action); "changes needed" and "not approved" must say why */
    public function status(Request $request, ExpertApplication $application)
    {
        abort_unless(in_array($application->status, ExpertApplication::REVIEW, true) || $application->status === 'rejected', 422, 'This application cannot change status.');
        $data = $request->validate([
            'status' => ['required', Rule::in(['pending', 'under_review', 'needs_changes', 'rejected'])],
            'message' => [Rule::requiredIf(in_array($request->status, ['needs_changes', 'rejected'], true)), 'nullable', 'string', 'max:3000'],
        ], ['message.required' => 'Tell the applicant what to change, or why it is not approved.']);
        if ($data['status'] === $application->status && blank($data['message'] ?? null)) return back()->with('error', 'Nothing changed.');
        Applications::setStatus($application, $data['status'], $data['message'] ?? null, (int) auth('admin')->id());
        return back()->with('success', 'Status set to "' . ExpertApplication::STATUSES[$data['status']] . '". The applicant sees it on the website.');
    }

    /** a message to the applicant, or a staff-only note */
    public function note(Request $request, ExpertApplication $application)
    {
        $data = $request->validate(['body' => 'required|string|max:3000']);
        Applications::note($application, 'staff', $data['body'], ['admin_id' => auth('admin')->id(), 'internal' => $request->boolean('internal')]);
        return back()->with('success', $request->boolean('internal') ? 'Staff note added.' : 'Message sent to the applicant.');
    }

    public function approve(Request $request, ExpertApplication $application)
    {
        abort_unless(in_array($application->status, ExpertApplication::REVIEW, true), 422, 'Only a sent, open application can be approved.');
        $data = $request->validate(['expert_category_id' => 'required|exists:expert_categories,id', 'message' => 'nullable|string|max:3000']);
        $expert = Applications::approve($application, (int) $data['expert_category_id'], $data['message'] ?? null, (int) auth('admin')->id());
        return redirect()->route('experts.edit', $expert)
            ->with('success', 'Approved. ' . $expert->name . ' can now sign in to the expert dashboard. The profile is Private: set the price and slots, then make it Public.');
    }

    /** let a person whose application was not approved apply again now (one more, even past the limit) */
    public function allowReapply(ExpertApplication $application)
    {
        abort_unless($application->status === 'rejected', 422);
        $application->update(['reapply_allowed_at' => now()]);
        Applications::note($application, 'staff', 'Allowed to apply again now.', ['admin_id' => auth('admin')->id(), 'internal' => true]);
        return back()->with('success', 'They can start a new application now.');
    }

    public function photo(ExpertApplication $application)
    {
        return Docs::response($application->photo, 'photo');
    }
}

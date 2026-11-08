<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Booking;
use App\Models\Expert;
use App\Models\ExpertPayout;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

/**
 * Expert payouts: what each expert has earned from completed, paid sessions (net of platform fee, GST and TDS),
 * what has been paid out, and what is still due; record a payout and follow it to "paid".
 */
class ExpertPayoutController extends Controller
{
    public function index(Request $request)
    {
        Booking::fillMissingCommission();

        $experts = Expert::orderBy('name')->get(['id', 'name', 'email', 'status']);
        $summaries = $experts->map(fn ($e) => ['expert' => $e] + ExpertPayout::summaryFor($e))
            ->filter(fn ($s) => $s['sessions'] || $s['paid_out'] || $s['in_progress'] || $request->boolean('all'))
            ->sortByDesc('due')->values();

        $payouts = ExpertPayout::with('expert:id,name')
            ->when($request->filled('expert_id'), fn ($q) => $q->where('expert_id', $request->expert_id))
            ->when($request->filled('status'), fn ($q) => $q->where('status', $request->status))
            ->latest()->paginate(20)->withQueryString();

        $totals = [
            'due' => $summaries->sum('due'),
            'in_progress' => $summaries->sum('in_progress'),
            'paid_out' => $summaries->sum('paid_out'),
            'platform' => (float) Booking::earned()->sum('amount') - (float) Booking::earned()->sum('expert_earning'),
        ];

        return view('admin.expert-payouts.index', compact('summaries', 'payouts', 'experts', 'totals'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'expert_id' => ['required', 'exists:experts,id'],
            'amount' => ['required', 'numeric', 'min:1'],
            'payout_method' => ['required', Rule::in(array_keys(ExpertPayout::METHODS))],
            'status' => ['required', Rule::in(array_keys(ExpertPayout::STATUSES))],
            'reference_id' => ['nullable', 'required_if:status,paid', 'string', 'max:100'],
            'period_start' => ['nullable', 'date'],
            'period_end' => ['nullable', 'date', 'after_or_equal:period_start'],
            'notes' => ['nullable', 'string', 'max:1000'],
        ], ['reference_id.required_if' => 'Enter the transaction / reference number for a paid payout.']);

        $expert = Expert::findOrFail($data['expert_id']);
        $due = ExpertPayout::summaryFor($expert)['due'];
        if ($data['amount'] > $due + 0.01) {
            return back()->withInput()->with('error', "{$expert->name} has only ₹" . number_format($due, 2) . ' due. A payout cannot be more than that.');
        }

        ExpertPayout::create($data + ['paid_at' => $data['status'] === 'paid' ? now() : null]);

        return back()->with('success', 'Payout of ₹' . number_format($data['amount'], 2) . " to {$expert->name} recorded.");
    }

    public function update(Request $request, ExpertPayout $payout)
    {
        if ($payout->status === 'paid') {
            return back()->with('error', 'A paid payout cannot be changed.');
        }
        $data = $request->validate([
            'status' => ['required', Rule::in(array_keys(ExpertPayout::STATUSES))],
            'reference_id' => ['nullable', 'required_if:status,paid', 'string', 'max:100'],
            'notes' => ['nullable', 'string', 'max:1000'],
        ], ['reference_id.required_if' => 'Enter the transaction / reference number to mark it paid.']);

        $payout->update($data + ['paid_at' => $data['status'] === 'paid' ? now() : null]);

        return back()->with('success', 'Payout updated.');
    }

    public function destroy(ExpertPayout $payout)
    {
        if ($payout->status === 'paid') {
            return back()->with('error', 'A paid payout cannot be deleted.');
        }
        $payout->delete();

        return back()->with('success', 'Payout deleted.');
    }
}

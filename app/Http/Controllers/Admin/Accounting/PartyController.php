<?php

namespace App\Http\Controllers\Admin\Accounting;

use App\Http\Controllers\Controller;
use App\Models\Accounting\JournalEntryLine;
use App\Models\Accounting\Party;
use Illuminate\Http\Request;

class PartyController extends Controller
{
    public function index(Request $request)
    {
        $query = Party::query();

        if ($request->filled('type')) {
            $type = $request->type;
            if ($type === 'customer') $query->where('is_customer', true);
            elseif ($type === 'vendor') $query->where('is_vendor', true);
            elseif ($type === 'founder') $query->where('is_founder', true);
            elseif ($type === 'investor') $query->where('is_investor', true);
            elseif ($type === 'employee') $query->where('is_employee', true);
        }

        if ($request->filled('search')) {
            $s = $request->search;
            $query->where(function ($q) use ($s) {
                $q->where('name', 'like', "%{$s}%")
                  ->orWhere('email', 'like', "%{$s}%")
                  ->orWhere('phone', 'like', "%{$s}%")
                  ->orWhere('gstin', 'like', "%{$s}%")
                  ->orWhere('pan', 'like', "%{$s}%");
            });
        }

        $parties = $query->orderBy('name', 'asc')->paginate(20);

        return view('admin.accounting.parties.index', compact('parties'));
    }

    public function create()
    {
        return view('admin.accounting.parties.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'legal_name' => 'nullable|string|max:255',
            'party_type' => 'required|in:customer,vendor,investor,founder,employee',
            'phone' => 'nullable|string|max:50',
            'email' => 'nullable|email|max:255',
            'gstin' => 'nullable|string|max:50',
            'pan' => 'nullable|string|max:50',
            'billing_address' => 'nullable|string',
            'shipping_address' => 'nullable|string',
            'state' => 'nullable|string|max:100',
            'state_code' => 'nullable|string|max:10',
            'is_customer' => 'sometimes|boolean',
            'is_vendor' => 'sometimes|boolean',
            'is_investor' => 'sometimes|boolean',
            'is_founder' => 'sometimes|boolean',
            'is_employee' => 'sometimes|boolean',
            'opening_balance' => 'nullable|numeric',
        ]);

        // Auto flags based on party_type if not checked
        $validated['is_customer'] = $request->has('is_customer') || $validated['party_type'] === 'customer';
        $validated['is_vendor'] = $request->has('is_vendor') || $validated['party_type'] === 'vendor';
        $validated['is_investor'] = $request->has('is_investor') || $validated['party_type'] === 'investor';
        $validated['is_founder'] = $request->has('is_founder') || $validated['party_type'] === 'founder';
        $validated['is_employee'] = $request->has('is_employee') || $validated['party_type'] === 'employee';
        $validated['opening_balance'] = $validated['opening_balance'] ?? 0.00;
        $validated['current_balance'] = $validated['opening_balance'];
        $validated['status'] = 'active';

        Party::create($validated);

        return redirect()->route('admin.accounting.parties.index')
            ->with('success', 'Party created successfully.');
    }

    public function show($id)
    {
        $party = Party::with(['salesInvoices', 'vendorBills', 'fundingRecords'])->findOrFail($id);

        $ledgerLines = JournalEntryLine::with(['journalEntry', 'account'])
            ->where('party_id', $party->id)
            ->whereHas('journalEntry', function ($q) {
                $q->where('status', 'posted');
            })
            ->join('journal_entries', 'journal_entries.id', '=', 'journal_entry_lines.journal_entry_id')
            ->orderBy('journal_entries.journal_date', 'desc')
            ->select('journal_entry_lines.*')
            ->take(50)
            ->get();

        return view('admin.accounting.parties.show', compact('party', 'ledgerLines'));
    }

    public function edit($id)
    {
        $party = Party::findOrFail($id);
        return view('admin.accounting.parties.edit', compact('party'));
    }

    public function update(Request $request, $id)
    {
        $party = Party::findOrFail($id);

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'legal_name' => 'nullable|string|max:255',
            'party_type' => 'required|in:customer,vendor,investor,founder,employee',
            'phone' => 'nullable|string|max:50',
            'email' => 'nullable|email|max:255',
            'gstin' => 'nullable|string|max:50',
            'pan' => 'nullable|string|max:50',
            'billing_address' => 'nullable|string',
            'shipping_address' => 'nullable|string',
            'state' => 'nullable|string|max:100',
            'state_code' => 'nullable|string|max:10',
            'is_customer' => 'sometimes|boolean',
            'is_vendor' => 'sometimes|boolean',
            'is_investor' => 'sometimes|boolean',
            'is_founder' => 'sometimes|boolean',
            'is_employee' => 'sometimes|boolean',
            'status' => 'required|in:active,inactive',
        ]);

        $validated['is_customer'] = $request->has('is_customer');
        $validated['is_vendor'] = $request->has('is_vendor');
        $validated['is_investor'] = $request->has('is_investor');
        $validated['is_founder'] = $request->has('is_founder');
        $validated['is_employee'] = $request->has('is_employee');

        $party->update($validated);

        return redirect()->route('admin.accounting.parties.index')
            ->with('success', 'Party updated successfully.');
    }

    public function destroy($id)
    {
        $party = Party::findOrFail($id);

        if ($party->salesInvoices()->count() > 0 || $party->vendorBills()->count() > 0) {
            return redirect()->back()->with('error', 'Cannot delete party with existing financial transactions. You can set status to inactive.');
        }

        $party->delete();

        return redirect()->route('admin.accounting.parties.index')
            ->with('success', 'Party deleted successfully.');
    }
}

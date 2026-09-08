<?php

namespace App\Http\Controllers\Admin\Accounting;

use App\Http\Controllers\Controller;
use App\Models\Accounting\AccountingTransaction;
use App\Models\Accounting\ChartOfAccount;
use App\Models\Accounting\JournalEntry;
use App\Models\Accounting\Party;
use App\Services\Accounting\AccountingEngineService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Exception;

class JournalController extends Controller
{
    protected AccountingEngineService $engine;

    public function __construct(AccountingEngineService $engine)
    {
        $this->engine = $engine;
    }

    public function index(Request $request)
    {
        $query = JournalEntry::with(['lines.account', 'lines.party', 'creator']);

        if ($request->filled('entry_type')) {
            $query->where('entry_type', $request->entry_type);
        }

        if ($request->filled('search')) {
            $s = $request->search;
            $query->where(function ($q) use ($s) {
                $q->where('journal_number', 'like', "%{$s}%")
                  ->orWhere('description', 'like', "%{$s}%");
            });
        }

        $journals = $query->orderBy('journal_date', 'desc')->paginate(20);

        return view('admin.accounting.journals.index', compact('journals'));
    }

    public function create()
    {
        $accounts = ChartOfAccount::orderBy('account_code', 'asc')->get();
        $parties = Party::orderBy('name', 'asc')->get();
        $nextJournalNumber = $this->engine->generateNumber('JV', 'journal_entries', 'journal_number');

        return view('admin.accounting.journals.create', compact('accounts', 'parties', 'nextJournalNumber'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'journal_date' => 'required|date',
            'description' => 'required|string|max:500',
            'lines' => 'required|array|min:2',
            'lines.*.account_id' => 'required|exists:chart_of_accounts,id',
            'lines.*.party_id' => 'nullable|exists:parties,id',
            'lines.*.debit' => 'nullable|numeric|min:0',
            'lines.*.credit' => 'nullable|numeric|min:0',
            'lines.*.description' => 'nullable|string|max:255',
        ]);

        $adminId = Auth::guard('admin')->id() ?? 1;

        $formattedLines = [];
        $totalDebit = 0;
        $totalCredit = 0;

        foreach ($validated['lines'] as $l) {
            $deb = (float) ($l['debit'] ?? 0);
            $cred = (float) ($l['credit'] ?? 0);

            if ($deb > 0 || $cred > 0) {
                $totalDebit += $deb;
                $totalCredit += $cred;

                $formattedLines[] = [
                    'account_id' => $l['account_id'],
                    'party_id' => $l['party_id'] ?? null,
                    'debit' => $deb,
                    'credit' => $cred,
                    'description' => $l['description'] ?? $validated['description'],
                ];
            }
        }

        if (abs(round($totalDebit, 2) - round($totalCredit, 2)) >= 0.01) {
            return redirect()->back()
                ->withInput()
                ->with('error', "Unbalanced Journal: Total Debits (₹" . number_format($totalDebit, 2) . ") must equal Total Credits (₹" . number_format($totalCredit, 2) . ").");
        }

        try {
            $transaction = $this->engine->postJournal([
                'transaction_date' => $validated['journal_date'],
                'transaction_type' => 'journal',
                'description' => $validated['description'],
                'entry_type' => 'manual',
                'created_by' => $adminId,
            ], $formattedLines);

            $journal = $transaction->journalEntries()->first();

            return redirect()->route('admin.accounting.journals.show', $journal->id)
                ->with('success', "Journal voucher {$journal->journal_number} posted successfully.");
        } catch (Exception $e) {
            return redirect()->back()
                ->withInput()
                ->with('error', "Journal posting failed: " . $e->getMessage());
        }
    }

    public function show($id)
    {
        $journal = JournalEntry::with(['lines.account', 'lines.party', 'transaction', 'creator', 'poster'])
            ->findOrFail($id);

        return view('admin.accounting.journals.show', compact('journal'));
    }

    public function reverse(Request $request, $id)
    {
        $journal = JournalEntry::findOrFail($id);
        $transaction = $journal->transaction;

        if (!$transaction) {
            return redirect()->back()->with('error', 'Cannot find transaction header for this journal.');
        }

        $reason = $request->input('reason', 'Manual user reversal.');

        try {
            $reversal = $this->engine->reverseTransaction($transaction, $reason);
            return redirect()->back()->with('success', "Journal reversed successfully via Reversal Transaction {$reversal->transaction_number}.");
        } catch (Exception $e) {
            return redirect()->back()->with('error', "Reversal failed: " . $e->getMessage());
        }
    }
}

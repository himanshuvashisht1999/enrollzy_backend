<?php

namespace App\Http\Controllers\Admin\Accounting;

use App\Http\Controllers\Controller;
use App\Models\Accounting\BankAccount;
use App\Models\Accounting\BankTransaction;
use App\Models\Accounting\ChartOfAccount;
use App\Models\Accounting\JournalEntryLine;
use App\Services\Accounting\AccountingEngineService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Exception;

class BankingController extends Controller
{
    protected AccountingEngineService $engine;

    public function __construct(AccountingEngineService $engine)
    {
        $this->engine = $engine;
    }

    public function index(Request $request)
    {
        $bankAccounts = BankAccount::with('ledgerAccount')->get();
        $totalBalance = $bankAccounts->sum('current_balance');

        $recentTransactions = BankTransaction::with('bankAccount')
            ->orderBy('transaction_date', 'desc')
            ->take(15)
            ->get();

        return view('admin.accounting.banking.index', compact('bankAccounts', 'totalBalance', 'recentTransactions'));
    }

    public function create()
    {
        $ledgerAccounts = ChartOfAccount::where('account_type', 'asset')
            ->where(function ($q) {
                $q->where('account_code', 'like', '112%')
                  ->orWhere('account_code', 'like', '111%')
                  ->orWhere('name', 'like', '%Bank%')
                  ->orWhere('name', 'like', '%Cash%');
            })->get();

        return view('admin.accounting.banking.create', compact('ledgerAccounts'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'account_name' => 'required|string|max:255',
            'bank_name' => 'required|string|max:255',
            'account_number_masked' => 'required|string|max:100',
            'ifsc' => 'nullable|string|max:50',
            'branch' => 'nullable|string|max:255',
            'opening_balance' => 'nullable|numeric',
            'ledger_account_id' => 'nullable|exists:chart_of_accounts,id',
        ]);

        $openingBal = (float) ($validated['opening_balance'] ?? 0);

        // If no ledger account chosen, auto create sub-account under 1120
        $ledgerAccountId = $validated['ledger_account_id'] ?? null;
        if (!$ledgerAccountId) {
            $parentBank = ChartOfAccount::where('account_code', '1120')->first();
            $newCode = '112' . rand(10, 99);
            while (ChartOfAccount::where('account_code', $newCode)->exists()) {
                $newCode = '112' . rand(10, 99);
            }

            $coa = ChartOfAccount::create([
                'account_code' => $newCode,
                'name' => $validated['account_name'],
                'account_type' => 'asset',
                'parent_id' => $parentBank ? $parentBank->id : null,
                'current_balance' => $openingBal,
                'is_system_account' => false,
                'is_active' => true,
            ]);
            $ledgerAccountId = $coa->id;
        }

        BankAccount::create([
            'account_name' => $validated['account_name'],
            'bank_name' => $validated['bank_name'],
            'account_number_masked' => $validated['account_number_masked'],
            'ifsc' => $validated['ifsc'] ?? null,
            'branch' => $validated['branch'] ?? null,
            'opening_balance' => $openingBal,
            'current_balance' => $openingBal,
            'ledger_account_id' => $ledgerAccountId,
            'is_active' => true,
        ]);

        return redirect()->route('admin.accounting.banking.index')
            ->with('success', 'Bank account added successfully.');
    }

    public function show($id)
    {
        $bankAccount = BankAccount::with('ledgerAccount')->findOrFail($id);

        $ledgerLines = JournalEntryLine::with(['journalEntry', 'party'])
            ->where('account_id', $bankAccount->ledger_account_id)
            ->whereHas('journalEntry', function ($q) {
                $q->where('status', 'posted');
            })
            ->join('journal_entries', 'journal_entries.id', '=', 'journal_entry_lines.journal_entry_id')
            ->orderBy('journal_entries.journal_date', 'desc')
            ->select('journal_entry_lines.*')
            ->paginate(25);

        return view('admin.accounting.banking.show', compact('bankAccount', 'ledgerLines'));
    }

    public function transfer(Request $request)
    {
        $validated = $request->validate([
            'from_account_id' => 'required|exists:bank_accounts,id|different:to_account_id',
            'to_account_id' => 'required|exists:bank_accounts,id',
            'transfer_date' => 'required|date',
            'amount' => 'required|numeric|min:0.01',
            'reference_number' => 'nullable|string|max:100',
            'notes' => 'nullable|string',
        ]);

        $fromBank = BankAccount::findOrFail($validated['from_account_id']);
        $toBank = BankAccount::findOrFail($validated['to_account_id']);
        $amount = (float) $validated['amount'];
        $adminId = Auth::guard('admin')->id() ?? 1;

        try {
            $lines = [
                // 1. DEBIT: Receiving Bank (Assets: Debit increases)
                [
                    'account_id' => $toBank->ledger_account_id,
                    'party_id' => null,
                    'debit' => $amount,
                    'credit' => 0.00,
                    'description' => "Fund transfer received from {$fromBank->account_name}",
                    'reference' => $validated['reference_number'] ?? null,
                ],
                // 2. CREDIT: Source Bank (Assets: Credit decreases)
                [
                    'account_id' => $fromBank->ledger_account_id,
                    'party_id' => null,
                    'debit' => 0.00,
                    'credit' => $amount,
                    'description' => "Fund transfer dispatched to {$toBank->account_name}",
                    'reference' => $validated['reference_number'] ?? null,
                ],
            ];

            $this->engine->postJournal([
                'transaction_date' => $validated['transfer_date'],
                'transaction_type' => 'bank_transfer',
                'description' => $validated['notes'] ?? "Bank Transfer from {$fromBank->account_name} to {$toBank->account_name}",
                'entry_type' => 'auto',
                'created_by' => $adminId,
            ], $lines);

            return redirect()->route('admin.accounting.banking.index')
                ->with('success', "Transfer of ₹" . number_format($amount, 2) . " completed successfully.");
        } catch (Exception $e) {
            return redirect()->back()->with('error', "Transfer failed: " . $e->getMessage());
        }
    }
}

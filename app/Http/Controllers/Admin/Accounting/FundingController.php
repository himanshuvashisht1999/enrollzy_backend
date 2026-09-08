<?php

namespace App\Http\Controllers\Admin\Accounting;

use App\Http\Controllers\Controller;
use App\Models\Accounting\BankAccount;
use App\Models\Accounting\ChartOfAccount;
use App\Models\Accounting\FundingRecord;
use App\Models\Accounting\Party;
use App\Services\Accounting\AccountingEngineService;
use App\Services\Accounting\FundingPostingService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Exception;

class FundingController extends Controller
{
    protected FundingPostingService $fundingPostingService;
    protected AccountingEngineService $engine;

    public function __construct(FundingPostingService $fundingPostingService, AccountingEngineService $engine)
    {
        $this->fundingPostingService = $fundingPostingService;
        $this->engine = $engine;
    }

    public function index(Request $request)
    {
        $query = FundingRecord::with(['party', 'depositBankAccount', 'equityLedgerAccount', 'creator', 'poster']);

        if ($request->filled('type')) {
            if ($request->type === 'founder') {
                $query->whereIn('funding_type', ['founder_capital', 'founder_current']);
            } elseif ($request->type === 'investor') {
                $query->whereNotIn('funding_type', ['founder_capital', 'founder_current']);
            }
        }

        $fundingRecords = $query->orderBy('received_date', 'desc')->paginate(20);

        $totalFounderCapital = FundingRecord::where('status', 'posted')
            ->whereIn('funding_type', ['founder_capital', 'founder_current'])
            ->sum('amount');

        $totalInvestorFunding = FundingRecord::where('status', 'posted')
            ->whereNotIn('funding_type', ['founder_capital', 'founder_current'])
            ->sum('amount');

        return view('admin.accounting.funding.index', compact('fundingRecords', 'totalFounderCapital', 'totalInvestorFunding'));
    }

    public function create()
    {
        $parties = Party::where('is_founder', true)->orWhere('is_investor', true)->orderBy('name', 'asc')->get();
        if ($parties->isEmpty()) {
            $parties = Party::orderBy('name', 'asc')->get();
        }

        $bankAccounts = BankAccount::where('is_active', true)->get();
        $equityAccounts = ChartOfAccount::whereIn('account_type', ['equity', 'liability'])->orderBy('account_code', 'asc')->get();
        $nextFundingNumber = $this->engine->generateNumber('FND', 'funding_records', 'funding_number');

        return view('admin.accounting.funding.create', compact('parties', 'bankAccounts', 'equityAccounts', 'nextFundingNumber'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'party_id' => 'required|exists:parties,id',
            'funding_type' => 'required|in:founder_capital,founder_current,equity_investment,preference_shares,convertible_note,unsecured_loan,secured_loan,grant',
            'amount' => 'required|numeric|min:0.01',
            'equity_percentage' => 'nullable|numeric|min:0|max:100',
            'valuation' => 'nullable|numeric|min:0',
            'shares_issued' => 'nullable|numeric|min:0',
            'share_price' => 'nullable|numeric|min:0',
            'deposit_bank_account_id' => 'required|exists:bank_accounts,id',
            'equity_ledger_account_id' => 'required|exists:chart_of_accounts,id',
            'received_date' => 'required|date',
            'reference_number' => 'nullable|string|max:100',
            'notes' => 'nullable|string',
            'action' => 'required|in:save_draft,save_and_post',
        ]);

        $adminId = Auth::guard('admin')->id() ?? 1;
        $fundingNumber = $this->engine->generateNumber('FND', 'funding_records', 'funding_number');

        $funding = FundingRecord::create([
            'funding_number' => $fundingNumber,
            'party_id' => $validated['party_id'],
            'funding_type' => $validated['funding_type'],
            'amount' => $validated['amount'],
            'equity_percentage' => $validated['equity_percentage'] ?? null,
            'valuation' => $validated['valuation'] ?? null,
            'shares_issued' => $validated['shares_issued'] ?? null,
            'share_price' => $validated['share_price'] ?? null,
            'deposit_bank_account_id' => $validated['deposit_bank_account_id'],
            'equity_ledger_account_id' => $validated['equity_ledger_account_id'],
            'received_date' => $validated['received_date'],
            'reference_number' => $validated['reference_number'] ?? null,
            'status' => 'draft',
            'notes' => $validated['notes'] ?? null,
            'created_by' => $adminId,
        ]);

        if ($validated['action'] === 'save_and_post') {
            try {
                $this->fundingPostingService->postFunding($funding);
            } catch (Exception $e) {
                return redirect()->route('admin.accounting.funding.show', $funding->id)
                    ->with('error', 'Funding saved but failed to post: ' . $e->getMessage());
            }
        }

        return redirect()->route('admin.accounting.funding.show', $funding->id)
            ->with('success', 'Funding record saved successfully.');
    }

    public function show($id)
    {
        $funding = FundingRecord::with(['party', 'depositBankAccount', 'equityLedgerAccount', 'creator', 'poster', 'transaction.journalEntries.lines.account'])
            ->findOrFail($id);

        return view('admin.accounting.funding.show', compact('funding'));
    }

    public function post($id)
    {
        $funding = FundingRecord::findOrFail($id);

        try {
            $this->fundingPostingService->postFunding($funding);
            return redirect()->back()->with('success', "Funding {$funding->funding_number} posted to the General Ledger.");
        } catch (Exception $e) {
            return redirect()->back()->with('error', 'Posting failed: ' . $e->getMessage());
        }
    }
}

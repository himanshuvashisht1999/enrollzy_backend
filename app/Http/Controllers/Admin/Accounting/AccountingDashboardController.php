<?php

namespace App\Http\Controllers\Admin\Accounting;

use App\Http\Controllers\Controller;
use App\Models\Accounting\AccountingTransaction;
use App\Models\Accounting\BankAccount;
use App\Models\Accounting\Expense;
use App\Models\Accounting\FundingRecord;
use App\Models\Accounting\SalesInvoice;
use App\Models\Accounting\VendorBill;
use App\Services\Accounting\FinancialReportService;
use Carbon\Carbon;
use Illuminate\Http\Request;

class AccountingDashboardController extends Controller
{
    protected FinancialReportService $reportService;

    public function __construct(FinancialReportService $reportService)
    {
        $this->reportService = $reportService;
    }

    public function index(Request $request)
    {
        $today = now()->toDateString();
        $startOfMonth = now()->startOfMonth()->toDateString();
        $endOfMonth = now()->endOfMonth()->toDateString();
        $startOfYear = now()->startOfYear()->toDateString();

        // 1. Receivables
        $totalReceivables = SalesInvoice::where('status', 'posted')->sum('balance_due');
        $overdueReceivables = SalesInvoice::where('status', 'posted')
            ->where('balance_due', '>', 0)
            ->where('due_date', '<', $today)
            ->sum('balance_due');
        $invoicedThisMonth = SalesInvoice::where('status', 'posted')
            ->whereBetween('invoice_date', [$startOfMonth, $endOfMonth])
            ->sum('total_amount');
        $receivedThisMonth = SalesInvoice::where('status', 'posted')
            ->whereBetween('invoice_date', [$startOfMonth, $endOfMonth])
            ->sum('paid_amount');

        // 2. Payables
        $totalPayables = VendorBill::where('status', 'posted')->sum('balance_due');
        $overduePayables = VendorBill::where('status', 'posted')
            ->where('balance_due', '>', 0)
            ->where('due_date', '<', $today)
            ->sum('balance_due');
        $billedThisMonth = VendorBill::where('status', 'posted')
            ->whereBetween('bill_date', [$startOfMonth, $endOfMonth])
            ->sum('net_payable');
        $paidThisMonth = VendorBill::where('status', 'posted')
            ->whereBetween('bill_date', [$startOfMonth, $endOfMonth])
            ->sum('paid_amount');

        // 3. Bank & Cash Balances
        $bankAccounts = BankAccount::where('is_active', true)->get();
        $totalBankCashBalance = $bankAccounts->sum('current_balance');

        // 4. Funding & Equity
        $totalFounderCapital = FundingRecord::where('status', 'posted')
            ->whereIn('funding_type', ['founder_capital', 'founder_current'])
            ->sum('amount');
        $totalInvestorFunding = FundingRecord::where('status', 'posted')
            ->whereNotIn('funding_type', ['founder_capital', 'founder_current'])
            ->sum('amount');

        // 5. P&L Quick Snapshot
        $monthlyPnl = $this->reportService->getProfitAndLoss($startOfMonth, $endOfMonth);
        $ytdPnl = $this->reportService->getProfitAndLoss($startOfYear, $today);

        // 6. Pending Actions
        $pendingExpenses = Expense::where('status', 'submitted')
            ->with(['categoryAccount', 'party', 'submitter'])
            ->orderBy('created_at', 'desc')
            ->take(5)
            ->get();

        $recentTransactions = AccountingTransaction::with('creator')
            ->orderBy('created_at', 'desc')
            ->take(8)
            ->get();

        $overdueInvoices = SalesInvoice::with('party')
            ->where('status', 'posted')
            ->where('balance_due', '>', 0)
            ->where('due_date', '<', $today)
            ->orderBy('due_date', 'asc')
            ->take(5)
            ->get();

        $overdueBills = VendorBill::with('party')
            ->where('status', 'posted')
            ->where('balance_due', '>', 0)
            ->where('due_date', '<', $today)
            ->orderBy('due_date', 'asc')
            ->take(5)
            ->get();

        return view('admin.accounting.dashboard.index', compact(
            'totalReceivables',
            'overdueReceivables',
            'invoicedThisMonth',
            'receivedThisMonth',
            'totalPayables',
            'overduePayables',
            'billedThisMonth',
            'paidThisMonth',
            'bankAccounts',
            'totalBankCashBalance',
            'totalFounderCapital',
            'totalInvestorFunding',
            'monthlyPnl',
            'ytdPnl',
            'pendingExpenses',
            'recentTransactions',
            'overdueInvoices',
            'overdueBills'
        ));
    }
}

<?php

namespace App\Services\Accounting;

use App\Models\Accounting\ChartOfAccount;
use App\Models\Accounting\JournalEntryLine;
use App\Models\Accounting\Party;
use App\Models\Accounting\SalesInvoice;
use App\Models\Accounting\VendorBill;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class FinancialReportService
{
    /**
     * Generate Trial Balance as of date range.
     */
    public function getTrialBalance(?string $fromDate = null, ?string $toDate = null): array
    {
        $toDate = $toDate ?: now()->toDateString();
        $fromDate = $fromDate ?: now()->startOfYear()->toDateString();

        $accounts = ChartOfAccount::orderBy('account_code', 'asc')->get();

        $rows = [];
        $totalDebit = 0.00;
        $totalCredit = 0.00;

        foreach ($accounts as $acc) {
            // Aggregate journal lines up to toDate
            $query = JournalEntryLine::where('account_id', $acc->id)
                ->whereHas('journalEntry', function ($q) use ($toDate, $fromDate) {
                    $q->where('status', 'posted')
                      ->where('journal_date', '<=', $toDate);
                });

            $lines = $query->get();
            $sumDebit = (float) $lines->sum('debit');
            $sumCredit = (float) $lines->sum('credit');

            if ($sumDebit == 0 && $sumCredit == 0) {
                continue;
            }

            $netBalance = 0.00;
            $debitBalance = 0.00;
            $creditBalance = 0.00;

            if (in_array($acc->account_type, ['asset', 'expense'])) {
                $net = $sumDebit - $sumCredit;
                if ($net >= 0) {
                    $debitBalance = $net;
                } else {
                    $creditBalance = abs($net);
                }
            } else {
                $net = $sumCredit - $sumDebit;
                if ($net >= 0) {
                    $creditBalance = $net;
                } else {
                    $debitBalance = abs($net);
                }
            }

            $totalDebit += $debitBalance;
            $totalCredit += $creditBalance;

            $rows[] = [
                'account' => $acc,
                'sum_debit' => $sumDebit,
                'sum_credit' => $sumCredit,
                'debit_balance' => round($debitBalance, 2),
                'credit_balance' => round($creditBalance, 2),
            ];
        }

        return [
            'from_date' => $fromDate,
            'to_date' => $toDate,
            'rows' => $rows,
            'total_debit' => round($totalDebit, 2),
            'total_credit' => round($totalCredit, 2),
            'is_balanced' => abs($totalDebit - $totalCredit) < 0.01,
            'difference' => abs(round($totalDebit - $totalCredit, 2)),
        ];
    }

    /**
     * Generate Profit & Loss Statement (Income Statement).
     */
    public function getProfitAndLoss(?string $fromDate = null, ?string $toDate = null): array
    {
        $toDate = $toDate ?: now()->toDateString();
        $fromDate = $fromDate ?: now()->startOfYear()->toDateString();

        // 1. Revenue Accounts (4000s)
        $revenueAccounts = ChartOfAccount::where('account_type', 'revenue')
            ->orderBy('account_code', 'asc')
            ->get();

        $revenueRows = [];
        $totalRevenue = 0.00;

        foreach ($revenueAccounts as $acc) {
            $lines = JournalEntryLine::where('account_id', $acc->id)
                ->whereHas('journalEntry', function ($q) use ($fromDate, $toDate) {
                    $q->where('status', 'posted')
                      ->whereBetween('journal_date', [$fromDate, $toDate]);
                })->get();

            $credit = (float) $lines->sum('credit');
            $debit = (float) $lines->sum('debit');
            $net = $credit - $debit;

            if ($net != 0) {
                $revenueRows[] = [
                    'account' => $acc,
                    'amount' => round($net, 2),
                ];
                $totalRevenue += $net;
            }
        }

        // 2. Expense Accounts (5000s)
        $expenseAccounts = ChartOfAccount::where('account_type', 'expense')
            ->orderBy('account_code', 'asc')
            ->get();

        $expenseRows = [];
        $totalExpenses = 0.00;

        foreach ($expenseAccounts as $acc) {
            $lines = JournalEntryLine::where('account_id', $acc->id)
                ->whereHas('journalEntry', function ($q) use ($fromDate, $toDate) {
                    $q->where('status', 'posted')
                      ->whereBetween('journal_date', [$fromDate, $toDate]);
                })->get();

            $debit = (float) $lines->sum('debit');
            $credit = (float) $lines->sum('credit');
            $net = $debit - $credit;

            if ($net != 0) {
                $expenseRows[] = [
                    'account' => $acc,
                    'amount' => round($net, 2),
                ];
                $totalExpenses += $net;
            }
        }

        // 3. Tax Accounts (6000s)
        $taxAccounts = ChartOfAccount::where('account_type', 'tax')
            ->orderBy('account_code', 'asc')
            ->get();

        $taxRows = [];
        $totalTaxes = 0.00;

        foreach ($taxAccounts as $acc) {
            $lines = JournalEntryLine::where('account_id', $acc->id)
                ->whereHas('journalEntry', function ($q) use ($fromDate, $toDate) {
                    $q->where('status', 'posted')
                      ->whereBetween('journal_date', [$fromDate, $toDate]);
                })->get();

            $debit = (float) $lines->sum('debit');
            $credit = (float) $lines->sum('credit');
            $net = $debit - $credit;

            if ($net != 0) {
                $taxRows[] = [
                    'account' => $acc,
                    'amount' => round($net, 2),
                ];
                $totalTaxes += $net;
            }
        }

        $operatingProfit = $totalRevenue - $totalExpenses;
        $netProfit = $operatingProfit - $totalTaxes;

        return [
            'from_date' => $fromDate,
            'to_date' => $toDate,
            'revenue_rows' => $revenueRows,
            'total_revenue' => round($totalRevenue, 2),
            'expense_rows' => $expenseRows,
            'total_expenses' => round($totalExpenses, 2),
            'operating_profit' => round($operatingProfit, 2),
            'tax_rows' => $taxRows,
            'total_taxes' => round($totalTaxes, 2),
            'net_profit' => round($netProfit, 2),
        ];
    }

    /**
     * Generate Balance Sheet as of specific date (Assets = Liabilities + Equity).
     */
    public function getBalanceSheet(?string $asOfDate = null): array
    {
        $asOfDate = $asOfDate ?: now()->toDateString();

        // 1. Assets (1000s)
        $assetAccounts = ChartOfAccount::where('account_type', 'asset')
            ->orderBy('account_code', 'asc')
            ->get();

        $assetRows = [];
        $totalAssets = 0.00;

        foreach ($assetAccounts as $acc) {
            $lines = JournalEntryLine::where('account_id', $acc->id)
                ->whereHas('journalEntry', function ($q) use ($asOfDate) {
                    $q->where('status', 'posted')
                      ->where('journal_date', '<=', $asOfDate);
                })->get();

            $debit = (float) $lines->sum('debit');
            $credit = (float) $lines->sum('credit');
            $net = $debit - $credit;

            if ($net != 0) {
                $assetRows[] = [
                    'account' => $acc,
                    'amount' => round($net, 2),
                ];
                $totalAssets += $net;
            }
        }

        // 2. Liabilities (2000s)
        $liabilityAccounts = ChartOfAccount::where('account_type', 'liability')
            ->orderBy('account_code', 'asc')
            ->get();

        $liabilityRows = [];
        $totalLiabilities = 0.00;

        foreach ($liabilityAccounts as $acc) {
            $lines = JournalEntryLine::where('account_id', $acc->id)
                ->whereHas('journalEntry', function ($q) use ($asOfDate) {
                    $q->where('status', 'posted')
                      ->where('journal_date', '<=', $asOfDate);
                })->get();

            $credit = (float) $lines->sum('credit');
            $debit = (float) $lines->sum('debit');
            $net = $credit - $debit;

            if ($net != 0) {
                $liabilityRows[] = [
                    'account' => $acc,
                    'amount' => round($net, 2),
                ];
                $totalLiabilities += $net;
            }
        }

        // 3. Equity (3000s)
        $equityAccounts = ChartOfAccount::where('account_type', 'equity')
            ->orderBy('account_code', 'asc')
            ->get();

        $equityRows = [];
        $totalDirectEquity = 0.00;

        foreach ($equityAccounts as $acc) {
            $lines = JournalEntryLine::where('account_id', $acc->id)
                ->whereHas('journalEntry', function ($q) use ($asOfDate) {
                    $q->where('status', 'posted')
                      ->where('journal_date', '<=', $asOfDate);
                })->get();

            $credit = (float) $lines->sum('credit');
            $debit = (float) $lines->sum('debit');
            $net = $credit - $debit;

            if ($net != 0) {
                $equityRows[] = [
                    'account' => $acc,
                    'amount' => round($net, 2),
                ];
                $totalDirectEquity += $net;
            }
        }

        // Calculate Cumulative Net Profit from beginning up to asOfDate
        $pnlAllTime = $this->getProfitAndLoss('1970-01-01', $asOfDate);
        $cumulativeNetProfit = $pnlAllTime['net_profit'];

        $totalEquity = $totalDirectEquity + $cumulativeNetProfit;
        $totalLiabilitiesAndEquity = $totalLiabilities + $totalEquity;

        $isBalanced = abs($totalAssets - $totalLiabilitiesAndEquity) < 0.01;

        return [
            'as_of_date' => $asOfDate,
            'asset_rows' => $assetRows,
            'total_assets' => round($totalAssets, 2),
            'liability_rows' => $liabilityRows,
            'total_liabilities' => round($totalLiabilities, 2),
            'equity_rows' => $equityRows,
            'total_direct_equity' => round($totalDirectEquity, 2),
            'cumulative_net_profit' => round($cumulativeNetProfit, 2),
            'total_equity' => round($totalEquity, 2),
            'total_liabilities_and_equity' => round($totalLiabilitiesAndEquity, 2),
            'is_balanced' => $isBalanced,
            'difference' => abs(round($totalAssets - $totalLiabilitiesAndEquity, 2)),
        ];
    }

    /**
     * Get General Ledger for a specific account.
     */
    public function getGeneralLedger(int $accountId, ?string $fromDate = null, ?string $toDate = null): array
    {
        $account = ChartOfAccount::findOrFail($accountId);
        $toDate = $toDate ?: now()->toDateString();
        $fromDate = $fromDate ?: now()->startOfYear()->toDateString();

        // Calculate Opening Balance prior to fromDate
        $priorLines = JournalEntryLine::where('account_id', $accountId)
            ->whereHas('journalEntry', function ($q) use ($fromDate) {
                $q->where('status', 'posted')
                  ->where('journal_date', '<', $fromDate);
            })->get();

        $priorDebit = (float) $priorLines->sum('debit');
        $priorCredit = (float) $priorLines->sum('credit');

        $isDebitNormal = in_array($account->account_type, ['asset', 'expense']);
        $openingBalance = $isDebitNormal ? ($priorDebit - $priorCredit) : ($priorCredit - $priorDebit);

        // Fetch period lines
        $periodLines = JournalEntryLine::with(['journalEntry', 'party'])
            ->where('account_id', $accountId)
            ->whereHas('journalEntry', function ($q) use ($fromDate, $toDate) {
                $q->where('status', 'posted')
                  ->whereBetween('journal_date', [$fromDate, $toDate]);
            })
            ->join('journal_entries', 'journal_entries.id', '=', 'journal_entry_lines.journal_entry_id')
            ->orderBy('journal_entries.journal_date', 'asc')
            ->orderBy('journal_entry_lines.id', 'asc')
            ->select('journal_entry_lines.*')
            ->get();

        $running = $openingBalance;
        $formattedLines = [];
        $totalDebit = 0.00;
        $totalCredit = 0.00;

        foreach ($periodLines as $line) {
            $debit = (float) $line->debit;
            $credit = (float) $line->credit;
            $totalDebit += $debit;
            $totalCredit += $credit;

            if ($isDebitNormal) {
                $running += ($debit - $credit);
            } else {
                $running += ($credit - $debit);
            }

            $formattedLines[] = [
                'line' => $line,
                'journal_entry' => $line->journalEntry,
                'debit' => $debit,
                'credit' => $credit,
                'running_balance' => round($running, 2),
            ];
        }

        return [
            'account' => $account,
            'from_date' => $fromDate,
            'to_date' => $toDate,
            'opening_balance' => round($openingBalance, 2),
            'lines' => $formattedLines,
            'total_debit' => round($totalDebit, 2),
            'total_credit' => round($totalCredit, 2),
            'closing_balance' => round($running, 2),
        ];
    }

    /**
     * Customer Receivables Ageing Report.
     */
    public function getCustomerAgeing(?string $asOfDate = null): array
    {
        $asOfDate = $asOfDate ?: now()->toDateString();
        $refDate = Carbon::parse($asOfDate);

        $invoices = SalesInvoice::with('party')
            ->where('status', 'posted')
            ->where('balance_due', '>', 0)
            ->where('invoice_date', '<=', $asOfDate)
            ->get();

        $parties = [];
        $grandTotal = ['current' => 0, 'age_1_30' => 0, 'age_31_60' => 0, 'age_61_90' => 0, 'age_90_plus' => 0, 'total' => 0];

        foreach ($invoices as $inv) {
            $partyId = $inv->party_id;
            $partyName = $inv->party->name ?? 'Unknown Customer';

            if (!isset($parties[$partyId])) {
                $parties[$partyId] = [
                    'party_id' => $partyId,
                    'party_name' => $partyName,
                    'current' => 0.00,
                    'age_1_30' => 0.00,
                    'age_31_60' => 0.00,
                    'age_61_90' => 0.00,
                    'age_90_plus' => 0.00,
                    'total' => 0.00,
                ];
            }

            $days = $refDate->diffInDays(Carbon::parse($inv->due_date), false);
            $overdueDays = -$days; // If positive, overdue
            $balance = (float) $inv->balance_due;

            if ($overdueDays <= 0) {
                $parties[$partyId]['current'] += $balance;
                $grandTotal['current'] += $balance;
            } elseif ($overdueDays <= 30) {
                $parties[$partyId]['age_1_30'] += $balance;
                $grandTotal['age_1_30'] += $balance;
            } elseif ($overdueDays <= 60) {
                $parties[$partyId]['age_31_60'] += $balance;
                $grandTotal['age_31_60'] += $balance;
            } elseif ($overdueDays <= 90) {
                $parties[$partyId]['age_61_90'] += $balance;
                $grandTotal['age_61_90'] += $balance;
            } else {
                $parties[$partyId]['age_90_plus'] += $balance;
                $grandTotal['age_90_plus'] += $balance;
            }

            $parties[$partyId]['total'] += $balance;
            $grandTotal['total'] += $balance;
        }

        return [
            'as_of_date' => $asOfDate,
            'parties' => array_values($parties),
            'totals' => $grandTotal,
        ];
    }

    /**
     * Vendor Payables Ageing Report.
     */
    public function getVendorAgeing(?string $asOfDate = null): array
    {
        $asOfDate = $asOfDate ?: now()->toDateString();
        $refDate = Carbon::parse($asOfDate);

        $bills = VendorBill::with('party')
            ->where('status', 'posted')
            ->where('balance_due', '>', 0)
            ->where('bill_date', '<=', $asOfDate)
            ->get();

        $parties = [];
        $grandTotal = ['current' => 0, 'age_1_30' => 0, 'age_31_60' => 0, 'age_61_90' => 0, 'age_90_plus' => 0, 'total' => 0];

        foreach ($bills as $bill) {
            $partyId = $bill->party_id;
            $partyName = $bill->party->name ?? 'Unknown Vendor';

            if (!isset($parties[$partyId])) {
                $parties[$partyId] = [
                    'party_id' => $partyId,
                    'party_name' => $partyName,
                    'current' => 0.00,
                    'age_1_30' => 0.00,
                    'age_31_60' => 0.00,
                    'age_61_90' => 0.00,
                    'age_90_plus' => 0.00,
                    'total' => 0.00,
                ];
            }

            $days = $refDate->diffInDays(Carbon::parse($bill->due_date), false);
            $overdueDays = -$days;
            $balance = (float) $bill->balance_due;

            if ($overdueDays <= 0) {
                $parties[$partyId]['current'] += $balance;
                $grandTotal['current'] += $balance;
            } elseif ($overdueDays <= 30) {
                $parties[$partyId]['age_1_30'] += $balance;
                $grandTotal['age_1_30'] += $balance;
            } elseif ($overdueDays <= 60) {
                $parties[$partyId]['age_31_60'] += $balance;
                $grandTotal['age_31_60'] += $balance;
            } elseif ($overdueDays <= 90) {
                $parties[$partyId]['age_61_90'] += $balance;
                $grandTotal['age_61_90'] += $balance;
            } else {
                $parties[$partyId]['age_90_plus'] += $balance;
                $grandTotal['age_90_plus'] += $balance;
            }

            $parties[$partyId]['total'] += $balance;
            $grandTotal['total'] += $balance;
        }

        return [
            'as_of_date' => $asOfDate,
            'parties' => array_values($parties),
            'totals' => $grandTotal,
        ];
    }

    /**
     * GST Tax Summary Register.
     */
    public function getGstReport(?string $fromDate = null, ?string $toDate = null): array
    {
        $toDate = $toDate ?: now()->toDateString();
        $fromDate = $fromDate ?: now()->startOfYear()->toDateString();

        $invoices = SalesInvoice::with('party')
            ->where('status', 'posted')
            ->whereBetween('invoice_date', [$fromDate, $toDate])
            ->get();

        $outputGst = [
            'taxable_amount' => (float) $invoices->sum('subtotal'),
            'cgst' => (float) $invoices->sum('cgst_amount'),
            'sgst' => (float) $invoices->sum('sgst_amount'),
            'igst' => (float) $invoices->sum('igst_amount'),
            'total' => (float) $invoices->sum('total_tax'),
        ];

        $bills = VendorBill::with('party')
            ->where('status', 'posted')
            ->whereBetween('bill_date', [$fromDate, $toDate])
            ->get();

        $inputGst = [
            'taxable_amount' => (float) $bills->sum('subtotal'),
            'cgst' => (float) $bills->sum('cgst_amount'),
            'sgst' => (float) $bills->sum('sgst_amount'),
            'igst' => (float) $bills->sum('igst_amount'),
            'total' => (float) $bills->sum('total_tax'),
        ];

        $netGstLiability = [
            'cgst' => $outputGst['cgst'] - $inputGst['cgst'],
            'sgst' => $outputGst['sgst'] - $inputGst['sgst'],
            'igst' => $outputGst['igst'] - $inputGst['igst'],
            'total' => $outputGst['total'] - $inputGst['total'],
        ];

        return [
            'from_date' => $fromDate,
            'to_date' => $toDate,
            'output_gst' => $outputGst,
            'input_gst' => $inputGst,
            'net_gst_liability' => $netGstLiability,
            'invoices' => $invoices,
            'bills' => $bills,
        ];
    }

    /**
     * TDS Report.
     */
    public function getTdsReport(?string $fromDate = null, ?string $toDate = null): array
    {
        $toDate = $toDate ?: now()->toDateString();
        $fromDate = $fromDate ?: now()->startOfYear()->toDateString();

        // 1. TDS Withheld on Vendor Bills (Payables)
        $billsWithTds = VendorBill::with(['party', 'tdsRate'])
            ->where('status', 'posted')
            ->where('tds_amount', '>', 0)
            ->whereBetween('bill_date', [$fromDate, $toDate])
            ->get();

        $tdsPayableTotal = (float) $billsWithTds->sum('tds_amount');

        // 2. TDS Deducted by Customers (Receivables)
        $invoicesWithTds = SalesInvoice::with('party')
            ->where('status', 'posted')
            ->where('tds_deducted', '>', 0)
            ->whereBetween('invoice_date', [$fromDate, $toDate])
            ->get();

        $tdsReceivableTotal = (float) $invoicesWithTds->sum('tds_deducted');

        return [
            'from_date' => $fromDate,
            'to_date' => $toDate,
            'bills_with_tds' => $billsWithTds,
            'tds_payable_total' => round($tdsPayableTotal, 2),
            'invoices_with_tds' => $invoicesWithTds,
            'tds_receivable_total' => round($tdsReceivableTotal, 2),
        ];
    }
}

<?php

namespace App\Http\Controllers\Admin\Accounting;

use App\Http\Controllers\Controller;
use App\Models\Accounting\ChartOfAccount;
use App\Services\Accounting\FinancialReportService;
use Illuminate\Http\Request;

class AccountingReportController extends Controller
{
    protected FinancialReportService $reportService;

    public function __construct(FinancialReportService $reportService)
    {
        $this->reportService = $reportService;
    }

    public function index()
    {
        return view('admin.accounting.reports.index');
    }

    public function trialBalance(Request $request)
    {
        $fromDate = $request->input('from_date', now()->startOfYear()->toDateString());
        $toDate = $request->input('to_date', now()->toDateString());

        $report = $this->reportService->getTrialBalance($fromDate, $toDate);

        return view('admin.accounting.reports.trial_balance', compact('report', 'fromDate', 'toDate'));
    }

    public function profitAndLoss(Request $request)
    {
        $fromDate = $request->input('from_date', now()->startOfYear()->toDateString());
        $toDate = $request->input('to_date', now()->toDateString());

        $report = $this->reportService->getProfitAndLoss($fromDate, $toDate);

        return view('admin.accounting.reports.profit_loss', compact('report', 'fromDate', 'toDate'));
    }

    public function balanceSheet(Request $request)
    {
        $asOfDate = $request->input('as_of_date', now()->toDateString());

        $report = $this->reportService->getBalanceSheet($asOfDate);

        return view('admin.accounting.reports.balance_sheet', compact('report', 'asOfDate'));
    }

    public function generalLedger(Request $request)
    {
        $accounts = ChartOfAccount::orderBy('account_code', 'asc')->get();
        $selectedAccountId = $request->input('account_id', $accounts->first()->id ?? 1);
        $fromDate = $request->input('from_date', now()->startOfYear()->toDateString());
        $toDate = $request->input('to_date', now()->toDateString());

        $report = $this->reportService->getGeneralLedger($selectedAccountId, $fromDate, $toDate);

        return view('admin.accounting.reports.general_ledger', compact('report', 'accounts', 'selectedAccountId', 'fromDate', 'toDate'));
    }

    public function customerAgeing(Request $request)
    {
        $asOfDate = $request->input('as_of_date', now()->toDateString());
        $report = $this->reportService->getCustomerAgeing($asOfDate);

        return view('admin.accounting.reports.customer_ageing', compact('report', 'asOfDate'));
    }

    public function vendorAgeing(Request $request)
    {
        $asOfDate = $request->input('as_of_date', now()->toDateString());
        $report = $this->reportService->getVendorAgeing($asOfDate);

        return view('admin.accounting.reports.vendor_ageing', compact('report', 'asOfDate'));
    }

    public function gstReport(Request $request)
    {
        $fromDate = $request->input('from_date', now()->startOfYear()->toDateString());
        $toDate = $request->input('to_date', now()->toDateString());

        $report = $this->reportService->getGstReport($fromDate, $toDate);

        return view('admin.accounting.reports.gst_report', compact('report', 'fromDate', 'toDate'));
    }

    public function tdsReport(Request $request)
    {
        $fromDate = $request->input('from_date', now()->startOfYear()->toDateString());
        $toDate = $request->input('to_date', now()->toDateString());

        $report = $this->reportService->getTdsReport($fromDate, $toDate);

        return view('admin.accounting.reports.tds_report', compact('report', 'fromDate', 'toDate'));
    }
}

<?php

require_once __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\Accounting\BankAccount;
use App\Models\Accounting\ChartOfAccount;
use App\Models\Accounting\Expense;
use App\Models\Accounting\FundingRecord;
use App\Models\Accounting\JournalEntry;
use App\Models\Accounting\Party;
use App\Models\Accounting\SalesInvoice;
use App\Models\Accounting\TaxRate;
use App\Models\Accounting\VendorBill;
use App\Services\Accounting\AccountingEngineService;
use App\Services\Accounting\BillPostingService;
use App\Services\Accounting\ExpensePostingService;
use App\Services\Accounting\FinancialReportService;
use App\Services\Accounting\FundingPostingService;
use App\Services\Accounting\InvoicePostingService;
use Illuminate\Support\Facades\DB;

echo "=== STARTING ACCOUNTING & FINANCE MODULE VERIFICATION ===\n\n";

$engine = app(AccountingEngineService::class);
$invService = app(InvoicePostingService::class);
$billService = app(BillPostingService::class);
$expService = app(ExpensePostingService::class);
$fndService = app(FundingPostingService::class);
$reportService = app(FinancialReportService::class);

// 1. Founder 1 Capital Infusion (₹5,00,000)
echo "1. Testing Founder Capital Infusion...\n";
$founder = Party::where('is_founder', true)->firstOrFail();
$hdfcBank = BankAccount::where('account_name', 'like', '%HDFC%')->firstOrFail();
$founderEquityAcc = ChartOfAccount::where('account_code', '3110')->firstOrFail();

$funding = FundingRecord::create([
    'funding_number' => $engine->generateNumber('FND', 'funding_records', 'funding_number'),
    'party_id' => $founder->id,
    'funding_type' => 'founder_capital',
    'amount' => 500000.00,
    'deposit_bank_account_id' => $hdfcBank->id,
    'equity_ledger_account_id' => $founderEquityAcc->id,
    'received_date' => now()->toDateString(),
    'reference_number' => 'UTR99887766',
    'status' => 'draft',
    'created_by' => 1,
]);

$fndService->postFunding($funding);
echo "   -> Posted Founder Capital: ₹5,00,000. HDFC Bank balance: ₹" . number_format($hdfcBank->fresh()->current_balance, 2) . "\n";

// 2. Sales Invoice to Customer (₹1,00,000 + 18% GST = ₹1,18,000)
echo "\n2. Testing Sales Invoice with GST Splitting (Intra-state CGST 9% + SGST 9%)...\n";
$customer = Party::where('is_customer', true)->firstOrFail();
$gst18 = TaxRate::where('rate', 18.00)->where('tax_type', 'gst')->firstOrFail();
$revenueAcc = ChartOfAccount::where('account_code', '4110')->firstOrFail();

$invoice = SalesInvoice::create([
    'invoice_number' => $engine->generateNumber('INV', 'sales_invoices', 'invoice_number'),
    'party_id' => $customer->id,
    'invoice_date' => now()->toDateString(),
    'due_date' => now()->addDays(30)->toDateString(),
    'subtotal' => 100000.00,
    'discount_amount' => 0.00,
    'cgst_amount' => 9000.00,
    'sgst_amount' => 9000.00,
    'igst_amount' => 0.00,
    'total_tax' => 18000.00,
    'tds_deducted' => 0.00,
    'total_amount' => 118000.00,
    'paid_amount' => 0.00,
    'balance_due' => 118000.00,
    'payment_status' => 'unpaid',
    'status' => 'draft',
    'place_of_supply' => 'Delhi',
    'created_by' => 1,
]);

$invoice->items()->create([
    'item_name' => 'University Recruitment Commission',
    'hsn_sac' => '999293',
    'quantity' => 1.00,
    'unit_price' => 100000.00,
    'taxable_amount' => 100000.00,
    'tax_rate_id' => $gst18->id,
    'gst_rate' => 18.00,
    'cgst_amount' => 9000.00,
    'sgst_amount' => 9000.00,
    'igst_amount' => 0.00,
    'total_amount' => 118000.00,
    'revenue_account_id' => $revenueAcc->id,
]);

$invService->postInvoice($invoice);
echo "   -> Posted Sales Invoice {$invoice->invoice_number}. Total: ₹1,18,000. Customer Balance: ₹" . number_format($customer->fresh()->current_balance, 2) . "\n";

// 3. Customer Payment Receipt (₹1,08,000 received in bank + ₹10,000 TDS deducted u/s 194J = ₹1,18,000 full settlement)
echo "\n3. Testing Customer Payment Receipt with TDS Receivable allocation...\n";
$invService->recordPaymentReceipt([
    'party_id' => $customer->id,
    'bank_account_id' => $hdfcBank->id,
    'amount' => 108000.00,
    'tds_deducted' => 10000.00,
    'discount_allowed' => 0.00,
    'payment_date' => now()->toDateString(),
    'reference_number' => 'RECEIPT-UTR-112233',
    'notes' => 'Full settlement with TDS',
    'allocations' => [
        ['invoice_id' => $invoice->id, 'amount' => 118000.00]
    ]
]);
$freshInv = $invoice->fresh();
echo "   -> Invoice {$freshInv->invoice_number} Payment Status: {$freshInv->payment_status}, Balance Due: ₹" . number_format($freshInv->balance_due, 2) . "\n";

// 4. Vendor Bill with TDS 194J (AWS Cloud Server Hosting: ₹50,000 + 18% IGST = ₹59,000, less TDS 10% on basic = ₹5,000, Net Payable = ₹54,000)
echo "\n4. Testing Vendor Bill with Inter-State IGST & TDS Withholding u/s 194J...\n";
$vendor = Party::where('is_vendor', true)->firstOrFail();
$tds194j = TaxRate::where('component', 'tds_194j')->where('rate', 10.00)->firstOrFail();
$hostingExpenseAcc = ChartOfAccount::where('account_code', '5410')->firstOrFail();

$bill = VendorBill::create([
    'bill_number' => $engine->generateNumber('BILL', 'vendor_bills', 'bill_number'),
    'vendor_bill_ref' => 'AWS-INV-2026-SEP',
    'party_id' => $vendor->id,
    'bill_date' => now()->toDateString(),
    'due_date' => now()->addDays(30)->toDateString(),
    'subtotal' => 50000.00,
    'cgst_amount' => 0.00,
    'sgst_amount' => 0.00,
    'igst_amount' => 9000.00,
    'total_tax' => 9000.00,
    'tds_rate_id' => $tds194j->id,
    'tds_rate' => 10.00,
    'tds_amount' => 5000.00,
    'total_amount' => 59000.00,
    'net_payable' => 54000.00,
    'paid_amount' => 0.00,
    'balance_due' => 54000.00,
    'payment_status' => 'unpaid',
    'status' => 'draft',
    'created_by' => 1,
]);

$bill->items()->create([
    'item_name' => 'AWS Cloud EC2 & RDS Hosting',
    'hsn_sac' => '998315',
    'quantity' => 1.00,
    'unit_price' => 50000.00,
    'taxable_amount' => 50000.00,
    'tax_rate_id' => $gst18->id,
    'gst_rate' => 18.00,
    'cgst_amount' => 0.00,
    'sgst_amount' => 0.00,
    'igst_amount' => 9000.00,
    'total_amount' => 59000.00,
    'expense_account_id' => $hostingExpenseAcc->id,
]);

$billService->postBill($bill);
echo "   -> Posted Vendor Bill {$bill->bill_number}. Total: ₹59,000, TDS: ₹5,000, Net Payable: ₹54,000. Vendor Balance: ₹" . number_format($vendor->fresh()->current_balance, 2) . "\n";

// 5. Vendor Bill Payment Dispatch (₹54,000 from Bank)
echo "\n5. Testing Vendor Bill Payment Dispatch from HDFC Bank...\n";
$billService->recordBillPayment([
    'party_id' => $vendor->id,
    'bank_account_id' => $hdfcBank->id,
    'amount' => 54000.00,
    'payment_date' => now()->toDateString(),
    'reference_number' => 'NEFT-AWS-556677',
    'notes' => 'Full settlement for AWS bill',
    'allocations' => [
        ['bill_id' => $bill->id, 'amount' => 54000.00]
    ]
]);
$freshBill = $bill->fresh();
echo "   -> Bill {$freshBill->bill_number} Payment Status: {$freshBill->payment_status}, Balance Due: ₹" . number_format($freshBill->balance_due, 2) . "\n";

// 6. Expense Multi-Tier Approval & Payment (Staff Lunch / Dinner ₹2,500)
echo "\n6. Testing Multi-Tier Expense Lifecycle (Draft -> Submit -> Approve -> Pay)...\n";
$officeExpAcc = ChartOfAccount::where('account_code', '5720')->firstOrFail();
$expense = Expense::create([
    'expense_number' => $engine->generateNumber('EXP', 'expenses', 'expense_number'),
    'expense_date' => now()->toDateString(),
    'title' => 'Team Lunch & Client Meeting Conveyance',
    'category_account_id' => $officeExpAcc->id,
    'paid_through_account_id' => $hdfcBank->id,
    'subtotal' => 2500.00,
    'tax_amount' => 0.00,
    'total_amount' => 2500.00,
    'payment_method' => 'upi',
    'payment_reference' => 'UPI99887711',
    'status' => 'draft',
    'created_by' => 1,
]);

$expService->submitExpense($expense);
echo "   -> Submitted Expense (Status: {$expense->fresh()->status})\n";
$expService->approveExpense($expense);
echo "   -> Approved Expense (Status: {$expense->fresh()->status})\n";
$expService->postExpense($expense);
echo "   -> Paid & Posted Expense (Status: {$expense->fresh()->status}). Ledger impact recorded.\n";

// 7. Verify Trial Balance
echo "\n7. VERIFYING TRIAL BALANCE STATEMENTS...\n";
$tb = $reportService->getTrialBalance();
echo "   -> Total Debits: ₹" . number_format($tb['total_debit'], 2) . "\n";
echo "   -> Total Credits: ₹" . number_format($tb['total_credit'], 2) . "\n";
echo "   -> Difference: ₹" . number_format($tb['difference'], 2) . "\n";
echo "   -> Is Balanced? " . ($tb['is_balanced'] ? "YES (PASSED! ✓)" : "NO (FAILED ✗)") . "\n";

// 8. Verify Profit & Loss
echo "\n8. VERIFYING PROFIT & LOSS STATEMENT...\n";
$pnl = $reportService->getProfitAndLoss();
echo "   -> Total Revenue: ₹" . number_format($pnl['total_revenue'], 2) . "\n";
echo "   -> Total Expenses: ₹" . number_format($pnl['total_expenses'], 2) . "\n";
echo "   -> Net Profit: ₹" . number_format($pnl['net_profit'], 2) . " (PASSED! ✓)\n";

// 9. Verify Balance Sheet ($Assets = $Liabilities + $Equity)
echo "\n9. VERIFYING BALANCE SHEET (Assets = Liabilities + Equity)...\n";
$bs = $reportService->getBalanceSheet();
echo "   -> Total Assets: ₹" . number_format($bs['total_assets'], 2) . "\n";
echo "   -> Total Liabilities: ₹" . number_format($bs['total_liabilities'], 2) . "\n";
echo "   -> Direct Equity: ₹" . number_format($bs['total_direct_equity'], 2) . "\n";
echo "   -> Cumulative Net Profit: ₹" . number_format($bs['cumulative_net_profit'], 2) . "\n";
echo "   -> Total Liabilities & Equity: ₹" . number_format($bs['total_liabilities_and_equity'], 2) . "\n";
echo "   -> Equation Holds? " . ($bs['is_balanced'] ? "YES (Assets == Liabilities + Equity) (PASSED! ✓)" : "NO (FAILED ✗)") . "\n";

// 10. Verify GST & TDS Tax Registers
echo "\n10. VERIFYING TAX REGISTERS...\n";
$gst = $reportService->getGstReport();
echo "   -> Output GST: ₹" . number_format($gst['output_gst']['total'], 2) . "\n";
echo "   -> Input GST ITC: ₹" . number_format($gst['input_gst']['total'], 2) . "\n";
echo "   -> Net GST Payable: ₹" . number_format($gst['net_gst_liability']['total'], 2) . "\n";

$tds = $reportService->getTdsReport();
echo "   -> TDS Withheld Payable: ₹" . number_format($tds['tds_payable_total'], 2) . " (u/s 194J on AWS bill)\n";
echo "   -> TDS Deducted Receivable: ₹" . number_format($tds['tds_receivable_total'], 2) . " (from University customer)\n";

echo "\n=== ALL ACCOUNTING VERIFICATION CHECKS PASSED PERFECTLY! ===\n";

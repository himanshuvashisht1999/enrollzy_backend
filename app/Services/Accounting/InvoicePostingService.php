<?php

namespace App\Services\Accounting;

use App\Models\Accounting\AccountingAuditLog;
use App\Models\Accounting\BankAccount;
use App\Models\Accounting\ChartOfAccount;
use App\Models\Accounting\PaymentAllocation;
use App\Models\Accounting\SalesInvoice;
use App\Models\Accounting\TaxRate;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Exception;

class InvoicePostingService
{
    protected AccountingEngineService $engine;

    public function __construct(AccountingEngineService $engine)
    {
        $this->engine = $engine;
    }

    /**
     * Post a Sales Invoice to the General Ledger.
     */
    public function postInvoice(SalesInvoice $invoice): SalesInvoice
    {
        return DB::transaction(function () use ($invoice) {
            if ($invoice->status === 'posted') {
                throw new Exception("Invoice {$invoice->invoice_number} is already posted.");
            }

            // Find Accounts Receivable (1130)
            $arAccount = ChartOfAccount::where('account_code', '1130')->first()
                ?? ChartOfAccount::where('name', 'like', '%Receivable%')->firstOrFail();

            // Default Revenue Account (4110)
            $defaultRevenueAccount = ChartOfAccount::where('account_code', '4110')->first()
                ?? ChartOfAccount::where('account_type', 'revenue')->firstOrFail();

            $lines = [];

            // 1. DEBIT: Accounts Receivable (Customer Party)
            $lines[] = [
                'account_id' => $arAccount->id,
                'party_id' => $invoice->party_id,
                'debit' => $invoice->total_amount,
                'credit' => 0.00,
                'description' => "Sales Invoice {$invoice->invoice_number} to {$invoice->party->name}",
                'reference' => $invoice->invoice_number,
            ];

            // 2. CREDIT: Revenue Accounts (per line item)
            $groupedRevenue = [];
            foreach ($invoice->items as $item) {
                $revAccId = $item->revenue_account_id ?? $defaultRevenueAccount->id;
                if (!isset($groupedRevenue[$revAccId])) {
                    $groupedRevenue[$revAccId] = 0.00;
                }
                $groupedRevenue[$revAccId] += (float) $item->taxable_amount;
            }

            foreach ($groupedRevenue as $accId => $taxableTotal) {
                if ($taxableTotal > 0) {
                    $lines[] = [
                        'account_id' => $accId,
                        'party_id' => $invoice->party_id,
                        'debit' => 0.00,
                        'credit' => round($taxableTotal, 2),
                        'description' => "Revenue from Invoice {$invoice->invoice_number}",
                        'reference' => $invoice->invoice_number,
                    ];
                }
            }

            // 3. CREDIT: GST Output Taxes (CGST / SGST / IGST)
            if ($invoice->cgst_amount > 0) {
                $cgstAcc = ChartOfAccount::where('account_code', '2121')->first()
                    ?? ChartOfAccount::where('name', 'like', '%Output CGST%')->firstOrFail();
                $lines[] = [
                    'account_id' => $cgstAcc->id,
                    'party_id' => $invoice->party_id,
                    'debit' => 0.00,
                    'credit' => (float) $invoice->cgst_amount,
                    'description' => "Output CGST on Invoice {$invoice->invoice_number}",
                    'tax_code' => 'CGST',
                    'reference' => $invoice->invoice_number,
                ];
            }

            if ($invoice->sgst_amount > 0) {
                $sgstAcc = ChartOfAccount::where('account_code', '2122')->first()
                    ?? ChartOfAccount::where('name', 'like', '%Output SGST%')->firstOrFail();
                $lines[] = [
                    'account_id' => $sgstAcc->id,
                    'party_id' => $invoice->party_id,
                    'debit' => 0.00,
                    'credit' => (float) $invoice->sgst_amount,
                    'description' => "Output SGST on Invoice {$invoice->invoice_number}",
                    'tax_code' => 'SGST',
                    'reference' => $invoice->invoice_number,
                ];
            }

            if ($invoice->igst_amount > 0) {
                $igstAcc = ChartOfAccount::where('account_code', '2123')->first()
                    ?? ChartOfAccount::where('name', 'like', '%Output IGST%')->firstOrFail();
                $lines[] = [
                    'account_id' => $igstAcc->id,
                    'party_id' => $invoice->party_id,
                    'debit' => 0.00,
                    'credit' => (float) $invoice->igst_amount,
                    'description' => "Output IGST on Invoice {$invoice->invoice_number}",
                    'tax_code' => 'IGST',
                    'reference' => $invoice->invoice_number,
                ];
            }

            // Post the double-entry transaction
            $transaction = $this->engine->postJournal([
                'organization_id' => $invoice->organization_id,
                'transaction_date' => $invoice->invoice_date->toDateString(),
                'transaction_type' => 'sales_invoice',
                'reference_type' => 'SalesInvoice',
                'reference_id' => $invoice->id,
                'description' => "Sales Invoice {$invoice->invoice_number} for {$invoice->party->name}",
                'entry_type' => 'auto',
                'created_by' => Auth::guard('admin')->id() ?? $invoice->created_by ?? 1,
            ], $lines);

            $adminId = Auth::guard('admin')->id() ?? 1;

            $invoice->update([
                'status' => 'posted',
                'posted_by' => $adminId,
                'posted_at' => now(),
                'transaction_id' => $transaction->id,
            ]);

            return $invoice;
        });
    }

    /**
     * Record a Customer Payment Receipt and allocate against open invoices.
     */
    public function recordPaymentReceipt(array $data)
    {
        return DB::transaction(function () use ($data) {
            $adminId = Auth::guard('admin')->id() ?? 1;
            $bankAccount = BankAccount::findOrFail($data['bank_account_id']);
            $amountReceived = (float) $data['amount'];
            $tdsDeducted = (float) ($data['tds_deducted'] ?? 0);
            $discountAllowed = (float) ($data['discount_allowed'] ?? 0);
            $totalCreditAR = $amountReceived + $tdsDeducted + $discountAllowed;

            if ($totalCreditAR <= 0) {
                throw new Exception("Payment amount must be greater than zero.");
            }

            // Find Accounts Receivable (1130)
            $arAccount = ChartOfAccount::where('account_code', '1130')->firstOrFail();

            $lines = [];

            // 1. DEBIT: Bank Account
            $lines[] = [
                'account_id' => $bankAccount->ledger_account_id,
                'party_id' => $data['party_id'],
                'debit' => $amountReceived,
                'credit' => 0.00,
                'description' => "Payment received via {$bankAccount->account_name}" . (!empty($data['reference_number']) ? " (Ref: {$data['reference_number']})" : ""),
                'reference' => $data['reference_number'] ?? null,
            ];

            // 2. DEBIT: TDS Receivable (1160) if customer deducted TDS
            if ($tdsDeducted > 0) {
                $tdsRecAccount = ChartOfAccount::where('account_code', '1160')->first()
                    ?? ChartOfAccount::where('name', 'like', '%TDS Receivable%')->firstOrFail();

                $lines[] = [
                    'account_id' => $tdsRecAccount->id,
                    'party_id' => $data['party_id'],
                    'debit' => $tdsDeducted,
                    'credit' => 0.00,
                    'description' => "TDS Deducted by customer on receipt",
                    'tax_code' => 'TDS',
                    'reference' => $data['reference_number'] ?? null,
                ];
            }

            // 3. DEBIT: Discount Allowed (5810 / Expense) if discount was given
            if ($discountAllowed > 0) {
                $discountAccount = ChartOfAccount::where('account_code', '5810')->first()
                    ?? ChartOfAccount::where('account_type', 'expense')->firstOrFail();

                $lines[] = [
                    'account_id' => $discountAccount->id,
                    'party_id' => $data['party_id'],
                    'debit' => $discountAllowed,
                    'credit' => 0.00,
                    'description' => "Cash discount allowed on payment receipt",
                    'reference' => $data['reference_number'] ?? null,
                ];
            }

            // 4. CREDIT: Accounts Receivable (Customer Party)
            $lines[] = [
                'account_id' => $arAccount->id,
                'party_id' => $data['party_id'],
                'debit' => 0.00,
                'credit' => $totalCreditAR,
                'description' => "Payment receipt from Customer Party ID #{$data['party_id']}",
                'reference' => $data['reference_number'] ?? null,
            ];

            // Post Journal
            $transaction = $this->engine->postJournal([
                'organization_id' => $data['organization_id'] ?? null,
                'transaction_date' => $data['payment_date'] ?? now()->toDateString(),
                'transaction_type' => 'receipt',
                'reference_type' => 'PaymentReceipt',
                'reference_id' => null,
                'description' => $data['notes'] ?? "Payment receipt of ₹" . number_format($amountReceived, 2),
                'entry_type' => 'auto',
                'created_by' => $adminId,
            ], $lines);

            // Allocate against open invoices
            $remainingToAllocate = $totalCreditAR;
            $allocationsInput = $data['allocations'] ?? [];

            if (!empty($allocationsInput)) {
                foreach ($allocationsInput as $alloc) {
                    if ($remainingToAllocate <= 0) break;
                    $invId = $alloc['invoice_id'];
                    $allocAmt = min((float) $alloc['amount'], $remainingToAllocate);

                    if ($allocAmt > 0) {
                        $invoice = SalesInvoice::find($invId);
                        if ($invoice) {
                            $this->applyAllocationToInvoice($invoice, $transaction->id, $allocAmt, $data['payment_date'] ?? now()->toDateString());
                            $remainingToAllocate -= $allocAmt;
                        }
                    }
                }
            } else {
                // Auto-allocate FIFO
                $openInvoices = SalesInvoice::where('party_id', $data['party_id'])
                    ->whereIn('payment_status', ['unpaid', 'partially_paid', 'overdue'])
                    ->where('status', 'posted')
                    ->orderBy('invoice_date', 'asc')
                    ->get();

                foreach ($openInvoices as $invoice) {
                    if ($remainingToAllocate <= 0) break;
                    $allocAmt = min($invoice->balance_due, $remainingToAllocate);

                    if ($allocAmt > 0) {
                        $this->applyAllocationToInvoice($invoice, $transaction->id, $allocAmt, $data['payment_date'] ?? now()->toDateString());
                        $remainingToAllocate -= $allocAmt;
                    }
                }
            }

            return $transaction;
        });
    }

    protected function applyAllocationToInvoice(SalesInvoice $invoice, int $transactionId, float $amount, string $date): void
    {
        PaymentAllocation::create([
            'organization_id' => $invoice->organization_id,
            'transaction_id' => $transactionId,
            'allocatable_type' => 'sales_invoice',
            'allocatable_id' => $invoice->id,
            'allocated_amount' => $amount,
            'allocation_date' => $date,
            'notes' => "Allocated against Invoice {$invoice->invoice_number}",
        ]);

        $newPaid = $invoice->paid_amount + $amount;
        $newBalance = max(0, $invoice->total_amount - $newPaid);
        $newStatus = $newBalance <= 0 ? 'paid' : ($newPaid > 0 ? 'partially_paid' : 'unpaid');

        $invoice->update([
            'paid_amount' => $newPaid,
            'balance_due' => $newBalance,
            'payment_status' => $newStatus,
        ]);
    }
}

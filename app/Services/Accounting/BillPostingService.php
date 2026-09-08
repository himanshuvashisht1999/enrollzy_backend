<?php

namespace App\Services\Accounting;

use App\Models\Accounting\BankAccount;
use App\Models\Accounting\ChartOfAccount;
use App\Models\Accounting\PaymentAllocation;
use App\Models\Accounting\VendorBill;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Exception;

class BillPostingService
{
    protected AccountingEngineService $engine;

    public function __construct(AccountingEngineService $engine)
    {
        $this->engine = $engine;
    }

    /**
     * Post a Vendor Bill to the General Ledger.
     */
    public function postBill(VendorBill $bill): VendorBill
    {
        return DB::transaction(function () use ($bill) {
            if ($bill->status === 'posted') {
                throw new Exception("Bill {$bill->bill_number} is already posted.");
            }

            // Find Accounts Payable (2110)
            $apAccount = ChartOfAccount::where('account_code', '2110')->first()
                ?? ChartOfAccount::where('name', 'like', '%Payable%')->firstOrFail();

            // Default Expense Account (5300)
            $defaultExpenseAccount = ChartOfAccount::where('account_code', '5300')->first()
                ?? ChartOfAccount::where('account_type', 'expense')->firstOrFail();

            $lines = [];

            // 1. DEBIT: Expense Accounts (per line item)
            $groupedExpense = [];
            foreach ($bill->items as $item) {
                $expAccId = $item->expense_account_id ?? $defaultExpenseAccount->id;
                if (!isset($groupedExpense[$expAccId])) {
                    $groupedExpense[$expAccId] = 0.00;
                }
                $groupedExpense[$expAccId] += (float) $item->taxable_amount;
            }

            foreach ($groupedExpense as $accId => $taxableTotal) {
                if ($taxableTotal > 0) {
                    $lines[] = [
                        'account_id' => $accId,
                        'party_id' => $bill->party_id,
                        'debit' => round($taxableTotal, 2),
                        'credit' => 0.00,
                        'description' => "Expense from Vendor Bill {$bill->bill_number}",
                        'reference' => $bill->bill_number,
                    ];
                }
            }

            // 2. DEBIT: Input GST Credits (CGST / SGST / IGST)
            if ($bill->cgst_amount > 0) {
                $cgstAcc = ChartOfAccount::where('account_code', '1151')->first()
                    ?? ChartOfAccount::where('name', 'like', '%Input CGST%')->firstOrFail();
                $lines[] = [
                    'account_id' => $cgstAcc->id,
                    'party_id' => $bill->party_id,
                    'debit' => (float) $bill->cgst_amount,
                    'credit' => 0.00,
                    'description' => "Input CGST Credit on Bill {$bill->bill_number}",
                    'tax_code' => 'CGST_INPUT',
                    'reference' => $bill->bill_number,
                ];
            }

            if ($bill->sgst_amount > 0) {
                $sgstAcc = ChartOfAccount::where('account_code', '1152')->first()
                    ?? ChartOfAccount::where('name', 'like', '%Input SGST%')->firstOrFail();
                $lines[] = [
                    'account_id' => $sgstAcc->id,
                    'party_id' => $bill->party_id,
                    'debit' => (float) $bill->sgst_amount,
                    'credit' => 0.00,
                    'description' => "Input SGST Credit on Bill {$bill->bill_number}",
                    'tax_code' => 'SGST_INPUT',
                    'reference' => $bill->bill_number,
                ];
            }

            if ($bill->igst_amount > 0) {
                $igstAcc = ChartOfAccount::where('account_code', '1153')->first()
                    ?? ChartOfAccount::where('name', 'like', '%Input IGST%')->firstOrFail();
                $lines[] = [
                    'account_id' => $igstAcc->id,
                    'party_id' => $bill->party_id,
                    'debit' => (float) $bill->igst_amount,
                    'credit' => 0.00,
                    'description' => "Input IGST Credit on Bill {$bill->bill_number}",
                    'tax_code' => 'IGST_INPUT',
                    'reference' => $bill->bill_number,
                ];
            }

            // 3. CREDIT: TDS Payable (if TDS is withheld by company)
            if ($bill->tds_amount > 0) {
                $tdsSection = $bill->tdsRate ? $bill->tdsRate->section : null;
                $tdsCode = '2130';
                if ($tdsSection === '194C') $tdsCode = '2131';
                elseif ($tdsSection === '194J') $tdsCode = '2132';
                elseif ($tdsSection === '194I') $tdsCode = '2133';

                $tdsPayableAccount = ChartOfAccount::where('account_code', $tdsCode)->first()
                    ?? ChartOfAccount::where('name', 'like', '%TDS Payable%')->firstOrFail();

                $lines[] = [
                    'account_id' => $tdsPayableAccount->id,
                    'party_id' => $bill->party_id,
                    'debit' => 0.00,
                    'credit' => (float) $bill->tds_amount,
                    'description' => "TDS Withheld u/s {$tdsSection} on Bill {$bill->bill_number}",
                    'tax_code' => "TDS_{$tdsSection}",
                    'reference' => $bill->bill_number,
                ];
            }

            // 4. CREDIT: Accounts Payable (Vendor Party) for Net Payable Amount
            $lines[] = [
                'account_id' => $apAccount->id,
                'party_id' => $bill->party_id,
                'debit' => 0.00,
                'credit' => (float) $bill->net_payable,
                'description' => "Vendor Bill {$bill->bill_number} from {$bill->party->name}" . ($bill->vendor_bill_ref ? " (Ref: {$bill->vendor_bill_ref})" : ""),
                'reference' => $bill->bill_number,
            ];

            // Post Journal
            $transaction = $this->engine->postJournal([
                'organization_id' => $bill->organization_id,
                'transaction_date' => $bill->bill_date->toDateString(),
                'transaction_type' => 'purchase_bill',
                'reference_type' => 'VendorBill',
                'reference_id' => $bill->id,
                'description' => "Vendor Bill {$bill->bill_number} from {$bill->party->name}",
                'entry_type' => 'auto',
                'created_by' => Auth::guard('admin')->id() ?? $bill->created_by ?? 1,
            ], $lines);

            $adminId = Auth::guard('admin')->id() ?? 1;

            $bill->update([
                'status' => 'posted',
                'posted_by' => $adminId,
                'posted_at' => now(),
                'transaction_id' => $transaction->id,
            ]);

            return $bill;
        });
    }

    /**
     * Record a Vendor Payment and allocate against open bills.
     */
    public function recordBillPayment(array $data)
    {
        return DB::transaction(function () use ($data) {
            $adminId = Auth::guard('admin')->id() ?? 1;
            $bankAccount = BankAccount::findOrFail($data['bank_account_id']);
            $amountPaid = (float) $data['amount'];

            if ($amountPaid <= 0) {
                throw new Exception("Payment amount must be greater than zero.");
            }

            // Find Accounts Payable (2110)
            $apAccount = ChartOfAccount::where('account_code', '2110')->firstOrFail();

            $lines = [];

            // 1. DEBIT: Accounts Payable (Vendor Party)
            $lines[] = [
                'account_id' => $apAccount->id,
                'party_id' => $data['party_id'],
                'debit' => $amountPaid,
                'credit' => 0.00,
                'description' => "Payment made to Vendor Party ID #{$data['party_id']}",
                'reference' => $data['reference_number'] ?? null,
            ];

            // 2. CREDIT: Bank Account
            $lines[] = [
                'account_id' => $bankAccount->ledger_account_id,
                'party_id' => $data['party_id'],
                'debit' => 0.00,
                'credit' => $amountPaid,
                'description' => "Payment dispatched via {$bankAccount->account_name}" . (!empty($data['reference_number']) ? " (Ref: {$data['reference_number']})" : ""),
                'reference' => $data['reference_number'] ?? null,
            ];

            // Post Journal
            $transaction = $this->engine->postJournal([
                'organization_id' => $data['organization_id'] ?? null,
                'transaction_date' => $data['payment_date'] ?? now()->toDateString(),
                'transaction_type' => 'payment',
                'reference_type' => 'VendorPayment',
                'reference_id' => null,
                'description' => $data['notes'] ?? "Vendor payment of ₹" . number_format($amountPaid, 2),
                'entry_type' => 'auto',
                'created_by' => $adminId,
            ], $lines);

            // Allocate against open bills
            $remainingToAllocate = $amountPaid;
            $allocationsInput = $data['allocations'] ?? [];

            if (!empty($allocationsInput)) {
                foreach ($allocationsInput as $alloc) {
                    if ($remainingToAllocate <= 0) break;
                    $billId = $alloc['bill_id'];
                    $allocAmt = min((float) $alloc['amount'], $remainingToAllocate);

                    if ($allocAmt > 0) {
                        $bill = VendorBill::find($billId);
                        if ($bill) {
                            $this->applyAllocationToBill($bill, $transaction->id, $allocAmt, $data['payment_date'] ?? now()->toDateString());
                            $remainingToAllocate -= $allocAmt;
                        }
                    }
                }
            } else {
                // Auto-allocate FIFO
                $openBills = VendorBill::where('party_id', $data['party_id'])
                    ->whereIn('payment_status', ['unpaid', 'partially_paid', 'overdue'])
                    ->where('status', 'posted')
                    ->orderBy('bill_date', 'asc')
                    ->get();

                foreach ($openBills as $bill) {
                    if ($remainingToAllocate <= 0) break;
                    $allocAmt = min($bill->balance_due, $remainingToAllocate);

                    if ($allocAmt > 0) {
                        $this->applyAllocationToBill($bill, $transaction->id, $allocAmt, $data['payment_date'] ?? now()->toDateString());
                        $remainingToAllocate -= $allocAmt;
                    }
                }
            }

            return $transaction;
        });
    }

    protected function applyAllocationToBill(VendorBill $bill, int $transactionId, float $amount, string $date): void
    {
        PaymentAllocation::create([
            'organization_id' => $bill->organization_id,
            'transaction_id' => $transactionId,
            'allocatable_type' => 'vendor_bill',
            'allocatable_id' => $bill->id,
            'allocated_amount' => $amount,
            'allocation_date' => $date,
            'notes' => "Allocated against Bill {$bill->bill_number}",
        ]);

        $newPaid = $bill->paid_amount + $amount;
        $newBalance = max(0, $bill->net_payable - $newPaid);
        $newStatus = $newBalance <= 0 ? 'paid' : ($newPaid > 0 ? 'partially_paid' : 'unpaid');

        $bill->update([
            'paid_amount' => $newPaid,
            'balance_due' => $newBalance,
            'payment_status' => $newStatus,
        ]);
    }
}

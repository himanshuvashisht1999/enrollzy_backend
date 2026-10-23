<?php

namespace App\Services\Accounting;

use App\Models\Accounting\BankAccount;
use App\Models\Accounting\ChartOfAccount;
use App\Models\Accounting\Expense;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Exception;

class ExpensePostingService
{
    protected AccountingEngineService $engine;

    public function __construct(AccountingEngineService $engine)
    {
        $this->engine = $engine;
    }

    /**
     * Submit an expense for approval.
     */
    public function submitExpense(Expense $expense): Expense
    {
        $adminId = Auth::guard('admin')->id() ?? 1;
        $expense->update([
            'status' => 'submitted',
            'submitted_by' => $adminId,
            'submitted_at' => now(),
        ]);

        return $expense;
    }

    /**
     * Approve an expense.
     */
    public function approveExpense(Expense $expense): Expense
    {
        $adminId = Auth::guard('admin')->id() ?? 1;
        $expense->update([
            'status' => 'approved',
            'approved_by' => $adminId,
            'approved_at' => now(),
        ]);

        return $expense;
    }

    /**
     * Reject an expense with a reason.
     */
    public function rejectExpense(Expense $expense, string $reason): Expense
    {
        $adminId = Auth::guard('admin')->id() ?? 1;
        $expense->update([
            'status' => 'rejected',
            'rejection_reason' => $reason,
            'approved_by' => $adminId,
            'approved_at' => now(),
        ]);

        return $expense;
    }

    /**
     * Post an approved expense to the General Ledger.
     */
    public function postExpense(Expense $expense): Expense
    {
        return DB::transaction(function () use ($expense) {
            if ($expense->status === 'posted' || $expense->status === 'paid') {
                throw new Exception("Expense {$expense->expense_number} is already posted.");
            }

            if (!in_array($expense->status, ['approved', 'submitted', 'draft'])) {
                throw new Exception("Expense {$expense->expense_number} cannot be posted from current status '{$expense->status}'.");
            }

            $adminId = Auth::guard('admin')->id() ?? 1;

            // Bank / Cash account paid through
            $bankAccount = BankAccount::find($expense->paid_through_account_id);
            if (!$bankAccount) {
                // Fallback to primary cash/bank account
                $bankAccount = BankAccount::where('is_active', true)->firstOrFail();
            }

            $defaultExpenseAccount = ChartOfAccount::where('account_code', '5300')->first()
                ?? ChartOfAccount::where('account_type', 'expense')->firstOrFail();

            $lines = [];

            // 1. DEBIT: Expense Account(s)
            if ($expense->items()->count() > 0) {
                $grouped = [];
                foreach ($expense->items as $item) {
                    $accId = $item->account_id ?? $expense->category_account_id ?? $defaultExpenseAccount->id;
                    if (!isset($grouped[$accId])) {
                        $grouped[$accId] = 0.00;
                    }
                    $grouped[$accId] += (float) $item->amount;
                }

                foreach ($grouped as $accId => $amt) {
                    if ($amt > 0) {
                        $lines[] = [
                            'account_id' => $accId,
                            'party_id' => $expense->party_id,
                            'debit' => round($amt, 2),
                            'credit' => 0.00,
                            'description' => $expense->title,
                            'reference' => $expense->expense_number,
                        ];
                    }
                }
            } else {
                $expAccId = $expense->category_account_id ?? $defaultExpenseAccount->id;
                $lines[] = [
                    'account_id' => $expAccId,
                    'party_id' => $expense->party_id,
                    'debit' => (float) $expense->subtotal,
                    'credit' => 0.00,
                    'description' => $expense->title,
                    'reference' => $expense->expense_number,
                ];
            }

            // 2. DEBIT: GST Input Credit if tax is applicable
            if ($expense->tax_amount > 0) {
                $gstInputAccount = ChartOfAccount::where('account_code', '1150')->first()
                    ?? ChartOfAccount::where('name', 'like', '%Input%')->firstOrFail();

                $lines[] = [
                    'account_id' => $gstInputAccount->id,
                    'party_id' => $expense->party_id,
                    'debit' => (float) $expense->tax_amount,
                    'credit' => 0.00,
                    'description' => "GST Input Credit on Expense {$expense->expense_number}",
                    'reference' => $expense->expense_number,
                ];
            }

            // 3. CREDIT: Bank Account or Cash (Assets: Credit decreases bank balance)
            $lines[] = [
                'account_id' => $bankAccount->ledger_account_id,
                'party_id' => $expense->party_id,
                'debit' => 0.00,
                'credit' => (float) $expense->total_amount,
                'description' => "Expense payment for {$expense->title} via {$bankAccount->account_name}",
                'reference' => $expense->payment_reference ?? $expense->expense_number,
            ];

            // Post journal
            $transaction = $this->engine->postJournal([
                'organization_id' => $expense->organization_id,
                'transaction_date' => $expense->expense_date->toDateString(),
                'transaction_type' => 'expense',
                'reference_type' => 'Expense',
                'reference_id' => $expense->id,
                'description' => "Expense: {$expense->title} (₹" . number_format($expense->total_amount, 2) . ")",
                'entry_type' => 'auto',
                'created_by' => $adminId,
            ], $lines);

            $expense->update([
                'status' => 'paid',
                'posted_by' => $adminId,
                'posted_at' => now(),
                'transaction_id' => $transaction->id,
            ]);

            return $expense;
        });
    }
}

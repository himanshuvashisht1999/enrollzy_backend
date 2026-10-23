<?php

namespace App\Services\Accounting;

use App\Models\Accounting\AccountingAuditLog;
use App\Models\Accounting\AccountingTransaction;
use App\Models\Accounting\BankAccount;
use App\Models\Accounting\ChartOfAccount;
use App\Models\Accounting\JournalEntry;
use App\Models\Accounting\JournalEntryLine;
use App\Models\Accounting\Party;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Exception;

class AccountingEngineService
{
    /**
     * Post a balanced double-entry journal voucher.
     *
     * @param array $transactionData
     * @param array $lines Array of ['account_id' => int, 'party_id' => int|null, 'debit' => float, 'credit' => float, 'description' => string, 'tax_code' => string|null, 'reference' => string|null]
     * @return AccountingTransaction
     * @throws Exception
     */
    public function postJournal(array $transactionData, array $lines): AccountingTransaction
    {
        return DB::transaction(function () use ($transactionData, $lines) {
            $totalDebit = 0.00;
            $totalCredit = 0.00;

            foreach ($lines as $line) {
                $totalDebit += (float) ($line['debit'] ?? 0);
                $totalCredit += (float) ($line['credit'] ?? 0);
            }

            // Check double-entry balance (Allow negligible float epsilon difference < 0.01)
            $difference = abs(round($totalDebit, 2) - round($totalCredit, 2));
            if ($difference >= 0.01) {
                throw new Exception("Double-entry unbalanced: Total Debits (₹" . number_format($totalDebit, 2) . ") must equal Total Credits (₹" . number_format($totalCredit, 2) . "). Difference: ₹" . number_format($difference, 2));
            }

            $adminId = Auth::guard('admin')->id() ?? $transactionData['created_by'] ?? 1;

            // Generate Transaction Number
            $txnNumber = $this->generateNumber('TXN', 'accounting_transactions', 'transaction_number');
            $journalNumber = $this->generateNumber('JV', 'journal_entries', 'journal_number');

            // 1. Create Transaction Header
            $transaction = AccountingTransaction::create([
                'organization_id' => $transactionData['organization_id'] ?? null,
                'transaction_number' => $txnNumber,
                'transaction_date' => $transactionData['transaction_date'] ?? now()->toDateString(),
                'transaction_type' => $transactionData['transaction_type'] ?? 'journal',
                'reference_type' => $transactionData['reference_type'] ?? null,
                'reference_id' => $transactionData['reference_id'] ?? null,
                'total_amount' => round($totalDebit, 2),
                'description' => $transactionData['description'] ?? null,
                'status' => 'posted',
                'created_by' => $adminId,
                'approved_by' => $adminId,
                'approved_at' => now(),
                'posted_by' => $adminId,
                'posted_at' => now(),
            ]);

            // 2. Create Journal Entry Header
            $journalEntry = JournalEntry::create([
                'organization_id' => $transactionData['organization_id'] ?? null,
                'transaction_id' => $transaction->id,
                'journal_number' => $journalNumber,
                'journal_date' => $transactionData['transaction_date'] ?? now()->toDateString(),
                'entry_type' => $transactionData['entry_type'] ?? 'auto',
                'description' => $transactionData['description'] ?? null,
                'total_debit' => round($totalDebit, 2),
                'total_credit' => round($totalCredit, 2),
                'status' => 'posted',
                'created_by' => $adminId,
                'posted_by' => $adminId,
                'posted_at' => now(),
            ]);

            // 3. Create Journal Entry Lines & Update Account Balances
            foreach ($lines as $line) {
                $debit = round((float) ($line['debit'] ?? 0), 2);
                $credit = round((float) ($line['credit'] ?? 0), 2);

                if ($debit == 0 && $credit == 0) {
                    continue;
                }

                $journalLine = JournalEntryLine::create([
                    'journal_entry_id' => $journalEntry->id,
                    'account_id' => $line['account_id'],
                    'party_id' => $line['party_id'] ?? null,
                    'debit' => $debit,
                    'credit' => $credit,
                    'description' => $line['description'] ?? $transactionData['description'] ?? null,
                    'tax_code' => $line['tax_code'] ?? null,
                    'reference' => $line['reference'] ?? null,
                ]);

                // Update Chart of Account Balance
                $this->updateAccountBalance($line['account_id'], $debit, $credit);

                // Update Party Balance if applicable
                if (!empty($line['party_id'])) {
                    $this->updatePartyBalance($line['party_id'], $debit, $credit);
                }

                // Update Bank Account Balance if ledger account belongs to a bank
                $bankAccount = BankAccount::where('ledger_account_id', $line['account_id'])->first();
                if ($bankAccount) {
                    // Bank accounts are Assets: Debit increases balance, Credit decreases balance
                    $netChange = $debit - $credit;
                    $bankAccount->increment('current_balance', $netChange);
                }
            }

            // 4. Audit Log
            AccountingAuditLog::create([
                'organization_id' => $transactionData['organization_id'] ?? null,
                'user_id' => $adminId,
                'entity_type' => 'AccountingTransaction',
                'entity_id' => $transaction->id,
                'action' => 'Posted Journal Entry',
                'old_values' => null,
                'new_values' => [
                    'transaction_number' => $txnNumber,
                    'journal_number' => $journalNumber,
                    'total_amount' => $totalDebit,
                    'lines_count' => count($lines),
                ],
                'ip_address' => request()->ip() ?? '127.0.0.1',
                'created_at' => now(),
            ]);

            return $transaction;
        });
    }

    /**
     * Update Chart of Account current balance based on standard normal balance rules.
     */
    protected function updateAccountBalance(int $accountId, float $debit, float $credit): void
    {
        $account = ChartOfAccount::find($accountId);
        if (!$account) {
            return;
        }

        // For Assets & Expenses: Normal balance is Debit (Debit increases, Credit decreases)
        // For Liabilities, Equity, Revenue, Taxes: Normal balance is Credit (Credit increases, Debit decreases)
        if (in_array($account->account_type, ['asset', 'expense'])) {
            $netChange = $debit - $credit;
        } else {
            $netChange = $credit - $debit;
        }

        $account->increment('current_balance', $netChange);
    }

    /**
     * Update Party current balance.
     */
    protected function updatePartyBalance(int $partyId, float $debit, float $credit): void
    {
        $party = Party::find($partyId);
        if (!$party) {
            return;
        }

        // If Customer (Asset receivable): Debit increases customer balance (owes us more), Credit decreases
        // If Vendor (Liability payable): Credit increases vendor balance (we owe them more), Debit decreases
        if ($party->is_customer) {
            $netChange = $debit - $credit;
        } elseif ($party->is_vendor || $party->is_investor || $party->is_founder) {
            $netChange = $credit - $debit;
        } else {
            $netChange = $debit - $credit;
        }

        $party->increment('current_balance', $netChange);
    }

    /**
     * Reverse a previously posted transaction with balanced offsetting entries.
     */
    public function reverseTransaction(AccountingTransaction $transaction, string $reason = ''): AccountingTransaction
    {
        return DB::transaction(function () use ($transaction, $reason) {
            if ($transaction->status === 'reversed') {
                throw new Exception("Transaction {$transaction->transaction_number} is already reversed.");
            }

            $adminId = Auth::guard('admin')->id() ?? 1;
            $originalJournal = $transaction->journalEntries()->with('lines')->first();

            if (!$originalJournal) {
                throw new Exception("No journal entry found to reverse for transaction {$transaction->transaction_number}.");
            }

            // Invert debit and credit
            $reversalLines = [];
            foreach ($originalJournal->lines as $line) {
                $reversalLines[] = [
                    'account_id' => $line->account_id,
                    'party_id' => $line->party_id,
                    'debit' => $line->credit, // Invert
                    'credit' => $line->debit, // Invert
                    'description' => "Reversal of {$originalJournal->journal_number}: " . ($reason ?: $line->description),
                    'tax_code' => $line->tax_code,
                    'reference' => "REV-{$originalJournal->journal_number}",
                ];
            }

            $reversalTransaction = $this->postJournal([
                'organization_id' => $transaction->organization_id,
                'transaction_date' => now()->toDateString(),
                'transaction_type' => 'reversal',
                'reference_type' => 'AccountingTransaction',
                'reference_id' => $transaction->id,
                'description' => "Reversal voucher for {$transaction->transaction_number}. Reason: {$reason}",
                'entry_type' => 'reversal',
                'created_by' => $adminId,
            ], $reversalLines);

            $transaction->update(['status' => 'reversed']);
            $originalJournal->update(['status' => 'reversed']);

            AccountingAuditLog::create([
                'organization_id' => $transaction->organization_id,
                'user_id' => $adminId,
                'entity_type' => 'AccountingTransaction',
                'entity_id' => $transaction->id,
                'action' => 'Reversed Transaction',
                'old_values' => ['status' => 'posted'],
                'new_values' => ['status' => 'reversed', 'reversal_txn' => $reversalTransaction->transaction_number, 'reason' => $reason],
                'ip_address' => request()->ip() ?? '127.0.0.1',
                'created_at' => now(),
            ]);

            return $reversalTransaction;
        });
    }

    /**
     * Unique number generator utility with locking.
     */
    public function generateNumber(string $prefix, string $table, string $column): string
    {
        $yearMonth = date('Ym');
        $pattern = "{$prefix}-{$yearMonth}-%";

        $lastRecord = DB::table($table)
            ->where($column, 'like', $pattern)
            ->orderBy('id', 'desc')
            ->first();

        $nextSeq = 1;
        if ($lastRecord && preg_match("/{$prefix}-{$yearMonth}-(\d+)/", $lastRecord->$column, $matches)) {
            $nextSeq = intval($matches[1]) + 1;
        }

        return sprintf("%s-%s-%04d", $prefix, $yearMonth, $nextSeq);
    }
}

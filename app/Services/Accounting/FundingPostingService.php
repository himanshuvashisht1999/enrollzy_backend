<?php

namespace App\Services\Accounting;

use App\Models\Accounting\BankAccount;
use App\Models\Accounting\ChartOfAccount;
use App\Models\Accounting\FundingRecord;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Exception;

class FundingPostingService
{
    protected AccountingEngineService $engine;

    public function __construct(AccountingEngineService $engine)
    {
        $this->engine = $engine;
    }

    /**
     * Post Founder Capital or Investor Funding to the General Ledger.
     */
    public function postFunding(FundingRecord $funding): FundingRecord
    {
        return DB::transaction(function () use ($funding) {
            if ($funding->status === 'posted') {
                throw new Exception("Funding record {$funding->funding_number} is already posted.");
            }

            $adminId = Auth::guard('admin')->id() ?? $funding->created_by ?? 1;

            $bankAccount = BankAccount::findOrFail($funding->deposit_bank_account_id);
            $equityAccount = ChartOfAccount::findOrFail($funding->equity_ledger_account_id);

            $lines = [];

            // 1. DEBIT: Bank Account (Cash infusion into company bank)
            $lines[] = [
                'account_id' => $bankAccount->ledger_account_id,
                'party_id' => $funding->party_id,
                'debit' => (float) $funding->amount,
                'credit' => 0.00,
                'description' => "Capital/Funding received: {$funding->funding_type} from {$funding->party->name}",
                'reference' => $funding->reference_number ?? $funding->funding_number,
            ];

            // 2. CREDIT: Equity or Debt Account (Credit increases Equity / Liabilities)
            $lines[] = [
                'account_id' => $equityAccount->id,
                'party_id' => $funding->party_id,
                'debit' => 0.00,
                'credit' => (float) $funding->amount,
                'description' => "Capital/Funding credit to {$equityAccount->name} for {$funding->party->name}",
                'reference' => $funding->reference_number ?? $funding->funding_number,
            ];

            $transactionType = in_array($funding->funding_type, ['founder_capital', 'founder_current'])
                ? 'founder_investment'
                : 'third_party_investment';

            $transaction = $this->engine->postJournal([
                'organization_id' => $funding->organization_id,
                'transaction_date' => $funding->received_date->toDateString(),
                'transaction_type' => $transactionType,
                'reference_type' => 'FundingRecord',
                'reference_id' => $funding->id,
                'description' => "Capital Funding: {$funding->funding_type} of ₹" . number_format($funding->amount, 2) . " from {$funding->party->name}",
                'entry_type' => 'auto',
                'created_by' => $adminId,
            ], $lines);

            $funding->update([
                'status' => 'posted',
                'posted_by' => $adminId,
                'posted_at' => now(),
                'transaction_id' => $transaction->id,
            ]);

            return $funding;
        });
    }
}

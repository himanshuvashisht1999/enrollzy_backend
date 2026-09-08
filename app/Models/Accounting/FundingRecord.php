<?php

namespace App\Models\Accounting;

use App\Models\Admin;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class FundingRecord extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'funding_records';

    protected $fillable = [
        'organization_id',
        'funding_number',
        'party_id',
        'funding_type',
        'amount',
        'equity_percentage',
        'valuation',
        'shares_issued',
        'share_price',
        'deposit_bank_account_id',
        'equity_ledger_account_id',
        'received_date',
        'reference_number',
        'terms_document',
        'status',
        'notes',
        'created_by',
        'posted_by',
        'posted_at',
        'transaction_id',
    ];

    protected $casts = [
        'received_date' => 'date',
        'amount' => 'decimal:2',
        'equity_percentage' => 'decimal:2',
        'valuation' => 'decimal:2',
        'shares_issued' => 'decimal:2',
        'share_price' => 'decimal:2',
        'posted_at' => 'datetime',
    ];

    public function party()
    {
        return $this->belongsTo(Party::class, 'party_id');
    }

    public function depositBankAccount()
    {
        return $this->belongsTo(BankAccount::class, 'deposit_bank_account_id');
    }

    public function equityLedgerAccount()
    {
        return $this->belongsTo(ChartOfAccount::class, 'equity_ledger_account_id');
    }

    public function transaction()
    {
        return $this->belongsTo(AccountingTransaction::class, 'transaction_id');
    }

    public function creator()
    {
        return $this->belongsTo(Admin::class, 'created_by');
    }

    public function poster()
    {
        return $this->belongsTo(Admin::class, 'posted_by');
    }
}

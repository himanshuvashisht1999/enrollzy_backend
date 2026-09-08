<?php

namespace App\Models\Accounting;

use App\Models\Admin;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BankTransaction extends Model
{
    use HasFactory;

    protected $table = 'bank_transactions';

    protected $fillable = [
        'organization_id',
        'bank_account_id',
        'transaction_date',
        'type',
        'amount',
        'reference_number',
        'payee_payer',
        'description',
        'reconciliation_status',
        'reconciled_at',
        'reconciled_by',
        'journal_entry_line_id',
        'transaction_id',
    ];

    protected $casts = [
        'transaction_date' => 'date',
        'amount' => 'decimal:2',
        'reconciled_at' => 'datetime',
    ];

    public function bankAccount()
    {
        return $this->belongsTo(BankAccount::class, 'bank_account_id');
    }

    public function reconciler()
    {
        return $this->belongsTo(Admin::class, 'reconciled_by');
    }

    public function journalLine()
    {
        return $this->belongsTo(JournalEntryLine::class, 'journal_entry_line_id');
    }

    public function transaction()
    {
        return $this->belongsTo(AccountingTransaction::class, 'transaction_id');
    }
}

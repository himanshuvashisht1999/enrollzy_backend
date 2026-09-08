<?php

namespace App\Models\Accounting;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class BankAccount extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'bank_accounts';

    protected $fillable = [
        'organization_id',
        'account_name',
        'bank_name',
        'account_number_masked',
        'ifsc',
        'branch',
        'opening_balance',
        'current_balance',
        'ledger_account_id',
        'is_active',
    ];

    protected $casts = [
        'opening_balance' => 'decimal:2',
        'current_balance' => 'decimal:2',
        'is_active' => 'boolean',
    ];

    public function ledgerAccount()
    {
        return $this->belongsTo(ChartOfAccount::class, 'ledger_account_id');
    }

    public function transactions()
    {
        return $this->hasMany(BankTransaction::class, 'bank_account_id');
    }
}

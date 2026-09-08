<?php

namespace App\Models\Accounting;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class ChartOfAccount extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'chart_of_accounts';

    protected $fillable = [
        'organization_id',
        'account_code',
        'name',
        'account_type',
        'parent_id',
        'description',
        'current_balance',
        'is_system_account',
        'is_active',
    ];

    protected $casts = [
        'current_balance' => 'decimal:2',
        'is_system_account' => 'boolean',
        'is_active' => 'boolean',
    ];

    public function parent()
    {
        return $this->belongsTo(ChartOfAccount::class, 'parent_id');
    }

    public function children()
    {
        return $this->hasMany(ChartOfAccount::class, 'parent_id')->with('children');
    }

    public function journalLines()
    {
        return $this->hasMany(JournalEntryLine::class, 'account_id');
    }

    public function getFullDisplayNameAttribute()
    {
        return "{$this->account_code} - {$this->name}";
    }

    public function getNormalBalanceSideAttribute()
    {
        // Assets & Expenses normally have Debit balance; Liabilities, Equity & Revenue normally Credit
        return in_array($this->account_type, ['asset', 'expense']) ? 'debit' : 'credit';
    }
}

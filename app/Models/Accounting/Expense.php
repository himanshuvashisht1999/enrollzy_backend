<?php

namespace App\Models\Accounting;

use App\Models\Admin;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Expense extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'expenses';

    protected $fillable = [
        'organization_id',
        'expense_number',
        'expense_date',
        'title',
        'category_account_id',
        'paid_through_account_id',
        'party_id',
        'subtotal',
        'tax_amount',
        'total_amount',
        'payment_method',
        'payment_reference',
        'receipt_attachment',
        'status',
        'submitted_by',
        'submitted_at',
        'approved_by',
        'approved_at',
        'rejection_reason',
        'posted_by',
        'posted_at',
        'transaction_id',
        'notes',
    ];

    protected $casts = [
        'expense_date' => 'date',
        'subtotal' => 'decimal:2',
        'tax_amount' => 'decimal:2',
        'total_amount' => 'decimal:2',
        'submitted_at' => 'datetime',
        'approved_at' => 'datetime',
        'posted_at' => 'datetime',
    ];

    public function categoryAccount()
    {
        return $this->belongsTo(ChartOfAccount::class, 'category_account_id');
    }

    public function paidThroughAccount()
    {
        return $this->belongsTo(BankAccount::class, 'paid_through_account_id');
    }

    public function party()
    {
        return $this->belongsTo(Party::class, 'party_id');
    }

    public function items()
    {
        return $this->hasMany(ExpenseItem::class, 'expense_id');
    }

    public function submitter()
    {
        return $this->belongsTo(Admin::class, 'submitted_by');
    }

    public function approver()
    {
        return $this->belongsTo(Admin::class, 'approved_by');
    }

    public function poster()
    {
        return $this->belongsTo(Admin::class, 'posted_by');
    }

    public function transaction()
    {
        return $this->belongsTo(AccountingTransaction::class, 'transaction_id');
    }
}

<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Money paid out to an expert for completed, paid sessions (admin → Expert Payouts; the expert sees their own).
 * Not to be confused with Payout, which is staff payroll (employee_payout).
 */
class ExpertPayout extends Model
{
    protected $table = 'payouts';

    protected $fillable = ['expert_id', 'amount', 'status', 'reference_id', 'payout_method', 'period_start', 'period_end', 'paid_at', 'notes'];

    protected $casts = [
        'amount' => 'decimal:2',
        'period_start' => 'date',
        'period_end' => 'date',
        'paid_at' => 'datetime',
    ];

    public const STATUSES = ['pending' => 'Pending', 'processing' => 'Processing', 'paid' => 'Paid'];
    public const METHODS = ['bank_transfer' => 'Bank transfer', 'upi' => 'UPI', 'cheque' => 'Cheque', 'cash' => 'Cash'];

    public function expert()
    {
        return $this->belongsTo(Expert::class);
    }

    /**
     * One expert's money position: what completed, paid sessions earned them (net of platform fee, GST and TDS),
     * what has been paid out, what is promised in pending payouts, and what is still due.
     */
    public static function summaryFor(Expert $expert): array
    {
        $earned = Booking::earned()->where('expert_id', $expert->id);
        $gross = (float) (clone $earned)->sum('amount');
        $net = (float) (clone $earned)->sum('expert_earning');
        $paid = (float) static::where('expert_id', $expert->id)->where('status', 'paid')->sum('amount');
        $inProgress = (float) static::where('expert_id', $expert->id)->whereIn('status', ['pending', 'processing'])->sum('amount');
        return [
            'sessions' => (clone $earned)->count(),
            'gross' => round($gross, 2),
            'net_earned' => round($net, 2),
            'paid_out' => round($paid, 2),
            'in_progress' => round($inProgress, 2),
            'due' => round(max(0, $net - $paid - $inProgress), 2),
        ];
    }
}

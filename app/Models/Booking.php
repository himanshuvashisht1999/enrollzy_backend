<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class Booking extends Model
{
    use SoftDeletes;
    protected $fillable = [
        'booking_id',
        'user_id',
        'expert_id',
        'slot_id',
        'booking_date',
        'status',
        'amount',
        'platform_fee',
        'expert_earning',
        'payment_status',
        'meeting_link',
        'notes',
        // Commission Snapshot
        'commission_override_type',
        'commission_override_value',
        'override_reason',
        'override_by',
        'applied_commission_type',
        'applied_commission_rate',
        'applied_gst_rate',
        'applied_tds_rate',
        'commission_breakdown',
        // last reschedule (the student on the website, the expert or the admin)
        'rescheduled_at',
        'rescheduled_from_slot_id',
        'rescheduled_by',
        'reschedule_count',
        // the website's booking page; the expert's summary; a student's cancellation request
        'topic', 'attendee', 'class_level', 'phone', 'email', 'session_summary', 'cancel_requested_at', 'cancel_reason',
    ];

    protected $casts = [
        'booking_date' => 'datetime',
        'amount' => 'decimal:2',
        'platform_fee' => 'decimal:2',
        'expert_earning' => 'decimal:2',
        'commission_breakdown' => 'array',
        'rescheduled_at' => 'datetime',
        'cancel_requested_at' => 'datetime',
    ];

    /** Booking statuses, as stored (lower case). */
    public const STATUSES = ['pending' => 'Pending', 'confirmed' => 'Confirmed', 'completed' => 'Completed', 'cancelled' => 'Cancelled'];
    /** Payment statuses: refund_due = cancelled after the student paid. */
    public const PAYMENT_STATUSES = ['pending' => 'Pending', 'paid' => 'Paid', 'refund_due' => 'Refund due', 'refunded' => 'Refunded'];

    public static function boot()
    {
        parent::boot();
        static::creating(function ($model) {
            $model->booking_id = 'BK-' . strtoupper(Str::random(10));
        });
    }

    /** When the session starts and ends (slot date + times), or null without a slot. */
    public function sessionStart(): ?\Carbon\Carbon
    {
        return $this->slot ? \Carbon\Carbon::parse($this->slot->date->format('Y-m-d') . ' ' . $this->slot->start_time) : null;
    }

    public function sessionEnd(): ?\Carbon\Carbon
    {
        return $this->slot ? \Carbon\Carbon::parse($this->slot->date->format('Y-m-d') . ' ' . $this->slot->end_time) : null;
    }

    /**
     * Platform fee (+ GST), TDS and the expert's net earning from the commission rules (CommissionService:
     * override > expert > category > global), stored on the booking so later rule changes do not alter it.
     */
    public function applyCommission(bool $save = true): self
    {
        if (!$this->expert) {
            return $this;
        }
        $b = app(\App\Services\CommissionService::class)->calculateStrict($this->expert, (float) $this->amount, $this);
        $this->fill([
            'platform_fee' => $b['platform_total_deduction'],
            'expert_earning' => $b['net_expert_earning'],
            'applied_commission_type' => $b['commission_type'],
            'applied_commission_rate' => $b['applied_rate'],
            'applied_gst_rate' => $b['applied_gst_rate'],
            'applied_tds_rate' => $b['applied_tds_rate'],
            'commission_breakdown' => $b,
        ]);
        if ($save) {
            $this->saveQuietly();
        }
        return $this;
    }

    /** Bookings made on the website are saved without a breakdown: work it out for them (optionally one expert's). */
    public static function fillMissingCommission(?int $expertId = null): int
    {
        $n = 0;
        static::with(['expert', 'slot'])->whereNull('commission_breakdown')
            ->when($expertId, fn ($q) => $q->where('expert_id', $expertId))
            ->get()->each(function (Booking $b) use (&$n) { $b->applyCommission(); $n++; });
        return $n;
    }

    /** Pending or confirmed, and not started yet: the time can still be moved. */
    public function canReschedule(): bool
    {
        return in_array($this->status, ['pending', 'confirmed'], true)
            && (!$this->sessionStart() || $this->sessionStart()->isFuture());
    }

    /**
     * Open slots of the same expert with the same fee, to move this booking to. Pass the open slots
     * already loaded for a list (ExpertSlot::open()) to avoid a query per booking.
     */
    public function rescheduleOptions(?\Illuminate\Support\Collection $open = null)
    {
        $open ??= ExpertSlot::open()->where('expert_id', $this->expert_id)->orderBy('date')->orderBy('start_time')->get();
        return $open->filter(fn ($s) => (int) $s->expert_id === (int) $this->expert_id
            && (int) $s->id !== (int) $this->slot_id
            && round((float) $s->cost, 2) === round((float) $this->amount, 2))->values();
    }

    /**
     * Move the booking to another slot of the same expert with the same fee ($by: expert or admin).
     * It keeps its id, payment and commission; the old slot opens again. Returns null, or why not.
     */
    public function moveToSlot(int $slotId, string $by): ?string
    {
        return DB::transaction(function () use ($slotId, $by) {
            $booking = static::lockForUpdate()->find($this->id);
            if (!$booking || !$booking->canReschedule()) {
                return 'Only an upcoming pending or confirmed booking can be rescheduled.';
            }
            if ((int) $booking->slot_id === $slotId) {
                return 'The booking is already at that time.';
            }

            $new = ExpertSlot::lockForUpdate()->find($slotId);
            $start = $new ? \Carbon\Carbon::parse($new->date->format('Y-m-d') . ' ' . $new->start_time) : null;
            $taken = static::where('slot_id', $slotId)->whereIn('status', ['pending', 'confirmed'])->exists();
            if (!$new || (int) $new->expert_id !== (int) $booking->expert_id || $new->status !== 'available' || $taken || $start->isPast()) {
                return 'That slot is not open (it is booked, closed, past or another expert\'s).';
            }
            if (round((float) $new->cost, 2) !== round((float) $booking->amount, 2)) {
                return 'That slot has a different fee (Rs ' . number_format((float) $new->cost, 2) . '). Pick one with the booking fee of Rs ' . number_format((float) $booking->amount, 2) . '.';
            }

            $old = $booking->slot;   // with trashed
            if ($old && !$old->trashed() && $old->status === 'booked') {
                $old->update(['status' => 'available']);
            }
            $new->update(['status' => 'booked']);
            $booking->update([
                'slot_id' => $new->id,
                'rescheduled_at' => now(),
                'rescheduled_from_slot_id' => $booking->slot_id,
                'rescheduled_by' => $by,
                'reschedule_count' => (int) $booking->reschedule_count + 1,
            ]);
            $this->setRawAttributes($booking->fresh()->getAttributes(), true);
            $this->unsetRelation('slot');
            return null;
        });
    }

    public function scopeEarned($q)
    {
        // what an expert has earned: sessions held and paid for
        return $q->where('status', 'completed')->where('payment_status', 'paid');
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function expert()
    {
        return $this->belongsTo(Expert::class);
    }

    public function slot()
    {
        // a deleted slot still tells when the booked session was
        return $this->belongsTo(ExpertSlot::class, 'slot_id')->withTrashed();
    }

    public function payments()
    {
        return $this->hasMany(Payment::class);
    }

    public function review()
    {
        return $this->hasOne(Review::class);
    }
}

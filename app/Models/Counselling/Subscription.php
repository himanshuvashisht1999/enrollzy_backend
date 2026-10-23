<?php

namespace App\Models\Counselling;

use Illuminate\Database\Eloquent\Model;

class Subscription extends Model
{
    protected $table = 'cns_subscriptions';
    protected $guarded = ['id'];
    protected $casts = ['confirmed_at' => 'datetime', 'via_email' => 'boolean', 'via_whatsapp' => 'boolean', 'via_inapp' => 'boolean', 'alert_types' => 'array'];

    /** Kinds a student can choose between (null = all). */
    public const KINDS = ['opens', 'closing', 'declared', 'date_changed', 'postponed', 'seats_revised'];

    public function subscriber() { return $this->belongsTo(Subscriber::class, 'subscriber_id'); }

    public function isConfirmed(): bool
    {
        return $this->confirmed_at !== null;
    }

    public function wants(string $kind): bool
    {
        return $this->alert_types === null || in_array($kind, $this->alert_types, true);
    }

    /** Name + page of what is followed, only while it is still public. */
    public function target(): ?array
    {
        if ($this->target_type === 'counselling') {
            $c = Counselling::where('id', $this->target_id)->where('visibility', 'public')->where('is_active', true)->first(['id', 'name', 'slug']);
            return $c ? ['type' => 'counselling', 'name' => $c->name, 'slug' => $c->slug, 'url' => config('cns.frontend_url') . '/counselling/' . $c->slug] : null;
        }
        $e = \Illuminate\Support\Facades\DB::table('exm_exams')->where('id', $this->target_id)->whereNull('deleted_at')->where('visibility', 'public')->first(['name', 'slug']);
        return $e ? ['type' => 'exam', 'name' => $e->name, 'slug' => $e->slug, 'url' => config('cns.frontend_url') . '/exams/' . $e->slug] : null;
    }
}

<?php

namespace App\Models\Counselling;

use Illuminate\Database\Eloquent\Model;

/** A student identified only by a confirmed email and/or phone: there are no student accounts. */
class Subscriber extends Model
{
    protected $table = 'cns_subscribers';
    protected $guarded = ['id'];
    protected $attributes = ['token_version' => 1, 'phone_code_attempts' => 0];
    protected $casts = ['email_confirmed_at' => 'datetime', 'phone_confirmed_at' => 'datetime', 'whatsapp_opt_in_at' => 'datetime',
        'phone_code_expires_at' => 'datetime', 'phone_code_sent_at' => 'datetime'];
    protected $hidden = ['phone_code_hash'];

    public function subscriptions() { return $this->hasMany(Subscription::class, 'subscriber_id'); }
    public function deliveries() { return $this->hasMany(AlertDelivery::class, 'subscriber_id'); }

    /** +91 98765 43210, 98765-43210, 919876543210 -> +919876543210. Null if it cannot be a phone number. */
    public static function normalizePhone(?string $raw): ?string
    {
        $raw = trim((string) $raw);
        if ($raw === '') return null;
        $digits = preg_replace('/\D+/', '', $raw);
        if (str_starts_with($raw, '+')) {
            return strlen($digits) >= 10 && strlen($digits) <= 15 ? '+' . $digits : null;
        }
        if (strlen($digits) === 10) return '+91' . $digits;                       // Indian local number
        if (strlen($digits) === 12 && str_starts_with($digits, '91')) return '+' . $digits;
        return null;
    }

    public static function normalizeEmail(?string $raw): ?string
    {
        $e = strtolower(trim((string) $raw));
        return $e !== '' ? $e : null;
    }

    public function canEmail(): bool { return $this->email && $this->email_confirmed_at; }
    public function canWhatsApp(): bool { return $this->phone && $this->phone_confirmed_at && $this->whatsapp_opt_in_at; }
    public function canInApp(): bool { return (bool) ($this->email_confirmed_at || $this->phone_confirmed_at); }

    /** Long-lived link token for the inbox / manage screens: an HMAC, so nothing is stored. Revoke by bumping token_version. */
    public function token(): string
    {
        return $this->id . '.' . hash_hmac('sha256', 'cns-inbox|' . $this->id . '|' . $this->token_version, config('app.key'));
    }

    public static function fromToken(?string $token): ?self
    {
        if (!$token || !str_contains($token, '.')) return null;
        [$id, $sig] = explode('.', $token, 2);
        $s = ctype_digit($id) ? static::find((int) $id) : null;
        return $s && hash_equals($s->token(), $id . '.' . $sig) && $s->canInApp() ? $s : null;
    }
}

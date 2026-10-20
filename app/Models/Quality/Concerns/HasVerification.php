<?php

namespace App\Models\Quality\Concerns;

/**
 * Same rule as exam dates: a record is only "verified" if someone confirmed it AFTER its last change.
 * Editing any substantive field of a verified record sends it back to unverified.
 */
trait HasVerification
{
    /** Fields whose change invalidates a verification. Notes and the verification fields themselves do not. */
    abstract protected function substantiveFields(): array;

    public static function bootHasVerification(): void
    {
        static::updating(function ($m) {
            if ($m->verification_status === 'verified' && !$m->isDirty('verification_status') && $m->isDirty($m->substantiveFields())) {
                $m->verification_status = 'unverified';
                $m->verified_by = null;
                $m->verified_at = null;
            }
        });
    }

    public function isVerified(): bool
    {
        return $this->verification_status === 'verified';
    }

    public function verify(?int $userId): void
    {
        $this->forceFill(['verification_status' => 'verified', 'verified_by' => $userId, 'verified_at' => now()])->save();
    }

    public function reject(?int $userId): void
    {
        $this->forceFill(['verification_status' => 'rejected', 'verified_by' => $userId, 'verified_at' => now()])->save();
    }

    public function scopeVerified($q)
    {
        return $q->where('verification_status', 'verified');
    }

    public function scopeNeedsReview($q)
    {
        return $q->where('verification_status', 'unverified');
    }

    public function scopeForEntity($q, string $type, $id)
    {
        return $q->where('entity_type', $type)->where('entity_id', (string) $id);
    }
}

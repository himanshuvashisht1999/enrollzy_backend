<?php

namespace App\Models\ExamModule\Concerns;

use App\Models\ExamModule\AuditLog;

trait Auditable
{
    public static function bootAuditable(): void
    {
        static::created(fn ($m) => $m->writeAudit('created', null, $m->getAttributes()));

        static::updated(function ($m) {
            $changes = collect($m->getChanges())->except(['updated_at']);
            if ($changes->isEmpty()) {
                return;
            }
            $old = $changes->mapWithKeys(fn ($v, $k) => [$k => $m->getRawOriginal($k)])->all();
            $m->writeAudit('updated', $old, $changes->all());
        });

        static::deleted(fn ($m) => $m->writeAudit('deleted', $m->getAttributes(), null));
    }

    protected function writeAudit(string $action, ?array $old, ?array $new): void
    {
        AuditLog::create([
            'auditable_type' => static::class,
            'auditable_id'   => $this->getKey(),
            'user_id'        => auth('admin')->id(),
            'action'         => $action,
            'old_values'     => $old,
            'new_values'     => $new,
        ]);
    }
}

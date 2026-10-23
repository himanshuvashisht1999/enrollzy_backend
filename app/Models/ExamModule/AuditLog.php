<?php

namespace App\Models\ExamModule;

use Illuminate\Database\Eloquent\Model;

class AuditLog extends Model
{
    protected $table = 'exm_audit_logs';
    public $timestamps = false;

    protected $fillable = ['auditable_type', 'auditable_id', 'user_id', 'action', 'old_values', 'new_values'];

    protected $casts = ['old_values' => 'array', 'new_values' => 'array', 'created_at' => 'datetime'];

    public function user()
    {
        return $this->belongsTo(\App\Models\Admin::class, 'user_id');
    }

    /** Values for display. Dates are UTC; older rows logged ISO "T...Z", normalised to match new rows. */
    public function displayValues(?array $values): array
    {
        return collect($values ?? [])->map(function ($v) {
            if (is_string($v) && preg_match('/^\d{4}-\d{2}-\d{2}T\d{2}:\d{2}:\d{2}(\.\d+)?Z$/', $v)) {
                return \Carbon\Carbon::parse($v)->utc()->format('Y-m-d H:i:s');
            }
            return $v;
        })->all();
    }

    public function getSubjectAttribute(): string
    {
        return class_basename($this->auditable_type) . ' #' . $this->auditable_id;
    }
}

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

    public function getSubjectAttribute(): string
    {
        return class_basename($this->auditable_type) . ' #' . $this->auditable_id;
    }
}

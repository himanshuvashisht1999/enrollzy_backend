<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

/** A Notice Board announcement for the calling team (see App\Support\Calling\Notices). */
class Notice extends Model
{
    use SoftDeletes;

    protected $guarded = ['id'];
    protected $casts = ['pinned' => 'boolean', 'require_ack' => 'boolean', 'expires_on' => 'date'];

    public const PRIORITIES = ['normal' => 'Normal', 'important' => 'Important', 'urgent' => 'Urgent'];

    public function poster()
    {
        return $this->belongsTo(Admin::class, 'posted_by');
    }

    public function acks()
    {
        return $this->hasMany(NoticeAck::class);
    }

    public function isLive(): bool
    {
        return !$this->expires_on || $this->expires_on->gte(today());
    }
}

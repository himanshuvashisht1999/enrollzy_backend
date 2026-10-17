<?php

namespace App\Models\ExamModule;

use Illuminate\Database\Eloquent\Model;

class EventType extends Model
{
    protected $table = 'exm_event_types';
    protected $fillable = ['name', 'slug', 'status_source', 'sort_order', 'status'];
    protected $casts = ['status' => 'boolean'];

    public function events()
    {
        return $this->hasMany(CycleEvent::class, 'event_type_id');
    }

    public function isManual(): bool
    {
        return $this->status_source === 'manual';
    }
}

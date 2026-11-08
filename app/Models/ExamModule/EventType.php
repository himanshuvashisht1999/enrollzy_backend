<?php

namespace App\Models\ExamModule;

use Illuminate\Database\Eloquent\Model;

class EventType extends Model
{
    protected $table = 'exm_event_types';
    protected $fillable = ['name', 'slug', 'status_source', 'applies_to', 'date_group', 'sort_order', 'status'];
    protected $casts = ['status' => 'boolean'];

    /** The heading an event of this type shows under in the website's "Important dates". */
    public const GROUPS = ['application' => 'Application', 'exam' => 'Exam', 'result' => 'Result', 'counselling' => 'Counselling', 'other' => 'Other dates'];

    public function events()
    {
        return $this->hasMany(CycleEvent::class, 'event_type_id');
    }

    public function isManual(): bool
    {
        return $this->status_source === 'manual';
    }
}

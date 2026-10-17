<?php

namespace App\Models\ExamModule;

use App\Models\ExamModule\Concerns\Auditable;
use Illuminate\Database\Eloquent\Model;

class Stage extends Model
{
    use Auditable;

    protected $table = 'exm_stages';
    protected $fillable = ['cycle_id', 'name', 'sort_order', 'is_qualifying'];
    protected $casts = ['is_qualifying' => 'boolean'];

    public function cycle()
    {
        return $this->belongsTo(Cycle::class, 'cycle_id');
    }

    public function events()
    {
        return $this->hasMany(CycleEvent::class, 'stage_id');
    }
}

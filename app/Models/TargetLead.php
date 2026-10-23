<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TargetLead extends Model
{
    protected $fillable = [
        'staff_id',
        'year',
        'month',
        'month_target_calling',
        'month_target_admissions',
    ];

    public function staff()
    {
        return $this->belongsTo(Admin::class, 'staff_id');
    }
}

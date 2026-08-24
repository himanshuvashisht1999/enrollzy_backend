<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class CallingStatus extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'calling_status';

    protected $fillable = [
        'name',
        'status',
        'organization_id',
        'date_require',
        'comment_require',
        'is_more_details',
        'calling_action_id'
    ];

    public function callingAction()
    {
        return $this->belongsTo(CallingAction::class, 'calling_action_id');
    }
}

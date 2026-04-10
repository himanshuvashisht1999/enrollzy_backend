<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CallingHistoryLog extends Model
{
    use HasFactory;

    protected $fillable = [
        'history_id',
        'log_type',
        'calling_action_id',
        'updated_by',
        'status',
    ];
    public function calling_action(){
        return $this->hasOne('App\Models\CallingAction','id','calling_action_id');
    }
    public function user(){
        return $this->hasOne('App\Models\Admin','id','updated_by');
    }
}

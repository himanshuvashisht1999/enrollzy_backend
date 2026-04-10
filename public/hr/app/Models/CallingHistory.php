<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CallingHistory extends Model
{
    use HasFactory;

    protected $fillable = [
       'user_type',
       'user_id',
        'category_id',
        'institute_id',
        'user_name',
        'user_phone',
        'reason',
        'date_required',
        'calling_action_id',
        'comment',
        'updated_by',
        'is_done',
        'image',
        'status',
    ];

    public function calling_action(){
        return $this->hasOne('App\Models\CallingAction','id','calling_action_id');
    }
    public function calling_status(){
        return $this->hasOne('App\Models\CallingStatus','id','reason');
    }

    public function user(){
        return $this->hasOne('App\Models\Users','id','user_id');
    }
}

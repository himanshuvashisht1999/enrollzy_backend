<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class NoticeAck extends Model
{
    public $timestamps = false;
    protected $guarded = ['id'];
    protected $casts = ['acked_at' => 'datetime'];
}

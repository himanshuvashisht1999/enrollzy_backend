<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CommunityLike extends Model
{
    protected $fillable = ['user_id', 'likable_id', 'likable_type'];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function likable()
    {
        return $this->morphTo();
    }
}

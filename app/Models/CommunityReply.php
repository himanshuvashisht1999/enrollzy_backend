<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CommunityReply extends Model
{
    protected $fillable = ['user_id', 'question_id', 'parent_id', 'content'];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function question()
    {
        return $this->belongsTo(CommunityQuestion::class, 'question_id');
    }

    public function parent()
    {
        return $this->belongsTo(CommunityReply::class, 'parent_id');
    }

    public function replies()
    {
        return $this->hasMany(CommunityReply::class, 'parent_id');
    }

    public function likes()
    {
        return $this->morphMany(CommunityLike::class, 'likable');
    }
}

<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CommunityQuestion extends Model
{
    protected $fillable = ['user_id', 'category_id', 'question_text', 'image', 'is_verified', 'views'];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function category()
    {
        return $this->belongsTo(CommunityCategory::class, 'category_id');
    }

    public function replies()
    {
        return $this->hasMany(CommunityReply::class, 'question_id');
    }

    public function likes()
    {
        return $this->morphMany(CommunityLike::class, 'likable');
    }
}

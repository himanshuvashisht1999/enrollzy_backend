<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CustomerAcademicDetail extends Model
{
    protected $fillable = [
        'user_id', 'examination', 'board_university', 'school_college', 'year', 'percentage'
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}

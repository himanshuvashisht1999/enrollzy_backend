<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ExamStage extends Model
{
    protected $fillable = ['title', 'status', 'sort_order'];

    protected $casts = [
        'status' => 'boolean',
    ];
}


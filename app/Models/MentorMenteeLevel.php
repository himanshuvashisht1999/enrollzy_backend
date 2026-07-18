<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MentorMenteeLevel extends Model
{
    use HasFactory;

    protected $fillable = ['name', 'status', 'commission_percentage'];
}


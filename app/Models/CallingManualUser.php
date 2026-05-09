<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class CallingManualUser extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'calling_manual_users';

    protected $fillable = [
        'name',
        'email',
        'phone',
        'city',
        'status',
        'organization_id'
    ];
}

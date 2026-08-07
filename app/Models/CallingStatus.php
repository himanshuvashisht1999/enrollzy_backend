<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class CallingStatus extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'calling_status';

    protected $fillable = [
        'name',
        'status',
        'organization_id',
        'date_require'
    ];
}

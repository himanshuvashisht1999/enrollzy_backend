<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CustomerField extends Model
{
    use HasFactory;

    protected $fillable = [
        'id',
        'user_id',
        'organization_id',
        'name',
        'label',
        'is_required',
        'sequence',
        'status',
        'created_at',
        'updated_at'
    ];
}

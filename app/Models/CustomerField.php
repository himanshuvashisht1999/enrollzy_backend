<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class CustomerField extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'customer_fields';

    protected $fillable = [
        'name',
        'label',
        'is_required',
        'status',
        'sequence',
        'organization_id',
        'user_id'
    ];
}

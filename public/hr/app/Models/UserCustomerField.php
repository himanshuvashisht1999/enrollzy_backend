<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UserCustomerField extends Model
{
    use HasFactory;

    protected $fillable = [
        'id',
        'user_id',
        'customer_field_id',
        'value',
        'status',
        'created_at',
        'updated_at'
    ];
}

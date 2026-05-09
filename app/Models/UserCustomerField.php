<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UserCustomerField extends Model
{
    use HasFactory;

    protected $table = 'user_customer_fields';

    protected $fillable = [
        'user_id',
        'customer_field_id',
        'value'
    ];

    public function field()
    {
        return $this->belongsTo(CustomerField::class, 'customer_field_id');
    }
}

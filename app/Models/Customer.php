<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Notifications\Notifiable;

class Customer extends Model
{
    use HasFactory, Notifiable, SoftDeletes;

    protected $table = 'users';

    protected $fillable = [
        'name', 'email', 'mobile', 'password', 'image', 'phone', 'role', 
        'category_id', 'institute_id', 'status', 'organization_id', 
        'country', 'state', 'city', 'pincode', 'is_admin'
    ];

    protected $hidden = [
        'password', 'remember_token',
    ];

    protected static function booted()
    {
        static::addGlobalScope('customer', function ($query) {
            $query->where('role', 'user');
        });
    }

    public function category()
    {
        return $this->belongsTo(CustomerCategory::class, 'category_id');
    }

    public function institute()
    {
        return $this->belongsTo(Institute::class, 'institute_id');
    }

    public function custom_fields()
    {
        return $this->hasMany(UserCustomerField::class, 'user_id');
    }
}

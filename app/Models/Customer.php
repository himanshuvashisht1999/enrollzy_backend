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
        'country', 'state', 'city', 'pincode', 'is_admin',
        'dob', 'gender', 'aadhaar_number', 'alternate_mobile',
        'interested_in_ids', 'interested_in_course', 'program_level', 'mode', 'session_ids',
        'father_name', 'father_mobile', 'father_email', 'father_occupation',
        'mother_name', 'mother_mobile', 'mother_email', 'mother_occupation',
        'sibling_enrolled', 'sibling_name', 'sibling_age', 'referred_by', 'source',
        'registration_no', 'class_batch', 'counselor_name', 'registration_date', 'payment_status', 'remarks'
    ];

    protected $casts = [
        'interested_in_ids' => 'array',
        'session_ids' => 'array',
    ];

    public function academic_details()
    {
        return $this->hasMany(CustomerAcademicDetail::class, 'user_id');
    }

    public function documents()
    {
        return $this->hasMany(CustomerDocument::class, 'user_id');
    }

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

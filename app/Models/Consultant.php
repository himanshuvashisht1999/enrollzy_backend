<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

class Consultant extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'consultant_id', 'full_name', 'business_name', 'phone', 'alternate_mobile', 'email', 'password', 'image', 'gender', 'dob',
        'consultant_type', 'is_gst_registered', 'gst_number', 'pan_number', 'aadhaar_number', 'years_of_experience', 'team_size', 
        'office_address', 'state', 'city', 'pincode', 'website', 'linkedin_profile',
        'category_id', 'sub_category_id', 'sub_sub_category_id', 'expertise_level', 'preferred_universities', 'preferred_courses', 'preferred_modes_of_study',
        'generates_own_leads', 'requires_company_leads', 'runs_ads', 'has_counseling_office', 'walk_in_students', 'approx_leads_per_month',
        'working_states', 'working_cities', 'can_handle_pan_india', 'languages_known',
        'account_holder_name', 'bank_name', 'account_number', 'ifsc_code', 'upi_id', 'qr_code_upload', 'cancelled_cheque_upload', 'pan_card_upload',
        'aadhaar_upload', 'pan_upload', 'gst_certificate_upload', 'business_registration_upload', 'visiting_card_upload', 'msme_upload', 'mou_upload', 'office_photos',
        'status', 'access_level', 'lead_visibility', 'lead_assignment_allowed', 'status_reason', 'organization_id'
    ];

    protected $casts = [
        'is_gst_registered' => 'boolean',
        'preferred_universities' => 'array',
        'preferred_courses' => 'array',
        'preferred_modes_of_study' => 'array',
        'generates_own_leads' => 'boolean',
        'requires_company_leads' => 'boolean',
        'runs_ads' => 'boolean',
        'has_counseling_office' => 'boolean',
        'walk_in_students' => 'boolean',
        'working_states' => 'array',
        'working_cities' => 'array',
        'can_handle_pan_india' => 'boolean',
        'languages_known' => 'array',
        'office_photos' => 'array',
        'lead_assignment_allowed' => 'boolean',
    ];

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($consultant) {
            if (empty($consultant->consultant_id)) {
                $count = self::whereYear('created_at', date('Y'))->count() + 1;
                $consultant->consultant_id = 'CONS-' . date('Y') . '-' . str_pad($count, 3, '0', STR_PAD_LEFT);
            }
        });
    }

    public function category()
    {
        return $this->belongsTo(ConsultantCategory::class, 'category_id');
    }

    public function sub_category()
    {
        return $this->belongsTo(ConsultantCategory::class, 'sub_category_id');
    }

    public function sub_sub_category()
    {
        return $this->belongsTo(ConsultantCategory::class, 'sub_sub_category_id');
    }
}

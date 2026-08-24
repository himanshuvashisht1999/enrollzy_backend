<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class CallingHistory extends Model
{
    use HasFactory;

    protected $table = 'calling_histories';

    protected $fillable = [
        'user_type',
        'user_id',
        'category_id',
        'institute_id',
        'user_name',
        'user_phone',
        'reason', // Calling Status ID
        'date_required', // Next Call Date
        'calling_action_id',
        'comment', // Remark
        'updated_by', // Staff ID
        'is_done',
        'image',
        'status',
        'organization_id',
        'university_id',
        'university_text',
        'program_level_id',
        'program_level_text',
        'school_type_id',
        'school_type_text',
        'course_id',
        'course_text',
        'course_type',
        'meeting_date',
        'time_slot',
        'meeting_link',
        'assign_to_staff_id',
        'current_university_id',
        'current_university_text',
        'current_course_id',
        'current_course_text',
        'current_course_type',
        'current_session'
    ];

    public function assignedStaff()
    {
        return $this->belongsTo(Admin::class, 'assign_to_staff_id');
    }

    public function customer()
    {
        return $this->belongsTo(Customer::class, 'user_id');
    }

    public function calling_status()
    {
        return $this->belongsTo(CallingStatus::class, 'reason');
    }

    public function calling_action()
    {
        return $this->belongsTo(CallingAction::class, 'calling_action_id');
    }

    public function staff()
    {
        // On our system staff is likely an Admin user or linked to staff table
        return $this->belongsTo(User::class, 'updated_by');
    }

    public function logs()
    {
        return $this->hasMany(CallingHistoryLog::class, 'history_id', 'id')->orderBy('id', 'desc');
    }
}

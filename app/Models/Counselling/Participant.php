<?php

namespace App\Models\Counselling;

use App\Models\ExamModule\Concerns\Auditable;
use Illuminate\Database\Eloquent\Model;

/** A college + course + quota taking part in a counselling cycle ("admission through" on college pages). */
class Participant extends Model
{
    use Auditable;

    protected $table = 'cns_participants';
    protected $fillable = ['cycle_id', 'organisation_id', 'course_id', 'quota_id', 'institute_type', 'institute_code',
        'annual_tuition_fee', 'other_fees', 'bond_details', 'total_seats', 'import_batch_id'];

    public function cycle() { return $this->belongsTo(CounsellingCycle::class, 'cycle_id'); }
    public function organisation() { return $this->belongsTo(\App\Models\Organisation::class, 'organisation_id'); }
    public function course() { return $this->belongsTo(\App\Models\Course::class, 'course_id'); }
    public function quota() { return $this->belongsTo(Quota::class, 'quota_id'); }
}

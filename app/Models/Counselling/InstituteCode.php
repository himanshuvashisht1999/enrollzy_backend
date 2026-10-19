<?php

namespace App\Models\Counselling;

use App\Models\ExamModule\Concerns\Auditable;
use Illuminate\Database\Eloquent\Model;

/** A code a counselling authority uses for a college (MCC code, JoSAA code, state code...). */
class InstituteCode extends Model
{
    use Auditable;

    protected $table = 'cns_institute_codes';
    protected $fillable = ['organisation_id', 'conducting_body_id', 'code', 'valid_from_year'];

    public function organisation()
    {
        return $this->belongsTo(\App\Models\Organisation::class, 'organisation_id');
    }

    public function conductingBody()
    {
        return $this->belongsTo(\App\Models\ExamModule\ConductingBody::class, 'conducting_body_id');
    }
}

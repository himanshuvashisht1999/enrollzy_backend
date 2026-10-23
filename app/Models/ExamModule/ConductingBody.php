<?php

namespace App\Models\ExamModule;

use App\Models\ExamModule\Concerns\Auditable;
use Illuminate\Database\Eloquent\Model;

class ConductingBody extends Model
{
    use Auditable;

    protected $table = 'exm_conducting_bodies';
    protected $fillable = ['name', 'short_name', 'body_type', 'website', 'status'];
    protected $casts = ['status' => 'boolean'];

    public const TYPES = ['government' => 'Government', 'private' => 'Private', 'university' => 'University'];

    public function exams()
    {
        return $this->hasMany(Exam::class, 'conducting_body_id');
    }
}

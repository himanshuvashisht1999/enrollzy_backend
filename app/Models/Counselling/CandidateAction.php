<?php

namespace App\Models\Counselling;

use App\Models\ExamModule\Concerns\Auditable;
use Illuminate\Database\Eloquent\Model;

class CandidateAction extends Model
{
    use Auditable;

    protected $table = 'cns_candidate_actions';
    protected $fillable = ['name', 'description', 'sort_order', 'status'];
    protected $casts = ['status' => 'boolean'];
}

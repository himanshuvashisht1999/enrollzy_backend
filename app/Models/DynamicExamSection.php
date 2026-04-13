<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DynamicExamSection extends Model
{
    protected $fillable = ['dynamic_exam_id', 'heading', 'content', 'order', 'status'];

    protected $casts = [
        'content' => 'array',
    ];

    public function exam()
    {
        return $this->belongsTo(DynamicExam::class, 'dynamic_exam_id');
    }
}

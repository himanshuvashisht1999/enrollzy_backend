<?php

namespace App\Models\Counselling;

use App\Models\ExamModule\Concerns\Auditable;
use Illuminate\Database\Eloquent\Model;

class DocumentType extends Model
{
    use Auditable;

    protected $table = 'cns_document_types';
    protected $fillable = ['name', 'description', 'is_affidavit', 'status'];
    protected $casts = ['is_affidavit' => 'boolean', 'status' => 'boolean'];
}

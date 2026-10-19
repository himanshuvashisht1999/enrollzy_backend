<?php

namespace App\Models\ExamModule;

use App\Models\ExamModule\Concerns\Auditable;
use Illuminate\Database\Eloquent\Model;

class CustomValue extends Model
{
    use Auditable;

    protected $table = 'exm_custom_values';
    protected $fillable = ['field_id', 'cycle_id', 'value'];
    protected $casts = ['value' => 'array'];

    public function field()
    {
        return $this->belongsTo(CustomField::class, 'field_id')->withTrashed();
    }
}

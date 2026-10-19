<?php

namespace App\Models\ExamModule;

use App\Models\ExamModule\Concerns\Auditable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

/** Schema Builder field: extras unique to one exam. Never for eligibility, fees or dates. */
class CustomField extends Model
{
    use Auditable, SoftDeletes;

    protected $table = 'exm_custom_fields';
    protected $fillable = ['exam_id', 'key', 'label', 'type', 'options', 'scope', 'sort_order'];
    protected $casts = ['options' => 'array'];

    public const TYPES = ['text' => 'Text', 'rich_text' => 'Rich text', 'number' => 'Number', 'date' => 'Date',
        'dropdown' => 'Dropdown', 'multi_select' => 'Multi-select', 'file' => 'File', 'url' => 'URL', 'group' => 'Repeatable group'];

    /** Words that belong to structured fields, so they can never be a custom field. */
    public const RESERVED = ['eligib', 'qualification', 'percentage', 'age', 'attempt', 'nationality', 'fee', 'fees', 'price',
        'amount', 'date', 'dates', 'deadline', 'registration', 'result', 'admit', 'answer_key', 'schedule', 'syllabus', 'pattern'];

    public function exam()
    {
        return $this->belongsTo(Exam::class, 'exam_id');
    }

    public function values()
    {
        return $this->hasMany(CustomValue::class, 'field_id');
    }

    /** Reserved if any underscore-separated part of the key starts with a structured-field word. */
    public static function reservedWord(string $key): ?string
    {
        foreach (explode('_', $key) as $part) {
            foreach (self::RESERVED as $word) {
                if ($part === $word || (strlen($word) > 3 && str_starts_with($part, $word))) {
                    return $word;
                }
            }
        }
        return null;
    }
}

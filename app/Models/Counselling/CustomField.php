<?php

namespace App\Models\Counselling;

use App\Models\ExamModule\Concerns\Auditable;
use App\Models\ExamModule\CustomField as ExamCustomField;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

/** Counselling Schema Builder field: extras unique to one counselling. Never for eligibility, fees, documents, process, rules or dates. */
class CustomField extends Model
{
    use Auditable, SoftDeletes;

    protected $table = 'cns_custom_fields';
    protected $fillable = ['counselling_id', 'key', 'label', 'type', 'options', 'scope', 'sort_order'];
    protected $casts = ['options' => 'array'];

    /** The same field types as the exam module's Schema Builder. */
    public const TYPES = ExamCustomField::TYPES;

    /**
     * Words that belong to the structured tabs (blocks, rounds, seats, cutoffs), so they can never be a custom field.
     * Not "rules": the Rules tab is seat-leaving and refunds, while "Bond and service rules" is a fair extra tab.
     */
    public const RESERVED = ['eligib', 'fee', 'fees', 'price', 'amount', 'document', 'documents', 'process', 'refund', 'deposit',
        'date', 'dates', 'deadline', 'schedule', 'registration', 'round', 'rounds', 'seat', 'seats', 'cutoff', 'cutoffs', 'allotment', 'result', 'quota'];

    public function counselling()
    {
        return $this->belongsTo(Counselling::class, 'counselling_id');
    }

    public function values()
    {
        return $this->hasMany(CustomValue::class, 'field_id');
    }

    /** Reserved if any underscore-separated part of the key starts with a structured-tab word. */
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

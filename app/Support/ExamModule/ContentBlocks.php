<?php

namespace App\Support\ExamModule;

use App\Models\ExamModule\City;
use App\Models\ProgramLevel;
use Illuminate\Validation\Rule;

/**
 * Definition of every cycle content block (eligibility, pattern, fees, ...).
 * One config drives the form, validation, display and copy-forward, so adding a field
 * means editing this file and one migration.
 *
 * Field types: text, textarea, number, date, select, bool, file, list (one per line), rows (repeating group).
 */
class ContentBlocks
{
    public const FEE_CATEGORIES = ['general' => 'General', 'obc_ncl' => 'OBC-NCL', 'ews' => 'EWS', 'sc' => 'SC', 'st' => 'ST', 'pwd' => 'PwD'];
    public const GENDERS = ['male' => 'Male', 'female' => 'Female', 'transgender' => 'Transgender'];
    public const CURRENCIES = ['INR' => 'INR', 'USD' => 'USD', 'EUR' => 'EUR', 'GBP' => 'GBP', 'AED' => 'AED'];
    public const MODES = ['cbt' => 'Computer based (CBT)', 'pen_paper' => 'Pen and paper', 'hybrid' => 'Hybrid', 'home_based' => 'Home based'];

    /** Table => block definition. Keys double as route segments. */
    public static function all(): array
    {
        return [
            'eligibility' => [
                'title' => 'Eligibility', 'table' => 'exm_eligibilities', 'label' => 'Eligibility',
                'fields' => [
                    ['name' => 'min_qualification_id', 'label' => 'Minimum qualification', 'type' => 'select', 'options' => fn () => ProgramLevel::where('status', 1)->orderBy('title')->pluck('title', 'id')->all(), 'col' => 6],
                    ['name' => 'nationality', 'label' => 'Nationality', 'type' => 'text', 'col' => 6, 'help' => 'E.g. Indian, Any'],
                    ['name' => 'subjects', 'label' => 'Qualifying subjects', 'type' => 'list', 'help' => 'One per line', 'col' => 12],
                    ['name' => 'min_percentage_general', 'label' => 'Min % (General)', 'type' => 'number', 'min' => 0, 'max' => 100, 'col' => 3],
                    ['name' => 'min_percentage_reserved', 'label' => 'Min % (Reserved)', 'type' => 'number', 'min' => 0, 'max' => 100, 'col' => 3],
                    ['name' => 'max_attempts', 'label' => 'Max attempts', 'type' => 'number', 'min' => 1, 'max' => 50, 'col' => 3],
                    ['name' => 'appearing_allowed', 'label' => 'Appearing candidates allowed', 'type' => 'bool', 'col' => 3],
                    ['name' => 'age_min', 'label' => 'Min age', 'type' => 'number', 'min' => 0, 'max' => 120, 'col' => 3],
                    ['name' => 'age_max', 'label' => 'Max age', 'type' => 'number', 'min' => 0, 'max' => 120, 'col' => 3],
                    ['name' => 'age_as_of', 'label' => 'Age as of', 'type' => 'date', 'col' => 6],
                    ['name' => 'age_relaxation', 'label' => 'Age relaxation by category', 'type' => 'rows', 'col' => 12, 'sub' => [
                        ['name' => 'category', 'label' => 'Category', 'type' => 'select', 'options' => self::FEE_CATEGORIES],
                        ['name' => 'years', 'label' => 'Years', 'type' => 'number', 'min' => 0, 'max' => 30],
                    ]],
                    ['name' => 'other_conditions', 'label' => 'Other conditions', 'type' => 'textarea', 'col' => 12],
                ],
                'summary' => fn ($r) => array_filter([
                    $r->min_qualification_id ? (ProgramLevel::find($r->min_qualification_id)->title ?? null) : null,
                    $r->min_percentage_general !== null ? 'Min ' . (float) $r->min_percentage_general . '%' : null,
                    ($r->age_min || $r->age_max) ? 'Age ' . ($r->age_min ?: '?') . '-' . ($r->age_max ?: '?') : null,
                    $r->max_attempts ? $r->max_attempts . ' attempts' : null,
                    $r->nationality,
                ]),
            ],
            'pattern' => [
                'title' => 'Exam pattern', 'table' => 'exm_patterns', 'label' => 'Pattern',
                'fields' => [
                    ['name' => 'mode', 'label' => 'Mode', 'type' => 'select', 'options' => self::MODES, 'col' => 4],
                    ['name' => 'duration_minutes', 'label' => 'Duration (minutes)', 'type' => 'number', 'min' => 1, 'max' => 2000, 'col' => 4],
                    ['name' => 'total_questions', 'label' => 'Total questions', 'type' => 'number', 'min' => 1, 'max' => 5000, 'col' => 4],
                    ['name' => 'total_marks', 'label' => 'Total marks', 'type' => 'number', 'min' => 0, 'col' => 4],
                    ['name' => 'negative_marking', 'label' => 'Negative marking', 'type' => 'bool', 'col' => 4],
                    ['name' => 'negative_marks', 'label' => 'Marks deducted per wrong answer', 'type' => 'number', 'min' => 0, 'col' => 4],
                    ['name' => 'question_types', 'label' => 'Question types', 'type' => 'list', 'help' => 'One per line (MCQ, numerical...)', 'col' => 12],
                    ['name' => 'sections', 'label' => 'Sections', 'type' => 'rows', 'col' => 12, 'sub' => [
                        ['name' => 'name', 'label' => 'Section', 'type' => 'text'],
                        ['name' => 'questions', 'label' => 'Questions', 'type' => 'number', 'min' => 0],
                        ['name' => 'marks', 'label' => 'Marks', 'type' => 'number', 'min' => 0],
                    ]],
                ],
                'summary' => fn ($r) => array_filter([
                    self::MODES[$r->mode] ?? null,
                    $r->duration_minutes ? $r->duration_minutes . ' min' : null,
                    $r->total_questions ? $r->total_questions . ' questions' : null,
                    $r->total_marks !== null ? (float) $r->total_marks . ' marks' : null,
                    $r->negative_marking ? 'Negative marking' . ($r->negative_marks !== null ? ' (-' . (float) $r->negative_marks . ')' : '') : null,
                    $r->sections ? count($r->sections) . ' sections' : null,
                ]),
            ],
            'fees' => [
                'title' => 'Fees', 'table' => 'exm_fees', 'label' => 'Fee',
                'fields' => [
                    ['name' => 'category', 'label' => 'Category', 'type' => 'select', 'options' => self::FEE_CATEGORIES, 'required' => true, 'col' => 4],
                    ['name' => 'gender', 'label' => 'Gender (optional)', 'type' => 'select', 'options' => self::GENDERS, 'col' => 4],
                    ['name' => 'amount', 'label' => 'Amount', 'type' => 'number', 'min' => 0, 'required' => true, 'col' => 2],
                    ['name' => 'currency', 'label' => 'Currency', 'type' => 'select', 'options' => self::CURRENCIES, 'required' => true, 'default' => 'INR', 'col' => 2],
                ],
                'summary' => fn ($r) => [(self::FEE_CATEGORIES[$r->category] ?? $r->category) . ($r->gender ? ' (' . (self::GENDERS[$r->gender] ?? $r->gender) . ')' : ''), $r->currency . ' ' . (float) $r->amount],
            ],
            'syllabus' => [
                'title' => 'Syllabus', 'table' => 'exm_syllabus', 'label' => 'Syllabus',
                'fields' => [
                    ['name' => 'subject', 'label' => 'Subject', 'type' => 'text', 'col' => 12],
                    ['name' => 'topics', 'label' => 'Topics', 'type' => 'list', 'help' => 'One per line', 'col' => 12],
                    ['name' => 'pdf', 'label' => 'Syllabus PDF', 'type' => 'file', 'mimes' => 'pdf', 'col' => 12],
                ],
                'require_one_of' => ['subject', 'pdf'],
                'summary' => fn ($r) => array_filter([$r->subject, $r->topics ? count($r->topics) . ' topics' : null, $r->pdf ? 'PDF attached' : null]),
            ],
            'centres' => [
                'title' => 'Exam centres', 'table' => 'exm_centres', 'label' => 'Centre',
                'fields' => [
                    ['name' => 'city_id', 'label' => 'City', 'type' => 'select', 'options' => fn () => City::where('status', true)->orderBy('name')->get()->mapWithKeys(fn ($c) => [$c->id => $c->name . ($c->state ? ', ' . $c->state : '')])->all(), 'col' => 6, 'quick_add' => 'city', 'help' => 'Leave empty for a country-level centre (international exams)'],
                    ['name' => 'country', 'label' => 'Country', 'type' => 'text', 'required' => true, 'default' => 'India', 'col' => 6],
                ],
                'summary' => fn ($r) => [($r->city->name ?? 'Any city') . ', ' . $r->country],
            ],
            'documents' => [
                'title' => 'Documents required', 'table' => 'exm_documents', 'label' => 'Document',
                'fields' => [
                    ['name' => 'name', 'label' => 'Document', 'type' => 'text', 'required' => true, 'col' => 8],
                    ['name' => 'is_mandatory', 'label' => 'Mandatory', 'type' => 'bool', 'default' => true, 'col' => 4],
                    ['name' => 'notes', 'label' => 'Notes', 'type' => 'text', 'col' => 12],
                ],
                'summary' => fn ($r) => [$r->name, $r->is_mandatory ? 'Mandatory' : 'Optional'],
            ],
            'papers' => [
                'title' => 'Previous papers', 'table' => 'exm_papers', 'label' => 'Paper',
                'fields' => [
                    ['name' => 'title', 'label' => 'Title', 'type' => 'text', 'required' => true, 'col' => 12],
                    ['name' => 'year', 'label' => 'Year', 'type' => 'number', 'min' => 1990, 'max' => 2100, 'col' => 4],
                    ['name' => 'shift', 'label' => 'Shift', 'type' => 'text', 'col' => 4],
                    ['name' => 'file', 'label' => 'File', 'type' => 'file', 'mimes' => 'pdf,doc,docx,zip', 'required' => true, 'col' => 4],
                ],
                'summary' => fn ($r) => array_filter([$r->title, $r->year, $r->shift]),
            ],
            'faqs' => [
                'title' => 'FAQs', 'table' => 'exm_faqs', 'label' => 'FAQ',
                'fields' => [
                    ['name' => 'question', 'label' => 'Question', 'type' => 'text', 'required' => true, 'col' => 12],
                    ['name' => 'answer', 'label' => 'Answer', 'type' => 'textarea', 'required' => true, 'col' => 12],
                    ['name' => 'sort_order', 'label' => 'Order', 'type' => 'number', 'min' => 0, 'default' => 0, 'col' => 3],
                ],
                'summary' => fn ($r) => [$r->question],
            ],
        ];
    }

    public static function get(string $key): ?array
    {
        return self::all()[$key] ?? null;
    }

    /** Resolve lazy select options. */
    public static function options(array $field): array
    {
        $o = $field['options'] ?? [];
        return $o instanceof \Closure ? $o() : $o;
    }

    public static function fileFields(array $block): array
    {
        return array_column(array_filter($block['fields'], fn ($f) => $f['type'] === 'file'), 'name');
    }

    /** Laravel validation rules for one block (stage handled by the caller). */
    public static function rules(array $block, bool $isUpdate = false): array
    {
        $rules = [];
        foreach ($block['fields'] as $f) {
            $rules += self::fieldRules($f, $f['name'], $isUpdate);
            if ($f['type'] === 'rows') {
                foreach ($f['sub'] as $s) {
                    $rules += self::fieldRules($s, $f['name'] . '.*.' . $s['name'], false, true);
                }
            }
        }
        return $rules;
    }

    private static function fieldRules(array $f, string $path, bool $isUpdate, bool $sub = false): array
    {
        $req = !empty($f['required']) ? 'required' : 'nullable';
        $min = $f['min'] ?? null;
        $max = $f['max'] ?? null;

        $r = match ($f['type']) {
            'text' => [$req, 'string', 'max:255'],
            'textarea' => [$req, 'string', 'max:5000'],
            'number' => [$req, 'numeric'] + ($min !== null ? [2 => "min:$min"] : []) + ($max !== null ? [3 => "max:$max"] : []),
            'date' => [$req, 'date'],
            'bool' => ['nullable', 'boolean'],
            'select' => [$req, Rule::in(array_map('strval', array_keys(self::options($f))))],
            'file' => [(!empty($f['required']) && !$isUpdate) ? 'required' : 'nullable', 'file', 'mimes:' . ($f['mimes'] ?? 'pdf'), 'max:10240'],
            'list' => ['nullable', 'string', 'max:10000'],
            'rows' => ['nullable', 'array', 'max:50'],
            default => ['nullable'],
        };
        return [$path => array_values($r)];
    }
}

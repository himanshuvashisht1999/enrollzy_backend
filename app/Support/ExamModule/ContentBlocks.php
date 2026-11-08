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
 * Field types: text, textarea, richtext (TinyMCE editor), number, date, select, multiselect, bool, file,
 * list (one per line), rows (repeating group; a sub field with 'distinct' => true cannot repeat, e.g. a category).
 * 'section' starts a titled group in the dialog; 'size' => 'xl' gives the block a wide dialog.
 */
class ContentBlocks
{
    /** Active categories from the shared Category master (id => name), used by fees and age relaxation. */
    public static function categories(): array
    {
        static $cache = null;
        return $cache ??= \App\Models\Counselling\Category::where('status', true)->orderBy('sort_order')->orderBy('name')->pluck('name', 'id')->all();
    }

    /** Every category name including inactive ones, so old rows still display. */
    public static function categoryName($id): string
    {
        return \App\Models\Counselling\Category::find($id)?->name ?? '-';
    }

    /** "General, EWS" for a fee row; rows saved before several categories were allowed only have category_id. */
    public static function feeCategories($r): string
    {
        $ids = $r->category_ids ?: array_filter([$r->category_id]);
        return $ids ? implode(', ', array_map(fn ($id) => self::categoryName($id), $ids)) : 'All candidates';
    }

    public const GENDERS = ['male' => 'Male', 'female' => 'Female', 'transgender' => 'Transgender'];
    public const CURRENCIES = ['INR' => 'INR', 'USD' => 'USD', 'EUR' => 'EUR', 'GBP' => 'GBP', 'AED' => 'AED'];
    public const MODES = ['cbt' => 'Computer based (CBT)', 'pen_paper' => 'Pen and paper', 'hybrid' => 'Hybrid', 'home_based' => 'Home based'];

    /** Table => block definition. Keys double as route segments. */
    public static function all(): array
    {
        return [
            'eligibility' => [
                'title' => 'Eligibility', 'table' => 'exm_eligibilities', 'label' => 'Eligibility', 'size' => 'xl',
                'fields' => [
                    ['name' => 'min_qualification_id', 'section' => 'Qualifying exam', 'label' => 'Minimum qualification', 'type' => 'select', 'options' => fn () => ProgramLevel::where('status', 1)->orderBy('title')->pluck('title', 'id')->all(), 'col' => 5],
                    ['name' => 'nationality', 'label' => 'Nationality', 'type' => 'text', 'col' => 4, 'help' => 'E.g. Indian, Any'],
                    ['name' => 'appearing_allowed', 'label' => 'Appearing candidates allowed', 'type' => 'bool', 'col' => 3],
                    ['name' => 'subjects', 'label' => 'Qualifying subjects', 'type' => 'list', 'help' => 'One per line', 'col' => 12],
                    ['name' => 'min_percentage_by_category', 'section' => 'Minimum marks in the qualifying exam', 'label' => 'Minimum % by category', 'type' => 'rows', 'col' => 12, 'add' => 'Add category',
                        'help' => 'One row per category. A category without a row has no minimum.', 'sub' => [
                        ['name' => 'category_id', 'label' => 'Category', 'type' => 'select', 'options' => fn () => self::categories(), 'distinct' => true],
                        ['name' => 'percentage', 'label' => 'Minimum %', 'type' => 'number', 'min' => 0, 'max' => 100],
                    ]],
                    ['name' => 'age_min', 'section' => 'Age', 'label' => 'Min age', 'type' => 'number', 'min' => 0, 'max' => 120, 'col' => 3],
                    ['name' => 'age_max', 'label' => 'Max age (General)', 'type' => 'number', 'min' => 0, 'max' => 120, 'col' => 3],
                    ['name' => 'age_as_of', 'label' => 'Age as of', 'type' => 'date', 'col' => 6],
                    ['name' => 'age_relaxation', 'label' => 'Upper age relaxation by category', 'type' => 'rows', 'col' => 12, 'add' => 'Add category', 'sub' => [
                        ['name' => 'category_id', 'label' => 'Category', 'type' => 'select', 'options' => fn () => self::categories(), 'distinct' => true],
                        ['name' => 'years', 'label' => 'Extra years', 'type' => 'number', 'min' => 0, 'max' => 30],
                    ]],
                    ['name' => 'max_attempts', 'section' => 'Attempts', 'label' => 'Max attempts (all candidates)', 'type' => 'number', 'min' => 1, 'max' => 50, 'col' => 4, 'help' => 'Leave empty if unlimited.'],
                    ['name' => 'attempts_by_category', 'label' => 'Different limit for a category', 'type' => 'rows', 'col' => 12, 'add' => 'Add category',
                        'help' => 'Only categories whose limit differs. Leave attempts empty for unlimited.', 'sub' => [
                        ['name' => 'category_id', 'label' => 'Category', 'type' => 'select', 'options' => fn () => self::categories(), 'distinct' => true],
                        ['name' => 'attempts', 'label' => 'Max attempts', 'type' => 'number', 'min' => 1, 'max' => 50],
                    ]],
                    ['name' => 'other_conditions', 'section' => 'Other conditions', 'label' => 'Other conditions', 'type' => 'richtext', 'col' => 12],
                ],
                'summary' => fn ($r) => array_filter([
                    $r->min_qualification_id ? (ProgramLevel::find($r->min_qualification_id)->title ?? null) : null,
                    $r->min_percentage_by_category ? 'Min % for ' . count($r->min_percentage_by_category) . ' ' . (count($r->min_percentage_by_category) === 1 ? 'category' : 'categories') : null,
                    ($r->age_min || $r->age_max) ? 'Age ' . ($r->age_min ?: '?') . '-' . ($r->age_max ?: '?') : null,
                    $r->max_attempts ? $r->max_attempts . ' attempts' : null,
                    $r->nationality,
                ]),
            ],
            'pattern' => [
                'title' => 'Exam pattern', 'table' => 'exm_patterns', 'label' => 'Pattern', 'size' => 'xl',
                'fields' => [
                    // one row per paper: JEE Main Paper 1 (B.E./B.Tech) is CBT, Paper 2A (B.Arch) is CBT with a pen and paper drawing test
                    ['name' => 'paper_name', 'section' => 'Paper', 'label' => 'Paper name', 'type' => 'text', 'col' => 12, 'help' => 'E.g. "Paper 1 (B.E./B.Tech)", "Paper 2A (B.Arch)". Add one pattern per paper; leave empty if the exam has one paper.'],
                    ['name' => 'mode', 'label' => 'Mode', 'type' => 'select', 'options' => self::MODES, 'col' => 3, 'help' => 'Of this paper. A section held differently sets its own mode below.'],
                    ['name' => 'duration_minutes', 'label' => 'Duration (minutes)', 'type' => 'number', 'min' => 1, 'max' => 2000, 'col' => 3],
                    ['name' => 'total_questions', 'label' => 'Total questions', 'type' => 'number', 'min' => 1, 'max' => 5000, 'col' => 3],
                    ['name' => 'total_marks', 'label' => 'Total marks', 'type' => 'number', 'min' => 0, 'col' => 3],
                    ['name' => 'question_types', 'label' => 'Question types', 'type' => 'list', 'help' => 'One per line (MCQ, numerical...)', 'col' => 12],
                    ['name' => 'marks_per_correct', 'section' => 'Marking scheme', 'label' => 'Marks per correct answer', 'type' => 'number', 'min' => 0, 'col' => 4],
                    ['name' => 'negative_marking', 'label' => 'Negative marking', 'type' => 'bool', 'col' => 4],
                    ['name' => 'negative_marks', 'label' => 'Marks deducted per wrong answer', 'type' => 'number', 'min' => 0, 'col' => 4],
                    ['name' => 'sections', 'section' => 'Sections', 'label' => 'Section-wise distribution', 'type' => 'rows', 'col' => 12, 'add' => 'Add section',
                        'help' => 'Fill mode, marking or time only where a section differs from the paper (e.g. a pen and paper drawing test in a CBT paper).', 'sub' => [
                        ['name' => 'name', 'label' => 'Section', 'type' => 'text'],
                        ['name' => 'mode', 'label' => 'Mode (if different)', 'type' => 'select', 'options' => self::MODES],
                        ['name' => 'questions', 'label' => 'Questions', 'type' => 'number', 'min' => 0],
                        ['name' => 'marks', 'label' => 'Marks', 'type' => 'number', 'min' => 0],
                        ['name' => 'correct_marks', 'label' => '+ per correct', 'type' => 'number', 'min' => 0],
                        ['name' => 'negative_marks', 'label' => '− per wrong', 'type' => 'number', 'min' => 0],
                        ['name' => 'duration_minutes', 'label' => 'Time (min)', 'type' => 'number', 'min' => 1, 'max' => 2000],
                    ]],
                    ['name' => 'notes', 'section' => 'Notes', 'label' => 'Notes (shifts, languages, calculator, anything else)', 'type' => 'richtext', 'col' => 12],
                ],
                'summary' => fn ($r) => array_filter([
                    $r->paper_name,
                    implode(' + ', array_unique(array_filter(array_merge([self::MODES[$r->mode] ?? null], array_map(fn ($s) => self::MODES[$s['mode'] ?? ''] ?? null, $r->sections ?? []))))) ?: null,
                    $r->duration_minutes ? $r->duration_minutes . ' min' : null,
                    $r->total_questions ? $r->total_questions . ' questions' : null,
                    $r->total_marks !== null ? (float) $r->total_marks . ' marks' : null,
                    $r->marks_per_correct !== null ? '+' . (float) $r->marks_per_correct . ' per correct' : null,
                    $r->negative_marking ? 'Negative marking' . ($r->negative_marks !== null ? ' (-' . (float) $r->negative_marks . ')' : '') : null,
                    $r->sections ? count($r->sections) . ' sections' : null,
                ]),
            ],
            'fees' => [
                'title' => 'Fees', 'table' => 'exm_fees', 'label' => 'Fee',
                'fields' => [
                    ['name' => 'category_ids', 'label' => 'Categories', 'type' => 'multiselect', 'options' => fn () => self::categories(), 'col' => 12,
                        'help' => 'Pick every category that pays this amount, e.g. General, EWS and OBC-NCL. Leave empty if every candidate pays it.'],
                    ['name' => 'gender', 'label' => 'Gender (optional)', 'type' => 'select', 'options' => self::GENDERS, 'col' => 6],
                    ['name' => 'amount', 'label' => 'Amount', 'type' => 'number', 'min' => 0, 'required' => true, 'col' => 3],
                    ['name' => 'currency', 'label' => 'Currency', 'type' => 'select', 'options' => self::CURRENCIES, 'required' => true, 'default' => 'INR', 'col' => 3],
                    ['name' => 'note', 'label' => 'Note (optional)', 'type' => 'text', 'col' => 12, 'help' => 'When the fee depends on something else, e.g. "per paper", "for centres outside India", "both papers".'],
                ],
                'summary' => fn ($r) => array_filter([self::feeCategories($r) . ($r->gender ? ' (' . (self::GENDERS[$r->gender] ?? $r->gender) . ')' : ''), $r->currency . ' ' . (float) $r->amount, $r->note]),
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
                    ['name' => 'category_ids', 'label' => 'Only for these categories', 'type' => 'multiselect', 'options' => fn () => self::categories(), 'col' => 12,
                        'help' => 'E.g. a caste certificate for SC / ST / OBC-NCL. Leave empty if every candidate needs it.'],
                    ['name' => 'notes', 'label' => 'Notes', 'type' => 'text', 'col' => 12, 'help' => 'Format, size or validity, e.g. "JPG, 10-200 KB" or "issued after 1 April 2026".'],
                ],
                'summary' => fn ($r) => array_filter([$r->name, $r->is_mandatory ? 'Mandatory' : 'Optional',
                    $r->category_ids ? 'Only ' . implode(', ', array_map(fn ($id) => self::categoryName($id), $r->category_ids)) : null]),
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
                    ['name' => 'answer', 'label' => 'Answer', 'type' => 'richtext', 'required' => true, 'col' => 12],
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

    public static function fieldRules(array $f, string $path, bool $isUpdate, bool $sub = false): array
    {
        $req = !empty($f['required']) ? 'required' : 'nullable';
        $min = $f['min'] ?? null;
        $max = $f['max'] ?? null;

        $r = match ($f['type']) {
            'text' => [$req, 'string', 'max:255'],
            'textarea' => [$req, 'string', 'max:5000'],
            // Editor HTML (tags, tables) is much longer than the text it shows; text columns hold 64 KB.
            'richtext' => [$req, 'string', 'max:60000'],
            'number' => [$req, 'numeric'] + ($min !== null ? [2 => "min:$min"] : []) + ($max !== null ? [3 => "max:$max"] : []),
            'date' => [$req, 'date'],
            'bool' => ['nullable', 'boolean'],
            'select' => [$req, Rule::in(array_map('strval', array_keys(self::options($f))))],
            'file' => [(!empty($f['required']) && !$isUpdate) ? 'required' : 'nullable', 'file', 'mimes:' . ($f['mimes'] ?? 'pdf'), 'max:10240'],
            'list' => ['nullable', 'string', 'max:10000'],
            'url' => [$req, 'url', 'max:500'],
            'multiselect' => ['nullable', 'array'],
            'rows' => ['nullable', 'array', 'max:50'],
            default => ['nullable'],
        };
        if ($sub && !empty($f['distinct'])) {
            $r[] = 'distinct';
        }
        $rules = [$path => array_values($r)];
        if ($f['type'] === 'multiselect') {
            $rules[$path . '.*'] = [Rule::in(array_map('strval', array_keys(self::options($f))))];
        }
        return $rules;
    }

    /** Readable names for rows sub-fields in validation messages ("Category in Minimum % by category"). */
    public static function attributeNames(array $block): array
    {
        $names = [];
        foreach ($block['fields'] as $f) {
            $names[$f['name']] = $f['label'];
            if ($f['type'] === 'multiselect') $names[$f['name'] . '.*'] = $f['label'];
            foreach ($f['sub'] ?? [] as $s) {
                $names[$f['name'] . '.*.' . $s['name']] = $s['label'] . ' in ' . $f['label'];
            }
        }
        return $names;
    }
}

<?php

namespace App\Support\Counselling;

use App\Models\Counselling\CandidateAction;
use App\Models\Counselling\Category;
use App\Models\Counselling\DocumentType;
use App\Models\Counselling\Quota;
use App\Models\ExamModule\Exam;
use App\Models\ExamModule\EventType;
use App\Support\ExamModule\ContentBlocks;
use Illuminate\Support\Facades\DB;

/**
 * Cycle content for a counselling: eligibility, process, fees, documents, rules.
 * Same idea as ExamModule\ContentBlocks, plus:
 *  - 'scopes': a block may live on the cycle, on a round, or both (a round row overrides or adds to the cycle's)
 *  - 'unique': the fields that identify a row within its scope; a round row with the same key replaces the cycle row
 *  - 'inherit': 'replace' (round row overrides the cycle row) or 'add' (round rows are added on top)
 *  - field type 'children': rows kept in a real child table (e.g. category-wise minimum scores)
 * Field types: text, textarea, number, date, select, multiselect, bool, file, list, url, rows, children.
 */
class CnsBlocks
{
    public const INSTITUTE_TYPES = ['government' => 'Government', 'private' => 'Private', 'deemed' => 'Deemed', 'central_university' => 'Central university', 'autonomous' => 'Autonomous', 'other' => 'Other'];
    public const FEE_TYPES = ['registration' => 'Registration', 'security_deposit' => 'Security deposit', 'tuition_advance' => 'Tuition advance', 'other' => 'Other'];
    public const DEPOSIT_OUTCOMES = ['refunded' => 'Refunded', 'forfeited' => 'Forfeited', 'partly_refunded' => 'Partly refunded', 'adjusted_in_fee' => 'Adjusted in fee', 'not_applicable' => 'Not applicable'];
    public const SEAT_OUTCOMES = ['retained' => 'Retained', 'forfeited' => 'Forfeited', 'released' => 'Released'];
    public const DOC_STAGES = ['registration' => 'Registration', 'reporting' => 'Reporting', 'document_verification' => 'Document verification'];
    public const DOMICILE_RULES = ['none' => 'None', 'state_domicile' => 'State domicile', 'study_years_in_state' => 'Years of study in the state', 'domicile_or_study' => 'Domicile or study', 'parent_domicile' => 'Parent domicile'];
    public const CLASSES = ['class_12' => 'Class 12', 'graduation' => 'Graduation', 'diploma' => 'Diploma', 'post_graduation' => 'Post-graduation'];
    public const NATIONALITIES = ['Indian' => 'Indian', 'OCI' => 'OCI', 'PIO' => 'PIO', 'NRI' => 'NRI', 'Foreign national' => 'Foreign national'];
    public const CURRENCIES = ['INR' => 'INR', 'USD' => 'USD', 'EUR' => 'EUR', 'GBP' => 'GBP', 'AED' => 'AED'];

    // ---- option sources (closures receive the context: ['counselling_id' => int]) ----
    private static function categories(): array { return Category::where('status', true)->orderBy('sort_order')->orderBy('name')->pluck('name', 'id')->all(); }
    private static function quotas(): array { return Quota::where('status', true)->orderBy('sort_order')->pluck('name', 'id')->all(); }

    public static function all(): array
    {
        return [
            'eligibility' => [
                'title' => 'Eligibility', 'table' => 'cns_eligibility', 'label' => 'Eligibility', 'scopes' => ['cycle'], 'inherit' => 'add',
                'unique' => ['quota_id'],
                'help' => 'One row for all quotas, plus extra rows where a quota has different rules (NRI, Management).',
                'fields' => [
                    ['name' => 'quota_id', 'label' => 'Applies to quota', 'type' => 'select', 'options' => fn () => self::quotas(), 'blank' => 'All quotas', 'col' => 4],
                    ['name' => 'qualifying_class', 'label' => 'Qualifying class', 'type' => 'select', 'options' => self::CLASSES, 'col' => 4],
                    ['name' => 'nationality_allowed', 'label' => 'Nationality allowed', 'type' => 'multiselect', 'options' => self::NATIONALITIES, 'col' => 4],
                    ['name' => 'qualifying_subjects', 'label' => 'Qualifying subjects', 'type' => 'list', 'help' => 'One per line', 'col' => 12],
                    ['name' => 'age_min', 'label' => 'Min age', 'type' => 'number', 'min' => 0, 'max' => 120, 'col' => 3],
                    ['name' => 'age_max', 'label' => 'Max age', 'type' => 'number', 'min' => 0, 'max' => 120, 'col' => 3],
                    ['name' => 'age_as_of', 'label' => 'Age as of', 'type' => 'date', 'col' => 6, 'help' => 'Cleared by Copy Cycle: enter the new date each year'],
                    ['name' => 'domicile_rule', 'label' => 'Domicile rule', 'type' => 'select', 'options' => self::DOMICILE_RULES, 'required' => true, 'default' => 'none', 'col' => 6],
                    ['name' => 'study_years_required', 'label' => 'Years of study required', 'type' => 'number', 'min' => 1, 'max' => 20, 'col' => 6],
                    ['name' => 'domicile_states', 'label' => 'Domicile states', 'type' => 'multiselect', 'options' => fn () => array_combine(Exam::STATES, Exam::STATES), 'col' => 12],
                    ['name' => 'domicile_notes', 'label' => 'Domicile exceptions', 'type' => 'textarea', 'help' => 'Defence wards, central govt employees, migrants...', 'col' => 12],
                    ['name' => 'other_conditions', 'label' => 'Other conditions', 'type' => 'textarea', 'col' => 12],
                    ['name' => 'scores', 'label' => 'Minimum scores by category', 'type' => 'children', 'col' => 12,
                        'table' => 'cns_eligibility_scores', 'fk' => 'eligibility_id', 'sub' => [
                            ['name' => 'category_id', 'label' => 'Category', 'type' => 'select', 'options' => fn () => self::categories(), 'required' => true],
                            ['name' => 'exam_id', 'label' => 'Exam', 'type' => 'select', 'options' => 'mapped_exams', 'blank' => 'Any'],
                            ['name' => 'min_percentile', 'label' => 'Min percentile', 'type' => 'number', 'min' => 0, 'max' => 100],
                            ['name' => 'min_score', 'label' => 'Min score', 'type' => 'number', 'min' => 0],
                            ['name' => 'max_rank', 'label' => 'Max rank', 'type' => 'number', 'min' => 1],
                            ['name' => 'min_qualifying_percent', 'label' => 'Min class % ', 'type' => 'number', 'min' => 0, 'max' => 100],
                        ]],
                ],
                'validate' => 'eligibility',
                'summary' => fn ($r) => array_filter([
                    $r->quota_id ? (Quota::find($r->quota_id)->name ?? null) : 'All quotas',
                    self::CLASSES[$r->qualifying_class] ?? null,
                    ($r->age_min || $r->age_max) ? 'Age ' . ($r->age_min ?: '?') . '-' . ($r->age_max ?: '?') : null,
                    $r->domicile_rule !== 'none' ? self::DOMICILE_RULES[$r->domicile_rule] : null,
                    ($n = DB::table('cns_eligibility_scores')->where('eligibility_id', $r->id)->count()) ? $n . ' score rules' : null,
                ]),
            ],
            'process' => [
                'title' => 'Process', 'table' => 'cns_process_steps', 'label' => 'Step', 'scopes' => ['cycle', 'round'], 'inherit' => 'add',
                'unique' => [], 'order' => 'step_no',
                'help' => 'Ordered steps shown as a numbered timeline. A round can add its own steps.',
                'fields' => [
                    ['name' => 'step_no', 'label' => 'Step no.', 'type' => 'number', 'min' => 1, 'max' => 500, 'col' => 3, 'help' => 'Blank = next number'],
                    ['name' => 'title', 'label' => 'Title', 'type' => 'text', 'required' => true, 'col' => 9],
                    ['name' => 'description', 'label' => 'Description', 'type' => 'textarea', 'col' => 12],
                    ['name' => 'link_url', 'label' => 'Link', 'type' => 'url', 'col' => 6],
                    ['name' => 'event_type_id', 'label' => 'Show the date of event', 'type' => 'select', 'options' => fn () => EventType::where('status', true)->whereIn('applies_to', ['counselling', 'both'])->orderBy('sort_order')->pluck('name', 'id')->all(), 'col' => 6, 'help' => 'The timeline then shows this event\'s date automatically'],
                ],
                'summary' => fn ($r) => array_filter(['#' . $r->step_no, $r->title, $r->event_type_id ? 'date: ' . (EventType::find($r->event_type_id)->name ?? '') : null]),
            ],
            'fees' => [
                'title' => 'Fees & deposits', 'table' => 'cns_fees', 'label' => 'Fee', 'scopes' => ['cycle', 'round'], 'inherit' => 'replace',
                'unique' => ['fee_type', 'category_id', 'quota_id', 'institute_type'],
                'help' => 'A round row replaces the cycle row with the same fee type, category, quota and institute type.',
                'fields' => [
                    ['name' => 'fee_type', 'label' => 'Fee type', 'type' => 'select', 'options' => self::FEE_TYPES, 'required' => true, 'col' => 4],
                    ['name' => 'quota_id', 'label' => 'Quota', 'type' => 'select', 'options' => fn () => self::quotas(), 'blank' => 'All', 'col' => 4],
                    ['name' => 'category_id', 'label' => 'Category', 'type' => 'select', 'options' => fn () => self::categories(), 'blank' => 'All', 'col' => 4],
                    ['name' => 'institute_type', 'label' => 'Institute type', 'type' => 'select', 'options' => self::INSTITUTE_TYPES, 'blank' => 'All', 'col' => 4],
                    ['name' => 'amount', 'label' => 'Amount', 'type' => 'number', 'min' => 0, 'required' => true, 'col' => 3],
                    ['name' => 'currency', 'label' => 'Currency', 'type' => 'select', 'options' => self::CURRENCIES, 'required' => true, 'default' => 'INR', 'col' => 2],
                    ['name' => 'is_refundable', 'label' => 'Refundable (see rules)', 'type' => 'bool', 'col' => 3],
                    ['name' => 'note', 'label' => 'Note', 'type' => 'text', 'col' => 12],
                ],
                'summary' => fn ($r) => array_filter([
                    self::FEE_TYPES[$r->fee_type] ?? $r->fee_type,
                    $r->currency . ' ' . (float) $r->amount,
                    $r->category_id ? (Category::find($r->category_id)->name ?? null) : null,
                    $r->quota_id ? (Quota::find($r->quota_id)->name ?? null) : null,
                    self::INSTITUTE_TYPES[$r->institute_type] ?? null,
                    $r->is_refundable ? 'refundable' : null,
                ]),
            ],
            'documents' => [
                'title' => 'Documents', 'table' => 'cns_documents', 'label' => 'Document', 'scopes' => ['cycle', 'round'], 'inherit' => 'add',
                'unique' => [], 'order' => 'sort_order',
                'help' => 'Round documents are added on top of the cycle\'s.',
                'fields' => [
                    ['name' => 'document_type_id', 'label' => 'Document', 'type' => 'select', 'options' => fn () => DocumentType::where('status', true)->orderBy('name')->pluck('name', 'id')->all(), 'required' => true, 'col' => 6],
                    ['name' => 'stage', 'label' => 'Needed at', 'type' => 'select', 'options' => self::DOC_STAGES, 'required' => true, 'default' => 'registration', 'col' => 6],
                    ['name' => 'applies_to_quota_id', 'label' => 'Only for quota', 'type' => 'select', 'options' => fn () => self::quotas(), 'blank' => 'All', 'col' => 4],
                    ['name' => 'applies_to_category_id', 'label' => 'Only for category', 'type' => 'select', 'options' => fn () => self::categories(), 'blank' => 'All', 'col' => 4],
                    ['name' => 'is_mandatory', 'label' => 'Mandatory', 'type' => 'bool', 'default' => true, 'col' => 2],
                    ['name' => 'sort_order', 'label' => 'Order', 'type' => 'number', 'min' => 0, 'default' => 0, 'col' => 2],
                    ['name' => 'template_file', 'label' => 'Template file (affidavit / undertaking)', 'type' => 'file', 'mimes' => 'pdf,doc,docx', 'col' => 6],
                    ['name' => 'template_url', 'label' => 'Template URL', 'type' => 'url', 'col' => 6],
                    ['name' => 'format_notes', 'label' => 'Format notes', 'type' => 'text', 'help' => 'E.g. On Rs 100 stamp paper, notarised', 'col' => 12],
                ],
                'summary' => fn ($r) => array_filter([
                    DocumentType::find($r->document_type_id)->name ?? '-', self::DOC_STAGES[$r->stage] ?? null,
                    $r->is_mandatory ? 'mandatory' : 'optional',
                    $r->applies_to_quota_id ? 'only ' . (Quota::find($r->applies_to_quota_id)->name ?? '') : null,
                    $r->applies_to_category_id ? 'only ' . (Category::find($r->applies_to_category_id)->name ?? '') : null,
                ]),
            ],
            'rules' => [
                'title' => 'Rules', 'table' => 'cns_rules', 'label' => 'Rule', 'scopes' => ['cycle', 'round'], 'inherit' => 'replace',
                'unique' => ['candidate_action_id', 'quota_id', 'institute_type'],
                'help' => 'If the candidate does X, this happens to their deposit and seat. A round row replaces the cycle row with the same action, quota and institute type.',
                'fields' => [
                    ['name' => 'candidate_action_id', 'label' => 'If the candidate...', 'type' => 'select', 'options' => fn () => CandidateAction::where('status', true)->orderBy('sort_order')->pluck('name', 'id')->all(), 'required' => true, 'col' => 12],
                    ['name' => 'quota_id', 'label' => 'Quota', 'type' => 'select', 'options' => fn () => self::quotas(), 'blank' => 'All', 'col' => 6],
                    ['name' => 'institute_type', 'label' => 'Institute type', 'type' => 'select', 'options' => self::INSTITUTE_TYPES, 'blank' => 'All', 'col' => 6],
                    ['name' => 'deposit_outcome', 'label' => 'Deposit', 'type' => 'select', 'options' => self::DEPOSIT_OUTCOMES, 'required' => true, 'default' => 'not_applicable', 'col' => 4],
                    ['name' => 'refund_percent', 'label' => 'Refund %', 'type' => 'number', 'min' => 0, 'max' => 100, 'col' => 4],
                    ['name' => 'refund_amount', 'label' => 'Refund amount', 'type' => 'number', 'min' => 0, 'col' => 4],
                    ['name' => 'seat_outcome', 'label' => 'Seat', 'type' => 'select', 'options' => self::SEAT_OUTCOMES, 'blank' => 'Not specified', 'col' => 6],
                    ['name' => 'eligible_for_next_round', 'label' => 'Can take part in the next round', 'type' => 'bool', 'default' => true, 'col' => 6],
                    ['name' => 'penalty_note', 'label' => 'Penalty note', 'type' => 'text', 'help' => 'E.g. Debarred from next year\'s counselling', 'col' => 12],
                    ['name' => 'source_url', 'label' => 'Source URL', 'type' => 'url', 'required' => true, 'col' => 12],
                ],
                'validate' => 'rules',
                'summary' => fn ($r) => array_filter([
                    CandidateAction::find($r->candidate_action_id)->name ?? '-',
                    'deposit ' . strtolower(self::DEPOSIT_OUTCOMES[$r->deposit_outcome] ?? '')
                        . ($r->deposit_outcome === 'partly_refunded' ? ' (' . ($r->refund_percent !== null ? (float) $r->refund_percent . '%' : 'Rs ' . (float) $r->refund_amount) . ')' : ''),
                    $r->seat_outcome ? 'seat ' . $r->seat_outcome : null,
                    $r->eligible_for_next_round ? null : 'debarred from next round',
                    $r->quota_id ? (Quota::find($r->quota_id)->name ?? null) : null,
                    self::INSTITUTE_TYPES[$r->institute_type] ?? null,
                ]),
            ],
        ];
    }

    public static function get(string $key): ?array
    {
        return self::all()[$key] ?? null;
    }

    /** Definition with every select option resolved to a plain array (closures need the counselling context). */
    public static function resolved(string $key, ?int $counsellingId = null): array
    {
        $def = self::get($key) ?? abort(404);
        $resolve = function (array $f) use ($counsellingId) {
            if (($f['options'] ?? null) === 'mapped_exams') {
                $f['options'] = $counsellingId
                    ? Exam::whereIn('id', DB::table('cns_counselling_exams')->where('counselling_id', $counsellingId)->pluck('exam_id'))->orderBy('name')->pluck('name', 'id')->all()
                    : [];
            } elseif (($f['options'] ?? null) instanceof \Closure) {
                $f['options'] = ($f['options'])();
            }
            return $f;
        };
        $def['fields'] = array_map(function ($f) use ($resolve) {
            $f = $resolve($f);
            if (isset($f['sub'])) {
                $f['sub'] = array_map($resolve, $f['sub']);
            }
            return $f;
        }, $def['fields']);
        return $def;
    }

    public static function fileFields(array $def): array
    {
        return array_column(array_filter($def['fields'], fn ($f) => $f['type'] === 'file'), 'name');
    }

    /** Validation rules for a resolved definition. */
    public static function rules(array $def, bool $isUpdate = false): array
    {
        $rules = [];
        foreach ($def['fields'] as $f) {
            if ($f['type'] === 'children') {
                $rules[$f['name']] = ['nullable', 'array', 'max:60'];
                foreach ($f['sub'] as $s) {
                    $rules += ContentBlocks::fieldRules($s, $f['name'] . '.*.' . $s['name'], false, true);
                }
                continue;
            }
            $rules += ContentBlocks::fieldRules($f, $f['name'], $isUpdate);
            if ($f['type'] === 'multiselect') {
                $rules[$f['name'] . '.*'] = [\Illuminate\Validation\Rule::in(array_map('strval', array_keys($f['options'] ?? [])))];
            }
        }
        return $rules;
    }
}

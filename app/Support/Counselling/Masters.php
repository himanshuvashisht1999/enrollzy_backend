<?php

namespace App\Support\Counselling;

use App\Models\Counselling\CandidateAction;
use App\Models\Counselling\Category;
use App\Models\Counselling\DocumentType;
use App\Models\Counselling\Quota;
use App\Models\Counselling\RoundType;
use App\Models\ExamModule\Exam;
use Illuminate\Support\Facades\DB;

/**
 * Definitions for the small counselling masters. One controller and one view serve all of them.
 * Field types: text, textarea, number, select, multiselect, bool.
 */
class Masters
{
    public static function all(): array
    {
        return [
            'categories' => [
                'title' => 'Categories', 'singular' => 'Category', 'model' => Category::class,
                'help' => 'Shared by exam fees, eligibility and counselling seats and cutoffs. Add state categories (Karnataka 2A, Maharashtra VJ...) with scope "State".',
                'fields' => [
                    ['name' => 'name', 'label' => 'Name', 'type' => 'text', 'required' => true, 'col' => 6],
                    ['name' => 'short_name', 'label' => 'Short name', 'type' => 'text', 'col' => 3],
                    ['name' => 'scope', 'label' => 'Scope', 'type' => 'select', 'options' => ['central' => 'Central', 'state' => 'State'], 'required' => true, 'default' => 'central', 'col' => 3],
                    ['name' => 'states', 'label' => 'States (for state scope)', 'type' => 'multiselect', 'options' => fn () => array_combine(Exam::STATES, Exam::STATES), 'col' => 12],
                    ['name' => 'is_pwd_subcategory', 'label' => 'PwD sub-category', 'type' => 'bool', 'col' => 4],
                    ['name' => 'sort_order', 'label' => 'Order', 'type' => 'number', 'default' => 0, 'col' => 4],
                    ['name' => 'status', 'label' => 'Active', 'type' => 'bool', 'default' => true, 'col' => 4],
                ],
                'columns' => ['name', 'short_name', 'scope'],
                'in_use' => fn ($m) => DB::table('exm_fees')->where('category_id', $m->id)->exists(),
            ],
            'quotas' => [
                'title' => 'Quotas', 'singular' => 'Quota', 'model' => Quota::class,
                'help' => 'All India, State, Management, NRI, Deemed...',
                'fields' => [
                    ['name' => 'name', 'label' => 'Name', 'type' => 'text', 'required' => true, 'col' => 6],
                    ['name' => 'short_name', 'label' => 'Short name', 'type' => 'text', 'col' => 3],
                    ['name' => 'sort_order', 'label' => 'Order', 'type' => 'number', 'default' => 0, 'col' => 3],
                    ['name' => 'description', 'label' => 'Description', 'type' => 'textarea', 'col' => 12],
                    ['name' => 'status', 'label' => 'Active', 'type' => 'bool', 'default' => true, 'col' => 4],
                ],
                'columns' => ['name', 'short_name'],
                'in_use' => fn ($m) => DB::table('exm_cycle_events')->where('quota_id', $m->id)->exists(),
            ],
            'round-types' => [
                'title' => 'Round types', 'singular' => 'Round type', 'model' => RoundType::class,
                'help' => 'Regular, Mop-up, Stray vacancy, Spot, Special. The default decides whether fresh registration is allowed in rounds of this type.',
                'fields' => [
                    ['name' => 'name', 'label' => 'Name', 'type' => 'text', 'required' => true, 'col' => 6],
                    ['name' => 'sort_order', 'label' => 'Order', 'type' => 'number', 'default' => 0, 'col' => 3],
                    ['name' => 'is_fresh_registration_default', 'label' => 'Fresh registration allowed by default', 'type' => 'bool', 'col' => 6],
                    ['name' => 'status', 'label' => 'Active', 'type' => 'bool', 'default' => true, 'col' => 3],
                ],
                'columns' => ['name'],
                'in_use' => fn ($m) => DB::table('cns_rounds')->where('round_type_id', $m->id)->exists(),
            ],
            'candidate-actions' => [
                'title' => 'Candidate actions', 'singular' => 'Candidate action', 'model' => CandidateAction::class,
                'help' => 'What a candidate does (joined, resigned within window...). Round rules in Phase A2 say what happens to the deposit and seat for each action.',
                'fields' => [
                    ['name' => 'name', 'label' => 'Name', 'type' => 'text', 'required' => true, 'col' => 8],
                    ['name' => 'sort_order', 'label' => 'Order', 'type' => 'number', 'default' => 0, 'col' => 4],
                    ['name' => 'description', 'label' => 'Description', 'type' => 'textarea', 'col' => 12],
                    ['name' => 'status', 'label' => 'Active', 'type' => 'bool', 'default' => true, 'col' => 4],
                ],
                'columns' => ['name'],
                'in_use' => fn ($m) => false,
            ],
            'document-types' => [
                'title' => 'Documents', 'singular' => 'Document', 'model' => DocumentType::class,
                'help' => 'Reusable document names (Class 10 marksheet, domicile certificate, NRI sponsor affidavit...).',
                'fields' => [
                    ['name' => 'name', 'label' => 'Name', 'type' => 'text', 'required' => true, 'col' => 8],
                    ['name' => 'is_affidavit', 'label' => 'Affidavit / undertaking', 'type' => 'bool', 'col' => 4],
                    ['name' => 'description', 'label' => 'Description', 'type' => 'textarea', 'col' => 12],
                    ['name' => 'status', 'label' => 'Active', 'type' => 'bool', 'default' => true, 'col' => 4],
                ],
                'columns' => ['name'],
                'in_use' => fn ($m) => false,
            ],
        ];
    }

    public static function get(string $key): ?array
    {
        return self::all()[$key] ?? null;
    }

    public static function options(array $field): array
    {
        $o = $field['options'] ?? [];
        return $o instanceof \Closure ? $o() : $o;
    }
}

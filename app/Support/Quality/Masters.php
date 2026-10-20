<?php

namespace App\Support\Quality;

use App\Models\Discipline;
use App\Models\Quality\AccreditationBody;
use App\Models\Quality\RankingBody;
use App\Models\Quality\RankingCategory;

/** Definitions for the three quality masters. One controller and one view serve all of them. Field types: text, textarea, number, select, multiselect, csv, bool. */
class Masters
{
    public static function all(): array
    {
        $levels = fn () => Levels::LABELS;
        return [
            'ranking-bodies' => [
                'title' => 'Ranking bodies', 'singular' => 'Ranking body', 'model' => RankingBody::class, 'unique' => ['name'],
                'help' => 'NIRF, QS, THE, India Today... Trust level tells the website how prominently to show the source.',
                'fields' => [
                    ['name' => 'name', 'label' => 'Name', 'type' => 'text', 'required' => true, 'col' => 8],
                    ['name' => 'short_name', 'label' => 'Short name', 'type' => 'text', 'col' => 4],
                    ['name' => 'type', 'label' => 'Type', 'type' => 'select', 'options' => RankingBody::TYPES, 'required' => true, 'col' => 4],
                    ['name' => 'trust_level', 'label' => 'Trust level', 'type' => 'select', 'options' => RankingBody::TRUST, 'required' => true, 'default' => 'medium', 'col' => 4],
                    ['name' => 'country', 'label' => 'Country', 'type' => 'text', 'col' => 4],
                    ['name' => 'website', 'label' => 'Website', 'type' => 'text', 'col' => 8],
                    ['name' => 'sort_order', 'label' => 'Order', 'type' => 'number', 'default' => 0, 'col' => 2],
                    ['name' => 'status', 'label' => 'Active', 'type' => 'bool', 'default' => true, 'col' => 2],
                ],
                'columns' => ['name', 'short_name', 'type', 'trust_level'],
                'in_use' => fn ($m) => $m->rankings()->withTrashed()->exists() || $m->categories()->exists(),
            ],
            'ranking-categories' => [
                'title' => 'Ranking categories', 'singular' => 'Ranking category', 'model' => RankingCategory::class, 'unique' => [],
                'help' => 'Overall, Engineering, Management, Asia, Subject... A category limited to certain levels cannot be recorded at other levels. Link a discipline to show a subject ranking on that discipline\'s courses.',
                'fields' => [
                    ['name' => 'ranking_body_id', 'label' => 'Ranking body', 'type' => 'select', 'options' => fn () => RankingBody::orderBy('sort_order')->pluck('name', 'id')->all(), 'required' => true, 'col' => 6],
                    ['name' => 'name', 'label' => 'Category', 'type' => 'text', 'required' => true, 'col' => 6],
                    ['name' => 'applicable_levels', 'label' => 'Recordable at (empty = any level)', 'type' => 'multiselect', 'options' => $levels, 'col' => 12],
                    ['name' => 'discipline_id', 'label' => 'Discipline (subject rankings)', 'type' => 'select', 'options' => fn () => Discipline::orderBy('title')->pluck('title', 'id')->all(), 'col' => 6],
                    ['name' => 'sort_order', 'label' => 'Order', 'type' => 'number', 'default' => 0, 'col' => 3],
                    ['name' => 'status', 'label' => 'Active', 'type' => 'bool', 'default' => true, 'col' => 3],
                ],
                'columns' => ['body_label', 'name', 'levels_label'],
                'in_use' => fn ($m) => $m->rankings()->withTrashed()->exists(),
            ],
            'accreditation-bodies' => [
                'title' => 'Accreditation & approval bodies', 'singular' => 'Body', 'model' => AccreditationBody::class, 'unique' => ['name', 'short_name'],
                'help' => 'NAAC, NBA, UGC, AICTE, NMC... "Kind" separates a grade (NAAC A++) from a statutory approval (AICTE) or a recognition (UGC). Grade options (comma separated) are offered when recording.',
                'fields' => [
                    ['name' => 'name', 'label' => 'Name', 'type' => 'text', 'required' => true, 'col' => 8],
                    ['name' => 'short_name', 'label' => 'Short name', 'type' => 'text', 'required' => true, 'col' => 4],
                    ['name' => 'kind', 'label' => 'Kind', 'type' => 'select', 'options' => AccreditationBody::KINDS, 'required' => true, 'col' => 4],
                    ['name' => 'applicable_levels', 'label' => 'Recordable at (empty = any level)', 'type' => 'multiselect', 'options' => $levels, 'col' => 8],
                    ['name' => 'has_grade', 'label' => 'Has a grade', 'type' => 'bool', 'col' => 4],
                    ['name' => 'grade_options', 'label' => 'Grade options, comma separated', 'type' => 'csv', 'col' => 8],
                    ['name' => 'has_validity', 'label' => 'Has a validity period', 'type' => 'bool', 'default' => true, 'col' => 4],
                    ['name' => 'discipline_ids', 'label' => 'Governs these disciplines (to find programmes missing an approval)', 'type' => 'multiselect', 'pivot' => 'disciplines',
                        'options' => fn () => Discipline::orderBy('title')->pluck('title', 'id')->all(), 'col' => 12],
                    ['name' => 'country', 'label' => 'Country', 'type' => 'text', 'col' => 4],
                    ['name' => 'website', 'label' => 'Website', 'type' => 'text', 'col' => 8],
                    ['name' => 'sort_order', 'label' => 'Order', 'type' => 'number', 'default' => 0, 'col' => 3],
                    ['name' => 'status', 'label' => 'Active', 'type' => 'bool', 'default' => true, 'col' => 3],
                ],
                'columns' => ['name', 'short_name', 'kind', 'levels_label'],
                'in_use' => fn ($m) => $m->records()->withTrashed()->exists() || $m->approvals()->withTrashed()->exists(),
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

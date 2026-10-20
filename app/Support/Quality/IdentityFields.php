<?php

namespace App\Support\Quality;

use App\Models\Quality\OrganisationGroup;
use Illuminate\Validation\Rule;

/** Which identity columns exist at each level, and how each one is validated. Used by the inline editor and the CSV import. */
class IdentityFields
{
    public const LEGAL_TYPES = [
        'central' => 'Central university', 'state_public' => 'State public university', 'state_private' => 'State private university', 'deemed' => 'Deemed university',
        'institution_of_national_importance' => 'Institution of national importance', 'autonomous_college' => 'Autonomous college', 'affiliated_college' => 'Affiliated college',
    ];
    public const CAMPUS_ROLES = ['main' => 'Main campus', 'off_campus' => 'Off campus', 'constituent_college' => 'Constituent college', 'study_centre' => 'Study centre'];
    public const UNIT_TYPES = ['department' => 'Department', 'school' => 'School', 'faculty' => 'Faculty', 'centre' => 'Centre'];

    public static function levels(): array
    {
        return [
            'organisation' => ['table' => 'organisations', 'key' => 'id', 'name' => 'name', 'label' => 'Organisations'],
            'campus' => ['table' => 'campuses', 'key' => 'id', 'name' => 'campus_name', 'label' => 'Campuses'],
            'department' => ['table' => 'departments', 'key' => 'id', 'name' => 'department_name', 'label' => 'Departments / Schools'],
        ];
    }

    /** column => rules for that level. Empty strings are turned into null before validation. */
    public static function rules(string $level, string|int|null $ignoreId = null): array
    {
        $t = self::levels()[$level]['table'];
        $unique = fn (string $col, int $max) => ['nullable', 'string', 'max:' . $max, 'regex:/^[A-Za-z0-9\-_\/\. ]+$/', Rule::unique($t, $col)->ignore($ignoreId)];
        return match ($level) {
            'organisation' => [
                'aishe_code' => $unique('aishe_code', 20), 'nirf_institute_id' => $unique('nirf_institute_id', 32),
                'legal_entity_type' => ['nullable', Rule::in(array_keys(self::LEGAL_TYPES))],
                'group_id' => ['nullable', Rule::exists('organisation_groups', 'id')],
                'affiliating_organisation_id' => ['nullable', 'integer', Rule::exists('organisations', 'id'), Rule::notIn([$ignoreId])],
            ],
            'campus' => [
                'campus_role' => ['nullable', Rule::in(array_keys(self::CAMPUS_ROLES))],
                'aishe_code' => $unique('aishe_code', 20), 'nirf_institute_id' => $unique('nirf_institute_id', 32),
                'inherits_org_accreditation' => ['nullable', 'boolean'],
            ],
            'department' => [
                'academic_unit_type' => ['nullable', Rule::in(array_keys(self::UNIT_TYPES))],
                'nirf_institute_id' => $unique('nirf_institute_id', 32),
            ],
        };
    }

    public static function columns(string $level): array
    {
        return array_keys(self::rules($level));
    }

    public static function groups(): array
    {
        return OrganisationGroup::orderBy('name')->pluck('name', 'id')->all();
    }
}

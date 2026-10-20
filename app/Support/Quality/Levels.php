<?php

namespace App\Support\Quality;

use App\Models\Campus;
use App\Models\Department;
use App\Models\Organisation;
use App\Models\OrganisationCourse;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\Relation;

/**
 * The four levels a ranking / accreditation can attach to. Records store the short alias
 * (organisation | campus | department | course), never a class name.
 *
 * "course" is the programme at a campus (organisation_courses), not the generic course master.
 */
class Levels
{
    public const MAP = [
        'organisation' => Organisation::class,
        'campus' => Campus::class,
        'department' => Department::class,
        'course' => OrganisationCourse::class,
    ];

    public const LABELS = ['organisation' => 'Organisation', 'campus' => 'Campus', 'department' => 'Department / School', 'course' => 'Course (programme)'];

    /** Called once at boot: lets Eloquent store aliases instead of class names. Non-enforcing, so other morph relations are untouched. */
    public static function register(): void
    {
        Relation::morphMap(self::MAP);
    }

    public static function valid(string $type): bool
    {
        return isset(self::MAP[$type]);
    }

    public static function find(string $type, $id): ?Model
    {
        return self::valid($type) ? (self::MAP[$type])::find($id) : null;
    }

    public static function typeOf(Model $m): string
    {
        return array_search(get_class($m), self::MAP, true) ?: abort(500, 'Not a quality level: ' . get_class($m));
    }

    public static function name(Model $m): string
    {
        return match (self::typeOf($m)) {
            'organisation' => $m->name,
            'campus' => $m->campus_name,
            'department' => $m->department_name,
            'course' => trim(($m->academic_unit_name ?: ($m->course?->name ?? 'Course #' . $m->id))),
        };
    }

    /** The organisation this entity belongs to (for display and permissions). */
    public static function organisationOf(Model $m): ?Organisation
    {
        return match (self::typeOf($m)) {
            'organisation' => $m,
            default => Organisation::find($m->organisation_id),
        };
    }

    /**
     * The entities above this one, nearest first: course -> department -> campus -> organisation.
     * Their records are DISPLAYED on this entity with a label ("University NAAC A++"), never copied onto it.
     *
     * @return array<int, array{0:string,1:Model}>
     */
    public static function ancestors(Model $m): array
    {
        $out = [];
        $type = self::typeOf($m);
        if ($type === 'course' && $m->department) $out[] = ['department', $m->department];
        if (in_array($type, ['course', 'department'], true) && $m->campus) $out[] = ['campus', $m->campus];
        if ($type !== 'organisation' && ($org = Organisation::find($m->organisation_id))) $out[] = ['organisation', $org];
        return $out;
    }

    /** Short context line: "Course · Amity University · Noida". */
    public static function describe(Model $m): string
    {
        $type = self::typeOf($m);
        $parts = [self::LABELS[$type]];
        if ($type !== 'organisation' && ($org = self::organisationOf($m))) $parts[] = $org->name;
        if ($type === 'department' && $m->campus) $parts[] = $m->campus->campus_name;
        if ($type === 'course' && $m->campus) $parts[] = $m->campus->campus_name;
        return implode(' · ', array_filter($parts));
    }

    /** Where to edit this entity in the existing admin. */
    public static function editUrl(Model $m): ?string
    {
        return match (self::typeOf($m)) {
            'organisation' => route('admin.organisations.edit', $m),
            'campus' => route('admin.organisations.campuses.edit', [$m->organisation_id, $m->id]),
            'department' => route('admin.departments.edit', $m->id),
            'course' => route('admin.organisation-courses.edit', $m->id),
        };
    }
}

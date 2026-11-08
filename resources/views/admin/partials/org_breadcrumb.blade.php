{{--
    Organisation › Campus › Department › Course breadcrumb, shown at the top of the organisation, campus, department and
    course pages, with links down to the next levels. Pass what the page has; the missing parents are worked out:
      @include('admin.partials.org_breadcrumb', ['bcCourse' => $organisationCourse])
      @include('admin.partials.org_breadcrumb', ['bcCampus' => $campus, 'bcHere' => 'campus'])
    bcHere: organisation | campuses | campus | departments | department | courses | course (the page being shown).
--}}
@php
    $bcCourse = $bcCourse ?? null;
    $bcDept = $bcDept ?? ($bcCourse?->department);
    $bcCampus = $bcCampus ?? ($bcDept?->campus ?? $bcCourse?->campus);
    $bcOrg = $bcOrg ?? ($bcCampus?->organisation ?? $bcDept?->organisation ?? $bcCourse?->organisation);
    $bcHere = $bcHere ?? ($bcCourse ? 'course' : ($bcDept ? 'department' : ($bcCampus ? 'campus' : 'organisation')));
    $bcType = (int) ($bcOrg->organisation_type_id ?? 0);
    $bcHasCampuses = $bcType !== 9;            // e-learning platforms have no campuses
    $bcHasDepartments = $bcType !== 4;         // schools list their classes as courses, without departments
    $bcFilter = array_filter(['organisation_id' => $bcOrg?->id, 'campus_id' => $bcCampus?->id, 'department_id' => $bcDept?->id]);
    $bcCourseName = $bcCourse ? ($bcCourse->course->name ?? $bcCourse->academic_unit_name ?? 'Course') : null;
    $bcCrumbs = [];
    $bcAdd = function (string $label, ?string $url, bool $current = false, ?string $icon = null) use (&$bcCrumbs) {
        $bcCrumbs[] = compact('label', 'url', 'current', 'icon');
    };
    $bcAdd('Organisations', route('admin.organisations.index'), false, 'fas fa-sitemap');
    if ($bcOrg) {
        $bcAdd($bcOrg->name, route('admin.organisations.edit', $bcOrg->id), $bcHere === 'organisation');
        if ($bcHasCampuses && ($bcCampus || in_array($bcHere, ['campuses', 'campus'], true))) {
            $bcAdd('Campuses', route('admin.organisations.campuses.index', $bcOrg->id), $bcHere === 'campuses');
        }
        if ($bcCampus) {
            $bcAdd($bcCampus->campus_name, route('admin.organisations.campuses.edit', [$bcOrg->id, $bcCampus->id]), $bcHere === 'campus');
        }
        if ($bcDept || $bcHere === 'departments') {
            $bcAdd('Departments', route('admin.departments.index', array_filter(['organisation_id' => $bcOrg->id, 'campus_id' => $bcCampus?->id])), $bcHere === 'departments');
        }
        if ($bcDept) {
            $bcAdd($bcDept->department_name, route('admin.departments.edit', $bcDept->id), $bcHere === 'department');
        }
        if ($bcCourse || $bcHere === 'courses') {
            $bcAdd('Courses', route('admin.organisation-courses.index', $bcFilter), $bcHere === 'courses');
        }
        if ($bcCourse) {
            $bcAdd($bcCourseName, route('admin.organisation-courses.edit', $bcCourse->id), true);
        }
    }
    // links down to the next levels of the page being shown
    $bcNext = [];
    if ($bcOrg && in_array($bcHere, ['organisation', 'campus', 'department'], true)) {
        if ($bcHere === 'organisation' && $bcHasCampuses) {
            $bcNext[] = ['Campuses', route('admin.organisations.campuses.index', $bcOrg->id), \App\Models\Campus::where('organisation_id', $bcOrg->id)->count(), 'fas fa-building'];
        }
        if ($bcHere !== 'department' && $bcHasDepartments) {
            $q = \App\Models\Department::where('organisation_id', $bcOrg->id);
            if ($bcCampus) $q->where('campus_id', $bcCampus->id);
            $bcNext[] = ['Departments', route('admin.departments.index', array_filter(['organisation_id' => $bcOrg->id, 'campus_id' => $bcCampus?->id])), $q->count(), 'fas fa-layer-group'];
        }
        $q = \App\Models\OrganisationCourse::where('organisation_id', $bcOrg->id);
        if ($bcCampus) $q->where('campus_id', $bcCampus->id);
        if ($bcDept) $q->where('department_id', $bcDept->id);
        $bcNext[] = ['Courses', route('admin.organisation-courses.index', $bcFilter), $q->count(), 'fas fa-graduation-cap'];
    }
@endphp
<div class="d-flex flex-wrap align-items-center justify-content-between gap-2 mb-3 px-3 py-2 bg-white border rounded-3 org-breadcrumb">
    <nav aria-label="breadcrumb" class="flex-grow-1" style="min-width:0">
        <ol class="breadcrumb mb-0 flex-wrap small">
            @foreach($bcCrumbs as $c)
                @if($c['current'])
                    <li class="breadcrumb-item active fw-bold text-dark text-break" aria-current="page">@if($c['icon'])<i class="{{ $c['icon'] }} me-1"></i>@endif{{ $c['label'] }}</li>
                @else
                    <li class="breadcrumb-item text-break"><a href="{{ $c['url'] }}" class="text-decoration-none">@if($c['icon'])<i class="{{ $c['icon'] }} me-1"></i>@endif{{ $c['label'] }}</a></li>
                @endif
            @endforeach
        </ol>
    </nav>
    @if($bcNext)
        <div class="d-flex flex-wrap gap-1">
            @foreach($bcNext as [$label, $url, $count, $icon])
                <a href="{{ $url }}" class="btn btn-sm btn-outline-secondary"><i class="{{ $icon }} me-1"></i>{{ $label }} <span class="badge bg-light text-dark border ms-1">{{ $count }}</span></a>
            @endforeach
        </div>
    @endif
</div>

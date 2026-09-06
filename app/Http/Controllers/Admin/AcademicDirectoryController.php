<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Organisation;
use App\Models\Campus;
use App\Models\Department;
use App\Models\OrganisationCourse;
use App\Models\OrganisationType;
use App\Models\Course;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Yajra\DataTables\DataTables;

class AcademicDirectoryController extends Controller
{
    /**
     * Display the Academic Directory Cockpit & Reports Master Page.
     */
    public function index(Request $request)
    {
        $totalOrgs = Organisation::count();
        $totalCampuses = Campus::count();
        $totalDepartments = Department::count();
        $totalCourses = OrganisationCourse::count();

        $organisationTypes = OrganisationType::where('status', true)->get();
        $organisationsList = Organisation::select('id', 'name', 'organisation_type_id')->orderBy('sort_order', 'asc')->orderBy('name', 'asc')->get();
        $campusesList = Campus::select('id', 'campus_name', 'city', 'organisation_id')->orderBy('sort_order', 'asc')->orderBy('campus_name', 'asc')->get();
        $departmentsList = Department::select('id', 'department_name', 'department_code', 'campus_id', 'organisation_id')->orderBy('sort_order', 'asc')->orderBy('department_name', 'asc')->get();
        
        // Distinct master courses offered in OrganisationCourse
        $coursesList = Course::whereIn('id', OrganisationCourse::whereNotNull('course_id')->select('course_id'))
            ->orderBy('sort_order', 'asc')
            ->orderBy('name', 'asc')
            ->get(['id', 'name']);

        return view('admin.academic_directory.index', compact(
            'totalOrgs',
            'totalCampuses',
            'totalDepartments',
            'totalCourses',
            'organisationTypes',
            'organisationsList',
            'campusesList',
            'departmentsList',
            'coursesList'
        ));
    }

    /**
     * AJAX Endpoint: Real-time dynamic counts for all 4 cards and cascading dropdown options.
     */
    public function getFilterCounts(Request $request)
    {
        // 1. Dynamic Organisation Count
        $orgQuery = Organisation::query();
        if ($request->filled('organisation_type_id')) {
            $orgQuery->where('organisation_type_id', $request->organisation_type_id);
        }
        if ($request->filled('organisation_id')) {
            $orgQuery->where('id', $request->organisation_id);
        }
        if ($request->filled('campus_id')) {
            $orgQuery->whereHas('campuses', fn($q) => $q->where('id', $request->campus_id));
        }
        if ($request->filled('department_id')) {
            $orgQuery->whereHas('departments', fn($q) => $q->where('id', $request->department_id));
        }
        if ($request->filled('course_id')) {
            $orgQuery->whereHas('courses', fn($q) => $q->where('course_id', $request->course_id));
        }
        $orgsCount = $orgQuery->count();

        // 2. Dynamic Campuses Count
        $campusQuery = Campus::query();
        if ($request->filled('organisation_type_id')) {
            $campusQuery->whereHas('organisation', fn($q) => $q->where('organisation_type_id', $request->organisation_type_id));
        }
        if ($request->filled('organisation_id')) {
            $campusQuery->where('organisation_id', $request->organisation_id);
        }
        if ($request->filled('campus_id')) {
            $campusQuery->where('id', $request->campus_id);
        }
        if ($request->filled('department_id')) {
            $campusQuery->whereHas('departments', fn($q) => $q->where('id', $request->department_id));
        }
        if ($request->filled('course_id')) {
            $campusQuery->whereHas('courses', fn($q) => $q->where('course_id', $request->course_id));
        }
        $campusesCount = $campusQuery->count();

        // 3. Dynamic Departments Count
        $deptQuery = Department::query();
        if ($request->filled('organisation_type_id')) {
            $deptQuery->whereHas('organisation', fn($q) => $q->where('organisation_type_id', $request->organisation_type_id));
        }
        if ($request->filled('organisation_id')) {
            $deptQuery->where('organisation_id', $request->organisation_id);
        }
        if ($request->filled('campus_id')) {
            $deptQuery->where('campus_id', $request->campus_id);
        }
        if ($request->filled('department_id')) {
            $deptQuery->where('id', $request->department_id);
        }
        if ($request->filled('course_id')) {
            $deptQuery->whereHas('courses', fn($q) => $q->where('course_id', $request->course_id));
        }
        $departmentsCount = $deptQuery->count();

        // 4. Dynamic Courses Count
        $courseQuery = OrganisationCourse::query();
        if ($request->filled('organisation_type_id')) {
            $courseQuery->whereHas('organisation', fn($q) => $q->where('organisation_type_id', $request->organisation_type_id));
        }
        if ($request->filled('organisation_id')) {
            $courseQuery->where('organisation_id', $request->organisation_id);
        }
        if ($request->filled('campus_id')) {
            $courseQuery->where('campus_id', $request->campus_id);
        }
        if ($request->filled('department_id')) {
            $courseQuery->where('department_id', $request->department_id);
        }
        if ($request->filled('course_id')) {
            $courseQuery->where('course_id', $request->course_id);
        }
        $coursesCount = $courseQuery->count();

        // 5. Cascading Dropdown Options
        $cascades = [];

        // Cascading Organisations
        $orgsQuery = Organisation::query()->select('id', 'name');
        if ($request->filled('organisation_type_id')) {
            $orgsQuery->where('organisation_type_id', $request->organisation_type_id);
        }
        $cascades['organisations'] = $orgsQuery->orderBy('sort_order', 'asc')->orderBy('name', 'asc')->get();

        // Cascading Campuses (if org or org type selected)
        $cQuery = Campus::query()->select('id', 'campus_name', 'city');
        if ($request->filled('organisation_id')) {
            $cQuery->where('organisation_id', $request->organisation_id);
        } elseif ($request->filled('organisation_type_id')) {
            $cQuery->whereHas('organisation', fn($q) => $q->where('organisation_type_id', $request->organisation_type_id));
        }
        $cascades['campuses'] = $cQuery->orderBy('sort_order', 'asc')->orderBy('campus_name', 'asc')->get();

        // Cascading Departments (if campus or org selected)
        $dQuery = Department::query()->select('id', 'department_name', 'department_code');
        if ($request->filled('campus_id')) {
            $dQuery->where('campus_id', $request->campus_id);
        } elseif ($request->filled('organisation_id')) {
            $dQuery->where('organisation_id', $request->organisation_id);
        } elseif ($request->filled('organisation_type_id')) {
            $dQuery->whereHas('organisation', fn($q) => $q->where('organisation_type_id', $request->organisation_type_id));
        }
        $cascades['departments'] = $dQuery->orderBy('sort_order', 'asc')->orderBy('department_name', 'asc')->get();

        // Cascading Courses (distinct master courses in filtered scope)
        $ocCourseQuery = OrganisationCourse::whereNotNull('course_id');
        if ($request->filled('department_id')) {
            $ocCourseQuery->where('department_id', $request->department_id);
        } elseif ($request->filled('campus_id')) {
            $ocCourseQuery->where('campus_id', $request->campus_id);
        } elseif ($request->filled('organisation_id')) {
            $ocCourseQuery->where('organisation_id', $request->organisation_id);
        } elseif ($request->filled('organisation_type_id')) {
            $ocCourseQuery->whereHas('organisation', fn($q) => $q->where('organisation_type_id', $request->organisation_type_id));
        }
        $courseIds = $ocCourseQuery->distinct()->pluck('course_id');
        $cascades['courses'] = Course::whereIn('id', $courseIds)->select('id', 'name')->orderBy('sort_order', 'asc')->orderBy('name', 'asc')->get();

        return response()->json([
            'status' => 1,
            'counts' => [
                'organisations' => $orgsCount,
                'campuses'      => $campusesCount,
                'departments'   => $departmentsCount,
                'courses'       => $coursesCount,
            ],
            'cascades' => $cascades
        ]);
    }

    /**
     * AJAX DataTables endpoint for Organisations.
     */
    public function getOrganisationsData(Request $request)
    {
        $query = Organisation::with(['organisationType'])->withCount(['campuses', 'departments', 'courses']);

        if ($request->filled('organisation_type_id')) {
            $query->where('organisation_type_id', $request->organisation_type_id);
        }

        if ($request->filled('organisation_id')) {
            $query->where('id', $request->organisation_id);
        }

        if ($request->filled('campus_id')) {
            $query->whereHas('campuses', fn($q) => $q->where('id', $request->campus_id));
        }

        if ($request->filled('department_id')) {
            $query->whereHas('departments', fn($q) => $q->where('id', $request->department_id));
        }

        if ($request->filled('course_id')) {
            $query->whereHas('courses', fn($q) => $q->where('course_id', $request->course_id));
        }

        $query->orderBy('sort_order', 'asc')->orderBy('name', 'asc');

        return DataTables::of($query)
            ->addIndexColumn()
            ->addColumn('sort_order_html', function ($row) {
                $val = $row->sort_order ?? 1;
                return "<input type='number' class='form-control form-control-sm dir-sort-input text-center' data-type='organisation' data-id='{$row->id}' value='{$val}' min='0' style='width: 60px; height: 28px; font-weight: 600; font-size: 0.82rem;'>";
            })
            ->addColumn('name_html', function ($row) {
                $name = e($row->name);
                $topBadge = $row->is_top ? '<span class="badge-pill-gold"><i class="fas fa-star me-1"></i>Top</span>' : '';
                $brandBadge = $row->brand_type ? '<span class="badge-pill-neutral">' . e($row->brand_type) . '</span>' : '';
                $code = $row->organisation_id_number ? '<span class="code-pill">#' . e($row->organisation_id_number) . '</span>' : '';
                $type = $row->organisationType ? $row->organisationType->title : 'Organisation';

                $gradients = [
                    'linear-gradient(135deg, #4f46e5 0%, #3730a3 100%)',
                    'linear-gradient(135deg, #0284c7 0%, #0369a1 100%)',
                    'linear-gradient(135deg, #0d9488 0%, #115e59 100%)',
                    'linear-gradient(135deg, #7c3aed 0%, #5b21b6 100%)',
                    'linear-gradient(135deg, #ea580c 0%, #c2410c 100%)',
                ];
                $bgGrad = $gradients[$row->id % count($gradients)];
                $letter = strtoupper(substr($row->name, 0, 1));

                return "
                <div class='d-flex align-items-center' style='gap: 12px;'>
                    <div class='org-avatar-box' style='background: {$bgGrad};'>
                        {$letter}
                    </div>
                    <div class='d-flex flex-column'>
                        <div class='d-flex align-items-center flex-wrap' style='gap: 6px;'>
                            <a href='javascript:void(0)' class='entity-title view-quick-drawer' data-type='organisation' data-id='{$row->id}'>{$name}</a>
                            {$topBadge}
                            {$brandBadge}
                        </div>
                        <div class='d-flex align-items-center flex-wrap mt-1' style='gap: 6px;'>
                            {$code}
                            <span class='badge-pill-indigo'>{$type}</span>
                        </div>
                    </div>
                </div>";
            })
            ->addColumn('location_html', function ($row) {
                $location = $row->head_office_location ?? $row->central_authority ?? null;
                if ($location) {
                    return "<div class='d-flex align-items-center text-secondary' style='font-size: 0.84rem;'>
                        <i class='fas fa-map-marker-alt text-danger me-1.5' style='opacity: 0.85;'></i>
                        <span class='fw-medium text-dark'>" . e($location) . "</span>
                    </div>";
                }
                return "<span class='text-muted small'>—</span>";
            })
            ->addColumn('hierarchy_chips', function ($row) {
                $campuses = $row->campuses_count;
                $depts = $row->departments_count;
                $courses = $row->courses_count;

                $cChip = "<a href='javascript:void(0)' class='stat-chip chip-cyan switch-to-tab' data-tab='campuses' data-org-id='{$row->id}' title='View all campuses of {$row->name}'><i class='fas fa-city me-1'></i>{$campuses} Campuses</a>";
                $dChip = "<a href='javascript:void(0)' class='stat-chip chip-indigo switch-to-tab' data-tab='departments' data-org-id='{$row->id}' title='View all departments of {$row->name}'><i class='fas fa-building me-1'></i>{$depts} Depts</a>";
                $crsChip = "<a href='javascript:void(0)' class='stat-chip chip-emerald switch-to-tab' data-tab='courses' data-org-id='{$row->id}' title='View all courses of {$row->name}'><i class='fas fa-graduation-cap me-1'></i>{$courses} Courses</a>";

                return "<div class='d-flex flex-wrap align-items-center' style='gap: 6px;'>{$cChip}{$dChip}{$crsChip}</div>";
            })
            ->addColumn('status_html', function ($row) {
                if ($row->status) {
                    return "<span class='status-pill status-active'><span class='status-dot'></span>Published</span>";
                }
                return "<span class='status-pill status-draft'><span class='status-dot'></span>Draft</span>";
            })
            ->addColumn('action', function ($row) {
                $quickViewBtn = "<button type='button' class='action-btn action-view view-quick-drawer' data-type='organisation' data-id='{$row->id}' title='Quick Inspect'><i class='fas fa-eye'></i></button>";
                $campusesUrl = route('admin.organisations.campuses.index', $row->id);
                $campusesBtn = "<a href='{$campusesUrl}' class='action-btn action-campus' title='Manage Campuses'><i class='fas fa-city'></i></a>";
                $editUrl = route('admin.organisations.edit', $row->id);
                $editBtn = "<a href='{$editUrl}' class='action-btn action-edit' title='Edit Organisation'><i class='fas fa-pen'></i></a>";

                return "<div class='d-flex align-items-center justify-content-end' style='gap: 6px;'>{$quickViewBtn}{$campusesBtn}{$editBtn}</div>";
            })
            ->rawColumns(['sort_order_html', 'name_html', 'location_html', 'hierarchy_chips', 'status_html', 'action'])
            ->make(true);
    }

    /**
     * AJAX DataTables endpoint for Campuses.
     */
    public function getCampusesData(Request $request)
    {
        $query = Campus::with(['organisation.organisationType', 'departments'])->withCount(['departments', 'courses']);

        if ($request->filled('organisation_type_id')) {
            $query->whereHas('organisation', fn($q) => $q->where('organisation_type_id', $request->organisation_type_id));
        }

        if ($request->filled('organisation_id')) {
            $query->where('organisation_id', $request->organisation_id);
        }

        if ($request->filled('campus_id')) {
            $query->where('id', $request->campus_id);
        }

        if ($request->filled('department_id')) {
            $query->whereHas('departments', fn($q) => $q->where('id', $request->department_id));
        }

        if ($request->filled('course_id')) {
            $query->whereHas('courses', fn($q) => $q->where('course_id', $request->course_id));
        }

        $query->orderBy('sort_order', 'asc')->orderBy('campus_name', 'asc');

        return DataTables::of($query)
            ->addIndexColumn()
            ->addColumn('sort_order_html', function ($row) {
                $val = $row->sort_order ?? 1;
                return "<input type='number' class='form-control form-control-sm dir-sort-input text-center' data-type='campus' data-id='{$row->id}' value='{$val}' min='0' style='width: 60px; height: 28px; font-weight: 600; font-size: 0.82rem;'>";
            })
            ->addColumn('campus_name_html', function ($row) {
                $name = e($row->campus_name);
                $code = $row->campus_code ? '<span class="code-pill me-1">#' . e($row->campus_code) . '</span>' : '';
                return "
                <div class='d-flex align-items-center' style='gap: 12px;'>
                    <div class='org-avatar-box' style='background: linear-gradient(135deg, #0284c7 0%, #0369a1 100%);'>
                        <i class='fas fa-city' style='font-size: 1rem;'></i>
                    </div>
                    <div>
                        <div class='d-flex align-items-center flex-wrap' style='gap: 6px;'>
                            <a href='javascript:void(0)' class='entity-title view-quick-drawer' data-type='campus' data-id='{$row->id}'>{$name}</a>
                        </div>
                        <div class='mt-1'>{$code}</div>
                    </div>
                </div>";
            })
            ->addColumn('organisation_html', function ($row) {
                if (!$row->organisation) return '<span class="text-muted">—</span>';
                $orgName = e($row->organisation->name);
                $orgType = $row->organisation->organisationType ? $row->organisation->organisationType->title : 'University';
                return "
                <div>
                    <a href='javascript:void(0)' class='fw-bold text-dark text-decoration-none hover-primary filter-by-org' data-org-id='{$row->organisation_id}' style='font-size: 0.88rem;'>{$orgName}</a>
                    <div class='text-muted small mt-0.5'><span class='badge-pill-neutral'>{$orgType}</span></div>
                </div>";
            })
            ->addColumn('location_html', function ($row) {
                $city = $row->city ?: 'N/A';
                $state = $row->state ? ', ' . e($row->state) : '';
                return "<div class='d-flex align-items-center text-secondary' style='font-size: 0.84rem;'>
                    <i class='fas fa-map-marker-alt text-danger me-1.5' style='opacity: 0.85;'></i>
                    <span class='fw-medium text-dark'>{$city}{$state}</span>
                </div>";
            })
            ->addColumn('facilities_badges', function ($row) {
                $facilities = [];
                if (!empty($row->facilities)) {
                    $facilities = is_array($row->facilities) ? $row->facilities : (json_decode($row->facilities, true) ?: explode(',', $row->facilities));
                }
                if (empty($facilities)) {
                    return '<span class="text-muted small">None listed</span>';
                }
                $html = "<div class='d-flex flex-wrap' style='gap: 4px; max-width: 220px;'>";
                $count = 0;
                foreach ($facilities as $fac) {
                    if ($count >= 2) break;
                    $facName = is_string($fac) ? $fac : (isset($fac['name']) ? $fac['name'] : 'Facility');
                    $html .= "<span class='badge-pill-neutral'>" . e(trim($facName)) . "</span>";
                    $count++;
                }
                if (count($facilities) > 2) {
                    $remaining = count($facilities) - 2;
                    $html .= "<span class='badge-pill-neutral' style='background: #f1f5f9; color: #64748b;'>+{$remaining}</span>";
                }
                $html .= "</div>";
                return $html;
            })
            ->addColumn('hierarchy_chips', function ($row) {
                $depts = $row->departments_count;
                $courses = $row->courses_count;

                $dChip = "<a href='javascript:void(0)' class='stat-chip chip-indigo switch-to-tab' data-tab='departments' data-org-id='{$row->organisation_id}' data-campus-id='{$row->id}' title='View departments of {$row->campus_name}'><i class='fas fa-building me-1'></i>{$depts} Depts</a>";
                $crsChip = "<a href='javascript:void(0)' class='stat-chip chip-emerald switch-to-tab' data-tab='courses' data-org-id='{$row->organisation_id}' data-campus-id='{$row->id}' title='View courses in {$row->campus_name}'><i class='fas fa-graduation-cap me-1'></i>{$courses} Courses</a>";

                return "<div class='d-flex flex-wrap align-items-center' style='gap: 6px;'>{$dChip}{$crsChip}</div>";
            })
            ->addColumn('verification_badge', function ($row) {
                if ($row->verification_status) {
                    return "<span class='status-pill status-active'><i class='fas fa-check-circle me-1'></i>Verified</span>";
                }
                return "<span class='status-pill status-draft'><i class='fas fa-clock me-1'></i>Pending</span>";
            })
            ->addColumn('action', function ($row) {
                $quickViewBtn = "<button type='button' class='action-btn action-view view-quick-drawer' data-type='campus' data-id='{$row->id}' title='Quick Inspect'><i class='fas fa-eye'></i></button>";
                $addDeptUrl = route('admin.departments.create', ['organisation_id' => $row->organisation_id, 'campus_id' => $row->id]);
                $addDeptBtn = "<a href='{$addDeptUrl}' class='action-btn action-add' title='Add Department'><i class='fas fa-plus'></i></a>";
                $editUrl = route('admin.organisations.campuses.edit', [$row->organisation_id, $row->id]);
                $editBtn = "<a href='{$editUrl}' class='action-btn action-edit' title='Edit Campus'><i class='fas fa-pen'></i></a>";

                return "<div class='d-flex align-items-center justify-content-end' style='gap: 6px;'>{$quickViewBtn}{$addDeptBtn}{$editBtn}</div>";
            })
            ->rawColumns(['sort_order_html', 'campus_name_html', 'organisation_html', 'location_html', 'facilities_badges', 'hierarchy_chips', 'verification_badge', 'action'])
            ->make(true);
    }

    /**
     * AJAX DataTables endpoint for Academic Departments.
     */
    public function getDepartmentsData(Request $request)
    {
        $query = Department::with(['organisation.organisationType', 'campus'])->withCount('courses');

        if ($request->filled('organisation_type_id')) {
            $query->whereHas('organisation', fn($q) => $q->where('organisation_type_id', $request->organisation_type_id));
        }

        if ($request->filled('organisation_id')) {
            $query->where('organisation_id', $request->organisation_id);
        }

        if ($request->filled('campus_id')) {
            $query->where('campus_id', $request->campus_id);
        }

        if ($request->filled('department_id')) {
            $query->where('id', $request->department_id);
        }

        if ($request->filled('course_id')) {
            $query->whereHas('courses', fn($q) => $q->where('course_id', $request->course_id));
        }

        $query->orderBy('sort_order', 'asc')->orderBy('department_name', 'asc');

        return DataTables::of($query)
            ->addIndexColumn()
            ->addColumn('sort_order_html', function ($row) {
                $val = $row->sort_order ?? 1;
                return "<input type='number' class='form-control form-control-sm dir-sort-input text-center' data-type='department' data-id='{$row->id}' value='{$val}' min='0' style='width: 60px; height: 28px; font-weight: 600; font-size: 0.82rem;'>";
            })
            ->addColumn('department_name_html', function ($row) {
                $name = e($row->department_name);
                $code = $row->department_code ? '<span class="code-pill">#' . e($row->department_code) . '</span>' : '';
                return "
                <div class='d-flex align-items-center' style='gap: 12px;'>
                    <div class='org-avatar-box' style='background: linear-gradient(135deg, #6366f1 0%, #4338ca 100%);'>
                        <i class='fas fa-building' style='font-size: 1rem;'></i>
                    </div>
                    <div>
                        <div class='d-flex align-items-center flex-wrap' style='gap: 6px;'>
                            <a href='javascript:void(0)' class='entity-title view-quick-drawer' data-type='department' data-id='{$row->id}'>{$name}</a>
                        </div>
                        <div class='mt-1'>{$code}</div>
                    </div>
                </div>";
            })
            ->addColumn('hierarchy_html', function ($row) {
                $orgName = $row->organisation ? e($row->organisation->name) : '—';
                $campusName = $row->campus ? e($row->campus->campus_name) : 'All Campuses';
                $orgId = $row->organisation_id;
                return "
                <div>
                    <a href='javascript:void(0)' class='fw-bold text-dark text-decoration-none hover-primary filter-by-org' data-org-id='{$orgId}' style='font-size: 0.88rem;'>{$orgName}</a>
                    <div class='text-muted small mt-0.5'><i class='fas fa-city text-info me-1' style='font-size: 0.72rem;'></i>{$campusName}</div>
                </div>";
            })
            ->addColumn('discipline_badge', function ($row) {
                $disc = $row->discipline_area ?: 'General';
                return "<span class='badge-pill-indigo'><i class='fas fa-tag me-1'></i>" . e($disc) . "</span>";
            })
            ->addColumn('hod_info', function ($row) {
                $hod = $row->head_of_department_name ?: 'Not Assigned';
                $email = $row->hod_email ? "<div class='text-muted' style='font-size: 0.76rem;'><i class='far fa-envelope me-1'></i>{$row->hod_email}</div>" : '';
                return "<div class='fw-semibold text-dark' style='font-size: 0.84rem;'>{$hod}</div>{$email}";
            })
            ->addColumn('faculty_labs', function ($row) {
                $fCount = $row->faculty_count ?? 0;
                $lCount = $row->department_labs_count ?? 0;
                return "<div class='text-secondary' style='font-size: 0.84rem;'><span class='fw-bold text-dark'>{$fCount}</span> Faculty &middot; <span class='fw-bold text-dark'>{$lCount}</span> Labs</div>";
            })
            ->addColumn('courses_count', function ($row) {
                $cCount = $row->courses_count;
                $orgId = $row->organisation_id;
                $campusId = $row->campus_id;
                return "<a href='javascript:void(0)' class='stat-chip chip-emerald switch-to-tab' data-tab='courses' data-org-id='{$orgId}' data-campus-id='{$campusId}' data-dept-id='{$row->id}'><i class='fas fa-graduation-cap me-1'></i>{$cCount} Courses</a>";
            })
            ->addColumn('action', function ($row) {
                $quickViewBtn = "<button type='button' class='action-btn action-view view-quick-drawer' data-type='department' data-id='{$row->id}' title='Quick Inspect'><i class='fas fa-eye'></i></button>";
                $addCourseUrl = route('admin.organisation-courses.create', ['organisation_id' => $row->organisation_id, 'campus_id' => $row->campus_id, 'department_id' => $row->id]);
                $addCourseBtn = "<a href='{$addCourseUrl}' class='action-btn action-add' title='Add Offered Course'><i class='fas fa-plus'></i></a>";
                $editUrl = route('admin.departments.edit', $row->id);
                $editBtn = "<a href='{$editUrl}' class='action-btn action-edit' title='Edit Department'><i class='fas fa-pen'></i></a>";

                return "<div class='d-flex align-items-center justify-content-end' style='gap: 6px;'>{$quickViewBtn}{$addCourseBtn}{$editBtn}</div>";
            })
            ->rawColumns(['sort_order_html', 'department_name_html', 'hierarchy_html', 'discipline_badge', 'hod_info', 'faculty_labs', 'courses_count', 'action'])
            ->make(true);
    }

    /**
     * AJAX DataTables endpoint for Offered Courses.
     */
    public function getCoursesData(Request $request)
    {
        $query = OrganisationCourse::with(['course.programLevel', 'organisation.organisationType', 'campus', 'department']);

        if ($request->filled('organisation_type_id')) {
            $query->whereHas('organisation', fn($q) => $q->where('organisation_type_id', $request->organisation_type_id));
        }

        if ($request->filled('organisation_id')) {
            $query->where('organisation_id', $request->organisation_id);
        }

        if ($request->filled('campus_id')) {
            $query->where('campus_id', $request->campus_id);
        }

        if ($request->filled('department_id')) {
            $query->where('department_id', $request->department_id);
        }

        if ($request->filled('course_id')) {
            $query->where('course_id', $request->course_id);
        }

        $query->orderBy('sort_order', 'asc');

        return DataTables::of($query)
            ->addIndexColumn()
            ->addColumn('sort_order_html', function ($row) {
                $val = $row->sort_order ?? 1;
                return "<input type='number' class='form-control form-control-sm dir-sort-input text-center' data-type='course' data-id='{$row->id}' value='{$val}' min='0' style='width: 60px; height: 28px; font-weight: 600; font-size: 0.82rem;'>";
            })
            ->addColumn('course_name_html', function ($row) {
                $name = $row->course ? e($row->course->name) : 'Offered Program';
                $level = ($row->course && $row->course->programLevel) ? $row->course->programLevel->name : 'General';
                $intake = $row->intake_capacity ? "<span class='badge-pill-neutral'>{$row->intake_capacity} Seats</span>" : '';

                return "
                <div class='d-flex align-items-center' style='gap: 12px;'>
                    <div class='org-avatar-box' style='background: linear-gradient(135deg, #10b981 0%, #047857 100%);'>
                        <i class='fas fa-graduation-cap' style='font-size: 1rem;'></i>
                    </div>
                    <div>
                        <div class='d-flex align-items-center flex-wrap' style='gap: 6px;'>
                            <a href='javascript:void(0)' class='entity-title view-quick-drawer' data-type='course' data-id='{$row->id}'>{$name}</a>
                            {$intake}
                        </div>
                        <div class='mt-1'>
                            <span class='badge-pill-indigo'>{$level}</span>
                        </div>
                    </div>
                </div>";
            })
            ->addColumn('hierarchy_html', function ($row) {
                $orgName = $row->organisation ? e($row->organisation->name) : '—';
                $campusName = $row->campus ? e($row->campus->campus_name) : 'All Campuses';
                $deptName = $row->department ? e($row->department->department_name) : 'Main Department';
                $orgId = $row->organisation_id;

                return "
                <div>
                    <a href='javascript:void(0)' class='fw-bold text-dark text-decoration-none hover-primary filter-by-org' data-org-id='{$orgId}' style='font-size: 0.88rem;'>{$orgName}</a>
                    <div class='text-muted small mt-0.5'><i class='fas fa-city text-info me-1' style='font-size: 0.72rem;'></i>{$campusName} &middot; <span class='text-secondary'>{$deptName}</span></div>
                </div>";
            })
            ->addColumn('mode_duration', function ($row) {
                $type = $row->program_type ?: 'Full-time';
                $dur = $row->duration ?: 'N/A';
                return "<div><span class='badge-pill-neutral'>{$type}</span><div class='text-muted small mt-0.5'>{$dur}</div></div>";
            })
            ->addColumn('fees_html', function ($row) {
                if ($row->total_fees) {
                    return "<span class='fw-bold text-dark' style='font-size: 0.95rem;'>₹" . number_format($row->total_fees) . "</span>";
                } elseif ($row->fee) {
                    $fees = $row->fee;
                    $formatted = is_numeric($fees) ? '₹' . number_format($fees) : e($fees);
                    return "<span class='fw-bold text-dark' style='font-size: 0.95rem;'>{$formatted}</span>";
                }
                return '<span class="text-muted small">Not Disclosed</span>';
            })
            ->addColumn('status_html', function ($row) {
                if ($row->status) {
                    return "<span class='status-pill status-active'><span class='status-dot'></span>Active</span>";
                }
                return "<span class='status-pill status-draft'><span class='status-dot'></span>Inactive</span>";
            })
            ->addColumn('action', function ($row) {
                $quickViewBtn = "<button type='button' class='action-btn action-view view-quick-drawer' data-type='course' data-id='{$row->id}' title='Quick Inspect'><i class='fas fa-eye'></i></button>";
                $editUrl = route('admin.organisation-courses.edit', ['organisation_course' => $row->id, 'organisation_id' => $row->organisation_id]);
                $editBtn = "<a href='{$editUrl}' class='action-btn action-edit' title='Edit Course'><i class='fas fa-pen'></i></a>";

                return "<div class='d-flex align-items-center justify-content-end' style='gap: 6px;'>{$quickViewBtn}{$editBtn}</div>";
            })
            ->rawColumns(['sort_order_html', 'course_name_html', 'hierarchy_html', 'mode_duration', 'fees_html', 'status_html', 'action'])
            ->make(true);
    }

    /**
     * AJAX endpoint to update sort_order inline.
     */
    public function updateSortOrder(Request $request)
    {
        $request->validate([
            'type' => 'required|in:organisation,campus,department,course',
            'id' => 'required',
            'sort_order' => 'required|integer',
        ]);

        $type = $request->type;
        $id = $request->id;
        $sortOrder = (int) $request->sort_order;

        if ($type === 'organisation') {
            Organisation::where('id', $id)->update(['sort_order' => $sortOrder]);
        } elseif ($type === 'campus') {
            Campus::where('id', $id)->update(['sort_order' => $sortOrder]);
        } elseif ($type === 'department') {
            Department::where('id', $id)->update(['sort_order' => $sortOrder]);
        } elseif ($type === 'course') {
            OrganisationCourse::where('id', $id)->update(['sort_order' => $sortOrder]);
        }

        return response()->json([
            'status' => 1,
            'message' => 'Sort order updated successfully.'
        ]);
    }

    /**
     * Fetch cascading options for filters.
     */
    public function getCascadingOptions(Request $request)
    {
        return $this->getFilterCounts($request);
    }

    /**
     * Render quick-view drawer modal partial.
     */
    public function getQuickView(Request $request, $type, $id)
    {
        if ($type === 'organisation') {
            $data = Organisation::with(['organisationType', 'campuses.departments', 'courses.course'])->findOrFail($id);
            return view('admin.academic_directory.partials.quick_view_organisation', compact('data'));
        } elseif ($type === 'campus') {
            $data = Campus::with(['organisation.organisationType', 'departments', 'courses.course'])->findOrFail($id);
            return view('admin.academic_directory.partials.quick_view_campus', compact('data'));
        } elseif ($type === 'department') {
            $data = Department::with(['organisation', 'campus', 'courses.course'])->findOrFail($id);
            return view('admin.academic_directory.partials.quick_view_department', compact('data'));
        } elseif ($type === 'course') {
            $data = OrganisationCourse::with(['organisation', 'campus', 'department', 'course.programLevel'])->findOrFail($id);
            return view('admin.academic_directory.partials.quick_view_course', compact('data'));
        }
        return response()->json(['status' => 0, 'message' => 'Invalid type'], 404);
    }

    /**
     * CSV Export streaming endpoint.
     */
    public function exportDirectory(Request $request)
    {
        $tab = $request->input('tab', 'organisations');
        $fileName = "academic_directory_{$tab}_" . date('Y-m-d_His') . ".csv";

        $headers = [
            "Content-type"        => "text/csv",
            "Content-Disposition" => "attachment; filename={$fileName}",
            "Pragma"              => "no-cache",
            "Cache-Control"       => "must-revalidate, post-check=0, pre-check=0",
            "Expires"             => "0"
        ];

        $callback = function() use ($tab, $request) {
            $file = fopen('php://output', 'w');

            if ($tab === 'organisations') {
                fputcsv($file, ['ID', 'Organisation Name', 'Type', 'Brand Type', 'Central Authority', 'Head Office', 'Campuses Count', 'Status']);
                $query = Organisation::with('organisationType')->withCount('campuses');
                if ($request->filled('organisation_type_id')) $query->where('organisation_type_id', $request->organisation_type_id);
                if ($request->filled('organisation_id')) $query->where('id', $request->organisation_id);
                if ($request->filled('campus_id')) $query->whereHas('campuses', fn($q) => $q->where('id', $request->campus_id));
                if ($request->filled('department_id')) $query->whereHas('departments', fn($q) => $q->where('id', $request->department_id));
                if ($request->filled('course_id')) $query->whereHas('courses', fn($q) => $q->where('course_id', $request->course_id));
                
                $query->chunk(100, function($rows) use ($file) {
                    foreach ($rows as $row) {
                        fputcsv($file, [
                            $row->id,
                            $row->name,
                            $row->organisationType ? $row->organisationType->title : 'N/A',
                            $row->brand_type ?? 'N/A',
                            $row->central_authority ?? 'N/A',
                            $row->head_office_location ?? 'N/A',
                            $row->campuses_count,
                            $row->status ? 'Published' : 'Draft'
                        ]);
                    }
                });
            } elseif ($tab === 'campuses') {
                fputcsv($file, ['ID', 'Campus Name', 'Campus Code', 'Parent Organisation', 'City', 'State', 'Verified', 'Status']);
                $query = Campus::with('organisation');
                if ($request->filled('organisation_type_id')) $query->whereHas('organisation', fn($q) => $q->where('organisation_type_id', $request->organisation_type_id));
                if ($request->filled('organisation_id')) $query->where('organisation_id', $request->organisation_id);
                if ($request->filled('campus_id')) $query->where('id', $request->campus_id);
                if ($request->filled('department_id')) $query->whereHas('departments', fn($q) => $q->where('id', $request->department_id));
                if ($request->filled('course_id')) $query->whereHas('courses', fn($q) => $q->where('course_id', $request->course_id));

                $query->chunk(100, function($rows) use ($file) {
                    foreach ($rows as $row) {
                        fputcsv($file, [
                            $row->id,
                            $row->campus_name,
                            $row->campus_code ?? 'N/A',
                            $row->organisation ? $row->organisation->name : 'N/A',
                            $row->city ?? 'N/A',
                            $row->state ?? 'N/A',
                            $row->verification_status ? 'Yes' : 'No',
                            $row->status ? 'Active' : 'Inactive'
                        ]);
                    }
                });
            } elseif ($tab === 'departments') {
                fputcsv($file, ['ID', 'Department Name', 'Code', 'Organisation', 'Campus', 'Discipline', 'HOD Name', 'Faculty Count', 'Labs Count']);
                $query = Department::with(['organisation', 'campus']);
                if ($request->filled('organisation_type_id')) $query->whereHas('organisation', fn($q) => $q->where('organisation_type_id', $request->organisation_type_id));
                if ($request->filled('organisation_id')) $query->where('organisation_id', $request->organisation_id);
                if ($request->filled('campus_id')) $query->where('campus_id', $request->campus_id);
                if ($request->filled('department_id')) $query->where('id', $request->department_id);
                if ($request->filled('course_id')) $query->whereHas('courses', fn($q) => $q->where('course_id', $request->course_id));

                $query->chunk(100, function($rows) use ($file) {
                    foreach ($rows as $row) {
                        fputcsv($file, [
                            $row->id,
                            $row->department_name,
                            $row->department_code ?? 'N/A',
                            $row->organisation ? $row->organisation->name : 'N/A',
                            $row->campus ? $row->campus->campus_name : 'N/A',
                            $row->discipline_area ?? 'General',
                            $row->head_of_department_name ?? 'N/A',
                            $row->faculty_count ?? 0,
                            $row->department_labs_count ?? 0
                        ]);
                    }
                });
            } elseif ($tab === 'courses') {
                fputcsv($file, ['ID', 'Program Name', 'Organisation', 'Campus', 'Department', 'Program Type', 'Duration', 'Total Fees', 'Status']);
                $query = OrganisationCourse::with(['course', 'organisation', 'campus', 'department']);
                if ($request->filled('organisation_type_id')) $query->whereHas('organisation', fn($q) => $q->where('organisation_type_id', $request->organisation_type_id));
                if ($request->filled('organisation_id')) $query->where('organisation_id', $request->organisation_id);
                if ($request->filled('campus_id')) $query->where('campus_id', $request->campus_id);
                if ($request->filled('department_id')) $query->where('department_id', $request->department_id);
                if ($request->filled('course_id')) $query->where('course_id', $request->course_id);

                $query->chunk(100, function($rows) use ($file) {
                    foreach ($rows as $row) {
                        fputcsv($file, [
                            $row->id,
                            $row->course ? $row->course->name : 'N/A',
                            $row->organisation ? $row->organisation->name : 'N/A',
                            $row->campus ? $row->campus->campus_name : 'N/A',
                            $row->department ? $row->department->department_name : 'N/A',
                            $row->program_type ?? 'N/A',
                            $row->duration ?? 'N/A',
                            $row->total_fees ?? $row->fee ?? 'N/A',
                            $row->status ? 'Active' : 'Inactive'
                        ]);
                    }
                });
            }

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }
}

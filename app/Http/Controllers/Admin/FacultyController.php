<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\FacultyProfile;
use App\Models\FacultyDesignation;
use App\Models\FacultyType;
use App\Models\FacultyDepartmentMapping;
use App\Models\FacultyCourseMapping;
use App\Models\FacultyQualification;
use App\Models\FacultyExperience;
use App\Models\FacultyPublication;
use App\Models\FacultyAchievement;
use App\Models\FacultyCertification;
use App\Models\FacultyCustomField;
use App\Models\Organisation;
use App\Models\Campus;
use App\Models\Department;
use App\Models\Course;
use App\Models\OrganisationCourse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;

class FacultyController extends Controller
{
    /**
     * Display a listing of faculties with filters and statistics.
     */
    public function index(Request $request)
    {
        $query = FacultyProfile::with([
            'designation',
            'facultyType',
            'departmentMappings.organisation',
            'departmentMappings.campus',
            'departmentMappings.department'
        ]);

        // Filters
        if ($request->filled('search')) {
            $search = trim($request->search);
            $query->where(function ($q) use ($search) {
                $q->where('full_name', 'like', "%{$search}%")
                  ->orWhere('faculty_code', 'like', "%{$search}%")
                  ->orWhere('official_email', 'like', "%{$search}%")
                  ->orWhere('official_phone', 'like', "%{$search}%");
            });
        }

        if ($request->filled('organisation_id')) {
            $orgId = $request->organisation_id;
            $query->whereHas('departmentMappings', function ($q) use ($orgId) {
                $q->where('organisation_id', $orgId);
            });
        }

        if ($request->filled('campus_id')) {
            $campusId = $request->campus_id;
            $query->whereHas('departmentMappings', function ($q) use ($campusId) {
                $q->where('campus_id', $campusId);
            });
        }

        if ($request->filled('department_id')) {
            $deptId = $request->department_id;
            $query->whereHas('departmentMappings', function ($q) use ($deptId) {
                $q->where('department_id', $deptId);
            });
        }

        if ($request->filled('designation_id')) {
            $query->where('designation_id', $request->designation_id);
        }

        if ($request->filled('faculty_type_id')) {
            $query->where('faculty_type_id', $request->faculty_type_id);
        }

        if ($request->filled('verification_status')) {
            $query->where('verification_status', $request->verification_status);
        }

        if ($request->has('status') && $request->status !== null && $request->status !== '') {
            $query->where('status', (int) $request->status);
        }

        // Stats
        $stats = [
            'total' => FacultyProfile::count(),
            'verified' => FacultyProfile::where('verification_status', 'verified')->count(),
            'pending' => FacultyProfile::where('verification_status', 'pending')->count(),
            'active' => FacultyProfile::where('status', 1)->count(),
        ];

        $faculties = $query->latest()->paginate(15)->withQueryString();

        // Masters for filter dropdowns
        $organisations = Organisation::select('id', 'name')->where('status', 1)->orderBy('name')->get();
        $campuses = $request->filled('organisation_id')
            ? Campus::where('organisation_id', $request->organisation_id)->orderBy('campus_name')->get()
            : Campus::select('id', 'campus_name')->orderBy('campus_name')->get();
        $departments = $request->filled('campus_id')
            ? Department::where('campus_id', $request->campus_id)->orderBy('department_name')->get()
            : Department::select('id', 'department_name')->orderBy('department_name')->get();
        $designations = FacultyDesignation::where('status', 1)->orderBy('hierarchy_order')->get();
        $facultyTypes = FacultyType::where('status', 1)->get();

        return view('admin.faculties.index', compact(
            'faculties',
            'stats',
            'organisations',
            'campuses',
            'departments',
            'designations',
            'facultyTypes'
        ));
    }

    /**
     * Show the form for creating a new faculty.
     */
    public function create(Request $request)
    {
        $organisations = Organisation::select('id', 'name')->where('status', 1)->orderBy('name')->get();
        $campuses = Campus::select('id', 'campus_name', 'organisation_id')->orderBy('campus_name')->get();
        $departments = Department::select('id', 'department_name', 'campus_id', 'organisation_id')->orderBy('department_name')->get();
        $courses = Course::select('id', 'name')->where('status', 1)->orderBy('name')->get();
        $designations = FacultyDesignation::where('status', 1)->orderBy('hierarchy_order')->get();
        $facultyTypes = FacultyType::where('status', 1)->get();

        $selectedOrganisationId = $request->query('organisation_id');
        $selectedCampusId = $request->query('campus_id');
        $selectedDepartmentId = $request->query('department_id');

        return view('admin.faculties.create', compact(
            'organisations',
            'campuses',
            'departments',
            'courses',
            'designations',
            'facultyTypes',
            'selectedOrganisationId',
            'selectedCampusId',
            'selectedDepartmentId'
        ));
    }

    /**
     * Store a newly created faculty in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'full_name' => 'required|string|max:255',
            'designation_id' => 'nullable|exists:faculty_designations,id',
            'faculty_type_id' => 'nullable|exists:faculty_types,id',
            'short_bio' => 'nullable|string',
            'official_email' => 'nullable|email|max:255',
            'profile_photo' => 'nullable|image|mimes:jpeg,png,jpg,webp|max:3072',
        ]);

        DB::beginTransaction();
        try {
            $data = $request->only([
                'full_name',
                'display_name',
                'designation_id',
                'designation_name',
                'faculty_type_id',
                'gender',
                'date_of_birth',
                'short_bio',
                'detailed_biography',
                'date_of_joining',
                'experience_years',
                'status',
                'official_email',
                'official_phone',
                'extension',
                'office_location',
                'office_room_number',
                'research_interests',
                'research_areas',
                'total_publications',
                'books_published',
                'patents',
                'research_projects',
                'grants_received',
                'linkedin_url',
                'google_scholar_url',
                'orcid_id',
                'researchgate_url',
                'personal_website_url',
                'scopus_id',
                'vidwan_id',
                'academia_url',
                'slug',
                'meta_title',
                'meta_description',
                'meta_keywords',
                'canonical_url',
                'profile_status',
                'is_indexed',
                'verification_status',
                'verification_source',
                'verification_notes',
            ]);

            $data['status'] = $request->has('status') ? (bool) $request->status : true;
            $data['is_indexed'] = $request->has('is_indexed') ? (bool) $request->is_indexed : true;

            // Designation name sync if designation_id provided
            if ($request->filled('designation_id')) {
                $desig = FacultyDesignation::find($request->designation_id);
                if ($desig) {
                    $data['designation_name'] = $desig->name;
                }
            }

            // Slug generation
            if (empty($data['slug'])) {
                $baseSlug = Str::slug($request->full_name);
                $uniqueSlug = $baseSlug;
                $counter = 1;
                while (FacultyProfile::where('slug', $uniqueSlug)->exists()) {
                    $uniqueSlug = $baseSlug . '-' . $counter;
                    $counter++;
                }
                $data['slug'] = $uniqueSlug;
            }

            // Tags arrays
            $data['specializations'] = $this->parseTags($request->specializations);
            $data['research_areas_tags'] = $this->parseTags($request->research_areas_tags);
            $data['subjects_taught'] = $this->parseTags($request->subjects_taught);

            // Handle Profile Photo Upload
            if ($request->hasFile('profile_photo')) {
                $file = $request->file('profile_photo');
                $filename = 'faculty_' . time() . '_' . Str::random(6) . '.' . $file->getClientOriginalExtension();
                $path = 'uploads/faculties';
                if (!File::isDirectory(public_path($path))) {
                    File::makeDirectory(public_path($path), 0777, true, true);
                }
                $file->move(public_path($path), $filename);
                $data['profile_photo'] = $path . '/' . $filename;
            }

            // Verification details
            if ($request->verification_status === 'verified') {
                $data['verified_by'] = auth('admin')->id();
                $data['verified_date'] = now();
                $data['last_verification_date'] = now();
            }

            $faculty = FacultyProfile::create($data);

            // 1. Save Department Mappings
            $this->syncDepartmentMappings($faculty, $request->input('department_mappings', []));

            // 2. Save Course Mappings
            $this->syncCourseMappings($faculty, $request->input('course_mappings', []));

            // 3. Save Qualifications
            $this->syncQualifications($faculty, $request->input('qualifications', []), $request);

            // 4. Save Experiences
            $this->syncExperiences($faculty, $request->input('experiences', []));

            // 5. Save Publications
            $this->syncPublications($faculty, $request->input('publications', []), $request);

            // 6. Save Achievements
            $this->syncAchievements($faculty, $request->input('achievements', []), $request);

            // 7. Save Certifications
            $this->syncCertifications($faculty, $request->input('certifications', []), $request);

            // 8. Save Dynamic Custom Fields
            $this->syncCustomFields($faculty, $request->input('custom_fields', []));

            DB::commit();

            return redirect()->route('admin.faculties.index')->with('success', 'Faculty profile created successfully.');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withInput()->with('error', 'Error creating faculty: ' . $e->getMessage());
        }
    }

    /**
     * Display the specified faculty profile.
     */
    public function show(FacultyProfile $faculty)
    {
        $faculty->load([
            'designation',
            'facultyType',
            'verifier',
            'departmentMappings.organisation',
            'departmentMappings.campus',
            'departmentMappings.department',
            'departmentMappings.designation',
            'courseMappings.course',
            'qualifications',
            'experiences',
            'publications',
            'achievements',
            'certifications',
            'customFields'
        ]);

        return view('admin.faculties.show', compact('faculty'));
    }

    /**
     * Show the form for editing the specified faculty.
     */
    public function edit(FacultyProfile $faculty)
    {
        $faculty->load([
            'designation',
            'facultyType',
            'departmentMappings',
            'courseMappings',
            'qualifications',
            'experiences',
            'publications',
            'achievements',
            'certifications',
            'customFields'
        ]);

        $organisations = Organisation::select('id', 'name')->where('status', 1)->orderBy('name')->get();
        $campuses = Campus::select('id', 'campus_name', 'organisation_id')->orderBy('campus_name')->get();
        $departments = Department::select('id', 'department_name', 'campus_id', 'organisation_id')->orderBy('department_name')->get();
        $courses = Course::select('id', 'name')->where('status', 1)->orderBy('name')->get();
        $designations = FacultyDesignation::where('status', 1)->orderBy('hierarchy_order')->get();
        $facultyTypes = FacultyType::where('status', 1)->get();

        return view('admin.faculties.edit', compact(
            'faculty',
            'organisations',
            'campuses',
            'departments',
            'courses',
            'designations',
            'facultyTypes'
        ));
    }

    /**
     * Update the specified faculty in storage.
     */
    public function update(Request $request, FacultyProfile $faculty)
    {
        $request->validate([
            'full_name' => 'required|string|max:255',
            'designation_id' => 'nullable|exists:faculty_designations,id',
            'faculty_type_id' => 'nullable|exists:faculty_types,id',
            'short_bio' => 'nullable|string',
            'official_email' => 'nullable|email|max:255',
            'profile_photo' => 'nullable|image|mimes:jpeg,png,jpg,webp|max:3072',
        ]);

        DB::beginTransaction();
        try {
            $data = $request->only([
                'full_name',
                'display_name',
                'designation_id',
                'designation_name',
                'faculty_type_id',
                'gender',
                'date_of_birth',
                'short_bio',
                'detailed_biography',
                'date_of_joining',
                'experience_years',
                'status',
                'official_email',
                'official_phone',
                'extension',
                'office_location',
                'office_room_number',
                'research_interests',
                'research_areas',
                'total_publications',
                'books_published',
                'patents',
                'research_projects',
                'grants_received',
                'linkedin_url',
                'google_scholar_url',
                'orcid_id',
                'researchgate_url',
                'personal_website_url',
                'scopus_id',
                'vidwan_id',
                'academia_url',
                'slug',
                'meta_title',
                'meta_description',
                'meta_keywords',
                'canonical_url',
                'profile_status',
                'is_indexed',
                'verification_status',
                'verification_source',
                'verification_notes',
            ]);

            $data['status'] = $request->has('status') ? (bool) $request->status : true;
            $data['is_indexed'] = $request->has('is_indexed') ? (bool) $request->is_indexed : true;

            if ($request->filled('designation_id')) {
                $desig = FacultyDesignation::find($request->designation_id);
                if ($desig) {
                    $data['designation_name'] = $desig->name;
                }
            }

            // Slug validation
            if (!empty($data['slug']) && $data['slug'] !== $faculty->slug) {
                $baseSlug = Str::slug($data['slug']);
                $uniqueSlug = $baseSlug;
                $counter = 1;
                while (FacultyProfile::where('slug', $uniqueSlug)->where('id', '!=', $faculty->id)->exists()) {
                    $uniqueSlug = $baseSlug . '-' . $counter;
                    $counter++;
                }
                $data['slug'] = $uniqueSlug;
            }

            // Tags
            $data['specializations'] = $this->parseTags($request->specializations);
            $data['research_areas_tags'] = $this->parseTags($request->research_areas_tags);
            $data['subjects_taught'] = $this->parseTags($request->subjects_taught);

            // Handle Profile Photo Upload
            if ($request->hasFile('profile_photo')) {
                if ($faculty->profile_photo && File::exists(public_path($faculty->profile_photo))) {
                    @unlink(public_path($faculty->profile_photo));
                }
                $file = $request->file('profile_photo');
                $filename = 'faculty_' . time() . '_' . Str::random(6) . '.' . $file->getClientOriginalExtension();
                $path = 'uploads/faculties';
                if (!File::isDirectory(public_path($path))) {
                    File::makeDirectory(public_path($path), 0777, true, true);
                }
                $file->move(public_path($path), $filename);
                $data['profile_photo'] = $path . '/' . $filename;
            }

            // Verification handling
            if ($request->verification_status === 'verified' && $faculty->verification_status !== 'verified') {
                $data['verified_by'] = auth('admin')->id();
                $data['verified_date'] = now();
                $data['last_verification_date'] = now();
            }

            $faculty->update($data);

            // Sync Repeaters & Mappings
            $this->syncDepartmentMappings($faculty, $request->input('department_mappings', []));
            $this->syncCourseMappings($faculty, $request->input('course_mappings', []));
            $this->syncQualifications($faculty, $request->input('qualifications', []), $request);
            $this->syncExperiences($faculty, $request->input('experiences', []));
            $this->syncPublications($faculty, $request->input('publications', []), $request);
            $this->syncAchievements($faculty, $request->input('achievements', []), $request);
            $this->syncCertifications($faculty, $request->input('certifications', []), $request);
            $this->syncCustomFields($faculty, $request->input('custom_fields', []));

            DB::commit();

            return redirect()->route('admin.faculties.index')->with('success', 'Faculty profile updated successfully.');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withInput()->with('error', 'Error updating faculty: ' . $e->getMessage());
        }
    }

    /**
     * Remove the specified faculty from storage.
     */
    public function destroy(FacultyProfile $faculty)
    {
        try {
            $faculty->delete();
            return redirect()->route('admin.faculties.index')->with('success', 'Faculty profile deleted successfully.');
        } catch (\Exception $e) {
            return back()->with('error', 'Error deleting faculty: ' . $e->getMessage());
        }
    }

    /**
     * Toggle status via AJAX.
     */
    public function toggleStatus(FacultyProfile $faculty)
    {
        $faculty->status = !$faculty->status;
        $faculty->save();

        return response()->json([
            'success' => true,
            'status' => $faculty->status,
            'message' => 'Status updated successfully.'
        ]);
    }

    /**
     * Update verification status quickly.
     */
    public function updateVerification(Request $request, FacultyProfile $faculty)
    {
        $request->validate([
            'verification_status' => 'required|in:pending,verified,unverified,rejected',
            'verification_notes' => 'nullable|string',
        ]);

        $faculty->verification_status = $request->verification_status;
        $faculty->verification_notes = $request->verification_notes;
        if ($request->verification_status === 'verified') {
            $faculty->verified_by = auth('admin')->id();
            $faculty->verified_date = now();
        }
        $faculty->last_verification_date = now();
        $faculty->save();

        if ($request->ajax()) {
            return response()->json(['success' => true, 'message' => 'Verification status updated.']);
        }

        return back()->with('success', 'Verification status updated.');
    }

    /**
     * Bulk destroy faculties.
     */
    public function bulkDestroy(Request $request)
    {
        $ids = $request->input('ids', []);
        if (!empty($ids)) {
            FacultyProfile::whereIn('id', $ids)->delete();
            return redirect()->route('admin.faculties.index')->with('success', count($ids) . ' faculty profiles deleted.');
        }
        return back()->with('error', 'No faculty selected.');
    }

    // ==========================================
    // REPEATER & MAPPING SYNC HELPERS
    // ==========================================

    protected function syncDepartmentMappings(FacultyProfile $faculty, array $mappings)
    {
        $faculty->departmentMappings()->delete();
        foreach ($mappings as $row) {
            if (!empty($row['organisation_id'])) {
                FacultyDepartmentMapping::create([
                    'faculty_id' => $faculty->id,
                    'organisation_id' => $row['organisation_id'],
                    'campus_id' => !empty($row['campus_id']) ? $row['campus_id'] : null,
                    'department_id' => !empty($row['department_id']) ? $row['department_id'] : null,
                    'designation_id' => !empty($row['designation_id']) ? $row['designation_id'] : null,
                    'joining_date' => !empty($row['joining_date']) ? $row['joining_date'] : null,
                    'is_primary_department' => !empty($row['is_primary_department']) ? (bool) $row['is_primary_department'] : false,
                    'status' => isset($row['status']) ? (bool) $row['status'] : true,
                ]);
            }
        }
    }

    protected function syncCourseMappings(FacultyProfile $faculty, array $mappings)
    {
        $faculty->courseMappings()->delete();
        foreach ($mappings as $row) {
            if (!empty($row['course_id']) || !empty($row['subject_name'])) {
                FacultyCourseMapping::create([
                    'faculty_id' => $faculty->id,
                    'organisation_course_id' => !empty($row['organisation_course_id']) ? $row['organisation_course_id'] : null,
                    'course_id' => !empty($row['course_id']) ? $row['course_id'] : null,
                    'subject_id' => !empty($row['subject_id']) ? $row['subject_id'] : null,
                    'subject_name' => $row['subject_name'] ?? null,
                    'academic_year' => $row['academic_year'] ?? null,
                    'semester' => $row['semester'] ?? null,
                    'role' => $row['role'] ?? 'Instructor',
                    'status' => isset($row['status']) ? (bool) $row['status'] : true,
                ]);
            }
        }
    }

    protected function syncQualifications(FacultyProfile $faculty, array $qualifications, Request $request)
    {
        $faculty->qualifications()->delete();
        $order = 1;
        foreach ($qualifications as $index => $row) {
            if (!empty($row['degree'])) {
                $docPath = $row['existing_document'] ?? null;
                if ($request->hasFile("qualifications.{$index}.certificate_document")) {
                    $file = $request->file("qualifications.{$index}.certificate_document");
                    $name = 'qual_' . time() . '_' . $index . '.' . $file->getClientOriginalExtension();
                    $file->move(public_path('uploads/faculties/qualifications'), $name);
                    $docPath = 'uploads/faculties/qualifications/' . $name;
                }

                FacultyQualification::create([
                    'faculty_id' => $faculty->id,
                    'degree' => $row['degree'],
                    'specialization' => $row['specialization'] ?? null,
                    'institution' => $row['institution'] ?? null,
                    'university' => $row['university'] ?? null,
                    'year' => $row['year'] ?? null,
                    'grade_percentage' => $row['grade_percentage'] ?? null,
                    'certificate_document' => $docPath,
                    'display_order' => $order++,
                ]);
            }
        }
    }

    protected function syncExperiences(FacultyProfile $faculty, array $experiences)
    {
        $faculty->experiences()->delete();
        $order = 1;
        foreach ($experiences as $row) {
            if (!empty($row['organization']) && !empty($row['designation'])) {
                FacultyExperience::create([
                    'faculty_id' => $faculty->id,
                    'organization' => $row['organization'],
                    'designation' => $row['designation'],
                    'start_date' => !empty($row['start_date']) ? $row['start_date'] : null,
                    'end_date' => !empty($row['end_date']) ? $row['end_date'] : null,
                    'currently_working' => !empty($row['currently_working']) ? (bool) $row['currently_working'] : false,
                    'description' => $row['description'] ?? null,
                    'display_order' => $order++,
                ]);
            }
        }
    }

    protected function syncPublications(FacultyProfile $faculty, array $publications, Request $request)
    {
        $faculty->publications()->delete();
        $order = 1;
        foreach ($publications as $index => $row) {
            if (!empty($row['title'])) {
                $docPath = $row['existing_document'] ?? null;
                if ($request->hasFile("publications.{$index}.document_pdf")) {
                    $file = $request->file("publications.{$index}.document_pdf");
                    $name = 'pub_' . time() . '_' . $index . '.' . $file->getClientOriginalExtension();
                    $file->move(public_path('uploads/faculties/publications'), $name);
                    $docPath = 'uploads/faculties/publications/' . $name;
                }

                FacultyPublication::create([
                    'faculty_id' => $faculty->id,
                    'title' => $row['title'],
                    'publication_type' => $row['publication_type'] ?? 'Journal',
                    'journal_conference' => $row['journal_conference'] ?? null,
                    'publisher' => $row['publisher'] ?? null,
                    'publication_date' => !empty($row['publication_date']) ? $row['publication_date'] : null,
                    'doi' => $row['doi'] ?? null,
                    'url' => $row['url'] ?? null,
                    'authors' => $row['authors'] ?? null,
                    'citation_count' => (int) ($row['citation_count'] ?? 0),
                    'document_pdf' => $docPath,
                    'verified' => !empty($row['verified']) ? (bool) $row['verified'] : false,
                    'display_order' => $order++,
                ]);
            }
        }
    }

    protected function syncAchievements(FacultyProfile $faculty, array $achievements, Request $request)
    {
        $faculty->achievements()->delete();
        $order = 1;
        foreach ($achievements as $index => $row) {
            if (!empty($row['title'])) {
                $imgPath = $row['existing_certificate'] ?? null;
                if ($request->hasFile("achievements.{$index}.certificate_image")) {
                    $file = $request->file("achievements.{$index}.certificate_image");
                    $name = 'achieve_' . time() . '_' . $index . '.' . $file->getClientOriginalExtension();
                    $file->move(public_path('uploads/faculties/achievements'), $name);
                    $imgPath = 'uploads/faculties/achievements/' . $name;
                }

                FacultyAchievement::create([
                    'faculty_id' => $faculty->id,
                    'title' => $row['title'],
                    'achievement_type' => $row['achievement_type'] ?? 'Award',
                    'organization' => $row['organization'] ?? null,
                    'year' => $row['year'] ?? null,
                    'description' => $row['description'] ?? null,
                    'certificate_image' => $imgPath,
                    'verification_status' => $row['verification_status'] ?? 'unverified',
                    'display_order' => $order++,
                ]);
            }
        }
    }

    protected function syncCertifications(FacultyProfile $faculty, array $certifications, Request $request)
    {
        $faculty->certifications()->delete();
        $order = 1;
        foreach ($certifications as $index => $row) {
            if (!empty($row['certification_name'])) {
                $uploadPath = $row['existing_upload'] ?? null;
                if ($request->hasFile("certifications.{$index}.certificate_upload")) {
                    $file = $request->file("certifications.{$index}.certificate_upload");
                    $name = 'cert_' . time() . '_' . $index . '.' . $file->getClientOriginalExtension();
                    $file->move(public_path('uploads/faculties/certifications'), $name);
                    $uploadPath = 'uploads/faculties/certifications/' . $name;
                }

                FacultyCertification::create([
                    'faculty_id' => $faculty->id,
                    'certification_name' => $row['certification_name'],
                    'issuing_organization' => $row['issuing_organization'] ?? null,
                    'issue_date' => !empty($row['issue_date']) ? $row['issue_date'] : null,
                    'expiry_date' => !empty($row['expiry_date']) ? $row['expiry_date'] : null,
                    'credential_id' => $row['credential_id'] ?? null,
                    'certificate_url' => $row['certificate_url'] ?? null,
                    'certificate_upload' => $uploadPath,
                    'display_order' => $order++,
                ]);
            }
        }
    }

    protected function syncCustomFields(FacultyProfile $faculty, array $customFields)
    {
        $faculty->customFields()->delete();
        foreach ($customFields as $row) {
            if (!empty($row['field_key']) && !empty($row['field_label'])) {
                FacultyCustomField::create([
                    'faculty_id' => $faculty->id,
                    'institution_type' => $row['institution_type'] ?? 'general',
                    'field_key' => Str::slug($row['field_key'], '_'),
                    'field_label' => $row['field_label'],
                    'field_type' => $row['field_type'] ?? 'text',
                    'field_value' => $row['field_value'] ?? null,
                ]);
            }
        }
    }

    protected function parseTags($input)
    {
        if (is_array($input)) {
            return array_values(array_filter(array_map('trim', $input)));
        }
        if (is_string($input)) {
            return array_values(array_filter(array_map('trim', explode(',', $input))));
        }
        return [];
    }

    // ==========================================
    // CASCADING AJAX ENDPOINTS
    // ==========================================

    public function getCampusesByOrganisation($organisationId)
    {
        $campuses = Campus::where('organisation_id', $organisationId)
            ->where('status', 1)
            ->select('id', 'campus_name')
            ->orderBy('campus_name')
            ->get();
        return response()->json($campuses);
    }

    public function getDepartmentsByCampus(Request $request)
    {
        $campusId = $request->query('campus_id');
        $orgId = $request->query('organisation_id');

        $query = Department::where('status', 'Active')->select('id', 'department_name');
        if ($campusId) {
            $query->where('campus_id', $campusId);
        } elseif ($orgId) {
            $query->where('organisation_id', $orgId);
        }

        $departments = $query->orderBy('department_name')->get();
        return response()->json($departments);
    }

    public function getCoursesByOrganisationOrDepartment(Request $request)
    {
        $deptId = $request->query('department_id');
        $orgId = $request->query('organisation_id');

        if ($deptId || $orgId) {
            $query = OrganisationCourse::with('course')->where('status', 1);
            if ($deptId) {
                $query->where('department_id', $deptId);
            }
            if ($orgId) {
                $query->where('organisation_id', $orgId);
            }
            $courses = $query->get()->map(function ($oc) {
                return [
                    'id' => $oc->course_id,
                    'organisation_course_id' => $oc->id,
                    'name' => $oc->course->name ?? $oc->academic_unit_name ?? 'Course #' . $oc->id
                ];
            });
            return response()->json($courses);
        }

        $courses = Course::where('status', 1)->select('id', 'name')->orderBy('name')->get();
        return response()->json($courses);
    }
}

<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Campus;
use App\Models\CampusDocument;
use App\Models\Organisation;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

class CampusController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Organisation $organisation)
    {
        $typeTitle = strtolower($organisation->organisationType->title ?? '');
        if ($organisation->organisation_type_id == 9 || str_contains($typeTitle, 'e-learning') || str_contains($typeTitle, 'elearning')) {
            return redirect()->route('admin.organisations.edit', $organisation->id)
                ->with('info', 'E-Learning Platforms do not have physical campuses.');
        }

        $campuses = $organisation->campuses()->withCount(['departments', 'courses'])->latest()->get();
        return view('admin.organisations.campuses.index', compact('organisation', 'campuses'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(Organisation $organisation)
    {
        $brandTypes = Organisation::BRAND_TYPES;
        $examCategories = \App\Models\Exam::whereNotNull('exam_category')->get()
            ->pluck('exam_category')
            ->flatten()
            ->unique()
            ->filter()
            ->values();
        $exams = \App\Models\Exam::select('id', 'name', 'exam_category')->orderBy('name')->get();
        $facilitiesMaster = \App\Models\Facility::where('status', 1)
            ->when($organisation->organisation_type_id, function ($query) use ($organisation) {
                $query->where(function ($q) use ($organisation) {
                    $q->whereHas('organisationTypes', function ($sub) use ($organisation) {
                        $sub->where('organisation_types.id', $organisation->organisation_type_id);
                    })->orDoesntHave('organisationTypes');
                });
            })
            ->orderBy('name')
            ->get();
        $schoolTypes = \App\Models\CampusTypeNew::all();
        $coachingCategories = \App\Models\CoachingCategory::where('status', 1)->orderBy('sort_order')->get();
        $academicClasses = \App\Models\AcademicClass::where('status', 1)->orderBy('sort_order', 'asc')->orderBy('id', 'asc')->get();
        return view('admin.organisations.campuses.create', compact('organisation', 'brandTypes', 'exams', 'examCategories', 'facilitiesMaster', 'schoolTypes', 'coachingCategories', 'academicClasses'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request, Organisation $organisation)
    {
        $request->validate([
            'campus_name' => 'required|string|max:255',
            'documents' => 'nullable|array',
            'documents.*.id' => 'nullable|integer',
            'documents.*.title' => 'nullable|string|max:255',
            'documents.*.file' => 'nullable|file|mimes:pdf|max:20480',
        ], [
            'documents.*.file.mimes' => 'Each uploaded document must be a PDF file.',
            'documents.*.file.max' => 'Each uploaded document must not exceed 20MB.',
        ]);

        $input = $request->except(['documents', 'has_documents_form']);
        foreach ($input as $k => $v) {
            if ($v === '') {
                $input[$k] = null;
            }
        }

        if ($request->has('events_and_activities') || $request->hasFile('events_and_activities')) {
            $input['events_and_activities'] = $this->processEventsAndActivities($request);
        }

        if ($request->has('school_classes')) {
            $input['school_classes'] = $this->processSchoolClasses($request);
        }

        $campus = new Campus($input);
        $campus->organisation_id = $organisation->id;
        $campus->save();

        $this->syncDocuments($request, $campus);

        return redirect()->route('admin.organisations.campuses.index', $organisation->id)
            ->with('success', 'Campus created successfully');
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Organisation $organisation, Campus $campus)
    {
        $campus->load('documents');
        $brandTypes = Organisation::BRAND_TYPES;
        $examCategories = \App\Models\Exam::whereNotNull('exam_category')->get()
            ->pluck('exam_category')
            ->flatten()
            ->unique()
            ->filter()
            ->values();
        $exams = \App\Models\Exam::select('id', 'name', 'exam_category')->orderBy('name')->get();
        $selectedFacilityIds = is_array($campus->facilities) 
            ? $campus->facilities 
            : (json_decode($campus->facilities, true) ?: []);
        $facilitiesMaster = \App\Models\Facility::where('status', 1)
            ->where(function ($query) use ($organisation, $selectedFacilityIds) {
                if ($organisation->organisation_type_id) {
                    $query->whereHas('organisationTypes', function ($sub) use ($organisation) {
                        $sub->where('organisation_types.id', $organisation->organisation_type_id);
                    })->orDoesntHave('organisationTypes');
                }
                if (!empty($selectedFacilityIds)) {
                    $query->orWhereIn('id', $selectedFacilityIds);
                }
            })
            ->orderBy('name')
            ->get();
        $schoolTypes = \App\Models\CampusTypeNew::all();
        $coachingCategories = \App\Models\CoachingCategory::where('status', 1)->orderBy('sort_order')->get();
        $academicClasses = \App\Models\AcademicClass::where('status', 1)->orderBy('sort_order', 'asc')->orderBy('id', 'asc')->get();
        return view('admin.organisations.campuses.edit', compact('organisation', 'campus', 'brandTypes', 'exams', 'examCategories', 'facilitiesMaster', 'schoolTypes', 'coachingCategories', 'academicClasses'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Organisation $organisation, Campus $campus)
    {
        $request->validate([
            'facilities' => 'nullable|array',
            'facilities.*' => 'string',
            'class_profile' => 'nullable|array',
            'class_profile.*.year' => 'nullable|integer',
            'class_profile.*.total_students' => 'nullable|integer|min:0',
            'class_profile.*.total_faculty' => 'nullable|integer|min:0',
            'class_profile.*.total_male_students' => 'nullable|integer|min:0',
            'class_profile.*.total_female_students' => 'nullable|integer|min:0',
            'class_profile.*.total_outside_state' => 'nullable|integer|min:0',
            'documents' => 'nullable|array',
            'documents.*.id' => 'nullable|integer',
            'documents.*.title' => 'nullable|string|max:255',
            'documents.*.file' => 'nullable|file|mimes:pdf|max:20480',
        ], [
            'documents.*.file.mimes' => 'Each uploaded document must be a PDF file.',
            'documents.*.file.max' => 'Each uploaded document must not exceed 20MB.',
        ]);

        $input = $request->except(['documents', 'has_documents_form']);
        foreach ($input as $k => $v) {
            if ($v === '') {
                $input[$k] = null;
            }
        }

        $booleans = [
            'smart_classrooms', 'library_available', 'digital_library_access', 'hostel_available',
            'medical_facility_available', 'transport_available', 'parking_available', 'cctv_coverage',
            'fire_safety_certified', 'disaster_management_plan', 'verification_status', 'status',
            'brand_compliance_verified', 'science_labs_available', 'computer_labs_available',
            'playground_available', 'gps_enabled_buses', 'visitor_management_system'
        ];

        foreach ($booleans as $field) {
            $input[$field] = $request->has($field) ? 1 : 0;
        }

        // Handle slug: if empty, let model handle it
        if (array_key_exists('slug', $input) && empty($input['slug'])) {
            unset($input['slug']);
        }

        if ($request->has('events_and_activities') || $request->hasFile('events_and_activities')) {
            $input['events_and_activities'] = $this->processEventsAndActivities($request, $campus->events_and_activities);
        }

        if ($request->has('school_classes')) {
            $input['school_classes'] = $this->processSchoolClasses($request);
        }

        $campus->update($input);
        $this->syncDocuments($request, $campus);

        return redirect()->route('admin.organisations.campuses.index', $organisation->id)
            ->with('success', 'Campus updated successfully');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Organisation $organisation, Campus $campus)
    {
        DB::transaction(function () use ($campus) {
            $campus->delete();
        });

        return redirect()->route('admin.organisations.campuses.index', $organisation->id)
            ->with('success', 'Campus and all associated departments and courses deleted successfully.');
    }

    /**
     * Store initial draft for multi-step form
     */
    public function storeDraft(Request $request, Organisation $organisation)
    {
        $request->validate([
            'campus_name' => 'required|string|max:255',
            // 'campus_type' => 'required',
        ]);

        $input = $request->all();
        $input['organisation_id'] = $organisation->id;

        // Handle slug: if empty, let model handle it
        if (array_key_exists('slug', $input) && empty($input['slug'])) {
            unset($input['slug']);
        }

        $campus = Campus::create($input);

        return response()->json([
            'status' => 'success',
            'campus_id' => $campus->id,
            'message' => 'Draft created successfully'
        ]);
    }

    /**
     * Batch save multiple fields for a tab
     */
    public function autosaveTab(Request $request, $orgId, $id)
    {
        \Log::info('autosaveTab request data for campus ' . $id . ':', $request->all());
        $campus = Campus::findOrFail($id);

        if ($request->has('class_profile')) {
            $request->validate([
                'class_profile' => 'nullable|array',
                'class_profile.*.year' => 'nullable|integer',
                'class_profile.*.total_students' => 'nullable|integer|min:0',
                'class_profile.*.total_faculty' => 'nullable|integer|min:0',
                'class_profile.*.total_male_students' => 'nullable|integer|min:0',
                'class_profile.*.total_female_students' => 'nullable|integer|min:0',
                'class_profile.*.total_outside_state' => 'nullable|integer|min:0',
            ]);
        }

        if ($request->has('facilities')) {
            $request->validate([
                'facilities' => 'nullable|array',
                'facilities.*' => 'string',
            ]);
        }

        $data = $request->all();
        
        // Handle slug: if empty, don't update it to avoid null constraint violation
        if (array_key_exists('slug', $data) && empty($data['slug'])) {
            unset($data['slug']);
        }

        unset($data['_token']);
        
        $booleans = [
            'smart_classrooms', 'library_available', 'digital_library_access', 'hostel_available',
            'medical_facility_available', 'transport_available', 'parking_available', 'cctv_coverage',
            'fire_safety_certified', 'disaster_management_plan', 'verification_status', 'status',
            'brand_compliance_verified', 'science_labs_available', 'computer_labs_available',
            'playground_available', 'gps_enabled_buses', 'visitor_management_system'
        ];

        $updateData = [];
        foreach ($data as $key => $value) {
            if (Schema::hasColumn('campuses', $key)) {
                if (in_array($key, $booleans)) {
                    $updateData[$key] = ($value === 'true' || $value === 1 || $value === '1' || $value === true) ? 1 : 0;
                } else {
                    $updateData[$key] = ($value === '') ? null : $value;
                }
            }
        }
        
        $processedEvents = null;
        if ($request->has('events_and_activities') || $request->hasFile('events_and_activities')) {
            $processedEvents = $this->processEventsAndActivities($request, $campus->events_and_activities);
            $updateData['events_and_activities'] = $processedEvents;
        }

        if ($request->has('school_classes')) {
            $updateData['school_classes'] = $this->processSchoolClasses($request);
        }

        $campus->update($updateData);

        $response = ['status' => 'success', 'message' => 'Tab saved'];
        if ($processedEvents !== null) {
            $response['events'] = $processedEvents;
        }
        return response()->json($response);
    }

    /**
     * Process school classes repeater.
     */
    protected function processSchoolClasses(Request $request): ?array
    {
        if (!$request->has('school_classes')) {
            return null;
        }

        $rawClasses = $request->input('school_classes', []);
        if (!is_array($rawClasses)) {
            return [];
        }

        $cleanClasses = [];
        foreach ($rawClasses as $sc) {
            if (!is_array($sc)) continue;
            $name = isset($sc['class_name']) ? trim((string)$sc['class_name']) : '';
            $curriculum = isset($sc['curriculum']) ? trim((string)$sc['curriculum']) : '';
            $fee = isset($sc['fee_structure']) ? trim((string)$sc['fee_structure']) : '';
            $admission = isset($sc['admission_process']) ? trim((string)$sc['admission_process']) : '';
            $examPrep = isset($sc['exam_preparation']) ? trim((string)$sc['exam_preparation']) : '';

            if ($name !== '' || $fee !== '' || $curriculum !== '' || $admission !== '' || $examPrep !== '') {
                $cleanClasses[] = [
                    'class_name' => $name,
                    'curriculum' => $curriculum,
                    'fee_structure' => $fee,
                    'admission_process' => $admission,
                    'exam_preparation' => $examPrep,
                ];
            }
        }

        return $cleanClasses;
    }

    /**
     * Process events and activities repeater with multiple uploaded images and existing images.
     */
    protected function processEventsAndActivities(Request $request, ?array $currentEvents = null): array
    {
        $rawEvents = $request->input('events_and_activities', []);
        if (!is_array($rawEvents)) {
            $rawEvents = [];
        }

        $processedEvents = [];
        $eventDir = public_path('uploads/campuses/events');
        if (!file_exists($eventDir)) {
            @mkdir($eventDir, 0777, true);
        }

        foreach ($rawEvents as $index => $eventData) {
            if (!is_array($eventData)) {
                continue;
            }

            $title = isset($eventData['title']) ? trim((string)$eventData['title']) : '';
            $category = isset($eventData['category']) ? trim((string)$eventData['category']) : 'Sports';
            $date = isset($eventData['date']) ? trim((string)$eventData['date']) : '';
            $description = isset($eventData['description']) ? trim((string)$eventData['description']) : '';

            // 1. Retain existing images that were not removed
            $images = [];
            if (!empty($eventData['existing_images']) && is_array($eventData['existing_images'])) {
                foreach ($eventData['existing_images'] as $img) {
                    if (!empty($img) && is_string($img)) {
                        $images[] = trim($img);
                    }
                }
            }

            // 2. Process newly uploaded multiple image files for this event index
            $uploadedFiles = $request->file("events_and_activities.{$index}.images");
            if ($uploadedFiles) {
                if (!is_array($uploadedFiles)) {
                    $uploadedFiles = [$uploadedFiles];
                }
                foreach ($uploadedFiles as $file) {
                    if ($file && $file->isValid()) {
                        $ext = $file->getClientOriginalExtension() ?: 'jpg';
                        $fileName = 'event_' . time() . '_' . uniqid() . '.' . $ext;
                        $file->move($eventDir, $fileName);
                        $images[] = 'uploads/campuses/events/' . $fileName;
                    }
                }
            }

            // Only add if at least title, description, or images exist
            if ($title !== '' || $description !== '' || !empty($images)) {
                $processedEvents[] = [
                    'title' => $title,
                    'category' => $category ?: 'Sports',
                    'date' => $date,
                    'description' => $description,
                    'images' => array_values(array_unique($images)),
                ];
            }
        }

        return $processedEvents;
    }

    protected function syncDocuments(Request $request, Campus $campus): void
    {
        $documents = $request->input('documents', []);
        $keptIds = [];
        $docDir = public_path('media/campuses/documents');

        if (!file_exists($docDir)) {
            @mkdir($docDir, 0777, true);
        }

        if (is_array($documents)) {
            $order = 0;
            foreach ($documents as $index => $docData) {
                $docId = $docData['id'] ?? null;
                $title = isset($docData['title']) ? trim((string)$docData['title']) : '';
                $uploadedFile = $request->file("documents.{$index}.file");

                if (!empty($docId)) {
                    $existingDoc = $campus->documents()->where('id', $docId)->first();
                    if ($existingDoc) {
                        $existingDoc->title = !empty($title) ? $title : ($existingDoc->title ?: 'Document');
                        $existingDoc->sort_order = $order++;

                        if ($uploadedFile && $uploadedFile->isValid()) {
                            if ($existingDoc->file_path && file_exists(public_path($existingDoc->file_path))) {
                                @unlink(public_path($existingDoc->file_path));
                            }

                            $origName = $uploadedFile->getClientOriginalName();
                            $fileName = time() . '_' . uniqid() . '.' . $uploadedFile->getClientOriginalExtension();
                            $uploadedFile->move($docDir, $fileName);
                            $savedPath = 'media/campuses/documents/' . $fileName;

                            $existingDoc->file_path = $savedPath;
                            $existingDoc->file_name = $origName;
                            $existingDoc->file_size = @filesize(public_path($savedPath)) ?: null;
                        }

                        $existingDoc->save();
                        $keptIds[] = $existingDoc->id;
                    }
                } else {
                    if ($uploadedFile && $uploadedFile->isValid()) {
                        $origName = $uploadedFile->getClientOriginalName();
                        $fileName = time() . '_' . uniqid() . '.' . $uploadedFile->getClientOriginalExtension();
                        $uploadedFile->move($docDir, $fileName);
                        $savedPath = 'media/campuses/documents/' . $fileName;

                        $newDoc = $campus->documents()->create([
                            'title' => !empty($title) ? $title : pathinfo($origName, PATHINFO_FILENAME),
                            'file_path' => $savedPath,
                            'file_name' => $origName,
                            'file_size' => @filesize(public_path($savedPath)) ?: null,
                            'sort_order' => $order++,
                        ]);
                        $keptIds[] = $newDoc->id;
                    }
                }
            }
        }

        if ($request->has('has_documents_form')) {
            $campus->documents()->whereNotIn('id', $keptIds)->get()->each(function ($doc) {
                if ($doc->file_path && file_exists(public_path($doc->file_path))) {
                    @unlink(public_path($doc->file_path));
                }
                $doc->delete();
            });
        }
    }

    public function destroyDocument(Organisation $organisation, Campus $campus, CampusDocument $document)
    {
        if ($document->campus_id !== $campus->id) {
            return response()->json([
                'status' => 'error',
                'message' => 'Document does not belong to this campus.'
            ], 403);
        }

        if ($document->file_path && file_exists(public_path($document->file_path))) {
            @unlink(public_path($document->file_path));
        }

        $document->delete();

        return response()->json([
            'status' => 'success',
            'message' => 'Document deleted successfully.'
        ]);
    }
}

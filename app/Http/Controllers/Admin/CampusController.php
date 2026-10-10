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
        $facilitiesMaster = \App\Models\Facility::where('status', 1)->orderBy('name')->get();
        $schoolTypes = \App\Models\CampusTypeNew::all();
        $coachingCategories = \App\Models\CoachingCategory::where('status', 1)->orderBy('sort_order')->get();
        return view('admin.organisations.campuses.create', compact('organisation', 'brandTypes', 'exams', 'examCategories', 'facilitiesMaster', 'schoolTypes', 'coachingCategories'));
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
        $facilitiesMaster = \App\Models\Facility::where('status', 1)->orderBy('name')->get();
        $schoolTypes = \App\Models\CampusTypeNew::all();
        $coachingCategories = \App\Models\CoachingCategory::where('status', 1)->orderBy('sort_order')->get();
        return view('admin.organisations.campuses.edit', compact('organisation', 'campus', 'brandTypes', 'exams', 'examCategories', 'facilitiesMaster', 'schoolTypes', 'coachingCategories'));
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
        
        $campus->update($updateData);
        return response()->json(['status' => 'success', 'message' => 'Tab saved']);
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

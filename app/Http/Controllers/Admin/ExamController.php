<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\CasteCategory;
use App\Models\Exam;
use App\Models\ExamStage;
use App\Models\Organisation;
use App\Models\ExamSelectedStage;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class ExamController extends Controller
{
    public function index()
    {
        $exams = Exam::with(['owningOrganisation', 'selectedStages.masterStage'])->latest()->paginate(10);
        return view('admin.exams.index', compact('exams'));
    }

    public function create()
    {
        $organisations = Organisation::where('status', true)->select('id', 'name')->get();
        $examCategories = Exam::whereNotNull('exam_category')->get()
            ->pluck('exam_category')
            ->flatten()
            ->unique()
            ->filter()
            ->values();
        $allStages = ExamStage::where('status', true)->orderBy('sort_order')->get();
        $casteCategories = CasteCategory::where('status', true)->orderBy('name')->get();

        return view('admin.exams.create', compact('organisations', 'examCategories', 'allStages', 'casteCategories'));
    }

    // public function store(Request $request)
    // {
    //     $data = $request->validate([
    //         'name' => 'required|string|max:255',
    //         'slug' => 'required|string|unique:exams,slug',
    //         'exam_category' => 'required|string',
    //         'organisation_id' => 'required|exists:organisations,id',
    //         'exam_frequency' => 'required|string',
    //         'exam_level' => 'required|string',
    //         'exam_mode' => 'required|string',
    //         'exam_duration' => 'required|string',
    //         'official_website' => 'nullable|url',
    //         'description' => 'required|string',
    //         'exam_pattern' => 'nullable|string',
    //         'eligibility_criteria' => 'nullable|string',
    //         'syllabus' => 'nullable|string',
    //         'selection_process' => 'nullable|string',
    //         'logo' => 'nullable|image|max:2048',
    //         'cover_image' => 'nullable|image|max:2048',
    //         'status' => 'required|boolean',
    //     ]);

    //     if ($request->hasFile('logo')) {
    //         $data['logo'] = $request->file('logo')->store('exams/logos', 'public');
    //     }

    //     if ($request->hasFile('cover_image')) {
    //         $data['cover_image'] = $request->file('cover_image')->store('exams/covers', 'public');
    //     }

    //     $exam = Exam::create($data);

    //     return redirect()->route('admin.exams.index', $exam->id)->with('success', 'Exam created successfully!');
    // }
    public function store(Request $request)
    {
        $request->merge(['slug' => Str::slug($request->name)]);

        $request->validate([
            'name' => 'required|string',
            'slug' => 'required|string|unique:exams,slug',
            'exam_category' => 'nullable|array',
            'registration_fee_structure' => 'nullable|array',
            'registration_fee_structure.*.categories' => 'exclude_unless:registration_fee_required,1|required|array',
            'registration_fee_structure.*.amount' => 'exclude_unless:registration_fee_required,1|required|numeric',
            'late_fee_rules' => 'nullable|array',
            'late_fee_rules.*.condition' => 'exclude_unless:late_registration_allowed,1|required|string',
            'late_fee_rules.*.penalty_amount' => 'exclude_unless:late_registration_allowed,1|required|numeric',
            'security_deposit_structure' => 'nullable|array',
            'security_deposit_structure.*.candidate_categories' => 'exclude_unless:security_deposit_required,1|required|array',
            'security_deposit_structure.*.amount' => 'exclude_unless:security_deposit_required,1|required|numeric',
            'forfeiture_scenarios' => 'nullable|array',
            'forfeiture_scenarios.*.scenario' => 'exclude_unless:security_deposit_required,1|required|string',
            'payment_modes_allowed' => 'nullable|array',
            'sessions' => 'nullable|array',
            'sessions.*.academic_year' => 'required_with:sessions|string',
            'sessions.*.exam_start_date' => 'nullable|date',
            'sessions.*.exam_end_date' => 'nullable|date|after_or_equal:sessions.*.exam_start_date',
        ]);

        // -----------------------------
        // Base Data
        // -----------------------------
        $data = $request->except('_token', 'sessions');

        // -----------------------------
        // Slug
        // -----------------------------
        $data['slug'] = Str::slug($request->name);

        // -----------------------------
        // Checkbox Defaults
        // -----------------------------
        $booleanFields = [
            'gap_year_allowed',
            'reservation_applicable',
            'negative_marking',
            'previous_year_question_papers_available',
            'normalization_applied',
            'counselling_conducted',
            'exam_discontinued',
            'featured_exam',
            'has_stages',
            'registration_fee_required',
            'late_registration_allowed',
            'security_deposit_required',
            'transaction_charges_applicable',
            'partial_refund_allowed'
        ];

        foreach ($booleanFields as $field) {
            $data[$field] = $request->has($field) ? 1 : 0;
        }

        // -----------------------------
        // Image Uploads
        // -----------------------------
        if ($request->hasFile('logo')) {
            $file = $request->file('logo');
            $name = time() . '_' . $file->getClientOriginalName();
            $file->move(public_path('media/exams/logos'), $name);
            $data['logo'] = 'media/exams/logos/' . $name;
        }

        if ($request->hasFile('cover_image')) {
            $file = $request->file('cover_image');
            $name = time() . '_' . $file->getClientOriginalName();
            $file->move(public_path('media/exams/covers'), $name);
            $data['cover_image'] = 'media/exams/covers/' . $name;
        }

        if ($request->hasFile('syllabus_pdf')) {
            $file = $request->file('syllabus_pdf');
            $name = time() . '_syllabus_' . $file->getClientOriginalName();
            $file->move(public_path('media/exams/syllabuses'), $name);
            $data['syllabus_url'] = 'media/exams/syllabuses/' . $name;
        }

        // -----------------------------
        // Array Handling
        // -----------------------------
        if ($request->filled('subjects_covered_raw')) {
            $data['subjects_covered'] = array_map(
                'trim',
                explode(',', $request->subjects_covered_raw)
            );
            unset($data['subjects_covered_raw']);
        }

        if ($request->has('subjects_required')) {
            $data['subjects_required'] = $request->subjects_required;
        }

        // -----------------------------
        // Audit Fields
        // -----------------------------
        $data['created_by'] = auth()->id();
        $data['updated_by'] = auth()->id();

        // -----------------------------
        // Create Exam
        // -----------------------------
        $exam = Exam::create($data);

        // -----------------------------
        // Sessions Save
        // -----------------------------
        if ($request->has('sessions')) {
            foreach ($request->sessions as $session) {
                if (!empty($session['academic_year'])) {
                    $exam->sessions()->create($session);
                }
            }
        }

        // Handle Exam Stages
        if ($request->has('has_stages')) {
            $selectedStages = $request->input('selected_stages', []);
            foreach ($selectedStages as $index => $stageId) {
                $exam->selectedStages()->create([
                    'exam_stage_id' => $stageId,
                    'sort_order' => $index + 1,
                    'status' => true
                ]);
            }
        }

        return redirect()
            ->route('admin.exams.index')
            ->with('success', 'Exam created successfully!');
    }

    public function edit(Exam $exam)
    {
        $organisations = Organisation::where('status', true)->select('id', 'name')->get();
        $examCategories = Exam::whereNotNull('exam_category')->get()
            ->pluck('exam_category')
            ->flatten()
            ->unique()
            ->filter()
            ->values();
        $exams = Exam::select('id', 'name', 'exam_category')->get();

        $selectedStages = $exam->selectedStages->pluck('exam_stage_id')->toArray();
        $allStages = ExamStage::where('status', true)->orderBy('sort_order')->get();
        $casteCategories = CasteCategory::where('status', true)->orderBy('name')->get();

        return view('admin.exams.edit', compact('exam', 'organisations', 'examCategories', 'exams', 'selectedStages', 'allStages', 'casteCategories'));
    }

    public function update(Request $request, Exam $exam)
    {
        if ($request->filled('name')) {
            $request->merge(['slug' => Str::slug($request->name)]);
        }

        $request->validate([
            'name' => 'required|string',
            'slug' => 'required|string|unique:exams,slug,' . $exam->id,
            'exam_category' => 'nullable|array',
            'registration_fee_structure' => 'nullable|array',
            'registration_fee_structure.*.categories' => 'exclude_unless:registration_fee_required,1|required|array',
            'registration_fee_structure.*.amount' => 'exclude_unless:registration_fee_required,1|required|numeric',
            'late_fee_rules' => 'nullable|array',
            'late_fee_rules.*.condition' => 'exclude_unless:late_registration_allowed,1|required|string',
            'late_fee_rules.*.penalty_amount' => 'exclude_unless:late_registration_allowed,1|required|numeric',
            'security_deposit_structure' => 'nullable|array',
            'security_deposit_structure.*.candidate_categories' => 'exclude_unless:security_deposit_required,1|required|array',
            'security_deposit_structure.*.amount' => 'exclude_unless:security_deposit_required,1|required|numeric',
            'forfeiture_scenarios' => 'nullable|array',
            'forfeiture_scenarios.*.scenario' => 'exclude_unless:security_deposit_required,1|required|string',
            'payment_modes_allowed' => 'nullable|array',
            'sessions' => 'nullable|array',
            'sessions.*.academic_year' => 'required_with:sessions|string',
            'sessions.*.exam_start_date' => 'nullable|date',
            'sessions.*.exam_end_date' => 'nullable|date|after_or_equal:sessions.*.exam_start_date',
        ]);

        $data = $request->except('_token', '_method', 'sessions', 'logo', 'cover_image', 'syllabus_pdf');

        // Handle boolean fields
        $booleans = [
            'gap_year_allowed',
            'reservation_applicable',
            'negative_marking',
            'previous_year_question_papers_available',
            'normalization_applied',
            'counselling_conducted',
            'exam_discontinued',
            'featured_exam',
            'has_stages',
            'registration_fee_required',
            'late_registration_allowed',
            'security_deposit_required',
            'transaction_charges_applicable',
            'partial_refund_allowed'
        ];

        foreach ($booleans as $field) {
            $data[$field] = $request->has($field) ? 1 : 0;
        }

        // Image uploads
        if ($request->hasFile('logo')) {
            $file = $request->file('logo');
            $name = time() . '_' . $file->getClientOriginalName();
            $file->move(public_path('media/exams/logos'), $name);
            $data['logo'] = 'media/exams/logos/' . $name;
        }

        if ($request->hasFile('cover_image')) {
            $file = $request->file('cover_image');
            $name = time() . '_' . $file->getClientOriginalName();
            $file->move(public_path('media/exams/covers'), $name);
            $data['cover_image'] = 'media/exams/covers/' . $name;
        }

        if ($request->hasFile('syllabus_pdf')) {
            $file = $request->file('syllabus_pdf');
            $name = time() . '_syllabus_' . $file->getClientOriginalName();
            $file->move(public_path('media/exams/syllabuses'), $name);
            $data['syllabus_url'] = 'media/exams/syllabuses/' . $name;
        }

        // Handle subjects covered
        if ($request->filled('subjects_covered_raw')) {
            $data['subjects_covered'] = array_map('trim', explode(',', $request->subjects_covered_raw));
            unset($data['subjects_covered_raw']);
        }

        if ($request->has('subjects_required')) {
            $data['subjects_required'] = $request->subjects_required;
        }

        $data['updated_by'] = auth()->id();

        $exam->update($data);

        // Update sessions (simple logic)
        if ($request->has('sessions')) {
            $exam->sessions()->delete(); // Clear old ones for simplicity
            foreach ($request->sessions as $session) {
                if (!empty($session['academic_year'])) {
                    $exam->sessions()->create($session);
                }
            }
        }

        // Handle Exam Stages
        if ($request->has('has_stages')) {
            $selectedStagesIds = $request->input('selected_stages', []);
            $exam->selectedStages()->delete();

            foreach ($selectedStagesIds as $index => $stageId) {
                $exam->selectedStages()->create([
                    'exam_stage_id' => $stageId,
                    'sort_order' => $index + 1,
                    'status' => true
                ]);
            }
        } else {
            $exam->selectedStages()->delete();
        }

        return redirect()->route('admin.exams.index')->with('success', 'Exam updated successfully!');
    }

    public function destroy(Exam $exam)
    {
        $exam->delete();
        return redirect()->route('admin.exams.index')->with('success', 'Exam deleted successfully!');
    }
}

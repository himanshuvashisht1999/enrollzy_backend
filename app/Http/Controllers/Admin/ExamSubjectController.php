<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Exam;
use App\Models\ExamStage;
use App\Models\ExamSubject;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class ExamSubjectController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $query = ExamSubject::with(['exam', 'examStage']);

        if ($request->filled('exam_id')) {
            $query->where('exam_id', $request->exam_id);
        }

        if ($request->filled('exam_stage_id')) {
            $query->where('exam_stage_id', $request->exam_stage_id);
        }

        $subjects = $query->latest()->paginate(20);
        $exams = Exam::where('has_stages', true)->get();
        $stages = ExamStage::all();

        return view('admin.exams.subjects.index', compact('subjects', 'exams', 'stages'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(Request $request)
    {
        $selectedExam = null;
        $selectedStage = null;

        if ($request->filled('exam_id')) {
            $selectedExam = Exam::find($request->exam_id);
        }

        if ($request->filled('exam_stage_id')) {
            $selectedStage = ExamStage::find($request->exam_stage_id);
        }

        $exams = Exam::where('has_stages', true)->get();
        $stages = ExamStage::all();

        return view('admin.exams.subjects.create', compact('exams', 'stages', 'selectedExam', 'selectedStage'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'exam_id' => 'required|exists:exams,id',
            'exam_stage_id' => 'required|exists:exam_stages,id',
            'subject_name' => 'required|string|max:255',
            'subject_type' => 'required|string',
            'display_order' => 'nullable|integer',
        ]);

        $data = $request->all();

        // Handle boolean fields
        $booleanFields = [
            'subject_choice_required',
            'negative_marking',
            'normalization_applied',
            'background_subject_required',
            'subject_contributes_to_merit',
            'subject_registration_required'
        ];

        foreach ($booleanFields as $field) {
            $data[$field] = $request->has($field);
        }

        // Handle JSON/Array fields
        $arrayFields = [
            'applicable_categories',
            'subject_mediums_available',
            'reference_books',
            'paper_names',
            'applicable_exam_stages',
            'focus_keywords'
        ];

        foreach ($arrayFields as $field) {
            if ($request->has($field)) {
                $data[$field] = (array) $request->input($field);
            } elseif ($request->has($field . '_raw')) {
                $data[$field] = array_map('trim', explode(',', $request->input($field . '_raw')));
            }
        }

        // Syllabus Structure handling (complex repeater)
        if ($request->has('syllabus')) {
            $data['syllabus_structure'] = $request->input('syllabus');
        }

        $data['created_by'] = auth()->id();
        $data['last_updated_on'] = now();

        ExamSubject::create($data);

        return redirect()->route('admin.exam-subjects.index', [
            'exam_id' => $request->exam_id,
            'exam_stage_id' => $request->exam_stage_id
        ])->with('success', 'Subject created successfully.');
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(ExamSubject $examSubject)
    {
        $exams = Exam::where('has_stages', true)->get();
        $stages = ExamStage::all();
        $selectedExam = $examSubject->exam;
        $selectedStage = $examSubject->examStage;

        return view('admin.exams.subjects.edit', compact('examSubject', 'exams', 'stages', 'selectedExam', 'selectedStage'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, ExamSubject $examSubject)
    {
        $request->validate([
            'exam_id' => 'required|exists:exams,id',
            'exam_stage_id' => 'required|exists:exam_stages,id',
            'subject_name' => 'required|string|max:255',
            'subject_type' => 'required|string',
            'display_order' => 'nullable|integer',
        ]);

        $data = $request->all();

        // Handle boolean fields
        $booleanFields = [
            'subject_choice_required',
            'negative_marking',
            'normalization_applied',
            'background_subject_required',
            'subject_contributes_to_merit',
            'subject_registration_required'
        ];

        foreach ($booleanFields as $field) {
            $data[$field] = $request->has($field);
        }

        // Handle JSON/Array fields
        $arrayFields = [
            'applicable_categories',
            'subject_mediums_available',
            'reference_books',
            'paper_names',
            'applicable_exam_stages',
            'focus_keywords'
        ];

        foreach ($arrayFields as $field) {
            if ($request->has($field)) {
                $data[$field] = (array) $request->input($field);
            } elseif ($request->has($field . '_raw')) {
                $data[$field] = array_map('trim', explode(',', $request->input($field . '_raw')));
            }
        }

        // Syllabus Structure handling
        if ($request->has('syllabus')) {
            $data['syllabus_structure'] = $request->input('syllabus');
        }

        $data['last_updated_on'] = now();

        $examSubject->update($data);

        return redirect()->route('admin.exam-subjects.index', [
            'exam_id' => $request->exam_id,
            'exam_stage_id' => $request->exam_stage_id
        ])->with('success', 'Subject updated successfully.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(ExamSubject $examSubject)
    {
        $examId = $examSubject->exam_id;
        $stageId = $examSubject->exam_stage_id;

        $examSubject->delete();

        return redirect()->route('admin.exam-subjects.index', [
            'exam_id' => $examId,
            'exam_stage_id' => $stageId
        ])->with('success', 'Subject deleted successfully.');
    }
}

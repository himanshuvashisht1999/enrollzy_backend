<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Counselling;
use App\Models\Exam;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Schema;

class CounsellingController extends Controller
{
    public function index(Exam $exam)
    {
        $counsellings = Counselling::where('exam_id', $exam->id)->orderBy('created_at', 'desc')->get();
        return view('admin.exams.counsellings.index', compact('exam', 'counsellings'));
    }

    public function create(Exam $exam)
    {
        return view('admin.exams.counsellings.create', compact('exam'));
    }

    public function store(Request $request, Exam $exam)
    {
        $input = $request->all();
        $input['exam_id'] = $exam->id;

        Counselling::create($input);

        return redirect()->route('admin.exams.counsellings.index', $exam->id)
            ->with('success', 'Counselling created successfully');
    }

    public function edit(Exam $exam, Counselling $counselling)
    {
        return view('admin.exams.counsellings.edit', compact('exam', 'counselling'));
    }

    public function update(Request $request, Exam $exam, Counselling $counselling)
    {
        $input = $request->all();

        // Handle slug: if empty, let model handle it
        if (array_key_exists('slug', $input) && empty($input['slug'])) {
            unset($input['slug']);
        }

        $counselling->update($input);

        return redirect()->route('admin.exams.counsellings.index', $exam->id)
            ->with('success', 'Counselling updated successfully');
    }

    public function destroy(Exam $exam, Counselling $counselling)
    {
        $counselling->delete();
        return redirect()->route('admin.exams.counsellings.index', $exam->id)
            ->with('success', 'Counselling deleted successfully');
    }

    public function storeDraft(Request $request, Exam $exam)
    {
        $request->validate([
            'counselling_name' => 'required|string|max:255',
            'conducting_authority_name' => 'required|string',
            'counselling_type' => 'required|string',
        ]);

        $input = $request->all();
        $input['exam_id'] = $exam->id;

        // Handle slug: if empty, let model handle it
        if (array_key_exists('slug', $input) && empty($input['slug'])) {
            unset($input['slug']);
        }

        $counselling = Counselling::create($input);

        return response()->json([
            'status' => 'success',
            'counselling_id' => $counselling->id,
            'message' => 'Draft created successfully'
        ]);
    }

    /**
     * Batch save multiple fields for a tab
     */
    public function autosaveTab(Request $request, $examId, $id)
    {
        $counselling = Counselling::findOrFail($id);
        $data = $request->all();
        
        // Handle slug: if empty, don't update it to avoid null constraint violation
        if (array_key_exists('slug', $data) && empty($data['slug'])) {
            unset($data['slug']);
        }

        // Remove internal Laravel fields and non-db fields
        unset($data['_token']);
        
        $booleans = [
            'domicile_required', 'minimum_exam_qualification_required', 'choice_locking_required',
            'original_documents_required_at_reporting', 'registration_fee_required',
            'late_registration_allowed', 'security_deposit_required', 'transaction_charges_applicable',
            'partial_refund_allowed'
        ];

        $updateData = [];
        foreach ($data as $key => $value) {
            if (Schema::hasColumn('counsellings', $key)) {
                if (in_array($key, $booleans)) {
                    $updateData[$key] = ($value === 'true' || $value === 1 || $value === '1' || $value === true) ? 1 : 0;
                } elseif ($key === 'number_of_rounds') {
                    $updateData[$key] = $value ?: 1;
                } elseif ($key === 'data_confidence_score') {
                    $updateData[$key] = $value ?: 100;
                } else {
                    $updateData[$key] = $value;
                }
            }
        }
        
        $counselling->update($updateData);
        return response()->json(['status' => 'success', 'message' => 'Tab saved']);
    }

    /**
     * Legacy single field autosave (optional use)
     */
    public function autosave(Request $request, $examId, $id)
    {
        $counselling = Counselling::findOrFail($id);
        $field = $request->field;
        $value = $request->value;

        if (Schema::hasColumn('counsellings', $field)) {
            $counselling->update([$field => $value]);
        }

        return response()->json(['status' => 'success']);
    }
}

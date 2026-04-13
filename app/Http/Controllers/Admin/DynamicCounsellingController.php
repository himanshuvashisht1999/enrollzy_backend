<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Counselling;
use App\Models\DynamicExam;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Schema;

class DynamicCounsellingController extends Controller
{
    public function index(DynamicExam $dynamicExam)
    {
        $counsellings = Counselling::where('dynamic_exam_id', $dynamicExam->id)
            ->orderBy('created_at', 'desc')->get();

        // Pass as $exam alias so existing views work
        return view('admin.dynamic-exams.counsellings.index', compact('dynamicExam', 'counsellings'));
    }

    public function create(DynamicExam $dynamicExam)
    {
        return view('admin.dynamic-exams.counsellings.create', compact('dynamicExam'));
    }

    public function store(Request $request, DynamicExam $dynamicExam)
    {
        $input = $request->all();
        $input['dynamic_exam_id'] = $dynamicExam->id;
        $input['exam_id'] = null;

        if (array_key_exists('slug', $input) && empty($input['slug'])) {
            unset($input['slug']);
        }

        Counselling::create($input);

        return redirect()->route('admin.dynamic-exams.counsellings.index', $dynamicExam->id)
            ->with('success', 'Counselling created successfully');
    }

    public function edit(DynamicExam $dynamicExam, Counselling $counselling)
    {
        return view('admin.dynamic-exams.counsellings.edit', compact('dynamicExam', 'counselling'));
    }

    public function update(Request $request, DynamicExam $dynamicExam, Counselling $counselling)
    {
        $input = $request->all();

        if (array_key_exists('slug', $input) && empty($input['slug'])) {
            unset($input['slug']);
        }

        $counselling->update($input);

        return redirect()->route('admin.dynamic-exams.counsellings.index', $dynamicExam->id)
            ->with('success', 'Counselling updated successfully');
    }

    public function destroy(DynamicExam $dynamicExam, Counselling $counselling)
    {
        $counselling->delete();
        return redirect()->route('admin.dynamic-exams.counsellings.index', $dynamicExam->id)
            ->with('success', 'Counselling deleted successfully');
    }

    public function storeDraft(Request $request, DynamicExam $dynamicExam)
    {
        $request->validate([
            'counselling_name' => 'required|string|max:255',
            'conducting_authority_name' => 'required|string',
            'counselling_type' => 'required|string',
        ]);

        $input = $request->all();
        $input['dynamic_exam_id'] = $dynamicExam->id;
        $input['exam_id'] = null;

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

    public function autosaveTab(Request $request, $dynamicExamId, $id)
    {
        $counselling = Counselling::findOrFail($id);
        $data = $request->all();

        if (array_key_exists('slug', $data) && empty($data['slug'])) {
            unset($data['slug']);
        }

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
}

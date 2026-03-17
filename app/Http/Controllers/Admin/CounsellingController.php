<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Counselling;
use App\Models\Exam;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class CounsellingController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Exam $exam)
    {
        $counsellings = Counselling::where('exam_id', $exam->id)->get();
        return view('admin.exams.counsellings.index', compact('exam', 'counsellings'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(Exam $exam)
    {
        return view('admin.exams.counsellings.create', compact('exam'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request, Exam $exam)
    {
        $validated = $request->validate([
            'counselling_name' => 'required|string|max:255',
            'counselling_type' => 'required|string',
            'conducting_authority_name' => 'required|string',
            'slug' => 'nullable|string|unique:counsellings,slug',
            'counselling_year' => 'nullable|string',
            'registration_fee_structure' => 'nullable|array',
            'registration_fee_structure.*.categories' => 'exclude_unless:registration_fee_required,1|required|array',
            'registration_fee_structure.*.amount' => 'exclude_unless:registration_fee_required,1|required|numeric',
            'late_fee_rules' => 'nullable|array',
            'late_fee_rules.*.condition' => 'exclude_unless:late_registration_allowed,1|required|string',
            'late_fee_rules.*.penalty_amount' => 'exclude_unless:late_registration_allowed,1|required|numeric',
            'security_deposit_structure' => 'nullable|array',
            'security_deposit_structure.*.candidate_categories' => 'exclude_unless:security_deposit_required,1|required|array',
            'security_deposit_structure.*.amount' => 'exclude_unless:security_deposit_required,1|required|numeric',
            'round_specific_fee_rules' => 'nullable|array',
            'round_specific_fee_rules.*.round_name' => 'required_with:round_specific_fee_rules|string',
            'forfeiture_scenarios' => 'nullable|array',
            'forfeiture_scenarios.*.scenario' => 'exclude_unless:security_deposit_required,1|required|string',
            'payment_modes_allowed' => 'nullable|array',
        ]);

        $input = $request->all();
        $input['exam_id'] = $exam->id;

        // Handle Checkboxes (Boolean fields)
        $booleans = [
            'domicile_required',
            'minimum_exam_qualification_required',
            'choice_locking_required',
            'original_documents_required_at_reporting',
            'registration_fee_required',
            'late_registration_allowed',
            'security_deposit_required',
            'transaction_charges_applicable',
            'partial_refund_allowed',
        ];

        foreach ($booleans as $field) {
            $input[$field] = $request->has($field) ? 1 : 0;
        }

        // Handle JSON Arrays that might come as null
        // (Eloquent casts handles array serialization, but better to ensure array type)
        // No explicit handling needed if form uses name="field[]" and sends array.

        // Create
        $counselling = Counselling::create($input);

        return redirect()->route('admin.exams.counsellings.index', $exam->id)
            ->with('success', 'Counselling created successfully');
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Exam $exam, Counselling $counselling)
    {
        return view('admin.exams.counsellings.edit', compact('exam', 'counselling'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Exam $exam, Counselling $counselling)
    {
        $validated = $request->validate([
            'counselling_name' => 'required|string|max:255',
            'counselling_type' => 'required|string',
            'conducting_authority_name' => 'required|string',
            'slug' => 'nullable|string|unique:counsellings,slug,' . $counselling->id,
            'counselling_year' => 'nullable|string',
            'registration_fee_structure' => 'nullable|array',
            'registration_fee_structure.*.categories' => 'exclude_unless:registration_fee_required,1|required|array',
            'registration_fee_structure.*.amount' => 'exclude_unless:registration_fee_required,1|required|numeric',
            'late_fee_rules' => 'nullable|array',
            'late_fee_rules.*.condition' => 'exclude_unless:late_registration_allowed,1|required|string',
            'late_fee_rules.*.penalty_amount' => 'exclude_unless:late_registration_allowed,1|required|numeric',
            'security_deposit_structure' => 'nullable|array',
            'security_deposit_structure.*.candidate_categories' => 'exclude_unless:security_deposit_required,1|required|array',
            'security_deposit_structure.*.amount' => 'exclude_unless:security_deposit_required,1|required|numeric',
            'round_specific_fee_rules' => 'nullable|array',
            'round_specific_fee_rules.*.round_name' => 'required_with:round_specific_fee_rules|string',
            'forfeiture_scenarios' => 'nullable|array',
            'forfeiture_scenarios.*.scenario' => 'exclude_unless:security_deposit_required,1|required|string',
            'payment_modes_allowed' => 'nullable|array',
        ]);

        $input = $request->all();

        // Handle Checkboxes (Boolean fields)
        $booleans = [
            'domicile_required',
            'minimum_exam_qualification_required',
            'choice_locking_required',
            'original_documents_required_at_reporting',
            'registration_fee_required',
            'late_registration_allowed',
            'security_deposit_required',
            'transaction_charges_applicable',
            'partial_refund_allowed',
        ];

        foreach ($booleans as $field) {
            $input[$field] = $request->has($field) ? 1 : 0;
        }

        $counselling->update($input);

        return redirect()->route('admin.exams.counsellings.index', $exam->id)
            ->with('success', 'Counselling updated successfully');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Exam $exam, Counselling $counselling)
    {
        $counselling->delete();
        return redirect()->route('admin.exams.counsellings.index', $exam->id)
            ->with('success', 'Counselling deleted successfully');
    }
}

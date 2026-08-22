<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

use App\Models\TargetLead;
use App\Models\Admin;

class TargetLeadController extends Controller
{
    public function index()
    {
        $targets = TargetLead::with('staff')->latest()->get();
        return view('admin.target_leads.index', compact('targets'));
    }

    public function create()
    {
        $staffMembers = Admin::where('status', 1)->get();
        return view('admin.target_leads.create', compact('staffMembers'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'year' => 'required|integer',
            'month' => 'required|string',
            'month_target_calling' => 'required|integer',
            'month_target_admissions' => 'required|integer',
            'staff_ids' => 'required|array',
            'staff_ids.*' => 'exists:admin,id'
        ]);

        foreach ($request->staff_ids as $staff_id) {
            TargetLead::updateOrCreate(
                [
                    'staff_id' => $staff_id,
                    'year' => $request->year,
                    'month' => $request->month,
                ],
                [
                    'month_target_calling' => $request->month_target_calling,
                    'month_target_admissions' => $request->month_target_admissions,
                ]
            );
        }

        return redirect()->route('admin.target-leads.index')->with('success', 'Targets assigned successfully.');
    }

    public function edit(TargetLead $targetLead)
    {
        $staffMembers = Admin::where('status', 1)->get();
        return view('admin.target_leads.edit', compact('targetLead', 'staffMembers'));
    }

    public function update(Request $request, TargetLead $targetLead)
    {
        $request->validate([
            'year' => 'required|integer',
            'month' => 'required|string',
            'month_target_calling' => 'required|integer',
            'month_target_admissions' => 'required|integer',
            'staff_id' => [
                'required',
                'exists:admin,id',
                \Illuminate\Validation\Rule::unique('target_leads')->where(function ($query) use ($request) {
                    return $query->where('year', $request->year)
                                 ->where('month', $request->month);
                })->ignore($targetLead->id),
            ]
        ], [
            'staff_id.unique' => 'A target for this staff member already exists for the selected year and month.'
        ]);

        $targetLead->update($request->all());

        return redirect()->route('admin.target-leads.index')->with('success', 'Target updated successfully.');
    }

    public function destroy(TargetLead $targetLead)
    {
        $targetLead->delete();
        return redirect()->route('admin.target-leads.index')->with('success', 'Target deleted successfully.');
    }
}

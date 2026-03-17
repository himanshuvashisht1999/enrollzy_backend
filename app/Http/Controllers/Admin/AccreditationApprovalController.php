<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AccreditationApproval;
use Illuminate\Http\Request;

class AccreditationApprovalController extends Controller
{
    public function index()
    {
        $items = AccreditationApproval::orderBy('sort_order')->orderBy('title')->get();
        return view('admin.accreditation-approvals.index', compact('items'));
    }

    public function create()
    {
        return view('admin.accreditation-approvals.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'status' => 'boolean',
            'sort_order' => 'nullable|integer',
        ]);

        AccreditationApproval::create([
            'title' => $request->title,
            'status' => $request->has('status'),
            'sort_order' => $request->sort_order ?? 0,
        ]);

        return redirect()->route('admin.accreditation-approvals.index')->with('success', 'Accreditation & Approval created successfully.');
    }

    public function edit(AccreditationApproval $accreditationApproval)
    {
        return view('admin.accreditation-approvals.edit', compact('accreditationApproval'));
    }

    public function update(Request $request, AccreditationApproval $accreditationApproval)
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'status' => 'boolean',
            'sort_order' => 'nullable|integer',
        ]);

        $accreditationApproval->update([
            'title' => $request->title,
            'status' => $request->has('status'),
            'sort_order' => $request->sort_order ?? 0,
        ]);

        return redirect()->route('admin.accreditation-approvals.index')->with('success', 'Accreditation & Approval updated successfully.');
    }

    public function destroy(AccreditationApproval $accreditationApproval)
    {
        $accreditationApproval->delete();
        return redirect()->route('admin.accreditation-approvals.index')->with('success', 'Accreditation & Approval deleted successfully.');
    }
}

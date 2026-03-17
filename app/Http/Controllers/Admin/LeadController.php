<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Lead;
use Illuminate\Http\Request;

class LeadController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $query = Lead::query();

        // Filters
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('type')) {
            $query->where('type', $request->type);
        }

        if ($request->filled('search')) {
            $query->where(function($q) use ($request) {
                $q->where('name', 'like', '%' . $request->search . '%')
                  ->orWhere('email', 'like', '%' . $request->search . '%')
                  ->orWhere('phone', 'like', '%' . $request->search . '%');
            });
        }

        $leads = $query->latest()->paginate(15);
        $statuses = ['New', 'Contacted', 'Converted', 'Rejected'];

        return view('admin.leads.index', compact('leads', 'statuses'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|max:255',
            'phone' => 'nullable|string|max:20',
            'subject' => 'nullable|string|max:255',
            'message' => 'required|string',
            'type' => 'nullable|string|in:Student,Expert,Alumni',
            'leadable_id' => 'nullable|integer',
            'leadable_type' => 'nullable|string',
        ]);

        Lead::create($request->all());

        return back()->with('success', 'Your inquiry has been submitted successfully. We will contact you soon.');
    }

    /**
     * Update the status of the lead.
     */
    public function updateStatus(Request $request, Lead $lead)
    {
        $request->validate([
            'status' => 'required|string|in:New,Contacted,Converted,Rejected',
        ]);

        $lead->update(['status' => $request->status]);

        return back()->with('success', 'Lead status updated successfully.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Lead $lead)
    {
        $lead->delete();
        return redirect()->route('leads.index')->with('success', 'Lead deleted successfully.');
    }
}

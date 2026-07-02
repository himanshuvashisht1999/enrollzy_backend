<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\MentorCommission;

class MentorCommissionController extends Controller
{
    public function index()
    {
        $commission = MentorCommission::first();
        return view('admin.mentor.commissions.index', compact('commission'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'commission_percentage' => 'required|numeric|min:0|max:100',
            'priority_1' => 'required|string',
            'priority_2' => 'required|string',
            'priority_3' => 'required|string',
            'priority_4' => 'required|string',
        ]);

        $priority_order = [
            $request->priority_1,
            $request->priority_2,
            $request->priority_3,
            $request->priority_4,
        ];

        if (count(array_unique($priority_order)) !== 4) {
            return back()->withErrors(['priority' => 'Priority levels must be unique.']);
        }

        $commission = MentorCommission::first();
        
        $data = [
            'commission_percentage' => $request->commission_percentage,
            'priority_order' => $priority_order
        ];

        if ($commission) {
            $commission->update($data);
        } else {
            MentorCommission::create($data);
        }

        return redirect()->route('admin.mentor.commissions.index')->with('success', 'General Commission & Priority updated successfully');
    }
}

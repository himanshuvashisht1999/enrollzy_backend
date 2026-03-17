<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\CommissionPolicy;
use App\Models\Expert;
use App\Models\CommissionLog;
use Illuminate\Support\Facades\DB;

class CommissionController extends Controller
{
    public function index()
    {
        // Global Policy
        $globalPolicy = CommissionPolicy::firstOrCreate(
            ['policy_type' => 'global'],
            [
                'commission_type' => 'percentage', 
                'commission_value' => 10,
                'gst_applicable' => true,
                'tds_applicable' => true
            ]
        );

        // Category Policies
        // Use ExpertCategory master table
        $existingCategories = \App\Models\ExpertCategory::where('is_active', true)->get();
        
        $categoryPolicies = CommissionPolicy::where('policy_type', 'category')->get()->keyBy('expert_category_id');

        return view('admin.commission.index', compact('globalPolicy', 'existingCategories', 'categoryPolicies'));
    }

    public function updateGlobal(Request $request)
    {
        $request->validate([
            'commission_type' => 'required|in:percentage,flat_fee',
            'commission_value' => 'required|numeric|min:0',
        ]);

        $policy = CommissionPolicy::where('policy_type', 'global')->firstOrFail();
        
        $oldValue = $policy->toArray();

        $policy->update([
            'commission_type' => $request->commission_type,
            'commission_value' => $request->commission_value,
            'gst_applicable' => $request->has('gst_applicable'),
            'tds_applicable' => $request->has('tds_applicable'),
        ]);

        // Log Change
        CommissionLog::create([
            'entity_type' => 'GlobalPolicy',
            'entity_id' => $policy->id,
            'old_value' => json_encode($oldValue),
            'new_value' => json_encode($policy->toArray()),
            'action_by' => auth()->id(),
            'reason' => 'Admin updated global settings'
        ]);

        return back()->with('success', 'Global commission settings updated successfully.');
    }

    public function updateCategory(Request $request)
    {
        $request->validate([
            'expert_category_id' => 'required|exists:expert_categories,id',
            'commission_type' => 'required|in:percentage,flat_fee',
            'commission_value' => 'required|numeric|min:0',
        ]);
        
        // Fetch name for logging (optional or store only ID)
        $cat = \App\Models\ExpertCategory::find($request->expert_category_id);

        $policy = CommissionPolicy::updateOrCreate(
            ['policy_type' => 'category', 'expert_category_id' => $request->expert_category_id],
            [
                'expert_category' => $cat->name, // Keep legacy name sync if desired, or make nullable
                'commission_type' => $request->commission_type,
                'commission_value' => $request->commission_value,
                'is_active' => true 
            ]
        );

        return back()->with('success', "Commission for {$cat->name} updated.");
    }
}

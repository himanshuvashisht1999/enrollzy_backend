<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Expert;
use Illuminate\Http\Request;
use File;

class ExpertController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $query = Expert::query();

        // Filters
        if ($request->filled('role')) {
            $query->where('role', 'like', '%' . $request->role . '%');
        }

        if ($request->filled('search')) {
            $query->where('name', 'like', '%' . $request->search . '%')
                  ->orWhere('degree', 'like', '%' . $request->search . '%');
        }

        $experts = $query->latest()->paginate(10);
        
        // Get unique roles for filter dropdown
        $roles = Expert::distinct()->pluck('role')->filter()->toArray();

        return view('admin.experts.index', compact('experts', 'roles'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('admin.experts.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required',
            'expert_category_id' => 'required|exists:expert_categories,id',
            'email' => 'required|email|unique:experts,email',
            'password' => 'required|min:6',
            'img' => 'required|image|mimes:jpeg,png,jpg,gif,webp|max:2048',
            // Simple validation for new fields
            'slug' => 'nullable|unique:experts,slug',
            'contact_number' => 'nullable|numeric|digits:10',
        ]);

        $data = $request->all();

        // SYNC ROLE Column for Legacy Support / DB Constraint
        $category = \App\Models\ExpertCategory::find($request->expert_category_id);
        $data['role'] = $category->name;

        // Handle Image
        if ($request->hasFile('img')) {
            $imageName = time() . '.' . $request->img->extension();
            $request->img->move(public_path('uploads/experts'), $imageName);
            $data['img'] = 'uploads/experts/' . $imageName;
            // Also set profile_photo_url if empty
            if(empty($data['profile_photo_url'])) {
                $data['profile_photo_url'] = asset($data['img']);
            }
        }

        // Handle Booleans (Legacy + New)
        $booleans = [
            'one_on_one_counseling', 'group_counseling', 'psychometric_based_counseling', 
            'data_driven_career_mapping', 'goal_oriented_planning', 'flexible_scheduling',
            // New
            'industry_experience', 'doubt_solving_sessions', 'one_to_one_mentoring',
            'verified_student_reviews_only', 'public_contact_allowed', 'profile_claimed'
        ];
        foreach ($booleans as $bool) {
            $data[$bool] = $request->has($bool);
        }

        // Handle Arrays & JSON
        $arrayFields = [
            'subject_specialization', 'other_qualifications', 'certifications', 
            'exams_cleared', 'notable_achievements', 'courses_taught', 
            'target_batches', 'language_of_teaching', 'demo_lecture_videos', 
            'articles_written', 'focus_keywords'
        ];
        foreach ($arrayFields as $field) {
            $data[$field] = $request->input($field, []);
            if ($field === 'other_qualifications') {
                $data[$field] = array_values($data[$field]);
            }
        }
        
        // Handle JSON Textarea
        if ($request->filled('top_rank_students')) {
             $decoded = json_decode($request->top_rank_students, true);
             $data['top_rank_students'] = (json_last_error() === JSON_ERROR_NONE) ? $decoded : [];
        } else {
             $data['top_rank_students'] = [];
        }

        Expert::create($data);

        return redirect()->route('experts.index')->with('success', 'Expert created successfully.');
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Expert $expert)
    {
        $activeCommission = \App\Models\ExpertCommission::where('expert_id', $expert->id)
            ->where('is_active', true)
            ->latest()
            ->first();
            
        return view('admin.experts.edit', compact('expert', 'activeCommission'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Expert $expert)
    {
        $request->validate([
            'name' => 'required',
            'expert_category_id' => 'required|exists:expert_categories,id',
            'email' => 'required|email|unique:experts,email,' . $expert->id,
            'password' => 'nullable|min:6',
            'img' => 'nullable|image|mimes:jpeg,png,jpg,gif,webp|max:2048',
             'slug' => 'nullable|unique:experts,slug,' . $expert->id,
        ]);

        $data = $request->all();

        // SYNC ROLE Column for Legacy Support / DB Constraint
        $category = \App\Models\ExpertCategory::find($request->expert_category_id);
        $data['role'] = $category->name;

        // Handle Password
        if ($request->filled('password')) {
            $data['password'] = $request->password; // Model hashes it
        } else {
            unset($data['password']);
        }

        // Handle Image
        if ($request->hasFile('img')) {
            // Delete old image
            if ($expert->img && File::exists(public_path($expert->img))) {
                File::delete(public_path($expert->img));
            }

            $imageName = time() . '.' . $request->img->extension();
            $request->img->move(public_path('uploads/experts'), $imageName);
            $data['img'] = 'uploads/experts/' . $imageName;
             // Update profile_photo_url if it was using default
             if(empty($data['profile_photo_url'])) {
                $data['profile_photo_url'] = asset($data['img']);
            }
        }

        // Handle Booleans
        $booleans = [
            'one_on_one_counseling', 'group_counseling', 'psychometric_based_counseling', 
            'data_driven_career_mapping', 'goal_oriented_planning', 'flexible_scheduling',
             // New
            'industry_experience', 'doubt_solving_sessions', 'one_to_one_mentoring',
            'verified_student_reviews_only', 'public_contact_allowed', 'profile_claimed'
        ];
        foreach ($booleans as $bool) {
            $data[$bool] = $request->has($bool);
        }

        // Handle Arrays & JSON
        $arrayFields = [
            'subject_specialization', 'other_qualifications', 'certifications', 
            'exams_cleared', 'notable_achievements', 'courses_taught', 
            'target_batches', 'language_of_teaching', 'demo_lecture_videos', 
            'articles_written', 'focus_keywords'
        ];
        foreach ($arrayFields as $field) {
            $data[$field] = $request->input($field, []);
            if ($field === 'other_qualifications') {
                $data[$field] = array_values($data[$field]);
            }
        }

        // Handle JSON Textarea
        if ($request->filled('top_rank_students')) {
             $decoded = json_decode($request->top_rank_students, true);
             $data['top_rank_students'] = (json_last_error() === JSON_ERROR_NONE) ? $decoded : [];
        } else {
             $data['top_rank_students'] = [];
        }

        $expert->update($data);

        return redirect()->route('experts.index')->with('success', 'Expert updated successfully.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Expert $expert)
    {
        if ($expert->img && File::exists(public_path($expert->img))) {
            File::delete(public_path($expert->img));
        }

        $expert->delete();

        return redirect()->route('experts.index')->with('success', 'Expert deleted successfully.');
    }
    public function updateCommission(Request $request, Expert $expert)
    {
        $request->validate([
            'commission_type' => 'required|in:percentage,flat_fee',
            'commission_value' => 'required|numeric|min:0',
            'reason' => 'nullable|string'
        ]);

        // Deactivate old active commissions
        \App\Models\ExpertCommission::where('expert_id', $expert->id)
            ->where('is_active', true)
            ->update(['is_active' => false]);

        // Create new commission logic
        \App\Models\ExpertCommission::create([
            'expert_id' => $expert->id,
            'commission_type' => $request->commission_type,
            'commission_value' => $request->commission_value,
            'reason' => $request->reason,
            'is_active' => true
        ]);

        // Log this change
        \App\Models\CommissionLog::create([
            'entity_type' => 'ExpertCommission',
            'entity_id' => $expert->id, // Logging against Expert ID for easier lookup, or the new commission ID? 
            // Better to log the creation. But for now identifying by Expert ID is fine for "Expert Commission Updated"
            'old_value' => null, 
            'new_value' => json_encode($request->only('commission_type', 'commission_value', 'reason')),
            'action_by' => auth()->id(),
            'reason' => 'Admin updated expert commission'
        ]);

        return back()->with('success', 'Expert specific commission updated.');
    }
}

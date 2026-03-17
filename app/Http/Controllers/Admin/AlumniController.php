<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Alumni;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class AlumniController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $alumni = Alumni::orderBy('sort_order')->orderBy('created_at', 'desc')->paginate(10);
        return view('admin.alumni.index', compact('alumni'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('admin.alumni.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:alumnis,email',
            'password' => 'required|min:6',
            'designation' => 'nullable|string|max:255',
            'company' => 'nullable|string|max:255',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
        ]);

        $data = $request->all();

        // Handle Image
        if ($request->hasFile('image')) {
            $image = $request->file('image');
            $imageName = time() . '_' . $image->getClientOriginalName();
            $image->move(public_path('images/alumni'), $imageName);
            $data['image'] = 'images/alumni/' . $imageName;
        }

        // Handle all Boolean fields
        $booleans = [
            'status', 'placed_in_top_companies', 'alumni_in_govt_civil_services', 'is_mentor',
            'formal_mentorship_available', 'career_guidance_sessions', 'academic_mentoring', 'startup_mentoring',
            'referral_programs_active', 'internship_support_via_alumni', 'campus_hiring_initiated_by_alumni',
            'startup_incubators_led_by_alumni', 'directory_access', 'network_platform_available', 
            'linkedin_integration_active', 'contact_via_portal_active'
        ];
        foreach ($booleans as $bool) {
            $data[$bool] = $request->has($bool);
        }

        Alumni::create($data);

        return redirect()->route('admin.alumni.index')->with('success', 'Alumni member added successfully.');
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Alumni $alumnus)
    {
        return view('admin.alumni.edit', compact('alumnus'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Alumni $alumnus)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:alumnis,email,' . $alumnus->id,
            'password' => 'nullable|min:6',
            'designation' => 'nullable|string|max:255',
            'company' => 'nullable|string|max:255',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
        ]);

        $data = $request->all();

        // Handle Password
        if ($request->filled('password')) {
            $data['password'] = $request->password;
        } else {
            unset($data['password']);
        }

        // Handle Image
        if ($request->hasFile('image')) {
            // Delete old image
            if ($alumnus->image && file_exists(public_path($alumnus->image))) {
                unlink(public_path($alumnus->image));
            }

            $image = $request->file('image');
            $imageName = time() . '_' . $image->getClientOriginalName();
            $image->move(public_path('images/alumni'), $imageName);
            $data['image'] = 'images/alumni/' . $imageName;
        }

        // Handle all Boolean fields
        $booleans = [
            'status', 'placed_in_top_companies', 'alumni_in_govt_civil_services', 'is_mentor',
            'formal_mentorship_available', 'career_guidance_sessions', 'academic_mentoring', 'startup_mentoring',
            'referral_programs_active', 'internship_support_via_alumni', 'campus_hiring_initiated_by_alumni',
            'startup_incubators_led_by_alumni', 'directory_access', 'network_platform_available', 
            'linkedin_integration_active', 'contact_via_portal_active'
        ];
        foreach ($booleans as $bool) {
            $data[$bool] = $request->has($bool);
        }

        $alumnus->update($data);

        return redirect()->route('admin.alumni.index')->with('success', 'Alumni details updated successfully.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Alumni $alumnus)
    {
        if ($alumnus->image && file_exists(public_path($alumnus->image))) {
            unlink(public_path($alumnus->image));
        }
        
        $alumnus->delete();

        return redirect()->route('admin.alumni.index')->with('success', 'Alumni member deleted successfully.');
    }
}

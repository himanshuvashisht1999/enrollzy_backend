<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Student;
use App\Models\Organisation;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class StudentController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $query = Student::query();

        if ($request->has('search')) {
            $search = $request->get('search');
            $query->where('full_name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%")
                  ->orWhere('institute_name', 'like', "%{$search}%");
        }

        if ($request->has('status') && $request->status != '') {
            $query->where('status', $request->status);
        }

        $students = $query->latest()->paginate(10);

        return view('admin.students.index', compact('students'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $organisations = Organisation::where('status', 1)->orderBy('name')->pluck('name', 'id');
        return view('admin.students.create', compact('organisations'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'full_name' => 'required|string|max:255',
            'profile_photo_url' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            'organisation_id' => 'nullable|exists:organisations,id',
            'previous_year_percentage' => 'nullable|numeric|min:0|max:100',
            'average_test_score' => 'nullable|numeric|min:0|max:100',
            'attendance_percentage' => 'nullable|numeric|min:0|max:100',
        ]);

        $data = $request->except(['profile_photo_url', 'img']);

        // Handle Image Upload
        if ($request->hasFile('img')) {
            $imageName = time() . '.' . $request->img->extension();
            $request->img->move(public_path('uploads/students'), $imageName);
            $data['profile_photo_url'] = 'uploads/students/' . $imageName;
        }

        // Handle Booleans
        $booleans = [
            'result_verified', 'would_recommend', 'discussion_forum_participation',
            'contact_visible', 'testimonial_visible', 'consent_for_data_use',
            'profile_indexing_allowed', 'profile_verified'
        ];
        foreach ($booleans as $bool) {
            $data[$bool] = $request->has($bool) ? 1 : 0;
        }

        // Handle Arrays (If they come as array from Select2)
        $arrays = ['rank_trend', 'strengths', 'weak_areas', 'study_groups_joined', 'fields_visible_public'];
        foreach ($arrays as $arr) {
            if ($request->has($arr) && is_array($request->$arr)) {
                $data[$arr] = $request->$arr; 
            }
        }

        Student::create($data);

        return redirect()->route('students.index')->with('success', 'Student profile created successfully.');
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        $student = Student::findOrFail($id);
        $organisations = Organisation::where('status', 1)->orderBy('name')->pluck('name', 'id');
        return view('admin.students.edit', compact('student', 'organisations'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $student = Student::findOrFail($id);

        $request->validate([
            'full_name' => 'required|string|max:255',
            'profile_photo_url' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            'organisation_id' => 'nullable|exists:organisations,id',
             'previous_year_percentage' => 'nullable|numeric|min:0|max:100',
        ]);

        $data = $request->except(['profile_photo_url', 'img']);

        // Handle Image Upload
        if ($request->hasFile('img')) {
            // Delete old image if exists
            if ($student->profile_photo_url && file_exists(public_path($student->profile_photo_url))) {
                @unlink(public_path($student->profile_photo_url));
            }
            $imageName = time() . '.' . $request->img->extension();
            $request->img->move(public_path('uploads/students'), $imageName);
            $data['profile_photo_url'] = 'uploads/students/' . $imageName;
        }

         // Handle Booleans
        $booleans = [
            'result_verified', 'would_recommend', 'discussion_forum_participation',
            'contact_visible', 'testimonial_visible', 'consent_for_data_use',
            'profile_indexing_allowed', 'profile_verified'
        ];
        foreach ($booleans as $bool) {
            $data[$bool] = $request->has($bool) ? 1 : 0;
        }

         // Handle Arrays
        $arrays = ['rank_trend', 'strengths', 'weak_areas', 'study_groups_joined', 'fields_visible_public'];
        foreach ($arrays as $arr) {
             if ($request->has($arr)) {
                 $data[$arr] = $request->$arr; // Will be casted automatically by model
             } else {
                 $data[$arr] = null; // Reset if empty
             }
        }

        $student->update($data);

        return redirect()->route('students.index')->with('success', 'Student profile updated successfully.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $student = Student::findOrFail($id);
        if ($student->profile_photo_url && file_exists(public_path($student->profile_photo_url))) {
            @unlink(public_path($student->profile_photo_url));
        }
        $student->delete();

        return redirect()->route('students.index')->with('success', 'Student profile deleted successfully.');
    }
}

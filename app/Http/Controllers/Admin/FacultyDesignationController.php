<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\FacultyDesignation;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class FacultyDesignationController extends Controller
{
    public function index(Request $request)
    {
        $query = FacultyDesignation::withCount('profiles')->orderBy('hierarchy_order', 'asc')->orderBy('name', 'asc');

        if ($request->filled('category') && $request->category !== 'all') {
            $query->where('institution_category', $request->category);
        }

        $designations = $query->paginate(20)->withQueryString();

        $counts = [
            'total' => FacultyDesignation::count(),
            'university' => FacultyDesignation::where('institution_category', 'university')->count(),
            'school' => FacultyDesignation::where('institution_category', 'school')->count(),
            'coaching' => FacultyDesignation::where('institution_category', 'coaching')->count(),
            'general' => FacultyDesignation::where('institution_category', 'all')->count(),
        ];

        return view('admin.faculty_designations.index', compact('designations', 'counts'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'institution_category' => 'required|string|in:all,university,school,coaching',
            'hierarchy_order' => 'nullable|integer',
            'status' => 'nullable|boolean',
        ]);

        $slug = Str::slug($request->name . '-' . $request->institution_category);
        $counter = 1;
        while (FacultyDesignation::where('slug', $slug)->exists()) {
            $slug = Str::slug($request->name . '-' . $request->institution_category) . '-' . $counter;
            $counter++;
        }

        FacultyDesignation::create([
            'name' => $request->name,
            'slug' => $slug,
            'institution_category' => $request->institution_category,
            'hierarchy_order' => $request->hierarchy_order ?? 0,
            'status' => $request->has('status') ? (bool) $request->status : true,
        ]);

        return redirect()->route('admin.faculty-designations.index')->with('success', 'Designation created successfully.');
    }

    public function update(Request $request, FacultyDesignation $facultyDesignation)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'institution_category' => 'required|string|in:all,university,school,coaching',
            'hierarchy_order' => 'nullable|integer',
            'status' => 'nullable|boolean',
        ]);

        $facultyDesignation->update([
            'name' => $request->name,
            'institution_category' => $request->institution_category,
            'hierarchy_order' => $request->hierarchy_order ?? 0,
            'status' => $request->has('status') ? (bool) $request->status : false,
        ]);

        return redirect()->route('admin.faculty-designations.index')->with('success', 'Designation updated successfully.');
    }

    public function destroy(FacultyDesignation $facultyDesignation)
    {
        if ($facultyDesignation->profiles()->count() > 0) {
            return back()->with('error', 'Cannot delete this designation because faculty profiles are associated with it.');
        }

        $facultyDesignation->delete();
        return redirect()->route('admin.faculty-designations.index')->with('success', 'Designation deleted successfully.');
    }

    public function toggleStatus(FacultyDesignation $facultyDesignation)
    {
        $facultyDesignation->status = !$facultyDesignation->status;
        $facultyDesignation->save();

        return response()->json([
            'success' => true,
            'status' => $facultyDesignation->status,
            'message' => 'Status updated successfully.'
        ]);
    }
}

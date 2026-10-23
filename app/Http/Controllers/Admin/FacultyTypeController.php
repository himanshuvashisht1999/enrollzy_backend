<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\FacultyType;
use Illuminate\Http\Request;

class FacultyTypeController extends Controller
{
    public function index()
    {
        $types = FacultyType::withCount('profiles')->latest()->paginate(20);
        return view('admin.faculty_types.index', compact('types'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255|unique:faculty_types,name',
            'status' => 'nullable|boolean',
        ]);

        FacultyType::create([
            'name' => $request->name,
            'status' => $request->has('status') ? (bool) $request->status : true,
        ]);

        return redirect()->route('admin.faculty-types.index')->with('success', 'Faculty type created successfully.');
    }

    public function update(Request $request, FacultyType $facultyType)
    {
        $request->validate([
            'name' => 'required|string|max:255|unique:faculty_types,name,' . $facultyType->id,
            'status' => 'nullable|boolean',
        ]);

        $facultyType->update([
            'name' => $request->name,
            'status' => $request->has('status') ? (bool) $request->status : false,
        ]);

        return redirect()->route('admin.faculty-types.index')->with('success', 'Faculty type updated successfully.');
    }

    public function destroy(FacultyType $facultyType)
    {
        if ($facultyType->profiles()->count() > 0) {
            return back()->with('error', 'Cannot delete this faculty type because faculty profiles are associated with it.');
        }

        $facultyType->delete();
        return redirect()->route('admin.faculty-types.index')->with('success', 'Faculty type deleted successfully.');
    }

    public function toggleStatus(FacultyType $facultyType)
    {
        $facultyType->status = !$facultyType->status;
        $facultyType->save();

        return response()->json([
            'success' => true,
            'status' => $facultyType->status,
            'message' => 'Status updated successfully.'
        ]);
    }
}

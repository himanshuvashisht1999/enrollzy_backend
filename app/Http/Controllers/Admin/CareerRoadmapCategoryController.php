<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\CareerRoadmapCategory;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class CareerRoadmapCategoryController extends Controller
{
    public function index(Request $request)
    {
        $query = CareerRoadmapCategory::latest();
        if ($request->filled('search')) {
            $query->where('name', 'like', '%' . $request->search . '%');
        }
        $categories = $query->paginate(15)->appends($request->all());
        return view('admin.career_roadmap_categories.index', compact('categories'));
    }

    public function create()
    {
        return view('admin.career_roadmap_categories.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255|unique:career_roadmap_categories',
            'status' => 'nullable|boolean'
        ]);

        CareerRoadmapCategory::create([
            'name' => $request->name,
            'description' => $request->description,
            'status' => $request->has('status') ? 1 : 0,
        ]);

        return redirect()->route('admin.career-roadmap-categories.index')->with('success', 'Category created successfully.');
    }

    public function edit(CareerRoadmapCategory $careerRoadmapCategory)
    {
        return view('admin.career_roadmap_categories.edit', compact('careerRoadmapCategory'));
    }

    public function update(Request $request, CareerRoadmapCategory $careerRoadmapCategory)
    {
        $request->validate([
            'name' => 'required|string|max:255|unique:career_roadmap_categories,name,' . $careerRoadmapCategory->id,
            'status' => 'nullable|boolean'
        ]);

        $careerRoadmapCategory->update([
            'name' => $request->name,
            'description' => $request->description,
            'status' => $request->has('status') ? 1 : 0,
        ]);

        return redirect()->route('admin.career-roadmap-categories.index')->with('success', 'Category updated successfully.');
    }

    public function destroy(CareerRoadmapCategory $careerRoadmapCategory)
    {
        $careerRoadmapCategory->delete();
        return redirect()->route('admin.career-roadmap-categories.index')->with('success', 'Category deleted successfully.');
    }
}

<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\CoachingCategory;

class CoachingCategoryController extends Controller
{
    public function index()
    {
        $categories = CoachingCategory::orderBy('sort_order', 'asc')->get();
        return view('admin.coaching_category.index', compact('categories'));
    }

    public function create()
    {
        return view('admin.coaching_category.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'status' => 'boolean',
            'sort_order' => 'integer',
        ]);

        $data = $request->all();
        $data['status'] = $request->has('status') ? 1 : 0;
        
        CoachingCategory::create($data);

        return redirect()->route('admin.coaching-categories.index')->with('success', 'Coaching Category created successfully.');
    }

    public function edit(CoachingCategory $coachingCategory)
    {
        return view('admin.coaching_category.edit', compact('coachingCategory'));
    }

    public function update(Request $request, CoachingCategory $coachingCategory)
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'status' => 'boolean',
            'sort_order' => 'integer',
        ]);

        $data = $request->all();
        $data['status'] = $request->has('status') ? 1 : 0;
        
        $coachingCategory->update($data);

        return redirect()->route('admin.coaching-categories.index')->with('success', 'Coaching Category updated successfully.');
    }

    public function destroy(CoachingCategory $coachingCategory)
    {
        $coachingCategory->delete();

        return redirect()->route('admin.coaching-categories.index')->with('success', 'Coaching Category deleted successfully.');
    }
}

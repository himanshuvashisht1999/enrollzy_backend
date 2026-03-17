<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\CasteCategory;
use Illuminate\Http\Request;

class CasteCategoryController extends Controller
{
    public function index()
    {
        $casteCategories = CasteCategory::latest()->paginate(15);
        return view('admin.caste_categories.index', compact('casteCategories'));
    }

    public function create()
    {
        return view('admin.caste_categories.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255|unique:caste_categories',
            'status' => 'required|boolean',
        ]);

        CasteCategory::create([
            'name' => $request->name,
            'status' => $request->status,
        ]);

        return redirect()->route('admin.caste-categories.index')->with('success', 'Caste Category created successfully.');
    }

    public function edit(CasteCategory $casteCategory)
    {
        return view('admin.caste_categories.edit', compact('casteCategory'));
    }

    public function update(Request $request, CasteCategory $casteCategory)
    {
        $request->validate([
            'name' => 'required|string|max:255|unique:caste_categories,name,' . $casteCategory->id,
            'status' => 'required|boolean',
        ]);

        $casteCategory->update([
            'name' => $request->name,
            'status' => $request->status,
        ]);

        return redirect()->route('admin.caste-categories.index')->with('success', 'Caste Category updated successfully.');
    }

    public function destroy(CasteCategory $casteCategory)
    {
        $casteCategory->delete();
        return redirect()->route('admin.caste-categories.index')->with('success', 'Caste Category deleted successfully.');
    }
}

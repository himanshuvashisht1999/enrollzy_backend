<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ExpertCategory;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class ExpertCategoryController extends Controller
{
    public function index()
    {
        $categories = ExpertCategory::withCount('experts')->latest()->get();
        return view('admin.expert-categories.index', compact('categories'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|unique:expert_categories,name',
        ]);

        ExpertCategory::create([
            'name' => $request->name,
            'slug' => Str::slug($request->name),
            'is_active' => true
        ]);

        return back()->with('success', 'Category created successfully.');
    }

    public function update(Request $request, ExpertCategory $expertCategory)
    {
        $request->validate([
            'name' => 'required|unique:expert_categories,name,' . $expertCategory->id,
        ]);

        $expertCategory->update([
            'name' => $request->name,
            'slug' => Str::slug($request->name),
            'is_active' => $request->has('is_active')
        ]);

        return back()->with('success', 'Category updated successfully.');
    }

    public function destroy(ExpertCategory $expertCategory)
    {
        if ($expertCategory->experts()->count() > 0) {
            return back()->with('error', 'Cannot delete category with associated experts.');
        }

        $expertCategory->delete();
        return back()->with('success', 'Category deleted successfully.');
    }
}

<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

use App\Models\CommunityCategory;
use Illuminate\Support\Str;

class CommunityCategoryController extends Controller
{
    public function index()
    {
        $categories = CommunityCategory::latest()->paginate(10);
        return view('admin.community.categories.index', compact('categories'));
    }

    public function create()
    {
        $categories = CommunityCategory::whereNull('parent_id')->get();
        return view('admin.community.categories.create', compact('categories'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'parent_id' => 'nullable|exists:community_categories,id'
        ]);

        CommunityCategory::create([
            'name' => $request->name,
            'slug' => Str::slug($request->name),
            'description' => $request->description,
            'parent_id' => $request->parent_id
        ]);

        return redirect()->route('admin.community-categories.index')->with('success', 'Category created successfully.');
    }

    public function edit(CommunityCategory $community_category)
    {
        $categories = CommunityCategory::where('id', '!=', $community_category->id)->whereNull('parent_id')->get();
        return view('admin.community.categories.edit', ['category' => $community_category, 'categories' => $categories]);
    }

    public function update(Request $request, CommunityCategory $community_category)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'parent_id' => 'nullable|exists:community_categories,id'
        ]);

        $community_category->update([
            'name' => $request->name,
            'slug' => Str::slug($request->name),
            'description' => $request->description,
            'parent_id' => $request->parent_id
        ]);

        return redirect()->route('admin.community-categories.index')->with('success', 'Category updated successfully.');
    }

    public function destroy(CommunityCategory $community_category)
    {
        $community_category->delete();
        return redirect()->route('admin.community-categories.index')->with('success', 'Category deleted successfully.');
    }
}

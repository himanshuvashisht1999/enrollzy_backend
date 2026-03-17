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
        return view('admin.community.categories.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
        ]);

        CommunityCategory::create([
            'name' => $request->name,
            'slug' => Str::slug($request->name),
            'description' => $request->description,
        ]);

        return redirect()->route('admin.community-categories.index')->with('success', 'Category created successfully.');
    }

    public function edit(CommunityCategory $community_category)
    {
        return view('admin.community.categories.edit', ['category' => $community_category]);
    }

    public function update(Request $request, CommunityCategory $community_category)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
        ]);

        $community_category->update([
            'name' => $request->name,
            'slug' => Str::slug($request->name),
            'description' => $request->description,
        ]);

        return redirect()->route('admin.community-categories.index')->with('success', 'Category updated successfully.');
    }

    public function destroy(CommunityCategory $community_category)
    {
        $community_category->delete();
        return redirect()->route('admin.community-categories.index')->with('success', 'Category deleted successfully.');
    }
}

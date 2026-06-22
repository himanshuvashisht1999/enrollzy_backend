<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\FaqCategory;
use Illuminate\Http\Request;

class FaqCategoryController extends Controller
{
    public function index(Request $request)
    {
        $query = FaqCategory::with('parent')->latest();
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where('name', 'like', "%{$search}%");
        }
        $categories = $query->paginate(15)->appends($request->all());
        return view('admin.faq_categories.index', compact('categories'));
    }

    public function create()
    {
        $parents = FaqCategory::all();
        return view('admin.faq_categories.create', compact('parents'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'parent_id' => 'nullable|exists:faq_categories,id',
            'status' => 'nullable|boolean',
        ]);

        FaqCategory::create([
            'name' => $request->name,
            'parent_id' => $request->parent_id,
            'status' => $request->has('status') ? 1 : 0,
        ]);

        return redirect()->route('admin.faq-categories.index')->with('success', 'FAQ Category created successfully.');
    }

    public function edit(FaqCategory $faqCategory)
    {
        $parents = FaqCategory::where('id', '!=', $faqCategory->id)->get();
        return view('admin.faq_categories.edit', compact('faqCategory', 'parents'));
    }

    public function update(Request $request, FaqCategory $faqCategory)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'parent_id' => 'nullable|exists:faq_categories,id|not_in:' . $faqCategory->id,
            'status' => 'nullable|boolean',
        ]);

        $faqCategory->update([
            'name' => $request->name,
            'parent_id' => $request->parent_id,
            'status' => $request->has('status') ? 1 : 0,
        ]);

        return redirect()->route('admin.faq-categories.index')->with('success', 'FAQ Category updated successfully.');
    }

    public function destroy(FaqCategory $faqCategory)
    {
        $faqCategory->delete();
        return redirect()->route('admin.faq-categories.index')->with('success', 'FAQ Category deleted successfully.');
    }
}

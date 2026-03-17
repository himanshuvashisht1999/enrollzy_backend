<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\NoteworthyCategory;
use Illuminate\Http\Request;

class NoteworthyCategoryController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $categories = NoteworthyCategory::orderBy('sort_order')->latest()->paginate(10);
        return view('admin.noteworthy-categories.index', compact('categories'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('admin.noteworthy-categories.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'sort_order' => 'required|integer',
            'status' => 'required|boolean',
        ]);

        NoteworthyCategory::create($request->all());

        return redirect()->route('admin.noteworthy-categories.index')
            ->with('success', 'Category created successfully.');
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(NoteworthyCategory $noteworthyCategory)
    {
        return view('admin.noteworthy-categories.edit', compact('noteworthyCategory'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, NoteworthyCategory $noteworthyCategory)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'sort_order' => 'required|integer',
            'status' => 'required|boolean',
        ]);

        $noteworthyCategory->update($request->all());

        return redirect()->route('admin.noteworthy-categories.index')
            ->with('success', 'Category updated successfully.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(NoteworthyCategory $noteworthyCategory)
    {
        $noteworthyCategory->delete();

        return redirect()->route('admin.noteworthy-categories.index')
            ->with('success', 'Category deleted successfully.');
    }
}

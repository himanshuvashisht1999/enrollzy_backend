<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\TrendingCourse;
use Illuminate\Http\Request;

class TrendingCourseController extends Controller
{
    public function index()
    {
        $courses = TrendingCourse::orderBy('sort_order', 'asc')->get();
        return view('admin.trending-courses.index', compact('courses'));
    }

    public function create()
    {
        return view('admin.trending-courses.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'instructor' => 'nullable|string|max:255',
            'price' => 'nullable|string|max:255',
            'rating' => 'nullable|string|max:255',
            'url' => 'nullable|string|max:255',
            'sort_order' => 'required|integer',
            'status' => 'required|boolean',
        ]);

        TrendingCourse::create($request->all());

        return redirect()->route('admin.trending-courses.index')->with('success', 'Trending Course created successfully.');
    }

    public function edit(TrendingCourse $trendingCourse)
    {
        return view('admin.trending-courses.edit', compact('trendingCourse'));
    }

    public function update(Request $request, TrendingCourse $trendingCourse)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'instructor' => 'nullable|string|max:255',
            'price' => 'nullable|string|max:255',
            'rating' => 'nullable|string|max:255',
            'url' => 'nullable|string|max:255',
            'sort_order' => 'required|integer',
            'status' => 'required|boolean',
        ]);

        $trendingCourse->update($request->all());

        return redirect()->route('admin.trending-courses.index')->with('success', 'Trending Course updated successfully.');
    }

    public function destroy(TrendingCourse $trendingCourse)
    {
        $trendingCourse->delete();
        return redirect()->route('admin.trending-courses.index')->with('success', 'Trending Course deleted successfully.');
    }
}

<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\FilteredPage;
use App\Models\CampusTypeNew;
use App\Models\StreamOffered;
use Illuminate\Http\Request;

class FilteredPageController extends Controller
{
    public function index()
    {
        $filteredPages = FilteredPage::all();
        return view('admin.filtered-pages.index', compact('filteredPages'));
    }

    public function create()
    {
        $schoolTypes = CampusTypeNew::where('status', 1)->get();
        $streams = StreamOffered::where('status', 1)->get();
        $coachingCategories = \App\Models\CoachingCategory::where('status', 1)->get();
        $programTypes = \App\Models\ProgramType::where('status', 1)->get();
        $courses = \App\Models\Course::where('status', 1)->orderBy('name')->get();
        return view('admin.filtered-pages.create', compact('schoolTypes', 'streams', 'coachingCategories', 'programTypes', 'courses'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'slug' => 'required|unique:filtered_pages,slug',
            'category' => 'required',
            'course_id' => 'nullable|exists:courses,id',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048'
        ]);

        $data = $request->all();
        if ($request->hasFile('image')) {
            $imageName = time() . '_' . uniqid() . '.' . $request->image->extension();
            $request->image->move(public_path('images/filtered_pages'), $imageName);
            $data['image'] = 'images/filtered_pages/' . $imageName;
        }

        FilteredPage::create($data);

        return redirect()->route('admin.filtered-pages.index')->with('success', 'Filtered Page created successfully.');
    }

    public function edit(FilteredPage $filteredPage)
    {
        $schoolTypes = CampusTypeNew::where('status', 1)->get();
        $streams = StreamOffered::where('status', 1)->get();
        $coachingCategories = \App\Models\CoachingCategory::where('status', 1)->get();
        $programTypes = \App\Models\ProgramType::where('status', 1)->get();
        $courses = \App\Models\Course::where('status', 1)->orderBy('name')->get();
        return view('admin.filtered-pages.edit', compact('filteredPage', 'schoolTypes', 'streams', 'coachingCategories', 'programTypes', 'courses'));
    }

    public function update(Request $request, FilteredPage $filteredPage)
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'slug' => 'required|unique:filtered_pages,slug,' . $filteredPage->id,
            'category' => 'required',
            'course_id' => 'nullable|exists:courses,id',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048'
        ]);

        $data = $request->all();
        if ($request->hasFile('image')) {
            $imageName = time() . '_' . uniqid() . '.' . $request->image->extension();
            $request->image->move(public_path('images/filtered_pages'), $imageName);
            $data['image'] = 'images/filtered_pages/' . $imageName;
        }

        $filteredPage->update($data);

        return redirect()->route('admin.filtered-pages.index')->with('success', 'Filtered Page updated successfully.');
    }

    public function destroy(FilteredPage $filteredPage)
    {
        $filteredPage->delete();

        return redirect()->route('admin.filtered-pages.index')->with('success', 'Filtered Page deleted successfully.');
    }
}

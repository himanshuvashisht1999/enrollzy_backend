<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\FilteredPage;
use App\Models\CampusTypeNew;
use App\Models\StreamOffered;
use App\Models\Course;
use App\Models\CoachingCategory;
use App\Models\ProgramType;
use Illuminate\Http\Request;

class FilteredPageController extends Controller
{
    public function index(Request $request)
    {
        $query = FilteredPage::query()->with([
            'schoolType',
            'stream',
            'course',
            'coachingCategory',
            'programType'
        ]);

        // Keyword Search Filter (title, slug, sub_title, city, state)
        if ($request->filled('search')) {
            $search = trim($request->search);
            $query->where(function ($q) use ($search) {
                $q->where('title', 'like', "%{$search}%")
                  ->orWhere('slug', 'like', "%{$search}%")
                  ->orWhere('sub_title', 'like', "%{$search}%")
                  ->orWhere('city', 'like', "%{$search}%")
                  ->orWhere('state', 'like', "%{$search}%");
            });
        }

        // Category Filter
        if ($request->filled('category')) {
            $query->where('category', $request->category);
        }

        // State Filter
        if ($request->filled('state')) {
            $query->where('state', $request->state);
        }

        // Course Filter
        if ($request->filled('course_id')) {
            $query->where('course_id', $request->course_id);
        }

        // School Type Filter
        if ($request->filled('school_type_id')) {
            $query->where('school_type_id', $request->school_type_id);
        }

        // Stream Filter
        if ($request->filled('stream_id')) {
            $query->where('stream_id', $request->stream_id);
        }

        // Sorting
        $sortBy = $request->get('sort_by', 'id');
        $sortOrder = strtolower($request->get('sort_order', 'desc')) === 'asc' ? 'asc' : 'desc';
        $allowedSorts = ['id', 'title', 'category', 'state', 'created_at'];

        if (in_array($sortBy, $allowedSorts)) {
            $query->orderBy($sortBy, $sortOrder);
        } else {
            $query->latest('id');
        }

        // Per page items
        $perPage = (int) $request->get('per_page', 15);
        if (!in_array($perPage, [10, 15, 25, 50, 100])) {
            $perPage = 15;
        }

        $filteredPages = $query->paginate($perPage)->withQueryString();

        // Datasets for filter dropdowns
        $availableCategories = ['School', 'University', 'Coaching', 'Carrier Road Map', 'Exam', 'Scholarship'];
        $categoriesFromDb = FilteredPage::select('category')->distinct()->whereNotNull('category')->where('category', '!=', '')->pluck('category')->toArray();
        $categories = array_values(array_unique(array_merge($availableCategories, $categoriesFromDb)));

        $states = FilteredPage::select('state')->distinct()->whereNotNull('state')->where('state', '!=', '')->orderBy('state')->pluck('state');
        $courses = Course::where('status', 1)->orderBy('name')->get();
        $schoolTypes = CampusTypeNew::where('status', 1)->orderBy('title')->get();

        return view('admin.filtered-pages.index', compact('filteredPages', 'categories', 'states', 'courses', 'schoolTypes'));
    }

    public function create()
    {
        $schoolTypes = CampusTypeNew::where('status', 1)->get();
        $streams = StreamOffered::where('status', 1)->get();
        $coachingCategories = CoachingCategory::where('status', 1)->get();
        $programTypes = ProgramType::where('status', 1)->get();
        $courses = Course::where('status', 1)->orderBy('sort_order', 'asc')->orderBy('name', 'asc')->get();
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
        $coachingCategories = CoachingCategory::where('status', 1)->get();
        $programTypes = ProgramType::where('status', 1)->get();
        $courses = Course::where('status', 1)->orderBy('sort_order', 'asc')->orderBy('name', 'asc')->get();
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

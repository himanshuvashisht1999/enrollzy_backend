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
        return view('admin.filtered-pages.create', compact('schoolTypes', 'streams'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'slug' => 'required|unique:filtered_pages,slug',
            'category' => 'required',
        ]);

        FilteredPage::create($request->all());

        return redirect()->route('admin.filtered-pages.index')->with('success', 'Filtered Page created successfully.');
    }

    public function edit(FilteredPage $filteredPage)
    {
        $schoolTypes = CampusTypeNew::where('status', 1)->get();
        $streams = StreamOffered::where('status', 1)->get();
        return view('admin.filtered-pages.edit', compact('filteredPage', 'schoolTypes', 'streams'));
    }

    public function update(Request $request, FilteredPage $filteredPage)
    {
        $request->validate([
            'slug' => 'required|unique:filtered_pages,slug,' . $filteredPage->id,
            'category' => 'required',
        ]);

        $filteredPage->update($request->all());

        return redirect()->route('admin.filtered-pages.index')->with('success', 'Filtered Page updated successfully.');
    }

    public function destroy(FilteredPage $filteredPage)
    {
        $filteredPage->delete();

        return redirect()->route('admin.filtered-pages.index')->with('success', 'Filtered Page deleted successfully.');
    }
}

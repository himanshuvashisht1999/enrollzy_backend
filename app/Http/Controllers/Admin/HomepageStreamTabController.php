<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\HomepageStreamTab;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class HomepageStreamTabController extends Controller
{
    public function index()
    {
        $tabs = HomepageStreamTab::orderBy('sort_order', 'asc')->get();
        return view('admin.homepage-stream-tabs.index', compact('tabs'));
    }

    private function getCommonData()
    {
        $organisations = \App\Models\Organisation::where('status', 1)->pluck('name', 'id')->toArray();
        $exams = \App\Models\DynamicExam::where('status', 'Active')->pluck('name', 'id')->toArray();
        $courses = \App\Models\Course::where('status', 1)->pluck('name', 'id')->toArray();
        $states = [
            'Andhra Pradesh', 'Arunachal Pradesh', 'Assam', 'Bihar', 'Chhattisgarh', 'Goa', 'Gujarat', 'Haryana', 
            'Himachal Pradesh', 'Jharkhand', 'Karnataka', 'Kerala', 'Madhya Pradesh', 'Maharashtra', 'Manipur', 
            'Meghalaya', 'Mizoram', 'Nagaland', 'Odisha', 'Punjab', 'Rajasthan', 'Sikkim', 'Tamil Nadu', 
            'Telangana', 'Tripura', 'Uttar Pradesh', 'Uttarakhand', 'West Bengal', 'Andaman and Nicobar Islands', 
            'Chandigarh', 'Dadra and Nagar Haveli and Daman and Diu', 'Delhi', 'Lakshadweep', 'Puducherry'
        ];

        return compact('organisations', 'exams', 'courses', 'states');
    }

    public function create()
    {
        return view('admin.homepage-stream-tabs.create', $this->getCommonData());
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'key' => 'nullable|string|max:255|unique:homepage_stream_tabs,key',
            'keywords' => 'nullable|string',
            'feature_colleges' => 'nullable|array',
            'default_exams' => 'nullable|array',
            'default_states' => 'nullable|array',
            'default_courses' => 'nullable|array',
            'sort_order' => 'required|integer',
            'status' => 'required|boolean',
        ]);

        $key = !empty($request->key) ? Str::slug($request->key) : Str::slug($request->name);

        $parseList = function($input) {
            if (empty($input)) return [];
            if (is_array($input)) return array_values(array_filter(array_map('trim', $input)));
            return array_values(array_filter(array_map('trim', explode(',', $input))));
        };

        HomepageStreamTab::create([
            'key' => $key,
            'name' => $request->name,
            'keywords' => $parseList($request->keywords),
            'feature_colleges' => $request->feature_colleges ?? [],
            'default_exams' => $request->default_exams ?? [],
            'default_states' => $request->default_states ?? [],
            'default_courses' => $request->default_courses ?? [],
            'sort_order' => $request->sort_order,
            'status' => $request->status,
        ]);

        return redirect()->route('admin.homepage-stream-tabs.index')->with('success', 'Stream Tab created successfully.');
    }

    public function edit(HomepageStreamTab $homepageStreamTab)
    {
        return view('admin.homepage-stream-tabs.edit', array_merge(['homepageStreamTab' => $homepageStreamTab], $this->getCommonData()));
    }

    public function update(Request $request, HomepageStreamTab $homepageStreamTab)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'key' => 'nullable|string|max:255|unique:homepage_stream_tabs,key,' . $homepageStreamTab->id,
            'keywords' => 'nullable|string',
            'feature_colleges' => 'nullable|array',
            'default_exams' => 'nullable|array',
            'default_states' => 'nullable|array',
            'default_courses' => 'nullable|array',
            'sort_order' => 'required|integer',
            'status' => 'required|boolean',
        ]);

        $key = !empty($request->key) ? Str::slug($request->key) : Str::slug($request->name);

        $parseList = function($input) {
            if (empty($input)) return [];
            if (is_array($input)) return array_values(array_filter(array_map('trim', $input)));
            return array_values(array_filter(array_map('trim', explode(',', $input))));
        };

        $homepageStreamTab->update([
            'key' => $key,
            'name' => $request->name,
            'keywords' => $parseList($request->keywords),
            'feature_colleges' => $request->feature_colleges ?? [],
            'default_exams' => $request->default_exams ?? [],
            'default_states' => $request->default_states ?? [],
            'default_courses' => $request->default_courses ?? [],
            'sort_order' => $request->sort_order,
            'status' => $request->status,
        ]);

        return redirect()->route('admin.homepage-stream-tabs.index')->with('success', 'Stream Tab updated successfully.');
    }

    public function destroy(HomepageStreamTab $homepageStreamTab)
    {
        $homepageStreamTab->delete();
        return redirect()->route('admin.homepage-stream-tabs.index')->with('success', 'Stream Tab deleted successfully.');
    }
}

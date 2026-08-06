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

    public function create()
    {
        return view('admin.homepage-stream-tabs.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'key' => 'nullable|string|max:255|unique:homepage_stream_tabs,key',
            'keywords' => 'nullable|string',
            'default_exams' => 'nullable|string',
            'default_states' => 'nullable|string',
            'default_courses' => 'nullable|string',
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
            'default_exams' => $parseList($request->default_exams),
            'default_states' => $parseList($request->default_states),
            'default_courses' => $parseList($request->default_courses),
            'sort_order' => $request->sort_order,
            'status' => $request->status,
        ]);

        return redirect()->route('admin.homepage-stream-tabs.index')->with('success', 'Stream Tab created successfully.');
    }

    public function edit(HomepageStreamTab $homepageStreamTab)
    {
        return view('admin.homepage-stream-tabs.edit', compact('homepageStreamTab'));
    }

    public function update(Request $request, HomepageStreamTab $homepageStreamTab)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'key' => 'nullable|string|max:255|unique:homepage_stream_tabs,key,' . $homepageStreamTab->id,
            'keywords' => 'nullable|string',
            'default_exams' => 'nullable|string',
            'default_states' => 'nullable|string',
            'default_courses' => 'nullable|string',
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
            'default_exams' => $parseList($request->default_exams),
            'default_states' => $parseList($request->default_states),
            'default_courses' => $parseList($request->default_courses),
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

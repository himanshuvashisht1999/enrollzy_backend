<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

use App\Models\HomepageSection;

class HomepageSectionController extends Controller
{
    public function index()
    {
        $sections = HomepageSection::orderBy('sort_order')->get();
        return view('admin.homepage-sections.index', compact('sections'));
    }

    public function update(Request $request, HomepageSection $homepageSection)
    {
        $request->validate([
            'is_visible' => 'required|boolean',
        ]);

        $homepageSection->update(['is_visible' => $request->is_visible]);

        return redirect()->back()->with('success', 'Section updated successfully.');
    }

    public function edit(HomepageSection $homepageSection)
    {
        return view('admin.homepage-sections.edit', compact('homepageSection'));
    }

    public function updateDetails(Request $request, HomepageSection $homepageSection)
    {
        $request->validate([
            'title' => 'nullable|string|max:255',
            'subtitle' => 'nullable|string',
            'cta_title' => 'nullable|string|max:255',
            'cta_url' => 'nullable|string|max:255',
        ]);

        $homepageSection->update([
            'title' => $request->title,
            'subtitle' => $request->subtitle,
            'cta_title' => $request->cta_title,
            'cta_url' => $request->cta_url,
        ]);

        return redirect()->route('homepage-sections.index')->with('success', 'Section details updated successfully.');
    }

    public function updateOrder(Request $request)
    {
        $request->validate([
            'sections' => 'required|array',
        ]);

        foreach ($request->sections as $order => $id) {
            HomepageSection::where('id', $id)->update(['sort_order' => $order + 1]);
        }

        return response()->json(['success' => true]);
    }
}

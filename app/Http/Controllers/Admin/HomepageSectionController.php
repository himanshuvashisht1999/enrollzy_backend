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

    public function edit($homepageSection)
    {
        if (!($homepageSection instanceof HomepageSection)) {
            $homepageSection = HomepageSection::findOrFail($homepageSection);
        }
        return view('admin.homepage-sections.edit', compact('homepageSection'));
    }

    public function updateDetails(Request $request, $homepageSection)
    {
        if (!($homepageSection instanceof HomepageSection)) {
            $homepageSection = HomepageSection::findOrFail($homepageSection);
        }

        $request->validate([
            'title' => 'nullable|string|max:255',
            'subtitle' => 'nullable|string',
            'cta_title' => 'nullable|string|max:255',
            'cta_url' => 'nullable|string|max:255',
            'image' => 'nullable|file|mimes:jpeg,png,jpg,gif,svg,webp,avif,bmp|max:20480',
        ]);

        $settings = is_array($homepageSection->settings) ? $homepageSection->settings : json_decode($homepageSection->settings ?? '[]', true);
        if ($request->has('items_visibility')) {
            $settings['items_visibility'] = $request->items_visibility;
        }
        if ($request->has('badge_text')) {
            $settings['badge_text'] = $request->badge_text;
        }

        $data = [
            'title' => $request->title,
            'subtitle' => $request->subtitle,
            'cta_title' => $request->cta_title,
            'cta_url' => $request->cta_url,
            'settings' => $settings,
        ];

        if ($request->hasFile('image')) {
            if ($homepageSection->image && file_exists(public_path($homepageSection->image))) {
                @unlink(public_path($homepageSection->image));
            }
            $file = $request->file('image');
            $filename = time() . '_' . preg_replace('/[^A-Za-z0-9\._-]/', '_', $file->getClientOriginalName());
            $file->move(public_path('uploads/homepage_sections'), $filename);
            $data['image'] = 'uploads/homepage_sections/' . $filename;
        }

        $homepageSection->update($data);

        return redirect()->back()->with('success', 'Section details updated successfully.');
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

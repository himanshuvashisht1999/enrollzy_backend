<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AboutUsPage;
use App\Models\AboutUsOffer;
use App\Models\AboutUsFeature;
use App\Models\AboutUsImpact;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;

class AboutUsController extends Controller
{
    public function edit()
    {
        $page = AboutUsPage::first();
        if (!$page) {
            $page = AboutUsPage::create([
                'hero_title' => 'We simplify education decisions. You shape your future.',
                'hero_subtitle' => 'ABOUT US',
                'story_title' => 'A journey built on a simple belief',
                'story_subtitle' => 'OUR STORY',
            ]);
        }
        
        $offers = AboutUsOffer::orderBy('sort_order')->get();
        $features = AboutUsFeature::orderBy('sort_order')->get();
        $impacts = AboutUsImpact::orderBy('sort_order')->get();

        return view('admin.about_us.edit', compact('page', 'offers', 'features', 'impacts'));
    }

    public function update(Request $request)
    {
        $page = AboutUsPage::first();

        $data = $request->except(['hero_image', 'story_image', 'cta_image', 'founder_1_image', 'founder_2_image']);

        // Handle Images
        $imageFields = ['hero_image', 'story_image', 'cta_image', 'founder_1_image', 'founder_2_image'];
        foreach ($imageFields as $field) {
            if ($request->hasFile($field)) {
                if ($page->$field && File::exists(public_path($page->$field))) {
                    File::delete(public_path($page->$field));
                }
                $imageName = $field . '_' . time() . '.' . $request->$field->extension();
                $request->$field->move(public_path('uploads/about_us'), $imageName);
                $data[$field] = 'uploads/about_us/' . $imageName;
            }
        }

        $page->update($data);

        return redirect()->back()->with('success', 'About Us page updated successfully!');
    }

    // --- Offers ---
    public function storeOffer(Request $request)
    {
        $request->validate(['title' => 'required', 'icon_image' => 'nullable|image']);
        $data = $request->all();
        if ($request->hasFile('icon_image')) {
            $imageName = 'offer_' . time() . '.' . $request->icon_image->extension();
            $request->icon_image->move(public_path('uploads/about_us/icons'), $imageName);
            $data['icon_image'] = 'uploads/about_us/icons/' . $imageName;
        }
        AboutUsOffer::create($data);
        return redirect()->back()->with('success', 'Offer added successfully!');
    }

    public function updateOffer(Request $request, $id)
    {
        $offer = AboutUsOffer::findOrFail($id);
        $data = $request->except(['icon_image']);
        if ($request->hasFile('icon_image')) {
            if ($offer->icon_image && File::exists(public_path($offer->icon_image))) {
                File::delete(public_path($offer->icon_image));
            }
            $imageName = 'offer_' . time() . '.' . $request->icon_image->extension();
            $request->icon_image->move(public_path('uploads/about_us/icons'), $imageName);
            $data['icon_image'] = 'uploads/about_us/icons/' . $imageName;
        }
        $offer->update($data);
        return redirect()->back()->with('success', 'Offer updated successfully!');
    }

    public function destroyOffer($id)
    {
        $offer = AboutUsOffer::findOrFail($id);
        if ($offer->icon_image && File::exists(public_path($offer->icon_image))) {
            File::delete(public_path($offer->icon_image));
        }
        $offer->delete();
        return redirect()->back()->with('success', 'Offer deleted successfully!');
    }

    // --- Features ---
    public function storeFeature(Request $request)
    {
        $request->validate(['title' => 'required', 'icon_image' => 'nullable|image']);
        $data = $request->all();
        if ($request->hasFile('icon_image')) {
            $imageName = 'feature_' . time() . '.' . $request->icon_image->extension();
            $request->icon_image->move(public_path('uploads/about_us/icons'), $imageName);
            $data['icon_image'] = 'uploads/about_us/icons/' . $imageName;
        }
        AboutUsFeature::create($data);
        return redirect()->back()->with('success', 'Feature added successfully!');
    }

    public function updateFeature(Request $request, $id)
    {
        $feature = AboutUsFeature::findOrFail($id);
        $data = $request->except(['icon_image']);
        if ($request->hasFile('icon_image')) {
            if ($feature->icon_image && File::exists(public_path($feature->icon_image))) {
                File::delete(public_path($feature->icon_image));
            }
            $imageName = 'feature_' . time() . '.' . $request->icon_image->extension();
            $request->icon_image->move(public_path('uploads/about_us/icons'), $imageName);
            $data['icon_image'] = 'uploads/about_us/icons/' . $imageName;
        }
        $feature->update($data);
        return redirect()->back()->with('success', 'Feature updated successfully!');
    }

    public function destroyFeature($id)
    {
        $feature = AboutUsFeature::findOrFail($id);
        if ($feature->icon_image && File::exists(public_path($feature->icon_image))) {
            File::delete(public_path($feature->icon_image));
        }
        $feature->delete();
        return redirect()->back()->with('success', 'Feature deleted successfully!');
    }

    // --- Impacts ---
    public function storeImpact(Request $request)
    {
        $request->validate(['count_text' => 'required', 'label' => 'required', 'icon_image' => 'nullable|image']);
        $data = $request->all();
        if ($request->hasFile('icon_image')) {
            $imageName = 'impact_' . time() . '.' . $request->icon_image->extension();
            $request->icon_image->move(public_path('uploads/about_us/icons'), $imageName);
            $data['icon_image'] = 'uploads/about_us/icons/' . $imageName;
        }
        AboutUsImpact::create($data);
        return redirect()->back()->with('success', 'Impact added successfully!');
    }

    public function updateImpact(Request $request, $id)
    {
        $impact = AboutUsImpact::findOrFail($id);
        $data = $request->except(['icon_image']);
        if ($request->hasFile('icon_image')) {
            if ($impact->icon_image && File::exists(public_path($impact->icon_image))) {
                File::delete(public_path($impact->icon_image));
            }
            $imageName = 'impact_' . time() . '.' . $request->icon_image->extension();
            $request->icon_image->move(public_path('uploads/about_us/icons'), $imageName);
            $data['icon_image'] = 'uploads/about_us/icons/' . $imageName;
        }
        $impact->update($data);
        return redirect()->back()->with('success', 'Impact updated successfully!');
    }

    public function destroyImpact($id)
    {
        $impact = AboutUsImpact::findOrFail($id);
        if ($impact->icon_image && File::exists(public_path($impact->icon_image))) {
            File::delete(public_path($impact->icon_image));
        }
        $impact->delete();
        return redirect()->back()->with('success', 'Impact deleted successfully!');
    }
}

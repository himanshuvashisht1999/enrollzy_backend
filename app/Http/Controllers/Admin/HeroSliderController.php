<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\HeroSlider;
use App\Models\Setting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;

class HeroSliderController extends Controller
{
    public function index()
    {
        $sliders = HeroSlider::orderBy('sort_order')->get();
        $setting = Setting::first();
        return view('admin.hero-sliders.index', compact('sliders', 'setting'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'image_path' => 'required|image|mimes:jpeg,png,jpg,webp|max:5120',
            'sort_order' => 'nullable|integer',
            'image_type' => 'required|string|in:Text,Full Banner',
            'heading'    => 'nullable|string|max:255',
            'subheading' => 'nullable|string',
            'button_text'=> 'nullable|string|max:255',
            'button_url' => 'nullable|string|max:255',
        ]);

        if ($request->hasFile('image_path')) {
            $imageName = 'hero_' . time() . '_' . uniqid() . '.' . $request->image_path->extension();
            $request->image_path->move(public_path('uploads/hero'), $imageName);
            $path = 'uploads/hero/' . $imageName;

            HeroSlider::create([
                'image_path' => $path,
                'image_type' => $request->image_type,
                'heading'    => $request->image_type === 'Text' ? $request->heading : null,
                'subheading' => $request->image_type === 'Text' ? $request->subheading : null,
                'button_text'=> $request->image_type === 'Text' ? $request->button_text : null,
                'button_url' => $request->image_type === 'Text' ? $request->button_url : null,
                'sort_order' => $request->sort_order ?? 0,
                'is_active' => true,
            ]);
        }

        return redirect()->back()->with('success', 'Hero image added successfully.');
    }

    public function update(Request $request, HeroSlider $hero_slider)
    {
        $request->validate([
            'image_path' => 'nullable|image|mimes:jpeg,png,jpg,webp|max:5120',
            'sort_order' => 'nullable|integer',
            'image_type' => 'required|string|in:Text,Full Banner',
            'heading'    => 'nullable|string|max:255',
            'subheading' => 'nullable|string',
            'button_text'=> 'nullable|string|max:255',
            'button_url' => 'nullable|string|max:255',
        ]);

        $data = [
            'image_type' => $request->image_type,
            'heading'    => $request->image_type === 'Text' ? $request->heading : null,
            'subheading' => $request->image_type === 'Text' ? $request->subheading : null,
            'button_text'=> $request->image_type === 'Text' ? $request->button_text : null,
            'button_url' => $request->image_type === 'Text' ? $request->button_url : null,
            'sort_order' => $request->sort_order ?? 0,
        ];

        if ($request->hasFile('image_path')) {
            // Delete old image
            if (File::exists(public_path($hero_slider->image_path))) {
                File::delete(public_path($hero_slider->image_path));
            }

            $imageName = 'hero_' . time() . '_' . uniqid() . '.' . $request->image_path->extension();
            $request->image_path->move(public_path('uploads/hero'), $imageName);
            $data['image_path'] = 'uploads/hero/' . $imageName;
        }

        $hero_slider->update($data);

        return redirect()->back()->with('success', 'Hero image updated successfully.');
    }

    public function updateStatus(Request $request, HeroSlider $hero_slider)
    {
        $hero_slider->update(['is_active' => !$hero_slider->is_active]);
        return response()->json(['success' => true]);
    }
    
    public function toggleGlobalBanner(Request $request)
    {
        $status = $request->status == 'true' || $request->status == '1';
        $setting = Setting::first();
        
        if (!$setting) {
            $setting = new Setting();
        }
        
        $setting->is_show_full_banner = $status;
        $setting->save();
        
        return response()->json(['success' => true]);
    }

    public function destroy(HeroSlider $hero_slider)
    {
        if (File::exists(public_path($hero_slider->image_path))) {
            File::delete(public_path($hero_slider->image_path));
        }
        $hero_slider->delete();
        return redirect()->back()->with('success', 'Hero image deleted successfully.');
    }
}

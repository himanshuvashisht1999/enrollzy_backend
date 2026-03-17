<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\HeroSlider;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;

class HeroSliderController extends Controller
{
    public function index()
    {
        $sliders = HeroSlider::orderBy('sort_order')->get();
        return view('admin.hero-sliders.index', compact('sliders'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'image_path' => 'required|image|mimes:jpeg,png,jpg,webp|max:5120',
            'sort_order' => 'nullable|integer',
        ]);

        if ($request->hasFile('image_path')) {
            $imageName = 'hero_' . time() . '_' . uniqid() . '.' . $request->image_path->extension();
            $request->image_path->move(public_path('uploads/hero'), $imageName);
            $path = 'uploads/hero/' . $imageName;

            HeroSlider::create([
                'image_path' => $path,
                'sort_order' => $request->sort_order ?? 0,
                'is_active' => true,
            ]);
        }

        return redirect()->back()->with('success', 'Hero image added successfully.');
    }

    public function updateStatus(Request $request, HeroSlider $slider)
    {
        $slider->update(['is_active' => !$slider->is_active]);
        return response()->json(['success' => true]);
    }

    public function destroy(HeroSlider $slider)
    {
        if (File::exists(public_path($slider->image_path))) {
            File::delete(public_path($slider->image_path));
        }
        $slider->delete();
        return redirect()->back()->with('success', 'Hero image deleted successfully.');
    }
}

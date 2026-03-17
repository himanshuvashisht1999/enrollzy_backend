<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\VideoTestimonial;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;

class VideoTestimonialController extends Controller
{
    public function index()
    {
        $testimonials = VideoTestimonial::orderBy('sort_order')->get();
        return view('admin.video-testimonials.index', compact('testimonials'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'course' => 'nullable|string|max:255',
            'video_url' => 'required|url',
            'thumbnail' => 'nullable|image|mimes:jpeg,png,jpg,webp|max:2048',
            'sort_order' => 'nullable|integer',
        ]);

        $data = $request->all();

        if ($request->hasFile('thumbnail')) {
            $imageName = 'video_thumb_' . time() . '.' . $request->thumbnail->extension();
            $request->thumbnail->move(public_path('uploads/testimonials'), $imageName);
            $data['thumbnail'] = 'uploads/testimonials/' . $imageName;
        }

        VideoTestimonial::create($data);

        return redirect()->back()->with('success', 'Video testimonial added successfully.');
    }

    public function update(Request $request, VideoTestimonial $videoTestimonial)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'course' => 'nullable|string|max:255',
            'video_url' => 'required|url',
            'thumbnail' => 'nullable|image|mimes:jpeg,png,jpg,webp|max:2048',
            'sort_order' => 'nullable|integer',
        ]);

        $data = $request->all();

        if ($request->hasFile('thumbnail')) {
            if ($videoTestimonial->thumbnail && File::exists(public_path($videoTestimonial->thumbnail))) {
                File::delete(public_path($videoTestimonial->thumbnail));
            }
            $imageName = 'video_thumb_' . time() . '.' . $request->thumbnail->extension();
            $request->thumbnail->move(public_path('uploads/testimonials'), $imageName);
            $data['thumbnail'] = 'uploads/testimonials/' . $imageName;
        }

        $videoTestimonial->update($data);

        return redirect()->back()->with('success', 'Video testimonial updated successfully.');
    }

    public function destroy(VideoTestimonial $videoTestimonial)
    {
        if ($videoTestimonial->thumbnail && File::exists(public_path($videoTestimonial->thumbnail))) {
            File::delete(public_path($videoTestimonial->thumbnail));
        }
        $videoTestimonial->delete();
        return redirect()->back()->with('success', 'Video testimonial deleted successfully.');
    }
}

<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Testimonial;
use App\Models\MentorProfile;
use Illuminate\Http\Request;
use File;

class TestimonialController extends Controller
{
    public function index(Request $request)
    {
        $query = Testimonial::query();

        if ($request->filled('rating')) {
            $query->where('rating', $request->rating);
        }

        if ($request->filled('search')) {
            $query->where('name', 'like', '%' . $request->search . '%')
                  ->orWhere('role', 'like', '%' . $request->search . '%');
        }

        $testimonials = $query->latest()->paginate(10);
        return view('admin.testimonials.index', compact('testimonials'));
    }

    public function create()
    {
        $mentors = MentorProfile::with('user')->get();
        return view('admin.testimonials.create', compact('mentors'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'role' => 'nullable|string|max:255',
            'mentor_profile_id' => 'nullable|integer',
            'content' => 'required|string',
            'rating' => 'nullable|integer|min:1|max:5',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif,webp|max:4096',
        ]);

        $data = $request->all();

        if ($request->hasFile('image')) {
            $imageName = time() . '.' . $request->image->extension();
            $request->image->move(public_path('uploads/testimonials'), $imageName);
            $data['image'] = 'uploads/testimonials/' . $imageName;
        }

        Testimonial::create($data);

        return redirect()->route('testimonials.index')->with('success', 'Testimonial added successfully.');
    }

    public function edit(Testimonial $testimonial)
    {
        $mentors = MentorProfile::with('user')->get();
        return view('admin.testimonials.edit', compact('testimonial', 'mentors'));
    }

    public function update(Request $request, Testimonial $testimonial)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'role' => 'nullable|string|max:255',
            'mentor_profile_id' => 'nullable|integer',
            'content' => 'required|string',
            'rating' => 'nullable|integer|min:1|max:5',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif,webp|max:4096',
        ]);

        $data = $request->all();

        if ($request->hasFile('image')) {
            if ($testimonial->image && File::exists(public_path($testimonial->image))) {
                File::delete(public_path($testimonial->image));
            }

            $imageName = time() . '.' . $request->image->extension();
            $request->image->move(public_path('uploads/testimonials'), $imageName);
            $data['image'] = 'uploads/testimonials/' . $imageName;
        }

        $testimonial->update($data);

        return redirect()->route('testimonials.index')->with('success', 'Testimonial updated successfully.');
    }

    public function destroy(Testimonial $testimonial)
    {
        if ($testimonial->image && File::exists(public_path($testimonial->image))) {
            File::delete(public_path($testimonial->image));
        }

        $testimonial->delete();

        return redirect()->route('testimonials.index')->with('success', 'Testimonial deleted successfully.');
    }
}

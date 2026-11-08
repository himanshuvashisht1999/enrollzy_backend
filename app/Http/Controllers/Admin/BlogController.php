<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Blog;
use App\Models\Category;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use File;

class BlogController extends Controller
{
    public function index(Request $request)
    {
        $query = Blog::with('category');

        // Filters
        if ($request->filled('category_id')) {
            $query->where('category_id', $request->category_id);
        }

        if ($request->filled('search')) {
            $query->where('title', 'like', '%' . $request->search . '%');
        }

        $blogs = $query->latest()->paginate(10);
        $categories = Category::orderBy('name')->get();
        
        return view('admin.blogs.index', compact('blogs', 'categories'));
    }

    public function create()
    {
        $categories = Category::orderBy('name')->get();
        return view('admin.blogs.create', compact('categories'));
    }

    /**
     * The featured image shows in 16:9 landscape boxes (homepage, blog list, blog page), so it must be a horizontal
     * image, at least 800 px wide and at least 3:2 (1200 x 675 recommended); square or portrait posters get cropped.
     */
    private function imageRules(): array
    {
        return ['nullable', 'image', 'mimes:jpeg,png,jpg,gif,webp', 'max:2048', 'dimensions:min_width=800',
            function ($attribute, $file, $fail) {
                [$w, $h] = @getimagesize($file->getRealPath()) ?: [0, 0];
                if ($w && $h && $w / $h < 1.5) {
                    $fail("Upload a horizontal (landscape) image, 1200 × 675 px recommended. This one is {$w} × {$h} px.");
                }
            }];
    }

    public function store(Request $request)
    {
        $request->validate([
            'title' => 'required|max:255',
            'category_id' => 'nullable|exists:categories,id',
            'content' => 'required',
            'image' => $this->imageRules(),
        ], ['image.dimensions' => 'The image must be at least 800 px wide (1200 × 675 px recommended).']);

        $data = $request->all();

        if ($request->hasFile('image')) {
            $imageName = time() . '.' . $request->image->extension();
            $request->image->move(public_path('uploads/blogs'), $imageName);
            $data['image'] = 'uploads/blogs/' . $imageName;
        }

        Blog::create($data);

        return redirect()->route('blogs.index')->with('success', 'Blog created successfully.');
    }

    public function edit(Blog $blog)
    {
        $categories = Category::orderBy('name')->get();
        return view('admin.blogs.edit', compact('blog', 'categories'));
    }

    public function update(Request $request, Blog $blog)
    {
        $request->validate([
            'title' => 'required|max:255',
            'category_id' => 'nullable|exists:categories,id',
            'content' => 'required',
            'image' => $this->imageRules(),
        ], ['image.dimensions' => 'The image must be at least 800 px wide (1200 × 675 px recommended).']);

        $data = $request->all();

        if ($request->hasFile('image')) {
            // Delete old image
            if ($blog->image && File::exists(public_path($blog->image))) {
                File::delete(public_path($blog->image));
            }

            $imageName = time() . '.' . $request->image->extension();
            $request->image->move(public_path('uploads/blogs'), $imageName);
            $data['image'] = 'uploads/blogs/' . $imageName;
        }

        $blog->update($data);

        return redirect()->route('blogs.index')->with('success', 'Blog updated successfully.');
    }

    public function destroy(Blog $blog)
    {
        if ($blog->image && File::exists(public_path($blog->image))) {
            File::delete(public_path($blog->image));
        }

        $blog->delete();

        return redirect()->route('blogs.index')->with('success', 'Blog deleted successfully.');
    }
}

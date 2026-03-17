<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\NoteworthyCategory;
use App\Models\NoteworthyMention;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;

class NoteworthyMentionController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $mentions = NoteworthyMention::with('category')->latest()->paginate(10);
        return view('admin.noteworthy-mentions.index', compact('mentions'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $categories = NoteworthyCategory::where('status', true)->orderBy('sort_order')->get();
        return view('admin.noteworthy-mentions.create', compact('categories'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'noteworthy_category_id' => 'required|exists:noteworthy_categories,id',
            'title' => 'required|string|max:255',
            'image' => 'required|image|mimes:jpeg,png,jpg,gif|max:2048',
            'subtitle' => 'nullable|string|max:255',
            'badge_text' => 'nullable|string|max:255',
            'url' => 'nullable|url',
            'sort_order' => 'required|integer',
            'status' => 'required|boolean',
        ]);

        $data = $request->all();

        if ($request->hasFile('image')) {
            $imageName = time() . '_' . uniqid() . '.' . $request->image->extension();
            $request->image->move(public_path('uploads/noteworthy-mentions'), $imageName);
            $data['image'] = 'uploads/noteworthy-mentions/' . $imageName;
        }

        NoteworthyMention::create($data);

        return redirect()->route('admin.noteworthy-mentions.index')
            ->with('success', 'Mention created successfully.');
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(NoteworthyMention $noteworthyMention)
    {
        $categories = NoteworthyCategory::where('status', true)->orderBy('sort_order')->get();
        return view('admin.noteworthy-mentions.edit', compact('noteworthyMention', 'categories'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, NoteworthyMention $noteworthyMention)
    {
        $request->validate([
            'noteworthy_category_id' => 'required|exists:noteworthy_categories,id',
            'title' => 'required|string|max:255',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            'subtitle' => 'nullable|string|max:255',
            'badge_text' => 'nullable|string|max:255',
            'url' => 'nullable|url',
            'sort_order' => 'required|integer',
            'status' => 'required|boolean',
        ]);

        $data = $request->all();

        if ($request->hasFile('image')) {
            // Delete old image if exists
            if ($noteworthyMention->image && File::exists(public_path($noteworthyMention->image))) {
                File::delete(public_path($noteworthyMention->image));
            }

            $imageName = time() . '_' . uniqid() . '.' . $request->image->extension();
            $request->image->move(public_path('uploads/noteworthy-mentions'), $imageName);
            $data['image'] = 'uploads/noteworthy-mentions/' . $imageName;
        }

        $noteworthyMention->update($data);

        return redirect()->route('admin.noteworthy-mentions.index')
            ->with('success', 'Mention updated successfully.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(NoteworthyMention $noteworthyMention)
    {
        // Delete image if exists
        if ($noteworthyMention->image && File::exists(public_path($noteworthyMention->image))) {
            File::delete(public_path($noteworthyMention->image));
        }

        $noteworthyMention->delete();

        return redirect()->route('admin.noteworthy-mentions.index')
            ->with('success', 'Mention deleted successfully.');
    }
}

<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\SchoolMarquee;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;

class SchoolMarqueeController extends Controller
{
    public function index()
    {
        $marquees = SchoolMarquee::orderBy('sort_order')->get();
        return view('admin.school-marquees.index', compact('marquees'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'logo'       => 'required|image|mimes:jpeg,png,jpg,webp,svg|max:2048',
            'name'       => 'nullable|string|max:255',
            'heading'    => 'nullable|string|max:255',
            'subheading' => 'nullable|string',
            'sort_order' => 'nullable|integer',
        ]);

        $data = $request->only(['name', 'heading', 'subheading', 'sort_order']);
        $data['sort_order'] = $request->sort_order ?? 0;
        
        // Inherit direction from existing records
        $existing = SchoolMarquee::first();
        $data['direction'] = $existing ? $existing->direction : 'rtl';
        $data['status'] = true;

        if ($request->hasFile('logo')) {
            $imageName = 'marquee_' . time() . '_' . uniqid() . '.' . $request->logo->extension();
            $request->logo->move(public_path('uploads/marquee'), $imageName);
            $data['logo'] = 'uploads/marquee/' . $imageName;
        }

        SchoolMarquee::create($data);

        return redirect()->back()->with('success', 'Company logo added precisely.');
    }

    public function update(Request $request, SchoolMarquee $school_marquee)
    {
        $request->validate([
            'logo'       => 'nullable|image|mimes:jpeg,png,jpg,webp,svg|max:2048',
            'name'       => 'nullable|string|max:255',
            'heading'    => 'nullable|string|max:255',
            'subheading' => 'nullable|string',
            'sort_order' => 'nullable|integer',
        ]);

        $data = $request->only(['name', 'heading', 'subheading', 'sort_order']);

        if ($request->hasFile('logo')) {
            if (File::exists(public_path($school_marquee->logo))) {
                File::delete(public_path($school_marquee->logo));
            }

            $imageName = 'marquee_' . time() . '_' . uniqid() . '.' . $request->logo->extension();
            $request->logo->move(public_path('uploads/marquee'), $imageName);
            $data['logo'] = 'uploads/marquee/' . $imageName;
        }

        $school_marquee->update($data);

        return redirect()->back()->with('success', 'Company logo updated successfully.');
    }

    public function updateDirection(Request $request)
    {
        $request->validate(['direction' => 'required|in:ltr,rtl']);
        SchoolMarquee::query()->update(['direction' => $request->direction]);
        return redirect()->back()->with('success', 'Scroll direction updated successfully.');
    }

    public function toggleStatus(SchoolMarquee $school_marquee)
    {
        $school_marquee->update(['status' => !$school_marquee->status]);
        return response()->json(['success' => true]);
    }

    public function destroy(SchoolMarquee $school_marquee)
    {
        if (File::exists(public_path($school_marquee->logo))) {
            File::delete(public_path($school_marquee->logo));
        }
        $school_marquee->delete();
        return redirect()->back()->with('success', 'Company logo removed successfully.');
    }
}

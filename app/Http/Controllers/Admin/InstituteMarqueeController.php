<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\InstituteMarquee;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;

class InstituteMarqueeController extends Controller
{
    public function index()
    {
        $marquees = InstituteMarquee::orderBy('sort_order')->get();
        return view('admin.institute-marquees.index', compact('marquees'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'logo'       => 'required|image|mimes:jpeg,png,jpg,webp,svg|max:2048',
            'name'       => 'nullable|string|max:255',
            'heading'    => 'nullable|string|max:255',
            'subheading' => 'nullable|string',
            'sort_order' => 'nullable|integer',
            'logo_url'   => 'nullable|string|max:255',
        ]);

        $data = $request->only(['name', 'heading', 'subheading', 'sort_order', 'logo_url']);
        $data['sort_order'] = $request->sort_order ?? 0;
        
        $existing = InstituteMarquee::first();
        $data['direction'] = $existing ? $existing->direction : 'rtl';
        $data['status'] = true;

        if ($request->hasFile('logo')) {
            $imageName = 'inst_marquee_' . time() . '_' . uniqid() . '.' . $request->logo->extension();
            $request->logo->move(public_path('uploads/marquee'), $imageName);
            $data['logo'] = 'uploads/marquee/' . $imageName;
        }

        InstituteMarquee::create($data);

        return redirect()->back()->with('success', 'Institute logo added successfully.');
    }

    public function update(Request $request, InstituteMarquee $institute_marquee)
    {
        $request->validate([
            'logo'       => 'nullable|image|mimes:jpeg,png,jpg,webp,svg|max:2048',
            'name'       => 'nullable|string|max:255',
            'heading'    => 'nullable|string|max:255',
            'subheading' => 'nullable|string',
            'sort_order' => 'nullable|integer',
            'logo_url'   => 'nullable|string|max:255',
        ]);

        $data = $request->only(['name', 'heading', 'subheading', 'sort_order', 'logo_url']);

        if ($request->hasFile('logo')) {
            if (File::exists(public_path($institute_marquee->logo))) {
                File::delete(public_path($institute_marquee->logo));
            }

            $imageName = 'inst_marquee_' . time() . '_' . uniqid() . '.' . $request->logo->extension();
            $request->logo->move(public_path('uploads/marquee'), $imageName);
            $data['logo'] = 'uploads/marquee/' . $imageName;
        }

        $institute_marquee->update($data);

        return redirect()->back()->with('success', 'Institute logo updated successfully.');
    }

    public function updateDirection(Request $request)
    {
        $request->validate(['direction' => 'required|in:ltr,rtl']);
        InstituteMarquee::query()->update(['direction' => $request->direction]);
        return redirect()->back()->with('success', 'Scroll direction updated successfully.');
    }

    public function toggleStatus(InstituteMarquee $institute_marquee)
    {
        $institute_marquee->update(['status' => !$institute_marquee->status]);
        return response()->json(['success' => true]);
    }

    public function destroy(InstituteMarquee $institute_marquee)
    {
        if (File::exists(public_path($institute_marquee->logo))) {
            File::delete(public_path($institute_marquee->logo));
        }
        $institute_marquee->delete();
        return redirect()->back()->with('success', 'Institute logo removed successfully.');
    }
}

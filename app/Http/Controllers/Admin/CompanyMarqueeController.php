<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\CompanyMarquee;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;

class CompanyMarqueeController extends Controller
{
    public function index()
    {
        $marquees = CompanyMarquee::orderBy('sort_order')->get();
        return view('admin.company-marquees.index', compact('marquees'));
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
        $data['status'] = true;

        if ($request->hasFile('logo')) {
            $imageName = 'marquee_' . time() . '_' . uniqid() . '.' . $request->logo->extension();
            $request->logo->move(public_path('uploads/marquee'), $imageName);
            $data['logo'] = 'uploads/marquee/' . $imageName;
        }

        CompanyMarquee::create($data);

        return redirect()->back()->with('success', 'Company logo added precisely.');
    }

    public function update(Request $request, CompanyMarquee $company_marquee)
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
            if (File::exists(public_path($company_marquee->logo))) {
                File::delete(public_path($company_marquee->logo));
            }

            $imageName = 'marquee_' . time() . '_' . uniqid() . '.' . $request->logo->extension();
            $request->logo->move(public_path('uploads/marquee'), $imageName);
            $data['logo'] = 'uploads/marquee/' . $imageName;
        }

        $company_marquee->update($data);

        return redirect()->back()->with('success', 'Company logo updated successfully.');
    }

    public function toggleStatus(CompanyMarquee $company_marquee)
    {
        $company_marquee->update(['status' => !$company_marquee->status]);
        return response()->json(['success' => true]);
    }

    public function destroy(CompanyMarquee $company_marquee)
    {
        if (File::exists(public_path($company_marquee->logo))) {
            File::delete(public_path($company_marquee->logo));
        }
        $company_marquee->delete();
        return redirect()->back()->with('success', 'Company logo removed successfully.');
    }
}

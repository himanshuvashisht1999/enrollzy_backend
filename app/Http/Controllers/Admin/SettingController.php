<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Setting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\File;

class SettingController extends Controller
{
    public function index()
    {
        $setting = Setting::first();
        if (!$setting) {
            $setting = Setting::create([
                'site_name' => 'College Website',
                'meta_title' => 'Best Online College Degrees',
                'footer_text' => '© 2025 College Website. All rights reserved.'
            ]);
        }
        return view('admin.settings.index', compact('setting'));
    }

    public function update(Request $request)
    {
        $setting = Setting::first();

        $request->validate([
            'site_name' => 'nullable|string|max:255',
            'logo' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg,webp|max:2048',
            'favicon' => 'nullable|image|mimes:ico,png,jpg|max:512',
            'meta_title' => 'nullable|string|max:255',
            'meta_description' => 'nullable|string',
            'meta_keywords' => 'nullable|string',
            'contact_email' => 'nullable|email|max:255',
            'contact_phone' => 'nullable|string|max:255',
            'address' => 'nullable|string',
            'footer_text' => 'nullable|string|max:255',
        ]);

        $data = $request->except(['logo', 'favicon']);

        // Handle Logo Upload
        if ($request->hasFile('logo')) {
            if ($setting->logo && File::exists(public_path($setting->logo))) {
                File::delete(public_path($setting->logo));
            }
            $imageName = 'logo_' . time() . '.' . $request->logo->extension();
            $request->logo->move(public_path('uploads/settings'), $imageName);
            $data['logo'] = 'uploads/settings/' . $imageName;
        }

        // Handle Favicon Upload
        if ($request->hasFile('favicon')) {
            if ($setting->favicon && File::exists(public_path($setting->favicon))) {
                File::delete(public_path($setting->favicon));
            }
            $faviconName = 'favicon_' . time() . '.' . $request->favicon->extension();
            $request->favicon->move(public_path('uploads/settings'), $faviconName);
            $data['favicon'] = 'uploads/settings/' . $faviconName;
        }

        $setting->update($data);

        return redirect()->back()->with('success', 'General settings updated successfully.');
    }
}

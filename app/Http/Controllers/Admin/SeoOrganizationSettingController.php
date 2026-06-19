<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\SeoOrganizationSetting;
use Illuminate\Support\Facades\File;

class SeoOrganizationSettingController extends Controller
{
    public function edit()
    {
        $setting = SeoOrganizationSetting::first() ?? new SeoOrganizationSetting();
        return view('admin.seo_organization_settings.edit', compact('setting'));
    }

    public function update(Request $request)
    {
        $setting = SeoOrganizationSetting::first() ?? new SeoOrganizationSetting();
        
        $data = $request->except([
            'logo', 'white_logo', 'dark_logo', 'favicon', 'apple_touch_icon', 'og_image',
            'default_og_image', 'default_twitter_image'
        ]);

        // Handle JSON array for same_as
        if ($request->has('same_as') && is_array($request->same_as)) {
            $data['same_as'] = array_values(array_filter($request->same_as));
        } else {
            $data['same_as'] = [];
        }

        // Handle boolean fields (checkboxes)
        $booleanFields = [
            'schema_enabled', 'organization_schema', 'search_action_schema', 
            'website_schema', 'breadcrumb_schema', 'logo_schema', 'social_profile_schema'
        ];
        
        foreach ($booleanFields as $field) {
            $data[$field] = $request->has($field) ? true : false;
        }

        // Handle File Uploads
        $fileFields = [
            'logo', 'white_logo', 'dark_logo', 'favicon', 'apple_touch_icon', 'og_image',
            'default_og_image', 'default_twitter_image'
        ];

        foreach ($fileFields as $field) {
            if ($request->hasFile($field)) {
                // Delete old file if exists
                if ($setting->$field && File::exists(public_path($setting->$field))) {
                    File::delete(public_path($setting->$field));
                }

                $file = $request->file($field);
                $imageName = $field . '_' . time() . '.' . $file->extension();
                $file->move(public_path('uploads/seo'), $imageName);
                $data[$field] = 'uploads/seo/' . $imageName;
            }
        }

        if ($setting->exists) {
            $setting->update($data);
        } else {
            SeoOrganizationSetting::create($data);
        }

        return redirect()->back()->with('success', 'SEO Organization Settings updated successfully!');
    }
}

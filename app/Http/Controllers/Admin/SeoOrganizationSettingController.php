<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\SeoOrganizationSetting;
use App\Models\OrganizationFounder;
use Illuminate\Support\Facades\File;

class SeoOrganizationSettingController extends Controller
{
    public function edit()
    {
        $setting = SeoOrganizationSetting::with('founders')->first() ?? new SeoOrganizationSetting();
        $founders = $setting->exists ? $setting->founders : collect();
        return view('admin.seo_organization_settings.edit', compact('setting', 'founders'));
    }

    public function update(Request $request)
    {
        $setting = SeoOrganizationSetting::first() ?? new SeoOrganizationSetting();
        
        $data = $request->except([
            'logo', 'white_logo', 'dark_logo', 'favicon', 'apple_touch_icon', 'og_image',
            'default_og_image', 'default_twitter_image', 'founders'
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

        $uploadPath = public_path('uploads/seo');
        if (!File::exists($uploadPath)) {
            File::makeDirectory($uploadPath, 0755, true);
        }

        foreach ($fileFields as $field) {
            if ($request->hasFile($field)) {
                if ($setting->$field && File::exists(public_path($setting->$field))) {
                    File::delete(public_path($setting->$field));
                }

                $file = $request->file($field);
                $imageName = $field . '_' . time() . '.' . $file->extension();
                $file->move($uploadPath, $imageName);
                $data[$field] = 'uploads/seo/' . $imageName;
            }
        }

        if ($setting->exists) {
            $setting->update($data);
        } else {
            $setting = SeoOrganizationSetting::create($data);
        }

        // Handle Repeatable Founders
        $foundersUploadPath = public_path('uploads/seo/founders');
        if (!File::exists($foundersUploadPath)) {
            File::makeDirectory($foundersUploadPath, 0755, true);
        }

        $submittedFounderIds = [];
        if ($request->has('founders') && is_array($request->founders)) {
            foreach ($request->founders as $index => $fData) {
                if (empty($fData['name'])) {
                    continue;
                }

                $founderId = $fData['id'] ?? null;
                $founder = $founderId ? OrganizationFounder::find($founderId) : null;

                $founderData = [
                    'seo_organization_setting_id' => $setting->id,
                    'name' => trim($fData['name']),
                    'job_title' => $fData['job_title'] ?? null,
                    'profile_url' => $fData['profile_url'] ?? null,
                    'linkedin_url' => $fData['linkedin_url'] ?? null,
                    'sort_order' => isset($fData['sort_order']) ? (int)$fData['sort_order'] : $index,
                    'is_active' => isset($fData['is_active']) ? (bool)$fData['is_active'] : true,
                ];

                // Handle same_as array / string
                if (!empty($fData['same_as'])) {
                    if (is_array($fData['same_as'])) {
                        $founderData['same_as'] = array_values(array_filter($fData['same_as']));
                    } else {
                        $links = array_map('trim', explode("\n", str_replace(',', "\n", $fData['same_as'])));
                        $founderData['same_as'] = array_values(array_filter($links));
                    }
                } else {
                    $founderData['same_as'] = [];
                }

                // Handle Founder Image Upload
                if ($request->hasFile("founders.{$index}.image_file")) {
                    $imgFile = $request->file("founders.{$index}.image_file");
                    $imgName = 'founder_' . time() . '_' . uniqid() . '.' . $imgFile->extension();
                    $imgFile->move($foundersUploadPath, $imgName);
                    
                    if ($founder && $founder->image && File::exists(public_path($founder->image))) {
                        File::delete(public_path($founder->image));
                    }
                    $founderData['image'] = 'uploads/seo/founders/' . $imgName;
                } elseif (!empty($fData['image_url'])) {
                    $founderData['image'] = trim($fData['image_url']);
                }

                if ($founder) {
                    $founder->update($founderData);
                    $submittedFounderIds[] = $founder->id;
                } else {
                    $newFounder = OrganizationFounder::create($founderData);
                    $submittedFounderIds[] = $newFounder->id;
                }
            }
        }

        // Delete founders that were removed in the UI
        OrganizationFounder::where('seo_organization_setting_id', $setting->id)
            ->whereNotIn('id', $submittedFounderIds)
            ->delete();

        // Update founder_name field for backward compatibility
        $firstFounder = OrganizationFounder::where('seo_organization_setting_id', $setting->id)->orderBy('sort_order')->first();
        if ($firstFounder) {
            $setting->update(['founder_name' => $firstFounder->name]);
        }

        return redirect()->back()->with('success', 'SEO Organization Settings & Founders updated successfully!');
    }
}

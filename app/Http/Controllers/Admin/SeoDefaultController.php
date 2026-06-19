<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\SeoDefault;
use Illuminate\Support\Facades\File;

class SeoDefaultController extends Controller
{
    public function edit()
    {
        $setting = SeoDefault::first() ?? new SeoDefault();
        return view('admin.seo_defaults.edit', compact('setting'));
    }

    public function update(Request $request)
    {
        $setting = SeoDefault::first() ?? new SeoDefault();
        
        $data = $request->except(['_token', 'default_og_image', 'default_twitter_image']);

        // Handle Image Uploads
        $uploadPath = public_path('uploads/seo/');
        if (!File::exists($uploadPath)) {
            File::makeDirectory($uploadPath, 0755, true);
        }

        $imageFields = [
            'default_og_image',
            'default_twitter_image',
        ];

        foreach ($imageFields as $field) {
            if ($request->hasFile($field)) {
                $file = $request->file($field);
                $filename = time() . '_' . $field . '.' . $file->getClientOriginalExtension();
                $file->move($uploadPath, $filename);
                
                // Delete old if exists
                if ($setting->$field && File::exists(public_path($setting->$field))) {
                    File::delete(public_path($setting->$field));
                }
                
                $data[$field] = 'uploads/seo/' . $filename;
            }
        }

        $setting->fill($data);
        $setting->save();

        return redirect()->route('admin.seo_defaults.edit')->with('success', 'Global SEO Defaults updated successfully!');
    }
}

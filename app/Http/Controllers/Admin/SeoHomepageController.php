<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\SeoHomepage;
use App\Models\SeoHomepageFaq;
use App\Models\SeoHomepageSection;
use App\Models\SeoHomepageSchemaBlock;
use Illuminate\Support\Facades\File;

class SeoHomepageController extends Controller
{
    public function edit()
    {
        $homepage = SeoHomepage::first() ?? new SeoHomepage();
        $faqs = SeoHomepageFaq::orderBy('sort_order')->get();
        $sections = SeoHomepageSection::orderBy('sort_order')->get();
        $schemaBlocks = SeoHomepageSchemaBlock::all();
        
        return view('admin.seo_homepage.edit', compact('homepage', 'faqs', 'sections', 'schemaBlocks'));
    }

    public function update(Request $request)
    {
        $homepage = SeoHomepage::first() ?? new SeoHomepage();
        
        $data = $request->except([
            '_token', 
            'og_image', 
            'twitter_image', 
            'featured_image', 
            'faqs',
            'sections',
            'schema_blocks',
            'allow_index',
            'allow_snippet',
            'allow_image_preview',
            'allow_video_preview'
        ]);

        // Process Booleans
        $data['allow_index'] = $request->has('allow_index');
        $data['allow_snippet'] = $request->has('allow_snippet');
        $data['allow_image_preview'] = $request->has('allow_image_preview');
        $data['allow_video_preview'] = $request->has('allow_video_preview');

        // Clean secondary keywords
        if ($request->has('secondary_keywords')) {
            $data['secondary_keywords'] = array_filter($request->secondary_keywords, fn($kw) => !empty($kw));
        }

        // Handle Image Uploads
        $uploadPath = public_path('uploads/seo/');
        if (!File::exists($uploadPath)) {
            File::makeDirectory($uploadPath, 0755, true);
        }

        $imageFields = ['og_image', 'twitter_image', 'featured_image'];
        foreach ($imageFields as $field) {
            if ($request->hasFile($field)) {
                $file = $request->file($field);
                $filename = time() . '_' . $field . '.' . $file->getClientOriginalExtension();
                $file->move($uploadPath, $filename);
                
                if ($homepage->$field && File::exists(public_path($homepage->$field))) {
                    File::delete(public_path($homepage->$field));
                }
                
                $data[$field] = 'uploads/seo/' . $filename;
            }
        }

        $homepage->fill($data);
        $homepage->save();

        // Handle FAQs
        if ($request->has('faqs')) {
            SeoHomepageFaq::truncate();
            foreach ($request->faqs as $index => $faqData) {
                if (!empty($faqData['question']) && !empty($faqData['answer'])) {
                    SeoHomepageFaq::create([
                        'question' => $faqData['question'],
                        'answer' => $faqData['answer'],
                        'sort_order' => $index,
                    ]);
                }
            }
        }

        // Handle Sections (Assuming simplified creation/update from a dynamic table)
        if ($request->has('sections')) {
            SeoHomepageSection::truncate();
            foreach ($request->sections as $index => $sectionData) {
                if (!empty($sectionData['section_name'])) {
                    SeoHomepageSection::create([
                        'section_name' => $sectionData['section_name'],
                        'section_slug' => \Str::slug($sectionData['section_name']),
                        'title' => $sectionData['title'] ?? null,
                        'subtitle' => $sectionData['subtitle'] ?? null,
                        'description' => $sectionData['description'] ?? null,
                        'button_text' => $sectionData['button_text'] ?? null,
                        'button_link' => $sectionData['button_link'] ?? null,
                        'status' => isset($sectionData['status']) ? true : false,
                        'sort_order' => $index,
                    ]);
                }
            }
        }

        // Handle Schema Blocks
        if ($request->has('schema_blocks')) {
            SeoHomepageSchemaBlock::truncate();
            foreach ($request->schema_blocks as $blockData) {
                if (!empty($blockData['schema_type']) && !empty($blockData['json_data'])) {
                    // Try to decode to ensure valid JSON, then re-encode
                    $jsonDecoded = json_decode($blockData['json_data'], true);
                    if (json_last_error() === JSON_ERROR_NONE) {
                        SeoHomepageSchemaBlock::create([
                            'schema_type' => $blockData['schema_type'],
                            'json_data' => $jsonDecoded,
                            'status' => isset($blockData['status']) ? true : false,
                        ]);
                    }
                }
            }
        }

        return redirect()->route('admin.seo_homepage.edit')->with('success', 'Homepage SEO Settings updated successfully!');
    }
}

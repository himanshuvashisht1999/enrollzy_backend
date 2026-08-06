<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Scholarship;
use App\Models\Course;
use App\Models\Organisation;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class ScholarshipController extends Controller
{
    public function index()
    {
        $scholarships = Scholarship::orderBy('sort_order')->get();
        return view('admin.scholarships.index', compact('scholarships'));
    }

    public function create()
    {
        $courses = Course::where('status', 1)->orderBy('name')->get();
        $organisations = Organisation::where('status', 1)->orderBy('name')->get();
        return view('admin.scholarships.create', compact('courses', 'organisations'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'slug' => 'nullable|string|unique:scholarships,slug',
            'short_name' => 'nullable|string|max:255',
            'scholarship_code' => 'nullable|string|max:255',
            'status' => 'required|boolean',
            'featured' => 'required|boolean',
            'featured_on_homepage' => 'required|boolean',
            'sort_order' => 'required|integer',
            'max_amount' => 'nullable|numeric|min:0',
            'featured_image' => 'nullable|image|max:2048',
            'banner_image' => 'nullable|image|max:2048',
            'provider_logo' => 'nullable|image|max:2048',
        ]);

        try {
            DB::beginTransaction();

            $data = $request->except(['eligibility', 'benefits', 'highlights', 'courses', 'universities', 'dates', 'documents', 'faqs', 'gallery', 'seo']);
            $data['slug'] = $request->slug ?: Str::slug($request->title);
            $data['created_by'] = auth()->id();

            // Handle uploads
            if ($request->hasFile('featured_image')) {
                $file = $request->file('featured_image');
                $name = time() . '_feat_' . $file->getClientOriginalName();
                $file->move(public_path('images/scholarships'), $name);
                $data['featured_image'] = 'images/scholarships/' . $name;
            }

            if ($request->hasFile('banner_image')) {
                $file = $request->file('banner_image');
                $name = time() . '_ban_' . $file->getClientOriginalName();
                $file->move(public_path('images/scholarships'), $name);
                $data['banner_image'] = 'images/scholarships/' . $name;
            }

            if ($request->hasFile('provider_logo')) {
                $file = $request->file('provider_logo');
                $name = time() . '_logo_' . $file->getClientOriginalName();
                $file->move(public_path('images/scholarships'), $name);
                $data['provider_logo'] = 'images/scholarships/' . $name;
            }

            $scholarship = Scholarship::create($data);

            // 1. Eligibility
            if ($request->has('eligibility')) {
                $eligData = $request->input('eligibility');
                $eligData['graduation_required'] = isset($eligData['graduation_required']) ? 1 : 0;
                $scholarship->eligibility()->create($eligData);
            }

            // 2. Benefits
            if ($request->has('benefits')) {
                foreach ($request->input('benefits') as $benefit) {
                    if (!empty($benefit['benefit_title'])) {
                        $scholarship->benefits()->create([
                            'benefit_title' => $benefit['benefit_title'],
                            'benefit_description' => $benefit['benefit_description'] ?? null,
                            'benefit_amount' => $benefit['benefit_amount'] ?? null,
                            'sort_order' => $benefit['sort_order'] ?? 0,
                        ]);
                    }
                }
            }

            // 3. Courses (Pivot)
            if ($request->has('courses')) {
                $scholarship->courses()->sync($request->input('courses'));
            }

            // 4. Universities (Pivot)
            if ($request->has('universities')) {
                $scholarship->universities()->sync($request->input('universities'));
            }

            // 5. Documents
            if ($request->has('documents')) {
                foreach ($request->input('documents') as $doc) {
                    if (!empty($doc['document_name'])) {
                        $scholarship->documents()->create([
                            'document_name' => $doc['document_name'],
                            'is_mandatory' => isset($doc['is_mandatory']) ? 1 : 0,
                        ]);
                    }
                }
            }

            // 6. Dates
            if ($request->has('dates')) {
                $scholarship->dates()->create($request->input('dates'));
            }

            // 7. FAQs
            if ($request->has('faqs')) {
                foreach ($request->input('faqs') as $faq) {
                    if (!empty($faq['question']) && !empty($faq['answer'])) {
                        $scholarship->faqs()->create([
                            'question' => $faq['question'],
                            'answer' => $faq['answer'],
                            'sort_order' => $faq['sort_order'] ?? 0,
                        ]);
                    }
                }
            }

            // 8. Highlights
            if ($request->has('highlights')) {
                foreach ($request->input('highlights') as $h) {
                    if (!empty($h['highlight_text'])) {
                        $scholarship->highlights()->create([
                            'highlight_text' => $h['highlight_text'],
                            'highlight_icon' => $h['highlight_icon'] ?? null,
                            'sort_order'     => $h['sort_order'] ?? 0,
                        ]);
                    }
                }
            }

            // 9. Gallery Uploads
            if ($request->hasFile('gallery_images')) {
                foreach ($request->file('gallery_images') as $index => $file) {
                    $name = time() . '_gal_' . $index . '_' . $file->getClientOriginalName();
                    $file->move(public_path('images/scholarships/gallery'), $name);
                    $scholarship->gallery()->create([
                        'image'      => 'images/scholarships/gallery/' . $name,
                        'sort_order' => $index,
                    ]);
                }
            }

            // 10. SEO
            if ($request->has('seo')) {
                $seo = $request->input('seo');
                $seo['no_index']  = isset($seo['no_index']) ? 1 : 0;
                $seo['no_follow'] = isset($seo['no_follow']) ? 1 : 0;
                $scholarship->seo()->create($seo);
            }

            DB::commit();
            return redirect()->route('admin.scholarships.index')->with('success', 'Scholarship created successfully.');

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withInput()->withErrors(['error' => 'Failed to create scholarship: ' . $e->getMessage()]);
        }
    }

    public function edit(Scholarship $scholarship)
    {
        $courses = Course::where('status', 1)->orderBy('name')->get();
        $organisations = Organisation::where('status', 1)->orderBy('name')->get();
        $scholarship->load(['eligibility', 'benefits', 'highlights', 'courses', 'universities', 'documents', 'dates', 'faqs', 'gallery', 'seo']);
        return view('admin.scholarships.edit', compact('scholarship', 'courses', 'organisations'));
    }

    public function update(Request $request, Scholarship $scholarship)
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'slug' => 'nullable|string|unique:scholarships,slug,' . $scholarship->id,
            'short_name' => 'nullable|string|max:255',
            'scholarship_code' => 'nullable|string|max:255',
            'status' => 'required|boolean',
            'featured' => 'required|boolean',
            'featured_on_homepage' => 'required|boolean',
            'sort_order' => 'required|integer',
            'max_amount' => 'nullable|numeric|min:0',
            'featured_image' => 'nullable|image|max:2048',
            'banner_image' => 'nullable|image|max:2048',
            'provider_logo' => 'nullable|image|max:2048',
        ]);

        try {
            DB::beginTransaction();

            $data = $request->except(['eligibility', 'benefits', 'highlights', 'courses', 'universities', 'dates', 'documents', 'faqs', 'gallery', 'seo', 'existing_gallery_delete']);
            $data['slug'] = $request->slug ?: Str::slug($request->title);
            $data['updated_by'] = auth()->id();

            // Handle uploads
            if ($request->hasFile('featured_image')) {
                if ($scholarship->featured_image && file_exists(public_path($scholarship->featured_image))) {
                    @unlink(public_path($scholarship->featured_image));
                }
                $file = $request->file('featured_image');
                $name = time() . '_feat_' . $file->getClientOriginalName();
                $file->move(public_path('images/scholarships'), $name);
                $data['featured_image'] = 'images/scholarships/' . $name;
            }

            if ($request->hasFile('banner_image')) {
                if ($scholarship->banner_image && file_exists(public_path($scholarship->banner_image))) {
                    @unlink(public_path($scholarship->banner_image));
                }
                $file = $request->file('banner_image');
                $name = time() . '_ban_' . $file->getClientOriginalName();
                $file->move(public_path('images/scholarships'), $name);
                $data['banner_image'] = 'images/scholarships/' . $name;
            }

            if ($request->hasFile('provider_logo')) {
                if ($scholarship->provider_logo && file_exists(public_path($scholarship->provider_logo))) {
                    @unlink(public_path($scholarship->provider_logo));
                }
                $file = $request->file('provider_logo');
                $name = time() . '_logo_' . $file->getClientOriginalName();
                $file->move(public_path('images/scholarships'), $name);
                $data['provider_logo'] = 'images/scholarships/' . $name;
            }

            $scholarship->update($data);

            // 1. Eligibility
            if ($request->has('eligibility')) {
                $eligData = $request->input('eligibility');
                $eligData['graduation_required'] = isset($eligData['graduation_required']) ? 1 : 0;
                $scholarship->eligibility()->updateOrCreate([], $eligData);
            }

            // 2. Benefits
            $scholarship->benefits()->delete();
            if ($request->has('benefits')) {
                foreach ($request->input('benefits') as $benefit) {
                    if (!empty($benefit['benefit_title'])) {
                        $scholarship->benefits()->create([
                            'benefit_title' => $benefit['benefit_title'],
                            'benefit_description' => $benefit['benefit_description'] ?? null,
                            'benefit_amount' => $benefit['benefit_amount'] ?? null,
                            'sort_order' => $benefit['sort_order'] ?? 0,
                        ]);
                    }
                }
            }

            // 3. Courses (Pivot)
            $scholarship->courses()->sync($request->input('courses', []));

            // 4. Universities (Pivot)
            $scholarship->universities()->sync($request->input('universities', []));

            // 5. Documents
            $scholarship->documents()->delete();
            if ($request->has('documents')) {
                foreach ($request->input('documents') as $doc) {
                    if (!empty($doc['document_name'])) {
                        $scholarship->documents()->create([
                            'document_name' => $doc['document_name'],
                            'is_mandatory' => isset($doc['is_mandatory']) ? 1 : 0,
                        ]);
                    }
                }
            }

            // 6. Dates
            $scholarship->dates()->updateOrCreate([], $request->input('dates', []));

            // 7. FAQs
            $scholarship->faqs()->delete();
            if ($request->has('faqs')) {
                foreach ($request->input('faqs') as $faq) {
                    if (!empty($faq['question']) && !empty($faq['answer'])) {
                        $scholarship->faqs()->create([
                            'question' => $faq['question'],
                            'answer' => $faq['answer'],
                            'sort_order' => $faq['sort_order'] ?? 0,
                        ]);
                    }
                }
            }

            // 8. Highlights (replace all)
            $scholarship->highlights()->delete();
            if ($request->has('highlights')) {
                foreach ($request->input('highlights') as $h) {
                    if (!empty($h['highlight_text'])) {
                        $scholarship->highlights()->create([
                            'highlight_text' => $h['highlight_text'],
                            'highlight_icon' => $h['highlight_icon'] ?? null,
                            'sort_order'     => $h['sort_order'] ?? 0,
                        ]);
                    }
                }
            }

            // 9. Gallery Deletions
            if ($request->has('existing_gallery_delete')) {
                foreach ($request->input('existing_gallery_delete') as $galId) {
                    $galItem = $scholarship->gallery()->find($galId);
                    if ($galItem) {
                        if (file_exists(public_path($galItem->image))) {
                            @unlink(public_path($galItem->image));
                        }
                        $galItem->delete();
                    }
                }
            }

            // 10. Gallery Uploads
            if ($request->hasFile('gallery_images')) {
                foreach ($request->file('gallery_images') as $index => $file) {
                    $name = time() . '_gal_' . $index . '_' . $file->getClientOriginalName();
                    $file->move(public_path('images/scholarships/gallery'), $name);
                    $scholarship->gallery()->create([
                        'image'      => 'images/scholarships/gallery/' . $name,
                        'sort_order' => $index,
                    ]);
                }
            }

            // 11. SEO
            if ($request->has('seo')) {
                $seo = $request->input('seo');
                $seo['no_index']  = isset($seo['no_index']) ? 1 : 0;
                $seo['no_follow'] = isset($seo['no_follow']) ? 1 : 0;
                $scholarship->seo()->updateOrCreate([], $seo);
            }

            DB::commit();
            return redirect()->route('admin.scholarships.index')->with('success', 'Scholarship updated successfully.');

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withInput()->withErrors(['error' => 'Failed to update scholarship: ' . $e->getMessage()]);
        }
    }

    public function destroy(Scholarship $scholarship)
    {
        try {
            DB::beginTransaction();

            // Soft delete – keep associated files
            $scholarship->delete();

            DB::commit();
            return redirect()->route('admin.scholarships.index')->with('success', 'Scholarship deleted successfully.');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withErrors(['error' => 'Failed to delete scholarship: ' . $e->getMessage()]);
        }
    }

    // ─── Autosave: Create draft ──────────────────────────────────────────────
    public function autosave(Request $request)
    {
        // Don't save until user has at least typed a title
        if (empty(trim($request->input('title', '')))) {
            return response()->json(['status' => 'skipped', 'message' => 'Title is required to autosave.']);
        }

        try {
            DB::beginTransaction();

            $data = $request->except([
                '_token', 'eligibility', 'benefits', 'highlights', 'courses',
                'universities', 'dates', 'documents', 'faqs', 'gallery_images', 'seo',
            ]);

            // Slug fallback
            if (empty($data['slug']) && !empty($data['title'])) {
                $data['slug'] = \Illuminate\Support\Str::slug($data['title']);
            }
            // Ensure unique slug
            if (!empty($data['slug'])) {
                $base = $data['slug'];
                $i = 1;
                while (Scholarship::where('slug', $data['slug'])->exists()) {
                    $data['slug'] = $base . '-' . $i++;
                }
            }

            $data['created_by'] = auth()->id();
            $data['status']     = $data['status'] ?? 0;
            $data['featured']   = $data['featured'] ?? 0;
            $data['featured_on_homepage'] = $data['featured_on_homepage'] ?? 0;
            $data['sort_order'] = $data['sort_order'] ?? 0;


            // File uploads
            foreach (['featured_image', 'banner_image', 'provider_logo'] as $img) {
                if ($request->hasFile($img)) {
                    $file = $request->file($img);
                    $name = time() . '_' . $img . '.' . $file->extension();
                    $file->move(public_path('images/scholarships'), $name);
                    $data[$img] = 'images/scholarships/' . $name;
                }
            }

            $scholarship = Scholarship::create($data);

            // Eligibility
            if ($request->has('eligibility')) {
                $elig = $request->input('eligibility');
                $elig['graduation_required'] = isset($elig['graduation_required']) ? 1 : 0;
                $scholarship->eligibility()->create($elig);
            }

            // Benefits
            if ($request->has('benefits')) {
                foreach ($request->input('benefits') as $b) {
                    if (!empty($b['benefit_title'])) {
                        $scholarship->benefits()->create([
                            'benefit_title'       => $b['benefit_title'],
                            'benefit_description' => $b['benefit_description'] ?? null,
                            'benefit_amount'      => $b['benefit_amount'] ?? null,
                            'sort_order'          => $b['sort_order'] ?? 0,
                        ]);
                    }
                }
            }

            // Courses & Universities pivot
            if ($request->has('courses')) {
                $scholarship->courses()->sync($request->input('courses'));
            }
            if ($request->has('universities')) {
                $scholarship->universities()->sync($request->input('universities'));
            }

            // Documents
            if ($request->has('documents')) {
                foreach ($request->input('documents') as $doc) {
                    if (!empty($doc['document_name'])) {
                        $scholarship->documents()->create([
                            'document_name' => $doc['document_name'],
                            'is_mandatory'  => isset($doc['is_mandatory']) ? 1 : 0,
                        ]);
                    }
                }
            }

            // Dates
            if ($request->has('dates')) {
                $scholarship->dates()->create($request->input('dates'));
            }

            // FAQs
            if ($request->has('faqs')) {
                foreach ($request->input('faqs') as $faq) {
                    if (!empty($faq['question'])) {
                        $scholarship->faqs()->create([
                            'question'   => $faq['question'],
                            'answer'     => $faq['answer'] ?? '',
                            'sort_order' => $faq['sort_order'] ?? 0,
                        ]);
                    }
                }
            }

            DB::commit();

            return response()->json([
                'status'         => 'success',
                'message'        => 'Draft saved.',
                'scholarship_id' => $scholarship->id,
                'edit_url'       => route('admin.scholarships.edit', $scholarship->id),
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['status' => 'error', 'message' => $e->getMessage()], 500);
        }
    }

    // ─── Autosave: Update existing ───────────────────────────────────────────
    public function autosaveUpdate(Request $request, Scholarship $scholarship)
    {
        try {
            DB::beginTransaction();

            $data = $request->except([
                '_token', '_method', 'eligibility', 'benefits', 'highlights', 'courses',
                'universities', 'dates', 'documents', 'faqs', 'seo',
                'gallery_images', 'existing_gallery_delete',
            ]);

            if (empty($data['slug']) && !empty($data['title'])) {
                $data['slug'] = \Illuminate\Support\Str::slug($data['title']);
            }
            $data['updated_by'] = auth()->id();

            // File uploads
            foreach (['featured_image', 'banner_image', 'provider_logo'] as $img) {
                if ($request->hasFile($img)) {
                    if ($scholarship->$img && file_exists(public_path($scholarship->$img))) {
                        @unlink(public_path($scholarship->$img));
                    }
                    $file = $request->file($img);
                    $name = time() . '_' . $img . '.' . $file->extension();
                    $file->move(public_path('images/scholarships'), $name);
                    $data[$img] = 'images/scholarships/' . $name;
                }
            }

            $scholarship->update($data);

            // Eligibility
            if ($request->has('eligibility')) {
                $elig = $request->input('eligibility');
                $elig['graduation_required'] = isset($elig['graduation_required']) ? 1 : 0;
                $scholarship->eligibility()->updateOrCreate([], $elig);
            }

            // Benefits (replace all)
            $scholarship->benefits()->delete();
            if ($request->has('benefits')) {
                foreach ($request->input('benefits') as $b) {
                    if (!empty($b['benefit_title'])) {
                        $scholarship->benefits()->create([
                            'benefit_title'       => $b['benefit_title'],
                            'benefit_description' => $b['benefit_description'] ?? null,
                            'benefit_amount'      => $b['benefit_amount'] ?? null,
                            'sort_order'          => $b['sort_order'] ?? 0,
                        ]);
                    }
                }
            }

            // Courses & Universities pivot
            $scholarship->courses()->sync($request->input('courses', []));
            $scholarship->universities()->sync($request->input('universities', []));

            // Documents (replace all)
            $scholarship->documents()->delete();
            if ($request->has('documents')) {
                foreach ($request->input('documents') as $doc) {
                    if (!empty($doc['document_name'])) {
                        $scholarship->documents()->create([
                            'document_name' => $doc['document_name'],
                            'is_mandatory'  => isset($doc['is_mandatory']) ? 1 : 0,
                        ]);
                    }
                }
            }

            // Dates
            $scholarship->dates()->updateOrCreate([], $request->input('dates', []));

            // FAQs (replace all)
            $scholarship->faqs()->delete();
            if ($request->has('faqs')) {
                foreach ($request->input('faqs') as $faq) {
                    if (!empty($faq['question'])) {
                        $scholarship->faqs()->create([
                            'question'   => $faq['question'],
                            'answer'     => $faq['answer'] ?? '',
                            'sort_order' => $faq['sort_order'] ?? 0,
                        ]);
                    }
                }
            }

            DB::commit();

            return response()->json([
                'status'  => 'success',
                'message' => 'Auto-saved.',
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['status' => 'error', 'message' => $e->getMessage()], 500);
        }
    }

    // ─── Trash (soft-deleted list) ────────────────────────────────────────────
    public function trash()
    {
        $scholarships = Scholarship::onlyTrashed()->latest()->get();
        return view('admin.scholarships.trash', compact('scholarships'));
    }

    // ─── Restore soft-deleted scholarship ────────────────────────────────────
    public function restore($id)
    {
        $scholarship = Scholarship::onlyTrashed()->findOrFail($id);
        $scholarship->restore();
        return redirect()->route('admin.scholarships.trash')->with('success', 'Scholarship restored successfully.');
    }
}


<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\DynamicExam;
use App\Models\DynamicExamSection;
use App\Models\Organisation;
use App\Models\ExamStage;
use App\Models\CasteCategory;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class DynamicExamController extends Controller
{
    public function index()
    {
        $exams = DynamicExam::latest()->paginate(10);
        return view('admin.dynamic-exams.index', compact('exams'));
    }

    public function create()
    {
        return view('admin.dynamic-exams.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'logo' => 'nullable|image|max:2048',
            'cover_image' => 'nullable|image|max:2048',
        ]);

        $slug = Str::slug($request->name);
        
        // Ensure unique slug
        $count = DynamicExam::where('slug', 'like', $slug . '%')->count();
        if ($count > 0) {
            $slug = $slug . '-' . time();
        }

        $logoPath = null;
        if ($request->hasFile('logo')) {
            $file = $request->file('logo');
            $name = time() . '_' . $file->getClientOriginalName();
            $file->move(public_path('media/dynamic_exams/logos'), $name);
            $logoPath = 'media/dynamic_exams/logos/' . $name;
        }

        $coverImagePath = null;
        if ($request->hasFile('cover_image')) {
            $file = $request->file('cover_image');
            $name = time() . '_' . $file->getClientOriginalName();
            $file->move(public_path('media/dynamic_exams/covers'), $name);
            $coverImagePath = 'media/dynamic_exams/covers/' . $name;
        }

        $exam = DynamicExam::create([
            'name' => $validated['name'],
            'slug' => $slug,
            'status' => $request->status ?? 'Active',
            'visibility' => $request->visibility ?? 'Public',
            'official_website' => $request->official_website,
            'featured_exam' => $request->has('featured_exam') ? 1 : 0,
            'has_stages' => $request->has('has_stages') ? 1 : 0,
            
            'short_name' => $request->short_name,
            'exam_type' => $request->exam_type,
            'exam_category' => $request->exam_category, // casts to JSON internally
            'conducting_body_type' => $request->conducting_body_type,
            'exam_frequency' => $request->exam_frequency,
            'conducting_authority_name' => $request->conducting_authority_name,
            'logo' => $logoPath,
            'cover_image' => $coverImagePath,
            'exam_source_type' => $request->exam_source_type ?? 'External',
            'owning_organisation_id' => $request->owning_organisation_id,
            'about_exam' => $request->about_exam,
        ]);

        return redirect()->route('admin.dynamic-exams.edit', $exam->id)->with('success', 'Exam created. Now you can build its content.');
    }

    public function edit(DynamicExam $dynamicExam)
    {
        $dynamicExam->load('sections');
        $organisations = Organisation::where('status', true)->select('id', 'name')->get();
        $allStages = ExamStage::where('status', true)->orderBy('sort_order')->get();
        
        return view('admin.dynamic-exams.edit', compact('dynamicExam', 'organisations', 'allStages'));
    }

    public function update(Request $request, DynamicExam $dynamicExam)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'logo' => 'nullable|image|max:2048',
            'cover_image' => 'nullable|image|max:2048',
        ]);

        $data = $request->only([
            'name', 'status', 'visibility', 'official_website', 'short_name', 
            'exam_type', 'exam_category', 'conducting_body_type', 'exam_frequency', 
            'conducting_authority_name', 'exam_source_type', 'owning_organisation_id', 
            'about_exam'
        ]);

        if ($request->has('featured_exam')) $data['featured_exam'] = 1;
        if ($request->has('has_stages')) $data['has_stages'] = 1;

        if ($request->hasFile('logo')) {
            $file = $request->file('logo');
            $name = time() . '_' . $file->getClientOriginalName();
            $file->move(public_path('media/dynamic_exams/logos'), $name);
            $data['logo'] = 'media/dynamic_exams/logos/' . $name;
        }
        if ($request->hasFile('cover_image')) {
            $file = $request->file('cover_image');
            $name = time() . '_' . $file->getClientOriginalName();
            $file->move(public_path('media/dynamic_exams/covers'), $name);
            $data['cover_image'] = 'media/dynamic_exams/covers/' . $name;
        }

        // Update Exam basic details
        $dynamicExam->update($data);

        // Update sections
        if ($request->has('sections')) {
            $sectionsData = json_decode($request->sections, true);
            
            // Delete old sections not in the new request
            $keepSectionIds = collect($sectionsData)->pluck('id')->filter()->toArray();
            $dynamicExam->sections()->whereNotIn('id', $keepSectionIds)->delete();

            // Create or Update
            foreach ($sectionsData as $index => $section) {
                $dynamicExam->sections()->updateOrCreate(
                    ['id' => $section['id'] ?? null],
                    [
                        'heading' => $section['heading'],
                        'content' => $section['content'] ?? [],
                        'order' => $index,
                        'status' => 1
                    ]
                );
            }
        } else {
             $dynamicExam->sections()->delete();
        }

        if ($request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => 'Exam layout updated successfully!'
            ]);
        }

        return redirect()->back()->with('success', 'Exam layout updated successfully!');
    }

    /**
     * AJAX - Autosave a single tab (Core Identity or a dynamic section)
     */
    public function autosaveTab(Request $request, DynamicExam $dynamicExam)
    {
        $tab = $request->input('_tab'); // 'core' or section_id

        if ($tab === 'core') {
            // Save core exam identity fields
            $data = $request->except(['_token', '_tab']);

            if ($request->hasFile('logo')) {
                $file = $request->file('logo');
                $name = time() . '_' . $file->getClientOriginalName();
                $file->move(public_path('media/dynamic_exams/logos'), $name);
                $data['logo'] = 'media/dynamic_exams/logos/' . $name;
            } else {
                unset($data['logo']);
            }
            
            if ($request->hasFile('cover_image')) {
                $file = $request->file('cover_image');
                $name = time() . '_' . $file->getClientOriginalName();
                $file->move(public_path('media/dynamic_exams/covers'), $name);
                $data['cover_image'] = 'media/dynamic_exams/covers/' . $name;
            } else {
                unset($data['cover_image']);
            }

            // status/visibility/featured/stages logic
            $data['status'] = $request->status;
            $data['visibility'] = $request->visibility ?? 'Public';
            $data['featured_exam'] = $request->has('featured_exam') ? 1 : 0;
            $data['has_stages'] = $request->has('has_stages') ? 1 : 0;
            $data['selected_stages'] = $request->input('selected_stages', []);
            $data['exam_category'] = $request->input('exam_category', []);

            // Only update allowed fields
            $dynamicExam->update(array_intersect_key($data, array_flip([
                'name','short_name','exam_type','exam_category','conducting_body_type',
                'exam_frequency','conducting_authority_name','logo','cover_image',
                'exam_source_type','owning_organisation_id','about_exam','status',
                'official_website','visibility','featured_exam','has_stages','selected_stages'
            ])));

            return response()->json(['success' => true, 'message' => 'Core Identity saved!']);
        }

        // Dynamic section tab save
        $sectionId = (int) $tab;
        $dynamicExam->load('sections');
        $section = $dynamicExam->sections->where('id', $sectionId)->first();

        if (!$section) {
            return response()->json(['success' => false, 'message' => 'Section not found'], 404);
        }

        $fields = $request->input('data.' . $sectionId, []);
        $content = $section->content ?? [];

        foreach ($content as &$el) {
            if ($el['type'] === 'input' && isset($fields[$el['name']])) {
                $value = $fields[$el['name']];
                if ($value instanceof \Illuminate\Http\UploadedFile) {
                    $name = time() . '_' . $value->getClientOriginalName();
                    $value->move(public_path('media/dynamic_exams_data'), $name);
                    $value = 'media/dynamic_exams_data/' . $name;
                } elseif (is_array($value)) {
                    $value = json_encode($value);
                }
                $el['value'] = $value;
            } elseif ($el['type'] === 'input' && $el['inputType'] === 'checkbox' && !isset($fields[$el['name']])) {
                $el['value'] = json_encode([]);
            }
        }

        $section->content = $content;
        $section->save();

        return response()->json(['success' => true, 'message' => 'Section saved!']);
    }

    public function data(DynamicExam $dynamicExam)
    {
        $dynamicExam->load('sections');
        $organisations = Organisation::where('status', true)->select('id', 'name')->get();
        $allStages = ExamStage::where('status', true)->orderBy('sort_order')->get();
        $casteCategories = CasteCategory::where('status', true)->orderBy('name')->get();
        
        return view('admin.dynamic-exams.data', compact('dynamicExam', 'organisations', 'allStages', 'casteCategories'));
    }

    public function saveData(Request $request, DynamicExam $dynamicExam)
    {
        $dynamicExam->load('sections');
        $inputs = $request->except(['_token', '_method']);

        // The inputs are categorized by section id and then field name.
        // E.g. name="data[{section_id}][{field_name}]"
        
        if (isset($inputs['data']) && is_array($inputs['data'])) {
            foreach ($inputs['data'] as $sectionId => $fields) {
                $section = $dynamicExam->sections->where('id', $sectionId)->first();
                if ($section && isset($section->content)) {
                    $content = $section->content;
                    foreach ($content as &$el) {
                        if ($el['type'] === 'input' && isset($fields[$el['name']])) {
                            $value = $fields[$el['name']];
                            
                            // Handle file uploads
                            if ($value instanceof \Illuminate\Http\UploadedFile) {
                                $value = $value->store('dynamic_exams_data', 'public');
                            }
                            // Handle checkboxes (arrays)
                            elseif (is_array($value)) {
                                $value = json_encode($value);
                            }
                            
                            $el['value'] = $value;
                        } elseif ($el['type'] === 'input' && $el['inputType'] === 'file' && isset($fields['old_' . $el['name']])) {
                            // Preserve old file if new one isn't uploaded
                            $el['value'] = $fields['old_' . $el['name']];
                        } elseif ($el['type'] === 'input' && $el['inputType'] === 'checkbox' && !isset($fields[$el['name']])) {
                            // Handle empty checkbox array
                            $el['value'] = json_encode([]);
                        }
                    }
                    $section->content = $content;
                    $section->save();
                }
            }
        }

        return redirect()->back()->with('success', 'Exam data saved successfully!');
    }

    public function destroy(DynamicExam $dynamicExam)
    {
        $dynamicExam->delete();
        return redirect()->route('admin.dynamic-exams.index')->with('success', 'Exam deleted successfully!');
    }
}

<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Counselling;
use App\Models\DynamicExam;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Schema;

class DynamicCounsellingController extends Controller
{
    public function index(DynamicExam $dynamicExam)
    {
        $counsellings = Counselling::where('dynamic_exam_id', $dynamicExam->id)
            ->orderBy('created_at', 'desc')->get();

        // Pass as $exam alias so existing views work
        return view('admin.dynamic-exams.counsellings.index', compact('dynamicExam', 'counsellings'));
    }

    public function create(DynamicExam $dynamicExam)
    {
        return view('admin.dynamic-exams.counsellings.create', compact('dynamicExam'));
    }

    public function store(Request $request, DynamicExam $dynamicExam)
    {
        $input = $request->all();
        $input['dynamic_exam_id'] = $dynamicExam->id;
        $input['exam_id'] = null;

        if (array_key_exists('slug', $input) && empty($input['slug'])) {
            unset($input['slug']);
        }

        $counselling = Counselling::create($input);

        return redirect()->route('admin.dynamic-exams.counsellings.edit', [$dynamicExam->id, $counselling->id])
            ->with('success', 'Counselling created successfully! Now you can build its structure.');
    }

    public function edit(DynamicExam $dynamicExam, Counselling $counselling)
    {
        $counselling->load('sections');
        return view('admin.dynamic-exams.counsellings.edit', compact('dynamicExam', 'counselling'));
    }

    public function update(Request $request, DynamicExam $dynamicExam, Counselling $counselling)
    {
        // For structure building (Schema Builder)
        if ($request->has('sections')) {
            $sectionsData = json_decode($request->sections, true);

            // Delete old sections not in the new request
            $keepSectionIds = collect($sectionsData)->pluck('id')->filter()->toArray();
            $counselling->sections()->whereNotIn('id', $keepSectionIds)->delete();

            // Create or Update
            foreach ($sectionsData as $index => $section) {
                $counselling->sections()->updateOrCreate(
                    ['id' => $section['id'] ?? null],
                    [
                        'heading' => $section['heading'],
                        'content' => $section['content'] ?? [],
                        'order' => $index,
                        'status' => 1
                    ]
                );
            }

            if ($request->ajax()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Counselling structure updated successfully!'
                ]);
            }
        }

        // Standard update for identity fields
        $input = $request->except(['sections', '_token', '_method']);
        if (array_key_exists('slug', $input) && empty($input['slug'])) {
            unset($input['slug']);
        }
        $counselling->update($input);

        return redirect()->route('admin.dynamic-exams.counsellings.index', $dynamicExam->id)
            ->with('success', 'Counselling updated successfully');
    }

    public function data(DynamicExam $dynamicExam, Counselling $counselling)
    {
        $counselling->load('sections');
        return view('admin.dynamic-exams.counsellings.data', compact('dynamicExam', 'counselling'));
    }

    public function saveData(Request $request, DynamicExam $dynamicExam, Counselling $counselling)
    {
        $counselling->load('sections');
        $inputs = $request->except(['_token', '_method']);

        if (isset($inputs['data']) && is_array($inputs['data'])) {
            foreach ($inputs['data'] as $sectionId => $fields) {
                $section = $counselling->sections->where('id', $sectionId)->first();
                if ($section && isset($section->content)) {
                    $content = $section->content;
                    foreach ($content as &$el) {
                        if ($el['type'] === 'input' && isset($fields[$el['name']])) {
                            $value = $fields[$el['name']];

                            // Handle file uploads
                            if ($value instanceof \Illuminate\Http\UploadedFile) {
                                $value = $value->store('dynamic_counsellings_data', 'public');
                            }
                            // Handle checkboxes (arrays)
                            elseif (is_array($value)) {
                                $value = json_encode($value);
                            }

                            $el['value'] = $value;
                        } elseif ($el['type'] === 'input' && $el['inputType'] === 'file' && isset($fields['old_' . $el['name']])) {
                            $el['value'] = $fields['old_' . $el['name']];
                        } elseif ($el['type'] === 'input' && $el['inputType'] === 'checkbox' && !isset($fields[$el['name']])) {
                            $el['value'] = json_encode([]);
                        }
                    }
                    $section->content = $content;
                    $section->save();
                }
            }
        }

        return redirect()->back()->with('success', 'Counselling data saved successfully!');
    }

    public function autosaveTab(Request $request, $dynamicExamId, $id)
    {
        $tab = $request->input('_tab');
        $counselling = Counselling::findOrFail($id);

        if ($tab === 'core') {
            $data = $request->except(['_token', '_tab']);
            if (array_key_exists('slug', $data) && empty($data['slug'])) {
                unset($data['slug']);
            }

            $booleans = [
                'domicile_required', 'minimum_exam_qualification_required', 'choice_locking_required',
                'original_documents_required_at_reporting', 'registration_fee_required',
                'late_registration_allowed', 'security_deposit_required', 'transaction_charges_applicable',
                'partial_refund_allowed'
            ];

            $updateData = [];
            foreach ($data as $key => $value) {
                if (Schema::hasColumn('counsellings', $key)) {
                    if (in_array($key, $booleans)) {
                        $updateData[$key] = ($value === 'true' || $value === 1 || $value === '1' || $value === true) ? 1 : 0;
                    } elseif ($key === 'number_of_rounds') {
                        $updateData[$key] = $value ?: 1;
                    } elseif ($key === 'data_confidence_score') {
                        $updateData[$key] = $value ?: 100;
                    } else {
                        $updateData[$key] = $value;
                    }
                }
            }
            $counselling->update($updateData);
            return response()->json(['success' => true, 'message' => 'Identity saved!']);
        }

        // Dynamic section tab save
        $sectionId = (int) $tab;
        $counselling->load('sections');
        $section = $counselling->sections->where('id', $sectionId)->first();

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
                    $value->move(public_path('media/dynamic_counsellings_data'), $name);
                    $value = 'media/dynamic_counsellings_data/' . $name;
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
    public function destroy(DynamicExam $dynamicExam, Counselling $counselling)
    {
        $counselling->delete();
        return redirect()->route('admin.dynamic-exams.counsellings.index', $dynamicExam->id)
            ->with('success', 'Counselling deleted successfully');
    }

    public function storeDraft(Request $request, DynamicExam $dynamicExam)
    {
        $request->validate([
            'counselling_name' => 'required|string|max:255',
            'conducting_authority_name' => 'required|string',
            'counselling_type' => 'required|string',
        ]);

        $input = $request->all();
        $input['dynamic_exam_id'] = $dynamicExam->id;
        $input['exam_id'] = null;

        if (array_key_exists('slug', $input) && empty($input['slug'])) {
            unset($input['slug']);
        }

        $counselling = Counselling::create($input);

        return response()->json([
            'status' => 'success',
            'counselling_id' => $counselling->id,
            'message' => 'Draft created successfully'
        ]);
    }
}

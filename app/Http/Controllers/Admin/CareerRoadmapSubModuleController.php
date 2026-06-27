<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\CareerRoadmapSubModule;
use App\Models\CareerRoadmapStage;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class CareerRoadmapSubModuleController extends Controller
{
    public function index(Request $request)
    {
        $query = CareerRoadmapSubModule::with(['stage', 'parent'])->latest();
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where('title', 'like', "%{$search}%")
                  ->orWhereHas('stage', function($q) use ($search) {
                      $q->where('title', 'like', "%{$search}%");
                  })
                  ->orWhereHas('parent', function($q) use ($search) {
                      $q->where('title', 'like', "%{$search}%");
                  });
        }
        $subModules = $query->paginate(15)->appends($request->all());
        return view('admin.career_roadmap_sub_modules.index', compact('subModules'));
    }

    public function create()
    {
        $stages = CareerRoadmapStage::where('status', 1)->get();
        $parents = CareerRoadmapSubModule::where('status', 1)->get();
        return view('admin.career_roadmap_sub_modules.create', compact('stages', 'parents'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'stage_id' => 'required|exists:career_roadmap_stages,id',
            'parent_id' => 'nullable|exists:career_roadmap_sub_modules,id',
            'title' => 'required|string|max:255',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg,webp|max:2048',
            'description' => 'nullable|string',
            'long_description' => 'nullable|string',
            'status' => 'nullable|boolean',
            'custom_field_names' => 'nullable|array',
            'custom_field_values' => 'nullable|array',
        ]);

        // Process custom fields
        $customFields = null;
        if ($request->has('custom_field_names') && $request->has('custom_field_values')) {
            $names = $request->custom_field_names;
            $values = $request->custom_field_values;
            $customFields = [];
            foreach ($names as $index => $name) {
                if (!empty($name) && isset($values[$index])) {
                    $customFields[$name] = $values[$index];
                }
            }
            if (empty($customFields)) {
                $customFields = null;
            }
        }

        $data = [
            'stage_id' => $request->stage_id,
            'parent_id' => $request->parent_id,
            'title' => $request->title,
            'description' => $request->description,
            'long_description' => $request->long_description,
            'custom_fields' => $customFields,
            'status' => $request->has('status') ? 1 : 0,
        ];

        if ($request->hasFile('image')) {
            $imageName = time() . '-' . uniqid() . '.' . $request->image->extension();
            $request->image->move(public_path('uploads/career_roadmap_sub_modules'), $imageName);
            $data['image'] = 'uploads/career_roadmap_sub_modules/' . $imageName;
        }

        CareerRoadmapSubModule::create($data);

        return redirect()->route('admin.career-roadmap-sub-modules.index')->with('success', 'Sub-module created successfully.');
    }

    public function edit(CareerRoadmapSubModule $careerRoadmapSubModule)
    {
        $stages = CareerRoadmapStage::where('status', 1)->get();
        // Prevent setting itself as parent
        $parents = CareerRoadmapSubModule::where('status', 1)->where('id', '!=', $careerRoadmapSubModule->id)->get();
        return view('admin.career_roadmap_sub_modules.edit', compact('careerRoadmapSubModule', 'stages', 'parents'));
    }

    public function update(Request $request, CareerRoadmapSubModule $careerRoadmapSubModule)
    {
        $request->validate([
            'stage_id' => 'required|exists:career_roadmap_stages,id',
            'parent_id' => 'nullable|exists:career_roadmap_sub_modules,id|not_in:' . $careerRoadmapSubModule->id,
            'title' => 'required|string|max:255',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg,webp|max:2048',
            'description' => 'nullable|string',
            'long_description' => 'nullable|string',
            'status' => 'nullable|boolean',
            'custom_field_names' => 'nullable|array',
            'custom_field_values' => 'nullable|array',
        ]);

        // Process custom fields
        $customFields = null;
        if ($request->has('custom_field_names') && $request->has('custom_field_values')) {
            $names = $request->custom_field_names;
            $values = $request->custom_field_values;
            $customFields = [];
            foreach ($names as $index => $name) {
                if (!empty($name) && isset($values[$index])) {
                    $customFields[$name] = $values[$index];
                }
            }
            if (empty($customFields)) {
                $customFields = null;
            }
        }

        $data = [
            'stage_id' => $request->stage_id,
            'parent_id' => $request->parent_id,
            'title' => $request->title,
            'description' => $request->description,
            'long_description' => $request->long_description,
            'custom_fields' => $customFields,
            'status' => $request->has('status') ? 1 : 0,
        ];

        if ($request->hasFile('image')) {
            if ($careerRoadmapSubModule->image && file_exists(public_path($careerRoadmapSubModule->image))) {
                unlink(public_path($careerRoadmapSubModule->image));
            }
            $imageName = time() . '-' . uniqid() . '.' . $request->image->extension();
            $request->image->move(public_path('uploads/career_roadmap_sub_modules'), $imageName);
            $data['image'] = 'uploads/career_roadmap_sub_modules/' . $imageName;
        }

        $careerRoadmapSubModule->update($data);

        return redirect()->route('admin.career-roadmap-sub-modules.index')->with('success', 'Sub-module updated successfully.');
    }

    public function destroy(CareerRoadmapSubModule $careerRoadmapSubModule)
    {
        if ($careerRoadmapSubModule->image && file_exists(public_path($careerRoadmapSubModule->image))) {
            unlink(public_path($careerRoadmapSubModule->image));
        }
        $careerRoadmapSubModule->delete();
        return redirect()->route('admin.career-roadmap-sub-modules.index')->with('success', 'Sub-module deleted successfully.');
    }
}

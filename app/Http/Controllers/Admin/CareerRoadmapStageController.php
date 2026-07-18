<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\CareerRoadmapStage;
use App\Models\CareerRoadmapCategory;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Storage;

class CareerRoadmapStageController extends Controller
{
    public function index(Request $request)
    {
        $query = CareerRoadmapStage::with('category')->latest();
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where('title', 'like', "%{$search}%")
                  ->orWhereHas('category', function($q) use ($search) {
                      $q->where('name', 'like', "%{$search}%");
                  });
        }
        $stages = $query->paginate(15)->appends($request->all());
        return view('admin.career_roadmap_stages.index', compact('stages'));
    }

    public function create()
    {
        $categories = CareerRoadmapCategory::where('status', 1)->get();
        return view('admin.career_roadmap_stages.create', compact('categories'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'category_id' => 'required|exists:career_roadmap_categories,id',
            'title' => 'required|string|max:255',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg,webp|max:2048',
            'description' => 'nullable|string',
            'status' => 'nullable|boolean'
        ]);

        $data = [
            'category_id' => $request->category_id,
            'title' => $request->title,
            'description' => $request->description,
            'status' => $request->has('status') ? 1 : 0,
        ];

        if ($request->hasFile('image')) {
            $imageName = time() . '-' . uniqid() . '.' . $request->image->extension();
            $request->image->move(public_path('uploads/career_roadmap_stages'), $imageName);
            $data['image'] = 'uploads/career_roadmap_stages/' . $imageName;
        }

        CareerRoadmapStage::create($data);

        return redirect()->route('admin.career-roadmap-stages.index')->with('success', 'Stage created successfully.');
    }

    public function edit(CareerRoadmapStage $careerRoadmapStage)
    {
        $categories = CareerRoadmapCategory::where('status', 1)->get();
        return view('admin.career_roadmap_stages.edit', compact('careerRoadmapStage', 'categories'));
    }

    public function update(Request $request, CareerRoadmapStage $careerRoadmapStage)
    {
        $request->validate([
            'category_id' => 'required|exists:career_roadmap_categories,id',
            'title' => 'required|string|max:255',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg,webp|max:2048',
            'description' => 'nullable|string',
            'status' => 'nullable|boolean'
        ]);

        $data = [
            'category_id' => $request->category_id,
            'title' => $request->title,
            'description' => $request->description,
            'status' => $request->has('status') ? 1 : 0,
        ];

        if ($request->hasFile('image')) {
            // Delete old image if exists
            if ($careerRoadmapStage->image && file_exists(public_path($careerRoadmapStage->image))) {
                unlink(public_path($careerRoadmapStage->image));
            }
            $imageName = time() . '-' . uniqid() . '.' . $request->image->extension();
            $request->image->move(public_path('uploads/career_roadmap_stages'), $imageName);
            $data['image'] = 'uploads/career_roadmap_stages/' . $imageName;
        }

        $careerRoadmapStage->update($data);

        return redirect()->route('admin.career-roadmap-stages.index')->with('success', 'Stage updated successfully.');
    }

    public function destroy(CareerRoadmapStage $careerRoadmapStage)
    {
        if ($careerRoadmapStage->image && file_exists(public_path($careerRoadmapStage->image))) {
            unlink(public_path($careerRoadmapStage->image));
        }
        $careerRoadmapStage->delete();
        return redirect()->route('admin.career-roadmap-stages.index')->with('success', 'Stage deleted successfully.');
    }
}

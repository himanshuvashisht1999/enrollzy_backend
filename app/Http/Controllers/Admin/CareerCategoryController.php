<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Career;
use App\Models\CareerCategory;
use Illuminate\Http\Request;

/** Career categories: one list with an add row and an edit row per category. */
class CareerCategoryController extends Controller
{
    public function index()
    {
        $categories = CareerCategory::withCount('careers')->orderBy('sort_order')->orderBy('name')->get();
        return view('admin.career_categories.index', compact('categories'));
    }

    public function store(Request $request)
    {
        $c = CareerCategory::create($this->payload($request));
        return redirect()->route('admin.career-categories.index')->with('success', $c->name . ' added.');
    }

    public function update(Request $request, CareerCategory $careerCategory)
    {
        $careerCategory->update($this->payload($request, $careerCategory));
        return redirect()->route('admin.career-categories.index')->with('success', $careerCategory->name . ' saved.');
    }

    public function destroy(CareerCategory $careerCategory)
    {
        // its careers stay, without a category
        Career::where('category_id', $careerCategory->id)->update(['category_id' => null]);
        $careerCategory->delete();
        return redirect()->route('admin.career-categories.index')->with('success', 'Deleted. Its careers now have no category.');
    }

    private function payload(Request $request, ?CareerCategory $c = null): array
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'slug' => 'nullable|string|max:255|regex:/^[a-z0-9]+(?:-[a-z0-9]+)*$/|unique:career_categories,slug' . ($c ? ',' . $c->id : ''),
            'icon' => 'nullable|in:' . implode(',', array_keys(Career::ICONS)),
            'description' => 'nullable|string|max:300',
            'sort_order' => 'nullable|integer',
        ], ['slug.regex' => 'The web address may only have small letters, numbers and single hyphens.']);
        return [
            'name' => trim($request->name),
            'slug' => $request->slug ?: ($c->slug ?? null),
            'icon' => $request->icon,
            'description' => $request->description,
            'sort_order' => (int) $request->input('sort_order', 0),
            'status' => $request->boolean('status'),
        ];
    }
}

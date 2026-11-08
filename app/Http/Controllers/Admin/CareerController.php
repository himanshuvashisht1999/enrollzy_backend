<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Career;
use App\Models\CareerCategory;
use App\Models\CareerRoadmapStage;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

/**
 * Careers (doctor, chartered accountant, engineer…): the website's career pages, the "popular careers" of each class
 * and the careers a stream opens.
 */
class CareerController extends Controller
{
    public function index(Request $request)
    {
        $q = trim((string) $request->query('search', ''));
        $cat = (int) $request->query('category');
        $careers = Career::with('category')->when($q !== '', fn ($x) => $x->where('name', 'like', "%$q%"))->when($cat, fn ($x) => $x->where('category_id', $cat))
            ->orderBy('sort_order')->orderBy('name')->get();
        $categories = CareerCategory::orderBy('sort_order')->orderBy('name')->get(['id', 'name']);
        $classes = CareerRoadmapStage::orderBy('sort_order')->orderBy('id')->pluck('title', 'id');
        return view('admin.careers.index', compact('careers', 'classes', 'q', 'categories', 'cat'));
    }

    public function create()
    {
        return view('admin.careers.form', $this->formData(new Career(['status' => true, 'sort_order' => (int) Career::max('sort_order') + 10])));
    }

    public function store(Request $request)
    {
        $career = Career::create($this->payload($request));
        return redirect()->route('admin.careers.index')->with('success', $career->name . ' added.');
    }

    public function edit(Career $career)
    {
        return view('admin.careers.form', $this->formData($career));
    }

    public function update(Request $request, Career $career)
    {
        $career->update($this->payload($request, $career));
        return redirect()->route('admin.careers.index')->with('success', $career->name . ' saved.');
    }

    public function destroy(Career $career)
    {
        $career->delete();
        return redirect()->route('admin.careers.index')->with('success', 'Deleted.');
    }

    private function formData(Career $career): array
    {
        $classes = CareerRoadmapStage::orderBy('sort_order')->orderBy('id')->get(['id', 'title']);
        $courses = DB::table('courses')->where('status', 1)->orderBy('name')->get(['id', 'name']);
        // exam names: the Exam module's, plus the ones careers already use (typed in)
        $examNames = DB::table('exm_exams')->whereNull('deleted_at')->where('is_archived', 0)->orderBy('name')->get(['name', 'short_name'])
            ->map(fn ($e) => $e->short_name ?: $e->name)
            ->merge(Career::pluck('exams')->flatten())->filter()->unique()->sort(SORT_NATURAL | SORT_FLAG_CASE)->values();
        $categories = CareerCategory::orderBy('sort_order')->orderBy('name')->get(['id', 'name']);
        return compact('career', 'classes', 'courses', 'examNames', 'categories');
    }

    private function payload(Request $request, ?Career $career = null): array
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'slug' => 'nullable|string|max:255|regex:/^[a-z0-9]+(?:-[a-z0-9]+)*$/|unique:careers,slug' . ($career ? ',' . $career->id : ''),
            'icon' => 'nullable|in:' . implode(',', array_keys(Career::ICONS)),
            'category_id' => 'nullable|exists:career_categories,id',
            'summary' => 'nullable|string|max:300',
            'about' => 'nullable|string|max:5000',
            'stream' => 'nullable|string|max:255',
            'route' => 'nullable|string|max:3000',
            'exams' => 'nullable|array',
            'course_ids' => 'nullable|array',
            'salary' => 'nullable|string|max:255',
            'recruiters' => 'nullable|string|max:2000',
            'faqs' => 'nullable|array', 'faqs.*.q' => 'nullable|string|max:300', 'faqs.*.a' => 'nullable|string|max:3000',
            'show_for' => 'nullable|array',
            'sort_order' => 'nullable|integer',
            'meta_title' => 'nullable|string|max:255',
            'meta_description' => 'nullable|string|max:500',
        ], ['slug.regex' => 'The web address may only have small letters, numbers and single hyphens.']);
        $lines = fn ($t) => array_values(array_filter(array_map('trim', preg_split('/\r?\n/', (string) $t))));
        return [
            'name' => trim($request->name),
            'slug' => $request->slug ?: ($career->slug ?? null),   // empty: keep the address (a new career's is made from its name)
            'icon' => $request->icon,
            'category_id' => $request->category_id ?: null,
            'summary' => $request->summary,
            'about' => $request->about,
            'stream' => $request->stream,
            'route' => $lines($request->route) ?: null,
            'exams' => array_values(array_filter(array_map('trim', (array) $request->input('exams', [])))) ?: null,
            'course_ids' => array_values(array_map('intval', (array) $request->input('course_ids', []))) ?: null,
            'salary' => $request->salary,
            'recruiters' => $request->recruiters,
            // its own questions, in order (empty rows dropped)
            'faqs' => collect($request->input('faqs', []))->map(fn ($f) => ['q' => trim((string) ($f['q'] ?? '')), 'a' => trim((string) ($f['a'] ?? ''))])
                ->filter(fn ($f) => $f['q'] !== '' && $f['a'] !== '')->values()->all() ?: null,
            'show_for' => array_values(array_map('intval', (array) $request->input('show_for', []))) ?: null,
            'sort_order' => (int) $request->input('sort_order', 0),
            'status' => $request->boolean('status'),
            'meta_title' => $request->meta_title,
            'meta_description' => $request->meta_description,
        ];
    }
}

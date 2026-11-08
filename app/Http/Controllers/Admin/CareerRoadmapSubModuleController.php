<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Career;
use App\Models\CareerRoadmapStage;
use App\Models\CareerRoadmapSubModule;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

/**
 * Career roadmaps and their items. A top-level module of a class is a roadmap (with a type: stream, career paths,
 * exams…); everything under it is an item (step, course, exam, scholarship, career…) with its facts in their own
 * fields and links to the Exam, Course and Scholarship modules and to Careers. The list is a tree per class, in the
 * order the website shows.
 */
class CareerRoadmapSubModuleController extends Controller
{
    /** Custom-field keys the form edits itself (the rest are "Other details"). */
    private const RESERVED = ['alert_message', 'btn1_label', 'btn1_url', 'btn2_label', 'btn2_url', 'Badge', 'badge', 'Salary', 'salary'];

    public function index(Request $request)
    {
        $classes = CareerRoadmapStage::with('category')->orderBy('sort_order')->orderBy('id')->get();
        $search = trim((string) $request->query('search', ''));

        if ($search !== '') {
            // a flat list of matches, each with its path
            $all = CareerRoadmapSubModule::orderBy('id')->get(['id', 'stage_id', 'parent_id', 'title']);
            $byId = $all->keyBy('id');
            $results = CareerRoadmapSubModule::with('stage')->where('title', 'like', '%' . $search . '%')->orderBy('stage_id')->orderBy('id')->limit(200)->get()
                ->map(function ($r) use ($byId) {
                    $path = []; $p = $r->parent_id;
                    while ($p && isset($byId[$p])) { array_unshift($path, $byId[$p]->title); $p = $byId[$p]->parent_id; }
                    $r->path = implode(' › ', $path);
                    return $r;
                });
            return view('admin.career_roadmap_sub_modules.index', compact('classes', 'search', 'results') + ['class' => null, 'roadmaps' => collect(), 'tree' => []]);
        }

        $class = $classes->firstWhere('id', (int) $request->query('class')) ?? $classes->first();
        $rows = $class ? CareerRoadmapSubModule::where('stage_id', $class->id)->orderBy('sort_order')->orderBy('id')
            ->get(['id', 'parent_id', 'title', 'roadmap_type', 'item_type', 'url_slug', 'status', 'sort_order', 'exam_id', 'course_id', 'scholarship_id', 'career_id']) : collect();
        $tree = $rows->groupBy(fn ($r) => (int) $r->parent_id);
        $roadmaps = $tree[0] ?? collect();
        return view('admin.career_roadmap_sub_modules.index', compact('classes', 'class', 'roadmaps', 'tree', 'search') + ['results' => collect()]);
    }

    public function create(Request $request)
    {
        $item = new CareerRoadmapSubModule([
            'stage_id' => (int) $request->query('stage_id') ?: null,
            'parent_id' => (int) $request->query('parent_id') ?: null,
            'status' => 1,
        ]);
        if ($item->parent_id && ($p = CareerRoadmapSubModule::find($item->parent_id))) {
            $item->stage_id = $p->stage_id;
            $item->sort_order = (int) CareerRoadmapSubModule::where('parent_id', $p->id)->max('sort_order') + 10;
        } elseif ($item->stage_id) {
            $item->sort_order = (int) CareerRoadmapSubModule::where('stage_id', $item->stage_id)->whereNull('parent_id')->max('sort_order') + 10;
        }
        return view('admin.career_roadmap_sub_modules.create', $this->formData($item));
    }

    public function store(Request $request)
    {
        $data = $this->payload($request);
        $item = CareerRoadmapSubModule::create($data);
        return redirect()->to(route('admin.career-roadmap-sub-modules.index', ['class' => $item->stage_id]) . '#item-' . $item->id)
            ->with('success', ($item->parent_id ? 'Item' : 'Roadmap') . ' created.');
    }

    public function edit(CareerRoadmapSubModule $careerRoadmapSubModule)
    {
        return view('admin.career_roadmap_sub_modules.edit', $this->formData($careerRoadmapSubModule));
    }

    public function update(Request $request, CareerRoadmapSubModule $careerRoadmapSubModule)
    {
        $data = $this->payload($request, $careerRoadmapSubModule);
        $stageChanged = (int) $data['stage_id'] !== (int) $careerRoadmapSubModule->stage_id;
        $careerRoadmapSubModule->update($data);
        // a roadmap moved to another class takes its items with it
        if ($stageChanged) {
            $ids = [$careerRoadmapSubModule->id]; $front = $ids;
            while ($front) { $front = CareerRoadmapSubModule::whereIn('parent_id', $front)->pluck('id')->all(); $ids = array_merge($ids, $front); }
            CareerRoadmapSubModule::whereIn('id', $ids)->update(['stage_id' => $data['stage_id']]);
        }
        return redirect()->to(route('admin.career-roadmap-sub-modules.index', ['class' => $careerRoadmapSubModule->stage_id]) . '#item-' . $careerRoadmapSubModule->id)
            ->with('success', 'Saved.');
    }

    /** The order of a list (roadmaps of a class, or items of a parent): sort[id] = position. */
    public function reorder(Request $request)
    {
        $request->validate(['sort' => 'required|array', 'sort.*' => 'integer']);
        DB::transaction(function () use ($request) {
            foreach ($request->input('sort') as $id => $pos) CareerRoadmapSubModule::where('id', (int) $id)->update(['sort_order' => (int) $pos]);
        });
        CareerRoadmapSubModule::query()->latest('id')->first()?->touch();   // the website's cache follows the latest change
        return $request->expectsJson() ? response()->json(['ok' => true]) : back()->with('success', 'Order saved.');
    }

    public function destroy(CareerRoadmapSubModule $careerRoadmapSubModule)
    {
        if ($careerRoadmapSubModule->image && file_exists(public_path($careerRoadmapSubModule->image))) {
            unlink(public_path($careerRoadmapSubModule->image));
        }
        $class = $careerRoadmapSubModule->stage_id;
        $careerRoadmapSubModule->delete();
        return redirect()->route('admin.career-roadmap-sub-modules.index', ['class' => $class])->with('success', 'Deleted.');
    }

    // ------------------------------------------------------------------ the form

    private function formData(CareerRoadmapSubModule $item): array
    {
        $classes = CareerRoadmapStage::with('category')->where('status', 1)->orderBy('sort_order')->orderBy('id')->get();
        // possible parents: every roadmap and item, with its path ("Commerce › Finance"), tagged with its class
        $all = CareerRoadmapSubModule::orderBy('sort_order')->orderBy('id')->get(['id', 'stage_id', 'parent_id', 'title']);
        $byId = $all->keyBy('id');
        $blocked = [];
        if ($item->exists) {   // not itself or its own items
            $blocked = [$item->id]; $front = [$item->id];
            while ($front) { $front = $all->whereIn('parent_id', $front)->pluck('id')->all(); $blocked = array_merge($blocked, $front); }
        }
        $parents = $all->reject(fn ($r) => in_array($r->id, $blocked, true))->map(function ($r) use ($byId) {
            $path = [$r->title]; $p = $r->parent_id; $depth = 0;
            while ($p && isset($byId[$p]) && $depth++ < 6) { array_unshift($path, $byId[$p]->title); $p = $byId[$p]->parent_id; }
            return ['id' => $r->id, 'stage_id' => $r->stage_id, 'label' => implode(' › ', $path)];
        })->sortBy('label', SORT_NATURAL | SORT_FLAG_CASE)->values();

        $exams = DB::table('exm_exams')->whereNull('deleted_at')->where('is_archived', 0)->orderBy('name')->get(['id', 'name', 'short_name']);
        $courses = DB::table('courses')->where('status', 1)->orderBy('name')->get(['id', 'name']);
        $scholarships = DB::table('scholarships')->whereNull('deleted_at')->orderBy('title')->get(['id', 'title']);
        $careers = Career::orderBy('sort_order')->orderBy('name')->get(['id', 'name']);
        $cf = is_array($item->custom_fields) ? $item->custom_fields : (json_decode((string) $item->custom_fields, true) ?: []);
        $other = collect($cf)->except(self::RESERVED)->all();
        return compact('item', 'classes', 'parents', 'exams', 'courses', 'scholarships', 'careers', 'cf', 'other');
    }

    private function payload(Request $request, ?CareerRoadmapSubModule $item = null): array
    {
        $request->validate([
            'stage_id' => 'required|exists:career_roadmap_stages,id',
            'parent_id' => 'nullable|exists:career_roadmap_sub_modules,id' . ($item ? '|not_in:' . $item->id : ''),
            'title' => 'required|string|max:255',
            'sort_order' => 'nullable|integer',
            'roadmap_type' => 'nullable|in:' . implode(',', array_keys(CareerRoadmapSubModule::ROADMAP_TYPES)),
            'item_type' => 'nullable|in:' . implode(',', array_keys(CareerRoadmapSubModule::ITEM_TYPES)),
            'url_slug' => 'nullable|string|max:120|regex:/^[a-z0-9]+(?:-[a-z0-9]+)*$/',
            'summary' => 'nullable|string|max:300',
            'description' => 'nullable|string',
            'long_description' => 'nullable|string',
            'eligibility' => 'nullable|string|max:2000',
            'conducted_by' => 'nullable|string|max:190',
            'when_text' => 'nullable|string|max:190',
            'duration' => 'nullable|string|max:100',
            'fee' => 'nullable|string|max:255',
            'salary' => 'nullable|string|max:255',
            'official_url' => 'nullable|string|max:500',
            'exam_id' => 'nullable|integer',
            'course_id' => 'nullable|integer',
            'scholarship_id' => 'nullable|integer',
            'career_id' => 'nullable|integer',
            'subjects' => 'nullable|string|max:2000',
            'who_should' => 'nullable|string|max:2000',
            'closes' => 'nullable|string|max:2000',
            'career_ids' => 'nullable|array',
            'career_ids.*' => 'integer',
            'meta_title' => 'nullable|string|max:255',
            'meta_description' => 'nullable|string|max:500',
            'alert_message' => 'nullable|string|max:500',
            'custom_field_names' => 'nullable|array',
            'custom_field_values' => 'nullable|array',
        ], ['url_slug.regex' => 'The web address may only have small letters, numbers and single hyphens (e.g. science-medical).']);

        $top = !$request->filled('parent_id');
        // the web address must be free in the class
        if ($top && $request->filled('url_slug')) {
            $taken = CareerRoadmapSubModule::whereNull('parent_id')->where('stage_id', $request->stage_id)->where('url_slug', $request->url_slug)
                ->when($item, fn ($q) => $q->where('id', '!=', $item->id))->exists();
            if ($taken) throw ValidationException::withMessages(['url_slug' => 'Another roadmap of this class already uses this web address.']);
        }

        // other details (free key / value pairs), plus the intro line and the two buttons
        $cf = [];
        foreach ((array) $request->input('custom_field_names', []) as $i => $name) {
            $name = trim((string) $name);
            $value = $request->input("custom_field_values.$i");
            if ($name !== '' && !in_array($name, self::RESERVED, true) && filled($value)) $cf[$name] = $value;
        }
        if ($top && $request->filled('alert_message')) $cf['alert_message'] = $request->alert_message;
        foreach ([1, 2] as $b) {
            if ($request->filled("btn{$b}_label")) { $cf["btn{$b}_label"] = $request->input("btn{$b}_label"); $cf["btn{$b}_url"] = $request->input("btn{$b}_url"); }
        }

        $type = $top ? null : $request->item_type;
        $data = [
            'stage_id' => (int) $request->stage_id,
            'parent_id' => $request->parent_id ?: null,
            'title' => trim($request->title),
            'sort_order' => (int) $request->input('sort_order', 0),
            'roadmap_type' => $top ? $request->roadmap_type : null,
            'item_type' => $type,
            'url_slug' => $top ? ($request->url_slug ?: ($item->url_slug ?? null) ?: Str::slug($request->title)) : null,
            'summary' => $request->summary,
            'description' => $request->description,
            'long_description' => $request->long_description,
            'custom_fields' => $cf ?: null,
            'status' => $request->boolean('status') ? 1 : 0,
            'meta_title' => $top ? $request->meta_title : null,
            'meta_description' => $top ? $request->meta_description : null,
        ];
        foreach (['eligibility', 'conducted_by', 'when_text', 'duration', 'fee', 'salary', 'official_url'] as $f) $data[$f] = $top ? null : $request->input($f);
        // a link only for the item type that has it
        $data['exam_id'] = !$top && $type === 'exam' ? ($request->exam_id ?: null) : null;
        $data['course_id'] = !$top && $type === 'course' ? ($request->course_id ?: null) : null;
        $data['scholarship_id'] = !$top && $type === 'scholarship' ? ($request->scholarship_id ?: null) : null;
        $data['career_id'] = !$top && $type === 'career' ? ($request->career_id ?: null) : null;
        // stream details only on a stream roadmap
        $stream = $top && $request->roadmap_type === 'stream';
        foreach (['subjects', 'who_should', 'closes'] as $f) $data[$f] = $stream ? $request->input($f) : null;
        $data['career_ids'] = $stream ? array_values(array_map('intval', (array) $request->input('career_ids', []))) : null;
        return $data;
    }
}

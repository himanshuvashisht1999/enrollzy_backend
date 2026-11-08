<?php

namespace App\Http\Controllers\Admin\ExamModule;

use App\Http\Controllers\Controller;
use App\Models\ExamModule\Cycle;
use App\Models\ExamModule\CustomField;
use App\Models\ExamModule\CustomValue;
use App\Models\ExamModule\Exam;
use App\Support\CustomFields\FieldValues;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

/** Schema Builder: define extra fields for one exam, and save their values per cycle. */
class CustomFieldController extends Controller
{
    use FieldValues;

    public function index(Request $request, Exam $exam)
    {
        $fields = $exam->customFields()->withCount('values')->get();
        // Opened from a cycle page: Back returns to that cycle and its tabs.
        $cycle = $request->filled('cycle') ? Cycle::where('exam_id', $exam->id)->find($request->query('cycle')) : null;
        return view('admin.exam-module.fields.index', compact('exam', 'fields', 'cycle'));
    }

    public function store(Request $request, Exam $exam)
    {
        $data = $request->validate([
            'label' => 'required|string|max:120',
            'key' => ['nullable', 'regex:/^[a-z][a-z0-9_]{1,59}$/'],
            'type' => ['required', Rule::in(array_keys(CustomField::TYPES))],
            'scope' => ['required', Rule::in(['exam', 'cycle'])],
            'options' => 'nullable|string|max:3000',
        ]);

        $key = ($data['key'] ?? null) ?: Str::snake(Str::ascii($data['label']));
        $key = preg_replace('/[^a-z0-9_]+/', '_', $key);
        if (!preg_match('/^[a-z][a-z0-9_]{1,59}$/', $key)) {
            return back()->withInput()->withErrors(['label' => 'Could not make a valid key from this label. Enter a key (a-z, 0-9, _).']);
        }
        if ($word = CustomField::reservedWord($key)) {
            return back()->withInput()->withErrors(['key' => "\"$word\" belongs to a structured field (eligibility, fees, dates, pattern, syllabus). Use the cycle's own tabs for that, not the Schema Builder."]);
        }
        if (CustomField::withTrashed()->where('exam_id', $exam->id)->where('key', $key)->exists()) {
            return back()->withInput()->withErrors(['key' => 'This key is already used by this exam (keys are never reused, even after deletion).']);
        }

        $options = $this->options($data['type'], $data['options'] ?? null);
        if (in_array($data['type'], ['dropdown', 'multi_select', 'group']) && !$options) {
            return back()->withInput()->withErrors(['options' => 'Add at least one option (one per line).']);
        }

        $exam->customFields()->create([
            'key' => $key, 'label' => $data['label'], 'type' => $data['type'], 'scope' => $data['scope'],
            'options' => $options, 'sort_order' => ($exam->customFields()->max('sort_order') ?? 0) + 1,
        ]);
        return back()->with('success', 'Field added.');
    }

    /** Key, type and scope are locked: only the label, options and order can change. */
    public function update(Request $request, CustomField $field)
    {
        $data = $request->validate([
            'label' => 'required|string|max:120',
            'options' => 'nullable|string|max:3000',
            'sort_order' => 'nullable|integer|min:0',
        ]);
        $field->update([
            'label' => $data['label'],
            'options' => $this->options($field->type, $data['options'] ?? null) ?? $field->options,
            'sort_order' => $data['sort_order'] ?? $field->sort_order,
        ]);
        return back()->with('success', 'Field updated.');
    }

    public function destroy(Request $request, CustomField $field)
    {
        if ($field->values()->exists() && !$request->boolean('confirm')) {
            return back()->with('error', 'This field has saved data. Confirm the deletion to continue.');
        }
        $field->delete();   // soft delete: values are kept
        return back()->with('success', 'Field deleted. Its saved values are kept.');
    }

    /**
     * Save custom field values for a cycle (exam-scope values are shared by all cycles).
     * Each tab posts fields[] with its own field, so saving one tab never clears the others.
     */
    public function saveValues(Request $request, Cycle $cycle)
    {
        $fields = CustomField::where('exam_id', $cycle->exam_id)
            ->when($request->filled('fields'), fn ($q) => $q->whereIn('id', (array) $request->input('fields')))
            ->get();
        $rules = [];
        foreach ($fields as $f) {
            $rules += $this->valueRules($f);
        }
        $request->validate($rules, [], $this->valueNames($fields));

        foreach ($fields as $f) {
            $cycleId = $f->scope === 'cycle' ? $cycle->id : null;
            $existing = CustomValue::where('field_id', $f->id)->where('cycle_id', $cycleId)->first();
            $value = $this->value($request, $f, $existing?->value, 'exm/custom');

            if ($value === null || $value === [] || $value === '') {
                $existing?->delete();
                continue;
            }
            $existing ? $existing->update(['value' => $value]) : CustomValue::create(['field_id' => $f->id, 'cycle_id' => $cycleId, 'value' => $value]);
        }
        $tab = $fields->count() === 1 ? 'cf-' . $fields->first()->id : 'eligibility';
        return back()->with('success', ($fields->count() === 1 ? $fields->first()->label : 'Extra details') . ' saved.')->with('content_tab', $tab);
    }
}

<?php

namespace App\Http\Controllers\Admin\ExamModule;

use App\Http\Controllers\Controller;
use App\Models\ExamModule\Cycle;
use App\Models\ExamModule\CustomField;
use App\Models\ExamModule\CustomValue;
use App\Models\ExamModule\Exam;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

/** Schema Builder: define extra fields for one exam, and save their values per cycle. */
class CustomFieldController extends Controller
{
    public function index(Exam $exam)
    {
        $fields = $exam->customFields()->withCount('values')->get();
        return view('admin.exam-module.fields.index', compact('exam', 'fields'));
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

    /** Save every custom field value for a cycle (exam-scope values are shared by all cycles). */
    public function saveValues(Request $request, Cycle $cycle)
    {
        $fields = CustomField::where('exam_id', $cycle->exam_id)->get();
        $rules = [];
        foreach ($fields as $f) {
            $rules += $this->valueRules($f);
        }
        $request->validate($rules);

        foreach ($fields as $f) {
            $cycleId = $f->scope === 'cycle' ? $cycle->id : null;
            $existing = CustomValue::where('field_id', $f->id)->where('cycle_id', $cycleId)->first();
            $value = $this->value($request, $f, $existing?->value);

            if ($value === null || $value === [] || $value === '') {
                $existing?->delete();
                continue;
            }
            $existing ? $existing->update(['value' => $value]) : CustomValue::create(['field_id' => $f->id, 'cycle_id' => $cycleId, 'value' => $value]);
        }
        return back()->with('success', 'Extra details saved.')->with('content_tab', 'custom');
    }

    private function valueRules(CustomField $f): array
    {
        $k = "cf.{$f->id}";
        return match ($f->type) {
            'text' => [$k => 'nullable|string|max:500'],
            'rich_text' => [$k => 'nullable|string|max:20000'],
            'number' => [$k => 'nullable|numeric'],
            'date' => [$k => 'nullable|date'],
            'url' => [$k => 'nullable|url|max:500'],
            'dropdown' => [$k => ['nullable', Rule::in($f->options ?? [])]],
            'multi_select' => [$k => 'nullable|array', "$k.*" => [Rule::in($f->options ?? [])]],
            'file' => [$k => 'nullable|file|mimes:pdf,jpg,jpeg,png,doc,docx|max:10240'],
            'group' => [$k => 'nullable|array|max:50', "$k.*.*" => 'nullable|string|max:500'],
            default => [],
        };
    }

    private function value(Request $request, CustomField $f, $current)
    {
        $k = "cf.{$f->id}";
        switch ($f->type) {
            case 'file':
                if ($request->hasFile($k)) {
                    $path = $request->file($k)->store('exm/custom', 'public');
                    if (is_string($current)) Storage::disk('public')->delete($current);
                    return $path;
                }
                return $current;
            case 'number':
                $v = $request->input($k);
                return ($v === null || $v === '') ? null : $v + 0;
            case 'multi_select':
                return array_values($request->input($k, []));
            case 'group':
                $rows = [];
                foreach ($request->input($k, []) as $row) {
                    $clean = [];
                    foreach (self::groupKeys($f) as $key) {
                        $clean[$key] = filled($row[$key] ?? null) ? $row[$key] : null;
                    }
                    if (array_filter($clean)) $rows[] = $clean;
                }
                return $rows;
            default:
                $v = $request->input($k);
                return ($v === null || $v === '') ? null : $v;
        }
    }

    /** Group sub-field keys, derived from the option labels. */
    public static function groupKeys(CustomField $f): array
    {
        return array_map(fn ($l) => Str::snake(Str::ascii($l)), $f->options ?? []);
    }

    private function options(string $type, ?string $text): ?array
    {
        if (!in_array($type, ['dropdown', 'multi_select', 'group'])) {
            return null;
        }
        $items = array_values(array_unique(array_filter(array_map('trim', preg_split('/\R/', (string) $text)))));
        return $items ?: null;
    }
}

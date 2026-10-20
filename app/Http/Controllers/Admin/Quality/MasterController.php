<?php

namespace App\Http\Controllers\Admin\Quality;

use App\Http\Controllers\Controller;
use App\Support\Quality\Masters;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

/** Generic CRUD for the ranking / accreditation masters, driven by App\Support\Quality\Masters. */
class MasterController extends Controller
{
    public function index(string $master)
    {
        $def = Masters::get($master) ?? abort(404);
        $rows = $def['model']::query()->orderBy('sort_order')->orderBy('name')->get();
        if ($master === 'ranking-categories') $rows->load('body');
        $inUse = $rows->mapWithKeys(fn ($r) => [$r->id => ($def['in_use'])($r)]);
        return view('admin.quality.masters', compact('master', 'def', 'rows', 'inUse'));
    }

    public function store(Request $request, string $master)
    {
        $def = Masters::get($master) ?? abort(404);
        $def['model']::create($this->validated($request, $def));
        return back()->with('success', $def['singular'] . ' added.');
    }

    public function update(Request $request, string $master, int $id)
    {
        $def = Masters::get($master) ?? abort(404);
        $row = $def['model']::findOrFail($id);
        $row->update($this->validated($request, $def, $row));
        return back()->with('success', $def['singular'] . ' updated.');
    }

    public function destroy(string $master, int $id)
    {
        $def = Masters::get($master) ?? abort(404);
        $row = $def['model']::findOrFail($id);
        if (($def['in_use'])($row)) {
            return back()->with('error', 'This ' . strtolower($def['singular']) . ' is in use. Mark it inactive instead.');
        }
        $row->delete();
        return back()->with('success', $def['singular'] . ' deleted.');
    }

    private function validated(Request $request, array $def, $row = null): array
    {
        $table = (new $def['model'])->getTable();
        $rules = [];
        foreach ($def['fields'] as $f) {
            $n = $f['name'];
            $req = !empty($f['required']) ? 'required' : 'nullable';
            $rules[$n] = match ($f['type']) {
                'text' => array_filter([$req, 'string', 'max:255', in_array($n, $def['unique'] ?? [], true) ? Rule::unique($table, $n)->ignore($row?->id) : null]),
                'textarea' => ['nullable', 'string', 'max:2000'],
                'csv' => ['nullable', 'string', 'max:500'],
                'number' => ['nullable', 'integer', 'min:0'],
                'select' => [$req, Rule::in(array_keys(Masters::options($f)))],
                'multiselect' => ['nullable', 'array'],
                default => ['nullable'],
            };
            if ($f['type'] === 'multiselect') $rules[$n . '.*'] = [Rule::in(array_keys(Masters::options($f)))];
        }
        // A category name is unique within its ranking body, not globally.
        if ($table === 'ranking_categories') {
            $rules['name'][] = Rule::unique($table, 'name')->where('ranking_body_id', $request->input('ranking_body_id'))->ignore($row?->id);
        }
        $data = $request->validate($rules);

        foreach ($def['fields'] as $f) {
            $n = $f['name'];
            $data[$n] = match ($f['type']) {
                'bool' => $request->boolean($n),
                'number' => (int) ($data[$n] ?? 0),
                'multiselect' => array_values($data[$n] ?? []) ?: null,
                'csv' => array_values(array_filter(array_map('trim', explode(',', (string) ($data[$n] ?? ''))), 'strlen')) ?: null,
                default => ($data[$n] ?? null) === '' ? null : ($data[$n] ?? null),
            };
        }
        return $data;
    }
}

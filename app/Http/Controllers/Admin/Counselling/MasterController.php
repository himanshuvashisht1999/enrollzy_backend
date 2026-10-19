<?php

namespace App\Http\Controllers\Admin\Counselling;

use App\Http\Controllers\Controller;
use App\Support\Counselling\Masters;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

/** Generic CRUD for the small counselling masters, driven by App\Support\Counselling\Masters. */
class MasterController extends Controller
{
    public function index(string $master)
    {
        $def = Masters::get($master) ?? abort(404);
        $table = (new $def['model'])->getTable();
        $rows = $def['model']::query()
            ->when(\Illuminate\Support\Facades\Schema::hasColumn($table, 'sort_order'), fn ($q) => $q->orderBy('sort_order'))
            ->orderBy('name')->get();
        $inUse = $rows->mapWithKeys(fn ($r) => [$r->id => ($def['in_use'])($r)]);
        return view('admin.counselling.masters.index', compact('master', 'def', 'rows', 'inUse'));
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
            $req = !empty($f['required']) ? 'required' : 'nullable';
            $rules[$f['name']] = match ($f['type']) {
                'text' => [$req, 'string', 'max:255'] + ($f['name'] === 'name' ? [3 => Rule::unique($table, 'name')->ignore($row?->id)] : []),
                'textarea' => ['nullable', 'string', 'max:2000'],
                'number' => ['nullable', 'integer', 'min:0'],
                'select' => [$req, Rule::in(array_keys(Masters::options($f)))],
                'multiselect' => ['nullable', 'array'],
                default => ['nullable'],
            };
            if ($f['type'] === 'multiselect') {
                $rules[$f['name'] . '.*'] = [Rule::in(array_keys(Masters::options($f)))];
            }
        }
        $data = $request->validate($rules);

        foreach ($def['fields'] as $f) {
            $n = $f['name'];
            $data[$n] = match ($f['type']) {
                'bool' => $request->boolean($n),
                'number' => (int) ($data[$n] ?? 0),
                'multiselect' => array_values($data[$n] ?? []) ?: null,
                default => ($data[$n] ?? null) === '' ? null : ($data[$n] ?? null),
            };
        }
        return $data;
    }
}

<?php

namespace App\Http\Controllers\Admin\ExamModule;

use App\Http\Controllers\Controller;
use App\Models\ExamModule\ContentRow;
use App\Models\ExamModule\Cycle;
use App\Support\ExamModule\ContentBlocks;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

/** CRUD for every cycle content block (eligibility, pattern, fees, ...), driven by ContentBlocks. */
class ContentController extends Controller
{
    public function store(Request $request, Cycle $cycle, string $block)
    {
        $def = ContentBlocks::get($block) ?? abort(404);
        $data = $this->prepare($request, $def, $cycle);

        $row = ContentRow::block($block);
        $row->fill($data + ['cycle_id' => $cycle->id])->save();

        return back()->with('success', $def['label'] . ' added.')->with('content_tab', $block);
    }

    public function update(Request $request, string $block, int $id)
    {
        $def = ContentBlocks::get($block) ?? abort(404);
        $row = ContentRow::block($block)->newQuery()->findOrFail($id);
        $data = $this->prepare($request, $def, $row->cycle, $row);

        $old = [];
        foreach (ContentBlocks::fileFields($def) as $f) {
            if (array_key_exists($f, $data) && $row->$f && $row->$f !== $data[$f]) {
                $old[] = $row->$f;
            }
        }
        $row->fill($data)->save();
        foreach ($old as $path) {
            $this->deleteFileIfUnused($def['table'], $path);
        }

        return back()->with('success', $def['label'] . ' updated.')->with('content_tab', $block);
    }

    public function destroy(string $block, int $id)
    {
        $def = ContentBlocks::get($block) ?? abort(404);
        $row = ContentRow::block($block)->newQuery()->findOrFail($id);
        $files = array_filter(array_map(fn ($f) => $row->$f, ContentBlocks::fileFields($def)));
        $row->delete();
        foreach ($files as $path) {
            $this->deleteFileIfUnused($def['table'], $path);
        }

        return back()->with('success', $def['label'] . ' deleted.')->with('content_tab', $block);
    }

    private function prepare(Request $request, array $def, Cycle $cycle, ?ContentRow $existing = null): array
    {
        $rules = ContentBlocks::rules($def, (bool) $existing)
            + ['stage_id' => ['nullable', Rule::exists('exm_stages', 'id')->where('cycle_id', $cycle->id)]];

        $validator = Validator::make($request->all(), $rules);
        $validator->after(function ($v) use ($def, $request, $existing) {
            if ($oneOf = $def['require_one_of'] ?? null) {
                $has = collect($oneOf)->contains(fn ($f) => $request->hasFile($f) || filled($request->input($f)) || ($existing && filled($existing->$f)));
                if (!$has) {
                    $v->errors()->add($oneOf[0], 'Provide at least one of: ' . implode(', ', $oneOf) . '.');
                }
            }
        });
        $validated = $validator->validate();

        $data = ['stage_id' => $validated['stage_id'] ?? null];
        foreach ($def['fields'] as $f) {
            $name = $f['name'];
            $value = $validated[$name] ?? null;

            switch ($f['type']) {
                case 'bool':
                    $data[$name] = $request->boolean($name);
                    break;
                case 'list':
                    $data[$name] = $this->lines($value);
                    break;
                case 'rows':
                    $data[$name] = $this->rows($value, $f['sub']);
                    break;
                case 'file':
                    if ($request->hasFile($name)) {
                        $data[$name] = $request->file($name)->store('exm/content/' . $def['table'], 'public');
                    }
                    break;
                default:
                    $data[$name] = ($value === '' || $value === null) ? null : $value;
            }
        }
        return $data;
    }

    private function lines(?string $text): ?array
    {
        $items = array_values(array_unique(array_filter(array_map('trim', preg_split('/\R/', (string) $text)))));
        return $items ? array_slice($items, 0, 200) : null;
    }

    private function rows(?array $rows, array $sub): ?array
    {
        $out = [];
        foreach ($rows ?? [] as $row) {
            $clean = [];
            foreach ($sub as $s) {
                $v = $row[$s['name']] ?? null;
                // Numbers and id-keyed selects (e.g. category_id) are stored as numbers, not strings.
                $clean[$s['name']] = ($v === '' || $v === null) ? null
                    : ($s['type'] === 'number' || ($s['type'] === 'select' && ctype_digit((string) $v)) ? $v + 0 : $v);
            }
            if (array_filter($clean, fn ($v) => $v !== null)) {
                $out[] = $clean;
            }
        }
        return $out ?: null;
    }

    private function deleteFileIfUnused(string $table, string $path): void
    {
        $used = collect(['pdf', 'file'])->contains(
            fn ($col) => \Illuminate\Support\Facades\Schema::hasColumn($table, $col) && \Illuminate\Support\Facades\DB::table($table)->where($col, $path)->exists()
        );
        if (!$used) {
            Storage::disk('public')->delete($path);
        }
    }
}

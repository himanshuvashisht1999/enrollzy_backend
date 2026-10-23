<?php

namespace App\Http\Controllers\Admin\Counselling;

use App\Http\Controllers\Controller;
use App\Models\Counselling\CnsRow;
use App\Models\Counselling\CounsellingCycle;
use App\Models\Counselling\Round;
use App\Support\Counselling\CnsBlocks;
use App\Support\Counselling\Inheritance;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;

/** CRUD for eligibility, process, fees, documents and rules, at cycle level or round level. */
class BlockController extends Controller
{
    public function storeForCycle(Request $request, CounsellingCycle $cycle, string $block)
    {
        $def = $this->definition($block, $cycle);
        abort_unless(in_array('cycle', $def['scopes']), 404);
        $this->create($request, $def, $block, $cycle, null);

        return back()->with('success', $def['label'] . ' added.')->with('block_tab', $block);
    }

    public function storeForRound(Request $request, Round $round, string $block)
    {
        $def = $this->definition($block, $round->cycle);
        abort_unless(in_array('round', $def['scopes']), 404);
        $this->create($request, $def, $block, $round->cycle, $round);

        return back()->with('success', $def['label'] . ' added to ' . $round->name . '.')->with('block_tab', $block);
    }

    public function update(Request $request, string $block, int $id)
    {
        $row = CnsRow::block($block)->newQuery()->findOrFail($id);
        $def = $this->definition($block, $row->cycle);
        [$data, $children] = $this->prepare($request, $def, $block, $row->cycle, $row->round, $row);

        $oldFiles = [];
        foreach (CnsBlocks::fileFields($def) as $f) {
            if (array_key_exists($f, $data) && $row->$f && $row->$f !== $data[$f]) {
                $oldFiles[] = $row->$f;
            }
        }
        DB::transaction(function () use ($row, $data, $children, $def) {
            $row->fill($data)->save();
            $this->syncChildren($def, $row, $children);
        });
        foreach ($oldFiles as $path) {
            $this->deleteFileIfUnused($def['table'], $path);
        }

        return back()->with('success', $def['label'] . ' updated.')->with('block_tab', $block);
    }

    /** Deleting a round row is how a round "resets" to the cycle's value. */
    public function destroy(string $block, int $id)
    {
        $def = CnsBlocks::get($block) ?? abort(404);
        $row = CnsRow::block($block)->newQuery()->findOrFail($id);
        $files = array_filter(array_map(fn ($f) => $row->$f, CnsBlocks::fileFields($def)));
        $isRoundOverride = $row->round_id && ($def['inherit'] ?? 'add') === 'replace'
            && CnsRow::forScope($block, $row->cycle_id)->contains(fn ($c) => Inheritance::key($def['unique'], $c) === Inheritance::key($def['unique'], $row));
        $row->delete();
        foreach ($files as $path) {
            $this->deleteFileIfUnused($def['table'], $path);
        }

        return back()->with('success', $isRoundOverride ? 'Reset: this round now uses the cycle value.' : $def['label'] . ' deleted.')->with('block_tab', $block);
    }

    /** Copy a cycle row into the round so it can be edited there. */
    public function override(Round $round, string $block, int $id)
    {
        $def = CnsBlocks::get($block) ?? abort(404);
        abort_unless(($def['inherit'] ?? 'add') === 'replace' && in_array('round', $def['scopes']), 404);

        $source = CnsRow::block($block)->newQuery()->where('cycle_id', $round->cycle_id)->whereNull('round_id')->findOrFail($id);
        $exists = CnsRow::forScope($block, $round->cycle_id, $round->id)
            ->contains(fn ($r) => Inheritance::key($def['unique'], $r) === Inheritance::key($def['unique'], $source));
        if ($exists) {
            return back()->with('error', 'This round already overrides that row.')->with('block_tab', $block);
        }

        $copy = $source->replicate();
        $copy->round_id = $round->id;
        $copy->save();

        return back()->with('success', 'Overridden in ' . $round->name . '. Edit the round row to change it.')->with('block_tab', $block);
    }

    // ---- internals ----

    private function definition(string $block, CounsellingCycle $cycle): array
    {
        return CnsBlocks::resolved($block, $cycle->counselling_id);
    }

    private function create(Request $request, array $def, string $block, CounsellingCycle $cycle, ?Round $round): CnsRow
    {
        [$data, $children] = $this->prepare($request, $def, $block, $cycle, $round, null);

        if ($block === 'process' && empty($data['step_no'])) {   // process is the only numbered block, and it has round_id
            $data['step_no'] = ((int) DB::table($def['table'])->where('cycle_id', $cycle->id)
                ->when($round, fn ($q) => $q->where('round_id', $round->id), fn ($q) => $q->whereNull('round_id'))->max('step_no')) + 1;
        }

        return DB::transaction(function () use ($block, $data, $children, $def, $cycle, $round) {
            $row = CnsRow::block($block);
            $owner = ['cycle_id' => $cycle->id] + (in_array('round', $def['scopes']) ? ['round_id' => $round?->id] : []);
            $row->fill($data + $owner)->save();
            $this->syncChildren($def, $row, $children);
            return $row;
        });
    }

    /** @return array{0: array, 1: array} [row attributes, child rows by field name] */
    private function prepare(Request $request, array $def, string $block, CounsellingCycle $cycle, ?Round $round, ?CnsRow $existing): array
    {
        // The form always shows one starter row: rows left completely blank are ignored, half-filled ones are validated.
        foreach ($def['fields'] as $f) {
            if ($f['type'] === 'children') {
                $request->merge([$f['name'] => array_values(array_filter(
                    (array) $request->input($f['name'], []),
                    fn ($row) => collect((array) $row)->contains(fn ($v) => $v !== null && $v !== ''),
                ))]);
            }
        }

        $validator = Validator::make($request->all(), CnsBlocks::rules($def, (bool) $existing));
        $validator->after(function ($v) use ($request, $def, $block, $cycle, $round, $existing) {
            $this->checkUrls($v, $request, $def);
            $this->checkUnique($v, $request, $def, $block, $cycle, $round, $existing);
            match ($def['validate'] ?? null) {
                'rules' => $this->validateRule($v, $request),
                'eligibility' => $this->validateEligibility($v, $request),
                default => null,
            };
        });
        $validated = $validator->validate();

        $data = [];
        $children = [];
        foreach ($def['fields'] as $f) {
            $name = $f['name'];
            $value = $validated[$name] ?? null;

            switch ($f['type']) {
                case 'bool':
                    $data[$name] = $request->boolean($name);
                    break;
                case 'list':
                    $items = array_values(array_unique(array_filter(array_map('trim', preg_split('/\R/', (string) $value)))));
                    $data[$name] = $items ? array_slice($items, 0, 200) : null;
                    break;
                case 'multiselect':
                    $data[$name] = array_values($value ?? []) ?: null;
                    break;
                case 'children':
                    $children[$name] = $this->childRows($value, $f['sub']);
                    break;
                case 'file':
                    if ($request->hasFile($name)) {
                        $data[$name] = $request->file($name)->store('cns/content/' . $def['table'], 'public');
                    }
                    break;
                case 'select':
                    // Id-keyed selects (category_id, quota_id...) are stored as integers.
                    $idKeyed = !empty($f['options']) && is_int(array_key_first($f['options']));
                    $data[$name] = ($value === '' || $value === null) ? ($f['default'] ?? null) : ($idKeyed && ctype_digit((string) $value) ? (int) $value : $value);
                    break;
                case 'number':
                    // A blank number falls back to its default (some columns are NOT NULL, e.g. document order).
                    $data[$name] = ($value === '' || $value === null) ? ($f['default'] ?? null) : $value + 0;
                    break;
                default:
                    $data[$name] = ($value === '' || $value === null) ? ($f['default'] ?? null) : $value;
            }
        }

        return [$data, $children];
    }

    private function childRows(?array $rows, array $sub): array
    {
        $out = [];
        foreach ($rows ?? [] as $row) {
            $clean = [];
            foreach ($sub as $s) {
                $v = $row[$s['name']] ?? null;
                $clean[$s['name']] = ($v === '' || $v === null) ? null : ($s['type'] === 'number' || ($s['type'] === 'select' && ctype_digit((string) $v)) ? $v + 0 : $v);
            }
            // A row needs at least a category to mean anything.
            if (!empty($clean['category_id'])) {
                $out[] = $clean;
            }
        }
        return $out;
    }

    private function syncChildren(array $def, CnsRow $row, array $children): void
    {
        foreach ($def['fields'] as $f) {
            if ($f['type'] !== 'children') {
                continue;
            }
            DB::table($f['table'])->where($f['fk'], $row->id)->delete();
            foreach ($children[$f['name']] ?? [] as $child) {
                DB::table($f['table'])->insert($child + [$f['fk'] => $row->id, 'created_at' => now(), 'updated_at' => now()]);
            }
        }
    }

    /** Links must be absolute http(s) and must not point back at this CRM. */
    private function checkUrls($v, Request $request, array $def): void
    {
        $own = array_map('strtolower', array_filter([$request->getHost(), parse_url(config('app.url'), PHP_URL_HOST)]));
        foreach ($def['fields'] as $f) {
            if ($f['type'] !== 'url' || !$request->filled($f['name'])) {
                continue;
            }
            $host = strtolower((string) parse_url($request->input($f['name']), PHP_URL_HOST));
            $scheme = strtolower((string) parse_url($request->input($f['name']), PHP_URL_SCHEME));
            if (!in_array($scheme, ['http', 'https'], true) || in_array($host, $own, true)) {
                $v->errors()->add($f['name'], 'Enter the official external URL, not a page of this CRM.');
            }
        }
    }

    /** No two rows may share a key in the same scope: edit the existing one instead. */
    private function checkUnique($v, Request $request, array $def, string $block, CounsellingCycle $cycle, ?Round $round, ?CnsRow $existing): void
    {
        if (empty($def['unique']) || $v->errors()->isNotEmpty()) {
            return;
        }
        $candidate = new \stdClass();
        foreach ($def['unique'] as $f) {
            $candidate->$f = $request->filled($f) ? $request->input($f) : null;
        }
        $key = Inheritance::key($def['unique'], $candidate);
        $clash = CnsRow::forScope($block, $cycle->id, $round?->id)
            ->first(fn ($r) => (!$existing || $r->id !== $existing->id) && Inheritance::key($def['unique'], $r) === $key);
        if ($clash) {
            $v->errors()->add($def['unique'][0], 'A ' . strtolower($def['label']) . ' with the same ' . implode(' / ', array_map(fn ($f) => str_replace(['_id', '_'], ['', ' '], $f), $def['unique']))
                . ' already exists here. Edit that one instead.');
        }
    }

    private function validateRule($v, Request $request): void
    {
        if ($request->deposit_outcome === 'partly_refunded' && !$request->filled('refund_percent') && !$request->filled('refund_amount')) {
            $v->errors()->add('refund_percent', 'Enter a refund percent or amount for a partly refunded deposit.');
        }
        if ($request->deposit_outcome !== 'partly_refunded' && ($request->filled('refund_percent') || $request->filled('refund_amount'))) {
            $v->errors()->add('deposit_outcome', 'Refund percent / amount only applies when the deposit is partly refunded.');
        }
    }

    private function validateEligibility($v, Request $request): void
    {
        if ($request->filled('age_min') && $request->filled('age_max') && (int) $request->age_min > (int) $request->age_max) {
            $v->errors()->add('age_max', 'Max age must be at least the min age.');
        }
        $rule = $request->input('domicile_rule', 'none');
        if ($rule !== 'none' && empty($request->input('domicile_states'))) {
            $v->errors()->add('domicile_states', 'Pick the domicile states for this rule.');
        }
        if (in_array($rule, ['study_years_in_state', 'domicile_or_study']) && !$request->filled('study_years_required')) {
            $v->errors()->add('study_years_required', 'Enter the years of study required.');
        }
        $seen = [];
        foreach ((array) $request->input('scores', []) as $i => $s) {
            if (empty($s['category_id'])) continue;
            $k = ($s['category_id'] ?? '') . '|' . ($s['exam_id'] ?? '');
            if (isset($seen[$k])) {
                $v->errors()->add("scores.$i.category_id", 'The same category and exam appear twice.');
            }
            $seen[$k] = true;
        }
    }

    private function deleteFileIfUnused(string $table, string $path): void
    {
        $used = collect(['template_file'])->contains(
            fn ($col) => Schema::hasColumn($table, $col) && DB::table($table)->where($col, $path)->exists()
        );
        if (!$used) {
            Storage::disk('public')->delete($path);
        }
    }
}

<?php

namespace App\Http\Controllers\Admin\Quality;

use App\Http\Controllers\Controller;
use App\Support\Quality\IdentityFields;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;

/** Identity review: AISHE code, NIRF institute id, legal entity type, group, campus role, department unit type. */
class IdentityController extends Controller
{
    public function index(Request $request)
    {
        $levels = IdentityFields::levels();
        $level = array_key_exists($request->query('level'), $levels) ? $request->query('level') : 'organisation';
        $def = $levels[$level];

        $q = DB::table($def['table'])->select('*');
        if ($s = trim((string) $request->query('q'))) $q->where($def['name'], 'like', '%' . $s . '%');
        $missing = $request->query('missing');
        if ($missing === 'aishe' && $level !== 'department') $q->where(fn ($w) => $w->whereNull('aishe_code')->orWhere('aishe_code', ''));
        if ($missing === 'nirf') $q->where(fn ($w) => $w->whereNull('nirf_institute_id')->orWhere('nirf_institute_id', ''));
        if ($missing === 'legal' && $level === 'organisation') $q->whereNull('legal_entity_type');
        if ($missing === 'role' && $level === 'campus') $q->whereNull('campus_role');
        if ($level === 'organisation') {
            if ($request->filled('group')) $q->where('group_id', (int) $request->query('group'));
            if ($request->filled('type')) $q->where('organisation_type_id', (int) $request->query('type'));
        }
        if ($level === 'campus' && $request->filled('organisation')) $q->where('organisation_id', (int) $request->query('organisation'));

        $rows = $q->orderBy($def['name'])->paginate(50)->withQueryString();
        $parents = [];
        if ($level !== 'organisation') {
            $parents = DB::table('organisations')->whereIn('id', $rows->pluck('organisation_id')->unique())->pluck('name', 'id')->all();
        }

        return view('admin.quality.identity', [
            'level' => $level, 'def' => $def, 'levels' => $levels, 'rows' => $rows, 'parents' => $parents,
            'groups' => IdentityFields::groups(), 'cols' => IdentityFields::columns($level),
            'orgTypes' => DB::table('organisation_types')->orderBy('sort_order')->pluck('title', 'id')->all(),
        ]);
    }

    /** Inline save of one row (called with fetch). */
    public function update(Request $request, string $level, string $id)
    {
        abort_unless(isset(IdentityFields::levels()[$level]), 404);
        $def = IdentityFields::levels()[$level];
        abort_unless(DB::table($def['table'])->where('id', $id)->exists(), 404);

        $input = $this->normalise($request->only(IdentityFields::columns($level)));
        $data = Validator::make($input, IdentityFields::rules($level, $id))->validate();
        $data = array_intersect_key($data, $input);   // only touch the fields that were sent
        if (array_key_exists('inherits_org_accreditation', $data)) $data['inherits_org_accreditation'] = (int) $data['inherits_org_accreditation'];
        if ($data) DB::table($def['table'])->where('id', $id)->update($data + ['updated_at' => now()]);

        return response()->json(['ok' => true, 'saved' => array_keys($data)]);
    }

    // ---------- CSV import (dry run, then confirm) ----------

    public function import(Request $request)
    {
        $request->validate(['file' => ['required', 'file', 'mimes:csv,txt', 'max:4096']]);
        $fh = fopen($request->file('file')->getRealPath(), 'r');
        $header = array_map(fn ($h) => Str::snake(trim(preg_replace('/^\xEF\xBB\xBF/', '', (string) $h))), fgetcsv($fh) ?: []);
        if (!in_array('id', $header, true)) {
            return back()->with('error', 'The file needs an "id" column (organisation id, or campus / department uuid). A "level" column (organisation, campus, department) is optional and defaults to organisation.');
        }

        $rows = []; $seen = []; $line = 1;
        while (($r = fgetcsv($fh)) !== false) {
            $line++;
            if (count(array_filter($r, 'strlen')) === 0) continue;
            $row = array_combine($header, array_pad(array_slice($r, 0, count($header)), count($header), ''));
            $level = strtolower(trim($row['level'] ?? '')) ?: 'organisation';
            $id = trim($row['id']);
            $res = ['line' => $line, 'level' => $level, 'id' => $id, 'name' => null, 'changes' => [], 'errors' => []];

            if (!isset(IdentityFields::levels()[$level])) { $res['errors'][] = "Unknown level \"$level\"."; $rows[] = $res; continue; }
            $def = IdentityFields::levels()[$level];
            $current = DB::table($def['table'])->where('id', $id)->first();
            if (!$current) { $res['errors'][] = "No $level with id \"$id\"."; $rows[] = $res; continue; }
            $res['name'] = $current->{$def['name']};

            // Group can be given by name; everything else by its column name.
            if ($level === 'organisation' && trim($row['group'] ?? '') !== '' && trim($row['group_id'] ?? '') === '') {
                $gid = DB::table('organisation_groups')->whereRaw('LOWER(name) = ?', [strtolower(trim($row['group']))])->value('id');
                if (!$gid) $res['errors'][] = 'Unknown group "' . trim($row['group']) . '" (create it under Groups first).';
                else $row['group_id'] = (string) $gid;
            }
            $given = $this->normalise(array_intersect_key($row, array_flip(IdentityFields::columns($level))));
            $given = array_filter($given, fn ($v) => $v !== null);   // a blank cell means "leave as is"
            $v = Validator::make($given, IdentityFields::rules($level, $id));
            if ($v->fails()) { foreach ($v->errors()->all() as $e) $res['errors'][] = $e; }

            foreach (['aishe_code', 'nirf_institute_id'] as $u) {
                if (isset($given[$u])) {
                    $key = "$level|$u|" . strtolower($given[$u]);
                    if (isset($seen[$key]) && $seen[$key] !== $id) $res['errors'][] = "$u \"{$given[$u]}\" appears twice in this file.";
                    $seen[$key] = $id;
                }
            }
            foreach ($given as $col => $val) {
                if ((string) ($current->$col ?? '') !== (string) $val) $res['changes'][$col] = ['from' => $current->$col ?? null, 'to' => $val];
            }
            $rows[] = $res;
        }
        fclose($fh);

        $token = Str::random(24);
        Cache::put('quality-identity-import:' . $token, $rows, now()->addHour());
        return view('admin.quality.identity-import', ['rows' => $rows, 'token' => $token,
            'ok' => count(array_filter($rows, fn ($r) => !$r['errors'] && $r['changes'])),
            'bad' => count(array_filter($rows, fn ($r) => $r['errors'])),
            'same' => count(array_filter($rows, fn ($r) => !$r['errors'] && !$r['changes']))]);
    }

    public function confirmImport(Request $request)
    {
        $rows = Cache::get('quality-identity-import:' . $request->validate(['token' => ['required', 'string']])['token']);
        if (!$rows) return redirect()->route('admin.quality.identity.index')->with('error', 'That preview has expired. Upload the file again.');

        $n = 0;
        DB::transaction(function () use ($rows, &$n) {
            foreach ($rows as $r) {
                if ($r['errors'] || !$r['changes']) continue;
                $def = IdentityFields::levels()[$r['level']];
                $data = array_map(fn ($c) => $c['to'], $r['changes']);
                if (isset($data['inherits_org_accreditation'])) $data['inherits_org_accreditation'] = (int) $data['inherits_org_accreditation'];
                DB::table($def['table'])->where('id', $r['id'])->update($data + ['updated_at' => now()]);
                $n++;
            }
        });
        Cache::forget('quality-identity-import:' . $request->input('token'));
        return redirect()->route('admin.quality.identity.index')->with('success', "$n row(s) updated. Rows with errors were skipped.");
    }

    public function template()
    {
        $csv = "level,id,aishe_code,nirf_institute_id,legal_entity_type,group,campus_role,academic_unit_type,inherits_org_accreditation\n"
            . "organisation,1,U-0001,IR-E-U-0001,state_public,,,,\n"
            . "campus,00000000-0000-0000-0000-000000000000,,,,,main,,1\n"
            . "department,00000000-0000-0000-0000-000000000000,,,,,,school,\n";
        return response($csv, 200, ['Content-Type' => 'text/csv', 'Content-Disposition' => 'attachment; filename="identity-import-template.csv"']);
    }

    private function normalise(array $in): array
    {
        return array_map(fn ($v) => is_string($v) ? (trim($v) === '' ? null : trim($v)) : $v, $in);
    }
}

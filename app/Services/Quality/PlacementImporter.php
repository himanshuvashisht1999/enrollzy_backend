<?php

namespace App\Services\Quality;

use App\Models\ExamModule\AuditLog;
use App\Models\Quality\EntityPlacement;
use App\Support\Quality\Levels;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

/**
 * CSV import of raw placement figures (one row per programme per academic year).
 *
 *  - preview() reads and checks every row with the same rules as the form and saves NOTHING.
 *  - apply() creates only the rows that were clean and new, under one batch id. It never updates or overwrites an existing row.
 *  - undo() takes a whole batch back out (soft delete).
 *
 * Columns: id (required), academic_year (required, 2024-25), level (default course), students_graduating, students_placed, students_higher_studies,
 * median_salary, highest_salary, average_salary, recruiters_count, placement_source, source_url, notes. Other columns are ignored, so the pre-filled
 * template (which carries organisation / campus / programme names for your eyes) uploads as it is.
 * Salaries may be written as rupees per year (650000, 6,50,000) or with a unit (6.5 LPA, 6.5 lakh, 1.2 crore). The placement percentage is never
 * stored: it is computed. A "placement_percentage" column, if present, is only used to warn when it does not match the figures.
 */
class PlacementImporter
{
    public const MAX_ROWS = 20000;
    public const COLUMNS = ['level', 'id', 'organisation', 'campus', 'programme', 'academic_year', 'students_graduating', 'students_placed', 'students_higher_studies',
        'median_salary', 'highest_salary', 'average_salary', 'recruiters_count', 'placement_source', 'source_url', 'notes'];
    private const INTS = ['students_graduating', 'students_placed', 'students_higher_studies', 'recruiters_count'];
    private const MONEY = ['median_salary', 'highest_salary', 'average_salary'];

    private const SYNONYMS = ['programme_id' => 'id', 'course_id' => 'id', 'year' => 'academic_year', 'batch' => 'academic_year', 'graduating' => 'students_graduating', 'eligible' => 'students_graduating',
        'eligible_students' => 'students_graduating', 'graduates' => 'students_graduating', 'placed' => 'students_placed', 'students_placed_count' => 'students_placed', 'higher_studies' => 'students_higher_studies',
        'median' => 'median_salary', 'median_package' => 'median_salary', 'median_ctc' => 'median_salary', 'highest' => 'highest_salary', 'highest_package' => 'highest_salary', 'highest_ctc' => 'highest_salary',
        'average' => 'average_salary', 'average_package' => 'average_salary', 'avg_salary' => 'average_salary', 'recruiters' => 'recruiters_count', 'companies' => 'recruiters_count',
        'source' => 'placement_source', 'url' => 'source_url', 'link' => 'source_url', 'placement_percent' => 'placement_percentage'];

    /** @return array{ok:bool, error?:string, rows:array, summary:array} */
    public function preview(string $path): array
    {
        $fh = fopen($path, 'r');
        if (!$fh) return $this->fail('The file could not be read.');
        $header = array_map(fn ($h) => $this->column($h), fgetcsv($fh) ?: []);
        $missing = array_values(array_diff(['id', 'academic_year'], $header));
        if ($missing) { fclose($fh); return $this->fail('The file needs the column' . (count($missing) > 1 ? 's' : '') . ': ' . implode(', ', $missing) . '. Download the template to see the layout.'); }

        $raw = []; $line = 1;
        while (($r = fgetcsv($fh)) !== false) {
            $line++;
            if (count(array_filter($r, fn ($v) => trim((string) $v) !== '')) === 0) continue;
            if (count($raw) >= self::MAX_ROWS) { fclose($fh); return $this->fail('More than ' . number_format(self::MAX_ROWS) . ' rows. Split the file.'); }
            $raw[] = ['line' => $line] + array_combine($header, array_pad(array_slice($r, 0, count($header)), count($header), ''));
        }
        fclose($fh);
        if (!$raw) return $this->fail('The file has no data rows.');

        // Lookups once for the whole file.
        $idsByLevel = [];
        foreach ($raw as $r) {
            $lvl = mb_strtolower(trim($r['level'] ?? '')) ?: 'course';
            if (Levels::valid($lvl)) $idsByLevel[$lvl][] = trim($r['id']);
        }
        $found = []; $existing = [];
        foreach ($idsByLevel as $lvl => $ids) {
            $model = Levels::MAP[$lvl]; $key = (new $model)->getKeyName();
            $found[$lvl] = array_flip($model::whereIn($key, array_values(array_unique($ids)))->pluck($key)->map(fn ($k) => (string) $k)->all());
            foreach (array_chunk(array_values(array_unique($ids)), 1000) as $chunk) {
                foreach (DB::table('entity_placements')->whereNull('deleted_at')->where('entity_type', $lvl)->whereIn('entity_id', $chunk)->get(['entity_id', 'academic_year']) as $e) {
                    $existing["$lvl|{$e->entity_id}|{$e->academic_year}"] = true;
                }
            }
        }

        $rows = []; $seen = [];
        foreach ($raw as $r) $rows[] = $this->check($r, $found, $existing, $seen);

        $summary = ['rows' => count($rows), 'new' => 0, 'exists' => 0, 'error' => 0, 'warnings' => 0, 'placed' => 0, 'graduating' => 0];
        foreach ($rows as $row) {
            $summary[$row['status']]++;
            if ($row['warnings']) $summary['warnings']++;
            if ($row['status'] === 'new') { $summary['placed'] += (int) ($row['data']['students_placed'] ?? 0); $summary['graduating'] += (int) ($row['data']['students_graduating'] ?? 0); }
        }
        return ['ok' => true, 'rows' => $rows, 'summary' => $summary];
    }

    private function check(array $r, array $found, array $existing, array &$seen): array
    {
        $out = ['line' => $r['line'], 'level' => mb_strtolower(trim($r['level'] ?? '')) ?: 'course', 'id' => trim($r['id']), 'year' => null, 'status' => 'new', 'errors' => [], 'warnings' => [], 'data' => []];
        $err = function (string $m) use (&$out) { $out['errors'][] = $m; };

        if (!Levels::valid($out['level'])) $err('Unknown level "' . $out['level'] . '" (use organisation, campus, department or course).');
        elseif ($out['id'] === '' || !isset($found[$out['level']][$out['id']])) $err('No ' . $out['level'] . ' with id "' . $out['id'] . '".');
        elseif ($out['level'] !== 'course') $out['warnings'][] = 'Recorded at ' . $out['level'] . ' level: shown as "reported by the institution" and never added into programme totals.';

        $year = $this->year($r['academic_year'] ?? '');
        $out['year'] = $year;
        if (!$year) $err('Academic year "' . trim($r['academic_year'] ?? '') . '" is not valid (use 2024-25).');

        $d = [];
        foreach (self::INTS as $f) {
            $v = trim((string) ($r[$f] ?? ''));
            if ($v === '') { $d[$f] = null; continue; }
            $clean = str_replace([',', ' '], '', $v);
            if (!ctype_digit($clean) || (int) $clean > 10000000) { $err(self::label($f) . " \"$v\" must be a whole number."); $d[$f] = null; } else $d[$f] = (int) $clean;
        }
        foreach (self::MONEY as $f) {
            $d[$f] = $this->money((string) ($r[$f] ?? ''), $problem);
            if ($problem) $err(self::label($f) . ': ' . $problem);
        }

        if (collect(array_merge(self::INTS, self::MONEY))->every(fn ($f) => ($d[$f] ?? null) === null) && !$out['errors']) $err('No figures in this row.');
        if ($d['students_placed'] !== null && $d['students_graduating'] !== null && $d['students_placed'] > $d['students_graduating']) $err('More students placed (' . number_format($d['students_placed']) . ') than graduating (' . number_format($d['students_graduating']) . ').');
        if ($d['students_higher_studies'] !== null && $d['students_graduating'] !== null && $d['students_higher_studies'] > $d['students_graduating']) $err('More students in higher studies than graduating.');
        if ($d['median_salary'] !== null && $d['highest_salary'] !== null && $d['median_salary'] > $d['highest_salary']) $err('Median salary is higher than the highest salary.');

        // A stated percentage is only cross-checked, never stored.
        $pct = trim(str_replace('%', '', (string) ($r['placement_percentage'] ?? '')));
        if ($pct !== '' && is_numeric($pct) && $d['students_placed'] !== null && $d['students_graduating']) {
            $calc = round($d['students_placed'] / $d['students_graduating'] * 100, 1);
            if (abs($calc - (float) $pct) > 1.0) $out['warnings'][] = "The stated placement percentage ($pct%) does not match the figures ($calc%). The percentage is computed from the figures.";
        }
        if ($d['students_placed'] !== null && $d['students_graduating'] === null) $out['warnings'][] = 'No graduating count, so the placement percentage cannot be computed.';

        $url = trim($r['source_url'] ?? '');
        if ($url !== '' && (!filter_var($url, FILTER_VALIDATE_URL) || strlen($url) > 500)) $err('Source link "' . mb_substr($url, 0, 60) . '" is not a valid URL.');
        $src = trim($r['placement_source'] ?? '');
        if (mb_strlen($src) > 255) $err('"Where the figures come from" is longer than 255 characters.');

        if (!$out['errors']) {
            $key = "{$out['level']}|{$out['id']}|$year";
            if (isset($existing[$key])) { $out['status'] = 'exists'; $out['errors'][] = "Figures for $year are already recorded. Left as they are (edit them on the entity page)."; }
            elseif (isset($seen[$key])) $err('Appears twice in this file (first on line ' . $seen[$key] . ').');
            else $seen[$key] = $r['line'];
        }
        if ($out['errors'] && $out['status'] !== 'exists') $out['status'] = 'error';

        if ($out['status'] === 'new') {
            $out['data'] = ['entity_type' => $out['level'], 'entity_id' => $out['id'], 'academic_year' => $year] + $d
                + ['placement_source' => $src ?: null, 'source_url' => $url ?: null, 'notes' => trim($r['notes'] ?? '') ?: null];
        }
        return $out;
    }

    /** Creates the clean new rows under one batch. */
    public function apply(array $rows, ?int $userId): array
    {
        $batch = 'pl-' . now()->format('ymd-His') . '-' . Str::lower(Str::random(4));
        $created = 0; $skipped = 0; $now = now();

        DB::transaction(function () use ($rows, $userId, $batch, $now, &$created, &$skipped) {
            foreach (array_chunk(array_values(array_filter($rows, fn ($r) => $r['status'] === 'new')), 500) as $chunk) {
                // Someone may have added the same figures since the preview: never create a second row.
                $have = [];
                foreach (collect($chunk)->groupBy('level') as $lvl => $group) {
                    foreach (DB::table('entity_placements')->whereNull('deleted_at')->where('entity_type', $lvl)->whereIn('entity_id', $group->pluck('id')->unique()->all())->get(['entity_id', 'academic_year']) as $e) {
                        $have["$lvl|{$e->entity_id}|{$e->academic_year}"] = true;
                    }
                }
                $insert = [];
                foreach ($chunk as $r) {
                    $d = $r['data'];
                    if (isset($have["{$d['entity_type']}|{$d['entity_id']}|{$d['academic_year']}"])) { $skipped++; continue; }
                    $insert[] = $d + ['verification_status' => 'unverified', 'import_batch' => $batch, 'created_by' => $userId, 'created_at' => $now, 'updated_at' => $now];
                }
                if ($insert) { DB::table('entity_placements')->insert($insert); $created += count($insert); }
            }
            AuditLog::create(['auditable_type' => EntityPlacement::class, 'auditable_id' => 0, 'user_id' => $userId, 'action' => 'import',
                'old_values' => null, 'new_values' => ['batch' => $batch, 'created' => $created, 'skipped_since_preview' => $skipped]]);
        });
        return ['batch' => $batch, 'created' => $created, 'skipped' => $skipped];
    }

    /** @return array{0:int,1:int} removed, of which verified */
    public function undo(string $batch, ?int $userId): array
    {
        $q = DB::table('entity_placements')->where('import_batch', $batch)->whereNull('deleted_at');
        $total = (clone $q)->count();
        $verified = (clone $q)->where('verification_status', 'verified')->count();
        if ($total === 0) return [0, 0];
        DB::transaction(function () use ($q, $batch, $total, $verified, $userId) {
            $q->update(['deleted_at' => now(), 'updated_at' => now()]);
            AuditLog::create(['auditable_type' => EntityPlacement::class, 'auditable_id' => 0, 'user_id' => $userId, 'action' => 'import_undone',
                'old_values' => null, 'new_values' => ['batch' => $batch, 'removed' => $total, 'of_which_verified' => $verified]]);
        });
        return [$total, $verified];
    }

    public function batches(int $limit = 20): array
    {
        return DB::table('entity_placements')->whereNotNull('import_batch')
            ->selectRaw("import_batch, MIN(created_at) AS at, MAX(created_by) AS by_user, COUNT(*) AS total, SUM(deleted_at IS NULL) AS active, SUM(deleted_at IS NULL AND verification_status = 'verified') AS verified")
            ->groupBy('import_batch')->orderByDesc('at')->limit($limit)->get()->all();
    }

    /** CSV lines: programmes (of one organisation, or all up to the cap) that have no figures yet for the year, ready to fill in. */
    public function template(?int $organisationId, ?string $year): \Generator
    {
        yield self::COLUMNS;
        $year = $year ?: $this->defaultYear();
        $q = DB::table('organisation_courses as oc')->join('organisations as o', 'o.id', '=', 'oc.organisation_id')->leftJoin('campuses as cp', 'cp.id', '=', 'oc.campus_id')->leftJoin('courses as c', 'c.id', '=', 'oc.course_id')
            ->when($organisationId, fn ($w) => $w->where('oc.organisation_id', $organisationId))
            ->whereNotExists(fn ($w) => $w->select(DB::raw(1))->from('entity_placements as e')->where('e.entity_type', 'course')->whereColumn('e.entity_id', 'oc.id')->where('e.academic_year', $year)->whereNull('e.deleted_at'))
            ->select('oc.id', 'o.name as organisation', 'cp.campus_name as campus', 'c.name as course', 'oc.academic_unit_name as unit')
            ->orderBy('o.name')->orderBy('oc.id')->limit(self::MAX_ROWS);
        $any = false;
        foreach ($q->cursor() as $r) {
            $any = true;
            yield ['course', $r->id, $r->organisation, $r->campus, $r->unit ?: $r->course, $year, '', '', '', '', '', '', '', '', '', ''];
        }
        if (!$any) {
            yield ['course', '12345', 'Example University', 'Main Campus', 'B.Tech Computer Science', $year, '120', '96', '10', '6.5 LPA', '28 LPA', '7.2 LPA', '85', 'Placement report ' . $year, 'https://example.com/placements.pdf', ''];
        }
    }

    /** The last academic year that has completed (placements are reported after the batch graduates). */
    public function defaultYear(): string
    {
        $y = (now()->month >= 7 ? now()->year : now()->year - 1) - 1;
        return $y . '-' . substr((string) ($y + 1), -2);
    }

    public function names(array $rows): array
    {
        $names = [];
        foreach (collect($rows)->filter(fn ($r) => Levels::valid($r['level']))->groupBy('level') as $lvl => $group) {
            $model = Levels::MAP[$lvl];
            $q = $model::whereIn((new $model)->getKeyName(), $group->pluck('id')->unique()->all());
            if ($lvl === 'course') $q->with('course', 'campus');
            foreach ($q->get() as $m) $names["$lvl|" . $m->getKey()] = Levels::describe($m) . ' · ' . Levels::name($m);
        }
        return $names;
    }

    /**
     * "6.5 LPA", "6.5 lakh", "1.2 crore", "6,50,000", "650000", "Rs 6,50,000" => rupees per year.
     * A bare small number is refused rather than guessed (is 6.5 lakh? 6.5 thousand? a monthly figure?).
     */
    public static function money(string $v, &$problem = null): ?int
    {
        $problem = null;
        $v = trim($v);
        if ($v === '') return null;
        $s = mb_strtolower(preg_replace('/[\x{20B9}]|\brs\.?|\binr\b|per\s*(annum|year)|\bp\.?\s*a\.?\b|\/\s*(yr|year)/iu', ' ', $v));
        $s = trim(preg_replace('/\s+/', ' ', $s));
        if (!preg_match('/^(\d[\d,]*(?:\.\d+)?)\s*(lpa|l\.p\.a\.?|lakhs?|lacs?|lac|l|crores?|cr)?\.?$/u', $s, $m)) { $problem = "\"$v\" is not an amount (use 650000, 6,50,000 or 6.5 LPA)."; return null; }
        $num = (float) str_replace(',', '', $m[1]);
        $unit = $m[2] ?? '';
        if ($unit !== '') $amount = $num * (str_starts_with($unit, 'c') ? 10000000 : 100000);
        elseif (str_contains($m[1], '.') || $num < 100000) { $problem = "\"$v\" has no unit and is too small to be a yearly salary in rupees (it may be monthly, or in lakhs). Write the yearly amount (650000) or add LPA (6.5 LPA)."; return null; }
        else $amount = $num;
        if ($amount > 1000000000) { $problem = "\"$v\" is more than Rs 100 crore a year, which cannot be right."; return null; }
        return (int) round($amount);
    }

    private static function label(string $f): string
    {
        return ucfirst(str_replace('_', ' ', $f));
    }

    private function column(string $h): string
    {
        $h = Str::snake(trim(preg_replace('/^\xEF\xBB\xBF/', '', $h)));
        return self::SYNONYMS[$h] ?? $h;
    }

    /** 2024-25, 2024-2025, 2024/25 => 2024-25. */
    private function year(string $v): ?string
    {
        $v = trim($v);
        if (!preg_match('/^(\d{4})\s*[-\/]\s*(\d{4}|\d{2})$/', $v, $m)) return null;
        $next = (int) $m[1] + 1;
        if (strlen($m[2]) === 4 ? (int) $m[2] !== $next : (int) $m[2] !== $next % 100) return null;
        return $m[1] . '-' . substr((string) $next, -2);
    }

    private function fail(string $msg): array
    {
        return ['ok' => false, 'error' => $msg, 'rows' => [], 'summary' => []];
    }
}

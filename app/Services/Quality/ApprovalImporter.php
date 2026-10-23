<?php

namespace App\Services\Quality;

use App\Models\ExamModule\AuditLog;
use App\Models\Quality\AccreditationBody;
use App\Models\Quality\EntityApproval;
use App\Support\Quality\Levels;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

/**
 * CSV import of statutory approvals (with sanctioned intake).
 *
 *  - preview() reads and checks every row with the same rules as the form and saves NOTHING.
 *  - apply() creates only the rows that were clean and new, under one batch id. It never updates or overwrites an existing record.
 *  - undo() takes a whole batch back out (soft delete), so a bad file can be reversed.
 *
 * Columns: id (required), body (required, e.g. NMC), academic_year (required, 2025-26), level (default course),
 * sanctioned_intake, approval_number, valid_from, valid_to, status, source_url, notes. Other columns are ignored,
 * so the pre-filled template (which carries organisation / campus / programme names for your eyes) can be uploaded as is.
 */
class ApprovalImporter
{
    public const MAX_ROWS = 20000;
    public const COLUMNS = ['level', 'id', 'organisation', 'campus', 'programme', 'discipline', 'body', 'academic_year', 'sanctioned_intake',
        'approval_number', 'valid_from', 'valid_to', 'status', 'source_url', 'notes'];

    private const SYNONYMS = ['programme_id' => 'id', 'course_id' => 'id', 'regulator' => 'body', 'approving_body' => 'body', 'year' => 'academic_year',
        'intake' => 'sanctioned_intake', 'seats' => 'sanctioned_intake', 'sanctioned_seats' => 'sanctioned_intake', 'approval_no' => 'approval_number',
        'letter_number' => 'approval_number', 'url' => 'source_url', 'source' => 'source_url'];

    /** @return array{ok:bool, error?:string, rows:array, summary:array} */
    public function preview(string $path): array
    {
        $fh = fopen($path, 'r');
        if (!$fh) return $this->fail('The file could not be read.');
        $header = fgetcsv($fh) ?: [];
        $header = array_map(fn ($h) => $this->column($h), $header);
        foreach (['id', 'body', 'academic_year'] as $need) {
            if (!in_array($need, $header, true)) { fclose($fh); return $this->fail("The file needs a \"$need\" column. Download the template to see the layout."); }
        }

        $raw = [];
        $line = 1;
        while (($r = fgetcsv($fh)) !== false) {
            $line++;
            if (count(array_filter($r, fn ($v) => trim((string) $v) !== '')) === 0) continue;
            if (count($raw) >= self::MAX_ROWS) { fclose($fh); return $this->fail('More than ' . number_format(self::MAX_ROWS) . ' rows. Split the file.'); }
            $raw[] = ['line' => $line] + array_combine($header, array_pad(array_slice($r, 0, count($header)), count($header), ''));
        }
        fclose($fh);
        if (!$raw) return $this->fail('The file has no data rows.');

        // Lookups done once for the whole file.
        $bodies = AccreditationBody::where('status', true)->get()->filter->isApprovalKind();
        $byKey = [];
        foreach ($bodies as $b) { $byKey[mb_strtolower($b->short_name)] = $b; $byKey[mb_strtolower($b->name)] = $b; }
        $idsByLevel = [];
        foreach ($raw as $r) {
            $lvl = mb_strtolower(trim($r['level'] ?? '')) ?: 'course';
            if (Levels::valid($lvl)) $idsByLevel[$lvl][] = trim($r['id']);
        }
        $found = [];
        foreach ($idsByLevel as $lvl => $ids) {
            $model = Levels::MAP[$lvl];
            $found[$lvl] = array_flip($model::whereIn((new $model)->getKeyName(), array_values(array_unique($ids)))->pluck((new $model)->getKeyName())->map(fn ($k) => (string) $k)->all());
        }
        $existing = [];
        $bodyIds = $bodies->pluck('id')->all();
        foreach ($idsByLevel as $lvl => $ids) {
            foreach (array_chunk(array_values(array_unique($ids)), 1000) as $chunk) {
                foreach (DB::table('entity_approvals')->whereNull('deleted_at')->where('entity_type', $lvl)->whereIn('entity_id', $chunk)->whereIn('accreditation_body_id', $bodyIds)
                    ->get(['entity_id', 'accreditation_body_id', 'academic_year']) as $e) {
                    $existing["$lvl|{$e->entity_id}|{$e->accreditation_body_id}|{$e->academic_year}"] = true;
                }
            }
        }

        $rows = []; $seen = [];
        foreach ($raw as $r) {
            $rows[] = $this->check($r, $byKey, $found, $existing, $seen);
        }

        $summary = ['rows' => count($rows), 'new' => 0, 'exists' => 0, 'error' => 0, 'intake' => 0];
        foreach ($rows as $row) {
            $summary[$row['status']]++;
            if ($row['status'] === 'new') $summary['intake'] += (int) ($row['data']['sanctioned_intake'] ?? 0);
        }
        return ['ok' => true, 'rows' => $rows, 'summary' => $summary];
    }

    private function check(array $r, array $byKey, array $found, array $existing, array &$seen): array
    {
        $out = ['line' => $r['line'], 'level' => mb_strtolower(trim($r['level'] ?? '')) ?: 'course', 'id' => trim($r['id']), 'body' => trim($r['body']),
            'year' => null, 'status' => 'new', 'errors' => [], 'data' => []];
        $err = function (string $m) use (&$out) { $out['errors'][] = $m; };

        if (!Levels::valid($out['level'])) $err('Unknown level "' . $out['level'] . '" (use organisation, campus, department or course).');
        elseif ($out['id'] === '' || !isset($found[$out['level']][$out['id']])) $err('No ' . $out['level'] . ' with id "' . $out['id'] . '".');

        $body = $byKey[mb_strtolower($out['body'])] ?? null;
        if (!$body) $err('Unknown approving body "' . $out['body'] . '" (it must be an active statutory approval / recognition / affiliation body).');
        elseif (Levels::valid($out['level']) && !$body->allowsLevel($out['level'])) $err($body->short_name . ' cannot be recorded at ' . $out['level'] . ' level.');

        $year = $this->year($r['academic_year'] ?? '');
        $out['year'] = $year;
        if (!$year) $err('Academic year "' . trim($r['academic_year'] ?? '') . '" is not valid (use 2025-26).');

        $intake = trim((string) ($r['sanctioned_intake'] ?? ''));
        $intakeVal = null;
        if ($intake !== '') {
            $clean = str_replace([',', ' '], '', $intake);
            if (!ctype_digit($clean) || (int) $clean > 100000) $err('Sanctioned intake "' . $intake . '" must be a whole number between 0 and 100000.');
            else $intakeVal = (int) $clean;
        }

        $from = $this->date($r['valid_from'] ?? '', $bad1);
        $to = $this->date($r['valid_to'] ?? '', $bad2);
        if ($bad1) $err('Valid-from "' . trim($r['valid_from']) . '" is not a date (use 2025-07-01).');
        if ($bad2) $err('Valid-to "' . trim($r['valid_to']) . '" is not a date (use 2026-06-30).');
        if ($from && $to && $to < $from) $err('Valid-to is before valid-from.');
        if ($body && !$body->has_validity && $to) $err($body->short_name . ' has no validity period.');

        $status = mb_strtolower(trim($r['status'] ?? '')) ?: 'active';
        if (!array_key_exists($status, EntityApproval::STATUSES)) $err('Status "' . $status . '" must be one of: ' . implode(', ', array_keys(EntityApproval::STATUSES)) . '.');

        $url = trim($r['source_url'] ?? '');
        if ($url !== '' && (!filter_var($url, FILTER_VALIDATE_URL) || strlen($url) > 500)) $err('Source link "' . mb_substr($url, 0, 60) . '" is not a valid URL.');
        $number = trim($r['approval_number'] ?? '');
        if (mb_strlen($number) > 100) $err('Approval number is longer than 100 characters.');

        if (!$out['errors']) {
            $key = "{$out['level']}|{$out['id']}|{$body->id}|$year";
            if (isset($existing[$key])) { $out['status'] = 'exists'; $out['errors'][] = 'Already recorded for ' . $year . '. Left as it is (edit it on the entity page).'; }
            elseif (isset($seen[$key])) $err('Appears twice in this file (first on line ' . $seen[$key] . ').');
            else $seen[$key] = $r['line'];
        }
        if ($out['errors'] && $out['status'] !== 'exists') $out['status'] = 'error';

        if ($out['status'] === 'new') {
            $out['body'] = $body->short_name;
            $out['data'] = ['entity_type' => $out['level'], 'entity_id' => $out['id'], 'accreditation_body_id' => $body->id, 'academic_year' => $year,
                'sanctioned_intake' => $intakeVal, 'approval_number' => $number ?: null, 'valid_from' => $from, 'valid_to' => $to, 'status' => $status,
                'source_url' => $url ?: null, 'notes' => trim($r['notes'] ?? '') ?: null];
        }
        return $out;
    }

    /** Creates the clean new rows under one batch. Returns the batch id and counts. */
    public function apply(array $rows, ?int $userId): array
    {
        $batch = 'ap-' . now()->format('ymd-His') . '-' . Str::lower(Str::random(4));
        $created = 0; $skipped = 0;
        $now = now();

        DB::transaction(function () use ($rows, $userId, $batch, $now, &$created, &$skipped) {
            $todo = array_values(array_filter($rows, fn ($r) => $r['status'] === 'new'));
            foreach (array_chunk($todo, 500) as $chunk) {
                // Someone may have added the same approval since the preview: never create a second one.
                $exists = [];
                foreach ($chunk as $r) $exists[$r['level']][] = $r['id'];
                $have = [];
                foreach ($exists as $lvl => $ids) {
                    foreach (DB::table('entity_approvals')->whereNull('deleted_at')->where('entity_type', $lvl)->whereIn('entity_id', array_unique($ids))
                        ->get(['entity_id', 'accreditation_body_id', 'academic_year']) as $e) {
                        $have["$lvl|{$e->entity_id}|{$e->accreditation_body_id}|{$e->academic_year}"] = true;
                    }
                }
                $insert = [];
                foreach ($chunk as $r) {
                    $d = $r['data'];
                    if (isset($have["{$d['entity_type']}|{$d['entity_id']}|{$d['accreditation_body_id']}|{$d['academic_year']}"])) { $skipped++; continue; }
                    $insert[] = $d + ['verification_status' => 'unverified', 'import_batch' => $batch, 'created_by' => $userId, 'created_at' => $now, 'updated_at' => $now];
                }
                if ($insert) { DB::table('entity_approvals')->insert($insert); $created += count($insert); }
            }
            // One audit entry for the whole batch (not thousands of row entries).
            AuditLog::create(['auditable_type' => EntityApproval::class, 'auditable_id' => 0, 'user_id' => $userId, 'action' => 'import',
                'old_values' => null, 'new_values' => ['batch' => $batch, 'created' => $created, 'skipped_since_preview' => $skipped]]);
        });
        return ['batch' => $batch, 'created' => $created, 'skipped' => $skipped];
    }

    /** Takes a whole batch back out (soft delete). Returns [removed, wasVerified]. */
    public function undo(string $batch, ?int $userId): array
    {
        $q = DB::table('entity_approvals')->where('import_batch', $batch)->whereNull('deleted_at');
        $total = (clone $q)->count();
        $verified = (clone $q)->where('verification_status', 'verified')->count();
        if ($total === 0) return [0, 0];
        DB::transaction(function () use ($q, $batch, $total, $verified, $userId) {
            $q->update(['deleted_at' => now(), 'updated_at' => now()]);
            AuditLog::create(['auditable_type' => EntityApproval::class, 'auditable_id' => 0, 'user_id' => $userId, 'action' => 'import_undone',
                'old_values' => null, 'new_values' => ['batch' => $batch, 'removed' => $total, 'of_which_verified' => $verified]]);
        });
        return [$total, $verified];
    }

    public function batches(int $limit = 20): array
    {
        return DB::table('entity_approvals')->whereNotNull('import_batch')
            ->selectRaw("import_batch, MIN(created_at) AS at, MAX(created_by) AS by_user, COUNT(*) AS total, SUM(deleted_at IS NULL) AS active, SUM(deleted_at IS NULL AND verification_status = 'verified') AS verified")
            ->groupBy('import_batch')->orderByDesc('at')->limit($limit)->get()->all();
    }

    /** CSV lines for the template: pre-filled with a regulator's programmes that have no current approval, or a few example rows. */
    public function template(?AccreditationBody $body, ?string $year): \Generator
    {
        yield self::COLUMNS;
        $year = $year ?: $this->defaultYear();
        if (!$body) {
            yield ['course', '12345', 'Example University', 'Main Campus', 'MBBS', 'Medical', 'NMC', $year, '150', 'F.NMC/2025/77', '2025-07-01', '2026-06-30', 'active', 'https://example.com/letter.pdf', ''];
            yield ['campus', '00000000-0000-0000-0000-000000000000', 'Example University', 'Main Campus', '', '', 'AICTE', $year, '1200', '', '', '', 'active', '', 'Institution-level approval'];
            return;
        }
        $q = ApprovalCoverage::missing(ApprovalCoverage::scope($body), $body)
            ->join('organisations as o', 'o.id', '=', 'oc.organisation_id')->leftJoin('campuses as cp', 'cp.id', '=', 'oc.campus_id')
            ->leftJoin('disciplines as d', 'd.id', '=', DB::raw('COALESCE(oc.discipline_id, c.discipline_id)'))
            ->select('oc.id', 'o.name as organisation', 'cp.campus_name as campus', 'c.name as course', 'oc.academic_unit_name as unit', 'd.title as discipline')
            ->orderBy('o.name')->orderBy('oc.id');
        foreach ($q->cursor() as $r) {
            yield ['course', $r->id, $r->organisation, $r->campus, $r->unit ?: $r->course, $r->discipline, $body->short_name, $year, '', '', '', '', 'active', '', ''];
        }
    }

    public function defaultYear(): string
    {
        $y = now()->month >= 4 ? now()->year : now()->year - 1;   // Indian academic year starts around April-July
        return $y . '-' . substr((string) ($y + 1), -2);
    }

    /** Names for the rows that will be displayed. */
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

    private function column(string $h): string
    {
        $h = Str::snake(trim(preg_replace('/^\xEF\xBB\xBF/', '', $h)));
        return self::SYNONYMS[$h] ?? $h;
    }

    /** 2025-26, 2025-2026, 2025/26, 2025 - 26 => 2025-26. */
    private function year(string $v): ?string
    {
        $v = trim($v);
        if (!preg_match('/^(\d{4})\s*[-\/]\s*(\d{4}|\d{2})$/', $v, $m)) return null;
        $to = strlen($m[2]) === 4 ? (int) $m[2] : (int) $m[2];
        $next = (int) $m[1] + 1;
        if (strlen($m[2]) === 4 ? $to !== $next : $to !== $next % 100) return null;
        return $m[1] . '-' . substr((string) $next, -2);
    }

    /** @param bool|null $bad set true when a non-empty value could not be read as a date */
    private function date(?string $v, &$bad = null): ?string
    {
        $bad = false;
        $v = trim((string) $v);
        if ($v === '') return null;
        foreach (['Y-m-d', 'd-m-Y', 'd/m/Y', 'Y/m/d', 'd M Y', 'j-n-Y', 'j/n/Y'] as $f) {
            $d = \DateTime::createFromFormat('!' . $f, $v);
            if ($d && $d->format($f) === $v) return $d->format('Y-m-d');
        }
        $bad = true;
        return null;
    }

    private function fail(string $msg): array
    {
        return ['ok' => false, 'error' => $msg, 'rows' => [], 'summary' => []];
    }
}

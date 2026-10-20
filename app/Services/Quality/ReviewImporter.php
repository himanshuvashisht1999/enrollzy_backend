<?php

namespace App\Services\Quality;

use App\Models\ExamModule\AuditLog;
use App\Models\Quality\EntityReview;
use App\Support\Quality\Levels;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

/**
 * CSV import of individual reviews / ratings.
 *
 *  - preview() reads and checks every row with the same rules as the form and saves NOTHING.
 *  - apply() creates only the rows that were clean and new, under one batch id. It never changes an existing review.
 *  - undo() takes a whole batch back out (soft delete).
 *
 * Columns: id (required), rating (required, 1-5), level (default course), reviewer_name, reviewer_type, infrastructure, campus_life, academics,
 * placements, value_for_money, title, comment, reviewed_on, review_year, source, source_url, is_published, notes.
 * Everything lands UNVERIFIED. A review cannot be verified without a source, so rows without one get a warning.
 * A review that is already recorded (same entity, reviewer, rating, text and date) is skipped, so re-importing a file is harmless.
 */
class ReviewImporter
{
    public const MAX_ROWS = 20000;
    public const COLUMNS = ['level', 'id', 'rating', 'reviewer_name', 'reviewer_type', 'infrastructure', 'campus_life', 'academics', 'placements', 'value_for_money',
        'title', 'comment', 'reviewed_on', 'review_year', 'source', 'source_url', 'is_published', 'notes'];
    private const SUBS = ['infrastructure', 'campus_life', 'academics', 'placements', 'value_for_money'];
    private const TYPE_MAP = ['student' => 'student', 'students' => 'student', 'current student' => 'student', 'alumni' => 'alumni', 'alumnus' => 'alumni', 'alumna' => 'alumni', 'ex student' => 'alumni',
        'parent' => 'parent', 'parents' => 'parent', 'guardian' => 'parent', 'faculty' => 'faculty', 'teacher' => 'faculty', 'staff' => 'faculty', 'professor' => 'faculty',
        'recruiter' => 'recruiter', 'employer' => 'recruiter', 'other' => 'other'];
    private const SYNONYMS = ['programme_id' => 'id', 'course_id' => 'id', 'stars' => 'rating', 'overall' => 'rating', 'overall_rating' => 'rating', 'name' => 'reviewer_name', 'reviewer' => 'reviewer_name',
        'type' => 'reviewer_type', 'reviewer_role' => 'reviewer_type', 'infra' => 'infrastructure', 'campus' => 'campus_life', 'faculty_rating' => 'academics', 'placement' => 'placements',
        'placement_rating' => 'placements', 'value' => 'value_for_money', 'review' => 'comment', 'text' => 'comment', 'review_text' => 'comment', 'headline' => 'title', 'date' => 'reviewed_on',
        'review_date' => 'reviewed_on', 'year' => 'review_year', 'platform' => 'source', 'url' => 'source_url', 'link' => 'source_url', 'published' => 'is_published', 'visible' => 'is_published'];

    /** @return array{ok:bool, error?:string, rows:array, summary:array} */
    public function preview(string $path): array
    {
        $fh = fopen($path, 'r');
        if (!$fh) return $this->fail('The file could not be read.');
        $header = array_map(fn ($h) => $this->column($h), fgetcsv($fh) ?: []);
        $missing = array_values(array_diff(['id', 'rating'], $header));
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
                foreach (DB::table('entity_reviews')->whereNull('deleted_at')->where('entity_type', $lvl)->whereIn('entity_id', $chunk)
                    ->get(['entity_type', 'entity_id', 'reviewer_name', 'rating', 'title', 'comment', 'reviewed_on', 'review_year']) as $e) {
                    $existing[self::fingerprint($e->entity_type, $e->entity_id, $e->reviewer_name, (float) $e->rating, $e->title, $e->comment, $e->reviewed_on, $e->review_year)] = true;
                }
            }
        }

        $rows = []; $seen = [];
        foreach ($raw as $r) $rows[] = $this->check($r, $found, $existing, $seen);

        $sum = ['rows' => count($rows), 'new' => 0, 'exists' => 0, 'error' => 0, 'warnings' => 0, 'rating_total' => 0.0];
        foreach ($rows as $row) {
            $sum[$row['status']]++;
            if ($row['warnings']) $sum['warnings']++;
            if ($row['status'] === 'new') $sum['rating_total'] += $row['data']['rating'];
        }
        $sum['average'] = $sum['new'] ? round($sum['rating_total'] / $sum['new'], 2) : null;
        return ['ok' => true, 'rows' => $rows, 'summary' => $sum];
    }

    private function check(array $r, array $found, array $existing, array &$seen): array
    {
        $out = ['line' => $r['line'], 'level' => mb_strtolower(trim($r['level'] ?? '')) ?: 'course', 'id' => trim($r['id']), 'status' => 'new', 'errors' => [], 'warnings' => [], 'data' => []];
        $err = function (string $m) use (&$out) { $out['errors'][] = $m; };

        if (!Levels::valid($out['level'])) $err('Unknown level "' . $out['level'] . '" (use organisation, campus, department or course).');
        elseif ($out['id'] === '' || !isset($found[$out['level']][$out['id']])) $err('No ' . $out['level'] . ' with id "' . $out['id'] . '".');

        $rating = self::stars((string) ($r['rating'] ?? ''), $p);
        if ($rating === null) $err('Rating: ' . ($p ?? 'is required (1 to 5).'));
        $subs = [];
        foreach (self::SUBS as $f) {
            if (trim((string) ($r[$f] ?? '')) === '') { $subs[$f] = null; continue; }
            $subs[$f] = self::stars((string) $r[$f], $sp);
            if ($subs[$f] === null) $err(ucfirst(str_replace('_', ' ', $f)) . ': ' . $sp);
        }

        $rawType = mb_strtolower(trim(preg_replace('/\s+/', ' ', (string) ($r['reviewer_type'] ?? ''))));
        $type = $rawType === '' ? 'other' : (self::TYPE_MAP[$rawType] ?? null);
        if ($type === null) { $type = 'other'; $out['warnings'][] = "Reviewer type \"$rawType\" is not one of student, alumni, parent, faculty, recruiter; saved as \"other\"."; }

        $on = $this->date((string) ($r['reviewed_on'] ?? ''), $badDate);
        if ($badDate) $err('Date "' . trim($r['reviewed_on']) . '" is not a date (use 2025-06-18).');
        elseif ($on && $on > now()->toDateString()) $err('The review date is in the future.');
        $yearRaw = trim((string) ($r['review_year'] ?? ''));
        $year = null;
        if ($yearRaw !== '') {
            if (!ctype_digit($yearRaw) || (int) $yearRaw < 1990 || (int) $yearRaw > (int) now()->year) $err("Year \"$yearRaw\" must be between 1990 and " . now()->year . '.');
            else $year = (int) $yearRaw;
        }
        if ($on) $year = (int) substr($on, 0, 4);

        $pub = $this->bool((string) ($r['is_published'] ?? ''), $badBool);
        if ($badBool) $err('Published "' . trim($r['is_published']) . '" must be yes or no.');

        $strings = ['reviewer_name' => 150, 'title' => 255, 'source' => 100];
        foreach ($strings as $f => $max) if (mb_strlen(trim((string) ($r[$f] ?? ''))) > $max) $err(ucfirst(str_replace('_', ' ', $f)) . " is longer than $max characters.");
        $comment = trim((string) ($r['comment'] ?? ''));
        if (mb_strlen($comment) > 5000) $err('The review text is longer than 5000 characters.');
        $url = trim((string) ($r['source_url'] ?? ''));
        if ($url !== '' && (!filter_var($url, FILTER_VALIDATE_URL) || strlen($url) > 500)) $err('Source link "' . mb_substr($url, 0, 60) . '" is not a valid URL.');
        $source = trim((string) ($r['source'] ?? ''));
        $title = trim((string) ($r['title'] ?? ''));
        $name = trim((string) ($r['reviewer_name'] ?? ''));

        if ($comment === '' && $title === '' && !$out['errors']) $out['warnings'][] = 'A rating with no text.';
        if ($source === '' && $url === '' && !$out['errors']) $out['warnings'][] = 'No source or link: this review cannot be verified until one is added.';

        if (!$out['errors']) {
            $fp = self::fingerprint($out['level'], $out['id'], $name, $rating, $title, $comment, $on, $year);
            if (isset($existing[$fp])) { $out['status'] = 'exists'; $out['errors'][] = 'This review is already recorded. Left as it is.'; }
            elseif (isset($seen[$fp])) $err('Appears twice in this file (first on line ' . $seen[$fp] . ').');
            else $seen[$fp] = $r['line'];
        }
        if ($out['errors'] && $out['status'] !== 'exists') $out['status'] = 'error';

        if ($out['status'] === 'new') {
            $out['data'] = ['entity_type' => $out['level'], 'entity_id' => $out['id'], 'reviewer_name' => $name ?: null, 'reviewer_type' => $type, 'rating' => $rating] + $subs
                + ['title' => $title ?: null, 'comment' => $comment ?: null, 'reviewed_on' => $on, 'review_year' => $year, 'source' => $source ?: null, 'source_url' => $url ?: null,
                    'is_published' => $pub ? 1 : 0, 'notes' => trim((string) ($r['notes'] ?? '')) ?: null];
        }
        return $out;
    }

    /** Creates the clean new rows under one batch. */
    public function apply(array $rows, ?int $userId): array
    {
        $batch = 'rv-' . now()->format('ymd-His') . '-' . Str::lower(Str::random(4));
        $created = 0; $skipped = 0; $now = now();

        DB::transaction(function () use ($rows, $userId, $batch, $now, &$created, &$skipped) {
            foreach (array_chunk(array_values(array_filter($rows, fn ($r) => $r['status'] === 'new')), 500) as $chunk) {
                // Someone may have added the same review since the preview: never create a second one.
                $have = [];
                foreach (collect($chunk)->groupBy('level') as $lvl => $group) {
                    foreach (DB::table('entity_reviews')->whereNull('deleted_at')->where('entity_type', $lvl)->whereIn('entity_id', $group->pluck('id')->unique()->all())
                        ->get(['entity_type', 'entity_id', 'reviewer_name', 'rating', 'title', 'comment', 'reviewed_on', 'review_year']) as $e) {
                        $have[self::fingerprint($e->entity_type, $e->entity_id, $e->reviewer_name, (float) $e->rating, $e->title, $e->comment, $e->reviewed_on, $e->review_year)] = true;
                    }
                }
                $insert = [];
                foreach ($chunk as $r) {
                    $d = $r['data'];
                    if (isset($have[self::fingerprint($d['entity_type'], $d['entity_id'], $d['reviewer_name'], $d['rating'], $d['title'], $d['comment'], $d['reviewed_on'], $d['review_year'])])) { $skipped++; continue; }
                    $insert[] = $d + ['verification_status' => 'unverified', 'import_batch' => $batch, 'created_by' => $userId, 'created_at' => $now, 'updated_at' => $now];
                }
                if ($insert) { DB::table('entity_reviews')->insert($insert); $created += count($insert); }
            }
            AuditLog::create(['auditable_type' => EntityReview::class, 'auditable_id' => 0, 'user_id' => $userId, 'action' => 'import',
                'old_values' => null, 'new_values' => ['batch' => $batch, 'created' => $created, 'skipped_since_preview' => $skipped]]);
        });
        return ['batch' => $batch, 'created' => $created, 'skipped' => $skipped];
    }

    /** @return array{0:int,1:int} removed, of which verified */
    public function undo(string $batch, ?int $userId): array
    {
        $q = DB::table('entity_reviews')->where('import_batch', $batch)->whereNull('deleted_at');
        $total = (clone $q)->count();
        $verified = (clone $q)->where('verification_status', 'verified')->count();
        if ($total === 0) return [0, 0];
        DB::transaction(function () use ($q, $batch, $total, $verified, $userId) {
            $q->update(['deleted_at' => now(), 'updated_at' => now()]);
            AuditLog::create(['auditable_type' => EntityReview::class, 'auditable_id' => 0, 'user_id' => $userId, 'action' => 'import_undone',
                'old_values' => null, 'new_values' => ['batch' => $batch, 'removed' => $total, 'of_which_verified' => $verified]]);
        });
        return [$total, $verified];
    }

    public function batches(int $limit = 20): array
    {
        return DB::table('entity_reviews')->whereNotNull('import_batch')
            ->selectRaw("import_batch, MIN(created_at) AS at, MAX(created_by) AS by_user, COUNT(*) AS total, SUM(deleted_at IS NULL) AS active, SUM(deleted_at IS NULL AND verification_status = 'verified') AS verified")
            ->groupBy('import_batch')->orderByDesc('at')->limit($limit)->get()->all();
    }

    /** A blank sheet with two example rows. */
    public function template(): \Generator
    {
        yield self::COLUMNS;
        yield ['course', '12345', '4.5', 'A. Sharma', 'student', '4', '4', '5', '4.5', '4', 'Great faculty', 'The labs and mentors were excellent.', '2025-06-18', '', 'Google', 'https://example.com/review/1', 'yes', ''];
        yield ['campus', '00000000-0000-0000-0000-000000000000', '5', 'Parent of a student', 'parent', '', '', '', '', '', 'Caring staff', '', '', '2025', 'School website', '', 'yes', ''];
    }

    /** CSV lines listing the ids a reviewer can paste: the organisation, its campuses, departments and programmes. */
    public function idList(int $organisationId): \Generator
    {
        yield ['level', 'id', 'organisation', 'campus', 'name'];
        $org = DB::table('organisations')->where('id', $organisationId)->first(['id', 'name']);
        if (!$org) return;
        yield ['organisation', $org->id, $org->name, '', $org->name];
        $campuses = DB::table('campuses')->where('organisation_id', $org->id)->whereNull('deleted_at')->orderBy('campus_name')->get(['id', 'campus_name']);
        $campusName = $campuses->pluck('campus_name', 'id');
        foreach ($campuses as $c) yield ['campus', $c->id, $org->name, $c->campus_name, $c->campus_name];
        foreach (DB::table('departments')->where('organisation_id', $org->id)->whereNull('deleted_at')->orderBy('department_name')->get(['id', 'department_name', 'campus_id']) as $d) {
            yield ['department', $d->id, $org->name, $campusName[$d->campus_id] ?? '', $d->department_name];
        }
        $q = DB::table('organisation_courses as oc')->leftJoin('courses as c', 'c.id', '=', 'oc.course_id')->where('oc.organisation_id', $org->id)->orderBy('oc.campus_id')->orderBy('oc.id')
            ->select('oc.id', 'oc.campus_id', 'c.name as course', 'oc.academic_unit_name as unit');
        foreach ($q->cursor() as $p) yield ['course', $p->id, $org->name, $campusName[$p->campus_id] ?? '', $p->unit ?: $p->course];
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

    /** "4", "4.5", "4,5", "4/5", "4.5 / 5", "4 stars" => 4.0 / 4.5. One decimal at most; 1 to 5. */
    public static function stars(string $v, &$problem = null): ?float
    {
        $problem = null;
        $s = mb_strtolower(trim($v));
        if ($s === '') { $problem = 'is required (1 to 5).'; return null; }
        $s = trim(preg_replace('/\s*(stars?|\x{2605}+)\s*$/u', '', $s));
        if (preg_match('/^\d+,\d$/', $s)) $s = str_replace(',', '.', $s);
        if (preg_match('/^(\d+(?:\.\d+)?)\s*\/\s*(\d+)$/', $s, $m)) {
            if ((int) $m[2] !== 5) { $problem = "\"$v\" is out of $m[2]; give it out of 5."; return null; }
            $s = $m[1];
        }
        if (!preg_match('/^\d(?:\.\d)?$/', $s)) { $problem = "\"$v\" is not a rating from 1 to 5 (one decimal at most)."; return null; }
        $n = (float) $s;
        if ($n < 1 || $n > 5) { $problem = "\"$v\" must be between 1 and 5."; return null; }
        return $n;
    }

    /** The same review, however it is spelled: used to skip reviews that are already recorded. */
    public static function fingerprint($type, $id, ?string $name, float $rating, ?string $title, ?string $comment, $on, $year): string
    {
        $norm = fn (?string $s) => EntityReview::normalise($s);
        return implode('|', [$type, $id, $norm($name), number_format($rating, 1), $norm($title), $norm($comment), $on ? substr((string) $on, 0, 10) : '', $year ?: '']);
    }

    private function bool(string $v, &$bad = null): bool
    {
        $bad = false;
        $v = mb_strtolower(trim($v));
        if ($v === '' || in_array($v, ['1', 'yes', 'y', 'true', 'published', 'show', 'visible'], true)) return true;
        if (in_array($v, ['0', 'no', 'n', 'false', 'hidden', 'unpublished', 'hide'], true)) return false;
        $bad = true;
        return true;
    }

    private function date(string $v, &$bad = null): ?string
    {
        $bad = false;
        $v = trim($v);
        if ($v === '') return null;
        foreach (['Y-m-d', 'd-m-Y', 'd/m/Y', 'Y/m/d', 'd M Y', 'j M Y', 'j-n-Y', 'j/n/Y'] as $f) {
            $d = \DateTime::createFromFormat('!' . $f, $v);
            if ($d && $d->format($f) === $v) return $d->format('Y-m-d');
        }
        $bad = true;
        return null;
    }

    private function column(string $h): string
    {
        $h = Str::snake(trim(preg_replace('/^\xEF\xBB\xBF/', '', $h)));
        return self::SYNONYMS[$h] ?? $h;
    }

    private function fail(string $msg): array
    {
        return ['ok' => false, 'error' => $msg, 'rows' => [], 'summary' => []];
    }
}

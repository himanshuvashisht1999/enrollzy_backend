<?php

namespace App\Services\Quality;

use Illuminate\Support\Facades\DB;

/**
 * Moves the real data out of the old typed places into the raw record tables, without guessing:
 *  - campuses.reviews (JSON list of individual reviews)          => entity_reviews at campus level
 *  - departments.placement_statistics overall_* (org-wide figures repeated in every department) => ONE entity_placements row per organisation and year
 * Everything arrives UNVERIFIED. Safe to re-run: a row is created once per source value.
 *
 * Deliberately NOT migrated (no source, no count, or not an individual review) - reported instead, left where they are:
 *  organisations.average_rating / total_reviews, organisation_courses.rating / roi, departments.college_reviews (sub-rating summaries), is_top.
 */
class LegacyMetrics
{
    private const TYPE_MAP = ['parent' => 'parent', 'student' => 'student', 'alumni' => 'alumni', 'alumnus' => 'alumni', 'faculty' => 'faculty', 'teacher' => 'faculty', 'recruiter' => 'recruiter'];

    /** @return array{counts:array<string,int>, warnings:string[], left_alone:array<string,int>} */
    public function run(bool $apply): array
    {
        $counts = ['reviews' => 0, 'reviews already copied' => 0, 'reviews skipped' => 0, 'placement rows' => 0, 'placement rows already copied' => 0, 'placement rows skipped' => 0];
        $warnings = [];
        $now = now();

        // ---- campus reviews ----
        $have = DB::table('entity_reviews')->where('entity_type', 'campus')->whereNotNull('legacy_source')->get(['entity_id', 'legacy_source'])
            ->map(fn ($r) => $r->entity_id . '|' . $r->legacy_source)->flip();
        $reviewRows = [];
        foreach (DB::table('campuses')->whereNotNull('reviews')->where('reviews', '!=', '')->where('reviews', '!=', '[]')->get(['id', 'campus_name', 'reviews']) as $c) {
            foreach (json_decode($c->reviews, true) ?: [] as $r) {
                if (!is_array($r)) continue;
                $rating = $r['rating'] ?? null;
                if (!is_numeric($rating) || $rating < 1 || $rating > 5) {
                    $counts['reviews skipped']++;
                    $warnings[] = "Campus \"{$c->campus_name}\": a review without a usable 1-5 rating was skipped.";
                    continue;
                }
                $key = 'campuses.reviews:' . substr(md5(json_encode($r)), 0, 12);
                if (isset($have[$c->id . '|' . $key])) { $counts['reviews already copied']++; continue; }
                [$on, $year] = $this->date($r['date'] ?? null);
                $reviewRows[] = ['entity_type' => 'campus', 'entity_id' => $c->id, 'reviewer_name' => $this->s($r['reviewer_name'] ?? null, 150),
                    'reviewer_type' => self::TYPE_MAP[mb_strtolower(trim((string) ($r['reviewer_type'] ?? '')))] ?? 'other', 'rating' => round((float) $rating, 1),
                    'title' => $this->s($r['title'] ?? null, 255), 'comment' => $this->s($r['comment'] ?? null), 'reviewed_on' => $on, 'review_year' => $year,
                    'is_published' => 1, 'verification_status' => 'unverified', 'legacy_source' => $key,
                    'notes' => 'Migrated from the old campus reviews list. Where the review was collected is unknown.', 'created_at' => $now, 'updated_at' => $now];
                $counts['reviews']++;
            }
        }

        // ---- org-wide placement figures repeated on departments ----
        $havePl = DB::table('entity_placements')->where('entity_type', 'organisation')->whereNotNull('legacy_source')->get(['entity_id', 'legacy_source'])
            ->map(fn ($r) => $r->entity_id . '|' . $r->legacy_source)->flip();
        $groups = [];
        DB::table('departments')->whereNotNull('placement_statistics')->where('placement_statistics', '!=', '[]')->orderBy('organisation_id')->select('organisation_id', 'placement_statistics')
            ->chunk(500, function ($depts) use (&$groups) {
                foreach ($depts as $d) {
                    foreach (json_decode($d->placement_statistics, true) ?: [] as $r) {
                        $vals = [$this->n($r['overall_students_placed'] ?? null), $this->n($r['overall_graduating_students'] ?? null), $this->n($r['overall_higher_studies'] ?? null), $this->n($r['overall_median_salary'] ?? null)];
                        if (count(array_filter($vals, fn ($v) => $v !== null)) === 0) continue;
                        $groups[$d->organisation_id][(string) ($r['year'] ?? '')][json_encode($vals)] = $vals;
                    }
                }
            });
        $plRows = [];
        foreach ($groups as $orgId => $years) {
            foreach ($years as $year => $variants) {
                $name = DB::table('organisations')->where('id', $orgId)->value('name');
                if (count($variants) > 1) {
                    $counts['placement rows skipped']++;
                    $warnings[] = "Organisation \"$name\" ($year): its departments disagree on the organisation-wide figures, so none were copied. Enter the real figures per programme.";
                    continue;
                }
                $key = 'departments.placement_statistics.overall:' . ($year === '' ? 'noyear' : $year);
                if (isset($havePl[$orgId . '|' . $key])) { $counts['placement rows already copied']++; continue; }
                [$placed, $grad, $higher, $median] = array_values($variants)[0];
                $note = 'Migrated from the figures repeated on every department. ' . ($year !== '' ? "The original year value was \"$year\" - it is not clear which academic year that means, so set it before verifying. " : 'No year was recorded. ')
                    . 'Reported by the institution for the whole organisation, not per programme.';
                if ($placed !== null && $grad !== null && $placed > $grad) $note .= " CHECK: more students placed ($placed) than graduating ($grad).";
                $plRows[] = ['entity_type' => 'organisation', 'entity_id' => (string) $orgId, 'academic_year' => null, 'students_graduating' => $grad, 'students_placed' => $placed,
                    'students_higher_studies' => $higher, 'median_salary' => $median, 'placement_source' => 'Old department placement statistics (organisation-wide)',
                    'verification_status' => 'unverified', 'legacy_source' => $key, 'notes' => $note, 'created_at' => $now, 'updated_at' => $now];
                $counts['placement rows']++;
            }
        }

        if ($apply) {
            DB::transaction(function () use ($reviewRows, $plRows) {
                foreach (array_chunk($reviewRows, 200) as $c) DB::table('entity_reviews')->insert($this->uniform($c));
                foreach (array_chunk($plRows, 200) as $c) DB::table('entity_placements')->insert($this->uniform($c));
            });
        }

        return ['counts' => $counts, 'warnings' => $warnings, 'left_alone' => $this->leftAlone()];
    }

    /** Typed values that have no source or count behind them. They stay as they are; the admin sees them labelled "unsourced". */
    public function leftAlone(): array
    {
        return [
            'organisations with a typed average rating / review count' => DB::table('organisations')->whereNotNull('average_rating')->count(),
            'programmes with a typed rating' => DB::table('organisation_courses')->whereNotNull('rating')->count(),
            'programmes with a typed ROI' => DB::table('organisation_courses')->whereNotNull('roi')->count(),
            'organisations flagged "top" by hand' => DB::table('organisations')->where('is_top', 1)->count(),
        ];
    }

    /** @return array{0:?string,1:?int} reviewed_on, review_year */
    private function date($v): array
    {
        $v = trim((string) $v);
        if ($v === '') return [null, null];
        if (preg_match('/^\d{4}$/', $v)) return [null, (int) $v];
        foreach (['Y-m-d', 'd M Y', 'j M Y', 'd-m-Y', 'd/m/Y'] as $f) {
            $d = \DateTime::createFromFormat('!' . $f, $v);
            if ($d && $d->format($f) === $v) return [$d->format('Y-m-d'), (int) $d->format('Y')];
        }
        return [null, null];
    }

    private function n($v): ?int
    {
        $v = preg_replace('/[^\d]/', '', (string) $v);
        return $v === '' ? null : (int) $v;
    }

    private function s($v, ?int $max = null): ?string
    {
        $v = trim((string) $v);
        if ($v === '') return null;
        return $max ? mb_substr($v, 0, $max) : $v;
    }

    private function uniform(array $rows): array
    {
        $keys = array_unique(array_merge(...array_map('array_keys', $rows)));
        return array_map(fn ($r) => array_merge(array_fill_keys($keys, null), $r), $rows);
    }
}

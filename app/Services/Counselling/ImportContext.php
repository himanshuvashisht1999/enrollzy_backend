<?php

namespace App\Services\Counselling;

use App\Models\Counselling\Alias;
use App\Models\Counselling\ImportBatch;
use Illuminate\Support\Facades\DB;

/**
 * Everything the importer needs to match one batch, loaded once: the college/course/quota/category/exam
 * masters, the authority's institute codes, learned aliases, and the rows that already exist
 * (for duplicate detection in append mode).
 */
class ImportContext
{
    private array $exact = [];      // type => [normalised name => [ids]]
    private array $names = [];      // type => [id => display name]
    private array $aliases = [];    // type => [normalised alias => id]
    private array $codes = [];      // upper-case authority code => organisation id
    private array $existing = [];   // identity key => true
    private array $cache = [];
    private ?int $soleExam = null;
    private string $type;

    private const FUZZY_MIN = 0.85;
    private const FUZZY_GAP = 0.03;

    public function __construct(ImportBatch $batch)
    {
        $this->type = $batch->type;
        $counselling = $batch->cycle->counselling;

        $this->load('organisation', DB::table('organisations')->get(['id', 'name']), ['name']);
        $this->load('course', DB::table('courses')->get(['id', 'name', 'full_form']), ['name', 'full_form']);
        $this->load('quota', DB::table('cns_quotas')->get(['id', 'name', 'short_name']), ['name', 'short_name']);
        $this->load('category', DB::table('cns_categories')->get(['id', 'name', 'short_name', 'slug']), ['name', 'short_name', 'slug']);

        $examIds = DB::table('cns_counselling_exams')->where('counselling_id', $counselling->id)->pluck('exam_id');
        $exams = DB::table('exm_exams')->whereIn('id', $examIds)->whereNull('deleted_at')->get(['id', 'name', 'short_name', 'aliases']);
        $this->load('exam', $exams, ['name', 'short_name']);
        foreach ($exams as $e) {
            foreach (json_decode($e->aliases ?? '[]', true) ?: [] as $alias) {
                $this->addExact('exam', $e->id, $alias);
            }
        }
        $this->soleExam = $exams->count() === 1 ? (int) $exams->first()->id : null;

        foreach (DB::table('cns_institute_codes')->where('conducting_body_id', $counselling->conducting_body_id)->get(['code', 'organisation_id']) as $c) {
            $this->codes[strtoupper(trim($c->code))] = (int) $c->organisation_id;
        }

        foreach (DB::table('cns_aliases')->get(['type', 'alias_norm', 'target_id']) as $a) {
            if (isset($this->names[$a->type][$a->target_id])) {          // ignore aliases whose target was deleted
                $this->aliases[$a->type][$a->alias_norm] = (int) $a->target_id;
            }
        }

        // Existing rows matter only when appending: replace mode removes them first.
        if ($batch->mode === 'append') {
            $this->loadExisting($batch);
        }
    }

    private function load(string $type, $rows, array $columns): void
    {
        foreach ($rows as $row) {
            $this->names[$type][(int) $row->id] = $row->name;
            foreach ($columns as $col) {
                $this->addExact($type, (int) $row->id, $row->$col ?? null);
            }
        }
    }

    private function addExact(string $type, int $id, ?string $value): void
    {
        $norm = Alias::normalize($value);
        if ($norm !== '' && !in_array($id, $this->exact[$type][$norm] ?? [], true)) {
            $this->exact[$type][$norm][] = $id;
        }
    }

    private function loadExisting(ImportBatch $batch): void
    {
        if ($batch->type === 'participants') {
            foreach (DB::table('cns_participants')->where('cycle_id', $batch->cycle_id)->get(['organisation_id', 'course_id', 'quota_id']) as $r) {
                $this->existing[implode('|', [$r->organisation_id, $r->course_id, $r->quota_id])] = true;
            }
        } elseif ($batch->type === 'seats') {
            $live = DB::table('cns_seat_matrix_versions')->where('round_id', $batch->round_id)->where('is_live', true)->value('id');
            if ($live) {
                foreach (DB::table('cns_seat_matrix_rows')->where('version_id', $live)->cursor() as $r) {
                    $this->existing[implode('|', [$r->organisation_id, $r->course_id, $r->quota_id, $r->category_id, $r->gender ?? '*', (int) $r->pwd])] = true;
                }
            }
        } else {
            foreach (DB::table('cns_cutoffs')->where('round_id', $batch->round_id)->cursor() as $r) {
                $this->existing[implode('|', [$r->exam_id, $r->organisation_id, $r->course_id, $r->quota_id, $r->category_id, $r->gender ?? '*', (int) $r->pwd, $r->rank_type])] = true;
            }
        }
    }

    public function orgByCode(string $code): ?int
    {
        return $this->codes[strtoupper(trim($code))] ?? null;
    }

    public function soleExam(): ?int
    {
        return $this->soleExam;
    }

    public function exists(string $key): bool
    {
        return isset($this->existing[$key]);
    }

    public function existsMessage(): string
    {
        return match ($this->type) {
            'participants' => 'This college, course and quota is already a participant. Use "Replace" to change it.',
            'seats' => 'These seats are already in the live seat matrix. Use "Replace" to change them.',
            default => 'This cutoff already exists for the round. Use "Replace" to change it.',
        };
    }

    /**
     * Match one name: exact -> learned alias -> fuzzy suggestion (needs confirmation) -> no match.
     * @return array{state: string, id: ?int, name: ?string, score: ?int, msg: ?string}
     */
    public function match(string $type, string $raw): array
    {
        $norm = Alias::normalize($raw);
        return $this->cache[$type][$norm] ??= $this->resolve($type, $norm);
    }

    private function resolve(string $type, string $norm): array
    {
        $exact = $this->exact[$type][$norm] ?? [];
        if (count($exact) === 1) {
            return $this->hit($type, $exact[0]);
        }
        if (isset($this->aliases[$type][$norm])) {
            return $this->hit($type, $this->aliases[$type][$norm]);
        }
        if (count($exact) > 1) {
            $list = implode('; ', array_map(fn ($id) => $this->names[$type][$id] . " (#$id)", array_slice($exact, 0, 4)));
            return $this->miss("matches " . count($exact) . " records ($list). Choose the right one to continue.");
        }
        if ($suggestion = $this->fuzzy($type, $norm)) {
            return ['state' => 'warning', 'id' => $suggestion[0], 'name' => $this->names[$type][$suggestion[0]], 'score' => (int) round($suggestion[1] * 100), 'msg' => null];
        }
        return $this->miss('no match in the ' . $type . ' master. Pick the right one to teach the importer, or add it to the master first.');
    }

    private function hit(string $type, int $id): array
    {
        return ['state' => 'matched', 'id' => $id, 'name' => $this->names[$type][$id] ?? null, 'score' => 100, 'msg' => null];
    }

    private function miss(string $msg): array
    {
        return ['state' => 'error', 'id' => null, 'name' => null, 'score' => null, 'msg' => $msg];
    }

    /** Closest name by edit distance, accepted only when clearly better than the runner-up. */
    private function fuzzy(string $type, string $norm): ?array
    {
        if (mb_strlen($norm) < 5) {
            return null;
        }
        $a = mb_substr($norm, 0, 250);
        $best = [null, 0.0];
        $second = 0.0;
        foreach ($this->exact[$type] ?? [] as $candidate => $ids) {
            $b = mb_substr($candidate, 0, 250);
            $max = max(strlen($a), strlen($b));
            if (abs(strlen($a) - strlen($b)) > 0.2 * $max) {
                continue;
            }
            $score = 1 - levenshtein($a, $b) / $max;
            if ($score > $best[1]) {
                $second = $best[1];
                $best = [$ids, $score];
            } elseif ($score > $second) {
                $second = $score;
            }
        }
        if ($best[0] === null || $best[1] < self::FUZZY_MIN || count($best[0]) !== 1 || $best[1] - $second < self::FUZZY_GAP) {
            return null;
        }
        return [$best[0][0], $best[1]];
    }
}

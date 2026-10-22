<?php

namespace App\Services\Counselling;

use App\Models\Counselling\Alias;
use App\Models\Counselling\Category;
use App\Models\Counselling\Cutoff;
use App\Models\Counselling\ImportBatch;
use App\Models\Counselling\ImportRow;
use App\Models\Counselling\Quota;
use App\Models\Counselling\SeatMatrixVersion;
use App\Models\ExamModule\Exam;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

/**
 * CSV import for participating institutes, seat matrix and cutoffs (spec section 12).
 *
 * Flow: upload -> every row is matched against the masters -> preview (matched / warning / error)
 * -> fix names (which teaches an alias) -> confirm saves only the matched rows under one batch -> undo.
 * Colleges and courses are NEVER created here: a missing one stays an error until it exists in its master.
 */
class Importer
{
    public const MAX_ROWS = 50000;
    public const MAX_BYTES = 20 * 1024 * 1024;

    public const DEFS = [
        'participants' => [
            'columns' => ['institute_code', 'organisation', 'course', 'quota', 'institute_type', 'annual_tuition_fee', 'other_fees', 'bond_details', 'total_seats'],
            'required' => ['course', 'quota'],
            'example' => ['MC001', 'Example Medical College', 'MBBS', 'All India', 'government', '150000', '20000', 'Service bond 1 year', '150'],
        ],
        'seats' => [
            'columns' => ['institute_code', 'organisation', 'course', 'quota', 'category', 'gender', 'pwd', 'seats'],
            'required' => ['course', 'quota', 'category', 'seats'],
            'example' => ['MC001', 'Example Medical College', 'MBBS', 'All India', 'OBC-NCL', '', 'no', '12'],
        ],
        'cutoffs' => [
            'columns' => ['exam', 'institute_code', 'organisation', 'course', 'quota', 'category', 'gender', 'pwd', 'rank_type', 'opening_value', 'closing_value'],
            'required' => ['course', 'quota', 'category', 'rank_type', 'closing_value'],
            'example' => ['NEET-UG', 'MC001', 'Example Medical College', 'MBBS', 'All India', 'General', '', 'no', 'air', '1200', '2140'],
        ],
    ];

    /** Header synonyms people actually use. */
    private const HEADERS = [
        'college' => 'organisation', 'college_name' => 'organisation', 'institute' => 'organisation', 'institute_name' => 'organisation',
        'organization' => 'organisation', 'organisation_name' => 'organisation', 'university' => 'organisation',
        'code' => 'institute_code', 'college_code' => 'institute_code', 'inst_code' => 'institute_code', 'institute_id' => 'institute_code',
        'program' => 'course', 'programme' => 'course', 'course_name' => 'course', 'branch' => 'course',
        'cat' => 'category', 'seat_category' => 'category',
        'opening' => 'opening_value', 'opening_rank' => 'opening_value', 'closing' => 'closing_value', 'closing_rank' => 'closing_value',
        'rank' => 'closing_value', 'seat' => 'seats', 'no_of_seats' => 'seats', 'total_seat' => 'total_seats', 'tuition_fee' => 'annual_tuition_fee',
        'type' => 'institute_type', 'rank_type' => 'rank_type', 'ranktype' => 'rank_type', 'exam_name' => 'exam', 'gender_quota' => 'gender',
    ];

    // ------------------------------------------------------------------ templates & parsing

    public function template(string $type): string
    {
        $def = self::DEFS[$type] ?? abort(404);
        return $this->csvLine($def['columns']) . $this->csvLine($def['example']);
    }

    /** @return array{0: array<int,string>, 1: array<int,array<string,string>>} [headers, rows keyed by header] */
    public function parse(string $path): array
    {
        $text = file_get_contents($path);
        if (str_starts_with($text, "\xEF\xBB\xBF")) {
            $text = substr($text, 3);
        }
        if (!mb_check_encoding($text, 'UTF-8')) {
            $text = mb_convert_encoding($text, 'UTF-8', 'Windows-1252');   // Excel "CSV" files
        }
        $firstLine = strtok($text, "\r\n");
        $delimiter = collect([',', ';', "\t"])->sortByDesc(fn ($d) => substr_count($firstLine, $d))->first();

        $stream = fopen('php://temp', 'r+');
        fwrite($stream, $text);
        rewind($stream);

        $headers = null;
        $rows = [];
        while (($cells = fgetcsv($stream, 0, $delimiter)) !== false) {
            if ($cells === [null] || count(array_filter($cells, fn ($c) => trim((string) $c) !== '')) === 0) {
                continue;
            }
            if ($headers === null) {
                $headers = array_map(fn ($h) => $this->normalizeHeader($h), $cells);
                continue;
            }
            if (count($rows) >= self::MAX_ROWS) {
                throw new \RuntimeException('The file has more than ' . number_format(self::MAX_ROWS) . ' rows. Split it into smaller files.');
            }
            $row = [];
            foreach ($headers as $i => $h) {
                if ($h !== '' && !array_key_exists($h, $row)) {
                    $row[$h] = trim((string) ($cells[$i] ?? ''));
                }
            }
            $rows[] = $row;
        }
        fclose($stream);

        return [array_values(array_filter($headers ?? [])), $rows];
    }

    private function normalizeHeader(?string $h): string
    {
        $h = strtolower(trim((string) $h));
        $h = preg_replace('/[^a-z0-9]+/', '_', $h);
        $h = trim($h, '_');
        return self::HEADERS[$h] ?? $h;
    }

    private function csvLine(array $cells): string
    {
        return implode(',', array_map(fn ($c) => '"' . str_replace('"', '""', (string) $c) . '"', $cells)) . "\r\n";
    }

    // ------------------------------------------------------------------ batch lifecycle

    public function createBatch(string $type, \App\Models\Counselling\CounsellingCycle $cycle, ?\App\Models\Counselling\Round $round, array $options, UploadedFile $file): ImportBatch
    {
        $def = self::DEFS[$type] ?? abort(404);
        [$headers, $rows] = $this->parse($file->getRealPath());

        $missing = array_values(array_diff($def['required'], $headers));
        if (!in_array('organisation', $headers) && !in_array('institute_code', $headers)) {
            $missing[] = 'organisation (or institute_code)';
        }
        if ($missing) {
            throw new \InvalidArgumentException('The file is missing the column(s): ' . implode(', ', $missing) . '. Download the template to see the expected headers.');
        }
        if (!$rows) {
            throw new \InvalidArgumentException('The file has no data rows.');
        }

        $path = $file->storeAs('cns/imports', now()->format('Ymd_His') . '_' . bin2hex(random_bytes(3)) . '.csv', 'public');

        return DB::transaction(function () use ($type, $cycle, $round, $options, $file, $path, $rows) {
            $batch = ImportBatch::create([
                'type' => $type, 'cycle_id' => $cycle->id, 'round_id' => $round?->id,
                'mode' => $options['mode'] ?? 'append', 'make_live' => (bool) ($options['make_live'] ?? true),
                'label' => $options['label'] ?? null, 'published_on' => $options['published_on'] ?? null, 'source_url' => $options['source_url'] ?? null,
                'file_name' => $file->getClientOriginalName(), 'file_path' => $path, 'status' => 'previewed',
                'total_rows' => count($rows), 'created_by' => auth('admin')->id(),
            ]);
            foreach (array_chunk($rows, 500, true) as $chunk) {
                $insert = [];
                foreach ($chunk as $i => $raw) {
                    $insert[] = ['batch_id' => $batch->id, 'row_no' => $i + 2, 'raw' => json_encode($raw), 'resolved' => null, 'status' => 'error', 'messages' => null];
                }
                DB::table('cns_import_rows')->insert($insert);
            }
            $this->process($batch);
            return $batch->fresh();
        });
    }

    /** (Re)match every row of the batch from its raw values: used after upload and after each fix. */
    public function process(ImportBatch $batch): void
    {
        $ctx = new ImportContext($batch);
        $seen = [];    // fully matched key => first row with it
        $maybe = [];   // key including suggested (not yet confirmed) matches => first row with it
        $counts = ['matched' => 0, 'warning' => 0, 'error' => 0];

        foreach (ImportRow::where('batch_id', $batch->id)->orderBy('row_no')->cursor() as $row) {
            [$resolved, $errors, $warnings, $exact, $provisional] = $this->resolveRow($batch->type, $row->raw, $ctx);

            // Duplicates: inside the file and against saved rows. Checked on every pass (after each fix or accepted
            // match), only on keys whose college, course, quota... are all known, so two different unmatched names never
            // look alike. A row still waiting for a confirmation is checked with its suggested match, so the admin is
            // warned BEFORE accepting a suggestion that would turn the row into a duplicate.
            if (!$errors) {
                if ($exact !== null) {
                    if (isset($seen[$exact])) {
                        $errors[] = 'Duplicate of row ' . $seen[$exact] . ' in this file.';
                    } elseif ($ctx->exists($exact)) {
                        $errors[] = $ctx->existsMessage();
                    } elseif (isset($maybe[$exact])) {
                        $warnings[] = 'Row ' . $maybe[$exact] . ' becomes the same as this row if its suggested match is accepted.';
                    }
                    $seen[$exact] ??= $row->row_no;
                    $maybe[$exact] ??= $row->row_no;
                } elseif ($provisional !== null) {
                    if (isset($maybe[$provisional])) {
                        $warnings[] = 'Accepting the suggested match makes this row a duplicate of row ' . $maybe[$provisional] . '. Pick a different match, or remove one of the rows.';
                    } elseif ($ctx->exists($provisional)) {
                        $warnings[] = 'Accepting the suggested match makes this row a duplicate of a saved row: ' . lcfirst($ctx->existsMessage());
                    }
                    $maybe[$provisional] ??= $row->row_no;
                }
            }

            $status = $errors ? 'error' : ($warnings ? 'warning' : 'matched');
            if (empty($resolved['_suggest'])) {
                unset($resolved['_suggest']);
            }
            $row->update(['resolved' => $resolved, 'status' => $status, 'messages' => array_merge($errors, $warnings) ?: null]);
            $counts[$status]++;
        }
        $batch->update(['matched_rows' => $counts['matched'], 'warning_rows' => $counts['warning'], 'error_rows' => $counts['error']]);
    }

    /** Teach the importer a name, then re-match the whole batch. */
    public function fix(ImportBatch $batch, string $type, string $raw, int $targetId): void
    {
        abort_unless(in_array($type, ['organisation', 'course', 'quota', 'category', 'exam']), 404);
        Alias::learn($type, $raw, $targetId);
        $this->process($batch);
    }

    /** Accept the suggested match of fuzzy rows (all, or one raw value). */
    public function acceptSuggestions(ImportBatch $batch, ?string $type = null, ?string $raw = null): int
    {
        $n = 0;
        foreach (ImportRow::where('batch_id', $batch->id)->where('status', 'warning')->cursor() as $row) {
            foreach ($row->resolved['_suggest'] ?? [] as $field => $s) {
                if (($type && $type !== $field) || ($raw !== null && Alias::normalize($raw) !== Alias::normalize($s['raw']))) {
                    continue;
                }
                Alias::learn($field, $s['raw'], $s['id']);
                $n++;
            }
        }
        $this->process($batch);
        return $n;
    }

    // ------------------------------------------------------------------ row resolution

    /**
     * @return array{0: array, 1: string[], 2: string[], 3: ?string, 4: ?string}
     *         [resolved, errors, warnings, key when every part is matched, key using suggested matches for pending names]
     */
    private function resolveRow(string $type, array $raw, ImportContext $ctx): array
    {
        $errors = [];
        $warnings = [];
        $r = ['_suggest' => []];
        $get = fn ($k) => trim((string) ($raw[$k] ?? ''));

        // --- college: code first, then name / alias / fuzzy
        $code = $get('institute_code');
        $name = $get('organisation');
        $org = null;
        if ($code !== '' && ($id = $ctx->orgByCode($code))) {
            $org = $id;
        } elseif ($name !== '') {
            $m = $ctx->match('organisation', $name);
            $org = $this->apply($m, 'organisation', $name, $r, $errors, $warnings, 'College');
        } elseif ($code !== '') {
            $errors[] = "Unknown institute code \"$code\" for this authority. Add it under Institute codes, or give the college name.";
        } else {
            $errors[] = 'College is missing (give institute_code or organisation).';
        }
        $r['organisation_id'] = $org;
        $r['institute_code'] = $code !== '' ? $code : null;

        // --- course / quota
        $r['course_id'] = $this->single($ctx, 'course', $get('course'), 'Course', $r, $errors, $warnings);
        $r['quota_id'] = $this->single($ctx, 'quota', $get('quota'), 'Quota', $r, $errors, $warnings);

        switch ($type) {
            case 'participants':
                $r['institute_type'] = $this->instituteType($get('institute_type'), $errors);
                foreach (['annual_tuition_fee', 'other_fees'] as $f) {
                    $r[$f] = $this->decimal($get($f), $f, $errors);
                }
                $r['total_seats'] = $this->integer($get('total_seats'), 'total_seats', $errors);
                $r['bond_details'] = $get('bond_details') ?: null;
                break;

            case 'seats':
                $r['category_id'] = $this->single($ctx, 'category', $get('category'), 'Category', $r, $errors, $warnings);
                $r['gender'] = $this->gender($get('gender'), $errors);
                $r['pwd'] = $this->pwd($get('pwd'), $errors);
                $seats = $this->integer($get('seats'), 'seats', $errors);
                if ($get('seats') === '') {
                    $errors[] = 'Seats is missing.';
                }
                $r['seats'] = $seats;
                break;

            case 'cutoffs':
                $examName = $get('exam');
                if ($examName === '' && $ctx->soleExam()) {
                    $r['exam_id'] = $ctx->soleExam();
                } elseif ($examName === '') {
                    $errors[] = 'Exam is missing (this counselling uses more than one exam).';
                    $r['exam_id'] = null;
                } else {
                    $m = $ctx->match('exam', $examName);
                    $r['exam_id'] = $this->apply($m, 'exam', $examName, $r, $errors, $warnings, 'Exam');
                }
                $r['category_id'] = $this->single($ctx, 'category', $get('category'), 'Category', $r, $errors, $warnings);
                $r['gender'] = $this->gender($get('gender'), $errors);
                $r['pwd'] = $this->pwd($get('pwd'), $errors);
                $r['rank_type'] = $this->rankType($get('rank_type'), $errors);
                $r['opening_value'] = $this->decimal($get('opening_value'), 'opening_value', $errors);
                $r['closing_value'] = $this->decimal($get('closing_value'), 'closing_value', $errors);
                if ($get('closing_value') === '') {
                    $errors[] = 'Closing value is missing.';
                }
                if ($r['rank_type'] && $r['opening_value'] !== null && $r['closing_value'] !== null) {
                    $worse = in_array($r['rank_type'], Cutoff::HIGHER_IS_WORSE);
                    if ($worse && $r['closing_value'] < $r['opening_value']) {
                        $errors[] = 'Closing rank must be at least the opening rank.';
                    }
                    if (!$worse && $r['closing_value'] > $r['opening_value']) {
                        $errors[] = 'Closing ' . $r['rank_type'] . ' must be at most the opening value.';
                    }
                }
                break;
        }

        // --- seats and cutoffs only for the cycle's participating institutes
        if (in_array($type, ['seats', 'cutoffs'], true) && $r['organisation_id'] && $r['course_id'] && $r['quota_id']
            && ($problem = $ctx->participantProblem($r['organisation_id'], $r['course_id'], $r['quota_id']))) {
            $errors[] = $problem;
        }

        return [$r, $errors, $warnings, $this->rowKey($type, $r, false), $this->rowKey($type, $r, true)];
    }

    /** Fields that identify a row (same order as ImportContext::exists() keys). */
    private const KEY_FIELDS = [
        'participants' => ['organisation_id', 'course_id', 'quota_id'],
        'seats' => ['organisation_id', 'course_id', 'quota_id', 'category_id', 'gender', 'pwd'],
        'cutoffs' => ['exam_id', 'organisation_id', 'course_id', 'quota_id', 'category_id', 'gender', 'pwd', 'rank_type'],
    ];

    /**
     * The row's identity key, or null when a part is still unknown. With $withSuggestions, a name waiting for confirmation
     * counts as its suggested match (and the key is returned only when at least one such suggestion was used).
     */
    private function rowKey(string $type, array $r, bool $withSuggestions): ?string
    {
        $fromSuggest = ['organisation_id' => 'organisation', 'course_id' => 'course', 'quota_id' => 'quota', 'category_id' => 'category', 'exam_id' => 'exam'];
        $used = false;
        $parts = [];
        foreach (self::KEY_FIELDS[$type] as $f) {
            $v = $r[$f] ?? null;
            if ($v === null && $withSuggestions && isset($fromSuggest[$f], $r['_suggest'][$fromSuggest[$f]]['id'])) {
                $v = $r['_suggest'][$fromSuggest[$f]]['id'];
                $used = true;
            }
            if ($f === 'gender') {
                $parts[] = $v ?? '*';
                continue;
            }
            if ($f === 'pwd') {
                $parts[] = (int) $v;
                continue;
            }
            if ($v === null || $v === '') {
                return null;
            }
            $parts[] = $v;
        }
        return (!$withSuggestions || $used) ? implode('|', $parts) : null;
    }

    /** Resolve one name through the matcher and record any suggestion / error. */
    private function apply(array $m, string $type, string $raw, array &$r, array &$errors, array &$warnings, string $label): ?int
    {
        if ($m['state'] === 'matched') {
            return $m['id'];
        }
        if ($m['state'] === 'warning') {
            $r['_suggest'][$type] = ['id' => $m['id'], 'name' => $m['name'], 'score' => $m['score'], 'raw' => $raw];
            $warnings[] = "$label \"$raw\" is not an exact match: did you mean \"{$m['name']}\" (" . $m['score'] . "% similar)? Confirm it.";
            return null;
        }
        $errors[] = "$label \"$raw\": " . $m['msg'];
        return null;
    }

    private function single(ImportContext $ctx, string $type, string $raw, string $label, array &$r, array &$errors, array &$warnings): ?int
    {
        if ($raw === '') {
            $errors[] = "$label is missing.";
            return null;
        }
        return $this->apply($ctx->match($type, $raw), $type, $raw, $r, $errors, $warnings, $label);
    }

    // ------------------------------------------------------------------ value normalisers

    private function gender(string $v, array &$errors): ?string
    {
        $n = strtolower(trim($v));
        if (in_array($n, ['', '-', 'na', 'n/a', 'all', 'any', 'neutral', 'gender neutral', 'gender-neutral', 'open'])) return null;
        if (in_array($n, ['f', 'female', 'female only', 'female-only', 'girls', 'women'])) return 'female';
        if (in_array($n, ['m', 'male', 'male only', 'male-only', 'boys'])) return 'male';
        $errors[] = "Gender \"$v\" is not recognised (use male, female or leave blank for gender-neutral).";
        return null;
    }

    private function pwd(string $v, array &$errors): bool
    {
        $n = strtolower(trim($v));
        if (in_array($n, ['', '0', 'n', 'no', 'false', 'non-pwd', '-'])) return false;
        if (in_array($n, ['1', 'y', 'yes', 'true', 'pwd'])) return true;
        $errors[] = "PwD \"$v\" is not recognised (use yes or no).";
        return false;
    }

    private function instituteType(string $v, array &$errors): ?string
    {
        $n = strtolower(trim($v));
        $map = ['' => null, 'government' => 'government', 'govt' => 'government', 'gov' => 'government', 'private' => 'private', 'deemed' => 'deemed',
            'deemed university' => 'deemed', 'central' => 'central_university', 'central university' => 'central_university', 'central_university' => 'central_university',
            'autonomous' => 'autonomous', 'other' => 'other'];
        if (!array_key_exists($n, $map)) {
            $errors[] = "Institute type \"$v\" is not recognised (government, private, deemed, central university, autonomous, other).";
            return null;
        }
        return $map[$n];
    }

    private function rankType(string $v, array &$errors): ?string
    {
        $n = strtolower(trim(preg_replace('/[\s_-]+/', ' ', $v)));
        $map = ['air' => 'air', 'rank' => 'air', 'all india rank' => 'air', 'category rank' => 'category_rank', 'cr' => 'category_rank', 'cat rank' => 'category_rank',
            'state merit rank' => 'state_merit_rank', 'smr' => 'state_merit_rank', 'state rank' => 'state_merit_rank', 'merit rank' => 'state_merit_rank',
            'score' => 'score', 'marks' => 'score', 'percentile' => 'percentile'];
        if (!isset($map[$n])) {
            $errors[] = $v === '' ? 'Rank type is missing.' : "Rank type \"$v\" is not recognised (air, category rank, state merit rank, score, percentile).";
            return null;
        }
        return $map[$n];
    }

    private function decimal(string $v, string $field, array &$errors): ?float
    {
        if ($v === '') return null;
        $clean = str_replace([',', ' '], '', $v);
        if (!is_numeric($clean) || (float) $clean < 0) {
            $errors[] = ucfirst(str_replace('_', ' ', $field)) . " \"$v\" must be a positive number.";
            return null;
        }
        return (float) $clean;
    }

    private function integer(string $v, string $field, array &$errors): ?int
    {
        if ($v === '') return null;
        $clean = str_replace([',', ' '], '', $v);
        if (!ctype_digit($clean)) {
            $errors[] = ucfirst(str_replace('_', ' ', $field)) . " \"$v\" must be a whole number.";
            return null;
        }
        return (int) $clean;
    }

    // ------------------------------------------------------------------ confirm / undo

    /** Save the matched rows under this batch. Warning and error rows are skipped. */
    public function confirm(ImportBatch $batch): int
    {
        abort_unless($batch->status === 'previewed', 422, 'This batch has already been processed.');
        $rows = ImportRow::where('batch_id', $batch->id)->where('status', 'matched')->orderBy('row_no');
        $now = now();
        $saved = 0;

        DB::transaction(function () use ($batch, $rows, $now, &$saved) {
            $snapshot = null;

            if ($batch->type === 'participants') {
                if ($batch->mode === 'replace') {
                    $snapshot = DB::table('cns_participants')->where('cycle_id', $batch->cycle_id)->get()->map(fn ($r) => (array) $r)->all();
                    DB::table('cns_participants')->where('cycle_id', $batch->cycle_id)->delete();
                }
                $rows->chunk(500, function ($chunk) use ($batch, $now, &$saved) {
                    $insert = $chunk->map(fn ($r) => [
                        'cycle_id' => $batch->cycle_id, 'organisation_id' => $r->resolved['organisation_id'], 'course_id' => $r->resolved['course_id'],
                        'quota_id' => $r->resolved['quota_id'], 'institute_type' => $r->resolved['institute_type'] ?? null, 'institute_code' => $r->resolved['institute_code'] ?? null,
                        'annual_tuition_fee' => $r->resolved['annual_tuition_fee'] ?? null, 'other_fees' => $r->resolved['other_fees'] ?? null,
                        'bond_details' => $r->resolved['bond_details'] ?? null, 'total_seats' => $r->resolved['total_seats'] ?? null,
                        'import_batch_id' => $batch->id, 'created_at' => $now, 'updated_at' => $now,
                    ])->all();
                    DB::table('cns_participants')->insert($insert);
                    $saved += count($insert);
                });
            }

            if ($batch->type === 'cutoffs') {
                if ($batch->mode === 'replace') {
                    $snapshot = DB::table('cns_cutoffs')->where('round_id', $batch->round_id)->get()->map(fn ($r) => (array) $r)->all();
                    DB::table('cns_cutoffs')->where('round_id', $batch->round_id)->delete();
                }
                $rows->chunk(500, function ($chunk) use ($batch, $now, &$saved) {
                    $insert = $chunk->map(fn ($r) => [
                        'round_id' => $batch->round_id, 'exam_id' => $r->resolved['exam_id'], 'organisation_id' => $r->resolved['organisation_id'],
                        'course_id' => $r->resolved['course_id'], 'quota_id' => $r->resolved['quota_id'], 'category_id' => $r->resolved['category_id'],
                        'gender' => $r->resolved['gender'] ?? null, 'pwd' => (int) ($r->resolved['pwd'] ?? 0), 'rank_type' => $r->resolved['rank_type'],
                        'opening_value' => $r->resolved['opening_value'] ?? null, 'closing_value' => $r->resolved['closing_value'] ?? null,
                        'import_batch_id' => $batch->id, 'created_at' => $now, 'updated_at' => $now,
                    ])->all();
                    DB::table('cns_cutoffs')->insert($insert);
                    $saved += count($insert);
                });
            }

            if ($batch->type === 'seats') {
                // A seat matrix import is always a NEW version: append = live version + these rows, replace = only these rows.
                $previousLive = SeatMatrixVersion::where('round_id', $batch->round_id)->where('is_live', true)->first();
                $version = SeatMatrixVersion::create([
                    'round_id' => $batch->round_id, 'version_no' => SeatMatrixVersion::nextNumber($batch->round_id),
                    'label' => $batch->label ?: 'Imported ' . $now->format('d M Y'), 'published_on' => $batch->published_on, 'source_url' => $batch->source_url,
                    'imported_by' => $batch->created_by, 'imported_at' => $now, 'is_live' => false, 'import_batch_id' => $batch->id,
                ]);
                if ($batch->mode === 'append' && $previousLive) {
                    DB::statement('INSERT INTO cns_seat_matrix_rows (version_id, organisation_id, course_id, quota_id, category_id, gender, pwd, seats) '
                        . 'SELECT ?, organisation_id, course_id, quota_id, category_id, gender, pwd, seats FROM cns_seat_matrix_rows WHERE version_id = ?', [$version->id, $previousLive->id]);
                }
                $rows->chunk(1000, function ($chunk) use ($version, &$saved) {
                    $insert = $chunk->map(fn ($r) => [
                        'version_id' => $version->id, 'organisation_id' => $r->resolved['organisation_id'], 'course_id' => $r->resolved['course_id'],
                        'quota_id' => $r->resolved['quota_id'], 'category_id' => $r->resolved['category_id'], 'gender' => $r->resolved['gender'] ?? null,
                        'pwd' => (int) ($r->resolved['pwd'] ?? 0), 'seats' => $r->resolved['seats'],
                    ])->all();
                    DB::table('cns_seat_matrix_rows')->insert($insert);
                    $saved += count($insert);
                });
                $version->update(['row_count' => DB::table('cns_seat_matrix_rows')->where('version_id', $version->id)->count()]);
                if ($batch->make_live) {
                    $version->makeLive();
                }
            }

            if ($snapshot) {
                // Replaced rows are kept so that Undo can bring them back.
                Storage::disk('public')->put('cns/imports/replaced_' . $batch->id . '.json', json_encode($snapshot));
            }
            $batch->update(['status' => 'confirmed', 'saved_rows' => $saved, 'confirmed_at' => $now]);
        });

        return $saved;
    }

    public function undo(ImportBatch $batch): void
    {
        abort_unless($batch->status === 'confirmed', 422, 'Only a confirmed batch can be undone.');

        DB::transaction(function () use ($batch) {
            if ($batch->type === 'participants') {
                DB::table('cns_participants')->where('import_batch_id', $batch->id)->delete();
                $this->restoreReplaced($batch, 'cns_participants');
            }
            if ($batch->type === 'cutoffs') {
                DB::table('cns_cutoffs')->where('import_batch_id', $batch->id)->delete();
                $this->restoreReplaced($batch, 'cns_cutoffs');
            }
            if ($batch->type === 'seats') {
                $version = SeatMatrixVersion::where('import_batch_id', $batch->id)->first();
                if ($version) {
                    $wasLive = $version->is_live;
                    $roundId = $version->round_id;
                    $version->delete();   // rows cascade
                    if ($wasLive) {
                        SeatMatrixVersion::where('round_id', $roundId)->orderByDesc('version_no')->first()?->makeLive();
                    }
                }
            }
            $batch->update(['status' => 'undone', 'undone_at' => now()]);
        });
    }

    private function restoreReplaced(ImportBatch $batch, string $table): void
    {
        $file = 'cns/imports/replaced_' . $batch->id . '.json';
        if (!Storage::disk('public')->exists($file)) {
            return;
        }
        foreach (array_chunk(json_decode(Storage::disk('public')->get($file), true) ?: [], 500) as $chunk) {
            DB::table($table)->insert($chunk);
        }
        Storage::disk('public')->delete($file);
    }

    // ------------------------------------------------------------------ error report

    public function errorCsv(ImportBatch $batch): string
    {
        $def = self::DEFS[$batch->type];
        $out = $this->csvLine(array_merge(['row'], $def['columns'], ['problem']));
        foreach (ImportRow::where('batch_id', $batch->id)->whereIn('status', ['error', 'warning'])->orderBy('row_no')->cursor() as $row) {
            $out .= $this->csvLine(array_merge([$row->row_no], array_map(fn ($c) => $row->raw[$c] ?? '', $def['columns']), [implode(' | ', $row->messages ?? [])]));
        }
        return $out;
    }
}

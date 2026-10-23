<?php

namespace App\Services\Quality;

use App\Models\Quality\AccreditationBody;
use App\Models\Quality\RankingBody;
use App\Models\Quality\RankingCategory;
use Illuminate\Support\Facades\DB;

/**
 * Copies the old single-value columns on `organisations` (NAAC / UGC / AICTE / NIRF) into the new record tables.
 * Everything arrives UNVERIFIED with year unknown, because the old columns never said which year or which source.
 * The old columns are left untouched. Safe to re-run at any time: an (organisation, legacy_source) pair is only ever created once,
 * so organisations added or edited since the last run are picked up and nothing is duplicated.
 */
class LegacyMigrator
{
    /** @return array{ok:bool, error?:string, counts:array<string,int>, acc_rows:int, rank_rows:int, warnings:string[]} */
    public function run(bool $apply): array
    {
        $acc = AccreditationBody::pluck('id', 'short_name');
        $nirf = RankingBody::where('short_name', 'NIRF')->first();
        foreach (['NAAC', 'UGC', 'AICTE'] as $need) {
            if (!isset($acc[$need])) return $this->fail("Accreditation body $need is missing. Run the masters step first.");
        }
        if (!$nirf) return $this->fail('Ranking body NIRF is missing. Run the masters step first.');
        $cats = RankingCategory::where('ranking_body_id', $nirf->id)->pluck('id', 'name');
        if (!isset($cats['Overall'])) return $this->fail('NIRF / Overall category is missing. Run the masters step first.');

        // The old nirf_rank_category kept only a number: which category it was is lost. Keep the number under an explicit placeholder.
        $unspecified = $cats['Unspecified category'] ?? null;
        if (!$unspecified && $apply) {
            $unspecified = RankingCategory::create(['ranking_body_id' => $nirf->id, 'name' => 'Unspecified category', 'applicable_levels' => ['organisation'], 'status' => true, 'sort_order' => 99])->id;
        }

        $existing = fn (string $table) => DB::table($table)->whereNotNull('legacy_source')
            ->get(['entity_id', 'legacy_source'])->map(fn ($r) => $r->entity_id . '|' . $r->legacy_source)->flip();
        $haveAcc = $existing('entity_accreditations');
        $haveRank = $existing('entity_rankings');

        $now = now();
        $counts = ['NAAC' => 0, 'UGC' => 0, 'AICTE' => 0, 'NIRF overall' => 0, 'NIRF category' => 0, 'already migrated' => 0, 'not a plain rank (left in old column)' => 0];
        $accRows = []; $rankRows = []; $warnings = [];

        DB::table('organisations')->select('id', 'name', 'naac_accredited', 'naac_grade', 'ugc_recognized', 'ugc_approval_number', 'ugc_recognition_date',
            'aicte_approved', 'nirf_rank_overall', 'nirf_rank_category', 'nirf_institute_id')->orderBy('id')
            ->chunk(500, function ($orgs) use (&$accRows, &$rankRows, &$counts, &$warnings, $acc, $cats, $unspecified, $haveAcc, $haveRank, $nirf, $now) {
                foreach ($orgs as $o) {
                    $id = (string) $o->id;
                    $accBase = ['entity_type' => 'organisation', 'entity_id' => $id, 'verification_status' => 'unverified', 'status' => 'active', 'created_at' => $now, 'updated_at' => $now];

                    $grade = trim((string) $o->naac_grade);
                    if ($o->naac_accredited || $grade !== '') {
                        $src = 'organisations.naac';
                        if (isset($haveAcc[$id . '|' . $src])) { $counts['already migrated']++; } else {
                            $accRows[] = $accBase + ['accreditation_body_id' => $acc['NAAC'], 'grade' => $grade !== '' ? mb_substr($grade, 0, 30) : null, 'legacy_source' => $src,
                                'notes' => 'Migrated from old columns. Validity period and cycle were never recorded.'];
                            $counts['NAAC']++;
                        }
                    }
                    if ($o->ugc_recognized) {
                        $src = 'organisations.ugc';
                        if (isset($haveAcc[$id . '|' . $src])) { $counts['already migrated']++; } else {
                            $accRows[] = $accBase + ['accreditation_body_id' => $acc['UGC'], 'certificate_number' => $o->ugc_approval_number ? mb_substr($o->ugc_approval_number, 0, 100) : null,
                                'valid_from' => $o->ugc_recognition_date ?: null, 'legacy_source' => $src, 'notes' => 'Migrated from old columns.'];
                            $counts['UGC']++;
                        }
                    }
                    if ($o->aicte_approved) {
                        $src = 'organisations.aicte';
                        if (isset($haveAcc[$id . '|' . $src])) { $counts['already migrated']++; } else {
                            $accRows[] = $accBase + ['accreditation_body_id' => $acc['AICTE'], 'legacy_source' => $src, 'notes' => 'Migrated from old columns. Approval year / validity never recorded.'];
                            $counts['AICTE']++;
                        }
                    }

                    $rankBase = ['entity_type' => 'organisation', 'entity_id' => $id, 'ranking_body_id' => $nirf->id, 'ranking_year' => null,
                        'external_institute_id' => $o->nirf_institute_id ?: null, 'verification_status' => 'unverified', 'created_at' => $now, 'updated_at' => $now];
                    foreach ([['nirf_rank_overall', 'organisations.nirf_rank_overall', $cats['Overall'], 'NIRF overall', 'Migrated from old column. Ranking year was never recorded.'],
                        ['nirf_rank_category', 'organisations.nirf_rank_category', $unspecified, 'NIRF category', 'Migrated from old column. Both the ranking year and WHICH category were never recorded - please correct.']] as [$col, $src, $catId, $label, $note]) {
                        $raw = trim((string) $o->$col);
                        if ($raw === '' || $raw === '0') continue;   // empty or 0 means "no rank"
                        if (isset($haveRank[$id . '|' . $src])) { $counts['already migrated']++; continue; }
                        if (!ctype_digit($raw) || (int) $raw < 1) {
                            $counts['not a plain rank (left in old column)']++;
                            $warnings[] = "#$id {$o->name}: $col = \"$raw\" is not a plain rank, left in the old column only.";
                            continue;
                        }
                        $rankRows[] = $rankBase + ['ranking_category_id' => $catId ?: 0, 'rank_exact' => (int) $raw, 'legacy_source' => $src, 'notes' => $note];
                        $counts[$label]++;
                    }
                }
            });

        if ($apply) {
            DB::transaction(function () use ($accRows, $rankRows) {
                foreach (array_chunk($accRows, 200) as $c) DB::table('entity_accreditations')->insert($this->uniformKeys($c));
                foreach (array_chunk($rankRows, 200) as $c) DB::table('entity_rankings')->insert($this->uniformKeys($c));
            });
        }
        return ['ok' => true, 'counts' => $counts, 'acc_rows' => count($accRows), 'rank_rows' => count($rankRows), 'warnings' => $warnings];
    }

    private function fail(string $msg): array
    {
        return ['ok' => false, 'error' => $msg, 'counts' => [], 'acc_rows' => 0, 'rank_rows' => 0, 'warnings' => []];
    }

    /** A multi-row insert needs identical keys in every row. */
    private function uniformKeys(array $rows): array
    {
        $keys = array_unique(array_merge(...array_map('array_keys', $rows)));
        return array_map(fn ($r) => array_merge(array_fill_keys($keys, null), $r), $rows);
    }
}

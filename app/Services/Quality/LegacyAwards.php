<?php

namespace App\Services\Quality;

use App\Models\Quality\EntityAward;
use Illuminate\Support\Facades\DB;

/**
 * Turns the old free-text award lists (organisations.awards_and_recognition / awards_or_recognition, which mostly repeat each other and mix
 * in rankings, memberships and affiliations) into one structured, UNVERIFIED record per distinct item.
 *
 * Nothing is guessed beyond what the text says:
 *  - an entry is split on ", " only when it is plainly a list (long, with several well-sized parts);
 *  - a year is taken only when the text contains exactly one (a range like 2024-25 gives its first year); several different years => none;
 *  - an issuer is taken only from a trailing "by X" or "(X)";
 *  - every item gets a `kind` so non-awards (rankings, memberships, affiliations, accreditation statements) can be filtered and sorted out.
 * The title always keeps the original wording. Old columns are left untouched. Safe to re-run: an item is created once per organisation.
 */
class LegacyAwards
{
    /** @return array{counts:array<string,int>, by_kind:array<string,int>, warnings:string[]} */
    public function run(bool $apply): array
    {
        $counts = ['organisations' => 0, 'items read' => 0, 'split from longer entries' => 0, 'duplicates collapsed' => 0, 'records to create' => 0, 'already copied' => 0, 'with a year' => 0, 'with an issuer' => 0];
        $byKind = array_fill_keys(array_keys(EntityAward::KINDS), 0);
        $now = now();

        $have = DB::table('entity_awards')->where('entity_type', 'organisation')->whereNotNull('legacy_source')->get(['entity_id', 'legacy_source'])
            ->map(fn ($r) => $r->entity_id . '|' . $r->legacy_source)->flip();

        $rows = [];
        DB::table('organisations')->select('id', 'awards_and_recognition as a', 'awards_or_recognition as b')->orderBy('id')->chunk(300, function ($orgs) use (&$rows, &$counts, &$byKind, $have, $now) {
            foreach ($orgs as $o) {
                $seen = [];
                $any = false;
                foreach ([$o->b, $o->a] as $raw) {                       // awards_or_recognition first: it is the one that has real content
                    foreach (self::items($raw) as [$title, $split]) {
                        $counts['items read']++;
                        $norm = EntityAward::normalise($title);
                        if ($norm === '' || isset($seen[$norm])) { if ($norm !== '') $counts['duplicates collapsed']++; continue; }
                        $seen[$norm] = true; $any = true;
                        if ($split) $counts['split from longer entries']++;
                        $key = 'organisations.awards:' . substr(md5($norm), 0, 16);
                        if (isset($have[$o->id . '|' . $key])) { $counts['already copied']++; continue; }
                        $kind = self::classify($title);
                        $year = self::year($title);
                        $issuer = in_array($kind, ['award', 'ranking_mention'], true) ? self::issuer($title) : null;
                        $rows[] = ['entity_type' => 'organisation', 'entity_id' => (string) $o->id, 'title' => mb_substr($title, 0, 500), 'kind' => $kind, 'issuer' => $issuer,
                            'award_year' => $year, 'verification_status' => 'unverified', 'legacy_source' => $key,
                            'notes' => 'Migrated from the old awards text. Kind, year and issuer were detected automatically: please check them.' . ($split ? ' Split from a longer comma-separated entry.' : ''),
                            'created_at' => $now, 'updated_at' => $now];
                        $counts['records to create']++; $byKind[$kind]++;
                        if ($year) $counts['with a year']++;
                        if ($issuer) $counts['with an issuer']++;
                    }
                }
                if ($any) $counts['organisations']++;
            }
        });

        if ($apply && $rows) {
            DB::transaction(function () use ($rows) {
                foreach (array_chunk($rows, 300) as $c) DB::table('entity_awards')->insert($c);
            });
        }
        return ['counts' => $counts, 'by_kind' => $byKind, 'warnings' => []];
    }

    /** @return array<int, array{0:string,1:bool}> [title, wasSplit] */
    public static function items(?string $raw): array
    {
        $raw = trim((string) $raw);
        if ($raw === '' || $raw === '[]' || strtolower($raw) === 'null') return [];
        $decoded = json_decode($raw, true);
        $list = is_array($decoded) ? $decoded : preg_split('/[\r\n;]+/', $raw);
        $out = [];
        foreach ($list as $el) {
            if (!is_scalar($el)) continue;
            $s = trim((string) $el);
            if ($s === '' || strtolower($s) === 'null') continue;
            $parts = self::split($s);
            foreach ($parts as $p) {
                $p = trim(preg_replace('/^(and|&)\s+/i', '', trim($p)), " \t.;-");
                if ($p !== '' && mb_strlen($p) >= 3) $out[] = [$p, count($parts) > 1];
            }
        }
        return $out;
    }

    /** Split only plain lists: long, at least three well-sized parts. "Best School, Punjab, 2018" stays whole. */
    public static function split(string $s): array
    {
        if (mb_strlen($s) <= 100 || substr_count($s, ', ') < 2) return [$s];
        $parts = array_map('trim', explode(', ', $s));
        foreach ($parts as $p) if (mb_strlen($p) < 6 || mb_strlen($p) > 170) return [$s];
        return $parts;
    }

    public static function classify(string $t): string
    {
        return match (true) {
            (bool) preg_match('/^\s*(affiliated|affiliation)\b|affiliated to|\b(CBSE|CISCE|ICSE|IGCSE|state board)\s+affiliated\b/i', $t) => 'affiliation',
            (bool) preg_match('/\b(accredited|accreditation|NAAC|NBA|AICTE|UGC)\b/i', $t) => 'accreditation_mention',
            (bool) preg_match('/^\s*member\b|\bmember\s+(school|of)\b|\bmembership\b|^round square\b/i', $t) => 'membership',
            (bool) preg_match('/\brank(ed|ing|ings)?\b|\btop\s*\d+\b|#\s?\d+/i', $t) => 'ranking_mention',
            (bool) preg_match('/\b(award|awarded|prize|trophy|medal|honou?rs?|winner|champion|samman|felicitat|certificate of|excellence|best\b|achiever|recogni[sz]ed as|recognition|laureate|first position|book of records)/i', $t) => 'award',
            default => 'other',
        };
    }

    /** A single year, or the first year of a range; several different years => null (ambiguous). */
    public static function year(string $t): ?int
    {
        preg_match_all('/\b((?:19|20)\d{2})(?:\s*[-\/\x{2013}]\s*(?:\d{4}|\d{2}))?\b/u', $t, $m);
        $years = array_values(array_unique(array_map('intval', $m[1])));
        if (count($years) !== 1) return null;
        $y = $years[0];
        return $y >= 1900 && $y <= (int) now()->year + 1 ? $y : null;
    }

    /** Trailing "by EducationWorld" or "(Businessworld)". Null when unclear. */
    public static function issuer(string $t): ?string
    {
        if (preg_match('/\(([A-Z][A-Za-z&.\' ]{2,40})\)\s*$/u', $t, $m) && !preg_match('/\d/', $m[1])) return trim($m[1]);
        if (preg_match('/\bby\s+([A-Z][A-Za-z&.\'\- ]{2,60})\s*$/u', $t, $m) && !preg_match('/\b(managed|founded|run|led|owned)\s+by\s+' . preg_quote(trim($m[1]), '/') . '\s*$/i', $t)) return trim($m[1], " .-");
        return null;
    }
}

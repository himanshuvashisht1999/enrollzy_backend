<?php

namespace App\Services\Quality;

use App\Models\Quality\AccreditationBody;
use Illuminate\Support\Facades\DB;

/**
 * Reads the old free-text lists organisations.statutory_approvals and organisations.international_accreditations
 * ("UGC, AICTE, NAAC A+, NBA", "AACSB, ACBSP, WES Recognition, QS Rating"...) and turns the parts that really say
 * "this institution holds that approval / accreditation" into UNVERIFIED records.
 *
 * Nothing is guessed. A part becomes a record only when ALL of these hold:
 *  - it names a body that exists in the masters (matched by short name or full name);
 *  - it is a plain statement, not a conditional one ("where applicable", "selected programs", "for Law programs", "such as ...", "as required");
 *  - it is not a membership, a ranking or rating mention, or a generic phrase;
 *  - the body can apply to an organisation (NBA / ABET are programme-level bodies, so they are never put on the institution);
 *  - the organisation has no record for that body yet (any source), and this text has not been copied before.
 * Everything else is counted by reason, and bodies the masters do not have are listed by name so they can be added and the step re-run.
 * The record carries no year, number or intake (the text never had them) and is marked unverified. The old columns are left untouched.
 * Safe to re-run: a (organisation, body, column) combination is created once.
 */
class LegacyFreeText
{
    public const COLUMNS = ['statutory_approvals' => 'organisations.statutory_approvals', 'international_accreditations' => 'organisations.international_accreditations'];

    /** Alias patterns, most specific first. Value: master short name, or a label with a leading "!" for things that have no master (reported, never created). */
    private const ALIASES = [
        '/\bUGC[\s-]*DEB\b|\bDEB\b|distance education bureau/i' => '!DEB (Distance Education Bureau)',
        '/\bNCISM\b|national commission for indian system of medicine/i' => '!NCISM',
        '/\bNCHMCT\b/i' => '!NCHMCT',
        '/\bHSCP\b/i' => '!HSCP',
        '/\bDISR\b/i' => '!DISR',
        '/institution of eminence|\bIoE\b/i' => '!Institution of Eminence',
        '/\bACBSP\b/i' => '!ACBSP',
        '/\bIACBE\b/i' => '!IACBE',
        '/\bWASC\b/i' => '!WASC',
        '/\bQAA\b/i' => '!QAA',
        '/\bBGA\b|business graduates association/i' => '!BGA',
        '/\bWES\b|world education services/i' => '!WES',
        '/\bWDO\b|world design organi[sz]ation/i' => '!WDO',
        '/\bIET\b/i' => '!IET',
        '/\bACU\b|association of commonwealth universities/i' => '!ACU',
        '/\bIAU\b|international association of universities/i' => '!IAU',
        '/\bAIU\b|association of indian universities/i' => 'AIU',
        '/\bAACSB\b/i' => 'AACSB',
        '/\bAMBA\b|association of mbas/i' => 'AMBA',
        '/\bABET\b/i' => 'ABET',
        '/\bNAAC\b/i' => 'NAAC',
        '/\bNBA\b|national board of accreditation/i' => 'NBA',
        '/\bAICTE\b/i' => 'AICTE',
        '/\bUGC\b|university grants commission/i' => 'UGC',
        '/\bBCI\b|bar council of india/i' => 'BCI',
        '/\bPCI\b|pharmacy council of india/i' => 'PCI',
        '/\bCOA\b|council of architecture/i' => 'COA',
        '/\bNCTE\b/i' => 'NCTE',
        '/\bINC\b|indian nursing council/i' => 'INC',
        '/\bNMC\b|national medical commission/i' => 'NMC',
        '/\bDCI\b|dental council of india/i' => 'DCI',
        '/\bICAR\b/i' => 'ICAR',
    ];

    private const CONDITIONAL = '/where (applicable|relevant|required)|\bapplicable\b|\bselected\b|\beligible\b|\bfor [a-z ]*(programs?|programmes?|courses?|school|schools|department)\b|\bprograms?\b|\bprogrammes?\b|\bschool of\b|as (required|per)|\bsome\b|\bcertain\b|\bpartial/i';
    private const MEMBERSHIP = '/\bmember(ship)?\b|\bparticipation\b|\bmou\b|collaborations?|partnerships?/i';
    private const RANKING = '/\branking|\brated\b|\brating\b|\bstar\b|\bi-?gauge\b|\blisted\b|\bimpact\b/i';

    /** @return array{counts:array<string,int>, skipped:array<string,int>, no_master:array<string,int>, to_create:int, warnings:string[], rows:array} */
    public function run(bool $apply): array
    {
        $bodies = AccreditationBody::all()->keyBy('short_name');
        $counts = ['organisations with text' => 0, 'parts read' => 0, 'records to create' => 0, 'already copied' => 0, 'already recorded' => 0];
        $skipped = []; $noMaster = []; $warnings = []; $rows = [];
        $bump = function (array &$a, string $k) { $a[$k] = ($a[$k] ?? 0) + 1; };
        $now = now();

        $haveSrc = [];
        foreach (['entity_approvals', 'entity_accreditations'] as $t) {
            foreach (DB::table($t)->where('entity_type', 'organisation')->whereNotNull('legacy_source')->where('legacy_source', 'like', 'organisations.%:%')->get(['entity_id', 'legacy_source']) as $r) $haveSrc[$r->entity_id . '|' . $r->legacy_source] = true;
        }
        $haveBody = [];
        foreach (['entity_approvals', 'entity_accreditations'] as $t) {
            foreach (DB::table($t)->where('entity_type', 'organisation')->whereNull('deleted_at')->get(['entity_id', 'accreditation_body_id']) as $r) $haveBody[$r->entity_id . '|' . $r->accreditation_body_id] = true;
        }

        DB::table('organisations')->select('id', 'statutory_approvals', 'international_accreditations')->orderBy('id')->chunk(300, function ($orgs) use (&$rows, &$counts, &$skipped, &$noMaster, &$haveSrc, &$haveBody, $bodies, $bump, $now) {
            foreach ($orgs as $o) {
                $any = false;
                foreach (self::COLUMNS as $col => $source) {
                    foreach (self::parts($o->$col) as [$text, $afterSuchAs]) {
                        $any = true; $counts['parts read']++;
                        $verdict = self::classify($text, $afterSuchAs, $bodies);
                        if ($verdict['skip']) {
                            $bump($skipped, $verdict['skip']);
                            if (isset($verdict['no_master'])) $bump($noMaster, $verdict['no_master']);
                            continue;
                        }
                        $body = $bodies[$verdict['body']];
                        $key = $source . ':' . $body->short_name;
                        if (isset($haveSrc[$o->id . '|' . $key])) { $counts['already copied']++; continue; }
                        if (isset($haveBody[$o->id . '|' . $body->id])) { $counts['already recorded']++; continue; }
                        $haveSrc[$o->id . '|' . $key] = true; $haveBody[$o->id . '|' . $body->id] = true;
                        $grade = ($body->has_grade && $verdict['grade'] && in_array($verdict['grade'], (array) $body->grade_options, true)) ? $verdict['grade'] : null;
                        $note = 'Copied from the old ' . ($col === 'statutory_approvals' ? 'statutory approvals' : 'international accreditations') . ' text: "' . mb_substr($text, 0, 200) . '". The text gave no year, number, validity or intake: please check and complete it from the official source.';
                        $common = ['entity_type' => 'organisation', 'entity_id' => (string) $o->id, 'accreditation_body_id' => $body->id, 'status' => 'active', 'verification_status' => 'unverified',
                            'legacy_source' => $key, 'notes' => $note, 'created_at' => $now, 'updated_at' => $now];
                        $rows[] = ['table' => $body->isApprovalKind() ? 'entity_approvals' : 'entity_accreditations', 'row' => $common + ($body->isApprovalKind() ? [] : ['grade' => $grade]), 'body' => $body->short_name];
                        $counts['records to create']++;
                    }
                }
                if ($any) $counts['organisations with text']++;
            }
        });

        $byBody = [];
        foreach ($rows as $r) $bump($byBody, $r['body']);
        arsort($skipped); arsort($noMaster); ksort($byBody);

        if ($apply) {
            DB::transaction(function () use ($rows) {
                foreach (collect($rows)->groupBy('table') as $table => $set) {
                    foreach ($set->pluck('row')->chunk(300) as $chunk) DB::table($table)->insert($chunk->all());
                }
            });
        }
        if ($noMaster) $warnings[] = count($noMaster) . ' name(s) in the text have no master yet (' . collect($noMaster)->take(8)->map(fn ($n, $k) => "$k x$n")->implode(', ') . (count($noMaster) > 8 ? ', ...' : '') . '). Add them under Masters if they matter, then run this step again: nothing is duplicated.';
        return ['counts' => $counts, 'skipped' => $skipped, 'no_master' => $noMaster, 'by_body' => $byBody, 'to_create' => $counts['records to create'], 'warnings' => $warnings, 'rows' => $apply ? [] : $rows];
    }

    /** @return array{skip:?string, body?:string, grade?:?string, no_master?:string} */
    public static function classify(string $text, bool $afterSuchAs, $bodies): array
    {
        $t = trim($text);
        $outer = trim(preg_replace('/\([^)]*\)/', ' ', $t));                 // text outside brackets: what is being claimed
        $inner = preg_match_all('/\(([^)]*)\)/', $t, $m) ? implode(' ', $m[1]) : '';

        $label = null;
        foreach (self::ALIASES as $re => $name) {
            if (preg_match($re, $outer) || ($outer === '' && preg_match($re, $inner))) { $label = $name; break; }
        }
        if ($label === null) {
            if (preg_match(self::MEMBERSHIP, $t)) return ['skip' => 'membership / participation, not an accreditation'];
            if (preg_match(self::RANKING, $t) || preg_match('/\b(QS|THE|times higher education|NIRF)\b/i', $outer)) return ['skip' => 'ranking or rating mention (no rank or year)'];
            $generic = (bool) preg_match('/^(other|and other|government|govt|council-specific)/i', $outer);
            $name = self::tidy($outer);
            return ['skip' => $generic ? 'general phrase, no specific body' : 'not a recognised body'] + (!$generic && $name !== '' ? ['no_master' => $name] : []);
        }
        // a body that was missing when the alias list was written may have been added to the masters since
        if (str_starts_with($label, '!') && isset($bodies[strtok(substr($label, 1), ' ')])) $label = strtok(substr($label, 1), ' ');
        $mention = fn (string $name) => str_starts_with($name, '!') ? substr($name, 1) : $name;

        if ($afterSuchAs || preg_match(self::CONDITIONAL, $t)) return ['skip' => 'conditional ("where applicable", "selected programs", "for ... programs"): not a whole-institution statement'];
        if (preg_match(self::MEMBERSHIP, $t)) return ['skip' => 'membership / participation, not an accreditation'];
        if (preg_match(self::RANKING, $t) && !preg_match('/accredit/i', $t)) return ['skip' => 'ranking or rating mention (no rank or year)'];
        if (str_starts_with($label, '!')) return ['skip' => 'no master for this body', 'no_master' => $mention($label)];
        if (!isset($bodies[$label])) return ['skip' => 'no master for this body', 'no_master' => $label];

        $body = $bodies[$label];
        $levels = $body->applicable_levels;
        if (is_array($levels) && $levels && !in_array('organisation', $levels, true)) return ['skip' => 'programme or department level body (belongs on the programmes, not the institution)'];

        $grade = null;
        if ($label === 'NAAC' && preg_match('/\bNAAC\s*[-:]?\s*(A\+\+|A\+|A|B\+\+|B\+|B|C|D)(?![A-Za-z+])/', $outer, $g)) $grade = strtoupper($g[1]);
        return ['skip' => null, 'body' => $label, 'grade' => $grade];
    }

    private static function tidy(string $s): string
    {
        $s = trim(preg_replace('/\s+/', ' ', $s), " \t.;:-");
        return mb_strlen($s) > 60 ? mb_substr($s, 0, 57) . '...' : $s;
    }

    /** Splits the old JSON list (or plain text) into parts on commas outside brackets. Returns [text, afterSuchAs]. */
    public static function parts($raw): array
    {
        if ($raw === null || $raw === '') return [];
        $dec = is_string($raw) ? json_decode($raw, true) : $raw;
        $items = is_array($dec) ? $dec : [is_string($raw) ? $raw : ''];
        $out = [];
        foreach ($items as $item) {
            if (!is_string($item) || trim($item) === '') continue;
            $depth = 0; $cur = ''; $pieces = [];
            foreach (preg_split('//u', $item, -1, PREG_SPLIT_NO_EMPTY) as $ch) {
                if ($ch === '(') $depth++;
                if ($ch === ')') $depth = max(0, $depth - 1);
                if (($ch === ',' || $ch === ';') && $depth === 0) { $pieces[] = $cur; $cur = ''; } else $cur .= $ch;
            }
            $pieces[] = $cur;
            $such = false;
            foreach ($pieces as $p) {
                $p = trim($p, " \t\n\r.");
                if ($p === '') continue;
                if (preg_match('/\b(such as|including|e\.g\.|like)\b/i', $p)) $such = true;
                $out[] = [$p, $such];
            }
        }
        return $out;
    }
}

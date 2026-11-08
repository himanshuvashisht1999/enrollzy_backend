<?php

namespace App\Services\Quality;

use App\Http\Controllers\Admin\Quality\RecordController;
use App\Models\Quality\AccreditationBody;
use App\Models\Quality\EntityAccreditation;
use App\Models\Quality\EntityApproval;
use App\Models\Quality\EntityRanking;
use App\Models\Quality\RankingBody;
use App\Models\Quality\RankingCategory;
use App\Services\GeminiOrganisationScraperService;
use App\Support\Quality\Levels;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Validation\ValidationException;

/**
 * Rankings, accreditations and statutory approvals of one entity (organisation / campus / department / course) by AI:
 * "Create" (find everything), "Missing Info" (only what is not recorded yet) and "Update" (re-check what is recorded and
 * add newer editions), each run by Gemini or Claude. The AI only PROPOSES rows; the admin ticks which to save, and
 * every saved row goes through the same checks as the manual forms and stays unverified until someone verifies it.
 */
class QualityAiService
{
    public const ACTIONS = ['create' => 'Create', 'missing' => 'Missing Info', 'update' => 'Update'];
    public const KINDS = ['rankings' => 'Rankings', 'accreditations' => 'Accreditations', 'approvals' => 'Statutory approvals & recognitions'];

    /**
     * What Fill by AI may look for at each level. Rankings (NIRF, QS...) and accreditations (NAAC...) belong to the ORGANISATION: a campus
     * shows them from its organisation and is never filled by AI. A campus keeps statutory approvals (AICTE / NMC / PCI... are issued per
     * campus address, with their own intake).
     */
    public static function kindsFor(string $type): array
    {
        return $type === 'campus' ? ['approvals' => self::KINDS['approvals']] : self::KINDS;
    }

    /**
     * Each body's OWN official website(s). A record is accepted only when its source is on one of these (or on the website saved for the
     * body in Quality > Masters): never the college's website, news or education portals.
     */
    public const OFFICIAL_SITES = [
        'NIRF' => ['nirfindia.org'],
        'QS' => ['topuniversities.com', 'qs.com'],
        'THE' => ['timeshighereducation.com'],
        'India Today' => ['indiatoday.in'],
        'Outlook' => ['outlookindia.com'],
        'The Week' => ['theweek.in'],
        'EW' => ['educationworld.in'],
        'NAAC' => ['naac.gov.in'],
        'NBA' => ['nbaind.org'],
        'UGC' => ['ugc.gov.in', 'ugc.ac.in'],
        'AICTE' => ['aicte-india.org', 'aicte.gov.in'],
        'NMC' => ['nmc.org.in'],
        'BCI' => ['barcouncilofindia.org'],
        'PCI' => ['pci.nic.in', 'pci.gov.in'],
        'DCI' => ['dciindia.gov.in'],
        'COA' => ['coa.gov.in'],
        'NCTE' => ['ncte.gov.in', 'ncte-india.org'],
        'INC' => ['indiannursingcouncil.org'],
        'AIU' => ['aiu.ac.in'],
        'ICAR' => ['icar.org.in'],
        'AACSB' => ['aacsb.edu'],
        'AMBA' => ['associationofmbas.com'],
        'ABET' => ['abet.org'],
    ];

    public function __construct(private GeminiOrganisationScraperService $ai) {}

    /** The official domains of a ranking or accreditation body (built-in list + the website saved on the body). */
    public function officialDomains($body): array
    {
        $domains = self::OFFICIAL_SITES[$body->short_name] ?? self::OFFICIAL_SITES[$body->name] ?? [];
        if ($body->website && ($h = parse_url((string) $body->website, PHP_URL_HOST))) $domains[] = preg_replace('/^www\./i', '', strtolower($h));
        return array_values(array_unique($domains));
    }

    /** Whether a URL is on one of the domains (or a sub-domain of one, e.g. facilities.aicte-india.org). */
    public function onDomains(?string $url, array $domains): bool
    {
        $host = strtolower((string) parse_url((string) $url, PHP_URL_HOST));
        if ($host === '' || !$domains) return false;
        foreach ($domains as $d) {
            if ($host === $d || str_ends_with($host, '.' . $d)) return true;
        }
        return false;
    }

    // ---------------------------------------------------------------- facts about the entity

    /** The official website to start from: the entity's own, else its organisation's. */
    public function defaultUrl(Model $entity, string $type): string
    {
        $org = Levels::organisationOf($entity);
        $own = match ($type) {
            'organisation' => $entity->official_website ?? null,
            'campus' => $entity->campus_website ?? null,
            'department' => $entity->department_website_url ?? null,
            default => null,
        };
        $url = trim((string) ($own ?: ($org->official_website ?? '')));
        return filter_var($url, FILTER_VALIDATE_URL) ? $url : '';
    }

    /** What is already recorded on this entity, per kind, in the shape the command and the review table use. */
    public function existing(string $type, string $id): array
    {
        return [
            'rankings' => EntityRanking::forEntity($type, $id)->with(['body', 'category'])->orderByDesc('ranking_year')->get()->map(fn ($r) => [
                'id' => $r->id, 'body' => $r->body->short_name ?: $r->body->name, 'category' => $r->category->name, 'year' => $r->ranking_year,
                'rank' => $r->rank_exact, 'band' => $r->rank_band_from ? $r->rank_band_from . '-' . $r->rank_band_to : null, 'score' => $r->score,
                'source_url' => $r->source_url, 'verification' => $r->verification_status,
            ])->all(),
            'accreditations' => EntityAccreditation::forEntity($type, $id)->with('body')->orderByDesc('valid_to')->get()->map(fn ($a) => [
                'id' => $a->id, 'body' => $a->body->short_name, 'grade' => $a->grade, 'score' => $a->score, 'cycle' => $a->cycle,
                'valid_from' => optional($a->valid_from)->format('Y-m-d'), 'valid_to' => optional($a->valid_to)->format('Y-m-d'), 'status' => $a->status,
                'certificate_number' => $a->certificate_number, 'source_url' => $a->source_url, 'verification' => $a->verification_status,
            ])->all(),
            'approvals' => EntityApproval::forEntity($type, $id)->with('body')->orderByDesc('academic_year')->get()->map(fn ($p) => [
                'id' => $p->id, 'body' => $p->body->short_name, 'academic_year' => $p->academic_year, 'approval_number' => $p->approval_number,
                'sanctioned_intake' => $p->sanctioned_intake, 'valid_from' => optional($p->valid_from)->format('Y-m-d'), 'valid_to' => optional($p->valid_to)->format('Y-m-d'),
                'status' => $p->status, 'source_url' => $p->source_url, 'verification' => $p->verification_status,
            ])->all(),
        ];
    }

    /** Bodies and categories that may be recorded at this level (the command must use these exact names). */
    public function masters(string $type): array
    {
        $rankBodies = RankingBody::where('status', true)->orderBy('sort_order')->get();
        $cats = RankingCategory::where('status', true)->where('name', '!=', 'Unspecified category')->orderBy('sort_order')->orderBy('name')->get()
            ->filter(fn ($c) => $c->allowsLevel($type))->groupBy('ranking_body_id');
        $accBodies = AccreditationBody::where('status', true)->orderBy('sort_order')->get()->filter(fn ($b) => $b->allowsLevel($type))->values();
        return [
            'ranking_bodies' => $rankBodies->filter(fn ($b) => isset($cats[$b->id]))->values()->map(fn ($b) => [
                'name' => $b->short_name ?: $b->name, 'full_name' => $b->name, 'sites' => $this->officialDomains($b),
                'categories' => $cats[$b->id]->pluck('name')->values()->all(),
            ])->all(),
            'accreditation_bodies' => $accBodies->reject(fn ($b) => $b->isApprovalKind())->values()->map(fn ($b) => [
                'name' => $b->short_name, 'full_name' => $b->name, 'has_grade' => (bool) $b->has_grade, 'grades' => $b->grade_options ?: [],
                'has_validity' => (bool) $b->has_validity, 'sites' => $this->officialDomains($b),
            ])->all(),
            'approval_bodies' => $accBodies->filter(fn ($b) => $b->isApprovalKind())->values()->map(fn ($b) => [
                'name' => $b->short_name, 'full_name' => $b->name, 'kind' => AccreditationBody::KINDS[$b->kind] ?? $b->kind,
                'has_validity' => (bool) $b->has_validity, 'sites' => $this->officialDomains($b),
            ])->all(),
        ];
    }

    /** Who and what the entity is, for the command. */
    protected function identity(Model $entity, string $type): array
    {
        $org = Levels::organisationOf($entity);
        $out = [
            'level' => Levels::LABELS[$type], 'name' => Levels::name($entity),
            'organisation' => $org?->name, 'organisation_short_name' => $org?->short_name,
            'organisation_type' => $org?->organisationType?->title, 'university_type' => $org?->university_type,
            'ownership' => $org?->ownership_type, 'established' => $org?->established_year,
            'head_office' => $org?->head_office_location, 'organisation_website' => $org?->official_website,
            'aishe_code' => $org?->aishe_code, 'nirf_institute_id' => $org?->nirf_institute_id,
        ];
        if ($type === 'campus') {
            $out += ['campus_city' => $entity->city, 'campus_state' => $entity->state, 'campus_address' => $entity->full_address, 'campus_aishe_code' => $entity->aishe_code ?? null, 'campus_nirf_institute_id' => $entity->nirf_institute_id ?? null];
        }
        if ($type === 'department') {
            $out += ['campus' => $entity->campus?->campus_name, 'department_type' => $entity->academic_unit_type ?? null];
        }
        if ($type === 'course') {
            $out += ['campus' => $entity->campus?->campus_name, 'department' => $entity->department?->department_name,
                'programme_level' => $entity->programLevel?->name ?? null, 'master_course' => $entity->course?->name ?? null];
        }
        return array_filter($out, fn ($v) => $v !== null && $v !== '');
    }

    // ---------------------------------------------------------------- the master command

    public function buildCommand(Model $entity, string $type, string $action, array $kinds, bool $search, string $url, string $websiteText = ''): string
    {
        $allowed = array_keys(self::kindsFor($type));
        $kinds = array_values(array_intersect($allowed, $kinds)) ?: $allowed;
        $id = (string) $entity->getKey();
        $identity = json_encode($this->identity($entity, $type), JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
        $masters = $this->masters($type);
        $existing = array_intersect_key($this->existing($type, $id), array_flip($kinds));
        $existingJson = json_encode($existing, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
        $today = now()->format('Y-m-d');
        $year = (int) now()->format('Y');
        $ay = now()->month >= 6 ? $year . '-' . substr((string) ($year + 1), 2) : ($year - 1) . '-' . substr((string) $year, 2);
        $levelLabel = strtolower(Levels::LABELS[$type]);
        $wanted = implode(', ', array_map(fn ($k) => strtoupper(self::KINDS[$k]), $kinds));

        $site = fn ($b) => ' | OFFICIAL WEBSITE: ' . ($b['sites'] ? implode(', ', $b['sites']) : "the body's own official website");
        $rankBodies = collect($masters['ranking_bodies'])->map(fn ($b) => "     - \"{$b['name']}\" ({$b['full_name']}): categories \"" . implode('", "', $b['categories']) . '"' . $site($b))->implode("\n") ?: '     (none may be recorded at this level)';
        $accBodies = collect($masters['accreditation_bodies'])->map(fn ($b) => "     - \"{$b['name']}\" ({$b['full_name']}): " . ($b['has_grade'] ? 'has a grade' . ($b['grades'] ? ', one of "' . implode('", "', $b['grades']) . '"' : '') : 'NO grade (leave grade null)') . ($b['has_validity'] ? ', has a validity period' : ', no validity period') . $site($b))->implode("\n") ?: '     (none may be recorded at this level)';
        $apprBodies = collect($masters['approval_bodies'])->map(fn ($b) => "     - \"{$b['name']}\" ({$b['full_name']}): {$b['kind']}" . ($b['has_validity'] ? ', has a validity period' : '') . $site($b))->implode("\n") ?: '     (none may be recorded at this level)';

        $actionText = match ($action) {
            'missing' => "MISSING INFO: the records already saved for this {$levelLabel} are listed under EXISTING RECORDS. Return ONLY records that are NOT already there: another ranking body, category or year; an accreditation body, cycle or validity period not yet recorded; an approval body or academic year not yet recorded. Do not repeat an existing record, even with small differences (for corrections the admin uses Update).",
            'update' => "UPDATE: re-check EVERY existing record listed under EXISTING RECORDS against the official source and return it with its \"existing_id\" and the CURRENT correct values (rank or band, grade, score, cycle, validity dates, status, approval number, intake, source URL). Mark an accreditation or approval whose validity has ended as \"expired\". ALSO return any NEWER edition that is not recorded yet (the latest NIRF / QS / THE year, a new NAAC cycle, the current academic year's approval). Do not return an existing record unchanged unless you verified it; say so in \"notes\" (\"verified unchanged\").",
            default => "CREATE: find ALL rankings, accreditations and approvals of this {$levelLabel} (the existing ones are listed only so you can mark them with \"existing_id\" when you find the same record).",
        };

        $searchText = $search
            ? "You MUST research with web search, and take every value ONLY from the issuing body's OWN official website (shown as OFFICIAL WEBSITE next to each body below): its ranking tables, accredited / approved lists, search pages or published PDFs. The college's own website, news sites and education portals (Shiksha, Careers360, Collegedunia, Wikipedia...) may be used ONLY to learn what to look up - NEVER as the source of a value and NEVER as source_url. Search each body separately on its own site (e.g. \"site:nirfindia.org <institution name> 2025\", \"site:naac.gov.in <institution name>\", \"site:nbaind.org <institution name>\", \"site:aicte-india.org <institution name>\") and open the official page."
            : "You may NOT use Google or any other search engine. You MAY open the issuing bodies' OWN official websites directly (your page-opening tool only works on those sites): start from the pages listed under OFFICIAL WEBSITES TO OPEN below and follow their links (ranking tables, accredited / approved lists, published PDFs). Take every value ONLY from those official pages. A mention on the college's own website does NOT count as a source: list it in \"not_found\" instead (e.g. \"NIRF: the college website says rank 55 in 2025 - not confirmed on nirfindia.org\"). If an official site cannot be opened, say so in \"not_found\" (e.g. \"nirfindia.org: page could not be opened\").";

        // where to start on each body's own website (both AIs can only open pages whose address appears in the command or on pages they opened)
        $starts = [];
        foreach (array_merge($masters['ranking_bodies'], $masters['accreditation_bodies'], $masters['approval_bodies']) as $b) {
            foreach ($b['sites'] as $d) $starts[$b['name']][] = 'https://www.' . $d . '/';
        }
        if (isset($starts['NIRF'])) {
            foreach (range($year, $year - 4) as $y) $starts['NIRF'][] = "https://www.nirfindia.org/Rankings/{$y}/Ranking.html";
        }
        $startList = collect($starts)->map(fn ($u, $n) => "   - {$n}: " . implode('  ', array_unique($u)))->implode("\n");
        $openList = "\n=== OFFICIAL WEBSITES TO OPEN (the only acceptable sources) ===\n{$startList}\n";

        $website = $websiteText !== '' ? "\n=== COLLEGE WEBSITE CONTENT (read automatically; ONLY to identify the institution and know what to look up - NEVER a source) ===\n" . mb_substr($websiteText, 0, 9000, 'UTF-8') . "\n=== END OF COLLEGE WEBSITE CONTENT ===\n" : '';

        $levelRules = <<<LEVELS
   - ORGANISATION level: institution-wide records: NIRF Overall / University / Colleges / Research / Innovation and the discipline category the WHOLE institution is ranked in (e.g. Engineering for an NIT, Management for an IIM); QS / THE world, Asia and India rankings; NAAC accreditation of the institution; UGC recognition (2(f) / 12(B) / university listing) and AIU membership; AACSB / AMBA of a standalone business school. Regulator approvals (AICTE / NMC / BCI / PCI / DCI / COA / NCTE / INC) are NEVER recorded here: they belong to the campus and its courses.
   - CAMPUS level: records issued to THIS campus specifically: its regulator approval for this address (AICTE EOA, NMC, BCI, PCI, DCI, COA, NCTE, INC) with the campus's total approved intake per academic year; a campus with its own NAAC accreditation or its own NIRF institute ID. An off-campus centre or a constituent college assessed separately. Do NOT copy the organisation's records down to the campus: the CRM already shows them here from the organisation.
   - DEPARTMENT / SCHOOL level: NBA accreditation of the department's programmes when NBA lists it at department level; AACSB / AMBA / EQUIS / ABET for a business or engineering school; a school ranked separately (e.g. a management school in NIRF Management under its own NIRF ID).
   - COURSE (programme) level: NBA accreditation of THIS programme (tier, validity), ABET for this programme, AMBA for this MBA, and the regulator approval / sanctioned intake of THIS programme for each academic year (AICTE, NMC, PCI, BCI, COA, NCTE, INC, DCI).
   - THIS ENTITY IS AT {$levelLabel} LEVEL: return ONLY records that belong at this level. A record that belongs to the parent (e.g. the university's NAAC grade when you were asked for a department) must NOT be returned.
LEVELS;

        $schema = <<<SCHEMA
{
  "rankings": [
    {"existing_id": null, "body": "NIRF", "category": "Engineering", "year": 2025, "rank": 46, "band": null, "score": 58.21, "total_ranked": 200,
     "external_institute_id": "IR-E-I-1075", "published_on": "2025-09-04", "source_url": "https://www.nirfindia.org/Rankings/2025/EngineeringRanking.html",
     "confidence": "high", "notes": "NIRF 2025 Engineering, rank 46, score 58.21"}
  ],
  "accreditations": [
    {"existing_id": null, "body": "NAAC", "grade": "A", "score": 3.12, "cycle": 1, "valid_from": "2021-11-23", "valid_to": "2026-11-22",
     "status": "active", "certificate_number": "EC(SC)/12/A&A/XX.1", "source_url": "https://www.naac.gov.in/...", "confidence": "high", "notes": "NAAC cycle 1, CGPA 3.12"}
  ],
  "approvals": [
    {"existing_id": null, "body": "AICTE", "academic_year": "2025-26", "approval_number": "F.No. Northern/1-XXXXXXXXXX/2025/EOA", "sanctioned_intake": 540,
     "valid_from": "2025-07-01", "valid_to": "2026-06-30", "status": "active", "source_url": "https://facilities.aicte-india.org/dashboard/pages/...", "confidence": "high", "notes": "EOA 2025-26, total approved intake"}
  ],
  "not_found": ["NAAC: no accreditation found on naac.gov.in for this institution"],
  "sources_checked": ["https://www.nirfindia.org/...", "https://www.naac.gov.in/..."]
}
SCHEMA;

        $mandatory = $search
            ? "MANDATORY FIRST STEP: use web search BEFORE you answer. Run several searches (one per body: NIRF, NAAC, NBA, AICTE, UGC, QS / THE, and any other\nbody listed below that applies) and open the bodies' OFFICIAL pages. An answer given without searching is wrong and will be rejected.\n\n"
            : "MANDATORY FIRST STEP: before you answer, OPEN the official websites listed under OFFICIAL WEBSITES TO OPEN (no search engine: open those\naddresses directly and follow their links). An answer given without opening them is wrong and will be rejected.\n\n";

        return <<<CMD
{$mandatory}You are an expert researcher of Indian higher-education quality records (rankings, accreditations, statutory approvals and recognitions).
Your job: find VERIFIED {$wanted} of ONE entity and return them as JSON for a database. Every value you return will be shown to an admin who
verifies it against your source, so accuracy and exact sources matter more than quantity. Today's date is {$today}; the current academic year is {$ay}.

=== THE ENTITY ===
{$identity}
College website (ONLY to identify the institution - never a source): {$url}

=== WHAT TO DO ===
{$actionText}
Look for: {$wanted}.

=== EXISTING RECORDS (already saved in our database for this entity) ===
{$existingJson}

=== RESEARCH METHOD ===
{$searchText}
Identify the entity correctly first: match the official name, city / state and, when given, the AISHE code and NIRF institute ID. Many institutions have
similar names (e.g. several "NIT"s, "Amity" campuses, "Symbiosis" institutes): never take a record of a different institution or campus.
{$openList}{$website}
=== WHICH LEVEL A RECORD BELONGS TO ===
{$levelRules}

=== 1. RANKINGS (only if asked for above) ===
   Bodies and categories allowed at this level (use these EXACT names in "body" and "category"):
{$rankBodies}
   Rules:
   - NIRF (nirfindia.org): published every year from 2016 in several categories. Look at the LATEST 5 NIRF years. For EACH year, return one entry per
     category the entity appears in. NIRF publishes exact ranks for the top 100 (200 in Engineering / Overall in recent years) and bands such as
     "101-150", "151-200", "201-300" beyond that: return a band in "band" and null in "rank" - never turn a band into a number. Give the NIRF score when
     shown, the NIRF institute ID (e.g. IR-E-U-0123) in "external_institute_id", and the exact ranking page or PDF as source_url.
     The "year" is the NIRF edition year as published ("NIRF 2025" = 2025).
   - QS / THE: use the edition year as the body names it ("QS World University Rankings 2026" = 2026). World, Asia and India / subject tables are different
     categories: use the matching category name from the list; skip a table whose category is not in the list. Return bands as "band" (e.g. "801-850").
   - Media rankings (India Today, Outlook-ICARE, The Week, Education World): only from the publisher's own page; give the stream / category as published
     mapped to the category list; skip if it does not map.
   - Never return a "rank" and a "band" together. Give at least one of rank, band or score.

=== 2. ACCREDITATIONS (only if asked for above) ===
   Bodies allowed at this level (use these EXACT names in "body"):
{$accBodies}
   Rules:
   - NAAC (naac.gov.in only: assessment outcome / accreditation status lists on naac.gov.in and assessmentonline.naac.gov.in): grade EXACTLY as listed (e.g. "A++", "A+", "A",
     "B++"), CGPA in "score", the cycle number in "cycle" (1st cycle = 1), the accreditation period in valid_from / valid_to (the date of the Executive
     Committee decision and the end of validity as stated). A cycle whose validity has ended is "expired". An institution that has applied but has no
     outcome yet is "applied". Return EVERY cycle you can verify (latest first), each as its own entry. If the institution was never accredited by NAAC,
     return nothing for NAAC and say so in "not_found".
   - NBA (nbaind.org, "Accredited programmes" / "Programme-wise accreditation status"): NBA accredits PROGRAMMES (e.g. B.Tech Computer Science), not
     institutions: record it at department or course level only, with the validity period ("valid up to" date) and the tier in "notes". No grade.
   - ABET (abet.org "Find accredited programs"), AACSB, AMBA, EQUIS and ICAR: only when the body's own directory lists this entity; give the accreditation
     date / next review as validity when published. No grade unless the body gives one.
   - status: "active" (valid on {$today}), "expired" (validity ended), "applied" (applied, no outcome), "withdrawn" (withdrawn / revoked).
   - Statutory approvals and recognitions (UGC, AICTE, NMC, BCI, PCI, DCI, COA, NCTE, INC, AIU) are NOT accreditations: put them under "approvals".

=== 3. STATUTORY APPROVALS & RECOGNITIONS (only if asked for above) ===
   Bodies allowed at this level (use these EXACT names in "body"):
{$apprBodies}
   Rules:
   - One entry per body per ACADEMIC YEAR, written "YYYY-YY" with consecutive years (e.g. "2025-26"). Look at the latest 3 academic years.
   - AICTE: the Extension of Approval (EOA) for each year from facilities.aicte-india.org (approval letter number in "approval_number", total approved
     intake in "sanctioned_intake" at organisation / campus level, or the programme's intake at course level). Institutions of National Importance (IIT,
     NIT, IIIT, IISER, AIIMS...) and Central universities do NOT need AICTE approval: return nothing for AICTE and say so in "not_found".
   - UGC: recognition under Section 2(f) / 12(B) or inclusion in the UGC list of universities (ugc.gov.in): the letter / notification number in
     "approval_number"; for a university created by an Act, put the Act and year in "approval_number" (e.g. "NIT Act, 2007") and in "notes".
     Use the academic year of the notification, or the current academic year {$ay} for a standing recognition, and say which in "notes".
   - NMC (seat matrix / list of colleges), PCI (approved institutions with intake), BCI (approved centres of legal education), DCI, COA (approved
     institutions and intake), NCTE (recognition order), INC (nursing institutions): approval / order number, intake and validity as published.
   - AIU: membership of the Association of Indian Universities (aiu.ac.in member list).
   - status: "active", "expired", "applied" or "withdrawn" as of {$today}.

=== ACCURACY RULES (apply to every entry) ===
   - NEVER invent or estimate a rank, band, grade, score, date, number or intake. If you cannot verify a value, leave it null; if you cannot verify the
     record at all, do not return it - list it in "not_found" instead.
   - Every entry MUST have "source_url": the exact page or PDF ON THE ISSUING BODY'S OWN OFFICIAL WEBSITE (shown as OFFICIAL WEBSITE above) that
     shows it. A source_url on the college's website, a news site, an education portal, Wikipedia or a search page is NOT accepted and the entry
     will be rejected: do not return it - list it in "not_found" with what you saw and where.
   - "confidence": "high" (seen on the official source), "medium" (official institution page or reputable news, official source not reachable),
     "low" (only secondary sources - avoid; prefer leaving it out).
   - Dates as "YYYY-MM-DD" (use the 1st of the month when only month and year are published, and say so in "notes"). Numbers as plain numbers.
   - "existing_id": the id from EXISTING RECORDS when your entry is the SAME record (same body + category + year for a ranking; same body + cycle or
     validity for an accreditation; same body + academic year for an approval); otherwise null.
   - "notes": one short line: what the source says, and anything the admin should check.

=== RETURN FORMAT ===
Reply with ONLY one valid JSON object (no text before or after), with exactly these keys - use [] for a kind that was not asked for or has nothing:
{$schema}
CMD;
    }

    // ---------------------------------------------------------------- run, compare, save

    // ---------------------------------------------------------------- saved results (CRM reference only)

    /** The latest saved AI results of this record (newest first); empty when the quality_ai_logs table is not there yet. */
    public static function logs(string $type, string $id, int $limit = 20): array
    {
        try {
            $rows = \Illuminate\Support\Facades\DB::table('quality_ai_logs as l')->leftJoin('admin as a', 'a.id', '=', 'l.admin_id')
                ->where('l.entity_type', $type)->where('l.entity_id', $id)->orderByDesc('l.id')->limit($limit)->get(['l.*', 'a.name as admin_name']);
        } catch (\Throwable $e) {
            return [];
        }
        return $rows->map(fn ($l) => self::presentLog($l, $l->admin_name ?? null))->all();
    }

    /** One saved result, ready for the page. */
    public static function presentLog(object $l, ?string $adminName): array
    {
        $list = fn ($v) => is_array($d = json_decode((string) $v, true)) ? $d : [];
        $engines = ['gemini' => 'Gemini', 'claude' => 'Claude', 'claude_gemini' => 'Claude + Gemini', 'gemini_claude' => 'Gemini + Claude'];
        $kinds = self::kindsFor((string) $l->entity_type) + ['rankings' => 'Rankings', 'accreditations' => 'Accreditations', 'approvals' => 'Statutory approvals'];
        return [
            'id' => (int) $l->id,
            'when' => \Illuminate\Support\Carbon::parse($l->created_at)->format('d M Y, h:i A'),
            'by' => $adminName,
            'action' => self::ACTIONS[$l->action] ?? $l->action,
            'engine' => $engines[$l->engine] ?? $l->engine,
            'kinds' => array_map(fn ($k) => $kinds[$k] ?? $k, $list($l->kinds)),
            'steps' => $list($l->steps),
            'notes' => $list($l->notes),
            'rows' => $list($l->rows),
            'sources' => $list($l->sources),
        ];
    }

    /** Runs the command and returns the proposals for the review table. */
    public const ENGINE_LABELS = ['claude' => 'Claude', 'gemini' => 'Gemini'];

    /**
     * One AI step. $previous (the raw result of a first AI) makes this the SECOND step of "Claude + Gemini" / "Gemini + Claude": the command then
     * also lists what the first AI found and what it could not finish, and this AI works only on those gaps; both results are merged.
     */
    public function run(Model $entity, string $type, string $action, array $kinds, bool $search, string $url, string $engine, ?string $customCommand = null, ?array $previous = null): array
    {
        // only what this level may be filled with (a campus: approvals only), even when the admin edited the command
        $kinds = array_values(array_intersect(array_keys(self::kindsFor($type)), $kinds)) ?: array_keys(self::kindsFor($type));
        $this->ai->useEngine($engine);
        $label = self::ENGINE_LABELS[$engine] ?? $engine;
        $command = $customCommand ?: $this->buildCommand($entity, $type, $action, $kinds, $search, $url, $url ? $this->ai->readWebsite($url) : '');
        if ($previous) {
            $command .= $this->followupSection($previous);
        }
        // one search per body and year adds up: allow up to 15 (Claude); Gemini searches Google as it needs
        // the AI may open pages ONLY on the official websites of the bodies allowed at this level (with or without Google search)
        $m = $this->masters($type);
        $domains = collect(array_merge($m['ranking_bodies'], $m['accreditation_bodies'], $m['approval_bodies']))->pluck('sites')->flatten()->unique()->values()->all();
        \App\Services\ClaudeExtractionService::$blockedDomains = [];
        $json = $this->ai->runJsonCommand($command, ['rankings', 'accreditations', 'approvals'], $search, 15, $domains);

        // this step's result, each row and note marked with the AI that produced it
        $raw = ['rankings' => [], 'accreditations' => [], 'approvals' => [], 'not_found' => [], 'by' => [$label]];
        foreach (['rankings', 'accreditations', 'approvals'] as $k) {
            foreach ((array) ($json[$k] ?? []) as $r) if (is_array($r)) $raw[$k][] = ['_by' => $label] + $r;
        }
        foreach ((array) ($json['not_found'] ?? []) as $n) if (is_string($n) && trim($n) !== '') $raw['not_found'][] = "{$label}: {$n}";
        foreach (\App\Services\ClaudeExtractionService::$blockedDomains as $d) {
            $raw['not_found'][] = "{$label}: {$d}: this website does not allow Claude to open its pages, so it was skipped" . ($previous ? '.' : ' (Gemini can try it).');
        }
        $merged = $previous ? $this->mergeRaw($previous, $raw) : $raw;

        return $this->propose($merged, $type, (string) $entity->getKey(), $kinds) + [
            'not_found' => $merged['not_found'],
            'sources_checked' => array_values(array_filter((array) ($json['sources_checked'] ?? []), 'is_string')),
            'raw' => $merged,
            // why a second AI should continue (Claude + Gemini / Gemini + Claude); empty = this result is complete
            'followup_reasons' => $previous ? [] : $this->followupReasons($merged, $kinds),
        ];
    }

    /**
     * When the second AI of "Claude + Gemini" / "Gemini + Claude" should take over. The first AI's own notes tell what it could not finish:
     * a page / search limit, a site that refused it, items it did not check or could not verify, or nothing found at all.
     * (A first AI that fails with an error is handled by the pop-up: the second AI then runs the whole command.)
     */
    public function followupReasons(array $raw, array $kinds): array
    {
        $reasons = [];
        $notes = implode("\n", $raw['not_found'] ?? []);
        if (preg_match('/\b(limit|max(imum)? uses|ran out|quota|budget)\b/i', $notes)) $reasons[] = 'it reached its page / search limit';
        if (preg_match('/(does not allow|not accessible|blocked|could not (be )?open|couldn\'t open|unable to (open|access|reach)|timed? ?out|failed to (load|open))/i', $notes)) $reasons[] = 'some official websites could not be opened';
        if (preg_match('/\b(not (been )?checked|not verified|unverified|did not (check|confirm|verify|get to)|not confirm|truncated|skipped)\b/i', $notes)) $reasons[] = 'it left items not checked or not verified';
        $found = 0;
        foreach ($kinds as $k) $found += count($raw[$k] ?? []);
        if ($found === 0) $reasons[] = 'it found nothing';
        return array_values(array_unique($reasons));
    }

    /** The extra part of the command for the SECOND AI: what the first one found and what is left. */
    protected function followupSection(array $previous): string
    {
        $first = implode(' + ', (array) ($previous['by'] ?? ['the first AI']));
        $found = [];
        foreach (['rankings', 'accreditations', 'approvals'] as $k) {
            foreach ((array) ($previous[$k] ?? []) as $r) {
                $found[] = '   - ' . $k . ': ' . implode(' | ', array_filter([
                    $r['body'] ?? null, $r['category'] ?? null, $r['year'] ?? ($r['academic_year'] ?? null),
                    isset($r['rank']) ? '#' . $r['rank'] : ($r['band'] ?? ($r['grade'] ?? null)), $r['source_url'] ?? null,
                ], fn ($v) => $v !== null && $v !== ''));
            }
        }
        $left = array_map(fn ($n) => '   - ' . $n, (array) ($previous['not_found'] ?? []));
        $foundText = $found ? implode("\n", $found) : '   (nothing)';
        $leftText = $left ? implode("\n", $left) : '   (nothing listed)';

        return <<<FOLLOWUP


=== FOLLOW-UP: ANOTHER AI ({$first}) ALREADY WORKED ON THIS COMMAND ===
You are the SECOND AI. You were called because the first one did not finish, in one of these cases:
   1. it reached its page or search limit before checking everything;
   2. an official website refused it or could not be opened (e.g. indiatoday.in refuses Claude);
   3. it reported items as "not checked", "not verified" or "skipped";
   4. it found nothing at all.
ALREADY FOUND by the first AI (do NOT return these again unless you can correct a value from the official source - then return the corrected entry):
{$foundText}
WHAT THE FIRST AI COULD NOT FINISH (work on exactly these):
{$leftText}
Rules for you:
   - Research ONLY the items above that are not finished, plus anything the command asks for that is in neither list.
   - Skip an item the first AI CONFIRMED does not exist on the official website (e.g. "not ranked by NIRF in 2024 (checked on nirfindia.org)",
     "not accredited by NAAC (naac.gov.in)"): do not research it again.
   - All the rules above still apply: values only from the issuing body's own official website, exact source_url, never the college website.
   - In "not_found", list only what YOU still could not find or confirm, and why.
FOLLOWUP;
    }

    /** First + second AI results together: the same record from both is kept once (the first, unless only the second has a source). */
    protected function mergeRaw(array $a, array $b): array
    {
        $key = [
            'rankings' => fn ($r) => strtolower(($r['body'] ?? '') . '|' . ($r['category'] ?? '') . '|' . ($r['year'] ?? '')),
            'accreditations' => fn ($r) => strtolower(($r['body'] ?? '') . '|' . ($r['cycle'] ?? '') . '|' . ($r['valid_from'] ?? '')),
            'approvals' => fn ($r) => strtolower(($r['body'] ?? '') . '|' . ($r['academic_year'] ?? '')),
        ];
        $out = ['not_found' => array_values(array_merge((array) ($a['not_found'] ?? []), (array) ($b['not_found'] ?? []))),
            'by' => array_values(array_unique(array_merge((array) ($a['by'] ?? []), (array) ($b['by'] ?? []))))];
        foreach ($key as $k => $fn) {
            $rows = [];
            foreach (array_merge((array) ($a[$k] ?? []), (array) ($b[$k] ?? [])) as $r) {
                if (!is_array($r)) continue;
                $id = $fn($r);
                if (!isset($rows[$id]) || (empty($rows[$id]['source_url']) && !empty($r['source_url']))) $rows[$id] = $r;
            }
            $out[$k] = array_values($rows);
        }
        return $out;
    }

    /**
     * Turns the AI's JSON into review rows: each with the values to save, what it matches ("new", "update" with the existing id, or "same"),
     * and a problem when the CRM could not accept it (unknown body, category not allowed at this level...).
     */
    public function propose(array $json, string $type, string $id, array $kinds): array
    {
        $existing = $this->existing($type, $id);
        $m = $this->masters($type);
        $rankBodies = RankingBody::where('status', true)->get();
        $accBodies = AccreditationBody::where('status', true)->get();
        $cats = RankingCategory::where('status', true)->get();
        $str = fn ($v) => is_scalar($v) && trim((string) $v) !== '' ? trim((string) $v) : null;
        $num = fn ($v) => is_numeric($v) ? $v + 0 : (is_string($v) && preg_match('/^\s*([\d.]+)/', $v, $x) ? $x[1] + 0 : null);
        $date = function ($v) use ($str) { $v = $str($v); if (!$v) return null; $t = strtotime($v); return $t ? date('Y-m-d', $t) : null; };
        $findBody = fn ($list, $name) => $name === null ? null : $list->first(fn ($b) => strcasecmp((string) $b->short_name, $name) === 0 || strcasecmp((string) $b->name, $name) === 0);
        $out = ['rankings' => [], 'accreditations' => [], 'approvals' => []];

        if (in_array('rankings', $kinds, true)) {
            foreach ((array) ($json['rankings'] ?? []) as $r) {
                if (!is_array($r)) continue;
                $body = $findBody($rankBodies, $str($r['body'] ?? null));
                $cat = $body ? $cats->first(fn ($c) => $c->ranking_body_id === $body->id && strcasecmp($c->name, (string) $str($r['category'] ?? '')) === 0) : null;
                $rank = $num($r['rank'] ?? null);
                $band = null;
                if (preg_match('/(\d+)\s*[-–]\s*(\d+)/', (string) ($r['band'] ?? ''), $b) && (int) $b[2] >= (int) $b[1]) $band = [(int) $b[1], (int) $b[2]];
                if ($rank && $band) $band = null;
                $row = [
                    'body' => $str($r['body'] ?? null), 'category' => $str($r['category'] ?? null), 'year' => (int) $num($r['year'] ?? 0) ?: null,
                    'rank' => $rank ? (int) $rank : null, 'band' => $band ? $band[0] . '-' . $band[1] : null, 'score' => $num($r['score'] ?? null),
                    'source_url' => $str($r['source_url'] ?? null), 'confidence' => $str($r['confidence'] ?? null), 'notes' => $str($r['notes'] ?? null), 'found_by' => $str($r['_by'] ?? null),
                    'values' => [
                        'ranking_body_id' => $body?->id, 'ranking_category_id' => $cat?->id, 'ranking_year' => (int) $num($r['year'] ?? 0) ?: null,
                        'rank_exact' => $rank ? (int) $rank : null, 'rank_band_from' => $band[0] ?? null, 'rank_band_to' => $band[1] ?? null,
                        'score' => $num($r['score'] ?? null), 'total_ranked' => ($t = $num($r['total_ranked'] ?? null)) ? (int) $t : null,
                        'external_institute_id' => $str($r['external_institute_id'] ?? null), 'published_on' => $date($r['published_on'] ?? null),
                        'source_url' => filter_var($str($r['source_url'] ?? null), FILTER_VALIDATE_URL) ?: null,
                        'notes' => $this->aiNote($r),
                    ],
                ];
                $row['problem'] = !$body ? 'Ranking body "' . $row['body'] . '" is not in the CRM list.'
                    : (!$cat ? 'Category "' . $row['category'] . '" is not a ' . ($body->short_name ?: $body->name) . ' category allowed here.'
                    : (!$cat->allowsLevel($type) ? 'This category cannot be recorded at this level.'
                    : $this->sourceProblem($row['source_url'], $body)));
                $match = collect($existing['rankings'])->first(fn ($e) => $e['body'] === ($body?->short_name ?: $body?->name) && $e['category'] === $cat?->name && (int) $e['year'] === (int) $row['year']);
                $row += $this->matchState($match, fn ($e) => (int) $e['rank'] === (int) $row['rank'] && $e['band'] === $row['band']);
                $out['rankings'][] = $row;
            }
        }

        foreach (['accreditations' => false, 'approvals' => true] as $kind => $approvalKind) {
            if (!in_array($kind, $kinds, true)) continue;
            foreach ((array) ($json[$kind] ?? []) as $r) {
                if (!is_array($r)) continue;
                $body = $findBody($accBodies, $str($r['body'] ?? null));
                $status = strtolower((string) $str($r['status'] ?? 'active'));
                $status = array_key_exists($status, EntityAccreditation::STATUSES) ? $status : 'active';
                $values = $approvalKind ? [
                    'accreditation_body_id' => $body?->id, 'academic_year' => $this->academicYear($r['academic_year'] ?? null),
                    'approval_number' => $str($r['approval_number'] ?? null), 'sanctioned_intake' => ($i = $num($r['sanctioned_intake'] ?? null)) !== null ? (int) $i : null,
                    'valid_from' => $date($r['valid_from'] ?? null), 'valid_to' => $date($r['valid_to'] ?? null), 'status' => $status,
                    'source_url' => filter_var($str($r['source_url'] ?? null), FILTER_VALIDATE_URL) ?: null, 'notes' => $this->aiNote($r),
                ] : [
                    'accreditation_body_id' => $body?->id, 'grade' => $body && $body->has_grade ? $str($r['grade'] ?? null) : null,
                    'score' => $num($r['score'] ?? null), 'cycle' => ($c = $num($r['cycle'] ?? null)) ? (int) $c : null,
                    'valid_from' => $date($r['valid_from'] ?? null), 'valid_to' => $body && !$body->has_validity ? null : $date($r['valid_to'] ?? null), 'status' => $status,
                    'certificate_number' => $str($r['certificate_number'] ?? null),
                    'source_url' => filter_var($str($r['source_url'] ?? null), FILTER_VALIDATE_URL) ?: null, 'notes' => $this->aiNote($r),
                ];
                $row = ['body' => $str($r['body'] ?? null), 'source_url' => $str($r['source_url'] ?? null), 'confidence' => $str($r['confidence'] ?? null),
                    'notes' => $str($r['notes'] ?? null), 'found_by' => $str($r['_by'] ?? null), 'values' => $values] + ($approvalKind
                        ? ['academic_year' => $values['academic_year'], 'approval_number' => $values['approval_number'], 'sanctioned_intake' => $values['sanctioned_intake'], 'valid_to' => $values['valid_to'], 'status' => $status]
                        : ['grade' => $values['grade'], 'score' => $values['score'], 'cycle' => $values['cycle'], 'valid_from' => $values['valid_from'], 'valid_to' => $values['valid_to'], 'status' => $status]);
                $row['problem'] = !$body ? 'Body "' . $row['body'] . '" is not in the CRM list.'
                    : (!$body->allowsLevel($type) ? $body->short_name . ' cannot be recorded at this level.'
                    : ($body->isApprovalKind() !== $approvalKind ? $body->short_name . ' belongs under ' . ($approvalKind ? 'Accreditations' : 'Approvals') . '.'
                    : ($approvalKind && !$values['academic_year'] ? 'No academic year.'
                    : (!$approvalKind && $values['grade'] !== null && !empty($body->grade_options) && !in_array($values['grade'], $body->grade_options, true) ? 'Grade "' . $values['grade'] . '" is not a ' . $body->short_name . ' grade.'
                    : $this->sourceProblem($row['source_url'], $body)))));
                $name = $body?->short_name;
                $match = $approvalKind
                    ? collect($existing['approvals'])->first(fn ($e) => $e['body'] === $name && $e['academic_year'] === $values['academic_year'])
                    : collect($existing['accreditations'])->first(fn ($e) => $e['body'] === $name && (($values['cycle'] && (int) $e['cycle'] === $values['cycle']) || ($values['valid_from'] && $e['valid_from'] === $values['valid_from'])));
                $row += $this->matchState($match, $approvalKind
                    ? fn ($e) => (string) $e['approval_number'] === (string) $values['approval_number'] && (int) $e['sanctioned_intake'] === (int) $values['sanctioned_intake'] && $e['status'] === $status
                    : fn ($e) => (string) $e['grade'] === (string) $values['grade'] && $e['valid_to'] === $values['valid_to'] && $e['status'] === $status);
                $out[$kind][] = $row;
            }
        }
        return ['proposals' => $out];
    }

    /** Why a row cannot be saved because of its source: none given, or not on the body's own official website. Null when the source is fine. */
    protected function sourceProblem(?string $url, $body): ?string
    {
        $domains = $this->officialDomains($body);
        $name = $body->short_name ?: $body->name;
        if (!$url || !filter_var($url, FILTER_VALIDATE_URL)) return 'No source link on ' . $name . "'s official website.";
        if ($domains && !$this->onDomains($url, $domains)) {
            return 'Source is not ' . $name . "'s official website (" . implode(' / ', $domains) . '): ' . (parse_url($url, PHP_URL_HOST) ?: $url) . '.';
        }
        return null;
    }

    /** "new", or "update" / "same" against the existing record it matches. */
    protected function matchState(?array $match, callable $same): array
    {
        if (!$match) return ['state' => 'new', 'existing_id' => null];
        return ['state' => $same($match) ? 'same' : 'update', 'existing_id' => $match['id'], 'existing' => $match];
    }

    protected function academicYear($v): ?string
    {
        if (!preg_match('/(\d{4})\s*[-–\/]\s*(\d{2,4})/', (string) $v, $m)) {
            return preg_match('/^\d{4}$/', trim((string) $v)) ? $v . '-' . substr((string) ((int) $v + 1), 2) : null;
        }
        $from = (int) $m[1];
        $to = (int) substr($m[2], -2);
        return ($from + 1) % 100 === $to ? $from . '-' . str_pad((string) $to, 2, '0', STR_PAD_LEFT) : null;
    }

    protected function aiNote(array $r): string
    {
        $c = trim((string) ($r['confidence'] ?? ''));
        return trim('Found by AI' . ($c ? " ({$c} confidence)" : '') . '. ' . mb_substr(trim((string) ($r['notes'] ?? '')), 0, 1500));
    }

    /**
     * Saves the rows the admin ticked: new ones are created, "update" ones change the existing record. Each goes through the same checks
     * as the manual forms. Returns per row: saved / updated / skipped with the reason.
     */
    public function save(array $rows, string $type, string $id): array
    {
        $records = app(RecordController::class);
        $results = [];
        foreach (array_slice($rows, 0, 200) as $i => $row) {
            $kind = $row['kind'] ?? '';
            $values = is_array($row['values'] ?? null) ? $row['values'] : [];
            $existingId = $row['existing_id'] ?? null;
            $label = $row['label'] ?? ('Row ' . ($i + 1));
            try {
                [$model, $check] = match ($kind) {
                    'rankings' => [EntityRanking::class, 'checkedRanking'],
                    'accreditations' => [EntityAccreditation::class, 'checkedAccreditation'],
                    'approvals' => [EntityApproval::class, 'checkedApproval'],
                    default => throw new \InvalidArgumentException('Unknown kind.'),
                };
                $existing = $existingId ? $model::forEntity($type, $id)->find($existingId) : null;
                if ($existingId && !$existing) throw new \InvalidArgumentException('The record it would update no longer exists.');
                if ($existing) {
                    // keep the existing note and evidence; add the AI's note
                    $values['notes'] = trim(($existing->notes ? $existing->notes . "\n" : '') . 'Updated by AI on ' . now()->format('d M Y') . ': ' . ($values['notes'] ?? ''));
                    $existing->update($records->{$check}($values, $type, $id, $existing));
                    $results[] = ['label' => $label, 'result' => 'updated'];
                } else {
                    $model::create($records->{$check}($values, $type, $id) + ['entity_type' => $type, 'entity_id' => (string) $id, 'created_by' => auth('admin')->id()]);
                    $results[] = ['label' => $label, 'result' => 'saved'];
                }
            } catch (ValidationException $e) {
                $results[] = ['label' => $label, 'result' => 'skipped', 'reason' => implode(' ', collect($e->errors())->flatten()->all())];
            } catch (\Throwable $e) {
                $results[] = ['label' => $label, 'result' => 'skipped', 'reason' => $e->getMessage()];
            }
        }
        return $results;
    }
}

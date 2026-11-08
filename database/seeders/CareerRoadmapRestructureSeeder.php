<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

/**
 * One-off content pass on the Career Roadmap module (run by the 2026_10_27_100100 migration; safe to run again):
 * orders and types every roadmap and item, moves the "Badge" / "Salary" custom fields into the fee and salary fields,
 * links items to the Exam, Course and Scholarship modules, fixes the content errors found in the audit, writes the
 * stream details of the Class 11 streams and real content for the Class 5 template roadmaps, and fills the Careers
 * master. A record is only changed when its title still matches, so other databases with other ids are left alone.
 */
class CareerRoadmapRestructureSeeder extends Seeder
{
    private const T = 'career_roadmap_sub_modules';
    private array $log = [];

    /** Roadmap id => [title it must start with, roadmap type, order within the class]. */
    private const ROADMAPS = [
        // Class 5
        447 => ['School Entrance Tests', 'school_entrance', 10], 1 => ['Defence track', 'school_entrance', 20], 2 => ['Science & technology', 'timeline', 30],
        3 => ['Olympiad / talent', 'scholarships', 40], 4 => ['Still exploring', 'timeline', 50],
        // Class 6
        212 => ['Defence exam', 'school_entrance', 10], 213 => ['Olympiad Exams', 'scholarships', 20], 214 => ['Scholarship Exams', 'scholarships', 30],
        260 => ['Science & Technology', 'skills', 40], 442 => ['Sports Activity', 'sports', 50],
        // Class 7
        230 => ['Defence Services Examination', 'school_entrance', 10], 232 => ['Olympiad Exams', 'scholarships', 20], 233 => ['Scholarship Exams', 'scholarships', 30],
        235 => ['Coding & Technology', 'skills', 40], 234 => ['Sports Opportunities', 'sports', 50],
        // Class 8
        270 => ['Entrance Exams', 'school_entrance', 10], 269 => ['Scholarship Exam', 'scholarships', 20], 272 => ['Foundation courses', 'exams', 30],
        271 => ['Aptitude Building', 'skills', 40], 291 => ['Sports Opportunities', 'sports', 50],
        // Class 9
        299 => ['Competitive Exams', 'school_entrance', 10], 300 => ['Olympiads', 'scholarships', 20], 297 => ['Scholarship', 'scholarships', 30],
        302 => ['Skill Development', 'skills', 40], 301 => ['Sports & Leadership', 'sports', 50],
        // Class 10
        378 => ['Stream Selection', 'stream', 10], 499 => ['Top Career Options', 'careers', 20], 328 => ['Competitive Exams Preparation', 'exams', 30],
        333 => ['Scholarship Exams', 'scholarships', 40], 489 => ['Government Exams', 'govt_jobs', 50], 346 => ['Skill Development Courses', 'skills', 60], 341 => ['Sports', 'sports', 70],
        // Class 11: the streams first
        638 => ['PCM', 'stream', 10], 637 => ['PCB', 'stream', 20], 639 => ['PCMB', 'stream', 30], 640 => ['Commerce', 'stream', 40], 641 => ['Arts', 'stream', 50],
        642 => ['Vocational Stream', 'stream', 60], 352 => ['Competitive Exams Preparation', 'exams', 70], 359 => ['International Exams', 'study_abroad', 80],
        365 => ['Leadership & Defence Training', 'sports', 90], 371 => ['sports activity', 'sports', 100],
        // Class 12
        11 => ['Science — engineering', 'career_path', 10], 12 => ['Science — medical', 'career_path', 20], 13 => ['Commerce', 'career_path', 30], 14 => ['Arts / humanities', 'career_path', 40],
        539 => ['Government Job Opportunities', 'govt_jobs', 50], 527 => ['Professional courses', 'skills', 60], 450 => ['Switch Streams', 'switch_stream', 70],
        // Graduation
        547 => ['Engineering', 'career_path', 10], 20 => ['SCIENCE', 'career_path', 20], 19 => ['Management / Commerce', 'career_path', 30], 21 => ['Law', 'career_path', 40],
        22 => ['Arts & Humanities', 'career_path', 50], 24 => ['Design & Creative', 'career_path', 60], 23 => ['Hospitality', 'career_path', 70], 459 => ['Study Mode', 'study_mode', 80], 465 => ['Dual Degree', 'study_mode', 90],
        // Post graduation
        411 => ['Science / Engineering', 'career_path', 10], 418 => ['Commerce / Management', 'career_path', 20], 424 => ['Law / Humanities', 'career_path', 30],
        434 => ['Entrance Exam', 'exams', 40], 483 => ['Study Mode', 'study_mode', 50], 476 => ['Dual Degree', 'study_mode', 60],
    ];

    public function run(): void
    {
        DB::transaction(function () {
            $this->order();
            $this->careers();
            $this->fixes();
            $this->classFive();
            $this->types();
            $this->facts();
            $this->links();
            $this->streams();
            $this->slugs();
            $this->masterCourses();
            $this->pageContent();
        });
        if ($this->command) foreach ($this->log as $l) $this->command->warn($l);
    }

    // ------------------------------------------------------------------ helpers

    /** The row, when its title still starts with $title (case-insensitive); else null, noted in the log. */
    private function row(int $id, string $title): ?object
    {
        $r = DB::table(self::T)->where('id', $id)->first();
        if (!$r || stripos(trim($r->title), $title) !== 0) { $this->log[] = "skipped #$id (expected \"$title\")"; return null; }
        return $r;
    }

    private function set(int $id, string $title, array $data): void
    {
        if ($this->row($id, $title)) DB::table(self::T)->where('id', $id)->update($data + ['updated_at' => now()]);
    }

    private function lines(array $l): string
    {
        return implode("\n", $l);
    }

    /** Adds an item under a parent, unless one with this title is already there; returns its id. */
    private function add(int $parent, string $title, array $data = [], int $sort = 0): int
    {
        $p = DB::table(self::T)->where('id', $parent)->first();
        $have = DB::table(self::T)->where('parent_id', $parent)->where('title', $title)->value('id');
        if ($have) { DB::table(self::T)->where('id', $have)->update($data + ['status' => 1, 'sort_order' => $sort]); return (int) $have; }
        $slug = Str::slug($title) ?: 'item';
        for ($s = $slug, $i = 2; DB::table(self::T)->where('slug', $s)->exists(); $i++) $s = $slug . '-' . $i;
        return (int) DB::table(self::T)->insertGetId($data + ['stage_id' => $p->stage_id, 'parent_id' => $parent, 'title' => $title, 'slug' => $s, 'status' => 1,
            'sort_order' => $sort, 'created_at' => now(), 'updated_at' => now()]);
    }

    // ------------------------------------------------------------------ order

    /** Stages and classes in their id order; items in the order they were entered; roadmaps as in ROADMAPS. */
    private function order(): void
    {
        DB::table('career_roadmap_categories')->where('sort_order', 0)->update(['sort_order' => DB::raw('id * 10')]);
        DB::table('career_roadmap_stages')->where('sort_order', 0)->update(['sort_order' => DB::raw('id * 10')]);
        $rows = DB::table(self::T)->where('sort_order', 0)->whereNotNull('parent_id')->orderBy('id')->get(['id', 'parent_id']);
        $pos = [];
        foreach ($rows as $r) {
            $pos[$r->parent_id] = ($pos[$r->parent_id] ?? DB::table(self::T)->where('parent_id', $r->parent_id)->max('sort_order')) + 10;
            DB::table(self::T)->where('id', $r->id)->update(['sort_order' => $pos[$r->parent_id]]);
        }
    }

    // ------------------------------------------------------------------ roadmap and item types

    private function types(): void
    {
        foreach (self::ROADMAPS as $id => [$title, $type, $sort]) $this->set($id, $title, ['roadmap_type' => $type, 'sort_order' => $sort]);

        // items: from the roadmap's type and the item's place in it (an item that already has a type keeps it)
        $kids = DB::table(self::T)->whereNotNull('parent_id')->get(['id', 'parent_id', 'title', 'item_type'])->groupBy('parent_id');
        foreach (DB::table(self::T)->whereNull('parent_id')->whereNotNull('roadmap_type')->get(['id', 'roadmap_type', 'title']) as $road) {
            $walk = function ($parent, int $depth) use (&$walk, $kids, $road) {
                foreach ($kids[$parent] ?? [] as $k) {
                    $hasKids = isset($kids[$k->id]);
                    if (!$k->item_type) DB::table(self::T)->where('id', $k->id)->update(['item_type' => $this->itemType($road, $k->title, $depth, $hasKids)]);
                    $walk($k->id, $depth + 1);
                }
            };
            $walk($road->id, 1);
        }
    }

    private function itemType(object $road, string $title, int $depth, bool $hasKids): string
    {
        $olympiad = preg_match('/olympiad|\bNSO\b|\bIMO\b|\bIEO\b|IGKO|NSEJS|NSTSE|\bSTSE\b|junior science|talent search|silverzone|homi bhabha/i', $title);
        switch ($road->roadmap_type) {
            case 'stream': return $hasKids ? 'step' : ($depth === 1 ? 'subject' : (preg_match('/^(PCB|PCM|PCMB)$/i', trim($title)) ? 'info' : ($road->id == 378 ? 'career' : 'subject')));
            case 'career_path': return $hasKids || $depth === 1 ? 'step' : (preg_match('/^(army|navy|air ?force|cabin crew|pilot|merchant navy|nda|business analyst|investment banker)/i', $title) ? 'career' : 'course');
            case 'careers': return $depth === 1 ? 'step' : 'career';
            case 'govt_jobs': return $hasKids ? 'step' : 'career';
            case 'exams': case 'school_entrance': return $hasKids ? 'step' : 'exam';
            case 'scholarships': return $hasKids ? 'step' : ($olympiad ? 'exam' : 'scholarship');
            case 'study_abroad': return $hasKids ? 'step' : (preg_match('/ivy league/i', $title) ? 'info' : 'exam');
            case 'skills': return $hasKids ? 'step' : ($road->id == 527 ? 'course' : 'activity');
            case 'sports': return $hasKids ? 'step' : 'activity';
            case 'timeline': return $hasKids || $depth === 1 ? 'step' : ($olympiad || preg_match('/\b(JEE|NEET|IAT|NDA|AISSEE|RIMC)\b/', $title) ? 'exam' : 'activity');
            default: return $hasKids || $depth === 1 ? 'step' : 'info';
        }
    }

    // ------------------------------------------------------------------ fee and salary out of the custom fields

    /**
     * The old form kept a label in "Badge" and a value in "Salary" ("Fee" / "Zero"). A fee label moves the value to fee;
     * a pay-like value to salary; anything else stays a custom field.
     */
    private function facts(): void
    {
        foreach (DB::table(self::T)->whereNotNull('custom_fields')->get(['id', 'custom_fields', 'fee', 'salary']) as $r) {
            $cf = json_decode((string) $r->custom_fields, true);
            if (!is_array($cf)) continue;
            $label = trim((string) ($cf['Badge'] ?? $cf['badge'] ?? ''));
            $value = trim((string) ($cf['Salary'] ?? $cf['salary'] ?? ''));
            if ($value === '') continue;
            $data = [];
            if (preg_match('/fee|cost|registration|charge/i', $label) || ($label === '' && preg_match('/fee|free|zero|registration/i', $value))) {
                if (!$r->fee) $data['fee'] = Str::limit(preg_replace('/^(fee|fees)\s*[-:–]\s*/i', '', preg_replace('/\s+/', ' ', strip_tags($value))), 250, '…');
            } elseif (preg_match('/₹|lpa|salary|per month|\/month|lakh|stipend/i', $value)) {
                if (!$r->salary) $data['salary'] = Str::limit(preg_replace('/\s+/', ' ', strip_tags($value)), 250, '…');
            } else {
                continue;
            }
            unset($cf['Badge'], $cf['badge'], $cf['Salary'], $cf['salary']);
            $data['custom_fields'] = $cf ? json_encode($cf, JSON_UNESCAPED_UNICODE) : null;
            DB::table(self::T)->where('id', $r->id)->update($data);
        }
    }

    // ------------------------------------------------------------------ links to the Exam, Course and Scholarship modules

    private function links(): void
    {
        $norm = fn ($s) => Str::lower(trim(preg_replace('/\s+/', ' ', preg_replace('/\s*\(.*?\)\s*/', ' ', (string) $s))));
        $abbr = fn ($s) => preg_match('/\(([A-Z][A-Za-z\s\-]{1,15})\)/', (string) $s, $m) ? Str::lower(trim($m[1])) : null;

        $exams = DB::table('exm_exams')->whereNull('deleted_at')->where('is_archived', 0)->get(['id', 'name', 'short_name', 'aliases']);
        $examBy = [];
        foreach ($exams as $e) foreach (array_merge([$e->name, $e->short_name], (array) (json_decode((string) $e->aliases, true) ?: [])) as $n) if ($n) $examBy[$norm($n)] = $e->id;
        foreach (DB::table(self::T)->where('item_type', 'exam')->whereNull('exam_id')->get(['id', 'title']) as $r) {
            $id = $examBy[$norm($r->title)] ?? ($abbr($r->title) ? ($examBy[$abbr($r->title)] ?? null) : null);
            if ($id) DB::table(self::T)->where('id', $r->id)->update(['exam_id' => $id]);
        }

        $courseBy = DB::table('courses')->where('status', 1)->get(['id', 'name'])->mapWithKeys(fn ($c) => [$norm($c->name) => $c->id])->all();
        foreach (DB::table(self::T)->where('item_type', 'course')->whereNull('course_id')->get(['id', 'title']) as $r) {
            $id = $courseBy[$norm($r->title)] ?? null;
            if ($id) DB::table(self::T)->where('id', $r->id)->update(['course_id' => $id]);
        }

        $sch = DB::table('scholarships')->whereNull('deleted_at')->get(['id', 'title', 'short_name']);
        foreach (DB::table(self::T)->where('item_type', 'scholarship')->whereNull('scholarship_id')->get(['id', 'title']) as $r) {
            $t = $norm($r->title);
            $hit = $sch->first(fn ($s) => $norm($s->title) === $t || ($s->short_name && $norm($s->short_name) === $t)
                || (mb_strlen($t) > 12 && (str_contains($norm($s->title), $t) || str_contains($t, $norm(preg_replace('/\s+\d{4}.*$/', '', $s->title))))));
            if ($hit) DB::table(self::T)->where('id', $r->id)->update(['scholarship_id' => $hit->id]);
        }
    }

    // ------------------------------------------------------------------ the content errors from the audit

    private function fixes(): void
    {
        // titles: typos, all-caps, wrong item
        $titles = [
            19 => ['Management / Commrce', 'Management / Commerce'], 20 => ['SCIENCE', 'Science'], 371 => ['sports activity', 'Sports Activity'],
            497 => ['JEE Advance', 'JEE Advanced'], 508 => ['Mechanical Engineer', 'Biomedical Engineer'], 521 => ['School techer', 'School Teacher'],
            536 => ['Phtotography', 'Photography / Videography'], 691 => ['DiplomaAviation', 'Diploma in Aviation & Hospitality'], 695 => ['AirForce Office', 'Air Force Officer'],
            39 => ['NTSE / Olympiads', 'Olympiads'], 483 => ['MODE', 'Study Mode'], 459 => ['Mode', 'Study Mode'],
            411 => ['Science/ Engineering', 'Science / Engineering'], 418 => ['Commerce/ Management', 'Commerce / Management'],
            447 => ['Selection Test', 'School Entrance Tests (Class 6 admission)'],
        ];
        foreach ($titles as $id => [$was, $now]) $this->set($id, $was, ['title' => $now]);

        // subjects and steps written in capitals or as "class 11"
        foreach (DB::table(self::T)->whereIn('parent_id', [637, 638, 639, 640, 641, 642])->get(['id', 'title']) as $r) {
            if (preg_match('/^class\s*11$/i', trim($r->title))) DB::table(self::T)->where('id', $r->id)->update(['title' => 'About the stream and its subjects']);
            elseif ($r->title === Str::upper($r->title) && mb_strlen($r->title) > 2) DB::table(self::T)->where('id', $r->id)->update(['title' => $this->titleCase($r->title)]);
        }
        foreach (DB::table(self::T)->whereIn('parent_id', DB::table(self::T)->whereIn('parent_id', [637, 638, 639, 640])->pluck('id'))->get(['id', 'title']) as $r)
            if ($r->title === Str::upper($r->title) && mb_strlen($r->title) > 2) DB::table(self::T)->where('id', $r->id)->update(['title' => $this->titleCase($r->title)]);

        // duplicates and an item that is not an exam
        $this->set(684, 'M.Sc CS', ['status' => 0]);
        $this->set(769, 'BA Journalism', ['status' => 0]);
        $this->set(441, 'UPES', ['status' => 0]);

        // the graduation roadmaps' intro lines named other roadmaps
        foreach ([19 => 'Management / Commerce', 20 => 'Science', 22 => 'Arts & Humanities', 23 => 'Hospitality', 24 => 'Design & Creative'] as $id => $name) {
            $r = DB::table(self::T)->where('id', $id)->first();
            $cf = $r ? (json_decode((string) $r->custom_fields, true) ?: []) : [];
            if (!empty($cf['alert_message'])) {
                $cf['alert_message'] = preg_replace('/pursue a career in [^.]+\./', 'pursue a career in ' . $name . '.', $cf['alert_message']);
                DB::table(self::T)->where('id', $id)->update(['custom_fields' => json_encode($cf, JSON_UNESCAPED_UNICODE)]);
            }
        }

        // NTSE and KVPY were discontinued (2021 and 2022)
        foreach ([3, 39] as $id) {
            $r = DB::table(self::T)->where('id', $id)->first();
            if (!$r) continue;
            $d = preg_replace(['/\s*(NTSE|KVPY)\s*[•·,\/]?\s*/u', '/\n\s*\.\s*(NTSE|KVPY)\s*/u'], [' ', "\n"], (string) $r->description);
            DB::table(self::T)->where('id', $id)->update(['description' => trim(preg_replace('/^[\s•·]+|[\s•·]+$/u', '', $d))]);
        }
        $this->set(3, 'Olympiad / talent', ['description' => "NSO\n. IMO\n. IEO\n. IGKO"]);

        // JEE and NEET are taken after Class 12: in Class 10 they are the foundation to start
        $under = array_merge([328], DB::table(self::T)->where('parent_id', 328)->pluck('id')->all());   // the items sit in a "class 10" container
        $jee = DB::table(self::T)->whereIn('parent_id', $under)->where('title', 'JEE')->value('id');
        $neet = DB::table(self::T)->whereIn('parent_id', $under)->where('title', 'NEET')->value('id');
        if ($jee) DB::table(self::T)->where('id', $jee)->update(['title' => 'JEE (foundation)', 'summary' => 'Taken after Class 12. Start the foundation now: Class 9–10 maths and science, then PCM in Class 11–12.']);
        if ($neet) DB::table(self::T)->where('id', $neet)->update(['title' => 'NEET (foundation)', 'summary' => 'Taken after Class 12. Start the foundation now: Class 9–10 biology and chemistry, then PCB in Class 11–12.']);

        // the Navodaya and residential school tests for Class 6 admission are taken in Class 5
        if ($this->row(447, 'School Entrance Tests')) {
            $ids = [447]; $front = [447];
            while ($front) { $front = DB::table(self::T)->whereIn('parent_id', $front)->pluck('id')->all(); $ids = array_merge($ids, $front); }
            DB::table(self::T)->whereIn('id', $ids)->update(['stage_id' => 1]);
            DB::table(self::T)->where('id', 447)->update(['description' => "JNVST\n. STATE RESIDENTIAL\n. EXCELLENCE SCHOOLS"]);
        }
    }

    private function titleCase(string $t): string
    {
        $keep = ['IT', 'PCB', 'PCM', 'PCMB', 'NSS', 'NCC'];
        return implode(' ', array_map(fn ($w) => in_array($w, $keep, true) ? $w : Str::ucfirst(Str::lower($w)), explode(' ', $t)));
    }

    // ------------------------------------------------------------------ Class 5: the three roadmaps that only had the template

    private function classFive(): void
    {
        $plans = [
            2 => ['Science & technology', "OLYMPIADS\n. CODING\n. JEE / NEET LATER", [
                ['Now — Class 5 to 6', 'Build curiosity: experiments, puzzles and the first olympiads.', [
                    ['Science and maths olympiads', 'exam', 'NSO (science) and IMO (maths) by the Science Olympiad Foundation, through your school.', 'Classes 1–12', 'Science Olympiad Foundation', 'Registration October–November'],
                    ['Start coding with Scratch', 'activity', 'Free on scratch.mit.edu: make small games and stories; it builds logic for later.', 'Any age', null, null],
                    ['Science kits and clubs', 'activity', 'A school science club, kits at home, or the Vigyan Prasar activities.', 'Any age', null, null],
                ]],
                ['Class 7 to 8', 'Turn interest into projects and the first scholarships.', [
                    ['INSPIRE-MANAK award', 'scholarship', 'Department of Science & Technology award for an original idea or model; selected ideas get ₹10,000.', 'Classes 6–10', 'DST and NIF', 'Nominations through the school, July–September'],
                    ['Robotics and STEM workshops', 'activity', 'Atal Tinkering Labs in many schools, or short courses.', 'Classes 6+', null, null],
                    ['NMMS scholarship (Class 8)', 'scholarship', 'National Means-cum-Merit Scholarship: ₹12,000 a year from Class 9 to 12 for students of government schools.', 'Class 8, family income under ₹3.5 lakh', 'State education departments', 'Exam in November–December'],
                ]],
                ['Class 9 to 10', 'Test your science seriously and choose your stream.', [
                    ['NSEJS (Junior Science Olympiad)', 'exam', 'The first stage of the International Junior Science Olympiad.', 'Classes 8–10', 'IAPT and HBCSE', 'November'],
                    ['Foundation for JEE / NEET', 'activity', 'Strong Class 9–10 maths and science is the real foundation; coaching is optional.', 'Classes 9–10', null, null],
                    ['Choose PCM, PCB or PCMB', 'info', 'PCM opens engineering and technology; PCB opens medicine and life sciences; PCMB keeps both.', 'After Class 10', null, null],
                ]],
                ['Class 11 to 12', 'The entrance exams to science and technology colleges.', [
                    ['JEE Main and JEE Advanced', 'exam', 'For B.Tech at NITs, IIITs and IITs.', 'PCM in Class 12', 'NTA and IITs', 'January–June'],
                    ['NEET UG', 'exam', 'For MBBS, BDS and other medical courses.', 'PCB in Class 12', 'NTA', 'May'],
                    ['IISER Aptitude Test (IAT)', 'exam', 'For BS-MS research degrees at the IISERs.', 'Science in Class 12', 'IISERs', 'June'],
                ]],
            ]],
            3 => ['Olympiad / talent', "NSO\n. IMO\n. IEO\n. IGKO", [
                ['Now — Class 5 to 6', 'School-level olympiads: practice for exams and a way to find what you are good at.', [
                    ['NSO, IMO, IEO and IGKO', 'exam', 'Science, maths, English and general knowledge olympiads of the Science Olympiad Foundation.', 'Classes 1–12', 'Science Olympiad Foundation', 'Registration through school, October–November'],
                    ['Silverzone olympiads', 'exam', 'Olympiads in maths, science, English, computers and more.', 'Classes 1–12', 'Silverzone Foundation', 'Through school'],
                ]],
                ['Class 7 to 10', 'National talent tests and the first scholarships.', [
                    ['NSTSE', 'exam', 'National Level Science Talent Search Exam.', 'Classes 2–12', 'Unified Council', 'December–January'],
                    ['NMMS (Class 8)', 'scholarship', '₹12,000 a year from Class 9 to 12 for students of government schools.', 'Class 8', 'State education departments', 'November–December'],
                    ['NSEJS', 'exam', 'National Standard Examination in Junior Science, the first stage of the Junior Science Olympiad.', 'Classes 8–10', 'IAPT and HBCSE', 'November'],
                ]],
                ['Class 11 to 12', 'The national olympiads and science scholarships.', [
                    ['IOQM and the National Standard Examinations', 'exam', 'Maths (IOQM) and physics, chemistry, biology and astronomy (NSEP, NSEC, NSEB, NSEA): the routes to the international olympiads.', 'Classes 11–12', 'MTA, IAPT and HBCSE', 'September–November'],
                    ['INSPIRE-SHE scholarship', 'scholarship', '₹80,000 a year for students of natural and basic sciences, for top Class 12 scorers.', 'Top 1% in Class 12, B.Sc / BS-MS', 'Department of Science & Technology', 'After Class 12 results'],
                ]],
            ]],
            4 => ['Still exploring', "TRY THINGS\n. FIND INTERESTS\n. DECIDE IN CLASS 10", [
                ['Explore now', 'Classes 5–8 are for trying many things; nothing has to be decided yet.', [
                    ['Try different activities', 'activity', 'Sports, music, art, coding, reading, debating: try one new thing each term.', 'Any age', null, null],
                    ['Notice what you enjoy', 'activity', 'Keep a short list of subjects and activities you like and are good at; it helps later.', 'Any age', null, null],
                ]],
                ['Build strong basics', 'Whatever you choose later, these help every stream.', [
                    ['Reading and maths habits', 'activity', 'Read every day and keep maths practice regular.', 'Any age', null, null],
                    ['Olympiads to test your interests', 'exam', 'An olympiad in science, maths or English shows what you enjoy under exam conditions.', 'Classes 1–12', 'Science Olympiad Foundation and others', 'October–November'],
                ]],
                ['Decide later, with data', 'The real decision is the stream after Class 10.', [
                    ['Aptitude and interest test (Class 9)', 'activity', 'A counsellor-led test of your interests and strengths.', 'Classes 9–10', null, null],
                    ['Choose a stream after Class 10', 'info', 'Science (PCM / PCB), Commerce, Arts or Vocational: see the Class 10 Stream Selection roadmap.', 'After Class 10', null, null],
                ]],
            ]],
        ];
        foreach ($plans as $id => [$title, $hint, $steps]) {
            if (!$this->row($id, $title)) continue;
            $placeholder = DB::table(self::T)->where('parent_id', $id)->whereIn('title', ['Immediate Action Plan', 'Long-term Goals'])->pluck('id')->all();
            if ($placeholder) {
                $all = $placeholder; $front = $placeholder;
                while ($front) { $front = DB::table(self::T)->whereIn('parent_id', $front)->pluck('id')->all(); $all = array_merge($all, $front); }
                DB::table(self::T)->whereIn('id', $all)->update(['status' => 0]);
            }
            DB::table(self::T)->where('id', $id)->update(['description' => $hint, 'custom_fields' => null]);
            foreach ($steps as $i => [$st, $summary, $items]) {
                $sid = $this->add($id, $st, ['item_type' => 'step', 'summary' => $summary], ($i + 1) * 10);
                foreach ($items as $j => [$t, $type, $sum, $elig, $by, $when]) $this->add($sid, $t, ['item_type' => $type, 'summary' => $sum, 'eligibility' => $elig, 'conducted_by' => $by, 'when_text' => $when], ($j + 1) * 10);
            }
        }
    }

    // ------------------------------------------------------------------ careers

    /** Class (career_roadmap_stages) ids by name: Class 5 … Post graduation. */
    private function classIds(array $names): array
    {
        $all = DB::table('career_roadmap_stages')->pluck('id', 'title')->mapWithKeys(fn ($id, $t) => [Str::lower(trim($t)) => (int) $id]);
        return array_values(array_filter(array_map(fn ($n) => $all[Str::lower($n)] ?? null, $names)));
    }

    private function careers(): void
    {
        $school = ['Class 5', 'Class 6', 'Class 7', 'Class 8'];
        $c910 = ['Class 9', 'Class 10'];
        $c1112 = ['Class 11', 'Class 12'];
        $list = [
            ['doctor', 'Doctor', 'med', 'Diagnoses and treats patients, from family doctor to surgeon.', 'PCB (Physics, Chemistry, Biology)', ['PCB in Class 11–12', 'NEET UG after Class 12', 'MBBS: 5.5 years with a one-year internship', 'NEET PG for MD / MS to specialise'], ['NEET UG', 'NEET PG'], 'About ₹8–15 LPA after MBBS; more after MD / MS', 'Government and private hospitals, AIIMS, Armed Forces Medical Services, own practice', [...$school, ...$c910, ...$c1112]],
            ['engineer', 'Engineer', 'eng', 'Designs and builds machines, software, roads, power systems and more.', 'PCM (Physics, Chemistry, Maths)', ['PCM in Class 11–12', 'JEE Main / JEE Advanced or a state CET', 'B.Tech / B.E.: 4 years', 'A job, or GATE for M.Tech and PSU jobs'], ['JEE Main', 'JEE Advanced', 'BITSAT', 'State CETs'], 'About ₹4–12 LPA starting; higher from IITs and NITs', 'IT companies, core engineering firms, PSUs, startups', [...$school, ...$c910, ...$c1112]],
            ['software-developer', 'Software developer', 'data', 'Builds apps, websites and software products.', 'PCM, or any stream with Maths', ['Maths in Class 11–12', 'JEE or CUET', 'B.Tech CSE, BCA or B.Sc Computer Science', 'Projects and internships, then a developer job'], ['JEE Main', 'CUET UG', 'NIMCET'], 'About ₹4–15 LPA starting', 'IT services, product companies, startups', ['Class 7', 'Class 8', ...$c910, ...$c1112, 'Graduation']],
            ['data-scientist', 'Data scientist / AI engineer', 'data', 'Finds patterns in data and builds machine-learning and AI products.', 'PCM, or any stream with Maths', ['Maths in Class 11–12', 'B.Tech, B.Sc or BCA', 'M.Tech, MCA, M.Sc or strong projects', 'Data analyst, then data scientist or ML engineer'], ['JEE Main', 'CUET UG', 'GATE'], 'About ₹6–20 LPA', 'Tech companies, banks, consulting, research labs', ['Class 11', 'Class 12', 'Graduation', 'Post graduation']],
            ['chartered-accountant', 'Chartered Accountant (CA)', 'ca', 'Audits accounts, handles tax and advises companies on money.', 'Commerce (any stream can apply)', ['Class 12 (Commerce helps)', 'CA Foundation', 'CA Intermediate', 'Three years of articleship', 'CA Final'], ['CA Foundation', 'CA Intermediate', 'CA Final'], 'About ₹7–12 LPA for freshers', 'Big Four firms, banks, companies, own practice', [...$c910, ...$c1112, 'Graduation']],
            ['company-secretary', 'Company Secretary (CS)', 'ca', 'Makes sure a company follows company law and runs its board matters.', 'Commerce (any stream can apply)', ['Class 12', 'CSEET', 'CS Executive', 'CS Professional with practical training'], ['CSEET'], 'About ₹4–8 LPA starting', 'Listed companies, law firms, own practice', ['Class 10', ...$c1112]],
            ['lawyer', 'Lawyer', 'law', 'Argues cases in court or advises companies and people on the law.', 'Any stream', ['Any stream in Class 11–12', 'CLAT, AILET or LSAT India', 'Integrated BA / BBA LLB: 5 years (or LLB after graduation)', 'AIBE to practise'], ['CLAT', 'AILET', 'LSAT India', 'AIBE'], 'About ₹3–10 LPA starting; top law firms higher', 'Law firms, companies, courts, government', [...$c910, ...$c1112, 'Graduation']],
            ['judge', 'Judge / legal counsel', 'law', 'Decides cases as a judge, or heads legal work at a company.', 'Any stream', ['LLB', 'LLM (optional)', 'Judicial services exam or law-firm experience', 'Civil judge or senior counsel'], ['Judicial Services Exam', 'CLAT PG'], 'Civil judge: about ₹77,840/month basic', 'Courts, companies, law firms', ['Post graduation']],
            ['civil-servant', 'Civil servant (IAS / IPS / IFS)', 'gov', 'Runs districts, police and departments, or represents India abroad.', 'Any stream', ['Any stream', 'Any graduation (age 21+)', 'UPSC Civil Services: prelims, mains, interview', 'Training at LBSNAA and the service academies'], ['UPSC CSE', 'State PSC'], '₹56,100/month basic plus allowances', 'Government of India and the states', [...$school, ...$c910, ...$c1112, 'Graduation', 'Post graduation']],
            ['defence-officer', 'Defence officer', 'def', 'Leads soldiers, sailors or airmen as an officer of the armed forces.', 'Any stream (PCM for the Navy and Air Force)', ['Class 12 (PCM for the Navy and Air Force)', 'NDA exam and SSB interview', 'Three years at the National Defence Academy', 'Academy training, then commission'], ['NDA', 'CDS', 'AFCAT', 'SSB'], '₹56,100/month basic as Lieutenant, plus allowances', 'Indian Army, Navy, Air Force', [...$school, ...$c910, ...$c1112, 'Graduation']],
            ['merchant-navy-officer', 'Merchant Navy officer', 'def', 'Navigates or runs the engines of cargo and passenger ships.', 'PCM', ['PCM in Class 11–12 (60%+)', 'IMU CET', 'B.Sc Nautical Science or B.Tech Marine Engineering', 'Sea time, then officer certificates'], ['IMU CET'], 'About ₹50,000–1.5 lakh/month at sea', 'Shipping companies', $c1112],
            ['pilot', 'Commercial pilot', 'def', 'Flies passenger and cargo aircraft.', 'Physics and Maths in Class 12', ['Physics and Maths in Class 12', 'DGCA Class 2, then Class 1 medical', 'Flying school for the CPL: about 1.5–2 years', 'Type rating and an airline job'], ['DGCA CPL exams', 'IGRUA entrance'], 'About ₹1–3 lakh/month as first officer', 'Airlines, charter and cargo operators', ['Class 10', ...$c1112]],
            ['architect', 'Architect', 'arch', 'Designs buildings, campuses and cities.', 'PCM', ['PCM in Class 11–12', 'NATA or JEE Main Paper 2', 'B.Arch: 5 years', 'Register with the Council of Architecture'], ['NATA', 'JEE Main Paper 2'], 'About ₹3–8 LPA starting', 'Architecture firms, builders, government, own practice', ['Class 10', ...$c1112]],
            ['designer', 'Designer', 'des', 'Designs clothes, products, apps, brands and games.', 'Any stream', ['Any stream', 'NID DAT, NIFT or UCEED', 'B.Des: 4 years', 'Portfolio, then studio or product jobs'], ['NID DAT', 'NIFT', 'UCEED', 'CEED'], 'About ₹3–8 LPA starting', 'Design studios, fashion brands, tech companies', [...$school, ...$c910, ...$c1112, 'Graduation']],
            ['manager', 'Manager (MBA)', 'biz', 'Runs marketing, finance, HR or operations, or a business of your own.', 'Any stream (Commerce is common)', ['Any stream', 'BBA / IPM (IPMAT, CUET) or any degree', 'CAT, XAT or GMAT', 'MBA / PGDM: 2 years'], ['IPMAT', 'CUET UG', 'CAT', 'XAT'], 'About ₹6–25 LPA after an MBA, by college', 'Companies in every sector, consulting, startups', [...$c1112, 'Graduation', 'Post graduation']],
            ['bank-officer', 'Bank officer', 'ca', 'Probationary officer in a bank, or an officer at RBI or NABARD.', 'Any stream', ['Any stream', 'Any graduation', 'IBPS PO, SBI PO or RBI Grade B', 'Interview, then officer training'], ['IBPS PO', 'SBI PO', 'RBI Grade B'], 'About ₹6–8 LPA as PO', 'Public sector banks, RBI, NABARD', [...$c1112, 'Graduation']],
            ['ssc-officer', 'Government officer (SSC)', 'gov', 'Inspector, auditor or assistant in central ministries.', 'Any stream', ['Any stream', 'Any graduation', 'SSC CGL tiers', 'Document verification and posting'], ['SSC CGL', 'RRB NTPC'], 'About ₹4–8 LPA', 'Central government departments', ['Class 12', 'Graduation']],
            ['economist', 'Economist', 'biz', 'Studies how money, markets and policy work, in government, banks and research.', 'Commerce or Arts, with Maths', ['Maths in Class 11–12 helps', 'BA / B.Sc Economics (CUET)', 'MA Economics', 'UPSC IES, RBI, research or banks'], ['CUET UG', 'CUET PG', 'UPSC IES'], 'About ₹5–12 LPA', 'RBI, government, banks, think tanks', $c1112],
            ['finance-analyst', 'Investment / finance analyst', 'ca', 'Researches companies and markets and manages investments.', 'Commerce or any stream with Maths', ['B.Com, BBA or any degree', 'MBA Finance, M.Com or CA', 'CFA Level I–III', 'Analyst, then fund or investment manager'], ['CFA', 'CAT'], 'About ₹6–15 LPA', 'Banks, funds, broking and research firms', ['Graduation', 'Post graduation']],
            ['teacher', 'Teacher', 'arts', 'Teaches at a school, from primary to senior secondary.', 'Any stream', ['Any stream', '4-year ITEP after Class 12, or a degree then B.Ed', 'CTET or a state TET', 'Teacher recruitment exam or school interview'], ['NCET (ITEP)', 'CTET', 'State TET'], 'About ₹3–7 LPA; government posts higher', 'Government and private schools', ['Class 10', ...$c1112, 'Graduation']],
            ['professor', 'Professor / researcher', 'arts', 'Teaches at a college and does research.', 'Any stream', ['Post graduation with 55%+', 'UGC NET or CSIR NET (JRF for a fellowship)', 'PhD', 'Assistant professor'], ['UGC NET', 'CSIR NET'], 'Assistant professor: about ₹57,700/month basic', 'Universities, colleges, research institutes', ['Post graduation']],
            ['scientist', 'Scientist (ISRO, DRDO, labs)', 'eng', 'Does research on space, defence, energy and new materials.', 'PCM or PCB', ['Science in Class 11–12', 'IISER / IISc / IIST through IAT or JEE', 'M.Sc or M.Tech', 'GATE, the lab recruitment exam, or a PhD'], ['IAT', 'JEE Advanced', 'GATE', 'ISRO ICRB'], 'About ₹56,100/month basic as Scientist C', 'ISRO, DRDO, BARC, CSIR labs', [...$school, ...$c1112, 'Post graduation']],
            ['pharmacist', 'Pharmacist', 'med', 'Makes, checks and dispenses medicines.', 'PCB or PCM', ['PCB or PCM in Class 11–12', 'B.Pharm: 4 years (state CET or CUET)', 'Register with the Pharmacy Council', 'Hospital, pharma company or own pharmacy'], ['CUET UG', 'State CETs', 'GPAT'], 'About ₹2.5–5 LPA starting', 'Pharma companies, hospitals, pharmacies', $c1112],
            ['nurse', 'Nurse', 'med', 'Cares for patients in hospitals and clinics.', 'PCB', ['PCB in Class 11–12', 'NEET UG or the nursing entrance of the college', 'B.Sc Nursing: 4 years', 'Register with the nursing council'], ['NEET UG', 'AIIMS B.Sc Nursing'], 'About ₹3–6 LPA', 'Hospitals, the armed forces, abroad', $c1112],
            ['physiotherapist', 'Physiotherapist', 'med', 'Helps people recover movement after injury or illness.', 'PCB', ['PCB in Class 11–12', 'NEET UG, a state CET or the university test', 'BPT: 4.5 years with internship', 'Hospital, sports team or own clinic'], ['NEET UG', 'State CETs'], 'About ₹3–6 LPA', 'Hospitals, sports teams, clinics', $c1112],
            ['veterinarian', 'Veterinary doctor', 'med', 'Treats animals, pets to farm animals.', 'PCB', ['PCB in Class 11–12', 'NEET UG', 'BVSc & AH: 5.5 years', 'Government or private practice'], ['NEET UG'], 'About ₹4–8 LPA', 'State animal husbandry, dairy, pet clinics', $c1112],
            ['biotechnologist', 'Biotechnologist', 'eng', 'Uses living cells to make medicines, vaccines and better crops.', 'PCB or PCM', ['PCB or PCM in Class 11–12', 'B.Sc or B.Tech Biotechnology (CUET, JEE or state CET)', 'M.Sc / M.Tech', 'Research lab or biotech company'], ['CUET UG', 'JEE Main', 'GAT-B'], 'About ₹3–6 LPA starting', 'Biotech and pharma companies, research labs', $c1112],
            ['agricultural-scientist', 'Agricultural scientist', 'eng', 'Improves crops, soil and farming.', 'PCB, PCM or Agriculture', ['Science in Class 11–12', 'B.Sc Agriculture (ICAR seats through CUET)', 'M.Sc', 'ICAR ASRB, banks or agri companies'], ['CUET UG (ICAR)', 'ICAR AIEEA PG'], 'About ₹4–8 LPA', 'ICAR, state agriculture departments, agri companies', $c1112],
            ['psychologist', 'Psychologist', 'arts', 'Helps people with their thoughts, feelings and behaviour.', 'Any stream (Psychology helps)', ['Any stream', 'BA / B.Sc Psychology (CUET)', 'MA / M.Sc Psychology', 'M.Phil Clinical Psychology for RCI licence'], ['CUET UG', 'CUET PG'], 'About ₹3–7 LPA', 'Hospitals, schools, companies, own practice', $c1112],
            ['journalist', 'Journalist', 'des', 'Finds and tells news and stories on TV, print and online.', 'Any stream', ['Any stream', 'BJMC or BA Journalism (CUET or college test)', 'Internships and a portfolio', 'Reporter, editor or producer'], ['CUET UG', 'IIMC entrance'], 'About ₹3–6 LPA starting', 'News channels, newspapers, digital media', $c1112],
            ['hotel-manager', 'Hotel / hospitality manager', 'des', 'Runs hotels, airlines’ guest services, cruises and events.', 'Any stream', ['Any stream', 'NCHM JEE', 'B.Sc Hospitality & Hotel Administration: 3 years', 'Hotel, airline or cruise jobs'], ['NCHM JEE'], 'About ₹2.5–5 LPA starting', 'Hotel chains, airlines, cruise lines', [...$c1112, 'Graduation']],
            ['sportsperson', 'Sportsperson', 'arts', 'Plays a sport at state, national and world level.', 'Any stream', ['School and district games', 'SAI and Khelo India trials', 'Academy training', 'National camps, sports quota jobs and scholarships'], ['Khelo India trials', 'SAI trials'], 'Varies widely: prize money, sponsorship, sports-quota jobs', 'Leagues, clubs, sports-quota posts in government and PSUs', [...$school, 'Class 9', 'Class 10']],
            ['specialist-doctor', 'Specialist doctor (MD / MS)', 'med', 'Specialises after MBBS: medicine, surgery, paediatrics and more.', 'PCB', ['MBBS with internship', 'NEET PG', 'MD / MS: 3 years', 'NEET SS for DM / MCh (super-specialist)'], ['NEET PG', 'NEET SS'], 'About ₹15–30 LPA and more', 'Hospitals, medical colleges, own practice', ['Graduation', 'Post graduation']],
            ['psu-engineer', 'Engineer in a PSU', 'eng', 'Engineer at ONGC, NTPC, BHEL, IOCL and other public sector companies.', 'PCM', ['B.Tech / B.E.', 'GATE', 'PSU interview', 'Engineer trainee, then engineer'], ['GATE'], 'About ₹10–15 LPA', 'ONGC, NTPC, BHEL, IOCL, GAIL', ['Graduation']],
        ];
        foreach ($list as $i => [$slug, $name, $icon, $summary, $stream, $route, $exams, $salary, $recruiters, $for]) {
            $data = ['name' => $name, 'icon' => $icon, 'summary' => $summary, 'stream' => $stream, 'route' => json_encode($route, JSON_UNESCAPED_UNICODE),
                'exams' => json_encode($exams, JSON_UNESCAPED_UNICODE), 'salary' => $salary, 'recruiters' => $recruiters,
                'show_for' => json_encode($this->classIds($for)), 'sort_order' => ($i + 1) * 10, 'status' => 1, 'updated_at' => now()];
            if (DB::table('careers')->where('slug', $slug)->exists()) continue;   // edited in the CRM since: keep it
            DB::table('careers')->insert($data + ['slug' => $slug, 'created_at' => now()]);
        }
        // the careers' courses, from the master courses
        $courseBy = DB::table('courses')->where('status', 1)->pluck('id', 'name')->mapWithKeys(fn ($id, $n) => [Str::lower(trim($n)) => $id]);
        $courses = ['doctor' => ['MBBS'], 'engineer' => ['B.Tech', 'B.E.'], 'software-developer' => ['B.Tech', 'BCA'], 'architect' => ['B.Arch'], 'designer' => ['B.Des'],
            'manager' => ['BBA', 'MBA'], 'lawyer' => ['BA LLB', 'BBA LLB', 'LLB'], 'pharmacist' => ['B.Pharm'], 'nurse' => ['B.Sc Nursing'], 'physiotherapist' => ['BPT'],
            'hotel-manager' => ['BHM'], 'chartered-accountant' => ['B.Com'], 'journalist' => ['BJMC'], 'teacher' => ['B.Ed'], 'veterinarian' => ['BVSc']];
        foreach ($courses as $slug => $names) {
            $ids = array_values(array_filter(array_map(fn ($n) => $courseBy[Str::lower($n)] ?? null, $names)));
            if ($ids) DB::table('careers')->where('slug', $slug)->whereNull('course_ids')->update(['course_ids' => json_encode($ids)]);
        }
    }

    // ------------------------------------------------------------------ Class 11 streams: who it suits, what it opens

    private function streams(): void
    {
        $careers = DB::table('careers')->pluck('id', 'slug');
        $ids = fn (array $s) => json_encode(array_values(array_filter(array_map(fn ($x) => $careers[$x] ?? null, $s))));
        $streams = [
            638 => ['PCM', 'Physics, Chemistry and Maths: the stream for engineering, technology, architecture, defence and aviation.',
                ['Physics', 'Chemistry', 'Mathematics', 'English', 'One optional: Computer Science, Physical Education, Economics or a language'],
                'You enjoy maths and problem solving, and want engineering, computers, architecture, the Navy or Air Force, flying or research.',
                ['MBBS, BDS and other NEET courses (they need Biology)'],
                ['engineer', 'software-developer', 'data-scientist', 'architect', 'defence-officer', 'pilot', 'merchant-navy-officer', 'scientist']],
            637 => ['PCB', 'Physics, Chemistry and Biology: the stream for medicine, healthcare and life sciences.',
                ['Physics', 'Chemistry', 'Biology', 'English', 'One optional: Maths, Psychology, Physical Education or a language'],
                'You enjoy biology and how the body works, and want medicine, nursing, pharmacy, physiotherapy, veterinary science or biotechnology.',
                ['B.Tech at IITs and NITs through JEE (it needs Maths); take Maths as the optional to keep it open'],
                ['doctor', 'pharmacist', 'nurse', 'physiotherapist', 'veterinarian', 'biotechnologist', 'agricultural-scientist', 'psychologist']],
            639 => ['PCMB', 'Physics, Chemistry, Maths and Biology: keeps both engineering and medicine open.',
                ['Physics', 'Chemistry', 'Mathematics', 'Biology', 'English'],
                'You are strong in both maths and biology and are not yet sure between engineering and medicine, or want biotech and research.',
                ['Nothing is closed, but it is the heaviest load: plan for one main exam (JEE or NEET) by Class 12'],
                ['doctor', 'engineer', 'biotechnologist', 'scientist', 'pharmacist', 'agricultural-scientist']],
            640 => ['Commerce', 'Accountancy, Business Studies and Economics: the stream for finance, business and management.',
                ['Accountancy', 'Business Studies', 'Economics', 'English', 'Maths or Applied Maths (optional, but keep it for CA, economics and analytics)'],
                'You like numbers, money and how businesses work, and want CA, CS, banking, management, economics or your own business.',
                ['Engineering (JEE) and medicine (NEET)'],
                ['chartered-accountant', 'company-secretary', 'manager', 'bank-officer', 'economist', 'finance-analyst', 'lawyer']],
            641 => ['Arts', 'History, Political Science, Geography, Psychology and more: the stream for law, civil services, media and the social sciences.',
                ['History', 'Political Science', 'Geography', 'Economics', 'Psychology', 'Sociology', 'English'],
                'You enjoy reading, writing, people and society, and want law, civil services, journalism, psychology, teaching or design.',
                ['Engineering (JEE) and medicine (NEET)'],
                ['civil-servant', 'lawyer', 'journalist', 'psychologist', 'teacher', 'designer', 'economist']],
            642 => ['Vocational', 'Job-ready skills (IT, healthcare, retail, tourism, agriculture) alongside the core subjects.',
                ['A vocational subject: IT, Health Care, Retail, Tourism or Agriculture', 'English', 'Two or three core subjects of your choice'],
                'You learn best by doing and want skills for an early job, or a diploma, B.Voc or hospitality route.',
                ['The direct JEE or NEET route, unless your core subjects are PCM or PCB'],
                ['hotel-manager', 'software-developer', 'designer', 'agricultural-scientist']],
        ];
        foreach ($streams as $id => [$title, $summary, $subjects, $who, $closes, $opens]) {
            $r = $this->row($id, $title);
            if (!$r) continue;
            $data = [];
            if (!$r->summary) $data['summary'] = $summary;
            if (!$r->subjects) $data['subjects'] = $this->lines($subjects);
            if (!$r->who_should) $data['who_should'] = $who;
            if (!$r->closes) $data['closes'] = $this->lines($closes);
            if (!$r->career_ids) $data['career_ids'] = $ids($opens);
            if ($data) DB::table(self::T)->where('id', $id)->update($data);
        }
    }

    // ------------------------------------------------------------------ addresses

    /** Each roadmap's address in its class, from its title (kept once set: the CRM can change it). */
    private function slugs(): void
    {
        $taken = [];
        foreach (DB::table(self::T)->whereNull('parent_id')->orderBy('sort_order')->orderBy('id')->get(['id', 'stage_id', 'title', 'url_slug']) as $r) {
            if ($r->url_slug) { $taken[$r->stage_id][$r->url_slug] = true; continue; }
        }
        foreach (DB::table(self::T)->whereNull('parent_id')->whereNull('url_slug')->orderBy('id')->get(['id', 'stage_id', 'title']) as $r) {
            $s = Str::slug($r->title) ?: 'roadmap';
            if (isset($taken[$r->stage_id][$s])) $s .= '-' . $r->id;
            $taken[$r->stage_id][$s] = true;
            DB::table(self::T)->where('id', $r->id)->update(['url_slug' => $s]);
        }
    }

    // ------------------------------------------------------------------ the page's texts

    /** The career roadmap page's texts, as the website has them today, so the CRM opens on them (only when none are saved). */
    private function pageContent(): void
    {
        $id = DB::table('settings')->value('id');
        if (!$id || DB::table('settings')->where('id', $id)->value('career_page_content')) return;
        $json = @file_get_contents(database_path('seeders/data/career_page_content.json'));
        if ($json) DB::table('settings')->where('id', $id)->update(['career_page_content' => $json]);
    }

    // ------------------------------------------------------------------ master courses: the wrong entrance exams

    /** Law courses do not take CAT; management courses do not take GATE, JEE Main or CLAT. */
    private function masterCourses(): void
    {
        $ex = DB::table('exm_exams')->whereNull('deleted_at')->pluck('id', 'short_name');
        $cat = $ex['CAT'] ?? null; $gate = $ex['GATE'] ?? null; $clat = $ex['CLAT'] ?? null; $jee = $ex['JEE Main'] ?? null;
        foreach (DB::table('courses')->whereNotNull('common_entrance_exams')->get(['id', 'name', 'common_entrance_exams']) as $c) {
            $ids = array_map('intval', json_decode($c->common_entrance_exams, true) ?: []);
            $new = $ids;
            $law = preg_match('/\bLL\.?B|LLM|LL\.M/i', $c->name);
            $mgmt = preg_match('/MBA|PGDM|BBA|Management|Post Graduate Program|^Finance$|^Marketing$|MHA/i', $c->name) && !$law;
            if ($law && $cat) { $new = array_values(array_diff($new, [$cat])); if ($clat && !in_array($clat, $new, true)) $new[] = $clat; }
            if ($mgmt) {
                $new = array_values(array_diff($new, array_filter([$gate, $clat, $jee])));
                if ($cat && preg_match('/MBA|PGDM|Management|Post Graduate Program|^Finance$|^Marketing$|MHA/i', $c->name) && !preg_match('/BBA|B\.Com|UG\+PG/i', $c->name) && !in_array($cat, $new, true)) $new[] = $cat;
            }
            if (preg_match('/^MCA/i', $c->name) && $clat) $new = array_values(array_diff($new, [$clat]));
            if ($new !== $ids) DB::table('courses')->where('id', $c->id)->update(['common_entrance_exams' => json_encode($new), 'updated_at' => now()]);
        }
    }
}

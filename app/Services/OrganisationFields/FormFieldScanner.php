<?php

namespace App\Services\OrganisationFields;

use App\Http\Controllers\Admin\CampusController;
use App\Http\Controllers\Admin\DepartmentController;
use App\Http\Controllers\Admin\OrganisationController;
use App\Http\Controllers\Admin\OrganisationCourseController;
use App\Models\Admin;
use App\Models\Campus;
use App\Models\Department;
use App\Models\Organisation;
use App\Models\OrganisationCourse;
use App\Models\OrganisationType;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

/**
 * Finds out which fields staff can ACTUALLY see and edit, by rendering the real edit form of each level for a sample record of each organisation
 * type and reading the form controls. The result is cached and rebuilt automatically whenever one of the edit templates changes.
 *
 * Why render instead of reading the templates: the forms show different sections for different organisation types, and only the rendered page
 * tells the truth. Where no sample record of a type exists, the nearest type's form is used (College -> University; the non-academic bodies -> University).
 */
class FormFieldScanner
{
    public const FILE = 'organisation-form-fields.json';
    public const LEVELS = ['organisation', 'campus', 'department', 'course'];
    private const FALLBACK = [2 => 1, 5 => 1, 6 => 1, 7 => 1, 8 => 1];

    private ?array $cache = null;

    /** Edit templates whose change means the cached result is stale. */
    private function templateFiles(): array
    {
        $base = resource_path('views/admin/');
        $files = [];
        foreach (['organisations', 'organisations/campuses', 'departments', 'organisation-courses'] as $dir) {
            foreach (array_merge(glob($base . $dir . '/edit.blade.php') ?: [], glob($base . $dir . '/partials/*.blade.php') ?: []) as $f) $files[] = $f;
        }
        sort($files);
        return $files;
    }

    public function sourceHash(): string
    {
        return md5(implode('|', array_map(fn ($f) => $f . ':' . filemtime($f) . ':' . filesize($f), $this->templateFiles())));
    }

    /** The cached result, rebuilt when missing or stale (or when $rescan). */
    public function manifest(bool $rescan = false): array
    {
        if (!$rescan && $this->cache !== null) return $this->cache;
        $disk = Storage::disk('local');
        $m = null;
        if (!$rescan && $disk->exists(self::FILE)) {
            $m = json_decode($disk->get(self::FILE), true);
            if (!is_array($m) || ($m['hash'] ?? null) !== $this->sourceHash()) $m = null;
        }
        if ($m === null) {
            $m = $this->scan();
            $disk->put(self::FILE, json_encode($m, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));
        }
        return $this->cache = $m;
    }

    /**
     * Field names on the edit form of $level for organisation type $typeId, or null when no form could be read at all.
     *
     * @return array{fields:string[], source:string}|null
     */
    public function formFor(string $level, int $typeId): ?array
    {
        $types = $this->manifest()['types'] ?? [];
        foreach ([$typeId, self::FALLBACK[$typeId] ?? null, 1] as $t) {   // own form, then the nearest type's, then the university form (the only variant of the sub-level forms)
            if ($t !== null && isset($types[$t][$level]['fields'])) return ['fields' => $types[$t][$level]['fields'], 'source' => $t === $typeId ? 'rendered' : "borrowed from type $t"];
        }
        if ($level === 'organisation') {          // the organisation page is the same HTML for every type
            foreach ($types as $t => $levels) if (isset($levels[$level]['fields'])) return ['fields' => $levels[$level]['fields'], 'source' => "borrowed from type $t"];
        }
        return null;
    }

    public function scan(): array
    {
        $previous = Auth::guard('admin')->user();
        $actor = $previous ?: Admin::role('superadmin', 'admin')->first() ?: Admin::first();
        if ($actor) Auth::guard('admin')->setUser($actor);

        $out = ['generated_at' => now()->toDateTimeString(), 'hash' => $this->sourceHash(), 'types' => [], 'errors' => []];
        // The pages are rendered without the HTTP middleware that normally shares the validation errors with every view.
        $hadErrors = app('view')->shared('errors');
        app('view')->share('errors', new \Illuminate\Support\ViewErrorBag());
        try {
            foreach (OrganisationType::orderBy('id')->pluck('title', 'id') as $typeId => $title) {
                $org = Organisation::where('organisation_type_id', $typeId)->orderBy('id')->first();
                $stub = false;
                if (!$org) {
                    // No organisation of this type exists yet: present a real one AS this type, so the forms that change with the type are still read.
                    $base = Organisation::orderBy('id')->first();
                    if (!$base) continue;
                    $org = $this->asType($base, $typeId);
                    $stub = true;
                }
                $campus = Campus::where('organisation_id', $org->id)->orderBy('id')->first() ?: Campus::orderBy('id')->first();
                $dept = Department::where('organisation_id', $org->id)->orderBy('id')->first() ?: Department::orderBy('id')->first();
                $course = OrganisationCourse::where('organisation_id', $org->id)->orderBy('id')->first() ?: OrganisationCourse::orderBy('id')->first();
                // A sample that belongs to another organisation is shown as belonging to this one (only the type matters to the forms).
                if ($dept && $dept->organisation_id != $org->id) $dept->setRelation('organisation', $stub ? $org : $this->asType($dept->organisation, $typeId));
                elseif ($dept && $stub) $dept->setRelation('organisation', $org);
                if ($course && $course->organisation_id != $org->id) $course->setRelation('organisation', $stub ? $org : $this->asType($course->organisation, $typeId));
                elseif ($course && $stub) $course->setRelation('organisation', $org);

                $jobs = [
                    'organisation' => [fn () => app(OrganisationController::class)->edit($org), "organisations/{$org->id}", "Organisation #{$org->id}"],
                    'campus' => $campus ? [fn () => app(CampusController::class)->edit($org, $campus), "campuses/{$campus->id}", "Campus {$campus->id}"] : null,
                    'department' => $dept ? [fn () => app(DepartmentController::class)->edit($dept), "departments/{$dept->id}", "Department {$dept->id}"] : null,
                    'course' => $course ? [fn () => app(OrganisationCourseController::class)->edit(request(), $course), "organisation-courses/{$course->id}", "Programme #{$course->id}"] : null,
                ];
                foreach ($jobs as $level => $job) {
                    if (!$job) continue;
                    [$render, $needle, $sample] = $job;
                    try {
                        $view = $render();
                        $html = $view instanceof View ? $view->render() : null;
                        if ($html === null) throw new \RuntimeException('the edit action did not return a page');
                        $out['types'][$typeId][$level] = ['fields' => $this->controls($html, $needle), 'sample' => $sample, 'type_title' => $title];
                    } catch (\Throwable $e) {
                        $out['errors'][] = "type $typeId $level ($sample): " . $e->getMessage();
                    }
                }
            }
        } finally {
            if ($hadErrors !== null) app('view')->share('errors', $hadErrors);
            if ($previous) Auth::guard('admin')->setUser($previous); else Auth::guard('admin')->forgetUser();
        }
        return $out;
    }

    /** An unsaved copy of $org that reports $typeId as its organisation type (and keeps its id, so routes still resolve). */
    private function asType(Organisation $org, int $typeId): Organisation
    {
        $copy = $org->replicate();
        $copy->forceFill(['id' => $org->id, 'organisation_type_id' => $typeId]);
        $copy->exists = true;
        $copy->setRelation('organisationType', OrganisationType::find($typeId));
        return $copy;
    }

    /**
     * Base names of the controls in the main update form (the form whose action contains $needle).
     * A browser ties every control between <form ...> and </form> to that form, even where sloppy markup closes a <div> around the
     * form early (several edit pages do). A strict HTML parser would cut the form short there and miss whole sections, so the controls
     * are read from that stretch of the page instead of from the parsed tree.
     */
    private function controls(string $html, string $needle): array
    {
        if (!preg_match_all('/<form\b[^>]*>/i', $html, $forms, PREG_OFFSET_CAPTURE)) throw new \RuntimeException('the page has no form');
        $best = null;
        foreach ($forms[0] as [$tag, $pos]) {
            if (!preg_match('/\baction=["\']([^"\']*)["\']/i', $tag, $a) || !str_contains($a[1], $needle)) continue;
            $close = stripos($html, '</form>', $pos);
            $names = $this->namesIn(substr($html, $pos, ($close === false ? strlen($html) : $close) - $pos));
            if ($best === null || count($names) > count($best)) $best = $names;
        }
        if ($best === null) throw new \RuntimeException("no form with action containing \"$needle\"");
        return $best;
    }

    /** Sorted base names (repeaters like class_profile[0][year] count once as class_profile) of the visible controls in a piece of HTML. */
    private function namesIn(string $fragment): array
    {
        $dom = new \DOMDocument();
        libxml_use_internal_errors(true);
        $dom->loadHTML('<?xml encoding="utf-8"?><body>' . $fragment . '</body>', LIBXML_PARSEHUGE);
        libxml_clear_errors();
        $names = [];
        foreach (['input', 'select', 'textarea'] as $tag) {
            foreach ($dom->getElementsByTagName($tag) as $el) {
                $type = strtolower($el->getAttribute('type'));
                $name = $el->getAttribute('name');
                if ($name === '' || in_array($type, ['hidden', 'submit', 'button', 'image', 'reset'], true) || in_array($name, ['_token', '_method'], true)) continue;
                $names[preg_replace('/\[.*$/', '', $name)] = true;
            }
        }
        $names = array_keys($names);
        sort($names);
        return $names;
    }
}

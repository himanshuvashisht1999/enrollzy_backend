<?php

namespace App\Support\Institutes;

use App\Models\Campus;
use App\Models\CoachingCategory;
use App\Models\Exam;
use App\Models\Organisation;
use App\Models\OrganisationMedia;
use App\Models\OwnershipType;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

/**
 * Coaching institutes (organisation type 3), brand level: the extra fields of the Institute section on the organisation
 * create / edit forms (partials/institute_brand_fields), their choices, validation, the clean-up before save, the
 * photo / video gallery, and a summary of the institute's branches. OrganisationController calls it for type 3 only.
 */
class InstituteFields
{
    public const TYPE = 3;

    public const TEACHING_MODES = ['Offline (classroom)', 'Online live', 'Recorded', 'Hybrid'];

    public const VERIFICATION = ['Pending', 'Verified', 'Rejected'];

    /** multi-choice columns that are cleared when nothing is chosen (a multi-select posts nothing when empty) */
    private const LISTS = ['coaching_category_ids', 'exams_prepared_for', 'teaching_modes'];

    /** what the Institute section's selects offer */
    public static function options(?Organisation $org = null): array
    {
        $ownership = OwnershipType::where('status', 1)->orderBy('sort_order')->orderBy('title')->pluck('title')->all();
        $current = $org?->ownership_type;
        if ($current && !in_array($current, $ownership, true)) $ownership[] = $current; // an older value keeps showing until changed

        return [
            'ownershipTypes' => $ownership,
            'coachingCategories' => CoachingCategory::where('status', 1)->orderBy('sort_order')->get(['id', 'title']),
            'exams' => Exam::orderBy('name')->pluck('name')->filter()->unique()->values(),
            'teachingModes' => self::TEACHING_MODES,
            'verification' => self::VERIFICATION,
        ];
    }

    /** validation for the extra fields (added to the type-3 rules on store and update) */
    public static function rules(): array
    {
        $text = 'nullable|string|max:255';
        $url = 'nullable|url|max:255';
        return [
            'ownership_type' => 'nullable|string|max:100',
            'cin_number' => ['nullable', 'string', 'max:30', 'regex:/^[A-Z0-9]{21}$/i'],
            'udyam_number' => ['nullable', 'string', 'max:30', 'regex:/^UDYAM-[A-Z]{2}-\d{2}-\d{7}$/i'],
            'coaching_category_ids' => 'nullable|array', 'coaching_category_ids.*' => 'integer',
            'exams_prepared_for' => 'nullable|array', 'exams_prepared_for.*' => 'string|max:255',
            'teaching_modes' => 'nullable|array', 'teaching_modes.*' => 'in:' . implode(',', self::TEACHING_MODES),
            'official_website' => $url, 'helpdesk_email' => 'nullable|email|max:255',
            'helpdesk_contact_number' => 'nullable|string|max:50', 'whatsapp_number' => 'nullable|string|max:30',
            'facebook_url' => $url, 'instagram_url' => $url, 'youtube_url' => $url, 'telegram_url' => $url,
            'play_store_url' => $url, 'app_store_url' => $url,
            'founder_name' => $text, 'director_name' => $text,
            'number_of_campuses' => 'nullable|integer|min:0|max:100000',
            'cities_present_in' => 'nullable', 'states_present_in' => 'nullable',
            'scholarship_test_name' => $text, 'scholarship_test_dates' => $text, 'scholarship_test_url' => $url,
            'franchise_contact_name' => $text, 'franchise_contact_phone' => 'nullable|string|max:30', 'franchise_contact_email' => 'nullable|email|max:255',
            'average_rating' => 'nullable|numeric|between:0,5', 'total_reviews' => 'nullable|integer|min:0',
            'verification_status' => 'nullable|in:' . implode(',', self::VERIFICATION), 'data_source' => $text,
            'gallery_photos' => 'nullable|array', 'gallery_photos.*' => 'image|mimes:jpg,jpeg,png,webp|max:4096',
            'gallery_videos' => 'nullable|array', 'gallery_videos.*.url' => 'nullable|url|max:255', 'gallery_videos.*.caption' => 'nullable|string|max:255',
            'gallery' => 'nullable|array', 'gallery.*.caption' => 'nullable|string|max:255', 'gallery.*.url' => 'nullable|url|max:255',
        ];
    }

    public static function messages(): array
    {
        return [
            'cin_number.regex' => 'The CIN should be 21 letters and digits, e.g. U80903MH2015PTC123456.',
            'udyam_number.regex' => 'The Udyam number should look like UDYAM-MH-12-1234567.',
            'gallery_photos.*.image' => 'Gallery photos must be images (JPG, PNG or WebP).',
            'gallery_photos.*.max' => 'Each gallery photo must be 4 MB or smaller.',
            'gallery_videos.*.url.url' => 'Each video must be a full link, e.g. https://www.youtube.com/watch?v=…',
        ];
    }

    /** the saved values from what the form posted: cleared lists, comma lists split, codes in capitals */
    public static function prepare(Request $request, array $data): array
    {
        if (!$request->has('institute_form')) return $data; // not the Institute section (or an older form)
        foreach (self::LISTS as $f) {
            $data[$f] = array_values(array_filter((array) $request->input($f, []), fn ($v) => trim((string) $v) !== ''));
        }
        foreach (['cities_present_in', 'states_present_in'] as $f) {
            if ($request->exists($f)) $data[$f] = self::splitList($request->input($f));
        }
        foreach (['cin_number', 'udyam_number'] as $f) {
            if (!empty($data[$f])) $data[$f] = strtoupper(trim($data[$f]));
        }
        if (array_key_exists('verification_status', $data) && !$data['verification_status']) $data['verification_status'] = 'Pending';
        unset($data['institute_form'], $data['gallery'], $data['gallery_videos'], $data['gallery_photos'], $data['has_gallery_form'],
            $data['legal_documents_form'], $data['existing_legal_documents_urls']);
        return $data;
    }

    /** "Delhi, Kota , Delhi" → ['Delhi', 'Kota'] (arrays pass through cleaned) */
    public static function splitList($value): array
    {
        $items = is_array($value) ? $value : explode(',', (string) $value);
        return array_values(array_unique(array_filter(array_map(fn ($v) => trim((string) $v), $items), fn ($v) => $v !== '')));
    }

    /**
     * legal documents on save: the ones still listed on the form stay (removed ones go), new uploads are added.
     * Only paths the organisation already had are kept, so a posted list can't point at other files.
     */
    public static function legalDocuments(Request $request, Organisation $org, array $uploaded): ?array
    {
        if (!$request->has('legal_documents_form')) return $uploaded ? array_merge($org->legal_documents_urls ?? [], $uploaded) : null;
        $kept = array_values(array_intersect($org->legal_documents_urls ?? [], (array) $request->input('existing_legal_documents_urls', [])));
        return array_merge($kept, $uploaded);
    }

    /** photos and videos from the gallery block: new uploads and links added, captions updated, ticked ones removed */
    public static function syncMedia(Request $request, Organisation $org, string $campusId = ''): void
    {
        if (!$request->has('has_gallery_form')) return;
        $existing = OrganisationMedia::where('organisation_id', $org->id)->where('campus_id', $campusId)->get()->keyBy('id');
        foreach ((array) $request->input('gallery', []) as $id => $row) {
            $m = $existing->get((int) $id);
            if (!$m) continue;
            if (!empty($row['remove'])) {
                if ($m->file_path && is_file(public_path($m->file_path))) @unlink(public_path($m->file_path));
                $m->delete();
                continue;
            }
            $m->update(['caption' => trim((string) ($row['caption'] ?? '')) ?: null]
                + ($m->type === 'video' && !empty($row['url']) ? ['video_url' => trim($row['url'])] : []));
        }

        $next = (int) OrganisationMedia::where('organisation_id', $org->id)->where('campus_id', $campusId)->max('sort_order');
        $dir = $campusId === '' ? 'media/institutes/gallery' : 'media/campuses/gallery';
        foreach ((array) $request->file('gallery_photos', []) as $file) {
            if (!$file || !$file->isValid()) continue;
            $name = time() . '_' . Str::random(8) . '.' . strtolower($file->extension());
            $file->move(public_path($dir), $name);
            OrganisationMedia::create(['organisation_id' => $org->id, 'campus_id' => $campusId, 'type' => 'photo', 'file_path' => $dir . '/' . $name, 'sort_order' => ++$next]);
        }
        foreach ((array) $request->input('gallery_videos', []) as $row) {
            $url = trim((string) ($row['url'] ?? ''));
            if ($url === '') continue;
            OrganisationMedia::create(['organisation_id' => $org->id, 'campus_id' => $campusId, 'type' => 'video', 'video_url' => $url,
                'caption' => trim((string) ($row['caption'] ?? '')) ?: null, 'sort_order' => ++$next]);
        }
    }

    /** the institute's active branches: how many, and in which cities and states (for "Fill from branches") */
    public static function branchSummary(?Organisation $org): array
    {
        if (!$org?->id) return ['count' => 0, 'cities' => [], 'states' => []];
        $campuses = Campus::where('organisation_id', $org->id)->where('status', 1)->get(['city', 'state']);
        return [
            'count' => $campuses->count(),
            'cities' => self::splitList($campuses->pluck('city')->all()),
            'states' => self::splitList($campuses->pluck('state')->all()),
        ];
    }

    // ---------------------------------------------------------------------------------------------------------------
    // Branches (campuses of a coaching institute): the Location fields (every organisation type) and the Branch Details
    // and Photos & Videos tabs (type 3) on the campus create / edit forms.

    public const DAYS = ['Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat', 'Sun'];

    public const STAY_TYPES = ['Hostel', 'PG'];

    public const STAY_FOR = ['Boys', 'Girls', 'Co-ed'];

    /** form inputs that are not campuses columns (the campus save writes every posted key as a column) */
    public const BRANCH_NON_COLUMNS = ['branch_form', 'has_gallery_form', 'gallery', 'gallery_photos', 'gallery_videos'];

    public static function locationRules(): array
    {
        return [
            'locality' => 'nullable|string|max:255', 'nearest_landmark' => 'nullable|string|max:255',
            'latitude' => 'nullable|numeric|between:-90,90', 'longitude' => 'nullable|numeric|between:-180,180',
        ];
    }

    /** the Branch Details and Photos & Videos rules (institute branches) */
    public static function branchRules(?string $campusId = null): array
    {
        $gallery = array_filter(self::rules(), fn ($k) => str_starts_with($k, 'gallery'), ARRAY_FILTER_USE_KEY);
        return [
            'opening_time' => 'nullable|date_format:H:i', 'closing_time' => 'nullable|date_format:H:i',
            'working_days' => 'nullable|array', 'working_days.*' => 'in:' . implode(',', self::DAYS),
            'branch_head_name' => 'nullable|string|max:255', 'branch_head_phone' => 'nullable|string|max:30',
            'whatsapp_number' => 'nullable|string|max:30', 'lead_email' => 'nullable|email|max:255',
            'reading_room_hours' => 'nullable|string|max:100',
            'average_rating' => 'nullable|numeric|between:0,5', 'total_reviews' => 'nullable|integer|min:0',
            'slug' => ['nullable', 'string', 'max:255', 'regex:/^[a-z0-9]+(?:-[a-z0-9]+)*$/', \Illuminate\Validation\Rule::unique('campuses', 'slug')->ignore($campusId, 'id')],
            'nearby_accommodations' => 'nullable|array',
            'nearby_accommodations.*.name' => 'nullable|string|max:255',
            'nearby_accommodations.*.type' => 'nullable|in:' . implode(',', self::STAY_TYPES),
            'nearby_accommodations.*.for' => 'nullable|in:' . implode(',', self::STAY_FOR),
            'nearby_accommodations.*.rent_min' => 'nullable|integer|min:0', 'nearby_accommodations.*.rent_max' => 'nullable|integer|min:0',
            'nearby_accommodations.*.distance_km' => 'nullable|numeric|min:0|max:100', 'nearby_accommodations.*.phone' => 'nullable|string|max:30',
        ] + $gallery;
    }

    public static function branchMessages(): array
    {
        return [
            'slug.regex' => 'The page link may use only small letters, digits and single hyphens, e.g. allen-kota-talwandi.',
            'slug.unique' => 'Another branch already uses this page link.',
            'opening_time.date_format' => 'Enter the opening time as HH:MM.', 'closing_time.date_format' => 'Enter the closing time as HH:MM.',
        ] + self::messages();
    }

    /** a campus save's input: non-column keys out, working days and nearby stays cleaned (when the Branch Details tab was on the form) */
    public static function prepareBranch(Request $request, array $input): array
    {
        foreach (self::BRANCH_NON_COLUMNS as $k) unset($input[$k]);
        if ($request->has('branch_form')) {
            $input['working_days'] = array_values(array_intersect(self::DAYS, (array) $request->input('working_days', [])));
            $input['nearby_accommodations'] = self::nearbyStays($request->input('nearby_accommodations', []));
        }
        return $input;
    }

    /**
     * the campus form's tab autosave (CampusController::autosaveTab, already limited to campuses columns): the hostel list
     * cleaned, working days kept to real days, coordinates and rating dropped when out of range, and the page link made
     * URL-safe and left unchanged when another branch has it (the Update button reports that instead).
     */
    public static function autosaveBranch(array $data, Campus $campus): array
    {
        if (array_key_exists('nearby_accommodations', $data)) $data['nearby_accommodations'] = self::nearbyStays($data['nearby_accommodations']);
        if (array_key_exists('working_days', $data)) $data['working_days'] = array_values(array_intersect(self::DAYS, (array) $data['working_days']));
        foreach (['latitude' => 90, 'longitude' => 180, 'average_rating' => 5] as $f => $max) {
            if (isset($data[$f]) && (!is_numeric($data[$f]) || abs((float) $data[$f]) > $max)) unset($data[$f]);
        }
        foreach (['opening_time', 'closing_time'] as $f) {
            if (isset($data[$f]) && !preg_match('/^\d{2}:\d{2}(:\d{2})?$/', (string) $data[$f])) unset($data[$f]);
        }
        if (isset($data['slug'])) {
            $slug = Str::slug((string) $data['slug']);
            $taken = $slug === '' || Campus::where('slug', $slug)->where('id', '!=', $campus->id)->exists();
            if ($taken) unset($data['slug']); else $data['slug'] = $slug;
        }
        return $data;
    }

    /** nearby hostels / PGs: rows with a name, numbers as numbers, in the order given */
    public static function nearbyStays($rows): array
    {
        $out = [];
        foreach ((array) $rows as $r) {
            if (!is_array($r) || trim((string) ($r['name'] ?? '')) === '') continue;
            $num = fn ($k, $f = 'intval') => isset($r[$k]) && $r[$k] !== '' && is_numeric($r[$k]) ? $f($r[$k]) : null;
            $out[] = [
                'name' => trim($r['name']),
                'type' => in_array($r['type'] ?? '', self::STAY_TYPES, true) ? $r['type'] : null,
                'for' => in_array($r['for'] ?? '', self::STAY_FOR, true) ? $r['for'] : null,
                'rent_min' => $num('rent_min'), 'rent_max' => $num('rent_max'),
                'distance_km' => $num('distance_km', fn ($v) => round((float) $v, 1)),
                'phone' => trim((string) ($r['phone'] ?? '')) ?: null,
            ];
        }
        return $out;
    }

    /** a page on the student website (FRONTEND_URL, or enrollzy.com when the CRM has no separate one) */
    public static function websiteUrl(string $path): string
    {
        $base = (string) config('cns.frontend_url');
        if ($base === '' || str_contains($base, '://crm.') || rtrim($base, '/') === rtrim((string) config('app.url'), '/')) $base = 'https://enrollzy.com';
        return rtrim($base, '/') . '/' . ltrim($path, '/');
    }

    /** a YouTube link's thumbnail, for the gallery list (null for other links) */
    public static function videoThumb(?string $url): ?string
    {
        if ($url && preg_match('~(?:youtu\.be/|youtube\.com/(?:watch\?(?:.*&)?v=|embed/|shorts/|live/))([\w-]{11})~', $url, $m)) {
            return 'https://img.youtube.com/vi/' . $m[1] . '/mqdefault.jpg';
        }
        return null;
    }
}

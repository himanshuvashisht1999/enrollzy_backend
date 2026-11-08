<?php

namespace App\Support\Institutes;

use App\Models\OrganisationCourse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

/**
 * Coaching institute programmes (organisation_courses of an organisation of type 3): the choices, checks and saving of the
 * programme details added in Phase 3: product type, target classes, access validity, demo class, test-series details, fee details,
 * batches (course_batches), year-wise results (course_results) and toppers (course_toppers).
 * The form part is resources/views/admin/organisation-courses/partials/institute_program_extras.blade.php.
 */
class ProgramFields
{
    public const PRODUCT_TYPES = ['Classroom programme', 'Online live classes', 'Recorded course', 'Test series', 'Crash course', 'Foundation course',
        'Dropper batch', 'Weekend batch', 'Mentorship programme', 'Study material'];
    public const TARGET_CLASSES = ['Foundation (Class 6–10)', 'Class 11', 'Class 12', '12th Pass', 'Dropper', 'Graduate'];
    public const DELIVERY_MODES = ['Offline', 'Online', 'Hybrid', 'Recorded'];
    public const FEE_INCLUDES = ['Classes', 'Study material', 'Test series', 'Doubt sessions', 'App / online access', 'Reading room', 'Hostel', 'Meals'];

    public const BATCH_TIMINGS = ['Morning', 'Afternoon', 'Evening', 'Weekend'];
    public const BATCH_MEDIUMS = ['Hindi', 'English', 'Bilingual'];
    public const BATCH_STATUSES = ['Open', 'Closing soon', 'Full'];

    /** switches of this part of the form (each sends a hidden 0 when unticked) */
    public const BOOLEANS = ['demo_class_available', 'all_india_rank_available', 'performance_analysis_available', 'gst_included'];
    /** lists kept as JSON arrays */
    public const LISTS = ['target_classes' => self::TARGET_CLASSES, 'fee_includes' => self::FEE_INCLUDES];

    public const PHOTO_DIR = 'media/organisation_courses/toppers';
    private const INT_MAX = 4294967295;

    public static function rules(): array
    {
        $int = 'nullable|integer|min:0|max:' . self::INT_MAX;
        return [
            'product_type' => 'nullable|in:' . implode(',', self::PRODUCT_TYPES),
            'delivery_mode' => 'nullable|in:' . implode(',', self::DELIVERY_MODES),
            'target_classes' => 'nullable|array', 'target_classes.*' => 'nullable|in:' . implode(',', self::TARGET_CLASSES),
            'fee_includes' => 'nullable|array', 'fee_includes.*' => 'nullable|in:' . implode(',', self::FEE_INCLUDES),
            'access_validity_months' => 'nullable|integer|min:0|max:240',
            'demo_video_url' => 'nullable|url|max:255',
            'total_tests' => $int, 'full_syllabus_tests_count' => 'nullable|integer|min:0|max:100000', 'sectional_tests_count' => $int,
            'early_bird_discount_percent' => 'nullable|numeric|min:0|max:100', 'scholarship_discount_max_percent' => 'nullable|numeric|min:0|max:100',
            'emi_amount' => $int, 'emi_tenure_months' => 'nullable|integer|min:1|max:120',
            'fee_min' => $int,
            'fee_max' => [...explode('|', $int), function ($attr, $value, $fail) {
                $min = request('fee_min');
                if (is_numeric($min) && is_numeric($value) && $value < $min) $fail('Fee "to" must not be less than fee "from".');
            }],

            'batches' => 'nullable|array|max:50',
            'batches.*.name' => 'nullable|string|max:255',
            'batches.*.start_date' => 'nullable|date',
            'batches.*.timing' => 'nullable|in:' . implode(',', self::BATCH_TIMINGS),
            'batches.*.timing_details' => 'nullable|string|max:100',
            'batches.*.medium' => 'nullable|in:' . implode(',', self::BATCH_MEDIUMS),
            'batches.*.total_seats' => 'nullable|integer|min:0|max:100000',
            'batches.*.seats_left' => ['nullable', 'integer', 'min:0', 'max:100000', function ($attr, $value, $fail) {
                $total = request(str_replace('seats_left', 'total_seats', $attr));
                if (is_numeric($total) && is_numeric($value) && $value > $total) $fail('Seats left cannot be more than the total seats of the batch.');
            }],
            'batches.*.status' => 'nullable|in:' . implode(',', self::BATCH_STATUSES),

            'results' => 'nullable|array|max:50',
            'results.*.year' => 'nullable|integer|min:1980|max:' . (date('Y') + 1) . '|distinct',
            'results.*.selections' => $int, 'results.*.top_rank' => $int,
            'results.*.notes' => 'nullable|string|max:255',

            'toppers' => 'nullable|array|max:100',
            'toppers.*.id' => 'nullable|integer',
            'toppers.*.name' => 'nullable|string|max:255',
            'toppers.*.rank' => $int,
            'toppers.*.year' => 'nullable|integer|min:1980|max:' . (date('Y') + 1),
            'toppers.*.exam' => 'nullable|string|max:100',
            'toppers.*.photo' => 'nullable|image|mimes:jpg,jpeg,png,webp|max:2048',
        ];
    }

    public static function messages(): array
    {
        return [
            'results.*.year.distinct' => 'Each year can be listed only once in Year-wise Results.',
            'results.*.year.min' => 'Result years start from 1980.', 'results.*.year.max' => 'A result year cannot be in the future.',
            'toppers.*.year.max' => 'A topper\'s year cannot be in the future.',
            'toppers.*.photo.image' => 'A topper photo must be an image (JPG, PNG or WebP).', 'toppers.*.photo.mimes' => 'A topper photo must be JPG, PNG or WebP.',
            'toppers.*.photo.max' => 'A topper photo can be at most 2 MB.',
            'demo_video_url.url' => 'The demo video link must be a full link, starting with https://',
            'batches.*.start_date.date' => 'A batch start date is not a valid date.',
            'highest_rank_achieved.integer' => 'Highest Rank must be a number, e.g. 15 for AIR 15.',
            'target_classes.*.in' => 'Pick the target students from the list.', 'fee_includes.*.in' => 'Pick what the fee covers from the list.',
        ];
    }

    public static function attributes(): array
    {
        return [
            'product_type' => 'product type', 'access_validity_months' => 'access validity', 'demo_video_url' => 'demo video link',
            'total_tests' => 'total tests', 'full_syllabus_tests_count' => 'full-length tests', 'sectional_tests_count' => 'sectional tests',
            'early_bird_discount_percent' => 'early-bird discount', 'scholarship_discount_max_percent' => 'scholarship discount',
            'emi_amount' => 'EMI amount', 'emi_tenure_months' => 'EMI months', 'fee_min' => 'fee from', 'fee_max' => 'fee to',
            'batches.*.total_seats' => 'batch total seats', 'batches.*.seats_left' => 'batch seats left', 'batches.*.name' => 'batch name',
            'results.*.year' => 'result year', 'results.*.selections' => 'selections', 'results.*.top_rank' => 'top rank',
            'toppers.*.name' => 'topper name', 'toppers.*.rank' => 'topper rank', 'toppers.*.year' => 'topper year', 'toppers.*.exam' => 'topper exam',
        ];
    }

    /** The programme's own columns, ready to save: the lists as arrays of known choices (none = NULL); the repeaters are saved by sync(). */
    public static function prepare(array $data): array
    {
        unset($data['batches'], $data['results'], $data['toppers'], $data['program_extras_form']);
        foreach (self::LISTS as $field => $allowed) {
            if (!array_key_exists($field, $data)) continue;
            $picked = array_values(array_intersect($allowed, (array) ($data[$field] ?? [])));
            $data[$field] = $picked ?: null;
        }
        return $data;
    }

    /** Batches, year-wise results and toppers from the form (only when the form showed them). */
    public static function sync(Request $request, OrganisationCourse $course): void
    {
        if (!$request->has('program_extras_form')) return;
        $now = now();
        $num = fn ($v) => ($v === null || $v === '') ? null : max(0, (int) $v);
        $str = fn ($v) => ($v = trim((string) $v)) === '' ? null : $v;

        DB::transaction(function () use ($request, $course, $now, $num, $str) {
            // batches: a row needs a name or a start date
            $course->batches()->delete();
            $order = 0;
            foreach ((array) $request->input('batches', []) as $b) {
                $name = $str($b['name'] ?? null);
                $start = $str($b['start_date'] ?? null);
                if (!$name && !$start) continue;
                $total = $num($b['total_seats'] ?? null);
                $left = $num($b['seats_left'] ?? null);
                $status = in_array($b['status'] ?? null, self::BATCH_STATUSES, true) ? $b['status'] : 'Open';
                if ($left === 0 && $total) $status = 'Full';
                $course->batches()->create([
                    'name' => $name, 'start_date' => $start,
                    'timing' => in_array($b['timing'] ?? null, self::BATCH_TIMINGS, true) ? $b['timing'] : null,
                    'timing_details' => $str($b['timing_details'] ?? null),
                    'medium' => in_array($b['medium'] ?? null, self::BATCH_MEDIUMS, true) ? $b['medium'] : null,
                    'total_seats' => $total, 'seats_left' => $left, 'status' => $status, 'sort_order' => $order++,
                ]);
            }

            // year-wise results: a row needs a year
            $course->results()->delete();
            foreach ((array) $request->input('results', []) as $r) {
                $year = $num($r['year'] ?? null);
                if (!$year) continue;
                $course->results()->create(['year' => $year, 'selections' => $num($r['selections'] ?? null), 'top_rank' => $num($r['top_rank'] ?? null), 'notes' => $str($r['notes'] ?? null)]);
            }

            // toppers: a row needs a name; kept rows keep their photo unless a new one is chosen
            $existing = $course->toppers()->get()->keyBy('id');
            $kept = [];
            $order = 0;
            foreach ((array) $request->input('toppers', []) as $i => $t) {
                $name = $str($t['name'] ?? null);
                if (!$name) continue;
                $row = !empty($t['id']) ? $existing->get((int) $t['id']) : null;
                $row ??= $course->toppers()->make();
                $row->fill(['name' => $name, 'rank' => $num($t['rank'] ?? null), 'year' => $num($t['year'] ?? null), 'exam' => $str($t['exam'] ?? null),
                    'consent' => !empty($t['consent']), 'sort_order' => $order++]);
                $file = $request->file("toppers.$i.photo");
                if ($file && $file->isValid()) {
                    $dir = public_path(self::PHOTO_DIR);
                    if (!is_dir($dir)) @mkdir($dir, 0775, true);
                    $fileName = time() . '_' . uniqid() . '.' . strtolower($file->getClientOriginalExtension());
                    $file->move($dir, $fileName);
                    self::unlink($row->photo_path);
                    $row->photo_path = self::PHOTO_DIR . '/' . $fileName;
                } elseif (!empty($t['remove_photo'])) {
                    self::unlink($row->photo_path);
                    $row->photo_path = null;
                }
                $row->save();
                $kept[] = $row->id;
            }
            foreach ($existing as $id => $old) {
                if (in_array($id, $kept, true)) continue;
                self::unlink($old->photo_path);
                $old->delete();
            }
        });
    }

    /** A programme copy gets the same batches and year-wise results (toppers stay with the original: their photos are its files). */
    public static function copy(OrganisationCourse $from, OrganisationCourse $to): void
    {
        foreach ($from->batches as $b) $to->batches()->create($b->only(['name', 'start_date', 'timing', 'timing_details', 'medium', 'total_seats', 'seats_left', 'status', 'sort_order']));
        foreach ($from->results as $r) $to->results()->create($r->only(['year', 'selections', 'top_rank', 'notes']));
    }

    /** Removes the batches, results and toppers (with their photos) of these programmes. */
    public static function deleteFor(array $courseIds): void
    {
        if (!$courseIds) return;
        foreach (DB::table('course_toppers')->whereIn('organisation_course_id', $courseIds)->pluck('photo_path') as $p) self::unlink($p);
        foreach (['course_batches', 'course_results', 'course_toppers'] as $t) DB::table($t)->whereIn('organisation_course_id', $courseIds)->delete();
    }

    private static function unlink(?string $path): void
    {
        if ($path && str_starts_with($path, self::PHOTO_DIR . '/') && is_file(public_path($path))) @unlink(public_path($path));
    }
}

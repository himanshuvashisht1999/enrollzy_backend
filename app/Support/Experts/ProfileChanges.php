<?php

namespace App\Support\Experts;

use App\Models\Expert;
use App\Models\ExpertProfileChange;
use Illuminate\Support\Facades\DB;

/**
 * Experts' profile edits from the website (expert dashboard > My profile), approved or rejected here (Expert Management >
 * Profile changes). Approving writes the requested values to the expert; nothing on the public profile changes before.
 *
 * The website has the same field list (enrollzy_frontend App\Support\Experts\ProfileChanges::FIELDS): keep the two in step.
 */
class ProfileChanges
{
    /** field => [label, kind]; fee_30 = the fee for 30 minutes (stored as price_per_min) */
    public const FIELDS = [
        'name' => ['Full name', 'text'], 'designation' => ['Current role', 'text'], 'current_institute_name' => ['Organisation', 'text'],
        'highest_qualification' => ['Highest qualification', 'text'], 'years_of_experience_total' => ['Years of experience', 'number'],
        'no_of_students_counseled' => ['Students guided', 'text'], 'city' => ['City', 'text'], 'state' => ['State', 'text'],
        'expertise_areas' => ['Expertise', 'list'], 'levels' => ['Classes / levels', 'list'], 'language_of_teaching' => ['Languages', 'list'],
        'session_modes' => ['Sessions', 'list'], 'helps_with' => ['"Helps with" line', 'text'], 'short_bio' => ['One-line introduction', 'text'],
        'detailed_bio' => ['About you', 'long'], 'linkedin_profile_url' => ['LinkedIn', 'text'], 'fee_30' => ['Fee for 30 minutes (₹)', 'number'],
    ];

    /** the expert's live value of a field, in the request's form */
    public static function live(Expert $e, string $field)
    {
        if ($field === 'fee_30') return $e->price_per_min !== null && (float) $e->price_per_min > 0 ? (int) round($e->price_per_min * 30) : null;
        return $e->{$field};
    }

    /** write the requested values (and the new photo) to the expert, and mark the request approved */
    public static function approve(ExpertProfileChange $c, ?string $note, int $adminId): void
    {
        DB::transaction(function () use ($c, $note, $adminId) {
            $e = $c->expert;
            $set = [];
            foreach ((array) $c->changes as $k => $v) {
                if (!isset(self::FIELDS[$k])) continue;
                if ($k === 'fee_30') {
                    $set['price_per_min'] = $v ? round($v / 30, 2) : null;
                } elseif ($k === 'years_of_experience_total') {
                    $set[$k] = $v;
                    $set['exp'] = $v !== null && $v !== '' ? $v . ' years' : null;   // the older text field the site also reads
                } elseif ($k === 'language_of_teaching') {
                    $set[$k] = $set['languages_supported'] = array_values((array) $v);
                } else {
                    $set[$k] = $v;
                }
            }
            if ($c->photo && ($img = Applications::publishPhoto($c->photo))) {
                $set['img'] = $img;
                $set['profile_photo_url'] = null;   // the website shows profile_photo_url first: clear it so the new photo shows
            }
            $e->forceFill($set)->save();
            Docs::delete($c->photo);
            $c->update(['status' => 'approved', 'note' => $note, 'reviewed_by' => $adminId, 'reviewed_at' => now(), 'photo' => null]);
        });
    }

    public static function reject(ExpertProfileChange $c, string $note, int $adminId): void
    {
        Docs::delete($c->photo);
        $c->update(['status' => 'rejected', 'note' => $note, 'reviewed_by' => $adminId, 'reviewed_at' => now(), 'photo' => null]);
    }

    /** a value as text for the review page */
    public static function show($v, string $kind): string
    {
        if ($kind === 'list') return implode(', ', (array) $v) ?: '—';
        $v = trim((string) $v);
        return $v === '' ? '—' : $v;
    }
}

<?php

namespace App\Support\Experts;

use App\Models\Expert;
use App\Models\ExpertApplication;
use App\Models\ExpertCategory;
use App\Models\ExpertDocument;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

/**
 * Expert applications: who may apply (and when again), and what approving does.
 *
 * - One open application at a time (pending, under review, changes needed): the applicant edits it until staff decide.
 * - After "not approved" the person may apply again after the wait (settings.expert_reapply_wait_days, 30 by default),
 *   up to settings.expert_max_applications applications in all (3 by default). Staff can let a person apply again at
 *   once, even past the limit ("Allow another application"), which gives exactly one more.
 * - Approving creates the expert (private profile) linked to the applicant's website account, or links the expert
 *   that already has their mobile number; the documents move to the expert.
 *
 * The website has the same rules (enrollzy_frontend App\Support\Experts\Applications::eligibility): keep both in step.
 */
class Applications
{
    /**
     * The form's optional profile sections (expert_applications.profile, written by the website's
     * App\Support\Experts\Applications::profileFrom): each key is the experts column it fills on approval, with the
     * label staff see on the application.
     */
    public const PROFILE_KEYS = [
        'date_of_birth' => 'Date of birth', 'subject_specialization' => 'Subject specialization',
        'top_rank_students' => 'Top achievements of their students', 'awards_recognition' => 'Awards & recognition',
        'conference_talks' => 'Talks & events', 'research_publications' => 'Research & publications', 'consulting_advisory_roles' => 'Advisory roles',
        'curriculum_design_experience' => 'Curriculum work', 'patents' => 'Patents',
        'alumni_recruiter_connections' => 'Alumni & recruiters', 'industry_connection' => 'Industry',
        'youtube_channel_url' => 'YouTube', 'instagram_profile_url' => 'Instagram', 'telegram_channel_url' => 'Telegram',
        'intro_video_url' => 'Intro video', 'demo_lecture_videos' => 'Sample session videos', 'articles_written' => 'Articles',
    ];

    /** the application's profile sections, only the known keys */
    public static function profileOf(ExpertApplication $app): array
    {
        return array_intersect_key((array) json_decode((string) $app->profile, true), self::PROFILE_KEYS);
    }

    public static function limits(): array
    {
        $s = DB::table('settings')->first(['expert_max_applications', 'expert_reapply_wait_days']);
        return ['max' => max(1, (int) ($s->expert_max_applications ?? 3)), 'wait_days' => max(0, (int) ($s->expert_reapply_wait_days ?? 30))];
    }

    /**
     * Can this account start a new application? ['can' => bool, 'reason' => open|expert|limit|wait|null, 'used' => n,
     * 'max' => n, 'from' => Carbon|null (when "wait"), 'latest' => the last application or null].
     */
    public static function eligibility(int $userId): array
    {
        ['max' => $max, 'wait_days' => $wait] = self::limits();
        $apps = ExpertApplication::where('user_id', $userId)->orderByDesc('id')->get();
        $latest = $apps->first();
        $out = ['can' => false, 'reason' => null, 'used' => $apps->count(), 'max' => $max, 'from' => null, 'latest' => $latest];

        if (Expert::where('user_id', $userId)->exists() || $apps->contains('status', 'approved')) return ['reason' => 'expert'] + $out;
        if ($latest && $latest->isOpen()) return ['reason' => 'open'] + $out;
        if ($latest && $latest->reapply_allowed_at) return ['can' => true] + $out;              // staff said: one more, now
        if ($apps->count() >= $max) return ['reason' => 'limit'] + $out;
        if ($latest && $latest->status === 'rejected' && $wait) {
            $from = Carbon::parse($latest->decided_at ?? $latest->updated_at)->addDays($wait)->startOfDay();
            if ($from->isFuture()) return ['reason' => 'wait', 'from' => $from] + $out;
        }
        return ['can' => true] + $out;
    }

    /** a thread entry; a status change records from/to */
    public static function note(ExpertApplication $app, string $author, ?string $body, array $extra = []): void
    {
        $app->notes()->create(['author' => $author, 'body' => $body ? trim($body) : null] + $extra);
    }

    /** staff move the application to $status with an optional message (shown to the applicant unless $internal) */
    public static function setStatus(ExpertApplication $app, string $status, ?string $message, int $adminId, bool $internal = false): void
    {
        $from = $app->status;
        $app->status = $status;
        if (in_array($status, ['approved', 'rejected'], true)) {
            $app->decided_at = now();
            $app->decided_by = $adminId;
        }
        $app->save();
        self::note($app, 'staff', $message, ['admin_id' => $adminId, 'internal' => $internal, 'from_status' => $from, 'to_status' => $status]);
    }

    /**
     * Approve: the expert this applicant becomes. An expert already linked to the account, or one with the same mobile
     * and no account yet, is linked instead of making a second profile. Returns the expert.
     */
    public static function approve(ExpertApplication $app, int $categoryId, ?string $message, int $adminId): Expert
    {
        return DB::transaction(function () use ($app, $categoryId, $message, $adminId) {
            $mobile = substr(preg_replace('/\D/', '', $app->mobile), -10);
            $expert = Expert::where('user_id', $app->user_id)->first()
                ?? Expert::whereNull('user_id')->whereRaw("RIGHT(REPLACE(REPLACE(contact_number, ' ', ''), '-', ''), 10) = ?", [$mobile])->first();

            if ($expert) {
                $expert->forceFill(['user_id' => $app->user_id])->save();   // user_id is not mass assignable
            } else {
                $category = ExpertCategory::findOrFail($categoryId);
                $slug = Str::slug($app->name) ?: 'expert';
                for ($s = $slug, $i = 2; Expert::where('slug', $s)->exists(); $i++) $s = $slug . '-' . $i;
                $email = Expert::where('email', $app->email)->exists() ? null : $app->email;   // another profile has it: staff add one later
                $expert = Expert::create([
                    'name' => $app->name, 'slug' => $s, 'email' => $email, 'contact_number' => $mobile,
                    'password' => Str::random(40), 'role' => $category->name, 'expert_category_id' => $category->id,
                    'gender' => $app->gender ? ucfirst(strtolower($app->gender)) : null, 'city' => $app->city, 'state' => $app->state,
                    'designation' => $app->designation, 'current_institute_name' => $app->organisation,
                    'highest_qualification' => $app->highest_qualification,
                    'years_of_experience_total' => $app->years_of_experience, 'exp' => $app->years_of_experience ? $app->years_of_experience . ' years' : null,
                    'no_of_students_counseled' => $app->students_counselled,
                    'expertise_areas' => $app->expertise_areas ?? [], 'levels' => $app->levels ?? [],
                    'language_of_teaching' => $app->languages ?? [], 'languages_supported' => $app->languages ?? [], 'session_modes' => $app->session_modes ?? [],
                    'short_bio' => $app->short_bio, 'detailed_bio' => $app->detailed_bio,
                    'linkedin_profile_url' => $app->linkedin_url, 'website_url' => $app->website_url,
                    'price_per_min' => $app->proposed_fee ? round($app->proposed_fee / 30, 2) : null,
                    // live but hidden: staff check pricing and slots, then set the profile Public
                    'status' => 'Active', 'profile_visibility' => 'Private', 'verification_status' => 'Verified',
                    'data_source' => 'Expert application #' . $app->id, 'img' => self::publishPhoto($app->photo),
                ] + self::profileOf($app));   // date of birth, subjects, students' achievements, beyond the classroom, links
                $expert->forceFill(['user_id' => $app->user_id])->save();   // user_id is not mass assignable
            }

            ExpertDocument::where('application_id', $app->id)->update(['expert_id' => $expert->id]);
            $app->expert_id = $expert->id;
            self::setStatus($app, 'approved', $message, $adminId);
            return $expert;
        });
    }

    /** the application photo becomes the profile photo (public uploads/experts, like photos added in the CRM) */
    public static function publishPhoto(?string $path): ?string
    {
        $file = Docs::absolute($path);
        if (!$file) return null;
        $name = 'app_' . Str::random(16) . '.' . pathinfo($file, PATHINFO_EXTENSION);
        if (!is_dir(public_path('uploads/experts'))) mkdir(public_path('uploads/experts'), 0755, true);
        copy($file, public_path('uploads/experts/' . $name));
        return 'uploads/experts/' . $name;
    }
}

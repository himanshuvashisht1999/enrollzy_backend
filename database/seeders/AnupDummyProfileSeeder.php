<?php

namespace Database\Seeders;

use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

/**
 * DUMMY TEST DATA for the expert "Anup Kumar Vishal" (slug anup-kumar-vishal), so his profile page shows every section.
 *
 *   php artisan db:seed --class=AnupDummyProfileSeeder           fill the profile
 *   php artisan db:seed --class=AnupDummyProfileRollbackSeeder   put everything back as it was
 *
 * - Fills only fields that are empty today. Name, email, password, photo and slug are never touched.
 * - Saves the expert's current row to storage/app/dummy/anup_profile_backup.json first (used by the rollback).
 * - Adds open slots (next 14 days), 3 sample students (…@example.test), 3 completed bookings and 3 reviews,
 *   all marked DUMMY so they are easy to find and are removed by the rollback.
 * - For local testing only. Do not run on the live database.
 */
class AnupDummyProfileSeeder extends Seeder
{
    public const SLUG = 'anup-kumar-vishal';
    public const BACKUP = 'dummy/anup_profile_backup.json';

    public function run(): void
    {
        $expert = DB::table('experts')->where('slug', self::SLUG)->first()
            ?? DB::table('experts')->where('name', 'Anup Kumar Vishal')->first();
        if (!$expert) { $this->command->error('Expert "Anup Kumar Vishal" not found.'); return; }

        $backupPath = storage_path('app/' . self::BACKUP);
        if (is_file($backupPath)) {
            $this->command->error('Dummy data was already added (' . $backupPath . '). Run AnupDummyProfileRollbackSeeder first.');
            return;
        }

        $cols = Schema::getColumnListing('experts');
        $created = ['slots' => [], 'users' => [], 'bookings' => [], 'reviews' => []];

        // ---------- 1. the expert's own fields (only where empty) ----------
        $careerCats = $this->ids('career_categories', 'name', ['Engineering', 'Science']);
        $careerClasses = $this->ids('career_roadmap_stages', 'title', ['Class 10', 'Class 11', 'Class 12', 'Graduation']);

        $fill = [
            'contact_number' => '9999900011', // test number: sign in on the website with OTP (on localhost the test OTP works)
            'designation' => 'Mentor',
            'gender' => 'Male',
            'short_bio' => 'JEE and engineering admissions mentor with 12 years of classroom and counselling experience in Patna.',
            'detailed_bio' => "I started as a Physics faculty and moved into admission counselling after seeing how many good students lost seats to poor choice filling. Today I help families plan the two years before JEE, choose between NIT, IIIT and private colleges, and fill JoSAA and state counselling choices round by round.\n\nMy sessions are practical: we look at your rank or mock scores, your budget and your interests, and leave with a written shortlist and next steps.",
            'helps_with' => 'Helps Class 11–12 students and droppers pick the right college, branch and exam plan.',
            'city' => 'Patna',
            'state' => 'Bihar',
            'subject_specialization' => ['Physics', 'Maths'],
            'expertise_areas' => ['Career Guidance', 'University Admissions', 'Competitive Exams', 'Scholarships'],
            'levels' => ['Class 9–10', 'Class 11–12', 'UG / PG'],
            'session_modes' => ['Online', 'In-person'],
            'offers_free_intro' => 1,
            'free_intro_minutes' => 15,
            'price_per_min' => 17,
            'career_category_ids' => $careerCats,
            'career_class_ids' => $careerClasses,
            'highest_qualification' => 'M.Tech',
            'degree' => 'B.Tech, Electrical Engineering',
            'exp' => '12 Years',
            'years_of_experience_total' => 12,
            'years_of_experience_current_institute' => 7,
            'other_qualifications' => [
                ['name' => 'M.Tech – Power Systems', 'institute' => 'Sample Institute of Technology', 'year' => 2014],
                ['name' => 'B.Tech – Electrical Engineering', 'institute' => 'Sample Engineering College', 'year' => 2012],
            ],
            'certifications' => ['Certified Career Counsellor', 'Psychometric Assessment'],
            'exams_cleared' => ['JEE Main', 'JEE Advanced', 'BCECE', 'CUET UG'],
            'notable_achievements' => ['Best Faculty Award, Vishal Academy (2022)', 'Speaker, Bihar Career Fair (2023)'],
            'industry_experience' => 1,
            'current_institute_name' => 'Vishal Academy, Patna',
            'faculty_type' => 'Full-time',
            'joining_year' => 2018,
            'courses_taught' => ['JEE Physics', 'Class 12 Boards Physics', 'Counselling workshop'],
            'target_batches' => ['Class 11', 'Class 12', 'Dropper'],
            'average_batch_size_handled' => 45,
            'teaching_style' => 'Exam-oriented',
            'language_of_teaching' => ['Hindi', 'English', 'Hinglish'],
            'lecture_mode' => 'Hybrid',
            'weekly_classes_count' => 12,
            'doubt_solving_sessions' => 1,
            'one_to_one_mentoring' => 1,
            'no_of_students_counseled' => 2400,
            'students_selected_count' => 310,
            'best_result_year' => 2024,
            'result_verification_source' => 'Institute result list (dummy)',
            'top_rank_students' => [
                ['name' => 'Student A', 'rank' => 'AIR 412', 'exam' => 'JEE Advanced', 'year' => 2024],
                ['name' => 'Student B', 'rank' => 'AIR 1,890', 'exam' => 'JEE Main', 'year' => 2024],
                ['name' => 'Student C', 'rank' => 'Rank 37', 'exam' => 'BCECE', 'year' => 2023],
                ['name' => 'Student D', 'rank' => '99.2 percentile', 'exam' => 'CUET UG', 'year' => 2024],
            ],
            'articles_written' => [
                ['title' => 'NIT vs IIIT vs private: how to choose', 'url' => 'https://example.com/articles/nit-vs-iiit'],
                ['title' => 'JoSAA choice filling, step by step', 'url' => 'https://example.com/articles/josaa-choice-filling'],
            ],
            'linkedin_profile_url' => 'https://example.com/linkedin/anup',
            'youtube_channel_url' => 'https://example.com/youtube/anup',
            'instagram_profile_url' => 'https://example.com/instagram/anup',
            'telegram_channel_url' => 'https://example.com/telegram/anup',
            'show_on_career_pages' => 1,
            'status' => 'Active',
            'profile_visibility' => 'Public',
            // "Profile page" tab of the CRM form
            'previous_institutes' => [
                ['role' => 'Physics faculty', 'name' => 'Sample Coaching Centre, Patna', 'years' => '2013 – 2018'],
                ['role' => 'Junior engineer', 'name' => 'Sample Power Utility, Bihar', 'years' => '2012 – 2013'],
            ],
            'counseling_specialization' => ['JoSAA choice filling', 'Branch selection', 'Drop-year planning'],
            'one_on_one_counseling' => 1,
            'goal_oriented_planning' => 1,
            'flexible_scheduling' => 1,
            'average_wait_time' => '2 hours',
            'exam_success_rate' => '78% of the 2024 batch qualified JEE Main',
            'students_admitted_to_top_university' => '140+ students at NITs and IIITs',
            'scholarship_conversion_rate' => '35 students won merit scholarships',
            'repeat_counseling_rate' => '1 in 3 families book a second session',
            'awards_recognition' => ['Bihar Educator of the Year nominee (2024)'],
            'conference_talks' => "JoSAA workshop, Sample Science College, Patna (2024)\nParents' session on engineering admissions, Gaya (2023)",
            'research_publications' => 'Article series on JEE preparation habits (2022)',
            'consulting_advisory_roles' => 'Admissions advisor, Vishal Academy',
            'curriculum_design_experience' => 'Designed the two-year JEE Physics plan at Vishal Academy',
            'university_admission_office_access' => 'In touch with NIT and IIIT admission cells',
            'alumni_recruiter_connections' => 'Alumni network of 300+ engineers',
            'student_testimonials' => [
                ['name' => 'Sample Student D', 'about' => 'JEE 2024, now at a sample NIT', 'text' => 'He made me drop two colleges I had no chance at and add three I did. I got a seat in round 3.'],
                ['name' => 'Sample Parent E', 'about' => 'Parent, Class 12 student', 'text' => 'Clear about fees and hostels. We knew our budget before the counselling started.'],
            ],
            'intro_video_url' => 'https://example.com/videos/anup-intro',
            'demo_lecture_videos' => ['https://example.com/videos/rotational-motion-demo', 'https://example.com/videos/josaa-choice-filling'],
        ];
        // yes/no fields the design shows as "on": set even though 0 counts as a value
        $forceOn = ['offers_free_intro', 'industry_experience', 'doubt_solving_sessions', 'one_to_one_mentoring', 'show_on_career_pages', 'one_on_one_counseling', 'goal_oriented_planning', 'flexible_scheduling'];

        $update = [];
        $skippedMissing = [];
        foreach ($fill as $col => $val) {
            if (!in_array($col, $cols, true)) { $skippedMissing[] = $col; continue; }
            if (!in_array($col, $forceOn, true) && !$this->isEmpty($expert->{$col})) continue;
            $update[$col] = is_array($val) ? json_encode($val, JSON_UNESCAPED_UNICODE) : $val;
        }
        if (in_array('data_source', $cols, true)) $update['data_source'] = 'Dummy test data (AnupDummyProfileSeeder)';
        if (in_array('updated_at', $cols, true)) $update['updated_at'] = now();

        // ---------- 2. backup first ----------
        @mkdir(dirname($backupPath), 0775, true);
        $backup = ['expert_id' => $expert->id, 'original' => (array) $expert, 'changed' => array_keys($update), 'created' => $created];
        file_put_contents($backupPath, json_encode($backup, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));

        try {
        DB::transaction(function () use ($expert, $update, &$created) {
            DB::table('experts')->where('id', $expert->id)->update($update);

            // ---------- 3. open slots for the next 14 days ----------
            $plan = [ // weekday plan: [start, minutes, mode, cost]
                'weekday' => [['16:00', 15, 'video', 0], ['18:30', 30, 'video', 499], ['19:30', 30, 'audio', 499], ['20:30', 30, 'chat', 499]],
                'saturday' => [['10:00', 60, 'video', 899], ['11:30', 60, 'audio', 899]],
            ];
            for ($d = 0; $d < 14; $d++) {
                $day = Carbon::today()->addDays($d);
                if ($day->isSunday()) continue;
                foreach ($day->isSaturday() ? $plan['saturday'] : $plan['weekday'] as [$start, $min, $mode, $cost]) {
                    $from = Carbon::parse($day->toDateString() . ' ' . $start);
                    if ($from->isPast()) continue;
                    $created['slots'][] = $this->insert('expert_slots', [
                        'expert_id' => $expert->id, 'date' => $day->toDateString(), 'start_time' => $from->format('H:i:s'),
                        'end_time' => $from->copy()->addMinutes($min)->format('H:i:s'), 'is_recurring' => 0, 'status' => 'available',
                        'mode' => $mode, 'cost' => $cost,
                    ]);
                }
            }

            // ---------- 4. three past sessions, each with a review ----------
            $reviews = [
                ['Sample Student A', 5, 'Clear plan for JoSAA rounds. He told me which choices were unrealistic for my rank instead of just agreeing.', 'video', 14],
                ['Sample Parent B', 5, 'Explained fees, hostel and loan options patiently. We left with a written shortlist.', 'video', 30],
                ['Sample Student C', 4, 'Useful advice. Chat replies were a little slow in the evening.', 'chat', 90],
            ];
            foreach ($reviews as $i => [$name, $stars, $comment, $mode, $ago]) {
                $when = Carbon::now()->subDays($ago)->setTime(18, 30);
                $userId = $this->insert('users', [
                    'name' => $name, 'email' => 'dummy.' . Str::slug($name, '.') . '@example.test',
                    'password' => Hash::make(Str::random(32)), 'role' => 'student', 'status' => 'Active', 'source' => 'DUMMY seeder',
                ]);
                $created['users'][] = $userId;
                $slotId = $this->insert('expert_slots', [
                    'expert_id' => $expert->id, 'date' => $when->toDateString(), 'start_time' => $when->format('H:i:s'),
                    'end_time' => $when->copy()->addMinutes(30)->format('H:i:s'), 'is_recurring' => 0, 'status' => 'booked', 'mode' => $mode, 'cost' => 499,
                ]);
                $created['slots'][] = $slotId;
                $bookingId = $this->insert('bookings', [
                    'booking_id' => 'DUMMY-ANUP-' . ($i + 1), 'user_id' => $userId, 'expert_id' => $expert->id, 'slot_id' => $slotId,
                    'booking_date' => $when->copy()->subDays(2), 'status' => 'completed', 'amount' => 499, 'platform_fee' => 99.80,
                    'expert_earning' => 399.20, 'payment_status' => 'paid', 'notes' => 'DUMMY test booking',
                    'topic' => 'University Admissions', 'attendee' => $i === 1 ? 'Parent' : 'Student', 'class_level' => 'Class 11–12',
                    'session_summary' => $i === 2 ? null : "We went through your JEE Main score and home-state quota.

Shortlist: NIT Patna (ECE), IIIT Bhagalpur (CSE), NIT Jamshedpur (EE) as safe options; two reach options for round 1.

Next steps: register for JoSAA by the deadline, keep the documents list ready, and book a 20-minute check before round 3.",
                ], $when);
                $created['bookings'][] = $bookingId;
                $created['reviews'][] = $this->insert('reviews', [
                    'booking_id' => $bookingId, 'expert_id' => $expert->id, 'user_id' => $userId,
                    'rating' => $stars, 'comment' => $comment, 'is_verified' => 1,
                ], $when->copy()->addDay());
            }

            // ---------- 5. a few upcoming times already booked (the profile strikes them through) ----------
            for ($d = 0, $n = 0; $d < 14 && $n < 4; $d++) {
                $day = Carbon::today()->addDays($d);
                $from = Carbon::parse($day->toDateString() . ' 17:00');
                if ($day->isWeekend() || $from->isPast()) continue;
                $slotId = $this->insert('expert_slots', [
                    'expert_id' => $expert->id, 'date' => $day->toDateString(), 'start_time' => '17:00:00', 'end_time' => '17:30:00',
                    'is_recurring' => 0, 'status' => 'booked', 'mode' => 'video', 'cost' => 499,
                ]);
                $created['slots'][] = $slotId;
                $created['bookings'][] = $this->insert('bookings', [
                    'booking_id' => 'DUMMY-ANUP-UP' . (++$n), 'user_id' => $created['users'][$n % count($created['users'])], 'expert_id' => $expert->id, 'slot_id' => $slotId,
                    'booking_date' => now(), 'status' => 'confirmed', 'amount' => 499, 'platform_fee' => 99.80,
                    'expert_earning' => 399.20, 'payment_status' => $n === 2 ? 'pending' : 'paid', 'notes' => 'DUMMY test booking. Want to compare NIT and IIIT options for ECE.',
                    'meeting_link' => $n === 2 ? null : 'https://meet.google.com/dummy-anup-' . $n, 'topic' => 'Competitive Exams', 'attendee' => 'Student and parent', 'class_level' => 'Class 11–12',
                ]);
            }
        });
        } catch (\Throwable $ex) {
            @unlink($backupPath); // nothing was saved, so no backup is needed
            $this->command->error('Nothing was changed. Error: ' . $ex->getMessage());
            return;
        }

        $backup['created'] = $created;
        file_put_contents($backupPath, json_encode($backup, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));

        $this->command->info('Anup Kumar Vishal (id ' . $expert->id . '): ' . count($update) . ' fields filled, ' . count($created['slots']) . ' slots, '
            . count($created['bookings']) . ' bookings, ' . count($created['reviews']) . ' reviews added.');
        if ($skippedMissing) $this->command->warn('Columns not in this database (run php artisan migrate first): ' . implode(', ', $skippedMissing));
        $this->command->line('Backup: ' . $backupPath);
    }

    /** insert only the columns the table has; returns the new id */
    private function insert(string $table, array $row, ?Carbon $at = null): int
    {
        $cols = Schema::getColumnListing($table);
        $at = $at ?: now();
        if (in_array('created_at', $cols, true)) $row['created_at'] = $at;
        if (in_array('updated_at', $cols, true)) $row['updated_at'] = $at;
        return (int) DB::table($table)->insertGetId(array_intersect_key($row, array_flip($cols)));
    }

    private function isEmpty($v): bool
    {
        if ($v === null) return true;
        $t = trim((string) $v);
        return $t === '' || $t === '[]' || $t === '{}' || $t === 'null' || $t === '0' || $t === '[{"name":null,"institute":null,"year":null}]';
    }

    /** ids of rows whose column contains any of the words (empty list when the table is missing) */
    private function ids(string $table, string $col, array $words): array
    {
        if (!Schema::hasTable($table)) return [];
        $q = DB::table($table)->where(function ($q) use ($col, $words) { foreach ($words as $w) $q->orWhere($col, 'like', '%' . $w . '%'); });
        return $q->pluck('id')->map(fn ($id) => (int) $id)->all();
    }
}

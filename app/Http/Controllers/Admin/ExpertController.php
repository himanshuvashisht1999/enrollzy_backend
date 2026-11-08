<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Expert;
use Illuminate\Http\Request;
use File;

class ExpertController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $query = Expert::query();

        // Filters
        if ($request->filled('role')) {
            $query->where('role', 'like', '%' . $request->role . '%');
        }

        if ($request->filled('search')) {
            $query->where('name', 'like', '%' . $request->search . '%')
                  ->orWhere('degree', 'like', '%' . $request->search . '%');
        }

        $experts = $query->latest()->paginate(10);
        
        // Get unique roles for filter dropdown
        $roles = Expert::distinct()->pluck('role')->filter()->toArray();

        return view('admin.experts.index', compact('experts', 'roles'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('admin.experts.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required',
            'expert_category_id' => 'required|exists:expert_categories,id',
            'email' => 'required|email|unique:experts,email',
            'password' => 'required|min:6',
            'img' => 'required|image|mimes:jpeg,png,jpg,gif,webp|max:2048',
            // Simple validation for new fields
            'slug' => 'nullable|unique:experts,slug',
            // the expert signs in on the website with OTP to this number (their account is linked by it)
            'contact_number' => ['required', 'regex:/^[6-9]\d{9}$/', 'unique:experts,contact_number'],
            'price_per_min' => 'nullable|numeric|min:0',
            'city' => 'nullable|string|max:100', 'state' => 'nullable|string|max:100', 'helps_with' => 'nullable|string|max:200',
            'no_of_students_counseled' => 'nullable|integer|min:0',
        ], ['contact_number.required' => "Enter the expert's 10-digit mobile number: they sign in on the website with OTP to it.", 'contact_number.regex' => 'Enter a valid 10-digit mobile number (starting with 6, 7, 8 or 9).', 'contact_number.unique' => 'Another expert already has this mobile number.']);

        $data = $request->all();
        $data['is_featured'] = $request->has('is_featured') ? (int)$request->is_featured : 0;

        // SYNC ROLE Column for Legacy Support / DB Constraint
        $category = \App\Models\ExpertCategory::find($request->expert_category_id);
        $data['role'] = $category->name;

        // Handle Image
        if ($request->hasFile('img')) {
            $imageName = time() . '.' . $request->img->extension();
            $request->img->move(public_path('uploads/experts'), $imageName);
            $data['img'] = 'uploads/experts/' . $imageName;
            // Also set profile_photo_url if empty
            if(empty($data['profile_photo_url'])) {
                $data['profile_photo_url'] = asset($data['img']);
            }
        }

        // Handle Booleans (Legacy + New)
        $booleans = [
            'one_on_one_counseling', 'group_counseling', 'psychometric_based_counseling', 
            'data_driven_career_mapping', 'goal_oriented_planning', 'flexible_scheduling',
            // New
            // (doubt_solving_sessions and one_to_one_mentoring were on the removed Teaching tab: left as they are)
            'industry_experience',
            'verified_student_reviews_only', 'public_contact_allowed', 'profile_claimed', 'show_on_career_pages',
            // the website's experts list
            'offers_free_intro',
        ];
        foreach ($booleans as $bool) {
            $data[$bool] = $request->has($bool);
        }

        // Handle Arrays & JSON
        $arrayFields = [
            'subject_specialization', 'other_qualifications', 'certifications', 
            'exams_cleared', 'notable_achievements',
            'language_of_teaching', 'demo_lecture_videos', // (courses_taught, target_batches: removed Institute tab, left as they are)
            'articles_written', 'focus_keywords'
        ];
        foreach ($arrayFields as $field) {
            $data[$field] = $request->input($field, []);
            if ($field === 'other_qualifications') {
                $data[$field] = array_values($data[$field]);
            }
            // articles: rows of [title, url]; a row without a URL is dropped
            if ($field === 'articles_written') {
                $data[$field] = array_values(array_filter(array_map(fn ($a) => is_array($a)
                    ? ['title' => trim((string) ($a['title'] ?? '')), 'url' => trim((string) ($a['url'] ?? ''))]
                    : ['title' => '', 'url' => trim((string) $a)], (array) $data[$field]), fn ($a) => $a['url'] !== ''));
            }
        }
        
        // Experts list: only known choices (expertise from the managed list, incl. hidden ones an expert already has; LEVELS, CONSULTATION)
        foreach (['expertise_areas' => \App\Models\ExpertiseArea::pluck('name')->all(), 'levels' => Expert::LEVELS, 'session_modes' => Expert::CONSULTATION] as $field => $allowed) {
            $data[$field] = array_values(array_intersect($allowed, (array) $request->input($field, [])));
        }
        $data['free_intro_minutes'] = max(5, min(60, (int) $request->input('free_intro_minutes', 15) ?: 15));

        // Career pages: the career categories and classes the expert advises on (empty: all)
        foreach (['career_category_ids', 'career_class_ids'] as $field) $data[$field] = array_values(array_map('intval', (array) $request->input($field, [])));

        // the website's expert profile ("Profile page" tab)
        $data = array_merge($data, $this->profileFields($request));

        Expert::create($data);

        return redirect()->route('experts.index')->with('success', 'Expert created successfully.');
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Expert $expert)
    {
        $activeCommission = \App\Models\ExpertCommission::where('expert_id', $expert->id)
            ->where('is_active', true)
            ->latest()
            ->first();
            
        return view('admin.experts.edit', compact('expert', 'activeCommission'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Expert $expert)
    {
        $request->validate([
            'name' => 'required',
            'expert_category_id' => 'required|exists:expert_categories,id',
            'email' => 'required|email|unique:experts,email,' . $expert->id,
            'password' => 'nullable|min:6',
            'img' => 'nullable|image|mimes:jpeg,png,jpg,gif,webp|max:2048',
             'slug' => 'nullable|unique:experts,slug,' . $expert->id,
            'contact_number' => ['required', 'regex:/^[6-9]\d{9}$/', 'unique:experts,contact_number,' . $expert->id],
             'price_per_min' => 'nullable|numeric|min:0',
            'city' => 'nullable|string|max:100', 'state' => 'nullable|string|max:100', 'helps_with' => 'nullable|string|max:200',
            'no_of_students_counseled' => 'nullable|integer|min:0',
        ], ['contact_number.required' => "Enter the expert's 10-digit mobile number: they sign in on the website with OTP to it.", 'contact_number.regex' => 'Enter a valid 10-digit mobile number (starting with 6, 7, 8 or 9).', 'contact_number.unique' => 'Another expert already has this mobile number.']);

        $data = $request->all();
        $data['is_featured'] = $request->has('is_featured') ? (int)$request->is_featured : 0;

        // SYNC ROLE Column for Legacy Support / DB Constraint
        $category = \App\Models\ExpertCategory::find($request->expert_category_id);
        $data['role'] = $category->name;

        // Handle Password
        if ($request->filled('password')) {
            $data['password'] = $request->password; // Model hashes it
        } else {
            unset($data['password']);
        }

        // Handle Image
        if ($request->hasFile('img')) {
            // Delete old image
            if ($expert->img && File::exists(public_path($expert->img))) {
                File::delete(public_path($expert->img));
            }

            $imageName = time() . '.' . $request->img->extension();
            $request->img->move(public_path('uploads/experts'), $imageName);
            $data['img'] = 'uploads/experts/' . $imageName;
             // Update profile_photo_url if it was using default
             if(empty($data['profile_photo_url'])) {
                $data['profile_photo_url'] = asset($data['img']);
            }
        }

        // Handle Booleans
        $booleans = [
            'one_on_one_counseling', 'group_counseling', 'psychometric_based_counseling', 
            'data_driven_career_mapping', 'goal_oriented_planning', 'flexible_scheduling',
             // New
            // (doubt_solving_sessions and one_to_one_mentoring were on the removed Teaching tab: left as they are)
            'industry_experience',
            'verified_student_reviews_only', 'public_contact_allowed', 'profile_claimed', 'show_on_career_pages',
            // the website's experts list
            'offers_free_intro',
        ];
        foreach ($booleans as $bool) {
            $data[$bool] = $request->has($bool);
        }

        // Handle Arrays & JSON
        $arrayFields = [
            'subject_specialization', 'other_qualifications', 'certifications', 
            'exams_cleared', 'notable_achievements',
            'language_of_teaching', 'demo_lecture_videos', // (courses_taught, target_batches: removed Institute tab, left as they are)
            'articles_written', 'focus_keywords'
        ];
        foreach ($arrayFields as $field) {
            $data[$field] = $request->input($field, []);
            if ($field === 'other_qualifications') {
                $data[$field] = array_values($data[$field]);
            }
            // articles: rows of [title, url]; a row without a URL is dropped
            if ($field === 'articles_written') {
                $data[$field] = array_values(array_filter(array_map(fn ($a) => is_array($a)
                    ? ['title' => trim((string) ($a['title'] ?? '')), 'url' => trim((string) ($a['url'] ?? ''))]
                    : ['title' => '', 'url' => trim((string) $a)], (array) $data[$field]), fn ($a) => $a['url'] !== ''));
            }
        }

        // Experts list: only known choices (expertise from the managed list, incl. hidden ones an expert already has; LEVELS, CONSULTATION)
        foreach (['expertise_areas' => \App\Models\ExpertiseArea::pluck('name')->all(), 'levels' => Expert::LEVELS, 'session_modes' => Expert::CONSULTATION] as $field => $allowed) {
            $data[$field] = array_values(array_intersect($allowed, (array) $request->input($field, [])));
        }
        $data['free_intro_minutes'] = max(5, min(60, (int) $request->input('free_intro_minutes', 15) ?: 15));

        // Career pages: the career categories and classes the expert advises on (empty: all)
        foreach (['career_category_ids', 'career_class_ids'] as $field) $data[$field] = array_values(array_map('intval', (array) $request->input($field, [])));

        // the website's expert profile ("Profile page" tab)
        $data = array_merge($data, $this->profileFields($request));

        $expert->update($data);
        // a new mobile number: the old website account no longer signs in as this expert; the new number links on its first sign-in
        if ($expert->wasChanged('contact_number') && $expert->user_id) $expert->forceFill(['user_id' => null])->saveQuietly();

        return redirect()->route('experts.index')->with('success', 'Expert updated successfully.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Expert $expert)
    {
        // deleting would also delete their payouts (and orphan their bookings): keep the money history
        $bookings = $expert->bookings()->withTrashed()->count();
        $payouts = $expert->payouts()->count();
        if ($bookings || $payouts) {
            return redirect()->route('experts.index')->with('error', "{$expert->name} has {$bookings} booking(s) and {$payouts} payout(s), so they cannot be deleted. Set their status to Inactive instead.");
        }

        if ($expert->img && File::exists(public_path($expert->img))) {
            File::delete(public_path($expert->img));
        }

        $expert->delete();

        return redirect()->route('experts.index')->with('success', 'Expert deleted successfully.');
    }
    public function updateCommission(Request $request, Expert $expert)
    {
        $request->validate([
            'commission_type' => 'required|in:percentage,flat_fee',
            'commission_value' => 'required|numeric|min:0',
            'reason' => 'nullable|string'
        ]);

        // Deactivate old active commissions
        \App\Models\ExpertCommission::where('expert_id', $expert->id)
            ->where('is_active', true)
            ->update(['is_active' => false]);

        // Create new commission logic
        \App\Models\ExpertCommission::create([
            'expert_id' => $expert->id,
            'commission_type' => $request->commission_type,
            'commission_value' => $request->commission_value,
            'reason' => $request->reason,
            'is_active' => true
        ]);

        // Log this change
        \App\Models\CommissionLog::create([
            'entity_type' => 'ExpertCommission',
            'entity_id' => $expert->id, // Logging against Expert ID for easier lookup, or the new commission ID? 
            // Better to log the creation. But for now identifying by Expert ID is fine for "Expert Commission Updated"
            'old_value' => null, 
            'new_value' => json_encode($request->only('commission_type', 'commission_value', 'reason')),
            'action_by' => auth()->id(),
            'reason' => 'Admin updated expert commission'
        ]);

        return back()->with('success', 'Expert specific commission updated.');
    }

    /**
     * The "Profile page" tab and the Identity extras: repeater rows without their main value are dropped, tags and lines are trimmed.
     */
    private function profileFields(Request $request): array
    {
        $rows = fn (string $k, array $cols, string $must) => array_values(array_filter(array_map(
            fn ($r) => array_map(fn ($c) => trim((string) ($r[$c] ?? '')), array_combine($cols, $cols)),
            array_filter((array) $request->input($k, []), 'is_array')), fn ($r) => $r[$must] !== ''));
        $tags = fn (string $k) => array_values(array_unique(array_filter(array_map(fn ($t) => trim((string) $t), (array) $request->input($k, [])))));
        $top = $request->input('top_rank_students');
        if (is_string($top)) $request->merge(['top_rank_students' => json_decode($top, true) ?: []]); // the old JSON box

        $data = [
            'previous_institutes' => $rows('previous_institutes', ['role', 'name', 'years'], 'name'),
            'top_rank_students' => $rows('top_rank_students', ['name', 'rank', 'exam', 'year'], 'name'),
            // (student_testimonials are no longer typed in: students rate the expert on the website, so they are left as they are)
            'counseling_specialization' => $tags('counseling_specialization'),
            'awards_recognition' => $tags('awards_recognition'),
            // Identity: one spelling of gender ("male" from the website form shows as Male), the list's languages for both
            // language fields, a real date of birth, and an http(s) website only
            'gender' => in_array($g = ucfirst(strtolower(trim((string) $request->input('gender')))), ['Male', 'Female', 'Other'], true) ? $g : null,
            'language_of_teaching' => $tags('language_of_teaching'),
            'languages_supported' => $tags('language_of_teaching'),
            'subject_specialization' => $tags('subject_specialization'),
            'date_of_birth' => strtotime((string) $request->input('date_of_birth')) ? date('Y-m-d', strtotime($request->input('date_of_birth'))) : null,
            'designation' => mb_substr(trim((string) $request->input('designation')), 0, 150) ?: null,
            'current_institute_name' => mb_substr(trim((string) $request->input('current_institute_name')), 0, 200) ?: null,
            'website_url' => preg_match('~^https?://~i', $w = trim((string) $request->input('website_url'))) ? mb_substr($w, 0, 300) : null,
        ];
        foreach (array_keys(Expert::TRACK_RECORD + Expert::BEYOND + Expert::NETWORK) as $k) {
            $t = trim(preg_replace('/\R{2,}/', "\n", (string) $request->input($k, '')));
            $data[$k] = $t === '' ? null : (isset(Expert::BEYOND[$k]) ? $t : mb_substr($t, 0, 255)); // the others are 255-character columns
        }
        $data['average_wait_time'] = mb_substr(trim((string) $request->input('average_wait_time', '')), 0, 60) ?: null;
        return $data;
    }
}

<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\MentorProfile;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class MentorController extends Controller
{
    public function index()
    {
        $mentors = MentorProfile::with('user', 'verification')->paginate(15);
        return view('admin.mentor.profiles.index', compact('mentors'));
    }

    public function create()
    {
        return view('admin.mentor.profiles.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'first_name' => 'required|string|max:255',
            'last_name' => 'required|string|max:255',
            'professional_headline' => 'nullable|string|max:255',
            'price_per_min' => 'nullable|numeric|min:0',
            'short_bio' => 'nullable|string',
            'city' => 'nullable|string|max:255',
            'state_country' => 'nullable|string|max:255',
            'profile_photo' => 'nullable|image|max:4096'
        ]);

        $email = strtolower($request->first_name . '.' . $request->last_name . rand(100, 999) . '@enrollzy.com');
        $user = User::create([
            'name' => $request->first_name . ' ' . $request->last_name,
            'email' => $email,
            'password' => Hash::make('password123')
        ]);

        $photoPath = null;
        if ($request->hasFile('profile_photo')) {
            $photoPath = $request->file('profile_photo')->store('mentor_profiles', 'public');
        }

        MentorProfile::create([
            'user_id' => $user->id,
            'first_name' => $request->first_name,
            'last_name' => $request->last_name,
            'professional_headline' => $request->professional_headline,
            'price_per_min' => $request->price_per_min ?? 500,
            'short_bio' => $request->short_bio,
            'city' => $request->city,
            'state_country' => $request->state_country,
            'profile_photo' => $photoPath
        ]);

        return redirect()->route('admin.mentor.profiles.index')->with('success', 'Mentor created successfully.');
    }

    public function show($id)
    {
        $profile = MentorProfile::with('user', 'verification')->findOrFail($id);
        
        $educations = DB::table('mentor_educations')->where('mentor_profile_id', $id)->get();
        $experiences = DB::table('mentor_experiences')->where('mentor_profile_id', $id)->get();
        $mentorship = DB::table('mentor_mentorship_details')->where('mentor_profile_id', $id)->first();
        $availability = DB::table('mentor_availability_details')->where('mentor_profile_id', $id)->first();
        $pricing = DB::table('mentor_pricing_details')->where('mentor_profile_id', $id)->first();
        
        return view('admin.mentor.profiles.show', compact('profile', 'educations', 'experiences', 'mentorship', 'availability', 'pricing'));
    }

    public function edit($id)
    {
        $profile = MentorProfile::with('user')->findOrFail($id);
        return view('admin.mentor.profiles.edit', compact('profile'));
    }

    public function update(Request $request, $id)
    {
        $profile = MentorProfile::findOrFail($id);
        $request->validate([
            'first_name' => 'required|string|max:255',
            'last_name' => 'required|string|max:255',
            'professional_headline' => 'nullable|string|max:255',
            'price_per_min' => 'nullable|numeric|min:0',
            'short_bio' => 'nullable|string',
            'city' => 'nullable|string|max:255',
            'state_country' => 'nullable|string|max:255',
            'profile_photo' => 'nullable|image|max:4096'
        ]);

        $photoPath = $profile->profile_photo;
        if ($request->hasFile('profile_photo')) {
            $photoPath = $request->file('profile_photo')->store('mentor_profiles', 'public');
        }

        $profile->update([
            'first_name' => $request->first_name,
            'last_name' => $request->last_name,
            'professional_headline' => $request->professional_headline,
            'price_per_min' => $request->price_per_min ?? 500,
            'short_bio' => $request->short_bio,
            'city' => $request->city,
            'state_country' => $request->state_country,
            'profile_photo' => $photoPath
        ]);

        if ($profile->user) {
            $profile->user->update([
                'name' => $request->first_name . ' ' . $request->last_name
            ]);
        }

        return redirect()->route('admin.mentor.profiles.index')->with('success', 'Mentor profile updated successfully.');
    }

    public function destroy($id)
    {
        $profile = MentorProfile::findOrFail($id);
        $profile->delete();
        return redirect()->route('admin.mentor.profiles.index')->with('success', 'Mentor deleted successfully.');
    }
}

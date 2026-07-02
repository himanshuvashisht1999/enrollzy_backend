<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\MentorProfile;
use Illuminate\Support\Facades\DB;

class MentorController extends Controller
{
    public function index()
    {
        $mentors = MentorProfile::with('user', 'verification')->paginate(15);
        return view('admin.mentor.profiles.index', compact('mentors'));
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
}

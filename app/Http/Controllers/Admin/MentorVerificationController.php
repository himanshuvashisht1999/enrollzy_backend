<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\MentorVerification;

class MentorVerificationController extends Controller
{
    public function index()
    {
        // Get verifications that have at least one pending item
        $verifications = MentorVerification::with('profile.user')
            ->where(function ($query) {
                $query->where('gov_id_status', 'pending')
                      ->orWhere('linkedin_status', 'pending')
                      ->orWhere('background_check_status', 'pending')
                      ->orWhere('degree_status', 'pending');
            })->paginate(10);

        return view('admin.mentor.verifications.index', compact('verifications'));
    }

    public function updateStatus(Request $request, $id, $type)
    {
        $verification = MentorVerification::findOrFail($id);
        $status = $request->input('status'); // 'verified' or 'rejected'

        $validTypes = ['gov_id', 'linkedin', 'background_check', 'degree'];
        if (!in_array($type, $validTypes)) {
            abort(400, 'Invalid verification type');
        }

        $column = $type . '_status';
        $commentColumn = $type . '_comment';
        
        $verification->update([
            $column => $status,
            $commentColumn => $request->input('comment')
        ]);

        return redirect()->back()->with('success', 'Verification status updated successfully.');
    }
}

<?php

namespace App\Http\Controllers\Backend\General;

use App\Models\Admin;
use App\Models\Users;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\File;
use App\Models\AdminImage;

class ProfileController extends Controller
{
    public function showProfile()
    {
        $profile = auth()->user();
        $attendance_images= AdminImage::where('admin_id',$profile->id)->get();
        return view('general.profile.detail', compact('profile','attendance_images'));
    }

    public function updateProfile(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'username' => 'required',
            'name' => 'required',
            'email' => 'required|email',
            'phone' => 'required',
            'dob' => 'required|date',
            'gender' => 'required',
        ]);

        if ($validator->fails()) {
            return response()->json(['status' => 0, 'message' => $validator->errors()->first()]);
        } else {
            $dataProfile = Admin::find(Auth::guard('admin')->id());
            if (!$dataProfile) {
                return response()->json(['status' => 0, 'message' => 'staff not found.']);
            }
            $dataProfile->username = $request->username;
            $dataProfile->name = $request->name;
            $dataProfile->email = $request->email;
            $dataProfile->phone = $request->phone;
            $dataProfile->dob = $request->dob;
            $dataProfile->gender = $request->gender;
            if ($dataProfile->save()) {
                staffLog('admin', $dataProfile->id, 'update', ' staff profile updated');
                return response()->json(['status' => 1, 'message' => 'Profile updated successfully.']);
            } else {
                return response()->json(['status' => 0, 'message' => 'Something went wrong.']);
            }
        }
    }

    public function updatePassword(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'currentPassword' => 'required',
            'password' => 'required|min:8|confirmed',
        ]);
        if ($validator->fails()) {
            return response()->json(['status' => 0, 'message' => $validator->errors()->first()]);
        }
        $staff = Admin::find(Auth::guard('admin')->id());
        if (!Hash::check($request->currentPassword, $staff->password)) {
            return response()->json(['status' => 0, 'message' => 'Current password is incorrect']);
        }
        $staff->password = Hash::make($request->password);
        if ($staff->update()) {
            staffLog('admin', $staff->id, 'update', ' staff password updated');
            return response()->json(['status' => 1, 'message' => 'Password updated successfully']);
        } else {
            return response()->json(['status' => 0, 'message' => 'Something went wrong ']);
        }
    }

    public function uploadAttendanceImage(Request $request)
    {
        // Validate
        $request->validate([
            'image' => 'required|image|mimes:jpeg,jpg,png|max:5120', // 5MB
        ]);

        $imgName = '';
        if ($request->file('image')) {
            $image = $request->file('image');
            $extImage = $image->getClientOriginalExtension();
            $imgName = "user-image-" . rand(1000,9999) . "_" . time() . "." . $extImage;

            $destinationPath = public_path('assets/user_attendance');

            // create folder if not exists
            if (!File::exists($destinationPath)) {
                File::makeDirectory($destinationPath, 0755, true);
            }

            $image->move($destinationPath, $imgName);
        }

        $save_data = new AdminImage();
        $save_data->admin_id = auth()->user()->id;
        $save_data->image = $imgName;
        $save_data->save();

        // If AJAX request, return JSON
        if ($request->ajax()) {
            return response()->json(['status' => 1, 'message' => 'Image added successfully', 'image_id' => $save_data->id]);
        }

        return redirect()->back()->with('success', 'Image added successfully');
    }

    public function deleteAttendanceImage(Request $request)
    {
        $request->validate([
            'id' => 'required|integer',
        ]);

        $record = AdminImage::where('admin_id', auth()->user()->id)
                    ->where('id', $request->id)
                    ->first();

        if (!$record) {
            if ($request->ajax()) {
                return response()->json(['status' => 0, 'message' => 'Image not found']);
            }
            return redirect()->back()->with('error', 'Image not found');
        }

        $filePath = public_path('assets/user_attendance/' . $record->image);
        if (File::exists($filePath)) {
            try {
                File::delete($filePath);
            } catch (\Exception $e) {
                // log if you want but continue to delete DB record
            }
        }

        $record->delete();

        if ($request->ajax()) {
            return response()->json(['status' => 1, 'message' => 'Image deleted successfully']);
        }

        return redirect()->back()->with('success', 'Image deleted successfully');
    }
}

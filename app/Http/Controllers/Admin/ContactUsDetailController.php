<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ContactUsDetail;
use Illuminate\Http\Request;

class ContactUsDetailController extends Controller
{
    public function edit()
    {
        $contactUs = ContactUsDetail::firstOrCreate(['id' => 1]);
        return view('admin.contact-us.edit', compact('contactUs'));
    }

    public function update(Request $request)
    {
        $contactUs = ContactUsDetail::firstOrCreate(['id' => 1]);
        
        $data = $request->except(['_token', '_method', 'co_founder_image', 'career_coach_image', 'hero_image']);
        
        // Handle Simple Array Points
        $arrayFields = ['career_coach_points', 'hero_trust_points', 'form_trust_points'];
        foreach ($arrayFields as $field) {
            if ($request->has($field)) {
                $points = $request->$field;
                if (is_string($points)) {
                    $data[$field] = array_map('trim', explode(',', $points));
                } else {
                    $data[$field] = $points;
                }
            }
        }

        // Handle complex JSON arrays if sent as string or array
        if ($request->has('why_contact_cards')) {
            $cards = $request->why_contact_cards;
            if (is_string($cards)) {
                $data['why_contact_cards'] = json_decode($cards, true);
            } else {
                $data['why_contact_cards'] = $cards; // Array of cards
            }
        }

        // Handle Image Uploads
        $imageFields = ['co_founder_image', 'career_coach_image', 'hero_image'];
        foreach ($imageFields as $imgField) {
            if ($request->hasFile($imgField)) {
                $file = $request->file($imgField);
                $filename = time() . '_' . $imgField . '.' . $file->getClientOriginalExtension();
                $file->move(public_path('uploads/contact'), $filename);
                $data[$imgField] = 'uploads/contact/' . $filename;
            }
        }

        $contactUs->update($data);

        return redirect()->back()->with('success', 'Contact Us page updated successfully.');
    }
}

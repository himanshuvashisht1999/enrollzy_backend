<?php

namespace App\Http\Controllers;

use App\Models\DigitalCard;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;

class PublicDigitalCardController extends Controller
{
    /**
     * Display the digital card public page.
     */
    public function show($slug)
    {
        $card = DigitalCard::where('slug', $slug)->first();

        if (!$card) {
            abort(404, 'Digital Card not found');
        }

        // If card is inactive and user is not admin, show 404/disabled
        $isAdmin = Auth::guard('admin')->check() || Auth::check();
        if (!$card->is_active && !$isAdmin) {
            return response()->view('digital_cards.inactive', compact('card'), 404);
        }

        // Increment views count (avoid counting on every reload by the same session if desired, or simple increment)
        $sessionKey = 'viewed_card_' . $card->id;
        if (!session()->has($sessionKey)) {
            $card->increment('views_count');
            session()->put($sessionKey, true);
        }

        return view('digital_cards.show', compact('card', 'isAdmin'));
    }

    /**
     * Serve vCard (.vcf) file — opens native "Add Contact" dialog on mobile.
     *
     * iOS & Android: Content-Disposition must be "inline" (not "attachment")
     * so the browser opens the .vcf file directly in the Contacts app
     * instead of saving it as a file download.
     */
    public function vcard($slug)
    {
        $card = DigitalCard::where('slug', $slug)->firstOrFail();

        // Increment downloads/saves counter
        $card->increment('downloads_count');

        $vcardContent = $card->generateVCard();
        $safeName = Str::slug($card->name ?: 'contact', '_');
        $fileName = "{$safeName}.vcf";

        return response($vcardContent, 200, [
            // text/x-vcard is the most widely supported MIME type for contacts
            // iOS Safari & Android Chrome both recognise this and open the Contacts app
            'Content-Type'        => 'text/x-vcard; charset=utf-8',
            // "inline" tells the browser to open the file, NOT download it
            // This triggers the native "Add Contact / Save Contact" dialog on mobile
            'Content-Disposition' => 'inline; filename="' . $fileName . '"',
            'Content-Length'      => strlen($vcardContent),
            'Cache-Control'       => 'no-cache, must-revalidate',
            'Pragma'              => 'no-cache',
        ]);
    }

    /**
     * API endpoint to get card details in JSON (for frontend apps / React / Vue / Mobile apps).
     */
    public function apiShow($slug)
    {
        $card = DigitalCard::where('slug', $slug)->where('is_active', true)->first();

        if (!$card) {
            return response()->json([
                'status' => false,
                'message' => 'Digital card not found or inactive.',
            ], 404);
        }

        return response()->json([
            'status' => true,
            'data' => [
                'id' => $card->id,
                'name' => $card->name,
                'designation' => $card->designation,
                'company_name' => $card->company_name,
                'slug' => $card->slug,
                'public_url' => $card->public_url,
                'vcard_url' => $card->vcard_url,
                'qr_code_url' => $card->qr_code_url,
                'profile_image' => $card->profile_image_url,
                'banner_image' => $card->banner_image_url,
                'slogan_tags' => $card->slogan_tags ?? [],
                'bio' => $card->bio,
                'companies' => $card->companies ?? [],
                'contact' => [
                    'phone' => $card->phone,
                    'whatsapp_number' => $card->whatsapp_number,
                    'whatsapp_message' => $card->whatsapp_message,
                    'email' => $card->email,
                    'address' => $card->address,
                    'maps_url' => $card->maps_url,
                    'website_url' => $card->website_url,
                ],
                'social' => [
                    'linkedin' => $card->linkedin_url,
                    'instagram' => $card->instagram_url,
                    'facebook' => $card->facebook_url,
                    'youtube' => $card->youtube_url,
                    'twitter' => $card->twitter_url,
                    'telegram' => $card->telegram_url,
                    'github' => $card->github_url,
                ],
                'custom_links' => $card->custom_links ?? [],
                'theme' => [
                    'theme_color' => $card->theme_color,
                    'accent_color' => $card->accent_color,
                    'theme_style' => $card->theme_style,
                ],
                'meta' => [
                    'title' => $card->meta_title ?: "{$card->name} | Digital Card",
                    'description' => $card->meta_description ?: $card->bio,
                ],
            ]
        ]);
    }
}

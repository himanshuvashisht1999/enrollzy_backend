<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\DigitalCard;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;

class DigitalCardController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $query = DigitalCard::query()->with('admin');

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('designation', 'like', "%{$search}%")
                  ->orWhere('company_name', 'like', "%{$search}%")
                  ->orWhere('phone', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%")
                  ->orWhere('slug', 'like', "%{$search}%");
            });
        }

        if ($request->filled('status')) {
            $query->where('is_active', $request->status == '1');
        }

        $cards = $query->orderBy('id', 'desc')->paginate(15)->withQueryString();

        $stats = [
            'total_cards' => DigitalCard::count(),
            'active_cards' => DigitalCard::where('is_active', true)->count(),
            'total_views' => DigitalCard::sum('views_count'),
            'total_downloads' => DigitalCard::sum('downloads_count'),
        ];

        return view('admin.digital_cards.index', compact('cards', 'stats'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $card = new DigitalCard();
        // default slogans
        $card->slogan_tags = ['PEOPLE', 'TECHNOLOGY', 'GROWTH'];
        $card->theme_color = '#0f172a';
        $card->accent_color = '#c59b27';
        $card->theme_style = 'curved_gold';
        $card->is_active = true;

        return view('admin.digital_cards.create', compact('card'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'designation' => 'nullable|string|max:255',
            'company_name' => 'nullable|string|max:255',
            'slug' => 'nullable|string|max:255|unique:digital_cards,slug',
            'title' => 'nullable|string|max:255',
            'bio' => 'nullable|string',
            'phone' => 'nullable|string|max:50',
            'whatsapp_number' => 'nullable|string|max:50',
            'whatsapp_message' => 'nullable|string|max:500',
            'email' => 'nullable|email|max:255',
            'address' => 'nullable|string',
            'maps_url' => 'nullable|string',
            'website_url' => 'nullable|string|max:255',
            'linkedin_url' => 'nullable|string|max:255',
            'instagram_url' => 'nullable|string|max:255',
            'facebook_url' => 'nullable|string|max:255',
            'youtube_url' => 'nullable|string|max:255',
            'twitter_url' => 'nullable|string|max:255',
            'telegram_url' => 'nullable|string|max:255',
            'github_url' => 'nullable|string|max:255',
            'theme_color' => 'nullable|string|max:50',
            'accent_color' => 'nullable|string|max:50',
            'theme_style' => 'nullable|string|max:50',
            'qr_scan_action' => 'nullable|string|in:card_url,vcard',
            'meta_title' => 'nullable|string|max:255',
            'meta_description' => 'nullable|string',
            'profile_image' => 'nullable|image|mimes:jpeg,png,jpg,webp,svg|max:4096',
            'banner_image' => 'nullable|image|mimes:jpeg,png,jpg,webp,svg|max:6144',
            'qr_code_custom_image' => 'nullable|image|mimes:jpeg,png,jpg,webp,svg|max:4096',
        ]);

        $data = $request->except(['_token', 'profile_image', 'banner_image', 'qr_code_custom_image', 'companies', 'slogan_tags', 'custom_links']);

        // Slogan Tags array processing
        if ($request->filled('slogan_tags')) {
            if (is_array($request->slogan_tags)) {
                $data['slogan_tags'] = array_values(array_filter(array_map('trim', $request->slogan_tags)));
            } else {
                $data['slogan_tags'] = array_values(array_filter(array_map('trim', explode(',', $request->slogan_tags))));
            }
        } else {
            $data['slogan_tags'] = ['PEOPLE', 'TECHNOLOGY', 'GROWTH'];
        }

        // Companies Repeater processing
        $companies = [];
        if ($request->has('companies') && is_array($request->companies)) {
            foreach ($request->companies as $index => $item) {
                if (!empty($item['name'])) {
                    $co = [
                        'name' => trim($item['name']),
                        'subtitle' => trim($item['subtitle'] ?? ''),
                        'link' => trim($item['link'] ?? ''),
                        'icon' => trim($item['icon'] ?? 'fas fa-building'),
                        'icon_color' => trim($item['icon_color'] ?? '#c59b27'),
                    ];

                    // Handle company logo upload if present
                    if ($request->hasFile("companies.{$index}.logo")) {
                        $coLogoFile = $request->file("companies.{$index}.logo");
                        $logoName = 'company_' . time() . '_' . Str::random(6) . '.' . $coLogoFile->getClientOriginalExtension();
                        $coLogoFile->move(public_path('uploads/digital_cards/companies'), $logoName);
                        $co['logo'] = 'uploads/digital_cards/companies/' . $logoName;
                    } elseif (!empty($item['existing_logo'])) {
                        $co['logo'] = $item['existing_logo'];
                    }

                    $companies[] = $co;
                }
            }
        }
        $data['companies'] = $companies;

        // Custom links repeater
        $customLinks = [];
        if ($request->has('custom_links') && is_array($request->custom_links)) {
            foreach ($request->custom_links as $link) {
                if (!empty($link['title']) && !empty($link['url'])) {
                    $customLinks[] = [
                        'title' => trim($link['title']),
                        'url' => trim($link['url']),
                        'icon' => trim($link['icon'] ?? 'fas fa-link'),
                    ];
                }
            }
        }
        $data['custom_links'] = $customLinks;

        // Slug generation if empty
        if (empty($data['slug'])) {
            $baseSlug = Str::slug($data['name']);
            $slug = $baseSlug;
            $counter = 1;
            while (DigitalCard::where('slug', $slug)->exists()) {
                $slug = $baseSlug . '-' . $counter;
                $counter++;
            }
            $data['slug'] = $slug;
        } else {
            $data['slug'] = Str::slug($data['slug']);
        }

        // Upload Profile Image
        if ($request->hasFile('profile_image')) {
            $file = $request->file('profile_image');
            $fileName = 'profile_' . time() . '_' . Str::random(6) . '.' . $file->getClientOriginalExtension();
            $destination = public_path('uploads/digital_cards/profiles');
            if (!File::exists($destination)) {
                File::makeDirectory($destination, 0755, true);
            }
            $file->move($destination, $fileName);
            $data['profile_image'] = 'uploads/digital_cards/profiles/' . $fileName;
        }

        // Upload Banner Image
        if ($request->hasFile('banner_image')) {
            $file = $request->file('banner_image');
            $fileName = 'banner_' . time() . '_' . Str::random(6) . '.' . $file->getClientOriginalExtension();
            $destination = public_path('uploads/digital_cards/banners');
            if (!File::exists($destination)) {
                File::makeDirectory($destination, 0755, true);
            }
            $file->move($destination, $fileName);
            $data['banner_image'] = 'uploads/digital_cards/banners/' . $fileName;
        }

        // Upload Custom QR code if provided
        if ($request->hasFile('qr_code_custom_image')) {
            $file = $request->file('qr_code_custom_image');
            $fileName = 'qr_' . time() . '_' . Str::random(6) . '.' . $file->getClientOriginalExtension();
            $destination = public_path('uploads/digital_cards/qr');
            if (!File::exists($destination)) {
                File::makeDirectory($destination, 0755, true);
            }
            $file->move($destination, $fileName);
            $data['qr_code_custom_image'] = 'uploads/digital_cards/qr/' . $fileName;
        }

        $data['is_active'] = $request->has('is_active');
        $data['admin_id'] = Auth::guard('admin')->id() ?: Auth::id();

        $card = DigitalCard::create($data);

        return redirect()->route('admin.digital-cards.index')
            ->with('success', 'Digital Card created successfully! You can preview and share the QR code.');
    }

    /**
     * Display the specified resource.
     */
    public function show(DigitalCard $digitalCard)
    {
        return view('admin.digital_cards.show', ['card' => $digitalCard]);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(DigitalCard $digitalCard)
    {
        return view('admin.digital_cards.edit', ['card' => $digitalCard]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, DigitalCard $digitalCard)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'designation' => 'nullable|string|max:255',
            'company_name' => 'nullable|string|max:255',
            'slug' => 'required|string|max:255|unique:digital_cards,slug,' . $digitalCard->id,
            'title' => 'nullable|string|max:255',
            'bio' => 'nullable|string',
            'phone' => 'nullable|string|max:50',
            'whatsapp_number' => 'nullable|string|max:50',
            'whatsapp_message' => 'nullable|string|max:500',
            'email' => 'nullable|email|max:255',
            'address' => 'nullable|string',
            'maps_url' => 'nullable|string',
            'website_url' => 'nullable|string|max:255',
            'linkedin_url' => 'nullable|string|max:255',
            'instagram_url' => 'nullable|string|max:255',
            'facebook_url' => 'nullable|string|max:255',
            'youtube_url' => 'nullable|string|max:255',
            'twitter_url' => 'nullable|string|max:255',
            'telegram_url' => 'nullable|string|max:255',
            'github_url' => 'nullable|string|max:255',
            'theme_color' => 'nullable|string|max:50',
            'accent_color' => 'nullable|string|max:50',
            'theme_style' => 'nullable|string|max:50',
            'qr_scan_action' => 'nullable|string|in:card_url,vcard',
            'meta_title' => 'nullable|string|max:255',
            'meta_description' => 'nullable|string',
            'profile_image' => 'nullable|image|mimes:jpeg,png,jpg,webp,svg|max:4096',
            'banner_image' => 'nullable|image|mimes:jpeg,png,jpg,webp,svg|max:6144',
            'qr_code_custom_image' => 'nullable|image|mimes:jpeg,png,jpg,webp,svg|max:4096',
        ]);

        $data = $request->except(['_token', '_method', 'profile_image', 'banner_image', 'qr_code_custom_image', 'companies', 'slogan_tags', 'custom_links']);

        // Slogan Tags
        if ($request->filled('slogan_tags')) {
            if (is_array($request->slogan_tags)) {
                $data['slogan_tags'] = array_values(array_filter(array_map('trim', $request->slogan_tags)));
            } else {
                $data['slogan_tags'] = array_values(array_filter(array_map('trim', explode(',', $request->slogan_tags))));
            }
        } else {
            $data['slogan_tags'] = ['PEOPLE', 'TECHNOLOGY', 'GROWTH'];
        }

        // Companies repeater
        $companies = [];
        if ($request->has('companies') && is_array($request->companies)) {
            foreach ($request->companies as $index => $item) {
                if (!empty($item['name'])) {
                    $co = [
                        'name' => trim($item['name']),
                        'subtitle' => trim($item['subtitle'] ?? ''),
                        'link' => trim($item['link'] ?? ''),
                        'icon' => trim($item['icon'] ?? 'fas fa-building'),
                        'icon_color' => trim($item['icon_color'] ?? '#c59b27'),
                    ];

                    if ($request->hasFile("companies.{$index}.logo")) {
                        $coLogoFile = $request->file("companies.{$index}.logo");
                        $logoName = 'company_' . time() . '_' . Str::random(6) . '.' . $coLogoFile->getClientOriginalExtension();
                        $destination = public_path('uploads/digital_cards/companies');
                        if (!File::exists($destination)) {
                            File::makeDirectory($destination, 0755, true);
                        }
                        $coLogoFile->move($destination, $logoName);
                        $co['logo'] = 'uploads/digital_cards/companies/' . $logoName;
                    } elseif (!empty($item['existing_logo'])) {
                        $co['logo'] = $item['existing_logo'];
                    }

                    $companies[] = $co;
                }
            }
        }
        $data['companies'] = $companies;

        // Custom links repeater
        $customLinks = [];
        if ($request->has('custom_links') && is_array($request->custom_links)) {
            foreach ($request->custom_links as $link) {
                if (!empty($link['title']) && !empty($link['url'])) {
                    $customLinks[] = [
                        'title' => trim($link['title']),
                        'url' => trim($link['url']),
                        'icon' => trim($link['icon'] ?? 'fas fa-link'),
                    ];
                }
            }
        }
        $data['custom_links'] = $customLinks;

        // Slug
        $data['slug'] = Str::slug($data['slug']);

        // Profile Image Upload
        if ($request->hasFile('profile_image')) {
            if ($digitalCard->profile_image && File::exists(public_path($digitalCard->profile_image))) {
                File::delete(public_path($digitalCard->profile_image));
            }
            $file = $request->file('profile_image');
            $fileName = 'profile_' . time() . '_' . Str::random(6) . '.' . $file->getClientOriginalExtension();
            $destination = public_path('uploads/digital_cards/profiles');
            if (!File::exists($destination)) {
                File::makeDirectory($destination, 0755, true);
            }
            $file->move($destination, $fileName);
            $data['profile_image'] = 'uploads/digital_cards/profiles/' . $fileName;
        }

        // Banner Image Upload
        if ($request->hasFile('banner_image')) {
            if ($digitalCard->banner_image && File::exists(public_path($digitalCard->banner_image))) {
                File::delete(public_path($digitalCard->banner_image));
            }
            $file = $request->file('banner_image');
            $fileName = 'banner_' . time() . '_' . Str::random(6) . '.' . $file->getClientOriginalExtension();
            $destination = public_path('uploads/digital_cards/banners');
            if (!File::exists($destination)) {
                File::makeDirectory($destination, 0755, true);
            }
            $file->move($destination, $fileName);
            $data['banner_image'] = 'uploads/digital_cards/banners/' . $fileName;
        }

        // Custom QR Image Upload
        if ($request->hasFile('qr_code_custom_image')) {
            if ($digitalCard->qr_code_custom_image && File::exists(public_path($digitalCard->qr_code_custom_image))) {
                File::delete(public_path($digitalCard->qr_code_custom_image));
            }
            $file = $request->file('qr_code_custom_image');
            $fileName = 'qr_' . time() . '_' . Str::random(6) . '.' . $file->getClientOriginalExtension();
            $destination = public_path('uploads/digital_cards/qr');
            if (!File::exists($destination)) {
                File::makeDirectory($destination, 0755, true);
            }
            $file->move($destination, $fileName);
            $data['qr_code_custom_image'] = 'uploads/digital_cards/qr/' . $fileName;
        }

        $data['is_active'] = $request->has('is_active');

        $digitalCard->update($data);

        return redirect()->route('admin.digital-cards.index')
            ->with('success', 'Digital Card updated successfully!');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(DigitalCard $digitalCard)
    {
        if ($digitalCard->profile_image && File::exists(public_path($digitalCard->profile_image))) {
            File::delete(public_path($digitalCard->profile_image));
        }
        if ($digitalCard->banner_image && File::exists(public_path($digitalCard->banner_image))) {
            File::delete(public_path($digitalCard->banner_image));
        }
        if ($digitalCard->qr_code_custom_image && File::exists(public_path($digitalCard->qr_code_custom_image))) {
            File::delete(public_path($digitalCard->qr_code_custom_image));
        }

        $digitalCard->delete();

        return redirect()->route('admin.digital-cards.index')
            ->with('success', 'Digital Card deleted successfully.');
    }

    /**
     * Ajax toggle active status
     */
    public function toggleStatus(Request $request, $id)
    {
        $card = DigitalCard::findOrFail($id);
        $card->is_active = !$card->is_active;
        $card->save();

        return response()->json([
            'status' => true,
            'is_active' => $card->is_active,
            'message' => 'Status updated successfully.',
        ]);
    }

    /**
     * Duplicate card
     */
    public function duplicate($id)
    {
        $card = DigitalCard::findOrFail($id);
        $newCard = $card->replicate();
        $newCard->title = ($card->title ?: $card->name) . ' (Copy)';
        $newCard->slug = Str::slug($card->name . '-copy-' . time());
        $newCard->views_count = 0;
        $newCard->downloads_count = 0;
        $newCard->save();

        return redirect()->route('admin.digital-cards.edit', $newCard->id)
            ->with('success', 'Card duplicated successfully! You can now adjust details.');
    }
}

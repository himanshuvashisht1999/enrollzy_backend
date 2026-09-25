<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class DigitalCard extends Model
{
    use HasFactory;

    protected $table = 'digital_cards';

    protected $fillable = [
        'admin_id',
        'title',
        'name',
        'designation',
        'company_name',
        'slug',
        'profile_image',
        'banner_image',
        'slogan_tags',
        'bio',
        'companies',
        'phone',
        'whatsapp_number',
        'whatsapp_message',
        'email',
        'address',
        'maps_url',
        'website_url',
        'linkedin_url',
        'instagram_url',
        'facebook_url',
        'youtube_url',
        'twitter_url',
        'telegram_url',
        'github_url',
        'custom_links',
        'theme_color',
        'accent_color',
        'theme_style',
        'qr_code_custom_image',
        'qr_scan_action',
        'is_active',
        'views_count',
        'downloads_count',
        'meta_title',
        'meta_description',
    ];

    protected $casts = [
        'slogan_tags' => 'array',
        'companies' => 'array',
        'custom_links' => 'array',
        'is_active' => 'boolean',
        'views_count' => 'integer',
        'downloads_count' => 'integer',
    ];

    /**
     * Boot function to generate slug automatically if missing
     */
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($card) {
            if (empty($card->slug)) {
                $baseSlug = Str::slug($card->name ?: 'card');
                $slug = $baseSlug;
                $counter = 1;
                while (static::where('slug', $slug)->exists()) {
                    $slug = $baseSlug . '-' . $counter;
                    $counter++;
                }
                $card->slug = $slug;
            }
        });
    }

    /**
     * Relationship with Admin / Creator
     */
    public function admin()
    {
        return $this->belongsTo(Admin::class, 'admin_id');
    }

    /**
     * Get full public URL for this card
     */
    public function getPublicUrlAttribute()
    {
        $frontendUrl = env('FRONTEND_URL');
        if (!empty($frontendUrl)) {
            return rtrim($frontendUrl, '/') . '/card/' . $this->slug;
        }

        return url('/card/' . $this->slug);
    }

    /**
     * Get direct vCard URL (.vcf extension for native mobile contact detection)
     */
    public function getVcardUrlAttribute()
    {
        $frontendUrl = env('FRONTEND_URL');
        if (!empty($frontendUrl)) {
            return rtrim($frontendUrl, '/') . '/card/' . $this->slug . '.vcf';
        }

        return url('/card/' . $this->slug . '.vcf');
    }

    /**
     * Get QR Code target URL depending on qr_scan_action
     */
    public function getQrTargetUrlAttribute()
    {
        if ($this->qr_scan_action === 'vcard') {
            return $this->vcard_url;
        }

        return $this->public_url;
    }

    /**
     * Get QR code dynamic image URL
     */
    public function getQrCodeUrlAttribute()
    {
        if (!empty($this->qr_code_custom_image)) {
            return asset($this->qr_code_custom_image);
        }

        $target = urlencode($this->qr_target_url);
        // ecc=H → highest error correction (30% damage recovery — best for printed cards)
        // size=400x400 → high resolution for clear scanning
        // margin=2 → small quiet zone around QR
        return "https://api.qrserver.com/v1/create-qr-code/?size=400x400&data={$target}&format=png&ecc=H&margin=2";
    }

    /**
     * Get profile image URL with fallback
     */
    public function getProfileImageUrlAttribute()
    {
        if (!empty($this->profile_image) && file_exists(public_path($this->profile_image))) {
            return asset($this->profile_image);
        }

        return 'https://ui-avatars.com/api/?name=' . urlencode($this->name ?: 'Card') . '&background=0f172a&color=fff&size=200&bold=true';
    }

    /**
     * Get banner image URL with fallback
     */
    public function getBannerImageUrlAttribute()
    {
        if (!empty($this->banner_image) && file_exists(public_path($this->banner_image))) {
            return asset($this->banner_image);
        }

        return null;
    }

    /**
     * Generate standard vCard (v3.0) content
     */
    public function generateVCard(): string
    {
        $vcard = "BEGIN:VCARD\r\n";
        $vcard .= "VERSION:3.0\r\n";

        // Full Name
        $fullName = trim($this->name);
        $vcard .= "FN:" . $this->escapeVCard($fullName) . "\r\n";

        // Name parts
        $nameParts = explode(' ', $fullName, 2);
        $firstName = $nameParts[0] ?? '';
        $lastName = $nameParts[1] ?? '';
        $vcard .= "N:" . $this->escapeVCard($lastName) . ";" . $this->escapeVCard($firstName) . ";;;\r\n";

        // Organization / Companies
        $orgNames = [];
        if (!empty($this->company_name)) {
            $orgNames[] = $this->company_name;
        }
        if (is_array($this->companies)) {
            foreach ($this->companies as $co) {
                if (!empty($co['name']) && !in_array($co['name'], $orgNames)) {
                    $orgNames[] = $co['name'];
                }
            }
        }
        if (!empty($orgNames)) {
            $vcard .= "ORG:" . $this->escapeVCard(implode(';', $orgNames)) . "\r\n";
        }

        // Title / Designation
        if (!empty($this->designation)) {
            $vcard .= "TITLE:" . $this->escapeVCard($this->designation) . "\r\n";
        }

        // Phone Numbers
        if (!empty($this->phone)) {
            $vcard .= "TEL;TYPE=CELL,VOICE:" . $this->escapeVCard($this->phone) . "\r\n";
        }
        if (!empty($this->whatsapp_number) && $this->whatsapp_number !== $this->phone) {
            $vcard .= "TEL;TYPE=WORK,VOICE:" . $this->escapeVCard($this->whatsapp_number) . "\r\n";
        }

        // Email
        if (!empty($this->email)) {
            $vcard .= "EMAIL;TYPE=WORK,INTERNET:" . $this->escapeVCard($this->email) . "\r\n";
        }

        // Website
        if (!empty($this->website_url)) {
            $vcard .= "URL:" . $this->escapeVCard($this->website_url) . "\r\n";
        }

        // Address
        if (!empty($this->address)) {
            $vcard .= "ADR;TYPE=WORK:;;" . $this->escapeVCard($this->address) . ";;;;\r\n";
        }

        // Note / Bio
        if (!empty($this->bio)) {
            $vcard .= "NOTE:" . $this->escapeVCard($this->bio) . "\r\n";
        }

        // Social Profiles
        if (!empty($this->linkedin_url)) {
            $vcard .= "X-SOCIALPROFILE;type=linkedin:" . $this->escapeVCard($this->linkedin_url) . "\r\n";
        }
        if (!empty($this->instagram_url)) {
            $vcard .= "X-SOCIALPROFILE;type=instagram:" . $this->escapeVCard($this->instagram_url) . "\r\n";
        }
        if (!empty($this->facebook_url)) {
            $vcard .= "X-SOCIALPROFILE;type=facebook:" . $this->escapeVCard($this->facebook_url) . "\r\n";
        }
        if (!empty($this->youtube_url)) {
            $vcard .= "X-SOCIALPROFILE;type=youtube:" . $this->escapeVCard($this->youtube_url) . "\r\n";
        }
        if (!empty($this->twitter_url)) {
            $vcard .= "X-SOCIALPROFILE;type=twitter:" . $this->escapeVCard($this->twitter_url) . "\r\n";
        }

        // Public Card Link
        $vcard .= "URL;TYPE=DigitalCard:" . $this->escapeVCard($this->public_url) . "\r\n";

        // Photo if available as base64 or URL
        if (!empty($this->profile_image) && file_exists(public_path($this->profile_image))) {
            try {
                $imagePath = public_path($this->profile_image);
                $imageData = @file_get_contents($imagePath);
                if ($imageData) {
                    $ext = strtoupper(pathinfo($imagePath, PATHINFO_EXTENSION));
                    if ($ext === 'JPG') $ext = 'JPEG';
                    $base64 = base64_encode($imageData);
                    $vcard .= "PHOTO;ENCODING=b;TYPE={$ext}:{$base64}\r\n";
                }
            } catch (\Exception $e) {
                // Skip photo embedding if fails
            }
        }

        $vcard .= "REV:" . gmdate('Ymd\THis\Z') . "\r\n";
        $vcard .= "END:VCARD\r\n";

        return $vcard;
    }

    /**
     * Escape special characters for vCard values
     */
    protected function escapeVCard(string $value): string
    {
        $value = str_replace('\\', '\\\\', $value);
        $value = str_replace(',', '\,', $value);
        $value = str_replace(';', '\;', $value);
        $value = str_replace("\r\n", "\\n", $value);
        $value = str_replace("\n", "\\n", $value);
        return $value;
    }
}

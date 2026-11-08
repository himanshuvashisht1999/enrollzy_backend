<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Billing details of an organisation (campus_id '') or one of its campuses (campus ids are UUIDs): what invoices print under "Billed To".
 * An organisation with campuses is billed per campus; a campus row with same_as_organisation uses the organisation's
 * row. For the billing module only (Billing → Organisation Billing); the website never reads it.
 */
class BillingProfile extends Model
{
    protected $fillable = [
        'organisation_id', 'campus_id', 'same_as_organisation', 'legal_name', 'contact_person', 'email', 'phone',
        'gstin', 'tan_number', 'pan_number', 'cin_number', 'address', 'city', 'state', 'pincode', 'country', 'notes', 'updated_by',
    ];

    protected $casts = ['same_as_organisation' => 'boolean'];

    /** the fields that make up the details (all but the toggle and notes) */
    public const DETAILS = ['legal_name', 'contact_person', 'email', 'phone', 'gstin', 'tan_number', 'pan_number', 'cin_number', 'address', 'city', 'state', 'pincode', 'country'];

    public static function row(int $organisationId, string $campusId = ''): ?self
    {
        return static::where('organisation_id', $organisationId)->where('campus_id', $campusId)->first();
    }

    /** whether anything worth printing was filled in */
    public function isFilled(): bool
    {
        foreach (array_diff(self::DETAILS, ['country']) as $f) if (trim((string) $this->$f) !== '') return true; // the country alone (India by default) is not details
        return false;
    }

    /**
     * what an invoice to this organisation (and campus) prints under "Billed To":
     * the campus's own details, or the organisation's when the campus is "same as organisation" (or there is no
     * campus). A campus with nothing saved yet prints the campus record's address and email, as invoices did before.
     * source: 'campus', 'organisation' or 'none' (nothing saved yet).
     */
    public static function billedTo(?Organisation $org, ?Campus $campus = null): array
    {
        $p = null;
        $source = 'none';
        if ($org && $campus) {
            $own = static::row($org->id, $campus->id);
            if ($own && $own->same_as_organisation) {
                $p = static::row($org->id);
                $source = $p && $p->isFilled() ? 'organisation' : 'none';
            } elseif ($own && $own->isFilled()) {
                [$p, $source] = [$own, 'campus'];
            }
        } elseif ($org) {
            $p = static::row($org->id);
            $source = $p && $p->isFilled() ? 'organisation' : 'none';
        }
        if ($source === 'none') $p = null;
        $fromCampus = $campus && $source === 'none'; // only saved details once there are some (no mixing of the two)

        $v = fn ($f) => trim((string) ($p?->$f ?? ''));
        $address = $v('address') !== '' ? $v('address') : ($fromCampus ? trim((string) $campus->full_address) : '');
        $location = array_filter([
            $v('city') !== '' ? $v('city') : ($fromCampus ? $campus->city : null),
            $v('state') !== '' ? $v('state') : ($fromCampus ? $campus->state : null),
            $v('pincode') !== '' ? $v('pincode') : ($fromCampus ? $campus->pincode : null),
        ]);
        $country = $v('country');
        if ($country !== '' && strcasecmp($country, 'India') !== 0) $location[] = $country;

        return [
            'title' => trim(($org->name ?? 'N/A') . ($campus ? ' - ' . $campus->campus_name : '')),
            'legal_name' => strcasecmp($v('legal_name'), (string) ($org->name ?? '')) === 0 ? '' : $v('legal_name'), // not twice when it is the name
            'attn' => $v('contact_person'),
            'address' => $address,
            'location' => implode(', ', $location),
            // what invoices print: the address, then the city, state and pincode on the same line after a comma
            'address_line' => implode(', ', array_filter([trim(preg_replace('/\s*\R\s*/', ', ', $address), ', '), implode(', ', $location)])),
            'email' => $v('email') !== '' ? $v('email') : ($fromCampus ? trim((string) $campus->campus_email) : ''),
            'phone' => $v('phone'),
            'gstin' => strtoupper($v('gstin')),
            'tan' => strtoupper($v('tan_number')),
            'pan' => strtoupper($v('pan_number')),
            'cin' => strtoupper($v('cin_number')),
            'source' => $source,
        ];
    }
}

<?php

namespace App\Http\Controllers\Admin\Billing;

use App\Http\Controllers\Controller;
use App\Models\BillingProfile;
use App\Models\Campus;
use App\Models\Organisation;
use Illuminate\Http\Request;

/**
 * Billing → Organisation Billing: the billing details (address, GSTIN, TAN, PAN, CIN, contact) of the organisations we
 * invoice, and of each of their campuses (a campus can be "same as organisation"). Stored in billing_profiles
 * (BillingProfile); invoices print them under "Billed To". For billing only, never shown on the website.
 */
class BillingOrganisationController extends Controller
{
    public function index(Request $request)
    {
        $search = trim((string) $request->input('search'));
        $setup = $request->input('setup'); // '', 'done', 'missing'

        // every organisation with its active campuses and saved rows (a few hundred: worked out here, then paged)
        $orgs = Organisation::query()->orderBy('name')->get(['id', 'name']);
        $campuses = Campus::where('status', true)->get(['id', 'organisation_id'])->groupBy('organisation_id');
        $rows = BillingProfile::all()->groupBy('organisation_id');

        $list = $orgs->map(function ($org) use ($campuses, $rows) {
            $mine = $rows->get($org->id, collect())->keyBy('campus_id');
            $own = $mine->get('');
            $ownFilled = $own && $own->isFilled();
            $ids = $campuses->get($org->id, collect())->pluck('id');
            // a campus is ready with its own details, or "same as organisation" when the organisation's are filled in
            $ready = $ids->filter(fn ($id) => ($r = $mine->get($id)) && ($r->same_as_organisation ? $ownFilled : $r->isFilled()))->count();
            return (object) ['org' => $org, 'own' => $own, 'campuses' => $ids->count(), 'ready' => $ready,
                'done' => $ids->count() ? $ready === $ids->count() : $ownFilled];
        });

        if ($search !== '') {
            $s = mb_strtolower($search);
            $list = $list->filter(fn ($x) => str_contains(mb_strtolower($x->org->name), $s)
                || ($x->own && (str_contains(mb_strtolower((string) $x->own->legal_name), $s) || str_contains(mb_strtolower((string) $x->own->gstin), $s))));
        }
        $counts = ['all' => $list->count(), 'done' => $list->where('done', true)->count()];
        if ($setup === 'done') $list = $list->where('done', true);
        if ($setup === 'missing') $list = $list->where('done', false);

        $page = max(1, (int) $request->input('page', 1));
        $organisations = new \Illuminate\Pagination\LengthAwarePaginator($list->slice(($page - 1) * 25, 25)->values(), $list->count(), 25, $page,
            ['path' => $request->url(), 'query' => $request->query()]);

        return view('admin.billing.organisations.index', compact('organisations', 'search', 'setup', 'counts'));
    }

    /** the details of the organisation itself, or (?campus=ID) of one of its campuses */
    public function edit(Request $request, Organisation $organisation)
    {
        $campuses = $organisation->campuses()->where('status', true)->orderBy('campus_name')->get(['id', 'campus_name', 'full_address', 'city', 'state', 'pincode', 'campus_email']);
        $campus = $request->filled('campus') ? $campuses->firstWhere('id', (string) $request->input('campus')) : null;
        if ($request->filled('campus') && !$campus) abort(404);

        $profile = BillingProfile::row($organisation->id, $campus->id ?? '');
        if (!$profile) {
            // a first visit: start from what the CRM already knows about the organisation / campus
            $profile = new BillingProfile($campus
                ? ['address' => $campus->full_address, 'city' => $campus->city, 'state' => $campus->state, 'pincode' => $campus->pincode, 'email' => $campus->campus_email, 'country' => 'India']
                : ['legal_name' => $organisation->registered_entity_name, 'pan_number' => $organisation->pan_number, 'gstin' => $organisation->gst_number, 'country' => 'India']);
        }
        $orgProfile = BillingProfile::row($organisation->id);
        $saved = BillingProfile::where('organisation_id', $organisation->id)->get()->keyBy('campus_id');

        return view('admin.billing.organisations.edit', compact('organisation', 'campuses', 'campus', 'profile', 'orgProfile', 'saved'));
    }

    public function update(Request $request, Organisation $organisation)
    {
        $campusId = (string) $request->input('campus_id', ''); // campus ids are UUIDs; '' is the organisation itself
        if ($campusId && !$organisation->campuses()->where('id', $campusId)->exists()) abort(404);

        // "same as organisation": only the switch and the notes; the campus's own (hidden) details stay as they were
        if ($campusId && $request->boolean('same_as_organisation')) {
            $notes = $request->validate(['notes' => 'nullable|string|max:2000']);
            BillingProfile::updateOrCreate(['organisation_id' => $organisation->id, 'campus_id' => $campusId],
                $notes + ['same_as_organisation' => true, 'updated_by' => auth('admin')->id()]);
            return redirect()->route('admin.billing.organisations.edit', [$organisation->id, 'campus' => $campusId])
                ->with('success', 'Campus set to use the organisation\'s billing details.');
        }

        $validated = $request->validate([
            'legal_name' => 'nullable|string|max:255',
            'contact_person' => 'nullable|string|max:255',
            'email' => 'nullable|email|max:255',
            'phone' => 'nullable|string|max:50',
            'gstin' => ['nullable', 'string', 'regex:/^[0-9]{2}[A-Z0-9]{13}$/i'],
            'tan_number' => 'nullable|string|max:25',
            'pan_number' => ['nullable', 'string', 'regex:/^[A-Z]{5}[0-9]{4}[A-Z]$/i'],
            'cin_number' => 'nullable|string|max:50',
            'address' => 'nullable|string|max:1000',
            'city' => 'nullable|string|max:100',
            'state' => 'nullable|string|max:100',
            'pincode' => 'nullable|string|max:20',
            'country' => 'nullable|string|max:100',
            'notes' => 'nullable|string|max:2000',
        ], [
            'gstin.regex' => 'The GSTIN should be 15 characters, starting with the 2-digit state code (e.g. 07AAAAA0000A1Z5).',
            'pan_number.regex' => 'The PAN should be 10 characters, like ABCDE1234F.',
        ]);
        foreach (['gstin', 'tan_number', 'pan_number', 'cin_number'] as $f) {
            if (isset($validated[$f])) $validated[$f] = strtoupper(trim($validated[$f]));
        }
        $validated['same_as_organisation'] = false;
        $validated['updated_by'] = auth('admin')->id();

        BillingProfile::updateOrCreate(['organisation_id' => $organisation->id, 'campus_id' => $campusId], $validated);

        $what = $campusId ? 'Campus billing details' : 'Organisation billing details';
        return redirect()->route('admin.billing.organisations.edit', array_filter([$organisation->id, 'campus' => $campusId ?: null]))
            ->with('success', $what . ' saved.');
    }

    /** for the invoice form: what this invoice would print under "Billed To" */
    public function billedTo(Request $request, Organisation $organisation)
    {
        $campus = $request->filled('campus') ? $organisation->campuses()->where('id', (string) $request->input('campus'))->first() : null;
        $needsCampus = !$campus && $organisation->campuses()->where('status', true)->exists();
        return response()->json(BillingProfile::billedTo($organisation, $campus) + [
            'needs_campus' => $needsCampus,
            'edit_url' => route('admin.billing.organisations.edit', array_filter([$organisation->id, 'campus' => $campus->id ?? null])),
        ]);
    }
}

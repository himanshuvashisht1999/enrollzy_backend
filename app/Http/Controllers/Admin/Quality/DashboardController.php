<?php

namespace App\Http\Controllers\Admin\Quality;

use App\Http\Controllers\Controller;
use App\Models\Organisation;
use App\Models\Quality\EntityAccreditation;
use App\Models\Quality\EntityApproval;
use App\Models\Quality\EntityAward;
use App\Models\Quality\EntityPlacement;
use App\Models\Quality\EntityReview;
use App\Models\Quality\EntityRanking;
use App\Support\Quality\Levels;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function index(Request $request)
    {
        $stats = [
            'rankings' => EntityRanking::count(),
            'rankings_unverified' => EntityRanking::where('verification_status', 'unverified')->count(),
            'rankings_legacy' => EntityRanking::whereNotNull('legacy_source')->where('verification_status', 'unverified')->count(),
            'accreditations' => EntityAccreditation::count(),
            'accreditations_unverified' => EntityAccreditation::where('verification_status', 'unverified')->count(),
            'accreditations_legacy' => EntityAccreditation::whereNotNull('legacy_source')->where('verification_status', 'unverified')->count(),
            'expiring_90' => EntityAccreditation::expiringWithin(90)->count(),
            'expired' => EntityAccreditation::expired()->where('status', 'active')->count(),
            'no_year' => EntityRanking::whereNull('ranking_year')->count(),
            'placements' => EntityPlacement::count(),
            'placements_unverified' => EntityPlacement::where('verification_status', 'unverified')->count(),
            'reviews' => EntityReview::count(),
            'reviews_unverified' => EntityReview::where('verification_status', 'unverified')->count(),
            'awards' => EntityAward::where('kind', 'award')->count(),
            'awards_unverified' => EntityAward::where('kind', 'award')->where('verification_status', 'unverified')->count(),
            'awards_to_sort' => EntityAward::where('kind', '!=', 'award')->where('verification_status', 'unverified')->count(),
            'approvals' => EntityApproval::count(),
            'approvals_unverified' => EntityApproval::where('verification_status', 'unverified')->count(),
            'approvals_expiring_90' => EntityApproval::expiringWithin(90)->count(),
        ];

        $expiring = EntityAccreditation::expiringWithin(90)->with('body')->orderBy('valid_to')->limit(15)->get();
        $expired = EntityAccreditation::expired()->where('status', 'active')->with('body')->orderBy('valid_to')->limit(15)->get();

        $names = [];
        foreach ($expiring->concat($expired)->groupBy('entity_type') as $t => $group) {
            $model = Levels::MAP[$t];
            $names[$t] = $model::whereIn((new $model)->getKeyName(), $group->pluck('entity_id'))->get()->mapWithKeys(fn ($m) => [(string) $m->getKey() => Levels::name($m)])->all();
        }

        // Organisations that claim a NAAC / NIRF value in the old columns but have no record yet are caught by the migration; these have neither.
        $identity = [
            'orgs_total' => Organisation::count(),
            'orgs_no_aishe' => Organisation::whereNull('aishe_code')->orWhere('aishe_code', '')->count(),
            'orgs_no_legal' => Organisation::whereNull('legal_entity_type')->count(),
        ];

        return view('admin.quality.dashboard', compact('stats', 'expiring', 'expired', 'names', 'identity'));
    }
}

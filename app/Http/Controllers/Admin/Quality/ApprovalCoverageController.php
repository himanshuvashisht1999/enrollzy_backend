<?php

namespace App\Http\Controllers\Admin\Quality;

use App\Http\Controllers\Controller;
use App\Models\Quality\AccreditationBody;
use App\Services\Quality\ApprovalCoverage;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

/**
 * Which programmes fall under a regulator (by discipline) but have no current approval recorded?
 * A programme's discipline is its own, else its course master's. Mapping lives on each body (Masters > Accreditation & approval bodies).
 */
class ApprovalCoverageController extends Controller
{
    public function index(Request $request)
    {
        $bodies = AccreditationBody::where('kind', '!=', 'accreditation')->where('status', true)->orderBy('sort_order')->get()
            ->filter(fn ($b) => $b->allowsLevel('course') && $b->disciplines()->exists())->values();

        $rows = [];
        foreach ($bodies as $b) {
            $scope = ApprovalCoverage::scope($b);
            $total = (clone $scope)->count();
            $missing = ApprovalCoverage::missing(clone $scope, $b)->count();
            $rows[] = ['body' => $b, 'total' => $total, 'covered' => $total - $missing, 'missing' => $missing,
                'disciplines' => $b->disciplines()->orderBy('title')->pluck('title')->all()];
        }
        $unmapped = AccreditationBody::where('kind', '!=', 'accreditation')->where('status', true)->orderBy('sort_order')->get()
            ->filter(fn ($b) => $b->allowsLevel('course') && !$b->disciplines()->exists())->pluck('short_name')->all();

        $selected = $request->filled('body') ? $bodies->firstWhere('id', (int) $request->query('body')) : null;
        $list = null;
        if ($selected) {
            $q = ApprovalCoverage::missing(ApprovalCoverage::scope($selected), $selected)
                ->join('organisations as o', 'o.id', '=', 'oc.organisation_id')
                ->leftJoin('campuses as cp', 'cp.id', '=', 'oc.campus_id')
                ->when($request->filled('q'), fn ($w) => $w->where('o.name', 'like', '%' . trim($request->query('q')) . '%'))
                ->select('oc.id', 'o.name as organisation', 'cp.campus_name as campus', 'c.name as course', 'oc.academic_unit_name as unit');
            $list = $q->orderBy('o.name')->orderBy('oc.id')->paginate(50)->withQueryString();
        }
        return view('admin.quality.coverage', compact('rows', 'unmapped', 'selected', 'list'));
    }
}

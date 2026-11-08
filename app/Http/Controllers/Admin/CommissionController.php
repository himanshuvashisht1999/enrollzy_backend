<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\CommissionLog;
use App\Models\CommissionPolicy;
use App\Models\Expert;
use App\Models\ExpertCategory;
use App\Models\ExpertCommission;
use App\Services\CommissionService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

/**
 * Commission Rules (Expert Management): what Enrollzy keeps from an expert booking. The first rule that exists applies:
 * the booking's own override (Bookings) > the expert's own rate > the expert's category rate > the global default.
 * GST and TDS are platform-wide (the global settings) whichever rate applies. Every change is written to commission_logs.
 * The website works bookings out the same way (enrollzy_frontend App\Support\Commission).
 */
class CommissionController extends Controller
{
    public function __construct(private CommissionService $commission)
    {
    }

    public function index(Request $request)
    {
        $global = $this->global();
        $categories = ExpertCategory::where('is_active', true)->withCount('experts')->orderBy('name')->get();
        $categoryRates = CommissionPolicy::where('policy_type', 'category')->where('is_active', true)->get()->keyBy('expert_category_id');

        // experts with their own rate (the latest active one each), searchable by name
        $q = trim((string) $request->query('q'));
        $expertRates = ExpertCommission::query()->where('is_active', true)
            ->whereIn('id', ExpertCommission::where('is_active', true)->selectRaw('MAX(id)')->groupBy('expert_id'))
            ->whereHas('expert', fn ($e) => $e->when($q, fn ($w) => $w->where('name', 'like', "%$q%")))
            ->with('expert:id,name,slug,expert_category_id,profile_visibility', 'expert.expertCategory:id,name')
            ->latest('id')->get();

        // the rate each expert would pay without their own one
        $fallback = fn (?int $categoryId) => ($categoryId && isset($categoryRates[$categoryId])) ? ['category', $categoryRates[$categoryId]] : ['global', $global];

        return view('admin.commission.index', [
            'global' => $global, 'categories' => $categories, 'categoryRates' => $categoryRates,
            'expertRates' => $expertRates, 'fallback' => $fallback, 'q' => $q,
            'experts' => Expert::orderBy('name')->get(['id', 'name', 'expert_category_id']),
            'example' => fn ($type, $value) => $this->commission->example(1000, $type, (float) $value),
        ]);
    }

    public function updateGlobal(Request $request)
    {
        $data = $this->validateRate($request);
        $policy = $this->global();
        $old = $policy->toArray();
        $policy->update($data + ['gst_applicable' => $request->boolean('gst_applicable'), 'tds_applicable' => $request->boolean('tds_applicable')]);
        $this->log('GlobalPolicy', $policy->id, $old, $policy->fresh()->toArray(), 'Global default changed');

        return back()->with('success', 'Global default saved.');
    }

    public function updateCategory(Request $request)
    {
        $request->validate(['expert_category_id' => 'required|exists:expert_categories,id']);
        $data = $this->validateRate($request);
        $cat = ExpertCategory::findOrFail($request->expert_category_id);
        $policy = CommissionPolicy::firstOrNew(['policy_type' => 'category', 'expert_category_id' => $cat->id]);
        $old = $policy->exists ? $policy->toArray() : null;
        $policy->fill($data + ['expert_category' => $cat->name, 'is_active' => true])->save();
        $this->log('CategoryPolicy', $policy->id, $old, $policy->toArray(), "Rate for {$cat->name}");

        return back()->with('success', "{$cat->name}: rate saved.")->withFragment('categories');
    }

    /** a category back on the global default */
    public function destroyCategory(ExpertCategory $category)
    {
        $policy = CommissionPolicy::where('policy_type', 'category')->where('expert_category_id', $category->id)->where('is_active', true)->first();
        if ($policy) {
            $policy->update(['is_active' => false]);
            $this->log('CategoryPolicy', $policy->id, $policy->toArray(), null, "{$category->name} now uses the global default");
        }
        return back()->with('success', "{$category->name} now uses the global default.")->withFragment('categories');
    }

    /** give an expert their own rate (replaces the one they had; earlier ones are kept, inactive, as history) */
    public function storeExpert(Request $request)
    {
        $request->validate(['expert_id' => 'required|exists:experts,id', 'reason' => 'nullable|string|max:255']);
        $data = $this->validateRate($request);
        $expert = Expert::findOrFail($request->expert_id);

        $new = DB::transaction(function () use ($expert, $data, $request) {
            ExpertCommission::where('expert_id', $expert->id)->where('is_active', true)->update(['is_active' => false]);
            return ExpertCommission::create($data + ['expert_id' => $expert->id, 'reason' => $request->reason, 'is_active' => true]);
        });
        $this->log('ExpertCommission', $expert->id, null, $new->toArray(), $request->reason ?: "Own rate for {$expert->name}");

        return back()->with('success', "{$expert->name}: own rate saved.")->withFragment('experts');
    }

    /** the expert goes back to their category rate (or the global default) */
    public function destroyExpert(Expert $expert)
    {
        $active = ExpertCommission::where('expert_id', $expert->id)->where('is_active', true)->get();
        if ($active->isNotEmpty()) {
            ExpertCommission::whereIn('id', $active->pluck('id'))->update(['is_active' => false]);
            $this->log('ExpertCommission', $expert->id, $active->last()->toArray(), null, "{$expert->name} now uses the category or global rate");
        }
        return back()->with('success', "{$expert->name} now uses the category or global rate.")->withFragment('experts');
    }

    // ---- internals ----

    private function global(): CommissionPolicy
    {
        return CommissionPolicy::firstOrCreate(['policy_type' => 'global'],
            ['commission_type' => 'percentage', 'commission_value' => 10, 'gst_applicable' => true, 'tds_applicable' => true, 'is_active' => true]);
    }

    /** a rate: a percentage (0–100) or a flat fee in rupees */
    private function validateRate(Request $request): array
    {
        return $request->validate([
            'commission_type' => ['required', Rule::in(['percentage', 'flat_fee'])],
            'commission_value' => ['required', 'numeric', 'min:0', $request->input('commission_type') === 'percentage' ? 'max:100' : 'max:100000'],
        ], ['commission_value.max' => $request->input('commission_type') === 'percentage' ? 'A percentage cannot be more than 100.' : 'The flat fee is too large.'],
           ['commission_value' => 'rate']);
    }

    private function log(string $type, int $id, ?array $old, ?array $new, string $reason): void
    {
        CommissionLog::create(['entity_type' => $type, 'entity_id' => $id, 'old_value' => $old ? json_encode($old) : null,
            'new_value' => $new ? json_encode($new) : null, 'action_by' => auth()->id(), 'reason' => mb_substr($reason, 0, 255)]);
    }
}

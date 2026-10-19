<?php

namespace App\Http\Controllers\Admin\Counselling;

use App\Http\Controllers\Controller;
use App\Models\Counselling\Alias;
use App\Models\Counselling\Category;
use App\Models\Counselling\Counselling;
use App\Models\Counselling\CounsellingCycle;
use App\Models\Counselling\ImportBatch;
use App\Models\Counselling\ImportRow;
use App\Models\Counselling\Quota;
use App\Models\Counselling\Round;
use App\Services\Counselling\Importer;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;

/** Import centre: upload -> preview -> fix names -> confirm -> (undo). Spec section 12. */
class ImportController extends Controller
{
    public function __construct(private Importer $importer)
    {
    }

    public function index(Request $request)
    {
        $batches = ImportBatch::with(['cycle.counselling', 'round', 'user'])
            ->when($request->type, fn ($q, $t) => $q->where('type', $t))
            ->latest('id')->paginate(25)->withQueryString();
        $aliases = Alias::orderBy('type')->orderBy('alias')->limit(300)->get();

        return view('admin.counselling.imports.index', ['batches' => $batches, 'aliases' => $aliases, 'names' => $this->aliasTargets($aliases)]);
    }

    public function create(Request $request)
    {
        $cycle = CounsellingCycle::with(['counselling', 'rounds'])->find($request->cycle);
        return view('admin.counselling.imports.create', [
            'cycle' => $cycle,
            'round' => $cycle ? $cycle->rounds->firstWhere('id', (int) $request->round) : null,
            'type' => in_array($request->type, array_keys(ImportBatch::TYPES)) ? $request->type : 'participants',
            'cycles' => CounsellingCycle::with(['counselling:id,name', 'rounds:id,cycle_id,name'])->orderByDesc('year')->get(),
        ]);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'type' => ['required', Rule::in(array_keys(ImportBatch::TYPES))],
            'cycle_id' => ['required', Rule::exists('cns_cycles', 'id')->whereNull('deleted_at')],
            'round_id' => ['nullable', Rule::exists('cns_rounds', 'id')->whereNull('deleted_at')],
            'mode' => ['required', Rule::in(['append', 'replace'])],
            'make_live' => 'nullable|boolean',
            'label' => 'nullable|string|max:255',
            'published_on' => 'nullable|date',
            'source_url' => 'nullable|url|max:500',
            'file' => 'required|file|mimes:csv,txt|max:' . (Importer::MAX_BYTES / 1024),
        ]);
        $cycle = CounsellingCycle::findOrFail($data['cycle_id']);

        $round = null;
        if ($data['type'] !== 'participants') {
            $round = Round::where('cycle_id', $cycle->id)->find($data['round_id'] ?? 0);
            if (!$round) {
                return back()->withInput()->withErrors(['round_id' => 'Choose the round these rows belong to.']);
            }
        }

        try {
            $batch = $this->importer->createBatch($data['type'], $cycle, $round, [
                'mode' => $data['mode'], 'make_live' => $request->boolean('make_live', true), 'label' => $data['label'] ?? null,
                'published_on' => $data['published_on'] ?? null, 'source_url' => $data['source_url'] ?? null,
            ], $request->file('file'));
        } catch (\InvalidArgumentException|\RuntimeException $e) {
            return back()->withInput()->withErrors(['file' => $e->getMessage()]);
        }

        return redirect()->route('admin.counselling.imports.show', $batch)->with('success', 'File read. Check the preview before confirming: nothing is saved yet.');
    }

    public function show(Request $request, ImportBatch $batch)
    {
        $batch->load(['cycle.counselling', 'round', 'user']);
        $rows = ImportRow::where('batch_id', $batch->id)
            ->when($request->status, fn ($q, $s) => $q->where('status', $s))
            ->orderBy('row_no')->paginate(50)->withQueryString();

        // Distinct names that still need a decision, with how many rows each affects.
        $unresolved = $this->unresolved($batch);

        return view('admin.counselling.imports.show', compact('batch', 'rows', 'unresolved'));
    }

    /** Pick the right master record for a name the importer could not match; it is remembered as an alias. */
    public function fix(Request $request, ImportBatch $batch)
    {
        $this->assertOpen($batch);
        $data = $request->validate([
            'type' => ['required', Rule::in(['organisation', 'course', 'quota', 'category', 'exam'])],
            'raw' => 'required|string|max:255',
            'target_id' => 'required|integer',
        ]);
        $table = ['organisation' => 'organisations', 'course' => 'courses', 'quota' => 'cns_quotas', 'category' => 'cns_categories', 'exam' => 'exm_exams'][$data['type']];
        if (!DB::table($table)->where('id', $data['target_id'])->exists()) {
            return back()->with('error', 'That record does not exist.');
        }
        $this->importer->fix($batch, $data['type'], $data['raw'], (int) $data['target_id']);

        return back()->with('success', 'Saved. "' . $data['raw'] . '" will be recognised from now on.');
    }

    public function accept(Request $request, ImportBatch $batch)
    {
        $this->assertOpen($batch);
        $n = $this->importer->acceptSuggestions($batch, $request->input('type'), $request->input('raw'));
        return back()->with('success', $n . ' suggested match' . ($n === 1 ? '' : 'es') . ' accepted.');
    }

    public function confirm(ImportBatch $batch)
    {
        $this->assertOpen($batch);
        $saved = $this->importer->confirm($batch);
        $skipped = $batch->fresh()->total_rows - $saved;

        return redirect()->route('admin.counselling.imports.show', $batch)
            ->with('success', "$saved row" . ($saved === 1 ? '' : 's') . ' saved.' . ($skipped ? " $skipped row" . ($skipped === 1 ? ' was' : 's were') . ' skipped: download the error file to fix and re-upload them.' : ''));
    }

    public function undo(ImportBatch $batch)
    {
        $this->importer->undo($batch);
        return back()->with('success', 'Import undone.');
    }

    public function cancel(ImportBatch $batch)
    {
        $this->assertOpen($batch);
        $batch->update(['status' => 'cancelled']);
        return redirect()->route('admin.counselling.imports.index')->with('success', 'Import cancelled. Nothing was saved.');
    }

    public function errors(ImportBatch $batch)
    {
        return response($this->importer->errorCsv($batch), 200, [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => 'attachment; filename="import-' . $batch->id . '-errors.csv"',
        ]);
    }

    public function template(string $type)
    {
        return response("\xEF\xBB\xBF" . $this->importer->template($type), 200, [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => 'attachment; filename="' . $type . '-template.csv"',
        ]);
    }

    /** Search-as-you-type for the pickers in the preview and in the manual forms. */
    public function lookup(Request $request)
    {
        $q = trim((string) $request->q);
        $type = $request->type;
        $rows = match ($type) {
            'organisation' => DB::table('organisations')->when($q, fn ($b) => $b->where('name', 'like', "%$q%"))->orderBy('name')->limit(30)->get(['id', 'name as text']),
            'course' => DB::table('courses')->when($q, fn ($b) => $b->where('name', 'like', "%$q%")->orWhere('full_form', 'like', "%$q%"))->orderBy('name')->limit(30)->get(['id', DB::raw("CONCAT(name, ' (#', id, ')') as text")]),
            'quota' => Quota::when($q, fn ($b) => $b->where('name', 'like', "%$q%"))->orderBy('sort_order')->limit(30)->get(['id', 'name as text']),
            'category' => Category::when($q, fn ($b) => $b->where('name', 'like', "%$q%"))->orderBy('sort_order')->limit(30)->get(['id', 'name as text']),
            'exam' => DB::table('exm_exams')->whereNull('deleted_at')->when($q, fn ($b) => $b->where('name', 'like', "%$q%"))->orderBy('name')->limit(30)->get(['id', 'name as text']),
            default => abort(404),
        };
        return response()->json(['results' => $rows]);
    }

    public function destroyAlias(Alias $alias)
    {
        $alias->delete();
        return back()->with('success', 'Alias removed. The name will need to be matched again next time.');
    }

    // ---- internals ----

    private function assertOpen(ImportBatch $batch): void
    {
        abort_unless($batch->status === 'previewed', 422, 'This import has already been ' . $batch->status . '.');
    }

    /** @return array{errors: array, warnings: array} grouped by field and raw value */
    private function unresolved(ImportBatch $batch): array
    {
        $out = ['errors' => [], 'warnings' => []];
        foreach (ImportRow::where('batch_id', $batch->id)->whereIn('status', ['error', 'warning'])->cursor() as $row) {
            foreach ($row->resolved['_suggest'] ?? [] as $field => $s) {
                $k = $field . '|' . Alias::normalize($s['raw']);
                $out['warnings'][$k] ??= ['type' => $field, 'raw' => $s['raw'], 'suggest' => $s, 'rows' => 0];
                $out['warnings'][$k]['rows']++;
            }
            // Name errors are fixable by picking a master record; other errors (numbers, duplicates) are not.
            foreach ($row->messages ?? [] as $msg) {
                if (preg_match('/^(College|Course|Quota|Category|Exam) "(.+?)":/', $msg, $m)) {
                    $type = ['College' => 'organisation', 'Course' => 'course', 'Quota' => 'quota', 'Category' => 'category', 'Exam' => 'exam'][$m[1]];
                    $k = $type . '|' . Alias::normalize($m[2]);
                    $out['errors'][$k] ??= ['type' => $type, 'raw' => $m[2], 'message' => preg_replace('/^[^:]+: /', '', $msg), 'rows' => 0];
                    $out['errors'][$k]['rows']++;
                }
            }
        }
        $out['errors'] = array_values($out['errors']);
        $out['warnings'] = array_values($out['warnings']);
        return $out;
    }

    private function aliasTargets($aliases): array
    {
        $names = [];
        foreach ($aliases->groupBy('type') as $type => $group) {
            $table = ['organisation' => 'organisations', 'course' => 'courses', 'quota' => 'cns_quotas', 'category' => 'cns_categories', 'exam' => 'exm_exams'][$type];
            $names[$type] = DB::table($table)->whereIn('id', $group->pluck('target_id'))->pluck('name', 'id')->all();
        }
        return $names;
    }
}

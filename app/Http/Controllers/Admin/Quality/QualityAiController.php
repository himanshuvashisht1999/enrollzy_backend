<?php

namespace App\Http\Controllers\Admin\Quality;

use App\Http\Controllers\Controller;
use App\Services\ClaudeExtractionService;
use App\Services\Quality\QualityAiService;
use App\Support\Quality\Levels;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

/** Create / Missing Info / Update of rankings, accreditations and approvals by Bot (Gemini) or Claude, on the Quality record page. */
class QualityAiController extends Controller
{
    public function __construct(private QualityAiService $service) {}

    private function input(Request $request, string $type): array
    {
        return $request->validate([
            'action' => 'required|in:' . implode(',', array_keys(QualityAiService::ACTIONS)),
            'kinds' => 'required|array|min:1',
            // a campus may only be filled with statutory approvals: rankings and accreditations belong to its organisation
            'kinds.*' => 'in:' . implode(',', array_keys(QualityAiService::kindsFor($type))),
            'url' => 'nullable|url|max:500',
            'search_google' => 'nullable|boolean',
            // claude_gemini / gemini_claude: the pop-up runs the two AIs as two steps, each step sends its own engine
            'engine' => 'nullable|in:gemini,claude,claude_gemini,gemini_claude',
            'command' => 'nullable|string|max:200000',
            // the first AI's result, when this is the second step of "Claude + Bot" / "Bot + Claude"
            'previous' => 'nullable|array',
        ]);
    }

    /** The master command for the chosen action, shown (and editable) before running. */
    public function command(Request $request, string $type, string $id)
    {
        $entity = Levels::find($type, $id) ?? abort(404);
        $d = $this->input($request, $type);
        @set_time_limit(120);
        $url = $d['url'] ?? $this->service->defaultUrl($entity, $type);
        $text = '';
        try {
            $text = $url ? app(\App\Services\GeminiOrganisationScraperService::class)->readWebsite($url) : '';
        } catch (\Throwable $e) {
            Log::info('Quality AI: website not read: ' . $e->getMessage());
        }
        $command = $this->service->buildCommand($entity, $type, $d['action'], $d['kinds'], (bool) ($d['search_google'] ?? true), $url, $text);
        return response()->json(['command' => $command, 'url' => $url, 'chars' => mb_strlen($command)]);
    }

    /** Runs the command on Bot or Claude and returns the rows found, compared with what is recorded. */
    public function run(Request $request, string $type, string $id)
    {
        $entity = Levels::find($type, $id) ?? abort(404);
        $d = $this->input($request, $type);
        // a combined choice sent as one step runs its first AI (the pop-up normally sends each step's own engine)
        $engine = explode('_', $d['engine'] ?? 'gemini')[0];
        @set_time_limit($engine === 'claude' ? 600 : 300);
        @ini_set('max_execution_time', $engine === 'claude' ? '600' : '300');
        ClaudeExtractionService::$lastUsage = [];
        try {
            $url = $d['url'] ?? $this->service->defaultUrl($entity, $type);
            $previous = is_array($request->input('previous')) ? $request->input('previous') : null;
            $result = $this->service->run($entity, $type, $d['action'], $d['kinds'], (bool) ($d['search_google'] ?? true), $url, $engine, $d['command'] ?? null, $previous);
            return response()->json(['success' => true, 'engine' => $engine, 'usage' => ClaudeExtractionService::$lastUsage] + $result);
        } catch (\Throwable $e) {
            Log::warning('Quality AI run failed for ' . $type . ' ' . $id . ': ' . $e->getMessage());
            return response()->json(['success' => false, 'message' => $e->getMessage()], 422);
        }
    }

    /**
     * Keeps what a finished run found (its notes and the rows it returned) for reference on this page, so the same research is
     * not run again. CRM only: never shown on the website. Called by the pop-up once per run (after both AIs of a combination).
     */
    public function storeLog(Request $request, string $type, string $id)
    {
        Levels::find($type, $id) ?? abort(404);
        $d = $request->validate([
            'action' => 'required|in:' . implode(',', array_keys(QualityAiService::ACTIONS)),
            'engine' => 'required|in:gemini,claude,claude_gemini,gemini_claude',
            'kinds' => 'nullable|array|max:5', 'kinds.*' => 'string|max:30',
            'steps' => 'nullable|array|max:10', 'steps.*' => 'nullable|string|max:1000',
            'notes' => 'nullable|array|max:100', 'notes.*' => 'nullable|string|max:3000',
            'sources' => 'nullable|array|max:100', 'sources.*' => 'nullable|string|max:1000',
            'rows' => 'nullable|array|max:300',
            'rows.*.kind' => 'nullable|string|max:30', 'rows.*.text' => 'nullable|string|max:1000', 'rows.*.notes' => 'nullable|string|max:3000',
            'rows.*.state' => 'nullable|string|max:60', 'rows.*.found_by' => 'nullable|string|max:60', 'rows.*.source_url' => 'nullable|string|max:1000',
        ]);
        $clean = fn ($list) => array_values(array_filter(array_map(fn ($v) => trim((string) $v), (array) $list), fn ($v) => $v !== ''));
        $rows = array_map(fn ($r) => array_intersect_key((array) $r, array_flip(['kind', 'text', 'notes', 'state', 'found_by', 'source_url'])), (array) ($d['rows'] ?? []));
        $log = [
            'entity_type' => $type, 'entity_id' => (string) $id, 'action' => $d['action'], 'engine' => $d['engine'],
            'kinds' => json_encode($clean($d['kinds'] ?? [])), 'steps' => json_encode($clean($d['steps'] ?? [])),
            'notes' => json_encode($clean($d['notes'] ?? []), JSON_UNESCAPED_UNICODE), 'rows' => json_encode($rows, JSON_UNESCAPED_UNICODE),
            'sources' => json_encode($clean($d['sources'] ?? []), JSON_UNESCAPED_UNICODE),
            'admin_id' => auth('admin')->id(), 'created_at' => now(), 'updated_at' => now(),
        ];
        $log['id'] = \Illuminate\Support\Facades\DB::table('quality_ai_logs')->insertGetId($log);
        return response()->json(['success' => true, 'log' => QualityAiService::presentLog((object) $log, auth('admin')->user()?->name)]);
    }

    /** Removes one saved AI result from this page. */
    public function destroyLog(string $type, string $id, int $log)
    {
        Levels::find($type, $id) ?? abort(404);
        $deleted = \Illuminate\Support\Facades\DB::table('quality_ai_logs')->where('id', $log)->where('entity_type', $type)->where('entity_id', (string) $id)->delete();
        return response()->json(['success' => (bool) $deleted]);
    }

    /** Saves the ticked rows (new ones created, "update" ones changing the existing record) with the manual forms' checks. */
    public function save(Request $request, string $type, string $id)
    {
        Levels::find($type, $id) ?? abort(404);
        $d = $request->validate(['rows' => 'required|array|min:1|max:200', 'rows.*.kind' => 'required|in:rankings,accreditations,approvals', 'rows.*.values' => 'required|array']);
        // the rows as sent (validated above), including each row's existing_id and label
        $rows = array_map(fn ($r) => ['kind' => $r['kind'] ?? '', 'values' => (array) ($r['values'] ?? []),
            'existing_id' => isset($r['existing_id']) && is_numeric($r['existing_id']) ? (int) $r['existing_id'] : null,
            'label' => mb_substr((string) ($r['label'] ?? ''), 0, 200)], array_values($request->input('rows', [])));
        $results = $this->service->save($rows, $type, $id);
        $saved = collect($results)->whereIn('result', ['saved', 'updated'])->count();
        if ($saved) session()->flash('success', $saved . ' record(s) saved from AI. They stay unverified until someone verifies them.');
        return response()->json(['success' => true, 'saved' => $saved, 'results' => $results]);
    }
}

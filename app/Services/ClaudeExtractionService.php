<?php

namespace App\Services;

use Anthropic\Client;
use Anthropic\Core\Exceptions\APIConnectionException;
use Anthropic\Core\Exceptions\APIStatusException;
use Anthropic\Lib\Streaming\MessageAccumulator;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

/**
 * Sends a "Create by Bot" master command (built by GeminiOrganisationScraperService for the chosen step: organisation by
 * type, campus, department, course or school course) to Claude instead of Gemini and returns Claude's reply text.
 * With search grounding on, Claude may search the web (web search tool) the way Gemini uses Google Search.
 *
 * Settings (.env, read through config/services.php):
 *   ANTHROPIC_API_KEY        the key (required)
 *   CLAUDE_BOT_MODEL         default claude-opus-5-5; claude-sonnet-5-5 costs about half
 *   CLAUDE_BOT_EFFORT        low | medium | high (default medium): how hard Claude thinks
 *   CLAUDE_BOT_WEB_SEARCHES  most web searches per extraction (default 5)
 */
class ClaudeExtractionService
{
    /** models that take the server-side refusal fallback ("default" form) on the Claude API */
    private const FALLBACK_MODELS = ['claude-opus-5-5', 'claude-opus-5', 'claude-sonnet-5-5', 'claude-fable-5-1'];

    /** the last call's token use, for the log and the admin's cost note */
    public static array $lastUsage = [];

    /** official sites the last call could not open because they block Claude's page reader (e.g. indiatoday.in) */
    public static array $blockedDomains = [];

    private const BLOCKED_CACHE_KEY = 'claude_fetch_blocked_domains';

    /** $transporter: an HTTP client to use instead of the network (tests); null = the real Claude API */
    public function __construct(private ?\Psr\Http\Client\ClientInterface $transporter = null) {}

    public function isConfigured(): bool
    {
        return (string) config('services.anthropic.api_key') !== '';
    }

    public function model(): string
    {
        return (string) (config('services.anthropic.model') ?: 'claude-opus-5-5');
    }

    /**
     * Runs the command and returns the text of Claude's final answer (the JSON the command asks for).
     *
     * @throws \Exception with a readable message when the key is missing, the API refuses, or nothing came back
     */
    /**
     * $fetchDomains: when given, Claude also gets a web_fetch tool that can open pages ONLY on these domains (e.g. the official websites of
     * NIRF, NAAC, NBA...). With $webSearch off this is the only way it reaches the internet: no search engine, just those sites.
     */
    public function run(string $prompt, bool $webSearch = true, ?int $maxSearches = null, ?array $fetchDomains = null): string
    {
        if (!$this->isConfigured()) {
            throw new \Exception('Claude API key is not configured. Add ANTHROPIC_API_KEY to the .env file.');
        }

        $model = $this->model();
        $client = new Client(apiKey: (string) config('services.anthropic.api_key'));
        $messages = [['role' => 'user', 'content' => $prompt]];
        // Sites that refuse Claude's page reader (the API then rejects the whole request): skip the ones already known
        self::$blockedDomains = [];
        $known = (array) Cache::get(self::BLOCKED_CACHE_KEY, []);
        $fetchDomains = array_values(array_filter((array) $fetchDomains));
        self::$blockedDomains = array_values(array_intersect($fetchDomains, $known));
        $fetchDomains = array_values(array_diff($fetchDomains, $known));
        $build = function () use ($webSearch, $maxSearches, &$fetchDomains) {
            $tools = [];
            if ($webSearch) {
                $tools[] = ['type' => 'web_search_20260209', 'name' => 'web_search',
                    'maxUses' => max(1, $maxSearches ?? (int) (config('services.anthropic.web_searches') ?: 5))];
            }
            if ($fetchDomains) {
                $tools[] = ['type' => 'web_fetch_20260209', 'name' => 'web_fetch', 'allowedDomains' => $fetchDomains, 'maxUses' => 100, 'maxContentTokens' => 30000];
            }
            $system = 'You extract verified facts about Indian educational institutions for a database. Follow the user\'s command exactly and reply with only the JSON it asks for.'
                . ($webSearch ? ' You have a web_search tool: use it to find and verify the facts before you answer. Never answer from memory alone.' : '')
                . ($fetchDomains ? ' You have a web_fetch tool that opens pages only on these official websites: ' . implode(', ', $fetchDomains) . '. Open their pages to verify every fact' . ($webSearch ? '.' : '; you have no search engine.') : '');
            return [$tools ?: null, $system];
        };
        [$tools, $system] = $build();

        $fallback = in_array($model, self::FALLBACK_MODELS, true);
        $nudged = false;
        $searches = 0;
        $fetches = 0;
        $domainRetries = 0;

        $message = null;
        // a long web-search turn can pause (server loop limit): send it back once or twice to let Claude finish
        for ($round = 0; $round < 4; $round++) {
            try {
                $stream = $client->beta->messages->createStream(
                    maxTokens: 64000,
                    messages: $messages,
                    model: $model,
                    fallbacks: $fallback ? 'default' : null,
                    outputConfig: ['effort' => (string) (config('services.anthropic.effort') ?: 'medium')],
                    system: $system,
                    tools: $tools,
                    betas: $fallback ? ['server-side-fallback-2026-07-01'] : null,
                    requestOptions: ['timeout' => 280.0, 'maxRetries' => 1]
                        + ($this->transporter ? ['transporter' => $this->transporter, 'streamingTransporter' => $this->transporter] : []),
                );
                $acc = MessageAccumulator::forBetaMessages();
                foreach ($stream as $event) {
                    $acc->accumulate($event);
                }
                $message = $acc->message();
            } catch (APIStatusException $e) {
                // "The following domains are not accessible to our user agent: ['indiatoday.in']": drop those sites and try again
                if ((int) ($e->status ?? 0) === 400 && $fetchDomains && $domainRetries < 5
                    && preg_match('/domains are not accessible to our user agent:\s*\[([^\]]*)\]/i', $e->getMessage(), $m)) {
                    preg_match_all("/['\"]([^'\"]+)['\"]/", $m[1], $d);
                    $blocked = array_values(array_intersect($fetchDomains, $d[1] ?? []));
                    if ($blocked) {
                        $domainRetries++;
                        self::$blockedDomains = array_values(array_unique(array_merge(self::$blockedDomains, $blocked)));
                        Cache::put(self::BLOCKED_CACHE_KEY, array_values(array_unique(array_merge((array) Cache::get(self::BLOCKED_CACHE_KEY, []), $blocked))), now()->addDays(7));
                        Log::info('Claude cannot open these sites; retrying without them', ['domains' => $blocked]);
                        $fetchDomains = array_values(array_diff($fetchDomains, $blocked));
                        [$tools, $system] = $build();
                        $round--;
                        continue;
                    }
                }
                Log::warning('Claude extraction failed: ' . $e->getMessage());
                throw new \Exception('Claude API error: ' . $this->readable($e));
            } catch (APIConnectionException $e) {
                Log::warning('Claude extraction connection error: ' . $e->getMessage());
                throw new \Exception('Could not reach the Claude API. Please try again.');
            }

            $searches += (int) ($message->usage->serverToolUse->webSearchRequests ?? 0);
            $fetches += (int) ($message->usage->serverToolUse->webFetchRequests ?? 0);
            $prev = self::$lastUsage;
            self::$lastUsage = [
                'model' => $message->model ?? $model,
                'input_tokens' => (int) ($message->usage->inputTokens ?? 0) + ($round ? (int) ($prev['input_tokens'] ?? 0) : 0),
                'output_tokens' => (int) ($message->usage->outputTokens ?? 0) + ($round ? (int) ($prev['output_tokens'] ?? 0) : 0),
                'web_searches' => $searches,
                'pages_opened' => $fetches,
            ];
            Log::info('Claude extraction', self::$lastUsage + ['stop_reason' => $message->stopReason, 'round' => $round]);

            if ($message->stopReason === 'pause_turn') {
                $messages = [['role' => 'user', 'content' => $prompt], ['role' => 'assistant', 'content' => $message->content]];
                continue;
            }
            // Web tools were given but Claude answered from memory: send it back once to do the research
            if (($webSearch || $fetchDomains) && $searches + $fetches === 0 && !$nudged && $message->stopReason === 'end_turn') {
                $nudged = true;
                $how = $webSearch ? 'use the web_search tool' . ($fetchDomains ? ' and open the official pages with web_fetch' : '') : 'open the official websites with the web_fetch tool';
                $messages = [
                    ['role' => 'user', 'content' => $prompt],
                    ['role' => 'assistant', 'content' => $message->content],
                    ['role' => 'user', 'content' => "You answered without using your web tools. Do the research now: {$how} to check each item on its official source, then reply again with ONLY the complete JSON in the same format."],
                ];
                continue;
            }
            break;
        }

        if ($message->stopReason === 'refusal') {
            throw new \Exception('Claude declined this request' . ($message->stopDetails?->explanation ? ': ' . $message->stopDetails->explanation : '.'));
        }
        if ($message->stopReason === 'max_tokens') {
            Log::warning('Claude extraction hit max_tokens; the JSON may be cut off', self::$lastUsage);
        }

        // the answer is the text after Claude's last web search (earlier text is its search narration)
        $blocks = array_values($message->content ?? []);
        $lastTool = -1;
        foreach ($blocks as $i => $b) {
            if (in_array($b->type ?? '', ['server_tool_use', 'web_search_tool_result', 'web_fetch_tool_result'], true)) $lastTool = $i;
        }
        $after = $all = '';
        foreach ($blocks as $i => $b) {
            if (($b->type ?? '') !== 'text') continue;
            $all .= $b->text;
            if ($i > $lastTool) $after .= $b->text;
        }
        $text = trim($after) !== '' ? $after : $all;
        if (trim($text) === '') {
            throw new \Exception('Claude returned no answer. Please try again.');
        }
        return $text;
    }

    private function readable(APIStatusException $e): string
    {
        $status = (int) ($e->status ?? 0);
        return match (true) {
            $status === 401 => 'the API key is not valid (check ANTHROPIC_API_KEY).',
            $status === 403 => 'this API key is not allowed to use ' . $this->model() . '.',
            $status === 429 => 'too many requests or the spend limit was reached. Please wait a minute and try again.',
            $status >= 500 => 'Claude is busy right now (' . $status . '). Please try again shortly.',
            default => $e->getMessage(),
        };
    }
}

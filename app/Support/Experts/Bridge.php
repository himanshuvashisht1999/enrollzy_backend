<?php

namespace App\Support\Experts;

use Illuminate\Support\Facades\DB;

/**
 * Signed requests between the website and the CRM for expert documents. On the live server each site can only reach
 * its own folders, so the CRM keeps every expert document (storage/app/private/expert-docs) and the website sends
 * uploads and fetches files through the CRM's /api/v1/expert-docs (Api\V1\ExpertDocsBridgeController).
 *
 * A request carries ?p= (the file's path, or the folder to store in), ?e= (expiry, unix time) and ?s= (signature):
 * HMAC-SHA256 of "METHOD|p|e" with settings.expert_docs_key, a random key both sites read from the shared database.
 * The website has the same class (enrollzy_frontend App\Support\Experts\Bridge): keep the two in step.
 */
class Bridge
{
    public static function key(): string
    {
        $key = DB::table('settings')->orderBy('id')->value('expert_docs_key');
        if (!$key) {
            DB::table('settings')->orderBy('id')->limit(1)->whereNull('expert_docs_key')->update(['expert_docs_key' => bin2hex(random_bytes(32))]);
            $key = DB::table('settings')->orderBy('id')->value('expert_docs_key');   // the one saved, if the other site was first
        }
        return (string) $key;
    }

    public static function sign(string $method, string $p, int $expires): string
    {
        return hash_hmac('sha256', strtoupper($method) . '|' . $p . '|' . $expires, self::key());
    }

    public static function valid(string $method, ?string $p, $expires, ?string $signature): bool
    {
        return $p !== null && $signature !== null && ctype_digit((string) $expires) && (int) $expires >= time() && (int) $expires <= time() + 3600
            && hash_equals(self::sign($method, $p, (int) $expires), $signature);
    }
}

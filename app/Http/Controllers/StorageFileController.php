<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use League\Flysystem\PathTraversalDetected;

/**
 * GET /storage/{path}: files of the public disk (storage/app/public), for servers without the public/storage link (the
 * live CRM has none, so every /storage/... address was 403).
 *
 * Only folders the website shows are open to everyone: exam and counselling files (papers, syllabus, bulletins, results,
 * logos...), category images, mentor photos. Every other folder (attendance selfies, expense receipts, project
 * documents, counselling imports, and any folder added later) needs a CRM login.
 */
class StorageFileController extends Controller
{
    public const PUBLIC_FOLDERS = [
        'exm/', 'cns/content/', 'cns/bulletins/', 'cns/results/', 'cns/logos/', 'cns/covers/', 'cns/custom/',
        'exams/', 'dynamic_exams_data/', 'dynamic_counsellings_data/', 'community_categories/', 'mentor_profiles/',
    ];

    public function __invoke(Request $request, string $path)
    {
        $path = ltrim(str_replace('\\', '/', $path), '/');
        // "exm/../attendance/x.jpg" would pass the folder check and still reach a private folder: no . or .. parts at all
        $parts = explode('/', $path);
        abort_if(array_intersect($parts, ['.', '..', '']) || str_contains($path, "\0"), 404);
        $open = collect(self::PUBLIC_FOLDERS)->contains(fn ($f) => str_starts_with($path, $f));
        // a private file answers 404 to visitors, so its name is not even confirmed
        abort_unless($open || Auth::guard('admin')->check(), 404);
        try {
            abort_unless(Storage::disk('public')->fileExists($path), 404);
            return Storage::disk('public')->response($path, null, $open
                ? ['Cache-Control' => 'public, max-age=86400', 'X-Content-Type-Options' => 'nosniff']
                : ['Cache-Control' => 'private, no-store', 'X-Content-Type-Options' => 'nosniff', 'X-Robots-Tag' => 'noindex']);
        } catch (PathTraversalDetected $e) {
            abort(404);
        }
    }
}

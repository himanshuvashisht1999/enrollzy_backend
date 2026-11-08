<?php

namespace App\Support\Experts;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Str;

/**
 * Expert documents and application photos: private files in storage/app/private/expert-docs, never in a public folder
 * and never at a /storage address. The CRM shows them to staff through an admin-only route; the website (which keeps
 * its uploads in this same folder, see the website's App\Support\Experts\Docs) shows an expert only their own.
 */
class Docs
{
    public const MIMES = 'pdf,jpg,jpeg,png,webp';
    public const MAX_KB = 5120;

    public static function root(): string
    {
        return storage_path('app/private/expert-docs');
    }

    /** save an upload under $folder ("applications/12", "experts/7") with a random name; returns the stored path */
    public static function store(UploadedFile $file, string $folder): string
    {
        $folder = trim(preg_replace('#[^a-z0-9/_-]#i', '', $folder), '/');
        $name = Str::random(32) . '.' . ($file->guessExtension() ?: 'bin');   // the extension of the real content, not the name sent
        $file->move(self::root() . '/' . $folder, $name);
        return $folder . '/' . $name;
    }

    /** the file on disk, or null (also for any path trying to leave the folder) */
    public static function absolute(?string $path): ?string
    {
        if (!$path || str_contains($path, '..') || str_contains($path, "\0")) return null;
        $file = self::root() . '/' . ltrim($path, '/');
        return is_file($file) ? $file : null;
    }

    /** show the file in the browser (PDF or image), never cached, never run as a page */
    public static function response(?string $path, ?string $name = null)
    {
        $file = self::absolute($path);
        abort_unless($file, 404);
        return response()->file($file, [
            'Content-Disposition' => 'inline; filename="' . str_replace(['"', "\r", "\n"], '', $name ?: basename($file)) . '"',
            // only PDFs and images are accepted (checked by content), and nosniff stops a browser treating one as a page
            'Cache-Control' => 'private, no-store', 'X-Content-Type-Options' => 'nosniff',
        ]);
    }

    public static function delete(?string $path): void
    {
        if ($file = self::absolute($path)) @unlink($file);
    }
}

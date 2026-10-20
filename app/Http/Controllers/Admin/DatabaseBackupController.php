<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ExamModule\AuditLog;
use App\Services\DatabaseBackup;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\StreamedResponse;

/**
 * GET /admin/database-backup: downloads the whole database as a .sql.gz file.
 * Any logged-in admin (the admin route group checks the login). Not linked from any menu: open the URL directly.
 * At most 5 downloads an hour (route throttle), each download written to the audit log, and the file is streamed
 * straight to the browser (never stored on the server).
 * Add ?full=1 to also include the rows of the sessions / cache / queue tables (left out by default).
 */
class DatabaseBackupController extends Controller
{
    public function download(Request $request, DatabaseBackup $backup)
    {
        $user = auth('admin')->user() ?? auth()->user();
        $skip = !$request->boolean('full');
        AuditLog::create(['auditable_type' => 'database_backup', 'auditable_id' => 0, 'user_id' => $user?->id, 'action' => 'download',
            'old_values' => null, 'new_values' => ['ip' => $request->ip(), 'skipped_throwaway_rows' => $skip]]);

        $file = config('database.connections.' . config('database.default') . '.database') . '-' . now()->format('Y-m-d-His') . '.sql.gz';
        return new StreamedResponse(function () use ($backup, $skip) {
            @set_time_limit(0);
            @ini_set('zlib.output_compression', '0');
            while (ob_get_level() > 0) ob_end_flush();
            $backup->stream(function (string $chunk) { echo $chunk; flush(); }, $skip);
        }, 200, [
            'Content-Type' => 'application/gzip',
            'Content-Disposition' => 'attachment; filename="' . $file . '"',
            'Cache-Control' => 'no-store, no-cache, must-revalidate',
            'X-Accel-Buffering' => 'no',
        ]);
    }
}
